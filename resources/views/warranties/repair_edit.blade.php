<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa sửa chữa & bảo trì - SGL</title>
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
                <a href="{{ asset('warranties/repair_detail') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa sửa chữa & bảo trì</h1>
            </div>
        </header>

        <main class="p-6">
            <form action="#" method="POST">
                @csrf
                @method('PUT')

                <!-- Thông tin cơ bản -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Thông tin cơ bản
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="repair_id" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu</label>
                            <input type="text" id="repair_id" name="repair_id" value="REP001" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="warranty_code" class="block text-sm font-medium text-gray-700 mb-1">Mã bảo hành</label>
                            <input type="text" id="warranty_code" name="warranty_code" value="W12345" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Khách hàng</label>
                            <input type="text" id="customer_name" name="customer_name" value="Công ty TNHH ABC" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="device_info" class="block text-sm font-medium text-gray-700 mb-1">Thiết bị</label>
                            <input type="text" id="device_info" name="device_info" value="DEV001 - Bộ điều khiển chính" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
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
                                <option value="maintenance" selected>Bảo trì định kỳ</option>
                                <option value="repair">Sửa chữa lỗi</option>
                                <option value="replacement">Thay thế linh kiện</option>
                                <option value="upgrade">Nâng cấp</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label for="repair_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày sửa chữa <span class="text-red-500">*</span></label>
                            <input type="date" id="repair_date" name="repair_date" value="2023-05-15" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="technician_name" class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật viên <span class="text-red-500">*</span></label>
                            <input type="text" id="technician_name" name="technician_name" value="Nguyễn Văn A" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho sửa chữa <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho sửa chữa --</option>
                                <option value="1" selected>Kho chính</option>
                                <option value="2">Kho phụ</option>
                                <option value="3">Kho linh kiện</option>
                                <option value="4">Kho bảo hành</option>
                            </select>
                        </div>
                        <div>
                            <label for="repair_status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái <span class="text-red-500">*</span></label>
                            <select id="repair_status" name="repair_status" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="completed" selected>Hoàn thành</option>
                                <option value="in_progress">Đang tiến hành</option>
                                <option value="pending">Chờ xử lý</option>
                                <option value="canceled">Đã hủy</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="repair_description" class="block text-sm font-medium text-gray-700 mb-1 required">Mô tả sửa chữa <span class="text-red-500">*</span></label>
                        <textarea id="repair_description" name="repair_description" rows="3" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">Tiến hành kiểm tra tổng thể thiết bị, vệ sinh bụi bẩn bên trong và bên ngoài thiết bị. Kiểm tra các kết nối, đầu cắm và phát hiện một số tiếp điểm bị ôxy hóa nhẹ. Đã tiến hành làm sạch và bôi chất chống ôxy hóa.

Thiết bị hoạt động bình thường sau khi bảo trì, không phát hiện lỗi hay vấn đề bất thường nào.</textarea>
                    </div>

                    <!-- Linh kiện thay thế -->
                    <div class="mt-4">
                        <h3 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-microchip text-blue-500 mr-2"></i>
                            Linh kiện thay thế
                        </h3>

                        <div id="parts-container">
                            <!-- Mẫu linh kiện đầu tiên -->
                            <div class="part-item border border-gray-200 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label for="part_name_1" class="block text-sm font-medium text-gray-700 mb-1">Tên linh kiện</label>
                                        <input type="text" id="part_name_1" name="part_name[]" value=""
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="part_code_1" class="block text-sm font-medium text-gray-700 mb-1">Mã linh kiện</label>
                                        <input type="text" id="part_code_1" name="part_code[]" value=""
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="part_quantity_1" class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                        <input type="number" id="part_quantity_1" name="part_quantity[]" min="1" value="1"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" class="bg-red-100 text-red-600 p-2 rounded-lg hover:bg-red-200 transition-colors part-remove hidden">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="add-part" 
                            class="mt-2 bg-blue-100 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-200 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Thêm linh kiện
                        </button>
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
                        
                        <!-- Hiển thị hình ảnh đã tải lên -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-3">
                            <div class="border border-gray-200 rounded-lg overflow-hidden relative">
                                <img src="https://via.placeholder.com/300x200?text=Thiết+bị+trước+khi+bảo+trì" alt="Thiết bị trước khi bảo trì" class="w-full h-auto">
                                <div class="p-2 bg-gray-50 flex justify-between items-center">
                                    <p class="text-sm text-gray-600">Thiết bị trước khi bảo trì</p>
                                    <button type="button" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-lg overflow-hidden relative">
                                <img src="https://via.placeholder.com/300x200?text=Thiết+bị+sau+khi+bảo+trì" alt="Thiết bị sau khi bảo trì" class="w-full h-auto">
                                <div class="p-2 bg-gray-50 flex justify-between items-center">
                                    <p class="text-sm text-gray-600">Thiết bị sau khi bảo trì</p>
                                    <button type="button" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Thêm hình ảnh mới -->
                        <input type="file" id="repair_photos" name="repair_photos[]" multiple accept="image/*"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Tối đa 5 ảnh, kích thước mỗi ảnh không quá 2MB</p>
                    </div>

                    <div>
                        <label for="repair_notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="repair_notes" name="repair_notes" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">Khách hàng phản hồi thiết bị hoạt động tốt sau khi bảo trì. Đề xuất cần kiểm tra lại sau 3 tháng nếu có điều kiện.</textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ asset('warranties/repair_detail') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Cập nhật
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Xử lý xóa hình ảnh
            const imageDeleteButtons = document.querySelectorAll('.text-red-500');
            imageDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Bạn có chắc chắn muốn xóa hình ảnh này?')) {
                        this.closest('.border-gray-200').remove();
                    }
                });
            });
        });
    </script>
</body>

</html> 