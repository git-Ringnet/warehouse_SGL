<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu bảo trì dự án - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu bảo trì dự án</h1>
                <div class="ml-4 px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                    Mẫu REQ-MAINT
                </div>
                <div class="ml-2 px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">
                    ID: {{ $id }}
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ url('/requests/maintenance/'.$id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-eye mr-2"></i> Xem phiếu
                </a>
                <a href="{{ url('/requests') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-times mr-2"></i> Hủy
                </a>
            </div>
        </header>
        
        <main class="p-6">
            <form action="{{ url('/requests/maintenance/'.$id) }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
                @csrf
                @method('PATCH')
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin đề xuất</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày đề xuất</label>
                            <input type="date" name="request_date" id="request_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="2024-06-20">
                        </div>
                        <div>
                            <label for="technician" class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật đề xuất</label>
                            <input type="text" name="technician" id="technician" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Trần Minh Trí">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án bảo trì</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên dự án</label>
                            <input type="text" name="project_name" id="project_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Hệ thống giám sát Tân Thành">
                        </div>
                        <div>
                            <label for="partner" class="block text-sm font-medium text-gray-700 mb-1 required">Đối tác</label>
                            <input type="text" name="partner" id="partner" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Viễn Thông A">
                        </div>
                        <div class="md:col-span-2">
                            <label for="project_address" class="block text-sm font-medium text-gray-700 mb-1 required">Địa chỉ dự án</label>
                            <input type="text" name="project_address" id="project_address" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="456 Đường Lê Hồng Phong, Phường Tân Thành, Quận Tân Phú, TP.HCM">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin bảo trì</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="maintenance_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày bảo trì dự kiến</label>
                            <input type="date" name="maintenance_date" id="maintenance_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="2024-07-05">
                        </div>
                        <div>
                            <label for="maintenance_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại bảo trì</label>
                            <select name="maintenance_type" id="maintenance_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Chọn loại bảo trì --</option>
                                <option value="regular" selected>Định kỳ</option>
                                <option value="emergency">Khẩn cấp</option>
                                <option value="preventive">Phòng ngừa</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="maintenance_reason" class="block text-sm font-medium text-gray-700 mb-1 required">Lý do bảo trì</label>
                            <textarea name="maintenance_reason" id="maintenance_reason" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">Bảo trì định kỳ 6 tháng theo hợp đồng. Kiểm tra các thiết bị camera, hệ thống lưu trữ và nâng cấp phần mềm quản lý.</textarea>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-lg font-semibold text-gray-800">Vật tư cần thiết</h2>
                        <button type="button" id="add_material" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="fas fa-plus-circle mr-1"></i> Thêm vật tư
                        </button>
                    </div>
                    
                    <div id="material_container">
                        <div class="material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="material_name_0" class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư</label>
                                <input type="text" name="material[0][name]" id="material_name_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Ổ cứng lưu trữ 4TB">
                            </div>
                            <div class="md:col-span-1">
                                <label for="material_quantity_0" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" name="material[0][quantity]" id="material_quantity_0" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="2">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </div>
                        </div>
                        <div class="material-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="material_name_1" class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư</label>
                                <input type="text" name="material[1][name]" id="material_name_1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Bộ nguồn dự phòng">
                            </div>
                            <div class="md:col-span-1">
                                <label for="material_quantity_1" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" name="material[1][quantity]" id="material_quantity_1" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="1">
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
                            <input type="text" name="customer_name" id="customer_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Lê Quang Hưng">
                        </div>
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1 required">Số điện thoại</label>
                            <input type="text" name="customer_phone" id="customer_phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="0987654321">
                        </div>
                        <div>
                            <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="customer_email" id="customer_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="hung.le@vienthonga.com">
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Nhân sự thực hiện</h2>
                    <div id="staff_container">
                        <div class="staff-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="staff_name_0" class="block text-sm font-medium text-gray-700 mb-1 required">Tên nhân viên</label>
                                <input type="text" name="staff[0][name]" id="staff_name_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Nguyễn Văn An">
                            </div>
                            <div class="md:col-span-1 flex items-end">
                                <button type="button" id="add_staff" class="h-10 px-4 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors flex items-center justify-center">
                                    <i class="fas fa-plus mr-2"></i> Thêm
                                </button>
                            </div>
                        </div>
                        <div class="staff-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
                            <div class="md:col-span-3">
                                <label for="staff_name_1" class="block text-sm font-medium text-gray-700 mb-1 required">Tên nhân viên</label>
                                <input type="text" name="staff[1][name]" id="staff_name_1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Phạm Thị Bình">
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
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Trạng thái phiếu</h2>
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <input type="radio" name="status" id="status_pending" value="pending" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="status_pending" class="ml-2 block text-sm font-medium text-gray-700">Chờ xử lý</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="status" id="status_processing" value="processing" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" checked>
                            <label for="status_processing" class="ml-2 block text-sm font-medium text-gray-700">Đang xử lý</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="status" id="status_completed" value="completed" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="status_completed" class="ml-2 block text-sm font-medium text-gray-700">Hoàn thành</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" name="status" id="status_cancelled" value="cancelled" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <label for="status_cancelled" class="ml-2 block text-sm font-medium text-gray-700">Hủy bỏ</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                    <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">Bảo trì định kỳ đã được thông báo cho khách hàng trước 2 tuần. Cần chuẩn bị đầy đủ vật tư và liên hệ xác nhận lịch trước khi thực hiện.</textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="copyForm()" class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 flex items-center">
                        <i class="fas fa-copy mr-2"></i> Sao chép phiếu
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                        <i class="fas fa-save mr-2"></i> Cập nhật phiếu
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Thêm vật tư
        let materialCount = 2; // Bắt đầu từ 2 vì đã có 2 mẫu có sẵn
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
        
        // Thêm nhân viên
        let staffCount = 2; // Bắt đầu từ 2 vì đã có 2 mẫu có sẵn
        document.getElementById('add_staff').addEventListener('click', function() {
            const container = document.getElementById('staff_container');
            const newRow = document.createElement('div');
            newRow.className = 'staff-row grid grid-cols-1 md:grid-cols-4 gap-4 mb-3';
            newRow.innerHTML = `
                <div class="md:col-span-3">
                    <label for="staff_name_${staffCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Tên nhân viên</label>
                    <input type="text" name="staff[${staffCount}][name]" id="staff_name_${staffCount}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="remove-row h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            staffCount++;
            
            addRemoveRowEventListeners();
        });
        
        // Xóa dòng
        function addRemoveRowEventListeners() {
            document.querySelectorAll('.remove-row').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.material-row, .staff-row').remove();
                });
            });
        }
        
        // Sao chép phiếu
        function copyForm() {
            alert('Đã sao chép phiếu này thành một phiếu mới!');
            // Thực tế sẽ lưu dữ liệu form hiện tại và chuyển hướng đến trang tạo phiếu mới với dữ liệu đã được điền sẵn
        }

        // Khởi tạo các event listeners khi trang tải xong
        document.addEventListener('DOMContentLoaded', function() {
            addRemoveRowEventListeners();
        });
    </script>
</body>
</html> 