<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo thống kê chi tiết thiết bị thành phẩm - SGL</title>
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
                <a href="{{ asset('report') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Báo cáo thống kê chi tiết thiết bị thành phẩm theo vật tư đã xuất cho Z755</h1>
            </div>
            <div class="flex gap-2">
                <button id="export-excel-btn"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Xuất Excel
                </button>
                <button id="export-pdf-btn"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Xuất PDF
                </button>
            </div>
        </header>

        <main class="p-6">
            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Bộ lọc báo cáo
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
                        <input type="date" id="date_from" name="date_from" value="{{ date('Y-m-d', strtotime('-30 days')) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
                        <input type="date" id="date_to" name="date_to" value="{{ date('Y-m-d') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="time_period" class="block text-sm font-medium text-gray-700 mb-1">Thời gian</label>
                        <select id="time_period" name="time_period"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="week" selected>Tuần</option>
                            <option value="month">Tháng</option>
                            <option value="year">Năm</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label for="product_type" class="block text-sm font-medium text-gray-700 mb-1">Loại thiết bị</label>
                        <select id="product_type" name="product_type"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" selected>Tất cả loại thiết bị</option>
                            <option value="laptop">Laptop</option>
                            <option value="desktop">Máy tính để bàn</option>
                            <option value="server">Máy chủ</option>
                            <option value="network">Thiết bị mạng</option>
                        </select>
                    </div>
                    <div>
                        <label for="material_type" class="block text-sm font-medium text-gray-700 mb-1">Loại vật tư</label>
                        <select id="material_type" name="material_type"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" selected>Tất cả loại vật tư</option>
                            <option value="cpu">CPU</option>
                            <option value="ram">RAM</option>
                            <option value="storage">Lưu trữ</option>
                            <option value="peripheral">Thiết bị ngoại vi</option>
                            <option value="network">Thiết bị mạng</option>
                        </select>
                    </div>
                    <div>
                        <label for="project" class="block text-sm font-medium text-gray-700 mb-1">Dự án</label>
                        <select id="project" name="project"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" selected>Tất cả dự án</option>
                            <option value="1">Dự án A</option>
                            <option value="2">Dự án B</option>
                            <option value="3">Dự án C</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="relative">
                        <input type="text" id="search" placeholder="Tìm kiếm theo mã thiết bị, tên thiết bị, mã vật tư..."
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button id="filter-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-search mr-2"></i> Áp dụng
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-medium mb-1">Tổng số thiết bị</h3>
                    <div class="flex justify-between items-center">
                        <p class="text-2xl font-bold text-gray-800">215</p>
                        <div class="bg-blue-100 p-2 rounded">
                            <i class="fas fa-laptop text-blue-500"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-medium mb-1">Tổng số vật tư sử dụng</h3>
                    <div class="flex justify-between items-center">
                        <p class="text-2xl font-bold text-gray-800">428</p>
                        <div class="bg-green-100 p-2 rounded">
                            <i class="fas fa-microchip text-green-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Table -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    STT
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mã thiết bị
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên thiết bị
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loại thiết bị
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mã vật tư
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên vật tư
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SL vật tư sử dụng
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dự án
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ngày sản xuất
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Row 1 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">LT-23060001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Laptop SGL Pro 15</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Laptop</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU-I5-10400</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU Intel Core i5-10400</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Dự án A</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2023</td>
                            </tr>
                            <!-- Row 2 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">LT-23060001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Laptop SGL Pro 15</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Laptop</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM-8GB-DDR4</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM 8GB DDR4 3200MHz</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Dự án A</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2023</td>
                            </tr>
                            <!-- Row 3 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">LT-23060001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Laptop SGL Pro 15</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Laptop</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SSD-256GB</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SSD 256GB SATA</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Dự án A</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2023</td>
                            </tr>
                            <!-- Row 4 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">LT-23060002</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Laptop SGL Pro 15</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Laptop</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU-I5-10400</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU Intel Core i5-10400</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Dự án A</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">02/06/2023</td>
                            </tr>
                            <!-- Row 5 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">5</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">DT-23060010</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Máy tính SGL Office</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Máy tính để bàn</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KB-WIRELESS</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bàn phím không dây</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Dự án B</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">05/06/2023</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Trước
                        </a>
                        <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Tiếp
                        </a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Hiển thị
                                <span class="font-medium">1</span>
                                đến
                                <span class="font-medium">5</span>
                                của
                                <span class="font-medium">215</span>
                                kết quả
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <a href="#" aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    1
                                </a>
                                <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    2
                                </a>
                                <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    3
                                </a>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                    ...
                                </span>
                                <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    43
                                </a>
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript để xử lý các chức năng trên trang -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Export to Excel functionality
            document.getElementById('export-excel-btn').addEventListener('click', function() {
                alert('Xuất Excel đang được xử lý');
            });

            // Export to PDF functionality
            document.getElementById('export-pdf-btn').addEventListener('click', function() {
                alert('Xuất PDF đang được xử lý');
            });

            // Filter functionality
            document.getElementById('filter-btn').addEventListener('click', function() {
                alert('Đang áp dụng bộ lọc');
            });
        });
    </script>
</body>

</html> 