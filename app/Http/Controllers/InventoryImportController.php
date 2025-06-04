<?php

namespace App\Http\Controllers;

use App\Models\InventoryImport;
use App\Models\InventoryImportMaterial;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryImportController extends Controller
{
    /**
     * Hiển thị danh sách phiếu nhập kho.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        
        $query = InventoryImport::with(['supplier', 'warehouse', 'materials.material']);
        
        // Xử lý tìm kiếm
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường được chọn
                switch ($filter) {
                    case 'import_code':
                        $query->where('import_code', 'like', "%{$search}%");
                        break;
                    case 'order_code':
                        $query->where('order_code', 'like', "%{$search}%");
                        break;
                    case 'supplier':
                        $query->whereHas('supplier', function($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                        break;
                }
            } else {
                // Tìm kiếm tổng quát nếu không chọn bộ lọc
                $query->where(function ($q) use ($search) {
                    $q->where('import_code', 'like', "%{$search}%")
                      ->orWhere('order_code', 'like', "%{$search}%")
                      ->orWhereHas('supplier', function($subq) use ($search) {
                          $subq->where('name', 'like', "%{$search}%");
                      });
                });
            }
        }
        
        $inventoryImports = $query->latest()->paginate(10);
        
        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $inventoryImports->appends([
            'search' => $search,
            'filter' => $filter
        ]);
        
        return view('inventory-imports.index', compact('inventoryImports', 'search', 'filter'));
    }

    /**
     * Hiển thị form tạo phiếu nhập kho mới.
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $materials = Material::all();
        
        return view('inventory-imports.create', compact('suppliers', 'warehouses', 'materials'));
    }

    /**
     * Lưu phiếu nhập kho mới vào database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'import_code' => 'required|string|max:255|unique:inventory_imports',
            'import_date' => 'required|date',
            'order_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'materials' => 'required|array|min:1',
            'materials.*.material_id' => 'required|exists:materials,id',
            'materials.*.quantity' => 'required|integer|min:1',
            'materials.*.serial' => 'nullable|string',
        ], [
            'supplier_id.required' => 'Nhà cung cấp không được để trống',
            'supplier_id.exists' => 'Nhà cung cấp không tồn tại',
            'warehouse_id.required' => 'Kho nhập không được để trống',
            'warehouse_id.exists' => 'Kho nhập không tồn tại',
            'import_code.required' => 'Mã phiếu nhập không được để trống',
            'import_code.unique' => 'Mã phiếu nhập đã tồn tại',
            'import_date.required' => 'Ngày nhập kho không được để trống',
            'import_date.date' => 'Ngày nhập kho không hợp lệ',
            'materials.required' => 'Vui lòng thêm ít nhất một vật tư',
            'materials.min' => 'Vui lòng thêm ít nhất một vật tư',
            'materials.*.material_id.required' => 'Vật tư không được để trống',
            'materials.*.material_id.exists' => 'Vật tư không tồn tại',
            'materials.*.quantity.required' => 'Số lượng không được để trống',
            'materials.*.quantity.integer' => 'Số lượng phải là số nguyên',
            'materials.*.quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1',
        ]);

        DB::beginTransaction();
        try {
            // Tạo phiếu nhập kho
            $inventoryImport = InventoryImport::create([
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'import_code' => $request->import_code,
                'import_date' => $request->import_date,
                'order_code' => $request->order_code,
                'notes' => $request->notes,
            ]);
            
            // Thêm các vật tư vào phiếu nhập kho
            foreach ($request->materials as $material) {
                InventoryImportMaterial::create([
                    'inventory_import_id' => $inventoryImport->id,
                    'material_id' => $material['material_id'],
                    'quantity' => $material['quantity'],
                    'serial' => $material['serial'] ?? null,
                    'notes' => $material['notes'] ?? null,
                ]);
            }
            
            DB::commit();
            return redirect()->route('inventory-imports.index')
                ->with('success', 'Phiếu nhập kho đã được thêm thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị chi tiết phiếu nhập kho.
     */
    public function show(string $id)
    {
        $inventoryImport = InventoryImport::with(['supplier', 'warehouse', 'materials.material'])->findOrFail($id);
        return view('inventory-imports.show', compact('inventoryImport'));
    }

    /**
     * Hiển thị form chỉnh sửa phiếu nhập kho.
     */
    public function edit(string $id)
    {
        $inventoryImport = InventoryImport::with(['supplier', 'warehouse', 'materials.material'])->findOrFail($id);
        $suppliers = Supplier::all();
        $warehouses = Warehouse::all();
        $materials = Material::all();
        
        return view('inventory-imports.edit', compact('inventoryImport', 'suppliers', 'warehouses', 'materials'));
    }

    /**
     * Cập nhật phiếu nhập kho trong database.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'import_code' => 'required|string|max:255|unique:inventory_imports,import_code,'.$id,
            'import_date' => 'required|date',
            'order_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'materials' => 'required|array|min:1',
            'materials.*.material_id' => 'required|exists:materials,id',
            'materials.*.quantity' => 'required|integer|min:1',
            'materials.*.serial' => 'nullable|string',
        ], [
            'supplier_id.required' => 'Nhà cung cấp không được để trống',
            'supplier_id.exists' => 'Nhà cung cấp không tồn tại',
            'warehouse_id.required' => 'Kho nhập không được để trống',
            'warehouse_id.exists' => 'Kho nhập không tồn tại',
            'import_code.required' => 'Mã phiếu nhập không được để trống',
            'import_code.unique' => 'Mã phiếu nhập đã tồn tại',
            'import_date.required' => 'Ngày nhập kho không được để trống',
            'import_date.date' => 'Ngày nhập kho không hợp lệ',
            'materials.required' => 'Vui lòng thêm ít nhất một vật tư',
            'materials.min' => 'Vui lòng thêm ít nhất một vật tư',
            'materials.*.material_id.required' => 'Vật tư không được để trống',
            'materials.*.material_id.exists' => 'Vật tư không tồn tại',
            'materials.*.quantity.required' => 'Số lượng không được để trống',
            'materials.*.quantity.integer' => 'Số lượng phải là số nguyên',
            'materials.*.quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1',
        ]);

        DB::beginTransaction();
        try {
            // Cập nhật phiếu nhập kho
            $inventoryImport = InventoryImport::findOrFail($id);
            $inventoryImport->update([
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'import_code' => $request->import_code,
                'import_date' => $request->import_date,
                'order_code' => $request->order_code,
                'notes' => $request->notes,
            ]);
            
            // Xóa tất cả các vật tư cũ của phiếu nhập kho
            $inventoryImport->materials()->delete();
            
            // Thêm lại các vật tư mới vào phiếu nhập kho
            foreach ($request->materials as $material) {
                InventoryImportMaterial::create([
                    'inventory_import_id' => $inventoryImport->id,
                    'material_id' => $material['material_id'],
                    'quantity' => $material['quantity'],
                    'serial' => $material['serial'] ?? null,
                    'notes' => $material['notes'] ?? null,
                ]);
            }
            
            DB::commit();
            return redirect()->route('inventory-imports.show', $id)
                ->with('success', 'Phiếu nhập kho đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Xóa phiếu nhập kho khỏi database.
     */
    public function destroy(string $id)
    {
        $inventoryImport = InventoryImport::findOrFail($id);
        $inventoryImport->materials()->delete();
        $inventoryImport->delete();

        return redirect()->route('inventory-imports.index')
            ->with('success', 'Phiếu nhập kho đã được xóa thành công.');
    }
} 