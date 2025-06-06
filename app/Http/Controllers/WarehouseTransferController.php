<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Material;
use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                    $query->where(function($q) use ($search) {
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
        $materials = Material::orderBy('name')->get();
        
        return view('warehouse-transfers.create', compact('warehouses', 'employees', 'materials'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'transfer_code' => 'required|string|max:50|unique:warehouse_transfers',
            'serial' => 'nullable|string|max:100',
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

            // Tạo phiếu chuyển kho
            $warehouseTransfer = WarehouseTransfer::create([
                'transfer_code' => $request->transfer_code,
                'serial' => $request->serial,
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'material_id' => $materialId,
                
                'quantity' => $request->quantity,
                'transfer_date' => $request->transfer_date,
                'employee_id' => $request->employee_id,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // Lưu chi tiết vật tư
            foreach ($materialsData as $material) {
                WarehouseTransferMaterial::create([
                    'warehouse_transfer_id' => $warehouseTransfer->id,
                    'material_id' => $material['id'],
                    'quantity' => $material['quantity'],
                    'type' => $material['type'] ?? null,
                ]);
            }

            DB::commit();
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
        $selectedMaterials = $warehouseTransfer->materials->map(function($item) {
            return [
                'id' => $item->material_id,
                'name' => $item->material->code . ' - ' . $item->material->name,
                'type' => $item->material->category ?? 'other',
                'quantity' => $item->quantity
            ];
        })->toArray();
        
        return view('warehouse-transfers.show', compact('warehouseTransfer', 'selectedMaterials'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WarehouseTransfer $warehouseTransfer)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();
        $materials = Material::orderBy('name')->get();
        
        $warehouseTransfer->load(['materials.material']);
        $selectedMaterials = $warehouseTransfer->materials->map(function($item) {
            return [
                'id' => $item->material_id,
                'name' => $item->material->code . ' - ' . $item->material->name,
                'type' => $item->material->category ?? 'other',
                'quantity' => $item->quantity
            ];
        })->toArray();
        
        return view('warehouse-transfers.edit', compact(
            'warehouseTransfer', 
            'warehouses', 
            'employees', 
            'materials', 
            'selectedMaterials'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WarehouseTransfer $warehouseTransfer)
    {
        $request->validate([
            'transfer_code' => 'required|string|max:50|unique:warehouse_transfers,transfer_code,'.$warehouseTransfer->id,
            'serial' => 'nullable|string|max:100',
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

            // Cập nhật phiếu chuyển kho
            $warehouseTransfer->update([
                'transfer_code' => $request->transfer_code,
                'serial' => $request->serial,
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'material_id' => $materialId,
                'quantity' => $request->quantity,
                'transfer_date' => $request->transfer_date,
                'employee_id' => $request->employee_id,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // Xóa chi tiết vật tư hiện có
            $warehouseTransfer->materials()->delete();

            // Tạo lại chi tiết vật tư
            foreach ($materialsData as $material) {
                WarehouseTransferMaterial::create([
                    'warehouse_transfer_id' => $warehouseTransfer->id,
                    'material_id' => $material['id'],
                    'quantity' => $material['quantity'],
                    'type' => $material['type'] ?? null,
                ]);
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
     * Remove the specified resource from storage.
     */
    public function destroy(WarehouseTransfer $warehouseTransfer)
    {
        try {
            $warehouseTransfer->delete();
            return redirect()->route('warehouse-transfers.index')->with('success', 'Phiếu chuyển kho đã được xóa thành công');
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa phiếu chuyển kho: ' . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi xóa phiếu chuyển kho');
        }
    }
}
