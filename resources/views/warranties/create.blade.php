<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm bảo hành mới - SGL</title>
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
                <a href="{{ asset('warranties') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Thêm bảo hành mới</h1>
            </div>
        </header>

        <main class="p-6">
            <form action="#" method="POST">
                @csrf
                <!-- Thông tin thiết bị - Bây giờ là container cho nhiều thiết bị -->
                <div id="devices-container">
                    <div class="device-item bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-microchip text-blue-500 mr-2"></i>
                                Thiết bị #<span class="device-number">1</span>
                            </h2>
                            <button type="button" class="device-remove bg-red-100 text-red-600 p-2 rounded-lg hover:bg-red-200 transition-colors hidden">
                                <i class="fas fa-trash"></i> Xóa thiết bị
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="device_code_1" class="block text-sm font-medium text-gray-700 mb-1 required">Mã thiết bị <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="text" id="device_code_1" name="device_code[]" required
                                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Nhập mã thiết bị">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-barcode text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="device_name_1" class="block text-sm font-medium text-gray-700 mb-1 required">Tên thiết bị <span class="text-red-500">*</span></label>
                                <input type="text" id="device_name_1" name="device_name[]" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nhập tên thiết bị">
                            </div>
                            <div>
                                <label for="device_serial_1" class="block text-sm font-medium text-gray-700 mb-1 required">Serial <span class="text-red-500">*</span></label>
                                <input type="text" id="device_serial_1" name="device_serial[]" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nhập serial thiết bị">
                            </div>
                            <div>
                                <label for="manufacture_date_1" class="block text-sm font-medium text-gray-700 mb-1">Ngày sản xuất</label>
                                <input type="date" id="manufacture_date_1" name="manufacture_date[]"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="device_description_1" class="block text-sm font-medium text-gray-700 mb-1">Mô tả thiết bị</label>
                            <textarea id="device_description_1" name="device_description[]" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập mô tả thiết bị"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <button type="button" id="add-device" class="bg-green-100 text-green-600 px-4 py-2 rounded-lg hover:bg-green-200 transition-colors flex items-center">
                        <i class="fas fa-plus mr-2"></i> Thêm thiết bị
                    </button>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-blue-500 mr-2"></i>
                        Thông tin khách hàng
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên khách hàng <span class="text-red-500">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập tên khách hàng">
                        </div>
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Dự án</label>
                            <input type="text" id="project_name" name="project_name"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập tên dự án">
                        </div>
                        <div>
                            <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1 required">Người liên hệ <span class="text-red-500">*</span></label>
                            <input type="text" id="contact_name" name="contact_name" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Tên người liên hệ">
                        </div>
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1 required">Số điện thoại <span class="text-red-500">*</span></label>
                            <input type="tel" id="contact_phone" name="contact_phone" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập số điện thoại">
                        </div>
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="contact_email" name="contact_email"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập địa chỉ email">
                        </div>
                        <div>
                            <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                            <input type="text" id="customer_address" name="customer_address"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập địa chỉ">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                        Thông tin bảo hành
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="warranty_package" class="block text-sm font-medium text-gray-700 mb-1 required">Gói bảo hành <span class="text-red-500">*</span></label>
                            <select id="warranty_package" name="warranty_package" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Chọn gói bảo hành</option>
                                <option value="basic">Cơ bản - 1 năm</option>
                                <option value="standard">Tiêu chuẩn - 2 năm</option>
                                <option value="premium">Premium - 2 năm</option>
                                <option value="extended">Mở rộng - 3 năm</option>
                            </select>
                        </div>
                        <div>
                            <label for="activation_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày kích hoạt</label>
                            <div class="relative">
                                <input type="text" id="activation_date" name="activation_date" placeholder="dd/mm/yyyy"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute top-0 right-0 p-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="activate_now" name="activate_now" checked>
                                        <label class="form-check-label text-xs text-gray-600" for="activate_now">Kích hoạt ngay</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày hết hạn</label>
                            <input type="date" id="expiry_date" name="expiry_date" disabled
                                class="w-full border border-gray-300 bg-gray-100 rounded-lg px-3 py-2 focus:outline-none">
                        </div>
                        <div class="md:col-span-3">
                            <label for="warranty_notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="warranty_notes" name="warranty_notes" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập ghi chú về bảo hành"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ asset('warranties') }}"
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
            let deviceCount = 1;
            const devicesContainer = document.getElementById('devices-container');
            const addDeviceBtn = document.getElementById('add-device');
            
            // Function to add a new device
            addDeviceBtn.addEventListener('click', function() {
                deviceCount++;
                
                const deviceItem = document.createElement('div');
                deviceItem.className = 'device-item bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6';
                
                deviceItem.innerHTML = `
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-microchip text-blue-500 mr-2"></i>
                            Thiết bị #<span class="device-number">${deviceCount}</span>
                        </h2>
                        <button type="button" class="device-remove bg-red-100 text-red-600 p-2 rounded-lg hover:bg-red-200 transition-colors">
                            <i class="fas fa-trash"></i> Xóa thiết bị
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="device_code_${deviceCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Mã thiết bị <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="device_code_${deviceCount}" name="device_code[]" required
                                    class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nhập mã thiết bị">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-gray-500"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="device_name_${deviceCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Tên thiết bị <span class="text-red-500">*</span></label>
                            <input type="text" id="device_name_${deviceCount}" name="device_name[]" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập tên thiết bị">
                        </div>
                        <div>
                            <label for="device_serial_${deviceCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Serial <span class="text-red-500">*</span></label>
                            <input type="text" id="device_serial_${deviceCount}" name="device_serial[]" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập serial thiết bị">
                        </div>
                        <div>
                            <label for="manufacture_date_${deviceCount}" class="block text-sm font-medium text-gray-700 mb-1">Ngày sản xuất</label>
                            <input type="date" id="manufacture_date_${deviceCount}" name="manufacture_date[]"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="device_description_${deviceCount}" class="block text-sm font-medium text-gray-700 mb-1">Mô tả thiết bị</label>
                        <textarea id="device_description_${deviceCount}" name="device_description[]" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập mô tả thiết bị"></textarea>
                    </div>
                `;
                
                devicesContainer.appendChild(deviceItem);
                
                // Show remove button on first device if there's more than one
                if (deviceCount === 2) {
                    document.querySelector('.device-remove').classList.remove('hidden');
                }
                
                // Add event listener to the new device's remove button
                const newDeviceRemoveBtn = deviceItem.querySelector('.device-remove');
                newDeviceRemoveBtn.addEventListener('click', function() {
                    deviceItem.remove();
                    deviceCount--;
                    
                    // Hide remove button on first device if only one remains
                    if (deviceCount === 1) {
                        document.querySelector('.device-remove').classList.add('hidden');
                    }
                    
                    // Renumber remaining devices
                    const deviceNumbers = document.querySelectorAll('.device-number');
                    deviceNumbers.forEach((el, index) => {
                        el.textContent = index + 1;
                    });
                });
                
                // Add event listener for the "Add module" button in this device
                setupAddModuleButton(deviceItem.querySelector('.add-module'));
            });
            
            // Function to set up Add Module buttons
            function setupAddModuleButton(button) {
                button.addEventListener('click', function() {
                    const deviceId = this.getAttribute('data-device-id');
                    const modulesContainer = document.querySelector(`.modules-container[data-device-id="${deviceId}"]`);
                    let moduleCount = modulesContainer.querySelectorAll('.module-item').length + 1;
                    
                    const moduleItem = document.createElement('div');
                    moduleItem.className = 'module-item border border-gray-200 rounded-lg p-4 mb-4';
                    moduleItem.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="module_code_${deviceId}_${moduleCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Mã module <span class="text-red-500">*</span></label>
                                <input type="text" id="module_code_${deviceId}_${moduleCount}" name="module_code[${deviceId}][]" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Mã module">
                            </div>
                            <div>
                                <label for="module_name_${deviceId}_${moduleCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Tên module <span class="text-red-500">*</span></label>
                                <input type="text" id="module_name_${deviceId}_${moduleCount}" name="module_name[${deviceId}][]" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Tên module">
                            </div>
                            <div class="flex items-end">
                                <button type="button" class="bg-red-100 text-red-600 p-2 rounded-lg hover:bg-red-200 transition-colors module-remove">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    `;
                    
                    modulesContainer.appendChild(moduleItem);
                    
                    // Show remove button on first module in this device if more than one
                    if (moduleCount === 2) {
                        const firstModuleRemove = modulesContainer.querySelector('.module-remove');
                        if (firstModuleRemove) {
                            firstModuleRemove.classList.remove('hidden');
                        }
                    }
                    
                    // Add event listener to the new module's remove button
                    const removeButton = moduleItem.querySelector('.module-remove');
                    removeButton.addEventListener('click', function() {
                        this.closest('.module-item').remove();
                        
                        // Update module count
                        const remainingModules = modulesContainer.querySelectorAll('.module-item');
                        
                        // Hide remove button on the only module
                        if (remainingModules.length === 1) {
                            const lastModuleRemove = remainingModules[0].querySelector('.module-remove');
                            if (lastModuleRemove) {
                                lastModuleRemove.classList.add('hidden');
                            }
                        }
                    });
                });
            }
            
            // Initialize the first "Add Module" button
            const initialAddModuleBtn = document.querySelector('.add-module');
            setupAddModuleButton(initialAddModuleBtn);
            
            // Warranty activation and expiry date calculation
            const warrantyPackage = document.getElementById('warranty_package');
            const activationDate = document.getElementById('activation_date');
            const expiryDate = document.getElementById('expiry_date');
            const activateNowCheckbox = document.getElementById('activate_now');
            
            function updateDates() {
                const today = new Date();
                const formattedToday = today.toISOString().split('T')[0];
                
                if (activateNowCheckbox.checked) {
                    activationDate.value = formattedToday;
                    activationDate.setAttribute('disabled', true);
                    
                    if (warrantyPackage.value) {
                        let years = 1; // Default 1 year
                        
                        switch (warrantyPackage.value) {
                            case 'basic':
                                years = 1;
                                break;
                            case 'standard':
                            case 'premium':
                                years = 2;
                                break;
                            case 'extended':
                                years = 3;
                                break;
                        }
                        
                        const expiry = new Date(today);
                        expiry.setFullYear(today.getFullYear() + years);
                        expiryDate.value = expiry.toISOString().split('T')[0];
                    }
                } else {
                    activationDate.removeAttribute('disabled');
                    expiryDate.value = '';
                }
            }
            
            activateNowCheckbox.addEventListener('change', updateDates);
            warrantyPackage.addEventListener('change', updateDates);
            activationDate.addEventListener('change', function() {
                if (!activateNowCheckbox.checked && activationDate.value && warrantyPackage.value) {
                    let years = 1;
                    
                    switch (warrantyPackage.value) {
                        case 'basic':
                            years = 1;
                            break;
                        case 'standard':
                        case 'premium':
                            years = 2;
                            break;
                        case 'extended':
                            years = 3;
                            break;
                    }
                    
                    const startDate = new Date(activationDate.value);
                    const expiry = new Date(startDate);
                    expiry.setFullYear(startDate.getFullYear() + years);
                    expiryDate.value = expiry.toISOString().split('T')[0];
                }
            });
            
            // Initialize dates on load
            updateDates();
        });
    </script>
</body>

</html> 