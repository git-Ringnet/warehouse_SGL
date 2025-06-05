<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Material;
use App\Models\Employee;
use App\Models\WarehouseMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the warehouses.
     */
    public function index()
    {
        $warehouses = Warehouse::all();
        return view('warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create()
    {
        $employees = Employee::all();
        return view('warehouses.create', compact('employees'));
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:warehouses,code',
            'name' => 'required',
            'address' => 'required',
            'manager' => 'required',
            'phone' => 'required',
        ]);

        Warehouse::create($request->all());

        return redirect()->route('warehouses.index')
            ->with('success', 'Kho hàng đã được thêm thành công.');
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse)
    {
        return view('warehouses.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit(Warehouse $warehouse)
    {
        $employees = Employee::all();
        return view('warehouses.edit', compact('warehouse', 'employees'));
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'code' => 'required|unique:warehouses,code,'.$warehouse->id,
            'name' => 'required',
            'address' => 'required',
            'manager' => 'required',
            'phone' => 'required',
        ]);

        $warehouse->update($request->all());

        return redirect()->route('warehouses.index')
            ->with('success', 'Kho hàng đã được cập nhật thành công.');
    }

    /**
     * Remove the specified warehouse from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Kho hàng đã được xóa thành công.');
    }
    
    /**
     * Get materials for a specific warehouse
     */
    public function getMaterials($warehouseId, Request $request)
    {
        try {
            $searchTerm = $request->input('term');
            
            // Get the warehouse
            $warehouse = Warehouse::findOrFail($warehouseId);
            
            // Query for materials in this warehouse
            // Chỉ lấy các mục có item_type = 'material', nếu trường này đã tồn tại
            $query = WarehouseMaterial::where('warehouse_id', $warehouseId);
            
            // Chỉ áp dụng điều kiện item_type nếu cột này đã tồn tại trong bảng
            if (Schema::hasColumn('warehouse_materials', 'item_type')) {
                $query->where('item_type', 'material');
            }
            
            $query->with('material');
            
            // Add search filter if term is provided
            if ($searchTerm) {
                $query->whereHas('material', function($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(code) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                      ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                      ->orWhereRaw('LOWER(category) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                      ->orWhereRaw('LOWER(serial) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
                });
            }
            
            // Get warehouse materials with their related material information
            $warehouseMaterials = $query->get();
            
            // Format the response
            $materials = $warehouseMaterials->map(function($warehouseMaterial) {
                $material = $warehouseMaterial->material;
                return [
                    'id' => $material->id,
                    'code' => $material->code,
                    'name' => $material->name,
                    'category' => $material->category,
                    'serial' => $material->serial ?? null,
                    'stock_quantity' => $warehouseMaterial->quantity,
                ];
            });
            
            return response()->json($materials);
        } catch (\Exception $e) {
            Log::error('Warehouse materials error: ' . $e->getMessage());
            
            return response()->json([
                'error' => true,
                'message' => 'Có lỗi xảy ra khi lấy danh sách vật tư: ' . $e->getMessage()
            ], 500);
        }
    }
} 