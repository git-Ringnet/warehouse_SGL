<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SGL - Hệ thống quản lý kho</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý khách hàng</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm khách hàng..."
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64" />
                    <select
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Ngừng hoạt động</option>
                    </select>
                </div>
                <button
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                    <i class="fas fa-plus mr-2"></i> Thêm khách hàng
                </button>
            </div>
        </header>
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên khách hàng</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Số điện thoại</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Email</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Địa chỉ</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ngày tạo</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Trạng thái</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Nguyễn Văn B</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0912345678</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">nguyenb@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">123 Lê Lợi, Hà Nội</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/05/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đang
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Trần Thị C</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0987654321</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">tranthic@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">456 Trần Hưng Đạo, TP.HCM</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/04/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ngừng
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Lê Văn D</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0901234567</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">levand@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">789 Nguyễn Trãi, Đà Nẵng</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">20/03/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đang
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Vũ Thị M</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0911111111</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">vum@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">11 Lê Lai, Hà Nội</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/07/2023</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đang
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Phạm Thị X</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0912345679</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">phamx@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">23 Nguyễn Huệ, Huế</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">12/07/2023</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đang
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">6</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Ngô Văn Y</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0987654322</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">ngoy@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">45 Lê Lợi, Hải Phòng</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">13/07/2023</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ngừng
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">7</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Lê Thị Z</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0901234568</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">lez@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">67 Nguyễn Văn Cừ, Cần Thơ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">14/07/2023</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đang
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">8</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Trịnh Văn A1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0912345680</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">trinha1@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">89 Lê Duẩn, Đắk Lắk</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/07/2023</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đang
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">9</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Nguyễn Thị B2
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0987654323</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">nguyenthib2@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">12 Nguyễn Trãi, Nam Định</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">16/07/2023</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ngừng
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">10</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Lê Văn C3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0901234569</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">levanc3@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">34 Lê Lợi, Quảng Ngãi</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">17/07/2023</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đang
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">11</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Phạm Thị D4</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0912345670</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">phamd4@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">56 Nguyễn Văn Cừ, Cần Thơ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">18/07/2023</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đang
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">12</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Ngô Văn E5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">0987654324</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">ngovane5@gmail.com</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">78 Phan Đình Phùng, Quảng
                                Ninh</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">19/07/2023</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ngừng
                                    hoạt động</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Dropdown Menus
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const allDropdowns = document.querySelectorAll('.dropdown-content');

            // Close all other dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== id) {
                    d.classList.remove('show');
                }
            });

            // Toggle current dropdown
            dropdown.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });
    </script>
</body>

</html>
