<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu sửa chữa - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/date-format.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <a href="{{ route('repairs.show', $repair->id) }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu sửa chữa - {{ $repair->repair_code }}</h1>
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

            <!-- Success Messages -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('repairs.update', $repair->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Thông tin bảo hành -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                        Thông tin bảo hành
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="warranty_code" class="block text-sm font-medium text-gray-700 mb-1">Mã bảo
                                hành</label>
                            <input type="text" id="warranty_code" name="warranty_code"
                                value="{{ $repair->warranty_code }}" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Khách
                                hàng</label>
                            <input type="text" id="customer_name"
                                value="{{ $repair->warranty ? $repair->warranty->customer_name : 'N/A' }}" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                    </div>

                    <!-- Hiển thị thiết bị trong phiếu sửa chữa -->
                    @if ($repair->repairItems->count() > 0)
                        <div class="mt-4">
                            <h3 class="text-md font-semibold text-gray-800 mb-3">Thiết bị trong phiếu sửa chữa</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="space-y-2">
                                    @foreach ($repair->repairItems as $item)
                                        @php
                                            $canEdit = $editableDevices[$item->device_code] ?? true;
                                            $isRejected = $item->device_status === 'rejected';
                                            $hasReplacements =
                                                $repair->materialReplacements
                                                    ->where('device_code', $item->device_code)
                                                    ->count() > 0;
                                        @endphp
                                        <div
                                            class="flex items-center justify-between p-3 bg-white rounded border {{ $isRejected ? 'border-red-200 bg-red-50' : ($hasReplacements ? 'border-yellow-200 bg-yellow-50' : '') }}">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900">{{ $item->device_code }} -
                                                    {{ $item->device_name }}</div>
                                                <div class="text-sm text-gray-600">Serial:
                                                    {{ $item->device_serial ?: 'Không có' }}</div>
                                                @if (!$canEdit)
                                                    <div class="text-xs mt-1">
                                                        @if ($isRejected)
                                                            <span class="text-red-600"><i class="fas fa-ban mr-1"></i>Đã
                                                                từ chối: {{ $item->rejected_reason }}</span>
                                                        @elseif($hasReplacements)
                                                            <span class="text-yellow-600"><i
                                                                    class="fas fa-exchange-alt mr-1"></i>Đã thay thế vật
                                                                tư</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $item->device_status === 'rejected'
                                                    ? 'bg-red-100 text-red-800'
                                                    : ($item->device_status === 'selected'
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $item->device_status_label }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Thông tin sửa chữa -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tools text-blue-500 mr-2"></i>
                        Thông tin sửa chữa
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="repair_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại
                                sửa chữa</label>
                            <select id="repair_type" name="repair_type" required disabled
                                class="w-full border border-gray-300 bg-gray-50 cursor-not-allowed rounded-lg px-3 py-2">
                                <option value="maintenance"
                                    {{ $repair->repair_type === 'maintenance' ? 'selected' : '' }}>Bảo trì định kỳ
                                </option>
                                <option value="repair" {{ $repair->repair_type === 'repair' ? 'selected' : '' }}>Sửa
                                    chữa lỗi</option>
                                <option value="replacement"
                                    {{ $repair->repair_type === 'replacement' ? 'selected' : '' }}>Thay thế linh kiện
                                </option>
                                <option value="upgrade" {{ $repair->repair_type === 'upgrade' ? 'selected' : '' }}>Nâng
                                    cấp</option>
                                <option value="other" {{ $repair->repair_type === 'other' ? 'selected' : '' }}>Khác
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="repair_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày
                                sửa chữa</label>
                            <input type="text" id="repair_date" name="repair_date"
                                value="{{ $repair->repair_date->format('d/m/Y') }}" required disabled
                                class="w-full border border-gray-300 bg-gray-50 cursor-not-allowed rounded-lg px-3 py-2 date-input">
                            <input type="hidden" name="repair_date" value="{{ $repair->repair_date->format('Y-m-d') }}">
                        </div>
                        <div>
                            <label for="technician_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ
                                thuật viên</label>
                            <select id="technician_id" name="technician_id" required disabled
                                class="w-full border border-gray-300 bg-gray-50 cursor-not-allowed rounded-lg px-3 py-2">
                                <option value="">-- Chọn kỹ thuật viên --</option>
                                @foreach (\App\Models\Employee::where('status', 'active')->get() as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ $repair->technician_id == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                            <select id="status" name="status" {{ $repair->status === 'completed' ? 'disabled' : '' }}
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $repair->status === 'completed' ? 'bg-gray-50 cursor-not-allowed' : '' }}">
                                <option value="in_progress" {{ $repair->status === 'in_progress' ? 'selected' : '' }}>Đang xử lý</option>
                                <option value="completed" {{ $repair->status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            </select>
                            @if ($repair->status === 'completed')
                                <input type="hidden" name="status" value="completed">
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="repair_description" class="block text-sm font-medium text-gray-700 mb-1 required">Mô
                            tả sửa chữa</label>
                        <textarea id="repair_description" name="repair_description" rows="3" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập mô tả chi tiết về vấn đề và cách sửa chữa">{{ $repair->repair_description }}</textarea>
                    </div>
                </div>

                <!-- Chi tiết vật tư từ thiết bị -->
                @if ($repair->repairItems->count() > 0)
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6 {{ $repair->status === 'completed' ? 'opacity-60' : '' }}">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-cogs text-blue-500 mr-2"></i>
                            Chi tiết vật tư từ thiết bị
                        </h2>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã thiết bị
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã vật tư
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên vật tư
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Serial vật tư
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Số lượng
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thao tác
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="device_materials_body">
                                    <!-- Dữ liệu sẽ được load qua JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        @if ($repair->status === 'completed')
                            <p class="mt-2 text-xs text-gray-500">Phiếu đã hoàn thành: không thể thao tác sửa chữa/thay thế.</p>
                        @endif
                    </div>
                @endif

                <!-- Đính kèm & Ghi chú -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-paperclip text-blue-500 mr-2"></i>
                        Đính kèm & Ghi chú
                    </h2>

                    <div class="mb-4">
                        <label for="repair_photos" class="block text-sm font-medium text-gray-700 mb-1">Hình
                            ảnh</label>

                        <!-- Hiển thị hình ảnh đã có -->
                        @if ($repair->repair_photos && count($repair->repair_photos) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-3">
                                @foreach ($repair->repair_photos as $index => $photo)
                                    <div class="photo-item border border-gray-200 rounded-lg overflow-hidden relative">
                                        <img src="{{ asset('storage/' . $photo) }}"
                                            alt="Hình ảnh sửa chữa {{ $index + 1 }}"
                                            class="w-full h-32 object-cover">
                                        <div class="p-2 bg-gray-50 flex justify-between items-center">
                                            <p class="text-sm text-gray-600">Hình {{ $index + 1 }}</p>
                                            <button type="button" class="text-red-500 hover:text-red-700"
                                                onclick="removePhoto({{ $index }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Thêm hình ảnh mới -->
                        <input type="file" id="repair_photos" name="repair_photos[]" multiple accept="image/*"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Tối đa 5 ảnh, kích thước mỗi ảnh không quá 2MB</p>
                    </div>

                    <div>
                        <label for="repair_notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="repair_notes" name="repair_notes" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập ghi chú bổ sung">{{ $repair->repair_notes }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('repairs.show', $repair->id) }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Cập nhật
                    </button>
                </div>

                <!-- Hidden fields for material replacements -->
                <input type="hidden" id="material_replacements_data" name="material_replacements" value="">
                <input type="hidden" id="damaged_materials_data" name="damaged_materials" value="">
                <!-- Hidden field for photos to delete -->
                <input type="hidden" id="photos_to_delete" name="photos_to_delete" value="">
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
                        @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
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
                        trong thành phẩm)</p>
                </div>

                <!-- Chọn serial vật tư cũ cần thay thế -->
                <div id="old-serial-selection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Chọn serial cần thay thế <span class="text-red-500">*</span>
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
                        @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Serial vật tư mới -->
                <div id="serial-selection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Chọn serial mới <span class="text-red-500">*</span>
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
                        placeholder="Nhập ghi chú về việc thay thế vật tư..."></textarea>
                </div>
            </div>

            <div class="flex space-x-3 mt-6">
                <button type="button" id="cancel-replace-btn"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Hủy
                </button>
                <button type="button" id="confirm-replace-btn"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    Xác nhận thay thế
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let deviceMaterialsList = [];
        let materialReplacements = [];
        let currentReplacingMaterial = null;
        let damagedMaterials = [];
        let existingReplacements = [];
        let originalMaterialSerials = {}; // Store original serials before any replacements
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Load existing data from backend - using simpler approach
        const existingDamagedMaterials = {!! json_encode($repair->damagedMaterials->toArray()) !!};
        const existingMaterialReplacements = {!! json_encode($repair->materialReplacements->toArray()) !!};
        
        // Load full replacement history with serial details
        const fullReplacementHistory = {!! json_encode($repair->materialReplacements->map(function($mr) {
            // Handle old_serials - could be string JSON or array
            $oldSerials = [];
            if ($mr->old_serials) {
                if (is_string($mr->old_serials)) {
                    $oldSerials = json_decode($mr->old_serials, true) ?: [];
                } else if (is_array($mr->old_serials)) {
                    $oldSerials = $mr->old_serials;
                }
            }
            
            // Handle new_serials - could be string JSON or array
            $newSerials = [];
            if ($mr->new_serials) {
                if (is_string($mr->new_serials)) {
                    $newSerials = json_decode($mr->new_serials, true) ?: [];
                } else if (is_array($mr->new_serials)) {
                    $newSerials = $mr->new_serials;
                }
            }
            
            return [
                'device_code' => $mr->device_code,
                'material_code' => $mr->material_code,
                'source_warehouse_id' => $mr->source_warehouse_id,
                'target_warehouse_id' => $mr->target_warehouse_id,
                'quantity' => $mr->quantity,
                'old_serials' => $oldSerials,
                'new_serials' => $newSerials,
                'notes' => $mr->notes,
            ];
        })) !!};

        const repairItemsData = {!! json_encode($repair->repairItems->toArray()) !!};
        const warrantyCode = '{{ $repair->warranty_code }}';

        // Load device materials when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadDeviceMaterials();
            // Disable all material actions if repair is completed
            const isCompleted = '{{ $repair->status }}' === 'completed';
            if (isCompleted) {
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.material-repair-btn') || e.target.closest('.material-replace-btn')) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                }, true);
            }
        });

        // Load device materials for all repair items (deduplicate by device_code + serial)
        function loadDeviceMaterials() {
            deviceMaterialsList = [];
            const seenDevices = new Set();

            repairItemsData.forEach((item, index) => {
                const normalizedSerial = (item.device_serial || 'no_serial').toString().replace(/\s+/g, '');
                const deviceKey = `${item.device_code}__${normalizedSerial}`;
                if (seenDevices.has(deviceKey)) return; // tránh gọi trùng
                seenDevices.add(deviceKey);

                const deviceId = `${item.device_code}_${normalizedSerial}_${index}`;
                fetchDeviceMaterials(deviceId, item.device_code, warrantyCode);
            });
        }

        // Fetch device materials from API
        function fetchDeviceMaterials(deviceId, deviceCode, warrantyCode = null) {
            let url = `/api/repairs/device-materials?device_id=${deviceId}`;
            if (warrantyCode) {
                url += `&warranty_code=${warrantyCode}`;
            }

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.materials) {
                        // Chuẩn hoá và gán device cho từng vật tư
                        const materialsWithDevice = data.materials.map(material => {
                            const materialKey = `${deviceCode}_${material.code}`;
                            if (!originalMaterialSerials[materialKey]) {
                                originalMaterialSerials[materialKey] = material.serial || '';
                            }
                            // Tìm dữ liệu sửa chữa đã lưu để prefill ghi chú
                            const existingDM = existingDamagedMaterials.find(dm =>
                                dm.device_code === deviceCode &&
                                dm.material_code === material.code &&
                                (
                                    !material.serial || dm.serial === material.serial || dm.serial === null
                                )
                            );
                            const prefilledNote = existingDM ? (existingDM.damage_description || '') : '';
                            // Kiểm tra đã từng thay thế để khóa nút thay thế
                            const hasReplaced = existingMaterialReplacements.some(mr =>
                                mr.device_code === deviceCode && mr.material_code === material.code
                            );
                            return {
                                ...material,
                                deviceCode: deviceCode,
                                deviceId: deviceId,
                                repairNote: prefilledNote,
                                hasPendingReplacement: hasReplaced
                            };
                        });

                        // Loại bỏ các vật tư cũ của cùng thiết bị (nếu có) để tránh trùng lặp khi reload
                        deviceMaterialsList = deviceMaterialsList.filter(m => m.deviceId !== deviceId);

                        // Thêm mới theo cặp khóa deviceId + materialCode, tránh trùng lặp
                        const existingKeys = new Set(
                            deviceMaterialsList.map(m => `${m.deviceId}__${m.code}`)
                        );
                        materialsWithDevice.forEach(m => {
                            const key = `${m.deviceId}__${m.code}`;
                            if (!existingKeys.has(key)) {
                                deviceMaterialsList.push(m);
                                existingKeys.add(key);
                            }
                        });

                        updateMaterialsDisplay();
                    }
                })
                .catch(error => {
                    console.error('Error fetching device materials:', error);
                });
        }

        // Helper: tạo subrow ghi chú sửa chữa dưới dòng vật tư
        function createRepairSubrow(rowEl, index, material) {
            const subRow = document.createElement('tr');
            subRow.className = 'repair-note-subrow bg-blue-50';
            const col = document.createElement('td');
            col.colSpan = 6;
            col.innerHTML = `
                <div class="p-3">
                    <label class="block text-xs text-gray-600 mb-1">Ghi chú sửa chữa</label>
                    <textarea class="repair-note-input w-full border border-blue-200 rounded px-3 py-2 text-sm" rows="2" data-index="${index}" placeholder="Nhập ghi chú sửa chữa...">${material.repairNote || ''}</textarea>
                </div>
            `;
            subRow.appendChild(col);
            rowEl.parentNode.insertBefore(subRow, rowEl.nextSibling);
        }

        // Update materials display in table
        function updateMaterialsDisplay() {
            const tbody = document.getElementById('device_materials_body');
            tbody.innerHTML = '';

            if (deviceMaterialsList.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">
                            Không có vật tư nào được tìm thấy
                        </td>
                    </tr>
                `;
                return;
            }

            deviceMaterialsList.forEach((material, index) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                // Check if this material is damaged
                const isDamaged = existingDamagedMaterials.some(dm =>
                    dm.device_code === material.deviceCode &&
                    dm.material_code === material.code
                    // Don't check serial for damaged status - it's per material type, not per serial
                );

                // Check if this material has been replaced
                const hasBeenReplaced = material.hasPendingReplacement === true;

                // Trạng thái sửa chữa đang bật khi có ghi chú
                const repairActive = !!(material.repairNote && material.repairNote.trim());
                const isDisabled = hasBeenReplaced;
                const disabledClass = isDisabled ? 'opacity-50 cursor-not-allowed' : '';

                row.innerHTML = `
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${material.deviceCode}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${material.code}</td>
                    <td class="px-3 py-2 text-sm text-gray-700">${material.name}</td>
                    <td class="px-3 py-2 text-sm text-gray-700">${material.serial || 'Không có'}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${material.quantity}</td>
                    <td class="px-3 py-2 text-sm">
                        <div class="flex items-center space-x-2 ${disabledClass}">
                            ${isDisabled
                                ? `<span class=\"px-2 py-1 text-xs bg-gray-100 text-gray-400 rounded cursor-not-allowed select-none\">Sửa chữa</span>`
                                : `<button type=\"button\" class=\"material-repair-btn ${repairActive ? 'bg-blue-200 text-blue-700' : 'bg-blue-100 text-blue-600'} px-2 py-1 rounded hover:bg-blue-200 transition-colors text-xs\" data-index=\"${index}\">
                                        <i class=\"fas ${repairActive ? 'fa-undo' : 'fa-tools'} mr-1\"></i> ${repairActive ? 'Huỷ Sửa chữa' : 'Sửa chữa'}
                                   </button>`}
                            ${!isDisabled ? `
                                <button type="button" onclick="openReplaceModal(${index})" 
                                    class="material-replace-btn bg-yellow-100 text-yellow-600 px-2 py-1 rounded hover:bg-yellow-200 transition-colors text-xs" data-index="${index}">
                                    <i class="fas fa-exchange-alt mr-1"></i> Thay thế
                                </button>
                            ` : `
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                    <i class="fas fa-check mr-1"></i> Đã thay thế
                                </span>
                            `}
                        </div>
                    </td>
                `;

                tbody.appendChild(row);

                // Nếu đã có ghi chú sửa chữa từ trước thì hiển thị subrow ngay
                if (repairActive) {
                    createRepairSubrow(row, index, material);
                }
            });
        }

        // Toggle ghi chú sửa chữa khi bấm nút
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.material-repair-btn');
            if (!btn) return;
            const index = parseInt(btn.getAttribute('data-index'));
            const tbody = document.getElementById('device_materials_body');
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => !r.classList.contains('repair-note-subrow'));
            const row = rows[index];
            if (!row) return;
            const next = row.nextElementSibling;
            const material = deviceMaterialsList[index];

            if (next && next.classList.contains('repair-note-subrow')) {
                // Đang mở -> đóng và clear ghi chú
                next.remove();
                material.repairNote = '';
                btn.innerHTML = '<i class="fas fa-tools mr-1"></i> Sửa chữa';
                btn.classList.remove('bg-blue-200','text-blue-700');
                btn.classList.add('bg-blue-100','text-blue-600');
            } else {
                // Mở subrow và bind input
                createRepairSubrow(row, index, material);
                const input = row.nextElementSibling.querySelector('.repair-note-input');
                input.addEventListener('input', function() {
                    deviceMaterialsList[index].repairNote = this.value;
                });
                btn.innerHTML = '<i class="fas fa-undo mr-1"></i> Huỷ Sửa chữa';
                btn.classList.remove('bg-blue-100','text-blue-600');
                btn.classList.add('bg-blue-200','text-blue-700');
            }
        });

        // Open replace material modal
        function openReplaceModal(materialIndex) {
            const material = deviceMaterialsList[materialIndex];
            if (!material) return;

            // Force clear everything first
            forceResetModal();

            // Use current material data (with updated serials if any)
            currentReplacingMaterial = {
                ...material,
                index: materialIndex
            };

            // Set material info
            document.getElementById('replace-material-code').textContent = material.code;
            document.getElementById('replace-material-name').textContent = material.name;
            document.getElementById('max-quantity').textContent = material.quantity;
            document.getElementById('replace-quantity').max = material.quantity;

            // Determine if serial is required for this material (has any serial info)
            const normalize = (s) => (s || '').toString().trim().toUpperCase();
            const serialStr = normalize(material.serial);
            const current = Array.isArray(material.current_serials)
                ? material.current_serials.map(normalize).filter(s => s && s !== 'N/A')
                : [];
            const hasAnySerial = (serialStr && serialStr !== 'N/A') || current.length > 0;
            // Persist flag for handlers
            currentReplacingMaterial = { ...material, index: materialIndex, requiresSerial: hasAnySerial };

            // Toggle serial sections by requirement
            const oldSerialWrap = document.getElementById('old-serial-selection');
            const newSerialWrap = document.getElementById('serial-selection');
            oldSerialWrap.classList.add('hidden');
            newSerialWrap.classList.add('hidden');

            // Check if there's a previous replacement to pre-populate form
            const lastReplacement = getLastReplacement(material.deviceCode, material.code);
            console.log('Last replacement found:', lastReplacement);
            if (lastReplacement) {
                // Pre-populate form fields with last replacement data
                document.getElementById('source-warehouse').value = lastReplacement.source_warehouse_id;
                document.getElementById('target-warehouse').value = lastReplacement.target_warehouse_id;
                document.getElementById('replace-quantity').value = lastReplacement.quantity;
                document.getElementById('replace-notes').value = lastReplacement.notes || '';

                // Auto-load old serials if source warehouse is selected
                if (lastReplacement.source_warehouse_id && hasAnySerial) {
                    setTimeout(() => {
                        const materialKey = `${material.deviceCode}_${material.code}`;
                        const originalSerial = originalMaterialSerials[materialKey] || material.serial || '';
                        let originalSerials = [];
                        if (originalSerial) {
                            if (typeof originalSerial === 'string') {
                                originalSerials = originalSerial.split(',').map(s => s.trim()).filter(s => s);
                            } else if (Array.isArray(originalSerial)) {
                                originalSerials = [...originalSerial];
                            }
                        }
                        loadCurrentSerials(originalSerials, lastReplacement.old_serials || []);
                    }, 100);
                }

                // Auto-load new serials if target warehouse is selected
                if (lastReplacement.target_warehouse_id && hasAnySerial) {
                    setTimeout(() => {
                        loadAvailableSerials(material.code, lastReplacement.target_warehouse_id, 1, lastReplacement.new_serials || []);
                    }, 200);
                }
            }

            // Show modal
            document.getElementById('replace-material-modal').classList.remove('hidden');

            // If material has no serial requirement, keep serial sections hidden
            if (!hasAnySerial) {
                document.getElementById('old-serial-selection').classList.add('hidden');
                document.getElementById('serial-selection').classList.add('hidden');
            }
        }

        // Get replacement history for a specific material
        function getMaterialReplacementHistory(deviceCode, materialCode) {
            return fullReplacementHistory.filter(r =>
                r.device_code === deviceCode && r.material_code === materialCode
            );
        }

        // Get the last replacement for a material to show current state
        function getLastReplacement(deviceCode, materialCode) {
            // First check the current session replacements (not yet saved to DB)
            const sessionReplacements = materialReplacements.filter(r =>
                r.device_code === deviceCode && r.material_code === materialCode
            );
            
            if (sessionReplacements.length > 0) {
                return sessionReplacements[sessionReplacements.length - 1];
            }
            
            // Then check database replacements
            const dbHistory = getMaterialReplacementHistory(deviceCode, materialCode);
            return dbHistory.length > 0 ? dbHistory[dbHistory.length - 1] : null;
        }

        // Force reset modal completely
        function forceResetModal() {
            // Reset all form fields
            document.getElementById('source-warehouse').value = '';
            document.getElementById('target-warehouse').value = '';
            document.getElementById('replace-quantity').value = '1';
            document.getElementById('replace-notes').value = '';

            // Hide serial sections
            document.getElementById('old-serial-selection').classList.add('hidden');
            document.getElementById('serial-selection').classList.add('hidden');

            // Clear content
            document.getElementById('old-serial-list').innerHTML = '';
            document.getElementById('serial-list').innerHTML = '';

            // Clear any cached selections
            currentReplacingMaterial = null;
        }

        // Reset replace modal state
        function resetReplaceModalState() {
            // Reset form fields
            document.getElementById('source-warehouse').value = '';
            document.getElementById('target-warehouse').value = '';
            document.getElementById('replace-quantity').value = '1';
            document.getElementById('replace-notes').value = '';

            // Hide and clear serial sections completely
            document.getElementById('old-serial-selection').classList.add('hidden');
            document.getElementById('serial-selection').classList.add('hidden');
            document.getElementById('old-serial-list').innerHTML = '';
            document.getElementById('serial-list').innerHTML = '';

            // Clear any existing checkbox selections in the DOM
            setTimeout(() => {
                document.querySelectorAll('.old-serial-checkbox').forEach(cb => {
                    cb.checked = false;
                    cb.removeAttribute('checked');
                });
                document.querySelectorAll('.serial-checkbox').forEach(cb => {
                    cb.checked = false;
                    cb.removeAttribute('checked');
                });
            }, 50);
        }

        // Load current serials for replacement
        function loadCurrentSerials(currentSerials, selectedSerials = []) {
            const oldSerialList = document.getElementById('old-serial-list');

            // Force clear everything first
            oldSerialList.innerHTML = '';

            // Remove any event listeners by cloning the element
            const newOldSerialList = oldSerialList.cloneNode(false);
            oldSerialList.parentNode.replaceChild(newOldSerialList, oldSerialList);

            if (currentSerials && currentSerials.length > 0) {
                let serialArray = [];
                currentSerials.forEach(serial => {
                    if (typeof serial === 'string' && serial.includes(',')) {
                        const splitSerials = serial.split(',').map(s => s.trim()).filter(s => s);
                        serialArray.push(...splitSerials);
                    } else if (serial) {
                        serialArray.push(serial.toString().trim());
                    }
                });

                serialArray = [...new Set(serialArray)].filter(s => s && s.trim());

                serialArray.forEach((serial, index) => {
                    // Check if this serial should be selected (for displaying current state)
                    const isSelected = selectedSerials.includes(serial);
                    const serialItem = document.createElement('div');
                    serialItem.className =
                        'flex items-center space-x-2 p-2 hover:bg-gray-50 rounded border border-gray-100';

                    // Create unique ID to avoid conflicts
                    const uniqueId = `old-serial-${serial}-${Date.now()}-${index}`;
                    serialItem.innerHTML = `
                        <input type="checkbox" class="old-serial-checkbox" value="${serial}" id="${uniqueId}" ${isSelected ? 'checked' : ''}>
                        <label for="${uniqueId}" class="flex-1 text-sm cursor-pointer">${serial}</label>
                    `;
                    document.getElementById('old-serial-list').appendChild(serialItem);
                });

                document.getElementById('old-serial-selection').classList.remove('hidden');
            } else {
                document.getElementById('old-serial-list').innerHTML =
                    '<p class="text-sm text-gray-500">Không có thông tin serial</p>';
                document.getElementById('old-serial-selection').classList.remove('hidden');
            }
        }

        // Event listeners for warehouse selection
        document.getElementById('source-warehouse').addEventListener('change', function() {
            if (this.value && currentReplacingMaterial && currentReplacingMaterial.requiresSerial) {
                // Use original serials, not current (modified) serials
                const materialKey = `${currentReplacingMaterial.deviceCode}_${currentReplacingMaterial.code}`;
                const originalSerial = originalMaterialSerials[materialKey] || currentReplacingMaterial.serial || '';
                
                let originalSerials = [];
                if (originalSerial) {
                    if (typeof originalSerial === 'string') {
                        originalSerials = originalSerial.split(',').map(s => s.trim()).filter(s => s);
                    } else if (Array.isArray(originalSerial)) {
                        originalSerials = [...originalSerial];
                    }
                }

                // Check if there's a previous replacement to show selected state
                const lastReplacement = getLastReplacement(currentReplacingMaterial.deviceCode,
                    currentReplacingMaterial.code);
                const selectedOldSerials = lastReplacement ? lastReplacement.old_serials : [];

                loadCurrentSerials(originalSerials, selectedOldSerials);
            }
        });

        document.getElementById('target-warehouse').addEventListener('change', function() {
            if (this.value && currentReplacingMaterial && currentReplacingMaterial.requiresSerial) {
                // Check if there's a previous replacement to show selected state
                const lastReplacement = getLastReplacement(currentReplacingMaterial.deviceCode,
                    currentReplacingMaterial.code);
                const selectedNewSerials = lastReplacement ? (lastReplacement.new_serials || []) : [];

                loadAvailableSerials(currentReplacingMaterial.code, this.value, 1, selectedNewSerials);
            }
        });

        // Load available serials from warehouse
        function loadAvailableSerials(materialCode, warehouseId, requiredQuantity, selectedSerials = []) {
            fetch(`/api/repairs/available-serials?material_code=${materialCode}&warehouse_id=${warehouseId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const serialList = document.getElementById('serial-list');

                    // Force clear everything first
                    serialList.innerHTML = '';

                    // Remove any event listeners by cloning the element
                    const newSerialList = serialList.cloneNode(false);
                    serialList.parentNode.replaceChild(newSerialList, serialList);

                    if (data.success && data.serials && data.serials.length > 0) {
                        data.serials.forEach((serial, index) => {
                            // Check if this serial should be selected (for displaying current state)
                            const isSelected = selectedSerials.includes(serial.serial);
                            const serialItem = document.createElement('div');
                            serialItem.className = 'flex items-center space-x-2 p-2 hover:bg-gray-50 rounded';

                            // Create unique ID to avoid conflicts
                            const uniqueId = `new-serial-${serial.serial}-${Date.now()}-${index}`;
                            serialItem.innerHTML = `
                            <input type="checkbox" class="serial-checkbox" value="${serial.serial}" 
                                   data-status="${serial.status}" id="${uniqueId}"
                                   ${serial.status !== 'available' ? 'disabled' : ''} ${isSelected ? 'checked' : ''}>
                            <span class="flex-1 text-sm">${serial.serial}</span>
                            <span class="text-xs px-2 py-1 rounded ${getSerialStatusClass(serial.status)}">
                                ${getSerialStatusText(serial.status)}
                            </span>
                        `;
                            document.getElementById('serial-list').appendChild(serialItem);
                        });

                        document.getElementById('serial-selection').classList.remove('hidden');
                    } else {
                        document.getElementById('serial-list').innerHTML =
                            '<p class="text-sm text-gray-500">Không có serial nào khả dụng trong kho này</p>';
                        document.getElementById('serial-selection').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error loading available serials:', error);
                });
        }

        // Get serial status class
        function getSerialStatusClass(status) {
            const classes = {
                'available': 'bg-green-100 text-green-800',
                'used': 'bg-red-100 text-red-800',
                'reserved': 'bg-yellow-100 text-yellow-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }

        // Get serial status text
        function getSerialStatusText(status) {
            const texts = {
                'available': 'Khả dụng',
                'used': 'Đã sử dụng',
                'reserved': 'Đã đặt trước'
            };
            return texts[status] || 'Không xác định';
        }

        // Close replace modal
        document.getElementById('close-replace-modal').addEventListener('click', function() {
            document.getElementById('replace-material-modal').classList.add('hidden');
            currentReplacingMaterial = null;
            resetReplaceModalState();
        });

        document.getElementById('cancel-replace-btn').addEventListener('click', function() {
            document.getElementById('replace-material-modal').classList.add('hidden');
            currentReplacingMaterial = null;
            resetReplaceModalState();
        });

        // Confirm replacement
        document.getElementById('confirm-replace-btn').addEventListener('click', function() {
            if (!currentReplacingMaterial) return;

            const sourceWarehouse = document.getElementById('source-warehouse').value;
            const targetWarehouse = document.getElementById('target-warehouse').value;
            const quantity = parseInt(document.getElementById('replace-quantity').value);
            const notes = document.getElementById('replace-notes').value;

            // Get selected old serials
            const oldSerials = Array.from(document.querySelectorAll('.old-serial-checkbox:checked'))
                .map(cb => cb.value);

            // Get selected new serials
            const newSerials = Array.from(document.querySelectorAll('.serial-checkbox:checked'))
                .map(cb => cb.value);

            // Validation
            if (!sourceWarehouse || !targetWarehouse) {
                alert('Vui lòng chọn kho nguồn và kho đích!');
                return;
            }

            if (currentReplacingMaterial.requiresSerial) {
                if (oldSerials.length === 0 || newSerials.length === 0) {
                    alert('Vui lòng chọn serial cũ và serial mới!');
                    return;
                }
                if (oldSerials.length !== newSerials.length) {
                    alert('Số lượng serial cũ và mới phải bằng nhau!');
                    return;
                }
            }

            // Add or update material replacements array
            const replacement = {
                device_code: currentReplacingMaterial.deviceCode,
                material_code: currentReplacingMaterial.code,
                material_name: currentReplacingMaterial.name,
                old_serials: currentReplacingMaterial.requiresSerial ? oldSerials : [],
                new_serials: currentReplacingMaterial.requiresSerial ? newSerials : [],
                quantity: quantity,
                source_warehouse_id: sourceWarehouse,
                target_warehouse_id: targetWarehouse,
                notes: notes
            };

            // Check if there's already a replacement for this material
            const existingIndex = materialReplacements.findIndex(r =>
                r.device_code === currentReplacingMaterial.deviceCode &&
                r.material_code === currentReplacingMaterial.code
            );

            if (existingIndex !== -1) {
                // Update existing replacement
                materialReplacements[existingIndex] = replacement;
            } else {
                // Add new replacement
                materialReplacements.push(replacement);
            }

            // Update material display - replace only the selected serials
            const materialIndex = currentReplacingMaterial.index;
            const currentMaterial = deviceMaterialsList[materialIndex];

            // Get current serials as array
            let currentSerialArray = [];
            if (currentMaterial.serial) {
                if (typeof currentMaterial.serial === 'string') {
                    currentSerialArray = currentMaterial.serial.split(',').map(s => s.trim()).filter(s => s);
                } else if (Array.isArray(currentMaterial.serial)) {
                    currentSerialArray = [...currentMaterial.serial];
                }
            }

            if (currentReplacingMaterial.requiresSerial) {
                // Replace old serials with new serials
                oldSerials.forEach((oldSerial, index) => {
                    const serialIndex = currentSerialArray.findIndex(s => s === oldSerial.trim());
                    if (serialIndex !== -1 && newSerials[index]) {
                        currentSerialArray[serialIndex] = newSerials[index].trim();
                    }
                });
                // Update the material serial display
                deviceMaterialsList[materialIndex].serial = currentSerialArray.join(', ');
            }
            updateMaterialsDisplay();

            // Close modal and reset state
            document.getElementById('replace-material-modal').classList.add('hidden');
            currentReplacingMaterial = null;

            // Force clear all selections for next time
            resetReplaceModalState();
        });

        // Handle form submission: gom vật tư có ghi chú sửa chữa
        document.querySelector('form').addEventListener('submit', function(e) {
            const damagedMaterials = [];
            deviceMaterialsList.forEach(material => {
                const note = (material.repairNote || '').trim();
                if (note) {
                    damagedMaterials.push({
                        device_code: material.deviceCode,
                        material_code: material.code,
                        material_name: material.name,
                        serial: material.serial,
                        damage_description: note
                    });
                }
            });

            document.getElementById('material_replacements_data').value = JSON.stringify(materialReplacements);
            document.getElementById('damaged_materials_data').value = JSON.stringify(damagedMaterials);
        });

        // Array to track photos to delete
        let photosToDelete = [];

        // Hàm xóa ảnh
        function removePhoto(index) {
            if (confirm('Bạn có chắc muốn xóa hình ảnh này?')) {
                // Get current photos from backend
                const currentPhotos = {!! json_encode($repair->repair_photos ?? []) !!};
                
                if (currentPhotos[index]) {
                    // Add photo to delete list
                    photosToDelete.push(currentPhotos[index]);
                    
                    // Update hidden field
                    document.getElementById('photos_to_delete').value = JSON.stringify(photosToDelete);
                    
                    // Hide the photo element visually
                    const photoElements = document.querySelectorAll('.photo-item');
                    if (photoElements[index]) {
                        photoElements[index].style.display = 'none';
                    }
                    
                    console.log('Photo marked for deletion:', currentPhotos[index]);
                    
                    // Show success message
                    showMessage('Ảnh đã được đánh dấu để xóa. Nhấn "Cập nhật" để lưu thay đổi.', 'success');
                }
            }
        }

        // Show message function
        function showMessage(message, type = 'success') {
            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            messageDiv.textContent = message;
            
            // Add to page
            document.body.appendChild(messageDiv);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.parentNode.removeChild(messageDiv);
                }
            }, 3000);
        }
    </script>
</body>

</html>
