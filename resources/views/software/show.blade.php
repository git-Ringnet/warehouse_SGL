<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phần mềm - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phần mềm</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Ứng dụng SGL Mobile
                </div>
                <div class="ml-2 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    v1.2.5
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url('/software') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ url('/software/1/edit') }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
            </div>
        </header>

        <main class="p-6 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">Thông tin cơ bản</h2>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-calendar-alt mr-1"></i> Tải lên: 15/06/2024
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cột 1 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Tên phần mềm</p>
                            <p class="text-base text-gray-800 font-semibold">Ứng dụng SGL Mobile</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Phiên bản</p>
                            <p class="text-base text-gray-800 font-semibold">1.2.5</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Loại phần mềm</p>
                            <p class="text-base text-gray-800 font-semibold">Ứng dụng di động</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Nền tảng</p>
                            <p class="text-base text-gray-800 font-semibold">Android</p>
                        </div>
                    </div>
                    
                    <!-- Cột 2 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ngày phát hành</p>
                            <p class="text-base text-gray-800 font-semibold">15/06/2024</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Hoạt động
                            </span>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Người tải lên</p>
                            <p class="text-base text-gray-800 font-semibold">Nguyễn Văn A</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- File Info -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin file</h2>
                
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-3 md:mb-0">
                        <i class="fas fa-mobile-alt text-green-500 text-4xl mr-4"></i>
                        <div>
                            <p class="text-lg font-medium text-gray-800">sgl_mobile_app_v1.2.5.apk</p>
                            <div class="mt-1 flex items-center flex-wrap gap-y-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">
                                    <i class="fas fa-mobile-alt mr-1"></i> APK
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                    <i class="fas fa-weight mr-1"></i> 25.4 MB
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-calendar mr-1"></i> 15/06/2024
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <a href="#" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-download mr-2"></i> Tải xuống
                    </a>
                </div>
                
                <div class="mt-4">
                    <p class="text-sm text-gray-500 mb-1">Đường dẫn lưu trữ:</p>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 font-mono text-sm text-gray-700 break-all">
                        /storage/app/public/software/mobile/sgl_mobile_app_v1.2.5.apk
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Mô tả phần mềm</h2>
                
                <div class="prose max-w-none text-gray-700">
                    <p>Ứng dụng quản lý kho hàng SGL trên nền tảng di động. Hỗ trợ quét mã vạch, kiểm kê và theo dõi tồn kho.</p>
                    <p class="mt-2">Các tính năng chính:</p>
                    <ul class="list-disc pl-5 space-y-1 mt-2">
                        <li>Quét mã vạch/QR code cho vật tư</li>
                        <li>Kiểm kê hàng tồn kho thời gian thực</li>
                        <li>Đồng bộ hóa dữ liệu với hệ thống chính</li>
                        <li>Gửi thông báo tức thì khi hàng tồn kho thay đổi</li>
                        <li>Xuất báo cáo PDF và Excel</li>
                        <li>Hỗ trợ làm việc ngoại tuyến</li>
                    </ul>
                </div>
            </div>
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