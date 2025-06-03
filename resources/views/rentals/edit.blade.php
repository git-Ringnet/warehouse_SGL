<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu cho thuê - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .sidebar {
            background: linear-gradient(180deg, #1a365d 0%, #0f2942 100%);
            transition: all 0.3s ease;
        }
        .content-area {
            margin-left: 256px;
            min-height: 100vh;
            background: #f8fafc;
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
                width: 70px;
            }
            .content-area {
                margin-left: 0 !important;
            }
        }
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu cho thuê</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    RNT-2406001
                </div>
            </div>
            <a href="{{ url('/rentals/1') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>
        
        <main class="p-6">
            <form action="{{ url('/rentals/1') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Mã phiếu cho thuê -->
                    <div>
                        <label for="rental_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu</label>
                        <input type="text" name="rental_code" id="rental_code" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100" value="RNT-2406001" readonly>
                    </div>

                    <!-- Khách hàng -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1 required">Khách hàng</label>
                        <select name="customer_id" id="customer_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Chọn khách hàng</option>
                            <option value="1" selected>Công ty ABC</option>
                            <option value="2">Công ty XYZ</option>
                            <option value="3">Công ty DEF</option>
                            <option value="4">Công ty GHI</option>
                        </select>
                    </div>

                    <!-- Thiết bị cho thuê -->
                    <div class="md:col-span-2">
                        <label for="equipment_section" class="block text-sm font-medium text-gray-700 mb-1 required">Thiết bị cho thuê</label>
                        <div id="equipment_section" class="border border-gray-300 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 equipment-row">
                                <div>
                                    <label for="equipment_id_0" class="block text-sm font-medium text-gray-700 mb-1">Thiết bị</label>
                                    <select name="equipment[0][id]" id="equipment_id_0" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Chọn thiết bị</option>
                                        <option value="1" selected>Camera Dome 2MP</option>
                                        <option value="2">Camera PTZ 5MP</option>
                                        <option value="3">Đầu ghi NVR 8 kênh</option>
                                        <option value="4">Bộ đàm</option>
                                        <option value="5">Thiết bị đo lường</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="equipment_quantity_0" class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                    <input type="number" name="equipment[0][quantity]" id="equipment_quantity_0" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="5">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 equipment-row">
                                <div>
                                    <label for="equipment_id_1" class="block text-sm font-medium text-gray-700 mb-1">Thiết bị</label>
                                    <select name="equipment[1][id]" id="equipment_id_1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Chọn thiết bị</option>
                                        <option value="1">Camera Dome 2MP</option>
                                        <option value="2">Camera PTZ 5MP</option>
                                        <option value="3" selected>Đầu ghi NVR 8 kênh</option>
                                        <option value="4">Bộ đàm</option>
                                        <option value="5">Thiết bị đo lường</option>
                                    </select>
                                </div>
                                <div class="flex items-end space-x-2">
                                    <div class="flex-grow">
                                        <label for="equipment_quantity_1" class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                        <input type="number" name="equipment[1][quantity]" id="equipment_quantity_1" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="1">
                                    </div>
                                    <button type="button" class="remove-equipment h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" id="add_equipment" class="px-3 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Thêm thiết bị
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Ngày cho thuê -->
                    <div>
                        <label for="rental_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày cho thuê</label>
                        <input type="date" name="rental_date" id="rental_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="2024-06-01">
                    </div>

                    <!-- Ngày hẹn trả -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày hẹn trả</label>
                        <input type="date" name="due_date" id="due_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="2024-06-30">
                    </div>

                    <!-- Trạng thái -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái</label>
                        <select name="status" id="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="active" selected>Đang cho thuê</option>
                            <option value="pending">Chờ xử lý</option>
                            <option value="returned">Đã trả</option>
                            <option value="overdue">Quá hạn</option>
                        </select>
                    </div>

                    <!-- Người liên hệ -->
                    <div>
                        <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">Người liên hệ</label>
                        <input type="text" name="contact_name" id="contact_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Nguyễn Văn A">
                    </div>

                    <!-- Số điện thoại liên hệ -->
                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại liên hệ</label>
                        <input type="text" name="contact_phone" id="contact_phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="0912345678">
                    </div>

                    <!-- Kho -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Kho</label>
                        <select name="status" id="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="active" selected>Kho 1</option>
                            <option value="active" selected>Kho 2</option>
                            <option value="returned">Kho 3</option>
                        </select>
                    </div>

                    <!-- Ghi chú -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">Khách hàng yêu cầu giao thiết bị vào buổi sáng. Đã đặt cọc 50% giá trị thuê.</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ url('/rentals/1') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Hủy
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-save mr-2"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Add more equipment
        let equipmentCount = 2; // Start at 2 since we already have rows 0 and 1
        
        document.getElementById('add_equipment').addEventListener('click', function() {
            const equipmentSection = document.getElementById('equipment_section');
            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 equipment-row';
            
            newRow.innerHTML = `
                <div>
                    <label for="equipment_id_${equipmentCount}" class="block text-sm font-medium text-gray-700 mb-1">Thiết bị</label>
                    <select name="equipment[${equipmentCount}][id]" id="equipment_id_${equipmentCount}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Chọn thiết bị</option>
                        <option value="1">Camera Dome 2MP</option>
                        <option value="2">Camera PTZ 5MP</option>
                        <option value="3">Đầu ghi NVR 8 kênh</option>
                        <option value="4">Bộ đàm</option>
                        <option value="5">Thiết bị đo lường</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <div class="flex-grow">
                        <label for="equipment_quantity_${equipmentCount}" class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                        <input type="number" name="equipment[${equipmentCount}][quantity]" id="equipment_quantity_${equipmentCount}" required min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="button" class="remove-equipment h-10 w-10 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                    </button>
                </div>
            `;
            
            // Insert before the button row
            const addButton = document.getElementById('add_equipment').parentNode;
            equipmentSection.insertBefore(newRow, addButton);
            
            equipmentCount++;
        });
        
        // Add event listener to remove buttons
        document.querySelectorAll('.remove-equipment').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.equipment-row').remove();
            });
        });
    </script>
</body>
</html> 