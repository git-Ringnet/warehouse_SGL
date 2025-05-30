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
                                    <option value="new_component">Kiểm thử linh kiện đầu vào</option>
                                    <option value="defective">Kiểm thử module bị lỗi</option>
                                    <option value="new_device">Kiểm thử thiết bị mới lắp ráp</option>
                                </select>
                            </div>

                            <div>
                                <label for="device_category" class="block text-sm font-medium text-gray-700 mb-1 required">Loại thiết bị/module</label>
                                <select id="device_category" name="device_category" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn loại thiết bị/module --</option>
                                    <option value="android">Android</option>
                                    <option value="module_4g">Module 4G</option>
                                    <option value="module_power">Module Công suất</option>
                                    <option value="module_iot">Module IoTs</option>
                                    <option value="smartbox">SGL SmartBox</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-1 required">Serial/Mã thiết bị</label>
                                <input type="text" id="serial_number" name="serial_number" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã thiết bị/serial" required>
                            </div>

                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kiểm thử</label>
                                <input type="date" id="test_date" name="test_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
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

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái</label>
                                <select id="status" name="status" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="pending">Chờ kiểm thử</option>
                                    <option value="testing">Đang kiểm thử</option>
                                    <option value="completed">Hoàn thành</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin chi tiết kiểm thử -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mt-6">Thông tin chi tiết kiểm thử</h2>
                        
                        <!-- Thông tin kiểm thử linh kiện đầu vào -->
                        <div id="new_component_fields" class="mt-4 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                                    <input type="text" id="supplier" name="supplier" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập tên nhà cung cấp">
                                </div>
                                
                                <div>
                                    <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">Mã lô</label>
                                    <input type="text" id="batch_number" name="batch_number" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã lô">
                                </div>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="manufacture_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày sản xuất</label>
                                    <input type="date" id="manufacture_date" name="manufacture_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                </div>
                                
                                <div>
                                    <label for="arrival_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày nhập kho</label>
                                    <input type="date" id="arrival_date" name="arrival_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin kiểm thử module lỗi -->
                        <div id="defective_fields" class="mt-4 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="defect_source" class="block text-sm font-medium text-gray-700 mb-1">Nguồn gốc lỗi</label>
                                    <select id="defect_source" name="defect_source" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                        <option value="">-- Chọn nguồn gốc lỗi --</option>
                                        <option value="customer_site">Site khách hàng</option>
                                        <option value="internal_test">Phát hiện nội bộ</option>
                                        <option value="production">Lỗi sản xuất</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="received_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày nhận module lỗi</label>
                                    <input type="date" id="received_date" name="received_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label for="defect_description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả lỗi</label>
                                <textarea id="defect_description" name="defect_description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mô tả chi tiết về lỗi"></textarea>
                            </div>
                        </div>

                        <!-- Thông tin kiểm thử thiết bị mới -->
                        <div id="new_device_fields" class="mt-4 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="assembly_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày lắp ráp</label>
                                    <input type="date" id="assembly_date" name="assembly_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                </div>
                                
                                <div>
                                    <label for="software_version" class="block text-sm font-medium text-gray-700 mb-1">Phiên bản phần mềm</label>
                                    <input type="text" id="software_version" name="software_version" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="VD: 1.2.5">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label for="device_config" class="block text-sm font-medium text-gray-700 mb-1">Cấu hình thiết bị</label>
                                <textarea id="device_config" name="device_config" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập thông tin cấu hình thiết bị"></textarea>
                            </div>
                        </div>

                        <!-- Hạng mục kiểm thử chung -->
                        <div class="mt-6">
                            <h3 class="text-md font-medium text-gray-800 mb-3">Hạng mục kiểm thử</h3>
                            
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="space-y-3">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="test_item_1" name="test_items[]" value="hardware_inspection" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="test_item_1" class="ml-2 block text-sm text-gray-700">Kiểm tra phần cứng</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="test_item_2" name="test_items[]" value="software_test" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="test_item_2" class="ml-2 block text-sm text-gray-700">Kiểm tra phần mềm</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="test_item_3" name="test_items[]" value="communication_test" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="test_item_3" class="ml-2 block text-sm text-gray-700">Kiểm tra kết nối/truyền thông</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="test_item_4" name="test_items[]" value="functionality_test" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="test_item_4" class="ml-2 block text-sm text-gray-700">Kiểm tra chức năng</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="test_item_5" name="test_items[]" value="stress_test" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="test_item_5" class="ml-2 block text-sm text-gray-700">Kiểm tra độ bền (stress test)</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="test_item_6" name="test_items[]" value="compatibility_test" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="test_item_6" class="ml-2 block text-sm text-gray-700">Kiểm tra khả năng tương thích</label>
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
        // Toggle fields based on test type
        function toggleTestTypeFields() {
            const testType = document.getElementById('test_type').value;
            
            // Hide all specific fields first
            document.getElementById('new_component_fields').classList.add('hidden');
            document.getElementById('defective_fields').classList.add('hidden');
            document.getElementById('new_device_fields').classList.add('hidden');
            
            // Show fields based on test type
            if(testType === 'new_component') {
                document.getElementById('new_component_fields').classList.remove('hidden');
            } else if(testType === 'defective') {
                document.getElementById('defective_fields').classList.remove('hidden');
            } else if(testType === 'new_device') {
                document.getElementById('new_device_fields').classList.remove('hidden');
            }
        }
        
        // Hàm toggleDropdown cho sidebar
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            
            // Close all other dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== id) {
                    d.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html> 