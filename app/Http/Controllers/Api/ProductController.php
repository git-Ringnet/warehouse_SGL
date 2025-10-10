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
        try {
            $dispatchId = $request->get('dispatch_id');
            $type = $request->get('type', 'contract'); // contract, backup, general
            
            if (!$dispatchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'dispatch_id là bắt buộc'
                ], 422);
            }

            // Lấy dispatch items theo type
            $dispatchItems = DB::table('dispatch_items')
                ->where('dispatch_id', $dispatchId)
                ->where('item_type', 'product')
                ->where('category', $type)
                ->get();

            $materialDetails = [];
            
            foreach ($dispatchItems as $item) {
                $productId = $item->item_id;
                $assemblyIds = $item->assembly_id;
                $productUnits = $item->product_unit;
                $serialNumbers = $item->serial_numbers;
                
                // Parse assembly_id (chuỗi) và product_unit (JSON string)
                $assemblyIdArray = is_string($assemblyIds) ? explode(',', $assemblyIds) : [$assemblyIds];
                // product_unit lưu dạng JSON string "[0,1,2,3]"
                if (is_string($productUnits)) {
                    $decoded = json_decode($productUnits, true);
                    $productUnitArray = is_array($decoded) ? $decoded : explode(',', $productUnits);
                } else {
                    $productUnitArray = is_array($productUnits) ? $productUnits : [$productUnits];
                }
                
                // Parse serial_numbers nếu là JSON string
                $serialNumbersArray = is_string($serialNumbers) ? json_decode($serialNumbers, true) : $serialNumbers;
                if (!is_array($serialNumbersArray)) {
                    $serialNumbersArray = [];
                }
                
                // Tạo key cho từng vị trí (kể cả khi không có serial) để phân biệt materials
                $productSerialDetails = [];
                $byPair = [];
                $byIndex = [];
                
                $maxCount = max(count($assemblyIdArray), count($productUnitArray), max(1, count($serialNumbersArray)));
                for ($serialIndex = 0; $serialIndex < $maxCount; $serialIndex++) {
                    $serial = $serialNumbersArray[$serialIndex] ?? '';
                    $assemblyId = $assemblyIdArray[$serialIndex] ?? ($assemblyIdArray[0] ?? null);
                    $productUnit = $productUnitArray[$serialIndex] ?? ($productUnitArray[0] ?? 0);
                    
                    // Nếu có assembly_id và product_unit, lấy từ assembly_materials
                    if ($assemblyId && $productUnit !== null) {
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
                            // Tách serial nếu có nhiều serial phân tách bằng dấu phẩy
                            $serialParts = [];
                            if ($material['serial'] && $material['serial'] !== 'null') {
                                $serialParts = array_map('trim', explode(',', $material['serial']));
                                $serialParts = array_filter($serialParts, function($s) { return !empty($s); });
                            }
                            
                            for ($i = 0; $i < $material['quantity']; $i++) {
                                // Chỉ gán serial cho index tương ứng, nếu không có thì để trống
                                $serialValue = isset($serialParts[$i]) ? $serialParts[$i] : '';
                                
                                $details[] = [
                                    'material_id' => $material['material_id'],
                                    'material_code' => $material['material_code'],
                                    'material_name' => $material['material_name'],
                                    'serial' => $serialValue,
                                    'index' => $i + 1
                                ];
                            }
                        }
                        
                        // Map theo serial (có thể rỗng cho N/A)
                        $productSerialDetails[$serial] = $details;
                        // Map theo cặp assembly:unit để frontend tra cứu chính xác
                        $byPair['$' . $assemblyId . ':' . $productUnit] = $details;
                        // Map theo index để fallback
                        $byIndex[$serialIndex] = $details;
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
                            'materials.name as material_name'
                        )
                        ->get();
                    
                    $details = [];
                    foreach ($productMaterials as $material) {
                        for ($i = 0; $i < $material->quantity; $i++) {
                            $details[] = [
                                'material_id' => $material->material_id,
                                'material_code' => $material->material_code,
                                'material_name' => $material->material_name,
                                'serial' => '', // Không có serial từ product_materials
                                'index' => $i + 1
                            ];
                        }
                    }
                    
                    // Gán cho tất cả serial nếu không có assembly data
                    $productSerialDetails[''] = $details;
                }
                
                // Đính kèm cấu trúc phụ để frontend có thể tra theo pair/index
                $materialDetails[$productId] = array_merge($productSerialDetails, [
                    '__by_pair__' => $byPair,
                    'by_index' => $byIndex,
                ]);
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