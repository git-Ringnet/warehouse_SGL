<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu chuyển kho - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu chuyển kho</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Mã phiếu: CK001
                </div>
            </div>
            <a href="{{ url('/warehouse-transfers/1') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="#" method="POST">
                    @csrf
                    @method('PUT')
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Thông tin phiếu chuyển kho</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cột 1 -->
                        <div class="space-y-4">
                            <div>
                                <label for="serial" class="block text-sm font-medium text-gray-700 mb-1 required">Serial được chuyển</label>
                                <input type="text" id="serial" name="serial" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="SER-VT001-001" required>
                            </div>
                            
                            <div>
                                <label for="source_warehouse" class="block text-sm font-medium text-gray-700 mb-1 required">Kho nguồn</label>
                                <select id="source_warehouse" name="source_warehouse" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn kho nguồn --</option>
                                    <option value="main" selected>Kho chính</option>
                                    <option value="secondary">Kho phụ</option>
                                    <option value="components">Kho linh kiện</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                <input type="number" id="quantity" name="quantity" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="100" min="1" required>
                            </div>
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="space-y-4">
                            <div>
                                <label for="destination_warehouse" class="block text-sm font-medium text-gray-700 mb-1 required">Kho đích</label>
                                <select id="destination_warehouse" name="destination_warehouse" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn kho đích --</option>
                                    <option value="main">Kho chính</option>
                                    <option value="secondary">Kho phụ</option>
                                    <option value="components" selected>Kho linh kiện</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="transfer_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày chuyển kho</label>
                                <input type="date" id="transfer_date" name="transfer_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="2024-06-01" required>
                            </div>
                            
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1 required">Nhân viên thực hiện</label>
                                <select id="employee_id" name="employee_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn nhân viên --</option>
                                    <option value="1" selected>Nguyễn Văn A</option>
                                    <option value="2">Trần Thị B</option>
                                    <option value="3">Lê Văn C</option>
                                    <option value="4">Phạm Thị D</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái</label>
                                <select id="status" name="status" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="pending">Chờ xác nhận</option>
                                    <option value="in_progress">Đang chuyển</option>
                                    <option value="completed" selected>Hoàn thành</option>
                                    <option value="canceled">Đã hủy</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phần chọn vật tư -->
                    <div class="mt-6">
                        <h3 class="text-md font-semibold text-gray-800 mb-3">Danh sách vật tư chuyển kho</h3>
                        
                        <!-- Bộ lọc loại vật tư -->
                        <div class="mb-3 flex items-center space-x-4">
                            <span class="text-sm font-medium text-gray-700">Loại vật tư:</span>
                            <div class="flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="material_type" value="all" class="form-radio text-blue-500" checked>
                                    <span class="ml-2 text-sm text-gray-700">Tất cả</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="material_type" value="component" class="form-radio text-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Linh kiện</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="material_type" value="product" class="form-radio text-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Thành phẩm</span>
                                </label>
                            </div>
                        </div>

                        <div class="border rounded-lg border-gray-300 p-4 bg-gray-50">
                            <div class="flex items-center space-x-2 mb-3">
                                <select id="material_select" class="flex-grow h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">-- Chọn vật tư --</option>
                                    <!-- Linh kiện -->
                                    <option value="C1" data-type="component">LK001 - Ốc vít 10mm</option>
                                    <option value="C2" data-type="component">LK002 - Ống nhựa PVC 20mm</option>
                                    <option value="C3" data-type="component">LK003 - Dây điện 2.5mm</option>
                                    <option value="C4" data-type="component">LK004 - Bóng đèn LED 10W</option>
                                    <option value="C5" data-type="component">LK005 - Keo dán 2 thành phần</option>
                                    <!-- Thành phẩm -->
                                    <option value="P1" data-type="product">TP001 - Đèn led downlight 12W</option>
                                    <option value="P2" data-type="product">TP002 - Hộp điện âm tường</option>
                                    <option value="P3" data-type="product">TP003 - Bảng điều khiển cảm ứng</option>
                                    <option value="P4" data-type="product">TP004 - Bộ chuyển nguồn tự động</option>
                                    <option value="P5" data-type="product">TP005 - Máy bơm nước mini</option>
                                </select>
                                <input type="number" id="material_quantity" class="w-24 h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="SL" min="1" value="1">
                                <button type="button" id="add_material" class="h-10 w-10 flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            
                            <div id="selected_materials" class="border rounded-lg border-gray-200 p-2 bg-white min-h-[100px] max-h-[200px] overflow-y-auto">
                                <!-- Danh sách vật tư được chọn sẽ xuất hiện ở đây -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phần hidden input sẽ lưu dữ liệu để submit -->
                    <input type="hidden" id="materials_json" name="materials_json" value="">
                    
                    <!-- Ghi chú -->
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">Chuyển bổ sung</textarea>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ url('/warehouse-transfers/1') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Ngăn chặn việc chọn cùng một kho cho cả nguồn và đích
        document.getElementById('source_warehouse').addEventListener('change', function() {
            updateWarehouseOptions();
        });
        
        document.getElementById('destination_warehouse').addEventListener('change', function() {
            updateWarehouseOptions();
        });
        
        function updateWarehouseOptions() {
            const sourceWarehouse = document.getElementById('source_warehouse').value;
            const destinationWarehouse = document.getElementById('destination_warehouse').value;
            
            if (sourceWarehouse && destinationWarehouse && sourceWarehouse === destinationWarehouse) {
                alert('Kho nguồn và kho đích không được trùng nhau');
                document.getElementById('destination_warehouse').value = '';
            }
        }
        
        // Quản lý chọn nhiều vật tư
        let selectedMaterials = [
            // Dữ liệu vật tư ban đầu (từ phiếu đã lưu trong cơ sở dữ liệu)
            {
                id: "C1",
                name: "LK001 - Ốc vít 10mm",
                type: "component",
                quantity: "80"
            },
            {
                id: "P2",
                name: "TP002 - Hộp điện âm tường",
                type: "product",
                quantity: "20"
            }
        ];
        
        // Quản lý bộ lọc loại vật tư
        document.querySelectorAll('input[name="material_type"]').forEach(radio => {
            radio.addEventListener('change', filterMaterials);
        });
        
        function filterMaterials() {
            const filterValue = document.querySelector('input[name="material_type"]:checked').value;
            const materialOptions = document.querySelectorAll('#material_select option');
            
            materialOptions.forEach(option => {
                if (option.value === '') return; // Skip placeholder
                
                const type = option.getAttribute('data-type');
                if (filterValue === 'all' || type === filterValue) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Reset selection
            document.getElementById('material_select').value = '';
        }
        
        // Khởi tạo dữ liệu
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedMaterialsDisplay();
        });
        
        document.getElementById('add_material').addEventListener('click', function() {
            const materialSelect = document.getElementById('material_select');
            const materialId = materialSelect.value;
            const materialText = materialSelect.options[materialSelect.selectedIndex].text;
            const quantity = document.getElementById('material_quantity').value;
            const materialType = materialSelect.options[materialSelect.selectedIndex].getAttribute('data-type');
            
            if (!materialId) {
                alert('Vui lòng chọn vật tư');
                return;
            }
            
            if (quantity < 1) {
                alert('Số lượng phải lớn hơn 0');
                return;
            }
            
            // Kiểm tra nếu vật tư đã được chọn
            const existingIndex = selectedMaterials.findIndex(m => m.id === materialId);
            if (existingIndex >= 0) {
                alert('Vật tư này đã được thêm vào danh sách');
                return;
            }
            
            // Thêm vật tư vào danh sách
            selectedMaterials.push({
                id: materialId,
                name: materialText,
                type: materialType || '',
                quantity: quantity
            });
            
            // Cập nhật hiển thị
            updateSelectedMaterialsDisplay();
            
            // Reset lựa chọn
            materialSelect.value = '';
            document.getElementById('material_quantity').value = '1';
        });
        
        function updateSelectedMaterialsDisplay() {
            const container = document.getElementById('selected_materials');
            
            if (selectedMaterials.length === 0) {
                container.classList.add('hidden');
                container.innerHTML = '';
                document.getElementById('materials_json').value = '[]';
                return;
            }
            
            container.classList.remove('hidden');
            container.innerHTML = '';
            
            selectedMaterials.forEach((material, index) => {
                const materialElem = document.createElement('div');
                materialElem.className = 'flex items-center justify-between py-2 px-3 border-b border-gray-200 last:border-b-0';
                
                // Hiển thị loại vật tư 
                const typeLabel = material.type === 'component' ? 
                    '<span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded text-xs mr-2">Linh kiện</span>' : 
                    (material.type === 'product' ? 
                        '<span class="px-1.5 py-0.5 bg-green-100 text-green-800 rounded text-xs mr-2">Thành phẩm</span>' : '');
                
                materialElem.innerHTML = `
                    <div class="flex items-center">
                        ${typeLabel}
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-700">${material.name}</span>
                            <span class="text-xs text-gray-500">Số lượng: ${material.quantity}</span>
                        </div>
                    </div>
                    <button type="button" class="text-red-500 hover:text-red-700" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                // Thêm sự kiện xóa
                const deleteButton = materialElem.querySelector('button');
                deleteButton.addEventListener('click', function() {
                    selectedMaterials.splice(index, 1);
                    updateSelectedMaterialsDisplay();
                });
                
                container.appendChild(materialElem);
            });
            
            // Cập nhật hidden input
            document.getElementById('materials_json').value = JSON.stringify(selectedMaterials);
        }
        
        // Kiểm tra form submit để đảm bảo có ít nhất một vật tư
        document.querySelector('form').addEventListener('submit', function(event) {
            if (selectedMaterials.length === 0) {
                event.preventDefault();
                alert('Vui lòng thêm ít nhất một vật tư');
                return false;
            }
        });
        
        // Áp dụng bộ lọc ban đầu
        filterMaterials();
    </script>
</body>
</html> 