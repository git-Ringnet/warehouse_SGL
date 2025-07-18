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
    <script src="{{ asset('js/assembly-product-unit.js') }}"></script>
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
            <form action="{{ route('assemblies.store') }}" method="POST">
                @csrf

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
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày lắp ráp <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="assembly_date" name="assembly_date" value="{{ date('Y-m-d') }}"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1 required">Thành
                                phẩm <span class="text-red-500">*</span></label>
                            <div class="relative flex space-x-2">
                                <select id="product_id"
                                    class="flex-1 border w-full border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Chọn thành phẩm --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                                            data-code="{{ $product->code }}">
                                            [{{ $product->code }}] {{ $product->name }}</option>
                                    @endforeach
                                </select>
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
                            <div class="text-xs text-gray-500 mt-1">Chọn thành phẩm, nhập số lượng và nhấn thêm. Có thể
                                thêm nhiều thành phẩm.</div>
                        </div>

                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1 required">Người
                                phụ trách <span class="text-red-500">*</span></label>
                            <select id="assigned_to" name="assigned_to" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người phụ trách --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}
                                        ({{ $employee->username }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="tester_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người
                                tiếp nhận kiểm thử <span class="text-red-500">*</span></label>
                            <select id="tester_id" name="tester_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người tiếp nhận kiểm thử --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}
                                        ({{ $employee->username }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1 required">Mục
                                đích <span class="text-red-500">*</span></label>
                            <select id="purpose" name="purpose" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="storage">Lưu kho</option>
                                <option value="project">Xuất đi dự án</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-4">
                        <div id="project_selection" class="hidden">
                            <div>
                                <label for="project_id"
                                    class="block text-sm font-medium text-gray-700 mb-1 required">Dự
                                    án <span class="text-red-500">*</span></label>
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

                        <!-- Old component list (kept for compatibility but hidden) -->
                        <table id="component_table" class="min-w-full divide-y divide-gray-200 hidden">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Loại vật tư
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên vật tư
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số lượng
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Serial
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ghi chú
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="component_list" class="bg-white divide-y divide-gray-200">
                                <tr id="no_components_row">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Chưa có vật tư nào được thêm vào phiếu lắp ráp
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
            </form>
        </main>
    </div>

    <script>
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
                Object.keys(createdProducts).forEach(productUniqueId => {
                    if (createdProducts[productUniqueId] === true) {
                        setTimeout(() => {
                            markProductAsCreated(productUniqueId);
                        }, 1000);
                    }
                });
            } catch (e) {
                console.error('Error loading created products from localStorage:', e);
            }

            const componentSearchInput = document.getElementById('component_search');
            const componentAddQuantity = document.getElementById('component_add_quantity');
            const componentProductSelect = document.getElementById('component_product_id');
            const addComponentBtn = document.getElementById('add_component_btn');
            const componentList = document.getElementById('component_list');
            const noComponentsRow = document.getElementById('no_components_row');
            const searchResults = document.getElementById('search_results');
            const warehouseSelect = document.getElementById('warehouse_id');
            const targetWarehouseContainer = document.getElementById('target_warehouse_container');
            const targetWarehouseIdSelect = document.getElementById('target_warehouse_id');

            // Thành phẩm UI elements
            const productSelect = document.getElementById('product_id');
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
            let warehouseStockData = {}; // Cache stock data for current warehouse

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
                    console.log("Search input cleared - disabled add button");
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

                // Check if component already exists for this product
                const existingComponent = selectedComponents.find(c =>
                    c.id === selectedComponentToAdd.id && c.productId === productUniqueId
                );

                if (existingComponent) {
                    alert('Vật tư này đã được thêm cho thành phẩm này!');
                    return;
                }


                // Add new component
                const newComponent = {
                    id: selectedComponentToAdd.id,
                    code: selectedComponentToAdd.code,
                    name: selectedComponentToAdd.name,
                    category: selectedComponentToAdd.category,
                    unit: selectedComponentToAdd.unit,
                    quantity: quantity,
                    originalQuantity: 0, // New component, no original quantity
                    notes: '',
                    serial: '',
                    productId: productUniqueId,
                    actualProductId: selectedProduct.id, // Store actual product ID for backend
                    isFromProduct: false, // User added component
                    isOriginal: false, // Not from original BOM
                    productUnit: 0 // Default to first product unit (0)
                };

                selectedComponents.push(newComponent);

                // Update component list and check for changes
                updateProductComponentList(productUniqueId);
                checkAndShowCreateNewProductButton(productUniqueId);

                // Fetch stock data if warehouse is selected
                if (warehouseSelect && warehouseSelect.value) {
                    fetchWarehouseStockData();
                }

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
                const productId = productSelect.value;
                const productName = productSelect.options[productSelect.selectedIndex].text;
                const quantity = parseInt(productAddQuantity.value) || 1;

                if (!productId) {
                    alert('Vui lòng chọn thành phẩm!');
                    return;
                }

                productCounter++;

                // Thêm vào danh sách thành phẩm đã chọn
                const newProduct = {
                    id: productId,
                    uniqueId: 'product_' + productCounter,
                    name: productName,
                    quantity: quantity,
                    serials: Array(quantity).fill('')
                };

                selectedProducts.push(newProduct);
                updateProductList();

                // Tạo block danh sách linh kiện cho sản phẩm này
                createProductComponentBlock(newProduct);

                // Update component product dropdown
                updateComponentProductDropdown();

                // Reset form
                productSelect.value = '';
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
                const existingBlock = document.getElementById('component_block_' + product.uniqueId);
                if (existingBlock) {
                    const existingTbody = existingBlock.querySelector(`#component_list_${product.uniqueId}`);
                    if (existingTbody) {
                        existingTbody.innerHTML = '';
                    }

                    const existingThead = existingBlock.querySelector('thead');
                    if (existingThead) existingThead.style.display = ''; // Luôn hiển thị

                    const existingHeader = existingBlock.querySelector('div.bg-blue-50');
                    if (existingHeader) existingHeader.style.display = ''; // Luôn hiển thị

                    const noComponentsRow = document.createElement('tr');
                    noComponentsRow.id = 'no_components_row_' + product.uniqueId;
                    noComponentsRow.innerHTML = `
            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                Chưa có linh kiện nào cho thành phẩm này
            </td>
        `;
                    existingTbody.appendChild(noComponentsRow);

                    const productUnitsRow = document.createElement('tr');
                    productUnitsRow.className = 'product-units-row';
                    productUnitsRow.innerHTML = `
            <td colspan="8" class="px-0 py-2">
                <div class="product-units-container" data-product-id="${product.uniqueId}"></div>
            </td>
        `;
                    existingTbody.appendChild(productUnitsRow);

                    return existingBlock;
                }

                document.getElementById('no_products_components').style.display = 'none';

                const componentBlock = document.createElement('div');
                componentBlock.id = 'component_block_' + product.uniqueId;
                componentBlock.className = 'mb-6 border border-gray-200 rounded-lg overflow-hidden';

                const header = document.createElement('div');
                header.className = 'bg-blue-50 border-b border-gray-200 p-3 flex justify-between items-center';
                header.style.display = ''; // Luôn hiển thị
                header.innerHTML = `
        <h4 class="font-medium text-blue-700">
            <i class="fas fa-box-open mr-2"></i>
            Linh kiện cho thành phẩm: ${product.name}
        </h4>
    `;

                const tableContainer = document.createElement('div');
                tableContainer.className = 'overflow-x-auto';

                const table = document.createElement('table');
                table.className = 'min-w-full divide-y divide-gray-200';

                const thead = document.createElement('thead');
                thead.className = 'bg-gray-50';
                thead.style.display = ''; // Luôn hiển thị
                thead.innerHTML = `
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại vật tư</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên vật tư</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ghi chú</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
        </tr>
    `;

                const tbody = document.createElement('tbody');
                tbody.id = 'component_list_' + product.uniqueId;
                tbody.className = 'bg-white divide-y divide-gray-200';

                const noComponentsRow = document.createElement('tr');
                noComponentsRow.id = 'no_components_row_' + product.uniqueId;
                noComponentsRow.innerHTML = `
        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
            Chưa có linh kiện nào cho thành phẩm này
        </td>
    `;
                tbody.appendChild(noComponentsRow);

                const productUnitsRow = document.createElement('tr');
                productUnitsRow.className = 'product-units-row';
                productUnitsRow.innerHTML = `
        <td colspan="8" class="px-0 py-2">
            <div class="product-units-container" data-product-id="${product.uniqueId}"></div>
        </td>
    `;
                tbody.appendChild(productUnitsRow);

                table.appendChild(thead);
                table.appendChild(tbody);
                tableContainer.appendChild(table);
                componentBlock.appendChild(header);
                componentBlock.appendChild(tableContainer);
                document.getElementById('component_blocks_container').appendChild(componentBlock);

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

                                // Add components to the selected components list
                                components.forEach(material => {
                                    const componentData = {
                                        id: material.id,
                                        code: material.code,
                                        name: material.name,
                                        category: material.category,
                                        unit: material.unit,
                                        quantity: material
                                            .quantity, // From product_materials pivot table
                                        originalQuantity: material
                                            .quantity, // Store original quantity
                                        notes: material.notes || '',
                                        serial: material.quantity === 1 ? '' : '',
                                        serials: material.quantity > 1 ? Array(material.quantity)
                                            .fill('') : [],
                                        productId: productUniqueId,
                                        actualProductId: product
                                            .id, // Store actual product ID for backend
                                        isFromProduct: true, // Flag to indicate this came from product BOM
                                        isOriginal: true // Flag to mark original components
                                    };

                                    selectedComponents.push(componentData);
                                });

                                // Update the component list for this product
                                updateProductComponentList(productUniqueId);

                                // Check for changes and show/hide create new product button
                                checkAndShowCreateNewProductButton(productUniqueId);

                                // Fetch stock data if warehouse is selected
                                if (warehouseSelect && warehouseSelect.value) {
                                    fetchWarehouseStockData();
                                }

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

                // Clear existing rows except the "no components" row
                const existingRows = componentListElement.querySelectorAll('tr:not(#no_components_row_' +
                    productUniqueId + ')');
                existingRows.forEach(row => row.remove());

                // Get components for this product
                const productComponents = selectedComponents.filter(comp => comp.productId === productUniqueId);

                if (productComponents.length === 0) {
                    noComponentsRow.style.display = '';
                    return;
                } else {
                    noComponentsRow.style.display = 'none';
                }

                // Add components to the list
                productComponents.forEach((component, index) => {
                    const row = document.createElement('tr');
                    row.className = 'component-row';
                    row.setAttribute('data-component-id', component.id);
                    row.setAttribute('data-product-id', productUniqueId);

                    // Mark modified components by comparing with original formula
                    const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                    let isModified = false;
                    if (product && product.originalComponents) {
                        const originalComponent = product.originalComponents.find(o => o.id === component
                            .id);
                        if (!originalComponent) {
                            // New component not in original formula
                            isModified = true;
                        } else if (originalComponent.quantity !== component.quantity) {
                            // Quantity changed from original
                            isModified = true;
                        }
                        // If component exists in original with same quantity, it's not modified
                    } else {
                        // Fallback to old logic if no original components available
                        isModified = !component.isOriginal || component.quantity !== component
                            .originalQuantity;
                    }

                    const modifiedClass = isModified ? 'bg-yellow-50' : '';

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 ${modifiedClass}">
                            <input type="hidden" name="components[${index}][id]" value="${component.id}">
                            ${component.code}
                            ${isModified ? '<i class="fas fa-edit text-yellow-500 ml-1" title="Đã sửa đổi"></i>' : ''}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 ${modifiedClass}">${component.category}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 ${modifiedClass}">${component.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 ${modifiedClass}">
                            <input type="number" min="1" step="1" name="components[${index}][quantity]" 
                                   value="${component.quantity}" 
                                   data-component-id="${component.id}" 
                                   data-product-id="${productUniqueId}" required
                                   class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 component-quantity-input">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 ${modifiedClass} serial-cell">
                            <!-- Serial inputs will be added here -->
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 ${modifiedClass}">
                            <input type="text" name="components[${index}][note]" 
                                   value="${component.notes}" 
                                   placeholder="Ghi chú (tùy chọn)"
                                   class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="stock-warning-container"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${modifiedClass}">
                            <button type="button" class="text-red-500 hover:text-red-700 delete-component" 
                                    data-product-id="${productUniqueId}" data-component-id="${component.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;

                    componentListElement.insertBefore(row, noComponentsRow);

                    // Add serial inputs for this component
                    const serialCell = row.querySelector('.serial-cell');
                    addSerialInputsToCell(serialCell, component, index);

                    // Check stock and display warning
                    checkStockSufficiency(component);
                    const warningContainer = row.querySelector('.stock-warning-container');
                    if (component.stockWarning) {
                        warningContainer.innerHTML =
                            `<div class="text-xs text-red-500 mt-1"><i class="fas fa-exclamation-triangle"></i> ${component.stockWarning}</div>`;
                    } else {
                        warningContainer.innerHTML = '';
                    }
                });

                // Add event listeners for quantity changes
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

                        // Find the component in the global array
                        const component = selectedComponents.find(c =>
                            c.id == componentId && c.productId === productId
                        );

                        if (component) {
                            // Handle quantity change with new function
                            handleComponentQuantityChange(component, newQuantity, productId);

                            // Check stock sufficiency
                            checkStockSufficiency(component);
                        } else {
                            console.error(
                                `Component not found: ID ${componentId}, Product ${productId}`);
                        }
                    });
                });
            }

            // Function to check stock sufficiency
            function checkStockSufficiency(component) {
                if (!warehouseSelect || !warehouseSelect.value) {
                    component.isStockSufficient = true;
                    component.stockWarning = '';
                    return true;
                }

                // Calculate total required quantity for this component across all products
                let totalRequired = 0;
                const componentProductIds = selectedComponents
                    .filter(c => c.id === component.id)
                    .map(c => c.productId);

                componentProductIds.forEach(productId => {
                    const product = selectedProducts.find(p => p.uniqueId === productId);
                    const comp = selectedComponents.find(c => c.id === component.id && c.productId ===
                        productId);

                    if (product && comp) {
                        totalRequired += comp.quantity * product.quantity;
                    }
                });

                // Get stock from warehouseStockData
                const availableStock = warehouseStockData[component.id] || 0;

                if (availableStock < totalRequired) {
                    component.isStockSufficient = false;
                    component.stockWarning = `Không đủ tồn kho (Có: ${availableStock}, Cần: ${totalRequired})`;
                    return false;
                } else {
                    component.isStockSufficient = true;
                    component.stockWarning = '';
                    return true;
                }
            }

            // Function to fetch warehouse stock data for all components
            async function fetchWarehouseStockData() {
                if (!warehouseSelect || !warehouseSelect.value || selectedComponents.length === 0) {
                    warehouseStockData = {};
                    return;
                }

                // Get unique material IDs from all components
                const materialIds = [...new Set(selectedComponents.map(c => c.id))];

                try {
                    const response = await fetch(`/assemblies/warehouse-stock/${warehouseSelect.value}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            material_ids: materialIds
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        warehouseStockData = data.stock_data;

                        // Update stock warnings for all components
                        selectedComponents.forEach(component => {
                            checkStockSufficiency(component);
                        });

                        // Update UI to show stock warnings
                        updateAllStockWarnings();
                    } else {
                        console.error('Error fetching stock data:', data.message);
                        warehouseStockData = {};
                    }
                } catch (error) {
                    console.error('Error fetching warehouse stock:', error);
                    warehouseStockData = {};
                }
            }

            // Function to update stock warnings in UI
            function updateAllStockWarnings() {
                // Update stock warnings in all product component lists
                selectedProducts.forEach(product => {
                    updateProductComponentList(product.uniqueId);
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
                            console.log(
                                "Material selected from search results, enabling add button");
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
                    const componentId = parseInt(btn.getAttribute('data-component-id'));

                    if (!productId || isNaN(componentId)) {
                        console.error('Invalid product ID or component ID');
                        return;
                    }

                    // Remove component from selectedComponents
                    selectedComponents = selectedComponents.filter(c =>
                        !(c.id === componentId && c.productId === productId)
                    );

                    // Update UI and check for changes
                    updateProductComponentList(productId);
                    checkAndShowCreateNewProductButton(productId);

                    // Show empty state if no components left
                    const productComponents = selectedComponents.filter(c => c.productId === productId);
                    if (productComponents.length === 0) {
                        const noComponentsRow = document.getElementById('no_components_row_' + productId);
                        if (noComponentsRow) {
                            noComponentsRow.style.display = '';
                        }
                    }
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
                        (index + 1) +
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
                        '<div class="space-y-2">' +
                        Array.from({
                                length: product.quantity || 1
                            }, (_, i) =>
                            '<input type="text" ' +
                            'name="products[' + index + '][serials][]" ' +
                            'placeholder="Serial ' + (i + 1) + '" ' +
                            'class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">'
                        ).join('') +
                        '</div>' +
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

                        // Handle quantity change with new function
                        handleProductQuantityChange(product, newQty, index, serialCell);

                        // Update hidden inputs and check stock
                        updateHiddenProductList();
                        if (warehouseSelect && warehouseSelect.value) {
                            fetchWarehouseStockData();
                        }
                    });

                    // Don't add event listener here - use event delegation instead
                });

                // Update hidden product inputs for form submission
                updateHiddenProductList();
            }

            // Hàm tạo các trường nhập serial cho thành phẩm với validation
            function generateProductSerialInputs(product, productIndex, container) {
                container.innerHTML = '';
                const quantity = parseInt(product.quantity);

                for (let i = 0; i < quantity; i++) {
                    const serialInput = document.createElement('div');
                    serialInput.className = 'mb-1';

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = product.serials[i] || '';
                    input.placeholder = 'Serial ' + (i + 1) + ' (tùy chọn)';
                    input.className =
                        'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                    // Save serial when typing
                    input.addEventListener('input', function() {
                        product.serials[i] = this.value;
                        updateHiddenProductList(); // Update hidden inputs when serial changes
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
                    resultItem.innerHTML = '<div class="font-medium">' + material.code + ': ' + material
                        .name + '</div>' + '<div class="text-xs text-gray-500">' + (material.category ||
                            '') + ' ' + (material.serial ? '| ' + material.serial : '') + ' ' +
                        '| Tồn kho: ' +
                        (material.stock_quantity || 0) + '</div>';

                    // Handle click on search result
                    resultItem.addEventListener('click', function() {
                        // Cập nhật cả hai biến để đảm bảo tính nhất quán
                        selectedMaterial = material;
                        selectedComponentToAdd = material;
                        componentSearchInput.value = material.code + ' - ' + material.name;
                        searchResults.classList.add('hidden');

                        // Kích hoạt nút thêm nếu đã chọn thành phẩm
                        if (componentProductSelect.value) {
                            addComponentBtn.disabled = false;
                            addComponentBtn.classList.remove('opacity-50');
                            console.log(
                                "Enabled add component button - material and product selected");
                        } else {
                            console.log("Material selected but waiting for product selection");
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
                if (event.key === 'Enter' && selectedMaterial && componentProductSelect.value) {
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

                // Add the component using the global function
                console.log("Adding component with:", {
                    materialToAdd,
                    selectedProduct,
                    quantity,
                    currentComponents: window.selectedComponents ? window.selectedComponents.length : 0
                });

                try {
                    // Gọi hàm window.addSelectedComponent để thêm linh kiện
                    const newComponent = window.addSelectedComponent(materialToAdd, selectedProduct, quantity);

                    if (!newComponent) {
                        console.error("Failed to add component - null returned from addSelectedComponent");
                        alert('Có lỗi khi thêm linh kiện. Vui lòng thử lại!');
                        return;
                    }

                    // Hiển thị thông báo thành công
                    const successMessage = document.createElement('div');
                    successMessage.className =
                        'text-green-500 text-sm p-3 text-center fixed top-4 right-4 bg-white shadow-lg rounded-lg z-50';
                    successMessage.innerHTML =
                        '<i class="fas fa-check-circle"></i> Đã thêm "' + newComponent.name + '" vào thành phẩm "' +
                        newComponent.productName + '"';
                    document.body.appendChild(successMessage);

                    // Xóa thông báo sau 2 giây
                    setTimeout(() => {
                        if (successMessage.parentNode) {
                            successMessage.parentNode.removeChild(successMessage);
                        }
                    }, 2000);

                    // Reset search input và selectedMaterial
                    componentSearchInput.value = '';
                    selectedMaterial = null;
                    selectedComponentToAdd = null;
                    searchResults.classList.add('hidden');

                    // Vô hiệu hóa nút thêm sau khi đã thêm
                    addComponentBtn.disabled = true;
                    addComponentBtn.classList.add('opacity-50');

                    // Cập nhật giao diện
                    console.log("Updating UI with new component. Total components:", window.selectedComponents
                        .length);
                    updateProductComponentList(selectedProductId);
                    updateComponentList();

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

                    // Add empty row back
                    componentList.appendChild(emptyRow);

                    // Show or hide the empty row
                    if (components.length > 0) {
                        emptyRow.style.display = 'none';
                    } else {
                        emptyRow.style.display = '';
                        continue; // Skip to next product if no components
                    }

                    // Add each component
                    components.forEach((component, index) => {
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
                            '<input type="number" min="1" step="1" name="components[' + index +
                            '][quantity]" value="' + (
                                component.quantity || 1) + '"' +
                            ' class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input"' +
                            ' data-component-index="' + selectedComponents.indexOf(component) + '">';

                        // Set row HTML
                        row.innerHTML =
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
                            '<input type="hidden" name="components[' + index + '][id]" value="' + component
                            .id + '">' +
                            '<input type="hidden" name="components[' + index + '][product_id]" value="' +
                            component
                            .productId + '">' +
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
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 serial-cell">' +
                            '<!-- Serial inputs will be added here -->' +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' +
                            '<input type="text" name="components[${index}][note]" value="' + (component
                                .note ||
                                '') + '"' +
                            ' class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 note-input"' +
                            ' placeholder="Ghi chú" data-component-index="' + selectedComponents.indexOf(
                                component) + '">' +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">' +
                            '<button type="button" class="text-red-500 hover:text-red-700 delete-component" ' +
                            'data-product-id="' + component.productId + '" data-component-id="' + component
                            .id + '">' +
                            '<i class="fas fa-trash"></i>' +
                            '</button>' +
                            '</td>';

                        componentList.appendChild(row);

                        // Add serial inputs for this component
                        const serialCell = row.querySelector('.serial-cell');
                        addSerialInputsToCell(serialCell, component, index);

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

                // Remove all rows except the no_components_row
                Array.from(oldComponentList.children).forEach(child => {
                    if (child.id !== 'no_components_row') {
                        oldComponentList.removeChild(child);
                    }
                });

                // Show/hide no components message
                if (selectedComponents.length === 0) {
                    noComponentsRow.style.display = '';
                    return;
                } else {
                    noComponentsRow.style.display = 'none';
                }

                // Add all components to the hidden list with proper form fields
                selectedComponents.forEach((component, index) => {
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
                                    '][serial_ids][]" value="' + (serialId || '') + '">';
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
                        '<input type="hidden" name="components[' + index + '][id]" value="' + component.id +
                        '">' +
                        '<input type="hidden" name="components[' + index + '][product_id]" value="' +
                        (component.actualProductId || component.productId.replace('product_', '')) + '">' +
                        '<input type="hidden" name="components[' + index + '][quantity]" value="' + (
                            component
                            .quantity || 1) + '">' +
                        '<input type="hidden" name="components[' + index + '][product_unit]" value="' + (
                            component.productUnit || 0) + '">' +
                        serialHtml +
                        '<input type="hidden" name="components[' + index + '][note]" value="' + (component
                            .note || '') +
                        '">' +
                        '</td>';

                    oldComponentList.insertBefore(row, noComponentsRow);
                });
            }

            // Update hidden product list for form submission
            window.updateHiddenProductList = function() {
                // Remove existing hidden product inputs
                const existingProductInputs = document.querySelectorAll('input[name^="products["]');
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

                    // Product Code
                    const productCodeInput = document.createElement('input');
                    productCodeInput.type = 'hidden';
                    productCodeInput.name = 'products[' + index + '][code]';
                    productCodeInput.value = product.code;
                    form.appendChild(productCodeInput);

                    // Product Quantity
                    const productQuantityInput = document.createElement('input');
                    productQuantityInput.type = 'hidden';
                    productQuantityInput.name = 'products[' + index + '][quantity]';
                    productQuantityInput.value = product.quantity;
                    form.appendChild(productQuantityInput);

                    // Product Serials
                    if (product.serials && product.serials.length > 0) {
                        product.serials.forEach((serial, serialIndex) => {
                            const serialInput = document.createElement('input');
                            serialInput.type = 'hidden';
                            serialInput.name = 'products[' + index + '][serials][]';
                            serialInput.value = serial || '';
                            form.appendChild(serialInput);
                        });
                    }
                });
            }

            // Function to add serial inputs to a cell
            function addSerialInputsToCell(serialCell, component, index) {
                serialCell.innerHTML = '';
                const quantity = parseInt(component.quantity) || 0;

                if (quantity > 0) {
                    // Initialize serials array if not exists
                    if (!component.serials || component.serials.length !== quantity) {
                        component.serials = Array(quantity).fill('');
                    }

                    // Create container for serial inputs
                    const container = document.createElement('div');
                    container.className = 'space-y-2 flex flex-col';

                    // Add serial inputs
                    for (let i = 0; i < quantity; i++) {
                        const serialInput = document.createElement('select');
                        serialInput.name = `components[${index}][serials][]`;
                        serialInput.className =
                            'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                        // Add default option
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = '-- Chọn serial --';
                        serialInput.appendChild(defaultOption);

                        // Set value if exists
                        if (component.serials[i]) {
                            serialInput.value = component.serials[i];
                        }

                        container.appendChild(serialInput);
                    }

                    serialCell.appendChild(container);

                    // Add note for multiple serials
                    if (quantity > 3) {
                        const noteDiv = document.createElement('div');
                        noteDiv.className = 'mt-1 text-xs text-blue-700';
                        noteDiv.innerHTML = '<i class="fas fa-info-circle mr-1"></i> ' + quantity + ' serials';
                        serialCell.appendChild(noteDiv);
                    }
                }
            }

            // Function to check if components have been modified from original formula
            function checkComponentModifications(productUniqueId) {
                const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                if (!product || !product.originalComponents) return false;

                const currentComponents = selectedComponents.filter(c => c.productId === productUniqueId);

                // Check for quantity changes
                for (const current of currentComponents) {
                    const original = product.originalComponents.find(o => o.id === current.id);
                    if (!original || current.quantity !== original.quantity) {
                        return true;
                    }
                }

                // Check for added/removed components
                if (currentComponents.length !== product.originalComponents.length) {
                    return true;
                }

                return false;
            }

            // Function to show/hide create new product button
            function checkAndShowCreateNewProductButton(productUniqueId) {
                const componentBlock = document.getElementById('component_block_' + productUniqueId);
                if (!componentBlock) return;

                // Remove existing button if any
                const existingButton = componentBlock.querySelector('.create-new-product-btn');
                if (existingButton) {
                    existingButton.remove();
                }

                // Check if components have been modified
                if (checkComponentModifications(productUniqueId)) {
                    const header = componentBlock.querySelector('.bg-blue-50');
                    if (header) {
                        const createButton = document.createElement('button');
                        createButton.type = 'button';
                        createButton.className =
                            'create-new-product-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm transition-colors flex items-center';
                        createButton.innerHTML = '<i class="fas fa-plus-circle mr-1"></i> Tạo thành phẩm mới';
                        createButton.onclick = () => handleCreateNewProduct(productUniqueId);
                        header.appendChild(createButton);
                    }
                }
            }

            // Update product quantity change handler
            function handleProductQuantityChange(product, newQty, index, serialCell) {
                const oldQty = product.quantity;
                product.quantity = newQty;

                // Adjust serials array length if quantity changed
                if (newQty > product.serials.length) {
                    const additionalSerials = Array(newQty - product.serials.length).fill('');
                    product.serials = [...product.serials, ...additionalSerials];
                } else if (newQty < product.serials.length) {
                    product.serials = product.serials.slice(0, newQty);
                }

                // Regenerate serial inputs
                generateProductSerialInputs(product, index, serialCell);

                // Update component quantities and UI
                const productComponents = selectedComponents.filter(c => c.productId === product.uniqueId);
                productComponents.forEach(component => {
                    // Update component in the global array
                    const componentIndex = selectedComponents.findIndex(c =>
                        c.id === component.id && c.productId === product.uniqueId
                    );
                    if (componentIndex !== -1) {
                        // Calculate total serial count needed (component quantity * product quantity)
                        const totalSerialCount = component.quantity * newQty;
                        
                        // Update serial inputs for this component
                        const componentRow = document.querySelector(
                            `tr[data-component-id="${component.id}"][data-product-id="${product.uniqueId}"]`
                        );
                        if (componentRow) {
                            const serialCell = componentRow.querySelector('.serial-cell');
                            if (serialCell) {
                                // Initialize or adjust serials array for component based on total serial count
                                if (!component.serials || component.serials.length !== totalSerialCount) {
                                    const oldSerials = component.serials || [];
                                    component.serials = Array(totalSerialCount).fill('').map((_, i) => oldSerials[i] || '');
                                }
                                
                                // Initialize or adjust serial_ids array for component based on total serial count
                                if (!component.serial_ids || component.serial_ids.length !== totalSerialCount) {
                                    const oldSerialIds = component.serial_ids || [];
                                    component.serial_ids = Array(totalSerialCount).fill('').map((_, i) => oldSerialIds[i] || '');
                                }
                                
                                // Store the original quantity for reference
                                component.originalQuantity = component.quantity;
                                
                                // Temporarily set quantity to total for serial input generation
                                const originalQuantity = component.quantity;
                                component.quantity = totalSerialCount;
                                
                                // Add serial inputs
                                addSerialInputsToCell(serialCell, component, componentIndex);
                                
                                // Restore original quantity
                                component.quantity = originalQuantity;
                            }
                        }
                        selectedComponents[componentIndex] = component;
                    }
                });

                // Update UI to reflect changes
                updateProductComponentList(product.uniqueId);
                checkAndShowCreateNewProductButton(product.uniqueId);
            }

            // Update component quantity change handler
            function handleComponentQuantityChange(component, newQuantity, productUniqueId) {
                const oldQuantity = component.quantity;
                component.quantity = newQuantity;

                // Initialize or adjust serials array
                if (!component.serials || component.serials.length !== newQuantity) {
                    const oldSerials = component.serials || [];
                    component.serials = Array(newQuantity).fill('').map((_, i) => oldSerials[i] || '');
                }

                // Find component in global array and update
                const componentIndex = selectedComponents.findIndex(c =>
                    c.id === component.id && c.productId === productUniqueId
                );

                if (componentIndex !== -1) {
                    selectedComponents[componentIndex] = component;

                    // Update UI
                    const row = document.querySelector(
                        `tr[data-component-id="${component.id}"][data-product-id="${productUniqueId}"]`);
                    if (row) {
                        const serialCell = row.querySelector('.serial-cell');
                        if (serialCell) {
                            addSerialInputsToCell(serialCell, component, componentIndex);
                        }
                    }
                }

                // Check for modifications and show/hide create new product button
                checkAndShowCreateNewProductButton(productUniqueId);
            }

            // Function to check if any components have modified quantities for a product
            function checkComponentsModified(productUniqueId) {
                const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                if (!product || !product.originalComponents) {
                    console.log('No original components found, using fallback logic');
                    // Fallback to old logic if no original components available
                    const productComponents = selectedComponents.filter(c => c.productId === productUniqueId);
                    const modified = productComponents.some(component => component.quantity !== component
                        .originalQuantity);
                    console.log(`Using fallback logic: ${modified ? 'MODIFIED' : 'not modified'}`);
                    return modified;
                }

                const currentComponents = selectedComponents.filter(c => c.productId === productUniqueId);
                const originalComponents = product.originalComponents;

                console.log('Component comparison:', {
                    currentCount: currentComponents.length,
                    originalCount: originalComponents.length,
                    current: currentComponents,
                    original: originalComponents
                });

                // Check if number of components changed
                if (currentComponents.length !== originalComponents.length) {
                    console.log('Component count changed, marking as modified');
                    return true;
                }

                // Check each component for changes
                for (let current of currentComponents) {
                    const original = originalComponents.find(o => o.id == current.id);
                    if (!original) {
                        // New component added
                        console.log(`New component added: ${current.id}`);
                        return true;
                    } else if (original.quantity != current.quantity) {
                        // Quantity changed
                        console.log(
                            `Component ${current.id} quantity changed: ${original.quantity} -> ${current.quantity}`
                        );
                        return true;
                    }
                }

                console.log('No modifications detected');
                return false;
            }

            // Function to add the "Create New Product" button
            function addCreateNewProductButton(componentBlock, productId, productUniqueId) {
                // Remove existing button if any
                const existingSection = componentBlock.querySelector('.duplicate-section');
                if (existingSection) {
                    existingSection.remove();
                }

                const duplicateSection = document.createElement('div');
                duplicateSection.className = 'bg-yellow-50 border-t border-yellow-200 p-3 duplicate-section';
                duplicateSection.setAttribute('data-product-id', productUniqueId);
                duplicateSection.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-yellow-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            Bạn đã thay đổi công thức gốc. Bạn có thể tạo một thành phẩm mới với công thức này.
                        </div>
                        <button type="button" class="create-new-product-btn bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm"
                                data-product-id="${productId}" data-unique-id="${productUniqueId}">
                            <i class="fas fa-plus-circle mr-1"></i> Tạo thành phẩm mới
                        </button>
                    </div>
                `;
                componentBlock.appendChild(duplicateSection);

                // Add event listener to the create new product button
                setTimeout(() => {
                    const createNewBtn = componentBlock.querySelector('.create-new-product-btn');
                    if (createNewBtn) {
                        // Xóa tất cả event listener cũ để tránh trùng lặp
                        const newBtn = createNewBtn.cloneNode(true);
                        createNewBtn.parentNode.replaceChild(newBtn, createNewBtn);

                        // Thêm event listener mới
                        newBtn.addEventListener('click', function() {
                            const productUniqueId = this.getAttribute('data-unique-id');
                            const productId = this.getAttribute('data-product-id');
                            console.log('Create new product button clicked:', {
                                productId,
                                productUniqueId
                            });
                            showCreateNewProductModal(productUniqueId);
                        });
                    }
                }, 100);
            }

            // Function to check and show create new product button
            function checkAndShowCreateNewProductButton(productUniqueId) {
                const componentBlock = document.getElementById('component_block_' + productUniqueId);
                if (!componentBlock) {
                    console.log(`Component block not found for ${productUniqueId}`);
                    return;
                }

                const isModified = checkComponentsModified(productUniqueId);
                const isDuplicate = selectedProducts.filter(p => p.uniqueId === productUniqueId).length > 1;

                console.log(`Product ${productUniqueId} status:`, {
                    isModified,
                    isDuplicate
                });

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
                    addCreateNewProductButton(componentBlock, productId, productUniqueId);
                } else {
                    // Remove the button if no longer modified and not duplicate
                    const existingSection = componentBlock.querySelector('.duplicate-section');
                    if (existingSection) {
                        console.log(`Removing create new product button for ${productUniqueId}`);
                        existingSection.remove();
                    }
                }
            }

            // Function to show create new product modal/alert
            function showCreateNewProductModal(productUniqueId) {
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
                const productComponents = selectedComponents.filter(c => c.productId === productUniqueId);

                if (!product || productComponents.length === 0) return;

                // Create a summary of the modified formula
                let formulaSummary = 'Công thức mới:\n';
                productComponents.forEach(comp => {
                    const isModified = comp.quantity !== comp.originalQuantity;
                    const status = isModified ? ` (đã thay đổi từ ${comp.originalQuantity})` : '';
                    formulaSummary += `- ${comp.name}: ${comp.quantity}${status}\n`;
                });

                // Show confirmation dialog using SweetAlert2 instead of confirm
                Swal.fire({
                    title: 'Xác nhận tạo thành phẩm mới',
                    html: `Bạn có muốn tạo thành phẩm mới <strong>"${product.name} (Modified)"</strong> với công thức sau?<br><br><pre style="text-align:left;background:#f5f5f5;padding:10px;max-height:200px;overflow-y:auto">${formulaSummary}</pre>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Tạo thành phẩm',
                    cancelButtonText: 'Hủy',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Find the create new product button
                        const componentBlock = document.getElementById('component_block_' +
                            productUniqueId);
                        if (componentBlock) {
                            const createNewBtn = componentBlock.querySelector('.create-new-product-btn');
                            if (createNewBtn && !createNewBtn.disabled) {
                                console.log(
                                    'Calling handleCreateNewProduct from showCreateNewProductModal');
                                // Call handleCreateNewProduct with the button element
                                if (typeof window.handleCreateNewProduct === "function") {
                                    window.handleCreateNewProduct(createNewBtn);
                                } else {
                                    console.error(
                                        'handleCreateNewProduct function not found in window scope');
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Lỗi',
                                        text: 'Không tìm thấy hàm xử lý tạo thành phẩm.',
                                        confirmButtonText: 'Đóng'
                                    });
                                }
                            } else if (createNewBtn && createNewBtn.disabled) {
                                console.log('Button is disabled, product already created');
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Thông báo',
                                    text: 'Thành phẩm này đã được tạo trước đó.',
                                    confirmButtonText: 'Đóng'
                                });
                            } else {
                                console.error('Create new product button not found');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Lỗi',
                                    text: 'Không tìm thấy nút tạo thành phẩm mới.',
                                    confirmButtonText: 'Đóng'
                                });
                            }

                            // Update UI to show that a new product will be created
                            const header = componentBlock.querySelector('h4');
                            if (header && !header.textContent.includes('(Sẽ tạo mới)')) {
                                header.innerHTML = header.innerHTML +
                                    ' <span class="text-green-600">(Sẽ tạo mới)</span>';
                            }
                        }
                    }
                });
            }

            // Validation trước khi submit
            document.querySelector('form').addEventListener('submit', function(e) {
                // Update hidden form data before validation
                updateHiddenComponentList();
                updateHiddenProductList();

                // Debug: Log data structure before submit
                console.log('Submit Data Debug:', {
                    selectedProducts: selectedProducts,
                    selectedComponents: selectedComponents,
                    hiddenProductInputs: Array.from(document.querySelectorAll(
                        'input[name^="products["]')).map(i => ({
                        name: i.name,
                        value: i.value
                    })),
                    hiddenComponentInputs: Array.from(document.querySelectorAll(
                        'input[name^="components["]')).map(i => ({
                        name: i.name,
                        value: i.value
                    }))
                });

                // Kiểm tra mã phiếu có hợp lệ không
                const assemblyCode = document.getElementById('assembly_code').value.trim();
                if (!assemblyCode) {
                    e.preventDefault();
                    alert('Vui lòng nhập mã phiếu lắp ráp!');
                    return false;
                }

                // Kiểm tra xem có đang hiển thị lỗi trùng mã không
                const hasCodeError = document.querySelector('.assembly-code-error');
                if (hasCodeError) {
                    e.preventDefault();
                    alert('Mã phiếu đã tồn tại! Vui lòng thay đổi mã phiếu trước khi lưu.');
                    return false;
                }

                // Kiểm tra có ít nhất một thành phẩm
                if (selectedProducts.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một thành phẩm vào phiếu lắp ráp!');
                    return false;
                }

                // Kiểm tra số lượng thành phẩm phải hợp lệ
                for (let i = 0; i < selectedProducts.length; i++) {
                    const product = selectedProducts[i];
                    const quantity = parseInt(product.quantity);
                    if (!quantity || quantity < 1) {
                        e.preventDefault();
                        alert(`Số lượng thành phẩm "${product.name}" phải là số nguyên dương!`);
                        return false;
                    }
                }

                // Validate serials for each product (only check for duplicates if serials provided)
                let hasSerialError = false;

                selectedProducts.forEach((product, index) => {
                    // Check for duplicate serials within this product (only if serials are provided)
                    const serialValues = product.serials.filter(s => s && s.trim() !== '');

                    if (serialValues.length > 0) {
                        const uniqueSerials = new Set(serialValues);
                        if (serialValues.length !== uniqueSerials.size) {
                            hasSerialError = true;
                        }
                    }
                });

                if (hasSerialError) {
                    e.preventDefault();
                    alert('Phát hiện trùng lặp serial thành phẩm. Vui lòng kiểm tra lại!');
                    return false;
                }



                if (selectedComponents.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một vật tư vào phiếu lắp ráp!');
                    return false;
                }

                // Kiểm tra số lượng và tồn kho
                let hasStockError = false;
                let errorMessages = [];

                for (const component of selectedComponents) {
                    // Kiểm tra số lượng phải lớn hơn 0
                    const quantity = parseInt(component.quantity);
                    if (!quantity || quantity < 1 || isNaN(quantity)) {
                        e.preventDefault();
                        alert(`Số lượng linh kiện "${component.name}" phải là số nguyên dương!`);
                        return false;
                    }

                    // Kiểm tra lại tồn kho
                    checkStockSufficiency(component);
                    if (!component.isStockSufficient) {
                        hasStockError = true;
                        errorMessages.push(
                            '- ' + component.code + ': ' + component.name + ' - ' + component
                            .stockWarning);
                    }
                }

                // Nếu có lỗi tồn kho, hiển thị thông báo và ngăn submit form
                if (hasStockError) {
                    e.preventDefault();
                    alert('Không thể tạo phiếu lắp ráp do không đủ tồn kho:\n' + errorMessages.join('\n'));
                    return false;
                }

                return true;
            });

            // Add event listener to update product dropdown for components when products change
            addProductBtn.addEventListener('click', function() {
                setTimeout(() => updateComponentProductDropdown(), 0);
            });

            // Initialize the component selection by updating the product dropdown
            updateComponentProductDropdown();

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

                    // Update the component list to reflect changes
                    updateComponentList();
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

            // Enhanced form validation to check for serial errors
            const originalFormValidation = document.querySelector('form').onsubmit;
            document.querySelector('form').addEventListener('submit', function(e) {
                // Check for serial validation errors
                const serialErrors = document.querySelectorAll('.serial-validation-msg');
                if (serialErrors.length > 0) {
                    e.preventDefault();
                    alert('Vui lòng sửa các lỗi serial trước khi lưu phiếu lắp ráp!');
                    // Focus on first error
                    const firstErrorInput = serialErrors[0].parentNode.querySelector('input');
                    if (firstErrorInput) {
                        firstErrorInput.focus();
                    }
                    return false;
                }
            });

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

                    // Add empty row back
                    componentList.appendChild(emptyRow);

                    // Show or hide the empty row
                    if (components.length > 0) {
                        emptyRow.style.display = 'none';
                    } else {
                        emptyRow.style.display = '';
                        continue; // Skip to next product if no components
                    }

                    // Add each component
                    components.forEach((component, index) => {
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
                            '<input type="number" min="1" step="1" name="components[' + index +
                            '][quantity]" value="' + (
                                component.quantity || 1) + '"' +
                            ' class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input"' +
                            ' data-component-index="' + selectedComponents.indexOf(component) + '">';

                        // Set row HTML
                        row.innerHTML =
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
                            '<input type="hidden" name="components[' + index + '][id]" value="' + component
                            .id + '">' +
                            '<input type="hidden" name="components[' + index + '][product_id]" value="' +
                            component
                            .productId + '">' +
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
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 serial-cell">' +
                            '<!-- Serial inputs will be added here -->' +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' +
                            '<input type="text" name="components[${index}][note]" value="' + (component
                                .note ||
                                '') + '"' +
                            ' class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 note-input"' +
                            ' placeholder="Ghi chú" data-component-index="' + selectedComponents.indexOf(
                                component) + '">' +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">' +
                            '<button type="button" class="text-red-500 hover:text-red-700 delete-component" ' +
                            'data-product-id="' + component.productId + '" data-component-id="' + component
                            .id + '">' +
                            '<i class="fas fa-trash"></i>' +
                            '</button>' +
                            '</td>';

                        componentList.appendChild(row);

                        // Add serial inputs for this component
                        const serialCell = row.querySelector('.serial-cell');
                        addSerialInputsToCell(serialCell, component, index);

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

            // Add event listener for product quantity changes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('product-quantity-input')) {
                    const input = e.target;
                    const quantity = parseInt(input.value) || 1;
                    const productRow = input.closest('tr');
                    const productId = productRow.getAttribute('data-product-id');
                    const index = parseInt(input.getAttribute('data-index'));

                    // Lấy product đúng từ selectedProducts
                    const product = selectedProducts[index];
                    if (!product) return;

                    // Cập nhật lại số lượng
                    product.quantity = quantity;

                    // Gọi hàm update nếu có
                    if (typeof window.updateProductQuantity === "function") {
                        window.updateProductQuantity(input);
                    }

                    // Ẩn phần header, thead của linh kiện nếu có
                    const componentBlock = document.getElementById('component_block_' + product.uniqueId);
                    if (componentBlock) {
                        const header = componentBlock.querySelector('div.bg-blue-50');
                        const thead = componentBlock.querySelector('thead');
                        if (header) header.style.display = 'none';
                        if (thead) thead.style.display = 'none';
                    }

                    // Xử lý phần nhập Serial
                    const serialCell = document.getElementById(`${productId}_serials`);
                    if (!serialCell) return;

                    // Khởi tạo hoặc cập nhật mảng serials
                    product.serials = product.serials || [];
                    while (product.serials.length < quantity) {
                        product.serials.push('');
                    }
                    product.serials = product.serials.slice(0, quantity);

                    // Xóa input cũ
                    serialCell.innerHTML = '';

                    // Tạo lại input serial mới
                    const container = document.createElement('div');
                    container.className = 'space-y-2';
                    for (let i = 0; i < quantity; i++) {
                        const input = document.createElement('input');
                        input.type = 'text';
                        input.name = `products[${index}][serials][]`;
                        input.value = product.serials[i];
                        input.placeholder = `Serial ${i + 1}`;
                        input.className =
                            'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';
                        container.appendChild(input);
                    }

                    serialCell.appendChild(container);
                }
            });

            // Function to mark a product as already created
            function markProductAsCreated(productUniqueId) {
                // Tìm nút tạo thành phẩm mới
                const componentBlock = document.getElementById('component_block_' + productUniqueId);
                if (!componentBlock) return;

                const createNewBtn = componentBlock.querySelector('.create-new-product-btn');
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
                        createdProducts[productUniqueId] = true;
                        localStorage.setItem('createdProducts', JSON.stringify(createdProducts));
                    } catch (e) {
                        console.error('Error saving to localStorage:', e);
                    }
                }
            }

            // Function to reload serials for a group of selects with the same material and product unit
            async function reloadSerialsForMaterialGroup(materialId, selectElements, productUnit = '0') {
                if (!warehouseSelect || !warehouseSelect.value) {
                    return;
                }

                const warehouseId = warehouseSelect.value;
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
                if (!warehouseSelect || !warehouseSelect.value) {
                    return; // Don't load if no warehouse selected
                }

                const warehouseId = warehouseSelect.value;

                // Add loading indicator
                const loadingOption = document.createElement('option');
                loadingOption.textContent = 'Đang tải...';
                loadingOption.disabled = true;

                // Clear and add loading option
                while (selectElement.children.length > 1) {
                    selectElement.removeChild(selectElement.lastChild);
                }
                selectElement.appendChild(loadingOption);

                try {
                    const response = await fetch('{{ route('assemblies.material-serials') }}?' +
                        new URLSearchParams({
                            material_id: component.id,
                            warehouse_id: warehouseId
                        }));

                    const data = await response.json();

                    // Remove loading option
                    selectElement.removeChild(loadingOption);

                    if (data.success && data.serials.length > 0) {
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
                        data.serials.forEach(serial => {
                            // Skip this serial if it's already selected in another dropdown of the same material
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
                        // No serials available
                        const noSerialOption = document.createElement('option');
                        noSerialOption.textContent = 'Không có serial khả dụng';
                        noSerialOption.disabled = true;
                        selectElement.appendChild(noSerialOption);
                    }
                } catch (error) {
                    console.error('Error loading serials:', error);
                    // Remove loading option and show error
                    if (selectElement.contains(loadingOption)) {
                        selectElement.removeChild(loadingOption);
                    }
                    const errorOption = document.createElement('option');
                    errorOption.textContent = 'Lỗi tải serial';
                    errorOption.disabled = true;
                    selectElement.appendChild(errorOption);
                }
            }

            // Function to load serials for select dropdown
            async function loadSerialsForSelect(selectElement, serialIdInput, component, index) {
                if (!warehouseSelect || !warehouseSelect.value) {
                    return; // Don't load if no warehouse selected
                }

                const warehouseId = warehouseSelect.value;

                // Add loading indicator
                const loadingOption = document.createElement('option');
                loadingOption.textContent = 'Đang tải...';
                loadingOption.disabled = true;

                // Clear and add loading option
                while (selectElement.children.length > 1) {
                    selectElement.removeChild(selectElement.lastChild);
                }
                selectElement.appendChild(loadingOption);

                try {
                    const response = await fetch('{{ route('assemblies.material-serials') }}?' +
                        new URLSearchParams({
                            material_id: component.id,
                            warehouse_id: warehouseId
                        }));

                    const data = await response.json();

                    // Remove loading option
                    selectElement.removeChild(loadingOption);

                    if (data.success && data.serials.length > 0) {
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
                        data.serials.forEach(serial => {
                            // Skip this serial if it's already selected in another dropdown of the same material
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
                        // No serials available
                        const noSerialOption = document.createElement('option');
                        noSerialOption.textContent = 'Không có serial khả dụng';
                        noSerialOption.disabled = true;
                        selectElement.appendChild(noSerialOption);
                    }
                } catch (error) {
                    console.error('Error loading serials:', error);
                    // Remove loading option and show error
                    if (selectElement.contains(loadingOption)) {
                        selectElement.removeChild(loadingOption);
                    }
                    const errorOption = document.createElement('option');
                    errorOption.textContent = 'Lỗi tải serial';
                    errorOption.disabled = true;
                    selectElement.appendChild(errorOption);
                }
            }

            // Function to show serial dropdown
            async function showSerialDropdown(input, serialIdInput, component, index) {
                if (!warehouseSelect || !warehouseSelect.value) {
                    alert('Vui lòng chọn kho xuất trước!');
                    return;
                }

                const warehouseId = warehouseSelect.value;

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
        });
    </script>
</body>

</html>
