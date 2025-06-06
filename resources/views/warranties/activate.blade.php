<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kích hoạt bảo hành - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Kích hoạt bảo hành</h1>
                <div class="ml-4 px-2 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">
                    ID: DEV-RAD-004
                </div>
            </div>
            <a href="{{ asset('warranties') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <!-- Device Info Summary -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin thiết bị</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Mã thiết bị</p>
                        <p class="text-gray-900 font-medium">DEV-RAD-004</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tên thiết bị</p>
                        <p class="text-gray-900 font-medium">Radio SPA Pro</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Khách hàng</p>
                        <p class="text-gray-900">Công ty Viễn thông PQR</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Dự án</p>
                        <p class="text-gray-900">Dự án Radio SPA</p>
                    </div>
                </div>
            </div>

            <form action="#" method="POST">
                @csrf

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-shield-alt text-green-500 mr-2"></i>
                        Thông tin kích hoạt bảo hành
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="warranty_package"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Gói bảo hành <span
                                    class="text-red-500">*</span></label>
                            <input type="number" placeholder="Nhập số năm bảo hành"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none">
                        </div>
                        <div>
                            <label for="activation_date"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kích hoạt <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="activation_date" name="activation_date" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày hết
                                hạn</label>
                            <input type="date" id="expiry_date" name="expiry_date" disabled
                                class="w-full border border-gray-300 bg-gray-100 rounded-lg px-3 py-2 focus:outline-none">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="warranty_notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="warranty_notes" name="warranty_notes" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập ghi chú về bảo hành"></textarea>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-cog text-blue-500 mr-2"></i>
                        Tùy chọn bổ sung
                    </h2>

                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="send_notification" name="send_notification" type="checkbox" checked
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="send_notification" class="font-medium text-gray-700">Gửi thông báo kích hoạt
                                    đến khách hàng</label>
                                <p class="text-gray-500">Gửi email thông báo kích hoạt bảo hành đến khách hàng</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="generate_qr" name="generate_qr" type="checkbox" checked
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="generate_qr" class="font-medium text-gray-700">Tạo mã QR Code</label>
                                <p class="text-gray-500">Tạo mã QR cho khách hàng có thể kiểm tra thông tin bảo hành</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Xác nhận kích hoạt -->
                <div class="bg-yellow-50 rounded-xl shadow-md p-6 border border-yellow-200 mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center mr-4">
                            <i class="fas fa-exclamation-triangle text-2xl text-yellow-500"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Xác nhận kích hoạt</h3>
                            <p class="text-gray-600">Bạn đang chuẩn bị kích hoạt bảo hành cho thiết bị này. Hành động
                                này không thể hoàn tác.</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="confirm_activate" name="confirm_activate" type="checkbox" required
                                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="confirm_activate" class="font-medium text-gray-700 required">Tôi xác nhận
                                    rằng thông tin đã được kiểm tra và đồng ý kích hoạt bảo hành <span
                                        class="text-red-500">*</span></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ asset('warranties') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit"
                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> Kích hoạt bảo hành
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const warrantyPackage = document.getElementById('warranty_package');
            const activationDate = document.getElementById('activation_date');
            const expiryDate = document.getElementById('expiry_date');
            const generateCodeBtn = document.getElementById('generate-code');
            const activationCode = document.getElementById('activation_code');

            // Set today's date as default
            const today = new Date();
            const formattedToday = today.toISOString().split('T')[0];
            activationDate.value = formattedToday;

            // Update expiry date based on package
            function updateExpiryDate() {
                if (activationDate.value && warrantyPackage.value) {
                    let years = 1; // Default 1 year

                    switch (warrantyPackage.value) {
                        case 'basic':
                            years = 1;
                            break;
                        case 'standard':
                        case 'premium':
                            years = 2;
                            break;
                        case 'extended':
                            years = 3;
                            break;
                    }

                    const startDate = new Date(activationDate.value);
                    const expiry = new Date(startDate);
                    expiry.setFullYear(startDate.getFullYear() + years);
                    expiryDate.value = expiry.toISOString().split('T')[0];
                }
            }

            // Generate random activation code
            function generateRandomCode() {
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                let result = '';
                for (let i = 0; i < 12; i++) {
                    if (i > 0 && i % 4 === 0) {
                        result += '-';
                    }
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return result;
            }

            // Event listeners
            warrantyPackage.addEventListener('change', updateExpiryDate);
            activationDate.addEventListener('change', updateExpiryDate);

            generateCodeBtn.addEventListener('click', function() {
                activationCode.value = generateRandomCode();
            });

            // Initialize expiry date
            updateExpiryDate();
        });
    </script>
</body>

</html>
