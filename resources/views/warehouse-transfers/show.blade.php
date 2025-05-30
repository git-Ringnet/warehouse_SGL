<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu chuyển kho - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu chuyển kho</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Mã phiếu: CK001
                </div>
                <div class="ml-2 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    Hoàn thành
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url('/warehouse-transfers') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ url('/warehouse-transfers/1/edit') }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
            </div>
        </header>

        <main class="p-6 space-y-6">
            <!-- Thông tin cơ bản -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">Thông tin phiếu chuyển kho</h2>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Ngày tạo: 01/06/2024
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cột 1 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Serial được chuyển</p>
                            <p class="text-base text-gray-800 font-semibold">SER-VT001-001</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Vật tư tương ứng</p>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded text-xs mr-2">Linh kiện</span>
                                    <span class="text-base text-gray-800 font-semibold">LK001 - Ốc vít 10mm</span>
                                    <span class="ml-2 text-sm text-gray-600">(Số lượng: 80)</span>
                                    <a href="{{ url('/inventory?material_id=C1') }}" class="ml-2 text-blue-500 hover:text-blue-700 text-sm">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                                
                                <div class="flex items-center">
                                    <span class="px-1.5 py-0.5 bg-green-100 text-green-800 rounded text-xs mr-2">Thành phẩm</span>
                                    <span class="text-base text-gray-800 font-semibold">TP002 - Hộp điện âm tường</span>
                                    <span class="ml-2 text-sm text-gray-600">(Số lượng: 20)</span>
                                    <a href="{{ url('/inventory?material_id=P2') }}" class="ml-2 text-blue-500 hover:text-blue-700 text-sm">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Kho nguồn</p>
                            <p class="text-base text-gray-800 font-semibold">Kho chính</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Số lượng</p>
                            <p class="text-base text-gray-800 font-semibold">100</p>
                        </div>
                    </div>
                    
                    <!-- Cột 2 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Kho đích</p>
                            <p class="text-base text-gray-800 font-semibold">Kho linh kiện</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ngày chuyển kho</p>
                            <p class="text-base text-gray-800 font-semibold">01/06/2024</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Nhân viên thực hiện</p>
                            <div class="flex items-center">
                                <span class="text-base text-gray-800 font-semibold">Nguyễn Văn A</span>
                                <a href="{{ url('/employees/1') }}" class="ml-2 text-blue-500 hover:text-blue-700 text-sm">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Hoàn thành
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Ghi chú -->
                <div class="mt-4">
                    <p class="text-sm text-gray-500 font-medium mb-1">Ghi chú</p>
                    <p class="text-base text-gray-800">Chuyển bổ sung</p>
                </div>
            </div>
            
            <!-- Lịch sử chuyển kho -->
           

           
        </main>
    </div>
</body>
</html> 