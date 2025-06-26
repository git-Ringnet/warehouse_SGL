# 📋 Hướng dẫn sử dụng ChangeLog Helper

## 🎯 Cách sử dụng trong Controller

Bạn có 2 cách để sử dụng ChangeLog:

### 📌 **Cách 1: Sử dụng ChangeLogHelper (Đơn giản nhất)**

```php
<?php
use App\Helpers\ChangeLogHelper;

// Trong MaterialController khi nhập kho
public function import(Request $request) 
{
    // Logic nhập kho...
    
    // Lấy thông tin nhà cung cấp
    $supplier = Supplier::find($request->supplier_id);
    $supplierName = $supplier ? $supplier->name : 'Không xác định';
    
    // Log nhập kho - mô tả chỉ là tên nhà cung cấp
    ChangeLogHelper::nhapKho(
        $material->code,          // Mã vật tư
        $material->name,          // Tên vật tư
        $quantity,                // Số lượng
        $importCode,              // Mã phiếu nhập
        $supplierName,            // Mô tả = tên nhà cung cấp
        [                         // Thông tin chi tiết
            'supplier_id' => $request->supplier_id,
            'warehouse_id' => $request->warehouse_id,
            'price' => 100000,
            'batch_number' => 'BATCH001'
        ],
        $material_notes           // Chú thích = ghi chú vật tư
    );
}

// Trong DispatchController khi xuất kho
public function export(Request $request)
{
    // Logic xuất kho...
    
    ChangeLogHelper::xuatKho(
        $product->code,
        $product->name, 
        $quantity,
        $dispatchCode,
        'Xuất cho khách hàng XYZ'
    );
}

// Trong AssemblyController khi lắp ráp
public function store(Request $request)
{
    // Logic lắp ráp...
    
    ChangeLogHelper::lapRap(
        $material->code,
        $material->name,
        $usedQuantity,
        $assemblyCode,
        'Sử dụng trong lắp ráp sản phẩm IoT'
    );
}
```

### 📌 **Cách 2: Sử dụng trực tiếp ChangeLogController**

```php
<?php
use App\Http\Controllers\ChangeLogController;

// Log nhập kho
ChangeLogController::logImport($code, $name, $quantity, $importCode, $description, $detailedInfo, $notes);

// Log xuất kho  
ChangeLogController::logExport($code, $name, $quantity, $exportCode, $description, $detailedInfo, $notes);

// Log lắp ráp
ChangeLogController::logAssembly($code, $name, $quantity, $assemblyCode, $description, $detailedInfo, $notes);

// Log sửa chữa
ChangeLogController::logRepair($code, $name, $quantity, $repairCode, $description, $detailedInfo, $notes);

// Log thu hồi
ChangeLogController::logRecall($code, $name, $quantity, $recallCode, $description, $detailedInfo, $notes);

// Log chuyển kho
ChangeLogController::logTransfer($code, $name, $quantity, $transferCode, $description, $detailedInfo, $notes);
```

### 📌 **Cách 3: Log tuỳ chỉnh hoàn toàn**

```php
<?php
use App\Http\Controllers\ChangeLogController;

ChangeLogController::createLogEntry([
    'item_code' => 'VT001',
    'item_name' => 'Bo mạch điều khiển',
    'change_type' => 'nhap_kho', // lap_rap, xuat_kho, sua_chua, thu_hoi, nhap_kho, chuyen_kho
    'document_code' => 'NK001',
    'quantity' => 10,
    'description' => 'Nhập kho từ nhà cung cấp',
    'performed_by' => 'Nguyễn Văn A', // Tự động lấy user hiện tại nếu không truyền
    'notes' => 'Đã kiểm tra chất lượng',
    'detailed_info' => [
        'supplier' => 'Công ty ABC',
        'price' => 50000
    ]
]);
```

## 🔄 **Log nhiều items cùng lúc**

```php
<?php
use App\Helpers\ChangeLogHelper;

// Khi nhập nhiều vật tư cùng lúc
$items = [
    [
        'code' => 'VT001',
        'name' => 'Bo mạch điều khiển',
        'quantity' => 5,
        'details' => ['price' => 100000]
    ],
    [
        'code' => 'VT002', 
        'name' => 'Cảm biến nhiệt độ',
        'quantity' => 10,
        'details' => ['price' => 50000]
    ]
];

ChangeLogHelper::logNhieu($items, 'nhap_kho', 'NK001', 'Nhập hàng loạt từ supplier');

// Hoặc dùng controller
ChangeLogController::logMultipleItems($items, 'nhap_kho', 'NK001', 'Nhập hàng loạt');
```

