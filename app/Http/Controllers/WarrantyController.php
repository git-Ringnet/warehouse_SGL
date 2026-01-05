<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarrantyController extends Controller
{
    /**
     * Display a listing of warranties.
     */
    public function index(Request $request)
    {
        $query = Warranty::with(['dispatch.project.customer', 'dispatch.rental.customer', 'dispatchItem', 'creator']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('warranty_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('customer_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('project_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('serial_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('warranty_start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('warranty_end_date', '<=', $request->date_to);
        }

        $warranties = $query->orderBy('warranty_start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('warranties.index', compact('warranties'));
    }

    /**
     * Display the specified warranty.
     */
    public function show(Warranty $warranty)
    {
        // Eager load tất cả relationships cần thiết để tránh N+1 query
        $warranty->load([
            'dispatch.project.customer',
            'dispatch.rental.customer',
            'dispatch.items' => function ($query) {
                $query->where('category', '!=', 'backup')
                    ->whereIn('item_type', ['product', 'good']);
            },
            'dispatch.items.product',
            'dispatch.items.good',
            'dispatchItem',
            'creator',
            'product',  // Cho single item warranty
            'material', // Cho single item warranty
            'good'      // Cho single item warranty
        ]);

        // Pre-load project items và product materials để tránh N+1 query trong view
        $projectItems = [];
        $productMaterials = [];

        if ($warranty->item_type === 'project' && $warranty->item_id) {
            // Load tất cả dispatches của project một lần
            // Loại trừ phiếu xuất vật tư từ lắp ráp, chỉ lấy phiếu thành phẩm từ kiểm thử
            $projectDispatches = \App\Models\Dispatch::where('project_id', $warranty->item_id)
                ->whereIn('status', ['approved', 'completed'])
                ->when($warranty->dispatch, function ($query) use ($warranty) {
                    if ($warranty->dispatch->dispatch_type) {
                        $query->where('dispatch_type', $warranty->dispatch->dispatch_type);
                    }
                })
                ->where(function ($q) {
                    // Phiếu xuất từ kiểm thử (chứa thành phẩm đã qua QA)
                    $q->where('dispatch_note', 'like', '%Sinh từ phiếu kiểm thử%')
                        // Hoặc phiếu xuất trực tiếp (không qua lắp ráp/kiểm thử)
                        ->orWhere(function ($subQ) {
                        $subQ->where('dispatch_note', 'not like', '%Sinh từ phiếu lắp ráp%')
                            ->where('dispatch_note', 'not like', '%Sinh từ phiếu kiểm thử%');
                    })
                        // Hoặc dispatch_note là null (phiếu xuất thủ công)
                        ->orWhereNull('dispatch_note');
                })
                ->with([
                    'items' => function ($query) {
                        $query->where('category', '!=', 'backup');
                    },
                    'items.product',
                    'items.good',
                    'items.material'
                ])
                ->get();

            // Build project items
            foreach ($projectDispatches as $dispatch) {
                // Pre-fetch device codes for this dispatch to update serials
                $dispatchDeviceCodes = \App\Models\DeviceCode::where('dispatch_id', $dispatch->id)->get();
                $serialMap = []; // old_serial => new_serial
                foreach ($dispatchDeviceCodes as $dc) {
                    if (!empty($dc->old_serial) && !empty($dc->serial_main) && $dc->old_serial !== $dc->serial_main) {
                        $serialMap[$dc->old_serial] = $dc->serial_main;
                    }
                }

                \Log::info('DEBUG SERIAL MAP', [
                    'dispatch_id' => $dispatch->id,
                    'found_device_codes' => $dispatchDeviceCodes->count(),
                    'map_content' => $serialMap
                ]);

                foreach ($dispatch->items as $dispatchItem) {
                    $itemDetails = null;
                    switch ($dispatchItem->item_type) {
                        case 'material':
                            $itemDetails = $dispatchItem->material;
                            break;
                        case 'product':
                            $itemDetails = $dispatchItem->product;
                            break;
                        case 'good':
                            $itemDetails = $dispatchItem->good;
                            break;
                    }

                    if ($itemDetails) {
                        // Cập nhật serials từ map
                        $rawSerials = $dispatchItem->serial_numbers;
                        $currentSerials = is_array($rawSerials) ? $rawSerials : explode(',', $rawSerials);
                        $currentSerials = array_map('trim', $currentSerials);

                        $origSerials = $currentSerials;

                        if (!empty($serialMap)) {
                            foreach ($currentSerials as $k => $v) {
                                if (isset($serialMap[$v])) {
                                    $currentSerials[$k] = $serialMap[$v];
                                }
                            }
                        }

                        // Loại bỏ rỗng
                        $currentSerials = array_filter($currentSerials, function ($value) {
                            return !is_null($value) && trim($value) !== '';
                        });

                        // Re-index
                        $currentSerials = array_values($currentSerials);

                        $projectItems[] = [
                            'code' => $itemDetails->code,
                            'name' => $itemDetails->name,
                            'quantity' => $dispatchItem->quantity,
                            'type' => $dispatchItem->item_type,
                            'serial_numbers' => $currentSerials,
                            'dispatch_item_id' => $dispatchItem->id,
                            'dispatch_id' => $dispatch->id,
                        ];
                    }
                }
            }

            // Build product materials với batch query
            $productItems = collect($projectItems)->where('type', 'product');

            \Log::info('DEBUG PRODUCT ITEMS', [
                'count' => $productItems->count(),
                'first_item' => $productItems->first(),
                'all_types' => collect($projectItems)->pluck('type')->unique()
            ]);

            if ($productItems->isNotEmpty()) {
                // Collect tất cả serial numbers
                // Collect tất cả serial numbers
                $allSerials = [];
                foreach ($productItems as $item) {
                    $itemRaw = $item['serial_numbers'];
                    $serials = is_array($itemRaw) ? $itemRaw : explode(',', $itemRaw);

                    // Log raw info to debug missing items
                    \Log::info('DEBUG ALL SERIALS LOOP', [
                        'code' => $item['code'],
                        'raw_type' => gettype($itemRaw),
                        'raw_content' => $itemRaw
                    ]);

                    foreach ($serials as $s) {
                        $s = trim($s);
                        if ($s !== '') {
                            $allSerials[] = $s;
                        }
                    }
                }
                $allSerials = array_unique($allSerials);
                // end foreach


                // Batch load assembly products có chứa serial numbers cần tìm
                if (!empty($allSerials)) {
                    // Mở rộng phạm vi tìm kiếm AssemblyProduct:
                    // Vì AssemblyProduct có thể lưu serial cũ (vd: a111), còn $allSerials chứa serial mới (vd: a111123456)
                    // nên cần tìm thêm old_serial tương ứng để query.
                    $lookupSerials = $allSerials;
                    $relatedOldSerials = \App\Models\DeviceCode::whereIn('serial_main', $allSerials)
                        ->whereNotNull('old_serial')
                        ->pluck('old_serial')
                        ->toArray();

                    if (!empty($relatedOldSerials)) {
                        $lookupSerials = array_merge($lookupSerials, $relatedOldSerials);
                    }

                    // Tìm assembly products với danh sách serial mở rộng
                    $assemblyProducts = \App\Models\AssemblyProduct::where(function ($q) use ($lookupSerials) {
                        foreach ($lookupSerials as $serial) {
                            $q->orWhere('serials', 'like', "%$serial%"); // Dùng LIKE để tìm trong chuỗi serials (csv)
                        }
                    })->with(['assembly.materials.material'])->get();

                    // Group materials by serial
                    $materialsBySerial = [];
                    foreach ($assemblyProducts as $assemblyProduct) {
                        $serials = $assemblyProduct->serials ? explode(',', $assemblyProduct->serials) : [];
                        foreach ($serials as $serialIndex => $serial) {
                            $serial = trim($serial);
                            $displaySerial = $serial;
                            $isFound = false;

                            // Case 1: Serial khớp trực tiếp
                            if (in_array($serial, $allSerials)) {
                                $isFound = true;
                            }
                            // Case 2: Serial cũ khớp với serial mới trong danh sách hiển thị (thông qua DeviceCode)
                            else {
                                // Tìm xem serial này có phải là old_serial của một device code nào đó trong ds hiển thị không
                                $mappedDevice = \App\Models\DeviceCode::where('old_serial', $serial)
                                    ->where('item_id', $assemblyProduct->product_id)
                                    ->first();

                                if ($serial === 'b222' || $serial === 'b222 ') {
                                    \Log::info('DEBUG MAPPING CHECK', [
                                        'serial_Assembly' => $serial,
                                        'product_id' => $assemblyProduct->product_id,
                                        'device_found' => $mappedDevice ? 'YES' : 'NO',
                                        'device_main' => $mappedDevice ? $mappedDevice->serial_main : 'N/A',
                                        'in_all_serials' => ($mappedDevice && in_array($mappedDevice->serial_main, $allSerials)) ? 'YES' : 'NO'
                                    ]);
                                }

                                if ($mappedDevice && in_array($mappedDevice->serial_main, $allSerials)) {
                                    $displaySerial = $mappedDevice->serial_main;
                                    $isFound = true;
                                    // Cập nhật serial để logic phía dưới dùng serial mới (quan trọng để tìm device code ở bước sau)
                                    $serial = $displaySerial;
                                }
                            }

                            // LOG DEBUG CHO TỪNG SERIAL
                            \Log::info('Checking serial in loop', [
                                'orig' => $origSerial ?? 'N/A',
                                'current' => $serial,
                                'display' => $displaySerial,
                                'is_found' => $isFound ? 'YES' : 'NO',
                                'in_all_serials' => in_array($serial, $allSerials) ? 'YES' : 'NO'
                            ]);

                            if ($isFound && $assemblyProduct->assembly) {
                                if (!isset($materialsBySerial[$displaySerial])) {
                                    $materialsBySerial[$displaySerial] = [
                                        'product' => $assemblyProduct,
                                        'materials' => []
                                    ];
                                }

                                // product_unit = serialIndex (vị trí trong danh sách serial gốc của assembly)
                                $productUnit = $serialIndex;

                                // ===== TÌM DEVICE CODE ĐỂ LẤY SERIAL VẬT TƯ ĐÃ CẬP NHẬT =====
                                $deviceCode = \App\Models\DeviceCode::where('item_id', $assemblyProduct->product_id)
                                    ->where(function ($q) use ($serial) {
                                        $q->where('serial_main', $serial)
                                            ->orWhere('old_serial', $serial);
                                    })
                                    ->first();

                                // Parse serial_components từ DeviceCode
                                $deviceCodeSerials = [];
                                if ($deviceCode && $deviceCode->serial_components) {
                                    $rawValue = $deviceCode->serial_components;
                                    if (is_array($rawValue)) {
                                        $deviceCodeSerials = $rawValue;
                                    } elseif (is_string($rawValue)) {
                                        $decoded = json_decode($rawValue, true);
                                        if (is_array($decoded)) {
                                            $deviceCodeSerials = $decoded;
                                        } elseif (is_string($decoded)) {
                                            // Double-encoded
                                            $decoded2 = json_decode($decoded, true);
                                            if (is_array($decoded2)) {
                                                $deviceCodeSerials = $decoded2;
                                            }
                                        }
                                    }
                                }

                                // Lấy materials từ assembly CHỉ cho product_unit này
                                $materials = \App\Models\AssemblyMaterial::where('assembly_id', $assemblyProduct->assembly_id)
                                    ->where('target_product_id', $assemblyProduct->product_id)
                                    ->where('product_unit', $productUnit)
                                    ->with('material')
                                    ->orderBy('id', 'asc') // Đảm bảo thứ tự nhất quán
                                    ->get();

                                \Log::info('DEBUG MATERIALS', [
                                    'orig_serial' => $originalSerial ?? 'N/A', // $serial has been updated, so use fallback or capture earlier
                                    'mapped_serial' => $serial,
                                    'assembly_id' => $assemblyProduct->assembly_id,
                                    'product_id' => $assemblyProduct->product_id,
                                    'product_unit' => $productUnit,
                                    'materials_count' => $materials->count(),
                                    'device_code_found' => $deviceCode ? 'YES' : 'NO',
                                    'device_code_serials_count' => count($deviceCodeSerials)
                                ]);

                                // Tạo flatList để map với serial_components
                                $flatList = [];
                                $groupedByMaterial = [];
                                foreach ($materials as $am) {
                                    if ($am->material) {
                                        $mid = $am->material_id;
                                        if (!isset($groupedByMaterial[$mid])) {
                                            $groupedByMaterial[$mid] = [
                                                'material' => $am->material,
                                                'assembly_code' => $assemblyProduct->assembly->code ?? 'N/A',
                                                'total_qty' => 0,
                                                'assembly_serials' => [] // Serials từ assembly
                                            ];
                                        }
                                        $groupedByMaterial[$mid]['total_qty'] += $am->quantity;
                                        if (!empty($am->serial)) {
                                            $parts = array_map('trim', explode(',', $am->serial));
                                            foreach ($parts as $p) {
                                                if (!empty($p)) {
                                                    $groupedByMaterial[$mid]['assembly_serials'][] = $p;
                                                }
                                            }
                                        }
                                    }
                                }

                                // Sort theo material_id để đảm bảo thứ tự nhất quán với DeviceCodeController
                                ksort($groupedByMaterial);

                                // Flatten và map serial
                                $flatIndex = 0;
                                foreach ($groupedByMaterial as $mid => $data) {
                                    $matCode = $data['material']->code;
                                    $matName = $data['material']->name;
                                    $qty = $data['total_qty'];
                                    $assemblySerials = $data['assembly_serials'];
                                    $materialUnit = $data['material']->unit ?? '';

                                    $serialsList = [];
                                    $typeIndex = 1; // 1-based index for map keys

                                    // Parse map (if JSON string)
                                    $deviceCodeMap = [];
                                    if ($deviceCode && !empty($deviceCode->serial_components_map)) {
                                        $mapRaw = $deviceCode->serial_components_map;
                                        $deviceCodeMap = is_array($mapRaw) ? $mapRaw : json_decode($mapRaw, true);
                                    }

                                    // ===== KIỂM TRA ĐƠN VỊ GỘP =====
                                    // Các đơn vị chiều dài/cân nặng được gộp thành 1 trường nhập liệu trong form
                                    $consolidatedUnits = ['mm', 'cm', 'm', 'Mm', 'Cm', 'M', 'g', 'kg', 'G', 'Kg', 'KG'];
                                    $isConsolidatedUnit = in_array($materialUnit, $consolidatedUnits);

                                    // Nếu là đơn vị gộp, chỉ tìm 1 serial (key _1)
                                    $loopCount = $isConsolidatedUnit ? 1 : $qty;

                                    for ($i = 0; $i < $loopCount; $i++) {
                                        $mapKey = $matCode . '_' . $typeIndex;
                                        $s = 'N/A';

                                        // Priority 1: Map (Exact match) - STRICT MODE (No fallback if map exists)
                                        if (!empty($deviceCodeMap)) {
                                            if (isset($deviceCodeMap[$mapKey])) {
                                                $val = trim($deviceCodeMap[$mapKey]);
                                                $s = !empty($val) ? $val : 'N/A';
                                            }
                                            // Nếu có map mà không tìm thấy key -> Coi như N/A (Không fallback về flat list để tránh sai lệch)
                                        }
                                        // Priority 2: Flat List (Legacy - Only if map is empty)
                                        elseif (!empty($deviceCodeSerials) && isset($deviceCodeSerials[$flatIndex])) {
                                            $val = trim($deviceCodeSerials[$flatIndex]);
                                            $s = !empty($val) ? $val : 'N/A';
                                        }
                                        // Priority 3: Assembly Serial
                                        elseif (isset($assemblySerials[$i]) && !empty($assemblySerials[$i])) {
                                            $s = $assemblySerials[$i];
                                        }

                                        $serialsList[] = $s;

                                        $flatIndex++;
                                        $typeIndex++;
                                    }

                                    // Điều chỉnh quantity hiển thị cho đơn vị gộp
                                    $displayQty = $isConsolidatedUnit ? $qty . ' ' . $materialUnit : $qty;

                                    $materialsBySerial[$serial]['materials'][$matCode] = [
                                        'code' => $matCode,
                                        'name' => $matName,
                                        'quantity' => $displayQty,
                                        'assembly_code' => $data['assembly_code'],
                                        'serial' => implode(', ', $serialsList)
                                    ];
                                }
                            }
                        }
                    }



                    // Build product materials array
                    foreach ($productItems as $item) {
                        $serials = is_array($item['serial_numbers']) ? $item['serial_numbers'] : [$item['serial_numbers']];
                        foreach ($serials as $serial) {
                            if (!empty($serial) && isset($materialsBySerial[$serial])) {
                                $productMaterials[] = [
                                    'product_code' => $item['code'],
                                    'product_name' => $item['name'],
                                    'serial_number' => $serial,
                                    'materials' => array_values($materialsBySerial[$serial]['materials'])
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Ghi nhật ký xem chi tiết bảo hành
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'warranties',
                'Xem chi tiết bảo hành: ' . $warranty->warranty_code,
                null,
                $warranty->toArray()
            );
        }

        return view('warranties.show', compact('warranty', 'projectItems', 'productMaterials'));
    }

    /**
     * Check warranty status by code (public endpoint).
     */
    public function check($warrantyCode)
    {
        $warranty = Warranty::where('warranty_code', $warrantyCode)
            ->with([
                'dispatch.project.customer',
                'dispatch.rental.customer',
                'dispatch.items' => function ($query) {
                    $query->where('category', '!=', 'backup')
                        ->whereIn('item_type', ['product', 'good']);
                },
                'dispatch.items.product',
                'dispatch.items.good',
                'dispatchItem',
                'creator'
            ])
            ->first();

        if (!$warranty) {
            return view('warranties.verify', [
                'warranty' => null,
                'message' => 'Không tìm thấy thông tin bảo hành với mã: ' . $warrantyCode
            ]);
        }

        // Load the item relationship dynamically based on item_type
        switch ($warranty->item_type) {
            case 'product':
                $warranty->load(['product']);
                break;
            case 'material':
                $warranty->load(['material']);
                break;
            case 'good':
                $warranty->load(['good']);
                break;
        }

        return view('warranties.verify', compact('warranty'));
    }

    /**
     * API endpoint to check warranty status
     */
    public function apiCheck(Request $request)
    {
        $warrantyCode = $request->get('warranty_code');

        if (!$warrantyCode) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập mã bảo hành'
            ]);
        }

        // Eager load relationships để lấy thông tin chính xác
        $warranty = Warranty::where('warranty_code', $warrantyCode)
            ->with(['dispatch.project.customer', 'dispatch.rental.customer'])
            ->first();

        if (!$warranty) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin bảo hành'
            ]);
        }

        // Lấy thông tin khách hàng chính xác từ relationship
        $customerName = $warranty->customer_name; // Fallback
        $projectName = $warranty->project_name;   // Fallback

        if ($warranty->dispatch) {
            if ($warranty->dispatch->dispatch_type === 'rental' && $warranty->dispatch->rental) {
                // Cho phiếu thuê: lấy từ rental
                $rental = $warranty->dispatch->rental;
                $projectName = $rental->rental_name ?? $warranty->project_name;

                // Lấy tên khách hàng từ customer relationship
                if ($rental->customer) {
                    $customer = $rental->customer;
                    $companyName = $customer->company_name ?? '';
                    $representativeName = $customer->name ?? '';

                    if ($companyName && $representativeName) {
                        $customerName = $companyName . ' (' . $representativeName . ')';
                    } elseif ($companyName) {
                        $customerName = $companyName;
                    } elseif ($representativeName) {
                        $customerName = $representativeName;
                    }
                }
            } elseif ($warranty->dispatch->project) {
                // Cho phiếu dự án: lấy từ project và customer
                $project = $warranty->dispatch->project;
                $projectName = $project->project_name ?? $warranty->project_name;

                if ($project->customer) {
                    $customer = $project->customer;
                    $companyName = $customer->company_name ?? '';
                    $representativeName = $customer->name ?? '';

                    if ($companyName && $representativeName) {
                        $customerName = $companyName . ' (' . $representativeName . ')';
                    } elseif ($companyName) {
                        $customerName = $companyName;
                    } elseif ($representativeName) {
                        $customerName = $representativeName;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'warranty' => [
                'warranty_code' => $warranty->warranty_code,
                'customer_name' => $customerName,
                'project_name' => $projectName,
                'item_name' => $warranty->item->name ?? 'N/A',
                'item_code' => $warranty->item->code ?? 'N/A',
                'serial_number' => $warranty->serial_number,
                'purchase_date' => $warranty->purchase_date->format('d/m/Y'),
                'warranty_start_date' => $warranty->warranty_start_date->format('d/m/Y'),
                'warranty_end_date' => $warranty->warranty_end_date->format('d/m/Y'),
                'warranty_period_months' => $warranty->warranty_period_months,
                'status' => $warranty->status,
                'status_label' => $warranty->status_label,
                'status_color' => $warranty->status_color,
                'is_active' => $warranty->is_active,
                'remaining_days' => $warranty->remaining_days,
                'warranty_terms' => $warranty->warranty_terms,
            ]
        ]);
    }

    /**
     * Get warranties for a specific dispatch
     */
    public function getDispatchWarranties($dispatchId)
    {
        $warranties = Warranty::where('dispatch_id', $dispatchId)
            ->with(['dispatchItem'])
            ->get();

        return response()->json([
            'success' => true,
            'warranties' => $warranties
        ]);
    }

    /**
     * API endpoint to get warranty items
     */
    public function getWarrantyItems($warrantyId)
    {
        try {
            $warranty = Warranty::with([
                'dispatch.project.customer',
                'dispatch.items' => function ($query) {
                    $query->where('category', 'contract');
                },
                'dispatch.items.product',
                'dispatch.items.material',
                'dispatch.items.good'
            ])
                ->findOrFail($warrantyId);

            $items = [];

            if ($warranty->dispatch && $warranty->dispatch->items) {
                foreach ($warranty->dispatch->items as $item) {
                    // Lấy thông tin chi tiết dựa vào loại item
                    $itemDetail = null;
                    $itemName = '';
                    $itemType = '';
                    $serialNumber = '';

                    switch ($item->item_type) {
                        case 'product':
                            if ($item->product) {
                                $itemDetail = $item->product;
                                $itemName = $itemDetail->name;
                                $itemType = 'Thiết bị';
                                $serialNumber = $item->serial_numbers;
                            }
                            break;
                        case 'material':
                            if ($item->material) {
                                $itemDetail = $item->material;
                                $itemName = $itemDetail->name;
                                $itemType = 'Vật tư';
                                $serialNumber = $item->serial_numbers;
                            }
                            break;
                        case 'good':
                            if ($item->good) {
                                $itemDetail = $item->good;
                                $itemName = $itemDetail->name;
                                $itemType = 'Hàng hóa';
                                $serialNumber = $item->serial_numbers;
                            }
                            break;
                    }

                    if ($itemDetail) {
                        $items[] = [
                            'id' => $item->id,
                            'code' => $itemDetail->code ?? '',
                            'name' => $itemName,
                            'type' => $itemType,
                            'serial_number' => $serialNumber
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách thiết bị: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Export warranty to PDF
     * Route: GET /api/warranties/{warranty_code}/export-pdf
     */
    public function exportPdf($warrantyCode)
    {
        try {
            // Tìm warranty theo code
            $warranty = Warranty::where('warranty_code', $warrantyCode)
                ->with(['dispatch.project', 'dispatch.rental', 'dispatch.items.product', 'dispatch.items.good', 'creator'])
                ->first();

            if (!$warranty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy dữ liệu bảo hành để xuất file.'
                ], 404);
            }

            // Load item relationship
            switch ($warranty->item_type) {
                case 'product':
                    $warranty->load(['product']);
                    break;
                case 'material':
                    $warranty->load(['material']);
                    break;
                case 'good':
                    $warranty->load(['good']);
                    break;
            }

            // Tạo tên file
            $fileName = 'Phieu_Bao_Hanh_' . $warranty->warranty_code . '.pdf';
            $filePath = 'exports/warranties/' . $fileName;

            // Tạo PDF
            $pdf = \PDF::loadView('exports.warranty_pdf', ['warranty' => $warranty]);

            // Lưu file vào storage/app/public
            \Storage::disk('public')->put($filePath, $pdf->output());

            // Lấy thông tin file
            $fileSize = \Storage::disk('public')->size($filePath);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            // Tạo URL download (expires sau 1 giờ)
            $downloadUrl = url('storage/' . $filePath);

            return response()->json([
                'success' => true,
                'message' => 'Tạo file PDF thành công',
                'data' => [
                    'file_name' => $fileName,
                    'file_type' => 'pdf',
                    'file_size' => $fileSizeMB . ' MB',
                    'download_url' => $downloadUrl,
                    'expires_in' => 3600 // 1 hour
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Export warranty PDF error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo file PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
