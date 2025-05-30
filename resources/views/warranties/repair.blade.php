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
                <a href="{{ asset('warranties/repair_list') }}" class="text-gray-600 hover:text-blue-500 mr-4">
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
                            <label for="warranty_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã bảo hành <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="warranty_code" name="warranty_code" required
                                    class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nhập mã bảo hành">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-hashtag text-gray-500"></i>
                                </div>
                                <button type="button" id="search_warranty" class="absolute inset-y-0 right-0 pr-3 flex items-center text-blue-500 hover:text-blue-700">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Khách hàng</label>
                            <input type="text" id="customer_name" name="customer_name" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                    </div>

                    <!-- Phần chọn thiết bị -->
                    <div class="mt-4">
                        <label for="device_select" class="block text-sm font-medium text-gray-700 mb-2 required">Chọn thiết bị cần sửa chữa <span class="text-red-500">*</span></label>
                        <select id="device_select" name="device_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Chọn thiết bị --</option>
                        </select>
                    </div>

                    <!-- Thông tin thiết bị được chọn -->
                    <div id="selected_device_info" class="mt-4 p-4 border border-gray-200 rounded-lg bg-gray-50 hidden">
                        <h3 class="font-medium text-gray-700 mb-2">Thông tin thiết bị</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                            <div>
                                <span class="text-gray-500">Mã thiết bị:</span>
                                <span id="device_code_display" class="ml-1 font-medium"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Tên thiết bị:</span>
                                <span id="device_name_display" class="ml-1 font-medium"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Serial:</span>
                                <span id="device_serial_display" class="ml-1 font-medium"></span>
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
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày</th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mô tả</th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kỹ thuật viên</th>
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
                            <label for="repair_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại sửa chữa <span class="text-red-500">*</span></label>
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
                            <label for="repair_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày sửa chữa <span class="text-red-500">*</span></label>
                            <input type="date" id="repair_date" name="repair_date" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="technician_name" class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật viên <span class="text-red-500">*</span></label>
                            <input type="text" id="technician_name" name="technician_name" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập tên kỹ thuật viên">
                        </div>
                        <div>
                            <label for="repair_status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái <span class="text-red-500">*</span></label>
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
                        <label for="repair_description" class="block text-sm font-medium text-gray-700 mb-1 required">Mô tả sửa chữa <span class="text-red-500">*</span></label>
                        <textarea id="repair_description" name="repair_description" rows="3" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập mô tả chi tiết về vấn đề và cách sửa chữa"></textarea>
                    </div>

                    <!-- Linh kiện thay thế -->
                    <div class="mt-4">
                        <h3 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-microchip text-blue-500 mr-2"></i>
                            Linh kiện thay thế
                        </h3>
                        
                        <div id="parts-container">
                            <div class="part-item border border-gray-200 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label for="part_name_1" class="block text-sm font-medium text-gray-700 mb-1">Tên linh kiện</label>
                                        <input type="text" id="part_name_1" name="part_name[]"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Tên linh kiện">
                                    </div>
                                    <div>
                                        <label for="part_code_1" class="block text-sm font-medium text-gray-700 mb-1">Mã linh kiện</label>
                                        <input type="text" id="part_code_1" name="part_code[]"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Mã linh kiện">
                                    </div>
                                    <div>
                                        <label for="part_quantity_1" class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                        <input type="number" id="part_quantity_1" name="part_quantity[]" min="1"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="1">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" class="bg-red-100 text-red-600 p-2 rounded-lg hover:bg-red-200 transition-colors part-remove hidden">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" id="add-part" class="mt-2 bg-blue-100 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-200 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Thêm linh kiện
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="repair_cost" class="block text-sm font-medium text-gray-700 mb-1">Chi phí sửa chữa</label>
                            <div class="relative">
                                <input type="number" id="repair_cost" name="repair_cost" min="0" 
                                    class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="0">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">VNĐ</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="next_maintenance" class="block text-sm font-medium text-gray-700 mb-1">Bảo trì tiếp theo</label>
                            <input type="date" id="next_maintenance" name="next_maintenance"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                        <label for="repair_photos" class="block text-sm font-medium text-gray-700 mb-1">Hình ảnh</label>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy các elements
            const warrantyCodeInput = document.getElementById('warranty_code');
            const searchWarrantyBtn = document.getElementById('search_warranty');
            const customerNameInput = document.getElementById('customer_name');
            const deviceSelect = document.getElementById('device_select');
            const selectedDeviceInfo = document.getElementById('selected_device_info');
            const deviceCodeDisplay = document.getElementById('device_code_display');
            const deviceNameDisplay = document.getElementById('device_name_display');
            const deviceSerialDisplay = document.getElementById('device_serial_display');
            const repairHistory = document.getElementById('repair_history');
            const repairHistoryBody = document.getElementById('repair_history_body');
            
            // Giả lập dữ liệu mẫu
            const sampleData = {
                "W12345": {
                    customer_name: "Công ty TNHH ABC",
                    devices: [
                        { id: 1, code: "DEV001", name: "Bộ điều khiển chính", serial: "SN001122" },
                        { id: 2, code: "DEV002", name: "Cảm biến nhiệt độ", serial: "SN002233" }
                    ],
                    repair_history: [
                        { date: "2023-05-15", type: "Bảo trì định kỳ", description: "Kiểm tra và vệ sinh thiết bị", technician: "Nguyễn Văn A" },
                        { date: "2023-08-20", type: "Sửa chữa lỗi", description: "Thay thế cảm biến bị lỗi", technician: "Trần Văn B" }
                    ]
                },
                "W67890": {
                    customer_name: "Công ty CP XYZ",
                    devices: [
                        { id: 3, code: "DEV003", name: "Màn hình giám sát", serial: "SN003344" }
                    ],
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
                    deviceSelect.innerHTML = '<option value="">-- Chọn thiết bị --</option>';
                    data.devices.forEach(device => {
                        deviceSelect.innerHTML += `<option value="${device.id}" 
                            data-code="${device.code}" 
                            data-name="${device.name}" 
                            data-serial="${device.serial}">
                            ${device.code} - ${device.name}
                        </option>`;
                    });
                    
                    // Hiển thị lịch sử sửa chữa nếu có
                    if (data.repair_history.length > 0) {
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
                    deviceSelect.innerHTML = '<option value="">-- Chọn thiết bị --</option>';
                    selectedDeviceInfo.classList.add('hidden');
                    repairHistory.classList.add('hidden');
                }
            });
            
            // Xử lý sự kiện chọn thiết bị
            deviceSelect.addEventListener('change', function() {
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    deviceCodeDisplay.textContent = selectedOption.dataset.code;
                    deviceNameDisplay.textContent = selectedOption.dataset.name;
                    deviceSerialDisplay.textContent = selectedOption.dataset.serial;
                    selectedDeviceInfo.classList.remove('hidden');
                } else {
                    selectedDeviceInfo.classList.add('hidden');
                }
            });
            
            // Xử lý thêm/xóa linh kiện
            const partsContainer = document.getElementById('parts-container');
            const addPartBtn = document.getElementById('add-part');
            let partCount = 1;
            
            addPartBtn.addEventListener('click', function() {
                partCount++;
                
                const partItem = document.createElement('div');
                partItem.className = 'part-item border border-gray-200 rounded-lg p-4 mb-4';
                partItem.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="part_name_${partCount}" class="block text-sm font-medium text-gray-700 mb-1">Tên linh kiện</label>
                            <input type="text" id="part_name_${partCount}" name="part_name[]"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Tên linh kiện">
                        </div>
                        <div>
                            <label for="part_code_${partCount}" class="block text-sm font-medium text-gray-700 mb-1">Mã linh kiện</label>
                            <input type="text" id="part_code_${partCount}" name="part_code[]"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Mã linh kiện">
                        </div>
                        <div>
                            <label for="part_quantity_${partCount}" class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                            <input type="number" id="part_quantity_${partCount}" name="part_quantity[]" min="1"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="1">
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="bg-red-100 text-red-600 p-2 rounded-lg hover:bg-red-200 transition-colors part-remove">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                `;
                
                partsContainer.appendChild(partItem);
                
                // Hiển thị nút xóa cho linh kiện đầu tiên nếu có nhiều hơn 1
                if (partCount === 2) {
                    document.querySelector('.part-remove').classList.remove('hidden');
                }
                
                // Thêm sự kiện xóa linh kiện
                const removeButtons = document.querySelectorAll('.part-remove');
                removeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        this.closest('.part-item').remove();
                        partCount--;
                        
                        // Ẩn nút xóa nếu chỉ còn 1 linh kiện
                        if (partCount === 1) {
                            document.querySelector('.part-remove').classList.add('hidden');
                        }
                    });
                });
            });
            
            // Tự động đặt ngày hiện tại cho ngày sửa chữa
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('repair_date').value = today;
        });
    </script>
</body>

</html> 