<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu kiểm thử - SGL</title>
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
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu kiểm thử</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    QA-24060001
                </div>
                <div class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Module 4G
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url('/testing/1') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ url('/testing/1') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Thông tin cơ bản -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2">Thông tin cơ bản</h2>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="test_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại kiểm thử</label>
                                <select id="test_type" name="test_type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="toggleTestTypeFields()">
                                    <option value="material" selected>Kiểm thử Vật tư/Hàng hóa</option>
                                    <option value="finished_product">Kiểm thử Thiết bị thành phẩm</option>
                                </select>
                            </div>

                            <div>
                                <label for="material_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại Vật tư hoặc hàng hóa</label>
                                <select id="material_type" name="material_type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="module_4g" selected>Module 4G</option>
                                    <option value="module_power">Module Công suất</option>
                                    <option value="module_iot">Module IoTs</option>
                                    <option value="android">Android</option>
                                    <option value="smartbox">SGL SmartBox</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-1 required">Serial/Mã thiết bị</label>
                                <input type="text" id="serial_number" name="serial_number" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="4G-MOD-2305621" required readonly>
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng Vật tư/Hàng hóa</label>
                                <input type="number" id="quantity" name="quantity" min="1" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="20" required>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="test_engineer" class="block text-sm font-medium text-gray-700 mb-1 required">Người kiểm thử</label>
                                <select id="test_engineer" name="test_engineer" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="1" selected>Nguyễn Văn A</option>
                                    <option value="2">Trần Văn B</option>
                                    <option value="3">Lê Thị C</option>
                                    <option value="4">Phạm Văn D</option>
                                    <option value="5">Lê Văn E</option>
                                </select>
                            </div>

                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kiểm thử</label>
                                <input type="date" id="test_date" name="test_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="2024-06-15" required>
                            </div>
                        </div>

                        <!-- Thông tin kiểm thử Vật tư/Hàng hóa đầu vào -->
                        <div id="material_fields" class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                                    <select id="supplier" name="supplier" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                        <option value="">-- Chọn nhà cung cấp --</option>
                                        <option value="1" selected>ABC Electronics</option>
                                        <option value="2">Tech Solutions</option>
                                        <option value="3">VN Components</option>
                                        <option value="4">Global Tech</option>
                                        <option value="5">Mega Components</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">Mã lô</label>
                                    <input type="text" id="batch_number" name="batch_number" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="LOT-2405-01">
                                </div>
                            </div>
                            
                            <!-- Serial Management -->
                            <div class="mt-4">
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
                                        <div class="flex items-center space-x-2">
                                            <input type="checkbox" id="serial_4G-MOD-2305621" name="serials[]" value="4G-MOD-2305621" class="serial-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                            <label for="serial_4G-MOD-2305621" class="text-sm text-gray-700">4G-MOD-2305621</label>
                                        </div>
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
                                        <textarea id="manual_serial" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm" placeholder="Nhập danh sách serial, mỗi serial một dòng" oninput="updateManualSerialCount()">4G-MOD-2305621</textarea>
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
                        </div>

                        <!-- Thông tin kiểm thử Thiết bị thành phẩm -->
                        <div id="finished_product_fields" class="mt-4 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="installation_request" class="block text-sm font-medium text-gray-700 mb-1">Phiếu yêu cầu lắp đặt</label>
                                    <input type="text" id="installation_request" name="installation_request" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="INST-240601" readonly>
                                </div>
                                
                                <div>
                                    <label for="assembly_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày lắp ráp</label>
                                    <input type="date" id="assembly_date" name="assembly_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="2024-06-10">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hạng mục kiểm thử và kết quả -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mt-6">Hạng mục kiểm thử và kết quả</h2>
                            <button type="button" onclick="addTestItem()" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm flex items-center">
                                <i class="fas fa-plus mr-1"></i> Thêm hạng mục
                            </button>
                        </div>
                        
                        <div class="mt-4">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hạng mục</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kết quả</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="test_items_table" class="bg-white divide-y divide-gray-100">
                                    <tr class="test-item-row">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <input type="text" name="test_item_names[]" value="Kiểm tra phần cứng" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <select name="test_results[]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pass" selected>Đạt</option>
                                                <option value="fail">Không đạt</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <input type="text" name="test_notes[]" value="Kiểm tra chân cắm và các kết nối đầy đủ" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button type="button" onclick="removeTestItem(this)" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="test-item-row">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <input type="text" name="test_item_names[]" value="Kiểm tra phần mềm" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <select name="test_results[]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pass" selected>Đạt</option>
                                                <option value="fail">Không đạt</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <input type="text" name="test_notes[]" value="Firmware v3.2.1 hoạt động ổn định" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button type="button" onclick="removeTestItem(this)" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Thông tin kết quả kiểm thử -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mt-6">Thông tin kết quả kiểm thử</h2>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="pass_quantity" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng Vật tư/Hàng hóa Đạt</label>
                                <input type="number" id="pass_quantity" name="pass_quantity" min="0" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="18" required>
                            </div>

                            <div>
                                <label for="fail_quantity" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng Vật tư/Hàng hóa Không đạt</label>
                                <input type="number" id="fail_quantity" name="fail_quantity" min="0" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="2" required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="fail_reasons" class="block text-sm font-medium text-gray-700 mb-1">Lý do không đạt</label>
                            <textarea id="fail_reasons" name="fail_reasons" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập lý do không đạt nếu có">2 module có vấn đề về kết nối anten, cần kiểm tra lại mạch RF.</textarea>
                        </div>

                        <div class="mt-4">
                            <label for="test_conclusion" class="block text-sm font-medium text-gray-700 mb-1 required">Kết luận</label>
                            <textarea id="test_conclusion" name="test_conclusion" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>Module 4G đạt chất lượng để đưa vào sản xuất. Đa số thông số kỹ thuật đều đạt yêu cầu. Cần loại bỏ 2 module bị lỗi anten.</textarea>
                        </div>
                    </div>

                    <!-- Submit buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ url('/testing') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="button" onclick="printTest()" class="h-10 bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-print mr-2"></i> In phiếu
                        </button>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
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

        // Toggle fields based on test type
        function toggleTestTypeFields() {
            const testType = document.getElementById('test_type').value;
            
            // Hide all specific fields first
            document.getElementById('material_fields').classList.add('hidden');
            document.getElementById('finished_product_fields').classList.add('hidden');
            
            // Show fields based on test type
            if(testType === 'material') {
                document.getElementById('material_fields').classList.remove('hidden');
            } else if(testType === 'finished_product') {
                document.getElementById('finished_product_fields').classList.remove('hidden');
            }
        }
        
        // Toggle between serial selection modes
        function toggleSerialMode(mode) {
            if (mode === 'select') {
                document.getElementById('select_serial_container').classList.remove('hidden');
                document.getElementById('manual_serial_container').classList.add('hidden');
                updateSelectedCount();
            } else {
                document.getElementById('select_serial_container').classList.add('hidden');
                document.getElementById('manual_serial_container').classList.remove('hidden');
                updateManualSerialCount();
            }
            updateQuantityFields();
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
            updateQuantityFields();
        }
        
        // Update selected count
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.serial-checkbox');
            const checkedCount = document.querySelectorAll('.serial-checkbox:checked').length;
            const totalCount = checkboxes.length;
            
            const serialCount = document.getElementById('serial_count');
            if (serialCount) {
                serialCount.textContent = `Đã chọn: ${checkedCount}/${totalCount}`;
            }
            
            updateQuantityFields();
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
        
        // Update quantity fields based on serial selections
        function updateQuantityFields() {
            let quantity = 0;
            
            // Determine which mode is active
            const serialMode = document.querySelector('input[name="serial_mode"]:checked');
            if (!serialMode) return;
            
            if (serialMode.value === 'select') {
                // Count selected serials
                quantity = document.querySelectorAll('.serial-checkbox:checked').length;
            } else {
                // Count manual serials
                const serialText = document.getElementById('manual_serial').value.trim();
                quantity = serialText ? serialText.split('\n').filter(line => line.trim()).length : 0;
            }
            
            // Update the main quantity field
            if (quantity > 0) {
                document.getElementById('quantity').value = quantity;
            }
        }

        // Add event listeners to update fields when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Update material type to supplier mapping
            document.getElementById('material_type').addEventListener('change', function() {
                const materialTypeSelect = document.getElementById('material_type');
                const materialTypeOption = materialTypeSelect.options[materialTypeSelect.selectedIndex].text;
                const supplierId = itemSupplierMapping[materialTypeOption] || '';
                
                if (supplierId) {
                    document.getElementById('supplier').value = supplierId;
                }
            });
            
            // Initialize serial management
            initSerialManagement();
            
            // Add event listeners to quantity fields
            document.getElementById('quantity').addEventListener('change', function() {
                const total = parseInt(this.value) || 0;
                const passQty = document.getElementById('pass_quantity');
                const failQty = document.getElementById('fail_quantity');
                
                // If pass and fail quantities exist, adjust them to match the total
                if (passQty && failQty) {
                    const currentPass = parseInt(passQty.value) || 0;
                    const currentFail = parseInt(failQty.value) || 0;
                    const currentTotal = currentPass + currentFail;
                    
                    if (currentTotal > 0 && currentTotal !== total) {
                        const ratio = currentPass / currentTotal;
                        passQty.value = Math.round(total * ratio);
                        failQty.value = total - passQty.value;
                    }
                }
            });
            
            // Initialize the test type fields
            toggleTestTypeFields();
        });
        
        // Initialize serial management based on existing data
        function initSerialManagement() {
            // Set up event listeners for serial checkboxes
            const checkboxes = document.querySelectorAll('.serial-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectedCount();
                    
                    // Update quantity field to match selected count
                    const selectQty = document.getElementById('select_quantity');
                    if (selectQty) {
                        const checkedCount = document.querySelectorAll('.serial-checkbox:checked').length;
                        selectQty.value = checkedCount || 1;
                    }
                });
            });
            
            // Initialize serial counts
            updateSelectedCount();
            updateManualSerialCount();
        }
        
        // Add new test item row
        function addTestItem() {
            const tbody = document.getElementById('test_items_table');
            const newRow = document.createElement('tr');
            newRow.className = 'test-item-row';
            newRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    <input type="text" name="test_item_names[]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <select name="test_results[]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="pass">Đạt</option>
                        <option value="fail">Không đạt</option>
                    </select>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <input type="text" name="test_notes[]" class="w-full border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <button type="button" onclick="removeTestItem(this)" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(newRow);
        }
        
        // Remove test item row
        function removeTestItem(button) {
            const tbody = document.getElementById('test_items_table');
            const row = button.closest('tr');
            
            // Don't remove if it's the last row
            if (tbody.rows.length > 1) {
                tbody.removeChild(row);
            } else {
                alert('Phải giữ lại ít nhất một hạng mục kiểm thử');
            }
        }

        // Validate quantities
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const passQty = parseInt(document.getElementById('pass_quantity').value || 0);
            const failQty = parseInt(document.getElementById('fail_quantity').value || 0);
            const totalQty = parseInt(document.getElementById('quantity').value || 0);
            
            if (passQty + failQty !== totalQty) {
                e.preventDefault();
                alert('Tổng số lượng vật tư Đạt và Không đạt phải bằng Số lượng Vật tư.');
            }
        });
        
        // Print test form
        function printTest() {
            window.print();
        }
    </script>
</body>
</html> 
