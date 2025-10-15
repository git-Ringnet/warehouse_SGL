<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Báo cáo tổng hợp vật tư - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
</head>

<body class="bg-gray-50">
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <h1 class="text-xl font-bold text-gray-800">Báo cáo tổng hợp vật tư</h1>
        </header>

        <main class="p-6">
            <!-- Bộ lọc -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Bộ lọc báo cáo</h3>
                <div id="filterForm">
                    <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                        <!-- Bộ lọc thời gian -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Thời gian</label>
                            <select name="time_filter" id="timeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="custom" {{ $timeFilter == 'custom' || !$timeFilter ? 'selected' : '' }}>Tùy chọn</option>
                                <option value="quarter" {{ $timeFilter == 'quarter' ? 'selected' : '' }}>Theo Quý</option>
                                <option value="year" {{ $timeFilter == 'year' ? 'selected' : '' }}>Theo Năm</option>
                            </select>
                        </div>
                        
                        <!-- Từ ngày -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Từ ngày</label>
                            <input type="text" name="from_date" id="fromDate" placeholder="DD/MM/YYYY" value="{{ $dateFrom }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <!-- Đến ngày -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Đến ngày</label>
                            <input type="text" name="to_date" id="toDate" placeholder="DD/MM/YYYY" value="{{ $dateTo }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <!-- Tìm kiếm -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm</label>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Mã, tên vật tư..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <!-- Danh mục -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Danh mục</label>
                            <select name="category_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tất cả danh mục</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Nút áp dụng -->
                        <div class="flex items-end">
                            <button type="button" onclick="applyFilter()" 
                                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-search mr-1"></i> Áp dụng
                            </button>
                        </div>
                        
                        <!-- Nút xuất -->
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
                </div>
                
                <!-- Loading indicator -->
                <div id="loadingIndicator" class="hidden mt-4 text-center">
                    <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-600 rounded-lg">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải dữ liệu...
                    </div>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
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
                                <div class="text-sm font-medium text-gray-500">Số lượng danh mục vật tư</div>
                                <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_categories']) }}</div>
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

                    <!-- Vật tư hư hỏng -->
                    <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-orange-500">
                        <div class="flex justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Vật tư hư hỏng (trong kỳ)</div>
                                <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['damaged_quantity'] ?? 0) }}</div>
                                <div class="mt-1 text-xs text-orange-500 font-medium">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Từ kiểm thử
                                </div>
                            </div>
                            <div class="bg-orange-100 rounded-full h-12 w-12 flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-orange-500 text-xl"></i>
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
            <div id="reportContainer" class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Báo cáo chi tiết xuất nhập tồn vật tư</h2>
                    <div id="resultCount" class="text-sm text-gray-500">
                        @if($reportData->count() > 0)
                            {{ $reportData->count() }} kết quả
                        @else
                            Không có kết quả
                        @endif
                    </div>
                </div>
                
                <!-- Ghi chú giải thích -->
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">Ghi chú:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Tồn cuối kỳ:</strong> Tồn đầu kỳ + Nhập - Xuất (tính theo công thức)</li>
                                <li><strong>Tồn hiện tại:</strong> Số lượng thực tế trong kho tại thời điểm hiện tại</li>
                                <li><strong>Chênh lệch:</strong> Tồn hiện tại - Tồn cuối kỳ (dương = thừa, âm = thiếu)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div id="reportTableContainer" class="overflow-x-auto">
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
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(8)">
                                    Tồn hiện tại <i class="fas fa-sort text-gray-300 ml-1"></i>
                                </th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(9)">
                                    Chênh lệch <i class="fas fa-sort text-gray-300 ml-1"></i>
                                </th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(10)">
                                    Hư hỏng <i class="fas fa-sort text-gray-300 ml-1"></i>
                                </th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($reportData as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 text-sm text-gray-900">{{ $loop->index + 1 }}</td>
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
                                <td class="py-3 px-4 text-sm text-gray-900 font-medium">{{ number_format($item['current_stock']) }}</td>
                                <td class="py-3 px-4 text-sm font-medium {{ ($item['current_stock'] - $item['closing_stock']) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    @php
                                        $difference = $item['current_stock'] - $item['closing_stock'];
                                    @endphp
                                    @if($difference >= 0)
                                        +{{ number_format($difference) }}
                                    @else
                                        {{ number_format($difference) }}
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-900 text-orange-600 font-medium">
                                    @if($item['damaged_quantity'] > 0)
                                        {{ number_format($item['damaged_quantity']) }}
                                    @else
                                        0
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <a href="{{ route('materials.show', $item['item_id']) }}" class="text-blue-500 hover:text-blue-700 mr-2" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" onclick="openHistoryModal({{ $item['item_id'] }})" class="text-gray-500 hover:text-gray-700" title="Lịch sử">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="py-8 px-4 text-center text-gray-500">
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
                                <td class="py-3 px-4 text-sm font-medium text-gray-700">{{ number_format($reportData->sum('current_stock')) }}</td>
                                <td class="py-3 px-4 text-sm font-medium {{ ($reportData->sum('current_stock') - $reportData->sum('closing_stock')) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    @php
                                        $totalDifference = $reportData->sum('current_stock') - $reportData->sum('closing_stock');
                                    @endphp
                                    @if($totalDifference >= 0)
                                        +{{ number_format($totalDifference) }}
                                    @else
                                        {{ number_format($totalDifference) }}
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-orange-600">{{ number_format($reportData->sum('damaged_quantity')) }}</td>
                                <td class="py-3 px-4"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                    
                    <div id="reportFooter" class="mt-4 flex justify-between items-center">
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
            </div>
        </main>
    </div>

    <!-- Modal lịch sử xuất nhập vật tư -->
    <div id="historyModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-30 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6 relative h-3/4 overflow-y-scroll">
            <button onclick="closeHistoryModal()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-xl">&times;</button>
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Lịch sử xuất nhập vật tư</h3>
            
            <!-- Bộ lọc thời gian -->
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex gap-4 items-center">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Từ ngày:</label>
                        <input type="text" id="historyFromDate" class="form-input text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Đến ngày:</label>
                        <input type="text" id="historyToDate" class="form-input text-sm border-gray-300 rounded-md">
                    </div>
                    <div class="mt-6">
                        <button onclick="filterHistory()" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                            <i class="fas fa-filter mr-1"></i> Lọc
                        </button>
                        <button onclick="clearHistoryFilter()" class="px-4 py-2 bg-gray-500 text-white text-sm rounded-md hover:bg-gray-600 ml-2">
                            <i class="fas fa-times mr-1"></i> Xóa lọc
                        </button>
                    </div>
                </div>
            </div>

            <div id="historyLoading" class="text-center py-4 text-gray-500 hidden">Đang tải dữ liệu...</div>
            <div id="historyError" class="text-center py-4 text-red-500 hidden"></div>
            <div id="historyTableWrapper" class="overflow-x-auto">
                <table id="historyTable" class="min-w-full bg-white border border-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-2 px-3 border-b text-left">Ngày</th>
                            <th class="py-2 px-3 border-b text-left">Loại</th>
                            <th class="py-2 px-3 border-b text-right">Số lượng</th>
                            <th class="py-2 px-3 border-b text-left">Kho</th>
                            <th class="py-2 px-3 border-b text-left">Người thực hiện</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="historyTotal" class="mt-3 text-sm text-gray-600 hidden"></div>
        </div>
    </div>

    <script>


        // Hàm áp dụng bộ lọc Ajax
        function applyFilter() {
            // Hiển thị loading
            showLoading();
            
            // Lấy dữ liệu từ form
            const formData = getFilterFormData();
            
            // Gọi API
            fetch('{{ route("reports.filter.ajax") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                hideLoading();
                console.log('Response data:', data); // Debug log
                
                if (data.success) {
                    // Lưu thông tin sắp xếp hiện tại trước khi cập nhật DOM
                    const currentSort = getCurrentSortInfo();
                    
                    // Cập nhật bảng
                    document.getElementById('reportTableContainer').innerHTML = data.html;
                    
                    // Khôi phục thứ tự sắp xếp nếu có
                    if (currentSort) {
                        // Đợi một chút để DOM được cập nhật
                        setTimeout(() => {
                            sortTable(currentSort.column);
                        }, 100);
                    }
                    
                    // Cập nhật số lượng kết quả
                    document.getElementById('resultCount').textContent = 
                        data.count > 0 ? `${data.count} kết quả` : 'Không có kết quả';
                    
                    // Cập nhật thống kê tổng quan
                    if (data.stats) {
                        updateStats(data.stats);
                    }
                    
                    // Cập nhật biểu đồ
                    if (data.chartData) {
                        updateCharts(data.chartData);
                    }
                        
                    console.log('Dữ liệu đã được cập nhật thành công');
                } else {
                    console.error('API returned error:', data);
                    alert('Có lỗi xảy ra khi lọc dữ liệu: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Fetch error:', error);
                alert('Có lỗi xảy ra khi lọc dữ liệu: ' + error.message);
            });
        }



        // Lấy dữ liệu từ form filter
        function getFilterFormData() {
            const form = document.getElementById('filterForm');
            const inputs = form.querySelectorAll('input, select');
            const data = {};
            
            inputs.forEach(input => {
                if (input.value !== null && input.value !== undefined && input.value.trim() !== '') {
                    data[input.name] = input.value.trim();
                }
            });
            
            // Thêm thông tin sắp xếp hiện tại
            const currentSort = getCurrentSortInfo();
            if (currentSort) {
                // Map columnIndex sang tên cột cho backend
                const columnMap = {
                    0: 'item_id', // STT
                    1: 'item_code', // Mã vật tư
                    2: 'item_name', // Tên vật tư
                    3: 'item_unit', // Đơn vị
                    4: 'opening_stock', // Tồn đầu kỳ
                    5: 'imports', // Nhập
                    6: 'exports', // Xuất
                    7: 'closing_stock', // Tồn cuối kỳ
                    8: 'current_stock', // Tồn hiện tại
                    9: 'current_stock', // Chênh lệch (sort by current_stock)
                    10: 'damaged_quantity', // Hư hỏng
                };
                
                data.sort_column = columnMap[currentSort.column] || 'item_name';
                data.sort_direction = currentSort.direction;
            }
            
            // Thêm thông tin filter thời gian
            const timeFilter = document.getElementById('timeFilter');
            if (timeFilter && timeFilter.value !== 'custom') {
                data.time_filter = timeFilter.value;
            }
            
            console.log('Filter data:', data); // Debug log
            return data;
        }

        // Hiển thị loading
        function showLoading() {
            document.getElementById('loadingIndicator').classList.remove('hidden');
        }

        // Ẩn loading
        function hideLoading() {
            document.getElementById('loadingIndicator').classList.add('hidden');
        }

        // Cập nhật thống kê tổng quan
        function updateStats(stats) {
            // Cập nhật tổng số vật tư
            document.querySelector('.text-2xl.font-bold.text-gray-800').textContent = 
                stats.total_items.toLocaleString();
            
            // Cập nhật số danh mục vật tư
            const statCards = document.querySelectorAll('.text-2xl.font-bold.text-gray-800');
            if (statCards[1]) {
                statCards[1].textContent = stats.total_categories.toLocaleString();
            }
            
            // Cập nhật nhập kho
            if (statCards[2]) {
                statCards[2].textContent = stats.imports.toLocaleString();
            }
            
            // Cập nhật xuất kho
            if (statCards[3]) {
                statCards[3].textContent = stats.exports.toLocaleString();
            }

            // Cập nhật vật tư hư hỏng
            if (statCards[4]) {
                statCards[4].textContent = stats.damaged_quantity.toLocaleString();
            }
            
            // Cập nhật % thay đổi nhập kho
            const importsChange = document.querySelector('.text-xs.text-green-500.font-medium, .text-xs.text-red-500.font-medium');
            if (importsChange && typeof stats.imports_change !== 'undefined') {
                const changeText = stats.imports_change >= 0 ? '+' + stats.imports_change : stats.imports_change;
                importsChange.innerHTML = `<i class="fas fa-arrow-${stats.imports_change >= 0 ? 'up' : 'down'} mr-1"></i> ${changeText}% so với kỳ trước`;
                importsChange.className = `text-xs ${stats.imports_change >= 0 ? 'text-green-500' : 'text-red-500'} font-medium`;
            }
            
            // Cập nhật % thay đổi xuất kho  
            const exportsChangeElements = document.querySelectorAll('.text-xs.text-green-500.font-medium, .text-xs.text-red-500.font-medium');
            if (exportsChangeElements[3] && typeof stats.exports_change !== 'undefined') {
                const changeText = stats.exports_change >= 0 ? '+' + stats.exports_change : stats.exports_change;
                exportsChangeElements[3].innerHTML = `<i class="fas fa-arrow-${stats.exports_change >= 0 ? 'up' : 'down'} mr-1"></i> ${changeText}% so với kỳ trước`;
                exportsChangeElements[3].className = `text-xs ${stats.exports_change >= 0 ? 'text-green-500' : 'text-red-500'} font-medium`;
            }
        }

        // Variables để lưu charts
        let trendsChart = null;
        let topChart = null;

        // Xử lý filter thời gian
        document.addEventListener('DOMContentLoaded', function() {
            const timeFilter = document.getElementById('timeFilter');
            const fromDate = document.getElementById('fromDate');
            const toDate = document.getElementById('toDate');

            if (timeFilter && fromDate && toDate) {
                timeFilter.addEventListener('change', function() {
                    const selectedValue = this.value;
                    const today = new Date();
                    
                    switch (selectedValue) {
                        case 'quarter':
                            // Tính quý hiện tại
                            const currentQuarter = Math.ceil((today.getMonth() + 1) / 3);
                            const quarterStart = new Date(today.getFullYear(), (currentQuarter - 1) * 3, 1);
                            const quarterEnd = new Date(today.getFullYear(), currentQuarter * 3, 0);
                            
                            fromDate.value = quarterStart.toISOString().split('T')[0];
                            toDate.value = quarterEnd.toISOString().split('T')[0];
                            break;
                            
                        case 'year':
                            // Tính năm hiện tại
                            const yearStart = new Date(today.getFullYear(), 0, 1);
                            const yearEnd = new Date(today.getFullYear(), 11, 31);
                            
                            fromDate.value = yearStart.toISOString().split('T')[0];
                            toDate.value = yearEnd.toISOString().split('T')[0];
                            break;
                            
                        case 'custom':
                        default:
                            // Giữ nguyên giá trị hiện tại
                            break;
                    }
                });
            }
        });

        // Cập nhật biểu đồ
        function updateCharts(chartData) {
            // Cập nhật biểu đồ xu hướng
            if (trendsChart) {
                trendsChart.data.labels = chartData.months;
                trendsChart.data.datasets[0].data = chartData.imports_data;
                trendsChart.data.datasets[1].data = chartData.exports_data;
                trendsChart.update();
            }
            
            // Cập nhật biểu đồ top vật tư
            if (topChart) {
                topChart.data.labels = chartData.top_items_labels;
                topChart.data.datasets[0].data = chartData.top_items_data;
                topChart.update();
            }
        }

        // Cập nhật hàm xuất Excel để dùng filter hiện tại
        function exportToExcel() {
            const formData = getFilterFormData();
            const params = new URLSearchParams(formData);
            window.location.href = '{{ route("reports.export.excel") }}?' + params.toString();
        }

        // Cập nhật hàm xuất PDF để dùng filter hiện tại
        function exportToPdf() {
            const formData = getFilterFormData();
            
            // Thêm thông tin sắp xếp hiện tại
            const currentSort = getCurrentSortInfo();
            if (currentSort) {
                formData.sort_column = currentSort.column;
                formData.sort_direction = currentSort.direction;
            }
            
            const params = new URLSearchParams(formData);
            
            // Hiển thị loading
            const pdfButton = document.querySelector('button[onclick="exportToPdf()"]');
            const originalText = pdfButton.innerHTML;
            pdfButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang tạo PDF...';
            pdfButton.disabled = true;
            
            fetch('{{ route("reports.export.pdf") }}?' + params.toString())
                .then(response => {
                    // Reset button
                    pdfButton.innerHTML = originalText;
                    pdfButton.disabled = false;
                    
                    if (!response.ok) {
                        if (response.status === 403) {
                            alert('Bạn không có quyền xuất file PDF');
                            return;
                        }
                        // Nếu có lỗi khác, parse JSON để lấy thông báo lỗi
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || 'Server error: ' + response.status);
                        });
                    }
                    
                    // Kiểm tra content type
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/pdf')) {
                        // PDF thành công - tạo blob và download
                        return response.blob().then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = 'bao_cao_vat_tu_' + new Date().toISOString().slice(0,19).replace(/[-:]/g, '_').replace('T', '_') + '.pdf';
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                            
                            // Hiển thị thông báo thành công
                            alert('Xuất PDF thành công! File đã được tải xuống.');
                        });
                    } else {
                        // Response không phải PDF, có thể là JSON error
                        return response.json().then(data => {
                            if (data.success === false) {
                                throw new Error(data.message || 'Có lỗi xảy ra khi xuất PDF');
                            }
                        });
                    }
                })
                .catch(error => {
                    // Reset button nếu chưa reset
                    pdfButton.innerHTML = originalText;
                    pdfButton.disabled = false;
                    
                    console.error('PDF Export Error:', error);
                    alert('Có lỗi xảy ra khi xuất PDF: ' + error.message);
                });
        }

        // Khởi tạo khi trang load
        document.addEventListener('DOMContentLoaded', function() {
            // Biểu đồ xu hướng nhập xuất
            const trendsCtx = document.getElementById('inventoryTrendsChart').getContext('2d');
            trendsChart = new Chart(trendsCtx, {
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
            topChart = new Chart(topCtx, {
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

        // Lấy thông tin sắp xếp hiện tại
        function getCurrentSortInfo() {
            const table = document.querySelector('table');
            if (!table) return null;
            
            const headerCells = table.querySelectorAll('th');
            for (let i = 0; i < headerCells.length; i++) {
                if (headerCells[i].classList.contains('sort-asc') || headerCells[i].classList.contains('sort-desc')) {
                    return {
                        column: i,
                        direction: headerCells[i].classList.contains('sort-asc') ? 'asc' : 'desc'
                    };
                }
            }
            return null;
        }

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

        // Initialize flatpickr for date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const dateConfig = {
                locale: 'vn',
                dateFormat: 'd/m/Y',
                allowInput: true,
                disableMobile: true,
                monthSelectorType: 'static',
                yearSelectorType: 'static'
            };

            flatpickr('#fromDate', dateConfig);
            flatpickr('#toDate', dateConfig);
            flatpickr('#historyFromDate', dateConfig);
            flatpickr('#historyToDate', dateConfig);
        });

        let currentMaterialId = null;

        function openHistoryModal(materialId) {
            currentMaterialId = materialId;
            document.getElementById('historyModal').classList.remove('hidden');
            
            // Reset bộ lọc
            document.getElementById('historyFromDate').value = '';
            document.getElementById('historyToDate').value = '';
            
            loadHistoryData();
        }

        function loadHistoryData() {
            if (!currentMaterialId) return;
            
            document.getElementById('historyLoading').classList.remove('hidden');
            document.getElementById('historyError').classList.add('hidden');
            document.getElementById('historyTotal').classList.add('hidden');
            document.querySelector('#historyTable tbody').innerHTML = '';
            
            // Lấy giá trị bộ lọc
            const fromDate = document.getElementById('historyFromDate').value;
            const toDate = document.getElementById('historyToDate').value;
            
            // Tạo URL với params
            let url = `/materials/${currentMaterialId}/history-ajax`;
            const params = new URLSearchParams();
            if (fromDate) params.append('from_date', fromDate);
            if (toDate) params.append('to_date', toDate);
            if (params.toString()) url += '?' + params.toString();
            
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('historyLoading').classList.add('hidden');
                    if (!data.success) {
                        document.getElementById('historyError').textContent = 'Không lấy được dữ liệu.';
                        document.getElementById('historyError').classList.remove('hidden');
                        return;
                    }
                    
                    const tbody = document.querySelector('#historyTable tbody');
                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-500 py-4">Không có lịch sử xuất nhập</td></tr>';
                        return;
                    }
                    
                    tbody.innerHTML = data.data.map(item => `
                        <tr>
                            <td class="py-2 px-3 border-b">${item.date}</td>
                            <td class="py-2 px-3 border-b">${item.type === 'Nhập' ? '<span class="text-green-600 font-medium">Nhập</span>' : '<span class="text-red-600 font-medium">Xuất</span>'}</td>
                            <td class="py-2 px-3 border-b text-right font-mono">${item.quantity}</td>
                            <td class="py-2 px-3 border-b">${item.warehouse_name}</td>
                            <td class="py-2 px-3 border-b">${item.user_name}</td>
                        </tr>
                    `).join('');
                    
                    // Hiển thị tổng số bản ghi
                    document.getElementById('historyTotal').textContent = `Tổng cộng: ${data.total} giao dịch`;
                    document.getElementById('historyTotal').classList.remove('hidden');
                })
                .catch(() => {
                    document.getElementById('historyLoading').classList.add('hidden');
                    document.getElementById('historyError').textContent = 'Có lỗi khi tải dữ liệu.';
                    document.getElementById('historyError').classList.remove('hidden');
                });
        }

        function filterHistory() {
            loadHistoryData();
        }

        function clearHistoryFilter() {
            document.getElementById('historyFromDate').value = '';
            document.getElementById('historyToDate').value = '';
            loadHistoryData();
        }

        function closeHistoryModal() {
            document.getElementById('historyModal').classList.add('hidden');
            currentMaterialId = null;
        }
    </script>
</body>

</html> 