<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * Khởi tạo controller - middleware được áp dụng trong routes
     */
    public function __construct()
    {
        // Middleware admin-only đã được áp dụng trong routes
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');

        $query = Role::query();

        // Xử lý tìm kiếm
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường cụ thể
                $query->where($filter, 'like', "%{$search}%");
            } else {
                // Tìm kiếm tổng quát
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('scope', 'like', "%{$search}%");
                });
            }
        }

        // Lọc theo trạng thái
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $roles = $query->orderBy('is_system', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('roles.index', compact('roles', 'search', 'filter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Lấy tất cả quyền trong hệ thống
        $permissions = Permission::orderBy('group')
            ->orderBy('display_name')
            ->get()
            ->groupBy('group');

        // Lấy tất cả nhân viên
        $employees = \App\Models\Employee::orderBy('name')->get();

        // Lấy tất cả dự án (sử dụng project_name thay vì name)
        $projects = \App\Models\Project::orderBy('project_name')->get();

        // Lấy tất cả hợp đồng cho thuê (không lọc theo status vì cột không tồn tại)
        $rentals = \App\Models\Rental::orderBy('created_at', 'desc')->get();

        return view('roles.create', compact('permissions', 'employees', 'projects', 'rentals'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:255',
            'scope' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'employees' => 'nullable|array',
            'employees.*' => 'exists:employees,id',
            'projects' => 'nullable|array',
            'projects.*' => 'exists:projects,id',
            'rentals' => 'nullable|array',
            'rentals.*' => 'exists:rentals,id',
        ]);

        // KIỂM TRA TRÙNG LẶP TRƯỚC KHI TẠO NHÓM QUYỀN
        if ($request->has('employees') && $request->has('permissions')) {
            $duplicateWarnings = [];
            $requestPermissions = $request->permissions;

            foreach ($request->employees as $employeeId) {
                $employee = \App\Models\Employee::find($employeeId);
                if ($employee && $employee->role_id) {
                    // Lấy quyền của role hiện tại của nhân viên
                    $currentRole = Role::find($employee->role_id);
                    if ($currentRole) {
                        $currentPermissions = $currentRole->permissions()->pluck('permissions.id')->toArray();

                        // Tìm quyền trùng lặp
                        $duplicates = array_intersect($requestPermissions, $currentPermissions);

                        if (!empty($duplicates)) {
                            $duplicateWarnings[] = [
                                'employee' => $employee,
                                'current_role' => $currentRole,
                                'duplicate_permissions' => Permission::whereIn('id', $duplicates)->get()
                            ];
                        }
                    }
                }
            }

            if (!empty($duplicateWarnings)) {
                return redirect()->back()
                    ->withInput()
                    ->with('duplicate_warnings', $duplicateWarnings)
                    ->with('warning', 'Phát hiện quyền trùng lặp với một số nhân viên. Vui lòng kiểm tra lại.');
            }
        }

        // TẠO ROLE SAU KHI ĐÃ KIỂM TRA TRÙNG LẶP
        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'scope' => $request->scope,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        // Gán quyền
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        // Gán nhân viên vào nhóm quyền (đã kiểm tra trùng lặp ở trên)
        if ($request->has('employees')) {

            \App\Models\Employee::whereIn('id', $request->employees)->update(['role_id' => $role->id]);

            // Ghi nhật ký cho việc gán nhân viên
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'employees',
                    'Gán ' . count($request->employees) . ' nhân viên vào nhóm quyền ' . $role->name,
                    null,
                    ['role_id' => $role->id, 'employees' => $request->employees]
                );
            }
        }

        // Gán dự án cho nhóm quyền
        if ($request->has('projects')) {
            $role->projects()->sync($request->projects);

            // Ghi nhật ký cho việc gán dự án
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'project_role',
                    'Gán ' . count($request->projects) . ' dự án cho nhóm quyền ' . $role->name,
                    null,
                    ['role_id' => $role->id, 'projects' => $request->projects]
                );
            }
        }

        // Gán hợp đồng cho thuê cho nhóm quyền
        if ($request->has('rentals')) {
            $role->rentals()->sync($request->rentals);

            // Ghi nhật ký cho việc gán hợp đồng cho thuê
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'rental_role',
                    'Gán ' . count($request->rentals) . ' hợp đồng cho thuê cho nhóm quyền ' . $role->name,
                    null,
                    ['role_id' => $role->id, 'rentals' => $request->rentals]
                );
            }
        }

        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'create',
                'roles',
                'Tạo nhóm quyền: ' . $role->name,
                null,
                $role->toArray()
            );
        }

        return redirect()->route('roles.index')
            ->with('success', 'Nhóm quyền đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        // Lấy tất cả quyền của role
        $rolePermissions = $role->permissions()->pluck('permissions.id')->toArray();

        // Lấy tất cả quyền phân theo nhóm
        $permissions = Permission::orderBy('group')
            ->orderBy('display_name')
            ->get()
            ->groupBy('group');

        // Lấy dự án được gán cho nhóm quyền này
        $projects = $role->projects;

        // Lấy hợp đồng cho thuê được gán cho nhóm quyền này
        $rentals = $role->rentals;

        //Lưu nhật ký xem chi tiết nhóm quyền
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'view',
                'roles',
                'Xem chi tiết nhóm quyền: ' . $role->name,
                null,
                $role->toArray()
            );
        }

        return view('roles.show', compact('role', 'permissions', 'rolePermissions', 'projects', 'rentals'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        // Không cho phép chỉnh sửa role hệ thống
        if ($role->is_system) {
            return redirect()->route('roles.show', $role->id)
                ->with('error', 'Không thể chỉnh sửa nhóm quyền hệ thống.');
        }

        // Lấy tất cả quyền của role
        $rolePermissions = $role->permissions()->pluck('permissions.id')->toArray();

        // Lấy tất cả quyền phân theo nhóm
        $permissions = Permission::orderBy('group')
            ->orderBy('display_name')
            ->get()
            ->groupBy('group');

        // Lấy tất cả nhân viên
        $employees = \App\Models\Employee::orderBy('name')->get();

        // Lấy danh sách id nhân viên đang thuộc nhóm quyền này
        $roleEmployees = $role->employees->pluck('id')->toArray();

        // Lấy tất cả dự án (sử dụng project_name thay vì name)
        $projects = \App\Models\Project::orderBy('project_name')->get();

        // Lấy danh sách id dự án đã được gán cho nhóm quyền này
        $roleProjects = $role->projects->pluck('id')->toArray();

        // Lấy tất cả hợp đồng cho thuê (không lọc theo status vì cột không tồn tại)
        $rentals = \App\Models\Rental::orderBy('created_at', 'desc')->get();

        // Lấy danh sách id hợp đồng cho thuê đã được gán cho nhóm quyền này
        $roleRentals = $role->rentals->pluck('id')->toArray();

        return view('roles.edit', compact(
            'role',
            'permissions',
            'rolePermissions',
            'employees',
            'roleEmployees',
            'projects',
            'roleProjects',
            'rentals',
            'roleRentals'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Không cho phép chỉnh sửa role hệ thống
        if ($role->is_system) {
            return redirect()->route('roles.show', $role->id)
                ->with('error', 'Không thể chỉnh sửa nhóm quyền hệ thống.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
            'scope' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'employees' => 'nullable|array',
            'employees.*' => 'exists:employees,id',
            'projects' => 'nullable|array',
            'projects.*' => 'exists:projects,id',
            'rentals' => 'nullable|array',
            'rentals.*' => 'exists:rentals,id',
        ]);

        $oldData = $role->toArray();

        // Chỉ cập nhật is_active nếu có field này trong request
        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'scope' => $request->scope,
        ];

        // Chỉ cập nhật is_active nếu có trong request (tránh tự động vô hiệu hóa)
        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->boolean('is_active');
        }

        // KIỂM TRA TRÙNG LẶP TRƯỚC KHI CẬP NHẬT
        if ($request->has('employees') && $request->has('permissions')) {
            $duplicateWarnings = [];
            $requestPermissions = $request->permissions;

            // Lấy danh sách nhân viên hiện tại của role này
            $currentEmployeeIds = $role->employees->pluck('id')->toArray();

            foreach ($request->employees as $employeeId) {
                // Bỏ qua nhân viên đã thuộc role này
                if (in_array($employeeId, $currentEmployeeIds)) {
                    continue;
                }

                $employee = \App\Models\Employee::find($employeeId);
                if ($employee && $employee->role_id) {
                    // Lấy quyền của role hiện tại của nhân viên
                    $currentRole = Role::find($employee->role_id);
                    if ($currentRole) {
                        $currentPermissions = $currentRole->permissions()->pluck('permissions.id')->toArray();

                        // Tìm quyền trùng lặp
                        $duplicates = array_intersect($requestPermissions, $currentPermissions);

                        if (!empty($duplicates)) {
                            $duplicateWarnings[] = [
                                'employee' => $employee,
                                'current_role' => $currentRole,
                                'duplicate_permissions' => Permission::whereIn('id', $duplicates)->get()
                            ];
                        }
                    }
                }
            }

            if (!empty($duplicateWarnings)) {
                return redirect()->back()
                    ->withInput()
                    ->with('duplicate_warnings', $duplicateWarnings)
                    ->with('warning', 'Phát hiện quyền trùng lặp với một số nhân viên. Vui lòng kiểm tra lại.');
            }
        }

        $role->update($updateData);

        // Gán quyền
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        // Cập nhật nhân viên trong nhóm quyền (đã kiểm tra trùng lặp ở trên)
        // Đầu tiên, bỏ nhóm quyền cho tất cả nhân viên thuộc nhóm quyền này
        \App\Models\Employee::where('role_id', $role->id)->update(['role_id' => null]);

        // Sau đó, gán lại nhóm quyền cho các nhân viên được chọn
        if ($request->has('employees')) {
            \App\Models\Employee::whereIn('id', $request->employees)->update(['role_id' => $role->id]);

            // Ghi nhật ký cho việc cập nhật nhân viên
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'employees',
                    'Cập nhật nhân viên trong nhóm quyền ' . $role->name,
                    ['old_employees' => $role->employees->pluck('id')->toArray()],
                    ['new_employees' => $request->employees]
                );
            }
        }

        // Cập nhật dự án cho nhóm quyền
        $oldProjects = $role->projects->pluck('id')->toArray();
        if ($request->has('projects')) {
            $role->projects()->sync($request->projects);

            // Ghi nhật ký cho việc cập nhật dự án
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'project_role',
                    'Cập nhật dự án cho nhóm quyền ' . $role->name,
                    ['old_projects' => $oldProjects],
                    ['new_projects' => $request->projects]
                );
            }
        } else {
            $role->projects()->detach();

            if (count($oldProjects) > 0 && Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'project_role',
                    'Xóa tất cả dự án khỏi nhóm quyền ' . $role->name,
                    ['old_projects' => $oldProjects],
                    ['new_projects' => []]
                );
            }
        }

        // Cập nhật hợp đồng cho thuê cho nhóm quyền
        $oldRentals = $role->rentals->pluck('id')->toArray();
        if ($request->has('rentals')) {
            $role->rentals()->sync($request->rentals);

            // Ghi nhật ký cho việc cập nhật hợp đồng cho thuê
            if (Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'rental_role',
                    'Cập nhật hợp đồng cho thuê cho nhóm quyền ' . $role->name,
                    ['old_rentals' => $oldRentals],
                    ['new_rentals' => $request->rentals]
                );
            }
        } else {
            $role->rentals()->detach();

            if (count($oldRentals) > 0 && Auth::check()) {
                UserLog::logActivity(
                    Auth::id(),
                    'update',
                    'rental_role',
                    'Xóa tất cả hợp đồng cho thuê khỏi nhóm quyền ' . $role->name,
                    ['old_rentals' => $oldRentals],
                    ['new_rentals' => []]
                );
            }
        }

        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'update',
                'roles',
                'Cập nhật nhóm quyền: ' . $role->name,
                $oldData,
                $role->toArray()
            );
        }

        return redirect()->route('roles.show', $role->id)
            ->with('success', 'Nhóm quyền đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Không cho phép xóa role hệ thống
        if ($role->is_system) {
            return redirect()->route('roles.index')
                ->with('error', 'Không thể xóa nhóm quyền hệ thống.');
        }

        $roleName = $role->name;
        $roleData = $role->toArray();

        // Kiểm tra xem có nhân viên nào đang sử dụng role này không
        $employeesCount = $role->employees()->count();
        if ($employeesCount > 0) {
            $employeeNames = $role->employees()->pluck('name')->take(3)->toArray();
            $employeeList = implode(', ', $employeeNames);
            if ($employeesCount > 3) {
                $employeeList .= ' và ' . ($employeesCount - 3) . ' nhân viên khác';
            }

            return redirect()->route('roles.show', $role->id)
                ->with('error', 'Không thể xóa nhóm quyền này vì đang có ' . $employeesCount . ' nhân viên sử dụng: ' . $employeeList . '. Vui lòng chuyển các nhân viên này sang nhóm quyền khác trước khi xóa.');
        }

        $role->permissions()->detach(); // Xóa các quan hệ với quyền
        $role->delete();

        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'delete',
                'roles',
                'Xóa nhóm quyền: ' . $roleName,
                $roleData,
                null
            );
        }

        return redirect()->route('roles.index')
            ->with('success', 'Nhóm quyền đã được xóa thành công.');
    }

    /**
     * Toggle the status of role
     */
    public function toggleStatus(Role $role)
    {
        // Không cho phép khóa/mở khóa role hệ thống
        if ($role->is_system) {
            return redirect()->route('roles.index')
                ->with('error', 'Không thể thay đổi trạng thái nhóm quyền hệ thống.');
        }

        $oldData = $role->toArray();
        $role->is_active = !$role->is_active;
        $role->save();

        $statusText = $role->is_active ? 'kích hoạt' : 'vô hiệu hóa';

        // Lấy danh sách nhân viên bị ảnh hưởng
        $affectedEmployees = $role->employees()->get();

        // Tạo thông báo cho từng nhân viên bị ảnh hưởng
        foreach ($affectedEmployees as $employee) {
            \App\Models\Notification::createNotification(
                'Thay đổi trạng thái nhóm quyền',
                'Nhóm quyền ' . $role->name . ' đã được ' . $statusText . '. Điều này có thể ảnh hưởng đến quyền truy cập của bạn.',
                'warning',
                $employee->id,
                'role',
                $role->id,
                route('roles.show', $role->id)
            );
        }

        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'update',
                'roles',
                'Thay đổi trạng thái nhóm quyền: ' . $role->name . ' -> ' . $statusText . ' (Ảnh hưởng ' . $affectedEmployees->count() . ' nhân viên)',
                $oldData,
                $role->toArray()
            );
        }

        return redirect()->route('roles.index')
            ->with('success', 'Nhóm quyền đã được ' . $statusText . ' thành công. ' .
                ($affectedEmployees->count() > 0 ? $affectedEmployees->count() . ' nhân viên bị ảnh hưởng.' : ''));
    }
}
