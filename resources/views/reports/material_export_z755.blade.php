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
        <h3 class="text-gray-700 text-3xl font-medium">Thống kê vật tư xuất Z755</h3>

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
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="material-type">
                        Loại vật tư
                    </label>
                    <select id="material-type"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Tất cả</option>
                        <option value="electronic">Linh kiện điện tử</option>
                        <option value="mechanical">Linh kiện cơ khí</option>
                        <option value="consumable">Vật tư tiêu hao</option>
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
            <div class="w-full md:w-1/2 px-3 mb-6">
                <div class="bg-blue-100 border-l-4 border-blue-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-blue-500 p-3 mr-4">
                        <i class="fas fa-boxes text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-blue-500 text-sm font-medium">Tổng vật tư đã xuất</p>
                        <p class="text-2xl font-bold">1,248</p>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 px-3 mb-6">
                <div class="bg-orange-100 border-l-4 border-orange-500 rounded-lg shadow-md p-5 flex items-center">
                    <div class="rounded-full bg-orange-500 p-3 mr-4">
                        <i class="fas fa-project-diagram text-white text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-orange-500 text-sm font-medium">Dự án sử dụng</p>
                        <p class="text-2xl font-bold">12</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng dữ liệu -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="p-4 flex justify-between items-center border-b">
                <h4 class="text-lg font-semibold text-gray-700">Danh sách vật tư xuất Z755</h4>
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
                                Mã vật tư</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tên vật tư</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Đơn vị</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Số lượng</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ngày xuất</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dự án</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @for ($i = 1; $i <= 10; $i++)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $i }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">VT-{{ 10000 + $i }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Module điều khiển PLC
                                    S7-1200</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Cái</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ rand(1, 5) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ date('d/m/Y', strtotime('-' . rand(1, 30) . ' days')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Z755-{{ rand(100, 999) }}
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">Hiển thị 1 đến 10 của 145 bản ghi</div>
                    <div class="flex space-x-1">
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Trước</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm bg-blue-500 text-white">1</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">2</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">3</a>
                        <span class="px-3 py-1 border rounded text-sm">...</span>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">15</a>
                        <a href="#" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Tiếp</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date pickers with today's date and 30 days ago
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);

        document.getElementById('to-date').valueAsDate = today;
        document.getElementById('from-date').valueAsDate = thirtyDaysAgo;
    });
</script>
