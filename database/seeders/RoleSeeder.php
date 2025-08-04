<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('role_permission')->truncate();
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Tạo các nhóm quyền mặc định
        $roles = [
            // Super Admin - quản trị toàn bộ hệ thống
            [
                'name' => 'Super Admin',
                'description' => 'Quản trị viên cao cấp, có toàn quyền trong hệ thống',
                'scope' => null,
                'is_active' => true,
                'is_system' => true,
            ],

            // Kho sản xuất
            [
                'name' => 'Kho Sản Xuất',
                'description' => 'Nhóm quản lý thiết bị thuộc kho sản xuất',
                'scope' => 'warehouse',
                'is_active' => true,
                'is_system' => false,
            ],

            // Kho thành phẩm
            [
                'name' => 'Kho Thành Phẩm',
                'description' => 'Nhóm quản lý thiết bị thành phẩm',
                'scope' => 'warehouse',
                'is_active' => true,
                'is_system' => false,
            ],

            // Kho bảo hành
            [
                'name' => 'Kho Bảo Hành',
                'description' => 'Nhóm quản lý bảo hành thiết bị',
                'scope' => 'warehouse',
                'is_active' => true,
                'is_system' => false,
            ],

            // Kho phần mềm
            [
                'name' => 'Kho Phần Mềm',
                'description' => 'Nhóm quản lý license, phần mềm, mã kích hoạt',
                'scope' => 'warehouse',
                'is_active' => true,
                'is_system' => false,
            ],

            // Quản lý dự án
            [
                'name' => 'Quản Lý Dự Án',
                'description' => 'Nhóm quản lý thiết bị theo địa bàn dự án',
                'scope' => 'project',
                'is_active' => true,
                'is_system' => false,
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::create($roleData);

            // Gán tất cả quyền cho Super Admin
            if ($role->name === 'Super Admin') {
                $permissions = Permission::all();
                $role->permissions()->attach($permissions->pluck('id')->toArray());
            }

            // Gán quyền cho các vai trò khác theo scope
            else {
                $this->assignDefaultPermissions($role);
            }
        }
    }

    /**
     * Gán các quyền mặc định cho từng nhóm quyền
     */
    private function assignDefaultPermissions(Role $role)
    {
        $permissionsToAssign = [];

        // Các quyền chung cho tất cả các nhóm
        $commonPermissions = [
            // Nhân viên - chỉ xem
            'employees.view',

            // Khách hàng - các quyền CRUD
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',

            // Nhà cung cấp - các quyền CRUD
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',

            // Báo cáo - xem dashboard và báo cáo chi tiết
            'reports.overview',    // Xem dashboard thống kê
            'reports.inventory',   // Xem báo cáo xuất nhập tồn chi tiết
            'reports.export',      // Xuất file báo cáo

            // Phiếu yêu cầu - quyền chung
            'requests.view',
            'requests.view_detail',
            'requests.export',
            'requests.copy',

            // Phiếu đề xuất dự án
            'requests.project.create',
            'requests.project.edit',
            'requests.project.delete',
            'requests.project.approve',
            'requests.project.reject',

            // Phiếu bảo trì dự án
            'requests.maintenance.create',
            'requests.maintenance.edit',
            'requests.maintenance.delete',
            'requests.maintenance.approve',
            'requests.maintenance.reject',

            // Phiếu bảo trì của khách hàng
            'requests.customer-maintenance.create',
            'requests.customer-maintenance.edit',
            'requests.customer-maintenance.delete',
            'requests.customer-maintenance.approve',
            'requests.customer-maintenance.reject',
        ];

        // Các quyền theo scope
        switch ($role->scope) {
            case 'warehouse':
                $warehousePermissions = [
                    // Kho hàng - xem
                    'warehouses.view',
                    'warehouses.view_detail',
                    'warehouses.export',

                    // Vật tư - các quyền CRUD
                    'materials.view',
                    'materials.create',
                    'materials.edit',
                    'materials.delete',
                    'materials.view_detail',
                    'materials.export',

                    // Hàng hóa - các quyền CRUD
                    'goods.view',
                    'goods.create',
                    'goods.edit',
                    'goods.delete',
                    'goods.view_detail',
                    'goods.export',

                    // Thành phẩm - các quyền CRUD
                    'products.view',
                    'products.create',
                    'products.edit',
                    'products.delete',
                    'products.view_detail',
                    'products.export',

                    // Nhập kho - các quyền CRUD
                    'inventory_imports.view',
                    'inventory_imports.create',
                    'inventory_imports.view_detail',
                    'inventory_imports.edit',
                    'inventory_imports.delete',

                    // Xuất kho - các quyền CRUD
                    'inventory.view',
                    'inventory.view_detail',
                    'inventory.create',
                    'inventory.edit',
                    'inventory.delete',
                    'inventory.approve',
                    'inventory.reject',

                    // Chuyển kho - các quyền CRUD
                    'warehouse-transfers.view',
                    'warehouse-transfers.view_detail',
                    'warehouse-transfers.create',
                    'warehouse-transfers.edit',
                    'warehouse-transfers.delete',

                    // Báo cáo tồn kho
                    'reports.inventory',

                    // Phạm vi quyền
                    'scope.warehouse',
                ];

                $permissionsToAssign = array_merge($commonPermissions, $warehousePermissions);

                // Thêm quyền đặc biệt theo tên nhóm
                if ($role->name === 'Kho Sản Xuất') {
                    $permissionsToAssign = array_merge($permissionsToAssign, [
                        'assembly.view',
                        'assembly.view_detail',
                        'assembly.create',
                        'assembly.edit',
                        'assembly.delete',
                        'assembly.export',

                        'testing.view',
                        'testing.view_detail',
                        'testing.create',
                        'testing.edit',
                        'testing.delete',

                        'testing.receive',
                        'testing.complete',
                        'testing.update_inventory',
                        'testing.print',
                    ]);
                }

                if ($role->name === 'Kho Bảo Hành') {
                    $permissionsToAssign = array_merge($permissionsToAssign, [
                        'repairs.view',
                        'repairs.view_detail',
                        'repairs.create',
                        'repairs.edit',
                        'repairs.delete',

                        'warranties.view',
                        'warranties.view_detail',
                    ]);
                }

                if ($role->name === 'Kho Phần Mềm') {
                    $permissionsToAssign = array_merge($permissionsToAssign, [
                        'software.view',
                        'software.view_detail',
                        'software.create',
                        'software.edit',
                        'software.delete',
                        'software.download',
                    ]);
                }

                break;

            case 'project':
                $projectPermissions = [
                    // Dự án - các quyền CRUD
                    'projects.view',
                    'projects.view_detail',
                    'projects.create',
                    'projects.edit',
                    'projects.delete',

                    // Cho thuê - các quyền CRUD
                    'rentals.view',
                    'rentals.view_detail',
                    'rentals.create',
                    'rentals.edit',
                    'rentals.delete',

                    // Kho hàng - xem
                    'warehouses.view',

                    // Vật tư, thành phẩm - chỉ xem
                    'materials.view',
                    'products.view',

                    // Nhập xuất kho - chỉ xem
                    'imports.view',
                    'exports.view',
                    'transfers.view',

                    // Báo cáo dự án
                    'reports.overview',

                    // Phạm vi quyền
                    'scope.project',
                    'scope.region',


                ];

                $permissionsToAssign = array_merge($commonPermissions, $projectPermissions);
                break;
        }

        // Gán quyền
        $permissions = Permission::whereIn('name', $permissionsToAssign)->get();
        $role->permissions()->attach($permissions->pluck('id')->toArray());
    }
}
