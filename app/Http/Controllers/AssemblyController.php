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
use App\Helpers\DateHelper;
use App\Models\UserLog;

class AssemblyController extends Controller
{
    /**
     * Normalize incoming serial_id from various formats to a single integer ID or null.
     */
    private function normalizeSerialId($rawSerialId)
    {
        // If it's already an integer-like scalar
        if (is_int($rawSerialId)) {
            return $rawSerialId;
        }
        if (is_string($rawSerialId)) {
            $trimmed = trim($rawSerialId);
            if ($trimmed === '') {
                return null;
            }
            // If string contains commas (e.g., "34,53"), take the first valid integer
            if (strpos($trimmed, ',') !== false) {
                $parts = array_map('trim', explode(',', $trimmed));
                foreach ($parts as $part) {
                    if ($part !== '' && ctype_digit($part)) {
                        return (int)$part;
                    }
                }
                return null;
            }
            // Plain string single number
            if (ctype_digit($trimmed)) {
                return (int)$trimmed;
            }
            return null;
        }
        if (is_array($rawSerialId)) {
            // Take the first non-empty numeric element
            foreach ($rawSerialId as $item) {
                if (is_int($item)) {
                    return $item;
                }
                if (is_string($item)) {
                    $ti = trim($item);
                    if ($ti !== '' && ctype_digit($ti)) {
                        return (int)$ti;
                    }
                }
            }
            return null;
        }
        return null;
    }

    /**
     * Normalize serials input (array or comma-separated string) into an array of strings.
     */
    private function normalizeSerials($rawSerials): array
    {
        if (is_array($rawSerials)) {
            return array_values(array_filter(array_map(function ($s) {
                return is_string($s) ? trim($s) : (string)$s;
            }, $rawSerials), function ($s) {
                return $s !== '';
            }));
        }
        if (is_string($rawSerials)) {
            $parts = array_map('trim', explode(',', $rawSerials));
            return array_values(array_filter($parts, function ($s) {
                return $s !== '';
            }));
        }
        return [];
    }

    /**
     * Normalize serial_ids input (array or comma-separated string) into an array of integer IDs.
     */
    private function normalizeSerialIds($rawSerialIds): array
    {
        $result = [];
        if (is_array($rawSerialIds)) {
            foreach ($rawSerialIds as $id) {
                $nid = $this->normalizeSerialId($id);
                if ($nid) {
                    $result[] = $nid;
                }
            }
            return $result;
        }
        if (is_string($rawSerialIds)) {
            $parts = array_map('trim', explode(',', $rawSerialIds));
            foreach ($parts as $p) {
                $nid = $this->normalizeSerialId($p);
                if ($nid) {
                    $result[] = $nid;
                }
            }
            return $result;
        }
        $nid = $this->normalizeSerialId($rawSerialIds);
        return $nid ? [$nid] : [];
    }
    
    /**
     * Check if a material unit should have consolidated serials (for size/weight units)
     */
    private function shouldConsolidateSerials($unit)
    {
        // Đơn vị chiều dài
        $lengthUnits = [
            'Mét', 'm', 'meter', 'meters',
            'cm', 'centimeter', 'centimeters', 
            'mm', 'millimeter', 'millimeters',
            'km', 'kilometer', 'kilometers',
            'inch', 'inches', 'in',
            'foot', 'feet', 'ft',
            'yard', 'yards', 'yd'
        ];
        
        // Đơn vị khối lượng
        $weightUnits = [
            'Kg', 'kg', 'kilogram', 'kilograms',
            'gram', 'grams', 'g',
            'mg', 'milligram', 'milligrams',
            'ton', 'tons', 't',
            'pound', 'pounds', 'lb', 'lbs',
            'ounce', 'ounces', 'oz'
        ];
        
        // Đơn vị diện tích (có thể cần gộp serial)
        $areaUnits = [
            'm²', 'm2', 'square meter', 'square meters',
            'cm²', 'cm2', 'square centimeter', 'square centimeters',
            'km²', 'km2', 'square kilometer', 'square kilometers',
            'inch²', 'in²', 'square inch', 'square inches',
            'foot²', 'ft²', 'square foot', 'square feet'
        ];
        
        // Đơn vị thể tích (có thể cần gộp serial)
        $volumeUnits = [
            'm³', 'm3', 'cubic meter', 'cubic meters',
            'cm³', 'cm3', 'cubic centimeter', 'cubic centimeters',
            'liter', 'liters', 'l', 'L',
            'ml', 'milliliter', 'milliliters',
            'gallon', 'gallons', 'gal',
            'quart', 'quarts', 'qt'
        ];
        
        $consolidateUnits = array_merge($lengthUnits, $weightUnits, $areaUnits, $volumeUnits);
        
        return in_array($unit, $consolidateUnits);
    }

