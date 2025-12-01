<?php

namespace App\Http\Controllers;

use App\Models\Testing;
use App\Models\TestingItem;
use App\Models\TestingDetail;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use App\Models\Assembly;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseMaterial;
use App\Models\Notification;
use App\Models\UserLog;
use App\Models\InventoryImport;
use App\Models\InventoryImportMaterial;
use App\Models\Dispatch;
use App\Models\DispatchItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Helpers\DateHelper;

class TestingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Testing::with(['tester', 'items', 'receiverEmployee']);

        // Apply filters
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('test_code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('receiverEmployee', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($q2) use ($search) {
                        $q2->where('serial_number', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by test type - chỉ áp dụng khi có giá trị cụ thể
        if ($request->has('test_type') && !empty($request->test_type)) {
            $query->where('test_type', $request->test_type);
        }

        // Filter by status - loại bỏ trạng thái 'cancelled'
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && !empty($request->date_from)) {
            $dateFrom = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_from)->format('Y-m-d');
            $query->where('test_date', '>=', $dateFrom);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $dateTo = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_to)->format('Y-m-d');
            $query->where('test_date', '<=', $dateTo);
        }

        $testings = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('testing.index', compact('testings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $employees = Employee::all();
        $materials = Material::where('is_hidden', false)
            ->select('id', 'code', 'name')
            ->orderBy('name', 'asc')
            ->get()
            ->unique('id')
            ->map(function($material) {
                return [
                    'id' => $material->id,
                    'code' => $material->code,
                    'name' => preg_replace('/[\x00-\x1F\x7F]/', '', $material->name) // Remove control characters
                ];
            });
        $products = Product::where('is_hidden', false)
            ->select('id', 'code', 'name')
            ->orderBy('name', 'asc')
            ->get()
            ->unique('id');
        $goods = Good::where('status', 'active')
            ->where('is_hidden', false)
            ->select('id', 'code', 'name')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function($good) {
                return [
                    'id' => $good->id,
                    'code' => $good->code,
                    'name' => preg_replace('/[\x00-\x1F\x7F]/', '', $good->name) // Remove control characters
                ];
            });
        $suppliers = Supplier::all();
        $warehouses = Warehouse::where('status', 'active')->get();

        // Get pending assemblies without testing records for selection
        $pendingAssemblies = Assembly::whereDoesntHave('testings')
            ->orWhereHas('testings', function ($query) {
                $query->where('status', 'cancelled');
            })
            ->where('status', '!=', 'cancelled')
            ->with('product')
            ->get();

        // Check if assembly_id is provided in the URL
        $selectedAssembly = null;
        if ($request->has('assembly_id')) {
            $selectedAssembly = Assembly::with('product')->find($request->assembly_id);
        }

        return view('testing.create', compact(
            'employees',
            'materials',
            'products',
            'goods',
            'suppliers',
            'warehouses',
            'pendingAssemblies',
            'selectedAssembly'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Convert date format before validation
        $request->merge([
            'test_date' => DateHelper::convertToDatabaseFormat($request->test_date)
        ]);

        $validator = Validator::make($request->all(), [
            'test_code' => 'required|string|unique:testings,test_code',
            'test_type' => 'required|in:material',
            'test_date' => 'required|date',
            'receiver_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:material,product',
            'items.*.id' => 'required',
            'items.*.warehouse_id' => 'required|exists:warehouses,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.serials' => 'nullable|array',
            'items.*.serials.*' => 'nullable|string',
            'test_items' => 'nullable|array',
            'test_items.*' => 'nullable|string',
        ]);

        // Custom validation: Kiểm tra số lượng serial không vượt quá số lượng kiểm thử
        // và không vượt quá số lượng "không có serial" thực tế trong kho
        $validator->after(function ($validator) use ($request) {
            if ($request->has('items')) {
                foreach ($request->items as $index => $item) {
                    $quantity = (int)($item['quantity'] ?? 0);
                    $serials = $item['serials'] ?? [];

                    // Tổng serial người dùng chọn (kể cả trống đại diện cho N/A)
                    $totalSelectedSerials = is_array($serials) ? count($serials) : 0;

                    // Số serial thực (không rỗng)
                    $validSerials = array_filter($serials, function($serial) {
                        return !empty(trim($serial));
                    });

                    // 1) Chặn tổng số serial chọn > số lượng kiểm thử
                    if ($totalSelectedSerials > $quantity) {
                        $validator->errors()->add(
                            "items.{$index}.serials",
                            "Số Serial chọn không được lớn hơn số lượng kiểm thử (đang chọn {$totalSelectedSerials}/{$quantity})"
                        );
                    }

                    // 2) Chặn số serial thực > số lượng kiểm thử (bảo toàn thông báo cũ)
                    if (count($validSerials) > $quantity) {
                        $validator->errors()->add(
                            "items.{$index}.serials",
                            "Số lượng serial (" . count($validSerials) . ") không được vượt quá số lượng kiểm thử ({$quantity})"
                        );
                    }

                    // 3) Kiểm tra số lượng chọn "Không có Serial" không vượt quá số N/A thực tế trong kho
                    try {
                        // Số lượng cần N/A = số lượng kiểm thử - số serial thực được chọn
                        $neededNoSerial = max(0, $quantity - count($validSerials));
                        if ($neededNoSerial > 0 && !empty($item['id']) && !empty($item['warehouse_id'])) {
                            $wmQuery = [
                                'warehouse_id' => $item['warehouse_id'],
                                // Map item_type: product -> good
                                'item_type' => ($item['item_type'] ?? 'material') === 'product' ? 'good' : ($item['item_type'] ?? 'material'),
                                'material_id' => (int)$item['id'],
                            ];
                            $wm = \App\Models\WarehouseMaterial::where($wmQuery)->first();
                            $availableQty = (int)($wm->quantity ?? 0);
                            // Lấy danh sách serial hiện có trong kho
                            $currentSerials = [];
                            if (!empty($wm) && !empty($wm->serial_number)) {
                                $decoded = json_decode($wm->serial_number, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $currentSerials = array_values(array_filter(array_map('trim', $decoded)));
                                } else {
                                    $currentSerials = array_values(array_filter(array_map('trim', explode(',', (string)$wm->serial_number))));
                                }
                            }
                            $availableNoSerial = max(0, $availableQty - count($currentSerials));
                            if ($neededNoSerial > $availableNoSerial) {
                                $validator->errors()->add(
                                    "items.{$index}.serials",
                                    "Số lượng thiết bị không có Serial cần kiểm thử (" . $neededNoSerial . ") vượt quá số lượng không Serial thực tế trong kho (" . $availableNoSerial . ")"
                                );
                            }
                        }
                    } catch (\Throwable $e) {
                        // An toàn: không làm hỏng flow nếu lỗi đọc dữ liệu kho
                    }
                }
            }
        });

        // Kiểm tra không cho phép tạo phiếu kiểm thử Thiết bị thành phẩm trực tiếp
        if ($request->test_type === 'finished_product') {
            return redirect()->back()
                ->with('error', 'Không thể tạo phiếu kiểm thử Thiết bị thành phẩm trực tiếp. Phiếu này chỉ được tạo thông qua lắp ráp.')
                ->withInput();
        }

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Create testing record
            $testing = Testing::create([
                'test_code' => $request->test_code,
                'test_type' => $request->test_type,
                'tester_id' => $request->receiver_id, // Sử dụng receiver_id làm tester_id
                'receiver_id' => $request->receiver_id,
                'test_date' => $request->test_date,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Add testing items
            foreach ($request->items as $item) {
                // Check inventory trước khi tạo
                $inventory = WarehouseMaterial::where([
                    'material_id' => $item['id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'item_type' => $item['item_type'] === 'product' ? 'good' : $item['item_type']
                ])->first();

                if (!$inventory || $inventory->quantity < $item['quantity']) {
                    throw new \Exception('Số lượng vượt quá tồn kho');
                }

                $itemData = [
                    'testing_id' => $testing->id,
                    'item_type' => $item['item_type'],
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity' => $item['quantity'],
                    'result' => 'pending',
                ];

                // Set the appropriate ID based on item type
                if ($item['item_type'] === 'material') {
                    $itemData['material_id'] = $item['id'];
                } else {
                    $itemData['good_id'] = $item['id']; // Thay đổi từ product_id thành good_id
                }

                // Xử lý serial numbers nếu có
                if (isset($item['serials']) && is_array($item['serials']) && !empty($item['serials'])) {
                    // Lưu toàn bộ serial thực (loại bỏ rỗng) vào cột serial_number để theo dõi
                    $selectedSerials = array_values(array_filter(array_map('trim', $item['serials'])));
                    if (!empty($selectedSerials)) { $itemData['serial_number'] = implode(', ', $selectedSerials); }
                }

                TestingItem::create($itemData);
            }

            // Add testing details if provided (only for non-finished_product)
            if ($request->has('test_items') && $testing->test_type !== 'finished_product') {
                foreach ($request->test_items as $testItem) {
                    if (!empty($testItem)) {
                        TestingDetail::create([
                            'testing_id' => $testing->id,
                            'test_item_name' => $testItem,
                            'result' => 'pending',
                        ]);
                    }
                }
            }

            // Create notification
            Notification::createNotification(
                'Phiếu kiểm thử mới',
                "Phiếu kiểm thử #{$testing->test_code} đã được tạo và chờ duyệt.",
                'info',
                $testing->receiver_id,
                'testing',
                $testing->id,
                route('testing.show', $testing->id)
            );

            DB::commit();

            // Log activity
            UserLog::logActivity(
                Auth::id(),
                'create',
                'testings',
                'Tạo mới phiếu kiểm thử: ' . $testing->test_code,
                null,
                $testing->toArray()
            );

            // Sau khi tạo phiếu kiểm thử thành công
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tạo phiếu kiểm thử thành công!',
                    'redirect' => route('testing.index')
                ]);
            }
            return redirect()->route('testing.index')->with('success', 'Tạo phiếu kiểm thử thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo phiếu kiểm thử: ' . $e->getMessage(), [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage())->withInput();
        }
    }

    /**
 * Display the specified resource.
 */
public function show(Testing $testing)
{
    // ✨ TỐI ƯU: Chỉ load relationships thực sự cần thiết
    // Với 50 thành phẩm + 1150 vật tư, việc eager load đúng cách rất quan trọng
    
    $startTime = microtime(true);
    
    // Load basic relationships (luôn cần)
    $testing->load([
        'tester:id,name',
        'assignedEmployee:id,name',
        'receiverEmployee:id,name',
        'approver:id,name',
        'successWarehouse:id,name',
        'failWarehouse:id,name'
    ]);
    
    // Load items với chỉ những fields cần thiết
    $testing->load(['items' => function($query) {
        $query->select([
            'id',
            'testing_id',
            'item_type',
            'material_id',
            'product_id',
            'good_id',
            'warehouse_id',
            'quantity',
            'serial_number',
            'serial_results',
            'result',
            'pass_quantity',
            'fail_quantity',
            'notes'
        ]);
    }]);
    
    // Load related models cho items (chỉ fields cần thiết)
    $testing->load([
        'items.material:id,code,name,unit',
        'items.good:id,code,name',
        'items.warehouse:id,name'
    ]);
    
    // Load details (nếu có)
    $testing->load(['details:id,testing_id,item_id,test_item_name,result,notes']);
    
    // Chỉ load assembly nếu là finished_product
    if ($testing->test_type === 'finished_product') {
        $testing->load([
            'assembly' => function($query) {
                $query->select([
                    'id',
                    'code',
                    'project_id'
                ]);
            },
            'assembly.products' => function($query) {
                $query->select([
                    'id',
                    'assembly_id',
                    'product_id',
                    'quantity',
                    'serials',
                    'product_unit'
                ]);
            },
            'assembly.products.product:id,code,name',
            'assembly.materials' => function($query) {
                $query->select([
                    'id',
                    'assembly_id',
                    'material_id',
                    'warehouse_id',
                    'quantity',
                    'serial',
                    'target_product_id',
                    'product_unit'
                ]);
            },
            'assembly.materials.material:id,code,name,unit',
            'assembly.materials.warehouse:id,name',
            'assembly.project:id,project_code,project_name'
        ]);
    }
    
    $loadTime = round((microtime(true) - $startTime) * 1000, 2);
    
    Log::info('🚀 Tối ưu show testing', [
        'testing_id' => $testing->id,
        'test_code' => $testing->test_code,
        'items_count' => $testing->items->count(),
        'load_time_ms' => $loadTime,
        'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
    ]);

    // Ghi nhật ký xem chi tiết phiếu kiểm thử (thu gọn dữ liệu log)
    if (Auth::check()) {
        $lightData = [
            'id' => $testing->id,
            'test_code' => $testing->test_code,
            'status' => $testing->status,
            'test_type' => $testing->test_type,
            'created_at' => $testing->created_at,
        ];
        UserLog::logActivity(
            Auth::id(),
            'view',
            'testings',
            'Xem chi tiết phiếu kiểm thử: ' . $testing->test_code,
            null,
            $lightData
        );
    }

    return view('testing.show', compact('testing'));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Testing $testing)
    {
        $testing->load(['tester', 'items.material', 'items.product', 'items.good', 'items.warehouse', 'items.supplier', 'details', 'assembly.materials.material', 'assembly.materials.warehouse', 'assembly.products.product', 'assembly.project']);

        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $materials = Material::where('is_hidden', false)->get();
        $products = Product::where('is_hidden', false)->get();
        $goods = Good::where('status', 'active')->get();
        $suppliers = Supplier::all();

        return view('testing.edit', compact('testing', 'employees', 'materials', 'products', 'goods', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Testing $testing)
    {
        // Convert date format before validation
        if ($request->has('test_date')) {
            $request->merge([
                'test_date' => DateHelper::convertToDatabaseFormat($request->test_date)
            ]);
        }

        // LOCK FIELD: Không cho phép chỉnh sửa Người tiếp nhận kiểm thử (receiver_id)
        // khi phiếu ở trạng thái Chờ xử lý hoặc Đang thực hiện
        if (in_array($testing->status, ['pending', 'in_progress'], true)
            && $request->has('receiver_id')
            && (string)$request->get('receiver_id') !== (string)$testing->receiver_id) {
            return response()->json([
                'success' => false,
                'message' => 'Không được phép thay đổi Người tiếp nhận kiểm thử khi phiếu ở trạng thái Chờ xử lý/Đang thực hiện.',
                'errors' => ['receiver_id' => ['Trường Người tiếp nhận kiểm thử đang bị khóa.']]
            ], 422);
        }
        
        // Kiểm tra xem có phải là auto-save request không
        // Auto-save chỉ có item_results, test_results, test_notes mà không có thông tin cơ bản
        $hasBasicInfo = $request->has('tester_id') && $request->has('assigned_to') && $request->has('receiver_id') && $request->has('test_date');
        $hasAutoSaveData = $request->has('item_results') || $request->has('test_results') || $request->has('test_notes');
        
        $isAutoSave = $hasAutoSaveData && !$hasBasicInfo;
        
        // Kiểm tra xem có phải là request thêm/xóa hạng mục kiểm thử không
        $isAddTestDetail = $request->has('action') && $request->action === 'add_test_detail';
        $isDeleteTestDetail = $request->has('action') && $request->action === 'delete_test_detail';
        
        Log::info('Testing update logic', [
            'hasBasicInfo' => $hasBasicInfo,
            'hasAutoSaveData' => $hasAutoSaveData,
            'isAutoSave' => $isAutoSave,
            'isAddTestDetail' => $isAddTestDetail,
            'isDeleteTestDetail' => $isDeleteTestDetail,
        ]);
        
        // EARLY HANDLERS: Bỏ qua validator tổng khi chỉ thêm/xóa hạng mục kiểm thử
        if ($isAddTestDetail) {
            try {
                $newTestDetail = TestingDetail::create([
                    'testing_id' => $testing->id,
                    'item_id' => $request->item_id ?? null, // item_id có thể null
                    'test_item_name' => $request->test_item_name,
                    'result' => 'pending',
                    'test_pass_quantity' => 0,
                    'test_fail_quantity' => 0,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Đã thêm hạng mục kiểm thử mới thành công.',
                    'test_detail_id' => $newTestDetail->id
                ]);
            } catch (\Exception $e) {
                Log::error('Lỗi khi tạo hạng mục kiểm thử mới: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi khi tạo hạng mục kiểm thử mới: ' . $e->getMessage()
                ], 500);
            }
        }

        if ($isDeleteTestDetail) {
            try {
                $detailId = $request->detail_id;
                $testDetail = TestingDetail::where('id', $detailId)
                    ->where('testing_id', $testing->id)
                    ->first();

                if (!$testDetail) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không tìm thấy hạng mục kiểm thử để xóa.'
                    ], 404);
                }

                $testDetail->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa hạng mục kiểm thử thành công.'
                ]);
            } catch (\Exception $e) {
                Log::error('Lỗi khi xóa hạng mục kiểm thử: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi khi xóa hạng mục kiểm thử: ' . $e->getMessage()
                ], 500);
            }
        }
        
        $validator = Validator::make($request->all(), [
            'tester_id' => ($isAutoSave || $isAddTestDetail || $isDeleteTestDetail) ? 'nullable|exists:employees,id' : 'required|exists:employees,id',
            'assigned_to' => ($isAutoSave || $isAddTestDetail || $isDeleteTestDetail) ? 'nullable|exists:employees,id' : 'required|exists:employees,id',
            'receiver_id' => ($isAutoSave || $isAddTestDetail || $isDeleteTestDetail) ? 'nullable|exists:employees,id' : 'required|exists:employees,id',
            'test_date' => ($isAutoSave || $isAddTestDetail || $isDeleteTestDetail) ? 'nullable|date' : 'required|date',
            'notes' => 'nullable|string',
            'pass_quantity' => 'nullable|integer|min:0',
            'fail_quantity' => 'nullable|integer|min:0',
            'fail_reasons' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'item_results' => 'nullable|array',
            'item_results.*' => 'nullable|in:pass,fail,pending',
            'item_notes' => 'nullable|array',
            'item_notes.*' => 'nullable|string',
            'item_pass_quantity' => 'nullable|array',
            'item_pass_quantity.*' => 'nullable|integer|min:0',
            'item_fail_quantity' => 'nullable|array',
            'item_fail_quantity.*' => 'nullable|integer|min:0',
            'serial_results' => 'nullable|array',
            'serial_results.*' => 'nullable|array',
            'serial_results.*.*' => 'nullable|in:pass,fail,pending',
            'test_results' => 'nullable|array',
            'test_results.*' => 'nullable|in:pass,fail,pending',
            'test_notes' => 'nullable|array',
            'test_notes.*' => 'nullable|string',

        ]);

        // Custom validation: Kiểm tra số lượng serial không vượt quá số lượng kiểm thử
        $validator->after(function ($validator) use ($request, $testing) {
            if ($request->has('serial_results')) {
                foreach ($request->serial_results as $itemId => $serialResults) {
                    // Bảo vệ: chỉ xử lý khi là mảng hợp lệ
                    if (!is_array($serialResults)) {
                        Log::warning('DEBUG: Bỏ qua serial_results không hợp lệ (không phải mảng)', [
                            'item_id' => $itemId,
                            'raw_value' => $serialResults
                        ]);
                        continue;
                    }
                    // Bỏ qua các key không hợp lệ (bắt đầu bằng 'unknown_')
                    if (strpos($itemId, 'unknown_') === 0) {
                        continue;
                    }
                    
                    // Tìm testing item để lấy quantity
                    $testingItem = TestingItem::where('testing_id', $testing->id)
                        ->where(function($query) use ($itemId) {
                            $query->where('id', $itemId)
                                ->orWhere('material_id', $itemId)
                                ->orWhere('good_id', $itemId)
                                ->orWhere('product_id', $itemId);
                        })
                        ->first();
                    
                    if ($testingItem) {
                        $quantity = (int)($testingItem->quantity ?? 0);
                        // Nếu có consolidated_unit_, đếm tối đa 1 mục cho consolidated và bỏ qua key consolidated khi đếm thường
                        $hasConsolidated = false;
                        foreach ($serialResults as $k => $v) {
                            if (strpos($k, 'consolidated_unit_') === 0) { $hasConsolidated = true; break; }
                        }

                        $count = 0;
                        if ($hasConsolidated) {
                            foreach ($serialResults as $k => $v) {
                                if (strpos($k, 'consolidated_unit_') === 0 && !empty($v) && $v !== 'pending') { $count = 1; break; }
                            }
                        } else {
                            foreach ($serialResults as $k => $v) {
                                if (!empty($v) && $v !== 'pending') { $count++; }
                            }
                        }

                        if ($count > $quantity) {
                            $validator->errors()->add(
                                "serial_results.{$itemId}", 
                                "Số lượng serial có kết quả (" . $count . ") không được vượt quá số lượng kiểm thử ({$quantity})"
                            );
                        }
                    }
                }
            }
        });

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $testing->toArray();

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Chỉ update testing record nếu không phải auto-save hoặc có đủ dữ liệu
            if (!$isAutoSave || ($request->has('tester_id') && $request->has('assigned_to') && $request->has('receiver_id') && $request->has('test_date'))) {
                // Xử lý ghi chú: chỉ lưu notes đơn giản, không lưu JSON phức tạp
                $notesToSave = null;
                if ($request->has('notes')) {
                    $notesToSave = $request->notes;
                } else {
                    $notesToSave = $testing->notes;
                }
                
                $testing->update([
                    'tester_id' => $request->tester_id ?? $testing->tester_id,
                    'assigned_to' => $request->assigned_to ?? $testing->assigned_to ?? $testing->tester_id,
                    'receiver_id' => $request->receiver_id ?? $testing->receiver_id,
                    'test_date' => $request->test_date ? $request->test_date : $testing->test_date,
                    'notes' => $notesToSave,
                    'pass_quantity' => $request->pass_quantity ?? $testing->pass_quantity ?? 0,
                    'fail_quantity' => $request->fail_quantity ?? $testing->fail_quantity ?? 0,
                    'fail_reasons' => $request->fail_reasons ?? $testing->fail_reasons,
                    'conclusion' => $request->conclusion ?? $testing->conclusion,
                ]);
            }

            // Add detailed logging for debugging
            Log::info('Cập nhật kiểm thử - Thông tin request', [
                'testing_id' => $testing->id,
                'item_results' => $request->item_results,
                'has_item_results' => $request->has('item_results'),
                'request_keys' => array_keys($request->all())
            ]);

            // Update items results if we have item_results in the request
            if ($request->has('item_results')) {
                Log::info('Bắt đầu xử lý kết quả kiểm thử cho các vật tư', [
                    'item_results_count' => count($request->item_results),
                    'item_results_keys' => array_keys($request->item_results)
                ]);

                foreach ($request->item_results as $itemKey => $result) {
                    Log::info('Xử lý kết quả kiểm thử cho item', [
                        'item_key' => $itemKey,
                        'result' => $result
                    ]);

                    // Parse item_id từ format "item_id_index" hoặc chỉ "item_id"
                    if (strpos($itemKey, '_') !== false) {
                        list($itemId, $index) = explode('_', $itemKey);
                    } else {
                        $itemId = $itemKey;
                    }
                    
                    // Tìm testing item theo item_id, material_id, product_id, good_id
                    $item = TestingItem::where('testing_id', $testing->id)
                        ->where(function($query) use ($itemId) {
                            $query->where('id', $itemId)
                                  ->orWhere('material_id', $itemId)
                                  ->orWhere('product_id', $itemId)
                                  ->orWhere('good_id', $itemId);
                        })
                        ->first();

                    if ($item) {
                        // Cập nhật result cho item này
                        $item->update([
                            'result' => $result,
                            'updated_at' => now()
                        ]);

                        Log::info('Đã cập nhật kết quả kiểm thử', [
                            'testing_id' => $testing->id,
                            'item_id' => $item->id,
                            'material_id' => $item->material_id,
                            'product_id' => $item->product_id,
                            'good_id' => $item->good_id,
                            'item_key' => $itemKey,
                            'old_result' => $item->getOriginal('result'),
                            'new_result' => $result
                        ]);
                    } else {
                        Log::warning('Không tìm thấy testing item', [
                            'testing_id' => $testing->id,
                            'item_id' => $itemId,
                            'item_key' => $itemKey
                        ]);
                    }
                }
            }

            // Update item notes if we have item_notes in the request
            if ($request->has('item_notes')) {
                Log::info('DEBUG: Xử lý item_notes', [
                    'testing_id' => $testing->id,
                    'item_notes_data' => $request->item_notes,
                    'item_notes_count' => count($request->item_notes)
                ]);

                foreach ($request->item_notes as $itemId => $note) {
                    Log::info('DEBUG: Xử lý item note', [
                        'item_id' => $itemId,
                        'note' => $note,
                        'note_length' => strlen($note)
                    ]);

                    $item = TestingItem::where(function ($query) use ($itemId, $testing) {
                        $query->where('testing_id', $testing->id)
                            ->where(function ($q) use ($itemId) {
                                $q->where('id', $itemId)
                                    ->orWhere('material_id', $itemId)
                                    ->orWhere('good_id', $itemId)
                                    ->orWhere('product_id', $itemId);
                            });
                    })->first();

                    if ($item) {
                        $oldNote = $item->notes;
                        $item->update(['notes' => $note]);
                        Log::info('DEBUG: Đã cập nhật item note', [
                            'testing_id' => $testing->id, 
                            'testing_item_id' => $item->id,
                            'item_type' => $item->item_type,
                            'material_id' => $item->material_id,
                            'product_id' => $item->product_id,
                            'good_id' => $item->good_id,
                            'old_note' => $oldNote,
                            'new_note' => $note
                        ]);
                    } else {
                        Log::warning('DEBUG: Không tìm thấy testing item cho item_notes', [
                            'testing_id' => $testing->id,
                            'item_id' => $itemId,
                            'note' => $note
                        ]);
                    }
                }
            }

            // Update item pass/fail quantities if we have item_pass_quantity and item_fail_quantity in the request
            if ($request->has('item_pass_quantity') || $request->has('item_fail_quantity')) {
                Log::info('Bắt đầu xử lý pass/fail quantities cho các vật tư', [
                    'item_pass_quantity' => $request->item_pass_quantity,
                    'item_fail_quantity' => $request->item_fail_quantity
                ]);

                // Xử lý pass quantities
                if ($request->has('item_pass_quantity')) {
                    $providedFailForItemIds = array_keys($request->get('item_fail_quantity', []));
                    foreach ($request->item_pass_quantity as $itemId => $passQuantity) {
                        // Tìm TestingItem theo cả material_id, product_id, good_id và id
                        $item = TestingItem::where('testing_id', $testing->id)
                            ->where(function($query) use ($itemId) {
                                $query->where('material_id', $itemId)
                                      ->orWhere('product_id', $itemId)
                                      ->orWhere('good_id', $itemId)
                                      ->orWhere('id', $itemId);
                            })
                            ->first();

                        if ($item) {
                            // Kiểm tra ràng buộc: không cho phép cập nhật pass/fail của vật tư lắp ráp trong phiếu thành phẩm
                            if ($testing->test_type == 'finished_product' && $item->item_type == 'material') {
                                Log::warning('Không cho phép cập nhật pass/fail của vật tư lắp ráp trong phiếu thành phẩm', [
                                    'testing_id' => $testing->id,
                                    'item_id' => $item->id,
                                    'item_type' => $item->item_type,
                                    'test_type' => $testing->test_type
                                ]);
                                continue;
                            }
                            
                            $passQuantity = (int) $passQuantity;
                            $maxPass = (int) ($item->quantity ?? $passQuantity);
                            if ($passQuantity > $maxPass) {
                                $passQuantity = $maxPass;
                            }
                            
                            // Kiểm tra ràng buộc: pass_quantity + fail_quantity ≤ quantity
                            $currentFailQuantity = (int)($item->fail_quantity ?? 0);
                            if ($passQuantity + $currentFailQuantity > $maxPass) {
                                Log::warning('Vi phạm ràng buộc: pass_quantity + fail_quantity > quantity', [
                                    'testing_id' => $testing->id,
                                    'item_id' => $item->id,
                                    'pass_quantity' => $passQuantity,
                                    'fail_quantity' => $currentFailQuantity,
                                    'max_quantity' => $maxPass
                                ]);
                                continue;
                            }
                            
                            $item->update(['pass_quantity' => $passQuantity]);
                            // Nếu không gửi fail_quantity cho item này, tự tính = quantity - pass
                            if (!in_array($itemId, $providedFailForItemIds, true)) {
                                $autoFail = max(0, (int)($item->quantity ?? 0) - $passQuantity);
                                $item->update(['fail_quantity' => $autoFail]);
                            }
                            Log::info('Đã cập nhật pass/fail (auto) cho item', [
                                'testing_id' => $testing->id,
                                'item_id' => $item->id,
                                'material_id' => $item->material_id,
                                'product_id' => $item->product_id,
                                'good_id' => $item->good_id,
                                'pass_quantity' => $passQuantity,
                                'fail_quantity' => $item->fail_quantity
                            ]);
                        } else {
                            Log::warning('Không tìm thấy testing item cho item_id', [
                                'testing_id' => $testing->id,
                                'item_id' => $itemId
                            ]);
                        }
                    }
                }

                // Xử lý fail quantities
                if ($request->has('item_fail_quantity')) {
                    foreach ($request->item_fail_quantity as $itemId => $failQuantity) {
                        // Tìm TestingItem theo cả material_id, product_id, good_id và id
                        $item = TestingItem::where('testing_id', $testing->id)
                            ->where(function($query) use ($itemId) {
                                $query->where('material_id', $itemId)
                                      ->orWhere('product_id', $itemId)
                                      ->orWhere('good_id', $itemId)
                                      ->orWhere('id', $itemId);
                            })
                            ->first();

                        if ($item) {
                            // Kiểm tra ràng buộc: không cho phép cập nhật pass/fail của vật tư lắp ráp trong phiếu thành phẩm
                            if ($testing->test_type == 'finished_product' && $item->item_type == 'material') {
                                Log::warning('Không cho phép cập nhật pass/fail của vật tư lắp ráp trong phiếu thành phẩm', [
                                    'testing_id' => $testing->id,
                                    'item_id' => $item->id,
                                    'item_type' => $item->item_type,
                                    'test_type' => $testing->test_type
                                ]);
                                continue;
                            }
                            
                            // Kiểm tra ràng buộc: pass_quantity + fail_quantity ≤ quantity
                            $currentPassQuantity = (int)($item->pass_quantity ?? 0);
                            $maxQuantity = (int)($item->quantity ?? 0);
                            if ($currentPassQuantity + $failQuantity > $maxQuantity) {
                                Log::warning('Vi phạm ràng buộc: pass_quantity + fail_quantity > quantity', [
                                    'testing_id' => $testing->id,
                                    'item_id' => $item->id,
                                    'pass_quantity' => $currentPassQuantity,
                                    'fail_quantity' => $failQuantity,
                                    'max_quantity' => $maxQuantity
                                ]);
                                continue;
                            }
                            
                            $item->update(['fail_quantity' => $failQuantity]);
                            Log::info('Đã cập nhật fail_quantity cho item', [
                                'testing_id' => $testing->id,
                                'item_id' => $item->id,
                                'material_id' => $item->material_id,
                                'product_id' => $item->product_id,
                                'good_id' => $item->good_id,
                                'fail_quantity' => $failQuantity
                            ]);
                        } else {
                            Log::warning('Không tìm thấy testing item cho item_id', [
                                'testing_id' => $testing->id,
                                'item_id' => $itemId
                            ]);
                        }
                    }
                }
            }

            // Update item pass/fail quantities
            // Xóa phần duplicate này vì đã xử lý ở trên với validation đầy đủ
            // Logic mới: Chỉ cho phép cập nhật pass/fail của thành phẩm, không cho phép cập nhật vật tư lắp ráp

            // Không cần xử lý item_pass_quantity_no_serial nữa vì đã có dropdown cho từng vật tư N/A

            // Update serial results
            if ($request->has('serial_results')) {
                // Sanitize: only keep entries that are arrays; drop scalars to avoid writing 0 into JSON column
                $rawSerialResults = $request->input('serial_results', []);
                $serialResultsInput = [];
                foreach ($rawSerialResults as $k => $v) {
                    if (is_array($v)) { $serialResultsInput[$k] = $v; }
                }

                Log::debug('DEBUG: Xử lý serial_results', [
                    'testing_id' => $testing->id,
                    'serial_results_keys' => array_keys($serialResultsInput)
                ]);
                
                foreach ($serialResultsInput as $itemId => $serialResults) {
                    Log::debug('DEBUG: Xử lý serial_results cho item');
                    Log::debug('item_id: ' . $itemId);
                    Log::debug('serial_results: ' . json_encode($serialResults));
                    
                    // PHÂN BIỆT RÕ RÀNG giữa 2 loại:
                    // 1. Thành phẩm: serial_results[item_id][label] - tìm theo item->id
                    // 2. Vật tư lắp ráp: serial_results[item_id][label] - tìm theo item->id (đã sửa view)
                    
                    // Tìm theo item->id (cho cả thành phẩm và vật tư lắp ráp)
                    $item = TestingItem::where('testing_id', $testing->id)
                        ->where('id', $itemId)
                        ->first();
                    
                    // Fallback tương thích: nếu key là material_id (từ view cũ), tìm theo material_id nhưng CHỌN ĐÚNG item bằng so khớp serial
                    if (!$item && is_numeric($itemId)) {
                        $candidateItems = TestingItem::where('testing_id', $testing->id)
                            ->where('item_type', 'material')
                            ->where('material_id', (int)$itemId)
                            ->get();
                        if ($candidateItems->count() > 0) {
                            $item = $this->findMatchingTestingItemBySerial($candidateItems, $serialResults);
                        }
                    }
                    
                    // Nếu không tìm thấy theo item->id, thử tìm theo product_id hoặc good_id (chỉ cho thành phẩm)
                    if (!$item) {
                        $item = TestingItem::where('testing_id', $testing->id)
                            ->where(function($query) use ($itemId) {
                                $query->where('product_id', $itemId)
                                      ->orWhere('good_id', $itemId);
                            })
                            ->first();
                    }
                    
                    if ($item) {
                        Log::info('DEBUG: Tìm thấy testing item', [
                            'item_id' => $item->id,
                            'material_id' => $item->material_id,
                            'product_id' => $item->product_id,
                            'good_id' => $item->good_id,
                            'item_type' => $item->item_type,
                            'search_item_id' => $itemId,
                            'old_serial_results' => $item->serial_results
                        ]);
                        
                        // Lưu ý: chỉ tự động chuyển 'pending' => 'pass' cho Vật tư/Hàng hóa (phiếu loại material)
                        // Thành phẩm (phiếu finished_product) giữ nguyên 'pending'
                        $normalizedSerialResults = [];
                        $shouldAutoPassPending = ($item->item_type === 'material') || ($item->item_type === 'product' && $testing->test_type === 'material');
                        
                        // Debug: Log request data for consolidated units
                        if (!empty($serialResults)) {
                            foreach ($serialResults as $key => $value) {
                                if (strpos($key, 'consolidated_unit_') === 0) {
                                    Log::info('DEBUG: Consolidated unit request', [
                                        'item_id' => $item->id,
                                        'material_id' => $item->material_id,
                                        'key' => $key,
                                        'value' => $value,
                                        'all_serial_results' => $serialResults
                                    ]);
                                }
                            }
                        }
                        
                        // Kiểm tra có consolidated_unit_ keys không
                        $hasConsolidated = false;
                        foreach ($serialResults as $label => $value) {
                            if (strpos($label, 'consolidated_unit_') === 0) {
                                $hasConsolidated = true;
                                break;
                            }
                        }
                        
                        if ($hasConsolidated && $item->item_type === 'material') {
                            // Xử lý serial gộp - tạo kết quả cho tất cả số lượng (không phụ thuộc quan hệ material)
                            $quantity = (int)($item->quantity ?? 0);
                                
                                // Lấy giá trị từ consolidated_unit_X (chỉ lấy giá trị đầu tiên tìm thấy)
                                $consolidatedValue = 'pending';
                                foreach ($serialResults as $label => $value) {
                                    if (strpos($label, 'consolidated_unit_') === 0) {
                                        $consolidatedValue = ($value === null || $value === '') ? 'pending' : $value;
                                        break; // Chỉ lấy giá trị đầu tiên
                                    }
                                }
                                // Áp dụng auto-pass khi được phép: pending -> pass đối với vật tư/hàng hóa
                                if ($shouldAutoPassPending && ($consolidatedValue === 'pending' || $consolidatedValue === null || $consolidatedValue === '')) {
                                    $consolidatedValue = 'pass';
                                }
                                
                                // Tạo kết quả cho tất cả số lượng với cùng một giá trị
                                for ($i = 0; $i < $quantity; $i++) {
                                    $key = $this->labelFromIndex($i);
                                    $normalizedSerialResults[$key] = $consolidatedValue;
                                }
                                
                                Log::debug('DEBUG: Xử lý consolidated_unit', [
                                    'item_id' => $item->id,
                                    'quantity' => $quantity,
                                    'consolidated_value' => $consolidatedValue,
                                    'normalized_serial_results' => $normalizedSerialResults
                                ]);
                            
                        } else {
                            // Xử lý serial thường
                            foreach ($serialResults as $label => $value) {
                                if ($shouldAutoPassPending) {
                                    $normalizedSerialResults[$label] = ($value === 'pending' || $value === null || $value === '') ? 'pass' : $value;
                                } else {
                                    $normalizedSerialResults[$label] = ($value === null || $value === '') ? 'pending' : $value;
                                }
                            }
                        }

                        // Lưu serial results trực tiếp vào database
                        if (empty($normalizedSerialResults)) {
                            // Rỗng → lưu NULL để tránh ghi 0 vào cột JSON
                            $item->update(['serial_results' => null]);
                        } else {
                            $item->update(['serial_results' => json_encode($normalizedSerialResults)]);
                        }

                        // Tính toán tự động no_serial_pass_quantity và no_serial_fail_quantity từ serial_results
                        $this->calculateNoSerialQuantities($item, $normalizedSerialResults);
                        
                        // Force refresh item để đảm bảo có dữ liệu mới nhất
                        $item->refresh();

                        // Tính toán lại kết quả của thành phẩm khi có thay đổi vật tư hoặc thành phẩm
                        if ($item->item_type === 'material') {
                            // Khi thay đổi vật tư, tính toán lại tất cả thành phẩm
                            $this->calculateProductResults($testing);
                        } elseif ($item->item_type === 'product') {
                            // Khi thay đổi thành phẩm, chỉ tính toán thành phẩm đó
                            $this->calculateProductResults($testing, $item->id);
                        }

                        Log::info('DEBUG: Đã cập nhật serial_results và tính toán no_serial quantities', [
                            'new_serial_results' => json_encode($normalizedSerialResults),
                            'item_details' => [
                                'id' => $item->id,
                                'material_id' => $item->material_id,
                                'product_id' => $item->product_id,
                                'good_id' => $item->good_id,
                                'item_type' => $item->item_type
                            ]
                        ]);
                    } else {
                        Log::warning('DEBUG: Không tìm thấy testing item', [
                            'item_id' => $itemId,
                            'testing_id' => $testing->id,
                            'search_strategy' => 'tried: id, material_id, product_id, good_id'
                        ]);
                    }
                }
                
                /**
                 * ✨ TỐI ƯU: Xử lý các testing items KHÔNG CÓ trong serial_results
                 * 
                 * Logic: Mặc định tất cả serial_results là "pass"
                 * Frontend chỉ gửi những serial_results có giá trị "fail"
                 * Backend cần set "pass" cho những items không được gửi lên
                 * 
                 * Điều này giảm 90-95% payload khi có nhiều vật tư (500-2000 items)
                 */
                $this->applyDefaultPassForMissingSerials($testing, $serialResultsInput);
            }

            // Update test pass/fail quantities
            if ($request->has('test_pass_quantity')) {
                foreach ($request->test_pass_quantity as $itemId => $detailQuantities) {
                    foreach ($detailQuantities as $detailId => $quantity) {
                        $detail = TestingDetail::find($detailId);
                        if ($detail && $detail->testing_id == $testing->id) {
                            $detail->update(['test_pass_quantity' => $quantity]);
                        }
                    }
                }
            }

            if ($request->has('test_fail_quantity')) {
                foreach ($request->test_fail_quantity as $itemId => $detailQuantities) {
                    foreach ($detailQuantities as $detailId => $quantity) {
                        $detail = TestingDetail::find($detailId);
                        if ($detail && $detail->testing_id == $testing->id) {
                            $detail->update(['test_fail_quantity' => $quantity]);
                        }
                    }
                }
            }

            // Update testing details results if we have test_results in the request
            if ($request->has('test_results')) {
                foreach ($request->test_results as $detailId => $result) {
                    $detail = TestingDetail::find($detailId);
                    if ($detail && $detail->testing_id == $testing->id) {
                        $detail->update(['result' => $result]);
                    }
                }
            }

            // Update testing details notes if we have test_notes in the request
            if ($request->has('test_notes')) {
                foreach ($request->test_notes as $detailId => $note) {
                    $detail = TestingDetail::find($detailId);
                    if ($detail && $detail->testing_id == $testing->id) {
                        $detail->update(['notes' => $note]);
                    }
                }
            }

            // Không cần tính toán tất cả thành phẩm ở đây nữa vì đã tính toán riêng lẻ khi cập nhật serial_results

            DB::commit();

            // Ghi nhật ký cập nhật phiếu kiểm thử
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'testings',
                    'Cập nhật phiếu kiểm thử: ' . $testing->test_code,
                    $oldData,
                    $testing->toArray()
                );
            }

            // Nếu là auto-save thì trả về JSON, nếu không thì redirect
            if ($isAutoSave) {
            return response()->json([
                'success' => true,
                'message' => 'Phiếu kiểm thử đã được cập nhật thành công.',
                'data' => $testing->toArray()
            ]);
            } else {
                return redirect()->route('testing.show', $testing->id)
                    ->with('success', 'Phiếu kiểm thử đã được cập nhật thành công.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật phiếu kiểm thử: ' . $e->getMessage(), [
                'testing_id' => $testing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testing $testing)
    {
        // Không cho phép xóa khi đang thực hiện, đã hoàn thành, hoặc có phiếu lắp ráp liên quan
        if ($testing->status == 'in_progress' || $testing->status == 'completed' || $testing->assembly_id) {
            $errorMessage = 'Không thể xóa phiếu kiểm thử';
            
            if ($testing->status == 'in_progress') {
                $errorMessage .= ' đang thực hiện.';
            } elseif ($testing->status == 'completed') {
                $errorMessage .= ' đã hoàn thành.';
            } elseif ($testing->assembly_id) {
                $errorMessage .= ' có phiếu lắp ráp liên quan.';
            }
            
            return redirect()->back()
                ->with('error', $errorMessage);
        }

        // Lưu dữ liệu cũ trước khi xóa
        $oldData = $testing->toArray();
        $testingCode = $testing->test_code;

        DB::beginTransaction();

        try {
            // Delete related records completely
            $testing->details()->forceDelete();
            $testing->items()->forceDelete();

            // Delete the testing record completely
            $testing->forceDelete();

            DB::commit();

            // Ghi nhật ký xóa phiếu kiểm thử
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'testings',
                    'Xóa hoàn toàn phiếu kiểm thử: ' . $testingCode,
                    $oldData,
                    null
                );
            }

            return redirect()->route('testing.index')
                ->with('success', 'Phiếu kiểm thử đã được xóa hoàn toàn thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Approve a testing record.
     */
    public function approve(Request $request, Testing $testing)
    {
        if ($testing->status != 'pending') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể duyệt phiếu kiểm thử đang ở trạng thái chờ xử lý.');
        }

        // Get employee ID from authenticated user if available
        $employeeId = null;
        if (Auth::check() && Auth::user()->employee) {
            $employeeId = Auth::user()->employee->id;
        }

        DB::beginTransaction();

        try {
            // Cập nhật phiếu kiểm thử
            $testing->update([
                'status' => 'in_progress',
                'approved_by' => $employeeId,
                'approved_at' => now(),
            ]);

            // Đồng bộ trạng thái với Assembly nếu có
            if ($testing->assembly_id) {
                $assembly = Assembly::find($testing->assembly_id);
                if ($assembly) {
                    $assembly->update([
                        'status' => 'in_progress'
                    ]);

                    Log::info('Đồng bộ trạng thái Assembly sau khi duyệt Testing', [
                        'testing_id' => $testing->id,
                        'assembly_id' => $assembly->id,
                        'new_status' => 'in_progress'
                    ]);
                }
            }

            // Tạo thông báo khi duyệt phiếu kiểm thử
            if ($testing->assigned_to) {
                Notification::createNotification(
                    'Phiếu kiểm thử được duyệt',
                    "Phiếu kiểm thử #{$testing->test_code} đã được duyệt và sẵn sàng thực hiện.",
                    'info',
                    $testing->assigned_to,
                    'testing',
                    $testing->id,
                    route('testing.show', $testing->id)
                );
            }

            // Thông báo cho người tiếp nhận kiểm thử
            if ($testing->receiver_id && $testing->receiver_id != $testing->assigned_to) {
                Notification::createNotification(
                    'Phiếu kiểm thử được duyệt',
                    "Phiếu kiểm thử #{$testing->test_code} đã được duyệt và sẵn sàng thực hiện.",
                    'info',
                    $testing->receiver_id,
                    'testing',
                    $testing->id,
                    route('testing.show', $testing->id)
                );
            }

            DB::commit();

            // Ghi nhật ký duyệt phiếu kiểm thử
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'approve',
                    'testings',
                    'Duyệt phiếu kiểm thử: ' . $testing->test_code,
                    null,
                    $testing->toArray()
                );
            }

            return redirect()->back()
                ->with('success', 'Phiếu kiểm thử đã được duyệt thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi khi duyệt phiếu: ' . $e->getMessage());
        }
    }

    /**
     * Reject a testing record.
     */
    public function reject(Request $request, Testing $testing)
    {
        if ($testing->status != 'pending') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể từ chối phiếu kiểm thử đang ở trạng thái chờ xử lý.');
        }

        DB::beginTransaction();

        try {
            // Cập nhật phiếu kiểm thử
            $testing->update([
                'status' => 'cancelled',
            ]);

            // Đồng bộ trạng thái với Assembly nếu có
            if ($testing->assembly_id) {
                $assembly = Assembly::find($testing->assembly_id);
                if ($assembly) {
                    $assembly->update([
                        'status' => 'cancelled'
                    ]);

                    Log::info('Đồng bộ trạng thái Assembly sau khi từ chối Testing', [
                        'testing_id' => $testing->id,
                        'assembly_id' => $assembly->id,
                        'new_status' => 'cancelled'
                    ]);
                }
            }

            // Tạo thông báo khi từ chối phiếu kiểm thử
            if ($testing->assigned_to) {
                Notification::createNotification(
                    'Phiếu kiểm thử bị từ chối',
                    "Phiếu kiểm thử #{$testing->test_code} đã bị từ chối.",
                    'error',
                    $testing->assigned_to,
                    'testing',
                    $testing->id,
                    route('testing.show', $testing->id)
                );
            }

            // Thông báo cho người tiếp nhận kiểm thử
            if ($testing->receiver_id && $testing->receiver_id != $testing->assigned_to) {
                Notification::createNotification(
                    'Phiếu kiểm thử bị từ chối',
                    "Phiếu kiểm thử #{$testing->test_code} đã bị từ chối.",
                    'error',
                    $testing->receiver_id,
                    'testing',
                    $testing->id,
                    route('testing.show', $testing->id)
                );
            }

            DB::commit();

            // Ghi nhật ký từ chối phiếu kiểm thử
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'reject',
                    'testings',
                    'Từ chối phiếu kiểm thử: ' . $testing->test_code,
                    null,
                    $testing->toArray()
                );
            }

            return redirect()->back()
                ->with('success', 'Phiếu kiểm thử đã bị từ chối.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi khi từ chối phiếu: ' . $e->getMessage());
        }
    }

    /**
     * Receive a testing record.
     */
    public function receive(Request $request, Testing $testing)
    {
        if ($testing->status != 'pending') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể tiếp nhận phiếu kiểm thử ở trạng thái Chờ xử lý.');
        }

        DB::beginTransaction();

        try {
            // Get employee ID from authenticated user if available
            $employeeId = null;
            if (Auth::check() && Auth::user()->employee) {
                $employeeId = Auth::user()->employee->id;
            }

            // Cập nhật trạng thái và thông tin tiếp nhận
            $testing->update([
                'status' => 'in_progress',
                'received_by' => $employeeId,
                'received_at' => now(),
            ]);

            // Gửi thông báo đến người phụ trách phiếu lắp ráp liên quan (nếu có)
            if ($testing->assembly && $testing->assembly->assigned_to) {
                Notification::createNotification(
                    'Phiếu kiểm thử đã được tiếp nhận',
                    "Phiếu kiểm thử #{$testing->test_code} đã được tiếp nhận và đang thực hiện.",
                    'info',
                    $testing->assembly->assigned_to,
                    'testing',
                    $testing->id,
                    route('testing.show', $testing->id)
                );
            }

            DB::commit();

            // Log activity
            UserLog::logActivity(
                Auth::id(),
                'receive',
                'testings',
                'Tiếp nhận phiếu kiểm thử: ' . $testing->test_code,
                null,
                $testing->toArray()
            );

            return redirect()->back()
                ->with('success', 'Tiếp nhận phiếu kiểm thử thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi khi tiếp nhận phiếu: ' . $e->getMessage());
        }
    }

    /**
     * Complete a testing record.
     */
    public function complete(Request $request, Testing $testing)
    {
        if ($testing->status != 'in_progress') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể hoàn thành phiếu kiểm thử đang ở trạng thái đang thực hiện.');
        }

        DB::beginTransaction();

        try {
            // Load chi tiết kiểm thử
            $testing->load(['details', 'items']);

            // Kiểm tra items pending dựa vào loại kiểm thử
            $itemsToCheck = $testing->test_type == 'finished_product'
                ? $testing->items->where('item_type', 'product')
                : $testing->items;

            // ĐỒNG BỘ KẾT QUẢ (vật tư/hàng hoá): lấy theo serial_results nếu có
            if ($testing->test_type !== 'finished_product') {
                foreach ($itemsToCheck as $item) {
                    $qty = (int) ($item->quantity ?? 0);
                    if (!empty($item->serial_results)) {
                        $serialResults = json_decode($item->serial_results, true);
                        if (is_array($serialResults)) {
                            $countPass = 0; $countFail = 0; $countPending = 0;
                            foreach ($serialResults as $res) {
                                if ($res === 'pass') $countPass++;
                                elseif ($res === 'fail') $countFail++;
                                else $countPending++;
                            }
                            // Nếu đã chấm đủ số lượng theo serial (không còn pending), đồng bộ pass/fail
                            if (($countPass + $countFail) === $qty && $countPending === 0) {
                                if ((int)($item->pass_quantity ?? 0) !== $countPass || (int)($item->fail_quantity ?? 0) !== $countFail) {
                                    $item->update(['pass_quantity' => $countPass, 'fail_quantity' => $countFail]);
                                }
                            }
                        }
                    }
                }
            }

            // RÀNG BUỘC + ĐỒNG BỘ: Chỉ đồng bộ từ serial_results khi đã chấm đủ serial, nếu còn pending thì chặn hoàn thành
            $blockingMessages = [];
            if ($testing->test_type == 'finished_product') {
                foreach ($itemsToCheck as $item) {
                    if ($item->item_type !== 'product') { continue; }

                    $qty = (int) ($item->quantity ?? 0);
                    $pass = (int) ($item->pass_quantity ?? 0);
                    $fail = (int) ($item->fail_quantity ?? 0);

                    // Nếu có serial thì bắt buộc phải chấm đủ (không còn pending)
                    $serials = [];
                    if (!empty($item->serial_number)) {
                        $serials = array_values(array_filter(array_map('trim', explode(',', $item->serial_number))));
                    }
                    $serialResults = [];
                    if (!empty($item->serial_results)) {
                        $decoded = json_decode($item->serial_results, true);
                        if (is_array($decoded)) { $serialResults = $decoded; }
                    }

                    if (!empty($serials)) {
                        // Đếm kết quả
                        $pending = 0; $countPass = 0; $countFail = 0;
                        foreach ($serialResults as $res) {
                            if ($res === 'pass') $countPass++;
                            elseif ($res === 'fail') $countFail++;
                            else $pending++;
                        }
                        // Nếu còn pending hoặc chưa đủ số lượng, chặn hoàn thành
                        if (($countPass + $countFail) !== $qty || $pending > 0) {
                            $name = $item->product ? $item->product->name : ($item->good->name ?? 'Thành phẩm');
                            $blockingMessages[] = "Thành phẩm '{$name}' chưa chấm đủ kết quả theo serial (còn thiếu hoặc còn 'Chưa có').";
                        } else {
                            // Đã đủ -> đồng bộ pass/fail
                            if ($pass + $fail !== $qty) {
                                $item->update(['pass_quantity' => $countPass, 'fail_quantity' => $countFail]);
                            }
                        }
                    } else {
                        // Không có serial: vẫn phải đảm bảo pass+fail=qty
                        if ($pass + $fail !== $qty) {
                            $name = $item->product ? $item->product->name : ($item->good->name ?? 'Thành phẩm');
                            $blockingMessages[] = "Thành phẩm '{$name}' chưa có đủ số lượng Đạt/Không đạt (cần đúng {$qty}).";
                        }
                    }
                }
            }

            // Nếu có lỗi ràng buộc, dừng lại
            if (!empty($blockingMessages)) {
                DB::rollBack();
                return redirect()->back()->with('error', implode("\n", $blockingMessages));
            }

            // Tính tổng số lượng và kết quả
            $totalQuantity = 0;
            $totalPassQuantity = 0;
            $totalFailQuantity = 0;
            
            foreach ($itemsToCheck as $item) {
                if ($testing->test_type == 'finished_product' && $item->item_type == 'material') {
                    // Đối với vật tư lắp ráp trong phiếu thành phẩm: KHÔNG tính vào tổng
                    // Chỉ tính từ thành phẩm để tránh ảnh hưởng từ vật tư lắp ráp
                    continue;
                }
                
                $passQuantity = (int)($item->pass_quantity ?? 0);
                $failQuantity = (int)($item->fail_quantity ?? 0);
                
                $totalQuantity += $item->quantity;
                $totalPassQuantity += $passQuantity;
                $totalFailQuantity += $failQuantity;
            }

            // Kiểm tra ràng buộc: Số lượng Đạt + Không đạt = Số lượng kiểm thử ban đầu
            $totalResultQuantity = $totalPassQuantity + $totalFailQuantity;
            if ($totalResultQuantity != $totalQuantity) {
                $errorMessage = "Tổng số lượng Đạt + Không đạt ({$totalResultQuantity}) phải bằng tổng số lượng kiểm thử ban đầu ({$totalQuantity}). Vui lòng kiểm tra lại!";
                
                DB::rollBack();
                return redirect()->back()
                    ->with('error', $errorMessage);
            }

            // Tính tỉ lệ đạt
            $passRate = ($totalQuantity > 0) ? round(($totalPassQuantity / $totalQuantity) * 100) : 100;

            // Tạo danh sách các thiết bị không đạt
            $failItems = [];
            foreach ($itemsToCheck as $item) {
                $failQuantity = $item->fail_quantity ?? 0;
                
                if ($failQuantity > 0) {
                    $itemName = '';
                    if ($item->item_type == 'material' && $item->material) {
                        $itemName = $item->material->name;
                    } elseif ($item->item_type == 'product' && $item->product) {
                        $itemName = $item->product->name;
                    } elseif ($item->item_type == 'finished_product' && $item->good) {
                        $itemName = $item->good->name;
                    }
                    $failItems[] = $itemName . ': ' . $failQuantity . ' không đạt';
                }
            }
            $failItemsText = implode("\n", $failItems);

            // Tạo kết luận tự động
            $conclusion = '';
            if ($passRate == 100) {
                $conclusion = 'Kết quả kiểm thử đạt 100%. Tất cả các thiết bị đều đạt yêu cầu.';
            } elseif ($passRate >= 80) {
                $conclusion = "Kết quả kiểm thử đạt mức tốt với {$passRate}% thiết bị đạt tiêu chuẩn. Cần cải thiện các thiết bị không đạt.";
            } elseif ($passRate >= 60) {
                $conclusion = "Kết quả kiểm thử đạt mức trung bình với {$passRate}% thiết bị đạt tiêu chuẩn. Cần cải thiện các thiết bị không đạt.";
            } else {
                $conclusion = "Kết quả kiểm thử không đạt yêu cầu với chỉ {$passRate}% thiết bị đạt tiêu chuẩn. Cần xem xét lại toàn bộ quy trình.";
            }

            // Thêm danh sách các thiết bị không đạt vào kết luận nếu có
            if (!empty($failItemsText)) {
                $conclusion .= " Các thiết bị cần khắc phục: {$failItemsText}.";
            }

            // Cập nhật phiếu kiểm thử
            $testing->update([
                'status' => 'completed',
                'pass_quantity' => $totalPassQuantity,
                'fail_quantity' => $totalFailQuantity,
                'conclusion' => $conclusion,
                'completed_at' => now(),
            ]);

            // Tạo serial records cho các thành phẩm đạt (pass)
            $this->createSerialRecordsForPassedProducts($testing);

            // Đồng bộ trạng thái với Assembly nếu có
            if ($testing->assembly_id) {
                $assembly = Assembly::find($testing->assembly_id);
                if ($assembly) {
                    $assembly->update([
                        'status' => 'completed'
                    ]);

                    // Gửi thông báo cho người phụ trách phiếu lắp ráp
                    if ($assembly->assigned_to) {
                        Notification::createNotification(
                            'Phiếu lắp ráp đã hoàn thành',
                            "Phiếu lắp ráp #{$assembly->code} đã hoàn thành (do phiếu kiểm thử đã hoàn thành).",
                            'success',
                            $assembly->assigned_to,
                            'assembly',
                            $assembly->id,
                            route('assemblies.show', $assembly->id)
                        );
                    }
                }
            }

            // Gửi thông báo cho người phụ trách
            if ($testing->assigned_to) {
                Notification::createNotification(
                    'Phiếu kiểm thử đã hoàn thành',
                    "Phiếu kiểm thử #{$testing->test_code} đã hoàn thành với kết quả: {$passRate}% đạt.",
                    'success',
                    $testing->assigned_to,
                    'testing',
                    $testing->id,
                    route('testing.show', $testing->id)
                );
            }

            DB::commit();

            // Log activity
            if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'complete',
                'testings',
                'Hoàn thành phiếu kiểm thử: ' . $testing->test_code,
                null,
                $testing->toArray()
            );
            }

            return redirect()->back()
                ->with('success', 'Đã hoàn thành phiếu kiểm thử thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi khi hoàn thành phiếu: ' . $e->getMessage());
        }
    }

    /**
     * Update the results of testing items based on pass/fail quantities.
     */
    private function updateItemsResults(Testing $testing, $passQuantity, $failQuantity)
    {
        // Load items if not already loaded
        if (!$testing->relationLoaded('items')) {
            $testing->load('items');
        }

        // Nếu không có items, log và return
        if ($testing->items->isEmpty()) {
            Log::warning('Không có items để cập nhật kết quả', [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code
            ]);
            return;
        }

        // Tổng số items
        $totalItems = $testing->items->count();

        // Tỉ lệ đạt
        $passRate = $passQuantity / ($passQuantity + $failQuantity);

        Log::info('Cập nhật kết quả cho items', [
            'testing_id' => $testing->id,
            'test_code' => $testing->test_code,
            'total_items' => $totalItems,
            'pass_rate' => $passRate
        ]);

        // Số lượng items cần đánh dấu đạt
        $itemsToPass = round($totalItems * $passRate);

        // Cập nhật kết quả cho từng item
        $counter = 0;
        foreach ($testing->items as $item) {
            if ($counter < $itemsToPass) {
                $item->update(['result' => 'pass']);
                Log::info('Cập nhật item thành đạt', [
                    'item_id' => $item->id,
                    'item_type' => $item->item_type
                ]);
            } else {
                $item->update(['result' => 'fail']);
                Log::info('Cập nhật item thành không đạt', [
                    'item_id' => $item->id,
                    'item_type' => $item->item_type
                ]);
            }
            $counter++;
        }
    }

    /**
     * Update inventory based on testing results.
     */
    public function updateInventory(Request $request, Testing $testing)
    {
        if ($testing->status != 'completed') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể cập nhật kho cho phiếu kiểm thử đã hoàn thành.');
        }

        if ($testing->is_inventory_updated) {
            return redirect()->back()
                ->with('error', 'Phiếu kiểm thử này đã được cập nhật vào kho.');
        }

        // Log đầu hàm để debug mọi trường hợp
        Log::info('DEBUG: Vào updateInventory', [
            'testing_id' => $testing->id,
            'test_code' => $testing->test_code,
            'request_data' => $request->all(),
            'status' => $testing->status,
            'is_inventory_updated' => $testing->is_inventory_updated
        ]);
        // Validate cho phép project_export là hợp lệ khi xuất đi dự án
        $rules = [
            'fail_warehouse_id' => 'required|exists:warehouses,id',
        ];
        if ($request->success_warehouse_id !== 'project_export') {
            $rules['success_warehouse_id'] = 'required|exists:warehouses,id';
        } else {
            $rules['success_warehouse_id'] = 'required';
        }
        $request->validate($rules);

        $successWarehouse = Warehouse::find($request->success_warehouse_id);
        $failWarehouse = Warehouse::find($request->fail_warehouse_id);

        if (($request->success_warehouse_id !== 'project_export' && !$successWarehouse) || !$failWarehouse) {
            return redirect()->back()->with('error', 'Kho không tồn tại.');
        }

        // Ràng buộc: Kho đạt và Kho không đạt không được trùng nhau
        if ($request->success_warehouse_id !== 'project_export') {
            if ((string)$request->success_warehouse_id === (string)$request->fail_warehouse_id) {
                return redirect()->back()->with('error', 'Kho đạt và Kho không đạt không được trùng nhau. Vui lòng chọn 2 kho khác nhau.');
            }
        }

        // Logic mới: Cho phép cập nhật ngay cả khi kho đích trùng với kho nguồn
        // Chỉ tạo phiếu chuyển kho khi có sự thay đổi vị trí thực sự
        if ($testing->test_type === 'material') {
            $items = $testing->items; // đã được eager load từ controller khác, nếu chưa Laravel sẽ lazy load
            
            // Log thông tin để debug
            Log::info('Kiểm tra logic kho cho phiếu kiểm thử vật tư', [
                'testing_id' => $testing->id,
                'success_warehouse_id' => $request->success_warehouse_id,
                'fail_warehouse_id' => $request->fail_warehouse_id,
                'items_count' => $items->count()
            ]);
            
            // Thông báo thông tin cho người dùng về việc tạo phiếu chuyển kho
            $willCreateTransfer = false;
            $transferInfo = [];
            
            // Kiểm tra cho chuyển Đạt
            if ($request->success_warehouse_id !== 'project_export') {
                $passItemsAtSameWarehouse = $items->filter(function ($item) {
                    $pq = (int)($item->pass_quantity ?? 0);
                    $pqNa = (int)($item->no_serial_pass_quantity ?? 0);
                    // Nếu có serial_results, tính pass theo serial (ưu tiên)
                    if (!empty($item->serial_results)) {
                        $sr = json_decode($item->serial_results, true);
                        if (is_array($sr)) {
                            $countPass = 0; foreach ($sr as $v) { if ($v === 'pass') { $countPass++; } }
                            $pq = max($pq, $countPass);
                        }
                    }
                    return ($pq + $pqNa) > 0; // có hàng đạt để chuyển
                });
                
                $passItemsAtDifferentWarehouse = $passItemsAtSameWarehouse->filter(function ($item) use ($request) {
                    return (string)$item->warehouse_id !== (string)$request->success_warehouse_id;
                });
                
                if ($passItemsAtDifferentWarehouse->count() > 0) {
                    $willCreateTransfer = true;
                    $transferInfo[] = "Sẽ tạo phiếu chuyển kho Đạt cho " . $passItemsAtDifferentWarehouse->count() . " mặt hàng";
                }
            }

            // Kiểm tra cho chuyển Không đạt
            $failItemsAtSameWarehouse = $items->filter(function ($item) {
                $fq = (int)($item->fail_quantity ?? 0);
                $fqNa = (int)($item->no_serial_fail_quantity ?? 0);
                $pqNa = (int)($item->no_serial_pass_quantity ?? 0);
                // Nếu có serial_results, tính fail theo serial (ưu tiên)
                if (!empty($item->serial_results)) {
                    $sr = json_decode($item->serial_results, true);
                    if (is_array($sr)) {
                        $countFail = 0; $countPass = 0; foreach ($sr as $v) { if ($v === 'fail') { $countFail++; } elseif ($v === 'pass') { $countPass++; } }
                        $fq = max($fq, $countFail);
                        // Ước lượng phần N/A còn lại mặc định vào không đạt nếu chưa khai báo
                        $total = (int)($item->quantity ?? 0);
                        $remaining = max(0, $total - ($countPass + $countFail + $pqNa + $fqNa));
                        $fqNa = max($fqNa, $remaining);
                    }
                }
                return ($fq + $fqNa) > 0; // có hàng không đạt để chuyển
            });
            
            $failItemsAtDifferentWarehouse = $failItemsAtSameWarehouse->filter(function ($item) use ($request) {
                return (string)$item->warehouse_id !== (string)$request->fail_warehouse_id;
            });
            
            if ($failItemsAtDifferentWarehouse->count() > 0) {
                $willCreateTransfer = true;
                $transferInfo[] = "Sẽ tạo phiếu chuyển kho Không đạt cho " . $failItemsAtDifferentWarehouse->count() . " mặt hàng";
            }
            
            // Log thông tin về việc tạo phiếu chuyển kho
            if ($willCreateTransfer) {
                Log::info('Sẽ tạo phiếu chuyển kho', [
                    'testing_id' => $testing->id,
                    'transfer_info' => $transferInfo
                ]);
            } else {
                Log::info('Không cần tạo phiếu chuyển kho vì tất cả hàng hóa đều ở cùng kho đích', [
                    'testing_id' => $testing->id
                ]);
            }
        }

        DB::beginTransaction();

        try {
            $totalPassQuantity = 0;
            $totalFailQuantity = 0;

            // Log để debug
            Log::info('Bắt đầu cập nhật kho cho phiếu kiểm thử', [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code,
                'test_type' => $testing->test_type,
                'success_warehouse_id' => $request->success_warehouse_id,
                'fail_warehouse_id' => $request->fail_warehouse_id,
                'items_count' => $testing->items->count()
            ]);

            // Phân biệt logic theo loại kiểm thử
            if ($testing->test_type == 'material') {
                // Kiểm thử Vật tư/Hàng hóa: chỉ tính tổng số lượng, không cập nhật kho trực tiếp
                // Việc cập nhật kho sẽ được thực hiện thông qua phiếu chuyển kho
                foreach ($testing->items as $item) {
                    $passQuantity = $item->pass_quantity ?? 0;
                    $failQuantity = $item->fail_quantity ?? 0;
                    
                    $totalPassQuantity += $passQuantity;
                    $totalFailQuantity += $failQuantity;
                }
            } else {
                // Kiểm thử Thành phẩm: xử lý thành phẩm và vật tư lắp ráp
                $productItems = $testing->items->where('item_type', 'product');
                $materialItems = $testing->items->where('item_type', 'material');
                
                // Xử lý thành phẩm (chỉ tính tổng; không cập nhật kho trực tiếp để tránh double khi duyệt phiếu nhập)
                foreach ($productItems as $item) {
                    $passQuantity = $item->pass_quantity ?? 0;
                    $failQuantity = $item->fail_quantity ?? 0;
                    
                    $totalPassQuantity += $passQuantity;
                    $totalFailQuantity += $failQuantity;
                }
                
                // Xử lý vật tư lắp ráp (chỉ những vật tư không đạt) - chỉ tính tổng; không cập nhật kho trực tiếp
                if ($materialItems->isNotEmpty()) {
                    foreach ($materialItems as $item) {
                        $passQuantity = (int)($item->pass_quantity ?? 0);
                        $failQuantity = (int)($item->fail_quantity ?? 0);
                        
                        // Nếu có serial_results và đã chấm đủ, đồng bộ lại fail
                            if (!empty($item->serial_results)) {
                            $decoded = json_decode($item->serial_results, true);
                            if (is_array($decoded)) {
                                $countPass = 0; $countFail = 0; $countPending = 0;
                                foreach ($decoded as $res) {
                                    if ($res === 'pass') $countPass++; elseif ($res === 'fail') $countFail++; else $countPending++;
                                }
                                if ($countPending === 0) { $passQuantity = $countPass; $failQuantity = $countFail; }
                            }
                        }

                        if ($failQuantity > 0) {
                            $totalFailQuantity += $failQuantity;
                        }
                    }
                } else if ($testing->assembly && $testing->assembly->materials) {
                    // Trường hợp không có material items trong testing -> suy ra tổng từ assembly materials + serial_results
                    foreach ($testing->assembly->materials as $asmMaterial) {
                        $materialId = $asmMaterial->material_id;
                        $testingItem = $testing->items->firstWhere('material_id', $materialId);
                        $failQuantity = 0;
                        if ($testingItem) {
                            if (!empty($testingItem->serial_results)) {
                                $decoded = json_decode($testingItem->serial_results, true);
                                if (is_array($decoded)) {
                                    foreach ($decoded as $res) { if ($res === 'fail') $failQuantity++; }
                                }
                                    } else {
                                $failQuantity = (int)($testingItem->fail_quantity ?? 0);
                            }
                        }
                        if ($failQuantity > 0) { $totalFailQuantity += $failQuantity; }
                    }
                }
            }

            // Log kết quả trước khi commit
            Log::info('Kết quả cập nhật kho', [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code,
                'total_pass_quantity' => $totalPassQuantity,
                'total_fail_quantity' => $totalFailQuantity,
                'success_warehouse_id' => $request->success_warehouse_id,
                'fail_warehouse_id' => $request->fail_warehouse_id
            ]);

            // Tạo phiếu nhập kho cho phiếu kiểm thử thành phẩm
            $createdImports = [];
            $successDispatch = null; // Khai báo biến để lưu phiếu xuất kho thành phẩm
            if ($testing->test_type == 'finished_product' && $testing->assembly && $testing->assembly->purpose == 'project') {
                // Chỉ tạo phiếu nhập kho cho vật tư không đạt (xuất đi dự án)
                // Chỉ tạo phiếu nhập kho cho vật tư không đạt khi thực sự có vật tư không đạt
                if ($this->hasFailMaterials($testing)) {
                $failImport = $this->createInventoryImport(
                    $testing,
                    $request->fail_warehouse_id,
                    'Vật tư lắp ráp không đạt từ phiếu kiểm thử: ' . $testing->test_code . ' (Xuất đi dự án)',
                    'fail'
                );
                if ($failImport) {
                    $createdImports[] = $failImport;
                    // Tự động duyệt tồn kho (đảm bảo vào kho ngay)
                    $this->approveInventoryImportAutomatically($failImport);
                    }
                } else {
                    Log::info('Không có vật tư không đạt, bỏ qua tạo phiếu nhập kho fail cho dự án', [
                        'testing_id' => $testing->id,
                        'test_code' => $testing->test_code
                    ]);
                }
                
                // TẠO PHIẾU XUẤT KHO THÀNH PHẨM KHI XUẤT ĐI DỰ ÁN
                $successDispatch = $this->createProjectExportDispatch($testing);
                if ($successDispatch) {
                    Log::info('Đã tạo phiếu xuất kho thành phẩm cho dự án', [
                        'testing_id' => $testing->id,
                        'dispatch_id' => $successDispatch->id,
                        'dispatch_code' => $successDispatch->dispatch_code
                    ]);
                }
            } else {
                // Trường hợp lưu kho: tạo 2 phiếu nhập riêng và duyệt ngay
                $successImport = $this->createInventoryImport(
                    $testing,
                    $request->success_warehouse_id,
                    'Thành phẩm đạt từ phiếu kiểm thử: ' . $testing->test_code,
                    'success'
                );
                if ($successImport) { $createdImports[] = $successImport; $this->approveInventoryImportAutomatically($successImport); }

                // Chỉ tạo phiếu nhập kho cho vật tư không đạt khi thực sự có vật tư không đạt
                if ($this->hasFailMaterials($testing)) {
                $failImport = $this->createInventoryImport(
                    $testing,
                    $request->fail_warehouse_id,
                    'Vật tư lắp ráp không đạt từ phiếu kiểm thử: ' . $testing->test_code,
                    'fail'
                );
                if ($failImport) { $createdImports[] = $failImport; $this->approveInventoryImportAutomatically($failImport); }
                } else {
                    Log::info('Không có vật tư không đạt, bỏ qua tạo phiếu nhập kho fail', [
                        'testing_id' => $testing->id,
                        'test_code' => $testing->test_code
                    ]);
                }
            }

            // Tạo phiếu chuyển kho cho phiếu kiểm thử vật tư/hàng hóa
            $createdTransfers = [];
            if ($testing->test_type == 'material') {
                Log::info('Bắt đầu tạo phiếu chuyển kho cho phiếu kiểm thử vật tư/hàng hóa', [
                    'testing_id' => $testing->id,
                    'test_code' => $testing->test_code,
                    'success_warehouse_id' => $request->success_warehouse_id,
                    'fail_warehouse_id' => $request->fail_warehouse_id
                ]);
                
                $createdTransfers = $this->createWarehouseTransfersFromTesting($testing, $request->success_warehouse_id, $request->fail_warehouse_id);
                // Hoàn tất cập nhật tồn kho cho các phiếu vừa tạo
                foreach ($createdTransfers as $transfer) {
                    if ($transfer) {
                        $this->completeWarehouseTransferAutomatically($transfer);
                    }
                }
                
                Log::info('Kết quả tạo phiếu chuyển kho', [
                    'testing_id' => $testing->id,
                    'created_transfers_count' => count($createdTransfers),
                    'transfer_codes' => collect($createdTransfers)->pluck('transfer_code')->toArray()
                ]);
            }

            // Cập nhật trạng thái phiếu kiểm thử
            $testing->update([
                'is_inventory_updated' => true,
                'success_warehouse_id' => $request->success_warehouse_id === 'project_export' ? null : $request->success_warehouse_id,
                'fail_warehouse_id' => $request->fail_warehouse_id,
            ]);

            DB::commit();

            // Tạo thông báo thành công tùy theo loại kiểm thử và mục đích lắp ráp
            if ($testing->test_type == 'finished_product' && $testing->assembly && $testing->assembly->purpose == 'project') {
                $projectName = 'Dự án';
                $projectCode = '';
                
                // Lấy thông tin từ bảng Project thông qua relationship
                if ($testing->assembly->project) {
                    $project = $testing->assembly->project;
                    $projectName = $project->project_name ?? 'Dự án';
                    $projectCode = $project->project_code ?? '';
                }
                
                $projectLabel = trim(($projectCode ? ($projectCode . ' - ') : '') . $projectName);
                $dispatchInfo = $successDispatch ? " và tạo phiếu xuất kho #{$successDispatch->dispatch_code} (đã tự động duyệt)" : "";
                $successMessage = "Đã cập nhật vào kho và tự động duyệt phiếu nhập kho (Dự án cho Thành phẩm đạt: {$projectLabel}, Kho lưu Module Vật tư lắp ráp không đạt: {$failWarehouse->name}){$dispatchInfo} {$totalPassQuantity} đạt / {$totalFailQuantity} không đạt";
            } elseif ($testing->test_type == 'material') {
                $transferInfo = "";
                if (count($createdTransfers) > 0) {
                    $transferInfo = " và tạo " . count($createdTransfers) . " phiếu chuyển kho";
                } else {
                    $transferInfo = " (Lưu ý: Không tạo được phiếu chuyển kho do kho nguồn và kho đích giống nhau)";
                }
                $successMessage = "Đã cập nhật vào kho, tự động duyệt phiếu nhập kho{$transferInfo} (Kho lưu Vật tư/Hàng hóa đạt: " . ($successWarehouse->name ?? 'Chưa có') . ", Kho lưu Vật tư/Hàng hóa không đạt: {$failWarehouse->name}) {$totalPassQuantity} đạt / {$totalFailQuantity} không đạt";
            } else {
                $successMessage = "Đã cập nhật vào kho và tự động duyệt phiếu nhập kho (Kho lưu Thành phẩm đạt: " . ($successWarehouse->name ?? 'Chưa có') . ", Kho lưu Module Vật tư lắp ráp không đạt: {$failWarehouse->name}) {$totalPassQuantity} đạt / {$totalFailQuantity} không đạt";
            }

            // Điều hướng theo nơi gọi: nếu từ danh sách (index) thì quay về index kèm thông báo
            if ($request->has('redirect_to') && $request->redirect_to === 'index') {
                return redirect()->route('testing.index')
                    ->with('success', $successMessage);
            }
            return redirect()->route('testing.show', $testing->id)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi khi cập nhật kho: ' . $e->getMessage());
        }
    }

    /**
     * Save testing results to warehouse (Lưu kho)
     */
    public function saveToWarehouse(Request $request, Testing $testing)
    {
        try {
            // Kiểm tra trạng thái phiếu
            if ($testing->status !== 'completed') {
                return redirect()->back()->with('error', 'Chỉ có thể lưu kho phiếu đã hoàn thành.');
            }

            // Kiểm tra xem đã lưu kho chưa
            if ($testing->is_inventory_updated) {
                return redirect()->back()->with('error', 'Phiếu đã được lưu kho trước đó.');
            }

            DB::beginTransaction();

            // Lấy thông tin kho đạt và kho không đạt
            $successWarehouse = Warehouse::where('type', 'success')->first();
            $failWarehouse = Warehouse::where('type', 'fail')->first();

            if (!$successWarehouse || !$failWarehouse) {
                throw new \Exception('Chưa cấu hình kho đạt hoặc kho không đạt.');
            }

            // Xử lý từng item
            foreach ($testing->items as $item) {
                $quantity = $item->quantity;
                $itemType = $item->item_type;
                $itemId = $item->item_id;

                // Xác định kho đích dựa trên kết quả
                $targetWarehouseId = ($item->result === 'pass') ? $successWarehouse->id : $failWarehouse->id;

                // Cập nhật kho
                $this->updateWarehouseMaterial($itemId, $targetWarehouseId, $quantity, $itemType, [
                    'name' => $item->item_name,
                    'code' => $item->item_code
                ]);
            }

            // Cập nhật trạng thái phiếu
            $testing->update([
                'is_inventory_updated' => true,
                'success_warehouse_id' => $successWarehouse->id,
                'fail_warehouse_id' => $failWarehouse->id,
                'updated_at' => now()
            ]);

            // Ghi log
            UserLog::create([
                'user_id' => Auth::id(),
                'action' => 'save_to_warehouse',
                'table_name' => 'testings',
                'record_id' => $testing->id,
                'description' => "Lưu kho phiếu kiểm thử {$testing->test_code}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Đã lưu kho thành công. Vật tư/hàng hóa đạt đã chuyển vào kho đạt, vật tư/hàng hóa không đạt đã chuyển vào kho không đạt.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving testing to warehouse: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi lưu kho: ' . $e->getMessage());
        }
    }

    /**
     * Update warehouse material quantity.
     */
    private function updateWarehouseMaterial($itemId, $warehouseId, $quantity, $itemType = 'material', $itemInfo = [])
    {
        // Kiểm tra dữ liệu đầu vào
        if (empty($itemId) || !is_numeric($itemId)) {
            Log::error('ID vật tư/sản phẩm không hợp lệ', [
                'itemId' => $itemId,
                'itemType' => $itemType
            ]);
            return;
        }

        if (empty($warehouseId) || !is_numeric($warehouseId)) {
            Log::error('ID kho không hợp lệ', [
                'warehouseId' => $warehouseId,
                'itemId' => $itemId
            ]);
            return;
        }

        if (empty($quantity) || !is_numeric($quantity) || $quantity <= 0) {
            Log::error('Số lượng không hợp lệ', [
                'quantity' => $quantity,
                'itemId' => $itemId,
                'warehouseId' => $warehouseId
            ]);
            return;
        }

        // Kiểm tra item có tồn tại không
        $itemExists = false;
        $itemModel = null;

        if ($itemType == 'material') {
            $itemModel = Material::find($itemId);
            $itemExists = $itemModel !== null;
        } elseif ($itemType == 'product') {
            $itemModel = Product::find($itemId);
            $itemExists = $itemModel !== null;
        } elseif ($itemType == 'good') {
            $itemModel = Good::find($itemId);
            $itemExists = $itemModel !== null;
        }

        if (!$itemExists) {
            Log::error('Không tìm thấy vật tư/sản phẩm/hàng hóa', [
                'itemId' => $itemId,
                'itemType' => $itemType
            ]);
            return;
        }

        // Kiểm tra kho có tồn tại không
        $warehouse = Warehouse::find($warehouseId);
        if (!$warehouse) {
            Log::error('Không tìm thấy kho', [
                'warehouseId' => $warehouseId
            ]);
            return;
        }

        // Log trước khi thực hiện cập nhật
        Log::info('Bắt đầu cập nhật vật tư/sản phẩm/hàng hóa vào kho', [
            'warehouse_id' => $warehouseId,
            'warehouse_name' => $warehouse->name,
            'item_id' => $itemId,
            'item_name' => $itemModel ? $itemModel->name : 'Unknown',
            'item_type' => $itemType,
            'quantity' => $quantity,
            'item_details' => $itemInfo
        ]);

        // Log thêm để debug
        Log::info('Thông tin chi tiết item', [
            'item_exists' => $itemExists,
            'item_model_class' => $itemModel ? get_class($itemModel) : 'null',
            'warehouse_exists' => $warehouse ? true : false
        ]);

        try {
            // Lấy thông tin kho trước khi cập nhật
            $existingWarehouseMaterial = WarehouseMaterial::where([
                'material_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'item_type' => $itemType,
            ])->first();

            $oldQuantity = $existingWarehouseMaterial ? $existingWarehouseMaterial->quantity : 0;

            if ($existingWarehouseMaterial) {
                // Cập nhật bản ghi hiện có
                $newQuantity = $oldQuantity + $quantity;
                $existingWarehouseMaterial->quantity = $newQuantity;
                $existingWarehouseMaterial->save();

                Log::info('Đã cập nhật số lượng vào kho (bản ghi hiện có)', [
                    'warehouse_id' => $warehouseId,
                    'warehouse_name' => $warehouse->name,
                    'item_id' => $itemId,
                    'item_type' => $itemType,
                    'old_quantity' => $oldQuantity,
                    'added_quantity' => $quantity,
                    'new_quantity' => $newQuantity
                ]);
            } else {
                // Tạo bản ghi mới
                $warehouseMaterial = new WarehouseMaterial();
                $warehouseMaterial->material_id = $itemId;
                $warehouseMaterial->warehouse_id = $warehouseId;
                $warehouseMaterial->item_type = $itemType;
                $warehouseMaterial->quantity = $quantity;
                $warehouseMaterial->save();

                Log::info('Đã tạo vật tư/sản phẩm/hàng hóa mới trong kho', [
                    'warehouse_id' => $warehouseId,
                    'warehouse_name' => $warehouse->name,
                    'item_id' => $itemId,
                    'item_type' => $itemType,
                    'quantity' => $quantity,
                    'warehouse_material_id' => $warehouseMaterial->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật kho: ' . $e->getMessage(), [
                'warehouse_id' => $warehouseId,
                'item_id' => $itemId,
                'item_type' => $itemType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check if a test code already exists.
     */
    public function checkTestCode(Request $request)
    {
        $code = $request->query('code');
        $exists = Testing::where('test_code', $code)->exists();
        
        return response()->json([
            'exists' => $exists
        ]);
    }
    
    /**
     * Get materials by type.
     */
    public function getMaterialsByType($type)
    {
        switch ($type) {
            case 'material':
                return Material::where('is_hidden', false)
                    ->select('id', 'code', 'name')
                    ->orderBy('name', 'asc') // Sắp xếp theo tên
                    ->get()
                    ->unique('id'); // Loại bỏ duplicate dựa trên id
            case 'product':
                return Good::where('status', 'active')
                    ->where('is_hidden', false) // Chỉ lấy hàng hóa không bị ẩn
                    ->select('id', 'code', 'name')
                    ->orderBy('name', 'asc') // Sắp xếp theo tên
                    ->get()
                    ->unique('id'); // Loại bỏ duplicate dựa trên id
            default:
                return response()->json([], 404);
        }
    }

    /**
     * Get inventory information for an item.
     */
    public function getInventoryInfo(Request $request, $type, $id, $warehouseId)
    {
        try {
            $query = [
                'warehouse_id' => $warehouseId,
                'item_type' => $type === 'product' ? 'good' : $type
            ];

            if ($type === 'material') {
                $query['material_id'] = $id;
            } elseif ($type === 'product') {
                $query['material_id'] = $id;
            }

            $inventory = WarehouseMaterial::where($query)->first();

            // Lấy danh sách serial numbers
            $serials = [];
            if ($inventory && $inventory->serial_numbers) {
                $serials = explode(',', $inventory->serial_numbers);
            }

            return response()->json([
                'available_quantity' => $inventory ? $inventory->quantity : 0,
                'serials' => $serials
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thông tin tồn kho: ' . $e->getMessage(), [
                'type' => $type,
                'id' => $id,
                'warehouse_id' => $warehouseId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'available_quantity' => 0,
                'serials' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item details by type and id.
     */
    public function getItemDetails(Request $request, $type, $id)
    {
        $item = null;
        $supplierData = null;

        switch ($type) {
            case 'material':
                $item = Material::with('suppliers')->find($id);
                if ($item) {
                    // Lấy nhà cung cấp đầu tiên từ relationship
                    $supplier = $item->suppliers->first();
                    if ($supplier) {
                        $supplierData = [
                            'supplier_id' => $supplier->id,
                            'supplier_name' => $supplier->name
                        ];
                    }
                }
                break;
            case 'product':
                $item = Product::with('materials')->find($id);
                break;
            case 'finished_product':
                $item = Good::with('suppliers')->find($id);
                if ($item) {
                    // Lấy nhà cung cấp đầu tiên từ relationship
                    $supplier = $item->suppliers->first();
                    if ($supplier) {
                        $supplierData = [
                            'supplier_id' => $supplier->id,
                            'supplier_name' => $supplier->name
                        ];
                    } else if ($item->supplier_id) {
                        // Fallback to legacy supplier_id if available
                        $supplier = Supplier::find($item->supplier_id);
                        if ($supplier) {
                            $supplierData = [
                                'supplier_id' => $supplier->id,
                                'supplier_name' => $supplier->name
                            ];
                        }
                    }
                }
                break;
        }

        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        // Thêm thông tin nhà cung cấp vào response
        $response = $item->toArray();
        if ($supplierData) {
            $response['supplier_id'] = $supplierData['supplier_id'];
            $response['supplier_name'] = $supplierData['supplier_name'];
        }

        return response()->json($response);
    }

    /**
     * Print a testing record.
     */
    public function print(Testing $testing)
    {
        $testing->load([
            'tester',
            'approver',
            'receiver',
            'items.material',
            'items.product.materials',
            'items.good',
            'items.supplier',
            'details',
            'assembly.products.product',
            'assembly.product',
            'assembly.assignedEmployee',
            'assembly.materials.material',
            'successWarehouse',
            'failWarehouse'
        ]);

        return view('testing.print', compact('testing'));
    }

    /**
     * Get serial numbers for a specific item.
     */
    public function getSerialNumbers(Request $request)
    {
        $type = $request->type;
        $id = $request->id;
        $serials = [];

        // Lấy danh sách serial từ kho dựa vào loại và ID
        if ($type && $id) {
            switch ($type) {
                case 'material':
                    $serials = WarehouseMaterial::where('material_id', $id)
                        ->where('item_type', 'material')
                        ->whereNotNull('serial_number')
                        ->where('serial_number', '!=', '')
                        ->pluck('serial_number')
                        ->toArray();
                    break;
                case 'product':
                    $serials = WarehouseMaterial::where('material_id', $id)
                        ->where('item_type', 'good')
                        ->whereNotNull('serial_number')
                        ->where('serial_number', '!=', '')
                        ->pluck('serial_number')
                        ->toArray();
                    break;
                case 'finished_product':
                    $serials = WarehouseMaterial::where('material_id', $id)
                        ->where('item_type', 'good')
                        ->whereNotNull('serial_number')
                        ->where('serial_number', '!=', '')
                        ->pluck('serial_number')
                        ->toArray();
                    break;
            }

            // Nếu không có serial thực, tạo dữ liệu mẫu để demo
            if (empty($serials)) {
                $itemName = '';
                switch ($type) {
                    case 'material':
                        $material = Material::find($id);
                        $itemName = $material ? $material->name : '';
                        break;
                    case 'product':
                        $product = Product::find($id);
                        $itemName = $product ? $product->name : '';
                        break;
                    case 'finished_product':
                        $good = Good::find($id);
                        $itemName = $good ? $good->name : '';
                        break;
                }

                if ($itemName) {
                    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $itemName), 0, 3));
                    for ($i = 1; $i <= 5; $i++) {
                        $serials[] = $prefix . '-' . date('Ym') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
                    }
                }
            }
        }

        return response()->json($serials);
    }

    /**
     * Check if any testing details or items are pending.
     */
    public function checkPending(Testing $testing)
    {
        // Load thiết bị và hạng mục kiểm thử
        $testing->load(['items', 'details']);

        // Đếm số lượng thiết bị và hạng mục đang pending
        $pendingItems = $testing->items->where('result', 'pending')->count();
        $pendingDetails = $testing->details->where('result', 'pending')->count();

        return response()->json([
            'has_pending' => ($pendingItems > 0 || $pendingDetails > 0),
            'pending_details' => $pendingDetails,
            'pending_items' => $pendingItems
        ]);
    }

    /**
     * Get available serials for testing items.
     */
    public function getAvailableSerials(Request $request)
    {
        $type = $request->get('type');
        $itemId = $request->get('item_id');
        $warehouseId = $request->get('warehouse_id');
        $quantity = $request->get('quantity', 1);

        Log::info('Testing getAvailableSerials', [
            'type' => $type,
            'itemId' => $itemId,
            'warehouseId' => $warehouseId,
            'quantity' => $quantity
        ]);

        $serials = [];

        if ($type && $itemId && $warehouseId) {
            // Lấy danh sách serial từ warehouse_materials (tồn kho thực tế)
            switch ($type) {
                case 'material':
                    $warehouseMaterial = \App\Models\WarehouseMaterial::where([
                        'material_id' => $itemId,
                        'item_type' => 'material',
                        'warehouse_id' => $warehouseId
                    ])->first();
                    
                    // Chỉ lấy serial khi có tồn kho > 0
                    if ($warehouseMaterial && $warehouseMaterial->quantity > 0 && !empty($warehouseMaterial->serial_number)) {
                        $serialArray = json_decode($warehouseMaterial->serial_number, true);
                        if (is_array($serialArray)) {
                            foreach ($serialArray as $serialNumber) {
                                if (!empty($serialNumber)) {
                                    $serials[] = [
                                        'serial_number' => $serialNumber,
                                        'quantity' => 1
                                    ];
                                }
                            }
                        }
                    }
                    break;
                    
                case 'product':
                    $warehouseMaterial = \App\Models\WarehouseMaterial::where([
                        'material_id' => $itemId,
                        'item_type' => 'good',
                        'warehouse_id' => $warehouseId
                    ])->first();
                    
                    // Chỉ lấy serial khi có tồn kho > 0
                    if ($warehouseMaterial && $warehouseMaterial->quantity > 0 && !empty($warehouseMaterial->serial_number)) {
                        $serialArray = json_decode($warehouseMaterial->serial_number, true);
                        if (is_array($serialArray)) {
                            foreach ($serialArray as $serialNumber) {
                                if (!empty($serialNumber)) {
                                    $serials[] = [
                                        'serial_number' => $serialNumber,
                                        'quantity' => 1
                                    ];
                                }
                            }
                        }
                    }
                    break;
            }

            Log::info('Serials from warehouse_materials', [
                'type' => $type,
                'itemId' => $itemId,
                'warehouseId' => $warehouseId,
                'warehouse_quantity' => $warehouseMaterial ? $warehouseMaterial->quantity : 0,
                'serials_count' => count($serials),
                'serials' => $serials
            ]);

            // Nếu không có serial, thêm option "Không có Serial"
            if (empty($serials)) {
                $serials[] = [
                    'serial_number' => '',
                    'quantity' => 0
                ];
            }
        }

        Log::info('Final response', ['serials' => $serials]);

        return response()->json(['serials' => $serials]);
    }

    /**
     * Tạo phiếu nhập kho từ phiếu kiểm thử thành phẩm
     */
    private function createInventoryImportsFromTesting($testing, $successWarehouseId, $failWarehouseId)
    {
        $createdImports = [];
        
        try {
            // Tạo phiếu nhập kho cho thành phẩm đạt
            $successImport = $this->createInventoryImport(
                $testing,
                $successWarehouseId,
                'Thành phẩm đạt từ phiếu kiểm thử: ' . $testing->test_code,
                'success'
            );
            if ($successImport) {
                $createdImports[] = $successImport;
            }

            // Kiểm tra xem có vật tư không đạt không trước khi tạo phiếu
            $hasFailMaterials = $this->hasFailMaterials($testing);
            
            if ($hasFailMaterials) {
                // Chỉ tạo phiếu nhập kho cho vật tư không đạt khi thực sự có vật tư không đạt
            $failImport = $this->createInventoryImport(
                $testing,
                $failWarehouseId,
                'Vật tư lắp ráp không đạt từ phiếu kiểm thử: ' . $testing->test_code,
                'fail'
            );
            if ($failImport) {
                $createdImports[] = $failImport;
                }
            } else {
                Log::info('Không có vật tư không đạt, bỏ qua tạo phiếu nhập kho fail', [
                    'testing_id' => $testing->id,
                    'test_code' => $testing->test_code
                ]);
            }

            Log::info('Đã tạo phiếu nhập kho từ phiếu kiểm thử', [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code,
                'created_imports' => count($createdImports)
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo phiếu nhập kho từ phiếu kiểm thử: ' . $e->getMessage(), [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code
            ]);
        }

        return $createdImports;
    }

    /**
     * Kiểm tra xem có vật tư không đạt không
     */
    private function hasFailMaterials($testing)
    {
        // Kiểm tra nếu có thành phẩm không đạt dựa trên serial_results
        $failedProducts = $testing->items->where('item_type', 'product')->filter(function($item) {
            if (empty($item->serial_results)) {
                return false;
            }
            
            $serialResults = json_decode($item->serial_results, true);
            if (!is_array($serialResults)) {
                return false;
            }
            
            // Kiểm tra có ít nhất 1 serial fail không
            foreach ($serialResults as $result) {
                if ($result === 'fail') {
                    return true;
                }
            }
            
            return false;
        });
        
        if ($failedProducts->isEmpty()) {
            return false;
        }
        
        // Kiểm tra vật tư của thành phẩm không đạt
        if ($testing->assembly && $testing->assembly->materials) {
            foreach ($failedProducts as $failedProduct) {
                $targetProductId = $failedProduct->product_id ?? $failedProduct->good_id;
                if (!$targetProductId) continue;
                
                Log::info('DEBUG: Checking failed product', [
                    'product_id' => $targetProductId,
                    'serial_results' => $failedProduct->serial_results
                ]);
                
                // Lấy vật tư từ assembly cho thành phẩm này
                $assemblyMaterials = $testing->assembly->materials->where('target_product_id', $targetProductId);
                
                Log::info('DEBUG: Assembly materials found', [
                    'count' => $assemblyMaterials->count(),
                    'materials' => $assemblyMaterials->pluck('material_id')->toArray()
                ]);
                
                // Lấy tất cả testing items cho materials này
                $allTestingItems = $testing->items->where('item_type', 'material')
                    ->sortBy('id')
                    ->values();
                
                // Kiểm tra tất cả testing items xem có fail không
                foreach ($allTestingItems as $testingItem) {
                    $hasFail = false;
                    
                    Log::info('DEBUG: Checking testing item', [
                        'material_id' => $testingItem->material_id,
                        'testing_item_id' => $testingItem->id,
                        'serial_results' => $testingItem->serial_results
                    ]);
                    
                    // Kiểm tra serial_results của vật tư
                    if (!empty($testingItem->serial_results)) {
                        $serialResults = json_decode($testingItem->serial_results, true);
                        if (is_array($serialResults)) {
                            foreach ($serialResults as $result) {
                                if ($result === 'fail') {
                                    $hasFail = true;
                                    Log::info('DEBUG: Found fail in serial_results', [
                                        'material_id' => $testingItem->material_id,
                                        'result' => $result
                                    ]);
                                    break;
                                }
                            }
                        }
                    }
                    
                    // Kiểm tra no_serial_fail_quantity của vật tư
                    if ((int)($testingItem->no_serial_fail_quantity ?? 0) > 0) {
                        $hasFail = true;
                        Log::info('DEBUG: Found fail in no_serial_fail_quantity', [
                            'material_id' => $testingItem->material_id,
                            'no_serial_fail_quantity' => $testingItem->no_serial_fail_quantity
                        ]);
                    }
                    
                    // Nếu có vật tư không đạt, trả về true
                    if ($hasFail) {
                        Log::info('DEBUG: Returning true - has fail materials', [
                            'material_id' => $testingItem->material_id
                        ]);
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Tạo một phiếu nhập kho
     */
    private function createInventoryImport($testing, $warehouseId, $notes, $type)
    {
        try {
            // SAFETY GUARD: Không tạo phiếu nhập kho cho phiếu kiểm thử Vật tư/Hàng hóa
            if (isset($testing->test_type) && $testing->test_type === 'material') {
                Log::warning('BỎ QUA tạo phiếu nhập kho vì test_type=material', [
                    'testing_id' => $testing->id ?? null,
                    'warehouse_id' => $warehouseId,
                    'type' => $type,
                ]);
                return null;
            }
            // Tạo mã phiếu nhập
            $importCode = $this->generateInventoryImportCode();
            
            // Tạo phiếu nhập kho
            // Lấy supplier hợp lệ thay vì gán cứng 1
            $supplierId = \App\Models\Supplier::orderBy('id')->value('id');
            if (!$supplierId) {
                throw new \Exception('Không tìm thấy nhà cung cấp nào để gán cho phiếu nhập kho');
            }

            $inventoryImport = \App\Models\InventoryImport::create([
                'supplier_id' => $supplierId,
                'warehouse_id' => $warehouseId,
                'import_code' => $importCode,
                'import_date' => now(),
                'order_code' => 'Từ phiếu kiểm thử: ' . $testing->test_code,
                'notes' => $notes,
                'status' => 'approved' // Tự động duyệt phiếu nhập kho từ kiểm thử
            ]);

            // Thêm materials vào phiếu nhập kho
            $this->addMaterialsToInventoryImport($inventoryImport, $testing, $type);

            // Tự động cập nhật kho khi tạo phiếu nhập kho từ kiểm thử
            // $this->approveInventoryImportAutomatically($inventoryImport);

            return $inventoryImport;

        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo phiếu nhập kho: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo mã phiếu nhập kho
     */
    private function generateInventoryImportCode()
    {
        $prefix = 'NK';
        $date = date('ymd');
        
        do {
            $randomNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $newCode = $prefix . $date . $randomNumber;
            $exists = \App\Models\InventoryImport::where('import_code', $newCode)->exists();
        } while ($exists);
        
        return $newCode;
    }

    /**
     * Thêm materials vào phiếu nhập kho
     */
    private function addMaterialsToInventoryImport($inventoryImport, $testing, $type)
    {
        $items = [];
        
        if ($type == 'success') {
            // Lấy thành phẩm đạt
            $items = $testing->items->where('item_type', 'product')->filter(function($item) {
                return ($item->pass_quantity ?? 0) > 0;
            });
            
            // KHÔNG lấy vật tư từ assembly vào phiếu thành phẩm
            // Vật tư sẽ được xử lý riêng trong phiếu vật tư hư hỏng
        } else {
            // Lấy thành phẩm không đạt từ testing items
            $failedProducts = $testing->items->where('item_type', 'product')->filter(function($item) {
                return ($item->fail_quantity ?? 0) > 0;
            });
            
            // Nếu không có thành phẩm nào không đạt, không tạo phiếu nhập kho fail
            if ($failedProducts->isEmpty()) {
                Log::info('Không có thành phẩm nào không đạt, bỏ qua tạo phiếu nhập kho fail', [
                    'testing_id' => $testing->id,
                    'test_code' => $testing->test_code
                ]);
                return;
            }
            
            Log::info('DEBUG: Tìm thấy thành phẩm không đạt', [
                'failed_products_count' => $failedProducts->count(),
                'failed_products' => $failedProducts->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'good_id' => $item->good_id,
                        'fail_quantity' => $item->fail_quantity
                    ];
                })->toArray()
            ]);
            
            // Lấy vật tư từ assembly của các thành phẩm không đạt
            $items = collect();
            if ($testing->assembly && $testing->assembly->materials) {
                foreach ($failedProducts as $failedProduct) {
                    $targetProductId = $failedProduct->product_id ?? $failedProduct->good_id;
                    if (!$targetProductId) continue;

                    // Xác định các đơn vị (unit index) bị fail từ serial_results của thành phẩm fail
                    $failedUnits = [];
                    if (!empty($failedProduct->serial_results)) {
                        $sr = json_decode($failedProduct->serial_results, true);
                        if (is_array($sr)) {
                            foreach ($sr as $label => $val) {
                                if ($val === 'fail') {
                                    $failedUnits[] = (int)(ord(strtoupper($label)) - 65); // A=0, B=1, ...
                                }
                            }
                        }
                    }

                    // Thành phẩm không đạt → vật tư của CÁC UNIT bị fail là không đạt
                    $assemblyMaterials = $testing->assembly->materials
                        ->where('target_product_id', $targetProductId);
                    
                    // Chỉ lấy các vật tư thuộc những unit bị fail (failedUnits)
                    if (!empty($failedUnits)) {
                        $assemblyMaterials = $assemblyMaterials->filter(function($am) use ($failedUnits) {
                            $unitIndex = (int)($am->product_unit ?? 0);
                            return in_array($unitIndex, $failedUnits);
                        });
                    }
                    
                    Log::info('DEBUG: Vật tư từ assembly cho thành phẩm không đạt', [
                        'target_product_id' => $targetProductId,
                        'failed_units' => $failedUnits,
                        'assembly_materials_count' => $assemblyMaterials->count(),
                        'assembly_materials' => $assemblyMaterials->map(function($am) {
                            return [
                                'id' => $am->id,
                                'material_id' => $am->material_id,
                                'quantity' => $am->quantity,
                                'serial' => $am->serial
                            ];
                        })->toArray()
                    ]);
                    
                    foreach ($assemblyMaterials as $assemblyMaterial) {
                        // Logic đúng: Thành phẩm không đạt → TẤT CẢ vật tư của thành phẩm đó đều không đạt
                        $materialId = $assemblyMaterial->material_id;
                        $totalQuantity = (int)($assemblyMaterial->quantity ?? 0);
                        $unitSerials = [];
                        if (!empty($assemblyMaterial->serial)) {
                            $unitSerials = array_values(array_filter(array_map('trim', explode(',', $assemblyMaterial->serial))));
                        }
                        $quantityToAdd = $totalQuantity > 0 ? $totalQuantity : 1;
                        
                        Log::info('DEBUG: Thêm vật tư từ thành phẩm không đạt vào phiếu nhập kho', [
                            'material_id' => $materialId,
                            'quantity' => $quantityToAdd,
                            'serial' => $assemblyMaterial->serial
                        ]);
                        
                        $items->push((object) [
                            'item_type' => 'material',
                            'material_id' => $materialId,
                            'quantity' => $quantityToAdd,
                            'serial_number' => !empty($unitSerials) ? implode(',', $unitSerials) : null,
                            'pass_quantity' => 0,
                            'fail_quantity' => $quantityToAdd
                        ]);
                    }
                }
            }
            
            Log::info('DEBUG: Vật tư được lấy cho phiếu nhập kho fail', [
                'total_items_count' => $items->count(),
                'items' => $items->map(function($item) {
                    return [
                        'item_type' => $item->item_type,
                        'material_id' => $item->material_id,
                        'quantity' => $item->quantity,
                        'pass_quantity' => $item->pass_quantity,
                        'fail_quantity' => $item->fail_quantity
                    ];
                })->toArray()
            ]);
        }


        Log::info('DEBUG: Tổng số items để tạo phiếu nhập kho', [
            'type' => $type,
            'count' => $items->count()
        ]);
        
        foreach ($items as $item) {
            // Xác định item_type và material_id
            $itemType = $item->item_type ?? 'material';
            $materialId = $item->material_id ?? $item->product_id ?? $item->good_id;
            
            // Xác định quantity dựa trên loại item và type của phiếu nhập
            $quantity = 0;
            if ($itemType == 'product') {
                if ($type == 'success') {
                    // Thành phẩm đạt: lấy pass_quantity
                    $quantity = $item->pass_quantity ?? 0;
                } else {
                    // Thành phẩm không đạt: lấy fail_quantity
                    $quantity = $item->fail_quantity ?? 0;
                }
            } elseif ($itemType == 'material') {
                if ($type == 'success') {
                    // Vật tư đạt: chỉ lấy phần ĐẠT (serial pass + N/A pass nếu có)
                    $passQuantity = (int)($item->pass_quantity ?? 0);
                    $noSerialPass = (int)($item->no_serial_pass_quantity ?? 0);
                    $quantity = $passQuantity + $noSerialPass;
                    if ($quantity === 0) { $quantity = (int)($item->quantity ?? 0); } // Fallback nhẹ khi dữ liệu thiếu
                } else {
                    // Vật tư không đạt: lấy đúng số lượng từ dòng assembly đã push vào $items
                    // KHÔNG cộng dồn theo serial/N/A để tránh lệch số lượng
                    $quantity = (int)($item->quantity ?? 0);
                }
            }
            
            Log::info('DEBUG: Xử lý item cho phiếu nhập kho', [
                'item_type' => $itemType,
                'material_id' => $materialId,
                'quantity' => $quantity,
                'pass_quantity' => $item->pass_quantity ?? 0,
                'fail_quantity' => $item->fail_quantity ?? 0
            ]);
            
            // Nhánh FAIL cho vật tư: giữ nguyên quantity theo assembly, bỏ mọi tính toán bổ sung
            if ($type == 'fail' && $itemType == 'material') {
                $quantity = (int)($item->quantity ?? $quantity);
            }
            
            if ($quantity > 0 && $materialId) {
                // Xử lý serial numbers nếu có
                $serialNumbers = null;
                if (!empty($item->serial_number)) {
                    $serialArray = explode(',', $item->serial_number);
                    $serialArray = array_map('trim', $serialArray);
                    $serialArray = array_filter($serialArray);
                    
                    // Nếu có serial_results, lọc serial theo kết quả tương ứng: success→pass, fail→fail
                    $hasResultsMap = false;
                    if (!empty($item->serial_results)) {
                        $serialResults = json_decode($item->serial_results, true);
                        if (is_array($serialResults)) {
                            $hasResultsMap = true;
                            $selected = [];
                            foreach ($serialArray as $index => $serial) {
                                $label = $this->labelFromIndex($index);
                                $res = $serialResults[$label] ?? null;
                                if ($type === 'fail') { if ($res === 'fail') { $selected[] = $serial; } }
                                else { if ($res === 'pass') { $selected[] = $serial; } }
                            }
                            Log::info('DEBUG: Lọc serial theo kết quả', [
                                'type' => $type,
                                'item_id' => $item->id,
                                'selected' => $selected,
                                'all' => $serialArray,
                                'results' => $serialResults
                            ]);
                            // Khi có serial_results, KHÔNG fallback toàn bộ; dùng đúng danh sách đã chọn (có thể rỗng)
                            $serialNumbers = $selected;
                        }
                    }
                    // Fallback CHỈ khi không có serial_results hợp lệ: dùng toàn bộ serial hiện có
                    if (!$hasResultsMap && $serialNumbers === null && count($serialArray) > 0) { $serialNumbers = $serialArray; }
                }

                \App\Models\InventoryImportMaterial::create([
                    'inventory_import_id' => $inventoryImport->id,
                    'material_id' => $materialId,
                    'warehouse_id' => $inventoryImport->warehouse_id,
                    'quantity' => $quantity,
                    'serial_numbers' => $serialNumbers,
                    'notes' => $type == 'success' ? 
                        ($itemType == 'product' ? 'Thành phẩm đạt từ kiểm thử' : 'Vật tư lắp ráp từ kiểm thử') : 
                        ($itemType == 'product' ? 'Thành phẩm không đạt từ kiểm thử' : 'Vật tư lắp ráp không đạt từ kiểm thử'),
                    'item_type' => $itemType
                ]);
            }
        }
    }

    /**
     * Tự động duyệt phiếu nhập kho và cập nhật kho
     */
    private function approveInventoryImportAutomatically($inventoryImport)
    {
        try {
            // Cập nhật số lượng tồn kho và serial numbers
            foreach ($inventoryImport->materials as $material) {
                // Cập nhật số lượng vật tư/thành phẩm/hàng hóa trong kho
                $warehouseMaterial = \App\Models\WarehouseMaterial::firstOrNew([
                    'warehouse_id' => $material->warehouse_id,
                    'material_id' => $material->material_id,
                    'item_type' => $material->item_type
                ]);

                $currentQty = $warehouseMaterial->quantity ?? 0;
                $warehouseMaterial->quantity = $currentQty + $material->quantity;

                // Cập nhật serial_number vào warehouse_materials nếu có serial
                if (!empty($material->serial_numbers)) {
                    $serials = is_array($material->serial_numbers) ? $material->serial_numbers : json_decode($material->serial_numbers, true);
                    $currentSerials = [];
                    if (!empty($warehouseMaterial->serial_number)) {
                        $currentSerials = json_decode($warehouseMaterial->serial_number, true) ?: [];
                    }
                    // Gộp serial cũ và mới, loại bỏ trùng lặp
                    $mergedSerials = array_unique(array_merge($currentSerials, $serials));
                    $warehouseMaterial->serial_number = json_encode($mergedSerials);
                }

                // Lưu warehouse material sau khi cập nhật quantity và serial
                $warehouseMaterial->save();

                // Lưu serial numbers vào bảng serials (nếu có)
                if (!empty($material->serial_numbers)) {
                    foreach ($material->serial_numbers as $serialNumber) {
                        \App\Models\Serial::create([
                            'serial_number' => $serialNumber,
                            'product_id' => $material->material_id,
                            'type' => $material->item_type,
                            'status' => 'active',
                            'notes' => $material->notes ?? null,
                            'warehouse_id' => $material->warehouse_id,
                        ]);
                    }
                }

                // Lưu nhật ký thay đổi khi phiếu được duyệt
                $itemType = $material->item_type;
                $itemId = $material->material_id;

                if ($itemType == 'material') {
                    $materialLS = \App\Models\Material::find($itemId);
                } else if ($itemType == 'good') {
                    $materialLS = \App\Models\Good::find($itemId);
                } else if ($itemType == 'product') {
                    // Xử lý thành phẩm - material_id chứa ID của Product hoặc Good
                    $materialLS = \App\Models\Product::find($itemId);
                    if (!$materialLS) {
                        // Nếu không tìm thấy Product, thử tìm Good
                        $materialLS = \App\Models\Good::find($itemId);
                    }
                }

                // Debug log để kiểm tra
                Log::info('DEBUG: Xử lý item cho nhật ký', [
                    'item_type' => $itemType,
                    'material_id' => $material->material_id,
                    'product_id' => $material->product_id ?? 'null',
                    'good_id' => $material->good_id ?? 'null',
                    'itemId' => $itemId,
                    'found_model' => $materialLS ? get_class($materialLS) . ' - ' . $materialLS->name : 'null'
                ]);

                if ($materialLS) {
                    // Lấy thông tin kho nhập để đưa vào description
                    $warehouse = \App\Models\Warehouse::find($material->warehouse_id);
                    $warehouseName = $warehouse ? $warehouse->name : 'Không xác định';

                    \App\Helpers\ChangeLogHelper::nhapKho(
                        $materialLS->code,
                        $materialLS->name,
                        $material->quantity,
                        $inventoryImport->import_code,
                        $warehouseName,
                        $material->notes
                    );
                }
            }

            // Ghi log tự động duyệt phiếu nhập kho
            Log::info('Tự động duyệt phiếu nhập kho từ kiểm thử', [
                'import_code' => $inventoryImport->import_code,
                'warehouse_id' => $inventoryImport->warehouse_id,
                'materials_count' => $inventoryImport->materials->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi tự động duyệt phiếu nhập kho: ' . $e->getMessage(), [
                'import_code' => $inventoryImport->import_code
            ]);
        }
    }

    // Generate Excel-like labels: 0->A, 25->Z, 26->AA, 27->AB, ...
    private function labelFromIndex(int $index): string
    {
        $label = '';
        $n = $index;
        do {
            $rem = $n % 26;
            $label = chr(65 + $rem) . $label;
            $n = intdiv($n, 26) - 1;
        } while ($n >= 0);
        return $label;
    }

    /**
     * Tạo phiếu chuyển kho từ phiếu kiểm thử
     */
    private function createWarehouseTransfersFromTesting($testing, $successWarehouseId, $failWarehouseId)
    {
        $createdTransfers = [];
        
        try {
            // Tạo phiếu chuyển kho cho vật tư/hàng hóa đạt
            $successTransfer = $this->createWarehouseTransfer(
                $testing,
                $successWarehouseId,
                'Vật tư/Hàng hóa đạt từ phiếu kiểm thử: ' . $testing->test_code,
                'success'
            );
            if ($successTransfer) {
                // Có thể trả về 1 hoặc nhiều phiếu (theo từng kho nguồn)
                if (is_array($successTransfer)) {
                    $createdTransfers = array_merge($createdTransfers, $successTransfer);
                } else {
                $createdTransfers[] = $successTransfer;
                }
            }

            // Kiểm tra xem có vật tư không đạt không trước khi tạo phiếu chuyển kho
            // Đối với phiếu kiểm thử Vật tư/Hàng hóa: kiểm theo fail_quantity của chính items
            // Đối với phiếu kiểm thử Thành phẩm: dùng logic hasFailMaterials (theo vật tư của TP không đạt)
            $hasFailItems = ($testing->test_type === 'material')
                ? $testing->items->filter(function ($item) {
                    $fq = (int)($item->fail_quantity ?? 0);
                    $fqNa = (int)($item->no_serial_fail_quantity ?? 0);
                    return ($fq + $fqNa) > 0;
                })->isNotEmpty()
                : $this->hasFailMaterials($testing);
            
            if ($hasFailItems) {
            // Tạo phiếu chuyển kho cho vật tư/hàng hóa không đạt
            $failTransfer = $this->createWarehouseTransfer(
                $testing,
                $failWarehouseId,
                'Vật tư/Hàng hóa không đạt từ phiếu kiểm thử: ' . $testing->test_code,
                'fail'
            );
            if ($failTransfer) {
                if (is_array($failTransfer)) {
                    $createdTransfers = array_merge($createdTransfers, $failTransfer);
                } else {
                $createdTransfers[] = $failTransfer;
                }
                }
            } else {
                Log::info('Không có vật tư không đạt, bỏ qua tạo phiếu chuyển kho fail', [
                    'testing_id' => $testing->id,
                    'test_code' => $testing->test_code
                ]);
            }

            Log::info('Đã tạo phiếu chuyển kho từ phiếu kiểm thử', [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code,
                'created_transfers' => count($createdTransfers)
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo phiếu chuyển kho từ phiếu kiểm thử: ' . $e->getMessage(), [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code
            ]);
        }

        return $createdTransfers;
    }

    /**
     * Tạo một phiếu chuyển kho
     */
    private function createWarehouseTransfer($testing, $destinationWarehouseId, $notes, $type)
    {
        try {
            // Tạo mã phiếu chuyển kho
            $transferCode = $this->generateWarehouseTransferCode();
            
            // Lấy items cần chuyển kho
            $items = [];
            if ($type == 'success') {
                // Lấy vật tư/hàng hóa đạt (bao gồm cả N/A đạt)
                // Nhưng cần tách biệt: chỉ lấy items có pass_quantity > 0 HOẶC no_serial_pass_quantity > 0
                $items = $testing->items->filter(function($item) {
                    $pq = (int)($item->pass_quantity ?? 0);
                    $pqNa = (int)($item->no_serial_pass_quantity ?? 0);
                    return ($pq + $pqNa) > 0;
                });
            } else {
                // Kiểm tra xem có vật tư không đạt không trước khi xử lý
                $hasFailForTransfer = ($testing->test_type === 'material')
                    ? $testing->items->filter(function ($item) {
                        $fq = (int)($item->fail_quantity ?? 0);
                        $fqNa = (int)($item->no_serial_fail_quantity ?? 0);
                        $pq = (int)($item->pass_quantity ?? 0);
                        $pqNa = (int)($item->no_serial_pass_quantity ?? 0);
                        $srPass = 0; $srFail = 0;
                        if (!empty($item->serial_results)) {
                            $sr = json_decode($item->serial_results, true);
                            if (is_array($sr)) { foreach ($sr as $v) { if ($v === 'pass') $srPass++; elseif ($v === 'fail') $srFail++; } }
                        }
                        $remaining = max(0, (int)($item->quantity ?? 0) - ($srPass + $srFail + $pqNa + $fqNa));
                        return ($fq + $fqNa + $remaining) > 0;
                    })->isNotEmpty()
                    : $this->hasFailMaterials($testing);

                if (!$hasFailForTransfer) {
                    Log::info('Không có vật tư không đạt, bỏ qua tạo phiếu chuyển kho fail', [
                        'testing_id' => $testing->id,
                        'test_code' => $testing->test_code
                    ]);
                    return null; // Không tạo phiếu chuyển kho nếu không có vật tư không đạt
                }
                
                // Lấy vật tư/hàng hóa không đạt
                $items = $testing->items->filter(function($item) {
                    $fq = (int)($item->fail_quantity ?? 0);
                    $fqNa = (int)($item->no_serial_fail_quantity ?? 0);
                    return ($fq + $fqNa) > 0;
                });
            }

            // Log để debug items được lọc
            Log::info('Items được lọc cho phiếu chuyển kho', [
                'type' => $type,
                'items_count' => $items->count(),
                'items_details' => $items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'item_type' => $item->item_type,
                        'material_id' => $item->material_id,
                        'product_id' => $item->product_id,
                        'good_id' => $item->good_id,
                        'pass_quantity' => $item->pass_quantity,
                        'fail_quantity' => $item->fail_quantity,
                        'warehouse_id' => $item->warehouse_id
                    ];
                })->toArray()
            ]);

            if ($items->isEmpty()) {
                return null; // Không có gì để chuyển
            }

            // Nhóm items theo kho nguồn thực tế để tạo 1 phiếu cho mỗi kho nguồn
            $itemsByWarehouse = $items->groupBy('warehouse_id');
            $createdTransfers = [];

            foreach ($itemsByWarehouse as $sourceWarehouseId => $itemsInSource) {
                // Logic mới: Chỉ tạo phiếu chuyển kho khi có sự thay đổi vị trí thực sự
                // Nếu kho nguồn và kho đích giống nhau thì bỏ qua phiếu cho kho đó
                if ((string)$sourceWarehouseId === (string)$destinationWarehouseId) {
                    Log::info('Kho nguồn và kho đích giống nhau, bỏ qua tạo phiếu chuyển kho', [
                        'warehouse_id' => $sourceWarehouseId,
                        'type' => $type,
                        'reason' => 'Không có sự thay đổi vị trí thực sự'
                    ]);
                    continue;
                }

                // Tạo phiếu chuyển kho cho kho nguồn hiện tại
                $transferCode = $this->generateWarehouseTransferCode();
            $warehouseTransfer = \App\Models\WarehouseTransfer::create([
                'transfer_code' => $transferCode,
                    'source_warehouse_id' => $sourceWarehouseId,
                'destination_warehouse_id' => $destinationWarehouseId,
                    'material_id' => $itemsInSource->first()->material_id ?? $itemsInSource->first()->product_id ?? $itemsInSource->first()->good_id ?? 1,
                    'employee_id' => $testing->tester_id ?? 1,
                    'quantity' => $itemsInSource->sum(function($item) use ($type) {
                    if ($type == 'success') {
                        // Vật tư đạt: pass_quantity + no_serial_pass_quantity
                        $passQuantity = (int)($item->pass_quantity ?? 0);
                        $noSerialPass = (int)($item->no_serial_pass_quantity ?? 0);
                        return $passQuantity + $noSerialPass;
                    } else {
                        // Vật tư không đạt: fail_quantity + no_serial_fail_quantity
                        $failQuantity = (int)($item->fail_quantity ?? 0);
                        $noSerialFail = (int)($item->no_serial_fail_quantity ?? 0);
                        return $failQuantity + $noSerialFail;
                    }
                }),
                'transfer_date' => now(),
                    'status' => 'completed',
                'notes' => $notes,
            ]);

                // Thêm materials vào phiếu chuyển kho theo kho nguồn hiện tại
                foreach ($itemsInSource as $item) {
                // ƯU TIÊN dùng tổng pass/fail đã chốt nếu đã đủ (tránh cộng trùng N/A)
                $totalQty = (int)($item->quantity ?? 0);
                $finalPass = (int)($item->pass_quantity ?? 0);
                $finalFail = (int)($item->fail_quantity ?? 0);
                $hasCompleteTotals = ($finalPass + $finalFail) === $totalQty && $totalQty > 0;

                if ($hasCompleteTotals) {
                    $quantity = ($type == 'success') ? $finalPass : $finalFail;
                } else {
                    // Chưa đủ tổng → fallback: đếm theo serial_results + N/A đã nhập
                    $srPass = 0; $srFail = 0;
                    if (!empty($item->serial_results)) {
                        $sr = json_decode($item->serial_results, true);
                        if (is_array($sr)) {
                            foreach ($sr as $v) { if ($v === 'pass') { $srPass++; } elseif ($v === 'fail') { $srFail++; } }
                        }
                    }
                    if ($type == 'success') {
                        $quantity = $srPass + (int)($item->no_serial_pass_quantity ?? 0);
                    } else {
                        $quantity = $srFail + (int)($item->no_serial_fail_quantity ?? 0);
                    }
                }

                // FIX: Không tự động cộng "phần còn lại" (remaining) vào phiếu không đạt.
                // Lấy đúng số lượng từ các trường đã được tính/saved trong phiếu kiểm thử
                // để đảm bảo số lượng ở phiếu chuyển kho khớp 100% với giao diện kết quả kiểm thử.

                if ($quantity > 0) {
                    // Xác định item_type và material_id
                    $itemType = $item->item_type;
                    $materialId = $item->material_id ?? $item->product_id ?? $item->good_id;

                    // Phân biệt đúng loại item dựa trên dữ liệu thực tế
                    if ($item->item_type == 'product') {
                        if ($item->good_id) {
                            // Nếu có good_id thì đây là hàng hóa
                            $itemType = 'good';
                            $materialId = $item->good_id;
                        } elseif ($item->product_id) {
                            // Nếu có product_id thì đây là thành phẩm
                            $itemType = 'product';
                            $materialId = $item->product_id;
                        }
                    }

                    // Log để debug việc phân biệt loại item
                    Log::info('Phân biệt loại item cho phiếu chuyển kho', [
                        'original_item_type' => $item->item_type,
                        'final_item_type' => $itemType,
                        'material_id' => $item->material_id,
                        'product_id' => $item->product_id,
                        'good_id' => $item->good_id,
                        'final_material_id' => $materialId
                    ]);

                    if ($materialId) {
                        // Chuẩn hóa và lọc serial theo kết quả pass/fail nếu có
                        $selectedSerials = null;
                        if (!empty($item->serial_number)) {
                            $rawSerials = array_values(array_filter(array_map('trim', explode(',', $item->serial_number))));
                            if (!empty($item->serial_results)) {
                                $serialResults = json_decode($item->serial_results, true);
                                if (is_array($serialResults)) {
                                    $tmp = [];
                                    foreach ($rawSerials as $idx => $serial) {
                                        $label = chr(65 + $idx); // A=0, B=1...
                                        $res = $serialResults[$label] ?? null;
                                        if ($type === 'success' && $res === 'pass') { $tmp[] = $serial; }
                                        if ($type === 'fail' && $res === 'fail') { $tmp[] = $serial; }
                                    }
                                    if (!empty($tmp)) { $selectedSerials = $tmp; }
                                }
                            }
                            if ($selectedSerials === null && !empty($rawSerials)) {
                                // Fallback: chỉ dùng toàn bộ serial khi không có serial_results
                                if (empty($item->serial_results)) {
                                    $selectedSerials = $rawSerials;
                                } else {
                                    // Nếu có serial_results nhưng không lọc được serial nào phù hợp thì để trống
                                    $selectedSerials = [];
                                }
                            }
                        }

                        // Log trước khi tạo WarehouseTransferMaterial
                        Log::info('Tạo WarehouseTransferMaterial', [
                            'warehouse_transfer_id' => $warehouseTransfer->id,
                            'material_id' => $materialId,
                            'quantity' => $quantity,
                            'type' => $itemType,
                            'selected_serials' => $selectedSerials,
                            'item_details' => [
                                'item_id' => $item->id,
                                'item_type' => $item->item_type,
                                'material_id' => $item->material_id,
                                'product_id' => $item->product_id,
                                'good_id' => $item->good_id
                            ]
                        ]);

                        \App\Models\WarehouseTransferMaterial::create([
                            'warehouse_transfer_id' => $warehouseTransfer->id,
                            'material_id' => $materialId,
                            'quantity' => $quantity,
                            'type' => $itemType, // Sử dụng 'type' thay vì 'item_type'
                            'serial_numbers' => (!empty($selectedSerials)) ? json_encode($selectedSerials) : null,
                            'notes' => $type == 'success' ? 'Vật tư/Hàng hóa đạt từ kiểm thử' : 'Vật tư/Hàng hóa không đạt từ kiểm thử',
                        ]);

                        // Ghi lại vào testing_items phần N/A đã phân bổ thêm (để DB phản ánh đúng)
                        if ($testing->test_type === 'material') {
                            try {
                                $ti = \App\Models\TestingItem::find($item->id);
                                if ($ti) {
                                    if ($type === 'fail') {
                                        $srPass = 0; $srFail = 0;
                                        if (!empty($ti->serial_results)) {
                                            $sr = json_decode($ti->serial_results, true);
                                            if (is_array($sr)) { foreach ($sr as $v) { if ($v === 'pass') $srPass++; elseif ($v === 'fail') $srFail++; } }
                                        }
                                        $pqNa = (int)($ti->no_serial_pass_quantity ?? 0);
                                        $fqNa = (int)($ti->no_serial_fail_quantity ?? 0);
                                        $total = (int)($ti->quantity ?? 0);
                                        $remaining = max(0, $total - ($srPass + $srFail + $pqNa + $fqNa));
                                        if ($remaining > 0) {
                                            // DISABLED: Logic cũ tự động cập nhật no_serial_fail_quantity
                                            // Bây giờ sử dụng calculateNoSerialQuantities() từ serial_results
                                            // $ti->no_serial_fail_quantity = $fqNa + $remaining;
                                            // $ti->save();
                                        }
                                    }
                                }
                            } catch (\Throwable $e) {}
                        }
                    }
                }
            }

            // Tự động hoàn thành phiếu chuyển kho
            $this->completeWarehouseTransferAutomatically($warehouseTransfer);
                $createdTransfers[] = $warehouseTransfer;
            }

            // Trả về mảng phiếu đã tạo (nếu chỉ có 1 sẽ là mảng 1 phần tử)
            return count($createdTransfers) === 1 ? $createdTransfers[0] : $createdTransfers;

        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo phiếu chuyển kho: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo mã phiếu chuyển kho
     */
    private function generateWarehouseTransferCode()
    {
        $prefix = 'CT';
        $date = date('ymd');
        
        do {
            $randomNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $newCode = $prefix . $date . $randomNumber;
            $exists = \App\Models\WarehouseTransfer::where('transfer_code', $newCode)->exists();
        } while ($exists);
        
        return $newCode;
    }

    /**
     * Tự động hoàn thành phiếu chuyển kho
     */
    private function completeWarehouseTransferAutomatically($warehouseTransfer)
    {
        try {
            // Idempotent guard: tránh xử lý lặp nếu đã chạy trước đó
            $guardNote = '[AUTOPROC_DONE]';
            if (is_string($warehouseTransfer->notes) && strpos($warehouseTransfer->notes, $guardNote) !== false) {
                Log::warning('Bỏ qua hoàn tất chuyển kho do đã xử lý trước đó', [
                    'transfer_code' => $warehouseTransfer->transfer_code
                ]);
                return;
            }
            // Gắn cờ đã xử lý vào notes (không ảnh hưởng nội dung cũ)
            $warehouseTransfer->notes = trim(($warehouseTransfer->notes ?? '') . ' ' . $guardNote);
            $warehouseTransfer->save();
            // Cập nhật số lượng tồn kho
            foreach ($warehouseTransfer->materials as $material) {
                // Giảm số lượng từ kho nguồn
                $sourceWarehouseMaterial = \App\Models\WarehouseMaterial::where([
                    'warehouse_id' => $warehouseTransfer->source_warehouse_id,
                    'material_id' => $material->material_id,
                    'item_type' => $material->type // Sử dụng 'type' thay vì 'item_type'
                ])->first();

                // Fallback: nếu không tìm thấy do sai lệch type ('product' vs 'good')
                if (!$sourceWarehouseMaterial) {
                    $altType = ($material->type === 'product') ? 'good' : 'product';
                    $sourceWarehouseMaterial = \App\Models\WarehouseMaterial::where([
                        'warehouse_id' => $warehouseTransfer->source_warehouse_id,
                        'material_id' => $material->material_id,
                        'item_type' => $altType
                    ])->first();
                    
                    Log::info('Fallback tìm WarehouseMaterial với type khác', [
                        'original_type' => $material->type,
                        'fallback_type' => $altType,
                        'material_id' => $material->material_id,
                        'found' => $sourceWarehouseMaterial ? 'yes' : 'no'
                    ]);
                }

                if ($sourceWarehouseMaterial) {
                    $oldQuantity = $sourceWarehouseMaterial->quantity;
                    $sourceWarehouseMaterial->quantity = max(0, $sourceWarehouseMaterial->quantity - $material->quantity);
                    $sourceWarehouseMaterial->save();

                    // Nếu có serial_numbers trong phiếu chuyển, loại bỏ khỏi kho nguồn
                    if (!empty($material->serial_numbers) && !empty($sourceWarehouseMaterial->serial_number)) {
                        $movedSerials = $this->normalizeSerialArray($material->serial_numbers);
                        $currentSerials = $this->normalizeSerialArray($sourceWarehouseMaterial->serial_number);
                        if (!empty($movedSerials) && !empty($currentSerials)) {
                            // So sánh theo giá trị đã trim, không phân biệt khoảng trắng
                            $remainingSerials = array_values(array_udiff(
                                $currentSerials,
                                $movedSerials,
                                function ($a, $b) { return strcasecmp(trim($a), trim($b)); }
                            ));
                            $sourceWarehouseMaterial->serial_number = json_encode($remainingSerials);
                            $sourceWarehouseMaterial->save();
                        }
                    }

                    Log::info('Đã trừ số lượng từ kho nguồn', [
                        'warehouse_id' => $warehouseTransfer->source_warehouse_id,
                        'material_id' => $material->material_id,
                        'item_type' => $material->type,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $sourceWarehouseMaterial->quantity,
                        'deducted_quantity' => $material->quantity
                    ]);
                } else {
                    Log::warning('Không tìm thấy vật tư trong kho nguồn để trừ số lượng', [
                        'warehouse_id' => $warehouseTransfer->source_warehouse_id,
                        'material_id' => $material->material_id,
                        'item_type' => $material->type
                    ]);
                }

                // Chỉ tăng vào kho đích khi đã trừ được từ kho nguồn (idempotent)
                if (isset($sourceWarehouseMaterial)) {
                    $destinationWarehouseMaterial = \App\Models\WarehouseMaterial::firstOrNew([
                        'warehouse_id' => $warehouseTransfer->destination_warehouse_id,
                        'material_id' => $material->material_id,
                        'item_type' => $material->type // Sử dụng 'type' thay vì 'item_type'
                    ]);

                    $currentQty = $destinationWarehouseMaterial->quantity ?? 0;
                    $destinationWarehouseMaterial->quantity = $currentQty + $material->quantity;
                
                Log::info('Đã tăng số lượng vào kho đích', [
                    'warehouse_id' => $warehouseTransfer->destination_warehouse_id,
                    'material_id' => $material->material_id,
                    'item_type' => $material->type,
                    'old_quantity' => $currentQty,
                    'new_quantity' => $destinationWarehouseMaterial->quantity,
                    'added_quantity' => $material->quantity
                ]);

                // Cập nhật serial_number vào warehouse_materials nếu có serial
                    if (!empty($material->serial_numbers)) {
                        $serials = $this->normalizeSerialArray($material->serial_numbers);
                        $currentSerials = [];
                        if (!empty($destinationWarehouseMaterial->serial_number)) {
                            $currentSerials = $this->normalizeSerialArray($destinationWarehouseMaterial->serial_number);
                        }
                        // Gộp serial cũ và mới, loại bỏ trùng lặp và trim
                        $mergedSerials = array_values(array_unique(array_map(function($s){return trim($s);}, array_merge($currentSerials, $serials))));
                        $destinationWarehouseMaterial->serial_number = json_encode($mergedSerials);
                    }
                    $destinationWarehouseMaterial->save();
                }

                // Lưu nhật ký chuyển kho
                $itemType = $material->type; // Sử dụng 'type' thay vì 'item_type'
                $itemId = $material->material_id;

                if ($itemType == 'material') {
                    $materialLS = \App\Models\Material::find($itemId);
                } else if ($itemType == 'good') {
                    $materialLS = \App\Models\Good::find($itemId);
                }

                if ($materialLS) {
                    $sourceWarehouse = \App\Models\Warehouse::find($warehouseTransfer->source_warehouse_id);
                    $destinationWarehouse = \App\Models\Warehouse::find($warehouseTransfer->destination_warehouse_id);
                    
                    \App\Helpers\ChangeLogHelper::chuyenKho(
                        $materialLS->code,
                        $materialLS->name,
                        $material->quantity,
                        $warehouseTransfer->transfer_code,
                        "Chuyển từ " . ($sourceWarehouse ? $sourceWarehouse->name : 'Kho không xác định') . " sang " . ($destinationWarehouse ? $destinationWarehouse->name : 'Kho không xác định'),
                        [
                            'source_warehouse_id' => $warehouseTransfer->source_warehouse_id,
                            'source_warehouse_name' => $sourceWarehouse ? $sourceWarehouse->name : 'Kho không xác định',
                            'destination_warehouse_id' => $warehouseTransfer->destination_warehouse_id,
                            'destination_warehouse_name' => $destinationWarehouse ? $destinationWarehouse->name : 'Kho không xác định',
                        ],
                        $warehouseTransfer->notes
                    );
                }
            }

            // Ghi log tự động hoàn thành phiếu chuyển kho
            Log::info('Tự động hoàn thành phiếu chuyển kho từ kiểm thử', [
                'transfer_code' => $warehouseTransfer->transfer_code,
                'source_warehouse_id' => $warehouseTransfer->source_warehouse_id,
                'destination_warehouse_id' => $warehouseTransfer->destination_warehouse_id,
                'materials_count' => $warehouseTransfer->materials->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi tự động hoàn thành phiếu chuyển kho: ' . $e->getMessage(), [
                'transfer_code' => $warehouseTransfer->transfer_code
            ]);
        }
    }

    /**
     * Tìm TestingItem chính xác dựa trên serial number
     * Giải quyết vấn đề khi có nhiều items cùng material_id
     */
    private function findMatchingTestingItemBySerial($candidateItems, $serialResults)
    {
        Log::info('DEBUG: Tìm TestingItem chính xác từ candidates', [
            'candidates_count' => $candidateItems->count(),
            'serial_results' => $serialResults
        ]);

        // Tạo mảng để lưu các ứng viên phù hợp với từng serial
        $matchedCandidates = [];
        $exactMatches = [];

        foreach ($candidateItems as $candidate) {
            Log::info('DEBUG: Kiểm tra candidate', [
                'candidate_id' => $candidate->id,
                'candidate_serial_number' => $candidate->serial_number,
                'candidate_material_id' => $candidate->material_id
            ]);

            // Nếu candidate có serial_number, kiểm tra xem có khớp với serial_results không
            if (!empty($candidate->serial_number)) {
                $serials = array_map('trim', explode(',', $candidate->serial_number));
                
                // Kiểm tra xem serial_results có chứa serial nào của candidate này không
                foreach ($serialResults as $label => $result) {
                    $index = ord(strtoupper($label)) - 65; // A=0, B=1, C=2...
                    if (isset($serials[$index]) && !empty($serials[$index])) {
                        // Nếu serial khớp chính xác với kết quả
                        if (strtolower($serials[$index]) === strtolower($result)) {
                            Log::info('DEBUG: Tìm thấy item khớp chính xác serial', [
                                'candidate_id' => $candidate->id,
                                'serial_index' => $index,
                                'serial_value' => $serials[$index],
                                'label' => $label,
                                'result' => $result
                            ]);
                            $exactMatches[] = $candidate;
                        }
                        
                        // Thêm vào danh sách ứng viên phù hợp
                        $matchedCandidates[] = $candidate;
                    }
                }
            }
        }

        // Ưu tiên trả về item khớp chính xác serial
        if (!empty($exactMatches)) {
            Log::info('DEBUG: Trả về item khớp chính xác serial', [
                'exact_match_id' => $exactMatches[0]->id
            ]);
            return $exactMatches[0];
        }
        
        // Nếu có ứng viên phù hợp, trả về ứng viên đầu tiên
        if (!empty($matchedCandidates)) {
            Log::info('DEBUG: Trả về item phù hợp với serial', [
                'matched_candidate_id' => $matchedCandidates[0]->id
            ]);
            return $matchedCandidates[0];
        }

        // Nếu không tìm thấy item khớp serial, trả về item đầu tiên (fallback)
        Log::warning('DEBUG: Không tìm thấy item khớp serial, dùng fallback', [
            'fallback_item_id' => $candidateItems->first()->id
        ]);
        return $candidateItems->first();
    }

    /**
     * Kiểm tra và cập nhật lại pass/fail quantities cho vật tư không có serial
     * Giải quyết vấn đề tính toán sai khi có nhiều testing cùng material_id
     */
    public function recalculateNoSerialQuantities(Request $request, Testing $testing)
    {
        try {
            if ($testing->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể tính toán lại cho phiếu đã hoàn thành'
                ]);
            }

            DB::beginTransaction();

            // Load assembly materials
            $testing->loadMissing('assembly.materials', 'assembly.project');
            
            if (!$testing->assembly || !$testing->assembly->materials) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có dữ liệu assembly materials'
                ]);
            }

            $updatedItems = [];
            $totalPass = 0;
            $totalFail = 0;

            // Xử lý từng unit
            foreach ($testing->assembly->materials->groupBy('product_unit') as $unitIdx => $materialsInUnit) {
                // Lấy số lượng N/A đã nhập từ notes
                $currentNotes = $testing->notes ?? '';
                $noSerialData = json_decode($currentNotes, true) ?: [];
                $unitPassQuantity = $noSerialData['no_serial_pass_quantity'][$unitIdx] ?? 0;

                if ($unitPassQuantity > 0) {
                    // Tạo danh sách vật tư không có serial
                    $noSerialRows = [];
                    foreach ($materialsInUnit as $asmMaterial) {
                        $quantity = (int) ($asmMaterial->quantity ?? 0);
                        $serialCount = 0;
                        if (!empty($asmMaterial->serial)) {
                            $serialArray = array_values(array_filter(array_map('trim', explode(',', $asmMaterial->serial))));
                            $serialCount = count($serialArray);
                        }
                        $noSerialCount = max(0, $quantity - $serialCount);
                        if ($noSerialCount > 0) {
                            $noSerialRows[] = [
                                'material_id' => $asmMaterial->material_id,
                                'no_serial_count' => $noSerialCount
                            ];
                        }
                    }

                    $remainingPass = $unitPassQuantity;

                    foreach ($noSerialRows as $row) {
                        if ($remainingPass <= 0) break;

                        $materialId = $row['material_id'];
                        $noSerialCount = $row['no_serial_count'];

                        // Tìm TestingItem thuộc về testing hiện tại
                        $item = TestingItem::where('testing_id', $testing->id)
                            ->where('material_id', $materialId)
                            ->first();

                        if ($item) {
                            // Tính pass/fail từ serial_results
                            $serialPass = 0;
                            $serialFail = 0;
                            if (!empty($item->serial_results)) {
                                $serialResults = json_decode($item->serial_results, true);
                                if (is_array($serialResults)) {
                                    foreach ($serialResults as $label => $val) {
                                        if ($val === 'pass') $serialPass++;
                                        if ($val === 'fail') $serialFail++;
                                    }
                                }
                            }

                            // Tính toán số lượng pass mới cho N/A
                            $allocatePass = min($noSerialCount, $remainingPass);
                            
                            // Tổng pass = pass từ serial + pass từ N/A
                            $newPass = $serialPass + $allocatePass;
                            
                            // Tổng fail = fail từ serial + (N/A còn lại)
                            $remainingNoSerial = $noSerialCount - $allocatePass;
                            $newFail = $serialFail + $remainingNoSerial;

                            // Cập nhật item
                            $item->update([
                                'pass_quantity' => $newPass,
                                'fail_quantity' => $newFail,
                            ]);

                            $updatedItems[] = [
                                'item_id' => $item->id,
                                'material_id' => $materialId,
                                'serial_pass' => $serialPass,
                                'serial_fail' => $serialFail,
                                'allocated_pass' => $allocatePass,
                                'final_pass' => $newPass,
                                'final_fail' => $newFail
                            ];

                            $totalPass += $newPass;
                            $totalFail += $newFail;

                            $remainingPass -= $allocatePass;
                        }
                    }
                }
            }

            DB::commit();

            Log::info('Đã tính toán lại pass/fail quantities cho vật tư không serial', [
                'testing_id' => $testing->id,
                'updated_items_count' => count($updatedItems),
                'total_pass' => $totalPass,
                'total_fail' => $totalFail
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã tính toán lại thành công',
                'data' => [
                    'updated_items' => $updatedItems,
                    'total_pass' => $totalPass,
                    'total_fail' => $totalFail
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tính toán lại pass/fail quantities: ' . $e->getMessage(), [
                'testing_id' => $testing->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * So sánh hai mảng serial numbers để tìm item khớp
     */
    private function serialNumbersMatch($candidateSerials, $assemblySerials)
    {
        // Loại bỏ các giá trị rỗng
        $candidateSerials = array_filter($candidateSerials);
        $assemblySerials = array_filter($assemblySerials);
        
        // Nếu cả hai đều rỗng, coi như khớp
        if (empty($candidateSerials) && empty($assemblySerials)) {
            return true;
        }
        
        // Nếu một trong hai rỗng, không khớp
        if (empty($candidateSerials) || empty($assemblySerials)) {
            return false;
        }
        
        // So sánh từng serial number (không phân biệt hoa thường)
        foreach ($assemblySerials as $assemblySerial) {
            $found = false;
            foreach ($candidateSerials as $candidateSerial) {
                if (strtolower(trim($assemblySerial)) === strtolower(trim($candidateSerial))) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Chuẩn hóa notes về dạng mảng. Nếu notes là text thuần thì đặt vào general_note.
     */
    private function normalizeNotesArray($notes)
    {
        if (is_array($notes)) {
            $arr = $notes;
        } else if (is_string($notes) && $notes !== '') {
            $decoded = json_decode($notes, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $arr = $decoded;
            } else {
                $arr = ['general_note' => $notes];
            }
        } else {
            $arr = [];
        }
        
        // Đảm bảo có cấu trúc cần thiết cho no_serial_pass_quantity
        if (!isset($arr['no_serial_pass_quantity'])) {
            $arr['no_serial_pass_quantity'] = [];
        }
        
        return $arr;
    }

    /**
     * Chuẩn hóa input serials về mảng string thuần (trim, bỏ rỗng) từ các định dạng:
     * - JSON string: "[\"S1\",\"S2\"]"
     * - CSV string: "S1,S2"
     * - Array: ['S1','S2']
     */
    private function normalizeSerialArray($value)
    {
        if (is_array($value)) {
            $arr = $value;
        } else if (is_string($value)) {
            $trimmed = trim($value);
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $arr = $decoded;
            } else {
                $arr = array_map('trim', explode(',', $trimmed));
            }
        } else {
            $arr = [];
        }
        // Chuẩn hóa: trim và loại bỏ rỗng
        $arr = array_values(array_filter(array_map(function ($s) {
            return is_string($s) ? trim($s) : $s;
        }, $arr)));
        return $arr;
    }

    /**
     * Tạo phiếu xuất kho thành phẩm cho dự án (không ảnh hưởng tồn kho)
     */
    private function createProjectExportDispatch(Testing $testing)
    {
        try {
            // Tạo mã phiếu xuất kho tự động
            $exportCode = 'XK' . date('ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Lấy thông tin dự án từ assembly relationship
            $projectName = 'Dự án';
            $projectCode = 'N/A';
            
            // Lấy thông tin từ bảng Project thông qua relationship
            if ($testing->assembly->project) {
                $project = $testing->assembly->project;
                $projectName = $project->project_name ?? 'Dự án';
                $projectCode = $project->project_code ?? 'N/A';
                
                Log::info('DEBUG: Project info loaded', [
                    'project_id' => $project->id,
                    'project_name' => $projectName,
                    'project_code' => $projectCode
                ]);
            } else {
                Log::warning('DEBUG: No project relationship found', [
                    'assembly_id' => $testing->assembly->id,
                    'assembly_purpose' => $testing->assembly->purpose,
                    'project_id' => $testing->assembly->project_id
                ]);
            }
            
            // Tạo phiếu xuất kho
            $dispatch = \App\Models\Dispatch::create([
                'dispatch_code' => $exportCode,
                'dispatch_date' => now(),
                'dispatch_type' => 'project',
                'dispatch_detail' => 'contract', // Xuất theo hợp đồng
                'project_id' => $testing->assembly->project_id ?? null,
                'project_receiver' => $projectCode . ' - ' . $projectName . ' (Xuất đi dự án)',
                'warranty_period' => null,
                'company_representative_id' => $testing->tester_id ?? Auth::id() ?? 1,
                'dispatch_note' => 'Sinh từ phiếu kiểm thử: ' . $testing->test_code . ' (Xuất đi dự án)',
                'status' => 'approved', // TỰ ĐỘNG DUYỆT - không cần duyệt thủ công
                'created_by' => Auth::id() ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Tạo dispatch items cho thành phẩm đạt
            foreach ($testing->items->where('item_type', 'product') as $item) {
                $passQuantity = (int)($item->pass_quantity ?? 0);
                if ($passQuantity > 0) {
                    // Xử lý serial numbers đúng cách để tránh double encoding
                    $serialNumbers = [];
                    
                    // Lấy serial numbers từ serial_number (nếu có)
                    if (!empty($item->serial_number)) {
                        $serialNumbers = array_values(array_filter(array_map('trim', explode(',', $item->serial_number))));
                    }
                    
                    // Nếu có serial_results, lọc chỉ lấy serial có kết quả 'pass'
                    if (!empty($item->serial_results)) {
                        $serialResults = json_decode($item->serial_results, true);
                        if (is_array($serialResults) && !empty($serialNumbers)) {
                            $passSerials = [];
                            foreach ($serialResults as $label => $result) {
                                if ($result === 'pass') {
                                    $index = ord(strtoupper($label)) - 65; // A=0, B=1, C=2...
                                    if (isset($serialNumbers[$index])) {
                                        $passSerials[] = $serialNumbers[$index];
                                    }
                                }
                            }
                            // Nếu có serial pass, sử dụng serial pass
                            if (!empty($passSerials)) {
                                $serialNumbers = $passSerials;
                            }
                        }
                    }
                    
                    // Tạo DispatchItem với serial_numbers là array thuần (không encode JSON)
                    \App\Models\DispatchItem::create([
                        'dispatch_id' => $dispatch->id,
                        'warehouse_id' => null, // KHÔNG CÓ KHO XUẤT (N/A)
                        'item_type' => 'product',
                        'item_id' => $item->product_id ?? $item->good_id,
                        'quantity' => $passQuantity,
                        'category' => 'contract',
                        'notes' => 'Thành phẩm đạt từ kiểm thử (xuất đi dự án)',
                        'serial_numbers' => $serialNumbers, // Truyền array thuần, Laravel sẽ tự cast
                    ]);
                    
                    // GHI NHẬT KÝ THAY ĐỔI VẬT TƯ CHO PHIẾU XUẤT KHO THÀNH PHẨM
                    $productModel = null;
                    if ($item->product_id) {
                        $productModel = \App\Models\Product::find($item->product_id);
                    } elseif ($item->good_id) {
                        $productModel = \App\Models\Good::find($item->good_id);
                    }
                    
                    if ($productModel) {
                        \App\Helpers\ChangeLogHelper::xuatKho(
                            $productModel->code,
                            $productModel->name,
                            $passQuantity,
                            $dispatch->dispatch_code,
                            'Xuất đi dự án: ' . $projectName,
                            [
                                'project_id' => $testing->assembly->project_id ?? null,
                                'project_name' => $projectName,
                                'project_code' => $projectCode,
                                'testing_id' => $testing->id,
                                'testing_code' => $testing->test_code,
                                'warehouse_id' => null, // N/A - không có kho xuất
                                'serial_numbers' => $serialNumbers,
                                'dispatch_type' => 'project',
                                'dispatch_detail' => 'contract'
                            ],
                            'Thành phẩm đạt từ kiểm thử (xuất đi dự án)'
                        );
                    }
                    
                    Log::info('Đã tạo dispatch item với serial', [
                        'item_id' => $item->id,
                        'pass_quantity' => $passQuantity,
                        'serial_numbers' => $serialNumbers,
                        'serial_results' => $item->serial_results,
                        'serial_number_original' => $item->serial_number,
                        'item_type' => $item->item_type,
                        'product_id' => $item->product_id,
                        'good_id' => $item->good_id
                    ]);
                }
            }

            Log::info('Đã tạo phiếu xuất kho thành phẩm cho dự án', [
                'testing_id' => $testing->id,
                'dispatch_id' => $dispatch->id,
                'dispatch_code' => $dispatch->dispatch_code,
                'project_name' => $projectName,
                'status' => 'approved'
            ]);

            return $dispatch;

        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo phiếu xuất kho thành phẩm cho dự án: ' . $e->getMessage(), [
                'testing_id' => $testing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Tính toán tự động no_serial_pass_quantity và no_serial_fail_quantity từ serial_results
     */
    private function calculateNoSerialQuantities($item, $serialResults)
    {
        // Lấy thông tin về serial thực tế của item
        $actualSerials = [];
        if (!empty($item->serial_number)) {
            $actualSerials = array_values(array_filter(array_map('trim', explode(',', $item->serial_number))));
        }
        
        $totalQuantity = (int)($item->quantity ?? 0);
        $serialCount = count($actualSerials);
        
        // Đếm số lượng N/A đạt và không đạt từ serial_results
        $noSerialPass = 0;
        $noSerialFail = 0;
        $noSerialPending = 0;
        
        Log::info('DEBUG: Bắt đầu tính toán no_serial quantities', [
            'item_id' => $item->id,
            'material_id' => $item->material_id,
            'total_quantity' => $totalQuantity,
            'actual_serials' => $actualSerials,
            'serial_count' => $serialCount,
            'serial_results' => $serialResults
        ]);
        
        // Duyệt qua TẤT CẢ các kết quả trong serial_results
        foreach ($serialResults as $label => $result) {
            $index = ord($label) - ord('A'); // A=0, B=1, C=2, ...
            
            // Kiểm tra xem vị trí này có serial thực tế không
            $hasActualSerial = isset($actualSerials[$index]) && !empty($actualSerials[$index]);
            
            Log::info('DEBUG: Kiểm tra vị trí', [
                'label' => $label,
                'index' => $index,
                'result' => $result,
                'has_actual_serial' => $hasActualSerial,
                'actual_serial_at_index' => $actualSerials[$index] ?? 'null'
            ]);
            
            // Chỉ tính những vị trí KHÔNG có serial thực tế (N/A)
            if (!$hasActualSerial) {
                if ($result === 'pass') {
                    $noSerialPass++;
                } elseif ($result === 'fail') {
                    $noSerialFail++;
                } elseif ($result === 'pending') {
                    $noSerialPending++;
                }
            }
        }
        
        // Cập nhật vào database
        $item->update([
            'no_serial_pass_quantity' => $noSerialPass,
            'no_serial_fail_quantity' => $noSerialFail
        ]);
        
        Log::info('DEBUG: Hoàn thành tính toán no_serial quantities', [
            'item_id' => $item->id,
            'item_type' => $item->item_type,
            'material_id' => $item->material_id,
            'total_quantity' => $totalQuantity,
            'actual_serials' => $actualSerials,
            'serial_count' => $serialCount,
            'no_serial_pass' => $noSerialPass,
            'no_serial_fail' => $noSerialFail,
            'no_serial_pending' => $noSerialPending,
            'serial_results' => $serialResults,
            'updated_in_db' => true
        ]);
    }

    /**
     * ✨ TỐI ƯU: Áp dụng giá trị mặc định "pass" cho các testing items không có trong serial_results
     * 
     * Logic tối ưu hóa:
     * - Frontend chỉ gửi serial_results có giá trị "fail" (giảm 90-95% payload)
     * - Backend cần set "pass" cho các items không được gửi lên
     * - Điều này giải quyết vấn đề timeout khi có 500-2000 vật tư
     * 
     * @param Testing $testing
     * @param array $receivedSerialResults - Các serial_results đã nhận từ request (chỉ chứa fail items)
     */
    private function applyDefaultPassForMissingSerials(Testing $testing, array $receivedSerialResults)
    {
        try {
            // Lấy tất cả testing items của phiếu này
            $allTestingItems = TestingItem::where('testing_id', $testing->id)
                ->get();
            
            $totalItems = $allTestingItems->count();
            $receivedItemsCount = count($receivedSerialResults);
            $defaultedItemsCount = 0;
            
            Log::info('🚀 Bắt đầu áp dụng default pass cho missing serials', [
                'testing_id' => $testing->id,
                'total_items' => $totalItems,
                'received_items' => $receivedItemsCount,
                'optimization_rate' => $totalItems > 0 ? round((1 - $receivedItemsCount / $totalItems) * 100, 1) . '%' : '0%'
            ]);
            
            foreach ($allTestingItems as $item) {
                // Kiểm tra xem item này có trong received serial_results không
                $itemId = $item->id;
                
                // Nếu item này KHÔNG CÓ trong received serial_results
                // → Nghĩa là frontend đã bỏ qua nó (vì tất cả đều pass/pending)
                // → Cần set mặc định là "pass"
                if (!isset($receivedSerialResults[$itemId])) {
                    // Lấy serial_results hiện tại từ database
                    $currentSerialResults = [];
                    if ($item->serial_results) {
                        $currentSerialResults = is_array($item->serial_results) 
                            ? $item->serial_results 
                            : json_decode($item->serial_results, true);
                    }
                    
                    // Xác định số lượng cần set default
                    $quantity = (int)($item->quantity ?? 0);
                    
                    if ($quantity > 0) {
                        // Tạo serial_results với tất cả giá trị "pass"
                        $defaultSerialResults = [];
                        
                        // Kiểm tra xem có phải auto-pass không
                        $shouldAutoPassPending = ($item->item_type === 'material') 
                            || ($item->item_type === 'product' && $testing->test_type === 'material');
                        
                        for ($i = 0; $i < $quantity; $i++) {
                            $label = $this->labelFromIndex($i);
                            
                            // Nếu đã có giá trị trong database, giữ nguyên
                            // Nếu chưa có, set mặc định là "pass" (nếu được phép auto-pass)
                            if (isset($currentSerialResults[$label])) {
                                $defaultSerialResults[$label] = $currentSerialResults[$label];
                            } else {
                                $defaultSerialResults[$label] = $shouldAutoPassPending ? 'pass' : 'pending';
                            }
                        }
                        
                        // Chỉ update nếu có thay đổi
                        if ($defaultSerialResults !== $currentSerialResults) {
                            $item->update(['serial_results' => json_encode($defaultSerialResults)]);
                            
                            // Tính toán lại no_serial quantities
                            $this->calculateNoSerialQuantities($item, $defaultSerialResults);
                            
                            $defaultedItemsCount++;
                            
                            Log::debug('Set default pass cho item', [
                                'item_id' => $item->id,
                                'material_id' => $item->material_id,
                                'product_id' => $item->product_id,
                                'quantity' => $quantity,
                                'default_value' => $shouldAutoPassPending ? 'pass' : 'pending'
                            ]);
                        }
                    }
                }
            }
            
            // Log kết quả tối ưu
            if ($totalItems > 0) {
                $optimizationRate = round((1 - $receivedItemsCount / $totalItems) * 100, 1);
                Log::info('✅ Hoàn thành áp dụng default pass', [
                    'testing_id' => $testing->id,
                    'total_items' => $totalItems,
                    'received_items' => $receivedItemsCount,
                    'defaulted_items' => $defaultedItemsCount,
                    'optimization_rate' => $optimizationRate . '%',
                    'performance_gain' => 'Giảm ' . $optimizationRate . '% payload và database queries'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Lỗi khi áp dụng default pass cho missing serials', [
                'testing_id' => $testing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Không throw exception để không làm gián đoạn flow chính
            // Chỉ log lỗi để debug
        }
    }


    /**
     * Tự động tính toán kết quả thành phẩm dựa trên vật tư lắp ráp
     * Logic: Nếu TẤT CẢ vật tư lắp ráp đều đạt → Thành phẩm đạt
     *        Nếu có ít nhất 1 vật tư lắp ráp không đạt → Thành phẩm không đạt
     *        Cập nhật cả pass_quantity/fail_quantity và serial_results để đồng bộ
     */
    private function calculateProductResults(Testing $testing, $specificProductId = null)
    {
        // Lấy thành phẩm cần tính toán (nếu có specificProductId thì chỉ tính cho thành phẩm đó)
        if ($specificProductId) {
            $productItems = $testing->items()->where('item_type', 'product')->where('id', $specificProductId)->get();
        } else {
            $productItems = $testing->items()->where('item_type', 'product')->get();
        }
        
        foreach ($productItems as $productItem) {
            $productQuantity = (int)($productItem->quantity ?? 0);
            $targetProductId = $productItem->product_id ?? $productItem->good_id;
            
            if (!$targetProductId || !$testing->assembly || !$testing->assembly->materials) {
                continue;
            }
            
            // Lấy tất cả materials của thành phẩm này
            $allMaterials = $testing->assembly->materials
                ->where('target_product_id', $targetProductId)
                ->sortBy('id')
                ->values();
            
            // Phân chia materials theo unit (ưu tiên product_unit, nếu thiếu thì round-robin theo productQuantity)
            $unitResults = [];
            $totalPass = 0;
            $totalFail = 0;
            $totalUnits = max(1, (int)$productQuantity);

            // Xây map unit -> danh sách AssemblyMaterial
            $hasExplicitUnit = $allMaterials->contains(function($am){ return $am->product_unit !== null; });
            $unitToAssemblyMaterials = array_fill(0, $totalUnits, collect());
            if ($hasExplicitUnit) {
                foreach ($allMaterials as $am) {
                    $u = (int)($am->product_unit ?? 0);
                    if ($u < 0) { $u = 0; }
                    if ($u >= $totalUnits) { $u = $totalUnits - 1; }
                    $unitToAssemblyMaterials[$u]->push($am);
                }
            } else {
                $cursor = 0;
                foreach ($allMaterials as $am) {
                    $u = $cursor % $totalUnits; // round-robin
                    $unitToAssemblyMaterials[$u]->push($am);
                    $cursor++;
                }
            }

            // Với từng unit, gom TestingItem tương ứng rồi quyết định pass/fail
            for ($unitIndex = 0; $unitIndex < $totalUnits; $unitIndex++) {
                $assemblyList = $unitToAssemblyMaterials[$unitIndex];
                if ($assemblyList->isEmpty()) { $unitResults[$unitIndex] = 'pass'; $totalPass++; continue; }

                $unitHasFail = false;
                foreach ($assemblyList as $assemblyMaterial) {
                    $materialId = $assemblyMaterial->material_id;
                    // Lấy testing items cho material này, theo thứ tự tạo
                    $testingItems = $testing->items()
                        ->where('item_type', 'material')
                        ->where('material_id', $materialId)
                        ->orderBy('id')
                        ->get()
                        ->values();

                    if ($testingItems->isEmpty()) { continue; }

                    // Chọn item tương ứng unit (nếu thiếu thì lấy cuối cùng)
                    $ti = $testingItems->get($unitIndex, $testingItems->last());

                    if (!empty($ti->serial_results)) {
                        $materialSerialResults = json_decode($ti->serial_results, true);
                        if (is_array($materialSerialResults)) {
                            foreach ($materialSerialResults as $res) {
                                if ($res === 'fail') { $unitHasFail = true; break; }
                            }
                        }
                    }
                    if (!$unitHasFail) {
                        $noSerialFail = (int)($ti->no_serial_fail_quantity ?? 0);
                        if ($noSerialFail > 0) { $unitHasFail = true; }
                    }
                    if ($unitHasFail) { break; }
                }

                $unitResults[$unitIndex] = $unitHasFail ? 'fail' : 'pass';
                if ($unitHasFail) { $totalFail++; } else { $totalPass++; }
            }
            
            // Tạo serial_results mới dựa trên kết quả từng unit
            $newSerialResults = [];
            if ($productQuantity > 0) {
                for ($i = 0; $i < $productQuantity; $i++) {
                    $label = $this->labelFromIndex($i); // A, B, C, ...
                    // Nếu không có kết quả unit (ví dụ: thiếu nhóm vật tư), coi như pass
                    $unitResult = $unitResults[$i] ?? 'pass';
                    $newSerialResults[$label] = $unitResult;
                }
            }
            
            // Cập nhật kết quả thành phẩm
            $productItem->update([
                'pass_quantity' => $totalPass,
                'fail_quantity' => $totalFail,
                'serial_results' => json_encode($newSerialResults),
                'result' => ($totalFail > 0) ? 'fail' : 'pass'
            ]);
            
            Log::info('Auto-calculated product result by units', [
                'testing_id' => $testing->id,
                'product_item_id' => $productItem->id,
                'product_quantity' => $productQuantity,
                'total_pass' => $totalPass,
                'total_fail' => $totalFail,
                'unit_results' => $unitResults,
                'new_serial_results' => $newSerialResults,
                'total_units' => $totalUnits
            ]);
        }
    }

    /**
     * Tạo serial records cho các thành phẩm đạt (pass) sau khi hoàn thành kiểm thử
     */
    private function createSerialRecordsForPassedProducts(Testing $testing)
    {
        try {
            Log::info('Bắt đầu tạo serial records cho thành phẩm đạt', [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code
            ]);

            foreach ($testing->items as $item) {
                // Chỉ xử lý thành phẩm có kết quả pass và có serial_number
                if ($item->item_type !== 'product' || $item->result !== 'pass' || empty($item->serial_number)) {
                    continue;
                }

                // Lấy danh sách serial numbers từ item
                $serialArray = explode(',', $item->serial_number);
                $serialArray = array_map('trim', $serialArray);
                $serialArray = array_filter($serialArray);

                if (empty($serialArray)) {
                    continue;
                }

                // Xác định warehouse_id từ assembly hoặc testing
                $warehouseId = null;
                if ($testing->assembly_id) {
                    $assembly = \App\Models\Assembly::find($testing->assembly_id);
                    if ($assembly) {
                        $warehouseId = $assembly->target_warehouse_id ?: $assembly->warehouse_id;
                    }
                }

                if (!$warehouseId) {
                    Log::warning('Không tìm thấy warehouse_id cho testing item', [
                        'testing_id' => $testing->id,
                        'item_id' => $item->id,
                        'assembly_id' => $testing->assembly_id
                    ]);
                    continue;
                }

                // Tạo serial records cho các serial đạt
                $createdCount = 0;
                foreach ($serialArray as $index => $serial) {
                    if (empty($serial)) continue;

                    // Kiểm tra kết quả của serial cụ thể từ serial_results
                    $serialResult = 'pass'; // Default
                    if (!empty($item->serial_results)) {
                        $serialResults = json_decode($item->serial_results, true);
                        if (is_array($serialResults)) {
                            // Convert index to letter (A=0, B=1, ..., Z=25, [=26, etc.)
                            $resultKey = $this->labelFromIndex($index); // A, B, ..., AA, AB
                            if (isset($serialResults[$resultKey])) {
                                $serialResult = $serialResults[$resultKey];
                            }
                        }
                    }

                    // Chỉ tạo serial nếu kết quả là 'pass'
                    if ($serialResult !== 'pass') {
                        Log::info('Bỏ qua serial không đạt', [
                            'testing_id' => $testing->id,
                            'serial' => $serial,
                            'result' => $serialResult,
                            'index' => $index
                        ]);
                        continue;
                    }

                    // Kiểm tra xem serial đã tồn tại chưa
                    $existingSerial = \App\Models\Serial::where('serial_number', $serial)
                        ->where('product_id', $item->product_id)
                        ->where('type', 'product')
                        ->first();

                    if (!$existingSerial) {
                        \App\Models\Serial::create([
                            'serial_number' => $serial,
                            'product_id' => $item->product_id,
                            'status' => 'active',
                            'notes' => 'Testing ID: ' . $testing->id,
                            'type' => 'product',
                            'warehouse_id' => $warehouseId
                        ]);
                        $createdCount++;
                    } else if ($existingSerial->status !== 'active') {
                        // Nếu trước đó serial bị inactive (do fail), khi pass lại thì kích hoạt lại
                        $existingSerial->update([
                            'status' => 'active',
                            'notes' => 'Testing ID: ' . $testing->id . ' (Re-activated after pass)'
                        ]);
                    }
                }

                Log::info('Đã tạo serial records cho thành phẩm đạt', [
                    'testing_id' => $testing->id,
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'serial_numbers' => $serialArray,
                    'created_count' => $createdCount,
                    'warehouse_id' => $warehouseId
                ]);
            }

            Log::info('Hoàn thành tạo serial records cho thành phẩm đạt', [
                'testing_id' => $testing->id
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo serial records cho thành phẩm đạt', [
                'testing_id' => $testing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
