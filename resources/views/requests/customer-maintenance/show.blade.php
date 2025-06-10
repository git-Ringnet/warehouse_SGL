<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu khách yêu cầu bảo trì - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
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
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background-color: white;
            border-radius: 0.5rem;
            max-width: 500px;
            width: 90%;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal {
            transform: scale(1);
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu khách yêu cầu bảo trì</h1>
                <div class="ml-4 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    Mẫu REQ-CUST-MAINT
                </div>
                <div class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    ID: {{ $id }}
                </div>
                <div class="ml-2 px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                    Đang xử lý
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ url('/requests/customer-maintenance/'.$id.'/edit') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <a href="{{ url('/requests') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>
        
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Thông tin chung</h2>
                        <p class="text-sm text-gray-600">Mã phiếu: CUST-MAINT-{{ str_pad($id, 4, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="printRequest()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-print mr-2"></i> In phiếu
                        </button>
                        <a href="{{ url('/requests/customer-maintenance/'.$id.'/copy') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-copy mr-2"></i> Sao chép
                        </a>
                        <button onclick="openDeleteModal('{{ $id }}', 'phiếu yêu cầu bảo trì')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-trash mr-2"></i> Xóa
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cột 1 -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Thông tin tiếp nhận</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Ngày tiếp nhận</p>
                                    <p class="text-base text-gray-800">25/06/2024</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Người tiếp nhận</p>
                                    <p class="text-base text-gray-800">Nguyễn Văn A</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Thông tin khách hàng</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Tên khách hàng/Đơn vị</p>
                                    <p class="text-base text-gray-800">Công ty TNHH Phát triển Công nghệ XYZ</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Số điện thoại</p>
                                    <p class="text-base text-gray-800">0901234567</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Email</p>
                                    <p class="text-base text-gray-800">contact@xyztech.com</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Địa chỉ</p>
                                    <p class="text-base text-gray-800">123 Đường Nguyễn Văn Linh, Quận 7, TP. HCM</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Thông tin thiết bị</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Tên thiết bị</p>
                                    <p class="text-base text-gray-800">Máy chủ Dell PowerEdge R740</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Model/Serial</p>
                                    <p class="text-base text-gray-800">SN: ABC12345XYZ</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Mô tả sự cố</p>
                                    <p class="text-base text-gray-800 whitespace-pre-line">Máy chủ gặp lỗi không thể khởi động, đèn báo hiệu lỗi ổ cứng sáng liên tục. Cần kiểm tra và thay thế các linh kiện bị hỏng.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột 2 -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Thông tin xử lý</h3>
                            <div class="grid grid-cols-2 gap-4 mb-3">
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Ngày dự kiến xử lý</p>
                                    <p class="text-base text-gray-800">15/07/2024</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Thời gian dự kiến</p>
                                    <p class="text-base text-gray-800">4 giờ</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <p class="text-sm text-gray-500 font-medium mb-1">Mức độ ưu tiên</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-circle text-xs mr-1"></i> Cao
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-circle text-xs mr-1"></i> Đang xử lý
                                </span>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Vật tư yêu cầu</h3>
                            @if(true)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">1</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">Ổ cứng SSD 1TB</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">2</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">2</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">Cáp SATA</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">4</td>
                                    </tr>
                                </tbody>
                            </table>
                            @else
                            <p class="text-gray-500 italic">Không có vật tư nào được yêu cầu</p>
                            @endif
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Ghi chú</h3>
                            <p class="text-base text-gray-800 whitespace-pre-line">Khách hàng yêu cầu xử lý nhanh chóng vì đây là máy chủ quan trọng phục vụ hệ thống của họ.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <p>Ngày tạo: 25/06/2024 14:30:00</p>
                            <p>Cập nhật lần cuối: 26/06/2024 09:15:23</p>
                        </div>
                        <div class="flex space-x-2">
                            <button type="button" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center">
                                <i class="fas fa-check-circle mr-2"></i> Đánh dấu hoàn thành
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            
        </main>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal p-5">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Xác nhận xóa</h3>
            <p class="text-gray-600 mb-5">Bạn có chắc chắn muốn xóa <span id="deleteItemName" class="font-medium"></span>?</p>
            <div class="flex justify-end space-x-2">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                    Hủy
                </button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Xử lý modal xóa
        function openDeleteModal(id, name) {
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteForm').action = '/requests/customer-maintenance/' + id;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }

        // Hàm in phiếu
        function printRequest() {
            window.print();
        }
    </script>
</body>
</html> 