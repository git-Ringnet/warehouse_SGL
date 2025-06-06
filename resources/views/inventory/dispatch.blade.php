<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo phiếu xuất kho - SGL</title>
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
                <a href="{{ asset('inventory/dispatch_list') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Tạo phiếu xuất kho</h1>
            </div>
        </header>

        <main class="p-6">
            <form action="#" method="POST">
                @csrf

                <!-- Thông tin phiếu xuất -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-invoice text-blue-500 mr-2"></i>
                        Thông tin phiếu xuất
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="dispatch_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu xuất</label>
                            <input type="text" id="dispatch_code" name="dispatch_code" value="XK{{ date('Ymd') }}-001" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="dispatch_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày xuất <span class="text-red-500">*</span></label>
                            <input type="date" id="dispatch_date" name="dispatch_date" value="{{ date('Y-m-d') }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="dispatch_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại hình <span class="text-red-500">*</span></label>
                            <select id="dispatch_type" name="dispatch_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn loại hình --</option>
                                <option value="project">Dự án</option>
                                <option value="rental">Cho thuê</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">Dự án</label>
                            <select id="project_id" name="project_id" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn dự án --</option>
                                <!-- Dự án sẽ được load dựa trên khách hàng đã chọn -->
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="warranty_period" class="block text-sm font-medium text-gray-700 mb-1">Thời gian bảo hành</label>
                            <input type="text" id="warranty_period" name="warranty_period" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="company_representative" class="block text-sm font-medium text-gray-700 mb-1">Người đại diện công ty</label>
                            <select id="company_representative" name="company_representative"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người đại diện --</option>
                                <!-- Người đại diện sẽ được load từ danh sách nhân viên -->
                            </select>
                        </div>
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho xuất <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho xuất --</option>
                                <option value="1">Kho chính</option>
                                <option value="2">Kho phụ</option>
                                <option value="3">Kho linh kiện</option>
                                <option value="4">Kho bảo hành</option>
                            </select>
                        </div>
                        <div>
                            <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người nhận <span class="text-red-500">*</span></label>
                            <select id="receiver_id" name="receiver_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người nhận --</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="dispatch_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="dispatch_note" name="dispatch_note" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập ghi chú cho phiếu xuất (nếu có)"></textarea>
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã SP
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên thành phẩm
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Đơn vị
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tồn kho
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số lượng xuất
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Đơn giá
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thành tiền
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="product_list" class="bg-white divide-y divide-gray-200">
                                <!-- Dữ liệu thành phẩm sẽ được thêm vào đây -->
                                <tr id="no_products_row">
                                    <td colspan="8" class="px-6 py-4 text-sm text-gray-500 text-center">
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
                    <a href="{{ asset('inventory') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Lưu phiếu xuất
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
                                    <input type="text" name="serial_main[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_1[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_2[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_n[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_sim[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="access_code[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="iot_id[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="mac_4g[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="note[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_main[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_1[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_2[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_part_n[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="serial_sim[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="access_code[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="iot_id[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="mac_4g[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" name="note[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
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
            const sampleProducts = [
                { id: 1, code: 'SP001', name: 'Bộ điều khiển chính', unit: 'Cái', stock: 25, price: 5000000 },
                { id: 2, code: 'SP002', name: 'Cảm biến nhiệt độ', unit: 'Cái', stock: 40, price: 1200000 },
                { id: 3, code: 'SP003', name: 'Màn hình hiển thị', unit: 'Bộ', stock: 15, price: 3500000 },
                { id: 4, code: 'SP004', name: 'Bo mạch nguồn', unit: 'Cái', stock: 30, price: 800000 },
                { id: 5, code: 'SP005', name: 'Dây cáp kết nối', unit: 'Cuộn', stock: 50, price: 250000 }
            ];
            
            // Dữ liệu mẫu cho người nhận
            const receivers = {
                customer: [
                    { id: 1, name: 'Công ty TNHH ABC' },
                    { id: 2, name: 'Công ty CP XYZ' },
                    { id: 3, name: 'Doanh nghiệp tư nhân MNO' }
                ],
                warehouse: [
                    { id: 4, name: 'Kho Hà Nội' },
                    { id: 5, name: 'Kho Đà Nẵng' },
                    { id: 6, name: 'Kho Hồ Chí Minh' }
                ],
                supplier: [
                    { id: 7, name: 'Nhà cung cấp A' },
                    { id: 8, name: 'Nhà cung cấp B' },
                    { id: 9, name: 'Nhà cung cấp C' }
                ],
                other: [
                    { id: 10, name: 'Đối tác khác' }
                ]
            };
            
            // Dữ liệu mẫu cho dự án
            const projects = {
                1: [ // Dự án của Công ty TNHH ABC
                    { id: 1, name: 'Dự án IoT A1', warrantyPeriod: '12 tháng' },
                    { id: 2, name: 'Dự án Smart City A2', warrantyPeriod: '24 tháng' },
                    { id: 3, name: 'Dự án Nhà máy thông minh A3', warrantyPeriod: '18 tháng' }
                ],
                2: [ // Dự án của Công ty CP XYZ
                    { id: 4, name: 'Dự án Xanh XYZ-1', warrantyPeriod: '36 tháng' },
                    { id: 5, name: 'Dự án Công nghệ XYZ-2', warrantyPeriod: '24 tháng' }
                ],
                3: [ // Dự án của Doanh nghiệp tư nhân MNO
                    { id: 6, name: 'Dự án MNO-2023', warrantyPeriod: '12 tháng' }
                ]
            };
            
            // Dữ liệu mẫu cho nhân viên
            const employees = [
                { id: 1, name: 'Nguyễn Văn A', position: 'Giám đốc dự án' },
                { id: 2, name: 'Trần Thị B', position: 'Trưởng phòng kỹ thuật' },
                { id: 3, name: 'Lê Văn C', position: 'Kỹ sư công nghệ' },
                { id: 4, name: 'Phạm Thị D', position: 'Quản lý sản xuất' },
                { id: 5, name: 'Hoàng Văn E', position: 'Trưởng phòng kinh doanh' }
            ];
            
            // Xử lý thay đổi loại người nhận
            const receiverTypeSelect = document.getElementById('receiver_type');
            const receiverIdSelect = document.getElementById('receiver_id');
            
            if (receiverTypeSelect) {
                receiverTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;
                    receiverIdSelect.innerHTML = '<option value="">-- Chọn người nhận --</option>';
                    
                    if (selectedType && receivers[selectedType]) {
                        receivers[selectedType].forEach(receiver => {
                            const option = document.createElement('option');
                            option.value = receiver.id;
                            option.textContent = receiver.name;
                            receiverIdSelect.appendChild(option);
                        });
                    }
                });
            }
            
            // Xử lý thêm thành phẩm
            const productSearchInput = document.getElementById('product_search');
            const addProductBtn = document.getElementById('add_product_btn');
            const productList = document.getElementById('product_list');
            const noProductsRow = document.getElementById('no_products_row');
            
            let selectedProducts = [];
            
            if (addProductBtn) {
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
                        quantity: 1,
                        total: foundProduct.price
                    });
                    
                    // Cập nhật giao diện
                    renderProductTable();
                    
                    // Reset input tìm kiếm
                    productSearchInput.value = '';
                });
            }
            
            // Xử lý thay đổi loại hình xuất kho
            const dispatchTypeSelect = document.getElementById('dispatch_type');
            const projectIdSelect = document.getElementById('project_id');
            
            if (dispatchTypeSelect) {
                dispatchTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;
                    
                    // Hiển thị/ẩn field dự án dựa vào loại hình
                    if (selectedType === 'project') {
                        projectIdSelect.parentElement.classList.remove('hidden');
                    } else {
                        projectIdSelect.parentElement.classList.add('hidden');
                    }
                });
            }
            
            // Xử lý thay đổi người nhận (khách hàng)
            if (receiverIdSelect) {
                receiverIdSelect.addEventListener('change', function() {
                    const customerId = this.value;
                    projectIdSelect.innerHTML = '<option value="">-- Chọn dự án --</option>';
                    
                    // Nếu có dự án cho khách hàng này
                    if (projects[customerId]) {
                        projects[customerId].forEach(project => {
                            const option = document.createElement('option');
                            option.value = project.id;
                            option.textContent = project.name;
                            option.dataset.warranty = project.warrantyPeriod;
                            projectIdSelect.appendChild(option);
                        });
                    }
                });
            }
            
            // Xử lý chọn dự án - cập nhật thời gian bảo hành
            const warrantyPeriodInput = document.getElementById('warranty_period');
            
            if (projectIdSelect) {
                projectIdSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.dataset.warranty) {
                        warrantyPeriodInput.value = selectedOption.dataset.warranty;
                    } else {
                        warrantyPeriodInput.value = '';
                    }
                });
            }
            
            // Xử lý hiển thị danh sách nhân viên đại diện
            const companyRepresentativeSelect = document.getElementById('company_representative');
            
            if (companyRepresentativeSelect) {
                // Tải danh sách nhân viên
                employees.forEach(employee => {
                    const option = document.createElement('option');
                    option.value = employee.id;
                    option.textContent = `${employee.name} (${employee.position})`;
                    companyRepresentativeSelect.appendChild(option);
                });
            }
            
            // Xử lý modal cập nhật mã thiết bị
            const updateDeviceCodesBtn = document.getElementById('update_device_codes_btn');
            const deviceCodeModal = document.getElementById('device-code-modal');
            const closeDeviceCodeModalBtn = document.getElementById('close-device-code-modal');
            const cancelDeviceCodesBtn = document.getElementById('cancel-device-codes');
            const saveDeviceCodesBtn = document.getElementById('save-device-codes');
            const addDeviceRowBtn = document.getElementById('add-device-row');
            
            if (updateDeviceCodesBtn) {
                updateDeviceCodesBtn.addEventListener('click', function() {
                    deviceCodeModal.classList.remove('hidden');
                });
            }
            
            if (closeDeviceCodeModalBtn) {
                closeDeviceCodeModalBtn.addEventListener('click', function() {
                    deviceCodeModal.classList.add('hidden');
                });
            }
            
            if (cancelDeviceCodesBtn) {
                cancelDeviceCodesBtn.addEventListener('click', function() {
                    deviceCodeModal.classList.add('hidden');
                });
            }
            
            if (saveDeviceCodesBtn) {
                saveDeviceCodesBtn.addEventListener('click', function() {
                    // Mã xử lý lưu thông tin mã thiết bị
                    alert('Đã lưu thông tin mã thiết bị!');
                    deviceCodeModal.classList.add('hidden');
                });
            }
            
            if (addDeviceRowBtn) {
                addDeviceRowBtn.addEventListener('click', function() {
                    const tbody = deviceCodeModal.querySelector('tbody');
                    const newRow = document.createElement('tr');
                    
                    newRow.innerHTML = `
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="serial_main[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="serial_part_1[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="serial_part_2[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="serial_part_n[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="serial_sim[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="access_code[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="iot_id[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="mac_4g[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                        <td class="px-2 py-2 border border-gray-200">
                            <input type="text" name="note[]" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                        </td>
                    `;
                    
                    tbody.appendChild(newRow);
                });
            }
        });
    </script>
</body>

</html> 