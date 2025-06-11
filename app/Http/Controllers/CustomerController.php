<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

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
                    case 'company_name':
                        $query->where('company_name', 'like', "%{$search}%");
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
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('company_phone', 'like', "%{$search}%")
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
            'company_name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:10,11',
            'company_phone' => 'nullable|numeric|digits_between:10,11',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'customer_name.required' => 'Tên người đại diện không được để trống',
            'customer_name.string' => 'Tên người đại diện phải là chuỗi ký tự',
            'customer_name.max' => 'Tên người đại diện không được vượt quá 255 ký tự',
            'company_name.required' => 'Tên công ty không được để trống',
            'company_name.string' => 'Tên công ty phải là chuỗi ký tự',
            'company_name.max' => 'Tên công ty không được vượt quá 255 ký tự',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Số điện thoại chỉ được nhập số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 số',
            'company_phone.numeric' => 'Số điện thoại công ty chỉ được nhập số',
            'company_phone.digits_between' => 'Số điện thoại công ty phải có từ 10 đến 11 số',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự',
        ]);

        Customer::create([
            'name' => $request->customer_name,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'company_phone' => $request->company_phone,
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
        
        // Lấy danh sách dự án liên quan đến khách hàng
        $projects = $customer->projects()->latest()->get();
        
        // Lấy danh sách phiếu cho thuê liên quan đến khách hàng
        $rentals = $customer->rentals()->latest()->get();
        
        return view('customers.show', compact('customer', 'projects', 'rentals'));
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
            'company_name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:10,11',
            'company_phone' => 'nullable|numeric|digits_between:10,11',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'customer_name.required' => 'Tên người đại diện không được để trống',
            'customer_name.string' => 'Tên người đại diện phải là chuỗi ký tự',
            'customer_name.max' => 'Tên người đại diện không được vượt quá 255 ký tự',
            'company_name.required' => 'Tên công ty không được để trống',
            'company_name.string' => 'Tên công ty phải là chuỗi ký tự',
            'company_name.max' => 'Tên công ty không được vượt quá 255 ký tự',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Số điện thoại chỉ được nhập số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 số',
            'company_phone.numeric' => 'Số điện thoại công ty chỉ được nhập số',
            'company_phone.digits_between' => 'Số điện thoại công ty phải có từ 10 đến 11 số',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update([
            'name' => $request->customer_name,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'company_phone' => $request->company_phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
        ]);

        return redirect()->route('customers.show', $id)
            ->with('success', 'Thông tin khách hàng đã được cập nhật thành công.');
    }

    /**
     * Kích hoạt tài khoản khách hàng
     */
    public function activateAccount(string $id)
    {
        $customer = Customer::findOrFail($id);
        
        // Kiểm tra nếu đã có tài khoản
        if ($customer->has_account) {
            return redirect()->route('customers.index')
                ->with('error', 'Khách hàng đã có tài khoản.');
        }
        
        // Tạo username từ email hoặc tên công ty
        $username = $customer->email ? explode('@', $customer->email)[0] : Str::slug($customer->company_name);
        $username = $username . rand(100, 999); // Thêm số ngẫu nhiên để tránh trùng
        
        // Tạo mật khẩu ngẫu nhiên
        $password = Str::random(10);
        
        // Tạo tài khoản người dùng mới
        User::create([
            'name' => $customer->name,
            'email' => $customer->email,
            'username' => $username,
            'password' => Hash::make($password),
            'role' => 'customer',
            'customer_id' => $customer->id,
        ]);
        
        // Cập nhật trạng thái tài khoản và lưu thông tin đăng nhập
        $customer->update([
            'has_account' => true,
            'account_username' => $username,
            'account_password' => $password // Lưu mật khẩu gốc (không phải đã hash)
        ]);
        
        return redirect()->route('customers.show', $id)
            ->with('success', "Tài khoản khách hàng đã được kích hoạt thành công!");
    }

    /**
     * Xóa khách hàng khỏi database.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        
        // Kiểm tra xem khách hàng có dự án liên quan không
        if ($customer->projects()->count() > 0) {
            return redirect()->route('customers.show', $id)
                ->with('error', 'Không thể xóa khách hàng này vì có dự án liên quan. Vui lòng xóa dự án trước.');
        }
        
        // Kiểm tra xem khách hàng có phiếu cho thuê liên quan không
        if ($customer->rentals()->count() > 0) {
            return redirect()->route('customers.show', $id)
                ->with('error', 'Không thể xóa khách hàng này vì có phiếu cho thuê liên quan. Vui lòng xóa phiếu cho thuê trước.');
        }
        
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Khách hàng đã được xóa thành công.');
    }

    /**
     * API - Lấy thông tin chi tiết của khách hàng.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerInfo($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy khách hàng'
            ], 404);
        }
    }

    /**
     * Khóa hoặc mở khóa tài khoản khách hàng
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleLock(string $id)
    {
        $customer = Customer::findOrFail($id);
        
        // Kiểm tra nếu chưa có tài khoản
        if (!$customer->has_account) {
            return redirect()->back()
                ->with('error', 'Khách hàng chưa có tài khoản nên không thể khóa/mở khóa.');
        }
        
        // Tìm tài khoản người dùng liên kết với khách hàng
        $user = User::where('customer_id', $customer->id)->first();
        
        if (!$user) {
            return redirect()->back()
                ->with('error', 'Không tìm thấy tài khoản người dùng liên kết với khách hàng này.');
        }
        
        // Đảo trạng thái khóa tài khoản
        $isLocked = !($customer->is_locked ?? false);
        
        // Cập nhật trạng thái khóa trong bảng customers
        $customer->update([
            'is_locked' => $isLocked
        ]);
        
        // Cập nhật trạng thái kích hoạt trong bảng users
        $user->update([
            'active' => !$isLocked
        ]);
        
        $message = $isLocked 
            ? 'Tài khoản khách hàng đã được khóa thành công.' 
            : 'Tài khoản khách hàng đã được mở khóa thành công.';
        
        return redirect()->back()
            ->with('success', $message);
    }
}
