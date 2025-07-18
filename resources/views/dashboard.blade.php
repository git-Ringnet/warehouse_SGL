<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    

        <!-- Main Content -->
        <main class=" pb-16 px-6">
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
                            <input type="text" id="searchQuery" placeholder="Tìm kiếm theo ID hoặc Tên..." 
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
                    <!-- <div>
                        <button id="advancedFilterBtn" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors">
                            <i class="fas fa-filter mr-1"></i> Bộ lọc
                        </button>
                    </div> -->
                    <button id="searchButton" 
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Tìm kiếm
                    </button>
                </div>
                
                <!-- Advanced Filter Panel -->
                <div id="advancedFilterPanel" class="mt-4 hidden border-t border-gray-200 dark:border-gray-700 pt-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Filter by Warehouse -->
                        <div>
                            <label for="filterWarehouse" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kho</label>
                            <select id="filterWarehouse" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả kho</option>
                                <!-- Warehouses will be loaded dynamically -->
                            </select>
                        </div>
                        
                        <!-- Filter by Status -->
                        <div>
                            <label for="filterStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trạng thái</label>
                            <select id="filterStatus" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả trạng thái</option>
                                <option value="active">Đang hoạt động</option>
                                <option value="inactive">Không hoạt động</option>
                                <option value="pending">Đang chờ xử lý</option>
                            </select>
                        </div>
                        
                        <!-- Filter by Date Range -->
                        <div>
                            <label for="filterDateRange" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thời gian</label>
                            <select id="filterDateRange" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả thời gian</option>
                                <option value="today">Hôm nay</option>
                                <option value="week">Tuần này</option>
                                <option value="month">Tháng này</option>
                                <option value="year">Năm nay</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Include Out of Stock Checkbox -->
                    <div class="mt-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="includeOutOfStock" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="includeOutOfStock" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Bao gồm cả sản phẩm ngoài kho (số lượng = 0)
                            </label>
                        </div>
                    </div>
                    
                    <!-- Category-specific filters -->
                    <div id="materialFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
                        <div>
                            <label for="filterMaterialType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Loại vật tư</label>
                            <select id="filterMaterialType" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả loại</option>
                                <option value="electronic">Điện tử</option>
                                <option value="mechanical">Cơ khí</option>
                                <option value="chemical">Hóa chất</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="productFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
                        <div>
                            <label for="filterProductType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Loại thành phẩm</label>
                            <select id="filterProductType" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả loại</option>
                                <option value="device">Thiết bị</option>
                                <option value="component">Linh kiện</option>
                                <option value="system">Hệ thống</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="projectFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
                        <div>
                            <label for="filterProjectStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trạng thái dự án</label>
                            <select id="filterProjectStatus" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả trạng thái</option>
                                <option value="planning">Đang lập kế hoạch</option>
                                <option value="in_progress">Đang thực hiện</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="on_hold">Tạm dừng</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <button id="resetFilters" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors mr-2">
                            Đặt lại
                        </button>
                        <button id="applyFilters" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            Áp dụng
                        </button>
                    </div>
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
                        
                        <!-- Search Results Count -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Tìm thấy </span>
                                    <span class="text-lg font-semibold text-gray-800 dark:text-white" id="resultCount">0</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400"> kết quả</span>
                                </div>
                                <div>
                                    <button id="viewAllResults" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                                        Xem tất cả <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search Results List -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">Mã</th>
                                        <th class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">Tên</th>
                                        <th class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">Loại</th>
                                        <th class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">Serial</th>
                                        <th class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">Vị trí</th>
                                        <th class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="searchResultsList">
                                    <!-- Results will be dynamically inserted here -->
                                </tbody>
                            </table>
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
                            <p class="text-lg font-bold text-gray-800 dark:text-white" id="materials-import">0</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng xuất kho</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white" id="materials-export">0</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng hư hỏng</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white" id="materials-damaged">0</p>
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
                            <p class="text-lg font-bold text-gray-800 dark:text-white" id="products-import">0</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng xuất kho</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white" id="products-export">0</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng hư hỏng</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white" id="products-damaged">0</p>
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
                            <p class="text-lg font-bold text-gray-800 dark:text-white" id="goods-import">0</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng xuất kho</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white" id="goods-export">0</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-2 rounded">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tổng hư hỏng</p>
                            <p class="text-lg font-bold text-gray-800 dark:text-white" id="goods-damaged">0</p>
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
                                <button data-category="products" class="category-filter px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Thành phẩm</button>
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
                        <div class="flex items-center space-x-2">
                            <select id="warehouseChartItemType" class="text-sm bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-1 px-2 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">Tất cả loại</option>
                                <option value="material">Vật tư</option>
                                <option value="product">Thành phẩm</option>
                                <option value="good">Hàng hóa</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="warehouseDistributionChart"></canvas>
                    </div>
                    <div class="mt-4" id="warehouse-distribution-legend">
                        <!-- Legend items will be dynamically inserted here -->
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

    <!-- Modal for viewing item details -->
    <div id="itemDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center sticky top-0 bg-white dark:bg-gray-800 z-10">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white" id="modalTitle">Chi tiết</h3>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-4">
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

        // Initialize all charts and store references
        let charts;
        
        const initCharts = () => {
            // Nếu đã có charts thì không khởi tạo lại
            if (window.chartInstances) {
                return window.chartInstances;
            }
            
            const textColor = "#000000";
            const gridColor = "rgba(255, 255, 255, 0.1)";
            const borderColor = "rgba(255, 255, 255, 0.2)";

            // Destroy existing charts if they exist
            if (window.chartInstances) {
                Object.values(window.chartInstances).forEach(chart => {
                    if (chart) chart.destroy();
                });
            }
            
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
                            data: [0, 0, 0, 0, 0, 0],
                            backgroundColor: "#10b981",
                            borderRadius: 4,
                        },
                        {
                            label: "Xuất kho",
                            data: [0, 0, 0, 0, 0, 0],
                            backgroundColor: "#ef4444",
                            borderRadius: 4,
                        },
                        {
                            label: "Hư hỏng",
                            data: [0, 0, 0, 0, 0, 0],
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
                    labels: ["Đang tải dữ liệu..."],
                    datasets: [{
                        data: [100],
                        backgroundColor: ["#3b82f6"],
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
                        "Vật tư",
                        "Thành phẩm",
                        "Hàng hóa",
                    ],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: [
                            "rgba(59, 130, 246, 0.8)",
                            "rgba(16, 185, 129, 0.8)",
                            "rgba(245, 158, 11, 0.8)",
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

            // Save chart instances for later access/destruction
            window.chartInstances = {
                inventoryOverviewChart,
                warehouseDistributionChart,
                inventoryCategoriesChart,
                projectGrowthChart
            };

            return {
                inventoryOverviewChart,
                warehouseDistributionChart,
                inventoryCategoriesChart,
                projectGrowthChart
            };
        };

        // Update charts for dark mode
        const updateChartsForDarkMode = (charts) => {
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
        const updateChartsForLightMode = (charts) => {
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
        charts = initCharts();

        // Initialize with correct theme
        if (localStorage.getItem("theme") === "dark") {
            updateChartsForDarkMode(charts);
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
                console.log('Category filter clicked:', this.dataset.category);
                
                // Remove active class from all buttons
                document.querySelectorAll('.category-filter').forEach(btn => {
                    btn.classList.remove('active', 'bg-blue-100', 'text-blue-800');
                    btn.classList.add('bg-gray-100', 'text-gray-800');
                });
                
                // Add active class to clicked button
                this.classList.add('active', 'bg-blue-100', 'text-blue-800');
                this.classList.remove('bg-gray-100', 'text-gray-800');
                
                const category = this.dataset.category;
                
                // Call the updateInventoryOverviewChart function directly
                updateInventoryOverviewChart(category, charts);
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
            
            // Xóa thông báo cũ nếu đang hiển thị
            clearTimeout(window.toastTimeout);
            
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
            
            // Lưu reference đến timeout để có thể xóa nếu cần
            window.toastTimeout = setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
            
            console.log(`Toast notification: [${type}] ${message}`);
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

        // Add this function to update statistics
        function updateStatistics() {
            fetch('/dashboard/statistics')
                .then(response => response.json())
                .then(data => {
                    // Update materials stats
                    document.querySelector('#materials-import').textContent = data.materials.total_import;
                    document.querySelector('#materials-export').textContent = data.materials.total_export;
                    document.querySelector('#materials-damaged').textContent = data.materials.total_damaged;

                    // Update products stats  
                    document.querySelector('#products-import').textContent = data.products.total_import;
                    document.querySelector('#products-export').textContent = data.products.total_export;
                    document.querySelector('#products-damaged').textContent = data.products.total_damaged;

                    // Update goods stats
                    document.querySelector('#goods-import').textContent = data.goods.total_import;
                    document.querySelector('#goods-export').textContent = data.goods.total_export;
                    document.querySelector('#goods-damaged').textContent = data.goods.total_damaged;
                })
                .catch(error => {
                    console.error('Error fetching statistics:', error);
                });
        }

        // Call updateStatistics initially and every 5 minutes
        updateStatistics();
        setInterval(updateStatistics, 300000);

        // Thêm hàm cập nhật biểu đồ tổng quan
        function updateInventoryOverviewChart(category = 'materials', charts) {
            console.log('updateInventoryOverviewChart called with category:', category);
            
            // Nếu charts không được truyền vào, sử dụng window.chartInstances
            if (!charts && window.chartInstances) {
                charts = window.chartInstances;
            }
            
            // Nếu không có charts, không làm gì cả
            if (!charts) {
                console.error('Charts not initialized');
                return;
            }
            
            // Hiển thị trạng thái loading
            const toast = document.getElementById("toast");
            toast.innerHTML = '<div class="toast bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Đang tải dữ liệu biểu đồ...</span></div>';
            toast.classList.remove("hidden");
            
            // Gọi API để lấy dữ liệu
            fetch(`/dashboard/inventory-overview-chart?category=${category}`)
                .then(response => {
                    console.log('API response received:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('API data received:', data);
                    
                    // Cập nhật biểu đồ
                    charts.inventoryOverviewChart.data.labels = data.labels;
                    charts.inventoryOverviewChart.data.datasets = data.datasets;
                    
                    // Cập nhật tiêu đề
                    let title = '';
                    switch (category) {
                        case 'materials':
                            title = 'Vật Tư';
                            break;
                        case 'products':
                            title = 'Thành Phẩm';
                            break;
                        case 'goods':
                            title = 'Hàng Hóa';
                            break;
                    }
                    
                    charts.inventoryOverviewChart.options.plugins.title.text = title;
                    charts.inventoryOverviewChart.update();
                    console.log('Chart updated successfully');
                    
                    // Hiển thị thông báo thành công
                    toast.innerHTML = '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu biểu đồ!</span></div>';
                    
                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                    toast.innerHTML = '<div class="toast bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-exclamation-circle mr-2"></i><span>Lỗi khi tải dữ liệu biểu đồ!</span></div>';
                    
                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                });
        }

        // Thêm hàm cập nhật biểu đồ phân loại kho
        function updateInventoryCategoriesChart(charts) {
            // Nếu charts không được truyền vào, sử dụng window.chartInstances
            if (!charts && window.chartInstances) {
                charts = window.chartInstances;
            }
            
            // Nếu không có charts, không làm gì cả
            if (!charts) {
                console.error('Charts not initialized');
                return;
            }
            
            fetch('/dashboard/inventory-categories-chart')
                .then(response => response.json())
                .then(data => {
                    // Cập nhật biểu đồ
                    charts.inventoryCategoriesChart.data.labels = data.labels;
                    charts.inventoryCategoriesChart.data.datasets[0].data = data.data;
                    charts.inventoryCategoriesChart.update();
                })
                .catch(error => {
                    console.error('Error fetching inventory categories data:', error);
                });
        }

        // Thêm hàm cập nhật biểu đồ phân bố theo kho
        function updateWarehouseDistributionChart(charts, filters = {}) {
            // Nếu charts không được truyền vào, sử dụng window.chartInstances
            if (!charts && window.chartInstances) {
                charts = window.chartInstances;
            }
            
            // Nếu không có charts, không làm gì cả
            if (!charts) {
                console.error('Charts not initialized');
                return;
            }
            
            // Xử lý các tham số lọc
            const itemType = filters.itemType || document.getElementById('warehouseChartItemType')?.value || '';
            
            // Hiển thị trạng thái loading
            const toast = document.getElementById("toast");
            toast.innerHTML = '<div class="toast bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Đang tải dữ liệu phân bố kho...</span></div>';
            toast.classList.remove("hidden");
            
            // Xây dựng query string từ các tham số lọc
            let queryParams = new URLSearchParams();
            if (itemType) {
                queryParams.append('item_type', itemType);
            }
            
            const url = `/dashboard/warehouse-distribution-chart?${queryParams.toString()}`;
            console.log('Fetching warehouse distribution data from:', url);
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log('Warehouse distribution data received:', data);
                    
                    // Cập nhật biểu đồ
                    charts.warehouseDistributionChart.data.labels = data.labels;
                    charts.warehouseDistributionChart.data.datasets[0].data = data.data;
                    charts.warehouseDistributionChart.data.datasets[0].backgroundColor = data.colors;
                    charts.warehouseDistributionChart.update();
                    
                    // Cập nhật legend bên dưới biểu đồ
                    const legendContainer = document.getElementById('warehouse-distribution-legend');
                    if (legendContainer) {
                        legendContainer.innerHTML = '';
                        
                        // Thêm tiêu đề
                        const titleElement = document.createElement('h4');
                        titleElement.className = 'text-sm font-medium text-gray-700 dark:text-gray-300 mb-2';
                        
                        let titleText = 'Phân bố theo kho';
                        if (itemType === 'material') {
                            titleText += ' - Vật tư';
                        } else if (itemType === 'product') {
                            titleText += ' - Thành phẩm';
                        } else if (itemType === 'good') {
                            titleText += ' - Hàng hóa';
                        }
                        
                        titleElement.textContent = titleText;
                        legendContainer.appendChild(titleElement);
                        
                        // Thêm thông tin tổng
                        if (data.total_quantity) {
                            const totalElement = document.createElement('div');
                            totalElement.className = 'text-sm text-gray-600 dark:text-gray-400 mb-3';
                            totalElement.textContent = `Tổng số lượng: ${data.total_quantity.toLocaleString()} đơn vị`;
                            legendContainer.appendChild(totalElement);
                        }
                        
                        // Thêm các mục trong legend
                        if (data.details && data.details.length > 0) {
                            data.details.forEach((detail, index) => {
                                const percent = data.data[index];
                                const color = data.colors[index];
                                const label = data.labels[index];
                                
                                const legendItem = document.createElement('div');
                                legendItem.className = 'flex items-center justify-between mb-2 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded';
                                
                                let detailsHtml = '';
                                if (detail.material_count > 0) {
                                    detailsHtml += `<span class="text-xs text-blue-500">Vật tư: ${detail.material_count}</span> `;
                                }
                                if (detail.product_count > 0) {
                                    detailsHtml += `<span class="text-xs text-green-500">Thành phẩm: ${detail.product_count}</span> `;
                                }
                                if (detail.good_count > 0) {
                                    detailsHtml += `<span class="text-xs text-orange-500">Hàng hóa: ${detail.good_count}</span>`;
                                }
                                
                                legendItem.innerHTML = `
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${color}"></div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-800 dark:text-white">${label}</span>
                                            <div class="mt-1">${detailsHtml}</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <span class="text-sm font-medium text-gray-800 dark:text-white">${percent}%</span>
                                        <span class="text-xs text-gray-500">${detail.total.toLocaleString()} đơn vị</span>
                                    </div>
                                `;
                                
                                legendContainer.appendChild(legendItem);
                            });
                        } else {
                            // Hiển thị mặc định nếu không có chi tiết
                            data.labels.forEach((label, index) => {
                                const percent = data.data[index];
                                const color = data.colors[index];
                                
                                const legendItem = document.createElement('div');
                                legendItem.className = 'flex items-center justify-between mb-2';
                                legendItem.innerHTML = `
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${color}"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">${label}</span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white">${percent}%</span>
                                `;
                                
                                legendContainer.appendChild(legendItem);
                            });
                        }
                    }
                    
                    // Hiển thị thông báo thành công
                    toast.innerHTML = '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu phân bố kho!</span></div>';
                    
                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error fetching warehouse distribution data:', error);
                    toast.innerHTML = '<div class="toast bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-exclamation-circle mr-2"></i><span>Lỗi khi tải dữ liệu phân bố kho!</span></div>';
                    
                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                });
        }

        // Thêm hàm cập nhật biểu đồ mức độ gia tăng dự án
        function updateProjectGrowthChart(charts) {
            // Nếu charts không được truyền vào, sử dụng window.chartInstances
            if (!charts && window.chartInstances) {
                charts = window.chartInstances;
            }
            
            // Nếu không có charts, không làm gì cả
            if (!charts) {
                console.error('Charts not initialized');
                return;
            }
            
            // Hiển thị trạng thái loading
            const toast = document.getElementById("toast");
            toast.innerHTML = '<div class="toast bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Đang tải dữ liệu biểu đồ dự án...</span></div>';
            toast.classList.remove("hidden");
            
            fetch('/dashboard/project-growth-chart')
                .then(response => {
                    console.log('Project growth API response received:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Project growth data received:', data);
                    
                    // Cập nhật biểu đồ
                    charts.projectGrowthChart.data.labels = data.labels;
                    charts.projectGrowthChart.data.datasets[0].data = data.data;
                    charts.projectGrowthChart.update();
                    console.log('Project growth chart updated successfully');
                    
                    // Hiển thị thông báo thành công
                    toast.innerHTML = '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu biểu đồ dự án!</span></div>';
                    
                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error fetching project growth data:', error);
                    toast.innerHTML = '<div class="toast bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-exclamation-circle mr-2"></i><span>Lỗi khi tải dữ liệu biểu đồ dự án!</span></div>';
                    
                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                });
        }

        // Sửa lại cách đăng ký event listener
        let chartsInitialized = false;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Nếu biểu đồ đã được khởi tạo và cập nhật, thì không làm gì cả
            if (chartsInitialized) {
                return;
            }
            
            // Chỉ khởi tạo biểu đồ một lần khi trang tải xong
            let charts = window.chartInstances;
            
            if (!charts) {
                charts = initCharts();
                
                // Initialize with correct theme
                if (localStorage.getItem("theme") === "dark") {
                    updateChartsForDarkMode(charts);
                }
    
                // Đăng ký event listener cho các nút category filter
                document.querySelectorAll('.category-filter').forEach(button => {
                    button.addEventListener('click', function() {
                        console.log('Category filter clicked:', this.dataset.category);
                        
                        // Remove active class from all buttons
                        document.querySelectorAll('.category-filter').forEach(btn => {
                            btn.classList.remove('active', 'bg-blue-100', 'text-blue-800');
                            btn.classList.add('bg-gray-100', 'text-gray-800');
                        });
                        
                        // Add active class to clicked button
                        this.classList.add('active', 'bg-blue-100', 'text-blue-800');
                        this.classList.remove('bg-gray-100', 'text-gray-800');
                        
                        const category = this.dataset.category;
                        updateInventoryOverviewChart(category, charts);
                    });
                });
                
                // Đăng ký event listener cho bộ lọc biểu đồ phân bố kho
                const warehouseChartItemType = document.getElementById('warehouseChartItemType');
                if (warehouseChartItemType) {
                    warehouseChartItemType.addEventListener('change', function() {
                        const itemType = this.value;
                        console.log('Warehouse chart filter changed:', itemType);
                        updateWarehouseDistributionChart(charts, { itemType });
                    });
                }
            }
    
            // Cập nhật tất cả biểu đồ khi trang tải xong
            updateStatistics();
            updateInventoryOverviewChart('materials', charts);
            updateInventoryCategoriesChart(charts);
            updateWarehouseDistributionChart(charts);
            updateProjectGrowthChart(charts);
            
            // Call updateStatistics every 5 minutes
            setInterval(updateStatistics, 300000);
            
            // Đánh dấu biểu đồ đã được khởi tạo và cập nhật
            chartsInitialized = true;
        });
        
        // Thêm code xử lý tìm kiếm nâng cao
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy các phần tử DOM
            const searchQuery = document.getElementById('searchQuery');
            const searchCategory = document.getElementById('searchCategory');
            const searchButton = document.getElementById('searchButton');
            const advancedFilterBtn = document.getElementById('advancedFilterBtn');
            const advancedFilterPanel = document.getElementById('advancedFilterPanel');
            const resetFiltersBtn = document.getElementById('resetFilters');
            const applyFiltersBtn = document.getElementById('applyFilters');
            const searchResults = document.getElementById('searchResults');
            const closeResults = document.getElementById('closeResults');
            const resultCount = document.getElementById('resultCount');
            const searchResultsList = document.getElementById('searchResultsList');
            const itemDetailsModal = document.getElementById('itemDetailsModal');
            const closeModal = document.getElementById('closeModal');

            // Biến để theo dõi trạng thái tìm kiếm
            let isSearching = false;
            
            // Xử lý sự kiện tìm kiếm
            function handleSearch() {
                // Nếu đang trong quá trình tìm kiếm, không thực hiện tìm kiếm mới
                if (isSearching) {
                    console.log('Search in progress, skipping...');
                    return;
                }
                
                // Đánh dấu đang tìm kiếm
                isSearching = true;
                
                // Thực hiện tìm kiếm
                performSearch().finally(() => {
                    // Đánh dấu đã hoàn thành tìm kiếm
                    isSearching = false;
                });
            }
            
            // Xử lý sự kiện tìm kiếm
            searchButton.addEventListener('click', handleSearch);
            
            // Xử lý sự kiện Enter trong ô tìm kiếm
            searchQuery.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    handleSearch();
                }
            });
            
            // Xử lý sự kiện áp dụng bộ lọc
            applyFiltersBtn.addEventListener('click', handleSearch);
            
            // Remove any existing event listeners
            const oldSearchButton = searchButton.cloneNode(true);
            searchButton.parentNode.replaceChild(oldSearchButton, searchButton);
            
            // Add the new event listener
            oldSearchButton.addEventListener('click', handleSearch);
            
            // Lấy danh sách kho để hiển thị trong bộ lọc
            fetch('/api/warehouses/search')
                .then(response => response.json())
                .then(data => {
                    const filterWarehouse = document.getElementById('filterWarehouse');
                    if (filterWarehouse) {
                        data.forEach(warehouse => {
                            const option = document.createElement('option');
                            option.value = warehouse.id;
                            option.textContent = warehouse.name;
                            filterWarehouse.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading warehouses:', error);
                });
            
            // Hiển thị/ẩn bộ lọc nâng cao
            advancedFilterBtn.addEventListener('click', function() {
                advancedFilterPanel.classList.toggle('hidden');
            });
            
            // Đặt lại bộ lọc
            resetFiltersBtn.addEventListener('click', function() {
                document.getElementById('filterWarehouse').value = '';
                document.getElementById('filterStatus').value = '';
                document.getElementById('filterDateRange').value = '';
                
                // Đặt lại các bộ lọc theo loại
                document.getElementById('filterMaterialType').value = '';
                document.getElementById('filterProductType').value = '';
                document.getElementById('filterProjectStatus').value = '';
                
                // Ẩn tất cả các bộ lọc theo loại
                document.getElementById('materialFilters').classList.add('hidden');
                document.getElementById('productFilters').classList.add('hidden');
                document.getElementById('projectFilters').classList.add('hidden');
            });
            
            // Hiển thị bộ lọc theo loại khi chọn loại tìm kiếm
            searchCategory.addEventListener('change', function() {
                const category = this.value;
                
                // Ẩn tất cả các bộ lọc theo loại
                document.getElementById('materialFilters').classList.add('hidden');
                document.getElementById('productFilters').classList.add('hidden');
                document.getElementById('projectFilters').classList.add('hidden');
                
                // Hiển thị bộ lọc tương ứng
                switch (category) {
                    case 'materials':
                        document.getElementById('materialFilters').classList.remove('hidden');
                        break;
                    case 'finished':
                        document.getElementById('productFilters').classList.remove('hidden');
                        break;
                    case 'projects':
                        document.getElementById('projectFilters').classList.remove('hidden');
                        break;
                }
            });
            
            // Xử lý tìm kiếm
            function performSearch() {
                return new Promise((resolve, reject) => {
                    const query = searchQuery.value.trim();
                    if (!query) {
                        showToast('warning', 'Vui lòng nhập từ khóa tìm kiếm');
                        resolve();
                        return;
                    }
                    
                    // Hiển thị trạng thái loading
                    showToast('info', 'Đang tìm kiếm...');
                    
                    // Thu thập các bộ lọc
                    const filters = {
                        warehouse_id: document.getElementById('filterWarehouse').value,
                        status: document.getElementById('filterStatus').value,
                        date_range: document.getElementById('filterDateRange').value,
                        include_out_of_stock: document.getElementById('includeOutOfStock').checked ? 'true' : 'false'
                    };
                    
                    // Thêm bộ lọc theo loại
                    const category = searchCategory.value;
                    switch (category) {
                        case 'materials':
                            filters.material_type = document.getElementById('filterMaterialType')?.value;
                            break;
                        case 'finished':
                            filters.product_type = document.getElementById('filterProductType')?.value;
                            break;
                        case 'projects':
                            filters.project_status = document.getElementById('filterProjectStatus')?.value;
                            break;
                    }
                    
                    // Xây dựng query string từ các bộ lọc
                    let queryParams = new URLSearchParams();
                    queryParams.append('query', query);
                    queryParams.append('category', category);
                    
                    // Thêm các bộ lọc vào query string
                    for (const [key, value] of Object.entries(filters)) {
                        if (value) {
                            queryParams.append(key, value);
                        }
                    }

                    console.log('Performing search with query:', query);
                    console.log('Search category:', category);
                    console.log('Search filters:', filters);
                    console.log('Search URL:', `/dashboard/search?${queryParams.toString()}`);
                    
                    // Ngăn chặn các request trùng lặp
                    if (window.lastSearchRequest) {
                        console.log('Aborting previous search request');
                        window.lastSearchRequest.abort();
                    }
                    
                    // Tạo controller để có thể hủy request
                    const controller = new AbortController();
                    window.lastSearchRequest = controller;
                    
                    // Gọi API tìm kiếm
                    fetch(`/dashboard/search?${queryParams.toString()}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            // Thêm timestamp để tránh cache
                            'Cache-Control': 'no-cache',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: controller.signal,
                        cache: 'no-store'
                    })
                    .then(response => {
                        console.log('Search response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Search response data:', data);
                        
                        // Xóa reference đến request hiện tại
                        window.lastSearchRequest = null;
                        
                        if (data.success) {
                            if (data.count > 0) {
                                // Hiển thị kết quả
                                displaySearchResults(data.results, data.count);
                                showToast('success', `Tìm thấy ${data.count} kết quả`);
                            } else {
                                // Không tìm thấy kết quả
                                searchResults.classList.add('hidden');
                                showToast('info', 'Không tìm thấy kết quả phù hợp');
                            }
                        } else {
                            console.error('Search error:', data.message);
                            searchResults.classList.add('hidden');
                            showToast('error', data.message || 'Có lỗi xảy ra khi tìm kiếm');
                        }
                        resolve();
                    })
                    .catch(error => {
                        // Không hiển thị lỗi nếu request bị hủy
                        if (error.name === 'AbortError') {
                            console.log('Search request aborted');
                            resolve();
                            return;
                        }
                        
                        console.error('Error searching:', error);
                        searchResults.classList.add('hidden');
                        showToast('error', 'Có lỗi xảy ra khi tìm kiếm: ' + error.message);
                        reject(error);
                    });
                });
            }
            
            // Hiển thị kết quả tìm kiếm
            function displaySearchResults(results, count) {
                // Cập nhật số lượng kết quả
                resultCount.textContent = count;
                
                // Xóa kết quả cũ
                searchResultsList.innerHTML = '';
                
                // Thêm kết quả mới
                results.forEach(result => {
                    const row = document.createElement('tr');
                    
                    // Mã
                    const codeCell = document.createElement('td');
                    codeCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                    codeCell.textContent = result.code;
                    
                    // Tên
                    const nameCell = document.createElement('td');
                    nameCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                    nameCell.textContent = result.name;
                    
                    // Loại
                    const categoryCell = document.createElement('td');
                    categoryCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                    
                    const categoryBadge = document.createElement('span');
                    categoryBadge.textContent = result.categoryName;
                    categoryBadge.className = 'px-2 py-1 rounded text-xs text-white font-medium';
                    
                    // Màu sắc theo loại
                    switch (result.category) {
                        case 'materials':
                            categoryBadge.classList.add('bg-blue-500');
                            break;
                        case 'finished':
                            categoryBadge.classList.add('bg-green-500');
                            break;
                        case 'goods':
                            categoryBadge.classList.add('bg-purple-500');
                            break;
                        case 'projects':
                            categoryBadge.classList.add('bg-yellow-500');
                            break;
                        case 'customers':
                            categoryBadge.classList.add('bg-red-500');
                            break;
                        default:
                            categoryBadge.classList.add('bg-gray-500');
                    }
                    
                    categoryCell.appendChild(categoryBadge);
                    
                    // Serial
                    const serialCell = document.createElement('td');
                    serialCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                    serialCell.textContent = result.serial;
                    
                    // Vị trí
                    const locationCell = document.createElement('td');
                    locationCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                    locationCell.textContent = result.location;
                    
                    // Thao tác
                    const actionCell = document.createElement('td');
                    actionCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                    
                    const viewButton = document.createElement('button');
                    viewButton.className = 'text-blue-500 hover:text-blue-700 mr-2';
                    viewButton.innerHTML = '<i class="fas fa-eye"></i>';
                    viewButton.addEventListener('click', function() {
                        showItemDetails(result);
                    });
                    
                    const detailLink = document.createElement('a');
                    detailLink.href = result.detailUrl;
                    detailLink.className = 'text-green-500 hover:text-green-700';
                    detailLink.innerHTML = '<i class="fas fa-external-link-alt"></i>';
                    
                    actionCell.appendChild(viewButton);
                    actionCell.appendChild(detailLink);
                    
                    // Thêm các ô vào hàng
                    row.appendChild(codeCell);
                    row.appendChild(nameCell);
                    row.appendChild(categoryCell);
                    row.appendChild(serialCell);
                    row.appendChild(locationCell);
                    row.appendChild(actionCell);
                    
                    // Thêm hàng vào bảng
                    searchResultsList.appendChild(row);
                });
                
                // Hiển thị kết quả
                searchResults.classList.remove('hidden');
            }
            
            // Hiển thị chi tiết item trong modal
            function showItemDetails(item) {
                // Cập nhật tiêu đề modal
                document.getElementById('modalTitle').textContent = `Chi tiết ${item.categoryName}`;
                
                // Cập nhật thông tin cơ bản
                document.getElementById('itemId').textContent = item.code;
                document.getElementById('itemName').textContent = item.name;
                document.getElementById('itemCategory').textContent = item.categoryName;
                document.getElementById('itemSerial').textContent = item.serial;
                document.getElementById('viewDetail').href = item.detailUrl;
                
                // Ẩn tất cả các phần thông tin chi tiết
                document.getElementById('materialsInfo').classList.add('hidden');
                document.getElementById('finishedInfo').classList.add('hidden');
                document.getElementById('goodsInfo').classList.add('hidden');
                document.getElementById('projectsInfo').classList.add('hidden');
                document.getElementById('customersInfo').classList.add('hidden');
                
                // Hiển thị phần thông tin chi tiết tương ứng
                switch (item.category) {
                    case 'materials':
                        const materialsInfo = document.getElementById('materialsInfo');
                        materialsInfo.classList.remove('hidden');
                        document.getElementById('materialSupplier').textContent = item.additionalInfo.supplier;
                        document.getElementById('materialQuantity').textContent = item.additionalInfo.quantity;
                        document.getElementById('materialUnit').textContent = item.additionalInfo.unit;
                        document.getElementById('itemDate').textContent = item.date;
                        document.getElementById('itemLocation').textContent = item.location;
                        break;
                        
                    case 'finished':
                        const finishedInfo = document.getElementById('finishedInfo');
                        finishedInfo.classList.remove('hidden');
                        document.getElementById('finishedManufactureDate').textContent = item.additionalInfo.manufactureDate;
                        document.getElementById('finishedQuantity').textContent = item.additionalInfo.quantity;
                        document.getElementById('finishedProject').textContent = item.additionalInfo.project;
                        document.getElementById('itemDate').textContent = item.date;
                        document.getElementById('itemLocation').textContent = item.location;
                        break;
                        
                    case 'goods':
                        const goodsInfo = document.getElementById('goodsInfo');
                        goodsInfo.classList.remove('hidden');
                        document.getElementById('goodsDistributor').textContent = item.additionalInfo.distributor;
                        document.getElementById('goodsPrice').textContent = item.additionalInfo.price;
                        document.getElementById('goodsQuantity').textContent = item.additionalInfo.quantity;
                        document.getElementById('itemDate').textContent = item.date;
                        document.getElementById('itemLocation').textContent = item.location;
                        break;
                        
                    case 'projects':
                        const projectsInfo = document.getElementById('projectsInfo');
                        projectsInfo.classList.remove('hidden');
                        document.getElementById('projectCustomer').textContent = item.additionalInfo.customer;
                        document.getElementById('projectStartDate').textContent = item.additionalInfo.startDate;
                        document.getElementById('projectEndDate').textContent = item.additionalInfo.endDate;
                        document.getElementById('itemLocation').textContent = item.location;
                        break;
                        
                    case 'customers':
                        const customersInfo = document.getElementById('customersInfo');
                        customersInfo.classList.remove('hidden');
                        document.getElementById('customerPhone').textContent = item.additionalInfo.phone;
                        document.getElementById('customerEmail').textContent = item.additionalInfo.email;
                        document.getElementById('customerAddress').textContent = item.additionalInfo.address;
                        
                        // Cập nhật bảng dự án liên quan
                        const customerProjectsList = document.getElementById('customerProjectsList');
                        customerProjectsList.innerHTML = '';
                        
                        if (item.additionalInfo.relatedProjects && item.additionalInfo.relatedProjects.length > 0) {
                            item.additionalInfo.relatedProjects.forEach(project => {
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
                                
                                // Màu sắc theo trạng thái
                                switch (project.status) {
                                    case 'Hoàn thành':
                                        statusBadge.classList.add('bg-green-500');
                                        break;
                                    case 'Đang thực hiện':
                                        statusBadge.classList.add('bg-blue-500');
                                        break;
                                    case 'Tạm dừng':
                                        statusBadge.classList.add('bg-orange-500');
                                        break;
                                    default:
                                        statusBadge.classList.add('bg-gray-500');
                                }
                                
                                statusCell.appendChild(statusBadge);
                                
                                row.appendChild(idCell);
                                row.appendChild(nameCell);
                                row.appendChild(dateCell);
                                row.appendChild(statusCell);
                                
                                customerProjectsList.appendChild(row);
                            });
                        } else {
                            const emptyRow = document.createElement('tr');
                            const emptyCell = document.createElement('td');
                            emptyCell.colSpan = 4;
                            emptyCell.className = 'py-4 px-4 text-center text-gray-500 dark:text-gray-400';
                            emptyCell.textContent = 'Không có dự án liên quan';
                            emptyRow.appendChild(emptyCell);
                            customerProjectsList.appendChild(emptyRow);
                        }
                        break;
                }
                
                // Hiển thị modal
                const itemDetailsModal = document.getElementById('itemDetailsModal');
                const closeModal = document.getElementById('closeModal');
                
                if (itemDetailsModal && closeModal) {
                    // Hiển thị modal
                    itemDetailsModal.classList.remove('hidden');
                    
                    // Xử lý sự kiện đóng modal
                    const closeModalHandler = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        itemDetailsModal.classList.add('hidden');
                        // Gỡ bỏ event listener sau khi đóng
                        closeModal.removeEventListener('click', closeModalHandler);
                    };
                    
                    // Thêm event listener cho nút đóng
                    closeModal.addEventListener('click', closeModalHandler);
                    
                    // Xử lý đóng modal khi click bên ngoài
                    const outsideClickHandler = function(e) {
                        if (e.target === itemDetailsModal) {
                            itemDetailsModal.classList.add('hidden');
                            // Gỡ bỏ event listener sau khi đóng
                            itemDetailsModal.removeEventListener('click', outsideClickHandler);
                        }
                    };
                    
                    // Thêm event listener cho click bên ngoài
                    itemDetailsModal.addEventListener('click', outsideClickHandler);
                }
            }
            
            // Đóng modal
            closeModal.addEventListener('click', function() {
                itemDetailsModal.classList.add('hidden');
            });
            
            // Đóng modal khi click bên ngoài
            itemDetailsModal.addEventListener('click', function(e) {
                if (e.target === itemDetailsModal) {
                    itemDetailsModal.classList.add('hidden');
                }
            });
            
            // Đóng kết quả tìm kiếm
            closeResults.addEventListener('click', function() {
                searchResults.classList.add('hidden');
            });
        });
        
        // Initialize all charts and store references
        // ... existing code ...
    </script>
</body>

</html>
