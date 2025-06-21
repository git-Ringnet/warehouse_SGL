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
                                <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_items']) }}</div>
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
                                <div class="text-sm font-medium text-gray-500">Số lượng kho</div>
                                <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_categories']) }}</div>
                                <div class="mt-1 text-xs text-green-500 font-medium">
                                    <i class="fas fa-arrow-up mr-1"></i> +4.6% so với tháng trước
                                </div>
                            </div>
                            <div class="bg-purple-100 rounded-full h-12 w-12 flex items-center justify-center">
                                <i class="fas fa-warehouse text-purple-500 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Nhập kho -->
                    <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-green-500">
                        <div class="flex justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Nhập kho (trong kỳ)</div>
                                <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['imports']) }}</div>
                                <div class="mt-1 text-xs {{ $stats['imports_change'] >= 0 ? 'text-green-500' : 'text-red-500' }} font-medium">
                                    <i class="fas fa-arrow-{{ $stats['imports_change'] >= 0 ? 'up' : 'down' }} mr-1"></i> 
                                    {{ $stats['imports_change'] >= 0 ? '+' : '' }}{{ $stats['imports_change'] }}% so với kỳ trước
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
                                <div class="text-sm font-medium text-gray-500">Xuất kho (trong kỳ)</div>
                                <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['exports']) }}</div>
                                <div class="mt-1 text-xs {{ $stats['exports_change'] >= 0 ? 'text-green-500' : 'text-red-500' }} font-medium">
                                    <i class="fas fa-arrow-{{ $stats['exports_change'] >= 0 ? 'up' : 'down' }} mr-1"></i> 
                                    {{ $stats['exports_change'] >= 0 ? '+' : '' }}{{ $stats['exports_change'] }}% so với kỳ trước
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

            <!-- Bộ lọc -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Bộ lọc báo cáo</h3>
                <form method="GET" action="{{ route('reports.index') }}" id="filterForm">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Từ ngày</label>
                            <input type="date" name="from_date" value="{{ $dateFrom }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Đến ngày</label>
                            <input type="date" name="to_date" value="{{ $dateTo }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm</label>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Mã, tên vật tư..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Danh mục</label>
                            <select name="category_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả danh mục</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-search mr-1"></i> Áp dụng
                            </button>
                        </div>
                        <div class="flex items-end space-x-1">
                            <button type="button" onclick="exportToExcel()" 
                                    class="flex-1 bg-green-600 text-white px-2 py-2 rounded-md hover:bg-green-700 transition duration-200 text-sm">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button type="button" onclick="exportToPdf()" 
                                    class="flex-1 bg-red-600 text-white px-2 py-2 rounded-md hover:bg-red-700 transition duration-200 text-sm">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Báo cáo chi tiết -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Báo cáo chi tiết xuất nhập tồn vật tư</h2>
                    <div class="text-sm text-gray-500">
                        @if($reportData->count() > 0)
                            {{ $reportData->count() }} kết quả
                        @else
                            Không có kết quả
                        @endif
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
                            @forelse($reportData as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">{{ $item['item_code'] }}</td>
                                <td class="py-3 px-4 text-sm text-gray-900">{{ $item['item_name'] }}</td>
                                <td class="py-3 px-4 text-sm text-gray-900">{{ $item['item_unit'] }}</td>
                                <td class="py-3 px-4 text-sm text-gray-900">{{ number_format($item['opening_stock']) }}</td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-green-600">
                                    @if($item['imports'] > 0)
                                        +{{ number_format($item['imports']) }}
                                    @else
                                        0
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-red-600">
                                    @if($item['exports'] > 0)
                                        -{{ number_format($item['exports']) }}
                                    @else
                                        0
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">{{ number_format($item['closing_stock']) }}</td>
                                <td class="py-3 px-4 text-sm">
                                    <button class="text-blue-500 hover:text-blue-700 mr-2" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="text-gray-500 hover:text-gray-700" title="Lịch sử">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="py-8 px-4 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4 text-gray-400"></i>
                                    <p class="text-lg font-medium">Không có dữ liệu</p>
                                    <p class="text-sm">Thử thay đổi bộ lọc để xem kết quả khác</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($reportData->count() > 0)
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="py-3 px-4 text-sm font-medium text-gray-700 text-right">Tổng:</td>
                                <td class="py-3 px-4 text-sm font-medium text-gray-700">{{ number_format($reportData->sum('opening_stock')) }}</td>
                                <td class="py-3 px-4 text-sm font-medium text-green-600">+{{ number_format($reportData->sum('imports')) }}</td>
                                <td class="py-3 px-4 text-sm font-medium text-red-600">-{{ number_format($reportData->sum('exports')) }}</td>
                                <td class="py-3 px-4 text-sm font-medium text-gray-700">{{ number_format($reportData->sum('closing_stock')) }}</td>
                                <td class="py-3 px-4"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                <div class="mt-4 flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        @if($reportData->count() > 0)
                            Hiển thị {{ $reportData->count() }} kết quả từ {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                        @else
                            Không có kết quả
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-clock mr-1"></i>
                        Cập nhật lúc: {{ \Carbon\Carbon::now()->format('H:i:s d/m/Y') }}
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Chức năng xuất Excel
        function exportToExcel() {
            const params = new URLSearchParams();
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            
            for (let [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }
            
            window.location.href = '{{ route("reports.export.excel") }}?' + params.toString();
        }

        // Chức năng xuất PDF
        function exportToPdf() {
            const params = new URLSearchParams();
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            
            for (let [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }
            
            fetch('{{ route("reports.export.pdf") }}?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message + ' Tìm thấy ' + data.data_count + ' kết quả.');
                    } else {
                        alert('Có lỗi xảy ra khi xuất PDF');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xuất PDF');
                });
        }

        // Khởi tạo khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            // Biểu đồ xu hướng nhập xuất
            const trendsCtx = document.getElementById('inventoryTrendsChart').getContext('2d');
            const trendsChart = new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: @json($chartData['months']),
                    datasets: [
                        {
                            label: 'Nhập kho',
                            data: @json($chartData['imports_data']),
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Xuất kho',
                            data: @json($chartData['exports_data']),
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
                    labels: @json($chartData['top_items_labels']),
                    datasets: [{
                        label: 'Số lượng tồn',
                        data: @json($chartData['top_items_data']),
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

        });

        // Table sorting functionality  
        function sortTable(columnIndex) {
            const table = document.querySelector('table');
            if (!table) return;
            
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