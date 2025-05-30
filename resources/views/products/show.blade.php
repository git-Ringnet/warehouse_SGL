<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết thành phẩm - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết thành phẩm</h1>
            </div>
            <div class="flex space-x-2">
                <a href="{{ asset('products/edit') }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <a href="{{ asset('products') }}"
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
                                <h2 class="text-2xl font-bold text-gray-800">Radio SPA Pro</h2>
                                <p class="text-gray-600 mt-1">Mã SP: SP-0001</p>
                                <p class="text-gray-600 mt-1">Serial: SER123456</p>
                            </div>
                            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end">
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                                    <span class="text-blue-500 font-medium">Kho Hà Nội</span>
                                </div>
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-tag text-blue-500 mr-2"></i>
                                    <span class="text-blue-500 font-medium">Mới</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin chi tiết -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin sản phẩm</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500">Tên sản phẩm</p>
                        <p class="text-gray-900 font-medium">Radio SPA Pro</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Mã sản phẩm</p>
                        <p class="text-gray-900 font-medium">SP-0001</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Serial</p>
                        <p class="text-gray-900 font-medium">SER123456</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Vị trí hiện tại</p>
                        <p class="text-gray-900">Kho Hà Nội</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Loại sản phẩm</p>
                        <p class="text-gray-900">Mới</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ngày tạo</p>
                        <p class="text-gray-900">05/06/2023</p>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-4 mb-6">
                    <p class="text-sm text-gray-500">Mô tả sản phẩm</p>
                    <p class="text-gray-900 mt-1">Radio SPA Pro là thiết bị viễn thông thế hệ mới nhất của SGL, được trang bị module GPS NEO-6M và hệ thống thu phát sóng hai chiều tiên tiến. Sản phẩm phù hợp cho các công trình viễn thông và trạm phát sóng.</p>
                </div>

                <!-- Thành phần linh kiện -->
                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Thành phần linh kiện</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Mã vật tư</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Serial vật tư</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Tên vật tư</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Số lượng</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">VT-0001</td>
                                    <td class="py-2 text-sm text-gray-700">SER-VT-001</td>
                                    <td class="py-2 text-sm text-gray-700">Vỏ thiết bị Radio SPA</td>
                                    <td class="py-2 text-sm text-gray-700">1</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">VT-0002</td>
                                    <td class="py-2 text-sm text-gray-700">SER-VT-002</td>
                                    <td class="py-2 text-sm text-gray-700">Module GPS NEO-6M</td>
                                    <td class="py-2 text-sm text-gray-700">1</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">VT-0003</td>
                                    <td class="py-2 text-sm text-gray-700">SER-VT-003</td>
                                    <td class="py-2 text-sm text-gray-700">Bo mạch điều khiển</td>
                                    <td class="py-2 text-sm text-gray-700">1</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">VT-0004</td>
                                    <td class="py-2 text-sm text-gray-700">SER-VT-004</td>
                                    <td class="py-2 text-sm text-gray-700">Ăng-ten thu phát</td>
                                    <td class="py-2 text-sm text-gray-700">2</td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">VT-0005</td>
                                    <td class="py-2 text-sm text-gray-700">SER-VT-005</td>
                                    <td class="py-2 text-sm text-gray-700">Pin Lithium 2000mAh</td>
                                    <td class="py-2 text-sm text-gray-700">1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal xác nhận xóa -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xác nhận xóa</h3>
            <p class="text-gray-700 mb-6">Bạn có chắc chắn muốn xóa thành phẩm này? Hành động này không thể hoàn tác.</p>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeDeleteModal()">
                    Hủy
                </button>
                <form action="#" method="POST">
                    <button type="button"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                        Xác nhận xóa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</body>

</html> 