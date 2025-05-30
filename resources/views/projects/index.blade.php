<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý dự án - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <style>
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
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý dự án</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm theo tên, mã dự án..." class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64 h-10" />
                    <select class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 h-10">
                        <option value="">Trạng thái</option>
                        <option value="active">Đang thực hiện</option>
                        <option value="completed">Hoàn thành</option>
                        <option value="pending">Chờ duyệt</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                <a href="{{ url('/projects/create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center h-10">
                    <i class="fas fa-plus-circle mr-2"></i> Tạo dự án mới
                </a>
            </div>
        </header>
        <main class="p-6">
            <!-- Project Stats -->
           

            <!-- Projects Table -->
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã dự án</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên dự án</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Khách hàng</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày bắt đầu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày kết thúc</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thiết bị BH</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <!-- Project 1 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">PRJ-2406001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lắp đặt hệ thống giám sát</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty ABC</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">30/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đang thực hiện</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">15/20</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/projects/1') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/projects/1/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(1, 'PRJ-2406001')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Project 2 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">PRJ-2406002</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nâng cấp hệ thống IoT</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty XYZ</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/07/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Chờ duyệt</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">0/10</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/projects/2') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/projects/2/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(2, 'PRJ-2406002')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Project 3 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">PRJ-2405003</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bảo trì hệ thống</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty DEF</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/05/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">31/05/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Hoàn thành</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">8/8</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/projects/3') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/projects/3/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(3, 'PRJ-2405003')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">Hiển thị 1-3 của 24 dự án</div>
                <div class="flex space-x-1">
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <a href="#" class="px-3 py-1 rounded border border-blue-500 bg-blue-500 text-white">1</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">2</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">3</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">4</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">5</a>
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </main>
    </div>

    
    <script>
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