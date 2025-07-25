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
    <!-- Thêm style cho dấu * -->
    <style>
        .required::after {
            content: " *";
            color: #dc2626;
            font-weight: bold;
        }
    </style>
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
                                <div class="flex space-x-2">
                                    <input type="text" id="transfer_code" name="transfer_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $generated_transfer_code }}" required>
                                    <button type="button" onclick="generateNewCode()" class="h-10 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg flex items-center justify-center transition-colors">
                                        <i class="fas fa-sync-alt mr-2"></i> Tạo mã mới
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Mã phiếu chuyển có thể chỉnh sửa hoặc tạo mới</p>
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
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Nhân viên thực hiện</label>
                                <select id="employee_id" name="employee_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">-- Chọn nhân viên --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Xóa trường trạng thái -->
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">List số seri</label>
                                    <div class="serial-container">
                                        <div class="serial-list mb-2 max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-3">
                                            <!-- Danh sách serial sẽ được thêm vào đây -->
                                            <div class="text-gray-500 text-sm">Chọn kho nguồn và vật tư để hiển thị danh sách serial</div>
                                        </div>
                                        <input type="hidden" name="materials[0][serial_numbers]" class="serial-input">
                                        <div class="serial-info text-xs text-gray-500 mt-1">
                                            <div>Số serial đã chọn: <span class="selected-count">0</span></div>
                                            <div>Số lượng không có serial: <span class="non-serial-count">0</span></div>
                                        </div>
                                        <div class="serial-error text-xs text-red-500 mt-1" style="display: none;"></div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                    <textarea name="materials[0][notes]" class="notes-input w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" rows="2" placeholder="Ghi chú cho vật tư này (nếu có)"></textarea>
                                </div>
                                <div class="mt-3 flex justify-end">
                                    <button type="button" class="remove-material text-red-500 hover:text-red-700">
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

            // Cập nhật hiển thị các nút xóa
            updateRemoveButtons();
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
                    updateRemoveButtons(); // Thêm cập nhật nút xóa khi có thay đổi
                });
            });

            // Thêm sự kiện click cho nút xóa
            const removeButton = row.querySelector('.remove-material');
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    removeMaterialRow(row);
                });
            }

            // Thêm sự kiện cho việc chọn kho và vật tư
            const sourceWarehouseSelect = document.getElementById('source_warehouse_id');
            const itemTypeSelect = row.querySelector('.item-type-select');
            const materialSelect = row.querySelector('.material-select');
            
            [sourceWarehouseSelect, itemTypeSelect, materialSelect].forEach(select => {
                select.addEventListener('change', function() {
                    if (sourceWarehouseSelect.value && materialSelect.value) {
                        loadSerials(row);
                    }
                });
            });
        }
        
        // Cập nhật hiển thị của các nút xóa
        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.material-row');
            const removeButtons = document.querySelectorAll('.remove-material');
            
            // Ẩn/hiện nút xóa dựa trên số lượng hàng
            if (rows.length <= 1) {
                removeButtons.forEach(btn => btn.style.display = 'none');
            } else {
                removeButtons.forEach(btn => btn.style.display = 'inline-flex');
            }
        }

        // Xóa một hàng vật tư
        function removeMaterialRow(row) {
            const rows = document.querySelectorAll('.material-row');
            
            // Nếu chỉ có 1 hàng, hiện thông báo và không cho xóa
            if (rows.length <= 1) {
                alert('Không thể xóa. Phiếu chuyển kho phải có ít nhất một vật tư!');
                return;
            }
            
            // Nếu có nhiều hơn 1 hàng thì hỏi xác nhận xóa
            if (confirm('Bạn có chắc chắn muốn xóa vật tư này không?')) {
                row.remove();
                updateRemoveButtons();
                updateSummaryTable();
                updateMaterialsJson();
            }
        }
        
        // Cập nhật bảng tổng hợp vật tư
        function updateSummaryTable() {
            const tbody = document.querySelector('#summary-table tbody');
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            const sourceWarehouseId = document.getElementById('source_warehouse_id').value;
            const sourceWarehouseSelect = document.getElementById('source_warehouse_id');
            let sourceWarehouseName = 'Không xác định';
            if (sourceWarehouseSelect && sourceWarehouseSelect.selectedIndex >= 0) {
                sourceWarehouseName = sourceWarehouseSelect.options[sourceWarehouseSelect.selectedIndex].text;
            }
            
            // Gom nhóm các item giống nhau
            const groupedMaterials = new Map();
            const materialRows = document.querySelectorAll('.material-row');
            
            materialRows.forEach(row => {
                const itemTypeSelect = row.querySelector('.item-type-select');
                const materialSelect = row.querySelector('.material-select');
                
                if (materialSelect && materialSelect.value && itemTypeSelect && itemTypeSelect.value) {
                    const materialId = materialSelect.value;
                    const itemType = itemTypeSelect.value;
                    const key = `${materialId}-${itemType}`;
                    
                    let materialName = 'Không xác định';
                    if (materialSelect.selectedIndex >= 0) {
                        materialName = materialSelect.options[materialSelect.selectedIndex].text;
                    }
                    
                    const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
                    const serialInput = row.querySelector('.serial-input');
                    let serialNumbers = [];
                    try {
                        if (serialInput.value) {
                            serialNumbers = JSON.parse(serialInput.value);
                        }
                    } catch (e) {
                        console.error('Lỗi khi parse serial numbers:', e);
                    }
                    
                    const notes = row.querySelector('.notes-input').value;
                    
                    if (!groupedMaterials.has(key)) {
                        groupedMaterials.set(key, {
                        id: materialId,
                        name: materialName,
                        type: itemType,
                            quantity: 0,
                            serialNumbers: new Set(),
                            notes: new Set(),
                        warehouse_name: sourceWarehouseName,
                            stock_quantity: '...'
                        });
                    }
                    
                    const group = groupedMaterials.get(key);
                    group.quantity += quantity;
                    serialNumbers.forEach(serial => group.serialNumbers.add(serial));
                    if (notes) group.notes.add(notes);
                    
                    // Kiểm tra tồn kho nếu đã chọn kho nguồn
                    if (sourceWarehouseId) {
                        checkInventory(materialId, sourceWarehouseId, itemType);
                    }
                }
            });
            
            // Nếu không có vật tư nào được chọn, hiển thị dòng thông báo
            if (groupedMaterials.size === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = '<td colspan="7" class="px-4 py-4 text-sm text-gray-500 text-center">Chưa có vật tư nào được thêm</td>';
                tbody.appendChild(emptyRow);
                return;
            }
            
            // Tạo các hàng mới cho bảng tổng hợp từ dữ liệu đã gộp
            Array.from(groupedMaterials.values()).forEach((material, index) => {
                const row = document.createElement('tr');
                
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
                    <td class="px-4 py-2 text-sm text-gray-900">${Array.from(material.serialNumbers).join(', ') || 'Không có'}</td>
                `;
                
                tbody.appendChild(row);
            });
        }
        
        // Hàm kiểm tra tồn kho
        function checkInventory(materialId, warehouseId, itemType) {
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
            const materialMap = new Map();
            const materialRows = document.querySelectorAll('.material-row');
            
            materialRows.forEach(row => {
                const itemTypeSelect = row.querySelector('.item-type-select');
                const materialSelect = row.querySelector('.material-select');
                if (materialSelect.value && itemTypeSelect.value) {
                    const materialId = materialSelect.value;
                    const materialName = materialSelect.options[materialSelect.selectedIndex].text;
                    const quantity = parseInt(row.querySelector('.quantity-input').value) || 0;
                    const serialInput = row.querySelector('.serial-input');
                    let serialNumbers = [];
                    try {
                        if (serialInput.value) {
                            serialNumbers = JSON.parse(serialInput.value).map(s => s.trim());
                        }
                    } catch (e) {
                        console.error('Lỗi khi parse serial numbers:', e);
                    }
                    const notes = row.querySelector('.notes-input').value;
                    const itemType = itemTypeSelect.value;
                    const key = `${materialId}-${itemType}`;
                    if (materialMap.has(key)) {
                        const existing = materialMap.get(key);
                        existing.quantity += quantity;
                        serialNumbers.forEach(serial => existing.serial_numbers.add(serial));
                        if (notes && existing.notes) {
                            existing.notes += '; ' + notes;
                        } else if (notes) {
                            existing.notes = notes;
                        }
                    } else {
                        materialMap.set(key, {
                        id: materialId,
                        name: materialName,
                        quantity: quantity,
                        type: itemType,
                            serial_numbers: new Set(serialNumbers),
                        notes: notes
                    });
                    }
                }
            });

            // Chuyển từ Map sang Array và chuyển Set serial_numbers về Array
            const materials = Array.from(materialMap.values()).map(material => ({
                ...material,
                serial_numbers: Array.from(material.serial_numbers)
            }));
            
            document.getElementById('materials_json').value = JSON.stringify(materials);
            console.log('Updated materials_json:', materials);
        }
        
        // Hàm load danh sách serial
        async function loadSerials(row) {
            const sourceWarehouseId = document.getElementById('source_warehouse_id').value;
            const materialId = row.querySelector('.material-select').value;
            const itemType = row.querySelector('.item-type-select').value;
            
            if (!materialId || !sourceWarehouseId) {
                return;
            }
            
            console.log('Loading serials...', {
                sourceWarehouseId,
                materialId,
                itemType
            });
            
            try {
                // Truy vấn trực tiếp vào database để lấy serial
                const response = await fetch(`/api/warehouse-transfers/check-serial-data?warehouse_id=${sourceWarehouseId}&material_id=${materialId}`);
                const data = await response.json();
                console.log('Serial Data Check:', data);
                
                if (data.count > 0) {
                    // Lấy serial từ dữ liệu trả về
                    const allSerials = [];
                    data.data.forEach(item => {
                        if (item.serial_numbers_decoded && Array.isArray(item.serial_numbers_decoded)) {
                            // Đảm bảo trim serial ngay sau khi decode
                            allSerials.push(...item.serial_numbers_decoded.map(s => s.trim()));
                        }
                    });
                    
                    // Hiển thị danh sách serial
                    const serialContainer = row.querySelector('.serial-list');
                    const serialInput = row.querySelector('.serial-input');
                    const selectedCountEl = row.querySelector('.selected-count');
                    const nonSerialCountEl = row.querySelector('.non-serial-count');
                    
                    // Hiển thị danh sách serial
                    serialContainer.innerHTML = allSerials.length > 0 
                        ? allSerials.map(serial => `
                            <div class="flex items-center space-x-2 mb-1">
                                <input type="checkbox" class="serial-checkbox" value="${serial}">
                                <span class="text-sm">${serial}</span>
                            </div>
                        `).join('')
                        : '<div class="text-gray-500 text-sm">Không có serial tồn kho</div>';
                        
                    // Cập nhật số lượng không có serial
                    // Lấy số lượng tồn kho từ API checkInventory
                    const inventoryResponse = await fetch(`${window.location.origin}/warehouse-transfers/check-inventory`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            material_id: materialId,
                            warehouse_id: sourceWarehouseId,
                            item_type: itemType
                        })
                    });
                    
                    const inventoryData = await inventoryResponse.json();
                    const totalStock = inventoryData.quantity || 0;
                    nonSerialCountEl.textContent = Math.max(0, totalStock - allSerials.length);
                    
                    // Thêm sự kiện cho các checkbox
                    const checkboxes = serialContainer.querySelectorAll('.serial-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', () => {
                            validateSerialSelection(row);
                            updateMaterialsJson();
                        });
                    });
                    
                    // Validate khi thay đổi số lượng
                    const quantityInput = row.querySelector('.quantity-input');
                    quantityInput.addEventListener('change', () => {
                        validateSerialSelection(row);
                        updateMaterialsJson();
                    });
                    
                    console.log(`Đã tìm thấy ${allSerials.length} serial và hiển thị trên form.`);
                } else {
                    const serialContainer = row.querySelector('.serial-list');
                    serialContainer.innerHTML = '<div class="text-gray-500 text-sm">Không có serial tồn kho</div>';
                }
            } catch (error) {
                console.error('Lỗi khi tải danh sách serial:', error);
                const serialContainer = row.querySelector('.serial-list');
                serialContainer.innerHTML = '<div class="text-red-500 text-sm">Có lỗi xảy ra khi tải danh sách serial</div>';
            }
        }

        // Hàm kiểm tra serial trùng giữa các hàng vật tư
        function checkDuplicateSerials() {
            const rows = document.querySelectorAll('.material-row');
            const allSelectedSerials = {};
            
            // Thu thập tất cả serials đã chọn từ mỗi hàng
            rows.forEach((row, index) => {
                const materialId = row.querySelector('.material-select').value;
                const selectedSerials = Array.from(row.querySelectorAll('.serial-checkbox:checked')).map(cb => cb.value.trim()); // Trim here
                
                if (selectedSerials.length > 0) {
                    selectedSerials.forEach(serial => {
                        if (!allSelectedSerials[serial]) {
                            allSelectedSerials[serial] = [];
                        }
                        allSelectedSerials[serial].push({
                            rowIndex: index,
                            materialId
                        });
                    });
                }
            });
            
            // Kiểm tra và hiển thị cảnh báo cho các serial trùng
            rows.forEach((row, index) => {
                const selectedSerials = Array.from(row.querySelectorAll('.serial-checkbox:checked')).map(cb => cb.value.trim()); // Trim here
                const errorDiv = row.querySelector('.serial-error');
                
                // Reset thông báo lỗi trùng serial
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
                
                // Tìm những serial bị trùng
                const duplicates = selectedSerials.filter(serial => 
                    allSelectedSerials[serial].length > 1 && 
                    allSelectedSerials[serial].some(info => info.rowIndex !== index)
                );
                
                if (duplicates.length > 0) {
                    // Hiển thị cảnh báo
                    let message = 'Serial đã được chọn ở hàng khác: ' + duplicates.join(', ');
                    errorDiv.textContent = message;
                    errorDiv.style.display = 'block';
                }
            });
        }

        // Hàm validate việc chọn serial
        function validateSerialSelection(row) {
            const quantityInput = row.querySelector('.quantity-input');
            const serialContainer = row.querySelector('.serial-list');
            const serialInput = row.querySelector('.serial-input');
            const selectedCountEl = row.querySelector('.selected-count');
            const nonSerialCountEl = row.querySelector('.non-serial-count');
            const errorDiv = row.querySelector('.serial-error');
            const infoDiv = row.querySelector('.serial-info');

            const quantity = parseInt(quantityInput.value) || 0;
            const selectedSerials = Array.from(serialContainer.querySelectorAll('.serial-checkbox:checked')).map(cb => cb.value.trim()); // Trim here
            const nonSerialStock = parseInt(nonSerialCountEl.textContent) || 0;
            
            console.log('Validating serials:', {
                quantity: quantity,
                selectedSerials: selectedSerials,
                nonSerialStock: nonSerialStock
            });

            // Cập nhật số lượng serial đã chọn
            selectedCountEl.textContent = selectedSerials.length;

            // Lưu danh sách serial đã chọn
            serialInput.value = JSON.stringify(selectedSerials);

            // Reset thông báo lỗi số lượng serial
            const quantityErrorDiv = row.querySelector('.quantity-error');
            if (!quantityErrorDiv) {
                const newErrorDiv = document.createElement('div');
                newErrorDiv.className = 'quantity-error text-xs text-red-500 mt-1';
                errorDiv.parentNode.insertBefore(newErrorDiv, errorDiv.nextSibling);
            }
            const quantityError = row.querySelector('.quantity-error');
            quantityError.style.display = 'none';
            
            // Trường hợp 1: Số lượng Serial chọn > số lượng chuyển
            if (selectedSerials.length > quantity) {
                quantityError.textContent = `Số lượng Serial đã chọn (${selectedSerials.length}) vượt quá số lượng cần chuyển (${quantity})`;
                quantityError.style.display = 'block';
                return false;
            }
            
            // Trường hợp 2: Số lượng Serial chọn < số lượng chuyển
            if (selectedSerials.length < quantity) {
                const emptySerialCount = quantity - selectedSerials.length;
                // Thêm thông báo thông tin (không phải lỗi)
                const nonSerialInfoEl = row.querySelector('.non-serial-info');
                if (!nonSerialInfoEl) {
                    const infoElement = document.createElement('div');
                    infoElement.className = 'non-serial-info text-xs text-blue-500 mt-1';
                    infoElement.textContent = `${emptySerialCount} thiết bị sẽ được chuyển với Serial trống`;
                    infoDiv.appendChild(infoElement);
                } else {
                    nonSerialInfoEl.textContent = `${emptySerialCount} thiết bị sẽ được chuyển với Serial trống`;
                }
            } else {
                // Xóa thông báo nếu không còn thiết bị không có Serial
                const nonSerialInfoEl = row.querySelector('.non-serial-info');
                if (nonSerialInfoEl) {
                    nonSerialInfoEl.remove();
                }
            }
            
            // Kiểm tra serial trùng sau khi validate số lượng
            checkDuplicateSerials();
            
            // Cập nhật trong bảng tổng hợp
            updateSummaryTable();
            updateMaterialsJson();
            
            return true;
        }

        // Thêm validate form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            updateMaterialsJson();
            const materialRows = document.querySelectorAll('.material-row');
            let hasError = false;

            // Kiểm tra số lượng tồn kho
            const stockCells = document.querySelectorAll('.stock-quantity');
            stockCells.forEach(cell => {
                if (cell.classList.contains('text-red-600')) {
                    hasError = true;
                    alert('Số lượng chuyển vượt quá tồn kho. Vui lòng kiểm tra lại!');
                e.preventDefault();
                return false;
            }
            });
            
            // Kiểm tra serialsw trùng nhau
            const duplicateErrors = document.querySelectorAll('.serial-error');
            for (let i = 0; i < duplicateErrors.length; i++) {
                if (duplicateErrors[i].style.display === 'block') {
                    hasError = true;
                    alert('Có serial bị trùng lặp giữa các hàng. Vui lòng kiểm tra lại!');
                e.preventDefault();
                return false;
            }
            }

            materialRows.forEach(row => {
                if (!validateSerialSelection(row)) {
                    hasError = true;
                }
            });

            if (hasError) {
                e.preventDefault();
                alert('Vui lòng kiểm tra lại các thông tin serial');
            }
        });

        // Function tạo mã mới
        async function generateNewCode() {
            try {
                console.log('Generating new code...');
                const response = await fetch('/api/warehouse-transfers/generate-code', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                console.log('Response:', data);
                
                if (data.success) {
                    document.getElementById('transfer_code').value = data.code;
                } else {
                    alert('Không thể tạo mã mới: ' + (data.message || 'Lỗi không xác định'));
                }
            } catch (error) {
                console.error('Error generating code:', error);
                alert('Có lỗi xảy ra khi tạo mã mới: ' + error.message);
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Code hiện tại ở đây...
            
            // Không cần thêm nút kiểm tra serial vào trang nữa vì đã tự động hiển thị
        });

        // Hàm kiểm tra dữ liệu serial - Giữ lại để debug nếu cần
        async function checkSerialData() {
            const sourceWarehouseId = document.getElementById('source_warehouse_id').value;
            const materialSelect = document.querySelector('.material-select');
            const materialId = materialSelect ? materialSelect.value : null;
            
            if (!materialId) {
                alert('Vui lòng chọn vật tư trước');
                return;
            }
            
            try {
                const response = await fetch(`/api/warehouse-transfers/check-serial-data?warehouse_id=${sourceWarehouseId}&material_id=${materialId}`);
                const data = await response.json();
                console.log('Serial Data Check:', data);
                
                // Hiển thị kết quả
                let message = `Tìm thấy ${data.count} bản ghi có serial:\n\n`;
                if (data.count > 0) {
                    data.data.forEach(item => {
                        message += `- ID: ${item.id}, Material: ${item.material_id}, Type: ${item.item_type}\n`;
                        message += `  Serial: ${JSON.stringify(item.serial_numbers_decoded)}\n\n`;
                    });
                } else {
                    message += 'Không tìm thấy dữ liệu serial nào trong database.';
                }
                
                alert(message);
            } catch (error) {
                console.error('Lỗi khi kiểm tra dữ liệu serial:', error);
                alert('Có lỗi xảy ra khi kiểm tra dữ liệu serial');
            }
        }

        // Hàm hiển thị serial trực tiếp - Không cần nữa vì đã tích hợp vào loadSerials
    </script>
</body>
</html> 