    /**
     * Consolidate serials for materials with size/weight units
     * Keep the same structure but consolidate serials within each component
     */
    private function consolidateSerialsForSizeWeightMaterials($components, $products)
    {
        $consolidatedComponents = [];

        foreach ($components as $component) {
            // Support both 'material_id' (edit form existing) and 'id' (newly added like create form)
            $materialId = $component['material_id'] ?? $component['id'] ?? null;
            if (!$materialId) {
                $consolidatedComponents[] = $component;
                continue;
            }
            $material = Material::find($materialId);
            
            if ($material && $this->shouldConsolidateSerials($material->unit)) {
                // For size/weight units, consolidate serials but keep the same structure
                $consolidatedComponent = $component;
                
                // Consolidate serials: if there are multiple serials, use the first one or combine them
                $serials = $this->normalizeSerials($component['serials'] ?? []);
                if (empty($serials) && isset($component['serial']) && $component['serial'] !== '') {
                    $serials = [$component['serial']];
                }
                
                // For consolidated serials, use the first serial or combine them
                if (!empty($serials)) {
                    $consolidatedComponent['serial'] = $serials[0]; // Use first serial
                    $consolidatedComponent['serials'] = []; // Clear multiple serials
                }
                
                // Consolidate serial IDs: use the first one
                $serialIds = $this->normalizeSerialIds($component['serial_ids'] ?? ($component['serial_id'] ?? []));
                if (!empty($serialIds)) {
                    $consolidatedComponent['serial_id'] = $serialIds[0]; // Use first serial ID
                    $consolidatedComponent['serial_ids'] = []; // Clear multiple serial IDs
                }
                
                $consolidatedComponent['is_consolidated'] = true;
                $consolidatedComponents[] = $consolidatedComponent;
            } else {
                // Keep non-consolidated components as is
                $consolidatedComponents[] = $component;
            }
        }

        return $consolidatedComponents;
    }

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
            $dateFrom = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_from)->format('Y-m-d');
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($request->filled('date_to')) {
            $dateTo = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_to)->format('Y-m-d');
            $query->whereDate('date', '<=', $dateTo);
        }

        $assemblies = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

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
        // dd($request->all());
        // Normalize components: accept either classic inputs or optimized JSON
        if ($request->has('components_json')) {
            $componentsData = json_decode($request->input('components_json'), true);
            if (is_array($componentsData)) {
                $filteredComponents = array_values(array_filter($componentsData, function ($component) {
                    $materialId = $component['material_id'] ?? $component['id'] ?? null;
                    return $materialId && isset($component['warehouse_id']) && $component['warehouse_id'];
                }));
                $request->merge(['components' => $filteredComponents]);
            }
        } elseif ($request->has('components') && is_array($request->components)) {
            // Filter out non-material component rows (e.g., placeholders without warehouse)
            $filteredComponents = array_values(array_filter($request->components, function ($component) {
                $materialId = $component['material_id'] ?? $component['id'] ?? null;
                return $materialId && isset($component['warehouse_id']) && $component['warehouse_id'];
            }));
            $request->merge(['components' => $filteredComponents]);
        }
        // Convert date format before validation
        $request->merge([
            'assembly_date' => DateHelper::convertToDatabaseFormat($request->assembly_date)
        ]);

        $request->validate([
            'assembly_code' => 'required|unique:assemblies,code',
            'assembly_date' => 'required|date',
            'warehouse_id' => 'nullable|exists:warehouses,id', // Changed to nullable since each component has its own warehouse
            'target_warehouse_id' => 'nullable|exists:warehouses,id',
            'default_warehouse_id' => 'nullable|exists:warehouses,id', // New field for default warehouse
            'assigned_to' => 'required|exists:employees,id',
            'tester_id' => 'required|exists:employees,id',
            'purpose' => 'required|in:storage,project',
            'project_id' => 'nullable|exists:projects,id',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.product_unit' => 'nullable|integer|min:0',
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
            
            // Consolidate serials for materials with size/weight units (Mét, Kg)
            $components = $this->consolidateSerialsForSizeWeightMaterials($components, $products);

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

                        // Check in serials table with type = product
                        $existingSerial = \App\Models\Serial::where('serial_number', $serial)
                            ->where('type', 'product')
                            ->first();

                        if ($existingSerial && $existingSerial->status === 'active') {
                            throw new \Exception("Serial '{$serial}' đã tồn tại trong tồn kho.");
                        }
                    }
                }
            }

            // Validate stock levels for all components
            foreach ($components as $component) {
                $materialId = $component['material_id'] ?? $component['id'] ?? null;
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
            // Use default_warehouse_id if provided, otherwise use warehouse_id from first component or null
            $defaultWarehouseId = $request->default_warehouse_id ?? (!empty($components) ? $components[0]['warehouse_id'] : null);

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
                'created_at' => $request->assembly_date,
                'updated_at' => now(),
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
                $rawProductSerials = $productData['serials'] ?? [];
                // Accept either array or comma-separated string
                $filteredSerials = array_values(array_unique($this->normalizeSerials($rawProductSerials)));
                $productSerialsStr = !empty($filteredSerials) ? implode(',', $filteredSerials) : null;

                // Create array of product units [0, 1, 2, ...] based on quantity
                $productUnits = [];
                for ($i = 0; $i < $productQty; $i++) {
                    $productUnits[] = $i;
                }

                // Create assembly product record with all serials and product_unit array
                AssemblyProduct::create([
                    'assembly_id' => $assembly->id,
                    'product_id' => $productData['id'],
                    'quantity' => $productQty,
                    'serials' => $productSerialsStr,
                    'product_unit' => $productUnits, // Model will automatically convert to JSON
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Use target_warehouse_id if available, otherwise use source warehouse_id
                $targetWarehouseId = $assembly->target_warehouse_id;
                if ($assembly->purpose === 'project' && !$targetWarehouseId) {
                    $targetWarehouseId = $assembly->warehouse_id;
                }

                // Serial records will be created only after testing completion
            }

            // Create assembly materials and update stock levels    
            foreach ($components as $component) {
                // Process serials - tolerate array or comma-separated string
                $serial = null;
                $serialIds = [];

                $normalizedSerials = $this->normalizeSerials($component['serials'] ?? []);
                if (!empty($normalizedSerials)) {
                    $serial = implode(',', array_values(array_unique($normalizedSerials)));
                } elseif (isset($component['serial']) && !empty($component['serial'])) {
                    $serial = $component['serial'];
                }

                // Get serial_ids if available in any format
                $serialIds = $this->normalizeSerialIds($component['serial_ids'] ?? ($component['serial_id'] ?? []));
                if (!empty($serialIds)) {
                    foreach ($serialIds as $sid) {
                        Serial::where('id', $sid)->update(['notes' => 'Assembly ID: ' . $assembly->id]);
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

                // Calculate serials for this specific unit
                $unitSerials = [];
                $unitSerialIds = [];

                $unitSerials = $this->normalizeSerials($component['serials'] ?? []);
                if (empty($unitSerials) && isset($component['serial']) && $component['serial'] !== '') {
                    $unitSerials = [$component['serial']];
                }
                // Fallback: if still empty but aggregate $serial above is present, reuse it
                if (empty($unitSerials) && !empty($serial)) {
                    $unitSerials = array_values(array_filter(array_map('trim', explode(',', (string)$serial))));
                }
                $unitSerialIds = $this->normalizeSerialIds($component['serial_ids'] ?? ($component['serial_id'] ?? []));

                $unitSerial = !empty($unitSerials) ? implode(',', $unitSerials) : null;

                // Create assembly material record for this specific unit
                $assemblyMaterialData = [
                    'assembly_id' => $assembly->id,
                    'material_id' => $component['material_id'] ?? $component['id'],
                    'target_product_id' => $componentProductId, // Link component to specific product
                    'product_unit' => $productUnit, // Store product unit for multi-unit assemblies
                    'quantity' => $componentQty,
                    'serial' => $unitSerial,
                    'note' => $component['note'] ?? null,
                    'warehouse_id' => $component['warehouse_id'], // Store warehouse_id for each component
                ];

                // Add serial_id if provided (for single serial or first serial of multiple)
                if (!empty($unitSerialIds)) {
                    // Normalize in case the first element is a comma-separated string
                    $first = $unitSerialIds[0] ?? null;
                    $normalizedFirst = $this->normalizeSerialId($first);
                    if ($normalizedFirst) {
                        $assemblyMaterialData['serial_id'] = $normalizedFirst; // Use first valid serial_id for this unit
                    }
                }

                AssemblyMaterial::create($assemblyMaterialData);
            }

            DB::commit();

            // Tạo thông báo thành công - chuyển sang workflow duyệt
            $successMessage = 'Phiếu lắp ráp đã được tạo thành công! Chờ duyệt để tạo phiếu kiểm thử và xuất kho.';

            // Tạo thông báo cho admin/người có quyền duyệt (đối tượng là Employee)
            $adminEmployees = Employee::whereHas('roleGroup.permissions', function ($query) {
                $query->where('name', 'assembly.approve');
            })->orWhere('role', 'admin')->get();

            foreach ($adminEmployees as $adminEmployee) {
                Notification::createNotification(
                    'Phiếu lắp ráp mới cần duyệt',
                    'Phiếu lắp ráp #' . $assembly->code . ' đã được tạo và cần duyệt.',
                    'info',
                    $adminEmployee->id,
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

        // Luôn ưu tiên phiếu xuất kho vật tư sinh ra từ phiếu lắp ráp này
        // Tiêu chí: dispatch_note chứa mã phiếu và không gắn project (project_id NULL)
        $dispatch = \App\Models\Dispatch::where(function ($q) use ($assembly) {
            $q->where('dispatch_note', 'like', '%Sinh từ phiếu lắp ráp: ' . $assembly->code . '%')
                ->orWhere('dispatch_note', 'like', '%Sinh ra từ phiếu lắp ráp ' . $assembly->code . '%')
                ->orWhere('dispatch_note', 'like', '%Từ phiếu lắp ráp ' . $assembly->code . '%');
        })
            ->whereNull('project_id')
            ->orderByDesc('created_at')
            ->first();

        // Fallback: nếu không có, vẫn chọn phiếu note chứa mã và null project
        if (!$dispatch) {
            $dispatch = \App\Models\Dispatch::where('dispatch_note', 'like', '%' . $assembly->code . '%')
                ->whereNull('project_id')
                ->orderByDesc('created_at')
                ->first();
        }

        $dispatches = collect();
        if ($dispatch) {
            $dispatches->push($dispatch);
        }

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

        return view('assemble.show', compact('assembly', 'dispatches', 'dispatch'));
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

        // Load all material serials for each material (respect per-component warehouse)
        $materialSerials = [];
        foreach ($assembly->materials as $material) {
            $warehouseIdForMaterial = $material->warehouse_id ?: $assembly->warehouse_id;
            $query = Serial::where('product_id', $material->material_id)
                ->where('type', 'material')
                ->where('warehouse_id', $warehouseIdForMaterial)
                ->where('status', 'active')
                ->where(function ($q) use ($assembly, $material) {
                    $q->whereNull('notes')
                        ->orWhere('notes', 'like', '%Assembly ID: ' . $assembly->id . '%');
                });

            // Loại trừ serial đang được sử dụng trong các phiếu lắp ráp khác ở trạng thái in_progress hoặc completed
            $usedSerialsInOtherAssemblies = AssemblyMaterial::where('material_id', $material->material_id)
                ->where(function ($query) {
                    $query->whereNotNull('serial')
                        ->orWhereNotNull('serial_id');
                })
                ->whereHas('assembly', function ($query) use ($assembly) {
                    $query->whereIn('status', ['in_progress', 'completed'])
                        ->where('id', '!=', $assembly->id);
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

            // Create unique key for material + warehouse + product_unit combination
            $key = $material->material_id . '_' . $warehouseIdForMaterial . '_' . ($material->product_unit ?? 0);
            $materialSerials[$key] = $serials;
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

        // Additional data for editable header fields when status is pending
        $employees = Employee::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        $projectsQuery = Project::orderBy('project_name');
        if ($user && $user->role !== 'admin') {
            $projectsQuery->whereHas('roles', function ($query) use ($user) {
                $query->where('roles.id', $user->role_id);
            });
        }
        $projects = $projectsQuery->get(['id', 'project_name', 'project_code']);

        // Also provide selectable data to enable adding products/materials in edit mode
        $products = \App\Models\Product::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $materials = \App\Models\Material::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'category', 'unit']);

        $warehouses = \App\Models\Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('assemble.edit', compact('assembly', 'productSerials', 'materialSerials', 'allProductSerials', 'employees', 'projects', 'products', 'materials', 'warehouses'));
    }

    /**
     * Update the specified assembly in storage.
     */
    public function update(Request $request, Assembly $assembly)
    {
        // Convert date format before validation
        $request->merge([
            'assembly_date' => DateHelper::convertToDatabaseFormat($request->assembly_date)
        ]);

        // Validation rules based on assembly status
        $validationRules = [
            'assembly_date' => 'required|date',
            'assembly_note' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:1',
            'products.*.serials' => 'nullable|array',
            'deleted_products' => 'nullable|array',
            'deleted_products.*' => 'integer|exists:products,id',
            'deleted_components' => 'nullable|array',
            'deleted_components.*.material_id' => 'required|integer|exists:materials,id',
            'deleted_components.*.target_product_id' => 'required|integer|exists:products,id',
            'deleted_components.*.product_unit' => 'nullable|integer|min:0',
        ];

        // Allow editing header fields while pending
        if ($assembly->status === 'pending') {
            $validationRules = array_merge($validationRules, [
                'assigned_to' => 'required|exists:employees,id',
                'tester_id' => 'required|exists:employees,id',
                'purpose' => 'required|in:storage,project',
                'project_id' => 'nullable|exists:projects,id',
            ]);
        }

        if ($assembly->status === 'in_progress') {
            // For in_progress assemblies, allow quantity and serial updates
            $validationRules['components'] = 'nullable|array';
            $validationRules['components.*.quantity'] = 'required|integer|min:1';
            $validationRules['components.*.serials'] = 'nullable|array';
            $validationRules['components.*.note'] = 'nullable|string';
            $validationRules['components.*.warehouse_id'] = 'nullable|exists:warehouses,id';
        } else {
            // For other statuses, allow serial and note updates
            $validationRules['components'] = 'nullable|array';
            $validationRules['components.*.serial'] = 'nullable|string';
            $validationRules['components.*.note'] = 'nullable|string';
            $validationRules['components.*.product_unit'] = 'nullable|integer|min:0';
            $validationRules['components.*.warehouse_id'] = 'nullable|exists:warehouses,id';
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

                        // Check in serials table with type = product
                        $existingSerial = \App\Models\Serial::where('serial_number', $serial)
                            ->where('type', 'product')
                            ->first();

                        if ($existingSerial && $existingSerial->status === 'active') {
                            // Allow if this serial already belongs to current assembly record being edited
                            $belongsToCurrentAssembly = \App\Models\AssemblyProduct::where('assembly_id', $assembly->id)
                                ->where('product_id', $productData['id'])
                                ->whereRaw('FIND_IN_SET(?, serials)', [$serial])
                                ->exists();

                            if (!$belongsToCurrentAssembly) {
                                throw new \Exception("Serial '{$serial}' đã tồn tại trong tồn kho.");
                            }
                        }
                    }
                }
            }

            // 2. Validate component serials for duplicates
            if ($request->components) {
                // First, collect all serials by material_id to check for cross-component duplicates
                $allSerialsByMaterial = [];
                $materialNames = [];

                foreach ($request->components as $componentIndex => $component) {
                    $materialId = $component['material_id'] ?? $component['id'] ?? null;
                    if (!$materialId) continue;

                    // Get material name for error messages
                    if (!isset($materialNames[$materialId])) {
                        $material = Material::find($materialId);
                        $materialNames[$materialId] = $material ? $material->name : 'Unknown';
                    }

                    // Collect serials for this component
                    $componentSerials = [];
                    if (isset($component['serials']) && is_array($component['serials'])) {
                        $componentSerials = array_filter($component['serials']);
                    } elseif (!empty($component['serial'])) {
                        $componentSerials = [$component['serial']];
                    }

                    // Add to material's serial list
                    if (!empty($componentSerials)) {
                        if (!isset($allSerialsByMaterial[$materialId])) {
                            $allSerialsByMaterial[$materialId] = [];
                        }
                        $allSerialsByMaterial[$materialId] = array_merge($allSerialsByMaterial[$materialId], $componentSerials);
                    }
                }

                // Check for duplicate serials across different components for the same material and product unit
                foreach ($allSerialsByMaterial as $materialId => $allSerials) {
                    // Group by product unit for this material
                    $serialsByProductUnit = [];
                    foreach ($request->components as $component) {
                        $componentMaterialId = $component['material_id'] ?? $component['id'] ?? null;
                        if ($componentMaterialId == $materialId) {
                            $productUnit = $component['product_unit'] ?? 0;
                            if (!isset($serialsByProductUnit[$productUnit])) {
                                $serialsByProductUnit[$productUnit] = [];
                            }
                            
                            // Add serials from this component
                            if (isset($component['serials']) && is_array($component['serials'])) {
                                $filteredSerials = array_filter($component['serials'], function($serial) {
                                    return !is_null($serial) && $serial !== '';
                                });
                                $serialsByProductUnit[$productUnit] = array_merge($serialsByProductUnit[$productUnit], $filteredSerials);
                            }
                            if (!empty($component['serial'])) {
                                $serialsByProductUnit[$productUnit][] = $component['serial'];
                            }
                        }
                    }
                    
                    // Check for duplicates within each product unit
                    foreach ($serialsByProductUnit as $productUnit => $serials) {
                        $uniqueSerials = array_unique($serials);
                        if (count($serials) !== count($uniqueSerials)) {
                            $materialName = $materialNames[$materialId];
                            $duplicates = array_diff_assoc($serials, $uniqueSerials);
                            $duplicateList = implode(', ', array_unique($duplicates));
                            throw new \Exception("Phát hiện serial trùng lặp cho linh kiện '{$materialName}' (Đơn vị " . ($productUnit + 1) . "): {$duplicateList}. Mỗi serial chỉ có thể sử dụng một lần trong cùng đơn vị thành phẩm.");
                        }
                    }
                }

                // Then check individual component validation
                foreach ($request->components as $componentIndex => $component) {
                    // Check for duplicate serials within the same component
                    if (isset($component['serials']) && is_array($component['serials'])) {
                        $filteredSerials = array_filter($component['serials']);
                        $uniqueSerials = array_unique($filteredSerials);

                        if (count($filteredSerials) !== count($uniqueSerials)) {
                            throw new \Exception("Phát hiện trùng lặp serial linh kiện trong cùng một dòng!");
                        }
                    }

                    // Check for existing serials in other assemblies
                    if (!empty($component['serial'])) {
                        $existingMaterial = AssemblyMaterial::whereHas('assembly', function ($query) use ($assembly) {
                            $query->where('id', '!=', $assembly->id);
                        })
                            ->where('material_id', $component['material_id'] ?? $component['id'])
                            ->where('serial', $component['serial'])
                            ->first();

                        if ($existingMaterial) {
                            $existingAssembly = Assembly::find($existingMaterial->assembly_id);
                            $materialName = Material::find($component['material_id'] ?? $component['id'])->name ?? 'Unknown';
                            throw new \Exception("Serial '{$component['serial']}' của linh kiện '{$materialName}' đã được sử dụng trong phiếu lắp ráp #{$existingAssembly->code}.");
                        }
                    }
                }
            }

            // 3. Update assembly basic info
            $basicUpdate = [
                'date' => $request->assembly_date,
                'notes' => $request->assembly_note,
            ];

            // When pending, also allow updating header fields
            if ($assembly->status === 'pending') {
                $basicUpdate['assigned_employee_id'] = $request->assigned_to;
                $basicUpdate['tester_id'] = $request->tester_id;
                $basicUpdate['purpose'] = $request->purpose;
                $basicUpdate['project_id'] = $request->purpose === 'project' ? ($request->project_id ?: null) : null;
            }

            $basicUpdate['updated_at'] = now();
            $assembly->update($basicUpdate);

            // 4. Handle product additions/removals and serial updates (pending only)
            if ($assembly->status === 'pending') {
                // Remove selected products
                if ($request->filled('deleted_products')) {
                    foreach ($request->deleted_products as $deletedProductId) {
                        // Delete assembly materials tied to this product
                        AssemblyMaterial::where('assembly_id', $assembly->id)
                            ->where('target_product_id', $deletedProductId)
                            ->delete();

                        // Delete assembly product record
                        AssemblyProduct::where('assembly_id', $assembly->id)
                            ->where('product_id', $deletedProductId)
                            ->delete();
                    }
                }

                // Get existing products to compare with new ones
                $existingProducts = AssemblyProduct::where('assembly_id', $assembly->id)->get();
                $existingProductUnits = [];
                foreach ($existingProducts as $existingProduct) {
                    $productId = $existingProduct->product_id;
                    $productUnits = $existingProduct->product_unit;
                    if (is_string($productUnits) && str_starts_with($productUnits, '[')) {
                        $productUnits = json_decode($productUnits, true);
                    }
                    if (is_array($productUnits)) {
                        foreach ($productUnits as $unit) {
                            $existingProductUnits[] = $productId . '_' . $unit;
                        }
                    } else {
                        $existingProductUnits[] = $productId . '_' . ($productUnits ?? 0);
                    }
                }

                // Delete existing products for this assembly first
                AssemblyProduct::where('assembly_id', $assembly->id)->delete();

                // Create new assembly products for each product
                $newProductUnits = [];
                foreach ($request->products as $productIndex => $productData) {
                    $productId = (int) $productData['id'];
                    $productQty = isset($productData['quantity']) ? (int) $productData['quantity'] : 1;
                    $rawProductSerials = $productData['serials'] ?? [];
                    $filteredSerials = array_values(array_unique($this->normalizeSerials($rawProductSerials)));
                    $productSerialsStr = !empty($filteredSerials) ? implode(',', $filteredSerials) : null;

                    // Always create product units based on current quantity
                    // This ensures product_unit array matches the actual quantity
                    $productUnits = [];
                    for ($i = 0; $i < $productQty; $i++) {
                        $productUnits[] = $i;
                    }

                    AssemblyProduct::create([
                        'assembly_id' => $assembly->id,
                        'product_id' => $productId,
                        'quantity' => $productQty,
                        'serials' => $productSerialsStr,
                        'product_unit' => $productUnits, // Model will automatically convert to JSON
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Track new product units for cleanup comparison
                    foreach ($productUnits as $unit) {
                        $newProductUnits[] = $productId . '_' . $unit;
                    }

                    // Maintain serial records (recreate all for this assembly once below)
                }

                // Clean up materials for removed product units
                $removedProductUnits = array_diff($existingProductUnits, $newProductUnits);
                if (!empty($removedProductUnits)) {
                    foreach ($removedProductUnits as $removedUnit) {
                        list($removedProductId, $removedUnitIndex) = explode('_', $removedUnit);

                        // Delete materials for this specific product unit
                        \App\Models\AssemblyMaterial::where('assembly_id', $assembly->id)
                            ->where('target_product_id', $removedProductId)
                            ->where('product_unit', $removedUnitIndex)
                            ->delete();

                        Log::info('Cleaned up materials for removed product unit', [
                            'assembly_id' => $assembly->id,
                            'product_id' => $removedProductId,
                            'product_unit' => $removedUnitIndex
                        ]);
                    }
                }

                // Serial records will be created only after testing completion
            }

            // 5. Delete selected components (pending only)
            // Track deleted component keys to avoid recreating them later
            $deletedKeys = [];
            if ($assembly->status === 'pending' && $request->filled('deleted_components')) {
                foreach ($request->deleted_components as $deleted) {
                    $materialId = (int)($deleted['material_id'] ?? 0);
                    $targetProductId = (int)($deleted['target_product_id'] ?? 0);
                    // Normalize product unit: treat empty string as null
                    $rawProductUnit = $deleted['product_unit'] ?? null;
                    $productUnit = ($rawProductUnit === '' || $rawProductUnit === null) ? null : (int)$rawProductUnit;
                    $deletedKeys[] = $materialId . '|' . ($targetProductId ?: 'null') . '|' . (is_null($productUnit) ? 'null' : $productUnit);
                    $query = AssemblyMaterial::where('assembly_id', $assembly->id)
                        ->where('material_id', $materialId)
                        ->when($targetProductId > 0, function ($q) use ($targetProductId) {
                            $q->where('target_product_id', $targetProductId);
                        }, function ($q) {
                            $q->whereNull('target_product_id');
                        });
                    if ($productUnit !== null) {
                        $query->where('product_unit', $productUnit);
                    }
                    $deletedCount = $query->delete();

                    // Fallback: if nothing deleted (possible mismatch on target_product_id), retry without target_product_id condition
                    if ($deletedCount === 0) {
                        $fallback = AssemblyMaterial::where('assembly_id', $assembly->id)
                            ->where('material_id', $materialId);
                        if ($productUnit !== null) {
                            $fallback->where('product_unit', $productUnit);
                        }
                        $deletedCount2 = $fallback->delete();
                    }
                }
            }

            // Build a map from client-side product keys (e.g., "product_0") to real product IDs
            $productKeyToId = [];
            if ($request->filled('products') && is_array($request->products)) {
                foreach ($request->products as $idx => $prod) {
                    if (isset($prod['id'])) {
                        $productKeyToId['product_' . $idx] = (int) $prod['id'];
                    }
                }
            }
            // Also build mapping from current assembly products order used by server-rendered rows
            if (!$assembly->relationLoaded('products')) {
                $assembly->load('products');
            }
            if ($assembly->products && $assembly->products->count() > 0) {
                foreach ($assembly->products->values() as $idx => $assemblyProduct) {
                    $key = 'product_' . $idx;
                    if (!isset($productKeyToId[$key])) {
                        $productKeyToId[$key] = (int) $assemblyProduct->product_id;
                    }
                }
            }

            // 6. Update or create component materials
            if ($request->components) {
                // Consolidate serials for materials with size/weight units (Mét, Kg)
                $request->merge(['components' => $this->consolidateSerialsForSizeWeightMaterials($request->components, $request->products)]);
                foreach ($request->components as $componentIndex => $component) {
                    // Support both 'material_id' (edit form existing) and 'id' (newly added like create form)
                    $materialId = $component['material_id'] ?? $component['id'] ?? null;
                    if (!$materialId) {
                        continue;
                    }

                    $targetProductId = $component['target_product_id'] ?? $component['product_id'] ?? null;
                    // In edit form, product_id may be like 'product_0'. Normalize to real numeric product id
                    if (!is_null($targetProductId) && !is_numeric($targetProductId)) {
                        if (isset($productKeyToId[$targetProductId])) {
                            $targetProductId = $productKeyToId[$targetProductId];
                        } elseif (isset($component['target_product_id']) && is_numeric($component['target_product_id'])) {
                            $targetProductId = (int) $component['target_product_id'];
                        } else {
                            // As a fallback, null out to avoid mismatched string filter
                            $targetProductId = null;
                        }
                    } else if (is_numeric($targetProductId)) {
                        $targetProductId = (int) $targetProductId;
                    }

                    $productUnit = isset($component['product_unit']) ? (int)$component['product_unit'] : null;

                    // Find specific assembly material (match by material + target product + optional unit)
                    $assemblyMaterialQuery = AssemblyMaterial::where('assembly_id', $assembly->id)
                        ->where('material_id', $materialId);
                    if (!is_null($targetProductId)) {
                        $assemblyMaterialQuery->where('target_product_id', (int) $targetProductId);
                    }
                    if ($productUnit !== null) {
                        $assemblyMaterialQuery->where('product_unit', $productUnit);
                    }
                    $assemblyMaterial = $assemblyMaterialQuery->first();
                    // Fallback: migrate legacy rows having NULL target_product_id (commonly the first product)
                    if (!$assemblyMaterial && !is_null($targetProductId)) {
                        $legacyQuery = AssemblyMaterial::where('assembly_id', $assembly->id)
                            ->where('material_id', $materialId)
                            ->whereNull('target_product_id');
                        if ($productUnit !== null) {
                            $legacyQuery->where('product_unit', $productUnit);
                        }
                        $legacyRow = $legacyQuery->first();
                        if ($legacyRow) {
                            // Attach it to the correct target product to avoid future duplicates
                            $legacyRow->update(['target_product_id' => (int) $targetProductId]);
                            $assemblyMaterial = $legacyRow;
                        }
                    }

                    // Skip recreation if this tuple was marked as deleted
                    $componentKey = ($materialId ?: '0') . '|' . (($targetProductId ?? null) ?: 'null') . '|' . (isset($productUnit) && $productUnit !== null ? $productUnit : 'null');
                    if (!$assemblyMaterial && in_array($componentKey, $deletedKeys, true)) {
                        Log::info('Skip recreating component because it was deleted in this request', ['key' => $componentKey]);
                        continue;
                    }

                    if ($assemblyMaterial) {
                        if ($assembly->status === 'in_progress') {
                            // For in_progress assemblies, allow quantity updates and serial updates
                            $newQuantity = (int)($component['quantity'] ?? $assemblyMaterial->quantity);
                            $oldQuantity = $assemblyMaterial->quantity;

                            if ($newQuantity < $oldQuantity) {
                                throw new \Exception("Không thể giảm số lượng linh kiện từ {$oldQuantity} xuống {$newQuantity}. Chỉ có thể tăng số lượng.");
                            }

                            // Process serials: start with current serials as baseline
                            $existingSerials = [];
                            if (!empty($assemblyMaterial->serial)) {
                                $existingSerials = array_filter(array_map('trim', explode(',', (string)$assemblyMaterial->serial)));
                            }

                            $serial = $assemblyMaterial->serial; // default: keep existing if client omitted
                            if (isset($component['serials']) && is_array($component['serials'])) {
                                // Filter out empty serials
                                $filteredSerials = array_filter($component['serials']);
                                // Only use unique values to prevent duplicates
                                $uniqueSerials = array_unique(array_map('trim', $filteredSerials));
                                // Merge existing (read-only originals) with new selections, preserve order (existing first)
                                $merged = array_values(array_unique(array_merge($existingSerials, $uniqueSerials)));
                                // Limit to new quantity
                                $merged = array_slice($merged, 0, max(0, $newQuantity));
                                // Convert to comma-separated string
                                $serial = !empty($merged) ? implode(',', $merged) : null;
                            } elseif (isset($component['serial'])) {
                                // Single serial provided (edge cases) – override appropriately
                                $serial = $component['serial'];
                            } else {
                                // No serial payload – keep existing serials unchanged
                                $serial = $assemblyMaterial->serial;
                            }

                            $updateData = [
                                'quantity' => $newQuantity,
                                'serial' => $serial,
                                'note' => $component['note'] ?? $assemblyMaterial->note,
                                'serial_id' => null, // Reset first
                                'product_unit' => $assemblyMaterial->product_unit,
                                'warehouse_id' => $component['warehouse_id'] ?? $assemblyMaterial->warehouse_id,
                            ];

                            // Set new serial_id if provided
                            if (isset($component['serial_id']) && !empty($component['serial_id'])) {
                                $normalizedSerialId = $this->normalizeSerialId($component['serial_id']);
                                if ($normalizedSerialId) {
                                    $updateData['serial_id'] = $normalizedSerialId;

                                    // Update new serial status
                                    Serial::where('id', $normalizedSerialId)
                                        ->update(['notes' => 'Assembly ID: ' . $assembly->id]);
                                }
                            }
                        } else if ($assembly->status === 'pending') {
                            // For pending status, allow quantity, serial and note updates
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
                                'quantity' => (int)($component['quantity'] ?? $assemblyMaterial->quantity),
                                'serial' => $serial,
                                'note' => $component['note'] ?? null,
                                'serial_id' => null, // Reset first
                                'warehouse_id' => $component['warehouse_id'] ?? $assemblyMaterial->warehouse_id,
                            ];
                        } else {
                            // For other statuses, allow serial and note updates only
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
                                'warehouse_id' => $component['warehouse_id'] ?? $assemblyMaterial->warehouse_id,
                            ];

                            // Set new serial_id if provided
                            if (isset($component['serial_id']) && !empty($component['serial_id'])) {
                                $normalizedSerialId = $this->normalizeSerialId($component['serial_id']);
                                if ($normalizedSerialId) {
                                    $updateData['serial_id'] = $normalizedSerialId;

                                    // Update new serial status
                                    Serial::where('id', $normalizedSerialId)
                                        ->update(['notes' => 'Assembly ID: ' . $assembly->id]);
                                }
                            }

                            // Do not change product_unit for non in_progress statuses
                        }

                        $assemblyMaterial->update($updateData);
                    } else if ($assembly->status === 'pending') {
                        // Create new component when pending
                        $quantity = (int)($component['quantity'] ?? 1);
                        $serial = null;
                        if (isset($component['serials']) && is_array($component['serials'])) {
                            $filteredSerials = array_filter($component['serials']);
                            $serial = !empty($filteredSerials) ? implode(',', array_unique($filteredSerials)) : null;
                        } elseif (isset($component['serial'])) {
                            $serial = $component['serial'] ?: null;
                        }
                        $warehouseId = $component['warehouse_id'] ?? $assembly->warehouse_id;

                        AssemblyMaterial::create([
                            'assembly_id' => $assembly->id,
                            'material_id' => $materialId,
                            'quantity' => max(1, $quantity),
                            'serial' => $serial,
                            'note' => $component['note'] ?? null,
                            'serial_id' => isset($component['serial_id']) ? $this->normalizeSerialId($component['serial_id']) : null,
                            'warehouse_id' => $warehouseId,
                            'target_product_id' => is_numeric($targetProductId) ? (int) $targetProductId : null,
                            'product_unit' => $productUnit,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
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
                        } else {
                            // Product doesn't exist, create new record
                            WarehouseMaterial::create([
                                'warehouse_id' => $dispatchItem->warehouse_id,
                                'material_id' => $dispatchItem->item_id,
                                'quantity' => $dispatchItem->quantity,
                                'item_type' => 'product'
                            ]);
                        }
                    }
                }

                // Delete dispatch items first
                $dispatch->items()->delete();
                // Delete the dispatch
                $dispatch->delete();
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
     * Delete serial records for this assembly and restore material serials
     */
    private function deleteSerialRecords($assemblyId)
    {
        try {
            Log::info('Bắt đầu xóa serial records cho assembly', [
                'assembly_id' => $assemblyId
            ]);

            // Find all testing records for this assembly
            $testings = \App\Models\Testing::where('assembly_id', $assemblyId)->get();
            
            foreach ($testings as $testing) {
                // Delete serial records created during testing completion
                foreach ($testing->items as $item) {
                    if ($item->item_type === 'product' && $item->result === 'pass' && !empty($item->serial_number)) {
                        $serialArray = explode(',', $item->serial_number);
                        $serialArray = array_map('trim', $serialArray);
                        $serialArray = array_filter($serialArray);

                        foreach ($serialArray as $serial) {
                            if (empty($serial)) continue;

                            // Delete the serial record created during testing
                            \App\Models\Serial::where('serial_number', $serial)
                                ->where('product_id', $item->product_id)
                                ->where('type', 'product')
                                ->where('notes', 'like', '%Testing ID: ' . $testing->id . '%')
                                ->delete();
                        }
                    }
                }
            }

            Log::info('Hoàn thành xóa serial records cho assembly', [
                'assembly_id' => $assemblyId
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa serial records cho assembly', [
                'assembly_id' => $assemblyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Serial records are now managed only during testing completion process

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
            // Kiểm tra serial có tồn tại và active không
            $existingSerial = \App\Models\Serial::where('serial_number', $serial)
                ->where('type', 'product')
                ->first();

            if ($existingSerial) {
                if ($existingSerial->status === 'active') {
                    // Kiểm tra xem serial có thuộc về assembly hiện tại không (khi edit)
                    if ($assemblyId) {
                        $belongsToCurrentAssembly = \App\Models\AssemblyProduct::where('assembly_id', $assemblyId)
                            ->where('product_id', $productId)
                            ->whereRaw('FIND_IN_SET(?, serials)', [$serial])
                            ->exists();

                        if ($belongsToCurrentAssembly) {
                            return response()->json([
                                'exists' => false,
                                'message' => 'Serial hợp lệ'
                            ]);
                        }
                    }

                    return response()->json([
                        'exists' => true,
                        'message' => "Serial '{$serial}' đã tồn tại và đang hoạt động trong tồn kho",
                        'type' => 'serial'
                    ]);
                } else {
                    // Serial tồn tại nhưng inactive - có thể tái sử dụng
                    return response()->json([
                        'exists' => false,
                        'message' => 'Serial hợp lệ (tái sử dụng từ serial không đạt)'
                    ]);
                }
            }

            // Serial không tồn tại - hợp lệ
            return response()->json([
                'exists' => false,
                'message' => 'Serial hợp lệ'
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi kiểm tra serial', [
                'serial' => $serial,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra khi kiểm tra serial'
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

                if ($assemblyMaterial) {
                    if (!empty($assemblyMaterial->serial)) {
                        $existingSerials = array_map('trim', explode(',', $assemblyMaterial->serial));
                    } elseif (!empty($assemblyMaterial->serial_id)) {
                        $s = Serial::find($assemblyMaterial->serial_id);
                        if ($s) {
                            $existingSerials = [trim($s->serial_number)];
                        }
                    }
                }
            }

            // Only use warehouse_materials.serial_number JSON (source of truth by warehouse/material)
            $wmSerialJsonList = WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $materialId)
                ->where('item_type', 'material')
                ->whereNotNull('serial_number')
                ->where('serial_number', '!=', '[]')
                ->where('serial_number', '!=', 'null')
                ->pluck('serial_number');
            $extraSerials = [];
            foreach ($wmSerialJsonList as $serialJson) {
                $arr = json_decode($serialJson, true);
                if (is_array($arr)) {
                    foreach ($arr as $sn) {
                        $sn = trim($sn);
                        if ($sn !== '') {
                            $extraSerials[$sn] = true;
                        }
                    }
                }
            }
            $jsonSerials = array_keys($extraSerials);
            $serials = collect($jsonSerials)
                ->sort()
                ->map(function ($sn) { return (object)['id' => null, 'serial_number' => $sn]; })
                ->values();

            // Ensure currently selected serials appear in dropdown even if not in warehouse JSON now
            foreach ($existingSerials as $existingSerial) {
                if (!$serials->contains('serial_number', $existingSerial)) {
                    $serials->push((object)['id' => null, 'serial_number' => $existingSerial]);
                }
            }

            $serials = $serials->toArray();

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
        // Load assembly products and project if not already loaded
        if (!$assembly->relationLoaded('products')) {
            $assembly->load('products.product');
        }
        if (!$assembly->relationLoaded('project')) {
            $assembly->load('project');
        }

        // Generate dispatch code
        $dispatchCode = Dispatch::generateDispatchCode();

        // Get current user for created_by
        $currentUserId = Auth::user() ? Auth::user()->id : 1; // Fallback to user ID 1

        // Sử dụng warehouse_id nếu target_warehouse_id là null
        $sourceWarehouseId = $assembly->target_warehouse_id ?? $assembly->warehouse_id;

        // Tạo trường project_receiver theo định dạng yêu cầu
        $projectReceiver = '';
        if ($assembly->project) {
            $projectReceiver = $assembly->project->project_code . ' - ' . $assembly->project->project_name . ' (' . ($assembly->project->customer->name ?? 'N/A') . ')';
        } else {
            $projectReceiver = 'Dự án';
        }

        // Tạo trường dispatch_note theo định dạng yêu cầu
        $dispatchNote = 'Sinh ra từ phiếu lắp ráp ' . $assembly->code;

        // Create dispatch record
        $dispatch = Dispatch::create([
            'dispatch_code' => $dispatchCode,
            'dispatch_date' => now(), // Sử dụng thời gian hiện tại thay vì assembly->date
            'dispatch_type' => 'project',
            'dispatch_detail' => 'contract', // "Xuất theo hợp đồng"
            'project_id' => $assembly->project_id,
            'project_receiver' => $projectReceiver,
            'warranty_period' => null, // Có thể thêm logic để set warranty period
            'company_representative_id' => $assembly->assigned_employee_id,
            'dispatch_note' => $dispatchNote,
            'status' => 'pending',
            'created_by' => $currentUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create dispatch items for each assembled product
        foreach ($assembly->products as $assemblyProduct) {
            // Parse serials if available
            $serialNumbers = [];
            if ($assemblyProduct->serials) {
                $serialNumbers = array_filter(explode(',', $assemblyProduct->serials));
            }

            // Use product_unit as array for JSON storage in dispatch_items
            $productUnit = $assemblyProduct->product_unit;

            DispatchItem::create([
                'dispatch_id' => $dispatch->id,
                'item_type' => 'product',
                'item_id' => $assemblyProduct->product_id,
                'quantity' => $assemblyProduct->quantity,
                'warehouse_id' => $sourceWarehouseId,
                'category' => 'contract',
                'serial_numbers' => !empty($serialNumbers) ? $serialNumbers : null,
                'assembly_id' => $assemblyProduct->assembly_id,
                'product_unit' => $productUnit,
                'notes' => 'Từ phiếu lắp ráp ' . $assembly->code,
                'created_at' => now(),
                'updated_at' => now(),
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


        return $dispatch;
    }

    /**
     * Tạo phiếu xuất kho cho vật tư khi lắp ráp lưu kho
     */
    private function createMaterialExportSlipForAssembly(Assembly $assembly)
    {

        // Tạo mã phiếu xuất kho tự động
        $exportCode = 'XK' . date('ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);


        // Tạo trường project_receiver theo định dạng yêu cầu
        // - Nếu xuất đi dự án: hiển thị mã phiếu xuất
        // - Nếu lưu kho: hiển thị mã phiếu lắp ráp
        $projectReceiver = $assembly->purpose === 'project'
            ? ('Lắp ráp xuất đi dự án: ' . $assembly->code)
            : ('Lắp ráp lưu kho: ' . $assembly->code);

        // Tạo trường dispatch_note theo định dạng yêu cầu
        $dispatchNote = 'Sinh từ phiếu lắp ráp: ' . $assembly->code;

        // Tạo phiếu xuất kho
        $dispatch = \App\Models\Dispatch::create([
            'dispatch_code' => $exportCode,
            'dispatch_date' => now(),
            'dispatch_type' => 'project', // Sử dụng 'project' thay vì 'assembly_material' vì enum chỉ chấp nhận 3 giá trị
            'dispatch_detail' => 'all', // Sử dụng 'all' thay vì 'Vật tư lắp ráp' vì enum chỉ chấp nhận 3 giá trị
            'project_id' => null,
            'project_receiver' => $projectReceiver,
            'warranty_period' => null,
            'company_representative_id' => Auth::id(),
            'dispatch_note' => $dispatchNote,
            'status' => 'approved', // Tự động duyệt
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // Lấy danh sách vật tư đã sử dụng trong lắp ráp
        $assemblyMaterials = \App\Models\AssemblyMaterial::where('assembly_id', $assembly->id)->get();



        foreach ($assemblyMaterials as $am) {
            if ($am->material) {


                // Xử lý serial_numbers - chuyển thành array
                $serialNumbers = null;
                if ($am->serial) {
                    $serialArray = explode(',', $am->serial);
                    $serialNumbers = $serialArray; // Không cần json_encode vì model đã cast thành array

                }

                // Tạo item trong phiếu xuất kho
                $dispatchItem = \App\Models\DispatchItem::create([
                    'dispatch_id' => $dispatch->id,
                    'item_type' => 'material',
                    'item_id' => $am->material_id,
                    'quantity' => $am->quantity,
                    // Xuất đúng từ kho đã chọn cho từng vật tư
                    'warehouse_id' => $am->warehouse_id,
                    'category' => 'general',
                    'serial_numbers' => $serialNumbers,
                    'notes' => 'Vật tư lắp ráp từ phiếu lắp ráp',
                    'created_at' => now(),
                    'updated_at' => now(),
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
                        // Ghi nhận đúng kho xuất theo vật tư
                        'warehouse_id' => $am->warehouse_id,
                        'target_warehouse_id' => $assembly->target_warehouse_id,
                        'quantity' => $am->quantity,
                        'serial' => $am->serial,
                        'assigned_employee_id' => $assembly->assigned_employee_id,
                        'created_by' => Auth::id(),
                        'created_at' => now()->toDateTimeString(),
                        'action_type' => 'material_assembly_warehouse_export'
                    ],
                    'Vật tư lắp ráp'
                );

                // Giảm tồn kho ngay khi tạo phiếu xuất vật tư (phiếu này đang tự động approved)
                try {
                    $wmRows = \App\Models\WarehouseMaterial::where('warehouse_id', $am->warehouse_id)
                        ->where('material_id', $am->material_id)
                        ->where('item_type', 'material')
                        ->get();

                    if ($wmRows->isNotEmpty()) {
                        // Giảm quantity trên dòng đầu tiên (giả định quantity tổng nằm trên 1 dòng)
                        $wm = $wmRows->first();
                        $newQty = max(0, ((int)$wm->quantity) - ((int)$am->quantity));
                        $wm->update(['quantity' => $newQty]);

                        // Nếu có serial xuất, loại bỏ các serial đã dùng khỏi JSON serial_number
                        if (!empty($serialNumbers)) {
                            foreach ($wmRows as $row) {
                                if (!empty($row->serial_number)) {
                                    $arr = json_decode($row->serial_number, true);
                                    if (is_array($arr)) {
                                        // Lọc bỏ serial đã dùng (so sánh chuỗi tỉa khoảng trắng)
                                        $remaining = [];
                                        foreach ($arr as $sn) {
                                            $snTrim = trim($sn);
                                            if ($snTrim === '') continue;
                                            if (!in_array($snTrim, $serialNumbers, true)) {
                                                $remaining[] = $snTrim;
                                            }
                                        }
                                        $row->serial_number = json_encode($remaining);
                                        $row->save();
                                    }
                                }
                            }

                            // Cập nhật bảng serials: đánh dấu serial đã dùng
                            \App\Models\Serial::where('type', 'material')
                                ->where('product_id', $am->material_id)
                                ->where('warehouse_id', $am->warehouse_id)
                                ->whereIn('serial_number', $serialNumbers)
                                ->update([
                                    'status' => 'inactive',
                                    'notes' => 'Used in Assembly ID: ' . $assembly->id,
                                ]);
                        }
                    } else {
                        Log::warning('WarehouseMaterial not found when decrementing on material export', [
                            'warehouse_id' => $am->warehouse_id,
                            'material_id' => $am->material_id,
                        ]);
                    }
                } catch (\Exception $decEx) {
                    Log::error('Error decrementing warehouse stock for material export', [
                        'error' => $decEx->getMessage(),
                        'warehouse_id' => $am->warehouse_id,
                        'material_id' => $am->material_id,
                    ]);
                }
            } else {
                Log::warning('Assembly material not found', [
                    'assembly_material_id' => $am->id,
                    'material_id' => $am->material_id
                ]);
            }
        }

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
                    'test_date' => now(), // Sử dụng thời gian hiện tại thay vì assembly->date
                    'notes' => 'Tự động tạo từ phiếu lắp ráp ' . $assembly->code,
                    'status' => 'pending',
                    'assembly_id' => $assembly->id, // Liên kết với phiếu lắp ráp
                    'created_at' => now(),
                    'updated_at' => now(),
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
                            'created_at' => now(),
                            'updated_at' => now(),
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
                        'created_at' => now(),
                        'updated_at' => now(),
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
            $getAll = $request->input('get_all', false);

            if (empty($formula)) {
                return response()->json([
                    'exists' => false,
                    'message' => 'No formula provided'
                ]);
            }

            // First, check against existing products in product_materials table
            $products = Product::with('materials')->get();
            $matchingProducts = [];

            foreach ($products as $product) {
                $productMaterials = $product->materials;

                // Check if the number of materials matches
                if (count($productMaterials) !== count($formula)) {
                    continue;
                }

                // Check if all materials and quantities match
                $matches = true;
                foreach ($formula as $formulaItem) {
                    // Handle both 'material_id' and 'id' fields
                    $materialId = $formulaItem['material_id'] ?? $formulaItem['id'] ?? null;
                    $quantity = $formulaItem['quantity'] ?? 0;

                    if (!$materialId) {
                        $matches = false;
                        break;
                    }

                    $productMaterial = $productMaterials->first(function ($material) use ($materialId) {
                        return (int)$material->pivot->material_id === (int)$materialId;
                    });

                    if (!$productMaterial || (int)$productMaterial->pivot->quantity !== (int)$quantity) {
                        $matches = false;
                        break;
                    }
                }

                if ($matches) {
                    $matchingProducts[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'code' => $product->code
                    ];

                    // If not requesting all matches, return the first one
                    if (!$getAll) {
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
            }

            // If requesting all matches and found some, return them
            if ($getAll && count($matchingProducts) > 0) {
                return response()->json([
                    'exists' => true,
                    'products' => $matchingProducts,
                    'message' => 'Found ' . count($matchingProducts) . ' matching products'
                ]);
            }

            // Second, check against existing assemblies (assembly_materials table)
            // Get all assemblies with their materials
            $assemblies = Assembly::with(['materials.material', 'products.product'])->get();

            foreach ($assemblies as $assembly) {
                $assemblyMaterials = $assembly->materials;

                // Check if the number of materials matches
                if (count($assemblyMaterials) !== count($formula)) {
                    continue;
                }

                // Check if all materials and quantities match
                $matches = true;
                foreach ($formula as $formulaItem) {
                    // Handle both 'material_id' and 'id' fields
                    $materialId = $formulaItem['material_id'] ?? $formulaItem['id'] ?? null;
                    $quantity = $formulaItem['quantity'] ?? 0;

                    if (!$materialId) {
                        $matches = false;
                        break;
                    }

                    $assemblyMaterial = $assemblyMaterials->first(function ($material) use ($materialId) {
                        return (int)$material->pivot->material_id === (int)$materialId;
                    });

                    if (!$assemblyMaterial || (int)$assemblyMaterial->pivot->quantity !== (int)$quantity) {
                        $matches = false;
                        break;
                    }
                }

                if ($matches) {
                    // Get the first product from this assembly for display
                    $firstProduct = $assembly->products->first();
                    $productName = $firstProduct ? $firstProduct->product->name : 'Unknown Product';

                    return response()->json([
                        'exists' => true,
                        'product' => [
                            'id' => $firstProduct ? $firstProduct->product->id : null,
                            'name' => $productName,
                            'code' => $firstProduct ? $firstProduct->product->code : 'N/A'
                        ],
                        'assembly' => [
                            'id' => $assembly->id,
                            'code' => $assembly->code
                        ],
                        'message' => 'Formula already exists in assembly: ' . $assembly->code . ' (Product: ' . $productName . ')'
                    ]);
                }
            }

            return response()->json([
                'exists' => false,
                'message' => 'Formula not found in existing products or assemblies'
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

            // Hard conflict check: any selected serial in this assembly already used by another approved/in_progress assembly
            $serialConflict = $this->checkSerialConflictAcrossAssemblies($assembly);
            if ($serialConflict['conflict'] === true) {
                DB::rollBack();
                if (request()->expectsJson()) {
                    return response()->json(['error' => $serialConflict['message']], 400);
                }
                return back()->withErrors(['error' => $serialConflict['message']])->withInput();
            }

            // Kiểm tra serial thành phẩm hợp lệ
            $productSerialValidation = $this->validateProductSerialsForApproval($assembly);
            if (!$productSerialValidation['valid']) {
                DB::rollBack();
                if (request()->expectsJson()) {
                    return response()->json(['error' => $productSerialValidation['message']], 400);
                }
                return back()->withErrors(['error' => $productSerialValidation['message']])->withInput();
            }

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

            // Do not create product dispatch for project purpose anymore
            $dispatch = null;

            // Create material export slip for both storage and project (xuất vật tư)
            if (in_array($assembly->purpose, ['storage', 'project'], true)) {
                // Use material export dispatch as the primary dispatch reference
                $dispatch = $this->createMaterialExportSlipForAssembly($assembly);
            }

            // Update assembly status to in_progress (đang thực hiện)
            $assembly->update(['status' => 'in_progress', 'updated_at' => now()]);

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
     * Kiểm tra xung đột serial của vật tư giữa các phiếu khác (đang thực hiện/đã hoàn thành)
     */
    private function checkSerialConflictAcrossAssemblies(Assembly $assembly): array
    {
        // Thu thập tất cả serial đã chọn trong phiếu hiện tại (theo vật tư)
        $currentSerialsByMaterialId = [];
        $materials = $assembly->materials()->with('material')->get();
        foreach ($materials as $am) {
            $serials = [];
            if (!empty($am->serial)) {
                $serials = array_filter(array_map('trim', explode(',', $am->serial)));
            } elseif (!empty($am->serial_id)) {
                $s = Serial::find($am->serial_id);
                if ($s) {
                    $serials = [trim($s->serial_number)];
                }
            }

            if (!empty($serials)) {
                $mid = (int) $am->material_id;
                if (!isset($currentSerialsByMaterialId[$mid])) {
                    $currentSerialsByMaterialId[$mid] = [];
                }
                $currentSerialsByMaterialId[$mid] = array_values(array_unique(array_merge($currentSerialsByMaterialId[$mid], $serials)));
            }
        }

        if (empty($currentSerialsByMaterialId)) {
            return ['conflict' => false, 'message' => ''];
        }

        // Cho phép tái sử dụng serial đã xuất hiện ở phiếu khác: bỏ chặn khi duyệt
        // Kiểm tra trùng với các phiếu khác (DISABLED)
        // Always allow: no conflict
        return ['conflict' => false, 'message' => ''];
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
     * Kiểm tra serial thành phẩm hợp lệ khi duyệt phiếu lắp ráp
     */
    private function validateProductSerialsForApproval(Assembly $assembly)
    {
        try {
            foreach ($assembly->products as $assemblyProduct) {
                if (empty($assemblyProduct->serials)) {
                    continue;
                }

                $serialArray = explode(',', $assemblyProduct->serials);
                $serialArray = array_map('trim', $serialArray);
                $serialArray = array_filter($serialArray);

                foreach ($serialArray as $serial) {
                    if (empty($serial)) continue;

                    // Kiểm tra serial có tồn tại và active không
                    $existingSerial = \App\Models\Serial::where('serial_number', $serial)
                        ->where('type', 'product')
                        ->first();

                    if ($existingSerial) {
                        if ($existingSerial->status === 'active') {
                            return [
                                'valid' => false,
                                'message' => "Serial '{$serial}' đã tồn tại và đang hoạt động trong tồn kho."
                            ];
                        }
                        // Nếu serial tồn tại nhưng inactive thì cho phép (có thể tái sử dụng)
                    }
                }
            }

            return ['valid' => true, 'message' => 'Tất cả serial thành phẩm đều hợp lệ.'];

        } catch (\Exception $e) {
            Log::error('Lỗi khi kiểm tra serial thành phẩm cho duyệt', [
                'assembly_id' => $assembly->id,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'message' => 'Có lỗi xảy ra khi kiểm tra serial thành phẩm: ' . $e->getMessage()
            ];
        }
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
            // Kiểm tra tồn kho theo đúng kho đã chọn cho từng vật tư
            $warehouseId = $assemblyMaterial->warehouse_id;

            // Lấy toàn bộ dòng tồn kho vật tư tại kho
            $wmRows = WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $material->id)
                ->where('item_type', 'material')
                ->get(['quantity', 'serial_number']);

            // Tổng tồn (bao gồm cả serial và không serial)
            $totalAvailable = (int) $wmRows->sum('quantity');

            // Tính tồn không-serial theo từng dòng: max(0, quantity - count(serial_number JSON))
            $nonSerializedAvailable = 0;
            $serializedCountFromWarehouseJson = 0;
            foreach ($wmRows as $wm) {
                $rowSerialCount = 0;
                if (!empty($wm->serial_number)) {
                    $arr = json_decode($wm->serial_number, true);
                    if (is_array($arr)) {
                        $rowSerialCount = count(array_filter(array_map('trim', $arr)));
                        $serializedCountFromWarehouseJson += $rowSerialCount;
                    }
                }
                $nonSerializedAvailable += max(0, ((int)$wm->quantity) - $rowSerialCount);
            }

            // Tổng số serial (nguồn 1: bảng serials)
            $totalSerializedInWarehouseFromSerials = (int) Serial::where('product_id', $material->id)
                ->where('type', 'material')
                ->where('warehouse_id', $warehouseId)
                ->where('status', 'active')
                ->count();

            // Dùng số serial lớn nhất cho mục đích tham khảo, nhưng ưu tiên non-serial tính theo từng dòng ở trên
            $totalSerializedInWarehouse = max($totalSerializedInWarehouseFromSerials, $serializedCountFromWarehouseJson);

            // Danh sách serial khả dụng để chọn (chưa bị giữ bởi assembly khác)
            // Include all active serials in this warehouse (do not exclude based on other assemblies)
            $availableSerialNumbers = Serial::where('product_id', $material->id)
                ->where('type', 'material')
                ->where('warehouse_id', $warehouseId)
                ->where('status', 'active')
                ->pluck('serial_number')
                ->map(function ($s) {
                    return trim($s);
                })
                ->filter()
                ->values()
                ->toArray();

            $wmSerialJsonList = WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $material->id)
                ->where('item_type', 'material')
                ->whereNotNull('serial_number')
                ->where('serial_number', '!=', '[]')
                ->where('serial_number', '!=', 'null')
                ->pluck('serial_number');
            foreach ($wmSerialJsonList as $serialJson) {
                $arr = json_decode($serialJson, true);
                if (is_array($arr)) {
                    foreach ($arr as $sn) {
                        $sn = trim($sn);
                        if ($sn === '') continue;
                        // Do not exclude serials used in other assemblies; show all present in warehouse
                        if (!in_array($sn, $availableSerialNumbers, true)) {
                            $availableSerialNumbers[] = $sn;
                        }
                    }
                }
            }

            $serializedAvailable = count($availableSerialNumbers);

            // Phân tách kiểm tra theo việc có chọn serial hay không
            $selectedSerialNumbers = [];
            if (!empty($assemblyMaterial->serial)) {
                $selectedSerialNumbers = array_filter(array_map('trim', explode(',', $assemblyMaterial->serial)));
            } elseif (!empty($assemblyMaterial->serial_id)) {
                $s = Serial::find($assemblyMaterial->serial_id);
                if ($s) {
                    $selectedSerialNumbers = [trim($s->serial_number)];
                }
            }

            $selectedSerialCount = count($selectedSerialNumbers);

            // 1) Luôn kiểm tra tổng tồn >= số lượng yêu cầu
            if ($totalAvailable < $requiredQuantity) {
                $errors[] = "Vật tư '{$material->name}' không đủ số lượng. Cần: {$requiredQuantity}, Có: {$totalAvailable}";
                continue;
            }

            if ($selectedSerialCount > 0) {
                // 2) Chọn serial: số serial chọn không được vượt quá số lượng yêu cầu
                if ($selectedSerialCount > $requiredQuantity) {
                    $errors[] = "Đã chọn quá số lượng serial cho vật tư '{$material->name}'. Cần: {$requiredQuantity}, Đã chọn: {$selectedSerialCount}";
                    continue;
                }

                // Mỗi serial đã chọn phải nằm trong danh sách serial khả dụng của kho
                foreach ($selectedSerialNumbers as $sn) {
                    if (!in_array($sn, $availableSerialNumbers, true)) {
                        $errors[] = "Serial '{$sn}' của vật tư '{$material->name}' không khả dụng trong kho";
                        continue 2;
                    }
                }

                // Đảm bảo đủ serial khả dụng cho số đã chọn
                if ($serializedAvailable < $selectedSerialCount) {
                    $errors[] = "Không đủ serial khả dụng cho vật tư '{$material->name}'. Cần serial: {$selectedSerialCount}, Khả dụng: {$serializedAvailable}";
                    continue;
                }

                // Phần số lượng còn lại sẽ lấy từ tồn không serial (đã tính chính xác theo từng dòng tồn)
                $nonSerialNeeded = $requiredQuantity - $selectedSerialCount;
                if ($nonSerialNeeded > 0 && $nonSerializedAvailable < $nonSerialNeeded) {
                    $errors[] = "Số lượng vật tư '{$material->name}' không có serial không đủ để thực hiện. Cần: {$nonSerialNeeded}, Có: {$nonSerializedAvailable}";
                    continue;
                }
            } else {
                // 3) Không chọn serial: toàn bộ lấy từ tồn không serial
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
        } catch (\Exception $e) {
            Log::error('Error updating related records for assembly', [
                'assembly_id' => $assembly->id,
                'error' => $e->getMessage()
            ]);
            // Không throw exception để không ảnh hưởng đến việc cập nhật phiếu lắp ráp chính
        }
    }

    /**
     * Lấy serial components từ assembly cho các products
     */
    public function getSerialComponents(Request $request)
    {
        try {
            $productIds = $request->input('product_ids', []);

            if (empty($productIds)) {
                return response()->json([
                    'success' => true,
                    'serial_components' => []
                ]);
            }

            $serialComponents = [];

            foreach ($productIds as $productId) {
                // Lấy serial từ assembly_products
                $assemblyProducts = DB::table('assembly_products')
                    ->where('product_id', $productId)
                    ->whereNotNull('serials')
                    ->where('serials', '!=', '')
                    ->pluck('serials')
                    ->toArray();

                // Lấy serial vật tư từ assembly_materials
                $assemblyMaterials = DB::table('assembly_materials')
                    ->join('assemblies', 'assembly_materials.assembly_id', '=', 'assemblies.id')
                    ->join('assembly_products', function ($join) use ($productId) {
                        $join->on('assembly_products.assembly_id', '=', 'assemblies.id')
                            ->where('assembly_products.product_id', '=', $productId);
                    })
                    ->where('assembly_materials.target_product_id', $productId)
                    ->whereNotNull('assembly_materials.serial')
                    ->where('assembly_materials.serial', '!=', '')
                    ->pluck('assembly_materials.serial')
                    ->toArray();

                // Kết hợp tất cả serial components
                $allSerials = array_merge($assemblyProducts, $assemblyMaterials);
                $serialComponents[$productId] = array_values(array_unique(array_filter($allSerials)));
            }

            return response()->json([
                'success' => true,
                'serial_components' => $serialComponents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy serial components: ' . $e->getMessage()
            ], 500);
        }
    }
}
