<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhà cung cấp - SGL</title>
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
        .sidebar .menu-item a {
            color: #fff;
            transition: all 0.3s ease;
        }
        .sidebar .menu-item a:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .sidebar .menu-item a.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid #3b82f6;
            color: #fff;
        }
        .sidebar .nav-item {
            color: #fff;
            transition: all 0.3s ease;
        }
        .sidebar .nav-item:hover {
            background: rgba(255,255,255,0.1);
        }
        .sidebar .nav-item.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid #3b82f6;
        }
        .sidebar .dropdown-content a {
            color: #fff;
            transition: all 0.3s ease;
        }
        .sidebar .dropdown-content a:hover {
            background: rgba(255,255,255,0.1);
        }
        .sidebar .dropdown-content a.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid #3b82f6;
            color: #fff;
            font-weight: 500;
        }
        .dropdown-content {
            display: none;
        }
        .dropdown-content.show {
            display: block;
        }
        .sidebar .logo-text {
            color: #fff;
            font-weight: 600;
        }
        .sidebar .logo-icon {
            color: #fff;
        }
        .sidebar .search-input {
            color: #e2e8f0;
        }
        .sidebar .search-input::placeholder {
            color: #94a3b8;
        }
        .sidebar .user-info {
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .user-info p {
            color: #e2e8f0;
        }
        .sidebar .user-info .role {
            color: #ffffff;
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
        .sidebar .nav-item .flex {
            color: #fff;
        }
        
        .sidebar .flex {
            color: #fff;
        }
        
        .sidebar .dropdown button .flex {
            color: #fff;
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý nhà cung cấp</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Nhập tên, số điện thoại, email..." class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64" />
                    <select class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                        <option value="">Bộ lọc</option>
                        <option value="name">Tên nhà cung cấp</option>
                        <option value="phone">Số điện thoại</option>
                        <option value="email">Email</option>
                        <option value="address">Địa chỉ</option>
                    </select>
                </div>
                <a href="{{ url('/suppliers/create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                    <i class="fas fa-plus mr-2"></i> Thêm nhà cung cấp
                </a>
            </div>
        </header>
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên nhà cung cấp</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số điện thoại</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Địa chỉ</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Công ty ABC</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0987654321</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">contact@abc.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">123 Lê Lợi, Hà Nội</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/05/2024</td>
                           
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/suppliers/1') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/suppliers/1/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(1, 'Công ty ABC')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Công ty XYZ</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0912345678</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">info@xyz.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">456 Trần Hưng Đạo, TP.HCM</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/04/2024</td>
                            
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/suppliers/2') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/suppliers/2/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(2, 'Công ty XYZ')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Công ty QWE</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0901234567</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">qwe@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">789 Nguyễn Trãi, Đà Nẵng</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">20/03/2024</td>
                           
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button onclick="openDeleteModal(3, 'Công ty QWE')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Công ty DEF</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0911111111</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">def@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">11 Lê Lai, Hà Nội</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/07/2023</td>
                           
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button onclick="openDeleteModal(4, 'Công ty DEF')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Công ty GHI</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0912345679</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">ghi@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">23 Nguyễn Huệ, Huế</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">12/07/2023</td>
                           
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button onclick="openDeleteModal(5, 'Công ty GHI')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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