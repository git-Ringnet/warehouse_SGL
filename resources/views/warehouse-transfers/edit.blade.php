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
    <script src="{{ asset('js/delete-modal.js') }}"></script>
</head>
<body>
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
                                <label for="transfer_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã phiếu chuyển</label>
                                <input type="text" id="transfer_code" name="transfer_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ old('transfer_code', $warehouseTransfer->transfer_code) }}" required>
                            </div>
                            
                            <div>
                                <label for="source_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho nguồn</label>
                                <select id="source_warehouse_id" name="source_warehouse_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn kho nguồn --</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('source_warehouse_id', $warehouseTransfer->source_warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
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
                                        <option value="{{ $warehouse->id }}" {{ old('destination_warehouse_id', $warehouseTransfer->destination_warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="transfer_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày chuyển kho</label>
                                <input type="date" id="transfer_date" name="transfer_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ old('transfer_date', $warehouseTransfer->transfer_date->format('Y-m-d')) }}" required>
                            </div>
                            
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1 required">Nhân viên thực hiện</label>
                                <select id="employee_id" name="employee_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn nhân viên --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id', $warehouseTransfer->employee_id) == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái</label>
                                <select id="status" name="status" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="pending" {{ old('status', $warehouseTransfer->status) == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                                    <option value="in_progress" {{ old('status', $warehouseTransfer->status) == 'in_progress' ? 'selected' : '' }}>Đang chuyển</option>
                                    <option value="completed" {{ old('status', $warehouseTransfer->status) == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                    <option value="canceled" {{ old('status', $warehouseTransfer->status) == 'canceled' ? 'selected' : '' }}>Đã hủy</option>
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
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Tên vật tư/ thành phẩm/ hàng hoá</label>
                                        <select name="materials[0][material_id]" class="material-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                            <option value="">-- Chọn vật tư --</option>
                                            @foreach($materials as $material)
                                                <option value="{{ $material->id }}" data-type="{{ $material->category }}">{{ $material->code }} - {{ $material->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                        <input type="number" name="materials[0][quantity]" class="quantity-input w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập số lượng" value="1" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại vật tư</label>
                                        <div class="flex items-center space-x-4 mt-2">
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="materials[0][type]" value="component" class="form-radio text-blue-500">
                                                <span class="ml-2 text-sm text-gray-700">Linh kiện</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="materials[0][type]" value="product" class="form-radio text-blue-500">
                                                <span class="ml-2 text-sm text-gray-700">Thành phẩm</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="materials[0][type]" value="other" class="form-radio text-blue-500" checked>
                                                <span class="ml-2 text-sm text-gray-700">Khác</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">List số seri</label>
                                    <textarea name="materials[0][serial_numbers]" class="serial-input w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" rows="2" placeholder="Nhập danh sách số seri, mỗi số seri trên một dòng hoặc ngăn cách bằng dấu phẩy"></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Số lượng seri nên trùng khớp với số lượng vật tư chuyển</p>
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
                    <input type="hidden" id="materials_json" name="materials_json" value="{{ old('materials_json') ?: json_encode($selectedMaterials) }}">
                    <input type="hidden" id="quantity" name="quantity" value="{{ old('quantity', $warehouseTransfer->quantity) }}">
                    
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
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số seri</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-sm text-gray-500 text-center">Chưa có vật tư nào được thêm</td>
                                    </tr>
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
                </form>
            </div>
        </main>
    </div>

    <script>
        // Debug giá trị $selectedMaterials
        const selectedMaterialsData = {!! json_encode($selectedMaterials) !!};
        console.log("Selected Materials from PHP:", selectedMaterialsData);

        // Khởi tạo khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM Content Loaded");
            initDeleteModal();
            
            // Thêm sự kiện cho nút "Thêm vật tư"
            document.getElementById('add-material').addEventListener('click', addMaterialRow);
            
            // Khởi tạo các sự kiện cho hàng vật tư đầu tiên
            setupMaterialRowEvents(document.querySelector('.material-row'));
            
            // Quan trọng: Nạp dữ liệu từ selectedMaterials
            // Đảm bảo rằng việc này được thực hiện sau khi các phần tử DOM đã sẵn sàng
            setTimeout(() => {
                loadExistingMaterials();
                updateRemoveButtons();
                updateSummaryTable();
            }, 100);
        });
        
        // Biến đếm số lượng hàng vật tư
        let materialCount = 1;
        
        // Nạp dữ liệu từ selectedMaterials vào các hàng vật tư
        function loadExistingMaterials() {
            const materialsJson = document.getElementById('materials_json').value;
            if (!materialsJson) return;
            
            try {
                console.log("Loading existing materials from:", materialsJson);
                const materials = JSON.parse(materialsJson);
                
                // Nếu không có vật tư, dừng
                if (materials.length === 0) return;
                
                // Tạo lại tất cả các hàng vật tư
                renderMaterialRows(materials);
                
                updateSummaryTable();
            } catch (error) {
                console.error('Lỗi khi nạp dữ liệu vật tư:', error);
            }
        }
        
        // Hàm để tạo lại tất cả các hàng vật tư từ mảng dữ liệu
        function renderMaterialRows(materials) {
            // Lấy container
            const container = document.getElementById('materials-container');
            
            // Xóa tất cả các hàng hiện có, trừ hàng đầu tiên
            while (container.children.length > 1) {
                container.removeChild(container.lastChild);
            }
            
            // Lấy hàng đầu tiên và đặt giá trị
            const firstRow = container.querySelector('.material-row');
            if (materials.length > 0) {
                setMaterialRowValues(firstRow, materials[0]);
            }
            
            // Tạo các hàng còn lại
            for (let i = 1; i < materials.length; i++) {
                const newRow = addMaterialRow();
                setMaterialRowValues(newRow, materials[i]);
            }
            
            // Cập nhật hiển thị nút xóa
            updateRemoveButtons();
        }
        
        // Đặt giá trị cho một hàng vật tư
        function setMaterialRowValues(row, material) {
            console.log("Setting values for material:", material);
            const materialSelect = row.querySelector('.material-select');
            const quantityInput = row.querySelector('.quantity-input');
            const serialInput = row.querySelector('.serial-input');
            const notesInput = row.querySelector('.notes-input');
            
            // Chọn vật tư
            for (let i = 0; i < materialSelect.options.length; i++) {
                if (materialSelect.options[i].value === material.id.toString()) {
                    materialSelect.selectedIndex = i;
                    console.log("Selected material index:", i, "for id:", material.id);
                    break;
                }
            }
            
            // Đặt số lượng
            quantityInput.value = material.quantity || 1;
            
            // Đặt số seri
            serialInput.value = Array.isArray(material.serial_numbers) 
                ? material.serial_numbers.join('\n') 
                : material.serial_numbers || '';
            
            // Đặt ghi chú
            notesInput.value = material.notes || '';
            
            // Đặt loại vật tư
            const typeInputs = row.querySelectorAll('input[type="radio"]');
            typeInputs.forEach(input => {
                if (material.type && input.value === material.type) {
                    input.checked = true;
                    console.log("Setting type radio:", input.value);
                }
            });
            
            // Nếu không có loại nào được chọn, chọn loại mặc định là 'other'
            const checkedType = row.querySelector('input[type="radio"]:checked');
            if (!checkedType) {
                const otherType = row.querySelector('input[type="radio"][value="other"]');
                if (otherType) otherType.checked = true;
            }
        }
        
        // Ngăn chặn việc chọn cùng một kho cho cả nguồn và đích
        document.getElementById('source_warehouse_id').addEventListener('change', function() {
            updateWarehouseOptions();
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
                    input.setAttribute('name', nameAttr.replace(/\[\d+\]/, `[${materialCount}]`));
                    
                    // Reset giá trị
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else if (input.type === 'number' && input.name.includes('quantity')) {
                        input.value = '1';
                    } else if (input.type === 'radio') {
                        if (input.value === 'other') {
                            input.checked = true;
                        } else {
                            input.checked = false;
                        }
                    } else {
                        input.value = '';
                    }
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
            
            console.log("Added new material row", template);
            return template;
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
            
            materialRows.forEach((row, index) => {
                const materialSelect = row.querySelector('.material-select');
                if (materialSelect.value) {
                    const materialId = materialSelect.value;
                    const materialName = materialSelect.options[materialSelect.selectedIndex].text;
                    const quantity = row.querySelector('.quantity-input').value;
                    const serialNumbers = row.querySelector('.serial-input').value;
                    const notes = row.querySelector('.notes-input').value;
                    
                    // Xác định loại vật tư
                    let type = 'other';
                    const typeInputs = row.querySelectorAll('input[type="radio"]');
                    typeInputs.forEach(input => {
                        if (input.checked) {
                            type = input.value;
                        }
                    });
                    
                    materials.push({
                        id: materialId,
                        name: materialName,
                        quantity: quantity,
                        type: type,
                        serial_numbers: serialNumbers,
                        notes: notes
                    });
                }
            });
            
            // Nếu không có vật tư nào được chọn, hiển thị dòng thông báo
            if (materials.length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = '<td colspan="5" class="px-4 py-4 text-sm text-gray-500 text-center">Chưa có vật tư nào được thêm</td>';
                tbody.appendChild(emptyRow);
                return;
            }
            
            // Tạo các hàng mới cho bảng tổng hợp
            materials.forEach((material, index) => {
                const row = document.createElement('tr');
                
                // Xác định loại vật tư để hiển thị
                let typeDisplay = 'Khác';
                let typeClass = 'bg-gray-100 text-gray-800';
                
                if (material.type === 'component') {
                    typeDisplay = 'Linh kiện';
                    typeClass = 'bg-blue-100 text-blue-800';
                } else if (material.type === 'product') {
                    typeDisplay = 'Thành phẩm';
                    typeClass = 'bg-green-100 text-green-800';
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
                
                row.innerHTML = `
                    <td class="px-4 py-2 text-sm text-gray-900">${index + 1}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${material.name}</td>
                    <td class="px-4 py-2 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs font-medium ${typeClass}">${typeDisplay}</span>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900">${material.quantity}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${serialDisplay}</td>
                `;
                
                tbody.appendChild(row);
            });
        }
        
        // Cập nhật dữ liệu JSON để submit
        function updateMaterialsJson() {
            const materials = [];
            const materialRows = document.querySelectorAll('.material-row');
            
            materialRows.forEach(row => {
                const materialSelect = row.querySelector('.material-select');
                if (materialSelect.value) {
                    const materialId = materialSelect.value;
                    const materialName = materialSelect.options[materialSelect.selectedIndex].text;
                    const quantity = row.querySelector('.quantity-input').value;
                    const serialNumbers = row.querySelector('.serial-input').value;
                    const notes = row.querySelector('.notes-input').value;
                    
                    // Xác định loại vật tư
                    let type = 'other';
                    const typeInputs = row.querySelectorAll('input[type="radio"]');
                    typeInputs.forEach(input => {
                        if (input.checked) {
                            type = input.value;
                        }
                    });
                    
                    materials.push({
                        id: materialId,
                        name: materialName,
                        quantity: quantity,
                        type: type,
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
                alert('Vui lòng thêm ít nhất một vật tư vào danh sách trước khi cập nhật phiếu chuyển kho.');
                return false;
            }
            
            // Cập nhật trường quantity (sử dụng số lượng của vật tư đầu tiên)
            if (materials.length > 0) {
                document.getElementById('quantity').value = materials[0].quantity || 1;
            }
        });
    </script>
</body>
</html> 