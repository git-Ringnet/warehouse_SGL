<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                            <input type="text" id="assembly_code" name="assembly_code"
                                value="LR{{ date('Ymd') }}-001"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2">
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
                                        <option value="{{ $product->id }}" data-name="{{ $product->name }}">
                                            {{ $product->name }}</option>
                                    @endforeach
                                </select>
                                <div class="w-24">
                                    <input type="number" id="product_add_quantity" min="1" value="1"
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
                                <option value="Nguyễn Văn A">Nguyễn Văn A</option>
                                <option value="Trần Thị B">Trần Thị B</option>
                                <option value="Lê Văn C">Lê Văn C</option>
                                <option value="Phạm Thị D">Phạm Thị D</option>
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
                                <option value="Nguyễn Văn A">Nguyễn Văn A</option>
                                <option value="Trần Thị B">Trần Thị B</option>
                                <option value="Lê Văn C">Lê Văn C</option>
                                <option value="Phạm Thị D">Phạm Thị D</option>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng
                                thái <span class="text-red-500">*</span></label>
                            <select id="status" name="status" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="in_progress">Đang thực hiện</option>
                                <option value="completed">Hoàn thành</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho
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
                                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1 required">Dự
                                    án <span class="text-red-500">*</span></label>
                                <select id="project_id" name="project_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Chọn dự án --</option>
                                    @foreach ($projects ?? [] as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                                <input type="number" id="component_add_quantity" min="1" value="1"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <button id="add_component_btn"
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
            const componentSearchInput = document.getElementById('component_search');
            const componentAddQuantity = document.getElementById('component_add_quantity');
            const componentProductSelect = document.getElementById('component_product_id');
            const addComponentBtn = document.getElementById('add_component_btn');
            const componentList = document.getElementById('component_list');
            const noComponentsRow = document.getElementById('no_components_row');
            const submitBtn = document.getElementById('submit-btn');
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
            let searchTimeout = null;
            let selectedMaterial = null;
            let warehouseMaterials = [];
            let productCounter = 0; // Để đếm và tạo ID duy nhất cho mỗi thành phẩm

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

            // Ensure quantity is at least 1
            componentAddQuantity.addEventListener('change', function() {
                if (parseInt(this.value) < 1 || isNaN(parseInt(this.value))) {
                    this.value = '1';
                }
            });

            // Ensure product quantity is at least 1
            productAddQuantity.addEventListener('change', function() {
                if (parseInt(this.value) < 1 || isNaN(parseInt(this.value))) {
                    this.value = '1';
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

            // Function to fetch and add components for a product
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

                // Create duplicate product button if same product exists more than once
                const isDuplicate = selectedProducts.filter(p => p.id === productId).length > 1;
                if (isDuplicate) {
                    const duplicateSection = document.createElement('div');
                    duplicateSection.className = 'bg-yellow-50 border-t border-yellow-200 p-3';
                    duplicateSection.innerHTML = `
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-yellow-700">
                                <i class="fas fa-info-circle mr-2"></i>
                                Bạn đang tạo bản sao của một thành phẩm. Bạn có muốn tạo một thành phẩm mới với công thức này không?
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
                                alert('Chức năng này sẽ cho phép bạn tạo thành phẩm mới với công thức linh kiện đã chỉnh sửa.');
                                // Here you would implement the logic to create a new product
                            });
                        }
                    }, 100);
                }

                // Mock data for testing - this would be replaced with the actual API call in production
                const mockComponents = [
                    {
                        id: 1001,
                        code: 'LK001',
                        name: 'Bo mạch chủ',
                        category: 'Linh kiện điện tử',
                        pivot: { quantity: 1 },
                        stock_quantity: 5,
                        serial: '',
                    },
                    {
                        id: 1002,
                        code: 'LK002',
                        name: 'Bộ vi xử lý',
                        category: 'Linh kiện điện tử',
                        pivot: { quantity: 1 },
                        stock_quantity: 8,
                        serial: '',
                    },
                    {
                        id: 1003,
                        code: 'LK003',
                        name: 'Bộ nhớ RAM',
                        category: 'Linh kiện điện tử',
                        pivot: { quantity: 2 },
                        stock_quantity: 12,
                        serial: '',
                    },
                    {
                        id: 1004,
                        code: 'VT001',
                        name: 'Vỏ máy tính',
                        category: 'Phụ kiện',
                        pivot: { quantity: 1 },
                        stock_quantity: 4,
                        serial: '',
                    }
                ];

                // Process mock data (in production, this would be the .then handler for the API call)
                setTimeout(() => {
                    // Remove loading indicator
                    const loadingElement = document.getElementById('loading-components-' + productUniqueId);
                    if (loadingElement) loadingElement.remove();

                    // Process the components data
                    const components = mockComponents;

                    if (components.length === 0) {
                        // Show info message if no components
                        const infoMessage = document.createElement('div');
                        infoMessage.className = 'text-amber-500 text-sm p-3 text-center';
                        infoMessage.innerHTML =
                            '<i class="fas fa-info-circle"></i> Thành phẩm này không có linh kiện định sẵn.';
                        infoMessage.id = 'info-components-' + productUniqueId;
                        componentBlock.appendChild(infoMessage);

                        // Remove message after 3 seconds
                        setTimeout(() => {
                            const element = document.getElementById('info-components-' +
                                productUniqueId);
                            if (element) element.remove();
                        }, 3000);

                        return;
                    }

                    // Add each component to the selected components list
                    const product = selectedProducts.find(p => p.uniqueId === productUniqueId);
                    if (!product) return;

                    components.forEach(component => {
                        // Check if this component already exists for this product
                        if (selectedComponents.some(c => c.id == component.id && c.productId ===
                                productUniqueId)) {
                            return; // Skip if already added
                        }

                        const newComponent = {
                            id: component.id,
                            code: component.code,
                            name: component.name,
                            category: component.category || '',
                            quantity: component.pivot?.quantity || 1,
                            originalQuantity: component.pivot?.quantity || 1, // Store original quantity
                            stock_quantity: component.stock_quantity || 0,
                            serial: component.serial || '',
                            serials: [],
                            note: '',
                            productId: productUniqueId,
                            productName: product.name,
                            isEditable: true // Make component quantities editable
                        };

                        // Check stock sufficiency
                        checkStockSufficiency(newComponent);

                        selectedComponents.push(newComponent);
                    });

                    // Update the component list display
                    updateComponentList();
                }, 800);
            }

            // Function to check if stock is sufficient for a component
            function checkStockSufficiency(component) {
                // Get component quantity and stock
                const quantity = parseInt(component.quantity) || 1;
                const stockQuantity = parseInt(component.stock_quantity) || 0;
                
                // Check if stock is sufficient
                component.isStockSufficient = stockQuantity >= quantity;
                
                // Set warning message if needed
                if (!component.isStockSufficient) {
                    component.stockWarning = `Không đủ tồn kho (còn ${stockQuantity}, cần ${quantity})`;
                } else {
                    component.stockWarning = '';
                }
                
                return component.isStockSufficient;
            }

            // Hàm cập nhật danh sách thành phẩm
            function updateProductList() {
                console.log('updateProductList called, selectedProducts.length:', selectedProducts.length);
                
                // Xóa các hàng thành phẩm hiện tại (trừ hàng thông báo)
                const productRows = document.querySelectorAll('.product-row');
                console.log('Removing product rows:', productRows.length);
                productRows.forEach(row => row.remove());

                // Ẩn/hiển thị thông báo "không có thành phẩm"
                if (selectedProducts.length > 0) {
                    noProductsRow.style.display = 'none';
                } else {
                    console.log('No products left, showing empty message');
                    noProductsRow.style.display = '';
                    return; // Thoát sớm khi không có thành phẩm
                }

                // Thêm hàng cho mỗi thành phẩm đã chọn
                selectedProducts.forEach((product, index) => {
                    const row = document.createElement('tr');
                    row.className = 'product-row';

                    row.innerHTML =
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
                        '<input type="hidden" name="products[' + index + '][id]" value="' + product.id +
                        '">' +
                        '<input type="hidden" name="products[' + index + '][quantity]" value="' + product
                        .quantity + '">' +
                        (index + 1) +
                        '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' + product.name +
                        '</td>' +
                        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' +
                        '<input type="number" min="1" name="products[' + index + '][quantity]" value="' + (
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
                    qtyInput.addEventListener('change', function() {
                        const newQty = parseInt(this.value) || 1;
                        if (newQty < 1) this.value = product.quantity = 1;
                        else product.quantity = newQty;

                        // Adjust serials array length if quantity increased
                        if (newQty > product.serials.length) {
                            const additionalSerials = Array(newQty - product.serials.length).fill(
                                '');
                            product.serials = [...product.serials, ...additionalSerials];
                        } else if (newQty < product.serials.length) {
                            product.serials = product.serials.slice(0, newQty);
                        }

                        // Regenerate serial inputs
                        generateProductSerialInputs(product, index, serialCell);
                    });

                    // Don't add event listener here - use event delegation instead
                });
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
                    input.name = 'products[' + productIndex + '][serials][]';
                    input.value = product.serials[i] || '';
                    input.placeholder = 'Serial ' + (i + 1);
                    input.className =
                        'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                    // Save serial when typing
                    input.addEventListener('input', function() {
                        product.serials[i] = this.value;
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
                if (!selectedMaterial) {
                    alert('Vui lòng chọn linh kiện trước!');
                    return;
                }

                if (selectedProducts.length === 0) {
                    alert('Vui lòng thêm thành phẩm trước khi thêm linh kiện!');
                    return;
                }

                const selectedProductId = componentProductSelect.value;

                if (!selectedProductId) {
                    alert('Vui lòng chọn thành phẩm mà linh kiện này sẽ thuộc về!');
                    return;
                }

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
                            '<div class="text-xs text-red-500 mt-1">Không đủ tồn kho: ' + component.stock_quantity +
                            ' < ' + component.quantity + '</div>' :
                            '';
                        
                        // Editable quantity input
                        const quantityInputHtml = 
                            '<input type="number" min="1" name="components[' + index + '][quantity]" value="' + (
                                component.quantity || 1) + '"' +
                            ' class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input"' +
                            ' data-component-index="' + selectedComponents.indexOf(component) + '">';

                        // Set row HTML
                        row.innerHTML =
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
                            '<input type="hidden" name="components[' + index + '][id]" value="' + component.id + '">' +
                            '<input type="hidden" name="components[' + index + '][product_id]" value="' + component
                            .productId + '">' +
                            component.code +
                            '</td>' +
                            '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' + component.category +
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
                            '<input type="text" name="components[' + index + '][note]" value="' + (component.note ||
                            '') + '"' +
                            ' class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 note-input"' +
                            ' placeholder="Ghi chú" data-component-index="' + selectedComponents.indexOf(component) + '">' +
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
                                comp.quantity = parseInt(this.value) || 1;
                                comp.manuallyAdjusted = true;
                                
                                // Check if quantity differs from original formula
                                checkAndShowCreateNewProductButton(comp.productId);
                                
                                checkStockSufficiency(comp);
                                updateComponentList();
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

                    row.innerHTML =
                        '<td>' +
                        '<input type="hidden" name="components[' + index + '][id]" value="' + component.id + '">' +
                        '<input type="hidden" name="components[' + index + '][product_id]" value="' + component
                        .productId.replace('product_', '') + '">' +
                        '<input type="hidden" name="components[' + index + '][quantity]" value="' + (component
                            .quantity || 1) + '">' +
                        '<input type="hidden" name="components[' + index + '][serial]" value="' + (component.serial ||
                            '') + '">' +
                        '<input type="hidden" name="components[' + index + '][note]" value="' + (component.note || '') +
                        '">' +
                        '</td>';

                    oldComponentList.insertBefore(row, noComponentsRow);
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
                    input.placeholder = 'Nhập serial';
                    input.className = 'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';
                    
                    input.addEventListener('input', function() {
                        component.serial = this.value;
                    });
                    
                    cell.appendChild(input);
                } else {
                    // If quantity > 1, show multiple serial inputs
                    for (let i = 0; i < quantity; i++) {
                        const serialDiv = document.createElement('div');
                        serialDiv.className = 'mb-1';
                        
                        const input = document.createElement('input');
                        input.type = 'text';
                        input.name = 'components[' + index + '][serials][]';
                        input.value = (component.serials && component.serials[i]) || '';
                        input.placeholder = 'Serial ' + (i + 1);
                        input.className = 'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';
                        
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
                const productComponents = selectedComponents.filter(c => c.productId === productUniqueId);
                return productComponents.some(component => component.quantity !== component.originalQuantity);
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
                    if (existingSection && !isDuplicate) {
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
                    alert('Đã ghi nhận yêu cầu tạo thành phẩm mới. Chức năng này sẽ được phát triển trong phiên bản tiếp theo.');
                    
                    // Optional: You could also update the UI to show that a new product will be created
                    const componentBlock = document.getElementById('component_block_' + productUniqueId);
                    if (componentBlock) {
                        const header = componentBlock.querySelector('h4');
                        if (header && !header.textContent.includes('(Sẽ tạo mới)')) {
                            header.innerHTML = header.innerHTML + ' <span class="text-green-600">(Sẽ tạo mới)</span>';
                        }
                    }
                }
            }

            // Validation trước khi submit
            document.querySelector('form').addEventListener('submit', function(e) {
                // Kiểm tra có ít nhất một thành phẩm
                if (selectedProducts.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một thành phẩm vào phiếu lắp ráp!');
                    return false;
                }

                // Validate serials for each product
                let hasSerialError = false;
                let hasMissingSerial = false;

                selectedProducts.forEach((product, index) => {
                    // Check for duplicate serials within this product
                    const serialValues = product.serials.filter(s => s && s.trim() !== '');
                    const uniqueSerials = new Set(serialValues);

                    if (serialValues.length !== uniqueSerials.size) {
                        hasSerialError = true;
                    }

                    // Check if all serials are provided when quantity > 1
                    if (product.quantity > 1 && serialValues.length < product.quantity) {
                        hasMissingSerial = true;
                    }
                });

                if (hasSerialError) {
                    e.preventDefault();
                    alert('Phát hiện trùng lặp serial thành phẩm. Vui lòng kiểm tra lại!');
                    return false;
                }

                if (hasMissingSerial) {
                    e.preventDefault();
                    alert('Vui lòng nhập đủ serial cho tất cả thành phẩm.');
                    return false;
                }

                // Kiểm tra kho xuất và kho nhập
                if (!validateWarehouses()) {
                    e.preventDefault();
                    alert('Kho nhập thành phẩm phải khác với kho xuất linh kiện!');
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
                    if (!quantity || quantity < 1) {
                        e.preventDefault();
                        alert('Số lượng phải lớn hơn 0!');
                        return false;
                    }

                    // Kiểm tra lại tồn kho
                    checkStockSufficiency(component);
                    if (!component.isStockSufficient) {
                        hasStockError = true;
                        errorMessages.push(
                            '- ' + component.code + ': ' + component.name + ' - ' + component.stockWarning);
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

            // Kiểm tra kho xuất và kho nhập không được trùng nhau
            function validateWarehouses() {
                const sourceWarehouse = warehouseSelect.value;
                const targetWarehouse = document.getElementById('target_warehouse_id').value;

                if (sourceWarehouse && targetWarehouse && sourceWarehouse === targetWarehouse) {
                    // Hiển thị cảnh báo
                    showWarehouseWarning();
                    return false;
                } else {
                    // Ẩn cảnh báo
                    hideWarehouseWarning();
                    return true;
                }
            }

            function showWarehouseWarning() {
                // Tìm container của kho nhập
                const targetContainer = document.getElementById('target_warehouse_id').parentElement;

                // Xóa cảnh báo cũ nếu có
                const existingWarning = targetContainer.querySelector('.warehouse-warning');
                if (existingWarning) {
                    existingWarning.remove();
                }

                // Tạo cảnh báo mới
                const warningDiv = document.createElement('div');
                warningDiv.className = 'warehouse-warning text-red-600 text-sm mt-1 font-medium';
                warningDiv.textContent = 'Kho nhập thành phẩm phải khác với kho xuất linh kiện!';
                targetContainer.appendChild(warningDiv);

                // Đổi màu border của select
                document.getElementById('target_warehouse_id').classList.add('border-red-500');
                warehouseSelect.classList.add('border-red-500');
            }

            function hideWarehouseWarning() {
                // Xóa cảnh báo
                const existingWarning = document.querySelector('.warehouse-warning');
                if (existingWarning) {
                    existingWarning.remove();
                }

                // Bỏ màu border đỏ
                document.getElementById('target_warehouse_id').classList.remove('border-red-500');
                warehouseSelect.classList.remove('border-red-500');
            }

            // Thêm event listener cho cả hai dropdown kho
            warehouseSelect.addEventListener('change', function() {
                fetchWarehouseMaterials(this.value);
                validateWarehouses();
            });

            document.getElementById('target_warehouse_id').addEventListener('change', function() {
                validateWarehouses();
            });

            // Khởi tạo: tải danh sách linh kiện của kho nếu đã chọn kho
            if (warehouseSelect.value) {
                fetchWarehouseMaterials(warehouseSelect.value);
            }

            // Add event listener to update product dropdown for components when products change
            addProductBtn.addEventListener('click', function() {
                setTimeout(() => updateComponentProductDropdown(), 0);
            });

            // Initialize the component selection by updating the product dropdown
            updateComponentProductDropdown();

            // Event delegation for delete product buttons
            document.addEventListener('click', function(e) {
                console.log('Document click detected:', e.target);
                
                if (e.target && (e.target.classList.contains('delete-product') || 
                    e.target.closest('.delete-product'))) {
                    
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Find the actual button (in case clicked on icon inside button)
                    const button = e.target.classList.contains('delete-product') ? 
                        e.target : e.target.closest('.delete-product');
                    
                    console.log('Delete product button clicked:', button);
                    
                    // Get the current index from the button's data-index attribute
                    const currentIndex = parseInt(button.getAttribute('data-index'));
                    console.log('Delete product clicked, current index:', currentIndex);
                    console.log('Selected products count:', selectedProducts.length);
                    console.log('Selected products:', selectedProducts.map(p => p.name));
                    
                    if (isNaN(currentIndex) || currentIndex < 0 || currentIndex >= selectedProducts.length) {
                        console.log('Invalid index, returning');
                        return;
                    }
                    
                    const product = selectedProducts[currentIndex];
                    const productId = product.uniqueId;
                    console.log('Product to delete:', product.name, 'uniqueId:', productId);

                    // Count components for this product before deletion
                    const componentsForProduct = selectedComponents.filter(comp => comp.productId === productId);
                    console.log('Components for this product:', componentsForProduct.length);

                    // Remove all components for this product first
                    selectedComponents = selectedComponents.filter(comp => comp.productId !== productId);
                    console.log('Selected components after filtering:', selectedComponents.length);

                    // Remove the component block for this product
                    const componentBlock = document.getElementById('component_block_' + productId);
                    if (componentBlock) {
                        componentBlock.remove();
                        console.log('Removed component block for:', productId);
                    }

                    // Also remove any "Create New Product" buttons for this product
                    const duplicateSection = document.querySelector('.duplicate-section[data-product-id="' + productId + '"]');
                    if (duplicateSection) {
                        duplicateSection.remove();
                        console.log('Removed duplicate section for:', productId);
                    }

                    // Remove the product from the selected products array
                    selectedProducts.splice(currentIndex, 1);
                    console.log('Selected products after deletion:', selectedProducts.length);
                    console.log('Remaining products:', selectedProducts.map(p => p.name));
                    
                    // Update product list UI (this will regenerate data-index correctly)
                    updateProductList();

                    // Update empty state message visibility
                    if (selectedProducts.length === 0) {
                        const noProductsComponents = document.getElementById('no_products_components');
                        console.log('no_products_components element:', noProductsComponents);
                        if (noProductsComponents) {
                            noProductsComponents.style.display = '';
                            console.log('Showed no_products_components');
                        } else {
                            console.log('no_products_components element not found!');
                        }
                    }

                    // Update product dropdown for components
                    updateComponentProductDropdown();

                    // Update the component list to reflect changes
                    updateComponentList();
                    
                    console.log('Product deletion completed');
                }
            });

        });
    </script>
</body>

</html>
