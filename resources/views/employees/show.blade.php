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

<body></body>
</body>
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
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-500 font-medium text-4xl">{{ substr($employee->name, 0, 1) }}</span>
                                </div>
                                @endif
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">{{ $employee->name }}</h2>
                                <div class="mt-2 flex items-center text-sm text-gray-600">
                                    @if ($employee->is_active)
                                    <div class="flex items-center">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                            Đang hoạt động
                                        </span>
                                        @if (Auth::id() !== $employee->id && !$employee->roleGroup->is_super_admin)
                                        <form action="{{ route('employees.toggle-status', $employee->id) }}" method="POST" class="ml-2">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-800 rounded-full text-xs font-semibold transition-colors"
                                                onclick="return confirm('Bạn có chắc chắn muốn khóa tài khoản này? Người dùng sẽ bị đăng xuất khỏi tất cả thiết bị.')">
                                                Khóa tài khoản
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                    @else
                                    <div class="flex items-center">
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                            Đã khóa
                                        </span>
                                        @if (Auth::id() !== $employee->id && !$employee->roleGroup->is_super_admin)
                                        <form action="{{ route('employees.toggle-status', $employee->id) }}" method="POST" class="ml-2">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="px-2 py-1 bg-green-100 hover:bg-green-200 text-green-800 rounded-full text-xs font-semibold transition-colors"
                                                onclick="return confirm('Bạn có chắc chắn muốn mở khóa tài khoản này?')">
                                                Mở khóa tài khoản
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                <p class="text-gray-600 mt-1">Tài khoản từ
                                    {{ \Carbon\Carbon::parse($employee->created_at)->format('H:i d/m/Y') }}
                                </p>
                                <p class="text-gray-600 mt-1">Cập nhật lần cuối:
                                    {{ \Carbon\Carbon::parse($employee->updated_at)->format('H:i d/m/Y') }}
                                </p>
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
                        <p class="text-sm text-gray-500">Phòng ban</p>
                        <p class="font-medium text-gray-800">{{ $employee->department ?: 'Không có' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Trạng thái</p>
                        <p class="font-medium text-gray-800">
                            @if ($employee->is_active)
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Đang hoạt động</span>
                            @else
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">Đã khóa</span>
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
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-project-diagram mr-2 text-blue-500"></i>
                        Dự án liên quan
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã dự án
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên dự án
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Khách hàng
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày bắt đầu
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày kết thúc
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Hành động
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if ($projects && $projects->count() > 0)
                                    @foreach($projects as $project)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $project->project_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $project->project_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $project->customer ? $project->customer->name : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('projects.show', $project->id) }}" class="text-blue-600 hover:text-blue-900">
                                                Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Nhân viên chưa có dự án nào
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phiếu cho thuê liên quan -->
        <div class="mt-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-contract mr-2 text-green-500"></i>
                        Phiếu cho thuê liên quan
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã phiếu
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên phiếu
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Khách hàng
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày thuê
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày hẹn trả
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Hành động
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @if ($rentals && $rentals->count() > 0)
                                    @foreach($rentals as $rental)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $rental->rental_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $rental->rental_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $rental->customer ? $rental->customer->name : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $rental->rental_date ? \Carbon\Carbon::parse($rental->rental_date)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <span class="{{ $rental->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                                {{ \Carbon\Carbon::parse($rental->due_date)->format('d/m/Y') }}
                                                @if ($rental->isOverdue())
                                                <span class="ml-1 text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Quá hạn</span>
                                                @elseif($rental->daysRemaining() <= 7)
                                                <span class="ml-1 text-xs bg-yellow-100 text-yellow-600 px-2 py-0.5 rounded-full">{{ $rental->daysRemaining() }} ngày</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('rentals.show', $rental->id) }}" class="text-blue-600 hover:text-blue-900">
                                                Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Nhân viên chưa có phiếu cho thuê nào
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
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