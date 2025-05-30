<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu đề xuất triển khai dự án - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .sidebar {
            background: linear-gradient(180deg, #1a365d 0%, #0f2942 100%);
            transition: all 0.3s ease;
        }
        .content-area {
            margin-left: 256px;
            min-height: 100vh;
            background: #f8fafc;
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
                width: 70px;
            }
            .content-area {
                margin-left: 0 !important;
            }
        }
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu đề xuất triển khai dự án</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    PRJ-00{{ $id }}
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ url('/requests/project/'.$id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-times mr-2"></i> Hủy
                </a>
            </div>
        </header>
        
        <main class="p-6">
            <form action="{{ url('/requests/project/'.$id) }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
                @csrf
                @method('PUT')
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin đề xuất</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày đề xuất</label>
                            <input type="date" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="2024-05-28">
                        </div>
                        <div>
                            <label for="technician" class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật đề xuất</label>
                            <input type="text" name="technician" id="technician" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Duy Đức">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên dự án</label>
                            <input type="text" name="project_name" id="project_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Đất Đỏ">
                        </div>
                        <div>
                            <label for="partner" class="block text-sm font-medium text-gray-700 mb-1 required">Đối tác</label>
                            <input type="text" name="partner" id="partner" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="VNPT Vũng Tàu">
                        </div>
                        <div class="md:col-span-2">
                            <label for="project_address" class="block text-sm font-medium text-gray-700 mb-1 required">Địa chỉ dự án</label>
                            <input type="text" name="project_address" id="project_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Thị trấn Đất Đỏ">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold text-gray-800">Thiết bị đề xuất</h2>
                        <button type="button" id="add_equipment" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-plus-circle mr-1"></i> Thêm thiết bị
                        </button>
                    </div>
                    
                    <div id="equipment_container">
                        <!-- Thiết bị 1 -->
                        <div class="equipment-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="equipment_name_0" class="block text-sm font-medium text-gray-700 mb-1 required">Tên thiết bị</label>
                                <input type="text" name="equipment[0][name]" id="equipment_name_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Cụm thu phát thanh">
                            </div>
                            <div class="md:col-span-1">
                                <label for="equipment_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" name="equipment[0][quantity]" id="equipment_quantity_0" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="3">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Thiết bị 2 -->
                        <div class="equipment-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="equipment_name_1" class="block text-sm font-medium text-gray-700 mb-1 required">Tên thiết bị</label>
                                <input type="text" name="equipment[1][name]" id="equipment_name_1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Bộ điều khiển trung tâm">
                            </div>
                            <div class="md:col-span-1">
                                <label for="equipment_quantity_1" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" name="equipment[1][quantity]" id="equipment_quantity_1" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="1">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Thiết bị 3 -->
                        <div class="equipment-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="equipment_name_2" class="block text-sm font-medium text-gray-700 mb-1 required">Tên thiết bị</label>
                                <input type="text" name="equipment[2][name]" id="equipment_name_2" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Bộ thu không dây">
                            </div>
                            <div class="md:col-span-1">
                                <label for="equipment_quantity_2" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" name="equipment[2][quantity]" id="equipment_quantity_2" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="2">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold text-gray-800">Vật tư đề xuất</h2>
                        <button type="button" id="add_material" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-plus-circle mr-1"></i> Thêm vật tư
                        </button>
                    </div>
                    
                    <div id="material_container">
                        <!-- Vật tư 1 -->
                        <div class="material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="material_name_0" class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư</label>
                                <input type="text" name="material[0][name]" id="material_name_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Dây điện 1.5mm">
                            </div>
                            <div class="md:col-span-1">
                                <label for="material_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" name="material[0][quantity]" id="material_quantity_0" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="100">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Vật tư 2 -->
                        <div class="material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="material_name_1" class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư</label>
                                <input type="text" name="material[1][name]" id="material_name_1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Dây tín hiệu 2 lõi">
                            </div>
                            <div class="md:col-span-1">
                                <label for="material_quantity_1" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" name="material[1][quantity]" id="material_quantity_1" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="50">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Vật tư 3 -->
                        <div class="material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="material_name_2" class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư</label>
                                <input type="text" name="material[2][name]" id="material_name_2" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Ống nhựa xoắn">
                            </div>
                            <div class="md:col-span-1">
                                <label for="material_quantity_2" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" name="material[2][quantity]" id="material_quantity_2" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="30">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin liên hệ khách hàng</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên khách hàng</label>
                            <input type="text" name="customer_name" id="customer_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Nguyễn Minh Tài">
                        </div>
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1 required">Số điện thoại</label>
                            <input type="text" name="customer_phone" id="customer_phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="982.133.564">
                        </div>
                        <div>
                            <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="customer_email" id="customer_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="minhta@gmail.com">
                        </div>
                        <div class="md:col-span-3">
                            <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1 required">Địa chỉ</label>
                            <input type="text" name="customer_address" id="customer_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="1 Đất Đỏ, Thị trấn Long Điền, huyện Long Đất, Tỉnh BRVT">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                    <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">Thi công trước ngày 15/06/2024. Yêu cầu kỹ thuật lắp đặt đúng kỹ thuật và đảm bảo an toàn.
Liên hệ với khách hàng trước khi tiến hành thi công ít nhất 2 ngày.</textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="{{ url('/requests/project/'.$id) }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 flex items-center">
                        <i class="fas fa-times mr-2"></i> Hủy
                    </a>
                    <a href="{{ url('/requests/project/'.$id.'/preview') }}" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 flex items-center" target="_blank">
                        <i class="fas fa-print mr-2"></i> Xem trước
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                        <i class="fas fa-save mr-2"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Biến đếm cho thiết bị và vật tư
        let equipmentCount = 3; // Đã có 3 thiết bị
        let materialCount = 3;  // Đã có 3 vật tư
        
        // Thêm thiết bị
        document.getElementById('add_equipment').addEventListener('click', function() {
            const container = document.getElementById('equipment_container');
            const newRow = document.createElement('div');
            newRow.className = 'equipment-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3';
            newRow.innerHTML = `
                <div class="md:col-span-3">
                    <label for="equipment_name_${equipmentCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Tên thiết bị</label>
                    <input type="text" name="equipment[${equipmentCount}][name]" id="equipment_name_${equipmentCount}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1">
                    <label for="equipment_quantity_${equipmentCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                    <input type="number" name="equipment[${equipmentCount}][quantity]" id="equipment_quantity_${equipmentCount}" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            equipmentCount++;
            
            addRemoveRowEventListeners();
        });
        
        // Thêm vật tư
        document.getElementById('add_material').addEventListener('click', function() {
            const container = document.getElementById('material_container');
            const newRow = document.createElement('div');
            newRow.className = 'material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3';
            newRow.innerHTML = `
                <div class="md:col-span-3">
                    <label for="material_name_${materialCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư</label>
                    <input type="text" name="material[${materialCount}][name]" id="material_name_${materialCount}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1">
                    <label for="material_quantity_${materialCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                    <input type="number" name="material[${materialCount}][quantity]" id="material_quantity_${materialCount}" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            materialCount++;
            
            addRemoveRowEventListeners();
        });
        
        // Xóa dòng
        function addRemoveRowEventListeners() {
            document.querySelectorAll('.remove-row').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.equipment-row, .material-row').remove();
                });
            });
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            addRemoveRowEventListeners();
        });
    </script>
</body>
</html> 