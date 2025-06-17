<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SGL - Hệ thống quản lý kho</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
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
                    Tổng quan
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
                            <!-- New notification -->
                            <a href="#" class="flex px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="h-8 w-8 rounded-full bg-green-500 text-white flex items-center justify-center">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-800 dark:text-gray-200 mb-1 font-medium">Xác nhận phiếu nhập #NV-2023-42 thành công</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">30 phút trước</p>
                                </div>
                                <span class="h-2 w-2 bg-blue-500 rounded-full"></span>
                            </a>
                            
                            <!-- Warning notification -->
                            <a href="#" class="flex px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="h-8 w-8 rounded-full bg-yellow-500 text-white flex items-center justify-center">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-800 dark:text-gray-200 mb-1 font-medium">Thời gian bảo hành sản phẩm #TH500-230815-042 sắp kết thúc</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">2 giờ trước</p>
                                </div>
                                <span class="h-2 w-2 bg-blue-500 rounded-full"></span>
                            </a>
                            
                            <!-- Update notification -->
                            <a href="#" class="flex px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="h-8 w-8 rounded-full bg-blue-500 text-white flex items-center justify-center">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-800 dark:text-gray-200 mb-1 font-medium">Nguyễn Văn A đã cập nhật thông tin phiếu xuất #PX-2023-78</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Hôm qua lúc 15:45</p>
                                </div>
                                <span class="h-2 w-2 bg-blue-500 rounded-full"></span>
                            </a>
                            
                            <!-- Read notification -->
                            <a href="#" class="flex px-4 py-3 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-shrink-0 mr-3">
                                    <div class="h-8 w-8 rounded-full bg-gray-500 text-white flex items-center justify-center">
                                        <i class="fas fa-box"></i>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-800 dark:text-gray-200 mb-1">Vật tư #VT001 đã được nhập kho thành công</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">21/08/2023</p>
                                </div>
                            </a>
                        </div>
                        <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                            <a href="/notifications" class="block text-center text-sm text-blue-600 dark:text-blue-400 hover:underline">Xem tất cả thông báo</a>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <button id="userMenuToggle" class="flex items-center focus:outline-none">
                        <img src="{{ Auth::user()->avatar ? asset(Auth::user()->avatar) : 'https://randomuser.me/api/portraits/men/32.jpg' }}" alt="User"
                            class="w-8 h-8 rounded-full mr-2" />
                        <span class="text-gray-700 dark:text-gray-300 hidden md:inline">{{ Auth::user()->name }}</span>
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
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                        Tổng quan hệ thống
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Thống kê và báo cáo tổng quan - Cập nhật lúc
                        <span id="current-time"></span>
                    </p>
                </div>

                <div class="flex space-x-3 mt-4 md:mt-0">
                    <div class="relative">
                        <select id="timeRangeSelect"
                            class="appearance-none bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="today">Theo ngày</option>
                            <option value="week">Theo tuần</option>
                            <option value="month" selected>Theo tháng</option>
                            <option value="year">Theo năm</option>
                            <option value="custom">Tùy chỉnh</option>
                        </select>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    
                    <!-- Date inputs - will show/hide based on selection -->
                    <div id="dateInputs" class="flex space-x-2">
                        <!-- Single day picker - for "Theo ngày" -->
                        <div id="dayInput" class="hidden">
                            <input type="date" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        
                        <!-- Week picker - for "Theo tuần" -->
                        <div id="weekInput" class="hidden">
                            <input type="week" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        
                        <!-- Month picker - for "Theo tháng" -->
                        <div id="monthInput" class="flex space-x-2">
                            <input type="month" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        
                        <!-- Year picker - for "Theo năm" -->
                        <div id="yearInput" class="hidden">
                            <select class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="2023" selected>2023</option>
                                <option value="2022">2022</option>
                                <option value="2021">2021</option>
                                <option value="2020">2020</option>
                            </select>
                        </div>
                        
                        <!-- Date range picker - for "Tùy chỉnh" -->
                        <div id="customRangeInput" class="hidden">
                            <div class="flex items-center space-x-2">
                                <input type="date" placeholder="Từ ngày" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <span class="text-gray-500 dark:text-gray-400">đến</span>
                                <input type="date" placeholder="Đến ngày" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Tìm kiếm thông tin</h3>
                <div class="flex flex-col md:flex-row gap-3">
                    <div class="flex-grow">
                        <div class="relative">
                            <input type="text" id="searchQuery" placeholder="Tìm kiếm theo ID hoặc Serial..." 
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-10 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <select id="searchCategory" 
                            class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-4 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-auto">
                            <option value="all">Tất cả</option>
                            <option value="materials">Vật tư</option>
                            <option value="finished">Thành phẩm</option>
                            <option value="goods">Hàng hóa</option>
                            <option value="projects">Dự án</option>
                            <option value="customers">Khách hàng</option>
                        </select>
                    </div>
                    <button id="searchButton" 
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Tìm kiếm
                    </button>
                </div>
                
                <!-- Search Results -->
                <div id="searchResults" class="mt-4 hidden">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold text-gray-800 dark:text-white" id="resultTitle">Kết quả tìm kiếm</h4>
                            <button id="closeResults" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Basic Item Details -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="mb-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Mã:</span>
                                        <span class="text-lg font-semibold text-gray-800 dark:text-white ml-2" id="itemId"></span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Tên:</span>
                                        <span class="text-lg font-semibold text-gray-800 dark:text-white ml-2" id="itemName"></span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Loại:</span>
                                        <span class="text-lg font-semibold text-gray-800 dark:text-white ml-2" id="itemCategory"></span>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Serial:</span>
                                        <span class="text-lg font-semibold text-gray-800 dark:text-white ml-2" id="itemSerial"></span>
                                    </div>
                                    <div>
                                        <a href="#" id="viewDetail" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                            Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Detailed Information Tables -->
                        <!-- Materials Info Table -->
                        <div id="materialsInfo" class="hidden">
                            <h5 class="font-semibold text-gray-800 dark:text-white mb-3">Thông tin vật tư</h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                    <tbody>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Nhà cung cấp</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="materialSupplier"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Ngày nhập</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="itemDate"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Số lượng hiện có</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="materialQuantity"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Đơn vị</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="materialUnit"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Vị trí</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="itemLocation"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Finished Products Info Table -->
                        <div id="finishedInfo" class="hidden">
                            <h5 class="font-semibold text-gray-800 dark:text-white mb-3">Thông tin thành phẩm</h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                    <tbody>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Ngày sản xuất</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="finishedManufactureDate"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Ngày nhập kho</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="itemDate"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Số lượng hiện có</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="finishedQuantity"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Thuộc dự án</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="finishedProject"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Vị trí</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="itemLocation"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Goods Info Table -->
                        <div id="goodsInfo" class="hidden">
                            <h5 class="font-semibold text-gray-800 dark:text-white mb-3">Thông tin hàng hóa</h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                    <tbody>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Nhà phân phối</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="goodsDistributor"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Ngày nhập</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="itemDate"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Giá nhập</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="goodsPrice"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Số lượng</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="goodsQuantity"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Vị trí</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="itemLocation"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Projects Info Table -->
                        <div id="projectsInfo" class="hidden">
                            <h5 class="font-semibold text-gray-800 dark:text-white mb-3">Thông tin dự án</h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                    <tbody>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Khách hàng</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="projectCustomer"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Địa điểm</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="itemLocation"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Ngày bắt đầu</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="projectStartDate"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Hạn hoàn thành</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="projectEndDate"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Customers Info Table -->
                        <div id="customersInfo" class="hidden">
                            <h5 class="font-semibold text-gray-800 dark:text-white mb-3">Thông tin khách hàng</h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                    <tbody>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Số điện thoại</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="customerPhone"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Email</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="customerEmail"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Địa chỉ</td>
                                            <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium" id="customerAddress"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Related Projects Table for Customer -->
                            <h5 class="font-semibold text-gray-800 dark:text-white mt-4 mb-3">Các dự án liên quan</h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden" id="customerProjectsTable">
                                    <thead class="bg-gray-100 dark:bg-gray-700">
                                        <tr>
                                            <th class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">Mã dự án</th>
                                            <th class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">Tên dự án</th>
                                            <th class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">Ngày bắt đầu</th>
                                            <th class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customerProjectsList">
                                        <!-- Will be filled dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <!-- Vật tư -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-3">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-500 dark:text-blue-300 mr-4">
                            <i class="fas fa-boxes text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Vật tư</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mt-2">
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng nhập kho</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">1,248</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng xuất kho</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">987</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng hư hỏng</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">125</p>
                        </div>
                    </div>
                </div>

                <!-- Thành phẩm -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-3">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 text-green-500 dark:text-green-300 mr-4">
                            <i class="fas fa-box-open text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Thành phẩm</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mt-2">
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng nhập kho</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">854</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng xuất kho</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">750</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng hư hỏng</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">95</p>
                        </div>
                    </div>
                </div>

                <!-- Hàng hóa -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-3">
                        <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-500 dark:text-purple-300 mr-4">
                            <i class="fas fa-shopping-cart text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Hàng hóa</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mt-2">
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng nhập kho</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">1,146</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng xuất kho</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">850</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng hư hỏng</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">125</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chart Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Inventory Overview Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 lg:col-span-2 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Tổng quan nhập/xuất/hư hỏng
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <button data-category="materials" class="category-filter px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800 active">Vật tư</button>
                            </div>
                            <div class="flex items-center">
                                <button data-category="finished" class="category-filter px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Thành phẩm</button>
                            </div>
                            <div class="flex items-center">
                                <button data-category="goods" class="category-filter px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Hàng hóa</button>
                            </div>
                        </div>
                    </div>
                    <div class="chart-container-lg">
                        <canvas id="inventoryOverviewChart"></canvas>
                    </div>
                </div>

                <!-- Project Growth Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Mức độ gia tăng dự án
                        </h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="projectGrowthChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Secondary Chart Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Inventory Categories Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Phân loại kho
                        </h3>
                        <div class="text-blue-500 dark:text-blue-300 text-sm">
                            <i class="fas fa-tags mr-1"></i> Tất cả
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="inventoryCategoriesChart"></canvas>
                    </div>
                </div>

                <!-- Warehouse Distribution Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Phân bố theo kho
                        </h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="warehouseDistributionChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Kho chính</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">35%</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Kho phụ</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">25%</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Kho linh kiện</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">20%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-purple-500 mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Kho thành phẩm</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">20%</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 hidden">
        <div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>Đã cập nhật dữ liệu thành công!</span>
        </div>
    </div>

    <script>
        // Update current time
        function updateCurrentTime() {
            const now = new Date();
            const options = {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            };
            document.getElementById("current-time").textContent =
                now.toLocaleDateString("vi-VN", options);
        }

        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();

        // Dropdown Menus
        const dropdownToggles = document.querySelectorAll('[id$="Toggle"]');

        dropdownToggles.forEach((toggle) => {
            toggle.addEventListener("click", (e) => {
                e.stopPropagation();
                const menu = toggle.nextElementSibling;
                menu.classList.toggle("hidden");
            });
        });
        
        // Mark notifications as read
        document.querySelector('.dropdown-menu button').addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Remove all blue dots
            const unreadIndicators = document.querySelectorAll('.dropdown-menu .bg-blue-500.rounded-full');
            unreadIndicators.forEach(dot => {
                dot.remove();
            });
            
            // Update notification counter
            document.querySelector('#notificationToggle span').textContent = '0';
            
            // Show toast notification
            showToast('success', 'Đã đánh dấu tất cả là đã đọc');
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

        // Chart tabs
        const chartTabs = document.querySelectorAll(".chart-tab");
        chartTabs.forEach((tab) => {
            tab.addEventListener("click", () => {
                // Remove active class from all tabs
                chartTabs.forEach((t) => t.classList.remove("active"));
                // Add active class to clicked tab
                tab.classList.add("active");

                // Here you would update the chart based on the selected tab
                // For demo purposes, we're just logging the tab data
                console.log(`Switched to ${tab.dataset.tab} view`);
            });
        });

        // Show Toast Notification
        setTimeout(() => {
            const toast = document.getElementById("toast");
            toast.classList.remove("hidden");

            setTimeout(() => {
                toast.classList.add("hidden");
            }, 3000);
        }, 2000);

        // Initialize Charts
        const initCharts = () => {
            const textColor = "#000000";
            const gridColor = "rgba(255, 255, 255, 0.1)";
            const borderColor = "rgba(255, 255, 255, 0.2)";

            // Inventory Overview Chart
            const inventoryOverviewCtx = document
                .getElementById("inventoryOverviewChart")
                .getContext("2d");
            const inventoryOverviewChart = new Chart(inventoryOverviewCtx, {
                type: "bar",
                data: {
                    labels: [
                        "Tháng 1",
                        "Tháng 2",
                        "Tháng 3",
                        "Tháng 4",
                        "Tháng 5",
                        "Tháng 6",
                    ],
                    datasets: [{
                            label: "Nhập kho",
                            data: [450, 500, 550, 600, 650, 700],
                            backgroundColor: "#10b981",
                            borderRadius: 4,
                        },
                        {
                            label: "Xuất kho",
                            data: [300, 350, 400, 450, 500, 550],
                            backgroundColor: "#ef4444",
                            borderRadius: 4,
                        },
                        {
                            label: "Hư hỏng",
                            data: [50, 45, 60, 55, 65, 70],
                            backgroundColor: "#f59e0b",
                            borderRadius: 4,
                        }
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                color: "#000000",
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                padding: 15
                            },
                        },
                        tooltip: {
                            mode: "index",
                            intersect: false,
                        },
                        datalabels: {
                            display: false,
                        },
                        title: {
                            display: true,
                            text: 'Vật Tư',
                            color: "#000000",
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: "#000000",
                                font: {
                                    weight: 'bold'
                                }
                            },
                            stacked: false,
                        },
                        y: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: "#000000",
                                font: {
                                    weight: 'bold'
                                }
                            },
                            beginAtZero: true,
                            stacked: false,
                        },
                    },
                    interaction: {
                        intersect: false,
                        mode: "index",
                    },
                },
                plugins: [ChartDataLabels],
            });

            // Project Growth Chart
            const projectGrowthCtx = document
                .getElementById("projectGrowthChart")
                .getContext("2d");
            const projectGrowthChart = new Chart(projectGrowthCtx, {
                type: "line",
                data: {
                    labels: ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6"],
                    datasets: [{
                        label: "Số lượng dự án",
                        data: [12, 19, 25, 37, 45, 56],
                        fill: true,
                        backgroundColor: "rgba(139, 92, 246, 0.2)",
                        borderColor: "#8b5cf6",
                        tension: 0.4,
                        pointBackgroundColor: "#8b5cf6",
                        pointBorderColor: "#fff",
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: "index",
                            intersect: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: "#000000",
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: "#000000",
                                font: {
                                    weight: 'bold'
                                },
                                precision: 0
                            },
                            beginAtZero: true
                        }
                    }
                }
            });

            // Warehouse Distribution Chart
            const warehouseDistributionCtx = document
                .getElementById("warehouseDistributionChart")
                .getContext("2d");
            const warehouseDistributionChart = new Chart(warehouseDistributionCtx, {
                type: "doughnut",
                data: {
                    labels: ["Kho chính", "Kho phụ", "Kho linh kiện", "Kho thành phẩm"],
                    datasets: [{
                        data: [35, 25, 20, 20],
                        backgroundColor: ["#3b82f6", "#10b981", "#f59e0b", "#8b5cf6"],
                        borderWidth: 0,
                    }, ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw}%`;
                                },
                            },
                        },
                        datalabels: {
                            formatter: (value) => {
                                return `${value}%`;
                            },
                            color: "#000000",
                            font: {
                                weight: "bold",
                                size: 11
                            },
                        },
                    },
                    cutout: "70%",
                },
                plugins: [ChartDataLabels],
            });

            // Inventory Categories Chart
            const inventoryCategoriesCtx = document
                .getElementById("inventoryCategoriesChart")
                .getContext("2d");
            const inventoryCategoriesChart = new Chart(inventoryCategoriesCtx, {
                type: "polarArea",
                data: {
                    labels: [
                        "Linh kiện",
                        "Thành phẩm",
                    ],
                    datasets: [{
                        data: [45, 25],
                        backgroundColor: [
                            "rgba(59, 130, 246, 0.8)",
                            "rgba(16, 185, 129, 0.8)",
                            "rgba(245, 158, 11, 0.8)",
                            "rgba(139, 92, 246, 0.8)",
                            "rgba(239, 68, 68, 0.8)",
                        ],
                        borderWidth: 0,
                    }, ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "right",
                            labels: {
                                color: "#000000",
                                font: {
                                    weight: 'bold'
                                }
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw}%`;
                                },
                            },
                        },
                        datalabels: {
                            formatter: (value) => {
                                return `${value}%`;
                            },
                            color: "#000000",
                            font: {
                                weight: "bold",
                            },
                        },
                    },
                    scales: {
                        r: {
                            grid: {
                                color: gridColor,
                            },
                            ticks: {
                                display: false,
                            },
                            pointLabels: {
                                color: "#000000",
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                    },
                },
            });

            return {
                inventoryOverviewChart,
                warehouseDistributionChart,
                inventoryCategoriesChart,
                projectGrowthChart
            };
        };

        // Update charts for dark mode
        const updateChartsForDarkMode = () => {
            const textColor = "#000000";
            const gridColor = "rgba(255, 255, 255, 0.1)";
            const borderColor = "rgba(255, 255, 255, 0.2)";

            // Inventory Overview Chart
            charts.inventoryOverviewChart.options.scales.x.grid.color = gridColor;
            charts.inventoryOverviewChart.options.scales.x.ticks.color = "#000000";
            charts.inventoryOverviewChart.options.scales.y.grid.color = gridColor;
            charts.inventoryOverviewChart.options.scales.y.ticks.color = "#000000";
            charts.inventoryOverviewChart.options.plugins.legend.labels.color = "#000000";
            charts.inventoryOverviewChart.options.plugins.title.color = "#000000";
            charts.inventoryOverviewChart.update();

            // Warehouse Distribution Chart
            charts.warehouseDistributionChart.options.plugins.datalabels.color = "#000000";
            charts.warehouseDistributionChart.update();

            // Inventory Categories Chart
            charts.inventoryCategoriesChart.options.plugins.legend.labels.color = "#000000";
            charts.inventoryCategoriesChart.options.plugins.datalabels.color = "#000000";
            charts.inventoryCategoriesChart.options.scales.r.grid.color = gridColor;
            charts.inventoryCategoriesChart.options.scales.r.pointLabels.color = "#000000";
            charts.inventoryCategoriesChart.update();

            // Project Growth Chart
            charts.projectGrowthChart.options.scales.x.grid.color = gridColor;
            charts.projectGrowthChart.options.scales.x.ticks.color = "#000000";
            charts.projectGrowthChart.options.scales.y.grid.color = gridColor;
            charts.projectGrowthChart.options.scales.y.ticks.color = "#000000";
            charts.projectGrowthChart.update();
        };

        // Update charts for light mode
        const updateChartsForLightMode = () => {
            const textColor = "#000000";
            const gridColor = "rgba(0, 0, 0, 0.1)";
            const borderColor = "rgba(0, 0, 0, 0.1)";

            // Inventory Overview Chart
            charts.inventoryOverviewChart.options.scales.x.grid.color = gridColor;
            charts.inventoryOverviewChart.options.scales.x.ticks.color = "#000000";
            charts.inventoryOverviewChart.options.scales.y.grid.color = gridColor;
            charts.inventoryOverviewChart.options.scales.y.ticks.color = "#000000";
            charts.inventoryOverviewChart.options.plugins.legend.labels.color = "#000000";
            charts.inventoryOverviewChart.options.plugins.title.color = "#000000";
            charts.inventoryOverviewChart.update();

            // Warehouse Distribution Chart
            charts.warehouseDistributionChart.options.plugins.datalabels.color = "#000000";
            charts.warehouseDistributionChart.update();

            // Inventory Categories Chart
            charts.inventoryCategoriesChart.options.plugins.legend.labels.color = "#000000";
            charts.inventoryCategoriesChart.options.plugins.datalabels.color = "#000000";
            charts.inventoryCategoriesChart.options.scales.r.grid.color = gridColor;
            charts.inventoryCategoriesChart.options.scales.r.pointLabels.color = "#000000";
            charts.inventoryCategoriesChart.update();

            // Project Growth Chart
            charts.projectGrowthChart.options.scales.x.grid.color = gridColor;
            charts.projectGrowthChart.options.scales.x.ticks.color = "#000000";
            charts.projectGrowthChart.options.scales.y.grid.color = gridColor;
            charts.projectGrowthChart.options.scales.y.ticks.color = "#000000";
            charts.projectGrowthChart.update();
        };

        // Initialize all charts and store references
        const charts = initCharts();

        // Initialize with correct theme
        if (localStorage.getItem("theme") === "dark") {
            updateChartsForDarkMode();
        }

        // Time range selector
        const timeRangeSelect = document.getElementById("timeRangeSelect");
        timeRangeSelect.addEventListener("change", function() {
            // Here you would update the charts based on the selected time range
            // For demo purposes, we're just logging the selected value
            console.log(`Time range changed to: ${this.value}`);

            // Show loading state
            const toast = document.getElementById("toast");
            toast.innerHTML =
                '<div class="toast bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Đang tải dữ liệu...</span></div>';
            toast.classList.remove("hidden");

            // Toggle appropriate date input based on selection
            const selectedValue = this.value;
            
            // Hide all inputs first
            document.getElementById('dayInput').classList.add('hidden');
            document.getElementById('weekInput').classList.add('hidden');
            document.getElementById('monthInput').classList.add('hidden');
            document.getElementById('yearInput').classList.add('hidden');
            document.getElementById('customRangeInput').classList.add('hidden');
            
            // Show the selected input
            switch(selectedValue) {
                case 'today':
                    document.getElementById('dayInput').classList.remove('hidden');
                    break;
                case 'week':
                    document.getElementById('weekInput').classList.remove('hidden');
                    break;
                case 'month':
                    document.getElementById('monthInput').classList.remove('hidden');
                    break;
                case 'year':
                    document.getElementById('yearInput').classList.remove('hidden');
                    break;
                case 'custom':
                    document.getElementById('customRangeInput').classList.remove('hidden');
                    break;
            }

            // Simulate data loading
            setTimeout(() => {
                toast.innerHTML =
                    '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu thành công!</span></div>';

                setTimeout(() => {
                    toast.classList.add("hidden");
                }, 2000);
            }, 1500);
        });

        // Dropdown Menus
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const allDropdowns = document.querySelectorAll(".dropdown-content");

            // Close all other dropdowns
            allDropdowns.forEach((d) => {
                if (d.id !== id) {
                    d.classList.remove("show");
                }
            });

            // Toggle current dropdown
            dropdown.classList.toggle("show");
        }

        // Close dropdowns when clicking outside
        document.addEventListener("click", (e) => {
            if (!e.target.closest(".dropdown")) {
                document.querySelectorAll(".dropdown-content").forEach((dropdown) => {
                    dropdown.classList.remove("show");
                });
            }
        });

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll(".dropdown-content").forEach((dropdown) => {
            dropdown.addEventListener("click", (e) => {
                e.stopPropagation();
            });
        });

        // Category filter for Inventory Overview Chart
        document.querySelectorAll('.category-filter').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.category-filter').forEach(btn => {
                    btn.classList.remove('active', 'bg-blue-100', 'text-blue-800');
                    btn.classList.add('bg-gray-100', 'text-gray-800');
                });
                
                // Add active class to clicked button
                this.classList.add('active', 'bg-blue-100', 'text-blue-800');
                this.classList.remove('bg-gray-100', 'text-gray-800');
                
                const category = this.dataset.category;
                
                // Update chart title
                let title = '';
                switch (category) {
                    case 'materials':
                        title = 'Vật Tư';
                        break;
                    case 'finished':
                        title = 'Thành Phẩm';
                        break;
                    case 'goods':
                        title = 'Hàng Hóa';
                        break;
                }
                
                charts.inventoryOverviewChart.options.plugins.title.text = title;
                charts.inventoryOverviewChart.options.plugins.title.color = "#000000";
                charts.inventoryOverviewChart.options.plugins.title.font = {
                    size: 16,
                    weight: 'bold'
                };
                
                // Show loading state
                const toast = document.getElementById("toast");
                toast.innerHTML =
                    '<div class="toast bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Đang tải dữ liệu...</span></div>';
                toast.classList.remove("hidden");
                
                // Simulate data update (in production, you would fetch data from the server)
                setTimeout(() => {
                    // Update chart data with random values for demonstration
                    const newData = {
                        materials: {
                            input: [450, 500, 550, 600, 650, 700],
                            output: [300, 350, 400, 450, 500, 550],
                            damaged: [50, 45, 60, 55, 65, 70]
                        },
                        finished: {
                            input: [200, 220, 240, 260, 280, 300],
                            output: [180, 200, 220, 240, 260, 280],
                            damaged: [20, 25, 30, 35, 40, 45]
                        },
                        goods: {
                            input: [350, 370, 390, 410, 430, 450],
                            output: [320, 340, 360, 380, 400, 420],
                            damaged: [30, 35, 40, 45, 50, 55]
                        }
                    };
                    
                    charts.inventoryOverviewChart.data.datasets[0].data = newData[category].input;
                    charts.inventoryOverviewChart.data.datasets[1].data = newData[category].output;
                    charts.inventoryOverviewChart.data.datasets[2].data = newData[category].damaged;
                    charts.inventoryOverviewChart.update();
                    
                    toast.innerHTML =
                        '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu thành công!</span></div>';
                    
                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                }, 1000);
            });
        });

        // Handle search functionality
        document.getElementById('searchButton').addEventListener('click', function() {
            const searchQuery = document.getElementById('searchQuery').value.trim();
            const searchCategory = document.getElementById('searchCategory').value;
            
            if (searchQuery === '') {
                showToast('warning', 'Vui lòng nhập ID hoặc Serial để tìm kiếm');
                return;
            }
            
            // Show loading state
            showToast('info', 'Đang tìm kiếm...');
            
            // In a real application, you would make an API call here
            // For demonstration, we'll simulate a search with setTimeout
            setTimeout(() => {
                // Simulate search results based on the category
                const result = simulateSearchResult(searchQuery, searchCategory);
                
                if (result) {
                    displaySearchResult(result);
                    showToast('success', 'Tìm thấy thông tin!');
                } else {
                    document.getElementById('searchResults').classList.add('hidden');
                    showToast('error', 'Không tìm thấy thông tin phù hợp');
                }
            }, 800);
        });
        
        // Close search results
        document.getElementById('closeResults').addEventListener('click', function() {
            document.getElementById('searchResults').classList.add('hidden');
        });
        
        // Display search result in the UI
        function displaySearchResult(result) {
            // Set the common item details
            document.getElementById('itemId').textContent = result.id;
            document.getElementById('itemName').textContent = result.name;
            document.getElementById('itemCategory').textContent = result.categoryName;
            document.getElementById('itemSerial').textContent = result.serial;
            
            // Set view detail link
            document.getElementById('viewDetail').setAttribute('href', result.detailUrl);
            
            // Hide all category specific info sections
            document.getElementById('materialsInfo').classList.add('hidden');
            document.getElementById('finishedInfo').classList.add('hidden');
            document.getElementById('goodsInfo').classList.add('hidden');
            document.getElementById('projectsInfo').classList.add('hidden');
            document.getElementById('customersInfo').classList.add('hidden');
            
            // Show the appropriate category info section and populate it
            const category = result.category;
            
            switch (category) {
                case 'materials':
                    const materialsInfo = document.getElementById('materialsInfo');
                    materialsInfo.classList.remove('hidden');
                    document.getElementById('materialSupplier').textContent = result.additionalInfo.supplier;
                    document.getElementById('materialQuantity').textContent = result.additionalInfo.quantity;
                    document.getElementById('materialUnit').textContent = result.additionalInfo.unit;
                    break;
                    
                case 'finished':
                    const finishedInfo = document.getElementById('finishedInfo');
                    finishedInfo.classList.remove('hidden');
                    document.getElementById('finishedManufactureDate').textContent = result.additionalInfo.manufactureDate;
                    document.getElementById('finishedQuantity').textContent = result.additionalInfo.quantity;
                    document.getElementById('finishedProject').textContent = result.additionalInfo.project;
                    break;
                    
                case 'goods':
                    const goodsInfo = document.getElementById('goodsInfo');
                    goodsInfo.classList.remove('hidden');
                    document.getElementById('goodsDistributor').textContent = result.additionalInfo.distributor;
                    document.getElementById('goodsPrice').textContent = result.additionalInfo.price;
                    document.getElementById('goodsQuantity').textContent = result.additionalInfo.quantity;
                    break;
                    
                case 'projects':
                    const projectsInfo = document.getElementById('projectsInfo');
                    projectsInfo.classList.remove('hidden');
                    document.getElementById('projectCustomer').textContent = result.additionalInfo.customer;
                    document.getElementById('projectStartDate').textContent = result.additionalInfo.startDate;
                    document.getElementById('projectEndDate').textContent = result.additionalInfo.endDate;
                    break;
                    
                case 'customers':
                    const customersInfo = document.getElementById('customersInfo');
                    customersInfo.classList.remove('hidden');
                    document.getElementById('customerPhone').textContent = result.additionalInfo.phone;
                    document.getElementById('customerEmail').textContent = result.additionalInfo.email;
                    document.getElementById('customerAddress').textContent = result.additionalInfo.address;
                    
                    // Populate related projects table for customers
                    if (result.additionalInfo.relatedProjects && result.additionalInfo.relatedProjects.length > 0) {
                        const projectsListContainer = document.getElementById('customerProjectsList');
                        projectsListContainer.innerHTML = ''; // Clear existing content
                        
                        result.additionalInfo.relatedProjects.forEach(project => {
                            const row = document.createElement('tr');
                            
                            const idCell = document.createElement('td');
                            idCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                            idCell.textContent = project.id;
                            
                            const nameCell = document.createElement('td');
                            nameCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                            nameCell.textContent = project.name;
                            
                            const dateCell = document.createElement('td');
                            dateCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                            dateCell.textContent = project.startDate;
                            
                            const statusCell = document.createElement('td');
                            statusCell.className = 'py-2 px-4 border-b';
                            
                            const statusBadge = document.createElement('span');
                            statusBadge.textContent = project.status;
                            statusBadge.className = 'px-2 py-1 rounded text-xs text-white font-medium';
                            
                            // Add color based on status
                            if (project.status === 'Hoàn thành') {
                                statusBadge.classList.add('bg-green-500');
                            } else if (project.status === 'Đang thực hiện') {
                                statusBadge.classList.add('bg-blue-500');
                            } else if (project.status === 'Tạm dừng') {
                                statusBadge.classList.add('bg-orange-500');
                            } else {
                                statusBadge.classList.add('bg-gray-500');
                            }
                            
                            statusCell.appendChild(statusBadge);
                            
                            row.appendChild(idCell);
                            row.appendChild(nameCell);
                            row.appendChild(dateCell);
                            row.appendChild(statusCell);
                            
                            projectsListContainer.appendChild(row);
                        });
                    }
                    break;
            }
            
            // Show the result container
            document.getElementById('searchResults').classList.remove('hidden');
        }
        
        // Show toast notifications
        function showToast(type, message) {
            const toast = document.getElementById('toast');
            let icon, bgColor;
            
            switch (type) {
                case 'success':
                    icon = 'fa-check-circle';
                    bgColor = 'bg-green-500';
                    break;
                case 'error':
                    icon = 'fa-exclamation-circle';
                    bgColor = 'bg-red-500';
                    break;
                case 'warning':
                    icon = 'fa-exclamation-triangle';
                    bgColor = 'bg-yellow-500';
                    break;
                case 'info':
                default:
                    icon = 'fa-info-circle';
                    bgColor = 'bg-blue-500';
            }
            
            toast.innerHTML = `<div class="toast ${bgColor} text-white px-4 py-3 rounded-lg shadow-lg flex items-center">
                <i class="fas ${icon} mr-2"></i>
                <span>${message}</span>
            </div>`;
            
            toast.classList.remove('hidden');
            
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }
        
        // Simulate search results (in a real app, this would be an API call)
        function simulateSearchResult(query, category) {
            // Simulate some example search results based on the query and category
            const exampleResults = {
                'VT001': {
                    id: 'VT001',
                    name: 'Ống đồng 15mm',
                    category: 'materials',
                    categoryName: 'Vật tư',
                    serial: 'MT15-220621-001',
                    date: '22/06/2023',
                    location: 'Kho chính - Kệ A1',
                    status: 'Tồn kho',
                    detailUrl: '#/materials/VT001',
                    additionalInfo: {
                        supplier: 'Cty TNHH Thép Hoàng Hà',
                        quantity: '120',
                        unit: 'Cuộn'
                    }
                },
                'TP002': {
                    id: 'TP002',
                    name: 'Bộ điều khiển nhiệt độ TH-500',
                    category: 'finished',
                    categoryName: 'Thành phẩm',
                    serial: 'TH500-230815-042',
                    date: '15/08/2023',
                    location: 'Kho thành phẩm - Khu B',
                    status: 'Đang sử dụng',
                    detailUrl: '#/finished/TP002',
                    additionalInfo: {
                        manufactureDate: '10/08/2023',
                        quantity: '15 bộ',
                        project: 'Dự án Nhà máy nhiệt điện Phú Mỹ'
                    }
                },
                'HH003': {
                    id: 'HH003',
                    name: 'Máy nén khí Hitachi H-50',
                    category: 'goods',
                    categoryName: 'Hàng hóa',
                    serial: 'HTH50-20230421',
                    date: '21/04/2023',
                    location: 'Kho phụ - Khu C',
                    status: 'Tồn kho',
                    detailUrl: '#/goods/HH003',
                    additionalInfo: {
                        distributor: 'Hitachi Việt Nam',
                        price: '45.000.000 VNĐ',
                        quantity: '3 máy'
                    }
                },
                'DA004': {
                    id: 'DA004',
                    name: 'Lắp đặt hệ thống điều hòa Sunhouse',
                    category: 'projects',
                    categoryName: 'Dự án',
                    serial: 'PRJ-2023-042',
                    date: '05/05/2023',
                    location: 'Nhà máy Sunhouse Hà Nội',
                    status: 'Đang thực hiện',
                    detailUrl: '#/projects/DA004',
                    additionalInfo: {
                        customer: 'Tập đoàn Sunhouse',
                        startDate: '05/05/2023',
                        endDate: '15/12/2023'
                    }
                },
                'KH005': {
                    id: 'KH005',
                    name: 'Tập đoàn Điện lực Việt Nam',
                    category: 'customers',
                    categoryName: 'Khách hàng',
                    serial: 'CUS-2018-015',
                    date: '10/01/2018',
                    location: 'Hà Nội',
                    status: 'Hoạt động',
                    detailUrl: '#/customers/KH005',
                    additionalInfo: {
                        phone: '024.2222.1999',
                        email: 'contact@evn.com.vn',
                        address: '11 Cửa Bắc, Trúc Bạch, Ba Đình, Hà Nội',
                        relatedProjects: [
                            {
                                id: 'DA001',
                                name: 'Bảo trì hệ thống điều hòa trung tâm',
                                startDate: '15/03/2023',
                                status: 'Hoàn thành'
                            },
                            {
                                id: 'DA004',
                                name: 'Cung cấp thiết bị văn phòng',
                                startDate: '01/06/2023',
                                status: 'Đang thực hiện'
                            },
                            {
                                id: 'DA008',
                                name: 'Nâng cấp hệ thống chiếu sáng',
                                startDate: '10/09/2023',
                                status: 'Đang thực hiện'
                            }
                        ]
                    }
                },
                'TH500-230815-042': {
                    id: 'TP002',
                    name: 'Bộ điều khiển nhiệt độ TH-500',
                    category: 'finished',
                    categoryName: 'Thành phẩm',
                    serial: 'TH500-230815-042',
                    date: '15/08/2023',
                    location: 'Kho thành phẩm - Khu B',
                    status: 'Đang sử dụng',
                    detailUrl: '#/finished/TP002',
                    additionalInfo: {
                        manufactureDate: '10/08/2023',
                        quantity: '15 bộ',
                        project: 'Dự án Nhà máy nhiệt điện Phú Mỹ'
                    }
                }
            };
            
            // Check if we have a direct match in our example data
            if (exampleResults[query]) {
                return exampleResults[query];
            }
            
            // If no direct match but query resembles a part of a serial
            if (query.length >= 4) {
                for (const key in exampleResults) {
                    const item = exampleResults[key];
                    if (item.serial.toLowerCase().includes(query.toLowerCase())) {
                        return item;
                    }
                    if (item.id.toLowerCase().includes(query.toLowerCase())) {
                        return item;
                    }
                }
            }
            
            // Return null if no match
            return null;
        }
        
        // Add event listener to search on Enter key press
        document.getElementById('searchQuery').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchButton').click();
            }
        });

        // Initialize date inputs based on default selection
        document.addEventListener("DOMContentLoaded", function() {
            const timeRangeSelect = document.getElementById("timeRangeSelect");
            const selectedValue = timeRangeSelect.value;
            
            // Hide all inputs first
            document.getElementById('dayInput').classList.add('hidden');
            document.getElementById('weekInput').classList.add('hidden');
            document.getElementById('monthInput').classList.add('hidden');
            document.getElementById('yearInput').classList.add('hidden');
            document.getElementById('customRangeInput').classList.add('hidden');
            
            // Show the selected input
            switch(selectedValue) {
                case 'today':
                    document.getElementById('dayInput').classList.remove('hidden');
                    break;
                case 'week':
                    document.getElementById('weekInput').classList.remove('hidden');
                    break;
                case 'month':
                    document.getElementById('monthInput').classList.remove('hidden');
                    break;
                case 'year':
                    document.getElementById('yearInput').classList.remove('hidden');
                    break;
                case 'custom':
                    document.getElementById('customRangeInput').classList.remove('hidden');
                    break;
            }
            
            // Set default values to current date/month/year
            const now = new Date();
            const currentYear = now.getFullYear();
            const currentMonth = now.getMonth() + 1;
            const formattedMonth = currentMonth < 10 ? `0${currentMonth}` : currentMonth;
            const currentDay = now.getDate();
            const formattedDay = currentDay < 10 ? `0${currentDay}` : currentDay;
            
            // Set default date
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.value = `${currentYear}-${formattedMonth}-${formattedDay}`;
            });
            
            // Set default month
            const monthInputs = document.querySelectorAll('input[type="month"]');
            monthInputs.forEach(input => {
                input.value = `${currentYear}-${formattedMonth}`;
            });
            
            // Set default week
            const weekInputs = document.querySelectorAll('input[type="week"]');
            weekInputs.forEach(input => {
                // Get the week number
                const firstDayOfYear = new Date(currentYear, 0, 1);
                const pastDaysOfYear = (now - firstDayOfYear) / 86400000;
                const currentWeek = Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
                const formattedWeek = currentWeek < 10 ? `0${currentWeek}` : currentWeek;
                input.value = `${currentYear}-W${formattedWeek}`;
            });
        });
    </script>
</body>

</html>
