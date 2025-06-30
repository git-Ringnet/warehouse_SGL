@extends('layouts.app')

@section('title', 'Quản lý phiếu yêu cầu - SGL')

@section('content')
    @php
        $user = Auth::guard('web')->user();
        $canCreateProject =
            $user &&
            ($user->role === 'admin' ||
                ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('requests.project.create')));
        $canCreateMaintenance =
            $user &&
            ($user->role === 'admin' ||
                ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('requests.maintenance.create')));
        $canCreateCustomerMaintenance =
            $user &&
            ($user->role === 'admin' ||
                ($user->role_id &&
                    $user->roleGroup &&
                    $user->roleGroup->hasPermission('requests.customer-maintenance.create')));
        $canViewDetail =
            $user &&
            ($user->role === 'admin' ||
                ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('requests.view_detail')));
        $canEdit =
            $user &&
            ($user->role === 'admin' ||
                ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('requests.edit')));
        $canDelete =
            $user &&
            ($user->role === 'admin' ||
                ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('requests.delete')));
        $canExport =
            $user &&
            ($user->role === 'admin' ||
                ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('requests.export')));
        $canCopy =
            $user &&
            ($user->role === 'admin' ||
                ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('requests.copy')));
    @endphp

    <div class="container-fluid px-6 py-4">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Quản lý phiếu yêu cầu</h1>
            <div class="flex space-x-2">
                @if ($canCreateProject || $canCreateMaintenance || $canCreateCustomerMaintenance)
                    <div class="req-dropdown">
                        <button
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center req-dropdown-toggle">
                            <i class="fas fa-plus mr-2"></i> Tạo mới
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>
                        <div class="req-dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                            @if ($canCreateProject)
                                <a href="{{ route('requests.project.create') }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-project-diagram mr-2"></i> Đề xuất triển khai dự án
                                </a>
                            @endif
                            @if ($canCreateMaintenance)
                                <a href="{{ url('/requests/maintenance/create') }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tools mr-2"></i> Bảo trì dự án
                                </a>
                            @endif
                            @if ($canCreateCustomerMaintenance)
                                <a href="{{ url('/requests/customer-maintenance/create') }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-cog mr-2"></i> Khách yêu cầu bảo trì
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <form action="{{ route('requests.index') }}" method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-grow min-w-[200px]">
                        <input type="text" name="search" placeholder="Tìm kiếm..." value="{{ request('search') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-full md:w-auto">
                        <select name="filter"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Tìm theo --</option>
                            <option value="request_code" {{ request('filter') == 'request_code' ? 'selected' : '' }}>Mã
                                phiếu</option>
                            <option value="project_name" {{ request('filter') == 'project_name' ? 'selected' : '' }}>Tên dự
                                án</option>
                            <option value="customer" {{ request('filter') == 'customer' ? 'selected' : '' }}>Khách hàng
                            </option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <select name="status"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Trạng thái --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt
                            </option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt
                            </option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối
                            </option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Đang
                                thực hiện</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành
                            </option>
                            <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Đã hủy
                            </option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-search mr-2"></i> Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mã phiếu
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Loại phiếu
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tên dự án
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Khách hàng
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ngày tạo
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Trạng thái
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Thao tác
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if (isset($requests) && count($requests) > 0)
                            @foreach ($requests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $request->request_code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($request->type == 'project')
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                                Triển khai dự án
                                            </span>
                                        @elseif($request->type == 'maintenance')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                                Bảo trì dự án
                                            </span>
                                        @elseif($request->type == 'customer_maintenance')
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                                Khách yêu cầu bảo trì
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->project_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->customer ? $request->customer->company_name : $request->customer_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->request_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @switch($request->status)
                                            @case('pending')
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">
                                                    Chờ duyệt
                                                </span>
                                            @break

                                            @case('approved')
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                                    Đã duyệt
                                                </span>
                                            @break

                                            @case('rejected')
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                                    Từ chối
                                                </span>
                                            @break

                                            @case('in_progress')
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                                    Đang thực hiện
                                                </span>
                                            @break

                                            @case('completed')
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                                    Hoàn thành
                                                </span>
                                            @break

                                            @case('canceled')
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                                    Đã hủy
                                                </span>
                                            @break

                                            @default
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                                    Không xác định
                                                </span>
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                        @if ($request->type == 'project')
                                            @if ($canViewDetail)
                                                <a href="{{ route('requests.project.show', $request->id) }}"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                                </a>

                                                <a href="{{ route('requests.project.preview', $request->id) }}"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group"
                                                    title="Xem trước">
                                                    <i class="fas fa-file-alt text-purple-500 group-hover:text-white"></i>
                                                </a>
                                            @endif

                                            @if ($canExport)
                                                <a href="#" onclick="exportToExcel('project', '{{ $request->id }}')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                                    title="Xuất Excel">
                                                    <i class="fas fa-file-excel text-green-500 group-hover:text-white"></i>
                                                </a>

                                                <a href="#" onclick="exportToPDF('project', '{{ $request->id }}')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                    title="Xuất PDF">
                                                    <i class="fas fa-file-pdf text-red-500 group-hover:text-white"></i>
                                                </a>
                                            @endif

                                            @if ($request->status === 'pending' && $canEdit)
                                                <a href="{{ route('requests.project.edit', $request->id) }}"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                                </a>
                                            @endif

                                            @if ($request->status === 'pending' && $canDelete)
                                                <form action="{{ route('requests.project.destroy', $request->id) }}"
                                                    method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                        title="Xóa"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu đề xuất này?')">
                                                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($canCopy)
                                                <form action="{{ route('requests.project.store') }}" method="POST"
                                                    class="inline-block">
                                                    @csrf
                                                    <input type="hidden" name="copy_from" value="{{ $request->id }}">
                                                    <button type="submit"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-teal-100 hover:bg-teal-500 transition-colors group"
                                                        title="Sao chép">
                                                        <i class="fas fa-copy text-teal-500 group-hover:text-white"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @elseif($request->type == 'maintenance')
                                            @if ($canViewDetail)
                                                <a href="{{ route('requests.maintenance.show', $request->id) }}"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                                </a>

                                                <a href="{{ route('requests.maintenance.preview', $request->id) }}"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group"
                                                    title="Xem trước">
                                                    <i class="fas fa-file-alt text-purple-500 group-hover:text-white"></i>
                                                </a>
                                            @endif

                                            @if ($canExport)
                                                <a href="#"
                                                    onclick="exportToExcel('maintenance', '{{ $request->id }}')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                                    title="Xuất Excel">
                                                    <i class="fas fa-file-excel text-green-500 group-hover:text-white"></i>
                                                </a>

                                                <a href="#"
                                                    onclick="exportToPDF('maintenance', '{{ $request->id }}')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                    title="Xuất PDF">
                                                    <i class="fas fa-file-pdf text-red-500 group-hover:text-white"></i>
                                                </a>
                                            @endif

                                            @if ($request->status === 'pending' && $canEdit)
                                                <a href="{{ route('requests.maintenance.edit', $request->id) }}"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                                </a>
                                            @endif

                                            @if ($request->status === 'pending' && $canDelete)
                                                <form action="{{ route('requests.maintenance.destroy', $request->id) }}"
                                                    method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                        title="Xóa"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu bảo trì này?')">
                                                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($canCopy)
                                                <form action="{{ route('requests.maintenance.store') }}" method="POST"
                                                    class="inline-block">
                                                    @csrf
                                                    <input type="hidden" name="copy_from" value="{{ $request->id }}">
                                                    <button type="submit"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-teal-100 hover:bg-teal-500 transition-colors group"
                                                        title="Sao chép">
                                                        <i class="fas fa-copy text-teal-500 group-hover:text-white"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @elseif($request->type == 'customer_maintenance')
                                            @if ($canViewDetail)
                                                <a href="{{ route('requests.customer-maintenance.show', $request->id) }}"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                                </a>

                                                <a href="{{ route('requests.customer-maintenance.preview', $request->id) }}"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group"
                                                    title="Xem trước">
                                                    <i class="fas fa-file-alt text-purple-500 group-hover:text-white"></i>
                                                </a>
                                            @endif

                                            @if ($canExport)
                                                <a href="#"
                                                    onclick="exportToExcel('customer-maintenance', '{{ $request->id }}')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                                    title="Xuất Excel">
                                                    <i class="fas fa-file-excel text-green-500 group-hover:text-white"></i>
                                                </a>

                                                <a href="#"
                                                    onclick="exportToPDF('customer-maintenance', '{{ $request->id }}')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                    title="Xuất PDF">
                                                    <i class="fas fa-file-pdf text-red-500 group-hover:text-white"></i>
                                                </a>
                                            @endif

                                            @if ($request->status === 'pending' && $canDelete)
                                                <form
                                                    action="{{ route('requests.customer-maintenance.destroy', $request->id) }}"
                                                    method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                        title="Xóa"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu yêu cầu bảo trì này?')">
                                                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($canCopy)
                                                <form action="{{ route('requests.customer-maintenance.store') }}"
                                                    method="POST" class="inline-block">
                                                    @csrf
                                                    <input type="hidden" name="copy_from" value="{{ $request->id }}">
                                                    <button type="submit"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-teal-100 hover:bg-teal-500 transition-colors group"
                                                        title="Sao chép">
                                                        <i class="fas fa-copy text-teal-500 group-hover:text-white"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                    Không có phiếu yêu cầu nào
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if (isset($requests) && $requests->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggle = document.querySelector('.req-dropdown-toggle');
            const dropdownMenu = document.querySelector('.req-dropdown-menu');

            dropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation(); // Ngăn sự kiện lan truyền lên document
                dropdownMenu.classList.toggle('hidden');
            });

            // Đóng dropdown khi click ra ngoài
            document.addEventListener('click', function(event) {
                if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        });

        function exportToExcel(type, id) {
            window.location.href = `/requests/${type}/${id}/export-excel`;
        }

        function exportToPDF(type, id) {
            window.location.href = `/requests/${type}/${id}/export-pdf`;
        }
    </script>
@endsection