## 📋 **Các loại hình có sẵn:**

- `lap_rap` - Lắp ráp  
- `xuat_kho` - Xuất kho
- `sua_chua` - Sửa chữa
- `thu_hoi` - Thu hồi
- `nhap_kho` - Nhập kho
- `chuyen_kho` - Chuyển kho

## 💡 **Tips sử dụng:**

1. **Tự động lấy user:** Hệ thống tự động lấy thông tin người thực hiện từ user đăng nhập
2. **Mô tả tự động:** Nếu không truyền description, sẽ tự động tạo mô tả phù hợp
3. **Thông tin chi tiết:** Sử dụng `detailed_info` để lưu thêm thông tin như giá, nhà cung cấp, serial number...
4. **Timestamp:** Thời gian sẽ tự động được ghi lại

## 📝 **Ví dụ thực tế trong MaterialController:**

```php
<?php

namespace App\Http\Controllers;

use App\Helpers\ChangeLogHelper;
use App\Models\Material;

class MaterialController extends Controller 
{
    public function store(Request $request)
    {
        // Tạo vật tư mới
        $material = Material::create($validatedData);
        
        // Log nhập kho vật tư mới
        ChangeLogHelper::nhapKho(
            $material->code,
            $material->name,
            $request->initial_quantity,
            'NK' . date('YmdHis'), // Mã phiếu nhập
            'Nhập vật tư mới vào hệ thống',
            [
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'price' => $request->price,
                'created_by' => auth()->user()->name
            ]
        );
        
        return redirect()->route('materials.index')
            ->with('success', 'Vật tư đã được tạo và ghi log thành công!');
    }
}
```

## 🚀 **Để sử dụng:**

1. Import helper: `use App\Helpers\ChangeLogHelper;`
2. Gọi method tương ứng với loại thay đổi
3. Truyền các tham số cần thiết
4. Hệ thống sẽ tự động ghi log vào database!

---

## 🔧 **Cập nhật ChangeLog**

### 📌 **Cách 1: Sử dụng ChangeLogHelper (Tiếng Việt)**

```php
<?php
use App\Helpers\ChangeLogHelper;

// Cập nhật toàn bộ changelog
ChangeLogHelper::capNhat($changelogId, [
    'description' => 'Mô tả mới',
    'notes' => 'Ghi chú cập nhật',
    'quantity' => 25
]);

// Cập nhật chỉ mô tả
ChangeLogHelper::capNhatMoTa($changelogId, 'Mô tả được cập nhật');

// Cập nhật chỉ ghi chú
ChangeLogHelper::capNhatGhiChu($changelogId, 'Ghi chú mới');

// Cập nhật số lượng
ChangeLogHelper::capNhatSoLuong($changelogId, 15);

// Cập nhật người thực hiện
ChangeLogHelper::capNhatNguoiThucHien($changelogId, 'Trần Văn B');

// Cập nhật thông tin chi tiết
ChangeLogHelper::capNhatThongTinChiTiet($changelogId, [
    'supplier' => 'Nhà cung cấp mới',
    'warehouse' => 'Kho A1',
    'price' => 120000
]);

// Thêm một thông tin chi tiết cụ thể
ChangeLogHelper::themThongTinChiTiet($changelogId, 'batch_number', 'BATCH002');

// Xóa một thông tin chi tiết
ChangeLogHelper::xoaThongTinChiTiet($changelogId, 'old_field');

// Cập nhật theo mã phiếu (tất cả changelog có cùng mã phiếu)
ChangeLogHelper::capNhatTheoMaPhieu('NK001', [
    'notes' => 'Cập nhật hàng loạt cho phiếu NK001'
]);

// Cập nhật theo mã vật tư (tất cả changelog của vật tư đó)
ChangeLogHelper::capNhatTheoMaVatTu('VT001', [
    'notes' => 'Cập nhật thông tin cho tất cả log của VT001'
]);
```

### 📌 **Cách 2: Sử dụng ChangeLogController trực tiếp**

