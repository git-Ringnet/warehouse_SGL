<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu chuyển kho - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu chuyển kho</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Mã phiếu: {{ $warehouseTransfer->transfer_code }}
                </div>
                <div class="ml-2 px-3 py-1 {{ $warehouseTransfer->status_class }} text-sm rounded-full">
                    {{ $warehouseTransfer->status_label }}
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('warehouse-transfers.index') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ route('warehouse-transfers.edit', $warehouseTransfer->id) }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <button onclick="openDeleteModal('{{ $warehouseTransfer->id }}', '{{ $warehouseTransfer->transfer_code }}')" class="h-10 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                </button>
            </div>
        </header>

        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        
        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        <main class="p-6 space-y-6">
            <!-- Thông tin cơ bản -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">Thông tin phiếu chuyển kho</h2>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Ngày tạo: {{ $warehouseTransfer->created_at->format('d/m/Y') }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cột 1 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Kho nguồn</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $warehouseTransfer->source_warehouse->name }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $warehouseTransfer->status_class }}">
                                <i class="fas fa-check-circle mr-1"></i> {{ $warehouseTransfer->status_label }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Cột 2 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Kho đích</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $warehouseTransfer->destination_warehouse->name }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ngày chuyển kho</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $warehouseTransfer->transfer_date->format('d/m/Y') }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Nhân viên thực hiện</p>
                            <div class="flex items-center">
                                <span class="text-base text-gray-800 font-semibold">{{ $warehouseTransfer->employee->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ghi chú -->
                <div class="mt-4">
                    <p class="text-sm text-gray-500 font-medium mb-1">Ghi chú</p>
                    <p class="text-base text-gray-800">{{ $warehouseTransfer->notes ?? 'Không có ghi chú' }}</p>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between">
                    <div>
                        <p class="text-sm text-gray-600">
                            Thời gian tạo: {{ $warehouseTransfer->created_at->format('d/m/Y H:i:s') }}
                        </p>
                        @if($warehouseTransfer->created_at != $warehouseTransfer->updated_at)
                        <p class="text-sm text-gray-600">
                            Cập nhật lần cuối: {{ $warehouseTransfer->updated_at->format('d/m/Y H:i:s') }}
                        </p>
                        @endif
                    </div>
                    
                    <button onclick="openDeleteModal('{{ $warehouseTransfer->id }}', '{{ $warehouseTransfer->transfer_code }}')" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash mr-1"></i> Xóa phiếu chuyển kho
                    </button>
                    <form id="delete-form-{{ $warehouseTransfer->id }}" action="{{ route('warehouse-transfers.destroy', $warehouseTransfer->id) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
            
            <!-- Thông tin vật tư -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Danh sách vật tư chuyển kho</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã - Tên vật tư</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kho nguồn</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số seri</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($selectedMaterials as $index => $material)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $material['name'] }}</td>
                                <td class="px-4 py-2 text-sm">
                                    @php
                                        $typeClass = 'bg-gray-100 text-gray-800';
                                        $typeDisplay = 'Khác';
                                        
                                        if ($material['type'] == 'material') {
                                            $typeClass = 'bg-blue-100 text-blue-800';
                                            $typeDisplay = 'Vật tư';
                                        } elseif ($material['type'] == 'product') {
                                            $typeClass = 'bg-green-100 text-green-800';
                                            $typeDisplay = 'Thành phẩm';
                                        } elseif ($material['type'] == 'good') {
                                            $typeClass = 'bg-yellow-100 text-yellow-800';
                                            $typeDisplay = 'Hàng hóa';
                                        }
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeClass }}">{{ $typeDisplay }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $warehouseTransfer->source_warehouse->name }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $material['quantity'] }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    @if(isset($material['serial_numbers']) && !empty($material['serial_numbers']))
                                        @if(is_array($material['serial_numbers']))
                                            {{ implode(', ', $material['serial_numbers']) }}
                                        @else
                                            {{ $material['serial_numbers'] }}
                                        @endif
                                    @else
                                        Không có
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Khởi tạo modal khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
        });

        // Mở modal xác nhận xóa
        function openDeleteModal(id, name) {
            // Thay đổi nội dung modal
            document.getElementById('customerNameToDelete').innerText = "phiếu chuyển kho " + name;
            
            // Thay đổi hành động khi nút xác nhận được nhấn
            document.getElementById('confirmDeleteBtn').onclick = function() {
                document.getElementById('delete-form-' + id).submit();
                closeDeleteModal();
            };
            
            // Hiển thị modal
            document.getElementById('deleteModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    </script>
</body>
</html> 