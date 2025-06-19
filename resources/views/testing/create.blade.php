<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo phiếu kiểm thử - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <h1 class="text-xl font-bold text-gray-800">Tạo phiếu kiểm thử mới</h1>
            <a href="{{ route('testing.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                <p>{{ session('error') }}</p>
            </div>
            @endif
            
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('testing.store') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2">Thông tin cơ bản</h2>
                        
                        <!-- Loại kiểm thử -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="test_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại kiểm thử</label>
                                <select id="test_type" name="test_type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn loại kiểm thử --</option>
                                    <option value="material">Kiểm thử Vật tư/Hàng hóa</option>
                                    <option value="finished_product">Kiểm thử Thiết bị thành phẩm</option>
                                </select>
                                @error('test_type')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kiểm thử</label>
                                <input type="date" id="test_date" name="test_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ date('Y-m-d') }}" required>
                                @error('test_date')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1 required">Người phụ trách</label>
                                <select id="assigned_to" name="assigned_to" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn người phụ trách --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người tiếp nhận kiểm thử</label>
                                <select id="receiver_id" name="receiver_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn người tiếp nhận --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                                @error('receiver_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Thêm vật tư/hàng hóa/thành phẩm -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Thêm vật tư, hàng hóa hoặc thành phẩm</h2>
                        
                        <div id="items-container">
                            <div class="item-row mb-6 border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Loại</label>
                                        <select name="items[0][item_type]" class="item-type w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="updateItemOptions(this, 0)">
                                    <option value="">-- Chọn loại --</option>
                                    <option value="material">Vật tư</option>
                                    <option value="product">Thành phẩm</option>
                                    <option value="finished_product">Hàng hóa</option>
                                </select>
                            </div>
                            
                            <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư/hàng hóa</label>
                                        <select name="items[0][id]" id="item_name_0" class="item-name w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn --</option>
                                </select>
                            </div>
                            
                            <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                                        <select name="items[0][supplier_id]" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                            @endforeach
                                </select>
                                </div>
                            </div>
                        
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Mã lô</label>
                                        <input type="text" name="items[0][batch_number]" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã lô">
                                </div>
                                
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                        <input type="number" name="items[0][quantity]" min="1" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="1" required>
                                </div>
                            </div>
                            
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Serial/Mã thiết bị</label>
                                    <input type="text" name="items[0][serial_number]" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập serial hoặc mã thiết bị">
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="button" class="remove-item px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200" onclick="removeItem(this)">
                                        <i class="fas fa-trash mr-1"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="button" id="add-item-btn" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center" onclick="addItem()">
                                <i class="fas fa-plus mr-2"></i> Thêm vật tư/hàng hóa
                            </button>
                        </div>
                    </div>
                    
                    <!-- Bảng tổng hợp vật tư đã thêm -->
                    <div class="mb-6 mt-4">
                        <h3 class="text-md font-medium text-gray-800 mb-3">Tổng hợp vật tư, hàng hoá đã thêm</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">STT</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">LOẠI</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">MÃ - TÊN SẢN PHẨM</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">NHÀ CUNG CẤP</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">MÃ LÔ</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">SERIAL</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ĐƠN VỊ</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">SỐ LƯỢNG</th>
                                    </tr>
                                </thead>
                                <tbody id="items-summary-table">
                                    <tr class="text-gray-500 text-center">
                                        <td colspan="8" class="py-4">Chưa có vật tư/hàng hóa nào được thêm</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Hạng mục kiểm thử -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                            <div class="flex justify-between items-center mb-3">
                            <h2 class="text-lg font-semibold text-gray-800">Hạng mục kiểm thử</h2>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="addDefaultTestItems()" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm flex items-center">
                                        <i class="fas fa-list-check mr-1"></i> Thêm mục mặc định
                                    </button>
                                <button type="button" onclick="addTestItem()" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm flex items-center">
                                    <i class="fas fa-plus mr-1"></i> Thêm hạng mục
                                </button>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div id="test_items_container" class="space-y-3">
                                    <div class="test-item flex items-center gap-4">
                                    <input type="text" name="test_items[]" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập hạng mục kiểm thử" required>
                                        <button type="button" onclick="removeTestItem(this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú bổ sung nếu có"></textarea>
                    </div>

                    <!-- Submit buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('testing.index') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Tạo phiếu kiểm thử
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        let itemCounter = 1;
        let itemsData = [];
        
        function updateItemOptions(selectElement, index) {
            const itemType = selectElement.value;
            const itemNameSelect = document.getElementById('item_name_' + index);
            const supplierContainer = selectElement.closest('.grid').querySelector(`select[name="items[${index}][supplier_id]"]`).closest('div');
            const supplierSelect = selectElement.closest('.grid').querySelector(`select[name="items[${index}][supplier_id]"]`);
            const serialInput = selectElement.closest('.item-row').querySelector(`input[name="items[${index}][serial_number]"]`);
            
            // Xóa container serial cũ nếu có
            const oldSerialContainer = selectElement.closest('.item-row').querySelector('.serial-container');
            if (oldSerialContainer) {
                oldSerialContainer.remove();
            }
            
            // Clear existing options
            itemNameSelect.innerHTML = '<option value="">-- Chọn --</option>';
            
            // Hiển thị/ẩn trường nhà cung cấp dựa vào loại item
            if (itemType === 'product') {
                supplierContainer.style.display = 'none';
            } else {
                supplierContainer.style.display = 'block';
            }
            
            if (!itemType) return;
            
            // Fetch items based on type
            fetch(`/api/testing/materials-by-type?type=${itemType}&search=`)
                .then(response => response.json())
                .then(items => {
                    // Xóa tất cả options cũ
                    itemNameSelect.innerHTML = '<option value="">-- Chọn --</option>';
                    
                    items.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        option.dataset.code = item.code || '';
                        itemNameSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching items:', error));
                
            // Thêm event listener cho việc chọn item
            itemNameSelect.onchange = function() {
                const selectedItemId = this.value;
                if (selectedItemId) {
                    console.log(`Fetching details for ${itemType} with ID ${selectedItemId}`);
                    
                    // Lấy thông tin nhà cung cấp của item
                    fetch(`/api/items/${itemType}/${selectedItemId}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Item details:', data);
                            if (data.supplier_id) {
                                // Tìm option có value là supplier_id và chọn nó
                                const supplierOption = supplierSelect.querySelector(`option[value="${data.supplier_id}"]`);
                                if (supplierOption) {
                                    supplierSelect.value = data.supplier_id;
                                    // Thêm thông báo đã tự động chọn nhà cung cấp
                                    const notification = document.createElement('div');
                                    notification.className = 'text-sm text-green-600 mt-1';
                                    notification.textContent = `Đã tự động chọn: ${data.supplier_name || supplierOption.textContent}`;
                                    const existingNotification = supplierSelect.parentNode.querySelector('.text-green-600');
                                    if (existingNotification) {
                                        existingNotification.remove();
                                    }
                                    supplierSelect.parentNode.appendChild(notification);
                                    
                                    // Tự động ẩn thông báo sau 3 giây
                                    setTimeout(() => {
                                        if (notification.parentNode) {
                                            notification.remove();
                                        }
                                    }, 3000);
                                }
                            }
                            
                            // Cập nhật dữ liệu vật tư trong bảng tổng hợp
                            updateItemData(index, {
                                id: selectedItemId,
                                type: itemType,
                                name: data.name,
                                code: data.code,
                                unit: data.unit,
                                supplier_name: data.supplier_name || (supplierSelect.selectedIndex > 0 ? supplierSelect.options[supplierSelect.selectedIndex].text : 'N/A')
                            });
                        })
                        .catch(error => console.error('Error fetching item details:', error));
                        
                    // Lấy danh sách serial có sẵn
                    fetch(`/api/testing/serial-numbers?type=${itemType}&id=${selectedItemId}`)
                        .then(response => response.json())
                        .then(serials => {
                            console.log('Serials:', serials);
                            
                            // Xóa container serial cũ nếu có
                            const oldSerialContainer = selectElement.closest('.item-row').querySelector('.serial-container');
                            if (oldSerialContainer) {
                                oldSerialContainer.remove();
                            }
                            
                            // Hiển thị danh sách serial nếu có
                            if (serials && serials.length > 0) {
                                // Tạo container cho danh sách serial
                                const serialContainerDiv = document.createElement('div');
                                serialContainerDiv.className = 'serial-container bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3';
                                
                                // Tạo label
                                const label = document.createElement('label');
                                label.className = 'block text-sm font-medium text-gray-700 mb-2';
                                label.textContent = 'Chọn serial có sẵn:';
                                serialContainerDiv.appendChild(label);
                                
                                // Tạo select
                                const serialSelect = document.createElement('select');
                                serialSelect.className = 'w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white mb-2';
                                
                                // Thêm option mặc định
                                const defaultOption = document.createElement('option');
                                defaultOption.value = '';
                                defaultOption.textContent = '-- Chọn serial --';
                                serialSelect.appendChild(defaultOption);
                                
                                // Thêm các options từ danh sách serial
                                serials.forEach(serial => {
                const option = document.createElement('option');
                                    option.value = serial;
                                    option.textContent = serial;
                                    serialSelect.appendChild(option);
                                });
                                
                                // Thêm event listener cho select
                                serialSelect.onchange = function() {
                                    serialInput.value = this.value;
                                    
                                    // Cập nhật serial trong dữ liệu vật tư
                                    updateItemData(index, { serial_number: this.value });
                                };
                                
                                serialContainerDiv.appendChild(serialSelect);
                                
                                // Thêm container vào trước input serial
                                const serialInputContainer = serialInput.parentNode;
                                serialInputContainer.insertBefore(serialContainerDiv, serialInput);
                            }
                        })
                        .catch(error => console.error('Error fetching serials:', error));
                }
            };
        }
        
        // Cập nhật dữ liệu vật tư và bảng tổng hợp
        function updateItemData(index, newData) {
            // Tìm vật tư trong mảng dữ liệu
            const existingItemIndex = itemsData.findIndex(item => item.index === index);
            
            if (existingItemIndex >= 0) {
                // Cập nhật dữ liệu cho vật tư đã tồn tại
                itemsData[existingItemIndex] = { ...itemsData[existingItemIndex], ...newData };
            } else {
                // Thêm vật tư mới vào mảng
                itemsData.push({ index, ...newData });
            }
            
            // Cập nhật bảng tổng hợp
            updateSummaryTable();
        }
        
        // Cập nhật bảng tổng hợp vật tư
        function updateSummaryTable() {
            const tableBody = document.getElementById('items-summary-table');
            
            // Xóa nội dung cũ
            tableBody.innerHTML = '';
            
            if (itemsData.length === 0) {
                // Hiển thị thông báo nếu không có vật tư
                const emptyRow = document.createElement('tr');
                emptyRow.className = 'text-gray-500 text-center';
                emptyRow.innerHTML = '<td colspan="8" class="py-4">Chưa có vật tư/hàng hóa nào được thêm</td>';
                tableBody.appendChild(emptyRow);
                return;
            }
            
            // Thêm các hàng mới
            itemsData.forEach((item, i) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                
                // Lấy giá trị số lượng và mã lô từ input
                const itemRow = document.querySelector(`.item-row:nth-child(${item.index + 1})`);
                const quantityInput = itemRow ? itemRow.querySelector(`input[name="items[${item.index}][quantity]"]`) : null;
                const batchInput = itemRow ? itemRow.querySelector(`input[name="items[${item.index}][batch_number]"]`) : null;
                const serialInput = itemRow ? itemRow.querySelector(`input[name="items[${item.index}][serial_number]"]`) : null;
                
                const quantity = quantityInput ? quantityInput.value : '1';
                const batchNumber = batchInput ? batchInput.value : 'N/A';
                const serialNumber = item.serial_number || (serialInput ? serialInput.value : 'N/A');
                
                // Xác định loại vật tư
                let typeText = '';
                switch (item.type) {
                    case 'material':
                        typeText = 'Vật tư';
                        break;
                    case 'product':
                        typeText = 'Thành phẩm';
                        break;
                    case 'finished_product':
                        typeText = 'Hàng hóa';
                        break;
                }
                
                row.innerHTML = `
                    <td class="py-2 px-3 border-b border-gray-200">${i + 1}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${typeText}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${item.code || ''} - ${item.name || ''}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${item.supplier_name || 'N/A'}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${batchNumber}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${serialNumber}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${item.unit || 'Chiếc'}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${quantity}</td>
                `;
                
                tableBody.appendChild(row);
            });
        }
        
        function addItem() {
            const container = document.getElementById('items-container');
            const newItem = document.createElement('div');
            newItem.className = 'item-row mb-6 border border-gray-200 rounded-lg p-4';
            newItem.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Loại</label>
                        <select name="items[${itemCounter}][item_type]" class="item-type w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="updateItemOptions(this, ${itemCounter})">
                            <option value="">-- Chọn loại --</option>
                            <option value="material">Vật tư</option>
                            <option value="product">Thành phẩm</option>
                            <option value="finished_product">Hàng hóa</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư/hàng hóa</label>
                        <select name="items[${itemCounter}][id]" id="item_name_${itemCounter}" class="item-name w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                            <option value="">-- Chọn --</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                        <select name="items[${itemCounter}][supplier_id]" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">-- Chọn nhà cung cấp --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mã lô</label>
                        <input type="text" name="items[${itemCounter}][batch_number]" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã lô" oninput="updateBatchNumber(${itemCounter}, this.value)">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                        <input type="number" name="items[${itemCounter}][quantity]" min="1" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="1" required oninput="updateQuantity(${itemCounter}, this.value)">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serial/Mã thiết bị</label>
                    <input type="text" name="items[${itemCounter}][serial_number]" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập serial hoặc mã thiết bị" oninput="updateSerialNumber(${itemCounter}, this.value)">
                </div>

                <div class="flex justify-end">
                    <button type="button" class="remove-item px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200" onclick="removeItem(this, ${itemCounter})">
                        <i class="fas fa-trash mr-1"></i> Xóa
                    </button>
                </div>
            `;
            container.appendChild(newItem);
            itemCounter++;
        }
        
        // Cập nhật số lượng trong bảng tổng hợp
        function updateQuantity(index, value) {
            const existingItemIndex = itemsData.findIndex(item => item.index === index);
            if (existingItemIndex >= 0) {
                itemsData[existingItemIndex].quantity = value;
                updateSummaryTable();
            }
        }
        
        // Cập nhật mã lô trong bảng tổng hợp
        function updateBatchNumber(index, value) {
            const existingItemIndex = itemsData.findIndex(item => item.index === index);
            if (existingItemIndex >= 0) {
                itemsData[existingItemIndex].batch_number = value;
                updateSummaryTable();
            }
        }
        
        // Cập nhật serial trong bảng tổng hợp
        function updateSerialNumber(index, value) {
            const existingItemIndex = itemsData.findIndex(item => item.index === index);
            if (existingItemIndex >= 0) {
                itemsData[existingItemIndex].serial_number = value;
                updateSummaryTable();
            }
        }
        
        function removeItem(button, index) {
            const container = document.getElementById('items-container');
            const item = button.closest('.item-row');
            
            // Don't remove if it's the only one
            if (container.children.length > 1) {
                container.removeChild(item);
                
                // Xóa vật tư khỏi mảng dữ liệu
                const itemIndex = itemsData.findIndex(item => item.index === index);
                if (itemIndex >= 0) {
                    itemsData.splice(itemIndex, 1);
                    updateSummaryTable();
                }
            }
        }
        
        // Add new test item
        function addTestItem() {
            const container = document.getElementById('test_items_container');
            const newItem = document.createElement('div');
            newItem.className = 'test-item flex items-center gap-4';
            newItem.innerHTML = `
                <input type="text" name="test_items[]" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập hạng mục kiểm thử" required>
                <button type="button" onclick="removeTestItem(this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(newItem);
        }
        
        // Remove test item
        function removeTestItem(button) {
            const container = document.getElementById('test_items_container');
            const item = button.closest('.test-item');
            
            // Don't remove if it's the last one
            if (container.children.length > 1) {
                container.removeChild(item);
            }
        }
        
        // Khởi tạo cho item đầu tiên
        document.addEventListener('DOMContentLoaded', function() {
            const firstItemTypeSelect = document.querySelector('.item-type');
            if (firstItemTypeSelect) {
                // Khởi tạo hiển thị/ẩn trường nhà cung cấp
                const supplierContainer = firstItemTypeSelect.closest('.grid').querySelector('select[name="items[0][supplier_id]"]').closest('div');
                if (firstItemTypeSelect.value === 'product') {
                    supplierContainer.style.display = 'none';
                }
                
                firstItemTypeSelect.addEventListener('change', function() {
                    updateItemOptions(this, 0);
                });
                
                // Khởi tạo event listener cho select tên sản phẩm đầu tiên
                const firstItemNameSelect = document.getElementById('item_name_0');
                if (firstItemNameSelect) {
                    firstItemNameSelect.onchange = function() {
                        const itemType = firstItemTypeSelect.value;
                        const selectedItemId = this.value;
                        const supplierSelect = this.closest('.grid').querySelector('select[name="items[0][supplier_id]"]');
                        const serialInput = this.closest('.item-row').querySelector('input[name="items[0][serial_number]"]');
                        
                        if (selectedItemId) {
                            console.log(`Fetching details for ${itemType} with ID ${selectedItemId}`);
                            
                            // Lấy thông tin nhà cung cấp của item
                            fetch(`/api/items/${itemType}/${selectedItemId}`)
                                .then(response => response.json())
                                .then(data => {
                                    console.log('Item details:', data);
                                    if (data.supplier_id) {
                                        // Tìm option có value là supplier_id và chọn nó
                                        const supplierOption = supplierSelect.querySelector(`option[value="${data.supplier_id}"]`);
                                        if (supplierOption) {
                                            supplierSelect.value = data.supplier_id;
                                            // Thêm thông báo đã tự động chọn nhà cung cấp
                                            const notification = document.createElement('div');
                                            notification.className = 'text-sm text-green-600 mt-1';
                                            notification.textContent = `Đã tự động chọn: ${data.supplier_name || supplierOption.textContent}`;
                                            const existingNotification = supplierSelect.parentNode.querySelector('.text-green-600');
                                            if (existingNotification) {
                                                existingNotification.remove();
                                            }
                                            supplierSelect.parentNode.appendChild(notification);
                                            
                                            // Tự động ẩn thông báo sau 3 giây
                                            setTimeout(() => {
                                                if (notification.parentNode) {
                                                    notification.remove();
                                                }
                                            }, 3000);
                                        }
                                    }
                                    
                                    // Cập nhật dữ liệu vật tư trong bảng tổng hợp
                                    updateItemData(0, {
                                        id: selectedItemId,
                                        type: itemType,
                                        name: data.name,
                                        code: data.code,
                                        unit: data.unit,
                                        supplier_name: data.supplier_name || (supplierSelect.selectedIndex > 0 ? supplierSelect.options[supplierSelect.selectedIndex].text : 'N/A')
                                    });
                                })
                                .catch(error => console.error('Error fetching item details:', error));
                                
                            // Lấy danh sách serial có sẵn
                            fetch(`/api/testing/serial-numbers?type=${itemType}&id=${selectedItemId}`)
                                .then(response => response.json())
                                .then(serials => {
                                    console.log('Serials:', serials);
                                    
                                    // Xóa container serial cũ nếu có
                                    const oldSerialContainer = this.closest('.item-row').querySelector('.serial-container');
                                    if (oldSerialContainer) {
                                        oldSerialContainer.remove();
                                    }
                                    
                                    // Hiển thị danh sách serial nếu có
                                    if (serials && serials.length > 0) {
                                        // Tạo container cho danh sách serial
                                        const serialContainerDiv = document.createElement('div');
                                        serialContainerDiv.className = 'serial-container bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3';
                                        
                                        // Tạo label
                                        const label = document.createElement('label');
                                        label.className = 'block text-sm font-medium text-gray-700 mb-2';
                                        label.textContent = 'Chọn serial có sẵn:';
                                        serialContainerDiv.appendChild(label);
                                        
                                        // Tạo select
                                        const serialSelect = document.createElement('select');
                                        serialSelect.className = 'w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white mb-2';
                                        
                                        // Thêm option mặc định
                                        const defaultOption = document.createElement('option');
                                        defaultOption.value = '';
                                        defaultOption.textContent = '-- Chọn serial --';
                                        serialSelect.appendChild(defaultOption);
                                        
                                        // Thêm các options từ danh sách serial
                                        serials.forEach(serial => {
                                            const option = document.createElement('option');
                                            option.value = serial;
                                            option.textContent = serial;
                                            serialSelect.appendChild(option);
                                        });
                                        
                                        // Thêm event listener cho select
                                        serialSelect.onchange = function() {
                                            serialInput.value = this.value;
                                            
                                            // Cập nhật serial trong dữ liệu vật tư
                                            updateItemData(0, { serial_number: this.value });
                                        };
                                        
                                        serialContainerDiv.appendChild(serialSelect);
                                        
                                        // Thêm container vào trước input serial
                                        const serialInputContainer = serialInput.parentNode;
                                        serialInputContainer.insertBefore(serialContainerDiv, serialInput);
                                    }
                                })
                                .catch(error => console.error('Error fetching serials:', error));
                        }
                    };
                }
                
                // Thêm event listener cho các trường input của item đầu tiên
                const quantityInput = document.querySelector('input[name="items[0][quantity]"]');
                if (quantityInput) {
                    quantityInput.addEventListener('input', function() {
                        updateQuantity(0, this.value);
                    });
                }
                
                const batchInput = document.querySelector('input[name="items[0][batch_number]"]');
                if (batchInput) {
                    batchInput.addEventListener('input', function() {
                        updateBatchNumber(0, this.value);
                    });
                }
                
                const serialInput = document.querySelector('input[name="items[0][serial_number]"]');
                if (serialInput) {
                    serialInput.addEventListener('input', function() {
                        updateSerialNumber(0, this.value);
                    });
                }
            }
        });

        // Function to add default testing items
        function addDefaultTestItems() {
            const testItemsContainer = document.getElementById('test_items_container');
            const existingItems = testItemsContainer.querySelectorAll('.test-item');
            
            // Remove existing items
            existingItems.forEach((item, index) => {
                if (index > 0) item.remove();
            });
            
            // Set the first item
            const firstItem = testItemsContainer.querySelector('input[name="test_items[]"]');
            firstItem.value = 'Kiểm tra ngoại quan';
            
            // Add additional default items
            const defaultItems = [
                'Kiểm tra chức năng cơ bản',
                'Kiểm tra hoạt động liên tục'
            ];
            
            defaultItems.forEach(itemText => {
                addTestItem();
                const newItems = testItemsContainer.querySelectorAll('input[name="test_items[]"]');
                newItems[newItems.length - 1].value = itemText;
            });
        }
    </script>
</body>
</html> 