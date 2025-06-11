<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu đề xuất triển khai dự án - SGL</title>
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
    </style>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu đề xuất triển khai dự án</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    PRJ-00{{ $id }}
                </div>
                <div class="ml-2 badge badge-yellow">
                    Chờ duyệt
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ url('/requests/project/'.$id.'/edit') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <button id="deleteButton" 
                    data-id="{{ $id }}" 
                    data-name="phiếu đề xuất triển khai dự án"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                </button>
                <a href="{{ url('/requests/project/'.$id.'/preview') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors" target="_blank">
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
                            <p class="font-medium">28/05/2024</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Kỹ thuật đề xuất</p>
                            <p class="font-medium">Duy Đức</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thông tin dự án</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Tên dự án</p>
                            <p class="font-medium">Đất Đỏ</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Đối tác</p>
                            <p class="font-medium">VNPT Vũng Tàu</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Địa chỉ dự án</p>
                            <p class="font-medium">Thị trấn Đất Đỏ</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Thiết bị đề xuất</h2>
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
                                    <td class="px-6 py-4 text-sm text-gray-700">Cụm thu phát thanh</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Bộ điều khiển trung tâm</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Bộ thu không dây</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Vật tư đề xuất</h2>
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
                                    <td class="px-6 py-4 text-sm text-gray-700">Dây điện 1.5mm</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">100</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Dây tín hiệu 2 lõi</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">50</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">Ống nhựa xoắn</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">30</td>
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
                            <p class="font-medium">Nguyễn Minh Tài</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Số điện thoại</p>
                            <p class="font-medium">982.133.564</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium">minhta@gmail.com</p>
                        </div>
                        <div class="md:col-span-3">
                            <p class="text-sm text-gray-500">Địa chỉ</p>
                            <p class="font-medium">1 Đất Đỏ, Thị trấn Long Điền, huyện Long Đất, Tỉnh BRVT</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Ghi chú</h2>
                    <p class="text-gray-700">
                        Thi công trước ngày 15/06/2024. Yêu cầu kỹ thuật lắp đặt đúng kỹ thuật và đảm bảo an toàn.
                        Liên hệ với khách hàng trước khi tiến hành thi công ít nhất 2 ngày.
                    </p>
                </div>
                
                <div class="border-t border-gray-200 pt-4 mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Người tạo</p>
                            <p class="font-medium">Duy Đức</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ngày tạo</p>
                            <p class="font-medium">28/05/2024 10:30</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Trạng thái</p>
                            <div class="badge badge-yellow">Chờ duyệt</div>
                        </div>
                    </div>
                </div>
            </div>
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
            form.action = "{{ url('/requests/project') }}/" + id;
            
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
    </script>
</body>
</html> 