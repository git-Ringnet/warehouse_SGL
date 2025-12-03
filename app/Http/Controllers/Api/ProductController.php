<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductMaterial;

class ProductController extends Controller
{
    /**
     * Create a new product from assembly.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createFromAssembly(Request $request)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                Log::error('Unauthenticated user tried to create product from assembly');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            
            Log::info('Creating product from assembly', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            
            $request->validate([
                'original_product_id' => 'required|exists:products,id',
                'components' => 'required|array',
                'components.*.id' => 'required|exists:materials,id',
                'components.*.quantity' => 'required|numeric|min:0.01',
                'components.*.notes' => 'nullable|string',
            ]);

            // Get original product
            $originalProduct = Product::findOrFail($request->original_product_id);

            // Generate new product code and name
            $timestamp = now()->format('YmdHis');
            $newCode = $originalProduct->code . "-M" . $timestamp;
            $newName = $originalProduct->name . " (Modified)";

            DB::beginTransaction();

            // Create new product
            $newProduct = Product::create([
                'code' => $newCode,
                'name' => $newName,
                'description' => $originalProduct->description,
                'inventory_warehouses' => $originalProduct->inventory_warehouses,
                'status' => 'active',
                'is_hidden' => false
            ]);

            // Attach materials with new quantities
            $materialsData = [];
            foreach ($request->components as $material) {
                if (!empty($material['id']) && !empty($material['quantity'])) {
                    // Verify material exists
                    $materialExists = Material::find($material['id']);
                    if ($materialExists) {
                        $materialsData[$material['id']] = [
                            'quantity' => $material['quantity'],
                            'notes' => $material['notes'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
            }

            // Attach materials to product
            if (!empty($materialsData)) {
                $newProduct->materials()->attach($materialsData);
            }

            // Log activity
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'products',
                    'Tạo mới thành phẩm từ assembly: ' . $newProduct->name,
                    null,
                    $newProduct->toArray()
                );
            }

            DB::commit();

            // Load materials relationship for the response
            $newProduct->load('materials');

            return response()->json([
                'success' => true,
                'message' => 'Thành phẩm mới đã được tạo thành công.',
                'product' => $newProduct
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating product from assembly: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo thành phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed materials information for each product
     */
    public function getMaterialsCount(Request $request)
    {
        try {
            $dispatchItems = $request->input('dispatch_items', []);
            
            // Nếu có dispatch_items, lấy từ assembly_materials theo assembly_id và product_unit
            if (!empty($dispatchItems)) {
                $materialDetails = [];
                
                foreach ($dispatchItems as $item) {
                    if ($item['item_type'] === 'product' && isset($item['assembly_id']) && isset($item['product_unit'])) {
                        $assemblyId = $item['assembly_id'];
                        $productUnit = $item['product_unit'];
                        $productId = $item['item_id'];
                        
                        // Lấy vật tư từ assembly_materials theo assembly_id, product_unit và target_product_id
                        $assemblyMaterials = DB::table('assembly_materials')
                            ->join('materials', 'assembly_materials.material_id', '=', 'materials.id')
                            ->where('assembly_materials.assembly_id', $assemblyId)
                            ->where('assembly_materials.product_unit', $productUnit)
                            ->where('assembly_materials.target_product_id', $productId)
                            ->select(
                                'assembly_materials.material_id',
                                'assembly_materials.quantity',
                                'assembly_materials.serial',
                                'materials.code as material_code',
                                'materials.name as material_name'
                            )
                            ->get();
                        
                        // Gộp các dòng cùng material_id và cộng quantity
                        $groupedMaterials = [];
                        foreach ($assemblyMaterials as $material) {
                            $key = $material->material_id;
                            if (!isset($groupedMaterials[$key])) {
                                $groupedMaterials[$key] = [
                                    'material_id' => $material->material_id,
                                    'material_code' => $material->material_code,
                                    'material_name' => $material->material_name,
                                    'quantity' => 0,
                                    'serial' => $material->serial
                                ];
                            }
                            $groupedMaterials[$key]['quantity'] += $material->quantity;
                        }
                        
                        $details = [];
                        foreach ($groupedMaterials as $material) {
                            for ($i = 0; $i < $material['quantity']; $i++) {
                                $details[] = [
                                    'material_id' => $material['material_id'],
                                    'material_code' => $material['material_code'],
                                    'material_name' => $material['material_name'],
                                    'serial' => $material['serial'],
                                    'index' => $i + 1
                                ];
                            }
                        }
                        
                        $materialDetails[$productId] = $details;
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'data' => $materialDetails
                ]);
            }
            
            // Fallback: lấy từ product_materials (quan hệ cố định) nếu không có dispatch_items
            $materialDetails = ProductMaterial::select('product_id', 'material_id', 'quantity')
                ->with('material:id,code,name')
                ->get()
                ->groupBy('product_id')
                ->map(function ($materials) {
                    $details = [];
                    foreach ($materials as $material) {
                        for ($i = 0; $i < $material->quantity; $i++) {
                            $details[] = [
                                'material_id' => $material->material_id,
                                'material_code' => $material->material->code,
                                'material_name' => $material->material->name,
                                'index' => $i + 1
                            ];
                        }
                    }
                    return $details;
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $materialDetails
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin vật tư: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API chuyên dụng để lấy serial vật tư cho modal "Cập nhật mã thiết bị"
     * Sử dụng chung cho cả trạng thái pending và approved
     */
    public function getDeviceCodeMaterials(Request $request)
    {
        // Log ngay đầu method để đảm bảo method được gọi
        \Illuminate\Support\Facades\Log::info('getDeviceCodeMaterials CALLED', [
            'request_all' => $request->all(),
            'dispatch_id' => $request->get('dispatch_id'),
            'type' => $request->get('type'),
            'url' => $request->fullUrl(),
        ]);
        
        try {
            $dispatchId = $request->get('dispatch_id');
            $type = $request->get('type', 'contract'); // contract, backup, general
            
            if (!$dispatchId) {
                \Illuminate\Support\Facades\Log::warning('getDeviceCodeMaterials: Missing dispatch_id');
                return response()->json([
                    'success' => false,
                    'message' => 'dispatch_id là bắt buộc'
                ], 422);
            }

            // Lấy dispatch items theo type và category để tránh lẫn lộn giữa contract và backup
            $dispatchItems = DB::table('dispatch_items')
                ->where('dispatch_id', $dispatchId)
                ->where('item_type', 'product')
                ->where('category', $type)
                ->get();

            // Log để debug - kiểm tra xem có lấy đúng items không
            \Illuminate\Support\Facades\Log::info('getDeviceCodeMaterials', [
                'dispatch_id' => $dispatchId,
                'type' => $type,
                'items_count' => $dispatchItems->count(),
                'items' => $dispatchItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'item_id' => $item->item_id,
                        'category' => $item->category,
                        'serial_numbers' => $item->serial_numbers,
                        'product_unit' => $item->product_unit,
                        'product_unit_type' => gettype($item->product_unit),
                        'assembly_id' => $item->assembly_id,
                        'quantity' => $item->quantity,
                    ];
                })->toArray(),
                // Kiểm tra xem có items khác category không
                'all_items_same_product' => DB::table('dispatch_items')
                    ->where('dispatch_id', $dispatchId)
                    ->where('item_type', 'product')
                    ->where(function($q) use ($dispatchItems) {
                        $productIds = $dispatchItems->pluck('item_id')->unique();
                        if ($productIds->isNotEmpty()) {
                            $q->whereIn('item_id', $productIds->toArray());
                        }
                    })
                    ->select('id', 'item_id', 'category', 'serial_numbers', 'product_unit', 'assembly_id')
                    ->get()
                    ->map(function($item) {
                        return [
                            'id' => $item->id,
                            'item_id' => $item->item_id,
                            'category' => $item->category,
                            'serial_numbers' => $item->serial_numbers,
                            'product_unit' => $item->product_unit,
                            'assembly_id' => $item->assembly_id,
                        ];
                    })->toArray(),
            ]);

            $materialDetails = [];
            
            foreach ($dispatchItems as $item) {
                $productId = $item->item_id;
                $assemblyIds = $item->assembly_id;
                $productUnits = $item->product_unit;
                $serialNumbers = $item->serial_numbers;
                
                // Parse assembly_id (chuỗi) và product_unit (có thể là JSON string hoặc comma-separated string)
                $assemblyIdArray = is_string($assemblyIds) ? explode(',', $assemblyIds) : ($assemblyIds ? [$assemblyIds] : []);
                $assemblyIdArray = array_map('trim', array_filter($assemblyIdArray));
                
                // Parse product_unit - có thể là JSON string "[0,2]" hoặc comma-separated string "0,2"
                // Hoặc có thể là double-encoded như "\"1\"" (JSON string chứa string)
                $productUnitArray = [];
                if (is_string($productUnits)) {
                    $trimmed = trim($productUnits);
                    // Thử parse JSON đầu tiên (xử lý cả trường hợp double-encoded như "\"1\"" hoặc "[1]")
                    $decoded = json_decode($productUnits, true);
                    if ($decoded !== null) {
                        // Nếu decode được và là array, dùng luôn
                        if (is_array($decoded)) {
                            $productUnitArray = array_map('intval', $decoded);
                        } 
                        // Nếu decode được và là string, thử parse lại (trường hợp "\"1\"" -> "1")
                        else if (is_string($decoded)) {
                            $decodedTrimmed = trim($decoded);
                            // Kiểm tra xem có phải JSON array không
                            if (strlen($decodedTrimmed) > 0 && $decodedTrimmed[0] === '[' && substr($decodedTrimmed, -1) === ']') {
                                $decodedArray = json_decode($decodedTrimmed, true);
                                if (is_array($decodedArray)) {
                                    $productUnitArray = array_map('intval', $decodedArray);
                } else {
                                    $productUnitArray = [intval($decoded)];
                                }
                            }
                            // Hoặc thử explode nếu là comma-separated
                            else if (strpos($decodedTrimmed, ',') !== false) {
                                $parts = explode(',', $decodedTrimmed);
                                $productUnitArray = array_map('intval', array_map('trim', $parts));
                            }
                            // Nếu không, thử parse thành số
                            else {
                                $productUnitArray = [intval($decoded)];
                            }
                        }
                        // Nếu decode được và là số, dùng luôn
                        else if (is_numeric($decoded)) {
                            $productUnitArray = [intval($decoded)];
                        }
                    }
                    
                    // Nếu JSON decode không thành công hoặc chưa có kết quả, thử các cách khác
                    if (empty($productUnitArray)) {
                        // Thử parse như JSON array nếu bắt đầu bằng [
                        if (strlen($trimmed) > 0 && $trimmed[0] === '[' && substr($trimmed, -1) === ']') {
                            $decoded = json_decode($productUnits, true);
                            if (is_array($decoded)) {
                                $productUnitArray = array_map('intval', $decoded);
                            }
                        }
                        // Nếu vẫn chưa có, thử explode
                        if (empty($productUnitArray)) {
                            $parts = explode(',', $productUnits);
                            // Loại bỏ dấu ngoặc kép nếu có
                            $parts = array_map(function($part) {
                                $cleaned = trim($part, '"\'');
                                return $cleaned;
                            }, $parts);
                            $productUnitArray = array_map('intval', array_map('trim', $parts));
                        }
                    }
                } else if (is_array($productUnits)) {
                    $productUnitArray = array_map('intval', $productUnits);
                } else if ($productUnits !== null) {
                    $productUnitArray = [intval($productUnits)];
                }
                
                // Parse serial_numbers nếu là JSON string
                $serialNumbersArray = is_string($serialNumbers) ? json_decode($serialNumbers, true) : $serialNumbers;
                if (!is_array($serialNumbersArray)) {
                    $serialNumbersArray = [];
                }
                $serialNumbersArray = array_map('trim', $serialNumbersArray);
                
                // Log sau khi parse để verify
                \Illuminate\Support\Facades\Log::info('After parsing product_unit', [
                    'dispatch_item_id' => $item->id,
                    'category' => $item->category,
                    'product_id' => $productId,
                    'product_unit_raw' => $productUnits,
                    'product_unit_raw_type' => gettype($productUnits),
                    'product_unit_parsed' => $productUnitArray,
                    'serial_numbers_raw' => $serialNumbers,
                    'serial_numbers_parsed' => $serialNumbersArray,
                    'assembly_id_raw' => $assemblyIds,
                    'assembly_id_parsed' => $assemblyIdArray,
                ]);
                
                // Tạo key cho từng vị trí (kể cả khi không có serial) để phân biệt materials
                // Map đúng theo thứ tự: serial_numbers[i] tương ứng với product_unit[i]
                $productSerialDetails = [];
                $byPair = [];
                $byIndex = [];
                
                // Đảm bảo mapping đúng: mỗi serial trong serial_numbers tương ứng với product_unit cùng index
                // Duyệt theo số lượng thực tế của dòng (quantity), để các dòng N/A (không có serial) vẫn tạo entry rỗng
                $itemQuantity = (int) ($item->quantity ?? 1);
                $count = max($itemQuantity, count($serialNumbersArray), 1);
                
                \Illuminate\Support\Facades\Log::info('Before mapping loop', [
                    'dispatch_item_id' => $item->id,
                    'category' => $item->category,
                    'product_id' => $productId,
                    'serial_numbers_array' => $serialNumbersArray,
                    'product_unit_array' => $productUnitArray,
                    'assembly_id_array' => $assemblyIdArray,
                    'count' => $count,
                ]);
                
                for ($serialIndex = 0; $serialIndex < $count; $serialIndex++) {
                    $serial = $serialNumbersArray[$serialIndex] ?? '';
                    // Lấy assembly_id và product_unit theo cùng index với serial
                    // KHÔNG fallback về index 0 cho assembly_id; nếu thiếu ở index hiện tại thì coi như không có
                    $assemblyId = $assemblyIdArray[$serialIndex] ?? null;
                    $productUnit = $productUnitArray[$serialIndex] ?? 0;
                    
                    // Chuyển product_unit về integer để so sánh với database
                    $productUnit = intval($productUnit);
                    
                    // Kiểm tra xem assembly_id có hợp lệ không
                    $assemblyIdValid = $assemblyId && $assemblyId !== '0' && $assemblyId !== 0 && trim((string)$assemblyId) !== '';
                    
                    // Log để debug mapping
                    \Illuminate\Support\Facades\Log::info('Mapping serial to product_unit', [
                        'dispatch_item_id' => $item->id,
                        'category' => $item->category,
                        'product_id' => $productId,
                        'serial_index' => $serialIndex,
                        'serial' => $serial,
                        'assembly_id' => $assemblyId,
                        'assembly_id_valid' => $assemblyIdValid,
                        'assembly_id_type' => gettype($assemblyId),
                        'product_unit_raw' => $productUnitArray[$serialIndex] ?? null,
                        'product_unit' => $productUnit,
                        'product_unit_type' => gettype($productUnit),
                        'serial_numbers_array' => $serialNumbersArray,
                        'product_unit_array' => $productUnitArray,
                        'assembly_id_array' => $assemblyIdArray,
                        'will_fetch_materials' => $assemblyIdValid && $productUnit !== null,
                    ]);
                    
                    // Nếu có assembly_id và product_unit hợp lệ (không phải 0), lấy từ assembly_materials
                    // Chỉ lấy materials khi assembly_id > 0 (không phải 0, null, hoặc rỗng)
                    if ($assemblyIdValid && $productUnit !== null) {
                        // Đảm bảo product_unit là integer để so sánh đúng với database
                        $productUnitInt = intval($productUnit);
                        
                        // Kiểm tra tất cả assembly_materials với cùng assembly_id và target_product_id để debug
                        $allMaterials = DB::table('assembly_materials')
                            ->where('assembly_id', $assemblyId)
                            ->where('target_product_id', $productId)
                            ->select('id', 'material_id', 'product_unit', 'serial', 'quantity')
                            ->get();
                        
                        // Log trước khi query
                        \Illuminate\Support\Facades\Log::info('Querying assembly_materials', [
                            'assembly_id' => $assemblyId,
                            'product_unit' => $productUnit,
                            'product_unit_int' => $productUnitInt,
                            'target_product_id' => $productId,
                            'serial' => $serial,
                            'dispatch_item_id' => $item->id,
                            'category' => $item->category,
                            'all_materials_for_assembly' => $allMaterials->map(function($am) {
                                return [
                                    'id' => $am->id,
                                    'material_id' => $am->material_id,
                                    'product_unit' => $am->product_unit,
                                    'product_unit_type' => gettype($am->product_unit),
                                    'serial' => $am->serial,
                                    'quantity' => $am->quantity,
                                ];
                            })->toArray(),
                        ]);
                        
                        // Log SQL query để debug
                        $query = DB::table('assembly_materials')
                            ->join('materials', 'assembly_materials.material_id', '=', 'materials.id')
                            ->where('assembly_materials.assembly_id', $assemblyId)
                            ->where('assembly_materials.product_unit', $productUnitInt)
                            ->where('assembly_materials.target_product_id', $productId)
                            ->select(
                                'assembly_materials.id',
                                'assembly_materials.material_id',
                                'assembly_materials.quantity',
                                'assembly_materials.serial',
                                'assembly_materials.product_unit as am_product_unit',
                                'materials.code as material_code',
                                'materials.name as material_name',
                                'materials.unit as material_unit'
                            );
                        
                        $sql = $query->toSql();
                        $bindings = $query->getBindings();
                        \Illuminate\Support\Facades\Log::info('Assembly materials SQL query', [
                            'sql' => $sql,
                            'bindings' => $bindings,
                            'assembly_id' => $assemblyId,
                            'product_unit_int' => $productUnitInt,
                            'target_product_id' => $productId,
                        ]);
                        
                        $assemblyMaterials = $query->get();
                        
                        // Log kết quả query
                        \Illuminate\Support\Facades\Log::info('Assembly materials query result', [
                            'assembly_id' => $assemblyId,
                            'product_unit' => $productUnitInt,
                            'target_product_id' => $productId,
                            'found_count' => $assemblyMaterials->count(),
                            'materials' => $assemblyMaterials->map(function($am) {
                                return [
                                    'id' => $am->id,
                                    'material_id' => $am->material_id,
                                    'material_code' => $am->material_code,
                                    'quantity' => $am->quantity,
                                    'serial' => $am->serial,
                                    'am_product_unit' => $am->am_product_unit,
                                    'am_product_unit_type' => gettype($am->am_product_unit),
                                ];
                            })->toArray(),
                        ]);
                        
                        // Gộp các dòng cùng material_id và cộng quantity, giữ lại tất cả serials
                        $groupedMaterials = [];
                        foreach ($assemblyMaterials as $material) {
                            $key = $material->material_id;
                            if (!isset($groupedMaterials[$key])) {
                                $groupedMaterials[$key] = [
                                    'material_id' => $material->material_id,
                                    'material_code' => $material->material_code,
                                    'material_name' => $material->material_name,
                                    'material_unit' => $material->material_unit ?? '',
                                    'quantity' => 0,
                                    'serials' => [] // Lưu tất cả serials thay vì chỉ một serial
                                ];
                            }
                            $groupedMaterials[$key]['quantity'] += $material->quantity;
                            
                            // Thêm serials từ dòng này vào danh sách
                            if ($material->serial && $material->serial !== 'null') {
                                $serialParts = array_map('trim', explode(',', $material->serial));
                                $serialParts = array_filter($serialParts, function($s) { return !empty($s) && $s !== 'null'; });
                                $groupedMaterials[$key]['serials'] = array_merge($groupedMaterials[$key]['serials'], $serialParts);
                            }
                        }
                        
                        $details = [];
                        foreach ($groupedMaterials as $material) {
                            // Lấy tất cả serials đã gộp
                            $serialParts = isset($material['serials']) ? array_values($material['serials']) : [];
                            
                            for ($i = 0; $i < $material['quantity']; $i++) {
                                // Chỉ gán serial cho index tương ứng, nếu không có thì để trống
                                $serialValue = isset($serialParts[$i]) ? $serialParts[$i] : '';
                                
                                $details[] = [
                                    'material_id' => $material['material_id'],
                                    'material_code' => $material['material_code'],
                                    'material_name' => $material['material_name'],
                                    'material_unit' => $material['material_unit'] ?? '',
                                    'serial' => $serialValue,
                                    'index' => $i + 1
                                ];
                            }
                        }
                        
                        // Map theo serial (có thể rỗng cho N/A)
                        $productSerialDetails[$serial] = $details;
                        
                        // Map theo cặp assembly:unit để frontend tra cứu chính xác
                        // Đảm bảo assemblyId và productUnit là string và integer
                        $assemblyIdStr = (string) $assemblyId;
                        $productUnitInt = (int) $productUnit;
                        $pairKey = '$' . $assemblyIdStr . ':' . $productUnitInt;
                        $byPair[$pairKey] = $details;
                        
                        \Illuminate\Support\Facades\Log::info('Mapping materials to pair key', [
                            'dispatch_item_id' => $item->id,
                            'category' => $item->category,
                            'serial' => $serial,
                            'assembly_id' => $assemblyId,
                            'assembly_id_str' => $assemblyIdStr,
                            'product_unit' => $productUnit,
                            'product_unit_int' => $productUnitInt,
                            'pair_key' => $pairKey,
                            'materials_count' => count($details),
                        ]);
                        
                        // Map theo index để fallback
                        $byIndex[$serialIndex] = $details;
                    } else {
                        // assembly_id không hợp lệ hoặc thiếu: tìm assembly thông qua Testing với success_warehouse_id
                        $fallbackDetails = [];
                        $warehouseId = $item->warehouse_id;
                        
                        // Tìm các Testing đã hoàn thành cho product này với kho lưu thành phẩm đạt = warehouse_id của dispatch_item
                        $testings = DB::table('testings')
                            ->where('status', 'completed')
                            ->where('success_warehouse_id', $warehouseId)
                            ->whereNotNull('assembly_id')
                            ->orderBy('id', 'asc')
                            ->get();
                        
                        // Lấy assembly_products từ các Testing tìm được
                        $assemblyProducts = collect();
                        foreach ($testings as $testing) {
                            $ap = DB::table('assembly_products')
                                ->where('assembly_id', $testing->assembly_id)
                                ->where('product_id', $productId)
                                ->first();
                            if ($ap) {
                                $assemblyProducts->push($ap);
                            }
                        }
                        
                        \Illuminate\Support\Facades\Log::info('Looking for assembly for product without assembly_id via Testing', [
                            'dispatch_item_id' => $item->id,
                            'product_id' => $productId,
                            'warehouse_id' => $warehouseId,
                            'serial_index' => $serialIndex,
                            'testings_count' => $testings->count(),
                            'assembly_products_count' => $assemblyProducts->count(),
                        ]);
                        
                        $foundAssemblyMaterials = false;
                        
                        // Tạo danh sách tất cả các cặp (assembly_id, product_unit) có sẵn
                        $availableUnits = [];
                        foreach ($assemblyProducts as $ap) {
                            // Parse product_unit từ assembly_products
                            $productUnitValue = $ap->product_unit;
                            $productUnits = [];
                            
                            if (is_string($productUnitValue)) {
                                $decoded = json_decode($productUnitValue, true);
                                if (is_array($decoded)) {
                                    $productUnits = array_map('intval', $decoded);
                                } else {
                                    $productUnits = array_map('intval', array_map('trim', explode(',', $productUnitValue)));
                                }
                            } elseif (is_array($productUnitValue)) {
                                $productUnits = array_map('intval', $productUnitValue);
                            } elseif ($productUnitValue !== null) {
                                $productUnits = [intval($productUnitValue)];
                            }
                            
                            foreach ($productUnits as $pu) {
                                $availableUnits[] = [
                                    'assembly_id' => $ap->assembly_id,
                                    'product_unit' => $pu
                                ];
                            }
                        }
                        
                        // Lấy cặp (assembly_id, product_unit) tương ứng với serialIndex
                        if (isset($availableUnits[$serialIndex])) {
                            $targetAssemblyId = $availableUnits[$serialIndex]['assembly_id'];
                            $targetProductUnit = $availableUnits[$serialIndex]['product_unit'];
                            
                            // Lấy vật tư từ assembly_materials cho cặp này
                            $assemblyMaterials = DB::table('assembly_materials')
                                ->join('materials', 'assembly_materials.material_id', '=', 'materials.id')
                                ->where('assembly_materials.assembly_id', $targetAssemblyId)
                                ->where('assembly_materials.target_product_id', $productId)
                                ->where('assembly_materials.product_unit', $targetProductUnit)
                                ->select(
                                    'assembly_materials.id',
                                    'assembly_materials.material_id',
                                    'assembly_materials.quantity',
                                    'assembly_materials.serial',
                                    'assembly_materials.product_unit as am_product_unit',
                                    'materials.code as material_code',
                                    'materials.name as material_name',
                                    'materials.unit as material_unit'
                                )
                                ->get();
                            
                            if ($assemblyMaterials->isNotEmpty()) {
                                // Gộp các dòng cùng material_id
                                $groupedMaterials = [];
                                foreach ($assemblyMaterials as $material) {
                                    $key = $material->material_id;
                                    if (!isset($groupedMaterials[$key])) {
                                        $groupedMaterials[$key] = [
                                            'material_id' => $material->material_id,
                                            'material_code' => $material->material_code,
                                            'material_name' => $material->material_name,
                                            'material_unit' => $material->material_unit ?? '',
                                            'quantity' => 0,
                                            'serials' => []
                                        ];
                                    }
                                    $groupedMaterials[$key]['quantity'] += $material->quantity;
                                    
                                    if ($material->serial && $material->serial !== 'null') {
                                        $serialParts = array_map('trim', explode(',', $material->serial));
                                        $serialParts = array_filter($serialParts, function($s) { return !empty($s) && $s !== 'null'; });
                                        $groupedMaterials[$key]['serials'] = array_merge($groupedMaterials[$key]['serials'], $serialParts);
                                    }
                                }
                                
                                foreach ($groupedMaterials as $material) {
                                    $serialParts = isset($material['serials']) ? array_values($material['serials']) : [];
                                    
                                    for ($i = 0; $i < $material['quantity']; $i++) {
                                        $serialValue = isset($serialParts[$i]) ? $serialParts[$i] : '';
                                        
                                        $fallbackDetails[] = [
                                            'material_id' => $material['material_id'],
                                            'material_code' => $material['material_code'],
                                            'material_name' => $material['material_name'],
                                            'material_unit' => $material['material_unit'],
                                            'serial' => $serialValue,
                                            'index' => $i + 1
                                        ];
                                    }
                                }
                                
                                $foundAssemblyMaterials = true;
                                
                                \Illuminate\Support\Facades\Log::info('Found assembly materials for product by product_unit', [
                                    'dispatch_item_id' => $item->id,
                                    'product_id' => $productId,
                                    'serial_index' => $serialIndex,
                                    'assembly_id' => $targetAssemblyId,
                                    'product_unit' => $targetProductUnit,
                                    'materials_count' => count($fallbackDetails),
                                ]);
                            }
                        }
                        
                        // Nếu không tìm được theo product_unit, thử tìm assembly đầu tiên có vật tư
                        if (!$foundAssemblyMaterials) {
                            foreach ($assemblyProducts as $ap) {
                                $assemblyMaterials = DB::table('assembly_materials')
                                    ->join('materials', 'assembly_materials.material_id', '=', 'materials.id')
                                    ->where('assembly_materials.assembly_id', $ap->assembly_id)
                                    ->where('assembly_materials.target_product_id', $productId)
                                    ->select(
                                        'assembly_materials.id',
                                        'assembly_materials.material_id',
                                        'assembly_materials.quantity',
                                        'assembly_materials.serial',
                                        'assembly_materials.product_unit as am_product_unit',
                                        'materials.code as material_code',
                                        'materials.name as material_name',
                                        'materials.unit as material_unit'
                                    )
                                    ->get();
                                
                                if ($assemblyMaterials->isNotEmpty()) {
                                    $groupedMaterials = [];
                                    foreach ($assemblyMaterials as $material) {
                                        $key = $material->material_id;
                                        if (!isset($groupedMaterials[$key])) {
                                            $groupedMaterials[$key] = [
                                                'material_id' => $material->material_id,
                                                'material_code' => $material->material_code,
                                                'material_name' => $material->material_name,
                                                'material_unit' => $material->material_unit ?? '',
                                                'quantity' => 0,
                                                'serials' => []
                                            ];
                                        }
                                        $groupedMaterials[$key]['quantity'] += $material->quantity;
                                        
                                        if ($material->serial && $material->serial !== 'null') {
                                            $serialParts = array_map('trim', explode(',', $material->serial));
                                            $serialParts = array_filter($serialParts, function($s) { return !empty($s) && $s !== 'null'; });
                                            $groupedMaterials[$key]['serials'] = array_merge($groupedMaterials[$key]['serials'], $serialParts);
                                        }
                                    }
                                    
                                    foreach ($groupedMaterials as $material) {
                                        $serialParts = isset($material['serials']) ? array_values($material['serials']) : [];
                                        
                                        for ($i = 0; $i < $material['quantity']; $i++) {
                                            $serialValue = isset($serialParts[$i]) ? $serialParts[$i] : '';
                                            
                                            $fallbackDetails[] = [
                                                'material_id' => $material['material_id'],
                                                'material_code' => $material['material_code'],
                                                'material_name' => $material['material_name'],
                                                'material_unit' => $material['material_unit'],
                                                'serial' => $serialValue,
                                                'index' => $i + 1
                                            ];
                                        }
                                    }
                                    
                                    $foundAssemblyMaterials = true;
                                    
                                    \Illuminate\Support\Facades\Log::info('Found assembly materials for product (fallback to first assembly)', [
                                        'dispatch_item_id' => $item->id,
                                        'product_id' => $productId,
                                        'assembly_id' => $ap->assembly_id,
                                        'materials_count' => count($fallbackDetails),
                                    ]);
                                    
                                    break;
                                }
                            }
                        }
                        
                        // Nếu không tìm được assembly materials, fallback về product_materials
                        if (!$foundAssemblyMaterials) {
                            $productMaterialsFallback = DB::table('product_materials')
                                ->join('materials', 'product_materials.material_id', '=', 'materials.id')
                                ->where('product_materials.product_id', $productId)
                                ->select(
                                    'product_materials.material_id',
                                    'product_materials.quantity',
                                    'materials.code as material_code',
                                    'materials.name as material_name',
                                    'materials.unit as material_unit'
                                )
                                ->get();
                            
                            foreach ($productMaterialsFallback as $material) {
                                for ($i = 0; $i < $material->quantity; $i++) {
                                    $fallbackDetails[] = [
                                        'material_id' => $material->material_id,
                                        'material_code' => $material->material_code,
                                        'material_name' => $material->material_name,
                                        'material_unit' => $material->material_unit ?? '',
                                        'serial' => '',
                                        'index' => $i + 1
                                    ];
                                }
                            }
                            
                            \Illuminate\Support\Facades\Log::info('Fallback to product_materials (no assembly found)', [
                                'dispatch_item_id' => $item->id,
                                'product_id' => $productId,
                                'materials_count' => count($fallbackDetails),
                            ]);
                        }
                        
                        $productSerialDetails[$serial] = $fallbackDetails;
                        $byIndex[$serialIndex] = $fallbackDetails;
                    }
                }
                
                // Nếu không có assembly_id, fallback về product_materials
                if (empty($productSerialDetails)) {
                    $productMaterials = DB::table('product_materials')
                        ->join('materials', 'product_materials.material_id', '=', 'materials.id')
                        ->where('product_materials.product_id', $productId)
                        ->select(
                            'product_materials.material_id',
                            'product_materials.quantity',
                            'materials.code as material_code',
                            'materials.name as material_name',
                            'materials.unit as material_unit'
                        )
                        ->get();
                    
                    $details = [];
                    foreach ($productMaterials as $material) {
                        for ($i = 0; $i < $material->quantity; $i++) {
                            $details[] = [
                                'material_id' => $material->material_id,
                                'material_code' => $material->material_code,
                                'material_name' => $material->material_name,
                                'material_unit' => $material->material_unit ?? '',
                                'serial' => '', // Không có serial từ product_materials
                                'index' => $i + 1
                            ];
                        }
                    }
                    
                    // Gán cho tất cả serial nếu không có assembly data
                    $productSerialDetails[''] = $details;
                }
                
                // Đính kèm cấu trúc phụ để frontend có thể tra theo pair/index
                // Tạo key duy nhất kết hợp product_id và category để tránh xung đột giữa contract và backup
                $key = $productId . '_' . $item->category;
                
                // Nếu đã có key này (trường hợp hiếm: cùng product_id và category trong nhiều dispatch_items),
                // merge materials thay vì ghi đè
                if (isset($materialDetails[$key])) {
                    // Merge productSerialDetails
                    foreach ($productSerialDetails as $serial => $details) {
                        if (!isset($materialDetails[$key][$serial])) {
                            $materialDetails[$key][$serial] = $details;
                        }
                    }
                    // Merge byPair và byIndex
                    $materialDetails[$key]['__by_pair__'] = array_merge(
                        $materialDetails[$key]['__by_pair__'] ?? [],
                        $byPair
                    );
                    $materialDetails[$key]['by_index'] = array_merge(
                        $materialDetails[$key]['by_index'] ?? [],
                        $byIndex
                    );
                } else {
                    $materialDetails[$key] = array_merge($productSerialDetails, [
                        '__by_pair__' => $byPair,
                        'by_index' => $byIndex,
                    ]);
                }
                
                // Giữ lại key cũ theo productId để backward compatibility với frontend
                // Nhưng ưu tiên dữ liệu từ category hiện tại nếu có xung đột
                if (!isset($materialDetails[$productId]) || $item->category === $type) {
                    $materialDetails[$productId] = array_merge($productSerialDetails, [
                        '__by_pair__' => $byPair,
                        'by_index' => $byIndex,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $materialDetails
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin vật tư cho device code: ' . $e->getMessage()
            ], 500);
        }
    }
} 