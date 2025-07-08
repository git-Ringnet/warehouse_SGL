<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý vật tư - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <style>
        #detailImage {
            cursor: zoom-in;
            transition: transform 0.3s ease;
        }

        #detailImage.zoomed {
            cursor: zoom-out;
        }

        .z-60 {
            z-index: 60;
        }

        .z-70 {
            z-index: 70;
        }
    </style>
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý vật tư</h1>
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
                            class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-lg z-30 hidden border border-gray-200">
                            <div class="p-4 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại vật tư</label>
                                    <select id="categoryFilter"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                                        <option value="">Tất cả loại</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category }}">{{ $category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Đơn vị</label>
                                    <select id="unitFilter"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                                        <option value="">Tất cả đơn vị</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit }}">{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tình trạng tồn
                                        kho</label>
                                    <select id="stockFilter"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
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
                                <button id="importDataButton"
                                    class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-file-import text-green-500 mr-2"></i> Import Data
                                </button>
                                <a href="{{ route('materials.template.download') }}"
                                    class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fa-solid fa-download text-green-500 mr-2"></i> Tải mẫu Import
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="{{ route('materials.hidden') }}"
                                    class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-eye-slash text-yellow-500 mr-2"></i> Vật tư ẩn
                                </a>
                                <a href="{{ route('materials.deleted') }}"
                                    class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-trash text-red-500 mr-2"></i> Đã xóa
                                </a>
                            </div>
                        </div>
                    </div>

                    @php
                        $user = Auth::guard('web')->user();
                        $isAdmin = $user && $user->role === 'admin';
                    @endphp

                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('materials.export')))
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
                                    <button id="exportFDFButton"
                                        class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                        <i class="fas fa-file-code text-blue-500 mr-2"></i> Xuất FDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('materials.create')))
                        <a href="{{ route('materials.create') }}">
                            <button
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                                <i class="fas fa-plus mr-2"></i> Thêm vật tư
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
            @if (session('error'))
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif
            <div class="mb-4">
                <div class="text-sm text-gray-600">
                    <span class="font-medium">{{ $materials->count() }}</span> vật tư được tìm thấy
                    <div id="filterTags" class="inline">
                        @if (request()->hasAny(['search', 'category', 'unit', 'stock']))
                            | Đang lọc
                            @if (request('search'))
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs ml-1">
                                    Từ khóa: "{{ request('search') }}"
                                </span>
                            @endif
                            @if (request('category'))
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs ml-1">
                                    Loại: {{ request('category') }}
                                </span>
                            @endif
                            @if (request('unit'))
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs ml-1">
                                    Đơn vị: {{ request('unit') }}
                                </span>
                            @endif
                            @if (request('stock'))
                                <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs ml-1">
                                    Tồn kho: {{ request('stock') === 'in_stock' ? 'Còn tồn kho' : 'Hết tồn kho' }}
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mã vật tư</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên vật tư</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Loại</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Đơn vị</th>

                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                title="Số lượng vật tư tồn kho">
                                <div class="flex items-center">
                                    Tổng tồn kho
                                    <i class="fas fa-info-circle ml-1 text-gray-400"></i>
                                </div>
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($materials as $index => $material)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $material->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $material->category }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->unit }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span
                                        class="font-medium px-2 py-1 rounded {{ $material->inventory_quantity > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}"
                                        @php
