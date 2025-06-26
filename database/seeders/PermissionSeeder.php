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
            // ===== QUẢN LÝ HỆ THỐNG =====
            // Quản lý nhân viên
            $this->createPermission('employees.view', 'Xem danh sách nhân viên', 'Xem thông tin nhân viên trong hệ thống', 'Quản lý hệ thống'),
            $this->createPermission('employees.create', 'Thêm nhân viên', 'Tạo nhân viên mới', 'Quản lý hệ thống'),
            $this->createPermission('employees.edit', 'Sửa nhân viên', 'Chỉnh sửa thông tin nhân viên', 'Quản lý hệ thống'),
            $this->createPermission('employees.delete', 'Xóa nhân viên', 'Xóa nhân viên khỏi hệ thống', 'Quản lý hệ thống'),
            
            // Quản lý khách hàng
            $this->createPermission('customers.view', 'Xem danh sách khách hàng', 'Xem thông tin khách hàng', 'Quản lý hệ thống'),
            $this->createPermission('customers.create', 'Thêm khách hàng', 'Tạo khách hàng mới', 'Quản lý hệ thống'),
            $this->createPermission('customers.edit', 'Sửa khách hàng', 'Chỉnh sửa thông tin khách hàng', 'Quản lý hệ thống'),
            $this->createPermission('customers.delete', 'Xóa khách hàng', 'Xóa khách hàng khỏi hệ thống', 'Quản lý hệ thống'),
            
            // Quản lý nhà cung cấp
            $this->createPermission('suppliers.view', 'Xem danh sách nhà cung cấp', 'Xem thông tin nhà cung cấp', 'Quản lý hệ thống'),
            $this->createPermission('suppliers.create', 'Thêm nhà cung cấp', 'Tạo nhà cung cấp mới', 'Quản lý hệ thống'),
            $this->createPermission('suppliers.edit', 'Sửa nhà cung cấp', 'Chỉnh sửa thông tin nhà cung cấp', 'Quản lý hệ thống'),
            $this->createPermission('suppliers.delete', 'Xóa nhà cung cấp', 'Xóa nhà cung cấp khỏi hệ thống', 'Quản lý hệ thống'),
            
            // Quản lý kho hàng
            $this->createPermission('warehouses.view', 'Xem danh sách kho hàng', 'Xem thông tin kho hàng', 'Quản lý hệ thống'),
            $this->createPermission('warehouses.create', 'Thêm kho hàng', 'Tạo kho hàng mới', 'Quản lý hệ thống'),
            $this->createPermission('warehouses.edit', 'Sửa kho hàng', 'Chỉnh sửa thông tin kho hàng', 'Quản lý hệ thống'),
            $this->createPermission('warehouses.delete', 'Xóa kho hàng', 'Xóa kho hàng khỏi hệ thống', 'Quản lý hệ thống'),
            
            // ===== QUẢN LÝ TÀI SẢN =====
            // Quản lý vật tư
            $this->createPermission('materials.view', 'Xem danh sách vật tư', 'Xem thông tin vật tư', 'Quản lý tài sản'),
            $this->createPermission('materials.create', 'Thêm vật tư', 'Tạo vật tư mới', 'Quản lý tài sản'),
            $this->createPermission('materials.edit', 'Sửa vật tư', 'Chỉnh sửa thông tin vật tư', 'Quản lý tài sản'),
            $this->createPermission('materials.delete', 'Xóa vật tư', 'Xóa vật tư khỏi hệ thống', 'Quản lý tài sản'),
            
            // Quản lý thành phẩm
            $this->createPermission('products.view', 'Xem danh sách thành phẩm', 'Xem thông tin thành phẩm', 'Quản lý tài sản'),
            $this->createPermission('products.create', 'Thêm thành phẩm', 'Tạo thành phẩm mới', 'Quản lý tài sản'),
            $this->createPermission('products.edit', 'Sửa thành phẩm', 'Chỉnh sửa thông tin thành phẩm', 'Quản lý tài sản'),
            $this->createPermission('products.delete', 'Xóa thành phẩm', 'Xóa thành phẩm khỏi hệ thống', 'Quản lý tài sản'),
            
            // Quản lý hàng hóa
            $this->createPermission('goods.view', 'Xem danh sách hàng hóa', 'Xem thông tin hàng hóa', 'Quản lý tài sản'),
            $this->createPermission('goods.create', 'Thêm hàng hóa', 'Tạo hàng hóa mới', 'Quản lý tài sản'),
            $this->createPermission('goods.edit', 'Sửa hàng hóa', 'Chỉnh sửa thông tin hàng hóa', 'Quản lý tài sản'),
            $this->createPermission('goods.delete', 'Xóa hàng hóa', 'Xóa hàng hóa khỏi hệ thống', 'Quản lý tài sản'),
            
            // ===== VẬN HÀNH KHO =====
            // Nhập kho
            $this->createPermission('imports.view', 'Xem nhập kho', 'Xem thông tin nhập kho', 'Vận hành kho'),
            $this->createPermission('imports.create', 'Thêm nhập kho', 'Tạo nhập kho mới', 'Vận hành kho'),
            $this->createPermission('imports.edit', 'Sửa nhập kho', 'Chỉnh sửa thông tin nhập kho', 'Vận hành kho'),
            $this->createPermission('imports.delete', 'Xóa nhập kho', 'Xóa nhập kho khỏi hệ thống', 'Vận hành kho'),
            
            // Xuất kho
            $this->createPermission('exports.view', 'Xem xuất kho', 'Xem thông tin xuất kho', 'Vận hành kho'),
            $this->createPermission('exports.create', 'Thêm xuất kho', 'Tạo xuất kho mới', 'Vận hành kho'),
            $this->createPermission('exports.edit', 'Sửa xuất kho', 'Chỉnh sửa thông tin xuất kho', 'Vận hành kho'),
            $this->createPermission('exports.delete', 'Xóa xuất kho', 'Xóa xuất kho khỏi hệ thống', 'Vận hành kho'),
            
            // Chuyển kho
            $this->createPermission('transfers.view', 'Xem chuyển kho', 'Xem thông tin chuyển kho', 'Vận hành kho'),
            $this->createPermission('transfers.create', 'Thêm chuyển kho', 'Tạo chuyển kho mới', 'Vận hành kho'),
            $this->createPermission('transfers.edit', 'Sửa chuyển kho', 'Chỉnh sửa thông tin chuyển kho', 'Vận hành kho'),
            $this->createPermission('transfers.delete', 'Xóa chuyển kho', 'Xóa chuyển kho khỏi hệ thống', 'Vận hành kho'),
            
            // ===== SẢN XUẤT & KIỂM THỬ =====
            // Lắp ráp
            $this->createPermission('assembly.view', 'Xem lắp ráp', 'Xem thông tin lắp ráp', 'Sản xuất & Kiểm thử'),
            $this->createPermission('assembly.create', 'Thêm lắp ráp', 'Tạo lắp ráp mới', 'Sản xuất & Kiểm thử'),
            $this->createPermission('assembly.edit', 'Sửa lắp ráp', 'Chỉnh sửa thông tin lắp ráp', 'Sản xuất & Kiểm thử'),
            $this->createPermission('assembly.delete', 'Xóa lắp ráp', 'Xóa lắp ráp khỏi hệ thống', 'Sản xuất & Kiểm thử'),
            
            // Kiểm thử
            $this->createPermission('testing.view', 'Xem kiểm thử', 'Xem thông tin kiểm thử', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.create', 'Thêm kiểm thử', 'Tạo kiểm thử mới', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.edit', 'Sửa kiểm thử', 'Chỉnh sửa thông tin kiểm thử', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.delete', 'Xóa kiểm thử', 'Xóa kiểm thử khỏi hệ thống', 'Sản xuất & Kiểm thử'),
            
            // ===== BẢO TRÌ & SỬA CHỮA =====
            // Sửa chữa
            $this->createPermission('repairs.view', 'Xem sửa chữa', 'Xem thông tin sửa chữa', 'Bảo trì & Sửa chữa'),
            $this->createPermission('repairs.create', 'Thêm sửa chữa', 'Tạo phiếu sửa chữa mới', 'Bảo trì & Sửa chữa'),
            $this->createPermission('repairs.edit', 'Sửa phiếu sửa chữa', 'Chỉnh sửa thông tin phiếu sửa chữa', 'Bảo trì & Sửa chữa'),
            $this->createPermission('repairs.delete', 'Xóa phiếu sửa chữa', 'Xóa phiếu sửa chữa khỏi hệ thống', 'Bảo trì & Sửa chữa'),
            
            // Bảo hành điện tử
            $this->createPermission('warranties.view', 'Xem bảo hành', 'Xem thông tin bảo hành', 'Bảo trì & Sửa chữa'),
            $this->createPermission('warranties.create', 'Thêm bảo hành', 'Tạo bảo hành mới', 'Bảo trì & Sửa chữa'),
            $this->createPermission('warranties.edit', 'Sửa bảo hành', 'Chỉnh sửa thông tin bảo hành', 'Bảo trì & Sửa chữa'),
            $this->createPermission('warranties.delete', 'Xóa bảo hành', 'Xóa bảo hành khỏi hệ thống', 'Bảo trì & Sửa chữa'),
            
            // ===== QUẢN LÝ DỰ ÁN =====
            // Dự án
            $this->createPermission('projects.view', 'Xem dự án', 'Xem thông tin dự án', 'Quản lý dự án'),
            $this->createPermission('projects.create', 'Thêm dự án', 'Tạo dự án mới', 'Quản lý dự án'),
            $this->createPermission('projects.edit', 'Sửa dự án', 'Chỉnh sửa thông tin dự án', 'Quản lý dự án'),
            $this->createPermission('projects.delete', 'Xóa dự án', 'Xóa dự án khỏi hệ thống', 'Quản lý dự án'),
            
            // Cho thuê
            $this->createPermission('rentals.view', 'Xem cho thuê', 'Xem thông tin cho thuê', 'Quản lý dự án'),
            $this->createPermission('rentals.create', 'Thêm cho thuê', 'Tạo cho thuê mới', 'Quản lý dự án'),
            $this->createPermission('rentals.edit', 'Sửa cho thuê', 'Chỉnh sửa thông tin cho thuê', 'Quản lý dự án'),
            $this->createPermission('rentals.delete', 'Xóa cho thuê', 'Xóa cho thuê khỏi hệ thống', 'Quản lý dự án'),
            
            // ===== PHIẾU YÊU CẦU =====
            // Phiếu yêu cầu
            $this->createPermission('requests.view', 'Xem phiếu yêu cầu', 'Xem danh sách phiếu yêu cầu', 'Phiếu yêu cầu'),
            $this->createPermission('requests.create', 'Tạo phiếu yêu cầu', 'Tạo phiếu yêu cầu mới', 'Phiếu yêu cầu'),
            $this->createPermission('requests.edit', 'Sửa phiếu yêu cầu', 'Chỉnh sửa phiếu yêu cầu', 'Phiếu yêu cầu'),
            $this->createPermission('requests.delete', 'Xóa phiếu yêu cầu', 'Xóa phiếu yêu cầu', 'Phiếu yêu cầu'),
            $this->createPermission('requests.approve', 'Duyệt phiếu yêu cầu', 'Phê duyệt phiếu yêu cầu', 'Phiếu yêu cầu'),
            $this->createPermission('requests.reject', 'Từ chối phiếu yêu cầu', 'Từ chối phiếu yêu cầu', 'Phiếu yêu cầu'),
            
            // ===== PHẦN MỀM & LICENSE =====
            // Phần mềm
            $this->createPermission('software.view', 'Xem phần mềm', 'Xem danh sách phần mềm và license', 'Phần mềm & License'),
            $this->createPermission('software.create', 'Thêm phần mềm', 'Tạo phần mềm/license mới', 'Phần mềm & License'),
            $this->createPermission('software.edit', 'Sửa phần mềm', 'Chỉnh sửa thông tin phần mềm/license', 'Phần mềm & License'),
            $this->createPermission('software.delete', 'Xóa phần mềm', 'Xóa phần mềm/license khỏi hệ thống', 'Phần mềm & License'),
            $this->createPermission('software.download', 'Tải phần mềm', 'Tải phần mềm/license từ hệ thống', 'Phần mềm & License'),
            
            // ===== BÁO CÁO =====
            // Báo cáo tổng hợp
            $this->createPermission('reports.view', 'Xem báo cáo', 'Xem các báo cáo trong hệ thống', 'Báo cáo'),
            $this->createPermission('reports.export', 'Xuất báo cáo', 'Xuất báo cáo ra file', 'Báo cáo'),
            $this->createPermission('reports.inventory', 'Xem báo cáo tồn kho', 'Xem báo cáo tồn kho của hệ thống', 'Báo cáo'),
            $this->createPermission('reports.operations', 'Xem báo cáo vận hành', 'Xem báo cáo vận hành của hệ thống', 'Báo cáo'),
            $this->createPermission('reports.projects', 'Xem báo cáo dự án', 'Xem báo cáo dự án của hệ thống', 'Báo cáo'),
            
            // ===== PHÂN QUYỀN (CHỈ ADMIN) =====
            // Quản lý nhóm quyền
            $this->createPermission('roles.view', 'Xem nhóm quyền', 'Xem danh sách nhóm quyền', 'Phân quyền'),
            $this->createPermission('roles.create', 'Thêm nhóm quyền', 'Tạo nhóm quyền mới', 'Phân quyền'),
            $this->createPermission('roles.edit', 'Sửa nhóm quyền', 'Chỉnh sửa nhóm quyền', 'Phân quyền'),
            $this->createPermission('roles.delete', 'Xóa nhóm quyền', 'Xóa nhóm quyền khỏi hệ thống', 'Phân quyền'),
            
            // Quản lý quyền
            $this->createPermission('permissions.view', 'Xem quyền', 'Xem danh sách quyền', 'Phân quyền'),
            $this->createPermission('permissions.manage', 'Quản lý quyền', 'Thêm/sửa/xóa quyền', 'Phân quyền'),
            
            // Nhật ký người dùng
            $this->createPermission('user-logs.view', 'Xem nhật ký người dùng', 'Xem nhật ký hoạt động người dùng', 'Phân quyền'),
            $this->createPermission('user-logs.export', 'Xuất nhật ký người dùng', 'Xuất nhật ký người dùng ra file', 'Phân quyền'),
            $this->createPermission('user-logs.filter', 'Lọc nhật ký', 'Tìm kiếm và lọc nhật ký theo nhiều tiêu chí', 'Phân quyền'),
            
            // ===== PHẠM VI QUYỀN =====
            // Quản lý phạm vi quyền
            $this->createPermission('scope.assign', 'Gán phạm vi quyền', 'Gán phạm vi quyền cho người dùng', 'Phạm vi quyền'),
            $this->createPermission('scope.warehouse', 'Quản lý theo kho', 'Quản lý theo phạm vi kho hàng', 'Phạm vi quyền'),
            $this->createPermission('scope.project', 'Quản lý theo dự án', 'Quản lý theo phạm vi dự án', 'Phạm vi quyền'),
            $this->createPermission('scope.region', 'Quản lý theo địa bàn', 'Quản lý theo phạm vi địa bàn (Tỉnh/Huyện/Xã)', 'Phạm vi quyền'),
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
