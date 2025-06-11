<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu bảo trì dự án - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu bảo trì dự án</h1>
                <div class="ml-4 px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">
                    Mẫu REQ-MAINT
                </div>
                <div class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    ID: {{ $id }}
                </div>
                <div class="ml-2 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    Đang xử lý
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ url('/requests/maintenance/'.$id.'/edit') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <button id="deleteButton" 
                    data-id="{{ $id }}" 
                    data-name="phiếu bảo trì dự án"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                </button>
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
                        <p class="text-sm text-gray-600">Mã phiếu: MAINT-{{ str_pad($id, 4, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="printRequest()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-print mr-2"></i> In phiếu
                        </button>
                        <a href="{{ url('/requests/maintenance/'.$id.'/copy') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-copy mr-2"></i> Sao chép
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cột 1 -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Thông tin đề xuất</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Ngày đề xuất</p>
                                    <p class="text-base text-gray-800">20/06/2024</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Kỹ thuật đề xuất</p>
                                    <p class="text-base text-gray-800">Trần Minh Trí</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Thông tin dự án</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Tên dự án</p>
                                    <p class="text-base text-gray-800">Hệ thống giám sát Tân Thành</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Đối tác</p>
                                    <p class="text-base text-gray-800">Viễn Thông A</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Địa chỉ dự án</p>
                                    <p class="text-base text-gray-800">456 Đường Lê Hồng Phong, Phường Tân Thành, Quận Tân Phú, TP.HCM</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Thông tin khách hàng</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Tên khách hàng</p>
                                    <p class="text-base text-gray-800">Lê Quang Hưng</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Số điện thoại</p>
                                    <p class="text-base text-gray-800">0987654321</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Email</p>
                                    <p class="text-base text-gray-800">hung.le@vienthonga.com</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột 2 -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Thông tin bảo trì</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Ngày bảo trì dự kiến</p>
                                    <p class="text-base text-gray-800">05/07/2024</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Loại bảo trì</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-circle text-xs mr-1"></i> Định kỳ
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Lý do bảo trì</p>
                                    <p class="text-base text-gray-800 whitespace-pre-line">Bảo trì định kỳ 6 tháng theo hợp đồng. Kiểm tra các thiết bị camera, hệ thống lưu trữ và nâng cấp phần mềm quản lý.</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-circle text-xs mr-1"></i> Đang xử lý
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Vật tư cần thiết</h3>
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
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">Ổ cứng lưu trữ 4TB</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">2</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">2</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">Bộ nguồn dự phòng</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">1</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Nhân sự thực hiện</h3>
                            <ul class="list-disc list-inside space-y-1">
                                <li class="text-base text-gray-800">Nguyễn Văn An</li>
                                <li class="text-base text-gray-800">Phạm Thị Bình</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <p>Ngày tạo: 20/06/2024 10:30:00</p>
                            <p>Cập nhật lần cuối: 21/06/2024 15:45:12</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-700 mb-2"><strong>Ghi chú:</strong></p>
                            <p class="text-sm text-gray-700">Bảo trì định kỳ đã được thông báo cho khách hàng trước 2 tuần. Cần chuẩn bị đầy đủ vật tư và liên hệ xác nhận lịch trước khi thực hiện.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline hoạt động -->
            
        </main>
    </div>

    <script>
        // Delete functionality setup
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
            
            // Attach click event to delete button
            document.getElementById('deleteButton').addEventListener('click', function() {
                const requestName = this.getAttribute('data-name');
                const requestId = this.getAttribute('data-id');
                openDeleteModal(requestId, requestName);
            });
        });

        // Override deleteCustomer function from delete-modal.js
        function deleteCustomer(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ url('/requests/maintenance') }}/" + id;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = "{{ csrf_token() }}";
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
        
        // Existing function for printing request
        function printRequest() {
            window.print();
        }
    </script>
</body>
</html> 