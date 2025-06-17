<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SGL - Hồ sơ cá nhân</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area ml-64">
        <!-- Top Bar -->
        <header
            class="bg-white dark:bg-gray-800 shadow-sm py-4 px-6 flex justify-between items-center fixed top-0 right-0 left-0 z-40"
            style="left: 256px">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Hồ sơ cá nhân
                </h1>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Notification Bell -->
                <div class="relative">
                    <button id="notificationToggle" class="flex items-center focus:outline-none relative">
                        <i class="fas fa-bell text-gray-700 dark:text-gray-300 text-xl"></i>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                    </button>
                    <div class="dropdown-menu absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-0 hidden z-50 border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Thông báo</h3>
                            <div class="flex space-x-2">
                                <button class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Đánh dấu đã đọc</button>
                            </div>
                        </div>
                        <div class="max-h-80 overflow-y-auto py-1">
                            <!-- Notifications -->
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <button id="userMenuToggle" class="flex items-center focus:outline-none">
                        <img src="{{ $employee->avatar ? asset($employee->avatar) : 'https://randomuser.me/api/portraits/men/32.jpg' }}" alt="User"
                            class="w-8 h-8 rounded-full mr-2" />
                        <span class="text-gray-700 dark:text-gray-300 hidden md:inline">{{ $employee->name }}</span>
                    </button>
                    <div
                        class="dropdown-menu absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 hidden z-50 border border-gray-200 dark:border-gray-700">
                        <a href="{{ route('profile') }}"
                            class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700">Hồ
                            sơ</a>
                        <a href="#"
                            class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700">Cài
                            đặt</a>
                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="block w-full text-left px-4 py-2 text-red-500 hover:bg-blue-50 dark:hover:bg-gray-700">Đăng xuất</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="pt-20 pb-16 px-6">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/3 flex flex-col items-center mb-6 md:mb-0">
                            <div class="relative">
                                <img src="{{ $employee->avatar ? asset($employee->avatar) : 'https://randomuser.me/api/portraits/men/32.jpg' }}" 
                                    alt="{{ $employee->name }}" 
                                    class="w-40 h-40 rounded-full object-cover border-4 border-blue-100 dark:border-blue-900">
                            </div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white mt-4">{{ $employee->name }}</h2>
                            <p class="text-blue-600 dark:text-blue-400 font-medium">{{ $employee->role }}</p>
                        </div>
                        
                        <div class="md:w-2/3 md:pl-8">
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Thông tin cá nhân</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Tên đăng nhập</p>
                                        <p class="text-gray-800 dark:text-white font-medium">{{ $employee->username }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                                        <p class="text-gray-800 dark:text-white font-medium">{{ $employee->email ?? 'Chưa cập nhật' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Số điện thoại</p>
                                        <p class="text-gray-800 dark:text-white font-medium">{{ $employee->phone }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Phòng ban</p>
                                        <p class="text-gray-800 dark:text-white font-medium">{{ $employee->department ?? 'Chưa cập nhật' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Địa chỉ</h3>
                                <p class="text-gray-800 dark:text-white">{{ $employee->address ?? 'Chưa cập nhật' }}</p>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Ghi chú</h3>
                                <p class="text-gray-800 dark:text-white">{{ $employee->notes ?? 'Không có ghi chú' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dropdown Menus
        const dropdownToggles = document.querySelectorAll('[id$="Toggle"]');

        dropdownToggles.forEach((toggle) => {
            toggle.addEventListener("click", (e) => {
                e.stopPropagation();
                const menu = toggle.nextElementSibling;
                menu.classList.toggle("hidden");
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener("click", () => {
            document.querySelectorAll(".dropdown-menu").forEach((menu) => {
                menu.classList.add("hidden");
            });
        });

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll(".dropdown-menu").forEach((menu) => {
            menu.addEventListener("click", (e) => {
                e.stopPropagation();
            });
        });
    </script>
</body>

</html> 