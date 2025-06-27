<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo phiếu xuất kho - SGL</title>
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
                <a href="{{ asset('inventory/dispatch_list') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Tạo phiếu xuất kho</h1>
            </div>
        </header>

        <main class="p-6">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <strong>Có lỗi xảy ra:</strong>
                    <ul class="mt-2">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form id="dispatch-form" action="{{ route('inventory.dispatch.store') }}" method="POST">
                @csrf

                <!-- Thông tin phiếu xuất -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-invoice text-blue-500 mr-2"></i>
                        Thông tin phiếu xuất
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="dispatch_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu
                                xuất</label>
                            <input type="text" id="dispatch_code" name="dispatch_code"
                                value="{{ $nextDispatchCode }}" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="dispatch_date"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày xuất <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="dispatch_date" name="dispatch_date" value="{{ date('Y-m-d') }}"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="dispatch_type"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Loại hình <span
                                    class="text-red-500">*</span></label>
                            <select id="dispatch_type" name="dispatch_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn loại hình --</option>
                                <option value="project">Dự án</option>
                                <option value="rental">Cho thuê</option>
                                <option value="warranty">Bảo hành</option>
                            </select>
                        </div>
                        <div>
                            <label for="dispatch_detail"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Chi tiết xuất kho <span
                                    class="text-red-500">*</span></label>
                            <select id="dispatch_detail" name="dispatch_detail" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn chi tiết xuất kho --</option>
                                <option value="all">Tất cả</option>
                                <option value="contract">Xuất theo hợp đồng</option>
                                <option value="backup">Xuất thiết bị dự phòng</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <!-- Phần chọn dự án (hiển thị khi loại hình = project/warranty) -->
                        <div id="project_section">
                            <label for="project_receiver"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Dự án<span
                                    class="text-red-500">*</span></label>
                            <select id="project_receiver" name="project_receiver" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn dự án --</option>
                                @if (isset($projects))
                                    @foreach ($projects as $project)
                                        <option
                                            value="{{ $project->project_code }} - {{ $project->project_name }} ({{ $project->customer->name ?? 'N/A' }})"
                                            data-project-id="{{ $project->id }}"
                                            data-warranty-period="{{ $project->warranty_period }}">
                                            {{ $project->project_code }} - {{ $project->project_name }}
                                            ({{ $project->customer->name ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <input type="hidden" id="project_id" name="project_id">
                        </div>

                        <!-- Phần cho thuê (hiển thị khi loại hình = rental) -->
                        <div id="rental_section" class="hidden">
                            <label for="rental_receiver"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Hợp đồng cho thuê<span
                                    class="text-red-500">*</span></label>
                            <select id="rental_receiver" name="rental_receiver"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn hợp đồng cho thuê --</option>
                                <!-- Động load từ API -->
                            </select>
                        </div>
                        <div>
                            <label for="warranty_period" class="block text-sm font-medium text-gray-700 mb-1">Thời gian
                                bảo hành</label>
                            <input type="text" id="warranty_period" name="warranty_period" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Chọn dự án để hiển thị thời gian bảo hành">
                        </div>
                        <div>
                            <label for="company_representative"
                                class="block text-sm font-medium text-gray-700 mb-1">Người đại diện công ty</label>
                            <select id="company_representative" name="company_representative_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người đại diện --</option>
                                @if (isset($employees))
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="dispatch_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi
                                chú</label>
                            <textarea id="dispatch_note" name="dispatch_note" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập ghi chú cho phiếu xuất (nếu có)"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Danh sách thành phẩm theo hợp đồng (hiển thị khi chọn "Tất cả") -->
                <div id="contract-product-list"
                    class="bg-white rounded-xl shadow-md p-6 border border-blue-200 mb-6 hidden">
                    <h2 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                        <i class="fas fa-file-contract text-blue-500 mr-2"></i>
                        <span>Danh sách thành phẩm theo hợp đồng</span>
                    </h2>

                    <!-- Chọn thành phẩm hợp đồng -->
                    <div class="mb-4">
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <select id="contract_product_select"
                                    class="w-full border border-blue-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50">
                                    <option value="">-- Chọn thành phẩm theo hợp đồng --</option>
                                </select>
                            </div>
                            <button type="button" id="add_contract_product_btn"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                <i class="fas fa-plus mr-1"></i> Thêm
                            </button>
                        </div>
                    </div>

                    <!-- Bảng thành phẩm hợp đồng đã chọn -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Mã SP</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Tên thành phẩm</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Đơn vị</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Tồn kho</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Kho xuất</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Số lượng xuất</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Serial</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="contract_product_list" class="bg-white divide-y divide-gray-200">
                                <tr id="no_contract_products_row">
                                    <td colspan="8" class="px-6 py-4 text-sm text-blue-500 text-center">
                                        Chưa có thành phẩm hợp đồng nào được thêm
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Nút cập nhật mã thiết bị hợp đồng -->
                    <div class="mt-4 flex justify-end">
                        <button type="button" id="update_contract_device_codes_btn"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i> Cập nhật mã thiết bị
                        </button>
                    </div>
                </div>

                <!-- Danh sách thiết bị dự phòng (hiển thị khi chọn "Tất cả") -->
                <div id="backup-product-list"
                    class="bg-white rounded-xl shadow-md p-6 border border-orange-200 mb-6 hidden">
                    <h2 class="text-lg font-semibold text-orange-800 mb-4 flex items-center">
                        <i class="fas fa-shield-alt text-orange-500 mr-2"></i>
                        <span>Danh sách thiết bị dự phòng</span>
                    </h2>

                    <!-- Chọn thiết bị dự phòng -->
                    <div class="mb-4">
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <select id="backup_product_select"
                                    class="w-full border border-orange-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 bg-orange-50">
                                    <option value="">-- Chọn thiết bị dự phòng --</option>
                                </select>
                            </div>
                            <button type="button" id="add_backup_product_btn"
                                class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                                <i class="fas fa-plus mr-1"></i> Thêm
                            </button>
                        </div>
                    </div>

                    <!-- Bảng thiết bị dự phòng đã chọn -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                        Mã SP</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                        Tên thiết bị</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                        Đơn vị</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                        Tồn kho</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                        Kho xuất</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                        Số lượng xuất</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                        Serial</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                        Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="backup_product_list" class="bg-white divide-y divide-gray-200">
                                <tr id="no_backup_products_row">
                                    <td colspan="8" class="px-6 py-4 text-sm text-orange-500 text-center">
                                        Chưa có thiết bị dự phòng nào được thêm
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Nút cập nhật mã thiết bị dự phòng -->
                    <div class="mt-4 flex justify-end">
                        <button type="button" id="update_backup_device_codes_btn"
                            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i> Cập nhật mã thiết bị
                        </button>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ asset('inventory') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Lưu phiếu xuất
                    </button>
                </div>
            </form>
        </main>
    </div>

    <!-- Modal cập nhật mã thiết bị -->
    <div id="device-code-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Cập nhật mã thiết bị</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" id="close-device-code-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Nhập thông tin mã thiết bị cho sản phẩm. Điền thông tin vào bảng
                    bên dưới:</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri chính
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri vật tư
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri sim
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mã truy cập
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    ID IoT
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mac 4G
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Chú thích
                                </th>
                            </tr>
                        </thead>
                        <tbody id="device-code-tbody">
                            <!-- Dữ liệu sẽ được tạo động từ JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-between">
                    <button type="button" id="import-device-codes"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-file-import mr-2"></i> Import Excel
                    </button>
                    <div>
                        <button type="button" id="cancel-device-codes"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors mr-2">
                            Hủy
                        </button>
                        <button type="button" id="save-device-codes"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thông tin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Khai báo các element cần thiết
            const dispatchDetailSelect = document.getElementById('dispatch_detail');
            const dispatchTypeSelect = document.getElementById('dispatch_type');
            const projectReceiverSelect = document.getElementById('project_receiver');

            let selectedContractProducts = [];
            let selectedBackupProducts = [];
            let availableItems = []; // Lưu danh sách sản phẩm từ kho đã chọn

            // Hàm tạo serial selects theo quantity
            function generateSerialInputs(quantity, category, productId, index) {
                let inputs = '';
                const borderColor = category === 'contract' ? 'border-blue-300 focus:ring-blue-500' :
                    'border-orange-300 focus:ring-orange-500';

                // Get product info for data attributes
                const product = (category === 'contract' ? selectedContractProducts : selectedBackupProducts)[
                    index];

                for (let i = 0; i < quantity; i++) {
                    inputs +=
                        `<select name="${category}_serials[${productId}][${i}]" 
                                class="w-32 border ${borderColor} rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 serial-select"
                                data-item-type="${product?.type || 'product'}" 
                                data-item-id="${productId}" 
                                data-warehouse-id="${product?.selected_warehouse_id || ''}"
                                data-serial-index="${i}"
                                data-product-index="${index}"
                                data-category="${category}">
                            <option value="">-- Chọn Serial ${i + 1} --</option>
                        </select>`;
                }
                return inputs;
            }

            // Hàm cập nhật serial selects khi quantity thay đổi
            function updateSerialInputsCreate(quantity, category, productId, index) {
                const container = document.getElementById(`${category}-serials-${index}`);
                if (container) {
                    // Lưu giá trị hiện tại
                    const currentSelects = container.querySelectorAll('select');
                    const currentValues = Array.from(currentSelects).map(select => select.value);

                    // Tạo lại selects
                    container.innerHTML = '';
                    const borderColor = category === 'contract' ? 'border-blue-300 focus:ring-blue-500' :
                        'border-orange-300 focus:ring-orange-500';

                    // Get product info for data attributes
                    const product = (category === 'contract' ? selectedContractProducts : selectedBackupProducts)[
                        index];

                    for (let i = 0; i < quantity; i++) {
                        const select = document.createElement('select');
                        select.name = `${category}_serials[${productId}][${i}]`;
                        select.className =
                            `w-32 border ${borderColor} rounded px-2 py-1 text-xs focus:outline-none focus:ring-1`;

                        // Set data attributes for loading serials
                        select.setAttribute('data-item-type', product?.type || 'product');
                        select.setAttribute('data-item-id', productId);
                        select.setAttribute('data-warehouse-id', product?.selected_warehouse_id || '');
                        select.setAttribute('data-serial-index', i);
                        select.setAttribute('data-selected-serial', currentValues[i] || '');

                        // Add default option
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = `-- Chọn Serial ${i + 1} --`;
                        select.appendChild(defaultOption);

                        // If there was a previously selected value, add it as selected option
                        if (currentValues[i]) {
                            const selectedOption = document.createElement('option');
                            selectedOption.value = currentValues[i];
                            selectedOption.textContent = currentValues[i];
                            selectedOption.selected = true;
                            select.appendChild(selectedOption);
                        }

                        // Add change event listener for validation
                        select.addEventListener('change', validateSerialOnChange);
                        select.setAttribute('data-validation-listener', 'true');

                        container.appendChild(select);
                    }

                    // Load available serials for new selects (this will populate all available options)
                    loadAvailableSerials();
                }
            }

            // Load tất cả sản phẩm từ tất cả kho ngay từ đầu
            loadAllAvailableItems();

            // Load danh sách hợp đồng cho thuê
            loadRentals();

            // Initial validation and option availability update after data loads
            setTimeout(function() {
                updateSerialOptionsAvailability();
            }, 1000);

            // Hàm cập nhật warehouse_id cho serial selects
            function updateSerialWarehouseIds(category, productIndex, warehouseId) {
                const serialSelects = document.querySelectorAll(`select[data-category="${category}"][data-product-index="${productIndex}"]`);
                serialSelects.forEach(select => {
                    select.setAttribute('data-warehouse-id', warehouseId);
                    // Clear existing options except default
                    while (select.children.length > 1) {
                        select.removeChild(select.lastChild);
                    }
                    // Reset value
                    select.value = '';
                });
                
                // Load serials for updated selects
                loadAvailableSerials();
            }

            // Hàm load available serial numbers cho tất cả serial selects
            async function loadAvailableSerials() {
                const serialSelects = document.querySelectorAll('select[name*="serials"]');
                console.log(`Loading serials for ${serialSelects.length} select elements`);

                for (const select of serialSelects) {
                    if (select.disabled) continue; // Skip disabled selects

                    const itemType = select.dataset.itemType;
                    const itemId = select.dataset.itemId;
                    const warehouseId = select.dataset.warehouseId;
                    const selectedSerial = select.dataset.selectedSerial;

                    console.log(`Checking select: itemType=${itemType}, itemId=${itemId}, warehouseId=${warehouseId}`);

                    if (!itemType || !itemId || !warehouseId) {
                        console.log(`Skipping select due to missing data: itemType=${itemType}, itemId=${itemId}, warehouseId=${warehouseId}`);
                        continue;
                    }

                    try {
                        console.log(`Fetching serials for ${itemType} ${itemId} in warehouse ${warehouseId}`);
                        fetch(`/api/dispatch/item-serials?item_type=${itemType}&item_id=${itemId}&warehouse_id=${warehouseId}`)
                            .then(response => {
                                console.log('API Response status:', response.status);
                                return response.json();
                            })
                            .then(data => {
                                console.log('API Request params:', {
                                    item_type: itemType,
                                    item_id: itemId,
                                    warehouse_id: warehouseId
                                });
                                console.log('API Response data:', data);
                                
                                if (data.success) {
                                    const availableSerials = data.serials || [];
                                    console.log('Available serials:', availableSerials);
                                    
                                    if (availableSerials.length === 0) {
                                        console.warn('Không có serial khả dụng. Chi tiết:', {
                                            total_serials: data.total_serials,
                                            used_serials: data.used_serials,
                                            available_serials: data.available_serials
                                        });
                                    }

                                    // Populate ONLY the current select (not all selects)
                                    // Clear previous options
                                    select.innerHTML = '<option value="">Chọn serial...</option>';
                                    
                                    // Add available serials
                                    availableSerials.forEach(serial => {
                                        const option = document.createElement('option');
                                        option.value = serial;
                                        option.textContent = serial;
                                        select.appendChild(option);
                                    });
                                    
                                    // Show "No serials available" if empty
                                    if (availableSerials.length === 0) {
                                        const option = document.createElement('option');
                                        option.value = '';
                                        option.textContent = 'Không có serial khả dụng';
                                        option.disabled = true;
                                        select.appendChild(option);
                                    }

                                    // Log serial availability info for debugging
                                    if (data.total_serials !== undefined && data.used_serials !== undefined) {
                                        console.log(
                                            `Serials for ${itemType} ${itemId}: ${data.available_serials}/${data.total_serials} available (${data.used_serials} used)`
                                        );

                                        // Show warning if low availability
                                        if (data.available_serials < data.total_serials * 0.2 && data
                                            .available_serials > 0) {
                                            console.warn(
                                                `Low serial availability for ${itemType} ${itemId}: Only ${data.available_serials} out of ${data.total_serials} serials available`
                                            );
                                        } else if (data.available_serials === 0 && data.total_serials > 0) {
                                            console.error(
                                                `No serial available for ${itemType} ${itemId}: All ${data.total_serials} serials are already used in approved dispatches`
                                            );
                                        }
                                    }

                                    // Set selected value if available
                                    const selectedSerial = select.dataset.selectedSerial;
                                    if (selectedSerial && availableSerials.includes(selectedSerial)) {
                                        select.value = selectedSerial;
                                    }

                                    // Add change event listener for validation
                                    if (!select.hasAttribute('data-validation-listener')) {
                                        select.addEventListener('change', validateSerialOnChange);
                                        select.setAttribute('data-validation-listener', 'true');
                                    }
                                } else {
                                    console.error(`API error for ${itemType} ${itemId}:`, data.message);
                                    
                                    // Show error ONLY in the current select
                                    select.innerHTML = '<option value="">Lỗi tải serial</option>';
                                }
                            });
                    } catch (error) {
                        console.error(`Error loading serials for ${itemType} ${itemId}:`, error);
                    }
                }
            }

            // Xử lý thêm sản phẩm hợp đồng
            const addContractProductBtn = document.getElementById('add_contract_product_btn');
            const contractProductSelect = document.getElementById('contract_product_select');

            if (addContractProductBtn) {
                addContractProductBtn.addEventListener('click', function() {
                    const selectedProductId = contractProductSelect.value;

                    if (!selectedProductId) {
                        alert('Vui lòng chọn thành phẩm hợp đồng để thêm!');
                        return;
                    }

                    addContractProduct(parseInt(selectedProductId));
                    contractProductSelect.value = '';
                });
            }

            // Xử lý thêm thiết bị dự phòng
            const addBackupProductBtn = document.getElementById('add_backup_product_btn');
            const backupProductSelect = document.getElementById('backup_product_select');

            if (addBackupProductBtn) {
                addBackupProductBtn.addEventListener('click', function() {
                    const selectedProductId = backupProductSelect.value;

                    if (!selectedProductId) {
                        alert('Vui lòng chọn thiết bị dự phòng để thêm!');
                        return;
                    }

                    addBackupProduct(parseInt(selectedProductId));
                    backupProductSelect.value = '';
                });
            }

            // Hàm load tất cả sản phẩm từ tất cả kho
            async function loadAllAvailableItems() {
                try {
                    const response = await fetch('/api/dispatch/items/all');
                    const data = await response.json();

                    if (data.success) {
                        // Chuẩn hóa dữ liệu để tương thích với frontend
                        availableItems = (data.items || []).map(item => ({
                            id: item.id,
                            type: item.type,
                            code: item.code,
                            name: item.name,
                            unit: item.unit,
                            warehouses: item.warehouses || [],
                            display_name: item.display_name
                        }));
                        updateProductSelects();
                    } else {
                        alert('Lỗi từ server: ' + (data.message || 'Không thể tải danh sách sản phẩm'));
                    }
                } catch (error) {
                    alert('Không thể tải danh sách sản phẩm: ' + error.message);
                }
            }

            // Hàm load danh sách hợp đồng cho thuê
            async function loadRentals() {
                try {
                    const response = await fetch('/api/dispatch/rentals');
                    const data = await response.json();

                    if (data.success) {
                        const rentalSelect = document.getElementById('rental_receiver');
                        if (rentalSelect) {
                            // Clear existing options
                            rentalSelect.innerHTML =
                                '<option value="">-- Chọn hợp đồng cho thuê --</option>';

                            // Add rental options
                            data.rentals.forEach(rental => {
                                const option = document.createElement('option');
                                option.value = rental.display_name;
                                option.textContent = rental.display_name;
                                option.dataset.rentalId = rental.id;
                                option.dataset.rentalCode = rental.rental_code;
                                option.dataset.customerName = rental.customer_name;
                                rentalSelect.appendChild(option);
                            });
                        }
                    } else {
                        console.error('Error loading rentals:', data.message);
                    }
                } catch (error) {
                    console.error('Error loading rentals:', error);
                }
            }

            // Cập nhật tất cả dropdown sản phẩm
            function updateProductSelects() {
                // Cập nhật dropdown hợp đồng
                const contractProductSelect = document.getElementById('contract_product_select');
                if (contractProductSelect) {
                    contractProductSelect.innerHTML =
                        '<option value="">-- Chọn sản phẩm hợp đồng --</option>';
                }

                // Cập nhật dropdown dự phòng
                const backupProductSelect = document.getElementById('backup_product_select');
                if (backupProductSelect) {
                    backupProductSelect.innerHTML =
                        '<option value="">-- Chọn thiết bị dự phòng --</option>';
                }

                // Thêm options từ availableItems
                availableItems.forEach(item => {
                    const contractOption = document.createElement('option');
                    contractOption.value = item.id;
                    contractOption.textContent = item.display_name;
                    contractOption.dataset.type = item.type; // Thêm type

                    const backupOption = document.createElement('option');
                    backupOption.value = item.id;
                    backupOption.textContent = item.display_name;
                    backupOption.dataset.type = item.type; // Thêm type

                    if (contractProductSelect) contractProductSelect.appendChild(contractOption);
                    if (backupProductSelect) backupProductSelect.appendChild(backupOption);
                });
            }

            // Clear tất cả dropdown sản phẩm
            function clearProductSelects() {
                const contractProductSelect = document.getElementById('contract_product_select');
                if (contractProductSelect) {
                    contractProductSelect.innerHTML =
                        '<option value="">-- Chọn sản phẩm hợp đồng --</option>';
                }

                const backupProductSelect = document.getElementById('backup_product_select');
                if (backupProductSelect) {
                    backupProductSelect.innerHTML =
                        '<option value="">-- Chọn thiết bị dự phòng --</option>';
                }
            }

            // Hàm thêm sản phẩm hợp đồng
            function addContractProduct(productId) {
                const contractProductSelect = document.getElementById('contract_product_select');
                const selectedOption = contractProductSelect.options[contractProductSelect.selectedIndex];
                const selectedType = selectedOption ? selectedOption.dataset.type : 'product';

                const foundProduct = availableItems.find(p => p.id == productId && p.type === selectedType);

                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                // Kiểm tra đã thêm chưa (so sánh cả id và type)
                const existingProduct = selectedContractProducts.find(p => p.id === foundProduct.id && p.type === foundProduct.type);
                if (existingProduct) {
                    alert('Sản phẩm này đã được thêm vào danh sách hợp đồng!');
                    return;
                }

                selectedContractProducts.push({
                    ...foundProduct,
                    quantity: 1,
                    selected_warehouse_id: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[0].warehouse_id : null,
                    current_stock: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[0].quantity : 0
                });

                renderContractProductTable();
                showStockWarnings();
            }

            // Hàm thêm thiết bị dự phòng
            function addBackupProduct(productId) {
                const backupProductSelect = document.getElementById('backup_product_select');
                const selectedOption = backupProductSelect.options[backupProductSelect.selectedIndex];
                const selectedType = selectedOption ? selectedOption.dataset.type : 'product';

                const foundProduct = availableItems.find(p => p.id == productId && p.type === selectedType);

                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                // Kiểm tra đã thêm chưa (so sánh cả id và type)
                const existingProduct = selectedBackupProducts.find(p => p.id === foundProduct.id && p.type === foundProduct.type);
                if (existingProduct) {
                    alert('Thiết bị này đã được thêm vào danh sách dự phòng!');
                    return;
                }

                selectedBackupProducts.push({
                    ...foundProduct,
                    quantity: 1,
                    selected_warehouse_id: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[0].warehouse_id : null,
                    current_stock: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[0].quantity : 0
                });

                renderBackupProductTable();
                showStockWarnings();
            }

            // Xử lý thay đổi chi tiết xuất kho
            if (dispatchDetailSelect) {
                dispatchDetailSelect.addEventListener('change', function() {
                    const selectedDetail = this.value;

                    // Lấy các elements
                    const contractProductList = document.getElementById('contract-product-list');
                    const backupProductList = document.getElementById('backup-product-list');

                    // Ẩn tất cả trước
                    contractProductList.classList.add('hidden');
                    backupProductList.classList.add('hidden');

                    // Hiển thị container tương ứng
                    if (selectedDetail === 'all') {
                        // Hiển thị 2 danh sách riêng biệt
                        contractProductList.classList.remove('hidden');
                        backupProductList.classList.remove('hidden');
                    } else if (selectedDetail === 'contract') {
                        // Chỉ hiển thị danh sách hợp đồng
                        contractProductList.classList.remove('hidden');
                    } else if (selectedDetail === 'backup') {
                        // Chỉ hiển thị danh sách dự phòng
                        backupProductList.classList.remove('hidden');
                    }
                });
            }

            // Xử lý thay đổi loại hình xuất kho
            if (dispatchTypeSelect) {
                dispatchTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;

                    const projectSection = document.getElementById('project_section');
                    const rentalSection = document.getElementById('rental_section');
                    const projectReceiverInput = document.getElementById('project_receiver');
                    const rentalReceiverInput = document.getElementById('rental_receiver');
                    const dispatchDetailSelect = document.getElementById('dispatch_detail');

                    // Reset all sections
                    projectSection.classList.add('hidden');
                    rentalSection.classList.add('hidden');
                    projectReceiverInput.removeAttribute('required');
                    rentalReceiverInput.removeAttribute('required');

                    if (selectedType === 'rental') {
                        // Hiển thị phần cho thuê, ẩn phần dự án
                        rentalSection.classList.remove('hidden');
                        rentalReceiverInput.setAttribute('required', 'required');
                        
                        // Load danh sách hợp đồng cho thuê
                        loadRentals();

                        // Tắt required cho project_receiver vì đang dùng rental
                        projectReceiverInput.removeAttribute('required');

                        // Ẩn project section để tránh confusion
                        projectSection.classList.add('hidden');

                        // Set project_receiver = rental_receiver và project_id = rental_id để tương thích với backend
                        const syncRentalToProject = function() {
                            projectReceiverInput.value = rentalReceiverInput.value;
                            
                            // Lấy rental_id từ selected option và gán vào project_id
                            const selectedOption = rentalReceiverInput.options[rentalReceiverInput.selectedIndex];
                            const projectIdInput = document.getElementById('project_id');
                            if (selectedOption && selectedOption.dataset.rentalId) {
                                projectIdInput.value = selectedOption.dataset.rentalId;
                            } else {
                                projectIdInput.value = '';
                            }
                        };

                        // Xóa event listener cũ nếu có để tránh duplicate
                        rentalReceiverInput.removeEventListener('input', syncRentalToProject);
                        rentalReceiverInput.removeEventListener('change', syncRentalToProject);

                        // Thêm event listeners mới
                        rentalReceiverInput.addEventListener('input', syncRentalToProject);
                        rentalReceiverInput.addEventListener('change', syncRentalToProject);

                        // Đồng bộ giá trị hiện tại
                        syncRentalToProject();

                        // Không tự động chọn, để người dùng tự chọn
                        if (dispatchDetailSelect) {
                            dispatchDetailSelect.disabled = false;
                            dispatchDetailSelect.value = ''; // Reset về chưa chọn
                        }

                        // Xóa hidden input nếu có
                        const hiddenDispatchDetail = document.getElementById(
                            'hidden_dispatch_detail');
                        if (hiddenDispatchDetail) {
                            hiddenDispatchDetail.remove();
                        }
                    } else if (selectedType === 'project') {
                        // Hiển thị phần dự án, ẩn phần cho thuê
                        projectSection.classList.remove('hidden');
                        projectReceiverInput.setAttribute('required', 'required');

                        // Không tự động chọn, để người dùng tự chọn
                        if (dispatchDetailSelect) {
                            dispatchDetailSelect.disabled = false;
                            dispatchDetailSelect.value = ''; // Reset về chưa chọn
                        }

                        // Xóa hidden input nếu có
                        const hiddenDispatchDetail = document.getElementById(
                            'hidden_dispatch_detail');
                        if (hiddenDispatchDetail) {
                            hiddenDispatchDetail.remove();
                        }
                    } else if (selectedType === 'warranty') {
                        // Hiển thị phần dự án, ẩn phần cho thuê
                        projectSection.classList.remove('hidden');
                        projectReceiverInput.setAttribute('required', 'required');

                        // Tự động chọn "backup" và disable dropdown cho warranty
                        if (dispatchDetailSelect) {
                            dispatchDetailSelect.value = 'backup';
                            dispatchDetailSelect.disabled = true;

                            // Tạo hidden input để đảm bảo giá trị được gửi đi
                            let hiddenDispatchDetail = document.getElementById(
                                'hidden_dispatch_detail');
                            if (!hiddenDispatchDetail) {
                                hiddenDispatchDetail = document.createElement('input');
                                hiddenDispatchDetail.type = 'hidden';
                                hiddenDispatchDetail.id = 'hidden_dispatch_detail';
                                hiddenDispatchDetail.name = 'dispatch_detail';
                                document.getElementById('dispatch-form').appendChild(
                                    hiddenDispatchDetail);
                            }
                            hiddenDispatchDetail.value = 'backup';

                            // Trigger change event để hiển thị backup product list
                            dispatchDetailSelect.dispatchEvent(new Event('change'));
                        }
                    }
                });
            }

            // Xử lý thay đổi dự án - cập nhật thời gian bảo hành và project_id
            if (projectReceiverSelect) {
                projectReceiverSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const warrantyPeriodInput = document.getElementById('warranty_period');
                    const projectIdInput = document.getElementById('project_id');

                    if (selectedOption && selectedOption.dataset.warrantyPeriod) {
                        warrantyPeriodInput.value = selectedOption.dataset.warrantyPeriod +
                            ' tháng';
                        projectIdInput.value = selectedOption.dataset.projectId || '';
                    } else {
                        warrantyPeriodInput.value = '';
                        projectIdInput.value = '';
                    }
                });
            }

            // Hàm hiển thị bảng sản phẩm hợp đồng
            function renderContractProductTable() {
                const contractProductList = document.getElementById('contract_product_list');
                const noContractProductsRow = document.getElementById('no_contract_products_row');

                if (!contractProductList || !noContractProductsRow) return;

                // Xóa tất cả hàng hiện tại (trừ hàng "không có sản phẩm")
                const existingRows = contractProductList.querySelectorAll(
                    'tr:not(#no_contract_products_row)');
                existingRows.forEach(row => row.remove());

                if (selectedContractProducts.length === 0) {
                    noContractProductsRow.classList.remove('hidden');
                    return;
                }

                noContractProductsRow.classList.add('hidden');

                selectedContractProducts.forEach((product, index) => {
                    // Tạo dropdown kho xuất cho sản phẩm hợp đồng
                    let warehouseOptions = '';
                    const productItem = availableItems.find(item => item.id === product.id);
                    if (productItem && productItem.warehouses) {
                        productItem.warehouses.forEach(warehouse => {
                            const selected = warehouse.warehouse_id == product
                                .selected_warehouse_id ? 'selected' : '';
                            warehouseOptions +=
                                `<option value="${warehouse.warehouse_id}" ${selected} data-quantity="${warehouse.quantity}">${warehouse.warehouse_name} (Tồn: ${warehouse.quantity})</option>`;
                        });
                    }

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-900 font-medium">${product.code}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.unit}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" id="contract-stock-${index}">${product.current_stock || 0}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select class="w-32 border border-blue-300 rounded px-2 py-1 text-sm contract-warehouse-select" 
                                data-index="${index}">
                                ${warehouseOptions}
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" value="${product.quantity}" min="1" max="${product.current_stock || 0}" 
                                class="w-20 border border-blue-300 rounded px-2 py-1 text-sm contract-quantity-input" 
                                data-index="${index}" id="contract-quantity-${index}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1" id="contract-serials-${index}">
                                ${generateSerialInputs(product.quantity, 'contract', product.id, index)}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-contract-product" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    contractProductList.appendChild(row);
                });

                // Thêm event listeners cho dropdown kho xuất hợp đồng
                const contractWarehouseSelects = contractProductList.querySelectorAll(
                    '.contract-warehouse-select');
                contractWarehouseSelects.forEach(select => {
                    select.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newWarehouseId = parseInt(this.value);
                        const selectedOption = this.options[this.selectedIndex];
                        const newQuantity = parseInt(selectedOption.dataset.quantity);

                        // Cập nhật thông tin kho đã chọn
                        selectedContractProducts[index].selected_warehouse_id =
                            newWarehouseId;
                        selectedContractProducts[index].current_stock = newQuantity;

                        // Cập nhật hiển thị tồn kho
                        const stockCell = document.getElementById(
                            `contract-stock-${index}`);
                        if (stockCell) {
                            stockCell.textContent = newQuantity;
                        }

                        // Cập nhật max cho input số lượng
                        const quantityInput = document.getElementById(
                            `contract-quantity-${index}`);
                        if (quantityInput) {
                            quantityInput.max = newQuantity;
                            // Nếu số lượng hiện tại lớn hơn tồn kho mới, giảm xuống
                            if (parseInt(quantityInput.value) > newQuantity) {
                                quantityInput.value = Math.min(parseInt(quantityInput
                                        .value),
                                    newQuantity);
                                selectedContractProducts[index].quantity = parseInt(
                                    quantityInput
                                    .value);
                            }
                        }

                        // Cập nhật warehouse_id cho tất cả serial selects của product này
                        updateSerialWarehouseIds('contract', index, newWarehouseId);

                        // Kiểm tra tồn kho ngay khi thay đổi kho
                        showStockWarnings();
                    });
                });

                // Thêm event listeners cho các input và nút xóa
                const contractQuantityInputs = contractProductList.querySelectorAll(
                    '.contract-quantity-input');
                contractQuantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newQuantity = parseInt(this.value);
                        if (selectedContractProducts[index]) {
                            selectedContractProducts[index].quantity = newQuantity;
                            // Cập nhật serial inputs
                            updateSerialInputsCreate(newQuantity, 'contract',
                                selectedContractProducts[index].id, index);
                            // Load serials for new inputs
                            setTimeout(() => {
                                loadAvailableSerials();
                            }, 100);
                        }
                        // Kiểm tra tồn kho ngay khi thay đổi
                        showStockWarnings();
                    });
                });

                const removeContractButtons = contractProductList.querySelectorAll(
                    '.remove-contract-product');
                removeContractButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedContractProducts.splice(index, 1);
                        renderContractProductTable();
                        // Kiểm tra lại tồn kho sau khi xóa sản phẩm
                        showStockWarnings();
                    });
                });

                // Load available serials cho contract products sau một chút để đảm bảo DOM đã được cập nhật
                setTimeout(() => {
                    loadAvailableSerials();
                }, 100);
            }

            // Hàm hiển thị bảng thiết bị dự phòng
            function renderBackupProductTable() {
                const backupProductList = document.getElementById('backup_product_list');
                const noBackupProductsRow = document.getElementById('no_backup_products_row');

                if (!backupProductList || !noBackupProductsRow) return;

                // Xóa tất cả hàng hiện tại (trừ hàng "không có sản phẩm")
                const existingRows = backupProductList.querySelectorAll('tr:not(#no_backup_products_row)');
                existingRows.forEach(row => row.remove());

                if (selectedBackupProducts.length === 0) {
                    noBackupProductsRow.classList.remove('hidden');
                    return;
                }

                noBackupProductsRow.classList.add('hidden');

                selectedBackupProducts.forEach((product, index) => {
                    // Tạo dropdown kho xuất cho thiết bị dự phòng
                    let warehouseOptions = '';
                    const productItem = availableItems.find(item => item.id === product.id);
                    if (productItem && productItem.warehouses) {
                        productItem.warehouses.forEach(warehouse => {
                            const selected = warehouse.warehouse_id == product
                                .selected_warehouse_id ? 'selected' : '';
                            warehouseOptions +=
                                `<option value="${warehouse.warehouse_id}" ${selected} data-quantity="${warehouse.quantity}">${warehouse.warehouse_name} (Tồn: ${warehouse.quantity})</option>`;
                        });
                    }

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-900 font-medium">${product.code}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.unit}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" id="backup-stock-${index}">${product.current_stock || 0}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select class="w-32 border border-orange-300 rounded px-2 py-1 text-sm backup-warehouse-select" 
                                data-index="${index}">
                                ${warehouseOptions}
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" value="${product.quantity}" min="1" max="${product.current_stock || 0}" 
                                class="w-20 border border-orange-300 rounded px-2 py-1 text-sm backup-quantity-input" 
                                data-index="${index}" id="backup-quantity-${index}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1" id="backup-serials-${index}">
                                ${generateSerialInputs(product.quantity, 'backup', product.id, index)}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-backup-product" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    backupProductList.appendChild(row);
                });

                // Thêm event listeners cho dropdown kho xuất dự phòng
                const backupWarehouseSelects = backupProductList.querySelectorAll(
                    '.backup-warehouse-select');
                backupWarehouseSelects.forEach(select => {
                    select.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newWarehouseId = parseInt(this.value);
                        const selectedOption = this.options[this.selectedIndex];
                        const newQuantity = parseInt(selectedOption.dataset.quantity);

                        // Cập nhật thông tin kho đã chọn
                        selectedBackupProducts[index].selected_warehouse_id =
                            newWarehouseId;
                        selectedBackupProducts[index].current_stock = newQuantity;

                        // Cập nhật hiển thị tồn kho
                        const stockCell = document.getElementById(`backup-stock-${index}`);
                        if (stockCell) {
                            stockCell.textContent = newQuantity;
                        }

                        // Cập nhật max cho input số lượng
                        const quantityInput = document.getElementById(
                            `backup-quantity-${index}`);
                        if (quantityInput) {
                            quantityInput.max = newQuantity;
                            // Nếu số lượng hiện tại lớn hơn tồn kho mới, giảm xuống
                            if (parseInt(quantityInput.value) > newQuantity) {
                                quantityInput.value = Math.min(parseInt(quantityInput
                                        .value),
                                    newQuantity);
                                selectedBackupProducts[index].quantity = parseInt(
                                    quantityInput
                                    .value);
                            }
                        }

                        // Cập nhật warehouse_id cho tất cả serial selects của product này
                        updateSerialWarehouseIds('backup', index, newWarehouseId);

                        // Kiểm tra tồn kho ngay khi thay đổi kho
                        showStockWarnings();
                    });
                });

                // Thêm event listeners cho các input và nút xóa
                const backupQuantityInputs = backupProductList.querySelectorAll('.backup-quantity-input');
                backupQuantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newQuantity = parseInt(this.value);
                        if (selectedBackupProducts[index]) {
                            selectedBackupProducts[index].quantity = newQuantity;
                            // Cập nhật serial inputs
                            updateSerialInputsCreate(newQuantity, 'backup',
                                selectedBackupProducts[
                                    index].id, index);
                            // Load serials for new inputs
                            setTimeout(() => {
                                loadAvailableSerials();
                            }, 100);
                        }
                        // Kiểm tra tồn kho ngay khi thay đổi
                        showStockWarnings();
                    });
                });

                const removeBackupButtons = backupProductList.querySelectorAll('.remove-backup-product');
                removeBackupButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedBackupProducts.splice(index, 1);
                        renderBackupProductTable();
                        // Kiểm tra lại tồn kho sau khi xóa sản phẩm
                        showStockWarnings();
                    });
                });

                // Load available serials cho backup products sau một chút để đảm bảo DOM đã được cập nhật
                setTimeout(() => {
                    loadAvailableSerials();
                }, 100);
            }

            // Xử lý modal cập nhật mã thiết bị
            const updateDeviceCodesBtn = document.getElementById('update_device_codes_btn');
            const updateContractDeviceCodesBtn = document.getElementById(
                'update_contract_device_codes_btn');
            const updateBackupDeviceCodesBtn = document.getElementById('update_backup_device_codes_btn');
            const deviceCodeModal = document.getElementById('device-code-modal');
            const closeDeviceCodeModalBtn = document.getElementById('close-device-code-modal');
            const cancelDeviceCodesBtn = document.getElementById('cancel-device-codes');
            const saveDeviceCodesBtn = document.getElementById('save-device-codes');
            const importDeviceCodesBtn = document.getElementById('import-device-codes');

            let currentDeviceCodeType = 'main'; // 'main', 'contract', 'backup'

            if (updateDeviceCodesBtn) {
                updateDeviceCodesBtn.addEventListener('click', function() {
                    currentDeviceCodeType = 'main';
                    renderDeviceCodeTable('main');
                    deviceCodeModal.classList.remove('hidden');
                });
            }

            if (updateContractDeviceCodesBtn) {
                updateContractDeviceCodesBtn.addEventListener('click', function() {
                    currentDeviceCodeType = 'contract';
                    renderDeviceCodeTable('contract');
                    deviceCodeModal.classList.remove('hidden');
                });
            }

            if (updateBackupDeviceCodesBtn) {
                updateBackupDeviceCodesBtn.addEventListener('click', function() {
                    currentDeviceCodeType = 'backup';
                    renderDeviceCodeTable('backup');
                    deviceCodeModal.classList.remove('hidden');
                });
            }

            // Hàm hiển thị bảng mã thiết bị
            function renderDeviceCodeTable(type = 'main') {
                const tbody = document.getElementById('device-code-tbody');
                tbody.innerHTML = '';

                let productsToShow = [];
                let prefixName = '';

                if (type === 'contract') {
                    productsToShow = selectedContractProducts;
                    prefixName = 'contract';
                } else if (type === 'backup') {
                    productsToShow = selectedBackupProducts;
                    prefixName = 'backup';
                } else {
                    productsToShow = selectedProducts;
                    prefixName = 'main';
                }

                productsToShow.forEach((product, index) => {
                    // Tạo chuỗi seri vật tư từ components
                    const componentSerials = product.components ?
                        product.components.map(comp => comp.serial).join(', ') : '';

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="${prefixName}_serial_main[${product.id}]" placeholder="Nhập seri chính..."
                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <textarea name="${prefixName}_serial_components[${product.id}]" placeholder="Nhập seri vật tư..." rows="2"
                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm">${componentSerials}</textarea>
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="${prefixName}_serial_sim[${product.id}]" placeholder="Nhập seri SIM..."
                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="${prefixName}_access_code[${product.id}]" placeholder="Mã truy cập..."
                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="${prefixName}_iot_id[${product.id}]" placeholder="ID IoT..."
                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="${prefixName}_mac_4g[${product.id}]" placeholder="Mac 4G..."
                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="${prefixName}_note[${product.id}]" placeholder="Chú thích..."
                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                if (productsToShow.length === 0) {
                    const emptyRow = document.createElement('tr');
                    let message = '';
                    if (type === 'contract') {
                        message =
                            'Chưa có thành phẩm hợp đồng nào được chọn. Vui lòng thêm thành phẩm trước khi cập nhật mã thiết bị.';
                    } else if (type === 'backup') {
                        message =
                            'Chưa có thiết bị dự phòng nào được chọn. Vui lòng thêm thiết bị trước khi cập nhật mã thiết bị.';
                    } else {
                        message =
                            'Chưa có thành phẩm nào được chọn. Vui lòng thêm thành phẩm trước khi cập nhật mã thiết bị.';
                    }

                    emptyRow.innerHTML = `
                        <td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">
                            ${message}
                        </td>
                    `;
                    tbody.appendChild(emptyRow);
                }
            }

            if (closeDeviceCodeModalBtn) {
                closeDeviceCodeModalBtn.addEventListener('click', function() {
                    deviceCodeModal.classList.add('hidden');
                });
            }

            if (cancelDeviceCodesBtn) {
                cancelDeviceCodesBtn.addEventListener('click', function() {
                    deviceCodeModal.classList.add('hidden');
                });
            }

            if (saveDeviceCodesBtn) {
                saveDeviceCodesBtn.addEventListener('click', function() {
                    // Mã xử lý lưu thông tin mã thiết bị
                    alert('Đã lưu thông tin mã thiết bị!');
                    deviceCodeModal.classList.add('hidden');
                });
            }

            if (importDeviceCodesBtn) {
                importDeviceCodesBtn.addEventListener('click', function() {
                    // Tạo input file ẩn để chọn file Excel
                    const fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.accept = '.xlsx,.xls,.csv';
                    fileInput.style.display = 'none';

                    fileInput.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            // Hiển thị thông báo đang xử lý
                            alert(
                                `Đang import file: ${file.name}\nChức năng này sẽ được phát triển trong phiên bản tiếp theo.`
                            );
                        }
                    });

                    document.body.appendChild(fileInput);
                    fileInput.click();
                    document.body.removeChild(fileInput);
                });
            }

            // Hàm kiểm tra tồn kho tổng hợp
            function validateTotalStock() {
                const stockErrors = [];
                const groupedItems = {};

                // Nhóm sản phẩm hợp đồng
                selectedContractProducts.forEach(product => {
                    const key = `${product.type}_${product.id}_${product.selected_warehouse_id}`;
                    if (!groupedItems[key]) {
                        groupedItems[key] = {
                            product: product,
                            totalQuantity: 0,
                            categories: []
                        };
                    }
                    groupedItems[key].totalQuantity += parseInt(product.quantity);
                    groupedItems[key].categories.push('hợp đồng');
                });

                // Nhóm sản phẩm dự phòng
                selectedBackupProducts.forEach(product => {
                    const key = `${product.type}_${product.id}_${product.selected_warehouse_id}`;
                    if (!groupedItems[key]) {
                        groupedItems[key] = {
                            product: product,
                            totalQuantity: 0,
                            categories: []
                        };
                    }
                    groupedItems[key].totalQuantity += parseInt(product.quantity);
                    groupedItems[key].categories.push('dự phòng');
                });

                // Kiểm tra từng nhóm sản phẩm
                Object.keys(groupedItems).forEach(key => {
                    const item = groupedItems[key];
                    const currentStock = item.product.current_stock || 0;

                    if (item.totalQuantity > currentStock) {
                        const categoriesText = item.categories.join(', ');
                        stockErrors.push(
                            `${item.product.code} - ${item.product.name}: ` +
                            `Tồn kho ${currentStock}, yêu cầu ${item.totalQuantity} ` +
                            `(Tổng từ: ${categoriesText})`
                        );
                    }
                });

                return stockErrors;
            }

            // Hàm hiển thị cảnh báo tồn kho
            function showStockWarnings() {
                const stockErrors = validateTotalStock();

                // Xóa cảnh báo cũ
                const oldWarnings = document.querySelectorAll('.stock-warning');
                oldWarnings.forEach(warning => warning.remove());

                if (stockErrors.length > 0) {
                    // Tạo div cảnh báo
                    const warningDiv = document.createElement('div');
                    warningDiv.className =
                        'stock-warning bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4';
                    warningDiv.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Cảnh báo tồn kho:</strong>
                        </div>
                        <ul class="mt-2 ml-6">
                            ${stockErrors.map(error => `<li>• ${error}</li>`).join('')}
                        </ul>
                    `;

                    // Thêm vào đầu form
                    const form = document.getElementById('dispatch-form');
                    form.insertBefore(warningDiv, form.firstChild);
                }
            }

            // Hàm kiểm tra serial numbers có bị trùng lặp không
            function validateSerialNumbers() {
                const allSerialSelects = document.querySelectorAll('select[name*="serials"]');
                const selectedSerials = [];
                const duplicates = [];

                // Thu thập tất cả serial numbers đã chọn
                allSerialSelects.forEach(select => {
                    if (select.value && select.value.trim() !== '') {
                        const serialValue = select.value.trim();
                        if (selectedSerials.includes(serialValue)) {
                            if (!duplicates.includes(serialValue)) {
                                duplicates.push(serialValue);
                            }
                        } else {
                            selectedSerials.push(serialValue);
                        }
                    }
                });

                return duplicates;
            }

            // Hàm hiển thị cảnh báo serial trùng lặp
            function showSerialDuplicateWarning(duplicates) {
                // Xóa cảnh báo cũ
                const oldWarning = document.querySelector('.serial-duplicate-warning');
                if (oldWarning) {
                    oldWarning.remove();
                }

                if (duplicates.length > 0) {
                    // Tạo div cảnh báo
                    const warningDiv = document.createElement('div');
                    warningDiv.className =
                        'serial-duplicate-warning bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
                    warningDiv.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Cảnh báo Serial trùng lặp:</strong>
                        </div>
                        <p class="mt-2">Các serial numbers sau đã được chọn nhiều lần: <strong>${duplicates.join(', ')}</strong></p>
                        <p class="text-sm mt-1">Vui lòng chọn serial numbers khác nhau cho mỗi sản phẩm.</p>
                    `;

                    // Thêm vào đầu form
                    const form = document.getElementById('dispatch-form');
                    form.insertBefore(warningDiv, form.firstChild);
                }
            }

            // Hàm validate serial khi user thay đổi selection
            function validateSerialOnChange() {
                const duplicates = validateSerialNumbers();
                showSerialDuplicateWarning(duplicates);

                // Disable các option đã chọn ở select khác
                updateSerialOptionsAvailability();
            }

            // Hàm cập nhật available options cho serial selects
            function updateSerialOptionsAvailability() {
                const allSerialSelects = document.querySelectorAll('select[name*="serials"]');
                const selectedValues = [];

                // Thu thập tất cả giá trị đã chọn
                allSerialSelects.forEach(select => {
                    if (select.value && select.value.trim() !== '') {
                        selectedValues.push(select.value.trim());
                    }
                });

                // Cập nhật từng select
                allSerialSelects.forEach(currentSelect => {
                    const currentValue = currentSelect.value;

                    Array.from(currentSelect.options).forEach(option => {
                        if (option.value === '') return; // Skip default option

                        if (selectedValues.includes(option.value) && option.value !==
                            currentValue) {
                            // Serial này đã được chọn ở select khác
                            option.disabled = true;
                            option.style.color = '#9CA3AF'; // Gray color
                            option.style.backgroundColor = '#F3F4F6';
                        } else {
                            // Serial có thể chọn
                            option.disabled = false;
                            option.style.color = '';
                            option.style.backgroundColor = '';
                        }
                    });
                });
            }

            // Xử lý form submit
            const dispatchForm = document.getElementById('dispatch-form');
            if (dispatchForm) {
                dispatchForm.addEventListener('submit', function(e) {
                    console.log('=== FORM SUBMIT EVENT TRIGGERED ===');

                    // Debug: Kiểm tra giá trị các trường quan trọng
                    const dispatchType = document.getElementById('dispatch_type').value;
                    const projectReceiver = document.getElementById('project_receiver').value;
                    const rentalReceiver = document.getElementById('rental_receiver').value;

                    console.log('Dispatch type:', dispatchType);
                    console.log('Project receiver:', projectReceiver);
                    console.log('Rental receiver:', rentalReceiver);

                    // Đảm bảo project_receiver có giá trị khi dispatch_type là rental
                    if (dispatchType === 'rental' && !projectReceiver && rentalReceiver) {
                        console.log('Syncing rental receiver to project receiver');
                        document.getElementById('project_receiver').value = rentalReceiver;
                    }

                    // Kiểm tra xem có sản phẩm nào được chọn không dựa trên dispatch_detail
                    const dispatchDetail = document.getElementById('dispatch_detail').value;
                    let hasRequiredProducts = false;
                    let errorMessage = '';

                    if (dispatchDetail === 'all') {
                        // Với "Tất cả", cần ít nhất một sản phẩm hợp đồng VÀ một thiết bị dự phòng
                        if (selectedContractProducts.length === 0 && selectedBackupProducts.length === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Vui lòng chọn ít nhất một sản phẩm hợp đồng và một thiết bị dự phòng để xuất kho!';
                        } else if (selectedContractProducts.length === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Phiếu xuất "Tất cả" phải có ít nhất một sản phẩm hợp đồng!';
                        } else if (selectedBackupProducts.length === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Phiếu xuất "Tất cả" phải có ít nhất một thiết bị dự phòng!';
                        } else {
                            hasRequiredProducts = true;
                        }
                    } else if (dispatchDetail === 'contract') {
                        // Với "Xuất theo hợp đồng", cần ít nhất một sản phẩm hợp đồng và KHÔNG được có dự phòng
                        if (selectedContractProducts.length === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Vui lòng chọn ít nhất một thành phẩm theo hợp đồng để xuất kho!';
                        } else if (selectedBackupProducts.length > 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Phiếu xuất theo hợp đồng không được chứa thiết bị dự phòng! Vui lòng chọn "Tất cả" nếu muốn xuất cả hai loại.';
                        } else {
                            hasRequiredProducts = true;
                        }
                    } else if (dispatchDetail === 'backup') {
                        // Với "Xuất thiết bị dự phòng", cần ít nhất một thiết bị dự phòng và KHÔNG được có hợp đồng
                        if (selectedBackupProducts.length === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Vui lòng chọn ít nhất một thiết bị dự phòng để xuất kho!';
                        } else if (selectedContractProducts.length > 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Phiếu xuất thiết bị dự phòng không được chứa sản phẩm hợp đồng! Vui lòng chọn "Tất cả" nếu muốn xuất cả hai loại.';
                        } else {
                            hasRequiredProducts = true;
                        }
                    }

                    if (!hasRequiredProducts) {
                        e.preventDefault();
                        console.log('FORM SUBMIT PREVENTED: No required products selected for dispatch_detail:', dispatchDetail);
                        alert(errorMessage);
                        return;
                    }

                    // Kiểm tra tồn kho trước khi submit
                    const stockErrors = validateTotalStock();
                    if (stockErrors.length > 0) {
                        e.preventDefault();
                        console.log('FORM SUBMIT PREVENTED: Stock validation failed');
                        alert('Không đủ tồn kho:\n\n' + stockErrors.join('\n'));
                        return;
                    }

                    // Kiểm tra serial numbers trùng lặp trước khi submit
                    const duplicateSerials = validateSerialNumbers();
                    if (duplicateSerials.length > 0) {
                        e.preventDefault();
                        console.log('FORM SUBMIT PREVENTED: Duplicate serial numbers');
                        alert('Có serial numbers bị trùng lặp:\n\n' + duplicateSerials.join(', ') +
                            '\n\nVui lòng chọn serial numbers khác nhau!');
                        showSerialDuplicateWarning(duplicateSerials);
                        return;
                    }

                    console.log('Product validation passed, proceeding with form submission...');

                    // Bắt đầu thêm items từ index 0
                    let itemIndex = 0;

                    // Thêm các input ẩn cho sản phẩm hợp đồng
                    selectedContractProducts.forEach((product) => {
                        const itemTypeInput = document.createElement('input');
                        itemTypeInput.type = 'hidden';
                        itemTypeInput.name = `items[${itemIndex}][item_type]`;
                        const validType = ['material', 'product', 'good'].includes(product
                                .type) ?
                            product.type : 'material';
                        itemTypeInput.value = validType;
                        this.appendChild(itemTypeInput);

                        const itemIdInput = document.createElement('input');
                        itemIdInput.type = 'hidden';
                        itemIdInput.name = `items[${itemIndex}][item_id]`;
                        itemIdInput.value = product.id;
                        this.appendChild(itemIdInput);

                        const quantityInput = document.createElement('input');
                        quantityInput.type = 'hidden';
                        quantityInput.name = `items[${itemIndex}][quantity]`;
                        quantityInput.value = product.quantity;
                        this.appendChild(quantityInput);

                        const warehouseIdInput = document.createElement('input');
                        warehouseIdInput.type = 'hidden';
                        warehouseIdInput.name = `items[${itemIndex}][warehouse_id]`;
                        warehouseIdInput.value = product.selected_warehouse_id;
                        this.appendChild(warehouseIdInput);

                        // Thêm category cho sản phẩm hợp đồng
                        const categoryInput = document.createElement('input');
                        categoryInput.type = 'hidden';
                        categoryInput.name = `items[${itemIndex}][category]`;
                        categoryInput.value = 'contract';
                        this.appendChild(categoryInput);

                        // Thêm serial numbers cho sản phẩm hợp đồng nếu có
                        const contractSerialSelects = document.querySelectorAll(
                            `select[name^="contract_serials[${product.id}]"]`);
                        if (contractSerialSelects.length > 0) {
                            const serialsArray = Array.from(contractSerialSelects)
                                .map(select => select.value.trim())
                                .filter(value => value.length > 0);

                            if (serialsArray.length > 0) {
                                const serialNumbersInput = document.createElement('input');
                                serialNumbersInput.type = 'hidden';
                                serialNumbersInput.name =
                                    `items[${itemIndex}][serial_numbers]`;
                                serialNumbersInput.value = JSON.stringify(serialsArray);
                                this.appendChild(serialNumbersInput);

                                console.log(`Contract product ${product.id} serials:`,
                                    serialsArray);
                            }
                        }

                        itemIndex++;
                    });

                    // Thêm các input ẩn cho thiết bị dự phòng
                    selectedBackupProducts.forEach((product) => {
                        const itemTypeInput = document.createElement('input');
                        itemTypeInput.type = 'hidden';
                        itemTypeInput.name = `items[${itemIndex}][item_type]`;
                        const validType = ['material', 'product', 'good'].includes(product
                                .type) ?
                            product.type : 'material';
                        itemTypeInput.value = validType;
                        this.appendChild(itemTypeInput);

                        const itemIdInput = document.createElement('input');
                        itemIdInput.type = 'hidden';
                        itemIdInput.name = `items[${itemIndex}][item_id]`;
                        itemIdInput.value = product.id;
                        this.appendChild(itemIdInput);

                        const quantityInput = document.createElement('input');
                        quantityInput.type = 'hidden';
                        quantityInput.name = `items[${itemIndex}][quantity]`;
                        quantityInput.value = product.quantity;
                        this.appendChild(quantityInput);

                        const warehouseIdInput = document.createElement('input');
                        warehouseIdInput.type = 'hidden';
                        warehouseIdInput.name = `items[${itemIndex}][warehouse_id]`;
                        warehouseIdInput.value = product.selected_warehouse_id;
                        this.appendChild(warehouseIdInput);

                        // Thêm category cho thiết bị dự phòng
                        const categoryInput = document.createElement('input');
                        categoryInput.type = 'hidden';
                        categoryInput.name = `items[${itemIndex}][category]`;
                        categoryInput.value = 'backup';
                        this.appendChild(categoryInput);

                        // Thêm serial numbers cho thiết bị dự phòng nếu có
                        const backupSerialSelects = document.querySelectorAll(
                            `select[name^="backup_serials[${product.id}]"]`);
                        if (backupSerialSelects.length > 0) {
                            const serialsArray = Array.from(backupSerialSelects)
                                .map(select => select.value.trim())
                                .filter(value => value.length > 0);

                            if (serialsArray.length > 0) {
                                const serialNumbersInput = document.createElement('input');
                                serialNumbersInput.type = 'hidden';
                                serialNumbersInput.name =
                                    `items[${itemIndex}][serial_numbers]`;
                                serialNumbersInput.value = JSON.stringify(serialsArray);
                                this.appendChild(serialNumbersInput);

                                console.log(`Backup product ${product.id} serials:`,
                                    serialsArray);
                            }
                        }

                        itemIndex++;
                    });

                    // Debug: Log all form data và kiểm tra xem form có được submit không
                    console.log('Form is being submitted...');
                    console.log('Selected contract products:', selectedContractProducts);
                    console.log('Selected backup products:', selectedBackupProducts);

                    const formData = new FormData(this);
                    console.log('Form data entries:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key, ':', value);
                    }

                    // Kiểm tra xem có data được thêm vào form không
                    const hiddenInputs = this.querySelectorAll('input[type="hidden"]');
                    console.log('Hidden inputs count:', hiddenInputs.length);

                    // Disable submit button để tránh double submit
                    const submitBtn = document.getElementById('submit-btn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML =
                            '<i class="fas fa-spinner fa-spin mr-2"></i> Đang xử lý...';
                    }
                });
            } else {
                console.error('Dispatch form not found!');
            }
        });
    </script>
</body>

</html>
