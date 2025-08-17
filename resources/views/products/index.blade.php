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
                <form action="{{ route('products.index') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                    <!-- Ô tìm kiếm -->
                    <div class="relative flex-1 flex">
                        <input type="text" name="search" placeholder="Tìm kiếm..." value="{{ request('search') }}"
                            class="flex-1 border border-gray-300 rounded-l-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700" />
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <button type="submit" 
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tổng tồn kho (bé hơn hoặc bằng)</label>
                                    <input type="number" name="stock_quantity" min="0" placeholder="Nhập số lượng" 
                                        value="{{ request('stock_quantity') }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-200">
                                    <a href="{{ route('products.index') }}"
                                        class="text-gray-500 hover:text-gray-700 text-sm">
                                        <i class="fas fa-times mr-1"></i> Xóa bộ lọc
                                    </a>
                                    <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                        Áp dụng
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
                                    {{-- <button id="exportFdfButton"
                                        class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-file-code text-blue-500 mr-2"></i> Xuất FDF
                                    </button> --}}
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
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
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group"
                                            title="Xem hình ảnh"
                                            onclick="openProductImages('{{ $product->id }}', '{{ $product->name }}')">
                                            <i class="fas fa-images text-purple-500 group-hover:text-white"></i>
                                        </button>
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
            </div>
            <div class="mt-4">
                {{ $products->links() }}
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
                                <li>• Mã thành phẩm không được trùng lặp</li>
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

    <!-- Image Modal -->
    <div id="fullImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-[9999] hidden">
        <div class="max-w-4xl max-h-full p-4">
            <div class="relative">
                <img id="fullModalImage" src="" alt="" class="max-w-full max-h-[80vh] rounded-lg">
                <button onclick="closeFullImageModal()"
                    class="absolute top-2 right-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p id="fullModalImageCaption" class="text-white text-center mt-2"></p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterDropdownButton = document.getElementById('filterDropdownButton');
            const filterDropdown = document.getElementById('filterDropdown');
            const stockQuantityFilter = document.querySelector('input[name="stock_quantity"]');

            function updateFilterButtonText() {
                const activeFilters = [];
                if (stockQuantityFilter && stockQuantityFilter.value) activeFilters.push('Tồn kho');
                
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
            
            // Filter dropdown toggle
            filterDropdownButton.addEventListener('click', function(e) {
                e.stopPropagation();
                filterDropdown.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                filterDropdown.classList.add('hidden');
            });
            
            // Prevent dropdown from closing when clicking inside
            filterDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
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
                const params = new URLSearchParams(window.location.search);
                window.location.href = `{{ route('products.export.excel') }}?${params.toString()}`;
                exportDropdown.classList.add('hidden');
            });

            document.getElementById('exportPdfButton').addEventListener('click', function() {
                const params = new URLSearchParams(window.location.search);
                window.location.href = `{{ route('products.export.pdf') }}?${params.toString()}`;
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

    <!-- Xem hình ảnh thành phẩm -->
    <script>
        // Xem hình ảnh thành phẩm
        function openProductImages(productId, productName) {
            const imagesModal = document.getElementById('imagesModal');
            const imagesModalContent = document.getElementById('imagesModalContent');
            const productImagesContainer = document.getElementById('productImagesContainer');
            const noImagesMessage = document.getElementById('noImagesMessage');
            const productNameInModal = document.getElementById('productNameInModal');
            
            // Set product name
            productNameInModal.textContent = productName;
            
            // Show modal first
            imagesModal.classList.remove('hidden');
            
            // Clear previous images
            productImagesContainer.innerHTML = '';
            
            // Show loading
            productImagesContainer.innerHTML = `
                <div class="col-span-full flex justify-center items-center py-8">
                    <i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i>
                </div>
            `;
            
            // Animate modal in
            setTimeout(() => {
                imagesModalContent.classList.remove('scale-95', 'opacity-0');
                imagesModalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
            
            // Fetch images
            fetch(`/api/products/${productId}/images`)
                .then(response => response.json())
                .then(data => {
                    productImagesContainer.innerHTML = '';
                    
                    if (data.success && data.images && data.images.length > 0) {
                        noImagesMessage.classList.add('hidden');
                        productImagesContainer.classList.remove('hidden');
                        
                        data.images.forEach(image => {
                            const imageDiv = document.createElement('div');
                            imageDiv.className = 'relative group';
                            imageDiv.innerHTML = `
                                <div class="w-full h-32 border border-gray-200 rounded-lg overflow-hidden">
                                    <img src="/storage/${image.image_path}" 
                                         alt="${image.alt_text || productName}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200 cursor-pointer"
                                         onclick="openFullImageModal('/storage/${image.image_path}', '${image.alt_text || productName}')">
                                </div>
                            `;
                            productImagesContainer.appendChild(imageDiv);
                        });
                    } else {
                        noImagesMessage.classList.remove('hidden');
                        productImagesContainer.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching images:', error);
                    productImagesContainer.innerHTML = `
                        <div class="col-span-full text-center py-4">
                            <p class="text-red-500">Có lỗi xảy ra khi tải hình ảnh</p>
                        </div>
                    `;
                });
        }

        // Close images modal
        document.getElementById('closeImagesModal').addEventListener('click', function() {
            const imagesModal = document.getElementById('imagesModal');
            const imagesModalContent = document.getElementById('imagesModalContent');
            
            imagesModalContent.classList.add('scale-95', 'opacity-0');
            imagesModalContent.classList.remove('scale-100', 'opacity-100');
            
            setTimeout(() => {
                imagesModal.classList.add('hidden');
            }, 200);
        });

        // Also close on background click
        document.getElementById('imagesModal').addEventListener('click', function(e) {
            if (e.target === this) {
                document.getElementById('closeImagesModal').click();
            }
        });

        // Image viewing functions
        function openFullImageModal(src, alt) {
            document.getElementById('fullModalImage').src = src;
            document.getElementById('fullModalImage').alt = alt;
            document.getElementById('fullModalImageCaption').textContent = alt;
            document.getElementById('fullImageModal').classList.remove('hidden');
        }

        function closeFullImageModal() {
            document.getElementById('fullImageModal').classList.add('hidden');
        }

        // Close modal when clicking outside the image
        document.getElementById('fullImageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeFullImageModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFullImageModal();
            }
        });

        // Function to handle image click in product images modal
        function handleImageClick(imagePath, altText) {
            openFullImageModal('/storage/' + imagePath, altText);
        }

        // Update the product images container to handle image clicks
        function updateProductImagesContainer(images, productName) {
            const container = document.getElementById('productImagesContainer');
            container.innerHTML = '';
            
            if (images && images.length > 0) {
                images.forEach(image => {
                    const imageDiv = document.createElement('div');
                    imageDiv.className = 'relative group';
                    imageDiv.innerHTML = `
                        <div class="w-full h-32 border border-gray-200 rounded-lg overflow-hidden">
                            <img src="/storage/${image.image_path}" 
                                 alt="${image.alt_text || productName}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200 cursor-pointer"
                                 onclick="handleImageClick('${image.image_path}', '${image.alt_text || productName}')">
                        </div>
                    `;
                    container.appendChild(imageDiv);
                });
            }
        }
    </script>
</body>

</html>
