<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo thống kê - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <h1 class="text-xl font-bold text-gray-800">Báo cáo thống kê</h1>
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

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">Loại báo cáo</label>
                        <select id="report_type" name="report_type"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" selected>Tổng quan</option>
                            <option value="inventory">Tồn kho</option>
                            <option value="assembly">Lắp ráp</option>
                            <option value="warranty">Bảo hành</option>
                            <option value="repair">Sửa chữa</option>
                            <option value="dispatch">Xuất kho</option>
                        </select>
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

                <div class="mt-4 flex justify-end">
                    <button id="filter-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-search mr-2"></i> Áp dụng
                    </button>
                </div>
            </div>

            <!-- Dashboard Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">Tổng thiết bị</h3>
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-microchip text-blue-500"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">1,257</p>
                    <div class="flex items-center mt-2">
                        <span class="text-green-500 text-sm font-medium flex items-center">
                            <i class="fas fa-arrow-up mr-1"></i> 12%
                        </span>
                        <span class="text-gray-500 text-sm ml-2">so với tháng trước</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">Lắp ráp</h3>
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-tools text-green-500"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">87</p>
                    <div class="flex items-center mt-2">
                        <span class="text-green-500 text-sm font-medium flex items-center">
                            <i class="fas fa-arrow-up mr-1"></i> 8%
                        </span>
                        <span class="text-gray-500 text-sm ml-2">so với tháng trước</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">Bảo hành</h3>
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-shield-alt text-yellow-500"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">32</p>
                    <div class="flex items-center mt-2">
                        <span class="text-red-500 text-sm font-medium flex items-center">
                            <i class="fas fa-arrow-down mr-1"></i> 5%
                        </span>
                        <span class="text-gray-500 text-sm ml-2">so với tháng trước</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">Xuất kho</h3>
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-truck text-purple-500"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">143</p>
                    <div class="flex items-center mt-2">
                        <span class="text-green-500 text-sm font-medium flex items-center">
                            <i class="fas fa-arrow-up mr-1"></i> 15%
                        </span>
                        <span class="text-gray-500 text-sm ml-2">so với tháng trước</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Inventory Over Time Chart -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Biến động tồn kho theo thời gian</h3>
                    <div class="h-80">
                        <canvas id="inventoryChart"></canvas>
                    </div>
                </div>

                <!-- Assembly by Type Chart -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Phân loại lắp ráp theo sản phẩm</h3>
                    <div class="h-80">
                        <canvas id="assemblyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Warranty Status Chart -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Trạng thái bảo hành</h3>
                    <div class="h-80">
                        <canvas id="warrantyChart"></canvas>
                    </div>
                </div>

                <!-- Dispatch by Location Chart -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Xuất kho theo địa điểm</h3>
                    <div class="h-80">
                        <canvas id="dispatchChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activities Table -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Hoạt động gần đây</h3>
                    <a href="#" class="text-blue-500 hover:text-blue-700 text-sm font-medium">Xem tất cả</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thời gian
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loại hoạt động
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mô tả
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Người thực hiện
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <!-- Recent activities will be populated here -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    25/06/2023 09:45
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Lắp ráp
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    Lắp ráp mới Radio SPA Pro
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    Nguyễn Văn A
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Hoàn thành
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    24/06/2023 14:30
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Bảo hành
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    Xác nhận bảo hành Radio SPA Lite
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    Trần Thị B
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Đang xử lý
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    24/06/2023 10:15
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Xuất kho
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    Xuất kho 5 Radio SPA Mini
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    Lê Văn C
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Hoàn thành
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    23/06/2023 16:20
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Sửa chữa
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    Sửa chữa Radio SPA Pro lỗi anten
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    Phạm Thị D
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Hoàn thành
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    23/06/2023 09:30
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Nhập kho
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    Nhập kho 50 linh kiện CPU Intel i5
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    Ngô Văn E
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Hoàn thành
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
                // Here you would typically make an AJAX call to update the dashboard
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
            
            // Initialize charts
            // 1. Inventory Chart
            const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
            const inventoryChart = new Chart(inventoryCtx, {
                type: 'line',
                data: {
                    labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                    datasets: [
                        {
                            label: 'Thiết bị',
                            data: [850, 920, 980, 1050, 1100, 1150, 1180, 1220, 1240, 1245, 1250, 1257],
                            borderColor: 'rgba(59, 130, 246, 1)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Linh kiện',
                            data: [1200, 1350, 1500, 1620, 1750, 1800, 1920, 2050, 2180, 2250, 2320, 2400],
                            borderColor: 'rgba(16, 185, 129, 1)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
            
            // 2. Assembly Chart
            const assemblyCtx = document.getElementById('assemblyChart').getContext('2d');
            const assemblyChart = new Chart(assemblyCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Radio SPA Pro', 'Radio SPA Lite', 'Radio SPA Mini', 'Radio SPA Plus', 'Radio SPA Ultra'],
                    datasets: [{
                        data: [35, 20, 15, 10, 7],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
            
            // 3. Warranty Chart
            const warrantyCtx = document.getElementById('warrantyChart').getContext('2d');
            const warrantyChart = new Chart(warrantyCtx, {
                type: 'pie',
                data: {
                    labels: ['Hoàn thành', 'Đang xử lý', 'Chờ duyệt', 'Từ chối'],
                    datasets: [{
                        data: [15, 8, 7, 2],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
            
            // 4. Dispatch Chart
            const dispatchCtx = document.getElementById('dispatchChart').getContext('2d');
            const dispatchChart = new Chart(dispatchCtx, {
                type: 'bar',
                data: {
                    labels: ['Hà Nội', 'TP HCM', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ', 'Khác'],
                    datasets: [{
                        label: 'Số lượng xuất kho',
                        data: [45, 38, 25, 15, 12, 8],
                        backgroundColor: 'rgba(139, 92, 246, 0.8)',
                        borderColor: 'rgba(139, 92, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Report type change handler
            const reportTypeSelect = document.getElementById('report_type');
            reportTypeSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                alert(`Đã chọn loại báo cáo: ${selectedValue}. Dữ liệu sẽ được cập nhật.`);
                // Here you would typically make an AJAX call to update the dashboard based on the report type
            });
        });
    </script>
</body>

</html> 