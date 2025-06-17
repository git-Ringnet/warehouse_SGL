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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="warranty_code" class="block text-sm font-medium text-gray-700 mb-1">Mã bảo hành hoặc thiết bị 
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="warranty_code" name="warranty_code" value="{{ old('warranty_code') }}"
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
                                <option value="maintenance" {{ old('repair_type') == 'maintenance' ? 'selected' : '' }}>Bảo trì định kỳ</option>
                                <option value="repair" {{ old('repair_type') == 'repair' ? 'selected' : '' }}>Sửa chữa lỗi</option>
                                <option value="replacement" {{ old('repair_type') == 'replacement' ? 'selected' : '' }}>Thay thế linh kiện</option>
                                <option value="upgrade" {{ old('repair_type') == 'upgrade' ? 'selected' : '' }}>Nâng cấp</option>
                                <option value="other" {{ old('repair_type') == 'other' ? 'selected' : '' }}>Khác</option>
                            </select>
                        </div>
                        <div>
                            <label for="repair_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày
                                sửa chữa <span class="text-red-500">*</span></label>
                            <input type="date" id="repair_date" name="repair_date" value="{{ old('repair_date', date('Y-m-d')) }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="technician_id"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật viên <span
                                    class="text-red-500">*</span></label>
                            <select id="technician_id" name="technician_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kỹ thuật viên --</option>
                                @foreach(App\Models\Employee::where('status', 'active')->get() as $employee)
                                    <option value="{{ $employee->id }}" {{ old('technician_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">
                                Kho linh kiện thay thế <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn Kho linh kiện thay thế --</option>
                                <option value="1" {{ old('warehouse_id') == '1' ? 'selected' : '' }}>Kho chính</option>
                                <option value="2" {{ old('warehouse_id') == '2' ? 'selected' : '' }}>Kho phụ</option>
                                <option value="3" {{ old('warehouse_id') == '3' ? 'selected' : '' }}>Kho linh kiện</option>
                                <option value="4" {{ old('warehouse_id') == '4' ? 'selected' : '' }}>Kho bảo hành</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="repair_description"
                            class="block text-sm font-medium text-gray-700 mb-1 required">Mô tả sửa chữa <span
                                class="text-red-500">*</span></label>
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

    <!-- Modal thêm thiết bị -->
    <div id="add-device-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Thêm thiết bị</h3>
                <button type="button" id="close-device-modal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="device_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã thiết bị <span class="text-red-500">*</span></label>
                    <input type="text" id="device_code" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nhập mã thiết bị">
                </div>
                <div>
                    <label for="device_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên thiết bị <span class="text-red-500">*</span></label>
                    <input type="text" id="device_name" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nhập tên thiết bị">
                </div>
                <div>
                    <label for="device_serial" class="block text-sm font-medium text-gray-700 mb-1">Serial number</label>
                    <input type="text" id="device_serial"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nhập serial number">
                </div>
                <div>
                    <label for="device_notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                    <textarea id="device_notes" rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Nhập ghi chú về thiết bị"></textarea>
                </div>
                <div>
                    <label for="device_images" class="block text-sm font-medium text-gray-700 mb-1">Hình ảnh thiết bị</label>
                    <input type="file" id="device_images" multiple accept="image/*"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" id="cancel-device-btn"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Hủy
                </button>
                <button type="button" id="confirm-device-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i> Thêm thiết bị
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
            const repairHistory = document.getElementById('repair_history');
            const repairHistoryBody = document.getElementById('repair_history_body');
            const addDeviceBtn = document.getElementById('add_device_btn');
            const addDeviceModal = document.getElementById('add-device-modal');
            const closeDeviceModal = document.getElementById('close-device-modal');
            const cancelDeviceBtn = document.getElementById('cancel-device-btn');
            const confirmDeviceBtn = document.getElementById('confirm-device-btn');
            
            // Mảng lưu trữ các thiết bị đã chọn
            let selectedDevices = [];
            let deviceCounter = 0;

            // Setup CSRF token for AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Xử lý sự kiện tìm kiếm mã bảo hành
            searchWarrantyBtn.addEventListener('click', function() {
                const warrantyCode = warrantyCodeInput.value.trim();
                
                if (!warrantyCode) {
                    alert('Vui lòng nhập mã bảo hành');
                    return;
                }

                // Call API to search warranty
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
                        
                        // Hiển thị thông tin khách hàng
                        customerNameInput.value = warranty.customer_name;
                        
                        // Thêm thiết bị từ bảo hành vào danh sách
                        if (warranty.devices && warranty.devices.length > 0) {
                            warranty.devices.forEach(device => {
                                addDeviceToList({
                                    id: device.id,
                                    code: device.code,
                                    name: device.name,
                                    serial: device.serial || '',
                                    notes: '',
                                    fromWarranty: true
                                });
                            });
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

            // Xử lý thêm thiết bị thủ công
            addDeviceBtn.addEventListener('click', function() {
                addDeviceModal.classList.remove('hidden');
                resetDeviceForm();
            });

            closeDeviceModal.addEventListener('click', function() {
                addDeviceModal.classList.add('hidden');
            });

            cancelDeviceBtn.addEventListener('click', function() {
                addDeviceModal.classList.add('hidden');
            });

            confirmDeviceBtn.addEventListener('click', function() {
                const deviceCode = document.getElementById('device_code').value.trim();
                const deviceName = document.getElementById('device_name').value.trim();
                const deviceSerial = document.getElementById('device_serial').value.trim();
                const deviceNotes = document.getElementById('device_notes').value.trim();

                if (!deviceCode || !deviceName) {
                    alert('Vui lòng nhập mã thiết bị và tên thiết bị');
                    return;
                }

                // Kiểm tra trùng lặp
                if (selectedDevices.some(device => device.code === deviceCode)) {
                    alert('Thiết bị này đã được thêm vào danh sách');
                    return;
                }

                addDeviceToList({
                    id: 'manual_' + (++deviceCounter),
                    code: deviceCode,
                    name: deviceName,
                    serial: deviceSerial,
                    notes: deviceNotes,
                    fromWarranty: false
                });

                addDeviceModal.classList.add('hidden');
            });

            // Hàm thêm thiết bị vào danh sách
            function addDeviceToList(device) {
                selectedDevices.push(device);
                updateSelectedDevicesDisplay();
            }

            // Hàm cập nhật hiển thị danh sách thiết bị đã chọn
            function updateSelectedDevicesDisplay() {
                selectedDevicesContainer.innerHTML = '';
                
                selectedDevices.forEach((device, index) => {
                    const deviceDiv = document.createElement('div');
                    deviceDiv.className = 'flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200';
                    deviceDiv.innerHTML = `
                        <input type="hidden" name="selected_devices[]" value="${device.id}">
                        <input type="hidden" name="device_code[${device.id}]" value="${device.code}">
                        <input type="hidden" name="device_name[${device.id}]" value="${device.name}">
                        <input type="hidden" name="device_serial[${device.id}]" value="${device.serial}">
                        <input type="hidden" name="device_notes[${device.id}]" value="${device.notes}">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">${device.code} - ${device.name}</div>
                            <div class="text-xs text-gray-500">
                                ${device.serial ? 'Serial: ' + device.serial : 'Không có serial'}
                                ${device.fromWarranty ? ' • Từ bảo hành' : ' • Thêm thủ công'}
                            </div>
                            ${device.notes ? '<div class="text-xs text-gray-600 mt-1">' + device.notes + '</div>' : ''}
                        </div>
                        <button type="button" class="text-red-500 hover:text-red-700 ml-2" onclick="removeDevice(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    selectedDevicesContainer.appendChild(deviceDiv);
                });
            }

            // Hàm xóa thiết bị
            window.removeDevice = function(index) {
                selectedDevices.splice(index, 1);
                updateSelectedDevicesDisplay();
            };

            // Hàm reset form thiết bị
            function resetDeviceForm() {
                document.getElementById('device_code').value = '';
                document.getElementById('device_name').value = '';
                document.getElementById('device_serial').value = '';
                document.getElementById('device_notes').value = '';
                document.getElementById('device_images').value = '';
            }

            // Đóng modal khi click bên ngoài
            addDeviceModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });

            // Validate form before submit
            document.querySelector('form').addEventListener('submit', function(e) {
                if (selectedDevices.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một thiết bị');
                    return false;
                }
            });
        });
    </script>
</body>

</html>
