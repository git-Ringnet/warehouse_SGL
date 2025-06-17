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
use App\Exports\AssemblyExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AssemblyController extends Controller
{
    /**
     * Display a listing of the assemblies.
     */
    public function index(Request $request)
    {
        // Build the query
        $query = Assembly::with(['product', 'products.product', 'assignedEmployee', 'tester', 'warehouse', 'targetWarehouse', 'project']);
        
        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('notes', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('assignedEmployee', function ($eq) use ($searchTerm) {
                      $eq->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('tester', function ($tq) use ($searchTerm) {
                      $tq->where('name', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('warehouse', function ($wq) use ($searchTerm) {
                      $wq->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('code', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('project', function ($pq) use ($searchTerm) {
                      $pq->where('project_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('project_code', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }
        
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Apply warehouse filter
        if ($request->filled('warehouse')) {
            $query->where('warehouse_id', $request->warehouse);
        }
        
        // Apply employee filter
        if ($request->filled('employee')) {
            $query->where('assigned_employee_id', $request->employee);
        }
        
        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        
        $assemblies = $query->orderBy('created_at', 'desc')->get();
        
        // Get filter options for dropdowns
        $statuses = [
            'pending' => 'Chờ xử lý',
            'in_progress' => 'Đang thực hiện',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy'
        ];
        
        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
            
        $employees = Employee::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        // Handle AJAX requests
        if ($request->ajax()) {
            return view('assemble.partials.assembly-list', compact('assemblies'))->render();
        }
        
        return view('assemble.index', compact('assemblies', 'statuses', 'warehouses', 'employees'));
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

                        // Check for existing serial in assembly_products table (for new multi-product assemblies)
                        $existingAssemblyProduct = AssemblyProduct::where('product_id', $productData['id'])
                            ->where('serials', 'like', '%' . $serial . '%')
                            ->first();

                        if ($existingAssemblyProduct) {
                            $existingAssembly = Assembly::find($existingAssemblyProduct->assembly_id);
                            throw new \Exception("Serial thành phẩm '{$serial}' đã tồn tại trong phiếu lắp ráp #{$existingAssembly->code}.");
                        }

                        // Also check legacy assemblies with product_serials field
                        $existingLegacyAssembly = Assembly::where('product_serials', 'like', '%' . $serial . '%')
                            ->whereNotNull('product_serials')
                            ->first();

                        if ($existingLegacyAssembly) {
                            throw new \Exception("Serial thành phẩm '{$serial}' đã tồn tại trong phiếu lắp ráp #{$existingLegacyAssembly->code}.");
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

            // Create single assembly record for all products
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
                'product_serials' => null, // Legacy field for single product assemblies
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
                if ($assembly->target_warehouse_id) {
                    $this->updateProductToTargetWarehouse($productData['id'], $assembly->target_warehouse_id, $productQty);
                }
            }

            // Create assembly materials and update stock levels
            foreach ($components as $component) {
                // Process serials - either from multiple inputs or single input
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

                            // Find if this material serial already exists in other assemblies
                            $existingMaterial = AssemblyMaterial::where('material_id', $component['id'])
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

                    // Check single serial existence in database if not empty
                    if (!empty($serial)) {
                        $existingMaterial = AssemblyMaterial::where('material_id', $component['id'])
                            ->where('serial', $serial)
                            ->first();

                        if ($existingMaterial) {
                            $existingAssembly = Assembly::find($existingMaterial->assembly_id);
                            $materialName = Material::find($component['id'])->name ?? 'Unknown';
                            throw new \Exception("Serial '{$serial}' của linh kiện '{$materialName}' đã được sử dụng trong phiếu lắp ráp #{$existingAssembly->code}.");
                        }
                    }
                }

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
                $componentQty = intval($component['quantity']);
                $totalRequiredQty = $componentQty * $productQty; // Calculate total quantity needed

                // Create assembly material record
                AssemblyMaterial::create([
                    'assembly_id' => $assembly->id,
                    'material_id' => $component['id'],
                    'target_product_id' => $componentProductId, // Link component to specific product
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

            // Create single testing record for this assembly
            $testing = $this->createTestingRecordForAssembly($assembly);

            DB::commit();

            // Tạo thông báo thành công với link đến phiếu kiểm thử
            $successMessage = 'Phiếu lắp ráp đã được tạo thành công!';

            // Nếu có phiếu kiểm thử được tạo, thêm thông báo và link
            if ($testing) {
                $testingUrl = route('testing.show', $testing->id);
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
        $assembly->load(['product', 'products.product', 'materials.material', 'assignedEmployee', 'tester', 'warehouse', 'targetWarehouse', 'project']);
        return view('assemble.show', compact('assembly'));
    }

    /**
     * Show the form for editing the specified assembly.
     */
    public function edit(Assembly $assembly)
    {
        // Load necessary relationships for edit mode
        $assembly->load([
            'product', 
            'products.product', 
            'materials.material', 
            'warehouse', 
            'targetWarehouse',
            'assignedEmployee',
            'tester',
            'project'
        ]);

        // Parse product serials if they exist (legacy support)
        $productSerials = [];
        if ($assembly->product_serials) {
            $productSerials = explode(',', $assembly->product_serials);
        }

        return view('assemble.edit', compact('assembly', 'productSerials'));
    }

    /**
     * Update the specified assembly in storage.
     */
    public function update(Request $request, Assembly $assembly)
    {
        // Simplified validation for edit mode - only allow updating date, serials, and notes
        $request->validate([
            'assembly_date' => 'required|date',
            'assembly_note' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.serials' => 'nullable|array',
            'components' => 'nullable|array',
            'components.*.serial' => 'nullable|string',
            'components.*.note' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // 1. Validate product serials for duplicates
            $allProductSerials = [];
            foreach ($request->products as $productData) {
                if (isset($productData['serials']) && is_array($productData['serials'])) {
                    $filteredSerials = array_filter($productData['serials']);
                    
                    // Check for duplicates within the form
                    foreach ($filteredSerials as $serial) {
                        if (in_array($serial, $allProductSerials)) {
                            throw new \Exception("Serial thành phẩm '{$serial}' bị trùng lặp.");
                        }
                        $allProductSerials[] = $serial;
                    }

                    // Check for duplicates in database (excluding current assembly)
                    foreach ($filteredSerials as $serial) {
                        if (empty($serial)) continue;

                        $existingAssembly = Assembly::whereHas('products', function ($query) use ($serial, $productData) {
                            $query->where('product_id', $productData['id'])
                                ->where('serials', 'like', '%' . $serial . '%');
                        })->where('id', '!=', $assembly->id)->first();

                        if ($existingAssembly) {
                            throw new \Exception("Serial thành phẩm '{$serial}' đã tồn tại trong phiếu lắp ráp #{$existingAssembly->code}.");
                        }

                        // Check in serials table
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

            // 2. Validate component serials for duplicates
            if ($request->components) {
                foreach ($request->components as $component) {
                    if (!empty($component['serial'])) {
                        $existingMaterial = AssemblyMaterial::whereHas('assembly', function ($query) use ($assembly) {
                            $query->where('id', '!=', $assembly->id);
                        })
                            ->where('material_id', $component['id'])
                            ->where('serial', $component['serial'])
                            ->first();

                        if ($existingMaterial) {
                            $existingAssembly = Assembly::find($existingMaterial->assembly_id);
                            $materialName = Material::find($component['id'])->name ?? 'Unknown';
                            throw new \Exception("Serial '{$component['serial']}' của linh kiện '{$materialName}' đã được sử dụng trong phiếu lắp ráp #{$existingAssembly->code}.");
                        }
                    }
                }
            }

            // 3. Update assembly basic info (only date and notes)
            $assembly->update([
                'date' => $request->assembly_date,
                'notes' => $request->assembly_note,
            ]);

            // 4. Update product serials
            $this->deleteSerialRecords($assembly->id);
            
            foreach ($request->products as $productIndex => $productData) {
                if (isset($productData['serials']) && is_array($productData['serials'])) {
                    $filteredSerials = array_filter($productData['serials']);
                    $productSerialsStr = !empty($filteredSerials) ? implode(',', $filteredSerials) : null;

                    // Update assembly product serials
                    $assemblyProduct = AssemblyProduct::where('assembly_id', $assembly->id)
                        ->where('product_id', $productData['id'])
                        ->first();
                    
                    if ($assemblyProduct) {
                        $assemblyProduct->update(['serials' => $productSerialsStr]);
                        
                        // Create new serial records
                        $this->createSerialRecords($filteredSerials, $productData['id'], $assembly->id);
                    }
                }
            }

            // 5. Update component serials and notes (if components exist)
            if ($request->components) {
                foreach ($request->components as $componentIndex => $component) {
                    // Find the specific assembly material by the global index
                    $assemblyMaterial = AssemblyMaterial::where('assembly_id', $assembly->id)
                        ->get()
                        ->get($componentIndex); // Get by array index
                    
                    if ($assemblyMaterial) {
                        $assemblyMaterial->update([
                            'serial' => $component['serial'] ?? null,
                            'note' => $component['note'] ?? null,
                        ]);
                    }
                }
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

            // Load assembly products if not already loaded for calculation
            if (!$assembly->relationLoaded('products')) {
                $assembly->load('products');
            }

            foreach ($assembly->materials as $material) {
                // Calculate total quantity to return based on actual products in assembly
                $totalQuantityToReturn = 0;

                if ($assembly->products && $assembly->products->count() > 0) {
                    // Multi-product assembly: sum up quantities for all products
                    foreach ($assembly->products as $assemblyProduct) {
                        $totalQuantityToReturn += $material->quantity * $assemblyProduct->quantity;
                    }
                } else {
                    // Legacy single product assembly - use default quantity of 1
                    $totalQuantityToReturn = $material->quantity * 1;
                }

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
        // This method is no longer needed since product_id and quantity columns 
        // have been removed from assemblies table
        // Use assembly->products relationship instead
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
            // Check in assembly_products table (for new multi-product assemblies)
            $query = AssemblyProduct::where('product_id', $productId)
                ->where('serials', 'like', '%' . $serial . '%');

            // Exclude current assembly if editing
            if ($assemblyId) {
                $query->whereHas('assembly', function ($q) use ($assemblyId) {
                    $q->where('id', '!=', $assemblyId);
                });
            }

            $existingAssemblyProduct = $query->first();

            if ($existingAssemblyProduct) {
                $existingAssembly = $existingAssemblyProduct->assembly;
                return response()->json([
                    'exists' => true,
                    'message' => "Serial đã tồn tại trong phiếu lắp ráp #{$existingAssembly->code}",
                    'type' => 'assembly'
                ]);
            }

            // Also check legacy assemblies with product_serials field
            $legacyQuery = Assembly::where('product_serials', 'like', '%' . $serial . '%')
                ->whereNotNull('product_serials');

            // Exclude current assembly if editing
            if ($assemblyId) {
                $legacyQuery->where('id', '!=', $assemblyId);
            }

            $existingAssembly = $legacyQuery->first();

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

        // Find the latest assembly code for today
        $latestAssembly = Assembly::where('code', 'like', $prefix . $date . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($latestAssembly) {
            // Extract the sequence number from the code
            $code = $latestAssembly->code;

            // Extract last 3 digits and increment
            if (preg_match('/^' . preg_quote($prefix . $date) . '(\d{3})$/', $code, $matches)) {
                $sequence = intval($matches[1]) + 1;
            } else {
                // Fallback: extract last 3 digits
                $sequence = intval(substr($code, -3)) + 1;
            }
        } else {
            $sequence = 1;
        }

        $baseCode = $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return response()->json(['code' => $baseCode]);
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
        // Load relationships if not already loaded
        if (!$assembly->relationLoaded('products')) {
            $assembly->load('products.product');
        }
        if (!$assembly->relationLoaded('materials')) {
            $assembly->load('materials.material');
        }

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

        // Add testing items for all assembled products
        if ($assembly->products && $assembly->products->count() > 0) {
            // Multi-product assembly (new structure)
            foreach ($assembly->products as $assemblyProduct) {
                \App\Models\TestingItem::create([
                    'testing_id' => $testing->id,
                    'item_type' => 'product',
                    'product_id' => $assemblyProduct->product_id,
                    'quantity' => $assemblyProduct->quantity,
                    'serial_number' => $assemblyProduct->serials,
                    'result' => 'pending',
                ]);
            }
        } else {
            // No products found - this shouldn't happen for new assemblies
            Log::warning("Assembly {$assembly->code} has no products in assembly_products table");
        }

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

            // Add testing items for all assembled products
            foreach ($assembly->products as $assemblyProduct) {
                \App\Models\TestingItem::create([
                    'testing_id' => $testing->id,
                    'item_type' => 'product',
                    'product_id' => $assemblyProduct->product_id,
                    'quantity' => $assemblyProduct->quantity,
                    'serial_number' => $assemblyProduct->serials,
                    'result' => 'pending',
                ]);
            }

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
    
    /**
     * Export assembly to Excel
     */
    public function exportExcel(Assembly $assembly)
    {
        try {
            return Excel::download(new AssemblyExport($assembly), 'phieu-lap-rap-' . $assembly->code . '-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Export Assembly Excel error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất Excel: ' . $e->getMessage());
        }
    }
    
    /**
     * Export assembly to PDF
     */
    public function exportPdf(Assembly $assembly)
    {
        try {
            // Load relationships
            $assembly->load([
                'products.product',
                'materials.material',
                'warehouse',
                'targetWarehouse',
                'assignedEmployee',
                'tester',
                'project'
            ]);
            
            $pdf = PDF::loadView('assemble.pdf', compact('assembly'));
            
            return $pdf->download('phieu-lap-rap-' . $assembly->code . '-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Export Assembly PDF error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất PDF: ' . $e->getMessage());
        }
    }
}
