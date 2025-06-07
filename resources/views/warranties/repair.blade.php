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
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <a href="{{ asset('repair_list') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Sửa chữa & Bảo trì thiết bị</h1>
            </div>
        </header>

        <main class="p-6">
            <form action="#" method="POST">
                @csrf

                <!-- Thông tin bảo hành -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                        Thông tin bảo hành
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="warranty_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã
                                bảo hành hoặc thiết bị<span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="warranty_code" name="warranty_code" required
                                    class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nhập mã bảo hành">
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
                            <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg">
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
                                                Trạng thái
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
                            <label for="repair_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại
                                sửa chữa <span class="text-red-500">*</span></label>
                            <select id="repair_type" name="repair_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Chọn loại sửa chữa</option>
                                <option value="maintenance">Bảo trì định kỳ</option>
                                <option value="repair">Sửa chữa lỗi</option>
                                <option value="replacement">Thay thế linh kiện</option>
                                <option value="upgrade">Nâng cấp</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label for="repair_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày
                                sửa chữa <span class="text-red-500">*</span></label>
                            <input type="date" id="repair_date" name="repair_date" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="technician_name"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật viên <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="technician_name" name="technician_name" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập tên kỹ thuật viên">
                        </div>
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Kho linh kiện thay thế <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn Kho linh kiện thay thế --</option>
                                <option value="1">Kho chính</option>
                                <option value="2">Kho phụ</option>
                                <option value="3">Kho linh kiện</option>
                                <option value="4">Kho bảo hành</option>
                            </select>
                        </div>
                        <div>
                            <label for="repair_status"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái <span
                                    class="text-red-500">*</span></label>
                            <select id="repair_status" name="repair_status" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="completed">Hoàn thành</option>
                                <option value="in_progress">Đang tiến hành</option>
                                <option value="pending">Chờ xử lý</option>
                                <option value="canceled">Đã hủy</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="repair_description"
                            class="block text-sm font-medium text-gray-700 mb-1 required">Mô tả sửa chữa <span
                                class="text-red-500">*</span></label>
                        <textarea id="repair_description" name="repair_description" rows="3" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập mô tả chi tiết về vấn đề và cách sửa chữa"></textarea>
                    </div>

                    <!-- Linh kiện thiết bị -->
                    <div class="mt-4">
                        <h3 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-microchip text-blue-500 mr-2"></i>
                            Chi tiết vật tư thiết bị
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã thiết bị
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã vật tư
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên vật tư
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thao tác
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="device-parts-table" class="bg-white divide-y divide-gray-200">
                                    <!-- Dữ liệu vật tư sẽ được thêm vào đây bằng JavaScript -->
                                </tbody>
                            </table>
                        </div>
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
                            placeholder="Nhập ghi chú bổ sung"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ asset('warranties/repair_list') }}"
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

    <!-- Hộp thông báo thay thế vật tư -->
    <div id="replace-part-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Thay thế vật tư</h3>
                <button type="button" id="close-replace-modal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-gray-700 mb-2">Thông tin vật tư cần thay thế:</p>
                <div class="bg-gray-50 p-3 rounded-lg mb-4">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-sm font-medium text-gray-600">Mã vật tư:</span>
                            <span class="text-sm text-gray-900 ml-1" id="old-part-code">VT001-C</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Tên vật tư:</span>
                            <span class="text-sm text-gray-900 ml-1" id="old-part-name">Bộ nhớ Flash</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4 mb-4">
                    <p class="text-gray-700 mb-2">Chuyển vật tư cũ đến kho:</p>
                    <select id="replace_warehouse_id" name="replace_warehouse_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-4">
                        <option value="">-- Chọn kho chuyển --</option>
                        <option value="3">Kho linh kiện</option>
                        <option value="4">Kho bảo hành</option>
                        <option value="5">Kho vật tư hỏng</option>
                        <option value="6">Kho tái chế</option>
                    </select>
                </div>

                <p class="text-gray-700 mb-2">Thay thế bằng vật tư mới:</p>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="new_part_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã
                            vật tư mới <span class="text-red-500">*</span></label>
                        <select id="new_part_code" name="new_part_code"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Chọn vật tư mới --</option>
                            <option value="VT002-C">VT002-C - Bộ nhớ Flash 16GB</option>
                            <option value="VT003-C">VT003-C - Bộ nhớ Flash 32GB</option>
                            <option value="VT004-C">VT004-C - Bộ nhớ Flash 64GB</option>
                        </select>
                    </div>
                    <div>
                        <label for="new_part_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi
                            chú</label>
                        <textarea id="new_part_note" name="new_part_note" rows="2" placeholder="Nhập ghi chú về việc thay thế vật tư"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy các elements
            const warrantyCodeInput = document.getElementById('warranty_code');
            const searchWarrantyBtn = document.getElementById('search_warranty');
            const customerNameInput = document.getElementById('customer_name');
            const selectedDevicesContainer = document.getElementById('selected_devices');
            const devicesContainer = document.getElementById('devices_container');
            const devicesList = document.getElementById('devices_list');
            const selectedDeviceInfo = document.getElementById('selected_device_info');
            const deviceCodeDisplay = document.getElementById('device_code_display');
            const deviceNameDisplay = document.getElementById('device_name_display');
            const deviceSerialDisplay = document.getElementById('device_serial_display');
            const repairHistory = document.getElementById('repair_history');
            const repairHistoryBody = document.getElementById('repair_history_body');
            
            // Mảng lưu trữ các thiết bị đã chọn
            let selectedDevices = [];

            // Ẩn container thiết bị ban đầu
            devicesContainer.style.display = 'none';

            // Giả lập dữ liệu mẫu
            const sampleData = {
                "W12345": {
                    customer_name: "Công ty TNHH ABC",
                    devices: [{
                            id: 1,
                            code: "DEV001",
                            name: "Bộ điều khiển chính",
                            serial: "SN001122",
                            parts: [
                                { id: "VT001-A", name: "Bo mạch chính", condition: false },
                                { id: "VT001-B", name: "Cảm biến nhiệt độ", condition: false },
                                { id: "VT001-C", name: "Bộ nhớ Flash", condition: true }
                            ]
                        },
                        {
                            id: 2,
                            code: "DEV002",
                            name: "Cảm biến nhiệt độ",
                            serial: "SN002233",
                            parts: [
                                { id: "VT002-A", name: "Cảm biến nhiệt", condition: false },
                                { id: "VT002-B", name: "Dây cáp kết nối", condition: true }
                            ]
                        }
                    ],
                    repair_history: [
                        {
                            date: "2023-05-15",
                            type: "Bảo trì định kỳ",
                            description: "Kiểm tra và vệ sinh thiết bị",
                            technician: "Nguyễn Văn A"
                        },
                        {
                            date: "2023-08-20",
                            type: "Sửa chữa lỗi",
                            description: "Thay thế cảm biến bị lỗi",
                            technician: "Trần Văn B"
                        }
                    ]
                },
                "W67890": {
                    customer_name: "Công ty CP XYZ",
                    devices: [{
                        id: 3,
                        code: "DEV003",
                        name: "Màn hình giám sát",
                        serial: "SN003344",
                        parts: [
                            { id: "VT003-A", name: "Màn hình LCD", condition: false },
                            { id: "VT003-B", name: "Bo mạch xử lý", condition: false },
                            { id: "VT003-C", name: "Cáp nguồn", condition: true }
                        ]
                    }],
                    repair_history: []
                }
            };

            // Xử lý sự kiện tìm kiếm mã bảo hành
            searchWarrantyBtn.addEventListener('click', function() {
                const warrantyCode = warrantyCodeInput.value.trim();

                if (warrantyCode && sampleData[warrantyCode]) {
                    const data = sampleData[warrantyCode];

                    // Hiển thị thông tin khách hàng
                    customerNameInput.value = data.customer_name;

                    // Cập nhật danh sách thiết bị
                    devicesList.innerHTML = '';
                    
                    // Thêm thiết bị mới vào danh sách
                    data.devices.forEach(device => {
                        const row = document.createElement('tr');

                        // Status class và text
                        let statusClass, statusText;
                        switch (device.status || 'active') {
                            case 'active':
                                statusClass = 'bg-green-100 text-green-800';
                                statusText = 'Hoạt động';
                                break;
                            case 'maintenance':
                                statusClass = 'bg-yellow-100 text-yellow-800';
                                statusText = 'Bảo trì';
                                break;
                            case 'inactive':
                                statusClass = 'bg-red-100 text-red-800';
                                statusText = 'Ngừng hoạt động';
                                break;
                            default:
                                statusClass = 'bg-green-100 text-green-800';
                                statusText = 'Hoạt động';
                        }

                        row.innerHTML = `
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${device.code}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${device.name}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">${device.serial}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                    ${statusText}
                                </span>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm">
                                <button type="button" class="select-device-btn bg-blue-100 text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition-colors text-xs"
                                    data-device-id="${device.id}" data-device-code="${device.code}">
                                    <i class="fas fa-check-circle mr-1"></i> Chọn
                                </button>
                            </td>
                        `;

                        devicesList.appendChild(row);
                    });
                    
                    // Hiển thị container thiết bị
                    devicesContainer.style.display = 'block';
                    
                    // Thêm sự kiện chọn thiết bị
                    const selectDeviceBtns = document.querySelectorAll('.select-device-btn');
                    selectDeviceBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                            const deviceId = parseInt(this.getAttribute('data-device-id'));
                            
                            // Kiểm tra xem thiết bị đã được chọn chưa
                            if (selectedDevices.some(d => d.id === deviceId)) {
                                alert('Thiết bị này đã được chọn!');
                                return;
                            }
                            
                            // Tìm thiết bị từ dữ liệu mẫu
                            const warrantyCode = warrantyCodeInput.value.trim();
                            if (warrantyCode && sampleData[warrantyCode]) {
                                const selectedDevice = sampleData[warrantyCode].devices.find(device => device.id === deviceId);
                                
                                if (selectedDevice) {
                                    // Thêm thiết bị vào danh sách đã chọn
                                    selectedDevices.push(selectedDevice);
                                    
                                    // Cập nhật hiển thị danh sách thiết bị đã chọn
                                    updateSelectedDevicesDisplay();
                                    
                                    // Cập nhật hiển thị danh sách vật tư
                                    updatePartsDisplay();
                                    
                                    // Hiển thị thông tin thiết bị
                                    selectedDeviceInfo.classList.remove('hidden');
                                }
                            }
                        });
                    });

                    // Hiển thị lịch sử sửa chữa nếu có
                    if (data.repair_history && data.repair_history.length > 0) {
                        repairHistoryBody.innerHTML = '';
                        data.repair_history.forEach(repair => {
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
                    alert('Không tìm thấy thông tin bảo hành với mã này!');
                    customerNameInput.value = '';
                    devicesContainer.style.display = 'none';
                    selectedDeviceInfo.classList.add('hidden');
                    repairHistory.classList.add('hidden');
                    
                    // Xóa danh sách vật tư
                    document.getElementById('device-parts-table').innerHTML = '';
                    
                    // Xóa danh sách thiết bị đã chọn
                    selectedDevices = [];
                    updateSelectedDevicesDisplay();
                }
            });
            
            // Hiển thị danh sách thiết bị đã chọn
            function updateSelectedDevicesDisplay() {
                selectedDevicesContainer.innerHTML = '';
                
                selectedDevices.forEach((device, index) => {
                    const deviceDiv = document.createElement('div');
                    deviceDiv.className = 'flex items-center justify-between bg-gray-50 p-2 rounded-lg border border-gray-200';
                    deviceDiv.innerHTML = `
                        <input type="hidden" name="selected_devices[]" value="${device.id}">
                        <div class="flex-1 text-sm text-gray-900">${device.code} - ${device.name}</div>
                        <button type="button" class="remove-device-btn text-red-500 hover:text-red-700 ml-2" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    selectedDevicesContainer.appendChild(deviceDiv);
                });
                
                // Thêm sự kiện xóa thiết bị
                const removeDeviceBtns = document.querySelectorAll('.remove-device-btn');
                removeDeviceBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        selectedDevices.splice(index, 1);
                        updateSelectedDevicesDisplay();
                        updatePartsDisplay();
                        
                        // Ẩn thông tin thiết bị nếu không còn thiết bị nào
                        if (selectedDevices.length === 0) {
                            selectedDeviceInfo.classList.add('hidden');
                        }
                    });
                });
            }
            
            // Cập nhật hiển thị danh sách vật tư từ tất cả thiết bị đã chọn
            function updatePartsDisplay() {
                const devicePartsTable = document.getElementById('device-parts-table');
                devicePartsTable.innerHTML = '';
                
                // Nếu không có thiết bị nào được chọn, không hiển thị gì
                if (selectedDevices.length === 0) {
                    return;
                }
                
                // Thêm vật tư từ tất cả thiết bị đã chọn
                selectedDevices.forEach(device => {
                    if (device.parts && device.parts.length > 0) {
                        device.parts.forEach((part, index) => {
                            const row = document.createElement('tr');
                            
                            row.innerHTML = `
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">${device.code}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">${part.id}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">${part.name}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <input type="checkbox" id="damaged_part_${device.code}_${part.id}" 
                                                name="damaged_parts[]" value="${part.id}" 
                                                ${part.condition ? 'checked' : ''} 
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded damage-checkbox"
                                                data-device-code="${device.code}">
                                            <label for="damaged_part_${device.code}_${part.id}" class="ml-2 text-sm text-gray-700">Hư hỏng</label>
                                        </div>
                                        <button type="button" data-part-id="${part.id}" data-device-code="${device.code}" data-part-name="${part.name}"
                                            class="replace-part-btn bg-yellow-100 text-yellow-600 px-2 py-1 rounded hover:bg-yellow-200 transition-colors text-xs ml-2">
                                            <i class="fas fa-exchange-alt mr-1"></i> Thay thế
                                        </button>
                                    </div>
                                </td>
                            `;
                            
                            devicePartsTable.appendChild(row);
                        });
                    }
                });
                
                // Cập nhật event listeners cho các checkbox và nút thay thế
                setupDamagedPartsCheckboxes();
                setupReplacePartButtons();
            }

            // Xử lý các checkbox đánh dấu hư hỏng
            function setupDamagedPartsCheckboxes() {
                const damageCheckboxes = document.querySelectorAll('.damage-checkbox');
                damageCheckboxes.forEach(checkbox => {
                    // Xử lý sự kiện thay đổi
                    checkbox.addEventListener('change', function() {
                        // Cập nhật trạng thái của part trong mảng selectedDevices
                        const partId = this.value;
                        const deviceCode = this.getAttribute('data-device-code');
                        const isChecked = this.checked;
                        
                        // Tìm thiết bị và vật tư tương ứng trong mảng selectedDevices
                        const device = selectedDevices.find(d => d.code === deviceCode);
                        if (device && device.parts) {
                            const part = device.parts.find(p => p.id === partId);
                            if (part) {
                                part.condition = isChecked;
                            }
                        }
                    });
                });
            }

            // Xử lý các nút thay thế vật tư
            function setupReplacePartButtons() {
                const replacePartBtns = document.querySelectorAll('.replace-part-btn');
                replacePartBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const partId = this.getAttribute('data-part-id');
                        const deviceCode = this.getAttribute('data-device-code');
                        const partName = this.getAttribute('data-part-name');
                        showReplacePartDialog(partId, deviceCode, partName);
                    });
                });
            }

            // Hiển thị dialog thay thế vật tư
            function showReplacePartDialog(partId, deviceCode, partName) {
                // Cập nhật thông tin trong modal
                document.getElementById('old-part-code').textContent = partId;
                document.getElementById('old-part-name').textContent = partName;

                // Hiển thị modal thay thế vật tư
                const replacePartModal = document.getElementById('replace-part-modal');
                replacePartModal.classList.remove('hidden');

                // Xử lý các sự kiện nút trong modal
                const closeReplaceModalBtn = document.getElementById('close-replace-modal');
                const cancelReplaceBtn = document.getElementById('cancel-replace-btn');
                const confirmReplaceBtn = document.getElementById('confirm-replace-btn');

                // Đóng modal khi click vào nút đóng hoặc hủy
                const closeReplaceModal = () => {
                    replacePartModal.classList.add('hidden');
                };

                closeReplaceModalBtn.addEventListener('click', closeReplaceModal);
                cancelReplaceBtn.addEventListener('click', closeReplaceModal);

                // Xử lý xác nhận thay thế
                confirmReplaceBtn.addEventListener('click', function() {
                    const newPartCode = document.getElementById('new_part_code').value;
                    const warehouseId = document.getElementById('replace_warehouse_id').value;

                    if (!newPartCode) {
                        alert('Vui lòng chọn vật tư mới!');
                        return;
                    }

                    if (!warehouseId) {
                        alert('Vui lòng chọn kho để chuyển vật tư cũ!');
                        return;
                    }

                    // Tìm thiết bị và vật tư tương ứng trong mảng selectedDevices
                    const device = selectedDevices.find(d => d.code === deviceCode);
                    if (device && device.parts) {
                        const part = device.parts.find(p => p.id === partId);
                        if (part) {
                            // Đánh dấu vật tư cũ là hư hỏng
                            part.condition = true;
                            
                            // Hiển thị thông báo chuyển vật tư thành công
                            const warehouseSelect = document.getElementById('replace_warehouse_id');
                            const warehouseName = warehouseSelect.options[warehouseSelect.selectedIndex].text;
                            
                            alert(
                                `Đã thay thế vật tư ${partId} bằng ${newPartCode}. Vật tư cũ đã được chuyển đến ${warehouseName}.`);
                                
                            // Cập nhật lại hiển thị danh sách vật tư
                            updatePartsDisplay();
                        }
                    }

                    // Đóng modal
                    closeReplaceModal();
                });
            }

            // Tự động đặt ngày hiện tại cho ngày sửa chữa
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('repair_date').value = today;
        });
    </script>
</body>

</html>
