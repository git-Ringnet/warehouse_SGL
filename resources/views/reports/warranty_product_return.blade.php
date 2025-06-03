<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo thống kê chi tiết module hỏng - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <x-sidebar-component />
    <div class="content-area">
        <h3 class="text-gray-700 text-3xl font-medium">Thống kê thiết bị nhập kho bảo hành</h3>

        <!-- Bộ lọc -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 mt-4">
            <div class="flex flex-wrap -mx-3 mb-2">
                <div class="w-full md:w-1/4 px-3 mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="from-date">
                        Từ ngày
                    </label>
                    <input type="date" id="from-date"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="w-full md:w-1/4 px-3 mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="to-date">
                        Đến ngày
                    </label>
                    <input type="date" id="to-date"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="w-full md:w-1/4 px-3 mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="return-status">
                        Trạng thái
                    </label>
                    <select id="return-status"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Tất cả</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="completed">Đã hoàn thành</option>
                        <option value="rejected">Từ chối bảo hành</option>
                    </select>
                </div>
                <div class="w-full md:w-1/4 px-3 mb-4">
                    <label for="time_period" class="block text-sm font-medium text-gray-700 mb-1">Thời gian</label>
                    <select id="time_period" name="time_period"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="week" selected>Tuần</option>
                        <option value="month">Tháng</option>
                        <option value="year">Năm</option>
                    </select>
                </div>
                <div class="w-full md:w-1/4 px-3 mb-4 flex items-end">
                    <button
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        <i class="fas fa-search mr-2"></i>Tìm kiếm
                    </button>
                </div>
            </div>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-blue-100 border-l-4 border-blue-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-blue-500 p-3 mr-4">
                        <i class="fas fa-box-open text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-blue-500 text-sm font-medium">Tổng thiết bị trả về</p>
                        <p class="text-2xl font-bold">127</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-green-100 border-l-4 border-green-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-green-500 p-3 mr-4">
                        <i class="fas fa-check-circle text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-green-500 text-sm font-medium">Đã xử lý</p>
                        <p class="text-2xl font-bold">98</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-yellow-100 border-l-4 border-yellow-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-yellow-500 p-3 mr-4">
                        <i class="fas fa-clock text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-yellow-500 text-sm font-medium">Đang xử lý</p>
                        <p class="text-2xl font-bold">24</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-red-100 border-l-4 border-red-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-red-500 p-3 mr-4">
                        <i class="fas fa-times-circle text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-red-500 text-sm font-medium">Từ chối bảo hành</p>
                        <p class="text-2xl font-bold">5</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h4 class="text-lg font-semibold text-gray-700 mb-4">Nguyên nhân bảo hành</h4>
                <div class="chart-container" style="position: relative; height:250px;">
                    <canvas id="warrantyReasonChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h4 class="text-lg font-semibold text-gray-700 mb-4">Thiết bị nhập kho bảo hành theo tháng</h4>
                <div class="chart-container" style="position: relative; height:250px;">
                    <canvas id="monthlyReturnChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bảng dữ liệu -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-4 flex justify-between items-center border-b">
                <h4 class="text-lg font-semibold text-gray-700">Danh sách thiết bị nhập kho bảo hành</h4>
                <div class="flex space-x-2">
                    <button
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex items-center">
                        <i class="fas fa-file-excel mr-2"></i>Xuất Excel
                    </button>
                    <button
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i>Xuất PDF
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mã trả hàng</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Khách hàng</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Thiết bị</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Số lượng</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lý do bảo hành</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ngày nhận</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Hạn xử lý</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @for ($i = 1; $i <= 10; $i++)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $i }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">RET-{{ 30000 + $i }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $customers = [
                                            'Công ty ABC',
                                            'Tập đoàn XYZ',
                                            'Viện nghiên cứu DEF',
                                            'Trường Đại học GHI',
                                        ];
                                        echo $customers[array_rand($customers)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $devices = [
                                            'Router Cisco RV345',
                                            'Switch Cisco SG350-28',
                                            'Camera IP Hikvision DS-2CD2023G0',
                                            'Server Dell PowerEdge R440',
                                        ];
                                        echo $devices[array_rand($devices)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ rand(1, 5) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $reasons = [
                                            'Lỗi phần mềm',
                                            'Hỏng phần cứng',
                                            'Lỗi kết nối mạng',
                                            'Lỗi nguồn điện',
                                            'Hao mòn tự nhiên',
                                        ];
                                        echo $reasons[array_rand($reasons)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ date('d/m/Y', strtotime('-' . rand(5, 60) . ' days')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ date('d/m/Y', strtotime('+' . rand(0, 30) . ' days')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statuses = [
                                            ['text' => 'Đã hoàn thành', 'class' => 'bg-green-100 text-green-800'],
                                            ['text' => 'Đang xử lý', 'class' => 'bg-yellow-100 text-yellow-800'],
                                            ['text' => 'Chờ xử lý', 'class' => 'bg-blue-100 text-blue-800'],
                                            ['text' => 'Từ chối bảo hành', 'class' => 'bg-red-100 text-red-800'],
                                        ];

                                        $statusWeights = [70, 20, 5, 5]; // Probabilities for each status
                                        $rand = rand(1, 100);
                                        $cumulativeWeight = 0;
                                        $selectedStatus = 0;

                                        foreach ($statusWeights as $index => $weight) {
                                            $cumulativeWeight += $weight;
                                            if ($rand <= $cumulativeWeight) {
                                                $selectedStatus = $index;
                                                break;
                                            }
                                        }

                                        $status = $statuses[$selectedStatus];
                                    @endphp
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status['class'] }}">
                                        {{ $status['text'] }}
                                    </span>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">Hiển thị 1 đến 10 của 127 bản ghi</div>
                    <div class="flex space-x-1">
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Trước</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm bg-blue-500 text-white">1</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">2</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">3</a>
                        <span class="px-3 py-1 border rounded text-sm">...</span>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">13</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Tiếp</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date pickers with today's date and 60 days ago
        const today = new Date();
        const sixtyDaysAgo = new Date();
        sixtyDaysAgo.setDate(today.getDate() - 60);

        document.getElementById('to-date').valueAsDate = today;
        document.getElementById('from-date').valueAsDate = sixtyDaysAgo;

        // Setup warranty reason chart
        const reasonCtx = document.getElementById('warrantyReasonChart').getContext('2d');
        const reasonChart = new Chart(reasonCtx, {
            type: 'pie',
            data: {
                labels: ['Lỗi phần mềm', 'Hỏng phần cứng', 'Lỗi kết nối mạng', 'Lỗi nguồn điện',
                    'Hao mòn tự nhiên'
                ],
                datasets: [{
                    data: [35, 25, 15, 15, 10],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(255, 205, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
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

        // Setup monthly return chart
        const monthlyCtx = document.getElementById('monthlyReturnChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                datasets: [{
                    label: 'Số thiết bị nhập kho bảo hành',
                    data: [18, 25, 22, 30, 12, 20],
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
