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
    <script src="{{ asset('js/delete-modal.js') }}"></script>
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
                <button id="deleteButton" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                </button>
            </div>
        </header>

        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
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
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Số điện thoại người đại diện</h3>
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
                                    @if(isset($customer->is_locked))
                                        <span class="ml-2 px-2 py-1 {{ $customer->is_locked ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }} text-sm rounded-full">
                                            {{ $customer->is_locked ? 'Đã khóa' : 'Đang hoạt động' }}
                                        </span>
                                    @endif
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
                                <div class="mt-2">
                                    <a href="{{ route('customers.toggle-lock', $customer->id) }}" class="text-sm px-3 py-1 rounded-md {{ $customer->is_locked ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }} text-white inline-flex items-center">
                                        <i class="fas {{ $customer->is_locked ? 'fa-unlock' : 'fa-lock' }} mr-1"></i>
                                        {{ $customer->is_locked ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}
                                    </a>
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

            <!-- Danh sách các dự án của khách hàng -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 mt-6 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Dự án liên quan</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã dự án</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên dự án</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày bắt đầu</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày kết thúc</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($projects as $project)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $project->project_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $project->project_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ date('d/m/Y', strtotime($project->start_date)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ date('d/m/Y', strtotime($project->end_date)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <a href="{{ route('projects.show', $project->id) }}" class="text-blue-500 hover:text-blue-700">Xem chi tiết</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Khách hàng chưa có dự án nào</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Danh sách các phiếu cho thuê của khách hàng -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 mt-6 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Phiếu cho thuê liên quan</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã phiếu</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên phiếu</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày thuê</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày hẹn trả</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($rentals as $rental)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $rental->rental_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $rental->rental_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ date('d/m/Y', strtotime($rental->rental_date)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ date('d/m/Y', strtotime($rental->due_date)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <a href="{{ route('rentals.show', $rental->id) }}" class="text-blue-500 hover:text-blue-700">Xem chi tiết</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Khách hàng chưa có phiếu cho thuê nào</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Variables for delete functionality
        const customerId = {{ $customer->id }};
        const customerName = "{{ $customer->name }} - {{ $customer->company_name }}";
        
        // Function to handle customer deletion
        function deleteCustomer(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('customers.index') }}/' + id;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }

        // Initialize delete button
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButton = document.getElementById('deleteButton');
            if (deleteButton) {
                deleteButton.addEventListener('click', function() {
                    openDeleteModal(customerId, customerName);
                });
            }
        });

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