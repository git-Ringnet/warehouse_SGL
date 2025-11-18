### API Authentication (Xác thực API)

- Base URL: `{your-domain}/api`
- Tất cả ví dụ dùng `application/json`

---

### 0) Đăng nhập và lấy API Token
- POST `/api/login`
- Body:
```json
{
  "username": "admin",
  "password": "password123"
}
```

Ví dụ:
```bash
curl -X POST "{your-domain}/api/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "password123"
  }'
```

Response (thành công):
```json
{
  "success": true,
  "message": "Đăng nhập thành công",
  "data": {
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "username": "admin",
      "name": "Nguyễn Văn A",
      "email": "admin@example.com",
      "role": "admin",
      "type": "employee"
    }
  }
}
```

Response (thất bại):
```json
{
  "success": false,
  "message": "Tên đăng nhập hoặc mật khẩu không đúng."
}
```

**Lưu ý**: 
- Token có thể được sử dụng cho cả nhân viên (Employee) và khách hàng (Customer)
- Token không có thời hạn mặc định, có thể xóa bằng API logout
- Sau khi có token, thêm vào header: `Authorization: Bearer <token>`

---

### 0a) Đăng xuất và xóa Token
- POST `/api/logout`
- Header: `Authorization: Bearer <token>`

Ví dụ:
```bash
curl -X POST "{your-domain}/api/logout" \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890"
```

Response:
```json
{
  "success": true,
  "message": "Đăng xuất thành công"
}
```

---

### 0b) Lấy thông tin người dùng hiện tại
- GET `/api/user`
- Header: `Authorization: Bearer <token>`

Ví dụ:
```bash
curl -X GET "{your-domain}/api/user" \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890"
```

