<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo thống kê bảo hành sửa chữa thành công - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <x-sidebar-component />
    <div class="content-area">
        <h3 class="text-gray-700 text-3xl font-medium">Báo cáo bảo hành sửa chữa thành công</h3>

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
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="technician">
                        Kỹ thuật viên
                    </label>
                    <select id="technician"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Tất cả</option>
                        <option value="1">Nguyễn Văn A</option>
                        <option value="2">Trần Văn B</option>
                        <option value="3">Lê Văn C</option>
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
                        <i class="fas fa-wrench text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-blue-500 text-sm font-medium">Tổng thiết bị đã sửa</p>
                        <p class="text-2xl font-bold">143</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-green-100 border-l-4 border-green-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-green-500 p-3 mr-4">
                        <i class="fas fa-check-circle text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-green-500 text-sm font-medium">Phần trăm thành công</p>
                        <p class="text-2xl font-bold">92%</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-yellow-100 border-l-4 border-yellow-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-yellow-500 p-3 mr-4">
                        <i class="fas fa-clock text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-yellow-500 text-sm font-medium">Thời gian trung bình</p>
                        <p class="text-2xl font-bold">3.2 ngày</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-purple-100 border-l-4 border-purple-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-purple-500 p-3 mr-4">
                        <i class="fas fa-user-cog text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-purple-500 text-sm font-medium">Kỹ thuật viên</p>
                        <p class="text-2xl font-bold">8</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h4 class="text-lg font-semibold text-gray-700 mb-4">Tỷ lệ sửa chữa thành công theo loại thiết bị</h4>
                <div class="chart-container" style="position: relative; height:250px;">
                    <canvas id="deviceTypeChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h4 class="text-lg font-semibold text-gray-700 mb-4">Hiệu suất sửa chữa theo tháng</h4>
                <div class="chart-container" style="position: relative; height:250px;">
                    <canvas id="monthlyRepairChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bảng dữ liệu -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-4 flex justify-between items-center border-b">
                <h4 class="text-lg font-semibold text-gray-700">Danh sách thiết bị sửa chữa thành công</h4>
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
                                Mã bảo hành</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Khách hàng</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Thiết bị</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lỗi ban đầu</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Giải pháp</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kỹ thuật viên</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ngày tiếp nhận</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ngày hoàn thành</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @for ($i = 1; $i <= 10; $i++)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $i }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">WR-{{ 20000 + $i }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $customers = [
                                            'Công ty ABC',
                                            'Tập đoàn XYZ',
                                            'Viện nghiên cứu DEF',
                                            'Trường Đại học GHI',
                                            'Bệnh viện JKL',
                                        ];
                                        echo $customers[array_rand($customers)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $devices = [
                                            'Màn hình LED Samsung LU28R550UQEXXV',
                                            'Máy in HP LaserJet Pro M404dn',
                                            'Laptop Dell Latitude 5420',
                                            'Máy chiếu Epson EB-E01',
                                            'UPS APC Back-UPS Pro 1500VA',
                                        ];
                                        echo $devices[array_rand($devices)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $issues = [
                                            'Không khởi động',
                                            'Màn hình chập chờn',
                                            'Kết nối không ổn định',
                                            'Quá nóng khi hoạt động',
                                            'Lỗi phần mềm hệ thống',
                                        ];
                                        echo $issues[array_rand($issues)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $solutions = [
                                            'Thay thế board mạch chính',
                                            'Cập nhật firmware mới',
                                            'Thay IC nguồn',
                                            'Vệ sinh và thay keo tản nhiệt',
                                            'Cài đặt lại phần mềm',
                                        ];
                                        echo $solutions[array_rand($solutions)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $technicians = [
                                            'Nguyễn Văn A',
                                            'Trần Văn B',
                                            'Lê Văn C',
                                            'Phạm Thị D',
                                            'Hoàng Văn E',
                                        ];
                                        echo $technicians[array_rand($technicians)];
                                    @endphp
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ date('d/m/Y', strtotime('-' . rand(10, 60) . ' days')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ date('d/m/Y', strtotime('-' . rand(1, 9) . ' days')) }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">Hiển thị 1-10 của 143 kết quả</div>
                    <div>
                        <nav class="relative z-0 inline-flex shadow-sm -space-x-px" aria-label="Pagination">
                            <a href="#"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <a href="#" aria-current="page"
                                class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                1
                            </a>
                            <a href="#"
                                class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                2
                            </a>
                            <a href="#"
                                class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                3
                            </a>
                            <span
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                            </span>
                            <a href="#"
                                class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                15
                            </a>
                            <a href="#"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Khởi tạo biểu đồ sau khi trang đã tải
        document.addEventListener('DOMContentLoaded', function() {
            // Biểu đồ tỷ lệ sửa chữa theo loại thiết bị
            const deviceTypeChart = new Chart(
                document.getElementById('deviceTypeChart'),
                {
                    type: 'doughnut',
                    data: {
                        labels: ['Màn hình', 'Máy in', 'Laptop', 'Máy chiếu', 'UPS'],
                        datasets: [{
                            label: 'Tỷ lệ sửa chữa thành công',
                            data: [35, 25, 20, 15, 5],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(153, 102, 255, 0.8)',
                                'rgba(255, 159, 64, 0.8)',
                                'rgba(255, 99, 132, 0.8)'
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)',
                                'rgba(255, 99, 132, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            title: {
                                display: false
                            }
                        }
                    }
                }
            );

            // Biểu đồ hiệu suất sửa chữa theo tháng
            const monthlyRepairChart = new Chart(
                document.getElementById('monthlyRepairChart'),
                {
                    type: 'bar',
                    data: {
                        labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                        datasets: [{
                            label: 'Số lượng sửa chữa thành công',
                            data: [12, 15, 18, 10, 14, 20, 25, 22, 17, 19, 18, 16],
                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                            borderColor: 'rgba(75, 192, 192, 1)',
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
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            title: {
                                display: false
                            }
                        }
                    }
                }
            );
        });
    </script>
</body>
</html> 