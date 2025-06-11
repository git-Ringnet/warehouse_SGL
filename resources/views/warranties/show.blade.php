<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết bảo hành - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết bảo hành</h1>
            </div>
            <div class="flex space-x-2">
                <button onclick="generateQR()"
                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-qrcode mr-2"></i> Tạo QR Code
                </button>
                <a href="{{ asset('warranties') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Thiết bị Radio SPA Pro</h2>
                                <div class="flex items-center mt-1">
                                    <p class="text-gray-600">Mã thiết bị: <span class="font-medium">DEV-SPA-001</span></p>
                                    <span class="mx-2 text-gray-300">|</span>
                                    <p class="text-gray-600">Serial: <span class="font-medium">SER123456</span></p>
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end">
                                <div class="flex items-center">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Đang bảo hành
                                    </span>
                                </div>
                                <div class="flex items-center mt-2">
                                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                    <span class="text-blue-700">01/01/2023 - 01/01/2025</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Thông tin thiết bị -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-microchip mr-2 text-blue-500"></i>
                            Thông tin thiết bị
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-6 mb-6">
                            <div>
                                <p class="text-sm text-gray-500">Tên thiết bị</p>
                                <p class="text-gray-900 font-medium">Radio SPA Pro</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Mã thiết bị</p>
                                <p class="text-gray-900 font-medium">DEV-SPA-001</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Serial</p>
                                <p class="text-gray-900 font-medium">SER123456</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày sản xuất</p>
                                <p class="text-gray-900">15/12/2022</p>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mb-6">
                            <p class="text-sm text-gray-500">Mô tả thiết bị</p>
                            <p class="text-gray-900 mt-1">Radio SPA Pro là thiết bị viễn thông thế hệ mới nhất của SGL, được trang bị module GPS NEO-6M và hệ thống thu phát sóng hai chiều tiên tiến. thành phẩm phù hợp cho các công trình viễn thông và trạm phát sóng.</p>
                        </div>

                        <!-- Module Components -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Các module thiết bị</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <table class="min-w-full">
                                    <thead>
                                        <tr>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">STT</th>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Mã module</th>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Tên module</th>
                                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Serial Number</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr>
                                            <td class="py-2 text-sm text-gray-700">1</td>
                                            <td class="py-2 text-sm text-gray-700">MOD-GPS-001</td>
                                            <td class="py-2 text-sm text-gray-700">GPS Module</td>
                                            <td class="py-2 text-sm text-gray-700">SER123456</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 text-sm text-gray-700">2</td>
                                            <td class="py-2 text-sm text-gray-700">MOD-CPU-002</td>
                                            <td class="py-2 text-sm text-gray-700">CPU Module</td>
                                            <td class="py-2 text-sm text-gray-700">SER123456</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 text-sm text-gray-700">3</td>
                                            <td class="py-2 text-sm text-gray-700">MOD-ANT-003</td>
                                            <td class="py-2 text-sm text-gray-700">Antenna Module</td>
                                            <td class="py-2 text-sm text-gray-700">SER123456</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 text-sm text-gray-700">4</td>
                                            <td class="py-2 text-sm text-gray-700">MOD-PWR-004</td>
                                            <td class="py-2 text-sm text-gray-700">Power Module</td>
                                            <td class="py-2 text-sm text-gray-700">SER123456</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin khách hàng và dịch vụ -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-500"></i>
                            Thông tin khách hàng
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Tên khách hàng</p>
                                <p class="text-gray-900 font-medium">Công ty TNHH ABC</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Người liên hệ</p>
                                <p class="text-gray-900">Nguyễn Văn A</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Số điện thoại</p>
                                <p class="text-gray-900">0912345678</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="text-gray-900">contact@abc.com.vn</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Địa chỉ</p>
                                <p class="text-gray-900">Số 123, Đường Lê Lợi, Quận Hoàn Kiếm, Hà Nội</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-shield-alt mr-2 text-blue-500"></i>
                            Thông tin bảo hành
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Gói bảo hành</p>
                                <p class="text-gray-900 font-medium">2 năm</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày kích hoạt</p>
                                <p class="text-gray-900">01/01/2023</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày hết hạn</p>
                                <p class="text-gray-900">01/01/2025</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Trạng thái</p>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Đang bảo hành
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Dự án</p>
                                <p class="text-gray-900">Dự án Viễn thông Hà Nội</p>
                            </div>
                        </div>

                        <div class="mt-6 text-center">
                            <button id="warranty-qr-btn"
                                class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg flex items-center justify-center w-full transition-colors">
                                <i class="fas fa-qrcode mr-2"></i> Hiển thị QR Code bảo hành
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- QR Code Modal -->
    <div id="qr-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">QR Code bảo hành</h3>
                <button type="button" onclick="closeQrModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="text-center mb-4">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=DEV-SPA-001" alt="QR Code" class="mx-auto">
                <p class="text-sm text-gray-600 mt-2">Quét mã QR này để kiểm tra thông tin bảo hành thiết bị</p>
            </div>
            <div class="flex justify-between space-x-3">
                <button type="button"
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex-1">
                    <i class="fas fa-download mr-2"></i> Tải xuống
                </button>
                <button type="button"
                    class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors flex-1">
                    <i class="fas fa-print mr-2"></i> In mã QR
                </button>
            </div>
        </div>
    </div>

    <script>
        function generateQR() {
            document.getElementById('qr-modal').classList.remove('hidden');
        }

        function closeQrModal() {
            document.getElementById('qr-modal').classList.add('hidden');
        }

        document.getElementById('warranty-qr-btn').addEventListener('click', function() {
            generateQR();
        });
    </script>
</body>

</html> 