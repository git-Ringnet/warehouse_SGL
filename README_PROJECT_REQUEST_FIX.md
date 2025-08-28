# Sửa lỗi Phiếu đề xuất triển khai dự án - Trang Edit

## Vấn đề đã gặp phải

1. **Dropdown hàng hóa không hiển thị**: Khi thêm hàng hóa mới trong trang edit, dropdown không hiển thị danh sách hàng hóa
2. **Không cập nhật được**: Khi bấm lưu, các chỉnh sửa không được cập nhật

## Nguyên nhân

1. **JavaScript không populate dropdown**: Khi tạo row mới, JavaScript không copy options từ dropdown gốc
2. **Controller validation không đúng**: Logic xử lý items trong controller có vấn đề
3. **Thiếu client-side validation**: Không có kiểm tra trước khi submit form

## Giải pháp đã thực hiện

### 1. Sửa file JavaScript (public/js/project-request-edit.js)

- **Thêm hàm `populateDropdown()`**: Copy options từ dropdown gốc sang dropdown mới
- **Sửa các hàm thêm row**: Gọi `populateDropdown()` sau khi tạo row mới
- **Thêm client-side validation**: Kiểm tra các trường bắt buộc trước khi submit
- **Thêm form validation**: Ngăn submit khi có lỗi validation

### 2. Sửa Controller (app/Http/Controllers/ProjectRequestController.php)

- **Sửa validation rules**: Cho phép cả equipment và good trong validation
- **Sửa logic xử lý items**: Chỉ tạo item khi có đầy đủ thông tin
- **Cải thiện error handling**: Xử lý lỗi tốt hơn

## Các thay đổi cụ thể

### JavaScript
```javascript
// Hàm populate dropdown với dữ liệu từ server
function populateDropdown(selectElement, itemType) {
    let sourceSelect = null;
    
    if (itemType === 'equipment') {
        sourceSelect = document.querySelector('select[name="equipment[0][id]"]');
    } else if (itemType === 'material') {
        sourceSelect = document.querySelector('select[name="material[0][id]"]');
    } else if (itemType === 'good') {
        sourceSelect = document.querySelector('select[name="good[0][id]"]');
    }
    
    if (sourceSelect) {
        Array.from(sourceSelect.options).forEach(option => {
            const newOption = option.cloneNode(true);
            selectElement.appendChild(newOption);
        });
    }
}

// Gọi populateDropdown sau khi tạo row mới
const newSelect = newRow.querySelector('select');
populateDropdown(newSelect, 'good');
```

### Controller
```php
// Sửa validation rules
$rules['equipment'] = 'nullable|array';
$rules['equipment.*.id'] = 'nullable|exists:products,id';
$rules['equipment.*.quantity'] = 'nullable|integer|min:1';

$rules['good'] = 'nullable|array';
$rules['good.*.id'] = 'nullable|exists:goods,id';
$rules['good.*.quantity'] = 'nullable|integer|min:1';

// Sửa logic xử lý items
if (!isset($item['id']) || !isset($item['quantity']) || empty($item['id']) || empty($item['quantity'])) {
    continue;
}

// Chỉ tạo item khi có model
if ($itemModel) {
    // ... set data ...
    ProjectRequestItem::create($itemData);
}
```

## Kết quả

1. ✅ **Dropdown hiển thị đúng**: Khi thêm hàng hóa mới, dropdown sẽ có đầy đủ options
2. ✅ **Form cập nhật được**: Các chỉnh sửa sẽ được lưu vào database
3. ✅ **Validation tốt hơn**: Có client-side validation để kiểm tra trước khi submit
4. ✅ **Error handling tốt hơn**: Hiển thị lỗi rõ ràng cho người dùng

## Hướng dẫn sử dụng

1. **Thêm hàng hóa**: Bấm nút "Thêm hàng hóa" → Dropdown sẽ có đầy đủ options
2. **Chọn hàng hóa**: Chọn từ dropdown → Validation sẽ kiểm tra
3. **Lưu form**: Bấm "Cập nhật" → Form sẽ được validate và lưu

## Lưu ý

- Cần đảm bảo file JavaScript được load đúng trong trang edit
- Controller cần có đầy đủ dữ liệu equipment, material, good
- Validation rules phải phù hợp với logic nghiệp vụ
