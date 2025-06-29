<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thành phẩm - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Quản lý thành phẩm</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <!-- Ô tìm kiếm -->
                    <div class="relative flex-1 flex">
                        <input type="text" id="searchInput" placeholder="Tìm kiếm..." value="{{ request('search') }}"
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tình trạng tồn kho</label>
                                    <select id="stockFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="in_stock">Còn tồn kho</option>
                                        <option value="out_of_stock">Hết tồn kho</option>
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
                                @php
                                    $user = Auth::guard('web')->user();
                                    $isAdmin = $user && $user->role === 'admin';
                                @endphp
                                @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('products.create')))
                                    <button id="importDataButton"
                                        class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-file-import text-green-500 mr-2"></i> Import Data
                                    </button>
                                    <a href="{{ route('products.import.template') }}"
                                        class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fa-solid fa-download text-green-500 mr-2"></i> Tải mẫu Import
                                    </a>
                                @endif
                                <div class="border-t border-gray-200 my-1"></div>
                                @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('products.view')))
                                    <a href="{{ route('products.hidden') }}"
                                        class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-eye-slash text-yellow-500 mr-2"></i> Thành phẩm ẩn
                                    </a>
                                    <a href="{{ route('products.deleted') }}"
                                        class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-trash text-red-500 mr-2"></i> Đã xóa
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('products.export')))
                        <div class="relative inline-block text-left">
                            <button id="exportDropdownButton" type="button"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors group">
                                <i class="fas fa-download mr-2"></i> Xuất dữ liệu
                                <i
                                    class="fas fa-chevron-down ml-2 transition-transform group-hover:transform group-hover:translate-y-0.5"></i>
                            </button>
                            <div id="exportDropdown"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden border border-gray-200 overflow-hidden">
                                <div class="py-1">
                                    <button id="exportExcelButton"
                                        class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-file-excel text-green-500 mr-2"></i> Xuất Excel
                                    </button>
                                    <button id="exportPdfButton"
                                        class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-file-pdf text-red-500 mr-2"></i> Xuất PDF
                                    </button>
                                    <button id="exportFdfButton"
                                        class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-file-code text-blue-500 mr-2"></i> Xuất FDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('products.create')))
                        <a href="{{ route('products.create') }}">
                            <button
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                                <i class="fas fa-plus mr-2"></i> Thêm thành phẩm
                            </button>
                        </a>
                    @endif
                </div>
            </div>
        </header>
        @if (session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        @if (session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif
        <main class="p-6">
            <div class="mb-4">
                <div class="text-sm text-gray-600">
                    <span class="font-medium" id="productCount">{{ $products->count() }}</span> thành phẩm được tìm thấy
                    <div id="filterTags" class="inline">
                        <!-- Filter tags will be populated by JavaScript -->
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                STT
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                MÃ THÀNH PHẨM
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                TÊN THÀNH PHẨM
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                TỔNG TỒN KHO
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                HÀNH ĐỘNG
                            </th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
                        @foreach ($products as $index => $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $product->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2.5 py-1 rounded-md text-sm font-medium 
                                        @if ($product->inventory_quantity > 50) bg-green-100 text-green-800
                                        @elseif($product->inventory_quantity > 20) bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ number_format($product->inventory_quantity, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                    <div class="flex justify-start space-x-2">
                                        @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('products.view_detail')))
                                            <a href="{{ route('products.show', $product->id) }}">
                                                <button
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                    title="Xem">
                                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                                </button>
                                            </a>
                                        @endif
                                        @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('products.edit')))
                                            <a href="{{ route('products.edit', $product->id) }}">
                                                <button
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                    title="Sửa">
                                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                                </button>
                                            </a>
                                        @endif
                                        @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('products.delete')))
                                            <button
                                                onclick="openDeleteModal('{{ $product->id }}', '{{ $product->code }}', {{ $product->inventory_quantity }})"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                title="Xóa">
                                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Loading state -->
                <div id="loadingState" class="hidden p-8 text-center">
                    <i class="fas fa-spinner fa-spin text-gray-400 text-2xl mb-2"></i>
                    <p class="text-gray-500">Đang tìm kiếm...</p>
                </div>

                <!-- No results state -->
                <div id="noResultsState" class="hidden p-8 text-center">
                    <i class="fas fa-search text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Không tìm thấy thành phẩm nào</h3>
                    <p class="text-gray-500">Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal backdrop -->
    <div id="modalBackdrop" class="fixed inset-0 bg-black opacity-50 z-40 hidden"></div>

    <!-- Modal xác nhận xóa khi có tồn kho -->
    <div id="deleteModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Không thể xóa</h3>
            <p class="text-red-700 mb-6">Không thể xóa thành phẩm <span id="productCode"
                    class="font-semibold"></span> vì còn tồn kho: <span id="inventoryQuantity"
                    class="font-semibold"></span></p>
            <div class="flex justify-end">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeDeleteModal()">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- Modal xóa khi inventory = 0 -->
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
                        Không (Đánh dấu đã xóa)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Import modal -->
    <div id="importModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full mx-4">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Import dữ liệu từ Excel</h3>
            <form id="importForm" action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Chọn file Excel
                    </label>
                    <input type="file" id="importFile" name="import_file" accept=".xlsx,.xls,.csv"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <p class="mt-2 text-sm text-gray-500">Hỗ trợ: .xlsx, .xls, .csv (Tối đa 10MB)</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-blue-800 mb-2">Lưu ý quan trọng:</h4>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• Tải mẫu Excel trước khi import</li>
                                <li>• Mã thành phẩm có thể trùng lặp</li>
                                <li>• Các trường có (*) là bắt buộc</li>
                                <li>• Xóa dòng mẫu trước khi import</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
                        onclick="closeImportModal()">
                        Hủy
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
                        <i class="fas fa-upload mr-2"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Images modal -->
    <div id="imagesModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-3xl w-full mx-4 transform transition-transform scale-95 opacity-0"
            id="imagesModalContent">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Hình ảnh thành phẩm: <span id="productNameInModal"
                        class="font-normal"></span></h3>
                <button id="closeImagesModal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mt-4">
                <div id="productImagesContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <!-- Images will be loaded here -->
                </div>
                <div id="noImagesMessage" class="text-center py-8 text-gray-500 hidden">
                    <i class="fas fa-image text-4xl mb-2"></i>
                    <p>thành phẩm này chưa có hình ảnh</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const stockFilter = document.getElementById('stockFilter');
            const filterDropdownButton = document.getElementById('filterDropdownButton');
            const filterDropdown = document.getElementById('filterDropdown');
            const clearFiltersInDropdown = document.getElementById('clearFiltersInDropdown');
            const applyFilters = document.getElementById('applyFilters');
            const productsTableBody = document.getElementById('productsTableBody');
            const productCount = document.getElementById('productCount');
            const filterTags = document.getElementById('filterTags');
            const loadingState = document.getElementById('loadingState');
            const noResultsState = document.getElementById('noResultsState');

            let currentFilters = {
                search: '',
                stock: ''
            };

            function performSearch() {
                const searchTerm = searchInput.value.trim();
                const stock = stockFilter.value;
                
                currentFilters.search = searchTerm;
                currentFilters.stock = stock;
                
                showLoading();
                updateFilterTags();

                const params = new URLSearchParams();
                if (searchTerm) params.append('search', searchTerm);
                if (stock) params.append('stock_filter', stock);

                fetch(`{{ route('products.search.api') }}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();
                        updateTable(data.products);
                        updateProductCount(data.total);
                        
                        if (data.products.length === 0) {
                            showNoResults();
                        } else {
                            hideNoResults();
                        }
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        hideLoading();
                        showNoResults();
                    });
            }

            function updateTable(products) {
                productsTableBody.innerHTML = '';
                
                if (products.length === 0) {
                    return;
                }
                
                products.forEach((product, index) => {
                    const row = createProductRow(product, index + 1);
                    productsTableBody.appendChild(row);
                });
            }

            function createProductRow(product, index) {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors';
                
                const inventoryClass = product.inventory_quantity > 50 ? 'bg-green-100 text-green-800' :
                                     product.inventory_quantity > 20 ? 'bg-yellow-100 text-yellow-800' :
                                     'bg-red-100 text-red-800';
                
                // Get permissions from PHP
                const isAdmin = {{ $isAdmin ? 'true' : 'false' }};
                const canViewDetail = {{ (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('products.view_detail')) ? 'true' : 'false' }};
                const canEdit = {{ (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('products.edit')) ? 'true' : 'false' }};
                const canDelete = {{ (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('products.delete')) ? 'true' : 'false' }};
                
                let actionButtons = '';
                
                if (isAdmin || canViewDetail) {
                    actionButtons += `
                        <a href="/products/${product.id}">
                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                            </button>
                        </a>
                    `;
                }
                
                if (isAdmin || canEdit) {
                    actionButtons += `
                        <a href="/products/${product.id}/edit">
                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                            </button>
                        </a>
                    `;
                }
                
                if (isAdmin || canDelete) {
                    actionButtons += `
                        <button onclick="openDeleteModal('${product.id}', '${product.code}', ${product.inventory_quantity})" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                        </button>
                    `;
                }
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${index}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.code}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${product.name}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2.5 py-1 rounded-md text-sm font-medium ${inventoryClass}">
                            ${new Intl.NumberFormat('vi-VN').format(product.inventory_quantity)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                        <div class="flex justify-start space-x-2">
                            ${actionButtons}
                        </div>
                    </td>
                `;
                
                return row;
            }

            function updateProductCount(count) {
                productCount.textContent = count;
            }

            function updateFilterTags() {
                let tagsHtml = '';

                if (currentFilters.search || currentFilters.stock) {
                    tagsHtml += ' | Đang lọc ';

                    if (currentFilters.search) {
                        tagsHtml += `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs ml-1">Từ khóa: "${currentFilters.search}"</span>`;
                    }
                    if (currentFilters.stock) {
                        const stockText = currentFilters.stock === 'in_stock' ? 'Còn tồn kho' : 'Hết tồn kho';
                        tagsHtml += `<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs ml-1">Tồn kho: ${stockText}</span>`;
                    }
                }

                filterTags.innerHTML = tagsHtml;
            }

            function updateFilterButtonText() {
                const activeFilters = [];
                if (stockFilter.value) activeFilters.push('Tồn kho');
                
                if (activeFilters.length > 0) {
                    filterDropdownButton.innerHTML = `<i class="fas fa-filter mr-2"></i> Bộ lọc (${activeFilters.length}) <i class="fas fa-chevron-down ml-auto"></i>`;
                    filterDropdownButton.classList.add('bg-blue-50', 'border-blue-300', 'text-blue-700');
                    filterDropdownButton.classList.remove('bg-gray-50', 'border-gray-300', 'text-gray-700');
                } else {
                    filterDropdownButton.innerHTML = `<i class="fas fa-filter mr-2"></i> Bộ lọc <i class="fas fa-chevron-down ml-auto"></i>`;
                    filterDropdownButton.classList.remove('bg-blue-50', 'border-blue-300', 'text-blue-700');
                    filterDropdownButton.classList.add('bg-gray-50', 'border-gray-300', 'text-gray-700');
                }
            }

            function showLoading() {
                productsTableBody.style.display = 'none';
                noResultsState.classList.add('hidden');
                loadingState.classList.remove('hidden');
            }

            function hideLoading() {
                loadingState.classList.add('hidden');
                productsTableBody.style.display = '';
            }

            function showNoResults() {
                productsTableBody.style.display = 'none';
                noResultsState.classList.remove('hidden');
            }

            function hideNoResults() {
                noResultsState.classList.add('hidden');
                productsTableBody.style.display = '';
            }

            // Search on button click or Enter key
            searchButton.addEventListener('click', performSearch);
            
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
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
                performSearch();
            });
            
            // Clear filters in dropdown
            clearFiltersInDropdown.addEventListener('click', function() {
                stockFilter.value = '';
                searchInput.value = '';
                currentFilters = { search: '', stock: '' };
                updateFilterButtonText();
                updateFilterTags();
                performSearch();
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                filterDropdown.classList.add('hidden');
            });
            
            // Prevent dropdown from closing when clicking inside
            filterDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Initialize
            updateFilterButtonText();
            updateFilterTags();
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
                        moreActionsDropdown.style.transition = 'opacity 150ms ease-in-out, transform 150ms ease-in-out';
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

        // Export dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const exportDropdownButton = document.getElementById('exportDropdownButton');
            const exportDropdown = document.getElementById('exportDropdown');
            
            // Toggle dropdown on button click
            exportDropdownButton.addEventListener('click', function(e) {
                e.stopPropagation();
                if (exportDropdown.classList.contains('hidden')) {
                    // Show dropdown with animation
                    exportDropdown.classList.remove('hidden');
                    exportDropdown.style.opacity = '0';
                    exportDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        exportDropdown.style.transition = 'opacity 150ms ease-in-out, transform 150ms ease-in-out';
                        exportDropdown.style.opacity = '1';
                        exportDropdown.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    // Hide dropdown with animation
                    exportDropdown.style.opacity = '0';
                    exportDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        exportDropdown.classList.add('hidden');
                        exportDropdown.style.transition = '';
                    }, 150);
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                if (!exportDropdown.classList.contains('hidden')) {
                    // Hide dropdown with animation
                    exportDropdown.style.opacity = '0';
                    exportDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        exportDropdown.classList.add('hidden');
                        exportDropdown.style.transition = '';
                    }, 150);
                }
            });
            
            // Prevent dropdown from closing when clicking inside it
            exportDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Export button handlers
            document.getElementById('exportExcelButton').addEventListener('click', function() {
                const params = new URLSearchParams();
                const searchValue = document.getElementById('searchInput').value;
                const stockValue = document.getElementById('stockFilter').value;
                
                if (searchValue) params.append('search', searchValue);
                if (stockValue) params.append('stock_filter', stockValue);
                
                window.location.href = `{{ route('products.export.excel') }}?${params.toString()}`;
                exportDropdown.classList.add('hidden');
            });

            document.getElementById('exportPdfButton').addEventListener('click', function() {
                const params = new URLSearchParams();
                const searchValue = document.getElementById('searchInput').value;
                const stockValue = document.getElementById('stockFilter').value;
                
                if (searchValue) params.append('search', searchValue);
                if (stockValue) params.append('stock_filter', stockValue);
                
                window.location.href = `{{ route('products.export.pdf') }}?${params.toString()}`;
                exportDropdown.classList.add('hidden');
            });

            document.getElementById('exportFdfButton').addEventListener('click', function() {
                const params = new URLSearchParams();
                const searchValue = document.getElementById('searchInput').value;
                const stockValue = document.getElementById('stockFilter').value;
                
                if (searchValue) params.append('search', searchValue);
                if (stockValue) params.append('stock_filter', stockValue);
                
                window.location.href = `{{ route('products.export.fdf') }}?${params.toString()}`;
                exportDropdown.classList.add('hidden');
            });
        });

        // Import Data button functionality
        document.addEventListener('DOMContentLoaded', function() {
            const importDataButton = document.getElementById('importDataButton');
            
            // Import Data button handler (if exists)
            if (importDataButton) {
                importDataButton.addEventListener('click', function() {
                    document.getElementById('importModal').classList.remove('hidden');
                });
            }
        });

        // Import modal functions
        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
        }

        // Delete modal functions
        function openDeleteModal(productId, productCode, inventoryQuantity) {
            if (inventoryQuantity > 0) {
                // Show inventory warning modal
                document.getElementById('productCode').textContent = productCode;
                document.getElementById('inventoryQuantity').textContent = new Intl.NumberFormat('vi-VN').format(inventoryQuantity);
                document.getElementById('deleteModal').classList.remove('hidden');
            } else {
                // Show confirmation modal for zero inventory
                // Set form actions for both hide and delete forms
                const deleteForm = document.getElementById('deleteForm');
                const hideForm = document.getElementById('hideForm');
                
                if (deleteForm) {
                    deleteForm.action = `/products/${productId}`;
                }
                if (hideForm) {
                    hideForm.action = `/products/${productId}`;
                }
                
                document.getElementById('deleteZeroInventoryModal').classList.remove('hidden');
            }
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function closeDeleteZeroInventoryModal() {
            document.getElementById('deleteZeroInventoryModal').classList.add('hidden');
        }
    </script>
</body>

</html>
