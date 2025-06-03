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
        <h3 class="text-gray-700 text-3xl font-medium">Thống kê thiết bị xuất theo dự án</h3>

        <!-- Bộ lọc -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 mt-4">
            <div class="flex flex-wrap -mx-3 mb-2">
                <div class="w-full md:w-1/5 px-3 mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="from-date">
                        Từ ngày
                    </label>
                    <input type="date" id="from-date"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="w-full md:w-1/5 px-3 mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="to-date">
                        Đến ngày
                    </label>
                    <input type="date" id="to-date"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="w-full md:w-1/5 px-3 mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="project">
                        Dự án
                    </label>
                    <select id="project"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Tất cả</option>
                        <option value="project-a">Dự án A</option>
                        <option value="project-b">Dự án B</option>
                        <option value="project-c">Dự án C</option>
                    </select>
                </div>
                <div class="w-full md:w-1/5 px-3 mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="product-type">
                        Loại thiết bị
                    </label>
                    <select id="product-type"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Tất cả</option>
                        <option value="router">Router</option>
                        <option value="switch">Switch</option>
                        <option value="camera">Camera</option>
                        <option value="server">Server</option>
                    </select>
                </div>
                <div class="w-full md:w-1/5 px-3 mb-4">
                    <label for="time_period" class="block text-sm font-medium text-gray-700 mb-1">Thời gian</label>
                    <select id="time_period" name="time_period"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="week" selected>Tuần</option>
                        <option value="month">Tháng</option>
                        <option value="year">Năm</option>
                    </select>
                </div>
                <div class="w-full md:w-1/5 px-3 mb-4 flex items-end">
                    <button
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
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
                        <i class="fas fa-boxes text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-blue-500 text-sm font-medium">Tổng thiết bị xuất</p>
                        <p class="text-2xl font-bold">635</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-purple-100 border-l-4 border-purple-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-purple-500 p-3 mr-4">
                        <i class="fas fa-project-diagram text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-purple-500 text-sm font-medium">Tổng dự án</p>
                        <p class="text-2xl font-bold">15</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-orange-100 border-l-4 border-orange-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-orange-500 p-3 mr-4">
                        <i class="fas fa-file-export text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-orange-500 text-sm font-medium">Phiếu xuất</p>
                        <p class="text-2xl font-bold">78</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h4 class="text-lg font-semibold text-gray-700 mb-4">Tỷ lệ thiết bị xuất theo dự án</h4>
            <div class="chart-container" style="position: relative; height:300px;">
                <canvas id="projectChart"></canvas>
            </div>
        </div>

        <!-- Bảng dữ liệu -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-4 flex justify-between items-center border-b">
                <h4 class="text-lg font-semibold text-gray-700">Danh sách thiết bị xuất theo dự án</h4>
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
                                Mã dự án</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tên dự án</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mã thiết bị</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tên thiết bị</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Số lượng</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ngày xuất</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @for ($i = 1; $i <= 10; $i++)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $i }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">DA-{{ rand(100, 999) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $projects = ['Dự án A', 'Dự án B', 'Dự án C', 'Dự án D'];
                                        echo $projects[array_rand($projects)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">TB-{{ 20000 + $i }}
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ rand(1, 10) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ date('d/m/Y', strtotime('-' . rand(1, 90) . ' days')) }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">Hiển thị 1 đến 10 của 63 bản ghi</div>
                    <div class="flex space-x-1">
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Trước</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm bg-blue-500 text-white">1</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">2</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">3</a>
                        <span class="px-3 py-1 border rounded text-sm">...</span>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">7</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Tiếp</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date pickers with today's date and 90 days ago
        const today = new Date();
        const ninetyDaysAgo = new Date();
        ninetyDaysAgo.setDate(today.getDate() - 90);

        document.getElementById('to-date').valueAsDate = today;
        document.getElementById('from-date').valueAsDate = ninetyDaysAgo;

        // Setup chart
        const ctx = document.getElementById('projectChart').getContext('2d');
        const projectChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Dự án A', 'Dự án B', 'Dự án C', 'Dự án D', 'Khác'],
                datasets: [{
                    label: 'Thiết bị xuất theo dự án',
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        'rgb(54, 162, 235)',
                        'rgb(255, 99, 132)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Phân bổ thiết bị theo dự án (%)'
                    }
                }
            }
        });
    });
</script>
