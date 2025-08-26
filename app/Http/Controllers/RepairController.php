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
            $query->whereDate('repair_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('repair_date', '<=', $request->date_to);
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
     * API: Search warranty by code or serial number
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
            // Tìm bảo hành (chính xác) theo mã bảo hành hoặc serial thiết bị (hợp đồng)
            $input = trim($warrantyCode);
            $normalizedSerial = strtoupper(preg_replace('/[\s-]+/', '', $input));
            
            Log::info('🔍 Searching warranty with:', [
                'input' => $input,
                'normalizedSerial' => $normalizedSerial
            ]);

            $warranty = Warranty::where('status', 'active')
                ->where(function ($q) use ($input, $normalizedSerial) {
                    $q->where('warranty_code', $input)
                        // Match serial_number của bảo hành đơn lẻ (exact, bỏ khoảng trắng và '-')
                        ->orWhere(function ($qq) use ($normalizedSerial) {
                            $qq->whereNotNull('serial_number')
                                ->whereRaw('UPPER(REPLACE(REPLACE(serial_number, " ", ""), "-", "")) = ?', [$normalizedSerial]);
                        })
                        // Match serial nằm trong dispatch items của warranty (tất cả categories)
                        ->orWhereHas('dispatch.items', function ($qi) use ($input, $normalizedSerial) {
                            $qi->whereIn('item_type', ['product', 'good'])
                                ->where(function ($qj) use ($input, $normalizedSerial) {
                                    // JSON_CONTAINS (whereJsonContains) khi column là JSON; fallback JSON_SEARCH
                                    $qj->whereJsonContains('serial_numbers', $input)
                                        ->orWhereRaw('JSON_SEARCH(serial_numbers, "one", ?) IS NOT NULL', [$input])
                                        ->orWhereRaw('JSON_SEARCH(serial_numbers, "one", ?) IS NOT NULL', [$normalizedSerial]);
                                });
                        })
                        // Match serial trong tất cả dispatch của dự án (nếu warranty có project_id)
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
                Log::info('🔍 Trying to find warranty by serial in project warranties...');
                
                $allProjectWarranties = Warranty::where('status', 'active')
                    ->where('item_type', 'project')
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
                                Log::info('✅ Found warranty by serial in project:', [
                                    'warranty_code' => $warranty->warranty_code,
                                    'project_id' => $warranty->item_id,
                                    'serial' => $serial
                                ]);
                                break 3;
                            }
                        }
                    }
                }
            }

            if (!$warranty) {
                Log::info('❌ No warranty found for:', [
                    'input' => $input,
                    'normalizedSerial' => $normalizedSerial
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin bảo hành với mã: ' . $warrantyCode
                ]);
            }


            
            Log::info('✅ Found warranty:', [
                'warranty_code' => $warranty->warranty_code,
                'customer_name' => $warranty->customer_name,
                'project_items_count' => count($warranty->project_items ?? [])
            ]);

            // Kiểm tra trạng thái bảo hành
            if ($warranty->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bảo hành không còn hiệu lực. Trạng thái: ' . $warranty->status_label
                ]);
            }

            // Nguồn dữ liệu CHÍNH: tổng hợp từ tất cả phiếu xuất của dự án (đã loại trừ backup)
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
                if (isset($indexed[$code])) {
                    // Gộp số lượng và serial từ nhiều phiếu
                    $mergedSerials = array_unique(array_merge($indexed[$code]['serial_numbers'], $serialNumbers));
                    $indexed[$code]['quantity'] += $quantity; // cộng dồn số lượng
                    $indexed[$code]['serial_numbers'] = $mergedSerials;
                    $indexed[$code]['serial_numbers_text'] = !empty($mergedSerials) ? implode(', ', $mergedSerials) : 'N/A';
                } else {
                    $indexed[$code] = [
                        'id' => $code . '_' . microtime(true) . '_' . uniqid(),
                        'code' => $code,
                        'name' => $name,
                        'quantity' => $quantity,
                        'serial' => $serialNumbers[0] ?? '',
                        'serial_numbers' => $serialNumbers,
                        'serial_numbers_text' => !empty($serialNumbers) ? implode(', ', $serialNumbers) : 'N/A',
                        'status' => 'active',
                        'type' => $pi['type'],
                        'source' => 'contract', // Đánh dấu nguồn từ hợp đồng
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
                    
                    if (isset($indexed[$code])) {
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
                    } else {
                        // Thêm thiết bị mới từ phiếu gốc (chủ yếu là backup)
                        $itemDetails = $it->product ?? $it->good;
                        if ($itemDetails) {
                            $indexed[$code] = [
                                'id' => $code . '_' . microtime(true) . '_' . uniqid(),
                                'code' => $code,
                                'name' => $itemDetails->name,
                                'quantity' => $it->quantity,
                                'serial' => $serialNumbers[0] ?? '',
                                'serial_numbers' => $serialNumbers,
                                'serial_numbers_text' => !empty($serialNumbers) ? implode(', ', $serialNumbers) : 'N/A',
                                'status' => 'active',
                                'type' => $it->item_type,
                                'source' => $category, // Đánh dấu nguồn từ backup hoặc contract
                            ];
                        }
                    }
                }
            }

            // Bổ sung thiết bị từ các dispatch khác của dự án (nếu có)
            if ($warranty->dispatch && $warranty->dispatch->project) {
                $project = $warranty->dispatch->project;
                $allProjectDispatches = $project->dispatches()
                    ->whereIn('status', ['approved', 'completed'])
                    ->with(['items.product', 'items.good'])
                    ->get();
                
                foreach ($allProjectDispatches as $projectDispatch) {
                    // Bỏ qua dispatch hiện tại vì đã xử lý ở trên
                    if ($projectDispatch->id === $warranty->dispatch->id) {
                        continue;
                    }
                    
                    $projectItems = $projectDispatch->items()
                        ->whereIn('item_type', ['product', 'good'])
                        ->whereIn('category', ['contract', 'backup'])
                        ->get();
                    
                    foreach ($projectItems as $it) {
                        $code = $it->product->code ?? $it->good->code ?? '';
                        if (!$code) {
                            continue;
                        }
                        
                        $category = $it->category;
                        $serialNumbers = $it->serial_numbers ?: [];
                        
                        if (isset($indexed[$code])) {
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
                        } else {
                            // Thêm thiết bị mới từ dispatch khác của dự án
                            $itemDetails = $it->product ?? $it->good;
                            if ($itemDetails) {
                                $indexed[$code] = [
                                    'id' => $code . '_' . microtime(true) . '_' . uniqid(),
                                    'code' => $code,
                                    'name' => $itemDetails->name,
                                    'quantity' => $it->quantity,
                                    'serial' => $serialNumbers[0] ?? '',
                                    'serial_numbers' => $serialNumbers,
                                    'serial_numbers_text' => !empty($serialNumbers) ? implode(', ', $serialNumbers) : 'N/A',
                                    'status' => 'active',
                                    'type' => $it->item_type,
                                    'source' => $category, // Đánh dấu nguồn từ backup hoặc contract
                                ];
                            }
                        }
                    }
                }
            }

            $devices = array_values($indexed);
            
            Log::info('📋 Devices found before serial filter:', [
                'total_devices' => count($devices),
                'devices' => array_map(function($d) {
                    return [
                        'code' => $d['code'],
                        'name' => $d['name'],
                        'serial_numbers' => $d['serial_numbers'],
                        'source' => $d['source']
                    ];
                }, $devices)
            ]);

            // Tách thành 1 hàng/1 serial thiết bị (nếu có danh sách serial)
            $expandedDevices = [];
            foreach ($devices as $d) {
                $serials = is_array($d['serial_numbers'] ?? null) ? $d['serial_numbers'] : [];
                if (!empty($serials)) {
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
                } else {
                    // Không có serial -> vẫn giữ một hàng (serial = N/A)
                    $expandedDevices[] = [
                        'id' => $d['id'],
                        'code' => $d['code'],
                        'name' => $d['name'],
                        'quantity' => 1,
                        'serial' => $d['serial'] ?? '',
                        'serial_numbers' => [],
                        'serial_numbers_text' => $d['serial_numbers_text'] ?? 'N/A',
                        'status' => $d['status'] ?? 'active',
                        'type' => $d['type'] ?? 'product',
                        'source' => $d['source'] ?? 'contract',
                    ];
                }
            }
            $devices = $expandedDevices;

            // Nếu input là serial (khác mã bảo hành), lọc chỉ còn thiết bị chứa đúng serial đó
            $isSerialSearch = strcasecmp($input, $warranty->warranty_code) !== 0;
            Log::info('🔍 Serial search info:', [
                'isSerialSearch' => $isSerialSearch,
                'input' => $input,
                'warranty_code' => $warranty->warranty_code,
                'devices_before_filter' => count($devices)
            ]);
            
            if ($isSerialSearch && !empty($normalizedSerial)) {
                $devices = array_values(array_filter($devices, function ($d) use ($normalizedSerial) {
                    $serials = $d['serial_numbers'] ?? [];
                    foreach ($serials as $s) {
                        $ns = strtoupper(preg_replace('/[\s-]+/', '', $s));
                        if ($ns === $normalizedSerial) return true;
                    }
                    if (!empty($d['serial'])) {
                        $ns2 = strtoupper(preg_replace('/[\s-]+/', '', $d['serial']));
                        if ($ns2 === $normalizedSerial) return true;
                    }
                    return false;
                }));
                
                Log::info('🔍 After serial filter:', [
                    'devices_after_filter' => count($devices),
                    'filtered_devices' => array_map(function($d) {
                        return [
                            'code' => $d['code'],
                            'serial_numbers' => $d['serial_numbers']
                        ];
                    }, $devices)
                ]);
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

            // Lấy lịch sử sửa chữa
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
     * API: Get device materials for repair
     */
    public function getDeviceMaterials(Request $request)
    {
        $deviceId = $request->get('device_id');
        $warrantyCode = $request->get('warranty_code');
        $deviceCode = $request->get('device_code'); // Thêm device_code để hỗ trợ thiết bị từ kho

        if (!$deviceId && !$deviceCode) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu thông tin device_id hoặc device_code'
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
                $materials = [[
                    'id' => $good->id,
                    'code' => $good->code,
                    'name' => $good->name,
                    'quantity' => 1,
                    'serial' => $deviceSerial,
                    'current_serials' => [$deviceSerial],
                    'status' => 'active',
                    'is_good' => true
                ]];
                
                return response()->json([
                    'success' => true,
                    'materials' => $materials
                ]);
            }
            
            // Regular product handling (existing code)
            // Parse device code from device_id (format: CODE_timestamp_random)
            $deviceCode = $deviceCode ?: (explode('_', $deviceId)[0] ?? $deviceId);
            $deviceSerial = '';

            // Extract device serial from device_id if available
            $deviceIdParts = explode('_', $deviceId);
            if (count($deviceIdParts) >= 2) {
                $deviceSerial = $deviceIdParts[1];
            }

            // Tìm product theo code
            $product = Product::where('code', $deviceCode)->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin sản phẩm'
                ]);
            }

            // Lấy danh sách vật tư từ assembly_materials (nguồn chính xác nhất)
            $materials = $this->getDeviceMaterialsFromAssembly($product);

            // Cập nhật serial từ replacement history nếu có warranty_code
            if ($warrantyCode && !empty($materials)) {
                $materials = $this->updateMaterialsSerialsFromHistory($materials, $deviceCode, $warrantyCode);
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

            Log::info("Found " . count($materials) . " materials from warranty for device {$deviceCode}");
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
                $query->whereJsonContains('old_serials', $originalSerial)
                    ->orWhereRaw('JSON_SEARCH(old_serials, "one", ?) IS NOT NULL', [$originalSerial]);
            })
            ->orderBy('replaced_at', 'desc')
            ->first();

        if ($latestReplacement && !empty($latestReplacement->new_serials)) {
            // Find which new serial corresponds to the original serial
            $oldSerials = $latestReplacement->old_serials;
            $newSerials = $latestReplacement->new_serials;

            $index = array_search($originalSerial, $oldSerials);
            if ($index !== false && isset($newSerials[$index])) {
                return $newSerials[$index];
            }
        }

        return $originalSerial;
    }

    /**
     * Get device materials from assembly (main source)
     */
    private function getDeviceMaterialsFromAssembly($product)
    {
        $materials = [];
        
        try {
            Log::info("Looking for materials for product: {$product->code} (ID: {$product->id})");
            
            // First try to get materials from assembly_materials table
        $assemblyMaterials = \App\Models\AssemblyMaterial::where('target_product_id', $product->id)
            ->with(['material', 'assembly'])
            ->get();

            Log::info("Found {$assemblyMaterials->count()} assembly materials for product {$product->code}");
            
        foreach ($assemblyMaterials as $am) {
            if ($am->material) {
                $materials[] = [
                    'id' => $am->material->id,
                    'code' => $am->material->code,
                    'name' => $am->material->name,
                    'quantity' => $am->quantity,
                    'serial' => $am->serial ?? '',
                    'current_serials' => [$am->serial ?? ''],
                    'status' => 'active'
                ];
                    Log::info("Added material: {$am->material->code} - {$am->material->name} from assembly");
                }
            }
            
            // If no materials found from assembly_materials, try to get from product_materials
            if (empty($materials)) {
                Log::info("No assembly materials found, checking product_materials for {$product->code}");
                
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
                        Log::info("Added material: {$pm->material->code} - {$pm->material->name} from product_materials");
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error getting materials for product {$product->code}: " . $e->getMessage());
        }

        Log::info("Found " . count($materials) . " materials for product {$product->code}");
        return $materials;
    }

    /**
     * Update materials serials from replacement history
     */
    private function updateMaterialsSerialsFromHistory($materials, $deviceCode, $warrantyCode)
    {
        foreach ($materials as &$material) {
            $originalSerial = $material['serial'];

            // Handle comma-separated serials (e.g., "111,222" -> ["111", "222"])
            $originalSerials = array_map('trim', explode(',', $originalSerial));
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
                    Log::info("Updated material {$material['code']} serial from {$singleSerial} to {$currentSerial}");
                }
            }

            // Update material serial if there were changes
            if ($hasChanges) {
                $material['serial'] = implode(',', $updatedSerials);
                $material['current_serials'] = $updatedSerials;
                Log::info("Final material {$material['code']} serial: {$material['serial']}");
            }
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
                ->whereHas('product', function($q) use ($input) {
                    $q->where('status', 'active')
                      ->where('is_hidden', false)
                      ->where(function($subQ) use ($input) {
                          $subQ->where('code', 'LIKE', "%{$input}%")
                               ->orWhere('name', 'LIKE', "%{$input}%");
                      });
                })
                ->whereHas('warehouse', function($q) {
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
                        if (!$hasMatchingSerial) continue;
                    } else {
                        continue; // Không tìm thấy theo tên/mã và không phải tìm serial
                    }
                }

                // Nếu có nhiều serial, tạo từng hàng riêng biệt
                if (!empty($serialNumbers)) {
                    foreach ($serialNumbers as $serial) {
                        $devices[] = [
                            'id' => 'warehouse_product_' . $product->id . '_' . $wp->warehouse_id . '_' . $serial . '_' . microtime(true) . '_' . uniqid(),
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
                        'id' => 'warehouse_product_' . $product->id . '_' . $wp->warehouse_id . '_' . microtime(true) . '_' . uniqid(),
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
                ->whereHas('good', function($q) use ($input) {
                    $q->where('status', 'active')
                      ->where('is_hidden', false)
                      ->where(function($subQ) use ($input) {
                          $subQ->where('code', 'LIKE', "%{$input}%")
                               ->orWhere('name', 'LIKE', "%{$input}%");
                      });
                })
                ->whereHas('warehouse', function($q) {
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
                        if (!$hasMatchingSerial) continue;
                    } else {
                        continue; // Không tìm thấy theo tên/mã và không phải tìm serial
                    }
                }

                // Nếu có nhiều serial, tạo từng hàng riêng biệt
                if (!empty($serialNumbers)) {
                    foreach ($serialNumbers as $serial) {
                        $devices[] = [
                            'id' => 'warehouse_good_' . $good->id . '_' . $wg->warehouse_id . '_' . $serial . '_' . microtime(true) . '_' . uniqid(),
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
                        'id' => 'warehouse_good_' . $good->id . '_' . $wg->warehouse_id . '_' . microtime(true) . '_' . uniqid(),
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
                ->where(function($q) use ($normalizedSerial) {
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
                    $existingDevice = collect($devices)->first(function($device) use ($itemCode, $serial) {
                        return $device['code'] === $itemCode && $device['warehouse_id'] === $serial->warehouse_id;
                    });

                    if (!$existingDevice) {
                        $devices[] = [
                            'id' => 'serial_' . $itemType . '_' . $item->id . '_' . $serial->warehouse_id . '_' . microtime(true) . '_' . uniqid(),
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

            // Nếu có yêu cầu serial cụ thể
            if (!empty($requiredSerials)) {
                $availableSerials = [];
                if (!empty($warehouseMaterial->serial_number)) {
                    $warehouseSerials = json_decode($warehouseMaterial->serial_number, true);
                    if (is_array($warehouseSerials)) {
                        $availableSerials = $warehouseSerials;
                    }
                }

                // Kiểm tra xem có đủ serial yêu cầu không
                $missingSerials = array_diff($requiredSerials, $availableSerials);
                if (!empty($missingSerials)) {
                    return response()->json([
                        'success' => false,
                        'available' => false,
                        'message' => "Thiếu serial: " . implode(', ', $missingSerials)
                    ]);
                }

                // Kiểm tra số lượng serial có đủ không
                if (count($availableSerials) < count($requiredSerials)) {
                    return response()->json([
                        'success' => false,
                        'available' => false,
                        'message' => "Không đủ serial. Yêu cầu: " . count($requiredSerials) . ", Có sẵn: " . count($availableSerials)
                    ]);
                }
            } else {
                // Không yêu cầu serial cụ thể: kiểm tra tổng tồn kho
                if ($totalStock < $requiredQuantity) {
                    return response()->json([
                        'success' => false,
                        'available' => false,
                        'message' => "Không đủ tồn kho. Yêu cầu: {$requiredQuantity}, Tổng tồn: {$totalStock} (Serial: {$serialStock}, Không serial: {$nonSerialStock})"
                    ]);
                }
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

            Log::info("Found material: {$material->name} (ID: {$material->id})");

            // Lấy danh sách serial có sẵn trong kho
            $serials = [];

            // Tìm trong bảng serials
            $serialRecords = \App\Models\Serial::where('warehouse_id', $warehouseId)
                ->where('type', 'material')
                ->where('status', 'active')
                ->where('product_id', $material->id) // product_id trong serials table chính là material_id khi type = 'material'
                ->get();

            Log::info("Found {$serialRecords->count()} serials for material {$materialCode} in warehouse {$warehouseId}");

            // Lấy danh sách serial đã được sử dụng trong thay thế
            $usedSerials = \App\Models\MaterialReplacementHistory::where('material_code', $materialCode)
                ->get()
                ->pluck('new_serials')
                ->flatten()
                ->unique()
                ->toArray();

            Log::info("Found used serials for material {$materialCode}: " . json_encode($usedSerials));

            foreach ($serialRecords as $serial) {
                // Chỉ thêm serial chưa được sử dụng
                if (!in_array($serial->serial_number, $usedSerials)) {
                    $serials[] = [
                        'serial' => $serial->serial_number,
                        'status' => 'available'
                    ];
                } else {
                    Log::info("Serial {$serial->serial_number} đã được sử dụng, bỏ qua");
                }
            }

            return response()->json([
                'success' => true,
                'serials' => $serials
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
        $request->validate([
            'warranty_code' => 'nullable|string|max:255',
            'repair_type' => 'required|in:maintenance,repair,replacement,upgrade,other',
            'repair_date' => 'required|date',
            'technician_id' => 'required|integer',
            'repair_description' => 'required|string',
            'repair_notes' => 'nullable|string',
            'repair_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'selected_devices' => 'nullable|array',
            'damaged_materials' => 'nullable|string',
        ]);

        // Custom validation: phải có ít nhất một thiết bị được chọn hoặc từ chối
        if (empty($request->selected_devices) && empty($request->rejected_devices)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['selected_devices' => 'Vui lòng chọn hoặc từ chối ít nhất một thiết bị.']);
        }

        try {
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
                                  $qi->whereIn('item_type', ['product','good'])
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

            // Create repair record
            $repair = Repair::create([
                'repair_code' => Repair::generateRepairCode(),
                // If warranty resolved from serial, always use real warranty code
                'warranty_code' => $warranty ? $warranty->warranty_code : ($inputWarrantyOrSerial ?: null),
                'warranty_id' => $warranty ? $warranty->id : null,
                'repair_type' => $request->repair_type,
                'repair_date' => $request->repair_date,
                'technician_id' => $request->technician_id,
                'warehouse_id' => $request->warehouse_id ?? 1,
                'repair_description' => $request->repair_description,
                'repair_notes' => $request->repair_notes,
                'repair_photos' => $repairPhotos,
                'status' => $initialStatus,
                'created_by' => Auth::id() ?? 1,
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
                    $deviceSerial = $request->input("device_serial.{$deviceKey}") ??
                        $request->input("device_serial[{$deviceKey}]") ??
                        $request->input("device_serial.{$deviceId}") ??
                        $request->input("device_serial[{$deviceId}]") ?? '';
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

                    // Debug logging
                    Log::info('Device data from request:', [
                        'deviceId' => $deviceId,
                        'deviceKey' => $deviceKey,
                        'deviceCode' => $deviceCode,
                        'deviceName' => $deviceName,
                        'deviceSerial' => $deviceSerial,
                        'deviceQuantity' => $deviceQuantity,
                        'deviceNotes' => $deviceNotes,
                        'deviceType' => $deviceType,
                        'deviceSource' => $deviceSource
                    ]);

                    // Handle device images
                    $deviceImages = [];
                    Log::info("Checking for device images for device: {$deviceId}");

                    // Kiểm tra xem có files trong device_images array không (với escaped key)
                    $deviceImagesArray = $request->file('device_images', []);
                    $hasDeviceImages = (isset($deviceImagesArray[$deviceKey]) && !empty($deviceImagesArray[$deviceKey])) ||
                        (isset($deviceImagesArray[$deviceId]) && !empty($deviceImagesArray[$deviceId]));

                    // Ưu tiên escaped key, fallback về original key
                    $deviceImageFiles = $deviceImagesArray[$deviceKey] ?? $deviceImagesArray[$deviceId] ?? [];

                    Log::info("Device images array keys: " . json_encode(array_keys($deviceImagesArray)));
                    Log::info("Has device images for {$deviceId}: " . ($hasDeviceImages ? 'YES' : 'NO'));

                    if ($hasDeviceImages) {
                        $files = $deviceImageFiles;
                        Log::info("Found " . count($files) . " files for device {$deviceId}");

                        foreach ($files as $index => $image) {
                            if ($image->isValid()) {
                                Log::info("Processing image {$index}: " . $image->getClientOriginalName());
                                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                                $path = $image->storeAs('repairs/devices', $filename, 'public');
                                $deviceImages[] = $path;
                                Log::info("Saved device image to: {$path}");
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

                    Log::info('Final device_serial to save: "' . $deviceSerial . '"');

                    $repairItem = RepairItem::create([
                        'repair_id' => $repair->id,
                        'device_code' => $deviceCode,
                        'device_name' => $deviceName,
                        'device_serial' => $deviceSerial,
                        'device_quantity' => $deviceQuantity,
                        'device_status' => 'selected',
                        'device_notes' => $deviceNotes,
                        'device_images' => $deviceImages,
                        'device_parts' => $deviceParts,
                        'device_type' => $deviceType,
                        'device_source' => $deviceSource,
                    ]);

                    Log::info('Created RepairItem:', [
                        'id' => $repairItem->id,
                        'device_code' => $repairItem->device_code,
                        'device_name' => $repairItem->device_name,
                        'device_serial' => $repairItem->device_serial,
                        'device_quantity' => $repairItem->device_quantity,
                        'device_notes' => $repairItem->device_notes,
                        'device_images_count' => count($repairItem->device_images ?? []),
                    ]);
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

                            Log::info("Created change log for rejected product: {$rejectedDevice['code']} in repair {$repair->repair_code}");
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
                                $replacement['source_warehouse_id'],
                                'add'
                            );
                        }

                        // Remove new materials from target warehouse
                        foreach ($replacement['new_serials'] as $newSerial) {
                            $this->updateWarehouseMaterial(
                                $replacement['material_code'],
                                $newSerial,
                                $replacement['target_warehouse_id'],
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

                            Log::info("Created change log for material replacement: {$replacement['material_code']} in repair {$repair->repair_code}");
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

            // Luôn flash trước, dù trả về JSON hay redirect
            session()->flash('success', 'Phiếu sửa chữa đã được tạo thành công!');

            // Hỗ trợ cả AJAX (fetch) và điều hướng thông thường
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('repairs.index')
                ]);
            }

            return redirect()->route('repairs.index');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating repair: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
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

            if ($action === 'add') {
                // Add material to warehouse
                WarehouseMaterial::updateOrCreate(
                    [
                        'warehouse_id' => $warehouseId,
                        'material_id' => $material->id,
                        'serial_number' => $serial,
                    ],
                    [
                        'quantity' => 1,
                        'status' => 'available',
                        'updated_at' => now(),
                    ]
                );
            } elseif ($action === 'remove') {
                // Remove material from warehouse
                WarehouseMaterial::where('warehouse_id', $warehouseId)
                    ->where('material_id', $material->id)
                    ->where('serial_number', $serial)
                    ->delete();
            }

            Log::info("Updated warehouse material: {$materialCode} - {$serial} - {$action}");
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
                            Log::info("Deleted photo: {$photoPath}");
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
            foreach ($replacement['old_serials'] as $oldSerial) {
                $this->updateWarehouseMaterial(
                    $replacement['material_code'],
                    $oldSerial,
                    $replacement['source_warehouse_id'],
                    'add'
                );
            }

            // Remove new materials from target warehouse
            foreach ($replacement['new_serials'] as $newSerial) {
                $this->updateWarehouseMaterial(
                    $replacement['material_code'],
                    $newSerial,
                    $replacement['target_warehouse_id'],
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

                Log::info("Created change log for material replacement: {$replacement['material_code']} in repair {$repair->repair_code}");
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

        Log::info("Updated warranty serials for repair: {$repair->repair_code}");
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

                Log::info("Updated dispatch item {$dispatchItem->id} serials from " . json_encode($replacement->old_serials) . " to " . json_encode($replacement->new_serials));
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

                        Log::info("Updated warranty {$warranty->warranty_code} serial from {$oldSerial} to {$newSerial}");
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

                    Log::info("Created change log for product rejection: {$repairItem->device_code} in repair {$repair->repair_code}");
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
            // Delete associated files
            if ($repair->repair_photos) {
                foreach ($repair->repair_photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }

            // Delete device images
            foreach ($repair->repairItems as $item) {
                if ($item->device_images) {
                    foreach ($item->device_images as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            $repair->delete();

            // Ghi nhật ký xóa phiếu sửa chữa
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'repairs',
                    'Xóa phiếu sửa chữa: ' . $repairCode,
                    $oldData,
                    null
                );
            }

            return redirect()->route('repairs.index')
                ->with('success', 'Phiếu sửa chữa đã được xóa thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
}
