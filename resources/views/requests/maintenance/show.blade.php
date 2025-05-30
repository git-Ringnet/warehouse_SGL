<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu đề xuất bảo trì dự án - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .sidebar {
            background: linear-gradient(180deg, #1a365d 0%, #0f2942 100%);
            transition: all 0.3s ease;
        }
        .content-area {
            margin-left: 256px;
            min-height: 100vh;
            background: #f8fafc;
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
                width: 70px;
            }
            .content-area {
                margin-left: 0 !important;
            }
        }
        .badge {
            border-radius: 9999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-blue {
            background-color: #ebf5ff;
            color: #2563eb;
        }
        .badge-green {
            background-color: #ecfdf5;
            color: #059669;
        }
        .badge-yellow {
            background-color: #fffbeb;
            color: #d97706;
        }
        .badge-red {
            background-color: #fef2f2;
            color: #dc2626;
        }
        .badge-purple {
            background-color: #f5f3ff;
            color: #7c3aed;
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu đề xuất bảo trì dự án</h1>
                <div class="ml-4 px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                    MNT-00{{ $id }}
                </div>
                <div class="ml-2 badge badge-green">
                    Đã duyệt
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ url('/requests/maintenance/'.$id.'/edit') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <a href="{{ url('/requests/maintenance/'.$id.'/preview') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors" target="_blank">
                    <i class="fas fa-file-excel mr-2"></i> Xem trước Excel
                </a>
                <a href="{{ url('/requests') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>
        
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin đề xuất</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Ngày đề xuất</p>
                            <p class="font-medium">25/05/2024</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Kỹ thuật đề xuất</p>
                            <p class="font-medium">Minh Trí</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Loại bảo trì</p>
                            <p class="font-medium">Bảo trì định kỳ</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Mức độ ưu tiên</p>
                            <p class="font-medium">Trung bình</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Tên dự án</p>
                            <p class="font-medium">Tân Thành</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Đối tác</p>
                            <p class="font-medium">Viễn Thông A</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Địa chỉ dự án</p>
                            <p class="font-medium">Phường Tân Thành, TP. Bà Rịa</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Tình trạng hiện tại của dự án</p>
                            <p class="font-medium">
                                Hệ thống đang hoạt động nhưng có hiện tượng không ổn định. Các thiết bị phát
                                thanh thỉnh thoảng mất kết nối với hệ thống trung tâm. Bộ điều khiển trung tâm
                                có dấu hiệu hoạt động chập chờn.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Chi tiết công việc bảo trì</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mô tả công việc</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Kiểm tra và cân chỉnh hệ thống phát thanh</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Thay thế bộ nguồn của thiết bị trung tâm</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Cập nhật phần mềm cho bộ điều khiển</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Kiểm tra và sửa chữa hệ thống dây dẫn</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thiết bị cần thay thế</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên thiết bị</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Nguồn dự phòng 24V</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Module kết nối không dây</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Vật tư cần bổ sung</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Dây tín hiệu chống nhiễu</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">20</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Ống luồn dây bảo vệ</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Đầu nối tín hiệu</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">10</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin liên hệ khách hàng</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Tên khách hàng</p>
                            <p class="font-medium">Trần Văn Bình</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Số điện thoại</p>
                            <p class="font-medium">097.456.8932</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium">binhtv@vienthonga.com</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thời gian thực hiện</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Ngày bắt đầu</p>
                            <p class="font-medium">01/06/2024</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ngày dự kiến hoàn thành</p>
                            <p class="font-medium">05/06/2024</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Ghi chú</h2>
                    <p class="text-gray-700">
                        Cần thực hiện bảo trì vào ngày cuối tuần để giảm thiểu ảnh hưởng đến hoạt động của khách hàng.
                        Mang theo đầy đủ thiết bị dự phòng để đảm bảo có thể khắc phục sự cố ngay tại chỗ.
                    </p>
                </div>
                
                <div class="border-t border-gray-200 pt-4 mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Người tạo</p>
                            <p class="font-medium">Minh Trí</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ngày tạo</p>
                            <p class="font-medium">25/05/2024 15:45</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Trạng thái</p>
                            <div class="badge badge-green">Đã duyệt</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Các xử lý cho trang chi tiết nếu cần
        });
    </script>
</body>
</html> 