Response (nhân viên):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "name": "Nguyễn Văn A",
    "email": "admin@example.com",
    "role": "admin",
    "type": "employee",
    "phone": "0123456789",
    "department": "IT"
  }
}
```

Response (khách hàng):
```json
{
  "success": true,
  "data": {
    "id": 10,
    "username": "customer1",
    "name": "Nguyễn Văn B",
    "email": "customer@example.com",
    "role": "customer",
    "type": "customer",
    "customer_id": 5,
    "customer": {
      "id": 5,
      "name": "Nguyễn Văn B",
      "company_name": "Công ty XYZ"
    }
  }
}
```

---

### Warranty, Maintenance & Repair APIs

- Base URL: `{your-domain}/api`
- Auth: nếu hệ thống yêu cầu, thêm `Authorization: Bearer <token>` vào header
- Tất cả ví dụ dùng `application/json`

---

### 1) Tra cứu thông tin bảo hành
- GET `/api/repairs/search-warranty?warranty_code={ma_bao_hanh_hoac_serial}`
- Hoặc: GET `/api/warranty/check?warranty_code={ma_bao_hanh}`

**Lưu ý**: API này có thể tìm cả các mã bảo hành đã hết hạn.

Ví dụ:
```bash
curl -X GET "{your-domain}/api/repairs/search-warranty?warranty_code=BH2025100007"
```
```bash
curl -X GET "{your-domain}/api/warranty/check?warranty_code=BH2025100007"
```

Response (thành công):
```json
{
  "success": true,
  "warranty": {
    "warranty_code": "BH2025100007",
    "project_name": "RNT-251021110 - Phiếu thuê 21/10 (Công Ty Lộc 1)",
    "status": "active",
    "activated_at": "2025-01-15 10:30:00",
    "warranty_end_date": "2026-01-15",
    "devices": [
      {
        "code": "SP-TP161001",
        "name": "Thành phẩm 1 16/10",
        "quantity": 1,
        "serial_numbers": [],
        "type": "product"
      }
    ]
  }
}
```

Response (thất bại):
```json
{
  "success": false,
  "message": "Không tìm thấy thông tin bảo hành với mã: BH202510000"
}
```

**Ghi chú**:
- `status`: Trạng thái bảo hành (`active`, `expired`, `claimed`, `void`)
- `activated_at`: Thời điểm kích hoạt bảo hành (format: `Y-m-d H:i:s`)
- `warranty_end_date`: Ngày kết thúc bảo hành (format: `Y-m-d`). Nếu có `activated_at`, sẽ tính từ `activated_at + warranty_period_months`, ngược lại dùng `warranty_end_date` từ database
- `devices`: Chỉ hiển thị các thiết bị thuộc dạng contract (loại bỏ backup và mixed)
- `repair_history`: Đã được tách thành API riêng

---

### 2a) Lấy TẤT CẢ phiếu bảo trì dự án (MaintenanceRequest) - Đơn giản, không lọc, không phân trang
- GET `/api/maintenance-requests/all`
- **Lưu ý**: API này trả về TẤT CẢ phiếu bảo trì dự án, không có filter hay phân trang. Chỉ cần gọi API là lấy được tất cả.

Ví dụ:
```bash
curl -X GET "{your-domain}/api/maintenance-requests/all"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 101,
      "request_code": "CUS-MAINT-20250101-0001",
      "request_date": "2025-01-01",
      "maintenance_date": "2025-01-05",
      "maintenance_type": "repair",
      "status": "pending",
      "project_type": "project",
      "project_id": 60,
      "project_name": "Dự án ABC",
      "customer_id": 10,
      "customer_name": "Công ty XYZ",
      "customer_phone": "0123456789",
      "customer_email": "contact@xyz.com",
      "customer_address": "123 Đường ABC",
      "notes": "Khách báo lỗi",
      "maintenance_reason": "Khách báo lỗi",
      "reject_reason": null,
      "proposer": {
        "id": 5,
        "name": "Nguyễn Văn A",
        "username": "admin",
        "email": "admin@example.com"
      },
      "customer": {
        "id": 10,
        "name": "Nguyễn Văn B",
        "company_name": "Công ty XYZ",
        "phone": "0123456789",
        "email": "contact@xyz.com"
      },
      "products_count": 2,
      "products": [
        {
          "id": 1,
          "product_id": 100,
          "product_code": "PROD-001",
          "product_name": "Sản phẩm A",
          "serial_number": "SN123456",
          "type": "Thành phẩm",
          "quantity": 1
        }
      ],
      "created_at": "2025-01-01 10:00:00",
      "updated_at": "2025-01-01 10:00:00"
    }
  ],
  "total": 50
}
```

---

### 2a-2) Lấy danh sách phiếu bảo trì dự án (MaintenanceRequest) - Có lọc và phân trang
- GET `/api/maintenance-requests/project`
- **Lưu ý**: API này có đầy đủ tính năng lọc và phân trang. Nếu cần lọc hoặc phân trang, sử dụng API này.
- Query parameters (tất cả đều tùy chọn):
  - `search`: Tìm kiếm theo mã phiếu, tên dự án, tên khách hàng, tên người đề xuất
  - `status`: Lọc theo trạng thái (pending, approved, rejected, in_progress, completed, canceled)
  - `maintenance_type`: Lọc theo loại bảo trì (maintenance, repair, replacement, upgrade, other)
  - `project_type`: Lọc theo loại dự án (project, rental)
  - `project_id`: Lọc theo ID dự án/phiếu cho thuê
  - `customer_id`: Lọc theo ID khách hàng
  - `proposer_id`: Lọc theo ID người đề xuất
  - `request_date_from`: Lọc từ ngày yêu cầu (dd/mm/YYYY hoặc YYYY-mm-dd)
  - `request_date_to`: Lọc đến ngày yêu cầu (dd/mm/YYYY hoặc YYYY-mm-dd)
  - `maintenance_date_from`: Lọc từ ngày bảo trì (dd/mm/YYYY hoặc YYYY-mm-dd)
  - `maintenance_date_to`: Lọc đến ngày bảo trì (dd/mm/YYYY hoặc YYYY-mm-dd)
  - `sort_by`: Sắp xếp theo trường (id, request_code, request_date, maintenance_date, created_at, updated_at) - mặc định: created_at
  - `sort_order`: Thứ tự sắp xếp (asc, desc) - mặc định: desc
  - `per_page`: Số bản ghi mỗi trang (1-100) - mặc định: 15
  - `page`: Số trang - mặc định: 1

Ví dụ:
```bash
# Lấy TẤT CẢ phiếu bảo trì dự án (không filter)
curl -X GET "{your-domain}/api/maintenance-requests/project"
```

```bash
# Lấy tất cả với phân trang
curl -X GET "{your-domain}/api/maintenance-requests/project?per_page=50"
```

```bash
# Lọc theo trạng thái
curl -X GET "{your-domain}/api/maintenance-requests/project?status=pending&per_page=20"
```

```bash
# Tìm kiếm và lọc
curl -X GET "{your-domain}/api/maintenance-requests/project?search=REQ&maintenance_type=repair&status=approved"
```

```bash
# Lọc theo khoảng thời gian
curl -X GET "{your-domain}/api/maintenance-requests/project?request_date_from=01/01/2025&request_date_to=31/01/2025"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 101,
      "request_code": "CUS-MAINT-20250101-0001",
      "request_date": "2025-01-01",
      "maintenance_date": "2025-01-05",
      "maintenance_type": "repair",
      "status": "pending",
      "project_type": "project",
      "project_id": 60,
      "project_name": "Dự án ABC",
      "customer_id": 10,
      "customer_name": "Công ty XYZ",
      "customer_phone": "0123456789",
      "customer_email": "contact@xyz.com",
      "customer_address": "123 Đường ABC",
      "notes": "Khách báo lỗi",
      "maintenance_reason": "Khách báo lỗi",
      "reject_reason": null,
      "proposer": {
        "id": 5,
        "name": "Nguyễn Văn A",
        "username": "admin",
        "email": "admin@example.com"
      },
      "customer": {
        "id": 10,
        "name": "Nguyễn Văn B",
        "company_name": "Công ty XYZ",
        "phone": "0123456789",
        "email": "contact@xyz.com"
      },
      "products_count": 2,
      "products": [
        {
          "id": 1,
          "product_id": 100,
          "product_code": "PROD-001",
          "product_name": "Sản phẩm A",
          "serial_number": "SN123456",
          "type": "Thành phẩm",
          "quantity": 1
        }
      ],
      "created_at": "2025-01-01 10:00:00",
      "updated_at": "2025-01-01 10:00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 50,
    "last_page": 4,
    "from": 1,
    "to": 15
  }
}
```

---

### 2b) Lấy TẤT CẢ phiếu khách yêu cầu bảo trì (CustomerMaintenanceRequest) - Đơn giản, không lọc, không phân trang
- GET `/api/customer-maintenance-requests/all`
- **Lưu ý**: API này trả về TẤT CẢ phiếu khách yêu cầu bảo trì, không có filter hay phân trang. Chỉ cần gọi API là lấy được tất cả.

Ví dụ:
```bash
curl -X GET "{your-domain}/api/customer-maintenance-requests/all"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 201,
      "request_code": "CUST-MAINT-250101-001",
      "request_date": "2025-01-01",
      "maintenance_reason": "Thiết bị bị lỗi",
      "maintenance_details": "Màn hình không hiển thị",
      "priority": "high",
      "status": "pending",
      "item_source": "project",
      "project_id": 60,
      "rental_id": null,
      "project_name": "Dự án ABC",
      "project_description": "Mô tả dự án",
      "selected_item": "product:100:SN123456",
      "estimated_cost": 500000.00,
      "customer_id": 10,
      "customer_name": "Công ty XYZ",
      "customer_phone": "0123456789",
      "customer_email": "contact@xyz.com",
      "customer_address": "123 Đường ABC",
      "notes": "Ghi chú",
      "rejection_reason": null,
      "approved_by": null,
      "approved_at": null,
      "customer": {
        "id": 10,
        "name": "Nguyễn Văn B",
        "company_name": "Công ty XYZ",
        "phone": "0123456789",
        "email": "contact@xyz.com"
      },
      "project": {
        "id": 60,
        "project_code": "PRJ-001",
        "project_name": "Dự án ABC"
      },
      "rental": null,
      "approved_by_user": null,
      "created_at": "2025-01-01 10:00:00",
      "updated_at": "2025-01-01 10:00:00"
    }
  ],
  "total": 30
}
```

---

### 2b-2) Lấy danh sách phiếu khách yêu cầu bảo trì (CustomerMaintenanceRequest) - Có lọc và phân trang
- GET `/api/customer-maintenance-requests`
- **Lưu ý**: API này có đầy đủ tính năng lọc và phân trang. Nếu cần lọc hoặc phân trang, sử dụng API này.
- Query parameters (tất cả đều tùy chọn):
  - `search`: Tìm kiếm theo mã phiếu, tên dự án, tên khách hàng, lý do bảo trì
  - `status`: Lọc theo trạng thái (pending, approved, rejected, in_progress, completed, canceled)
  - `priority`: Lọc theo mức độ ưu tiên (low, medium, high, urgent)
  - `item_source`: Lọc theo nguồn thiết bị (project, rental)
  - `project_id`: Lọc theo ID dự án
  - `rental_id`: Lọc theo ID phiếu cho thuê
  - `customer_id`: Lọc theo ID khách hàng
  - `approved_by`: Lọc theo ID người duyệt
  - `request_date_from`: Lọc từ ngày yêu cầu (dd/mm/YYYY hoặc YYYY-mm-dd)
  - `request_date_to`: Lọc đến ngày yêu cầu (dd/mm/YYYY hoặc YYYY-mm-dd)
  - `approved_at_from`: Lọc từ ngày duyệt (dd/mm/YYYY hoặc YYYY-mm-dd)
  - `approved_at_to`: Lọc đến ngày duyệt (dd/mm/YYYY hoặc YYYY-mm-dd)
  - `sort_by`: Sắp xếp theo trường (id, request_code, request_date, priority, status, created_at, updated_at, approved_at) - mặc định: created_at
  - `sort_order`: Thứ tự sắp xếp (asc, desc) - mặc định: desc
  - `per_page`: Số bản ghi mỗi trang (1-100) - mặc định: 15
  - `page`: Số trang - mặc định: 1

Ví dụ:
```bash
# Lấy TẤT CẢ phiếu khách yêu cầu bảo trì (không filter)
curl -X GET "{your-domain}/api/customer-maintenance-requests"
```

```bash
# Lấy tất cả với phân trang
curl -X GET "{your-domain}/api/customer-maintenance-requests?per_page=50"
```

```bash
# Lọc theo trạng thái và mức độ ưu tiên
curl -X GET "{your-domain}/api/customer-maintenance-requests?status=pending&priority=high"
```

```bash
# Lọc theo khách hàng và nguồn thiết bị
curl -X GET "{your-domain}/api/customer-maintenance-requests?customer_id=10&item_source=project"
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 201,
      "request_code": "CUST-MAINT-250101-001",
      "request_date": "2025-01-01",
      "maintenance_reason": "Thiết bị bị lỗi",
      "maintenance_details": "Màn hình không hiển thị",
      "priority": "high",
      "status": "pending",
      "item_source": "project",
      "project_id": 60,
      "rental_id": null,
      "project_name": "Dự án ABC",
      "project_description": "Mô tả dự án",
      "selected_item": "product:100:SN123456",
      "estimated_cost": 500000.00,
      "customer_id": 10,
      "customer_name": "Công ty XYZ",
      "customer_phone": "0123456789",
      "customer_email": "contact@xyz.com",
      "customer_address": "123 Đường ABC",
      "notes": "Ghi chú",
      "rejection_reason": null,
      "approved_by": null,
      "approved_at": null,
      "customer": {
        "id": 10,
        "name": "Nguyễn Văn B",
        "company_name": "Công ty XYZ",
        "phone": "0123456789",
        "email": "contact@xyz.com"
      },
      "project": {
        "id": 60,
        "project_code": "PRJ-001",
        "project_name": "Dự án ABC"
      },
      "rental": null,
      "approved_by_user": null,
      "created_at": "2025-01-01 10:00:00",
      "updated_at": "2025-01-01 10:00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 30,
    "last_page": 2,
    "from": 1,
    "to": 15
  }
}
```

---

### 2b-3) Tạo phiếu khách yêu cầu bảo trì
- POST `/api/customer-maintenance-requests`
- Body:
```json
{
  "customer_id": 10,
  "project_name": "Dự án ABC",
  "project_description": "Mô tả dự án",
  "maintenance_reason": "Thiết bị bị lỗi",
  "maintenance_details": "Màn hình không hiển thị",
  "priority": "high",
  "item_source": "project",
  "project_id": 60,
  "request_date": "01/01/2025",
  "estimated_cost": 500000,
  "selected_item": "product:100:SN123456",
  "notes": "Ghi chú"
}
```

**Lưu ý**:
- `item_source`: bắt buộc, giá trị: `project` hoặc `rental`
- Nếu `item_source` = `project` thì `project_id` bắt buộc
- Nếu `item_source` = `rental` thì `rental_id` bắt buộc
- `priority`: bắt buộc, giá trị: `low`, `medium`, `high`, `urgent`
- `request_date`: tùy chọn, định dạng: `dd/mm/YYYY` hoặc `YYYY-mm-dd`, mặc định là ngày hiện tại
- `selected_item`: tùy chọn, định dạng: `{type}:{id}:{serial_number}` (serial_number là tùy chọn)
  - `type`: loại item, giá trị: `product` hoặc `good`
  - `id`: ID của sản phẩm hoặc hàng hóa
  - `serial_number`: số serial (tùy chọn, chỉ cần nếu có)
  - Ví dụ: `"product:100:SN123456"` (sản phẩm ID 100, serial SN123456)
  - Ví dụ: `"product:100"` (sản phẩm ID 100, không có serial)
  - Ví dụ: `"good:50:SN789012"` (hàng hóa ID 50, serial SN789012)

Ví dụ:
```bash
curl -X POST "{your-domain}/api/customer-maintenance-requests" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 10,
    "project_name": "Dự án ABC",
    "maintenance_reason": "Thiết bị bị lỗi",
    "priority": "high",
    "item_source": "project",
    "project_id": 60
  }'
