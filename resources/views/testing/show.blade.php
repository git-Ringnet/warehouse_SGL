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
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .print-only {
                display: block;
            }
            body {
                font-size: 12pt;
                color: #000;
                background-color: #fff;
            }
            .content-area {
                margin: 0;
                padding: 0;
            }
            .page-break {
                page-break-before: always;
            }
        }
        .print-only {
            display: none;
        }
    </style>
</head>
<body>
    <x-sidebar-component class="no-print" />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40 no-print">
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
                <button onclick="window.print()" class="h-10 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-print mr-2"></i> In phiếu
                </button>
                <a href="{{ url('/testing/1/edit') }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Sửa
                </a>
            </div>
        </header>

        <!-- Print Header (only visible when printing) -->
        <div class="print-only p-6 border-b border-gray-300 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="SGL Logo" class="h-16 mr-4">
                    <div>
                        <h1 class="text-xl font-bold">CÔNG TY CỔ PHẦN CÔNG NGHỆ SGL</h1>
                        <p class="text-gray-600">Địa chỉ: 123 Đường XYZ, Quận ABC, TP. HCM</p>
                    </div>
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-bold uppercase">Phiếu kiểm thử</h2>
                    <p class="text-lg font-bold text-blue-800">QA-24060001</p>
                </div>
            </div>
        </div>

        <main class="p-6 space-y-6">
            <!-- Thông tin cơ bản -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
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
                            <p class="text-base text-gray-800 font-semibold">Kiểm thử Vật tư/Hàng hóa</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Loại Vật tư hoặc hàng hóa</p>
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
                            <p class="text-sm text-gray-500 font-medium mb-1">Số lượng</p>
                            <p class="text-base text-gray-800 font-semibold">20 thiết bị</p>
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
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Kết quả kiểm thử</p>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> 90% Đạt
                                </span>
                                <span class="text-sm text-gray-600">(18 Đạt / 2 Không đạt)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hạng mục kiểm thử và kết quả -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Hạng mục kiểm thử và kết quả</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hạng mục</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kết quả</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra phần cứng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">Kiểm tra chân cắm và các kết nối đầy đủ</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra phần mềm</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">Firmware v3.2.1 hoạt động ổn định</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra kết nối/truyền thông</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">Kết nối mạng ổn định, tốc độ đạt yêu cầu</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra chức năng</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">Tất cả chức năng hoạt động theo yêu cầu</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kiểm tra độ bền (stress test)</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">Hoạt động ổn định trong 72 giờ liên tục</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Chi tiết kết quả kiểm thử -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Chi tiết kết quả kiểm thử</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center mb-2">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-green-800">Số lượng Đạt: 18</h3>
                                <p class="text-sm text-green-700">90% của tổng số lượng kiểm thử</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center mb-2">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-4">
                                <i class="fas fa-times-circle text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-red-800">Số lượng Không Đạt: 2</h3>
                                <p class="text-sm text-red-700">10% của tổng số lượng kiểm thử</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h3 class="font-medium text-gray-800 mb-2">Lý do không đạt:</h3>
                    <p class="text-gray-700">2 module có vấn đề về kết nối anten, cần kiểm tra lại mạch RF.</p>
                </div>
                
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="font-medium text-blue-800 mb-2">Kết luận:</h3>
                    <p class="text-blue-700">Module 4G đạt chất lượng để đưa vào sản xuất. Đa số thông số kỹ thuật đều đạt yêu cầu. Cần loại bỏ 2 module bị lỗi anten.</p>
                </div>
            </div>
            
            <!-- Xác nhận và phê duyệt -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Xác nhận và phê duyệt</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center p-4">
                        <div class="font-medium text-gray-800 mb-2">Người kiểm thử</div>
                        <div class="h-20 flex items-end justify-center">
                            <p class="font-medium">Nguyễn Văn A</p>
                        </div>
                        <div class="pt-2 border-t border-gray-300 mt-2">
                            <p class="text-sm text-gray-600">15/06/2024</p>
                        </div>
                    </div>
                    
                    <div class="text-center p-4">
                        <div class="font-medium text-gray-800 mb-2">Người phụ trách kiểm thử</div>
                        <div class="h-20 flex items-end justify-center">
                            <p class="font-medium">Trần Thị B</p>
                        </div>
                        <div class="pt-2 border-t border-gray-300 mt-2">
                            <p class="text-sm text-gray-600">15/06/2024</p>
                        </div>
                    </div>
                    
                    <div class="text-center p-4">
                        <div class="font-medium text-gray-800 mb-2">Phê duyệt</div>
                        <div class="h-20 flex items-end justify-center">
                            <p class="font-medium">Lê Văn C</p>
                        </div>
                        <div class="pt-2 border-t border-gray-300 mt-2">
                            <p class="text-sm text-gray-600">16/06/2024</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin thêm -->
            <div class="print-only mt-8 pt-8 border-t border-gray-300">
                <div class="text-sm text-gray-600">
                    <p>© Công ty Cổ phần Công nghệ SGL</p>
                    <p>Điện thoại: (028) 1234 5678 | Email: info@sgl.com.vn | Website: www.sgl.com.vn</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Function to handle printing
        function printTest() {
            window.print();
        }
    </script>
</body>
</html> 