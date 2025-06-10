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
            <a href="{{ url('/testing') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ url('/testing') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2">Thông tin cơ bản</h2>
                        
                        <!-- Loại kiểm thử -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="test_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại kiểm thử</label>
                                <select id="test_type" name="test_type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="toggleTestTypeFields()">
                                    <option value="">-- Chọn loại kiểm thử --</option>
                                    <option value="material">Kiểm thử Vật tư/Hàng hóa</option>
                                    <option value="finished_product">Kiểm thử Thiết bị thành phẩm</option>
                                </select>
                            </div>

                            <div>
                                <label for="test_engineer" class="block text-sm font-medium text-gray-700 mb-1 required">Người kiểm thử</label>
                                <select id="test_engineer" name="test_engineer" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn người kiểm thử --</option>
                                    <option value="1">Nguyễn Văn A</option>
                                    <option value="2">Trần Văn B</option>
                                    <option value="3">Lê Thị C</option>
                                    <option value="4">Phạm Văn D</option>
                                    <option value="5">Lê Văn E</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kiểm thử</label>
                                <input type="date" id="test_date" name="test_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div>
                                <label for="test_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu kiểm thử</label>
                                <input type="text" id="test_code" name="test_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="QA-{{ date('ymd') }}001" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Thêm vật tư/hàng hóa/thành phẩm -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Thêm vật tư, hàng hóa hoặc thành phẩm</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="item_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại</label>
                                <select id="item_type" name="item_type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="updateItemOptions()">
                                    <option value="">-- Chọn loại --</option>
                                    <option value="material">Vật tư</option>
                                    <option value="product">Hàng hóa</option>
                                    <option value="finished_product">Thiết bị thành phẩm</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="item_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư/hàng hóa</label>
                                <select id="item_name" name="item_name" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn --</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                                <select id="supplier" name="supplier" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                    <option value="1">ABC Electronics</option>
                                    <option value="2">Tech Solutions</option>
                                    <option value="3">VN Components</option>
                                    <option value="4">Global Tech</option>
                                    <option value="5">Mega Components</option>
                                </select>
                            </div>
                            
                            <!-- Hidden quantity field that will be updated automatically -->
                            <input type="hidden" id="item_quantity" name="item_quantity" value="0">
                        </div>
                        
                        <!-- Serial -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">Quản lý Serial</label>
                                <div class="flex items-center gap-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="serial_mode" value="select" class="h-4 w-4 text-blue-600 focus:ring-blue-500" checked onclick="toggleSerialMode('select')">
                                        <span class="ml-2 text-sm text-gray-700">Chọn từ danh sách</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="serial_mode" value="manual" class="h-4 w-4 text-blue-600 focus:ring-blue-500" onclick="toggleSerialMode('manual')">
                                        <span class="ml-2 text-sm text-gray-700">Nhập thủ công</span>
                                    </label>
                                </div>
                            </div>
                        
                            <!-- Chọn serial từ danh sách -->
                            <div id="select_serial_container" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="flex justify-between items-center mb-3">
                                    <h3 class="text-md font-medium text-gray-800">Chọn Serial</h3>
                                    <span class="text-sm text-gray-500" id="serial_count">Đã chọn: 0/0</span>
                                </div>
                                <div id="serial_list" class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-60 overflow-y-auto">
                                    <!-- Serials will be added here dynamically -->
                                    <p class="text-sm text-gray-500 col-span-full text-center py-4">Chọn vật tư/hàng hóa để hiển thị serial</p>
                                </div>
                                
                                <div class="mt-4 pt-3 border-t border-gray-200">
                                    <div class="flex items-center gap-4">
                                        <label class="block text-sm font-medium text-gray-700">Số lượng</label>
                                        <div class="relative rounded-md shadow-sm w-32">
                                            <input type="number" id="select_quantity" min="1" value="1" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                                            oninput="updateSelectedSerialsFromQuantity()">
                                        </div>
                                        <p class="text-xs text-gray-500">Nhập số lượng sẽ tự động chọn các serial đầu tiên</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Nhập serial thủ công -->
                            <div id="manual_serial_container" class="hidden border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <div class="mb-3">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-md font-medium text-gray-800">Nhập Serial mới</h3>
                                        <span class="text-sm text-blue-600 font-medium" id="manual_serial_count">0 serial</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mb-2">Mỗi serial trên một dòng</p>
                                    <textarea id="manual_serial" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm" placeholder="Nhập danh sách serial, mỗi serial một dòng" oninput="updateManualSerialCount()"></textarea>
                                </div>
                                
                                <div class="flex gap-2 items-center">
                                    <div class="flex-1">
                                        <label for="serial_prefix" class="block text-sm text-gray-600 mb-1">Tiền tố</label>
                                        <input type="text" id="serial_prefix" class="w-full h-8 border border-gray-300 rounded px-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm" placeholder="Ví dụ: 4G-MOD-">
                                    </div>
                                    <div class="flex-1">
                                        <label for="serial_count_input" class="block text-sm text-gray-600 mb-1">Số lượng</label>
                                        <input type="number" id="serial_count_input" min="1" value="1" class="w-full h-8 border border-gray-300 rounded px-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                                    </div>
                                    <div class="mt-6">
                                        <button type="button" class="h-8 bg-blue-500 text-white rounded px-4 text-sm hover:bg-blue-600" onclick="generateSerials()">Tạo</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="button" id="add_item_btn" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center" onclick="addItemToList()">
                                <i class="fas fa-plus mr-2"></i> Thêm vào danh sách
                            </button>
                        </div>
                    </div>
                    
                    <!-- Danh sách vật tư/hàng hóa đã thêm -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                        <div class="flex justify-between items-center mb-3">
                            <h2 class="text-lg font-semibold text-gray-800">Danh sách vật tư, hàng hóa đã thêm</h2>
                            <span class="text-sm text-gray-500" id="items_count">Tổng số: 0</span>
                        </div>
                        
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Serial</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nhà cung cấp</th>
                                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="added_items_list" class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500">Chưa có vật tư/hàng hóa nào được thêm</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Thông tin chi tiết kiểm thử -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin chi tiết kiểm thử</h2>
                        
                        <!-- Thông tin kiểm thử vật tư/hàng hóa -->
                        <div id="material_fields" class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">Mã lô</label>
                                    <input type="text" id="batch_number" name="batch_number" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã lô">
                                </div>
                            </div>
                        </div>

                        <!-- Hạng mục kiểm thử -->
                        <div class="mt-6">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-md font-medium text-gray-800">Hạng mục kiểm thử</h3>
                                <button type="button" onclick="addTestItem()" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm flex items-center">
                                    <i class="fas fa-plus mr-1"></i> Thêm hạng mục
                                </button>
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
                        <div class="mt-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú bổ sung nếu có"></textarea>
                        </div>
                    </div>

                    <!-- Submit buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ url('/testing') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
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
        // Sample data - this would come from database
        const itemTypes = {
            material: ['Module 4G', 'Module Công suất', 'Module IoTs', 'Ăng-ten', 'Bộ nguồn', 'Mạch điều khiển'],
            product: ['Bộ điều khiển SGL-500', 'Thiết bị đo nhiệt độ', 'Bộ phát wifi công nghiệp', 'Bộ chuyển đổi tín hiệu'],
            finished_product: ['Bộ thu phát SGL-4G-Premium', 'Thiết bị giám sát IOT-SGL-01', 'Hệ thống điều khiển thông minh SGL-Smart']
        };
        
        // Sample serial data per item - this would come from database
        const serialData = {
            'Module 4G': ['4G-MOD-2305621', '4G-MOD-2305622', '4G-MOD-2305623', '4G-MOD-2305624', '4G-MOD-2305625'],
            'Module Công suất': ['PWR-MOD-230101', 'PWR-MOD-230102', 'PWR-MOD-230103'],
            'Module IoTs': ['IOT-MOD-230201', 'IOT-MOD-230202', 'IOT-MOD-230203'],
            'Bộ điều khiển SGL-500': ['SGL-500-230001', 'SGL-500-230002'],
            'Bộ thu phát SGL-4G-Premium': ['SGL-4GP-230055', 'SGL-4GP-230056']
        };
        
        // Mapping of items to their suppliers
        const itemSupplierMapping = {
            'Module 4G': '1', // ABC Electronics
            'Module Công suất': '3', // VN Components
            'Module IoTs': '2', // Tech Solutions
            'Ăng-ten': '3', // VN Components
            'Bộ nguồn': '4', // Global Tech
            'Mạch điều khiển': '5', // Mega Components
            'Bộ điều khiển SGL-500': '1', // ABC Electronics
            'Thiết bị đo nhiệt độ': '2', // Tech Solutions
            'Bộ phát wifi công nghiệp': '4', // Global Tech
            'Bộ chuyển đổi tín hiệu': '5', // Mega Components
            'Bộ thu phát SGL-4G-Premium': '1', // ABC Electronics
            'Thiết bị giám sát IOT-SGL-01': '2', // Tech Solutions
            'Hệ thống điều khiển thông minh SGL-Smart': '5' // Mega Components
        };

        // Keep track of added items
        let addedItems = [];
        let addedItemCount = 0;
        
        // Toggle between serial selection modes
        function toggleSerialMode(mode) {
            if (mode === 'select') {
                document.getElementById('select_serial_container').classList.remove('hidden');
                document.getElementById('manual_serial_container').classList.add('hidden');
            } else {
                document.getElementById('select_serial_container').classList.add('hidden');
                document.getElementById('manual_serial_container').classList.remove('hidden');
            }
            updateItemQuantity();
        }
        
        // Generate serials based on prefix and count
        function generateSerials() {
            const prefix = document.getElementById('serial_prefix').value.trim();
            const count = parseInt(document.getElementById('serial_count_input').value) || 0;
            
            if (count <= 0) {
                alert('Số lượng phải lớn hơn 0');
                return;
            }
            
            const today = new Date();
            const dateStr = today.getFullYear().toString().slice(-2) + 
                           (today.getMonth() + 1).toString().padStart(2, '0') + 
                           today.getDate().toString().padStart(2, '0');
            
            let serials = [];
            for (let i = 1; i <= count; i++) {
                const randomDigits = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                serials.push(`${prefix}${dateStr}-${randomDigits}`);
            }
            
            const serialTextarea = document.getElementById('manual_serial');
            const existingSerials = serialTextarea.value.trim();
            
            serialTextarea.value = existingSerials 
                ? existingSerials + '\n' + serials.join('\n') 
                : serials.join('\n');
            
            updateManualSerialCount();
        }
        
        // Update manual serial count
        function updateManualSerialCount() {
            const serialText = document.getElementById('manual_serial').value.trim();
            const serialLines = serialText ? serialText.split('\n').filter(line => line.trim()) : [];
            
            document.getElementById('manual_serial_count').textContent = `${serialLines.length} serial`;
            updateItemQuantity();
        }

        // Update item options based on selected type
        function updateItemOptions() {
            const itemType = document.getElementById('item_type').value;
            const itemNameSelect = document.getElementById('item_name');
            
            // Clear previous options
            itemNameSelect.innerHTML = '<option value="">-- Chọn --</option>';
            
            if (!itemType) return;
            
            // Add new options based on item type
            const items = itemTypes[itemType] || [];
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item;
                option.textContent = item;
                itemNameSelect.appendChild(option);
            });
            
            // Clear serial list as well
            updateSerialList();
            
            // Add event listener to item_name
            itemNameSelect.addEventListener('change', function() {
                // Update serial list
                updateSerialList();
                
                // Update supplier based on selected item
                const selectedItem = itemNameSelect.value;
                const supplierId = itemSupplierMapping[selectedItem] || '';
                
                if (supplierId) {
                    document.getElementById('supplier').value = supplierId;
                }
            });
        }
        
        // Auto select serials based on quantity
        function updateSelectedSerialsFromQuantity() {
            const qty = parseInt(document.getElementById('select_quantity').value) || 0;
            const checkboxes = document.querySelectorAll('.serial-checkbox');
            
            if (checkboxes.length === 0 || qty <= 0) return;
            
            // Uncheck all first
            checkboxes.forEach(cb => cb.checked = false);
            
            // Then check the first qty checkboxes
            for (let i = 0; i < Math.min(qty, checkboxes.length); i++) {
                checkboxes[i].checked = true;
            }
            
            // Update count
            updateSelectedCount();
        }
        
        // Update serial list based on selected item
        function updateSerialList() {
            const itemName = document.getElementById('item_name').value;
            const serialList = document.getElementById('serial_list');
            const serialCount = document.getElementById('serial_count');
            
            // Reset quantity input
            document.getElementById('select_quantity').value = 1;
            
            // Clear previous serials
            serialList.innerHTML = '';
            
            if (!itemName) {
                serialList.innerHTML = '<p class="text-sm text-gray-500 col-span-full text-center py-4">Chọn vật tư/hàng hóa để hiển thị serial</p>';
                serialCount.textContent = 'Đã chọn: 0/0';
                return;
            }
            
            // Get serials for this item
            const serials = serialData[itemName] || [];
            
            if (serials.length === 0) {
                serialList.innerHTML = '<p class="text-sm text-gray-500 col-span-full text-center py-4">Không có serial khả dụng</p>';
                serialCount.textContent = 'Đã chọn: 0/0';
                return;
            }
            
            // Add checkboxes for each serial
            serials.forEach(serial => {
                const div = document.createElement('div');
                div.className = 'flex items-center space-x-2';
                div.innerHTML = `
                    <input type="checkbox" id="serial_${serial}" name="serials[]" value="${serial}" class="serial-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="serial_${serial}" class="text-sm text-gray-700">${serial}</label>
                `;
                serialList.appendChild(div);
            });
            
            // Add event listeners to checkboxes to update count
            const checkboxes = document.querySelectorAll('.serial-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Update selected count
                    updateSelectedCount();
                    
                    // Update quantity field to match selected count
                    const checkedCount = document.querySelectorAll('.serial-checkbox:checked').length;
                    document.getElementById('select_quantity').value = checkedCount || 1;
                });
            });
            
            // Select the first checkbox by default
            if (checkboxes.length > 0) {
                checkboxes[0].checked = true;
            }
            
            // Update count initially
            updateSelectedCount();
        }
        
        // Update selected count
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.serial-checkbox');
            const checkedCount = document.querySelectorAll('.serial-checkbox:checked').length;
            const totalCount = checkboxes.length;
            
            const serialCount = document.getElementById('serial_count');
            serialCount.textContent = `Đã chọn: ${checkedCount}/${totalCount}`;
            
            updateItemQuantity();
        }
        
        // Update item quantity based on selected serials
        function updateItemQuantity() {
            let quantity = 0;
            
            // Get selected mode
            const mode = document.querySelector('input[name="serial_mode"]:checked').value;
            
            if (mode === 'select') {
                // Count selected serials
                quantity = document.querySelectorAll('.serial-checkbox:checked').length;
            } else {
                // Count manual serials
                const serialText = document.getElementById('manual_serial').value.trim();
                quantity = serialText ? serialText.split('\n').filter(line => line.trim()).length : 0;
            }
            
            // Don't update if quantity is 0 to avoid overwriting user's input
            if (quantity > 0) {
                document.getElementById('item_quantity').value = quantity;
            }
        }

        // Add item to the list
        function addItemToList() {
            const itemType = document.getElementById('item_type').value;
            const itemName = document.getElementById('item_name').value;
            const supplier = document.getElementById('supplier');
            const supplierName = supplier.options[supplier.selectedIndex].text;
            
            // Get selected mode
            const mode = document.querySelector('input[name="serial_mode"]:checked').value;
            let serials = [];
            
            if (mode === 'select') {
                // Get selected serials
                serials = Array.from(document.querySelectorAll('.serial-checkbox:checked')).map(cb => cb.value);
            } else {
                // Get manual serials
                const serialText = document.getElementById('manual_serial').value.trim();
                if (serialText) {
                    serials = serialText.split('\n').filter(line => line.trim());
                }
            }
            
            // Set quantity based on serials
            const itemQuantity = serials.length || 1;
            
            if (!itemType || !itemName) {
                alert('Vui lòng chọn loại và tên vật tư/hàng hóa');
                return;
            }
            
            if (serials.length === 0) {
                alert('Vui lòng chọn hoặc nhập ít nhất một serial');
                return;
            }
            
            // Map item type to display text
            const itemTypeDisplay = {
                'material': 'Vật tư',
                'product': 'Hàng hóa',
                'finished_product': 'Thành phẩm'
            };
            
            const serialText = serials.length > 0 ? serials.join(', ') : 'Không có';
            
            // Add to array for form submission
            addedItems.push({
                type: itemType,
                name: itemName,
                quantity: itemQuantity,
                supplier: supplier.value,
                supplierName: supplierName !== '-- Chọn nhà cung cấp --' ? supplierName : 'Chưa xác định',
                serials: serials
            });
            
            // Update UI
            const addedItemsList = document.getElementById('added_items_list');
            
            // Clear "no items" message if this is the first item
            if (addedItemCount === 0) {
                addedItemsList.innerHTML = '';
            }
            
            addedItemCount++;
            
            // Create new row
            const row = document.createElement('tr');
            row.dataset.index = addedItemCount - 1;
            row.className = 'hover:bg-gray-50';
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${addedItemCount}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${itemTypeDisplay[itemType] || itemType}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${itemName}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${serialText.length > 50 ? serialText.substring(0, 50) + '...' : serialText}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${itemQuantity}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${supplier.value ? (supplierName !== '-- Chọn nhà cung cấp --' ? supplierName : '-') : '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                    <button type="button" onclick="removeItemFromList(${addedItemCount - 1})" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>
            `;
            
            addedItemsList.appendChild(row);
            
            // Update count
            document.getElementById('items_count').textContent = `Tổng số: ${addedItemCount}`;
            
            // Reset form
            document.getElementById('item_type').selectedIndex = 0;
            document.getElementById('item_name').innerHTML = '<option value="">-- Chọn --</option>';
            document.getElementById('supplier').selectedIndex = 0;
            document.getElementById('serial_list').innerHTML = '<p class="text-sm text-gray-500 col-span-full text-center py-4">Chọn vật tư/hàng hóa để hiển thị serial</p>';
            document.getElementById('serial_count').textContent = 'Đã chọn: 0/0';
            document.getElementById('manual_serial').value = '';
            document.getElementById('manual_serial_count').textContent = '0 serial';
        }
        
        // Remove item from list
        function removeItemFromList(index) {
            if (index >= 0 && index < addedItems.length) {
                // Remove from array
                addedItems.splice(index, 1);
                addedItemCount--;
                
                // Update UI
                const addedItemsList = document.getElementById('added_items_list');
                
                if (addedItemCount === 0) {
                    addedItemsList.innerHTML = `
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500">Chưa có vật tư/hàng hóa nào được thêm</td>
                        </tr>
                    `;
                } else {
                    // Re-render the entire list
                    addedItemsList.innerHTML = '';
                    addedItems.forEach((item, idx) => {
                        const itemTypeDisplay = {
                            'material': 'Vật tư',
                            'product': 'Hàng hóa',
                            'finished_product': 'Thành phẩm'
                        };
                        
                        const serialText = item.serials.length > 0 ? item.serials.join(', ') : 'Không có';
                        
                        const row = document.createElement('tr');
                        row.dataset.index = idx;
                        row.className = 'hover:bg-gray-50';
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${idx + 1}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${itemTypeDisplay[item.type] || item.type}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.name}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">${serialText}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${item.quantity}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${item.supplier ? (item.supplierName !== '-') ? item.supplierName : '-' : '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                <button type="button" onclick="removeItemFromList(${idx})" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </td>
                        `;
                        
                        addedItemsList.appendChild(row);
                    });
                }
                
                // Update count
                document.getElementById('items_count').textContent = `Tổng số: ${addedItemCount}`;
            }
        }
        
        // Add new test item
        function addTestItem() {
            const container = document.getElementById('test_items_container');
            const newItem = document.createElement('div');
            newItem.className = 'test-item flex items-center gap-4';
            newItem.innerHTML = `
                <input type="text" name="test_items[]" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập hạng mục kiểm thử">
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
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add a few default test items
            addTestItem();
            addTestItem();
        });
    </script>
</body>
</html> 