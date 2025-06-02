<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $group = $request->input('group');
        
        $query = Permission::query();
        
        // Lọc theo nhóm
        if ($group) {
            $query->where('group', $group);
        }
        
        // Xử lý tìm kiếm
        if ($search) {
            if ($filter) {
                // Tìm kiếm theo trường cụ thể
                $query->where($filter, 'like', "%{$search}%");
            } else {
                // Tìm kiếm tổng quát
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('display_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
        }
        
        $permissions = $query->orderBy('group')
                           ->orderBy('display_name')
                           ->paginate(15);
        
        // Lấy danh sách các nhóm quyền
        $groups = Permission::select('group')->distinct()->orderBy('group')->pluck('group');
        
        return view('permissions.index', compact('permissions', 'search', 'filter', 'group', 'groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Lấy danh sách các nhóm quyền
        $groups = Permission::select('group')->distinct()->orderBy('group')->pluck('group');
        
        return view('permissions.create', compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name|regex:/^[a-zA-Z0-9\-_.]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'group' => 'required|string|max:255',
        ], [
            'name.regex' => 'Tên quyền chỉ được chứa chữ cái, số, gạch ngang, gạch dưới và dấu chấm.',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'group' => $request->group,
        ]);
        
        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'create',
                'permissions',
                'Tạo quyền: ' . $permission->name,
                null,
                $permission->toArray()
            );
        }

        return redirect()->route('permissions.index')
            ->with('success', 'Quyền đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        // Lấy danh sách các vai trò (role) có quyền này
        $roles = $permission->roles()->paginate(10);
        
        return view('permissions.show', compact('permission', 'roles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        // Lấy danh sách các nhóm quyền
        $groups = Permission::select('group')->distinct()->orderBy('group')->pluck('group');
        
        return view('permissions.edit', compact('permission', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id . '|regex:/^[a-zA-Z0-9\-_.]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'group' => 'required|string|max:255',
        ], [
            'name.regex' => 'Tên quyền chỉ được chứa chữ cái, số, gạch ngang, gạch dưới và dấu chấm.',
        ]);

        $oldData = $permission->toArray();
        
        $permission->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'group' => $request->group,
        ]);
        
        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'update',
                'permissions',
                'Cập nhật quyền: ' . $permission->name,
                $oldData,
                $permission->toArray()
            );
        }

        return redirect()->route('permissions.show', $permission->id)
            ->with('success', 'Quyền đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        // Kiểm tra xem quyền này có đang được sử dụng bởi bất kỳ vai trò nào không
        if ($permission->roles()->count() > 0) {
            return redirect()->route('permissions.index')
                ->with('error', 'Không thể xóa quyền đang được sử dụng bởi các nhóm quyền.');
        }

        $permissionName = $permission->name;
        $permissionData = $permission->toArray();
        
        $permission->delete();
        
        // Ghi nhật ký
        if (Auth::check()) {
            UserLog::logActivity(
                Auth::id(),
                'delete',
                'permissions',
                'Xóa quyền: ' . $permissionName,
                $permissionData,
                null
            );
        }

        return redirect()->route('permissions.index')
            ->with('success', 'Quyền đã được xóa thành công.');
    }
}