```

Response (thành công):
```json
{
  "success": true,
  "message": "Phiếu khách yêu cầu bảo trì đã được tạo thành công.",
  "data": {
    "customer_maintenance_request": {
      "id": 201,
      "request_code": "CUST-MAINT-250101-001",
      "status": "pending",
      "priority": "high",
      "project_name": "Dự án ABC",
      "customer_name": "Công ty XYZ"
    }
  }
}
```

Response (lỗi validation):
```json
{
  "success": false,
  "message": "Dữ liệu không hợp lệ",
  "errors": {
    "project_name": ["Trường project name là bắt buộc."],
    "maintenance_reason": ["Trường maintenance reason là bắt buộc."]
  }
}
```

---

### 2b-4) Cập nhật phiếu khách yêu cầu bảo trì
- PUT/PATCH `/api/customer-maintenance-requests/{id}`
- **Lưu ý**: Chỉ có thể cập nhật khi phiếu ở trạng thái `pending`
- Body (tất cả các trường đều tùy chọn, chỉ cập nhật các trường được gửi):
```json
{
  "project_name": "Dự án ABC (cập nhật)",
  "project_description": "Mô tả mới",
  "maintenance_reason": "Lý do mới",
  "maintenance_details": "Chi tiết mới",
  "priority": "urgent",
  "item_source": "rental",
  "rental_id": 5,
  "request_date": "02/01/2025",
  "estimated_cost": 600000,
  "selected_item": "product:101:SN789012",
  "notes": "Ghi chú mới"
}
```

**Lưu ý về `selected_item`**:
- Định dạng: `{type}:{id}:{serial_number}` (serial_number là tùy chọn)
- `type`: loại item - `product` (thành phẩm) hoặc `good` (hàng hóa)
- `id`: ID của sản phẩm/hàng hóa trong database
- `serial_number`: số serial của thiết bị (tùy chọn, chỉ cần nếu thiết bị có serial)
- Ví dụ:
  - `"product:100:SN123456"` - Thành phẩm ID 100, serial SN123456
  - `"product:100"` - Thành phẩm ID 100, không có serial
  - `"good:50:SN789012"` - Hàng hóa ID 50, serial SN789012
  - `"good:50"` - Hàng hóa ID 50, không có serial

Ví dụ:
```bash
curl -X PATCH "{your-domain}/api/customer-maintenance-requests/201" \
  -H "Content-Type: application/json" \
  -d '{
    "priority": "urgent",
    "maintenance_details": "Cập nhật chi tiết"
  }'
