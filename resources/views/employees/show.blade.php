<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết nhân viên - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết nhân viên</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    ID: {{ $employee->id }}
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('employees.edit', $employee->id) }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Sửa
                </a>
                @if ($employee->name != 'Quản trị viên')
                    <button onclick="openDeleteModal('{{ $employee->id }}', '{{ $employee->name }}')"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-trash mr-2"></i> Xóa
                    </button>
                @endif
                <form id="delete-form-{{ $employee->id }}" action="{{ route('employees.destroy', $employee->id) }}"
                    method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
                <a href="{{ route('employees.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif

            @if (session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif

            <!-- Thông tin cơ bản -->
            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row justify-between">
                            <div class="flex flex-col md:flex-row items-start gap-6">
                                <div
                                    class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 overflow-hidden">
                                    @if ($employee->avatar)
                                        <img src="{{ asset('storage/' . $employee->avatar) }}"
                                            alt="{{ $employee->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-user-circle text-6xl"></i>
                                    @endif
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800">{{ $employee->name }}</h2>
                                    <div class="mt-2 flex items-center text-sm text-gray-600">
                                        @if ($employee->role == 'admin')
                                            <div
                                                class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold mr-2">
                                                Quản trị viên
                                            </div>
                                        @elseif($employee->role == 'manager')
                                            <div
                                                class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold mr-2">
                                                Quản lý
                                            </div>
                                        @elseif($employee->role == 'tech')
                                            <div
                                                class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-semibold mr-2">
                                                Kỹ thuật viên
                                            </div>
                                        @else
                                            <div
                                                class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold mr-2">
                                                Nhân viên
                                            </div>
                                        @endif

                                        @if ($employee->is_active)
                                            <span
                                                class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                                Đang hoạt động
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                                Đã khóa
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-gray-600 mt-1">Tài khoản từ
                                        {{ \Carbon\Carbon::parse($employee->created_at)->format('d/m/Y') }}</p>
                                    <p class="text-gray-600 mt-1">Cập nhật lần cuối:
                                        {{ \Carbon\Carbon::parse($employee->updated_at)->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="mt-6 lg:mt-0 flex flex-col">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-envelope text-gray-400 w-6"></i>
                                    <span class="ml-3 text-gray-700">{{ $employee->email ?: 'Không có' }}</span>
                                </div>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-phone text-gray-400 w-6"></i>
                                    <span class="ml-3 text-gray-700">{{ $employee->phone }}</span>
                                </div>
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-gray-400 w-6"></i>
                                    <span class="ml-3 text-gray-700">{{ $employee->address ?: 'Không có' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Thông tin tài khoản và cá nhân -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-blue-500"></i>
                        Thông tin nhân viên
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Username</p>
                            <p class="font-medium text-gray-800">{{ $employee->username }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Họ và tên</p>
                            <p class="font-medium text-gray-800">{{ $employee->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium text-gray-800">{{ $employee->email ?: 'Không có' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Số điện thoại</p>
                            <p class="font-medium text-gray-800">{{ $employee->phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Địa chỉ</p>
                            <p class="font-medium text-gray-800">{{ $employee->address ?: 'Không có' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Thông tin công việc -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-briefcase mr-2 text-purple-500"></i>
                        Thông tin công việc
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Vai trò</p>
                            <p class="font-medium text-gray-800">
                                @if ($employee->roleGroup)
                                    <span
                                        class="px-2 py-1 {{ $employee->roleGroup->is_active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }} rounded-full text-xs font-semibold">
                                        {{ $employee->roleGroup->name }}
                                    </span>
                                @else
                                    <span class="text-gray-500">Chưa được gán vai trò</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Phòng ban</p>
                            <p class="font-medium text-gray-800">
                                {{ $employee->department ?? 'Chưa phân công' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Trạng thái</p>
                            <p class="font-medium text-gray-800">
                                @if ($employee->is_active)
                                    <span
                                        class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        Đang hoạt động
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                        Đã khóa
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ghi chú</p>
                            <p class="font-medium text-gray-800">{{ $employee->notes ?: 'Không có' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách quyền -->
            @if ($employee->roleGroup && $employee->roleGroup->permissions->count() > 0)
                <div class="mt-6">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-lock mr-2 text-green-500"></i>
                            Quyền hạn của nhân viên
                        </h3>

                        <div class="space-y-4">
                            <p class="text-sm text-gray-600">
                                Các quyền được cấp thông qua nhóm quyền <span
                                    class="font-semibold">{{ $employee->roleGroup->name }}</span>:
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mt-3">
                                @foreach ($employee->roleGroup->permissions->groupBy('group') as $group => $permissions)
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <h4 class="font-medium text-gray-700 mb-2">{{ $group }}</h4>
                                        <ul class="space-y-1">
                                            @foreach ($permissions as $permission)
                                                <li class="text-sm">
                                                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                                    {{ $permission->display_name }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Dự án liên quan -->
            <div class="mt-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-project-diagram mr-2 text-indigo-500"></i>
                        Dự án liên quan
                    </h3>

                    @if ($projects->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã dự án</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên dự án</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Khách hàng</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ngày bắt đầu</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($projects as $project)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $project->project_code }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $project->project_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $project->customer->company_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('projects.show', $project->id) }}"
                                                    class="text-blue-600 hover:text-blue-900">Xem chi tiết</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">Nhân viên này chưa phụ trách dự án nào.</p>
                    @endif
                </div>
            </div>

            <!-- Phiếu cho thuê liên quan -->
            <div class="mt-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-clipboard-list mr-2 text-amber-500"></i>
                        Phiếu cho thuê liên quan
                    </h3>

                    @if ($rentals->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã phiếu</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên phiếu</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Khách hàng</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ngày cho thuê</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ngày hẹn trả</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($rentals as $rental)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $rental->rental_code }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $rental->rental_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $rental->customer->company_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($rental->rental_date)->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span
                                                    class="@if ($rental->isOverdue()) text-red-600 font-medium @endif">
                                                    {{ \Carbon\Carbon::parse($rental->due_date)->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('rentals.show', $rental->id) }}"
                                                    class="text-blue-600 hover:text-blue-900">Xem chi tiết</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">Nhân viên này chưa phụ trách phiếu cho thuê nào.</p>
                    @endif
                </div>
            </div>

        </main>
    </div>

    <script>
        // Khởi tạo modal khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
        });

        // Mở modal xác nhận xóa
        function openDeleteModal(id, name) {
            // Thay đổi nội dung modal
            document.getElementById('customerNameToDelete').innerText = name;

            // Thay đổi hành động khi nút xác nhận được nhấn
            document.getElementById('confirmDeleteBtn').onclick = function() {
                document.getElementById('delete-form-' + id).submit();
                closeDeleteModal();
            };

            // Hiển thị modal
            document.getElementById('deleteModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    </script>
</body>

</html>
