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
use App\Models\Testing;
use App\Models\TestingItem;
use App\Models\User;
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
use App\Helpers\ChangeLogHelper;
use App\Models\UserLog;

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
            'warehouse_id' => 'nullable|exists:warehouses,id', // Changed to nullable since each component has its own warehouse
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
            'components.*.warehouse_id' => 'required|exists:warehouses,id', // Each component must have its own warehouse
            'components.*.product_unit' => 'nullable|integer|min:0', // Product unit for multi-unit assemblies
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
            }

            // Create single assembly record for all products
            // Use warehouse_id from first component or null if no components
            $defaultWarehouseId = !empty($components) ? $components[0]['warehouse_id'] : null;

            $assembly = Assembly::create([
                'code' => $request->assembly_code,
                'date' => $request->assembly_date,
                'warehouse_id' => $request->warehouse_id ?? $defaultWarehouseId,
                'target_warehouse_id' => $request->target_warehouse_id,
                'assigned_employee_id' => $request->assigned_to,
                'tester_id' => $request->tester_id,
                'purpose' => $request->purpose,
                'project_id' => $request->project_id,
                'status' => 'pending',
                'notes' => $request->assembly_note,
                'product_serials' => null, // Legacy field for single product assemblies
                'created_by' => Auth::user()->id,
            ]);

            // Ghi nhật ký tạo mới phiếu lắp ráp
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'assemblies',
                    'Tạo mới phiếu lắp ráp: ' . $assembly->code,
                    null,
                    $assembly->toArray()
                );
            }

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

                // Use target_warehouse_id if available, otherwise use source warehouse_id
                $targetWarehouseId = $assembly->target_warehouse_id;
                if ($assembly->purpose === 'project' && !$targetWarehouseId) {
                    $targetWarehouseId = $assembly->warehouse_id;
                }

                // Only create serial records if we have a valid warehouse ID and serials
                if (!empty($filteredSerials) && $targetWarehouseId) {
                    $this->createSerialRecords($filteredSerials, $productData['id'], $assembly->id, $targetWarehouseId);
                }
            }

            // Create assembly materials and update stock levels    
            foreach ($components as $component) {
                // Process serials - either from multiple inputs or single input
                $serial = null;
                $serialIds = [];

                if (isset($component['serials']) && is_array($component['serials'])) {
                    // Filter out empty serials before joining
                    $filteredComponentSerials = array_filter($component['serials']);

                    // Only use unique values to prevent duplicates
                    $uniqueComponentSerials = array_unique($filteredComponentSerials);

                    // Join serials with comma
                    $serial = implode(',', $uniqueComponentSerials);

                    // Get serial_ids if available
                    if (isset($component['serial_ids']) && is_array($component['serial_ids'])) {
                        $serialIds = array_filter($component['serial_ids']);

                        // Mark serials as used
                        foreach ($serialIds as $serialId) {
                            if (!empty($serialId)) {
                                Serial::where('id', $serialId)
                                    ->update(['notes' => 'Assembly ID: ' . $assembly->id]);
                            }
                        }
                    }
                } elseif (isset($component['serial'])) {
                    $serial = $component['serial'];

                    // Get single serial_id if available
                    if (isset($component['serial_id']) && !empty($component['serial_id'])) {
                        $serialIds = [$component['serial_id']];

                        // Mark serial as used
                        Serial::where('id', $component['serial_id'])
                            ->update(['notes' => 'Assembly ID: ' . $assembly->id]);
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
                $productUnit = intval($component['product_unit'] ?? 0);

                // Debug: Log product_unit value
                Log::info('Processing component product_unit', [
                    'component_id' => $component['id'],
                    'raw_product_unit' => $component['product_unit'] ?? 'not_set',
                    'parsed_product_unit' => $productUnit,
                    'component_data' => $component
                ]);

                // Calculate serials for this specific unit
                $unitSerials = [];
                $unitSerialIds = [];

                if (isset($component['serials']) && is_array($component['serials'])) {
                    $filteredSerials = array_filter($component['serials']);

                    // For multi-unit assemblies, each unit should have its own serials
                    // The frontend should send separate components for each unit
                    // So we just use the serials as they are for this specific unit
                    $unitSerials = $filteredSerials;

                    // Get serial IDs for this unit
                    if (isset($component['serial_ids']) && is_array($component['serial_ids'])) {
                        $filteredSerialIds = array_filter($component['serial_ids']);
                        $unitSerialIds = $filteredSerialIds;
                    }
                } elseif (isset($component['serial'])) {
                    $unitSerials = [$component['serial']];
                    if (isset($component['serial_id']) && !empty($component['serial_id'])) {
                        $unitSerialIds = [$component['serial_id']];
                    }
                }

                $unitSerial = !empty($unitSerials) ? implode(',', $unitSerials) : null;

                // Create assembly material record for this specific unit
                $assemblyMaterialData = [
                    'assembly_id' => $assembly->id,
                    'material_id' => $component['id'],
                    'target_product_id' => $componentProductId, // Link component to specific product
                    'product_unit' => $productUnit, // Store product unit for multi-unit assemblies
                    'quantity' => $componentQty,
                    'serial' => $unitSerial,
                    'note' => $component['note'] ?? null,
                    'warehouse_id' => $component['warehouse_id'], // Store warehouse_id for each component
                ];

                // Add serial_id if provided (for single serial or first serial of multiple)
                if (!empty($unitSerialIds)) {
                    $assemblyMaterialData['serial_id'] = $unitSerialIds[0]; // Use first serial_id for this unit
                }

                Log::info('Creating assembly material for unit', [
                    'material_id' => $component['id'],
                    'product_unit' => $productUnit,
                    'quantity' => $componentQty,
                    'unit_serials' => $unitSerials,
                    'unit_serial' => $unitSerial,
                    'original_serials' => $component['serials'] ?? 'not_set',
                    'original_serial_ids' => $component['serial_ids'] ?? 'not_set',
                    'product_qty' => $productQty
                ]);

                AssemblyMaterial::create($assemblyMaterialData);
            }

            DB::commit();

            // Tạo thông báo thành công - chuyển sang workflow duyệt
            $successMessage = 'Phiếu lắp ráp đã được tạo thành công! Chờ duyệt để tạo phiếu kiểm thử và xuất kho.';

            // Tạo thông báo cho admin/người có quyền duyệt
            $adminUsers = User::whereHas('roleGroup.permissions', function ($query) {
                $query->where('name', 'assembly.approve');
            })->orWhere('role', 'admin')->get();

            foreach ($adminUsers as $adminUser) {
                Notification::createNotification(
                    'Phiếu lắp ráp mới cần duyệt',
                    'Phiếu lắp ráp #' . $assembly->code . ' đã được tạo và cần duyệt.',
                    'info',
                    $adminUser->id,
                    'assembly',
                    $assembly->id,
                    route('assemblies.show', $assembly->id)
                );
            }

            // Tạo thông báo cho người được phân công lắp ráp
            Notification::createNotification(
                'Phiếu lắp ráp mới',
                'Bạn đã được phân công lắp ráp phiếu #' . $assembly->code,
                'info',
                $assembly->assigned_employee_id,
                'assembly',
                $assembly->id,
                route('assemblies.show', $assembly->id)
            );

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

        // Lấy các phiếu xuất kho liên quan đến assembly này
        $dispatches = \App\Models\Dispatch::where('dispatch_note', 'like', '%' . $assembly->code . '%')
            ->orWhere('project_id', $assembly->project_id)
            ->orderByDesc('created_at')
            ->get();

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

        return view('assemble.show', compact('assembly', 'dispatches'));
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

        // Load all material serials for each material
        $materialSerials = [];
        foreach ($assembly->materials as $material) {
            $query = Serial::where('product_id', $material->material_id)
                ->where('type', 'material')
                ->where('warehouse_id', $assembly->warehouse_id)
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
        // Validation rules based on assembly status
        $validationRules = [
            'assembly_date' => 'required|date',
            'assembly_note' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.serials' => 'nullable|array',
        ];

        if ($assembly->status === 'in_progress') {
            // For in_progress assemblies, allow quantity and serial updates
            $validationRules['components'] = 'nullable|array';
            $validationRules['components.*.quantity'] = 'required|integer|min:1';
            $validationRules['components.*.serials'] = 'nullable|array';
            $validationRules['components.*.note'] = 'nullable|string';
        } else {
            // For other statuses, allow serial and note updates
            $validationRules['components'] = 'nullable|array';
            $validationRules['components.*.serial'] = 'nullable|string';
            $validationRules['components.*.note'] = 'nullable|string';
            $validationRules['components.*.product_unit'] = 'nullable|integer|min:0';
        }

        $request->validate($validationRules);

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
                foreach ($request->components as $componentIndex => $component) {
                    // Check for duplicate serials within the same component
                    if (isset($component['serials']) && is_array($component['serials'])) {
                        $filteredSerials = array_filter($component['serials']);
                        $uniqueSerials = array_unique($filteredSerials);

                        if (count($filteredSerials) !== count($uniqueSerials)) {
                            throw new \Exception("Phát hiện trùng lặp serial linh kiện trong phiếu lắp ráp!");
                        }
                    }

                    // Check for existing serials in other assemblies
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

            // 3. Update assembly basic info (only date, notes, and warehouse_id)
            $assembly->update([
                'date' => $request->assembly_date,
                'notes' => $request->assembly_note,
            ]);

            // 4. Update product serials (only if status is pending)
            if ($assembly->status === 'pending') {
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

                            // Ensure we have a valid warehouse ID
                            if (!$targetWarehouseId) {
                                $targetWarehouseId = $assembly->warehouse_id;
                            }

                            // Only create serial records if we have a valid warehouse ID and serials
                            if ($targetWarehouseId && !empty($filteredSerials)) {
                                $this->createSerialRecords($filteredSerials, $productData['id'], $assembly->id, $targetWarehouseId);
                            }
                        }
                    }
                }
            }

            // 5. Update component materials
            if ($request->components) {
                foreach ($request->components as $componentIndex => $component) {
                    // Find the specific assembly material by material_id
                    $assemblyMaterial = AssemblyMaterial::where('assembly_id', $assembly->id)
                        ->where('material_id', $component['material_id'])
                        ->first();

                    // Debug logging
                    Log::info('Processing component update', [
                        'componentIndex' => $componentIndex,
                        'componentMaterialId' => $component['material_id'] ?? 'not_set',
                        'assemblyId' => $assembly->id,
                        'assemblyMaterialFound' => $assemblyMaterial ? 'yes' : 'no',
                        'componentData' => $component
                    ]);

                    if ($assemblyMaterial) {
                        if ($assembly->status === 'in_progress') {
                            // For in_progress assemblies, allow quantity updates and serial updates
                            $newQuantity = (int)($component['quantity'] ?? $assemblyMaterial->quantity);
                            $oldQuantity = $assemblyMaterial->quantity;

                            if ($newQuantity < $oldQuantity) {
                                throw new \Exception("Không thể giảm số lượng linh kiện từ {$oldQuantity} xuống {$newQuantity}. Chỉ có thể tăng số lượng.");
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
                                'quantity' => $newQuantity,
                                'serial' => $serial,
                                'note' => $component['note'] ?? $assemblyMaterial->note,
                                'serial_id' => null, // Reset first
                                'product_unit' => $assemblyMaterial->product_unit,
                            ];

                            // Set new serial_id if provided
                            if (isset($component['serial_id']) && !empty($component['serial_id'])) {
                                $updateData['serial_id'] = $component['serial_id'];

                                // Update new serial status
                                Serial::where('id', $component['serial_id'])
                                    ->update(['notes' => 'Assembly ID: ' . $assembly->id]);
                            }
                        } else {
                            // For other statuses, allow serial and note updates
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
                        }

                        $assemblyMaterial->update($updateData);
                    }
                }
            }

            DB::commit();

            // Cập nhật các phiếu xuất kho và kiểm thử liên quan nếu assembly ở trạng thái in_progress
            if ($assembly->status === 'in_progress') {
                $this->updateRelatedRecords($assembly);
            }

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
            // Lưu dữ liệu cũ trước khi xóa
            $assemblyData = $assembly->toArray();
            $assemblyCode = $assembly->code;

            // Load materials if not already loaded
            if (!$assembly->relationLoaded('materials')) {
                $assembly->load('materials');
            }

            // Load assembly products if not already loaded
            if (!$assembly->relationLoaded('products')) {
                $assembly->load('products.product');
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

            // 6. Delete related dispatch if exists and restore warehouse stock
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

            // 7. Delete the assembly
            $assembly->delete();

            DB::commit();

            // Ghi nhật ký xóa phiếu lắp ráp
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'assemblies',
                    'Xóa phiếu lắp ráp: ' . $assemblyCode,
                    $assemblyData,
                    null
                );
            }

            return redirect()->route('assemblies.index')->with('success', 'Phiếu lắp ráp đã được xóa thành công và tồn kho đã được cập nhật');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting assembly: ' . $e->getMessage());
            return redirect()->route('assemblies.index')->with('error', 'Có lỗi xảy ra khi xóa: ' . $e->getMessage());
        }
    }

    /**
     * Create serial records for each product serial
     */
    private function createSerialRecords(array $serials, int $productId, int $assemblyId, int $warehouseId)
    {
        if (empty($serials)) return;

        foreach ($serials as $serial) {
            if (empty($serial)) continue;

            Serial::create([
                'serial_number' => $serial,
                'product_id' => $productId,
                'status' => 'active',
                'notes' => 'Assembly ID: ' . $assemblyId,
                'type' => 'product',
                'warehouse_id' => $warehouseId
            ]);
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

            // Loại trừ serial đang được sử dụng trong các phiếu lắp ráp khác ở trạng thái in_progress
            $usedSerialsInOtherAssemblies = AssemblyMaterial::where('material_id', $materialId)
                ->where(function ($query) {
                    $query->whereNotNull('serial')
                        ->orWhereNotNull('serial_id');
                })
                ->whereHas('assembly', function ($query) use ($assemblyId) {
                    $query->where('status', 'in_progress')
                        ->where('id', '!=', $assemblyId);
                })
                ->get()
                ->flatMap(function ($assemblyMaterial) {
                    $serials = [];
                    if ($assemblyMaterial->serial) {
                        $serials = array_merge($serials, array_map('trim', explode(',', $assemblyMaterial->serial)));
                    }
                    if ($assemblyMaterial->serial_id) {
                        $serial = Serial::find($assemblyMaterial->serial_id);
                        if ($serial) {
                            $serials[] = $serial->serial_number;
                        }
                    }
                    return $serials;
                })
                ->filter()
                ->unique()
                ->toArray();

            if (!empty($usedSerialsInOtherAssemblies)) {
                $query->whereNotIn('serial_number', $usedSerialsInOtherAssemblies);
            }

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

        // Sử dụng warehouse_id nếu target_warehouse_id là null
        $sourceWarehouseId = $assembly->target_warehouse_id ?? $assembly->warehouse_id;
        Log::info('Using warehouse ID for dispatch', [
            'target_warehouse_id' => $assembly->target_warehouse_id,
            'warehouse_id' => $assembly->warehouse_id,
            'using_warehouse_id' => $sourceWarehouseId
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
            'dispatch_note' => 'Sinh ra từ phiếu lắp ráp ' . $assembly->code,
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
     * Tạo phiếu xuất kho cho vật tư khi lắp ráp lưu kho
     */
    private function createMaterialExportSlipForAssembly(Assembly $assembly)
    {
        Log::info('Starting createMaterialExportSlipForAssembly', [
            'assembly_id' => $assembly->id,
            'assembly_code' => $assembly->code,
            'purpose' => $assembly->purpose,
            'warehouse_id' => $assembly->warehouse_id,
            'target_warehouse_id' => $assembly->target_warehouse_id
        ]);

        // Tạo mã phiếu xuất kho tự động
        $exportCode = 'XK' . date('ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        Log::info('Generated export code', ['export_code' => $exportCode]);

        // Tạo phiếu xuất kho
        $dispatch = \App\Models\Dispatch::create([
            'dispatch_code' => $exportCode,
            'dispatch_date' => now(),
            'dispatch_type' => 'project', // Sử dụng 'project' thay vì 'assembly_material' vì enum chỉ chấp nhận 3 giá trị
            'dispatch_detail' => 'all', // Sử dụng 'all' thay vì 'Vật tư lắp ráp' vì enum chỉ chấp nhận 3 giá trị
            'project_id' => null,
            'project_receiver' => 'Lắp ráp lưu kho: ' . $assembly->code,
            'warranty_period' => null,
            'company_representative_id' => Auth::id(),
            'dispatch_note' => 'Sinh từ phiếu lắp ráp: ' . $assembly->code,
            'status' => 'approved', // Tự động duyệt
            'created_by' => Auth::id(),
        ]);

        Log::info('Created dispatch record', [
            'dispatch_id' => $dispatch->id,
            'dispatch_code' => $dispatch->dispatch_code
        ]);

        // Lấy danh sách vật tư đã sử dụng trong lắp ráp
        $assemblyMaterials = \App\Models\AssemblyMaterial::where('assembly_id', $assembly->id)->get();

        Log::info('Found assembly materials', [
            'count' => $assemblyMaterials->count(),
            'materials' => $assemblyMaterials->map(function ($am) {
                return [
                    'material_id' => $am->material_id,
                    'quantity' => $am->quantity,
                    'serial' => $am->serial
                ];
            })->toArray()
        ]);

        foreach ($assemblyMaterials as $am) {
            if ($am->material) {
                Log::info('Processing assembly material', [
                    'material_id' => $am->material_id,
                    'material_code' => $am->material->code,
                    'material_name' => $am->material->name,
                    'quantity' => $am->quantity,
                    'serial' => $am->serial
                ]);

                // Xử lý serial_numbers - chuyển thành array
                $serialNumbers = null;
                if ($am->serial) {
                    $serialArray = explode(',', $am->serial);
                    $serialNumbers = $serialArray; // Không cần json_encode vì model đã cast thành array
                    Log::info('Processed serial numbers', [
                        'original_serial' => $am->serial,
                        'serial_array' => $serialArray
                    ]);
                }

                // Tạo item trong phiếu xuất kho
                $dispatchItem = \App\Models\DispatchItem::create([
                    'dispatch_id' => $dispatch->id,
                    'item_type' => 'material',
                    'item_id' => $am->material_id,
                    'quantity' => $am->quantity,
                    'warehouse_id' => $assembly->warehouse_id, // Thêm warehouse_id từ assembly
                    'category' => 'general',
                    'serial_numbers' => $serialNumbers,
                    'notes' => 'Vật tư lắp ráp từ phiếu lắp ráp',
                ]);

                Log::info('Created dispatch item', [
                    'dispatch_item_id' => $dispatchItem->id,
                    'dispatch_id' => $dispatchItem->dispatch_id,
                    'item_type' => $dispatchItem->item_type,
                    'item_id' => $dispatchItem->item_id,
                    'quantity' => $dispatchItem->quantity,
                    'warehouse_id' => $dispatchItem->warehouse_id,
                    'serial_numbers' => $dispatchItem->serial_numbers
                ]);

                // Lưu nhật ký thay đổi cho xuất kho vật tư lắp ráp
                \App\Helpers\ChangeLogHelper::xuatKho(
                    $am->material->code,
                    $am->material->name,
                    $am->quantity,
                    $exportCode,
                    'Sinh từ Phiếu lắp ráp với mã ' . $assembly->code,
                    [
                        'assembly_id' => $assembly->id,
                        'material_id' => $am->material_id,
                        'warehouse_id' => $assembly->warehouse_id,
                        'target_warehouse_id' => $assembly->target_warehouse_id,
                        'quantity' => $am->quantity,
                        'serial' => $am->serial,
                        'assigned_employee_id' => $assembly->assigned_employee_id,
                        'created_by' => Auth::id(),
                        'created_at' => now()->toDateTimeString(),
                        'action_type' => 'material_assembly_warehouse_export'
                    ],
                    'Vật tư lắp ráp lưu kho'
                );

                Log::info('Created change log for material export', [
                    'material_code' => $am->material->code,
                    'export_code' => $exportCode
                ]);
            } else {
                Log::warning('Assembly material not found', [
                    'assembly_material_id' => $am->id,
                    'material_id' => $am->material_id
                ]);
            }
        }

        Log::info('Completed createMaterialExportSlipForAssembly', [
            'assembly_id' => $assembly->id,
            'dispatch_id' => $dispatch->id,
            'dispatch_code' => $dispatch->dispatch_code
        ]);

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

    /**
     * Check if a formula already exists in products
     */
    public function checkFormula(Request $request)
    {
        try {
            $formula = $request->input('formula', []);

            Log::info('Check formula request:', ['formula' => $formula]);

            if (empty($formula)) {
                return response()->json([
                    'exists' => false,
                    'message' => 'No formula provided'
                ]);
            }

            // Get all products with their materials
            $products = Product::with('materials')->get();

            Log::info('Checking against products:', ['total_products' => $products->count()]);

            foreach ($products as $product) {
                $productMaterials = $product->materials;

                Log::info('Checking product:', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_materials_count' => $productMaterials->count(),
                    'formula_count' => count($formula)
                ]);

                // Check if the number of materials matches
                if (count($productMaterials) !== count($formula)) {
                    Log::info('Material count mismatch, skipping product');
                    continue;
                }

                // Check if all materials and quantities match
                $matches = true;
                foreach ($formula as $formulaItem) {
                    $materialId = $formulaItem['material_id'];
                    $quantity = $formulaItem['quantity'];

                    $productMaterial = $productMaterials->where('material_id', $materialId)->first();

                    if (!$productMaterial || $productMaterial->quantity != $quantity) {
                        $matches = false;
                        Log::info('Material/quantity mismatch:', [
                            'material_id' => $materialId,
                            'quantity' => $quantity,
                            'product_material' => $productMaterial ? $productMaterial->toArray() : null
                        ]);
                        break;
                    }
                }

                if ($matches) {
                    Log::info('Formula match found:', ['product_id' => $product->id, 'product_name' => $product->name]);
                    return response()->json([
                        'exists' => true,
                        'product' => [
                            'id' => $product->id,
                            'name' => $product->name,
                            'code' => $product->code
                        ],
                        'message' => 'Formula already exists in product: ' . $product->name
                    ]);
                }
            }

            Log::info('No matching formula found');
            return response()->json([
                'exists' => false,
                'message' => 'Formula not found in existing products'
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking formula: ' . $e->getMessage());
            return response()->json([
                'exists' => false,
                'error' => 'Error checking formula: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve assembly and create testing/dispatch records
     */
    public function approve(Assembly $assembly)
    {
        try {
            DB::beginTransaction();

            // Check if assembly is already approved
            if ($assembly->status === 'approved') {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'Phiếu lắp ráp đã được duyệt trước đó.'], 400);
                }
                return back()->withErrors(['error' => 'Phiếu lắp ráp đã được duyệt trước đó.'])->withInput();
            }

            // Kiểm tra tính khả dụng của vật tư trước khi duyệt
            $materialAvailabilityCheck = $this->checkMaterialAvailability($assembly);
            if (!$materialAvailabilityCheck['available']) {
                DB::rollback();
                if (request()->expectsJson()) {
                    return response()->json(['error' => $materialAvailabilityCheck['message']], 400);
                }
                return back()
                    ->withErrors(['error' => $materialAvailabilityCheck['message']])
                    ->withInput()
                    ->with('error', $materialAvailabilityCheck['message']);
            }

            // Create testing record
            $testing = $this->createTestingRecordForAssembly($assembly);

            // Create dispatch record if purpose is project
            $dispatch = null;
            if ($assembly->purpose === 'project' && $assembly->project_id) {
                // Always reload assembly from DB to ensure project_id is up-to-date
                $assemblyFresh = Assembly::with('project')->find($assembly->id);
                $dispatch = $this->createDispatchForAssembly($assemblyFresh);
            }

            // Create material export slip if purpose is storage
            if ($assembly->purpose === 'storage') {
                $this->createMaterialExportSlipForAssembly($assembly);
            }

            // Update assembly status to in_progress (đang thực hiện)
            $assembly->update(['status' => 'in_progress']);

            // Tạo thông báo cho người được phân công lắp ráp
            Notification::createNotification(
                'Phiếu lắp ráp đã được duyệt',
                'Phiếu lắp ráp #' . $assembly->code . ' đã được duyệt và tạo phiếu kiểm thử/xuất kho.',
                'success',
                $assembly->assigned_employee_id,
                'assembly',
                $assembly->id,
                route('assemblies.show', $assembly->id)
            );

            // Gửi thông báo cho người phụ trách kho xuất nếu có phiếu xuất kho
            if ($dispatch) {
                $warehouse = null;
                if ($dispatch->project_id && $assembly->warehouse_id) {
                    $warehouse = Warehouse::find($assembly->warehouse_id);
                } elseif ($dispatch->project_id && $assembly->target_warehouse_id) {
                    $warehouse = Warehouse::find($assembly->target_warehouse_id);
                }
                if ($warehouse && $warehouse->manager) {
                    Notification::createNotification(
                        'Có phiếu xuất kho được tạo',
                        'Phiếu xuất kho #' . $dispatch->dispatch_code . ' vừa được tạo từ phiếu lắp ráp #' . $assembly->code . '.',
                        'info',
                        $warehouse->manager,
                        'dispatch',
                        $dispatch->id,
                        route('inventory.dispatch.show', $dispatch->id)
                    );
                }
            }

            // Gửi thông báo cho người tiếp nhận kiểm thử nếu có phiếu kiểm thử
            if ($testing && $testing->receiver_id) {
                Notification::createNotification(
                    'Có phiếu kiểm thử được tạo',
                    'Phiếu kiểm thử #' . $testing->test_code . ' vừa được tạo từ phiếu lắp ráp #' . $assembly->code . '.',
                    'info',
                    $testing->receiver_id,
                    'testing',
                    $testing->id,
                    route('testing.show', $testing->id)
                );
            }

            DB::commit();

            // Create success message
            $successMessage = 'Phiếu lắp ráp đã được duyệt thành công!';

            if ($testing) {
                $testingUrl = route('testing.show', $testing->id);
                $successMessage .= ' <a href="' . $testingUrl . '" class="text-blue-600 hover:underline">Phiếu kiểm thử</a> đã được tạo.';
            }

            if ($dispatch) {
                $dispatchUrl = route('inventory.dispatch.show', $dispatch->id);
                $successMessage .= ' <a href="' . $dispatchUrl . '" class="text-blue-600 hover:underline">Phiếu xuất kho</a> đã được tạo.';
            }

            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'assembly_id' => $assembly->id,
                    'status' => 'approved'
                ]);
            }

            // Redirect về trang gọi (danh sách hoặc chi tiết)
            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = 'Có lỗi xảy ra khi duyệt: ' . $e->getMessage();

            if (request()->expectsJson()) {
                return response()->json(['error' => $errorMessage], 500);
            }

            return back()->withErrors(['error' => $errorMessage]);
        }
    }

    /**
     * Cancel the specified assembly.
     */
    public function cancel(Assembly $assembly)
    {
        if (!in_array($assembly->status, ['pending', 'in_progress'])) {
            return back()->withErrors(['error' => 'Chỉ có thể huỷ phiếu ở trạng thái Chờ xử lý hoặc Đang thực hiện.']);
        }
        $assembly->status = 'cancelled';
        $assembly->save();
        // Ghi log huỷ phiếu
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'cancel',
                'assemblies',
                'Huỷ phiếu lắp ráp: ' . $assembly->code,
                null,
                $assembly->toArray()
            );
        }
        // Redirect về trang gọi (danh sách hoặc chi tiết)
        return back()->with('success', 'Đã huỷ phiếu lắp ráp thành công!');
    }

    /**
     * Kiểm tra tính khả dụng của vật tư cho phiếu lắp ráp
     */
    private function checkMaterialAvailability(Assembly $assembly)
    {
        $errors = [];

        // Lấy danh sách vật tư cần thiết cho phiếu lắp ráp
        $assemblyMaterials = $assembly->materials()->with(['material', 'serial'])->get();

        foreach ($assemblyMaterials as $assemblyMaterial) {
            $material = $assemblyMaterial->material;
            $requiredQuantity = $assemblyMaterial->quantity;
            $warehouseId = $assembly->warehouse_id;

            // Lấy tồn kho của vật tư trong kho
            $warehouseMaterials = WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $material->id)
                ->where('item_type', 'material')
                ->get();

            $totalAvailable = 0;
            $serializedAvailable = 0;
            $nonSerializedAvailable = 0;

            foreach ($warehouseMaterials as $wm) {
                $totalAvailable += $wm->quantity;

                if ($wm->serial_number) {
                    // Vật tư có serial
                    $serializedAvailable += $wm->quantity;
                } else {
                    // Vật tư không có serial
                    $nonSerializedAvailable += $wm->quantity;
                }
            }

            // Kiểm tra nếu vật tư có yêu cầu serial cụ thể
            if ($assemblyMaterial->serial_id || $assemblyMaterial->serial) {
                // Xử lý trường hợp serial được lưu dưới dạng chuỗi (ví dụ: "123,1234")
                if ($assemblyMaterial->serial && !$assemblyMaterial->serial_id) {
                    $serialNumbers = explode(',', $assemblyMaterial->serial);
                    $serialNumbers = array_map('trim', $serialNumbers);

                    // Kiểm tra từng serial có đang được sử dụng trong phiếu lắp ráp khác không
                    foreach ($serialNumbers as $serialNumber) {
                        $usedInOtherAssembly = AssemblyMaterial::where('serial', 'LIKE', '%' . $serialNumber . '%')
                            ->where('assembly_id', '!=', $assembly->id)
                            ->whereHas('assembly', function ($query) {
                                $query->whereIn('status', ['in_progress']);
                            })
                            ->exists();

                        if ($usedInOtherAssembly) {
                            $errors[] = "Serial '{$serialNumber}' của vật tư '{$material->name}' đang được sử dụng trong phiếu lắp ráp khác";
                            continue 2; // Continue to next assembly material
                        }

                        // Kiểm tra serial có trong kho không
                        $serialInWarehouse = WarehouseMaterial::where('warehouse_id', $warehouseId)
                            ->where('material_id', $material->id)
                            ->where('item_type', 'material')
                            ->where(function ($query) use ($serialNumber) {
                                $query->where('serial_number', $serialNumber)
                                    ->orWhere('serial_number', 'LIKE', '%"' . $serialNumber . '"%')
                                    ->orWhere('serial_number', 'LIKE', '%' . $serialNumber . '%');
                            })
                            ->exists();

                        if (!$serialInWarehouse) {
                            $errors[] = "Serial '{$serialNumber}' của vật tư '{$material->name}' không có trong kho";
                            continue 2; // Continue to next assembly material
                        }
                    }
                } else if ($assemblyMaterial->serial_id) {
                    // Trường hợp có serial_id (hiếm gặp với cấu trúc hiện tại)
                    $serial = Serial::find($assemblyMaterial->serial_id);
                    if (!$serial) {
                        $errors[] = "Serial ID '{$assemblyMaterial->serial_id}' không tồn tại cho vật tư '{$material->name}'";
                        continue;
                    }

                    // Kiểm tra serial có đang được sử dụng trong phiếu lắp ráp khác không
                    $usedInOtherAssembly = AssemblyMaterial::where('serial_id', $assemblyMaterial->serial_id)
                        ->where('assembly_id', '!=', $assembly->id)
                        ->whereHas('assembly', function ($query) {
                            $query->whereIn('status', ['in_progress']);
                        })
                        ->exists();

                    if ($usedInOtherAssembly) {
                        $errors[] = "Serial '{$assemblyMaterial->serial}' của vật tư '{$material->name}' đang được sử dụng trong phiếu lắp ráp khác";
                        continue;
                    }

                    // Kiểm tra serial có trong kho không
                    $serialInWarehouse = WarehouseMaterial::where('warehouse_id', $warehouseId)
                        ->where('material_id', $material->id)
                        ->where('item_type', 'material')
                        ->where(function ($query) use ($assemblyMaterial) {
                            $query->where('serial_number', $assemblyMaterial->serial)
                                ->orWhere('serial_number', 'LIKE', '%"' . $assemblyMaterial->serial . '"%')
                                ->orWhere('serial_number', 'LIKE', '%' . $assemblyMaterial->serial . '%');
                        })
                        ->exists();

                    if (!$serialInWarehouse) {
                        $errors[] = "Serial '{$assemblyMaterial->serial}' của vật tư '{$material->name}' không có trong kho";
                        continue;
                    }
                }
            } else {
                // Vật tư không có serial cụ thể
                // Kiểm tra tổng số lượng có đủ không
                if ($totalAvailable < $requiredQuantity) {
                    $errors[] = "Vật tư '{$material->name}' không đủ số lượng. Cần: {$requiredQuantity}, Có: {$totalAvailable}";
                    continue;
                }

                // Kiểm tra số lượng vật tư không có serial có đủ không
                if ($nonSerializedAvailable < $requiredQuantity) {
                    $errors[] = "Số lượng vật tư '{$material->name}' không có serial không đủ để thực hiện. Cần: {$requiredQuantity}, Có: {$nonSerializedAvailable}";
                    continue;
                }
            }
        }

        if (!empty($errors)) {
            return [
                'available' => false,
                'message' => implode('. ', $errors)
            ];
        }

        return [
            'available' => true,
            'message' => 'Tất cả vật tư đều khả dụng'
        ];
    }

    /**
     * Cập nhật các phiếu xuất kho và kiểm thử liên quan khi phiếu lắp ráp được cập nhật
     */
    private function updateRelatedRecords(Assembly $assembly)
    {
        try {
            // Lấy tất cả các material_id của assembly này
            $materialIds = $assembly->materials->pluck('material_id')->toArray();
            // Lấy tất cả dispatch_items liên quan đến các material này
            $dispatchItems = \App\Models\DispatchItem::whereIn('item_id', $materialIds)
                ->where('item_type', 'material')
                ->get();
            // Lấy tất cả dispatch_id liên quan
            $dispatchIds = $dispatchItems->pluck('dispatch_id')->unique()->toArray();
            // Lấy các dispatch liên quan
            $dispatches = \App\Models\Dispatch::whereIn('id', $dispatchIds)->get();
            foreach ($dispatches as $dispatch) {
                // Cập nhật số lượng vật tư và serial trong phiếu xuất kho dựa trên số lượng mới trong phiếu lắp ráp
                foreach ($assembly->materials as $assemblyMaterial) {
                    $dispatchItem = \App\Models\DispatchItem::where('dispatch_id', $dispatch->id)
                        ->where('item_id', $assemblyMaterial->material_id)
                        ->where('item_type', 'material')
                        ->first();
                    if ($dispatchItem) {
                        // Xử lý serial_numbers: nếu là chuỗi thì tách thành mảng, nếu null thì để mảng rỗng
                        $serialNumbers = [];
                        if (!empty($assemblyMaterial->serial)) {
                            if (is_array($assemblyMaterial->serial)) {
                                $serialNumbers = $assemblyMaterial->serial;
                            } else {
                                $serialNumbers = array_map('trim', explode(',', $assemblyMaterial->serial));
                            }
                        }
                        $dispatchItem->update([
                            'quantity' => $assemblyMaterial->quantity,
                            'serial_numbers' => $serialNumbers
                        ]);
                        // Ghi changelog nếu có helper
                        if (class_exists('App\\Helpers\\ChangeLogHelper')) {
                            // Tìm dòng changelog cũ theo mã phiếu xuất kho
                            $materialCode = $assemblyMaterial->material->code ?? '';
                            $dispatchCode = $dispatch->dispatch_code ?? '';
                            $description = 'Cập nhật số lượng và serial từ phiếu lắp ráp';
                            $detailedInfo = [
                                'serial_numbers' => $serialNumbers,
                                'assembly_code' => $assembly->code
                            ];
                            // Thử cập nhật theo mã phiếu
                            $updated = \App\Helpers\ChangeLogHelper::capNhatTheoMaPhieu($dispatchCode, [
                                'quantity' => $assemblyMaterial->quantity,
                                'description' => $description,
                                'detailed_info' => $detailedInfo,
                                'item_code' => $materialCode,
                                'item_name' => $assemblyMaterial->material->name ?? '',
                            ]);
                            // Nếu không có dòng cũ, tạo mới
                            if (!$updated) {
                                \App\Helpers\ChangeLogHelper::xuatKho(
                                    $materialCode,
                                    $assemblyMaterial->material->name ?? '',
                                    $assemblyMaterial->quantity,
                                    $dispatchCode,
                                    $description,
                                    $detailedInfo
                                );
                            }
                        }
                    }
                }
            }

            // Cập nhật phiếu kiểm thử liên quan (giữ nguyên)
            $testings = Testing::where('assembly_id', $assembly->id)->get();
            foreach ($testings as $testing) {
                foreach ($assembly->products as $assemblyProduct) {
                    $testingItem = TestingItem::where('testing_id', $testing->id)
                        ->where('product_id', $assemblyProduct->product_id)
                        ->first();
                    if ($testingItem) {
                        $testingItem->update([
                            'quantity' => $assemblyProduct->quantity
                        ]);
                    }
                }
            }

            Log::info('Updated related records for assembly', [
                'assembly_id' => $assembly->id,
                'assembly_code' => $assembly->code,
                'dispatches_updated' => $dispatches->count(),
                'testings_updated' => $testings->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating related records for assembly', [
                'assembly_id' => $assembly->id,
                'error' => $e->getMessage()
            ]);
            // Không throw exception để không ảnh hưởng đến việc cập nhật phiếu lắp ráp chính
        }
    }
}
