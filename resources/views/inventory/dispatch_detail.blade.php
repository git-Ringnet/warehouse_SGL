<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu xuất kho - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu xuất kho</h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ asset('inventory/dispatch_edit') }}">
                    <button
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                    </button>
                </a>
                <button id="print-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-print mr-2"></i> In phiếu
                </button>
            </div>
        </header>

        <main class="p-6">
            <!-- Header Info -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <div class="flex flex-col lg:flex-row justify-between gap-4">
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-lg font-semibold text-gray-800 mr-2">Mã phiếu xuất:</span>
                            <span class="text-lg text-blue-600 font-bold">XK001</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Ngày xuất:</span>
                            <span class="text-sm text-gray-700">05/05/2023</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Kho xuất:</span>
                            <span class="text-sm text-gray-700">Kho chính</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Người nhận:</span>
                            <span class="text-sm text-gray-700">Công ty TNHH ABC</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Loại hình:</span>
                            <span class="text-sm text-gray-700">Dự án</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Dự án:</span>
                            <span class="text-sm text-gray-700">Dự án IoT A1</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Thời gian bảo hành:</span>
                            <span class="text-sm text-gray-700">12 tháng</span>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Người lập phiếu:</span>
                            <span class="text-sm text-gray-700">Nguyễn Văn A</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Người duyệt:</span>
                            <span class="text-sm text-gray-700">Trần Văn B</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Người đại diện công ty:</span>
                            <span class="text-sm text-gray-700">Nguyễn Văn A (Giám đốc dự án)</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Cập nhật lần cuối:</span>
                            <span class="text-sm text-gray-700">08/05/2023 14:35:22</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Trạng thái:</span>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Đã hoàn thành
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 border-t border-gray-200 pt-4">
                    <div class="flex items-center">
                        <span class="text-sm font-medium text-gray-700 mr-2">Ghi chú:</span>
                        <span class="text-sm text-gray-700">Xuất hàng theo đơn đặt hàng số ĐH-2023-056 ngày 28/04/2023</span>
                    </div>
                </div>
            </div>

            <!-- Product List -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-boxes text-blue-500 mr-2"></i>
                    Danh sách thành phẩm
                </h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    STT
                                </th>
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
                                    Số lượng
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kho xuất
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Sample data -->
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">SP001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bộ điều khiển chính</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho chính</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <a href="#" class="text-blue-500 hover:text-blue-700">Chi tiết</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">SP002</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cảm biến nhiệt độ</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho chính</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <a href="#" class="text-blue-500 hover:text-blue-700">Chi tiết</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex flex-wrap gap-3 justify-end">
                <button id="export-excel-btn"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Xuất Excel
                </button>
                <button id="export-pdf-btn"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Xuất PDF
                </button>
            </div>
        </main>
    </div>

    <!-- Modal chi tiết sản phẩm -->
    <div id="product-detail-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Chi tiết sản phẩm: <span id="product-detail-name">Bộ điều khiển chính</span></h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" id="close-product-detail-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Mã sản phẩm: <span id="product-detail-code" class="font-medium">SP001</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Đơn vị: <span id="product-detail-unit" class="font-medium">Cái</span></p>
                    </div>
                </div>
                
                <h4 class="text-md font-semibold text-gray-800 mb-3">Danh sách mã thiết bị</h4>
                
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
                        <tbody id="product-detail-devices">
                            <!-- Dữ liệu sẽ được điền bằng JavaScript -->
                            <tr>
                                <td class="px-3 py-2 border border-gray-200 text-sm">SG-2023-001</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">PT1-001</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">PT2-001</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">PTN-001</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">SIM-001</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">AC-001</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">IOT-001</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">MAC-001</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">Thiết bị chính</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 border border-gray-200 text-sm">SG-2023-002</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">PT1-002</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">PT2-002</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">PTN-002</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">SIM-002</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">AC-002</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">IOT-002</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">MAC-002</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">Thiết bị phụ</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 flex justify-end space-x-3">
                <button type="button" id="close-detail-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Đóng
                </button>
                <button id="export-product-excel-btn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Xuất Excel
                </button>
                <button id="export-product-pdf-btn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Xuất PDF
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý sự kiện in phiếu
            const printBtn = document.getElementById('print-btn');
            printBtn.addEventListener('click', function() {
                window.print();
            });
            
            // Xử lý sự kiện xuất Excel
            const exportExcelBtn = document.getElementById('export-excel-btn');
            exportExcelBtn.addEventListener('click', function() {
                alert('Tính năng xuất Excel đang được phát triển!');
            });
            
            // Xử lý sự kiện xuất PDF
            const exportPdfBtn = document.getElementById('export-pdf-btn');
            exportPdfBtn.addEventListener('click', function() {
                alert('Tính năng xuất PDF đang được phát triển!');
            });

            // Dữ liệu mẫu cho chi tiết sản phẩm
            const productDetails = {
                1: {
                    code: 'SP001',
                    name: 'Bộ điều khiển chính',
                    unit: 'Cái',
                    devices: [
                        {
                            main_serial: 'SG-2023-001',
                            part1_serial: 'PT1-001',
                            part2_serial: 'PT2-001',
                            partn_serial: 'PTN-001',
                            sim_serial: 'SIM-001',
                            access_code: 'AC-001',
                            iot_id: 'IOT-001',
                            mac_4g: 'MAC-001',
                            note: 'Thiết bị chính'
                        },
                        {
                            main_serial: 'SG-2023-002',
                            part1_serial: 'PT1-002',
                            part2_serial: 'PT2-002',
                            partn_serial: 'PTN-002',
                            sim_serial: 'SIM-002',
                            access_code: 'AC-002',
                            iot_id: 'IOT-002',
                            mac_4g: 'MAC-002',
                            note: 'Thiết bị phụ'
                        }
                    ]
                },
                2: {
                    code: 'SP002',
                    name: 'Cảm biến nhiệt độ',
                    unit: 'Cái',
                    devices: [
                        {
                            main_serial: 'SG-2023-003',
                            part1_serial: 'PT1-003',
                            part2_serial: 'PT2-003',
                            partn_serial: 'PTN-003',
                            sim_serial: 'SIM-003',
                            access_code: 'AC-003',
                            iot_id: 'IOT-003',
                            mac_4g: 'MAC-003',
                            note: 'Cảm biến chính'
                        },
                        {
                            main_serial: 'SG-2023-004',
                            part1_serial: 'PT1-004',
                            part2_serial: 'PT2-004',
                            partn_serial: 'PTN-004',
                            sim_serial: 'SIM-004',
                            access_code: 'AC-004',
                            iot_id: 'IOT-004',
                            mac_4g: 'MAC-004',
                            note: 'Cảm biến phụ'
                        },
                        {
                            main_serial: 'SG-2023-005',
                            part1_serial: 'PT1-005',
                            part2_serial: 'PT2-005',
                            partn_serial: 'PTN-005',
                            sim_serial: 'SIM-005',
                            access_code: 'AC-005',
                            iot_id: 'IOT-005',
                            mac_4g: 'MAC-005',
                            note: 'Cảm biến dự phòng'
                        }
                    ]
                }
            };
            
            // Xử lý sự kiện hiển thị chi tiết sản phẩm
            const productDetailLinks = document.querySelectorAll('a.text-blue-500');
            const productDetailModal = document.getElementById('product-detail-modal');
            const closeProductDetailModalBtn = document.getElementById('close-product-detail-modal');
            const closeDetailBtn = document.getElementById('close-detail-btn');
            const productDetailName = document.getElementById('product-detail-name');
            const productDetailCode = document.getElementById('product-detail-code');
            const productDetailUnit = document.getElementById('product-detail-unit');
            const productDetailDevices = document.getElementById('product-detail-devices');
            const exportProductExcelBtn = document.getElementById('export-product-excel-btn');
            const exportProductPdfBtn = document.getElementById('export-product-pdf-btn');
            
            productDetailLinks.forEach((link, index) => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = index + 1; // Giả định id sản phẩm bắt đầu từ 1
                    const product = productDetails[productId];
                    
                    if (product) {
                        productDetailName.textContent = product.name;
                        productDetailCode.textContent = product.code;
                        productDetailUnit.textContent = product.unit;
                        
                        // Xóa các hàng dữ liệu hiện tại
                        productDetailDevices.innerHTML = '';
                        
                        // Thêm dữ liệu mới
                        product.devices.forEach(device => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td class="px-3 py-2 border border-gray-200 text-sm">${device.main_serial}</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">${device.part1_serial}</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">${device.part2_serial}</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">${device.partn_serial}</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">${device.sim_serial}</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">${device.access_code}</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">${device.iot_id}</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">${device.mac_4g}</td>
                                <td class="px-3 py-2 border border-gray-200 text-sm">${device.note}</td>
                            `;
                            productDetailDevices.appendChild(row);
                        });
                        
                        // Hiển thị modal
                        productDetailModal.classList.remove('hidden');
                    }
                });
            });
            
            // Đóng modal chi tiết sản phẩm
            closeProductDetailModalBtn.addEventListener('click', function() {
                productDetailModal.classList.add('hidden');
            });
            
            closeDetailBtn.addEventListener('click', function() {
                productDetailModal.classList.add('hidden');
            });
            
            // Xử lý sự kiện xuất Excel chi tiết sản phẩm
            exportProductExcelBtn.addEventListener('click', function() {
                alert('Tính năng xuất Excel chi tiết sản phẩm đang được phát triển!');
            });
            
            // Xử lý sự kiện xuất PDF chi tiết sản phẩm
            exportProductPdfBtn.addEventListener('click', function() {
                alert('Tính năng xuất PDF chi tiết sản phẩm đang được phát triển!');
            });
        });
    </script>
</body>

</html> 