# Hướng dẫn khắc phục lỗi upload file lớn (>50MB)

## Vấn đề
Lỗi `POST Content-Length of 69368348 bytes exceeds the limit of 41943040 bytes` xảy ra khi upload file lớn hơn 50MB.

## Nguyên nhân
- PHP mặc định giới hạn `upload_max_filesize` và `post_max_size` là 40MB
- Cần cấu hình nhiều nơi để tăng giới hạn lên 500MB

## Các bước đã thực hiện

### 1. Cấu hình PHP Settings
Đã tạo/cập nhật các file sau:

#### `php.ini` (thư mục gốc)
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
max_file_uploads = 20
```

#### `public/php.ini`
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
max_file_uploads = 20
```

#### `.htaccess` (thư mục gốc)
```apache
php_value upload_max_filesize 500M
php_value post_max_size 500M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 512M
php_value max_file_uploads 20
php_value max_input_vars 3000
```

#### `public/.htaccess`
```apache
php_value upload_max_filesize 500M
php_value post_max_size 500M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 512M
php_value max_file_uploads 20
php_value max_input_vars 3000
```

### 2. Cấu hình Laravel

#### `public/index.php`
Thêm cấu hình PHP settings vào đầu file:
```php
// Set PHP settings for large file uploads
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);
ini_set('memory_limit', '512M');
ini_set('max_file_uploads', 20);
ini_set('max_input_vars', 3000);
```

#### `app/Http/Middleware/LargeFileUploadMiddleware.php`
Tạo middleware mới để xử lý upload file lớn.

#### `app/Http/Controllers/SoftwareController.php`
Thêm cấu hình PHP settings vào các method `store()` và `update()`.

### 3. Cập nhật Routes
Thêm middleware `large.file.upload` vào routes upload:
```php
Route::post('/', [SoftwareController::class, 'store'])->name('store')
    ->middleware([\App\Http\Middleware\CheckPermissionMiddleware::class . ':software.create', 'large.file.upload']);
```

## Cách kiểm tra

### 1. Kiểm tra cấu hình PHP
Truy cập: `http://localhost/warehouse_SGL/public/test-upload.php`

### 2. Kiểm tra qua command line
```bash
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL; echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;"
```

## Lưu ý quan trọng

### 1. Restart Web Server
Sau khi thay đổi cấu hình, cần restart web server (Apache/Nginx):
- **XAMPP**: Restart Apache
- **Laravel Sail**: `./vendor/bin/sail restart`
- **Laravel Valet**: `valet restart`

### 2. Kiểm tra Web Server
Nếu sử dụng Nginx, cần thêm vào `nginx.conf`:
```nginx
client_max_body_size 500M;
```

### 3. Kiểm tra PHP-FPM (nếu có)
Nếu sử dụng PHP-FPM, cần cấu hình trong `php-fpm.conf`:
```ini
request_terminate_timeout = 300
```

## Troubleshooting

### Nếu vẫn gặp lỗi:

1. **Kiểm tra web server logs**:
   - Apache: `logs/error.log`
   - Nginx: `logs/error.log`

2. **Kiểm tra PHP logs**:
   - XAMPP: `php/logs/php_error_log`
   - Laravel: `storage/logs/laravel.log`

3. **Kiểm tra quyền thư mục**:
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   ```

4. **Clear cache**:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

### Nếu sử dụng XAMPP:
1. Mở `xampp/apache/conf/php.ini`
2. Tìm và sửa các dòng:
   ```ini
   upload_max_filesize = 500M
   post_max_size = 500M
   max_execution_time = 300
   memory_limit = 512M
   ```
3. Restart Apache

### Nếu sử dụng Laravel Sail:
Thêm vào `docker-compose.yml`:
```yaml
services:
  laravel.test:
    environment:
      - UPLOAD_MAX_FILESIZE=500M
      - POST_MAX_SIZE=500M
      - MAX_EXECUTION_TIME=300
      - MEMORY_LIMIT=512M
```

## Kết quả mong đợi
Sau khi áp dụng tất cả các cấu hình trên, bạn sẽ có thể upload file lên đến 500MB mà không gặp lỗi. 