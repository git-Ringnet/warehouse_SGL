<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo thống kê chi tiết vật tư kiểm thử cài đặt nghiệm thu - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Báo cáo thống kê chi tiết vật tư kiểm thử cài đặt nghiệm thu</h1>
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
                        <label for="test_type" class="block text-sm font-medium text-gray-700 mb-1">Loại kiểm thử</label>
                        <select id="test_type" name="test_type"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" selected>Tất cả loại kiểm thử</option>
                            <option value="function">Kiểm thử chức năng</option>
                            <option value="performance">Kiểm thử hiệu năng</option>
                            <option value="compatibility">Kiểm thử tương thích</option>
                            <option value="safety">Kiểm thử an toàn</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
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
                        <label for="test_result" class="block text-sm font-medium text-gray-700 mb-1">Kết quả kiểm thử</label>
                        <select id="test_result" name="test_result"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" selected>Tất cả kết quả</option>
                            <option value="passed">Đạt</option>
                            <option value="failed">Không đạt</option>
                            <option value="pending">Đang kiểm thử</option>
                        </select>
                    </div>
                    <div>
                        <label for="tester" class="block text-sm font-medium text-gray-700 mb-1">Người kiểm thử</label>
                        <select id="tester" name="tester"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" selected>Tất cả</option>
                            <option value="1">Nguyễn Văn A</option>
                            <option value="2">Trần Thị B</option>
                            <option value="3">Lê Văn C</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="relative">
                        <input type="text" id="search" placeholder="Tìm kiếm theo mã kiểm thử, mã vật tư, tên vật tư..."
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
                                    Mã kiểm thử
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ngày kiểm thử
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mã vật tư
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên vật tư
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loại vật tư
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loại kiểm thử
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Người kiểm thử
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kết quả
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Đạt tiêu chuẩn
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Row 1 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KT2023060001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU-I5-10400</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU Intel Core i5-10400</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kiểm thử hiệu năng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn A</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Đạt
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Row 2 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KT2023060002</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM-8GB-DDR4</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM 8GB DDR4 3200MHz</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kiểm thử chức năng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Trần Thị B</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Đạt
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Row 3 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KT2023060003</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">05/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SSD-256GB</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SSD 256GB SATA</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lưu trữ</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kiểm thử hiệu năng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lê Văn C</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Đạt
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Row 4 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KT2023060004</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">10/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KB-WIRELESS</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bàn phím không dây</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Thiết bị ngoại vi</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kiểm thử chức năng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn A</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Đang kiểm thử
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <i class="fas fa-minus-circle text-yellow-500"></i>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Row 5 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">5</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KT2023060005</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">ROUTER-WIFI6</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Router WiFi 6</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Thiết bị mạng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kiểm thử tương thích</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Trần Thị B</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Không đạt
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <i class="fas fa-times-circle text-red-500"></i>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Row 6 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">6</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KT2023060006</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">18/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU-I7-11700</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU Intel Core i7-11700</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kiểm thử hiệu năng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lê Văn C</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Đạt
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Row 7 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">7</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KT2023060007</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">20/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM-16GB-DDR4</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM 16GB DDR4 3600MHz</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kiểm thử chức năng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn A</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Đạt
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Row 8 -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">8</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">KT2023060008</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">22/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SSD-512GB</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SSD 512GB NVMe</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lưu trữ</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kiểm thử hiệu năng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Trần Thị B</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Đang kiểm thử
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <i class="fas fa-minus-circle text-yellow-500"></i>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Hiển thị <span class="font-medium">1</span> đến <span class="font-medium">8</span> của <span class="font-medium">24</span> bản ghi
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