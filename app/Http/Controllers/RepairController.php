<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\RepairItem;
use App\Models\Warranty;
use App\Models\Product;
use App\Models\Material;
use App\Models\WarehouseMaterial;
use App\Models\AssemblyMaterial;
use App\Models\MaterialReplacementHistory;
use App\Models\DispatchItem;
use App\Models\DamagedMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Models\Employee;
use App\Models\Warehouse;
use App\Models\Dispatch;
use App\Models\Serial;
use App\Models\Assembly;
use App\Models\User;
use App\Helpers\ChangeLogHelper;
use App\Models\Good;
use App\Models\ProductMaterial;
use App\Models\UserLog;

class RepairController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Repair::with(['warranty', 'maintenanceRequest.customer', 'repairItems', 'technician', 'createdBy', 'warehouse']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('repair_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('warranty_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('technician', function ($techQuery) use ($searchTerm) {
                        $techQuery->where('name', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('warranty', function ($warrantyQuery) use ($searchTerm) {
                        $warrantyQuery->where('customer_name', 'LIKE', "%{$searchTerm}%");
                    })
                    // Tìm theo tên khách hàng từ phiếu bảo trì (nếu tạo từ bảo trì)
                    ->orWhereHas('maintenanceRequest', function ($mrQuery) use ($searchTerm) {
                        $mrQuery->where('customer_name', 'LIKE', "%{$searchTerm}%");
                    })
                    // Tìm theo tên công ty hoặc tên khách hàng từ bảng customers (qua maintenanceRequest.customer)
                    ->orWhereHas('maintenanceRequest.customer', function ($customerQuery) use ($searchTerm) {
                        $customerQuery->where('company_name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('name', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply repair type filter
        if ($request->filled('repair_type')) {
            $query->where('repair_type', $request->repair_type);
        }

        // Apply warehouse filter
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $dateFrom = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_from)->format('Y-m-d');
            $query->whereDate('repair_date', '>=', $dateFrom);
        }
        if ($request->filled('date_to')) {
            $dateTo = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_to)->format('Y-m-d');
            $query->whereDate('repair_date', '<=', $dateTo);
        }

        $repairs = $query->orderBy('repair_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Giữ flash message khi quay lại từ redirect (nếu có)
        $success = session('success');
        $error = session('error');

        return view('warranties.repair_list', compact('repairs'))
            ->with('success', $success)
            ->with('error', $error);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('warranties.repair');
    }

    /**
     * Private helper: Find warranty by code, serial number, or phone number (shared logic)
     */
    private function findWarrantyByCodeOrSerial($warrantyCode)
    {
        $input = trim($warrantyCode);
        $normalizedSerial = strtoupper(preg_replace('/[\s-]+/', '', $input));

        // Kiểm tra xem input có phải là số điện thoại không (chỉ chứa số và có độ dài 10-11 ký tự)
        $isPhoneNumber = preg_match('/^[0-9]{10,11}$/', $input);

        Log::info('findWarrantyByCodeOrSerial called', [
            'input' => $input,
            'isPhoneNumber' => $isPhoneNumber
        ]);

        // Nếu là số điện thoại, tìm theo customer phone
        if ($isPhoneNumber) {
            // Tìm customer có số điện thoại này
            $customers = \App\Models\Customer::where('phone', $input)->pluck('id');

            if ($customers->isNotEmpty()) {
                // Tìm warranties thông qua dispatch -> project -> customer
                $warranties = Warranty::whereHas('dispatch.project', function ($q) use ($customers) {
                    $q->whereIn('customer_id', $customers);
                })
                    ->orWhereHas('dispatch.rental', function ($q) use ($customers) {
                        $q->whereIn('customer_id', $customers);
                    })
                    ->with(['dispatch.items.product', 'dispatch.items.good', 'dispatch.project.dispatches.items.product', 'dispatch.project.dispatches.items.good'])
                    ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END") // Ưu tiên active
                    ->orderBy('created_at', 'desc') // Mới nhất
                    ->get();

                Log::info('Search by phone number via customers', [
                    'phone' => $input,
                    'customer_ids' => $customers->toArray(),
                    'found_count' => $warranties->count(),
                    'warranties' => $warranties->map(function ($w) {
                        return [
                            'id' => $w->id,
                            'warranty_code' => $w->warranty_code,
                            'customer_name' => $w->customer_name,
                            'status' => $w->status
                        ];
                    })
                ]);

                if ($warranties->isNotEmpty()) {
                    // Trả về warranty đầu tiên (active và mới nhất)
                    return $warranties->first();
                }
            }

            // Fallback: Tìm trực tiếp trong bảng warranties (nếu có customer_phone)
            $warranties = Warranty::where('customer_phone', $input)
                ->with(['dispatch.items.product', 'dispatch.items.good', 'dispatch.project.dispatches.items.product', 'dispatch.project.dispatches.items.good'])
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->orderBy('created_at', 'desc')
                ->get();

            if ($warranties->isNotEmpty()) {
                return $warranties->first();
            }
        }

        // Tìm bảo hành theo mã bảo hành hoặc serial (không filter status để tìm được cả warranty đã hết hạn)
        $warranty = Warranty::where(function ($q) use ($input, $normalizedSerial) {
            $q->where('warranty_code', $input)
                ->orWhere(function ($qq) use ($normalizedSerial) {
                    $qq->whereNotNull('serial_number')
                        ->whereRaw('UPPER(REPLACE(REPLACE(serial_number, " ", ""), "-", "")) = ?', [$normalizedSerial]);
                })
                ->orWhereHas('dispatch.items', function ($qi) use ($input, $normalizedSerial) {
                    $qi->whereIn('item_type', ['product', 'good'])
                        ->where(function ($qj) use ($input, $normalizedSerial) {
                            $qj->whereJsonContains('serial_numbers', $input)
                                ->orWhereRaw('JSON_SEARCH(serial_numbers, "one", ?) IS NOT NULL', [$input])
                                ->orWhereRaw('JSON_SEARCH(serial_numbers, "one", ?) IS NOT NULL', [$normalizedSerial]);
                        });
                })
                ->orWhereHas('dispatch.project.dispatches.items', function ($qi) use ($input, $normalizedSerial) {
                    $qi->whereIn('item_type', ['product', 'good'])
                        ->where(function ($qj) use ($input, $normalizedSerial) {
                            $qj->whereJsonContains('serial_numbers', $input)
                                ->orWhereRaw('JSON_SEARCH(serial_numbers, "one", ?) IS NOT NULL', [$input])
                                ->orWhereRaw('JSON_SEARCH(serial_numbers, "one", ?) IS NOT NULL', [$normalizedSerial]);
                        });
                });
        })
            ->with(['dispatch.items.product', 'dispatch.items.good', 'dispatch.project.dispatches.items.product', 'dispatch.project.dispatches.items.good'])
            ->first();

        // Nếu không tìm thấy warranty trực tiếp, thử tìm trong tất cả warranty có project
        if (!$warranty) {
            $allProjectWarranties = Warranty::where('item_type', 'project')
                ->with(['dispatch.project', 'dispatch.items.product', 'dispatch.items.good', 'dispatch.project.dispatches.items.product', 'dispatch.project.dispatches.items.good'])
                ->get();

            foreach ($allProjectWarranties as $projectWarranty) {
                $projectItems = $projectWarranty->project_items ?? [];
                foreach ($projectItems as $item) {
                    $itemSerials = $item['serial_numbers'] ?? [];
                    foreach ($itemSerials as $serial) {
                        $normalizedItemSerial = strtoupper(preg_replace('/[\s-]+/', '', $serial));
                        if ($normalizedItemSerial === $normalizedSerial) {
                            $warranty = $projectWarranty;
                            break 3;
                        }
                    }
                }
            }
        }

        return $warranty;
    }

    /**
     * API: Search warranty by code or serial number (new format for API)
     */
    public function searchWarrantyApi(Request $request)
    {
        $warrantyCode = $request->get('warranty_code');

        if (!$warrantyCode) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã bảo hành hoặc số điện thoại.'
            ], 400);
        }

        try {
            $warranty = $this->findWarrantyByCodeOrSerial($warrantyCode);

            if (!$warranty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin bảo hành với mã: ' . $warrantyCode
                ], 404);
            }

            // Lấy devices (copy logic từ searchWarranty nhưng filter chỉ contract)
            $input = trim($warrantyCode);
            $normalizedSerial = strtoupper(preg_replace('/[\s-]+/', '', $input));

            $devices = [];
            $projectItems = $warranty->project_items ?? [];

            $indexed = [];
            foreach ($projectItems as $pi) {
                if (empty($pi['code']) || empty($pi['name']) || empty($pi['type'])) {
                    continue;
                }
                if (!in_array($pi['type'], ['product', 'good'])) {
                    continue;
                }
                $code = $pi['code'];
                $name = $pi['name'];
                $quantity = (int) ($pi['quantity'] ?? 1);
                $serialNumbers = is_array($pi['serial_numbers'] ?? null) ? $pi['serial_numbers'] : [];
                $dispatchItemId = $pi['dispatch_item_id'] ?? null;

                $uniqueKey = $code . '_' . ($dispatchItemId ?? 'no_dispatch_item');

                if (isset($indexed[$uniqueKey])) {
                    $mergedSerials = array_unique(array_merge($indexed[$uniqueKey]['serial_numbers'], $serialNumbers));
                    $indexed[$uniqueKey]['quantity'] += $quantity;
                    $indexed[$uniqueKey]['serial_numbers'] = $mergedSerials;
                    $indexed[$uniqueKey]['serial_numbers_text'] = !empty($mergedSerials) ? implode(', ', $mergedSerials) : 'N/A';
                } else {
                    $indexed[$uniqueKey] = [
                        'id' => $code . '_' . ($dispatchItemId ?? 'no_dispatch_item') . '_' . microtime(true) . '_' . uniqid(),
                        'code' => $code,
                        'name' => $name,
                        'quantity' => $quantity,
                        'serial' => $serialNumbers[0] ?? '',
                        'serial_numbers' => $serialNumbers,
                        'serial_numbers_text' => !empty($serialNumbers) ? implode(', ', $serialNumbers) : 'N/A',
                        'status' => 'active',
                        'type' => $pi['type'],
                        'source' => 'contract',
                        'dispatch_item_id' => $dispatchItemId,
                    ];
                }
            }

            // Bổ sung serial từ phiếu gốc
            if ($warranty->dispatch) {
                $dispatch = $warranty->dispatch;
                $items = $dispatch->items()
                    ->whereIn('item_type', ['product', 'good'])
                    ->where('category', 'contract') // API: Chỉ lấy contract
                    ->with(['product', 'good'])
                    ->get();
                foreach ($items as $it) {
                    $code = $it->product->code ?? $it->good->code ?? '';
                    if (!$code || !isset($indexed[$code])) {
                        continue;
                    }
                    $serialNumbers = $it->serial_numbers ?: [];
                    if (!empty($serialNumbers)) {
                        $merged = array_unique(array_merge($indexed[$code]['serial_numbers'], $serialNumbers));
                        $indexed[$code]['serial_numbers'] = $merged;
                        $indexed[$code]['serial_numbers_text'] = implode(', ', $merged);
                    }
                }
            }

            $devices = array_values($indexed);

            // Add good to devices if warranty is for a good
            if ($warranty->item_type === 'good' && $warranty->item) {
                $good = $warranty->item;
                $devices[] = [
                    'id' => 'good_' . $good->code . '_' . ($warranty->serial_number ?: '') . '_' . microtime(true) . '_' . uniqid(),
                    'code' => $good->code,
                    'name' => $good->name,
                    'quantity' => 1,
                    'serial' => $warranty->serial_number ?: '',
                    'serial_numbers' => $warranty->serial_number ? [$warranty->serial_number] : [],
                    'serial_numbers_text' => $warranty->serial_number ? $warranty->serial_number : 'N/A',
                    'status' => 'active',
                    'type' => 'good',
                    'source' => 'contract',
                ];
            }

            // Tính toán thời gian bảo hành
            $warrantyActivationTime = $warranty->activated_at ? $warranty->activated_at->format('Y-m-d H:i:s') : null;

            // Tính ngày kết thúc: nếu có activated_at thì dùng activated_at + warranty_period_months, ngược lại dùng warranty_end_date
            $warrantyEndTime = null;
            if ($warranty->activated_at && $warranty->warranty_period_months) {
                $warrantyEndTime = $warranty->activated_at->copy()->addMonths($warranty->warranty_period_months)->format('Y-m-d');
            } elseif ($warranty->warranty_end_date) {
                $warrantyEndTime = $warranty->warranty_end_date->format('Y-m-d');
            }

            // Format project_name: "PRJ-CODE - Project Name (Customer Name)"
            $projectNameFormatted = $warranty->project_name;
            if ($warranty->dispatch && $warranty->dispatch->project) {
                $project = $warranty->dispatch->project;
                $customerName = $warranty->customer_name ?? '';
                $projectNameFormatted = $project->project_code . ' - ' . $project->project_name;
                if ($customerName) {
                    $projectNameFormatted .= ' (' . $customerName . ')';
                }
            }

            // Gộp devices theo code để tính tổng quantity và serial_numbers
            $groupedDevices = [];
            foreach ($devices as $device) {
                $code = $device['code'];
                $type = $device['type'];
                $key = $code . '_' . $type;

                if (isset($groupedDevices[$key])) {
                    // Gộp quantity và serial_numbers
                    $groupedDevices[$key]['quantity'] += $device['quantity'];
                    $groupedDevices[$key]['serial_numbers'] = array_unique(
                        array_merge($groupedDevices[$key]['serial_numbers'], $device['serial_numbers'])
                    );
                } else {
                    $groupedDevices[$key] = [
                        'code' => $code,
                        'name' => $device['name'],
                        'quantity' => $device['quantity'],
                        'serial_numbers' => $device['serial_numbers'],
                        'type' => $type,
                    ];
                }
            }

            // Format devices cho API
            $formattedDevices = array_values($groupedDevices);

            return response()->json([
                'success' => true,
                'warranty' => [
                    'warranty_code' => $warranty->warranty_code,
                    'project_name' => $projectNameFormatted,
                    'status' => $warranty->status,
                    'activated_at' => $warrantyActivationTime,
                    'warranty_end_date' => $warrantyEndTime,
                    'devices' => $formattedDevices
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching warranty API: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tìm kiếm bảo hành: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Web: Search warranty by code or serial number (for repair creation page)
     */
    public function searchWarranty(Request $request)
    {
        $warrantyCode = $request->get('warranty_code');

        if (!$warrantyCode) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã bảo hành hoặc serial number'
            ]);
        }

        try {
            // Sử dụng hàm helper để tìm warranty (cho phép tìm cả warranty đã hết hạn)
            $warranty = $this->findWarrantyByCodeOrSerial($warrantyCode);

            if (!$warranty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin bảo hành với mã: ' . $warrantyCode
                ]);
            }

            // Nguồn dữ liệu CHÍNH: tổng hợp từ tất cả phiếu xuất của dự án (đã loại trừ backup)
            $devices = [];
            $projectItems = $warranty->project_items ?? [];

            Log::info('Debug searchWarranty', [
                'warranty_code' => $warrantyCode,
                'warranty_id' => $warranty->id,
                'project_items_count' => count($projectItems),
                'project_items' => $projectItems
            ]);

            $indexed = [];
            foreach ($projectItems as $index => $pi) {
                Log::info("Processing project item $index", [
                    'pi' => $pi,
                    'code_empty' => empty($pi['code']),
                    'name_empty' => empty($pi['name']),
                    'type_empty' => empty($pi['type']),
                    'type_value' => $pi['type'] ?? 'null'
                ]);

                if (empty($pi['code']) || empty($pi['name']) || empty($pi['type'])) {
                    Log::info("Skipping item $index due to empty fields");
                    continue;
                }
                if (!in_array($pi['type'], ['product', 'good'])) {
                    Log::info("Skipping item $index due to invalid type: " . $pi['type']);
                    continue;
                }
                $code = $pi['code'];
                $name = $pi['name'];
                $quantity = (int) ($pi['quantity'] ?? 1);
                $serialNumbers = is_array($pi['serial_numbers'] ?? null) ? $pi['serial_numbers'] : [];
                $dispatchItemId = $pi['dispatch_item_id'] ?? null;
                $dispatchId = $pi['dispatch_id'] ?? null;

                // Tạo key duy nhất bao gồm dispatch_item_id để phân biệt các sản phẩm từ phiếu xuất khác nhau
                $uniqueKey = $code . '_' . ($dispatchItemId ?? 'no_dispatch_item');

                if (isset($indexed[$uniqueKey])) {
                    // Gộp số lượng và serial từ cùng một dispatch item
                    $mergedSerials = array_unique(array_merge($indexed[$uniqueKey]['serial_numbers'], $serialNumbers));
                    $indexed[$uniqueKey]['quantity'] += $quantity; // cộng dồn số lượng
                    $indexed[$uniqueKey]['serial_numbers'] = $mergedSerials;
                    $indexed[$uniqueKey]['serial_numbers_text'] = !empty($mergedSerials) ? implode(', ', $mergedSerials) : 'N/A';
                } else {
                    $indexed[$uniqueKey] = [
                        'id' => $code . '_' . ($dispatchItemId ?? 'no_dispatch_item') . '_' . microtime(true) . '_' . uniqid(),
                        'code' => $code,
                        'name' => $name,
                        'quantity' => $quantity,
                        'serial' => $serialNumbers[0] ?? '',
                        'serial_numbers' => $serialNumbers,
                        'serial_numbers_text' => !empty($serialNumbers) ? implode(', ', $serialNumbers) : 'N/A',
                        'status' => 'active',
                        'type' => $pi['type'],
                        'source' => 'contract', // Đánh dấu nguồn từ hợp đồng
                        'dispatch_item_id' => $dispatchItemId, // Lưu dispatch_item_id để sử dụng sau này
                        'dispatch_id' => $dispatchId, // Lưu dispatch_id để debug
                    ];
                }
            }

            // Bổ sung serial từ phiếu gốc (nếu có) nhưng KHÔNG thêm mã mới ngoài danh sách tổng hợp
            if ($warranty->dispatch) {
                $dispatch = $warranty->dispatch;
                $items = $dispatch->items()
                    // Bao gồm cả thiết bị dự phòng/backup theo yêu cầu mới
                    ->whereIn('item_type', ['product', 'good'])
                    ->whereIn('category', ['contract', 'backup'])
                    ->with(['product', 'good'])
                    ->get();
                foreach ($items as $it) {
                    $code = $it->product->code ?? $it->good->code ?? '';
                    if (!$code) {
                        continue;
                    }

                    $category = $it->category;
                    $serialNumbers = $it->serial_numbers ?: [];

                    if (!isset($indexed[$code])) {
                        // KHÔNG thêm mã mới từ phiếu gốc nếu không tồn tại trong project_items của warranty
                        // Yêu cầu mới: chỉ sử dụng thiết bị thuộc trực tiếp bảo hành hiện tại
                        continue;
                    }
                    // Cập nhật thông tin cho thiết bị đã có
                    if (!empty($serialNumbers)) {
                        $merged = array_unique(array_merge($indexed[$code]['serial_numbers'], $serialNumbers));
                        $indexed[$code]['serial_numbers'] = $merged;
                        $indexed[$code]['serial_numbers_text'] = implode(', ', $merged);
                    }
                    // Cập nhật source nếu có thiết bị dự phòng
                    if ($category === 'backup') {
                        $indexed[$code]['source'] = 'mixed'; // Có cả contract và backup
                    }
                }
            }

            // Loại bỏ việc bổ sung từ các dispatch khác của dự án để CHỈ lấy thiết bị thuộc warranty hiện tại

            // Danh sách mã hợp lệ chỉ theo warranty hiện tại (chỉ lấy theo code sản phẩm)
            $allowedCodes = array_values(array_unique(array_map(function ($item) {
                return $item['code'] ?? '';
            }, array_values($indexed))));
            $devices = array_values($indexed);

            // Tách thành 1 hàng/1 serial thiết bị (nếu có danh sách serial) và thêm N/A nếu thiếu
            $expandedDevices = [];
            Log::info('Debug devices before expansion', [
                'devices_count' => count($devices),
                'devices' => $devices
            ]);

            foreach ($devices as $d) {
                $serials = is_array($d['serial_numbers'] ?? null) ? $d['serial_numbers'] : [];
                $serialCount = count($serials);
                // Push all existing serial rows
                foreach ($serials as $sn) {
                    $expandedDevices[] = [
                        'id' => $d['code'] . '_' . $sn . '_' . microtime(true) . '_' . uniqid(),
                        'code' => $d['code'],
                        'name' => $d['name'],
                        'quantity' => 1,
                        'serial' => $sn,
                        'serial_numbers' => [$sn],
                        'serial_numbers_text' => $sn,
                        'status' => $d['status'] ?? 'active',
                        'type' => $d['type'] ?? 'product',
                        'source' => $d['source'] ?? 'contract',
                    ];
                }
                // Add N/A rows to make up total quantity if quantity > serials
                $missing = max(0, ((int) ($d['quantity'] ?? 0)) - $serialCount);
                for ($i = 0; $i < $missing; $i++) {
                    $expandedDevices[] = [
                        'id' => $d['code'] . '_NA_' . ($d['dispatch_item_id'] ?? 'no_dispatch_item') . '_' . $i . '_' . microtime(true) . '_' . uniqid(),
                        'code' => $d['code'],
                        'name' => $d['name'],
                        'quantity' => 1,
                        'serial' => '',
                        'serial_numbers' => [],
                        'serial_numbers_text' => 'N/A',
                        'status' => $d['status'] ?? 'active',
                        'type' => $d['type'] ?? 'product',
                        'source' => $d['source'] ?? 'contract',
                        'dispatch_item_id' => $d['dispatch_item_id'] ?? null, // Truyền dispatch_item_id
                        'dispatch_id' => $d['dispatch_id'] ?? null, // Truyền dispatch_id để debug
                    ];
                }
            }
            // Chỉ giữ các thiết bị có code thuộc warranty hiện tại
            Log::info('Debug before filtering', [
                'expanded_devices_count' => count($expandedDevices),
                'allowed_codes' => $allowedCodes,
                'expanded_devices' => $expandedDevices
            ]);

            $devices = array_values(array_filter($expandedDevices, function ($d) use ($allowedCodes) {
                return in_array($d['code'] ?? '', $allowedCodes, true);
            }));

            // Web version: Không filter devices (giữ cả backup và mixed để hiển thị đầy đủ)

            // Nếu input là serial (khác mã bảo hành), lọc chỉ còn thiết bị chứa đúng serial đó
            $input = trim($warrantyCode);
            $normalizedSerial = strtoupper(preg_replace('/[\s-]+/', '', $input));
            $isSerialSearch = strcasecmp($input, $warranty->warranty_code) !== 0;

            if ($isSerialSearch && !empty($normalizedSerial)) {
                $devices = array_values(array_filter($devices, function ($d) use ($normalizedSerial) {
                    $serials = $d['serial_numbers'] ?? [];
                    foreach ($serials as $s) {
                        $ns = strtoupper(preg_replace('/[\s-]+/', '', $s));
                        if ($ns === $normalizedSerial)
                            return true;
                    }
                    if (!empty($d['serial'])) {
                        $ns2 = strtoupper(preg_replace('/[\s-]+/', '', $d['serial']));
                        if ($ns2 === $normalizedSerial)
                            return true;
                    }
                    return false;
                }));
            }

            // Add good to devices if warranty is for a good
            if ($warranty->item_type === 'good' && $warranty->item) {
                $good = $warranty->item;
                $devices[] = [
                    'id' => 'good_' . $good->code . '_' . ($warranty->serial_number ?: '') . '_' . microtime(true) . '_' . uniqid(),
                    'code' => $good->code,
                    'name' => $good->name,
                    'quantity' => 1,
                    'serial' => $warranty->serial_number ?: '',
                    'serial_numbers' => $warranty->serial_number ? [$warranty->serial_number] : [],
                    'serial_numbers_text' => $warranty->serial_number ? $warranty->serial_number : 'N/A',
                    'status' => 'active',
                    'type' => 'good', // Add type field to identify as a good
                    'source' => 'contract', // Bảo hành đơn lẻ thuộc contract
                ];
            }

            // Lấy lịch sử sửa chữa (cho web version)
            $repairHistory = [];
            $existingRepairs = Repair::where('warranty_code', $warranty->warranty_code)
                ->with(['technician', 'warehouse'])
                ->orderBy('repair_date', 'desc')
                ->get();

            foreach ($existingRepairs as $repair) {
                $repairHistory[] = [
                    'date' => $repair->repair_date->format('d/m/Y'),
                    'type' => $this->getRepairTypeLabel($repair->repair_type),
                    'description' => $repair->repair_description,
                    'technician' => $repair->technician->name ?? 'N/A'
                ];
            }

            Log::info('Debug searchWarranty result', [
                'warranty_code' => $warrantyCode,
                'devices_count' => count($devices),
                'devices' => $devices
            ]);

            return response()->json([
                'success' => true,
                'warranty' => [
                    'warranty_code' => $warranty->warranty_code,
                    'customer_name' => $warranty->customer_name,
                    'devices' => $devices,
                    'repair_history' => $repairHistory
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching warranty: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tìm kiếm bảo hành: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Get repair history by warranty code or serial
     */
    public function getRepairHistory(Request $request)
    {
        $inputCode = $request->get('warranty_code') ?: $request->get('serial');

        if (!$inputCode) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã bảo hành hoặc serial number'
            ]);
        }

        try {
            $warranty = $this->findWarrantyByCodeOrSerial($inputCode);

            if (!$warranty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin bảo hành với mã: ' . $inputCode
                ]);
            }

            $repairs = Repair::where('warranty_code', $warranty->warranty_code)
                ->with(['technician', 'warehouse'])
                ->orderBy('repair_date', 'desc')
                ->get();

            $repairHistory = $repairs->map(function ($repair) {
                return [
                    'date' => $repair->repair_date ? $repair->repair_date->format('d/m/Y') : null,
                    'repair_date' => $repair->repair_date ? $repair->repair_date->format('Y-m-d') : null,
                    'type' => $this->getRepairTypeLabel($repair->repair_type),
                    'repair_type' => $repair->repair_type,
                    'description' => $repair->repair_description,
                    'technician' => $repair->technician->name ?? 'N/A',
                    'warehouse' => $repair->warehouse->name ?? null,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'warranty_code' => $warranty->warranty_code,
                'repair_history' => $repairHistory,
            ]);
        } catch (\Exception $e) {
            Log::error('API get repair history error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy lịch sử sửa chữa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get device materials for repair
     */
    public function getDeviceMaterials(Request $request)
    {
        $deviceId = $request->get('deviceId') ?: $request->get('device_id');
        $warrantyCode = $request->get('warranty_code');
        $deviceCode = $request->get('device_code'); // Thêm device_code để hỗ trợ thiết bị từ kho
        $dispatchItemId = $request->get('dispatch_item_id'); // Thêm dispatch_item_id để phân biệt sản phẩm cùng serial N/A

        if (!$deviceId && !$deviceCode) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu thông tin deviceId hoặc device_code'
            ]);
        }

        try {
            // Check if this is a good (starts with good_)
            if (strpos($deviceId, 'good_') === 0) {
                // Parse good code from device_id (format: good_CODE_serial_timestamp_random)
                $parts = explode('_', $deviceId);
                $goodCode = $parts[1] ?? '';
                $deviceSerial = $parts[2] ?? '';

                // Find good by code
                $good = Good::where('code', $goodCode)->first();

                if (!$good) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không tìm thấy thông tin hàng hóa'
                    ]);
                }

                // For goods, we don't have component materials like products
                // Just return the good itself as a material for repair purposes
                $materials = [
                    [
                        'id' => $good->id,
                        'code' => $good->code,
                        'name' => $good->name,
                        'quantity' => 1,
                        'serial' => $deviceSerial,
                        'current_serials' => [$deviceSerial],
                        'status' => 'active',
                        'is_good' => true,
                        'unit' => 'cái'
                    ]
                ];

                return response()->json([
                    'success' => true,
                    'materials' => $materials
                ]);
            }

            // Check if this is a warehouse device (starts with warehouse_product_ or warehouse_good_)
            if (strpos($deviceId, 'warehouse_product_') === 0 || strpos($deviceId, 'warehouse_good_') === 0) {
                // Parse warehouse device info
                $parts = explode('_', $deviceId);
                $deviceType = $parts[1] ?? ''; // product or good
                $deviceIdInDb = $parts[2] ?? '';
                $warehouseId = $parts[3] ?? '';
                $deviceSerial = $parts[4] ?? '';

                if ($deviceType === 'product') {
                    // Find product by code (deviceIdInDb is actually the product code)
                    $product = Product::where('code', $deviceIdInDb)->first();
                    if (!$product) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Không tìm thấy thông tin sản phẩm'
                        ]);
                    }

                    // Kiểm tra xem có serial cụ thể không
                    if (!empty($deviceSerial)) {
                        // Nếu có serial cụ thể, tìm vật tư theo serial này
                        $materials = $this->getDeviceMaterialsBySerial($deviceIdInDb, $deviceSerial, $warrantyCode);
                        // Fallback: nếu chưa có serial vật tư, thử lấy từ bất kỳ warranty nào có cùng product+serial
                        if (empty($materials)) {
                            $materials = $this->getMaterialsFromAnyWarrantyBySerial($deviceIdInDb, $deviceSerial);
                        }
                        // Áp serial từ lịch sử thay thế ưu tiên theo warranty hiện tại; nếu không có, áp theo mọi warranty
                        if (!empty($materials)) {
                            if (!empty($warrantyCode)) {
                                $materials = $this->updateMaterialsSerialsFromHistory($materials, $deviceIdInDb, $warrantyCode);
                            } else {
                                $materials = $this->updateMaterialsSerialsFromAnyWarranty($materials, $deviceIdInDb);
                            }
                        }
                        // Cuối cùng: nếu vẫn trống, trả về vật tư lắp ráp nên thành phẩm
                        if (empty($materials)) {
                            $materials = $this->getDeviceMaterialsFromAssembly($product);
                        }
                    } else {
                        // Nếu không có serial, chỉ trả vật tư lắp ráp mặc định cho sản phẩm
                        $materials = $this->getDeviceMaterialsFromAssembly($product);
                        // Không áp dụng lịch sử thay thế khi thành phẩm không có serial
                    }
                } else {
                    // Handle warehouse good
                    $good = Good::where('code', $deviceIdInDb)->first();
                    if (!$good) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Không tìm thấy thông tin hàng hóa'
                        ]);
                    }

                    $materials = [
                        [
                            'id' => $good->id,
                            'code' => $good->code,
                            'name' => $good->name,
                            'quantity' => 1,
                            'serial' => $deviceSerial,
                            'current_serials' => [$deviceSerial],
                            'status' => 'active',
                            'is_good' => true,
                            'unit' => 'cái'
                        ]
                    ];
                }
            } else {
                // Regular product handling (existing code) - for warranty devices
                // Parse device code/serial from various possible formats
                $deviceCode = $deviceCode ?: (explode('_', $deviceId)[0] ?? $deviceId);
                $deviceSerial = $request->get('device_serial', '');

                if (empty($deviceSerial)) {
                    // Try multiple delimiters in order
                    $candidates = [
                        function ($id) {
                            $p = explode('_', $id);
                            return $p[1] ?? '';
                        },
                        function ($id) {
                            $p = explode('|', $id);
                            return $p[1] ?? '';
                        },
                        function ($id) {
                            $p = explode(':', $id);
                            return $p[1] ?? '';
                        },
                        function ($id) {
                            $p = explode('#', $id);
                            return $p[1] ?? '';
                        },
                        function ($id) {
                            $p = explode(' ', $id);
                            return $p[1] ?? '';
                        },
                    ];
                    foreach ($candidates as $resolver) {
                        $candidate = trim((string) $resolver($deviceId));
                        if (!empty($candidate)) {
                            $deviceSerial = $candidate;
                            break;
                        }
                    }
                }

                // Tìm product theo code
                $product = Product::where('code', $deviceCode)->first();

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không tìm thấy thông tin sản phẩm'
                    ]);
                }

                // Kiểm tra xem có serial cụ thể không (loại trừ N/A)
                $normalizedSerial = strtoupper(trim($deviceSerial ?? ''));
                if (!empty($deviceSerial) && $normalizedSerial !== 'N/A' && $normalizedSerial !== 'NA') {
                    // Nếu có serial cụ thể, tìm vật tư theo serial này
                    $materials = $this->getDeviceMaterialsBySerial($deviceCode, $deviceSerial, $warrantyCode);
                    if (!empty($materials)) {
                        if (!empty($warrantyCode)) {
                            $materials = $this->updateMaterialsSerialsFromHistory($materials, $deviceCode, $warrantyCode);
                        } else {
                            $materials = $this->updateMaterialsSerialsFromAnyWarranty($materials, $deviceCode);
                        }
                    }
                } else {
                    // Nếu không có serial hoặc serial là N/A, tìm assembly_id và product_unit cho N/A
                    Log::info('Processing N/A serial for product', [
                        'product_id' => $product->id,
                        'product_code' => $product->code,
                        'device_serial' => $deviceSerial
                    ]);

                    // Thử lấy assembly_id và product_unit từ dispatch item trước
                    $dispatchInfo = $this->getAssemblyInfoFromDispatch($product->id, $warrantyCode, $dispatchItemId);
                    if ($dispatchInfo) {
                        Log::info('Found assembly info from dispatch', [
                            'product' => $product->code,
                            'assembly_id' => $dispatchInfo['assembly_id'],
                            'product_unit' => $dispatchInfo['product_unit'],
                            'dispatch_item_id' => $dispatchItemId
                        ]);
                        $materials = $this->getDeviceMaterialsFromAssembly(
                            $product,
                            (int) $dispatchInfo['assembly_id'],
                            isset($dispatchInfo['product_unit']) && is_numeric($dispatchInfo['product_unit']) ? (int) $dispatchInfo['product_unit'] : null
                        );
                    } else {
                        // Fallback: tìm đơn vị thành phẩm tiếp theo
                        $unitInfo = $this->getNextAvailableProductUnit($product->id, $warrantyCode);
                        if ($unitInfo) {
                            Log::info('Found available product unit', [
                                'product' => $product->code,
                                'assembly_id' => $unitInfo['assembly_id'],
                                'product_unit' => $unitInfo['product_unit']
                            ]);
                            $materials = $this->getDeviceMaterialsFromAssembly(
                                $product,
                                (int) $unitInfo['assembly_id'],
                                isset($unitInfo['product_unit']) && is_numeric($unitInfo['product_unit']) ? (int) $unitInfo['product_unit'] : null
                            );
                        } else {
                            // Fallback to old logic
                            $assemblyId = $this->findAssemblyIdForProductSerial($product->id, 'N/A', $warrantyCode);
                            Log::info('Using fallback assembly for N/A serial', [
                                'product' => $product->code,
                                'assembly_id' => $assemblyId
                            ]);
                            if ($assemblyId) {
                                $materials = $this->getDeviceMaterialsFromAssembly($product, $assemblyId);
                            } else {
                                $materials = $this->getDeviceMaterialsFromAssembly($product);
                            }
                        }
                    }
                    // Không áp dụng lịch sử thay thế khi thành phẩm không có serial
                }
            }

            // Debug: log final materials payload returned to frontend
            try {
                Log::info('getDeviceMaterials result', [
                    'device' => $deviceIdInDb ?? $deviceCode ?? null,
                    'serial' => $deviceSerial ?? null,
                    'warranty' => $warrantyCode ?? null,
                    'materials' => $materials
                ]);
            } catch (\Exception $e) {
                // ignore
            }

            return response()->json([
                'success' => true,
                'materials' => $materials
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting device materials: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách vật tư'
            ]);
        }
    }

    /**
     * Get device materials from warranty and replacement history
     */
    private function getDeviceMaterialsFromWarranty($deviceCode, $deviceSerial, $warrantyCode = null)
    {
        $materials = [];

        if (!$warrantyCode) {
            return $materials;
        }

        try {
            // Find warranty
            $warranty = Warranty::where('warranty_code', $warrantyCode)->first();
            if (!$warranty) {
                return $materials;
            }

            // Get product materials from warranty
            $productMaterials = $warranty->product_materials ?? [];

            foreach ($productMaterials as $productMaterial) {
                // Find matching product and serial
                if ($productMaterial['product_code'] === $deviceCode) {
                    // Check if device serial matches (if specified)
                    if (!$deviceSerial || $productMaterial['serial_number'] === $deviceSerial || $productMaterial['serial_number'] === 'N/A') {

                        // Get materials for this product
                        foreach ($productMaterial['materials'] as $material) {
                            // Check for latest replacement
                            $currentSerial = $this->getLatestMaterialSerial(
                                $deviceCode,
                                $material['code'],
                                $material['serial'],
                                $warrantyCode
                            );

                            $materials[] = [
                                'id' => null, // Will be resolved by material code
                                'code' => $material['code'],
                                'name' => $material['name'],
                                'quantity' => $material['quantity'],
                                'serial' => $currentSerial,
                                'current_serials' => [$currentSerial],
                                'status' => 'active'
                            ];
                        }
                        break;
                    }
                }
            }
            return $materials;
        } catch (\Exception $e) {
            Log::error('Error getting materials from warranty: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get latest material serial after replacements
     */
    private function getLatestMaterialSerial($deviceCode, $materialCode, $originalSerial, $warrantyCode)
    {
        // Find material replacement history for this device and material
        $latestReplacement = MaterialReplacementHistory::whereHas('repair', function ($query) use ($warrantyCode) {
            $query->where('warranty_code', $warrantyCode);
        })
            ->where('device_code', $deviceCode)
            ->where('material_code', $materialCode)
            ->where(function ($query) use ($originalSerial) {
                $normalized = (trim($originalSerial) === '' || strtoupper(trim($originalSerial)) === 'N/A') ? 'N/A' : trim($originalSerial);
                $query->whereJsonContains('old_serials', $normalized)
                    ->orWhereRaw('JSON_SEARCH(old_serials, "one", ?) IS NOT NULL', [$normalized]);
            })
            ->orderBy('replaced_at', 'desc')
            ->first();

        if ($latestReplacement && !empty($latestReplacement->new_serials)) {
            // Find which new serial corresponds to the original serial
            $oldSerials = $latestReplacement->old_serials;
            $newSerials = $latestReplacement->new_serials;

            $normalized = (trim($originalSerial) === '' || strtoupper(trim($originalSerial)) === 'N/A') ? 'N/A' : trim($originalSerial);
            $index = array_search($normalized, $oldSerials, true);
            if ($index !== false && isset($newSerials[$index])) {
                return $newSerials[$index];
            }
        }

        return $originalSerial;
    }

    /**
     * Get device materials by specific serial
     */
    private function getDeviceMaterialsBySerial($productCode, $deviceSerial, $warrantyCode = null)
    {
        $materials = [];

        // Cache key để tránh query lặp lại trong cùng request
        $cacheKey = "device_materials_{$productCode}_{$deviceSerial}_{$warrantyCode}";
        if (isset($GLOBALS[$cacheKey])) {
            return $GLOBALS[$cacheKey];
        }

        try {
            // Tìm product theo code
            $product = Product::where('code', $productCode)->first();
            if (!$product) {
                Log::warning("Product not found: {$productCode}");
                return $materials;
            }

            // FAST PATH 1: Tìm theo TestingItem finished_product có serial_number (query nhanh với index)
            try {
                $finishedProductItem = \App\Models\TestingItem::where('item_type', 'finished_product')
                    ->where('serial_number', $deviceSerial)
                    ->orderByDesc('id')
                    ->first();
                if ($finishedProductItem) {
                    $siblingMaterialItems = \App\Models\TestingItem::where('testing_id', $finishedProductItem->testing_id)
                        ->where('item_type', 'material')
                        ->with('material')
                        ->get();

                    $byCode = [];
                    foreach ($siblingMaterialItems as $mi) {
                        if (!$mi->material) {
                            continue;
                        }
                        $code = $mi->material->code;
                        $name = $mi->material->name;
                        if (!isset($byCode[$code])) {
                            $byCode[$code] = [
                                'id' => $mi->material->id,
                                'code' => $code,
                                'name' => $name,
                                'quantity' => 0,
                                'serials' => [],
                            ];
                        }
                        $byCode[$code]['quantity'] += (int) ($mi->quantity ?? 1);
                        if (!empty($mi->serial_number)) {
                            $byCode[$code]['serials'][] = trim($mi->serial_number);
                        }
                    }

                    foreach ($byCode as $code => $entry) {
                        $serials = array_values(array_unique(array_filter($entry['serials'])));
                        $materials[] = [
                            'id' => $entry['id'],
                            'code' => $entry['code'],
                            'name' => $entry['name'],
                            'quantity' => $entry['quantity'] > 0 ? $entry['quantity'] : 1,
                            'serial' => implode(',', $serials),
                            'current_serials' => $serials,
                            'status' => 'active'
                        ];
                    }

                    if (!empty($materials)) {
                        $GLOBALS[$cacheKey] = $materials;
                        return $materials;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Finished product TestingItem lookup failed: ' . $e->getMessage());
            }

            // FAST PATH 2: Tìm theo AssemblyProduct với LIKE (nhanh hơn JSON_SEARCH)
            try {
                $ap = \App\Models\AssemblyProduct::where('product_id', $product->id)
                    ->where('serials', 'like', '%' . $deviceSerial . '%')
                    ->orderByDesc('id')
                    ->first();
                if ($ap) {
                    $assemblyMaterials = \App\Models\AssemblyMaterial::where('assembly_id', $ap->assembly_id)
                        ->where(function ($q) use ($product) {
                            $q->where('target_product_id', $product->id)
                                ->orWhereNull('target_product_id');
                        })
                        ->with(['material', 'serial'])
                        ->get();
                    foreach ($assemblyMaterials as $am) {
                        if ($am->material) {
                            $serials = [];
                            if (!empty($am->serial)) {
                                $serials = array_values(array_filter(array_map('trim', explode(',', (string) $am->serial))));
                            }
                            if (!empty($am->serial_id) && $am->serial && !in_array((string) $am->serial->serial_number, $serials, true)) {
                                $serials[] = (string) $am->serial->serial_number;
                            }
                            $materials[] = [
                                'id' => $am->material->id,
                                'code' => $am->material->code,
                                'name' => $am->material->name,
                                'quantity' => (int) ($am->quantity ?? 1),
                                'serial' => implode(',', $serials),
                                'current_serials' => $serials,
                                'status' => 'active'
                            ];
                        }
                    }
                    if (!empty($materials)) {
                        $GLOBALS[$cacheKey] = $materials;
                        return $materials;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Assembly mapping lookup failed: ' . $e->getMessage());
            }

            // SLOW PATH: Lấy serial vật tư theo lắp ráp/Testing với JSON_SEARCH (chỉ khi cần)
            try {
                // Sử dụng LIKE trước để filter nhanh, sau đó mới dùng JSON_SEARCH
                $testingItems = \App\Models\TestingItem::where('item_type', 'material')
                    ->whereHas('testing', function ($q) {
                        $q->whereIn('status', ['completed', 'approved', 'received']);
                    })
                    ->where('serial_results', 'like', '%' . $deviceSerial . '%')
                    ->with('material')
                    ->get();

                if ($testingItems->count() > 0) {
                    $materialsByCode = [];
                    foreach ($testingItems as $ti) {
                        $materialCode = $ti->material->code ?? '';
                        if ($materialCode === '') {
                            continue;
                        }
                        $serials = [];
                        $sr = $ti->serial_results;
                        if (!is_array($sr)) {
                            $sr = json_decode((string) $sr, true) ?: [];
                        }
                        // 1) Cấu trúc by_product_serial: { by_product_serial: { '<productSerial>': { '<materialCode>': [serial...] } } }
                        if (isset($sr['by_product_serial'][$deviceSerial][$materialCode]) && is_array($sr['by_product_serial'][$deviceSerial][$materialCode])) {
                            $serials = $sr['by_product_serial'][$deviceSerial][$materialCode];
                        }
                        // 2) Cấu trúc mappings: [{ product_serial: '...', materials: [{ code:'', serials:[]|serial:'' }] }]
                        if (empty($serials) && !empty($sr['mappings']) && is_array($sr['mappings'])) {
                            foreach ($sr['mappings'] as $map) {
                                if (($map['product_serial'] ?? null) === $deviceSerial && !empty($map['materials']) && is_array($map['materials'])) {
                                    foreach ($map['materials'] as $m) {
                                        if (($m['code'] ?? '') === $materialCode) {
                                            if (!empty($m['serials']) && is_array($m['serials'])) {
                                                $serials = $m['serials'];
                                            } elseif (!empty($m['serial']) && is_string($m['serial'])) {
                                                $serials = [$m['serial']];
                                            }
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                        // 3) Các khoá phẳng như trước (không ràng buộc theo code)
                        if (empty($serials)) {
                            $candidateKeys = ['material_serials', 'materialSerials', 'serials', 'material_serial', 'serial_list'];
                            foreach ($candidateKeys as $k) {
                                if (!empty($sr[$k])) {
                                    if (is_array($sr[$k])) {
                                        $serials = $sr[$k];
                                    } elseif (is_string($sr[$k])) {
                                        $serials = [$sr[$k]];
                                    }
                                    if (!empty($serials)) {
                                        break;
                                    }
                                }
                            }
                        }
                        // 4) Một số biến thể khác
                        if (empty($serials)) {
                            if (!empty($sr['serial']) && is_string($sr['serial'])) {
                                $serials = [$sr['serial']];
                            } elseif (!empty($sr['material']) && is_array($sr['material']) && !empty($sr['material']['serial'])) {
                                $serials = [(string) $sr['material']['serial']];
                            }
                        }

                        $serials = array_values(array_filter(array_map('trim', is_array($serials) ? $serials : [])));
                        if (!isset($materialsByCode[$materialCode])) {
                            $materialsByCode[$materialCode] = [
                                'id' => $ti->material->id ?? null,
                                'code' => $materialCode,
                                'name' => $ti->material->name ?? '',
                                'quantity' => (int) ($ti->quantity ?? 1),
                                'serial' => implode(',', $serials),
                                'current_serials' => $serials,
                                'status' => 'active'
                            ];
                        } else {
                            // Cộng dồn số lượng, hợp nhất serials
                            $materialsByCode[$materialCode]['quantity'] += (int) ($ti->quantity ?? 1);
                            $merged = array_values(array_unique(array_merge($materialsByCode[$materialCode]['current_serials'], $serials)));
                            $materialsByCode[$materialCode]['current_serials'] = $merged;
                            $materialsByCode[$materialCode]['serial'] = implode(',', $merged);
                        }
                    }
                    $materials = array_values($materialsByCode);
                    if (!empty($materials)) {
                        $GLOBALS[$cacheKey] = $materials;
                        return $materials; // Found exact mapping from assembly/testing
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Testing mapping lookup failed: ' . $e->getMessage());
            }

            // Tìm trong bảng serials để lấy vật tư theo serial cụ thể
            $serialRecord = \App\Models\Serial::where('serial_number', $deviceSerial)
                ->where('type', 'product')
                ->where('product_id', $product->id)
                ->first();

            if ($serialRecord) {

                // Tìm tất cả vật tư có serial cụ thể này
                $materialSerials = \App\Models\Serial::where('serial_number', $deviceSerial)
                    ->where('type', 'material')
                    ->with('material')
                    ->get();


                foreach ($materialSerials as $materialSerial) {
                    if ($materialSerial->material) {
                        $materials[] = [
                            'id' => $materialSerial->material->id,
                            'code' => $materialSerial->material->code,
                            'name' => $materialSerial->material->name,
                            'quantity' => 1, // Mỗi serial = 1 vật tư
                            'serial' => $materialSerial->serial_number,
                            'current_serials' => [$materialSerial->serial_number],
                            'status' => 'active'
                        ];
                    }
                }
                // Không trả về vật tư lắp ráp tại đây để cho phép fallback warranty lấy đúng serial
            } else {
                Log::warning("No serial record found for product {$productCode} with serial {$deviceSerial}");
            }

            // Fallback D: Lấy theo warranty hiện tại (product_materials + lịch sử thay thế của warranty)
            if (empty($materials) && !empty($warrantyCode)) {
                try {
                    $fromWarranty = $this->getDeviceMaterialsFromWarranty($productCode, $deviceSerial, $warrantyCode);
                    if (!empty($fromWarranty)) {
                        $materials = $this->updateMaterialsSerialsFromHistory($fromWarranty, $productCode, $warrantyCode);
                    }
                } catch (\Exception $e) {
                    Log::warning('Warranty-specific fallback failed: ' . $e->getMessage());
                }
            }

            // Fallback E: Nếu vẫn rỗng, trả về vật tư theo lắp ráp chung của sản phẩm
            if (empty($materials)) {
                $materials = $this->getDeviceMaterialsFromAssembly($product);
            }
        } catch (\Exception $e) {
            Log::error("Error getting materials for product {$productCode} with serial {$deviceSerial}: " . $e->getMessage());
        }

        // Cache kết quả
        $GLOBALS[$cacheKey] = $materials;
        return $materials;
    }

    /**
     * Fallback: Lấy vật tư (kèm serial) từ bất kỳ warranty nào có cùng product code + device serial
     * Optimized with caching and limited search scope
     */
    private function getMaterialsFromAnyWarrantyBySerial(string $productCode, string $deviceSerial): array
    {
        // Cache key để tránh query lặp lại  
        $cacheKey = "materials_any_warranty_{$productCode}_{$deviceSerial}";
        if (isset($GLOBALS[$cacheKey])) {
            return $GLOBALS[$cacheKey];
        }

        $materials = [];
        try {
            // Giới hạn tìm kiếm 50 warranty gần nhất để tối ưu performance
            // Thường vật tư sẽ nằm trong các warranty mới tạo
            $candidateWarranties = Warranty::where('status', 'active')
                ->whereIn('item_type', ['project', 'product'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            foreach ($candidateWarranties as $warranty) {
                // 1) Nếu có product_materials, ưu tiên tìm đúng product_code + serial_number ở đây
                $productMaterials = $warranty->product_materials ?? [];
                foreach ($productMaterials as $pm) {
                    $pmProductCode = $pm['product_code'] ?? '';
                    $pmSerial = $pm['serial_number'] ?? '';
                    if ($pmProductCode === $productCode && $pmSerial === $deviceSerial) {
                        foreach ($pm['materials'] as $m) {
                            $materials[] = [
                                'id' => null,
                                'code' => $m['code'],
                                'name' => $m['name'],
                                'quantity' => (int) ($m['quantity'] ?? 1),
                                'serial' => (string) ($m['serial'] ?? ''),
                                'current_serials' => array_filter([(string) ($m['serial'] ?? '')]),
                                'status' => 'active',
                            ];
                        }
                        $GLOBALS[$cacheKey] = $materials;
                        return $materials;
                    }
                }

                // 2) Nếu là project và có project_items, xác nhận serial thuộc product_code này
                $projectItems = $warranty->project_items ?? [];
                foreach ($projectItems as $pi) {
                    $piCode = $pi['code'] ?? '';
                    $piSerials = is_array($pi['serial_numbers'] ?? null) ? $pi['serial_numbers'] : [];
                    if ($piCode === $productCode && in_array($deviceSerial, $piSerials, true)) {
                        // Tìm lại trong product_materials cùng warranty theo code+serial
                        foreach ($productMaterials as $pm) {
                            if (($pm['product_code'] ?? '') === $productCode && ($pm['serial_number'] ?? '') === $deviceSerial) {
                                foreach ($pm['materials'] as $m) {
                                    $materials[] = [
                                        'id' => null,
                                        'code' => $m['code'],
                                        'name' => $m['name'],
                                        'quantity' => (int) ($m['quantity'] ?? 1),
                                        'serial' => (string) ($m['serial'] ?? ''),
                                        'current_serials' => array_filter([(string) ($m['serial'] ?? '')]),
                                        'status' => 'active',
                                    ];
                                }
                                $GLOBALS[$cacheKey] = $materials;
                                return $materials;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('getMaterialsFromAnyWarrantyBySerial failed: ' . $e->getMessage());
        }

        // Cache empty result để tránh query lại
        $GLOBALS[$cacheKey] = $materials;
        return $materials;
    }

    /**
     * Get assembly info from dispatch item for N/A product
     */
    private function getAssemblyInfoFromDispatch(int $productId, ?string $warrantyCode = null, ?int $dispatchItemId = null): ?array
    {
        if (!$warrantyCode) {
            return null;
        }

        // Tìm warranty để lấy dispatch_id
        $warranty = \App\Models\Warranty::where('warranty_code', $warrantyCode)->first();
        if (!$warranty) {
            return null;
        }

        // Ưu tiên cao nhất: Nếu có dispatchItemId từ request, lấy thông tin từ dispatch item cụ thể đó
        if ($dispatchItemId) {
            $dispatchItem = \App\Models\DispatchItem::where('id', $dispatchItemId)
                ->where('item_type', 'product')
                ->where('item_id', $productId)
                ->whereNotNull('assembly_id')
                ->whereNotNull('product_unit')
                ->first();

            if ($dispatchItem) {
                Log::info('Found assembly info from specific dispatch item (from request)', [
                    'warranty_code' => $warrantyCode,
                    'dispatch_item_id' => $dispatchItemId,
                    'assembly_id' => $dispatchItem->assembly_id,
                    'product_unit' => $dispatchItem->product_unit
                ]);
                return [
                    'assembly_id' => $dispatchItem->assembly_id,
                    'product_unit' => $dispatchItem->product_unit
                ];
            }
        }

        // Ưu tiên thứ hai: Nếu có dispatch_item_id trong warranty, lấy thông tin từ dispatch item cụ thể đó
        if ($warranty->dispatch_item_id) {
            $dispatchItem = \App\Models\DispatchItem::where('id', $warranty->dispatch_item_id)
                ->where('item_type', 'product')
                ->where('item_id', $productId)
                ->whereNotNull('assembly_id')
                ->whereNotNull('product_unit')
                ->first();

            if ($dispatchItem) {
                Log::info('Found assembly info from specific dispatch item (from warranty)', [
                    'warranty_code' => $warrantyCode,
                    'dispatch_item_id' => $warranty->dispatch_item_id,
                    'assembly_id' => $dispatchItem->assembly_id,
                    'product_unit' => $dispatchItem->product_unit
                ]);
                return [
                    'assembly_id' => $dispatchItem->assembly_id,
                    'product_unit' => $dispatchItem->product_unit
                ];
            }
        }

        // Fallback: Nếu không có dispatch_item_id hoặc không tìm thấy, tìm theo dispatch_id
        if ($warranty->dispatch_id) {
            $dispatchItem = \App\Models\DispatchItem::where('dispatch_id', $warranty->dispatch_id)
                ->where('item_type', 'product')
                ->where('item_id', $productId)
                ->whereNotNull('assembly_id')
                ->whereNotNull('product_unit')
                ->first();

            if ($dispatchItem) {
                Log::info('Found assembly info from dispatch fallback', [
                    'warranty_code' => $warrantyCode,
                    'dispatch_id' => $warranty->dispatch_id,
                    'assembly_id' => $dispatchItem->assembly_id,
                    'product_unit' => $dispatchItem->product_unit
                ]);
                return [
                    'assembly_id' => $dispatchItem->assembly_id,
                    'product_unit' => $dispatchItem->product_unit
                ];
            }
        }

        return null;
    }

    /**
     * Get next available product unit for N/A serial from assembly
     */
    private function getNextAvailableProductUnit(int $productId, ?string $warrantyCode = null): ?array
    {
        // Tìm assembly có thành phẩm này với serial N/A, ưu tiên theo project
        $query = \App\Models\AssemblyProduct::where('product_id', $productId)
            ->where(function ($q) {
                $q->whereNull('serials')
                    ->orWhere('serials', '')
                    ->orWhere('serials', 'N/A')
                    ->orWhere('serials', 'NA');
            });

        // Nếu có warranty context, ưu tiên assembly của project đó
        if ($warrantyCode) {
            $warranty = \App\Models\Warranty::where('warranty_code', $warrantyCode)->first();
            if ($warranty && $warranty->project_id) {
                $projectAssembly = $query->clone()
                    ->whereHas('assembly', function ($q) use ($warranty) {
                        $q->where('project_id', $warranty->project_id);
                    })
                    ->orderBy('id')
                    ->first();

                if ($projectAssembly) {
                    $availableUnit = $this->findAvailableUnitInAssembly($projectAssembly->assembly_id, $productId);
                    if ($availableUnit !== null) {
                        return [
                            'assembly_id' => $projectAssembly->assembly_id,
                            'product_unit' => $availableUnit
                        ];
                    }
                }
            }
        }

        // Fallback: tìm assembly khác theo thứ tự ID
        $assemblies = $query->orderBy('id')->get();
        foreach ($assemblies as $assembly) {
            $availableUnit = $this->findAvailableUnitInAssembly($assembly->assembly_id, $productId);
            if ($availableUnit !== null) {
                return [
                    'assembly_id' => $assembly->assembly_id,
                    'product_unit' => $availableUnit
                ];
            }
        }

        return null;
    }

    /**
     * Find available product unit in specific assembly
     */
    private function findAvailableUnitInAssembly(int $assemblyId, int $productId): ?int
    {
        // Lấy tất cả product_unit đã xuất cho assembly này
        $usedUnits = \App\Models\DispatchItem::where('assembly_id', $assemblyId)
            ->where('item_type', 'product')
            ->where('item_id', $productId)
            ->whereNotNull('product_unit')
            ->pluck('product_unit')
            ->toArray();

        // Lấy tất cả product_unit có trong assembly này
        $availableUnits = \App\Models\AssemblyMaterial::where('assembly_id', $assemblyId)
            ->where('target_product_id', $productId)
            ->whereNotNull('product_unit')
            ->pluck('product_unit')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Tìm unit đầu tiên chưa được xuất
        foreach ($availableUnits as $unit) {
            if (!in_array($unit, $usedUnits)) {
                return $unit;
            }
        }

        return null;
    }

    /**
     * Resolve assembly_id for a product's specific finished-product unit by serial (or N/A).
     * When multiple N/A records exist, try to find the most relevant one based on warranty context.
     */
    private function findAssemblyIdForProductSerial(int $productId, ?string $deviceSerial, ?string $warrantyCode = null): ?int
    {
        $query = \App\Models\AssemblyProduct::where('product_id', $productId);
        $normalized = strtoupper(trim((string) $deviceSerial));

        if ($normalized === '' || $normalized === 'N/A' || $normalized === 'NA') {
            $query->where(function ($q) {
                $q->whereNull('serials')
                    ->orWhere('serials', '')
                    ->orWhere('serials', 'N/A')
                    ->orWhere('serials', 'NA');
            });
        } else {
            $query->where('serials', $deviceSerial);
        }

        // If we have warranty context, try to find assembly related to this warranty's project
        if ($warrantyCode) {
            $warranty = \App\Models\Warranty::where('warranty_code', $warrantyCode)->first();
            if ($warranty && $warranty->project_id) {
                // Try to find assembly for this specific project first
                $projectAssembly = $query->clone()
                    ->whereHas('assembly', function ($q) use ($warranty) {
                        $q->where('project_id', $warranty->project_id);
                    })
                    ->orderByDesc('id')
                    ->first();

                if ($projectAssembly) {
                    Log::info('Found assembly for warranty project', [
                        'warranty_code' => $warrantyCode,
                        'project_id' => $warranty->project_id,
                        'assembly_id' => $projectAssembly->assembly_id
                    ]);
                    return $projectAssembly->assembly_id;
                }
            }
        }

        // Fallback to most recent assembly
        $assembly = $query->orderByDesc('id')->first();
        if ($assembly) {
            Log::info('Using most recent assembly', [
                'assembly_id' => $assembly->assembly_id,
                'created_at' => $assembly->created_at
            ]);
        }

        return optional($assembly)->assembly_id;
    }

    /**
     * Get device materials from assembly (main source)
     * Optimized with caching to avoid repeated database queries
     */
    private function getDeviceMaterialsFromAssembly($product, ?int $assemblyId = null, ?int $productUnit = null)
    {
        // Cache key để tránh query lặp lại trong cùng request
        $cacheKey = "assembly_materials_{$product->id}_{$assemblyId}_{$productUnit}";
        if (isset($GLOBALS[$cacheKey])) {
            return $GLOBALS[$cacheKey];
        }

        $materials = [];

        try {

            // First try to get materials from assembly_materials table
            $query = \App\Models\AssemblyMaterial::query();
            if ($assemblyId) {
                $query->where('assembly_id', $assemblyId);
                // Nếu có product_unit cụ thể, chỉ lấy vật tư của unit đó
                if ($productUnit !== null) {
                    $query->where('product_unit', $productUnit);
                }
            } else {
                $query->where('target_product_id', $product->id);
            }
            // Chỉ eager load material, không cần assembly để giảm overhead
            $assemblyMaterials = $query->with('material')->get();


            foreach ($assemblyMaterials as $am) {
                if ($am->material) {
                    // Xử lý serial từ assembly - có thể là string hoặc JSON array
                    $raw = (string) ($am->serial ?? '');
                    $serials = array_values(array_filter(array_map(function ($s) {
                        return trim((string) $s);
                    }, preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [])));

                    $materials[] = [
                        'id' => $am->material->id,
                        'code' => $am->material->code,
                        'name' => $am->material->name,
                        'quantity' => $am->quantity,
                        'serial' => implode(', ', $serials),
                        'current_serials' => $serials,
                        'status' => 'active'
                    ];
                }
            }

            // If no materials found from assembly_materials, try to get from product_materials
            if (empty($materials)) {
                // Cache key cho product materials (thường dùng chung cho nhiều unit)
                $pmCacheKey = "product_materials_{$product->id}";
                if (isset($GLOBALS[$pmCacheKey])) {
                    $materials = $GLOBALS[$pmCacheKey];
                } else {
                    // Check if product has materials defined in product_materials table
                    $productMaterials = \App\Models\ProductMaterial::where('product_id', $product->id)
                        ->with('material')
                        ->get();

                    foreach ($productMaterials as $pm) {
                        if ($pm->material) {
                            $materials[] = [
                                'id' => $pm->material->id,
                                'code' => $pm->material->code,
                                'name' => $pm->material->name,
                                'quantity' => $pm->quantity,
                                'serial' => '',
                                'current_serials' => [''],
                                'status' => 'active'
                            ];
                        }
                    }
                    $GLOBALS[$pmCacheKey] = $materials;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error getting materials for product {$product->code}: " . $e->getMessage());
        }

        // Cache kết quả
        $GLOBALS[$cacheKey] = $materials;
        return $materials;
    }

    /**
     * Update materials serials from replacement history
     */
    private function updateMaterialsSerialsFromHistory($materials, $deviceCode, $warrantyCode)
    {
        foreach ($materials as &$material) {
            $originalSerial = (string) ($material['serial'] ?? '');
            $quantity = (int) ($material['quantity'] ?? 1);

            // Ưu tiên: nếu có bản ghi thay thế gần nhất cho device+material trong warranty hiện tại,
            // sử dụng trực tiếp danh sách new_serials của bản ghi đó để phản ánh trạng thái mới nhất
            try {
                $latest = MaterialReplacementHistory::whereHas('repair', function ($q) use ($warrantyCode) {
                    $q->where('warranty_code', $warrantyCode);
                })
                    ->where('device_code', $deviceCode)
                    ->where('material_code', $material['code'] ?? '')
                    ->orderBy('replaced_at', 'desc')
                    ->first();
                if ($latest && is_array($latest->new_serials) && count($latest->new_serials) > 0) {
                    $normalizedNew = array_map(function ($s) {
                        $t = trim((string) $s);
                        return ($t === '' || strtoupper($t) === 'NVA' || strtoupper($t) === 'N/A') ? 'N/A' : $t;
                    }, $latest->new_serials);
                    // Nếu số lượng khớp hoặc lớn hơn 0, ghi đè trực tiếp
                    if (!empty($normalizedNew)) {
                        $material['serial'] = implode(',', $normalizedNew);
                        $material['current_serials'] = $normalizedNew;
                        continue; // sang vật tư tiếp theo
                    }
                }
            } catch (\Exception $e) {
                // bỏ qua nếu lỗi
            }

            // Parse and normalize serials; keep empty entries to preserve positions
            $originalSerials = array_map(function ($s) {
                $t = trim($s);
                if ($t === '' || strtoupper($t) === 'N/A' || strtoupper($t) === 'NVA')
                    return 'N/A';
                return $t;
            }, explode(',', $originalSerial));

            // Pad N/A to match material quantity (important when some are non-serial)
            if ($quantity > count($originalSerials)) {
                $originalSerials = array_pad($originalSerials, $quantity, 'N/A');
            }

            $updatedSerials = [];
            $hasChanges = false;

            foreach ($originalSerials as $singleSerial) {
                $currentSerial = $this->getLatestMaterialSerial(
                    $deviceCode,
                    $material['code'],
                    $singleSerial,
                    $warrantyCode
                );

                $updatedSerials[] = $currentSerial;

                if ($currentSerial !== $singleSerial) {
                    $hasChanges = true;
                }
            }

            // Update material serial if there were changes
            if ($hasChanges) {
                // Remove empty remnants and join
                $final = array_values(array_filter($updatedSerials, function ($v) {
                    return trim((string) $v) !== '';
                }));
                $material['serial'] = implode(',', $final);
                $material['current_serials'] = $final;
            }
        }

        return $materials;
    }

    /**
     * Áp serial vật tư mới nhất từ mọi lịch sử thay thế có cùng device_code
     */
    private function updateMaterialsSerialsFromAnyWarranty(array $materials, string $deviceCode): array
    {
        try {
            $histories = MaterialReplacementHistory::where('device_code', $deviceCode)
                ->orderBy('replaced_at', 'desc')
                ->get();
            if ($histories->isEmpty())
                return $materials;

            foreach ($materials as &$material) {
                $originalSerial = $material['serial'] ?? '';
                // Không loại bỏ ký tự rỗng để vẫn map được các vị trí N/A
                $oldSerials = array_map('trim', explode(',', $originalSerial));
                $updated = false;

                foreach ($histories as $h) {
                    if ($h->material_code !== ($material['code'] ?? ''))
                        continue;
                    // Chuẩn hoá 'N/A' trong lịch sử
                    $hOld = array_map(function ($v) {
                        $t = is_string($v) ? trim($v) : $v;
                        return ($t === '' || strtoupper((string) $t) === 'N/A') ? 'N/A' : $t;
                    }, (array) ($h->old_serials ?? []));
                    $hNew = (array) ($h->new_serials ?? []);
                    if (!empty($oldSerials)) {
                        $allEmpty = true;
                        foreach ($oldSerials as $i => $s) {
                            $norm = ($s === '' || strtoupper($s) === 'N/A') ? 'N/A' : $s;
                            if ($norm !== 'N/A')
                                $allEmpty = false;
                            $pos = array_search($norm, $hOld, true);
                            if ($pos !== false && isset($hNew[$pos])) {
                                $oldSerials[$i] = trim((string) $hNew[$pos]);
                                $updated = true;
                            }
                        }
                        // Trường hợp tất cả đều N/A và lịch sử có serial mới -> thay toàn bộ theo hNew
                        if ($allEmpty && !empty($hNew)) {
                            $oldSerials = array_map(function ($v) {
                                return trim((string) $v);
                            }, $hNew);
                            $updated = true;
                        }
                    } elseif (!empty($hNew)) {
                        // Trường hợp ban đầu rỗng, nếu có bản ghi thay thế gần nhất -> áp serial mới
                        $oldSerials = $hNew;
                        $updated = true;
                    }
                    if ($updated)
                        break; // Lấy bản gần nhất
                }

                if ($updated) {
                    // Loại bỏ chuỗi rỗng còn sót lại khi hiển thị
                    $final = array_values(array_filter($oldSerials, function ($v) {
                        return trim((string) $v) !== '';
                    }));
                    $material['serial'] = implode(',', $final);
                    $material['current_serials'] = $final;
                }
            }
        } catch (\Exception $e) {
            Log::warning('updateMaterialsSerialsFromAnyWarranty failed: ' . $e->getMessage());
        }
        return $materials;
    }

    /**
     * API: Search devices in warehouse by code or serial
     */
    public function searchWarehouseDevices(Request $request)
    {
        $searchTerm = $request->get('search_term');

        if (!$searchTerm) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã thiết bị hoặc serial number'
            ]);
        }

        try {
            $input = trim($searchTerm);
            $normalizedSerial = strtoupper(preg_replace('/[\s-]+/', '', $input));

            $devices = [];

            // Tìm kiếm trong warehouse_materials cho products
            $warehouseProducts = \App\Models\WarehouseMaterial::where('item_type', 'product')
                ->whereHas('product', function ($q) use ($input) {
                    $q->where('status', 'active')
                        ->where('is_hidden', false)
                        ->where(function ($subQ) use ($input) {
                            $subQ->where('code', 'LIKE', "%{$input}%")
                                ->orWhere('name', 'LIKE', "%{$input}%");
                        });
                })
                ->whereHas('warehouse', function ($q) {
                    $q->where('status', 'active');
                })
                ->with(['product', 'warehouse'])
                ->get();

            foreach ($warehouseProducts as $wp) {
                $product = $wp->product;

                // Xử lý serial numbers - tách thành từng serial riêng biệt
                $serialNumbers = [];
                if ($wp->serial_number) {
                    // Nếu serial_number là JSON array
                    if (is_string($wp->serial_number) && strpos($wp->serial_number, '[') === 0) {
                        $serialNumbers = json_decode($wp->serial_number, true) ?: [];
                    } else {
                        // Nếu là string đơn
                        $serialNumbers = [$wp->serial_number];
                    }
                }

                // Nếu tìm theo serial, chỉ trả về thiết bị có serial khớp
                if (strcasecmp($input, $product->code) !== 0 && strcasecmp($input, $product->name) !== 0) {
                    if (!empty($normalizedSerial)) {
                        $hasMatchingSerial = false;
                        foreach ($serialNumbers as $sn) {
                            $ns = strtoupper(preg_replace('/[\s-]+/', '', $sn));
                            if ($ns === $normalizedSerial) {
                                $hasMatchingSerial = true;
                                break;
                            }
                        }
                        if (!$hasMatchingSerial)
                            continue;
                    } else {
                        continue; // Không tìm thấy theo tên/mã và không phải tìm serial
                    }
                }

                // Nếu có nhiều serial, tạo từng hàng riêng biệt
                if (!empty($serialNumbers)) {
                    foreach ($serialNumbers as $serial) {
                        $devices[] = [
                            'id' => 'warehouse_product_' . $product->code . '_' . $wp->warehouse_id . '_' . $serial . '_' . microtime(true) . '_' . uniqid(),
                            'code' => $product->code,
                            'name' => $product->name,
                            'quantity' => 1, // Mỗi serial = 1 thành phẩm
                            'serial' => $serial,
                            'serial_numbers' => [$serial],
                            'serial_numbers_text' => $serial,
                            'status' => 'active',
                            'type' => 'product',
                            'source' => 'warehouse',
                            'warehouse_id' => $wp->warehouse_id,
                            'warehouse_name' => $wp->warehouse->name,
                        ];
                    }
                } else {
                    // Nếu không có serial, tạo một hàng với quantity tổng
                    $devices[] = [
                        'id' => 'warehouse_product_' . $product->code . '_' . $wp->warehouse_id . '_' . microtime(true) . '_' . uniqid(),
                        'code' => $product->code,
                        'name' => $product->name,
                        'quantity' => $wp->quantity,
                        'serial' => '',
                        'serial_numbers' => [],
                        'serial_numbers_text' => 'N/A',
                        'status' => 'active',
                        'type' => 'product',
                        'source' => 'warehouse',
                        'warehouse_id' => $wp->warehouse_id,
                        'warehouse_name' => $wp->warehouse->name,
                    ];
                }
            }

            // Tìm kiếm trong warehouse_materials cho goods
            $warehouseGoods = \App\Models\WarehouseMaterial::where('item_type', 'good')
                ->whereHas('good', function ($q) use ($input) {
                    $q->where('status', 'active')
                        ->where('is_hidden', false)
                        ->where(function ($subQ) use ($input) {
                            $subQ->where('code', 'LIKE', "%{$input}%")
                                ->orWhere('name', 'LIKE', "%{$input}%");
                        });
                })
                ->whereHas('warehouse', function ($q) {
                    $q->where('status', 'active');
                })
                ->with(['good', 'warehouse'])
                ->get();

            foreach ($warehouseGoods as $wg) {
                $good = $wg->good;

                // Xử lý serial numbers - tách thành từng serial riêng biệt
                $serialNumbers = [];
                if ($wg->serial_number) {
                    // Nếu serial_number là JSON array
                    if (is_string($wg->serial_number) && strpos($wg->serial_number, '[') === 0) {
                        $serialNumbers = json_decode($wg->serial_number, true) ?: [];
                    } else {
                        // Nếu là string đơn
                        $serialNumbers = [$wg->serial_number];
                    }
                }

                // Nếu tìm theo serial, chỉ trả về thiết bị có serial khớp
                if (strcasecmp($input, $good->code) !== 0 && strcasecmp($input, $good->name) !== 0) {
                    if (!empty($normalizedSerial)) {
                        $hasMatchingSerial = false;
                        foreach ($serialNumbers as $sn) {
                            $ns = strtoupper(preg_replace('/[\s-]+/', '', $sn));
                            if ($ns === $normalizedSerial) {
                                $hasMatchingSerial = true;
                                break;
                            }
                        }
                        if (!$hasMatchingSerial)
                            continue;
                    } else {
                        continue; // Không tìm thấy theo tên/mã và không phải tìm serial
                    }
                }

                // Nếu có nhiều serial, tạo từng hàng riêng biệt
                if (!empty($serialNumbers)) {
                    foreach ($serialNumbers as $serial) {
                        $devices[] = [
                            'id' => 'warehouse_good_' . $good->code . '_' . $wg->warehouse_id . '_' . $serial . '_' . microtime(true) . '_' . uniqid(),
                            'code' => $good->code,
                            'name' => $good->name,
                            'quantity' => 1, // Mỗi serial = 1 hàng hóa
                            'serial' => $serial,
                            'serial_numbers' => [$serial],
                            'serial_numbers_text' => $serial,
                            'status' => 'active',
                            'type' => 'good',
                            'source' => 'warehouse',
                            'warehouse_id' => $wg->warehouse_id,
                            'warehouse_name' => $wg->warehouse->name,
                        ];
                    }
                } else {
                    // Nếu không có serial, tạo một hàng với quantity tổng
                    $devices[] = [
                        'id' => 'warehouse_good_' . $good->code . '_' . $wg->warehouse_id . '_' . microtime(true) . '_' . uniqid(),
                        'code' => $good->code,
                        'name' => $good->name,
                        'quantity' => $wg->quantity,
                        'serial' => '',
                        'serial_numbers' => [],
                        'serial_numbers_text' => 'N/A',
                        'status' => 'active',
                        'type' => 'good',
                        'source' => 'warehouse',
                        'warehouse_id' => $wg->warehouse_id,
                        'warehouse_name' => $wg->warehouse->name,
                    ];
                }
            }

            // Tìm kiếm trong bảng serials cho products và goods
            $serialRecords = \App\Models\Serial::where('status', 'active')
                ->where('warehouse_id', '>', 0)
                ->where(function ($q) use ($normalizedSerial) {
                    $q->whereRaw('UPPER(REPLACE(REPLACE(serial_number, " ", ""), "-", "")) = ?', [$normalizedSerial]);
                })
                ->with(['product', 'good', 'warehouse'])
                ->get();

            foreach ($serialRecords as $serial) {
                $item = null;
                $itemType = '';
                $itemCode = '';
                $itemName = '';

                if ($serial->product) {
                    $item = $serial->product;
                    $itemType = 'product';
                    $itemCode = $item->code;
                    $itemName = $item->name;
                } elseif ($serial->good) {
                    $item = $serial->good;
                    $itemType = 'good';
                    $itemCode = $item->code;
                    $itemName = $item->name;
                }

                if ($item && $item->status === 'active' && !$item->is_hidden) {
                    // Kiểm tra xem thiết bị đã được thêm vào danh sách chưa
                    $existingDevice = collect($devices)->first(function ($device) use ($itemCode, $serial) {
                        return $device['code'] === $itemCode && $device['warehouse_id'] === $serial->warehouse_id;
                    });

                    if (!$existingDevice) {
                        $devices[] = [
                            // Use warehouse_* format so getDeviceMaterials recognizes warehouse devices
                            'id' => 'warehouse_' . $itemType . '_' . $itemCode . '_' . $serial->warehouse_id . '_' . $serial->serial_number . '_' . microtime(true) . '_' . uniqid(),
                            'code' => $itemCode,
                            'name' => $itemName,
                            'quantity' => 1,
                            'serial' => $serial->serial_number,
                            'serial_numbers' => [$serial->serial_number],
                            'serial_numbers_text' => $serial->serial_number,
                            'status' => 'active',
                            'type' => $itemType,
                            'source' => 'warehouse',
                            'warehouse_id' => $serial->warehouse_id,
                            'warehouse_name' => $serial->warehouse->name,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'devices' => $devices
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching warehouse devices: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tìm kiếm thiết bị trong kho: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Check stock availability for material replacement
     */
    public function checkStockAvailability(Request $request)
    {
        $request->validate([
            'material_code' => 'required|string',
            'warehouse_id' => 'required|integer',
            'required_quantity' => 'required|integer|min:1',
            'required_serials' => 'array'
        ]);

        try {
            $materialCode = $request->get('material_code');
            $warehouseId = $request->get('warehouse_id');
            $requiredQuantity = $request->get('required_quantity');
            $requiredSerials = $request->get('required_serials', []);

            // Tìm material theo code
            $material = Material::where('code', $materialCode)->first();
            if (!$material) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy vật tư với mã: ' . $materialCode
                ]);
            }

            // Kiểm tra tồn kho trong warehouse
            $warehouseMaterial = WarehouseMaterial::where('material_id', $material->id)
                ->where('warehouse_id', $warehouseId)
                ->where('item_type', 'material')
                ->first();

            if (!$warehouseMaterial || $warehouseMaterial->quantity < $requiredQuantity) {
                return response()->json([
                    'success' => false,
                    'available' => false,
                    'message' => "Không đủ tồn kho. Yêu cầu: {$requiredQuantity}, Tồn kho: " . ($warehouseMaterial ? $warehouseMaterial->quantity : 0)
                ]);
            }

            // Tính toán chi tiết tồn kho: tổng, có-serial và không-serial
            $totalStock = (int) $warehouseMaterial->quantity;
            $serialStock = 0;
            if (!empty($warehouseMaterial->serial_number)) {
                $warehouseSerials = json_decode($warehouseMaterial->serial_number, true);
                if (is_array($warehouseSerials)) {
                    $serialStock = count(array_filter(array_map('trim', $warehouseSerials)));
                }
            }
            $nonSerialStock = max(0, $totalStock - $serialStock);

            // Kiểm tra tổng tồn kho (không phân biệt serial hay không serial)
            if ($totalStock < $requiredQuantity) {
                return response()->json([
                    'success' => false,
                    'available' => false,
                    'message' => "Không đủ tồn kho. Yêu cầu: {$requiredQuantity}, Tổng tồn: {$totalStock} (Serial: {$serialStock}, Không serial: {$nonSerialStock})"
                ]);
            }

            return response()->json([
                'success' => true,
                'available' => true,
                'message' => 'Đủ tồn kho cho việc thay thế',
                'details' => [
                    'total_stock' => $totalStock,
                    'serial_stock' => $serialStock,
                    'non_serial_stock' => $nonSerialStock
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking stock availability: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi kiểm tra tồn kho: ' . $e->getMessage()
            ]);
        }
    }



    /**
     * API: Get available serials in warehouse
     */
    public function getAvailableSerials(Request $request)
    {
        $materialCode = $request->get('material_code');
        $warehouseId = $request->get('warehouse_id');

        if (!$materialCode || !$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu thông tin material_code hoặc warehouse_id'
            ]);
        }

        try {
            // Tìm material theo code
            $material = \App\Models\Material::where('code', $materialCode)->first();

            if (!$material) {
                Log::error("Material not found with code: {$materialCode}");
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy vật tư'
                ]);
            }

            // Lấy danh sách serial có sẵn trong kho
            $serials = [];

            // 1. Tìm trong bảng serials
            $serialRecords = \App\Models\Serial::where('warehouse_id', $warehouseId)
                ->where('type', 'material')
                ->where('status', 'available')
                ->where('product_id', $material->id)
                ->get();

            // 2. Tìm trong bảng warehouse_materials (nếu có serial)
            $warehouseMaterialSerials = \App\Models\WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $material->id)
                ->where('item_type', 'material')
                ->whereNotNull('serial_number')
                ->get();

            // 3. Tìm trong bảng goods (nếu material là good)
            $goodSerials = \App\Models\Good::where('code', $materialCode)
                ->whereHas('warehouseMaterials', function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId)
                        ->whereNotNull('serial_number');
                })
                ->with([
                    'warehouseMaterials' => function ($q) use ($warehouseId) {
                        $q->where('warehouse_id', $warehouseId)
                            ->whereNotNull('serial_number');
                    }
                ])
                ->first();

            if ($goodSerials) {
                Log::info("Found {$goodSerials->warehouseMaterials->count()} serials in Good table");
            }

            // Lấy danh sách serial đã được sử dụng trong thay thế
            $usedSerials = \App\Models\MaterialReplacementHistory::where('material_code', $materialCode)
                ->get()
                ->pluck('new_serials')
                ->flatten()
                ->unique()
                ->toArray();

            // Thêm serials từ bảng Serial
            foreach ($serialRecords as $serial) {
                if (!in_array($serial->serial_number, $usedSerials)) {
                    $serials[] = [
                        'serial' => $serial->serial_number,
                        'status' => 'available'
                    ];
                }
            }

            // Thêm serials từ bảng WarehouseMaterial
            foreach ($warehouseMaterialSerials as $wm) {
                // Xử lý serial_number có thể là JSON array hoặc string
                $wmSerials = [];
                if (is_string($wm->serial_number)) {
                    // Thử parse JSON
                    $decoded = json_decode($wm->serial_number, true);
                    if (is_array($decoded)) {
                        $wmSerials = $decoded;
                    } else {
                        $wmSerials = [$wm->serial_number];
                    }
                } elseif (is_array($wm->serial_number)) {
                    $wmSerials = $wm->serial_number;
                }

                foreach ($wmSerials as $serial) {
                    if (!in_array($serial, $usedSerials)) {
                        $serials[] = [
                            'serial' => $serial,
                            'status' => 'available'
                        ];
                    }
                }
            }

            // Thêm serials từ bảng Good
            if ($goodSerials && $goodSerials->warehouseMaterials) {
                foreach ($goodSerials->warehouseMaterials as $wm) {
                    // Xử lý serial_number có thể là JSON array hoặc string
                    $goodSerials = [];
                    if (is_string($wm->serial_number)) {
                        $decoded = json_decode($wm->serial_number, true);
                        if (is_array($decoded)) {
                            $goodSerials = $decoded;
                        } else {
                            $goodSerials = [$wm->serial_number];
                        }
                    } elseif (is_array($wm->serial_number)) {
                        $goodSerials = $wm->serial_number;
                    }

                    foreach ($goodSerials as $serial) {
                        if (!in_array($serial, $usedSerials)) {
                            $serials[] = [
                                'serial' => $serial,
                                'status' => 'available'
                            ];
                        }
                    }
                }
            }

            // Loại bỏ duplicate serials
            $serials = array_unique($serials, SORT_REGULAR);

            // Tính tồn không-serial trong kho
            $nonSerialStock = 0;

            // Từ WarehouseMaterial - tính số lượng không có serial (chỉ item_type = 'material')
            $nonSerialFromWM = 0;
            $warehouseMaterials = \App\Models\WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $material->id)
                ->where('item_type', 'material')
                ->get();

            foreach ($warehouseMaterials as $wm) {
                // Tính số lượng không có serial
                if (
                    is_null($wm->serial_number) ||
                    (is_string($wm->serial_number) && (trim($wm->serial_number) === '' || trim($wm->serial_number) === '[]')) ||
                    (is_array($wm->serial_number) && empty($wm->serial_number))
                ) {
                    // Nếu không có serial nào, toàn bộ quantity là non-serial
                    $nonSerialFromWM += $wm->quantity;
                } else {
                    // Nếu có serials, tính số lượng non-serial = quantity - số serials
                    $serialCount = 0;
                    if (is_string($wm->serial_number)) {
                        $decoded = json_decode($wm->serial_number, true);
                        if (is_array($decoded)) {
                            $serialCount = count($decoded);
                        } else {
                            $serialCount = 1; // Nếu không phải JSON array, coi như 1 serial
                        }
                    } elseif (is_array($wm->serial_number)) {
                        $serialCount = count($wm->serial_number);
                    }

                    $nonSerialCount = max(0, $wm->quantity - $serialCount);
                    $nonSerialFromWM += $nonSerialCount;
                }
            }

            // Từ Good (nếu material là good)
            $nonSerialFromGood = 0;
            if ($goodSerials && $goodSerials->warehouseMaterials) {
                foreach ($goodSerials->warehouseMaterials as $wm) {
                    // Nếu serial_number là null hoặc empty array
                    if (
                        is_null($wm->serial_number) ||
                        (is_string($wm->serial_number) && (trim($wm->serial_number) === '' || trim($wm->serial_number) === '[]')) ||
                        (is_array($wm->serial_number) && empty($wm->serial_number))
                    ) {
                        $nonSerialFromGood += $wm->quantity;
                    }
                }
            }

            $nonSerialStock = $nonSerialFromWM + $nonSerialFromGood;

            return response()->json([
                'success' => true,
                'serials' => array_values($serials),
                'details' => [
                    'non_serial_stock' => (int) $nonSerialStock
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting available serials: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách serial'
            ]);
        }
    }

    /**
     * API: Replace material in repair
     */
    public function replaceMaterial(Request $request)
    {
        $request->validate([
            'materialCode' => 'required|string',
            'materialName' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'oldSerials' => 'required|array|min:1',
            'newSerials' => 'required|array|min:1',
            'sourceWarehouse' => 'required|integer',
            'targetWarehouse' => 'required|integer',
            'deviceCode' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Validate quantities match
            if (
                count($request->oldSerials) !== $request->quantity ||
                count($request->newSerials) !== $request->quantity
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng serial không khớp với số lượng thay thế'
                ]);
            }

            // Find material
            $material = \App\Models\Material::where('code', $request->materialCode)->first();
            if (!$material) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy vật tư'
                ]);
            }

            // TODO: Implement actual material replacement logic
            // 1. Move old materials to source warehouse
            // 2. Move new materials from target warehouse
            // 3. Update assembly_materials table
            // 4. Create warehouse transfer records
            // 5. Log the replacement

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thay thế vật tư thành công',
                'updated_material' => [
                    'materialCode' => $request->materialCode,
                    'materialName' => $request->materialName,
                    'quantity' => $request->quantity,
                    'newSerials' => $request->newSerials
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error replacing material: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thay thế vật tư: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get repair type label
     */
    private function getRepairTypeLabel($type)
    {
        $labels = [
            'maintenance' => 'Bảo trì',
            'repair' => 'Sửa chữa',
            'replacement' => 'Thay thế',
            'upgrade' => 'Nâng cấp',
            'other' => 'Khác'
        ];

        return $labels[$type] ?? 'Không xác định';
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'warranty_code' => 'nullable|string|max:255',
                'repair_type' => 'required|in:maintenance,repair,replacement,upgrade,other',
                'repair_date' => 'required|date_format:d/m/Y',
                'technician_id' => 'required|integer|exists:employees,id', // Ensure it's a valid employee ID
                'repair_description' => 'required|string',
                'repair_notes' => 'nullable|string',
                'repair_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'selected_devices' => 'nullable|array',
                'damaged_materials' => 'nullable|string',
                'warehouse_id' => 'nullable|integer|exists:warehouses,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        // Custom validation: phải có ít nhất một thiết bị được chọn hoặc từ chối
        if (empty($request->selected_devices) && empty($request->rejected_devices)) {
            Log::info('No devices selected or rejected, returning error');
            return redirect()->back()
                ->withInput()
                ->withErrors(['selected_devices' => 'Vui lòng chọn hoặc từ chối ít nhất một thiết bị.']);
        }

        try {
            Log::info('Starting database transaction...');
            DB::beginTransaction();

            // Find warranty if warranty_code is provided (accepts warranty code or device serial)
            $warranty = null;
            $inputWarrantyOrSerial = trim((string) $request->warranty_code);
            if ($inputWarrantyOrSerial !== '') {
                // 1) Try exact warranty code
                $warranty = Warranty::where('warranty_code', $inputWarrantyOrSerial)->first();

                // 2) Fallback: treat input as serial; find ACTIVE warranty by serial
                if (!$warranty) {
                    $normalizedSerial = strtoupper(preg_replace('/[\s-]+/', '', $inputWarrantyOrSerial));
                    $warranty = Warranty::where('status', 'active')
                        ->where(function ($q) use ($inputWarrantyOrSerial, $normalizedSerial) {
                            $q->whereRaw('UPPER(REPLACE(REPLACE(IFNULL(serial_number, ""), " ", ""), "-", "")) = ?', [$normalizedSerial])
                                ->orWhereHas('dispatch.items', function ($qi) use ($inputWarrantyOrSerial, $normalizedSerial) {
                                    $qi->whereIn('item_type', ['product', 'good'])
                                        ->where(function ($qj) use ($inputWarrantyOrSerial, $normalizedSerial) {
                                            $qj->whereJsonContains('serial_numbers', $inputWarrantyOrSerial)
                                                ->orWhereRaw('JSON_SEARCH(serial_numbers, "one", ?) IS NOT NULL', [$inputWarrantyOrSerial])
                                                ->orWhereRaw('JSON_SEARCH(serial_numbers, "one", ?) IS NOT NULL', [$normalizedSerial]);
                                        });
                                });
                        })
                        ->first();
                }
            }

            // Handle file uploads
            $repairPhotos = [];
            if ($request->hasFile('repair_photos')) {
                foreach ($request->file('repair_photos') as $photo) {
                    $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $path = $photo->storeAs('repairs/photos', $filename, 'public');
                    $repairPhotos[] = $path;
                }
            }

            // Initial status: luôn là đang xử lý khi tạo mới
            $initialStatus = 'in_progress';

            // Validate and get warehouse_id
            $warehouseId = null;
            if ($request->warehouse_id) {
                $warehouseId = (int) $request->warehouse_id;
                // Verify warehouse exists
                $warehouse = Warehouse::find($warehouseId);
                if (!$warehouse) {
                    throw new \Exception('Kho không tồn tại. Vui lòng chọn kho hợp lệ.');
                }
            } else {
                // Get default warehouse if not provided
                $defaultWarehouse = Warehouse::where('status', 'active')
                    ->where('is_hidden', false)
                    ->where(function ($q) {
                        $q->where('is_default', true)
                            ->orWhereNull('is_default');
                    })
                    ->orderBy('is_default', 'desc')
                    ->first();

                if ($defaultWarehouse) {
                    $warehouseId = $defaultWarehouse->id;
                } else {
                    // If no default warehouse, get first active warehouse
                    $firstWarehouse = Warehouse::where('status', 'active')
                        ->where('is_hidden', false)
                        ->first();
                    if ($firstWarehouse) {
                        $warehouseId = $firstWarehouse->id;
                    }
                }

                if (!$warehouseId) {
                    throw new \Exception('Không tìm thấy kho hợp lệ. Vui lòng tạo kho trước khi tạo phiếu sửa chữa.');
                }
            }

            // Create repair record with retry mechanism for duplicate repair_code
            $repair = null;
            $maxRetries = 10;
            $repairData = [
                // If warranty resolved from serial, always use real warranty code
                'warranty_code' => $warranty ? $warranty->warranty_code : ($inputWarrantyOrSerial ?: null),
                'warranty_id' => $warranty ? $warranty->id : null,
                'repair_type' => $request->repair_type,
                'repair_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->repair_date)->format('Y-m-d'),
                'technician_id' => (int) $request->technician_id, // Ensure integer type
                'warehouse_id' => $warehouseId,
                'repair_description' => $request->repair_description,
                'repair_notes' => $request->repair_notes,
                'repair_photos' => $repairPhotos,
                'status' => $initialStatus,
                'created_by' => Auth::id() ?? 1,
            ];

            // Track the last number used in this transaction to avoid duplicates
            $prefix = 'SC';
            $year = date('Y');
            $month = date('m');
            $lastUsedNumber = null;

            for ($i = 0; $i < $maxRetries; $i++) {
                try {
                    // Generate repair code with tracking of used numbers in transaction
                    if ($lastUsedNumber === null) {
                        // First attempt: get from database
                        $lastRepair = Repair::where('repair_code', 'like', $prefix . $year . $month . '%')
                            ->lockForUpdate()
                            ->orderBy('repair_code', 'desc')
                            ->first();

                        if ($lastRepair) {
                            $lastUsedNumber = (int) substr($lastRepair->repair_code, -4);
                        } else {
                            $lastUsedNumber = 0;
                        }
                    }

                    // Increment for next code
                    $lastUsedNumber++;
                    $repairCode = $prefix . $year . $month . str_pad($lastUsedNumber, 4, '0', STR_PAD_LEFT);
                    $repairData['repair_code'] = $repairCode;

                    Log::info('Attempting to create repair', [
                        'attempt' => $i + 1,
                        'repair_code' => $repairCode,
                        'last_used_number' => $lastUsedNumber
                    ]);

                    // Try to create the repair
                    $repair = Repair::create($repairData);

                    // Success - break out of retry loop
                    Log::info('Repair created successfully', ['repair_id' => $repair->id, 'repair_code' => $repair->repair_code]);
                    break;
                } catch (QueryException $e) {
                    // Handle database constraint violations
                    if ($e->getCode() == 23000) { // Integrity constraint violation
                        $errorMessage = $e->getMessage();

                        // Check if it's a foreign key constraint violation (1452)
                        if (
                            strpos($errorMessage, '1452') !== false ||
                            strpos($errorMessage, 'foreign key constraint fails') !== false ||
                            strpos($errorMessage, 'Cannot add or update a child row') !== false
                        ) {

                            // Foreign key violation - don't retry, throw error immediately
                            $errorMsg = 'Lỗi ràng buộc database: ';
                            if (strpos($errorMessage, 'technician_id') !== false) {
                                $errorMsg .= 'Kỹ thuật viên không tồn tại.';
                            } elseif (strpos($errorMessage, 'warehouse_id') !== false) {
                                $errorMsg .= 'Kho không tồn tại.';
                            } elseif (strpos($errorMessage, 'created_by') !== false) {
                                $errorMsg .= 'Người tạo không tồn tại.';
                            } else {
                                $errorMsg .= 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại thông tin.';
                            }

                            Log::error('Foreign key constraint violation', [
                                'error' => $errorMessage,
                                'repair_data' => $repairData
                            ]);

                            throw new \Exception($errorMsg);
                        }

                        // Check if it's a duplicate repair_code error (1062)
                        if (
                            strpos($errorMessage, 'repair_code') !== false ||
                            strpos($errorMessage, 'Duplicate entry') !== false ||
                            strpos($errorMessage, 'UNIQUE constraint failed') !== false ||
                            strpos($errorMessage, '1062') !== false
                        ) {

                            Log::warning('Duplicate repair code detected, retrying', [
                                'attempt' => $i + 1,
                                'repair_code' => $repairCode ?? 'N/A',
                                'error' => $errorMessage
                            ]);

                            // If this is the last retry, throw error
                            if ($i === $maxRetries - 1) {
                                Log::error('Failed to create repair after max retries', [
                                    'attempts' => $maxRetries,
                                    'last_code' => $repairCode ?? 'N/A'
                                ]);
                                throw new \Exception('Không thể tạo mã phiếu sửa chữa duy nhất sau ' . $maxRetries . ' lần thử. Vui lòng thử lại.');
                            }

                            // Continue to next iteration (will increment lastUsedNumber)
                            continue;
                        }

                        // Other constraint violations - throw error
                        Log::error('Unknown constraint violation', [
                            'error' => $errorMessage,
                            'error_code' => $e->getCode()
                        ]);
                        throw new \Exception('Lỗi ràng buộc database: ' . $errorMessage);
                    }
                    // Re-throw if it's not a constraint violation we can handle
                    throw $e;
                }
            }

            // Verify repair was created successfully
            if (!$repair || !$repair->id) {
                throw new \Exception('Không thể tạo phiếu sửa chữa. Vui lòng thử lại.');
            }

            Log::info('Repair created successfully', [
                'repair_id' => $repair->id,
                'repair_code' => $repair->repair_code,
                'technician_id' => $repair->technician_id,
                'warehouse_id' => $repair->warehouse_id
            ]);

            // Ghi nhật ký tạo mới phiếu sửa chữa
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'repairs',
                    'Tạo mới phiếu sửa chữa: ' . $repair->repair_code,
                    null,
                    $repair->toArray()
                );
            }

            // Process selected devices
            if ($request->selected_devices && !empty($request->selected_devices)) {
                // Remove duplicates to prevent duplicate repair items
                $uniqueDeviceIds = array_unique($request->selected_devices);

                foreach ($uniqueDeviceIds as $deviceId) {
                    try {
                        // Escape device ID to match frontend format
                        $deviceKey = str_replace(['.', '[', ']'], ['_DOT_', '_LB_', '_RB_'], $deviceId);

                        // Check all possible input formats with escaped key
                        $deviceCode = $request->input("device_code.{$deviceKey}") ??
                            $request->input("device_code[{$deviceKey}]") ??
                            $request->input("device_code.{$deviceId}") ??
                            $request->input("device_code[{$deviceId}]") ?? '';
                        $deviceName = $request->input("device_name.{$deviceKey}") ??
                            $request->input("device_name[{$deviceKey}]") ??
                            $request->input("device_name.{$deviceId}") ??
                            $request->input("device_name[{$deviceId}]") ?? '';
                        // Try to get device_serial from original key first, then escaped key
                        $deviceSerial = $request->input("device_serial[{$deviceId}]") ??
                            $request->input("device_serial.{$deviceId}") ??
                            $request->input("device_serial[{$deviceKey}]") ??
                            $request->input("device_serial.{$deviceKey}") ?? '';

                        // Do not auto-derive serial from deviceId; keep null when not provided
                        $deviceQuantity = $request->input("device_quantity.{$deviceKey}") ??
                            $request->input("device_quantity[{$deviceKey}]") ??
                            $request->input("device_quantity.{$deviceId}") ??
                            $request->input("device_quantity[{$deviceId}]") ?? 1;
                        $deviceNotes = $request->input("device_notes.{$deviceKey}") ??
                            $request->input("device_notes[{$deviceKey}]") ??
                            $request->input("device_notes.{$deviceId}") ??
                            $request->input("device_notes[{$deviceId}]") ?? '';
                        $deviceType = $request->input("device_type.{$deviceKey}") ??
                            $request->input("device_type[{$deviceKey}]") ??
                            $request->input("device_type.{$deviceId}") ??
                            $request->input("device_type[{$deviceId}]") ?? 'product';
                        $deviceSource = $request->input("device_source.{$deviceKey}") ??
                            $request->input("device_source[{$deviceKey}]") ??
                            $request->input("device_source.{$deviceId}") ??
                            $request->input("device_source[{$deviceId}]") ?? 'contract';

                        // Handle device images
                        $deviceImages = [];

                        // Kiểm tra xem có files trong device_images array không (với escaped key)
                        $deviceImagesArray = $request->file('device_images', []);
                        $hasDeviceImages = (isset($deviceImagesArray[$deviceKey]) && !empty($deviceImagesArray[$deviceKey])) ||
                            (isset($deviceImagesArray[$deviceId]) && !empty($deviceImagesArray[$deviceId]));

                        // Ưu tiên escaped key, fallback về original key
                        $deviceImageFiles = $deviceImagesArray[$deviceKey] ?? $deviceImagesArray[$deviceId] ?? [];

                        if ($hasDeviceImages) {
                            $files = $deviceImageFiles;

                            foreach ($files as $index => $image) {
                                if ($image->isValid()) {
                                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                                    $path = $image->storeAs('repairs/devices', $filename, 'public');
                                    $deviceImages[] = $path;
                                } else {
                                    Log::warning("Invalid image file for device {$deviceId} at index {$index}");
                                }
                            }
                        } else {
                            Log::info("No device images found for device: {$deviceId}");
                        }

                        // Lấy thông tin device_parts nếu có
                        $deviceParts = [];
                        if ($request->has("device_parts.{$deviceId}")) {
                            $partsData = $request->input("device_parts.{$deviceId}");
                            if (is_string($partsData)) {
                                $deviceParts = json_decode($partsData, true) ?: [];
                            } elseif (is_array($partsData)) {
                                $deviceParts = $partsData;
                            }
                        }

                        // Validate required fields
                        if (empty($deviceCode) || empty($deviceName)) {
                            throw new \Exception("Missing required device information: code={$deviceCode}, name={$deviceName}");
                        }

                        $repairItem = RepairItem::create([
                            'repair_id' => $repair->id,
                            'device_code' => $deviceCode,
                            'device_name' => $deviceName,
                            'device_serial' => $deviceSerial ?: null, // Allow null serial
                            'device_quantity' => $deviceQuantity,
                            'device_status' => 'selected',
                            'device_notes' => $deviceNotes,
                            'device_images' => $deviceImages,
                            'device_parts' => $deviceParts,
                            'device_type' => $deviceType,
                            'device_source' => $deviceSource,
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Error processing device {$deviceId}: " . $e->getMessage());
                        Log::error("Device data: " . json_encode([
                            'deviceId' => $deviceId,
                            'deviceKey' => $deviceKey,
                            'deviceCode' => $deviceCode,
                            'deviceName' => $deviceName,
                            'deviceSerial' => $deviceSerial,
                            'deviceQuantity' => $deviceQuantity,
                        ]));
                        throw $e;
                    }
                }
            }

            // Process rejected devices
            if ($request->has('rejected_devices')) {
                $rejectedDevices = json_decode($request->rejected_devices, true);
                if (is_array($rejectedDevices)) {
                    foreach ($rejectedDevices as $rejectedDevice) {
                        $repairItem = RepairItem::create([
                            'repair_id' => $repair->id,
                            'device_code' => $rejectedDevice['code'] ?? '',
                            'device_name' => $rejectedDevice['name'] ?? '',
                            'device_serial' => '',
                            'device_quantity' => $rejectedDevice['quantity'] ?? 1,
                            'device_status' => 'rejected',
                            'device_notes' => $rejectedDevice['reason'] ?? '',
                            'rejected_reason' => $rejectedDevice['reason'] ?? '',
                            'rejected_warehouse_id' => $rejectedDevice['warehouse_id'] ?? null,
                            'rejected_at' => $rejectedDevice['rejected_at'] ?? now(),
                            'device_type' => $rejectedDevice['type'] ?? 'product',
                        ]);

                        // Lưu nhật ký thay đổi cho thành phẩm bị từ chối
                        try {
                            ChangeLogHelper::suaChua(
                                $rejectedDevice['code'] ?? '',
                                $rejectedDevice['name'] ?? '',
                                $rejectedDevice['quantity'] ?? 1,
                                $repair->repair_code,
                                'Thu hồi', // Mô tả cố định theo yêu cầu
                                [
                                    'repair_id' => $repair->id,
                                    'rejected_quantity' => $rejectedDevice['quantity'] ?? 1,
                                    'total_quantity' => $rejectedDevice['total_quantity'] ?? 1,
                                    'rejected_reason' => $rejectedDevice['reason'] ?? '',
                                    'rejected_warehouse_id' => $rejectedDevice['warehouse_id'] ?? null,
                                    'warranty_code' => $repair->warranty_code,
                                    'action_type' => 'product_rejection_on_create'
                                ],
                                $rejectedDevice['reason'] ?? ''
                            );
                        } catch (\Exception $e) {
                            Log::error("Failed to create change log for rejected product: " . $e->getMessage());
                        }
                    }
                }
            }

            // Process material replacements (if any were made during the repair process)
            if ($request->has('material_replacements')) {
                $materialReplacements = json_decode($request->material_replacements, true);
                if (is_array($materialReplacements)) {
                    foreach ($materialReplacements as $replacement) {
                        // Log material replacement history
                        DB::table('material_replacement_history')->insert([
                            'repair_id' => $repair->id,
                            'device_code' => $replacement['device_code'],
                            'material_code' => $replacement['material_code'],
                            'material_name' => $replacement['material_name'],
                            'old_serials' => json_encode($replacement['old_serials']),
                            'new_serials' => json_encode($replacement['new_serials']),
                            'quantity' => $replacement['quantity'],
                            'source_warehouse_id' => $replacement['source_warehouse_id'],
                            'target_warehouse_id' => $replacement['target_warehouse_id'],
                            'notes' => $replacement['notes'] ?? '',
                            'replaced_by' => Auth::id() ?? 1,
                            'replaced_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Update warehouse materials inventory
                        // Move old materials to source warehouse
                        foreach ($replacement['old_serials'] as $oldSerial) {
                            $this->updateWarehouseMaterial(
                                $replacement['material_code'],
                                $oldSerial,
                                (int) $replacement['source_warehouse_id'],
                                'add'
                            );
                        }

                        // Remove new materials from target warehouse
                        foreach ($replacement['new_serials'] as $newSerial) {
                            $this->updateWarehouseMaterial(
                                $replacement['material_code'],
                                $newSerial,
                                (int) $replacement['target_warehouse_id'],
                                'remove'
                            );
                        }

                        // Tạo phiếu xuất kho cho vật tư thay thế
                        try {
                            $exportCode = $this->createExportSlipForReplacement($repair, $replacement);

                            // Lưu nhật ký thay đổi cho xuất kho vật tư thay thế
                            ChangeLogHelper::xuatKho(
                                $replacement['material_code'],
                                $replacement['material_name'],
                                $replacement['quantity'],
                                $exportCode,
                                'Sinh từ Phiếu sửa chữa với mã ' . $repair->repair_code,
                                [
                                    'repair_id' => $repair->id,
                                    'device_code' => $replacement['device_code'],
                                    'old_serials' => $replacement['old_serials'],
                                    'new_serials' => $replacement['new_serials'],
                                    'source_warehouse_id' => $replacement['source_warehouse_id'],
                                    'target_warehouse_id' => $replacement['target_warehouse_id'],
                                    'warranty_code' => $repair->warranty_code,
                                    'action_type' => 'material_replacement_on_create'
                                ],
                                $replacement['notes'] ?? ''
                            );
                        } catch (\Exception $e) {
                            Log::error("Failed to create change log for material replacement: " . $e->getMessage());
                        }
                    }
                }
            }

            // Process damaged materials (ghi chú sửa chữa vật tư) nếu có trong lúc tạo
            if ($request->has('damaged_materials') && !empty($request->damaged_materials)) {
                $damagedMaterials = json_decode($request->damaged_materials, true);
                if (is_array($damagedMaterials)) {
                    $this->processDamagedMaterials($repair, $damagedMaterials);
                }
            }

            DB::commit();

            // Verify repair still exists after commit
            $repair->refresh();
            if (!$repair->exists) {
                throw new \Exception('Phiếu sửa chữa không được lưu vào database. Vui lòng thử lại.');
            }

            // Luôn flash trước, dù trả về JSON hay redirect
            session()->flash('success', 'Phiếu sửa chữa đã được tạo thành công!');

            // Hỗ trợ cả AJAX (fetch) và điều hướng thông thường
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Phiếu sửa chữa đã được tạo thành công!',
                    'repair_id' => $repair->id,
                    'repair_code' => $repair->repair_code,
                    'redirect' => route('repairs.index')
                ]);
            }

            return redirect()->route('repairs.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Validation error creating repair:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating repair: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            $errorMessage = 'Có lỗi xảy ra khi tạo phiếu sửa chữa: ' . $e->getMessage();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => $errorMessage]);
        }
    }

    /**
     * Revert material replacements when deleting repair
     */
    private function revertMaterialReplacements($repair)
    {
        try {
            // Get all replacement histories for this repair
            $replacements = MaterialReplacementHistory::where('repair_id', $repair->id)->get();

            foreach ($replacements as $replacement) {
                Log::info("Reverting material replacement", [
                    'repair_id' => $repair->id,
                    'device_code' => $replacement->device_code,
                    'material_code' => $replacement->material_code,
                    'old_serials' => $replacement->old_serials,
                    'new_serials' => $replacement->new_serials,
                    'source_warehouse_id' => $replacement->source_warehouse_id,
                    'target_warehouse_id' => $replacement->target_warehouse_id
                ]);

                // Revert: Remove old serials from source warehouse (they were added there)
                foreach ($replacement->old_serials as $oldSerial) {
                    $this->updateWarehouseMaterial(
                        $replacement->material_code,
                        $oldSerial,
                        $replacement->source_warehouse_id,
                        'remove'
                    );
                }

                // Revert: Add new serials back to target warehouse (they were removed from there)
                foreach ($replacement->new_serials as $newSerial) {
                    $this->updateWarehouseMaterial(
                        $replacement->material_code,
                        $newSerial,
                        $replacement->target_warehouse_id,
                        'add'
                    );
                }
            }

            // Delete replacement history records
            MaterialReplacementHistory::where('repair_id', $repair->id)->delete();

            Log::info("Successfully reverted all material replacements for repair: " . $repair->id);
        } catch (\Exception $e) {
            Log::error('Error reverting material replacements: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update warehouse material inventory
     */
    private function updateWarehouseMaterial($materialCode, $serial, $warehouseId, $action = 'add')
    {
        try {
            $material = Material::where('code', $materialCode)->first();
            if (!$material) {
                Log::warning("Material not found: {$materialCode}");
                return;
            }

            // Always treat serial as string to avoid [222] vs ["222"] discrepancies
            $serial = (string) $serial;

            if ($action === 'add') {
                // Add material to warehouse
                if ($serial === 'N/A' || $serial === '') {
                    // For non-serial materials, increment quantity
                    $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $warehouseId)
                        ->where('material_id', $material->id)
                        ->where('serial_number', $serial)
                        ->where('item_type', 'material')
                        ->first();

                    if ($warehouseMaterial) {
                        $warehouseMaterial->increment('quantity');
                    } else {
                        WarehouseMaterial::create([
                            'warehouse_id' => $warehouseId,
                            'material_id' => $material->id,
                            'serial_number' => $serial,
                            'item_type' => 'material',
                            'quantity' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } else {
                    // For serial materials: consolidate all serial rows into a single JSON-array row
                    $rows = WarehouseMaterial::where('warehouse_id', $warehouseId)
                        ->where('material_id', $material->id)
                        ->where('item_type', 'material')
                        ->where(function ($q) {
                            $q->whereNull('serial_number')
                                ->orWhere('serial_number', '!=', 'N/A')
                                ->orWhereRaw("JSON_VALID(serial_number)");
                        })
                        ->get();

                    $serialSet = [];
                    foreach ($rows as $row) {
                        if (is_null($row->serial_number) || $row->serial_number === '' || $row->serial_number === 'N/A') {
                            continue;
                        }
                        $decoded = json_decode($row->serial_number, true);
                        if (is_array($decoded)) {
                            foreach ($decoded as $s) {
                                $serialSet[(string) $s] = true;
                            }
                        } else {
                            $serialSet[(string) $row->serial_number] = true;
                        }
                    }

                    if (!isset($serialSet[(string) $serial])) {
                        $serialSet[(string) $serial] = true;
                    }

                    $allSerials = array_map('strval', array_keys($serialSet));

                    // Choose base row or create new
                    $baseRow = $rows->first();
                    if (!$baseRow) {
                        $baseRow = new WarehouseMaterial();
                        $baseRow->warehouse_id = $warehouseId;
                        $baseRow->material_id = $material->id;
                        $baseRow->item_type = 'material';
                    }

                    $baseRow->serial_number = json_encode(array_values(array_map('strval', $allSerials)));
                    $baseRow->quantity = count($allSerials);
                    $baseRow->save();

                    // Delete other duplicate rows
                    foreach ($rows as $row) {
                        if ($row->id !== $baseRow->id) {
                            $row->delete();
                        }
                    }

                    Log::info('Consolidated serials into base warehouse material row', [
                        'material_id' => $material->id,
                        'warehouse_id' => $warehouseId,
                        'serials' => array_values(array_map('strval', $allSerials)),
                        'quantity' => $baseRow->quantity,
                    ]);
                }
            } elseif ($action === 'remove') {
                // Remove material from warehouse
                Log::info("Removing material from warehouse", [
                    'material_code' => $materialCode,
                    'serial' => $serial,
                    'warehouse_id' => $warehouseId,
                    'action' => $action
                ]);

                if ($serial === 'N/A' || $serial === '') {
                    // For non-serial materials, decrease quantity
                    $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $warehouseId)
                        ->where('material_id', $material->id)
                        ->where('serial_number', $serial)
                        ->where('item_type', 'material')
                        ->first();

                    if ($warehouseMaterial) {
                        Log::info("Found warehouse material for non-serial", [
                            'current_quantity' => $warehouseMaterial->quantity,
                            'material_id' => $material->id
                        ]);

                        if ($warehouseMaterial->quantity > 1) {
                            $warehouseMaterial->decrement('quantity');
                            Log::info("Decremented quantity to: " . ($warehouseMaterial->quantity - 1));
                        } else {
                            $warehouseMaterial->delete();
                            Log::info("Deleted warehouse material record");
                        }
                    } else {
                        Log::warning("Warehouse material not found for non-serial", [
                            'material_id' => $material->id,
                            'warehouse_id' => $warehouseId,
                            'serial' => $serial
                        ]);
                    }
                } else {
                    // For serial materials: consolidate, then remove from the set and persist
                    Log::info('Attempting to remove serial from warehouse material', [
                        'material_id' => $material->id,
                        'warehouse_id' => $warehouseId,
                        'serial' => $serial,
                    ]);

                    $rows = WarehouseMaterial::where('warehouse_id', $warehouseId)
                        ->where('material_id', $material->id)
                        ->where('item_type', 'material')
                        ->where(function ($q) {
                            $q->whereNull('serial_number')
                                ->orWhere('serial_number', '!=', 'N/A')
                                ->orWhereRaw("JSON_VALID(serial_number)");
                        })
                        ->get();

                    $serialSet = [];
                    foreach ($rows as $row) {
                        if (is_null($row->serial_number) || $row->serial_number === '' || $row->serial_number === 'N/A') {
                            continue;
                        }
                        $decoded = json_decode($row->serial_number, true);
                        if (is_array($decoded)) {
                            foreach ($decoded as $s) {
                                $serialSet[(string) $s] = true;
                            }
                        } else {
                            $serialSet[(string) $row->serial_number] = true;
                        }
                    }

                    if (isset($serialSet[(string) $serial])) {
                        unset($serialSet[(string) $serial]);
                    }

                    $allSerials = array_map('strval', array_keys($serialSet));

                    if (count($allSerials) === 0) {
                        // No serials left: delete all serial rows
                        foreach ($rows as $row) {
                            $row->delete();
                        }
                        Log::info('Removed last serial; deleted all serial rows', [
                            'material_id' => $material->id,
                            'warehouse_id' => $warehouseId,
                        ]);
                    } else {
                        // Persist into a single base row and delete duplicates
                        $baseRow = $rows->first();
                        if (!$baseRow) {
                            $baseRow = new WarehouseMaterial();
                            $baseRow->warehouse_id = $warehouseId;
                            $baseRow->material_id = $material->id;
                            $baseRow->item_type = 'material';
                        }
                        $baseRow->serial_number = json_encode(array_values(array_map('strval', $allSerials)));
                        $baseRow->quantity = count($allSerials);
                        $baseRow->save();
                        foreach ($rows as $row) {
                            if ($row->id !== $baseRow->id) {
                                $row->delete();
                            }
                        }

                        Log::info('Removed serial and consolidated remaining', [
                            'material_id' => $material->id,
                            'warehouse_id' => $warehouseId,
                            'serial_removed' => $serial,
                            'remaining_serials' => array_values(array_map('strval', $allSerials)),
                            'quantity' => $baseRow->quantity,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error updating warehouse material: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Repair $repair)
    {
        $repair->load([
            'warranty',
            'repairItems.rejectedWarehouse',
            'technician',
            'createdBy',
            'warehouse',
            'materialReplacements.sourceWarehouse',
            'materialReplacements.targetWarehouse',
            'materialReplacements.replacedBy'
        ]);

        // Ghi nhật ký xem chi tiết phiếu sửa chữa
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'repairs',
                'Xem chi tiết phiếu sửa chữa: ' . $repair->repair_code,
                null,
                $repair->toArray()
            );
        }

        return view('warranties.repair_detail', compact('repair'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Repair $repair)
    {
        $repair->load(['warranty', 'repairItems', 'materialReplacements', 'damagedMaterials']);

        // Determine which devices can be edited
        $editableDevices = $this->getEditableDevices($repair);

        return view('warranties.repair_edit', compact('repair', 'editableDevices'));
    }

    /**
     * Get devices that can be edited (not rejected or replaced)
     */
    private function getEditableDevices($repair)
    {
        $editableDevices = [];

        foreach ($repair->repairItems as $item) {
            $canEdit = true;

            // Check if device is rejected
            if ($item->device_status === 'rejected') {
                $canEdit = false;
            }

            // Check if device has material replacements
            if ($canEdit) {
                $hasReplacements = $repair->materialReplacements
                    ->where('device_code', $item->device_code)
                    ->count() > 0;

                if ($hasReplacements) {
                    $canEdit = false;
                }
            }

            $editableDevices[$item->device_code] = $canEdit;
        }

        return $editableDevices;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Repair $repair)
    {
        $request->validate([
            // Các trường bị khóa ở giao diện: không bắt buộc gửi lên
            'repair_type' => 'sometimes|in:maintenance,repair,replacement,upgrade,other',
            'repair_date' => 'sometimes|date',
            'technician_id' => 'sometimes|integer',
            'repair_description' => 'required|string',
            'repair_notes' => 'nullable|string',
            'repair_items.*.device_status' => 'nullable|in:processing,selected,rejected',
            'repair_items.*.device_notes' => 'nullable|string',
            'material_replacements' => 'nullable|string',
            'damaged_materials' => 'nullable|string',
            'photos_to_delete' => 'nullable|string',
        ]);

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $repair->toArray();

        try {
            DB::beginTransaction();

            // Handle photo deletion first
            $repairPhotos = $repair->repair_photos ?? [];
            if ($request->has('photos_to_delete') && !empty($request->photos_to_delete)) {
                $photosToDelete = json_decode($request->photos_to_delete, true);
                if ($photosToDelete && is_array($photosToDelete)) {
                    foreach ($photosToDelete as $photoPath) {
                        // Remove from array
                        $repairPhotos = array_filter($repairPhotos, function ($photo) use ($photoPath) {
                            return $photo !== $photoPath;
                        });

                        // Delete physical file
                        if (Storage::disk('public')->exists($photoPath)) {
                            Storage::disk('public')->delete($photoPath);
                        }
                    }
                    // Re-index array to remove gaps
                    $repairPhotos = array_values($repairPhotos);
                }
            }

            // Handle file uploads for repair photos
            if ($request->hasFile('repair_photos')) {
                foreach ($request->file('repair_photos') as $photo) {
                    $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $path = $photo->storeAs('repairs/photos', $filename, 'public');
                    $repairPhotos[] = $path;
                }
            }

            // Determine status based on device actions
            $newStatus = $this->determineRepairStatus($repair, $request);

            // Update repair record
            $repair->update([
                // Không cho phép thay đổi các trường khóa từ form: giữ nguyên giá trị cũ
                'repair_type' => $repair->repair_type,
                'repair_date' => $repair->repair_date,
                'technician_id' => $repair->technician_id,
                'warehouse_id' => $request->warehouse_id,
                'repair_description' => $request->repair_description,
                'repair_notes' => $request->repair_notes,
                'repair_photos' => $repairPhotos,
                'status' => $newStatus,
            ]);

            // Ghi nhật ký cập nhật phiếu sửa chữa
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'repairs',
                    'Cập nhật phiếu sửa chữa: ' . $repair->repair_code,
                    $oldData,
                    $repair->toArray()
                );
            }

            // Update device items if provided
            if ($request->has('repair_items')) {
                $this->updateRepairItems($repair, $request->repair_items);
            }

            // Handle material replacements if provided
            if ($request->has('material_replacements') && !empty($request->material_replacements)) {
                $replacements = json_decode($request->material_replacements, true);
                if ($replacements && is_array($replacements)) {
                    $this->processMaterialReplacements($repair, $replacements);
                }
            }

            // Handle damaged materials if provided
            if ($request->has('damaged_materials') && !empty($request->damaged_materials)) {
                $damagedMaterials = json_decode($request->damaged_materials, true);
                if ($damagedMaterials && is_array($damagedMaterials)) {
                    $this->processDamagedMaterials($repair, $damagedMaterials);
                }
            }

            // Update warranty serial numbers if there were material replacements
            $this->updateWarrantySerials($repair);

            DB::commit();

            return redirect()->route('repairs.show', $repair->id)
                ->with('success', 'Phiếu sửa chữa đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating repair: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Determine repair status based on device actions
     */
    private function determineRepairStatus($repair, $request)
    {
        // If user explicitly sets status, use it
        if ($request->has('status') && $request->status !== 'auto') {
            return $request->status;
        }

        // Auto-determine status based on device rejections and material replacements
        $hasProcessedActions = false;

        // Check if any device has been rejected (not just selected)
        if ($request->has('repair_items')) {
            foreach ($request->repair_items as $item) {
                if (isset($item['device_status']) && $item['device_status'] === 'rejected') {
                    $hasProcessedActions = true;
                    break;
                }
            }
        }

        // Check if any material replacements were made
        if (!$hasProcessedActions && $request->has('material_replacements') && !empty($request->material_replacements)) {
            $hasProcessedActions = true;
        }

        // Check existing repair items for rejected devices
        if (!$hasProcessedActions) {
            $repair->load('repairItems');
            foreach ($repair->repairItems as $item) {
                if ($item->device_status === 'rejected') {
                    $hasProcessedActions = true;
                    break;
                }
            }
        }

        // Check existing material replacements
        if (!$hasProcessedActions) {
            $repair->load('materialReplacements');
            if ($repair->materialReplacements->count() > 0) {
                $hasProcessedActions = true;
            }
        }

        // If there are processed actions (rejection or replacement), mark as completed, otherwise in_progress
        return $hasProcessedActions ? 'completed' : 'in_progress';
    }

    /**
     * Update repair items (devices)
     */
    private function updateRepairItems($repair, $repairItems)
    {
        foreach ($repairItems as $itemData) {
            if (isset($itemData['id'])) {
                // Update existing item
                $repairItem = RepairItem::find($itemData['id']);
                if ($repairItem) {
                    $repairItem->update([
                        'device_status' => $itemData['device_status'] ?? $repairItem->device_status,
                        'device_notes' => $itemData['device_notes'] ?? $repairItem->device_notes,
                        'rejected_reason' => $itemData['rejected_reason'] ?? null,
                        'rejected_warehouse_id' => $itemData['rejected_warehouse_id'] ?? null,
                        'rejected_at' => ($itemData['device_status'] === 'rejected') ? now() : null,
                    ]);
                }
            }
        }
    }

    /**
     * Process material replacements and update serials
     */
    private function processMaterialReplacements($repair, $replacements)
    {
        foreach ($replacements as $replacement) {
            // Create replacement history record
            MaterialReplacementHistory::create([
                'repair_id' => $repair->id,
                'device_code' => $replacement['device_code'],
                'material_code' => $replacement['material_code'],
                'material_name' => $replacement['material_name'],
                'old_serials' => $replacement['old_serials'],
                'new_serials' => $replacement['new_serials'],
                'quantity' => $replacement['quantity'],
                'source_warehouse_id' => $replacement['source_warehouse_id'],
                'target_warehouse_id' => $replacement['target_warehouse_id'],
                'notes' => $replacement['notes'] ?? '',
                'replaced_by' => Auth::id(),
                'replaced_at' => now(),
            ]);

            // Update warehouse materials inventory
            // Move old materials to source warehouse
            Log::info("Processing material replacement", [
                'device_code' => $replacement['device_code'],
                'material_code' => $replacement['material_code'],
                'old_serials' => $replacement['old_serials'],
                'new_serials' => $replacement['new_serials'],
                'source_warehouse_id' => $replacement['source_warehouse_id'],
                'target_warehouse_id' => $replacement['target_warehouse_id']
            ]);

            foreach ($replacement['old_serials'] as $oldSerial) {
                Log::info("Adding old serial to source warehouse", [
                    'serial' => $oldSerial,
                    'warehouse_id' => $replacement['source_warehouse_id']
                ]);
                $this->updateWarehouseMaterial(
                    $replacement['material_code'],
                    $oldSerial,
                    (int) $replacement['source_warehouse_id'],
                    'add'
                );
            }

            // Remove new materials from target warehouse
            foreach ($replacement['new_serials'] as $newSerial) {
                Log::info("Removing new serial from target warehouse", [
                    'serial' => $newSerial,
                    'warehouse_id' => $replacement['target_warehouse_id']
                ]);
                $this->updateWarehouseMaterial(
                    $replacement['material_code'],
                    $newSerial,
                    (int) $replacement['target_warehouse_id'],
                    'remove'
                );
            }

            // Tạo phiếu xuất kho cho vật tư thay thế
            try {
                $exportCode = $this->createExportSlipForReplacement($repair, $replacement);

                // Lưu nhật ký thay đổi cho xuất kho vật tư thay thế
                ChangeLogHelper::xuatKho(
                    $replacement['material_code'],
                    $replacement['material_name'],
                    $replacement['quantity'],
                    $exportCode,
                    'Sinh từ Phiếu sửa chữa với mã ' . $repair->repair_code,
                    [
                        'repair_id' => $repair->id,
                        'device_code' => $replacement['device_code'],
                        'new_serials' => $replacement['new_serials'],
                        'target_warehouse_id' => $replacement['target_warehouse_id'],
                        'warranty_code' => $repair->warranty_code,
                        'action_type' => 'material_replacement_export'
                    ],
                    $replacement['notes'] ?? ''
                );

                // Lưu nhật ký thay đổi cho thu hồi vật tư cũ
                if ($repair->warranty) {
                    $warranty = $repair->warranty;

                    // Xác định loại item để hiển thị chính xác
                    $itemTypeLabel = '';
                    $itemType = $replacement['item_type'] ?? 'material';
                    switch ($itemType) {
                        case 'material':
                            $itemTypeLabel = 'vật tư';
                            break;
                        case 'product':
                            $itemTypeLabel = 'thành phẩm';
                            break;
                        case 'good':
                            $itemTypeLabel = 'hàng hóa';
                            break;
                        default:
                            $itemTypeLabel = 'vật tư';
                            break;
                    }

                    // Tạo description cho thu hồi
                    $description = '';
                    if ($warranty->item_type === 'project' && $warranty->item_id) {
                        $project = \App\Models\Project::find($warranty->item_id);
                        $description = "Thu hồi {$itemTypeLabel} từ dự án: " . ($project ? $project->project_name : 'Không xác định');
                    } elseif ($warranty->item_type === 'rental' && $warranty->item_id) {
                        $rental = \App\Models\Rental::find($warranty->item_id);
                        $description = "Thu hồi {$itemTypeLabel} từ phiếu cho thuê: " . ($rental ? $rental->rental_name : 'Không xác định');
                    } else {
                        $description = "Thu hồi {$itemTypeLabel} từ phiếu sửa chữa: {$repair->repair_code}";
                    }

                    // Tạo mã thu hồi tự động
                    $recallCode = 'TH' . date('ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                    ChangeLogHelper::thuHoi(
                        $replacement['material_code'],
                        $replacement['material_name'],
                        $replacement['quantity'],
                        $recallCode,
                        $description,
                        [
                            'repair_id' => $repair->id,
                            'repair_code' => $repair->repair_code,
                            'device_code' => $replacement['device_code'],
                            'source_warehouse_id' => $replacement['source_warehouse_id'],
                            'warranty_code' => $repair->warranty_code,
                            'action_type' => 'material_recall_from_replacement',
                            'old_serials' => $replacement['old_serials']
                        ],
                        "Thu hồi {$itemTypeLabel} - Lý do thay thế: " . ($replacement['notes'] ?? 'Thay thế vật tư')
                    );
                }
            } catch (\Exception $e) {
                Log::error("Failed to create change log for material replacement: " . $e->getMessage());
            }
        }
    }

    /**
     * Tạo phiếu xuất kho cho vật tư thay thế
     */
    private function createExportSlipForReplacement($repair, $replacement)
    {
        // Tạo mã phiếu xuất kho tự động
        $exportCode = 'XK' . date('ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Tạo phiếu xuất kho
        $dispatch = \App\Models\Dispatch::create([
            'dispatch_code' => $exportCode,
            'dispatch_date' => now(),
            'dispatch_type' => 'project', // Sử dụng 'project' thay vì 'repair' vì enum chỉ chấp nhận 3 giá trị
            'dispatch_detail' => 'all', // Sử dụng 'all' thay vì 'Vật tư thay thế cho sửa chữa' vì enum chỉ chấp nhận 3 giá trị
            'project_id' => null,
            'project_receiver' => 'Sửa chữa: ' . $repair->repair_code,
            'warranty_period' => null,
            'company_representative_id' => Auth::id(),
            'dispatch_note' => 'Sinh từ phiếu sửa chữa: ' . $repair->repair_code,
            'status' => 'approved', // Tự động duyệt
            'created_by' => Auth::id(),
        ]);

        // Tạo item trong phiếu xuất kho
        \App\Models\DispatchItem::create([
            'dispatch_id' => $dispatch->id,
            'item_type' => 'material',
            'item_id' => $this->getMaterialIdByCode($replacement['material_code']),
            'quantity' => $replacement['quantity'],
            'warehouse_id' => $replacement['target_warehouse_id'], // Thêm warehouse_id
            'category' => 'general',
            'serial_numbers' => $replacement['new_serials'], // Không cần json_encode vì model đã cast thành array
            'notes' => $replacement['notes'] ?? 'Vật tư thay thế từ phiếu sửa chữa',
        ]);

        return $exportCode;
    }

    /**
     * Lấy ID của material theo code
     */
    private function getMaterialIdByCode($materialCode)
    {
        $material = \App\Models\Material::where('code', $materialCode)->first();
        return $material ? $material->id : null;
    }

    /**
     * Process damaged materials and save to database
     */
    private function processDamagedMaterials($repair, $damagedMaterials)
    {
        // Delete existing damaged materials for this repair
        \App\Models\DamagedMaterial::where('repair_id', $repair->id)->delete();

        // Track processed combinations to avoid duplicates
        $processedCombinations = [];

        // Create new damaged material records
        foreach ($damagedMaterials as $damaged) {
            // Xử lý serial - nếu rỗng thì set null thay vì empty string
            $serial = !empty($damaged['serial']) ? $damaged['serial'] : null;

            // Tạo key để kiểm tra duplicate
            $combinationKey = $repair->id . '-' . $damaged['device_code'] . '-' . $damaged['material_code'] . '-' . ($serial ?? '');

            // Kiểm tra nếu đã xử lý combination này
            if (in_array($combinationKey, $processedCombinations)) {
                Log::warning("Skipping duplicate damaged material combination: {$combinationKey}");
                continue;
            }

            $processedCombinations[] = $combinationKey;

            \App\Models\DamagedMaterial::create([
                'repair_id' => $repair->id,
                'device_code' => $damaged['device_code'],
                'material_code' => $damaged['material_code'],
                'material_name' => $damaged['material_name'],
                'serial' => $serial,
                'damage_description' => $damaged['damage_description'] ?? '',
                'reported_by' => Auth::id(),
                'reported_at' => now(),
            ]);
        }
    }

    /**
     * Update warranty serial numbers after material replacements
     */
    private function updateWarrantySerials($repair)
    {
        if (!$repair->warranty) {
            return;
        }

        $warranty = $repair->warranty;
        $replacements = $repair->materialReplacements;

        if ($replacements->isEmpty()) {
            return;
        }

        // For project-type warranties, update dispatch items
        if ($warranty->item_type === 'project' && $warranty->item_id) {
            $this->updateProjectDispatchSerials($warranty, $replacements);
        } else {
            // For single-item warranties, update warranty serial_number field
            $this->updateSingleWarrantySerial($warranty, $replacements);
        }
    }

    /**
     * Update dispatch item serials for project warranties
     */
    private function updateProjectDispatchSerials($warranty, $replacements)
    {
        foreach ($replacements as $replacement) {
            // Find dispatch items for this product
            $dispatchItems = DispatchItem::whereHas('dispatch', function ($query) use ($warranty) {
                $query->where('project_id', $warranty->item_id);
            })
                ->where('item_type', 'product')
                ->whereHas('product', function ($query) use ($replacement) {
                    $query->where('code', $replacement->device_code);
                })
                ->where('category', 'contract')
                ->get();

            foreach ($dispatchItems as $dispatchItem) {
                $currentSerials = $dispatchItem->serial_numbers ?: [];

                // Replace old serials with new ones
                foreach ($replacement->old_serials as $index => $oldSerial) {
                    $serialIndex = array_search($oldSerial, $currentSerials);
                    if ($serialIndex !== false && isset($replacement->new_serials[$index])) {
                        $currentSerials[$serialIndex] = $replacement->new_serials[$index];
                    }
                }

                // Update dispatch item
                $dispatchItem->update([
                    'serial_numbers' => $currentSerials
                ]);
            }
        }
    }

    /**
     * Update single warranty serial number
     */
    private function updateSingleWarrantySerial($warranty, $replacements)
    {
        foreach ($replacements as $replacement) {
            // For single-item warranties, only update if the device code matches
            if ($warranty->item && $warranty->item->code === $replacement->device_code) {
                // If warranty has a single serial and it matches the old serial being replaced
                if (!empty($replacement->old_serials) && !empty($replacement->new_serials)) {
                    $oldSerial = $replacement->old_serials[0];
                    $newSerial = $replacement->new_serials[0];

                    if ($warranty->serial_number === $oldSerial) {
                        $warranty->update([
                            'serial_number' => $newSerial
                        ]);
                    }
                }
            }
        }
    }

    /**
     * API: Update device status in repair
     */
    public function updateDeviceStatus(Request $request)
    {
        $request->validate([
            'repair_id' => 'required|integer|exists:repairs,id',
            'device_id' => 'required|string',
            'status' => 'required|in:selected,rejected',
            'notes' => 'nullable|string',
            'rejected_reason' => 'nullable|string',
            'rejected_warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'rejected_quantity' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $repair = Repair::find($request->repair_id);

            // Find or create repair item for this device
            $repairItem = RepairItem::where('repair_id', $repair->id)
                ->where('device_code', $request->device_id)
                ->first();

            if (!$repairItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thiết bị trong phiếu sửa chữa'
                ]);
            }

            // Update device status
            $updateData = [
                'device_status' => $request->status,
                'device_notes' => $request->notes,
            ];

            if ($request->status === 'rejected') {
                $updateData['rejected_reason'] = $request->rejected_reason;
                $updateData['rejected_warehouse_id'] = $request->rejected_warehouse_id;
                $updateData['rejected_at'] = now();

                // Cập nhật số lượng từ chối nếu có
                if ($request->has('rejected_quantity')) {
                    $updateData['device_quantity'] = $request->rejected_quantity;
                }
            }

            $repairItem->update($updateData);

            // Lưu nhật ký thay đổi khi từ chối thành phẩm
            if ($request->status === 'rejected') {
                try {
                    $rejectedQuantity = $request->rejected_quantity ?? $repairItem->device_quantity;

                    ChangeLogHelper::suaChua(
                        $repairItem->device_code,
                        $repairItem->device_name,
                        $rejectedQuantity,
                        $repair->repair_code,
                        'Thu hồi', // Mô tả cố định theo yêu cầu
                        [
                            'repair_id' => $repair->id,
                            'device_serial' => $repairItem->device_serial,
                            'rejected_quantity' => $rejectedQuantity,
                            'rejected_reason' => $request->rejected_reason,
                            'rejected_warehouse_id' => $request->rejected_warehouse_id,
                            'warranty_code' => $repair->warranty_code,
                            'action_type' => 'product_rejection'
                        ],
                        $request->notes ?? $request->rejected_reason
                    );
                } catch (\Exception $e) {
                    Log::error("Failed to create change log for product rejection: " . $e->getMessage());
                }
            }

            // Auto-update repair status based on device actions
            $this->autoUpdateRepairStatus($repair);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Trạng thái thiết bị đã được cập nhật',
                'device_status' => $repairItem->device_status_label,
                'repair_status' => $repair->fresh()->status_label
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating device status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Auto-update repair status based on device rejections and material replacements
     */
    private function autoUpdateRepairStatus($repair)
    {
        $repair->load(['repairItems', 'materialReplacements']);

        $hasProcessedActions = false;

        // Check if any device has been rejected (not just selected)
        foreach ($repair->repairItems as $item) {
            if ($item->device_status === 'rejected') {
                $hasProcessedActions = true;
                break;
            }
        }

        // Check if any material replacements exist
        if (!$hasProcessedActions && $repair->materialReplacements->count() > 0) {
            $hasProcessedActions = true;
        }

        // Update repair status
        $newStatus = $hasProcessedActions ? 'completed' : 'in_progress';
        if ($repair->status !== $newStatus) {
            $repair->update(['status' => $newStatus]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Repair $repair)
    {
        // Lưu dữ liệu cũ trước khi xóa
        $oldData = $repair->toArray();
        $repairCode = $repair->repair_code;

        try {
            DB::beginTransaction();

            // 1) Khôi phục tồn kho/serial và xóa phiếu xuất kho liên quan đến phiếu sửa chữa này
            $relatedDispatches = \App\Models\Dispatch::where('dispatch_note', 'like', "%Sinh từ Phiếu sửa chữa với mã %{$repairCode}%")
                ->get();

            foreach ($relatedDispatches as $dispatch) {
                $dispatch->load('items');
                foreach ($dispatch->items as $di) {
                    $itemType = $di->item_type ?? 'material';

                    $wm = \App\Models\WarehouseMaterial::firstOrCreate([
                        'warehouse_id' => $di->warehouse_id,
                        'material_id' => $di->item_id,
                        'item_type' => $itemType,
                    ], [
                        'quantity' => 0
                    ]);
                    $wm->quantity = (int) $wm->quantity + (int) $di->quantity;
                    $wm->save();

                    // Trả lại serials nếu có
                    $serials = [];
                    if (is_array($di->serial_numbers)) {
                        $serials = $di->serial_numbers;
                    } elseif (!empty($di->serial_numbers)) {
                        $decoded = json_decode($di->serial_numbers, true);
                        if (is_array($decoded)) {
                            $serials = $decoded;
                        }
                    }

                    foreach ($serials as $sn) {
                        \App\Models\Serial::updateOrCreate([
                            'serial_number' => $sn,
                            'type' => $itemType,
                            'product_id' => $di->item_id,
                            'warehouse_id' => $di->warehouse_id,
                        ], [
                            'status' => 'active'
                        ]);
                    }
                }

                // Xóa chi tiết và phiếu xuất kho
                $dispatch->items()->delete();
                $dispatch->delete();
            }

            // 2) Xóa ChangeLog liên quan
            \App\Models\ChangeLog::where('description', 'like', "%Sinh từ Phiếu sửa chữa với mã %{$repairCode}%")->delete();

            // 3) Xóa file đính kèm
            if ($repair->repair_photos) {
                foreach ($repair->repair_photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            foreach ($repair->repairItems as $item) {
                if ($item->device_images) {
                    foreach ($item->device_images as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            // 4) Hoàn tác việc thay thế serial
            $this->revertMaterialReplacements($repair);

            // 5) Xóa phiếu sửa chữa
            $repair->delete();

            // 5) Ghi nhật ký
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'repairs',
                    'Xóa phiếu sửa chữa (đã hoàn tác tồn kho & phiếu xuất): ' . $repairCode,
                    $oldData,
                    null
                );
            }

            DB::commit();

            return redirect()->route('repairs.index')
                ->with('success', 'Đã xóa phiếu và hoàn tác tồn kho/serial, phiếu xuất, nhật ký.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
}
