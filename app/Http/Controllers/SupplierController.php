<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Material;
use App\Models\Good;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Hiển thị danh sách nhà cung cấp.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        
        $query = Supplier::query();
        
        // Xử lý tìm kiếm
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường được chọn
                switch ($filter) {
                    case 'name':
                        $query->where('name', 'like', "%{$search}%");
                        break;
                    case 'phone':
                        $query->where('phone', 'like', "%{$search}%");
                        break;
                    case 'email':
                        $query->where('email', 'like', "%{$search}%");
                        break;
                    case 'address':
                        $query->where('address', 'like', "%{$search}%");
                        break;
                }
            } else {
                // Tìm kiếm tổng quát nếu không chọn bộ lọc
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }
        }
        
        $suppliers = $query->latest()->paginate(10);
        
        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $suppliers->appends([
            'search' => $search,
            'filter' => $filter
        ]);
        
        return view('suppliers.index', compact('suppliers', 'search', 'filter'));
    }

    /**
     * Hiển thị form tạo nhà cung cấp mới.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Lưu nhà cung cấp mới vào database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'representative' => 'nullable|string|max:255',
            'phone' => 'required|numeric|digits_between:10,11',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'name.required' => 'Tên nhà cung cấp không được để trống',
            'name.string' => 'Tên nhà cung cấp phải là chuỗi ký tự',
            'name.max' => 'Tên nhà cung cấp không được vượt quá 255 ký tự',
            'representative.string' => 'Tên người đại diện phải là chuỗi ký tự',
            'representative.max' => 'Tên người đại diện không được vượt quá 255 ký tự',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Số điện thoại chỉ được nhập số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 số',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự',
        ]);

        Supplier::create($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Nhà cung cấp đã được thêm thành công.');
    }

    /**
     * Hiển thị chi tiết nhà cung cấp.
     */
    public function show(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        
        // Lấy các vật tư liên quan với nhà cung cấp này thông qua relationship
        $materials = $supplier->materials;
        
        // Lấy các hàng hóa liên quan với nhà cung cấp này thông qua relationship
        $goods = $supplier->goods;
        
        return view('suppliers.show', compact('supplier', 'materials', 'goods'));
    }

    /**
     * Hiển thị form chỉnh sửa thông tin nhà cung cấp.
     */
    public function edit(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Cập nhật thông tin nhà cung cấp trong database.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'representative' => 'nullable|string|max:255',
            'phone' => 'required|numeric|digits_between:10,11',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'name.required' => 'Tên nhà cung cấp không được để trống',
            'name.string' => 'Tên nhà cung cấp phải là chuỗi ký tự',
            'name.max' => 'Tên nhà cung cấp không được vượt quá 255 ký tự',
            'representative.string' => 'Tên người đại diện phải là chuỗi ký tự',
            'representative.max' => 'Tên người đại diện không được vượt quá 255 ký tự',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Số điện thoại chỉ được nhập số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 số',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự',
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        return redirect()->route('suppliers.show', $id)
            ->with('success', 'Thông tin nhà cung cấp đã được cập nhật thành công.');
    }

    /**
     * Xóa nhà cung cấp khỏi database.
     */
    public function destroy(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        
        // Kiểm tra xem nhà cung cấp có vật tư liên quan không
        $materialsCount = $supplier->materials()->count();
        
        if ($materialsCount > 0) {
            return redirect()->route('suppliers.show', $id)
                ->with('error', 'Không thể xóa nhà cung cấp này vì có vật tư liên quan. Vui lòng xóa các vật tư trước.');
        }
        
        // Kiểm tra xem nhà cung cấp có hàng hóa liên quan không
        $goodsCount = $supplier->goods()->count();
        
        if ($goodsCount > 0) {
            return redirect()->route('suppliers.show', $id)
                ->with('error', 'Không thể xóa nhà cung cấp này vì có hàng hóa liên quan. Vui lòng xóa các hàng hóa trước.');
        }
        
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Nhà cung cấp đã được xóa thành công.');
    }
}