```php
<?php
use App\Http\Controllers\ChangeLogController;

// Cập nhật toàn bộ
ChangeLogController::updateLogEntry($id, [
    'description' => 'Mô tả mới',
    'quantity' => 30,
    'notes' => 'Cập nhật số lượng'
]);

// Cập nhật từng field cụ thể
ChangeLogController::updateDescription($id, 'Mô tả mới');
ChangeLogController::updateNotes($id, 'Ghi chú mới');
ChangeLogController::updateQuantity($id, 25);
ChangeLogController::updatePerformedBy($id, 'Người thực hiện mới');

// Cập nhật thông tin chi tiết
ChangeLogController::updateDetailedInfo($id, [
    'price' => 150000,
    'supplier' => 'Công ty XYZ'
]);

// Thêm/xóa thông tin chi tiết
ChangeLogController::addDetailedInfo($id, 'serial_number', 'SN123456');
ChangeLogController::removeDetailedInfo($id, 'old_info');

// Cập nhật theo mã phiếu hoặc mã vật tư
ChangeLogController::updateByDocumentCode('NK001', ['notes' => 'Cập nhật hàng loạt']);
ChangeLogController::updateByItemCode('VT001', ['notes' => 'Cập nhật cho vật tư VT001']);
```

### 📌 **Cách 3: Cập nhật qua API (PUT/PATCH)**

```javascript
// PUT request để cập nhật changelog
fetch('/change-logs/123', {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        description: 'Mô tả cập nhật qua API',
        quantity: 20,
        notes: 'Cập nhật từ frontend',
        detailed_info: {
            updated_by: 'API User',
            update_reason: 'Correction'
        }
    })
})
.then(response => response.json())
.then(data => {
    console.log('Cập nhật thành công:', data);
});

// PATCH request để cập nhật một số field
fetch('/change-logs/123', {
    method: 'PATCH',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
        notes: 'Chỉ cập nhật ghi chú'
    })
})
.then(response => response.json())
.then(data => {
    console.log('Cập nhật thành công:', data);
});
```

## 📝 **Ví dụ thực tế cập nhật ChangeLog:**

```php
<?php

namespace App\Http\Controllers;

use App\Helpers\ChangeLogHelper;

class MaterialController extends Controller 
{
    public function updateStock(Request $request, $materialId)
    {
        // Logic cập nhật stock...
        
        // Lấy ID của changelog liên quan (ví dụ từ session hoặc DB)
        $lastChangeLogId = session('last_material_changelog_id');
        
        if ($lastChangeLogId) {
            // Cập nhật quantity trong changelog
            ChangeLogHelper::capNhatSoLuong(
                $lastChangeLogId, 
                $request->new_quantity
            );
            
            // Thêm thông tin về việc cập nhật
            ChangeLogHelper::themThongTinChiTiet(
                $lastChangeLogId,
                'updated_at',
                now()->format('Y-m-d H:i:s')
            );
            
            ChangeLogHelper::themThongTinChiTiet(
                $lastChangeLogId,
                'update_reason',
                $request->update_reason ?? 'Điều chỉnh tồn kho'
            );
        }
        
        return redirect()->back()
            ->with('success', 'Đã cập nhật tồn kho và nhật ký thành công!');
    }
    
    public function correctImportLog($importCode)
    {
        // Sửa lỗi cho tất cả changelog của một phiếu nhập
        ChangeLogHelper::capNhatTheoMaPhieu($importCode, [
            'notes' => 'Đã kiểm tra và xác nhận chính xác',
            'detailed_info' => [
                'verified_by' => auth()->user()->name,
                'verified_at' => now()->format('Y-m-d H:i:s'),
                'status' => 'verified'
            ]
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật tất cả log của phiếu ' . $importCode
        ]);
    }
}
```

## 💡 **Tips cập nhật:**

1. **Chỉ cập nhật field cần thiết:** Sử dụng các method cụ thể như `capNhatMoTa()`, `capNhatSoLuong()` thay vì cập nhật toàn bộ
2. **Ghi lại lý do cập nhật:** Luôn thêm thông tin về việc tại sao cập nhật vào `detailed_info`
3. **Cập nhật hàng loạt:** Sử dụng `capNhatTheoMaPhieu()` hoặc `capNhatTheoMaVatTu()` để cập nhật nhiều log cùng lúc
4. **Validation:** API route có validation tự động, method trực tiếp cần cẩn thận với dữ liệu đầu vào
5. **Backup trước khi cập nhật:** Đối với những thay đổi quan trọng, nên backup thông tin cũ vào `detailed_info`

## 🔒 **Lưu ý bảo mật:**

- Chỉ user có quyền mới được cập nhật changelog
- Cập nhật qua API cần CSRF token
- Nên log lại việc cập nhật để audit trail
- Không cho phép cập nhật `time_changed` và `performed_by` từ frontend 