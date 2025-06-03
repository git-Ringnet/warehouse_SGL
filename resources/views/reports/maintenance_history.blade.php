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
        <h3 class="text-gray-700 text-3xl font-medium">Lịch sử bảo trì theo dự án</h3>

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
            <div class="w-full md:w-1/3 px-3 mb-6">
                <div class="bg-blue-100 border-l-4 border-blue-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-blue-500 p-3 mr-4">
                        <i class="fas fa-wrench text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-blue-500 text-sm font-medium">Tổng số lần bảo trì</p>
                        <p class="text-2xl font-bold">235</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/3 px-3 mb-6">
                <div class="bg-green-100 border-l-4 border-green-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-green-500 p-3 mr-4">
                        <i class="fas fa-project-diagram text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-green-500 text-sm font-medium">Số dự án được bảo trì</p>
                        <p class="text-2xl font-bold">12</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/3 px-3 mb-6">
                <div class="bg-purple-100 border-l-4 border-purple-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-purple-500 p-3 mr-4">
                        <i class="fas fa-calendar-check text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-purple-500 text-sm font-medium">Lần bảo trì gần nhất</p>
                        <p class="text-2xl font-bold">{{ date('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h4 class="text-lg font-semibold text-gray-700 mb-4">Số lần bảo trì theo dự án</h4>
                <div class="chart-container" style="position: relative; height:250px;">
                    <canvas id="projectMaintenanceChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h4 class="text-lg font-semibold text-gray-700 mb-4">Xu hướng bảo trì theo thời gian</h4>
                <div class="chart-container" style="position: relative; height:250px;">
                    <canvas id="timelineChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bảng dữ liệu -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-4 flex justify-between items-center border-b">
                <h4 class="text-lg font-semibold text-gray-700">Lịch sử bảo trì chi tiết</h4>
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
                                Thiết bị</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Loại bảo trì</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ngày bảo trì</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kỹ thuật viên</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Trạng thái</th>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $types = ['Định kỳ', 'Khắc phục sự cố', 'Nâng cấp', 'Kiểm tra chức năng'];
                                        echo $types[array_rand($types)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ date('d/m/Y', strtotime('-' . rand(1, 180) . ' days')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $technicians = ['Nguyễn Văn A', 'Trần Văn B', 'Lê Văn C', 'Phạm Văn D'];
                                        echo $technicians[array_rand($technicians)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statuses = [
                                            ['text' => 'Hoàn thành', 'class' => 'bg-green-100 text-green-800'],
                                            ['text' => 'Đang xử lý', 'class' => 'bg-yellow-100 text-yellow-800'],
                                            ['text' => 'Chờ phụ tùng', 'class' => 'bg-blue-100 text-blue-800'],
                                            ['text' => 'Cần xử lý thêm', 'class' => 'bg-red-100 text-red-800'],
                                        ];
                                        $status = $statuses[array_rand($statuses)];
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
                    <div class="text-sm text-gray-500">Hiển thị 1 đến 10 của 235 bản ghi</div>
                    <div class="flex space-x-1">
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Trước</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm bg-blue-500 text-white">1</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">2</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">3</a>
                        <span class="px-3 py-1 border rounded text-sm">...</span>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">24</a>
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
        // Initialize date pickers with today's date and 180 days ago
        const today = new Date();
        const sixMonthsAgo = new Date();
        sixMonthsAgo.setDate(today.getDate() - 180);

        document.getElementById('to-date').valueAsDate = today;
        document.getElementById('from-date').valueAsDate = sixMonthsAgo;

        // Setup project maintenance chart
        const projectCtx = document.getElementById('projectMaintenanceChart').getContext('2d');
        const projectMaintenanceChart = new Chart(projectCtx, {
            type: 'bar',
            data: {
                labels: ['Dự án A', 'Dự án B', 'Dự án C', 'Dự án D', 'Dự án E'],
                datasets: [{
                    label: 'Số lần bảo trì',
                    data: [65, 52, 38, 24, 56],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
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

        // Setup timeline chart
        const timelineCtx = document.getElementById('timelineChart').getContext('2d');
        const timelineChart = new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                datasets: [{
                    label: 'Số lần bảo trì',
                    data: [25, 32, 18, 40, 35, 45],
                    fill: false,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>
