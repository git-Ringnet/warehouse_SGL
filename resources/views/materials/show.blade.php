<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết vật tư - SGL</title>
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
                <a href="{{ route('materials.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chi tiết vật tư</h1>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('materials.edit', $material->id) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors flex items-center">
                    <i class="fas fa-edit mr-2"></i> Sửa
                </a>
                <button type="button" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center" onclick="confirmDelete()">
                    <i class="fas fa-trash mr-2"></i> Xóa
                </button>
            </div>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md border border-gray-100 max-w-5xl mx-auto">
                <!-- Thông tin chính -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Hình ảnh và thông tin cơ bản -->
                    <div class="p-6 border-b md:border-b-0 md:border-r border-gray-200">
                        <div class="mb-6">
                            <div class="w-full h-64 bg-gray-100 rounded-lg overflow-hidden mb-4">
                                <img src="{{ asset('images/materials/module-gps.jpg') }}" alt="Pin lithium 3.7V" class="w-full h-full object-contain">
                            </div>
                            <div class="flex justify-center">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Mới
                                </span>
                            </div>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 mb-2">VT-0002</h2>
                        <h3 class="text-lg font-medium text-gray-700 mb-4">Module GPS NEO-6M</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Loại:</span>
                                <span class="font-medium text-gray-800">Linh kiện định vị</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Đơn vị:</span>
                                <span class="font-medium text-gray-800">Cái</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Số lượng tồn:</span>
                                <span class="font-medium text-gray-800">75</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Giá nhập:</span>
                                <span class="font-medium text-gray-800">250,000 ₫</span>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin chi tiết -->
                    <div class="p-6 col-span-2">
                        <div class="mb-8">
                            <h4 class="text-lg font-medium text-gray-800 mb-2">Mô tả</h4>
                            <p class="text-gray-700">
                                Module GPS NEO-6M là một module định vị vệ tinh GPS được tích hợp sẵn anten vào bo mạch. Module sử dụng chip NEO-6M của u-blox, một trong những chip GPS phổ biến và đáng tin cậy trên thị trường. Module này cung cấp khả năng định vị với độ chính xác cao, phù hợp cho nhiều ứng dụng theo dõi vị trí, định vị, và dẫn đường.
                            </p>
                        </div>

                        <div class="mb-8">
                            <h4 class="text-lg font-medium text-gray-800 mb-2">Thông số kỹ thuật</h4>
                            <ul class="list-disc list-inside space-y-1 text-gray-700">
                                <li>Chip GPS: u-blox NEO-6M</li>
                                <li>Điện áp hoạt động: 3.3V - 5V DC</li>
                                <li>Giao tiếp: UART (TTL)</li>
                                <li>Tần số cập nhật: 1Hz (mặc định)</li>
                                <li>Anten: Ceramic Patch Antenna tích hợp</li>
                                <li>EEPROM tích hợp để lưu cấu hình</li>
                                <li>LED báo trạng thái tín hiệu</li>
                            </ul>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-lg font-medium text-gray-800 mb-2">Thông tin kho</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Kho:</span>
                                        <span class="font-medium text-gray-800">Kho linh kiện</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Vị trí:</span>
                                        <span class="font-medium text-gray-800">Kệ D, Ngăn 2</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Ngày nhập:</span>
                                        <span class="font-medium text-gray-800">15/05/2023</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-lg font-medium text-gray-800 mb-2">Thông tin nhà cung cấp</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Nhà cung cấp:</span>
                                        <span class="font-medium text-gray-800">Công ty TNHH Linh kiện DEF</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Mã đơn hàng:</span>
                                        <span class="font-medium text-gray-800">PO-20230515</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Bảo hành:</span>
                                        <span class="font-medium text-gray-800">6 tháng</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h4 class="text-lg font-medium text-gray-800 mb-2">Ghi chú</h4>
                            <p class="text-gray-700 italic">
                                Cần kiểm tra trạng thái anten trước khi sử dụng. Nên tránh để gần các nguồn nhiễu điện từ để đảm bảo chất lượng tín hiệu.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Lịch sử giao dịch -->
                <div class="border-t border-gray-200 p-6">
                    <h4 class="text-lg font-medium text-gray-800 mb-4">Lịch sử giao dịch</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">NK-00125</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Nhập kho</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">100</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/05/2023</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Nhập kho lần đầu</td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">XK-00078</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Xuất kho</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">-15</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">20/06/2023</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Xuất cho dự án ABC</td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">XK-00092</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Xuất kho</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">-10</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">05/07/2023</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Xuất cho dự án XYZ</td>
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
            <p class="text-gray-700 mb-6">Bạn có chắc chắn muốn xóa vật tư này? Hành động này không thể hoàn tác.</p>
            <div class="flex justify-end space-x-3">
                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors" onclick="closeDeleteModal()">
                    Hủy
                </button>
                <form action="{{ route('materials.destroy', $material->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
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