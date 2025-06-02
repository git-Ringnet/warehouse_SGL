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
        
        return view('roles.create', compact('permissions'));
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
        ]);

        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'scope' => $request->scope,
            'is_active' => $request->has('is_active'),
        ]);

        // Gán quyền
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
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
        
        return view('roles.show', compact('role', 'permissions', 'rolePermissions'));
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
        
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
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
        ]);

        $oldData = $role->toArray();
        
        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'scope' => $request->scope,
            'is_active' => $request->has('is_active'),
        ]);

        // Gán quyền
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
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
        if ($role->employees()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Không thể xóa nhóm quyền đang được sử dụng bởi nhân viên.');
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
        
        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'update',
                'roles',
                'Thay đổi trạng thái nhóm quyền: ' . $role->name . ' -> ' . $statusText,
                $oldData,
                $role->toArray()
            );
        }

        return redirect()->route('roles.index')
            ->with('success', 'Nhóm quyền đã được ' . $statusText . ' thành công.');
    }
}
