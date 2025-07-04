<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý dự án - SGL</title>
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
    </style>
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        @php
            $user = Auth::guard('web')->user();
            $canCreate =
                $user &&
                ($user->role === 'admin' ||
                    ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('projects.create')));
            $canViewDetail =
                $user &&
                ($user->role === 'admin' ||
                    ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('projects.view_detail')));
            $canEdit =
                $user &&
                ($user->role === 'admin' ||
                    ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('projects.edit')));
            $canDelete =
                $user &&
                ($user->role === 'admin' ||
                    ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('projects.delete')));
        @endphp

        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý dự án</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <form action="{{ route('projects.index') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                    <input type="text" name="search" placeholder="Tìm kiếm theo tên, mã dự án..."
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64 h-10"
                        value="{{ $search ?? '' }}" />
                    <select name="filter"
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 h-10">
                        <option value="">Tất cả</option>
                        <option value="project_code"
                            {{ isset($filter) && $filter == 'project_code' ? 'selected' : '' }}>Mã dự án</option>
                        <option value="project_name"
                            {{ isset($filter) && $filter == 'project_name' ? 'selected' : '' }}>Tên dự án</option>
                        <option value="customer" {{ isset($filter) && $filter == 'customer' ? 'selected' : '' }}>Khách
                            hàng</option>
                    </select>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors h-10">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </form>
                @if ($canCreate)
                    <a href="{{ route('projects.create') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center h-10">
                        <i class="fas fa-plus-circle mr-2"></i> Tạo dự án mới
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
        <main class="p-6">
            <!-- Projects Table -->
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mã dự án</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên dự án</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Khách hàng</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Người đại diện</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ngày bắt đầu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ngày kết thúc</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Bảo hành</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($projects as $project)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $project->project_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $project->project_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $project->customer->company_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $project->customer->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $daysLeft = $project->remaining_warranty_days;
                                        $colorClass = 'text-green-600';
                                        $icon = 'check-circle';

                                        if (!$project->has_valid_warranty) {
                                            $colorClass = 'text-red-600';
                                            $icon = 'times-circle';
                                        } elseif ($daysLeft <= 7) {
                                            $colorClass = 'text-red-600';
                                            $icon = 'exclamation-circle';
                                        } elseif ($daysLeft <= 30) {
                                            $colorClass = 'text-orange-500';
                                            $icon = 'exclamation-triangle';
                                        } elseif ($daysLeft <= 90) {
                                            $colorClass = 'text-yellow-500';
                                            $icon = 'info-circle';
                                        }
                                    @endphp
                                    <span class="font-medium flex items-center {{ $colorClass }}">
                                        <i class="fas fa-{{ $icon }} mr-1"></i>
                                        @if ($project->has_valid_warranty)
                                            {{ $daysLeft }} ngày
                                        @else
                                            Hết hạn
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    @if ($canViewDetail)
                                        <a href="{{ route('projects.show', $project->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($canEdit)
                                        <a href="{{ route('projects.edit', $project->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                            title="Sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($canDelete)
                                        <button type="button"
                                            onclick="openDeleteModal('{{ $project->id }}', '{{ $project->project_name }}')"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                            title="Xóa">
                                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Không có dự án
                                    nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Hiển thị {{ $projects->firstItem() ?? 0 }}-{{ $projects->lastItem() ?? 0 }} của
                    {{ $projects->total() }} dự án
                </div>
                <div>
                    {{ $projects->links() }}
                </div>
            </div>
        </main>
    </div>

    <script>
        // Khởi tạo modal khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
        });

        // Ghi đè hàm deleteCustomer để xử lý xóa dự án
        function deleteCustomer(id) {
            // Tạo và gửi form xóa
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/projects/' + id;

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
    </script>
</body>

</html>
