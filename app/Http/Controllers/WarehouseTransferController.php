<?php

namespace App\Http\Controllers;

use App\Helpers\ChangeLogHelper;
use App\Models\Employee;
use App\Models\Material;
use App\Models\Product;
use App\Models\Good;
use App\Models\UserLog;
use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferMaterial;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WarehouseTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WarehouseTransfer::query()->with(['source_warehouse', 'destination_warehouse', 'material', 'employee']);

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $filter = $request->filter ?? '';

            switch ($filter) {
                case 'serial':
                    $query->where('serial', 'like', "%{$search}%");
                    break;
                case 'material':
                    $query->whereHas('material', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
                    break;
                case 'source':
                    $query->whereHas('source_warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                    break;
                case 'destination':
                    $query->whereHas('destination_warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                    break;
                case 'status':
                    $status = match (strtolower($search)) {
                        'chờ xác nhận', 'cho xac nhan' => 'pending',
                        'đang chuyển', 'dang chuyen' => 'in_progress',
                        'hoàn thành', 'hoan thanh' => 'completed',
                        'đã hủy', 'da huy' => 'canceled',
                        default => $search
                    };
                    $query->where('status', 'like', "%{$status}%");
                    break;
                default:
                    $query->where(function ($q) use ($search) {
                        $q->where('transfer_code', 'like', "%{$search}%")
                            ->orWhere('serial', 'like', "%{$search}%")
                            ->orWhereHas('material', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
                    });
            }
        }

        $warehouseTransfers = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('warehouse-transfers.index', compact('warehouseTransfers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $materials = Material::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();
        $products = Product::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();
        $goods = Good::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();

        return view('warehouse-transfers.create', compact(
            'warehouses',
            'employees',
            'materials',
            'products',
            'goods'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'transfer_code' => 'required|string|max:50|unique:warehouse_transfers',
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'transfer_date' => 'required|date',
            'employee_id' => 'required|exists:employees,id',
            'status' => 'required|in:pending,in_progress,completed,canceled',
            'materials_json' => 'required|json',
        ], [
            'transfer_code.required' => 'Mã phiếu chuyển là bắt buộc',
            'transfer_code.unique' => 'Mã phiếu chuyển đã tồn tại',
            'source_warehouse_id.required' => 'Kho nguồn là bắt buộc',
            'destination_warehouse_id.required' => 'Kho đích là bắt buộc',
            'destination_warehouse_id.different' => 'Kho đích không được trùng với kho nguồn',
            'quantity.min' => 'Số lượng phải lớn hơn 0',
            'transfer_date.required' => 'Ngày chuyển kho là bắt buộc',
            'employee_id.required' => 'Nhân viên thực hiện là bắt buộc',
        ]);

        try {
            DB::beginTransaction();

            // Phân tích chuỗi JSON vật tư
            $materialsData = json_decode($request->materials_json, true);
            if (empty($materialsData)) {
                return back()->with('error', 'Danh sách vật tư không hợp lệ')->withInput();
            }

            // Lấy vật tư chính (vật tư đầu tiên)
            $primaryMaterial = $materialsData[0];
            $materialId = $primaryMaterial['id'];
            $itemType = $primaryMaterial['type'] ?? 'material';

            // Tạo phiếu chuyển kho
            $warehouseTransfer = WarehouseTransfer::create([
                'transfer_code' => $request->transfer_code,
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'material_id' => $materialId,
                'quantity' => $request->quantity ?? 1,
                'transfer_date' => $request->transfer_date,
                'employee_id' => $request->employee_id,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // Kiểm tra nếu trạng thái là 'completed'
            $isCompleted = $request->status === 'completed';

            // Lưu chi tiết vật tư
            foreach ($materialsData as $material) {
                // Xử lý danh sách số serial
                $serialNumbers = null;
                if (!empty($material['serial_numbers'])) {
                    // Kiểm tra xem serial_numbers là chuỗi hay mảng
                    if (is_array($material['serial_numbers'])) {
                        $serialArray = $material['serial_numbers'];
                    } else {
                        $serialArray = preg_split('/[,;\n\r]+/', $material['serial_numbers']);
                    }
                    $serialArray = array_map('trim', $serialArray);
                    $serialArray = array_filter($serialArray);
                    $serialNumbers = !empty($serialArray) ? $serialArray : null;
                }

                WarehouseTransferMaterial::create([
                    'warehouse_transfer_id' => $warehouseTransfer->id,
                    'material_id' => $material['id'],
                    'quantity' => $material['quantity'],
                    'type' => $material['type'] ?? 'material',
                    'serial_numbers' => $serialNumbers,
                    'notes' => $material['notes'] ?? null,
                ]);

                // Nếu trạng thái là 'completed', cập nhật tồn kho
                if ($isCompleted) {
                    $materialId = $material['id'];
                    $itemType = $material['type'] ?? 'material';
                    $quantity = $material['quantity'];

                    // Lấy thông tin vật tư để tạo nhật ký
                    $itemModel = null;
                    if ($itemType == 'material') {
                        $itemModel = Material::find($materialId);
                    } elseif ($itemType == 'product') {
                        $itemModel = Product::find($materialId);
                    } elseif ($itemType == 'good') {
                        $itemModel = Good::find($materialId);
                    }

                    // Giảm số lượng tồn kho ở kho nguồn
                    $this->updateWarehouseStock(
                        $request->source_warehouse_id,
                        $materialId,
                        $itemType,
                        -$quantity,
                        "Giảm tồn kho từ phiếu chuyển kho #{$warehouseTransfer->transfer_code}"
                    );

                    // Tăng số lượng tồn kho ở kho đích
                    $this->updateWarehouseStock(
                        $request->destination_warehouse_id,
                        $materialId,
                        $itemType,
                        $quantity,
                        "Tăng tồn kho từ phiếu chuyển kho #{$warehouseTransfer->transfer_code}"
                    );

                    // Tạo nhật ký chuyển kho
                    if ($itemModel) {
                        ChangeLogHelper::chuyenKho(
                            $itemModel->code,
                            $itemModel->name,
                            $quantity,
                            $warehouseTransfer->transfer_code,
                            "Chuyển từ {$request->source_warehouse_id} sang {$request->destination_warehouse_id}",
                            [
                                'source_warehouse_id' => $request->source_warehouse_id,
                                'source_warehouse_name' => $request->source_warehouse_id,
                                'destination_warehouse_id' => $request->destination_warehouse_id,
                                'destination_warehouse_name' => $request->destination_warehouse_id,
                            ],
                            $warehouseTransfer->notes
                        );
                    }
                }
            }

            DB::commit();

            // Ghi nhật ký tạo mới phiếu chuyển kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'create',
                    'warehouse_transfers',
                    'Tạo mới phiếu chuyển kho: ' . $warehouseTransfer->transfer_code,
                    null,
                    $warehouseTransfer->toArray()
                );
            }

            return redirect()->route('warehouse-transfers.index')->with('success', 'Phiếu chuyển kho đã được tạo thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo phiếu chuyển kho: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi tạo phiếu chuyển kho')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(WarehouseTransfer $warehouseTransfer)
    {
        $warehouseTransfer->load(['source_warehouse', 'destination_warehouse', 'material', 'employee', 'materials.material']);
        $selectedMaterials = $warehouseTransfer->materials->map(function ($item) {
            return [
                'id' => $item->material_id,
                'name' => $item->material->code . ' - ' . $item->material->name,
                'type' => $item->type ?? 'material',
                'quantity' => $item->quantity,
                'serial_numbers' => $item->serial_numbers,
                'notes' => $item->notes
            ];
        })->toArray();

        // Ghi nhật ký xem chi tiết phiếu chuyển kho
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'warehouse_transfers',
                'Xem chi tiết phiếu chuyển kho: ' . $warehouseTransfer->transfer_code,
                null,
                $warehouseTransfer->toArray()
            );
        }

        return view('warehouse-transfers.show', compact('warehouseTransfer', 'selectedMaterials'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WarehouseTransfer $warehouseTransfer)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $materials = Material::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();
        $products = Product::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();
        $goods = Good::where('status', 'active')->where('is_hidden', false)->orderBy('name')->get();

        $warehouseTransfer->load(['materials.material']);
        $selectedMaterials = $warehouseTransfer->materials->map(function ($item) {
            return [
                'id' => $item->material_id,
                'name' => $item->material->code . ' - ' . $item->material->name,
                'type' => $item->type ?? $item->material->category ?? 'other',
                'quantity' => $item->quantity,
                'serial_numbers' => $item->serial_numbers,
                'notes' => $item->notes
            ];
        })->toArray();

        return view('warehouse-transfers.edit', compact(
            'warehouseTransfer',
            'warehouses',
            'employees',
            'materials',
            'products',
            'goods',
            'selectedMaterials'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WarehouseTransfer $warehouseTransfer)
    {
        $request->validate([
            'transfer_code' => 'required|string|max:50|unique:warehouse_transfers,transfer_code,' . $warehouseTransfer->id,
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'quantity' => 'required|integer|min:1',
            'transfer_date' => 'required|date',
            'employee_id' => 'required|exists:employees,id',
            'status' => 'required|in:pending,in_progress,completed,canceled',
            'materials_json' => 'required|json',
        ], [
            'transfer_code.required' => 'Mã phiếu chuyển là bắt buộc',
            'transfer_code.unique' => 'Mã phiếu chuyển đã tồn tại',
            'source_warehouse_id.required' => 'Kho nguồn là bắt buộc',
            'destination_warehouse_id.required' => 'Kho đích là bắt buộc',
            'destination_warehouse_id.different' => 'Kho đích không được trùng với kho nguồn',
            'quantity.required' => 'Số lượng là bắt buộc',
            'quantity.min' => 'Số lượng phải lớn hơn 0',
            'transfer_date.required' => 'Ngày chuyển kho là bắt buộc',
            'employee_id.required' => 'Nhân viên thực hiện là bắt buộc',
        ]);

        // Lưu dữ liệu cũ trước khi cập nhật
        $oldData = $warehouseTransfer->toArray();

        try {
            DB::beginTransaction();

            // Phân tích chuỗi JSON vật tư
            $materialsData = json_decode($request->materials_json, true);
            if (empty($materialsData)) {
                return back()->with('error', 'Danh sách vật tư không hợp lệ')->withInput();
            }

            // Lấy vật tư chính (vật tư đầu tiên)
            $primaryMaterial = $materialsData[0];
            $materialId = $primaryMaterial['id'];
            $itemType = $primaryMaterial['type'] ?? 'material';

            // Kiểm tra nếu trạng thái chuyển thành 'completed'
            $isCompletingTransfer = $request->status === 'completed' && $warehouseTransfer->status !== 'completed';

            // Cập nhật phiếu chuyển kho
            $warehouseTransfer->update([
                'transfer_code' => $request->transfer_code,
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'material_id' => $materialId,
                'quantity' => $request->quantity,
                'transfer_date' => $request->transfer_date,
                'employee_id' => $request->employee_id,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // Ghi nhật ký cập nhật phiếu chuyển kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'warehouse_transfers',
                    'Cập nhật phiếu chuyển kho: ' . $warehouseTransfer->transfer_code,
                    $oldData,
                    $warehouseTransfer->toArray()
                );
            }

            // Xóa chi tiết vật tư hiện có
            $warehouseTransfer->materials()->delete();

            // Tạo lại chi tiết vật tư
            foreach ($materialsData as $material) {
                // Xử lý danh sách số serial
                $serialNumbers = null;
                if (!empty($material['serial_numbers'])) {
                    // Kiểm tra xem serial_numbers là chuỗi hay mảng
                    if (is_array($material['serial_numbers'])) {
                        $serialArray = $material['serial_numbers'];
                    } else {
                        $serialArray = preg_split('/[,;\n\r]+/', $material['serial_numbers']);
                    }
                    $serialArray = array_map('trim', $serialArray);
                    $serialArray = array_filter($serialArray);
                    $serialNumbers = !empty($serialArray) ? $serialArray : null;
                }

                WarehouseTransferMaterial::create([
                    'warehouse_transfer_id' => $warehouseTransfer->id,
                    'material_id' => $material['id'],
                    'quantity' => $material['quantity'],
                    'type' => $material['type'] ?? 'material',
                    'serial_numbers' => $serialNumbers,
                    'notes' => $material['notes'] ?? null,
                ]);

                // Nếu trạng thái chuyển thành 'completed', cập nhật tồn kho
                if ($isCompletingTransfer) {
                    $materialId = $material['id'];
                    $itemType = $material['type'] ?? 'material';
                    $quantity = $material['quantity'];

                    // Lấy thông tin vật tư để tạo nhật ký
                    $itemModel = null;
                    if ($itemType == 'material') {
                        $itemModel = Material::find($materialId);
                    } elseif ($itemType == 'product') {
                        $itemModel = Product::find($materialId);
                    } elseif ($itemType == 'good') {
                        $itemModel = Good::find($materialId);
                    }

                    // Giảm số lượng tồn kho ở kho nguồn
                    $this->updateWarehouseStock(
                        $request->source_warehouse_id,
                        $materialId,
                        $itemType,
                        -$quantity,
                        "Giảm tồn kho từ phiếu chuyển kho #{$warehouseTransfer->transfer_code}"
                    );

                    // Tăng số lượng tồn kho ở kho đích
                    $this->updateWarehouseStock(
                        $request->destination_warehouse_id,
                        $materialId,
                        $itemType,
                        $quantity,
                        "Tăng tồn kho từ phiếu chuyển kho #{$warehouseTransfer->transfer_code}"
                    );

                    // Tạo nhật ký chuyển kho
                    if ($itemModel) {
                        ChangeLogHelper::chuyenKho(
                            $itemModel->code,
                            $itemModel->name,
                            $quantity,
                            $warehouseTransfer->transfer_code,
                            "Chuyển từ {$request->source_warehouse_id} sang {$request->destination_warehouse_id}",
                            [
                                'source_warehouse_id' => $request->source_warehouse_id,
                                'source_warehouse_name' => $request->source_warehouse_id,
                                'destination_warehouse_id' => $request->destination_warehouse_id,
                                'destination_warehouse_name' => $request->destination_warehouse_id,
                            ],
                            $warehouseTransfer->notes
                        );
                    }
                }
            }

            DB::commit();
            return redirect()->route('warehouse-transfers.show', $warehouseTransfer)->with('success', 'Phiếu chuyển kho đã được cập nhật thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật phiếu chuyển kho: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi cập nhật phiếu chuyển kho')->withInput();
        }
    }

    /**
     * Cập nhật số lượng tồn kho
     * 
     * @param int $warehouseId ID của kho
     * @param int $materialId ID của vật tư/thành phẩm/hàng hóa
     * @param string $itemType Loại sản phẩm (material, product, good)
     * @param int $quantityChange Thay đổi số lượng (dương là tăng, âm là giảm)
     * @param string $note Ghi chú về việc thay đổi tồn kho
     * @return bool Kết quả cập nhật
     */
    private function updateWarehouseStock($warehouseId, $materialId, $itemType, $quantityChange, $note = '')
    {
        try {
            // Tìm bản ghi tồn kho
            $warehouseMaterial = WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $materialId)
                ->where('item_type', $itemType)
                ->first();

            if ($warehouseMaterial) {
                // Cập nhật số lượng tồn kho hiện có
                $newQuantity = $warehouseMaterial->quantity + $quantityChange;

                if ($newQuantity <= 0) {
                    // Nếu số lượng mới <= 0, xóa bản ghi tồn kho
                    $warehouseMaterial->delete();
                    Log::info("Đã xóa bản ghi tồn kho: warehouseId={$warehouseId}, materialId={$materialId}, itemType={$itemType}, ghi chú={$note}");
                } else {
                    // Cập nhật số lượng mới
                    $warehouseMaterial->quantity = $newQuantity;
                    $warehouseMaterial->save();
                    Log::info("Cập nhật tồn kho: warehouseId={$warehouseId}, materialId={$materialId}, itemType={$itemType}, thay đổi={$quantityChange}, mới={$warehouseMaterial->quantity}, ghi chú={$note}");
                }
            } else {
                // Tạo mới bản ghi tồn kho nếu chưa tồn tại
                if ($quantityChange > 0) {
                    WarehouseMaterial::create([
                        'warehouse_id' => $warehouseId,
                        'material_id' => $materialId,
                        'item_type' => $itemType,
                        'quantity' => $quantityChange,
                    ]);

                    Log::info("Tạo mới tồn kho: warehouseId={$warehouseId}, materialId={$materialId}, itemType={$itemType}, số lượng={$quantityChange}, ghi chú={$note}");
                } else {
                    Log::warning("Không thể giảm tồn kho không tồn tại: warehouseId={$warehouseId}, materialId={$materialId}, itemType={$itemType}, thay đổi={$quantityChange}");
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Lỗi khi cập nhật tồn kho: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WarehouseTransfer $warehouseTransfer)
    {
        // Lưu dữ liệu cũ trước khi xóa
        $oldData = $warehouseTransfer->toArray();
        $warehouseTransferCode = $warehouseTransfer->transfer_code;

        try {
            $warehouseTransfer->delete();

            // Ghi nhật ký xóa phiếu chuyển kho
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'delete',
                    'warehouse_transfers',  
                    'Xóa phiếu chuyển kho: ' . $warehouseTransferCode,
                    $oldData,
                    null
                );
            }

            return redirect()->route('warehouse-transfers.index')->with('success', 'Phiếu chuyển kho đã được xóa thành công');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa phiếu chuyển kho: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi xóa phiếu chuyển kho');
        }
    }

    /**
     * Kiểm tra số lượng tồn kho của sản phẩm trong kho nguồn
     */
    public function checkInventory(Request $request)
    {
        try {
            $materialId = $request->input('material_id');
            $warehouseId = $request->input('warehouse_id');
            $itemType = $request->input('item_type', 'material');

            Log::info("API checkInventory được gọi với: materialId={$materialId}, warehouseId={$warehouseId}, itemType={$itemType}");

            if (!$materialId || !$warehouseId) {
                Log::warning("Thiếu thông tin sản phẩm hoặc kho: materialId={$materialId}, warehouseId={$warehouseId}");
                return response()->json(['error' => 'Thiếu thông tin sản phẩm hoặc kho'], 400);
            }

            // Kiểm tra xem bảng warehouse_materials có cột item_type không
            $hasItemTypeColumn = Schema::hasColumn('warehouse_materials', 'item_type');
            Log::info("Bảng warehouse_materials có cột item_type: " . ($hasItemTypeColumn ? 'Có' : 'Không'));

            // Truy vấn trực tiếp từ DB để debug
            $rawResults = DB::select("SELECT * FROM warehouse_materials WHERE warehouse_id = ? AND material_id = ?", [$warehouseId, $materialId]);
            Log::info("Raw query results: " . json_encode($rawResults));

            // Khởi tạo query
            $query = WarehouseMaterial::where('warehouse_id', $warehouseId)
                ->where('material_id', $materialId);

            Log::info("Query ban đầu: warehouse_id={$warehouseId}, material_id={$materialId}");

            // Chỉ áp dụng điều kiện item_type nếu cột tồn tại
            if ($hasItemTypeColumn) {
                // Nếu loại là material, có thể trong DB là null hoặc material
                if ($itemType == 'material') {
                    $query->where(function ($q) {
                        $q->where('item_type', 'material')
                            ->orWhereNull('item_type');
                    });
                    Log::info("Áp dụng điều kiện: item_type='material' HOẶC item_type IS NULL");
                } else {
                    $query->where('item_type', $itemType);
                    Log::info("Áp dụng điều kiện: item_type='{$itemType}'");
                }
            }

            // Lấy SQL query để debug
            $querySql = $query->toSql();
            $queryBindings = $query->getBindings();
            Log::info("SQL Query: {$querySql}, Bindings: " . json_encode($queryBindings));

            // Lấy tổng số lượng
            $quantity = $query->sum('quantity');

            // Lấy danh sách các bản ghi tìm thấy để debug
            $records = $query->get();
            Log::info("Tìm thấy " . count($records) . " bản ghi tồn kho");
            foreach ($records as $index => $record) {
                Log::info("Bản ghi #{$index}: id={$record->id}, warehouse_id={$record->warehouse_id}, material_id={$record->material_id}, item_type={$record->item_type}, quantity={$record->quantity}");
            }

            // Lấy thông tin kho
            $warehouse = Warehouse::find($warehouseId);
            $warehouseName = $warehouse ? $warehouse->name : 'Không xác định';

            // Log để debug
            Log::info("Kết quả kiểm tra tồn kho: materialId={$materialId}, warehouseId={$warehouseId}, itemType={$itemType}, quantity={$quantity}, warehouseName={$warehouseName}");

            return response()->json([
                'quantity' => $quantity,
                'warehouse_name' => $warehouseName,
                'has_stock' => $quantity > 0,
                'records_count' => count($records)
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi kiểm tra tồn kho: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra khi kiểm tra tồn kho: ' . $e->getMessage()
            ], 500);
        }
    }
}
