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
            <div class="flex items-center gap-2">
                <!-- <button type="button" class="generate-new-code bg-blue-100 hover:bg-blue-200 text-blue-600 h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i> Đổi mã mới
                </button> -->
            <a href="{{ route('testing.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
            </div>
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
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Mã phiếu kiểm thử -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu kiểm thử</label>
                                <div class="flex gap-2">
                                    <input type="text" id="test_code" name="test_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <button type="button" class="generate-new-code bg-blue-100 hover:bg-blue-200 text-blue-600 h-10 px-4 rounded-lg flex items-center transition-colors whitespace-nowrap">
                                        <i class="fas fa-sync-alt mr-2"></i> Đổi mã mới
                                    </button>
                                </div>
                                <div id="codeError" class="text-red-500 text-xs mt-1 hidden">Mã phiếu kiểm thử đã tồn tại</div>
                                @error('test_code')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        
                        <!-- Loại kiểm thử -->
                            <div>
                                <label for="test_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại kiểm thử</label>
                                <select id="test_type" name="test_type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn loại kiểm thử --</option>
                                    <option value="material">Kiểm thử Vật tư/Hàng hóa</option>
                                </select>
                                @error('test_type')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Ngày kiểm thử -->
                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kiểm thử</label>
                                <input type="date" id="test_date" name="test_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ date('Y-m-d') }}" required>
                                @error('test_date')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-4">
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

                    <!-- Thêm vật tư/hàng hóa -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Thêm vật tư, hàng hóa</h2>
                        
                        <div id="items-container">
                            <div class="item-row mb-6 border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Loại</label>
                                        <select name="items[0][item_type]" class="item-type w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="updateItemOptions(this, 0)">
                                    <option value="">-- Chọn loại --</option>
                                    <option value="material">Vật tư</option>
                                            <option value="product">Hàng hóa</option>
                                </select>
                            </div>
                            
                            <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư/hàng hóa</label>
                                        <select name="items[0][id]" id="item_name_0" class="item-name w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="checkInventory(this, 0)">
                                    <option value="">-- Chọn --</option>
                                </select>
                            </div>
                            
                            <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Kho hàng</label>
                                        <select name="items[0][warehouse_id]" class="warehouse-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="checkInventory(this, 0)">
                                            <option value="">-- Chọn kho hàng --</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                </select>
                                </div>
                            </div>
                        
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                        <div class="relative">
                                            <input type="number" name="items[0][quantity]" min="1" class="quantity-input w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="1" required onchange="checkInventory(this, 0)">
                                            <div class="inventory-warning hidden absolute -bottom-6 left-0 text-red-500 text-xs">
                                                Trong kho không đủ số lượng
                                            </div>
                                </div>
                            </div>
                            
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Serial</label>
                                        <select name="items[0][serial_numbers][]" multiple class="serial-select w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" style="min-height: 38px;">
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end mt-4">
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
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">MÃ - TÊN</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">SỐ LƯỢNG</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">KHO HÀNG</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">SERIAL</th>
                                    </tr>
                                </thead>
                                <tbody id="items-summary-table">
                                    <tr class="text-gray-500 text-center">
                                        <td colspan="6" class="py-4">Chưa có vật tư/hàng hóa nào được thêm</td>
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
                                    <input type="text" name="test_items[]" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập hạng mục kiểm thử">
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
        let inventoryData = {};
        
        // Tự động tạo mã phiếu kiểm thử khi tải trang
        document.addEventListener('DOMContentLoaded', function() {
            generateTestCode();

            // Thêm sự kiện click cho tất cả các nút đổi mã mới
            document.querySelectorAll('.generate-new-code').forEach(button => {
                button.addEventListener('click', generateTestCode);
            });
        });

        // Hàm tạo mã phiếu kiểm thử
        function generateTestCode() {
            const now = new Date();
            const year = now.getFullYear().toString().slice(-2);
            const month = (now.getMonth() + 1).toString().padStart(2, '0');
            const day = now.getDate().toString().padStart(2, '0');
            const hour = now.getHours().toString().padStart(2, '0');
            const minute = now.getMinutes().toString().padStart(2, '0');
            const second = now.getSeconds().toString().padStart(2, '0');
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const testCode = `QA${year}${month}${day}${hour}${minute}${second}${random}`;
            document.getElementById('test_code').value = testCode;
            checkTestCode(testCode);
        }

        // Kiểm tra mã phiếu kiểm thử có tồn tại không
        function checkTestCode(code) {
            fetch(`/api/testing/check-code?code=${code}`)
                .then(response => response.json())
                .then(data => {
                    const errorDiv = document.getElementById('codeError');
                    if (data.exists) {
                        errorDiv.classList.remove('hidden');
                        generateTestCode(); // Tạo mã mới nếu bị trùng
                    } else {
                        errorDiv.classList.add('hidden');
                    }
                });
        }

        // Thêm sự kiện kiểm tra khi nhập mã
        document.getElementById('test_code').addEventListener('input', function(e) {
            checkTestCode(e.target.value);
        });
        
        function updateItemOptions(selectElement, index) {
            const itemType = selectElement.value;
            const itemNameSelect = document.getElementById('item_name_' + index);
            
            // Reset item name select
            itemNameSelect.innerHTML = '<option value="">-- Chọn --</option>';
            
            if (!itemType) return;
            
            // Fetch items based on type
            fetch(`/api/testing/materials/${itemType}`)
                .then(response => response.json())
                .then(items => {
                    items.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = `[${item.code}] ${item.name}`;
                        itemNameSelect.appendChild(option);
                    });
                });
        }

        function checkInventory(element, index) {
            const itemType = document.querySelector(`select[name="items[${index}][item_type]"]`).value;
            const itemId = document.querySelector(`select[name="items[${index}][id]"]`).value;
            const warehouseId = document.querySelector(`select[name="items[${index}][warehouse_id]"]`).value;
            const quantityInput = document.querySelector(`input[name="items[${index}][quantity]"]`);
            const warningDiv = quantityInput.parentElement.querySelector('.inventory-warning');
            
            if (!itemType || !itemId || !warehouseId) return;
                    
            // Fetch inventory data
            fetch(`/api/inventory/${itemType}/${itemId}/${warehouseId}`)
                        .then(response => response.json())
                        .then(data => {
                    inventoryData[`${itemType}-${itemId}-${warehouseId}`] = data.quantity;
                    
                    // Check quantity
                    if (parseInt(quantityInput.value) > data.quantity) {
                        warningDiv.classList.remove('hidden');
                        quantityInput.classList.add('border-red-500');
                    } else {
                        warningDiv.classList.add('hidden');
                        quantityInput.classList.remove('border-red-500');
                            }
                            
                    // Update serials
                    updateSerials(index, data.serials || []);
                });
        }

        function updateSerials(index, serials) {
            const serialSelect = document.querySelector(`select[name="items[${index}][serial_numbers][]"]`);
            serialSelect.innerHTML = '';
            
                                serials.forEach(serial => {
                const option = document.createElement('option');
                                    option.value = serial;
                                    option.textContent = serial;
                                    serialSelect.appendChild(option);
                                });
        }

        function addItem() {
            const container = document.getElementById('items-container');
            const template = container.children[0].cloneNode(true);
                                    
            // Update indices
            template.querySelectorAll('select, input').forEach(element => {
                element.name = element.name.replace('[0]', `[${itemCounter}]`);
                if (element.id) {
                    element.id = element.id.replace('_0', `_${itemCounter}`);
                }
            });
            
            // Reset values
            template.querySelectorAll('select').forEach(select => {
                select.selectedIndex = 0;
            });
            template.querySelectorAll('input[type="number"]').forEach(input => {
                input.value = 1;
            });
            
            // Update event listeners
            template.querySelector('.item-type').setAttribute('onchange', `updateItemOptions(this, ${itemCounter})`);
            template.querySelector('.item-name').setAttribute('onchange', `checkInventory(this, ${itemCounter})`);
            template.querySelector('.warehouse-select').setAttribute('onchange', `checkInventory(this, ${itemCounter})`);
            template.querySelector('.quantity-input').setAttribute('onchange', `checkInventory(this, ${itemCounter})`);
            
            container.appendChild(template);
            itemCounter++;
            updateSummaryTable();
        }
        
        function removeItem(button) {
            const itemRow = button.closest('.item-row');
            if (document.querySelectorAll('.item-row').length > 1) {
                itemRow.remove();
                updateSummaryTable();
            }
        }

        function updateSummaryTable() {
            const tbody = document.getElementById('items-summary-table');
            const items = document.querySelectorAll('.item-row');
            
            if (items.length === 0) {
                tbody.innerHTML = `
                    <tr class="text-gray-500 text-center">
                        <td colspan="6" class="py-4">Chưa có vật tư/hàng hóa nào được thêm</td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = '';
            items.forEach((item, index) => {
                const type = item.querySelector('.item-type').value;
                const typeText = type === 'material' ? 'Vật tư' : 'Hàng hóa';
                const itemSelect = item.querySelector('.item-name');
                const itemText = itemSelect.options[itemSelect.selectedIndex]?.text || '--';
                const quantity = item.querySelector('input[type="number"]').value;
                const warehouseSelect = item.querySelector('.warehouse-select');
                const warehouseText = warehouseSelect.options[warehouseSelect.selectedIndex]?.text || '--';
                const serialSelect = item.querySelector('.serial-select');
                const selectedSerials = Array.from(serialSelect.selectedOptions).map(opt => opt.value).join(', ');
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="py-2 px-3 border-b border-gray-200">${index + 1}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${typeText}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${itemText}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${quantity}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${warehouseText}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${selectedSerials || '--'}</td>
            `;
                tbody.appendChild(row);
            });
                }

        function addTestItem() {
            const container = document.getElementById('test_items_container');
            const template = container.children[0].cloneNode(true);
            template.querySelector('input').value = '';
            container.appendChild(template);
        }
        
        function removeTestItem(button) {
            const item = button.closest('.test-item');
            if (document.querySelectorAll('.test-item').length > 1) {
                item.remove();
                                                }
        }

        function addDefaultTestItems() {
            const container = document.getElementById('test_items_container');
            container.innerHTML = '';
            
            const defaultItems = [
                'Kiểm tra ngoại quan',
                'Kiểm tra kích thước',
                'Kiểm tra chức năng',
                'Kiểm tra an toàn'
            ];
            
            defaultItems.forEach(item => {
                const div = document.createElement('div');
                div.className = 'test-item flex items-center gap-4';
                div.innerHTML = `
                    <input type="text" name="test_items[]" value="${item}" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <button type="button" onclick="removeTestItem(this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                container.appendChild(div);
            });
        }
    </script>
</body>
</html> 