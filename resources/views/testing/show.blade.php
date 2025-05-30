<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu kiểm thử - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu kiểm thử</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    QA-24060001
                </div>
                <div class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Module 4G
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url('/testing') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ url('/testing/1/edit') }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Sửa
                </a>
            </div>
        </header>

        <main class="p-6 space-y-6">
            <!-- Thông tin cơ bản -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">Thông tin phiếu kiểm thử</h2>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-calendar-alt mr-1"></i> Ngày kiểm thử: 15/06/2024
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i> Hoàn thành
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cột 1 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Loại kiểm thử</p>
                            <p class="text-base text-gray-800 font-semibold">Kiểm thử linh kiện đầu vào</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Loại thiết bị/module</p>
                            <p class="text-base text-gray-800 font-semibold">Module 4G</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Serial/Mã thiết bị</p>
                            <p class="text-base text-gray-800 font-semibold">4G-MOD-2305621</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Nhà cung cấp</p>
                            <p class="text-base text-gray-800 font-semibold">Công ty ABC Electronics</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Mã lô</p>
                            <p class="text-base text-gray-800 font-semibold">LOT-2405-01</p>
                        </div>
                    </div>
                    
                    <!-- Cột 2 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ngày kiểm thử</p>
                            <p class="text-base text-gray-800 font-semibold">15/06/2024</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ngày sản xuất</p>
                            <p class="text-base text-gray-800 font-semibold">10/05/2024</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ngày nhập kho</p>
                            <p class="text-base text-gray-800 font-semibold">05/06/2024</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Người kiểm thử</p>
                            <p class="text-base text-gray-800 font-semibold">Nguyễn Văn A</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Hoàn thành
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hạng mục kiểm thử và kết quả -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Hạng mục kiểm thử và kết quả</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hạng mục</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kết quả</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra phần cứng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đã kiểm tra</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">Kiểm tra chân cắm và các kết nối đầy đủ</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra phần mềm</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đã kiểm tra</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">Firmware v3.2.1 hoạt động ổn định</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra kết nối/truyền thông</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đã kiểm tra</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">Kết nối mạng ổn định, tốc độ đạt yêu cầu</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra chức năng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đã kiểm tra</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">Tất cả chức năng hoạt động theo yêu cầu</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra độ bền (stress test)</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đã kiểm tra</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">Hoạt động ổn định trong 72 giờ liên tục</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Ghi nhận kết quả -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Kết quả kiểm thử</h2>
                
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center mb-2">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-4">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-800">Kết quả: Đạt</h3>
                            <p class="text-sm text-gray-600">Module hoạt động ổn định và đáp ứng tất cả yêu cầu kỹ thuật.</p>
                        </div>
                    </div>
                    
                    <div class="mt-2 pl-14">
                        <p class="text-sm text-gray-600"><span class="font-medium">Đánh giá của người kiểm thử:</span> Module 4G đạt chất lượng để đưa vào sản xuất. Tất cả thông số kỹ thuật đều đạt yêu cầu. Đặc biệt kết nối mạng ổn định trong điều kiện tín hiệu yếu.</p>
                    </div>
                </div>
                
                <!-- <div class="mb-4">
                    <h3 class="text-md font-medium text-gray-800 mb-2">Yêu cầu bổ sung (nếu có)</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Không có yêu cầu bổ sung.</p>
                    </div>
                </div> -->
                
                <!-- <div>
                    <h3 class="text-md font-medium text-gray-800 mb-2">Đề xuất</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Có thể tiến hành đặt hàng thêm module cùng loại từ nhà cung cấp này cho các đợt sản xuất tiếp theo.</p>
                    </div>
                </div> -->
            </div>
            
            <!-- Người phê duyệt -->
            <!-- <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin phê duyệt</h2>
                
                <div class="flex flex-col md:flex-row md:gap-6">
                    <div class="mb-4 md:mb-0 md:flex-1">
                        <h3 class="text-md font-medium text-gray-800 mb-2">Người kiểm thử</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-sm font-medium text-gray-800">Nguyễn Văn A</p>
                            <p class="text-sm text-gray-600 mt-1">Kỹ thuật viên QA</p>
                            <div class="mt-2 flex items-center">
                                <i class="fas fa-signature text-gray-600 mr-2"></i>
                                <p class="text-sm text-gray-600">Đã ký ngày 15/06/2024</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:flex-1">
                        <h3 class="text-md font-medium text-gray-800 mb-2">Quản lý phê duyệt</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-sm font-medium text-gray-800">Trần Văn B</p>
                            <p class="text-sm text-gray-600 mt-1">Quản lý phòng QA</p>
                            <div class="mt-2 flex items-center">
                                <i class="fas fa-signature text-gray-600 mr-2"></i>
                                <p class="text-sm text-gray-600">Đã ký ngày 16/06/2024</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </main>
    </div>
    
    <script>
        // Hàm toggleDropdown cho sidebar
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