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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
            $query->where('test_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->where('test_date', '<=', $request->date_to);
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
        $materials = Material::where('is_hidden', false)->get();
        $products = Product::where('is_hidden', false)->get();
        $goods = Good::where('status', 'active')->get();
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

        // Kiểm tra không cho phép tạo phiếu kiểm thử Thiết bị thành phẩm trực tiếp
        if ($request->test_type === 'finished_product') {
            return redirect()->back()
                ->with('error', 'Không thể tạo phiếu kiểm thử Thiết bị thành phẩm trực tiếp. Phiếu này chỉ được tạo thông qua lắp ráp.')
                ->withInput();
        }

        if ($validator->fails()) {
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
                    // Lấy serial đầu tiên được chọn
                    $selectedSerials = array_filter($item['serials']); // Loại bỏ các giá trị rỗng
                    if (!empty($selectedSerials)) {
                        $itemData['serial_number'] = implode(', ', $selectedSerials);
                    }
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
            return redirect()->route('testing.index')->with('success', 'Tạo phiếu kiểm thử thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo phiếu kiểm thử: ' . $e->getMessage(), [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Testing $testing)
    {
        $testing->load([
            'tester',
            'assignedEmployee',
            'receiverEmployee',
            'approver',
            'receiver',
            'items.material',
            'items.good',
            'items.warehouse',
            'items.supplier',
            'details',
            'assembly.products.product',
            'assembly.product',
            'assembly.assignedEmployee',
            'assembly.materials.material',
            'successWarehouse',
            'failWarehouse'
        ]);

        // Ghi nhật ký xem chi tiết phiếu kiểm thử
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'testings',
                'Xem chi tiết phiếu kiểm thử: ' . $testing->test_code,
                null,
                $testing->toArray()
            );
        }

        return view('testing.show', compact('testing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Testing $testing)
    {
        $testing->load(['tester', 'items.material', 'items.product', 'items.good', 'items.warehouse', 'items.supplier', 'details', 'assembly.materials.material', 'assembly.materials.warehouse', 'assembly.products.product']);

        $employees = Employee::where('status', 'active')->get();
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
        // Debug: Log request data
        Log::info('Testing update request', [
            'request_data' => $request->all(),
            'has_tester_id' => $request->has('tester_id'),
            'has_assigned_to' => $request->has('assigned_to'),
            'has_receiver_id' => $request->has('receiver_id'),
            'has_test_date' => $request->has('test_date'),
            'tester_id_value' => $request->get('tester_id'),
            'assigned_to_value' => $request->get('assigned_to'),
        ]);
        
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
            'tester_id' => $isAutoSave ? 'nullable|exists:employees,id' : 'required|exists:employees,id',
            'assigned_to' => $isAutoSave ? 'nullable|exists:employees,id' : 'required|exists:employees,id',
            'receiver_id' => $isAutoSave ? 'nullable|exists:employees,id' : 'required|exists:employees,id',
            'test_date' => $isAutoSave ? 'nullable|date' : 'required|date',
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
            'item_pass_quantity_no_serial' => 'nullable|array',
            'item_pass_quantity_no_serial.*' => 'nullable|array',
            'item_pass_quantity_no_serial.*.*' => 'nullable|integer|min:0',
        ]);

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
                // Hợp nhất ghi chú: lưu dạng JSON { general_note: string, ... }
                $existingNotesArray = $this->normalizeNotesArray($testing->notes);
                if ($request->has('notes')) {
                    $existingNotesArray['general_note'] = $request->notes;
                }
                $mergedNotesJson = !empty($existingNotesArray) ? json_encode($existingNotesArray) : null;
                $testing->update([
                    'tester_id' => $request->tester_id ?? $testing->tester_id,
                    'assigned_to' => $request->assigned_to ?? $testing->assigned_to ?? $testing->tester_id,
                    'receiver_id' => $request->receiver_id ?? $testing->receiver_id,
                    'test_date' => $request->test_date ? $request->test_date : $testing->test_date,
                    'notes' => $mergedNotesJson,
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
                            $passQuantity = (int) $passQuantity;
                            $maxPass = (int) ($item->quantity ?? $passQuantity);
                            if ($passQuantity > $maxPass) {
                                $passQuantity = $maxPass;
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
            if ($request->has('item_pass_quantity')) {
                $providedFailForItemIds = array_keys($request->get('item_fail_quantity', []));
                foreach ($request->item_pass_quantity as $itemId => $quantity) {
                    $item = TestingItem::where('testing_id', $testing->id)
                        ->where('id', $itemId)
                        ->first();
                    
                    if ($item) {
                        $quantity = (int) $quantity;
                        $maxPass = (int) ($item->quantity ?? $quantity);
                        if ($quantity > $maxPass) {
                            $quantity = $maxPass;
                        }
                        $item->update(['pass_quantity' => $quantity]);
                        if (!in_array($itemId, $providedFailForItemIds, true)) {
                            $autoFail = max(0, (int)($item->quantity ?? 0) - $quantity);
                            $item->update(['fail_quantity' => $autoFail]);
                        }
                    }
                }
            }

            if ($request->has('item_fail_quantity')) {
                foreach ($request->item_fail_quantity as $itemId => $quantity) {
                    $item = TestingItem::where('testing_id', $testing->id)
                        ->where('id', $itemId)
                        ->first();
                    
                    if ($item) {
                        $item->update(['fail_quantity' => $quantity]);
                    }
                }
            }

            // Xử lý item_pass_quantity_no_serial (số lượng Đạt cho vật tư không có serial)
            if ($request->has('item_pass_quantity_no_serial')) {
                Log::info('Bắt đầu xử lý item_pass_quantity_no_serial (per product)', [
                    'item_pass_quantity_no_serial' => $request->item_pass_quantity_no_serial,
                    'testing_id' => $testing->id
                ]);

                $testing->loadMissing('assembly.materials', 'items');

                // Duyệt từng thành phẩm (testing item) -> từng unit và số lượng đạt N/A
                foreach ($request->item_pass_quantity_no_serial as $productItemId => $units) {
                    // Bỏ qua nếu không phải là item thành phẩm
                    $productItem = $testing->items->firstWhere('id', (int)$productItemId);
                    if (!$productItem) { continue; }

                    // Xác định product_id mục tiêu để lọc materials đúng thành phẩm
                    $targetProductId = $productItem->product_id ?? $productItem->good_id ?? null;

                    foreach ($units as $unitIdx => $unitPassQuantityRaw) {
                        $unitIdx = (int)$unitIdx;
                        $unitPassQuantity = max(0, (int) $unitPassQuantityRaw);

                        // Lưu lại số lượng vào notes theo cấu trúc mới, bảo toàn general_note
                        $currentNotesArray = $this->normalizeNotesArray($testing->notes);
                        $currentNotesArray['no_serial_pass_quantity'] = $currentNotesArray['no_serial_pass_quantity'] ?? [];
                        $currentNotesArray['no_serial_pass_quantity'][$productItemId] = $currentNotesArray['no_serial_pass_quantity'][$productItemId] ?? [];
                        $currentNotesArray['no_serial_pass_quantity'][$productItemId][$unitIdx] = $unitPassQuantity;
                        $testing->update(['notes' => json_encode($currentNotesArray)]);

                        if (!$testing->assembly || !$testing->assembly->materials) { continue; }

                        // Lọc đúng materials của đơn vị và thuộc về thành phẩm này (theo target_product_id)
                        $materialsInUnit = $testing->assembly->materials->filter(function ($asmMaterial) use ($unitIdx, $targetProductId) {
                            if ((int)($asmMaterial->product_unit ?? -1) !== $unitIdx) return false;
                            if ($targetProductId && ($asmMaterial->target_product_id ?? null) && $asmMaterial->target_product_id != $targetProductId) return false;
                            return true;
                        });

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
                                    'no_serial_count' => $noSerialCount,
                                    'assembly_material_id' => $asmMaterial->id,
                                    'product_unit' => $asmMaterial->product_unit
                                ];
                            }
                        }

                        $remainingPass = $unitPassQuantity;

                        foreach ($noSerialRows as $row) {
                            if ($remainingPass <= 0) break;
                            $materialId = $row['material_id'];
                            $noSerialCount = $row['no_serial_count'];

                            // Tìm đúng TestingItem vật tư này thuộc phiếu hiện tại
                            $candidateItems = TestingItem::where('testing_id', $testing->id)
                                ->where('item_type', 'material')
                                ->where('material_id', $materialId)
                                ->get();

                            $item = $candidateItems->first();
                            if ($candidateItems->count() > 1) {
                                // Ưu tiên item có serial khớp với assembly material
                                foreach ($candidateItems as $candidate) {
                                    if (!empty($candidate->serial_number) && !empty($testing->assembly)) {
                                        $candidateSerials = array_map('trim', explode(',', $candidate->serial_number));
                                        // Tìm assembly material theo material_id
                                        $asmSerials = [];
                                        foreach ($testing->assembly->materials as $asmMat) {
                                            if ($asmMat->material_id == $materialId && !empty($asmMat->serial)) {
                                                $asmSerials = array_map('trim', explode(',', $asmMat->serial));
                                                break;
                                            }
                                        }
                                        if (!empty(array_intersect($candidateSerials, $asmSerials))) {
                                            $item = $candidate; break;
                                        }
                                    }
                                }
                            }

                            if ($item) {
                                // Tính pass/fail từ serial_results (nếu có)
                                $serialPass = 0; $serialFail = 0;
                                if (!empty($item->serial_results)) {
                                    $serialResults = json_decode($item->serial_results, true);
                                    if (is_array($serialResults)) {
                                        foreach ($serialResults as $val) {
                                            if ($val === 'pass') $serialPass++;
                                            if ($val === 'fail') $serialFail++;
                                        }
                                    }
                                }

                                $allocatePass = min($noSerialCount, $remainingPass);
                                $newPass = $serialPass + $allocatePass;
                                $remainingNoSerial = $noSerialCount - $allocatePass;
                                $newFail = $serialFail + $remainingNoSerial;

                                $item->update([
                                    'pass_quantity' => $newPass,
                                    'fail_quantity' => $newFail,
                                ]);

                                $remainingPass -= $allocatePass;
                            }
                        }
                    }
                }
            }

            // Update serial results
            if ($request->has('serial_results')) {
                Log::info('DEBUG: Xử lý serial_results', [
                    'testing_id' => $testing->id,
                    'serial_results_data' => $request->serial_results
                ]);
                
                foreach ($request->serial_results as $itemId => $serialResults) {
                    Log::info('DEBUG: Xử lý serial_results cho item', [
                        'item_id' => $itemId,
                        'serial_results' => $serialResults
                    ]);
                    
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
                        
                        // Chuẩn hóa giá trị serial_results
                        // Lưu ý: chỉ tự động chuyển 'pending' => 'pass' cho Vật tư/Hàng hóa (phiếu loại material)
                        // Thành phẩm (phiếu finished_product) giữ nguyên 'pending'
                        $normalizedSerialResults = [];
                        $shouldAutoPassPending = ($item->item_type === 'material') || ($item->item_type === 'product' && $testing->test_type === 'material');
                        foreach ($serialResults as $label => $value) {
                            if ($shouldAutoPassPending) {
                                $normalizedSerialResults[$label] = ($value === 'pending' || $value === null || $value === '') ? 'pass' : $value;
                            } else {
                                $normalizedSerialResults[$label] = ($value === null || $value === '') ? 'pending' : $value;
                            }
                        }

                        // Lưu serial results trực tiếp vào database, KHÔNG đụng đến pass/fail quantities
                        $item->update(['serial_results' => json_encode($normalizedSerialResults)]);

                        Log::info('DEBUG: Đã cập nhật serial_results (không thay đổi pass/fail quantity)', [
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
                
                // Xử lý thành phẩm
                foreach ($productItems as $item) {
                    $passQuantity = $item->pass_quantity ?? 0;
                    $failQuantity = $item->fail_quantity ?? 0;
                    
                    $totalPassQuantity += $passQuantity;
                    $totalFailQuantity += $failQuantity;

                    // Xác định đúng item_id và item_type
                    $itemId = null;
                    $itemType = null;
                    
                    if ($item->product_id) {
                        $itemId = $item->product_id;
                        $itemType = 'product';
                    } elseif ($item->good_id) {
                        $itemId = $item->good_id;
                        $itemType = 'good';
                    } elseif ($item->material_id) {
                        $itemId = $item->material_id;
                        $itemType = 'material';
                    }

                    if ($itemId && $itemType) {
                        if ($passQuantity > 0) {
                            // Cập nhật thành phẩm đạt vào kho đạt
                            $this->updateWarehouseMaterial(
                                $itemId,
                                $request->success_warehouse_id,
                                $passQuantity,
                                $itemType,
                                ['item_name' => $item->item_name ?? 'Unknown']
                            );

                            // Tạo serial thành phẩm đạt (nếu có danh sách serial)
                            // Ưu tiên lấy theo serial_results (nếu có đánh giá theo serial), fallback AssemblyProduct.serials
                            $passSerials = [];
                            if (!empty($item->serial_results)) {
                                $serialResults = json_decode($item->serial_results, true);
                                foreach ($serialResults as $label => $result) {
                                    if ($result === 'pass' && isset($item->serial_number)) {
                                        // serial_number có thể lưu danh sách, tách theo dấu phẩy
                                        $serialsFromItem = array_map('trim', explode(',', $item->serial_number));
                                        // Ánh xạ nhãn A, B, C... theo thứ tự
                                        $index = ord(strtoupper($label)) - 65;
                                        if (isset($serialsFromItem[$index]) && $serialsFromItem[$index] !== '') {
                                            $passSerials[] = $serialsFromItem[$index];
                                        }
                                    }
                                }
                            }

                            // Nếu không có serial_results, dùng serials từ assembly products
                            if (empty($passSerials) && $item->testing && $item->testing->assembly && $item->product_id) {
                                $assembly = $item->testing->assembly->loadMissing('products');
                                $ap = $assembly->products->firstWhere('product_id', $item->product_id);
                                if ($ap && !empty($ap->serials)) {
                                    $passSerials = array_filter(array_map('trim', explode(',', $ap->serials)));
                                }
                            }

                            if (!empty($passSerials)) {
                                foreach ($passSerials as $sn) {
                                    if ($sn === '') continue;
                                    // Chỉ tạo nếu chưa tồn tại
                                    $exists = \App\Models\Serial::whereRaw('LOWER(serial_number) = ?', [strtolower($sn)])
                                        ->where('product_id', $itemId)
                                        ->where('type', 'product')
                                        ->exists();
                                    if (!$exists) {
                                        \App\Models\Serial::create([
                                            'serial_number' => $sn,
                                            'product_id' => $itemId,
                                            'type' => 'product',
                                            'status' => 'active',
                                            'warehouse_id' => is_numeric($request->success_warehouse_id) ? (int)$request->success_warehouse_id : null,
                                            'notes' => 'Testing ID: ' . $testing->id,
                                        ]);
                                    }
                                }
                            }
                        }

                        if ($failQuantity > 0) {
                            // Cập nhật thành phẩm không đạt vào kho không đạt
                            $this->updateWarehouseMaterial(
                                $itemId,
                                $request->fail_warehouse_id,
                                $failQuantity,
                                $itemType,
                                ['item_name' => $item->item_name ?? 'Unknown']
                            );

                            // Tạo serial thành phẩm không đạt để có thể tra cứu sau này
                            // Dùng serial_results để lấy các serial đánh dấu 'fail'; fallback AssemblyProduct.serials nếu không có
                            $failSerials = [];
                            if (!empty($item->serial_results)) {
                                $serialResults = json_decode($item->serial_results, true);
                                if ($serialResults && isset($item->serial_number)) {
                                    $serialsFromItem = array_map('trim', explode(',', $item->serial_number));
                                    foreach ($serialResults as $label => $result) {
                                        if ($result === 'fail') {
                                            $index = ord(strtoupper($label)) - 65;
                                            if (isset($serialsFromItem[$index]) && $serialsFromItem[$index] !== '') {
                                                $failSerials[] = $serialsFromItem[$index];
                                            }
                                        }
                                    }
                                }
                            }

                            if (empty($failSerials) && $item->testing && $item->testing->assembly && $item->product_id) {
                                $assembly = $item->testing->assembly->loadMissing('products');
                                $ap = $assembly->products->firstWhere('product_id', $item->product_id);
                                if ($ap && !empty($ap->serials)) {
                                    // Nếu không có phân loại pass/fail theo serial, lưu toàn bộ vào trạng thái failed
                                    $failSerials = array_filter(array_map('trim', explode(',', $ap->serials)));
                                }
                            }

                            if (!empty($failSerials)) {
                                foreach ($failSerials as $sn) {
                                    if ($sn === '') continue;
                                    $exists = \App\Models\Serial::whereRaw('LOWER(serial_number) = ?', [strtolower($sn)])
                                        ->where('product_id', $itemId)
                                        ->where('type', 'product')
                                        ->exists();
                                    if (!$exists) {
                                        \App\Models\Serial::create([
                                            'serial_number' => $sn,
                                            'product_id' => $itemId,
                                            'type' => 'product',
                                            'status' => 'failed',
                                            'warehouse_id' => (int)$request->fail_warehouse_id,
                                            'notes' => 'Testing ID: ' . $testing->id . ' (failed)'
                                        ]);
                                    } else {
                                        // Nếu đã tồn tại, cập nhật trạng thái và kho về fail
                                        \App\Models\Serial::whereRaw('LOWER(serial_number) = ?', [strtolower($sn)])
                                            ->where('product_id', $itemId)
                                            ->where('type', 'product')
                                            ->update([
                                                'status' => 'failed',
                                                'warehouse_id' => (int)$request->fail_warehouse_id,
                                                'notes' => 'Testing ID: ' . $testing->id . ' (failed)'
                                            ]);
                                    }
                                }
                            }
                        }
                    }
                }
                
                // Xử lý vật tư lắp ráp (chỉ những vật tư không đạt)
                foreach ($materialItems as $item) {
                    $passQuantity = $item->pass_quantity ?? 0;
                    $failQuantity = $item->fail_quantity ?? 0;
                    
                    // Chỉ xử lý vật tư không đạt (vì vật tư đạt đã được xuất kho rồi)
                    if ($failQuantity > 0) {
                        $totalFailQuantity += $failQuantity;

                        // Xác định đúng item_id và item_type
                        $itemId = null;
                        $itemType = null;
                        
                        if ($item->material_id) {
                            $itemId = $item->material_id;
                            $itemType = 'material';
                        } elseif ($item->product_id) {
                            $itemId = $item->product_id;
                            $itemType = 'product';
                        } elseif ($item->good_id) {
                            $itemId = $item->good_id;
                            $itemType = 'good';
                        }

                        if ($itemId && $itemType) {
                            // Cập nhật vật tư không đạt vào kho vật tư không đạt
                            $this->updateWarehouseMaterial(
                                $itemId,
                                $request->fail_warehouse_id,
                                $failQuantity,
                                $itemType,
                                ['item_name' => $item->item_name ?? 'Unknown', 'note' => 'Vật tư lắp ráp không đạt']
                            );
                        }
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
            if ($testing->test_type == 'finished_product' && $testing->assembly && $testing->assembly->purpose == 'project') {
                // Chỉ tạo phiếu nhập kho cho vật tư không đạt (xuất đi dự án)
                $failImport = $this->createInventoryImport(
                    $testing,
                    $request->fail_warehouse_id,
                    'Vật tư lắp ráp không đạt từ phiếu kiểm thử: ' . $testing->test_code . ' (Xuất đi dự án)',
                    'fail'
                );
                if ($failImport) {
                    $createdImports[] = $failImport;
                }
            } else {
                // Trường hợp còn lại (lưu kho): tạo cả phiếu nhập kho cho thành phẩm đạt và vật tư không đạt
                $createdImports = $this->createInventoryImportsFromTesting($testing, $request->success_warehouse_id, $request->fail_warehouse_id);
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
                $projectName = $testing->assembly->project_name ?? 'Dự án';
                $successMessage = "Đã cập nhật vào kho và tự động duyệt phiếu nhập kho (Dự án cho Thành phẩm đạt: {$projectName}, Kho lưu Module Vật tư lắp ráp không đạt: {$failWarehouse->name}) {$totalPassQuantity} đạt / {$totalFailQuantity} không đạt";
            } elseif ($testing->test_type == 'material') {
                $transferInfo = count($createdTransfers) > 0 ? " và tạo " . count($createdTransfers) . " phiếu chuyển kho" : "";
                $successMessage = "Đã cập nhật vào kho, tự động duyệt phiếu nhập kho{$transferInfo} (Kho lưu Vật tư/Hàng hóa đạt: " . ($successWarehouse->name ?? 'Chưa có') . ", Kho lưu Vật tư/Hàng hóa không đạt: {$failWarehouse->name}) {$totalPassQuantity} đạt / {$totalFailQuantity} không đạt";
            } else {
                $successMessage = "Đã cập nhật vào kho và tự động duyệt phiếu nhập kho (Kho lưu Thành phẩm đạt: " . ($successWarehouse->name ?? 'Chưa có') . ", Kho lưu Module Vật tư lắp ráp không đạt: {$failWarehouse->name}) {$totalPassQuantity} đạt / {$totalFailQuantity} không đạt";
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
                    ->get();
            case 'product':
                return Good::where('status', 'active')
                    ->select('id', 'code', 'name')
                    ->get();
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
            // Lấy danh sách serial từ inventory_import_materials (từ phiếu nhập kho)
            switch ($type) {
                case 'material':
                    $query = DB::table('inventory_import_materials')
                        ->where('material_id', $itemId)
                        ->where('item_type', 'material')
                        ->where('warehouse_id', $warehouseId)
                        ->whereNotNull('serial_numbers')
                        ->where('serial_numbers', '!=', '')
                        ->where('serial_numbers', '!=', '[]');
                    
                    Log::info('Material query SQL', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
                    
                    $serials = $query->limit($quantity)
                        ->get(['serial_numbers'])
                        ->toArray();
                    break;
                case 'product':
                    // Thử query trực tiếp từ inventory_import_materials trước
                    $directQuery = DB::table('inventory_import_materials')
                        ->where('material_id', $itemId)
                        ->where('item_type', 'good')
                        ->whereNotNull('serial_numbers')
                        ->where('serial_numbers', '!=', '')
                        ->where('serial_numbers', '!=', '[]');
                    
                    Log::info('Direct query (without warehouse filter)', [
                        'sql' => $directQuery->toSql(), 
                        'bindings' => $directQuery->getBindings(),
                        'results' => $directQuery->get()->toArray()
                    ]);
                    
                    $query = DB::table('inventory_import_materials')
                        ->where('material_id', $itemId)
                        ->where('item_type', 'good') // Sửa từ 'product' thành 'good'
                        ->where('warehouse_id', $warehouseId)
                        ->whereNotNull('serial_numbers')
                        ->where('serial_numbers', '!=', '')
                        ->where('serial_numbers', '!=', '[]');
                    
                    Log::info('Product query SQL', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
                    
                    $serials = $query->limit($quantity)
                        ->get(['serial_numbers'])
                        ->toArray();
                    break;
            }

            Log::info('Raw serials from DB', ['serials' => $serials]);

            // Xử lý serial_numbers từ JSON array
            $processedSerials = [];
            foreach ($serials as $serial) {
                if (!empty($serial->serial_numbers)) {
                    $serialArray = json_decode($serial->serial_numbers, true);
                    Log::info('Decoded serial array', ['serial_numbers' => $serial->serial_numbers, 'decoded' => $serialArray]);
                    if (is_array($serialArray)) {
                        foreach ($serialArray as $serialNumber) {
                            if (!empty($serialNumber)) {
                                $processedSerials[] = [
                                    'serial_number' => $serialNumber,
                                    'quantity' => 1
                                ];
                            }
                        }
                    }
                }
            }

            Log::info('Processed serials', ['processed_serials' => $processedSerials]);

            // Nếu không có serial, thêm option "Không có Serial"
            if (empty($processedSerials)) {
                $processedSerials[] = [
                    'serial_number' => '',
                    'quantity' => 0
                ];
            }

            $serials = $processedSerials;
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

            // Tạo phiếu nhập kho cho vật tư không đạt
            $failImport = $this->createInventoryImport(
                $testing,
                $failWarehouseId,
                'Vật tư lắp ráp không đạt từ phiếu kiểm thử: ' . $testing->test_code,
                'fail'
            );
            if ($failImport) {
                $createdImports[] = $failImport;
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
     * Tạo một phiếu nhập kho
     */
    private function createInventoryImport($testing, $warehouseId, $notes, $type)
    {
        try {
            // Tạo mã phiếu nhập
            $importCode = $this->generateInventoryImportCode();
            
            // Tạo phiếu nhập kho
            $inventoryImport = \App\Models\InventoryImport::create([
                'supplier_id' => 1, // Supplier mặc định
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
            // Lấy vật tư không đạt từ testing items
            $items = $testing->items->where('item_type', 'material')->filter(function($item) {
                return ($item->fail_quantity ?? 0) > 0;
            });
            
            Log::info('DEBUG: Vật tư có fail_quantity > 0', [
                'count' => $items->count(),
                'items' => $items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'material_id' => $item->material_id,
                        'fail_quantity' => $item->fail_quantity
                    ];
                })->toArray()
            ]);
            
            // Nếu không có vật tư nào có fail_quantity > 0, thử đếm từ serial_results
            if ($items->count() == 0) {
                Log::info('DEBUG: Không có vật tư nào có fail_quantity > 0, thử đếm từ serial_results');
                
                foreach ($testing->items->where('item_type', 'material') as $item) {
                    if (!empty($item->serial_results)) {
                        $serialResults = json_decode($item->serial_results, true);
                        if (is_array($serialResults)) {
                            $failCount = 0;
                            foreach ($serialResults as $serial => $result) {
                                if ($result === 'fail') {
                                    $failCount++;
                                }
                            }
                            
                            Log::info('DEBUG: Đếm từ serial_results', [
                                'item_id' => $item->id,
                                'material_id' => $item->material_id,
                                'serial_results' => $serialResults,
                                'fail_count' => $failCount
                            ]);
                            
                            if ($failCount > 0) {
                                // Tạo item mới với fail_quantity được tính từ serial_results
                                $newItem = (object) [
                                    'item_type' => 'material',
                                    'material_id' => $item->material_id,
                                    'quantity' => $failCount,
                                    'serial_number' => $item->serial_number,
                                    'pass_quantity' => 0,
                                    'fail_quantity' => $failCount
                                ];
                                $items = $items->push($newItem);
                                
                                Log::info('DEBUG: Đã thêm item từ serial_results', [
                                    'new_item' => $newItem
                                ]);
                            }
                        }
                    }
                }
            }
        }

        Log::info('DEBUG: Tổng số items để tạo phiếu nhập kho', [
            'type' => $type,
            'count' => $items->count()
        ]);
        
        foreach ($items as $item) {
            // Xác định item_type và material_id
            $itemType = $item->item_type ?? 'material';
            $materialId = $item->material_id ?? $item->product_id ?? $item->good_id;
            
            // Xác định quantity dựa trên loại item
            $quantity = 0;
            if ($itemType == 'product') {
                // Thành phẩm: lấy pass_quantity
                $quantity = $item->pass_quantity ?? 0;
            } elseif ($itemType == 'material') {
                if ($type == 'success') {
                    // Vật tư trong assembly: lấy quantity từ assembly
                    $quantity = $item->quantity ?? 0;
                } else {
                    // Vật tư không đạt: lấy fail_quantity
                    $quantity = $item->fail_quantity ?? 0;
                }
            }
            
            Log::info('DEBUG: Xử lý item cho phiếu nhập kho', [
                'item_type' => $itemType,
                'material_id' => $materialId,
                'quantity' => $quantity,
                'pass_quantity' => $item->pass_quantity ?? 0,
                'fail_quantity' => $item->fail_quantity ?? 0
            ]);
            
            if ($quantity > 0 && $materialId) {
                // Xử lý serial numbers nếu có
                $serialNumbers = null;
                if (!empty($item->serial_number)) {
                    $serialArray = explode(',', $item->serial_number);
                    $serialArray = array_map('trim', $serialArray);
                    $serialArray = array_filter($serialArray);
                    
                    // Nếu là phiếu vật tư hư hỏng, chỉ lấy serial có kết quả "fail"
                    if ($type == 'fail' && !empty($item->serial_results)) {
                        $serialResults = json_decode($item->serial_results, true);
                        if (is_array($serialResults)) {
                            $failSerials = [];
                            foreach ($serialArray as $index => $serial) {
                                $serialLabel = chr(65 + $index); // A, B, C, D, E...
                                if (isset($serialResults[$serialLabel]) && $serialResults[$serialLabel] === 'fail') {
                                    $failSerials[] = $serial;
                                }
                            }
                            
                            Log::info('DEBUG: Xử lý serial cho phiếu vật tư hư hỏng', [
                                'item_id' => $item->id,
                                'material_id' => $materialId,
                                'all_serials' => $serialArray,
                                'serial_results' => $serialResults,
                                'fail_serials' => $failSerials,
                                'final_serial_numbers' => $failSerials
                            ]);
                            
                            if (count($failSerials) > 0) {
                                $serialNumbers = $failSerials;
                            }
                        }
                    } else {
                        // Phiếu thành phẩm đạt: lấy tất cả serial
                        if (count($serialArray) > 0) {
                            $serialNumbers = $serialArray;
                        }
                    }
                }

                \App\Models\InventoryImportMaterial::create([
                    'inventory_import_id' => $inventoryImport->id,
                    'material_id' => $materialId,
                    'warehouse_id' => $inventoryImport->warehouse_id,
                    'quantity' => $quantity,
                    'serial_numbers' => $serialNumbers,
                    'notes' => $type == 'success' ? 
                        ($itemType == 'product' ? 'Thành phẩm đạt từ kiểm thử' : 'Vật tư lắp ráp từ kiểm thử') : 
                        'Vật tư lắp ráp không đạt từ kiểm thử',
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
                }

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
                $createdTransfers[] = $successTransfer;
            }

            // Tạo phiếu chuyển kho cho vật tư/hàng hóa không đạt
            $failTransfer = $this->createWarehouseTransfer(
                $testing,
                $failWarehouseId,
                'Vật tư/Hàng hóa không đạt từ phiếu kiểm thử: ' . $testing->test_code,
                'fail'
            );
            if ($failTransfer) {
                $createdTransfers[] = $failTransfer;
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
                // Lấy vật tư/hàng hóa đạt
                $items = $testing->items->filter(function($item) {
                    return ($item->pass_quantity ?? 0) > 0;
                });
            } else {
                // Lấy vật tư/hàng hóa không đạt
                $items = $testing->items->filter(function($item) {
                    return ($item->fail_quantity ?? 0) > 0;
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

            // Kiểm tra kho nguồn và kho đích
            $sourceWarehouseId = $items->first()->warehouse_id;
            
            // Nếu kho nguồn và kho đích giống nhau thì không tạo phiếu chuyển kho
            if ($sourceWarehouseId == $destinationWarehouseId) {
                Log::info('Kho nguồn và kho đích giống nhau, không tạo phiếu chuyển kho', [
                    'warehouse_id' => $sourceWarehouseId,
                    'type' => $type
                ]);
                return null;
            }

            // Tạo phiếu chuyển kho
            $warehouseTransfer = \App\Models\WarehouseTransfer::create([
                'transfer_code' => $transferCode,
                'source_warehouse_id' => $sourceWarehouseId, // Kho nguồn (kho ban đầu)
                'destination_warehouse_id' => $destinationWarehouseId,
                'material_id' => $items->first()->material_id ?? $items->first()->product_id ?? $items->first()->good_id ?? 1, // Material ID mặc định
                'employee_id' => $testing->tester_id ?? 1, // Employee ID mặc định nếu không có
                'quantity' => $items->sum(function($item) use ($type) {
                    return $type == 'success' ? ($item->pass_quantity ?? 0) : ($item->fail_quantity ?? 0);
                }),
                'transfer_date' => now(),
                'status' => 'completed', // Tự động hoàn thành
                'notes' => $notes,
            ]);

            // Thêm materials vào phiếu chuyển kho
            foreach ($items as $item) {
                $quantity = $type == 'success' ? ($item->pass_quantity ?? 0) : ($item->fail_quantity ?? 0);
                
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
                        // Log trước khi tạo WarehouseTransferMaterial
                        Log::info('Tạo WarehouseTransferMaterial', [
                            'warehouse_transfer_id' => $warehouseTransfer->id,
                            'material_id' => $materialId,
                            'quantity' => $quantity,
                            'type' => $itemType,
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
                            'serial_numbers' => $item->serial_number ? json_encode(explode(',', $item->serial_number)) : null,
                            'notes' => $type == 'success' ? 'Vật tư/Hàng hóa đạt từ kiểm thử' : 'Vật tư/Hàng hóa không đạt từ kiểm thử',
                        ]);
                    }
                }
            }

            // Tự động hoàn thành phiếu chuyển kho
            $this->completeWarehouseTransferAutomatically($warehouseTransfer);

            return $warehouseTransfer;

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
            // Cập nhật số lượng tồn kho
            foreach ($warehouseTransfer->materials as $material) {
                // Giảm số lượng từ kho nguồn
                $sourceWarehouseMaterial = \App\Models\WarehouseMaterial::where([
                    'warehouse_id' => $warehouseTransfer->source_warehouse_id,
                    'material_id' => $material->material_id,
                    'item_type' => $material->type // Sử dụng 'type' thay vì 'item_type'
                ])->first();

                if ($sourceWarehouseMaterial) {
                    $oldQuantity = $sourceWarehouseMaterial->quantity;
                    $sourceWarehouseMaterial->quantity = max(0, $sourceWarehouseMaterial->quantity - $material->quantity);
                    $sourceWarehouseMaterial->save();
                    
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

                // Tăng số lượng vào kho đích
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
                    $serials = is_array($material->serial_numbers) ? $material->serial_numbers : json_decode($material->serial_numbers, true);
                    $currentSerials = [];
                    if (!empty($destinationWarehouseMaterial->serial_number)) {
                        $currentSerials = json_decode($destinationWarehouseMaterial->serial_number, true) ?: [];
                    }
                    // Gộp serial cũ và mới, loại bỏ trùng lặp
                    $mergedSerials = array_unique(array_merge($currentSerials, $serials));
                    $destinationWarehouseMaterial->serial_number = json_encode($mergedSerials);
                }
                $destinationWarehouseMaterial->save();

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
            $testing->loadMissing('assembly.materials');
            
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
        return $arr;
    }
}