```

Response (thành công):
```json
{
  "success": true,
  "message": "Phiếu khách yêu cầu bảo trì đã được cập nhật thành công.",
  "data": {
    "customer_maintenance_request": {
      "id": 201,
      "request_code": "CUST-MAINT-250101-001",
      "status": "pending",
      "priority": "urgent",
      "project_name": "Dự án ABC",
      "customer_name": "Công ty XYZ"
    }
  }
}
```

Response (lỗi - phiếu đã được duyệt):
```json
{
  "success": false,
  "message": "Không thể cập nhật phiếu yêu cầu đã được duyệt hoặc đã xử lý."
}
```

---

### 2c) Lấy danh sách phiếu yêu cầu sửa chữa & bảo trì (Tương thích ngược - giữ lại)
- GET `/api/maintenance-requests`
- Lưu ý: API này tương thích với API `/api/maintenance-requests/project`, khuyến nghị sử dụng API riêng cho từng loại phiếu

---

### 3) Lấy danh sách thiết bị theo dự án/phiếu cho thuê
- POST `/api/maintenance-requests/devices`
- Body:
```bash
curl -X POST "{your-domain}/api/maintenance-requests/devices" \
  -H "Content-Type: application/json" \
  -d '{
    "project_type": "project",   # hoặc "rental"
    "project_id": 60             # ID dự án/phiếu cho thuê
  }'
