<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết nhóm quyền - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        @php
            $user = Auth::guard('web')->user();
            $canEdit =
                ($user && $user->role === 'admin') ||
                ($user && $user->roleGroup && $user->roleGroup->hasPermission('roles.edit'));
            $canDelete =
                ($user && $user->role === 'admin') ||
                ($user && $user->roleGroup && $user->roleGroup->hasPermission('roles.delete'));
        @endphp

        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <a href="{{ route('roles.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-eye mr-2"></i>Chi tiết nhóm quyền
                </h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $role->name }}
                </div>

                @if ($role->is_active)
                    <div class="ml-2 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                        <i class="fas fa-check-circle mr-1"></i> Đang hoạt động
                    </div>
                @else
                    <div class="ml-2 px-3 py-1 bg-red-100 text-red-800 text-sm rounded-full">
                        <i class="fas fa-times-circle mr-1"></i> Đã vô hiệu hóa
                    </div>
                @endif

                @if ($role->is_system)
                    <div class="ml-2 px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">
                        <i class="fas fa-lock mr-1"></i> Hệ thống
                    </div>
                @endif
            </div>
            <div class="flex space-x-3">
                @if ($canEdit && !$role->is_system)
                    <a href="{{ route('roles.edit', $role->id) }}"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                    </a>
                    <form action="{{ route('roles.toggleStatus', $role->id) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="{{ $role->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas {{ $role->is_active ? 'fa-ban' : 'fa-check-circle' }} mr-2"></i>
                            {{ $role->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Thông tin nhóm quyền -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Thông tin nhóm quyền
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Tên nhóm quyền</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $role->name }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Mô tả</p>
                            <p class="text-base text-gray-800">{{ $role->description ?? 'Không có mô tả' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái</p>
                            <p class="text-base text-gray-800">
                                @if ($role->is_active)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Đang hoạt động
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i> Đã vô hiệu hóa
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Loại</p>
                            <p class="text-base text-gray-800">
                                @if ($role->is_system)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-lock mr-1"></i> Quyền hệ thống
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-user-cog mr-1"></i> Quyền tùy chỉnh
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Thời gian tạo</p>
                            <p class="text-base text-gray-800">{{ $role->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Cập nhật lần cuối</p>
                            <p class="text-base text-gray-800">{{ $role->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if ($canDelete && !$role->is_system)
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            @php
                                $employeesCount = $role->employees->count();
                            @endphp
                            @if ($employeesCount > 0)
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                                        <div>
                                            <h4 class="text-sm font-medium text-yellow-800">Không thể xóa nhóm quyền</h4>
                                            <p class="text-sm text-yellow-700 mt-1">
                                                Có {{ $employeesCount }} nhân viên đang sử dụng nhóm quyền này. 
                                                Vui lòng chuyển các nhân viên sang nhóm quyền khác trước khi xóa.
                                            </p>
                                            <a href="{{ route('roles.edit', $role->id) }}" 
                                                class="mt-2 inline-flex items-center text-sm text-yellow-700 hover:text-yellow-900 underline">
                                                <i class="fas fa-edit mr-1"></i>
                                                Chỉnh sửa nhóm quyền
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhóm quyền này? Hành động này không thể hoàn tác.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex items-center text-red-600 hover:text-red-800 transition-colors">
                                        <i class="fas fa-trash-alt mr-2"></i> Xóa nhóm quyền
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Thống kê -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-chart-pie mr-2 text-indigo-500"></i>
                            Thống kê quyền
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h3 class="text-sm font-medium text-blue-800">Tổng số quyền</h3>
                                <p class="mt-2 text-2xl font-bold text-blue-700">{{ $role->permissions->count() }}</p>
                                @php
                                    // Đếm số nhóm quyền mà role này thực sự có quyền
                                    $rolePermissionGroups = $role->permissions->pluck('group')->unique()->count();
                                @endphp
                                <p class="mt-1 text-xs text-blue-600">từ {{ $rolePermissionGroups }} nhóm khác nhau</p>
                            </div>

                            <div class="bg-green-50 p-4 rounded-lg">
                                <h3 class="text-sm font-medium text-green-800">Số nhân viên</h3>
                                <p class="mt-2 text-2xl font-bold text-green-700">{{ $role->employees->count() }}</p>
                                <p class="mt-1 text-xs text-green-600">đang sử dụng nhóm quyền này</p>
                            </div>

                            <div class="bg-purple-50 p-4 rounded-lg">
                                <h3 class="text-sm font-medium text-purple-800">Trạng thái</h3>
                                <p
                                    class="mt-2 text-lg font-bold {{ $role->is_active ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $role->is_active ? 'Đang hoạt động' : 'Đã vô hiệu hóa' }}
                                </p>
                                <p class="mt-1 text-xs {{ $role->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $role->is_active ? 'Nhóm quyền có thể được sử dụng' : 'Nhóm quyền đã bị khóa' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách các quyền trong nhóm -->
            <div class="mt-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-key mr-2 text-yellow-500"></i>
                        Danh sách quyền
                    </h2>

                    <div class="mb-6">
                        <input type="text" id="permissionSearch" placeholder="Tìm kiếm quyền..."
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    @if ($role->permissions->count() > 0)
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 permission-groups">
                            @foreach ($permissions as $group => $groupPermissions)
                                @php
                                    $hasPermissionInGroup = false;
                                    foreach ($groupPermissions as $permission) {
                                        if (in_array($permission->id, $rolePermissions)) {
                                            $hasPermissionInGroup = true;
                                            break;
                                        }
                                    }
                                @endphp

                                @if ($hasPermissionInGroup)
                                    <div class="permission-group">
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <h3 class="font-medium text-gray-800 mb-3 group-name">{{ $group }}
                                            </h3>
                                            <ul class="space-y-2">
                                                @foreach ($groupPermissions as $permission)
                                                    @if (in_array($permission->id, $rolePermissions))
                                                        <li class="permission-item flex items-center text-sm">
                                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                                            <span
                                                                class="permission-name">{{ $permission->display_name }}</span>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- No results message -->
                        <div id="noPermissionsFound" class="hidden text-center py-10">
                            <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-500">Không tìm thấy quyền nào khớp với từ khóa tìm kiếm</p>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="fas fa-lock text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-500">Nhóm quyền này không có quyền nào</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Danh sách nhân viên được gán nhóm quyền này -->
            <div class="mt-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-users mr-2 text-blue-500"></i>
                            Nhân viên thuộc nhóm quyền này
                        </h2>

                        <div class="mt-4 md:mt-0">
                            <a href="{{ route('roles.edit', $role->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                                <i class="fas fa-user-plus mr-2"></i> Thêm nhân viên
                            </a>
                        </div>
                    </div>

                    @if ($role->employees->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nhân viên</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Liên hệ</th>

                                        <th scope="col"
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Trạng thái</th>
                                        <th scope="col"
                                            class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($role->employees as $employee)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-500">
                                                {{ $employee->id }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div
                                                        class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-500"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $employee->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $employee->username }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $employee->email }}</div>
                                                <div class="text-sm text-gray-500">{{ $employee->phone }}</div>
                                            </td>

                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                                @if ($employee->status == 'active')
                                                    <span
                                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i> Đang hoạt động
                                                    </span>
                                                @elseif($employee->status == 'leave')
                                                    <span
                                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-clock mr-1"></i> Nghỉ phép
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        <i class="fas fa-times-circle mr-1"></i> Đã nghỉ việc
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-right">
                                                <div class="flex justify-end space-x-2">
                                                    <a href="{{ route('employees.show', $employee->id) }}"
                                                        class="text-blue-600 hover:text-blue-900"
                                                        title="Xem thông tin">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('employees.edit', $employee->id) }}"
                                                        class="text-yellow-600 hover:text-yellow-900"
                                                        title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="fas fa-user-slash text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-500">Không có nhân viên nào được gán vào nhóm quyền này</p>
                            <a href="{{ route('roles.edit', $role->id) }}"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                                <i class="fas fa-user-plus mr-2"></i> Thêm nhân viên
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Danh sách dự án -->
            <div class="mb-6 bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Dự án được phân quyền ({{ $projects->count() }})
                </h2>
                @if ($projects->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên dự án
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Khách hàng
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($projects as $project)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $project->project_name }}</div>
                                            @if (isset($project->contract_code))
                                                <div class="text-xs text-gray-500">Mã HĐ:
                                                    {{ $project->contract_code }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if ($project->customer)
                                                    {{ $project->customer->name }}
                                                @else
                                                    <span class="text-gray-500 italic">Không có</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 italic">Không có dự án nào được phân quyền cho nhóm này.</p>
                @endif
            </div>

            <!-- Danh sách hợp đồng cho thuê -->
            <div class="mb-6 bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Hợp đồng cho thuê được phân quyền
                    ({{ $rentals->count() }})</h2>
                @if ($rentals->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã hợp đồng
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Khách hàng
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày bắt đầu
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($rentals as $rental)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">#{{ $rental->id }}</div>
                                            @if (isset($rental->contract_code))
                                                <div class="text-xs text-gray-500">{{ $rental->contract_code }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if ($rental->customer)
                                                    {{ $rental->customer->name }}
                                                @else
                                                    <span class="text-gray-500 italic">Không có</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if ($rental->start_date)
                                                    {{ \Carbon\Carbon::parse($rental->start_date)->format('d/m/Y') }}
                                                @else
                                                    <span class="text-gray-500 italic">Chưa xác định</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 italic">Không có hợp đồng cho thuê nào được phân quyền cho nhóm này.</p>
                @endif
            </div>
        </main>
    </div>

    <script>
        // Tìm kiếm quyền
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('permissionSearch');
            const permissionGroups = document.querySelectorAll('.permission-group');
            const noResultsMessage = document.getElementById('noPermissionsFound');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    let foundAny = false;

                    permissionGroups.forEach(group => {
                        const groupName = group.querySelector('.group-name').textContent
                            .toLowerCase();
                        const permissionItems = group.querySelectorAll('.permission-item');
                        let foundInGroup = false;

                        permissionItems.forEach(item => {
                            const permissionName = item.querySelector('.permission-name')
                                .textContent.toLowerCase();

                            if (permissionName.includes(searchTerm) || groupName.includes(
                                    searchTerm)) {
                                item.style.display = '';
                                foundInGroup = true;
                                foundAny = true;
                            } else {
                                item.style.display = 'none';
                            }
                        });

                        group.style.display = foundInGroup ? '' : 'none';
                    });

                    noResultsMessage.style.display = foundAny ? 'none' : 'block';
                });
            }
        });
    </script>
</body>

</html>
