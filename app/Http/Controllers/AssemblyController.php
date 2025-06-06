<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\AssemblyMaterial;
use App\Models\Material;
use App\Models\Product;
use App\Models\Serial;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssemblyController extends Controller
{
    /**
     * Display a listing of the assemblies.
     */
    public function index()
    {
        $assemblies = Assembly::with('product')->get();
        return view('assemble.index', compact('assemblies'));
    }

    /**
     * Show the form for creating a new assembly.
     */
    public function create()
    {
        // Get all products and materials for the form
        $products = Product::all();
        $materials = Material::all();
        $warehouses = Warehouse::all();

        return view('assemble.create', compact('products', 'materials', 'warehouses'));
    }

    /**
     * Store a newly created assembly in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'assembly_code' => 'required|unique:assemblies,code',
            'assembly_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'target_warehouse_id' => 'required|exists:warehouses,id|different:warehouse_id',
            'assigned_to' => 'required',
            'product_quantity' => 'required|integer|min:1',
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:materials,id',
            'components.*.quantity' => 'required|integer|min:1',
        ], [
            'target_warehouse_id.different' => 'Kho nhập thành phẩm phải khác với kho xuất linh kiện.',
        ]);

        DB::beginTransaction();
        try {
            $productQty = intval($request->product_quantity);

            // Xử lý product_serials
            $productSerials = $request->product_serials ?? [];
            
            // Validate that we have enough serials if product quantity > 1
            if ($productQty > 1 && count(array_filter($productSerials)) < $productQty) {
                throw new \Exception('Vui lòng nhập đủ serial cho tất cả thành phẩm.');
            }
            
            // Check for duplicate serials within the form
            $filteredSerials = array_filter($productSerials);
            if (count($filteredSerials) !== count(array_unique($filteredSerials))) {
                throw new \Exception('Không được nhập trùng serial thành phẩm.');
            }
            
            // Check for duplicate serials in the database
            if (!empty($filteredSerials)) {
                foreach ($filteredSerials as $serial) {
                    // Skip empty serials
                    if (empty($serial)) continue;
                    
                    // Find assemblies with this serial
                    $existingAssembly = Assembly::where('product_serials', 'like', '%' . $serial . '%')
                        ->where('product_id', $request->product_id)
                        ->first();
                    
                    if ($existingAssembly) {
                        throw new \Exception("Serial thành phẩm '{$serial}' đã tồn tại trong phiếu lắp ráp #{$existingAssembly->code}.");
                    }
                    
                    // Also check in the serials table
                    $existingSerial = Serial::where('serial_number', $serial)
                        ->where('product_id', $request->product_id)
                        ->first();
                        
                    if ($existingSerial) {
                        throw new \Exception("Serial '{$serial}' đã tồn tại trong cơ sở dữ liệu.");
                    }
                }
            }
            
            $productSerialsStr = !empty($filteredSerials) ? implode(',', $filteredSerials) : null;

            // Create the assembly record
            $assembly = Assembly::create([
                'code' => $request->assembly_code,
                'date' => $request->assembly_date,
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'target_warehouse_id' => $request->target_warehouse_id,
                'assigned_to' => $request->assigned_to,
                'status' => 'completed',
                'notes' => $request->assembly_note,
                'quantity' => $productQty,
                'product_serials' => $productSerialsStr,
            ]);

            // Create serial records for each product serial
            $this->createSerialRecords($filteredSerials, $request->product_id, $assembly->id);

            // Validate stock levels
            foreach ($request->components as $component) {
                $materialId = $component['id'];
                $componentQty = intval($component['quantity']);
                $totalRequiredQty = $componentQty * $productQty; // Total required quantity

                // Get current stock of this material in the warehouse
                $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $request->warehouse_id)
                    ->where('material_id', $materialId)
                    ->where('item_type', 'material')  // Chỉ lấy các mục là linh kiện
                    ->first();

                if (!$warehouseMaterial || $warehouseMaterial->quantity < $totalRequiredQty) {
                    throw new \Exception('Không đủ vật tư trong kho. Vui lòng kiểm tra lại số lượng.');
                }
            }

            // Create the assembly materials and update stock levels
            foreach ($request->components as $component) {
                // Process serials - either from multiple inputs or single input
                $serial = null;
                if (isset($component['serials']) && is_array($component['serials'])) {
                    $filteredComponentSerials = array_filter($component['serials']);
                    
                    // If component quantity > 1 and we have serials, validate we have enough
                    $componentQty = intval($component['quantity']);
                    $totalSerialNeeded = $componentQty * $productQty;
                    
                    if (!empty($filteredComponentSerials) && count($filteredComponentSerials) < $componentQty) {
                        // Only show warning if some serials were entered but not enough
                        Log::warning("Insufficient serials for component ID: {$component['id']}. Expected: {$componentQty}, Got: " . count($filteredComponentSerials));
                    }
                    
                    // Check for duplicate component serials within this form
                    if (count($filteredComponentSerials) !== count(array_unique($filteredComponentSerials))) {
                        throw new \Exception('Không được nhập trùng serial cho linh kiện.');
                    }
                    
                    // Check for duplicate component serials in database (excluding current assembly)
                    if (!empty($filteredComponentSerials)) {
                        foreach ($filteredComponentSerials as $componentSerial) {
                            // Skip empty serials
                            if (empty($componentSerial)) continue;
                            
                            // Find if this material serial already exists in other assemblies
                            $existingMaterial = AssemblyMaterial::whereHas('assembly', function($query) use ($assembly) {
                                    $query->where('id', '!=', $assembly->id);
                                })
                                ->where('material_id', $component['id'])
                                ->where('serial', 'like', '%' . $componentSerial . '%')
                                ->first();
                                
                            if ($existingMaterial) {
                                $existingAssembly = Assembly::find($existingMaterial->assembly_id);
                                $materialName = Material::find($component['id'])->name ?? 'Unknown';
                                throw new \Exception("Serial '{$componentSerial}' của linh kiện '{$materialName}' đã được sử dụng trong phiếu lắp ráp #{$existingAssembly->code}.");
                            }
                        }
                    }
                    
                    $serial = implode(',', $filteredComponentSerials);
                } elseif (isset($component['serial'])) {
                    $serial = $component['serial'];
                    
                    // Check single serial existence in database if not empty (excluding current assembly)
                    if (!empty($serial)) {
                        $existingMaterial = AssemblyMaterial::whereHas('assembly', function($query) use ($assembly) {
                                $query->where('id', '!=', $assembly->id);
                            })
                            ->where('material_id', $component['id'])
                            ->where('serial', $serial)
                            ->first();
                            
                        if ($existingMaterial) {
                            $existingAssembly = Assembly::find($existingMaterial->assembly_id);
                            $materialName = Material::find($component['id'])->name ?? 'Unknown';
                            throw new \Exception("Serial '{$serial}' của linh kiện '{$materialName}' đã được sử dụng trong phiếu lắp ráp #{$existingAssembly->code}.");
                        }
                    }
                }

                $componentQty = intval($component['quantity']);
                $totalRequiredQty = $componentQty * $productQty; // Calculate total quantity needed

                // Create assembly material record
                AssemblyMaterial::create([
                    'assembly_id' => $assembly->id,
                    'material_id' => $component['id'],
                    'quantity' => $componentQty,
                    'serial' => $serial,
                    'note' => $component['note'] ?? null,
                ]);

                // Update warehouse stock
                WarehouseMaterial::where('warehouse_id', $request->warehouse_id)
                    ->where('material_id', $component['id'])
                    ->where('item_type', 'material')  // Chỉ cập nhật các mục là linh kiện
                    ->decrement('quantity', $totalRequiredQty); // Decrement by total required quantity
            }

            // Cập nhật thành phẩm vào kho đích
            $this->updateProductToTargetWarehouse($assembly);

            DB::commit();
            return redirect()->route('assemblies.index')->with('success', 'Phiếu lắp ráp đã được tạo thành công');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified assembly.
     */
    public function show(Assembly $assembly)
    {
        $assembly->load(['product', 'materials.material']);
        return view('assemble.show', compact('assembly'));
    }

    /**
     * Show the form for editing the specified assembly.
     */
    public function edit(Assembly $assembly)
    {
        $assembly->load(['product', 'materials.material']);
        $products = Product::all();
        $materials = Material::all();
        $warehouses = Warehouse::all();

        return view('assemble.edit', compact('assembly', 'products', 'materials', 'warehouses'));
    }

    /**
     * Update the specified assembly in storage.
     */
    public function update(Request $request, Assembly $assembly)
    {
        $request->validate([
            'assembly_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'target_warehouse_id' => 'required|exists:warehouses,id|different:warehouse_id',
            'assigned_to' => 'required',
            'product_quantity' => 'required|integer|min:1',
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:materials,id',
            'components.*.quantity' => 'required|integer|min:1',
        ], [
            'target_warehouse_id.different' => 'Kho nhập thành phẩm phải khác với kho xuất linh kiện.',
        ]);

        DB::beginTransaction();
        try {
            $productQty = intval($request->product_quantity);
            $oldProductQty = $assembly->quantity;
            $oldWarehouseId = $assembly->warehouse_id;
            $newWarehouseId = $request->warehouse_id;
            $oldTargetWarehouseId = $assembly->target_warehouse_id;
            $newTargetWarehouseId = $request->target_warehouse_id;
            $oldProductId = $assembly->product_id;
            $newProductId = $request->product_id;
            
            // Xử lý product_serials
            $productSerials = $request->product_serials ?? [];
            
            // Validate that we have enough serials if product quantity > 1
            if ($productQty > 1 && count(array_filter($productSerials)) < $productQty) {
                throw new \Exception('Vui lòng nhập đủ serial cho tất cả thành phẩm.');
            }
            
            // Check for duplicate serials within the form
            $filteredSerials = array_filter($productSerials);
            if (count($filteredSerials) !== count(array_unique($filteredSerials))) {
                throw new \Exception('Không được nhập trùng serial thành phẩm.');
            }
            
            // Check for duplicate serials in the database (exclude current assembly)
            if (!empty($filteredSerials)) {
                foreach ($filteredSerials as $serial) {
                    // Skip empty serials
                    if (empty($serial)) continue;
                    
                    // Find assemblies with this serial (excluding current assembly)
                    $existingAssembly = Assembly::where('product_serials', 'like', '%' . $serial . '%')
                        ->where('product_id', $request->product_id)
                        ->where('id', '!=', $assembly->id)
                        ->first();
                    
                    if ($existingAssembly) {
                        throw new \Exception("Serial thành phẩm '{$serial}' đã tồn tại trong phiếu lắp ráp #{$existingAssembly->code}.");
                    }
                    
                    // Also check in the serials table (excluding ones linked to this assembly)
                    $existingSerial = Serial::where('serial_number', $serial)
                        ->where('product_id', $request->product_id)
                        ->where(function($query) use ($assembly) {
                            $query->whereNull('notes')
                                ->orWhere('notes', 'not like', '%Assembly ID: ' . $assembly->id . '%');
                        })
                        ->first();
                        
                    if ($existingSerial) {
                        throw new \Exception("Serial '{$serial}' đã tồn tại trong cơ sở dữ liệu.");
                    }
                }
            }
            
            // Delete existing serials for this assembly if product changed
            if ($oldProductId != $newProductId) {
                $this->deleteSerialRecords($assembly->id);
            }
            
            $productSerialsStr = !empty($filteredSerials) ? implode(',', $filteredSerials) : null;
            
            // Load existing materials to calculate stock adjustment
            $existingMaterials = $assembly->materials->keyBy('material_id');
            
            // STEP 1: Restore stock for existing materials (if warehouse is the same)
            if ($oldWarehouseId == $newWarehouseId) {
                foreach ($existingMaterials as $material) {
                    $existingTotalQty = $material->quantity * $oldProductQty; // Calculate old total quantity
                    
                    // Return the items to warehouse stock
                    WarehouseMaterial::where('warehouse_id', $oldWarehouseId)
                        ->where('material_id', $material->material_id)
                        ->where('item_type', 'material')  // Chỉ cập nhật các mục là linh kiện
                        ->increment('quantity', $existingTotalQty);
                }
            }
            
            // STEP 2: Validate stock levels for new materials
            foreach ($request->components as $component) {
                $materialId = $component['id'];
                $componentQty = intval($component['quantity']);
                $totalRequiredQty = $componentQty * $productQty; // Calculate total quantity needed
                
                // Only validate for the new warehouse or for increased quantities
                if ($newWarehouseId != $oldWarehouseId) {
                    // If warehouse has changed, validate all quantities
                    $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $request->warehouse_id)
                        ->where('material_id', $materialId)
                        ->where('item_type', 'material')
                        ->first();
                    
                    if (!$warehouseMaterial || $warehouseMaterial->quantity < $totalRequiredQty) {
                        throw new \Exception('Không đủ vật tư trong kho. Vui lòng kiểm tra lại số lượng.');
                    }
                }
            }
            
            // STEP 3: Update the assembly record
            $assembly->update([
                'date' => $request->assembly_date,
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'target_warehouse_id' => $request->target_warehouse_id,
                'assigned_to' => $request->assigned_to,
                'notes' => $request->assembly_note,
                'quantity' => $productQty,
                'product_serials' => $productSerialsStr,
            ]);
            
            // Update serial records
            $this->updateSerialRecords($filteredSerials, $request->product_id, $assembly->id);

            // STEP 4: Delete all existing materials (stock already restored above)
            $assembly->materials()->delete();
            
            // STEP 5: Create new assembly materials and update stock
            foreach ($request->components as $component) {
                // Process serials - either from multiple inputs or single input
                $serial = null;
                if (isset($component['serials']) && is_array($component['serials'])) {
                    $filteredComponentSerials = array_filter($component['serials']);
                    
                    // If component quantity > 1 and we have serials, validate we have enough
                    $componentQty = intval($component['quantity']);
                    $totalSerialNeeded = $componentQty * $productQty;
                    
                    if (!empty($filteredComponentSerials) && count($filteredComponentSerials) < $componentQty) {
                        // Only show warning if some serials were entered but not enough
                        Log::warning("Insufficient serials for component ID: {$component['id']}. Expected: {$componentQty}, Got: " . count($filteredComponentSerials));
                    }
                    
                    // Check for duplicate component serials
                    if (count($filteredComponentSerials) !== count(array_unique($filteredComponentSerials))) {
                        throw new \Exception('Không được nhập trùng serial cho linh kiện.');
                    }
                    
                    // Check for duplicate component serials in database (excluding current assembly)
                    if (!empty($filteredComponentSerials)) {
                        foreach ($filteredComponentSerials as $componentSerial) {
                            // Skip empty serials
                            if (empty($componentSerial)) continue;
                            
                            // Find if this material serial already exists in other assemblies
                            $existingMaterial = AssemblyMaterial::whereHas('assembly', function($query) use ($assembly) {
                                    $query->where('id', '!=', $assembly->id);
                                })
                                ->where('material_id', $component['id'])
                                ->where('serial', 'like', '%' . $componentSerial . '%')
                                ->first();
                                
                            if ($existingMaterial) {
                                $existingAssembly = Assembly::find($existingMaterial->assembly_id);
                                $materialName = Material::find($component['id'])->name ?? 'Unknown';
                                throw new \Exception("Serial '{$componentSerial}' của linh kiện '{$materialName}' đã được sử dụng trong phiếu lắp ráp #{$existingAssembly->code}.");
                            }
                        }
                    }
                    
                    $serial = implode(',', $filteredComponentSerials);
                } elseif (isset($component['serial'])) {
                    $serial = $component['serial'];
                    
                    // Check single serial existence in database if not empty (excluding current assembly)
                    if (!empty($serial)) {
                        $existingMaterial = AssemblyMaterial::whereHas('assembly', function($query) use ($assembly) {
                                $query->where('id', '!=', $assembly->id);
                            })
                            ->where('material_id', $component['id'])
                            ->where('serial', $serial)
                            ->first();
                            
                        if ($existingMaterial) {
                            $existingAssembly = Assembly::find($existingMaterial->assembly_id);
                            $materialName = Material::find($component['id'])->name ?? 'Unknown';
                            throw new \Exception("Serial '{$serial}' của linh kiện '{$materialName}' đã được sử dụng trong phiếu lắp ráp #{$existingAssembly->code}.");
                        }
                    }
                }
                
                $componentQty = intval($component['quantity']);
                $totalRequiredQty = $componentQty * $productQty; // Calculate total quantity needed
                
                // Create new assembly material
                AssemblyMaterial::create([
                    'assembly_id' => $assembly->id,
                    'material_id' => $component['id'],
                    'quantity' => $componentQty,
                    'serial' => $serial,
                    'note' => $component['note'] ?? null,
                ]);
                
                // Reduce warehouse stock
                WarehouseMaterial::where('warehouse_id', $newWarehouseId)
                    ->where('material_id', $component['id'])
                    ->where('item_type', 'material')  // Chỉ cập nhật các mục là linh kiện
                    ->decrement('quantity', $totalRequiredQty); // Decrement by total required quantity
            }

            // Cập nhật kho thành phẩm (nếu kho đã thay đổi)
            if ($oldTargetWarehouseId != $newTargetWarehouseId) {
                // Nếu kho đích đã thay đổi, cần giảm số lượng thành phẩm ở kho cũ
                // và tăng số lượng ở kho mới
                if ($oldTargetWarehouseId) {
                    // Giảm số lượng ở kho cũ
                    WarehouseMaterial::where('warehouse_id', $oldTargetWarehouseId)
                        ->where('material_id', $assembly->product_id)
                        ->where('item_type', 'product')
                        ->decrement('quantity', $oldProductQty); // Use old product quantity
                }
                
                // Và cập nhật vào kho mới
                $this->updateProductToTargetWarehouse($assembly);
            } else if ($assembly->product_id != $request->product_id || $assembly->quantity != $productQty) {
                // Nếu kho không thay đổi nhưng thành phẩm hoặc số lượng có thay đổi
                // Giảm số lượng thành phẩm cũ
                if ($oldTargetWarehouseId) {
                    WarehouseMaterial::where('warehouse_id', $oldTargetWarehouseId)
                        ->where('material_id', $assembly->product_id)
                        ->where('item_type', 'product')
                        ->decrement('quantity', $oldProductQty); // Use old product quantity
                }
                
                // Sau khi cập nhật assembly, thêm thành phẩm mới vào kho
                $this->updateProductToTargetWarehouse($assembly);
            }

            DB::commit();
            return redirect()->route('assemblies.index')->with('success', 'Phiếu lắp ráp đã được cập nhật thành công');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified assembly from storage.
     */
    public function destroy(Assembly $assembly)
    {
        DB::beginTransaction();
        try {
            // Load materials if not already loaded
            if (!$assembly->relationLoaded('materials')) {
                $assembly->load('materials');
            }
            
            // 1. Return components back to source warehouse
            $warehouseId = $assembly->warehouse_id;
            $productQuantity = $assembly->quantity ?? 1;
            
            foreach ($assembly->materials as $material) {
                // Calculate total quantity to return: component quantity per product × total products
                $totalQuantityToReturn = $material->quantity * $productQuantity;
                
                // Return items to warehouse stock
                WarehouseMaterial::where('warehouse_id', $warehouseId)
                    ->where('material_id', $material->material_id)
                    ->where('item_type', 'material')
                    ->increment('quantity', $totalQuantityToReturn);
                    
                Log::info("Assembly deletion: Returned {$totalQuantityToReturn} of material ID {$material->material_id} to warehouse {$warehouseId}");
            }
            
            // 2. Remove assembled product from target warehouse
            if ($assembly->target_warehouse_id && $assembly->product_id) {
                WarehouseMaterial::where('warehouse_id', $assembly->target_warehouse_id)
                    ->where('material_id', $assembly->product_id)
                    ->where('item_type', 'product')
                    ->decrement('quantity', $productQuantity);
                    
                Log::info("Assembly deletion: Removed {$productQuantity} of product ID {$assembly->product_id} from warehouse {$assembly->target_warehouse_id}");
            }
            
            // 3. Delete serial records for this assembly
            $this->deleteSerialRecords($assembly->id);
            
            // 4. Delete related materials
            $assembly->materials()->delete();
            
            // 5. Delete the assembly
            $assembly->delete();
            
            DB::commit();
            return redirect()->route('assemblies.index')->with('success', 'Phiếu lắp ráp đã được xóa thành công và tồn kho đã được cập nhật');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting assembly: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi xóa: ' . $e->getMessage()]);
        }
    }

    /**
     * Search materials API endpoint
     */
    public function searchMaterials(Request $request)
    {
        try {
            $searchTerm = $request->input('term');

            if (empty($searchTerm)) {
                return response()->json([]);
            }

            // Make search case-insensitive and more comprehensive
            $materials = Material::where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(category) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(serial) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            })
                ->limit(10)
                ->get(['id', 'code', 'name', 'category', 'serial']);

            // Add a debug flag to check if search is working
            $result = [
                'success' => true,
                'count' => $materials->count(),
                'data' => $materials
            ];

            return response()->json($materials);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Material search error: ' . $e->getMessage());

            // Return error with more details for debugging
            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Cập nhật thành phẩm vào kho đích
     */
    private function updateProductToTargetWarehouse(Assembly $assembly)
    {
        // Lấy thông tin thành phẩm và số lượng từ assembly
        $productId = $assembly->product_id;
        $warehouseId = $assembly->target_warehouse_id;
        $quantity = $assembly->quantity;

        // Kiểm tra xem thành phẩm đã có trong kho chưa
        $warehouseProduct = WarehouseMaterial::where('warehouse_id', $warehouseId)
            ->where('material_id', $productId)
            ->where('item_type', 'product')
            ->first();

        if ($warehouseProduct) {
            // Nếu đã có, tăng số lượng
            $warehouseProduct->increment('quantity', $quantity);
        } else {
            // Nếu chưa có, tạo mới
            WarehouseMaterial::create([
                'warehouse_id' => $warehouseId,
                'material_id' => $productId,
                'quantity' => $quantity,
                'item_type' => 'product' // Xác định đây là thành phẩm, không phải linh kiện
            ]);
        }
    }

    /**
     * Create serial records for each product serial
     */
    private function createSerialRecords(array $serials, int $productId, int $assemblyId)
    {
        if (empty($serials)) return;
        
        foreach ($serials as $serial) {
            if (empty($serial)) continue;
            
            Serial::create([
                'serial_number' => $serial,
                'product_id' => $productId,
                'status' => 'active',
                'notes' => 'Assembly ID: ' . $assemblyId
            ]);
        }
    }
    
    /**
     * Update serial records for a particular assembly
     */
    private function updateSerialRecords(array $newSerials, int $productId, int $assemblyId)
    {
        // Delete existing serials for this assembly
        $this->deleteSerialRecords($assemblyId);
        
        // Create new serials
        $this->createSerialRecords($newSerials, $productId, $assemblyId);
    }
    
    /**
     * Delete serial records for a particular assembly
     */
    private function deleteSerialRecords(int $assemblyId)
    {
        Serial::where('notes', 'like', '%Assembly ID: ' . $assemblyId . '%')->delete();
    }

    /**
     * API to check if a serial exists 
     */
    public function checkSerial(Request $request)
    {
        $request->validate([
            'serial' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'assembly_id' => 'nullable|integer'
        ]);
        
        $serial = $request->serial;
        $productId = $request->product_id;
        $assemblyId = $request->assembly_id;
        
        try {
            // Check in assemblies table
            $query = Assembly::where('product_serials', 'like', '%' . $serial . '%')
                ->where('product_id', $productId);
                
            // Exclude current assembly if editing
            if ($assemblyId) {
                $query->where('id', '!=', $assemblyId);
            }
            
            $existingAssembly = $query->first();
            
            if ($existingAssembly) {
                return response()->json([
                    'exists' => true,
                    'message' => "Serial đã tồn tại trong phiếu lắp ráp #{$existingAssembly->code}",
                    'type' => 'assembly'
                ]);
            }
            
            // Check in serials table
            $query = Serial::where('serial_number', $serial)
                ->where('product_id', $productId);
                
            // Exclude serials from current assembly if editing
            if ($assemblyId) {
                $query->where(function($q) use ($assemblyId) {
                    $q->whereNull('notes')
                        ->orWhere('notes', 'not like', '%Assembly ID: ' . $assemblyId . '%');
                });
            }
            
            $existingSerial = $query->first();
            
            if ($existingSerial) {
                return response()->json([
                    'exists' => true,
                    'message' => "Serial đã tồn tại trong cơ sở dữ liệu",
                    'type' => 'serial'
                ]);
            }
            
            return response()->json([
                'exists' => false,
                'message' => "Serial hợp lệ"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => true, 
                'message' => 'Lỗi kiểm tra serial: ' . $e->getMessage()
            ], 500);
        }
    }
}
