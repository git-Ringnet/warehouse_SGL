<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết kho hàng - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết kho hàng</h1>
            </div>
            <div class="flex space-x-2">
                <a href="{{ asset('warehouses/edit') }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <a href="{{ asset('warehouses') }}"
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
                                <h2 class="text-2xl font-bold text-gray-800">Kho Hà Nội</h2>
                                <p class="text-gray-600 mt-1">Mã kho: KHO-HN</p>
                            </div>
                            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end">
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-user text-blue-500 mr-2"></i>
                                    <span class="text-blue-500 font-medium">Nguyễn Văn A</span>
                                </div>
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-phone text-green-500 mr-2"></i>
                                    <span class="text-green-500 font-medium">0912345678</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin chi tiết -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin kho hàng</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500">Tên kho</p>
                        <p class="text-gray-900 font-medium">Kho Hà Nội</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Mã kho</p>
                        <p class="text-gray-900 font-medium">KHO-HN</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Địa chỉ</p>
                        <p class="text-gray-900">Số 15, Đường Trần Duy Hưng, Cầu Giấy, Hà Nội</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Người quản lý</p>
                        <p class="text-gray-900">Nguyễn Văn A</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Số điện thoại</p>
                        <p class="text-gray-900">0912345678</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="text-gray-900">nguyenvana@sgl.com</p>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-4 mb-6">
                    <p class="text-sm text-gray-500">Mô tả</p>
                    <p class="text-gray-900 mt-1">Kho chính tại Hà Nội, chuyên lưu trữ thiết bị và sản phẩm cho khu vực miền Bắc. Diện tích 1,200m², bao gồm khu vực đóng gói và khu vực bảo quản riêng biệt cho các thiết bị nhạy cảm.</p>
                </div>

                <!-- Danh sách sản phẩm trong kho -->
                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Sản phẩm trong kho</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h5 class="text-sm font-medium text-gray-700">Tổng số sản phẩm: <span class="text-blue-600">145</span></h5>
                            <div class="flex items-center gap-2">
                                <input type="text" placeholder="Tìm kiếm sản phẩm" class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                                <button class="bg-blue-100 text-blue-600 px-3 py-1 rounded-lg text-sm hover:bg-blue-200 transition-colors">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">STT</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Mã SP</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Tên sản phẩm</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Số lượng</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Loại</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">1</td>
                                    <td class="py-2 text-sm text-gray-700">SP-0001</td>
                                    <td class="py-2 text-sm text-gray-700">Radio SPA Pro</td>
                                    <td class="py-2 text-sm text-gray-700">25</td>
                                    <td class="py-2 text-sm text-gray-700">Mới</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">2</td>
                                    <td class="py-2 text-sm text-gray-700">SP-0005</td>
                                    <td class="py-2 text-sm text-gray-700">GPS Tracker X2</td>
                                    <td class="py-2 text-sm text-gray-700">15</td>
                                    <td class="py-2 text-sm text-gray-700">Mới</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">3</td>
                                    <td class="py-2 text-sm text-gray-700">SP-0010</td>
                                    <td class="py-2 text-sm text-gray-700">Bộ đàm SGL X10</td>
                                    <td class="py-2 text-sm text-gray-700">30</td>
                                    <td class="py-2 text-sm text-gray-700">Mới</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">4</td>
                                    <td class="py-2 text-sm text-gray-700">SP-0012</td>
                                    <td class="py-2 text-sm text-gray-700">Thiết bị định vị SGL-GPS</td>
                                    <td class="py-2 text-sm text-gray-700">18</td>
                                    <td class="py-2 text-sm text-gray-700">Mới</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">5</td>
                                    <td class="py-2 text-sm text-gray-700">SP-0015</td>
                                    <td class="py-2 text-sm text-gray-700">Camera an ninh SGL-CAM</td>
                                    <td class="py-2 text-sm text-gray-700">22</td>
                                    <td class="py-2 text-sm text-gray-700">Mới</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="mt-4 flex justify-between items-center">
                            <div class="text-sm text-gray-600">Hiển thị 1 - 5 của 25 sản phẩm</div>
                            <div class="flex space-x-1">
                                <button class="w-8 h-8 flex items-center justify-center rounded bg-blue-600 text-white">
                                    1
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-200 text-gray-600">
                                    2
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-200 text-gray-600">
                                    3
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded hover:bg-gray-200 text-gray-600">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html> 