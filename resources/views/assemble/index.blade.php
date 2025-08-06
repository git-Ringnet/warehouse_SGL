<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý lắp ráp - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <style>
        .z-60 {
            z-index: 60;
        }

        .z-70 {
            z-index: 70;
        }
    </style>
</head>

<body class="bg-gray-50">
    @php
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
    @endphp
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area ml-64">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý lắp ráp</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <!-- Ô tìm kiếm -->
                    <div class="relative flex-1 flex">
                        <input type="text" id="searchInput" placeholder="Tìm kiếm..."
                            class="flex-1 border border-gray-300 rounded-l-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700" />
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <button id="searchButton" type="button"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-r-lg border border-blue-500 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <!-- Dropdown bộ lọc -->
                    <div class="relative inline-block text-left">
                        <button id="filterDropdownButton" type="button"
                            class="border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 flex items-center transition-colors min-w-[120px]">
                            <i class="fas fa-filter mr-2"></i> Bộ lọc
                            <i class="fas fa-chevron-down ml-auto"></i>
                        </button>
                        <div id="filterDropdown"
                            class="absolute left-0 mt-2 w-80 bg-white rounded-md shadow-lg z-30 hidden border border-gray-200">
                            <div class="p-4 space-y-3">
                                <div class="gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                                        <select id="statusFilter"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                                            <option value="">Tất cả trạng thái</option>
                                            @foreach ($statuses as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Người phụ trách</label>
                                    <select id="employeeFilter"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                                        <option value="">Tất cả nhân viên</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Từ ngày (lắp
                                            ráp)</label>
                                        <input type="date" id="dateFromFilter"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Đến ngày (lắp
                                            ráp)</label>
                                        <input type="date" id="dateToFilter"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                                    </div>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-200">
                                    <a href="{{ route('assemblies.index') }}"
                                        class="text-gray-500 hover:text-gray-700 text-sm">
                                        <i class="fas fa-times mr-1"></i> Xóa bộ lọc
                                    </a>
                                    <button id="applyFilters"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                        Áp dụng
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 items-center">
                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('assembly.create')))
                        <a href="{{ route('assemblies.create') }}">
                            <button
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                                <i class="fas fa-plus mr-2"></i> Tạo phiếu lắp ráp
                            </button>
                        </a>
                    @endif
                </div>
            </div>
        </header>
        <main class="p-6">
            <div id="notificationArea">
                @if (session('success'))
                    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4"
                        id="successAlert">
                        {!! session('success') !!}
                    </div>
                @endif
                @if ($errors->has('error'))
                    <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4"
                        id="errorAlert">
                        {{ $errors->first('error') }}
                    </div>
                @endif
            </div>
            <div class="mb-4">
                <div class="text-sm text-gray-600">
                    <span class="font-medium" id="resultCount">{{ $assemblies->count() }}</span> phiếu lắp ráp được tìm
                    thấy
                    <div id="filterTags" class="inline">
                        @if (request()->hasAny(['search', 'status', 'warehouse', 'employee', 'date_from', 'date_to']))
                            | Đang lọc
                            @if (request('search'))
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs ml-1">
                                    Từ khóa: "{{ request('search') }}"
                                </span>
                            @endif
                            @if (request('status'))
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs ml-1">
                                    Trạng thái: {{ $statuses[request('status')] ?? request('status') }}
                                </span>
                            @endif
                            @if (request('warehouse'))
                                @php
                                    $selectedWarehouse = $warehouses->firstWhere('id', request('warehouse'));
                                @endphp
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs ml-1">
                                    Kho: {{ $selectedWarehouse->name ?? request('warehouse') }}
                                </span>
                            @endif
                            @if (request('employee'))
                                @php
                                    $selectedEmployee = $employees->firstWhere('id', request('employee'));
                                @endphp
                                <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs ml-1">
                                    Nhân viên: {{ $selectedEmployee->name ?? request('employee') }}
                                </span>
                            @endif
                            @if (request('date_from') || request('date_to'))
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs ml-1">
                                    Thời gian: {{ request('date_from') ?? 'Từ đầu' }} -
                                    {{ request('date_to') ?? 'Đến cuối' }}
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div id="assemblyTableContainer">
                @include('assemble.partials.assembly-list', ['assemblies' => $assemblies])
            </div>
        </main>
    </div>

    <script>
        // Function to show notifications
        function showNotification(message, type = 'info') {
            const notificationArea = document.getElementById('notificationArea');
            const notification = document.createElement('div');
            
            notification.className = `px-4 py-3 rounded-lg mb-4 ${
                type === 'success' ? 'bg-green-100 border border-green-200 text-green-700' :
                type === 'error' ? 'bg-red-100 border border-red-200 text-red-700' :
                'bg-blue-100 border border-blue-200 text-blue-700'
            }`;
            
            notification.innerHTML = message;
            notification.id = 'dynamicNotification';
            
            // Remove existing dynamic notification
            const existingNotification = document.getElementById('dynamicNotification');
            if (existingNotification) {
                existingNotification.remove();
            }
            
            // Insert at the beginning of notification area
            notificationArea.insertBefore(notification, notificationArea.firstChild);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.transition = 'opacity 0.5s ease-out';
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 500);
                }
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const filterDropdownButton = document.getElementById('filterDropdownButton');
            const filterDropdown = document.getElementById('filterDropdown');
            const applyFiltersButton = document.getElementById('applyFilters');
            const clearFiltersButton = document.getElementById('clearFiltersInDropdown');
            const assemblyTableContainer = document.getElementById('assemblyTableContainer');
            const resultCount = document.getElementById('resultCount');
            const filterTags = document.getElementById('filterTags');

            // Auto-hide success/error alerts after 5 seconds
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');

            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.transition = 'opacity 0.5s ease-out';
                    successAlert.style.opacity = '0';
                    setTimeout(() => {
                        successAlert.remove();
                    }, 500);
                }, 5000);
            }

            if (errorAlert) {
                setTimeout(() => {
                    errorAlert.style.transition = 'opacity 0.5s ease-out';
                    errorAlert.style.opacity = '0';
                    setTimeout(() => {
                        errorAlert.remove();
                    }, 500);
                }, 5000);
            }

            // Toggle filter dropdown
            filterDropdownButton.addEventListener('click', function(e) {
                e.stopPropagation();
                if (filterDropdown.classList.contains('hidden')) {
                    // Show dropdown with animation
                    filterDropdown.classList.remove('hidden');
                    filterDropdown.style.opacity = '0';
                    filterDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        filterDropdown.style.transition =
                            'opacity 150ms ease-in-out, transform 150ms ease-in-out';
                        filterDropdown.style.opacity = '1';
                        filterDropdown.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    // Hide dropdown with animation
                    filterDropdown.style.opacity = '0';
                    filterDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        filterDropdown.classList.add('hidden');
                        filterDropdown.style.transition = '';
                    }, 150);
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!filterDropdown.contains(e.target) && !filterDropdownButton.contains(e.target)) {
                    filterDropdown.classList.add('hidden');
                }
            });

            // Prevent dropdown from closing when clicking inside it
            filterDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Handle approve forms with AJAX
            document.addEventListener('submit', function(e) {
                if (e.target.action && e.target.action.includes('/approve')) {
                    e.preventDefault();

                    const form = e.target;
                    const button = form.querySelector('button[type="submit"]');
                    const originalText = button.innerHTML;

                    // Show loading state
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: new FormData(form)
                        })
                        .then(response => {
                            if (response.redirected) {
                                // If redirected, follow the redirect
                                window.location.href = response.url;
                            } else if (!response.ok) {
                                // Handle error responses
                                return response.json().then(errorData => {
                                    throw new Error(errorData.error || 'Có lỗi xảy ra khi duyệt phiếu lắp ráp');
                                });
                            } else {
                                return response.json();
                            }
                        })
                        .then(data => {
                            if (data && data.success) {
                                // Show success message and reload
                                showNotification('Phiếu lắp ráp đã được duyệt thành công!', 'success');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else if (data && data.error) {
                                // Show error message
                                showNotification(data.error, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // Show error message
                            showNotification(error.message || 'Có lỗi xảy ra khi duyệt phiếu lắp ráp', 'error');
                        })
                        .finally(() => {
                            // Restore button state
                            button.disabled = false;
                            button.innerHTML = originalText;
                        });
                }
            });

            // Search button click
            searchButton.addEventListener('click', function() {
                const searchTerm = searchInput.value;
                const statusFilter = document.getElementById('statusFilter').value;
                const employeeFilter = document.getElementById('employeeFilter').value;
                const dateFromFilter = document.getElementById('dateFromFilter').value;
                const dateToFilter = document.getElementById('dateToFilter').value;

                const params = new URLSearchParams();
                if (searchTerm) params.append('search', searchTerm);
                if (statusFilter) params.append('status', statusFilter);
                if (employeeFilter) params.append('employee', employeeFilter);
                if (dateFromFilter) params.append('date_from', dateFromFilter);
                if (dateToFilter) params.append('date_to', dateToFilter);

                const newUrl =
                    `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
                window.history.pushState({}, '', newUrl);
                window.location.reload();
            });

            // Search on Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = searchInput.value;
                    const statusFilter = document.getElementById('statusFilter').value;
                    const employeeFilter = document.getElementById('employeeFilter').value;
                    const dateFromFilter = document.getElementById('dateFromFilter').value;
                    const dateToFilter = document.getElementById('dateToFilter').value;

                    const params = new URLSearchParams();
                    if (searchTerm) params.append('search', searchTerm);
                    if (statusFilter) params.append('status', statusFilter);
                    if (employeeFilter) params.append('employee', employeeFilter);
                    if (dateFromFilter) params.append('date_from', dateFromFilter);
                    if (dateToFilter) params.append('date_to', dateToFilter);

                    const newUrl =
                        `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
                    window.history.pushState({}, '', newUrl);
                    window.location.reload();
                }
            });

            // Apply filters
            applyFiltersButton.addEventListener('click', function() {
                const searchTerm = searchInput.value;
                const statusFilter = document.getElementById('statusFilter').value;
                const employeeFilter = document.getElementById('employeeFilter').value;
                const dateFromFilter = document.getElementById('dateFromFilter').value;
                const dateToFilter = document.getElementById('dateToFilter').value;

                const params = new URLSearchParams();
                if (searchTerm) params.append('search', searchTerm);
                if (statusFilter) params.append('status', statusFilter);
                if (employeeFilter) params.append('employee', employeeFilter);
                if (dateFromFilter) params.append('date_from', dateFromFilter);
                if (dateToFilter) params.append('date_to', dateToFilter);

                const newUrl =
                    `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
                window.history.pushState({}, '', newUrl);
                window.location.reload();
            });

            // Update filter tags
            function updateFilterTags() {
                const searchTerm = searchInput.value;
                const statusFilter = document.getElementById('statusFilter').value;
                const warehouseFilter = document.getElementById('warehouseFilter').value;
                const employeeFilter = document.getElementById('employeeFilter').value;
                const dateFromFilter = document.getElementById('dateFromFilter').value;
                const dateToFilter = document.getElementById('dateToFilter').value;

                let tagsHtml = '';

                if (searchTerm || statusFilter || warehouseFilter || employeeFilter || dateFromFilter ||
                    dateToFilter) {
                    tagsHtml = ' | Đang lọc';

                    if (searchTerm) {
                        tagsHtml +=
                            ` <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs ml-1">Từ khóa: "${searchTerm}"</span>`;
                    }

                    if (statusFilter) {
                        const statusText = document.getElementById('statusFilter').options[document.getElementById(
                            'statusFilter').selectedIndex].text;
                        tagsHtml +=
                            ` <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs ml-1">Trạng thái: ${statusText}</span>`;
                    }

                    if (warehouseFilter) {
                        const warehouseText = document.getElementById('warehouseFilter').options[document
                            .getElementById('warehouseFilter').selectedIndex].text;
                        tagsHtml +=
                            ` <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs ml-1">Kho: ${warehouseText}</span>`;
                    }

                    if (employeeFilter) {
                        const employeeText = document.getElementById('employeeFilter').options[document
                            .getElementById('employeeFilter').selectedIndex].text;
                        tagsHtml +=
                            ` <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs ml-1">Nhân viên: ${employeeText}</span>`;
                    }

                    if (dateFromFilter || dateToFilter) {
                        tagsHtml +=
                            ` <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs ml-1">Thời gian: ${dateFromFilter || 'Từ đầu'} - ${dateToFilter || 'Đến cuối'}</span>`;
                    }
                }

                filterTags.innerHTML = tagsHtml;
            }

            // Load current filter values from URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('search')) searchInput.value = urlParams.get('search');
            if (urlParams.get('status')) document.getElementById('statusFilter').value = urlParams.get('status');
            if (urlParams.get('warehouse')) document.getElementById('warehouseFilter').value = urlParams.get(
                'warehouse');
            if (urlParams.get('employee')) document.getElementById('employeeFilter').value = urlParams.get(
                'employee');
            if (urlParams.get('date_from')) document.getElementById('dateFromFilter').value = urlParams.get(
                'date_from');
            if (urlParams.get('date_to')) document.getElementById('dateToFilter').value = urlParams.get('date_to');
        });
    </script>
</body>

</html>
