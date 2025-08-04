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
            $this->createPermission('employees.view', 'Xem danh sách nhân viên', 'Xem danh sách tất cả nhân viên trong hệ thống', 'Quản lý hệ thống'),
            $this->createPermission('employees.view_detail', 'Xem chi tiết nhân viên', 'Xem thông tin chi tiết của nhân viên', 'Quản lý hệ thống'),
            $this->createPermission('employees.create', 'Thêm nhân viên', 'Thêm nhân viên mới vào hệ thống', 'Quản lý hệ thống'),
            $this->createPermission('employees.edit', 'Sửa thông tin nhân viên', 'Chỉnh sửa thông tin nhân viên', 'Quản lý hệ thống'),
            $this->createPermission('employees.delete', 'Xóa nhân viên', 'Xóa nhân viên khỏi hệ thống', 'Quản lý hệ thống'),
            $this->createPermission('employees.toggle_status', 'Khóa/mở khóa tài khoản', 'Khóa hoặc mở khóa tài khoản nhân viên', 'Quản lý hệ thống'),
            $this->createPermission('employees.export', 'Xuất dữ liệu nhân viên', 'Xuất danh sách nhân viên ra file Excel/PDF', 'Quản lý hệ thống'),

            // Quản lý khách hàng
            $this->createPermission('customers.view', 'Xem danh sách khách hàng', 'Xem thông tin khách hàng', 'Quản lý hệ thống'),
            $this->createPermission('customers.view_detail', 'Xem chi tiết khách hàng', 'Xem thông tin chi tiết của khách hàng', 'Quản lý hệ thống'),
            $this->createPermission('customers.create', 'Thêm khách hàng', 'Tạo khách hàng mới', 'Quản lý hệ thống'),
            $this->createPermission('customers.edit', 'Sửa khách hàng', 'Chỉnh sửa thông tin khách hàng', 'Quản lý hệ thống'),
            $this->createPermission('customers.delete', 'Xóa khách hàng', 'Xóa khách hàng khỏi hệ thống', 'Quản lý hệ thống'),
            $this->createPermission('customers.manage', 'Quản lý khách hàng', 'Kích hoạt/vô hiệu hóa tài khoản khách hàng', 'Quản lý hệ thống'),
            $this->createPermission('customers.export', 'Xuất dữ liệu khách hàng', 'Xuất danh sách khách hàng ra file Excel/PDF', 'Quản lý hệ thống'),

            // Quản lý nhà cung cấp
            $this->createPermission('suppliers.view', 'Xem danh sách nhà cung cấp', 'Xem thông tin nhà cung cấp', 'Quản lý hệ thống'),
            $this->createPermission('suppliers.view_detail', 'Xem chi tiết nhà cung cấp', 'Xem thông tin chi tiết nhà cung cấp', 'Quản lý hệ thống'),
            $this->createPermission('suppliers.create', 'Thêm nhà cung cấp', 'Tạo nhà cung cấp mới', 'Quản lý hệ thống'),
            $this->createPermission('suppliers.edit', 'Sửa nhà cung cấp', 'Chỉnh sửa thông tin nhà cung cấp', 'Quản lý hệ thống'),
            $this->createPermission('suppliers.delete', 'Xóa nhà cung cấp', 'Xóa nhà cung cấp khỏi hệ thống', 'Quản lý hệ thống'),
            $this->createPermission('suppliers.export', 'Xuất dữ liệu nhà cung cấp', 'Xuất danh sách nhà cung cấp ra file Excel/PDF', 'Quản lý hệ thống'),

            // Quản lý kho hàng
            $this->createPermission('warehouses.view', 'Xem danh sách kho hàng', 'Xem thông tin kho hàng', 'Quản lý hệ thống'),
            $this->createPermission('warehouses.view_detail', 'Xem chi tiết kho hàng', 'Xem thông tin chi tiết và tồn kho của kho hàng', 'Quản lý hệ thống'),
            $this->createPermission('warehouses.create', 'Thêm kho hàng', 'Tạo kho hàng mới', 'Quản lý hệ thống'),
            $this->createPermission('warehouses.edit', 'Sửa kho hàng', 'Chỉnh sửa thông tin kho hàng', 'Quản lý hệ thống'),
            $this->createPermission('warehouses.delete', 'Xóa kho hàng', 'Xóa kho hàng khỏi hệ thống', 'Quản lý hệ thống'),
            $this->createPermission('warehouses.export', 'Xuất file kho hàng', 'Xuất danh sách kho hàng ra file Excel, PDF', 'Quản lý hệ thống'),

            // ===== QUẢN LÝ TÀI SẢN =====
            // Quản lý vật tư
            $this->createPermission('materials.view', 'Xem danh sách vật tư', 'Xem thông tin vật tư', 'Quản lý tài sản'),
            $this->createPermission('materials.view_detail', 'Xem chi tiết vật tư', 'Xem thông tin chi tiết và hình ảnh của vật tư', 'Quản lý tài sản'),
            $this->createPermission('materials.create', 'Thêm vật tư', 'Tạo vật tư mới', 'Quản lý tài sản'),
            $this->createPermission('materials.edit', 'Sửa vật tư', 'Chỉnh sửa thông tin vật tư', 'Quản lý tài sản'),
            $this->createPermission('materials.delete', 'Xóa vật tư', 'Xóa vật tư khỏi hệ thống', 'Quản lý tài sản'),
            $this->createPermission('materials.export', 'Xuất file vật tư', 'Xuất danh sách vật tư ra file', 'Quản lý tài sản'),

            // Quản lý thành phẩm
            $this->createPermission('products.view', 'Xem danh sách thành phẩm', 'Xem thông tin thành phẩm', 'Quản lý tài sản'),
            $this->createPermission('products.view_detail', 'Xem chi tiết thành phẩm', 'Xem thông tin chi tiết và hình ảnh của thành phẩm', 'Quản lý tài sản'),
            $this->createPermission('products.create', 'Thêm thành phẩm', 'Tạo thành phẩm mới', 'Quản lý tài sản'),
            $this->createPermission('products.edit', 'Sửa thành phẩm', 'Chỉnh sửa thông tin thành phẩm', 'Quản lý tài sản'),
            $this->createPermission('products.delete', 'Xóa thành phẩm', 'Xóa thành phẩm khỏi hệ thống', 'Quản lý tài sản'),
            $this->createPermission('products.export', 'Xuất file thành phẩm', 'Xuất danh sách thành phẩm ra file', 'Quản lý tài sản'),

            // Quản lý hàng hóa
            $this->createPermission('goods.view', 'Xem danh sách hàng hóa', 'Xem thông tin hàng hóa', 'Quản lý tài sản'),
            $this->createPermission('goods.view_detail', 'Xem chi tiết hàng hóa', 'Xem thông tin chi tiết và hình ảnh của hàng hóa', 'Quản lý tài sản'),
            $this->createPermission('goods.create', 'Thêm hàng hóa', 'Tạo hàng hóa mới', 'Quản lý tài sản'),
            $this->createPermission('goods.edit', 'Sửa hàng hóa', 'Chỉnh sửa thông tin hàng hóa', 'Quản lý tài sản'),
            $this->createPermission('goods.delete', 'Xóa hàng hóa', 'Xóa hàng hóa khỏi hệ thống', 'Quản lý tài sản'),
            $this->createPermission('goods.export', 'Xuất file hàng hóa', 'Xuất danh sách hàng hóa ra file', 'Quản lý tài sản'),

            // ===== VẬN HÀNH KHO =====
            // Nhập kho
            $this->createPermission('inventory_imports.view', 'Xem nhập kho', 'Xem danh sách phiếu nhập kho', 'Vận hành kho'),
            $this->createPermission('inventory_imports.create', 'Thêm nhập kho', 'Tạo phiếu nhập kho mới', 'Vận hành kho'),
            $this->createPermission('inventory_imports.view_detail', 'Xem chi tiết nhập kho', 'Xem chi tiết phiếu nhập kho', 'Vận hành kho'),
            $this->createPermission('inventory_imports.edit', 'Sửa nhập kho', 'Chỉnh sửa phiếu nhập kho', 'Vận hành kho'),
            $this->createPermission('inventory_imports.delete', 'Xóa nhập kho', 'Xóa phiếu nhập kho', 'Vận hành kho'),
            $this->createPermission('inventory_imports.approve', 'Duyệt nhập kho', 'Duyệt phiếu nhập kho', 'Vận hành kho'),
            $this->createPermission('inventory_imports.cancel', 'Huỷ nhập kho', 'Huỷ phiếu nhập kho', 'Vận hành kho'),

            // Xuất kho
            $this->createPermission('inventory.view', 'Xem danh sách xuất kho', 'Xem danh sách phiếu xuất kho', 'Vận hành kho'),
            $this->createPermission('inventory.create', 'Tạo phiếu xuất kho', 'Tạo mới phiếu xuất kho', 'Vận hành kho'),
            $this->createPermission('inventory.view_detail', 'Xem chi tiết xuất kho', 'Xem chi tiết phiếu xuất kho', 'Vận hành kho'),
            $this->createPermission('inventory.edit', 'Sửa phiếu xuất kho', 'Chỉnh sửa phiếu xuất kho', 'Vận hành kho'),
            $this->createPermission('inventory.delete', 'Xóa phiếu xuất kho', 'Xóa phiếu xuất kho', 'Vận hành kho'),
            $this->createPermission('inventory.approve', 'Duyệt phiếu xuất kho', 'Duyệt phiếu xuất kho', 'Vận hành kho'),
            $this->createPermission('inventory.cancel', 'Hủy phiếu xuất kho', 'Hủy phiếu xuất kho', 'Vận hành kho'),

            // Chuyển kho
            $this->createPermission('warehouse-transfers.view', 'Xem chuyển kho', 'Xem thông tin chuyển kho', 'Vận hành kho'),
            $this->createPermission('warehouse-transfers.view_detail', 'Xem chi tiết chuyển kho', 'Xem chi tiết phiếu chuyển kho', 'Vận hành kho'),
            $this->createPermission('warehouse-transfers.create', 'Thêm chuyển kho', 'Tạo chuyển kho mới', 'Vận hành kho'),
            $this->createPermission('warehouse-transfers.edit', 'Sửa chuyển kho', 'Chỉnh sửa thông tin chuyển kho', 'Vận hành kho'),
            $this->createPermission('warehouse-transfers.delete', 'Xóa chuyển kho', 'Xóa chuyển kho khỏi hệ thống', 'Vận hành kho'),
            $this->createPermission('warehouse-transfers.approve', 'Duyệt chuyển kho', 'Duyệt phiếu chuyển kho', 'Vận hành kho'),

            // ===== SẢN XUẤT & KIỂM THỬ =====
            // Lắp ráp
            $this->createPermission('assembly.view', 'Xem lắp ráp', 'Xem thông tin lắp ráp', 'Sản xuất & Kiểm thử'),
            $this->createPermission('assembly.view_detail', 'Xem chi tiết lắp ráp', 'Xem chi tiết phiếu lắp ráp', 'Sản xuất & Kiểm thử'),
            $this->createPermission('assembly.create', 'Thêm lắp ráp', 'Tạo lắp ráp mới', 'Sản xuất & Kiểm thử'),
            $this->createPermission('assembly.edit', 'Sửa lắp ráp', 'Chỉnh sửa thông tin lắp ráp', 'Sản xuất & Kiểm thử'),
            $this->createPermission('assembly.delete', 'Xóa lắp ráp', 'Xóa phiếu lắp ráp', 'Sản xuất & Kiểm thử'),
            $this->createPermission('assembly.approve', 'Duyệt lắp ráp', 'Duyệt phiếu lắp ráp', 'Sản xuất & Kiểm thử'),
            $this->createPermission('assembly.cancel', 'Huỷ lắp ráp', 'Huỷ phiếu lắp ráp', 'Sản xuất & Kiểm thử'),
            $this->createPermission('assembly.export', 'Xuất file lắp ráp', 'Xuất phiếu lắp ráp ra file Excel, PDF', 'Sản xuất & Kiểm thử'),

            // Kiểm thử
            $this->createPermission('testing.view', 'Xem kiểm thử', 'Xem danh sách phiếu kiểm thử', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.view_detail', 'Xem chi tiết kiểm thử', 'Xem chi tiết phiếu kiểm thử', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.create', 'Thêm kiểm thử', 'Tạo phiếu kiểm thử mới', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.edit', 'Sửa kiểm thử', 'Chỉnh sửa thông tin phiếu kiểm thử', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.delete', 'Xóa kiểm thử', 'Xóa phiếu kiểm thử', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.receive', 'Tiếp nhận kiểm thử', 'Tiếp nhận phiếu kiểm thử để thực hiện', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.complete', 'Hoàn thành kiểm thử', 'Đánh dấu hoàn thành phiếu kiểm thử', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.update_inventory', 'Cập nhật kho kiểm thử', 'Cập nhật kết quả kiểm thử vào kho', 'Sản xuất & Kiểm thử'),
            $this->createPermission('testing.print', 'In phiếu kiểm thử', 'In phiếu kiểm thử ra PDF', 'Sản xuất & Kiểm thử'),

            // ===== BẢO TRÌ & SỬA CHỮA =====
            // Sửa chữa
            $this->createPermission('repairs.view', 'Xem sửa chữa', 'Xem thông tin sửa chữa', 'Bảo trì & Sửa chữa'),
            $this->createPermission('repairs.view_detail', 'Xem chi tiết sửa chữa', 'Xem chi tiết phiếu sửa chữa', 'Bảo trì & Sửa chữa'),
            $this->createPermission('repairs.create', 'Thêm sửa chữa', 'Tạo phiếu sửa chữa mới', 'Bảo trì & Sửa chữa'),
            $this->createPermission('repairs.edit', 'Sửa phiếu sửa chữa', 'Chỉnh sửa thông tin phiếu sửa chữa', 'Bảo trì & Sửa chữa'),
            $this->createPermission('repairs.delete', 'Xóa phiếu sửa chữa', 'Xóa phiếu sửa chữa khỏi hệ thống', 'Bảo trì & Sửa chữa'),
            $this->createPermission('repairs.complete', 'Hoàn thành sửa chữa', 'Đánh dấu hoàn thành phiếu sửa chữa', 'Bảo trì & Sửa chữa'),

            // Bảo hành điện tử
            $this->createPermission('warranties.view', 'Xem bảo hành', 'Xem thông tin bảo hành', 'Bảo trì & Sửa chữa'),
            $this->createPermission('warranties.view_detail', 'Xem chi tiết bảo hành', 'Xem chi tiết phiếu bảo hành', 'Bảo trì & Sửa chữa'),

            // ===== QUẢN LÝ DỰ ÁN =====
            // Dự án
            $this->createPermission('projects.view', 'Xem dự án', 'Xem thông tin dự án', 'Quản lý dự án'),
            $this->createPermission('projects.view_detail', 'Xem chi tiết dự án', 'Xem chi tiết dự án', 'Quản lý dự án'),
            $this->createPermission('projects.create', 'Thêm dự án', 'Tạo dự án mới', 'Quản lý dự án'),
            $this->createPermission('projects.edit', 'Sửa dự án', 'Chỉnh sửa thông tin dự án', 'Quản lý dự án'),
            $this->createPermission('projects.delete', 'Xóa dự án', 'Xóa dự án khỏi hệ thống', 'Quản lý dự án'),

            // Cho thuê
            $this->createPermission('rentals.view', 'Xem cho thuê', 'Xem thông tin cho thuê', 'Quản lý dự án'),
            $this->createPermission('rentals.view_detail', 'Xem chi tiết cho thuê', 'Xem chi tiết cho thuê', 'Quản lý dự án'),
            $this->createPermission('rentals.create', 'Thêm cho thuê', 'Tạo cho thuê mới', 'Quản lý dự án'),
            $this->createPermission('rentals.edit', 'Sửa cho thuê', 'Chỉnh sửa thông tin cho thuê', 'Quản lý dự án'),
            $this->createPermission('rentals.delete', 'Xóa cho thuê', 'Xóa cho thuê khỏi hệ thống', 'Quản lý dự án'),

            // ===== PHIẾU YÊU CẦU =====
            // Phiếu yêu cầu
            $this->createPermission('requests.view', 'Xem phiếu yêu cầu', 'Xem danh sách phiếu yêu cầu', 'Phiếu yêu cầu'),
            $this->createPermission('requests.view_detail', 'Xem chi tiết phiếu yêu cầu', 'Xem chi tiết phiếu yêu cầu', 'Phiếu yêu cầu'),
            $this->createPermission('requests.export', 'Xuất file phiếu yêu cầu', 'Xuất phiếu yêu cầu ra file Excel/PDF', 'Phiếu yêu cầu'),
            $this->createPermission('requests.copy', 'Sao chép phiếu yêu cầu', 'Sao chép phiếu yêu cầu', 'Phiếu yêu cầu'),

            // Phiếu đề xuất dự án
            $this->createPermission('requests.project.create', 'Tạo phiếu đề xuất dự án', 'Tạo mới phiếu đề xuất triển khai dự án', 'Phiếu yêu cầu'),
            $this->createPermission('requests.project.edit', 'Sửa phiếu đề xuất dự án', 'Chỉnh sửa phiếu đề xuất dự án', 'Phiếu yêu cầu'),
            $this->createPermission('requests.project.delete', 'Xóa phiếu đề xuất dự án', 'Xóa phiếu đề xuất dự án', 'Phiếu yêu cầu'),
            $this->createPermission('requests.project.approve', 'Duyệt phiếu đề xuất dự án', 'Phê duyệt phiếu đề xuất dự án', 'Phiếu yêu cầu'),
            $this->createPermission('requests.project.reject', 'Từ chối phiếu đề xuất dự án', 'Từ chối phiếu đề xuất dự án', 'Phiếu yêu cầu'),

            // Phiếu bảo trì dự án
            $this->createPermission('requests.maintenance.create', 'Tạo phiếu bảo trì dự án', 'Tạo mới phiếu bảo trì dự án', 'Phiếu yêu cầu'),
            $this->createPermission('requests.maintenance.edit', 'Sửa phiếu bảo trì dự án', 'Chỉnh sửa phiếu bảo trì dự án', 'Phiếu yêu cầu'),
            $this->createPermission('requests.maintenance.delete', 'Xóa phiếu bảo trì dự án', 'Xóa phiếu bảo trì dự án', 'Phiếu yêu cầu'),
            $this->createPermission('requests.maintenance.approve', 'Duyệt phiếu bảo trì dự án', 'Phê duyệt phiếu bảo trì dự án', 'Phiếu yêu cầu'),
            $this->createPermission('requests.maintenance.reject', 'Từ chối phiếu bảo trì dự án', 'Từ chối phiếu bảo trì dự án', 'Phiếu yêu cầu'),

            // Phiếu bảo trì của khách hàng
            $this->createPermission('requests.customer-maintenance.create', 'Tạo phiếu bảo trì khách hàng', 'Tạo mới phiếu yêu cầu bảo trì của khách hàng', 'Phiếu yêu cầu'),
            $this->createPermission('requests.customer-maintenance.edit', 'Sửa phiếu bảo trì khách hàng', 'Chỉnh sửa phiếu bảo trì của khách hàng', 'Phiếu yêu cầu'),
            $this->createPermission('requests.customer-maintenance.delete', 'Xóa phiếu bảo trì khách hàng', 'Xóa phiếu bảo trì của khách hàng', 'Phiếu yêu cầu'),
            $this->createPermission('requests.customer-maintenance.approve', 'Duyệt phiếu bảo trì khách hàng', 'Phê duyệt phiếu bảo trì của khách hàng', 'Phiếu yêu cầu'),
            $this->createPermission('requests.customer-maintenance.reject', 'Từ chối phiếu bảo trì khách hàng', 'Từ chối phiếu bảo trì của khách hàng', 'Phiếu yêu cầu'),

            // ===== PHẦN MỀM & LICENSE =====
            // Phần mềm
            $this->createPermission('software.view', 'Xem phần mềm', 'Xem danh sách phần mềm và license', 'Phần mềm & License'),
            $this->createPermission('software.view_detail', 'Xem chi tiết phần mềm', 'Xem chi tiết phần mềm và license', 'Phần mềm & License'),
            $this->createPermission('software.create', 'Thêm phần mềm', 'Tạo phần mềm/license mới', 'Phần mềm & License'),
            $this->createPermission('software.edit', 'Sửa phần mềm', 'Chỉnh sửa thông tin phần mềm/license', 'Phần mềm & License'),
            $this->createPermission('software.delete', 'Xóa phần mềm', 'Xóa phần mềm/license khỏi hệ thống', 'Phần mềm & License'),
            $this->createPermission('software.download', 'Tải phần mềm', 'Tải phần mềm/license từ hệ thống', 'Phần mềm & License'),

            // ===== BÁO CÁO =====
            // Báo cáo dashboard và thống kê tổng quan
            $this->createPermission('reports.overview', 'Xem dashboard thống kê', 'Xem dashboard và thống kê tổng quan của hệ thống', 'Báo cáo'),
            // Báo cáo chi tiết xuất nhập tồn
            $this->createPermission('reports.inventory', 'Xem báo cáo xuất nhập tồn chi tiết', 'Xem báo cáo chi tiết xuất nhập tồn theo thời gian', 'Báo cáo'),
            // Xuất file báo cáo
            $this->createPermission('reports.export', 'Xuất file báo cáo', 'Xuất báo cáo ra file Excel/PDF', 'Báo cáo'),

            // ===== PHÂN QUYỀN (CHỈ ADMIN) =====
            // Quản lý nhóm quyền
            $this->createPermission('roles.view', 'Xem nhóm quyền', 'Xem danh sách nhóm quyền', 'Phân quyền'),
            $this->createPermission('roles.view_detail', 'Xem chi tiết nhóm quyền', 'Xem chi tiết nhóm quyền', 'Phân quyền'),
            $this->createPermission('roles.create', 'Thêm nhóm quyền', 'Tạo nhóm quyền mới', 'Phân quyền'),
            $this->createPermission('roles.edit', 'Sửa nhóm quyền', 'Chỉnh sửa nhóm quyền', 'Phân quyền'),
            $this->createPermission('roles.delete', 'Xóa nhóm quyền', 'Xóa nhóm quyền khỏi hệ thống', 'Phân quyền'),

            // Nhật ký người dùng
            $this->createPermission('user-logs.view', 'Xem nhật ký người dùng', 'Xem nhật ký hoạt động người dùng', 'Phân quyền'),
            $this->createPermission('user-logs.export', 'Xuất nhật ký người dùng', 'Xuất nhật ký người dùng ra file', 'Phân quyền'),

            // Nhật ký thay đổi
            $this->createPermission('change-logs.view', 'Xem nhật ký thay đổi', 'Xem nhật ký thay đổi hệ thống', 'Nhật ký hệ thống'),
            $this->createPermission('change-logs.view_detail', 'Xem chi tiết nhật ký thay đổi', 'Xem chi tiết nhật ký thay đổi hệ thống', 'Nhật ký hệ thống'),
            $this->createPermission('change-logs.export', 'Xuất nhật ký thay đổi', 'Xuất nhật ký thay đổi ra file', 'Nhật ký hệ thống'),
        ];

        // Thêm vào database
        Permission::insert($permissions);

        // Xóa các quyền cũ bị trùng lặp
        $duplicatePermissions = ['reports.overview.view', 'reports.inventory.view'];
        foreach ($duplicatePermissions as $permissionName) {
            $existingPermission = Permission::where('name', $permissionName)->first();
            if ($existingPermission) {
                // Xóa quyền khỏi bảng role_permission trước
                DB::table('role_permission')->where('permission_id', $existingPermission->id)->delete();
                // Xóa quyền
                $existingPermission->delete();
                // Log information about deleted permission
            }
        }

        // Quyền vận hành
        $this->createPermissionIfNotExists('inventory.view', 'Xem xuất nhập kho', 'Xem xuất nhập kho', 'Vận hành kho');
        $this->createPermissionIfNotExists('repairs.view', 'Xem sửa chữa bảo trì', 'Xem sửa chữa bảo trì', 'Bảo trì & Sửa chữa');
        $this->createPermissionIfNotExists('warranties.view', 'Xem bảo hành điện tử', 'Xem bảo hành điện tử', 'Bảo trì & Sửa chữa');

        // Quyền dự án
        $this->createPermissionIfNotExists('projects.view', 'Xem dự án', 'Xem dự án', 'Quản lý dự án');
        $this->createPermissionIfNotExists('rentals.view', 'Xem cho thuê', 'Xem cho thuê', 'Quản lý dự án');

        // Quyền thiết lập khác
        $this->createPermissionIfNotExists('customers.view', 'Xem khách hàng', 'Xem khách hàng', 'Quản lý hệ thống');
        $this->createPermissionIfNotExists('customers.manage', 'Quản lý khách hàng', 'Quản lý khách hàng', 'Quản lý hệ thống');
        $this->createPermissionIfNotExists('suppliers.view', 'Xem nhà cung cấp', 'Xem nhà cung cấp', 'Quản lý hệ thống');
        $this->createPermissionIfNotExists('employees.view', 'Xem nhân viên', 'Xem nhân viên', 'Quản lý hệ thống');
        $this->createPermissionIfNotExists('employees.toggle_status', 'Khóa/mở khóa tài khoản', 'Khóa hoặc mở khóa tài khoản nhân viên', 'Quản lý hệ thống');
        $this->createPermissionIfNotExists('goods.view', 'Xem hàng hóa', 'Xem hàng hóa', 'Quản lý tài sản');
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
}
