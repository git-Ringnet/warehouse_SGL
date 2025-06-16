<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo phiếu nhập kho - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <h1 class="text-xl font-bold text-gray-800">Tạo phiếu nhập kho</h1>
            <a href="{{ route('inventory-imports.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        
        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('inventory-imports.store') }}" method="POST">
                    @csrf
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Thông tin phiếu nhập kho</h2>
                    
                    @if ($errors->any())
                    <div class="mb-4 bg-red-50 p-3 rounded border border-red-200">
                        <ul class="list-disc list-inside text-red-500">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cột 1 -->
                        <div class="space-y-4">
                            <div>
                                <label for="import_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã phiếu nhập</label>
                                <input type="text" id="import_code" name="import_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã phiếu nhập" required value="{{ old('import_code') }}">
                            </div>
                            
                            <div>
                                <label for="import_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày nhập kho</label>
                                <input type="date" id="import_date" name="import_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required value="{{ old('import_date', date('Y-m-d')) }}">
                            </div>
                            
                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1 required">Nhà cung cấp</label>
                                <select id="supplier_id" name="supplier_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-2 text-sm">
                                    <a href="{{ route('suppliers.create') }}" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-plus-circle mr-1"></i>Thêm nhà cung cấp mới
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="space-y-4">
                            <div>
                                <label for="order_code" class="block text-sm font-medium text-gray-700 mb-1">Mã đơn hàng</label>
                                <input type="text" id="order_code" name="order_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã đơn hàng liên quan (nếu có)" value="{{ old('order_code') }}">
                            </div>
                            
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú về phiếu nhập kho (nếu có)">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phần vật tư -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Danh sách vật tư nhập kho</h3>
                        
                        <div id="materials-container">
                            <!-- Template cho hàng vật tư đầu tiên -->
                            <div class="material-row border border-gray-200 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Loại sản phẩm</label>
                                        <select name="materials[0][item_type]" class="item-type-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="updateItemOptions(this, 0)">
                                            <option value="">-- Chọn loại --</option>
                                            <option value="material">Vật tư</option>
                                            <option value="product">Thành phẩm</option>
                                            <option value="good">Hàng hóa</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư/ thành phẩm/ hàng hoá</label>
                                        <select name="materials[0][material_id]" class="material-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                            <option value="">-- Chọn sản phẩm --</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Kho nhập</label>
                                        <select name="materials[0][warehouse_id]" class="warehouse-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                            <option value="">-- Chọn kho nhập --</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                        <input type="number" name="materials[0][quantity]" class="quantity-input w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập số lượng" value="1" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">List số seri</label>
                                        <textarea name="materials[0][serial_numbers]" class="serial-input w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" rows="2" placeholder="Nhập danh sách số seri, mỗi số seri trên một dòng hoặc ngăn cách bằng dấu phẩy"></textarea>
                                        <p class="text-xs text-gray-500 mt-1">Số seri không bắt buộc. Nếu nhập, số lượng seri nên trùng khớp với số lượng.</p>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                    <textarea name="materials[0][notes]" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú cho vật tư này (nếu có)"></textarea>
                                </div>
                                <div class="mt-2 flex justify-end">
                                    <button type="button" class="remove-material text-red-500 hover:text-red-700" onclick="removeMaterial(this)" style="display: none;">
                                        <i class="fas fa-trash mr-1"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-2">
                            <button type="button" id="add-material" class="flex items-center text-blue-500 hover:text-blue-700">
                                <i class="fas fa-plus-circle mr-1"></i> Thêm vật tư
                            </button>
                        </div>
                    </div>
                    
                    <!-- Bảng tổng hợp các vật tư đã thêm -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Tổng hợp vật tư, hàng hoá đã thêm</h3>
                        <div class="overflow-x-auto">
                            <table id="summary-table" class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã - Tên sản phẩm</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kho nhập</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr>
                                        <td colspan="7" class="px-4 py-4 text-sm text-gray-500 text-center">Chưa có vật tư nào được thêm</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('inventory-imports.index') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu phiếu nhập
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Dữ liệu JSON từ Laravel -->
    <script id="app-data" type="application/json">
        {
            "materials": {!! json_encode($materials->map(function($material) {
                return [
                    'id' => $material->id,
                    'name' => $material->code . ' - ' . $material->name,
                    'type' => 'material',
                    'unit' => $material->unit ?? '',
                    'category' => $material->category ?? 'other'
                ];
            })) !!},
            "products": {!! json_encode($products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->code . ' - ' . $product->name,
                    'type' => 'product',
                    'unit' => '',
                    'category' => 'product'
                ];
            })) !!},
            "goods": {!! json_encode($goods->map(function($good) {
                return [
                    'id' => $good->id,
                    'name' => $good->code . ' - ' . $good->name,
                    'type' => 'good',
                    'unit' => $good->unit ?? '',
                    'category' => $good->category ?? 'other'
                ];
            })) !!}
        }
    </script>

    <script>
        // Tự động điền ngày hiện tại vào ô ngày nhập kho
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy dữ liệu từ thẻ script JSON
            const appDataElement = document.getElementById('app-data');
            const appData = JSON.parse(appDataElement.textContent);
            
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            
            document.getElementById('import_date').value = `${year}-${month}-${day}`;
            
            // Thêm xử lý cho nút "Thêm vật tư"
            document.getElementById('add-material').addEventListener('click', addMaterial);
            
            // Hiển thị nút xóa nếu có nhiều hơn 1 hàng
            updateRemoveButtons();
            
            // Khởi tạo danh sách vật tư/thành phẩm/hàng hoá
            initializeItemLists(appData);
            
            // Cập nhật bảng tổng hợp ban đầu
            updateSummaryTable();
            
            // Đăng ký sự kiện change cho các input để cập nhật bảng tổng hợp
            registerChangeEvents();
        });
        
        // Biến đếm số lượng hàng vật tư
        let materialCount = 1;
        
        // Dữ liệu các sản phẩm từ cả 3 bảng
        let itemsData = {
            material: [],
            product: [],
            good: []
        };
        
        // Khởi tạo danh sách vật tư/thành phẩm/hàng hoá
        function initializeItemLists(appData) {
            itemsData.material = appData.materials;
            itemsData.product = appData.products;
            itemsData.good = appData.goods;
        }
        
        // Hàm thêm hàng vật tư mới
        function addMaterial() {
            const container = document.getElementById('materials-container');
            const template = container.querySelector('.material-row').cloneNode(true);
            
            // Cập nhật các attributes
            const inputs = template.querySelectorAll('select, input, textarea');
            inputs.forEach(input => {
                const nameAttr = input.getAttribute('name');
                if (nameAttr) {
                    input.setAttribute('name', nameAttr.replace('[0]', `[${materialCount}]`));
                    
                    // Reset giá trị
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else if (input.type === 'number' && input.name.includes('quantity')) {
                        input.value = '1';
                    } else {
                        input.value = '';
                    }
                }
            });
            
            // Hiển thị nút xóa
            const removeButton = template.querySelector('.remove-material');
            removeButton.style.display = 'inline-flex';
            
            // Thêm hàng mới vào container
            container.appendChild(template);
            
            // Đăng ký sự kiện cho dropdown loại sản phẩm mới
            const typeSelect = template.querySelector('.item-type-select');
            if(typeSelect) {
                typeSelect.addEventListener('change', function() {
                    updateItemOptions(this, materialCount);
                });
            }
            
            // Đăng ký sự kiện cho các input khác để cập nhật bảng tổng hợp
            registerChangeEventsForRow(template);
            
            materialCount++;
            
            // Cập nhật hiển thị của các nút xóa
            updateRemoveButtons();
            
            // Cập nhật bảng tổng hợp
            updateSummaryTable();
        }
        
        // Hàm xóa hàng vật tư
        function removeMaterial(button) {
            const materialRow = button.closest('.material-row');
            materialRow.remove();
            
            // Cập nhật hiển thị của các nút xóa
            updateRemoveButtons();
            
            // Cập nhật bảng tổng hợp
            updateSummaryTable();
        }
        
        // Cập nhật hiển thị của các nút xóa
        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.material-row');
            const removeButtons = document.querySelectorAll('.remove-material');
            
            // Ẩn/hiện nút xóa dựa trên số lượng hàng
            if (rows.length <= 1) {
                removeButtons.forEach(btn => btn.style.display = 'none');
            } else {
                removeButtons.forEach(btn => btn.style.display = 'inline-flex');
            }
        }
        
        // Cập nhật danh sách sản phẩm khi chọn loại
        function updateItemOptions(selectElement, rowIndex) {
            const itemType = selectElement.value;
            const row = selectElement.closest('.material-row');
            const materialSelect = row.querySelector('.material-select');
            
            // Xóa tất cả các option hiện tại (trừ option đầu tiên)
            while (materialSelect.options.length > 1) {
                materialSelect.remove(1);
            }
            
            // Nếu không có loại sản phẩm được chọn, không thêm các option
            if (!itemType) {
                return;
            }
            
            // Thêm các option mới dựa trên loại sản phẩm
            const items = itemsData[itemType] || [];
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.text = item.name;
                option.dataset.unit = item.unit;
                option.dataset.type = item.type;
                materialSelect.add(option);
            });
            
            // Cập nhật bảng tổng hợp
            updateSummaryTable();
        }
        
        // Đăng ký sự kiện change cho tất cả các input để cập nhật bảng tổng hợp
        function registerChangeEvents() {
            const rows = document.querySelectorAll('.material-row');
            rows.forEach(row => {
                registerChangeEventsForRow(row);
            });
        }
        
        // Đăng ký sự kiện change cho một hàng
        function registerChangeEventsForRow(row) {
            const inputs = row.querySelectorAll('select, input, textarea');
            inputs.forEach(input => {
                input.addEventListener('change', updateSummaryTable);
            });
        }
        
        // Cập nhật bảng tổng hợp
        function updateSummaryTable() {
            const table = document.getElementById('summary-table');
            const tbody = table.querySelector('tbody');
            
            // Lấy tất cả các hàng vật tư
            const materialRows = document.querySelectorAll('.material-row');
            
            // Xóa tất cả các hàng trong bảng tổng hợp
            tbody.innerHTML = '';
            
            // Nếu không có vật tư nào, hiển thị dòng thông báo
            if (materialRows.length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `<td colspan="7" class="px-4 py-4 text-sm text-gray-500 text-center">Chưa có vật tư nào được thêm</td>`;
                tbody.appendChild(emptyRow);
                return;
            }
            
            // Đếm số hàng có dữ liệu hợp lệ
            let validRows = 0;
            
            // Tạo các hàng mới cho bảng tổng hợp
            materialRows.forEach((row, index) => {
                const itemTypeSelect = row.querySelector('select[name*="item_type"]');
                const materialSelect = row.querySelector('select[name*="material_id"]');
                const warehouseSelect = row.querySelector('select[name*="warehouse_id"]');
                const quantityInput = row.querySelector('input[name*="quantity"]');
                const notesTextarea = row.querySelector('textarea[name*="notes"]');
                
                // Kiểm tra xem đã chọn loại và sản phẩm chưa
                if (!itemTypeSelect.value || !materialSelect.value) return;
                
                validRows++;
                
                const itemType = itemTypeSelect.value;
                const itemTypeText = itemTypeSelect.options[itemTypeSelect.selectedIndex].text;
                const selectedOption = materialSelect.options[materialSelect.selectedIndex];
                const materialText = selectedOption ? selectedOption.text : 'Chưa chọn';
                
                const warehouseOption = warehouseSelect.options[warehouseSelect.selectedIndex];
                const warehouseText = warehouseOption ? warehouseOption.text : 'Chưa chọn';
                
                const quantity = quantityInput ? quantityInput.value : '0';
                const notes = notesTextarea ? notesTextarea.value : '';
                
                // Lấy đơn vị từ dataset của option được chọn
                let unit = '';
                if (selectedOption && selectedOption.dataset.unit) {
                    unit = selectedOption.dataset.unit;
                }
                
                // Xác định loại hiển thị
                let typeDisplay = '';
                switch(itemType) {
                    case 'material':
                        typeDisplay = 'Vật tư';
                        break;
                    case 'product':
                        typeDisplay = 'Thành phẩm';
                        break;
                    case 'good':
                        typeDisplay = 'Hàng hóa';
                        break;
                    default:
                        typeDisplay = 'Chưa chọn';
                }
                
                const summaryRow = document.createElement('tr');
                summaryRow.innerHTML = `
                    <td class="px-4 py-2 text-sm text-gray-900">${validRows}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${typeDisplay}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${materialText}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${warehouseText}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${unit}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${quantity}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${notes}</td>
                `;
                tbody.appendChild(summaryRow);
            });
            
            // Nếu sau khi duyệt qua tất cả vẫn không có hàng nào được thêm hoặc hợp lệ
            if (validRows === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `<td colspan="7" class="px-4 py-4 text-sm text-gray-500 text-center">Chưa có vật tư nào được thêm hoặc vật tư chưa được chọn đầy đủ</td>`;
                tbody.appendChild(emptyRow);
            }
        }
    </script>
</body>
</html> 