<?php

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Warehouse;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use App\Models\Warranty;
use App\Models\Project;
use App\Models\Rental;
use App\Helpers\ChangeLogHelper;
use App\Models\UserLog;
use App\Exports\DispatchExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Serial;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class DispatchController extends Controller
{
    /**
     * Display a listing of the dispatches.
     */
    public function index(Request $request)
    {
        $query = Dispatch::with(['creator', 'project', 'companyRepresentative'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strtolower($request->search);
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(dispatch_code) LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('LOWER(project_receiver) LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereRaw('LOWER(dispatch_note) LIKE ?', ['%' . $searchTerm . '%'])
                  ->orWhereHas('companyRepresentative', function($q) use ($searchTerm) {
                      $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%']);
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && !empty($request->type)) {
            $query->where('dispatch_type', $request->type);
        }

        if ($request->has('from_date') && !empty($request->from_date)) {
            $fromDate = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
            $query->where('dispatch_date', '>=', $fromDate);
        }

        if ($request->has('to_date') && !empty($request->to_date)) {
            $toDate = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();
            $query->where('dispatch_date', '<=', $toDate);
        }

        $dispatches = $query->paginate(10)->withQueryString();
        return view('inventory.index', compact('dispatches'));
    }

    /**
     * Show the form for creating a new dispatch.
     */
    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->get();

        $employees = Employee::orderBy('name')->get();

        // Lọc dự án theo quyền của nhân viên đang đăng nhập
        $projects = $this->getFilteredProjects();

        $nextDispatchCode = Dispatch::generateDispatchCode();

        return view('inventory.dispatch', compact('warehouses', 'employees', 'projects', 'nextDispatchCode'));
    }

    /**
     * Helper method để lọc dự án theo quyền của nhân viên
     */
    private function getFilteredProjects()
    {
        $user = Auth::guard('web')->user();
        $projects = collect();

        if ($user) {
            // Nếu là admin, lấy tất cả dự án
            if ($user->role === 'admin') {
                $projects = Project::with('customer')->get();
            } else {
                // Nếu có role_id, lọc theo dự án được gán cho role
                if ($user->role_id && $user->roleGroup) {
                    $projects = $user->roleGroup->projects()->with('customer')->get();
                } else {
                    // Nếu không có role, chỉ lấy dự án mà nhân viên phụ trách
                    $projects = Project::where('employee_id', $user->id)->with('customer')->get();
                }
            }
        } else {
            // Fallback: lấy tất cả dự án nếu không có user
            $projects = Project::with('customer')->get();
        }

        return $projects;
    }

    /**
     * Helper method để lọc hợp đồng cho thuê theo quyền của nhân viên
     */
    private function getFilteredRentals()
    {
        $user = Auth::guard('web')->user();
        $rentals = collect();

        if ($user) {
            // Nếu là admin, lấy tất cả hợp đồng cho thuê
            if ($user->role === 'admin') {
                $rentals = Rental::with('customer')->get();
            } else {
                // Nếu có role_id, lọc theo hợp đồng cho thuê được gán cho role
                if ($user->role_id && $user->roleGroup) {
                    $rentals = $user->roleGroup->rentals()->with('customer')->get();
                } else {
                    // Nếu không có role, chỉ lấy hợp đồng cho thuê mà nhân viên phụ trách
                    $rentals = Rental::where('employee_id', $user->id)->with('customer')->get();
                }
            }
        } else {
            // Fallback: lấy tất cả hợp đồng cho thuê nếu không có user
            $rentals = Rental::with('customer')->get();
        }

        return $rentals;
    }

    /**
     * Store a newly created dispatch in storage.
     */
    public function store(Request $request)
    {
        try {
            // Convert dispatch_date from dd/mm/yyyy to Y-m-d format
            if ($request->has('dispatch_date') && $request->dispatch_date) {
                try {
                    $convertedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->dispatch_date)->format('Y-m-d');
                    $request->merge(['dispatch_date' => $convertedDate]);
                } catch (\Exception $e) {
                    Log::error('Date conversion error: ' . $e->getMessage());
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Ngày xuất không hợp lệ. Vui lòng nhập theo định dạng dd/mm/yyyy.');
                }
            }

            $validationRules = [
                'dispatch_date' => 'required|date',
                'dispatch_type' => 'required|in:project,rental,warranty',
                'dispatch_detail' => 'required|in:all,contract,backup',
                'items' => 'required|array|min:1',
                'items.*.item_type' => 'required|in:material,product,good',
                'items.*.item_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.warehouse_id' => 'required|exists:warehouses,id',
                'items.*.category' => 'sometimes|in:contract,backup,general',
            ];
            
            // Validation for project_id depends on dispatch_type
            if ($request->dispatch_type === 'project') {
                $validationRules['project_id'] = 'required|exists:projects,id';
            } else if ($request->dispatch_type === 'rental') {
                // For rental type, project_id is required and must be a valid rental_id
                $validationRules['project_id'] = 'required|exists:rentals,id';
            } else {
                $validationRules['project_id'] = 'nullable|exists:projects,id';
            }

            // Validation for project_receiver depends on dispatch_type
            if ($request->dispatch_type === 'project' || $request->dispatch_type === 'warranty') {
                $validationRules['project_receiver'] = 'required|string';
            } elseif ($request->dispatch_type === 'rental') {
                // For rental, either project_receiver or rental_receiver should be provided
                $validationRules['project_receiver'] = 'required_without:rental_receiver|string';
                $validationRules['rental_receiver'] = 'required_without:project_receiver|string';
            }

            $request->validate($validationRules);

            // Additional validation based on dispatch_detail
            $this->validateItemsByDispatchDetail($request);
            Log::info('Dispatch detail validation passed');

            // For rental type, ensure project_receiver is filled from rental_receiver if needed
            if ($request->dispatch_type === 'rental' && !$request->project_receiver && $request->rental_receiver) {
                $request->merge(['project_receiver' => $request->rental_receiver]);
                Log::info('Synced rental_receiver to project_receiver:', ['project_receiver' => $request->project_receiver]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        DB::beginTransaction();

        try {
            // TEMPORARY: Disable stock check to allow dispatch creation
            Log::info('STOCK CHECK TEMPORARILY DISABLED - Creating dispatch without stock validation');
            Log::info('Items to be dispatched:', $request->items);

            // Kiểm tra tồn kho trước khi tạo phiếu xuất (tính tổng số lượng theo sản phẩm)
            Log::info('Starting stock check for items:', $request->items);
            $stockErrors = [];

            // Nhóm items theo sản phẩm và kho để tính tổng số lượng
            $groupedItems = [];
            if (isset($request->items) && is_array($request->items)) {
                foreach ($request->items as $index => $item) {
                    $key = $item['item_type'] . '_' . $item['item_id'] . '_' . $item['warehouse_id'];
                    if (!isset($groupedItems[$key])) {
                        $groupedItems[$key] = [
                            'item_type' => $item['item_type'],
                            'item_id' => $item['item_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'total_quantity' => 0,
                            'categories' => []
                        ];
                    }
                    $groupedItems[$key]['total_quantity'] += (int)$item['quantity'];
                    $groupedItems[$key]['categories'][] = $item['category'] ?? 'general';
                }

                // Kiểm tra tồn kho cho từng nhóm sản phẩm
                foreach ($groupedItems as $key => $groupedItem) {
                    Log::info("Checking stock for grouped item $key:", $groupedItem);
                    try {
                        $stockCheck = $this->checkItemStock(
                            $groupedItem['item_type'],
                            $groupedItem['item_id'],
                            $groupedItem['warehouse_id'],
                            $groupedItem['total_quantity']
                        );
                        Log::info("Stock check result for grouped item $key:", $stockCheck);

                        // Tổng tồn kho và serial chưa xuất trong kho
                        $totalInWarehouse = $stockCheck['current_stock'];
                        
                        // Lấy tất cả serial của item này trong kho
                        $allSerials = Serial::where('warehouse_id', $groupedItem['warehouse_id'])
                            ->where('type', $groupedItem['item_type'])
                            ->where('product_id', $groupedItem['item_id'])
                            ->pluck('serial_number')
                            ->toArray();
                        
                        // Lấy serial đã xuất từ dispatch_items của các phiếu approved
                        $dispatchedSerials = DispatchItem::whereHas('dispatch', function($q) {
                                $q->where('status', 'approved');
                            })
                            ->where('item_type', $groupedItem['item_type'])
                            ->where('item_id', $groupedItem['item_id'])
                            ->where('warehouse_id', $groupedItem['warehouse_id'])
                            ->get()
                            ->pluck('serial_numbers')
                            ->flatten()
                            ->filter(function($serial) {
                                return !empty($serial);
                            })
                            ->map(function($serialData) {
                                if (is_string($serialData)) {
                                    $decoded = json_decode($serialData, true);
                                    return is_array($decoded) ? $decoded : [$serialData];
                                }
                                return is_array($serialData) ? $serialData : [$serialData];
                            })
                            ->flatten()
                            ->unique()
                            ->toArray();
                        
                        // Serial chưa xuất = tất cả serial - serial đã xuất
                        $availableSerials = array_diff($allSerials, $dispatchedSerials);
                        $serialInWarehouse = count($availableSerials);
                        Log::info("Stock check result for grouped item $key:", $stockCheck);

                        if (!$stockCheck['sufficient']) {
                            // Thêm thông tin về categories để user hiểu rõ hơn
                            $categoriesText = implode(', ', array_unique($groupedItem['categories']));
                            $stockErrors[] = $stockCheck['message'] . " (Tổng từ: $categoriesText)";
                        }
                    } catch (\Exception $stockException) {
                        Log::error("Error checking stock for grouped item $key:", [
                            'item' => $groupedItem,
                            'error' => $stockException->getMessage(),
                            'trace' => $stockException->getTraceAsString()
                        ]);
                        // Skip stock check nếu có lỗi, để không block quá trình tạo phiếu
                    }
                }
            }

            if (!empty($stockErrors)) {
                Log::error('Stock errors found:', $stockErrors);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Không đủ tồn kho:\n' . implode('\n', $stockErrors));
            }

            Log::info('Stock check passed, creating dispatch');

            // KHÔNG trừ tồn kho khi tạo phiếu - chỉ trừ khi duyệt
            Log::info('Stock check passed, creating dispatch without reducing stock');

            // Create dispatch
            $dispatchData = [
                'dispatch_code' => Dispatch::generateDispatchCode(),
                'dispatch_date' => $request->dispatch_date,
                'dispatch_type' => $request->dispatch_type,
                'dispatch_detail' => $request->dispatch_detail,
                'project_id' => $request->project_id,
                'project_receiver' => $request->project_receiver,
                'warranty_period' => $request->warranty_period,
                'company_representative_id' => $request->company_representative_id,
                'dispatch_note' => $request->dispatch_note,
                'status' => 'pending',
                'created_by' => Auth::id() ?? 1, // Default to user ID 1 if not authenticated
            ];
            Log::info('Creating dispatch with data:', $dispatchData);

            $dispatch = Dispatch::create($dispatchData);
            Log::info('Dispatch created with ID:', ['id' => $dispatch->id]);

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

            // Create dispatch items
            Log::info('Creating dispatch items...');
            $firstDispatchItem = null;
            
            // Sắp xếp items: contract trước, backup sau để virtual serial được tạo đúng thứ tự
            $sortedItems = collect($request->items)->sortBy(function($item) {
                $category = $item['category'] ?? 'general';
                // contract = 0, backup = 1, general = 2
                return $category === 'contract' ? 0 : ($category === 'backup' ? 1 : 2);
            })->values()->toArray();
            
            foreach ($sortedItems as $index => $item) {
                Log::info("Creating dispatch item $index:", $item);
                Log::info("Raw serial_numbers for item $index:", [
                    'serial_numbers' => $item['serial_numbers'] ?? 'not set',
                    'type' => gettype($item['serial_numbers'] ?? null)
                ]);

                // Determine category based on dispatch_detail and item data
                $category = $this->determineItemCategory($dispatch->dispatch_detail, $item);
                Log::info("Determined category for item $index:", ['category' => $category]);

                // Handle serial numbers - can be JSON string or array
                $serialNumbers = [];
                if (isset($item['serial_numbers'])) {
                    if (is_string($item['serial_numbers'])) {
                        // If it's a JSON string, decode it
                        $decoded = json_decode($item['serial_numbers'], true);
                        $serialNumbers = is_array($decoded) ? $decoded : [];
                    } elseif (is_array($item['serial_numbers'])) {
                        // If it's already an array, use it directly
                        $serialNumbers = $item['serial_numbers'];
                    }
                    // Filter out empty values
                    $serialNumbers = array_filter($serialNumbers, function ($serial) {
                        return !empty(trim($serial));
                    });
                    // Re-index array after filtering
                    $serialNumbers = array_values($serialNumbers);
                }
                
                // Nếu quantity > số serial, nghĩa là có thiết bị không có serial
                // Không cần thêm gì vào serial_numbers, chỉ cần giữ nguyên quantity
                // Logic hiển thị sẽ dựa vào quantity để tạo đúng số bản ghi
                Log::info("Serial numbers processed for item $index:", [
                    'quantity' => $item['quantity'],
                    'serial_count' => count($serialNumbers),
                    'has_no_serial_items' => (int)$item['quantity'] > count($serialNumbers)
                ]);

                // Xử lý assembly_id và product_unit cho sản phẩm
                $assemblyId = null;
                $productUnit = null;
                
                if ($item['item_type'] === 'product') {
                    // Tìm assembly_id từ serial_numbers nếu có - gộp chung nhưng lưu đúng assembly_id và product_unit
                    if (!empty($serialNumbers) && !in_array('N/A', $serialNumbers) && !in_array('NA', $serialNumbers)) {
                        // Có serial cụ thể - tìm assembly_id và product_unit cho từng serial
                        $assemblyIds = [];
                        $productUnits = [];
                        
                        foreach ($serialNumbers as $serialIndex => $serial) {
                            $serialAssemblyId = null;
                            $serialProductUnit = null;
                            
                            // Tìm assembly_id cho serial cụ thể này
                            $assemblyProduct = \App\Models\AssemblyProduct::where('product_id', $item['item_id'])
                                ->where(function($q) use ($serial) {
                                    $q->where('serials', $serial)
                                      ->orWhereRaw('FIND_IN_SET(?, serials) > 0', [$serial]);
                                })
                                ->first();
                            
                            if ($assemblyProduct) {
                                $serialAssemblyId = $assemblyProduct->assembly_id;
                                
                                // Tìm product_unit từ assembly_products (ưu tiên) hoặc assembly_materials
                                $serialProductUnit = 0; // Default value
                                
                                if ($assemblyProduct->product_unit !== null) {
                                    // Sử dụng accessor của model để lấy product_unit đã được parse
                                    $productUnitValue = $assemblyProduct->product_unit;
                                    
                                    if (is_array($productUnitValue)) {
                                        // Tìm vị trí của serial trong assembly_products.serials
                                        $serialsStr = $assemblyProduct->serials ?? '';
                                        $serialsArray = array_map('trim', explode(',', $serialsStr));
                                        
                                        // Tìm index của serial trong mảng serials của assembly
                                        $serialPositionInAssembly = array_search(trim($serial), $serialsArray);
                                        
                                        if ($serialPositionInAssembly !== false && isset($productUnitValue[$serialPositionInAssembly])) {
                                            // Lấy product_unit tại vị trí tương ứng với serial trong assembly
                                            $serialProductUnit = $productUnitValue[$serialPositionInAssembly];
                                        } else {
                                            // Fallback: nếu không tìm thấy, dùng index đầu tiên hoặc 0
                                            $serialProductUnit = $productUnitValue[0] ?? 0;
                                        }
                                        
                                        Log::info('Using product_unit from assembly_products', [
                                            'assembly_id' => $serialAssemblyId,
                                            'serial' => $serial,
                                            'serials_array' => $serialsArray,
                                            'serial_position_in_assembly' => $serialPositionInAssembly,
                                            'product_unit_array' => $productUnitValue,
                                            'final_product_unit' => $serialProductUnit
                                        ]);
                                    } else {
                                        // Nếu là single value, dùng luôn
                                        $serialProductUnit = $productUnitValue;
                                        
                                        Log::info('Using product_unit from assembly_products (single value)', [
                                            'assembly_id' => $serialAssemblyId,
                                            'serial' => $serial,
                                            'final_product_unit' => $serialProductUnit
                                        ]);
                                    }
                                } else {
                                    // Fallback: tìm từ assembly_materials
                                    $assemblyMaterial = \App\Models\AssemblyMaterial::where('assembly_id', $serialAssemblyId)
                                    ->where('target_product_id', $item['item_id'])
                                    ->whereNotNull('product_unit')
                                    ->first();
                                if ($assemblyMaterial) {
                                        $serialProductUnit = $assemblyMaterial->product_unit;
                                        Log::info('Using product_unit from assembly_materials', [
                                            'assembly_id' => $serialAssemblyId,
                                            'product_unit' => $serialProductUnit
                                        ]);
                                    } else {
                                        Log::info('No product_unit found, using default 0', [
                                            'assembly_id' => $serialAssemblyId
                                        ]);
                                    }
                                }
                                
                                Log::info('Found assembly_id from serial for product', [
                                    'product_id' => $item['item_id'],
                                    'serial' => $serial,
                                    'serial_index' => $serialIndex,
                                    'assembly_id' => $serialAssemblyId,
                                    'product_unit' => $serialProductUnit
                                ]);
                            }
                            
                            $assemblyIds[] = $serialAssemblyId;
                            $productUnits[] = $serialProductUnit;
                        }
                        
                        // Tính số lượng N/A products cần thêm vào
                        $itemQuantity = (int) ($item['quantity'] ?? 1);
                        $naQuantity = $itemQuantity - count($serialNumbers);
                        
                        // Thêm assembly_id và product_unit cho N/A products
                        if ($naQuantity > 0) {
                            // 1) Kiểm tra tồn kho N/A tại warehouse của dòng này
                            $warehouseIdForItem = $item['warehouse_id'] ?? null;
                            if ($warehouseIdForItem) {
                                $wm = DB::table('warehouse_materials')
                                    ->where('warehouse_id', $warehouseIdForItem)
                                    ->where('material_id', $item['item_id'])
                                    ->where('item_type', 'product')
                                    ->first();
                                $totalQty = (int)($wm->quantity ?? 0);
                                $serialCnt = 0;
                                if (!empty($wm?->serial_number)) {
                                    $dec = json_decode($wm->serial_number, true);
                                    if (is_array($dec)) {
                                        $serialCnt = count(array_filter(array_map('trim', $dec)));
                                    } else {
                                        $serialCnt = count(array_filter(array_map('trim', explode(',', (string)$wm->serial_number))));
                                    }
                                }
                                $naStockAvail = max(0, $totalQty - $serialCnt);
                                // Nếu không có tồn N/A, không gán assembly cho phần N/A còn lại
                                if ($naStockAvail <= 0) {
                                    for ($i = 0; $i < $naQuantity; $i++) {
                                        $assemblyIds[] = null; // sẽ thành '' khi implode
                                        $productUnits[] = 0;
                                    }
                                    Log::info('No NA stock at warehouse for remaining NA units, set assembly_id=0', [
                                        'product_id' => $item['item_id'],
                                        'warehouse_id' => $warehouseIdForItem,
                                        'na_quantity' => $naQuantity,
                                        'na_stock_available' => $naStockAvail
                                    ]);
                                    // Skip assembly selection for NA part
                                    goto after_na_assignment_store;
                                }
                            }
                            $usedSerialCount = count($serialNumbers);
                            // Đếm số serial đã gán theo từng assembly trong item hiện tại
                            $serialCountByAssembly = [];
                            for ($i = 0; $i < $usedSerialCount; $i++) {
                                $aid = $assemblyIds[$i] ?? null;
                                if ($aid !== null && $aid !== '') {
                                    $serialCountByAssembly[$aid] = ($serialCountByAssembly[$aid] ?? 0) + 1;
                                }
                            }
                            
                            // Lấy các assembly_id có sẵn cho product_id này
                            // Ưu tiên assemblies không có serial, sau đó mới đến assemblies có serial nhưng có slot N/A
                            $warehouseIdForItem = $item['warehouse_id'] ?? null;
                            $productIdForItem = (int)$item['item_id'];
                            $availableAssemblies = \App\Models\AssemblyProduct::where('product_id', $productIdForItem)
                                ->orderBy('assembly_id')
                                ->get()
                                ->filter(function($assembly) use ($warehouseIdForItem, $productIdForItem) {
                                    // Chỉ nhận assembly có sức chứa N/A còn lại ở đúng kho xuất theo bản ghi testing
                                    $capacity = $this->getNaCapacityForAssemblyProduct((int)$assembly->assembly_id, $productIdForItem, $warehouseIdForItem, true);
                                    if ($capacity <= 0) { return false; }
                                    return true;
                                })
                                ->sortBy(function($assembly) {
                                    // Ưu tiên assemblies không có serial trước
                                    $hasSerials = $assembly->serials && 
                                                 $assembly->serials !== '' && 
                                                 $assembly->serials !== 'N/A' && 
                                                 $assembly->serials !== 'NA';
                                    return $hasSerials ? 1 : 0; // 0 = không có serial (ưu tiên), 1 = có serial
                                })
                                ->values(); // Reset keys
                            
                            Log::info('Available assemblies for N/A products', [
                                        'product_id' => $item['item_id'],
                                'assemblies_count' => $availableAssemblies->count(),
                                'assemblies' => $availableAssemblies->pluck('assembly_id')->toArray()
                            ]);
                            
                            $assemblyIndex = 0;
                            $currentProductUnitIndex = $usedSerialCount; // Bắt đầu từ product_unit tiếp theo sau serial
                            
                            for ($q = 0; $q < $naQuantity; $q++) {
                                if ($assemblyIndex < $availableAssemblies->count()) {
                                    $assembly = $availableAssemblies[$assemblyIndex];
                                    $assemblyId = $assembly->assembly_id;
                                    
                                    // Parse product_unit để lấy unit phù hợp
                                    $productUnitValue = $assembly->product_unit;
                                    $assemblyProductUnits = [];
                                    
                                    if (is_string($productUnitValue)) {
                                        $assemblyProductUnits = json_decode($productUnitValue, true) ?: [$productUnitValue];
                                    } elseif (is_array($productUnitValue)) {
                                        $assemblyProductUnits = $productUnitValue;
                                } else {
                                        $assemblyProductUnits = [$productUnitValue];
                                    }
                                    
                                    // Tìm product_unit phù hợp với index hiện tại
                                    $targetProductUnit = null;
                                    
                                if ($assembly->serials && $assembly->serials !== 'N/A' && $assembly->serials !== 'NA') {
                                    // Assembly có serial trong dữ liệu lắp ráp, chọn unit tiếp theo sau SỐ SERIAL của assembly này
                                    $assemblySerialsStr = is_string($assembly->serials) ? $assembly->serials : '';
                                    $parts = preg_split('/[\s,;|\/]+/', $assemblySerialsStr, -1, PREG_SPLIT_NO_EMPTY);
                                    $assemblySerialCount = is_array($parts) ? count($parts) : 1; // mặc định 1 nếu có chuỗi
                                    if ($assemblySerialCount < count($assemblyProductUnits)) {
                                        $targetProductUnit = $assemblyProductUnits[$assemblySerialCount];
                                    }
                                } else {
                                        // Assembly không có serial, lấy product_unit theo thứ tự từ đầu
                                        // Ví dụ: assembly_id = 42 có [0], N/A sẽ dùng 0
                                        $targetProductUnit = $assemblyProductUnits[0] ?? 0;
                                    }
                                    
                                    // Nếu không tìm được product_unit phù hợp, chuyển sang assembly tiếp theo
                                    if ($targetProductUnit === null) {
                                        $assemblyIndex++;
                                        if ($assemblyIndex < $availableAssemblies->count()) {
                                            $assembly = $availableAssemblies[$assemblyIndex];
                                            $assemblyId = $assembly->assembly_id;
                                            
                                            $productUnitValue = $assembly->product_unit;
                                            if (is_string($productUnitValue)) {
                                                $assemblyProductUnits = json_decode($productUnitValue, true) ?: [$productUnitValue];
                                            } elseif (is_array($productUnitValue)) {
                                                $assemblyProductUnits = $productUnitValue;
                                            } else {
                                                $assemblyProductUnits = [$productUnitValue];
                                            }
                                            
                                            $targetProductUnit = $assemblyProductUnits[0] ?? 0;
                                        }
                                    }
                                    
                                    $assemblyIds[] = $assemblyId;
                                    $productUnits[] = $targetProductUnit;
                                    
                                    Log::info('Assigned assembly and product unit for N/A product', [
                                        'na_index' => $q,
                                'assembly_id' => $assemblyId,
                                        'product_unit' => $targetProductUnit,
                                        'used_serial_count' => $usedSerialCount,
                                        'assembly_serials' => $assembly->serials,
                                        'assembly_product_units' => $assemblyProductUnits
                                    ]);
                                    
                                    // Chuyển sang assembly tiếp theo sau khi đã gán
                                    $assemblyIndex++;
                                } else {
                                    $assemblyIds[] = null;
                                    $productUnits[] = null;
                                    Log::warning('No more assemblies available for N/A product', [
                                        'na_index' => $q,
                                        'assembly_index' => $assemblyIndex
                                    ]);
                                }
                            }
                        }
                        
                        // Gộp assembly_id và product_unit thành chuỗi phân tách bằng dấu phẩy
                        // Không dùng array_filter để tránh loại bỏ giá trị 0 và null
                        $assemblyId = implode(',', array_map(function($val) { return $val !== null ? $val : ''; }, $assemblyIds));
                        $productUnit = implode(',', array_map(function($val) { return $val !== null ? $val : 0; }, $productUnits));
                        
                        Log::info('Combined assembly data for dispatch item', [
                            'product_id' => $item['item_id'],
                            'assembly_ids_array' => $assemblyIds,
                            'product_units_array' => $productUnits,
                            'assembly_ids_string' => $assemblyId,
                            'product_units_string' => $productUnit,
                            'serial_numbers' => $serialNumbers,
                            'na_quantity' => $naQuantity
                        ]);
                    } elseif (empty($serialNumbers) || in_array('N/A', $serialNumbers) || in_array('NA', $serialNumbers)) {
                        // Xử lý N/A products với logic ưu tiên serial trước - gộp chung 1 hàng
                        $itemQuantity = (int) ($item['quantity'] ?? 1);
                        
                        // Tính số lượng serial đã sử dụng trong dispatch này để dành product_unit
                        $usedSerialCount = 0;
                        
                        // Đếm số serial đã sử dụng cho cùng product_id trong dispatch này
                        foreach ($request->items as $otherItem) {
                            if ($otherItem['item_type'] === 'product' && 
                                $otherItem['item_id'] == $item['item_id'] && 
                                !empty($otherItem['serial_numbers'])) {
                                
                                // Normalize serial_numbers to array
                                $otherSerialNumbers = [];
                                if (is_string($otherItem['serial_numbers'])) {
                                    $decoded = json_decode($otherItem['serial_numbers'], true);
                                    $otherSerialNumbers = is_array($decoded) ? $decoded : [];
                                } elseif (is_array($otherItem['serial_numbers'])) {
                                    $otherSerialNumbers = $otherItem['serial_numbers'];
                                }
                                
                                if (!empty($otherSerialNumbers) && 
                                    !in_array('N/A', $otherSerialNumbers) && 
                                    !in_array('NA', $otherSerialNumbers)) {
                                    $usedSerialCount += count($otherSerialNumbers);
                                }
                            }
                        }
                        
                        Log::info('Processing N/A products with serial priority', [
                            'product_id' => $item['item_id'],
                            'quantity' => $itemQuantity,
                            'used_serial_count' => $usedSerialCount
                        ]);
                        
                        // Kiểm tra tồn kho N/A trong warehouse trước
                        $warehouseId = $item['warehouse_id'] ?? null;
                        $hasNAStock = false;
                        if ($warehouseId) {
                            $warehouseMaterial = DB::table('warehouse_materials')
                                ->where('warehouse_id', $warehouseId)
                                ->where('material_id', $item['item_id'])
                                ->where('item_type', 'product')
                                ->first();
                            
                            if ($warehouseMaterial) {
                                $totalQuantity = (int) ($warehouseMaterial->quantity ?? 0);
                                $serialCount = 0;
                                
                                // Đếm số serial trong kho
                                if (!empty($warehouseMaterial->serial_number)) {
                                    $decoded = json_decode($warehouseMaterial->serial_number, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $serialCount = count(array_filter(array_map('trim', $decoded)));
                                    } else {
                                        $serialParts = explode(',', (string) $warehouseMaterial->serial_number);
                                        $serialCount = count(array_filter(array_map('trim', $serialParts)));
                                    }
                                }
                                
                                // Tồn N/A = tổng số lượng - số serial
                                $naStock = max(0, $totalQuantity - $serialCount);
                                $hasNAStock = $naStock > 0;
                                
                                Log::info('Warehouse N/A stock check', [
                                    'warehouse_id' => $warehouseId,
                                    'product_id' => $item['item_id'],
                                    'total_quantity' => $totalQuantity,
                                    'serial_count' => $serialCount,
                                    'na_stock' => $naStock,
                                    'has_na_stock' => $hasNAStock
                                ]);
                            }
                        }
                        
                        // Nếu không có tồn N/A trong kho, gán assembly_id = 0
                        if (!$hasNAStock) {
                            Log::info('No N/A stock in warehouse, setting assembly_id to 0', [
                                'product_id' => $item['item_id'],
                                'warehouse_id' => $warehouseId,
                                'quantity' => $itemQuantity
                            ]);
                            
                            $assemblyId = '0';
                            $productUnit = implode(',', array_fill(0, $itemQuantity, '0'));
                            
                            // Bỏ qua logic tìm assembly, gán trực tiếp
                        } else {
                            // Có tồn N/A, tiếp tục logic tìm assembly như cũ
                        
                        // Loại trừ các cặp assembly_id:product_unit đã dùng trong phiếu xuất đã duyệt
                        $approvedPairs = [];
                        $approvedItems = DB::table('dispatch_items as di')
                            ->join('dispatches as d', 'd.id', '=', 'di.dispatch_id')
                            ->where('di.item_type', 'product')
                            ->where('di.item_id', $item['item_id'])
                            ->where('d.status', 'approved')
                            ->select('di.assembly_id', 'di.product_unit')
                            ->get();
                        foreach ($approvedItems as $row) {
                            $aIds = array_map('trim', explode(',', (string) $row->assembly_id));
                            $pUnitsRaw = $row->product_unit;
                            // Parse product_unit (có thể là JSON string hoặc comma-separated string)
                            $pUnits = [];
                            if (is_string($pUnitsRaw)) {
                                $decoded = json_decode($pUnitsRaw, true);
                                if (is_array($decoded)) {
                                    $pUnits = array_map('intval', $decoded);
                                } else {
                                    $pUnits = array_map('intval', array_map('trim', explode(',', $pUnitsRaw)));
                                }
                            } elseif (is_array($pUnitsRaw)) {
                                $pUnits = array_map('intval', $pUnitsRaw);
                            }
                            
                            $len = min(count($aIds), count($pUnits));
                            for ($ii = 0; $ii < $len; $ii++) {
                                if ($aIds[$ii] !== '' && isset($pUnits[$ii])) {
                                    $approvedPairs[$aIds[$ii] . ':' . $pUnits[$ii]] = true;
                                }
                            }
                        }
                        
                        // Lấy các assembly_id có sẵn cho product_id này
                        // Ưu tiên assemblies không có serial, sau đó mới đến assemblies có serial nhưng có slot N/A còn trống (chưa dùng trong phiếu đã duyệt)
                        // QUAN TRỌNG: Chỉ lấy assemblies thuộc kho xuất (warehouse_id của item)
                        $warehouseId = $item['warehouse_id'] ?? null;
                        $availableAssemblies = \App\Models\AssemblyProduct::where('product_id', $item['item_id'])
                            ->with('assembly')
                            ->orderBy('assembly_id')
                            ->get()
                            ->filter(function($assemblyProduct) use ($warehouseId, $item) {
                                // Sức chứa NA thực sự còn lại theo Testing nhập kho đúng kho xuất
                                $capacity = $this->getNaCapacityForAssemblyProduct((int)$assemblyProduct->assembly_id, (int)$item['item_id'], $warehouseId, true);
                                return $capacity > 0;
                            })
                            ->sortBy(function($assemblyProduct) {
                                // Ưu tiên assemblies không có serial trước
                                $hasSerials = $assemblyProduct->serials && 
                                             $assemblyProduct->serials !== '' && 
                                             $assemblyProduct->serials !== 'N/A' && 
                                             $assemblyProduct->serials !== 'NA';
                                return $hasSerials ? 1 : 0; // 0 = không có serial (ưu tiên), 1 = có serial
                            })
                            ->values(); // Reset keys
                        
                        // approvedPairs đã được lấy ở trên, sử dụng lại

                        // Tính sức chứa N/A còn lại theo từng assembly (để không vượt quá slot)
                        $assemblyRemaining = [];
                        foreach ($availableAssemblies as $asm) {
                            $units = [];
                            if (is_string($asm->product_unit)) {
                                $units = json_decode($asm->product_unit, true) ?: [];
                            } elseif (is_array($asm->product_unit)) {
                                $units = $asm->product_unit;
                            } elseif ($asm->product_unit !== null) {
                                $units = [$asm->product_unit];
                            }

                            $hasSerials = $asm->serials && $asm->serials !== '' && $asm->serials !== 'N/A' && $asm->serials !== 'NA';
                            $serialCount = 0;
                            if ($hasSerials) {
                                $partsTmp = preg_split('/[\s,;|\/]+/', (string)$asm->serials, -1, PREG_SPLIT_NO_EMPTY);
                                $serialCount = is_array($partsTmp) ? count($partsTmp) : 1;
                            }
                            $capacity = $hasSerials ? max(0, count($units) - $serialCount) : (count($units) > 0 ? count($units) : 1);

                            // Trừ các slot đã dùng ở phiếu đã duyệt cho assembly này
                            if ($capacity > 0) {
                                $aidStr = (string)$asm->assembly_id;
                                $used = 0;
                                // Tính theo số cặp đã dùng thuộc NA slots của assembly
                                foreach ($units as $idx => $u) {
                                    if ($hasSerials && $idx < $serialCount) continue; // chỉ NA slot
                                    $key = $aidStr . ':' . (int)$u;
                                    if (isset($approvedPairs[$key])) $used++;
                                }
                                $capacity = max(0, $capacity - $used);
                            }
                            $assemblyRemaining[(int)$asm->assembly_id] = $capacity;
                        }
                        
                        $assemblyIds = [];
                        $productUnits = [];
                        $localUsedPairs = [];
                        
                        $needed = $itemQuantity;
                        foreach ($availableAssemblies as $assembly) {
                            if ($needed <= 0) { break; }
                            $assemblyId = $assembly->assembly_id;
                            if (($assemblyRemaining[(int)$assemblyId] ?? 0) <= 0) { continue; }
                            // Parse product_unit để lấy unit phù hợp
                            $productUnitValue = $assembly->product_unit;
                            $assemblyProductUnits = [];
                            if (is_string($productUnitValue)) {
                                $assemblyProductUnits = json_decode($productUnitValue, true) ?: [$productUnitValue];
                            } elseif (is_array($productUnitValue)) {
                                $assemblyProductUnits = $productUnitValue;
                            } else {
                                $assemblyProductUnits = [$productUnitValue];
                            }
                            // Offset theo số serial tồn tại trong assembly này
                            $assemblySerialsStr = is_string($assembly->serials) ? $assembly->serials : '';
                            $parts = preg_split('/[\s,;|\/]+/', $assemblySerialsStr, -1, PREG_SPLIT_NO_EMPTY);
                            $assemblySerialCount = ($assembly->serials && $assembly->serials !== 'N/A' && $assembly->serials !== 'NA') ? (is_array($parts) ? count($parts) : 1) : 0;
                            
                            for ($unitIdx = $assemblySerialCount; $unitIdx < count($assemblyProductUnits) && $needed > 0; $unitIdx++) {
                                $candidateUnit = $assemblyProductUnits[$unitIdx];
                                $key = $assemblyId . ':' . $candidateUnit;
                                if (!isset($approvedPairs[$key]) && !isset($localUsedPairs[$key])) {
                                    $assemblyIds[] = $assemblyId;
                                    $productUnits[] = $candidateUnit;
                                    $localUsedPairs[$key] = true;
                                    $needed--;
                                    if (isset($assemblyRemaining[(int)$assemblyId])) {
                                        $assemblyRemaining[(int)$assemblyId] = max(0, $assemblyRemaining[(int)$assemblyId] - 1);
                                    }
                                }
                            }
                        }
                        // Nếu vẫn thiếu, fill phần còn lại để giữ đúng số lượng
                        while ($needed > 0) {
                            $assemblyIds[] = null;
                            $productUnits[] = null;
                            $needed--;
                        }
                        
                        after_na_assignment_store:
                        // Gộp assembly_id và product_unit thành chuỗi phân tách bằng dấu phẩy
                        $assemblyId = implode(',', array_map(function($val) { return $val !== null ? $val : ''; }, $assemblyIds));
                        $productUnit = implode(',', array_map(function($val) { return $val !== null ? $val : 0; }, $productUnits));
                        
                        Log::info('Combined assembly data for N/A dispatch item', [
                            'product_id' => $item['item_id'],
                            'assembly_ids_array' => $assemblyIds,
                            'product_units_array' => $productUnits,
                            'assembly_ids_string' => $assemblyId,
                            'product_units_string' => $productUnit,
                            'quantity' => $itemQuantity
                        ]);
                        } // End else: có tồn N/A, đã tìm assembly
                    }
                    
                    // Nếu vẫn chưa có assembly_id, thử tìm từ project context
                    if (!$assemblyId) {
                        $unitInfo = $this->getNextAvailableProductUnitForDispatch($item['item_id'], $dispatch->project_id ?? null);
                        if ($unitInfo) {
                            $assemblyId = $unitInfo['assembly_id'];
                            $productUnit = $unitInfo['product_unit'];
                            Log::info('Fallback: Found assembly_id from project context', [
                                'product_id' => $item['item_id'],
                                'assembly_id' => $assemblyId,
                                'product_unit' => $productUnit
                            ]);
                        }
                    }
                }

                // Tạo virtual serial cho thiết bị không có serial
                // SỬ DỤNG RANDOM SUFFIX ĐỂ ĐẢM BẢO DUY NHẤT TOÀN CỤC
                // Giải thích: Thay vì N/A-0, N/A-1 (dễ trùng giữa các project),
                // ta dùng N/A-A1B2C3 (random) để tránh xung đột khi cùng thiết bị
                // xuất hiện ở nhiều project khác nhau.
                $quantity = (int)$item['quantity'];
                $serialCount = count($serialNumbers);
                
                if ($quantity > $serialCount) {
                    // Cần tạo virtual serial với random suffix (duy nhất toàn cục)
                    $needCount = $quantity - $serialCount;
                    
                    // Sử dụng SerialHelper để tạo virtual serial duy nhất
                    $newVirtualSerials = \App\Helpers\SerialHelper::generateUniqueVirtualSerials($needCount);
                    $serialNumbers = array_merge($serialNumbers, $newVirtualSerials);
                    
                    Log::info("Added virtual serials for item $index:", [
                        'quantity' => $quantity,
                        'real_serial_count' => $serialCount,
                        'virtual_serial_count' => $needCount,
                        'new_virtual_serials' => $newVirtualSerials,
                        'final_serials' => $serialNumbers
                    ]);
                }
                
                $dispatchItemData = [
                    'dispatch_id' => $dispatch->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'warehouse_id' => $item['warehouse_id'],
                    'category' => $category,
                    'serial_numbers' => $serialNumbers,
                    'assembly_id' => $assemblyId,
                    'product_unit' => $productUnit,
                    'notes' => $item['notes'] ?? null,
                ];
                Log::info("Creating dispatch item with data:", $dispatchItemData);
                Log::info("Final serial_numbers for item $index:", [
                    'serial_numbers' => $serialNumbers,
                    'count' => count($serialNumbers)
                ]);

                $dispatchItem = DispatchItem::create($dispatchItemData);
                Log::info("DispatchItem created with ID:", ['id' => $dispatchItem->id]);

                // Store first dispatch item for warranty creation reference
                if ($firstDispatchItem === null) {
                    $firstDispatchItem = $dispatchItem;
                }
            }

            // KHÔNG tạo bảo hành khi tạo phiếu - chỉ tạo khi duyệt
            Log::info('Skipping warranty creation - will create when dispatch is approved');

            DB::commit();
            Log::info('Transaction committed successfully');

            // Count total warranties created
            $totalWarranties = $dispatch->warranties()->count();
            Log::info('Total warranties created:', ['count' => $totalWarranties]);

            Log::info('=== DISPATCH STORE COMPLETED SUCCESSFULLY ===');
            return redirect()->route('inventory.index')
                ->with('success', 'Phiếu xuất kho đã được tạo thành công.' . '. Đã tạo ' . $totalWarranties . ' bảo hành điện tử.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('=== DISPATCH STORE FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo phiếu xuất: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified dispatch.
     */
    public function show(Dispatch $dispatch)
    {
        $dispatch->load(['project', 'creator', 'companyRepresentative', 'items.material', 'items.product', 'items.good', 'items.warehouse']);

        // Ghi nhật ký xem chi tiết phiếu xuất
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'dispatches',
                'Xem chi tiết phiếu xuất: ' . $dispatch->dispatch_code,
                null,
                $dispatch->toArray()
            );
        }

        return view('inventory.dispatch_detail', compact('dispatch'));
    }

    /**
     * Show the form for editing the specified dispatch.
     */
    public function edit(Dispatch $dispatch)
    {
        if (in_array($dispatch->status, ['completed', 'cancelled'])) {
            return redirect()->route('inventory.dispatch.show', $dispatch->id)
                ->with('error', 'Không thể chỉnh sửa phiếu xuất đã hoàn thành hoặc đã hủy.');
        }

        $warehouses = Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->get();

        $employees = Employee::orderBy('name')->get();

        // Lọc dự án theo quyền của nhân viên đang đăng nhập
        $projects = $this->getFilteredProjects();
        
        // Lọc hợp đồng cho thuê theo quyền của nhân viên đang đăng nhập
        $rentals = $this->getFilteredRentals();

        $dispatch->load(['items.material', 'items.product', 'items.good', 'items.warehouse', 'project', 'rental', 'companyRepresentative']);

        return view('inventory.dispatch_edit', compact('dispatch', 'warehouses', 'employees', 'projects', 'rentals'));
    }

    /**
     * Update the specified dispatch in storage.
     */
    public function update(Request $request, Dispatch $dispatch)
    {
        
        if (in_array($dispatch->status, ['completed', 'cancelled'])) {
            return redirect()->route('inventory.dispatch.show', $dispatch->id)
                ->with('error', 'Không thể cập nhật phiếu xuất đã hoàn thành hoặc đã hủy.');
        }

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $dispatch->toArray();

        // Convert dispatch_date from dd/mm/yyyy to Y-m-d format
        if ($request->has('dispatch_date') && $request->dispatch_date) {
            try {
                $convertedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->dispatch_date)->format('Y-m-d');
                $request->merge(['dispatch_date' => $convertedDate]);
            } catch (\Exception $e) {
                Log::error('Date conversion error: ' . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ngày xuất không hợp lệ. Vui lòng nhập theo định dạng dd/mm/yyyy.');
            }
        }

        // Validation rules depend on dispatch status
        if ($dispatch->status === 'pending') {
            // Full editing for pending dispatch
            // Build validation rules depending on dispatch_type (project_id can be a Project ID or Rental ID)
            $rules = [
                'dispatch_date' => 'required|date',
                'dispatch_type' => 'required|in:project,rental,warranty',
                'dispatch_detail' => 'required|in:all,contract,backup',
                'project_receiver' => 'required|string',
                'company_representative_id' => 'nullable|exists:employees,id',
                'dispatch_note' => 'nullable|string',
                'contract_items.*' => 'nullable|array',
                'backup_items.*' => 'nullable|array',
                'general_items.*' => 'nullable|array',
            ];

            if ($request->dispatch_type === 'project') {
                $rules['project_id'] = 'required|exists:projects,id';
            } elseif ($request->dispatch_type === 'rental') {
                $rules['project_id'] = 'required|exists:rentals,id';
            } else { // warranty: allow either project or rental id or empty
                $rules['project_id'] = [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        if (empty($value)) return; // allow empty
                        $existsInProjects = DB::table('projects')->where('id', $value)->exists();
                        $existsInRentals = DB::table('rentals')->where('id', $value)->exists();
                        if (!$existsInProjects && !$existsInRentals) {
                            $fail('The selected ' . str_replace('_', ' ', $attribute) . ' is invalid.');
                        }
                    }
                ];
            }

            $request->validate($rules);

            // Additional validation for dispatch detail and items
            $this->validateUpdateItemsByDispatchDetail($request, $dispatch);
        } else {
            // Limited editing for approved dispatch
            $request->validate([
                'dispatch_date' => 'required|date',
                'company_representative_id' => 'nullable|exists:employees,id',
                'dispatch_note' => 'nullable|string',
                // Serial numbers validation for approved
                'contract_items.*' => 'nullable|array',
                'backup_items.*' => 'nullable|array',
                'general_items.*' => 'nullable|array',
            ]);
        }

        DB::beginTransaction();

        try {
            if ($dispatch->status === 'pending') {
                // Full update for pending dispatch
                $dispatch->update([
                    'dispatch_date' => $request->dispatch_date,
                    'dispatch_type' => $request->dispatch_type,
                    'dispatch_detail' => $request->dispatch_detail,
                    'project_id' => $request->project_id,
                    'project_receiver' => $request->project_receiver,
                    'warranty_period' => $request->warranty_period,
                    'company_representative_id' => $request->company_representative_id,
                    'dispatch_note' => $request->dispatch_note,
                ]);

                // Process items for pending dispatch
                $this->updateDispatchItemsPending($request, $dispatch);
            } else {
                // Limited update for approved dispatch
                $dispatch->update([
                    'dispatch_date' => $request->dispatch_date,
                    'company_representative_id' => $request->company_representative_id,
                    'dispatch_note' => $request->dispatch_note,
                ]);

                // Only update serial numbers for approved dispatch
                $this->updateDispatchItemsApproved($request, $dispatch);
            }

            DB::commit();

            // Ghi nhật ký cập nhật phiếu xuất
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'dispatches',
                    'Cập nhật phiếu xuất: ' . $dispatch->dispatch_code,
                    $oldData,
                    $dispatch->toArray()
                );
            }

            return redirect()->route('inventory.dispatch.show', $dispatch->id)
                ->with('success', 'Phiếu xuất kho đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật phiếu xuất: ' . $e->getMessage());
        }
    }

    /**
     * Update dispatch items for pending dispatch (full editing)
     */
    private function updateDispatchItemsPending(Request $request, Dispatch $dispatch)
    {
        // Collect all items from different categories (existing + newly added)
        $allItems = [];

        // Process existing contract items (by dispatch item ID)
        if ($request->has('contract_items')) {
            foreach ($request->contract_items as $itemId => $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $serialNumbers = [];
                    if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                        $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                            return !empty(trim($serial));
                        });
                    }

                    $allItems[] = [
                        'item_type' => $itemData['item_type'],
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'warehouse_id' => $itemData['warehouse_id'],
                        'category' => $itemData['category'] ?? 'contract',
                        'serial_numbers' => $serialNumbers,
                        'assembly_id' => $itemData['assembly_id'] ?? null,
                        'product_unit' => $itemData['product_unit'] ?? null,
                    ];
                }
            }
        }

        // Process newly added items from dropdowns (items array)
        if ($request->has('items')) {
            foreach ($request->items as $itemKey => $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    // Handle serial numbers - can be JSON string or array
                    $serialNumbers = [];
                    if (isset($itemData['serial_numbers'])) {
                        if (is_string($itemData['serial_numbers'])) {
                            // If it's a JSON string, decode it
                            $decoded = json_decode($itemData['serial_numbers'], true);
                            $serialNumbers = is_array($decoded) ? $decoded : [];
                        } elseif (is_array($itemData['serial_numbers'])) {
                            // If it's already an array, use it directly
                            $serialNumbers = $itemData['serial_numbers'];
                        }
                        // Filter out empty values
                        $serialNumbers = array_filter($serialNumbers, function ($serial) {
                            return !empty(trim($serial));
                        });
                    }

                    $allItems[] = [
                        'item_type' => $itemData['item_type'],
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'warehouse_id' => $itemData['warehouse_id'],
                        'category' => $itemData['category'] ?? 'general',
                        'serial_numbers' => $serialNumbers,
                        'assembly_id' => $itemData['assembly_id'] ?? null,
                        'product_unit' => $itemData['product_unit'] ?? null,
                    ];
                }
            }
        }

        // Process backup items
        if ($request->has('backup_items')) {
            foreach ($request->backup_items as $itemId => $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $serialNumbers = [];
                    if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                        $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                            return !empty(trim($serial));
                        });
                    }

                    $allItems[] = [
                        'item_type' => $itemData['item_type'],
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'warehouse_id' => $itemData['warehouse_id'],
                        'category' => $itemData['category'] ?? 'backup',
                        'serial_numbers' => $serialNumbers,
                        'assembly_id' => $itemData['assembly_id'] ?? null,
                        'product_unit' => $itemData['product_unit'] ?? null,
                    ];
                }
            }
        }

        // Process general items
        if ($request->has('general_items')) {
            foreach ($request->general_items as $itemId => $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $serialNumbers = [];
                    if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                        $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                            return !empty(trim($serial));
                        });
                    }

                    $allItems[] = [
                        'item_type' => $itemData['item_type'],
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'warehouse_id' => $itemData['warehouse_id'],
                        'category' => $itemData['category'] ?? 'general',
                        'serial_numbers' => $serialNumbers,
                        'assembly_id' => $itemData['assembly_id'] ?? null,
                        'product_unit' => $itemData['product_unit'] ?? null,
                    ];
                }
            }
        }

        // Validate stock for pending dispatch
        $stockErrors = [];
        foreach ($allItems as $item) {
            $stockCheck = $this->checkItemStock($item['item_type'], $item['item_id'], $item['warehouse_id'], $item['quantity']);
            if (!$stockCheck['sufficient']) {
                $stockErrors[] = $stockCheck['message'];
            }
            
            // Kiểm tra tồn kho N/A nếu có serial N/A
            if ($item['item_type'] === 'product' && !empty($item['serial_numbers'])) {
                $naCount = 0;
                foreach ($item['serial_numbers'] as $serial) {
                    $serialUpper = strtoupper(trim($serial));
                    if ($serialUpper === 'N/A' || $serialUpper === 'NA' || strpos($serialUpper, 'N/A-') === 0) {
                        $naCount++;
                    }
                }
                
                if ($naCount > 0) {
                    // Kiểm tra tồn kho N/A trong warehouse
                    $warehouseMaterial = DB::table('warehouse_materials')
                        ->where('warehouse_id', $item['warehouse_id'])
                        ->where('material_id', $item['item_id'])
                        ->where('item_type', 'product')
                        ->first();
                    
                    if ($warehouseMaterial) {
                        $totalQuantity = (int) ($warehouseMaterial->quantity ?? 0);
                        $serialCount = 0;
                        
                        // Đếm số serial trong kho
                        if (!empty($warehouseMaterial->serial_number)) {
                            $decoded = json_decode($warehouseMaterial->serial_number, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $serialCount = count(array_filter(array_map('trim', $decoded)));
                            } else {
                                $serialParts = explode(',', (string) $warehouseMaterial->serial_number);
                                $serialCount = count(array_filter(array_map('trim', $serialParts)));
                            }
                        }
                        
                        // Tồn N/A = tổng số lượng - số serial
                        $naStock = max(0, $totalQuantity - $serialCount);
                        
                        if ($naStock < $naCount) {
                            $itemName = $item['item_id']; // Có thể lấy tên từ product nếu cần
                            $stockErrors[] = "Sản phẩm ID {$item['item_id']} tại kho xuất: Không đủ tồn không serial. Yêu cầu: {$naCount}, Tồn có: {$naStock}";
                            
                            Log::warning('Insufficient N/A stock when updating dispatch', [
                                'product_id' => $item['item_id'],
                                'warehouse_id' => $item['warehouse_id'],
                                'na_count_requested' => $naCount,
                                'na_stock_available' => $naStock,
                                'total_quantity' => $totalQuantity,
                                'serial_count' => $serialCount
                            ]);
                        }
                    } else {
                        // Không tìm thấy warehouse_material, không có tồn kho
                        $itemName = $item['item_id'];
                        $stockErrors[] = "Sản phẩm ID {$item['item_id']} tại kho xuất: Không có tồn kho không serial. Yêu cầu: {$naCount}";
                        
                        Log::warning('No warehouse material found when checking N/A stock', [
                            'product_id' => $item['item_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'na_count_requested' => $naCount
                        ]);
                    }
                }
            }
        }

        if (!empty($stockErrors)) {
            throw new \Exception('Không đủ tồn kho:\n' . implode('\n', $stockErrors));
        }

        // Delete existing items and recreate
        $dispatch->items()->delete();

        // Create new dispatch items with proper assembly_id and product_unit calculation
        foreach ($allItems as $item) {
            $assemblyId = $item['assembly_id'] ?? null;
            $productUnit = $item['product_unit'] ?? null;
            $serialNumbers = $item['serial_numbers'] ?? [];
            
            // Nếu là sản phẩm và có serial numbers, tách thành các bản ghi riêng
            if ($item['item_type'] === 'product' && !empty($serialNumbers) && !in_array('N/A', $serialNumbers) && !in_array('NA', $serialNumbers)) {
                $assemblyIds = [];
                $productUnits = [];
                
                foreach ($serialNumbers as $serialIndex => $serial) {
                    $serialAssemblyId = null;
                    $serialProductUnit = null;
                    
                    // Tìm assembly_id cho serial cụ thể này
                    $assemblyProduct = \App\Models\AssemblyProduct::where('product_id', $item['item_id'])
                        ->where(function($q) use ($serial) {
                            $q->where('serials', $serial)
                              ->orWhereRaw('FIND_IN_SET(?, serials) > 0', [$serial]);
                        })
                        ->first();
                    
                    if ($assemblyProduct) {
                        $serialAssemblyId = $assemblyProduct->assembly_id;
                        
                        // Tìm product_unit từ assembly_products (ưu tiên) hoặc assembly_materials
                        if ($assemblyProduct->product_unit !== null) {
                            $productUnitValue = $assemblyProduct->product_unit;
                            
                            if (is_array($productUnitValue)) {
                                // Tìm vị trí của serial trong assembly_products.serials
                                $serialsStr = $assemblyProduct->serials ?? '';
                                $serialsArray = array_map('trim', explode(',', $serialsStr));
                                
                                // Tìm index của serial trong mảng serials của assembly
                                $serialPositionInAssembly = array_search(trim($serial), $serialsArray);
                                
                                if ($serialPositionInAssembly !== false && isset($productUnitValue[$serialPositionInAssembly])) {
                                    // Lấy product_unit tại vị trí tương ứng với serial trong assembly
                                    $serialProductUnit = $productUnitValue[$serialPositionInAssembly];
                                } else {
                                    // Fallback: nếu không tìm thấy, dùng index đầu tiên hoặc 0
                                    $serialProductUnit = $productUnitValue[0] ?? 0;
                                }
                            } else {
                                $serialProductUnit = $productUnitValue;
                            }
                        } else {
                            // Fallback: tìm từ assembly_materials
                            $assemblyMaterial = \App\Models\AssemblyMaterial::where('assembly_id', $serialAssemblyId)
                                ->where('target_product_id', $item['item_id'])
                                ->whereNotNull('product_unit')
                                ->first();
                            if ($assemblyMaterial) {
                                $serialProductUnit = $assemblyMaterial->product_unit;
                            }
                        }
                    }
                    
                    $assemblyIds[] = $serialAssemblyId;
                    $productUnits[] = $serialProductUnit;
                }
                
                // Tính số lượng N/A products cần thêm vào
                $itemQuantity = (int) ($item['quantity'] ?? 1);
                $naQuantity = $itemQuantity - count($serialNumbers);
                
                // Thêm assembly_id và product_unit cho N/A products
                if ($naQuantity > 0) {
                    // 1) Kiểm tra tồn kho N/A tại warehouse của dòng này
                    $warehouseIdForItem = $item['warehouse_id'] ?? null;
                    if ($warehouseIdForItem) {
                        $wm = DB::table('warehouse_materials')
                            ->where('warehouse_id', $warehouseIdForItem)
                            ->where('material_id', $item['item_id'])
                            ->where('item_type', 'product')
                            ->first();
                        $totalQty = (int)($wm->quantity ?? 0);
                        $serialCnt = 0;
                        if (!empty($wm?->serial_number)) {
                            $dec = json_decode($wm->serial_number, true);
                            if (is_array($dec)) {
                                $serialCnt = count(array_filter(array_map('trim', $dec)));
                            } else {
                                $serialCnt = count(array_filter(array_map('trim', explode(',', (string)$wm->serial_number))));
                            }
                        }
                        $naStockAvail = max(0, $totalQty - $serialCnt);
                        if ($naStockAvail <= 0) {
                            for ($i = 0; $i < $naQuantity; $i++) {
                                $assemblyIds[] = null; // sẽ thành '' khi implode
                                $productUnits[] = 0;
                            }
                            Log::info('No NA stock at warehouse for remaining NA units (update pending), set assembly_id=0', [
                                'product_id' => $item['item_id'],
                                'warehouse_id' => $warehouseIdForItem,
                                'na_quantity' => $naQuantity,
                                'na_stock_available' => $naStockAvail
                            ]);
                            goto after_na_assignment_update;
                        }
                    }
                    $usedSerialCount = count($serialNumbers);
                    
                    // Lấy các assembly_id có sẵn cho product_id này
                    // Ưu tiên assemblies không có serial, sau đó mới đến assemblies có serial nhưng có slot N/A
                    $warehouseIdForItem = $item['warehouse_id'] ?? null;
                    $productIdForItem = (int)$item['item_id'];
                    $availableAssemblies = \App\Models\AssemblyProduct::where('product_id', $productIdForItem)
                        ->orderBy('assembly_id')
                        ->get()
                        ->filter(function($assembly) use ($warehouseIdForItem, $productIdForItem) {
                            $capacity = $this->getNaCapacityForAssemblyProduct((int)$assembly->assembly_id, $productIdForItem, $warehouseIdForItem, true);
                            return $capacity > 0;
                        })
                        ->sortBy(function($assembly) {
                            // Ưu tiên assemblies không có serial trước
                            $hasSerials = $assembly->serials && 
                                         $assembly->serials !== '' && 
                                         $assembly->serials !== 'N/A' && 
                                         $assembly->serials !== 'NA';
                            return $hasSerials ? 1 : 0; // 0 = không có serial (ưu tiên), 1 = có serial
                        })
                        ->values(); // Reset keys
                    
                    $assemblyIndex = 0;
                    $currentProductUnitIndex = $usedSerialCount; // Bắt đầu từ product_unit tiếp theo sau serial
                    
                    for ($q = 0; $q < $naQuantity; $q++) {
                        if ($assemblyIndex < $availableAssemblies->count()) {
                            $assembly = $availableAssemblies[$assemblyIndex];
                            $assemblyId = $assembly->assembly_id;
                            
                            // Parse product_unit để lấy unit phù hợp
                            $productUnitValue = $assembly->product_unit;
                            $assemblyProductUnits = [];
                            
                            if (is_string($productUnitValue)) {
                                $assemblyProductUnits = json_decode($productUnitValue, true) ?: [$productUnitValue];
                            } elseif (is_array($productUnitValue)) {
                                $assemblyProductUnits = $productUnitValue;
                            } else {
                                $assemblyProductUnits = [$productUnitValue];
                            }
                            
                            // Tìm product_unit phù hợp với index hiện tại
                            $targetProductUnit = null;
                            
                            if ($assembly->serials && $assembly->serials !== 'N/A' && $assembly->serials !== 'NA') {
                                // Assembly có serial, tìm product_unit tiếp theo sau serial
                                // Ví dụ: assembly_id = 41 có [0,1], serial dùng 0, N/A sẽ dùng 1
                                if ($usedSerialCount < count($assemblyProductUnits)) {
                                    $targetProductUnit = $assemblyProductUnits[$usedSerialCount];
                                }
                            } else {
                                // Assembly không có serial, lấy product_unit theo thứ tự từ đầu
                                // Ví dụ: assembly_id = 42 có [0], N/A sẽ dùng 0
                                $targetProductUnit = $assemblyProductUnits[0] ?? 0;
                            }
                            
                            // Nếu không tìm được product_unit phù hợp, chuyển sang assembly tiếp theo
                            if ($targetProductUnit === null) {
                                $assemblyIndex++;
                                if ($assemblyIndex < $availableAssemblies->count()) {
                                    $assembly = $availableAssemblies[$assemblyIndex];
                                    $assemblyId = $assembly->assembly_id;
                                    
                                    $productUnitValue = $assembly->product_unit;
                                    if (is_string($productUnitValue)) {
                                        $assemblyProductUnits = json_decode($productUnitValue, true) ?: [$productUnitValue];
                                    } elseif (is_array($productUnitValue)) {
                                        $assemblyProductUnits = $productUnitValue;
                                    } else {
                                        $assemblyProductUnits = [$productUnitValue];
                                    }
                                    
                                    $targetProductUnit = $assemblyProductUnits[0] ?? 0;
                                }
                            }
                            
                            $assemblyIds[] = $assemblyId;
                            $productUnits[] = $targetProductUnit;
                            
                            // Chuyển sang assembly tiếp theo sau khi đã gán
                            $assemblyIndex++;
                        } else {
                            $assemblyIds[] = null;
                            $productUnits[] = null;
                        }
                        after_na_assignment_store:
                    }
                    after_na_assignment_update:
                }
                
                // Gộp assembly_id và product_unit thành chuỗi phân tách bằng dấu phẩy
                // Không dùng array_filter để tránh loại bỏ giá trị 0 và null
                $assemblyId = implode(',', array_map(function($val) { return $val !== null ? $val : ''; }, $assemblyIds));
                $productUnit = implode(',', array_map(function($val) { return $val !== null ? $val : 0; }, $productUnits));
            } elseif ($item['item_type'] === 'product' && (empty($serialNumbers) || in_array('N/A', $serialNumbers) || in_array('NA', $serialNumbers))) {
                // Xử lý N/A products với logic ưu tiên serial trước - gộp chung 1 hàng
                $itemQuantity = (int) ($item['quantity'] ?? 1);
                
                // Tính số lượng serial đã sử dụng trong dispatch này để dành product_unit
                $usedSerialCount = 0;
                
                // Đếm số serial đã sử dụng cho cùng product_id trong dispatch này
                foreach ($allItems as $otherItem) {
                    if ($otherItem['item_type'] === 'product' && 
                        $otherItem['item_id'] == $item['item_id'] && 
                        !empty($otherItem['serial_numbers'])) {
                        
                        // Normalize serial_numbers to array
                        $otherSerialNumbers = [];
                        if (is_string($otherItem['serial_numbers'])) {
                            $decoded = json_decode($otherItem['serial_numbers'], true);
                            $otherSerialNumbers = is_array($decoded) ? $decoded : [];
                        } elseif (is_array($otherItem['serial_numbers'])) {
                            $otherSerialNumbers = $otherItem['serial_numbers'];
                        }
                        
                        if (!empty($otherSerialNumbers) && 
                            !in_array('N/A', $otherSerialNumbers) && 
                            !in_array('NA', $otherSerialNumbers)) {
                            $usedSerialCount += count($otherSerialNumbers);
                        }
                    }
                }
                
                Log::info('Processing N/A products with serial priority in update', [
                    'product_id' => $item['item_id'],
                    'quantity' => $itemQuantity,
                    'used_serial_count' => $usedSerialCount
                ]);
                
                // Kiểm tra tồn kho N/A trong warehouse trước
                $warehouseId = $item['warehouse_id'] ?? null;
                $hasNAStock = false;
                if ($warehouseId) {
                    $warehouseMaterial = DB::table('warehouse_materials')
                        ->where('warehouse_id', $warehouseId)
                        ->where('material_id', $item['item_id'])
                        ->where('item_type', 'product')
                        ->first();
                    
                    if ($warehouseMaterial) {
                        $totalQuantity = (int) ($warehouseMaterial->quantity ?? 0);
                        $serialCount = 0;
                        
                        // Đếm số serial trong kho
                        if (!empty($warehouseMaterial->serial_number)) {
                            $decoded = json_decode($warehouseMaterial->serial_number, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $serialCount = count(array_filter(array_map('trim', $decoded)));
                            } else {
                                $serialParts = explode(',', (string) $warehouseMaterial->serial_number);
                                $serialCount = count(array_filter(array_map('trim', $serialParts)));
                            }
                        }
                        
                        // Tồn N/A = tổng số lượng - số serial
                        $naStock = max(0, $totalQuantity - $serialCount);
                        $hasNAStock = $naStock > 0;
                        
                        Log::info('Warehouse N/A stock check (update)', [
                            'warehouse_id' => $warehouseId,
                            'product_id' => $item['item_id'],
                            'total_quantity' => $totalQuantity,
                            'serial_count' => $serialCount,
                            'na_stock' => $naStock,
                            'has_na_stock' => $hasNAStock
                        ]);
                    }
                }
                
                // Nếu không có tồn N/A trong kho, gán assembly_id = 0
                if (!$hasNAStock) {
                    Log::info('No N/A stock in warehouse, setting assembly_id to 0 (update)', [
                        'product_id' => $item['item_id'],
                        'warehouse_id' => $warehouseId,
                        'quantity' => $itemQuantity
                    ]);
                    
                    $assemblyId = '0';
                    $productUnit = implode(',', array_fill(0, $itemQuantity, '0'));
                    
                    // Bỏ qua logic tìm assembly, gán trực tiếp
                } else {
                    // Có tồn N/A, tiếp tục logic tìm assembly như cũ
                
                // Loại trừ các cặp assembly_id:product_unit đã dùng trong phiếu xuất đã duyệt
                $approvedPairs = [];
                $approvedItems = DB::table('dispatch_items as di')
                    ->join('dispatches as d', 'd.id', '=', 'di.dispatch_id')
                    ->where('di.item_type', 'product')
                    ->where('di.item_id', $item['item_id'])
                    ->where('d.status', 'approved')
                    ->select('di.assembly_id', 'di.product_unit')
                    ->get();
                foreach ($approvedItems as $row) {
                    $aIds = array_map('trim', explode(',', (string) $row->assembly_id));
                    $pUnitsRaw = $row->product_unit;
                    // Parse product_unit (có thể là JSON string hoặc comma-separated string)
                    $pUnits = [];
                    if (is_string($pUnitsRaw)) {
                        $decoded = json_decode($pUnitsRaw, true);
                        if (is_array($decoded)) {
                            $pUnits = array_map('intval', $decoded);
                        } else {
                            $pUnits = array_map('intval', array_map('trim', explode(',', $pUnitsRaw)));
                        }
                    } elseif (is_array($pUnitsRaw)) {
                        $pUnits = array_map('intval', $pUnitsRaw);
                    }
                    
                    $len = min(count($aIds), count($pUnits));
                    for ($ii = 0; $ii < $len; $ii++) {
                        if ($aIds[$ii] !== '' && isset($pUnits[$ii])) {
                            $approvedPairs[$aIds[$ii] . ':' . $pUnits[$ii]] = true;
                        }
                    }
                }
                
                // Lấy các assembly_id có sẵn cho product_id này
                // Ưu tiên assemblies không có serial, sau đó mới đến assemblies có serial nhưng có slot N/A còn trống (chưa dùng trong phiếu đã duyệt)
                // QUAN TRỌNG: Chỉ lấy assemblies thuộc kho xuất (warehouse_id của item)
                $warehouseId = $item['warehouse_id'] ?? null;
                $availableAssemblies = \App\Models\AssemblyProduct::where('product_id', $item['item_id'])
                    ->with('assembly') // Eager load Assembly để kiểm tra warehouse
                    ->orderBy('assembly_id')
                    ->get()
                    ->filter(function($assemblyProduct) use ($warehouseId, $item) {
                        $capacity = $this->getNaCapacityForAssemblyProduct((int)$assemblyProduct->assembly_id, (int)$item['item_id'], $warehouseId, true);
                        return $capacity > 0;
                    })
                    ->sortBy(function($assemblyProduct) {
                        // Ưu tiên assemblies không có serial trước
                        $hasSerials = $assemblyProduct->serials && 
                                     $assemblyProduct->serials !== '' && 
                                     $assemblyProduct->serials !== 'N/A' && 
                                     $assemblyProduct->serials !== 'NA';
                        return $hasSerials ? 1 : 0; // 0 = không có serial (ưu tiên), 1 = có serial
                    })
                    ->values(); // Reset keys
                
                // approvedPairs đã được lấy ở trên, sử dụng lại
                
                $assemblyIds = [];
                $productUnits = [];
                $localUsedPairs = [];
                // Tính sức chứa còn lại theo từng assembly để tránh vượt quá slot N/A
                $assemblyRemaining = [];
                foreach ($availableAssemblies as $asm) {
                    $units = [];
                    if (is_string($asm->product_unit)) {
                        $units = json_decode($asm->product_unit, true) ?: [];
                    } elseif (is_array($asm->product_unit)) {
                        $units = $asm->product_unit;
                    } elseif ($asm->product_unit !== null) {
                        $units = [$asm->product_unit];
                    }
                    $hasSerials = $asm->serials && $asm->serials !== '' && $asm->serials !== 'N/A' && $asm->serials !== 'NA';
                    $serialCountCap = 0;
                    if ($hasSerials) {
                        $partsTmp = preg_split('/[\s,;|\/]+/', (string)$asm->serials, -1, PREG_SPLIT_NO_EMPTY);
                        $serialCountCap = is_array($partsTmp) ? count($partsTmp) : 1;
                    }
                    $capacity = $hasSerials ? max(0, count($units) - $serialCountCap) : (count($units) > 0 ? count($units) : 1);
                    if ($capacity > 0) {
                        $aidStr = (string)$asm->assembly_id;
                        $used = 0;
                        foreach ($units as $idx => $u) {
                            if ($hasSerials && $idx < $serialCountCap) continue;
                            $key = $aidStr . ':' . (int)$u;
                            if (isset($approvedPairs[$key])) $used++;
                        }
                        $capacity = max(0, $capacity - $used);
                    }
                    $assemblyRemaining[(int)$asm->assembly_id] = $capacity;
                }
                $needed = $itemQuantity;
                foreach ($availableAssemblies as $assembly) {
                    if ($needed <= 0) { break; }
                    $assemblyId = $assembly->assembly_id;
                    if (($assemblyRemaining[(int)$assemblyId] ?? 0) <= 0) { continue; }
                    $productUnitValue = $assembly->product_unit;
                    $assemblyProductUnits = [];
                    if (is_string($productUnitValue)) {
                        $assemblyProductUnits = json_decode($productUnitValue, true) ?: [$productUnitValue];
                    } elseif (is_array($productUnitValue)) {
                        $assemblyProductUnits = $productUnitValue;
                    } else {
                        $assemblyProductUnits = [$productUnitValue];
                    }
                    $assemblySerialsStr = is_string($assembly->serials) ? $assembly->serials : '';
                    $parts = preg_split('/[\s,;|\/]+/', $assemblySerialsStr, -1, PREG_SPLIT_NO_EMPTY);
                    $assemblySerialCount = ($assembly->serials && $assembly->serials !== 'N/A' && $assembly->serials !== 'NA') ? (is_array($parts) ? count($parts) : 1) : 0;
                    for ($unitIdx = $assemblySerialCount; $unitIdx < count($assemblyProductUnits) && $needed > 0; $unitIdx++) {
                        $candidateUnit = $assemblyProductUnits[$unitIdx];
                        $key = $assemblyId . ':' . $candidateUnit;
                        if (!isset($approvedPairs[$key]) && !isset($localUsedPairs[$key])) {
                            $assemblyIds[] = $assemblyId;
                            $productUnits[] = $candidateUnit;
                            $localUsedPairs[$key] = true;
                            $needed--;
                            if (isset($assemblyRemaining[(int)$assemblyId])) {
                                $assemblyRemaining[(int)$assemblyId] = max(0, $assemblyRemaining[(int)$assemblyId] - 1);
                            }
                        }
                    }
                }
                while ($needed > 0) {
                    $assemblyIds[] = null;
                    $productUnits[] = null;
                    $needed--;
                }
                
                // Gộp assembly_id và product_unit thành chuỗi phân tách bằng dấu phẩy
                $assemblyId = implode(',', array_map(function($val) { return $val !== null ? $val : ''; }, $assemblyIds));
                $productUnit = implode(',', array_map(function($val) { return $val !== null ? $val : 0; }, $productUnits));
                } // End else: có tồn N/A, đã tìm assembly
            }
            
            DispatchItem::create([
                'dispatch_id' => $dispatch->id,
                'item_type' => $item['item_type'],
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'warehouse_id' => $item['warehouse_id'],
                'category' => $item['category'],
                'serial_numbers' => $serialNumbers,
                'assembly_id' => $assemblyId,
                'product_unit' => $productUnit,
            ]);
        }
    }

    /**
     * Update only serial numbers for approved dispatch
     */
    private function updateDispatchItemsApproved(Request $request, Dispatch $dispatch)
    {
        $stockErrors = [];
        $allItemsToUpdate = [];
        
        // Collect all items to update and validate N/A stock
        if ($request->has('contract_items')) {
            foreach ($request->contract_items as $itemId => $itemData) {
                if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                    $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                        return !empty(trim($serial));
                    });
                    
                    $dispatchItem = $dispatch->items()->where('id', $itemId)->first();
                    if ($dispatchItem) {
                        $allItemsToUpdate[] = [
                            'item' => $dispatchItem,
                            'serial_numbers' => $serialNumbers
                        ];
                        
                        // Validate N/A stock
                        $naCount = 0;
                        foreach ($serialNumbers as $serial) {
                            $serialUpper = strtoupper(trim($serial));
                            if ($serialUpper === 'N/A' || $serialUpper === 'NA' || strpos($serialUpper, 'N/A-') === 0) {
                                $naCount++;
                            }
                        }
                        
                        if ($naCount > 0) {
                            $warehouseMaterial = DB::table('warehouse_materials')
                                ->where('warehouse_id', $dispatchItem->warehouse_id)
                                ->where('material_id', $dispatchItem->item_id)
                                ->where('item_type', $dispatchItem->item_type)
                                ->first();
                            
                            if ($warehouseMaterial) {
                                $totalQuantity = (int) ($warehouseMaterial->quantity ?? 0);
                                $serialCount = 0;
                                
                                if (!empty($warehouseMaterial->serial_number)) {
                                    $decoded = json_decode($warehouseMaterial->serial_number, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $serialCount = count(array_filter(array_map('trim', $decoded)));
                                    } else {
                                        $serialParts = explode(',', (string) $warehouseMaterial->serial_number);
                                        $serialCount = count(array_filter(array_map('trim', $serialParts)));
                                    }
                                }
                                
                                $naStock = max(0, $totalQuantity - $serialCount);
                                
                                if ($naStock < $naCount) {
                                    $stockErrors[] = "Sản phẩm ID {$dispatchItem->item_id} tại kho xuất: Không đủ tồn không serial. Yêu cầu: {$naCount}, Tồn có: {$naStock}";
                                }
                            } else {
                                $stockErrors[] = "Sản phẩm ID {$dispatchItem->item_id} tại kho xuất: Không có tồn kho không serial. Yêu cầu: {$naCount}";
                            }
                        }
                    }
                }
            }
        }

        if ($request->has('backup_items')) {
            foreach ($request->backup_items as $itemId => $itemData) {
                if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                    $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                        return !empty(trim($serial));
                    });
                    
                    $dispatchItem = $dispatch->items()->where('id', $itemId)->first();
                    if ($dispatchItem) {
                        $allItemsToUpdate[] = [
                            'item' => $dispatchItem,
                            'serial_numbers' => $serialNumbers
                        ];
                        
                        // Validate N/A stock (same logic as above)
                        $naCount = 0;
                        foreach ($serialNumbers as $serial) {
                            $serialUpper = strtoupper(trim($serial));
                            if ($serialUpper === 'N/A' || $serialUpper === 'NA' || strpos($serialUpper, 'N/A-') === 0) {
                                $naCount++;
                            }
                        }
                        
                        if ($naCount > 0) {
                            $warehouseMaterial = DB::table('warehouse_materials')
                                ->where('warehouse_id', $dispatchItem->warehouse_id)
                                ->where('material_id', $dispatchItem->item_id)
                                ->where('item_type', $dispatchItem->item_type)
                                ->first();
                            
                            if ($warehouseMaterial) {
                                $totalQuantity = (int) ($warehouseMaterial->quantity ?? 0);
                                $serialCount = 0;
                                
                                if (!empty($warehouseMaterial->serial_number)) {
                                    $decoded = json_decode($warehouseMaterial->serial_number, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $serialCount = count(array_filter(array_map('trim', $decoded)));
                                    } else {
                                        $serialParts = explode(',', (string) $warehouseMaterial->serial_number);
                                        $serialCount = count(array_filter(array_map('trim', $serialParts)));
                                    }
                                }
                                
                                $naStock = max(0, $totalQuantity - $serialCount);
                                
                                if ($naStock < $naCount) {
                                    $stockErrors[] = "Sản phẩm ID {$dispatchItem->item_id} tại kho xuất: Không đủ tồn không serial. Yêu cầu: {$naCount}, Tồn có: {$naStock}";
                                }
                            } else {
                                $stockErrors[] = "Sản phẩm ID {$dispatchItem->item_id} tại kho xuất: Không có tồn kho không serial. Yêu cầu: {$naCount}";
                            }
                        }
                    }
                }
            }
        }

        if ($request->has('general_items')) {
            foreach ($request->general_items as $itemId => $itemData) {
                if (isset($itemData['serial_numbers']) && is_array($itemData['serial_numbers'])) {
                    $serialNumbers = array_filter($itemData['serial_numbers'], function ($serial) {
                        return !empty(trim($serial));
                    });
                    
                    $dispatchItem = $dispatch->items()->where('id', $itemId)->first();
                    if ($dispatchItem) {
                        $allItemsToUpdate[] = [
                            'item' => $dispatchItem,
                            'serial_numbers' => $serialNumbers
                        ];
                        
                        // Validate N/A stock (same logic as above)
                        $naCount = 0;
                        foreach ($serialNumbers as $serial) {
                            $serialUpper = strtoupper(trim($serial));
                            if ($serialUpper === 'N/A' || $serialUpper === 'NA' || strpos($serialUpper, 'N/A-') === 0) {
                                $naCount++;
                            }
                        }
                        
                        if ($naCount > 0) {
                            $warehouseMaterial = DB::table('warehouse_materials')
                                ->where('warehouse_id', $dispatchItem->warehouse_id)
                                ->where('material_id', $dispatchItem->item_id)
                                ->where('item_type', $dispatchItem->item_type)
                                ->first();
                            
                            if ($warehouseMaterial) {
                                $totalQuantity = (int) ($warehouseMaterial->quantity ?? 0);
                                $serialCount = 0;
                                
                                if (!empty($warehouseMaterial->serial_number)) {
                                    $decoded = json_decode($warehouseMaterial->serial_number, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $serialCount = count(array_filter(array_map('trim', $decoded)));
                                    } else {
                                        $serialParts = explode(',', (string) $warehouseMaterial->serial_number);
                                        $serialCount = count(array_filter(array_map('trim', $serialParts)));
                                    }
                                }
                                
                                $naStock = max(0, $totalQuantity - $serialCount);
                                
                                if ($naStock < $naCount) {
                                    $stockErrors[] = "Sản phẩm ID {$dispatchItem->item_id} tại kho xuất: Không đủ tồn không serial. Yêu cầu: {$naCount}, Tồn có: {$naStock}";
                                }
                            } else {
                                $stockErrors[] = "Sản phẩm ID {$dispatchItem->item_id} tại kho xuất: Không có tồn kho không serial. Yêu cầu: {$naCount}";
                            }
                        }
                    }
                }
            }
        }
        
        // Throw error if validation fails
        if (!empty($stockErrors)) {
            throw new \Exception('Không đủ tồn kho không serial:\n' . implode('\n', $stockErrors));
        }
        
        // Update serial numbers if validation passes
        foreach ($allItemsToUpdate as $itemUpdate) {
            $itemUpdate['item']->update([
                'serial_numbers' => $itemUpdate['serial_numbers']
            ]);
        }
    }

    /**
     * Approve the specified dispatch.
     */
    public function approve(Request $request, Dispatch $dispatch)
    {
        if ($dispatch->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể duyệt phiếu xuất đang chờ xử lý.'
            ]);
        }

        // Check for duplicate serials with already approved dispatches
        $duplicateSerials = $this->checkDuplicateSerials($dispatch);
        if (!empty($duplicateSerials)) {
            return response()->json([
                'success' => false,
                'message' => 'Phát hiện serial numbers trùng lặp với phiếu xuất đã duyệt',
                'duplicate_serials' => $duplicateSerials
            ]);
        }

        DB::beginTransaction();

        try {
            // Kiểm tra tồn kho lại trước khi duyệt
            $stockErrors = [];

            // Nhóm items theo sản phẩm và kho để tính tổng số lượng
            $groupedItems = [];
            foreach ($dispatch->items as $item) {
                $key = $item->item_type . '_' . $item->item_id . '_' . $item->warehouse_id;
                if (!isset($groupedItems[$key])) {
                    $groupedItems[$key] = [
                        'item_type' => $item->item_type,
                        'item_id' => $item->item_id,
                        'warehouse_id' => $item->warehouse_id,
                        'total_quantity' => 0,
                        'serial_selected' => 0,
                        'categories' => []
                    ];
                }
                $groupedItems[$key]['total_quantity'] += $item->quantity;
                // Đếm số serial THẬT đã chọn cho item này (loại trừ virtual serial N/A-*)
                if (is_array($item->serial_numbers)) {
                    $realSerials = array_filter($item->serial_numbers, function($s) {
                        return !empty(trim($s)) && strpos($s, 'N/A-') !== 0;
                    });
                    $groupedItems[$key]['serial_selected'] += count($realSerials);
                } elseif (is_string($item->serial_numbers) && !empty($item->serial_numbers)) {
                    $decodedSerials = json_decode($item->serial_numbers, true);
                    if (is_array($decodedSerials)) {
                        $realSerials = array_filter($decodedSerials, function($s) {
                            return !empty(trim($s)) && strpos($s, 'N/A-') !== 0;
                        });
                        $groupedItems[$key]['serial_selected'] += count($realSerials);
                    }
                }
                $groupedItems[$key]['categories'][] = $item->category ?? 'general';
            }

            // Kiểm tra tồn kho cho từng nhóm sản phẩm
            foreach ($groupedItems as $key => $groupedItem) {
                try {
                    $stockCheck = $this->checkItemStock(
                        $groupedItem['item_type'],
                        $groupedItem['item_id'],
                        $groupedItem['warehouse_id'],
                        $groupedItem['total_quantity']
                    );

                    if (!$stockCheck['sufficient']) {
                        $categoriesText = implode(', ', array_unique($groupedItem['categories']));
                        $stockErrors[] = $stockCheck['message'] . " (Tổng từ: $categoriesText)";
                    }

                    // Tính số serial khả dụng và tổng tồn trực tiếp từ warehouse_materials
                    $warehouseMaterialRow = \App\Models\WarehouseMaterial::where('item_type', $groupedItem['item_type'])
                        ->where('material_id', $groupedItem['item_id'])
                        ->where('warehouse_id', $groupedItem['warehouse_id'])
                        ->first();

                    $availableSerialCount = 0;
                    $currentTotalQuantity = 0;
                    if ($warehouseMaterialRow) {
                        $currentTotalQuantity = (int) $warehouseMaterialRow->quantity;
                        $currentSerials = $this->normalizeSerialArray($warehouseMaterialRow->serial_number);
                        $availableSerialCount = is_array($currentSerials) ? count(array_filter($currentSerials)) : 0;
                    }
                    
                    // Không cho phép "mượn" số lượng không-serial vượt quá tồn không-serial thực tế
                    // Ví dụ: tồn = 6 (3 có serial, 3 không serial); yêu cầu 4 nhưng không chọn serial nào => phải báo lỗi thiếu 1 serial
                    $currentNonSerialAvailable = max(0, $currentTotalQuantity - $availableSerialCount);
                    $noSerialRequestedTotal = (int)$groupedItem['total_quantity'] - (int)$groupedItem['serial_selected'];
                    if ($noSerialRequestedTotal > $currentNonSerialAvailable) {
                        $missingRealSerials = $noSerialRequestedTotal - $currentNonSerialAvailable;
                        // Lấy tên item để hiển thị lỗi rõ ràng hơn
                        $itemName = 'Unknown';
                        if ($groupedItem['item_type'] === 'material') {
                            $item = \App\Models\Material::find($groupedItem['item_id']);
                        } elseif ($groupedItem['item_type'] === 'product') {
                            $item = \App\Models\Product::find($groupedItem['item_id']);
                        } elseif ($groupedItem['item_type'] === 'good') {
                            $item = \App\Models\Good::find($groupedItem['item_id']);
                        }
                        if (isset($item)) {
                            $itemName = "{$item->code} - {$item->name}";
                        }
                        // Lấy tên kho để hiển thị thay vì ID
                        $warehouseName = 'Unknown';
                        $warehouse = \App\Models\Warehouse::find($groupedItem['warehouse_id']);
                        if ($warehouse) {
                            $warehouseName = $warehouse->name ?? (string)$groupedItem['warehouse_id'];
                        }
                        $stockErrors[] = sprintf(
                            'Mục %s cần chọn thêm %d serial (tồn không-serial khả dụng: %d). Sản phẩm: %s, Kho: %s.',
                            $groupedItem['item_type'],
                            $missingRealSerials,
                            $currentNonSerialAvailable,
                            $itemName,
                            $warehouseName
                        );
                    }
                    
                    // CHỈ kiểm tra serial nếu có serial được chọn (bỏ qua kiểm tra cho thiết bị no_serial)
                    if ((int)$groupedItem['serial_selected'] > 0) {
                        // Xác thực: số serial đã chọn không vượt quá số serial khả dụng
                        if ((int)$groupedItem['serial_selected'] > $availableSerialCount) {
                            $stockErrors[] = sprintf(
                                'Mục %s (ID %d) tại kho %d chọn %d serial vượt quá số serial khả dụng (%d).',
                                $groupedItem['item_type'],
                                $groupedItem['item_id'],
                                $groupedItem['warehouse_id'],
                                (int)$groupedItem['serial_selected'],
                                $availableSerialCount
                            );
                        }
                    }

                    // Kiểm tra tồn kho không-serial nếu có yêu cầu (CHỈ kiểm tra tổng quantity, không kiểm tra serial)
                    $noSerialRequired = $groupedItem['total_quantity'] - $groupedItem['serial_selected'];
                    if ($noSerialRequired > 0) {
                        // Với thiết bị no_serial, chỉ cần kiểm tra tổng tồn kho đủ không
                        // Không cần kiểm tra chi tiết serial nữa
                        Log::info('No-serial stock check (quantity only):', [
                            'item' => $groupedItem['item_type'] . '_' . $groupedItem['item_id'],
                            'totalInWarehouse' => $currentTotalQuantity,
                            'noSerialRequired' => $noSerialRequired,
                            'sufficient' => $currentTotalQuantity >= $noSerialRequired
                        ]);
                        
                        // Kiểm tra đơn giản: tổng tồn kho >= số lượng yêu cầu
                        if ($currentTotalQuantity < $noSerialRequired) {
                            // Lấy tên item để hiển thị lỗi rõ ràng hơn
                            $itemName = 'Unknown';
                            if ($groupedItem['item_type'] === 'material') {
                                $item = \App\Models\Material::find($groupedItem['item_id']);
                            } elseif ($groupedItem['item_type'] === 'product') {
                                $item = \App\Models\Product::find($groupedItem['item_id']);
                            } elseif ($groupedItem['item_type'] === 'good') {
                                $item = \App\Models\Good::find($groupedItem['item_id']);
                            }
                            if (isset($item)) {
                                $itemName = "{$item->code} - {$item->name}";
                            }
                            
                            $stockErrors[] = "Không đủ tồn kho cho {$itemName}. Yêu cầu {$noSerialRequired}, còn {$currentTotalQuantity}";
                        }
                    }
                } catch (\Exception $stockException) {
                    Log::error("Error checking stock for grouped item $key:", [
                        'item' => $groupedItem,
                        'error' => $stockException->getMessage()
                    ]);
                    $stockErrors[] = "Lỗi kiểm tra tồn kho cho sản phẩm ID {$groupedItem['item_id']}";
                }
            }

            if (!empty($stockErrors)) {
                Log::error('Stock errors found during approval:', $stockErrors);
                return response()->json([
                    'success' => false,
                    'message' => 'Không đủ tồn kho để duyệt phiếu:\n' . implode('\n', $stockErrors)
                ]);
            }

            // Trừ tồn kho khi duyệt
            Log::info('Reducing stock for all items during approval...');
            foreach ($groupedItems as $key => $groupedItem) {
                try {
                    $this->reduceItemStock(
                        $groupedItem['item_type'],
                        $groupedItem['item_id'],
                        $groupedItem['warehouse_id'],
                        $groupedItem['total_quantity']
                    );
                    Log::info("Reduced stock for grouped item $key: {$groupedItem['item_type']} ID {$groupedItem['item_id']}, total quantity {$groupedItem['total_quantity']}");
                } catch (\Exception $stockException) {
                    Log::error("Error reducing stock for grouped item $key:", [
                        'item' => $groupedItem,
                        'error' => $stockException->getMessage()
                    ]);
                    throw $stockException;
                }
            }

            // Tạo virtual serial cho thiết bị không có serial và loại bỏ serial đã xuất khỏi warehouse_materials.serial_number
            try {
                $dispatch->load('items');
                
                // Tính virtual serial counter cho dự án/rental này
                // SỬ DỤNG RANDOM SUFFIX ĐỂ ĐẢM BẢO DUY NHẤT TOÀN CỤC
                // Giải thích: Khi duyệt phiếu, nếu thiết bị chưa có serial,
                // ta tạo virtual serial với random suffix (N/A-A1B2C3) thay vì
                // index tuần tự (N/A-0, N/A-1) để tránh trùng lặp giữa các project.
                
                foreach ($dispatch->items as $dispatchItem) {
                    $serialNumbers = is_array($dispatchItem->serial_numbers) ? $dispatchItem->serial_numbers : [];
                    $quantity = (int)$dispatchItem->quantity;
                    
                    // Nếu quantity > số serial thực tế → tạo virtual serial
                    $currentSerialCount = count(array_filter($serialNumbers, function($s) { return !empty(trim($s)); }));
                    if ($quantity > $currentSerialCount) {
                        $needNewVirtuals = $quantity - $currentSerialCount;
                        
                        // Sử dụng SerialHelper để tạo virtual serial duy nhất toàn cục
                        $newVirtualSerials = \App\Helpers\SerialHelper::generateUniqueVirtualSerials($needNewVirtuals);
                        $serialNumbers = array_merge($serialNumbers, $newVirtualSerials);
                        
                        // Lưu virtual serial vào DB
                        $dispatchItem->serial_numbers = $serialNumbers;
                        $dispatchItem->save();
                        
                        Log::info('Created virtual serials for dispatch item', [
                            'dispatch_item_id' => $dispatchItem->id,
                            'quantity' => $quantity,
                            'real_serials' => $currentSerialCount,
                            'virtual_serials' => $needNewVirtuals,
                            'new_virtual_serials' => $newVirtualSerials,
                            'total_serials' => count($serialNumbers)
                        ]);
                    }
                    
                    // Loại bỏ serial thật đã xuất khỏi warehouse_materials.serial_number
                    $realSerials = array_filter($serialNumbers, function($s) {
                        return !empty(trim($s)) && strpos($s, 'N/A-') !== 0;
                    });
                    
                    if (!empty($realSerials)) {
                        $warehouseMaterial = \App\Models\WarehouseMaterial::where('item_type', $dispatchItem->item_type)
                            ->where('material_id', $dispatchItem->item_id)
                            ->where('warehouse_id', $dispatchItem->warehouse_id)
                            ->first();

                        if ($warehouseMaterial && !empty($warehouseMaterial->serial_number)) {
                            $currentSerials = $this->normalizeSerialArray($warehouseMaterial->serial_number);
                            if (!empty($currentSerials)) {
                                $remainingSerials = array_values(array_udiff(
                                    $currentSerials,
                                    $realSerials,
                                    function ($a, $b) { return strcasecmp(trim($a), trim($b)); }
                                ));
                                $warehouseMaterial->serial_number = json_encode($remainingSerials);
                                $warehouseMaterial->save();
                            }
                        }
                    }
                }
                Log::info('Created virtual serials and removed dispatched serials from warehouse_materials.serial_number');
            } catch (\Exception $serialUpdateEx) {
                Log::error('Error updating serials on approval', [
                    'dispatch_id' => $dispatch->id,
                    'error' => $serialUpdateEx->getMessage()
                ]);
                // Không chặn duyệt phiếu nếu lỗi cập nhật serial JSON, chỉ ghi log
            }

            // Ghi nhật ký thay đổi cho từng sản phẩm khi duyệt phiếu xuất
            Log::info('Creating change logs for dispatch approval...');
            foreach ($groupedItems as $key => $groupedItem) {
                try {
                    // Lấy thông tin sản phẩm
                    $itemModel = null;
                    switch ($groupedItem['item_type']) {
                        case 'material':
                            $itemModel = \App\Models\Material::find($groupedItem['item_id']);
                            break;
                        case 'product':
                            $itemModel = \App\Models\Product::find($groupedItem['item_id']);
                            break;
                        case 'good':
                            $itemModel = \App\Models\Good::find($groupedItem['item_id']);
                            break;
                    }

                    if ($itemModel) {
                        // Lấy tên dự án hoặc cho thuê dựa vào dispatch_type
                        $description = '';

                        if ($dispatch->dispatch_type === 'project' && $dispatch->project_id) {
                            $project = \App\Models\Project::find($dispatch->project_id);
                            $description = $project ? $project->project_name : 'Không có dự án';
                        } elseif ($dispatch->dispatch_type === 'rental' && $dispatch->project_id) {
                            // Với rental, project_id thực ra là rental_id
                            $rental = \App\Models\Rental::find($dispatch->project_id);
                            $description = $rental ? $rental->rental_name : 'Không có thông tin cho thuê';
                        }

                        // Tạo nhật ký xuất kho cho sản phẩm chính
                        ChangeLogHelper::xuatKho(
                            $itemModel->code,
                            $itemModel->name,
                            $groupedItem['total_quantity'],
                            $dispatch->dispatch_code,
                            $description, // Tên dự án/cho thuê
                            [
                                'dispatch_id' => $dispatch->id,
                                'warehouse_id' => $groupedItem['warehouse_id'],
                                'project_id' => $dispatch->project_id,
                                'dispatch_type' => $dispatch->dispatch_type,
                                'dispatch_detail' => $dispatch->dispatch_detail,
                                'project_receiver' => $dispatch->project_receiver,
                                'categories' => array_unique($groupedItem['categories']),
                                'item_type' => $groupedItem['item_type'],
                                'approved_by' => Auth::id(),
                                'approved_at' => now()->toDateTimeString()
                            ],
                            $dispatch->dispatch_note // Ghi chú của phiếu xuất
                        );
                    }
                } catch (\Exception $logException) {
                    Log::error("Error creating change log for grouped item $key:", [
                        'item' => $groupedItem,
                        'error' => $logException->getMessage()
                    ]);
                    // Continue processing even if change log creation fails
                }
            }

            // Cập nhật trạng thái duyệt
            $dispatch->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Tạo/Cập nhật bảo hành điện tử khi duyệt (nếu không phải backup-only)
            $warrantyAction = null;
            $dispatch->load('items'); // Load items trước khi kiểm tra isBackupOnlyDispatch
            
            Log::info("Checking warranty creation for dispatch", [
                'dispatch_id' => $dispatch->id,
                'dispatch_type' => $dispatch->dispatch_type,
                'dispatch_detail' => $dispatch->dispatch_detail,
                'project_id' => $dispatch->project_id,
                'items_count' => $dispatch->items->count(),
                'is_backup_only' => $this->isBackupOnlyDispatch($dispatch)
            ]);
            
            if (!$this->isBackupOnlyDispatch($dispatch)) {
                $firstDispatchItem = $dispatch->items->first();
                
                Log::info('First dispatch item for warranty', [
                    'has_first_item' => $firstDispatchItem ? true : false,
                    'first_item_id' => $firstDispatchItem ? $firstDispatchItem->id : null,
                    'first_item_type' => $firstDispatchItem ? $firstDispatchItem->item_type : null,
                    'first_item_category' => $firstDispatchItem ? $firstDispatchItem->category : null
                ]);

                if ($firstDispatchItem) {
                    try {
                        // Get warranty period for rental from rental record
                        $warrantyPeriod = $dispatch->warranty_period;
                        
                        // If not set and this is a rental, try to get from rental record
                        if (!$warrantyPeriod && $dispatch->dispatch_type === 'rental' && $dispatch->project_id) {
                            $rental = \App\Models\Rental::find($dispatch->project_id);
                            if ($rental) {
                                // Use warranty period from rental if defined, else fall back later
                                $warrantyPeriod = $rental->warranty_period ?: null;
                                Log::info("Resolved warranty period from rental", [
                                    'rental_id' => $rental->id,
                                    'rental_warranty_period' => $rental->warranty_period,
                                    'applied_warranty_period' => $warrantyPeriod
                                ]);
                            }
                        }
                        
                        // If still no warranty period, use default
                        if (!$warrantyPeriod) {
                            $warrantyPeriod = '12 tháng';
                            Log::info("Using fallback warranty period", ['warranty_period' => $warrantyPeriod]);
                        }

                        // Tạo fake request object với thông tin cần thiết
                        $fakeRequest = new \Illuminate\Http\Request();
                        $fakeRequest->merge([
                            'dispatch_type' => $dispatch->dispatch_type,
                            'project_id' => $dispatch->project_id,
                            'project_receiver' => $dispatch->project_receiver,
                            'warranty_period' => $warrantyPeriod
                        ]);

                        Log::info("Creating warranty with fake request", [
                            'dispatch_id' => $dispatch->id,
                            'dispatch_type' => $dispatch->dispatch_type,
                            'warranty_period' => $warrantyPeriod
                        ]);

                        $warrantyAction = $this->createWarrantyForDispatchItem($dispatch, $firstDispatchItem, $fakeRequest);
                        Log::info("Project warranty created/updated during approval:", ['dispatch_id' => $dispatch->id, 'warranty_action' => $warrantyAction]);
                    } catch (\Exception $warrantyException) {
                        Log::error("Error creating project warranty during approval:", [
                            'dispatch_id' => $dispatch->id,
                            'error' => $warrantyException->getMessage(),
                            'trace' => $warrantyException->getTraceAsString()
                        ]);
                        // Continue processing even if warranty creation fails
                    }
                }
            }

            DB::commit();

            // Ghi nhật ký duyệt phiếu xuất
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'approve',
                    'dispatches',
                    'Duyệt phiếu xuất: ' . $dispatch->dispatch_code,
                    null,
                    $dispatch->toArray()
                );
            }

            // Xây dựng thông điệp tạo/cập nhật bảo hành rõ ràng
            $approvalMessage = 'Phiếu xuất đã được duyệt thành công.';
            if ($warrantyAction && is_array($warrantyAction)) {
                if (($warrantyAction['action'] ?? null) === 'created') {
                    $approvalMessage .= ' Đã tạo bảo hành điện tử: ' . ($warrantyAction['warranty_code'] ?? '') . '.';
                } elseif (($warrantyAction['action'] ?? null) === 'updated') {
                    $itemsAdded = $warrantyAction['items_count'] ?? 0;
                    $approvalMessage .= ' Đã cập nhật bảo hành điện tử: ' . ($warrantyAction['warranty_code'] ?? '') . ($itemsAdded ? ' (thêm ' . $itemsAdded . ' thiết bị).' : '.');
                }
            }

            return response()->json([
                'success' => true,
                'message' => $approvalMessage,
                'status' => $dispatch->status_label,
                'status_color' => $dispatch->status_color
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi duyệt phiếu xuất: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel the specified dispatch.
     */
    public function cancel(Request $request, Dispatch $dispatch)
    {
        if (in_array($dispatch->status, ['approved', 'completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy phiếu xuất đã duyệt, đã hoàn thành hoặc đã hủy.'
            ]);
        }

        DB::beginTransaction();

        try {
            // Chỉ pending mới có thể hủy, và pending chưa trừ tồn kho nên không cần hoàn trả
            Log::info('Cancelling pending dispatch:', ['dispatch_id' => $dispatch->id]);

            $dispatch->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            // Ghi nhật ký hủy phiếu xuất
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'cancel',
                    'dispatches',
                    'Hủy phiếu xuất: ' . $dispatch->dispatch_code,
                    null,
                    $dispatch->toArray()
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Phiếu xuất đã được hủy thành công.',
                'status' => $dispatch->status_label,
                'status_color' => $dispatch->status_color
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy phiếu xuất: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete the specified dispatch (only when cancelled).
     */
    public function destroy(Dispatch $dispatch)
    {
        // Lưu dữ liệu cũ trước khi xóa
        $oldData = $dispatch->toArray();
        $dispatchCode = $dispatch->dispatch_code;

        if ($dispatch->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa phiếu xuất đã hủy.'
            ]);
        }

        DB::beginTransaction();

        try {
            // Xóa tất cả items của dispatch
            $dispatch->items()->delete();

            // Xóa dispatch
            $dispatch->delete();

            DB::commit();

            // Ghi nhật ký xóa phiếu xuất
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'dispatches',
                    'Xóa phiếu xuất: ' . $dispatchCode,
                    $oldData,
                    null
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Phiếu xuất đã được xóa thành công.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa phiếu xuất: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mark the specified dispatch as completed.
     */
    public function complete(Dispatch $dispatch)
    {
        if ($dispatch->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể hoàn thành phiếu xuất đã được duyệt.'
            ]);
        }

        try {
            $dispatch->update([
                'status' => 'completed',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phiếu xuất đã được đánh dấu hoàn thành.',
                'status' => $dispatch->status_label,
                'status_color' => $dispatch->status_color
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get available items for dispatch from a specific warehouse.
     */
    public function getAvailableItems(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $itemType = $request->get('item_type', 'all');

        if (!$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Warehouse ID is required'
            ]);
        }

        $items = collect();

        // Get materials
        if (in_array($itemType, ['all', 'material'])) {
            $materials = Material::whereHas('warehouseMaterials', function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'material')
                    ->where('quantity', '>', 0);
            })->with(['warehouseMaterials' => function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'material');
            }])->get();

            foreach ($materials as $material) {
                $quantity = $material->warehouseMaterials->sum('quantity');
                if ($quantity > 0) {
                    $items->push([
                        'id' => $material->id,
                        'type' => 'material',
                        'code' => $material->code,
                        'name' => $material->name,
                        'unit' => $material->unit,
                        'available_quantity' => $quantity,
                        'display_name' => "{$material->code} - {$material->name} (Tồn: {$quantity})"
                    ]);
                }
            }
        }

        // Get products
        if (in_array($itemType, ['all', 'product'])) {
            $products = Product::whereHas('warehouseMaterials', function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'product')
                    ->where('quantity', '>', 0);
            })->with(['warehouseMaterials' => function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'product');
            }])->get();

            foreach ($products as $product) {
                $quantity = $product->warehouseMaterials->sum('quantity');
                if ($quantity > 0) {
                    $items->push([
                        'id' => $product->id,
                        'type' => 'product',
                        'code' => $product->code,
                        'name' => $product->name,
                        'unit' => 'Cái', // Products typically use "Cái" as unit
                        'available_quantity' => $quantity,
                        'display_name' => "{$product->code} - {$product->name} (Tồn: {$quantity})"
                    ]);
                }
            }
        }

        // Get goods
        if (in_array($itemType, ['all', 'good'])) {
            $goods = Good::whereHas('warehouseMaterials', function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'good')
                    ->where('quantity', '>', 0);
            })->with(['warehouseMaterials' => function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'good');
            }])->get();

            foreach ($goods as $good) {
                $quantity = $good->warehouseMaterials->sum('quantity');
                if ($quantity > 0) {
                    $items->push([
                        'id' => $good->id,
                        'type' => 'good',
                        'code' => $good->code,
                        'name' => $good->name,
                        'unit' => $good->unit ?? 'Cái',
                        'available_quantity' => $quantity,
                        'display_name' => "{$good->code} - {$good->name} (Tồn: {$quantity})"
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'items' => $items->sortBy('name')->values()
        ]);
    }

    /**
     * Get all available items for dispatch from all warehouses.
     * Returns thành phẩm (products) and hàng hóa (goods) for dispatch
     */
    public function getAllAvailableItems(Request $request)
    {
        $items = collect();

        // Lấy danh sách kho active 1 lần để tái sử dụng
        $activeWarehouses = \App\Models\Warehouse::where('status', 'active')
            ->where('is_hidden', false)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Get products from products table (thành phẩm)
        $products = Product::where('status', 'active')
            ->where('is_hidden', false)
            ->with(['warehouseMaterials' => function ($query) {
                $query->where('item_type', 'product')
                    ->with('warehouse');
            }])->get();

        foreach ($products as $product) {
            // Tính tồn kho theo từng kho cho sản phẩm này, đảm bảo không bị thiếu và đúng số lượng
            $warehouses = [];
            // Gom nhóm quantity theo warehouse_id
            $byWarehouse = [];
            if ($product->warehouseMaterials && $product->warehouseMaterials->isNotEmpty()) {
                foreach ($product->warehouseMaterials as $warehouseMaterial) {
                    $wid = (int) $warehouseMaterial->warehouse_id;
                    $byWarehouse[$wid] = ($byWarehouse[$wid] ?? 0) + ((int) ($warehouseMaterial->quantity ?? 0));
                }
            }
            // Duyệt tất cả kho active và điền quantity nếu có, nếu không thì 0
            foreach ($activeWarehouses as $warehouse) {
                $qty = (int) ($byWarehouse[$warehouse->id] ?? 0);
                $warehouses[] = [
                    'warehouse_id' => $warehouse->id,
                    'warehouse_name' => $warehouse->name,
                    'quantity' => $qty
                ];
            }

            // Add item - always include products
            $items->push([
                'id' => $product->id,
                'type' => 'product',
                'code' => $product->code,
                'name' => $product->name,
                'unit' => 'Cái', // Default unit for products
                'warehouses' => $warehouses,
                'display_name' => "{$product->code} - {$product->name}"
            ]);
        }

        // Get goods from goods table (hàng hóa)
        $goods = Good::where('status', 'active')
            ->where('is_hidden', false)
            ->with(['warehouseMaterials' => function ($query) {
                $query->where('item_type', 'good')
                    ->with('warehouse');
            }])->get();

        foreach ($goods as $good) {
            // Tính tồn kho theo từng kho cho hàng hóa này
            $warehouses = [];
            $byWarehouse = [];
            if ($good->warehouseMaterials && $good->warehouseMaterials->isNotEmpty()) {
                foreach ($good->warehouseMaterials as $warehouseMaterial) {
                    $wid = (int) $warehouseMaterial->warehouse_id;
                    $byWarehouse[$wid] = ($byWarehouse[$wid] ?? 0) + ((int) ($warehouseMaterial->quantity ?? 0));
                }
            }
            foreach ($activeWarehouses as $warehouse) {
                $qty = (int) ($byWarehouse[$warehouse->id] ?? 0);
                $warehouses[] = [
                    'warehouse_id' => $warehouse->id,
                    'warehouse_name' => $warehouse->name,
                    'quantity' => $qty
                ];
            }

            // Add item - always include goods
            $items->push([
                'id' => $good->id,
                'type' => 'good',
                'code' => $good->code,
                'name' => $good->name,
                'unit' => $good->unit ?? 'Cái', // Use good's unit or default to 'Cái'
                'warehouses' => $warehouses,
                'display_name' => "{$good->code} - {$good->name}"
            ]);
        }

        // If no items found, return empty but with debug info
        if ($items->isEmpty()) {
            $totalProducts = Product::count();
            $totalGoods = Good::count();
            $productsWithInventory = Product::whereHas('warehouseMaterials', function ($query) {
                $query->where('item_type', 'product')->where('quantity', '>', 0);
            })->count();
            $goodsWithInventory = Good::whereHas('warehouseMaterials', function ($query) {
                $query->where('item_type', 'good')->where('quantity', '>', 0);
            })->count();

            return response()->json([
                'success' => true,
                'items' => [],
                'debug' => [
                    'total_products' => $totalProducts,
                    'total_goods' => $totalGoods,
                    'products_with_inventory' => $productsWithInventory,
                    'goods_with_inventory' => $goodsWithInventory,
                    'message' => 'Không tìm thấy thành phẩm hoặc hàng hóa nào có tồn kho. Vui lòng kiểm tra dữ liệu trong bảng products, goods và warehouse_materials.'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'items' => $items->sortBy('name')->values()
        ]);
    }

    /**
     * Create warranty for dispatch item
     */
    private function createWarrantyForDispatchItem(Dispatch $dispatch, DispatchItem $dispatchItem, Request $request)
    {
        // Initialize default values
        $warrantyPeriodMonths = 12; // Default 12 months
        $warrantyStartDate = $dispatch->dispatch_date;
        $warrantyEndDate = null;

        // Determine warranty period
        if ($dispatch->dispatch_type === 'rental') {
            // Lấy thời gian từ phiếu cho thuê
            $rental = Rental::find($dispatch->project_id);
            if ($rental) {
                $startDate = Carbon::parse($rental->rental_date);
                $endDate   = Carbon::parse($rental->due_date);
                $warrantyPeriodMonths = max(1, $startDate->diffInMonths($endDate) ?: 1);
                $warrantyStartDate = $startDate;
                $warrantyEndDate   = $endDate;
                
                Log::info('Rental warranty dates calculated', [
                    'rental_id' => $rental->id,
                    'rental_date' => $rental->rental_date,
                    'due_date' => $rental->due_date,
                    'warranty_period_months' => $warrantyPeriodMonths,
                    'warranty_start_date' => $warrantyStartDate,
                    'warranty_end_date' => $warrantyEndDate
                ]);
            } else {
                Log::warning('Rental not found for dispatch, using default warranty period', [
                    'dispatch_id' => $dispatch->id,
                    'project_id' => $dispatch->project_id
                ]);
            }
        } else {
            // Parse warranty period from request or use default
            if ($request->warranty_period) {
                // Extract number from warranty period string (e.g., "12 tháng" -> 12)
                preg_match('/(\d+)/', $request->warranty_period, $matches);
                if (!empty($matches[1])) {
                    $warrantyPeriodMonths = (int) $matches[1];
                }
            }
        }

        // Calculate warranty end date if not already set (for non-rental or rental not found)
        if (!$warrantyEndDate) {
            $warrantyEndDate = Carbon::parse($warrantyStartDate)->copy()->addMonths($warrantyPeriodMonths);
        }

        // Get item details
        $item = null;
        switch ($dispatchItem->item_type) {
            case 'material':
                $item = Material::find($dispatchItem->item_id);
                break;
            case 'product':
                $item = Product::find($dispatchItem->item_id);
                break;
            case 'good':
                $item = Good::find($dispatchItem->item_id);
                break;
        }

        if (!$item) {
            Log::warning('Item not found, skipping warranty creation', [
                'item_type' => $dispatchItem->item_type,
                'item_id' => $dispatchItem->item_id
            ]);
            return; // Skip if item not found
        }

        Log::info('Item found for warranty', [
            'item_name' => $item->name,
            'item_code' => $item->code
        ]);

        // Create warranty for the dispatch 
        // For regular projects: one warranty per project (shared across dispatches)
        // For rentals: one warranty per rental (shared across all dispatches of same rental)
        $existingWarranty = null;
        
        if ($dispatch->dispatch_type === 'rental') {
            Log::info('Rental dispatch: checking for existing warranty in same rental (project_id)', [
                'dispatch_id' => $dispatch->id,
                'rental_id' => $dispatch->project_id,
                'dispatch_type' => $dispatch->dispatch_type
            ]);

            // For rental dispatches, share warranty across all dispatches of the same rental
            $existingWarranty = Warranty::where('item_type', 'project')
                ->whereHas('dispatch', function ($query) use ($dispatch) {
                $query->where('project_id', $dispatch->project_id)
                    ->where('dispatch_type', 'rental'); // Only rental dispatches share warranty
                })
                ->first();
                
            Log::info('Rental warranty check result', [
                'found_existing' => $existingWarranty ? true : false,
                'existing_warranty_id' => $existingWarranty ? $existingWarranty->id : null,
                'existing_warranty_code' => $existingWarranty ? $existingWarranty->warranty_code : null
            ]);
            
        } elseif ($dispatch->project_id) {
            Log::info('Project dispatch: checking for existing warranty in project (any dispatch)', [
                'project_id' => $dispatch->project_id,
                'dispatch_type' => $dispatch->dispatch_type
            ]);

            // For regular project dispatches, share warranty across all dispatches of the same project
            $existingWarranty = Warranty::where('item_type', 'project')
                ->whereHas('dispatch', function ($query) use ($dispatch) {
                $query->where('project_id', $dispatch->project_id)
                    ->where('dispatch_type', '!=', 'rental'); // Exclude rentals from shared warranty
                })
                ->first();

            Log::info('Project warranty check result', [
                'found_existing' => $existingWarranty ? true : false,
                'existing_warranty_id' => $existingWarranty ? $existingWarranty->id : null,
                'existing_warranty_code' => $existingWarranty ? $existingWarranty->warranty_code : null
            ]);
        } else {
            Log::info('No project_id, checking within same dispatch only');

            // For non-project dispatches, check only within the same dispatch
            $existingWarranty = Warranty::where('item_type', 'project')
                ->where('dispatch_id', $dispatch->id)
                ->first();
        }

        if (!$existingWarranty) {
            Log::info('Creating new warranty for project (no existing warranty found)');

            // Lấy tất cả item cần bảo hành: bao gồm 'contract' và 'general', loại trừ 'backup'
            $contractItems = $dispatch->items()->where('category', '!=', 'backup')->get();
            $allItemsInfo = [];
            $allSerialNumbers = [];

            Log::info('Processing contract items for warranty (excluding backup)', [
                'dispatch_id' => $dispatch->id,
                'total_contract_items' => $contractItems->count(),
                'total_all_items' => $dispatch->items->count()
            ]);

            foreach ($contractItems as $index => $item) {
                Log::info("Processing item $index", [
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'quantity' => $item->quantity
                ]);

                // Get item details
                $itemDetails = null;
                switch ($item->item_type) {
                    case 'material':
                        $itemDetails = Material::find($item->item_id);
                        break;
                    case 'product':
                        $itemDetails = Product::find($item->item_id);
                        break;
                    case 'good':
                        $itemDetails = Good::find($item->item_id);
                        break;
                }

                if ($itemDetails) {
                    $itemInfo = "{$itemDetails->code} - {$itemDetails->name} (SL: {$item->quantity})";
                    $allItemsInfo[] = $itemInfo;
                    Log::info("Added item to warranty", ['item_info' => $itemInfo]);
                } else {
                    Log::warning("Item details not found", [
                        'item_type' => $item->item_type,
                        'item_id' => $item->item_id
                    ]);
                }

                // Collect serial numbers
                if (!empty($item->serial_numbers) && is_array($item->serial_numbers)) {
                    $allSerialNumbers = array_merge($allSerialNumbers, $item->serial_numbers);
                    Log::info("Added serial numbers", ['serials' => $item->serial_numbers]);
                }
            }

            Log::info('Final warranty items summary', [
                'total_items_info' => count($allItemsInfo),
                'items_info' => $allItemsInfo,
                'total_serial_numbers' => count($allSerialNumbers)
            ]);

            // Xác định project_name dựa trên dispatch_type
            $projectNameForWarranty = $dispatch->project_receiver;
            $notesExtra = '';
            if ($dispatch->dispatch_type === 'rental' && $dispatch->project_id) {
                $rental = Rental::find($dispatch->project_id);
                if ($rental) {
                    $projectNameForWarranty = $rental->rental_name;
                    $notesExtra = " - Cho thuê: {$rental->rental_name}";
                }
            } elseif ($dispatch->project_id && $dispatch->project) {
                $notesExtra = " - Dự án: {$dispatch->project->project_name}";
            }

            $warranty = Warranty::create([
                'warranty_code' => Warranty::generateWarrantyCode(),
                'dispatch_id' => $dispatch->id,
                'dispatch_item_id' => $dispatchItem->id, // Keep reference to first item for compatibility
                'item_type' => 'project', // Mark as project-wide warranty
                'item_id' => $dispatch->project_id ?? 0, // Use project_id as item_id
                'serial_number' => !empty($allSerialNumbers) ? implode(', ', array_unique($allSerialNumbers)) : null,
                'customer_name' => $dispatch->project_receiver,
                'customer_phone' => null, // Can be added to form later
                'customer_email' => null, // Can be added to form later
                'customer_address' => null, // Can be added to form later
                'project_name' => $projectNameForWarranty,
                'purchase_date' => $dispatch->dispatch_date,
                'warranty_start_date' => $warrantyStartDate,
                'warranty_end_date' => $warrantyEndDate,
                'warranty_period_months' => $warrantyPeriodMonths,
                'warranty_type' => 'standard',
                'status' => 'active',
                'warranty_terms' => $this->getProjectWarrantyTerms($allItemsInfo),
                'notes' => "Bảo hành tự động tạo từ phiếu xuất {$dispatch->dispatch_code}" . $notesExtra .
                    "\nBao gồm các sản phẩm: " . implode(', ', $allItemsInfo),
                'created_by' => Auth::id() ?? 1,
                'activated_at' => now(),
            ]);

            // Ghi nhật ký tạo mới bảo hành
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'warranties',
                    'Tạo mới bảo hành: ' . $warranty->warranty_code,
                    null,
                    $warranty->toArray()
                );
            }

            // Generate QR code
            $warranty->generateQRCode();

            Log::info('New project warranty created successfully', [
                'warranty_id' => $warranty->id,
                'warranty_code' => $warranty->warranty_code,
                'items_count' => count($allItemsInfo)
            ]);

            // Trả về thông tin để UI hiển thị thông báo
            return [
                'action' => 'created',
                'warranty_id' => $warranty->id,
                'warranty_code' => $warranty->warranty_code,
                'items_count' => count($allItemsInfo),
            ];
        } else {
            Log::info('Updating existing project warranty instead of creating new one', [
                'existing_warranty_id' => $existingWarranty->id,
                'existing_warranty_code' => $existingWarranty->warranty_code
            ]);

            // Update existing warranty with additional information from new dispatch
            // Lấy item để cập nhật bảo hành: bao gồm 'contract' và 'general', loại trừ 'backup'
            $contractItems = $dispatch->items()->where('category', '!=', 'backup')->get();
            $newItemsInfo = [];
            $newSerialNumbers = [];

            foreach ($contractItems as $item) {
                // Get item details
                $itemDetails = null;
                switch ($item->item_type) {
                    case 'material':
                        $itemDetails = Material::find($item->item_id);
                        break;
                    case 'product':
                        $itemDetails = Product::find($item->item_id);
                        break;
                    case 'good':
                        $itemDetails = Good::find($item->item_id);
                        break;
                }

                if ($itemDetails) {
                    $newItemsInfo[] = "{$itemDetails->code} - {$itemDetails->name} (SL: {$item->quantity})";
                }

                // Collect serial numbers
                if (!empty($item->serial_numbers) && is_array($item->serial_numbers)) {
                    $newSerialNumbers = array_merge($newSerialNumbers, $item->serial_numbers);
                }
            }

            // Merge with existing serial numbers
            $existingSerials = $existingWarranty->serial_number ? explode(', ', $existingWarranty->serial_number) : [];
            $allSerials = array_unique(array_merge($existingSerials, $newSerialNumbers));

            // Update notes to include new dispatch information
            $additionalNote = "\nPhiếu xuất bổ sung: {$dispatch->dispatch_code} - " . now()->format('d/m/Y H:i') .
                "\nThêm sản phẩm: " . implode(', ', $newItemsInfo);

            $existingWarranty->update([
                'serial_number' => !empty($allSerials) ? implode(', ', $allSerials) : $existingWarranty->serial_number,
                'notes' => $existingWarranty->notes . $additionalNote,
            ]);

            Log::info('Existing project warranty updated successfully', [
                'added_items_count' => count($newItemsInfo)
            ]);

            return [
                'action' => 'updated',
                'warranty_id' => $existingWarranty->id,
                'warranty_code' => $existingWarranty->warranty_code,
                'items_count' => count($newItemsInfo),
            ];
        }

        Log::info('=== WARRANTY CREATION/UPDATE COMPLETED ===');
        return null;
    }

    /**
     * Get default warranty terms based on item type
     */
    private function getDefaultWarrantyTerms($item)
    {
        $terms = [
            "1. Sản phẩm được bảo hành miễn phí trong thời gian bảo hành.",
            "2. Bảo hành không áp dụng cho các trường hợp hư hỏng do người sử dụng.",
            "3. Sản phẩm phải còn nguyên tem bảo hành và không có dấu hiệu tác động vật lý.",
            "4. Khách hàng cần mang theo phiếu bảo hành khi yêu cầu bảo hành.",
            "5. Thời gian bảo hành được tính từ ngày xuất kho."
        ];

        if ($item) {
            $terms[] = "6. Sản phẩm: {$item->name} - Mã: {$item->code}";
        }

        return implode("\n", $terms);
    }

    /**
     * Get warranty terms for project-wide warranty
     */
    private function getProjectWarrantyTerms($allItemsInfo)
    {
        $terms = [
            "1. Toàn bộ sản phẩm trong dự án được bảo hành miễn phí trong thời gian bảo hành.",
            "2. Bảo hành không áp dụng cho các trường hợp hư hỏng do người sử dụng.",
            "3. Sản phẩm phải còn nguyên tem bảo hành và không có dấu hiệu tác động vật lý.",
            "4. Khách hàng cần mang theo phiếu bảo hành khi yêu cầu bảo hành.",
            "5. Thời gian bảo hành được tính từ ngày xuất kho.",
            "6. Bảo hành áp dụng cho tất cả sản phẩm trong dự án:"
        ];

        if (!empty($allItemsInfo)) {
            foreach ($allItemsInfo as $index => $itemInfo) {
                $terms[] = "   " . ($index + 1) . ". " . $itemInfo;
            }
        }

        return implode("\n", $terms);
    }

    /**
     * Chuẩn hóa mảng serial từ nhiều kiểu đầu vào (array | json | csv)
     */
    private function normalizeSerialArray($input)
    {
        $serials = [];
        if (is_array($input)) {
            $serials = $input;
        } elseif (is_string($input)) {
            $trimmed = trim($input);
            if ($trimmed === '') {
                $serials = [];
            } elseif (str_starts_with($trimmed, '[')) {
                $decoded = json_decode($trimmed, true);
                $serials = is_array($decoded) ? $decoded : [];
            } else {
                $serials = array_map('trim', explode(',', $trimmed));
            }
        }

        // Chuẩn hóa: trim, loại bỏ rỗng, unique theo giá trị (không phân biệt hoa thường)
        $serials = array_values(array_filter(array_map(function ($s) {
            return is_string($s) ? trim($s) : $s;
        }, $serials), function ($s) {
            return !empty($s);
        }));

        if (!empty($serials)) {
            $lowerMap = [];
            $unique = [];
            foreach ($serials as $s) {
                $key = mb_strtolower($s);
                if (!isset($lowerMap[$key])) {
                    $lowerMap[$key] = true;
                    $unique[] = $s;
                }
            }
            return $unique;
        }

        return $serials;
    }

    /**
     * Check if dispatch contains only backup items (no warranty needed)
     */
    private function isBackupOnlyDispatch($dispatch)
    {
        Log::info('isBackupOnlyDispatch check', [
            'dispatch_id' => $dispatch->id,
            'dispatch_detail' => $dispatch->dispatch_detail,
            'items_count' => $dispatch->items->count(),
            'items_categories' => $dispatch->items->pluck('category')->toArray()
        ]);

        // If dispatch_detail is explicitly 'backup', it's backup-only
        if ($dispatch->dispatch_detail === 'backup') {
            Log::info('isBackupOnlyDispatch: dispatch_detail is backup, returning true');
            return true;
        }

        // If dispatch_detail is 'all', check if all items are backup category
        if ($dispatch->dispatch_detail === 'all') {
            $allItemsAreBackup = true;
            foreach ($dispatch->items as $item) {
                if ($item->category !== 'backup') {
                    $allItemsAreBackup = false;
                    break;
                }
            }
            Log::info('isBackupOnlyDispatch: dispatch_detail is all, allItemsAreBackup=' . ($allItemsAreBackup ? 'true' : 'false'));
            return $allItemsAreBackup;
        }

        Log::info('isBackupOnlyDispatch: returning false (dispatch_detail=' . $dispatch->dispatch_detail . ')');
        return false;
    }

    /**
     * Get next available product unit for N/A serial from assembly (for dispatch)
     */
    private function getNextAvailableProductUnitForDispatch(int $productId, ?int $projectId = null): ?array
    {
        // Tìm assembly có thành phẩm này với serial N/A, ưu tiên theo project
        $query = \App\Models\AssemblyProduct::where('product_id', $productId)
            ->where(function ($q) {
                $q->whereNull('serials')
                    ->orWhere('serials', '')
                    ->orWhere('serials', 'N/A')
                    ->orWhere('serials', 'NA');
            });

        // Nếu có project context, ưu tiên assembly của project đó
        if ($projectId) {
            $projectAssembly = $query->clone()
                ->whereHas('assembly', function($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                })
                ->whereNotNull('product_unit')
                ->orderBy('product_unit')
                ->first();
            
            if ($projectAssembly) {
                // Check if this specific unit is available (not dispatched yet)
                $isDispatched = \App\Models\DispatchItem::where('assembly_id', $projectAssembly->assembly_id)
                    ->where('item_type', 'product')
                    ->where('item_id', $productId)
                    ->where('product_unit', $projectAssembly->product_unit)
                    ->exists();
                
                if (!$isDispatched) {
                    return [
                        'assembly_id' => $projectAssembly->assembly_id,
                        'product_unit' => $projectAssembly->product_unit
                    ];
                }
            }
        }

        // Fallback: tìm assembly khác theo thứ tự product_unit
        $assemblies = $query->whereNotNull('product_unit')->orderBy('product_unit')->get();
        foreach ($assemblies as $assembly) {
            // Check if this specific unit is available (not dispatched yet)
            $isDispatched = \App\Models\DispatchItem::where('assembly_id', $assembly->assembly_id)
                ->where('item_type', 'product')
                ->where('item_id', $productId)
                ->where('product_unit', $assembly->product_unit)
                ->exists();
            
            if (!$isDispatched) {
                return [
                    'assembly_id' => $assembly->assembly_id,
                    'product_unit' => $assembly->product_unit
                ];
            }
        }

        return null;
    }

    /**
     * Get next available product unit for N/A serial from assembly with offset (for dispatch)
     */
    private function getNextAvailableProductUnitForDispatchWithOffset(int $productId, ?int $projectId = null, int $offset = 0): ?array
    {
        // Tìm assembly có thành phẩm này với serial N/A, ưu tiên theo project
        $query = \App\Models\AssemblyProduct::where('product_id', $productId)
            ->where(function ($q) {
                $q->whereNull('serials')
                    ->orWhere('serials', '')
                    ->orWhere('serials', 'N/A')
                    ->orWhere('serials', 'NA');
            });

        // Nếu có project context, ưu tiên assembly của project đó
        if ($projectId) {
            $projectAssemblies = $query->clone()
                ->whereHas('assembly', function($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                })
                ->whereNotNull('product_unit')
                ->orderBy('product_unit')
                ->get();
            
            foreach ($projectAssemblies as $assembly) {
                // Parse product_unit để tìm unit phù hợp với offset
                $productUnitValue = $assembly->product_unit;
                $productUnits = [];
                
                if (is_string($productUnitValue)) {
                    $productUnits = json_decode($productUnitValue, true) ?: [$productUnitValue];
                } elseif (is_array($productUnitValue)) {
                    $productUnits = $productUnitValue;
                } else {
                    $productUnits = [$productUnitValue];
                }
                
                // Tìm unit có index = offset
                if (isset($productUnits[$offset])) {
                    $targetUnit = $productUnits[$offset];
                    
                    // Check if this specific unit is available (not dispatched yet)
                    $isDispatched = \App\Models\DispatchItem::where('assembly_id', $assembly->assembly_id)
                        ->where('item_type', 'product')
                        ->where('item_id', $productId)
                        ->where('product_unit', $targetUnit)
                        ->exists();
                    
                    if (!$isDispatched) {
                        return [
                            'assembly_id' => $assembly->assembly_id,
                            'product_unit' => $targetUnit
                        ];
                    }
                }
            }
        }

        // Fallback: tìm assembly khác theo thứ tự product_unit
        $assemblies = $query->whereNotNull('product_unit')->orderBy('product_unit')->get();
        foreach ($assemblies as $assembly) {
            // Parse product_unit để tìm unit phù hợp với offset
            $productUnitValue = $assembly->product_unit;
            $productUnits = [];
            
            if (is_string($productUnitValue)) {
                $productUnits = json_decode($productUnitValue, true) ?: [$productUnitValue];
            } elseif (is_array($productUnitValue)) {
                $productUnits = $productUnitValue;
            } else {
                $productUnits = [$productUnitValue];
            }
            
            // Tìm unit có index = offset
            if (isset($productUnits[$offset])) {
                $targetUnit = $productUnits[$offset];
                
                // Check if this specific unit is available (not dispatched yet)
                $isDispatched = \App\Models\DispatchItem::where('assembly_id', $assembly->assembly_id)
                    ->where('item_type', 'product')
                    ->where('item_id', $productId)
                    ->where('product_unit', $targetUnit)
                    ->exists();
                
                if (!$isDispatched) {
                    return [
                        'assembly_id' => $assembly->assembly_id,
                        'product_unit' => $targetUnit
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Find available product unit in specific assembly (for dispatch)
     */
    private function findAvailableUnitInAssemblyForDispatch(int $assemblyId, int $productId): ?int
    {
        // Lấy tất cả product_unit đã xuất cho assembly này
        $usedUnits = \App\Models\DispatchItem::where('assembly_id', $assemblyId)
            ->where('item_type', 'product')
            ->where('item_id', $productId)
            ->whereNotNull('product_unit')
            ->pluck('product_unit')
            ->toArray();

        // Lấy tất cả product_unit có trong assembly này từ assembly_products
        $availableUnits = \App\Models\AssemblyProduct::where('assembly_id', $assemblyId)
            ->where('product_id', $productId)
            ->whereNotNull('product_unit')
            ->pluck('product_unit')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Tìm unit đầu tiên chưa được xuất
        foreach ($availableUnits as $unit) {
            if (!in_array($unit, $usedUnits)) {
                return $unit;
            }
        }

        return null;
    }

    /**
     * Resolve assembly_id and product_unit for a given product in context of a project or selected serial
     */
    public function resolveAssemblyForProduct(Request $request)
    {
        $productId = (int) $request->get('product_id');
        $projectId = $request->has('project_id') ? (int) $request->get('project_id') : null;
        $serial = trim((string) $request->get('serial', ''));

        if (!$productId) {
            return response()->json([
                'success' => false,
                'message' => 'product_id is required'
            ], 422);
        }

        try {
            // 1) If serial is provided, try to find assembly by matching either product serials or material serials for this product
            if ($serial !== '') {
                // Try match in assembly_products.serials
                $ap = DB::table('assembly_products')
                    ->where('product_id', $productId)
                    ->whereNotNull('serials')
                    ->where('serials', '!=', '')
                    ->where(function($q) use ($serial) {
                        $q->where('serials', $serial)
                          ->orWhereRaw('FIND_IN_SET(?, serials) > 0', [$serial]);
                    })
                    ->orderBy('id')
                    ->first();

                if ($ap) {
                    Log::info('resolveAssemblyForProduct: Found assembly_products match', [
                        'product_id' => $productId,
                        'serial' => $serial,
                        'assembly_id' => $ap->assembly_id,
                        'product_unit' => $ap->product_unit,
                        'assembly_products_data' => $ap
                    ]);

                    return response()->json([
                        'success' => true,
                        'assembly_id' => (int) $ap->assembly_id,
                        'product_unit' => $ap->product_unit !== null ? (int) $ap->product_unit : null
                    ]);
                }

                // Try match in assembly_materials.serial scoped by target_product_id
                $am = DB::table('assembly_materials')
                    ->where('target_product_id', $productId)
                    ->whereNotNull('serial')
                    ->where('serial', '!=', '')
                    ->where(function($q) use ($serial) {
                        $q->where('serial', $serial)
                          ->orWhereRaw('FIND_IN_SET(?, serial) > 0', [$serial]);
                    })
                    ->orderBy('id')
                    ->first();

                if ($am) {
                    return response()->json([
                        'success' => true,
                        'assembly_id' => (int) $am->assembly_id,
                        'product_unit' => isset($am->product_unit) ? (int) $am->product_unit : null
                    ]);
                }
            }

            // 2) Fallback: find next available N/A unit by project context
            $unitInfo = $this->getNextAvailableProductUnitForDispatch($productId, $projectId);

            if ($unitInfo) {
                return response()->json([
                    'success' => true,
                    'assembly_id' => $unitInfo['assembly_id'] ?? null,
                    'product_unit' => $unitInfo['product_unit'] ?? null
                ]);
            }

            return response()->json([
                'success' => true,
                'assembly_id' => null,
                'product_unit' => null
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine category for dispatch item based on dispatch_detail and item data
     */
    private function determineItemCategory($dispatchDetail, $item)
    {
        // If dispatch_detail is contract or backup, all items follow that category
        if ($dispatchDetail === 'contract') {
            return 'contract';
        }

        if ($dispatchDetail === 'backup') {
            return 'backup';
        }

        // If dispatch_detail is 'all', check if item has category specified
        if ($dispatchDetail === 'all') {
            // Check if category is explicitly set in the request
            if (isset($item['category']) && in_array($item['category'], ['contract', 'backup'])) {
                return $item['category'];
            }

            // Fallback: try to determine from item type or other indicators
            // This is for backward compatibility or when category is not explicitly set
            if (isset($item['item_type'])) {
                // You can add custom logic here based on your business rules
                // For now, we'll default to 'general' for mixed dispatches
                return 'general';
            }
        }

        // Default fallback
        return 'general';
    }

    /**
     * Get all projects for dispatch form
     */
    public function getProjects()
    {
        // Lọc dự án theo quyền của nhân viên đang đăng nhập
        $projects = $this->getFilteredProjects();

        return response()->json([
            'success' => true,
            'projects' => $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'project_code' => $project->project_code,
                    'project_name' => $project->project_name,
                    'customer_name' => $project->customer->name ?? '',
                    'warranty_period' => $project->warranty_period,
                    'warranty_period_formatted' => $project->warranty_period_formatted,
                    'display_name' => $project->project_code . ' - ' . $project->project_name . ' (' . ($project->customer->name ?? 'N/A') . ')'
                ];
            })
        ]);
    }

    /**
     * Get available serial numbers for a specific item in a specific warehouse
     * Only returns serials that are not already used in approved dispatches
     */
    public function getItemSerials(Request $request)
    {
        $itemType = $request->get('item_type');
        $itemId = $request->get('item_id');
        $warehouseId = $request->get('warehouse_id');
        $currentDispatchId = $request->get('current_dispatch_id'); // For edit mode

        if (!$itemType || !$itemId || !$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters'
            ]);
        }

        try {
            // Debug: Log query parameters
            Log::info('getItemSerials called with params:', [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'current_dispatch_id' => $currentDispatchId
            ]);

            // Get serials based on item type and warehouse
            $serials = [];
            
            // For goods (hàng hóa), check warehouse_materials table
            if ($itemType === 'good') {
                $warehouseMaterials = \App\Models\WarehouseMaterial::where('warehouse_id', $warehouseId)
                    ->where('material_id', $itemId)
                    ->where('item_type', 'good')
                    ->where('quantity', '>', 0)
                    ->whereNotNull('serial_number')
                    ->get();
                
                foreach ($warehouseMaterials as $wm) {
                    if ($wm->serial_number) {
                        // Handle JSON array format
                        if (is_string($wm->serial_number) && strpos($wm->serial_number, '[') === 0) {
                            $decodedSerials = json_decode($wm->serial_number, true);
                            if (is_array($decodedSerials)) {
                                $serials = array_merge($serials, array_filter($decodedSerials));
                            }
                        } else {
                            // Handle single string format
                            $serials[] = $wm->serial_number;
                        }
                    }
                }
                
                // Remove duplicates and empty values
                $serials = array_values(array_unique(array_filter($serials)));
            } else {
                // For products and materials, prioritize warehouse-scoped source of truth
                // PRODUCTS: read from warehouse_materials only (ensures correct warehouse filtering)
                if ($itemType === 'product') {
                    $serials = [];
                    $warehouseMaterials = \App\Models\WarehouseMaterial::where('warehouse_id', $warehouseId)
                        ->where('material_id', $itemId)
                        ->where('item_type', 'product')
                        ->where('quantity', '>', 0)
                        ->whereNotNull('serial_number')
                        ->get();

                    foreach ($warehouseMaterials as $wm) {
                        if ($wm->serial_number) {
                            if (is_string($wm->serial_number) && strpos($wm->serial_number, '[') === 0) {
                                $decodedSerials = json_decode($wm->serial_number, true);
                                if (is_array($decodedSerials)) {
                                    $serials = array_merge($serials, array_filter($decodedSerials));
                                }
                            } else {
                                $serials[] = $wm->serial_number;
                            }
                        }
                    }

                    // Remove duplicates and empty values
                    $serials = array_values(array_unique(array_filter($serials)));
                } else {
                    // MATERIALS: use Serial table (keeps existing behavior)
                    $serials = \App\Models\Serial::where('type', $itemType)
                        ->where('product_id', $itemId)
                        ->where('warehouse_id', $warehouseId)
                        ->where('status', 'active')
                        ->pluck('serial_number')
                        ->toArray();
                }
            }

            // Debug: Log raw serials found
            Log::info('Raw serials found in database:', [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'count' => count($serials),
                'serials' => $serials
            ]);

            // Get serial numbers that are already used in approved dispatches
            $approvedDispatchItemsQuery = \App\Models\DispatchItem::whereHas('dispatch', function ($query) use ($currentDispatchId) {
                $query->where('status', 'approved');
                // Exclude current dispatch when editing
                if ($currentDispatchId) {
                    $query->where('id', '!=', $currentDispatchId);
                }
            })
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->where('warehouse_id', $warehouseId);

            $usedSerials = $approvedDispatchItemsQuery->get()
                ->pluck('serial_numbers')
                ->flatten()
                ->filter()
                ->toArray();

            // Also exclude any old_serial values recorded in device_codes for approved dispatches
            $approvedDispatchIds = $approvedDispatchItemsQuery->pluck('dispatch_id')->unique()->toArray();

            if (!empty($approvedDispatchIds)) {
                $oldSerials = \App\Models\DeviceCode::whereIn('dispatch_id', $approvedDispatchIds)
                    ->where('product_id', $itemId)
                    // Match by item_type if present; otherwise accept null item_type
                    ->where(function ($q) use ($itemType) {
                        $q->whereNull('item_type')->orWhere('item_type', $itemType);
                    })
                    ->pluck('old_serial')
                    ->filter()
                    ->toArray();

                // Merge and de-duplicate
                $usedSerials = array_values(array_unique(array_merge($usedSerials, array_filter($oldSerials))));
            }

            // Debug: Log used serials
            Log::info('Used serials found:', [
                'count' => count($usedSerials),
                'used_serials' => $usedSerials
            ]);

            // Filter out used serials (including old_serial replacements)
            $availableSerials = array_diff($serials, $usedSerials);

            // Debug: Log final result
            Log::info('Final serial calculation:', [
                'total_serials' => count($serials),
                'used_serials' => count($usedSerials),
                'available_serials' => count($availableSerials),
                'available_serial_list' => array_values($availableSerials)
            ]);

            return response()->json([
                'success' => true,
                'serials' => array_values($availableSerials), // Re-index array
                'total_serials' => count($serials),
                'used_serials' => count($usedSerials),
                'available_serials' => count($availableSerials)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getItemSerials:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching serials: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check for duplicate serial numbers with already approved dispatches
     * Only check real serials, skip virtual serials (N/A-0, N/A-1...)
     */
    private function checkDuplicateSerials(Dispatch $dispatch)
    {
        $duplicates = [];

        foreach ($dispatch->items as $dispatchItem) {
            if (empty($dispatchItem->serial_numbers) || !is_array($dispatchItem->serial_numbers)) {
                continue;
            }

            foreach ($dispatchItem->serial_numbers as $serial) {
                if (empty(trim($serial))) continue;
                
                // Skip virtual serials (N/A-0, N/A-1, etc.) - only check real serials
                if (strpos(trim($serial), 'N/A-') === 0) {
                    continue;
                }

                // Check if this serial exists in any approved dispatch (excluding current one)
                $existingItem = DispatchItem::whereHas('dispatch', function ($query) use ($dispatch) {
                    $query->where('status', 'approved')
                        ->where('id', '!=', $dispatch->id);
                })
                    ->where('item_type', $dispatchItem->item_type)
                    ->where('item_id', $dispatchItem->item_id)
                    ->where('warehouse_id', $dispatchItem->warehouse_id)
                    ->whereJsonContains('serial_numbers', $serial)
                    ->with('dispatch')
                    ->first();

                if ($existingItem) {
                    $duplicates[] = [
                        'serial' => $serial,
                        'item_type' => $dispatchItem->item_type,
                        'item_id' => $dispatchItem->item_id,
                        'item_code' => $dispatchItem->item_code ?? 'N/A',
                        'item_name' => $dispatchItem->item_name ?? 'N/A',
                        'existing_dispatch_code' => $existingItem->dispatch->dispatch_code,
                        'existing_dispatch_id' => $existingItem->dispatch->id,
                    ];
                }
            }
        }

        return $duplicates;
    }

    /**
     * Count used N/A units for a given assembly-product in approved dispatches.
     * A "used NA unit" is computed as quantity minus count of real serials present in serial_numbers.
     */
    private function countUsedNaUnits(int $assemblyId, int $productId): int
    {
        $approvedItems = DispatchItem::where('assembly_id', $assemblyId)
            ->where('item_type', 'product')
            ->where('item_id', $productId)
            ->whereHas('dispatch', function ($q) {
                $q->where('status', 'approved');
            })
            ->get(['quantity', 'serial_numbers']);

        $used = 0;
        foreach ($approvedItems as $di) {
            $qty = (int)($di->quantity ?? 0);
            $serials = is_array($di->serial_numbers) ? $di->serial_numbers : [];
            // Count real serials (exclude virtual N/A-* and plain N/A/NA, and blanks)
            $realSerialCount = 0;
            foreach ($serials as $s) {
                $s = is_string($s) ? trim($s) : '';
                if ($s === '' || strtoupper($s) === 'N/A' || strtoupper($s) === 'NA' || strpos($s, 'N/A-') === 0) {
                    continue;
                }
                $realSerialCount++;
            }
            $used += max(0, $qty - $realSerialCount);
        }
        return $used;
    }

    /**
     * Get available NA capacity for an assembly-product, constrained by testing receipts into a warehouse.
     */
    private function getNaCapacityForAssemblyProduct(int $assemblyId, int $productId, ?int $warehouseId, bool $requireProductSpecific = true): int
    {
        // 1) Warehouse filter by testing receipts
        if ($warehouseId) {
            $existsProductSpecific = DB::table('testings as t')
                ->join('testing_items as ti', 'ti.testing_id', '=', 't.id')
                ->where('t.assembly_id', $assemblyId)
                ->whereIn('t.status', ['completed', 'approved', 'received'])
                ->where('t.success_warehouse_id', $warehouseId)
                ->whereIn('ti.item_type', ['finished_product', 'product'])
                ->where('ti.product_id', $productId)
                ->exists();

            if (!$existsProductSpecific) {
                if ($requireProductSpecific) {
                    return 0; // must have product-specific receipt
                }
                $existsAny = DB::table('testings as t')
                    ->where('t.assembly_id', $assemblyId)
                    ->whereIn('t.status', ['completed', 'approved', 'received'])
                    ->where('t.success_warehouse_id', $warehouseId)
                    ->exists();
                if (!$existsAny) {
                    return 0;
                }
            }
        }

        // 2) Determine total NA slots from the AssemblyProduct row: units count minus real serials
        $ap = \App\Models\AssemblyProduct::where('assembly_id', $assemblyId)
            ->where('product_id', $productId)
            ->first();
        if (!$ap) { return 0; }

        // Parse product_unit to array and count units
        $units = [];
        if (is_string($ap->product_unit)) {
            $decoded = json_decode($ap->product_unit, true);
            if (is_array($decoded)) { $units = $decoded; }
            elseif ($ap->product_unit !== '') { $units = [$ap->product_unit]; }
        } elseif (is_array($ap->product_unit)) {
            $units = $ap->product_unit;
        } elseif ($ap->product_unit !== null) {
            $units = [$ap->product_unit];
        }
        $unitsCount = count($units);

        // Count real serials present in assembly serials
        $serialCount = 0;
        if ($ap->serials && $ap->serials !== 'N/A' && $ap->serials !== 'NA') {
            $parts = preg_split('/[\s,;|\/]+/', (string)$ap->serials, -1, PREG_SPLIT_NO_EMPTY);
            $serialCount = is_array($parts) ? count($parts) : 1;
        }

        // If no units defined and no serials, treat as one NA slot (legacy behavior)
        $baseCapacity = $unitsCount > 0 ? max(0, $unitsCount - $serialCount) : ($serialCount > 0 ? 0 : 1);

        // 3) Subtract used NA units from approved dispatches
        $used = $this->countUsedNaUnits($assemblyId, $productId);
        return max(0, $baseCapacity - $used);
    }

    /**
     * Check if item has sufficient stock.
     */
    private function checkItemStock($itemType, $itemId, $warehouseId, $requestedQuantity)
    {
        try {
            Log::info("Checking stock in database:", [
                'item_type' => $itemType,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'requested_quantity' => $requestedQuantity
            ]);

            $warehouseMaterial = \App\Models\WarehouseMaterial::where('item_type', $itemType)
                ->where('material_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            Log::info("Database query result:", [
                'found_record' => $warehouseMaterial ? true : false,
                'quantity' => $warehouseMaterial ? $warehouseMaterial->quantity : 'N/A'
            ]);

            $currentStock = $warehouseMaterial ? $warehouseMaterial->quantity : 0;
            $sufficient = $currentStock >= $requestedQuantity;

            // Get item name for error message
            $itemName = 'Unknown';
            if ($itemType === 'material') {
                $item = \App\Models\Material::find($itemId);
            } elseif ($itemType === 'product') {
                $item = \App\Models\Product::find($itemId);
            } elseif ($itemType === 'good') {
                $item = \App\Models\Good::find($itemId);
            }

            if (isset($item)) {
                $itemName = "{$item->code} - {$item->name}";
            }

            return [
                'sufficient' => $sufficient,
                'current_stock' => $currentStock,
                'requested_quantity' => $requestedQuantity,
                'message' => $sufficient ? '' : "Không đủ tồn kho cho {$itemName}. Tồn kho hiện tại: {$currentStock}, yêu cầu: {$requestedQuantity}"
            ];
        } catch (\Exception $e) {
            Log::error("Error checking stock:", [
                'error' => $e->getMessage(),
                'params' => compact('itemType', 'itemId', 'warehouseId', 'requestedQuantity')
            ]);

            // Trả về kết quả an toàn để không block việc tạo phiếu
            return [
                'sufficient' => true, // Cho phép tạo phiếu nếu không check được stock
                'current_stock' => 0,
                'requested_quantity' => $requestedQuantity,
                'message' => ''
            ];
        }
    }

    /**
     * Reduce item stock in warehouse.
     */
    private function reduceItemStock($itemType, $itemId, $warehouseId, $quantity)
    {
        $warehouseMaterial = \App\Models\WarehouseMaterial::where('item_type', $itemType)
            ->where('material_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($warehouseMaterial) {
            $newQuantity = $warehouseMaterial->quantity - $quantity;
            $warehouseMaterial->update(['quantity' => max(0, $newQuantity)]);
        }
    }

    /**
     * Restore item stock in warehouse (for cancelled dispatches).
     */
    private function restoreItemStock($itemType, $itemId, $warehouseId, $quantity)
    {
        $warehouseMaterial = \App\Models\WarehouseMaterial::where('item_type', $itemType)
            ->where('material_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($warehouseMaterial) {
            $newQuantity = $warehouseMaterial->quantity + $quantity;
            $warehouseMaterial->update(['quantity' => $newQuantity]);
        } else {
            // Create new warehouse material record if it doesn't exist
            \App\Models\WarehouseMaterial::create([
                'item_type' => $itemType,
                'material_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity
            ]);
        }
    }

    /**
     * Get all rentals for dispatch form
     */
    public function getRentals()
    {
        // Lọc hợp đồng cho thuê theo quyền của nhân viên đang đăng nhập
        $rentals = $this->getFilteredRentals();

        return response()->json([
            'success' => true,
            'rentals' => $rentals->map(function ($rental) {
                return [
                    'id' => $rental->id,
                    'rental_code' => $rental->rental_code,
                    'rental_name' => $rental->rental_name,
                    'customer_name' => $rental->customer->company_name ?? '',
                    'customer_representative' => $rental->customer->name ?? '',
                    'rental_date' => $rental->rental_date,
                    'due_date' => $rental->due_date,
                    'display_name' => $rental->rental_code . ' - ' . $rental->rental_name . ' (' . ($rental->customer->company_name ?? 'N/A') . ')'
                ];
            })
        ]);
    }

    /**
     * Validate items based on dispatch_detail
     */
    private function validateItemsByDispatchDetail(Request $request)
    {
        $dispatchDetail = $request->dispatch_detail;
        $items = $request->items ?? [];

        if (empty($items)) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                ['items' => ['Phiếu xuất phải có ít nhất một sản phẩm!']]
            );
        }

        // Group items by category
        $contractItems = [];
        $backupItems = [];
        $generalItems = [];

        foreach ($items as $item) {
            $category = $item['category'] ?? 'general';
            switch ($category) {
                case 'contract':
                    $contractItems[] = $item;
                    break;
                case 'backup':
                    $backupItems[] = $item;
                    break;
                default:
                    $generalItems[] = $item;
                    break;
            }
        }

        // Validate based on dispatch_detail
        switch ($dispatchDetail) {
            case 'contract':
                if (empty($contractItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Phiếu xuất theo hợp đồng phải có ít nhất một thành phẩm theo hợp đồng!']]
                    );
                }

                break;

            case 'backup':
                if (empty($backupItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Phiếu xuất thiết bị dự phòng phải có ít nhất một thiết bị dự phòng!']]
                    );
                }

                break;

            case 'all':
                if (empty($contractItems) && empty($backupItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Vui lòng chọn ít nhất một sản phẩm hợp đồng và một thiết bị dự phòng để xuất kho!']]
                    );
                }
                if (empty($contractItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Phiếu xuất "Tất cả" phải có ít nhất một sản phẩm hợp đồng!']]
                    );
                }
                if (empty($backupItems)) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Phiếu xuất "Tất cả" phải có ít nhất một thiết bị dự phòng!']]
                    );
                }
                break;
        }

        Log::info('Dispatch detail validation passed', [
            'dispatch_detail' => $dispatchDetail,
            'contract_items' => count($contractItems),
            'backup_items' => count($backupItems),
            'general_items' => count($generalItems)
        ]);
    }

    /**
     * Validate items for update based on dispatch_detail
     */
    private function validateUpdateItemsByDispatchDetail(Request $request, Dispatch $dispatch)
    {
        $dispatchDetail = $request->dispatch_detail;

        // Count existing items + new items
        $contractItemsCount = 0;
        $backupItemsCount = 0;
        $generalItemsCount = 0;

        // Count existing items that are not disabled/removed
        if ($request->has('contract_items')) {
            foreach ($request->contract_items as $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $contractItemsCount++;
                }
            }
        }

        if ($request->has('backup_items')) {
            foreach ($request->backup_items as $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $backupItemsCount++;
                }
            }
        }

        if ($request->has('general_items')) {
            foreach ($request->general_items as $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $generalItemsCount++;
                }
            }
        }

        // Count newly added items
        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                if (isset($itemData['item_type']) && isset($itemData['item_id'])) {
                    $category = $itemData['category'] ?? 'general';
                    switch ($category) {
                        case 'contract':
                            $contractItemsCount++;
                            break;
                        case 'backup':
                            $backupItemsCount++;
                            break;
                        default:
                            $generalItemsCount++;
                            break;
                    }
                }
            }
        }

        // Validate based on dispatch_detail
        switch ($dispatchDetail) {
            case 'contract':
                if ($contractItemsCount === 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['contract_items' => ['Phiếu xuất theo hợp đồng phải có ít nhất một thành phẩm theo hợp đồng!']]
                    );
                }
                
                break;

            case 'backup':
                if ($backupItemsCount === 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['backup_items' => ['Phiếu xuất thiết bị dự phòng phải có ít nhất một thiết bị dự phòng!']]
                    );
                }

                break;

            case 'all':
                if ($contractItemsCount === 0 && $backupItemsCount === 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['items' => ['Vui lòng chọn ít nhất một sản phẩm hợp đồng và một thiết bị dự phòng để xuất kho!']]
                    );
                }
                if ($contractItemsCount === 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['contract_items' => ['Phiếu xuất "Tất cả" phải có ít nhất một sản phẩm hợp đồng!']]
                    );
                }
                if ($backupItemsCount === 0) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['backup_items' => ['Phiếu xuất "Tất cả" phải có ít nhất một thiết bị dự phòng!']]
                    );
                }
                break;
        }

        Log::info('Update dispatch detail validation passed', [
            'dispatch_detail' => $dispatchDetail,
            'contract_items' => $contractItemsCount,
            'backup_items' => $backupItemsCount,
            'general_items' => $generalItemsCount
        ]);
    }

    /**
     * Search dispatches via AJAX
     */
    public function search(Request $request)
    {
        $query = Dispatch::with(['project', 'creator', 'companyRepresentative', 'items']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('dispatch_code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('project_receiver', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('dispatch_note', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply dispatch type filter
        if ($request->filled('dispatch_type')) {
            $query->where('dispatch_type', $request->dispatch_type);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $dateFrom = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_from)->format('Y-m-d');
            $query->whereDate('dispatch_date', '>=', $dateFrom);
        }
        if ($request->filled('date_to')) {
            $dateTo = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_to)->format('Y-m-d');
            $query->whereDate('dispatch_date', '<=', $dateTo);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'dispatch_date');
        $sortDirection = $request->get('sort_direction', 'desc');

        if (in_array($sortBy, ['dispatch_code', 'dispatch_date', 'status', 'dispatch_type'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('dispatch_date', 'desc');
        }

        $query->orderBy('created_at', 'desc');

        $dispatches = $query->get();

        // Transform dispatches for JSON response
        $transformedDispatches = $dispatches->map(function ($dispatch) {
            return [
                'id' => $dispatch->id,
                'dispatch_code' => $dispatch->dispatch_code,
                'dispatch_date' => $dispatch->dispatch_date->format('d/m/Y'),
                'project_receiver' => $dispatch->project_receiver,
                'total_items' => $dispatch->items->count(),
                'dispatch_type' => $dispatch->dispatch_type,
                'company_representative' => $dispatch->companyRepresentative->name ?? '-',
                'creator' => $dispatch->creator->name ?? '-',
                'status' => $dispatch->status,
                'status_label' => $this->getStatusLabel($dispatch->status),
                'status_color' => $this->getStatusColor($dispatch->status),
                'can_edit' => !in_array($dispatch->status, ['completed', 'cancelled']),
                'can_approve' => $dispatch->status === 'pending',
                'can_cancel' => $dispatch->status === 'pending',
                'can_delete' => $dispatch->status === 'cancelled',
            ];
        });

        return response()->json([
            'success' => true,
            'dispatches' => $transformedDispatches,
            'total' => $dispatches->count(),
        ]);
    }

    /**
     * Get status label for display
     */
    private function getStatusLabel($status)
    {
        switch ($status) {
            case 'pending':
                return 'Chờ xử lý';
            case 'approved':
                return 'Đã duyệt';
            case 'completed':
                return 'Đã hoàn thành';
            case 'cancelled':
                return 'Đã hủy';
            default:
                return 'Không xác định';
        }
    }

    /**
     * Get status color for styling
     */
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'pending':
                return 'yellow';
            case 'approved':
                return 'blue';
            case 'completed':
                return 'green';
            case 'cancelled':
                return 'red';
            default:
                return 'gray';
        }
    }

    /**
     * Export dispatch to Excel
     */
    public function exportExcel(Dispatch $dispatch)
    {
        $dispatch->load(['project', 'creator', 'companyRepresentative', 'items.material', 'items.product', 'items.good', 'items.warehouse']);
        
        return Excel::download(new DispatchExport($dispatch), 'phieu-xuat-' . $dispatch->dispatch_code . '.xlsx');
    }

    /**
     * Export dispatch to PDF
     */
    public function exportPdf(Dispatch $dispatch)
    {
        $dispatch->load(['project', 'creator', 'companyRepresentative', 'items.material', 'items.product', 'items.good', 'items.warehouse']);
        $pdf = PDF::loadView('exports.dispatch_pdf', ['dispatch' => $dispatch]);
        return $pdf->download('phieu-xuat-' . $dispatch->dispatch_code . '.pdf');
    }
}
