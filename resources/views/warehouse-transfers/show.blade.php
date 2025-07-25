<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chi tiết phiếu chuyển kho - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu chuyển kho</h1>
            <div class="flex items-center space-x-3">
                <a href="{{ route('warehouse-transfers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>

                @if($warehouseTransfer->status === 'pending')
                    <a href="{{ route('warehouse-transfers.edit', $warehouseTransfer) }}" 
                        class="bg-yellow-500 hover:bg-yellow-600 text-white h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>

                    <form action="{{ route('warehouse-transfers.approve', $warehouseTransfer) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                            onclick="return confirm('Bạn có chắc chắn muốn hoàn thành phiếu chuyển kho này?')"
                            class="bg-green-500 hover:bg-green-600 text-white h-10 px-4 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-check mr-2"></i> Hoàn thành
                        </button>
                    </form>

                    <form action="{{ route('warehouse-transfers.destroy', $warehouseTransfer) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                            onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu chuyển kho này?')"
                            class="bg-red-500 hover:bg-red-600 text-white h-10 px-4 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-trash mr-2"></i> Xóa
                </button>
                    </form>
                @endif
            </div>
        </header>

        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        
        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <!-- Thông tin chung -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Mã phiếu chuyển</label>
                            <div class="text-base text-gray-900">{{ $warehouseTransfer->transfer_code }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Kho nguồn</label>
                            <div class="text-base text-gray-900">{{ $warehouseTransfer->source_warehouse->name }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Kho đích</label>
                            <div class="text-base text-gray-900">{{ $warehouseTransfer->destination_warehouse->name }}</div>
                        </div>
                        
                    <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nhân viên thực hiện</label>
                            <div class="text-base text-gray-900">{{ $warehouseTransfer->employee ? $warehouseTransfer->employee->name : 'Không có' }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Ghi chú</label>
                            <div class="text-base text-gray-900">{{ $warehouseTransfer->notes ?: 'Không có' }}</div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Người tạo phiếu</label>
                            <div class="text-base text-gray-900">{{ $warehouseTransfer->creator ? $warehouseTransfer->creator->name : 'Không có' }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Ngày tạo phiếu</label>
                            <div class="text-base text-gray-900">{{ $warehouseTransfer->created_at->format('H:i d/m/Y') }}</div>
                        </div>
                        
                    <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Ngày chuyển</label>
                            <div class="text-base text-gray-900">{{ date('d/m/Y', strtotime($warehouseTransfer->transfer_date)) }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Chỉnh sửa lần cuối</label>
                            <div class="text-base text-gray-900">{{ $warehouseTransfer->updated_at->format('H:i d/m/Y') }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Trạng thái</label>
                            <div class="inline-flex">
                                @php
                                    $statusClass = $warehouseTransfer->status === 'pending' 
                                        ? 'bg-yellow-100 text-yellow-800' 
                                        : 'bg-green-100 text-green-800';
                                    $statusText = $warehouseTransfer->status === 'pending' 
                                        ? 'Chờ xử lý' 
                                        : 'Hoàn thành';
                                @endphp
                                <span class="px-2 py-1 rounded-full text-sm font-medium {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Danh sách vật tư chuyển kho -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Danh sách vật tư chuyển kho</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã - Tên vật tư</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số seri</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                                @foreach($warehouseTransfer->materials as $index => $material)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-2 text-sm">
                                    @php
                                            $typeClass = match($material->type) {
                                                'material' => 'bg-blue-100 text-blue-800',
                                                'product' => 'bg-green-100 text-green-800',
                                                'good' => 'bg-yellow-100 text-yellow-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            $typeText = match($material->type) {
                                                'material' => 'Vật tư',
                                                'product' => 'Thành phẩm',
                                                'good' => 'Hàng hóa',
                                                default => 'Khác'
                                            };
                                    @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeClass }}">
                                            {{ $typeText }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        @php
                                            $item = $material->item();
                                        @endphp
                                        {{ $item ? ($item->code . ' - ' . $item->name) : 'Không tìm thấy' }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $material->quantity }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                        @if($material->serial_numbers)
                                            @php
                                                $serials = is_array($material->serial_numbers) 
                                                    ? $material->serial_numbers 
                                                    : json_decode($material->serial_numbers, true);
                                            @endphp
                                            @if(!empty($serials))
                                                {{ implode(', ', $serials) }}
                                            @else
                                                Không có
                                            @endif
                                        @else
                                        Không có
                                    @endif
                                </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $material->notes ?: 'Không có' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 