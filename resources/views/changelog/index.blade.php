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
                    <i class="fas fa-file-pdf mr-2"></i> Xuất FDF
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
                        <label for="activity_type" class="block text-sm font-medium text-gray-700 mb-1">Loại hình</label>
                        <select id="activity_type" name="activity_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Tất cả</option>
                            <option value="assembly">Lắp ráp</option>
                            <option value="export">Xuất kho</option>
                            <option value="repair">Sửa chữa</option>
                            <option value="recall">Thu hồi</option>
                            <option value="import">Nhập kho</option>
                            <option value="transfer">Chuyển kho</option>
                        </select>
                    </div>
                    <div>
                        <label for="document_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu</label>
                        <input type="text" id="document_code" name="document_code" placeholder="Nhập mã phiếu..." class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="mt-4">
                    <div class="relative">
                        <input type="text" id="search" placeholder="Tìm kiếm theo mã vật tư, tên vật tư, người thực hiện..."
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thời gian</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã Vật tư/Thành Phẩm/Hàng Hóa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên Vật tư/Thành Phẩm/Hàng Hóa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại hình</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã phiếu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mô tả</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người thực hiện</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Chú thích</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Xem Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <!-- Sample data -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">15/12/2023 08:30:12</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">VT001</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Bo mạch điều khiển chính v2.1</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Lắp ráp
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">LR001</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">5</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Sử dụng vật tư cho phiếu lắp ráp thiết bị IoT</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn A</td>
                            <td class="px-4 py-3 text-sm text-gray-500">Đã kiểm tra chất lượng</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">15/12/2023 10:15:45</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">SP002</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Cảm biến nhiệt độ độ chính xác cao</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Xuất kho
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">XK002</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">10</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Xuất kho giao cho khách hàng ABC</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Trần Thị B</td>
                            <td class="px-4 py-3 text-sm text-gray-500">Giao hàng theo hợp đồng HD001</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">16/12/2023 14:30:00</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">DEV001</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Bộ điều khiển chính - SN001122</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Sửa chữa
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">SC003</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">1</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Bảo trì định kỳ và thay thế linh kiện</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Lê Văn C</td>
                            <td class="px-4 py-3 text-sm text-gray-500">Thiết bị hoạt động bình thường</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">17/12/2023 09:22:30</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">SP003</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Màn hình hiển thị TFT 7 inch</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Thu hồi
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">TH004</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">3</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Thu hồi sản phẩm lỗi từ khách hàng</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Phạm Thị D</td>
                            <td class="px-4 py-3 text-sm text-gray-500">Lỗi màn hình hiển thị</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">18/12/2023 11:45:15</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">VT025</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Dây cáp nguồn 12V</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    Nhập kho
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">NK005</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">50</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Nhập kho vật tư mới từ nhà cung cấp</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Hoàng Văn E</td>
                            <td class="px-4 py-3 text-sm text-gray-500">Đã kiểm tra chất lượng</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">19/12/2023 15:20:00</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">HH010</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Hộp đựng thiết bị nhựa ABS</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                    Chuyển kho
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">CK006</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">25</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Chuyển từ kho Hà Nội sang kho Hồ Chí Minh</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Vũ Thị F</td>
                            <td class="px-4 py-3 text-sm text-gray-500">Phân phối theo kế hoạch</td>
                            <td class="px-4 py-3 whitespace-nowrap">
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
                    Hiển thị <span class="font-medium">1</span> đến <span class="font-medium">6</span> của <span class="font-medium">6</span> bản ghi
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
                            <p class="text-sm font-medium text-gray-500">Thời gian:</p>
                            <p class="text-sm text-gray-900">15/12/2023 08:30:12</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Mã vật tư:</p>
                            <p class="text-sm text-gray-900">VT001</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Tên vật tư:</p>
                            <p class="text-sm text-gray-900">Bo mạch điều khiển chính v2.1</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Loại hình:</p>
                            <p class="text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Lắp ráp
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Mã phiếu:</p>
                            <p class="text-sm text-gray-900">LR001</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Số lượng:</p>
                            <p class="text-sm text-gray-900">5 cái</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Người thực hiện:</p>
                            <p class="text-sm text-gray-900">Nguyễn Văn A</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Mô tả:</p>
                            <p class="text-sm text-gray-900">Sử dụng vật tư cho phiếu lắp ráp thiết bị IoT Smart City theo hợp đồng HD001/2024. Vật tư đã được kiểm tra chất lượng và đáp ứng yêu cầu kỹ thuật.</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Chú thích:</p>
                            <p class="text-sm text-gray-900">Đã kiểm tra chất lượng - Vật tư đạt tiêu chuẩn ISO 9001 và đã qua kiểm định chất lượng đầu vào.</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Thông tin chi tiết:</p>
                            <p class="text-sm text-gray-900">
                                - Mã phiếu lắp ráp: LR001/2023<br>
                                - Sản phẩm đích: Thiết bị IoT Smart City<br>
                                - Kho xuất: Kho vật tư chính<br>
                                - Vị trí: Kệ A-02, Ngăn 15<br>
                                - Thời gian hoàn thành: 08:45:30<br>
                                - Trạng thái: Đã hoàn thành
                            </p>
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
                                <p class="text-xs text-gray-500 mt-1">Hình ảnh thành phẩm</p>
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