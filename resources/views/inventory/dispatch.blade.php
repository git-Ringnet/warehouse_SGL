<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tạo phiếu xuất kho - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supplier-dropdown.css') }}">
    <script src="{{ asset('js/date-format.js') }}"></script>
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <a href="{{ asset('inventory') }}" class="text-gray-600 hover:text-blue-500 mr-4">
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
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày xuất</label>
                            <input type="text" id="dispatch_date" name="dispatch_date" value="{{ date('d/m/Y') }}"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 date-input">
                        </div>
                        <div>
                            <label for="dispatch_type"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Loại hình</label>
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
                                class="block text-sm font-medium text-gray-700 mb-1 required">Chi tiết xuất kho</label>
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
                        <!-- Phần chọn dự án (hiển thị khi loại hình = project) -->
                        <div id="project_section">
                            <label for="project_receiver"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Dự án</label>
                            <div class="relative">
                                <input type="text" id="project_receiver_search" autocomplete="off"
                                       placeholder="Tìm kiếm dự án..." 
                                       class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <div id="project_receiver_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                @if (isset($projects))
                                    @foreach ($projects as $project)
                                            <div class="project-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                                                 data-value="{{ $project->project_code }} - {{ $project->project_name }} ({{ $project->customer->name ?? 'N/A' }})"
                                            data-project-id="{{ $project->id }}"
                                                 data-warranty-period="{{ $project->warranty_period }}"
                                                 data-text="{{ $project->project_code }} - {{ $project->project_name }} ({{ $project->customer->name ?? 'N/A' }})">
                                            {{ $project->project_code }} - {{ $project->project_name }}
                                            ({{ $project->customer->name ?? 'N/A' }})
                                            </div>
                                    @endforeach
                                @endif
                                </div>
                                <input type="hidden" id="project_receiver" name="project_receiver" required>
                            <input type="hidden" id="project_id" name="project_id">
                            </div>
                        </div>

                        <!-- Phần cho thuê (hiển thị khi loại hình = rental) -->
                        <div id="rental_section" class="hidden">
                            <label for="rental_receiver"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Hợp đồng cho thuê</label>
                            <div class="relative">
                                <input type="text" id="rental_receiver_search" autocomplete="off"
                                       placeholder="Tìm kiếm hợp đồng cho thuê..." 
                                       class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <div id="rental_receiver_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    <!-- Options sẽ được populate bằng JavaScript -->
                                </div>
                                <input type="hidden" id="rental_receiver" name="rental_receiver" required>
                            </div>
                        </div>

                        <!-- Phần bảo hành (hiển thị khi loại hình = warranty) -->
                        <div id="warranty_section" class="hidden">
                            <label for="warranty_receiver"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Dự án / Hợp đồng cho thuê</label>
                            <div class="relative">
                                <input type="text" id="warranty_receiver_search" autocomplete="off"
                                       placeholder="Tìm kiếm dự án / hợp đồng cho thuê..." 
                                       class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <div id="warranty_receiver_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    <!-- Options sẽ được populate bằng JavaScript -->
                                </div>
                                <input type="hidden" id="warranty_receiver" name="warranty_receiver" required>
                            </div>
                        </div>

                        <div class="hidden">
                            <label for="warranty_period" class="block text-sm font-medium text-gray-700 mb-1">Thời gian
                                bảo hành</label>
                            <input type="text" id="warranty_period" name="warranty_period" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Chọn dự án để hiển thị thời gian bảo hành">
                        </div>
                        <div>
                            <label for="company_representative"
                                class="block text-sm font-medium text-gray-700 mb-1">Người đại diện công ty</label>
                            <div class="relative">
                                <input type="text" id="company_representative_search" autocomplete="off"
                                       placeholder="Tìm kiếm người đại diện..." 
                                       class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <div id="company_representative_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                @if (isset($employees))
                                    @foreach ($employees as $employee)
                                            <div class="employee-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                                                 data-value="{{ $employee->id }}" 
                                                 data-text="{{ $employee->name }}">
                                                {{ $employee->name }}
                                            </div>
                                    @endforeach
                                @endif
                                </div>
                                <input type="hidden" id="company_representative" name="company_representative_id" required>
                            </div>
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

                <!-- Danh sách thiết bị theo hợp đồng (hiển thị khi chọn "Tất cả") -->
                <div id="contract-product-list"
                    class="bg-white rounded-xl shadow-md p-6 border border-blue-200 mb-6 hidden">
                    <h2 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                        <i class="fas fa-file-contract text-blue-500 mr-2"></i>
                        <span>Danh sách thiết bị theo hợp đồng</span>
                    </h2>

                    <!-- Chọn thành phẩm hợp đồng -->
                    <div class="mb-4">
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <div class="relative">
                                    <input type="text" id="contract_product_search" autocomplete="off"
                                           placeholder="Tìm kiếm thành phẩm theo hợp đồng..." 
                                           class="w-full h-10 border border-blue-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50">
                                    <div id="contract_product_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-blue-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        <!-- Options sẽ được populate bằng JavaScript -->
                                    </div>
                                    <input type="hidden" id="contract_product_select" required>
                                </div>
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
                                        Mã</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Tên</th>
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
                    {{-- <div class="mt-4 flex justify-end">
                        <button type="button" id="update_contract_device_codes_btn"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i> Cập nhật mã thiết bị
                        </button>
                    </div> --}}
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
                                <div class="relative">
                                    <input type="text" id="backup_product_search" autocomplete="off"
                                           placeholder="Tìm kiếm thiết bị dự phòng..." 
                                           class="w-full h-10 border border-orange-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 bg-orange-50">
                                    <div id="backup_product_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-orange-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        <!-- Options sẽ được populate bằng JavaScript -->
                                    </div>
                                    <input type="hidden" id="backup_product_select" required>
                                </div>
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
                                        Mã</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                        Tên</th>
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
                    {{-- <div class="mt-4 flex justify-end">
                        <button type="button" id="update_backup_device_codes_btn"
                            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i> Cập nhật mã thiết bị
                        </button>
                    </div> --}}
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
    <div id="device-code-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Cập nhật mã thiết bị</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" id="close-device-code-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Nhập thông tin mã thiết bị cho sản phẩm. Điền thông tin vào bảng bên dưới:</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri chính
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri vật tư
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri sim
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mã truy cập
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    ID IoT
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mac 4G
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
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
                    <div class="flex space-x-2">
                        <a href="{{ route('device-codes.template') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-download mr-2"></i> Tải mẫu Excel
                        </a>
                        <button type="button" id="import-device-codes" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-file-import mr-2"></i> Import Excel
                        </button>
                    </div>
                    <div>
                        <button type="button" id="cancel-device-codes" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors mr-2">
                            Hủy
                        </button>
                        <button type="button" id="save-device-codes" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
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
            const projectSection = document.getElementById('project_section');
            const rentalSection = document.getElementById('rental_section');
            const warrantySection = document.getElementById('warranty_section');
            const projectReceiverInput = document.getElementById('project_receiver');
            const rentalReceiverInput = document.getElementById('rental_receiver');
            const warrantyReceiverInput = document.getElementById('warranty_receiver');
            const projectIdInput = document.getElementById('project_id');
            const warrantyPeriodInput = document.getElementById('warranty_period');

            let selectedContractProducts = [];
            let selectedBackupProducts = [];
            let availableItems = []; // Lưu danh sách sản phẩm từ kho đã chọn
            let isLoadingSerials = false; // Flag để tránh gọi loadAvailableSerials nhiều lần

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
                            `w-32 border ${borderColor} rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 serial-select`;

                        // Set data attributes for loading serials
                        select.setAttribute('data-item-type', product?.type || 'product');
                        select.setAttribute('data-item-id', productId);
                        select.setAttribute('data-warehouse-id', product?.selected_warehouse_id || '');
                        select.setAttribute('data-serial-index', i);
                        select.setAttribute('data-product-index', index);
                        select.setAttribute('data-category', category);
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
                const serialSelects = document.querySelectorAll(
                    `select[data-category="${category}"][data-product-index="${productIndex}"]`);
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
                // Tránh gọi nhiều lần cùng lúc
                if (isLoadingSerials) {
                    console.log('Already loading serials, skipping...');
                    return;
                }
                
                isLoadingSerials = true;
                const serialSelects = document.querySelectorAll('select[name*="serials"]');
                console.log(`Loading serials for ${serialSelects.length} select elements`);

                for (const select of serialSelects) {
                    if (select.disabled) continue; // Skip disabled selects

                    const itemType = select.dataset.itemType;
                    const itemId = select.dataset.itemId;
                    const warehouseId = select.dataset.warehouseId;
                    const selectedSerial = select.dataset.selectedSerial;

                    console.log(
                        `Checking select: itemType=${itemType}, itemId=${itemId}, warehouseId=${warehouseId}`
                        );

                    if (!itemType || !itemId || !warehouseId) {
                        console.log(
                            `Skipping select due to missing data: itemType=${itemType}, itemId=${itemId}, warehouseId=${warehouseId}`
                            );
                        continue;
                    }

                    try {
                        console.log(`Fetching serials for ${itemType} ${itemId} in warehouse ${warehouseId}`);
                        fetch(
                                `/api/dispatch/item-serials?item_type=${itemType}&item_id=${itemId}&warehouse_id=${warehouseId}`)
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

                                    // Remove duplicates from availableSerials
                                    const uniqueSerials = [...new Set(availableSerials)];
                                    console.log(`Original serials: ${availableSerials.length}, Unique serials: ${uniqueSerials.length}`);

                                    // Add available serials (only unique ones)
                                    uniqueSerials.forEach(serial => {
                                        const option = document.createElement('option');
                                        option.value = serial;
                                        option.textContent = serial;
                                        select.appendChild(option);
                                    });

                                    // Show "No serials available" if empty
                                    if (uniqueSerials.length === 0) {
                                        const option = document.createElement('option');
                                        option.value = '';
                                        option.textContent = 'Không có serial khả dụng';
                                        option.disabled = true;
                                        select.appendChild(option);
                                    }

                                    // Log serial availability info for debugging
                                    if (data.total_serials !== undefined && data.used_serials !==
                                        undefined) {
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
                
                // Reset flag khi hoàn thành
                isLoadingSerials = false;
            }

            // Xử lý thêm sản phẩm hợp đồng
            const addContractProductBtn = document.getElementById('add_contract_product_btn');
            const contractProductSelect = document.getElementById('contract_product_select');

            if (addContractProductBtn) {
                addContractProductBtn.addEventListener('click', function() {
                    const selectedValue = contractProductSelect.value;
                    const selectedType = contractProductSelect.dataset.type;
                    const selectedId = contractProductSelect.dataset.id;

                    if (!selectedValue) {
                        alert('Vui lòng chọn thành phẩm hợp đồng để thêm!');
                        return;
                    }

                    // Parse the combined value or use separate values
                    let productId, productType;
                    if (selectedValue.includes('_')) {
                        [productId, productType] = selectedValue.split('_');
                    } else {
                        productId = selectedId || selectedValue;
                        productType = selectedType;
                    }

                    addContractProduct(parseInt(productId), productType);
                    contractProductSelect.value = '';
                    contractProductSelect.dataset.type = '';
                    contractProductSelect.dataset.id = '';
                });
            }

            // Xử lý thêm thiết bị dự phòng
            const addBackupProductBtn = document.getElementById('add_backup_product_btn');
            const backupProductSelect = document.getElementById('backup_product_select');

            if (addBackupProductBtn) {
                addBackupProductBtn.addEventListener('click', function() {
                    const selectedValue = backupProductSelect.value;
                    const selectedType = backupProductSelect.dataset.type;
                    const selectedId = backupProductSelect.dataset.id;

                    if (!selectedValue) {
                        alert('Vui lòng chọn thiết bị dự phòng để thêm!');
                        return;
                    }

                    // Parse the combined value or use separate values
                    let productId, productType;
                    if (selectedValue.includes('_')) {
                        [productId, productType] = selectedValue.split('_');
                    } else {
                        productId = selectedId || selectedValue;
                        productType = selectedType;
                    }

                    addBackupProduct(parseInt(productId), productType);
                    backupProductSelect.value = '';
                    backupProductSelect.dataset.type = '';
                    backupProductSelect.dataset.id = '';
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
                        
                        // Debug: Log available items để kiểm tra dữ liệu
                        console.log('Available items loaded:', availableItems);
                        console.log('Sample item with warehouses:', availableItems.find(item => item.warehouses && item.warehouses.length > 0));
                        
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
                        const rentalDropdown = document.getElementById('rental_receiver_dropdown');
                        if (rentalDropdown) {
                            // Clear existing options
                            rentalDropdown.innerHTML = '';

                            // Add rental options
                            data.rentals.forEach(rental => {
                                const option = document.createElement('div');
                                option.className = 'rental-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                                option.dataset.value = rental.display_name;
                                option.dataset.text = rental.display_name;
                                option.dataset.rentalId = rental.id;
                                option.dataset.rentalCode = rental.rental_code;
                                option.dataset.customerName = rental.customer_name;
                                option.textContent = rental.display_name;
                                rentalDropdown.appendChild(option);
                            });
                        }
                    } else {
                        console.error('Error loading rentals:', data.message);
                    }
                } catch (error) {
                    console.error('Error loading rentals:', error);
                }
            }

            // Cập nhật tất cả dropdown sản phẩm (hỗ trợ UI mới dùng search)
            function updateProductSelects() {
                // Cập nhật dropdown hợp đồng (hidden select cũ nếu còn) và dropdown mới
                const contractProductSelect = document.getElementById('contract_product_select');
                if (contractProductSelect) {
                    contractProductSelect.innerHTML = '<option value="">-- Chọn thiết bị theo hợp đồng --</option>';
                }
                const contractDropdown = document.getElementById('contract_product_dropdown');
                if (contractDropdown) {
                    contractDropdown.innerHTML = '';
                }

                // Cập nhật dropdown dự phòng (hidden select cũ nếu còn) và dropdown mới
                const backupProductSelect = document.getElementById('backup_product_select');
                if (backupProductSelect) {
                    backupProductSelect.innerHTML = '<option value="">-- Chọn thiết bị dự phòng --</option>';
                }
                const backupDropdown = document.getElementById('backup_product_dropdown');
                if (backupDropdown) {
                    backupDropdown.innerHTML = '';
                }

                // Thêm options từ availableItems
                availableItems.forEach(item => {
                    // Options cho select cũ (nếu còn).
                    if (contractProductSelect) {
                        const opt = document.createElement('option');
                        opt.value = `${item.id}_${item.type}`; // Include type in value
                        opt.textContent = item.display_name;
                        opt.dataset.type = item.type;
                        opt.dataset.id = item.id;
                        contractProductSelect.appendChild(opt);
                    }
                    if (backupProductSelect) {
                        const opt = document.createElement('option');
                        opt.value = `${item.id}_${item.type}`; // Include type in value
                        opt.textContent = item.display_name;
                        opt.dataset.type = item.type;
                        opt.dataset.id = item.id;
                        backupProductSelect.appendChild(opt);
                    }

                    // Options cho dropdown mới
                    if (contractDropdown) {
                        const div = document.createElement('div');
                        div.className = 'product-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                        div.dataset.value = `${item.id}_${item.type}`; // Include type in value
                        div.dataset.text = item.display_name;
                        div.dataset.type = item.type;
                        div.dataset.id = item.id;
                        div.textContent = item.display_name;
                        contractDropdown.appendChild(div);
                    }
                    if (backupDropdown) {
                        const div = document.createElement('div');
                        div.className = 'product-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                        div.dataset.value = `${item.id}_${item.type}`; // Include type in value
                        div.dataset.text = item.display_name;
                        div.dataset.type = item.type;
                        div.dataset.id = item.id;
                        div.textContent = item.display_name;
                        backupDropdown.appendChild(div);
                    }
                });
            }

            // Clear tất cả dropdown sản phẩm
            function clearProductSelects() {
                const contractProductSelect = document.getElementById('contract_product_select');
                if (contractProductSelect) {
                    contractProductSelect.innerHTML = '<option value="">-- Chọn thiết bị theo hợp đồng --</option>';
                }
                const backupProductSelect = document.getElementById('backup_product_select');
                if (backupProductSelect) {
                    backupProductSelect.innerHTML = '<option value="">-- Chọn thiết bị dự phòng --</option>';
                }
                const contractDropdown = document.getElementById('contract_product_dropdown');
                if (contractDropdown) contractDropdown.innerHTML = '';
                const backupDropdown = document.getElementById('backup_product_dropdown');
                if (backupDropdown) backupDropdown.innerHTML = '';
            }

            // Hàm thêm sản phẩm hợp đồng
            function addContractProduct(productId, productType = null) {
                // Determine type from availableItems by id and type (if provided)
                let foundProduct;
                if (productType) {
                    foundProduct = availableItems.find(p => p.id == productId && p.type === productType);
                } else {
                    foundProduct = availableItems.find(p => p.id == productId);
                }

                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                // Kiểm tra đã thêm chưa (so sánh cả id và type)
                const existingProduct = selectedContractProducts.find(p => p.id === foundProduct.id && p.type ===
                    foundProduct.type);
                if (existingProduct) {
                    alert('Sản phẩm này đã được thêm vào danh sách hợp đồng!');
                    return;
                }

                selectedContractProducts.push({
                    ...foundProduct,
                    quantity: 1,
                    selected_warehouse_id: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[0]
                        .warehouse_id : null,
                    current_stock: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[0]
                        .quantity : 0
                });

                renderContractProductTable();
                showStockWarnings();
            }

            // Hàm thêm thiết bị dự phòng
            function addBackupProduct(productId, productType = null) {
                // Determine type from availableItems by id and type (if provided)
                let foundProduct;
                if (productType) {
                    foundProduct = availableItems.find(p => p.id == productId && p.type === productType);
                } else {
                    foundProduct = availableItems.find(p => p.id == productId);
                }

                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                // Kiểm tra đã thêm chưa (so sánh cả id và type)
                const existingProduct = selectedBackupProducts.find(p => p.id === foundProduct.id && p.type ===
                    foundProduct.type);
                if (existingProduct) {
                    alert('Thiết bị này đã được thêm vào danh sách dự phòng!');
                    return;
                }

                selectedBackupProducts.push({
                    ...foundProduct,
                    quantity: 1,
                    selected_warehouse_id: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[0]
                        .warehouse_id : null,
                    current_stock: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[0]
                        .quantity : 0
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

                    // Reset all sections
                    projectSection.classList.add('hidden');
                    rentalSection.classList.add('hidden');
                    warrantySection.classList.add('hidden');
                    projectReceiverInput.removeAttribute('required');
                    rentalReceiverInput.removeAttribute('required');
                    warrantyReceiverInput.removeAttribute('required');

                    if (selectedType === 'rental') {
                        // Hiển thị phần cho thuê, ẩn phần dự án và bảo hành
                        rentalSection.classList.remove('hidden');
                        rentalReceiverInput.setAttribute('required', 'required');

                        // Load danh sách hợp đồng cho thuê
                        loadRentals();

                        // Set project_receiver từ hidden/search input; project_id đã được set khi click option
                        const rentalSearchInput = document.getElementById('rental_receiver_search');
                        const syncRentalToProject = function() {
                            projectReceiverInput.value = rentalReceiverInput.value || (rentalSearchInput ? rentalSearchInput.value : '');
                        };

                        // Xóa event listener cũ nếu có để tránh duplicate
                        if (rentalSearchInput) {
                            rentalSearchInput.removeEventListener('input', syncRentalToProject);
                            rentalSearchInput.removeEventListener('change', syncRentalToProject);
                        }

                        // Thêm event listeners mới
                        if (rentalSearchInput) {
                            rentalSearchInput.addEventListener('input', syncRentalToProject);
                            rentalSearchInput.addEventListener('change', syncRentalToProject);
                        }

                        // Đồng bộ giá trị hiện tại
                        syncRentalToProject();

                        // Không tự động chọn, để người dùng tự chọn
                        if (dispatchDetailSelect) {
                            dispatchDetailSelect.disabled = false;
                            dispatchDetailSelect.value = ''; // Reset về chưa chọn
                        }

                        // Xóa hidden input nếu có
                        const hiddenDispatchDetail = document.getElementById('hidden_dispatch_detail');
                        if (hiddenDispatchDetail) {
                            hiddenDispatchDetail.remove();
                        }
                    } else if (selectedType === 'project') {
                        // Hiển thị phần dự án, ẩn phần cho thuê và bảo hành
                        projectSection.classList.remove('hidden');
                        projectReceiverInput.setAttribute('required', 'required');

                        // Không tự động chọn, để người dùng tự chọn
                        if (dispatchDetailSelect) {
                            dispatchDetailSelect.disabled = false;
                            dispatchDetailSelect.value = ''; // Reset về chưa chọn
                        }

                        // Xóa hidden input nếu có
                        const hiddenDispatchDetail = document.getElementById('hidden_dispatch_detail');
                        if (hiddenDispatchDetail) {
                            hiddenDispatchDetail.remove();
                        }
                    } else if (selectedType === 'warranty') {
                        // Hiển thị phần bảo hành, ẩn phần dự án và cho thuê
                        warrantySection.classList.remove('hidden');
                        warrantyReceiverInput.setAttribute('required', 'required');

                        // Load danh sách dự án và hợp đồng cho thuê cho phần bảo hành
                        loadWarrantyReceivers();

                        // Tự động chọn "backup" và disable dropdown cho warranty
                        if (dispatchDetailSelect) {
                            dispatchDetailSelect.value = 'backup';
                            dispatchDetailSelect.disabled = true;

                            // Tạo hidden input để đảm bảo giá trị được gửi đi
                            let hiddenDispatchDetail = document.getElementById('hidden_dispatch_detail');
                            if (!hiddenDispatchDetail) {
                                hiddenDispatchDetail = document.createElement('input');
                                hiddenDispatchDetail.type = 'hidden';
                                hiddenDispatchDetail.id = 'hidden_dispatch_detail';
                                hiddenDispatchDetail.name = 'dispatch_detail';
                                document.getElementById('dispatch-form').appendChild(hiddenDispatchDetail);
                            }
                            hiddenDispatchDetail.value = 'backup';

                            // Trigger change event để hiển thị backup product list
                            dispatchDetailSelect.dispatchEvent(new Event('change'));
                        }
                    }
                });
            }

            // Xử lý thay đổi dự án - cập nhật thời gian bảo hành và project_id
            if (projectReceiverInput) {
                projectReceiverInput.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    
                    if (selectedOption && selectedOption.dataset.projectId) {
                        projectIdInput.value = selectedOption.dataset.projectId;
                        if (selectedOption.dataset.warrantyPeriod) {
                            warrantyPeriodInput.value = selectedOption.dataset.warrantyPeriod + ' tháng';
                        }
                    } else {
                        projectIdInput.value = '';
                        warrantyPeriodInput.value = '';
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
                    const productItem = availableItems.find(item => item.id === product.id && item.type === product.type);
                    console.log(`Contract - Looking for product: id=${product.id}, type=${product.type}`);
                    console.log('Contract - Found productItem:', productItem);
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
                    const productItem = availableItems.find(item => item.id === product.id && item.type === product.type);
                    console.log(`Backup - Looking for product: id=${product.id}, type=${product.type}`);
                    console.log('Backup - Found productItem:', productItem);
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
                    const serialContainer = document.getElementById(`${prefixName}-serials-${index}`);
                    if (serialContainer) {
                        const serialSelects = serialContainer.querySelectorAll('select');
                        const mainSerials = Array.from(serialSelects)
                            .map(select => select.value)
                            .filter(Boolean);

                        if (mainSerials.length > 0) {
                            const row = document.createElement('tr');
                            // Add product_id as data attribute
                            row.setAttribute('data-product-id', product.id);
                            row.innerHTML = `
                                <td class="px-2 py-2 border border-gray-200">
                                    <div class="flex flex-col space-y-1">
                                        ${mainSerials.map(serial => `
                                            <input type="text" 
                                                name="${prefixName}_serial_main[${product.id}][]" 
                                                value="${serial}"
                                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm mb-1"
                                                readonly>
                                        `).join('')}
                                    </div>
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <div class="flex flex-col space-y-1" id="${prefixName}-component-serials-${index}">
                                        <div class="text-sm text-gray-500">Đang tải serial vật tư...</div>
                                    </div>
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${prefixName}_serial_sim[${product.id}]" 
                                        placeholder="Nhập seri SIM..."
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${prefixName}_access_code[${product.id}]" 
                                        placeholder="Nhập mã truy cập..."
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${prefixName}_iot_id[${product.id}]" 
                                        placeholder="Nhập ID IoT..."
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${prefixName}_mac_4g[${product.id}]" 
                                        placeholder="Nhập MAC 4G..."
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${prefixName}_note[${product.id}]" 
                                        placeholder="Nhập chú thích..."
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                            `;
                            tbody.appendChild(row);

                            // Fetch component serials for all main serials
                            const componentSerialsContainer = document.getElementById(`${prefixName}-component-serials-${index}`);
                            componentSerialsContainer.innerHTML = ''; // Clear loading message

                            // Create an array to store all promises
                            const fetchPromises = mainSerials.map(mainSerial =>
                                fetch(`/api/device-info/${mainSerial}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success && data.data.componentSerials) {
                                            // Split component serials by comma and create input for each
                                            data.data.componentSerials.forEach(serialStr => {
                                                // Split by comma and trim
                                                const serials = serialStr.split(',').map(s => s.trim()).filter(Boolean);
                                                serials.forEach(serial => {
                                                    const input = document.createElement('input');
                                                    input.type = 'text';
                                                    input.name = `${prefixName}_serial_components[${product.id}][]`;
                                                    input.value = serial;
                                                    input.readOnly = true;
                                                    input.className = 'w-full border border-gray-300 rounded px-2 py-1 text-sm mb-1';
                                                    componentSerialsContainer.appendChild(input);
                                                });
                                            });
                                        }
                                    })
                                    .catch(error => console.error('Error:', error))
                            );

                            // Wait for all fetches to complete
                            Promise.all(fetchPromises).catch(error => {
                                console.error('Error fetching component serials:', error);
                                componentSerialsContainer.innerHTML = '<div class="text-sm text-red-500">Lỗi khi tải serial vật tư</div>';
                            });
                        }
                    }
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

            // Hàm lấy thông tin thiết bị từ serial
            async function fetchDeviceInfo(serial, prefixName, index, productId) {
                try {
                    const response = await fetch(`/api/device-info/${serial}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        // Populate device info fields
                        const row = document.querySelector(`tr:nth-child(${index + 1})`);
                        if (row) {
                            const inputs = row.querySelectorAll('input');
                            
                            // Fill component serials if they exist
                            if (data.component_serials && data.component_serials.length > 0) {
                                const componentContainer = document.getElementById(`${prefixName}-component-serials-${index}`);
                                if (componentContainer) {
                                    // Remove existing inputs except the "Add" button
                                    const addButton = componentContainer.querySelector('.add-component-serial');
                                    componentContainer.innerHTML = '';
                                    
                                    // Add new inputs for each component serial
                                    data.component_serials.forEach((serial, idx) => {
                                        const input = document.createElement('input');
                                        input.type = 'text';
                                        input.name = `${prefixName}_serial_components[${productId}][${idx}]`;
                                        input.value = serial;
                                        input.placeholder = `Nhập seri vật tư ${idx + 1}...`;
                                        input.className = 'w-full border border-gray-300 rounded px-2 py-1 text-sm';
                                        componentContainer.appendChild(input);
                                    });
                                    
                                    // Re-add the "Add" button
                                    componentContainer.appendChild(addButton);
                                }
                            }
                            
                            // Fill other fields
                            if (data.sim_serial) inputs[2].value = data.sim_serial;
                            if (data.access_code) inputs[3].value = data.access_code;
                            if (data.iot_id) inputs[4].value = data.iot_id;
                            if (data.mac_4g) inputs[5].value = data.mac_4g;
                            if (data.note) inputs[6].value = data.note;
                        }
                    }
                } catch (error) {
                    console.error('Error fetching device info:', error);
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
                saveDeviceCodesBtn.addEventListener('click', async function() {
                    try {
                        const tbody = document.getElementById('device-code-tbody');
                        if (!tbody) {
                            throw new Error('Could not find device code table body');
                        }

                        const rows = tbody.querySelectorAll('tr');
                        const deviceCodes = [];

                        rows.forEach((row) => {
                            // Skip empty message row
                            if (row.querySelector('td[colspan]')) {
                                return;
                            }

                            // Get product ID from the data attribute
                            const productId = row.getAttribute('data-product-id');
                            if (!productId) {
                                console.warn('Row missing product ID, skipping...');
                                return;
                            }

                            // Get all main serials
                            const mainSerialInputs = row.querySelectorAll('input[name*="serial_main"]');
                            const mainSerials = Array.from(mainSerialInputs)
                                .map(input => input.value)
                                .filter(Boolean);

                            if (mainSerials.length === 0) {
                                console.warn(`No main serials found for product ${productId}, skipping...`);
                                return;
                            }

                            // Get all component serials
                            const componentSerialInputs = row.querySelectorAll('input[name*="serial_components"]');
                            const componentSerials = Array.from(componentSerialInputs)
                                .map(input => input.value)
                                .filter(Boolean);

                            // Get other fields
                            const simSerial = row.querySelector('input[name*="serial_sim"]')?.value || '';
                            const accessCode = row.querySelector('input[name*="access_code"]')?.value || '';
                            const iotId = row.querySelector('input[name*="iot_id"]')?.value || '';
                            const mac4g = row.querySelector('input[name*="mac_4g"]')?.value || '';
                            const note = row.querySelector('input[name*="note"]')?.value || '';

                            // Create a device code entry for each main serial
                            mainSerials.forEach(mainSerial => {
                                deviceCodes.push({
                                    product_id: productId,
                                    serial_main: mainSerial,
                                    serial_components: componentSerials,
                                    serial_sim: simSerial,
                                    access_code: accessCode,
                                    iot_id: iotId,
                                    mac_4g: mac4g,
                                    note: note
                                });
                            });
                        });

                        if (deviceCodes.length === 0) {
                            throw new Error('Không có thông tin mã thiết bị nào để lưu!');
                        }

                        // Show loading state
                        saveDeviceCodesBtn.disabled = true;
                        saveDeviceCodesBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang lưu...';

                        // Save all device codes
                        const promises = deviceCodes.map(deviceCode => 
                            fetch('/device-codes', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify(deviceCode)
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                        );

                        const results = await Promise.all(promises);
                        const hasErrors = results.some(result => !result.success);

                        if (hasErrors) {
                            throw new Error('Có lỗi xảy ra khi lưu một số thông tin. Vui lòng kiểm tra lại.');
                        }

                        alert('Đã lưu thông tin mã thiết bị thành công!');
                        deviceCodeModal.classList.add('hidden');

                    } catch (error) {
                        console.error('Error saving device codes:', error);
                        alert('Có lỗi xảy ra khi lưu thông tin: ' + error.message);
                    } finally {
                        // Reset button state
                        if (saveDeviceCodesBtn) {
                            saveDeviceCodesBtn.disabled = false;
                            saveDeviceCodesBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Lưu thông tin';
                        }
                    }
                });
            }

            if (importDeviceCodesBtn) {
                importDeviceCodesBtn.addEventListener('click', function() {
                    const fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.accept = '.xlsx,.xls';
                    fileInput.style.display = 'none';
                    
                    fileInput.addEventListener('change', async function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            const formData = new FormData();
                            formData.append('file', file);
                            
                            try {
                                const response = await fetch('/device-codes/import', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                });
                                
                                const result = await response.json();
                                
                                if (result.success) {
                                    // Populate the form with imported data
                                    const tbody = document.getElementById('device-code-tbody');
                                    const rows = tbody.querySelectorAll('tr');
                                    
                                    result.data.forEach((item, index) => {
                                        if (rows[index]) {
                                            const inputs = rows[index].querySelectorAll('input');
                                            if (inputs[0]) inputs[0].value = item.main_serial;
                                            
                                            // Handle component serials
                                            const componentSerials = item.component_serials.split(',').map(s => s.trim());
                                            const componentContainer = rows[index].querySelector('.flex.flex-col');
                                            if (componentContainer) {
                                                // Clear existing component inputs except the "Add" button
                                                const addButton = componentContainer.querySelector('.add-component-serial');
                                                componentContainer.innerHTML = '';
                                                
                                                // Add new component inputs
                                                componentSerials.forEach((serial, idx) => {
                                                    if (serial) {
                                                        const input = document.createElement('input');
                                                        input.type = 'text';
                                                        input.name = `${currentDeviceCodeType}_serial_components[${index}][${idx}]`;
                                                        input.value = serial;
                                                        input.placeholder = `Nhập seri vật tư ${idx + 1}...`;
                                                        input.className = 'w-full border border-gray-300 rounded px-2 py-1 text-sm';
                                                        componentContainer.appendChild(input);
                                                    }
                                                });
                                                
                                                // Re-add the "Add" button
                                                componentContainer.appendChild(addButton);
                                            }
                                            
                                            if (inputs[2]) inputs[2].value = item.sim_serial;
                                            if (inputs[3]) inputs[3].value = item.access_code;
                                            if (inputs[4]) inputs[4].value = item.iot_id;
                                            if (inputs[5]) inputs[5].value = item.mac_4g;
                                            if (inputs[6]) inputs[6].value = item.note;
                                        }
                                    });
                                    
                                    alert('Import dữ liệu thành công!');
                                } else {
                                    alert('Lỗi: ' + result.message);
                                }
                            } catch (error) {
                                console.error('Error importing file:', error);
                                alert('Có lỗi xảy ra khi import file');
                            }
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

            // Hàm kiểm tra serial numbers có bị trùng lặp không (kiểm tra toàn bộ phiếu, không phân biệt category)
            function validateSerialNumbers() {
                const seen = new Set();
                const duplicates = new Set();
                document.querySelectorAll('select[name*="_serials"]').forEach(sel => {
                    const val = (sel.value || '').trim();
                    if (!val) return;
                    if (seen.has(val)) duplicates.add(val);
                    else seen.add(val);
                });
                return Array.from(duplicates);
            }

            // Hàm kiểm tra serial trùng lặp cho từng category
            function validateCategorySerialNumbers(category) {
                const categorySerialSelects = document.querySelectorAll(`select[data-category="${category}"]`);
                const selectedSerials = [];
                const duplicates = [];

                // Thu thập tất cả serial numbers đã chọn trong category này
                categorySerialSelects.forEach(select => {
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

                console.log(`${category} serials:`, selectedSerials);
                console.log(`${category} duplicates:`, duplicates);

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
            function validateSerialOnChange(event) {
                // Xác định category từ select được thay đổi
                const category = event.target.dataset.category;

                if (category) {
                    // Chỉ cập nhật lại options cho category này
                    updateCategorySerialOptions(category);
                } else {
                    // Fallback cho trường hợp không xác định được category
                    updateSerialOptionsAvailability();
                }

                // Vẫn kiểm tra tất cả các duplicates
                const duplicates = validateSerialNumbers();
                showSerialDuplicateWarning(duplicates);
            }

            // Hàm cập nhật available options cho serial selects
            function updateSerialOptionsAvailability() {
                // Xử lý riêng biệt cho từng category để tránh xung đột
                updateCategorySerialOptions('contract');
                updateCategorySerialOptions('backup');
            }

            // Hàm xử lý availability riêng cho từng category
            function updateCategorySerialOptions(category) {
                const categorySerialSelects = document.querySelectorAll(`select[data-category="${category}"]`);
                const selectedValues = [];

                // Thu thập tất cả giá trị đã chọn trong category này
                categorySerialSelects.forEach(select => {
                    if (select.value && select.value.trim() !== '') {
                        selectedValues.push(select.value.trim());
                    }
                });

                console.log(`${category} selected serials:`, selectedValues);

                // Cập nhật từng select trong category
                categorySerialSelects.forEach(currentSelect => {
                    const currentValue = currentSelect.value;

                    Array.from(currentSelect.options).forEach(option => {
                        if (option.value === '') return; // Skip default option

                        if (selectedValues.includes(option.value) && option.value !==
                            currentValue) {
                            // Serial này đã được chọn ở select khác trong cùng category
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
                    const warrantyReceiver = document.getElementById('warranty_receiver').value;
                    const projectId = document.getElementById('project_id').value;

                    console.log('Dispatch type:', dispatchType);
                    console.log('Project receiver:', projectReceiver);
                    console.log('Rental receiver:', rentalReceiver);
                    console.log('Warranty receiver:', warrantyReceiver);
                    console.log('Project ID:', projectId);

                    // Kiểm tra các trường bắt buộc theo loại hình
                    let hasRequiredFields = true;
                    let requiredFieldMessage = '';

                    if (dispatchType === 'project') {
                        if (!projectReceiver?.trim()) {
                            hasRequiredFields = false;
                            requiredFieldMessage = 'Vui lòng chọn Dự án';
                        }
                    } else if (dispatchType === 'rental') {
                        if (!rentalReceiver?.trim()) {
                            hasRequiredFields = false;
                            requiredFieldMessage = 'Vui lòng chọn Hợp đồng cho thuê';
                        }
                    } else if (dispatchType === 'warranty') {
                        if (!projectId) {
                            hasRequiredFields = false;
                            requiredFieldMessage = 'Vui lòng chọn Dự án / Hợp đồng cho thuê';
                        }
                    }

                    if (!hasRequiredFields) {
                        e.preventDefault();
                        alert(requiredFieldMessage);
                        return;
                    }

                    // Kiểm tra project_id khi là loại hình bảo hành
                    if (dispatchType === 'warranty' && !projectId) {
                        e.preventDefault();
                        alert('Vui lòng chọn dự án hoặc hợp đồng cho thuê để bảo hành!');
                        return;
                    }

                    // Kiểm tra xem có sản phẩm nào được chọn không dựa trên dispatch_detail
                    const dispatchDetail = document.getElementById('dispatch_detail').value;
                    let hasRequiredProducts = false;
                    let errorMessage = '';

                    if (dispatchDetail === 'all') {
                        // Với "Tất cả", cần ít nhất một sản phẩm hợp đồng VÀ một thiết bị dự phòng
                        if (selectedContractProducts.length === 0 && selectedBackupProducts.length === 0) {
                            hasRequiredProducts = false;
                            errorMessage =
                                'Vui lòng chọn ít nhất một sản phẩm hợp đồng và một thiết bị dự phòng để xuất kho!';
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
                            errorMessage =
                            'Vui lòng chọn ít nhất một thành phẩm theo hợp đồng để xuất kho!';
                        } else {
                            hasRequiredProducts = true;
                        }
                    } else if (dispatchDetail === 'backup') {
                        // Với "Xuất thiết bị dự phòng", cần ít nhất một thiết bị dự phòng và KHÔNG được có hợp đồng
                        if (selectedBackupProducts.length === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Vui lòng chọn ít nhất một thiết bị dự phòng để xuất kho!';
                        } else {
                            hasRequiredProducts = true;
                        }
                    }

                    if (!hasRequiredProducts) {
                        e.preventDefault();
                        console.log(
                            'FORM SUBMIT PREVENTED: No required products selected for dispatch_detail:',
                            dispatchDetail);
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
                        // Sử dụng selector CHÍNH XÁC với index và product index để lấy CHÍNH XÁC serial của sản phẩm này
                        console.log(
                            `Searching for serials for contract product: id=${product.id}, index=${selectedContractProducts.indexOf(product)}`
                            );

                        // Tìm container chứa các serial selects cho sản phẩm này
                        const serialContainer = document.getElementById(
                            `contract-serials-${selectedContractProducts.indexOf(product)}`);

                        if (serialContainer) {
                            // Lấy chỉ các serial selects trong container này
                            const contractSerialSelects = serialContainer.querySelectorAll(
                            'select');
                            console.log(
                                `Found ${contractSerialSelects.length} serial selects for contract product ${product.id}`
                                );

                            if (contractSerialSelects.length > 0) {
                                const serialsArray = Array.from(contractSerialSelects)
                                    .map(select => select.value.trim())
                                    .filter(value => value.length > 0);

                                console.log(`Contract product ${product.id} serials:`,
                                serialsArray);

                                if (serialsArray.length > 0) {
                                    const serialNumbersInput = document.createElement('input');
                                    serialNumbersInput.type = 'hidden';
                                    serialNumbersInput.name = `items[${itemIndex}][serial_numbers]`;
                                    serialNumbersInput.value = JSON.stringify(serialsArray);
                                    this.appendChild(serialNumbersInput);
                                }
                            }
                        } else {
                            console.error(
                                `Serial container not found for contract product ${product.id}`);
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
                        // Sử dụng selector CHÍNH XÁC với index và product index để lấy CHÍNH XÁC serial của sản phẩm này
                        console.log(
                            `Searching for serials for backup product: id=${product.id}, index=${selectedBackupProducts.indexOf(product)}`
                            );

                        // Tìm container chứa các serial selects cho sản phẩm này
                        const serialContainer = document.getElementById(
                            `backup-serials-${selectedBackupProducts.indexOf(product)}`);

                        if (serialContainer) {
                            // Lấy chỉ các serial selects trong container này
                            const backupSerialSelects = serialContainer.querySelectorAll('select');
                            console.log(
                                `Found ${backupSerialSelects.length} serial selects for backup product ${product.id}`
                                );

                            if (backupSerialSelects.length > 0) {
                                const serialsArray = Array.from(backupSerialSelects)
                                    .map(select => select.value.trim())
                                    .filter(value => value.length > 0);

                                console.log(`Backup product ${product.id} serials:`, serialsArray);

                                if (serialsArray.length > 0) {
                                    const serialNumbersInput = document.createElement('input');
                                    serialNumbersInput.type = 'hidden';
                                    serialNumbersInput.name = `items[${itemIndex}][serial_numbers]`;
                                    serialNumbersInput.value = JSON.stringify(serialsArray);
                                    this.appendChild(serialNumbersInput);
                                }
                            }
                        } else {
                            console.error(
                                `Serial container not found for backup product ${product.id}`);
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

            // Khởi tạo modal cập nhật mã thiết bị
            function initDeviceCodeModal(type = 'main') {
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
                    const serialContainer = document.getElementById(`${prefixName}-serials-${index}`);
                    if (serialContainer) {
                        const serialSelects = serialContainer.querySelectorAll('select');
                        const mainSerials = Array.from(serialSelects)
                            .map(select => select.value)
                            .filter(Boolean);

                        if (mainSerials.length > 0) {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td class="px-2 py-2 border border-gray-200">
                                    <div class="flex flex-col space-y-1">
                                        ${mainSerials.map(serial => `
                                            <input type="text" 
                                                name="${prefixName}_serial_main[${product.id}][]" 
                                                value="${serial}"
                                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm mb-1"
                                                readonly>
                                        `).join('')}
                                    </div>
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <div class="flex flex-col space-y-1" id="${prefixName}-component-serials-${index}">
                                        <div class="text-sm text-gray-500">Đang tải serial vật tư...</div>
                                    </div>
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${prefixName}_serial_sim[${product.id}]" 
                                        placeholder="Nhập seri SIM..."
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${prefixName}_access_code[${product.id}]" 
                                        placeholder="Nhập mã truy cập..."
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${prefixName}_iot_id[${product.id}]" 
                                        placeholder="Nhập ID IoT..."
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${prefixName}_mac_4g[${product.id}]" 
                                        placeholder="Nhập MAC 4G..."
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${prefixName}_note[${product.id}]" 
                                        placeholder="Nhập chú thích..."
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                            `;
                            tbody.appendChild(row);

                            // Fetch component serials for all main serials
                            const componentSerialsContainer = document.getElementById(`${prefixName}-component-serials-${index}`);
                            componentSerialsContainer.innerHTML = ''; // Clear loading message

                            // Create an array to store all promises
                            const fetchPromises = mainSerials.map(mainSerial =>
                                fetch(`/api/device-info/${mainSerial}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success && data.data.componentSerials) {
                                            // Split component serials by comma and create input for each
                                            data.data.componentSerials.forEach(serialStr => {
                                                // Split by comma and trim
                                                const serials = serialStr.split(',').map(s => s.trim()).filter(Boolean);
                                                serials.forEach(serial => {
                                                    const input = document.createElement('input');
                                                    input.type = 'text';
                                                    input.name = `${prefixName}_serial_components[${product.id}][]`;
                                                    input.value = serial;
                                                    input.readOnly = true;
                                                    input.className = 'w-full border border-gray-300 rounded px-2 py-1 text-sm mb-1';
                                                    componentSerialsContainer.appendChild(input);
                                                });
                                            });
                                        }
                                    })
                                    .catch(error => console.error('Error:', error))
                            );

                            // Wait for all fetches to complete
                            Promise.all(fetchPromises).catch(error => {
                                console.error('Error fetching component serials:', error);
                                componentSerialsContainer.innerHTML = '<div class="text-sm text-red-500">Lỗi khi tải serial vật tư</div>';
                            });
                        }
                    }
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

            // Xử lý sự kiện khi click vào nút cập nhật mã thiết bị
            document.querySelectorAll('.update-device-code').forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.dataset.type || 'main';
                    initDeviceCodeModal(type);
                    document.getElementById('device-code-modal').classList.remove('hidden');
                });
            });

            // Hàm load danh sách dự án và hợp đồng cho thuê cho phần bảo hành
            async function loadWarrantyReceivers() {
                try {
                    // Load danh sách dự án
                    const projectResponse = await fetch('/api/dispatch/projects');
                    const projectData = await projectResponse.json();

                    // Load danh sách hợp đồng cho thuê
                    const rentalResponse = await fetch('/api/dispatch/rentals');
                    const rentalData = await rentalResponse.json();

                    const warrantyDropdown = document.getElementById('warranty_receiver_dropdown');
                    if (warrantyDropdown) {
                        // Clear existing options
                        warrantyDropdown.innerHTML = '';

                        // Add project options
                        if (projectData.success && projectData.projects) {
                            projectData.projects.forEach(project => {
                                const option = document.createElement('div');
                                option.className = 'warranty-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                                option.dataset.value = project.display_name;
                                option.dataset.text = project.display_name;
                                option.dataset.projectId = project.id;
                                option.dataset.type = 'project';
                                option.dataset.warrantyPeriod = project.warranty_period;
                                option.textContent = project.display_name;
                                warrantyDropdown.appendChild(option);
                            });
                        }

                        // Add rental options
                        if (rentalData.success && rentalData.rentals) {
                            rentalData.rentals.forEach(rental => {
                                const option = document.createElement('div');
                                option.className = 'warranty-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                                option.dataset.value = rental.display_name;
                                option.dataset.text = rental.display_name;
                                option.dataset.rentalId = rental.id;
                                option.dataset.type = 'rental';
                                option.textContent = rental.display_name;
                                warrantyDropdown.appendChild(option);
                            });
                        }


                    }
                } catch (error) {
                    console.error('Error loading warranty receivers:', error);
                }
            }

            // Load tất cả sản phẩm từ tất cả kho ngay từ đầu
            loadAllAvailableItems();
            
            // Khởi tạo search functionality cho các field mới
            initializeSearchFunctionality();
        });
        
        // Khởi tạo search functionality cho các field mới
        function initializeSearchFunctionality() {
            // Company Representative Search
            initializeCompanyRepresentativeSearch();
            
            // Project Receiver Search
            initializeProjectReceiverSearch();
            
            // Rental Receiver Search
            initializeRentalReceiverSearch();
            
            // Warranty Receiver Search
            initializeWarrantyReceiverSearch();
            
            // Contract Product Search
            initializeContractProductSearch();
            
            // Backup Product Search
            initializeBackupProductSearch();
        }
        
        // Project Receiver Search
        function initializeProjectReceiverSearch() {
            const searchInput = document.getElementById('project_receiver_search');
            const dropdown = document.getElementById('project_receiver_dropdown');
            const hiddenInput = document.getElementById('project_receiver');
            const projectIdInput = document.getElementById('project_id');
            
            if (!searchInput || !dropdown || !hiddenInput) return;
            
            // Show dropdown on focus
            searchInput.addEventListener('focus', function() {
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Filter projects based on search input
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = dropdown.querySelectorAll('.project-option');
                
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'block';
                        // Highlight search term
                        const highlightedText = option.dataset.text.replace(
                            new RegExp(searchTerm, 'gi'),
                            match => `<mark class="bg-yellow-200">${match}</mark>`
                        );
                        option.innerHTML = highlightedText;
                                } else {
                        option.style.display = 'none';
                    }
                });
                
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Handle project option selection
            dropdown.addEventListener('click', function(e) {
                if (e.target.classList.contains('project-option')) {
                    const option = e.target;
                    const selectedValue = option.dataset.value;
                    const selectedText = option.dataset.text;
                    const projectId = option.dataset.projectId;
                    const warrantyPeriod = option.dataset.warrantyPeriod;
                    
                    searchInput.value = selectedText;
                    hiddenInput.value = selectedValue;
                    projectIdInput.value = projectId;
                    dropdown.classList.add('hidden');
                    
                    // Remove highlighting
                    option.innerHTML = option.dataset.text;
                    
                    // Update warranty period if exists
                    const warrantyPeriodInput = document.getElementById('warranty_period');
                    if (warrantyPeriodInput && warrantyPeriod) {
                        warrantyPeriodInput.value = warrantyPeriod + ' tháng';
                    }
                    
                    // Set project_receiver for compatibility
                    const projectReceiverInput = document.getElementById('project_receiver');
                    if (projectReceiverInput) {
                        try {
                            const fullText = selectedText.trim();
                            // Extract name from format: "CODE - NAME (CUSTOMER)"
                            const matches = fullText.match(/^[^-]+ - ([^(]+)/);
                            if (matches && matches[1]) {
                                projectReceiverInput.value = matches[1].trim();
                            } else {
                                // Fallback: use full text if pattern doesn't match
                                projectReceiverInput.value = fullText;
                            }
                        } catch (error) {
                            console.error('Error processing project receiver:', error);
                            // Fallback: use raw value
                            projectReceiverInput.value = selectedValue;
                        }
                    }
                }
            });
            
            // Keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                const options = Array.from(dropdown.querySelectorAll('.project-option:not([style*="display: none"])'));
                const currentIndex = options.findIndex(option => option.classList.contains('highlight'));
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (currentIndex < options.length - 1) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex + 1].classList.add('highlight');
                        }
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        if (currentIndex > 0) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex - 1].classList.add('highlight');
                        }
                        break;
                    case 'Enter':
                        e.preventDefault();
                        const highlightedOption = dropdown.querySelector('.project-option.highlight');
                        if (highlightedOption) {
                            highlightedOption.click();
                        }
                        break;
                    case 'Escape':
                        dropdown.classList.add('hidden');
                        break;
                }
            });
        }
        
        // Company Representative Search
        function initializeCompanyRepresentativeSearch() {
            const searchInput = document.getElementById('company_representative_search');
            const dropdown = document.getElementById('company_representative_dropdown');
            const hiddenInput = document.getElementById('company_representative');
            
            if (!searchInput || !dropdown || !hiddenInput) return;
            
            // Show dropdown on focus
            searchInput.addEventListener('focus', function() {
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Filter employees based on search input
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = dropdown.querySelectorAll('.employee-option');
                
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'block';
                        // Highlight search term
                        const highlightedText = option.dataset.text.replace(
                            new RegExp(searchTerm, 'gi'),
                            match => `<mark class="bg-yellow-200">${match}</mark>`
                        );
                        option.innerHTML = highlightedText;
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Handle employee option selection
            dropdown.addEventListener('click', function(e) {
                if (e.target.classList.contains('employee-option')) {
                    const option = e.target;
                    const selectedId = option.dataset.value;
                    const selectedText = option.dataset.text;
                    
                    searchInput.value = selectedText;
                    hiddenInput.value = selectedId;
                    dropdown.classList.add('hidden');
                    
                    // Remove highlighting
                    option.innerHTML = option.dataset.text;
                }
            });
            
            // Keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                const options = Array.from(dropdown.querySelectorAll('.employee-option:not([style*="display: none"])'));
                const currentIndex = options.findIndex(option => option.classList.contains('highlight'));
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (currentIndex < options.length - 1) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex + 1].classList.add('highlight');
                        }
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        if (currentIndex > 0) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex - 1].classList.add('highlight');
                        }
                        break;
                    case 'Enter':
                        e.preventDefault();
                        const highlightedOption = dropdown.querySelector('.employee-option.highlight');
                        if (highlightedOption) {
                            highlightedOption.click();
                        }
                        break;
                    case 'Escape':
                        dropdown.classList.add('hidden');
                        break;
                }
            });
        }
        
        // Rental Receiver Search
        function initializeRentalReceiverSearch() {
            const searchInput = document.getElementById('rental_receiver_search');
            const dropdown = document.getElementById('rental_receiver_dropdown');
            const hiddenInput = document.getElementById('rental_receiver');
            
            if (!searchInput || !dropdown || !hiddenInput) return;
            
            // Show dropdown on focus
            searchInput.addEventListener('focus', function() {
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Filter rentals based on search input
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = dropdown.querySelectorAll('.rental-option');
                
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'block';
                        // Highlight search term
                        const highlightedText = option.dataset.text.replace(
                            new RegExp(searchTerm, 'gi'),
                            match => `<mark class="bg-yellow-200">${match}</mark>`
                        );
                        option.innerHTML = highlightedText;
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Handle rental option selection
            dropdown.addEventListener('click', function(e) {
                if (e.target.classList.contains('rental-option')) {
                    const option = e.target;
                    const selectedValue = option.dataset.value;
                    const selectedText = option.dataset.text;
                    const rentalId = option.dataset.rentalId;
                    
                    searchInput.value = selectedText;
                    hiddenInput.value = selectedValue;
                    dropdown.classList.add('hidden');
                    
                    // Remove highlighting
                    option.innerHTML = option.dataset.text;
                    
                    // Set project_id for compatibility
                    const projectIdInput = document.getElementById('project_id');
                    if (projectIdInput) {
                        projectIdInput.value = rentalId;
                    }
                    
                    // Set project_receiver for compatibility
                    const projectReceiverInput = document.getElementById('project_receiver');
                    if (projectReceiverInput) {
                        try {
                            const fullText = selectedText.trim();
                                    // Extract name from format: "CODE - NAME (CUSTOMER)"
                                    const matches = fullText.match(/^[^-]+ - ([^(]+)/);
                                    if (matches && matches[1]) {
                                        projectReceiverInput.value = matches[1].trim();
                                    } else {
                                        // Fallback: use full text if pattern doesn't match
                                        projectReceiverInput.value = fullText;
                                    }
                        } catch (error) {
                            console.error('Error processing rental receiver:', error);
                            // Fallback: use raw value
                            projectReceiverInput.value = selectedValue;
                        }
                    }
                }
            });
            
            // Keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                const options = Array.from(dropdown.querySelectorAll('.rental-option:not([style*="display: none"])'));
                const currentIndex = options.findIndex(option => option.classList.contains('highlight'));
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (currentIndex < options.length - 1) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex + 1].classList.add('highlight');
                        }
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        if (currentIndex > 0) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex - 1].classList.add('highlight');
                        }
                        break;
                    case 'Enter':
                        e.preventDefault();
                        const highlightedOption = dropdown.querySelector('.rental-option.highlight');
                        if (highlightedOption) {
                            highlightedOption.click();
                        }
                        break;
                    case 'Escape':
                        dropdown.classList.add('hidden');
                        break;
                }
            });
        }
        
        // Warranty Receiver Search
        function initializeWarrantyReceiverSearch() {
            const searchInput = document.getElementById('warranty_receiver_search');
            const dropdown = document.getElementById('warranty_receiver_dropdown');
            const hiddenInput = document.getElementById('warranty_receiver');
            
            if (!searchInput || !dropdown || !hiddenInput) return;
            
            // Show dropdown on focus
            searchInput.addEventListener('focus', function() {
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Filter warranty receivers based on search input
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = dropdown.querySelectorAll('.warranty-option');
                
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'block';
                        // Highlight search term
                        const highlightedText = option.dataset.text.replace(
                            new RegExp(searchTerm, 'gi'),
                            match => `<mark class="bg-yellow-200">${match}</mark>`
                        );
                        option.innerHTML = highlightedText;
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Handle warranty option selection
            dropdown.addEventListener('click', function(e) {
                if (e.target.classList.contains('warranty-option')) {
                    const option = e.target;
                    const selectedValue = option.dataset.value;
                    const selectedText = option.dataset.text;
                    const selectedType = option.dataset.type;
                    const selectedId = selectedType === 'project' ? option.dataset.projectId : option.dataset.rentalId;
                    
                    searchInput.value = selectedText;
                    hiddenInput.value = selectedValue;
                    dropdown.classList.add('hidden');
                    
                    // Remove highlighting
                    option.innerHTML = option.dataset.text;
                    
                    // Update project_id based on type
                    const projectIdInput = document.getElementById('project_id');
                    if (projectIdInput) {
                        projectIdInput.value = selectedId;
                    }
                    
                    // Set warranty period
                    const warrantyPeriodInput = document.getElementById('warranty_period');
                    if (warrantyPeriodInput) {
                        if (selectedType === 'project' && option.dataset.warrantyPeriod) {
                            warrantyPeriodInput.value = option.dataset.warrantyPeriod + ' tháng';
                        } else {
                            warrantyPeriodInput.value = '12 tháng'; // Default for rental
                        }
                    }
                    
                    // Set project_receiver for compatibility
                    const projectReceiverInput = document.getElementById('project_receiver');
                    if (projectReceiverInput) {
                        try {
                            const fullText = selectedText.trim();
                            // Extract name from format: "CODE - NAME (CUSTOMER)"
                            const matches = fullText.match(/^[^-]+ - ([^(]+)/);
                            if (matches && matches[1]) {
                                projectReceiverInput.value = matches[1].trim();
                            } else {
                                // Fallback: use full text if pattern doesn't match
                                projectReceiverInput.value = fullText;
                            }
                                } catch (error) {
                                    console.error('Error processing warranty receiver:', error);
                                    // Fallback: use raw value
                                    projectReceiverInput.value = selectedValue;
                                }
                    }
                }
            });
            
            // Keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                const options = Array.from(dropdown.querySelectorAll('.warranty-option:not([style*="display: none"])'));
                const currentIndex = options.findIndex(option => option.classList.contains('highlight'));
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (currentIndex < options.length - 1) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex + 1].classList.add('highlight');
                        }
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        if (currentIndex > 0) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex - 1].classList.add('highlight');
                        }
                        break;
                    case 'Enter':
                        e.preventDefault();
                        const highlightedOption = dropdown.querySelector('.warranty-option.highlight');
                        if (highlightedOption) {
                            highlightedOption.click();
                        }
                        break;
                    case 'Escape':
                        dropdown.classList.add('hidden');
                        break;
                }
            });
        }
        
        // Contract Product Search
        function initializeContractProductSearch() {
            const searchInput = document.getElementById('contract_product_search');
            const dropdown = document.getElementById('contract_product_dropdown');
            const hiddenInput = document.getElementById('contract_product_select');
            
            if (!searchInput || !dropdown || !hiddenInput) return;
            
            // Show dropdown on focus
            searchInput.addEventListener('focus', function() {
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Filter products based on search input
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = dropdown.querySelectorAll('.product-option');
                
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'block';
                        // Highlight search term
                        const highlightedText = option.dataset.text.replace(
                            new RegExp(searchTerm, 'gi'),
                            match => `<mark class="bg-yellow-200">${match}</mark>`
                        );
                        option.innerHTML = highlightedText;
                            } else {
                        option.style.display = 'none';
                    }
                });
                
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Handle product option selection
            dropdown.addEventListener('click', function(e) {
                if (e.target.classList.contains('product-option')) {
                    const option = e.target;
                    const selectedValue = option.dataset.value; // This now contains "id_type"
                    const selectedText = option.dataset.text;
                    const selectedType = option.dataset.type;
                    const selectedId = option.dataset.id;
                    
                    searchInput.value = selectedText;
                    hiddenInput.value = selectedValue; // Store the combined value
                    hiddenInput.dataset.type = selectedType; // Store type separately
                    hiddenInput.dataset.id = selectedId; // Store id separately
                    dropdown.classList.add('hidden');
                    
                    // Remove highlighting
                    option.innerHTML = option.dataset.text;
                }
            });
            
            // Keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                const options = Array.from(dropdown.querySelectorAll('.product-option:not([style*="display: none"])'));
                const currentIndex = options.findIndex(option => option.classList.contains('highlight'));
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (currentIndex < options.length - 1) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex + 1].classList.add('highlight');
                        }
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        if (currentIndex > 0) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex - 1].classList.add('highlight');
                        }
                        break;
                    case 'Enter':
                        e.preventDefault();
                        const highlightedOption = dropdown.querySelector('.product-option.highlight');
                        if (highlightedOption) {
                            const selectedValue = highlightedOption.dataset.value;
                            const selectedText = highlightedOption.dataset.text;
                            const selectedType = highlightedOption.dataset.type;
                            const selectedId = highlightedOption.dataset.id;
                            
                            searchInput.value = selectedText;
                            hiddenInput.value = selectedValue;
                            hiddenInput.dataset.type = selectedType;
                            hiddenInput.dataset.id = selectedId;
                            dropdown.classList.add('hidden');
                        }
                        break;
                    case 'Escape':
                        dropdown.classList.add('hidden');
                        break;
                }
            });
        }
        
        // Backup Product Search
        function initializeBackupProductSearch() {
            const searchInput = document.getElementById('backup_product_search');
            const dropdown = document.getElementById('backup_product_dropdown');
            const hiddenInput = document.getElementById('backup_product_select');
            
            if (!searchInput || !dropdown || !hiddenInput) return;
            
            // Show dropdown on focus
            searchInput.addEventListener('focus', function() {
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Filter products based on search input
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const options = dropdown.querySelectorAll('.product-option');
                
                options.forEach(option => {
                    const text = option.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        option.style.display = 'block';
                        // Highlight search term
                        const highlightedText = option.dataset.text.replace(
                            new RegExp(searchTerm, 'gi'),
                            match => `<mark class="bg-yellow-200">${match}</mark>`
                        );
                        option.innerHTML = highlightedText;
                    } else {
                        option.style.display = 'none';
                    }
                });
                
                if (dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Handle product option selection
            dropdown.addEventListener('click', function(e) {
                if (e.target.classList.contains('product-option')) {
                    const option = e.target;
                    const selectedValue = option.dataset.value; // This now contains "id_type"
                    const selectedText = option.dataset.text;
                    const selectedType = option.dataset.type;
                    const selectedId = option.dataset.id;
                    
                    searchInput.value = selectedText;
                    hiddenInput.value = selectedValue; // Store the combined value
                    hiddenInput.dataset.type = selectedType; // Store type separately
                    hiddenInput.dataset.id = selectedId; // Store id separately
                    dropdown.classList.add('hidden');
                    
                    // Remove highlighting
                    option.innerHTML = option.dataset.text;
                }
            });
            
            // Keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                const options = Array.from(dropdown.querySelectorAll('.product-option:not([style*="display: none"])'));
                const currentIndex = options.findIndex(option => option.classList.contains('highlight'));
                
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (currentIndex < options.length - 1) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex + 1].classList.add('hidden');
                        }
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        if (currentIndex > 0) {
                            options.forEach(option => option.classList.remove('highlight'));
                            options[currentIndex - 1].classList.add('highlight');
                        }
                        break;
                    case 'Enter':
                        e.preventDefault();
                        const highlightedOption = dropdown.querySelector('.product-option.highlight');
                        if (highlightedOption) {
                            const selectedValue = highlightedOption.dataset.value;
                            const selectedText = highlightedOption.dataset.text;
                            const selectedType = highlightedOption.dataset.type;
                            const selectedId = highlightedOption.dataset.id;
                            
                            searchInput.value = selectedText;
                            hiddenInput.value = selectedValue;
                            hiddenInput.dataset.type = selectedType;
                            hiddenInput.dataset.id = selectedId;
                            dropdown.classList.add('hidden');
                        }
                        break;
                    case 'Escape':
                        dropdown.classList.add('hidden');
                        break;
                }
            });
        }
        
        // Hide dropdown when clicking outside with delay, using closest on IDs
        let hideTimeout;
        document.addEventListener('mousedown', function(e) {
            const isInsideAny =
                e.target.closest('#company_representative_search') ||
                e.target.closest('#company_representative_dropdown') ||
                e.target.closest('#project_receiver_search') ||
                e.target.closest('#project_receiver_dropdown') ||
                e.target.closest('#rental_receiver_search') ||
                e.target.closest('#rental_receiver_dropdown') ||
                e.target.closest('#warranty_receiver_search') ||
                e.target.closest('#warranty_receiver_dropdown') ||
                e.target.closest('#contract_product_search') ||
                e.target.closest('#contract_product_dropdown') ||
                e.target.closest('#backup_product_search') ||
                e.target.closest('#backup_product_dropdown');

            if (!isInsideAny) {
                clearTimeout(hideTimeout);
                hideTimeout = setTimeout(() => {
                    const dropdowns = document.querySelectorAll('#company_representative_dropdown, #project_receiver_dropdown, #rental_receiver_dropdown, #warranty_receiver_dropdown, #contract_product_dropdown, #backup_product_dropdown');
                    dropdowns.forEach(dropdown => dropdown.classList.add('hidden'));
                }, 300);
            }
        });

        // Prevent blur closing when clicking inside dropdowns (before focusout)
        document.addEventListener('mousedown', function(e) {
            if (e.target.closest('#company_representative_dropdown, #project_receiver_dropdown, #rental_receiver_dropdown, #warranty_receiver_dropdown, #contract_product_dropdown, #backup_product_dropdown')) {
                e.preventDefault();
                clearTimeout(hideTimeout);
            }
        });

        // Ensure rental/warranty data loads on first focus if empty
        const rentalSearchInputEl = document.getElementById('rental_receiver_search');
        const rentalDropdownEl = document.getElementById('rental_receiver_dropdown');
        if (rentalSearchInputEl && rentalDropdownEl) {
            rentalSearchInputEl.addEventListener('focus', function() {
                if (rentalDropdownEl.children.length === 0) {
                    if (typeof loadRentals === 'function') loadRentals();
                }
            });
        }
        const warrantySearchInputEl = document.getElementById('warranty_receiver_search');
        const warrantyDropdownEl = document.getElementById('warranty_receiver_dropdown');
        if (warrantySearchInputEl && warrantyDropdownEl) {
            warrantySearchInputEl.addEventListener('focus', function() {
                if (warrantyDropdownEl.children.length === 0) {
                    if (typeof loadWarrantyReceivers === 'function') loadWarrantyReceivers();
                }
            });
        }

        // Ensure contract/backup product lists show on focus if data already loaded
        const contractProductSearch = document.getElementById('contract_product_search');
        const contractProductDropdown = document.getElementById('contract_product_dropdown');
        if (contractProductSearch && contractProductDropdown) {
            contractProductSearch.addEventListener('focus', function() {
                if (contractProductDropdown.children.length > 0) {
                    contractProductDropdown.classList.remove('hidden');
                }
            });
        }
        const backupProductSearch = document.getElementById('backup_product_search');
        const backupProductDropdown = document.getElementById('backup_product_dropdown');
        if (backupProductSearch && backupProductDropdown) {
            backupProductSearch.addEventListener('focus', function() {
                if (backupProductDropdown.children.length > 0) {
                    backupProductDropdown.classList.remove('hidden');
                }
            });
        }
    </script>
</body>

</html>
