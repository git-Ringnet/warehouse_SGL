<!DOCTYPE html>
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
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Danh sách vật tư chuyển kho (chỉ xem)</h3>
                        
                        <div id="materials-container" class="read-only-materials">
                            @foreach($selectedMaterials as $index => $item)
                            <!-- Mẫu một hàng vật tư -->
                            <div class="material-row border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại sản phẩm</label>
                                        <div class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 flex items-center">
                                            @if($item['type'] == 'material')
                                                Vật tư
                                            @elseif($item['type'] == 'product')
                                                Thành phẩm
                                            @elseif($item['type'] == 'good')
                                                Hàng hóa
                                            @else
                                                Khác
                                            @endif
                                        </div>
                                        <input type="hidden" name="materials[{{ $index }}][item_type]" value="{{ $item['type'] }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên vật tư/ thành phẩm/ hàng hoá</label>
                                        <div class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 flex items-center">
                                            {{ $item['name'] }}
                                        </div>
                                        <input type="hidden" name="materials[{{ $index }}][material_id]" value="{{ $item['id'] }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                        <div class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 flex items-center">
                                            {{ $item['quantity'] }}
                                        </div>
                                        <input type="hidden" name="materials[{{ $index }}][quantity]" value="{{ $item['quantity'] }}">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">List số seri (nếu có)</label>
                                    <div class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 min-h-[60px]">
                                        {{ is_array($item['serial_numbers']) && count($item['serial_numbers']) > 0 ? 
                                            (count($item['serial_numbers']) > 3 ? 
                                                implode(', ', array_slice($item['serial_numbers'], 0, 3)) . '... (' . count($item['serial_numbers']) . ' số seri)' : 
                                                implode(', ', $item['serial_numbers'])
                                            ) : 'Không có' 
                                        }}
                                    </div>
                                    <input type="hidden" name="materials[{{ $index }}][serial_numbers]" value="{{ is_array($item['serial_numbers']) ? implode("\n", $item['serial_numbers']) : '' }}">
                                </div>
                                <div class="mt-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                    <div class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 min-h-[60px]">
                                        {{ $item['notes'] ?: 'Không có' }}
                                    </div>
                                    <input type="hidden" name="materials[{{ $index }}][notes]" value="{{ $item['notes'] }}">
                                </div>
                            </div>
                            @endforeach
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
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kho nguồn</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tồn kho</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số seri</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($selectedMaterials as $index => $item)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $item['name'] }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                                @if($item['type'] == 'material') bg-blue-100 text-blue-800
                                                @elseif($item['type'] == 'product') bg-green-100 text-green-800
                                                @elseif($item['type'] == 'good') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                @if($item['type'] == 'material')
                                                    Vật tư
                                                @elseif($item['type'] == 'product')
                                                    Thành phẩm
                                                @elseif($item['type'] == 'good')
                                                    Hàng hóa
                                                @else
                                                    Khác
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $warehouseTransfer->source_warehouse->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">-</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $item['quantity'] }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ is_array($item['serial_numbers']) && count($item['serial_numbers']) > 0 ? 
                                                (count($item['serial_numbers']) > 3 ? 
                                                    implode(', ', array_slice($item['serial_numbers'], 0, 3)) . '... (' . count($item['serial_numbers']) . ' số seri)' : 
                                                    implode(', ', $item['serial_numbers'])
                                                ) : 'Không có' 
                                            }}
                                        </td>
                                    </tr>
                                    @endforeach
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

        // Biến đếm số lượng hàng vật tư
        let materialCount = {{ count($selectedMaterials) }};
        
        // Khởi tạo khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM Content Loaded");
            initDeleteModal();
            
            // Ngăn chặn việc chọn cùng một kho cho cả nguồn và đích
            document.getElementById('source_warehouse_id').addEventListener('change', function() {
                updateWarehouseOptions();
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
            // Kiểm tra xem có ít nhất một vật tư nào được chọn không
            if (materialCount === 0) {
                e.preventDefault();
                alert('Không có vật tư nào trong phiếu chuyển kho.');
                return false;
            }
        });
    </script>
</body>
</html> 