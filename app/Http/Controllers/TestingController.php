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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
    public function create()
    {
        $employees = Employee::all();
        $materials = Material::where('is_hidden', false)->get();
        $products = Product::where('is_hidden', false)->get();
        $goods = Good::where('status', 'active')->get();
        $suppliers = Supplier::all();
        
        return view('testing.create', compact('employees', 'materials', 'products', 'goods', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_type' => 'required|in:material,finished_product',
            'tester_id' => 'required|exists:employees,id',
            'test_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:material,product,finished_product',
            'items.*.id' => 'required',
            'items.*.serial_number' => 'nullable|string',
            'items.*.supplier_id' => 'nullable|exists:suppliers,id',
            'items.*.batch_number' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'test_items' => 'required|array|min:1',
            'test_items.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Generate test code
            $testCode = 'QA-' . Carbon::now()->format('ymd');
            $lastTest = Testing::where('test_code', 'like', $testCode . '%')
                ->orderBy('test_code', 'desc')
                ->first();
                
            if ($lastTest) {
                $lastNumber = (int) substr($lastTest->test_code, -3);
                $testCode .= str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $testCode .= '001';
            }

            // Create testing record
            $testing = Testing::create([
                'test_code' => $testCode,
                'test_type' => $request->test_type,
                'tester_id' => $request->tester_id,
                'test_date' => $request->test_date,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Add testing items
            foreach ($request->items as $item) {
                $itemData = [
                    'testing_id' => $testing->id,
                    'item_type' => $item['item_type'],
                    'serial_number' => $item['serial_number'] ?? null,
                    'supplier_id' => $item['supplier_id'] ?? null,
                    'batch_number' => $item['batch_number'] ?? null,
                    'quantity' => $item['quantity'],
                    'result' => 'pending',
                ];

                // Set the appropriate ID based on item type
                switch ($item['item_type']) {
                    case 'material':
                        $itemData['material_id'] = $item['id'];
                        break;
                    case 'product':
                        $itemData['product_id'] = $item['id'];
                        break;
                    case 'finished_product':
                        $itemData['good_id'] = $item['id'];
                        break;
                }

                TestingItem::create($itemData);
            }

            // Add testing details (test items)
            foreach ($request->test_items as $testItem) {
                TestingDetail::create([
                    'testing_id' => $testing->id,
                    'test_item_name' => $testItem,
                    'result' => 'pending',
                ]);
            }

            DB::commit();

            return redirect()->route('testing.show', $testing->id)
                ->with('success', 'Phiếu kiểm thử đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
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
            'approver', 
            'receiver', 
            'items.material', 
            'items.product.materials', 
            'items.good', 
            'items.supplier', 
            'details'
        ]);
        
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
            'test_date' => 'required|date',
            'notes' => 'nullable|string',
            'pass_quantity' => 'required_if:status,completed|integer|min:0',
            'fail_quantity' => 'required_if:status,completed|integer|min:0',
            'fail_reasons' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'test_item_names' => 'required|array|min:1',
            'test_item_names.*' => 'required|string',
            'test_results' => 'required|array|min:1',
            'test_results.*' => 'required|in:pass,fail,pending',
            'test_notes' => 'nullable|array',
            'test_notes.*' => 'nullable|string',
        ]);

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
                'test_date' => $request->test_date,
                'notes' => $request->notes,
                'pass_quantity' => $request->pass_quantity ?? 0,
                'fail_quantity' => $request->fail_quantity ?? 0,
                'fail_reasons' => $request->fail_reasons,
                'conclusion' => $request->conclusion,
            ]);

            // Update testing details
            // First, delete existing details
            $testing->details()->delete();
            
            // Then create new ones
            foreach ($request->test_item_names as $key => $testItemName) {
                TestingDetail::create([
                    'testing_id' => $testing->id,
                    'test_item_name' => $testItemName,
                    'result' => $request->test_results[$key] ?? 'pending',
                    'notes' => $request->test_notes[$key] ?? null,
                ]);
            }

            // Update items results if we have item_results in the request
            if ($request->has('item_results')) {
                foreach ($request->item_results as $itemId => $result) {
                    $item = TestingItem::find($itemId);
                    if ($item && $item->testing_id == $testing->id) {
                        $item->update(['result' => $result]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('testing.show', $testing->id)
                ->with('success', 'Phiếu kiểm thử đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
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

        DB::beginTransaction();

        try {
            // Delete related records
            $testing->details()->delete();
            $testing->items()->delete();
            
            // Delete the testing record
            $testing->delete();

            DB::commit();

            return redirect()->route('testing.index')
                ->with('success', 'Phiếu kiểm thử đã được xóa thành công.');
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

        $testing->update([
            'status' => 'in_progress',
            'approved_by' => $employeeId,
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Phiếu kiểm thử đã được duyệt thành công.');
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

        $testing->update([
            'status' => 'cancelled',
        ]);

        return redirect()->back()
            ->with('success', 'Phiếu kiểm thử đã bị từ chối.');
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

        $validator = Validator::make($request->all(), [
            'pass_quantity' => 'required|integer|min:0',
            'fail_quantity' => 'required|integer|min:0',
            'conclusion' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $testing->update([
            'status' => 'completed',
            'pass_quantity' => $request->pass_quantity,
            'fail_quantity' => $request->fail_quantity,
            'conclusion' => $request->conclusion,
        ]);

        return redirect()->back()
            ->with('success', 'Phiếu kiểm thử đã được hoàn thành thành công.');
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

        DB::beginTransaction();

        try {
            // Update testing record
            $testing->update([
                'success_warehouse_id' => $request->success_warehouse_id,
                'fail_warehouse_id' => $request->fail_warehouse_id,
                'is_inventory_updated' => true,
            ]);

            // Process items based on their result
            foreach ($testing->items as $item) {
                if ($item->item_type == 'material') {
                    // For materials, update warehouse quantities
                    if ($item->result == 'pass') {
                        $this->updateWarehouseMaterial($item->material_id, $request->success_warehouse_id, $item->quantity);
                    } else if ($item->result == 'fail') {
                        $this->updateWarehouseMaterial($item->material_id, $request->fail_warehouse_id, $item->quantity);
                    }
                }
                // Similar logic can be added for products and finished goods
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Kho đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Update warehouse material quantity.
     */
    private function updateWarehouseMaterial($materialId, $warehouseId, $quantity)
    {
        $warehouseMaterial = WarehouseMaterial::firstOrNew([
            'material_id' => $materialId,
            'warehouse_id' => $warehouseId,
        ]);

        if ($warehouseMaterial->exists) {
            $warehouseMaterial->quantity += $quantity;
        } else {
            $warehouseMaterial->quantity = $quantity;
        }

        $warehouseMaterial->save();
    }

    /**
     * Get materials by type and search term.
     */
    public function getMaterialsByType(Request $request)
    {
        $type = $request->type;
        $search = $request->search ?? '';
        
        switch ($type) {
            case 'material':
                $items = Material::where('is_hidden', false)
                    ->where('name', 'like', "%{$search}%")
                    ->get(['id', 'name', 'code']);
                break;
            case 'product':
                $items = Product::where('is_hidden', false)
                    ->where('name', 'like', "%{$search}%")
                    ->get(['id', 'name', 'code']);
                break;
            case 'finished_product':
                $items = Good::where('status', 'active')
                    ->where('name', 'like', "%{$search}%")
                    ->get(['id', 'name', 'code']);
                break;
            default:
                $items = collect();
        }
        
        return response()->json($items);
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
                $item = Good::find($id);
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
        $testing->load(['tester', 'approver', 'receiver', 'items.material', 'items.product', 'items.good', 'items.supplier', 'details']);
        
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
} 