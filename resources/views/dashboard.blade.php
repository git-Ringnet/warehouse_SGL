<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SGL - Hệ thống quản lý kho</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/time-range-filter.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
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
                            <option value="day" selected>Theo ngày</option>
                            <option value="week">Theo tuần</option>
                            <option value="month">Theo tháng</option>
                            <option value="year">Theo năm</option>
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
                            <div class="flex items-center space-x-2">
                                <input type="text" id="dayStartDate" placeholder="DD/MM/YYYY"
                                    class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <span class="text-gray-500 dark:text-gray-400">đến</span>
                                <input type="text" id="dayEndDate" placeholder="DD/MM/YYYY"
                                    class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>

                        <!-- Week picker - for "Theo tuần" -->
                        <div id="weekInput" class="hidden">
                            <div class="flex items-center space-x-2">
                                <input type="text" id="weekStartDate" placeholder="DD/MM/YYYY"
                                    class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <span class="text-gray-500 dark:text-gray-400">đến</span>
                                <input type="text" id="weekEndDate" placeholder="DD/MM/YYYY"
                                    class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>

                        <!-- Month picker - for "Theo tháng" -->
                        <div id="monthInput" class="flex space-x-2">
                            <div class="flex items-center space-x-2">
                                <input type="text" id="monthStartDate" placeholder="DD/MM/YYYY"
                                    class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <span class="text-gray-500 dark:text-gray-400">đến</span>
                                <input type="text" id="monthEndDate" placeholder="DD/MM/YYYY"
                                    class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>

                        <!-- Year picker - for "Theo năm" -->
                        <div id="yearInput" class="hidden">
                            <div class="flex items-center space-x-2">
                                <input type="text" id="yearStartDate" placeholder="DD/MM/YYYY"
                                    class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <span class="text-gray-500 dark:text-gray-400">đến</span>
                                <input type="text" id="yearEndDate" placeholder="DD/MM/YYYY"
                                    class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
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
                            <option value="rentals">Phiếu cho thuê</option>
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
                            <label for="filterWarehouse"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kho</label>
                            <select id="filterWarehouse"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả kho</option>
                                <!-- Warehouses will be loaded dynamically -->
                            </select>
                        </div>

                        <!-- Filter by Status -->
                        <div>
                            <label for="filterStatus"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trạng
                                thái</label>
                            <select id="filterStatus"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả trạng thái</option>
                                <option value="active">Đang hoạt động</option>
                                <option value="inactive">Không hoạt động</option>
                                <option value="pending">Đang chờ xử lý</option>
                            </select>
                        </div>

                        <!-- Filter by Date Range -->
                        <div>
                            <label for="filterDateRange"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thời
                                gian</label>
                            <select id="filterDateRange"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả thời gian</option>
                                <option value="today">Hôm nay</option>
                                <option value="week">Tuần này</option>
                                <option value="month">Tháng này</option>
                                <option value="year">Năm nay</option>
                            </select>
                        </div>
                    </div>

                    <!-- Exclude Out of Stock Checkbox -->
                    <div class="mt-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="excludeOutOfStock"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="excludeOutOfStock"
                                class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Chỉ hiển thị sản phẩm có tồn kho > 0
                            </label>
                        </div>
                    </div>

                    <!-- Category-specific filters -->
                    <div id="materialFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
                        <div>
                            <label for="filterMaterialType"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Loại vật
                                tư</label>
                            <select id="filterMaterialType"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả loại</option>
                                <option value="electronic">Điện tử</option>
                                <option value="mechanical">Cơ khí</option>
                                <option value="chemical">Hóa chất</option>
                            </select>
                        </div>
                    </div>

                    <div id="productFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
                        <div>
                            <label for="filterProductType"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Loại thành
                                phẩm</label>
                            <select id="filterProductType"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả loại</option>
                                <option value="device">Thiết bị</option>
                                <option value="component">Linh kiện</option>
                                <option value="system">Hệ thống</option>
                            </select>
                        </div>
                    </div>

                    <div id="projectFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
                        <div>
                            <label for="filterProjectStatus"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trạng thái dự
                                án</label>
                            <select id="filterProjectStatus"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả trạng thái</option>
                                <option value="planning">Đang lập kế hoạch</option>
                                <option value="in_progress">Đang thực hiện</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="on_hold">Tạm dừng</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                    </div>

                    <div id="rentalFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 hidden">
                        <div>
                            <label for="filterRentalStatus"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trạng thái
                                phiếu thuê</label>
                            <select id="filterRentalStatus"
                                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả trạng thái</option>
                                <option value="active">Đang hoạt động</option>
                                <option value="overdue">Quá hạn</option>
                                <option value="completed">Đã hoàn thành</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button id="resetFilters"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors mr-2">
                            Đặt lại
                        </button>
                        <button id="applyFilters"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            Áp dụng
                        </button>
                    </div>
                </div>

                <!-- Search Results -->
                <div id="searchResults" class="mt-4 hidden">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold text-gray-800 dark:text-white" id="resultTitle">Kết quả tìm kiếm
                            </h4>
                            <button id="closeResults"
                                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Search Results Count -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Tìm thấy </span>
                                    <span class="text-lg font-semibold text-gray-800 dark:text-white"
                                        id="resultCount">0</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400"> kết quả</span>
                                </div>
                                <div>
                                    <button id="viewAllResults"
                                        class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
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
                                        <th
                                            class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Mã</th>
                                        <th
                                            class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tên</th>
                                        <th
                                            class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300 w-[15%]">
                                            Loại</th>
                                        <th
                                            class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Số lượng</th>
                                        <th
                                            class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Vị trí</th>
                                        <th
                                            class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Thao tác</th>
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
                        <div
                            class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-500 dark:text-blue-300 mr-4">
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
                        <div
                            class="p-3 rounded-full bg-green-100 dark:bg-green-900 text-green-500 dark:text-green-300 mr-4">
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
                        <div
                            class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-500 dark:text-purple-300 mr-4">
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
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 lg:col-span-2 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Tổng quan nhập/xuất/hư hỏng
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <button data-category="materials"
                                    class="category-filter px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800 active">Vật
                                    tư</button>
                            </div>
                            <div class="flex items-center">
                                <button data-category="products"
                                    class="category-filter px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Thành
                                    phẩm</button>
                            </div>
                            <div class="flex items-center">
                                <button data-category="goods"
                                    class="category-filter px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Hàng
                                    hóa</button>
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
                            <select id="warehouseChartItemType"
                                class="text-sm bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-1 px-2 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                onchange="window.handleWarehouseItemTypeChange && window.handleWarehouseItemTypeChange(this.value)">
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
    <div id="itemDetailsModal"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div
                class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center sticky top-0 bg-white dark:bg-gray-800 z-10">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white" id="modalTitle">Chi tiết</h3>
                <button id="closeModal"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
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
                                <span class="text-lg font-semibold text-gray-800 dark:text-white ml-2"
                                    id="itemId"></span>
                            </div>
                            <div class="mb-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Tên:</span>
                                <span class="text-lg font-semibold text-gray-800 dark:text-white ml-2"
                                    id="itemName"></span>
                            </div>
                            <div class="mb-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Loại:</span>
                                <span class="text-lg font-semibold text-gray-800 dark:text-white ml-2"
                                    id="itemCategory"></span>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Serial:</span>
                                <span class="text-lg font-semibold text-gray-800 dark:text-white ml-2"
                                    id="itemSerial"></span>
                            </div>
                            <div>
                                <a href="#" id="viewDetail"
                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
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
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="materialSupplier"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Ngày nhập</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="itemDate"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Số lượng hiện có
                                    </td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="materialQuantity"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Đơn vị</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="materialUnit"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Vị trí</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="itemLocation"></td>
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
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="finishedManufactureDate"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Ngày nhập kho</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="itemDate"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Số lượng hiện có
                                    </td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="finishedQuantity"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Thuộc dự án</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="finishedProject"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Vị trí</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="itemLocation"></td>
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
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="goodsDistributor"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Ngày nhập</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="itemDate"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Số lượng</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="goodsQuantity"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Vị trí</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="itemLocation"></td>
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
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="projectCustomer"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Địa điểm</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="itemLocation"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Ngày bắt đầu</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="projectStartDate"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Hạn hoàn thành</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="projectEndDate"></td>
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
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="customerPhone"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Email</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="customerEmail"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b text-gray-600 dark:text-gray-400">Địa chỉ</td>
                                    <td class="py-2 px-4 border-b text-gray-800 dark:text-white font-medium"
                                        id="customerAddress"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Related Projects Table for Customer -->
                    <h5 class="font-semibold text-gray-800 dark:text-white mt-4 mb-3">Các dự án liên quan</h5>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden"
                            id="customerProjectsTable">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Mã dự án</th>
                                    <th
                                        class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tên dự án</th>
                                    <th
                                        class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Ngày bắt đầu</th>
                                    <th
                                        class="py-2 px-4 border-b text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Trạng thái</th>
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
        document.addEventListener("DOMContentLoaded", function() {

            // Update current time
            updateCurrentTime();
            setInterval(updateCurrentTime, 1000);

            // Initialize charts
            if (typeof initCharts === 'function') {
                window.chartInstances = initCharts();
            }

            // Thêm event listener cho các trường ngày để tự động cập nhật biểu đồ và thống kê
            const dateInputs = document.querySelectorAll(
                '#dayStartDate, #dayEndDate, #weekStartDate, #weekEndDate, #monthStartDate, #monthEndDate, #yearStartDate, #yearEndDate'
                );
            dateInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Tự động cập nhật thống kê và biểu đồ khi thay đổi ngày
                    setTimeout(() => {
                        // Cập nhật thống kê trước
                        updateStatistics();

                        // Sau đó cập nhật biểu đồ
                        if (window.chartInstances && window.chartInstances
                            .inventoryOverviewChart) {
                            updateInventoryOverviewChart('materials', window
                                .chartInstances);
                        }
                    }, 100);
                });
            });

            // Thêm event listener cho advanced filter button nếu tồn tại
            const advancedFilterBtn = document.getElementById('advancedFilterBtn');
            const advancedFilterPanel = document.getElementById('advancedFilterPanel');
            if (advancedFilterBtn && advancedFilterPanel) {
                advancedFilterBtn.addEventListener('click', function() {
                    advancedFilterPanel.classList.toggle('hidden');
                });
            }
        });

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
                    labels: [],
                    datasets: [{
                            label: "Nhập kho",
                            data: [],
                            backgroundColor: "#10b981",
                            borderRadius: 4,
                        },
                        {
                            label: "Xuất kho",
                            data: [],
                            backgroundColor: "#ef4444",
                            borderRadius: 4,
                        },
                        {
                            label: "Hư hỏng",
                            data: [],
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
                    labels: [],
                    datasets: [{
                        label: "Số lượng dự án",
                        data: [],
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
                    labels: [],
                    datasets: [{
                        data: [],
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
                                    const val = Number(context.raw);
                                    const formatted = Number.isFinite(val) ? val.toFixed(val >= 1 ? 1 : 2).replace(/\.0+$/, '') : context.raw;
                                    return `${context.label}: ${formatted}%`;
                                },
                            },
                        },
                        datalabels: {
                            formatter: (value) => {
                                const val = Number(value);
                                const formatted = Number.isFinite(val) ? val.toFixed(val >= 1 ? 1 : 2).replace(/\.0+$/, '') : value;
                                return `${formatted}%`;
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
                    labels: [],
                    datasets: [{
                        data: [],
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

            // Show the selected input
            switch (selectedValue) {
                case 'day':
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
            }

            // Update statistics and charts based on new time range
            setTimeout(() => {
                // Update statistics first
                updateStatistics();

                // Update all charts if available
                if (window.chartInstances) {
                    const timeParams = getTimeRangeParams();
                    updateInventoryOverviewChart('materials', window.chartInstances, timeParams);
                    updateInventoryCategoriesChart(window.chartInstances, timeParams);
                    updateWarehouseDistributionChart(window.chartInstances, timeParams);
                    updateProjectGrowthChart(window.chartInstances, timeParams);
                }

                toast.innerHTML =
                    '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu thành công!</span></div>';

                setTimeout(() => {
                    toast.classList.add("hidden");
                }, 2000);
            }, 500);
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
                        relatedProjects: [{
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


            // Show the selected input
            switch (selectedValue) {
                case 'day':
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

            }

            // Set default values to current date
            const now = new Date();
            const today = now.toISOString().split('T')[0]; // YYYY-MM-DD format for calculations

            // Set default values for all date inputs
            setDefaultDateRanges(today);

            // Update statistics with initial date range
            setTimeout(() => {
                updateStatistics();
            }, 100);

            // Add event listener for time range selection change
            timeRangeSelect.addEventListener('change', function() {
                const selectedValue = this.value;

                // Hide all inputs first
                document.getElementById('dayInput').classList.add('hidden');
                document.getElementById('weekInput').classList.add('hidden');
                document.getElementById('monthInput').classList.add('hidden');
                document.getElementById('yearInput').classList.add('hidden');

                // Show the selected input
                switch (selectedValue) {
                    case 'day':
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
                }

                // Update date ranges based on selection
                setDefaultDateRanges(today);

                // Tự động cập nhật tất cả biểu đồ khi thay đổi loại thời gian
                setTimeout(() => {
                    if (window.chartInstances) {
                        const timeParams = getTimeRangeParams();
                        updateInventoryOverviewChart('materials', window.chartInstances,
                        timeParams);
                        updateInventoryCategoriesChart(window.chartInstances, timeParams);
                        updateWarehouseDistributionChart(window.chartInstances, timeParams);
                        updateProjectGrowthChart(window.chartInstances, timeParams);
                    }
                }, 100);
            });
        });

        // Function to get time range parameters from form
        function getTimeRangeParams() {
            const timeRangeSelect = document.getElementById("timeRangeSelect");
            const timeRangeType = timeRangeSelect.value;

            let startDate = '';
            let endDate = '';

            switch (timeRangeType) {
                case 'day':
                    startDate = document.getElementById('dayStartDate').value;
                    endDate = document.getElementById('dayEndDate').value;
                    break;
                case 'week':
                    startDate = document.getElementById('weekStartDate').value;
                    endDate = document.getElementById('weekEndDate').value;
                    break;
                case 'month':
                    startDate = document.getElementById('monthStartDate').value;
                    endDate = document.getElementById('monthEndDate').value;
                    break;
                case 'year':
                    startDate = document.getElementById('yearStartDate').value;
                    endDate = document.getElementById('yearEndDate').value;
                    break;
            }

            return {
                time_range_type: timeRangeType,
                start_date: startDate,
                end_date: endDate
            };
        }

        // Function to set default date ranges based on time range type
        function setDefaultDateRanges(today) {
            const timeRangeSelect = document.getElementById("timeRangeSelect");
            const selectedValue = timeRangeSelect.value;

            // Helper function to format date as dd/mm/yyyy
            const formatDateForDisplay = (dateString) => {
                const date = new Date(dateString);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            };

            switch (selectedValue) {
                case 'day':
                    // For day range, set both start and end to today
                    document.getElementById('dayStartDate').value = formatDateForDisplay(today);
                    document.getElementById('dayEndDate').value = formatDateForDisplay(today);
                    break;

                case 'week':
                    // For week range, set to current week
                    const weekStart = getWeekStart(today);
                    const weekEnd = getWeekEnd(today);
                    document.getElementById('weekStartDate').value = formatDateForDisplay(weekStart);
                    document.getElementById('weekEndDate').value = formatDateForDisplay(weekEnd);
                    break;

                case 'month':
                    // For month range, set to current month
                    const monthStart = getMonthStart(today);
                    const monthEnd = getMonthEnd(today);
                    document.getElementById('monthStartDate').value = formatDateForDisplay(monthStart);
                    document.getElementById('monthEndDate').value = formatDateForDisplay(monthEnd);
                    break;

                case 'year':
                    // For year range, set to current year
                    const yearStart = getYearStart(today);
                    const yearEnd = getYearEnd(today);
                    document.getElementById('yearStartDate').value = formatDateForDisplay(yearStart);
                    document.getElementById('yearEndDate').value = formatDateForDisplay(yearEnd);
                    break;
            }
        }

        // Helper functions to calculate date ranges
        function getWeekStart(dateString) {
            const date = new Date(dateString);
            const day = date.getDay();
            const diff = date.getDate() - day + (day === 0 ? -6 : 1); // Adjust when day is Sunday
            const monday = new Date(date.setDate(diff));
            return monday.toISOString().split('T')[0];
        }

        function getWeekEnd(dateString) {
            const date = new Date(dateString);
            const day = date.getDay();
            const diff = date.getDate() - day + (day === 0 ? 0 : 7); // Adjust when day is Sunday
            const sunday = new Date(date.setDate(diff));
            return sunday.toISOString().split('T')[0];
        }

        function getMonthStart(dateString) {
            const date = new Date(dateString);
            return new Date(date.getFullYear(), date.getMonth(), 1).toISOString().split('T')[0];
        }

        function getMonthEnd(dateString) {
            const date = new Date(dateString);
            return new Date(date.getFullYear(), date.getMonth() + 1, 0).toISOString().split('T')[0];
        }

        function getYearStart(dateString) {
            const date = new Date(dateString);
            return new Date(date.getFullYear(), 0, 1).toISOString().split('T')[0];
        }

        function getYearEnd(dateString) {
            const date = new Date(dateString);
            return new Date(date.getFullYear(), 11, 31).toISOString().split('T')[0];
        }

        // Function to get current date range based on selection
        function getCurrentDateRange() {
            const timeRangeSelect = document.getElementById("timeRangeSelect");
            const selectedValue = timeRangeSelect.value;

            // Helper function to convert dd/mm/yyyy to YYYY-MM-DD for API calls
            const convertToApiFormat = (dateString) => {
                if (!dateString) return '';
                const parts = dateString.split('/');
                if (parts.length === 3) {
                    const day = parts[0];
                    const month = parts[1];
                    const year = parts[2];
                    return `${year}-${month}-${day}`;
                }
                return dateString; // Return as is if not in expected format
            };

            switch (selectedValue) {
                case 'day':
                    return {
                        start: convertToApiFormat(document.getElementById('dayStartDate').value),
                            end: convertToApiFormat(document.getElementById('dayEndDate').value)
                    };
                case 'week':
                    return {
                        start: convertToApiFormat(document.getElementById('weekStartDate').value),
                            end: convertToApiFormat(document.getElementById('weekEndDate').value)
                    };
                case 'month':
                    return {
                        start: convertToApiFormat(document.getElementById('monthStartDate').value),
                            end: convertToApiFormat(document.getElementById('monthEndDate').value)
                    };
                case 'year':
                    return {
                        start: convertToApiFormat(document.getElementById('yearStartDate').value),
                            end: convertToApiFormat(document.getElementById('yearEndDate').value)
                    };

                default:
                    return {
                        start: convertToApiFormat(document.getElementById('monthStartDate').value),
                            end: convertToApiFormat(document.getElementById('monthEndDate').value)
                    };
            }
        }

        // Add this function to update statistics
        function updateStatistics() {
            // Lấy thông tin thời gian từ bộ tìm kiếm
            const timeRangeSelect = document.getElementById("timeRangeSelect");
            const timeRangeType = timeRangeSelect ? timeRangeSelect.value : 'month';

            let startDate = '';
            let endDate = '';

            // Helper function to convert dd/mm/yyyy to YYYY-MM-DD for API calls
            const convertToApiFormat = (dateString) => {
                if (!dateString) return '';
                const parts = dateString.split('/');
                if (parts.length === 3) {
                    const day = parts[0];
                    const month = parts[1];
                    const year = parts[2];
                    return `${year}-${month}-${day}`;
                }
                return dateString; // Return as is if not in expected format
            };

            // Lấy ngày bắt đầu và kết thúc dựa trên loại tìm kiếm
            switch (timeRangeType) {
                case 'day':
                    startDate = convertToApiFormat(document.getElementById('dayStartDate')?.value || '');
                    endDate = convertToApiFormat(document.getElementById('dayEndDate')?.value || '');
                    break;
                case 'week':
                    startDate = convertToApiFormat(document.getElementById('weekStartDate')?.value || '');
                    endDate = convertToApiFormat(document.getElementById('weekEndDate')?.value || '');
                    break;
                case 'month':
                    startDate = convertToApiFormat(document.getElementById('monthStartDate')?.value || '');
                    endDate = convertToApiFormat(document.getElementById('monthEndDate')?.value || '');
                    break;
                case 'year':
                    startDate = convertToApiFormat(document.getElementById('yearStartDate')?.value || '');
                    endDate = convertToApiFormat(document.getElementById('yearEndDate')?.value || '');
                    break;
            }

            // Tạo URL với tham số
            const params = new URLSearchParams({
                time_range_type: timeRangeType
            });

            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);

            fetch(`/dashboard/statistics?${params.toString()}`)
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

        // Hàm kiểm tra và hiển thị thông báo không có dữ liệu
        function showNoDataMessage(chartElement, message = 'Không có dữ liệu') {
            // Tạo một div để hiển thị thông báo thay vì vẽ lên canvas
            const existingMessage = chartElement.querySelector('.no-data-message');
            if (existingMessage) {
                existingMessage.remove();
            }

            const messageDiv = document.createElement('div');
            messageDiv.className =
                'no-data-message absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded-lg';
            messageDiv.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">${message}</p>
                </div>
            `;

            // Thêm position relative cho container nếu chưa có
            if (getComputedStyle(chartElement).position === 'static') {
                chartElement.style.position = 'relative';
            }

            chartElement.appendChild(messageDiv);
        }

        // Hàm xóa thông báo không có dữ liệu
        function removeNoDataMessage(chartElement) {
            const existingMessage = chartElement.querySelector('.no-data-message');
            if (existingMessage) {
                existingMessage.remove();
            }
        }

        // Thêm hàm cập nhật biểu đồ tổng quan
        function updateInventoryOverviewChart(category = 'materials', charts) {
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
            toast.innerHTML =
                '<div class="toast bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Đang tải dữ liệu biểu đồ...</span></div>';
            toast.classList.remove("hidden");

            // Lấy thông tin thời gian từ bộ tìm kiếm
            const timeRangeSelect = document.getElementById("timeRangeSelect");
            const timeRangeType = timeRangeSelect ? timeRangeSelect.value : 'month';

            let startDate = '';
            let endDate = '';

            // Helper function to convert dd/mm/yyyy to YYYY-MM-DD for API calls
            const convertToApiFormat = (dateString) => {
                if (!dateString) return '';
                const parts = dateString.split('/');
                if (parts.length === 3) {
                    const day = parts[0];
                    const month = parts[1];
                    const year = parts[2];
                    return `${year}-${month}-${day}`;
                }
                return dateString; // Return as is if not in expected format
            };

            // Lấy ngày bắt đầu và kết thúc dựa trên loại tìm kiếm
            switch (timeRangeType) {
                case 'day':
                    startDate = convertToApiFormat(document.getElementById('dayStartDate')?.value || '');
                    endDate = convertToApiFormat(document.getElementById('dayEndDate')?.value || '');
                    break;
                case 'week':
                    startDate = convertToApiFormat(document.getElementById('weekStartDate')?.value || '');
                    endDate = convertToApiFormat(document.getElementById('weekEndDate')?.value || '');
                    break;
                case 'month':
                    startDate = convertToApiFormat(document.getElementById('monthStartDate')?.value || '');
                    endDate = convertToApiFormat(document.getElementById('monthEndDate')?.value || '');
                    break;
                case 'year':
                    startDate = convertToApiFormat(document.getElementById('yearStartDate')?.value || '');
                    endDate = convertToApiFormat(document.getElementById('yearEndDate')?.value || '');
                    break;
            }

            // Tạo URL với tham số
            const params = new URLSearchParams({
                category: category,
                time_range_type: timeRangeType
            });

            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);

            // Test: Hiển thị trạng thái "không có dữ liệu" thay vì gọi API
            // Để test hiển thị "không có dữ liệu", comment dòng dưới và uncomment phần API call
            const testNoData = false; // Set thành false để gọi API thực

            if (testNoData) {
                // Hiển thị thông báo không có dữ liệu
                const chartContainer = document.querySelector('.chart-container-lg');
                if (chartContainer) {
                    showNoDataMessage(chartContainer, 'Không có dữ liệu cho khoảng thời gian này');
                }
                toast.innerHTML =
                    '<div class="toast bg-yellow-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-info-circle mr-2"></i><span>Không có dữ liệu cho khoảng thời gian này</span></div>';
                setTimeout(() => {
                    toast.classList.add("hidden");
                }, 3000);
                return;
            }

            // Gọi API để lấy dữ liệu (uncomment để sử dụng API thực)
            fetch(`/dashboard/inventory-overview-chart?${params.toString()}`)
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    // Kiểm tra xem có dữ liệu không
                    const hasData = data.labels && data.labels.length > 0 &&
                        data.datasets && data.datasets.length > 0 &&
                        data.datasets.some(dataset => dataset.data && dataset.data.length > 0 &&
                            dataset.data.some(value => value > 0));

                    if (!hasData) {
                        // Hiển thị thông báo không có dữ liệu
                        const chartContainer = document.querySelector('.chart-container-lg');
                        if (chartContainer) {
                            showNoDataMessage(chartContainer, 'Không có dữ liệu cho khoảng thời gian này');
                        }
                        toast.innerHTML =
                            '<div class="toast bg-yellow-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-info-circle mr-2"></i><span>Không có dữ liệu cho khoảng thời gian này</span></div>';
                        setTimeout(() => {
                            toast.classList.add("hidden");
                        }, 3000);
                        return;
                    }

                    // Xóa thông báo không có dữ liệu nếu có
                    const chartContainer = document.querySelector('.chart-container-lg');
                    if (chartContainer) {
                        removeNoDataMessage(chartContainer);
                    }

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

                    // Thêm thông tin thời gian vào tiêu đề
                    const timeRangeLabels = {
                        'day': 'Theo ngày',
                        'week': 'Theo tuần',
                        'month': 'Theo tháng',
                        'year': 'Theo năm'
                    };
                    title += ` - ${timeRangeLabels[timeRangeType] || 'Theo tháng'}`;

                    charts.inventoryOverviewChart.options.plugins.title.text = title;
                    charts.inventoryOverviewChart.update();

                    // Hiển thị thông báo thành công
                    toast.innerHTML =
                        '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu biểu đồ!</span></div>';

                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                    toast.innerHTML =
                        '<div class="toast bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-exclamation-circle mr-2"></i><span>Lỗi khi tải dữ liệu biểu đồ!</span></div>';

                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                });
        }

        // Thêm hàm cập nhật biểu đồ phân loại kho
        function updateInventoryCategoriesChart(charts, timeParams = {}) {
            // Nếu charts không được truyền vào, sử dụng window.chartInstances
            if (!charts && window.chartInstances) {
                charts = window.chartInstances;
            }

            // Nếu không có charts, không làm gì cả
            if (!charts) {
                console.error('Charts not initialized for inventory categories');
                return;
            }

            // Kiểm tra xem chart có tồn tại không
            if (!charts.inventoryCategoriesChart) {
                console.error('inventoryCategoriesChart not found in charts object');
                return;
            }


            // Tạo URL với tham số thời gian
            const url = new URL('/dashboard/inventory-categories-chart', window.location.origin);
            if (timeParams.start_date) url.searchParams.append('start_date', timeParams.start_date);
            if (timeParams.end_date) url.searchParams.append('end_date', timeParams.end_date);
            if (timeParams.time_range_type) url.searchParams.append('time_range_type', timeParams.time_range_type);

            // Gọi API để lấy dữ liệu
            fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {

                    // Kiểm tra xem có dữ liệu không
                    const hasData = data.labels && data.labels.length > 0 &&
                        data.data && data.data.length > 0 &&
                        data.data.some(value => value > 0);


                    if (!hasData) {
                        // Hiển thị thông báo không có dữ liệu
                        const chartContainer = document.querySelector('.chart-container');
                        if (chartContainer) {
                            showNoDataMessage(chartContainer, 'Không có dữ liệu phân loại');
                        }
                        return;
                    }

                    // Xóa thông báo không có dữ liệu nếu có
                    const chartContainer = document.querySelector('.chart-container');
                    if (chartContainer) {
                        removeNoDataMessage(chartContainer);
                    }


                    // Cập nhật biểu đồ
                    charts.inventoryCategoriesChart.data.labels = data.labels;
                    charts.inventoryCategoriesChart.data.datasets[0].data = data.data;
                    charts.inventoryCategoriesChart.update();

                })
                .catch(error => {
                    console.error('Error fetching inventory categories data:', error);

                    // Hiển thị thông báo lỗi
                    const chartContainer = document.querySelector('.chart-container');
                    if (chartContainer) {
                        showNoDataMessage(chartContainer, 'Lỗi khi tải dữ liệu biểu đồ');
                    }
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
            toast.innerHTML =
                '<div class="toast bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Đang tải dữ liệu phân bố kho...</span></div>';
            toast.classList.remove("hidden");

            // Xây dựng query string từ các tham số lọc
            let queryParams = new URLSearchParams();
            if (itemType) {
                queryParams.append('item_type', itemType);
            }

            // Test: Hiển thị trạng thái "không có dữ liệu" thay vì gọi API
            const testNoData = false; // Set thành false để gọi API thực

            if (testNoData) {
                // Hiển thị thông báo không có dữ liệu
                const chartContainer = document.querySelector('.chart-container');
                if (chartContainer) {
                    showNoDataMessage(chartContainer, 'Không có dữ liệu phân bố kho');
                }
                toast.innerHTML =
                    '<div class="toast bg-yellow-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-info-circle mr-2"></i><span>Không có dữ liệu phân bố kho</span></div>';
                setTimeout(() => {
                    toast.classList.add("hidden");
                }, 3000);
                return;
            }

            // Gọi API để lấy dữ liệu (uncomment để sử dụng API thực)
            const url = `/dashboard/warehouse-distribution-chart?${queryParams.toString()}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {

                    // Kiểm tra xem có dữ liệu không
                    const hasData = data.labels && data.labels.length > 0 &&
                        data.data && data.data.length > 0 &&
                        data.data.some(value => value > 0);

                    if (!hasData) {
                        // Hiển thị thông báo không có dữ liệu
                        const chartContainer = document.querySelector('.chart-container');
                        if (chartContainer) {
                            showNoDataMessage(chartContainer, 'Không có dữ liệu phân bố kho');
                        }
                        toast.innerHTML =
                            '<div class="toast bg-yellow-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-info-circle mr-2"></i><span>Không có dữ liệu phân bố kho</span></div>';
                        setTimeout(() => {
                            toast.classList.add("hidden");
                        }, 3000);
                        return;
                    }

                    // Xóa thông báo không có dữ liệu nếu có
                    const chartContainer = document.querySelector('.chart-container');
                    if (chartContainer) {
                        removeNoDataMessage(chartContainer);
                    }

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
                                legendItem.className =
                                    'flex items-center justify-between mb-2 p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded';

                                let detailsHtml = '';
                                if (itemType === 'material' && detail.material_count > 0) {
                                    detailsHtml +=
                                        `<span class="text-xs text-blue-500">Vật tư: ${detail.material_count}</span> `;
                                } else if (itemType === 'product' && detail.product_count > 0) {
                                    detailsHtml +=
                                        `<span class="text-xs text-green-500">Thành phẩm: ${detail.product_count}</span> `;
                                } else if (itemType === 'good' && detail.good_count > 0) {
                                    detailsHtml +=
                                        `<span class="text-xs text-orange-500">Hàng hóa: ${detail.good_count}</span>`;
                                } else if (!itemType) {
                                    // Khi không có filter, hiển thị tất cả
                                    if (detail.material_count > 0) {
                                        detailsHtml +=
                                            `<span class="text-xs text-blue-500">Vật tư: ${detail.material_count}</span> `;
                                    }
                                    if (detail.product_count > 0) {
                                        detailsHtml +=
                                            `<span class="text-xs text-green-500">Thành phẩm: ${detail.product_count}</span> `;
                                    }
                                    if (detail.good_count > 0) {
                                        detailsHtml +=
                                            `<span class="text-xs text-orange-500">Hàng hóa: ${detail.good_count}</span>`;
                                    }
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
                                        <span class="text-sm font-medium text-gray-800 dark:text-white">${Number.isFinite(Number(percent)) ? Number(percent).toFixed(Number(percent) >= 1 ? 1 : 2).replace(/\.0+$/, '') : percent}%</span>
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
                                    <span class="text-sm font-medium text-gray-800 dark:text-white">${Number.isFinite(Number(percent)) ? Number(percent).toFixed(Number(percent) >= 1 ? 1 : 2).replace(/\.0+$/, '') : percent}%</span>
                                `;

                                legendContainer.appendChild(legendItem);
                            });
                        }
                    }

                    // Hiển thị thông báo thành công
                    toast.innerHTML =
                        '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu phân bố kho!</span></div>';

                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error fetching warehouse distribution data:', error);
                    toast.innerHTML =
                        '<div class="toast bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-exclamation-circle mr-2"></i><span>Lỗi khi tải dữ liệu phân bố kho!</span></div>';

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
            toast.innerHTML =
                '<div class="toast bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Đang tải dữ liệu biểu đồ dự án...</span></div>';
            toast.classList.remove("hidden");

            // Test: Hiển thị trạng thái "không có dữ liệu" thay vì gọi API
            const testNoData = false; // Set thành false để gọi API thực

            if (testNoData) {
                // Hiển thị thông báo không có dữ liệu
                const chartContainer = document.querySelector('.chart-container');
                if (chartContainer) {
                    showNoDataMessage(chartContainer, 'Không có dữ liệu dự án');
                }
                toast.innerHTML =
                    '<div class="toast bg-yellow-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-info-circle mr-2"></i><span>Không có dữ liệu dự án</span></div>';
                setTimeout(() => {
                    toast.classList.add("hidden");
                }, 3000);
                return;
            }

            // Gọi API để lấy dữ liệu (uncomment để sử dụng API thực)
            fetch('/dashboard/project-growth-chart')
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    // Kiểm tra xem có dữ liệu không
                    const hasData = data.labels && data.labels.length > 0 &&
                        data.data && data.data.length > 0 &&
                        data.data.some(value => value > 0);

                    if (!hasData) {
                        // Hiển thị thông báo không có dữ liệu
                        const chartContainer = document.querySelector('.chart-container');
                        if (chartContainer) {
                            showNoDataMessage(chartContainer, 'Không có dữ liệu dự án');
                        }
                        toast.innerHTML =
                            '<div class="toast bg-yellow-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-info-circle mr-2"></i><span>Không có dữ liệu dự án</span></div>';
                        setTimeout(() => {
                            toast.classList.add("hidden");
                        }, 3000);
                        return;
                    }

                    // Xóa thông báo không có dữ liệu nếu có
                    const chartContainer = document.querySelector('.chart-container');
                    if (chartContainer) {
                        removeNoDataMessage(chartContainer);
                    }

                    // Cập nhật biểu đồ
                    charts.projectGrowthChart.data.labels = data.labels;
                    charts.projectGrowthChart.data.datasets[0].data = data.data;
                    charts.projectGrowthChart.update();

                    // Hiển thị thông báo thành công
                    toast.innerHTML =
                        '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu biểu đồ dự án!</span></div>';

                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error fetching project growth data:', error);
                    toast.innerHTML =
                        '<div class="toast bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-exclamation-circle mr-2"></i><span>Lỗi khi tải dữ liệu biểu đồ dự án!</span></div>';

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
                        updateWarehouseDistributionChart(charts, {
                            itemType
                        });
                    });
                } else {
                    console.error('warehouseChartItemType element not found!');
                }

                // Thêm event delegation như backup
                document.addEventListener('change', function(e) {
                    if (e.target && e.target.id === 'warehouseChartItemType') {
                        const itemType = e.target.value;
                        updateWarehouseDistributionChart(charts, {
                            itemType
                        });
                    }
                });

                // Global fallback handler wired from the select's onchange attribute
                window.handleWarehouseItemTypeChange = function(itemType) {
                    try {
                        const chartsRef = window.chartInstances;
                        if (!chartsRef) {
                            console.warn('Global handler: chartInstances not ready');
                            return;
                        }
                        updateWarehouseDistributionChart(chartsRef, {
                            itemType
                        });
                    } catch (err) {
                        console.error('Global handler error:', err);
                    }
                };
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

        // Đảm bảo handler và binding luôn tồn tại kể cả khi charts đã có sẵn
        if (!window.handleWarehouseItemTypeChange) {
            window.handleWarehouseItemTypeChange = function(itemType) {
                try {
                    const chartsRef = window.chartInstances;
                    if (!chartsRef) {
                        return;
                    }
                    updateWarehouseDistributionChart(chartsRef, { itemType });
                } catch (err) {
                    console.error('Global handler error:', err);
                }
            };
        }

        // Binding đảm bảo (ngoài nhánh if) và tái gắn khi DOM thay đổi
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'warehouseChartItemType') {
                window.handleWarehouseItemTypeChange(e.target.value);
            }
        });

        // Thử gắn trực tiếp một lần sau khi DOM ready (phòng trường hợp re-render)
        document.addEventListener('DOMContentLoaded', function() {
            const selectEl = document.getElementById('warehouseChartItemType');
            if (selectEl && !selectEl.dataset.bound) {
                selectEl.addEventListener('change', function() {
                    window.handleWarehouseItemTypeChange(this.value);
                });
                selectEl.dataset.bound = '1';
            }
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
            fetch('/warehouses/api-search')
                .then(response => response.json())
                .then(response => {
                    const filterWarehouse = document.getElementById('filterWarehouse');
                    if (filterWarehouse && response.success && response.data && response.data.warehouses) {
                        response.data.warehouses.forEach(warehouse => {
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
            if (advancedFilterBtn && advancedFilterPanel) {
                advancedFilterBtn.addEventListener('click', function() {
                    advancedFilterPanel.classList.toggle('hidden');
                });
            }

            // Đặt lại bộ lọc
            if (resetFiltersBtn) {
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
            }

            // Hiển thị bộ lọc theo loại khi chọn loại tìm kiếm
            if (searchCategory) {
                searchCategory.addEventListener('change', function() {
                    const category = this.value;

                    // Ẩn tất cả các bộ lọc theo loại
                    document.getElementById('materialFilters').classList.add('hidden');
                    document.getElementById('productFilters').classList.add('hidden');
                    document.getElementById('projectFilters').classList.add('hidden');
                    document.getElementById('rentalFilters').classList.add('hidden');

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
                        case 'rentals':
                            document.getElementById('rentalFilters').classList.remove('hidden');
                            break;
                    }
                });
            }

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
                        exclude_out_of_stock: document.getElementById('excludeOutOfStock').checked ?
                            'true' : 'false'
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
                        case 'rentals':
                            filters.rental_status = document.getElementById('filterRentalStatus')?.value;
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

                    // Ngăn chặn các request trùng lặp
                    if (window.lastSearchRequest) {
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
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                // Thêm timestamp để tránh cache
                                'Cache-Control': 'no-cache',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            signal: controller.signal,
                            cache: 'no-store'
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
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
                    const renderRow = (loc, isFirst) => {
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

                        // Số lượng theo từng vị trí
                        const qtyCell = document.createElement('td');
                        qtyCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                        if (loc) {
                            qtyCell.textContent = loc.quantity ?? 0;
                        } else {
                            const qty = (result.quantity !== undefined) ? result.quantity : (result.additionalInfo && result.additionalInfo.quantity ? result.additionalInfo.quantity : 0);
                            qtyCell.textContent = qty;
                        }

                        // Vị trí
                        const locationCell = document.createElement('td');
                        locationCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                        if (loc) {
                            locationCell.textContent = `${loc.name} (${loc.quantity})${loc.counted === false ? ' *' : ''}`;
                        } else {
                            locationCell.textContent = result.location || 'N/A';
                        }

                        // Thao tác
                        const actionCell = document.createElement('td');
                        actionCell.className = 'py-2 px-4 border-b text-gray-800 dark:text-white';
                        const viewButton = document.createElement('button');
                        viewButton.className = 'text-blue-500 hover:text-blue-700 mr-2';
                        viewButton.innerHTML = '<i class="fas fa-eye"></i>';
                        viewButton.addEventListener('click', function() {
                            // Nếu là dòng vị trí, gắn selectedLocation để modal hiển thị đúng số lượng/vị trí
                            const payload = Object.assign({}, result);
                            if (loc) { payload.selectedLocation = loc; }
                            showItemDetails(payload);
                        });

                        const detailLink = document.createElement('a');
                        detailLink.href = result.detailUrl;
                        detailLink.className = 'text-green-500 hover:text-green-700';
                        detailLink.innerHTML = '<i class="fas fa-external-link-alt"></i>';

                        actionCell.appendChild(viewButton);
                        actionCell.appendChild(detailLink);

                        row.appendChild(codeCell);
                        row.appendChild(nameCell);
                        row.appendChild(categoryCell);
                        row.appendChild(qtyCell);
                        row.appendChild(locationCell);
                        row.appendChild(actionCell);
                        searchResultsList.appendChild(row);
                    };

                    if (Array.isArray(result.locations) && result.locations.length > 0) {
                        result.locations.forEach((loc, idx) => renderRow(loc, idx === 0));
                    } else {
                        renderRow(null, true);
                    }
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

                // Tính vị trí mặc định từ danh sách locations nếu có
                const defaultLocation = (function() {
                    if (Array.isArray(item.locations) && item.locations.length > 0) {
                        const counted = item.locations.filter(l => l.counted !== false);
                        const arr = (counted.length > 0 ? counted : item.locations)
                            .map(l => `${l.name} (${l.quantity})${l.counted === false ? ' *' : ''}`);
                        return arr.join(', ');
                    }
                    return item.location || 'N/A';
                })();

                // Nếu click từ một hàng vị trí, ưu tiên hiển thị theo vị trí đó
                const selectedLoc = item.selectedLocation || null;

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
                        document.getElementById('materialQuantity').textContent = (selectedLoc ? selectedLoc.quantity : item.additionalInfo.quantity);
                        document.getElementById('materialUnit').textContent = item.additionalInfo.unit;
                        document.getElementById('itemDate').textContent = item.date;
                        document.getElementById('itemLocation').textContent = selectedLoc ? `${selectedLoc.name} (${selectedLoc.quantity})${selectedLoc.counted === false ? ' *' : ''}` : defaultLocation;
                        break;

                    case 'finished':
                        const finishedInfo = document.getElementById('finishedInfo');
                        finishedInfo.classList.remove('hidden');
                        document.getElementById('finishedManufactureDate').textContent = item.additionalInfo
                            .manufactureDate;
                        document.getElementById('finishedQuantity').textContent = (selectedLoc ? selectedLoc.quantity : item.additionalInfo.quantity);
                        document.getElementById('finishedProject').textContent = item.additionalInfo.project;
                        document.getElementById('itemDate').textContent = item.date;
                        document.getElementById('itemLocation').textContent = selectedLoc ? `${selectedLoc.name} (${selectedLoc.quantity})${selectedLoc.counted === false ? ' *' : ''}` : defaultLocation;
                        break;

                    case 'goods':
                        const goodsInfo = document.getElementById('goodsInfo');
                        goodsInfo.classList.remove('hidden');
                        document.getElementById('goodsDistributor').textContent = item.additionalInfo.distributor;
                        document.getElementById('goodsQuantity').textContent = (selectedLoc ? selectedLoc.quantity : item.additionalInfo.quantity);
                        document.getElementById('itemDate').textContent = item.date;
                        document.getElementById('itemLocation').textContent = selectedLoc ? `${selectedLoc.name} (${selectedLoc.quantity})${selectedLoc.counted === false ? ' *' : ''}` : defaultLocation;
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
                const closeModal = document.getElementById('closeModal');

                if (closeModal) {
                    // Lấy modal element
                    const modalElement = document.getElementById('itemDetailsModal');
                    if (modalElement) {
                        // Hiển thị modal
                        modalElement.classList.remove('hidden');

                        // Xử lý sự kiện đóng modal
                        const closeModalHandler = function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            modalElement.classList.add('hidden');
                            // Gỡ bỏ event listener sau khi đóng
                            closeModal.removeEventListener('click', closeModalHandler);
                        };

                        // Thêm event listener cho nút đóng
                        closeModal.addEventListener('click', closeModalHandler);

                        // Xử lý đóng modal khi click bên ngoài
                        const outsideClickHandler = function(e) {
                            if (e.target === modalElement) {
                                modalElement.classList.add('hidden');
                                // Gỡ bỏ event listener sau khi đóng
                                modalElement.removeEventListener('click', outsideClickHandler);
                            }
                        };

                        // Thêm event listener cho click bên ngoài
                        modalElement.addEventListener('click', outsideClickHandler);
                    }
                }
            }

            // Đóng kết quả tìm kiếm

            // Thêm event listener cho modal nếu chưa có
            const existingCloseModal = document.getElementById('closeModal');
            const existingItemDetailsModal = document.getElementById('itemDetailsModal');

            if (existingCloseModal && existingItemDetailsModal) {
                // Kiểm tra xem đã có event listener chưa
                const hasEventListener = existingCloseModal.onclick !== null;
                if (!hasEventListener) {
                    existingCloseModal.addEventListener('click', function() {
                        existingItemDetailsModal.classList.add('hidden');
                    });

                    // Đóng modal khi click bên ngoài
                    existingItemDetailsModal.addEventListener('click', function(e) {
                        if (e.target === existingItemDetailsModal) {
                            existingItemDetailsModal.classList.add('hidden');
                        }
                    });
                }
            }

            // Đóng kết quả tìm kiếm
            const existingCloseResults = document.getElementById('closeResults');
            const existingSearchResults = document.getElementById('searchResults');
            if (existingCloseResults && existingSearchResults) {
                existingCloseResults.addEventListener('click', function() {
                    existingSearchResults.classList.add('hidden');
                });
            }
        });

        // Initialize flatpickr for date inputs with Vietnamese locale and dd/mm/yyyy format
        document.addEventListener('DOMContentLoaded', function() {
            // Flatpickr configuration for Vietnamese locale with dd/mm/yyyy format
            const dateConfig = {
                locale: 'vn',
                dateFormat: 'd/m/Y',
                allowInput: true,
                clickOpens: true,
                time_24hr: true,
                placeholder: 'DD/MM/YYYY',
                // Set default date to today
                defaultDate: new Date(),
                // Enable date range selection
                mode: 'single',
                // Vietnamese month and day names
                monthNames: [
                    'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                    'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
                ],
                dayNames: [
                    'Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'
                ],
                dayNamesShort: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7']
            };

            // Initialize flatpickr for all date inputs
            flatpickr('#dayStartDate', dateConfig);
            flatpickr('#dayEndDate', dateConfig);
            flatpickr('#weekStartDate', dateConfig);
            flatpickr('#weekEndDate', dateConfig);
            flatpickr('#monthStartDate', dateConfig);
            flatpickr('#monthEndDate', dateConfig);
            flatpickr('#yearStartDate', dateConfig);
            flatpickr('#yearEndDate', dateConfig);

            // Note: Default date values are set by setDefaultDateRanges function
            // which is called after flatpickr initialization
        });
    </script>

    <!-- Flatpickr Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
</body>

</html>
