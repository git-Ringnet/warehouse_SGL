<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu nhập kho - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu nhập kho</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Mã phiếu: NK001
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ url('/inventory-imports/1/edit') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Sửa
                </a>
                <button onclick="printDocument()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-print mr-2"></i> In
                </button>
                <a href="{{ url('/inventory-imports') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            <!-- Thông tin cơ bản -->
            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-800 mb-2">Phiếu nhập kho #NK001</h2>
                                <p class="text-gray-600">Ngày tạo: 01/06/2024 09:30:22</p>
                                <div class="mt-2">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        Đã nhập kho
                                    </span>
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Người tạo:</p>
                                    <p class="font-medium text-gray-800">Nguyễn Văn Quản Trị</p>
                                </div>
                                <div class="mt-2 text-right">
                                    <p class="text-sm text-gray-600">Số mặt hàng:</p>
                                    <p class="font-medium text-gray-800">1</p>
                                </div>
                                <div class="mt-2 text-right">
                                    <p class="text-sm text-gray-600">Tổng giá trị:</p>
                                    <p class="font-medium text-gray-800">5,000,000 VNĐ</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Thông tin vật tư -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 md:col-span-2">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-boxes mr-2 text-blue-500"></i>
                            Thông tin vật tư
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã vật tư</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phân loại</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn giá</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium">VT001</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Ốc vít 10mm</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Kg</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Linh kiện</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">100</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">50,000 VNĐ</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-medium">5,000,000 VNĐ</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">Tổng cộng:</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900">5,000,000 VNĐ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Thông tin nhà cung cấp và ghi chú -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-building mr-2 text-purple-500"></i>
                                Nhà cung cấp
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="mb-4">
                                <p class="text-sm text-gray-500">Tên nhà cung cấp</p>
                                <p class="font-medium text-gray-800">Công ty TNHH ABC</p>
                            </div>
                            <div class="mb-4">
                                <p class="text-sm text-gray-500">Liên hệ</p>
                                <p class="font-medium text-gray-800">0987654321</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Địa chỉ</p>
                                <p class="font-medium text-gray-800">123 Lê Lợi, Quận 1, TP.HCM</p>
                            </div>
                            <div class="mt-4 text-sm">
                                <a href="{{ url('/suppliers/1') }}" class="text-blue-500 hover:text-blue-700">
                                    <i class="fas fa-external-link-alt mr-1"></i> Xem chi tiết nhà cung cấp
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-orange-500"></i>
                                Thông tin bổ sung
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="mb-4">
                                <p class="text-sm text-gray-500">Kho nhập</p>
                                <p class="font-medium text-gray-800">Kho chính</p>
                            </div>
                            <div class="mb-4">
                                <p class="text-sm text-gray-500">Mã đơn hàng</p>
                                <p class="font-medium text-gray-800">ĐH-2024-06-01</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ghi chú</p>
                                <p class="font-medium text-gray-800">Nhập đợt 1</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- <div class="mt-6 bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-history mr-2 text-gray-500"></i>
                    Lịch sử hoạt động
                </h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="min-w-[100px] text-sm text-gray-500">01/06/2024<br>10:15:30</div>
                        <div class="ml-6 bg-gray-50 p-3 rounded-lg border border-gray-100 flex-grow">
                            <p class="text-gray-800"><span class="font-medium">Nguyễn Văn Quản Trị</span> đã chỉnh sửa phiếu nhập kho</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="min-w-[100px] text-sm text-gray-500">01/06/2024<br>09:30:22</div>
                        <div class="ml-6 bg-gray-50 p-3 rounded-lg border border-gray-100 flex-grow">
                            <p class="text-gray-800"><span class="font-medium">Nguyễn Văn Quản Trị</span> đã tạo phiếu nhập kho</p>
                        </div>
                    </div>
                </div>
            </div> -->
        </main>
    </div>

    <script>
        function printDocument() {
            window.print();
        }
    </script>
</body>
</html> 