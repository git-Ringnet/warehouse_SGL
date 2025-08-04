<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm quyền mới "Hoàn thành sửa chữa" trong Bảo trì & Sửa chữa
        $this->createPermissionIfNotExists('repairs.complete', 'Hoàn thành sửa chữa', 'Đánh dấu hoàn thành phiếu sửa chữa', 'Bảo trì & Sửa chữa');

        // 2. Thêm quyền mới trong Sản xuất & Kiểm thử
        $this->createPermissionIfNotExists('assembly.approve', 'Duyệt lắp ráp', 'Duyệt phiếu lắp ráp', 'Sản xuất & Kiểm thử');
        $this->createPermissionIfNotExists('assembly.cancel', 'Huỷ lắp ráp', 'Huỷ phiếu lắp ráp', 'Sản xuất & Kiểm thử');

        // 3. Thêm quyền mới trong Vận hành kho
        $this->createPermissionIfNotExists('inventory_imports.approve', 'Duyệt nhập kho', 'Duyệt phiếu nhập kho', 'Vận hành kho');
        $this->createPermissionIfNotExists('inventory_imports.cancel', 'Huỷ nhập kho', 'Huỷ phiếu nhập kho', 'Vận hành kho');
        $this->createPermissionIfNotExists('warehouse-transfers.approve', 'Duyệt chuyển kho', 'Duyệt phiếu chuyển kho', 'Vận hành kho');

        // 4. Xóa các quyền không cần thiết trong Phiếu yêu cầu
        $permissionsToDelete = [
            'requests.update_status', // Cập nhật trạng thái
            'requests.delete', // Xoá phiếu yêu cầu
            'requests.approve', // Duyệt phiếu yêu cầu
            'requests.edit', // Sửa phiếu yêu cầu
            'requests.reject', // Từ chối phiếu yêu cầu
        ];

        foreach ($permissionsToDelete as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                // Xóa khỏi bảng role_permission trước
                DB::table('role_permission')->where('permission_id', $permission->id)->delete();
                // Xóa permission
                $permission->delete();
            }
        }

        // 5. Xóa các quyền không cần thiết trong Sản xuất & Kiểm thử
        $testingPermissionsToDelete = [
            'testing.approve', // Duyệt kiểm thử
            'testing.reject', // Từ chối kiểm thử
        ];

        foreach ($testingPermissionsToDelete as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                // Xóa khỏi bảng role_permission trước
                DB::table('role_permission')->where('permission_id', $permission->id)->delete();
                // Xóa permission
                $permission->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa các quyền mới đã thêm
        $newPermissionsToDelete = [
            'repairs.complete',
            'assembly.approve',
            'assembly.cancel',
            'inventory_imports.approve',
            'inventory_imports.cancel',
            'warehouse-transfers.approve',
        ];

        foreach ($newPermissionsToDelete as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                // Xóa khỏi bảng role_permission trước
                DB::table('role_permission')->where('permission_id', $permission->id)->delete();
                // Xóa permission
                $permission->delete();
            }
        }

        // Khôi phục các quyền đã xóa
        $restoredPermissions = [
            ['name' => 'requests.update_status', 'display_name' => 'Cập nhật trạng thái', 'description' => 'Cập nhật trạng thái phiếu yêu cầu', 'group' => 'Phiếu yêu cầu'],
            ['name' => 'requests.delete', 'display_name' => 'Xóa phiếu yêu cầu', 'description' => 'Xóa phiếu yêu cầu', 'group' => 'Phiếu yêu cầu'],
            ['name' => 'requests.approve', 'display_name' => 'Duyệt phiếu yêu cầu', 'description' => 'Phê duyệt phiếu yêu cầu', 'group' => 'Phiếu yêu cầu'],
            ['name' => 'requests.edit', 'display_name' => 'Sửa phiếu yêu cầu', 'description' => 'Chỉnh sửa phiếu yêu cầu', 'group' => 'Phiếu yêu cầu'],
            ['name' => 'requests.reject', 'display_name' => 'Từ chối phiếu yêu cầu', 'description' => 'Từ chối phiếu yêu cầu', 'group' => 'Phiếu yêu cầu'],
            ['name' => 'testing.approve', 'display_name' => 'Duyệt kiểm thử', 'description' => 'Duyệt phiếu kiểm thử', 'group' => 'Sản xuất & Kiểm thử'],
            ['name' => 'testing.reject', 'display_name' => 'Từ chối kiểm thử', 'description' => 'Từ chối phiếu kiểm thử', 'group' => 'Sản xuất & Kiểm thử'],
        ];

        foreach ($restoredPermissions as $permissionData) {
            if (!Permission::where('name', $permissionData['name'])->exists()) {
                Permission::create($permissionData);
            }
        }
    }

    /**
     * Tạo permission nếu chưa tồn tại
     */
    private function createPermissionIfNotExists($name, $displayName, $description, $group)
    {
        if (!Permission::where('name', $name)->exists()) {
            Permission::create([
                'name' => $name,
                'display_name' => $displayName,
                'description' => $description,
                'group' => $group,
            ]);
        }
    }
};
