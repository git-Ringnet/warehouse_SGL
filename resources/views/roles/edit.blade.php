<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa nhóm quyền - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <div class="flex items-center">
                <a href="{{ route('roles.show', $role->id) }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-edit mr-2"></i>Chỉnh sửa nhóm quyền: {{ $role->name }}
                </h1>
            </div>
        </header>

        <main class="p-6">
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm leading-5 font-medium text-red-800">
                                Đã xảy ra lỗi:
                            </h3>
                            <ul class="mt-1 text-sm leading-5 text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Thông báo cảnh báo quyền trùng lặp -->
            @if (session('duplicate_warnings'))
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm leading-5 font-medium text-yellow-800">
                                Cảnh báo quyền trùng lặp!
                            </h3>
                            <p class="mt-1 text-sm text-yellow-700">
                                Một số nhân viên bạn chọn đã có quyền tương tự từ nhóm quyền khác:
                            </p>
                            <div class="mt-3 space-y-3">
                                @foreach (session('duplicate_warnings') as $warning)
                                    <div class="bg-yellow-100 border border-yellow-200 rounded-lg p-3">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-yellow-800">
                                                    <i class="fas fa-user mr-1"></i>
                                                    {{ $warning['employee']->name }}
                                                </p>
                                                <p class="text-xs text-yellow-600 mt-1">
                                                    Hiện tại thuộc nhóm: <strong>{{ $warning['current_role']->name }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <p class="text-xs text-yellow-600 mb-1">Quyền trùng lặp:</p>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($warning['duplicate_permissions']->take(3) as $permission)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-200 text-yellow-800">
                                                        {{ $permission->display_name }}
                                                    </span>
                                                @endforeach
                                                @if ($warning['duplicate_permissions']->count() > 3)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-200 text-yellow-800">
                                                        +{{ $warning['duplicate_permissions']->count() - 3 }} quyền khác
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-3 text-sm text-yellow-700">
                                <i class="fas fa-lightbulb mr-1"></i>
                                <strong>Khuyến nghị:</strong> Xem xét loại bỏ nhân viên này khỏi danh sách hoặc điều chỉnh quyền để tránh xung đột.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('roles.update', $role->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Thông tin cơ bản -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">
                        <i class="fas fa-info-circle mr-2"></i>Thông tin cơ bản
                    </h2>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Tên nhóm quyền
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Tên nhóm quyền phải là duy nhất trong hệ thống</p>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $role->description) }}</textarea>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700">
                                Kích hoạt nhóm quyền
                            </label>
                            <p class="ml-2 text-xs text-gray-500">
                                (Bỏ tick để vô hiệu hóa nhóm quyền này)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Phân quyền -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">
                        <i class="fas fa-key mr-2"></i>Phân quyền
                    </h2>

                    <div class="mb-4 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="select-all-permissions"
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="select-all-permissions" class="ml-2 block text-sm font-medium text-gray-700">
                                Chọn tất cả
                            </label>
                        </div>

                        <div class="relative">
                            <input type="text" id="permission-search" placeholder="Tìm kiếm quyền..."
                                class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @foreach ($permissions as $group => $groupPermissions)
                            <div class="permission-group border border-gray-200 rounded-lg overflow-hidden">
                                <div
                                    class="bg-gray-50 px-4 py-3 flex justify-between items-center cursor-pointer group-header">
                                    <h3 class="font-medium text-gray-700">{{ $group }}</h3>
                                    <div class="flex items-center space-x-3">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox"
                                                class="select-group h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Chọn nhóm</span>
                                        </label>
                                        <i class="fas fa-chevron-down text-gray-500 group-toggle-icon"></i>
                                    </div>
                                </div>

                                <div class="px-4 py-3 group-permissions">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @php
                                            // Nhóm các quyền theo từ khóa
                                            $groupedPermissions = [];
                                            foreach ($groupPermissions as $permission) {
                                                $key = explode('.', $permission->name)[0];
                                                $groupedPermissions[$key][] = $permission;
                                            }

                                            // Sắp xếp các nhóm quyền
                                            ksort($groupedPermissions);
                                        @endphp

                                        @foreach ($groupedPermissions as $key => $permissions)
                                            <div class="space-y-2">
                                                @foreach ($permissions as $permission)
                                                    <div class="permission-item flex items-center">
                                                        <input type="checkbox" id="permission-{{ $permission->id }}"
                                                            name="permissions[]" value="{{ $permission->id }}"
                                                            {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                            class="permission-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                        <label for="permission-{{ $permission->id }}"
                                                            class="ml-2 block text-sm text-gray-700">
                                                            {{ $permission->display_name }}
                                                            @if ($permission->name === 'customers.manage')
                                                                <i class="fas fa-user-lock text-gray-400 ml-1"
                                                                    title="Quyền này cho phép kích hoạt hoặc vô hiệu hóa tài khoản khách hàng"></i>
                                                            @endif
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Thêm nhân viên vào nhóm quyền -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">
                        <i class="fas fa-users mr-2"></i>Nhân viên trong nhóm quyền này
                    </h2>

                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">Nhân viên thuộc nhóm quyền này sẽ được phép thực hiện các
                            quyền đã chọn ở trên</p>

                        <!-- Tìm kiếm nhân viên -->
                        <div class="mb-6 relative">
                            <input type="text" id="employeeSearch" placeholder="Tìm kiếm nhân viên..."
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="max-h-80 overflow-y-auto">
                                <table class="min-w-full divide-y divide-gray-200 employee-list">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" id="select-all-employees"
                                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tên nhân viên</th>
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Username</th>
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($employees as $employee)
                                            <tr class="employee-row hover:bg-gray-50">
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <input type="checkbox" name="employees[]"
                                                        value="{{ $employee->id }}"
                                                        {{ in_array($employee->id, $roleEmployees) ? 'checked' : '' }}
                                                        class="employee-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 employee-name">
                                                        {{ $employee->name }}</div>
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500 employee-username">
                                                        {{ $employee->username }}</div>
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    @if ($employee->status == 'active')
                                                        <span
                                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Đang hoạt động
                                                        </span>
                                                    @elseif($employee->status == 'leave')
                                                        <span
                                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            Nghỉ phép
                                                        </span>
                                                    @else
                                                        <span
                                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            Đã nghỉ việc
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div id="noEmployeesFound" class="hidden py-4 text-center text-gray-500 text-sm">
                                Không tìm thấy nhân viên nào phù hợp với từ khóa tìm kiếm
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-sm text-gray-600 mt-4">
                            <div>
                                <span class="font-medium">Lưu ý:</span> Nhân viên được chọn sẽ được gán vào nhóm quyền
                                này sau khi lưu thay đổi
                            </div>
                            <div>
                                <span id="selectedEmployeesCount">{{ count($roleEmployees) }}</span> nhân viên được
                                chọn
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quản lý dự án có quyền truy cập -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">
                        <i class="fas fa-project-diagram mr-2"></i>Dự án có quyền truy cập
                    </h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chọn dự án mà nhóm quyền này có thể
                            truy cập</label>
                        <div class="border border-gray-300 rounded-lg p-4 max-h-80 overflow-y-auto">
                            @if ($projects->count() > 0)
                                <div class="mb-2 flex items-center">
                                    <button type="button" id="select-all-projects"
                                        class="text-sm text-blue-600 hover:text-blue-800">Chọn tất cả</button>
                                    <span class="mx-2">|</span>
                                    <button type="button" id="deselect-all-projects"
                                        class="text-sm text-blue-600 hover:text-blue-800">Bỏ chọn tất cả</button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($projects as $project)
                                        <div class="flex items-start">
                                            <input type="checkbox" name="projects[]"
                                                id="project-{{ $project->id }}" value="{{ $project->id }}"
                                                class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                {{ in_array($project->id, $roleProjects) ? 'checked' : '' }}>
                                            <label for="project-{{ $project->id }}"
                                                class="ml-2 block text-sm text-gray-700">
                                                <div class="font-medium">{{ $project->project_name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    @if ($project->customer)
                                                        {{ $project->customer->name }}
                                                    @else
                                                        Không có khách hàng
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">Không có dự án nào đang hoạt động.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quản lý hợp đồng cho thuê có quyền truy cập -->
                <div class="mb-6 bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Phân quyền hợp đồng cho thuê</h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chọn hợp đồng cho thuê mà nhóm
                            quyền này có thể truy cập</label>
                        <div class="border border-gray-300 rounded-lg p-4 max-h-80 overflow-y-auto">
                            @if ($rentals->count() > 0)
                                <div class="mb-2 flex items-center">
                                    <button type="button" id="select-all-rentals"
                                        class="text-sm text-blue-600 hover:text-blue-800">Chọn tất cả</button>
                                    <span class="mx-2">|</span>
                                    <button type="button" id="deselect-all-rentals"
                                        class="text-sm text-blue-600 hover:text-blue-800">Bỏ chọn tất cả</button>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($rentals as $rental)
                                        <div class="flex items-start">
                                            <input type="checkbox" name="rentals[]" id="rental-{{ $rental->id }}"
                                                value="{{ $rental->id }}"
                                                class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                {{ in_array($rental->id, $roleRentals) ? 'checked' : '' }}>
                                            <label for="rental-{{ $rental->id }}"
                                                class="ml-2 block text-sm text-gray-700">
                                                <div class="font-medium">{{ $rental->rental_name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    @if ($rental->customer)
                                                        {{ $rental->customer->name }}
                                                    @else
                                                        Không có khách hàng
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">Không có hợp đồng cho thuê nào đang hoạt động.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Cài đặt -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('roles.show', $role->id) }}"
                            class="px-6 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>Hủy
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kiểm tra trạng thái ban đầu của các nhóm
            function initializeGroupStatus() {
                document.querySelectorAll('.permission-group').forEach(group => {
                    const groupCheckbox = group.querySelector('.select-group');
                    const checkboxes = group.querySelectorAll('.permission-checkbox');

                    // Nếu tất cả checkbox trong nhóm đã được chọn, chọn checkbox nhóm
                    let allChecked = checkboxes.length > 0;
                    checkboxes.forEach(checkbox => {
                        if (!checkbox.checked) allChecked = false;
                    });

                    groupCheckbox.checked = allChecked;
                });

                // Kiểm tra "Chọn tất cả"
                updateSelectAllStatus();
            }

            // Xử lý tìm kiếm quyền
            const searchInput = document.getElementById('permission-search');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const permissionItems = document.querySelectorAll('.permission-item');

                permissionItems.forEach(item => {
                    const label = item.querySelector('label').textContent.toLowerCase();

                    if (label.includes(searchTerm)) {
                        item.style.display = '';
                        // Hiển thị group chứa item khớp với kết quả tìm kiếm
                        const group = item.closest('.permission-group');
                        group.style.display = '';
                        group.querySelector('.group-permissions').style.display = '';
                        group.querySelector('.group-toggle-icon').classList.remove(
                            'fa-chevron-down');
                        group.querySelector('.group-toggle-icon').classList.add('fa-chevron-up');
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Ẩn group không có kết quả nào khớp
                document.querySelectorAll('.permission-group').forEach(group => {
                    const visibleItems = group.querySelectorAll('.permission-item[style=""]');
                    if (visibleItems.length === 0 && searchTerm) {
                        group.style.display = 'none';
                    }
                });
            });

            // Xử lý chọn tất cả các quyền
            const selectAllCheckbox = document.getElementById('select-all-permissions');
            selectAllCheckbox.addEventListener('change', function() {
                const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
                permissionCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });

                // Cập nhật trạng thái checkbox nhóm
                document.querySelectorAll('.select-group').forEach(groupCheckbox => {
                    groupCheckbox.checked = this.checked;
                });
            });

            // Xử lý chọn nhóm quyền
            document.querySelectorAll('.select-group').forEach(groupCheckbox => {
                groupCheckbox.addEventListener('change', function() {
                    const group = this.closest('.permission-group');
                    const checkboxes = group.querySelectorAll('.permission-checkbox');

                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });

                    // Cập nhật trạng thái "Chọn tất cả" khi tất cả nhóm được chọn
                    updateSelectAllStatus();
                });
            });

            // Cập nhật trạng thái checkbox nhóm khi checkbox con thay đổi
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const group = this.closest('.permission-group');
                    const groupCheckbox = group.querySelector('.select-group');
                    const checkboxes = group.querySelectorAll('.permission-checkbox');

                    // Kiểm tra xem tất cả checkbox con đã được chọn chưa
                    let allChecked = true;
                    checkboxes.forEach(cb => {
                        if (!cb.checked) allChecked = false;
                    });

                    groupCheckbox.checked = allChecked;

                    // Cập nhật trạng thái "Chọn tất cả"
                    updateSelectAllStatus();
                });
            });

            // Xử lý đóng/mở nhóm quyền
            document.querySelectorAll('.group-header').forEach(header => {
                header.addEventListener('click', function(e) {
                    // Bỏ qua sự kiện khi click vào checkbox
                    if (e.target.type === 'checkbox' || e.target.tagName === 'LABEL' || e.target
                        .tagName === 'SPAN') {
                        return;
                    }

                    const group = this.closest('.permission-group');
                    const permissionsDiv = group.querySelector('.group-permissions');
                    const toggleIcon = group.querySelector('.group-toggle-icon');

                    if (permissionsDiv.style.display === 'none') {
                        permissionsDiv.style.display = '';
                        toggleIcon.classList.remove('fa-chevron-down');
                        toggleIcon.classList.add('fa-chevron-up');
                    } else {
                        permissionsDiv.style.display = 'none';
                        toggleIcon.classList.remove('fa-chevron-up');
                        toggleIcon.classList.add('fa-chevron-down');
                    }
                });
            });

            function updateSelectAllStatus() {
                const allCheckboxes = document.querySelectorAll('.permission-checkbox');
                const selectAllCheckbox = document.getElementById('select-all-permissions');

                let allChecked = true;
                allCheckboxes.forEach(checkbox => {
                    if (!checkbox.checked) allChecked = false;
                });

                selectAllCheckbox.checked = allChecked;
            }

            // Xử lý tìm kiếm nhân viên
            const employeeSearch = document.getElementById('employeeSearch');
            const employeeRows = document.querySelectorAll('.employee-row');
            const noEmployeesFoundMessage = document.getElementById('noEmployeesFound');

            if (employeeSearch) {
                employeeSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    let foundAny = false;

                    employeeRows.forEach(row => {
                        const name = row.querySelector('.employee-name').textContent.toLowerCase();
                        const username = row.querySelector('.employee-username').textContent
                            .toLowerCase();

                        if (name.includes(searchTerm) || username.includes(searchTerm)) {
                            row.style.display = '';
                            foundAny = true;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    noEmployeesFoundMessage.style.display = foundAny ? 'none' : 'block';
                });
            }

            // Xử lý chọn tất cả nhân viên
            const selectAllEmployeesCheckbox = document.getElementById('select-all-employees');
            const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');

            if (selectAllEmployeesCheckbox) {
                // Kiểm tra trạng thái ban đầu của "Chọn tất cả"
                function updateSelectAllEmployeesStatus() {
                    let allChecked = true;
                    let visibleCount = 0;

                    employeeCheckboxes.forEach(checkbox => {
                        // Chỉ xét các checkbox đang hiển thị
                        if (checkbox.closest('tr').style.display !== 'none') {
                            visibleCount++;
                            if (!checkbox.checked) {
                                allChecked = false;
                            }
                        }
                    });

                    selectAllEmployeesCheckbox.checked = (visibleCount > 0) && allChecked;
                    selectAllEmployeesCheckbox.indeterminate = !allChecked && visibleCount > 0 && document
                        .querySelectorAll('.employee-checkbox:checked').length > 0;

                    // Cập nhật số lượng nhân viên được chọn
                    const selectedCount = document.querySelectorAll('.employee-checkbox:checked').length;
                    document.getElementById('selectedEmployeesCount').textContent = selectedCount;
                }

                selectAllEmployeesCheckbox.addEventListener('change', function() {
                    employeeCheckboxes.forEach(checkbox => {
                        // Chỉ thay đổi trạng thái của các checkbox đang hiển thị
                        if (checkbox.closest('tr').style.display !== 'none') {
                            checkbox.checked = this.checked;
                        }
                    });

                    // Cập nhật số lượng nhân viên được chọn
                    updateSelectAllEmployeesStatus();
                });

                // Khi checkbox nhân viên thay đổi, cập nhật trạng thái "Chọn tất cả"
                employeeCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateSelectAllEmployeesStatus();
                    });
                });

                // Khởi tạo trạng thái ban đầu
                updateSelectAllEmployeesStatus();
            }

            // Khởi tạo trạng thái ban đầu của các nhóm quyền
            initializeGroupStatus();

            // Hàm để chọn/bỏ chọn tất cả dự án
            const selectAllProjectsBtn = document.getElementById('select-all-projects');
            const deselectAllProjectsBtn = document.getElementById('deselect-all-projects');
            
            if (selectAllProjectsBtn) {
                console.log('Tìm thấy nút select-all-projects');
                selectAllProjectsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const checkboxes = document.querySelectorAll('input[name="projects[]"]');
                    console.log('Tìm thấy', checkboxes.length, 'checkbox dự án');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                    console.log('Đã chọn tất cả dự án');
                });
            } else {
                console.log('KHÔNG tìm thấy nút select-all-projects');
            }

            if (deselectAllProjectsBtn) {
                console.log('Tìm thấy nút deselect-all-projects');
                deselectAllProjectsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const checkboxes = document.querySelectorAll('input[name="projects[]"]');
                    console.log('Tìm thấy', checkboxes.length, 'checkbox dự án');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    console.log('Đã bỏ chọn tất cả dự án');
                });
            } else {
                console.log('KHÔNG tìm thấy nút deselect-all-projects');
            }

            // Hàm để chọn/bỏ chọn tất cả hợp đồng cho thuê
            const selectAllRentalsBtn = document.getElementById('select-all-rentals');
            const deselectAllRentalsBtn = document.getElementById('deselect-all-rentals');
            
            if (selectAllRentalsBtn) {
                console.log('Tìm thấy nút select-all-rentals');
                selectAllRentalsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const checkboxes = document.querySelectorAll('input[name="rentals[]"]');
                    console.log('Tìm thấy', checkboxes.length, 'checkbox hợp đồng cho thuê');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                    console.log('Đã chọn tất cả hợp đồng cho thuê');
                });
            } else {
                console.log('KHÔNG tìm thấy nút select-all-rentals');
            }

            if (deselectAllRentalsBtn) {
                console.log('Tìm thấy nút deselect-all-rentals');
                deselectAllRentalsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const checkboxes = document.querySelectorAll('input[name="rentals[]"]');
                    console.log('Tìm thấy', checkboxes.length, 'checkbox hợp đồng cho thuê');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    console.log('Đã bỏ chọn tất cả hợp đồng cho thuê');
                });
            } else {
                console.log('KHÔNG tìm thấy nút deselect-all-rentals');
            }
        });
    </script>
</body>

</html>
