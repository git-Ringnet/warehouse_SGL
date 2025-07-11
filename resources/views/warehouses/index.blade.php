<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý kho hàng - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    @php
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
    @endphp
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý kho hàng</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <!-- Ô tìm kiếm -->
                    <div class="relative flex-1 flex">
                        <input type="text" id="searchInput" placeholder="Tìm kiếm kho hàng..."
                            value="{{ request('search') }}"
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
                            class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-lg z-30 hidden border border-gray-200">
                            <div class="p-4 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Người quản lý</label>
                                    <select id="managerFilter"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                                        <option value="">Tất cả người quản lý</option>
                                        @foreach ($managers as $manager)
                                            <option value="{{ $manager }}"
                                                {{ request('manager') == $manager ? 'selected' : '' }}>
                                                {{ $manager }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-200">
                                    <button id="clearFiltersInDropdown"
                                        class="text-gray-500 hover:text-gray-700 text-sm">
                                        <i class="fas fa-times mr-1"></i> Xóa bộ lọc
                                    </button>
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
                    @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('warehouses.view')))
                    <!-- Dropdown menu cho các action phụ -->
                    <div class="relative inline-block text-left">
                        <button id="moreActionsButton" type="button"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors group">
                            <i class="fas fa-ellipsis-h mr-2 text-[23px]"></i>
                            <i
                                class="fas fa-chevron-down ml-2 transition-transform group-hover:transform group-hover:translate-y-0.5"></i>
                        </button>
                        <div id="moreActionsDropdown"
                            class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-20 hidden border border-gray-200 overflow-hidden">
                            <div class="py-1">
                                <a href="{{ route('warehouses.hidden') }}"
                                    class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-eye-slash text-yellow-500 mr-2"></i> Kho hàng ẩn
                                </a>
                                <a href="{{ route('warehouses.deleted') }}"
                                    class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-trash text-red-500 mr-2"></i> Kho hàng đã xóa
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('warehouses.create')))
                    <a href="{{ route('warehouses.create') }}">
                        <button
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-plus mr-2"></i> Thêm kho hàng
                        </button>
                    </a>
                    @endif
                </div>
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mã kho</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên kho</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Địa chỉ</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Người quản lý</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tình trạng tồn kho</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($warehouses as $index => $warehouse)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $warehouse->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $warehouse->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $warehouse->address ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ optional($warehouse->managerEmployee)->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $totalQuantity = $warehouse->warehouseMaterials()->sum('quantity');
                                    @endphp
                                    @if($totalQuantity > 0)
                                        <span class="px-2 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">
                                            Còn tồn kho
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                            Hết tồn kho
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('warehouses.view_detail')))
                                    <a href="{{ route('warehouses.show', $warehouse->id) }}">
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem chi tiết">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                    @endif
                                    @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('warehouses.edit')))
                                    <a href="{{ route('warehouses.edit', $warehouse->id) }}">
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                            title="Sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                    @endif
                                    @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('warehouses.delete')))
                                    <button
                                        onclick="openDeleteModal('{{ $warehouse->id }}', '{{ $warehouse->code }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                        title="Xóa">
                                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @if (count($warehouses) == 0)
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Không có dữ liệu kho hàng
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4 rounded-lg shadow-sm">
                <div class="flex flex-1 justify-between sm:hidden">
                    <a href="#"
                        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Trang
                        trước</a>
                    <a href="#"
                        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Trang
                        sau</a>
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Hiển thị <span class="font-medium">1</span> đến <span
                                class="font-medium">{{ count($warehouses) }}</span> của
                            <span class="font-medium">{{ count($warehouses) }}</span> kết quả
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <a href="#"
                                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left h-5 w-5"></i>
                            </a>
                            <a href="#" aria-current="page"
                                class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">1</a>
                            <a href="#"
                                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right h-5 w-5"></i>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal báo lỗi khi còn tồn kho -->
    <div id="errorModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Không thể xóa</h3>
            <p class="text-red-700 mb-6">Không thể xóa kho hàng <span id="warehouseCodeError"
                    class="font-semibold"></span> vì còn tồn kho: <span id="totalQuantity"
                    class="font-semibold"></span> đơn vị</p>
            <div class="flex justify-end">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeErrorModal()">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận xóa khi inventory = 0 -->
    <div id="deleteZeroInventoryModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xác nhận thao tác</h3>
            <p class="text-gray-700 mb-6">Thao tác xóa có thể làm mất dữ liệu. Bạn muốn xác nhận ngừng sử dụng và ẩn
                hạng mục này thay cho việc xóa?</p>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeDeleteZeroInventoryModal()">
                    Hủy
                </button>
                <form id="hideForm" action="" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="action" value="hide">
                    <button type="submit"
                        class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors">
                        Có (Ẩn hạng mục)
                    </button>
                </form>
                <form id="deleteForm" action="" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="action" value="delete">
                    <button type="submit"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                        Không (Xóa)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(warehouseId, warehouseCode) {
            // Gọi API để kiểm tra số lượng tồn kho
            fetch(`/warehouses/${warehouseId}/check-inventory`)
                .then(response => response.json())
                .then(data => {
                    if (data.hasInventory) {
                        // Hiển thị modal báo lỗi
                        document.getElementById('warehouseCodeError').textContent = warehouseCode;
                        document.getElementById('totalQuantity').textContent = new Intl.NumberFormat('vi-VN').format(
                            data.totalQuantity);
                        document.getElementById('errorModal').classList.remove('hidden');
                    } else {
                        // Hiển thị modal xác nhận ẩn/xóa
                        const deleteForm = document.getElementById('deleteForm');
                        const hideForm = document.getElementById('hideForm');

                        if (deleteForm) {
                            deleteForm.action = `/warehouses/${warehouseId}`;
                        }
                        if (hideForm) {
                            hideForm.action = `/warehouses/${warehouseId}`;
                        }

                        document.getElementById('deleteZeroInventoryModal').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi kiểm tra tồn kho');
                });
        }

        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
        }

        function closeDeleteZeroInventoryModal() {
            document.getElementById('deleteZeroInventoryModal').classList.add('hidden');
        }

        // Search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const filterDropdownButton = document.getElementById('filterDropdownButton');
            const filterDropdown = document.getElementById('filterDropdown');
            const managerFilter = document.getElementById('managerFilter');
            const applyFilters = document.getElementById('applyFilters');
            const clearFiltersInDropdown = document.getElementById('clearFiltersInDropdown');
            const warehouseTableBody = document.querySelector('tbody');
            const paginationInfo = document.querySelector('.text-sm.text-gray-700');

            function performAjaxSearch() {
                const params = new URLSearchParams();

                if (searchInput.value) {
                    params.append('search', searchInput.value);
                }
                if (managerFilter.value) {
                    params.append('manager', managerFilter.value);
                }

                // Show loading state
                warehouseTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Đang tải...
                            </div>
                        </td>
                    </tr>
                `;

                // Make AJAX request
                fetch(`/warehouses/api-search?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update URL without reloading
                            const url = new URL(window.location.href);
                            url.search = params.toString();
                            window.history.pushState({}, '', url);

                            // Update table content
                            if (data.data.warehouses.length === 0) {
                                warehouseTableBody.innerHTML = `
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Không có dữ liệu kho hàng
                                        </td>
                                    </tr>
                                `;
                            } else {
                                warehouseTableBody.innerHTML = data.data.warehouses.map((warehouse, index) => {
                                    // Calculate inventory status
                                    let inventoryStatus = '';
                                    if (warehouse.total_quantity > 0) {
                                        inventoryStatus = `
                                            <span class="px-2 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">
                                                Còn tồn kho
                                            </span>`;
                                    } else {
                                        inventoryStatus = `
                                            <span class="px-2 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                                Hết tồn kho
                                            </span>`;
                                    }

                                    return `
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${index + 1}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${warehouse.code}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${warehouse.name}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${warehouse.address ?? ''}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${warehouse.manager}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">${inventoryStatus}</td>
                                        <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                            <a href="/warehouses/${warehouse.id}">
                                                <button
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                    title="Xem">
                                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                                </button>
                                            </a>
                                            <a href="/warehouses/${warehouse.id}/edit">
                                                <button
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                    title="Sửa">
                                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                                </button>
                                            </a>
                                            <button onclick="openDeleteModal('${warehouse.id}', '${warehouse.code}')"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                title="Xóa">
                                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    `;
                                }).join('');
                            }

                            // Update pagination info
                            if (paginationInfo) {
                                paginationInfo.innerHTML = `
                                    Hiển thị <span class="font-medium">1</span> đến <span class="font-medium">${data.data.totalCount}</span> của
                                    <span class="font-medium">${data.data.totalCount}</span> kết quả
                                `;
                            }

                            // Update manager filter options
                            const managerFilterOptions = data.data.managers.map(manager =>
                                `<option value="${manager}" ${managerFilter.value === manager ? 'selected' : ''}>${manager}</option>`
                            ).join('');
                            managerFilter.innerHTML = `
                                <option value="">Tất cả người quản lý</option>
                                ${managerFilterOptions}
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        warehouseTableBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-red-500">
                                    Có lỗi xảy ra khi tải dữ liệu
                                </td>
                            </tr>
                        `;
                    });
            }

            function updateFilterButtonText() {
                const activeFilters = [];
                if (managerFilter.value) activeFilters.push('manager');

                if (activeFilters.length > 0) {
                    filterDropdownButton.innerHTML =
                        `<i class="fas fa-filter mr-2"></i> Bộ lọc (${activeFilters.length}) <i class="fas fa-chevron-down ml-auto"></i>`;
                    filterDropdownButton.classList.add('bg-blue-50', 'border-blue-300', 'text-blue-700');
                    filterDropdownButton.classList.remove('bg-gray-50', 'border-gray-300', 'text-gray-700');
                } else {
                    filterDropdownButton.innerHTML =
                        `<i class="fas fa-filter mr-2"></i> Bộ lọc <i class="fas fa-chevron-down ml-auto"></i>`;
                    filterDropdownButton.classList.remove('bg-blue-50', 'border-blue-300', 'text-blue-700');
                    filterDropdownButton.classList.add('bg-gray-50', 'border-gray-300', 'text-gray-700');
                }
            }

            // Search on button click or Enter key
            searchButton.addEventListener('click', performAjaxSearch);

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performAjaxSearch();
                }
            });

            // Filter dropdown toggle
            filterDropdownButton.addEventListener('click', function(e) {
                e.stopPropagation();
                filterDropdown.classList.toggle('hidden');
            });

            // Apply filters button  
            applyFilters.addEventListener('click', function() {
                filterDropdown.classList.add('hidden');
                updateFilterButtonText();
                performAjaxSearch();
            });

            // Clear filters in dropdown
            clearFiltersInDropdown.addEventListener('click', function() {
                managerFilter.value = '';
                searchInput.value = '';
                updateFilterButtonText();
                performAjaxSearch();
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                filterDropdown.classList.add('hidden');
            });

            // Prevent dropdown from closing when clicking inside
            filterDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Update filter button text on load
            updateFilterButtonText();
        });

        // More actions dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const moreActionsButton = document.getElementById('moreActionsButton');
            const moreActionsDropdown = document.getElementById('moreActionsDropdown');

            // Toggle dropdown on button click
            moreActionsButton.addEventListener('click', function(e) {
                e.stopPropagation();
                if (moreActionsDropdown.classList.contains('hidden')) {
                    // Show dropdown with animation
                    moreActionsDropdown.classList.remove('hidden');
                    moreActionsDropdown.style.opacity = '0';
                    moreActionsDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        moreActionsDropdown.style.transition =
                            'opacity 150ms ease-in-out, transform 150ms ease-in-out';
                        moreActionsDropdown.style.opacity = '1';
                        moreActionsDropdown.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    // Hide dropdown with animation
                    moreActionsDropdown.style.opacity = '0';
                    moreActionsDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        moreActionsDropdown.classList.add('hidden');
                        moreActionsDropdown.style.transition = '';
                    }, 150);
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                if (!moreActionsDropdown.classList.contains('hidden')) {
                    // Hide dropdown with animation
                    moreActionsDropdown.style.opacity = '0';
                    moreActionsDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        moreActionsDropdown.classList.add('hidden');
                        moreActionsDropdown.style.transition = '';
                    }, 150);
                }
            });

            // Prevent dropdown from closing when clicking inside it
            moreActionsDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
</body>

</html>
