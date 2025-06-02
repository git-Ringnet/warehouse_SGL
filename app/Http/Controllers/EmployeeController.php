<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Hiển thị danh sách nhân viên.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        
        $query = Employee::query();
        
        // Xử lý tìm kiếm
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường được chọn
                switch ($filter) {
                    case 'username':
                        $query->where('username', 'like', "%{$search}%");
                        break;
                    case 'name':
                        $query->where('name', 'like', "%{$search}%");
                        break;
                    case 'phone':
                        $query->where('phone', 'like', "%{$search}%");
                        break;
                    case 'email':
                        $query->where('email', 'like', "%{$search}%");
                        break;
                    case 'role':
                        $query->where('role', 'like', "%{$search}%");
                        break;
                    case 'status':
                        $query->where('status', 'like', "%{$search}%");
                        break;
                }
            } else {
                // Tìm kiếm tổng quát nếu không chọn bộ lọc
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('role', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%");
                });
            }
        }
        
        $employees = $query->latest()->paginate(10);
        
        // Giữ lại tham số tìm kiếm và lọc khi phân trang
        $employees->appends([
            'search' => $search,
            'filter' => $filter
        ]);
        
        return view('employees.index', compact('employees', 'search', 'filter'));
    }

    /**
     * Hiển thị form tạo nhân viên mới.
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Lưu nhân viên mới vào database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:employees,username|alpha_num',
            'password' => 'required|string|min:8|confirmed',
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:10,11',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'hire_date' => 'required|date',
            'notes' => 'nullable|string',
            'role' => 'required|string',
            'status' => 'required|string',
        ], [
            'username.required' => 'Username không được để trống',
            'username.unique' => 'Username đã tồn tại',
            'username.alpha_num' => 'Username chỉ được chứa chữ cái và số',
            'password.required' => 'Mật khẩu không được để trống',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'name.required' => 'Tên nhân viên không được để trống',
            'name.string' => 'Tên nhân viên phải là chuỗi ký tự',
            'name.max' => 'Tên nhân viên không được vượt quá 255 ký tự',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Số điện thoại chỉ được nhập số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 số',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự',
            'hire_date.required' => 'Ngày tuyển dụng không được để trống',
            'hire_date.date' => 'Ngày tuyển dụng không đúng định dạng',
            'role.required' => 'Vai trò không được để trống',
            'status.required' => 'Trạng thái không được để trống',
        ]);

        Employee::create($request->all());

        return redirect()->route('employees.index')
            ->with('success', 'Nhân viên đã được thêm thành công.');
    }

    /**
     * Hiển thị chi tiết nhân viên.
     */
    public function show(string $id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.show', compact('employee'));
    }

    /**
     * Hiển thị form chỉnh sửa thông tin nhân viên.
     */
    public function edit(string $id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.edit', compact('employee'));
    }

    /**
     * Cập nhật thông tin nhân viên trong database.
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);
        
        $rules = [
            'username' => 'required|string|max:255|unique:employees,username,'.$id.'|alpha_num',
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:10,11',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'hire_date' => 'required|date',
            'notes' => 'nullable|string',
            'role' => 'required|string',
            'status' => 'required|string',
        ];
        
        // Chỉ xác thực mật khẩu khi có nhập
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
        
        $messages = [
            'username.required' => 'Username không được để trống',
            'username.unique' => 'Username đã tồn tại',
            'username.alpha_num' => 'Username chỉ được chứa chữ cái và số',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'name.required' => 'Tên nhân viên không được để trống',
            'name.string' => 'Tên nhân viên phải là chuỗi ký tự',
            'name.max' => 'Tên nhân viên không được vượt quá 255 ký tự',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Số điện thoại chỉ được nhập số',
            'phone.digits_between' => 'Số điện thoại phải có từ 10 đến 11 số',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự',
            'hire_date.required' => 'Ngày tuyển dụng không được để trống',
            'hire_date.date' => 'Ngày tuyển dụng không đúng định dạng',
            'role.required' => 'Vai trò không được để trống',
            'status.required' => 'Trạng thái không được để trống',
        ];

        $request->validate($rules, $messages);

        // Cập nhật thông tin
        $data = $request->except(['password', 'password_confirmation']);
        
        // Cập nhật mật khẩu nếu có nhập
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }
        
        $employee->update($data);

        return redirect()->route('employees.show', $id)
            ->with('success', 'Thông tin nhân viên đã được cập nhật thành công.');
    }

    /**
     * Xóa nhân viên khỏi database.
     */
    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Nhân viên đã được xóa thành công.');
    }
} 