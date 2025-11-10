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

### 2) Lấy danh sách thiết bị theo dự án/phiếu cho thuê
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

### 3) Tạo yêu cầu hỗ trợ bảo hành/sửa chữa (Maintenance Request)
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

### 4) Cập nhật yêu cầu hỗ trợ bảo hành/sửa chữa
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

### 5) (Tuỳ chọn) Tạo/Cập nhật phiếu sửa chữa nội bộ
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


