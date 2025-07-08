<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thành phẩm - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa thành phẩm</h1>
                <div class="ml-4 px-2 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    ID: {{ $product->code }}
                </div>
            </div>
            <a href="{{ route('products.show', $product->id) }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('products.update', $product->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 p-4 rounded-lg border border-red-200">
                            <div class="text-red-600 font-medium mb-2">Có lỗi xảy ra:</div>
                            <ul class="list-disc pl-5 text-red-500">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-50 p-4 rounded-lg border border-red-200">
                            <div class="text-red-600">{{ session('error') }}</div>
                        </div>
                    @endif

                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin thành phẩm</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã
                                    thành phẩm <span class="text-red-500">*</span></label>
                                <input type="text" id="code" name="code" value="{{ $product->code }}"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên
                                    thành phẩm <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" value="{{ $product->name }}"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô
                                    tả</label>
                                <textarea id="description" name="description" rows="3"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $product->description }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Upload hình ảnh -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hình ảnh thành phẩm</label>
                        <div id="dropzone"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition-colors cursor-pointer mb-2">
                            <input type="file" id="imageInput" name="images[]" class="hidden" multiple
                                accept="image/*">
                            <div class="mb-2">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl"></i>
                            </div>
                            <p class="text-gray-700 mb-1">Kéo thả hình ảnh vào đây hoặc</p>
                            <button type="button" id="addImageBtn"
                                class="text-blue-500 font-medium hover:text-blue-600">Chọn từ thiết bị</button>
                            <p class="text-xs text-gray-500 mt-1">Hỗ trợ: JPG, PNG, GIF (Tối đa 2MB)</p>
                        </div>

                        <!-- Hidden field for storing IDs of deleted images -->
                        <input type="hidden" name="deleted_images" id="deletedImages" value="">

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-3">
                            <!-- Existing images would be displayed here -->
                            @if (isset($product->images) && count($product->images) > 0)
                                @foreach ($product->images as $image)
                                    <div id="existing-image-{{ $image->id }}" class="relative">
                                        <div class="w-32 h-32 border border-gray-200 rounded-lg overflow-hidden">
                                            <img src="{{ asset('storage/' . $image->image_path) }}"
                                                class="w-full h-full object-cover">
                                        </div>
                                        <button type="button"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 delete-existing-image"
                                            data-image-id="{{ $image->id }}">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div id="imagePreviewContainer" class="flex flex-wrap gap-3 mt-3">
                            <!-- New image previews will be displayed here -->
                        </div>
                    </div>

                    <!-- Vật tư sử dụng -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thông tin vật tư sử dụng</label>
                        <div class="border border-gray-300 rounded-lg p-4">
                            <div id="materialsContainer" class="space-y-3">
                                <!-- Materials would be populated here -->
                                @if (isset($product->materials) && count($product->materials) > 0)
                                    @foreach ($product->materials as $material)
                                        <div class="material-row grid grid-cols-12 gap-2 items-center">
                                            <div class="col-span-5">
                                                <select
                                                    class="material-select w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                    <option value="">-- Chọn vật tư --</option>
                                                    @foreach($materials as $mat)
                                                        <option value="{{ $mat->id }}" {{ $mat->id == $material->id ? 'selected' : '' }}>
                                                            {{ $mat->name }} ({{ $mat->code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-span-3">
                                                <input type="number" placeholder="Số lượng"
                                                    value="{{ number_format($material->pivot->quantity) ?? 1 }}" min="1"
                                                    class="material-quantity w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </div>
                                            <div class="col-span-3">
                                                <input type="text" placeholder="Ghi chú"
                                                    value="{{ $material->pivot->notes ?? '' }}"
                                                    class="material-note w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </div>
                                            <div class="col-span-1">
                                                <button type="button"
                                                    class="remove-material-btn w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                                    <i
                                                        class="fas fa-times text-red-500 group-hover:text-white text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Initial empty material row -->
                                    <div class="material-row grid grid-cols-12 gap-2 items-center">
                                        <div class="col-span-5">
                                            <select
                                                class="material-select w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                <option value="">-- Chọn vật tư --</option>
                                                @foreach($materials as $material)
                                                    <option value="{{ $material->id }}">{{ $material->name }} ({{ $material->code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-3">
                                            <input type="number" placeholder="Số lượng" min="1"
                                                class="material-quantity w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </div>
                                        <div class="col-span-3">
                                            <input type="text" placeholder="Ghi chú"
                                                class="material-note w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </div>
                                        <div class="col-span-1">
                                            <button type="button"
                                                class="remove-material-btn w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group">
                                                <i
                                                    class="fas fa-times text-red-500 group-hover:text-white text-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="my-3">
                                <button type="button" id="addMaterialBtn"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg flex items-center text-sm">
                                    <i class="fas fa-plus mr-2"></i> Thêm vật tư
                                </button>
                            </div>
                            <!-- No materials message -->
                            <div id="noMaterialsMessage"
                                class="text-gray-500 text-center py-3 {{ isset($product->materials) && count($product->materials) > 0 ? 'hidden' : '' }}">
                                Chưa có vật tư nào được thêm
                            </div>
                        </div>
                    </div>

                    <!-- Cách tính tồn kho -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kho dùng để tính tồn kho</label>
                        <div class="space-y-2">
                            @foreach (App\Models\Warehouse::orderBy('name')->where('status','active')->where('is_hidden', 0)->get() as $warehouse)
                                <div class="flex items-center">
                                    <input type="checkbox" id="warehouse_{{ $warehouse->id }}"
                                        name="inventory_warehouses[]" value="{{ $warehouse->id }}"
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        {{ is_array($product->inventory_warehouses ?? null) && in_array($warehouse->id, $product->inventory_warehouses) ? 'checked' : '' }}>
                                    <label for="warehouse_{{ $warehouse->id }}"
                                        class="ml-2 block text-sm text-gray-700">
                                        {{ $warehouse->name }}
                                    </label>
                                </div>
                            @endforeach
                            <div class="flex items-center mt-2">
                                <input type="checkbox" id="warehouse_all" name="inventory_warehouses[]"
                                    value="all"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    {{ is_array($product->inventory_warehouses ?? null) && in_array('all', $product->inventory_warehouses) ? 'checked' : '' }}>
                                <label for="warehouse_all" class="ml-2 block text-sm text-gray-700 font-medium">
                                    Tất cả các kho
                                </label>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Lựa chọn kho để thực hiện đếm số lượng thành phẩm tồn trong
                            kho đó.</p>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('products.show', $product->id) }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Hủy
                        </a>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Pass materials data to JavaScript -->
    <script>
        window.materialsData = @json($materials);
    </script>
    
    <!-- JavaScript for UI functionality -->
    <script src="{{ asset('js/product-form.js') }}"></script>
</body>

</html>
