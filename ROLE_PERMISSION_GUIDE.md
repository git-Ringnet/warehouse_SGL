# Hướng dẫn hệ thống phân quyền nhóm - Warehouse WMS

## Tổng quan
Hệ thống warehouse WMS đã được cập nhật với tính năng phân quyền nhóm mà **chỉ có tài khoản admin mới có quyền tạo, chỉnh sửa và xóa nhóm phân quyền**. Điều này đảm bảo an toàn hệ thống và tránh việc cấp quyền không phù hợp.

## Tính năng mới

### 1. Middleware AdminOnlyMiddleware
- Chỉ tài khoản có `role = 'admin'` mới có thể truy cập các chức năng quản lý role và permission
- Tự động redirect hoặc trả về lỗi 403 nếu người dùng không phải admin

### 2. Cập nhật Routes và Middleware (Laravel 11)
- Đăng ký middleware trong `bootstrap/app.php`:
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->alias([
          'admin-only' => \App\Http\Middleware\AdminOnlyMiddleware::class,
      ]);
  })
  ```
- Áp dụng middleware cho các route quản lý role và permission:
  ```php
  Route::middleware('admin-only')->group(function () {
      Route::resource('roles', RoleController::class);
      Route::patch('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus']);
      Route::resource('permissions', PermissionController::class);
  });
  ```

### 3. Cập nhật Sidebar
- Hiển thị menu "Phân Quyền" với đầy đủ chức năng cho admin
- Hiển thị menu bị khóa với thông báo "Chỉ admin mới có quyền" cho người dùng thường

### 4. Thông báo trên giao diện
- Thêm thông báo rõ ràng về quyền admin trên các trang quản lý role
- Hiển thị cảnh báo an toàn hệ thống

### 5. Template quyền nhanh (MỚI)
- 6 template quyền phổ biến để tạo nhanh:
  - 🏭 **Quản lý kho**: Vận hành kho + Quản lý tài sản + Báo cáo
  - ⚙️ **Sản xuất**: Sản xuất & Kiểm thử + Quản lý tài sản
  - 🔧 **Bảo trì**: Bảo trì & Sửa chữa + Phần mềm
  - 📋 **Dự án**: Quản lý dự án + Phiếu yêu cầu
  - 👁️ **Chỉ xem**: Tất cả quyền .view
  - 🗑️ **Xóa tất cả**: Bỏ chọn tất cả quyền

### 6. Giao diện cải tiến (MỚI)
- **Màu sắc phân biệt**: Mỗi nhóm quyền có màu sắc và icon riêng
- **Icon trực quan**: Hiển thị icon cho từng loại quyền (xem, tạo, sửa, xóa)
- **Tìm kiếm thông minh**: Tìm kiếm quyền theo tên
- **Chọn nhanh**: Chọn chỉ quyền xem, chọn tất cả, chọn theo nhóm

## Cách sử dụng

### Đối với Admin

1. **Đăng nhập với tài khoản admin:**
   - Username: `admin`
   - Password: `admin123` (vui lòng đổi mật khẩu sau lần đăng nhập đầu)

2. **Truy cập menu Phân Quyền:**
   - Vào sidebar > Phân Quyền
   - Chọn "Nhóm quyền" để quản lý các role
   - Chọn "Danh sách quyền" để xem các permission có sẵn

3. **Tạo nhóm quyền mới:**
   - Click "Thêm nhóm quyền"
   - Điền thông tin cơ bản (tên, mô tả)
   
   **📋 Sử dụng Template quyền nhanh:**
   - Click vào template phù hợp (VD: "Quản lý kho")
   - Hệ thống tự động chọn các quyền phù hợp
   - Có thể điều chỉnh thêm/bớt quyền sau đó
   
   **🔍 Tìm kiếm và chọn quyền:**
   - Sử dụng ô tìm kiếm để tìm quyền cụ thể
   - Chọn "Chỉ quyền xem" để chỉ cấp quyền xem
   - Chọn theo nhóm hoặc chọn tất cả
   
   - Gán nhân viên vào nhóm
   - Lưu lại

4. **Quản lý nhóm quyền:**
   - Xem chi tiết: Click biểu tượng mắt
   - Chỉnh sửa: Click biểu tượng bút chì
   - Xóa: Click biểu tượng thùng rác (không áp dụng cho role hệ thống)
   - Bật/tắt: Click biểu tượng toggle

### Đối với người dùng thường

- Menu "Phân Quyền" sẽ hiển thị trạng thái bị khóa
- Không thể truy cập vào các trang quản lý role/permission
- Nhận thông báo lỗi nếu cố gắng truy cập trực tiếp URL

## Cấu trúc dữ liệu

### Roles (Nhóm quyền)
- `id`: ID nhóm quyền
- `name`: Tên nhóm quyền (unique)
- `description`: Mô tả nhóm quyền
- `scope`: Phạm vi áp dụng (warehouse, project, null)
- `is_active`: Trạng thái hoạt động
- `is_system`: Đánh dấu role hệ thống (không được xóa)

### Permissions (Quyền) - Đã tổ chức lại
- `id`: ID quyền
- `name`: Tên kỹ thuật (VD: users.create)
- `display_name`: Tên hiển thị
- `description`: Mô tả chi tiết
- `group`: Nhóm quyền (đã tổ chức lại thành 10 nhóm chính)

### Role-Permission (Quan hệ nhiều-nhiều)
- `role_id`: ID nhóm quyền
- `permission_id`: ID quyền

## Nhóm quyền mới (10 nhóm chính)

### 1. 🏢 Quản lý hệ thống
- Nhân viên, Khách hàng, Nhà cung cấp, Kho hàng
- **Màu**: Xám | **Icon**: ⚙️

### 2. 📦 Quản lý tài sản
- Vật tư, Thành phẩm, Hàng hóa
- **Màu**: Xanh dương | **Icon**: 📦

### 3. 🏭 Vận hành kho
- Nhập kho, Xuất kho, Chuyển kho
- **Màu**: Xanh lá | **Icon**: 🏭

### 4. ⚙️ Sản xuất & Kiểm thử
- Lắp ráp, Kiểm thử
- **Màu**: Tím | **Icon**: 🏭

### 5. 🔧 Bảo trì & Sửa chữa
- Sửa chữa, Bảo hành điện tử
- **Màu**: Cam | **Icon**: 🔧

### 6. 📋 Quản lý dự án
- Dự án, Cho thuê
- **Màu**: Chàm | **Icon**: 📊

### 7. 📄 Phiếu yêu cầu
- Tạo, sửa, duyệt phiếu yêu cầu
- **Màu**: Hồng | **Icon**: 📄

### 8. 💻 Phần mềm & License
- Quản lý phần mềm, license
- **Màu**: Xanh ngọc | **Icon**: 💻

### 9. 📊 Báo cáo
- Báo cáo tổng hợp, xuất báo cáo
- **Màu**: Vàng | **Icon**: 📊

### 10. 🛡️ Phân quyền (Chỉ admin)
- Quản lý nhóm quyền, quyền, nhật ký
- **Màu**: Đỏ | **Icon**: 🛡️

### 11. 🗺️ Phạm vi quyền
- Gán phạm vi quyền theo kho, dự án, địa bàn
- **Màu**: Xanh dương nhạt | **Icon**: 🗺️

## Template quyền chi tiết

### 🏭 Quản lý kho
**Quyền được cấp:**
- Kho hàng (xem, tạo, sửa, xóa)
- Vật tư, Thành phẩm, Hàng hóa (xem, tạo, sửa, xóa)
- Nhập kho, Xuất kho, Chuyển kho (xem, tạo, sửa, xóa)
- Báo cáo (xem, xuất)

**Phù hợp cho:** Quản lý kho, Thủ kho, Nhân viên kho

### ⚙️ Sản xuất
**Quyền được cấp:**
- Lắp ráp, Kiểm thử (xem, tạo, sửa, xóa)
- Vật tư, Thành phẩm (xem, tạo, sửa, xóa)
- Báo cáo vận hành

**Phù hợp cho:** Kỹ sư sản xuất, Công nhân lắp ráp, QC

### 🔧 Bảo trì
**Quyền được cấp:**
- Sửa chữa, Bảo hành (xem, tạo, sửa, xóa)
- Phần mềm & License (xem, tạo, sửa, xóa)
- Phiếu yêu cầu (xem, tạo, sửa, xóa)

**Phù hợp cho:** Kỹ thuật viên, Nhân viên bảo trì

### 📋 Dự án
**Quyền được cấp:**
- Dự án, Cho thuê (xem, tạo, sửa, xóa)
- Phiếu yêu cầu (xem, tạo, sửa, xóa)
- Báo cáo dự án

**Phù hợp cho:** Quản lý dự án, Nhân viên dự án

### 👁️ Chỉ xem
**Quyền được cấp:**
- Tất cả quyền .view (chỉ xem, không tạo/sửa/xóa)

**Phù hợp cho:** Khách hàng, Đối tác, Nhân viên mới

## Roles mặc định

1. **Super Admin**: Toàn quyền hệ thống
2. **Kho Sản Xuất**: Quản lý thiết bị kho sản xuất
3. **Kho Thành Phẩm**: Quản lý thiết bị thành phẩm
4. **Kho Bảo Hành**: Quản lý bảo hành thiết bị
5. **Kho Phần Mềm**: Quản lý license, phần mềm
6. **Quản Lý Dự Án**: Quản lý thiết bị theo dự án

## Command hữu ích

### Seed dữ liệu
```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=AdminSeeder
```

### Test hệ thống
```bash
php artisan test:role-permissions --setup
php artisan test:role-permissions
```

### Refresh toàn bộ
```bash
php artisan migrate:refresh --seed
```

## Bảo mật

### Các biện pháp an toàn đã áp dụng:
1. **Middleware bảo vệ**: Chỉ admin mới truy cập được
2. **Validation nghiêm ngặt**: Kiểm tra dữ liệu đầu vào
3. **Role hệ thống**: Không thể xóa các role quan trọng
4. **Nhật ký hoạt động**: Ghi lại mọi thay đổi
5. **Thông báo rõ ràng**: Người dùng biết quyền hạn của mình
6. **Template an toàn**: Các template được thiết kế theo nguyên tắc bảo mật

### Khuyến nghị:
1. Đổi mật khẩu admin mặc định ngay lập tức
2. Sử dụng template có sẵn thay vì tự tạo từ đầu
3. Tạo role chuyên biệt thay vì dùng admin cho mọi việc
4. Định kỳ review và audit các quyền được cấp
5. Backup dữ liệu trước khi thực hiện thay đổi lớn
6. Test kỹ trước khi deploy lên production

## Troubleshooting

### Lỗi 403 Forbidden
- Kiểm tra user có role admin không
- Đảm bảo đã đăng nhập với guard 'web'

### Không thể tạo role
- Kiểm tra middleware đã được đăng ký
- Kiểm tra routes đã áp dụng middleware

### Menu không hiển thị đúng
- Clear cache: `php artisan cache:clear`
- Kiểm tra điều kiện Auth trong blade

### Template không hoạt động
- Kiểm tra JavaScript console có lỗi không
- Đảm bảo file JS đã được load đúng

---

**Lưu ý**: Tài liệu này sẽ được cập nhật khi có thay đổi về hệ thống phân quyền. 