<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo dự án mới - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Tạo dự án mới</h1>
            <a href="{{ url('/projects') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>
        
        <main class="p-6">
            <form action="{{ url('/projects') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Mã dự án -->
                    <div>
                        <label for="project_code" class="block text-sm font-medium text-gray-700 mb-1">Mã dự án</label>
                        <input type="text" name="project_code" id="project_code" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="PRJ-{{ date('ymd') }}{{ rand(100, 999) }}" readonly>
                    </div>

                    <!-- Tên dự án -->
                    <div>
                        <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên dự án</label>
                        <input type="text" name="project_name" id="project_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Khách hàng -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1 required">Khách hàng</label>
                        <select name="customer_id" id="customer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Chọn khách hàng</option>
                            <option value="1">Công ty ABC</option>
                            <option value="2">Công ty XYZ</option>
                            <option value="3">Công ty DEF</option>
                        </select>
                    </div>

                    <!-- Loại dự án -->
                    <div>
                        <label for="project_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại dự án</label>
                        <select name="project_type" id="project_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Chọn loại dự án</option>
                            <option value="installation">Lắp đặt mới</option>
                            <option value="upgrade">Nâng cấp</option>
                            <option value="maintenance">Bảo trì</option>
                            <option value="warranty">Bảo hành</option>
                        </select>
                    </div>

                    <!-- Ngày bắt đầu -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày bắt đầu</label>
                        <input type="date" name="start_date" id="start_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Ngày kết thúc dự kiến -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kết thúc dự kiến</label>
                        <input type="date" name="end_date" id="end_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Số lượng thiết bị -->
                    <div>
                        <label for="device_count" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng thiết bị</label>
                        <input type="number" name="device_count" id="device_count" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Thời gian bảo hành (tháng) -->
                    <div>
                        <label for="warranty_period" class="block text-sm font-medium text-gray-700 mb-1 required">Thời gian bảo hành (tháng)</label>
                        <input type="number" name="warranty_period" id="warranty_period" required min="1" max="36" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Trạng thái -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái</label>
                        <select name="status" id="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pending" selected>Chờ duyệt</option>
                            <option value="active">Đang thực hiện</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>

                    <!-- Người liên hệ -->
                    <div>
                        <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">Người liên hệ</label>
                        <input type="text" name="contact_name" id="contact_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Số điện thoại liên hệ -->
                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại liên hệ</label>
                        <input type="text" name="contact_phone" id="contact_phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Email liên hệ -->
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Email liên hệ</label>
                        <input type="email" name="contact_email" id="contact_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Địa chỉ -->
                    <div>
                        <label for="contact_address" class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                        <input type="text" name="contact_address" id="contact_address" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Mô tả -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea name="description" id="description" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <!-- Ghi chú -->
                    <!-- <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div> -->
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ url('/projects') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Hủy
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-save mr-2"></i> Tạo dự án
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Set default dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const nextMonth = new Date();
            nextMonth.setMonth(today.getMonth() + 1);

            document.getElementById('start_date').value = today.toISOString().split('T')[0];
            document.getElementById('end_date').value = nextMonth.toISOString().split('T')[0];
        });

        // Dropdown Menus
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