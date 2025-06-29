<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản lý xuất kho - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Quản lý xuất kho</h1>
            <div class="flex items-center gap-2">
                @php
                    $user = Auth::guard('web')->user();
                    $isAdmin = $user && $user->role === 'admin';
                @endphp
                @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.create')))
                <a href="{{ route('inventory.dispatch.create') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i> Tạo phiếu xuất
                </a>
                @endif
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filter and Search -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-6 border border-gray-100">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" id="search_input"
                                placeholder="Tìm kiếm mã phiếu, thành phẩm, khách hàng..."
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <select id="status_filter"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending">Chờ xử lý</option>
                            <option value="approved">Đã duyệt</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                        <select id="type_filter"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tất cả loại hình</option>
                            <option value="project">Dự án</option>
                            <option value="rental">Cho thuê</option>
                            <option value="warranty">Bảo hành</option>
                        </select>
                        <div class="relative flex items-center">
                            <input type="date" id="date_from" placeholder="Từ ngày"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="mx-2 text-gray-500">-</span>
                            <input type="date" id="date_to" placeholder="Đến ngày"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button id="filter_btn"
                            class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-filter mr-1"></i> Lọc
                        </button>
                        <button id="reset_filter_btn"
                            class="bg-gray-100 text-gray-600 hover:bg-gray-200 px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-redo-alt mr-1"></i> Đặt lại
                        </button>
                    </div>
                </div>
            </div>

            <!-- Records Table -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" id="sort_id">
                                        Mã phiếu <i class="fas fa-sort ml-1 text-gray-400"></i>
                                    </div>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" id="sort_date">
                                        Ngày xuất <i class="fas fa-sort ml-1 text-gray-400"></i>
                                    </div>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Người nhận
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Số lượng SP
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dự án
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Người đại diện dự án
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Người tạo
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
                        <tbody class="bg-white divide-y divide-gray-200" id="dispatch_table_body">
                            @forelse($dispatches as $dispatch)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $dispatch->dispatch_code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispatch->dispatch_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispatch->project_receiver }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ $dispatch->total_items }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispatch->dispatch_type === 'project' || $dispatch->dispatch_type === 'warranty' ? $dispatch->project_receiver : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispatch->companyRepresentative->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispatch->creator->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $dispatch->status_color }}-100 text-{{ $dispatch->status_color }}-800">
                                        {{ $dispatch->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.view_detail')))
                                        <a href="{{ route('inventory.dispatch.show', $dispatch->id) }}">
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                title="Xem">
                                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        @endif

                                        @if((!in_array($dispatch->status, ['completed', 'cancelled'])) && ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.edit'))))
                                        <a href="{{ route('inventory.dispatch.edit', $dispatch->id) }}">
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                title="Sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        @endif

                                        @if($dispatch->status === 'pending' && ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.approve'))))
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group approve-btn"
                                            title="Duyệt phiếu xuất" data-id="{{ $dispatch->id }}">
                                            <i class="fas fa-check text-green-500 group-hover:text-white"></i>
                                        </button>
                                        @endif

                                        @if($dispatch->status === 'pending' && ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.cancel'))))
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group cancel-btn"
                                            title="Hủy phiếu xuất" data-id="{{ $dispatch->id }}">
                                            <i class="fas fa-times text-red-500 group-hover:text-white"></i>
                                        </button>
                                        @endif

                                        @if($dispatch->status === 'cancelled' && ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.delete'))))
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-500 transition-colors group delete-btn"
                                            title="Xóa phiếu xuất" data-id="{{ $dispatch->id }}">
                                            <i class="fas fa-trash text-gray-500 group-hover:text-white"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                    Chưa có phiếu xuất kho nào
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-3 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a href="#"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Trước
                        </a>
                        <a href="#"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Sau
                        </a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Hiển thị
                                <span class="font-medium">1</span>
                                đến
                                <span class="font-medium">3</span>
                                trong số
                                <span class="font-medium">3</span>
                                kết quả
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                aria-label="Pagination">
                                <a href="#"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Trang trước</span>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <a href="#" aria-current="page"
                                    class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    1
                                </a>
                                <a href="#"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Trang sau</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lọc và tìm kiếm
            const searchInput = document.getElementById('search_input');
            const statusFilter = document.getElementById('status_filter');
            const typeFilter = document.getElementById('type_filter');
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            const filterBtn = document.getElementById('filter_btn');
            const resetFilterBtn = document.getElementById('reset_filter_btn');
            const tableBody = document.getElementById('dispatch_table_body');

            // Sắp xếp
            const sortId = document.getElementById('sort_id');
            const sortDate = document.getElementById('sort_date');
            
            // Biến lưu trữ trạng thái sắp xếp
            let currentSort = {
                field: 'dispatch_date',
                direction: 'desc'
            };

            // Loading state
            function showLoading() {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Đang tải dữ liệu...
                            </div>
                        </td>
                    </tr>
                `;
            }

            // Perform search and update table
            function performSearch() {
                showLoading();
                
                const params = new URLSearchParams({
                    search: searchInput.value,
                    status: statusFilter.value,
                    dispatch_type: typeFilter.value,
                    date_from: dateFrom.value,
                    date_to: dateTo.value,
                    sort_by: currentSort.field,
                    sort_direction: currentSort.direction
                });

                fetch(`{{ route('inventory.search') }}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateTable(data.dispatches);
                        } else {
                            showError('Có lỗi xảy ra khi tìm kiếm');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('Không thể kết nối tới server');
                    });
            }

            // Update table with new data
            function updateTable(dispatches) {
                if (dispatches.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                Không tìm thấy phiếu xuất kho nào
                            </td>
                        </tr>
                    `;
                    return;
                }

                tableBody.innerHTML = dispatches.map(dispatch => {
                    const isAdmin = {{ Auth::guard('web')->user() && Auth::guard('web')->user()->role === 'admin' ? 'true' : 'false' }};
                    const userPermissions = {
                        view_detail: {{ auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.view_detail') ? 'true' : 'false' }},
                        edit: {{ auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.edit') ? 'true' : 'false' }},
                        approve: {{ auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.approve') ? 'true' : 'false' }},
                        cancel: {{ auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.cancel') ? 'true' : 'false' }},
                        delete: {{ auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory.delete') ? 'true' : 'false' }}
                    };

                    const canView = isAdmin || userPermissions.view_detail;
                    const canEdit = isAdmin || (userPermissions.edit && !['completed', 'cancelled'].includes(dispatch.status));
                    const canApprove = isAdmin || (userPermissions.approve && dispatch.status === 'pending');
                    const canCancel = isAdmin || (userPermissions.cancel && dispatch.status === 'pending');
                    const canDelete = (dispatch.status === 'cancelled') && (isAdmin || userPermissions.delete);

                    return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${dispatch.dispatch_code}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${dispatch.dispatch_date}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${dispatch.project_receiver}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            ${dispatch.total_items}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${dispatch.project_receiver}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${dispatch.company_representative}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${dispatch.creator}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-${dispatch.status_color}-100 text-${dispatch.status_color}-800">
                                ${dispatch.status_label}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                ${canView ? `
                                    <a href="/inventory/dispatch/${dispatch.id}">
                                        <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                ` : ''}
                                ${canEdit ? `
                                    <a href="/inventory/dispatch/${dispatch.id}/edit">
                                        <button class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                ` : ''}
                                ${canApprove ? `
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group approve-btn" title="Duyệt phiếu xuất" data-id="${dispatch.id}">
                                        <i class="fas fa-check text-green-500 group-hover:text-white"></i>
                                    </button>
                                ` : ''}
                                ${canCancel ? `
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group cancel-btn" title="Hủy phiếu xuất" data-id="${dispatch.id}">
                                        <i class="fas fa-times text-red-500 group-hover:text-white"></i>
                                    </button>
                                ` : ''}
                                ${canDelete ? `
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-500 transition-colors group delete-btn" title="Xóa phiếu xuất" data-id="${dispatch.id}">
                                        <i class="fas fa-trash text-gray-500 group-hover:text-white"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                    `;
                }).join('');

                // Re-attach event listeners to new buttons
                attachActionButtonListeners();
            }

            // Show error message
            function showError(message) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-red-500">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            ${message}
                        </td>
                    </tr>
                `;
            }

            // Event listeners for filter controls
            filterBtn.addEventListener('click', performSearch);
            
            resetFilterBtn.addEventListener('click', function() {
                searchInput.value = '';
                statusFilter.value = '';
                typeFilter.value = '';
                dateFrom.value = '';
                dateTo.value = '';
                currentSort = { field: 'dispatch_date', direction: 'desc' };
                updateSortIcons();
                performSearch();
            });

            // Auto-search on input change (debounced)
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 500);
            });

            // Auto-search on filter change
            statusFilter.addEventListener('change', performSearch);
            typeFilter.addEventListener('change', performSearch);
            dateFrom.addEventListener('change', performSearch);
            dateTo.addEventListener('change', performSearch);

            // Sorting functionality
            function updateSortIcons() {
                // Reset all sort icons
                document.querySelectorAll('[id^="sort_"] i').forEach(icon => {
                    icon.className = 'fas fa-sort ml-1 text-gray-400';
                });

                // Update active sort icon
                const activeIcon = document.querySelector(`#sort_${currentSort.field.replace('_', '')} i`);
                if (activeIcon) {
                    activeIcon.className = currentSort.direction === 'asc' 
                        ? 'fas fa-sort-up ml-1 text-blue-500' 
                        : 'fas fa-sort-down ml-1 text-blue-500';
                }
            }

            sortId.addEventListener('click', function() {
                if (currentSort.field === 'dispatch_code') {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.field = 'dispatch_code';
                    currentSort.direction = 'asc';
                }
                updateSortIcons();
                performSearch();
            });

            sortDate.addEventListener('click', function() {
                if (currentSort.field === 'dispatch_date') {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.field = 'dispatch_date';
                    currentSort.direction = 'desc';
                }
                updateSortIcons();
                performSearch();
            });

            // Initialize sort icons
            updateSortIcons();

            // Action button event handlers
            function attachActionButtonListeners() {
                const approveButtons = document.querySelectorAll('.approve-btn');
                const cancelButtons = document.querySelectorAll('.cancel-btn');
                const deleteButtons = document.querySelectorAll('.delete-btn');
                
                // Xử lý duyệt phiếu xuất
                approveButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const dispatchId = this.getAttribute('data-id');
                        if (confirm(`Bạn có chắc chắn muốn duyệt phiếu xuất ${dispatchId}?`)) {
                            // Disable button để tránh double click
                            this.disabled = true;
                            this.innerHTML = '<i class="fas fa-spinner fa-spin text-green-500"></i>';
                            
                            fetch(`/inventory/dispatch/${dispatchId}/approve`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    performSearch(); // Refresh table instead of reload
                                } else {
                                    alert('Lỗi: ' + data.message);
                                    // Re-enable button nếu lỗi
                                    this.disabled = false;
                                    this.innerHTML = '<i class="fas fa-check text-green-500"></i>';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Có lỗi xảy ra khi duyệt phiếu xuất');
                                // Re-enable button nếu lỗi
                                this.disabled = false;
                                this.innerHTML = '<i class="fas fa-check text-green-500"></i>';
                            });
                        }
                    });
                });
                
                // Xử lý hủy phiếu xuất
                cancelButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const dispatchId = this.getAttribute('data-id');
                        if (confirm(`Bạn có chắc chắn muốn hủy phiếu xuất ${dispatchId}?`)) {
                            // Disable button để tránh double click
                            this.disabled = true;
                            this.innerHTML = '<i class="fas fa-spinner fa-spin text-red-500"></i>';
                            
                            fetch(`/inventory/dispatch/${dispatchId}/cancel`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    performSearch(); // Refresh table instead of reload
                                } else {
                                    alert('Lỗi: ' + data.message);
                                    // Re-enable button nếu lỗi
                                    this.disabled = false;
                                    this.innerHTML = '<i class="fas fa-times text-red-500"></i>';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Có lỗi xảy ra khi hủy phiếu xuất');
                                // Re-enable button nếu lỗi
                                this.disabled = false;
                                this.innerHTML = '<i class="fas fa-times text-red-500"></i>';
                            });
                        }
                    });
                });
                
                // Xử lý xóa phiếu xuất
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const dispatchId = this.getAttribute('data-id');
                        if (confirm(`Bạn có chắc chắn muốn xóa phiếu xuất ${dispatchId}? Thao tác này không thể hoàn tác.`)) {
                            // Disable button để tránh double click
                            this.disabled = true;
                            this.innerHTML = '<i class="fas fa-spinner fa-spin text-gray-500"></i>';
                            
                            fetch(`/inventory/dispatch/${dispatchId}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    performSearch(); // Refresh table instead of reload
                                } else {
                                    alert('Lỗi: ' + data.message);
                                    // Re-enable button nếu lỗi
                                    this.disabled = false;
                                    this.innerHTML = '<i class="fas fa-trash text-gray-500"></i>';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Có lỗi xảy ra khi xóa phiếu xuất');
                                // Re-enable button nếu lỗi
                                this.disabled = false;
                                this.innerHTML = '<i class="fas fa-trash text-gray-500"></i>';
                            });
                        }
                    });
                });
            }

            // Initial attachment of event listeners for existing buttons
            attachActionButtonListeners();

            // Perform initial search to load data
            performSearch();
        });
    </script>
</body>

</html> 