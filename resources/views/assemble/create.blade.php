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
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1 required">Sản
                                phẩm <span class="text-red-500">*</span></label>
                            <select id="product_id" name="product_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn sản phẩm --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
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
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho
                                xuất
                                <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho xuất linh kiện --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}
                                        ({{ $warehouse->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="product_quantity"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng sản phẩm
                                <span class="text-red-500">*</span></label>
                            <input type="number" id="product_quantity" name="product_quantity" min="1"
                                value="1" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="product_serial" class="block text-sm font-medium text-gray-700 mb-1">Serial sản
                                phẩm</label>
                            <div id="product_serial_container" class="space-y-2">
                                <!-- Các trường serial sản phẩm sẽ được thêm vào đây -->
                                <div class="flex items-center">
                                    <div class="flex-grow">
                                        <input type="text" name="product_serials[]"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Nhập serial sản phẩm">
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Nhập serial cho mỗi sản phẩm. Số lượng trường sẽ tự
                                động cập nhật theo số lượng sản phẩm.</p>
                        </div>
                    </div>
                </div>

                <!-- Danh sách linh kiện -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-microchip text-blue-500 mr-2"></i>
                        Danh sách linh kiện sử dụng
                    </h2>

                    <!-- Tìm kiếm linh kiện -->
                    <div class="mb-4">
                        <div class="relative flex space-x-2">
                            <div class="relative flex-1">
                                <input type="text" id="component_search"
                                    placeholder="Nhập hoặc click để xem danh sách linh kiện..."
                                    class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                            <div class="w-24">
                                <input type="number" id="component_add_quantity" min="1" value="1"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Số lượng">
                            </div>
                            <button type="button" id="add_component_btn"
                                class="px-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                Thêm
                            </button>
                            <!-- Dropdown results -->
                            <div id="search_results"
                                class="absolute z-10 w-full left-0 right-0 mt-10 bg-white border border-gray-300 rounded-lg shadow-lg hidden max-h-60 overflow-y-auto">
                                <!-- Results will be populated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Bảng linh kiện đã chọn -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
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
                                <!-- Dữ liệu linh kiện sẽ được thêm vào đây -->
                                <tr id="no_components_row">
                                    <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        Chưa có linh kiện nào được thêm vào phiếu lắp ráp
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
            const addComponentBtn = document.getElementById('add_component_btn');
            const componentList = document.getElementById('component_list');
            const noComponentsRow = document.getElementById('no_components_row');
            const submitBtn = document.getElementById('submit-btn');
            const searchResults = document.getElementById('search_results');
            const warehouseSelect = document.getElementById('warehouse_id');
            const productQuantityInput = document.getElementById('product_quantity');

            let selectedComponents = [];
            let allMaterials = @json($materials);
            let searchTimeout = null;
            let selectedMaterial = null;
            let warehouseMaterials = [];

            // Ensure quantity is at least 1
            componentAddQuantity.addEventListener('change', function() {
                if (parseInt(this.value) < 1) {
                    this.value = 1;
                }
            });

            // Lấy danh sách linh kiện khi chọn kho
            warehouseSelect.addEventListener('change', function() {
                fetchWarehouseMaterials(this.value);
            });

            // Lấy danh sách linh kiện khi click vào ô tìm kiếm
            componentSearchInput.addEventListener('click', function() {
                const warehouseId = warehouseSelect.value;
                if (!warehouseId) {
                    alert('Vui lòng chọn kho trước khi tìm kiếm linh kiện!');
                    return;
                }

                showAllMaterials();
            });

            // Hàm lấy danh sách linh kiện theo kho
            function fetchWarehouseMaterials(warehouseId) {
                if (!warehouseId) return;

                // Hiển thị đang tải
                warehouseMaterials = [];

                // Gọi API để lấy linh kiện theo kho
                fetch(`/api/warehouses/${warehouseId}/materials`)
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
                    fetch(`/api/warehouses/${warehouseId}/materials`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                searchResults.innerHTML =
                                    `<div class="p-2 text-red-500">Lỗi: ${data.message}</div>`;
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
                    resultItem.innerHTML = `
                        <div class="font-medium">${material.code}: ${material.name}</div>
                        <div class="text-xs text-gray-500">
                            ${material.category || ''} 
                            ${material.serial ? '| ' + material.serial : ''} 
                            | Tồn kho: ${material.stock_quantity || 0}
                        </div>
                    `;

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

            // Xử lý tìm kiếm linh kiện khi gõ
            componentSearchInput.addEventListener('input', function() {
                const searchTerm = componentSearchInput.value.trim().toLowerCase();

                // Clear any existing timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                // Set a timeout to avoid too many searches while typing
                searchTimeout = setTimeout(() => {
                    const warehouseId = warehouseSelect.value;
                    if (!warehouseId) {
                        alert('Vui lòng chọn kho trước khi tìm kiếm linh kiện!');
                        return;
                    }

                    if (searchTerm.length === 0) {
                        // Nếu ô tìm kiếm trống, hiển thị tất cả linh kiện
                        showAllMaterials();
                        return;
                    }

                    // Nếu đã có danh sách linh kiện của kho, lọc trực tiếp
                    if (warehouseMaterials.length > 0) {
                        const filteredMaterials = warehouseMaterials.filter(material =>
                            material.code?.toLowerCase().includes(searchTerm) ||
                            material.name?.toLowerCase().includes(searchTerm) ||
                            material.category?.toLowerCase().includes(searchTerm) ||
                            material.serial?.toLowerCase().includes(searchTerm)
                        );

                        displaySearchResults(filteredMaterials);
                        return;
                    }

                    // Nếu chưa có danh sách linh kiện, tìm kiếm qua API
                    searchResults.innerHTML =
                        '<div class="p-2 text-gray-500">Đang tìm kiếm...</div>';
                    searchResults.classList.remove('hidden');

                    // Gọi API để tìm kiếm linh kiện
                    fetch(
                            `/api/warehouses/${warehouseId}/materials?term=${encodeURIComponent(searchTerm)}`
                            )
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                searchResults.innerHTML =
                                    `<div class="p-2 text-red-500">Lỗi: ${data.message}</div>`;
                                console.error('Error searching materials:', data.message);
                                return;
                            }

                            const materials = Array.isArray(data) ? data : (data.materials ||
                            []);
                            displaySearchResults(materials);
                        })
                        .catch(error => {
                            console.error('Error searching materials:', error);
                            searchResults.innerHTML =
                                '<div class="p-2 text-red-500">Có lỗi xảy ra khi tìm kiếm!</div>';
                        });
                }, 300);
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!componentSearchInput.contains(event.target) && !searchResults.contains(event.target)) {
                    searchResults.classList.add('hidden');
                }
            });

            // Xử lý thêm linh kiện
            addComponentBtn.addEventListener('click', function() {
                addSelectedComponent();
            });

            // Xử lý số lượng input serial sản phẩm
            updateProductSerialInputs();

            // Listen for product quantity changes to update serial inputs
            productQuantityInput.addEventListener('change', function() {
                if (parseInt(this.value) < 1) {
                    this.value = 1;
                }
                // Cập nhật số lượng input serial sản phẩm
                updateProductSerialInputs();

                // Update component quantities based on product quantity
                updateComponentQuantities();
            });

            // Hàm cập nhật số lượng input serial sản phẩm theo số lượng sản phẩm
            function updateProductSerialInputs() {
                const productQty = parseInt(productQuantityInput.value) || 1;
                const container = document.getElementById('product_serial_container');
                const currentInputs = container.querySelectorAll('input[name="product_serials[]"]');

                // Lưu lại giá trị của các input hiện tại
                let serialValues = [];
                currentInputs.forEach(input => {
                    serialValues.push(input.value);
                });

                // Xóa tất cả input hiện tại
                container.innerHTML = '';

                // Tạo số lượng input tương ứng với số lượng sản phẩm
                for (let i = 0; i < productQty; i++) {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'flex items-center mb-2 serial-input-row';

                    const numberBadge = document.createElement('div');
                    numberBadge.className =
                        'bg-blue-100 text-blue-800 font-medium rounded-full w-6 h-6 flex items-center justify-center mr-2 flex-shrink-0';
                    numberBadge.innerText = (i + 1);

                    const inputWrapper = document.createElement('div');
                    inputWrapper.className = 'flex-grow';

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'product_serials[]';
                    input.className =
                        'w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500';
                    input.placeholder = `Serial sản phẩm ${i + 1}`;

                    // Giữ lại giá trị cũ nếu có
                    if (i < serialValues.length) {
                        input.value = serialValues[i];
                    }

                    inputWrapper.appendChild(input);
                    rowDiv.appendChild(numberBadge);
                    rowDiv.appendChild(inputWrapper);
                    container.appendChild(rowDiv);
                }

                // Thêm thông báo nếu có nhiều sản phẩm
                if (productQty > 3) {
                    const noteDiv = document.createElement('div');
                    noteDiv.className = 'mt-2 text-sm text-blue-700';
                    noteDiv.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Đang hiển thị ' + productQty +
                        ' trường nhập serial cho ' + productQty + ' sản phẩm.';
                    container.appendChild(noteDiv);
                }
            }

            // Update component quantities based on product quantity
            function updateComponentQuantities() {
                const productQty = parseInt(productQuantityInput.value) || 1;

                selectedComponents.forEach(component => {
                    // Only update if component doesn't have manually adjusted quantity
                    if (!component.manuallyAdjusted) {
                        component.quantity = productQty;
                    }

                    // Always check stock sufficiency when product quantity changes
                    checkStockSufficiency(component);
                });

                updateComponentList();
            }

            // Check if stock is sufficient for a component based on product quantity
            function checkStockSufficiency(component) {
                // Số lượng sản phẩm
                const productQty = parseInt(productQuantityInput.value) || 1;

                // Số lượng linh kiện cho mỗi sản phẩm
                const componentQtyPerProduct = parseInt(component.quantity);

                // Tổng số linh kiện cần = số lượng sản phẩm * số lượng linh kiện cho mỗi sản phẩm
                const totalRequiredQty = productQty * componentQtyPerProduct;

                // Tồn kho hiện có
                const stockQty = parseInt(component.stock_quantity);

                // Check if stock is sufficient
                component.isStockSufficient = totalRequiredQty <= stockQty;
                component.stockWarning = !component.isStockSufficient ?
                    `Không đủ tồn kho (còn ${stockQty}, cần ${totalRequiredQty} = ${productQty} SP × ${componentQtyPerProduct})` :
                    '';

                return component.isStockSufficient;
            }

            // Add selected component function
            function addSelectedComponent() {
                if (selectedMaterial) {
                    // Check if already added
                    if (selectedComponents.some(c => c.id == selectedMaterial.id)) {
                        alert('Vật tư này đã được thêm vào phiếu lắp ráp!');
                        return;
                    }

                    // Số lượng linh kiện cho mỗi sản phẩm
                    const componentQtyPerProduct = parseInt(componentAddQuantity.value) || 1;

                    // Số lượng sản phẩm
                    const productQty = parseInt(productQuantityInput.value) || 1;

                    // Tổng số linh kiện cần = số lượng sản phẩm * số lượng linh kiện cho mỗi sản phẩm
                    const totalRequiredQty = productQty * componentQtyPerProduct;

                    // Tồn kho hiện có
                    const stockQty = parseInt(selectedMaterial.stock_quantity) || 0;

                    // Check if there's enough stock
                    if (totalRequiredQty > stockQty) {
                        alert(
                            `Không đủ tồn kho! Tồn kho hiện tại: ${stockQty}, Yêu cầu: ${totalRequiredQty} (${productQty} sản phẩm × ${componentQtyPerProduct} linh kiện/sản phẩm)`);
                        return;
                    }

                    // Add to selected components
                    const newComponent = {
                        id: selectedMaterial.id,
                        code: selectedMaterial.code,
                        name: selectedMaterial.name,
                        category: selectedMaterial.category || '',
                        quantity: componentQtyPerProduct,
                        stock_quantity: selectedMaterial.stock_quantity || 0,
                        serial: selectedMaterial.serial || '',
                        serials: [],
                        note: '',
                        manuallyAdjusted: true // Mark as manually adjusted to prevent auto-update from product quantity
                    };

                    // Check stock sufficiency
                    checkStockSufficiency(newComponent);

                    selectedComponents.push(newComponent);

                    // Update UI
                    updateComponentList();
                    componentSearchInput.value = '';
                    componentAddQuantity.value = '1'; // Reset quantity to 1
                    selectedMaterial = null;
                    searchResults.classList.add('hidden');
                } else {
                    const searchTerm = componentSearchInput.value.trim();

                    if (!searchTerm) {
                        alert('Vui lòng chọn linh kiện trước khi thêm!');
                        return;
                    }

                    // Không tìm thấy linh kiện hoặc chưa chọn
                    alert('Vui lòng chọn một linh kiện từ danh sách!');
                }
            }

            // Generate serial input fields based on quantity
            function generateSerialInputs(component, index, container) {
                const quantity = parseInt(component.quantity);
                const serialsContainer = document.createElement('div');
                serialsContainer.className = 'serial-inputs mt-2';

                // Clear existing serials container
                const existingContainer = container.querySelector('.serial-inputs');
                if (existingContainer) {
                    existingContainer.remove();
                }

                if (quantity > 1) {
                    // For quantities > 1, show multiple serial inputs
                    for (let i = 0; i < quantity; i++) {
                        const serialDiv = document.createElement('div');
                        serialDiv.className = 'mb-1';
                        const serialInput = document.createElement('input');
                        serialInput.type = 'text';
                        serialInput.name = `components[${index}][serials][]`;
                        serialInput.value = component.serials[i] || '';
                        serialInput.placeholder = `Serial ${i+1}`;
                        serialInput.className =
                            'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                        // Save serial when typing
                        serialInput.addEventListener('input', function() {
                            component.serials[i] = this.value;
                        });

                        serialDiv.appendChild(serialInput);
                        serialsContainer.appendChild(serialDiv);
                    }
                } else {
                    // For quantity = 1, show single serial input
                    const serialInput = document.createElement('input');
                    serialInput.type = 'text';
                    serialInput.name = `components[${index}][serial]`;
                    serialInput.value = component.serial || '';
                    serialInput.placeholder = 'Nhập serial';
                    serialInput.className =
                        'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                    // Save serial when typing
                    serialInput.addEventListener('input', function() {
                        component.serial = this.value;
                    });

                    serialsContainer.appendChild(serialInput);
                }

                container.appendChild(serialsContainer);
            }

            // Cập nhật danh sách linh kiện
            function updateComponentList() {
                // Ẩn thông báo "không có linh kiện"
                if (selectedComponents.length > 0) {
                    noComponentsRow.style.display = 'none';
                } else {
                    noComponentsRow.style.display = '';
                }

                // Xóa các hàng linh kiện hiện tại (trừ hàng thông báo)
                const componentRows = document.querySelectorAll('.component-row');
                componentRows.forEach(row => row.remove());

                // Thêm hàng cho mỗi linh kiện đã chọn
                selectedComponents.forEach((component, index) => {
                    const row = document.createElement('tr');
                    row.className = 'component-row';

                    // Hiển thị cảnh báo tồn kho nếu có
                    const stockWarningHtml = component.stockWarning ?
                        `<div class="text-xs text-red-600 font-medium mt-1">${component.stockWarning}</div>` :
                        '';

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="hidden" name="components[${index}][id]" value="${component.id}">
                            ${component.code}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${component.category}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${component.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="number" min="1" name="components[${index}][quantity]" value="${component.quantity || 1}"
                                class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input">
                            ${stockWarningHtml}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 serial-cell">
                            <!-- Serial inputs will be added here -->
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="text" name="components[${index}][note]" value="${component.note || ''}"
                                class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ghi chú">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-500 hover:text-red-700 delete-component" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;

                    componentList.insertBefore(row, noComponentsRow);

                    // Generate serial inputs based on quantity
                    const serialCell = row.querySelector('.serial-cell');
                    generateSerialInputs(component, index, serialCell);

                    // Add event listener for quantity changes
                    const qtyInput = row.querySelector('.quantity-input');
                    qtyInput.addEventListener('change', function() {
                        const newQty = parseInt(this.value) || 1;
                        if (newQty < 1) this.value = component.quantity = 1;
                        else component.quantity = newQty;

                        // Mark as manually adjusted
                        component.manuallyAdjusted = true;

                        // Regenerate serial inputs
                        generateSerialInputs(component, index, serialCell);

                        // Check stock sufficiency
                        checkStockSufficiency(component);

                        // Update stock warning
                        updateStockWarning(row, component);
                    });

                    // Thêm event listener để xóa linh kiện
                    row.querySelector('.delete-component').addEventListener('click', function() {
                        selectedComponents.splice(index, 1);
                        updateComponentList();
                    });
                });
            }

            // Update stock warning when quantity changes
            function updateStockWarning(row, component) {
                const stockWarningContainer = row.querySelector('td:nth-child(4) div');

                if (component.stockWarning) {
                    if (stockWarningContainer) {
                        stockWarningContainer.innerHTML = component.stockWarning;
                    } else {
                        const warningDiv = document.createElement('div');
                        warningDiv.className = 'text-xs text-red-600 font-medium mt-1';
                        warningDiv.textContent = component.stockWarning;
                        row.querySelector('td:nth-child(4)').appendChild(warningDiv);
                    }
                } else if (stockWarningContainer) {
                    stockWarningContainer.remove();
                }
            }

            // Validation trước khi submit
            document.querySelector('form').addEventListener('submit', function(e) {
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
                            `- ${component.code}: ${component.name} - ${component.stockWarning}`);
                    }
                }

                // Nếu có lỗi tồn kho, hiển thị thông báo và ngăn submit form
                if (hasStockError) {
                    e.preventDefault();
                    alert(`Không thể tạo phiếu lắp ráp do không đủ tồn kho:\n${errorMessages.join('\n')}`);
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
        });
    </script>
</body>

</html>
