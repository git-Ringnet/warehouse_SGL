<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết khách hàng - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết khách hàng</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    ID: #{{ $customer->id }}
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('customers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ route('customers.edit', $customer->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
            </div>
        </header>

        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-6">Thông tin khách hàng</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Tên người đại diện</h3>
                            <p class="text-base text-gray-900">{{ $customer->name }}</p>
                        </div>
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Công ty</h3>
                            <p class="text-base text-gray-900">{{ $customer->company_name }}</p>
                        </div>
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Số điện thoại</h3>
                            <p class="text-base text-gray-900">{{ $customer->phone }}</p>
                        </div>
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Số điện thoại công ty</h3>
                            <p class="text-base text-gray-900">{{ $customer->company_phone ?? 'Không có' }}</p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Email</h3>
                            <p class="text-base text-gray-900">{{ $customer->email ?? 'Không có' }}</p>
                        </div>
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Địa chỉ công ty</h3>
                            <p class="text-base text-gray-900">{{ $customer->address ?? 'Không có' }}</p>
                        </div>
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Ngày tạo</h3>
                            <p class="text-base text-gray-900">{{ $customer->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Cập nhật lần cuối</h3>
                            <p class="text-base text-gray-900">{{ $customer->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="mb-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Trạng thái tài khoản</h3>
                            @if($customer->has_account)
                                <div>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded-full">Đã kích hoạt</span>
                                </div>
                                <div class="mt-2 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                    <h4 class="text-sm font-medium text-blue-800 mb-2">Thông tin đăng nhập</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div>
                                            <span class="text-gray-600 text-sm">Tên đăng nhập:</span>
                                            <span class="font-medium ml-1">{{ $customer->account_username }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 text-sm">Mật khẩu:</span>
                                            <span class="font-medium ml-1">{{ $customer->account_password }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-sm rounded-full">Chưa kích hoạt</span>
                                    <a href="{{ route('customers.activate', $customer->id) }}" class="ml-2 text-blue-500 hover:text-blue-700 text-sm">
                                        <i class="fas fa-user-check mr-1"></i> Kích hoạt ngay
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Ghi chú</h3>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <p class="text-base text-gray-900">{{ $customer->notes ?? 'Không có ghi chú' }}</p>
                    </div>
                </div>
            </div>

            <!-- Danh sách các dự án của khách hàng (mẫu) -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 mt-6 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Dự án liên quan</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên dự án</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày bắt đầu</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">PRJ001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Hệ thống quản lý kho</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Đang triển khai</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <a href="#" class="text-blue-500 hover:text-blue-700">Xem chi tiết</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex justify-center">
                    <p class="text-gray-500 text-sm italic">Chức năng liên kết dự án sẽ được cập nhật sau</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dropdown Menus
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
    </script>
</body>
</html> 