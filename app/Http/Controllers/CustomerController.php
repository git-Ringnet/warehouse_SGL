<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Hiển thị danh sách khách hàng.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        
        $query = Customer::query();
        
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
        
        $customers = $query->latest()->paginate(10);
        
        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $customers->appends([
            'search' => $search,
            'filter' => $filter
        ]);
        
        return view('customers.index', compact('customers', 'search', 'filter'));
    }

    /**
     * Hiển thị form tạo khách hàng mới.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Lưu khách hàng mới vào database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:10,11',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'phone.numeric' => 'Số điện thoại chỉ được nhập số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 số',
        ]);

        Customer::create([
            'name' => $request->customer_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Khách hàng đã được thêm thành công.');
    }

    /**
     * Hiển thị chi tiết khách hàng.
     */
    public function show(string $id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.show', compact('customer'));
    }

    /**
     * Hiển thị form chỉnh sửa thông tin khách hàng.
     */
    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    /**
     * Cập nhật thông tin khách hàng trong database.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:10,11',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'phone.numeric' => 'Số điện thoại chỉ được nhập số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 số',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update([
            'name' => $request->customer_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        return redirect()->route('customers.show', $id)
            ->with('success', 'Thông tin khách hàng đã được cập nhật thành công.');
    }

    /**
     * Xóa khách hàng khỏi database.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Khách hàng đã được xóa thành công.');
    }
}
