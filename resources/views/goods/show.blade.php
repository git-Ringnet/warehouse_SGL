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
                <a href="{{ route('goods.edit', $good->id ?? 1) }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
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
                        <p class="text-gray-900">06/06/2025</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Cập nhật lần cuối</p>
                        <p class="text-gray-900">06/06/2025</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nhà cung cấp</p>
                        <div class="text-gray-900">
                            @if(isset($good->suppliers) && count($good->suppliers) > 0)
                                @foreach($good->suppliers as $supplier)
                                    <div class="flex items-center mb-1">
                                        <i class="fas fa-building text-gray-400 mr-2"></i>
                                        <a href="{{ route('suppliers.show', $supplier->id) }}" class="text-blue-600 hover:underline">
                                            {{ $supplier->name }}
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <!-- Sample supplier data -->
                                <div class="flex items-center mb-1">
                                    <i class="fas fa-building text-gray-400 mr-2"></i>
                                    <a href="#" class="text-blue-600 hover:underline">Công ty TNHH Điện tử ABC</a>
                                    <span class="text-blue-600 mx-2">Xem chi tiết</span>
                                </div>
                                <div class="flex items-center mb-1">
                                    <i class="fas fa-building text-gray-400 mr-2"></i>
                                    <a href="#" class="text-blue-600 hover:underline">Công ty CP Thiết bị XYZ</a>
                                    <span class="text-blue-600 mx-2">Xem chi tiết</span>
                                </div>
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
                                <span class="px-3 py-1.5 rounded-md bg-green-100 text-green-800 inline-flex items-center">
                                    <i class="fas fa-warehouse mr-2 text-green-600"></i>
                                    <span class="text-lg">120</span>
                                </span>
                            </p>
                            {{-- <p class="text-xs text-gray-500 mt-1">
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
                            </p> --}}
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
                @else
                    <!-- Demo images when not available -->
                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <p class="text-sm text-gray-500 mb-2">Hình ảnh</p>
                        <div class="flex flex-wrap gap-4">
                            @for ($i = 1; $i <= 4; $i++)
                                <div class="w-32 h-32">
                                    <img src="https://source.unsplash.com/random/300x300?product&sig={{ $i }}"
                                        alt="Demo Image {{ $i }}"
                                        class="w-full h-full object-cover rounded-lg border border-gray-200 cursor-pointer"
                                        onclick="openImageModal('https://source.unsplash.com/random/800x600?product&sig={{ $i }}')">
                                </div>
                            @endfor
                        </div>
                    </div>
                @endif

                {{-- @if (isset($good) && $good->notes)
                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-sm text-gray-500">Ghi chú</p>
                        <p class="text-gray-900 mt-1">{{ $good->notes }}</p>
                    </div>
                @endif --}}
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-sm text-gray-500">Ghi chú</p>
                    <p class="text-gray-900 mt-1">Ốc vít thông dụng</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <button onclick="confirmDelete()"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i> Xóa hàng hóa
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal xác nhận xóa -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xác nhận xóa</h3>
            <p class="text-gray-700 mb-6">Bạn có chắc chắn muốn xóa hàng hóa này? Hành động này không thể hoàn tác.</p>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeDeleteModal()">
                    Hủy
                </button>
                <form action="#" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                        Xác nhận xóa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal xem ảnh lớn -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300">
            <i class="fas fa-times"></i>
        </button>
        <img id="largeImage" src="" alt="Ảnh lớn" class="max-w-4xl max-h-[80vh] object-contain">
    </div>

    <script>
        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
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
                closeImageModal();
            }
        });
    </script>
</body>

</html>