```

Response:
```json
{
  "devices": [
    {
      "code": "SP-TP161001",
      "name": "Thành phẩm 1 16/10",
      "type": "product"
    },
    {
      "code": "HH-001",
      "name": "Hàng hóa 1",
      "type": "good"
    }
  ]
}
```

**Lưu ý**:
- `type`: Trả về giá trị `"product"` hoặc `"good"` (không phải "Thành phẩm" hoặc "Hàng hoá") để đồng bộ với các API khác
- API chỉ trả về danh sách thiết bị duy nhất (loại bỏ trùng lặp), không bao gồm serial number

---

### 3a) Lấy danh sách serial thiết bị theo device_id và project_id
- POST `/api/maintenance-requests/device-serials`
- Body:
```bash
curl -X POST "{your-domain}/api/maintenance-requests/device-serials" \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 123,            # ID thiết bị (product_id hoặc good_id)
    "project_type": "project",   # hoặc "rental"
    "project_id": 60,            # ID dự án/phiếu cho thuê
    "item_type": "product",      # "product" hoặc "good" (tùy chọn, nhưng nên có để chính xác)
    "category": "contract"       # "contract" hoặc "backup" (tùy chọn, nếu không có thì lấy cả 2)
  }'
```

Response:
```json
{
  "serial": [
    "SN123456",
    "SN789012",
    "N/A",
    "N/A-2",
    "N/A-3"
  ]
}
```

**Lưu ý**:
- Nếu có nhiều "N/A", sẽ được đánh số thành "N/A", "N/A-2", "N/A-3", ...
- Serial sử dụng `SerialDisplayHelper` để lấy serial hiển thị (có thể đã đổi tên trong `device_codes`)
- `device_id`: ID thiết bị - là `product_id` hoặc `good_id` (ID của sản phẩm/hàng hóa)
- `item_type`: Loại thiết bị - `"product"` (thành phẩm) hoặc `"good"` (hàng hóa)
  - Nếu không có `item_type`, API sẽ tìm cả product và good (có thể trả về nhiều kết quả nếu trùng ID)
  - Nên cung cấp `item_type` để kết quả chính xác
- `category`: Loại category - `"contract"` (hợp đồng) hoặc `"backup"` (dự phòng)
  - Nếu không có `category`, API sẽ lấy cả contract và backup (giống trang show dự án/cho thuê)
  - Nên cung cấp `category` nếu chỉ cần một loại
- API sẽ tìm tất cả dispatch items có `item_id` khớp với `device_id`, `item_type` và `category` trong dự án/phiếu cho thuê, sau đó gộp tất cả serial lại

---

### 3b) Lấy danh sách dự án/phiếu cho thuê dựa trên project_type và thông tin khách hàng
- POST `/api/maintenance-requests/projects-or-rentals`
- Body:
```bash
curl -X POST "{your-domain}/api/maintenance-requests/projects-or-rentals" \
  -H "Content-Type: application/json" \
  -d '{
    "project_type": "project",        # "project" hoặc "rental"
    "customer_id": 1,                 # (tùy chọn) ID khách hàng
    "customer_name": "ABC",           # (tùy chọn) Tên khách hàng
    "customer_phone": "0123456789",   # (tùy chọn) Số điện thoại
    "customer_email": "abc@example.com" # (tùy chọn) Email
  }'
