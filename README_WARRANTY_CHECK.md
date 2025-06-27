# Hướng dẫn kiểm tra hết hạn bảo hành dự án

## Command kiểm tra hết hạn bảo hành

Để kiểm tra và thông báo các dự án sắp hết hạn bảo hành, sử dụng command sau:

```bash
php artisan warranty:check-expiry
```

### Tùy chọn

- `--days=30`: Số ngày trước khi hết hạn để thông báo (mặc định: 30 ngày)

### Ví dụ

```bash
# Kiểm tra các dự án hết hạn trong 30 ngày tới (mặc định)
php artisan warranty:check-expiry

# Kiểm tra các dự án hết hạn trong 7 ngày tới
php artisan warranty:check-expiry --days=7

# Kiểm tra các dự án hết hạn trong 60 ngày tới
php artisan warranty:check-expiry --days=60
```

## Cách hoạt động

1. Command sẽ tìm tất cả các dự án có nhân viên phụ trách
2. Tính toán ngày kết thúc bảo hành = ngày bắt đầu + thời gian bảo hành
3. Kiểm tra xem ngày kết thúc bảo hành có trong khoảng thời gian cần thông báo không
4. Tạo thông báo cho nhân viên phụ trách dự án
5. Tránh spam bằng cách chỉ thông báo 1 lần trong 7 ngày

## Loại thông báo

- **Error (đỏ)**: Dự án đã hết hạn bảo hành hoặc còn ≤ 7 ngày
- **Warning (vàng)**: Dự án còn 8-30 ngày
- **Info (xanh)**: Dự án còn > 30 ngày

## Thiết lập tự động

Để chạy tự động hàng ngày, thêm vào cron job:

```bash
# Mở crontab
crontab -e

# Thêm dòng sau để chạy hàng ngày lúc 9:00 sáng
0 9 * * * cd /path/to/your/project && php artisan warranty:check-expiry
```

## Test thủ công

Để test command, có thể chạy:

```bash
# Test với dự án có thời gian bảo hành ngắn
php artisan warranty:check-expiry --days=365
```

## Lưu ý

- Command chỉ thông báo cho các dự án có nhân viên phụ trách
- Thông báo sẽ được gửi đến nhân viên phụ trách dự án
- Có thể xem thông báo trong sidebar hoặc trang notifications 