<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tạo phiếu lắp ráp - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supplier-dropdown.css') }}">
    <script src="{{ asset('js/assembly-product-unit.js') }}"></script>
    <script src="{{ asset('js/date-format.js') }}"></script>
    <style>
        .product-unit-row {
            background-color: #f0f8ff !important;
            border-left: 3px solid #3b82f6;
        }

        .product-unit-row td {
            border-top: 1px solid #dbeafe;
        }

        .product-unit-row:hover {
            background-color: #e0f2fe !important;
        }

        .unit-separator {
            background-color: #e5f3ff !important;
            border-top: 2px solid #3b82f6;
            border-bottom: 2px solid #3b82f6;
        }

        .unit-separator td {
            padding: 8px 24px;
            text-align: center;
            font-weight: 600;
            color: #1e40af;
            font-size: 0.875rem;
        }

        .cursor-not-allowed {
            cursor: not-allowed !important;
        }

        .product-limit-message {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <a href="{{ route('assemblies.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Tạo phiếu lắp ráp</h1>
            </div>
        </header>

        <main class="p-6">
            <form action="{{ route('assemblies.store') }}" method="POST" id="assembly_form">
                @csrf
                <!-- Hidden container for JS to inject components/products hidden inputs -->
                <div id="hidden_form_data" style="display:none;"></div>

                @if ($errors->any())
                    <div class="mb-4 bg-red-50 p-4 rounded-lg border border-red-200">
                        <div class="text-red-600 font-medium mb-2">Có lỗi xảy ra:</div>
                        <ul class="list-disc pl-5 text-red-500">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Thông tin phiếu lắp ráp -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tools text-blue-500 mr-2"></i>
                        Thông tin phiếu lắp ráp
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="assembly_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu lắp
                                ráp</label>
                            <div class="flex space-x-2">
                                <div class="relative flex-1">
                                    <input type="text" id="assembly_code" name="assembly_code"
                                        placeholder="Đang tạo mã tự động..." readonly
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-spinner fa-spin text-blue-500" id="code-loading"></i>
                                        <i class="fas fa-check text-green-500 hidden" id="code-success"></i>
                                        <i class="fas fa-exclamation-triangle text-red-500 hidden" id="code-error"></i>
                                    </div>
                                </div>
                                <button type="button" id="regenerate-code-btn"
                                    class="px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors whitespace-nowrap"
                                    title="Tạo mã mới">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Mã phiếu được tạo tự động và không trùng lặp. Nhấn
                                <i class="fas fa-sync-alt"></i> để tạo mã mới.
                            </div>
                        </div>
                        <div>
                            <label for="assembly_date"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày lắp ráp </label>
                            <input type="text" id="assembly_date" name="assembly_date" value="{{ date('d/m/Y') }}"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 date-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1 required">Thành
                                phẩm </label>
                            <div class="relative flex space-x-2">
                                <div class="flex-1 relative">
                                    <input type="text" id="product_search" autocomplete="off"
                                           placeholder="Tìm kiếm thành phẩm..." 
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <div id="product_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        @foreach ($products as $product)
                                            <div class="product-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                                                 data-value="{{ $product->id }}" 
                                                 data-name="{{ $product->name }}"
                                                 data-code="{{ $product->code }}"
                                                 data-text="[{{ $product->code }}] {{ $product->name }}">
                                                [{{ $product->code }}] {{ $product->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" id="product_id" name="product_id">
                                </div>
                                <div class="w-24">
                                    <input type="number" id="product_add_quantity" min="1" step="1"
                                        value="1"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Số lượng">
                                </div>
                                <button type="button" id="add_product_btn"
                                    class="px-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                    Thêm
                                </button>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Tìm kiếm thành phẩm, nhập số lượng và nhấn thêm.</div>
                        </div>

                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1 required">Người
                                phụ trách </label>
                            <div class="relative">
                                <input type="text" id="assigned_to_search" autocomplete="off"
                                       placeholder="Tìm kiếm người phụ trách..." 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div id="assigned_to_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @foreach ($employees as $employee)
                                        <div class="employee-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                                             data-value="{{ $employee->id }}" 
                                             data-text="{{ $employee->name }} ({{ $employee->username }})">
                                            {{ $employee->name }} ({{ $employee->username }})
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" id="assigned_to" name="assigned_to" required>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="tester_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người
                                tiếp nhận kiểm thử </label>
                            <div class="relative">
                                <input type="text" id="tester_id_search" autocomplete="off"
                                       placeholder="Tìm kiếm người tiếp nhận kiểm thử..." 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div id="tester_id_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @foreach ($employees as $employee)
                                        <div class="employee-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                                             data-value="{{ $employee->id }}" 
                                             data-text="{{ $employee->name }} ({{ $employee->username }})">
                                            {{ $employee->name }} ({{ $employee->username }})
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" id="tester_id" name="tester_id" required>
                            </div>
                        </div>
                        <div>
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1 required">Mục
                                đích </label>
                            <select id="purpose" name="purpose" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="storage">Lưu kho</option>
                                <option value="project">Xuất đi dự án</option>
                            </select>
                        </div>

                        <div id="project_selection" class="hidden">
                            <div>
                                <label for="project_id"
                                    class="block text-sm font-medium text-gray-700 mb-1 required">Dự
                                    án </label>
                                <select id="project_id" name="project_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Chọn dự án --</option>
                                    @foreach ($projects ?? [] as $project)
                                        <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="default_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Kho để xuất xuất vật tư
                                <span class="text-xs text-gray-500 ml-1">(Tùy chọn)</span>
                            </label>
                            <select id="default_warehouse_id" name="default_warehouse_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho mặc định --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                                @endforeach
                            </select>
                            <div class="text-xs text-gray-500 mt-1">Chọn kho này để tự động điền vào tất cả các cột "Kho xuất" của vật tư. Bạn vẫn có thể thay đổi từng kho xuất riêng lẻ nếu muốn.</div>
                        </div>
                        <div>
                            <label for="apply_default_warehouse" class="block text-sm font-medium text-gray-700 mb-1">
                                Áp dụng kho mặc định
                            </label>
                            <button type="button" id="apply_default_warehouse" 
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                                <i class="fas fa-magic mr-2"></i>
                                Áp dụng cho tất cả vật tư
                            </button>
                            <div class="text-xs text-gray-500 mt-1">Nhấn để áp dụng kho mặc định cho tất cả vật tư hiện tại</div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div>
                            <label for="assembly_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi
                                chú</label>
                            <textarea id="assembly_note" name="assembly_note" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập ghi chú cho phiếu lắp ráp (nếu có)"></textarea>
                        </div>
                    </div>

                    <!-- Danh sách thành phẩm đã chọn -->
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Thành phẩm đã thêm</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên thành phẩm</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Số lượng</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Serial</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="product_list" class="bg-white divide-y divide-gray-200">
                                    <tr id="no_products_row">
                                        <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">
                                            Chưa có thành phẩm nào được thêm vào phiếu lắp ráp
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Danh sách linh kiện -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Danh sách vật tư sử dụng</h3>
                        </div>
                        <div class="mt-4 flex items-center space-x-2 mb-4">
                            <div class="flex-grow">
                                <input type="text" id="component_search"
                                    placeholder="Nhập hoặc click để xem danh sách vật tư..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <!-- Search Results Popup -->
                                <div id="search_results"
                                    class="absolute bg-white mt-1 border border-gray-300 rounded-lg shadow-lg z-10 hidden w-full max-w-2xl max-h-60 overflow-y-auto">
                                    <!-- Search results will be populated here -->
                                </div>
                            </div>
                            <div class="w-28">
                                <select id="component_product_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">--Thành phẩm--</option>
                                </select>
                            </div>
                            <div class="w-24">
                                <input type="number" id="component_add_quantity" min="1" step="1"
                                    value="1" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <button id="add_component_btn" type="button"
                                    class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none opacity-50"
                                    disabled>
                                    Thêm
                                </button>
                            </div>

                        </div>

                        <!-- Component blocks container - where product component blocks will be added -->
                        <div id="component_blocks_container">
                            <!-- Empty state -->
                            <div id="no_products_components" class="text-center py-8 text-gray-500">
                                Vui lòng thêm thành phẩm trước để xem danh sách vật tư
                            </div>
                            <!-- Product component blocks will be added here -->
                        </div>

                        <!-- Hidden form data container -->
                        <div id="hidden_form_data" class="hidden">
                            <!-- Form data will be added here by JavaScript -->
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('assemblies.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Lưu phiếu lắp ráp
                    </button>
                </div>

                <!-- Remove old hidden table -->
                <!-- <table id="component_table" class="min-w-full divide-y divide-gray-200 hidden">
                    <thead class="bg-gray-50">
                        <tr>...</tr>
                    </thead>
                    <tbody id="component_list" class="bg-white divide-y divide-gray-200">
                        <tr id="no_components_row">...</tr>
                    </tbody>
                </table> -->
            </form>
        </main>
    </div>

    <script>
        // Prevent double-submit lock
        window._assemblySubmitting = false;

        // Function hiển thị thông báo validation
        function showValidationError(message, focusElementId = null) {
            // Tạo thông báo đẹp hơn
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-md';
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Tự động ẩn sau 5 giây
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
            
            // Focus vào element nếu có
            if (focusElementId) {
                const element = document.getElementById(focusElementId);
                if (element) {
                    element.focus();
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            
            return notification;
        }

        // Global function for handling product unit changes
        function handleProductUnitChange(element) {
            const componentIndex = parseInt(element.getAttribute('data-component-index'));
            const component = window.selectedComponents[componentIndex];
            if (component) {
                component.productUnit = parseInt(element.value) || 0;

                // Update serial inputs for this component
                const row = element.closest('tr');
                if (row) {
                    const serialCell = row.querySelector('.serial-cell');
                    if (serialCell) {
                        // Fetch serials for this material with the new product unit
                        if (typeof window.fetchMaterialSerials === "function") {
                            window.fetchMaterialSerials(element);
                        }
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Generate unique assembly code on page load
            generateUniqueAssemblyCode();

            // Kiểm tra các sản phẩm đã tạo từ localStorage
            try {
                const createdProducts = JSON.parse(localStorage.getItem('createdProducts') || '{}');
                // Đánh dấu các sản phẩm đã tạo
                Object.keys(createdProducts).forEach(key => {
                    if (createdProducts[key] === true) {
                        setTimeout(() => {
                            // Parse key to get productUniqueId and unitIndex
                            if (key.includes('_unit_')) {
                                const [productUniqueId, unitIndex] = key.split('_unit_');
                                markProductAsCreated(productUniqueId, unitIndex);
                            } else {
                                // Legacy format - mark all units
                                markProductAsCreated(key);
                            }
                        }, 1000);
                    }
                });
            } catch (e) {
                console.error('Error loading created products from localStorage:', e);
            }

            const componentSearchInput = document.getElementById('component_search');
            const componentAddQuantity = document.getElementById('component_add_quantity');
            const componentProductSelect = document.getElementById('component_product_id');
            const componentProductUnitSelect = document.getElementById('component_product_unit');
            const addComponentBtn = document.getElementById('add_component_btn');
            const componentList = document.getElementById('component_list');
            const noComponentsRow = document.getElementById('no_components_row');
            const searchResults = document.getElementById('search_results');
            const warehouseSelect = document.getElementById('target_warehouse_id');
            const targetWarehouseContainer = document.getElementById('target_warehouse_container');
            const targetWarehouseIdSelect = document.getElementById('target_warehouse_id');

            // Thành phẩm UI elements
            const productSearch = document.getElementById('product_search');
            const productHidden = document.getElementById('product_id');
            const productAddQuantity = document.getElementById('product_add_quantity');
            const addProductBtn = document.getElementById('add_product_btn');
            const productList = document.getElementById('product_list');
            const noProductsRow = document.getElementById('no_products_row');

            // Khởi tạo mảng selectedComponents rỗng
            window.selectedComponents = [];
            let selectedComponents = window.selectedComponents;

            let selectedProducts = []; // Danh sách thành phẩm đã chọn
            window.selectedProducts = selectedProducts;
            let allMaterials = @json($materials);
            let selectedMaterial = null;
            let warehouseMaterials = [];
            let productCounter = 0; // Để đếm và tạo ID duy nhất cho mỗi thành phẩm
            let codeValidationTimeout = null; // Debounce timer for code validation
            // let warehouseStockData = {}; // Cache stock data for current warehouse - REMOVED

            // Assembly code validation when user types
            const assemblyCodeInput = document.getElementById('assembly_code');
            let isRegeneratingCode = false; // Flag to prevent auto-generation during manual regeneration

            assemblyCodeInput.addEventListener('input', function() {
                const code = this.value.trim();

                // Clear previous timeout
                if (codeValidationTimeout) {
                    clearTimeout(codeValidationTimeout);
                }

                // If field is empty and not currently regenerating, auto-generate new code after a delay
                if (code === '' && !isRegeneratingCode) {
                    hideCodeValidation();
                    codeValidationTimeout = setTimeout(() => {
                        if (this.value.trim() === '' && !
                            isRegeneratingCode
                        ) { // Double-check field is still empty and not regenerating
                            generateUniqueAssemblyCode();
                        }
                    }, 1000); // Wait 1 second before auto-generating
                } else if (code !== '') {
                    // Debounce validation (wait 500ms after user stops typing)
                    codeValidationTimeout = setTimeout(() => {
                        validateAssemblyCode(code);
                    }, 500);
                }
            });

            // Also validate on blur (when user leaves the field)
            assemblyCodeInput.addEventListener('blur', function() {
                const code = this.value.trim();
                if (code) {
                    validateAssemblyCode(code);
                }
            });

            // Handle regenerate code button
            const regenerateCodeBtn = document.getElementById('regenerate-code-btn');
            regenerateCodeBtn.addEventListener('click', function() {
                // Set flag to prevent auto-generation conflict
                isRegeneratingCode = true;

                // Clear any pending validation timeout
                if (codeValidationTimeout) {
                    clearTimeout(codeValidationTimeout);
                    codeValidationTimeout = null;
                }

                // Clear current code and validation state
                document.getElementById('assembly_code').value = '';
                hideCodeValidation();

                // Generate new code immediately
                generateUniqueAssemblyCode().finally(() => {
                    // Reset flag after generation is complete
                    setTimeout(() => {
                        isRegeneratingCode = false;
                    }, 100);
                });
            });

            // Component search functionality
            let componentSearchTimeout = null;
            let selectedComponentToAdd = null;

            // Component search input event listeners
            componentSearchInput.addEventListener('focus', function() {
                // Show all materials when focused
                if (allMaterials.length > 0) {
                    displayComponentSearchResults(allMaterials);
                }
            });

            // Xóa selectedMaterial khi xóa nội dung input
            componentSearchInput.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    selectedMaterial = null;
                    selectedComponentToAdd = null;

                    // Disable add button when search input is cleared
                    addComponentBtn.disabled = true;
                    addComponentBtn.classList.add('opacity-50');
                }
            });

            componentSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim();

                // Clear previous timeout
                if (componentSearchTimeout) {
                    clearTimeout(componentSearchTimeout);
                }

                if (searchTerm === '') {
                    // Show all materials when empty
                    displayComponentSearchResults(allMaterials);
                } else {
                    // Debounce search
                    componentSearchTimeout = setTimeout(() => {
                        searchComponents(searchTerm);
                    }, 300);
                }
            });

            // Hide search results when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#component_search') && !e.target.closest('#search_results')) {
                    searchResults.classList.add('hidden');
                }
            });

            // Add component button click handler
            addComponentBtn.addEventListener('click', function() {
                // Check specific conditions and show appropriate message
                if (!selectedComponentToAdd && !componentProductSelect.value) {
                    alert('Vui lòng chọn vật tư và thành phẩm!');
                    return;
                } else if (!selectedComponentToAdd) {
                    alert('Vui lòng chọn vật tư!');
                    return;
                } else if (!componentProductSelect.value) {
                    alert('Vui lòng chọn thành phẩm!');
                    return;
                }

                const productUniqueId = componentProductSelect.value;
                const selectedProduct = selectedProducts.find(p => p.uniqueId === productUniqueId);
                if (!selectedProduct) {
                    alert('Không tìm thấy thành phẩm được chọn!');
                    return;
                }

                const quantity = parseInt(componentAddQuantity.value) || 1;

                // Check if component already exists for this product (any unit)
                const existingComponents = selectedComponents.filter(c =>
                    c.id === selectedComponentToAdd.id && c.productId === productUniqueId
                );

                if (existingComponents.length > 0) {
                    alert('Vật tư này đã được thêm cho thành phẩm này!');
                    return;
                }


                // Add new component for all product units
                const productQty = parseInt(selectedProduct.quantity) || 1;

                for (let unitIndex = 0; unitIndex < productQty; unitIndex++) {
                    const newComponent = {
                        id: selectedComponentToAdd.id,
                        code: selectedComponentToAdd.code,
                        name: selectedComponentToAdd.name,
                        category: selectedComponentToAdd.category,
                        unit: selectedComponentToAdd.unit,
                        quantity: quantity,
                        originalQuantity: quantity, // Store the original quantity for this component
                        notes: '',
                        serial: '',
                        serials: [],
                        productId: productUniqueId,
                        actualProductId: selectedProduct.id, // Store actual product ID for backend
                        isFromProduct: false, // User added component
                        isOriginal: false, // Not from original BOM
                        productUnit: unitIndex,
                        unitLabel: unitIndex > 0 ? `Đơn vị ${unitIndex + 1}` : ''
                    };

                    selectedComponents.push(newComponent);
                }
                updateProductComponentList(productUniqueId);
                checkAndShowCreateNewProductButton(productUniqueId);

                // Fetch stock data if warehouse is selected - REMOVED
                // if (warehouseSelect.value) {
                //     fetchWarehouseStockData();
                // }

                // Reset form
                componentSearchInput.value = '';
                componentAddQuantity.value = '1';
                selectedComponentToAdd = null;
                searchResults.classList.add('hidden');
            });

            // Purpose selection handler
            const purposeSelect = document.getElementById('purpose');
            const projectSelection = document.getElementById('project_selection');
            const projectIdSelect = document.getElementById('project_id');

            purposeSelect.addEventListener('change', function() {
                if (this.value === 'project') {
                    // Khi xuất đi dự án: hiện chọn dự án, ẩn kho nhập
                    projectSelection.classList.remove('hidden');
                    projectIdSelect.setAttribute('required', 'required');
                    targetWarehouseContainer.classList.add('hidden');
                    targetWarehouseIdSelect.removeAttribute('required');
                } else {
                    // Khi lưu kho: ẩn chọn dự án, hiện kho nhập
                    projectSelection.classList.add('hidden');
                    projectIdSelect.removeAttribute('required');
                }
            });

            // Trigger the change event to set initial state
            purposeSelect.dispatchEvent(new Event('change'));

            // Validate component quantity input
            componentAddQuantity.addEventListener('input', function() {
                const value = this.value.trim();
                const numValue = parseInt(value);

                // Check if empty or invalid
                if (value === '' || isNaN(numValue) || numValue < 1) {
                    this.setCustomValidity('Số lượng phải là số nguyên dương (≥ 1)');
                    this.classList.add('border-red-500');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('border-red-500');
                }
            });

            componentAddQuantity.addEventListener('blur', function() {
                const value = this.value.trim();
                if (value === '' || parseInt(value) < 1 || isNaN(parseInt(value))) {
                    this.value = '1';
                    this.setCustomValidity('');
                    this.classList.remove('border-red-500');
                }
            });

            // Validate product quantity input
            productAddQuantity.addEventListener('input', function() {
                const value = this.value.trim();
                const numValue = parseInt(value);

                // Check if empty or invalid
                if (value === '' || isNaN(numValue) || numValue < 1) {
                    this.setCustomValidity('Số lượng phải là số nguyên dương (≥ 1)');
                    this.classList.add('border-red-500');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('border-red-500');
                }
            });

            productAddQuantity.addEventListener('blur', function() {
                const value = this.value.trim();
                if (value === '' || parseInt(value) < 1 || isNaN(parseInt(value))) {
                    this.value = '1';
                    this.setCustomValidity('');
                    this.classList.remove('border-red-500');
                }
            });

            // Xử lý thêm thành phẩm
            addProductBtn.addEventListener('click', function() {
                const productId = productHidden.value;
                const productName = productSearch.value;
                const quantity = parseInt(productAddQuantity.value) || 1;

                if (!productId) {
                    alert('Vui lòng chọn thành phẩm!');
                    return;
                }

                // Kiểm tra xem thành phẩm đã được thêm chưa
                const existingProduct = selectedProducts.find(product => product.id === productId);
                if (existingProduct) {
                    alert('Thành phẩm "' + productName + '" đã được thêm vào danh sách!');
                    return;
                }

                // Kiểm tra số lượng thành phẩm đã thêm (chỉ cho phép 1 thành phẩm)
                // if (selectedProducts.length >= 1) {
                //     alert('Chỉ có thể thêm 1 thành phẩm cho mỗi phiếu lắp ráp!');
                //     return;
                // }

                productCounter++;

                // Thêm vào danh sách thành phẩm đã chọn
                const newProduct = {
                    id: productId,
                    uniqueId: 'product_' + productCounter,
                    name: productName,
                    quantity: quantity,
                    originalQuantity: quantity, // Store original quantity for comparison
                    serials: Array(quantity).fill('')
                };

                selectedProducts.push(newProduct);
                updateProductList();

                // Tạo block danh sách linh kiện cho sản phẩm này
                createProductComponentBlock(newProduct);

                // Update component product dropdown
                updateComponentProductDropdown();

                // Disable nút thêm thành phẩm sau khi đã thêm
                updateAddProductButtonState();

                // Reset form
                productSearch.value = '';
                productHidden.value = '';
                productAddQuantity.value = '1';

                // Sau khi tạo thành phẩm, tự động cuộn đến block linh kiện của nó
                const componentBlock = document.getElementById('component_block_' + newProduct.uniqueId);
                if (componentBlock) {
                    setTimeout(() => {
                        componentBlock.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }, 200);
                }

                // Tự động fetch và thêm linh kiện của thành phẩm
                fetchProductComponents(productId, newProduct.uniqueId);
            });

            // Tạo block danh sách linh kiện cho một thành phẩm
            function createProductComponentBlock(product) {
                // Kiểm tra nếu block đã tồn tại
                const existingBlock = document.getElementById('component_block_' + product.uniqueId);
                if (existingBlock) {
                    return existingBlock; // Nếu đã tồn tại, trả về block hiện có
                }
                // Ẩn thông báo "không có thành phẩm"
                document.getElementById('no_products_components').style.display = 'none';

                // Tạo block mới
                const componentBlock = document.createElement('div');
                componentBlock.id = 'component_block_' + product.uniqueId;
                componentBlock.className = 'mb-6 border border-gray-200 rounded-lg overflow-hidden';

                // Tạo header cho block
                const header = document.createElement('div');
                header.className = 'bg-blue-50 border-b border-gray-200 p-3 flex justify-between items-center';
                header.innerHTML =
                    '<h4 class="font-medium text-blue-700">' +
                    '<i class="fas fa-box-open mr-2"></i>' +
                    'Linh kiện cho thành phẩm: ' + product.name +
                    '</h4>';

                // Thêm sự kiện cuộn lên đầu trang
                setTimeout(() => {
                    const scrollBtn = document.getElementById('scroll_to_' + product.uniqueId);
                    if (scrollBtn) {
                        scrollBtn.addEventListener('click', () => {
                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        });
                    }
                }, 100);

                // Tạo container cho các bảng linh kiện
                const tableContainer = document.createElement('div');
                tableContainer.className = 'overflow-x-auto';
                tableContainer.id = 'component_list_' + product.uniqueId;

                // Tạo row "no components" cho trường hợp không có linh kiện
                const noComponentsRow = document.createElement('tr');
                noComponentsRow.id = 'no_components_row_' + product.uniqueId;
                noComponentsRow.innerHTML =
                    '<td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Chưa có linh kiện nào cho thành phẩm này</td>';

                // Thêm vào container
                tableContainer.appendChild(noComponentsRow);

                // Thêm vào block
                componentBlock.appendChild(header);
                componentBlock.appendChild(tableContainer);

                // Thêm vào container
                document.getElementById('component_blocks_container').appendChild(componentBlock);

                // Trả về block mới tạo
                return componentBlock;
            }

            // Function to generate unique assembly code
            async function generateUniqueAssemblyCode() {
                // Show loading state
                showCodeLoading();

                // Make input readonly while generating
                const input = document.getElementById('assembly_code');
                input.setAttribute('readonly', true);
                input.classList.add('bg-gray-50');
                input.placeholder = 'Đang tạo mã tự động...';

                try {
                    const response = await fetch('{{ route('assemblies.generate-code') }}');
                    const data = await response.json();

                    // Validate that the generated code won't conflict with multi-product suffixes
                    const baseCode = data.code;
                    const isCodeAvailable = await validateBaseCodeAvailability(baseCode);

                    if (isCodeAvailable) {
                        input.value = baseCode;
                        showCodeSuccess('Mã phiếu hợp lệ');
                    } else {
                        // If base code conflicts, try to generate a new one
                        const alternativeCode = await generateAlternativeCode(baseCode);
                        input.value = alternativeCode;
                        showCodeSuccess('Mã phiếu hợp lệ (đã điều chỉnh)');
                    }

                    // Enable input for manual editing
                    input.removeAttribute('readonly');
                    input.classList.remove('bg-gray-50');
                    input.placeholder = '';

                    return input.value; // Return the final code
                } catch (error) {
                    console.error('Error generating assembly code:', error);

                    // Show error and allow manual input
                    showCodeError('Lỗi tạo mã tự động. Vui lòng nhập thủ công.');
                    input.placeholder = 'Nhập mã phiếu lắp ráp';
                    input.removeAttribute('readonly');
                    input.classList.remove('bg-gray-50');

                    throw error; // Re-throw error for promise handling
                }
            }

            // Function to validate assembly code when user types manually
            async function validateAssemblyCode(code) {
                if (!code || code.trim() === '') {
                    hideCodeValidation();
                    return;
                }

                // Show loading
                showCodeLoading();

                try {
                    // Check both the base code and potential multi-product suffixes
                    const isAvailable = await validateBaseCodeAvailability(code);

                    if (isAvailable) {
                        showCodeSuccess('Mã phiếu hợp lệ');
                    } else {
                        showCodeError('Mã phiếu đã tồn tại hoặc có thể gây xung đột! Vui lòng chọn mã khác.');
                    }
                } catch (error) {
                    console.error('Error validating assembly code:', error);
                    showCodeError('Lỗi kiểm tra mã phiếu');
                }
            }

            // Function to validate base code availability (checks for conflicts with multi-product suffixes)
            async function validateBaseCodeAvailability(baseCode) {
                try {
                    // Check the base code itself
                    const baseResponse = await fetch('{{ route('assemblies.check-code') }}?code=' +
                        encodeURIComponent(baseCode));
                    const baseData = await baseResponse.json();

                    if (baseData.exists) {
                        return false;
                    }

                    // Check potential multi-product suffixes (-P1, -P2, -P3, etc.)
                    for (let i = 1; i <= 5; i++) { // Check up to 5 products
                        const suffixCode = baseCode + '-P' + i;
                        const suffixResponse = await fetch('{{ route('assemblies.check-code') }}?code=' +
                            encodeURIComponent(suffixCode));
                        const suffixData = await suffixResponse.json();

                        if (suffixData.exists) {
                            return false; // Conflict found
                        }
                    }

                    return true; // No conflicts found
                } catch (error) {
                    console.error('Error checking code availability:', error);
                    return false; // Assume not available on error
                }
            }

            // Function to generate alternative code when conflicts are found
            async function generateAlternativeCode(originalCode) {
                // Extract prefix and date from original code (e.g., ASM250613001 -> ASM250613)
                const match = originalCode.match(/^(ASM\d{6})(\d{3})$/);
                if (!match) {
                    // If pattern doesn't match, just append a number
                    return originalCode + '001';
                }

                const prefix = match[1]; // ASM250613
                let sequence = parseInt(match[2]); // 001

                // Try incrementing sequence until we find an available code
                for (let attempts = 0; attempts < 100; attempts++) {
                    sequence++;
                    const newCode = prefix + sequence.toString().padStart(3, '0');

                    const isAvailable = await validateBaseCodeAvailability(newCode);
                    if (isAvailable) {
                        return newCode;
                    }
                }

                // If we can't find an alternative, return original with timestamp
                return originalCode + '-' + Date.now().toString().slice(-6);
            }

            // Helper functions for code validation UI
            function showCodeLoading() {
                document.getElementById('code-loading').classList.remove('hidden');
                document.getElementById('code-success').classList.add('hidden');
                hideCodeError();
            }

            function showCodeSuccess(message) {
                document.getElementById('code-loading').classList.add('hidden');
                document.getElementById('code-success').classList.remove('hidden');
                hideCodeError();
            }

            function showCodeError(message) {
                document.getElementById('code-loading').classList.add('hidden');
                document.getElementById('code-success').classList.add('hidden');
                document.getElementById('code-error').classList.remove('hidden');

                // Remove existing error message
                const existingError = document.querySelector('.assembly-code-error');
                if (existingError) {
                    existingError.remove();
                }

                // Add error message below the input flex container
                const codeContainer = document.getElementById('assembly_code').parentElement.parentElement
                    .parentElement;
                const errorDiv = document.createElement('div');
                errorDiv.className = 'assembly-code-error text-red-600 text-sm mt-1 font-medium';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>' + message;
                codeContainer.appendChild(errorDiv);

                // Add red border to input
                document.getElementById('assembly_code').classList.add('border-red-500');
            }

            function hideCodeError() {
                const existingError = document.querySelector('.assembly-code-error');
                if (existingError) {
                    existingError.remove();
                }
                document.getElementById('assembly_code').classList.remove('border-red-500');
                document.getElementById('code-error').classList.add('hidden');
            }

            function hideCodeValidation() {
                document.getElementById('code-loading').classList.add('hidden');
                document.getElementById('code-success').classList.add('hidden');
                document.getElementById('code-error').classList.add('hidden');
                hideCodeError();
            }

            // Function to fetch and add components for a product - REAL API CALL
            function fetchProductComponents(productId, productUniqueId) {
                // Show loading indicator inside the product component block
                const componentBlock = document.getElementById('component_block_' + productUniqueId);
                if (!componentBlock) return;

                const loadingMessage = document.createElement('div');
                loadingMessage.className = 'text-blue-500 text-sm p-3 text-center';
                loadingMessage.id = 'loading-components-' + productUniqueId;
                loadingMessage.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> Đang tải linh kiện cho thành phẩm...';
                componentBlock.appendChild(loadingMessage);

                // REAL API CALL to get product materials
                fetch(`{{ url('/assemblies/product-materials') }}/${productId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Remove loading indicator
                        const loadingElement = document.getElementById('loading-components-' + productUniqueId);
                        if (loadingElement) loadingElement.remove();

                        if (data.success) {
                            const components = data.materials;

                            if (components.length === 0) {
                                // Show info message if no components
                                const infoMessage = document.createElement('div');
                                infoMessage.className = 'text-amber-500 text-sm p-3 text-center';
                                infoMessage.innerHTML =
                                    '<i class="fas fa-info-circle"></i> Thành phẩm này không có linh kiện định sẵn trong hệ thống.';
                                infoMessage.id = 'info-components-' + productUniqueId;
                                componentBlock.appendChild(infoMessage);

                                // Remove message after 5 seconds
                                setTimeout(() => {
                                    const element = document.getElementById('info-components-' +
                                        productUniqueId);
                                    if (element) element.remove();
                                }, 5000);
                            } else {
                                // Store original components for change detection
                                const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                                if (product) {
                                    product.originalComponents = JSON.parse(JSON.stringify(
                                        components)); // Deep copy
                                }

                                // Add components to the selected components list - create for each product unit
                                const productQty = parseInt(product.quantity) || 1;

                                for (let unitIndex = 0; unitIndex < productQty; unitIndex++) {
                                    components.forEach(material => {
                                        const componentData = {
                                            id: material.id,
                                            code: material.code,
                                            name: material.name,
                                            category: material.category,
                                            unit: material.unit,
                                            quantity: material.quantity || 1,
                                            originalQuantity: material.quantity || 1,
                                            notes: material.notes || '',
                                            serial: '',
                                            serials: [],
                                            productId: productUniqueId,
                                            actualProductId: product.id,
                                            productRealId: product.id,
                                            isFromProduct: true,
                                            isOriginal: true,
                                            warehouseId: getWarehouseId() || '',
                                            productUnit: unitIndex,
                                            unitLabel: unitIndex > 0 ? `Đơn vị ${unitIndex + 1}` :
                                                ''
                                        };

                                        selectedComponents.push(componentData);
                                    });
                                }

                                // Update the component list for this product
                                updateProductComponentList(productUniqueId);

                                // Check for changes and show/hide create new product button
                                checkAndShowCreateNewProductButton(productUniqueId);

                                // Success message
                                const successMessage = document.createElement('div');
                                successMessage.className = 'text-green-500 text-sm p-3 text-center';
                                successMessage.innerHTML =
                                    `<i class="fas fa-check-circle"></i> Đã tải ${components.length} linh kiện từ công thức sản xuất`;
                                successMessage.id = 'success-components-' + productUniqueId;
                                componentBlock.appendChild(successMessage);

                                // Remove success message after 3 seconds
                                setTimeout(() => {
                                    const element = document.getElementById('success-components-' +
                                        productUniqueId);
                                    if (element) element.remove();
                                }, 3000);
                            }
                        } else {
                            throw new Error(data.message || 'Lỗi khi tải linh kiện');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching product components:', error);

                        // Remove loading indicator
                        const loadingElement = document.getElementById('loading-components-' + productUniqueId);
                        if (loadingElement) loadingElement.remove();

                        // Show error message
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'text-red-500 text-sm p-3 text-center';
                        errorMessage.innerHTML =
                            '<i class="fas fa-exclamation-triangle"></i> Lỗi khi tải linh kiện: ' + error
                            .message;
                        errorMessage.id = 'error-components-' + productUniqueId;
                        componentBlock.appendChild(errorMessage);

                        // Remove error message after 5 seconds
                        setTimeout(() => {
                            const element = document.getElementById('error-components-' +
                                productUniqueId);
                            if (element) element.remove();
                        }, 5000);
                    });
            }

            // Function to update component list for a specific product
            function updateProductComponentList(productUniqueId) {
                const componentListElement = document.getElementById('component_list_' + productUniqueId);
                const noComponentsRow = document.getElementById('no_components_row_' + productUniqueId);

                if (!componentListElement) return;

                // Clear existing content completely - remove ALL children
                while (componentListElement.firstChild) {
                    componentListElement.removeChild(componentListElement.firstChild);
                }

                // Get components for this product
                const productComponents = selectedComponents.filter(comp => comp.productId === productUniqueId);

                if (productComponents.length === 0) {
                    // Re-add no components row
                    const noComponentsRowNew = document.createElement('tr');
                    noComponentsRowNew.id = 'no_components_row_' + productUniqueId;
                    noComponentsRowNew.innerHTML =
                        '<td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Chưa có linh kiện nào cho thành phẩm này</td>';
                    componentListElement.appendChild(noComponentsRowNew);
                    return;
                }

                // Get the product to know its quantity
                const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                const productQty = parseInt(product?.quantity) || 1;

                // Create separate tables for each product unit
                for (let unitIndex = 0; unitIndex < productQty; unitIndex++) {
                    // Create unit header
                    const unitHeader = document.createElement('div');
                    unitHeader.className = 'bg-blue-100 border-b border-blue-200 p-3 mb-2';
                    unitHeader.innerHTML = `
                        <h5 class="font-semibold text-blue-800 flex items-center">
                            <i class="fas fa-box mr-2"></i>
                            Đơn vị thành phẩm ${unitIndex + 1}
                        </h5>
                    `;
                    componentListElement.appendChild(unitHeader);

                    // Create table for this unit
                    const unitTable = document.createElement('table');
                    unitTable.className = 'min-w-full divide-y divide-gray-200 mb-4';
                    unitTable.innerHTML = `
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại vật tư</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kho xuất</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ghi chú</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="unit_table_${productUniqueId}_${unitIndex}">
                        </tbody>
                    `;
                    componentListElement.appendChild(unitTable);

                    // Get components for this specific unit
                    const unitComponents = productComponents.filter(c => c.productUnit === unitIndex);

                    // Add components for this unit
                    const unitTableBody = document.getElementById(`unit_table_${productUniqueId}_${unitIndex}`);

                    // Use global index for all components across all units
                    let globalIndex = 0;
                    for (let u = 0; u < unitIndex; u++) {
                        const prevUnitComponents = productComponents.filter(c => c.productUnit === u);
                        globalIndex += prevUnitComponents.length;
                    }

                    unitComponents.forEach((component, localIndex) => {
                        const currentGlobalIndex = globalIndex + localIndex;
                        const row = document.createElement('tr');
                        row.className = 'component-row';
                        row.setAttribute('data-unit-index', unitIndex);

                        // Mark modified components by comparing with original formula (only for first unit)
                        let isModified = false;
                        if (unitIndex === 0 && product && product.originalComponents) {
                            const originalComponent = product.originalComponents.find(o => o.id ===
                                component.id);
                            if (!originalComponent) {
                                isModified = true;
                            } else if (originalComponent.quantity !== component.quantity) {
                                isModified = true;
                            }
                        } else if (unitIndex > 0) {
                            // For other units, check if it's different from the first unit
                            const firstUnitComponent = productComponents.find(c => c.id === component.id &&
                                c.productUnit === 0);
                            if (firstUnitComponent && firstUnitComponent.quantity !== component.quantity) {
                                isModified = true;
                            }
                        }

                        const modifiedClass = isModified ? 'bg-yellow-50' : '';

                        // Create empty row with cells
                        for (let i = 0; i < 8; i++) {
                            const cell = document.createElement('td');
                            cell.className = `px-6 py-4 whitespace-nowrap text-sm ${modifiedClass}`;
                            row.appendChild(cell);
                        }

                        // Fill in the cells
                        row.cells[0].className += ' text-gray-900';
                        row.cells[0].innerHTML = `
                            <input type="hidden" name="components[${currentGlobalIndex}][id]" value="${component.id}">
                                <input type="hidden" name="components[${currentGlobalIndex}][product_unit]" value="${unitIndex}">
                            ${component.code}
                            ${isModified ? '<i class="fas fa-edit text-yellow-500 ml-1" title="Đã sửa đổi"></i>' : ''}
                    `;

                        row.cells[1].className += ' text-gray-700';
                        row.cells[1].textContent = component.category;

                        row.cells[2].className += ' text-gray-900';
                        row.cells[2].textContent = component.name;

                        row.cells[3].className += ' text-gray-700';
                        row.cells[3].innerHTML = `
                            <input type="number" min="1" step="1" name="components[${currentGlobalIndex}][quantity]" 
                                   value="${component.quantity}" 
                                   data-component-id="${component.id}" 
                                       data-product-id="${productUniqueId}" 
                                       data-unit-index="${unitIndex}" required
                                   class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 component-quantity-input">
                    `;

                        // Warehouse cell (4) - will be filled by addWarehouseSelectToCell
                        row.cells[4].className += ' text-gray-700 warehouse-cell';

                        // Serial cell (5) - will be filled by addSerialInputsToCell
                        row.cells[5].className += ' text-gray-700 serial-cell';

                        // Note cell (6)
                        row.cells[6].className += ' text-gray-700';
                        row.cells[6].innerHTML = `
                            <input type="text" name="components[${currentGlobalIndex}][note]" 
                                   value="${component.notes}" 
                                   placeholder="Ghi chú (tùy chọn)"
                                   class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    `;

                        // Action cell (7)
                        row.cells[7].className += ' text-gray-700';
                        row.cells[7].innerHTML = `
                            <button type="button" class="text-red-500 hover:text-red-700 delete-component" 
                                        data-product-id="${productUniqueId}" data-component-id="${component.id}" data-unit-index="${unitIndex}">
                                <i class="fas fa-trash"></i>
                            </button>
                    `;

                        // Add row to unit table
                        unitTableBody.appendChild(row);

                        // Add warehouse select to cell 4
                        const warehouseCell = row.cells[4];
                        addWarehouseSelectToCell(warehouseCell, component, currentGlobalIndex);

                        // Add serial inputs to cell 5
                        const serialCell = row.cells[5];
                        addSerialInputsToCell(serialCell, component, currentGlobalIndex);
                    });
                }

                // Add event listeners for quantity changes in all unit tables
                const quantityInputs = componentListElement.querySelectorAll('.component-quantity-input');
                quantityInputs.forEach(input => {
                    // Validate on input
                    input.addEventListener('input', function() {
                        const value = this.value.trim();
                        const numValue = parseInt(value);

                        // Check if empty or invalid
                        if (value === '' || isNaN(numValue) || numValue < 1) {
                            this.setCustomValidity('Số lượng phải là số nguyên dương (≥ 1)');
                            this.classList.add('border-red-500');
                        } else {
                            this.setCustomValidity('');
                            this.classList.remove('border-red-500');
                        }
                    });

                    input.addEventListener('change', function() {
                        const componentId = this.getAttribute('data-component-id');
                        const productId = this.getAttribute('data-product-id');
                        const value = this.value.trim();
                        let newQuantity = parseInt(value);

                        // Validate and correct value
                        if (value === '' || isNaN(newQuantity) || newQuantity < 1) {
                            newQuantity = 1;
                            this.value = '1';
                        }

                        // Clear validation styling
                        this.setCustomValidity('');
                        this.classList.remove('border-red-500');

                        // Update component quantity in selectedComponents
                        const unitIndex = this.getAttribute('data-unit-index');
                        const component = selectedComponents.find(c =>
                            c.id == componentId && c.productId === productId && c
                            .productUnit === parseInt(unitIndex)
                        );
                        if (component) {
                            const oldQuantity = component.quantity;
                            component.quantity = newQuantity;

                            // Mark component as manually adjusted to prevent auto-recalculation
                            component.manuallyAdjusted = true;

                            // Update serial inputs if quantity changed
                            if (oldQuantity !== newQuantity) {
                                // Adjust serials array length
                                if (newQuantity === 1) {
                                    // If quantity becomes 1, use first serial from array as single serial
                                    component.serial = (component.serials && component.serials[
                                        0]) || '';
                                    component.serials = [];
                                } else if (newQuantity > 1) {
                                    // If quantity > 1, ensure serials array has correct length
                                    if (!component.serials) component.serials = [];

                                    // If switching from single to multiple, move serial to first array position
                                    if (oldQuantity === 1 && component.serial) {
                                        component.serials[0] = component.serial;
                                        component.serial = '';
                                    }

                                    // Adjust array length
                                    if (newQuantity > component.serials.length) {
                                        // Add empty serials if quantity increased
                                        const additionalSerials = Array(newQuantity - component
                                            .serials.length).fill('');
                                        component.serials = [...component.serials, ...
                                            additionalSerials
                                        ];
                                    } else if (newQuantity < component.serials.length) {
                                        // Trim array if quantity decreased
                                        component.serials = component.serials.slice(0, newQuantity);
                                    }
                                }

                                // Update serial inputs in the current row
                                const row = this.closest('tr');
                                if (row) {
                                    const serialCell = row.querySelector('.serial-cell');
                                    if (serialCell) {
                                        const index = this.name.match(/\[(\d+)\]/)[1];
                                        addSerialInputsToCell(serialCell, component, index);
                                    }
                                }
                            } else {
                                console.log('Component not found for quantity update:', {
                                    componentId,
                                    productId,
                                    unitIndex,
                                    availableComponents: selectedComponents.filter(c => c
                                        .id == componentId && c.productId === productId)
                                });
                            }
                        }
                    });
                });
            }

            // Function to search components
            function searchComponents(searchTerm) {
                if (!searchTerm) {
                    displayComponentSearchResults(allMaterials);
                    return;
                }

                const filteredMaterials = allMaterials.filter(material => {
                    return material.code.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        material.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        material.category.toLowerCase().includes(searchTerm.toLowerCase());
                });

                displayComponentSearchResults(filteredMaterials);
            }

            // Function to display component search results
            function displayComponentSearchResults(materials) {
                if (materials.length === 0) {
                    searchResults.innerHTML = '<div class="p-2 text-gray-500">Không tìm thấy vật tư nào</div>';
                    searchResults.classList.remove('hidden');
                    return;
                }

                let html = '';
                materials.slice(0, 10).forEach(material => { // Limit to 10 results
                    html += `
                        <div class="p-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 component-search-item" 
                             data-id="${material.id}" 
                             data-code="${material.code}" 
                             data-name="${material.name}"
                             data-category="${material.category || 'Không xác định'}"
                             data-unit="${material.unit || ''}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-medium text-sm">[${material.code}] ${material.name}</div>
                                    <div class="text-xs text-gray-500">${material.category || 'Không xác định'} ${material.unit ? '- ' + material.unit : ''}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                searchResults.innerHTML = html;
                searchResults.classList.remove('hidden');

                // Add click handlers for search result items
                searchResults.querySelectorAll('.component-search-item').forEach(item => {
                    item.addEventListener('click', function() {
                        // Create material object with all required properties
                        selectedComponentToAdd = {
                            id: parseInt(this.getAttribute('data-id')),
                            code: this.getAttribute('data-code'),
                            name: this.getAttribute('data-name'),
                            category: this.getAttribute('data-category') || 'Không xác định',
                            unit: this.getAttribute('data-unit') || ''
                        };

                        // Set both variables for consistency
                        selectedMaterial = selectedComponentToAdd;

                        componentSearchInput.value =
                            `[${selectedComponentToAdd.code}] ${selectedComponentToAdd.name}`;
                        searchResults.classList.add('hidden');

                        // Enable add button if product is selected
                        if (componentProductSelect.value) {
                            addComponentBtn.disabled = false;
                            addComponentBtn.classList.remove('opacity-50');
                        }
                    });
                });
            }

            // Add event listener for delete component buttons and product unit selection
            document.addEventListener('click', function(e) {
                if (e.target.closest('.delete-component')) {
                    e.preventDefault();
                    const btn = e.target.closest('.delete-component');
                    const productId = btn.getAttribute('data-product-id');
                    const componentId = btn.getAttribute('data-component-id');
                    const unitIndex = btn.getAttribute('data-unit-index');

                    // Check if this component is from an existing product
                    const component = selectedComponents.find(c => 
                        c.id == componentId && c.productId === productId && c.productUnit == unitIndex
                    );

                    if (component && component.isFromExistingProduct) {
                        // Prevent deletion of components from existing products
                        Swal.fire({
                            icon: 'warning',
                            title: 'Không thể xóa',
                            text: 'Không thể xóa vật tư từ thành phẩm đã tồn tại',
                            confirmButtonText: 'Đóng'
                        });
                        return;
                    }

                    // Remove specific component unit from selectedComponents
                    const beforeCount = selectedComponents.length;
                    selectedComponents = selectedComponents.filter(c =>
                        !(c.id == componentId && c.productId === productId && c.productUnit ==
                            unitIndex)
                    );
                    const afterCount = selectedComponents.length;
                    
                    console.log(`Removed component: ${componentId} from product ${productId} unit ${unitIndex}`);
                    console.log(`Components count: ${beforeCount} -> ${afterCount}`);

                    // Update UI and check for changes
                    updateProductComponentList(productId);
                    checkAndShowCreateNewProductButton(productId);
                }
            });

            // Add event listener for product unit selection
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('product-unit-select')) {
                    const selectedUnit = e.target.value;
                    const productId = e.target.getAttribute('data-product-id');

                    // Update all components for this product with the selected unit
                    const components = selectedComponents.filter(c => c.productId === productId);
                    components.forEach(component => {
                        component.productUnit = selectedUnit;

                        // Find and update the component's row
                        const componentRows = document.querySelectorAll('tr.component-row');
                        componentRows.forEach(row => {
                            const hiddenProductId = row.querySelector(
                                'input[name*="[product_id]"]');
                            if (hiddenProductId && hiddenProductId.value === productId) {
                                // Update unit display
                                const unitCell = row.querySelector('.product-unit-cell');
                                if (unitCell) {
                                    unitCell.textContent =
                                        `Đơn vị ${parseInt(selectedUnit) + 1}`;
                                }

                                // Clear and reload serial dropdowns
                                const serialCell = row.querySelector('.serial-cell');
                                if (serialCell) {
                                    const serialSelects = serialCell.querySelectorAll(
                                        'select');
                                    serialSelects.forEach(select => {
                                        select.value = ''; // Reset selection
                                        select.setAttribute('data-product-unit',
                                            selectedUnit);
                                        // Trigger serial reload
                                        const event = new Event('click');
                                        select.dispatchEvent(event);
                                    });
                                }
                            }
                        });
                    });
                }
            });

            // Hàm cập nhật trạng thái nút thêm thành phẩm
            function updateAddProductButtonState() {
                // if (selectedProducts.length >= 1) {
                //     // Disable nút thêm và các trường nhập liệu
                //     addProductBtn.disabled = true;
                //     addProductBtn.classList.add('opacity-50', 'cursor-not-allowed');
                //     productSelect.disabled = true;
                //     productAddQuantity.disabled = true;
                // } else {
                //     // Enable nút thêm và các trường nhập liệu
                //     addProductBtn.disabled = false;
                //     addProductBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                //     productSelect.disabled = false;
                //     productAddQuantity.disabled = false;
                    
                //     // Xóa thông báo
                //     const existingMessage = document.querySelector('.product-limit-message');
                //     if (existingMessage) {
                //         existingMessage.remove();
                //     }
                // }
            }

            // Hàm cập nhật danh sách thành phẩm
            function updateProductList() {
                // Xóa các hàng thành phẩm hiện tại (trừ hàng thông báo)
                const productRows = document.querySelectorAll('.product-row');
                productRows.forEach(row => row.remove());

                // Ẩn/hiển thị thông báo "không có thành phẩm"
                if (selectedProducts.length > 0) {
                    noProductsRow.style.display = 'none';
                } else {
                    noProductsRow.style.display = '';
                    return; // Thoát sớm khi không có thành phẩm
                }

                // Thêm hàng cho mỗi thành phẩm đã chọn
                selectedProducts.forEach((product, index) => {
                    const row = document.createElement('tr');
                    row.className = 'product-row';
                    row.setAttribute('data-product-id', product.uniqueId);

                    row.innerHTML =
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
                        product.id + // Use product.id instead of (index + 1)
                        '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' + product.name +
                        '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' +
                        '<input type="number" min="1" step="1" value="' + (
                            product.quantity || 1) + '"' +
                        ' class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 product-quantity-input"' +
                        ' data-index="' + index + '">' +
                        '</td>' +
                        '<td class="px-6 py-4 text-sm text-gray-700 serial-cell" id="' +
                        product.uniqueId + '_serials">' +
                        '<div class="space-y-2"></div>' +
                        '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">' +
                        '<button type="button" class="text-red-500 hover:text-red-700 delete-product" data-index="' +
                        index + '">' +
                        '<i class="fas fa-trash"></i>' +
                        '</button>' +
                        '</td>';

                    productList.insertBefore(row, noProductsRow);

                    // Generate serial inputs based on quantity
                    const serialCell = row.querySelector('#' + product.uniqueId + '_serials');
                    generateProductSerialInputs(product, index, serialCell);

                    // Add event listener for quantity changes
                    const qtyInput = row.querySelector('.product-quantity-input');

                    // Validate on input
                    qtyInput.addEventListener('input', function() {
                        const value = this.value.trim();
                        const numValue = parseInt(value);

                        // Check if empty or invalid
                        if (value === '' || isNaN(numValue) || numValue < 1) {
                            this.setCustomValidity('Số lượng phải là số nguyên dương (≥ 1)');
                            this.classList.add('border-red-500');
                        } else {
                            this.setCustomValidity('');
                            this.classList.remove('border-red-500');
                        }
                    });

                    qtyInput.addEventListener('change', function() {
                        const value = this.value.trim();
                        let newQty = parseInt(value);

                        // Validate and correct value
                        if (value === '' || isNaN(newQty) || newQty < 1) {
                            newQty = 1;
                            this.value = '1';
                        }

                        // Clear validation styling
                        this.setCustomValidity('');
                        this.classList.remove('border-red-500');

                        product.quantity = newQty;

                        // Lưu lại các giá trị serial hiện tại từ DOM trước khi thay đổi
                        const existingInputs = serialCell.querySelectorAll('input[type="text"]');
                        const currentInputValues = [];
                        existingInputs.forEach((input, inputIndex) => {
                            if (inputIndex < newQty) {
                                currentInputValues.push(input.value);
                            }
                        });
                        
                        // Cập nhật product.serials với các giá trị hiện tại
                        for (let i = 0; i < Math.min(currentInputValues.length, newQty); i++) {
                            product.serials[i] = currentInputValues[i];
                        }
                        
                        // Điều chỉnh mảng serials dựa trên số lượng mới
                        if (newQty > product.serials.length) {
                            const additionalSerials = Array(newQty - product.serials.length).fill('');
                            product.serials = [...product.serials, ...additionalSerials];
                        } else if (newQty < product.serials.length) {
                            product.serials = product.serials.slice(0, newQty);
                        }

                        // Regenerate serial inputs
                        generateProductSerialInputs(product, index, serialCell);

                        // Update hidden inputs when quantity changes
                        updateHiddenProductList();
                    });

                    // Don't add event listener here - use event delegation instead
                });

                // Update hidden product inputs for form submission
                updateHiddenProductList();
                
                // Update add product button state
                updateAddProductButtonState();
            }

            // Hàm tạo các trường nhập serial cho thành phẩm với validation
            function generateProductSerialInputs(product, productIndex, container) {
                container.innerHTML = '';
                const quantity = parseInt(product.quantity);

                for (let i = 0; i < quantity; i++) {
                    const serialInput = document.createElement('div');
                    serialInput.className = 'mb-2 last:mb-0';

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = product.serials[i] || '';
                    // Ensure serials are submitted directly without hidden duplicates
                    input.name = `products[${productIndex}][serials][]`;
                    input.placeholder = quantity > 1 ? `Serial ${i + 1} (tùy chọn)` : 'Serial (tùy chọn)';
                    input.className =
                        'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500';

                    // Save serial when typing
                    input.addEventListener('input', function() {
                        product.serials[i] = this.value;
                        updateHiddenProductList(); // Update hidden inputs when serial changes
                    });

                    // Save serial when losing focus (blur)
                    input.addEventListener('blur', function() {
                        product.serials[i] = this.value;
                        updateHiddenProductList();
                    });

                    // Add serial validation
                    addProductSerialValidation(input, product.id);

                    serialInput.appendChild(input);
                    container.appendChild(serialInput);
                }

                // Thêm thông báo nếu có nhiều serial
                if (quantity > 3) {
                    const noteDiv = document.createElement('div');
                    noteDiv.className = 'mt-1 text-xs text-blue-700';
                    noteDiv.innerHTML = '<i class="fas fa-info-circle mr-1"></i> ' + quantity + ' serials';
                    container.appendChild(noteDiv);
                }
            }

            // Update product selection dropdown for components when products are added or removed
            function updateComponentProductDropdown() {
                componentProductSelect.innerHTML = '<option value="">--Thành phẩm--</option>';

                if (selectedProducts.length === 0) {
                    componentProductSelect.disabled = true;
                    addComponentBtn.disabled = true;
                    addComponentBtn.classList.add('opacity-50');
                    componentSearchInput.disabled = true;
                    componentAddQuantity.disabled = true;

                    if (componentSearchInput.value) {
                        componentSearchInput.value = '';
                        searchResults.classList.add('hidden');
                        selectedMaterial = null;
                        selectedComponentToAdd = null;
                    }
                } else {
                    componentProductSelect.disabled = false;
                    // Chỉ kích hoạt nút thêm nếu đã chọn vật tư
                    const hasSelectedMaterial = selectedMaterial || selectedComponentToAdd;
                    addComponentBtn.disabled = !hasSelectedMaterial;
                    if (hasSelectedMaterial) {
                        addComponentBtn.classList.remove('opacity-50');
                    } else {
                        addComponentBtn.classList.add('opacity-50');
                    }
                    componentSearchInput.disabled = false;
                    componentAddQuantity.disabled = false;

                    // Remove warning message if exists
                    const existingMessage = document.querySelector('.component-product-warning');
                    if (existingMessage) {
                        existingMessage.remove();
                    }

                    // Add products to the dropdown
                    selectedProducts.forEach((product) => {
                        const option = document.createElement('option');
                        option.value = product.uniqueId;
                        option.textContent = product.name;
                        componentProductSelect.appendChild(option);
                    });
                }
            }

            // Thêm sự kiện khi thay đổi dropdown thành phẩm
            componentProductSelect.addEventListener('change', function() {

                // Kích hoạt nút thêm nếu đã chọn vật tư
                const hasSelectedMaterial = selectedMaterial || selectedComponentToAdd;
                addComponentBtn.disabled = !hasSelectedMaterial || !this.value;

                console.log("Product dropdown changed:", {
                    selectedValue: this.value,
                    hasSelectedMaterial: hasSelectedMaterial,
                    selectedMaterial: selectedMaterial,
                    selectedComponentToAdd: selectedComponentToAdd
                });

                if (hasSelectedMaterial && this.value) {
                    addComponentBtn.classList.remove('opacity-50');
                    console.log("Enabled add component button - both material and product selected");
                } else {
                    addComponentBtn.classList.add('opacity-50');
                    if (!hasSelectedMaterial) {
                        console.log("Add button disabled - no material selected");
                    }
                    if (!this.value) {
                        console.log("Add button disabled - no product selected");
                    }
                }
            });

            // Hàm lấy danh sách linh kiện theo kho
            function fetchWarehouseMaterials(warehouseId) {
                if (!warehouseId) return;

                // Hiển thị đang tải
                warehouseMaterials = [];

                // Gọi API để lấy linh kiện theo kho
                fetch('/api/warehouses/' + warehouseId + '/materials')
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error('Error fetching warehouse materials:', data.message);
                            return;
                        }

                        // Lưu danh sách vật tư của kho
                        warehouseMaterials = Array.isArray(data) ? data : (data.materials || []);
                    })
                    .catch(error => {
                        console.error('Error loading warehouse materials:', error);
                    });
            }

            // Hiển thị tất cả linh kiện của kho
            function showAllMaterials() {
                if (warehouseMaterials.length === 0) {
                    const warehouseId = getWarehouseId();
                    if (!warehouseId) return;

                    // Hiển thị đang tải
                    searchResults.innerHTML =
                        '<div class="p-2 text-gray-500">Đang tải danh sách linh kiện...</div>';
                    searchResults.classList.remove('hidden');

                    // Gọi API để lấy linh kiện theo kho
                    fetch('/api/warehouses/' + warehouseId + '/materials')
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                searchResults.innerHTML =
                                    '<div class="p-2 text-red-500">Lỗi: ' + data.message + '</div>';
                                console.error('Error fetching warehouse materials:', data.message);
                                return;
                            }

                            // Lưu và hiển thị danh sách vật tư của kho
                            warehouseMaterials = Array.isArray(data) ? data : (data.materials || []);
                            displaySearchResults(warehouseMaterials);
                        })
                        .catch(error => {
                            console.error('Error loading warehouse materials:', error);
                            searchResults.innerHTML =
                                '<div class="p-2 text-red-500">Có lỗi xảy ra khi tải dữ liệu!</div>';
                        });
                } else {
                    // Hiển thị danh sách đã có
                    displaySearchResults(warehouseMaterials);
                }
            }

            // Hiển thị kết quả tìm kiếm
            function displaySearchResults(materials) {
                if (materials.length === 0) {
                    searchResults.innerHTML =
                        '<div class="p-2 text-gray-500">Không có linh kiện nào trong kho này</div>';
                    return;
                }

                searchResults.innerHTML = '';
                materials.forEach(material => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                    resultItem.innerHTML =
                        '<div class="font-medium">' + material.code + ': ' + material.name + '</div>' +
                        '<div class="text-xs text-gray-500">' +
                        (material.category || '') + ' ' +
                        (material.serial ? '| ' + material.serial : '') + ' ' +
                        '| Tồn kho: ' + (material.stock_quantity || 0) +
                        '</div>';

                    // Handle click on search result
                    resultItem.addEventListener('click', function() {
                        // Cập nhật cả hai biến để đảm bảo tính nhất quán
                        selectedMaterial = material;
                        selectedComponentToAdd = material;
                        componentSearchInput.value = material.code + ' - ' + material.name;
                        searchResults.classList.add('hidden');

                        // Kích hoạt nút thêm nếu đã chọn thành phẩm và đơn vị
                        const hasSelectedProduct = componentProductSelect.value;
                        const hasSelectedUnit = componentProductUnitSelect.value !== '';
                        addComponentBtn.disabled = !hasSelectedProduct || !hasSelectedUnit;

                        if (hasSelectedProduct && hasSelectedUnit) {
                            addComponentBtn.classList.remove('opacity-50');
                            console.log(
                                "Enabled add component button - material, product and unit selected"
                                );
                        } else {
                            addComponentBtn.classList.add('opacity-50');
                            if (!hasSelectedProduct) {
                                console.log("Material selected but waiting for product selection");
                            }
                            if (!hasSelectedUnit) {
                                console.log(
                                    "Material and product selected but waiting for unit selection"
                                    );
                            }
                        }
                    });

                    searchResults.appendChild(resultItem);
                });

                searchResults.classList.remove('hidden');
            }

            // Xử lý thêm linh kiện khi nhấn nút "Thêm"
            addComponentBtn.addEventListener('click', handleAddComponent);

            // Thêm sự kiện Enter trong ô nhập liệu linh kiện để thêm linh kiện nhanh
            componentSearchInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter' && selectedMaterial && componentProductSelect.value &&
                    componentProductUnitSelect.value) {
                    event.preventDefault(); // Ngăn chặn hành vi mặc định của Enter
                    handleAddComponent(); // Gọi hàm thêm linh kiện
                }
            });



            // Add selected component function
            function handleAddComponent() {
                if (selectedProducts.length === 0) {
                    alert('Vui lòng thêm thành phẩm trước khi thêm linh kiện!');
                    return;
                }

                // Kiểm tra cả hai biến để đảm bảo có vật tư được chọn
                if (!selectedMaterial && !selectedComponentToAdd) {
                    alert('Vui lòng chọn linh kiện trước khi thêm!');
                    return;
                }

                // Ưu tiên sử dụng selectedComponentToAdd nếu có
                const materialToAdd = selectedComponentToAdd || selectedMaterial;
                if (!materialToAdd) {
                    alert('Vui lòng chọn linh kiện trước khi thêm!');
                    return;
                }

                const selectedProductId = componentProductSelect.value;
                if (!selectedProductId) {
                    alert('Vui lòng chọn thành phẩm trước khi thêm!');
                    return;
                }

                const selectedProduct = selectedProducts.find(p => p.uniqueId === selectedProductId);
                if (!selectedProduct) {
                    alert('Không tìm thấy thành phẩm được chọn!');
                    return;
                }

                // Đảm bảo số lượng là số nguyên dương
                let quantity = parseInt(componentAddQuantity.value);
                if (isNaN(quantity) || quantity < 1) {
                    quantity = 1;
                    componentAddQuantity.value = "1";
                }

                try {
                    // Log số lượng vật tư trước khi thêm
                    const componentsBefore = window.selectedComponents ? window.selectedComponents.length : 0;
                    console.log(`Components before adding: ${componentsBefore}`);

                    // Gọi hàm window.addSelectedComponent để thêm linh kiện
                    const newComponent = window.addSelectedComponent(materialToAdd, selectedProduct, quantity);

                    if (!newComponent) {
                        console.log("No component added - user cancelled or error occurred");
                        return; // Exit early if no component was added
                    }

                    // Log số lượng vật tư sau khi thêm
                    const componentsAfter = window.selectedComponents ? window.selectedComponents.length : 0;

                    // Hiển thị thông báo thành công
                    const successMessage = document.createElement('div');
                    successMessage.className =
                        'text-green-500 text-sm p-3 text-center fixed top-4 right-4 bg-white shadow-lg rounded-lg z-50';

                    // Sử dụng dữ liệu an toàn từ materialToAdd và selectedProduct
                    const componentName = materialToAdd.name || materialToAdd.code || 'Vật tư';
                    const productName = selectedProduct.name || 'Thành phẩm';
                    const unitText = selectedUnit !== '' ? ` (Đơn vị ${parseInt(selectedUnit) + 1})` : '';

                    successMessage.innerHTML =
                        '<i class="fas fa-check-circle"></i> Đã thêm "' + componentName + '" vào thành phẩm "' +
                        productName + '"' + unitText;

                    // Kiểm tra document.body tồn tại trước khi append
                    if (document.body) {
                        document.body.appendChild(successMessage);

                        // Xóa thông báo sau 2 giây
                        setTimeout(() => {
                            if (successMessage && successMessage.parentNode) {
                                successMessage.parentNode.removeChild(successMessage);
                            }
                        }, 2000);
                    }

                    // Reset search input và selectedMaterial
                    componentSearchInput.value = '';
                    selectedMaterial = null;
                    selectedComponentToAdd = null;
                    searchResults.classList.add('hidden');

                    // Reset dropdowns
                    componentProductSelect.value = '';
                    componentProductUnitSelect.innerHTML = '<option value="">--Đơn vị--</option>';
                    componentProductUnitSelect.disabled = true;

                    // Disable add button
                    addComponentBtn.disabled = true;
                    addComponentBtn.classList.add('opacity-50');

                    // Cập nhật giao diện
                    console.log("Updating UI with new component. Total components:", window.selectedComponents
                        .length);
                    console.log("Components for this product:", window.selectedComponents.filter(c => c
                        .productId === selectedProductId));
                    updateProductComponentList(selectedProductId);

                    // Kiểm tra thay đổi công thức và hiển thị button tạo thành phẩm mới
                    checkAndShowCreateNewProductButton(selectedProductId);

                    // Tự động cuộn đến block của thành phẩm
                    const componentBlock = document.getElementById('component_block_' + selectedProductId);
                    if (componentBlock) {
                        componentBlock.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                } catch (error) {
                    console.error("Error adding component:", error);
                    alert('Có lỗi khi thêm linh kiện: ' + error.message);
                }
            }

            // Update component quantities based on product quantity
            function updateComponentQuantities() {
                // Store existing components that were manually added
                const manuallyAddedComponents = selectedComponents.filter(c => !c.isFromProduct && !c.isFromExistingProduct);
                // Store existing components from existing products
                const existingProductComponents = selectedComponents.filter(c => c.isFromExistingProduct);

                console.log('Stored existing product components:', existingProductComponents.length);
                console.log('Existing product components details:', existingProductComponents.map(c => ({
                    id: c.id,
                    name: c.name,
                    productId: c.productId,
                    productUnit: c.productUnit,
                    isFromExistingProduct: c.isFromExistingProduct
                })));

                // Clear existing components
                selectedComponents = [];

                // Recreate components for each product based on current quantities
                console.log('updateComponentQuantities - processing products:', selectedProducts.map(p => ({
                    uniqueId: p.uniqueId,
                    name: p.name,
                    quantity: p.quantity,
                    hasOriginalComponents: !!p.originalComponents,
                    originalComponentsCount: p.originalComponents ? p.originalComponents.length : 0,
                    isUsingExisting: p.isUsingExisting
                })));

                selectedProducts.forEach(product => {
                    const productQty = parseInt(product.quantity) || 1;

                    console.log(`Processing product ${product.uniqueId}:`, {
                        name: product.name,
                        quantity: productQty,
                        hasOriginalComponents: !!product.originalComponents,
                        originalComponentsCount: product.originalComponents ? product
                            .originalComponents.length : 0,
                        isUsingExisting: product.isUsingExisting
                    });

                    // If using existing product, recreate components from existing product data
                    if (product.isUsingExisting) {
                        // Find the original components from existing product
                        // Check both old and new productId patterns
                        const existingComponents = existingProductComponents.filter(c => 
                            c.productId === product.uniqueId || 
                            c.productId === `product_${product.id}` ||
                            c.productId === product.productId
                        );
                        
                        console.log(`Processing existing product ${product.uniqueId}:`, {
                            existingComponentsCount: existingComponents.length,
                            productQty: productQty,
                            existingComponents: existingComponents.map(c => ({
                                id: c.id,
                                name: c.name,
                                productUnit: c.productUnit
                            }))
                        });
                        
                        if (existingComponents.length > 0) {
                            // Get the base components (from unit 0)
                            const baseComponents = existingComponents.filter(c => c.productUnit === 0);
                            
                            console.log(`Found ${baseComponents.length} base components for product ${product.uniqueId}`);
                            
                            // Recreate for all units
                            for (let unitIndex = 0; unitIndex < productQty; unitIndex++) {
                                baseComponents.forEach(baseComponent => {
                                    // Get default warehouse if available
                                    const defaultWarehouseId = document.getElementById('default_warehouse_id')?.value || '';
                                    
                                    const newComponent = {
                                        ...baseComponent,
                                        productUnit: unitIndex,
                                        unitLabel: unitIndex > 0 ? `Đơn vị ${unitIndex + 1}` : '',
                                        isFromExistingProduct: true,
                                        isFromProduct: true,
                                        originalQuantity: baseComponent.originalQuantity || baseComponent.quantity,
                                        warehouseId: defaultWarehouseId || baseComponent.warehouseId || ''
                                    };
                                    console.log(`Creating component for unit ${unitIndex}:`, newComponent);
                                    selectedComponents.push(newComponent);
                                });
                            }
                        } else {
                            console.log(`No existing components found for product ${product.uniqueId}`);
                        }
                    } else {
                        // Add original components from product formula (existing logic)
                    if (product.originalComponents) {
                        for (let unitIndex = 0; unitIndex < productQty; unitIndex++) {
                            product.originalComponents.forEach(originalComponent => {
                                // Get default warehouse if available
                                const defaultWarehouseId = document.getElementById('default_warehouse_id')?.value || '';
                                
                                const newComponent = {
                                    ...originalComponent,
                                    id: originalComponent.id,
                                    code: originalComponent.code,
                                    name: originalComponent.name,
                                    category: originalComponent.category,
                                    unit: originalComponent.unit,
                                    quantity: originalComponent.quantity || 1,
                                    originalQuantity: originalComponent.quantity || 1,
                                    notes: originalComponent.notes || '',
                                    serial: '',
                                    serials: [],
                                    productId: product.uniqueId,
                                    actualProductId: product.id,
                                    isFromProduct: true,
                                    isOriginal: true,
                                    warehouseId: defaultWarehouseId,
                                    productUnit: unitIndex,
                                    unitLabel: unitIndex > 0 ? `Đơn vị ${unitIndex + 1}` : ''
                                };

                                selectedComponents.push(newComponent);
                            });
                            }
                        }
                    }
                });

                // Re-add manually added components - ONLY to their original units
                console.log("Re-adding manually added components:", manuallyAddedComponents.length);
                manuallyAddedComponents.forEach(component => {
                    const product = selectedProducts.find(p => p.uniqueId === component.productId);
                    if (product) {
                        // Only add to the specific unit where it was originally added
                        // Get default warehouse if available
                        const defaultWarehouseId = document.getElementById('default_warehouse_id')?.value || '';
                        
                        const newComponent = {
                            ...component,
                            productUnit: component.productUnit, // Keep original unit
                            unitLabel: component.productUnit > 0 ?
                                `Đơn vị ${component.productUnit + 1}` : '',
                            warehouseId: defaultWarehouseId || component.warehouseId || ''
                        };

                        console.log(
                            `Re-adding component "${component.name}" (ID: ${component.id}) to unit ${component.productUnit} only`
                            );
                        selectedComponents.push(newComponent);
                    }
                });

                // Re-add existing product components to their original units only
                console.log("Re-adding existing product components:", existingProductComponents.length);
                existingProductComponents.forEach(component => {
                    const product = selectedProducts.find(p => p.uniqueId === component.productId);
                    if (product && product.isUsingExisting) {
                        // Only add to the specific unit where it was originally added
                        // Get default warehouse if available
                        const defaultWarehouseId = document.getElementById('default_warehouse_id')?.value || '';
                        
                        const newComponent = {
                            ...component,
                            productUnit: component.productUnit, // Keep original unit
                            unitLabel: component.productUnit > 0 ?
                                `Đơn vị ${component.productUnit + 1}` : '',
                            isFromExistingProduct: true,
                            isFromProduct: true,
                            warehouseId: defaultWarehouseId || component.warehouseId || ''
                        };

                        console.log(
                            `Re-adding existing product component "${component.name}" (ID: ${component.id}) to unit ${component.productUnit} only`
                            );
                        selectedComponents.push(newComponent);
                    }
                });

                console.log('Final selectedComponents after updateComponentQuantities:', {
                    total: selectedComponents.length,
                    components: selectedComponents.map(c => ({
                        id: c.id,
                        name: c.name,
                        productId: c.productId,
                        productUnit: c.productUnit,
                        isFromProduct: c.isFromProduct
                    }))
                });

                // Debug: Check if any components exist in multiple units
                const componentsByMaterial = {};
                selectedComponents.forEach(comp => {
                    if (!componentsByMaterial[comp.id]) {
                        componentsByMaterial[comp.id] = [];
                    }
                    componentsByMaterial[comp.id].push({
                        name: comp.name,
                        productUnit: comp.productUnit,
                        isFromProduct: comp.isFromProduct,
                        isOriginal: comp.isOriginal
                    });
                });

                Object.keys(componentsByMaterial).forEach(materialId => {
                    const components = componentsByMaterial[materialId];
                    if (components.length > 1) {
                        console.log(`WARNING: Material ${materialId} exists in multiple units:`,
                        components);
                        // Check if this is a user-added component
                        const userAddedComponents = components.filter(c => !c.isFromProduct);
                        if (userAddedComponents.length > 1) {
                            console.log(
                                `ERROR: User-added material ${materialId} exists in multiple units! This should not happen.`
                                );
                        }
                    }
                });

                // Update all product component lists
                selectedProducts.forEach(product => {
                    updateProductComponentList(product.uniqueId);
                });

                // Add visual feedback for quantity changes
                selectedProducts.forEach(product => {
                    const componentBlock = document.getElementById('component_block_' + product.uniqueId);
                    if (componentBlock) {
                        const feedbackMessage = document.createElement('div');
                        feedbackMessage.className =
                            'text-blue-500 text-sm p-2 text-center bg-blue-50 border border-blue-200 rounded';
                        feedbackMessage.innerHTML =
                            `<i class="fas fa-info-circle"></i> Đã cập nhật hiển thị cho ${product.quantity} đơn vị thành phẩm`;
                        feedbackMessage.id = 'quantity-feedback-' + product.uniqueId;
                        componentBlock.appendChild(feedbackMessage);

                        setTimeout(() => {
                            const element = document.getElementById('quantity-feedback-' + product
                                .uniqueId);
                            if (element) element.remove();
                        }, 3000);
                    }
                });
            }



            // Update component list to allow editing quantities
            function updateComponentList() {
                // Group components by product
                const componentsByProduct = {};

                // Initialize empty arrays for each product
                selectedProducts.forEach(product => {
                    componentsByProduct[product.uniqueId] = [];
                });

                // Group components by product ID
                selectedComponents.forEach(component => {
                    if (componentsByProduct[component.productId]) {
                        componentsByProduct[component.productId].push(component);
                    }
                });

                // Update each product's component list
                for (const [productId, components] of Object.entries(componentsByProduct)) {
                    const componentList = document.getElementById('component_list_' + productId);
                    if (!componentList) continue;

                    // Clear current components (except for empty state row)
                    const emptyRow = document.getElementById('no_components_row_' + productId);
                    while (componentList.firstChild) {
                        componentList.removeChild(componentList.firstChild);
                    }

                    // Add empty row back if it exists
                    if (emptyRow) {
                        componentList.appendChild(emptyRow);
                    }

                    // Show or hide the empty row
                    if (emptyRow) {
                        if (components.length > 0) {
                            emptyRow.style.display = 'none';
                        } else {
                            emptyRow.style.display = '';
                            continue; // Skip to next product if no components
                        }
                    }

                    // Add each component
                    components.forEach((component, index) => {
                        const globalIndex = selectedComponents.indexOf(component);
                        const row = document.createElement('tr');

                        // Add stock warning if needed
                        const stockWarningHtml = component.stockWarning ?
                            '<div class="text-xs text-red-500 mt-1 stock-warning">Không đủ tồn kho: ' +
                            component
                            .stock_quantity +
                            ' < ' + component.quantity + '</div>' :
                            '';

                        // Editable quantity input
                        const quantityInputHtml =
                            '<input type="number" min="1" step="1" name="components[' + globalIndex +
                            '][quantity]" value="' + (
                                component.quantity || 1) + '"' +
                            ' class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input component-quantity-input"' +
                            ' data-component-index="' + globalIndex + '">';

                        // Ensure default warehouse for this component if missing
                        if (!component.warehouseId) {
                            component.warehouseId = getWarehouseId() || '';
                        }

                        // Set row HTML with warehouse cell
                        row.innerHTML =
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
                            '<input type="hidden" name="components[' + globalIndex + '][id]" value="' + component
                            .id + '">' +
                            // Do not include visible product_id input to avoid overriding hidden mapping
                            component.code +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' + component
                            .category +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' +
                            component.name +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' +
                            quantityInputHtml +
                            stockWarningHtml +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 warehouse-cell">' +
                            '<!-- Warehouse selector will be added here -->' +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 serial-cell">' +
                            '<!-- Serial inputs will be added here -->' +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' +
                            '<input type="text" name="components[' + globalIndex + '][note]" value="' + (component
                                .note ||
                                '') + '"' +
                            ' class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 note-input"' +
                            ' placeholder="Ghi chú" data-component-index="' + selectedComponents.indexOf(
                                component) + '">' +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">' +
                            '<button type="button" class="text-red-500 hover:text-red-700 delete-component" ' +
                            'data-index="' + selectedComponents.indexOf(component) + '">' +
                            '<i class="fas fa-trash"></i>' +
                            '</button>' +
                            '</td>';

                        componentList.appendChild(row);

                        // Add warehouse selector for this component
                        const warehouseCell = row.querySelector('.warehouse-cell');
                        if (warehouseCell) {
                            addWarehouseSelectToCell(warehouseCell, component, globalIndex);
                        }

                        // Add serial inputs for this component
                        const serialCell = row.querySelector('.serial-cell');
                        addSerialInputsToCell(serialCell, component, globalIndex);

                        // Add product unit selector
                        const productUnitCell = row.querySelector('.product-unit-cell');
                        if (productUnitCell) {
                            addProductUnitSelector(productUnitCell, component, index);
                        }
                    });

                    // Check and show create new product button for this product after updating components
                    checkAndShowCreateNewProductButton(productId);
                }

                // Also update the hidden component list for form submission
                updateHiddenComponentList();
            }

            // Update hidden component list for form submission
            function updateHiddenComponentList() {
                // Get the old component list (hidden but needed for form submission)
                const oldComponentList = document.getElementById('component_list');
                const noComponentsRow = document.getElementById('no_components_row');

                // Kiểm tra oldComponentList có tồn tại không
                if (!oldComponentList) {
                    console.warn('Component list not found');
                    return;
                }

                // Remove all rows except the no_components_row
                Array.from(oldComponentList.children).forEach(child => {
                    if (child.id !== 'no_components_row') {
                        oldComponentList.removeChild(child);
                    }
                });

                // Show/hide no components message
                if (noComponentsRow) {
                    if (selectedComponents.length === 0) {
                        noComponentsRow.style.display = '';
                        return;
                    } else {
                        noComponentsRow.style.display = 'none';
                    }
                }

                // Debug: Log selectedComponents before creating form fields
                console.log('updateHiddenComponentList - selectedComponents:', {
                    total: selectedComponents.length,
                    components: selectedComponents.map(c => ({
                        id: c.id,
                        name: c.name,
                        productId: c.productId,
                        productUnit: c.productUnit,
                        isFromProduct: c.isFromProduct
                    }))
                });

                // Build a map from uniqueId -> real product id for reliable mapping
                const uidToRealId = {};
                selectedProducts.forEach(p => { uidToRealId[p.uniqueId] = p.id; });

                // Add all components to the hidden list with proper form fields
                selectedComponents.forEach((component, index) => {
                    const globalIndex = index; // Ensure names align with selectedComponents order
                    // Debug: Log the component and available products
                    console.log('Processing component for hidden input:', {
                        componentId: component.id,
                        componentProductId: component.productId,
                        componentActualProductId: component.actualProductId,
                        selectedProducts: selectedProducts.map(p => ({ id: p.id, uniqueId: p.uniqueId })),
                        componentIndex: index
                    });

                    // Resolve product_id and product_index for this component in multi-product mode
                    let resolvedProductId = '';
                    let resolvedProductIndex = -1;
                    // Prefer explicit real id set on component
                    if (!resolvedProductId && component.productRealId) {
                        resolvedProductId = component.productRealId;
                        resolvedProductIndex = selectedProducts.findIndex(p => p.id == component.productRealId);
                    }
                    // Then by uniqueId
                    if (!resolvedProductId && component.productId && uidToRealId[component.productId]) {
                        resolvedProductId = uidToRealId[component.productId];
                        resolvedProductIndex = selectedProducts.findIndex(p => p.uniqueId === component.productId);
                    }
                    // Fallback to actualProductId
                    if (!resolvedProductId && component.actualProductId) {
                        resolvedProductId = component.actualProductId;
                        resolvedProductIndex = selectedProducts.findIndex(p => p.id == component.actualProductId);
                    }
                    // Try parse pattern product_N
                    if (!resolvedProductId && typeof component.productId === 'string') {
                        const m = component.productId.match(/^product_(\d+)$/);
                        if (m) {
                            const idx = Math.max(0, parseInt(m[1], 10) - 1);
                            if (selectedProducts[idx]) {
                                resolvedProductId = selectedProducts[idx].id;
                                resolvedProductIndex = idx;
                            }
                        }
                    }
                    // Last fallback
                    if (!resolvedProductId && selectedProducts[0]) {
                        resolvedProductId = selectedProducts[0].id;
                        resolvedProductIndex = 0;
                    }

                    console.log('Component mapping:', {
                        componentId: component.id,
                        compProductId: component.productId,
                        compActualProductId: component.actualProductId,
                        resolvedProductId,
                        resolvedProductIndex
                    });

                    console.log('Resolved product_id for component:', {
                        componentId: component.id,
                        resolvedProductId: resolvedProductId,
                        selectedProductsFirstId: selectedProducts[0] ? selectedProducts[0].id : 'undefined'
                    });
                    const row = document.createElement('tr');
                    row.style.display = 'none'; // Hide row but keep form fields

                    // Handle serials
                    let serialHtml = '';
                    if (component.serials && component.serials.length > 0) {
                        // Multiple serials
                        component.serials.forEach((serial, serialIndex) => {
                            serialHtml += '<input type="hidden" name="components[' + index +
                                '][serials][]" value="' + (serial || '') + '">';
                        });
                        // Multiple serial_ids
                        if (component.serial_ids && component.serial_ids.length > 0) {
                            component.serial_ids.forEach((serialId, serialIndex) => {
                                serialHtml += '<input type="hidden" name="components[' + index +
                                    '][serial_id][]" value="' + (serialId || '') + '">';
                            });
                        }
                    } else {
                        // Single serial
                        serialHtml = '<input type="hidden" name="components[' + index +
                            '][serial]" value="' + (component.serial || '') + '">';
                        // Add serial_id if available
                        if (component.serial_id) {
                            serialHtml += '<input type="hidden" name="components[' + index +
                                '][serial_id]" value="' + component.serial_id + '">';
                        }
                    }

                    row.innerHTML =
                        '<td>' +
                        '<input type="hidden" name="components[' + globalIndex + '][id]" value="' + component.id +
                        '">' +
                        '<input type="hidden" name="components[' + globalIndex + '][product_id]" value="' +
                        resolvedProductId + '">' +
                        '<input type="hidden" name="components[' + globalIndex + '][product_index]" value="' +
                        resolvedProductIndex + '">' +
                        // ensure warehouse_id is present for backend validation
                        '<input type="hidden" name="components[' + globalIndex + '][warehouse_id]" value="' +
                        (component.warehouseId || getWarehouseId() || '') + '">' +
                        '<input type="hidden" name="components[' + globalIndex + '][quantity]" value="' + (
                            component
                            .quantity || 1) + '">' +
                        '<input type="hidden" name="components[' + globalIndex + '][product_unit]" value="' + (
                            component.productUnit || 0) + '">' +
                        serialHtml +
                        '<input type="hidden" name="components[' + globalIndex + '][note]" value="' + (component
                            .note || '') +
                        '">' +
                        '</td>';

                    // Đảm bảo noComponentsRow vẫn tồn tại trước khi insert
                    if (noComponentsRow && noComponentsRow.parentNode) {
                        oldComponentList.insertBefore(row, noComponentsRow);
                    } else {
                        oldComponentList.appendChild(row);
                    }
                });
            }

            // Update hidden product list for form submission
            window.updateHiddenProductList = function() {
                // Debug: Log selectedProducts before creating hidden inputs
                console.log('updateHiddenProductList - selectedProducts:', selectedProducts.map(p => ({
                    id: p.id,
                    name: p.name,
                    uniqueId: p.uniqueId
                })));

                // Đồng bộ serial values từ DOM trước khi tạo hidden inputs
                selectedProducts.forEach((product, index) => {
                    const serialCell = document.getElementById(product.uniqueId + '_serials');
                    if (serialCell) {
                        const inputs = serialCell.querySelectorAll('input[type="text"]');
                        // Build serials array strictly from visible inputs to avoid stale values
                        const newSerials = Array.from(inputs).map(inp => (inp.value || '').trim());
                        console.log(`Syncing serials for product ${product.id}:`, {
                            inputsCount: inputs.length,
                            newSerials
                        });
                        product.serials = newSerials;
                    } else {
                        console.warn(`Serial cell not found for product ${product.uniqueId}`);
                        // Ensure serials length matches quantity even if cell missing
                        if (!Array.isArray(product.serials)) product.serials = [];
                        product.serials = product.serials.slice(0, parseInt(product.quantity) || 0);
                    }
                });

                // Remove existing HIDDEN product inputs only (do NOT remove visible serial inputs)
                const existingProductInputs = document.querySelectorAll('input[type="hidden"][name^="products["]');
                existingProductInputs.forEach(input => input.remove());

                // Add products to form
                selectedProducts.forEach((product, index) => {
                    const form = document.querySelector('form');

                    // Product ID
                    const productIdInput = document.createElement('input');
                    productIdInput.type = 'hidden';
                    productIdInput.name = 'products[' + index + '][id]';
                    productIdInput.value = product.id;
                    form.appendChild(productIdInput);

                    // Product Code - lấy từ danh sách đã chọn (đã lưu cùng product)
                    const productCode = product.code || (product.name ? product.name : '');
                    
                    const productCodeInput = document.createElement('input');
                    productCodeInput.type = 'hidden';
                    productCodeInput.name = 'products[' + index + '][code]';
                    productCodeInput.value = productCode;
                    form.appendChild(productCodeInput);

                    // Product Quantity
                    const productQuantityInput = document.createElement('input');
                    productQuantityInput.type = 'hidden';
                    productQuantityInput.name = 'products[' + index + '][quantity]';
                    productQuantityInput.value = product.quantity;
                    form.appendChild(productQuantityInput);

                    // Do NOT create hidden product serial inputs here.
                    // Visible serial inputs already have proper names: products[index][serials][]
                });
            }

            // Make updateProductComponentList available globally for assembly-product-unit.js
            window.updateProductComponentList = updateProductComponentList;

            // Function to add serial inputs to a cell
            function addSerialInputsToCell(cell, component, index) {
                // Clear existing content
                cell.innerHTML = '';

                const quantity = parseInt(component.quantity) || 1;

                // Generate unique identifier for this component instance
                const componentInstanceId = 'comp_' + component.id + '_' + component.productId + '_' + index + '_' +
                    Date.now();

                // If quantity is 1, show single serial dropdown
                if (quantity === 1) {
                    const serialContainer = document.createElement('div');
                    serialContainer.className = 'relative';

                    // Create select dropdown
                    const selectElement = document.createElement('select');
                    selectElement.name = 'components[' + index + '][serial]';
                    selectElement.className =
                        'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 material-serial-select';
                    selectElement.setAttribute('data-material-id', component.id);
                    selectElement.setAttribute('data-component-index', index);
                    selectElement.setAttribute('data-instance-id', componentInstanceId);
                    selectElement.setAttribute('data-product-id', component.actualProductId);
                    selectElement.setAttribute('data-product-unit', component.productUnit || 0);
                    selectElement.id = componentInstanceId + '_select';

                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Chọn serial (tùy chọn)';
                    selectElement.appendChild(defaultOption);

                    // Hidden input for serial_id
                    const serialIdInput = document.createElement('input');
                    serialIdInput.type = 'hidden';
                    serialIdInput.name = 'components[' + index + '][serial_id]';
                    serialIdInput.value = component.serial_id || '';

                    // Load serials when warehouse is selected
                    loadSerialsForSelect(selectElement, serialIdInput, component, index);

                    // Add event listener for select change
                    selectElement.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        const selectedSerial = this.value;
                        const currentMaterialId = this.getAttribute('data-material-id');

                        // Check if this serial is already selected in other dropdowns of the same material
                        const isSerialUsed = selectedSerial && selectedComponents.some(comp =>
                            comp.id === component.id && // Same material
                            ((comp.serial === selectedSerial) || // Check single serial
                                (comp.serials && comp.serials.includes(selectedSerial))) &&
                            // Check multiple serials
                            comp !== component // Different component instance
                        );

                        if (isSerialUsed) {
                            // Reset selection
                            this.value = '';
                            component.serial = '';
                            component.serial_id = '';
                            serialIdInput.value = '';

                            // Show error message
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'text-red-500 text-xs mt-1';
                            errorDiv.textContent = 'Serial này đã được sử dụng cho vật tư này';

                            // Remove existing error message if any
                            const existingError = this.parentElement.querySelector('.text-red-500');
                            if (existingError) {
                                existingError.remove();
                            }

                            this.parentElement.appendChild(errorDiv);

                            // Remove error message after 3 seconds
                            setTimeout(() => {
                                errorDiv.remove();
                            }, 3000);

                            return;
                        }

                        // Remove existing error message if any
                        const existingError = this.parentElement.querySelector('.text-red-500');
                        if (existingError) {
                            existingError.remove();
                        }

                        component.serial = selectedSerial;

                        if (selectedOption.dataset.serialId) {
                            component.serial_id = selectedOption.dataset.serialId;
                            serialIdInput.value = selectedOption.dataset.serialId;
                        } else {
                            component.serial_id = '';
                            serialIdInput.value = '';
                        }

                        // Reload all other serial dropdowns to hide selected serials
                        setTimeout(() => {
                            reloadAllComponentSerials(this);
                        }, 100);
                    });

                    // Add event listener for warehouse change
                    const warehouseSelect = cell.parentElement.cells[4].querySelector('.warehouse-select');
                    if (warehouseSelect) {
                        // Remove existing event listener if any
                        const oldListener = warehouseSelect._serialChangeListener;
                        if (oldListener) {
                            warehouseSelect.removeEventListener('change', oldListener);
                        }

                        // Create new event listener
                        const listener = function() {
                            component.warehouseId = this.value;
                            // Clear and reload serials when warehouse changes
                            loadSerialsForSelect(selectElement, serialIdInput, component, index);
                        };
                        warehouseSelect._serialChangeListener = listener;
                        warehouseSelect.addEventListener('change', listener);
                    }

                    serialContainer.appendChild(selectElement);
                    serialContainer.appendChild(serialIdInput);
                    cell.appendChild(serialContainer);
                } else {
                    // If quantity > 1, show multiple serial dropdowns
                    // Ensure serials array exists and has correct length
                    if (!component.serials) component.serials = [];
                    if (!component.serial_ids) component.serial_ids = [];

                    for (let i = 0; i < quantity; i++) {
                        const serialDiv = document.createElement('div');
                        serialDiv.className = 'mb-1 relative';

                        // Create select dropdown for each serial
                        const selectElement = document.createElement('select');
                        selectElement.name = 'components[' + index + '][serials][]';
                        selectElement.className =
                            'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 material-serial-select';
                        selectElement.setAttribute('data-material-id', component.id);
                        selectElement.setAttribute('data-component-index', index);
                        selectElement.setAttribute('data-serial-index', i);
                        selectElement.setAttribute('data-instance-id', componentInstanceId);
                        selectElement.setAttribute('data-product-id', component.actualProductId);
                        selectElement.setAttribute('data-product-unit', component.productUnit || 0);
                        selectElement.id = componentInstanceId + '_select_' + i;

                        // Add default option
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = 'Chọn serial ' + (i + 1) + ' (tùy chọn)';
                        selectElement.appendChild(defaultOption);

                        // Hidden input for serial_id
                        const serialIdInput = document.createElement('input');
                        serialIdInput.type = 'hidden';
                        serialIdInput.name = 'components[' + index + '][serial_id][]';
                        serialIdInput.value = (component.serial_ids && component.serial_ids[i]) || '';

                        // Load serials when warehouse is selected
                        loadSerialsForMultipleSelect(selectElement, serialIdInput, component, index, i);

                        // Add event listener for warehouse change
                        const warehouseSelect = cell.parentElement.cells[4].querySelector('.warehouse-select');
                        if (warehouseSelect) {
                            // Remove existing event listener if any
                            const oldListener = warehouseSelect._serialChangeListener;
                            if (oldListener) {
                                warehouseSelect.removeEventListener('change', oldListener);
                            }

                            // Create new event listener
                            const listener = function() {
                                component.warehouseId = this.value;
                                // Clear and reload serials when warehouse changes
                                loadSerialsForMultipleSelect(selectElement, serialIdInput, component, index, i);
                            };
                            warehouseSelect._serialChangeListener = listener;
                            warehouseSelect.addEventListener('change', listener);
                        }

                        // Add event listener for select change
                        selectElement.addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            const selectedSerial = this.value;
                            const currentMaterialId = this.getAttribute('data-material-id');

                            // Check if this serial is already selected in other dropdowns of the same material
                            const isSerialUsed = selectedSerial && (
                                // Check in other serials of this component
                                (component.serials && component.serials.some((serial, idx) =>
                                    idx !== i && serial === selectedSerial
                                )) ||
                                // Check in other components of the same material
                                selectedComponents.some(comp =>
                                    comp.id === component.id && // Same material
                                    comp !== component && // Different component instance
                                    ((comp.serial === selectedSerial) || // Check single serial
                                        (comp.serials && comp.serials.includes(selectedSerial))
                                        ) // Check multiple serials
                                )
                            );

                            if (isSerialUsed) {
                                // Reset selection
                                this.value = '';
                                if (!component.serials) component.serials = [];
                                component.serials[i] = '';
                                if (!component.serial_ids) component.serial_ids = [];
                                component.serial_ids[i] = '';
                                serialIdInput.value = '';

                                // Show error message
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'text-red-500 text-xs mt-1';
                                errorDiv.textContent = 'Serial này đã được sử dụng cho vật tư này';

                                // Remove existing error message if any
                                const existingError = this.parentElement.querySelector('.text-red-500');
                                if (existingError) {
                                    existingError.remove();
                                }

                                this.parentElement.appendChild(errorDiv);

                                // Remove error message after 3 seconds
                                setTimeout(() => {
                                    errorDiv.remove();
                                }, 3000);

                                return;
                            }

                            // Remove existing error message if any
                            const existingError = this.parentElement.querySelector('.text-red-500');
                            if (existingError) {
                                existingError.remove();
                            }

                            // Update component's serials array
                            if (!component.serials) component.serials = [];
                            component.serials[i] = selectedSerial;

                            // Update component's serial_ids array
                            if (selectedOption.dataset.serialId) {
                                if (!component.serial_ids) component.serial_ids = [];
                                component.serial_ids[i] = selectedOption.dataset.serialId;
                                serialIdInput.value = selectedOption.dataset.serialId;
                            } else {
                                if (!component.serial_ids) component.serial_ids = [];
                                component.serial_ids[i] = '';
                                serialIdInput.value = '';
                            }

                            // Reload all other serial dropdowns to hide selected serials
                            setTimeout(() => {
                                reloadAllComponentSerials(this);
                            }, 100);
                        });

                        serialDiv.appendChild(selectElement);
                        serialDiv.appendChild(serialIdInput);
                        cell.appendChild(serialDiv);
                    }

                    if (quantity > 3) {
                        const note = document.createElement('div');
                        note.className = 'text-xs text-gray-500 mt-1';
                        note.textContent = quantity + ' serials';
                        cell.appendChild(note);
                    }
                }
            }

            // Function to check if any components have modified quantities for a product
            function checkComponentsModified(productUniqueId) {
                console.log(`Checking if components modified for product ${productUniqueId}`);

                const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                
                // If using existing product, don't consider it modified
                if (product && product.isUsingExisting) {
                    console.log(`Product ${productUniqueId} is using existing product, not modified`);
                    return false;
                }
                
                // If product has components from existing product, don't consider it modified
                const hasExistingComponents = selectedComponents.some(c => 
                    c.productId === productUniqueId && c.isFromExistingProduct
                );
                if (hasExistingComponents) {
                    console.log(`Product ${productUniqueId} has existing components, not modified`);
                    return false;
                }
                
                if (!product || !product.originalComponents) {
                    console.log('No original components found, using fallback logic');
                    // Fallback to old logic if no original components available
                    const productComponents = selectedComponents.filter(c => c.productId === productUniqueId && c
                        .productUnit === 0);
                    const modified = productComponents.some(component => {
                        const originalComponent = product.originalComponents?.find(o => o.id == component
                            .id);
                        return originalComponent && originalComponent.quantity !== component.quantity;
                    });
                    console.log(`Using fallback logic: ${modified ? 'MODIFIED' : 'not modified'}`);
                    return modified;
                }

                const originalComponents = product.originalComponents;
                const productQty = parseInt(product.quantity) || 1;

                // Check if product quantity changed from original (if we have original quantity stored)
                if (product.originalQuantity && product.originalQuantity !== product.quantity) {
                    console.log(
                        `Product quantity changed from ${product.originalQuantity} to ${product.quantity}, marking as modified`
                        );
                    return true;
                }

                // Check each unit separately
                for (let unitIndex = 0; unitIndex < productQty; unitIndex++) {
                    const unitComponents = selectedComponents.filter(c => c.productId === productUniqueId && c
                        .productUnit === unitIndex);

                    console.log(`Checking unit ${unitIndex}:`, {
                        currentCount: unitComponents.length,
                        originalCount: originalComponents.length,
                        current: unitComponents.map(c => ({
                            id: c.id,
                            name: c.name,
                            quantity: c.quantity,
                            isFromProduct: c.isFromProduct
                        })),
                        original: originalComponents.map(o => ({
                            id: o.id,
                            name: o.name,
                            quantity: o.quantity
                        }))
                    });

                    // Check if number of components changed
                    if (unitComponents.length !== originalComponents.length) {
                        console.log(`Unit ${unitIndex}: Component count changed, marking as modified`);
                        return true;
                    }

                    // Check each component for changes
                    for (let current of unitComponents) {
                        const original = originalComponents.find(o => o.id == current.id);
                        if (!original) {
                            // New component added
                            console.log(`Unit ${unitIndex}: New component added: ${current.id}`);
                            return true;
                        } else if (original.quantity != current.quantity) {
                            // Quantity changed
                            console.log(
                                `Unit ${unitIndex}: Component ${current.id} quantity changed: ${original.quantity} -> ${current.quantity}`
                                );
                            return true;
                        }
                    }
                }

                console.log('No modifications detected in any unit');
                return false;
            }

            // Function to add the "Create New Product" button
            function addCreateNewProductButton(componentBlock, productId, productUniqueId) {
                // Remove existing buttons if any
                const existingSections = componentBlock.querySelectorAll('.duplicate-section');
                existingSections.forEach(section => section.remove());

                // Find all unit tables and add button for each unit that has modifications
                const tables = componentBlock.querySelectorAll('table');
                const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                const originalComponents = product?.originalComponents || [];

                tables.forEach((table, unitIndex) => {
                    // Check if this unit has modifications
                    const unitComponents = selectedComponents.filter(c => c.productId === productUniqueId &&
                        c.productUnit === unitIndex);
                    let hasModifications = false;

                    // Don't show banner if using existing product
                    const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                    if (product && product.isUsingExisting) {
                        console.log(`Product ${productUniqueId} is using existing product, skipping banner for unit ${unitIndex}`);
                        return;
                    }

                    // Check if number of components changed
                    if (unitComponents.length !== originalComponents.length) {
                        hasModifications = true;
                    } else {
                        // Check each component for changes
                        for (let current of unitComponents) {
                            const original = originalComponents.find(o => o.id == current.id);
                            if (!original || original.quantity != current.quantity) {
                                hasModifications = true;
                                break;
                            }
                        }
                    }

                    // Only add button if this unit has modifications
                    if (hasModifications) {
                        const duplicateSection = document.createElement('div');
                        duplicateSection.className =
                            'bg-yellow-50 border-t border-yellow-200 p-3 duplicate-section';
                        duplicateSection.setAttribute('data-product-id', productUniqueId);
                        duplicateSection.setAttribute('data-unit-index', unitIndex);
                        duplicateSection.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-yellow-700">
                            <i class="fas fa-info-circle mr-2"></i>
                                    Đơn vị ${unitIndex + 1}: Bạn đã thay đổi công thức gốc. Bạn có thể tạo một thành phẩm mới với công thức này.
                        </div>
                        <button type="button" class="create-new-product-btn bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm"
                                        data-product-id="${productId}" data-unique-id="${productUniqueId}" data-unit-index="${unitIndex}">
                            <i class="fas fa-plus-circle mr-1"></i> Tạo thành phẩm mới
                        </button>
                    </div>
                `;

                        // Insert after this specific table
                        table.parentNode.insertBefore(duplicateSection, table.nextSibling);
                    }
                });

                // Add event listeners to all create new product buttons
                setTimeout(() => {
                    const createNewBtns = componentBlock.querySelectorAll('.create-new-product-btn');
                    createNewBtns.forEach(btn => {
                        // Xóa tất cả event listener cũ để tránh trùng lặp
                        const newBtn = btn.cloneNode(true);
                        btn.parentNode.replaceChild(newBtn, btn);

                        // Thêm event listener mới
                        newBtn.addEventListener('click', function(e) {
                            e.preventDefault(); // Ngăn chặn hành vi mặc định
                            e.stopPropagation(); // Ngăn chặn event bubbling

                            const productUniqueId = this.getAttribute('data-unique-id');
                            const productId = this.getAttribute('data-product-id');
                            const unitIndex = this.getAttribute('data-unit-index');
                            console.log('Create new product button clicked:', {
                                productId,
                                productUniqueId,
                                unitIndex
                            });
                            showCreateNewProductModal(productUniqueId, unitIndex);
                        });
                    });
                }, 100);
            }

            // Function to check and show create new product button
            function checkAndShowCreateNewProductButton(productUniqueId) {
                const isModified = checkComponentsModified(productUniqueId);
                const isDuplicate = selectedProducts.filter(p => p.uniqueId === productUniqueId).length > 1;
                const isUsingExisting = selectedProducts.find(p => p.uniqueId === productUniqueId)?.isUsingExisting;

                console.log(`Product ${productUniqueId} status:`, {
                    isModified,
                    isDuplicate,
                    isUsingExisting
                });

                // Don't show create new product button if using existing product
                if (isUsingExisting) {
                    console.log(`Product ${productUniqueId} is using existing product, hiding create new button`);
                    const componentBlock = document.getElementById('component_block_' + productUniqueId);
                    if (componentBlock) {
                        const existingSections = componentBlock.querySelectorAll('.duplicate-section');
                        existingSections.forEach(section => {
                            console.log(
                                `Removing create new product button for ${productUniqueId} (using existing)`
                                );
                            section.remove();
                        });
                    }
                    return;
                }

                if (isModified || isDuplicate) {
                    // Find the product object to get its actual ID
                    const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                    if (!product) {
                        console.error(`Product not found for uniqueId ${productUniqueId}`);
                        return;
                    }

                    // Use the actual product ID, not just the number from the uniqueId
                    const productId = product.id;
                    console.log(
                        `Adding create new product button for product ${productId} (uniqueId: ${productUniqueId})`
                    );

                    // Find the component block for this product
                    const componentBlock = document.getElementById('component_block_' + productUniqueId);
                    if (componentBlock) {
                        console.log(`Adding button to component block for product ${productUniqueId}`);
                        addCreateNewProductButton(componentBlock, productId, productUniqueId);
                    } else {
                        console.log(`Component block not found for ${productUniqueId}`);
                    }
                } else {
                    // Remove the button if no longer modified and not duplicate
                    const componentBlock = document.getElementById('component_block_' + productUniqueId);
                    if (componentBlock) {
                        const existingSections = componentBlock.querySelectorAll('.duplicate-section');
                        existingSections.forEach(section => {
                            console.log(`Removing create new product button for ${productUniqueId}`);
                            section.remove();
                        });
                    }
                }
            }

            // Function to show create new product modal/alert
            function showCreateNewProductModal(productUniqueId, unitIndex = null) {
                console.log('showCreateNewProductModal called with:', {
                    productUniqueId,
                    unitIndex
                });
                console.log('Current selectedComponents state:', {
                    total: selectedComponents.length,
                    components: selectedComponents.map(c => ({
                        id: c.id,
                        name: c.name,
                        productId: c.productId,
                        productUnit: c.productUnit,
                        quantity: c.quantity
                    }))
                });

                // Kiểm tra nếu đang xử lý yêu cầu tạo thành phẩm
                // Sử dụng window.isCreatingProduct để truy cập biến từ file assembly-product-unit.js
                if (window.isCreatingProduct === true) {
                    console.log('Đang xử lý yêu cầu tạo thành phẩm, vui lòng đợi...');
                    Swal.fire({
                        icon: 'info',
                        title: 'Đang xử lý',
                        text: 'Yêu cầu tạo thành phẩm đang được xử lý, vui lòng đợi.',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                // Chỉ lấy components của đơn vị cụ thể nếu unitIndex được cung cấp
                const productComponents = unitIndex !== null ?
                    selectedComponents.filter(c => c.productId === productUniqueId && c.productUnit === parseInt(
                        unitIndex)) :
                    selectedComponents.filter(c => c.productId === productUniqueId);

                if (!product || productComponents.length === 0) return;

                // Debug: Log all components for this product to check state
                console.log('All components for product:', {
                    productUniqueId,
                    unitIndex,
                    allComponents: selectedComponents.filter(c => c.productId === productUniqueId),
                    filteredComponents: productComponents,
                    totalSelectedComponents: selectedComponents.length
                });

                // Debug: Check if there are any components with wrong unit
                const wrongUnitComponents = selectedComponents.filter(c =>
                    c.productId === productUniqueId &&
                    c.productUnit !== parseInt(unitIndex) &&
                    c.productUnit !== undefined
                );
                if (wrongUnitComponents.length > 0) {
                    console.log('WARNING: Found components with wrong unit:', wrongUnitComponents);
                }

                // Create a summary of the modified formula
                const unitText = unitIndex !== null ? ` (Đơn vị ${parseInt(unitIndex) + 1})` : '';
                let formulaSummary = `Công thức mới${unitText}:\n`;
                productComponents.forEach(comp => {
                    const isModified = comp.quantity !== comp.originalQuantity;
                    const status = isModified ? ` (đã thay đổi từ ${comp.originalQuantity})` : '';
                    formulaSummary += `- ${comp.name}: ${comp.quantity}${status}\n`;
                });

                console.log('Components for formula check:', {
                    productUniqueId,
                    unitIndex,
                    totalComponents: selectedComponents.filter(c => c.productId === productUniqueId).length,
                    unitComponents: productComponents.length,
                    components: productComponents
                });

                // Check if formula already exists before showing confirmation
                checkFormulaExists(productUniqueId, productComponents).then((exists) => {
                    console.log('Formula check result:', exists);
                    if (exists) {
                        console.log('Formula exists, showing confirmation dialog');
                        // Formula exists, show confirmation with options
                        Swal.fire({
                            title: `Công thức đã tồn tại${unitText}`,
                            html: `Đã tồn tại thành phẩm với công thức này.<br><br>Bạn muốn:<br><br><pre style="text-align:left;background:#f5f5f5;padding:10px;max-height:200px;overflow-y:auto">${formulaSummary}</pre>`,
                            icon: 'warning',
                            showCancelButton: true,
                            showDenyButton: true,
                            confirmButtonText: 'Tạo thành phẩm mới',
                            denyButtonText: 'Lắp ráp thành phẩm đã tồn tại',
                            cancelButtonText: 'Hủy',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Create new product with existing formula
                                const componentBlock = document.getElementById('component_block_' +
                                    productUniqueId);
                                const createNewBtn = componentBlock ? componentBlock.querySelector(
                                        `.create-new-product-btn[data-unit-index="${unitIndex}"]`) :
                                    null;
                                if (createNewBtn && typeof window.handleCreateNewProduct ===
                                    "function") {
                                    window.handleCreateNewProduct(createNewBtn);
                                }
                            } else if (result.isDenied) {
                                // Show list of existing products to choose from
                                showExistingProductsList(productUniqueId, unitIndex);
                            }
                        });
                    } else {
                        console.log('Formula does not exist, showing normal confirmation');
                        // Formula doesn't exist, show normal confirmation
                        Swal.fire({
                            title: `Xác nhận tạo thành phẩm mới${unitText}`,
                            html: `Bạn có muốn tạo thành phẩm mới <strong>"${product.name} (Modified)"</strong> với công thức sau?<br><br><pre style="text-align:left;background:#f5f5f5;padding:10px;max-height:200px;overflow-y:auto">${formulaSummary}</pre>`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Tạo thành phẩm',
                            cancelButtonText: 'Hủy',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const componentBlock = document.getElementById('component_block_' +
                                    productUniqueId);
                                const createNewBtn = componentBlock ? componentBlock.querySelector(
                                        `.create-new-product-btn[data-unit-index="${unitIndex}"]`) :
                                    null;
                                if (createNewBtn && typeof window.handleCreateNewProduct ===
                                    "function") {
                                    window.handleCreateNewProduct(createNewBtn);
                                }
                            }
                        });
                    }
                });
            }

            // Function to check if formula already exists
            async function checkFormulaExists(productUniqueId, productComponents) {
                try {
                    console.log('Checking formula exists for:', {
                        productUniqueId,
                        productComponents
                    });

                    // Create formula data for comparison
                    const formulaData = productComponents.map(comp => ({
                        material_id: comp.id,
                        quantity: comp.quantity
                    }));

                    console.log('Formula data to check:', formulaData);
                    console.log('Formula data details:', formulaData.map(item => ({
                        material_id: item.material_id,
                        quantity: item.quantity,
                        type: typeof item.material_id
                    })));

                    console.log('Sending request to check formula:', {
                        url: '{{ route('assemblies.check-formula') }}',
                        formula: formulaData
                    });

                    const response = await fetch('{{ route('assemblies.check-formula') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            formula: formulaData
                        })
                    });

                    console.log('Response status:', response.status);
                    console.log('Response ok:', response.ok);

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    console.log('Formula check response:', data);
                    return data.exists ? data.product : false;
                } catch (error) {
                    console.error('Error checking formula:', error);
                    return false;
                }
            }



            // Function to show list of existing products to choose from
            async function showExistingProductsList(productUniqueId, unitIndex = null) {
                try {
                    // Get current product components to check formula
                    const productComponents = selectedComponents.filter(c => c.productId === productUniqueId);
                    const formulaData = productComponents.map(comp => ({
                        material_id: comp.id,
                        quantity: comp.quantity
                    }));

                    // Get all existing products with this formula
                    const response = await fetch('{{ route('assemblies.check-formula') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            formula: formulaData,
                            get_all: true // Request to get all matching products
                        })
                    });

                    const result = await response.json();

                    if (result.exists && result.products && result.products.length > 0) {
                        // Create options for select
                        const options = result.products.map(product =>
                            `<option value="${product.id}">${product.code} - ${product.name}</option>`
                        ).join('');

                        // Show dialog to select existing product
                        const {
                            value: selectedProductId
                        } = await Swal.fire({
                            title: 'Chọn thành phẩm đã tồn tại',
                            html: `
                                <p>Chọn thành phẩm đã tồn tại để sử dụng:</p>
                                <select id="existing-product-select" class="swal2-select">
                                    ${options}
                                </select>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Chọn',
                            cancelButtonText: 'Hủy',
                            preConfirm: () => {
                                const select = document.getElementById('existing-product-select');
                                return select.value;
                            }
                        });

                        if (selectedProductId) {
                            const selectedProduct = result.products.find(p => p.id == selectedProductId);
                            if (selectedProduct) {
                                useExistingProduct(productUniqueId, selectedProduct, unitIndex);
                            }
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi',
                            text: 'Không tìm thấy thành phẩm đã tồn tại',
                            confirmButtonText: 'Đóng'
                        });
                    }
                } catch (error) {
                    console.error('Error showing existing products list:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Có lỗi xảy ra khi tải danh sách thành phẩm',
                        confirmButtonText: 'Đóng'
                    });
                }
            }

            // Function to use existing product for assembly
            async function useExistingProduct(productUniqueId, existingProduct, unitIndex = null) {
                console.log('Using existing product for assembly:', existingProduct);

                try {
                    // Load materials for the existing product
                    const response = await fetch(`/assemblies/product-materials/${existingProduct.id}`);
                    const result = await response.json();
                    console.log('API response for product materials:', result);

                    if (result.success && result.materials) {
                        // Find the current product and keep its quantity and uniqueId
                const currentProduct = selectedProducts.find(p => p.uniqueId === productUniqueId);
                        if (!currentProduct) throw new Error('Current product not found');

                        // Clear all components for this product (simulate delete-components)
                        console.log('Before clearing components for productUniqueId:', productUniqueId);
                        console.log('Current selectedComponents:', selectedComponents.map(c => ({
                            id: c.id,
                            productId: c.productId,
                            productUnit: c.productUnit
                        })));
                        
                        selectedComponents = selectedComponents.filter(c => c.productId !== productUniqueId);
                        
                        console.log('After clearing components for productUniqueId:', productUniqueId);
                        console.log('Remaining selectedComponents:', selectedComponents.map(c => ({
                            id: c.id,
                            productId: c.productId,
                            productUnit: c.productUnit
                        })));

                        // Update product meta to the chosen existing one, keep uniqueId
                    currentProduct.id = existingProduct.id;
                    currentProduct.name = existingProduct.name;
                    currentProduct.code = existingProduct.code;
                        currentProduct.isUsingExisting = true;

                        // Build base components from API and replicate for all units
                        const baseComponents = result.materials.map(material => ({
                            id: material.id,
                            code: material.code || '',
                            name: material.name,
                            category: material.category || '',
                            unit: material.unit || '',
                            quantity: material.quantity || 1,
                            originalQuantity: material.quantity || 1,
                            notes: material.notes || '',
                            serial: '',
                            serials: [],
                            productId: productUniqueId,
                            actualProductId: currentProduct.id,
                            isFromProduct: true,
                            isOriginal: true,
                            isFromExistingProduct: true,
                            productUnit: 0,
                            unitLabel: ''
                        }));

                        // Persist base components on the product for later quantity changes
                        // Get default warehouse if available
                        const defaultWarehouseId = document.getElementById('default_warehouse_id')?.value || '';
                        currentProduct.baseComponents = baseComponents.map(c => ({ 
                            ...c, 
                            warehouseId: defaultWarehouseId || c.warehouseId || '' 
                        }));

                        const productQty = parseInt(currentProduct.quantity) || 1;
                        console.log('Adding components for productQty:', productQty, 'baseComponents:', baseComponents.length);
                        
                        for (let unit = 0; unit < productQty; unit++) {
                            baseComponents.forEach(base => {
                                // Get default warehouse if available
                                const defaultWarehouseId = document.getElementById('default_warehouse_id')?.value || '';
                                const cloned = { 
                                    ...base, 
                                    productUnit: unit, 
                                    unitLabel: unit > 0 ? `Đơn vị ${unit + 1}` : '',
                                    warehouseId: defaultWarehouseId || base.warehouseId || ''
                                };
                                selectedComponents.push(cloned);
                                console.log('Added component:', {
                                    id: cloned.id,
                                    name: cloned.name,
                                    productId: cloned.productId,
                                    productUnit: cloned.productUnit,
                                    isFromExistingProduct: cloned.isFromExistingProduct
                                });
                            });
                        }
                        
                        console.log('Final selectedComponents count:', selectedComponents.length);

                        // Update UI header and state on the existing block
                    const componentBlock = document.getElementById('component_block_' + productUniqueId);
                    if (componentBlock) {
                        const header = componentBlock.querySelector('h4');
                        if (header) {
                            header.innerHTML = `Linh kiện cho thành phẩm: ${existingProduct.name} (Đã tồn tại)`;
                        }
                        componentBlock.classList.add('using-existing-product');

                            const createNewBtn = componentBlock.querySelector('.create-new-product-btn');
                            if (createNewBtn) createNewBtn.style.display = 'none';
                            const modifiedBanner = componentBlock.querySelector('.modified-formula-banner');
                            if (modifiedBanner) modifiedBanner.style.display = 'none';
                        }

                        console.log('After updating product data:', {
                            currentProduct: currentProduct,
                            selectedComponentsCount: selectedComponents.length,
                            componentsForThisProduct: selectedComponents.filter(c => c.productId === productUniqueId).length
                        });

                        // Re-render components for this product only
                        updateProductComponentList(productUniqueId);

                        // Update the product list display
                        updateProductList();
                        
                        // Force re-render the component list to show new materials
                        setTimeout(() => {
                            console.log('Force re-rendering component list...');
                            
                            // Clear the component block first
                            const componentBlock = document.getElementById('component_block_' + productUniqueId);
                            if (componentBlock) {
                                const tableContainer = componentBlock.querySelector('.overflow-x-auto');
                                if (tableContainer) {
                                    tableContainer.innerHTML = '';
                                    console.log('Cleared component block content');
                                }
                            }
                            
                            // Force re-render
                            updateProductComponentList(productUniqueId);
                            
                            console.log('After force re-render - selectedComponents:', selectedComponents.filter(c => c.productId === productUniqueId).map(c => ({
                                id: c.id,
                                name: c.name,
                                isFromExistingProduct: c.isFromExistingProduct
                            })));
                        }, 100);

                        console.log('After re-rendering:', {
                            selectedComponentsCount: selectedComponents.length,
                            componentsForThisProduct: selectedComponents.filter(c => c.productId === productUniqueId).length
                        });

                        // Apply restrictions (read-only) for existing-product components
                        applyExistingProductRestrictions();

                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công',
                            text: `Đã chuyển sang sử dụng thành phẩm đã tồn tại: ${existingProduct.name}`,
                            confirmButtonText: 'Đóng'
                        });
                    } else {
                        throw new Error('Failed to load product materials');
                    }
                } catch (error) {
                    console.error('Error loading existing product materials:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Không thể tải vật tư của thành phẩm đã tồn tại',
                        confirmButtonText: 'Đóng'
                    });
                }
            }

            // Function to update component display
            function updateComponentDisplay() {
                // Trigger any existing display update logic
                if (typeof updateComponentList === 'function') {
                    updateComponentList();
                }

                // Update component count display
                const componentCount = selectedComponents.length;
                const countElement = document.getElementById('component-count');
                if (countElement) {
                    countElement.textContent = componentCount;
                }

                // Apply editing restrictions for existing products
                applyExistingProductRestrictions();

                // Update any other UI elements that depend on selectedComponents
                console.log('Component display updated, total components:', componentCount);
            }

            // Function to apply restrictions for components from existing products
            function applyExistingProductRestrictions() {
                selectedProducts.forEach(product => {
                    if (product.isUsingExisting) {
                        const componentBlock = document.getElementById('component_block_' + product.uniqueId);
                        if (componentBlock) {
                            // Disable editing for components from existing product
                            const componentRows = componentBlock.querySelectorAll('tr[data-component-id]');
                            componentRows.forEach(row => {
                                const componentId = row.getAttribute('data-component-id');
                                const component = selectedComponents.find(c => c.id == componentId && c.isFromExistingProduct);
                                
                                if (component) {
                                    // Disable quantity input
                                    const quantityInput = row.querySelector('.component-quantity-input');
                                    if (quantityInput) {
                                        quantityInput.disabled = true;
                                        quantityInput.classList.add('bg-gray-100');
                                        quantityInput.setAttribute('readonly', 'readonly');
                                    }
                                    
                                    // Disable delete button
                                    const deleteBtn = row.querySelector('.delete-component-btn');
                                    if (deleteBtn) {
                                        deleteBtn.style.display = 'none';
                                    }
                                    
                                    // Add visual indicator
                                    row.classList.add('bg-blue-50');
                                    row.setAttribute('title', 'Vật tư từ thành phẩm đã tồn tại - Không thể sửa đổi');
                                }
                            });
                        }
                    }
                });
            }

            // Validation trước khi submit
            document.querySelector('form').addEventListener('submit', async function(e) {
                e.preventDefault(); // Prevent default submission

                // Validation các trường bắt buộc
                const assignedTo = document.getElementById('assigned_to').value;
                const testerId = document.getElementById('tester_id').value;
                const selectedProductsCount = selectedProducts.length;

                // Kiểm tra người phụ trách
                if (!assignedTo) {
                    showValidationError('Vui lòng chọn người phụ trách!', 'assigned_to_search');
                    return;
                }

                // Kiểm tra người tiếp nhận kiểm thử
                if (!testerId) {
                    showValidationError('Vui lòng chọn người tiếp nhận kiểm thử!', 'tester_id_search');
                    return;
                }

                // Kiểm tra thành phẩm
                if (selectedProductsCount === 0) {
                    showValidationError('Vui lòng thêm ít nhất một thành phẩm!', 'product_search');
                    return;
                }

                // Kiểm tra mục đích và dự án
                const purpose = document.getElementById('purpose').value;
                if (purpose === 'project') {
                    const projectId = document.getElementById('project_id').value;
                    if (!projectId) {
                        showValidationError('Vui lòng chọn dự án khi mục đích là "Xuất đi dự án"!', 'project_id');
                        return;
                    }
                }

                // Kiểm tra ngày lắp ráp
                const assemblyDate = document.getElementById('assembly_date').value;
                if (!assemblyDate) {
                    showValidationError('Vui lòng nhập ngày lắp ráp!', 'assembly_date');
                    return;
                }

                // Check if default warehouse is selected and apply it to all components if needed
                const defaultWarehouseId = document.getElementById('default_warehouse_id').value;
                if (defaultWarehouseId) {
                    const warehouseSelects = document.querySelectorAll('.warehouse-select');
                    let updatedCount = 0;
                    
                    warehouseSelects.forEach(select => {
                        if (!select.value) {
                            select.value = defaultWarehouseId;
                            // Update component data
                            const componentIndex = select.name.match(/components\[(\d+)\]\[warehouse_id\]/)?.[1];
                            if (componentIndex !== undefined) {
                                const component = selectedComponents[componentIndex];
                                if (component) {
                                    component.warehouseId = defaultWarehouseId;
                                }
                            }
                            updatedCount++;
                        }
                    });

                    if (updatedCount > 0) {
                        console.log(`Auto-applied default warehouse to ${updatedCount} components`);
                    }
                }

                // Loại bỏ name của toàn bộ input/select/textarea hiển thị thuộc components để không ghi đè hidden inputs
                const visibleComponentInputs = document.querySelectorAll('table input[name^="components["], table select[name^="components["], table textarea[name^="components["]');
                visibleComponentInputs.forEach(el => {
                    el.removeAttribute('name');
                });

                // Check for serial validation errors first
                const serialErrors = document.querySelectorAll('.serial-validation-msg');
                if (serialErrors.length > 0) {
                    alert('Vui lòng sửa các lỗi serial trước khi lưu phiếu lắp ráp!');
                    // Focus on first error
                    const firstErrorInput = serialErrors[0].parentNode.querySelector('input');
                    if (firstErrorInput) {
                        firstErrorInput.focus();
                    }
                    // Ngăn mọi listener khác tiếp tục xử lý submit
                    e.stopImmediatePropagation();
                    return;
                }

                // Check if we have components for all units
                const productUnits = new Set();
                selectedComponents.forEach(c => {
                    productUnits.add(c.productUnit);
                });

                // Check if we have components for each product
                selectedProducts.forEach(product => {
                    const productComponents = selectedComponents.filter(c => c.productId ===
                        product
                        .uniqueId);
                    console.log(`Product ${product.uniqueId} (${product.name}):`, {
                        quantity: product.quantity,
                        components: productComponents.length,
                        units: productComponents.map(c => c.productUnit)
                    });
                });

                // Đồng bộ hidden inputs
                updateHiddenComponentList();
                updateHiddenProductList();

                // Kiểm tra mã phiếu có hợp lệ không
                const assemblyCode = document.getElementById('assembly_code').value.trim();
                if (!assemblyCode) {
                    showValidationError('❌ Vui lòng nhập mã phiếu lắp ráp!', 'assembly_code');
                    return;
                }

                // Kiểm tra xem có đang hiển thị lỗi trùng mã không
                const hasCodeError = document.querySelector('.assembly-code-error');
                if (hasCodeError) {
                    showValidationError('❌ Mã phiếu đã tồn tại! Vui lòng thay đổi mã phiếu trước khi lưu.', 'assembly_code');
                    return;
                }

                // Kiểm tra số lượng thành phẩm phải hợp lệ
                for (let i = 0; i < selectedProducts.length; i++) {
                    const product = selectedProducts[i];
                    const quantity = parseInt(product.quantity);
                    if (!quantity || quantity < 1) {
                        showValidationError(`Số lượng thành phẩm "${product.name}" phải là số nguyên dương!`);
                        return;
                    }
                }

                // Kiểm tra xem có vật tư nào được chọn không
                if (selectedComponents.length === 0) {
                    showValidationError('Vui lòng thêm ít nhất một vật tư cho thành phẩm!');
                    return;
                }

                // Validate serials for each product (only check for duplicates if serials provided)
                let hasSerialError = false;
                let serialErrorDetails = '';

                selectedProducts.forEach((product, index) => {
                    // Check for duplicate serials within this product (only if serials are provided)
                    const serialValues = product.serials.filter(s => s && s.trim() !== '');

                    if (serialValues.length > 0) {
                        const uniqueSerials = new Set(serialValues);
                        if (serialValues.length !== uniqueSerials.size) {
                            hasSerialError = true;
                            serialErrorDetails += `\n- Thành phẩm "${product.name}": Có serial trùng lặp`;
                        }
                    }
                });

                if (hasSerialError) {
                    showValidationError('Phát hiện trùng lặp serial thành phẩm. Vui lòng kiểm tra lại!' + serialErrorDetails);
                    // Ngăn mọi listener khác tiếp tục xử lý submit
                    e.stopImmediatePropagation();
                    return;
                }

                // Server-side existence check for each entered product serial (defensive check before submit)
                let hasServerSerialError = false;
                let serverSerialErrorMsg = '';
                for (let pIndex = 0; pIndex < selectedProducts.length; pIndex++) {
                    const product = selectedProducts[pIndex];
                    const serialCell = document.getElementById(product.uniqueId + '_serials');
                    const inputs = serialCell ? serialCell.querySelectorAll('input[type="text"]') : [];
                    const serials = (product.serials || []).map(s => (s || '').trim());
                    for (let sIndex = 0; sIndex < serials.length; sIndex++) {
                        const s = serials[sIndex];
                        if (!s) continue;
                        try {
                            const result = await checkSerialExists(s, product.id);
                            if (result && result.exists) {
                                hasServerSerialError = true;
                                const message = result.message || 'Serial đã tồn tại trong cơ sở dữ liệu';
                                serverSerialErrorMsg = message;
                                // Always ensure inputs are present and decorated
                                if (serialCell) {
                                    // Rebuild inputs from the current product.serials to avoid any missing DOM
                                    generateProductSerialInputs(product, pIndex, serialCell);
                                    const regenInputs = serialCell.querySelectorAll('input[type="text"]');
                                    if (regenInputs[sIndex]) {
                                        showSerialValidation(regenInputs[sIndex], false, message);
                                        if (typeof regenInputs[sIndex].focus === 'function') {
                                            regenInputs[sIndex].focus();
                                        }
                                    }
                                }
                                break;
                            }
                        } catch (_) { /* ignore */ }
                    }
                    if (hasServerSerialError) break;
                }
                if (hasServerSerialError) {
                    showValidationError('❌ ' + (serverSerialErrorMsg || 'Serial thành phẩm đã tồn tại trong hệ thống'));
                    e.stopImmediatePropagation();
                    return;
                }

                // Kiểm tra xem có vật tư nào được chọn không (đã kiểm tra ở trên)

                // Kiểm tra số lượng vật tư phải hợp lệ
                for (const component of selectedComponents) {
                    // Kiểm tra số lượng phải lớn hơn 0
                    const quantity = parseInt(component.quantity);
                    if (!quantity || quantity < 1 || isNaN(quantity)) {
                        showValidationError(`Số lượng vật tư "${component.name}" phải là số nguyên dương!`);
                        return;
                    }
                }

                // Nếu tất cả validation đều pass, hiển thị thông báo và submit
                console.log('✅ Tất cả validation đều pass, chuẩn bị submit form...');
                
                // Hiển thị thông báo thành công
                const successNotification = document.createElement('div');
                successNotification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-md';
                successNotification.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span>✅ Tất cả thông tin đã hợp lệ! Đang lưu phiếu lắp ráp...</span>
                    </div>
                `;
                document.body.appendChild(successNotification);
                
                // Tự động ẩn sau 3 giây
                setTimeout(() => {
                    if (successNotification.parentNode) {
                        successNotification.parentNode.removeChild(successNotification);
                    }
                }, 3000);
                
                // Chuẩn bị hidden inputs cho serial vật tư rồi submit (chống double submit)
                if (window._assemblySubmitting) {
                    return;
                }
                window._assemblySubmitting = true;

                try {
                    if (typeof window.prepareComponentSerialHiddenInputs === 'function') {
                        window.prepareComponentSerialHiddenInputs();
                    }

                    const form = document.getElementById('assembly_form');
                    form.submit();
                } finally {
                    // Reset flag after a delay to avoid quick double clicks
                    setTimeout(() => { window._assemblySubmitting = false; }, 3000);
                }
            });

            // Add event listener to update product dropdown for components when products change
            addProductBtn.addEventListener('click', function() {
                setTimeout(() => {
                    updateComponentProductDropdown();
                    updateAddProductButtonState();
                }, 0);
            });

            // Initialize the component selection by updating the product dropdown
            updateComponentProductDropdown();
            
            // Initialize add product button state
            updateAddProductButtonState();

            // Event delegation for delete product buttons
            document.addEventListener('click', function(e) {
                if (e.target && (e.target.classList.contains('delete-product') ||
                        e.target.closest('.delete-product'))) {

                    e.preventDefault();
                    e.stopPropagation();

                    // Find the actual button (in case clicked on icon inside button)
                    const button = e.target.classList.contains('delete-product') ?
                        e.target : e.target.closest('.delete-product');

                    // Get the current index from the button's data-index attribute
                    const currentIndex = parseInt(button.getAttribute('data-index'));

                    if (isNaN(currentIndex) || currentIndex < 0 || currentIndex >= selectedProducts
                        .length) {
                        return;
                    }

                    const product = selectedProducts[currentIndex];
                    const productId = product.uniqueId;

                    // Count components for this product before deletion
                    const componentsForProduct = selectedComponents.filter(comp => comp.productId ===
                        productId);

                    // Remove all components for this product first
                    selectedComponents = selectedComponents.filter(comp => comp.productId !== productId);

                    // Remove the component block for this product
                    const componentBlock = document.getElementById('component_block_' + productId);
                    if (componentBlock) {
                        componentBlock.remove();
                    }

                    // Also remove any "Create New Product" buttons for this product
                    const duplicateSection = document.querySelector('.duplicate-section[data-product-id="' +
                        productId + '"]');
                    if (duplicateSection) {
                        duplicateSection.remove();
                    }

                    // Remove the product from the selected products array
                    selectedProducts.splice(currentIndex, 1);

                    // Update product list UI (this will regenerate data-index correctly)
                    updateProductList();

                    // Update empty state message visibility
                    if (selectedProducts.length === 0) {
                        const noProductsComponents = document.getElementById('no_products_components');
                        if (noProductsComponents) {
                            noProductsComponents.style.display = '';
                        }
                    }

                    // Update product dropdown for components
                    updateComponentProductDropdown();

                    // Update add product button state
                    updateAddProductButtonState();

                    // Update the component list to reflect changes
                    // Call updateProductComponentList for each remaining product to recreate the full structure
                    selectedProducts.forEach(product => {
                        updateProductComponentList(product.uniqueId);
                    });
                }
            });

            // Function to get all currently selected serials (excluding specific element)
            function getAllSelectedSerials(excludeElement = null) {
                const selectedSerials = new Set();

                // Get serials from all select dropdowns
                document.querySelectorAll('select[data-material-id]').forEach(selectElement => {
                    // Skip if this is the element we want to exclude
                    if (excludeElement && selectElement === excludeElement) {
                        return;
                    }

                    if (selectElement.value && selectElement.value !== '') {
                        selectedSerials.add(selectElement.value);
                    }
                });

                return selectedSerials;
            }

            // Cache for material serials to avoid repeated API calls
            let materialSerialsCache = {};

            // Debug function (can be removed in production)
            function debugSerialState() {
                console.group('Serial Dropdowns Debug');
                const dropdowns = document.querySelectorAll('select[data-material-id]');
                const materialGroups = {};

                dropdowns.forEach((dropdown, index) => {
                    const materialId = dropdown.getAttribute('data-material-id');
                    const instanceId = dropdown.getAttribute('data-instance-id');
                    const value = dropdown.value;

                    if (!materialGroups[materialId]) {
                        materialGroups[materialId] = [];
                    }

                    materialGroups[materialId].push({
                        index,
                        instanceId,
                        value,
                        optionCount: dropdown.options.length - 1, // Exclude default option
                        dropdown
                    });
                });

                Object.keys(materialGroups).forEach(materialId => {
                    console.group(`Material ID: ${materialId}`);
                    materialGroups[materialId].forEach(info => {
                        console.log(
                            `Instance ${info.instanceId}: Value="${info.value}", Options=${info.optionCount}`
                        );
                    });
                    console.groupEnd();
                });

                console.log('Cache:', materialSerialsCache);
                console.groupEnd();
            }

            // Add debug button in development (can be removed in production)
            if (window.location.hostname === 'localhost' || window.location.hostname.includes('127.0.0.1')) {
                document.addEventListener('DOMContentLoaded', function() {
                    const debugBtn = document.createElement('button');
                    debugBtn.textContent = 'Debug Serials';
                    debugBtn.type = 'button';
                    debugBtn.className =
                        'fixed bottom-4 right-4 bg-red-500 text-white px-3 py-2 rounded text-xs z-50';
                    debugBtn.onclick = debugSerialState;
                    document.body.appendChild(debugBtn);
                });
            }

            // Function to reload serials for all components (excluding specific element)
            function reloadAllComponentSerials(excludeElement = null) {
                // Group selects by material ID and product unit to process them together
                const selectsByMaterialAndUnit = {};

                document.querySelectorAll('.product-serial-select').forEach(selectElement => {
                    // Skip if this is the element we want to exclude
                    if (excludeElement && selectElement === excludeElement) {
                        return;
                    }

                    const materialId = selectElement.getAttribute('data-material-id');
                    const productUnit = selectElement.getAttribute('data-product-unit') || '0';
                    const key = `${materialId}_${productUnit}`;

                    if (!selectsByMaterialAndUnit[key]) {
                        selectsByMaterialAndUnit[key] = [];
                    }
                    selectsByMaterialAndUnit[key].push(selectElement);
                });

                // Process each material group by product unit
                Object.keys(selectsByMaterialAndUnit).forEach(key => {
                    const [materialId, productUnit] = key.split('_');
                    const selects = selectsByMaterialAndUnit[key];
                    reloadSerialsForMaterialGroup(materialId, selects, productUnit);
                });
            }

            // Function to reload serials for a group of selects with the same material and product unit
            async function reloadSerialsForMaterialGroup(materialId, selectElements, productUnit = '0') {
                const warehouseId = document.getElementById('warehouse_id').value;
                if (!warehouseId) {
                    return;
                }

                const cacheKey = `${materialId}_${warehouseId}_${productUnit}`;

                try {
                    let serials;

                    // Check cache first
                    if (materialSerialsCache[cacheKey]) {
                        serials = materialSerialsCache[cacheKey];
                    } else {
                        // Fetch from API
                        const response = await fetch('{{ route('assemblies.material-serials') }}?' +
                            new URLSearchParams({
                                material_id: materialId,
                                warehouse_id: warehouseId
                            }));

                        const data = await response.json();

                        if (data.success && data.serials) {
                            serials = data.serials;
                            // Cache the result for 30 seconds
                            materialSerialsCache[cacheKey] = serials;
                            setTimeout(() => {
                                delete materialSerialsCache[cacheKey];
                            }, 30000);
                        } else {
                            serials = [];
                        }
                    }

                    // Get all currently selected serials for this material
                    const selectedSerials = new Set();
                    selectElements.forEach(select => {
                        if (select.value && select.value !== '') {
                            selectedSerials.add(select.value);
                        }
                    });

                    // Update each select element
                    selectElements.forEach(selectElement => {
                        const currentValue = selectElement.value;
                        const componentIndex = selectElement.getAttribute('data-component-index');
                        const serialIndex = selectElement.getAttribute('data-serial-index');

                        // Clear existing options except the first default one
                        while (selectElement.children.length > 1) {
                            selectElement.removeChild(selectElement.lastChild);
                        }

                        // Add serial options
                        serials.forEach(serial => {
                            // Skip this serial if it's already selected in another dropdown of the same material
                            // BUT allow it if it's the current value of this dropdown
                            if (selectedSerials.has(serial.serial_number) && serial
                                .serial_number !== currentValue) {
                                return;
                            }

                            const option = document.createElement('option');
                            option.value = serial.serial_number;
                            option.textContent = serial.serial_number;
                            option.dataset.serialId = serial.id;

                            // Restore selected value if it matches
                            if (currentValue === serial.serial_number) {
                                option.selected = true;
                            }

                            selectElement.appendChild(option);
                        });

                        // If no serials available, show message
                        if (serials.length === 0) {
                            const noSerialOption = document.createElement('option');
                            noSerialOption.textContent = 'Không có serial khả dụng';
                            noSerialOption.disabled = true;
                            selectElement.appendChild(noSerialOption);
                        }
                    });

                } catch (error) {
                    console.error('Error reloading serials for material', materialId, ':', error);
                }
            }

            // Function to load serials for multiple select (quantity > 1)
            async function loadSerialsForMultipleSelect(selectElement, serialIdInput, component, index,
                serialIndex) {
                if (!component.warehouseId) {
                    selectElement.innerHTML = '<option value="">Chọn serial (tùy chọn)</option>';
                    return;
                }

                // Add loading indicator
                selectElement.innerHTML = '<option value="">Đang tải...</option>';

                try {
                    const serials = await fetchMaterialSerials(component.id, component.warehouseId);

                    // Clear select and add default option
                    selectElement.innerHTML = '<option value="">Chọn serial (tùy chọn)</option>';

                    if (serials.length > 0) {
                        // Get currently selected serials from all dropdowns for this material (excluding this one)
                        const allSelectedSerials = new Set();
                        document.querySelectorAll(`select[data-material-id="${component.id}"]`).forEach(
                            otherSelect => {
                                if (otherSelect !== selectElement && otherSelect.value && otherSelect
                                    .value !== '') {
                                    allSelectedSerials.add(otherSelect.value);
                                }
                            });

                        // Add serial options
                        serials.forEach(serial => {
                            // Skip this serial if it's already selected in another dropdown
                            if (allSelectedSerials.has(serial.serial_number)) {
                                return; // Skip this serial
                            }

                            const option = document.createElement('option');
                            option.value = serial.serial_number;
                            option.textContent = serial.serial_number;
                            option.dataset.serialId = serial.id;

                            // Select this option if it matches component's current serial for this index
                            if ((component.serials && component.serials[serialIndex] === serial
                                    .serial_number) ||
                                (component.serial_ids && component.serial_ids[serialIndex] == serial.id)
                            ) {
                                option.selected = true;
                                selectElement.value = serial.serial_number;
                                if (!component.serials) component.serials = [];
                                if (!component.serial_ids) component.serial_ids = [];
                                component.serials[serialIndex] = serial.serial_number;
                                component.serial_ids[serialIndex] = serial.id;
                                serialIdInput.value = serial.id;
                            }

                            selectElement.appendChild(option);
                        });
                    } else {
                        const noSerialOption = document.createElement('option');
                        noSerialOption.textContent = 'Không có serial khả dụng';
                        noSerialOption.disabled = true;
                        selectElement.appendChild(noSerialOption);
                    }
                } catch (error) {
                    console.error('Error loading serials:', error);
                    selectElement.innerHTML = '<option value="">Lỗi tải serial</option>';
                }
            }

            // Function to load serials for select dropdown
            async function loadSerialsForSelect(selectElement, serialIdInput, component, index) {
                if (!component.warehouseId) {
                    selectElement.innerHTML = '<option value="">Chọn serial (tùy chọn)</option>';
                    return;
                }

                // Add loading indicator
                selectElement.innerHTML = '<option value="">Đang tải...</option>';

                try {
                    const serials = await fetchMaterialSerials(component.id, component.warehouseId);

                    // Clear select and add default option
                    selectElement.innerHTML = '<option value="">Chọn serial (tùy chọn)</option>';

                    if (serials.length > 0) {
                        // Get currently selected serials from all dropdowns for this material (excluding this one)
                        const allSelectedSerials = new Set();
                        document.querySelectorAll(`select[data-material-id="${component.id}"]`).forEach(
                            otherSelect => {
                                if (otherSelect !== selectElement && otherSelect.value && otherSelect
                                    .value !== '') {
                                    allSelectedSerials.add(otherSelect.value);
                                }
                            });

                        // Add serial options
                        serials.forEach(serial => {
                            // Skip this serial if it's already selected in another dropdown
                            if (allSelectedSerials.has(serial.serial_number)) {
                                return; // Skip this serial
                            }

                            const option = document.createElement('option');
                            option.value = serial.serial_number;
                            option.textContent = serial.serial_number;
                            option.dataset.serialId = serial.id;

                            // Select this option if it matches component's current serial
                            if (component.serial === serial.serial_number || component.serial_id ==
                                serial.id) {
                                option.selected = true;
                                selectElement.value = serial.serial_number;
                                component.serial = serial.serial_number;
                                component.serial_id = serial.id;
                                serialIdInput.value = serial.id;
                            }

                            selectElement.appendChild(option);
                        });
                    } else {
                        const noSerialOption = document.createElement('option');
                        noSerialOption.textContent = 'Không có serial khả dụng';
                        noSerialOption.disabled = true;
                        selectElement.appendChild(noSerialOption);
                    }
                } catch (error) {
                    console.error('Error loading serials:', error);
                    selectElement.innerHTML = '<option value="">Lỗi tải serial</option>';
                }
            }

            // Function to show serial dropdown
            async function showSerialDropdown(input, serialIdInput, component, index) {
                const warehouseId = getWarehouseId();
                if (!warehouseId) {
                    alert('Vui lòng chọn kho xuất trước!');
                    return;
                }

                try {
                    const response = await fetch('{{ route('assemblies.material-serials') }}?' +
                        new URLSearchParams({
                            material_id: component.id,
                            warehouse_id: warehouseId
                        }));

                    const data = await response.json();

                    if (!data.success) {
                        alert('Lỗi khi lấy danh sách serial: ' + data.message);
                        return;
                    }

                    if (data.serials.length === 0) {
                        alert('Không có serial nào khả dụng cho linh kiện này trong kho đã chọn');
                        return;
                    }

                    // Create dropdown modal
                    const modal = document.createElement('div');
                    modal.className =
                        'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';

                    const modalContent = document.createElement('div');
                    modalContent.className = 'bg-white rounded-lg p-6 w-96 max-h-96 overflow-y-auto';

                    const title = document.createElement('h3');
                    title.className = 'text-lg font-medium mb-4';
                    title.textContent = 'Chọn Serial';

                    const serialList = document.createElement('div');
                    serialList.className = 'space-y-2';

                    // Add serial options
                    data.serials.forEach(serial => {
                        const option = document.createElement('div');
                        option.className =
                            'p-2 border rounded cursor-pointer hover:bg-blue-50 hover:border-blue-300';
                        option.textContent = serial.serial_number;
                        option.addEventListener('click', function() {
                            input.value = serial.serial_number;
                            serialIdInput.value = serial.id;
                            component.serial = serial.serial_number;
                            component.serial_id = serial.id;
                            document.body.removeChild(modal);
                        });
                        serialList.appendChild(option);
                    });

                    const cancelBtn = document.createElement('button');
                    cancelBtn.className = 'mt-4 px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400';
                    cancelBtn.textContent = 'Hủy';
                    cancelBtn.addEventListener('click', function() {
                        document.body.removeChild(modal);
                    });

                    modalContent.appendChild(title);
                    modalContent.appendChild(serialList);
                    modalContent.appendChild(cancelBtn);
                    modal.appendChild(modalContent);

                    // Close modal when clicking outside
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            document.body.removeChild(modal);
                        }
                    });

                    document.body.appendChild(modal);

                } catch (error) {
                    console.error('Error fetching serials:', error);
                    alert('Lỗi khi lấy danh sách serial');
                }
            }

            // Function to check serial via API
            async function checkSerialExists(serial, productId, assemblyId = null) {
                if (!serial || !serial.trim() || !productId) {
                    return {
                        exists: false,
                        message: ''
                    };
                }

                try {
                    const response = await fetch('{{ route('api.check-serial') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            serial: serial.trim(),
                            product_id: productId,
                            assembly_id: assemblyId
                        })
                    });

                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error checking serial:', error);
                    return {
                        exists: false,
                        message: '',
                        error: true
                    };
                }
            }

            // Function to show serial validation message
            function showSerialValidation(input, isValid, message) {
                // Remove existing validation message
                const existingMsg = input.parentNode.querySelector('.serial-validation-msg');
                if (existingMsg) {
                    existingMsg.remove();
                }

                // Add validation styling
                if (isValid) {
                    input.classList.remove('border-red-500', 'bg-red-50');
                    input.classList.add('border-green-500');
                } else {
                    input.classList.remove('border-green-500');
                    input.classList.add('border-red-500', 'bg-red-50');

                    // Add error message
                    const msgDiv = document.createElement('div');
                    msgDiv.className = 'serial-validation-msg text-xs text-red-600 mt-1';
                    msgDiv.textContent = message;
                    input.parentNode.appendChild(msgDiv);
                }
            }

            // Function to add serial validation to product serial inputs
            function addProductSerialValidation(input, productId) {
                let validationTimeout;

                input.addEventListener('input', function() {
                    const serial = this.value.trim();

                    // Clear previous timeout
                    clearTimeout(validationTimeout);

                    // Reset styling
                    this.classList.remove('border-red-500', 'border-green-500', 'bg-red-50');

                    // Remove existing validation message
                    const existingMsg = this.parentNode.querySelector('.serial-validation-msg');
                    if (existingMsg) {
                        existingMsg.remove();
                    }

                    if (!serial) {
                        return; // No validation for empty serial
                    }

                    // Show loading state
                    this.classList.add('border-blue-300');

                    // Debounce validation
                    validationTimeout = setTimeout(async () => {
                        const result = await checkSerialExists(serial, productId);

                        this.classList.remove('border-blue-300');

                        if (result.error) {
                            showSerialValidation(this, false, 'Lỗi kiểm tra serial');
                            return;
                        }

                        if (result.exists) {
                            showSerialValidation(this, false, result.message);
                        } else {
                            showSerialValidation(this, true, '');
                        }
                    }, 500); // Wait 500ms after user stops typing
                });

                // Also check on blur
                input.addEventListener('blur', async function() {
                    const serial = this.value.trim();
                    if (!serial) return;

                    const result = await checkSerialExists(serial, productId);

                    if (result.error) {
                        showSerialValidation(this, false, 'Lỗi kiểm tra serial');
                        return;
                    }

                    if (result.exists) {
                        showSerialValidation(this, false, result.message);
                    } else {
                        showSerialValidation(this, true, '');
                    }
                });
            }

            // Enhanced function to add serial inputs with validation
            function addProductSerialInputsToCell(cell, product, productIndex) {
                // Clear existing content
                cell.innerHTML = '';

                const quantity = parseInt(product.quantity) || 1;

                // Initialize serials array if needed
                if (!product.serials) {
                    product.serials = new Array(quantity).fill('');
                }

                for (let i = 0; i < quantity; i++) {
                    const serialDiv = document.createElement('div');
                    serialDiv.className = 'mb-2 last:mb-0';

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = product.serials[i] || '';
                    input.placeholder = quantity > 1 ? `Serial ${i + 1} (tùy chọn)` : 'Serial (tùy chọn)';
                    input.className =
                        'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500';

                    // Add event listener to update product.serials array
                    input.addEventListener('input', function() {
                        product.serials[i] = this.value;
                        updateHiddenProductList();
                    });

                    // Add serial validation
                    addProductSerialValidation(input, product.id);

                    serialDiv.appendChild(input);
                    cell.appendChild(serialDiv);
                }
            }

            // Function to add validation to existing product serial inputs
            function addValidationToExistingProductSerials() {
                // Find all product serial inputs and add validation
                document.querySelectorAll('.product-serials-cell input[type="text"]').forEach(input => {
                    const row = input.closest('tr');
                    const productIdInput = row.querySelector('input[name*="[id]"]');
                    if (productIdInput && productIdInput.value) {
                        addProductSerialValidation(input, productIdInput.value);
                    }
                });
            }

            // Enhanced form validation to check for serial errors - REMOVED (duplicate validation)

            // Initialize validation for existing inputs when page loads
            setTimeout(addValidationToExistingProductSerials, 1000);

            // Check for success message from server and show SweetAlert2
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    html: `{!! session('success') !!}`,
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('assemblies.index') }}';
                    }
                });
            @endif

            // Check for error message from server and show SweetAlert2
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: '{{ session('error') }}',
                    confirmButtonText: 'OK'
                });
            @endif

            // Removed duplicate function - now using handleAddComponent instead

            // Function to add product unit selector to a cell
            window.addProductUnitSelector = function(cell, component, index) {
                // Clear existing content
                cell.innerHTML = '';

                // Get the product for this component
                const product = selectedProducts.find(p => p.uniqueId === component.productId);
                if (!product) return;

                const productQuantity = parseInt(product.quantity) || 1;

                // Create select element
                const selectElement = document.createElement('select');
                selectElement.name = `components[${index}][product_unit]`;
                selectElement.className =
                    'w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 product-unit-select';
                selectElement.setAttribute('data-component-index', index);
                selectElement.setAttribute('data-material-id', component.id);
                selectElement.setAttribute('data-product-id', component.actualProductId);
                selectElement.setAttribute('onchange', 'handleProductUnitChange(this)');

                // Add options for each product unit
                for (let i = 0; i < productQuantity; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `Đơn vị ${i + 1}`;

                    // Get product serial if available
                    const productSerials = product.serials || [];
                    if (productSerials[i]) {
                        option.textContent += ` (Serial: ${productSerials[i]})`;
                    }

                    // Select current product unit
                    if (i === (component.productUnit || 0)) {
                        option.selected = true;
                    }

                    selectElement.appendChild(option);
                }

                // Add event listener to update serials when product unit changes
                selectElement.addEventListener('change', function() {
                    const componentIndex = parseInt(this.getAttribute('data-component-index'));
                    const component = selectedComponents[componentIndex];
                    if (component) {
                        component.productUnit = parseInt(this.value) || 0;

                        // Update serial inputs for this component
                        const row = this.closest('tr');
                        if (row) {
                            const serialCell = row.querySelector('.serial-cell');
                            if (serialCell) {
                                addSerialInputsToCell(serialCell, component, componentIndex);
                            }
                        }
                    }
                });

                cell.appendChild(selectElement);

                // Add a visible badge showing the current unit
                const badge = document.createElement('div');
                badge.className =
                    'mt-2 inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800';
                badge.textContent = `Đơn vị ${parseInt(component.productUnit || 0) + 1}`;
                cell.appendChild(badge);

                // Add visual indicator of product unit (hidden since we now have a badge)
                const indicator = document.createElement('div');
                indicator.className =
                    `product-unit-indicator product-unit-${component.productUnit || 0}-bg mt-2 text-xs hidden`;
                indicator.innerHTML =
                    `<i class="fas fa-link mr-1"></i> Đơn vị ${parseInt(component.productUnit || 0) + 1}`;
                indicator.setAttribute('data-product-id', component.productId);

                // Get product serial if available
                const productSerials = product.serials || [];
                if (productSerials[component.productUnit || 0]) {
                    indicator.innerHTML +=
                        ` (Serial: <strong>${productSerials[component.productUnit || 0]}</strong>)`;
                }

                cell.appendChild(indicator);
            }

            // DUPLICATE FUNCTION REMOVED - This logic is already handled by updateProductComponentList

            // Add event listener for product quantity changes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('product-quantity-input')) {
                    const productRow = e.target.closest('tr');
                    const productId = productRow.getAttribute('data-product-id');
                    const productIndex = parseInt(e.target.getAttribute('data-index'));
                    const product = selectedProducts[productIndex];
                    const quantity = parseInt(e.target.value) || 1;

                    if (product) {
                        const oldQuantity = product.quantity;
                        // Update product quantity in our data model
                        product.quantity = quantity;

                        // Update selectedComponents to match new quantity
                        if (quantity > oldQuantity) {
                            // Add more components for new units
                            const existingComponents = selectedComponents.filter(c => c.productId ===
                                productId);
                            const componentsForFirstUnit = existingComponents.filter(c => c.productUnit ===
                                0);

                            for (let unitIndex = oldQuantity; unitIndex < quantity; unitIndex++) {
                                componentsForFirstUnit.forEach(originalComponent => {
                                    const newComponent = {
                                        ...originalComponent,
                                        productUnit: unitIndex,
                                        quantity: originalComponent.originalQuantity ||
                                            originalComponent.quantity,
                                        manuallyAdjusted: false
                                    };
                                    selectedComponents.push(newComponent);
                                });
                            }
                        } else if (quantity < oldQuantity) {
                            // Remove components for removed units
                            selectedComponents = selectedComponents.filter(c =>
                                !(c.productId === productId && c.productUnit >= quantity)
                            );
                        }

                        // Ensure we have components for all units (if product has original components)
                        if (product.originalComponents && product.originalComponents.length > 0) {
                            const existingComponents = selectedComponents.filter(c => c.productId ===
                                productId);
                            const existingUnitCounts = [...new Set(existingComponents.map(c => c
                                .productUnit))];

                            // Check if we need to add components for any missing units
                            for (let unitIndex = 0; unitIndex < quantity; unitIndex++) {
                                if (!existingUnitCounts.includes(unitIndex)) {
                                    product.originalComponents.forEach(originalComponent => {
                                        // Get default warehouse if available
                                        const defaultWarehouseId = document.getElementById('default_warehouse_id')?.value || '';
                                        
                                        const newComponent = {
                                            id: originalComponent.id,
                                            code: originalComponent.code,
                                            name: originalComponent.name,
                                            category: originalComponent.category,
                                            unit: originalComponent.unit,
                                            quantity: originalComponent.quantity || 1,
                                            originalQuantity: originalComponent.quantity || 1,
                                            notes: originalComponent.notes || '',
                                            serial: '',
                                            serials: [],
                                            productId: productId,
                                            actualProductId: product.id,
                                            isFromProduct: true,
                                            isOriginal: true,
                                            warehouseId: defaultWarehouseId,
                                            productUnit: unitIndex,
                                            unitLabel: unitIndex > 0 ?
                                                `Đơn vị ${unitIndex + 1}` : ''
                                        };
                                        selectedComponents.push(newComponent);
                                    });
                                }
                            }
                        }

                        // Call our updateProductComponentList to refresh the UI
                        if (typeof updateProductComponentList === "function") {
                            updateProductComponentList(productId);
                        } else {
                            console.error("updateProductComponentList function not found");
                        }

                        // Update component quantities when product quantity changes
                        updateComponentQuantities();
                    }

                    // Serial inputs are already handled by generateProductSerialInputs
                }
            });

            // Function to mark a product as already created
            function markProductAsCreated(productUniqueId, unitIndex = null) {
                // Tìm nút tạo thành phẩm mới
                const componentBlock = document.getElementById('component_block_' + productUniqueId);
                if (!componentBlock) return;

                // Find specific button for unit if unitIndex is provided
                const createNewBtn = unitIndex !== null ?
                    componentBlock.querySelector(`.create-new-product-btn[data-unit-index="${unitIndex}"]`) :
                    componentBlock.querySelector('.create-new-product-btn');

                if (createNewBtn) {
                    // Vô hiệu hóa nút
                    createNewBtn.disabled = true;
                    createNewBtn.classList.add('opacity-50');
                    createNewBtn.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Đã tạo thành phẩm';

                    // Thêm class để đánh dấu đã tạo
                    componentBlock.classList.add('product-already-created');

                    // Thêm thông báo đã tạo
                    const header = componentBlock.querySelector('h4');
                    if (header && !header.textContent.includes('(Đã tạo)')) {
                        header.innerHTML = header.innerHTML +
                            ' <span class="text-green-600">(Đã tạo)</span>';
                    }

                    // Lưu trạng thái vào localStorage để tránh tạo lại sau khi refresh
                    try {
                        const createdProducts = JSON.parse(localStorage.getItem('createdProducts') || '{}');
                        const key = unitIndex !== null ? `${productUniqueId}_unit_${unitIndex}` : productUniqueId;
                        createdProducts[key] = true;
                        localStorage.setItem('createdProducts', JSON.stringify(createdProducts));
                    } catch (e) {
                        console.error('Error saving to localStorage:', e);
                    }
                }
            }

            // Function to safely get warehouse ID
            function getWarehouseId() {
                // Priority: default warehouse > target warehouse > null
                const defaultWarehouseId = document.getElementById('default_warehouse_id')?.value;
                if (defaultWarehouseId) {
                    return defaultWarehouseId;
                }
                return warehouseSelect?.value || null;
            }

            // Function to add warehouse select to a cell
            function addWarehouseSelectToCell(cell, component, index) {
                const warehouseSelect = document.createElement('select');
                warehouseSelect.name = `components[${index}][warehouse_id]`;
                warehouseSelect.required = true;
                warehouseSelect.className =
                    'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 warehouse-select';

                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = '-- Chọn kho --';
                warehouseSelect.appendChild(defaultOption);

                // Add warehouse options from PHP data
                const warehouseData = @json($warehouses);
                warehouseData.forEach(warehouse => {
                    const warehouseOption = document.createElement('option');
                    warehouseOption.value = warehouse.id;
                    warehouseOption.textContent = `${warehouse.name} (${warehouse.code})`;
                    if (component.warehouseId === warehouse.id) {
                        warehouseOption.selected = true;
                    }
                    warehouseSelect.appendChild(warehouseOption);
                });

                // Add warehouse change handler
                warehouseSelect.addEventListener('change', function() {
                    component.warehouseId = this.value;
                    // Get the serial cell and reload serials
                    const serialCell = cell.parentElement.cells[5]; // Get serial cell by index
                    if (serialCell) {
                        addSerialInputsToCell(serialCell, component, index);
                    }
                });

                cell.appendChild(warehouseSelect);
            }

            // DUPLICATE FUNCTION REMOVED - This logic is already handled above

            // Function to fetch serials from server
            async function fetchMaterialSerials(materialId, warehouseId) {
                try {
                    const response = await fetch(`{{ route('assemblies.material-serials') }}?` +
                        new URLSearchParams({
                            material_id: materialId,
                            warehouse_id: warehouseId
                        }));

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    if (!data.success) {
                        throw new Error(data.message || 'Lỗi khi tải serial');
                    }

                    return data.serials || [];
                } catch (error) {
                    console.error('Error fetching serials:', error);
                    throw error;
                }
            }



            // Add event listener for component quantity changes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('component-quantity-input')) {
                    const row = e.target.closest('tr');
                    if (!row) return;

                    const productId = e.target.getAttribute('data-product-id');
                    if (!productId) return;

                    // Update component quantity in selectedComponents array
                    const componentId = e.target.getAttribute('data-component-id');
                    const newQuantity = parseInt(e.target.value) || 1;

                    // Find and update the component
                    const component = selectedComponents.find(c =>
                        c.id == componentId && c.productId === productId
                    );

                    if (component) {
                        // Check if this component is from an existing product
                        if (component.isFromExistingProduct) {
                            // Prevent quantity change for components from existing products
                            e.target.value = component.originalQuantity || component.quantity;
                            Swal.fire({
                                icon: 'warning',
                                title: 'Không thể thay đổi',
                                text: 'Không thể thay đổi số lượng vật tư từ thành phẩm đã tồn tại',
                                confirmButtonText: 'Đóng'
                            });
                            return;
                        }

                        component.quantity = newQuantity;
                        component.manuallyAdjusted = true;
                        console.log(`Updated component ${component.name} quantity to ${newQuantity}`);

                        // Check if components are modified and show/hide create new product button
                        checkAndShowCreateNewProductButton(productId);
                    }
                }
            });

            // Event listener for default warehouse selection
            document.getElementById('default_warehouse_id').addEventListener('change', function() {
                const defaultWarehouseId = this.value;
                const applyButton = document.getElementById('apply_default_warehouse');
                
                if (defaultWarehouseId) {
                    applyButton.disabled = false;
                    applyButton.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    applyButton.disabled = true;
                    applyButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });

            // Event listener for apply default warehouse button
            document.getElementById('apply_default_warehouse').addEventListener('click', function() {
                const defaultWarehouseId = document.getElementById('default_warehouse_id').value;
                
                if (!defaultWarehouseId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Chưa chọn kho mặc định',
                        text: 'Vui lòng chọn kho mặc định trước khi áp dụng',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                // Confirm action
                Swal.fire({
                    icon: 'question',
                    title: 'Áp dụng kho mặc định',
                    text: 'Bạn có muốn áp dụng kho mặc định cho tất cả vật tư hiện tại?',
                    showCancelButton: true,
                    confirmButtonText: 'Áp dụng',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        applyDefaultWarehouseToAllComponents(defaultWarehouseId);
                    }
                });
            });

            // Function to apply default warehouse to all components
            function applyDefaultWarehouseToAllComponents(defaultWarehouseId) {
                let updatedCount = 0;
                const warehouseSelects = document.querySelectorAll('.warehouse-select');
                
                warehouseSelects.forEach(select => {
                    const oldValue = select.value;
                    select.value = defaultWarehouseId;
                    
                    // Trigger change event to update component data and reload serials
                    const changeEvent = new Event('change', { bubbles: true });
                    select.dispatchEvent(changeEvent);
                    
                    updatedCount++;
                });

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Đã áp dụng kho mặc định',
                    text: `Đã cập nhật kho xuất cho ${updatedCount} vật tư`,
                    confirmButtonText: 'Đóng'
                });

                // Update selectedComponents array
                selectedComponents.forEach(component => {
                    component.warehouseId = defaultWarehouseId;
                });
            }

            // Function to update warehouse selects when new components are added
            function updateNewComponentWarehouse(componentElement, defaultWarehouseId) {
                if (defaultWarehouseId) {
                    const warehouseSelect = componentElement.querySelector('.warehouse-select');
                    if (warehouseSelect) {
                        warehouseSelect.value = defaultWarehouseId;
                        // Trigger change event
                        const changeEvent = new Event('change', { bubbles: true });
                        warehouseSelect.dispatchEvent(changeEvent);
                    }
                }
            }

            // Override the existing addWarehouseSelectToCell function to support default warehouse
            const originalAddWarehouseSelectToCell = addWarehouseSelectToCell;
            addWarehouseSelectToCell = function(cell, component, index) {
                // Call original function
                originalAddWarehouseSelectToCell.call(this, cell, component, index);
                
                // Check if default warehouse is selected and apply it
                const defaultWarehouseId = document.getElementById('default_warehouse_id').value;
                if (defaultWarehouseId) {
                    const warehouseSelect = cell.querySelector('.warehouse-select');
                    if (warehouseSelect) {
                        warehouseSelect.value = defaultWarehouseId;
                        component.warehouseId = defaultWarehouseId;
                        
                        // Trigger change event to reload serials
                        const changeEvent = new Event('change', { bubbles: true });
                        warehouseSelect.dispatchEvent(changeEvent);
                    }
                }
            };

            // Handle employee search functionality for assigned_to
            const assignedToSearch = document.getElementById('assigned_to_search');
            const assignedToDropdown = document.getElementById('assigned_to_dropdown');
            const assignedToOptions = document.querySelectorAll('#assigned_to_dropdown .employee-option');
            const assignedToHidden = document.getElementById('assigned_to');

            let selectedAssignedToId = '';
            let selectedAssignedToName = '';

            // Show dropdown when input is focused
            assignedToSearch.addEventListener('focus', function() {
                assignedToDropdown.classList.remove('hidden');
                filterEmployees(assignedToOptions, assignedToSearch.value);
            });

            // Handle assigned_to search input
            assignedToSearch.addEventListener('input', function() {
                filterEmployees(assignedToOptions, this.value);
            });

            // Handle assigned_to option selection
            assignedToOptions.forEach(option => {
                option.addEventListener('click', function() {
                    selectedAssignedToId = this.getAttribute('data-value');
                    selectedAssignedToName = this.getAttribute('data-text');
                    assignedToSearch.value = selectedAssignedToName;
                    assignedToHidden.value = selectedAssignedToId;
                    assignedToDropdown.classList.add('hidden');
                });
            });

            // Handle employee search functionality for tester_id
            const testerIdSearch = document.getElementById('tester_id_search');
            const testerIdDropdown = document.getElementById('tester_id_dropdown');
            const testerIdOptions = document.querySelectorAll('#tester_id_dropdown .employee-option');
            const testerIdHidden = document.getElementById('tester_id');

            let selectedTesterId = '';
            let selectedTesterName = '';

            // Show dropdown when input is focused
            testerIdSearch.addEventListener('focus', function() {
                testerIdDropdown.classList.remove('hidden');
                filterEmployees(testerIdOptions, testerIdSearch.value);
            });

            // Handle tester_id search input
            testerIdSearch.addEventListener('input', function() {
                filterEmployees(testerIdOptions, this.value);
            });

            // Handle tester_id option selection
            testerIdOptions.forEach(option => {
                option.addEventListener('click', function() {
                    selectedTesterId = this.getAttribute('data-value');
                    selectedTesterName = this.getAttribute('data-text');
                    testerIdSearch.value = selectedTesterName;
                    testerIdHidden.value = selectedTesterId;
                    testerIdDropdown.classList.add('hidden');
                });
            });

            // Hide dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!assignedToSearch.contains(e.target) && !assignedToDropdown.contains(e.target)) {
                    assignedToDropdown.classList.add('hidden');
                }
                if (!testerIdSearch.contains(e.target) && !testerIdDropdown.contains(e.target)) {
                    testerIdDropdown.classList.add('hidden');
                }
            });

            // Filter employees based on search input
            function filterEmployees(options, searchTerm) {
                const searchTermLower = searchTerm.toLowerCase();
                options.forEach(option => {
                    const employeeName = option.getAttribute('data-text');
                    const employeeNameLower = employeeName.toLowerCase();
                    
                    if (employeeNameLower.includes(searchTermLower)) {
                        option.style.display = 'block';
                        
                        // Highlight search term if it exists
                        if (searchTerm) {
                            const regex = new RegExp(`(${searchTerm})`, 'gi');
                            option.innerHTML = employeeName.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
                        } else {
                            option.innerHTML = employeeName;
                        }
                    } else {
                        option.style.display = 'none';
                    }
                });
            }

            // Initialize product search functionality
            const productDropdown = document.getElementById('product_dropdown');
            const productOptions = document.querySelectorAll('#product_dropdown .product-option');

            // Show dropdown when input is focused
            productSearch.addEventListener('focus', function() {
                productDropdown.classList.remove('hidden');
                filterProducts(productOptions, productSearch.value);
            });

            // Handle product search input
            productSearch.addEventListener('input', function() {
                filterProducts(productOptions, this.value);
            });

            // Handle product option selection
            productOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const productId = this.getAttribute('data-value');
                    const productText = this.getAttribute('data-text');
                    productSearch.value = productText;
                    productHidden.value = productId;
                    productDropdown.classList.add('hidden');
                });
            });

            // Filter products based on search input
            function filterProducts(options, searchTerm) {
                const searchTermLower = searchTerm.toLowerCase();
                options.forEach(option => {
                    const productText = option.getAttribute('data-text');
                    const productTextLower = productText.toLowerCase();
                    
                    if (productTextLower.includes(searchTermLower)) {
                        option.style.display = 'block';
                        
                        // Highlight search term if it exists
                        if (searchTerm) {
                            const regex = new RegExp(`(${searchTerm})`, 'gi');
                            option.innerHTML = productText.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
                        } else {
                            option.innerHTML = productText;
                        }
                    } else {
                        option.style.display = 'none';
                    }
                });
            }

            // Hide product dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!productSearch.contains(e.target) && !productDropdown.contains(e.target)) {
                    productDropdown.classList.add('hidden');
                }
            });

            // Keyboard navigation for assigned_to
            let assignedToSelectedIndex = -1;
            assignedToSearch.addEventListener('keydown', function(e) {
                const visibleOptions = Array.from(assignedToOptions).filter(option => 
                    option.style.display !== 'none'
                );

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    assignedToSelectedIndex = Math.min(assignedToSelectedIndex + 1, visibleOptions.length - 1);
                    updateEmployeeSelection(visibleOptions, assignedToSelectedIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    assignedToSelectedIndex = Math.max(assignedToSelectedIndex - 1, -1);
                    updateEmployeeSelection(visibleOptions, assignedToSelectedIndex);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (assignedToSelectedIndex >= 0 && visibleOptions[assignedToSelectedIndex]) {
                        const option = visibleOptions[assignedToSelectedIndex];
                        selectedAssignedToId = option.getAttribute('data-value');
                        selectedAssignedToName = option.getAttribute('data-text');
                        assignedToSearch.value = selectedAssignedToName;
                        assignedToHidden.value = selectedAssignedToId;
                        assignedToDropdown.classList.add('hidden');
                        assignedToSelectedIndex = -1;
                    }
                } else if (e.key === 'Escape') {
                    assignedToDropdown.classList.add('hidden');
                    assignedToSelectedIndex = -1;
                }
            });

            // Keyboard navigation for tester_id
            let testerIdSelectedIndex = -1;
            testerIdSearch.addEventListener('keydown', function(e) {
                const visibleOptions = Array.from(testerIdOptions).filter(option => 
                    option.style.display !== 'none'
                );

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    testerIdSelectedIndex = Math.min(testerIdSelectedIndex + 1, visibleOptions.length - 1);
                    updateEmployeeSelection(visibleOptions, testerIdSelectedIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    testerIdSelectedIndex = Math.max(testerIdSelectedIndex - 1, -1);
                    updateEmployeeSelection(visibleOptions, testerIdSelectedIndex);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (testerIdSelectedIndex >= 0 && visibleOptions[testerIdSelectedIndex]) {
                        const option = visibleOptions[testerIdSelectedIndex];
                        selectedTesterId = option.getAttribute('data-value');
                        selectedTesterName = option.getAttribute('data-text');
                        testerIdSearch.value = selectedTesterName;
                        testerIdHidden.value = selectedTesterId;
                        testerIdDropdown.classList.add('hidden');
                        testerIdSelectedIndex = -1;
                    }
                } else if (e.key === 'Escape') {
                    testerIdDropdown.classList.add('hidden');
                    testerIdSelectedIndex = -1;
                }
            });

            function updateEmployeeSelection(visibleOptions, selectedIndex) {
                // Remove previous selection from all employee options
                document.querySelectorAll('.employee-option').forEach(option => {
                    option.classList.remove('bg-blue-100', 'text-blue-900');
                });

                // Add selection to current index
                if (selectedIndex >= 0 && visibleOptions[selectedIndex]) {
                    visibleOptions[selectedIndex].classList.add('bg-blue-100', 'text-blue-900');
                }
            }
        });
    </script>
</body>

</html>
