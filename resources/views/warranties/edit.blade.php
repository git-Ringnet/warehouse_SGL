<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa bảo hành - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa bảo hành</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    ID: DEV-SPA-001
                </div>
            </div>
            <a href="{{ asset('warranties/show') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <form action="#" method="POST">
                @csrf
                @method('PUT')
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-microchip text-blue-500 mr-2"></i>
                        Thông tin thiết bị
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="device_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã thiết bị <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" id="device_code" name="device_code" required value="DEV-SPA-001"
                                    class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-gray-500"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="device_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên thiết bị <span class="text-red-500">*</span></label>
                            <input type="text" id="device_name" name="device_name" required value="Radio SPA Pro"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="device_serial" class="block text-sm font-medium text-gray-700 mb-1 required">Serial <span class="text-red-500">*</span></label>
                            <input type="text" id="device_serial" name="device_serial" required value="SER123456"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="manufacture_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày sản xuất</label>
                            <input type="date" id="manufacture_date" name="manufacture_date" value="2022-12-15"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="device_description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả thiết bị</label>
                        <textarea id="device_description" name="device_description" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">Radio SPA Pro là thiết bị viễn thông thế hệ mới nhất của SGL, được trang bị module GPS NEO-6M và hệ thống thu phát sóng hai chiều tiên tiến. Sản phẩm phù hợp cho các công trình viễn thông và trạm phát sóng.</textarea>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-blue-500 mr-2"></i>
                        Thông tin khách hàng
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên khách hàng <span class="text-red-500">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" required value="Công ty TNHH ABC"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Dự án</label>
                            <input type="text" id="project_name" name="project_name" value="Dự án Viễn thông Hà Nội"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1 required">Người liên hệ <span class="text-red-500">*</span></label>
                            <input type="text" id="contact_name" name="contact_name" required value="Nguyễn Văn A"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1 required">Số điện thoại <span class="text-red-500">*</span></label>
                            <input type="tel" id="contact_phone" name="contact_phone" required value="0912345678"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="contact_email" name="contact_email" value="contact@abc.com.vn"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                            <input type="text" id="customer_address" name="customer_address" value="Số 123, Đường Lê Lợi, Quận Hoàn Kiếm, Hà Nội"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                                <option value="premium" selected>Premium - 2 năm</option>
                                <option value="extended">Mở rộng - 3 năm</option>
                            </select>
                        </div>
                        <div>
                            <label for="activation_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kích hoạt <span class="text-red-500">*</span></label>
                            <input type="text" id="activation_date" name="activation_date" placeholder="dd/mm/yyyy" name="activation_date" required value="2023-01-01"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày hết hạn <span class="text-red-500">*</span></label>
                            <input type="date" id="expiry_date" name="expiry_date" required value="2025-01-01"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="warranty_status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái <span class="text-red-500">*</span></label>
                            <select id="warranty_status" name="warranty_status" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="active" selected>Đang bảo hành</option>
                                <option value="expired">Hết hạn</option>
                                <option value="pending">Chờ kích hoạt</option>
                                <option value="canceled">Đã hủy</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label for="warranty_notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="warranty_notes" name="warranty_notes" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">Gói Premium bảo hành đầy đủ các thành phần, hỗ trợ kỹ thuật 24/7.</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ asset('warranties/show') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Module management
            const modulesContainer = document.getElementById('modules-container');
            const addModuleBtn = document.getElementById('add-module');
            let moduleCount = 4; // Current number of modules
            
            // Initialize remove module buttons
            const removeButtons = document.querySelectorAll('.module-remove');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (document.querySelectorAll('.module-item').length > 1) {
                        this.closest('.module-item').remove();
                    } else {
                        alert('Bạn phải có ít nhất một module!');
                    }
                });
            });
            
            addModuleBtn.addEventListener('click', function() {
                moduleCount++;
                
                const moduleItem = document.createElement('div');
                moduleItem.className = 'module-item border border-gray-200 rounded-lg p-4 mb-4';
                moduleItem.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="module_code_${moduleCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Mã module <span class="text-red-500">*</span></label>
                            <input type="text" id="module_code_${moduleCount}" name="module_code[]" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Mã module">
                        </div>
                        <div>
                            <label for="module_name_${moduleCount}" class="block text-sm font-medium text-gray-700 mb-1 required">Tên module <span class="text-red-500">*</span></label>
                            <input type="text" id="module_name_${moduleCount}" name="module_name[]" required
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
                
                // Add event listener to the new remove button
                moduleItem.querySelector('.module-remove').addEventListener('click', function() {
                    if (document.querySelectorAll('.module-item').length > 1) {
                        this.closest('.module-item').remove();
                    } else {
                        alert('Bạn phải có ít nhất một module!');
                    }
                });
            });
            
            // Warranty package change handler to update expiry date
            const warrantyPackage = document.getElementById('warranty_package');
            const activationDate = document.getElementById('activation_date');
            const expiryDate = document.getElementById('expiry_date');
            
            function updateExpiryDate() {
                if (activationDate.value && warrantyPackage.value) {
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
                    
                    const startDate = new Date(activationDate.value);
                    const expiry = new Date(startDate);
                    expiry.setFullYear(startDate.getFullYear() + years);
                    expiryDate.value = expiry.toISOString().split('T')[0];
                }
            }
            
            warrantyPackage.addEventListener('change', updateExpiryDate);
            activationDate.addEventListener('change', updateExpiryDate);
        });
    </script>
</body>

</html> 