<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu xuất kho - SGL</title>
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
                <a href="{{ asset('inventory/dispatch_detail') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu xuất kho</h1>
            </div>
        </header>

        <main class="p-6">
            <form action="#" method="POST">
                @csrf
                @method('PUT')

                <!-- Thông tin phiếu xuất -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-invoice text-blue-500 mr-2"></i>
                        Thông tin phiếu xuất
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="dispatch_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu
                                xuất</label>
                            <input type="text" id="dispatch_code" name="dispatch_code" value="XK001" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="dispatch_date"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày xuất <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="dispatch_date" name="dispatch_date" value="2023-05-05" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="dispatch_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại hình <span class="text-red-500">*</span></label>
                            <select id="dispatch_type" name="dispatch_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn loại hình --</option>
                                <option value="project" selected>Dự án</option>
                                <option value="rental">Cho thuê</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">Dự án</label>
                            <select id="project_id" name="project_id" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn dự án --</option>
                                <option value="1" selected>Dự án IoT A1</option>
                                <option value="2">Dự án Smart City A2</option>
                                <option value="3">Dự án Nhà máy thông minh A3</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="warranty_period" class="block text-sm font-medium text-gray-700 mb-1">Thời gian bảo hành</label>
                            <input type="text" id="warranty_period" name="warranty_period" value="12 tháng" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="company_representative" class="block text-sm font-medium text-gray-700 mb-1">Người đại diện công ty</label>
                            <select id="company_representative" name="company_representative"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người đại diện --</option>
                                <option value="1" selected>Nguyễn Văn A (Giám đốc dự án)</option>
                                <option value="2">Trần Thị B (Trưởng phòng kỹ thuật)</option>
                                <option value="3">Lê Văn C (Kỹ sư công nghệ)</option>
                            </select>
                        </div>
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho xuất <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho xuất --</option>
                                <option value="1" selected>Kho chính</option>
                                <option value="2">Kho phụ</option>
                                <option value="3">Kho linh kiện</option>
                                <option value="4">Kho bảo hành</option>
                            </select>
                        </div>
                        <div>
                            <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người
                                nhận <span class="text-red-500">*</span></label>
                            <select id="receiver_id" name="receiver_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người nhận --</option>
                                <option value="1" selected>Công ty TNHH ABC</option>
                                <option value="2">Công ty CP XYZ</option>
                                <option value="3">Doanh nghiệp tư nhân MNO</option>
                                <option value="4">Kho Hà Nội</option>
                                <option value="5">Kho Đà Nẵng</option>
                                <option value="6">Kho Hồ Chí Minh</option>
                            </select>
                        </div>
                        <div class="">
                            <label for="dispatch_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi
                                chú</label>
                            <textarea id="dispatch_note" name="dispatch_note" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">Xuất hàng theo đơn đặt hàng số ĐH-2023-056 ngày 28/04/2023</textarea>
                        </div>
                    </div>
                </div>

                <!-- Danh sách thành phẩm -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-boxes text-blue-500 mr-2"></i>
                        Danh sách thành phẩm xuất kho
                    </h2>

                    <!-- Tìm kiếm thành phẩm -->
                    <div class="mb-4">
                        <div class="relative">
                            <input type="text" id="product_search" placeholder="Tìm kiếm thành phẩm theo mã, tên..."
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <button type="button" id="add_product_btn"
                                class="absolute inset-y-0 right-0 px-3 bg-blue-500 text-white rounded-r-lg hover:bg-blue-600 transition-colors">
                                Thêm
                            </button>
                        </div>
                    </div>

                    <!-- Bảng thành phẩm đã chọn -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã SP
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên thành phẩm
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Đơn vị
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tồn kho
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số lượng xuất
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="product_list" class="bg-white divide-y divide-gray-200">
                                <!-- thành phẩm hiện tại -->
                                <tr class="product-row">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <input type="hidden" name="products[0][id]" value="1">
                                        SP001
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bộ điều khiển chính
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">25</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="number" name="products[0][quantity]" min="1"
                                            max="25" value="2"
                                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input"
                                            data-index="0">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button type="button" class="text-red-500 hover:text-red-700 delete-product"
                                            data-index="0">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="product-row">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <input type="hidden" name="products[1][id]" value="2">
                                        SP002
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cảm biến nhiệt độ
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">40</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="number" name="products[1][quantity]" min="1"
                                            max="40" value="3"
                                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input"
                                            data-index="1">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button type="button" class="text-red-500 hover:text-red-700 delete-product"
                                            data-index="1">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Hàng "không có thành phẩm" -->
                                <tr id="no_products_row" style="display: none;">
                                    <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        Chưa có thành phẩm nào được thêm vào phiếu xuất
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Nút cập nhật mã thiết bị -->
                    <div class="mt-4 flex justify-end">
                        <button type="button" id="update_device_codes_btn" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i> Cập nhật mã thiết bị
                        </button>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ asset('inventory/dispatch_detail') }}"
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

    <!-- Modal cập nhật mã thiết bị -->
    <div id="device-code-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Cập nhật mã thiết bị</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" id="close-device-code-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Nhập thông tin mã thiết bị cho sản phẩm. Điền thông tin vào bảng bên dưới:</p>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri chính
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri vật tư 1
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri vật tư 2
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri vật tư n
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri sim
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mã truy cập
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    ID IoT
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mac 4G
                                </th>
                                <th scope="col" class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Chú thích
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Các hàng dữ liệu -->
                            <tr>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_main[]" value="SG-2023-001" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_1[]" value="PT1-001" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_2[]" value="PT2-001" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_n[]" value="PTN-001" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_sim[]" value="SIM-001" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="access_code[]" value="AC-001" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="iot_id[]" value="IOT-001" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="mac_4g[]" value="MAC-001" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="note[]" value="Thiết bị chính" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_main[]" value="SG-2023-002" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_1[]" value="PT1-002" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_2[]" value="PT2-002" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_n[]" value="PTN-002" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_sim[]" value="SIM-002" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="access_code[]" value="AC-002" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="iot_id[]" value="IOT-002" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="mac_4g[]" value="MAC-002" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="note[]" value="Thiết bị phụ" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 flex justify-between">
                    <button type="button" id="add-device-row" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-plus mr-2"></i> Thêm hàng
                    </button>
                    <div>
                        <button type="button" id="cancel-device-codes" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors mr-2">
                            Hủy
                        </button>
                        <button type="button" id="save-device-codes" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thông tin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dữ liệu mẫu cho thành phẩm
            const sampleProducts = [{
                    id: 1,
                    code: 'SP001',
                    name: 'Bộ điều khiển chính',
                    unit: 'Cái',
                    stock: 25,
                    price: 5000000
                },
                {
                    id: 2,
                    code: 'SP002',
                    name: 'Cảm biến nhiệt độ',
                    unit: 'Cái',
                    stock: 40,
                    price: 1200000
                },
                {
                    id: 3,
                    code: 'SP003',
                    name: 'Màn hình hiển thị',
                    unit: 'Bộ',
                    stock: 15,
                    price: 3500000
                },
                {
                    id: 4,
                    code: 'SP004',
                    name: 'Bo mạch nguồn',
                    unit: 'Cái',
                    stock: 30,
                    price: 800000
                },
                {
                    id: 5,
                    code: 'SP005',
                    name: 'Dây cáp kết nối',
                    unit: 'Cuộn',
                    stock: 50,
                    price: 250000
                }
            ];

            // Khởi tạo mảng thành phẩm đã chọn
            let selectedProducts = [{
                    id: 1,
                    code: 'SP001',
                    name: 'Bộ điều khiển chính',
                    unit: 'Cái',
                    stock: 25,
                    quantity: 2
                },
                {
                    id: 2,
                    code: 'SP002',
                    name: 'Cảm biến nhiệt độ',
                    unit: 'Cái',
                    stock: 40,
                    quantity: 3
                }
            ];

            // Xử lý thêm thành phẩm
            const productSearchInput = document.getElementById('product_search');
            const addProductBtn = document.getElementById('add_product_btn');
            const productList = document.getElementById('product_list');
            const noProductsRow = document.getElementById('no_products_row');

            addProductBtn.addEventListener('click', function() {
                const searchTerm = productSearchInput.value.trim().toLowerCase();

                if (!searchTerm) {
                    alert('Vui lòng nhập mã hoặc tên thành phẩm để tìm kiếm!');
                    return;
                }

                // Tìm thành phẩm trong dữ liệu mẫu
                const foundProduct = sampleProducts.find(p =>
                    p.code.toLowerCase().includes(searchTerm) ||
                    p.name.toLowerCase().includes(searchTerm)
                );

                if (!foundProduct) {
                    alert('Không tìm thấy thành phẩm phù hợp!');
                    return;
                }

                // Kiểm tra xem thành phẩm đã được thêm chưa
                if (selectedProducts.some(p => p.id === foundProduct.id)) {
                    alert('thành phẩm này đã được thêm vào phiếu xuất!');
                    return;
                }

                // Thêm thành phẩm vào danh sách
                selectedProducts.push({
                    ...foundProduct,
                    quantity: 1
                });

                // Cập nhật giao diện
                updateProductList();

                // Xóa nội dung tìm kiếm
                productSearchInput.value = '';
            });

            function updateProductList() {
                // Ẩn thông báo "không có thành phẩm"
                if (selectedProducts.length > 0) {
                    noProductsRow.style.display = 'none';
                } else {
                    noProductsRow.style.display = '';
                }

                // Xóa các hàng thành phẩm hiện tại (trừ hàng thông báo)
                const productRows = document.querySelectorAll('.product-row');
                productRows.forEach(row => row.remove());

                // Thêm hàng cho mỗi thành phẩm đã chọn
                selectedProducts.forEach((product, index) => {
                    const row = document.createElement('tr');
                    row.className = 'product-row';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="hidden" name="products[${index}][id]" value="${product.id}">
                            ${product.code}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${product.unit}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${product.stock}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="number" name="products[${index}][quantity]" min="1" max="${product.stock}" value="${product.quantity}"
                                class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input"
                                data-index="${index}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-500 hover:text-red-700 delete-product" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;

                    productList.insertBefore(row, noProductsRow);
                });

                // Thêm sự kiện cho input số lượng
                const quantityInputs = document.querySelectorAll('.quantity-input');
                quantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newQuantity = parseInt(this.value);

                        if (newQuantity < 1) {
                            this.value = 1;
                            selectedProducts[index].quantity = 1;
                        } else if (newQuantity > selectedProducts[index].stock) {
                            this.value = selectedProducts[index].stock;
                            selectedProducts[index].quantity = selectedProducts[index].stock;
                            alert(
                                `Số lượng xuất không thể vượt quá số lượng tồn kho (${selectedProducts[index].stock})!`);
                        } else {
                            selectedProducts[index].quantity = newQuantity;
                        }
                    });
                });

                // Thêm sự kiện xóa thành phẩm
                const deleteButtons = document.querySelectorAll('.delete-product');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedProducts.splice(index, 1);
                        updateProductList();
                    });
                });
            }

            // Khởi tạo sự kiện cho các nút xóa và input số lượng ban đầu
            const deleteButtons = document.querySelectorAll('.delete-product');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    selectedProducts.splice(index, 1);
                    updateProductList();
                });
            });

            const quantityInputs = document.querySelectorAll('.quantity-input');
            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const index = parseInt(this.dataset.index);
                    const newQuantity = parseInt(this.value);

                    if (newQuantity < 1) {
                        this.value = 1;
                        selectedProducts[index].quantity = 1;
                    } else if (newQuantity > selectedProducts[index].stock) {
                        this.value = selectedProducts[index].stock;
                        selectedProducts[index].quantity = selectedProducts[index].stock;
                        alert(
                            `Số lượng xuất không thể vượt quá số lượng tồn kho (${selectedProducts[index].stock})!`);
                    } else {
                        selectedProducts[index].quantity = newQuantity;
                    }
                });
            });
        });
    </script>
</body>

</html>