```

Response:
```json
{
  "projects": [
    {
      "project_code": "PRJ-251031229",
      "project_name": "ABCXTZ"
    },
    {
      "project_code": "PRJ-251031230",
      "project_name": "XYZ Company"
    }
  ]
}
```

**Lưu ý**:
- Có thể filter theo một trong các thông tin khách hàng: `customer_id`, `customer_name`, `customer_phone`, hoặc `customer_email`
- Nếu không có filter nào, sẽ trả về tất cả dự án/phiếu cho thuê
- Với `project_type: "rental"`, `project_code` sẽ là `rental_code` và `project_name` sẽ là `rental_name`

---

### 4) Tạo yêu cầu hỗ trợ bảo hành/sửa chữa (Maintenance Request)
- POST `/api/maintenance-requests`
- Body mẫu:
```bash
curl -X POST "{your-domain}/api/maintenance-requests" \
  -H "Content-Type: application/json" \
  -d '{
    "project_type": "project",                 # project | rental
    "project_id": 60,
    "maintenance_type": "repair",              # maintenance|repair|replacement|upgrade|other
    "proposer_username": "admin",              # hoặc proposer_id / proposer_email
    "request_date": "10/11/2025",
    "maintenance_date": "12/11/2025",
    "notes": "Khách báo lỗi",
    "selected_devices": "[\"123_0\",\"123_1\"]"
  }'