$warehouseTooltip = '';
                                        if(is_array($material->inventory_warehouses) && in_array('all', $material->inventory_warehouses)) {
                                            $warehouseTooltip = 'Tất cả các kho';
                                        } elseif(is_array($material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                                            $warehouseNames = [];
                                            foreach($material->inventory_warehouses as $warehouseId) {
                                                $warehouse = App\Models\Warehouse::find($warehouseId);
                                                if($warehouse) {
                                                    $warehouseNames[] = $warehouse->name;
                                                }
                                            }
                                            $warehouseTooltip = implode(', ', $warehouseNames);
                                        } else {
                                            $warehouseTooltip = 'Tất cả các kho';
                                        } @endphp
                                        title="Tính theo: {{ $warehouseTooltip }}">
                                        {{ number_format($material->inventory_quantity, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('materials.view_detail')))
                                        <a href="{{ route('materials.show', $material->id) }}">
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                title="Xem chi tiết">
                                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <button type="button"
                                            onclick="showImagesModal('{{ $material->id }}', '{{ $material->name }}')"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group"
                                            title="Xem hình ảnh">
                                            <i class="fas fa-images text-purple-500 group-hover:text-white"></i>
                                        </button>
                                    @endif

                                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('materials.edit')))
                                        <a href="{{ route('materials.edit', $material->id) }}">
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                title="Chỉnh sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                    @endif

                                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('materials.delete')))
                                        <button
                                            onclick="openDeleteModal('{{ $material->id }}', '{{ $material->code }}', {{ $material->inventory_quantity }})"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                            title="Xóa">
                                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-6 py-4">
                    {{ $materials->links() }}
                </div>
            </div>
    </div>
    </div>
    </main>
    </div>

    <!-- Modal xác nhận xóa -->
    <div id="deleteModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Không thể xóa</h3>
            <p class="text-red-700 mb-6">Không thể xóa vật tư <span id="materialCode" class="font-semibold"></span>
                vì còn tồn kho: <span id="inventoryQuantity" class="font-semibold"></span></p>
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

    <!-- Modal xem hình ảnh -->
    <div id="imagesModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden opacity-0 transition-opacity duration-300">
        <div
            class="bg-white rounded-lg shadow-lg p-6 max-w-4xl w-full transform scale-95 opacity-0 transition-all duration-300">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Hình ảnh vật tư: <span id="materialName"></span></h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeImagesModal()">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="materialImagesContainer" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                <!-- Images will be loaded here via JavaScript -->
            </div>
            <div class="text-center text-gray-500 italic text-sm mb-4 hidden" id="noImagesMessage">
                Vật tư này chưa có hình ảnh nào.
            </div>
            <div class="flex justify-end">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeImagesModal()">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- Modal xem ảnh chi tiết -->
    <div id="imageDetailModal"
        class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-60 hidden">
        <div class="relative max-w-screen-lg max-h-screen-lg w-full h-full flex items-center justify-center p-4">
            <!-- Close button -->
            <button type="button" onclick="closeImageDetailModal()"
                class="absolute top-4 right-4 text-white hover:text-gray-300 text-3xl z-70 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>

            <!-- Previous button -->
            <button type="button" onclick="previousImage()" id="prevImageBtn"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 text-3xl z-70 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-chevron-left"></i>
            </button>

            <!-- Next button -->
            <button type="button" onclick="nextImage()" id="nextImageBtn"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 text-3xl z-70 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-chevron-right"></i>
            </button>

            <!-- Image container -->
            <div class="relative max-w-full max-h-full flex items-center justify-center">
                <img id="detailImage" src="" alt="Chi tiết hình ảnh"
                    class="max-w-full max-h-full object-contain rounded-lg shadow-lg transition-transform duration-300"
                    onclick="toggleImageZoom()">
            </div>

            <!-- Image info -->
            <div
                class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg">
                <span id="imageCounter">1 / 1</span>
                <span class="mx-2">•</span>
                <span class="text-sm">Nhấp vào ảnh để zoom</span>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div id="importModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Import dữ liệu từ Excel</h3>
            <form id="importForm" action="{{ route('materials.import') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="import_file" class="block text-sm font-medium text-gray-700 mb-2">Chọn file
                        Excel</label>
                    <input type="file" id="import_file" name="import_file" accept=".xlsx,.xls,.csv" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Hỗ trợ: .xlsx, .xls, .csv (Tối đa 10MB)</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <div class="flex">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-1">Lưu ý quan trọng:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Tải mẫu Excel trước khi import</li>
                                <li>Mã vật tư phải duy nhất</li>
                                <li>Các trường có (*) là bắt buộc</li>
                                <li>Xóa dòng mẫu trước khi import</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeImportModal()"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Hủy
                    </button>
                    <button type="submit" id="importSubmitButton"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                        <i class="fas fa-upload mr-2"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal(materialId, materialCode, inventoryQuantity) {
            if (inventoryQuantity > 0) {
                // Show inventory warning modal
                document.getElementById('materialCode').textContent = materialCode;
                document.getElementById('inventoryQuantity').textContent = new Intl.NumberFormat('vi-VN').format(
                    inventoryQuantity);
                document.getElementById('deleteModal').classList.remove('hidden');
            } else {
                // Show confirmation modal for zero inventory
                // Set form actions for both hide and delete forms
                const deleteForm = document.getElementById('deleteForm');
                const hideForm = document.getElementById('hideForm');

                if (deleteForm) {
                    deleteForm.action = `/materials/${materialId}`;
                }
                if (hideForm) {
                    hideForm.action = `/materials/${materialId}`;
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

        // Functions for the images modal
        function showImagesModal(materialId, materialName) {
            // Set material name in modal title
            document.getElementById('materialName').textContent = materialName;

            // Show the modal with animation
            const modal = document.getElementById('imagesModal');
            const modalContent = modal.querySelector('.bg-white');

            // First, display the modal but with 0 opacity
            modal.classList.remove('hidden');
            setTimeout(() => {
                // Then animate in
                modal.style.opacity = '1';
                modalContent.style.opacity = '1';
                modalContent.style.transform = 'scale(1)';
            }, 50);

            // Reset the container and add loading spinner
            const container = document.getElementById('materialImagesContainer');
            container.innerHTML =
                '<div class="flex justify-center items-center h-40 bg-gray-100 rounded-lg animate-pulse col-span-full"><i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i></div>';

            // Hide no images message initially
            document.getElementById('noImagesMessage').classList.add('hidden');

            // Fetch the images for the material
            fetch(`/api/materials/${materialId}/images`)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API response:', data);
                    container.innerHTML = '';

                    if (data.error) {
                        container.innerHTML = '<div class="col-span-full text-center text-red-500">Lỗi: ' + data
                            .message + '</div>';
                        return;
                    }

                    if (data.images && data.images.length > 0) {
                        data.images.forEach((image, index) => {
                            const imageDiv = document.createElement('div');
                            imageDiv.className = 'relative opacity-0 transform translate-y-4';
                            imageDiv.style.transition = 'all 300ms ease';
                            imageDiv.style.transitionDelay = `${index * 50}ms`;

                            imageDiv.innerHTML = `
                                <div class="w-full h-40 border border-gray-200 rounded-lg overflow-hidden cursor-pointer hover:opacity-75 transition-opacity" onclick="openImageDetailModal(${index}, '${image.url}')">
                                    <img src="${image.url}" alt="Hình ảnh vật tư" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<div class=\\'flex items-center justify-center h-full bg-gray-100 text-gray-500\\'>Không thể tải ảnh</div>'">
                                </div>
                            `;

                            container.appendChild(imageDiv);

                            // Trigger animation after a small delay
                            setTimeout(() => {
                                imageDiv.style.opacity = '1';
                                imageDiv.style.transform = 'translateY(0)';
                            }, 10);
                        });
                    } else {
                        document.getElementById('noImagesMessage').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching material images:', error);
                    container.innerHTML =
                        '<div class="col-span-full text-center text-red-500">Có lỗi xảy ra khi tải hình ảnh: ' + error
                        .message + '</div>';
                });
        }

        function closeImagesModal() {
            const modal = document.getElementById('imagesModal');
            const modalContent = modal.querySelector('.bg-white');

            // Animate out
            modal.style.opacity = '0';
            modalContent.style.opacity = '0';
            modalContent.style.transform = 'scale(0.95)';

            // Wait for animation to finish before hiding
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Variables for image detail modal
        let currentImageIndex = 0;
        let currentImages = [];
        let isImageZoomed = false;

        // Functions for the image detail modal
        function openImageDetailModal(index, imageUrl) {
            // Store current images and index
            currentImageIndex = index;
            currentImages = [];

            // Get all images from the current modal
            const imageElements = document.querySelectorAll('#materialImagesContainer img');
            imageElements.forEach(img => {
                currentImages.push(img.src);
            });

            // Show the detail modal
            const modal = document.getElementById('imageDetailModal');
            const detailImage = document.getElementById('detailImage');

            detailImage.src = imageUrl;
            updateImageCounter();
            updateNavigationButtons();

            modal.classList.remove('hidden');

            // Reset zoom state
            isImageZoomed = false;
            detailImage.style.transform = 'scale(1)';
            detailImage.classList.remove('zoomed');
        }

        function closeImageDetailModal() {
            const modal = document.getElementById('imageDetailModal');
            modal.classList.add('hidden');

            // Reset zoom state
            isImageZoomed = false;
            const detailImage = document.getElementById('detailImage');
            detailImage.style.transform = 'scale(1)';
        }

        function previousImage() {
            if (currentImages.length <= 1) return;

            currentImageIndex = currentImageIndex > 0 ? currentImageIndex - 1 : currentImages.length - 1;

            const detailImage = document.getElementById('detailImage');
            detailImage.src = currentImages[currentImageIndex];

            updateImageCounter();
            updateNavigationButtons();

            // Reset zoom
            isImageZoomed = false;
            detailImage.style.transform = 'scale(1)';
            detailImage.classList.remove('zoomed');
        }

        function nextImage() {
            if (currentImages.length <= 1) return;

            currentImageIndex = currentImageIndex < currentImages.length - 1 ? currentImageIndex + 1 : 0;

            const detailImage = document.getElementById('detailImage');
            detailImage.src = currentImages[currentImageIndex];

            updateImageCounter();
            updateNavigationButtons();

            // Reset zoom
            isImageZoomed = false;
            detailImage.style.transform = 'scale(1)';
            detailImage.classList.remove('zoomed');
        }

        function updateImageCounter() {
            const counter = document.getElementById('imageCounter');
            counter.textContent = `${currentImageIndex + 1} / ${currentImages.length}`;
        }

        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevImageBtn');
            const nextBtn = document.getElementById('nextImageBtn');

            if (currentImages.length <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';
            }
        }

        function toggleImageZoom() {
            const detailImage = document.getElementById('detailImage');

            if (isImageZoomed) {
                detailImage.style.transform = 'scale(1)';
                detailImage.classList.remove('zoomed');
            } else {
                detailImage.style.transform = 'scale(1.5)';
                detailImage.classList.add('zoomed');
            }

            isImageZoomed = !isImageZoomed;
        }

        // Add keyboard navigation for image detail modal
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('imageDetailModal');

            if (!modal.classList.contains('hidden')) {
                switch (e.key) {
                    case 'Escape':
                        closeImageDetailModal();
                        break;
                    case 'ArrowLeft':
                        previousImage();
                        break;
                    case 'ArrowRight':
                        nextImage();
                        break;
                    case ' ':
                        e.preventDefault();
                        toggleImageZoom();
                        break;
                }
            }
        });

        // Import modal functions
        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            document.getElementById('importForm').reset();
        }

        // Search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            const categoryFilter = document.getElementById('categoryFilter');
            const unitFilter = document.getElementById('unitFilter');
            const stockFilter = document.getElementById('stockFilter');
            const filterDropdownButton = document.getElementById('filterDropdownButton');
            const filterDropdown = document.getElementById('filterDropdown');
            const clearFiltersInDropdown = document.getElementById('clearFiltersInDropdown');
            const applyFilters = document.getElementById('applyFilters');

            function performSearch() {
                const searchTerm = searchInput.value;
                const category = categoryFilter.value;
                const unit = unitFilter.value;
                const stock = stockFilter.value;

                // Show loading indicator
                showLoadingIndicator();

                // Build query parameters
                const params = new URLSearchParams();
                if (searchTerm) params.append('search', searchTerm);
                if (category) params.append('category', category);
                if (unit) params.append('unit', unit);
                if (stock) params.append('stock', stock);

                // Perform AJAX search
                fetch(`{{ route('materials.search.api') }}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        updateTable(data.materials);
                        updateResultCount(data.count);
                        updateFilterTags();
                        updateURL(params);
                        hideLoadingIndicator();
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        hideLoadingIndicator();
                        showErrorMessage('Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại.');
                    });
            }

            function showLoadingIndicator() {
                // Add loading overlay to the table
                const tableContainer = document.querySelector('.bg-white.rounded-xl.shadow-md');
                if (tableContainer && !tableContainer.querySelector('.loading-overlay')) {
                    const loadingOverlay = document.createElement('div');
                    loadingOverlay.className =
                        'loading-overlay absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10';
                    loadingOverlay.innerHTML =
                        '<div class="flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i> Đang tìm kiếm...</div>';
                    tableContainer.style.position = 'relative';
                    tableContainer.appendChild(loadingOverlay);
                }
            }

            function hideLoadingIndicator() {
                const loadingOverlay = document.querySelector('.loading-overlay');
                if (loadingOverlay) {
                    loadingOverlay.remove();
                }
            }

            function updateTable(materials) {
                const tbody = document.querySelector('table tbody');
                tbody.innerHTML = '';

                if (materials.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 italic">
                                Không tìm thấy vật tư nào
                            </td>
                        </tr>
                    `;
                    return;
                }

                materials.forEach((material, index) => {
                    const warehouseTooltip = getWarehouseTooltip(material.inventory_warehouses);
                    const stockClass = material.inventory_quantity > 0 ? 'bg-green-100 text-green-800' :
                        'bg-gray-100 text-gray-800';

                    tbody.innerHTML += `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${index + 1}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${material.code}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${material.name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${material.category}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${material.unit}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="font-medium px-2 py-1 rounded ${stockClass}" title="${warehouseTooltip}">
                                    ${formatNumber(material.inventory_quantity)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="/materials/${material.id}">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </button>
                                </a>
                                <a href="/materials/${material.id}/edit">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                        <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                    </button>
                                </a>
                                <button type="button" onclick="showImagesModal('${material.id}', '${material.name}')" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group" title="Xem hình ảnh">
                                    <i class="fas fa-images text-purple-500 group-hover:text-white"></i>
                                </button>
                                <button onclick="openDeleteModal('${material.id}', '${material.code}', ${material.inventory_quantity})" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }

            function updateResultCount(count) {
                const countElement = document.querySelector('.text-sm.text-gray-600 .font-medium');
                if (countElement) {
                    countElement.textContent = count;
                }
            }

            function updateURL(params) {
                const newUrl = params.toString() ? `${window.location.pathname}?${params.toString()}` : window
                    .location.pathname;
                window.history.pushState({}, '', newUrl);
            }

            function showErrorMessage(message) {
                // Show error message at the top of the page
                const main = document.querySelector('main');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4';
                errorDiv.textContent = message;
                main.insertBefore(errorDiv, main.firstChild);

                // Remove error message after 5 seconds
                setTimeout(() => {
                    errorDiv.remove();
                }, 5000);
            }

            function getWarehouseTooltip(inventoryWarehouses) {
                if (Array.isArray(inventoryWarehouses) && inventoryWarehouses.includes('all')) {
                    return 'Tất cả các kho';
                } else if (Array.isArray(inventoryWarehouses) && inventoryWarehouses.length > 0) {
                    return 'Kho được chọn';
                } else {
                    return 'Tất cả các kho';
                }
            }

            function formatNumber(number) {
                return new Intl.NumberFormat('vi-VN').format(number);
            }

            function updateFilterTags() {
                const filterTagsContainer = document.getElementById('filterTags');
                let tagsHTML = '';

                const searchTerm = searchInput.value;
                const category = categoryFilter.value;
                const unit = unitFilter.value;
                const stock = stockFilter.value;

                if (searchTerm || category || unit || stock) {
                    tagsHTML += ' | Đang lọc';

                    if (searchTerm) {
                        tagsHTML +=
                            ` <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs ml-1">Từ khóa: "${searchTerm}"</span>`;
                    }

                    if (category) {
                        tagsHTML +=
                            ` <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs ml-1">Loại: ${category}</span>`;
                    }

                    if (unit) {
                        tagsHTML +=
                            ` <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs ml-1">Đơn vị: ${unit}</span>`;
                    }

                    if (stock) {
                        const stockText = stock === 'in_stock' ? 'Còn tồn kho' : 'Hết tồn kho';
                        tagsHTML +=
                            ` <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs ml-1">Tồn kho: ${stockText}</span>`;
                    }
                }

                filterTagsContainer.innerHTML = tagsHTML;
            }

            function updateFilterButtonText() {
                const activeFilters = [];
                if (categoryFilter.value) activeFilters.push('Loại');
                if (unitFilter.value) activeFilters.push('Đơn vị');
                if (stockFilter.value) activeFilters.push('Tồn kho');

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
                categoryFilter.value = '';
                unitFilter.value = '';
                stockFilter.value = '';
                searchInput.value = '';
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

            // Set current filter values from URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('search')) searchInput.value = urlParams.get('search');
            if (urlParams.get('category')) categoryFilter.value = urlParams.get('category');
            if (urlParams.get('unit')) unitFilter.value = urlParams.get('unit');
            if (urlParams.get('stock')) stockFilter.value = urlParams.get('stock');

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

            // Import data button click
            const importDataButton = document.getElementById('importDataButton');
            if (importDataButton) {
                importDataButton.addEventListener('click', function() {
                    // Close the more actions dropdown first
                    moreActionsDropdown.classList.add('hidden');

                    // Open import modal
                    openImportModal();
                });
            }
        });

        // Export dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const exportDropdownButton = document.getElementById('exportDropdownButton');
            const exportDropdown = document.getElementById('exportDropdown');
            const exportExcelButton = document.getElementById('exportExcelButton');
            const exportFDFButton = document.getElementById('exportFDFButton');

            // Toggle dropdown on button click
            exportDropdownButton.addEventListener('click', function(e) {
                e.stopPropagation();
                if (exportDropdown.classList.contains('hidden')) {
                    // Show dropdown with animation
                    exportDropdown.classList.remove('hidden');
                    exportDropdown.style.opacity = '0';
                    exportDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        exportDropdown.style.transition =
                            'opacity 150ms ease-in-out, transform 150ms ease-in-out';
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

            // Function to get current filters as URL parameters
            function getCurrentFiltersParams() {
                const params = new URLSearchParams();
                const searchInput = document.getElementById('searchInput');
                const categoryFilter = document.getElementById('categoryFilter');
                const unitFilter = document.getElementById('unitFilter');
                const stockFilter = document.getElementById('stockFilter');

                if (searchInput.value) params.append('search', searchInput.value);
                if (categoryFilter.value) params.append('category', categoryFilter.value);
                if (unitFilter.value) params.append('unit', unitFilter.value);
                if (stockFilter.value) params.append('stock', stockFilter.value);

                return params.toString();
            }

            // Handle export buttons
            exportExcelButton.addEventListener('click', function() {
                const params = getCurrentFiltersParams();
                const url = '/materials/export/excel' + (params ? '?' + params : '');
                window.location.href = url;
                exportDropdown.classList.add('hidden');
            });

            exportFDFButton.addEventListener('click', function() {
                const params = getCurrentFiltersParams();
                const url = '/materials/export/fdf' + (params ? '?' + params : '');
                window.location.href = url;
                exportDropdown.classList.add('hidden');
            });
        });

        // Import form functionality
        document.addEventListener('DOMContentLoaded', function() {
            const importForm = document.getElementById('importForm');
            const importSubmitButton = document.getElementById('importSubmitButton');

            if (importForm) {
                importForm.addEventListener('submit', function() {
                    // Show loading state
                    importSubmitButton.disabled = true;
                    importSubmitButton.innerHTML =
                        '<i class="fas fa-spinner fa-spin mr-2"></i> Đang import...';
                });
            }
        });
    </script>
</body>

</html>
