<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm thành phẩm mới - SGL</title>
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
                <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Thêm thành phẩm mới</h1>
            </div>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mx-auto">
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã
                                    thành phẩm <span class="text-red-500">*</span></label>
                                <input type="text" id="code" name="code" placeholder="SP-XXXX" required
                                    value="{{ old('code') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên
                                    thành phẩm <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" placeholder="Nhập tên thành phẩm"
                                    required value="{{ old('name') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Loại thành phẩm</label>
                                <div class="relative">
                                    <div class="flex">
                                        <select id="category" name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-10">
                                            <option value="">-- Chọn loại thành phẩm --</option>
                                            @foreach(\App\Models\Product::where('category', '!=', '')->whereNotNull('category')->distinct()->pluck('category')->toArray() as $cat)
                                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" id="addCategoryBtn" class="ml-2 bg-green-500 hover:bg-green-600 text-white w-10 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Modal thêm loại thành phẩm mới -->
                                <div id="categoryModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
                                    <div class="bg-white rounded-lg p-6 w-full max-w-md">
                                        <h3 class="text-lg font-semibold mb-4">Thêm loại thành phẩm mới</h3>
                                        <input type="text" id="newCategory" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập tên loại thành phẩm mới">
                                        <div class="flex justify-end space-x-3">
                                            <button type="button" id="cancelAddCategory" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">Hủy</button>
                                            <button type="button" id="confirmAddCategory" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg">Thêm</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô
                                    tả</label>
                                <textarea id="description" name="description" rows="3"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
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
                        <div id="imagePreviewContainer" class="flex flex-wrap gap-3 mt-3">
                            <!-- Image previews will be displayed here -->
                        </div>
                    </div>

                    <!-- Vật tư sử dụng -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thông tin vật tư sử dụng</label>
                        <div class="border border-gray-300 rounded-lg p-4">
                            <div id="materialsContainer" class="space-y-3">
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
                                            <i class="fas fa-times text-red-500 group-hover:text-white text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="my-3">
                                <button type="button" id="addMaterialBtn"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg flex items-center text-sm">
                                    <i class="fas fa-plus mr-2"></i> Thêm vật tư
                                </button>
                            </div>

                            <!-- No materials message -->
                            <div id="noMaterialsMessage" class="text-gray-500 text-center py-3 hidden">
                                Chưa có vật tư nào được thêm
                            </div>
                        </div>
                    </div>

                    <!-- Cách tính tồn kho -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kho dùng để tính tồn kho</label>
                        <div class="space-y-2">
                            @foreach (App\Models\Warehouse::orderBy('name')->where('status', 'active')->where('is_hidden', false)->get() as $warehouse)
                                <div class="flex items-center">
                                    <input type="checkbox" id="warehouse_{{ $warehouse->id }}"
                                        name="inventory_warehouses[]" value="{{ $warehouse->id }}"
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <label for="warehouse_{{ $warehouse->id }}"
                                        class="ml-2 block text-sm text-gray-700">
                                        {{ $warehouse->name }}
                                        @if($warehouse->is_hidden || $warehouse->status !== 'active')
                                            <span class="text-xs text-gray-500 italic">({{ $warehouse->is_hidden ? 'Đã ẩn' : 'Đã xóa' }})</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                            <div class="flex items-center mt-2">
                                <input type="checkbox" id="warehouse_all" name="inventory_warehouses[]"
                                    value="all"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                <label for="warehouse_all" class="ml-2 block text-sm text-gray-700 font-medium">
                                    Tất cả các kho
                                </label>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Lựa chọn kho để thực hiện đếm số lượng thành phẩm tồn trong
                            kho đó.</p>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('products.index') }}"
                            class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                            Hủy
                        </a>
                        <button type="submit"
                            class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thành phẩm
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

    <!-- JavaScript for category management -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy các phần tử
            const categorySelect = document.getElementById('category');
            const addCategoryBtn = document.getElementById('addCategoryBtn');
            const categoryModal = document.getElementById('categoryModal');
            const newCategoryInput = document.getElementById('newCategory');
            const confirmAddCategory = document.getElementById('confirmAddCategory');
            const cancelAddCategory = document.getElementById('cancelAddCategory');
            
            // Mở modal thêm loại thành phẩm
            addCategoryBtn.addEventListener('click', function() {
                categoryModal.classList.remove('hidden');
                newCategoryInput.focus();
            });
            
            // Đóng modal khi nhấn Hủy
            cancelAddCategory.addEventListener('click', function() {
                categoryModal.classList.add('hidden');
                newCategoryInput.value = '';
            });
            
            // Xử lý thêm loại thành phẩm mới
            confirmAddCategory.addEventListener('click', function() {
                const newCategoryName = newCategoryInput.value.trim();
                if (newCategoryName) {
                    // Kiểm tra xem loại thành phẩm đã tồn tại chưa
                    let exists = false;
                    for (let i = 0; i < categorySelect.options.length; i++) {
                        if (categorySelect.options[i].text === newCategoryName) {
                            exists = true;
                            break;
                        }
                    }
                    
                    if (!exists) {
                        // Thêm option mới
                        const newOption = document.createElement('option');
                        newOption.value = newCategoryName;
                        newOption.text = newCategoryName;
                        newOption.selected = true;
                        categorySelect.appendChild(newOption);
                        
                        // Lưu danh sách loại thành phẩm vào localStorage
                        saveCategories();
                    }
                    
                    // Đóng modal
                    categoryModal.classList.add('hidden');
                    newCategoryInput.value = '';
                }
            });
            
            // Lưu danh sách loại thành phẩm vào localStorage
            function saveCategories() {
                const categories = [];
                for (let i = 1; i < categorySelect.options.length; i++) { // Bỏ qua option đầu tiên
                    categories.push(categorySelect.options[i].text);
                }
                localStorage.setItem('productCategories', JSON.stringify(categories));
            }
            
            // Tải danh sách loại thành phẩm từ localStorage
            function loadCategories() {
                const savedCategories = localStorage.getItem('productCategories');
                if (savedCategories) {
                    const categories = JSON.parse(savedCategories);
                    // Xem nếu có loại nào chưa có trong select thì thêm vào
                    categories.forEach(category => {
                        let exists = false;
                        for (let i = 0; i < categorySelect.options.length; i++) {
                            if (categorySelect.options[i].text === category) {
                                exists = true;
                                break;
                            }
                        }
                        if (!exists) {
                            const option = document.createElement('option');
                            option.value = category;
                            option.text = category;
                            categorySelect.appendChild(option);
                        }
                    });
                }
            }
            
            // Tải danh sách loại thành phẩm khi trang tải xong
            loadCategories();
        });
    </script>
</body>

</html>