```

**Response thành công:**
```json
{
  "success": true,
  "message": "Yêu cầu hỗ trợ đã được tạo thành công (pending).",
  "data": {
    "maintenance_request": {
      "request_date": "10/11/2025",
      "proposer_username": "admin",
      "request_code": "CUS-MAINT-20251112-0034",
      "status": "pending",
      "project_code": "PRJ-251031229",
      "maintenance_type": "repair",
      "maintenance_reason": "Khách báo lỗi",
      "selected_devices": ["123_0", "123_1"]
    }
  }
}
```

**Response thất bại:**
```json
{
  "success": false,
  "message": "Có lỗi xảy ra khi tạo yêu cầu: No query results for model [App\\Models\\Project] 6"
}
```

**Lưu ý**:
- `request_code`: Phiếu yêu cầu hỗ trợ tạo bởi khách hàng có mã là `CUS-MAINT`, không phải `REQ-MAINT`
- `request_date`: Format `dd/mm/YYYY`
- `selected_devices`: Mảng các ID thiết bị dạng `"dispatchItemId_index"`

---

### 5) Cập nhật yêu cầu hỗ trợ bảo hành/sửa chữa - Không cần API này
<!--
- PUT/PATCH `/api/maintenance-requests/{id}`
- Body mẫu:
```bash
curl -X PATCH "{your-domain}/api/maintenance-requests/101" \
  -H "Content-Type: application/json" \
  -d '{
    "maintenance_type": "repair",
    "notes": "Đã tiếp nhận",
    "status": "pending",
    "selected_devices": "[\"123_0\",\"123_1\"]"
  }'
```
- Response:
```json
{
  "success": true,
  "message": "Yêu cầu hỗ trợ đã được cập nhật.",
  "data": {
    "maintenance_request": {
      "id": 101,
      "request_code": "REQ-2025-0001",
      "status": "pending"
    }
  }
}
-->
```

---

### 6) (Tuỳ chọn) Tạo/Cập nhật phiếu sửa chữa nội bộ
- Các API repair đã có sẵn:
  - POST `/api/repairs`
  - PUT/PATCH `/api/repairs/{id}`
- Nếu khách chỉ cần workflow bảo hành/sửa chữa thông qua yêu cầu hỗ trợ (maintenance request) thì có thể bỏ qua phần này.

---

### Postman Collection
Import file `docs/api/warranty-repair.postman_collection.json`, chỉnh các biến:
- `base_url`, `project_id`, `proposer_username` (hoặc proposer_id/proposer_email)
- `dispatch_item_id` (lấy từ API devices)
- `maintenance_request_id` (tự động set sau khi gọi API tạo nếu dùng Postman)
- `warranty_code`, `repair_id` (nếu cần dùng API repair).



