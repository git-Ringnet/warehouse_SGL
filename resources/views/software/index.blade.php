<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phần mềm - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
</head>
<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý phần mềm</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm theo tên, phiên bản..." class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64 h-10" />
                    <select class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 h-10">
                        <option value="">Loại file</option>
                        <option value="apk">APK</option>
                        <option value="bin">BIN</option>
                        <option value="zip">ZIP</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <a href="{{ url('/software/create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center h-10">
                    <i class="fas fa-plus-circle mr-2"></i> Thêm phần mềm mới
                </a>
            </div>
        </header>
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên phần mềm</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phiên bản</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại file</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kích thước</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày tải lên</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <!-- Sample data row 1 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Ứng dụng SGL Mobile</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1.2.5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">APK</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">25.4 MB</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/software/1') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/software/1/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(1, 'Ứng dụng SGL Mobile')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                                <a href="#" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group" title="Tải xuống">
                                    <i class="fas fa-download text-purple-500 group-hover:text-white"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Sample data row 2 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Firmware SGL Scanner</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2.0.1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">BIN</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">5.7 MB</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">10/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/software/2') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/software/2/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(2, 'Firmware SGL Scanner')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                                <a href="#" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group" title="Tải xuống">
                                    <i class="fas fa-download text-purple-500 group-hover:text-white"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Sample data row 3 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Bộ cài đặt SGL Desktop</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3.1.4</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">ZIP</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">105.2 MB</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">05/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/software/3') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/software/3/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(3, 'Bộ cài đặt SGL Desktop')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                                <a href="#" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group" title="Tải xuống">
                                    <i class="fas fa-download text-purple-500 group-hover:text-white"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Sample data row 4 -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Driver SGL Printer</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1.0.0</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">EXE</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">12.8 MB</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Đã ngừng</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/software/4') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/software/4/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(4, 'Driver SGL Printer')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                                <a href="#" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group" title="Tải xuống">
                                    <i class="fas fa-download text-purple-500 group-hover:text-white"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">Hiển thị 1-4 của 4 mục</div>
                <div class="flex space-x-1">
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <a href="#" class="px-3 py-1 rounded border border-blue-500 bg-blue-500 text-white">1</a>
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </main>
    </div>

   

    <script>
       
        
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