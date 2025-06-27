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
                                <label for="order_code" class="block text-sm font-medium text-gray-700 mb-1">Mã đơn hàng</label>
                                <input type="text" id="order_code" name="order_code" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mã đơn hàng liên quan (nếu có)" value="{{ old('order_code', $inventoryImport->order_code) }}">
                            </div>
                            
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú về phiếu nhập kho (nếu có)">{{ old('notes', $inventoryImport->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phần vật tư - Chỉ hiển thị, không cho phép chỉnh sửa -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Danh sách vật tư nhập kho</h3>
                        
                        <div id="materials-container">
                            @forelse($inventoryImport->materials as $key => $item)
                            <div class="material-row border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại sản phẩm</label>
                                        <div class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 bg-gray-100 flex items-center">
                                            @if(($item->item_type ?? 'material') == 'material')
                                                Vật tư
                                            @elseif(($item->item_type ?? '') == 'product')
                                                Thành phẩm
                                            @elseif(($item->item_type ?? '') == 'good')
                                                Hàng hóa
                                            @else
                                                Không xác định
                                            @endif
                                            <input type="hidden" name="materials[{{ $key }}][item_type]" value="{{ $item->item_type ?? 'material' }}">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên vật tư/ thành phẩm/ hàng hoá</label>
                                        <div class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 bg-gray-100 flex items-center">
                                            @if($item->item_type === 'material')
                                                {{ $item->material ? ($item->material->code . ' - ' . $item->material->name) : 'Không xác định' }}
                                            @elseif($item->item_type === 'product')
                                                {{ $item->product ? ($item->product->code . ' - ' . $item->product->name) : 'Không xác định' }}
                                            @elseif($item->item_type === 'good')
                                                {{ $item->good ? ($item->good->code . ' - ' . $item->good->name) : 'Không xác định' }}
                                            @else
                                                Không xác định
                                            @endif
                                            <input type="hidden" name="materials[{{ $key }}][material_id]" value="{{ $item->material_id }}">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kho nhập</label>
                                        <div class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 bg-gray-100 flex items-center">
                                            {{ $item->warehouse ? $item->warehouse->name : 'Không xác định' }}
                                            <input type="hidden" name="materials[{{ $key }}][warehouse_id]" value="{{ $item->warehouse_id }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                                        <div class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 bg-gray-100 flex items-center">
                                            {{ $item->quantity }}
                                            <input type="hidden" name="materials[{{ $key }}][quantity]" value="{{ $item->quantity }}">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">List số seri</label>
                                        <div class="w-full min-h-10 border border-gray-200 rounded-lg px-3 py-2 bg-gray-100">
                                            {{ $item->serial_numbers ? implode(", ", $item->serial_numbers) : 'Không có' }}
                                            <input type="hidden" name="materials[{{ $key }}][serial_numbers]" value="{{ $item->serial_numbers ? implode(", ", $item->serial_numbers) : '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                                    <div class="w-full min-h-10 border border-gray-200 rounded-lg px-3 py-2 bg-gray-100">
                                        {{ $item->notes ?: 'Không có ghi chú' }}
                                        <input type="hidden" name="materials[{{ $key }}][notes]" value="{{ $item->notes }}">
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="p-4 bg-gray-50 rounded-lg text-gray-500 text-center">
                                Không có vật tư nào trong phiếu nhập kho này
                            </div>
                            @endforelse
                        </div>
                    </div>
                    
                    <!-- Bảng tổng hợp các vật tư đã thêm -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Tổng hợp vật tư, hàng hoá đã thêm</h3>
                        <div class="overflow-x-auto">
                            <table id="summary-table" class="min-w-full bg-white border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã - Tên sản phẩm</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kho nhập</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($inventoryImport->materials as $index => $item)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $index + 1 }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                @if(($item->item_type ?? 'material') == 'material')
                                                    Vật tư
                                                @elseif(($item->item_type ?? '') == 'product')
                                                    Thành phẩm
                                                @elseif(($item->item_type ?? '') == 'good')
                                                    Hàng hóa
                                                @else
                                                    Không xác định
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                @if($item->item_type === 'material')
                                                    {{ $item->material ? ($item->material->code . ' - ' . $item->material->name) : 'Không xác định' }}
                                                @elseif($item->item_type === 'product')
                                                    {{ $item->product ? ($item->product->code . ' - ' . $item->product->name) : 'Không xác định' }}
                                                @elseif($item->item_type === 'good')
                                                    {{ $item->good ? ($item->good->code . ' - ' . $item->good->name) : 'Không xác định' }}
                                                @else
                                                    Không xác định
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $item->warehouse ? $item->warehouse->name : 'Không xác định' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                @if($item->item_type === 'material')
                                                    {{ $item->material && isset($item->material->unit) ? $item->material->unit : '' }}
                                                @elseif($item->item_type === 'product')
                                                    {{ $item->product && isset($item->product->unit) ? $item->product->unit : '' }}
                                                @elseif($item->item_type === 'good')
                                                    {{ $item->good && isset($item->good->unit) ? $item->good->unit : '' }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $item->quantity }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $item->notes }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-4 text-sm text-gray-500 text-center">Không có vật tư nào trong phiếu nhập kho này</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('inventory-imports.show', $inventoryImport->id) }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu phiếu nhập
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 