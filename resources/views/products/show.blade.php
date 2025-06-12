<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết thành phẩm - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết thành phẩm</h1>
            </div>
            <div class="flex space-x-2">
                @if ($product->status !== 'deleted' && !$product->is_hidden)
                    <a href="{{ route('products.edit', $product->id) }}"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                    </a>
                @endif
                <a href="{{ route('products.index') }}"
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
                                <h2 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h2>
                                <p class="text-gray-600 mt-1">Mã SP: {{ $product->code }}</p>
                            </div>
                            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end">
                                @if ($product->status === 'deleted')
                                    <div class="flex items-center mb-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-trash mr-1"></i>
                                            Đã xóa
                                        </span>
                                    </div>
                                @elseif($product->is_hidden)
                                    <div class="flex items-center mb-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-eye-slash mr-1"></i>
                                            Đã ẩn
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin chi tiết -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin thành phẩm</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500">Tên thành phẩm</p>
                        <p class="text-gray-900 font-medium">{{ $product->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Mã thành phẩm</p>
                        <p class="text-gray-900 font-medium">{{ $product->code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ngày tạo</p>
                        <p class="text-gray-900">{{ $product->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Cập nhật lần cuối</p>
                        <p class="text-gray-900">{{ $product->updated_at->format('d/m/Y H:i') }}</p>
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
                                    <i
                                        class="fas fa-warehouse mr-2 {{ $inventoryQuantity > 0 ? 'text-green-600' : 'text-red-600' }}"></i>
                                    <span class="text-lg">{{ number_format($inventoryQuantity, 0, ',', '.') }}</span>
                                </span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Tính theo: 
                                @if(is_array($product->inventory_warehouses) && in_array('all', $product->inventory_warehouses))
                                    Tất cả các kho
                                @elseif(is_array($product->inventory_warehouses) && !empty($product->inventory_warehouses))
                                    @php
                                        $warehouseNames = \App\Models\Warehouse::whereIn('id', $product->inventory_warehouses)->pluck('name')->toArray();
                                    @endphp
                                    {{ implode(', ', $warehouseNames) }}
                                @else
                                    Tất cả các kho
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Vật tư sử dụng cho thành phẩm -->
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h4 class="text-md font-semibold text-gray-700 mb-3">Vật tư sử dụng để lắp ráp</h4>

                    @if (isset($product->materials) && count($product->materials) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            STT</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            Mã vật tư</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            Tên vật tư</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            Số lượng</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            Đơn vị</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            Nhà cung cấp</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            Ghi chú</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach ($product->materials as $index => $material)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                                                {{ $index + 1 }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $material->code }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">
                                                {{ $material->name }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                                                <span
                                                    class="font-medium">{{ number_format($material->pivot->quantity) }}</span>
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                                                {{ $material->unit ?? 'N/A' }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                                                @if ($material->suppliers && count($material->suppliers) > 0)
                                                    @foreach ($material->suppliers as $supplier)
                                                        <span
                                                            class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-1 mb-1">
                                                            {{ $supplier->name }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-gray-400">Chưa có</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                                                {{ $material->pivot->notes ?? '-' }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                                                <a href="{{ route('materials.show', $material->id) }}"
                                                    class="text-blue-500 hover:text-blue-700">
                                                    Xem chi tiết
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-box-open text-gray-300 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Chưa có vật tư nào</h3>
                            <p class="text-gray-500 mb-4">Thành phẩm này chưa được cấu hình vật tư sử dụng</p>
                            @if ($product->status !== 'deleted' && !$product->is_hidden)
                                <a href="{{ route('products.edit', $product->id) }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Thêm vật tư
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Hình ảnh thành phẩm -->
                @if (isset($product->images) && count($product->images) > 0)
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="text-md font-semibold text-gray-700 mb-3">Hình ảnh thành phẩm</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach ($product->images as $image)
                                <div class="relative group">
                                    <div class="w-full h-32 border border-gray-200 rounded-lg overflow-hidden">
                                        <img src="{{ asset('storage/' . $image->image_path) }}"
                                            alt="{{ $image->alt_text ?? $product->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200 cursor-pointer"
                                            onclick="openImageModal('{{ asset('storage/' . $image->image_path) }}', '{{ $image->alt_text ?? $product->name }}')">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Mô tả -->
                @if ($product->description)
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="text-md font-semibold text-gray-700 mb-3">Mô tả</h4>
                        <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="max-w-4xl max-h-full p-4">
            <div class="relative">
                <img id="modalImage" src="" alt="" class="max-w-full max-h-full rounded-lg">
                <button onclick="closeImageModal()"
                    class="absolute top-2 right-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p id="modalImageCaption" class="text-white text-center mt-2"></p>
        </div>
    </div>

    <script>
        function openImageModal(src, alt) {
            document.getElementById('modalImage').src = src;
            document.getElementById('modalImage').alt = alt;
            document.getElementById('modalImageCaption').textContent = alt;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
</body>

</html>
