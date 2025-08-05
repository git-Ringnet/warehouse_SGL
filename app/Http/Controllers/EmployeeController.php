<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeesExport;

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
                    case 'department':
                        $query->where('department', 'like', "%{$search}%");
                        break;
                    case 'role':
                        $query->whereHas('roleGroup', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
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
                      ->orWhere('department', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhereHas('roleGroup', function ($subQ) use ($search) {
                          $subQ->where('name', 'like', "%{$search}%");
                      });
                });
            }
        }
        
        $employees = $query->with('roleGroup')->oldest()->paginate(10);
        
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
        // Lấy danh sách các nhóm quyền hiện có
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        
        return view('employees.create', compact('roles'));
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
            'department' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string',
            'role_id' => 'nullable|exists:roles,id',
            'scope_type' => 'nullable|string',
            'scope_value' => 'nullable|string',
            'is_active' => 'required|boolean',
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
            'department.max' => 'Tên phòng ban không được vượt quá 255 ký tự',
            'is_active.required' => 'Trạng thái không được để trống',
            'role_id.exists' => 'Vai trò không hợp lệ',
            'avatar.image' => 'Tệp phải là hình ảnh',
            'avatar.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif',
            'avatar.max' => 'Kích thước hình ảnh không được vượt quá 2MB',
        ]);

        // Đặt vai trò mặc định là 'staff' nếu không có
        $data = $request->except('avatar');
        $data['role'] = 'staff'; // Mặc định là nhân viên
        $data['status'] = $data['is_active'] ? 'active' : 'inactive'; // Đồng bộ status cũ với is_active mới

        // Xử lý upload avatar nếu có
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('employees/avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        $employee = Employee::create($data);
        
        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'create',
                'employees',
                'Tạo nhân viên: ' . $employee->name,
                null,
                $employee->toArray()
            );
        }

        return redirect()->route('employees.index')
            ->with('success', 'Nhân viên đã được thêm thành công.');
    }

    /**
     * Hiển thị chi tiết nhân viên.
     */
    public function show(string $id)
    {
        $employee = Employee::with(['roleGroup', 'projects', 'rentals', 'warehouses'])->findOrFail($id);
        
        // Lấy danh sách dự án và phiếu cho thuê liên quan đến nhân viên
        $projects = $employee->projects()->latest()->get();
        $rentals = $employee->rentals()->latest()->get();
        
        // Debug thông tin kho
        Log::info('Employee warehouses:', [
            'employee_id' => $employee->id,
            'warehouses' => $employee->warehouses()->get()
        ]);

        // Ghi nhật ký xem chi tiết nhân viên
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'employees',
                'Xem chi tiết nhân viên: ' . $employee->name,
                null,
                ['id' => $employee->id, 'name' => $employee->name, 'role' => $employee->role]
            );
        }
        
        return view('employees.show', compact('employee', 'projects', 'rentals'));
    }

    /**
     * Hiển thị form chỉnh sửa thông tin nhân viên.
     */
    public function edit(string $id)
    {
        $employee = Employee::findOrFail($id);
        
        // Lấy danh sách các nhóm quyền hiện có
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        
        return view('employees.edit', compact('employee', 'roles'));
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
            'department' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string',
            'role_id' => 'nullable|exists:roles,id',
            'scope_type' => 'nullable|string',
            'scope_value' => 'nullable|string',
            'is_active' => 'required|boolean',
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
            'department.max' => 'Tên phòng ban không được vượt quá 255 ký tự',
            'is_active.required' => 'Trạng thái không được để trống',
            'role_id.exists' => 'Vai trò không hợp lệ',
            'avatar.image' => 'Tệp phải là hình ảnh',
            'avatar.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif',
            'avatar.max' => 'Kích thước hình ảnh không được vượt quá 2MB',
        ];

        $request->validate($rules, $messages);

        $oldData = $employee->toArray();

        // Cập nhật thông tin
        $data = $request->except(['password', 'password_confirmation', 'avatar']);
        
        // Đồng bộ status cũ với is_active mới
        $data['status'] = $data['is_active'] ? 'active' : 'inactive';
        $data['role'] = $employee->role; // Giữ nguyên vai trò
        
        // Cập nhật mật khẩu nếu có nhập
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }
        
        // Xử lý upload avatar nếu có
        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu tồn tại
            if ($employee->avatar && Storage::disk('public')->exists($employee->avatar)) {
                Storage::disk('public')->delete($employee->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('employees/avatars', 'public');
            $data['avatar'] = $avatarPath;
        }
        
        $employee->update($data);
        
        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'update',
                'employees',
                'Cập nhật thông tin nhân viên: ' . $employee->name,
                $oldData,
                $employee->toArray()
            );
        }

        return redirect()->route('employees.show', $id)
            ->with('success', 'Thông tin nhân viên đã được cập nhật thành công.');
    }

    /**
     * Xóa nhân viên khỏi database.
     */
    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employeeName = $employee->name;
        $employeeData = $employee->toArray();
        
        // Kiểm tra xem nhân viên có liên quan đến dự án không
        if ($employee->projects()->count() > 0) {
            return redirect()->route('employees.show', $id)
                ->with('error', 'Không thể xóa nhân viên này vì có dự án liên quan.');
        }
        
        // Kiểm tra xem nhân viên có liên quan đến phiếu cho thuê không
        if ($employee->rentals()->count() > 0) {
            return redirect()->route('employees.show', $id)
                ->with('error', 'Không thể xóa nhân viên này vì có phiếu cho thuê liên quan.');
        }

        // Kiểm tra xem nhân viên có đang quản lý kho nào không
        if ($employee->warehouses()->count() > 0) {
            return redirect()->route('employees.show', $id)
                ->with('error', 'Không thể xóa nhân viên này vì đang quản lý kho.');
        }
        
        // Kiểm tra xem nhân viên có liên quan đến phiếu kiểm thử không
        if (
            \App\Models\Testing::where('tester_id', $id)->exists() || 
            \App\Models\Testing::where('assigned_to', $id)->exists() || 
            \App\Models\Testing::where('receiver_id', $id)->exists() || 
            \App\Models\Testing::where('approved_by', $id)->exists() || 
            \App\Models\Testing::where('received_by', $id)->exists()
        ) {
            return redirect()->route('employees.show', $id)
                ->with('error', 'Không thể xóa nhân viên này vì có phiếu kiểm thử liên quan.');
        }
        
        // Kiểm tra xem nhân viên có liên quan đến phiếu lắp ráp không
        if (
            \App\Models\Assembly::where('assigned_employee_id', $id)->exists() || 
            \App\Models\Assembly::where('tester_id', $id)->exists()
        ) {
            return redirect()->route('employees.show', $id)
                ->with('error', 'Không thể xóa nhân viên này vì có phiếu lắp ráp liên quan.');
        }
        
        $employee->delete();
        
        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'delete',
                'employees',
                'Xóa nhân viên: ' . $employeeName,
                $employeeData,
                null
            );
        }

        return redirect()->route('employees.index')
            ->with('success', 'Nhân viên đã được xóa thành công.');
    }

    /**
     * Khóa/mở khóa tài khoản nhân viên.
     */
    public function toggleActive(string $id)
    {
        $employee = Employee::findOrFail($id);
        $isActive = $employee->toggleActive();
        
        $statusText = $isActive ? "mở khóa" : "khóa";
        
        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'update',
                'employees',
                'Đã ' . $statusText . ' tài khoản nhân viên: ' . $employee->name,
                ['is_active' => !$isActive],
                ['is_active' => $isActive]
            );
        }

        return redirect()->route('employees.index')
            ->with('success', 'Đã ' . $statusText . ' tài khoản nhân viên thành công.');
    }

    /**
     * Khóa/mở khóa tài khoản nhân viên và đăng xuất khỏi tất cả thiết bị
     */
    public function toggleStatus(string $id)
    {
        $employee = Employee::findOrFail($id);
        
        // Lưu trạng thái cũ để ghi log
        $oldStatus = $employee->is_active;
        
        // Đổi trạng thái
        $employee->is_active = !$employee->is_active;
        $employee->save();
        
        // Nếu khóa tài khoản, xóa tất cả session của user đó
        if (!$employee->is_active) {
            // Xóa tất cả session của user này
            DB::table('sessions')
                ->where('user_id', $employee->id)
                ->delete();
        }
        
        // Ghi log
        if (Auth::check()) {
            $action = $employee->is_active ? 'unlock' : 'lock';
            $message = $employee->is_active ? 
                'Mở khóa tài khoản nhân viên: ' . $employee->name :
                'Khóa tài khoản nhân viên: ' . $employee->name;
                
            UserLog::logActivity(
                Auth::id(),
                $action,
                'employees',
                $message,
                ['is_active' => $oldStatus],
                ['is_active' => $employee->is_active]
            );
        }
        
        $message = $employee->is_active ? 
            'Tài khoản đã được mở khóa thành công.' :
            'Tài khoản đã được khóa thành công và người dùng đã bị đăng xuất khỏi tất cả thiết bị.';
            
        return redirect()->back()->with('success', $message);
    }

    /**
     * Export employees list to PDF
     */
    public function exportPDF(Request $request)
    {
        try {
            // Build the query with filters
            $query = Employee::query();

            // Apply filters
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('username', 'like', "%{$searchTerm}%")
                        ->orWhere('name', 'like', "%{$searchTerm}%")
                        ->orWhere('phone', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%")
                        ->orWhere('department', 'like', "%{$searchTerm}%")
                        ->orWhereHas('roleGroup', function($q) use ($searchTerm) {
                            $q->where('name', 'like', "%{$searchTerm}%");
                        });
                });
            }

            $employees = $query->with('roleGroup')->get();

            // Generate PDF using DomPDF
            $pdf = PDF::loadView('employees.pdf', compact('employees'));

            // Set paper size and orientation
            $pdf->setPaper('a4', 'landscape');

            // Set PDF options
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'debugKeepTemp' => false,
                'debugCss' => false,
                'debugLayout' => false,
                'debugLayoutLines' => false,
                'debugLayoutBlocks' => false,
                'debugLayoutInline' => false,
                'debugLayoutPaddingBox' => false,
                'chroot' => public_path(),
                'isFontSubsettingEnabled' => true,
                'isRemoteEnabled' => true
            ]);

            // Generate filename
            $filename = 'danh-sach-nhan-vien-' . date('Y-m-d-His') . '.pdf';

            // Download the file
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Export PDF error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export employees list to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            // Get current filters from request
            $filters = [
                'search' => $request->get('search'),
                'filter' => $request->get('filter')
            ];

            return Excel::download(new EmployeesExport($filters), 'danh-sach-nhan-vien-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Export Excel error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất Excel: ' . $e->getMessage());
        }
    }
} 