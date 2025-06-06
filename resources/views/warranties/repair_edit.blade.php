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
                            <label for="warranty_code" class="block text-sm font-medium text-gray-700 mb-1">Mã Bảo hành
                                hoặc thiết bị</label>
                            <div class="relative">
                                <input type="text" id="warranty_code" name="warranty_code" value="W12345"
                                    class="w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2">
                                <button type="button" id="search_warranty_btn"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-blue-500 hover:text-blue-700">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Khách
                                hàng</label>
                            <input type="text" id="customer_name" name="customer_name" value="Công ty TNHH ABC"
                                readonly class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="device_info" class="block text-sm font-medium text-gray-700 mb-1">Thiết
                                bị</label>
                            <input type="text" id="device_info" name="device_info"
                                value="DEV001 - Bộ điều khiển chính" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                    </div>

                    <!-- Thông tin thiết bị tìm được -->
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
                                <option value="maintenance" selected>Bảo trì định kỳ</option>
                                <option value="repair">Sửa chữa lỗi</option>
                                <option value="replacement">Thay thế linh kiện</option>
                                <option value="upgrade">Nâng cấp</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label for="repair_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày
                                sửa chữa <span class="text-red-500">*</span></label>
                            <input type="date" id="repair_date" name="repair_date" value="2023-05-15" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="technician_name"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Kỹ thuật viên <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="technician_name" name="technician_name" value="Nguyễn Văn A"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="warehouse_id"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Kho linh kiện thay thế <span
                                    class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn Kho linh kiện thay thế --</option>
                                <option value="1" selected>Kho chính</option>
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
                                <option value="completed" selected>Hoàn thành</option>
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
                                        <label for="part_name_1"
                                            class="block text-sm font-medium text-gray-700 mb-1">Tên linh kiện</label>
                                        <input type="text" id="part_name_1" name="part_name[]" value=""
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="part_code_1"
                                            class="block text-sm font-medium text-gray-700 mb-1">Mã linh kiện</label>
                                        <input type="text" id="part_code_1" name="part_code[]" value=""
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="part_quantity_1"
                                            class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                        <input type="number" id="part_quantity_1" name="part_quantity[]"
                                            min="1" value="1"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button"
                                            class="bg-red-100 text-red-600 p-2 rounded-lg hover:bg-red-200 transition-colors part-remove hidden">
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

                    <!-- Chi tiết vật tư thiết bị -->
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <h3 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-list-check text-blue-500 mr-2"></i>
                            Chi tiết vật tư thiết bị
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã vật tư
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên vật tư
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Trạng thái
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tình trạng
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Kho chuyển đến
                                        </th>
                                        <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thao tác
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="device-parts-body">
                                    <!-- Mẫu dữ liệu vật tư -->
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">VT001-A</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">Bo mạch chính
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Hoạt động tốt
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <div class="flex items-center">
                                                <input type="checkbox" id="damaged_part_1" name="damaged_parts[]"
                                                    value="VT001-A"
                                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded damage-checkbox">
                                                <label for="damaged_part_1" class="ml-2 text-sm text-gray-700">Hư
                                                    hỏng</label>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm warehouse-cell">
                                            <select name="part_warehouse_VT001-A"
                                                class="warehouse-select border border-gray-300 rounded-lg px-2 py-1 text-xs w-full hidden">
                                                <option value="">-- Chọn kho --</option>
                                                <option value="3">Kho linh kiện</option>
                                                <option value="4">Kho bảo hành</option>
                                                <option value="5">Kho vật tư hỏng</option>
                                                <option value="6">Kho tái chế</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <button type="button" data-part-id="VT001-A"
                                                class="replace-part-btn bg-yellow-100 text-yellow-600 px-2 py-1 rounded hover:bg-yellow-200 transition-colors text-xs">
                                                <i class="fas fa-exchange-alt mr-1"></i> Thay thế
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">VT001-B</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">Cảm biến nhiệt độ
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Cần kiểm tra
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <div class="flex items-center">
                                                <input type="checkbox" id="damaged_part_2" name="damaged_parts[]"
                                                    value="VT001-B"
                                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded damage-checkbox">
                                                <label for="damaged_part_2" class="ml-2 text-sm text-gray-700">Hư
                                                    hỏng</label>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm warehouse-cell">
                                            <select name="part_warehouse_VT001-B"
                                                class="warehouse-select border border-gray-300 rounded-lg px-2 py-1 text-xs w-full hidden">
                                                <option value="">-- Chọn kho --</option>
                                                <option value="3">Kho linh kiện</option>
                                                <option value="4">Kho bảo hành</option>
                                                <option value="5">Kho vật tư hỏng</option>
                                                <option value="6">Kho tái chế</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <button type="button" data-part-id="VT001-B"
                                                class="replace-part-btn bg-yellow-100 text-yellow-600 px-2 py-1 rounded hover:bg-yellow-200 transition-colors text-xs">
                                                <i class="fas fa-exchange-alt mr-1"></i> Thay thế
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">VT001-C</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">Bộ nhớ Flash</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Hư hỏng
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <div class="flex items-center">
                                                <input type="checkbox" id="damaged_part_3" name="damaged_parts[]"
                                                    value="VT001-C" checked
                                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded damage-checkbox">
                                                <label for="damaged_part_3" class="ml-2 text-sm text-gray-700">Hư
                                                    hỏng</label>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm warehouse-cell">
                                            <select name="part_warehouse_VT001-C"
                                                class="warehouse-select border border-gray-300 rounded-lg px-2 py-1 text-xs w-full">
                                                <option value="">-- Chọn kho --</option>
                                                <option value="3">Kho linh kiện</option>
                                                <option value="4">Kho bảo hành</option>
                                                <option value="5">Kho vật tư hỏng</option>
                                                <option value="6">Kho tái chế</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <button type="button" data-part-id="VT001-C"
                                                class="replace-part-btn bg-yellow-100 text-yellow-600 px-2 py-1 rounded hover:bg-yellow-200 transition-colors text-xs">
                                                <i class="fas fa-exchange-alt mr-1"></i> Thay thế
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="button" id="add-new-part-btn"
                                class="bg-blue-100 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-200 transition-colors flex items-center">
                                <i class="fas fa-plus mr-2"></i> Thêm vật tư mới
                            </button>
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

                        <!-- Hiển thị hình ảnh đã tải lên -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-3">
                            <div class="border border-gray-200 rounded-lg overflow-hidden relative">
                                <img src="https://via.placeholder.com/300x200?text=Thiết+bị+trước+khi+bảo+trì"
                                    alt="Thiết bị trước khi bảo trì" class="w-full h-auto">
                                <div class="p-2 bg-gray-50 flex justify-between items-center">
                                    <p class="text-sm text-gray-600">Thiết bị trước khi bảo trì</p>
                                    <button type="button" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-lg overflow-hidden relative">
                                <img src="https://via.placeholder.com/300x200?text=Thiết+bị+sau+khi+bảo+trì"
                                    alt="Thiết bị sau khi bảo trì" class="w-full h-auto">
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

    <!-- Hộp thông báo chuyển kho vật tư hỏng -->
    <div id="transfer-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Chuyển vật tư hỏng</h3>
                <button type="button" id="close-transfer-modal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-gray-700 mb-2">Các vật tư hư hỏng sẽ được chuyển đến kho:</p>
                <select id="damaged_warehouse_id" name="damaged_warehouse_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Chọn kho chuyển --</option>
                    <option value="3">Kho linh kiện</option>
                    <option value="4">Kho bảo hành</option>
                    <option value="5">Kho vật tư hỏng</option>
                    <option value="6">Kho tái chế</option>
                </select>
            </div>

            <div class="border-t border-gray-200 pt-4 mb-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Danh sách vật tư hỏng:</h4>
                <ul class="list-disc pl-5 text-sm text-gray-600" id="damaged-parts-list">
                    <li>VT001-C - Bộ nhớ Flash</li>
                </ul>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" id="cancel-transfer-btn"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Hủy
                </button>
                <button type="button" id="confirm-transfer-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-check mr-2"></i> Xác nhận chuyển
                </button>
            </div>
        </div>
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

    <!-- Hộp thông báo thêm vật tư mới -->
    <div id="new-part-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Thêm vật tư mới</h3>
                <button type="button" id="close-new-part-modal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="add_part_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã
                            vật tư <span class="text-red-500">*</span></label>
                        <input type="text" id="add_part_code" name="add_part_code" placeholder="Nhập mã vật tư"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="add_part_name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên
                            vật tư <span class="text-red-500">*</span></label>
                        <input type="text" id="add_part_name" name="add_part_name" placeholder="Nhập tên vật tư"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="add_part_status"
                            class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái <span
                                class="text-red-500">*</span></label>
                        <select id="add_part_status" name="add_part_status"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="good">Hoạt động tốt</option>
                            <option value="check">Cần kiểm tra</option>
                            <option value="damaged">Hư hỏng</option>
                        </select>
                    </div>
                    <div>
                        <label for="add_part_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi
                            chú</label>
                        <textarea id="add_part_note" name="add_part_note" rows="2" placeholder="Nhập ghi chú về vật tư mới"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" id="cancel-new-part-btn"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Hủy
                </button>
                <button type="button" id="confirm-new-part-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i> Thêm vật tư
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý tìm kiếm theo mã bảo hành hoặc thiết bị
            const warrantyCodeInput = document.getElementById('warranty_code');
            const searchWarrantyBtn = document.getElementById('search_warranty_btn');
            const devicesContainer = document.getElementById('devices_container');
            const devicesList = document.getElementById('devices_list');
            const devicePartsBody = document.getElementById('device-parts-body');
            const customerNameInput = document.getElementById('customer_name');
            const deviceInfoInput = document.getElementById('device_info');

            // Dữ liệu mẫu cho demo (trong thực tế sẽ được lấy từ API)
            const sampleData = {
                "W12345": {
                    customer_name: "Công ty TNHH ABC",
                    devices: [{
                            id: 1,
                            code: "DEV001",
                            name: "Bộ điều khiển chính",
                            serial: "SN001122",
                            status: "active",
                            parts: [{
                                    code: "VT001-A",
                                    name: "Bo mạch chính",
                                    status: "good"
                                },
                                {
                                    code: "VT001-B",
                                    name: "Cảm biến nhiệt độ",
                                    status: "check"
                                },
                                {
                                    code: "VT001-C",
                                    name: "Bộ nhớ Flash",
                                    status: "damaged"
                                }
                            ]
                        },
                        {
                            id: 2,
                            code: "DEV002",
                            name: "Cảm biến nhiệt độ",
                            serial: "SN002233",
                            status: "active",
                            parts: [{
                                    code: "VT002-A",
                                    name: "Bo mạch cảm biến",
                                    status: "good"
                                },
                                {
                                    code: "VT002-B",
                                    name: "Đầu đo nhiệt",
                                    status: "good"
                                },
                                {
                                    code: "VT002-C",
                                    name: "Bộ khuếch đại tín hiệu",
                                    status: "check"
                                }
                            ]
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
                        status: "maintenance",
                        parts: [{
                                code: "VT003-A",
                                name: "Màn hình LCD",
                                status: "damaged"
                            },
                            {
                                code: "VT003-B",
                                name: "Bo mạch điều khiển",
                                status: "check"
                            },
                            {
                                code: "VT003-C",
                                name: "Nguồn cấp",
                                status: "good"
                            }
                        ]
                    }]
                },
                "DEV001": {
                    customer_name: "Công ty TNHH ABC",
                    devices: [{
                        id: 1,
                        code: "DEV001",
                        name: "Bộ điều khiển chính",
                        serial: "SN001122",
                        status: "active",
                        parts: [{
                                code: "VT001-A",
                                name: "Bo mạch chính",
                                status: "good"
                            },
                            {
                                code: "VT001-B",
                                name: "Cảm biến nhiệt độ",
                                status: "check"
                            },
                            {
                                code: "VT001-C",
                                name: "Bộ nhớ Flash",
                                status: "damaged"
                            }
                        ]
                    }]
                }
            };

            // Ẩn container thiết bị ban đầu
            devicesContainer.style.display = 'none';

            // Xử lý sự kiện tìm kiếm
            searchWarrantyBtn.addEventListener('click', function() {
                searchWarrantyOrDevice();
            });

            // Tìm kiếm khi nhấn Enter
            warrantyCodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchWarrantyOrDevice();
                }
            });

            function searchWarrantyOrDevice() {
                const searchCode = warrantyCodeInput.value.trim();

                if (!searchCode) {
                    alert('Vui lòng nhập mã bảo hành hoặc mã thiết bị!');
                    return;
                }

                if (sampleData[searchCode]) {
                    // Hiển thị container thiết bị
                    devicesContainer.style.display = 'block';

                    // Cập nhật thông tin khách hàng
                    customerNameInput.value = sampleData[searchCode].customer_name;

                    // Xóa danh sách thiết bị cũ
                    devicesList.innerHTML = '';

                    // Thêm thiết bị mới vào danh sách
                    sampleData[searchCode].devices.forEach(device => {
                        const row = document.createElement('tr');

                        // Status class và text
                        let statusClass, statusText;
                        switch (device.status) {
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
                                statusClass = 'bg-gray-100 text-gray-800';
                                statusText = 'Không xác định';
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

                    // Thêm sự kiện chọn thiết bị
                    const selectDeviceBtns = document.querySelectorAll('.select-device-btn');
                    selectDeviceBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                            const deviceId = this.getAttribute('data-device-id');
                            const deviceCode = this.getAttribute('data-device-code');

                            // Tìm thiết bị trong dữ liệu
                            let selectedDevice;
                            for (const key in sampleData) {
                                const foundDevice = sampleData[key].devices.find(d => d.id ==
                                    deviceId);
                                if (foundDevice) {
                                    selectedDevice = foundDevice;
                                    break;
                                }
                            }

                            if (selectedDevice) {
                                // Cập nhật thông tin thiết bị được chọn
                                deviceInfoInput.value =
                                    `${selectedDevice.code} - ${selectedDevice.name}`;

                                // Hiển thị linh kiện của thiết bị
                                showDeviceParts(selectedDevice);
                            }
                        });
                    });

                    // Nếu chỉ có 1 thiết bị, tự động chọn
                    if (sampleData[searchCode].devices.length === 1) {
                        const device = sampleData[searchCode].devices[0];
                        deviceInfoInput.value = `${device.code} - ${device.name}`;
                        showDeviceParts(device);
                    } else {
                        // Xóa thông tin thiết bị nếu có nhiều thiết bị
                        deviceInfoInput.value = '';
                        devicePartsBody.innerHTML = '';
                    }
                } else {
                    alert('Không tìm thấy thông tin với mã này!');
                }
            }

            // Hiển thị danh sách linh kiện của thiết bị được chọn
            function showDeviceParts(device) {
                // Xóa danh sách linh kiện cũ
                devicePartsBody.innerHTML = '';

                // Thêm linh kiện mới vào danh sách
                device.parts.forEach((part, index) => {
                    const row = document.createElement('tr');

                    // Status class và text
                    let statusClass, statusText;
                    switch (part.status) {
                        case 'good':
                            statusClass = 'bg-green-100 text-green-800';
                            statusText = 'Hoạt động tốt';
                            break;
                        case 'check':
                            statusClass = 'bg-yellow-100 text-yellow-800';
                            statusText = 'Cần kiểm tra';
                            break;
                        case 'damaged':
                            statusClass = 'bg-red-100 text-red-800';
                            statusText = 'Hư hỏng';
                            break;
                        default:
                            statusClass = 'bg-gray-100 text-gray-800';
                            statusText = 'Không xác định';
                    }

                    const isDamaged = part.status === 'damaged';

                    row.innerHTML = `
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${part.code}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${part.name}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                ${statusText}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <div class="flex items-center">
                                <input type="checkbox" id="damaged_part_${index + 1}" name="damaged_parts[]" value="${part.code}" 
                                    ${isDamaged ? 'checked' : ''}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded damage-checkbox">
                                <label for="damaged_part_${index + 1}" class="ml-2 text-sm text-gray-700">Hư hỏng</label>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm warehouse-cell">
                            <select name="part_warehouse_${part.code}" class="warehouse-select border border-gray-300 rounded-lg px-2 py-1 text-xs w-full ${!isDamaged ? 'hidden' : ''}">
                                <option value="">-- Chọn kho --</option>
                                <option value="3">Kho linh kiện</option>
                                <option value="4">Kho bảo hành</option>
                                <option value="5" ${isDamaged ? 'selected' : ''}>Kho vật tư hỏng</option>
                                <option value="6">Kho tái chế</option>
                            </select>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <button type="button" data-part-id="${part.code}"
                                class="replace-part-btn bg-yellow-100 text-yellow-600 px-2 py-1 rounded hover:bg-yellow-200 transition-colors text-xs">
                                <i class="fas fa-exchange-alt mr-1"></i> Thay thế
                            </button>
                        </td>
                    `;

                    devicePartsBody.appendChild(row);
                });

                // Cập nhật đăng ký sự kiện cho các checkbox và nút thay thế mới
                updateDamagedPartsCheckboxes();

                // Cập nhật sự kiện cho các nút thay thế
                const replacePartBtns = document.querySelectorAll('.replace-part-btn');
                replacePartBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const partId = this.getAttribute('data-part-id');
                        showReplacePartDialog(partId);
                    });
                });

                // Xử lý các checkbox đánh dấu hư hỏng và chọn kho
                function setupDamageCheckboxListeners() {
                    const damageCheckboxes = document.querySelectorAll('.damage-checkbox');
                    damageCheckboxes.forEach(checkbox => {
                        // Thiết lập trạng thái ban đầu
                        const partCode = checkbox.value;
                        const warehouseSelect = document.querySelector(
                            `select[name="part_warehouse_${partCode}"]`);
                        if (warehouseSelect) {
                            warehouseSelect.classList.toggle('hidden', !checkbox.checked);
                        }

                        // Xử lý sự kiện thay đổi
                        checkbox.addEventListener('change', function() {
                            const partCode = this.value;
                            const warehouseSelect = document.querySelector(
                                `select[name="part_warehouse_${partCode}"]`);
                            if (warehouseSelect) {
                                warehouseSelect.classList.toggle('hidden', !this.checked);
                                if (this.checked && warehouseSelect.value === '') {
                                    // Mặc định chọn "Kho vật tư hỏng" khi đánh dấu hư hỏng
                                    warehouseSelect.value = '5';
                                }
                            }

                            // Cập nhật danh sách vật tư hư hỏng
                            updateDamagedPartsList();
                        });
                    });
                }

                // Thiết lập các sự kiện cho checkbox khi trang được tải
                setupDamageCheckboxListeners();
            }

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

            // Xử lý các vật tư thiết bị
            const damagedCheckboxes = document.querySelectorAll('input[name="damaged_parts[]"]');
            const replacePartBtns = document.querySelectorAll('.replace-part-btn');
            const addNewPartBtn = document.getElementById('add-new-part-btn');
            const submitBtn = document.getElementById('submit-btn');

            // Modal chuyển kho
            const transferModal = document.getElementById('transfer-modal');
            const closeTransferModalBtn = document.getElementById('close-transfer-modal');
            const cancelTransferBtn = document.getElementById('cancel-transfer-btn');
            const confirmTransferBtn = document.getElementById('confirm-transfer-btn');
            const damagedPartsList = document.getElementById('damaged-parts-list');

            // Mảng lưu trữ các vật tư hư hỏng đã chọn
            let selectedDamagedParts = [];

            // Cập nhật trạng thái khi checkbox thay đổi
            damagedCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateDamagedPartsList();
                });
            });

            // Xử lý thay thế vật tư
            replacePartBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const partId = this.getAttribute('data-part-id');
                    showReplacePartDialog(partId);
                });
            });

            // Xử lý thêm vật tư mới
            addNewPartBtn.addEventListener('click', function() {
                showAddNewPartDialog();
            });

            // Xử lý nút Cập nhật - kiểm tra các vật tư hư hỏng có chọn kho chưa
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // Kiểm tra tất cả các vật tư được đánh dấu hư hỏng đã chọn kho chưa
                let allWarehousesSelected = true;
                let unselectedParts = [];

                const damagedCheckboxes = document.querySelectorAll('.damage-checkbox:checked');
                damagedCheckboxes.forEach(checkbox => {
                    const partCode = checkbox.value;
                    const warehouseSelect = document.querySelector(
                        `select[name="part_warehouse_${partCode}"]`);
                    if (warehouseSelect && warehouseSelect.value === '') {
                        allWarehousesSelected = false;
                        // Lấy tên vật tư
                        const partRow = checkbox.closest('tr');
                        const partName = partRow.cells[1].textContent;
                        unselectedParts.push(`${partCode} - ${partName}`);
                    }
                });

                if (!allWarehousesSelected) {
                    alert(
                        `Vui lòng chọn kho chuyển đến cho các vật tư hư hỏng sau:\n${unselectedParts.join('\n')}`);
                    return;
                }

                // Nếu không có vật tư hư hỏng hoặc tất cả đã chọn kho thì submit form
                document.querySelector('form').submit();
            });

            // Xử lý đóng modal
            closeTransferModalBtn.addEventListener('click', closeTransferModal);
            cancelTransferBtn.addEventListener('click', closeTransferModal);

            // Xử lý xác nhận chuyển kho
            confirmTransferBtn.addEventListener('click', function() {
                const selectedWarehouse = document.getElementById('damaged_warehouse_id').value;

                if (!selectedWarehouse) {
                    alert('Vui lòng chọn kho để chuyển vật tư hỏng!');
                    return;
                }

                // Thêm input ẩn để lưu thông tin kho chuyển
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'damaged_warehouse_id';
                hiddenInput.value = selectedWarehouse;
                document.querySelector('form').appendChild(hiddenInput);

                // Submit form
                document.querySelector('form').submit();
            });

            // Cập nhật danh sách vật tư hỏng được chọn
            function updateDamagedPartsList() {
                selectedDamagedParts = [];
                damagedPartsList.innerHTML = '';

                damagedCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const partRow = checkbox.closest('tr');
                        const partCode = partRow.cells[0].textContent;
                        const partName = partRow.cells[1].textContent;

                        selectedDamagedParts.push({
                            code: partCode,
                            name: partName
                        });

                        // Thêm vào danh sách hiển thị trong modal
                        const listItem = document.createElement('li');
                        listItem.textContent = `${partCode} - ${partName}`;
                        damagedPartsList.appendChild(listItem);
                    }
                });
            }

            // Hiển thị modal chuyển kho
            function showTransferModal() {
                transferModal.classList.remove('hidden');
            }

            // Đóng modal chuyển kho
            function closeTransferModal() {
                transferModal.classList.add('hidden');
            }

            // Hiển thị dialog thay thế vật tư
            function showReplacePartDialog(partId) {
                // Tìm thông tin vật tư từ ID
                const partRow = document.querySelector(`button[data-part-id="${partId}"]`).closest('tr');
                const partCode = partRow.cells[0].textContent;
                const partName = partRow.cells[1].textContent;

                // Cập nhật thông tin trong modal
                document.getElementById('old-part-code').textContent = partCode;
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

                    // Đánh dấu vật tư cũ là hư hỏng
                    const damagedCheckbox = partRow.querySelector('input[type="checkbox"]');
                    damagedCheckbox.checked = true;

                    // Trong thực tế sẽ gọi API để lưu thay đổi và cập nhật lại dữ liệu bảng

                    // Hiển thị thông báo chuyển vật tư thành công
                    const warehouseSelect = document.getElementById('replace_warehouse_id');
                    const warehouseName = warehouseSelect.options[warehouseSelect.selectedIndex].text;

                    // Cập nhật UI để hiển thị trạng thái đã thay thế
                    const statusCell = partRow.cells[2];
                    statusCell.innerHTML = `
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            Đã thay thế
                        </span>
                    `;

                    alert(
                        `Đã thay thế vật tư ${partCode} bằng ${newPartCode}. Vật tư cũ đã được chuyển đến ${warehouseName}.`);

                    // Đóng modal
                    closeReplaceModal();
                });
            }

            // Hiển thị dialog thêm vật tư mới
            function showAddNewPartDialog() {
                // Hiển thị modal thêm vật tư mới
                const newPartModal = document.getElementById('new-part-modal');
                newPartModal.classList.remove('hidden');

                // Xử lý các sự kiện nút trong modal
                const closeNewPartModalBtn = document.getElementById('close-new-part-modal');
                const cancelNewPartBtn = document.getElementById('cancel-new-part-btn');
                const confirmNewPartBtn = document.getElementById('confirm-new-part-btn');

                // Đóng modal khi click vào nút đóng hoặc hủy
                const closeNewPartModal = () => {
                    newPartModal.classList.add('hidden');
                };

                closeNewPartModalBtn.addEventListener('click', closeNewPartModal);
                cancelNewPartBtn.addEventListener('click', closeNewPartModal);

                // Xử lý xác nhận thêm vật tư mới
                confirmNewPartBtn.addEventListener('click', function() {
                    const partCode = document.getElementById('add_part_code').value;
                    const partName = document.getElementById('add_part_name').value;
                    const partStatus = document.getElementById('add_part_status').value;

                    if (!partCode || !partName) {
                        alert('Vui lòng nhập đầy đủ thông tin vật tư!');
                        return;
                    }

                    // Trong thực tế sẽ gọi API để lưu thông tin vật tư mới
                    // và cập nhật lại dữ liệu bảng

                    // Đóng modal
                    closeNewPartModal();

                    // Thêm vật tư mới vào bảng
                    const tableBody = document.getElementById('device-parts-body');
                    const newRow = document.createElement('tr');

                    const statusLabel = {
                        'good': 'Hoạt động tốt',
                        'check': 'Cần kiểm tra',
                        'damaged': 'Hư hỏng'
                    };

                    const statusClass = {
                        'good': 'bg-green-100 text-green-800',
                        'check': 'bg-yellow-100 text-yellow-800',
                        'damaged': 'bg-red-100 text-red-800'
                    };

                    const isDamaged = partStatus === 'damaged';
                    const newPartId = `new_part_${Date.now()}`;

                    newRow.innerHTML = `
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${partCode}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${partName}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass[partStatus]}">
                                ${statusLabel[partStatus]}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <div class="flex items-center">
                                <input type="checkbox" id="${newPartId}" name="damaged_parts[]" value="${partCode}" 
                                    ${isDamaged ? 'checked' : ''} 
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded damage-checkbox">
                                <label for="${newPartId}" class="ml-2 text-sm text-gray-700">Hư hỏng</label>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm warehouse-cell">
                            <select name="part_warehouse_${partCode}" class="warehouse-select border border-gray-300 rounded-lg px-2 py-1 text-xs w-full ${!isDamaged ? 'hidden' : ''}">
                                <option value="">-- Chọn kho --</option>
                                <option value="3">Kho linh kiện</option>
                                <option value="4">Kho bảo hành</option>
                                <option value="5" ${isDamaged ? 'selected' : ''}>Kho vật tư hỏng</option>
                                <option value="6">Kho tái chế</option>
                            </select>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <button type="button" data-part-id="${partCode}"
                                class="replace-part-btn bg-yellow-100 text-yellow-600 px-2 py-1 rounded hover:bg-yellow-200 transition-colors text-xs">
                                <i class="fas fa-exchange-alt mr-1"></i> Thay thế
                            </button>
                        </td>
                    `;

                    tableBody.appendChild(newRow);

                    // Thêm sự kiện cho nút thay thế mới
                    const newReplaceBtn = newRow.querySelector('.replace-part-btn');
                    newReplaceBtn.addEventListener('click', function() {
                        const partId = this.getAttribute('data-part-id');
                        showReplacePartDialog(partId);
                    });

                    // Cập nhật danh sách checkbox và trình xử lý sự kiện cho chúng
                    updateDamagedPartsCheckboxes();
                    setupDamageCheckboxListeners();

                    alert(`Đã thêm vật tư mới: ${partCode} - ${partName}`);
                });
            }

            // Cập nhật danh sách checkbox vật tư hư hỏng sau khi thêm mới
            function updateDamagedPartsCheckboxes() {
                damagedCheckboxes = document.querySelectorAll('input[name="damaged_parts[]"]');
                damagedCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateDamagedPartsList();
                    });
                });
            }
        });
    </script>
</body>

</html>
