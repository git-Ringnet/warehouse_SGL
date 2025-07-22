<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\AssemblyMaterial;
use App\Models\AssemblyProduct;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Project;
use App\Models\Serial;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use App\Exports\AssemblyExport;
use App\Helpers\ChangeLogHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\UserLog;
use App\Http\Controllers\ChangeLogController;

class AssemblyController extends Controller
{
    /**
     * Display a listing of the assemblies.
     */
    public function index(Request $request)
    {
        // Build the query
        $query = Assembly::with(['product', 'products.product', 'assignedEmployee', 'tester', 'project']);

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

        // Get projects based on user's role
        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        $projectsQuery = Project::orderBy('project_name');
        
        if ($user->role !== 'admin') {
            // If user is not admin, get only projects assigned to their role
            $projectsQuery->whereHas('roles', function ($query) use ($user) {
                $query->where('roles.id', $user->role_id);
            });
        }
        
        $projects = $projectsQuery->get(['id', 'project_name', 'project_code']);

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
            'components.*.warehouse_id' => 'required|exists:warehouses,id',
            'components.*.product_id' => 'required',
        ]);

        // dd($request->all());

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

                        // Check in the serials table (exact match, case-insensitive)
                        $existingSerial = Serial::whereRaw('LOWER(serial_number) = ?', [strtolower($serial)])
                            ->where('product_id', $productData['id'])
                            ->where('type', 'product') // Only check for type='product'
                            ->first();

                        if ($existingSerial) {
                            throw new \Exception("Serial '{$serial}' đã tồn tại trong cơ sở dữ liệu.");
                        }
                    }
                }
            }

            // Validate stock levels and serials for all components
            foreach ($components as $component) {
                $materialId = $component['id'];
                $warehouseId = $component['warehouse_id'];
                $componentQty = intval($component['quantity']);

                // Find the product this component belongs to
                $componentProductId = $component['product_id'];
                if (is_string($componentProductId) && strpos($componentProductId, 'product_') === 0) {
                    $componentProductId = str_replace('product_', '', $componentProductId);
                }
                $componentProductId = intval($componentProductId);

                $productData = collect($products)->firstWhere('id', $componentProductId);
                if (!$productData) {
                    Log::error('Component product not found', [
                        'component_product_id' => $component['product_id'],
                        'converted_id' => $componentProductId,
                        'available_products' => collect($products)->pluck('id')->toArray(),
                        'material_id' => $materialId
                    ]);
                    throw new \Exception('Không tìm thấy thành phẩm cho linh kiện. Component product ID: ' . $component['product_id']);
                }

                $productQty = intval($productData['quantity']);

                // Check stock level in the specific warehouse
                $warehouseMaterial = WarehouseMaterial::where('material_id', $materialId)
                    ->where('warehouse_id', $warehouseId)
                    ->where('quantity', '>=', $componentQty * $productQty)
                    ->first();

                if (!$warehouseMaterial) {
                    $material = Material::find($materialId);
                    $warehouse = Warehouse::find($warehouseId);
                    throw new \Exception("Không đủ số lượng vật tư '{$material->name}' trong kho '{$warehouse->name}'");
                }
            }

            // Create assembly record
            $assembly = new Assembly();
            $assembly->code = $request->assembly_code;
            $assembly->date = $request->assembly_date;
            $assembly->assigned_employee_id = $request->assigned_to;
            $assembly->tester_id = $request->tester_id;
            $assembly->purpose = $request->purpose;
            $assembly->project_id = $request->project_id;
            $assembly->notes = $request->assembly_note;
            $assembly->status = 'pending';
            $assembly->created_by = Auth::id();
            $assembly->save();

            // Create assembly materials
            foreach ($components as $component) {
                $assemblyMaterial = new AssemblyMaterial();
                $assemblyMaterial->assembly_id = $assembly->id;
                $assemblyMaterial->material_id = $component['id'];
                $assemblyMaterial->warehouse_id = $component['warehouse_id'];
                $assemblyMaterial->quantity = $component['quantity'];
                $assemblyMaterial->notes = $component['note'] ?? null;
                $assemblyMaterial->product_unit = $component['product_unit'] ?? 0;

                // Handle serial and serial_id
                if (isset($component['serial']) && !empty($component['serial'])) {
                    $assemblyMaterial->serial = $component['serial'];
                }
                if (isset($component['serial_id']) && !empty($component['serial_id'])) {
                    $assemblyMaterial->serial_id = $component['serial_id'];
                }

                $assemblyMaterial->save();
            }

            // Create assembly products
            foreach ($products as $productData) {
                $assemblyProduct = new AssemblyProduct();
                $assemblyProduct->assembly_id = $assembly->id;
                $assemblyProduct->product_id = $productData['id'];
                $assemblyProduct->quantity = $productData['quantity'];

                // Handle product serials
                if (isset($productData['serials']) && !empty($productData['serials'])) {
                    $filteredSerials = array_filter($productData['serials']);
                    if (!empty($filteredSerials)) {
                        $assemblyProduct->serials = implode(',', $filteredSerials);
                    }
                }

                $assemblyProduct->save();

                // Create serial records for product
                if (!empty($productData['serials'])) {
                    foreach ($productData['serials'] as $serial) {
                        if (empty($serial)) continue;

                        Serial::create([
                            'serial_number' => $serial,
                            'product_id' => $productData['id'],
                            'type' => 'product',
                            'status' => 'active',
                            'notes' => 'Assembly ID: ' . $assembly->id
                        ]);
                    }
                }
            }

            // Create notification for assigned employee
            Notification::create([
                'user_id' => $assembly->assignedEmployee->user_id,
                'title' => 'Phiếu lắp ráp mới',
                'message' => "Bạn được phân công phiếu lắp ráp {$assembly->code}",
                'type' => 'assembly_assigned',
                'reference_id' => $assembly->id
            ]);

            // Create notification for tester
            Notification::create([
                'user_id' => $assembly->tester->user_id,
                'title' => 'Phiếu lắp ráp cần kiểm thử',
                'message' => "Bạn được phân công kiểm thử phiếu lắp ráp {$assembly->code}",
                'type' => 'assembly_test_assigned',
                'reference_id' => $assembly->id
            ]);

            // Log the activity
            ChangeLogController::createLogEntry([
                'item_code' => $assembly->code,
                'item_name' => 'Phiếu lắp ráp',
                'change_type' => 'lap_rap',
                'document_code' => $assembly->code,
                'description' => 'Tạo phiếu lắp ráp',
                'performed_by' => Auth::user()->name,
                'detailed_info' => $assembly->toArray()
            ]);

            // Log user action
            UserLog::logActivity(
                Auth::id(),
                'create',
                'assemblies',
                "Tạo phiếu lắp ráp {$assembly->code}",
                null,
                $assembly->toArray()
            );

            DB::commit();

            return redirect()
                ->route('assemblies.index')
                ->with('success', 'Tạo phiếu lắp ráp thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assembly creation error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified assembly.
     */
    public function show(Assembly $assembly)
    {
        $assembly->load(['product', 'products.product', 'materials.material', 'assignedEmployee', 'tester', 'project']);

        // Ghi nhật ký xem chi tiết phiếu lắp ráp
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'assemblies',
                'Xem chi tiết phiếu lắp ráp: ' . $assembly->code,
                null,
                $assembly->toArray()
            );
        }

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
            'assignedEmployee',
            'tester',
            'project'
        ]);

        // Parse product serials if they exist (legacy support)
        $productSerials = [];
        if ($assembly->product_serials) {
            $productSerials = explode(',', $assembly->product_serials);
        }

        // Load all material serials for each material
        $materialSerials = [];
        foreach ($assembly->materials as $material) {
            $query = Serial::where('product_id', $material->material_id)
                ->where('type', 'material')
                ->where('warehouse_id', $material->warehouse_id)
                ->where('status', 'active')
                ->where(function ($q) use ($assembly, $material) {
                    $q->whereNull('notes')
                        ->orWhere('notes', 'like', '%Assembly ID: ' . $assembly->id . '%');
                });

            // Get existing serials for this material
            $existingSerials = [];
            if ($material->serials) {
                $existingSerials = array_map('trim', explode(',', $material->serials));
            }

            // Add existing serials that might not be in warehouse anymore
            $serials = $query->orderBy('serial_number')->get(['id', 'serial_number'])->toArray();
            foreach ($existingSerials as $existingSerial) {
                $found = false;
                foreach ($serials as $serial) {
                    if ($serial['serial_number'] === $existingSerial) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $serials[] = [
                        'id' => null,
                        'serial_number' => $existingSerial
                    ];
                }
            }

            $materialSerials[$material->material_id] = $serials;
        }

        // Load all product serials
        $allProductSerials = [];
        foreach ($assembly->products as $assemblyProduct) {
            $query = Serial::where('product_id', $assemblyProduct->product_id)
                ->where('type', 'material')
                ->where('status', 'active')
                ->where(function ($q) use ($assembly) {
                    $q->whereNull('notes')
                        ->orWhere('notes', 'like', '%Assembly ID: ' . $assembly->id . '%');
                })
                ->orderBy('serial_number');

            $allProductSerials[$assemblyProduct->product_id] = $query->get(['id', 'serial_number'])->toArray();
        }

        return view('assemble.edit', compact('assembly', 'productSerials', 'materialSerials', 'allProductSerials'));
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
            'components.*.product_unit' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Lưu dữ liệu cũ trước khi cập nhật
            $oldData = $assembly->toArray();

            // 1. Validate product serials for duplicates
            foreach ($request->products as $productIndex => $productData) {
                if (isset($productData['serials']) && is_array($productData['serials'])) {
                    $filteredSerials = array_filter($productData['serials']);

                    // Check for duplicates within this specific product only
                    if (count($filteredSerials) !== count(array_unique($filteredSerials))) {
                        $productCode = $productData['code'] ?? 'Unknown';
                        throw new \Exception("Không được nhập trùng serial thành phẩm [{$productCode}].");
                    }

                    // Check for duplicates in database (excluding current assembly)
                    foreach ($filteredSerials as $serial) {
                        if (empty($serial)) continue;

                        // Check in serials table (exact match, case-insensitive)
                        $existingSerial = Serial::whereRaw('LOWER(serial_number) = ?', [strtolower($serial)])
                            ->where('product_id', $productData['id'])
                            ->where('type', 'product') // Only check for type='product'
                            ->first();

                        // If serial exists, check if it belongs to current assembly
                        if ($existingSerial) {
                            $expectedNote = 'Assembly ID: ' . $assembly->id;
                            if ($existingSerial->notes !== $expectedNote) {
                                throw new \Exception("Serial '{$serial}' đã tồn tại trong cơ sở dữ liệu.");
                            }
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

                        // Use target_warehouse_id if available, otherwise use source warehouse_id
                        $targetWarehouseId = $assembly->target_warehouse_id;
                        if ($assembly->purpose === 'project' && !$targetWarehouseId) {
                            $targetWarehouseId = $assembly->warehouse_id;
                        }
                        
                        // Create new serial records with correct warehouse
                        $this->createSerialRecords($filteredSerials, $productData['id'], $assembly->id, $targetWarehouseId);
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
                        // Reset old serial if exists
                        if ($assemblyMaterial->serial_id) {
                            Serial::where('id', $assemblyMaterial->serial_id)
                                ->update(['notes' => null]);
                        }

                        // Process serials if present
                        $serial = null;
                        if (isset($component['serials']) && is_array($component['serials'])) {
                            // Filter out empty serials
                            $filteredSerials = array_filter($component['serials']);

                            // Only use unique values to prevent duplicates
                            $uniqueSerials = array_unique($filteredSerials);

                            // Convert to comma-separated string
                            $serial = implode(',', $uniqueSerials);
                        } elseif (isset($component['serial'])) {
                            $serial = $component['serial'];
                        } else {
                            $serial = $component['serial'] ?? null;
                        }

                        $updateData = [
                            'serial' => $serial,
                            'note' => $component['note'] ?? null,
                            'serial_id' => null, // Reset first
                        ];

                        // Set new serial_id if provided
                        if (isset($component['serial_id']) && !empty($component['serial_id'])) {
                            $updateData['serial_id'] = $component['serial_id'];

                            // Update new serial status
                            Serial::where('id', $component['serial_id'])
                                ->update(['notes' => 'Assembly ID: ' . $assembly->id]);
                        }

                        // Update product_unit if provided
                        if (isset($component['product_unit'])) {
                            $updateData['product_unit'] = (int)$component['product_unit'];
                        }

                        $assemblyMaterial->update($updateData);
                    }
                }
            }

            DB::commit();

            // Ghi nhật ký cập nhật phiếu lắp ráp
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'assemblies',
                    'Cập nhật phiếu lắp ráp: ' . $assembly->code,
                    $oldData,
                    $assembly->toArray()
                );
            }

            return redirect()->route('assemblies.show', $assembly->id)->with('success', 'Phiếu lắp ráp đã được cập nhật thành công');
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
            // Load necessary relationships
            $assembly->load(['materials', 'products']);

            // 1. Return components back to their source warehouses
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
                $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $material->warehouse_id)
                    ->where('material_id', $material->material_id)
                    ->where('item_type', 'material')
                    ->first();

                if ($warehouseMaterial) {
                    // Material exists, increment quantity
                    $warehouseMaterial->increment('quantity', $totalQuantityToReturn);
                } else {
                    // Material doesn't exist, create new record
                    WarehouseMaterial::create([
                        'warehouse_id' => $material->warehouse_id,
                        'material_id' => $material->material_id,
                        'quantity' => $totalQuantityToReturn,
                        'item_type' => 'material'
                    ]);
                }

                Log::info("Assembly deletion: Returned {$totalQuantityToReturn} of material ID {$material->material_id} to warehouse {$material->warehouse_id}");
            }

            // 2. Remove assembled products from target warehouse if exists
            $dispatch = Dispatch::where('dispatch_note', 'like', '%phiếu lắp ráp ' . $assembly->code . '%')->first();
            if ($dispatch) {
                // Restore warehouse stock for each dispatch item
                foreach ($dispatch->items as $dispatchItem) {
                    if ($dispatchItem->item_type === 'product') {
                        $warehouseProduct = WarehouseMaterial::where('warehouse_id', $dispatchItem->warehouse_id)
                            ->where('material_id', $dispatchItem->item_id)
                        ->where('item_type', 'product')
                        ->first();

                    if ($warehouseProduct) {
                            // Product exists, increment quantity
                            $warehouseProduct->increment('quantity', $dispatchItem->quantity);
                            Log::info("Restored {$dispatchItem->quantity} of product ID {$dispatchItem->item_id} to warehouse {$dispatchItem->warehouse_id}");
                        } else {
                            // Product doesn't exist, create new record
                            WarehouseMaterial::create([
                                'warehouse_id' => $dispatchItem->warehouse_id,
                                'material_id' => $dispatchItem->item_id,
                                'quantity' => $dispatchItem->quantity,
                                'item_type' => 'product'
                            ]);
                            Log::info("Created warehouse record and restored {$dispatchItem->quantity} of product ID {$dispatchItem->item_id} to warehouse {$dispatchItem->warehouse_id}");
                        }
                    }
                }

                // Delete dispatch items first
                $dispatch->items()->delete();
                // Delete the dispatch
                $dispatch->delete();
                Log::info("Deleted related dispatch for assembly {$assembly->code}");
            }

            // 3. Delete serial records for this assembly and restore material serials
            $this->deleteSerialRecords($assembly->id);

            // Reset all material serial statuses for this assembly
            // First, handle serial_id field
            Serial::whereIn('id', function ($query) use ($assembly) {
                $query->select('serial_id')
                    ->from('assembly_materials')
                    ->where('assembly_id', $assembly->id)
                    ->whereNotNull('serial_id');
            })->update(['notes' => null]);

            // Then, handle serial field (comma-separated serial numbers)
            $serialNumbers = AssemblyMaterial::where('assembly_id', $assembly->id)
                ->whereNotNull('serial')
                ->pluck('serial')
                ->flatMap(function ($serial) {
                    return explode(',', $serial);
                })
                ->filter()
                ->unique()
                ->toArray();

            if (!empty($serialNumbers)) {
                Serial::whereIn('serial_number', $serialNumbers)
                    ->update(['notes' => null]);
            }

            // 4. Delete related materials
            $assembly->materials()->delete();

            // 5. Delete assembly products
            $assembly->products()->delete();

            // 6. Delete the assembly
            $assembly->delete();

            DB::commit();

            // Ghi nhật ký xóa phiếu lắp ráp
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'assemblies',
                    'Xóa phiếu lắp ráp: ' . $assembly->code,
                    $assembly->toArray(),
                    null
                );
            }

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
        Log::info('Updating product inventory', [
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity
        ]);

        // Kiểm tra xem thành phẩm đã có trong kho chưa
        $warehouseProduct = WarehouseMaterial::where('warehouse_id', $warehouseId)
            ->where('material_id', $productId)
            ->where('item_type', 'product')
            ->first();

        if ($warehouseProduct) {
            // Nếu đã có, tăng số lượng
            $warehouseProduct->increment('quantity', $quantity);
            Log::info('Incremented existing product quantity', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'old_quantity' => $warehouseProduct->quantity - $quantity,
                'new_quantity' => $warehouseProduct->quantity
            ]);
        } else {
            // Nếu chưa có, tạo mới
            $newProduct = WarehouseMaterial::create([
                'warehouse_id' => $warehouseId,
                'material_id' => $productId,
                'quantity' => $quantity,
                'item_type' => 'product' // Xác định đây là thành phẩm, không phải linh kiện
            ]);
            Log::info('Created new product inventory', [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
                'new_record_id' => $newProduct->id
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
    private function createSerialRecords(array $serials, int $productId, int $assemblyId, ?int $warehouseId = null)
    {
        if (empty($serials)) return;

        foreach ($serials as $serial) {
            if (empty($serial)) continue;

            $data = [
                'serial_number' => $serial,
                'product_id' => $productId,
                'status' => 'active',
                'notes' => 'Assembly ID: ' . $assemblyId,
                'type' => 'product'
            ];

            // Only add warehouse_id if it's provided
            if ($warehouseId !== null) {
                $data['warehouse_id'] = $warehouseId;
            }

            Serial::create($data);
        }
    }

    /**
     * Update serial records for a particular assembly
     */
    private function updateSerialRecords(array $newSerials, int $productId, int $assemblyId, int $warehouseId)
    {
        // Delete existing serials for this assembly
        $this->deleteSerialRecords($assemblyId);

        // Create new serials
        $this->createSerialRecords($newSerials, $productId, $assemblyId, $warehouseId);
    }

    /**
     * Delete serial records for a particular assembly
     */
    private function deleteSerialRecords(int $assemblyId)
    {
        Serial::where('notes', 'like', '%Assembly ID: ' . $assemblyId . '%')
            ->where('type', 'product')
            ->delete();
    }

    /**
     * Check if a serial exists in a comma-separated string (case-insensitive)
     */
    private function serialExistsInString($needle, $haystack)
    {
        if (empty($haystack)) return false;

        $serials = array_map('trim', explode(',', $haystack));
        return in_array(strtolower($needle), array_map('strtolower', $serials));
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
            // Check in serials table (exact match, case-insensitive)
            // Only check serials with type='product' since we're validating product serials
            $existingSerial = Serial::whereRaw('LOWER(serial_number) = ?', [strtolower($serial)])
                ->where('product_id', $productId)
                ->where('type', 'product') // Add type check to only match product serials
                ->first();

            if ($existingSerial) {
                // If editing assembly, check if this serial belongs to current assembly
                if ($assemblyId && $existingSerial->notes) {
                    $expectedNote = 'Assembly ID: ' . $assemblyId;
                    if ($existingSerial->notes === $expectedNote) {
                        // This serial belongs to current assembly, so it's valid
                        return response()->json([
                            'exists' => false,
                            'message' => "Serial hợp lệ (thuộc assembly hiện tại)"
                        ]);
                    }
                }

                // Serial exists and doesn't belong to current assembly (or not editing)
                $errorMessage = "Serial đã tồn tại trong cơ sở dữ liệu";

                return response()->json([
                    'exists' => true,
                    'message' => $errorMessage,
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
     * Get material serials by warehouse and material
     */
    public function getMaterialSerials(Request $request)
    {
        // Handle both GET and POST requests
        if ($request->isMethod('post')) {
            $materialId = $request->input('material_id');
            $warehouseId = $request->input('warehouse_id');
            $productUnit = $request->input('product_unit');
            $assemblyId = $request->input('assembly_id');
        } else {
            $materialId = $request->query('material_id');
            $warehouseId = $request->query('warehouse_id');
            $productUnit = $request->query('product_unit');
            $assemblyId = $request->query('assembly_id');
        }

        // Validate required parameters
        if (!$materialId || !$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters material_id or warehouse_id'
            ], 400);
        }

        try {
            // Get existing serials for this material in this assembly if we're editing
            $existingSerials = [];
            if ($assemblyId) {
                $assemblyMaterial = AssemblyMaterial::where('assembly_id', $assemblyId)
                    ->where('material_id', $materialId)
                    ->first();

                if ($assemblyMaterial && $assemblyMaterial->serials) {
                    $existingSerials = array_map('trim', explode(',', $assemblyMaterial->serials));
                }
            }

            // Get all serials used by this material type in this assembly
            $usedSerialsByMaterial = [];
            if ($assemblyId) {
                $usedSerialsByMaterial = AssemblyMaterial::where('assembly_id', $assemblyId)
                    ->where('material_id', $materialId)
                    ->whereNotNull('serial')
                    ->pluck('serial')
                    ->flatMap(function ($serial) {
                        return array_map('trim', explode(',', $serial));
                    })
                    ->filter()
                    ->toArray();
            }

            // Lấy danh sách serial của material trong warehouse cụ thể
            // Serial phải có type = 'material' và warehouse_id = warehouse_id
            $query = Serial::where('product_id', $materialId)
                ->where('type', 'material')
                ->where('warehouse_id', $warehouseId)
                ->where('status', 'active');

            // Include serials that are:
            // 1. Not used (notes is null)
            // 2. Used by this assembly (notes contains this assembly ID)
            // 3. Currently assigned to this material in this assembly (in existingSerials)
            $query->where(function ($q) use ($assemblyId, $existingSerials) {
                $q->whereNull('notes')
                    ->orWhere('notes', 'like', '%Assembly ID: ' . $assemblyId . '%')
                    ->orWhereIn('serial_number', $existingSerials);
            });

            // Filter out serials already used in this assembly for other units
            if ($productUnit !== null && $assemblyId) {
                // Get serials already used for this material in this assembly but for different units
                $usedSerials = AssemblyMaterial::where('assembly_id', $assemblyId)
                    ->where('material_id', $materialId)
                    ->where('product_unit', '!=', $productUnit)
                    ->whereNotNull('serial')
                    ->pluck('serial')
                    ->flatMap(function ($serial) {
                        return array_map('trim', explode(',', $serial));
                    })
                    ->filter()
                    ->toArray();

                if (!empty($usedSerials)) {
                    $query->whereNotIn('serial_number', $usedSerials);
                }
            }

            $serials = $query->orderBy('serial_number')
                ->get(['id', 'serial_number']);

            // Add existing serials that might not be in the warehouse anymore
            foreach ($existingSerials as $existingSerial) {
                if (!$serials->contains('serial_number', $existingSerial)) {
                    $serials->push((object)[
                        'id' => null,
                        'serial_number' => $existingSerial
                    ]);
                }
            }

            // Check if each serial is already used by this material in this assembly
            foreach ($serials as $serial) {
                $serial->is_used_by_same_material = in_array($serial->serial_number, $usedSerialsByMaterial);
            }

            Log::info('Material serials request', [
                'material_id' => $materialId,
                'warehouse_id' => $warehouseId,
                'product_unit' => $productUnit,
                'assembly_id' => $assemblyId,
                'existing_serials' => $existingSerials,
                'count' => $serials->count(),
                'method' => $request->method()
            ]);

            return response()->json([
                'success' => true,
                'serials' => $serials
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting material serials: ' . $e->getMessage(), [
                'material_id' => $materialId,
                'warehouse_id' => $warehouseId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách serial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product serials for assembly
     */
    public function getProductSerials(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'assembly_id' => 'nullable|exists:assemblies,id',
            'product_unit' => 'nullable|integer',
            'exclude_serials' => 'nullable|array',
        ]);

        try {
            $productId = $request->product_id;
            $assemblyId = $request->assembly_id;
            $productUnit = $request->product_unit;
            $excludeSerials = $request->exclude_serials ?? [];

            // Query for available serials for this product
            $query = Serial::where('product_id', $productId)
                ->where('type', 'material')
                ->where('status', 'active')
                ->orderBy('serial_number');

            // If we're in an assembly context, include serials already assigned to this assembly
            if ($assemblyId) {
                $query->where(function ($q) use ($assemblyId) {
                    $q->whereNull('notes')
                        ->orWhere('notes', 'like', '%Assembly ID: ' . $assemblyId . '%');
                });
            } else {
                // Only include unused serials
                $query->whereNull('notes');
            }

            // Exclude specific serials if needed
            if (!empty($excludeSerials)) {
                $query->whereNotIn('serial_number', $excludeSerials);
            }

            // Fetch the serials
            $serials = $query->get(['id', 'serial_number']);

            return response()->json([
                'success' => true,
                'serials' => $serials
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách serial: ' . $e->getMessage()
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
     * Create a dispatch record for an assembly with project purpose
     */
    private function createDispatchForAssembly(Assembly $assembly)
    {
        Log::info('createDispatchForAssembly called', ['assembly_id' => $assembly->id]);

        // Load assembly products and project if not already loaded
        if (!$assembly->relationLoaded('products')) {
            $assembly->load('products.product');
        }
        if (!$assembly->relationLoaded('project')) {
            $assembly->load('project');
        }

        Log::info('Loaded relationships', [
            'products_count' => $assembly->products->count(),
            'project_name' => $assembly->project ? $assembly->project->project_name : 'null'
        ]);

        // Generate dispatch code
        $dispatchCode = Dispatch::generateDispatchCode();
        Log::info('Generated dispatch code: ' . $dispatchCode);

        // Get current user for created_by
        $currentUserId = Auth::user() ? Auth::user()->id : 1; // Fallback to user ID 1

        // Get warehouse ID from the first assembly material
        $sourceWarehouseId = $assembly->materials->first()->warehouse_id ?? null;
        if (!$sourceWarehouseId) {
            throw new \Exception('Không tìm thấy kho nguồn cho phiếu xuất kho');
        }

        Log::info('Using warehouse ID for dispatch', [
            'warehouse_id' => $sourceWarehouseId
        ]);

        // Create dispatch record
        $dispatch = Dispatch::create([
            'dispatch_code' => $dispatchCode,
            'dispatch_date' => $assembly->date,
            'dispatch_type' => 'project',
            'dispatch_detail' => 'contract', // "Xuất theo hợp đồng"
            'project_id' => $assembly->project_id,
            'project_receiver' => $assembly->project->project_name ?? 'Dự án',
            'warranty_period' => null, // Có thể thêm logic để set warranty period
            'company_representative_id' => $assembly->assigned_employee_id,
            'dispatch_note' => 'Tự động tạo từ phiếu lắp ráp ' . $assembly->code,
            'status' => 'pending',
            'created_by' => $currentUserId,
        ]);

        // Create dispatch items for each assembled product
        foreach ($assembly->products as $assemblyProduct) {
            // Parse serials if available
            $serialNumbers = [];
            if ($assemblyProduct->serials) {
                $serialNumbers = array_filter(explode(',', $assemblyProduct->serials));
            }

            DispatchItem::create([
                'dispatch_id' => $dispatch->id,
                'item_type' => 'product',
                'item_id' => $assemblyProduct->product_id,
                'quantity' => $assemblyProduct->quantity,
                'warehouse_id' => $sourceWarehouseId,
                'category' => 'contract',
                'serial_numbers' => !empty($serialNumbers) ? $serialNumbers : null,
                'notes' => 'Từ phiếu lắp ráp ' . $assembly->code,
            ]);

            // For assembly with purpose=project, we don't need to decrement stock
            // because the product was just assembled and will be dispatched directly
            // without being stored in the warehouse first
            Log::info("Dispatch: Skip decrementing stock for assembly with purpose=project", [
                'product_id' => $assemblyProduct->product_id,
                'warehouse_id' => $sourceWarehouseId,
                'quantity' => $assemblyProduct->quantity
            ]);
        }

        // Ghi nhật ký tạo mới phiếu xuất
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'create',
                'dispatches',
                'Tạo mới phiếu xuất: ' . $dispatch->dispatch_code,
                null,
                $dispatch->toArray()
            );
        }

        Log::info("Created dispatch {$dispatch->dispatch_code} for assembly {$assembly->code}");

        return $dispatch;
    }

    /**
     * Create a testing record for an assembly
     */
    private function createTestingRecordForAssembly(Assembly $assembly)
    {
        return DB::transaction(function () use ($assembly) {
            try {
                // Load relationships if not already loaded
                if (!$assembly->relationLoaded('products')) {
                    $assembly->load('products.product');
                }
                if (!$assembly->relationLoaded('materials')) {
                    $assembly->load('materials.material');
                }

                // Generate test code
                $baseTestCode = 'QA-' . Carbon::now()->format('ymd');

                // Tìm mã phiếu kiểm thử chưa được sử dụng
                $testCode = null;
                $attempt = 1;
                $maxAttempts = 999;

                // Lấy danh sách mã đã tồn tại trong ngày để tránh truy vấn nhiều lần
                $existingCodes = DB::table('testings')
                    ->where('test_code', 'like', $baseTestCode . '%')
                    ->pluck('test_code')
                    ->toArray();

                do {
                    $candidateCode = $baseTestCode . str_pad($attempt, 3, '0', STR_PAD_LEFT);

                    // Kiểm tra trong danh sách đã lấy
                    if (!in_array($candidateCode, $existingCodes)) {
                        // Double check với DB một lần nữa để đảm bảo
                        if (!DB::table('testings')->where('test_code', $candidateCode)->exists()) {
                            $testCode = $candidateCode;
                            break;
                        }
                    }

                    $attempt++;

                    // Nếu đã thử hết các số từ 001-999
                    if ($attempt > $maxAttempts) {
                        // Tạo mã với timestamp
                        do {
                            $testCode = $baseTestCode . substr(time(), -4) . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                            // Kiểm tra lại với DB
                            $exists = DB::table('testings')->where('test_code', $testCode)->exists();
                        } while ($exists);
                        break;
                    }
                } while (true);

                // Kiểm tra lần cuối và insert với lock
                $exists = DB::table('testings')
                    ->lockForUpdate()
                    ->where('test_code', $testCode)
                    ->exists();

                if ($exists) {
                    throw new \Exception('Mã phiếu kiểm thử đã tồn tại. Vui lòng thử lại.');
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

                // Ghi nhật ký tạo mới phiếu kiểm thử
                if (Auth::check()) {
                    UserLog::logActivity(
                        Auth::id(),
                        'create',
                        'testings',
                        'Tạo mới phiếu kiểm thử: ' . $testing->test_code,
                        null,
                        $testing->toArray()
                    );
                }

                return $testing;
            } catch (\Exception $e) {
                // Log lỗi và ném ngoại lệ
                Log::error('Lỗi khi tạo phiếu kiểm thử: ' . $e->getMessage());
                throw new \Exception('Không thể tạo phiếu kiểm thử. Vui lòng thử lại. Chi tiết: ' . $e->getMessage());
            }
        });
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

    public function createTestingFromAssembly(Assembly $assembly)
    {
        // Kiểm tra đã có phiếu kiểm thử chưa
        $existing = $assembly->testings()->first();
        if ($existing) {
            return redirect()->route('testing.show', $existing->id)
                ->with('info', 'Phiếu kiểm thử đã tồn tại.');
        }

        // Tạo mới kiểm thử (dùng lại logic đã có)
        $testing = $this->createTestingRecordForAssembly($assembly);

        return redirect()->route('testing.show', $testing->id)
            ->with('success', 'Đã tạo phiếu kiểm thử tự động từ phiếu lắp ráp.');
    }
}
