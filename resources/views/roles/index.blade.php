<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhóm quyền - SGL</title>
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
        @php
            $user = Auth::guard('web')->user();
            $canCreate =
                ($user && $user->role === 'admin') ||
                ($user && $user->roleGroup && $user->roleGroup->hasPermission('roles.create'));
            $canViewDetail =
                ($user && $user->role === 'admin') ||
                ($user && $user->roleGroup && $user->roleGroup->hasPermission('roles.view_detail'));
            $canEdit =
                ($user && $user->role === 'admin') ||
                ($user && $user->roleGroup && $user->roleGroup->hasPermission('roles.edit'));
            $canDelete =
                ($user && $user->role === 'admin') ||
                ($user && $user->roleGroup && $user->roleGroup->hasPermission('roles.delete'));
            $hasAnyActionPermission = $canEdit || $canDelete;
        @endphp

        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">
                <i class="fas fa-user-shield mr-2"></i>Quản lý nhóm quyền
            </h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <form action="{{ route('roles.index') }}" method="GET" class="flex flex-col md:flex-row gap-2 w-full">
                    <div class="flex gap-2 w-full md:w-auto">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tìm kiếm..."
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64" />
                        <select name="filter"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                            <option value="" {{ !isset($filter) || !$filter ? 'selected' : '' }}>
                                Tất cả
                            </option>
                            <option value="name" {{ isset($filter) && $filter == 'name' ? 'selected' : '' }}>
                                Tên nhóm
                            </option>
                            <option value="description"
                                {{ isset($filter) && $filter == 'description' ? 'selected' : '' }}>Mô tả
                            </option>
                        </select>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg px-4 py-2 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                @if ($canCreate)
                    <a href="{{ route('roles.create') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center transition-colors w-full md:w-auto">
                        <i class="fas fa-plus-circle mr-2"></i> Thêm nhóm quyền
                    </a>
                @endif
            </div>
        </header>
        @if (session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        @if (session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        <!-- Thông báo quyền -->
        @if (!$canCreate && !$canEdit && !$canDelete)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Thông báo:</strong> Bạn chỉ có quyền xem danh sách nhóm quyền.
                            Để thực hiện các thao tác khác, vui lòng liên hệ quản trị viên.
                        </p>
                    </div>
                </div>
            </div>
        @elseif(!$canCreate || !$canEdit || !$canDelete)
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Thông tin quyền:</strong>
                            @if ($canCreate)
                                Có thể tạo mới.
                            @endif
                            @if ($canEdit)
                                Có thể chỉnh sửa.
                            @endif
                            @if ($canDelete)
                                Có thể xóa.
                            @endif
                            @if (!$canCreate)
                                Không thể tạo mới.
                            @endif
                            @if (!$canEdit)
                                Không thể chỉnh sửa.
                            @endif
                            @if (!$canDelete)
                                Không thể xóa.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
        <main class="p-6">


            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                STT
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên nhóm quyền</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mô tả</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Trạng thái</th>
                            @if ($hasAnyActionPermission)
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Hành động</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($roles as $role)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $role->name }}
                                    @if ($role->is_system)
                                        <span
                                            class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                            Hệ thống
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $role->description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($role->is_active)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Đang hoạt động
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Đã vô hiệu hóa
                                        </span>
                                    @endif
                                </td>
                                @if ($hasAnyActionPermission)
                                    <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                        @if ($canViewDetail)
                                            <a href="{{ route('roles.show', $role->id) }}"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                title="Xem">
                                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                            </a>
                                        @endif
                                        @if ($canEdit && !$role->is_system)
                                            <a href="{{ route('roles.edit', $role->id) }}"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                title="Sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </a>
                                        @endif

                                        @if ($canDelete && !$role->is_system)
                                            @php
                                                $employeesCount = $role->employees()->count();
                                            @endphp
                                            @if ($employeesCount > 0)
                                                <button
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 cursor-not-allowed opacity-50"
                                                    title="Không thể xóa - Có {{ $employeesCount }} nhân viên đang sử dụng"
                                                    disabled>
                                                    <i class="fas fa-trash-alt text-gray-400"></i>
                                                </button>
                                            @else
                                                <button
                                                    onclick="openDeleteModal('{{ $role->id }}', '{{ $role->name }}', 0)"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                    title="Xóa">
                                                    <i class="fas fa-trash-alt text-red-500 group-hover:text-white"></i>
                                                </button>

                                                <form id="delete-form-{{ $role->id }}"
                                                    action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                                    class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif
                                        @endif

                                        @if ($canEdit && !$role->is_system)
                                            <form action="{{ route('roles.toggleStatus', $role->id) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full {{ $role->is_active ? 'bg-orange-100 hover:bg-orange-500' : 'bg-green-100 hover:bg-green-500' }} transition-colors group"
                                                    title="{{ $role->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}">
                                                    <i
                                                        class="fas {{ $role->is_active ? 'fa-ban' : 'fa-check-circle' }} {{ $role->is_active ? 'text-orange-500' : 'text-green-500' }} group-hover:text-white"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $hasAnyActionPermission ? '6' : '5' }}"
                                    class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ liệu nhóm quyền
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $roles->links() }}
            </div>
        </main>
    </div>

    <script>
        // Khởi tạo modal khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
        });

        // Mở modal xác nhận xóa
        function openDeleteModal(id, name, employeesCount) {
            // Thay đổi nội dung modal
            document.getElementById('customerNameToDelete').innerText = name;

            // Hiển thị thông tin về việc xóa nhóm quyền
            const modalContent = document.querySelector('#deleteModal .modal-content p');
            if (modalContent) {
                modalContent.innerHTML =
                    `Bạn có chắc chắn muốn xóa nhóm quyền <strong>"${name}"</strong>?<br><br>
                    <span class="text-sm text-gray-600">⚠️ Hành động này không thể hoàn tác. Tất cả quyền được gán trong nhóm này sẽ bị xóa.</span>`;
            }

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
