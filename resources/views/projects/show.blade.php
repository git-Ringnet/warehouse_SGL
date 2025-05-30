<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết dự án - SGL</title>
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
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background-color: white;
            border-radius: 0.5rem;
            max-width: 500px;
            width: 90%;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal {
            transform: scale(1);
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết dự án</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    PRJ-2406001
                </div>
                <div class="ml-2 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    Đang thực hiện
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url('/projects') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ url('/projects/1/edit') }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                
            </div>
        </header>

        <main class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Thông tin dự án -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Thông tin dự án</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                            <div>
                                <p class="text-sm text-gray-500">Tên dự án</p>
                                <p class="text-base text-gray-800 font-medium">Lắp đặt hệ thống giám sát</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Khách hàng</p>
                                <p class="text-base text-gray-800 font-medium">Công ty ABC</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Loại dự án</p>
                                <p class="text-base text-gray-800 font-medium">Lắp đặt mới</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày bắt đầu</p>
                                <p class="text-base text-gray-800 font-medium">01/06/2024</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày kết thúc dự kiến</p>
                                <p class="text-base text-gray-800 font-medium">30/06/2024</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Thời gian bảo hành</p>
                                <p class="text-base text-gray-800 font-medium">12 tháng</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày hết hạn bảo hành</p>
                                <p class="text-base text-gray-800 font-medium">30/06/2025</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tổng số thiết bị</p>
                                <p class="text-base text-gray-800 font-medium">20 thiết bị</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Mô tả</p>
                            <p class="text-base text-gray-800 mt-1">
                                Lắp đặt hệ thống giám sát camera an ninh và thiết bị IoT cho tòa nhà văn phòng tại khu vực trung tâm thành phố. Bao gồm 15 camera, 5 thiết bị trung tâm và phần mềm quản lý.
                            </p>
                        </div>
                        
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Ghi chú</p>
                            <p class="text-base text-gray-800 mt-1">
                                Yêu cầu hoàn thành đúng tiến độ, có đội kỹ thuật hỗ trợ 24/7 trong tuần đầu tiên sau khi bàn giao.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Theo dõi thiết bị bảo hành -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Theo dõi bảo hành thiết bị</h2>
                            <a href="{{ url('/projects/1/edit') }}" class="text-blue-500 hover:text-blue-600 text-sm font-medium">
                                <i class="fas fa-plus-circle mr-1"></i> Thêm thiết bị bảo hành
                            </a>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã thiết bị</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên thiết bị</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày bảo hành</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vấn đề</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">CAM-001</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Camera Dome 2MP</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">15/06/2024</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">Lỗi kết nối</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đã sửa</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 flex space-x-2">
                                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </button>
                                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">CAM-005</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Camera PTZ 5MP</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">18/06/2024</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">Không quay được</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Đang xử lý</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 flex space-x-2">
                                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </button>
                                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">NVR-002</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Đầu ghi hình NVR 8 kênh</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">20/06/2024</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">Lỗi ổ cứng</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đã sửa</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 flex space-x-2">
                                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </button>
                                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar thông tin -->
                <div class="lg:col-span-1">
                    <!-- Tóm tắt bảo hành -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Tóm tắt bảo hành</h2>
                        
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-500">Thiết bị bảo hành</span>
                                <span class="text-sm font-medium text-gray-800">15/20</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-blue-500 h-2.5 rounded-full" style="width: 75%"></div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mt-6">
                            <div class="p-3 bg-green-50 rounded-lg">
                                <p class="text-xs text-green-500 uppercase">Đã sửa</p>
                                <p class="text-xl font-bold text-green-600">12</p>
                            </div>
                            <div class="p-3 bg-yellow-50 rounded-lg">
                                <p class="text-xs text-yellow-500 uppercase">Đang xử lý</p>
                                <p class="text-xl font-bold text-yellow-600">3</p>
                            </div>
                            <div class="p-3 bg-red-50 rounded-lg">
                                <p class="text-xs text-red-500 uppercase">Lỗi nghiêm trọng</p>
                                <p class="text-xl font-bold text-red-600">1</p>
                            </div>
                            <div class="p-3 bg-blue-50 rounded-lg">
                                <p class="text-xs text-blue-500 uppercase">Chờ phản hồi</p>
                                <p class="text-xl font-bold text-blue-600">2</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thời gian bảo hành còn lại -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Thời gian bảo hành còn lại</h2>
                        
                        <div class="flex items-center justify-center">
                            <div class="inline-flex items-center justify-center rounded-full h-20 w-20 bg-blue-100 text-blue-600 text-xl font-semibold">
                                80%
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-500">Thời gian còn lại</p>
                            <p class="text-lg font-semibold text-gray-800">290 ngày</p>
                            <p class="text-xs text-gray-500 mt-1">Hết hạn: 30/06/2025</p>
                        </div>
                    </div>
                    
                    <!-- Thông tin liên hệ -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Liên hệ khách hàng</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Người liên hệ</p>
                                <p class="text-base text-gray-800 font-medium">Nguyễn Văn A</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Số điện thoại</p>
                                <p class="text-base text-gray-800 font-medium">0912345678</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="text-base text-gray-800 font-medium">nguyenvana@abc.com</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Địa chỉ</p>
                                <p class="text-base text-gray-800 font-medium">123 Lê Lợi, Quận 1, TP.HCM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa dự án <span id="deleteProjectId" class="font-medium">PRJ-2406001</span>?</p>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Hủy
                    </button>
                    <form id="deleteForm" method="POST" action="{{ url('/projects/1') }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                            Xóa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Delete modal functionality
        function openDeleteModal(id, name) {
            document.getElementById('deleteProjectId').textContent = name;
            document.getElementById('deleteForm').action = '/projects/' + id;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }

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