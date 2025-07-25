<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa vật tư - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa vật tư</h1>
                <div class="ml-4 px-2 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    ID: {{ $material->code }}
                </div>
            </div>
            <a href="{{ route('materials.show', $material->id) }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('materials.update', $material->id) }}" method="POST"
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

                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin vật tư</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã
                                    vật tư <span class="text-red-500">*</span></label>
                                <input type="text" id="code" name="code" value="{{ $material->code }}"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên
                                    vật tư <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" value="{{ $material->name }}"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Loại vật tư
                                    <span class="text-red-500">*</span></label>
                                <div class="flex relative">
                                    <select id="category" name="category" required
                                        class="w-full border border-gray-300 rounded-lg rounded-r-none px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Chọn loại vật tư</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category }}"
                                                {{ $material->category == $category ? 'selected' : '' }}>
                                                {{ $category }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" id="clearCategoryBtn"
                                        class="absolute right-12 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden mx-2">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button type="button" id="addCategoryBtn"
                                        class="bg-blue-500 text-white px-3 py-2 rounded-lg rounded-l-none border-l-0 hover:bg-blue-600 transition-colors">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp <span
                                        class="text-red-500">*</span></label>
                                <div class="flex">
                                    <select id="supplier-select"
                                        class="w-full border border-gray-300 rounded-lg rounded-r-none px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Chọn nhà cung cấp</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" id="add-supplier-btn"
                                        class="bg-blue-500 text-white px-3 py-2 rounded-lg rounded-l-none border-l-0 hover:bg-blue-600 transition-colors">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="suppliers-container" class="mt-2">
                                    <!-- Các nhà cung cấp đã liên kết sẽ được thêm vào đây -->
                                    @if ($material->suppliers && count($material->suppliers) > 0)
                                        @foreach ($material->suppliers as $supplier)
                                            <div id="supplier-{{ $supplier->id }}"
                                                class="flex items-center justify-between border border-gray-200 rounded-lg p-2 mb-2">
                                                <input type="hidden" name="supplier_ids[]"
                                                    value="{{ $supplier->id }}">
                                                <div>{{ $supplier->name }}</div>
                                                <button type="button" class="text-red-500 hover:text-red-700"
                                                    onclick="document.getElementById('supplier-{{ $supplier->id }}').remove()">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700 mb-1 required">Đơn
                                    vị<span class="text-red-500">*</span></label>
                                <select id="unit" name="unit" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Chọn đơn vị</option>
                                    <option value="Cái" {{ $material->unit == 'Cái' ? 'selected' : '' }}>Cái
                                    </option>
                                    <option value="Bộ" {{ $material->unit == 'Bộ' ? 'selected' : '' }}>Bộ</option>
                                    <option value="Chiếc" {{ $material->unit == 'Chiếc' ? 'selected' : '' }}>Chiếc
                                    </option>
                                    <option value="Mét" {{ $material->unit == 'Mét' ? 'selected' : '' }}>Mét
                                    </option>
                                    <option value="Cuộn" {{ $material->unit == 'Cuộn' ? 'selected' : '' }}>Cuộn
                                    </option>
                                    <option value="Kg" {{ $material->unit == 'Kg' ? 'selected' : '' }}>Kg</option>
                                </select>
                            </div>
                        </div>

                        <!-- Hình ảnh -->
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
                                    @foreach ($material->images as $image)
                                        <div class="relative" id="existing-image-{{ $image->id }}">
                                            <div class="w-32 h-32 border border-gray-200 rounded-lg overflow-hidden">
                                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                                    alt="{{ $material->name }}" class="w-full h-full object-cover">
                                            </div>
                                            <button type="button"
                                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 delete-existing-image"
                                                data-image-id="{{ $image->id }}">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    @endforeach

                                    <!-- New image previews will be inserted here -->
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $material->notes }}</textarea>
                        </div>

                        <!-- Cách tính tồn kho -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kho dùng để tính tồn
                                kho</label>
                            <div class="space-y-2">
                                @foreach (App\Models\Warehouse::orderBy('name')->where('status', 'active')->where('is_hidden', 0)->get() as $warehouse)
                                    <div class="flex items-center">
                                        <input type="checkbox" id="warehouse_{{ $warehouse->id }}"
                                            name="inventory_warehouses[]" value="{{ $warehouse->id }}"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                            {{ is_array($material->inventory_warehouses) && in_array($warehouse->id, $material->inventory_warehouses) ? 'checked' : '' }}>
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
                                        {{ is_array($material->inventory_warehouses) && in_array('all', $material->inventory_warehouses) ? 'checked' : '' }}>
                                    <label for="warehouse_all" class="ml-2 block text-sm text-gray-700 font-medium">
                                        Tất cả các kho
                                    </label>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Lựa chọn kho để thực hiện đếm số lượng vật tư tồn
                                trong kho đó.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('materials.show', $material->id) }}"
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
                <h3 class="text-lg font-bold text-gray-900">Thêm loại vật tư mới</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" id="closeModalBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addCategoryForm">
                <div class="mb-4">
                    <label for="newCategoryName" class="block text-sm font-medium text-gray-700 mb-1">Tên loại vật tư
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
    <script src="{{ asset('js/material-form.js') }}"></script>
    <script>
        // Initialize the material form
        document.addEventListener('DOMContentLoaded', function() {
            initializeMaterialForm(true); // true = edit form

            // Handle supplier add/remove functionality
            const supplierSelect = document.getElementById('supplier-select');
            const addSupplierBtn = document.getElementById('add-supplier-btn');
            const suppliersContainer = document.getElementById('suppliers-container');

            console.log('Supplier elements (edit):', {
                select: supplierSelect,
                button: addSupplierBtn,
                container: suppliersContainer
            });

            // Add supplier when button is clicked
            addSupplierBtn.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent form submission
                console.log('Add button clicked (edit)', supplierSelect.value);
                if (supplierSelect.value) {
                    addSupplier(supplierSelect.value, supplierSelect.options[supplierSelect.selectedIndex]
                        .text);
                    supplierSelect.value = '';
                }
            });

            // Function to add a supplier to the list
            function addSupplier(value, text) {
                console.log('Adding supplier (edit):', value, text);
                // Check if supplier already exists
                if (document.querySelector(`input[name="supplier_ids[]"][value="${value}"]`)) {
                    console.log('Supplier already exists');
                    return;
                }

                const supplierId = `supplier-${value}`;
                const supplierRow = document.createElement('div');
                supplierRow.id = supplierId;
                supplierRow.className =
                    'flex items-center justify-between border border-gray-200 rounded-lg p-2 mb-2';

                const supplierText = document.createElement('div');
                supplierText.className = 'text-gray-800';
                supplierText.textContent = text;

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'supplier_ids[]';
                hiddenInput.value = value;

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'text-red-500 hover:text-red-700';
                removeButton.innerHTML = '<i class="fas fa-times"></i>';
                removeButton.onclick = function() {
                    document.getElementById(supplierId).remove();
                };

                supplierRow.appendChild(hiddenInput);
                supplierRow.appendChild(supplierText);
                supplierRow.appendChild(removeButton);
                suppliersContainer.appendChild(supplierRow);

                console.log('Supplier added (edit):', supplierId);
            }

            // Handle category clear button
            const categorySelect = document.getElementById('category');
            const clearCategoryBtn = document.getElementById('clearCategoryBtn');

            // Show clear button initially if category is selected
            if (categorySelect.value) {
                clearCategoryBtn.classList.remove('hidden');
            }

            categorySelect.addEventListener('change', function() {
                if (this.value) {
                    clearCategoryBtn.classList.remove('hidden');
                } else {
                    clearCategoryBtn.classList.add('hidden');
                }
            });

            clearCategoryBtn.addEventListener('click', function() {
                const selectedValue = categorySelect.value;

                // Tìm option có value tương ứng và ẩn nó
                const selectedOption = categorySelect.querySelector(`option[value="${selectedValue}"]`);
                if (selectedOption) {
                    selectedOption.style.display = 'none'; // hoặc dùng hidden = true
                }

                categorySelect.value = '';
                clearCategoryBtn.classList.add('hidden');
            });

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

            // Auto-add supplier from dropdown when form is submitted
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                // First, check if we have any suppliers selected
                const existingSuppliers = document.querySelectorAll('input[name="supplier_ids[]"]');

                let hasSupplier = existingSuppliers.length > 0 || supplierSelect.value !== '';

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

                    // Insert error message after supplier container
                    const suppliersContainer = document.getElementById('suppliers-container');
                    suppliersContainer.parentElement.appendChild(errorDiv);

                    // Scroll to supplier section
                    suppliersContainer.parentElement.scrollIntoView({
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
                if (supplierSelect.value !== '') {
                    // Check if this supplier is already added
                    let alreadyExists = false;

                    for (const existingSupplier of existingSuppliers) {
                        if (existingSupplier.value === supplierSelect.value) {
                            alreadyExists = true;
                            break;
                        }
                    }

                    // If not already added, create hidden input to include in form submission
                    if (!alreadyExists) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'supplier_ids[]';
                        hiddenInput.value = supplierSelect.value;
                        form.appendChild(hiddenInput);
                    }
                }
            });
        });
    </script>
</body>

</html>
