<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu nhập kho - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu nhập kho #{{ $inventoryImport->import_code }}</h1>
            <div class="flex items-center space-x-2">
                <a href="{{ route('inventory-imports.edit', $inventoryImport->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Sửa
                </a>
                <a href="{{ route('inventory-imports.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
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
                <div class="space-y-6">
                    <!-- Thông tin phiếu nhập kho -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Thông tin phiếu nhập kho</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Mã phiếu nhập</p>
                                    <p class="text-base font-medium text-gray-900">{{ $inventoryImport->import_code }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Ngày nhập kho</p>
                                    <p class="text-base text-gray-900">{{ $inventoryImport->import_date->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Nhà cung cấp</p>
                                    <p class="text-base text-gray-900">{{ $inventoryImport->supplier->name }}</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Kho nhập</p>
                                    <p class="text-base text-gray-900">{{ $inventoryImport->warehouse->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Mã đơn hàng</p>
                                    <p class="text-base text-gray-900">{{ $inventoryImport->order_code ?? 'Không có' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Ghi chú</p>
                                    <p class="text-base text-gray-900">{{ $inventoryImport->notes ?? 'Không có' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Danh sách vật tư -->
                    <div class="mt-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Danh sách vật tư nhập kho</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã vật tư</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Serial</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse($inventoryImport->materials as $key => $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $key + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->material->code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->material->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->material->unit }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->serial ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->notes ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ liệu vật tư</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between">
                    <div>
                        <p class="text-sm text-gray-600">
                            Thời gian tạo: {{ $inventoryImport->created_at->format('d/m/Y H:i:s') }}
                        </p>
                        @if($inventoryImport->created_at != $inventoryImport->updated_at)
                        <p class="text-sm text-gray-600">
                            Cập nhật lần cuối: {{ $inventoryImport->updated_at->format('d/m/Y H:i:s') }}
                        </p>
                        @endif
                    </div>
                    
                    <button onclick="openDeleteModal('{{ $inventoryImport->id }}', '{{ $inventoryImport->import_code }}')" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash mr-1"></i> Xóa phiếu nhập
                    </button>
                    <form id="delete-form-{{ $inventoryImport->id }}" action="{{ route('inventory-imports.destroy', $inventoryImport->id) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
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
            document.getElementById('customerNameToDelete').innerText = "phiếu nhập " + name;
            
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