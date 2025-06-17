<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\AssemblyMaterial;
use App\Models\AssemblyProduct;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Product;
use App\Models\Project;
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
        $assemblies = Assembly::with(['product', 'products.product', 'assignedEmployee', 'tester', 'warehouse', 'targetWarehouse', 'project'])->get();
        return view('assemble.index', compact('assemblies'));
    }

    /**
     * Show the form for creating a new assembly.
     */
    public function create()
    {
        // Get all active products for the form
        $products = Product::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        // Get all active materials for search
        $materials = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'category', 'unit']);

        // Get all active warehouses
        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        // Get all active employees
        $employees = Employee::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        // Get all active projects
        $projects = Project::orderBy('project_name')
            ->get(['id', 'project_name', 'project_code']);

        return view('assemble.create', compact('products', 'materials', 'warehouses', 'employees', 'projects'));
    }

    /**
     * Store a newly created assembly in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'assembly_code' => 'required|unique:assemblies,code',
            'assembly_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'target_warehouse_id' => 'nullable|exists:warehouses,id',
            'assigned_to' => 'required|exists:employees,id',
            'tester_id' => 'required|exists:employees,id',
            'purpose' => 'required|in:storage,project',
            'project_id' => 'nullable|exists:projects,id',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:materials,id',
            'components.*.quantity' => 'required|integer|min:1',
            'components.*.product_id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $products = $request->products;
            $components = $request->components;

            // Validate serial numbers for products (only if serials are provided)
            foreach ($products as $productIndex => $productData) {
                $productQty = intval($productData['quantity']);
                $productSerials = $productData['serials'] ?? [];
                $productCode = $productData['code'] ?? 'Unknown';

                // Get non-empty serials
                $filteredSerials = array_filter($productSerials);

                // Only validate if serials are provided
                if (!empty($filteredSerials)) {
                    // Check for duplicate serials within the form
                    if (count($filteredSerials) !== count(array_unique($filteredSerials))) {
                        throw new \Exception("Không được nhập trùng serial thành phẩm [{$productCode}].");
                    }

                    // Check for duplicate serials in the database
                    foreach ($filteredSerials as $serial) {
                        if (empty($serial)) continue;

                        // Find assemblies with this serial
                        $existingAssembly = Assembly::where('product_serials', 'like', '%' . $serial . '%')
                            ->where('product_id', $productData['id'])
                            ->first();

                        if ($existingAssembly) {
                            throw new \Exception("Serial thành phẩm '{$serial}' đã tồn tại trong phiếu lắp ráp #{$existingAssembly->code}.");
                        }

                        // Also check in the serials table
                        $existingSerial = Serial::where('serial_number', $serial)
                            ->where('product_id', $productData['id'])
                            ->first();

                        if ($existingSerial) {
                            throw new \Exception("Serial '{$serial}' đã tồn tại trong cơ sở dữ liệu.");
                        }
                    }
                }
            }

            // Validate stock levels for all components
            foreach ($components as $component) {
                $materialId = $component['id'];
                $componentQty = intval($component['quantity']);

                // Find the product this component belongs to
                // Handle both numeric ID and string format like "product_1"
                $componentProductId = $component['product_id'];
                if (is_string($componentProductId) && strpos($componentProductId, 'product_') === 0) {
                    // Convert "product_1" to "1"
                    $componentProductId = str_replace('product_', '', $componentProductId);
                }
                $componentProductId = intval($componentProductId);

                $productData = collect($products)->firstWhere('id', $componentProductId);
                if (!$productData) {
                    // Debug information
                    Log::error('Component product not found', [
                        'component_product_id' => $component['product_id'],
                        'converted_id' => $componentProductId,
                        'available_products' => collect($products)->pluck('id')->toArray(),
                        'material_id' => $materialId
                    ]);
                    throw new \Exception('Không tìm thấy thành phẩm cho linh kiện. Component product ID: ' . $component['product_id']);
                }

                $productQty = intval($productData['quantity']);
                $totalRequiredQty = $componentQty * $productQty; // Total required quantity

                // Get current stock of this material in the warehouse
                $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $request->warehouse_id)
                    ->where('material_id', $materialId)
                    ->where('item_type', 'material')
                    ->first();

                if (!$warehouseMaterial || $warehouseMaterial->quantity < $totalRequiredQty) {
                    throw new \Exception('Không đủ vật tư trong kho. Vui lòng kiểm tra lại số lượng.');
                }
            }

            // Create one assembly record for all products
            $assembly = Assembly::create([
                'code' => $request->assembly_code,
                'date' => $request->assembly_date,
                'warehouse_id' => $request->warehouse_id,
                'target_warehouse_id' => $request->target_warehouse_id,
                'assigned_employee_id' => $request->assigned_to,
                'tester_id' => $request->tester_id,
                'purpose' => $request->purpose,
                'project_id' => $request->project_id,
                'status' => 'pending',
                'notes' => $request->assembly_note,
            ]);

            // Create assembly products for each product
            foreach ($products as $productIndex => $productData) {
                $productQty = intval($productData['quantity']);
                $productSerials = $productData['serials'] ?? [];
                $filteredSerials = array_filter($productSerials);
                $productSerialsStr = !empty($filteredSerials) ? implode(',', $filteredSerials) : null;

                // Create assembly product record
                AssemblyProduct::create([
                    'assembly_id' => $assembly->id,
                    'product_id' => $productData['id'],
                    'quantity' => $productQty,
                    'serials' => $productSerialsStr,
                ]);

                // Create serial records for each product serial
                $this->createSerialRecords($filteredSerials, $productData['id'], $assembly->id);

                // Update product inventory in target warehouse
                $this->updateProductToTargetWarehouse($productData['id'], $request->target_warehouse_id, $productQty);
            }

            // Create assembly materials for all components (avoid duplicates)
            $processedComponents = [];
            foreach ($components as $component) {
                $materialId = $component['id'];
                $componentQty = intval($component['quantity']);

                // Find the product this component belongs to
                $componentProductId = $component['product_id'];
                if (is_string($componentProductId) && strpos($componentProductId, 'product_') === 0) {
                    $componentProductId = str_replace('product_', '', $componentProductId);
                }
                $componentProductId = intval($componentProductId);

                $productData = collect($products)->firstWhere('id', $componentProductId);
                if (!$productData) {
                    throw new \Exception('Không tìm thấy thành phẩm cho linh kiện. Component product ID: ' . $component['product_id']);
                }

                $productQty = intval($productData['quantity']);
                $totalRequiredQty = $componentQty * $productQty;

                // Process serials
                $serial = null;
                if (isset($component['serials']) && is_array($component['serials'])) {
                    $filteredComponentSerials = array_filter($component['serials']);

                    // Check for duplicate component serials within this form
                    if (count($filteredComponentSerials) !== count(array_unique($filteredComponentSerials))) {
                        throw new \Exception('Không được nhập trùng serial cho linh kiện.');
                    }

                    // Check for duplicate component serials in database
                    if (!empty($filteredComponentSerials)) {
                        foreach ($filteredComponentSerials as $componentSerial) {
                            if (empty($componentSerial)) continue;

                            $existingMaterial = AssemblyMaterial::whereHas('assembly', function ($query) use ($assembly) {
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

                    if (!empty($serial)) {
                        $existingMaterial = AssemblyMaterial::whereHas('assembly', function ($query) use ($assembly) {
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
                    ->where('item_type', 'material')
                    ->decrement('quantity', $totalRequiredQty);
            }

            DB::commit();
            return redirect()->route('assemblies.index')->with('success', 'Phiếu lắp ráp đã được tạo thành công!');
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
        $assembly->load(['product', 'products.product', 'materials.material', 'assignedEmployee', 'tester', 'warehouse', 'targetWarehouse', 'project']);
        return view('assemble.show', compact('assembly'));
    }

    /**
     * Show the form for editing the specified assembly.
     */
    public function edit(Assembly $assembly)
    {
        $assembly->load(['product', 'products.product', 'materials.material', 'warehouse', 'targetWarehouse']);

        // Get all active products for the form
        $products = Product::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        // Get all active materials for search
        $materials = Material::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'category', 'unit']);

        // Get all active warehouses
        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        // Get all active employees
        $employees = Employee::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        // Get all active projects
        $projects = Project::orderBy('project_name')
            ->get(['id', 'project_name', 'project_code']);

        // Parse product serials if they exist (legacy support)
        $productSerials = [];
        if ($assembly->product_serials) {
            $productSerials = explode(',', $assembly->product_serials);
        }

        return view('assemble.edit', compact('assembly', 'products', 'materials', 'warehouses', 'employees', 'projects', 'productSerials'));
    }

    /**
     * Update the specified assembly in storage.
     */
    public function update(Request $request, Assembly $assembly)
    {
        $request->validate([
            'assembly_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'target_warehouse_id' => 'nullable|exists:warehouses,id',
            'assigned_to' => 'required|exists:employees,id',
            'tester_id' => 'required|exists:employees,id',
            'purpose' => 'required|in:storage,project',
            'project_id' => 'nullable|exists:projects,id',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:materials,id',
            'components.*.quantity' => 'required|integer|min:1',
            'components.*.product_id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $products = $request->products;
            $components = $request->components;
            $oldWarehouseId = $assembly->warehouse_id;
            $newWarehouseId = $request->warehouse_id;
            $oldTargetWarehouseId = $assembly->target_warehouse_id;
            $newTargetWarehouseId = $request->target_warehouse_id;

            // Validate serial numbers for products
            foreach ($products as $productIndex => $productData) {
                $productQty = intval($productData['quantity']);
                $productSerials = $productData['serials'] ?? [];
                $productCode = $productData['code'] ?? 'Unknown';

                // Get non-empty serials
                $filteredSerials = array_filter($productSerials);

                // Only validate if serials are provided
                if (!empty($filteredSerials)) {
                    // Check for duplicate serials within the form
                    if (count($filteredSerials) !== count(array_unique($filteredSerials))) {
                        throw new \Exception("Không được nhập trùng serial thành phẩm [{$productCode}].");
                    }

                    // Check for duplicate serials in the database (exclude current assembly)
                    foreach ($filteredSerials as $serial) {
                        if (empty($serial)) continue;

                        // Find assemblies with this serial (excluding current assembly)
                        $existingAssembly = Assembly::whereHas('products', function ($query) use ($serial, $productData) {
                            $query->where('product_id', $productData['id'])
                                ->where('serials', 'like', '%' . $serial . '%');
                        })->where('id', '!=', $assembly->id)->first();

                        if ($existingAssembly) {
                            throw new \Exception("Serial thành phẩm '{$serial}' đã tồn tại trong phiếu lắp ráp #{$existingAssembly->code}.");
                        }

                        // Also check in the serials table (excluding ones linked to this assembly)
                        $existingSerial = Serial::where('serial_number', $serial)
                            ->where('product_id', $productData['id'])
                            ->where(function ($query) use ($assembly) {
                                $query->whereNull('notes')
                                    ->orWhere('notes', 'not like', '%Assembly ID: ' . $assembly->id . '%');
                            })
                            ->first();

                        if ($existingSerial) {
                            throw new \Exception("Serial '{$serial}' đã tồn tại trong cơ sở dữ liệu.");
                        }
                    }
                }
            }

            // Load existing materials and products to calculate stock adjustment
            $existingMaterials = $assembly->materials->keyBy('material_id');
            $existingProducts = $assembly->products;

            // STEP 1: Restore stock for existing materials to old warehouse
            foreach ($existingMaterials as $material) {
                // Calculate total quantity needed for ALL products in the assembly
                $totalOldQty = 0;
                foreach ($existingProducts as $existingProduct) {
                    $totalOldQty += $material->quantity * $existingProduct->quantity;
                }

                // Find or create warehouse material record in old warehouse
                $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $oldWarehouseId)
                    ->where('material_id', $material->material_id)
                    ->where('item_type', 'material')
                    ->first();

                if ($warehouseMaterial) {
                    // Material exists, increment quantity
                    $warehouseMaterial->increment('quantity', $totalOldQty);
                } else {
                    // Material doesn't exist, create new record
                    WarehouseMaterial::create([
                        'warehouse_id' => $oldWarehouseId,
                        'material_id' => $material->material_id,
                        'quantity' => $totalOldQty,
                        'item_type' => 'material'
                    ]);
                }

                Log::info("Assembly update: Returned {$totalOldQty} of material ID {$material->material_id} to warehouse {$oldWarehouseId}");
            }

            // STEP 2: Validate stock levels for new materials in new warehouse
            foreach ($components as $component) {
                $materialId = $component['id'];
                $componentQty = intval($component['quantity']);

                // Find the product this component belongs to
                $componentProductId = $component['product_id'];
                if (is_string($componentProductId) && strpos($componentProductId, 'product_') === 0) {
                    $componentProductId = str_replace('product_', '', $componentProductId);
                }
                $componentProductId = intval($componentProductId);

                $productData = collect($products)->firstWhere('id', $componentProductId);
                if (!$productData) {
                    throw new \Exception('Không tìm thấy thành phẩm cho linh kiện. Component product ID: ' . $component['product_id']);
                }

                $productQty = intval($productData['quantity']);
                $totalRequiredQty = $componentQty * $productQty; // Calculate total quantity needed

                // Validate stock in new warehouse
                $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $newWarehouseId)
                    ->where('material_id', $materialId)
                    ->where('item_type', 'material')
                    ->first();

                if (!$warehouseMaterial || $warehouseMaterial->quantity < $totalRequiredQty) {
                    $materialName = Material::find($materialId)->name ?? 'Unknown';
                    $availableQty = $warehouseMaterial ? $warehouseMaterial->quantity : 0;
                    throw new \Exception("Không đủ vật tư '{$materialName}' trong kho. Cần: {$totalRequiredQty}, Có: {$availableQty}");
                }
            }

            // STEP 3: Update the assembly record
            $assembly->update([
                'date' => $request->assembly_date,
                'warehouse_id' => $request->warehouse_id,
                'target_warehouse_id' => $request->target_warehouse_id,
                'assigned_employee_id' => $request->assigned_to,
                'tester_id' => $request->tester_id,
                'purpose' => $request->purpose,
                'project_id' => $request->project_id,
                'notes' => $request->assembly_note,
            ]);

            // STEP 4: Remove existing products from target warehouse
            foreach ($existingProducts as $existingProduct) {
                $warehouseProduct = WarehouseMaterial::where('warehouse_id', $oldTargetWarehouseId)
                    ->where('material_id', $existingProduct->product_id)
                    ->where('item_type', 'product')
                    ->first();

                if ($warehouseProduct) {
                    if ($warehouseProduct->quantity >= $existingProduct->quantity) {
                        $warehouseProduct->decrement('quantity', $existingProduct->quantity);
                        Log::info("Assembly update: Removed {$existingProduct->quantity} of old product ID {$existingProduct->product_id} from warehouse {$oldTargetWarehouseId}");
                    } else {
                        $actualQuantity = $warehouseProduct->quantity;
                        $warehouseProduct->update(['quantity' => 0]);
                        Log::warning("Assembly update: Only {$actualQuantity} of old product ID {$existingProduct->product_id} available in warehouse {$oldTargetWarehouseId}, expected {$existingProduct->quantity}. Set to 0.");
                    }
                }
            }

            // STEP 5: Delete existing assembly products and serials
            $this->deleteSerialRecords($assembly->id);
            $assembly->products()->delete();

            // STEP 6: Create new assembly products
            foreach ($products as $productIndex => $productData) {
                $productQty = intval($productData['quantity']);
                $productSerials = $productData['serials'] ?? [];
                $filteredSerials = array_filter($productSerials);
                $productSerialsStr = !empty($filteredSerials) ? implode(',', $filteredSerials) : null;

                // Create assembly product record
                AssemblyProduct::create([
                    'assembly_id' => $assembly->id,
                    'product_id' => $productData['id'],
                    'quantity' => $productQty,
                    'serials' => $productSerialsStr,
                ]);

                // Create serial records for each product serial
                $this->createSerialRecords($filteredSerials, $productData['id'], $assembly->id);

                // Update product inventory in target warehouse
                $this->updateProductToTargetWarehouse($productData['id'], $newTargetWarehouseId, $productQty);
            }

            // STEP 7: Delete all existing materials (stock already restored above)
            $assembly->materials()->delete();

            // STEP 8: Create new assembly materials and update stock
            foreach ($components as $component) {
                $materialId = $component['id'];
                $componentQty = intval($component['quantity']);

                // Find the product this component belongs to
                $componentProductId = $component['product_id'];
                if (is_string($componentProductId) && strpos($componentProductId, 'product_') === 0) {
                    $componentProductId = str_replace('product_', '', $componentProductId);
                }
                $componentProductId = intval($componentProductId);

                $productData = collect($products)->firstWhere('id', $componentProductId);
                if (!$productData) {
                    throw new \Exception('Không tìm thấy thành phẩm cho linh kiện. Component product ID: ' . $component['product_id']);
                }

                $productQty = intval($productData['quantity']);
                $totalRequiredQty = $componentQty * $productQty;

                // Process serials
                $serial = null;
                if (isset($component['serials']) && is_array($component['serials'])) {
                    $filteredComponentSerials = array_filter($component['serials']);

                    // Check for duplicate component serials
                    if (count($filteredComponentSerials) !== count(array_unique($filteredComponentSerials))) {
                        throw new \Exception('Không được nhập trùng serial cho linh kiện.');
                    }

                    // Check for duplicate component serials in database (excluding current assembly)
                    if (!empty($filteredComponentSerials)) {
                        foreach ($filteredComponentSerials as $componentSerial) {
                            if (empty($componentSerial)) continue;

                            $existingMaterial = AssemblyMaterial::whereHas('assembly', function ($query) use ($assembly) {
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

                    if (!empty($serial)) {
                        $existingMaterial = AssemblyMaterial::whereHas('assembly', function ($query) use ($assembly) {
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

                // Create assembly material record
                AssemblyMaterial::create([
                    'assembly_id' => $assembly->id,
                    'material_id' => $component['id'],
                    'quantity' => $componentQty,
                    'serial' => $serial,
                    'note' => $component['note'] ?? null,
                ]);

                // Update warehouse stock
                WarehouseMaterial::where('warehouse_id', $newWarehouseId)
                    ->where('material_id', $component['id'])
                    ->where('item_type', 'material')
                    ->decrement('quantity', $totalRequiredQty);
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

                // Find or create warehouse material record
                $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $warehouseId)
                    ->where('material_id', $material->material_id)
                    ->where('item_type', 'material')
                    ->first();

                if ($warehouseMaterial) {
                    // Material exists, increment quantity
                    $warehouseMaterial->increment('quantity', $totalQuantityToReturn);
                } else {
                    // Material doesn't exist, create new record
                    WarehouseMaterial::create([
                        'warehouse_id' => $warehouseId,
                        'material_id' => $material->material_id,
                        'quantity' => $totalQuantityToReturn,
                        'item_type' => 'material'
                    ]);
                }

                Log::info("Assembly deletion: Returned {$totalQuantityToReturn} of material ID {$material->material_id} to warehouse {$warehouseId}");
            }

            // 2. Remove assembled products from target warehouse
            if ($assembly->target_warehouse_id) {
                // Load assembly products if not already loaded
                if (!$assembly->relationLoaded('products')) {
                    $assembly->load('products');
                }

                foreach ($assembly->products as $assemblyProduct) {
                    $warehouseProduct = WarehouseMaterial::where('warehouse_id', $assembly->target_warehouse_id)
                        ->where('material_id', $assemblyProduct->product_id)
                        ->where('item_type', 'product')
                        ->first();

                    if ($warehouseProduct) {
                        if ($warehouseProduct->quantity >= $assemblyProduct->quantity) {
                            // Sufficient quantity, decrement normally
                            $warehouseProduct->decrement('quantity', $assemblyProduct->quantity);
                            Log::info("Assembly deletion: Removed {$assemblyProduct->quantity} of product ID {$assemblyProduct->product_id} from warehouse {$assembly->target_warehouse_id}");
                        } else {
                            // Not enough quantity, set to 0 and log warning
                            $actualQuantity = $warehouseProduct->quantity;
                            $warehouseProduct->update(['quantity' => 0]);
                            Log::warning("Assembly deletion: Only {$actualQuantity} of product ID {$assemblyProduct->product_id} available in warehouse {$assembly->target_warehouse_id}, expected {$assemblyProduct->quantity}. Set to 0.");
                        }
                    } else {
                        // Product not found in warehouse, log warning
                        Log::warning("Assembly deletion: Product ID {$assemblyProduct->product_id} not found in warehouse {$assembly->target_warehouse_id}. Cannot remove {$assemblyProduct->quantity} units.");
                    }
                }

                // Also handle legacy single product (backward compatibility)
                if ($assembly->product_id) {
                    $warehouseProduct = WarehouseMaterial::where('warehouse_id', $assembly->target_warehouse_id)
                        ->where('material_id', $assembly->product_id)
                        ->where('item_type', 'product')
                        ->first();

                    if ($warehouseProduct) {
                        if ($warehouseProduct->quantity >= $productQuantity) {
                            // Sufficient quantity, decrement normally
                            $warehouseProduct->decrement('quantity', $productQuantity);
                            Log::info("Assembly deletion: Removed {$productQuantity} of legacy product ID {$assembly->product_id} from warehouse {$assembly->target_warehouse_id}");
                        } else {
                            // Not enough quantity, set to 0 and log warning
                            $actualQuantity = $warehouseProduct->quantity;
                            $warehouseProduct->update(['quantity' => 0]);
                            Log::warning("Assembly deletion: Only {$actualQuantity} of legacy product ID {$assembly->product_id} available in warehouse {$assembly->target_warehouse_id}, expected {$productQuantity}. Set to 0.");
                        }
                    } else {
                        // Product not found in warehouse, log warning
                        Log::warning("Assembly deletion: Legacy product ID {$assembly->product_id} not found in warehouse {$assembly->target_warehouse_id}. Cannot remove {$productQuantity} units.");
                    }
                }
            }

            // 3. Delete serial records for this assembly
            $this->deleteSerialRecords($assembly->id);

            // 4. Delete related materials
            $assembly->materials()->delete();

            // 5. Delete assembly products
            $assembly->products()->delete();

            // 6. Delete the assembly
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
     * Cập nhật thành phẩm vào kho đích
     */
    private function updateProductToTargetWarehouse($productId, $warehouseId, $quantity)
    {
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
     * Cập nhật thành phẩm vào kho đích (legacy method for backward compatibility)
     */
    private function updateProductToTargetWarehouseFromAssembly(Assembly $assembly)
    {
        // This method is kept for backward compatibility with existing code
        if ($assembly->product_id && $assembly->target_warehouse_id && $assembly->quantity) {
            $this->updateProductToTargetWarehouse($assembly->product_id, $assembly->target_warehouse_id, $assembly->quantity);
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
                $query->where(function ($q) use ($assemblyId) {
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

    /**
     * Generate unique assembly code
     */
    public function generateAssemblyCode()
    {
        $prefix = 'ASM';
        $date = now()->format('ymd');

        // Find the latest assembly code for today, considering the -P suffix pattern
        $latestAssembly = Assembly::where('code', 'like', $prefix . $date . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($latestAssembly) {
            // Extract the base sequence number (before -P suffix if exists)
            $code = $latestAssembly->code;

            // Check if code has -P suffix (e.g., ASM250613001-P1)
            if (preg_match('/^' . preg_quote($prefix . $date) . '(\d{3})(-P\d+)?$/', $code, $matches)) {
                $sequence = intval($matches[1]) + 1;
            } else {
                // Fallback: extract last 3 digits and increment
                $sequence = intval(substr($code, -3)) + 1;
            }
        } else {
            $sequence = 1;
        }

        $baseCode = $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return response()->json(['code' => $baseCode]);
    }

    /**
     * Generate unique assembly code for each product in multi-product assembly
     */
    private function generateUniqueAssemblyCode($baseCode, $productIndex)
    {
        $proposedCode = $baseCode . '-P' . $productIndex;

        // Check if this code already exists
        $counter = 1;
        while (Assembly::where('code', $proposedCode)->exists()) {
            // If exists, try with a different base sequence
            $prefix = 'ASM';
            $date = now()->format('ymd');

            // Extract current sequence number and increment
            if (preg_match('/^' . preg_quote($prefix . $date) . '(\d{3})$/', $baseCode, $matches)) {
                $sequence = intval($matches[1]) + $counter;
                $newBaseCode = $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
                $proposedCode = $newBaseCode . '-P' . $productIndex;
                $counter++;
            } else {
                // Fallback: append counter to the existing code
                $proposedCode = $baseCode . '-P' . $productIndex . '-' . $counter;
                $counter++;
            }

            // Prevent infinite loop
            if ($counter > 100) {
                throw new \Exception('Không thể tạo mã phiếu lắp ráp unique. Vui lòng thử lại.');
            }
        }

        return $proposedCode;
    }

    /**
     * Get materials for a specific product
     */
    public function getProductMaterials(Request $request, $productId)
    {
        try {
            $product = Product::with('materials')->findOrFail($productId);

            $materials = $product->materials->map(function ($material) {
                return [
                    'id' => $material->id,
                    'code' => $material->code,
                    'name' => $material->name,
                    'category' => $material->category,
                    'unit' => $material->unit,
                    'quantity' => $material->pivot->quantity,
                    'notes' => $material->pivot->notes,
                ];
            });

            return response()->json([
                'success' => true,
                'materials' => $materials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách vật tư: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all employees for dropdown
     */
    public function getEmployees()
    {
        try {
            $employees = Employee::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'username']);

            return response()->json([
                'success' => true,
                'employees' => $employees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách nhân viên: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if assembly code exists
     */
    public function checkAssemblyCode(Request $request)
    {
        $code = $request->input('code');
        $assemblyId = $request->input('assembly_id'); // For edit mode

        $query = Assembly::where('code', $code);

        if ($assemblyId) {
            $query->where('id', '!=', $assemblyId);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Mã phiếu đã tồn tại' : 'Mã phiếu hợp lệ'
        ]);
    }

    /**
     * Get warehouse materials stock for assembly validation
     */
    public function getWarehouseMaterialsStock(Request $request, $warehouseId)
    {
        try {
            $materialIds = $request->input('material_ids', []);

            if (empty($materialIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng cung cấp danh sách material_ids'
                ], 400);
            }

            $warehouseMaterials = WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('item_type', 'material') // Chỉ lấy linh kiện, không lấy thành phẩm
                ->whereIn('material_id', $materialIds)
                ->get(['material_id', 'quantity']);

            // Format response as key-value pairs for easy lookup
            $stockData = [];
            foreach ($warehouseMaterials as $wm) {
                $stockData[$wm->material_id] = $wm->quantity;
            }

            // Add zero stock for materials not found in warehouse
            foreach ($materialIds as $materialId) {
                if (!isset($stockData[$materialId])) {
                    $stockData[$materialId] = 0;
                }
            }

            return response()->json([
                'success' => true,
                'stock_data' => $stockData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin tồn kho: ' . $e->getMessage()
            ], 500);
        }
    }
}
