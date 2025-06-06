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
 <!-- #region 
  -->                                    <option value="material">Kiểm thử Vật tư/Hàng hóa</option>
                                    <option value="finished_product">Kiểm thử Thiết bị thành phẩm</option>
                                </select>
                            </div>

                            <div>
                                <label for="material_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại Vật tư hoặc hàng hóa</label>
                                <select id="material_type" name="material_type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="getSerialData()">
                                    <option value="">-- Chọn loại vật tư/hàng hóa --</option>
                                    @foreach(['Module 4G', 'Module Công suất', 'Module IoTs', 'Android', 'SmartBox'] as $material)
                                        <option value="{{ $material }}">{{ $material }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-1 required">Serial/Mã thiết bị</label>
                                <input type="text" id="serial_number" name="serial_number" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Mã thiết bị/serial sẽ hiển thị tự động" readonly required>
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng Vật tư/Hàng hóa</label>
                                <input type="number" id="quantity" name="quantity" min="1" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="1" required>
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
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kiểm thử</label>
                                <input type="date" id="test_date" name="test_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin chi tiết kiểm thử -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mt-6">Thông tin chi tiết kiểm thử</h2>
                        
                        <!-- Thông tin kiểm thử vật tư/hàng hóa -->
                        <div id="material_fields" class="mt-4 hidden">
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
                        </div>

                        <!-- Thông tin kiểm thử thiết bị thành phẩm -->
                        <div id="finished_product_fields" class="mt-4 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="installation_request" class="block text-sm font-medium text-gray-700 mb-1">Phiếu yêu cầu lắp đặt</label>
                                    <input type="text" id="installation_request" name="installation_request" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã phiếu lắp đặt" readonly>
                                </div>
                                
                                <div>
                                    <label for="assembly_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày lắp ráp</label>
                                    <input type="date" id="assembly_date" name="assembly_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
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
        
        // Get serial data based on material type
        function getSerialData() {
            const materialType = document.getElementById('material_type').value;
            if (!materialType) {
                document.getElementById('serial_number').value = '';
                return;
            }
            
            // Normally this would be an API call, but for now simulate with a generated code
            const today = new Date();
            const dateCode = today.getFullYear().toString().slice(-2) + 
                             (today.getMonth() + 1).toString().padStart(2, '0') + 
                             today.getDate().toString().padStart(2, '0');
            const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            
            // Generate code based on material type (simplified for demo)
            const prefix = materialType.split(' ')[0].substring(0, 3).toUpperCase();
            document.getElementById('serial_number').value = `${prefix}-${dateCode}-${randomNum}`;
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