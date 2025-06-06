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
                                    <input type="text" id="supplier" name="supplier" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="Công ty ABC Electronics">
                                </div>
                                
                                <div>
                                    <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">Mã lô</label>
                                    <input type="text" id="batch_number" name="batch_number" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="LOT-2405-01">
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
</body>
</html> 