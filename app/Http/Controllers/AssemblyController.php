<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\AssemblyMaterial;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AssemblyController extends Controller
{
    /**
     * Display a listing of the assemblies.
     */
    public function index()
    {
        $assemblies = Assembly::with(['product', 'assignedEmployee', 'tester', 'warehouse', 'targetWarehouse', 'project'])->get();
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

        // Debug: Log request data
        Log::info('Assembly Store Request Debug', [
            'products' => $request->products,
            'components' => $request->components,
            'request_all' => $request->all()
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

            // Create assembly records for each product
            $createdAssemblies = [];
            $baseCode = $request->assembly_code; // Use the code from form
            $createdTestings = []; // Lưu các phiếu kiểm thử đã tạo

            foreach ($products as $productIndex => $productData) {
                $productQty = intval($productData['quantity']);
                $productSerials = $productData['serials'] ?? [];
                $filteredSerials = array_filter($productSerials);
                $productSerialsStr = !empty($filteredSerials) ? implode(',', $filteredSerials) : null;

                // Generate assembly code based on number of products
                if (count($products) == 1) {
                    // Single product: use base code as-is
                    $assemblyCode = $baseCode;
                } else {
                    // Multiple products: add suffix -P1, -P2, etc.
                    $assemblyCode = $this->generateUniqueAssemblyCode($baseCode, $productIndex + 1);
                }

                // Create the assembly record for this product
                $assembly = Assembly::create([
                    'code' => $assemblyCode,
                    'date' => $request->assembly_date,
                    'product_id' => $productData['id'],
                    'warehouse_id' => $request->warehouse_id,
                    'target_warehouse_id' => $request->target_warehouse_id,
                    'assigned_employee_id' => $request->assigned_to,
                    'tester_id' => $request->tester_id,
                    'purpose' => $request->purpose,
                    'project_id' => $request->project_id,
                    'status' => 'pending',
                    'notes' => $request->assembly_note,
                    'quantity' => $productQty,
                    'product_serials' => $productSerialsStr,
                ]);

                $createdAssemblies[] = $assembly;

                // Create serial records for each product serial
                $this->createSerialRecords($filteredSerials, $productData['id'], $assembly->id);

                // Get components for this specific product
                $productComponents = array_filter($components, function ($component) use ($productData) {
                    // Handle both numeric ID and string format like "product_1"
                    $componentProductId = $component['product_id'];
                    if (is_string($componentProductId) && strpos($componentProductId, 'product_') === 0) {
                        $componentProductId = str_replace('product_', '', $componentProductId);
                    }
                    return intval($componentProductId) == $productData['id'];
                });

                // Create the assembly materials and update stock levels
                foreach ($productComponents as $componentIndex => $component) {
                    // Process serials - either from multiple inputs or single input
                    $serial = null;
                    if (isset($component['serials']) && is_array($component['serials'])) {
                        $filteredComponentSerials = array_filter($component['serials']);

                        // Check for duplicate component serials within this form
                        if (count($filteredComponentSerials) !== count(array_unique($filteredComponentSerials))) {
                            throw new \Exception('Không được nhập trùng serial cho linh kiện.');
                        }

                        // Check for duplicate component serials in database (excluding current assembly)
                        if (!empty($filteredComponentSerials)) {
                            foreach ($filteredComponentSerials as $componentSerial) {
                                if (empty($componentSerial)) continue;

                                // Find if this material serial already exists in other assemblies
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

                        // Check single serial existence in database if not empty (excluding current assembly)
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
                        ->where('item_type', 'material')
                        ->decrement('quantity', $totalRequiredQty);
                }

                // Cập nhật thành phẩm vào kho đích
                $this->updateProductToTargetWarehouse($assembly);

                // Create testing record for this assembly
                $testing = $this->createTestingRecordForAssembly($assembly);
                $createdTestings[] = $testing;
            }

            DB::commit();
            
            // Tạo thông báo thành công với link đến phiếu kiểm thử
            $successMessage = 'Phiếu lắp ráp đã được tạo thành công!';
            
            // Nếu có phiếu kiểm thử được tạo, thêm thông báo và link
            if (count($createdTestings) > 0) {
                $testingUrl = route('testing.show', $createdTestings[0]->id);
                $successMessage .= ' <a href="' . $testingUrl . '" class="text-blue-600 hover:underline">Phiếu kiểm thử</a> đã được tạo tự động.';
            }
            
            return redirect()->route('assemblies.index')->with('success', $successMessage);
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
        $assembly->load(['product', 'materials.material', 'assignedEmployee', 'tester', 'warehouse', 'targetWarehouse', 'project', 'testings.tester']);
        return view('assemble.show', compact('assembly'));
    }

    /**
     * Show the form for editing the specified assembly.
     */
    public function edit(Assembly $assembly)
    {
        $assembly->load(['product', 'materials.material', 'warehouse', 'targetWarehouse']);

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

        // Parse product serials if they exist
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
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'target_warehouse_id' => 'nullable|exists:warehouses,id',
            'assigned_to' => 'required|exists:employees,id',
            'tester_id' => 'required|exists:employees,id',
            'purpose' => 'required|in:storage,project',
            'project_id' => 'nullable|exists:projects,id',
            'product_quantity' => 'required|integer|min:1',
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:materials,id',
            'components.*.quantity' => 'required|integer|min:1',
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

            // Delete existing serials for this assembly if product changed
            if ($oldProductId != $newProductId) {
                $this->deleteSerialRecords($assembly->id);
            }

            $productSerialsStr = !empty($filteredSerials) ? implode(',', $filteredSerials) : null;

            // Load existing materials to calculate stock adjustment
            $existingMaterials = $assembly->materials->keyBy('material_id');

            // STEP 1: Restore stock for existing materials to old warehouse
            foreach ($existingMaterials as $material) {
                $existingTotalQty = $material->quantity * $oldProductQty; // Calculate old total quantity

                // Find or create warehouse material record in old warehouse
                $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $oldWarehouseId)
                    ->where('material_id', $material->material_id)
                    ->where('item_type', 'material')
                    ->first();

                if ($warehouseMaterial) {
                    // Material exists, increment quantity
                    $warehouseMaterial->increment('quantity', $existingTotalQty);
                } else {
                    // Material doesn't exist, create new record
                    WarehouseMaterial::create([
                        'warehouse_id' => $oldWarehouseId,
                        'material_id' => $material->material_id,
                        'quantity' => $existingTotalQty,
                        'item_type' => 'material'
                    ]);
                }

                Log::info("Assembly update: Returned {$existingTotalQty} of material ID {$material->material_id} to warehouse {$oldWarehouseId}");
            }

            // STEP 2: Validate stock levels for new materials in new warehouse
            foreach ($request->components as $component) {
                $materialId = $component['id'];
                $componentQty = intval($component['quantity']);
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
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'target_warehouse_id' => $request->target_warehouse_id,
                'assigned_employee_id' => $request->assigned_to,
                'tester_id' => $request->tester_id,
                'purpose' => $request->purpose,
                'project_id' => $request->project_id,
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

                    // Check single serial existence in database if not empty (excluding current assembly)
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

                // Reduce warehouse stock safely
                $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $newWarehouseId)
                    ->where('material_id', $component['id'])
                    ->where('item_type', 'material')
                    ->first();

                if ($warehouseMaterial && $warehouseMaterial->quantity >= $totalRequiredQty) {
                    $warehouseMaterial->decrement('quantity', $totalRequiredQty);
                    Log::info("Assembly update: Consumed {$totalRequiredQty} of material ID {$component['id']} from warehouse {$newWarehouseId}");
                } else {
                    $availableQty = $warehouseMaterial ? $warehouseMaterial->quantity : 0;
                    throw new \Exception("Không đủ tồn kho để trừ. Material ID: {$component['id']}, Cần: {$totalRequiredQty}, Có: {$availableQty}");
                }
            }

            // STEP 6: Update product inventory in target warehouse
            // First, remove old product from old target warehouse
            if ($oldTargetWarehouseId) {
                $oldWarehouseProduct = WarehouseMaterial::where('warehouse_id', $oldTargetWarehouseId)
                    ->where('material_id', $oldProductId)
                    ->where('item_type', 'product')
                    ->first();

                if ($oldWarehouseProduct) {
                    if ($oldWarehouseProduct->quantity >= $oldProductQty) {
                        $oldWarehouseProduct->decrement('quantity', $oldProductQty);
                        Log::info("Assembly update: Removed {$oldProductQty} of old product ID {$oldProductId} from old warehouse {$oldTargetWarehouseId}");
                    } else {
                        // Not enough quantity, set to 0 and log warning
                        $actualQuantity = $oldWarehouseProduct->quantity;
                        $oldWarehouseProduct->update(['quantity' => 0]);
                        Log::warning("Assembly update: Only {$actualQuantity} of old product ID {$oldProductId} available in old warehouse {$oldTargetWarehouseId}, expected {$oldProductQty}. Set to 0.");
                    }
                } else {
                    Log::warning("Assembly update: Old product ID {$oldProductId} not found in old warehouse {$oldTargetWarehouseId}");
                }
            }

            // Then, add new product to new target warehouse
            $this->updateProductToTargetWarehouse($assembly);
            
            // STEP 7: Update or create associated testing record
            $this->updateOrCreateTestingRecord($assembly);

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

            // 2. Remove assembled product from target warehouse
            if ($assembly->target_warehouse_id && $assembly->product_id) {
                $warehouseProduct = WarehouseMaterial::where('warehouse_id', $assembly->target_warehouse_id)
                    ->where('material_id', $assembly->product_id)
                    ->where('item_type', 'product')
                    ->first();

                if ($warehouseProduct) {
                    if ($warehouseProduct->quantity >= $productQuantity) {
                        // Sufficient quantity, decrement normally
                        $warehouseProduct->decrement('quantity', $productQuantity);
                        Log::info("Assembly deletion: Removed {$productQuantity} of product ID {$assembly->product_id} from warehouse {$assembly->target_warehouse_id}");
                    } else {
                        // Not enough quantity, set to 0 and log warning
                        $actualQuantity = $warehouseProduct->quantity;
                        $warehouseProduct->update(['quantity' => 0]);
                        Log::warning("Assembly deletion: Only {$actualQuantity} of product ID {$assembly->product_id} available in warehouse {$assembly->target_warehouse_id}, expected {$productQuantity}. Set to 0.");
                    }
                } else {
                    // Product not found in warehouse, log warning
                    Log::warning("Assembly deletion: Product ID {$assembly->product_id} not found in warehouse {$assembly->target_warehouse_id}. Cannot remove {$productQuantity} units.");
                }
            }

            // 3. Delete serial records for this assembly
            $this->deleteSerialRecords($assembly->id);

            // 4. Check and handle related testing records
            $relatedTestings = \App\Models\Testing::where('assembly_id', $assembly->id)->get();
            foreach ($relatedTestings as $testing) {
                // Nếu phiếu kiểm thử chưa hoàn thành, xóa nó
                if ($testing->status != 'completed') {
                    // Xóa các items và details của phiếu kiểm thử
                    $testing->items()->delete();
                    $testing->details()->delete();
                    $testing->delete();
                    Log::info("Assembly deletion: Deleted related testing record ID {$testing->id}");
                } else {
                    // Nếu phiếu kiểm thử đã hoàn thành, chỉ bỏ liên kết
                    $testing->update(['assembly_id' => null]);
                    Log::info("Assembly deletion: Unlinked completed testing record ID {$testing->id}");
                }
            }

            // 5. Delete related materials
            $assembly->materials()->delete();

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

    /**
     * Create a testing record for an assembly
     */
    private function createTestingRecordForAssembly(Assembly $assembly)
    {
        // Generate test code
        $testCode = 'QA-' . Carbon::now()->format('ymd');
        $lastTest = \App\Models\Testing::where('test_code', 'like', $testCode . '%')
            ->orderBy('test_code', 'desc')
            ->first();
            
        if ($lastTest) {
            $lastNumber = (int) substr($lastTest->test_code, -3);
            $testCode .= str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $testCode .= '001';
        }
        
        // Lấy người tạo phiếu hiện tại nếu có
        $currentUserId = null;
        if (Auth::check() && Auth::user() && Auth::user()->employee) {
            $currentUserId = Auth::user()->employee->id;
        }
        
        // Nếu không có người dùng hiện tại, sử dụng tester_id từ assembly
        if (!$currentUserId) {
            $currentUserId = $assembly->tester_id;
        }
        
        // Create testing record linked to this assembly
        $testing = \App\Models\Testing::create([
            'test_code' => $testCode,
            'test_type' => 'finished_product', // Thành phẩm
            'tester_id' => $currentUserId, // Người tạo phiếu (người hiện tại hoặc từ assembly)
            'assigned_to' => $assembly->assigned_employee_id, // Người phụ trách (từ phiếu lắp ráp)
            'receiver_id' => $assembly->tester_id, // Người tiếp nhận kiểm thử (từ phiếu lắp ráp)
            'test_date' => $assembly->date, // Sử dụng ngày lắp ráp
            'notes' => 'Tự động tạo từ phiếu lắp ráp ' . $assembly->code,
            'status' => 'pending',
            'assembly_id' => $assembly->id, // Liên kết với phiếu lắp ráp
        ]);
        
        // Add testing item for the assembled product
        \App\Models\TestingItem::create([
            'testing_id' => $testing->id,
            'item_type' => 'product',
            'product_id' => $assembly->product_id,
            'quantity' => $assembly->quantity,
            'serial_number' => $assembly->product_serials, // Sử dụng serial từ phiếu lắp ráp
            'result' => 'pending',
        ]);
        
        // Thêm các vật tư từ phiếu lắp ráp vào phiếu kiểm thử
        foreach ($assembly->materials as $material) {
            \App\Models\TestingItem::create([
                'testing_id' => $testing->id,
                'item_type' => 'material',
                'material_id' => $material->material_id,
                'quantity' => $material->quantity,
                'serial_number' => $material->serial,
                'result' => 'pending',
            ]);
        }
        
        // Add default testing items
        $defaultTestItems = [
            'Kiểm tra ngoại quan',
            'Kiểm tra chức năng cơ bản',
            'Kiểm tra hoạt động liên tục'
        ];
        
        foreach ($defaultTestItems as $testItem) {
            \App\Models\TestingDetail::create([
                'testing_id' => $testing->id,
                'test_item_name' => $testItem,
                'result' => 'pending',
            ]);
        }
        
        return $testing;
    }

    /**
     * Update or create testing record for an assembly
     */
    private function updateOrCreateTestingRecord(Assembly $assembly)
    {
        // Load the assembly with its materials if not already loaded
        if (!$assembly->relationLoaded('materials')) {
            $assembly->load('materials.material');
        }
        
        // Lấy người tạo phiếu hiện tại nếu có
        $currentUserId = null;
        if (Auth::check() && Auth::user() && Auth::user()->employee) {
            $currentUserId = Auth::user()->employee->id;
        }
        
        // Nếu không có người dùng hiện tại, sử dụng tester_id từ assembly
        if (!$currentUserId) {
            $currentUserId = $assembly->tester_id;
        }
        
        // Check if there's an existing testing record for this assembly
        $testing = \App\Models\Testing::where('assembly_id', $assembly->id)->first();
        
        if ($testing) {
            // Update existing testing record
            $testing->update([
                'test_type' => 'finished_product',
                'assigned_to' => $assembly->assigned_employee_id, // Người phụ trách (từ phiếu lắp ráp)
                'receiver_id' => $assembly->tester_id, // Người tiếp nhận kiểm thử (từ phiếu lắp ráp)
                'test_date' => $assembly->date,
                'notes' => 'Tự động cập nhật từ phiếu lắp ráp ' . $assembly->code,
            ]);
            
            // Delete existing items and recreate them
            $testing->items()->delete();
            
            // Add testing item for the assembled product
            \App\Models\TestingItem::create([
                'testing_id' => $testing->id,
                'item_type' => 'product',
                'product_id' => $assembly->product_id,
                'quantity' => $assembly->quantity,
                'serial_number' => $assembly->product_serials,
                'result' => 'pending',
            ]);
            
            // Add materials from assembly
            foreach ($assembly->materials as $material) {
                \App\Models\TestingItem::create([
                    'testing_id' => $testing->id,
                    'item_type' => 'material',
                    'material_id' => $material->material_id,
                    'quantity' => $material->quantity,
                    'serial_number' => $material->serial,
                    'result' => 'pending',
                ]);
            }
            
            return $testing;
        } else {
            // Create new testing record
            return $this->createTestingRecordForAssembly($assembly);
        }
    }
}
