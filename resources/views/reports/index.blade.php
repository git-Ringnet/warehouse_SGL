<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo thống kê - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Báo cáo thống kê</h1>
        </header>

        <main class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Báo cáo thống kê chi tiết vật tư xuất cho kho Z755 -->
                <a href="{{ url('report/material_export_z755') }}" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-box text-blue-500 text-xl"></i>
                        </div>
                        <h2 class="ml-4 text-lg font-semibold text-gray-800">Thống kê chi tiết vật tư xuất cho kho Z755</h2>
                    </div>
                    <p class="text-gray-600">Báo cáo thống kê chi tiết vật tư xuất cho kho Z755 theo tuần/tháng/năm</p>
                </a>

                <!-- Báo cáo thống kê chi tiết thiết bị thành phẩm -->
                <a href="{{ url('report/finished_product_by_material') }}" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-laptop text-green-500 text-xl"></i>
                        </div>
                        <h2 class="ml-4 text-lg font-semibold text-gray-800">Thống kê chi tiết thiết bị thành phẩm</h2>
                    </div>
                    <p class="text-gray-600">Báo cáo thống kê chi tiết thiết bị thành phẩm theo vật tư đã xuất cho Z755 theo tuần/tháng/năm</p>
                </a>

                <!-- Báo cáo thống kê chi tiết module hỏng -->
                <a href="{{ url('report/defective_modules') }}" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        </div>
                        <h2 class="ml-4 text-lg font-semibold text-gray-800">Thống kê chi tiết module hỏng</h2>
                    </div>
                    <p class="text-gray-600">Báo cáo thống kê chi tiết module hỏng trong quá trình sản xuất theo tuần/tháng/năm</p>
                </a>

                <!-- Báo cáo thống kê thiết bị nhập kho -->
                <a href="{{ url('report/finished_product_import') }}" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="fas fa-warehouse text-purple-500 text-xl"></i>
                        </div>
                        <h2 class="ml-4 text-lg font-semibold text-gray-800">Thống kê thiết bị nhập kho</h2>
                    </div>
                    <p class="text-gray-600">Báo cáo thống kê số lượng thiết bị thành phẩm được nhập kho</p>
                </a>

                <!-- Báo cáo thống kê thiết bị xuất đi -->
                <a href="{{ url('report/product_export_by_project') }}" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="fas fa-truck text-yellow-500 text-xl"></i>
                        </div>
                        <h2 class="ml-4 text-lg font-semibold text-gray-800">Thống kê thiết bị xuất đi</h2>
                    </div>
                    <p class="text-gray-600">Báo cáo thống kê số lượng thiết bị xuất đi cho từng dự án</p>
                </a>

                <!-- Báo cáo chi tiết lịch sử bảo trì -->
                <a href="{{ url('report/maintenance_history') }}" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="bg-indigo-100 p-3 rounded-full">
                            <i class="fas fa-tools text-indigo-500 text-xl"></i>
                        </div>
                        <h2 class="ml-4 text-lg font-semibold text-gray-800">Chi tiết lịch sử bảo trì</h2>
                    </div>
                    <p class="text-gray-600">Báo cáo chi tiết lịch sử bảo trì cho từng dự án</p>
                </a>

                <!-- Báo cáo chi tiết thiết bị bảo hành -->
                <a href="{{ url('report/warranty_repair_success') }}" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="bg-teal-100 p-3 rounded-full">
                            <i class="fas fa-check-circle text-teal-500 text-xl"></i>
                        </div>
                        <h2 class="ml-4 text-lg font-semibold text-gray-800">Chi tiết thiết bị bảo hành sửa chữa</h2>
                    </div>
                    <p class="text-gray-600">Báo cáo chi tiết thiết bị bảo hành xử lý sửa chữa thành công</p>
                </a>

                <!-- Báo cáo chi tiết thiết bị xử lý bảo hành nhập kho -->
                <a href="{{ url('report/warranty_product_return') }}" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="bg-pink-100 p-3 rounded-full">
                            <i class="fas fa-undo-alt text-pink-500 text-xl"></i>
                        </div>
                        <h2 class="ml-4 text-lg font-semibold text-gray-800">Chi tiết thiết bị xử lý và nhập kho bảo hành</h2>
                    </div>
                    <p class="text-gray-600">Báo cáo chi tiết thiết bị đã xử lý và nhập kho bảo hành theo tuần/tháng/năm</p>
                </a>

                <!-- Báo cáo vật tư nhập kho -->
                <a href="{{ url('report/inventory_import') }}" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="fas fa-clipboard-list text-orange-500 text-xl"></i>
                        </div>
                        <h2 class="ml-4 text-lg font-semibold text-gray-800">Chi tiết vật tư nhập kho</h2>
                    </div>
                    <p class="text-gray-600">Báo cáo thống kê chi tiết vật tư nhập kho</p>
                </a>
            </div>
        </main>
    </div>

    <!-- JavaScript để xử lý các chức năng trên trang -->
    <script>
        // Javascript code can be added here if needed
    </script>
</body>

</html> 