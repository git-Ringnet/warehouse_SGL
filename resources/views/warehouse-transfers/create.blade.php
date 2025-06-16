<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tạo phiếu chuyển kho - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <h1 class="text-xl font-bold text-gray-800">Tạo phiếu chuyển kho</h1>
            <a href="{{ route('warehouse-transfers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        
        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('warehouse-transfers.store') }}" method="POST">
                    @csrf
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Thông tin phiếu chuyển kho</h2>
                    
                    @if ($errors->any())
                    <div class="mb-4 bg-red-50 p-3 rounded border border-red-200">
                        <ul class="list-disc list-inside text-red-500">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cột 1 -->
                        <div class="space-y-4">
                            <div>
                                <label for="transfer_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã phiếu chuyển</label>
                                <input type="text" id="transfer_code" name="transfer_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã phiếu chuyển" value="{{ old('transfer_code') }}" required>
                            </div>
                            
                            <div>
                                <label for="source_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho nguồn</label>
                                <select id="source_warehouse_id" name="source_warehouse_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn kho nguồn --</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('source_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="space-y-4">
                            <div>
                                <label for="destination_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho đích</label>
                                <select id="destination_warehouse_id" name="destination_warehouse_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn kho đích --</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('destination_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="transfer_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày chuyển kho</label>
                                <input type="date" id="transfer_date" name="transfer_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                            </div>
                            
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1 required">Nhân viên thực hiện</label>
                                <select id="employee_id" name="employee_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn nhân viên --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái</label>
                                <select id="status" name="status" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Đang chuyển</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                    <option value="canceled" {{ old('status') == 'canceled' ? 'selected' : '' }}>Đã hủy</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phần chọn vật tư -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Danh sách vật tư chuyển kho</h3>
                        
                        <div id="materials-container">
                            <!-- Mẫu một hàng vật tư -->
                            <div class="material-row border border-gray-200 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Loại sản phẩm</label>
                                        <select name="materials[0][item_type]" class="item-type-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required onchange="updateItemOptions(this, 0)">
                                            <option value="">-- Chọn loại --</option>
                                            <option value="material">Vật tư</option>
                                            <option value="product">Thành phẩm</option>
                                            <option value="good">Hàng hóa</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư/ thành phẩm/ hàng hoá</label>
                                        <select name="materials[0][material_id]" class="material-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                            <option value="">-- Chọn sản phẩm --</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                        <input type="number" name="materials[0][quantity]" class="quantity-input w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập số lượng" value="1" min="1" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">List số seri (nếu có)</label>
                                    <textarea name="materials[0][serial_numbers]" class="serial-input w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" rows="2" placeholder="Nhập danh sách số seri, mỗi số seri trên một dòng hoặc ngăn cách bằng dấu phẩy"></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Số seri không bắt buộc. Nếu nhập, số lượng seri nên trùng khớp với số lượng.</p>
                                </div>
                                <div class="mt-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                    <textarea name="materials[0][notes]" class="notes-input w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" rows="2" placeholder="Ghi chú cho vật tư này (nếu có)"></textarea>
                                </div>
                                <div class="mt-3 flex justify-end">
                                    <button type="button" class="remove-material text-red-500 hover:text-red-700" style="display: none;">
                                        <i class="fas fa-trash mr-1"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-2">
                            <button type="button" id="add-material" class="flex items-center text-blue-500 hover:text-blue-700">
                                <i class="fas fa-plus-circle mr-1"></i> Thêm vật tư
                            </button>
                        </div>
                    </div>
                    
                    <!-- Phần hidden input sẽ lưu dữ liệu để submit -->
                    <input type="hidden" id="materials_json" name="materials_json" value="{{ old('materials_json', '[]') }}">
                    
                    <!-- Bảng tổng hợp các vật tư đã thêm -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Tổng hợp vật tư, hàng hoá đã thêm</h3>
                        <div class="overflow-x-auto">
                            <table id="summary-table" class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã - Tên vật tư</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kho nguồn</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tồn kho</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số seri</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr>
                                        <td colspan="7" class="px-4 py-4 text-sm text-gray-500 text-center">Chưa có vật tư nào được thêm</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Ghi chú -->
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú về phiếu chuyển kho (nếu có)">{{ old('notes') }}</textarea>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('warehouse-transfers.index') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu phiếu chuyển kho
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Tự động điền ngày hiện tại vào ô ngày chuyển kho
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            
            document.getElementById('transfer_date').value = `${year}-${month}-${day}`;
            
            // Thêm sự kiện cho nút "Thêm vật tư"
            document.getElementById('add-material').addEventListener('click', addMaterialRow);
            
            // Khởi tạo các sự kiện cho hàng vật tư đầu tiên
            setupMaterialRowEvents(document.querySelector('.material-row'));
            
            // Cập nhật bảng tổng hợp ban đầu
            updateSummaryTable();
            
            // Khởi tạo danh sách vật tư/thành phẩm/hàng hoá
            initializeItemLists();
        });
        
        // Dữ liệu các sản phẩm từ cả 3 bảng
        let itemsData = {
            material: [],
            product: [],
            good: []
        };
        
        // Khởi tạo danh sách vật tư/thành phẩm/hàng hoá
        function initializeItemLists() {
            // Chuyển dữ liệu từ Blade sang Javascript
            itemsData.material = {!! json_encode($materials->map(function($material) {
                return [
                    'id' => $material->id,
                    'name' => $material->code . ' - ' . $material->name,
                    'type' => 'material',
                    'category' => $material->category ?? 'other'
                ];
            })) !!};
            
            itemsData.product = {!! json_encode($products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->code . ' - ' . $product->name,
                    'type' => 'product',
                    'category' => 'product'
                ];
            })) !!};
            
            itemsData.good = {!! json_encode($goods->map(function($good) {
                return [
                    'id' => $good->id,
                    'name' => $good->code . ' - ' . $good->name,
                    'type' => 'good',
                    'category' => $good->category ?? 'other'
                ];
            })) !!};
        }
        
        // Cập nhật danh sách sản phẩm khi chọn loại
        function updateItemOptions(selectElement, rowIndex) {
            const itemType = selectElement.value;
            const row = selectElement.closest('.material-row');
            const materialSelect = row.querySelector('.material-select');
            
            // Xóa tất cả các option hiện tại (trừ option đầu tiên)
            while (materialSelect.options.length > 1) {
                materialSelect.remove(1);
            }
            
            // Nếu không có loại sản phẩm được chọn, không thêm các option
            if (!itemType) {
                return;
            }
            
            // Thêm các option mới dựa trên loại sản phẩm
            const items = itemsData[itemType] || [];
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.text = item.name;
                option.dataset.type = item.type;
                option.dataset.category = item.category;
                materialSelect.add(option);
            });
            
            // Cập nhật bảng tổng hợp
            updateSummaryTable();
            updateMaterialsJson();
        }
        
        // Biến đếm số lượng hàng vật tư
        let materialCount = 1;
        
        // Ngăn chặn việc chọn cùng một kho cho cả nguồn và đích
        document.getElementById('source_warehouse_id').addEventListener('change', function() {
            updateWarehouseOptions();
            // Cập nhật lại bảng tổng hợp khi thay đổi kho nguồn
            updateSummaryTable();
        });
        
        document.getElementById('destination_warehouse_id').addEventListener('change', function() {
            updateWarehouseOptions();
        });
        
        function updateWarehouseOptions() {
            const sourceWarehouse = document.getElementById('source_warehouse_id').value;
            const destinationWarehouse = document.getElementById('destination_warehouse_id').value;
            
            if (sourceWarehouse && destinationWarehouse && sourceWarehouse === destinationWarehouse) {
                alert('Kho nguồn và kho đích không được trùng nhau');
                document.getElementById('destination_warehouse_id').value = '';
            }
        }
        
        // Hàm thêm hàng vật tư mới
        function addMaterialRow() {
            const container = document.getElementById('materials-container');
            const template = container.querySelector('.material-row').cloneNode(true);
            
            // Cập nhật các attributes
            const inputs = template.querySelectorAll('select, input, textarea');
            inputs.forEach(input => {
                const nameAttr = input.getAttribute('name');
                if (nameAttr) {
                    input.setAttribute('name', nameAttr.replace('[0]', `[${materialCount}]`));
                    
                    // Reset giá trị
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else if (input.type === 'number' && input.name.includes('quantity')) {
                        input.value = '1';
                    } else {
                        input.value = '';
                    }
                }
                
                // Thêm sự kiện onchange cho select loại sản phẩm
                if (input.classList.contains('item-type-select')) {
                    input.setAttribute('onchange', `updateItemOptions(this, ${materialCount})`);
                }
            });
            
            // Hiển thị nút xóa
            const removeButton = template.querySelector('.remove-material');
            removeButton.style.display = 'inline-flex';
            removeButton.addEventListener('click', function() {
                removeMaterialRow(template);
            });
            
            // Thêm hàng mới vào container
            container.appendChild(template);
            
            // Thiết lập các sự kiện
            setupMaterialRowEvents(template);
            
            materialCount++;
            
            // Cập nhật hiển thị của các nút xóa
            updateRemoveButtons();
        }
        
        // Thiết lập các sự kiện cho một hàng vật tư
        function setupMaterialRowEvents(row) {
            // Lắng nghe sự kiện thay đổi để cập nhật bảng tổng hợp
            const inputs = row.querySelectorAll('select, input, textarea');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    updateSummaryTable();
                    updateMaterialsJson();
                });
            });
        }
        
        // Xóa một hàng vật tư
        function removeMaterialRow(row) {
            if (confirm('Bạn có chắc chắn muốn xóa vật tư này không?')) {
                row.remove();
                updateRemoveButtons();
                updateSummaryTable();
                updateMaterialsJson();
            }
        }
        
        // Cập nhật hiển thị của các nút xóa
        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.material-row');
            const removeButtons = document.querySelectorAll('.remove-material');
            
            if (rows.length <= 1) {
                removeButtons.forEach(btn => btn.style.display = 'none');
            } else {
                removeButtons.forEach(btn => btn.style.display = 'inline-flex');
            }
        }
        
        // Cập nhật bảng tổng hợp vật tư
        function updateSummaryTable() {
            const table = document.getElementById('summary-table');
            const tbody = table.querySelector('tbody');
            
            // Xóa tất cả các hàng hiện tại
            tbody.innerHTML = '';
            
            // Lấy thông tin từ tất cả các hàng vật tư
            const materialRows = document.querySelectorAll('.material-row');
            const materials = [];
            const sourceWarehouseId = document.getElementById('source_warehouse_id').value;
            const sourceWarehouseName = sourceWarehouseId ? 
                document.getElementById('source_warehouse_id').options[document.getElementById('source_warehouse_id').selectedIndex].text : 
                'Chưa chọn kho';
            
            materialRows.forEach((row, index) => {
                const itemTypeSelect = row.querySelector('.item-type-select');
                const materialSelect = row.querySelector('.material-select');
                
                if (materialSelect.value && itemTypeSelect.value) {
                    const materialId = materialSelect.value;
                    const materialName = materialSelect.options[materialSelect.selectedIndex].text;
                    const quantity = row.querySelector('.quantity-input').value;
                    const serialNumbers = row.querySelector('.serial-input').value;
                    const notes = row.querySelector('.notes-input').value;
                    const itemType = itemTypeSelect.value;
                    
                    materials.push({
                        id: materialId,
                        name: materialName,
                        quantity: quantity,
                        type: itemType,
                        serial_numbers: serialNumbers,
                        notes: notes,
                        warehouse_id: sourceWarehouseId,
                        warehouse_name: sourceWarehouseName,
                        stock_quantity: 0 // Sẽ được cập nhật sau
                    });
                    
                    // Kiểm tra tồn kho nếu đã chọn kho nguồn
                    if (sourceWarehouseId) {
                        checkInventory(materialId, sourceWarehouseId, itemType, index);
                    }
                }
            });
            
            // Nếu không có vật tư nào được chọn, hiển thị dòng thông báo
            if (materials.length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = '<td colspan="7" class="px-4 py-4 text-sm text-gray-500 text-center">Chưa có vật tư nào được thêm</td>';
                tbody.appendChild(emptyRow);
                return;
            }
            
            // Tạo các hàng mới cho bảng tổng hợp
            materials.forEach((material, index) => {
                const row = document.createElement('tr');
                row.dataset.materialId = material.id;
                row.dataset.materialType = material.type;
                
                // Xác định loại vật tư để hiển thị
                let typeDisplay = 'Khác';
                let typeClass = 'bg-gray-100 text-gray-800';
                
                switch(material.type) {
                    case 'material':
                        typeDisplay = 'Vật tư';
                        typeClass = 'bg-blue-100 text-blue-800';
                        break;
                    case 'product':
                        typeDisplay = 'Thành phẩm';
                        typeClass = 'bg-green-100 text-green-800';
                        break;
                    case 'good':
                        typeDisplay = 'Hàng hoá';
                        typeClass = 'bg-yellow-100 text-yellow-800';
                        break;
                }
                
                // Tóm tắt số seri để hiển thị
                let serialDisplay = 'Không có';
                if (material.serial_numbers) {
                    const serials = material.serial_numbers.split(/[,;\n\r]+/).filter(s => s.trim());
                    if (serials.length > 0) {
                        serialDisplay = serials.length > 3 
                            ? `${serials.slice(0, 3).join(', ')}... (${serials.length} số seri)` 
                            : serials.join(', ');
                    }
                }
                
                // Hiển thị cảnh báo nếu số lượng chuyển lớn hơn tồn kho
                const stockClass = parseInt(material.quantity) > material.stock_quantity ? 'text-red-600 font-bold' : 'text-gray-900';
                
                row.innerHTML = `
                    <td class="px-4 py-2 text-sm text-gray-900">${index + 1}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${material.name}</td>
                    <td class="px-4 py-2 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs font-medium ${typeClass}">${typeDisplay}</span>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900">${material.warehouse_name}</td>
                    <td class="px-4 py-2 text-sm stock-quantity ${stockClass}" data-id="${material.id}" data-type="${material.type}">${material.stock_quantity}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${material.quantity}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${serialDisplay}</td>
                `;
                
                tbody.appendChild(row);
            });
        }
        
        // Hàm kiểm tra tồn kho
        function checkInventory(materialId, warehouseId, itemType, index) {
            if (!materialId || !warehouseId) return;
            
            console.log(`Đang kiểm tra tồn kho: materialId=${materialId}, warehouseId=${warehouseId}, itemType=${itemType}`);
            
            // Lấy base URL từ window.location
            const baseUrl = window.location.origin;
            const apiUrl = `${baseUrl}/warehouse-transfers/check-inventory`;
            
            console.log('Gọi API URL:', apiUrl);
            
            // Sử dụng phương thức POST thay vì GET
            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    material_id: materialId,
                    warehouse_id: warehouseId,
                    item_type: itemType
                })
            })
            .then(response => {
                console.log('API Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('API Response data:', data);
                // Cập nhật hiển thị tồn kho trong bảng
                const stockCells = document.querySelectorAll(`.stock-quantity[data-id="${materialId}"][data-type="${itemType}"]`);
                console.log('Tìm thấy', stockCells.length, 'ô cần cập nhật');
                
                stockCells.forEach(cell => {
                    cell.textContent = data.quantity;
                    
                    // Hiển thị cảnh báo nếu tồn kho không đủ
                    const row = cell.closest('tr');
                    const quantityCell = row.querySelector('td:nth-child(6)');
                    const quantity = parseInt(quantityCell.textContent);
                    
                    console.log('Số lượng chuyển:', quantity, 'Tồn kho:', data.quantity);
                    
                    if (quantity > data.quantity) {
                        cell.classList.add('text-red-600', 'font-bold');
                        // Hiển thị thông báo cảnh báo
                        const warningMsg = document.createElement('div');
                        warningMsg.className = 'text-red-600 text-xs mt-1';
                        warningMsg.textContent = 'Tồn kho không đủ!';
                        
                        // Xóa cảnh báo cũ nếu có
                        const oldWarning = cell.querySelector('.text-red-600.text-xs');
                        if (oldWarning) oldWarning.remove();
                        
                        cell.appendChild(warningMsg);
                    } else {
                        cell.classList.remove('text-red-600', 'font-bold');
                        // Xóa thông báo cảnh báo nếu có
                        const oldWarning = cell.querySelector('.text-red-600.text-xs');
                        if (oldWarning) oldWarning.remove();
                    }
                });
            })
            .catch(error => {
                console.error('Lỗi khi kiểm tra tồn kho:', error);
            });
        }
        
        // Cập nhật dữ liệu JSON để submit
        function updateMaterialsJson() {
            const materials = [];
            const materialRows = document.querySelectorAll('.material-row');
            
            materialRows.forEach(row => {
                const itemTypeSelect = row.querySelector('.item-type-select');
                const materialSelect = row.querySelector('.material-select');
                
                if (materialSelect.value && itemTypeSelect.value) {
                    const materialId = materialSelect.value;
                    const materialName = materialSelect.options[materialSelect.selectedIndex].text;
                    const quantity = row.querySelector('.quantity-input').value;
                    const serialNumbers = row.querySelector('.serial-input').value;
                    const notes = row.querySelector('.notes-input').value;
                    const itemType = itemTypeSelect.value;
                    
                    materials.push({
                        id: materialId,
                        name: materialName,
                        quantity: quantity,
                        type: itemType,
                        serial_numbers: serialNumbers,
                        notes: notes
                    });
                }
            });
            
            document.getElementById('materials_json').value = JSON.stringify(materials);
        }
        
        // Kiểm tra trước khi submit form
        document.querySelector('form').addEventListener('submit', function(e) {
            // Cập nhật JSON data trước khi submit
            updateMaterialsJson();
            
            // Lấy dữ liệu từ input hidden
            const materialsJson = document.getElementById('materials_json').value;
            let materials = [];
            
            try {
                materials = JSON.parse(materialsJson);
            } catch (error) {
                console.error('Lỗi khi phân tích dữ liệu JSON:', error);
            }
            
            // Kiểm tra xem có ít nhất một vật tư nào được chọn không
            if (materials.length === 0) {
                e.preventDefault();
                alert('Vui lòng thêm ít nhất một vật tư vào danh sách trước khi tạo phiếu chuyển kho.');
                return false;
            }
            
            // Kiểm tra tồn kho
            const stockCells = document.querySelectorAll('.stock-quantity.text-red-600');
            if (stockCells.length > 0) {
                e.preventDefault();
                alert('Có vật tư có số lượng tồn kho không đủ. Vui lòng kiểm tra lại số lượng chuyển kho.');
                return false;
            }
        });
    </script>
</body>
</html> 