<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu nhập kho - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu nhập kho #{{ $inventoryImport->import_code }}</h1>
            <a href="{{ route('inventory-imports.show', $inventoryImport->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
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
                <form action="{{ route('inventory-imports.update', $inventoryImport->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Thông tin phiếu nhập kho</h2>
                    
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
                                <label for="import_code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã phiếu nhập</label>
                                <input type="text" id="import_code" name="import_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã phiếu nhập" required value="{{ old('import_code', $inventoryImport->import_code) }}">
                            </div>
                            
                            <div>
                                <label for="import_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày nhập kho</label>
                                <input type="date" id="import_date" name="import_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required value="{{ old('import_date', $inventoryImport->import_date->format('Y-m-d')) }}">
                            </div>
                            
                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1 required">Nhà cung cấp</label>
                                <select id="supplier_id" name="supplier_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $inventoryImport->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="space-y-4">
                            <div>
                                <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho nhập</label>
                                <select id="warehouse_id" name="warehouse_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn kho --</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $inventoryImport->warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="order_code" class="block text-sm font-medium text-gray-700 mb-1">Mã đơn hàng</label>
                                <input type="text" id="order_code" name="order_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã đơn hàng liên quan (nếu có)" value="{{ old('order_code', $inventoryImport->order_code) }}">
                            </div>
                            
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú về phiếu nhập kho (nếu có)">{{ old('notes', $inventoryImport->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phần vật tư -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Danh sách vật tư nhập kho</h3>
                        
                        <div id="materials-container">
                            @forelse($inventoryImport->materials as $key => $item)
                            <div class="material-row border border-gray-200 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Vật tư</label>
                                        <select name="materials[{{ $key }}][material_id]" class="material-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                            <option value="">-- Chọn vật tư --</option>
                                            @foreach($materials as $material)
                                                <option value="{{ $material->id }}" {{ $item->material_id == $material->id ? 'selected' : '' }}>{{ $material->code }} - {{ $material->name }} ({{ $material->unit }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                        <input type="number" name="materials[{{ $key }}][quantity]" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập số lượng" value="{{ $item->quantity }}" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Serial</label>
                                        <input type="text" name="materials[{{ $key }}][serial]" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập số serial (nếu có)" value="{{ $item->serial }}">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                    <textarea name="materials[{{ $key }}][notes]" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú cho vật tư này (nếu có)">{{ $item->notes }}</textarea>
                                </div>
                                <div class="mt-2 flex justify-end">
                                    <button type="button" class="remove-material text-red-500 hover:text-red-700" onclick="removeMaterial(this)">
                                        <i class="fas fa-trash mr-1"></i> Xóa
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="material-row border border-gray-200 rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Vật tư</label>
                                        <select name="materials[0][material_id]" class="material-select w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                            <option value="">-- Chọn vật tư --</option>
                                            @foreach($materials as $material)
                                                <option value="{{ $material->id }}">{{ $material->code }} - {{ $material->name }} ({{ $material->unit }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Số lượng</label>
                                        <input type="number" name="materials[0][quantity]" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập số lượng" value="1" min="1" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Serial</label>
                                        <input type="text" name="materials[0][serial]" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập số serial (nếu có)">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                    <textarea name="materials[0][notes]" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú cho vật tư này (nếu có)"></textarea>
                                </div>
                                <div class="mt-2 flex justify-end">
                                    <button type="button" class="remove-material text-red-500 hover:text-red-700" onclick="removeMaterial(this)" style="display: none;">
                                        <i class="fas fa-trash mr-1"></i> Xóa
                                    </button>
                                </div>
                            </div>
                            @endforelse
                        </div>
                        
                        <div class="mt-2">
                            <button type="button" id="add-material" class="flex items-center text-blue-500 hover:text-blue-700">
                                <i class="fas fa-plus-circle mr-1"></i> Thêm vật tư
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('inventory-imports.show', $inventoryImport->id) }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
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
        document.addEventListener('DOMContentLoaded', function() {
            // Thêm xử lý cho nút "Thêm vật tư"
            document.getElementById('add-material').addEventListener('click', addMaterial);
            
            // Hiển thị nút xóa nếu có nhiều hơn 1 hàng
            updateRemoveButtons();
        });
        
        // Biến đếm số lượng hàng vật tư
        let materialCount = document.querySelectorAll('.material-row').length;
        
        // Hàm thêm hàng vật tư mới
        function addMaterial() {
            const container = document.getElementById('materials-container');
            const template = container.querySelector('.material-row').cloneNode(true);
            
            // Cập nhật các attributes
            const inputs = template.querySelectorAll('select, input, textarea');
            inputs.forEach(input => {
                const nameAttr = input.getAttribute('name');
                if (nameAttr) {
                    input.setAttribute('name', nameAttr.replace(/materials\[\d+\]/, `materials[${materialCount}]`));
                    
                    // Reset giá trị
                    if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else if (input.type === 'number' && input.name.includes('quantity')) {
                        input.value = '1';
                    } else {
                        input.value = '';
                    }
                }
            });
            
            // Hiển thị nút xóa
            const removeButton = template.querySelector('.remove-material');
            removeButton.style.display = 'inline-flex';
            
            // Thêm hàng mới vào container
            container.appendChild(template);
            
            materialCount++;
            
            // Cập nhật hiển thị của các nút xóa
            updateRemoveButtons();
        }
        
        // Hàm xóa hàng vật tư
        function removeMaterial(button) {
            const materialRow = button.closest('.material-row');
            materialRow.remove();
            
            // Cập nhật hiển thị của các nút xóa
            updateRemoveButtons();
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
    </script>
</body>
</html> 