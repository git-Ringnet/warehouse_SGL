# ĐÃ HOÀN THÀNH SỬA LỖI SERIAL

## Tóm tắt

Đã sửa xong tất cả các vấn đề về serial number khi thu hồi thiết bị về kho.

## Các vấn đề đã được sửa

### ✅ Vấn đề 1: Không duyệt được phiếu xuất từ "Kho hư hỏng"
**Trạng thái**: ĐÃ SỬA

**Nguyên nhân**: Logic kiểm tra tồn kho không phân biệt serial thực và virtual serial (N/A-*)

**Giải pháp**: Sửa `DispatchController::approve()` để chỉ kiểm tra serial THỰC, bỏ qua virtual serial

### ✅ Vấn đề 2: Serial bị đổi thành N/A-* khi thu hồi về kho
**Trạng thái**: ĐÃ SỬA

**Nguyên nhân**: Khi thu hồi, hệ thống lấy serial từ `dispatch_items.serial_numbers` (có N/A-*) thay vì tìm serial thực từ `device_codes`

**Giải pháp**: Sửa `EquipmentServiceController::returnEquipment()` để:
- Kiểm tra nếu serial là N/A-*
- Tìm serial thực từ `device_codes.old_serial` hoặc `device_codes.serial_main`
- Lưu serial thực vào kho thay vì N/A-*

### ✅ Vấn đề 3: Dropdown hiển thị cả N/A-* và serial thực
**Trạng thái**: ĐÃ SỬA

**Nguyên nhân**: API `getItemSerials()` không map N/A-* sang serial thực, và không loại bỏ N/A-* của các serial đang được dùng

**Giải pháp**: Sửa `DispatchController::getItemSerials()` để:
- Tìm tất cả N/A-* trong kho
- Map chúng sang serial thực từ `device_codes`
- CHỈ map nếu serial thực KHÔNG đang được dùng ở phiếu xuất khác
- Loại bỏ N/A-* không được map (vì serial thực đang được dùng)
- Chỉ trả về serial thực đã được map

## Kết quả kiểm tra

### Test với LOA-TCN30 (ID: 45) trong "Kho Hư Hỏng" (ID: 3)

**Tình huống**:
- Ban đầu xuất 8 serial (0136592-0136599) cho dự án
- Thu hồi 1 serial (0136592) về kho
- 7 serial còn lại (0136593-0136599) vẫn đang ở dự án

**Trước khi sửa**:
```json
{
    "serials": [
        "0136592",
        "N/A-6BNZJN",
        "N/A-QBRBTJ",
        "N/A-W77RBP",
        "N/A-WK4H7W",
        "N/A-DMH9VG",
        "N/A-V3ANC7",
        "N/A-RBR24S"
    ]
}
```
❌ Hiển thị 8 serial (1 thực + 7 N/A-*) trong khi chỉ có 1 serial khả dụng

**Sau khi sửa**:
```json
{
    "serials": [
        "0136592"
    ]
}
```
✅ Chỉ hiển thị 1 serial thực đã thu hồi về kho!

## Logic hoạt động

### Khi thu hồi thiết bị về kho:
1. Kiểm tra serial có phải N/A-* không
2. Nếu có, tìm serial thực từ `device_codes`
3. Lưu serial thực vào kho

### Khi tạo phiếu xuất mới:
1. Lấy tất cả serial trong kho (bao gồm N/A-* và serial thực)
2. Lấy danh sách serial đang được dùng (`usedSerials`)
3. Với mỗi N/A-* trong kho:
   - Tìm serial thực tương ứng từ `device_codes`
   - Kiểm tra serial thực có trong `usedSerials` không
   - Nếu KHÔNG → map N/A-* sang serial thực
   - Nếu CÓ → bỏ qua (không hiển thị)
4. Chỉ trả về serial thực đã được map

## Các file đã được sửa

1. **app/Http/Controllers/EquipmentServiceController.php**
   - Method: `returnEquipment()`
   - Thêm logic resolve virtual serial sang real serial trước khi lưu vào kho

2. **app/Http/Controllers/DispatchController.php**
   - Method: `approve()` - Phân biệt real serial và virtual serial khi kiểm tra tồn kho
   - Method: `getItemSerials()` - Map N/A-* sang serial thực và loại bỏ N/A-* của serial đang dùng

3. **app/Helpers/SerialHelper.php**
   - Thêm method `isVirtualSerial()` để kiểm tra serial có phải N/A-* không

## Hướng dẫn sử dụng

### Từ bây giờ trở đi (dữ liệu mới)

Hệ thống sẽ tự động xử lý đúng:

1. **Khi xuất hàng**: Nếu không có serial, hệ thống tạo N/A-* tạm thời
2. **Khi cập nhật mã thiết bị**: Serial thực được lưu vào `device_codes`
3. **Khi thu hồi**: Hệ thống tự động tìm serial thực và lưu vào kho
4. **Khi xuất lại**: Dropdown chỉ hiển thị serial thực đã thu hồi (không hiển thị serial đang dùng)

### Sửa dữ liệu cũ (đã thu hồi trước khi fix)

Có 5 lệnh Artisan để sửa dữ liệu cũ:

#### 1. Kiểm tra serial cần sửa
```bash
php artisan warehouse:check-virtual-serials
```

#### 2. Sửa serial (trường hợp có old_serial)
```bash
# Xem trước
php artisan warehouse:fix-virtual-serials --dry-run

# Thực thi
php artisan warehouse:fix-virtual-serials
```

#### 3. Sửa serial (trường hợp cập nhật mã thiết bị sau khi xuất)
```bash
# Xem trước tất cả
php artisan warehouse:fix-serials-from-device-codes --dry-run

# Xem trước theo mã hàng
php artisan warehouse:fix-serials-from-device-codes --dry-run --item-code=LOA-TCN30

# Thực thi
php artisan warehouse:fix-serials-from-device-codes --item-code=LOA-TCN30
```

#### 4. Debug serial cụ thể
```bash
php artisan debug:serial-mapping good 45
```

#### 5. Truy vết lịch sử serial
```bash
php artisan trace:serial 0136592
```

## Lưu ý quan trọng

1. **Virtual serial (N/A-*)** chỉ dùng nội bộ để theo dõi, không hiển thị cho người dùng
2. **Serial thực** luôn được ưu tiên khi lưu vào kho
3. **Bảng device_codes** là nguồn chân lý cho mapping giữa N/A-* và serial thực
4. **Dropdown chỉ hiển thị serial khả dụng** - không hiển thị serial đang được dùng ở phiếu xuất khác
5. Hệ thống đã được test với trường hợp thực tế và hoạt động chính xác

## Hỗ trợ

Nếu gặp vấn đề, vui lòng:
1. Chạy lệnh debug để kiểm tra: `php artisan debug:serial-mapping <item_type> <item_id>`
2. Kiểm tra log tại: `storage/logs/laravel.log`
3. Liên hệ với đội phát triển

---

**Ngày hoàn thành**: 09/03/2026
**Người thực hiện**: Kiro AI Assistant
