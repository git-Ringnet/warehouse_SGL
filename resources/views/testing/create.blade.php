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
    <link rel="stylesheet" href="{{ asset('css/supplier-dropdown.css') }}">
    <script src="{{ asset('js/date-format.js') }}"></script>
    <style>
        .required::after {
            content: " *";
            color: #ef4444;
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <h1 class="text-xl font-bold text-gray-800">Tạo phiếu kiểm thử mới</h1>
            <div class="flex items-center gap-2">
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
                                <label class="block text-sm font-medium text-gray-700 mb-1 required">Mã phiếu kiểm thử</label>
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
                                <small class="text-gray-500 text-xs mt-1 block">Lưu ý: Phiếu kiểm thử Thiết bị thành phẩm chỉ được tạo thông qua lắp ráp</small>
                                @error('test_type')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Ngày kiểm thử -->
                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kiểm thử</label>
                                <input type="text" id="test_date" name="test_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white date-input" value="{{ date('d/m/Y') }}" required>
                                @error('test_date')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                                                <div class="mt-4">
                                <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người tiếp nhận kiểm thử</label>
                                <div class="relative">
                                    <input type="text" id="receiver_id_search" 
                                           placeholder="Tìm kiếm người tiếp nhận kiểm thử..." 
                                           class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <div id="receiver_id_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        @foreach($employees as $employee)
                                            <div class="employee-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                                                 data-value="{{ $employee->id }}" 
                                                 data-text="{{ $employee->name }}">
                                                {{ $employee->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" id="receiver_id" name="receiver_id" required>
                                </div>
                                @error('receiver_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                    </div>

                    <!-- Thêm vật tư/hàng hóa -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Thêm vật tư, hàng hóa hoặc thành phẩm</h2>
                        
                        <div id="items-container">
                            <div class="item-row mb-6 border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Loại</label>
                                        <select name="items[0][item_type]" class="item-type w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn loại --</option>
                                    <option value="material">Vật tư</option>
                                            <option value="product">Hàng hóa</option>
                                </select>
                            </div>
                            
                            <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư/hàng hóa</label>
                                        <div class="relative">
                                            <input type="text" id="item_search_0" 
                                                   placeholder="Chọn loại trước, sau đó tìm kiếm..." 
                                                   class="item-search w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                            <div id="item_dropdown_0" class="item-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                                <!-- Options will be populated dynamically -->
                                            </div>
                                            <input type="hidden" name="items[0][id]" id="item_name_0" class="item-name" required>
                                        </div>
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
                        
                                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                        <div class="relative">
                                            <input type="number" name="items[0][quantity]" min="1" class="quantity-input w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="1" required onchange="checkInventory(this, 0)">
                                            <div class="inventory-warning text-xs mt-1 hidden">
                                                <span class="text-red-600 font-medium">⚠️ Không đủ tồn kho</span>
                                            </div>
                                        </div>
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
                        <h3 class="text-md font-medium text-gray-800 mb-3">Tổng hợp vật tư, hàng hóa hoặc thành phẩm đã thêm</h3>
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
                            <h2 class="text-lg font-semibold text-gray-800">Hạng mục kiểm thử (Không bắt buộc)</h2>
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
                                    <input type="text" name="test_items[]" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập hạng mục kiểm thử (không bắt buộc)">
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
            
            // Thêm event listeners cho các trường input
            // Event listener cho item type change
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('item-type')) {
                    const index = e.target.name.match(/\[(\d+)\]/)[1];
                    updateItemOptions(e.target, index);
                }
            });
            
            // Event listener cho item name change
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('item-name')) {
                    const index = e.target.name.match(/\[(\d+)\]/)[1];
                    checkInventory(e.target, index);
                }
            });
            
            // Event listener cho warehouse change
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('warehouse-select')) {
                    const index = e.target.name.match(/\[(\d+)\]/)[1];
                    checkInventory(e.target, index);
                }
            });
            
            // Event listener cho quantity change
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('quantity-input')) {
                    const index = e.target.name.match(/\[(\d+)\]/)[1];
                    checkInventory(e.target, index);
                }
            });
            
            // Event listener để cập nhật bảng tổng hợp
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('item-type') || 
                    e.target.classList.contains('item-name') || 
                    e.target.classList.contains('warehouse-select') || 
                    e.target.classList.contains('quantity-input')) {
                    console.log('Field changed, updating summary table...');
                    setTimeout(updateSummaryTable, 100); // Delay để đảm bảo value đã được cập nhật
                }
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
        const testCodeElement = document.getElementById('test_code');
        if (testCodeElement) {
            // Remove existing event listeners to prevent duplicates
            testCodeElement.removeEventListener('input', checkTestCodeHandler);
            testCodeElement.addEventListener('input', checkTestCodeHandler);
        }
        
        function checkTestCodeHandler(e) {
            checkTestCode(e.target.value);
        }
        
        // Biến để theo dõi việc đang fetch data
        let isFetching = false;
        
        // Gắn event listener cho tất cả các dropdown item-type
        function attachItemTypeListeners() {
            document.querySelectorAll('.item-type').forEach((select, index) => {
                // Remove existing event listeners
                select.removeEventListener('change', itemTypeChangeHandler);
                // Add new event listener
                select.addEventListener('change', itemTypeChangeHandler);
            });
        }
        
        function itemTypeChangeHandler(e) {
            const index = Array.from(document.querySelectorAll('.item-type')).indexOf(e.target);
            updateItemOptions(e.target, index);
        }
        
        // Gắn event listener cho tất cả các field khác
        function attachAllListeners() {
            // Gắn event listener cho item-type dropdowns
            attachItemTypeListeners();
            
            // Gắn event listener cho các field khác
            document.querySelectorAll('.item-name, .warehouse-select, .quantity-input').forEach((element, index) => {
                const itemRow = element.closest('.item-row');
                const itemIndex = Array.from(document.querySelectorAll('.item-row')).indexOf(itemRow);
                
                // Remove existing event listeners
                element.removeEventListener('change', inventoryChangeHandler);
                // Add new event listener
                element.addEventListener('change', inventoryChangeHandler);
            });
        }
        
        function inventoryChangeHandler(e) {
            const itemRow = e.target.closest('.item-row');
            const itemIndex = Array.from(document.querySelectorAll('.item-row')).indexOf(itemRow);
            checkInventory(e.target, itemIndex);
            
            // Force validation khi quantity thay đổi
            if (e.target.type === 'number') {
                const quantity = parseInt(e.target.value) || 0;
                const itemType = itemRow.querySelector('.item-type')?.value;
                const itemId = itemRow.querySelector('.item-name')?.value;
                const warehouseId = itemRow.querySelector('.warehouse-select')?.value;
                
                if (itemType && itemId && warehouseId && quantity > 0) {
                    setTimeout(() => {
                        console.log('🔄 Force validation after quantity change...');
                        validateSerialSelection(itemIndex, itemType, itemId, warehouseId, quantity);
                    }, 100);
                }
            }
        }
        
        // Global click outside handler for all dropdowns
        document.addEventListener('click', function(e) {
            // Hide all material dropdowns when clicking outside
            document.querySelectorAll('.item-dropdown').forEach(dropdown => {
                const itemSearch = dropdown.previousElementSibling;
                if (itemSearch && !itemSearch.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                    dropdown.style.display = 'none';
                }
            });
        });
        
        // Initialize material search for the first item
        initializeMaterialSearch(0);
        
        // Attach inventory change listeners for the first item
        document.querySelectorAll('.warehouse-select, .quantity-input').forEach((element) => {
            element.addEventListener('change', inventoryChangeHandler);
        });
        
        function updateItemOptions(selectElement, index) {
            const itemType = selectElement.value;
            const itemNameSelect = document.getElementById('item_name_' + index);
            
            console.log('updateItemOptions called for index:', index, 'type:', itemType);
            
            // Ngăn chặn gọi nhiều lần
            if (isFetching) {
                console.log('Already fetching, skipping...');
                return;
            }
            
            // Reset item name select và itemsData
            itemNameSelect.innerHTML = '<option value="">-- Chọn --</option>';
            itemsData = itemsData.filter(item => item.type !== itemType);
            
            if (!itemType) return;
            
            isFetching = true;
            
            // Fetch items based on type
            fetch(`/api/testing/materials/${itemType}`)
                .then(response => response.json())
                .then(items => {
                    console.log('Raw items from API:', items.length);
                    
                    // Sử dụng Set để loại bỏ duplicate dựa trên id
                    const uniqueItems = new Map();
                    items.forEach(item => {
                        if (!uniqueItems.has(item.id)) {
                            uniqueItems.set(item.id, item);
                        } else {
                            console.log('Duplicate found in API response:', item);
                        }
                    });
                    
                    console.log('Before adding options, dropdown has:', itemNameSelect.children.length, 'options');
                    
                    // Thêm options vào dropdown
                    uniqueItems.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = `[${item.code}] ${item.name}`;
                        itemNameSelect.appendChild(option);
                        
                        // Cập nhật itemsData
                        itemsData.push({
                            id: item.id,
                            code: item.code,
                            name: item.name,
                            type: itemType
                        });
                    });
                    
                    console.log('After adding options, dropdown has:', itemNameSelect.children.length, 'options');
                    console.log('Items loaded:', items.length, 'Unique items:', uniqueItems.size);
                    
                    // Kiểm tra xem có duplicate trong dropdown không
                    const optionValues = Array.from(itemNameSelect.children).map(opt => opt.value);
                    const uniqueValues = [...new Set(optionValues)];
                    if (optionValues.length !== uniqueValues.length) {
                        console.warn('WARNING: Duplicate options found in dropdown!');
                        console.log('All option values:', optionValues);
                        console.log('Unique option values:', uniqueValues);
                    }
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                })
                .finally(() => {
                    isFetching = false;
                });
        }

        function checkInventory(element, index) {
            const itemRow = element.closest('.item-row');
            const itemType = itemRow.querySelector('.item-type').value;
            const itemId = itemRow.querySelector('.item-name').value;
            const warehouseId = itemRow.querySelector('.warehouse-select').value;
            const quantityInput = itemRow.querySelector('input[type="number"]');
            const quantity = parseInt(quantityInput.value) || 0;
            
            console.log('checkInventory called:', { itemType, itemId, warehouseId, quantity });
            
            // Reset màu về mặc định
            quantityInput.classList.remove('border-red-500', 'border-green-500');
            quantityInput.classList.add('border-gray-300');
            
            // Tìm warning element
            const warningElement = quantityInput.parentElement.querySelector('.inventory-warning');
            if (warningElement) {
                warningElement.classList.add('hidden');
            }
            
            // Tự động check ngay khi có đủ thông tin
            if (itemType && itemId && warehouseId && quantity > 0) {
                fetch(`/api/inventory/${itemType}/${itemId}/${warehouseId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Inventory data:', data);
                        const availableQuantity = parseInt(data.available_quantity) || 0;
                        
                        if (availableQuantity < quantity) {
                            quantityInput.classList.remove('border-gray-300', 'border-green-500');
                            quantityInput.classList.add('border-red-500');
                            if (warningElement) {
                                warningElement.classList.remove('hidden');
                            }
                            console.log('Quantity exceeds stock - RED BORDER');
                        } else {
                            quantityInput.classList.remove('border-gray-300', 'border-red-500');
                            quantityInput.classList.add('border-green-500');
                            if (warningElement) {
                                warningElement.classList.add('hidden');
                            }
                            console.log('Quantity OK - GREEN BORDER');
                        }
                    })
                    .catch(error => {
                        console.error('Error checking inventory:', error);
                        quantityInput.classList.remove('border-gray-300', 'border-green-500');
                        quantityInput.classList.add('border-red-500');
                        if (warningElement) {
                            warningElement.classList.remove('hidden');
                        }
                    });
            }
            
            // Reload serials khi thay đổi item hoặc warehouse
            if (element.classList.contains('item-name') || element.classList.contains('warehouse-select')) {
                updateSummaryTable();
            }
            
            // Reload serials khi thay đổi số lượng
            if (element.classList.contains('quantity-input')) {
                updateSummaryTable();
            }
        }

        function addItem() {
            const container = document.getElementById('items-container');
            const template = container.children[0].cloneNode(true);
            const currentItemCounter = container.children.length;
                                    
            // Update indices
            template.querySelectorAll('select, input, div').forEach(element => {
                if (element.name) {
                    element.name = element.name.replace('[0]', `[${currentItemCounter}]`);
                }
                if (element.id) {
                    element.id = element.id.replace('_0', `_${currentItemCounter}`);
                }
            });
            
            // Reset values
            template.querySelectorAll('select').forEach(select => {
                select.selectedIndex = 0;
            });
            template.querySelectorAll('input[type="number"]').forEach(input => {
                input.value = 1;
            });
            
            // Reset search inputs
            template.querySelectorAll('.item-search').forEach(input => {
                input.value = '';
                input.placeholder = 'Chọn loại trước, sau đó tìm kiếm...';
            });
            template.querySelectorAll('.item-name').forEach(input => {
                input.value = '';
            });
            
            // Reset warning elements
            template.querySelectorAll('.inventory-warning').forEach(warning => {
                warning.classList.add('hidden');
            });
            
            container.appendChild(template);
            
            console.log('Adding new item with index:', currentItemCounter);
            
            // Debug: Check if elements exist after cloning
            const debugSearch = template.querySelector(`#item_search_${currentItemCounter}`);
            const debugDropdown = template.querySelector(`#item_dropdown_${currentItemCounter}`);
            console.log('Debug elements after cloning:', {
                search: !!debugSearch,
                dropdown: !!debugDropdown,
                searchId: debugSearch?.id,
                dropdownId: debugDropdown?.id
            });
            
            // Initialize material search for the new item
            initializeMaterialSearch(currentItemCounter);
            
            // Get the actual appended element (not the template)
            const actualNewItemRow = container.children[container.children.length - 1];
            
            // Attach inventory change listeners for the new item
            actualNewItemRow.querySelectorAll('.warehouse-select, .quantity-input').forEach((element) => {
                element.removeEventListener('change', inventoryChangeHandler);
                element.addEventListener('change', inventoryChangeHandler);
            });
            
            // Note: Item type change listener is handled by initializeMaterialSearch
            
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
            
            console.log('updateSummaryTable called, items count:', items.length);
            
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
                const itemType = item.querySelector('.item-type').value;
                const itemId = item.querySelector('.item-name').value; // This is now the hidden input
                const warehouseId = item.querySelector('.warehouse-select').value;
                const quantity = item.querySelector('input[type="number"]').value;
                
                console.log(`Item ${index}:`, { itemType, itemId, warehouseId, quantity });
                
                if (!itemType || !itemId || !warehouseId || !quantity) {
                    console.log(`Item ${index} skipped - missing data`);
                    return;
                }
                
                const itemData = itemsData.find(data => data.id == itemId && data.type === itemType);
                const warehouseData = JSON.parse('@json($warehouses)');
                const warehouse = warehouseData.find(w => w.id == warehouseId);
                
                console.log('itemsData:', itemsData);
                console.log('Looking for:', { itemId, itemType });
                console.log('Found itemData:', itemData);
                
                const typeText = itemType === 'material' ? 'Vật tư' : 'Hàng hóa';
                const itemText = itemData ? `[${itemData.code}] ${itemData.name}` : 'N/A';
                const warehouseText = warehouse ? warehouse.name : 'N/A';
                
                console.log(`Adding item ${index} to table:`, { typeText, itemText, warehouseText, quantity });
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="py-2 px-3 border-b border-gray-200">${index + 1}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${typeText}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${itemText}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${quantity}</td>
                    <td class="py-2 px-3 border-b border-gray-200">${warehouseText}</td>
                    <td class="py-2 px-3 border-b border-gray-200">
                        <div class="serial-checkboxes">
                            <!-- Checkboxes sẽ được tạo động bằng JavaScript -->
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
                
                // Load serials cho item này
                loadSerials(row.querySelector('.serial-checkboxes'), index, itemType, itemId, warehouseId, quantity);
            });
        }

        function loadSerials(containerElement, index, itemType, itemId, warehouseId, quantity) {
            if (!itemType || !itemId || !warehouseId || !quantity) return;
            
            // Clear container
            containerElement.innerHTML = '';
            
            fetch(`/api/testing/serials?type=${itemType}&item_id=${itemId}&warehouse_id=${warehouseId}&quantity=${quantity}`)
                .then(response => response.json())
                .then(data => {
                    if (data.serials && data.serials.length > 0) {
                        // Lọc bỏ serial rỗng
                        const validSerials = data.serials.filter(serial => !empty(serial.serial_number));
                        
                        if (validSerials.length > 0) {
                            validSerials.forEach((serial, serialIndex) => {
                                const checkboxDiv = document.createElement('div');
                                checkboxDiv.className = 'flex items-center mb-1';
                                
                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.name = `items[${index}][serials][]`;
                                checkbox.value = serial.serial_number || '';
                                checkbox.id = `serial_${index}_${serialIndex}`;
                                checkbox.className = 'mr-2';
                                
                                // Tự động check checkbox đầu tiên nếu có ít hơn hoặc bằng số lượng cần
                                if (serialIndex < quantity) {
                                    checkbox.checked = true;
                                }
                                
                                // Thêm event listener để theo dõi thay đổi serial và validation
                                checkbox.addEventListener('change', function(e) {
                                    console.log('🔍 Serial checkbox changed:', {
                                        value: e.target.value,
                                        checked: e.target.checked,
                                        index: index,
                                        quantity: quantity
                                    });
                                    
                                    // Gọi validation NGAY LẬP TỨC
                                    setTimeout(() => {
                                        validateSerialSelection(index, itemType, itemId, warehouseId, quantity);
                                    }, 10);
                                });
                                
                                const label = document.createElement('label');
                                label.htmlFor = `serial_${index}_${serialIndex}`;
                                label.textContent = serial.serial_number || 'Không có Serial';
                                label.className = 'text-sm';
                                
                                checkboxDiv.appendChild(checkbox);
                                checkboxDiv.appendChild(label);
                                containerElement.appendChild(checkboxDiv);
                            });
                            
                            // Thêm thông báo về số lượng serial
                            const infoDiv = document.createElement('div');
                            infoDiv.className = 'text-blue-600 text-xs mt-2 bg-blue-50 p-2 rounded border border-blue-300';
                            infoDiv.innerHTML = `
                                <div class="font-bold">📊 Thông tin Serial:</div>
                                <div>• Có <strong>${validSerials.length}</strong> serial khả dụng</div>
                                <div>• Số lượng kiểm thử: <strong>${quantity}</strong></div>
                                <div class="text-xs text-gray-600 mt-1">💡 Chỉ hiển thị serial từ kho có tồn kho > 0</div>
                            `;
                            containerElement.appendChild(infoDiv);
                            
                            // Test validation ngay sau khi load serial
                            setTimeout(() => {
                                console.log('🧪 Testing validation after loading serials...');
                                validateSerialSelection(index, itemType, itemId, warehouseId, quantity);
                            }, 200);
                        } else {
                            const noSerialDiv = document.createElement('div');
                            noSerialDiv.className = 'text-gray-600 text-sm bg-gray-50 p-3 rounded-lg border border-gray-300';
                            noSerialDiv.innerHTML = `
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">ℹ️</span>
                                    <div>
                                        <div class="font-bold">Không có dữ liệu Serial</div>
                                        <div class="text-xs text-gray-600 mt-1">
                                            • API không trả về dữ liệu serial<br>
                                            • Hoặc có lỗi trong quá trình xử lý<br>
                                            • Vui lòng kiểm tra lại thông tin
                                        </div>
                                    </div>
                                </div>
                            `;
                            containerElement.appendChild(noSerialDiv);
                        }
                    } else {
                        const noSerialDiv = document.createElement('div');
                        noSerialDiv.className = 'text-gray-600 text-sm bg-gray-50 p-3 rounded-lg border border-gray-300';
                        noSerialDiv.innerHTML = `
                            <div class="flex items-center">
                                <span class="text-lg mr-2">ℹ️</span>
                                <div>
                                    <div class="font-bold">Không có dữ liệu Serial</div>
                                    <div class="text-xs text-gray-600 mt-1">
                                        • API không trả về dữ liệu serial<br>
                                        • Hoặc có lỗi trong quá trình xử lý<br>
                                        • Vui lòng kiểm tra lại thông tin
                                    </div>
                                </div>
                            </div>
                        `;
                        containerElement.appendChild(noSerialDiv);
                    }
                })
                .catch(error => {
                    console.error('Error loading serials:', error);
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'text-red-600 text-sm bg-red-50 p-3 rounded-lg border border-red-300';
                    errorDiv.innerHTML = `
                        <div class="flex items-center">
                            <span class="text-lg mr-2">❌</span>
                            <div>
                                <div class="font-bold">Lỗi tải Serial</div>
                                <div class="text-xs text-red-600 mt-1">
                                    • Không thể kết nối đến server<br>
                                    • Hoặc có lỗi trong quá trình xử lý<br>
                                    • Vui lòng thử lại hoặc liên hệ admin
                                </div>
                            </div>
                        </div>
                    `;
                    containerElement.appendChild(errorDiv);
                });
        }
        
        // Validation: Kiểm tra số lượng serial được chọn không vượt quá số lượng kiểm thử
        function validateSerialSelection(index, itemType, itemId, warehouseId, quantity) {
            console.log('=== validateSerialSelection START ===');
            console.log('Index:', index, 'Type:', itemType, 'ID:', itemId, 'Warehouse:', warehouseId, 'Quantity:', quantity);
            
            // Tìm item row theo index
            const allItemRows = document.querySelectorAll('.item-row');
            console.log('Total item rows found:', allItemRows.length);
            
            if (index >= allItemRows.length) {
                console.error('Index out of range:', index, 'vs', allItemRows.length);
                return;
            }
            
            const itemRow = allItemRows[index];
            console.log('Item row found:', itemRow);
            
            // Tìm tất cả checkbox serial trong item này - sử dụng selector cụ thể hơn
            const serialCheckboxes = itemRow.querySelectorAll('input[type="checkbox"][name*="serials"]');
            const selectedSerials = itemRow.querySelectorAll('input[type="checkbox"][name*="serials"]:checked');
            const selectedCount = selectedSerials.length;
            
            console.log('Serial elements found:', {
                totalCheckboxes: serialCheckboxes.length,
                selectedCount: selectedCount,
                quantity: quantity,
                checkboxes: Array.from(serialCheckboxes).map(cb => ({value: cb.value, checked: cb.checked}))
            });
            
            // Xóa cảnh báo cũ nếu có
            const oldWarning = itemRow.querySelector('.serial-warning');
            if (oldWarning) oldWarning.remove();
            
            const oldInfo = itemRow.querySelector('.serial-info');
            if (oldInfo) oldInfo.remove();
            
            if (selectedCount > quantity) {
                // Hiển thị cảnh báo NGAY LẬP TỨC - LỚN VÀ RÕ RÀNG
                const warningDiv = document.createElement('div');
                warningDiv.className = 'serial-warning text-red-700 text-sm font-bold bg-red-100 p-3 rounded-lg border-2 border-red-400 shadow-lg';
                warningDiv.innerHTML = `
                    <div class="flex items-center">
                        <span class="text-xl mr-2">⚠️</span>
                        <div>
                            <div class="font-bold">LỖI VALIDATION!</div>
                            <div>Đã chọn <strong>${selectedCount}</strong> serial nhưng số lượng kiểm thử chỉ <strong>${quantity}</strong></div>
                        </div>
                    </div>
                `;
                warningDiv.style.display = 'block';
                warningDiv.style.marginTop = '10px';
                warningDiv.style.marginBottom = '10px';
                
                // Thêm vào item row - đặt ở vị trí dễ nhìn
                const serialContainer = itemRow.querySelector('.serial-container');
                if (serialContainer) {
                    serialContainer.appendChild(warningDiv);
                } else {
                    itemRow.appendChild(warningDiv);
                }
                
                console.log('⚠️ Warning displayed:', warningDiv.textContent);
                
                // Bỏ check serial cuối cùng được chọn
                const lastChecked = selectedSerials[selectedSerials.length - 1];
                if (lastChecked) {
                    lastChecked.checked = false;
                    console.log(`🔒 Unchecked serial: ${lastChecked.value}`);
                    
                    // Gọi lại validation để cập nhật warning
                    setTimeout(() => validateSerialSelection(index, itemType, itemId, warehouseId, quantity), 100);
                }
            } else {
                // Hiển thị thông tin OK nếu có serial được chọn
                if (selectedCount > 0) {
                    const infoDiv = document.createElement('div');
                    infoDiv.className = 'serial-info text-green-700 text-sm font-bold bg-green-100 p-3 rounded-lg border-2 border-green-400';
                    infoDiv.innerHTML = `
                        <div class="flex items-center">
                            <span class="text-xl mr-2">✅</span>
                            <div>
                                <div class="font-bold">OK!</div>
                                <div>Đã chọn <strong>${selectedCount}/${quantity}</strong> serial</div>
                            </div>
                        </div>
                    `;
                    infoDiv.style.display = 'block';
                    infoDiv.style.marginTop = '10px';
                    infoDiv.style.marginBottom = '10px';
                    
                    const serialContainer = itemRow.querySelector('.serial-container');
                    if (serialContainer) {
                        serialContainer.appendChild(infoDiv);
                    } else {
                        itemRow.appendChild(infoDiv);
                    }
                    
                    console.log('✅ Info displayed:', infoDiv.textContent);
                }
            }
            
            console.log('=== validateSerialSelection END ===');
        }
        
        // Helper function để kiểm tra empty
        function empty(value) {
            return value === null || value === undefined || value === '';
        }
        
        // Test function để kiểm tra validation hoạt động
        function testSerialValidation() {
            console.log('🧪 Testing serial validation...');
            const itemRows = document.querySelectorAll('.item-row');
            console.log('Found', itemRows.length, 'item rows');
            
            itemRows.forEach((row, index) => {
                const quantityInput = row.querySelector('input[type="number"]');
                if (quantityInput) {
                    const quantity = parseInt(quantityInput.value) || 0;
                    console.log(`Item ${index} quantity:`, quantity);
                    
                    // Test validation với quantity hiện tại
                    if (quantity > 0) {
                        validateSerialSelection(index, 'material', '1', '1', quantity);
                    }
                }
            });
        }
        
        // Gọi test function khi trang load xong
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Page loaded, testing serial validation...');
            setTimeout(testSerialValidation, 1000); // Đợi 1 giây để đảm bảo mọi thứ load xong
        });

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

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const testType = document.getElementById('test_type').value;
            
            // Kiểm tra không cho phép tạo phiếu kiểm thử Thiết bị thành phẩm trực tiếp
            if (testType === 'finished_product') {
                e.preventDefault();
                alert('Không thể tạo phiếu kiểm thử Thiết bị thành phẩm trực tiếp. Phiếu này chỉ được tạo thông qua lắp ráp.');
                return false;
            }

            // Kiểm tra các trường bắt buộc
            const requiredFields = [
                'test_code',
                'test_type', 
                'test_date',
                'receiver_id'
            ];

            for (let field of requiredFields) {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ thông tin bắt buộc.');
                    element.focus();
                    return false;
                }
            }

            // Kiểm tra có ít nhất một item được thêm và validation serial
            const items = document.querySelectorAll('.item-row');
            let hasValidItem = false;
            let hasSerialError = false;
            let errorDetails = [];
            
            console.log('Validating form with', items.length, 'items');
            
            items.forEach((item, itemIndex) => {
                const itemType = item.querySelector('.item-type').value;
                const itemId = item.querySelector('.item-name').value;
                const warehouseId = item.querySelector('.warehouse-select').value;
                const quantity = parseInt(item.querySelector('input[type="number"]').value) || 0;
                
                console.log(`Item ${itemIndex}:`, {
                    itemType,
                    itemId,
                    warehouseId,
                    quantity
                });
                
                if (itemType && itemId && warehouseId && quantity > 0) {
                    hasValidItem = true;
                    
                    // Kiểm tra serial selection - tìm tất cả checkbox serial trong item này
                    const serialCheckboxes = item.querySelectorAll('input[type="checkbox"][name*="[serials][]"]');
                    const checkedSerials = item.querySelectorAll('input[type="checkbox"][name*="[serials][]"]:checked');
                    const selectedSerialCount = checkedSerials.length;
                    
                    console.log(`Item ${itemIndex} serials:`, {
                        totalCheckboxes: serialCheckboxes.length,
                        checkedCount: selectedSerialCount,
                        quantity: quantity
                    });
                    
                    // Kiểm tra số lượng serial không vượt quá số lượng kiểm thử
                    if (selectedSerialCount > quantity) {
                        hasSerialError = true;
                        const itemName = item.querySelector('.item-name option:checked')?.text || `Item ${itemIndex}`;
                        errorDetails.push(`${itemName}: Chọn ${selectedSerialCount} serial nhưng số lượng kiểm thử chỉ ${quantity}`);
                        console.error(`Item ${itemIndex}: ${selectedSerialCount} serials selected but quantity is ${quantity}`);
                    }
                }
            });

            if (!hasValidItem) {
                e.preventDefault();
                alert('Vui lòng thêm ít nhất một vật tư/hàng hóa.');
                return false;
            }
            
            if (hasSerialError) {
                e.preventDefault();
                const errorMessage = 'Số lượng serial được chọn vượt quá số lượng kiểm thử:\n\n' + errorDetails.join('\n');
                alert(errorMessage);
                console.error('Serial validation failed:', errorDetails);
                return false;
            }

            console.log('Form validation passed');
            return true;
        });

        // Receiver ID search functionality
        const receiverIdSearch = document.getElementById('receiver_id_search');
        const receiverIdDropdown = document.getElementById('receiver_id_dropdown');
        const receiverIdHidden = document.getElementById('receiver_id');
        let selectedReceiverId = '';
        let selectedReceiverName = '';

        // Show dropdown on focus
        receiverIdSearch.addEventListener('focus', function() {
            receiverIdDropdown.classList.remove('hidden');
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!receiverIdSearch.contains(e.target) && !receiverIdDropdown.contains(e.target)) {
                receiverIdDropdown.classList.add('hidden');
            }
        });

        // Filter employees based on search input
        receiverIdSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = receiverIdDropdown.querySelectorAll('.employee-option');
            
            options.forEach(option => {
                const text = option.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    option.style.display = 'block';
                    // Highlight search term
                    const highlightedText = option.textContent.replace(
                        new RegExp(searchTerm, 'gi'),
                        match => `<mark class="bg-yellow-200">${match}</mark>`
                    );
                    option.innerHTML = highlightedText;
                } else {
                    option.style.display = 'none';
                }
            });
            
            receiverIdDropdown.classList.remove('hidden');
        });

        // Handle employee option selection
        receiverIdDropdown.addEventListener('click', function(e) {
            if (e.target.classList.contains('employee-option')) {
                const option = e.target;
                selectedReceiverId = option.dataset.value;
                selectedReceiverName = option.dataset.text;
                
                receiverIdSearch.value = selectedReceiverName;
                receiverIdHidden.value = selectedReceiverId;
                receiverIdDropdown.classList.add('hidden');
                
                // Remove highlighting
                option.innerHTML = option.dataset.text;
            }
        });

        // Keyboard navigation
        receiverIdSearch.addEventListener('keydown', function(e) {
            const options = Array.from(receiverIdDropdown.querySelectorAll('.employee-option:not([style*="display: none"])'));
            const currentIndex = options.findIndex(option => option.classList.contains('highlight'));
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (currentIndex < options.length - 1) {
                        options.forEach(option => option.classList.remove('highlight'));
                        options[currentIndex + 1].classList.add('highlight');
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (currentIndex > 0) {
                        options.forEach(option => option.classList.remove('highlight'));
                        options[currentIndex - 1].classList.add('highlight');
                    }
                    break;
                case 'Enter':
                    e.preventDefault();
                    const highlightedOption = receiverIdDropdown.querySelector('.employee-option.highlight');
                    if (highlightedOption) {
                        highlightedOption.click();
                    }
                    break;
                case 'Escape':
                    receiverIdDropdown.classList.add('hidden');
            }
        });

        // Initialize material search functionality for the first item
        initializeMaterialSearch(0);

        // Function to initialize material search for a specific item index
        function initializeMaterialSearch(itemIndex) {
            console.log('Initializing material search for itemIndex:', itemIndex);
            const itemSearch = document.getElementById(`item_search_${itemIndex}`);
            const itemDropdown = document.getElementById(`item_dropdown_${itemIndex}`);
            const itemHidden = document.getElementById(`item_name_${itemIndex}`);
            
            console.log('Elements found:', {
                itemSearch: !!itemSearch,
                itemDropdown: !!itemDropdown,
                itemHidden: !!itemHidden,
                itemSearchId: itemSearch?.id,
                itemDropdownId: itemDropdown?.id,
                itemHiddenId: itemHidden?.id
            });
            
            if (!itemSearch || !itemDropdown || !itemHidden) {
                console.log('Missing elements for itemIndex:', itemIndex, {
                    itemSearch: !!itemSearch,
                    itemDropdown: !!itemDropdown,
                    itemHidden: !!itemHidden
                });
                return;
            }
            
            const itemTypeSelect = itemSearch.closest('.item-row').querySelector('.item-type');
            
            console.log('Found all elements for itemIndex:', itemIndex);

            // Show dropdown on focus
            itemSearch.addEventListener('focus', function() {
                console.log('Focus event triggered for itemIndex:', itemIndex);
                populateMaterialOptions(itemIndex);
            });
            
            // Also show dropdown on click (backup)
            itemSearch.addEventListener('click', function() {
                console.log('Click event triggered for itemIndex:', itemIndex);
                populateMaterialOptions(itemIndex);
            });

            // Handle search input
            itemSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = itemDropdown.querySelectorAll('.material-option');
                
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'block';
                        // Highlight search term
                        const highlightedText = option.textContent.replace(
                            new RegExp(searchTerm, 'gi'),
                            match => `<mark class="bg-yellow-200">${match}</mark>`
                        );
                        option.innerHTML = highlightedText;
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                itemDropdown.classList.remove('hidden');
            });

            // Handle material option selection
            itemDropdown.addEventListener('click', function(e) {
                if (e.target.classList.contains('material-option')) {
                    const option = e.target;
                    const materialId = option.dataset.value;
                    const materialText = option.dataset.text;
                    
                    itemSearch.value = materialText;
                    itemHidden.value = materialId;
                    itemDropdown.classList.add('hidden');
                    
                    // Remove highlighting
                    option.innerHTML = option.dataset.text;
                    
                    // Trigger inventory check
                    checkInventory(itemHidden, itemIndex);
                }
            });

            // Note: Global click outside handler is now managed at document level

            // Update materials when item type changes
            const itemTypeChangeHandler = function() {
                console.log('Item type changed for itemIndex:', itemIndex, 'New value:', this.value);
                itemSearch.value = '';
                itemHidden.value = '';
                
                // Update placeholder based on selected type
                if (this.value === 'material') {
                    itemSearch.placeholder = 'Tìm kiếm vật tư...';
                } else if (this.value === 'product') {
                    itemSearch.placeholder = 'Tìm kiếm hàng hóa...';
                } else {
                    itemSearch.placeholder = 'Chọn loại trước, sau đó tìm kiếm...';
                }
                
                populateMaterialOptions(itemIndex);
            };
            
            // Remove any existing listener and add new one
            itemTypeSelect.removeEventListener('change', itemTypeChangeHandler);
            itemTypeSelect.addEventListener('change', itemTypeChangeHandler);
        }

        // Function to populate material options based on item type
        function populateMaterialOptions(itemIndex) {
            console.log('populateMaterialOptions called for itemIndex:', itemIndex);
            const itemSearch = document.getElementById(`item_search_${itemIndex}`);
            if (!itemSearch) {
                console.log('itemSearch not found for itemIndex:', itemIndex);
                return;
            }
            
            const itemRow = itemSearch.closest('.item-row');
            const itemTypeSelect = itemRow.querySelector('.item-type');
            const itemDropdown = document.getElementById(`item_dropdown_${itemIndex}`);
            
            if (!itemTypeSelect || !itemDropdown) {
                console.log('Missing elements for itemIndex:', itemIndex, {
                    itemTypeSelect: !!itemTypeSelect,
                    itemDropdown: !!itemDropdown
                });
                return;
            }

            const itemType = itemTypeSelect.value;
            let materials = [];

            if (itemType === 'material') {
                materials = @json($materials ?? []);
                console.log('Loading materials:', materials.length, 'for itemIndex:', itemIndex);
                console.log('Materials data:', materials);
            } else if (itemType === 'product') {
                materials = @json($goods ?? []);
                console.log('Loading goods:', materials.length, 'for itemIndex:', itemIndex);
                console.log('Goods data:', materials);
            } else {
                console.log('No item type selected for itemIndex:', itemIndex);
                itemDropdown.innerHTML = '<div class="px-3 py-2 text-gray-500">Vui lòng chọn loại trước</div>';
                return;
            }

            // Clear existing options
            itemDropdown.innerHTML = '';

            if (materials.length === 0) {
                itemDropdown.innerHTML = '<div class="px-3 py-2 text-gray-500">Không có dữ liệu</div>';
                return;
            }

            // Sort materials alphabetically
            materials.sort((a, b) => {
                const nameA = (a.code + ' - ' + a.name).toLowerCase();
                const nameB = (b.code + ' - ' + b.name).toLowerCase();
                return nameA.localeCompare(nameB);
            });

            // Add options
            materials.forEach(material => {
                const option = document.createElement('div');
                option.className = 'material-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                option.dataset.value = material.id;
                option.dataset.text = `${material.code} - ${material.name}`;
                option.textContent = `${material.code} - ${material.name}`;
                
                // Add click event listener to the option
                option.addEventListener('click', function() {
                    const materialId = this.dataset.value;
                    const materialText = this.dataset.text;
                    
                    const itemSearch = document.getElementById(`item_search_${itemIndex}`);
                    const itemHidden = document.getElementById(`item_name_${itemIndex}`);
                    
                    if (itemSearch && itemHidden) {
                        itemSearch.value = materialText;
                        itemHidden.value = materialId;
                        itemDropdown.classList.add('hidden');
                        itemDropdown.style.display = 'none';
                        
                        // Trigger inventory check
                        checkInventory(itemHidden, itemIndex);
                    }
                });
                
                itemDropdown.appendChild(option);
            });

            // Show dropdown automatically when materials are loaded (with a small delay)
            if (materials.length > 0) {
                console.log('Showing dropdown with', materials.length, 'items for itemIndex:', itemIndex);
                setTimeout(() => {
                    itemDropdown.classList.remove('hidden');
                    itemDropdown.style.display = 'block';
                    console.log('Dropdown visibility after showing:', {
                        classList: itemDropdown.classList.toString(),
                        style: itemDropdown.style.display,
                        hidden: itemDropdown.hidden
                    });
                }, 300); // Small delay to let user see the placeholder change
            } else {
                console.log('No materials to show for itemIndex:', itemIndex);
            }
        }
    </script>
</body>
</html> 