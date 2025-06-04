<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sửa chữa & bảo trì - SGL</title>
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
            <div class="flex items-center">
                <a href="{{ asset('repair') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chi tiết sửa chữa & bảo trì</h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ asset('warranties/repair_edit') }}">
                    <button
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                    </button>
                </a>
                <button id="print-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-print mr-2"></i> In phiếu
                </button>
            </div>
        </header>

        <main class="p-6">
            <!-- Header Info -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <div class="flex flex-col lg:flex-row justify-between gap-4">
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-lg font-semibold text-gray-800 mr-2">Mã phiếu:</span>
                            <span class="text-lg text-blue-600 font-bold">REP001</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Thiết bị:</span>
                            <span class="text-sm text-gray-700">DEV001 - Bộ điều khiển chính</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Mã bảo hành:</span>
                            <span class="text-sm text-blue-600">W12345</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Khách hàng:</span>
                            <span class="text-sm text-gray-700">Công ty TNHH ABC</span>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Ngày sửa chữa:</span>
                            <span class="text-sm text-gray-700">15/05/2023</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Kỹ thuật viên:</span>
                            <span class="text-sm text-gray-700">Nguyễn Văn A</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Kho sửa chữa:</span>
                            <span class="text-sm text-gray-700">Kho chính</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Loại sửa chữa:</span>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Bảo trì định kỳ
                            </span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Trạng thái:</span>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Hoàn thành
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repair Information -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-tools text-blue-500 mr-2"></i>
                    Thông tin sửa chữa
                </h2>

                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-700 mb-2">Mô tả sửa chữa:</h3>
                    <div class="bg-gray-50 p-4 rounded-lg text-gray-700">
                        <p>Tiến hành kiểm tra tổng thể thiết bị, vệ sinh bụi bẩn bên trong và bên ngoài thiết bị. Kiểm tra các kết nối, đầu cắm và phát hiện một số tiếp điểm bị ôxy hóa nhẹ. Đã tiến hành làm sạch và bôi chất chống ôxy hóa.</p>
                        <p class="mt-2">Thiết bị hoạt động bình thường sau khi bảo trì, không phát hiện lỗi hay vấn đề bất thường nào.</p>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-700 mb-2">Linh kiện đã thay thế:</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã linh kiện
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên linh kiện
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số lượng
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">N/A</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Không có linh kiện thay thế</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Device Information -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-microchip text-blue-500 mr-2"></i>
                    Thông tin thiết bị
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-1">Mã thiết bị:</h3>
                        <p class="text-gray-900">DEV001</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-1">Tên thiết bị:</h3>
                        <p class="text-gray-900">Bộ điều khiển chính</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-1">Serial:</h3>
                        <p class="text-gray-900">SN001122</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-1">Ngày sản xuất:</h3>
                        <p class="text-gray-900">01/01/2022</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-1">Phiên bản:</h3>
                        <p class="text-gray-900">v2.1</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-md font-medium text-gray-700 mb-2">Lịch sử sửa chữa:</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã phiếu
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Loại
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kỹ thuật viên
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Trạng thái
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="bg-blue-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">REP001</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15/05/2023</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Bảo trì định kỳ
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Nguyễn Văn A</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Hoàn thành
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Photos & Notes -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-paperclip text-blue-500 mr-2"></i>
                    Hình ảnh & Ghi chú
                </h2>

                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-700 mb-2">Hình ảnh:</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <img src="https://via.placeholder.com/300x200?text=Thiết+bị+trước+khi+bảo+trì" alt="Thiết bị trước khi bảo trì" class="w-full h-auto">
                            <div class="p-2 bg-gray-50">
                                <p class="text-sm text-gray-600">Thiết bị trước khi bảo trì</p>
                            </div>
                        </div>
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <img src="https://via.placeholder.com/300x200?text=Thiết+bị+sau+khi+bảo+trì" alt="Thiết bị sau khi bảo trì" class="w-full h-auto">
                            <div class="p-2 bg-gray-50">
                                <p class="text-sm text-gray-600">Thiết bị sau khi bảo trì</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-md font-medium text-gray-700 mb-2">Ghi chú:</h3>
                    <div class="bg-gray-50 p-4 rounded-lg text-gray-700">
                        <p>Khách hàng phản hồi thiết bị hoạt động tốt sau khi bảo trì. Đề xuất cần kiểm tra lại sau 3 tháng nếu có điều kiện.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý sự kiện in phiếu
            const printBtn = document.getElementById('print-btn');
            printBtn.addEventListener('click', function() {
                window.print();
            });
        });
    </script>
</body>

</html> 