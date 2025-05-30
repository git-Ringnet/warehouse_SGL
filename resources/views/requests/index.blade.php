<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu yêu cầu - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
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
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Phiếu yêu cầu</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm theo tên dự án, đối tác..." class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64 h-10" />
                    <select class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 h-10">
                        <option value="">Loại phiếu</option>
                        <option value="project">Triển khai dự án</option>
                        <option value="maintenance">Bảo trì dự án</option>
                        <option value="components">Nhập linh kiện</option>
                    </select>
                </div>
                <div class="dropdown relative">
                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center h-10">
                        <i class="fas fa-plus-circle mr-2"></i> Tạo phiếu mới <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div class="dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 hidden z-50 border border-gray-200">
                        <a href="{{ url('/requests/project') }}" class="block px-4 py-2 text-gray-800 hover:bg-blue-50 hover:text-blue-600">
                            <i class="fas fa-project-diagram mr-2"></i> Phiếu đề xuất triển khai dự án
                        </a>
                        <a href="{{ url('/requests/maintenance') }}" class="block px-4 py-2 text-gray-800 hover:bg-blue-50 hover:text-blue-600">
                            <i class="fas fa-tools mr-2"></i> Phiếu đề xuất bảo trì dự án
                        </a>
                        <a href="{{ url('/requests/components') }}" class="block px-4 py-2 text-gray-800 hover:bg-blue-50 hover:text-blue-600">
                            <i class="fas fa-microchip mr-2"></i> Phiếu đề xuất nhập linh kiện
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày đề xuất</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kỹ thuật đề xuất</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại phiếu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên dự án</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Đối tác</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <!-- Phiếu 1 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">28/05/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Duy Đức</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Triển khai dự án</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Đất Đỏ</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">VNPT Vũng Tàu</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Chờ duyệt</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/requests/project/1') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/requests/project/1/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(1, 'Đất Đỏ')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                                <a href="{{ url('/requests/project/1/preview') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group" title="Xuất Excel" target="_blank">
                                    <i class="fas fa-file-excel text-green-500 group-hover:text-white"></i>
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Phiếu 2 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">25/05/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Minh Trí</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">Bảo trì dự án</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Tân Thành</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Viễn Thông A</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đã duyệt</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/requests/maintenance/2') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/requests/maintenance/2/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(2, 'Tân Thành')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                                <a href="{{ url('/requests/maintenance/2/preview') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group" title="Xuất Excel" target="_blank">
                                    <i class="fas fa-file-excel text-green-500 group-hover:text-white"></i>
                                </a>
                            </td>
                        </tr>
                        
                        <!-- Phiếu 3 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">22/05/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Hoàng Nam</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs">Nhập linh kiện</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bình Châu</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">FPT Telecom</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Từ chối</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/requests/components/3') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/requests/components/3/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(3, 'Bình Châu')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                                <a href="{{ url('/requests/components/3/preview') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group" title="Xuất Excel" target="_blank">
                                    <i class="fas fa-file-excel text-green-500 group-hover:text-white"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">Hiển thị 1-3 của 12 phiếu yêu cầu</div>
                <div class="flex space-x-1">
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <a href="#" class="px-3 py-1 rounded border border-blue-500 bg-blue-500 text-white">1</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">2</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">3</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">4</a>
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal p-5">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Xác nhận xóa</h3>
            <p class="text-gray-600 mb-5">Bạn có chắc chắn muốn xóa phiếu yêu cầu <span id="deleteItemName" class="font-medium"></span>?</p>
            <div class="flex justify-end space-x-2">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                    Hủy
                </button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Dropdown Menus
        document.addEventListener('DOMContentLoaded', function() {
            const dropdown = document.querySelector('.dropdown button');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            
            dropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('hidden');
            });
            
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        });

        // Delete Modal Functions
        function openDeleteModal(id, name) {
            document.getElementById('deleteItemName').textContent = name;
            
            // Set the form action based on the phiếu type
            const row = event.target.closest('tr');
            const type = row.querySelector('td:nth-child(4) span').textContent.trim().toLowerCase();
            
            let route = '';
            if (type.includes('triển khai')) {
                route = '/requests/project/' + id;
            } else if (type.includes('bảo trì')) {
                route = '/requests/maintenance/' + id;
            } else if (type.includes('nhập linh kiện')) {
                route = '/requests/components/' + id;
            }
            
            document.getElementById('deleteForm').action = route;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }
    </script>
</body>
</html> 