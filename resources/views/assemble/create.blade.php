<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tạo phiếu lắp ráp - SGL</title>
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
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Chọn thành phẩm --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                                            data-code="{{ $product->code }}">
                                            [{{ $product->code }}] {{ $product->name }}</option>
                                    @endforeach
                                </select>
                                <div class="w-24">
                                    <input type="number" id="product_add_quantity" min="1" step="1"
                                        value="1" required
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="warehouse_id"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Kho
                                xuất
                                <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho xuất vật tư --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}
                                        ({{ $warehouse->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="target_warehouse_container">
                            <label for="target_warehouse_id"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Kho nhập
                                <span class="text-red-500">*</span></label>
                            <select id="target_warehouse_id" name="target_warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho nhập thành phẩm --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}
                                        ({{ $warehouse->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

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
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
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
        document.addEventListener('DOMContentLoaded', function() {
            // Generate unique assembly code on page load
            generateUniqueAssemblyCode();

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

            let selectedComponents = [];
            let selectedProducts = []; // Danh sách thành phẩm đã chọn
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
                    isOriginal: false // Not from original BOM
                };

                selectedComponents.push(newComponent);

                // Update component list and check for changes
                updateProductComponentList(productUniqueId);
                checkAndShowCreateNewProductButton(productUniqueId);

                // Fetch stock data if warehouse is selected
                if (warehouseSelect.value) {
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
                    targetWarehouseContainer.classList.remove('hidden');
                    targetWarehouseIdSelect.setAttribute('required', 'required');
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

                // Tạo bảng linh kiện
                const tableContainer = document.createElement('div');
                tableContainer.className = 'overflow-x-auto';
                tableContainer.innerHTML =
                    '<table class="min-w-full divide-y divide-gray-200">' +
                    '    <thead class="bg-gray-50">' +
                    '        <tr>' +
                    '            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' +
                    '                Mã' +
                    '            </th>' +
                    '            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' +
                    '                Loại vật tư' +
                    '            </th>' +
                    '            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' +
                    '                Tên vật tư' +
                    '            </th>' +
                    '            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' +
                    '                Số lượng' +
                    '            </th>' +
                    '            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' +
                    '                Serial' +
                    '            </th>' +
                    '            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' +
                    '                Ghi chú' +
                    '            </th>' +
                    '            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' +
                    '                Thao tác' +
                    '            </th>' +
                    '        </tr>' +
                    '    </thead>' +
                    '    <tbody id="component_list_' + product.uniqueId +
                    '" class="bg-white divide-y divide-gray-200">' +
                    '        <tr id="no_components_row_' + product.uniqueId + '">' +
                    '            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">' +
                    '                Chưa có linh kiện nào cho thành phẩm này' +
                    '            </td>' +
                    '        </tr>' +
                    '    </tbody>' +
                    '</table>';

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
                                        actualProductId: product.id, // Store actual product ID for backend
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
                                if (warehouseSelect.value) {
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

                        // Update component quantity in selectedComponents
                        const component = selectedComponents.find(c =>
                            c.id == componentId && c.productId === productId
                        );
                        if (component) {
                            const oldQuantity = component.quantity;
                            component.quantity = newQuantity;

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
                                const serialCell = row.querySelector('.serial-cell');
                                const componentIndex = Array.from(componentListElement
                                    .querySelectorAll('.component-row')).indexOf(row);
                                addSerialInputsToCell(serialCell, component, componentIndex);
                            }

                        // Check stock sufficiency
                            checkStockSufficiency(component);

                            // Check for changes and update UI
                            checkAndShowCreateNewProductButton(productId);
                            updateProductComponentList(productId);
                        }
                    });
                });
            }

            // Function to check stock sufficiency
            function checkStockSufficiency(component) {
                if (!warehouseSelect.value) {
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
                const warehouseId = warehouseSelect.value;
                if (!warehouseId || selectedComponents.length === 0) {
                    warehouseStockData = {};
                    return;
                }

                // Get unique material IDs from all components
                const materialIds = [...new Set(selectedComponents.map(c => c.id))];

                try {
                    const response = await fetch(`/assemblies/warehouse-stock/${warehouseId}`, {
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
                             data-category="${material.category}"
                             data-unit="${material.unit || ''}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-medium text-sm">[${material.code}] ${material.name}</div>
                                    <div class="text-xs text-gray-500">${material.category} ${material.unit ? '- ' + material.unit : ''}</div>
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
                        selectedComponentToAdd = {
                            id: parseInt(this.getAttribute('data-id')),
                            code: this.getAttribute('data-code'),
                            name: this.getAttribute('data-name'),
                            category: this.getAttribute('data-category'),
                            unit: this.getAttribute('data-unit')
                        };

                        componentSearchInput.value =
                            `[${selectedComponentToAdd.code}] ${selectedComponentToAdd.name}`;
                        searchResults.classList.add('hidden');
                    });
                });
            }

            // Add event listener for delete component buttons
            document.addEventListener('click', function(e) {
                if (e.target.closest('.delete-component')) {
                    e.preventDefault();
                    const btn = e.target.closest('.delete-component');
                    const productId = btn.getAttribute('data-product-id');
                    const componentId = btn.getAttribute('data-component-id');

                    // Remove component from selectedComponents
                    selectedComponents = selectedComponents.filter(c =>
                        !(c.id == componentId && c.productId === productId)
                    );

                    // Update UI and check for changes
                    updateProductComponentList(productId);
                    checkAndShowCreateNewProductButton(productId);
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
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 serial-cell" id="' +
                        product.uniqueId + '_serials">' +
                        '<!-- Serial inputs will be added here -->' +
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

                        // Adjust serials array length if quantity changed
                        if (newQty > product.serials.length) {
                            const additionalSerials = Array(newQty - product.serials.length).fill(
                                '');
                            product.serials = [...product.serials, ...additionalSerials];
                        } else if (newQty < product.serials.length) {
                            product.serials = product.serials.slice(0, newQty);
                        }

                        // Regenerate serial inputs
                        generateProductSerialInputs(product, index, serialCell);

                        // Update hidden inputs when quantity changes
                        updateHiddenProductList();

                        // Re-check stock when product quantity changes
                        if (warehouseSelect.value) {
                            fetchWarehouseStockData();
                        }
                    });

                    // Don't add event listener here - use event delegation instead
                });

                // Update hidden product inputs for form submission
                updateHiddenProductList();
            }

            // Hàm tạo các trường nhập serial cho thành phẩm
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
                    }
                } else {
                    componentProductSelect.disabled = false;
                    addComponentBtn.disabled = false;
                    addComponentBtn.classList.remove('opacity-50');
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
                    // Nếu chưa có dữ liệu, lấy từ API
                    const warehouseId = warehouseSelect.value;
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
                        selectedMaterial = material;
                        componentSearchInput.value = material.code + ' - ' + material.name;
                        searchResults.classList.add('hidden');
                    });

                    searchResults.appendChild(resultItem);
                });

                searchResults.classList.remove('hidden');
            }

            // Xử lý thêm linh kiện khi nhấn nút "Thêm"
            addComponentBtn.addEventListener('click', addSelectedComponent);

            // Thêm sự kiện Enter trong ô nhập liệu linh kiện để thêm linh kiện nhanh
            componentSearchInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter' && selectedMaterial && componentProductSelect.value) {
                    event.preventDefault(); // Ngăn chặn hành vi mặc định của Enter
                    addSelectedComponent(); // Gọi hàm thêm linh kiện
                }
            });

            // Add selected component function
            function addSelectedComponent() {
                if (selectedProducts.length === 0) {
                    alert('Vui lòng thêm thành phẩm trước khi thêm linh kiện!');
                    return;
                }
                const selectedProductId = componentProductSelect.value;
                const selectedProduct = selectedProducts.find(p => p.uniqueId === selectedProductId);
                if (!selectedProduct) return;

                // Kiểm tra xem linh kiện đã tồn tại chưa
                if (selectedComponents.some(c => c.id === selectedMaterial.id && c.productId ===
                        selectedProductId)) {
                    alert('Linh kiện này đã được thêm vào thành phẩm!');
                    return;
                }

                const quantity = parseInt(componentAddQuantity.value) || 1;

                const newComponent = {
                    id: selectedMaterial.id,
                    code: selectedMaterial.code,
                    name: selectedMaterial.name,
                    category: selectedMaterial.category,
                    quantity: quantity,
                    originalQuantity: quantity, // Store original quantity
                    stock_quantity: selectedMaterial.stock_quantity || 0,
                    serial: '',
                    serials: [],
                    note: '',
                    productId: selectedProductId,
                    actualProductId: selectedProduct.id, // Store actual product ID for backend
                    productName: selectedProduct.name,
                    isEditable: true // Make component quantities editable
                };

                // Check stock sufficiency
                checkStockSufficiency(newComponent);

                selectedComponents.push(newComponent);

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

                // Cập nhật giao diện
                updateComponentList();

                // Tự động cuộn đến block của thành phẩm
                const componentBlock = document.getElementById('component_block_' + selectedProductId);
                if (componentBlock) {
                    componentBlock.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }

                // Clear search
                componentSearchInput.value = '';
                componentAddQuantity.value = '1';
                searchResults.classList.add('hidden');
                selectedMaterial = null;
            }

            // Update component quantities based on product quantity
            function updateComponentQuantities() {
                selectedComponents.forEach(component => {
                    // Only recalculate if component was not manually adjusted
                    if (!component.manuallyAdjusted) {
                        // Recalculate component quantity based on product quantities
                        let newQty = 0;
                        selectedProducts.forEach(product => {
                            newQty += parseInt(product.quantity) || 1;
                        });

                        component.quantity = newQty > 0 ? newQty : 1;
                    }

                    // Always check stock sufficiency when product quantity changes
                    checkStockSufficiency(component);
                });

                updateComponentList();
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
                            '<input type="text" name="components[' + index + '][note]" value="' + (component
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

                        // Add serial inputs for this component
                        const serialCell = row.querySelector('.serial-cell');
                        addSerialInputsToCell(serialCell, component, index);

                        // Add event listeners for quantity input
                        const quantityInput = row.querySelector('.quantity-input');
                        quantityInput.addEventListener('change', function() {
                            const index = parseInt(this.getAttribute('data-component-index'));
                            const comp = selectedComponents[index];
                            if (comp) {
                                const oldQuantity = comp.quantity;
                                const newQuantity = parseInt(this.value) || 1;
                                comp.quantity = newQuantity;
                                comp.manuallyAdjusted = true;

                                // Update serial inputs immediately if quantity changed
                                if (oldQuantity !== newQuantity) {
                                    // Adjust serials array length
                                    if (newQuantity === 1) {
                                        // If quantity becomes 1, use first serial from array as single serial
                                        comp.serial = (comp.serials && comp.serials[0]) || '';
                                        comp.serials = [];
                                    } else if (newQuantity > 1) {
                                        // If quantity > 1, ensure serials array has correct length
                                        if (!comp.serials) comp.serials = [];

                                        // If switching from single to multiple, move serial to first array position
                                        if (oldQuantity === 1 && comp.serial) {
                                            comp.serials[0] = comp.serial;
                                            comp.serial = '';
                                        }

                                        // Adjust array length
                                        if (newQuantity > comp.serials.length) {
                                            // Add empty serials if quantity increased
                                            const additionalSerials = Array(newQuantity - comp
                                                .serials.length).fill('');
                                            comp.serials = [...comp.serials, ...additionalSerials];
                                        } else if (newQuantity < comp.serials.length) {
                                            // Trim array if quantity decreased
                                            comp.serials = comp.serials.slice(0, newQuantity);
                                        }
                                    }

                                    // Update serial inputs in the current row
                                    const serialCell = row.querySelector('.serial-cell');
                                    addSerialInputsToCell(serialCell, comp, index);
                                }

                                // Check if quantity differs from original formula
                                checkAndShowCreateNewProductButton(comp.productId);

                                checkStockSufficiency(comp);

                                // Update stock warning display in current row
                                const stockWarningDiv = row.querySelector('.stock-warning');
                                if (stockWarningDiv) {
                                    stockWarningDiv.remove();
                                }

                                if (comp.stockWarning) {
                                    const stockWarning = document.createElement('div');
                                    stockWarning.className =
                                        'text-xs text-red-500 mt-1 stock-warning';
                                    stockWarning.textContent = 'Không đủ tồn kho: ' + comp
                                        .stock_quantity + ' < ' + comp.quantity;
                                    this.parentNode.appendChild(stockWarning);
                                }

                                // Update hidden component list for form submission  
                                updateHiddenComponentList();
                                updateHiddenProductList();
                            }
                        });

                        // Add event listeners for note input
                        const noteInput = row.querySelector('.note-input');
                        if (noteInput) {
                            noteInput.addEventListener('input', function() {
                                const index = parseInt(this.getAttribute('data-component-index'));
                                const comp = selectedComponents[index];
                                if (comp) {
                                    comp.note = this.value;
                                }
                            });
                        }

                        // Add event listeners for delete button
                        const deleteBtn = row.querySelector('.delete-component');
                        deleteBtn.addEventListener('click', function() {
                            const index = parseInt(this.getAttribute('data-index'));
                            if (index >= 0 && index < selectedComponents.length) {
                                selectedComponents.splice(index, 1);
                                updateComponentList();
                            }
                        });
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
                    } else {
                        // Single serial
                        serialHtml = '<input type="hidden" name="components[' + index +
                            '][serial]" value="' + (component.serial || '') + '">';
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
                        serialHtml +
                        '<input type="hidden" name="components[' + index + '][note]" value="' + (component
                            .note || '') +
                        '">' +
                        '</td>';

                    oldComponentList.insertBefore(row, noComponentsRow);
                });
            }

            // Update hidden product list for form submission
            function updateHiddenProductList() {
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
            function addSerialInputsToCell(cell, component, index) {
                // Clear existing content
                cell.innerHTML = '';

                const quantity = parseInt(component.quantity) || 1;

                // If quantity is 1, show single serial input
                if (quantity === 1) {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'components[' + index + '][serial]';
                    input.value = component.serial || '';
                    input.placeholder = 'Serial (tùy chọn)';
                    input.className =
                        'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                    input.addEventListener('input', function() {
                        component.serial = this.value;
                    });

                    cell.appendChild(input);
                } else {
                    // If quantity > 1, show multiple serial inputs
                    // Ensure serials array exists and has correct length
                    if (!component.serials) component.serials = [];

                    for (let i = 0; i < quantity; i++) {
                        const serialDiv = document.createElement('div');
                        serialDiv.className = 'mb-1';

                        const input = document.createElement('input');
                        input.type = 'text';
                        input.name = 'components[' + index + '][serials][]';
                        input.value = (component.serials && component.serials[i]) || '';
                        input.placeholder = 'Serial ' + (i + 1) + ' (tùy chọn)';
                        input.className =
                            'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                        input.addEventListener('input', function() {
                            if (!component.serials) component.serials = [];
                            component.serials[i] = this.value;
                        });

                        serialDiv.appendChild(input);
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
                const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                if (!product || !product.originalComponents) {
                    // Fallback to old logic if no original components available
                const productComponents = selectedComponents.filter(c => c.productId === productUniqueId);
                return productComponents.some(component => component.quantity !== component.originalQuantity);
                }

                const currentComponents = selectedComponents.filter(c => c.productId === productUniqueId);
                const originalComponents = product.originalComponents;

                // Check if number of components changed
                if (currentComponents.length !== originalComponents.length) {
                    return true;
                }

                // Check each component for changes
                for (let current of currentComponents) {
                    const original = originalComponents.find(o => o.id === current.id);
                    if (!original) {
                        // New component added
                        return true;
                    } else if (original.quantity !== current.quantity) {
                        // Quantity changed
                        return true;
                    }
                }

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
                        createNewBtn.addEventListener('click', function() {
                            const productUniqueId = this.getAttribute('data-unique-id');
                            showCreateNewProductModal(productUniqueId);
                        });
                    }
                }, 100);
            }

            // Function to check and show create new product button
            function checkAndShowCreateNewProductButton(productUniqueId) {
                const componentBlock = document.getElementById('component_block_' + productUniqueId);
                if (!componentBlock) return;

                const isModified = checkComponentsModified(productUniqueId);
                const isDuplicate = selectedProducts.filter(p => p.uniqueId === productUniqueId).length > 1;

                if (isModified || isDuplicate) {
                    // Extract product ID from uniqueId (e.g., "product_1" -> "1")
                    const productId = productUniqueId.split('_')[1];
                    addCreateNewProductButton(componentBlock, productId, productUniqueId);
                } else {
                    // Remove the button if no longer modified and not duplicate
                    const existingSection = componentBlock.querySelector('.duplicate-section');
                    if (existingSection) {
                        existingSection.remove();
                    }
                }
            }

            // Function to show create new product modal/alert
            function showCreateNewProductModal(productUniqueId) {
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

                // Show confirmation dialog
                const confirmed = confirm(
                    `Bạn có muốn tạo thành phẩm mới "${product.name} (Modified)" với công thức sau?\n\n${formulaSummary}\n` +
                    `Chức năng này sẽ lưu công thức mới vào hệ thống để sử dụng cho các lần lắp ráp tiếp theo.`
                );

                if (confirmed) {
                    // Here you would normally send the data to backend to create new product
                    alert(
                        'Đã ghi nhận yêu cầu tạo thành phẩm mới. Chức năng này sẽ được phát triển trong phiên bản tiếp theo.'
                    );

                    // Optional: You could also update the UI to show that a new product will be created
                    const componentBlock = document.getElementById('component_block_' + productUniqueId);
                    if (componentBlock) {
                        const header = componentBlock.querySelector('h4');
                        if (header && !header.textContent.includes('(Sẽ tạo mới)')) {
                            header.innerHTML = header.innerHTML +
                                ' <span class="text-green-600">(Sẽ tạo mới)</span>';
                        }
                    }
                }
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
                    hiddenProductInputs: Array.from(document.querySelectorAll('input[name^="products["]')).map(i => ({name: i.name, value: i.value})),
                    hiddenComponentInputs: Array.from(document.querySelectorAll('input[name^="components["]')).map(i => ({name: i.name, value: i.value}))
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

            // Thêm event listener cho cả hai dropdown kho
            warehouseSelect.addEventListener('change', function() {
                fetchWarehouseMaterials(this.value);

                // Fetch stock data when warehouse changes
                if (this.value && selectedComponents.length > 0) {
                    fetchWarehouseStockData();
                } else {
                    // Clear stock data when no warehouse selected
                    warehouseStockData = {};
                    updateAllStockWarnings();
                }
            });

            // Khởi tạo: tải danh sách linh kiện của kho nếu đã chọn kho
            if (warehouseSelect.value) {
                fetchWarehouseMaterials(warehouseSelect.value);

                // Also fetch stock data if there are components
                if (selectedComponents.length > 0) {
                    fetchWarehouseStockData();
                }
            }

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

        });
    </script>
</body>

</html>
