<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa chữa & Bảo trì - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <a href="{{ route('repairs.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Tạo phiếu sửa chữa & Bảo trì thiết bị</h1>
            </div>
        </header>

        <main class="p-6">
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('repairs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Thông tin bảo hành -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                        Thông tin bảo hành
                    </h2>

                    <!-- Tabs for different search methods -->
                    <div class="mb-4">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button type="button" id="warranty-tab" class="search-tab border-b-2 border-blue-500 py-2 px-1 text-sm font-medium text-blue-600" aria-current="page">
                                    <i class="fas fa-shield-alt mr-2"></i>
                                    Tìm theo bảo hành
                                </button>
                                <button type="button" id="warehouse-tab" class="search-tab border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                    <i class="fas fa-warehouse mr-2"></i>
                                    Tìm trong kho
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Warranty search section -->
                    <div id="warranty-search-section" class="search-section">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="warranty_code" class="block text-sm font-medium text-gray-700 mb-1">Mã bảo hành
                                    hoặc thiết bị
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="warranty_code" name="warranty_code"
                                        value="{{ old('warranty_code') }}"
                                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Nhập mã bảo hành (nếu có)">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-hashtag text-gray-500"></i>
                                    </div>
                                    <button type="button" id="search_warranty"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Khách
                                    hàng</label>
                                <input type="text" id="customer_name" name="customer_name" readonly
                                    class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                            </div>
                        </div>
                    </div>

                    <!-- Warehouse search section -->
                    <div id="warehouse-search-section" class="search-section hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="warehouse_search_term" class="block text-sm font-medium text-gray-700 mb-1">Tìm thiết bị trong kho
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="warehouse_search_term" name="warehouse_search_term"
                                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Nhập mã thiết bị, tên hoặc serial">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-500"></i>
                                    </div>
                                    <button type="button" id="search_warehouse_devices"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nguồn thiết bị</label>
                                <div class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                                    <i class="fas fa-warehouse mr-2"></i>
                                    Thiết bị trong kho
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phần chọn thiết bị -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Thiết bị</label>

                        <!-- Danh sách thiết bị đã chọn -->
                        <div id="selected_devices" class="space-y-2 mb-4">
                            <!-- Selected devices will be displayed here -->
                        </div>

                        <!-- Danh sách thiết bị -->
                        <div id="devices_container" class="mt-4 mb-2 border-t border-gray-200 pt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Danh sách thiết bị</h3>
                            <div class="max-h-50 overflow-y-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Mã thiết bị
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tên thiết bị
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Serial
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Loại
                                            </th>
                                            
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Chú thích
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Hình ảnh
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Thao tác
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="devices_list">
                                        <!-- Danh sách thiết bị sẽ được thêm vào đây qua JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Chi tiết vật tư thiết bị -->
                    <div id="device_materials" class="mt-4 hidden">
                        <h3 class="font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-cogs text-blue-500 mr-2"></i>
                            Chi tiết vật tư từ thiết bị
                        </h3>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã vật tư</th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên vật tư</th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Serial vật tư</th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="device_materials_body">
                                    <!-- Dữ liệu sẽ được thêm vào đây bằng JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Lịch sử sửa chữa của thiết bị -->
                    <div id="repair_history" class="mt-4 hidden">
                        <h3 class="font-medium text-gray-700 mb-2">Lịch sử sửa chữa</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ngày</th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Loại</th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mô tả</th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Kỹ thuật viên</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="repair_history_body">
                                    <!-- Dữ liệu sẽ được thêm vào đây bằng JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Thông tin sửa chữa -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tools text-blue-500 mr-2"></i>
                        Thông tin sửa chữa
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="repair_type"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Loại
                                sửa chữa</label>
                            <select id="repair_type" name="repair_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Chọn loại sửa chữa</option>
                                <option value="maintenance"
                                    {{ old('repair_type') == 'maintenance' ? 'selected' : '' }}>Bảo trì định kỳ</option>
                                <option value="repair" {{ old('repair_type') == 'repair' ? 'selected' : '' }}>Sửa chữa
                                    lỗi</option>
                                <option value="replacement"
                                    {{ old('repair_type') == 'replacement' ? 'selected' : '' }}>Thay thế linh kiện
                                </option>
                                <option value="upgrade" {{ old('repair_type') == 'upgrade' ? 'selected' : '' }}>Nâng
                                    cấp</option>
                                <option value="other" {{ old('repair_type') == 'other' ? 'selected' : '' }}>Khác
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="repair_date"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày
                                sửa chữa</label>
                            <input type="date" id="repair_date" name="repair_date"
                                value="{{ old('repair_date', date('Y-m-d')) }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="technician_id"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật viên</label>
                            <select id="technician_id" name="technician_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kỹ thuật viên --</option>
                                @foreach (App\Models\Employee::where('status', 'active')->get() as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ old('technician_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="repair_description"
                            class="block text-sm font-medium text-gray-700 mb-1 required">Mô tả sửa chữa</label>
                        <textarea id="repair_description" name="repair_description" rows="3" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập mô tả chi tiết về vấn đề và cách sửa chữa">{{ old('repair_description') }}</textarea>
                    </div>
                </div>

                <!-- Đính kèm & Ghi chú -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-paperclip text-blue-500 mr-2"></i>
                        Đính kèm & Ghi chú
                    </h2>

                    <div class="mb-4">
                        <label for="repair_photos" class="block text-sm font-medium text-gray-700 mb-1">Hình
                            ảnh</label>
                        <input type="file" id="repair_photos" name="repair_photos[]" multiple accept="image/*"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Tối đa 5 ảnh, kích thước mỗi ảnh không quá 2MB</p>
                    </div>

                    <div>
                        <label for="repair_notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="repair_notes" name="repair_notes" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập ghi chú bổ sung">{{ old('repair_notes') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('repairs.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Lưu thông tin
                    </button>
                </div>
            </form>
        </main>
    </div>

    <!-- Modal thay thế vật tư -->
    <div id="replace-material-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Thay thế vật tư</h3>
                <button type="button" id="close-replace-modal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Thông tin vật tư cần thay thế -->
                <div class="bg-gray-50 p-3 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Thông tin vật tư:</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Mã vật tư:</span>
                            <span id="replace-material-code" class="font-medium ml-2"></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Tên vật tư:</span>
                            <span id="replace-material-name" class="font-medium ml-2"></span>
                        </div>
                    </div>
                </div>

                <!-- Chuyển vật tư cũ đến kho -->
                <div>
                    <label for="source-warehouse" class="block text-sm font-medium text-gray-700 mb-1">
                        Chuyển vật tư cũ đến kho: <span class="text-red-500">*</span>
                    </label>
                    <select id="source-warehouse" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Chọn kho chuyển --</option>
                        @foreach (App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Số lượng cần thay thế -->
                <div>
                    <label for="replace-quantity" class="block text-sm font-medium text-gray-700 mb-1">
                        Số lượng cần thay thế: <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="replace-quantity" min="1" max="1" value="1"
                        required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Tối đa: <span id="max-quantity">1</span> (số lượng vật tư
                        trong
                        thành phẩm)</p>
                </div>

                <!-- Chọn serial vật tư cũ cần thay thế -->
                <div id="old-serial-selection" class="hidden">
                    <label id="old-serial-label" class="block text-sm font-medium text-gray-700 mb-2">
                        Chọn serial cần thay thế <span id="old-serial-required-star" class="text-red-500">*</span>
                    </label>
                    <div id="old-serial-list"
                        class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3">
                        <!-- Danh sách serial cũ sẽ được load vào đây -->
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Chọn <span id="required-old-serial-count">1</span> serial
                        cần thay thế</p>
                </div>

                <!-- Thay thế bằng vật tư mới -->
                <div>
                    <label for="target-warehouse" class="block text-sm font-medium text-gray-700 mb-1">
                        Kho lấy vật tư mới <span class="text-red-500">*</span>
                    </label>
                    <select id="target-warehouse" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Chọn kho lấy vật tư --</option>
                        @foreach (App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Serial vật tư mới -->
                <div id="serial-selection" class="hidden">
                    <label id="new-serial-label" class="block text-sm font-medium text-gray-700 mb-2">
                        Chọn serial mới <span id="new-serial-required-star" class="text-red-500">*</span>
                    </label>
                    <div id="serial-list"
                        class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3">
                        <!-- Danh sách serial sẽ được load vào đây -->
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Chọn <span id="required-serial-count">1</span> serial để
                        thay thế</p>
                </div>

                <!-- Ghi chú -->
                <div>
                    <label for="replace-notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                    <textarea id="replace-notes" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nhập ghi chú về việc thay thế vật tư"></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" id="cancel-replace-btn"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Hủy
                </button>
                <button type="button" id="confirm-replace-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-check mr-2"></i> Xác nhận thay thế
                </button>
            </div>
        </div>
    </div>

    <!-- Modal từ chối thiết bị -->
    <div id="reject-device-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Từ chối thiết bị</h3>
                <button type="button" id="close-reject-modal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Thông tin thiết bị -->
                <div class="bg-gray-50 p-3 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700">Thiết bị:
                        <span id="reject-device-name" class="font-semibold"></span>
                    </h4>
                </div>

                <!-- Lý do từ chối -->
                <div>
                    <label for="reject-reason" class="block text-sm font-medium text-gray-700 mb-1">
                        Lý do từ chối <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reject-reason" rows="4" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nhập lý do từ chối thiết bị..."></textarea>
                </div>

                <!-- Theo yêu cầu: chỉ giữ trường Lý do từ chối -->
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" id="cancel-reject-btn"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Hủy
                </button>
                <button type="button" id="confirm-reject-btn"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-ban mr-2"></i>Xác nhận từ chối
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy các elements
            const warrantyCodeInput = document.getElementById('warranty_code');
            const searchWarrantyBtn = document.getElementById('search_warranty');
            const customerNameInput = document.getElementById('customer_name');
            const selectedDevicesContainer = document.getElementById('selected_devices');
            const deviceMaterials = document.getElementById('device_materials');
            const deviceMaterialsBody = document.getElementById('device_materials_body');
            const repairHistory = document.getElementById('repair_history');
            const repairHistoryBody = document.getElementById('repair_history_body');

            // Tab elements
            const warrantyTab = document.getElementById('warranty-tab');
            const warehouseTab = document.getElementById('warehouse-tab');
            const warrantySearchSection = document.getElementById('warranty-search-section');
            const warehouseSearchSection = document.getElementById('warehouse-search-section');

            // Warehouse search elements
            const warehouseSearchTermInput = document.getElementById('warehouse_search_term');
            const searchWarehouseDevicesBtn = document.getElementById('search_warehouse_devices');

            // Modal elements
            const replaceModal = document.getElementById('replace-material-modal');
            const closeReplaceModal = document.getElementById('close-replace-modal');
            const cancelReplaceBtn = document.getElementById('cancel-replace-btn');
            const confirmReplaceBtn = document.getElementById('confirm-replace-btn');


            // Mảng lưu trữ các thiết bị đã chọn
            let selectedDevices = [];
            let deviceCounter = 0;
            let deviceMaterialsList = []; // Mảng lưu trữ vật tư từ các thiết bị
            let currentReplacingMaterial = null; // Vật tư hiện tại đang thay thế
            let currentRejectingDevice = null; // Thiết bị hiện tại đang từ chối
            let rejectedDevices = []; // Lưu danh sách thiết bị đã từ chối
            let materialReplacements = []; // Lưu danh sách thay thế vật tư
            let currentWarrantyCode = null; // Mã bảo hành hiện tại

            // Setup CSRF token for AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Tab switching functionality
            warrantyTab.addEventListener('click', function() {
                switchTab('warranty');
            });

            warehouseTab.addEventListener('click', function() {
                switchTab('warehouse');
            });

            function switchTab(activeTab) {
                // Update tab styles
                if (activeTab === 'warranty') {
                    warrantyTab.className = 'search-tab border-b-2 border-blue-500 py-2 px-1 text-sm font-medium text-blue-600';
                    warehouseTab.className = 'search-tab border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300';
                    
                    // Show/hide sections
                    warrantySearchSection.classList.remove('hidden');
                    warehouseSearchSection.classList.add('hidden');
                    
                    // Clear warehouse search results
                    clearDeviceList();
                } else {
                    warrantyTab.className = 'search-tab border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300';
                    warehouseTab.className = 'search-tab border-b-2 border-blue-500 py-2 px-1 text-sm font-medium text-blue-600';
                    
                    // Show/hide sections
                    warrantySearchSection.classList.add('hidden');
                    warehouseSearchSection.classList.remove('hidden');
                    
                    // Clear warranty search results
                    clearDeviceList();
                    customerNameInput.value = '';
                    repairHistory.classList.add('hidden');
                }
            }

            // Xử lý sự kiện tìm kiếm thiết bị trong kho
            searchWarehouseDevicesBtn.addEventListener('click', function() {
                const searchTerm = warehouseSearchTermInput.value.trim();

                if (!searchTerm) {
                    alert('Vui lòng nhập mã thiết bị, tên hoặc serial');
                    return;
                }

                // Gọi API tìm kiếm thiết bị trong kho
                fetch('/api/repairs/search-warehouse-devices?search_term=' + encodeURIComponent(searchTerm), {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Hiển thị danh sách thiết bị từ kho
                            displayDevicesFromWarehouse(data.devices);
                        } else {
                            alert(data.message || 'Không tìm thấy thiết bị trong kho');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi tìm kiếm thiết bị trong kho');
                    });
            });

            // Xử lý sự kiện tìm kiếm mã bảo hành
            searchWarrantyBtn.addEventListener('click', function() {
                const warrantyCode = warrantyCodeInput.value.trim();

                if (!warrantyCode) {
                    alert('Vui lòng nhập mã bảo hành');
                    return;
                }

                // Gọi API tìm kiếm bảo hành
                fetch('/api/repairs/search-warranty?warranty_code=' + encodeURIComponent(warrantyCode), {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const warranty = data.warranty;

                            // Lưu warranty code để sử dụng sau
                            currentWarrantyCode = warranty.warranty_code;

                            // Hiển thị thông tin khách hàng
                            customerNameInput.value = warranty.customer_name;

                            // Hiển thị danh sách thiết bị từ bảo hành
                            if (warranty.devices && warranty.devices.length > 0) {
                                displayDevicesFromWarranty(warranty.devices);
                            }

                            // Hiển thị lịch sử sửa chữa nếu có
                            if (warranty.repair_history && warranty.repair_history.length > 0) {
                                repairHistoryBody.innerHTML = '';
                                warranty.repair_history.forEach(repair => {
                                    repairHistoryBody.innerHTML += `
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${repair.date}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${repair.type}</td>
                                        <td class="px-3 py-2 text-sm text-gray-700">${repair.description}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${repair.technician}</td>
                                    </tr>
                                `;
                                });
                                repairHistory.classList.remove('hidden');
                            } else {
                                repairHistory.classList.add('hidden');
                            }
                        } else {
                            alert(data.message || 'Không tìm thấy thông tin bảo hành');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi tìm kiếm bảo hành');
                    });
            });



            // Hàm hiển thị danh sách thiết bị từ bảo hành
            function displayDevicesFromWarranty(devices) {
                const devicesList = document.getElementById('devices_list');
                devicesList.innerHTML = '';

                devices.forEach(device => {
                    // Determine if this is a good or a product
                    const isGood = device.type === 'good';
                    const deviceTypeTextDisplay = isGood ? 'Hàng hoá' : 'Thành phẩm';

                    // Get source display text
                    const sourceDisplay = getSourceDisplayText(device.source);

                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    row.innerHTML = `
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${device.code}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">
                            ${device.name}
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700" style="max-width: 200px; word-wrap: break-word;">${(device.serial_numbers_text && device.serial_numbers_text.trim()) ? device.serial_numbers_text : (device.serial && device.serial.trim()) ? device.serial : 'N/A'}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${deviceTypeTextDisplay}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 hidden">
                            <input type="number" min="1" max="${device.quantity || 1}" value="1" 
                                   class="w-16 border border-gray-300 rounded px-2 py-1 text-center device-quantity" 
                                   data-device-id="${device.id}">
                            <span class="text-xs text-gray-500 ml-1">/${device.quantity || 1}</span>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap hidden">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(device.status)}">
                                ${getStatusText(device.status)}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700">
                            <textarea class="w-[300px] border border-gray-300 rounded px-2 py-1 text-xs device-notes" 
                                      rows="2" placeholder="Nhập chú thích..." data-device-id="${device.id}"></textarea>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">
                            <input type="file" multiple accept="image/*" 
                                   class="text-xs device-images" data-device-id="${device.id}">
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">
                            <div class="flex space-x-1">
                                <button type="button" class="select-device-btn bg-blue-100 text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition-colors text-xs" 
                                        data-device='${JSON.stringify(device)}'>
                                    <i class="fas fa-check-circle mr-1"></i> Chọn
                                </button>
                                <button type="button" class="reject-device-btn bg-red-100 text-red-600 px-2 py-1 rounded hover:bg-red-200 transition-colors text-xs" 
                                        data-device='${JSON.stringify(device)}'>
                                    <i class="fas fa-times mr-1"></i> Từ chối
                                </button>
                            </div>
                        </td>
                    `;
                    devicesList.appendChild(row);
                });

                // Thêm event listeners cho các button chọn/từ chối
                addDeviceEventListeners();
            }

            // Hàm lấy class CSS cho trạng thái
            function getStatusClass(status) {
                switch (status) {
                    case 'active':
                    case 'Hoạt động':
                        return 'bg-green-100 text-green-800';
                    case 'inactive':
                    case 'Không hoạt động':
                        return 'bg-red-100 text-red-800';
                    case 'maintenance':
                    case 'Bảo trì':
                        return 'bg-yellow-100 text-yellow-800';
                    default:
                        return 'bg-gray-100 text-gray-800';
                }
            }

            // Hàm lấy text hiển thị cho trạng thái
            function getStatusText(status) {
                switch (status) {
                    case 'active':
                        return 'Hoạt động';
                    case 'inactive':
                        return 'Không hoạt động';
                    case 'maintenance':
                        return 'Bảo trì';
                    case 'Hoạt động':
                    case 'Không hoạt động':
                    case 'Bảo trì':
                        return status;
                    default:
                        return 'Không xác định';
                }
            }

            // Hàm hiển thị danh sách thiết bị từ kho
            function displayDevicesFromWarehouse(devices) {
                const devicesList = document.getElementById('devices_list');
                devicesList.innerHTML = '';

                devices.forEach(device => {
                    // Determine if this is a good or a product
                    const isGood = device.type === 'good';
                    const deviceTypeTextDisplay = isGood ? 'Hàng hoá' : 'Thành phẩm';

                    // Get source display text
                    const sourceDisplay = getSourceDisplayText(device.source);

                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    row.innerHTML = `
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${device.code}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">
                            ${device.name}
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700" style="max-width: 200px; word-wrap: break-word;">${(device.serial_numbers_text && device.serial_numbers_text.trim()) ? device.serial_numbers_text : (device.serial && device.serial.trim()) ? device.serial : 'N/A'}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${deviceTypeTextDisplay}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 hidden">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getSourceClass(device.source)}">
                                ${sourceDisplay}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">${device.warehouse_name}</div>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700 hidden">
                            <input type="number" min="1" max="${device.quantity || 1}" value="1" 
                                   class="w-16 border border-gray-300 rounded px-2 py-1 text-center device-quantity" 
                                   data-device-id="${device.id}">
                            <span class="text-xs text-gray-500 ml-1">/${device.quantity || 1}</span>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap hidden">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(device.status)}">
                                ${getStatusText(device.status)}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700">
                            <textarea class="w-[300px] border border-gray-300 rounded px-2 py-1 text-xs device-notes" 
                                      rows="2" placeholder="Nhập chú thích..." data-device-id="${device.id}"></textarea>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">
                            <input type="file" multiple accept="image/*" 
                                   class="text-xs device-images" data-device-id="${device.id}">
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">
                            <div class="flex space-x-1">
                                <button type="button" class="select-device-btn bg-blue-100 text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition-colors text-xs" 
                                        data-device='${JSON.stringify(device)}'>
                                    <i class="fas fa-check-circle mr-1"></i> Chọn
                                </button>
                                <button type="button" class="reject-device-btn bg-red-100 text-red-600 px-2 py-1 rounded hover:bg-red-200 transition-colors text-xs" 
                                        data-device='${JSON.stringify(device)}'>
                                    <i class="fas fa-times mr-1"></i> Từ chối
                                </button>
                            </div>
                        </td>
                    `;
                    devicesList.appendChild(row);
                });

                // Thêm event listeners cho các button chọn/từ chối (tương tự như warranty)
                addDeviceEventListeners();
            }

            // Hàm xóa danh sách thiết bị
            function clearDeviceList() {
                const devicesList = document.getElementById('devices_list');
                devicesList.innerHTML = '';
            }

            // Hàm lấy class CSS cho nguồn thiết bị
            function getSourceClass(source) {
                switch (source) {
                    case 'contract':
                        return 'bg-blue-100 text-blue-800';
                    case 'backup':
                        return 'bg-green-100 text-green-800';
                    case 'mixed':
                        return 'bg-purple-100 text-purple-800';
                    case 'warehouse':
                        return 'bg-orange-100 text-orange-800';
                    default:
                        return 'bg-gray-100 text-gray-800';
                }
            }

            // Hàm lấy text hiển thị cho nguồn thiết bị
            function getSourceDisplayText(source) {
                switch (source) {
                    case 'contract':
                        return 'Hợp đồng';
                    case 'backup':
                        return 'Dự phòng';
                    case 'mixed':
                        return 'Hỗn hợp';
                    case 'warehouse':
                        return 'Trong kho';
                    default:
                        return 'Không xác định';
                }
            }

            // Hàm thêm event listeners cho các button thiết bị
            function addDeviceEventListeners() {
                // Thêm event listeners cho input số lượng để validation
                document.querySelectorAll('.device-quantity').forEach(input => {
                    input.addEventListener('input', function() {
                        const maxQuantity = parseInt(this.getAttribute('max'));
                        const currentValue = parseInt(this.value);

                        if (currentValue > maxQuantity) {
                            this.value = maxQuantity;
                            alert(
                                `⚠️ Số lượng không được vượt quá ${maxQuantity} sản phẩm có sẵn!`
                                );
                        }

                        if (currentValue < 1) {
                            this.value = 1;
                        }
                    });

                    input.addEventListener('keypress', function(e) {
                        // Chỉ cho phép số
                        if (!/[\d]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter']
                            .includes(e.key)) {
                            e.preventDefault();
                        }
                    });

                    input.addEventListener('paste', function(e) {
                        e.preventDefault();
                        const paste = (e.clipboardData || window.clipboardData).getData('text');
                        const maxQuantity = parseInt(this.getAttribute('max'));
                        const pasteValue = parseInt(paste);

                        if (!isNaN(pasteValue)) {
                            if (pasteValue > maxQuantity) {
                                this.value = maxQuantity;
                                alert(
                                    `⚠️ Số lượng không được vượt quá ${maxQuantity} sản phẩm có sẵn!`
                                    );
                            } else if (pasteValue < 1) {
                                this.value = 1;
                            } else {
                                this.value = pasteValue;
                            }
                        }
                    });
                });

                // Thêm event listeners cho các button chọn/từ chối
                document.querySelectorAll('.select-device-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const device = JSON.parse(this.getAttribute('data-device'));
                        const row = this.closest('tr');

                        // Toggle off if already selected
                        if (isDeviceSelected(device.id)) {
                            removeDeviceById(device.id);
                            // Sau khi huỷ chọn, nếu không còn vật tư thì ẩn bảng; ngược lại render lại
                            if (typeof updateMaterialsDisplay === 'function') {
                                updateMaterialsDisplay();
                            }
                            if (deviceMaterialsList.length === 0) {
                                deviceMaterials.classList.add('hidden');
                            }
                            // Reset UI
                            row.style.backgroundColor = '';
                            this.innerHTML = '<i class="fas fa-check mr-1"></i> Chọn';
                            this.className =
                                'select-device-btn bg-blue-100 text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition-colors text-xs';
                            const rejectBtn = row.querySelector('.reject-device-btn');
                            if (rejectBtn) {
                                rejectBtn.disabled = false;
                                rejectBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                rejectBtn.className =
                                    'reject-device-btn bg-red-100 text-red-600 px-2 py-1 rounded hover:bg-red-200 transition-colors text-xs';
                                rejectBtn.innerHTML = '<i class="fas fa-times mr-1"></i> Từ chối';
                            }
                            return;
                        }

                        const quantityInput = row.querySelector('.device-quantity');
                        const quantity = parseInt(quantityInput.value);
                        const maxQuantity = parseInt(quantityInput.getAttribute('max'));
                        const notes = row.querySelector('.device-notes').value;
                        const imageInput = row.querySelector('.device-images');
                        const images = imageInput.files;

                        // Validation số lượng trước khi chọn
                        if (quantity > maxQuantity) {
                            alert(
                                `❌ Số lượng không được vượt quá ${maxQuantity} sản phẩm có sẵn!\nVui lòng nhập số lượng từ 1 đến ${maxQuantity}.`);
                            quantityInput.focus();
                            quantityInput.select();
                            return;
                        }
                        if (quantity < 1 || isNaN(quantity)) {
                            alert('❌ Số lượng phải lớn hơn 0!');
                            quantityInput.focus();
                            quantityInput.select();
                            return;
                        }

                        const imagesArray = Array.from(images);

                        const deviceToAdd = {
                            id: device.id,
                            code: device.code,
                            name: device.name,
                            serial: device.serial || '',
                            quantity: quantity,
                            notes: notes,
                            images: imagesArray,
                            status: device.status,
                            fromWarranty: device.source !== 'warehouse',
                            type: device.type || 'product',
                            source: device.source || 'contract'
                        };

                        addDeviceToList(deviceToAdd);
                        
                        // Fetch vật tư cho cả thiết bị từ bảo hành và từ kho
                        if (device.source !== 'warehouse' && currentWarrantyCode) {
                            // Thiết bị từ bảo hành
                            fetchDeviceMaterials(device.id, device.code);
                        } else if (device.source === 'warehouse') {
                            // Thiết bị từ kho
                            fetchDeviceMaterialsFromWarehouse(device.code, device.type);
                        }

                        // Selected UI
                        row.style.backgroundColor = '#d1fae5';
                        this.innerHTML = '<i class="fas fa-undo mr-1"></i> Huỷ Chọn';
                        this.className =
                            'select-device-btn bg-green-100 text-green-600 px-2 py-1 rounded transition-colors text-xs';

                        // Disable reject while selected
                        const rejectBtn = row.querySelector('.reject-device-btn');
                        if (rejectBtn) {
                            rejectBtn.disabled = true;
                            rejectBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        }
                    });
                });

                document.querySelectorAll('.reject-device-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const device = JSON.parse(this.getAttribute('data-device'));
                        const row = this.closest('tr');

                        // Toggle off if already rejected
                        if (isDeviceRejected(device.id)) {
                            cancelRejectForDevice(device, row, this);
                            return;
                        }

                        currentRejectingDevice = {
                            element: this,
                            device: device,
                            row: row
                        };

                        // Hiển thị thông tin thiết bị trong modal
                        document.getElementById('reject-device-name').textContent = `${device.code} - ${device.name}`;
                        // Reset form (chỉ còn lý do)
                        document.getElementById('reject-reason').value = '';

                        // Hiển thị modal
                        document.getElementById('reject-device-modal').classList.remove('hidden');
                    });
                });
            }

            // Hàm lấy vật tư của thiết bị
            function fetchDeviceMaterials(deviceId, deviceCode) {
                console.log('🔍 Fetching materials for device:', {
                    deviceId: deviceId,
                    deviceCode: deviceCode,
                    warrantyCode: currentWarrantyCode
                });

                // Gọi API lấy vật tư của thiết bị
                const url =
                    `/api/repairs/device-materials?device_id=${deviceId}${currentWarrantyCode ? '&warranty_code=' + encodeURIComponent(currentWarrantyCode) : ''}`;
                console.log('🌐 API URL:', url);

                fetch(url, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('📦 API Response for device', deviceCode, ':', data);

                        if (data.success && data.materials && data.materials.length > 0) {
                            console.log(`✅ Found ${data.materials.length} materials for device ${deviceCode}`);

                            // Thêm vật tư vào danh sách
                            data.materials.forEach((material, index) => {
                                console.log(
                                    `📝 Processing material ${index + 1}/${data.materials.length}:`,
                                    material);

                                addMaterialToList({
                                    deviceId: deviceId,
                                    deviceCode: deviceCode,
                                    materialId: material.id,
                                    materialCode: material.code,
                                    materialName: material.name,
                                    materialSerial: material.serial || '',
                                    quantity: material.quantity || 1,
                                    currentSerials: material.current_serials || [],
                                    status: material.status || 'available'
                                });
                            });

                            // Hiển thị bảng vật tư
                            deviceMaterials.classList.remove('hidden');
                        } else {
                            console.log('⚠️ No materials found for device', deviceCode, '- API response:',
                            data);
                        }
                    })
                    .catch(error => {
                        console.error('❌ Error fetching device materials for', deviceCode, ':', error);
                        alert('Có lỗi xảy ra khi lấy danh sách vật tư thiết bị');
                    });
            }

            // Hàm lấy vật tư của thiết bị từ kho
            function fetchDeviceMaterialsFromWarehouse(deviceCode, deviceType) {
                console.log('🔍 Fetching materials for warehouse device:', {
                    deviceCode: deviceCode,
                    deviceType: deviceType
                });

                // Gọi API lấy vật tư của thiết bị từ kho
                const url = `/api/repairs/device-materials?device_code=${encodeURIComponent(deviceCode)}&device_type=${deviceType}`;
                console.log('🌐 API URL:', url);

                fetch(url, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('📦 API Response for warehouse device', deviceCode, ':', data);

                        if (data.success && data.materials && data.materials.length > 0) {
                            console.log(`✅ Found ${data.materials.length} materials for warehouse device ${deviceCode}`);

                            // Thêm vật tư vào danh sách
                            data.materials.forEach((material, index) => {
                                console.log(
                                    `📝 Processing warehouse material ${index + 1}/${data.materials.length}:`,
                                    material);

                                addMaterialToList({
                                    deviceId: 'warehouse_' + deviceCode + '_' + Date.now() + '_' + Math.random(),
                                    deviceCode: deviceCode,
                                    materialId: material.material_code, // Sử dụng material_code thay vì id
                                    materialCode: material.material_code,
                                    materialName: material.material_name,
                                    materialSerial: '',
                                    quantity: material.quantity || 1,
                                    currentSerials: [],
                                    status: 'available',
                                    unit: material.unit || 'cái',
                                    notes: material.notes || ''
                                });
                            });

                            // Hiển thị bảng vật tư
                            deviceMaterials.classList.remove('hidden');
                        } else {
                            console.log('⚠️ No materials found for warehouse device', deviceCode, '- API response:',
                            data);
                        }
                    })
                    .catch(error => {
                        console.error('❌ Error fetching warehouse device materials for', deviceCode, ':', error);
                        alert('Có lỗi xảy ra khi lấy danh sách vật tư thiết bị từ kho');
                    });
            }





            // Hàm thêm vật tư vào danh sách
            function addMaterialToList(material) {
                // Kiểm tra trùng lặp theo deviceId + materialCode (không dùng materialId vì có thể null)
                const exists = deviceMaterialsList.some(m =>
                    m.deviceId === material.deviceId && m.materialCode === material.materialCode
                );

                if (!exists) {
                    deviceMaterialsList.push(material);
                    console.log('✅ Added material to list:', {
                        deviceId: material.deviceId,
                        deviceCode: material.deviceCode,
                        materialCode: material.materialCode,
                        materialName: material.materialName,
                        serial: material.materialSerial
                    });
                    updateMaterialsDisplay();
                } else {
                    console.log('⚠️ Material already exists in list:', {
                        deviceId: material.deviceId,
                        materialCode: material.materialCode
                    });
                }
            }

            // Hàm cập nhật hiển thị bảng vật tư
            function updateMaterialsDisplay() {
                deviceMaterialsBody.innerHTML = '';

                deviceMaterialsList.forEach((material, index) => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';

                    // Hiển thị lịch sử thay thế nếu có
                    const replacementBadge = material.replacementHistory && material.replacementHistory
                        .length > 0 ?
                        `<span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full" title="Đã thay thế ${material.replacementHistory.length} lần">
                            <i class="fas fa-sync-alt mr-1"></i>${material.replacementHistory.length}
                           </span>` :
                        '';

                    const repairActive = !!material.repairNote;
                    const replaceActive = !!material.hasPendingReplacement;
                    const repairLabel = repairActive ? 'Huỷ Sửa chữa' : 'Sửa chữa';
                    const repairIcon = repairActive ? 'fa-undo' : 'fa-tools';
                    const repairBtnClass = repairActive ? 'bg-blue-200 text-blue-700' : 'bg-blue-100 text-blue-600';
                    const replaceLabel = replaceActive ? 'Huỷ Thay thế' : 'Thay thế';
                    const replaceIcon = replaceActive ? 'fa-undo' : 'fa-exchange-alt';
                    const replaceBtnClass = replaceActive ? 'bg-yellow-200 text-yellow-700' : 'bg-yellow-100 text-yellow-600';

                    row.innerHTML = `
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${material.materialCode}</td>
                        <td class="px-3 py-2 text-sm text-gray-700">${material.materialName}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${(material.materialSerial && material.materialSerial.trim()) ? material.materialSerial : 'N/A'}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">
                            <div class="flex items-center space-x-2">
                                <label class="inline-flex items-center space-x-1 text-xs mr-2">
                                    <input type="checkbox" class="material-damaged-checkbox" data-index="${index}" ${material.isDamaged ? 'checked' : ''} />
                                    <span>Hư hỏng</span>
                                </label>
                                <button type="button" class="material-repair-btn ${repairBtnClass} px-2 py-1 rounded hover:bg-blue-200 transition-colors text-xs" data-index="${index}">
                                    <i class="fas ${repairIcon} mr-1"></i> ${repairLabel}
                                </button>
                                <button type="button" class="material-replace-btn ${replaceBtnClass} px-2 py-1 rounded hover:bg-yellow-200 transition-colors text-xs" data-index="${index}">
                                    <i class="fas ${replaceIcon} mr-1"></i> ${replaceLabel}
                                </button>
                            </div>
                        </td>
                    `;
                    deviceMaterialsBody.appendChild(row);
                });
            }

                // Hàm thay thế vật tư
            window.replaceMaterial = function(index) {
                // Lấy dữ liệu mới nhất từ deviceMaterialsList
                const material = deviceMaterialsList[index];

                console.log('🚀 Opening replacement modal for material:', {
                    index: index,
                    materialCode: material.materialCode,
                    currentData: material
                });

                currentReplacingMaterial = {
                    ...material,
                    index: index
                };

                // Reset toàn bộ modal trước khi hiển thị
                resetReplaceModalState();

                // Hiển thị thông tin vật tư trong modal
                document.getElementById('replace-material-code').textContent = material.materialCode;
                document.getElementById('replace-material-name').textContent = material.materialName;

                // Xác định vật tư có serial hay không và phân tích cấu trúc serial
                const serialAnalysis = (() => {
                    const normalize = (s) => (s || '').toString().trim().toUpperCase();
                    const serialStr = normalize(material.materialSerial);
                    const current = Array.isArray(material.currentSerials)
                        ? material.currentSerials.map(normalize).filter(s => s && s !== 'N/A')
                        : [];
                    const originals = Array.isArray(material.originalSerials)
                        ? material.originalSerials.map(normalize).filter(s => s && s !== 'N/A')
                        : [];
                    const hasStr = serialStr && serialStr !== 'N/A';
                    const hasAnySerial = hasStr || current.length > 0 || originals.length > 0;
                    
                    // Phân tích cấu trúc: có bao nhiêu serial và bao nhiêu không serial
                    const totalQuantity = material.quantity || 1;
                    const serialCount = Math.max(current.length, originals.length, hasStr ? 1 : 0);
                    const nonSerialCount = totalQuantity - serialCount;
                    
                    return {
                        hasAnySerial,
                        totalQuantity,
                        serialCount,
                        nonSerialCount,
                        mixedType: serialCount > 0 && nonSerialCount > 0
                    };
                })();

                // Chuẩn hoá hiển thị các phần chọn serial theo phân tích
                const oldSerialWrap = document.getElementById('old-serial-selection');
                const oldStar = document.getElementById('old-serial-required-star');
                const newSerialWrap = document.getElementById('serial-selection');
                const newStar = document.getElementById('new-serial-required-star');
                const newSerialList = document.getElementById('serial-list');

                if (!serialAnalysis.hasAnySerial) {
                    // Vật tư không có serial: ẩn phần chọn serial, không bắt buộc
                    if (oldSerialWrap) oldSerialWrap.classList.add('hidden');
                    if (oldStar) oldStar.classList.add('hidden');
                    if (newSerialWrap) newSerialWrap.classList.remove('hidden');
                    if (newStar) newStar.classList.add('hidden');
                    newSerialList.innerHTML = '<p class="text-sm text-gray-500">N/A</p>';
                    currentReplacingMaterial.requiresSerial = false;
                } else if (serialAnalysis.mixedType) {
                    // Vật tư hỗn hợp (có cả serial và không serial)
                    if (oldSerialWrap) oldSerialWrap.classList.remove('hidden');
                    if (oldStar) oldStar.classList.add('hidden'); // Không bắt buộc chọn serial
                    if (newSerialWrap) newSerialWrap.classList.add('hidden');
                    if (newStar) newStar.classList.remove('hidden');
                    newSerialList.innerHTML = '';
                    currentReplacingMaterial.requiresSerial = false; // Có thể chọn hoặc không
                    currentReplacingMaterial.mixedType = true;
                } else {
                    // Vật tư chỉ có serial: bắt buộc chọn
                    if (oldSerialWrap) oldSerialWrap.classList.add('hidden'); // sẽ mở khi loadCurrentSerials
                    if (oldStar) oldStar.classList.remove('hidden');
                    if (newSerialWrap) newSerialWrap.classList.add('hidden');
                    if (newStar) newStar.classList.remove('hidden');
                    newSerialList.innerHTML = '';
                    currentReplacingMaterial.requiresSerial = true;
                }
                
                // Lưu thông tin phân tích để sử dụng sau
                currentReplacingMaterial.serialAnalysis = serialAnalysis;
                
                // Cập nhật label và dấu * theo loại vật tư
                const oldSerialLabel = document.getElementById('old-serial-label');
                const newSerialLabel = document.getElementById('new-serial-label');
                
                if (!serialAnalysis.hasAnySerial) {
                    // Vật tư không có serial
                    oldSerialLabel.textContent = 'Chọn serial cần thay thế';
                    newSerialLabel.textContent = 'Chọn serial mới';
                } else if (serialAnalysis.mixedType) {
                    // Vật tư hỗn hợp
                    oldSerialLabel.textContent = 'Chọn serial cần thay thế (tùy chọn)';
                    newSerialLabel.textContent = 'Chọn serial mới';
                } else {
                    // Vật tư chỉ có serial
                    oldSerialLabel.textContent = 'Chọn serial cần thay thế';
                    newSerialLabel.textContent = 'Chọn serial mới';
                }

                // Set giá trị max cho số lượng (dựa trên số lượng vật tư trong thành phẩm)
                const replaceQuantityInput = document.getElementById('replace-quantity');
                const maxQuantitySpan = document.getElementById('max-quantity');
                const actualQuantity = material
                    .quantity; // Lấy quantity của vật tư trong thành phẩm, không phải currentSerials.length

                replaceQuantityInput.max = actualQuantity;
                replaceQuantityInput.value = 1;
                maxQuantitySpan.textContent = actualQuantity;

                // Thêm validation cho input số lượng
                replaceQuantityInput.addEventListener('input', function() {
                    const value = parseInt(this.value);
                    if (value > actualQuantity) {
                        this.value = actualQuantity;
                        alert(
                            `Số lượng không được vượt quá ${actualQuantity} (số lượng vật tư trong thành phẩm)`
                        );
                    }
                    if (value < 1) {
                        this.value = 1;
                    }

                    // Cập nhật giới hạn chọn serial cũ khi thay đổi số lượng
                    updateOldSerialSelection(parseInt(this.value));

                    // Reset checkbox serial mới nếu đã chọn kho
                    const targetWarehouse = document.getElementById('target-warehouse').value;
                    if (targetWarehouse) {
                        loadAvailableSerials(material.materialCode, targetWarehouse, parseInt(this
                            .value));
                    }
                });

                // Load serial với trạng thái đã chọn trước đó
                let serialsToShow = [];
                let selectedOldSerials = [];
                let selectedNewSerials = [];

                console.log('🔍 DEBUG - Material data in modal:', {
                    index: index,
                    materialCode: material.materialCode,
                    materialSerial: material.materialSerial,
                    currentSerials: material.currentSerials,
                    replacementHistory: material.replacementHistory,
                    lastSelection: material.lastReplacementSelection,
                    fullMaterial: material
                });

                // Lấy trạng thái đã chọn trước đó (nếu có)
                if (material.lastReplacementSelection) {
                    selectedOldSerials = material.lastReplacementSelection.oldSerials || [];
                    selectedNewSerials = material.lastReplacementSelection.newSerials || [];
                }

                // Xây dựng danh sách serial để hiển thị cho việc thay thế:
                // 1. Bắt đầu với serial gốc ban đầu (trước khi có bất kỳ thay thế nào)
                if (material.originalSerials && material.originalSerials.length > 0) {
                    serialsToShow = [...material.originalSerials];
                } else {
                    // Lần đầu tiên, lưu serial hiện tại làm serial gốc
                    if (material.currentSerials && material.currentSerials.length > 0) {
                        serialsToShow = [...material.currentSerials];
                        material.originalSerials = [...material.currentSerials];
                    } else if (material.materialSerial && material.materialSerial.trim()) {
                        const originals = material.materialSerial.split(',').map(s => s.trim()).filter(s => s);
                        serialsToShow = [...originals];
                        material.originalSerials = [...originals];
                    }
                }

                // 2. Loại bỏ các serial mới đã được thay thế vào (không thể thay thế tiếp)
                if (selectedNewSerials.length > 0) {
                    serialsToShow = serialsToShow.filter(serial => !selectedNewSerials.includes(serial));
                }

                // 3. Giữ lại các serial hiện tại chưa được thay thế
                if (material.currentSerials && material.currentSerials.length > 0) {
                    const currentNotReplaced = material.currentSerials.filter(serial =>
                        !selectedNewSerials.includes(serial)
                    );
                    // Thêm các serial hiện tại chưa có trong danh sách
                    currentNotReplaced.forEach(serial => {
                        if (!serialsToShow.includes(serial)) {
                            serialsToShow.push(serial);
                        }
                    });
                }

                console.log('📌 Serials to show in modal:', serialsToShow);
                console.log('✅ Previously selected old serials:', selectedOldSerials);
                console.log('✅ Previously selected new serials:', selectedNewSerials);

                if (serialsToShow.length > 0) {
                    loadCurrentSerials(serialsToShow, selectedOldSerials);
                    // Bắt buộc chọn serial cũ (có kiểm tra tồn tại phần tử)
                    const oldStarEl = document.getElementById('old-serial-required-star');
                    if (oldStarEl) oldStarEl.classList.remove('hidden');
                } else {
                    console.log('⚠️ No serials to show! (vật tư không có serial)');
                    // Hiển thị thông tin không có serial: giữ phần chọn serial cũ hidden
                    const oldSel = document.getElementById('old-serial-selection');
                    if (oldSel) oldSel.classList.add('hidden');
                    const oldStarEl2 = document.getElementById('old-serial-required-star');
                    if (oldStarEl2) oldStarEl2.classList.add('hidden');
                }

                // Khôi phục kho lấy vật tư mới và danh sách serial (nếu có)
                setTimeout(() => {
                    if (material.lastReplacementSelection && material.lastReplacementSelection.targetWarehouse) {
                        const targetSelect = document.getElementById('target-warehouse');
                        if (targetSelect) {
                            targetSelect.value = material.lastReplacementSelection.targetWarehouse;
                            // Luôn reload danh sách serial theo kho đã chọn, dùng quantity hiện tại
                            const qty = parseInt(document.getElementById('replace-quantity').value) || 1;
                            loadAvailableSerials(
                                material.materialCode,
                                material.lastReplacementSelection.targetWarehouse,
                                qty,
                                selectedNewSerials
                            );
                        }
                    }
                }, 100);

                // Cập nhật số lượng cần chọn trong UI
                document.getElementById('required-old-serial-count').textContent = 1;
                document.getElementById('required-serial-count').textContent = 1;

                // Restore thông tin kho đã chọn trước đó (nếu có)
                if (material.lastReplacementSelection) {
                    if (material.lastReplacementSelection.sourceWarehouse) {
                        document.getElementById('source-warehouse').value = material.lastReplacementSelection
                            .sourceWarehouse;
                    }
                    if (material.lastReplacementSelection.notes) {
                        document.getElementById('replace-notes').value = material.lastReplacementSelection
                            .notes;
                    }
                }

                // Hiển thị modal
                replaceModal.classList.remove('hidden');
            };



            // Hàm thêm thiết bị vào danh sách
            function addDeviceToList(device) {
                // Kiểm tra trùng lặp
                if (selectedDevices.some(d => d.id === device.id)) {
                    alert('Thiết bị này đã được chọn');
                    return;
                }

                selectedDevices.push(device);
                updateSelectedDevicesDisplay();
            }

            // Toggle Sửa chữa vật tư: mở subtab inline với trường Ghi chú *; toggle lần nữa để huỷ
            document.addEventListener('click', function(e) {
                const btn = e.target.closest && e.target.closest('.material-repair-btn');
                if (btn) {
                    const index = parseInt(btn.getAttribute('data-index'));
                    const material = deviceMaterialsList[index];

                    // Nếu đang ở trạng thái Huỷ Sửa chữa -> bỏ ghi chú
                    if (material && material.repairNote !== undefined) {
                        // Xoá ghi chú và ẩn subtab nếu có
                        material.repairNote = undefined;
                        const sub = btn.closest('tr').nextElementSibling;
                        if (sub && sub.classList.contains('material-repair-subtab')) sub.remove();
                        btn.innerHTML = '<i class="fas fa-tools mr-1"></i> Sửa chữa';
                        btn.className = 'material-repair-btn bg-blue-100 text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition-colors text-xs';
                        return;
                    }

                    // Tạo subtab ngay dưới dòng vật tư (toggle nếu đã mở mà chưa lưu)
                    const tr = btn.closest('tr');
                    const existing = tr.nextElementSibling;
                    if (existing && existing.classList.contains('material-repair-subtab')) {
                        // Nếu chưa lưu ghi chú, click lần nữa sẽ hủy và ẩn subtab
                        if (material.repairNote === undefined) {
                            existing.remove();
                            btn.innerHTML = '<i class="fas fa-tools mr-1"></i> Sửa chữa';
                            btn.className = 'material-repair-btn bg-blue-100 text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition-colors text-xs';
                            return;
                        }
                        // Nếu đã lưu, case này được xử lý ở nhánh trên (Huỷ Sửa chữa)
                    }
                    const sub = document.createElement('tr');
                    sub.className = 'material-repair-subtab';
                    sub.innerHTML = `
                        <td colspan="4" class="px-3 py-2 bg-blue-50">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-700 required w-[100px]">Ghi chú:</label>
                                <input type="text" class="repair-note-input w-full border border-blue-200 rounded px-2 py-1 text-sm" placeholder="Nhập ghi chú sửa chữa bắt buộc" />
                                <button type="button" class="confirm-repair-note bg-blue-500 text-white px-3 py-1 rounded text-sm">Lưu</button>
                            </div>
                        </td>`;
                    tr.after(sub);

                    const input = sub.querySelector('.repair-note-input');
                    const confirmBtn = sub.querySelector('.confirm-repair-note');
                    confirmBtn.addEventListener('click', function() {
                        const noteVal = (input.value || '').trim();
                        if (!noteVal) {
                            alert('Ghi chú sửa chữa là bắt buộc.');
                            input.focus();
                            return;
                        }
                        material.repairNote = noteVal;
                        btn.innerHTML = '<i class="fas fa-undo mr-1"></i> Huỷ Sửa chữa';
                        btn.className = 'material-repair-btn bg-blue-200 text-blue-700 px-2 py-1 rounded transition-colors text-xs';
                        // Ẩn subtab sau khi lưu
                        const subRow = btn.closest('tr').nextElementSibling;
                        if (subRow && subRow.classList.contains('material-repair-subtab')) subRow.remove();
                    });
                }
            });

            // Gắn handler cho nút Thay thế mới (mở modal sẵn có)
            document.addEventListener('click', function(e) {
                const replaceBtn = e.target.closest && e.target.closest('.material-replace-btn');
                if (replaceBtn) {
                    const index = parseInt(replaceBtn.getAttribute('data-index'));
                    replaceMaterial(index);
                }
            });

            function isDeviceSelected(deviceId) {
                return selectedDevices.some(d => d.id === deviceId);
            }

            function removeDeviceById(deviceId) {
                const index = selectedDevices.findIndex(d => d.id === deviceId);
                if (index !== -1) {
                    // Gỡ vật tư liên quan
                    const removed = selectedDevices[index];
                    deviceMaterialsList = deviceMaterialsList.filter(m => m.deviceId != removed.id);
                    // Cập nhật lại bảng vật tư ngay sau khi lọc
                    if (typeof updateMaterialsDisplay === 'function') {
                        updateMaterialsDisplay();
                    }
                    if (deviceMaterialsList.length === 0) {
                        deviceMaterials.classList.add('hidden');
                    }
                    selectedDevices.splice(index, 1);
                    updateSelectedDevicesDisplay();
                }
            }

            // Hàm cập nhật hiển thị danh sách thiết bị đã chọn
            function updateSelectedDevicesDisplay() {
                selectedDevicesContainer.innerHTML = '';

                selectedDevices.forEach((device, index) => {
                    // Debug logging để kiểm tra dữ liệu device
                    console.log('🔍 Device data in updateSelectedDevicesDisplay:', {
                        id: device.id,
                        code: device.code,
                        name: device.name,
                        serial: device.serial,
                        quantity: device.quantity,
                        notes: device.notes,
                        imagesCount: device.images ? device.images.length : 0,
                        type: device.type || 'product'
                    });

                    const deviceDiv = document.createElement('div');
                    deviceDiv.className =
                        'flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200';

                    // Tạo HTML hiển thị hình ảnh nếu có
                    let imagesDisplay = '';
                    if (device.images && device.images.length > 0) {
                        imagesDisplay =
                            `<div class="text-xs text-blue-600 mt-1">📸 ${device.images.length} hình ảnh đã chọn</div>`;
                    }

                    // Determine device type label
                    const isGood = device.type === 'good';
                    const typeLabel = isGood ? 'Hàng hoá' : 'Thành phẩm';
                    
                    // Get source display text
                    const sourceDisplay = getSourceDisplayText(device.source || 'contract');

                    deviceDiv.innerHTML = `
                        <input type="hidden" name="selected_devices[]" value="${device.id}">
                        <input type="hidden" name="device_code[${device.id}]" value="${device.code}">
                        <input type="hidden" name="device_name[${device.id}]" value="${device.name}">
                        <input type="hidden" name="device_serial[${device.id}]" value="${device.serial || ''}">
                        <input type="hidden" name="device_quantity[${device.id}]" value="${device.quantity || 1}">
                        <input type="hidden" name="device_notes[${device.id}]" value="${device.notes || ''}">
                        <input type="hidden" name="device_type[${device.id}]" value="${device.type || 'product'}">
                        <input type="hidden" name="device_source[${device.id}]" value="${device.source || 'contract'}">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">
                                ${device.code} - ${device.name}
                            </div>
                            <div class="text-xs text-gray-500">
                                ${device.serial && device.serial.trim() ? 'Serial: ' + device.serial : 'Serial: N/A'}
                                ${device.quantity ? ' • Số lượng: ' + device.quantity : ''}
                                • Loại: ${typeLabel}
                                • Nguồn: ${sourceDisplay}
                            </div>
                            ${device.notes ? '<div class="text-xs text-gray-600 mt-1">💬 ' + device.notes + '</div>' : ''}
                            ${imagesDisplay}
                        </div>
                        <button type="button" class="text-red-500 hover:text-red-700 ml-2" onclick="removeDevice(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    selectedDevicesContainer.appendChild(deviceDiv);

                    // Không cần tạo hidden file inputs nữa vì dùng FormData trong submit
                });
            }

            // Hàm xóa thiết bị
            window.removeDevice = function(index) {
                const removedDevice = selectedDevices[index];
                console.log('Removing device:', removedDevice);
                console.log('Current materials before removal:', deviceMaterialsList);

                // Xóa vật tư liên quan đến thiết bị này
                const materialsBefore = deviceMaterialsList.length;
                deviceMaterialsList = deviceMaterialsList.filter(material => {
                    // So sánh deviceId với id của thiết bị bị xóa
                    const shouldKeep = material.deviceId != removedDevice.id;
                    console.log(
                        `Material ${material.materialCode} from device ${material.deviceCode} (deviceId: ${material.deviceId}): ${shouldKeep ? 'keeping' : 'removing'}`
                    );
                    return shouldKeep;
                });

                const materialsAfter = deviceMaterialsList.length;
                console.log(`Materials removed: ${materialsBefore - materialsAfter}`);
                console.log('Materials after removal:', deviceMaterialsList);

                // Cập nhật hiển thị vật tư
                updateMaterialsDisplay();

                // Ẩn bảng vật tư nếu không còn vật tư nào
                if (deviceMaterialsList.length === 0) {
                    deviceMaterials.classList.add('hidden');
                }

                // Reset trạng thái button trong bảng danh sách thiết bị (nếu có)
                resetDeviceRowState(removedDevice);

                // Xóa thiết bị khỏi danh sách đã chọn
                selectedDevices.splice(index, 1);
                updateSelectedDevicesDisplay();
            };

            // Hàm reset trạng thái hàng thiết bị trong bảng
            function resetDeviceRowState(device) {
                // Tìm hàng có data-device chứa id của thiết bị bị xóa
                const rows = document.querySelectorAll('#devices_list tr');
                rows.forEach(row => {
                    const selectBtn = row.querySelector('.select-device-btn');
                    const rejectBtn = row.querySelector('.reject-device-btn');

                    if (selectBtn && selectBtn.hasAttribute('data-device')) {
                        try {
                            const rowDevice = JSON.parse(selectBtn.getAttribute('data-device'));
                            if (rowDevice.id == device.id) {
                                // Reset trạng thái hàng
                                row.style.backgroundColor = '';

                                // Reset button chọn
                                selectBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Chọn';
                                selectBtn.disabled = false;
                                selectBtn.className =
                                    'select-device-btn bg-blue-100 text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition-colors text-xs';

                                // Reset button từ chối
                                if (rejectBtn) {
                                    rejectBtn.innerHTML = '<i class="fas fa-times mr-1"></i> Từ chối';
                                    rejectBtn.disabled = false;
                                    rejectBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                    rejectBtn.className =
                                        'reject-device-btn bg-red-100 text-red-600 px-2 py-1 rounded hover:bg-red-200 transition-colors text-xs';
                                }

                                console.log('Reset device row state for:', device.code);
                            }
                        } catch (e) {
                            console.error('Error parsing device data:', e);
                        }
                    }
                });
            }



            // Event listeners cho modal thay thế vật tư
            closeReplaceModal.addEventListener('click', function() {
                closeReplaceModalFunction();
            });

            cancelReplaceBtn.addEventListener('click', function() {
                closeReplaceModalFunction();
            });

            // Đóng modal khi click bên ngoài
            replaceModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeReplaceModalFunction();
                }
            });

            // Xử lý thay đổi kho đích
            document.getElementById('target-warehouse').addEventListener('change', function() {
                const warehouseId = this.value;
                const quantity = document.getElementById('replace-quantity').value;

                if (warehouseId && currentReplacingMaterial) {
                    loadAvailableSerials(currentReplacingMaterial.materialCode, warehouseId, quantity);
                } else {
                    document.getElementById('serial-selection').classList.add('hidden');
                }
            });

            // Xử lý thay đổi số lượng
            document.getElementById('replace-quantity').addEventListener('change', function() {
                const quantity = parseInt(this.value) || 1;
                const warehouseId = document.getElementById('target-warehouse').value;

                // Cập nhật số serial cần chọn cho cả serial cũ và mới
                document.getElementById('required-serial-count').textContent = quantity;
                document.getElementById('required-old-serial-count').textContent = quantity;

                // Cập nhật trạng thái checkbox serial cũ
                updateOldSerialSelection(quantity);

                if (warehouseId && currentReplacingMaterial) {
                    loadAvailableSerials(currentReplacingMaterial.materialCode, warehouseId, quantity);
                }
            });

            // Xử lý thay đổi kho lấy vật tư mới
            document.getElementById('target-warehouse').addEventListener('change', function() {
                const warehouseId = this.value;
                const quantity = parseInt(document.getElementById('replace-quantity').value) || 1;

                if (warehouseId && currentReplacingMaterial) {
                    // Hiển thị loading state
                    const serialList = document.getElementById('serial-list');
                    serialList.innerHTML =
                        '<p class="text-sm text-gray-500">🔄 Đang tải danh sách serial...</p>';
                    document.getElementById('serial-selection').classList.remove('hidden');

                    loadAvailableSerials(currentReplacingMaterial.materialCode, warehouseId, quantity);
                } else {
                    // Ẩn phần chọn serial nếu chưa chọn kho
                    document.getElementById('serial-selection').classList.add('hidden');
                }
            });

            confirmReplaceBtn.addEventListener('click', function() {
                processReplaceMaterial();
            });

            // Hàm đóng modal
            function closeReplaceModalFunction() {
                replaceModal.classList.add('hidden');
                currentReplacingMaterial = null;

                // Reset toàn bộ trạng thái modal khi đóng
                resetReplaceModalState();
            }

            // Hàm hiển thị serial hiện tại với trạng thái đã chọn
            function loadCurrentSerials(currentSerials, selectedSerials = []) {
                const oldSerialList = document.getElementById('old-serial-list');
                oldSerialList.innerHTML = '';

                if (currentSerials && currentSerials.length > 0) {
                    // Xử lý serial - nếu là string có dấu phẩy thì tách ra
                    let serialArray = [];
                    currentSerials.forEach(serial => {
                        if (typeof serial === 'string' && serial.includes(',')) {
                            // Tách serial có dấu phẩy: "111, 222" -> ["111", "222"]
                            const splitSerials = serial.split(',').map(s => s.trim()).filter(s => s);
                            serialArray.push(...splitSerials);
                        } else {
                            serialArray.push(serial);
                        }
                    });

                    // Loại bỏ serial trùng lặp và rỗng
                    serialArray = [...new Set(serialArray)].filter(s => s && s.trim());

                    console.log('📋 Loading serials for replacement:', serialArray);
                    console.log('✅ Previously selected serials:', selectedSerials);

                    // Hiển thị từng serial với trạng thái đã chọn
                    serialArray.forEach((serial, index) => {
                        const isSelected = selectedSerials.includes(serial);
                        console.log(
                            `🔍 Serial ${serial}: isSelected = ${isSelected} (from selectedSerials: [${selectedSerials.join(', ')}])`
                        );

                        const serialItem = document.createElement('div');
                        serialItem.className =
                            'flex items-center space-x-2 p-2 hover:bg-gray-50 rounded border border-gray-100';

                        serialItem.innerHTML = `
                            <input type="checkbox" class="old-serial-checkbox" value="${serial}" id="old-serial-${serial}-${index}" ${isSelected ? 'checked' : ''}>
                            <label for="old-serial-${serial}-${index}" class="flex-1 text-sm cursor-pointer">${serial}</label>
                        `;
                        oldSerialList.appendChild(serialItem);

                        // Debug: kiểm tra lại checkbox sau khi tạo
                        const checkbox = document.getElementById(`old-serial-${serial}-${index}`);
                        console.log(`✅ Checkbox created for ${serial}: checked = ${checkbox.checked}`);
                    });

                    document.getElementById('old-serial-selection').classList.remove('hidden');

                    // Thêm event listener để giới hạn số lượng checkbox được chọn
                    const quantity = parseInt(document.getElementById('replace-quantity').value);
                    updateOldSerialSelection(quantity);
                } else {
                    // Kiểm tra nếu là vật tư hỗn hợp (có cả serial và không serial)
                    const material = currentReplacingMaterial;
                    if (material && material.serialAnalysis && material.serialAnalysis.mixedType) {
                        oldSerialList.innerHTML = '<p class="text-sm text-gray-500">Vật tư này có cả serial và không serial. Bạn có thể chọn serial hoặc để trống.</p>';
                        document.getElementById('old-serial-selection').classList.remove('hidden');
                    } else {
                        oldSerialList.innerHTML = '<p class="text-sm text-gray-500">Không có thông tin serial</p>';
                        document.getElementById('old-serial-selection').classList.remove('hidden');
                    }
                }
            }

            // Hàm lấy badge trạng thái cho serial
            function getSerialStatusBadge(serial) {
                // Kiểm tra xem serial này có trong lịch sử thay thế không
                const material = currentReplacingMaterial;
                let isReplaced = false;

                if (material && material.replacementHistory) {
                    for (let history of material.replacementHistory) {
                        if (history.newSerials.includes(serial)) {
                            isReplaced = true;
                            break;
                        }
                    }
                }

                if (isReplaced) {
                    return `<span class="text-xs px-2 py-1 rounded bg-green-100 text-green-800">
                                <i class="fas fa-sync-alt mr-1"></i>Đã thay thế
                            </span>`;
                } else {
                    return `<span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-800">
                                Đang sử dụng
                            </span>`;
                }
            }

            // Hàm cập nhật trạng thái chọn serial cũ
            function updateOldSerialSelection(maxCount) {
                const oldCheckboxes = document.querySelectorAll('.old-serial-checkbox');

                // Lưu trạng thái checked hiện tại trước khi reset
                const checkedStates = {};
                oldCheckboxes.forEach(cb => {
                    checkedStates[cb.value] = cb.checked;
                });

                // Reset trạng thái (không reset checked)
                oldCheckboxes.forEach(cb => {
                    cb.disabled = false;
                    // Không reset cb.checked = false; để giữ trạng thái
                    // Remove existing event listeners
                    const newCheckbox = cb.cloneNode(true);
                    // Khôi phục trạng thái checked
                    newCheckbox.checked = checkedStates[cb.value] || false;
                    cb.replaceWith(newCheckbox);
                });

                // Re-query after cloning
                const newOldCheckboxes = document.querySelectorAll('.old-serial-checkbox');

                newOldCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const checkedCount = document.querySelectorAll(
                            '.old-serial-checkbox:checked').length;
                        const material = currentReplacingMaterial;
                        const analysis = material ? material.serialAnalysis : null;

                        // Xử lý logic khác nhau cho từng loại vật tư
                        if (analysis && analysis.mixedType) {
                            // Vật tư hỗn hợp: có thể chọn hoặc không
                            if (maxCount === 1) {
                                // Số lượng = 1: có thể chọn tối đa 1 hoặc không chọn
                                if (checkedCount >= 1) {
                                    newOldCheckboxes.forEach(cb => {
                                        if (!cb.checked) cb.disabled = true;
                                    });
                                } else {
                                    newOldCheckboxes.forEach(cb => {
                                        cb.disabled = false;
                                    });
                                }
                            } else {
                                // Số lượng > 1: bắt buộc chọn đủ
                                if (checkedCount >= maxCount) {
                                    newOldCheckboxes.forEach(cb => {
                                        if (!cb.checked) cb.disabled = true;
                                    });
                                } else {
                                    newOldCheckboxes.forEach(cb => {
                                        cb.disabled = false;
                                    });
                                }
                            }
                        } else {
                            // Vật tư chỉ có serial: bắt buộc chọn đủ
                            if (checkedCount >= maxCount) {
                                newOldCheckboxes.forEach(cb => {
                                    if (!cb.checked) cb.disabled = true;
                                });
                            } else {
                                newOldCheckboxes.forEach(cb => {
                                    cb.disabled = false;
                                });
                            }
                        }
                    });
                });
            }

            // Hàm load serial có sẵn
            function loadAvailableSerials(materialCode, warehouseId, requiredQuantity, selectedSerials = []) {
                console.log('Loading serials for:', {
                    materialCode,
                    warehouseId,
                    requiredQuantity,
                    selectedSerials
                });

                // Gọi API lấy serial có sẵn trong kho
                fetch(`/api/repairs/available-serials?material_code=${materialCode}&warehouse_id=${warehouseId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('API response:', data);
                        const serialList = document.getElementById('serial-list');
                        serialList.innerHTML = '';

                        if (data.success && data.serials && data.serials.length > 0) {
                            data.serials.forEach(serial => {
                                const isSelected = selectedSerials.includes(serial.serial);
                                const serialItem = document.createElement('div');
                                serialItem.className =
                                    'flex items-center space-x-2 p-2 hover:bg-gray-50 rounded';
                                serialItem.innerHTML = `
                                <input type="checkbox" class="serial-checkbox" value="${serial.serial}" 
                                       data-status="${serial.status}" 
                                       ${serial.status !== 'available' ? 'disabled' : ''} ${isSelected ? 'checked' : ''}>
                                <span class="flex-1 text-sm">${serial.serial}</span>
                                <span class="text-xs px-2 py-1 rounded ${getSerialStatusClass(serial.status)}">
                                    ${getSerialStatusText(serial.status)}
                                </span>
                            `;
                                serialList.appendChild(serialItem);
                            });

                            document.getElementById('serial-selection').classList.remove('hidden');
                            
                            // Kiểm tra loại vật tư để quyết định có bắt buộc chọn serial không
                            const material = currentReplacingMaterial;
                            const analysis = material ? material.serialAnalysis : null;
                            const newStarEl = document.getElementById('new-serial-required-star');
                            
                            if (analysis && analysis.mixedType && requiredQuantity === 1) {
                                // Vật tư hỗn hợp, số lượng = 1: không bắt buộc
                                if (newStarEl) newStarEl.classList.add('hidden');
                            } else {
                                // Bắt buộc chọn serial mới khi có danh sách
                                if (newStarEl) newStarEl.classList.remove('hidden');
                            }

                            // Thêm event listener để giới hạn số lượng checkbox được chọn
                            const checkboxes = serialList.querySelectorAll('.serial-checkbox:not([disabled])');
                            checkboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', function() {
                                    const checkedCount = serialList.querySelectorAll(
                                        '.serial-checkbox:checked').length;
                                    const maxCount = parseInt(requiredQuantity);

                                    if (checkedCount >= maxCount) {
                                        checkboxes.forEach(cb => {
                                            if (!cb.checked) cb.disabled = true;
                                        });
                                    } else {
                                        checkboxes.forEach(cb => {
                                            cb.disabled = false;
                                        });
                                    }
                                });
                            });
                        } else {
                            // Không có serial trong kho -> coi như vật tư không có serial
                            serialList.innerHTML = '<p class="text-sm text-gray-500">N/A</p>';
                            document.getElementById('serial-selection').classList.remove('hidden');
                            const nsEl = document.getElementById('new-serial-required-star');
                            if (nsEl) nsEl.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching available serials:', error);
                        const serialList = document.getElementById('serial-list');
                        serialList.innerHTML =
                            '<p class="text-sm text-red-500">Có lỗi xảy ra khi lấy danh sách serial</p>';
                        document.getElementById('serial-selection').classList.remove('hidden');
                    });
            }



            // Hàm lấy class cho trạng thái serial
            function getSerialStatusClass(status) {
                switch (status) {
                    case 'available':
                        return 'bg-green-100 text-green-800';
                    case 'exported':
                        return 'bg-red-100 text-red-800';
                    case 'reserved':
                        return 'bg-yellow-100 text-yellow-800';
                    default:
                        return 'bg-gray-100 text-gray-800';
                }
            }

            // Hàm lấy text cho trạng thái serial
            function getSerialStatusText(status) {
                switch (status) {
                    case 'available':
                        return 'Có sẵn';
                    case 'exported':
                        return 'Đã xuất';
                    case 'reserved':
                        return 'Đã đặt';
                    default:
                        return 'Không xác định';
                }
            }

            // Hàm xử lý thay thế vật tư
            async function processReplaceMaterial() {
                const sourceWarehouse = document.getElementById('source-warehouse').value;
                const targetWarehouse = document.getElementById('target-warehouse').value;
                const quantity = parseInt(document.getElementById('replace-quantity').value);
                const notes = document.getElementById('replace-notes').value;
                const selectedNewSerials = Array.from(document.querySelectorAll('.serial-checkbox:checked')).map(
                    cb => cb.value);
                const selectedOldSerials = Array.from(document.querySelectorAll('.old-serial-checkbox:checked'))
                    .map(cb => cb.value);

                // Validation
                if (!sourceWarehouse || !targetWarehouse) {
                    alert('Vui lòng chọn kho chuyển và kho lấy vật tư');
                    return;
                }

                // Logic validation theo loại vật tư
                const material = currentReplacingMaterial;
                const analysis = material.serialAnalysis;
                
                if (analysis.hasAnySerial && !analysis.mixedType) {
                    // Vật tư chỉ có serial: bắt buộc chọn đủ số lượng
                    if (selectedOldSerials.length !== quantity) {
                        alert(`Vui lòng chọn đúng ${quantity} serial vật tư cũ cần thay thế`);
                        return;
                    }
                    if (selectedNewSerials.length !== quantity) {
                        alert(`Vui lòng chọn đúng ${quantity} serial vật tư mới để thay thế`);
                        return;
                    }
                } else if (analysis.mixedType) {
                    // Vật tư hỗn hợp: kiểm tra logic đặc biệt
                    if (quantity === 1) {
                        // Số lượng = 1: có thể chọn serial hoặc không
                        if (selectedOldSerials.length > 1 || selectedNewSerials.length > 1) {
                            alert('Khi số lượng thay thế là 1, chỉ có thể chọn tối đa 1 serial');
                            return;
                        }
                        // Nếu chọn serial thì phải chọn cả cũ và mới
                        if ((selectedOldSerials.length > 0 && selectedNewSerials.length === 0) ||
                            (selectedOldSerials.length === 0 && selectedNewSerials.length > 0)) {
                            alert('Nếu chọn serial cũ thì phải chọn serial mới tương ứng');
                            return;
                        }
                    } else {
                        // Số lượng > 1: bắt buộc chọn đủ serial
                        if (selectedOldSerials.length !== quantity) {
                            alert(`Vui lòng chọn đúng ${quantity} serial vật tư cũ cần thay thế`);
                            return;
                        }
                        if (selectedNewSerials.length !== quantity) {
                            alert(`Vui lòng chọn đúng ${quantity} serial vật tư mới để thay thế`);
                            return;
                        }
                    }
                }
                // Vật tư không có serial: không cần validation serial

                // Validation số lượng không vượt quá số lượng vật tư trong thành phẩm
                const actualQuantity = currentReplacingMaterial.quantity; // Lấy quantity của vật tư
                if (quantity > actualQuantity) {
                    alert(
                        `Số lượng thay thế (${quantity}) không được vượt quá số lượng vật tư trong thành phẩm (${actualQuantity})`
                    );
                    return;
                }

                // Kiểm tra tồn kho trước khi xử lý thay thế
                const checkStockAvailability = async () => {
                    try {
                        // Nếu không chọn serial mới, chỉ kiểm tra số lượng tổng
                        const checkData = {
                            material_code: currentReplacingMaterial.materialCode,
                            warehouse_id: targetWarehouse,
                            required_quantity: quantity,
                            required_serials: selectedNewSerials
                        };
                        
                        const response = await fetch(`/api/repairs/check-stock-availability`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(checkData)
                        });
                        
                        const result = await response.json();
                        
                        if (!result.success) {
                            alert(`Lỗi kiểm tra tồn kho: ${result.message}`);
                            return false;
                        }
                        
                        if (!result.available) {
                            alert(`Không đủ tồn kho: ${result.message}`);
                            return false;
                        }
                        
                        return true;
                    } catch (error) {
                        console.error('Lỗi kiểm tra tồn kho:', error);
                        alert('Có lỗi xảy ra khi kiểm tra tồn kho');
                        return false;
                    }
                };
                
                // Thực hiện kiểm tra tồn kho
                const stockAvailable = await checkStockAvailability();
                if (!stockAvailable) {
                    return;
                }

                // Xử lý thay thế
                const replacementInfo = {
                    materialCode: currentReplacingMaterial.materialCode,
                    materialName: currentReplacingMaterial.materialName,
                    quantity: quantity,
                    oldSerials: selectedOldSerials,
                    newSerials: selectedNewSerials,
                    sourceWarehouse: sourceWarehouse,
                    targetWarehouse: targetWarehouse,
                    notes: notes,
                    deviceCode: currentReplacingMaterial.deviceCode
                };

                // Kiểm tra điều kiện serial bắt buộc theo tồn kho chi tiết nếu người dùng chưa chọn serial mới
                if (selectedNewSerials.length === 0) {
                    try {
                        const resp = await fetch(`/api/repairs/check-stock-availability`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                material_code: currentReplacingMaterial.materialCode,
                                warehouse_id: targetWarehouse,
                                required_quantity: quantity,
                                required_serials: []
                            })
                        });
                        const res = await resp.json();
                        if (res && res.success && res.details) {
                            const serialStock = parseInt(res.details.serial_stock || 0);
                            const nonSerialStock = parseInt(res.details.non_serial_stock || 0);
                            // Nếu số lượng yêu cầu vượt quá tồn không-serial => phải chọn đủ phần vượt bằng serial
                            if (quantity > nonSerialStock) {
                                const mustPick = quantity - nonSerialStock;
                                alert(`Kho chỉ còn ${nonSerialStock} không-serial. Vui lòng chọn thêm ${mustPick} serial trong danh sách serial mới.`);
                                return;
                            }
                        }
                    } catch (e) {
                        console.error('Stock detail check failed:', e);
                        // fallback: cho tiếp tục nếu không lấy được chi tiết, vì bước check tổng đã OK
                    }
                }

                // Lưu trạng thái đã chọn vào material ngay lập tức
                const materialIndex = currentReplacingMaterial.index;
                const lastSelection = {
                    oldSerials: selectedOldSerials,
                    newSerials: selectedNewSerials,
                    sourceWarehouse: sourceWarehouse,
                    targetWarehouse: targetWarehouse,
                    notes: notes
                };

                // Cập nhật trạng thái đã chọn vào deviceMaterialsList
                deviceMaterialsList[materialIndex] = {
                    ...deviceMaterialsList[materialIndex],
                    lastReplacementSelection: lastSelection
                };

                console.log('💾 Saved selection state:', lastSelection);

                // Tránh trùng lặp: nếu đã có thay thế cho cùng device_code + material_code trong phiên hiện tại,
                // thì cập nhật lại bằng cách loại bỏ bản cũ trước khi thêm bản mới
                materialReplacements = materialReplacements.filter(r =>
                    !(r.device_code === replacementInfo.deviceCode && r.material_code === replacementInfo.materialCode)
                );

                // Lưu thông tin thay thế vào mảng để gửi cùng form
                materialReplacements.push({
                    device_code: replacementInfo.deviceCode,
                    material_code: replacementInfo.materialCode,
                    material_name: replacementInfo.materialName,
                    old_serials: replacementInfo.oldSerials,
                    new_serials: replacementInfo.newSerials,
                    quantity: replacementInfo.quantity,
                    source_warehouse_id: replacementInfo.sourceWarehouse,
                    target_warehouse_id: replacementInfo.targetWarehouse,
                    notes: replacementInfo.notes || ''
                });

                // Cập nhật vật tư trong danh sách hiển thị
                updateMaterialAfterReplacement(replacementInfo);

                // Đóng modal mà không reset state
                replaceModal.classList.add('hidden');
                currentReplacingMaterial = null;
            }

            // Hàm cập nhật vật tư sau khi thay thế
            function updateMaterialAfterReplacement(replacementInfo) {
                const materialIndex = currentReplacingMaterial.index;
                const material = deviceMaterialsList[materialIndex];

                console.log('🔧 BEFORE replacement:');
                console.log('- Index:', materialIndex);
                console.log('- currentSerials:', material.currentSerials);
                console.log('- materialSerial:', material.materialSerial);
                console.log('- Replacement info:', replacementInfo);

                // Lấy danh sách serial hiện tại
                let currentSerialList = [];
                if (material.materialSerial && material.materialSerial.trim()) {
                    currentSerialList = material.materialSerial.split(',').map(s => s.trim()).filter(s => s);
                } else if (material.currentSerials && material.currentSerials.length > 0) {
                    currentSerialList = [...material.currentSerials];
                }

                console.log('📋 Current serial list before replacement:', currentSerialList);

                // Xóa serial cũ được thay thế
                replacementInfo.oldSerials.forEach(oldSerial => {
                    const index = currentSerialList.indexOf(oldSerial);
                    if (index > -1) {
                        console.log(`❌ Removing old serial: ${oldSerial} at index ${index}`);
                        currentSerialList.splice(index, 1);
                    }
                });

                // Thêm serial mới
                replacementInfo.newSerials.forEach(newSerial => {
                    console.log(`✅ Adding new serial: ${newSerial}`);
                    currentSerialList.push(newSerial);
                });

                // Cập nhật cả hai nguồn dữ liệu
                material.currentSerials = [...currentSerialList];
                material.materialSerial = currentSerialList.join(', ');

                console.log('🔧 AFTER replacement:');
                console.log('- currentSerials:', material.currentSerials);
                console.log('- materialSerial:', material.materialSerial);

                // Lưu thông tin thay thế vào material trước khi cập nhật
                if (!material.replacementHistory) {
                    material.replacementHistory = [];
                }

                material.replacementHistory.push({
                    timestamp: new Date().toLocaleString('vi-VN'),
                    oldSerials: [...replacementInfo.oldSerials],
                    newSerials: [...replacementInfo.newSerials],
                    sourceWarehouse: getWarehouseName(replacementInfo.sourceWarehouse),
                    targetWarehouse: getWarehouseName(replacementInfo.targetWarehouse),
                    notes: replacementInfo.notes
                });

                // Giữ nguyên lastReplacementSelection đã lưu trước đó
                const existingSelection = material.lastReplacementSelection || null;

                // Cập nhật lại đối tượng trong danh sách - QUAN TRỌNG!
                deviceMaterialsList[materialIndex] = {
                    ...material,
                    currentSerials: [...currentSerialList],
                    materialSerial: currentSerialList.join(', '),
                    replacementHistory: [...material.replacementHistory],
                    lastReplacementSelection: existingSelection // Giữ nguyên trạng thái đã lưu
                };

                console.log('💾 Updated material in deviceMaterialsList:', deviceMaterialsList[materialIndex]);

                // Cập nhật lại bảng hiển thị
                updateMaterialsDisplay();

                // Chỉ đóng modal mà không reset (để giữ trạng thái cho lần mở sau)
                replaceModal.classList.add('hidden');
                currentReplacingMaterial = null;

                // Đồng bộ UI: nếu có bảng "Linh kiện đã thay thế" hiện trên trang, đảm bảo không nhân đôi dòng
                // bằng cách re-render từ materialReplacements (nếu có hàm hiển thị tương ứng)
                if (typeof renderReplacedMaterialsTable === 'function') {
                    try { renderReplacedMaterialsTable(materialReplacements); } catch (e) { /* ignore */ }
                }

                console.log('🔄 Replacement completed and modal closed (state preserved)');
            }

            // Hàm reset trạng thái modal thay thế
            function resetReplaceModalState() {
                document.getElementById('source-warehouse').value = '';
                document.getElementById('target-warehouse').value = '';
                document.getElementById('replace-quantity').value = 1;
                document.getElementById('replace-notes').value = '';
                document.getElementById('serial-selection').classList.add('hidden');
                document.getElementById('old-serial-selection').classList.add('hidden');

                // Reset danh sách serial
                document.getElementById('old-serial-list').innerHTML = '';
                document.getElementById('serial-list').innerHTML = '';
            }

            // Hàm lấy tên kho
            function getWarehouseName(warehouseId) {
                const warehouses = {
                    '1': 'Kho chính / Kho hỏng',
                    '2': 'Kho phụ / Kho bảo trì',
                    '3': 'Kho linh kiện / Kho tái chế',
                    '4': 'Kho bảo hành / Kho kiểm định'
                };
                return warehouses[warehouseId] || 'Không xác định';
            }

            // Hàm hiển thị lịch sử thay thế vật tư đã bị xóa

            // Xử lý modal từ chối thiết bị
            const rejectModal = document.getElementById('reject-device-modal');
            const closeRejectModal = document.getElementById('close-reject-modal');
            const cancelRejectBtn = document.getElementById('cancel-reject-btn');
            const confirmRejectBtn = document.getElementById('confirm-reject-btn');

            closeRejectModal.addEventListener('click', function() {
                closeRejectModalFunction();
            });

            cancelRejectBtn.addEventListener('click', function() {
                closeRejectModalFunction();
            });

            // Đóng modal khi click bên ngoài
            rejectModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeRejectModalFunction();
                }
            });

            confirmRejectBtn.addEventListener('click', function() {
                const reason = document.getElementById('reject-reason').value.trim();
                // Theo yêu cầu: chỉ yêu cầu lý do, bỏ kho/số lượng
                const warehouseId = null;
                const rejectQuantity = 1;
                const totalQuantity = 1;

                if (!reason) {
                    alert('Vui lòng nhập lý do từ chối thiết bị');
                    document.getElementById('reject-reason').focus();
                    return;
                }

                // Bỏ các kiểm tra kho/số lượng để phù hợp yêu cầu UI

                // Thực hiện từ chối thiết bị
                processRejectDevice(reason, warehouseId, rejectQuantity);
            });

            function closeRejectModalFunction() {
                rejectModal.classList.add('hidden');
                currentRejectingDevice = null;
            }

            function processRejectDevice(reason, warehouseId, rejectQuantity) {
                const device = currentRejectingDevice.device;
                const row = currentRejectingDevice.row;
                const element = currentRejectingDevice.element;
                const totalQuantity = device.quantity || 1;

                // Thêm vào danh sách đã từ chối
                rejectedDevices.push({
                    id: device.id,
                    code: device.code,
                    name: device.name,
                    quantity: rejectQuantity,
                    total_quantity: totalQuantity,
                    reason: reason,
                    warehouse_id: warehouseId,
                    rejected_at: new Date().toISOString()
                });

                // Cập nhật giao diện dựa trên số lượng từ chối
                if (rejectQuantity >= totalQuantity) {
                    // Từ chối toàn bộ - đánh dấu đỏ và đổi nút thành Huỷ Từ chối
                    row.style.backgroundColor = '#fee2e2';
                    element.innerHTML = '<i class="fas fa-undo mr-1"></i> Huỷ Từ chối';
                    element.disabled = false;
                    element.className =
                        'reject-device-btn bg-red-200 text-red-700 px-2 py-1 rounded transition-colors text-xs';

                    // Vô hiệu hóa button chọn trong khi đang bị từ chối
                    const selectBtn = row.querySelector('.select-device-btn');
                    if (selectBtn) {
                        selectBtn.disabled = true;
                        selectBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                } else {
                    // Từ chối một phần - đánh dấu vàng và đổi nút thành Huỷ Từ chối
                    row.style.backgroundColor = '#fef3c7';
                    element.innerHTML = '<i class="fas fa-undo mr-1"></i> Huỷ Từ chối';
                    element.disabled = false;
                    element.className =
                        'reject-device-btn bg-yellow-200 text-yellow-800 px-2 py-1 rounded transition-colors text-xs';

                    // Cập nhật số lượng hiển thị trong bảng
                    const quantityCell = row.querySelector('.device-quantity');
                    if (quantityCell) {
                        quantityCell.max = totalQuantity - rejectQuantity;
                        quantityCell.value = Math.min(quantityCell.value, totalQuantity - rejectQuantity);
                    }
                }

                // Đóng modal
                closeRejectModalFunction();

                // Hiển thị thông báo thành công
                alert(`✅ Đã từ chối thiết bị: ${device.code} - ${device.name}\n📝 Lý do: ${reason}`);
            }

            function isDeviceRejected(deviceId) {
                return rejectedDevices.some(d => d.id === deviceId);
            }

            function cancelRejectForDevice(device, row, btnEl) {
                // Remove from rejectedDevices
                rejectedDevices = rejectedDevices.filter(d => d.id !== device.id);
                // Reset UI
                row.style.backgroundColor = '';
                btnEl.disabled = false;
                btnEl.className =
                    'reject-device-btn bg-red-100 text-red-600 px-2 py-1 rounded hover:bg-red-200 transition-colors text-xs';
                btnEl.innerHTML = '<i class="fas fa-times mr-1"></i> Từ chối';
                // Re-enable select
                const selectBtn = row.querySelector('.select-device-btn');
                if (selectBtn) {
                    selectBtn.disabled = false;
                    selectBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            // Validate form before submit
            document.querySelector('form').addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                // Kiểm tra phải có ít nhất một thiết bị được chọn HOẶC từ chối
                if (selectedDevices.length === 0 && rejectedDevices.length === 0) {
                    alert('Vui lòng chọn hoặc từ chối ít nhất một thiết bị');
                    return false;
                }

                // Hiển thị thông báo xác nhận
                const confirmMsg = `🔍 TỔNG KẾT PHIẾU SỬA CHỮA:\n\n` +
                    `✅ Thiết bị đã chọn: ${selectedDevices.length}\n` +
                    `❌ Thiết bị đã từ chối: ${rejectedDevices.length}\n` +
                    `🔧 Vật tư đã thay thế: ${materialReplacements.length}\n\n` +
                    `Bạn có muốn lưu phiếu sửa chữa này không?`;

                if (!confirm(confirmMsg)) {
                    return false;
                }

                // Tạo FormData để gửi dữ liệu và files
                const formData = new FormData();

                // Thêm các field cơ bản từ form
                const formInputs = this.querySelectorAll('input, select, textarea');
                console.log('🔍 Form has', formInputs.length, 'inputs total');

                formInputs.forEach(input => {
                    if (input.type === 'file') {
                        console.log('⏭️ Skipping file input:', input.name);
                        return; // Skip file inputs, sẽ xử lý riêng
                    }
                    if (input.name && input.value) {
                        console.log('📝 Adding form input:', input.name, '=', input.value);
                        formData.append(input.name, input.value);
                    } else if (input.name) {
                        console.log('⚠️ Empty input:', input.name, '(value:', input.value, ')');
                    }
                });

                // Thêm CSRF token
                formData.append('_token', csrfToken);

                // Debug: Kiểm tra selectedDevices trước khi gửi
                console.log('🔍 selectedDevices before submit:', selectedDevices);
                console.log('🔍 selectedDevices IDs:', selectedDevices.map(d => d.id));

                // Kiểm tra duplicate IDs
                const deviceIds = selectedDevices.map(d => d.id);
                const uniqueIds = [...new Set(deviceIds)];
                if (deviceIds.length !== uniqueIds.length) {
                    console.error('❌ DUPLICATE DEVICE IDs DETECTED!');
                    console.error('All IDs:', deviceIds);
                    console.error('Unique IDs:', uniqueIds);
                    alert('❌ Có thiết bị bị trùng lặp! Vui lòng refresh trang và thử lại.');
                    return;
                }

                // Thêm thông tin thiết bị đã chọn và hình ảnh
                selectedDevices.forEach((device, index) => {
                    console.log(`🔍 Processing device ${index}: ${device.id} - ${device.code}`);

                    // Escape device ID để tránh conflict với Laravel input parsing
                    const deviceKey = device.id.replace(/\./g, '_DOT_').replace(/\[/g, '_LB_')
                        .replace(/\]/g, '_RB_');
                    console.log(`🔑 Original device ID: ${device.id}`);
                    console.log(`🔑 Escaped device key: ${deviceKey}`);

                    formData.append('selected_devices[]', device.id);
                    formData.append(`device_code[${deviceKey}]`, device.code);
                    formData.append(`device_name[${deviceKey}]`, device.name);
                    formData.append(`device_serial[${deviceKey}]`, device.serial || '');
                    formData.append(`device_quantity[${deviceKey}]`, device.quantity || 1);
                    formData.append(`device_notes[${deviceKey}]`, device.notes || '');

                    console.log(`📝 Added device data with key ${deviceKey}:`, {
                        code: device.code,
                        name: device.name,
                        serial: device.serial,
                        quantity: device.quantity,
                        notes: device.notes
                    });

                    // Thêm hình ảnh thiết bị với escaped key
                    if (device.images && device.images.length > 0) {
                        for (let i = 0; i < device.images.length; i++) {
                            const imageKey = `device_images[${deviceKey}][]`;
                            formData.append(imageKey, device.images[i]);
                            console.log(
                                `📸 Adding image ${i} for device ${device.code} with key: ${imageKey}`,
                                device.images[i]);
                        }
                        console.log(
                            `📸 Added ${device.images.length} images for device ${device.code}`);
                    } else {
                        console.log(`❌ No images for device ${device.code}. Images:`, device
                        .images);
                    }
                });

                // Thêm thông tin thiết bị đã từ chối
                if (rejectedDevices.length > 0) {
                    formData.append('rejected_devices', JSON.stringify(rejectedDevices));
                }

                // Thêm thông tin thay thế vật tư
                if (materialReplacements.length > 0) {
                    formData.append('material_replacements', JSON.stringify(materialReplacements));
                }

                // Thêm thông tin sửa chữa vật tư (theo checkbox Hư hỏng hoặc có ghi chú)
                const damagedMaterialsPayload = [];
                deviceMaterialsList.forEach(m => {
                    if ((m.isDamaged === true) || (m.repairNote && m.repairNote.trim())) {
                        damagedMaterialsPayload.push({
                            device_code: m.deviceCode,
                            material_code: m.materialCode,
                            material_name: m.materialName,
                            serial: (m.materialSerial && m.materialSerial.trim()) ? m.materialSerial : null,
                            damage_description: (m.repairNote && m.repairNote.trim()) ? m.repairNote : ''
                        });
                    }
                });
                if (damagedMaterialsPayload.length > 0) {
                    formData.append('damaged_materials', JSON.stringify(damagedMaterialsPayload));
                }

                // Thêm repair photos từ input file
                const repairPhotosInput = document.querySelector('input[name="repair_photos[]"]');
                if (repairPhotosInput && repairPhotosInput.files.length > 0) {
                    for (let i = 0; i < repairPhotosInput.files.length; i++) {
                        formData.append('repair_photos[]', repairPhotosInput.files[i]);
                    }
                }

                // Debug: Log form data
                console.log('📤 Sending FormData with:');
                const formDataEntries = [];
                for (let pair of formData.entries()) {
                    if (pair[1] instanceof File) {
                        console.log(pair[0] + ': [FILE] ' + pair[1].name);
                        formDataEntries.push([pair[0], '[FILE] ' + pair[1].name]);
                    } else {
                        console.log(pair[0] + ': ' + pair[1]);
                        formDataEntries.push([pair[0], pair[1]]);
                    }
                }

                // Group by key to check for duplicates
                const groupedEntries = {};
                formDataEntries.forEach(([key, value]) => {
                    if (!groupedEntries[key]) {
                        groupedEntries[key] = [];
                    }
                    groupedEntries[key].push(value);
                });

                // Log grouped entries and highlight duplicates
                console.log('📋 Grouped FormData entries:');
                Object.keys(groupedEntries).forEach(key => {
                    const values = groupedEntries[key];
                    if (values.length > 1) {
                        console.warn(`⚠️ DUPLICATE KEY: ${key} has ${values.length} values:`,
                            values);
                    } else {
                        console.log(`✅ ${key}:`, values[0]);
                    }
                });

                // Gửi request
                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                            // Không set Content-Type, để browser tự động set với boundary
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            // Redirect về trang danh sách
                            window.location.href = '/repairs';
                        } else {
                            throw new Error('Network response was not ok');
                        }
                    })
                    .catch(error => {
                        console.error('❌ Error submitting form:', error);
                        alert('Có lỗi xảy ra khi lưu phiếu sửa chữa. Vui lòng thử lại.');
                    });
            });
        });
    </script>
</body>

</html>
