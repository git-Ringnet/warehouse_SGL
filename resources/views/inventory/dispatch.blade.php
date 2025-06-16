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
                                <option value="other">Khác</option>
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
                        <div>
                            <label for="project_receiver"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Dự án<span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="project_receiver" name="project_receiver" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập tên dự án hoặc người nhận...">
                        </div>
                        <div>
                            <label for="warranty_period" class="block text-sm font-medium text-gray-700 mb-1">Thời gian
                                bảo hành</label>
                            <input type="text" id="warranty_period" name="warranty_period"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="VD: 12 tháng">
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
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho
                                xuất <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho xuất --</option>
                                @if (isset($warehouses))
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
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
                <!-- Danh sách thành phẩm chính (hiển thị khi chọn contract hoặc backup riêng lẻ) -->
                <div id="main-product-list" class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 id="product-list-title" class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i id="product-list-icon" class="fas fa-boxes text-blue-500 mr-2"></i>
                        <span id="product-list-text">Danh sách thành phẩm xuất kho</span>
                    </h2>

                    <!-- Chọn thành phẩm -->
                    <div class="mb-4">
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <select id="product_select"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Chọn thành phẩm để thêm vào phiếu xuất --</option>
                                </select>
                            </div>
                            <button type="button" id="add_product_btn"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                <i class="fas fa-plus mr-1"></i> Thêm
                            </button>
                        </div>
                    </div>

                    <!-- Bảng thành phẩm đã chọn -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã SP
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên thành phẩm
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Đơn vị
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tồn kho
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số lượng xuất
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Serial
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="product_list" class="bg-white divide-y divide-gray-200">
                                <!-- Dữ liệu thành phẩm sẽ được thêm vào đây -->
                                <tr id="no_products_row">
                                    <td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        Chưa có thành phẩm nào được thêm vào phiếu xuất
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Nút cập nhật mã thiết bị -->
                    <div class="mt-4 flex justify-end">
                        <button type="button" id="update_device_codes_btn"
                            class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i> Cập nhật mã thiết bị
                        </button>
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
                                    <td colspan="7" class="px-6 py-4 text-sm text-blue-500 text-center">
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
                                    <td colspan="7" class="px-6 py-4 text-sm text-orange-500 text-center">
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
            const productSelect = document.getElementById('product_select');
            const addProductBtn = document.getElementById('add_product_btn');
            const productList = document.getElementById('product_list');
            const noProductsRow = document.getElementById('no_products_row');
            const dispatchDetailSelect = document.getElementById('dispatch_detail');
            const dispatchTypeSelect = document.getElementById('dispatch_type');
            const projectReceiverSelect = document.getElementById('project_receiver');
            const warehouseSelect = document.getElementById('warehouse_id');

            let selectedProducts = [];
            let selectedContractProducts = [];
            let selectedBackupProducts = [];
            let availableItems = []; // Lưu danh sách sản phẩm từ kho đã chọn

            // Khi kho được chọn, load sản phẩm từ kho đó
            if (warehouseSelect) {
                warehouseSelect.addEventListener('change', function() {
                    const warehouseId = this.value;
                    if (warehouseId) {
                        loadAvailableItems(warehouseId);
                    } else {
                        // Clear product selects nếu không chọn kho
                        console.log('Clearing product selects');
                        clearProductSelects();
                        availableItems = [];
                    }
                });
            }

            if (addProductBtn) {
                addProductBtn.addEventListener('click', function() {
                    const selectedProductId = productSelect.value;

                    if (!selectedProductId) {
                        alert('Vui lòng chọn thành phẩm để thêm!');
                        return;
                    }

                    // Sử dụng hàm chung để thêm sản phẩm
                    addProductToTable(parseInt(selectedProductId));

                    // Reset dropdown về trạng thái ban đầu
                    productSelect.value = '';
                });
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

            // Hàm load sản phẩm từ kho
            async function loadAvailableItems(warehouseId) {
                try {
                    const response = await fetch(`/api/dispatch/items?warehouse_id=${warehouseId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        // Chuẩn hóa dữ liệu để tương thích với frontend
                        availableItems = (data.items || []).map(item => ({
                            id: item.id,
                            type: item.type,
                            code: item.code,
                            name: item.name,
                            unit: item.unit,
                            stock: item.available_quantity, // Map available_quantity to stock
                            available_quantity: item.available_quantity
                        }));
                        updateProductSelects();
                    } else {
                        alert('Lỗi từ server: ' + (data.message || 'Không thể tải danh sách sản phẩm'));
                    }
                } catch (error) {
                    alert('Không thể tải danh sách sản phẩm từ kho: ' + error.message);
                }
            }

            // Cập nhật tất cả dropdown sản phẩm
            function updateProductSelects() {
                // Cập nhật dropdown chính
                productSelect.innerHTML = '<option value="">-- Chọn sản phẩm --</option>';
                
                // Cập nhật dropdown hợp đồng
                const contractProductSelect = document.getElementById('contract_product_select');
                if (contractProductSelect) {
                    contractProductSelect.innerHTML = '<option value="">-- Chọn sản phẩm hợp đồng --</option>';
                }
                
                // Cập nhật dropdown dự phòng
                const backupProductSelect = document.getElementById('backup_product_select');
                if (backupProductSelect) {
                    backupProductSelect.innerHTML = '<option value="">-- Chọn thiết bị dự phòng --</option>';
                }
                
                // Thêm options từ availableItems
                availableItems.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.code} - ${item.name} (Tồn: ${item.stock} ${item.unit})`;
                    
                    // Clone option cho các dropdown khác
                    const contractOption = option.cloneNode(true);
                    const backupOption = option.cloneNode(true);
                    
                    productSelect.appendChild(option);
                    if (contractProductSelect) contractProductSelect.appendChild(contractOption);
                    if (backupProductSelect) backupProductSelect.appendChild(backupOption);
                });
            }

            // Clear tất cả dropdown sản phẩm
            function clearProductSelects() {
                productSelect.innerHTML = '<option value="">-- Chọn sản phẩm --</option>';
                
                const contractProductSelect = document.getElementById('contract_product_select');
                if (contractProductSelect) {
                    contractProductSelect.innerHTML = '<option value="">-- Chọn sản phẩm hợp đồng --</option>';
                }
                
                const backupProductSelect = document.getElementById('backup_product_select');
                if (backupProductSelect) {
                    backupProductSelect.innerHTML = '<option value="">-- Chọn thiết bị dự phòng --</option>';
                }
            }

            // Hàm thêm sản phẩm vào bảng chính
            function addProductToTable(productId, type = null) {
                const foundProduct = availableItems.find(p => p.id == productId);
                
                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                // Xác định type dựa trên lựa chọn hiện tại nếu không được chỉ định
                if (!type) {
                    const dispatchDetailSelect = document.getElementById('dispatch_detail');
                    const selectedDetail = dispatchDetailSelect ? dispatchDetailSelect.value : '';
                    
                    if (selectedDetail === 'contract') {
                        type = 'contract';
                    } else if (selectedDetail === 'backup') {
                        type = 'backup';
                    } else {
                        type = 'manual';
                    }
                }

                // Kiểm tra xem sản phẩm đã được thêm chưa
                const existingProduct = selectedProducts.find(p => p.id === foundProduct.id);
                
                if (existingProduct) {
                    alert('Sản phẩm này đã được thêm vào phiếu xuất!');
                    return;
                } else {
                    // Thêm sản phẩm mới
                    selectedProducts.push({
                        ...foundProduct,
                        quantity: 1,
                        type: type,
                        types: [type]
                    });
                    
                    // Cập nhật giao diện
                    renderProductTable();
                }
            }

            // Hàm thêm sản phẩm hợp đồng
            function addContractProduct(productId) {
                const foundProduct = availableItems.find(p => p.id == productId);
                
                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                // Kiểm tra xem sản phẩm đã được thêm chưa
                const existingProduct = selectedContractProducts.find(p => p.id === foundProduct.id);
                
                if (existingProduct) {
                    alert('Sản phẩm này đã được thêm vào danh sách hợp đồng!');
                    return;
                } else {
                    // Thêm sản phẩm mới
                    selectedContractProducts.push({
                        ...foundProduct,
                        quantity: 1
                    });
                    
                    // Cập nhật giao diện
                    renderContractProductTable();
                }
            }

            // Hàm thêm thiết bị dự phòng
            function addBackupProduct(productId) {
                const foundProduct = availableItems.find(p => p.id == productId);
                
                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                // Kiểm tra xem sản phẩm đã được thêm chưa
                const existingProduct = selectedBackupProducts.find(p => p.id === foundProduct.id);
                
                if (existingProduct) {
                    alert('Thiết bị này đã được thêm vào danh sách dự phòng!');
                    return;
                } else {
                    // Thêm sản phẩm mới
                    selectedBackupProducts.push({
                        ...foundProduct,
                        quantity: 1
                    });
                    
                    // Cập nhật giao diện
                    renderBackupProductTable();
                }
            }

            // Xử lý thay đổi chi tiết xuất kho
            if (dispatchDetailSelect) {
                dispatchDetailSelect.addEventListener('change', function() {
                    const selectedDetail = this.value;
                    
                    // Lấy các elements
                    const productListText = document.getElementById('product-list-text');
                    const productListIcon = document.getElementById('product-list-icon');
                    const mainProductList = document.getElementById('main-product-list');
                    const contractProductList = document.getElementById('contract-product-list');
                    const backupProductList = document.getElementById('backup-product-list');

                    // Ẩn tất cả trước
                    mainProductList.classList.add('hidden');
                    contractProductList.classList.add('hidden');
                    backupProductList.classList.add('hidden');

                    // Hiển thị container tương ứng
                    if (selectedDetail === 'all') {
                        // Hiển thị 2 danh sách riêng biệt
                        contractProductList.classList.remove('hidden');
                        backupProductList.classList.remove('hidden');
                    } else if (selectedDetail === 'contract') {
                        // Hiển thị danh sách chính với tiêu đề hợp đồng
                        mainProductList.classList.remove('hidden');
                        productListText.textContent = 'Danh sách thành phẩm theo hợp đồng';
                        productListIcon.className = 'fas fa-file-contract text-blue-500 mr-2';
                    } else if (selectedDetail === 'backup') {
                        // Hiển thị danh sách chính với tiêu đề dự phòng
                        mainProductList.classList.remove('hidden');
                        productListText.textContent = 'Danh sách thiết bị dự phòng';
                        productListIcon.className = 'fas fa-shield-alt text-orange-500 mr-2';
                    } else {
                        // Mặc định hiển thị danh sách chính
                        mainProductList.classList.remove('hidden');
                        productListText.textContent = 'Danh sách thành phẩm xuất kho';
                        productListIcon.className = 'fas fa-boxes text-blue-500 mr-2';
                    }
                });
            }

            // Xử lý thay đổi loại hình xuất kho
            if (dispatchTypeSelect) {
                dispatchTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;

                    // Cập nhật danh sách dự án/người nhận dựa vào loại hình
                    updateProjectReceiverOptions(selectedType);
                });
            }

            // Xử lý thay đổi dự án/người nhận - cập nhật thời gian bảo hành
            if (projectReceiverSelect) {
                projectReceiverSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const warrantyPeriodInput = document.getElementById('warranty_period');

                    if (selectedOption && selectedOption.dataset.warranty) {
                        warrantyPeriodInput.value = selectedOption.dataset.warranty;
                    } else {
                        warrantyPeriodInput.value = '';
                    }
                });
            }

            // Hàm hiển thị bảng sản phẩm
            function renderProductTable() {

                // Xóa tất cả hàng hiện tại (trừ hàng "không có sản phẩm")
                const existingRows = productList.querySelectorAll('tr:not(#no_products_row)');
                existingRows.forEach(row => row.remove());

                if (selectedProducts.length === 0) {
                    noProductsRow.classList.remove('hidden');
                    return;
                }

                noProductsRow.classList.add('hidden');

                selectedProducts.forEach((product, index) => {
                    // Tạo badges cho loại sản phẩm
                    let typeBadges = '';
                    if (product.types && product.types.length > 0) {
                        product.types.forEach(type => {
                            let badgeClass = '';
                            let badgeText = '';
                            switch(type) {
                                case 'manual':
                                    badgeClass = 'bg-gray-100 text-gray-800';
                                    badgeText = 'Thủ công';
                                    break;
                                case 'contract':
                                    badgeClass = 'bg-blue-100 text-blue-800';
                                    badgeText = 'Hợp đồng';
                                    break;
                                case 'backup':
                                    badgeClass = 'bg-orange-100 text-orange-800';
                                    badgeText = 'Dự phòng';
                                    break;
                            }
                            typeBadges += `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${badgeClass} mr-1">${badgeText}</span>`;
                        });
                    }

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-medium">${product.code}</div>
                            <div class="text-xs text-gray-500 mt-1">${typeBadges}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.unit}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.stock}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" value="${product.quantity}" min="1" max="${product.stock}" 
                                class="w-20 border border-gray-300 rounded px-2 py-1 text-sm quantity-input" 
                                data-index="${index}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" placeholder="Nhập serial (tùy chọn)" 
                                class="w-100 border border-gray-300 rounded px-2 py-1 text-sm serial-input" 
                                name="serials[${product.id}]" data-product-index="${index}">
                            <div class="text-xs text-gray-400 mt-1">Cách nhau bằng dấu phẩy</div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-product" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    productList.appendChild(row);
                });

                // Thêm event listeners cho các input số lượng
                const quantityInputs = productList.querySelectorAll('.quantity-input');
                quantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newQuantity = parseInt(this.value);

                        if (newQuantity > 0 && newQuantity <= selectedProducts[index].stock) {
                            selectedProducts[index].quantity = newQuantity;
                        } else {
                            alert('Số lượng không hợp lệ!');
                            this.value = selectedProducts[index].quantity;
                        }
                    });
                });

                // Thêm event listeners cho nút xóa
                const removeButtons = productList.querySelectorAll('.remove-product');
                removeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedProducts.splice(index, 1);
                        renderProductTable();
                    });
                });
            }

            // Hàm hiển thị bảng sản phẩm hợp đồng
            function renderContractProductTable() {
                const contractProductList = document.getElementById('contract_product_list');
                const noContractProductsRow = document.getElementById('no_contract_products_row');
                
                if (!contractProductList || !noContractProductsRow) return;

                // Xóa tất cả hàng hiện tại (trừ hàng "không có sản phẩm")
                const existingRows = contractProductList.querySelectorAll('tr:not(#no_contract_products_row)');
                existingRows.forEach(row => row.remove());

                if (selectedContractProducts.length === 0) {
                    noContractProductsRow.classList.remove('hidden');
                    return;
                }

                noContractProductsRow.classList.add('hidden');

                selectedContractProducts.forEach((product, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-900 font-medium">${product.code}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.unit}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.stock}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" value="${product.quantity}" min="1" max="${product.stock}" 
                                class="w-20 border border-blue-300 rounded px-2 py-1 text-sm contract-quantity-input" 
                                data-index="${index}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" placeholder="Nhập serial (tùy chọn)" 
                                class="w-100 border border-blue-300 rounded px-2 py-1 text-sm contract-serial-input" 
                                name="contract_serials[${product.id}]">
                            <div class="text-xs text-blue-400 mt-1">Không bắt buộc</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-contract-product" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    contractProductList.appendChild(row);
                });

                // Thêm event listeners cho các input và nút xóa
                const contractQuantityInputs = contractProductList.querySelectorAll('.contract-quantity-input');
                contractQuantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newQuantity = parseInt(this.value);
                        if (selectedContractProducts[index]) {
                            selectedContractProducts[index].quantity = newQuantity;
                        }
                    });
                });

                const removeContractButtons = contractProductList.querySelectorAll('.remove-contract-product');
                removeContractButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedContractProducts.splice(index, 1);
                        renderContractProductTable();
                    });
                });
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
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-900 font-medium">${product.code}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.unit}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.stock}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" value="${product.quantity}" min="1" max="${product.stock}" 
                                class="w-20 border border-orange-300 rounded px-2 py-1 text-sm backup-quantity-input" 
                                data-index="${index}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" placeholder="Nhập serial (tùy chọn)" 
                                class="w-100 border border-orange-300 rounded px-2 py-1 text-sm backup-serial-input" 
                                name="backup_serials[${product.id}]">
                            <div class="text-xs text-orange-400 mt-1">Không bắt buộc</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-backup-product" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    backupProductList.appendChild(row);
                });

                // Thêm event listeners cho các input và nút xóa
                const backupQuantityInputs = backupProductList.querySelectorAll('.backup-quantity-input');
                backupQuantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newQuantity = parseInt(this.value);
                        if (selectedBackupProducts[index]) {
                            selectedBackupProducts[index].quantity = newQuantity;
                        }
                    });
                });

                const removeBackupButtons = backupProductList.querySelectorAll('.remove-backup-product');
                removeBackupButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedBackupProducts.splice(index, 1);
                        renderBackupProductTable();
                    });
                });
            }

            // Xử lý modal cập nhật mã thiết bị
            const updateDeviceCodesBtn = document.getElementById('update_device_codes_btn');
            const updateContractDeviceCodesBtn = document.getElementById('update_contract_device_codes_btn');
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
                        message = 'Chưa có thành phẩm hợp đồng nào được chọn. Vui lòng thêm thành phẩm trước khi cập nhật mã thiết bị.';
                    } else if (type === 'backup') {
                        message = 'Chưa có thiết bị dự phòng nào được chọn. Vui lòng thêm thiết bị trước khi cập nhật mã thiết bị.';
                    } else {
                        message = 'Chưa có thành phẩm nào được chọn. Vui lòng thêm thành phẩm trước khi cập nhật mã thiết bị.';
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
                            alert(`Đang import file: ${file.name}\nChức năng này sẽ được phát triển trong phiên bản tiếp theo.`);
                            
                            // TODO: Thêm logic xử lý file Excel ở đây
                            // - Đọc file Excel
                            // - Parse dữ liệu
                            // - Cập nhật vào bảng
                        }
                    });
                    
                    document.body.appendChild(fileInput);
                    fileInput.click();
                    document.body.removeChild(fileInput);
                });
            }

            // Xử lý form submit
            const dispatchForm = document.getElementById('dispatch-form');
            if (dispatchForm) {
                dispatchForm.addEventListener('submit', function(e) {
                    
                    // Kiểm tra xem có sản phẩm nào được chọn không
                    if (selectedProducts.length === 0 && selectedContractProducts.length === 0 && selectedBackupProducts.length === 0) {
                        e.preventDefault();
                        alert('Vui lòng chọn ít nhất một sản phẩm để xuất kho!');
                        return;
                    }

                    // Thêm các input ẩn cho sản phẩm chính
                    let itemIndex = 0;
                    selectedProducts.forEach((product) => {
                        const itemTypeInput = document.createElement('input');
                        itemTypeInput.type = 'hidden';
                        itemTypeInput.name = `items[${itemIndex}][item_type]`;
                        // Đảm bảo item_type luôn có giá trị hợp lệ
                        const validType = ['material', 'product', 'good'].includes(product.type) ? product.type : 'material';
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

                        // Thêm category input
                        const categoryInput = document.createElement('input');
                        categoryInput.type = 'hidden';
                        categoryInput.name = `items[${itemIndex}][category]`;
                        // Xác định category dựa trên dispatch_detail hoặc product type
                        const dispatchDetail = document.getElementById('dispatch_detail').value;
                        let category = 'general';
                        if (dispatchDetail === 'contract' || (product.type && product.type === 'contract')) {
                            category = 'contract';
                        } else if (dispatchDetail === 'backup' || (product.type && product.type === 'backup')) {
                            category = 'backup';
                        }
                        categoryInput.value = category;
                        this.appendChild(categoryInput);

                        // Thêm serial numbers nếu có
                        const serialInput = document.querySelector(`input[name="serials[${product.id}]"]`);
                        if (serialInput && serialInput.value.trim()) {
                            const serialNumbersInput = document.createElement('input');
                            serialNumbersInput.type = 'hidden';
                            serialNumbersInput.name = `items[${itemIndex}][serial_numbers]`;
                            // Chuyển string thành array, loại bỏ khoảng trắng thừa
                            const serialsArray = serialInput.value.split(',').map(s => s.trim()).filter(s => s.length > 0);
                            serialNumbersInput.value = JSON.stringify(serialsArray);
                            this.appendChild(serialNumbersInput);
                        }

                        itemIndex++;
                    });

                    // Thêm các input ẩn cho sản phẩm hợp đồng
                    selectedContractProducts.forEach((product) => {
                        const itemTypeInput = document.createElement('input');
                        itemTypeInput.type = 'hidden';
                        itemTypeInput.name = `items[${itemIndex}][item_type]`;
                        const validType = ['material', 'product', 'good'].includes(product.type) ? product.type : 'material';
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

                        // Thêm category cho sản phẩm hợp đồng
                        const categoryInput = document.createElement('input');
                        categoryInput.type = 'hidden';
                        categoryInput.name = `items[${itemIndex}][category]`;
                        categoryInput.value = 'contract';
                        this.appendChild(categoryInput);

                        // Thêm serial numbers cho sản phẩm hợp đồng nếu có
                        const contractSerialInput = document.querySelector(`input[name="contract_serials[${product.id}]"]`);
                        if (contractSerialInput && contractSerialInput.value.trim()) {
                            const serialNumbersInput = document.createElement('input');
                            serialNumbersInput.type = 'hidden';
                            serialNumbersInput.name = `items[${itemIndex}][serial_numbers]`;
                            const serialsArray = contractSerialInput.value.split(',').map(s => s.trim()).filter(s => s.length > 0);
                            serialNumbersInput.value = JSON.stringify(serialsArray);
                            this.appendChild(serialNumbersInput);
                        }

                        itemIndex++;
                    });

                    // Thêm các input ẩn cho thiết bị dự phòng
                    selectedBackupProducts.forEach((product) => {
                        const itemTypeInput = document.createElement('input');
                        itemTypeInput.type = 'hidden';
                        itemTypeInput.name = `items[${itemIndex}][item_type]`;
                        const validType = ['material', 'product', 'good'].includes(product.type) ? product.type : 'material';
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

                        // Thêm category cho thiết bị dự phòng
                        const categoryInput = document.createElement('input');
                        categoryInput.type = 'hidden';
                        categoryInput.name = `items[${itemIndex}][category]`;
                        categoryInput.value = 'backup';
                        this.appendChild(categoryInput);

                        // Thêm serial numbers cho thiết bị dự phòng nếu có
                        const backupSerialInput = document.querySelector(`input[name="backup_serials[${product.id}]"]`);
                        if (backupSerialInput && backupSerialInput.value.trim()) {
                            const serialNumbersInput = document.createElement('input');
                            serialNumbersInput.type = 'hidden';
                            serialNumbersInput.name = `items[${itemIndex}][serial_numbers]`;
                            const serialsArray = backupSerialInput.value.split(',').map(s => s.trim()).filter(s => s.length > 0);
                            serialNumbersInput.value = JSON.stringify(serialsArray);
                            this.appendChild(serialNumbersInput);
                        }

                        itemIndex++;
                    });
                    
                    // Debug: Log all form data
                    const formData = new FormData(this);
                    for (let [key, value] of formData.entries()) {
                        console.log(key, ':', value);
                    }
                });
            } else {
                console.error('Dispatch form not found!');
            }
        });
    </script>
</body>

</html>
