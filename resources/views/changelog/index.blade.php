<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhật ký thay đổi vật tư và thiết bị - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Nhật ký thay đổi vật tư và thiết bị</h1>
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
                    Bộ lọc
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
                        <input type="date" id="date_from" name="date_from" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
                        <input type="date" id="date_to" name="date_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="change_type" class="block text-sm font-medium text-gray-700 mb-1">Loại thay đổi</label>
                        <select id="change_type" name="change_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Tất cả</option>
                            <option value="add">Thêm mới</option>
                            <option value="remove">Loại bỏ</option>
                            <option value="replace">Thay thế</option>
                            <option value="move">Di chuyển</option>
                            <option value="update">Cập nhật thông tin</option>
                        </select>
                    </div>
                    <div>
                        <label for="item_type" class="block text-sm font-medium text-gray-700 mb-1">Loại mục</label>
                        <select id="item_type" name="item_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Tất cả</option>
                            <option value="material">Vật tư</option>
                            <option value="equipment">Thiết bị</option>
                            <option value="component">Linh kiện</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="relative">
                        <input type="text" id="search" placeholder="Tìm kiếm theo serial, tên thiết bị, người thực hiện..."
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button id="filter-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-search mr-2"></i> Tìm kiếm
                    </button>
                </div>
            </div>

            <!-- Change Log Table -->
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thời gian</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại mục</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên mục</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại thay đổi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mô tả thay đổi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người thực hiện</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <!-- Sample data -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/06/2023 08:30:12</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SN001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Linh kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU Intel i5</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Thêm mới
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Thêm mới linh kiện vào kho</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn A</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/06/2023 09:15:45</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SN002</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Linh kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM 8GB DDR4</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Thêm mới
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Thêm mới linh kiện vào kho</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn A</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">16/06/2023 10:30:00</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SN003</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Thiết bị</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Radio SPA Pro</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Di chuyển
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Di chuyển từ kho Hà Nội đến kho Hồ Chí Minh</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Trần Thị B</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">17/06/2023 14:22:30</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SN004</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Linh kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SSD 256GB</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Thay thế
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Thay thế linh kiện do hỏng</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lê Văn C</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">18/06/2023 09:45:15</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SN005</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Vật tư</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Dây cáp điện</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Loại bỏ
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Loại bỏ vật tư hết hạn sử dụng</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Phạm Thị D</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">6</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">19/06/2023 11:15:00</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SN006</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Thiết bị</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Radio SPA Lite</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    Cập nhật thông tin
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Cập nhật thông tin bảo hành thiết bị</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Ngô Văn E</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">7</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">20/06/2023 15:30:45</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SN007</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Linh kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bàn phím 4x4</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Thêm mới
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Thêm mới linh kiện từ nhà cung cấp</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Vũ Thị G</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">8</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">21/06/2023 09:10:30</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SN008</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Linh kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Anten 5G</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Di chuyển
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Di chuyển đến vị trí lắp ráp</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn H</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">9</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">22/06/2023 13:45:00</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SN009</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Linh kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bo mạch khuếch đại</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Thay thế
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Thay thế linh kiện mới</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Trần Thị I</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">10</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">23/06/2023 16:00:30</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SN010</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Vật tư</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Pin Lithium 5000mAh</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    Cập nhật thông tin
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">Cập nhật thông số kỹ thuật</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lê Văn K</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Hiển thị <span class="font-medium">1</span> đến <span class="font-medium">10</span> của <span class="font-medium">24</span> bản ghi
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

    <!-- Detail Modal -->
    <div id="detail-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Chi tiết thay đổi</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="border-t border-gray-200 pt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Mã thay đổi:</p>
                            <p class="text-sm text-gray-900">CHG-001</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Thời gian:</p>
                            <p class="text-sm text-gray-900">15/06/2023 08:30:12</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Mã serial:</p>
                            <p class="text-sm text-gray-900">SN001</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Loại mục:</p>
                            <p class="text-sm text-gray-900">Linh kiện</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tên mục:</p>
                            <p class="text-sm text-gray-900">CPU Intel i5</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Loại thay đổi:</p>
                            <p class="text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Thêm mới
                                </span>
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Mô tả thay đổi:</p>
                            <p class="text-sm text-gray-900">Thêm mới linh kiện vào kho. Linh kiện được mua từ nhà cung cấp ABC và đã được kiểm tra chất lượng đạt yêu cầu.</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Người thực hiện:</p>
                            <p class="text-sm text-gray-900">Nguyễn Văn A</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Phòng ban:</p>
                            <p class="text-sm text-gray-900">Phòng Kỹ thuật</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Thông tin trước khi thay đổi:</p>
                            <p class="text-sm text-gray-900">Không có (thêm mới)</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Thông tin sau khi thay đổi:</p>
                            <p class="text-sm text-gray-900">
                                - Mã sản phẩm: CPU-I5-10400<br>
                                - Serial: SN001<br>
                                - Nhà sản xuất: Intel<br>
                                - Ngày sản xuất: 10/01/2023<br>
                                - Tình trạng: Mới 100%<br>
                                - Vị trí lưu trữ: Kho Hà Nội, Kệ A, Ngăn 2
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Ghi chú:</p>
                            <p class="text-sm text-gray-900">Linh kiện đã được dán tem kiểm tra và nhập kho theo quy trình QT-KT-001.</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Hình ảnh đính kèm:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="border border-gray-200 rounded-lg p-2">
                                <img src="https://via.placeholder.com/300x200?text=Image+1" alt="Ảnh đính kèm" class="w-full h-auto rounded">
                                <p class="text-xs text-gray-500 mt-1">Hình ảnh tem kiểm tra</p>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-2">
                                <img src="https://via.placeholder.com/300x200?text=Image+2" alt="Ảnh đính kèm" class="w-full h-auto rounded">
                                <p class="text-xs text-gray-500 mt-1">Hình ảnh sản phẩm</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button id="close-modal-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Export buttons
            const exportExcelBtn = document.getElementById('export-excel-btn');
            exportExcelBtn.addEventListener('click', function() {
                alert('Tính năng xuất Excel đang được phát triển!');
            });
            
            const exportPdfBtn = document.getElementById('export-pdf-btn');
            exportPdfBtn.addEventListener('click', function() {
                alert('Tính năng xuất PDF đang được phát triển!');
            });
            
            // Filter button
            const filterBtn = document.getElementById('filter-btn');
            filterBtn.addEventListener('click', function() {
                alert('Đã áp dụng bộ lọc!');
            });
            
            // Modal handling
            const detailModal = document.getElementById('detail-modal');
            const detailButtons = document.querySelectorAll('.bg-blue-100');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const closeModalX = document.getElementById('close-modal');
            
            detailButtons.forEach(button => {
                button.addEventListener('click', function() {
                    detailModal.classList.remove('hidden');
                });
            });
            
            closeModalBtn.addEventListener('click', function() {
                detailModal.classList.add('hidden');
            });
            
            closeModalX.addEventListener('click', function() {
                detailModal.classList.add('hidden');
            });
            
            // Close modal when clicking outside
            detailModal.addEventListener('click', function(e) {
                if (e.target === detailModal) {
                    detailModal.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html> 