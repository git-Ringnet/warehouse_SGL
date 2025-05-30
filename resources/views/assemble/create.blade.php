<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo phiếu lắp ráp - SGL</title>
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
                <a href="{{ asset('assemble') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Tạo phiếu lắp ráp</h1>
            </div>
        </header>

        <main class="p-6">
            <form action="#" method="POST">
                @csrf

                <!-- Thông tin phiếu lắp ráp -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tools text-blue-500 mr-2"></i>
                        Thông tin phiếu lắp ráp
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="assembly_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu lắp ráp</label>
                            <input type="text" id="assembly_code" name="assembly_code" value="LR{{ date('Ymd') }}-001" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="assembly_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày lắp ráp <span class="text-red-500">*</span></label>
                            <input type="date" id="assembly_date" name="assembly_date" value="{{ date('Y-m-d') }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="assembly_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại lắp ráp <span class="text-red-500">*</span></label>
                            <select id="assembly_type" name="assembly_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn loại lắp ráp --</option>
                                <option value="new">Thiết bị mới</option>
                                <option value="warranty">Bảo hành</option>
                                <option value="repair">Sửa chữa</option>
                                <option value="upgrade">Nâng cấp</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1 required">Sản phẩm <span class="text-red-500">*</span></label>
                            <select id="product_id" name="product_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn sản phẩm --</option>
                                <option value="1">Radio SPA Pro</option>
                                <option value="2">Radio SPA Lite</option>
                                <option value="3">Radio SPA Mini</option>
                                <option value="4">Radio SPA Plus</option>
                                <option value="5">Radio SPA Ultra</option>
                            </select>
                        </div>
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1 required">Người phụ trách <span class="text-red-500">*</span></label>
                            <select id="assigned_to" name="assigned_to" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người phụ trách --</option>
                                <option value="1">Nguyễn Văn A</option>
                                <option value="2">Trần Thị B</option>
                                <option value="3">Lê Văn C</option>
                                <option value="4">Phạm Thị D</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="assembly_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="assembly_note" name="assembly_note" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập ghi chú cho phiếu lắp ráp (nếu có)"></textarea>
                    </div>
                </div>

                <!-- Danh sách linh kiện -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-microchip text-blue-500 mr-2"></i>
                        Danh sách linh kiện sử dụng
                    </h2>

                    <!-- Tìm kiếm linh kiện -->
                    <div class="mb-4">
                        <div class="relative">
                            <input type="text" id="component_search" placeholder="Nhập serial hoặc tên linh kiện để tìm kiếm..."
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <button type="button" id="add_component_btn"
                                class="absolute inset-y-0 right-0 px-3 bg-blue-500 text-white rounded-r-lg hover:bg-blue-600 transition-colors">
                                Thêm
                            </button>
                        </div>
                    </div>

                    <!-- Bảng linh kiện đã chọn -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Serial
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Loại linh kiện
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên linh kiện
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Vị trí lắp đặt
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ghi chú
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="component_list" class="bg-white divide-y divide-gray-200">
                                <!-- Dữ liệu linh kiện sẽ được thêm vào đây -->
                                <tr id="no_components_row">
                                    <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        Chưa có linh kiện nào được thêm vào phiếu lắp ráp
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ asset('assemble') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Lưu phiếu lắp ráp
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dữ liệu mẫu cho linh kiện
            const sampleComponents = [
                { id: 1, serial: 'SN001', type: 'Bộ xử lý', name: 'CPU Intel i5', position: 'Mainboard' },
                { id: 2, serial: 'SN002', type: 'Bộ nhớ', name: 'RAM 8GB DDR4', position: 'Slot RAM 1' },
                { id: 3, serial: 'SN003', type: 'Bộ nhớ', name: 'RAM 8GB DDR4', position: 'Slot RAM 2' },
                { id: 4, serial: 'SN004', type: 'Lưu trữ', name: 'SSD 256GB', position: 'Khe M.2' },
                { id: 5, serial: 'SN005', type: 'Nguồn', name: 'Nguồn 400W', position: 'Hộp nguồn' },
                { id: 6, serial: 'SN006', type: 'Màn hình', name: 'LCD 7 inch', position: 'Mặt trước' },
                { id: 7, serial: 'SN007', type: 'Bàn phím', name: 'Bàn phím 4x4', position: 'Mặt trước' },
                { id: 8, serial: 'SN008', type: 'Anten', name: 'Anten 5G', position: 'Mặt sau' },
                { id: 9, serial: 'SN009', type: 'Bo mạch', name: 'Mạch khuếch đại', position: 'Khe PCI' },
                { id: 10, serial: 'SN010', type: 'Pin', name: 'Pin Lithium 5000mAh', position: 'Khay pin' }
            ];
            
            // Xử lý thêm linh kiện
            const componentSearchInput = document.getElementById('component_search');
            const addComponentBtn = document.getElementById('add_component_btn');
            const componentList = document.getElementById('component_list');
            const noComponentsRow = document.getElementById('no_components_row');
            
            let selectedComponents = [];
            
            addComponentBtn.addEventListener('click', function() {
                const searchTerm = componentSearchInput.value.trim().toLowerCase();
                
                if (!searchTerm) {
                    alert('Vui lòng nhập serial hoặc tên linh kiện để tìm kiếm!');
                    return;
                }
                
                // Tìm linh kiện trong dữ liệu mẫu
                const foundComponent = sampleComponents.find(c => 
                    c.serial.toLowerCase().includes(searchTerm) || 
                    c.name.toLowerCase().includes(searchTerm)
                );
                
                if (!foundComponent) {
                    alert('Không tìm thấy linh kiện phù hợp!');
                    return;
                }
                
                // Kiểm tra xem linh kiện đã được thêm chưa
                if (selectedComponents.some(c => c.id === foundComponent.id)) {
                    alert('Linh kiện này đã được thêm vào phiếu lắp ráp!');
                    return;
                }
                
                // Thêm linh kiện vào danh sách
                selectedComponents.push({
                    ...foundComponent,
                    note: ''
                });
                
                // Cập nhật giao diện
                updateComponentList();
                
                // Xóa nội dung tìm kiếm
                componentSearchInput.value = '';
            });
            
            function updateComponentList() {
                // Ẩn thông báo "không có linh kiện"
                if (selectedComponents.length > 0) {
                    noComponentsRow.style.display = 'none';
                } else {
                    noComponentsRow.style.display = '';
                }
                
                // Xóa các hàng linh kiện hiện tại (trừ hàng thông báo)
                const componentRows = document.querySelectorAll('.component-row');
                componentRows.forEach(row => row.remove());
                
                // Thêm hàng cho mỗi linh kiện đã chọn
                selectedComponents.forEach((component, index) => {
                    const row = document.createElement('tr');
                    row.className = 'component-row';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="hidden" name="components[${index}][id]" value="${component.id}">
                            ${component.serial}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${component.type}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${component.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="text" name="components[${index}][position]" value="${component.position}"
                                class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="text" name="components[${index}][note]" value="${component.note}"
                                class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ghi chú">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-500 hover:text-red-700 delete-component" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    
                    componentList.insertBefore(row, noComponentsRow);
                });
                
                // Thêm sự kiện xóa linh kiện
                const deleteButtons = document.querySelectorAll('.delete-component');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedComponents.splice(index, 1);
                        updateComponentList();
                    });
                });
            }
            
            // Kiểm tra sản phẩm được chọn để hiển thị linh kiện gợi ý
            const productSelect = document.getElementById('product_id');
            productSelect.addEventListener('change', function() {
                const productId = this.value;
                
                if (productId) {
                    // Đây là nơi bạn có thể thêm logic để lấy danh sách linh kiện dựa trên sản phẩm được chọn
                    // Trong ví dụ này, chúng ta sẽ chỉ hiển thị một thông báo
                    
                    const assemblyType = document.getElementById('assembly_type').value;
                    let message = '';
                    
                    if (assemblyType === 'new') {
                        message = 'Sản phẩm mới cần đầy đủ các linh kiện. Hãy thêm các linh kiện cần thiết vào danh sách.';
                    } else if (assemblyType === 'warranty' || assemblyType === 'repair') {
                        message = 'Chỉ thêm các linh kiện cần thay thế vào danh sách.';
                    } else if (assemblyType === 'upgrade') {
                        message = 'Thêm các linh kiện mới cần nâng cấp vào danh sách.';
                    } else {
                        message = 'Hãy chọn loại lắp ráp để xem hướng dẫn về linh kiện.';
                    }
                    
                    alert(message);
                }
            });
        });
    </script>
</body>

</html> 