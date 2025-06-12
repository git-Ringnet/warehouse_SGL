<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa hàng hóa - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa hàng hóa</h1>
                <div class="ml-4 px-2 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    ID: {{ $good->code ?? 'HH-101' }}
                </div>
            </div>
            <a href="{{ route('goods.show', $good->id) }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('goods.update', $good->id) }}" method="POST" enctype="multipart/form-data">
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

                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin hàng hóa</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã
                                    hàng hóa <span class="text-red-500">*</span></label>
                                <input type="text" id="code" name="code"
                                    value="{{ $good->code ?? 'HH-101' }}" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên
                                    hàng hóa <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name"
                                    value="{{ $good->name ?? 'Hàng hóa mẫu 1' }}" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1 required">Loại
                                    hàng hóa <span class="text-red-500">*</span></label>
                                <div class="flex">
                                    <select id="category" name="category" required
                                        class="w-full border border-gray-300 rounded-lg rounded-r-none px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Chọn loại hàng hóa</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category }}"
                                                {{ ($good->category ?? 'Loại 1') == $category ? 'selected' : '' }}>
                                                {{ $category }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" id="addCategoryBtn"
                                        class="bg-blue-500 text-white px-3 py-2 rounded-lg rounded-l-none border-l-0 hover:bg-blue-600 transition-colors">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700 mb-1">Nhà cung
                                    cấp <span class="text-red-500">*</span></label>
                                <div class="space-y-2">
                                    <div class="flex">
                                        <select id="supplier_select"
                                            class="w-full border border-gray-300 rounded-lg rounded-r-none px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Chọn nhà cung cấp</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" id="addSupplierBtn"
                                            class="bg-green-500 text-white px-3 py-2 rounded-lg rounded-l-none border-l-0 hover:bg-green-600 transition-colors">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>

                                    <div id="selectedSuppliers" class="mt-2 space-y-2">
                                        <!-- Existing suppliers -->
                                        @if ($good->suppliers->count() > 0)
                                            @foreach ($good->suppliers as $supplier)
                                                <div
                                                    class="flex items-center justify-between bg-gray-100 p-2 rounded-lg supplier-item">
                                                    <span>{{ $supplier->name }}</span>
                                                    <input type="hidden" name="supplier_ids[]"
                                                        value="{{ $supplier->id }}">
                                                    <button type="button"
                                                        class="text-red-500 hover:text-red-700 remove-supplier">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @else
                                            <!-- Fallback to single supplier if available -->
                                            @if (isset($good->supplier_id) && $good->supplier_id)
                                                @php
                                                    $supplier = App\Models\Supplier::find($good->supplier_id);
                                                @endphp
                                                @if ($supplier)
                                                    <div
                                                        class="flex items-center justify-between bg-gray-100 p-2 rounded-lg supplier-item">
                                                        <span>{{ $supplier->name }}</span>
                                                        <input type="hidden" name="supplier_ids[]"
                                                            value="{{ $supplier->id }}">
                                                        <button type="button"
                                                            class="text-red-500 hover:text-red-700 remove-supplier">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700 mb-1 required">Đơn
                                    vị<span class="text-red-500">*</span></label>
                                <select id="unit" name="unit" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Chọn đơn vị</option>
                                    <option value="Cái" {{ ($good->unit ?? '') == 'Cái' ? 'selected' : '' }}>Cái
                                    </option>
                                    <option value="Bộ" {{ ($good->unit ?? '') == 'Bộ' ? 'selected' : '' }}>Bộ
                                    </option>
                                    <option value="Chiếc" {{ ($good->unit ?? '') == 'Chiếc' ? 'selected' : '' }}>Chiếc
                                    </option>
                                    <option value="Mét" {{ ($good->unit ?? '') == 'Mét' ? 'selected' : '' }}>Mét
                                    </option>
                                    <option value="Cuộn" {{ ($good->unit ?? '') == 'Cuộn' ? 'selected' : '' }}>Cuộn
                                    </option>
                                    <option value="Kg" {{ ($good->unit ?? '') == 'Kg' ? 'selected' : '' }}>Kg
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Hình ảnh hiện tại -->
                        <div class="md:col-span-2">
                            <label for="images" class="block text-sm font-medium text-gray-700 mb-1">Hình
                                ảnh</label>
                            <div class="flex flex-col space-y-2">
                                <div id="dropzone"
                                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition-colors cursor-pointer">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-2" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <p class="mb-1 text-gray-600 font-medium">Kéo và thả file hoặc</p>
                                        <p class="text-xs text-gray-500 mb-3">Hỗ trợ: JPG, JPEG, PNG, GIF (Tối đa 2MB)
                                        </p>
                                        <button type="button" id="addImageBtn"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                                            Chọn file
                                        </button>
                                    </div>
                                </div>
                                <input type="file" id="imageInput" name="images[]" accept="image/*" multiple
                                    class="hidden">

                                <!-- Hidden input for deleted images -->
                                <input type="hidden" id="deletedImages" name="deleted_images" value="">

                                <div id="imagePreviewContainer" class="flex flex-wrap gap-4 mt-2">
                                    <!-- Existing images -->
                                    @if (isset($good) && $good->images && count($good->images) > 0)
                                        @foreach ($good->images as $image)
                                            <div class="relative" id="existing-image-{{ $image->id }}">
                                                <div
                                                    class="w-32 h-32 border border-gray-200 rounded-lg overflow-hidden">
                                                    <img src="{{ asset('storage/' . $image->image_path) }}"
                                                        alt="{{ $good->name ?? 'Hàng hóa' }}"
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

                                    <!-- New image previews will be inserted here -->
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $good->notes ?? '' }}</textarea>
                        </div>

                        <!-- Cách tính tồn kho -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kho dùng để tính tồn
                                kho</label>
                            <div class="space-y-2">
                                @foreach (App\Models\Warehouse::orderBy('name')->get() as $warehouse)
                                    @php
                                        $isSelected =
                                            isset($good) &&
                                            is_array($good->inventory_warehouses) &&
                                            in_array($warehouse->id, $good->inventory_warehouses);
                                    @endphp
                                    <div class="flex items-center">
                                        <input type="checkbox" id="warehouse_{{ $warehouse->id }}"
                                            name="inventory_warehouses[]" value="{{ $warehouse->id }}"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                            {{ $isSelected ? 'checked' : '' }}>
                                        <label for="warehouse_{{ $warehouse->id }}"
                                            class="ml-2 block text-sm text-gray-700">
                                            {{ $warehouse->name }}
                                        </label>
                                    </div>
                                @endforeach
                                <div class="flex items-center mt-2">
                                    @php
                                        $allSelected =
                                            !isset($good) ||
                                            !is_array($good->inventory_warehouses) ||
                                            in_array('all', $good->inventory_warehouses) ||
                                            empty($good->inventory_warehouses);
                                    @endphp
                                    <input type="checkbox" id="warehouse_all" name="inventory_warehouses[]"
                                        value="all"
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        {{ $allSelected ? 'checked' : '' }}>
                                    <label for="warehouse_all" class="ml-2 block text-sm text-gray-700 font-medium">
                                        Tất cả các kho
                                    </label>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Lựa chọn kho để thực hiện đếm số lượng hàng hóa tồn
                                trong kho đó.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('goods.show', $good->id ?? 1) }}"
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

    <!-- Modal Add Category -->
    <div id="addCategoryModal"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Thêm loại hàng hóa mới</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" id="closeModalBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addCategoryForm">
                <div class="mb-4">
                    <label for="newCategoryName" class="block text-sm font-medium text-gray-700 mb-1">Tên loại hàng
                        hóa
                        <span class="text-red-500">*</span></label>
                    <input type="text" id="newCategoryName" name="newCategoryName" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" id="cancelCategoryBtn"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Hủy
                    </button>
                    <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                        <i class="fas fa-plus mr-2"></i> Thêm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Include common JS file -->
    <script src="{{ asset('js/good-form.js') }}"></script>
    <script>
        // Initialize the good form
        document.addEventListener('DOMContentLoaded', function() {
            initializeGoodForm(true); // true = edit form

            // Handle warehouse checkboxes
            const allWarehouseCheckbox = document.getElementById('warehouse_all');
            const warehouseCheckboxes = document.querySelectorAll(
                'input[name="inventory_warehouses[]"]:not([value="all"])');

            // When "All" is checked, uncheck others
            allWarehouseCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    warehouseCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                }
            });

            // When any other warehouse is checked, uncheck "All"
            warehouseCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        allWarehouseCheckbox.checked = false;
                    }

                    // If no warehouses are checked, check "All"
                    const anyChecked = Array.from(warehouseCheckboxes).some(cb => cb.checked);
                    if (!anyChecked) {
                        allWarehouseCheckbox.checked = true;
                    }
                });
            });

            // Handle supplier selection
            const supplierSelect = document.getElementById('supplier_select');
            const addSupplierBtn = document.getElementById('addSupplierBtn');
            const selectedSuppliersContainer = document.getElementById('selectedSuppliers');

            // Add supplier button click
            addSupplierBtn.addEventListener('click', function() {
                const selectedOption = supplierSelect.options[supplierSelect.selectedIndex];
                if (selectedOption.value === '') {
                    return; // No supplier selected
                }

                // Check if this supplier is already added
                const existingSuppliers = document.querySelectorAll('input[name="supplier_ids[]"]');
                for (const existingSupplier of existingSuppliers) {
                    if (existingSupplier.value === selectedOption.value) {
                        return; // Already added
                    }
                }

                // Create supplier item
                const supplierItem = document.createElement('div');
                supplierItem.className =
                    'flex items-center justify-between bg-gray-100 p-2 rounded-lg supplier-item';
                supplierItem.innerHTML = `
                    <span>${selectedOption.text}</span>
                    <input type="hidden" name="supplier_ids[]" value="${selectedOption.value}">
                    <button type="button" class="text-red-500 hover:text-red-700 remove-supplier">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                // Add item to container
                selectedSuppliersContainer.appendChild(supplierItem);

                // Add event listener for remove button
                const removeBtn = supplierItem.querySelector('.remove-supplier');
                removeBtn.addEventListener('click', function() {
                    supplierItem.remove();
                });

                // Reset select to default
                supplierSelect.value = '';
            });

            // Handle existing remove buttons
            document.querySelectorAll('.remove-supplier').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.supplier-item').remove();
                });
            });

            // Add handling for existing image deletion
            document.querySelectorAll('.delete-existing-image').forEach(button => {
                button.addEventListener('click', function() {
                    const imageId = this.dataset.imageId;
                    const deletedImagesInput = document.getElementById('deletedImages');
                    const currentDeleted = deletedImagesInput.value ? deletedImagesInput.value
                        .split(',') : [];

                    if (!currentDeleted.includes(imageId)) {
                        currentDeleted.push(imageId);
                        deletedImagesInput.value = currentDeleted.join(',');
                    }

                    document.getElementById('existing-image-' + imageId).remove();
                });
            });

            // Auto-add supplier from dropdown when form is submitted
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                // First, check if we have any suppliers selected
                const existingSuppliers = document.querySelectorAll('input[name="supplier_ids[]"]');
                const selectedOption = supplierSelect.options[supplierSelect.selectedIndex];

                let hasSupplier = existingSuppliers.length > 0 || selectedOption.value !== '';

                // If no suppliers at all, prevent submission and show error
                if (!hasSupplier) {
                    e.preventDefault();

                    // Remove any existing error message
                    const existingError = document.getElementById('supplier-error');
                    if (existingError) {
                        existingError.remove();
                    }

                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'supplier-error';
                    errorDiv.className = 'text-red-500 text-sm mt-1';
                    errorDiv.textContent = 'Vui lòng chọn ít nhất một nhà cung cấp';

                    // Insert error message after supplier section
                    const supplierSection = document.querySelector('label[for="supplier"]').parentElement;
                    supplierSection.appendChild(errorDiv);

                    // Scroll to supplier section
                    supplierSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                }

                // Remove error message if validation passes
                const existingError = document.getElementById('supplier-error');
                if (existingError) {
                    existingError.remove();
                }

                // Auto-add supplier from dropdown if selected
                if (selectedOption.value !== '') {
                    // Check if this supplier is already added
                    let alreadyExists = false;

                    for (const existingSupplier of existingSuppliers) {
                        if (existingSupplier.value === selectedOption.value) {
                            alreadyExists = true;
                            break;
                        }
                    }

                    // If not already added, create hidden input to include in form submission
                    if (!alreadyExists) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'supplier_ids[]';
                        hiddenInput.value = selectedOption.value;
                        form.appendChild(hiddenInput);
                    }
                }
            });
        });
    </script>
</body>

</html>
