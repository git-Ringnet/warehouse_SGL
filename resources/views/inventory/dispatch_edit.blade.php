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
                            <label for="dispatch_type"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Loại hình <span
                                    class="text-red-500">*</span></label>
                            <select id="dispatch_type" name="dispatch_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn loại hình --</option>
                                <option value="project" selected>Dự án</option>
                                <option value="rental">Cho thuê</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label for="dispatch_detail" class="block text-sm font-medium text-gray-700 mb-1">Chi tiết
                                xuất kho <span class="text-red-500">*</span></label>
                            <select id="dispatch_detail" name="dispatch_detail"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="all" selected>Tất cả</option>
                                <option value="contract">Xuất theo hợp đồng</option>
                                <option value="backup">Xuất thiết bị dự phòng</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="project_receiver"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Dự án <span
                                    class="text-red-500">*</span></label>
                            <select id="project_receiver" name="project_receiver" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="project_1">Dự án: IoT A1 -- Người nhận: Trần Văn A</option>
                                <option value="project_2">Dự án: Smart City A2 -- Người nhận: Trần Văn B</option>
                                <option value="project_3">Dự án: Nhà máy thông minh A3 -- Người nhận: Trần Văn C
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="warranty_period" class="block text-sm font-medium text-gray-700 mb-1">Thời gian
                                bảo hành</label>
                            <input type="text" id="warranty_period" name="warranty_period" value="12 tháng" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="company_representative"
                                class="block text-sm font-medium text-gray-700 mb-1">Người đại diện công ty</label>
                            <select id="company_representative" name="company_representative"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người đại diện --</option>
                                <option value="1" selected>Nguyễn Văn A (Giám đốc dự án)</option>
                                <option value="2">Trần Thị B (Trưởng phòng kỹ thuật)</option>
                                <option value="3">Lê Văn C (Kỹ sư công nghệ)</option>
                            </select>
                        </div>
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho
                                xuất <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho xuất --</option>
                                <option value="1" selected>Kho chính</option>
                                <option value="2">Kho phụ</option>
                                <option value="3">Kho linh kiện</option>
                                <option value="4">Kho bảo hành</option>
                            </select>
                        </div>
                    </div>
                    <div class="w-full">
                        <label for="dispatch_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi
                            chú</label>
                        <textarea id="dispatch_note" name="dispatch_note" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">Xuất hàng theo đơn đặt hàng số ĐH-2023-056 ngày 28/04/2023</textarea>
                    </div>
                </div>

                <!-- Danh sách thành phẩm chính đã được ẩn -->

                <!-- Block Xuất theo hợp đồng -->
                <div id="contract-export-block" class="bg-white rounded-xl shadow-md p-6 border border-blue-200 mb-6" style="display: none;">
                    <h2 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                        <i class="fas fa-file-contract text-blue-500 mr-2"></i>
                        <span id="contract-block-title">Danh sách thành phẩm theo hợp đồng</span>
                    </h2>

                    <!-- Chọn sản phẩm hợp đồng -->
                    <div class="mb-4">
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <select id="contract_product_select" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Chọn thành phẩm để thêm vào hợp đồng --</option>
                                </select>
                            </div>
                            <button type="button" id="add_contract_product_btn"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                <i class="fas fa-plus mr-1"></i> Thêm
                            </button>
                        </div>
                    </div>

                    <!-- Bảng sản phẩm hợp đồng -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mã SP</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên thành phẩm</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Đơn vị</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tồn kho</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số lượng xuất</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="contract_product_list" class="bg-white divide-y divide-gray-200">
                                <tr id="no_contract_products_row">
                                    <td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        Chưa có thành phẩm nào được thêm vào danh sách hợp đồng
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Nút cập nhật mã thiết bị hợp đồng -->
                    <div class="mt-4 flex justify-end">
                        <button type="button" id="update_contract_device_codes_btn"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i> Cập nhật mã thiết bị
                        </button>
                    </div>
                </div>

                <!-- Block Xuất thiết bị dự phòng -->
                <div id="backup-export-block" class="bg-white rounded-xl shadow-md p-6 border border-orange-200 mb-6" style="display: none;">
                    <h2 class="text-lg font-semibold text-orange-800 mb-4 flex items-center">
                        <i class="fas fa-shield-alt text-orange-500 mr-2"></i>
                        <span id="backup-block-title">Danh sách thiết bị dự phòng</span>
                    </h2>

                    <!-- Chọn sản phẩm dự phòng -->
                    <div class="mb-4">
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <select id="backup_product_select" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <option value="">-- Chọn thiết bị dự phòng --</option>
                                </select>
                            </div>
                            <button type="button" id="add_backup_product_btn"
                                class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                                <i class="fas fa-plus mr-1"></i> Thêm
                            </button>
                        </div>
                    </div>

                    <!-- Bảng sản phẩm dự phòng -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mã SP</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên thành phẩm</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Đơn vị</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tồn kho</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số lượng xuất</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="backup_product_list" class="bg-white divide-y divide-gray-200">
                                <tr id="no_backup_products_row">
                                    <td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        Chưa có thiết bị dự phòng nào được thêm
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Nút cập nhật mã thiết bị dự phòng -->
                    <div class="mt-4 flex justify-end">
                        <button type="button" id="update_backup_device_codes_btn"
                            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition-colors">
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
    <div id="device-code-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Cập nhật mã thiết bị</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" id="close-device-code-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Nhập thông tin mã thiết bị cho sản phẩm. Điền thông tin vào bảng
                    bên dưới:</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri chính
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri vật tư
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri sim
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mã truy cập
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    ID IoT
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mac 4G
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Chú thích
                                </th>
                            </tr>
                        </thead>
                        <tbody id="device-code-tbody">
                            <!-- Dữ liệu sẽ được tạo động từ JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-between">
                    <button type="button" id="import-device-codes"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-file-import mr-2"></i> Import Excel
                    </button>
                    <div>
                        <button type="button" id="cancel-device-codes"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors mr-2">
                            Hủy
                        </button>
                        <button type="button" id="save-device-codes"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thông tin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Input file ẩn cho import Excel -->
    <input type="file" id="excel-file-input" accept=".xlsx,.xls,.csv" style="display: none;">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dữ liệu mẫu cho thành phẩm
            const sampleProducts = [{
                    id: 1,
                    code: 'SP001',
                    name: 'Bộ điều khiển chính v2.1',
                    unit: 'Cái',
                    stock: 25,
                    price: 5000000,
                    components: [{
                            name: 'Vi xử lý ARM Cortex-M4',
                            serial: 'ARM001'
                        },
                        {
                            name: 'Bộ nhớ Flash 256KB',
                            serial: 'FL256'
                        },
                        {
                            name: 'Module WiFi ESP32',
                            serial: 'ESP32001'
                        },
                        {
                            name: 'Cảm biến nhiệt độ DHT22',
                            serial: 'DHT001'
                        }
                    ]
                },
                {
                    id: 2,
                    code: 'SP002',
                    name: 'Cảm biến nhiệt độ độ chính xác cao',
                    unit: 'Cái',
                    stock: 40,
                    price: 1200000,
                    components: [{
                            name: 'Chip cảm biến SHT30',
                            serial: 'SHT001'
                        },
                        {
                            name: 'Module ADC 16-bit',
                            serial: 'ADC16001'
                        },
                        {
                            name: 'Bộ lọc nhiễu',
                            serial: 'FILTER001'
                        }
                    ]
                },
                {
                    id: 3,
                    code: 'SP003',
                    name: 'Màn hình hiển thị TFT 7 inch',
                    unit: 'Bộ',
                    stock: 15,
                    price: 3500000,
                    components: [{
                            name: 'Panel TFT 7 inch',
                            serial: 'TFT7001'
                        },
                        {
                            name: 'Driver IC ILI9341',
                            serial: 'ILI001'
                        },
                        {
                            name: 'Cảm ứng điện dung',
                            serial: 'TOUCH001'
                        },
                        {
                            name: 'Backlight LED',
                            serial: 'LED001'
                        }
                    ]
                },
                {
                    id: 4,
                    code: 'SP004',
                    name: 'Bo mạch nguồn 24V/5A',
                    unit: 'Cái',
                    stock: 30,
                    price: 800000,
                    components: [{
                            name: 'IC chuyển đổi LM2596',
                            serial: 'LM001'
                        },
                        {
                            name: 'Cuộn cảm 220µH',
                            serial: 'L220001'
                        },
                        {
                            name: 'Tụ điện 1000µF',
                            serial: 'C1000001'
                        }
                    ]
                },
                {
                    id: 5,
                    code: 'SP005',
                    name: 'Dây cáp kết nối RS485',
                    unit: 'Cuộn',
                    stock: 50,
                    price: 250000,
                    components: [{
                            name: 'Dây đồng 2x0.75mm²',
                            serial: 'WIRE001'
                        },
                        {
                            name: 'Connector RJ45',
                            serial: 'RJ45001'
                        },
                        {
                            name: 'Vỏ bọc chống nhiễu',
                            serial: 'SHIELD001'
                        }
                    ]
                },
                {
                    id: 6,
                    code: 'SP006',
                    name: 'Module truyền thông 4G LTE',
                    unit: 'Cái',
                    stock: 20,
                    price: 2800000,
                    components: [{
                            name: 'Chip 4G Quectel EC25',
                            serial: 'EC25001'
                        },
                        {
                            name: 'Anten 4G',
                            serial: 'ANT4G001'
                        },
                        {
                            name: 'SIM Slot',
                            serial: 'SIM001'
                        }
                    ]
                },
                {
                    id: 7,
                    code: 'SP007',
                    name: 'Bộ cảm biến áp suất',
                    unit: 'Cái',
                    stock: 35,
                    price: 1800000,
                    components: [{
                            name: 'Cảm biến áp suất MPX5700',
                            serial: 'MPX001'
                        },
                        {
                            name: 'Bộ khuếch đại',
                            serial: 'AMP001'
                        },
                        {
                            name: 'Housing chống nước IP67',
                            serial: 'IP67001'
                        }
                    ]
                }
            ];

            // Mảng cho hợp đồng và dự phòng với dữ liệu mẫu
            let selectedContractProducts = [{
                    id: 1,
                    code: 'SP001',
                    name: 'Bộ điều khiển chính v2.1',
                    unit: 'Cái',
                    stock: 25,
                    quantity: 2
                },
                {
                    id: 3,
                    code: 'SP003',
                    name: 'Màn hình hiển thị TFT 7 inch',
                    unit: 'Bộ',
                    stock: 15,
                    quantity: 1
                }
            ];
            
            let selectedBackupProducts = [{
                    id: 2,
                    code: 'SP002',
                    name: 'Cảm biến nhiệt độ độ chính xác cao',
                    unit: 'Cái',
                    stock: 40,
                    quantity: 3
                },
                {
                    id: 4,
                    code: 'SP004',
                    name: 'Bo mạch nguồn 24V/5A',
                    unit: 'Cái',
                    stock: 30,
                    quantity: 2
                }
            ];

            // Khởi tạo dropdown sản phẩm cho 2 danh sách
            const contractProductSelect = document.getElementById('contract_product_select');
            const backupProductSelect = document.getElementById('backup_product_select');
            
            function populateProductDropdown(selectElement) {
                sampleProducts.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = `${product.code} - ${product.name} (Tồn: ${product.stock})`;
                    selectElement.appendChild(option);
                });
            }

            populateProductDropdown(contractProductSelect);
            populateProductDropdown(backupProductSelect);

            // Xử lý hiển thị block theo chi tiết xuất kho
            const dispatchDetailSelect = document.getElementById('dispatch_detail');
            const contractBlock = document.getElementById('contract-export-block');
            const backupBlock = document.getElementById('backup-export-block');

            function updateDisplayBlocks() {
                const selectedValue = dispatchDetailSelect.value;
                
                switch(selectedValue) {
                    case 'all':
                        // Hiển thị cả 2 block
                        contractBlock.style.display = 'block';
                        backupBlock.style.display = 'block';
                        break;
                    case 'contract':
                        // Chỉ hiển thị block hợp đồng
                        contractBlock.style.display = 'block';
                        backupBlock.style.display = 'none';
                        break;
                    case 'backup':
                        // Chỉ hiển thị block dự phòng
                        contractBlock.style.display = 'none';
                        backupBlock.style.display = 'block';
                        break;
                    default:
                        // Mặc định hiển thị cả 2
                        contractBlock.style.display = 'block';
                        backupBlock.style.display = 'block';
                }
            }

            // Khởi tạo hiển thị ban đầu
            updateDisplayBlocks();

            // Lắng nghe thay đổi dispatch detail
            dispatchDetailSelect.addEventListener('change', updateDisplayBlocks);

            // Xử lý thêm sản phẩm cho 2 danh sách
            const addContractProductBtn = document.getElementById('add_contract_product_btn');
            const addBackupProductBtn = document.getElementById('add_backup_product_btn');
            const contractProductList = document.getElementById('contract_product_list');
            const backupProductList = document.getElementById('backup_product_list');
            const noContractProductsRow = document.getElementById('no_contract_products_row');
            const noBackupProductsRow = document.getElementById('no_backup_products_row');

            // Xử lý thêm sản phẩm hợp đồng
            addContractProductBtn.addEventListener('click', function() {
                const selectedId = parseInt(contractProductSelect.value);

                if (!selectedId) {
                    alert('Vui lòng chọn sản phẩm để thêm!');
                    return;
                }

                const foundProduct = sampleProducts.find(p => p.id === selectedId);
                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                if (selectedContractProducts.some(p => p.id === foundProduct.id)) {
                    alert('Sản phẩm này đã được thêm vào danh sách hợp đồng!');
                    return;
                }

                selectedContractProducts.push({
                    ...foundProduct,
                    quantity: 1
                });

                updateContractProductList();
                contractProductSelect.value = '';
            });

            // Xử lý thêm sản phẩm dự phòng
            addBackupProductBtn.addEventListener('click', function() {
                const selectedId = parseInt(backupProductSelect.value);

                if (!selectedId) {
                    alert('Vui lòng chọn thiết bị để thêm!');
                    return;
                }

                const foundProduct = sampleProducts.find(p => p.id === selectedId);
                if (!foundProduct) {
                    alert('Không tìm thấy thông tin thiết bị!');
                    return;
                }

                if (selectedBackupProducts.some(p => p.id === foundProduct.id)) {
                    alert('Thiết bị này đã được thêm vào danh sách dự phòng!');
                    return;
                }

                selectedBackupProducts.push({
                    ...foundProduct,
                    quantity: 1
                });

                updateBackupProductList();
                backupProductSelect.value = '';
            });

                        // Function updateProductList đã được loại bỏ

            function updateContractProductList() {
                if (selectedContractProducts.length > 0) {
                    noContractProductsRow.style.display = 'none';
                } else {
                    noContractProductsRow.style.display = '';
                }

                const contractRows = document.querySelectorAll('.contract-product-row');
                contractRows.forEach(row => row.remove());

                selectedContractProducts.forEach((product, index) => {
                    const row = document.createElement('tr');
                    row.className = 'contract-product-row';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="hidden" name="contract_products[${index}][id]" value="${product.id}">
                            ${product.code}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${product.unit}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${product.stock}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="number" name="contract_products[${index}][quantity]" min="1" max="${product.stock}" value="${product.quantity}"
                                class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 contract-quantity-input"
                                data-index="${index}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" name="contract_products[${index}][serial]" placeholder="Nhập serial (tùy chọn)" 
                                class="w-100 border border-gray-300 rounded px-2 py-1 text-sm"
                                value="${product.serial || ''}">
                            <div class="text-xs text-gray-400 mt-1">Không bắt buộc</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-500 hover:text-red-700 delete-contract-product" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    contractProductList.insertBefore(row, noContractProductsRow);
                });

                // Event listeners cho contract products
                const contractQuantityInputs = document.querySelectorAll('.contract-quantity-input');
                contractQuantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newQuantity = parseInt(this.value);
                        if (newQuantity < 1) {
                            this.value = 1;
                            selectedContractProducts[index].quantity = 1;
                        } else if (newQuantity > selectedContractProducts[index].stock) {
                            this.value = selectedContractProducts[index].stock;
                            selectedContractProducts[index].quantity = selectedContractProducts[index].stock;
                            alert(`Số lượng xuất không thể vượt quá số lượng tồn kho (${selectedContractProducts[index].stock})!`);
                        } else {
                            selectedContractProducts[index].quantity = newQuantity;
                        }
                    });
                });

                const deleteContractButtons = document.querySelectorAll('.delete-contract-product');
                deleteContractButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedContractProducts.splice(index, 1);
                        updateContractProductList();
                    });
                });
            }

            function updateBackupProductList() {
                if (selectedBackupProducts.length > 0) {
                    noBackupProductsRow.style.display = 'none';
                } else {
                    noBackupProductsRow.style.display = '';
                }

                const backupRows = document.querySelectorAll('.backup-product-row');
                backupRows.forEach(row => row.remove());

                selectedBackupProducts.forEach((product, index) => {
                    const row = document.createElement('tr');
                    row.className = 'backup-product-row';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="hidden" name="backup_products[${index}][id]" value="${product.id}">
                            ${product.code}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${product.unit}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${product.stock}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="number" name="backup_products[${index}][quantity]" min="1" max="${product.stock}" value="${product.quantity}"
                                class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-orange-500 backup-quantity-input"
                                data-index="${index}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" name="backup_products[${index}][serial]" placeholder="Nhập serial (tùy chọn)" 
                                class="w-100 border border-gray-300 rounded px-2 py-1 text-sm"
                                value="${product.serial || ''}">
                            <div class="text-xs text-gray-400 mt-1">Không bắt buộc</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-500 hover:text-red-700 delete-backup-product" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    backupProductList.insertBefore(row, noBackupProductsRow);
                });

                // Event listeners cho backup products
                const backupQuantityInputs = document.querySelectorAll('.backup-quantity-input');
                backupQuantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newQuantity = parseInt(this.value);
                        if (newQuantity < 1) {
                            this.value = 1;
                            selectedBackupProducts[index].quantity = 1;
                        } else if (newQuantity > selectedBackupProducts[index].stock) {
                            this.value = selectedBackupProducts[index].stock;
                            selectedBackupProducts[index].quantity = selectedBackupProducts[index].stock;
                            alert(`Số lượng xuất không thể vượt quá số lượng tồn kho (${selectedBackupProducts[index].stock})!`);
                        } else {
                            selectedBackupProducts[index].quantity = newQuantity;
                        }
                    });
                });

                const deleteBackupButtons = document.querySelectorAll('.delete-backup-product');
                deleteBackupButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedBackupProducts.splice(index, 1);
                        updateBackupProductList();
                    });
                });
            }

                        // Khởi tạo danh sách với dữ liệu có sẵn
            updateContractProductList();
            updateBackupProductList();

            // Xử lý thay đổi warranty period khi chọn người nhận/dự án
            const projectReceiverSelect = document.getElementById('project_receiver');
            const warrantyInput = document.getElementById('warranty_period');

            projectReceiverSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const warrantyPeriod = selectedOption.getAttribute('data-warranty') || '';
                warrantyInput.value = warrantyPeriod;
            });

            // Xử lý modal cập nhật mã thiết bị
            const updateContractDeviceCodesBtn = document.getElementById('update_contract_device_codes_btn');
            const updateBackupDeviceCodesBtn = document.getElementById('update_backup_device_codes_btn');
            const deviceCodeModal = document.getElementById('device-code-modal');
            const deviceCodeTbody = document.getElementById('device-code-tbody');
            const closeModalBtn = document.getElementById('close-device-code-modal');
            const cancelBtn = document.getElementById('cancel-device-codes');
            const saveBtn = document.getElementById('save-device-codes');
            const importBtn = document.getElementById('import-device-codes');
            const excelInput = document.getElementById('excel-file-input');

            let currentDeviceContext = 'contract'; // 'contract', 'backup'

            function populateDeviceCodes() {
                deviceCodeTbody.innerHTML = '';

                let currentProducts;
                let contextName;
                
                switch(currentDeviceContext) {
                    case 'contract':
                        currentProducts = selectedContractProducts;
                        contextName = 'hợp đồng';
                        break;
                    case 'backup':
                        currentProducts = selectedBackupProducts;
                        contextName = 'dự phòng';
                        break;
                    default:
                        currentProducts = selectedContractProducts;
                        contextName = 'hợp đồng';
                }

                if (currentProducts.length === 0) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.innerHTML = `
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-info-circle text-3xl mb-2"></i>
                                <p>Chưa có sản phẩm nào được chọn trong danh sách ${contextName}</p>
                                <p class="text-sm">Vui lòng thêm sản phẩm vào danh sách trước khi cập nhật mã thiết bị</p>
                            </div>
                        </td>
                    `;
                    deviceCodeTbody.appendChild(noDataRow);
                    return;
                }

                currentProducts.forEach((product, index) => {
                    for (let i = 0; i < product.quantity; i++) {
                        const row = document.createElement('tr');

                        // Tạo serial cho vật tư từ components
                        let componentSerials = '';
                        if (product.components && product.components.length > 0) {
                            componentSerials = product.components.map(comp => comp.serial).join(', ');
                        }

                        row.innerHTML = `
                            <td class="px-2 py-2 border border-gray-200">
                                <input type="text" name="serial_main[]" 
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                    placeholder="Nhập serial chính"
                                    value="SG-2024-${String(Date.now() + index * 1000 + i).slice(-3)}">
                            </td>
                            <td class="px-2 py-2 border border-gray-200">
                                <textarea name="serial_components[]" 
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm resize-none"
                                    rows="2" placeholder="Serial vật tư...">${componentSerials}</textarea>
                            </td>
                            <td class="px-2 py-2 border border-gray-200">
                                <input type="text" name="serial_sim[]" 
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                    placeholder="Serial SIM"
                                    value="SIM-${String(Date.now() + index * 100 + i).slice(-3)}">
                            </td>
                            <td class="px-2 py-2 border border-gray-200">
                                <input type="text" name="access_code[]" 
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                    placeholder="Mã truy cập"
                                    value="AC-${String(Date.now() + index * 10 + i).slice(-4)}">
                            </td>
                            <td class="px-2 py-2 border border-gray-200">
                                <input type="text" name="iot_id[]" 
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                    placeholder="ID IoT"
                                    value="IOT-${String(Date.now() + index + i).slice(-4)}">
                            </td>
                            <td class="px-2 py-2 border border-gray-200">
                                <input type="text" name="mac_4g[]" 
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                    placeholder="MAC 4G"
                                    value="4G-${String(Date.now() + index * 50 + i).slice(-4)}">
                            </td>
                            <td class="px-2 py-2 border border-gray-200">
                                <input type="text" name="note[]" 
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                    placeholder="Chú thích"
                                    value="${product.name} #${i + 1}">
                            </td>
                        `;
                        deviceCodeTbody.appendChild(row);
                    }
                });
            }

            updateContractDeviceCodesBtn.addEventListener('click', function() {
                currentDeviceContext = 'contract';
                populateDeviceCodes();
                deviceCodeModal.classList.remove('hidden');
            });

            updateBackupDeviceCodesBtn.addEventListener('click', function() {
                currentDeviceContext = 'backup';
                populateDeviceCodes();
                deviceCodeModal.classList.remove('hidden');
            });

            closeModalBtn.addEventListener('click', function() {
                deviceCodeModal.classList.add('hidden');
            });

            cancelBtn.addEventListener('click', function() {
                deviceCodeModal.classList.add('hidden');
            });

            saveBtn.addEventListener('click', function() {
                alert('Đã lưu thông tin mã thiết bị!');
                deviceCodeModal.classList.add('hidden');
            });

            importBtn.addEventListener('click', function() {
                excelInput.click();
            });

            excelInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    alert(`Đã chọn file: ${file.name}. Chức năng import đang được phát triển.`);
                    this.value = '';
                }
            });
        });
    </script>
</body>

</html>
