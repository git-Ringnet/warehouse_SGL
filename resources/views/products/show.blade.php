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
                <a href="{{ route('products.edit', $product->id) }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
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
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-tag text-blue-500 mr-2"></i>
                                    <span class="text-blue-500 font-medium">{{ $product->type }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin chi tiết -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin sản phẩm</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500">Tên sản phẩm</p>
                        <p class="text-gray-900 font-medium">{{ $product->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Mã sản phẩm</p>
                        <p class="text-gray-900 font-medium">{{ $product->code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Loại sản phẩm</p>
                        <p class="text-gray-900">{{ $product->type }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ngày tạo</p>
                        <p class="text-gray-900">{{ $product->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
                
                @if($product->description)
                <div class="border-t border-gray-200 pt-4 mb-6">
                    <p class="text-sm text-gray-500">Mô tả sản phẩm</p>
                    <p class="text-gray-900 mt-1">{{ $product->description }}</p>
                </div>
                @endif

                <div class="border-t border-gray-200 pt-4">
                    <div class="text-sm text-gray-500 mb-2">
                        <p><i>Lưu ý: Serial sẽ được tạo khi thành phẩm được lắp ráp</i></p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button onclick="confirmDelete()" 
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i> Xóa sản phẩm
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal xác nhận xóa -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xác nhận xóa</h3>
            <p class="text-gray-700 mb-6">Bạn có chắc chắn muốn xóa thành phẩm này? Hành động này không thể hoàn tác.</p>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeDeleteModal()">
                    Hủy
                </button>
                <form action="{{ route('products.destroy', $product->id) }}" method="POST">
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

    <script>
        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</body>

</html> 