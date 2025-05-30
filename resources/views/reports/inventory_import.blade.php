<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo thống kê chi tiết vật tư nhập - SGL</title>
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
                <a href="{{ asset('reports') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Báo cáo thống kê chi tiết vật tư nhập</h1>
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
                        <label for="warehouse" class="block text-sm font-medium text-gray-700 mb-1">Kho</label>
                        <select id="warehouse" name="warehouse"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" selected>Tất cả các kho</option>
                            <option value="hn">Kho Hà Nội</option>
                            <option value="hcm">Kho Hồ Chí Minh</option>
                            <option value="dn">Kho Đà Nẵng</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                        <select id="supplier" name="supplier"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" selected>Tất cả nhà cung cấp</option>
                            <option value="1">Công ty TNHH ABC</option>
                            <option value="2">Công ty TNHH XYZ</option>
                            <option value="3">Công ty Cổ phần DEF</option>
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
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select id="status" name="status"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" selected>Tất cả trạng thái</option>
                            <option value="pending">Chờ kiểm tra</option>
                            <option value="approved">Đã kiểm tra</option>
                            <option value="rejected">Từ chối</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="relative">
                        <input type="text" id="search" placeholder="Tìm kiếm theo mã đơn, mã vật tư, tên vật tư..."
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
                                    Mã đơn nhập
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ngày nhập
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mã vật tư
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên vật tư
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loại
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Số lượng
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Đơn giá (₫)
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thành tiền (₫)
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nhà cung cấp
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kho
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Row 1 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">NK2023060001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU-I5-10400</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU Intel Core i5-10400</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">50</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4,200,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">210,000,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty TNHH ABC</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho Hà Nội</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Đã kiểm tra
                                    </span>
                                </td>
                            </tr>
                            <!-- Row 2 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">NK2023060001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM-8GB-DDR4</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM 8GB DDR4 3200MHz</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">100</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">850,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">85,000,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty TNHH ABC</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho Hà Nội</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Đã kiểm tra
                                    </span>
                                </td>
                            </tr>
                            <!-- Row 3 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">NK2023060002</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">05/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SSD-256GB</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SSD 256GB SATA</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lưu trữ</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">80</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1,200,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">96,000,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty TNHH XYZ</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho Hồ Chí Minh</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Đã kiểm tra
                                    </span>
                                </td>
                            </tr>
                            <!-- Row 4 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">NK2023060003</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">10/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KB-WIRELESS</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bàn phím không dây</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Thiết bị ngoại vi</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">30</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">450,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">13,500,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty Cổ phần DEF</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho Hà Nội</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Chờ kiểm tra
                                    </span>
                                </td>
                            </tr>
                            <!-- Row 5 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">5</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">NK2023060003</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">10/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">MOUSE-WIRELESS</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Chuột không dây</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Thiết bị ngoại vi</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">40</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">250,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">10,000,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty Cổ phần DEF</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho Hà Nội</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Chờ kiểm tra
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Hiển thị <span class="font-medium">1</span> đến <span class="font-medium">5</span> của <span class="font-medium">85</span> bản ghi
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="px-3 py-1 rounded border border-gray-300 bg-blue-500 text-white hover:bg-blue-600">1</button>
                    <button class="px-3 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">2</button>
                    <button class="px-3 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">3</button>
                    <button class="px-3 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter button
            const filterBtn = document.getElementById('filter-btn');
            filterBtn.addEventListener('click', function() {
                alert('Đã áp dụng bộ lọc báo cáo!');
                // Here you would typically make an AJAX call to update the report
            });
            
            // Export buttons
            const exportExcelBtn = document.getElementById('export-excel-btn');
            exportExcelBtn.addEventListener('click', function() {
                alert('Tính năng xuất Excel đang được phát triển!');
            });
            
            const exportPdfBtn = document.getElementById('export-pdf-btn');
            exportPdfBtn.addEventListener('click', function() {
                alert('Tính năng xuất PDF đang được phát triển!');
            });
        });
    </script>
</body>

</html> 