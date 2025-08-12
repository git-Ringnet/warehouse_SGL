# Fix Bug Đồng Bộ Serial Numbers

## Mô tả vấn đề

Bug xảy ra khi cập nhật mã thiết bị trong phiếu xuất kho, hệ thống chỉ cập nhật bảng `device_codes` nhưng không đồng bộ với bảng `dispatch_items` (serial_numbers). Điều này khiến cho các phiếu bảo trì và cho thuê vẫn sử dụng serial cũ từ `dispatch_items`.

## Nguyên nhân

1. Khi cập nhật mã thiết bị trong phiếu xuất kho, dữ liệu chỉ được lưu vào bảng `device_codes`
2. Bảng `dispatch_items` không được cập nhật với serial numbers mới
3. Các phiếu bảo trì và cho thuê đọc serial numbers từ `dispatch_items`, nên vẫn thấy serial cũ

## Giải pháp đã triển khai

### 1. Cập nhật DeviceCodeController

**File:** `app/Http/Controllers/DeviceCodeController.php`

- Thêm method `syncSerialNumbersToDispatchItems()` để đồng bộ serial numbers từ `device_codes` sang `dispatch_items`
- Tự động gọi method này sau khi lưu device codes thành công
- Thêm API endpoint `syncSerialNumbers()` để đồng bộ thủ công

### 2. Cập nhật MaintenanceRequestController

**File:** `app/Http/Controllers/MaintenanceRequestController.php`

- Sửa lỗi ở dòng 727: thay `$item->serial_number` bằng logic lấy từ `serial_numbers` array
- Đảm bảo phiếu bảo trì sử dụng serial mới nhất từ dispatch_items

### 3. Thêm API Route

**File:** `routes/api.php`

```php
Route::post('device-codes/sync-serial-numbers', [DeviceCodeController::class, 'syncSerialNumbers']);
```

### 4. Cập nhật giao diện

**File:** `resources/views/inventory/dispatch_edit.blade.php`

- Thêm nút "Đồng bộ Serial" trong modal cập nhật mã thiết bị
- Thêm JavaScript để xử lý đồng bộ thủ công khi cần
- Tự động redirect về trang dự án/rental sau khi đồng bộ

### 5. Cải tiến giao diện dự án và cho thuê

**Files:** `resources/views/projects/show.blade.php`, `resources/views/rentals/show.blade.php`

- Thêm nút "Làm mới" cho từng bảng thiết bị
- Auto-refresh khi có tham số `refresh=true` trong URL
- Hiển thị thông báo thành công khi đồng bộ hoàn tất

## Cách sử dụng

### Tự động đồng bộ

Khi lưu thông tin mã thiết bị trong modal, hệ thống sẽ tự động đồng bộ serial numbers sang dispatch_items.

### Đồng bộ thủ công

1. Mở phiếu xuất kho cần đồng bộ
2. Nhấn nút "Cập nhật mã thiết bị" (hợp đồng hoặc dự phòng)
3. Trong modal, nhấn nút "Đồng bộ Serial"
4. Hệ thống sẽ đồng bộ serial numbers từ device_codes sang dispatch_items
5. Tự động redirect về trang dự án/rental với thông báo thành công

### Làm mới dữ liệu

1. **Tự động:** Sau khi đồng bộ, hệ thống tự động redirect và refresh dữ liệu
2. **Thủ công:** Nhấn nút "Làm mới" trên từng bảng thiết bị để refresh dữ liệu

## Kiểm tra kết quả

1. **Phiếu bảo trì:** Serial numbers sẽ được cập nhật theo mã thiết bị mới
2. **Phiếu cho thuê:** Serial numbers sẽ được cập nhật theo mã thiết bị mới
3. **Phiếu sửa chữa:** Serial numbers sẽ được cập nhật theo mã thiết bị mới
4. **Giao diện dự án/rental:** Hiển thị serial mới nhất sau khi refresh

## Lưu ý

- Đồng bộ chỉ ảnh hưởng đến dispatch_items có cùng dispatch_id và category
- Serial numbers được lấy từ trường `serial_main` trong bảng `device_codes`
- Nếu không có device_codes, hệ thống sẽ giữ nguyên serial numbers hiện tại
- Giao diện sẽ tự động refresh sau khi đồng bộ để hiển thị dữ liệu mới nhất

## Troubleshooting

### Nếu đồng bộ không hoạt động

1. Kiểm tra log Laravel: `storage/logs/laravel.log`
2. Kiểm tra console browser để xem lỗi JavaScript
3. Sử dụng nút "Làm mới" để refresh dữ liệu

### Nếu serial vẫn không đúng

1. Kiểm tra dữ liệu trong bảng `device_codes`
2. Kiểm tra dữ liệu trong bảng `dispatch_items`
3. Đảm bảo dispatch_id và category khớp nhau
4. Nhấn nút "Làm mới" để refresh giao diện

### Nếu giao diện không cập nhật

1. Nhấn nút "Làm mới" trên bảng thiết bị tương ứng
2. Refresh trang thủ công (F5)
3. Kiểm tra xem có thông báo lỗi nào không

## Files đã thay đổi

1. `app/Http/Controllers/DeviceCodeController.php`
2. `app/Http/Controllers/MaintenanceRequestController.php`
3. `routes/api.php`
4. `resources/views/inventory/dispatch_edit.blade.php`
5. `resources/views/projects/show.blade.php`
6. `resources/views/rentals/show.blade.php`
7. `README_SERIAL_SYNC_FIX.md` (file này) 