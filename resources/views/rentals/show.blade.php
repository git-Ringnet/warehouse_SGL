<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu cho thuê - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu cho thuê</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    RNT-2406001
                </div>
                <div class="ml-2 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    Đang cho thuê
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url('/rentals') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ url('/rentals/1/edit') }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
            </div>
        </header>

        <main class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Thông tin phiếu cho thuê -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Thông tin phiếu cho thuê</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                            <div>
                                <p class="text-sm text-gray-500">Mã phiếu</p>
                                <p class="text-base text-gray-800 font-medium">RNT-2406001</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Khách hàng</p>
                                <p class="text-base text-gray-800 font-medium">Công ty ABC</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày cho thuê</p>
                                <p class="text-base text-gray-800 font-medium">01/06/2024</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày hẹn trả</p>
                                <p class="text-base text-gray-800 font-medium">30/06/2024</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tiền đặt cọc</p>
                                <p class="text-base text-gray-800 font-medium">5,000,000 VNĐ</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Trạng thái</p>
                                <p class="text-base text-gray-800 font-medium">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đang cho thuê</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Người liên hệ</p>
                                <p class="text-base text-gray-800 font-medium">Nguyễn Văn A</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Số điện thoại</p>
                                <p class="text-base text-gray-800 font-medium">0912345678</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Ghi chú</p>
                            <p class="text-base text-gray-800 mt-1">
                                Khách hàng yêu cầu giao thiết bị vào buổi sáng. Đã đặt cọc 50% giá trị thuê.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Danh sách thiết bị cho thuê -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Danh sách thiết bị cho thuê</h2>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã thiết bị</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên thiết bị</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Đơn giá thuê</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">EQ-001</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Camera Dome 2MP</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">5</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">500,000 VNĐ</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">2,500,000 VNĐ</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">EQ-003</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Đầu ghi NVR 8 kênh</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">1</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">1,500,000 VNĐ</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">1,500,000 VNĐ</td>
                                    </tr>
                                    <tr class="bg-gray-50 font-semibold">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900" colspan="4">Tổng cộng</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">4,000,000 VNĐ</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Lịch sử gia hạn -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Lịch sử gia hạn</h2>
                            <button onclick="openExtendModal()" class="text-blue-500 hover:text-blue-600 text-sm font-medium">
                                <i class="fas fa-clock mr-1"></i> Gia hạn phiếu thuê
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày gia hạn</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người thực hiện</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày hẹn trả cũ</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày hẹn trả mới</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">15/06/2024</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">Nhân viên A</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">15/06/2024</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">30/06/2024</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">Khách hàng yêu cầu gia hạn thêm 15 ngày</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar thông tin -->
                <div class="lg:col-span-1">
                    <!-- Thông tin trạng thái -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Tình trạng phiếu thuê</h2>
                        
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-500">Tiến độ thuê</span>
                                <span class="text-sm font-medium text-gray-800">50%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-blue-500 h-2.5 rounded-full" style="width: 50%"></div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Thời gian còn lại</p>
                                <p class="text-xl font-semibold text-gray-800">15 ngày</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày hẹn trả</p>
                                <p class="text-xl font-semibold text-gray-800">30/06/2024</p>
                            </div>
                            <div class="mt-2">
                                <button onclick="openExtendModal()" class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center justify-center transition-colors">
                                    <i class="fas fa-clock mr-2"></i> Gia hạn phiếu thuê
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thông tin khách hàng -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Thông tin khách hàng</h2>
                        
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Khách hàng</p>
                                <p class="text-base text-gray-800 font-medium">Công ty ABC</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Người liên hệ</p>
                                <p class="text-base text-gray-800 font-medium">Nguyễn Văn A</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Số điện thoại</p>
                                <p class="text-base text-gray-800 font-medium">0912345678</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="text-base text-gray-800 font-medium">nguyenvana@abc.com</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Địa chỉ</p>
                                <p class="text-base text-gray-800 font-medium">123 Lê Lợi, Quận 1, TP.HCM</p>
                            </div>
                            <div class="pt-3 border-t border-gray-200">
                                <a href="{{ url('/customers/1') }}" class="text-blue-500 hover:text-blue-600 text-sm flex items-center">
                                    <i class="fas fa-external-link-alt mr-2"></i> Xem chi tiết khách hàng
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal gia hạn -->
    <div id="extendModal" class="modal-overlay">
        <div class="modal">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Gia hạn phiếu cho thuê</h3>
                <form id="extendForm" method="POST" action="{{ url('/rentals/1/extend') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="current_due_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày hẹn trả hiện tại</label>
                            <input type="date" id="current_due_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" value="2024-06-30" readonly>
                        </div>
                        <div>
                            <label for="new_due_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày hẹn trả mới</label>
                            <input type="date" name="new_due_date" id="new_due_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="extend_notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea name="extend_notes" id="extend_notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeExtendModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Hủy
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        function openExtendModal() {
            document.getElementById('extendModal').classList.add('show');
            
            // Set default new due date to current due date + 15 days
            const currentDueDate = new Date(document.getElementById('current_due_date').value);
            const newDueDate = new Date(currentDueDate);
            newDueDate.setDate(newDueDate.getDate() + 15);
            document.getElementById('new_due_date').value = newDueDate.toISOString().split('T')[0];
        }

        function closeExtendModal() {
            document.getElementById('extendModal').classList.remove('show');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('extendModal');
            if (event.target === modal) {
                closeExtendModal();
            }
        }
    </script>
</body>
</html> 