<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực bảo hành - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm py-4 px-6">
            <div class="max-w-5xl mx-auto flex justify-between items-center">
                <div class="flex items-center">
                    <span class="ml-3 text-xl font-bold text-gray-800">Hệ thống xác thực bảo hành</span>
                </div>
                <div>
                    <a href="#" class="text-blue-600 hover:text-blue-800">Liên hệ hỗ trợ</a>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-grow py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <!-- Verified Badge -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6 text-center">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-4">
                        <i class="fas fa-check-circle text-5xl text-green-500"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Thiết bị được bảo hành chính hãng</h1>
                    <p class="text-gray-600">Thiết bị <strong>Radio SPA Pro</strong> đang trong thời gian bảo hành</p>
                    <div class="mt-4 inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full">
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt mr-2"></i>
                            <span>Còn hiệu lực đến ngày 01/01/2025</span>
                        </div>
                    </div>
                </div>

                <!-- Device Information -->
                <div class="bg-white rounded-lg shadow-lg mb-6 overflow-hidden">
                    <div class="px-6 py-4 bg-blue-600">
                        <h2 class="text-lg font-semibold text-white">Thông tin thiết bị</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Mã thiết bị</p>
                                        <p class="text-gray-900 font-medium">DEV-SPA-001</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Tên thiết bị</p>
                                        <p class="text-gray-900 font-medium">Radio SPA Pro</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Serial</p>
                                        <p class="text-gray-900">SER123456</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Ngày sản xuất</p>
                                        <p class="text-gray-900">15/12/2022</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warranty Information -->
                <div class="bg-white rounded-lg shadow-lg mb-6 overflow-hidden">
                    <div class="px-6 py-4 bg-green-600">
                        <h2 class="text-lg font-semibold text-white">Thông tin bảo hành</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Gói bảo hành</p>
                                        <p class="text-gray-900 font-medium">Premium - 2 năm</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Ngày kích hoạt</p>
                                        <p class="text-gray-900">01/01/2023</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Ngày hết hạn</p>
                                        <p class="text-gray-900">01/01/2025</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Trạng thái</p>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Đang bảo hành
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Thời gian còn lại</p>
                                        <p class="text-gray-900">1 năm 8 tháng</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modules Information -->
                <div class="bg-white rounded-lg shadow-lg mb-6 overflow-hidden">
                    <div class="px-6 py-4 bg-purple-600">
                        <h2 class="text-lg font-semibold text-white">Các module trong thiết bị</h2>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã module
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên module
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Phiên bản
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Trạng thái
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">MOD-GPS-001</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">GPS Module</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">v1.2</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                Hoạt động tốt
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">MOD-CPU-002</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU Module</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">v2.0</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                Hoạt động tốt
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">MOD-ANT-003</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Antenna Module</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">v1.5</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                Hoạt động tốt
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">MOD-PWR-004</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Power Module</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">v2.1</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                                Hoạt động tốt
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Support Contact -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gray-800">
                        <h2 class="text-lg font-semibold text-white">Liên hệ hỗ trợ kỹ thuật</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-phone text-blue-500"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm text-gray-500">Hotline hỗ trợ kỹ thuật</p>
                                        <p class="text-gray-900 font-medium">1900 1234</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-envelope text-blue-500"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm text-gray-500">Email hỗ trợ</p>
                                        <p class="text-gray-900 font-medium">support@sgl.com.vn</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="#" class="inline-flex items-center bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors">
                                <i class="fas fa-headset mr-2"></i>
                                Yêu cầu hỗ trợ kỹ thuật
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html> 