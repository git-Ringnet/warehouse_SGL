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
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
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
                        <div class="">
                            <label for="dispatch_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="dispatch_note" name="dispatch_note" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập ghi chú cho phiếu xuất (nếu có)"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Danh sách sản phẩm -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-boxes text-blue-500 mr-2"></i>
                        Danh sách sản phẩm xuất kho
                    </h2>

                    <!-- Tìm kiếm sản phẩm -->
                    <div class="mb-4">
                        <div class="relative">
                            <input type="text" id="product_search" placeholder="Tìm kiếm sản phẩm theo mã, tên..."
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

                    <!-- Bảng sản phẩm đã chọn -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã SP
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên sản phẩm
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
                                <!-- Dữ liệu sản phẩm sẽ được thêm vào đây -->
                                <tr id="no_products_row">
                                    <td colspan="8" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        Chưa có sản phẩm nào được thêm vào phiếu xuất
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dữ liệu mẫu cho sản phẩm
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
            
            // Xử lý thay đổi loại người nhận
            const receiverTypeSelect = document.getElementById('receiver_type');
            const receiverIdSelect = document.getElementById('receiver_id');
            
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
            
            // Xử lý thêm sản phẩm
            const productSearchInput = document.getElementById('product_search');
            const addProductBtn = document.getElementById('add_product_btn');
            const productList = document.getElementById('product_list');
            const noProductsRow = document.getElementById('no_products_row');
            const totalAmountEl = document.getElementById('total_amount');
            
            let selectedProducts = [];
            
            addProductBtn.addEventListener('click', function() {
                const searchTerm = productSearchInput.value.trim().toLowerCase();
                
                if (!searchTerm) {
                    alert('Vui lòng nhập mã hoặc tên sản phẩm để tìm kiếm!');
                    return;
                }
                
                // Tìm sản phẩm trong dữ liệu mẫu
                const foundProduct = sampleProducts.find(p => 
                    p.code.toLowerCase().includes(searchTerm) || 
                    p.name.toLowerCase().includes(searchTerm)
                );
                
                if (!foundProduct) {
                    alert('Không tìm thấy sản phẩm phù hợp!');
                    return;
                }
                
                // Kiểm tra xem sản phẩm đã được thêm chưa
                if (selectedProducts.some(p => p.id === foundProduct.id)) {
                    alert('Sản phẩm này đã được thêm vào phiếu xuất!');
                    return;
                }
                
                // Thêm sản phẩm vào danh sách
                selectedProducts.push({
                    ...foundProduct,
                    quantity: 1,
                    total: foundProduct.price
                });
                
                // Cập nhật giao diện
                updateProductList();
                
                // Xóa nội dung tìm kiếm
                productSearchInput.value = '';
            });
            
            function updateProductList() {
                // Ẩn thông báo "không có sản phẩm"
                if (selectedProducts.length > 0) {
                    noProductsRow.style.display = 'none';
                } else {
                    noProductsRow.style.display = '';
                }
                
                // Xóa các hàng sản phẩm hiện tại (trừ hàng thông báo)
                const productRows = document.querySelectorAll('.product-row');
                productRows.forEach(row => row.remove());
                
                // Thêm hàng cho mỗi sản phẩm đã chọn
                let totalAmount = 0;
                
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="number" name="products[${index}][price]" min="0" value="${product.price}"
                                class="w-32 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 price-input"
                                data-index="${index}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 product-total" data-index="${index}">
                            ${formatCurrency(product.total)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-500 hover:text-red-700 delete-product" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    
                    productList.insertBefore(row, noProductsRow);
                    totalAmount += product.total;
                });
                
                // Cập nhật tổng tiền
                totalAmountEl.textContent = formatCurrency(totalAmount);
                
                // Thêm sự kiện cho input số lượng và đơn giá
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
                            alert(`Số lượng xuất không thể vượt quá số lượng tồn kho (${selectedProducts[index].stock})!`);
                        } else {
                            selectedProducts[index].quantity = newQuantity;
                        }
                        
                        // Cập nhật thành tiền
                        selectedProducts[index].total = selectedProducts[index].quantity * selectedProducts[index].price;
                        document.querySelector(`.product-total[data-index="${index}"]`).textContent = formatCurrency(selectedProducts[index].total);
                        
                        // Cập nhật tổng tiền
                        updateTotalAmount();
                    });
                });
                
                const priceInputs = document.querySelectorAll('.price-input');
                priceInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newPrice = parseInt(this.value);
                        
                        if (newPrice < 0) {
                            this.value = 0;
                            selectedProducts[index].price = 0;
                        } else {
                            selectedProducts[index].price = newPrice;
                        }
                        
                        // Cập nhật thành tiền
                        selectedProducts[index].total = selectedProducts[index].quantity * selectedProducts[index].price;
                        document.querySelector(`.product-total[data-index="${index}"]`).textContent = formatCurrency(selectedProducts[index].total);
                        
                        // Cập nhật tổng tiền
                        updateTotalAmount();
                    });
                });
                
                // Thêm sự kiện xóa sản phẩm
                const deleteButtons = document.querySelectorAll('.delete-product');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedProducts.splice(index, 1);
                        updateProductList();
                    });
                });
            }
            
            function updateTotalAmount() {
                const totalAmount = selectedProducts.reduce((sum, product) => sum + product.total, 0);
                totalAmountEl.textContent = formatCurrency(totalAmount);
            }
            
            function formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' })
                    .format(amount)
                    .replace('₫', 'VNĐ');
            }
        });
    </script>
</body>

</html> 