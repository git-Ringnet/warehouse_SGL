### Warranty, Maintenance & Repair APIs

- Base URL: `{your-domain}/api`
- Auth: nếu hệ thống yêu cầu, thêm `Authorization: Bearer <token>`
- Tất cả ví dụ dùng `application/json`

---

### 1) Tra cứu thông tin bảo hành
- GET `/api/repairs/search-warranty?warranty_code={ma_bao_hanh_hoac_serial}`
- Hoặc: GET `/api/warranty/check?warranty_code={ma_bao_hanh}`

Ví dụ:
```bash
curl -X GET "{your-domain}/api/repairs/search-warranty?warranty_code=WAR-2025-001"
```
```bash
curl -X GET "{your-domain}/api/warranty/check?warranty_code=WAR-2025-001"
```

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
      "request_code": "REQ-MAINT-20250101-0001",
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
      "request_code": "REQ-MAINT-20250101-0001",
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
- Kết quả trả về mảng thiết bị, mỗi phần tử có `id` dạng `"dispatchItemId_index"`. Dùng các id này cho `selected_devices` ở bước tạo/cập nhật yêu cầu bảo trì.

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
- Response (rút gọn):
```json
{
  "success": true,
  "data": {
    "maintenance_request": {
      "id": 101,
      "request_code": "REQ-2025-0001",
      "status": "pending"
    }
  }
}
```

---

### 5) Cập nhật yêu cầu hỗ trợ bảo hành/sửa chữa
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


