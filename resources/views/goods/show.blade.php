<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết hàng hóa - SGL</title>
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
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết hàng hóa</h1>
            </div>
            <div class="flex space-x-2">
                @if ($good->status !== 'deleted' && !$good->is_hidden)
                    <a href="{{ route('goods.edit', $good->id ?? 1) }}"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                    </a>
                @endif
                <a href="{{ route('goods.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">{{ $good->name ?? 'Hàng hóa mẫu 1' }}</h2>
                                <p class="text-gray-600 mt-1">Mã hàng hóa: {{ $good->code ?? 'HH-101' }}</p>
                            </div>
                            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end">
                                @if ($good->status === 'deleted')
                                    <div class="flex items-center mb-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-trash mr-1"></i>
                                            Đã xóa
                                        </span>
                                    </div>
                                @elseif($good->is_hidden)
                                    <div class="flex items-center mb-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-eye-slash mr-1"></i>
                                            Đã ẩn
                                        </span>
                                    </div>
                                @endif
                                <div class="flex items-center">
                                    <i class="fas fa-tag text-gray-400 mr-2"></i>
                                    <span class="text-gray-800 font-medium">{{ $good->category ?? 'Loại 1' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin chi tiết -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin hàng hóa</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500">Tên hàng hóa</p>
                        <p class="text-gray-900 font-medium">{{ $good->name ?? 'Hàng hóa mẫu 1' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Mã hàng hóa</p>
                        <p class="text-gray-900 font-medium">{{ $good->code ?? 'HH-101' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Loại hàng hóa</p>
                        <p class="text-gray-900">{{ $good->category ?? 'Loại 1' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Đơn vị</p>
                        <p class="text-gray-900">{{ $good->unit ?? 'Cái' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ngày tạo</p>
                        <p class="text-gray-900">{{ $good->created_at->format('H:i d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Cập nhật lần cuối</p>
                        <p class="text-gray-900">{{ $good->updated_at->format('H:i d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nhà cung cấp</p>
                        <div class="text-gray-900">
                            @if ($good->suppliers && $good->suppliers->count() > 0)
                                @foreach ($good->suppliers as $supplier)
                                    <div class="flex items-center mb-1">
                                        <i class="fas fa-building text-gray-400 mr-2"></i>
                                        <a href="{{ route('suppliers.show', $supplier->id) }}"
                                            class="text-blue-600 hover:underline">
                                            {{ $supplier->name }}
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-gray-500">Chưa có nhà cung cấp</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Thông tin số lượng -->
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h4 class="text-md font-semibold text-gray-700 mb-3">Thông tin số lượng</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                        <div>
                            <p class="text-sm text-gray-500">Tổng số lượng tồn kho</p>
                            <p class="text-gray-900 font-medium mt-1">
                                <span
                                    class="px-3 py-1.5 rounded-md {{ $inventoryQuantity > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} inline-flex items-center">
                                    <i class="fas fa-warehouse mr-2 {{ $inventoryQuantity > 0 ? 'text-green-600' : 'text-red-600' }}"></i>
                                    <span class="text-lg">{{ number_format($inventoryQuantity, 0, ',', '.') }}</span>
                                </span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Tính theo:
                                @if (isset($good) && is_array($good->inventory_warehouses) && in_array('all', $good->inventory_warehouses))
                                    Tất cả các kho
                                @elseif(isset($good) && is_array($good->inventory_warehouses) && !empty($good->inventory_warehouses))
                                    @php
                                        $warehouseNames = [];
                                        foreach ($good->inventory_warehouses as $warehouseId) {
                                            $warehouse = App\Models\Warehouse::find($warehouseId);
                                            if ($warehouse) {
                                                $warehouseNames[] = $warehouse->name;
                                            }
                                        }
                                    @endphp
                                    {{ implode(', ', $warehouseNames) }}
                                @else
                                    Tất cả các kho
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Hình ảnh hàng hóa nếu có -->
                @if (isset($good) && $good->images && $good->images->count() > 0)
                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <p class="text-sm text-gray-500 mb-2">Hình ảnh</p>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($good->images as $image)
                                <div class="w-32 h-32">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $good->name }}"
                                        class="w-full h-full object-cover rounded-lg border border-gray-200 cursor-pointer"
                                        onclick="openImageModal('{{ asset('storage/' . $image->image_path) }}')">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (isset($good) && $good->notes)
                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-sm text-gray-500">Ghi chú</p>
                        <p class="text-gray-900 mt-1">{{ $good->notes }}</p>
                    </div>
                @endif

                @if ($good->status !== 'deleted' && !$good->is_hidden)
                    <div class="mt-6 flex justify-end">
                        <button
                            onclick="openDeleteModal('{{ $good->id }}', '{{ $good->code }}', {{ $inventoryQuantity }})"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-trash mr-2"></i> Xóa hàng hóa
                        </button>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Modal xác nhận xóa khi có tồn kho -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Không thể xóa</h3>
            <p class="text-red-700 mb-6">Không thể xóa hàng hóa <span id="goodCode" class="font-semibold"></span> vì
                còn tồn kho: <span id="inventoryQuantity" class="font-semibold"></span></p>
            <div class="flex justify-end">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeDeleteModal()">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận xóa khi không có tồn kho -->
    <div id="deleteZeroInventoryModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xác nhận thao tác</h3>
            <p class="text-gray-700 mb-6">Thao tác xóa có thể làm mất dữ liệu. Bạn muốn xác nhận ngừng sử dụng và ẩn
                hạng mục này thay cho việc xóa?</p>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeDeleteZeroInventoryModal()">
                    Hủy
                </button>
                <form id="hideForm" action="{{ route('goods.destroy', $good->id) }}" method="POST"
                    class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="action" value="hide">
                    <button type="submit"
                        class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors">
                        Có (Ẩn hạng mục)
                    </button>
                </form>
                <form id="deleteForm" action="{{ route('goods.destroy', $good->id) }}" method="POST"
                    class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="action" value="delete">
                    <button type="submit"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                        Không (Đánh dấu đã xóa)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal xem ảnh lớn -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-[9999] hidden flex items-center justify-center">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300">
            <i class="fas fa-times"></i>
        </button>
        <img id="largeImage" src="" alt="Ảnh lớn" class="max-w-4xl max-h-[80vh] object-contain">
    </div>

    <script>
        function openDeleteModal(goodId, goodCode, inventoryQuantity) {
            if (inventoryQuantity > 0) {
                // Show inventory warning modal
                document.getElementById('goodCode').textContent = goodCode;
                document.getElementById('inventoryQuantity').textContent = new Intl.NumberFormat('vi-VN').format(
                    inventoryQuantity);
                document.getElementById('deleteModal').classList.remove('hidden');
            } else {
                // Show confirmation modal for zero inventory
                // Set form actions for both hide and delete forms
                const deleteForm = document.getElementById('deleteForm');
                const hideForm = document.getElementById('hideForm');

                if (deleteForm) {
                    deleteForm.action = `/goods/${goodId}`;
                }
                if (hideForm) {
                    hideForm.action = `/goods/${goodId}`;
                }

                document.getElementById('deleteZeroInventoryModal').classList.remove('hidden');
            }
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function closeDeleteZeroInventoryModal() {
            document.getElementById('deleteZeroInventoryModal').classList.add('hidden');
        }

        function openImageModal(imageUrl) {
            const modal = document.getElementById('imageModal');
            const largeImage = document.getElementById('largeImage');

            largeImage.src = imageUrl;
            modal.classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        // Add escape key listener for modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
                closeDeleteZeroInventoryModal();
                closeImageModal();
            }
        });
    </script>
</body>

</html>
