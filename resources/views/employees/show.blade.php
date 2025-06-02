<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết nhân viên - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết nhân viên</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    ID: EMP001
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ url('/employees/1/edit') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Sửa
                </a>
              
                <a href="{{ url('/employees') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            <!-- Thông tin cơ bản -->
            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row justify-between">
                            <div class="flex flex-col md:flex-row items-start gap-6">
                                <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 overflow-hidden">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Nguyễn Văn Quản Trị" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800">Nguyễn Văn Quản Trị</h2>
                                    <div class="mt-2 flex items-center text-sm text-gray-600">
                                        <div class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold mr-2">
                                            Quản trị viên
                                        </div>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                            Đang hoạt động
                                        </span>
                                    </div>
                                  
                                    <p class="text-gray-600 mt-1">Tài khoản từ 01/01/2024</p>
                                </div>
                            </div>
                            <div class="mt-6 lg:mt-0 flex flex-col">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-envelope text-gray-400 w-6"></i>
                                    <span class="ml-3 text-gray-700">admin@sgl.com</span>
                                </div>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-phone text-gray-400 w-6"></i>
                                    <span class="ml-3 text-gray-700">0987654321</span>
                                </div>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-gray-400 w-6"></i>
                                    <span class="ml-3 text-gray-700">123 Lê Lợi, Quận 1, TP.HCM</span>
                                </div>
                           
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Thông tin tài khoản và cá nhân (đã gộp) -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-blue-500"></i>
                        Thông tin nhân viên
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Username</p>
                            <p class="font-medium text-gray-800">admin</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Mã nhân viên</p>
                            <p class="font-medium text-gray-800">EMP001</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Họ và tên</p>
                            <p class="font-medium text-gray-800">Nguyễn Văn Quản Trị</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium text-gray-800">admin@sgl.com</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Số điện thoại</p>
                            <p class="font-medium text-gray-800">0987654321</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Địa chỉ</p>
                            <p class="font-medium text-gray-800">123 Lê Lợi, Quận 1, TP.HCM</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Lần đăng nhập cuối</p>
                            <p class="font-medium text-gray-800">30/05/2024 15:30:22</p>
                        </div>
                    </div>
                </div>
                
                <!-- Thông tin công việc -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-briefcase mr-2 text-purple-500"></i>
                        Thông tin công việc
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Vai trò</p>
                            <p class="font-medium text-gray-800">Quản trị viên</p>
                        </div>
                     
                     
                        <div>
                            <p class="text-sm text-gray-500">Trạng thái</p>
                            <p class="font-medium text-gray-800">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                    Đang hoạt động
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ngày vào làm</p>
                            <p class="font-medium text-gray-800">01/01/2024</p>
                        </div>
                     
                    </div>
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
    </script>
</body>
</html> 