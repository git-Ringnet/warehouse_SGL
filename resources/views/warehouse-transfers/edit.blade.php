                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <s>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     </s><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chỉnh sửa phiếu chuyển kho - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <style>
        .required::after {
            content: " *";
            color: #ef4444;
        }
    </style>
</head>
<body>
    @if($warehouseTransfer->status !== 'pending')
    <script>
        window.location.href = "{{ route('warehouse-transfers.show', $warehouseTransfer->id) }}";
    </script>
    @endif

    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu chuyển kho</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Mã phiếu: {{ $warehouseTransfer->transfer_code }}
                </div>
            </div>
            <a href="{{ route('warehouse-transfers.show', $warehouseTransfer->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
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
                <form action="{{ route('warehouse-transfers.update', $warehouseTransfer->id) }}" method="POST">
                    @csrf
                    @method('PUT')
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
                                <label for="transfer_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu chuyển</label>
                                <input type="text" id="transfer_code" name="transfer_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100" value="{{ old('transfer_code', $warehouseTransfer->transfer_code) }}" readonly>
                            </div>
                            
                            <div>
                                <label for="source_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho nguồn</label>
                                <select id="source_warehouse_id" name="source_warehouse_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn kho nguồn --</option>
                                    @foreach($warehouses as $warehouse)
                                        @if(!$warehouse->is_hidden && !$warehouse->deleted_at)
                                            <option value="{{ $warehouse->id }}" {{ old('source_warehouse_id', $warehouseTransfer->source_warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                        @endif
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
                                        @if(!$warehouse->is_hidden && !$warehouse->deleted_at)
                                            <option value="{{ $warehouse->id }}" {{ old('destination_warehouse_id', $warehouseTransfer->destination_warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="transfer_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày chuyển kho</label>
                                <input type="date" id="transfer_date" name="transfer_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ old('transfer_date', $warehouseTransfer->transfer_date->format('Y-m-d')) }}" required>
                            </div>
                            
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Nhân viên thực hiện</label>
                                <select id="employee_id" name="employee_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">-- Chọn nhân viên --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id', $warehouseTransfer->employee_id) == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phần chọn vật tư -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Danh sách vật tư chuyển kho</h3>
                        <!-- Thay thế toàn bộ phần render vật tư và JS vật tư bằng logic từ create.blade.php -->

                        <!-- PHẦN HTML: render vật tư -->
                        <div id="materials-container">
                            <!-- Các dòng vật tư sẽ được render động bằng JS, giống create.blade.php -->
                        </div>
                        <button type="button" id="add-material" class="mt-4 px-4 py-2 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                            <i class="fas fa-plus mr-1"></i> Thêm vật tư
                        </button>
                        <input type="hidden" id="materials_json" name="materials_json" value="{{ old('materials_json') ?: json_encode($selectedMaterials) }}">
                    </div>
                    
                    <!-- Bảng tổng hợp các vật tư đã thêm (giống create) -->
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
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="summary-table-body" class="divide-y divide-gray-200">
                                    <!-- Render động bằng JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Ghi chú -->
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">{{ old('notes', $warehouseTransfer->notes) }}</textarea>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('warehouse-transfers.show', $warehouseTransfer->id) }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>

                    <!-- PHẦN SCRIPT: logic JS vật tư/serial động -->
                    <script>
                        // --- ĐỒNG BỘ LOGIC TỪ CREATE.BLADE.PHP ---
                        let itemsData = {
                            material: [],
                            product: [],
                            good: []
                        };
                        let selectedMaterials = {!! json_encode($selectedMaterials) !!};

                        // Hàm load items theo kho nguồn
                        async function loadItemsByWarehouse(warehouseId) {
                            if (!warehouseId) {
                                itemsData = { material: [], product: [], good: [] };
                                return;
                            }
                            
                            try {
                                const response = await fetch(`/api/warehouse-transfers/get-items-by-warehouse?warehouse_id=${warehouseId}`);
                                const data = await response.json();
                                
                                if (response.ok) {
                                    itemsData = {
                                        material: data.materials || [],
                                        product: data.products || [],
                                        good: data.goods || []
                                    };
                                    console.log('Loaded items for warehouse:', warehouseId, itemsData);
                                } else {
                                    console.error('Error loading items:', data.error);
                                }
                            } catch (error) {
                                console.error('Error loading items:', error);
                            }
                        }

                        // Hàm render lại toàn bộ vật tư đã chọn (bao gồm cả serial động)
                        async function renderMaterials() {
                            const container = document.getElementById('materials-container');
                            container.innerHTML = '';
                            
                            // Đợi items được load nếu chưa có
                            const sourceWarehouseId = document.getElementById('source_warehouse_id').value;
                            if (sourceWarehouseId && (itemsData.material.length === 0 && itemsData.product.length === 0 && itemsData.good.length === 0)) {
                                await loadItemsByWarehouse(sourceWarehouseId);
                            }
                            
                            // Luôn render tất cả selectedMaterials (kể cả item chưa đủ id/type)
                            if (selectedMaterials.length === 0) {
                                container.innerHTML = '<div class="text-gray-500 text-sm">Chưa có vật tư nào được chọn</div>';
                                updateMaterialsJson();
                                updateSummaryTable();
                                return;
                            }
                            selectedMaterials.forEach((item, idx) => {
                                // Tạo node row giống create.blade.php
                                const row = document.createElement('div');
                                row.className = 'material-row border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50';
                                row.innerHTML = `
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Loại sản phẩm</label>
                                            <select class="item-type-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 bg-white" data-idx="${idx}">
                                                <option value="">-- Chọn loại --</option>
                                                <option value="material" ${item.type === 'material' ? 'selected' : ''}>Vật tư</option>
                                                <option value="product" ${item.type === 'product' ? 'selected' : ''}>Thành phẩm</option>
                                                <option value="good" ${item.type === 'good' ? 'selected' : ''}>Hàng hóa</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư/ thành phẩm/ hàng hoá</label>
                                            <select class="material-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 bg-white" data-idx="${idx}">
                                                <option value="">-- Chọn --</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                            <input type="number" min="1" class="quantity-input w-full h-10 border border-gray-300 rounded-lg px-3 py-2 bg-white" value="${item.quantity || 1}">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">List số seri</label>
                                        <div class="serial-container">
                                            <div class="serial-list mb-2 max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-3"></div>
                                            <input type="hidden" class="serial-input">
                                            <div class="serial-info text-xs text-gray-500 mt-1">
                                                <div>Số serial đã chọn: <span class="selected-count">0</span></div>
                                                <div>Số lượng không có serial: <span class="non-serial-count">0</span></div>
                                            </div>
                                            <div class="serial-error text-xs text-red-500 mt-1" style="display: none;"></div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                        <textarea class="notes-input w-full border border-gray-300 rounded-lg px-3 py-2 bg-white" rows="2">${item.notes || ''}</textarea>
                                    </div>
                                    <button type="button" class="remove-material mt-2 px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">Xóa</button>
                                `;
                                // Gán sự kiện xóa (chỉ cho xóa nếu còn >1 item)
                                row.querySelector('.remove-material').onclick = () => {
                                    if (selectedMaterials.length <= 1) {
                                        alert('Phải có ít nhất 1 vật tư trong danh sách!');
                                        return;
                                    }
                                    selectedMaterials.splice(idx, 1);
                                    renderMaterials();
                                };
                                // Gán sự kiện thay đổi loại sản phẩm
                                row.querySelector('.item-type-select').onchange = function() {
                                    const type = this.value;
                                    const materialSelect = row.querySelector('.material-select');
                                    materialSelect.innerHTML = '<option value="">-- Chọn --</option>';
                                    (itemsData[type] || []).forEach(itemOpt => {
                                        const opt = document.createElement('option');
                                        opt.value = itemOpt.id;
                                        opt.text = itemOpt.name;
                                        materialSelect.appendChild(opt);
                                    });
                                    // Reset vật tư và serial khi đổi loại
                                    materialSelect.value = '';
                                    selectedMaterials[idx].id = '';
                                    selectedMaterials[idx].name = '';
                                    selectedMaterials[idx].type = type; // Cập nhật type
                                    selectedMaterials[idx].serial_numbers = []; // Reset serial numbers
                                    // Clear serial list
                                    const serialContainer = row.querySelector('.serial-list');
                                    const selectedCountEl = row.querySelector('.selected-count');
                                    const nonSerialCountEl = row.querySelector('.non-serial-count');
                                    serialContainer.innerHTML = '<div class="text-gray-500 text-sm">Chọn vật tư để xem serial</div>';
                                    selectedCountEl.textContent = '0';
                                    nonSerialCountEl.textContent = '0';
                                    updateMaterialsJson();
                                    updateSummaryTable();
                                };
                                // Gán sự kiện thay đổi vật tư
                                row.querySelector('.material-select').onchange = function() {
                                    selectedMaterials[idx].id = this.value;
                                    selectedMaterials[idx].name = this.options[this.selectedIndex].text;
                                    // Reset serial numbers khi thay đổi item
                                    selectedMaterials[idx].serial_numbers = [];
                                    // Load serial mới cho item mới
                                    loadSerials(row, idx, []);
                                    updateMaterialsJson();
                                    updateSummaryTable();
                                };
                                // Gán sự kiện thay đổi số lượng, serial, notes
                                row.querySelector('.quantity-input').oninput = function() {
                                    selectedMaterials[idx].quantity = this.value;
                                    updateMaterialsJson();
                                    updateSummaryTable();
                                };
                                row.querySelector('.notes-input').oninput = function() {
                                    selectedMaterials[idx].notes = this.value;
                                    updateMaterialsJson();
                                    updateSummaryTable();
                                };
                                // Khởi tạo options vật tư đúng loại
                                const typeSelect = row.querySelector('.item-type-select');
                                const materialSelect = row.querySelector('.material-select');
                                // Fill lại options cho select vật tư dựa trên loại đã chọn
                                materialSelect.innerHTML = '<option value="">-- Chọn --</option>';
                                if (item.type && itemsData[item.type]) {
                                    itemsData[item.type].forEach(itemOpt => {
                                        const opt = document.createElement('option');
                                        opt.value = itemOpt.id;
                                        opt.text = itemOpt.name;
                                        materialSelect.appendChild(opt);
                                    });
                                }
                                // Set lại value nếu đã có id vật tư
                                if (item.id) {
                                    materialSelect.value = item.id;
                                }
                                // Nếu có serial thì render lại serial
                                if (item.id && item.type) {
                                    loadSerials(row, idx, item.serial_numbers || []);
                                } else {
                                    // Hiển thị thông báo cho item chưa chọn
                                    const serialContainer = row.querySelector('.serial-list');
                                    const selectedCountEl = row.querySelector('.selected-count');
                                    const nonSerialCountEl = row.querySelector('.non-serial-count');
                                    serialContainer.innerHTML = '<div class="text-gray-500 text-sm">Chọn vật tư để xem serial</div>';
                                    selectedCountEl.textContent = '0';
                                    nonSerialCountEl.textContent = '0';
                                }
                                container.appendChild(row);
                            });
                            updateMaterialsJson();
                            updateSummaryTable();
                        }
                        // Hàm thêm vật tư mới
                        async function addMaterialRow() {
                            selectedMaterials.push({
                                id: '',
                                name: '',
                                type: '', // Sẽ được cập nhật khi chọn loại
                                quantity: 1,
                                serial_numbers: [],
                                notes: ''
                            });
                            await renderMaterials();
                            console.log('Added new material row, total items:', selectedMaterials.length); // Debug log
                        }
                        // Hàm load serial động (giống create)
                        async function loadSerials(row, idx, preselectedSerials = []) {
                            const sourceWarehouseId = document.getElementById('source_warehouse_id').value;
                            const materialId = row.querySelector('.material-select').value;
                            const itemType = row.querySelector('.item-type-select').value;
                            const selectedCountEl = row.querySelector('.selected-count');
                            const nonSerialCountEl = row.querySelector('.non-serial-count');
                            if (!materialId || !sourceWarehouseId || !itemType) {
                                console.warn('Thiếu param khi gọi loadSerials', { materialId, sourceWarehouseId, itemType });
                                return;
                            }
                            try {
                                const response = await fetch(`/api/warehouse-transfers/get-serials?warehouse_id=${sourceWarehouseId}&material_id=${materialId}&item_type=${itemType}`);
                                if (!response.ok) {
                                    // Chỉ log lỗi, không hiển thị lỗi đỏ cho user
                                    console.error('Lỗi response khi fetch serial:', response.status, response.statusText);
                                    return;
                                }
                                const data = await response.json();
                                let allSerials = [];
                                if (data.success && data.data && data.data.available_serials) {
                                    allSerials = data.data.available_serials;
                                }
                                // Merge thêm các serial đã chọn nhưng không còn trong kho
                                const mergedSerials = [...allSerials];
                                (preselectedSerials || []).forEach(serial => {
                                    if (!mergedSerials.includes(serial)) {
                                        mergedSerials.push(serial);
                                    }
                                });
                                const serialContainer = row.querySelector('.serial-list');
                                serialContainer.innerHTML = mergedSerials.length > 0
                                    ? mergedSerials.map(serial => {
                                        const checked = preselectedSerials.includes(serial) ? 'checked' : '';
                                        // Nếu serial không còn trong kho, disable và tooltip
                                        const disabled = !allSerials.includes(serial) ? 'disabled title="Serial này đã được chọn cho phiếu này, hiện không còn trong kho"' : '';
                                        const isTransferred = !allSerials.includes(serial);
                                        return `
                                            <div class="flex items-center space-x-2 mb-1">
                                                <input type="checkbox" class="serial-checkbox" value="${serial}" ${checked} ${disabled}>
                                                <span class="text-sm">${serial}${isTransferred ? ' <span style="color:red">(đã chuyển)</span>' : ''}</span>
                                                ${isTransferred ? '<button type="button" class="remove-transferred-serial ml-2 px-1 py-0.5 bg-red-100 text-red-600 text-xs rounded hover:bg-red-200" data-serial="' + serial + '">Xóa</button>' : ''}
                                            </div>
                                        `;
                                    }).join('')
                                    : '<div class="text-gray-500 text-sm">Không có serial tồn kho</div>';
                                // Cập nhật số lượng không có serial
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
                                const nonSerialCountEl = row.querySelector('.non-serial-count');
                                nonSerialCountEl.textContent = Math.max(0, totalStock - allSerials.length);
                                // Sự kiện cho checkbox serial
                                const checkboxes = serialContainer.querySelectorAll('.serial-checkbox');
                                checkboxes.forEach(checkbox => {
                                    checkbox.addEventListener('change', () => {
                                        // Lưu serial đã chọn vào selectedMaterials
                                        const checkedSerials = Array.from(serialContainer.querySelectorAll('.serial-checkbox:checked')).map(cb => cb.value.trim());
                                        selectedMaterials[idx].serial_numbers = checkedSerials;
                                        // Sử dụng validateSerialSelection thay vì logic cũ
                                        validateSerialSelection(row);
                                    });
                                });
                                // Thêm event listener cho nút xóa serial đã chuyển:
                                const removeButtons = serialContainer.querySelectorAll('.remove-transferred-serial');
                                removeButtons.forEach(button => {
                                    button.addEventListener('click', () => {
                                        const serialToRemove = button.getAttribute('data-serial');
                                        // Xóa serial khỏi selectedMaterials
                                        if (selectedMaterials[idx].serial_numbers) {
                                            selectedMaterials[idx].serial_numbers = selectedMaterials[idx].serial_numbers.filter(s => s !== serialToRemove);
                                        }
                                        // Reload serial list để cập nhật UI
                                        loadSerials(row, idx, selectedMaterials[idx].serial_numbers || []);
                                        updateMaterialsJson();
                                        updateSummaryTable();
                                    });
                                });
                                // Nếu có serial đã chọn trước thì cập nhật lại
                                if (preselectedSerials.length > 0 && selectedCountEl) {
                                    selectedCountEl.textContent = preselectedSerials.length;
                                }
                                // Validate serial khi thay đổi số lượng
                                row.querySelector('.quantity-input').addEventListener('change', () => {
                                    validateSerialSelection(row);
                                });
                            } catch (error) {
                                // Chỉ log lỗi, không hiển thị lỗi đỏ cho user
                                console.error('Lỗi khi fetch serial:', error);
                            }
                        }
                        
                        // Hàm kiểm tra serial trùng giữa các hàng vật tư
                        function checkDuplicateSerials() {
                            const rows = document.querySelectorAll('.material-row');
                            const allSelectedSerials = {};
                            
                            // Thu thập tất cả serials đã chọn từ mỗi hàng
                            rows.forEach((row, index) => {
                                const materialId = row.querySelector('.material-select').value;
                                const selectedSerials = Array.from(row.querySelectorAll('.serial-checkbox:checked')).map(cb => cb.value.trim());
                                
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
                                const selectedSerials = Array.from(row.querySelectorAll('.serial-checkbox:checked')).map(cb => cb.value.trim());
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
                            const selectedSerials = Array.from(serialContainer.querySelectorAll('.serial-checkbox:checked')).map(cb => cb.value.trim());
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
                        
                        // Hàm cập nhật JSON vật tư
                        function updateMaterialsJson() {
                            document.getElementById('materials_json').value = JSON.stringify(selectedMaterials);
                        }
                        // Hàm cập nhật bảng tổng hợp (giữ nguyên như cũ)
                        function updateSummaryTable() {
                            const tbody = document.getElementById('summary-table-body');
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
                            
                            selectedMaterials.forEach(item => {
                                console.log('Processing item:', item); // Debug log
                                if (item.type) { // Chỉ cần có type là hiển thị
                                    const key = item.id ? `${item.id}-${item.type}` : `temp-${item.type}-${Math.random()}`;
                                    
                                    if (!groupedMaterials.has(key)) {
                                        groupedMaterials.set(key, {
                                            id: item.id || '',
                                            name: item.name || 'Chưa chọn vật tư',
                                            type: item.type,
                                            quantity: 0,
                                            serialNumbers: new Set(),
                                            notes: new Set(),
                                            warehouse_name: sourceWarehouseName,
                                            stock_quantity: '...'
                                        });
                                    }
                                    
                                    const group = groupedMaterials.get(key);
                                    group.quantity += parseInt(item.quantity) || 0;
                                    
                                    if (Array.isArray(item.serial_numbers)) {
                                        item.serial_numbers.forEach(serial => group.serialNumbers.add(serial));
                                    }
                                    
                                    if (item.notes) group.notes.add(item.notes);
                                    
                                    // Kiểm tra tồn kho nếu đã chọn kho nguồn và có id
                                    if (sourceWarehouseId && item.id) {
                                        checkInventory(item.id, sourceWarehouseId, item.type);
                                    }
                                }
                            });
                            
                            // Nếu không có vật tư nào được chọn, hiển thị dòng thông báo
                            if (groupedMaterials.size === 0) {
                                const emptyRow = document.createElement('tr');
                                emptyRow.innerHTML = '<td colspan="8" class="px-4 py-4 text-sm text-gray-500 text-center">Chưa có vật tư nào được thêm</td>';
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
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <button type="button" class="remove-material px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200" data-idx="${index}">Xóa</button>
                                    </td>
                                `;
                                
                                row.querySelector('.remove-material').onclick = () => {
                                    if (selectedMaterials.length <= 1) {
                                        alert('Phải có ít nhất 1 vật tư trong danh sách!');
                                        return;
                                    }
                                    // Xóa tất cả các item có cùng key
                                    const key = `${material.id}-${material.type}`;
                                    selectedMaterials = selectedMaterials.filter(item => `${item.id}-${item.type}` !== key);
                                    renderMaterials();
                                };
                                
                                tbody.appendChild(row);
                            });
                        }
                        
                        // Hàm kiểm tra tồn kho
                        function checkInventory(materialId, warehouseId, itemType) {
                            if (!materialId || !warehouseId) return;
                            
                            fetch(`${window.location.origin}/warehouse-transfers/check-inventory`, {
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
                            .then(response => response.json())
                            .then(data => {
                                // Cập nhật hiển thị tồn kho trong bảng
                                const stockCells = document.querySelectorAll(`.stock-quantity[data-id="${materialId}"][data-type="${itemType}"]`);
                                
                                stockCells.forEach(cell => {
                                    cell.textContent = data.quantity ?? 0;
                                    
                                    // Hiển thị cảnh báo nếu tồn kho không đủ
                                    const row = cell.closest('tr');
                                    const quantityCell = row.querySelector('td:nth-child(6)');
                                    const quantity = parseInt(quantityCell.textContent);
                                    
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
                                const stockCells = document.querySelectorAll(`.stock-quantity[data-id="${materialId}"][data-type="${itemType}"]`);
                                stockCells.forEach(cell => {
                                    cell.textContent = 'Lỗi';
                                });
                            });
                        }
                        document.getElementById('add-material').onclick = addMaterialRow;
                        document.addEventListener('DOMContentLoaded', async function() {
                            await renderMaterials();
                        });
                    </script>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Biến đếm số lượng hàng vật tư
        let materialCount = {{ count($selectedMaterials) }};
        
        // Khởi tạo khi trang được tải
        document.addEventListener('DOMContentLoaded', async function() {
            console.log("DOM Content Loaded");
            initDeleteModal();
            
            // Load items theo kho nguồn ban đầu
            const sourceWarehouseId = document.getElementById('source_warehouse_id').value;
            if (sourceWarehouseId) {
                await loadItemsByWarehouse(sourceWarehouseId);
            }
            
            // Ngăn chặn việc chọn cùng một kho cho cả nguồn và đích
            document.getElementById('source_warehouse_id').addEventListener('change', async function() {
                updateWarehouseOptions();
                // Load items theo kho nguồn mới
                await loadItemsByWarehouse(this.value);
                // Reset tất cả items đã chọn (để trống hoàn toàn)
                selectedMaterials = [];
                await renderMaterials(); // Không render vật tư nào cho đến khi user chọn lại
            });
            
            document.getElementById('destination_warehouse_id').addEventListener('change', function() {
                updateWarehouseOptions();
            });
        });
        
        // Ngăn chặn việc chọn cùng một kho cho cả nguồn và đích
        function updateWarehouseOptions() {
            const sourceWarehouseId = document.getElementById('source_warehouse_id').value;
            const destinationWarehouseId = document.getElementById('destination_warehouse_id').value;
            
            // Nếu cả hai cùng chọn một kho
            if (sourceWarehouseId && destinationWarehouseId && sourceWarehouseId === destinationWarehouseId) {
                alert('Kho nguồn và kho đích không được trùng nhau');
                document.getElementById('destination_warehouse_id').value = '';
            }
        }
        
        // Kiểm tra trước khi submit form
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
            
            // Kiểm tra serials trùng nhau
            const duplicateErrors = document.querySelectorAll('.serial-error');
            for (let i = 0; i < duplicateErrors.length; i++) {
                if (duplicateErrors[i].style.display === 'block') {
                    hasError = true;
                    alert('Có serial bị trùng lặp giữa các hàng. Vui lòng kiểm tra lại!');
                    e.preventDefault();
                    return false;
                }
            }

            // Validate tất cả các row
            materialRows.forEach(row => {
                if (!validateSerialSelection(row)) {
                    hasError = true;
                }
            });

            // Kiểm tra xem có ít nhất một vật tư nào được chọn không
            if (materialCount === 0) {
                e.preventDefault();
                alert('Không có vật tư nào trong phiếu chuyển kho.');
                return false;
            }

            if (hasError) {
                e.preventDefault();
                alert('Vui lòng kiểm tra lại các thông tin serial');
            }
        });
    </script>
</body>
</html> 