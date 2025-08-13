# Cấu hình Server Production cho Upload File Lớn

## 🚨 **QUAN TRỌNG: Cấu hình này cần được áp dụng trên SERVER PRODUCTION**

### 1. **Apache Configuration**

#### Nếu sử dụng Apache, thêm vào `apache2.conf` hoặc virtual host:
```apache
# Large file upload settings
LimitRequestBody 524288000
TimeOut 300
ProxyTimeout 300

<Directory /var/www/html>
    php_value upload_max_filesize 500M
    php_value post_max_size 500M
    php_value max_execution_time 300
    php_value max_input_time 300
    php_value memory_limit 512M
    php_value max_file_uploads 20
    php_value max_input_vars 3000
</Directory>
```

### 2. **Nginx Configuration**

#### Thêm vào `nginx.conf` hoặc site configuration:
```nginx
http {
    client_max_body_size 500M;
    client_body_timeout 300s;
    client_header_timeout 300s;
    proxy_read_timeout 300s;
    proxy_connect_timeout 300s;
    proxy_send_timeout 300s;
    
    # PHP-FPM settings
    fastcgi_read_timeout 300;
    fastcgi_send_timeout 300;
    fastcgi_connect_timeout 300;
}
```

### 3. **PHP-FPM Configuration**

#### Sửa file `php-fpm.conf` hoặc pool configuration:
```ini
[www]
request_terminate_timeout = 300
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
upload_max_filesize = 500M
post_max_size = 500M
max_file_uploads = 20
max_input_vars = 3000
```

### 4. **PHP Configuration**

#### Sửa file `php.ini`:
```ini
; File upload settings
upload_max_filesize = 500M
post_max_size = 500M
max_file_uploads = 20
max_input_vars = 3000

; Memory and execution settings
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

; Session settings
session.gc_maxlifetime = 1440
session.cookie_lifetime = 0

; Output buffering
output_buffering = 4096

; Timeouts
default_socket_timeout = 300
```

### 5. **Laravel Environment Variables**

#### Thêm vào file `.env` trên server:
```env
# Large file upload settings
UPLOAD_MAX_FILESIZE=500M
POST_MAX_SIZE=500M
MAX_EXECUTION_TIME=300
MEMORY_LIMIT=512M
```

### 6. **Docker Configuration (nếu sử dụng Docker)**

#### Thêm vào `docker-compose.yml`:
```yaml
services:
  app:
    environment:
      - UPLOAD_MAX_FILESIZE=500M
      - POST_MAX_SIZE=500M
      - MAX_EXECUTION_TIME=300
      - MEMORY_LIMIT=512M
    volumes:
      - ./php.ini:/usr/local/etc/php/php.ini
```

### 7. **Cấu hình Cloudflare (nếu có)**

#### Thêm Page Rules:
- **URL Pattern**: `*yourdomain.com/software*`
- **Settings**: 
  - Cache Level: Bypass
  - Security Level: Medium
  - Browser Integrity Check: Off

### 8. **Kiểm tra sau khi deploy**

#### Tạo file test trên server:
```php
<?php
// File: public/test-server-config.php
echo "<h2>Server Configuration Test</h2>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . "</p>";

// Test upload form
echo "<form method='post' enctype='multipart/form-data'>";
echo "<input type='file' name='test_file' accept='*/*'>";
echo "<input type='submit' value='Test Upload'>";
echo "</form>";

if ($_FILES) {
    if (isset($_FILES['test_file']) && $_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        echo "<p style='color: green;'>✓ Upload successful! File size: " . number_format($_FILES['test_file']['size']) . " bytes</p>";
    } else {
        echo "<p style='color: red;'>✗ Upload failed! Error: " . $_FILES['test_file']['error'] . "</p>";
    }
}
?>
```

### 9. **Restart Services**

Sau khi cấu hình, restart các services:
```bash
# Apache
sudo systemctl restart apache2

# Nginx
sudo systemctl restart nginx

# PHP-FPM
sudo systemctl restart php8.1-fpm

# Laravel (clear cache)
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 10. **Monitoring**

#### Kiểm tra logs:
```bash
# Apache logs
tail -f /var/log/apache2/error.log

# Nginx logs
tail -f /var/log/nginx/error.log

# PHP logs
tail -f /var/log/php8.1-fpm.log

# Laravel logs
tail -f storage/logs/laravel.log
```

## ⚠️ **Lưu ý quan trọng:**

1. **Bảo mật**: Cấu hình này cho phép upload file lớn, cần kiểm tra file type và virus scan
2. **Storage**: Đảm bảo server có đủ dung lượng lưu trữ
3. **Bandwidth**: Upload file lớn có thể ảnh hưởng đến performance
4. **Backup**: Cấu hình backup cho các file đã upload
5. **Monitoring**: Theo dõi disk usage và performance

## 🔧 **Troubleshooting:**

Nếu vẫn gặp lỗi trên server:
1. Kiểm tra web server logs
2. Kiểm tra PHP logs
3. Kiểm tra Laravel logs
4. Đảm bảo tất cả services đã được restart
5. Kiểm tra quyền thư mục storage 