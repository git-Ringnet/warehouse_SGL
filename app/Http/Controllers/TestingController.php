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
        $query = Testing::with(['tester', 'items']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('test_code', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($q2) use ($search) {
                        $q2->where('serial_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('test_type')) {
            $query->where('test_type', $request->test_type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
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
        $warehouses = Warehouse::where('status', 'active')->get(); // Thêm dòng này

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
            'warehouses', // Thêm dòng này
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
            'items.*.serial_numbers' => 'nullable|array',
            'test_items' => 'nullable|array',
            'test_items.*' => 'nullable|string',
        ]);

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
                // Check inventory
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

                // Add serial numbers if provided
                if (!empty($item['serial_numbers'])) {
                    $itemData['serial_numbers'] = implode(',', $item['serial_numbers']);
                }

                TestingItem::create($itemData);
            }

            // Add testing details if provided
            if ($request->has('test_items')) {
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

            return redirect()->route('testing.show', $testing->id)
                ->with('success', 'Phiếu kiểm thử đã được tạo thành công.');
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
        $testing->load(['tester', 'items.material', 'items.product', 'items.good', 'items.supplier', 'details']);

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
        $validator = Validator::make($request->all(), [
            'tester_id' => 'required|exists:employees,id',
            'assigned_to' => 'required|exists:employees,id',
            'receiver_id' => 'required|exists:employees,id',
            'test_date' => 'required|date',
            'notes' => 'nullable|string',
            'pass_quantity' => 'nullable|integer|min:0',
            'fail_quantity' => 'nullable|integer|min:0',
            'fail_reasons' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'item_results' => 'nullable|array',
            'item_results.*' => 'nullable|in:pass,fail,pending',
            'item_notes' => 'nullable|array',
            'item_notes.*' => 'nullable|string',
            'test_results' => 'nullable|array',
            'test_results.*' => 'nullable|in:pass,fail,pending',
            'test_notes' => 'nullable|array',
            'test_notes.*' => 'nullable|string',
        ]);

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $testing->toArray();

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Update testing record
            $testing->update([
                'tester_id' => $request->tester_id,
                'assigned_to' => $request->assigned_to,
                'receiver_id' => $request->receiver_id,
                'test_date' => $request->test_date,
                'notes' => $request->notes,
                'pass_quantity' => $request->pass_quantity ?? 0,
                'fail_quantity' => $request->fail_quantity ?? 0,
                'fail_reasons' => $request->fail_reasons,
                'conclusion' => $request->conclusion,
            ]);

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

                foreach ($request->item_results as $itemId => $result) {
                    Log::info('Xử lý kết quả kiểm thử cho item', [
                        'item_id' => $itemId,
                        'result' => $result
                    ]);

                    // Tìm testing item dựa vào material_id hoặc id
                    $item = TestingItem::where(function ($query) use ($itemId, $testing) {
                        $query->where('testing_id', $testing->id)
                            ->where(function ($q) use ($itemId) {
                                $q->where('id', $itemId)
                                    ->orWhere('material_id', $itemId);
                            });
                    })->first();

                    if ($item) {
                        $item->update([
                            'result' => $result,
                            'updated_at' => now()
                        ]);

                        // Log để debug
                        Log::info('Đã cập nhật kết quả kiểm thử', [
                            'testing_id' => $testing->id,
                            'item_id' => $item->id,
                            'material_id' => $item->material_id,
                            'old_result' => $item->getOriginal('result'),
                            'new_result' => $result
                        ]);
                    } else {
                        Log::info('Không tìm thấy item hiện có, tìm kiếm vật tư để tạo mới', [
                            'item_id' => $itemId,
                            'testing_id' => $testing->id
                        ]);

                        // Kiểm tra xem itemId có phải là material_id hay không
                        $material = Material::find($itemId);

                        if ($material) {
                            // Tạo mới testing item
                            $newItem = TestingItem::create([
                                'testing_id' => $testing->id,
                                'material_id' => $itemId,
                                'item_type' => 'material',
                                'result' => $result,
                                'quantity' => 1
                            ]);

                            // Log tạo mới
                            Log::info('Đã tạo mới testing item', [
                                'testing_id' => $testing->id,
                                'item_id' => $newItem->id,
                                'material_id' => $itemId,
                                'result' => $result
                            ]);
                        } else {
                            Log::warning('Không tìm thấy vật tư với ID này', [
                                'item_id' => $itemId
                            ]);
                        }
                    }
                }
            } else {
                Log::warning('Không có dữ liệu item_results trong request', [
                    'request_keys' => array_keys($request->all())
                ]);
            }

            // Update item notes if we have item_notes in the request
            if ($request->has('item_notes')) {
                foreach ($request->item_notes as $itemId => $note) {
                    $item = TestingItem::where(function ($query) use ($itemId, $testing) {
                        $query->where('testing_id', $testing->id)
                            ->where(function ($q) use ($itemId) {
                                $q->where('id', $itemId)
                                    ->orWhere('material_id', $itemId);
                            });
                    })->first();

                    if ($item) {
                        $item->update(['notes' => $note]);
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

            return redirect()->route('testing.show', $testing->id)
                ->with('success', 'Phiếu kiểm thử đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi cập nhật phiếu kiểm thử: ' . $e->getMessage(), [
                'testing_id' => $testing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testing $testing)
    {
        if ($testing->status == 'completed') {
            return redirect()->back()
                ->with('error', 'Không thể xóa phiếu kiểm thử đã hoàn thành.');
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
        if ($testing->status != 'in_progress') {
            return redirect()->back()
                ->with('error', 'Chỉ có thể tiếp nhận phiếu kiểm thử đang ở trạng thái đang thực hiện.');
        }

        // Get employee ID from authenticated user if available
        $employeeId = null;
        if (Auth::check() && Auth::user()->employee) {
            $employeeId = Auth::user()->employee->id;
        }

        $testing->update([
            'received_by' => $employeeId,
            'received_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Phiếu kiểm thử đã được tiếp nhận thành công.');
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
                ? $testing->items->where('item_type', 'good')
                : $testing->items;

            $pendingItems = $itemsToCheck->where('result', 'pending')->count();

            if ($pendingItems > 0) {
                $itemLabel = $testing->test_type == 'finished_product' ? 'thành phẩm' : 'thiết bị';
                $errorMessage = "Không thể hoàn thành phiếu kiểm thử: Còn {$pendingItems} {$itemLabel} chưa có kết quả đánh giá. Vui lòng cập nhật đầy đủ kết quả trước khi hoàn thành.";

                DB::rollBack();
                return redirect()->back()
                    ->with('error', $errorMessage);
            }

            // Tính toán số lượng đạt/không đạt dựa trên items đã lọc
            $passCount = $itemsToCheck->where('result', 'pass')->count();
            $failCount = $itemsToCheck->where('result', 'fail')->count();
            $totalCount = $passCount + $failCount;

            // Nếu không có thiết bị nào có kết quả, sử dụng giá trị mặc định
            if ($totalCount == 0) {
                $passCount = 1;
                $failCount = 0;
                $totalCount = 1;
            }

            // Tính tỉ lệ đạt
            $passRate = ($totalCount > 0) ? round(($passCount / $totalCount) * 100) : 100;

            // Tạo danh sách các thiết bị không đạt
            $failItems = $itemsToCheck->where('result', 'fail')->map(function ($item) {
                $itemName = '';
                if ($item->item_type == 'material' && $item->material) {
                    $itemName = $item->material->name;
                } elseif ($item->item_type == 'product' && $item->product) {
                    $itemName = $item->product->name;
                } elseif ($item->item_type == 'finished_product' && $item->good) {
                    $itemName = $item->good->name;
                }
                return $itemName . ': ' . ($item->notes ?: 'Không đạt yêu cầu');
            })->join("\n");

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
            if (!empty($failItems)) {
                $conclusion .= " Các thiết bị cần khắc phục: {$failItems}.";
            }

            // Log thông tin hoàn thành phiếu
            Log::info('Hoàn thành phiếu kiểm thử tự động', [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code,
                'pass_count' => $passCount,
                'fail_count' => $failCount,
                'pass_rate' => $passRate,
                'conclusion' => $conclusion
            ]);

            // Cập nhật trạng thái phiếu
            $testing->update([
                'status' => 'completed',
                'pass_quantity' => $passCount,
                'fail_quantity' => $failCount,
                'fail_reasons' => $failItems,
                'conclusion' => $conclusion,
            ]);

            // Đồng bộ trạng thái với Assembly nếu có
            if ($testing->assembly_id) {
                $assembly = Assembly::find($testing->assembly_id);
                if ($assembly) {
                    $assembly->update([
                        'status' => 'completed'
                    ]);

                    Log::info('Đồng bộ trạng thái Assembly sau khi hoàn thành Testing', [
                        'testing_id' => $testing->id,
                        'assembly_id' => $assembly->id,
                        'new_status' => 'completed'
                    ]);
                }
            }

            // Tạo thông báo khi hoàn thành phiếu kiểm thử
            $notificationType = $passRate == 100 ? 'success' : ($passRate >= 80 ? 'info' : 'warning');
            $notificationTitle = 'Phiếu kiểm thử hoàn thành';
            $notificationMessage = "Phiếu kiểm thử #{$testing->test_code} đã hoàn thành với tỉ lệ đạt {$passRate}% ({$passCount}/{$totalCount}).";

            // Thông báo cho người phụ trách kiểm thử
            if ($testing->assigned_to) {
                Notification::createNotification(
                    $notificationTitle,
                    $notificationMessage,
                    $notificationType,
                    $testing->assigned_to,
                    'testing',
                    $testing->id,
                    route('testing.show', $testing->id)
                );
            }

            // Thông báo cho người tiếp nhận kiểm thử
            if ($testing->receiver_id && $testing->receiver_id != $testing->assigned_to) {
                Notification::createNotification(
                    $notificationTitle,
                    $notificationMessage,
                    $notificationType,
                    $testing->receiver_id,
                    'testing',
                    $testing->id,
                    route('testing.show', $testing->id)
                );
            }

            // Thông báo cho người lắp ráp nếu có phiếu lắp ráp liên quan
            if ($testing->assembly_id && $assembly) {
                Notification::createNotification(
                    'Phiếu lắp ráp hoàn thành',
                    "Phiếu lắp ráp #{$assembly->code} đã hoàn thành kiểm thử với tỉ lệ đạt {$passRate}%.",
                    $notificationType,
                    $assembly->assigned_employee_id,
                    'assembly',
                    $assembly->id,
                    route('assemblies.show', $assembly->id)
                );
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Phiếu kiểm thử đã được hoàn thành thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi hoàn thành phiếu kiểm thử: ' . $e->getMessage(), [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code,
                'error' => $e->getMessage()
            ]);

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

        $validator = Validator::make($request->all(), [
            'success_warehouse_id' => 'required|exists:warehouses,id',
            'fail_warehouse_id' => 'required|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Load đầy đủ các quan hệ để đảm bảo dữ liệu được xử lý chính xác
        $testing->load([
            'items.material',
            'items.product',
            'items.good'
        ]);

        DB::beginTransaction();

        try {
            // Lấy tên kho thành công và kho thất bại để ghi log
            $successWarehouse = Warehouse::find($request->success_warehouse_id);
            $failWarehouse = Warehouse::find($request->fail_warehouse_id);

            // Log thông tin bắt đầu cập nhật kho
            Log::info('Bắt đầu cập nhật kho từ phiếu kiểm thử', [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code,
                'success_warehouse' => $successWarehouse ? $successWarehouse->name : 'Unknown',
                'fail_warehouse' => $failWarehouse ? $failWarehouse->name : 'Unknown',
                'items_count' => $testing->items->count()
            ]);

            // Thống kê số lượng item theo loại
            $itemTypeCount = [
                'material' => $testing->items->where('item_type', 'material')->count(),
                'product' => $testing->items->where('item_type', 'product')->count(),
                'finished_product' => $testing->items->where('item_type', 'finished_product')->count(),
            ];

            Log::info('Phân loại items trong phiếu kiểm thử', $itemTypeCount);

            // Update testing record
            $testing->update([
                'success_warehouse_id' => $request->success_warehouse_id,
                'fail_warehouse_id' => $request->fail_warehouse_id,
                'is_inventory_updated' => true,
            ]);

            // Kiểm tra nếu không có items
            if ($testing->items->isEmpty()) {
                Log::warning('Không có items nào để cập nhật vào kho', [
                    'testing_id' => $testing->id,
                    'test_code' => $testing->test_code
                ]);
            }

            // Kiểm tra nếu không có kết quả pass/fail
            $hasResults = $testing->items->whereIn('result', ['pass', 'fail'])->count() > 0;
            if (!$hasResults) {
                Log::warning('Không có items nào có kết quả pass/fail để cập nhật vào kho', [
                    'testing_id' => $testing->id,
                    'test_code' => $testing->test_code,
                    'items_status' => $testing->items->pluck('result')->toArray()
                ]);
            }

            // Process items based on their result
            foreach ($testing->items as $item) {
                Log::info('Xử lý item: ', [
                    'item_id' => $item->id,
                    'item_type' => $item->item_type,
                    'result' => $item->result
                ]);

                $itemInfo = [
                    'item_id' => null,
                    'item_name' => 'Unknown',
                    'item_code' => 'Unknown',
                    'item_type' => $item->item_type,
                    'quantity' => $item->quantity,
                    'result' => $item->result
                ];

                // Lấy thông tin cụ thể của item để log
                if ($item->item_type == 'material' && $item->material) {
                    $itemInfo['item_id'] = $item->material_id;
                    $itemInfo['item_name'] = $item->material->name;
                    $itemInfo['item_code'] = $item->material->code;
                } elseif ($item->item_type == 'product' && $item->product) {
                    $itemInfo['item_id'] = $item->product_id;
                    $itemInfo['item_name'] = $item->product->name;
                    $itemInfo['item_code'] = $item->product->code;
                } elseif ($item->item_type == 'finished_product' && $item->good) {
                    $itemInfo['item_id'] = $item->good_id;
                    $itemInfo['item_name'] = $item->good->name;
                    $itemInfo['item_code'] = $item->good->code;

                    // Log chi tiết cho hàng hóa (finished_product)
                    Log::info('Chi tiết hàng hóa (finished_product)', [
                        'good_id' => $item->good_id,
                        'good_name' => $item->good->name,
                        'good_code' => $item->good->code,
                        'quantity' => $item->quantity,
                        'result' => $item->result
                    ]);
                }

                // Ghi log thông tin item đang xử lý
                Log::info('Chi tiết item đang xử lý', $itemInfo);

                if ($item->result == 'pass') {
                    Log::info('Item đạt tiêu chuẩn, cập nhật vào kho thành công', [
                        'warehouse_id' => $request->success_warehouse_id,
                        'warehouse_name' => $successWarehouse ? $successWarehouse->name : 'Unknown'
                    ]);

                    // Update warehouse for passing items
                    switch ($item->item_type) {
                        case 'material':
                            $this->updateWarehouseMaterial($item->material_id, $request->success_warehouse_id, $item->quantity, 'material', $itemInfo);
                            break;
                        case 'product':
                            $this->updateWarehouseMaterial($item->product_id, $request->success_warehouse_id, $item->quantity, 'product', $itemInfo);
                            break;
                        case 'finished_product':
                            Log::info('Cập nhật hàng hóa vào kho thành công', [
                                'good_id' => $item->good_id,
                                'warehouse_id' => $request->success_warehouse_id
                            ]);
                            $this->updateWarehouseMaterial($item->good_id, $request->success_warehouse_id, $item->quantity, 'good', $itemInfo);
                            break;
                    }
                } else if ($item->result == 'fail') {
                    Log::info('Item không đạt tiêu chuẩn, cập nhật vào kho thất bại', [
                        'warehouse_id' => $request->fail_warehouse_id,
                        'warehouse_name' => $failWarehouse ? $failWarehouse->name : 'Unknown'
                    ]);

                    // Update warehouse for failing items
                    switch ($item->item_type) {
                        case 'material':
                            $this->updateWarehouseMaterial($item->material_id, $request->fail_warehouse_id, $item->quantity, 'material', $itemInfo);
                            break;
                        case 'product':
                            $this->updateWarehouseMaterial($item->product_id, $request->fail_warehouse_id, $item->quantity, 'product', $itemInfo);
                            break;
                        case 'finished_product':
                            Log::info('Cập nhật hàng hóa vào kho thất bại', [
                                'good_id' => $item->good_id,
                                'warehouse_id' => $request->fail_warehouse_id
                            ]);
                            $this->updateWarehouseMaterial($item->good_id, $request->fail_warehouse_id, $item->quantity, 'good', $itemInfo);
                            break;
                    }
                } else {
                    Log::warning('Item chưa có kết quả kiểm thử (pass/fail), bỏ qua cập nhật kho', [
                        'item_id' => $item->id,
                        'item_type' => $item->item_type,
                        'result' => $item->result
                    ]);
                }
            }

            // Log kết thúc cập nhật kho
            Log::info('Hoàn thành cập nhật kho từ phiếu kiểm thử', [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Kho đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Log lỗi khi cập nhật kho
            Log::error('Lỗi cập nhật kho từ phiếu kiểm thử: ' . $e->getMessage(), [
                'testing_id' => $testing->id,
                'test_code' => $testing->test_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
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
    public function getInventoryInfo($type, $id, $warehouseId)
    {
        try {
            $query = [
                'warehouse_id' => $warehouseId,
                'item_type' => $type
            ];

            // Xác định trường ID dựa vào loại
            if ($type === 'material') {
                $query['material_id'] = $id;
            } elseif ($type === 'product') {
                $query['material_id'] = $id;
                $query['item_type'] = 'good'; // Thay đổi từ 'product' thành 'good'
            }

            $inventory = WarehouseMaterial::where($query)->first();

            // Lấy danh sách serial numbers
            $serials = [];
            if ($inventory && $inventory->serial_numbers) {
                $serials = explode(',', $inventory->serial_numbers);
            }

            return response()->json([
                'quantity' => $inventory ? $inventory->quantity : 0,
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
                'quantity' => 0,
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
                        ->where('item_type', 'product')
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
}
