<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo tổng hợp vật tư - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body class="bg-gray-50">
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <h1 class="text-xl font-bold text-gray-800">Báo cáo tổng hợp vật tư</h1>
            <div class="flex items-center gap-4">
                <div class="flex items-center">
                    <label for="from_date" class="mr-2 text-sm font-medium text-gray-700">Từ ngày:</label>
                    <input type="date" id="from_date" name="from_date" class="border border-gray-300 rounded-md p-1.5 text-sm">
                </div>
                <div class="flex items-center">
                    <label for="to_date" class="mr-2 text-sm font-medium text-gray-700">Đến ngày:</label>
                    <input type="date" id="to_date" name="to_date" class="border border-gray-300 rounded-md p-1.5 text-sm">
                </div>
                <button id="filter_button" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1.5 rounded-md text-sm">
                    <i class="fas fa-filter mr-1"></i> Lọc
                </button>
                <div class="relative">
                    <button id="export_dropdown_btn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-1.5 rounded-md text-sm">
                        <i class="fas fa-file-export mr-1"></i> Xuất báo cáo <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    <div id="export_dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                        <a href="#" id="export_excel" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-excel text-green-500 mr-2"></i> Xuất Excel
                        </a>
                        <a href="#" id="export_pdf" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-pdf text-red-500 mr-2"></i> Xuất PDF
                        </a>
                        <a href="#" id="print_report" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-print text-blue-500 mr-2"></i> In báo cáo
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6">
            <!-- Thống kê tổng quan -->
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Tổng vật tư -->
                    <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-blue-500">
                        <div class="flex justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Tổng số vật tư</div>
                                <div class="text-2xl font-bold text-gray-800">1,250</div>
                                <div class="mt-1 text-xs text-green-500 font-medium">
                                    <i class="fas fa-arrow-up mr-1"></i> +8.2% so với tháng trước
                                </div>
                            </div>
                            <div class="bg-blue-100 rounded-full h-12 w-12 flex items-center justify-center">
                                <i class="fas fa-boxes text-blue-500 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Số lượng danh mục vật tư -->
                    <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-purple-500">
                        <div class="flex justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Số lượng danh mục</div>
                                <div class="text-2xl font-bold text-gray-800">45</div>
                                <div class="mt-1 text-xs text-green-500 font-medium">
                                    <i class="fas fa-arrow-up mr-1"></i> +4.6% so với tháng trước
                                </div>
                            </div>
                            <div class="bg-purple-100 rounded-full h-12 w-12 flex items-center justify-center">
                                <i class="fas fa-tags text-purple-500 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Nhập kho -->
                    <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-green-500">
                        <div class="flex justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Nhập kho (30 ngày)</div>
                                <div class="text-2xl font-bold text-gray-800">325</div>
                                <div class="mt-1 text-xs text-green-500 font-medium">
                                    <i class="fas fa-arrow-up mr-1"></i> +5.7% so với kỳ trước
                                </div>
                            </div>
                            <div class="bg-green-100 rounded-full h-12 w-12 flex items-center justify-center">
                                <i class="fas fa-sign-in-alt text-green-500 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Xuất kho -->
                    <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-red-500">
                        <div class="flex justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Xuất kho (30 ngày)</div>
                                <div class="text-2xl font-bold text-gray-800">412</div>
                                <div class="mt-1 text-xs text-red-500 font-medium">
                                    <i class="fas fa-arrow-down mr-1"></i> -3.2% so với kỳ trước
                                </div>
                            </div>
                            <div class="bg-red-100 rounded-full h-12 w-12 flex items-center justify-center">
                                <i class="fas fa-sign-out-alt text-red-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Biểu đồ nhập xuất -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Xu hướng nhập xuất vật tư</h2>
                    <canvas id="inventoryTrendsChart" height="240"></canvas>
                </div>

                <!-- Biểu đồ top vật tư -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Top vật tư tồn kho</h2>
                    <canvas id="topMaterialsChart" height="240"></canvas>
                </div>
            </div>

            <!-- Báo cáo chi tiết -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Báo cáo chi tiết xuất nhập tồn vật tư</h2>
                    <div class="flex items-center">
                        <div class="relative mr-2">
                            <input type="text" id="search_input" placeholder="Tìm kiếm vật tư..." 
                                class="border border-gray-300 rounded-md py-1.5 pl-8 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search absolute left-3 top-2 text-gray-400"></i>
                        </div>
                        <select id="category_filter" class="border border-gray-300 rounded-md py-1.5 px-3 text-sm">
                            <option value="">Tất cả phân loại</option>
                            <option value="cpu">CPU</option>
                            <option value="ram">RAM</option>
                            <option value="storage">Lưu trữ</option>
                            <option value="peripherals">Thiết bị ngoại vi</option>
                        </select>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(1)">
                                    Mã vật tư <i class="fas fa-sort text-gray-300 ml-1"></i>
                                </th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(2)">
                                    Tên vật tư <i class="fas fa-sort text-gray-300 ml-1"></i>
                                </th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(4)">
                                    Tồn đầu kỳ <i class="fas fa-sort text-gray-300 ml-1"></i>
                                </th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(5)">
                                    Nhập <i class="fas fa-sort text-gray-300 ml-1"></i>
                                </th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(6)">
                                    Xuất <i class="fas fa-sort text-gray-300 ml-1"></i>
                                </th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(7)">
                                    Tồn cuối kỳ <i class="fas fa-sort text-gray-300 ml-1"></i>
                                </th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <!-- Dữ liệu mẫu - sẽ được thay thế bằng dữ liệu thực tế -->
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-sm text-gray-900">1</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">VT001</td>
                                <td class="py-3 px-4 text-sm text-gray-900">Bộ vi xử lý Intel Core i5</td>
                                <td class="py-3 px-4 text-sm text-gray-900">Cái</td>
                                <td class="py-3 px-4 text-sm text-gray-900">150</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-green-600">+50</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-red-600">-75</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">125</td>
                                <td class="py-3 px-4 text-sm">
                                    <button class="text-blue-500 hover:text-blue-700 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-sm text-gray-900">2</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">VT002</td>
                                <td class="py-3 px-4 text-sm text-gray-900">RAM DDR4 8GB</td>
                                <td class="py-3 px-4 text-sm text-gray-900">Thanh</td>
                                <td class="py-3 px-4 text-sm text-gray-900">300</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-green-600">+100</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-red-600">-120</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">280</td>
                                <td class="py-3 px-4 text-sm">
                                    <button class="text-blue-500 hover:text-blue-700 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-sm text-gray-900">3</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">VT003</td>
                                <td class="py-3 px-4 text-sm text-gray-900">SSD 512GB</td>
                                <td class="py-3 px-4 text-sm text-gray-900">Cái</td>
                                <td class="py-3 px-4 text-sm text-gray-900">200</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-green-600">+80</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-red-600">-95</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">185</td>
                                <td class="py-3 px-4 text-sm">
                                    <button class="text-blue-500 hover:text-blue-700 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-sm text-gray-900">4</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">VT004</td>
                                <td class="py-3 px-4 text-sm text-gray-900">Màn hình LCD 24 inch</td>
                                <td class="py-3 px-4 text-sm text-gray-900">Cái</td>
                                <td class="py-3 px-4 text-sm text-gray-900">120</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-green-600">+30</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-red-600">-45</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">105</td>
                                <td class="py-3 px-4 text-sm">
                                    <button class="text-blue-500 hover:text-blue-700 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-sm text-gray-900">5</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">VT005</td>
                                <td class="py-3 px-4 text-sm text-gray-900">Bàn phím cơ</td>
                                <td class="py-3 px-4 text-sm text-gray-900">Cái</td>
                                <td class="py-3 px-4 text-sm text-gray-900">100</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-green-600">+25</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-red-600">-40</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">85</td>
                                <td class="py-3 px-4 text-sm">
                                    <button class="text-blue-500 hover:text-blue-700 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="py-3 px-4 text-sm font-medium text-gray-700 text-right">Tổng:</td>
                                <td class="py-3 px-4 text-sm font-medium text-gray-700">870</td>
                                <td class="py-3 px-4 text-sm font-medium text-green-600">+285</td>
                                <td class="py-3 px-4 text-sm font-medium text-red-600">-375</td>
                                <td class="py-3 px-4 text-sm font-medium text-gray-700">780</td>
                                <td class="py-3 px-4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-4 flex justify-between items-center">
                    <div class="text-sm text-gray-500">Hiển thị 1-5 của 100 kết quả</div>
                    <div class="flex space-x-1">
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="px-3 py-1 border border-blue-500 bg-blue-500 text-white rounded-md text-sm">1</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm">2</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm">3</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm">...</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm">20</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date values (current month)
            const today = new Date();
            const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            
            const fromDateInput = document.getElementById('from_date');
            const toDateInput = document.getElementById('to_date');
            
            fromDateInput.valueAsDate = firstDayOfMonth;
            toDateInput.valueAsDate = today;
            
            // Filter button functionality
            document.getElementById('filter_button').addEventListener('click', function() {
                // Placeholder for filter functionality
                alert('Chức năng lọc dữ liệu sẽ được xử lý ở đây');
            });
            
            // Export dropdown
            const exportDropdownBtn = document.getElementById('export_dropdown_btn');
            const exportDropdown = document.getElementById('export_dropdown');
            
            exportDropdownBtn.addEventListener('click', function() {
                exportDropdown.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!exportDropdownBtn.contains(e.target) && !exportDropdown.contains(e.target)) {
                    exportDropdown.classList.add('hidden');
                }
            });
            
            // Export actions
            document.getElementById('export_excel').addEventListener('click', function(e) {
                e.preventDefault();
                alert('Xuất báo cáo Excel');
            });
            
            document.getElementById('export_pdf').addEventListener('click', function(e) {
                e.preventDefault();
                alert('Xuất báo cáo PDF');
            });
            
            document.getElementById('print_report').addEventListener('click', function(e) {
                e.preventDefault();
                alert('In báo cáo');
            });

            // Biểu đồ xu hướng nhập xuất
            const trendsCtx = document.getElementById('inventoryTrendsChart').getContext('2d');
            const trendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                    datasets: [
                        {
                            label: 'Nhập kho',
                            data: [65, 78, 52, 91, 43, 58, 85, 92, 110, 105, 98, 120],
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Xuất kho',
                            data: [40, 68, 86, 74, 56, 60, 87, 96, 105, 115, 90, 85],
                            borderColor: '#EF4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
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
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Biểu đồ top vật tư
            const topCtx = document.getElementById('topMaterialsChart').getContext('2d');
            const topChart = new Chart(topCtx, {
                type: 'bar',
                data: {
                    labels: ['RAM DDR4 8GB', 'SSD 512GB', 'Bộ vi xử lý Intel Core i5', 'Màn hình LCD 24"', 'Bàn phím cơ'],
                    datasets: [{
                        label: 'Số lượng tồn',
                        data: [280, 185, 125, 105, 85],
                        backgroundColor: [
                            'rgba(99, 102, 241, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(168, 85, 247, 0.7)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Search functionality
            const searchInput = document.getElementById('search_input');
            searchInput.addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('tbody tr');
                
                tableRows.forEach(row => {
                    const materialCode = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const materialName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    
                    if (materialCode.includes(searchValue) || materialName.includes(searchValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Table sorting functionality
        function sortTable(columnIndex) {
            const table = document.querySelector('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const headerCells = table.querySelectorAll('th');
            
            // Determine sort direction
            let sortDirection = headerCells[columnIndex].classList.contains('sort-asc') ? 'desc' : 'asc';
            
            // Reset all header sort indicators
            headerCells.forEach(cell => {
                cell.classList.remove('sort-asc', 'sort-desc');
                const icon = cell.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-sort text-gray-300 ml-1';
                }
            });
            
            // Set current header sort indicator
            headerCells[columnIndex].classList.add('sort-' + sortDirection);
            const currentIcon = headerCells[columnIndex].querySelector('i');
            if (currentIcon) {
                currentIcon.className = sortDirection === 'asc' ? 
                    'fas fa-sort-up text-blue-500 ml-1' : 
                    'fas fa-sort-down text-blue-500 ml-1';
            }
            
            // Sort the rows
            rows.sort((a, b) => {
                const aValue = a.cells[columnIndex].textContent.trim();
                const bValue = b.cells[columnIndex].textContent.trim();
                
                // Check if it's a number
                if (!isNaN(parseFloat(aValue)) && !isNaN(parseFloat(bValue))) {
                    return sortDirection === 'asc' ? 
                        parseFloat(aValue) - parseFloat(bValue) : 
                        parseFloat(bValue) - parseFloat(aValue);
                }
                
                // Sort as strings
                return sortDirection === 'asc' ? 
                    aValue.localeCompare(bValue) : 
                    bValue.localeCompare(aValue);
            });
            
            // Re-add rows to the table
            rows.forEach(row => tbody.appendChild(row));
        }
    </script>
</body>

</html> 