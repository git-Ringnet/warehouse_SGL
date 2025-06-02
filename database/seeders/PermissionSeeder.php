<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Permission::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $permissions = [
            // Quản lý nhân viên
            $this->createPermission('employees.view', 'Xem danh sách nhân viên', 'Xem thông tin nhân viên trong hệ thống', 'Nhân viên'),
            $this->createPermission('employees.create', 'Thêm nhân viên', 'Tạo nhân viên mới', 'Nhân viên'),
            $this->createPermission('employees.edit', 'Sửa nhân viên', 'Chỉnh sửa thông tin nhân viên', 'Nhân viên'),
            $this->createPermission('employees.delete', 'Xóa nhân viên', 'Xóa nhân viên khỏi hệ thống', 'Nhân viên'),
            
            // Quản lý khách hàng
            $this->createPermission('customers.view', 'Xem danh sách khách hàng', 'Xem thông tin khách hàng', 'Khách hàng'),
            $this->createPermission('customers.create', 'Thêm khách hàng', 'Tạo khách hàng mới', 'Khách hàng'),
            $this->createPermission('customers.edit', 'Sửa khách hàng', 'Chỉnh sửa thông tin khách hàng', 'Khách hàng'),
            $this->createPermission('customers.delete', 'Xóa khách hàng', 'Xóa khách hàng khỏi hệ thống', 'Khách hàng'),
            
            // Quản lý nhà cung cấp
            $this->createPermission('suppliers.view', 'Xem danh sách nhà cung cấp', 'Xem thông tin nhà cung cấp', 'Nhà cung cấp'),
            $this->createPermission('suppliers.create', 'Thêm nhà cung cấp', 'Tạo nhà cung cấp mới', 'Nhà cung cấp'),
            $this->createPermission('suppliers.edit', 'Sửa nhà cung cấp', 'Chỉnh sửa thông tin nhà cung cấp', 'Nhà cung cấp'),
            $this->createPermission('suppliers.delete', 'Xóa nhà cung cấp', 'Xóa nhà cung cấp khỏi hệ thống', 'Nhà cung cấp'),
            
            // Quản lý kho hàng
            $this->createPermission('warehouses.view', 'Xem danh sách kho hàng', 'Xem thông tin kho hàng', 'Kho hàng'),
            $this->createPermission('warehouses.create', 'Thêm kho hàng', 'Tạo kho hàng mới', 'Kho hàng'),
            $this->createPermission('warehouses.edit', 'Sửa kho hàng', 'Chỉnh sửa thông tin kho hàng', 'Kho hàng'),
            $this->createPermission('warehouses.delete', 'Xóa kho hàng', 'Xóa kho hàng khỏi hệ thống', 'Kho hàng'),
            
            // Quản lý vật tư
            $this->createPermission('materials.view', 'Xem danh sách vật tư', 'Xem thông tin vật tư', 'Vật tư'),
            $this->createPermission('materials.create', 'Thêm vật tư', 'Tạo vật tư mới', 'Vật tư'),
            $this->createPermission('materials.edit', 'Sửa vật tư', 'Chỉnh sửa thông tin vật tư', 'Vật tư'),
            $this->createPermission('materials.delete', 'Xóa vật tư', 'Xóa vật tư khỏi hệ thống', 'Vật tư'),
            
            // Quản lý thành phẩm
            $this->createPermission('products.view', 'Xem danh sách thành phẩm', 'Xem thông tin thành phẩm', 'Thành phẩm'),
            $this->createPermission('products.create', 'Thêm thành phẩm', 'Tạo thành phẩm mới', 'Thành phẩm'),
            $this->createPermission('products.edit', 'Sửa thành phẩm', 'Chỉnh sửa thông tin thành phẩm', 'Thành phẩm'),
            $this->createPermission('products.delete', 'Xóa thành phẩm', 'Xóa thành phẩm khỏi hệ thống', 'Thành phẩm'),
            
            // Lắp ráp
            $this->createPermission('assembly.view', 'Xem lắp ráp', 'Xem thông tin lắp ráp', 'Lắp ráp'),
            $this->createPermission('assembly.create', 'Thêm lắp ráp', 'Tạo lắp ráp mới', 'Lắp ráp'),
            $this->createPermission('assembly.edit', 'Sửa lắp ráp', 'Chỉnh sửa thông tin lắp ráp', 'Lắp ráp'),
            $this->createPermission('assembly.delete', 'Xóa lắp ráp', 'Xóa lắp ráp khỏi hệ thống', 'Lắp ráp'),
            
            // Kiểm thử
            $this->createPermission('testing.view', 'Xem kiểm thử', 'Xem thông tin kiểm thử', 'Kiểm thử'),
            $this->createPermission('testing.create', 'Thêm kiểm thử', 'Tạo kiểm thử mới', 'Kiểm thử'),
            $this->createPermission('testing.edit', 'Sửa kiểm thử', 'Chỉnh sửa thông tin kiểm thử', 'Kiểm thử'),
            $this->createPermission('testing.delete', 'Xóa kiểm thử', 'Xóa kiểm thử khỏi hệ thống', 'Kiểm thử'),
            
            // Nhập kho
            $this->createPermission('imports.view', 'Xem nhập kho', 'Xem thông tin nhập kho', 'Nhập kho'),
            $this->createPermission('imports.create', 'Thêm nhập kho', 'Tạo nhập kho mới', 'Nhập kho'),
            $this->createPermission('imports.edit', 'Sửa nhập kho', 'Chỉnh sửa thông tin nhập kho', 'Nhập kho'),
            $this->createPermission('imports.delete', 'Xóa nhập kho', 'Xóa nhập kho khỏi hệ thống', 'Nhập kho'),
            
            // Xuất kho
            $this->createPermission('exports.view', 'Xem xuất kho', 'Xem thông tin xuất kho', 'Xuất kho'),
            $this->createPermission('exports.create', 'Thêm xuất kho', 'Tạo xuất kho mới', 'Xuất kho'),
            $this->createPermission('exports.edit', 'Sửa xuất kho', 'Chỉnh sửa thông tin xuất kho', 'Xuất kho'),
            $this->createPermission('exports.delete', 'Xóa xuất kho', 'Xóa xuất kho khỏi hệ thống', 'Xuất kho'),
            
            // Chuyển kho
            $this->createPermission('transfers.view', 'Xem chuyển kho', 'Xem thông tin chuyển kho', 'Chuyển kho'),
            $this->createPermission('transfers.create', 'Thêm chuyển kho', 'Tạo chuyển kho mới', 'Chuyển kho'),
            $this->createPermission('transfers.edit', 'Sửa chuyển kho', 'Chỉnh sửa thông tin chuyển kho', 'Chuyển kho'),
            $this->createPermission('transfers.delete', 'Xóa chuyển kho', 'Xóa chuyển kho khỏi hệ thống', 'Chuyển kho'),
            
            // Sửa chữa - bảo hành
            $this->createPermission('repairs.view', 'Xem sửa chữa', 'Xem thông tin sửa chữa', 'Sửa chữa'),
            $this->createPermission('repairs.create', 'Thêm sửa chữa', 'Tạo phiếu sửa chữa mới', 'Sửa chữa'),
            $this->createPermission('repairs.edit', 'Sửa phiếu sửa chữa', 'Chỉnh sửa thông tin phiếu sửa chữa', 'Sửa chữa'),
            $this->createPermission('repairs.delete', 'Xóa phiếu sửa chữa', 'Xóa phiếu sửa chữa khỏi hệ thống', 'Sửa chữa'),
            
            // Bảo hành điện tử
            $this->createPermission('warranties.view', 'Xem bảo hành', 'Xem thông tin bảo hành', 'Bảo hành'),
            $this->createPermission('warranties.create', 'Thêm bảo hành', 'Tạo bảo hành mới', 'Bảo hành'),
            $this->createPermission('warranties.edit', 'Sửa bảo hành', 'Chỉnh sửa thông tin bảo hành', 'Bảo hành'),
            $this->createPermission('warranties.delete', 'Xóa bảo hành', 'Xóa bảo hành khỏi hệ thống', 'Bảo hành'),
            
            // Dự án
            $this->createPermission('projects.view', 'Xem dự án', 'Xem thông tin dự án', 'Dự án'),
            $this->createPermission('projects.create', 'Thêm dự án', 'Tạo dự án mới', 'Dự án'),
            $this->createPermission('projects.edit', 'Sửa dự án', 'Chỉnh sửa thông tin dự án', 'Dự án'),
            $this->createPermission('projects.delete', 'Xóa dự án', 'Xóa dự án khỏi hệ thống', 'Dự án'),
            
            // Cho thuê
            $this->createPermission('rentals.view', 'Xem cho thuê', 'Xem thông tin cho thuê', 'Cho thuê'),
            $this->createPermission('rentals.create', 'Thêm cho thuê', 'Tạo cho thuê mới', 'Cho thuê'),
            $this->createPermission('rentals.edit', 'Sửa cho thuê', 'Chỉnh sửa thông tin cho thuê', 'Cho thuê'),
            $this->createPermission('rentals.delete', 'Xóa cho thuê', 'Xóa cho thuê khỏi hệ thống', 'Cho thuê'),
            
            // Báo cáo
            $this->createPermission('reports.view', 'Xem báo cáo', 'Xem các báo cáo trong hệ thống', 'Báo cáo'),
            $this->createPermission('reports.export', 'Xuất báo cáo', 'Xuất báo cáo ra file', 'Báo cáo'),
            
            // Phân quyền
            $this->createPermission('roles.view', 'Xem nhóm quyền', 'Xem danh sách nhóm quyền', 'Phân quyền'),
            $this->createPermission('roles.create', 'Thêm nhóm quyền', 'Tạo nhóm quyền mới', 'Phân quyền'),
            $this->createPermission('roles.edit', 'Sửa nhóm quyền', 'Chỉnh sửa nhóm quyền', 'Phân quyền'),
            $this->createPermission('roles.delete', 'Xóa nhóm quyền', 'Xóa nhóm quyền khỏi hệ thống', 'Phân quyền'),
            
            $this->createPermission('permissions.view', 'Xem quyền', 'Xem danh sách quyền', 'Phân quyền'),
            $this->createPermission('permissions.manage', 'Quản lý quyền', 'Thêm/sửa/xóa quyền', 'Phân quyền'),
            
            $this->createPermission('user-logs.view', 'Xem nhật ký người dùng', 'Xem nhật ký hoạt động người dùng', 'Phân quyền'),
            $this->createPermission('user-logs.export', 'Xuất nhật ký người dùng', 'Xuất nhật ký người dùng ra file', 'Phân quyền'),
            $this->createPermission('user-logs.filter', 'Lọc nhật ký', 'Tìm kiếm và lọc nhật ký theo nhiều tiêu chí', 'Phân quyền'),
            
            // Quản lý phạm vi quyền
            $this->createPermission('scope.assign', 'Gán phạm vi quyền', 'Gán phạm vi quyền cho người dùng', 'Phạm vi quyền'),
            $this->createPermission('scope.warehouse', 'Quản lý theo kho', 'Quản lý theo phạm vi kho hàng', 'Phạm vi quyền'),
            $this->createPermission('scope.project', 'Quản lý theo dự án', 'Quản lý theo phạm vi dự án', 'Phạm vi quyền'),
            $this->createPermission('scope.region', 'Quản lý theo địa bàn', 'Quản lý theo phạm vi địa bàn (Tỉnh/Huyện/Xã)', 'Phạm vi quyền'),
            
            // Phần mềm
            $this->createPermission('software.view', 'Xem phần mềm', 'Xem danh sách phần mềm và license', 'Phần mềm'),
            $this->createPermission('software.create', 'Thêm phần mềm', 'Tạo phần mềm/license mới', 'Phần mềm'),
            $this->createPermission('software.edit', 'Sửa phần mềm', 'Chỉnh sửa thông tin phần mềm/license', 'Phần mềm'),
            $this->createPermission('software.delete', 'Xóa phần mềm', 'Xóa phần mềm/license khỏi hệ thống', 'Phần mềm'),
            $this->createPermission('software.download', 'Tải phần mềm', 'Tải phần mềm/license từ hệ thống', 'Phần mềm'),
            
            // Phiếu yêu cầu
            $this->createPermission('requests.view', 'Xem phiếu yêu cầu', 'Xem danh sách phiếu yêu cầu', 'Phiếu yêu cầu'),
            $this->createPermission('requests.create', 'Tạo phiếu yêu cầu', 'Tạo phiếu yêu cầu mới', 'Phiếu yêu cầu'),
            $this->createPermission('requests.edit', 'Sửa phiếu yêu cầu', 'Chỉnh sửa phiếu yêu cầu', 'Phiếu yêu cầu'),
            $this->createPermission('requests.delete', 'Xóa phiếu yêu cầu', 'Xóa phiếu yêu cầu', 'Phiếu yêu cầu'),
            $this->createPermission('requests.approve', 'Duyệt phiếu yêu cầu', 'Phê duyệt phiếu yêu cầu', 'Phiếu yêu cầu'),
            $this->createPermission('requests.reject', 'Từ chối phiếu yêu cầu', 'Từ chối phiếu yêu cầu', 'Phiếu yêu cầu'),
            
            // Chi tiết báo cáo
            $this->createPermission('reports.inventory', 'Xem báo cáo tồn kho', 'Xem báo cáo tồn kho của hệ thống', 'Báo cáo'),
            $this->createPermission('reports.operations', 'Xem báo cáo vận hành', 'Xem báo cáo vận hành của hệ thống', 'Báo cáo'),
            $this->createPermission('reports.projects', 'Xem báo cáo dự án', 'Xem báo cáo dự án của hệ thống', 'Báo cáo'),
        ];
        
        // Thêm vào database
        Permission::insert($permissions);
    }
    
    /**
     * Tạo mảng dữ liệu cho một quyền
     */
    private function createPermission($name, $displayName, $description, $group)
    {
        return [
            'name' => $name,
            'display_name' => $displayName,
            'description' => $description,
            'group' => $group,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
