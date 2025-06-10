<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thành phẩm - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý thành phẩm</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm thành phẩm..."
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64" />
                    <select
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                        <option value="">Bộ lọc</option>
                    </select>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <button
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                        <i class="fas fa-file-import mr-2"></i> Import Data
                    </button>
                    <a href="#">
                        <button
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                            <i class="fa-solid fa-download mr-2"></i> Tải mẫu Import
                        </button>
                    </a>
                    <a href="{{ route('products.create') }}">
                        <button
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                            <i class="fas fa-plus mr-2"></i> Thêm thành phẩm
                        </button>
                    </a>
                </div>
            </div>
        </header>
        @if (session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        @if (session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <div class="mb-4 flex justify-end mr-4">
                    <div class="relative inline-block text-left">
                        <button id="exportDropdownButton" type="button"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors group">
                            <i class="fas fa-download mr-2"></i> Xuất dữ liệu
                            <i
                                class="fas fa-chevron-down ml-2 transition-transform group-hover:transform group-hover:translate-y-0.5"></i>
                        </button>
                        <div id="exportDropdown"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden border border-gray-200 overflow-hidden">
                            <div class="py-1">
                                <button id="exportExcelButton"
                                    class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-file-excel text-green-500 mr-2"></i> Xuất Excel
                                </button>
                                <button id="exportPdfButton"
                                    class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i> Xuất FDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                Mã SP</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                Tên thành phẩm</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                Tồn kho</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">
                                Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($products as $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    @if ($product->model)
                                        <div class="text-xs text-gray-500">Model: {{ $product->model }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span
                                            class="px-2.5 py-1 rounded-md text-sm font-medium 
                                            @if (87 > 50) bg-green-100 text-green-800
                                            @elseif(87 > 20) bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            87
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium space-x-1">
                                    <div class="flex justify-start space-x-2">
                                    <a href="{{ route('products.show', $product->id) }}">
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                    <a href="{{ route('products.edit', $product->id) }}">
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                            title="Sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                        <button type="button"
                                            onclick="showImagesModal('{{ $product->id }}', '{{ $product->name }}')"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group"
                                            title="Xem hình ảnh">
                                            <i class="fas fa-images text-purple-500 group-hover:text-white"></i>
                                        </button>
                                        <button
                                            onclick="openDeleteModal('{{ $product->id }}', '{{ $product->code }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                        title="Xóa">
                                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                    </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        <!-- No products -->
                        @if ($products->isEmpty())
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500 whitespace-nowrap">
                                    Không có thành phẩm nào
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal backdrop -->
    <div id="modalBackdrop" class="fixed inset-0 bg-black opacity-50 z-40 hidden"></div>

    <!-- Delete confirmation modal -->
    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4 transform transition-transform scale-95 opacity-0"
            id="deleteModalContent">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Xác nhận xóa thành phẩm</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Bạn có chắc chắn muốn xóa thành phẩm "<span id="productNameToDelete" class="font-medium"></span>"?
                    Hành động này không thể hoàn tác.
                </p>
                <div class="flex justify-center space-x-3">
                    <button type="button" id="cancelDeleteBtn"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-md">
                        Hủy bỏ
                    </button>
                    <form id="deleteProductForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                            Xóa thành phẩm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Images modal -->
    <div id="imagesModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-3xl w-full mx-4 transform transition-transform scale-95 opacity-0"
            id="imagesModalContent">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Hình ảnh thành phẩm: <span id="productNameInModal"
                        class="font-normal"></span></h3>
                <button id="closeImagesModal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mt-4">
                <div id="productImagesContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <!-- Images will be loaded here -->
                </div>
                <div id="noImagesMessage" class="text-center py-8 text-gray-500 hidden">
                    <i class="fas fa-image text-4xl mb-2"></i>
                    <p>thành phẩm này chưa có hình ảnh</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Delete confirmation modal
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-product-btn');
            const deleteModal = document.getElementById('deleteModal');
            const deleteModalContent = document.getElementById('deleteModalContent');
            const modalBackdrop = document.getElementById('modalBackdrop');
            const productNameToDelete = document.getElementById('productNameToDelete');
            const deleteProductForm = document.getElementById('deleteProductForm');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

            // Open delete modal
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    const productName = this.getAttribute('data-product-name');

                    deleteProductForm.action = `/products/${productId}`;
                    productNameToDelete.textContent = productName;

                    // Show modal with animation
                    modalBackdrop.classList.remove('hidden');
                    deleteModal.classList.remove('hidden');

                    // Add animation
                    setTimeout(() => {
                        modalBackdrop.classList.add('opacity-50');
                        deleteModalContent.classList.remove('scale-95', 'opacity-0');
                        deleteModalContent.classList.add('scale-100', 'opacity-100');
                    }, 10);
                });
            });

            // Close delete modal
            function closeDeleteModal() {
                // Remove with animation
                deleteModalContent.classList.remove('scale-100', 'opacity-100');
                deleteModalContent.classList.add('scale-95', 'opacity-0');
                modalBackdrop.classList.remove('opacity-50');

                setTimeout(() => {
                    deleteModal.classList.add('hidden');
                    modalBackdrop.classList.add('hidden');
                }, 300);
            }

            cancelDeleteBtn.addEventListener('click', closeDeleteModal);

            // Images modal functionality
            const viewImagesButtons = document.querySelectorAll('.view-images-btn');
            const imagesModal = document.getElementById('imagesModal');
            const imagesModalContent = document.getElementById('imagesModalContent');
            const closeImagesModal = document.getElementById('closeImagesModal');
            const productNameInModal = document.getElementById('productNameInModal');
            const productImagesContainer = document.getElementById('productImagesContainer');
            const noImagesMessage = document.getElementById('noImagesMessage');

            // Open images modal
            viewImagesButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    const productName = this.getAttribute('data-product-name');

                    productNameInModal.textContent = productName;

                    // Clear previous images
                    productImagesContainer.innerHTML = '';

                    // Show loading
                    productImagesContainer.innerHTML = `
                        <div class="col-span-full flex justify-center items-center py-12">
                            <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-l-2 border-blue-500"></div>
                        </div>
                    `;

                    // Show modal with animation
                    modalBackdrop.classList.remove('hidden');
                    imagesModal.classList.remove('hidden');

                    setTimeout(() => {
                        modalBackdrop.classList.add('opacity-50');
                        imagesModalContent.classList.remove('scale-95', 'opacity-0');
                        imagesModalContent.classList.add('scale-100', 'opacity-100');
                    }, 10);

                    // For the UI demo, simulate loading images
                    setTimeout(() => {
                        productImagesContainer.innerHTML = '';

                        // Sample images (in a real app, these would be loaded from the server)
                        const sampleImages = [
                            'https://via.placeholder.com/300',
                            'https://via.placeholder.com/300',
                            'https://via.placeholder.com/300',
                            'https://via.placeholder.com/300'
                        ];

                        if (sampleImages.length > 0) {
                            sampleImages.forEach(imageUrl => {
                                const imageDiv = document.createElement('div');
                                imageDiv.className = 'relative group';
                                imageDiv.innerHTML = `
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <img src="${imageUrl}" alt="${productName}" class="w-full h-48 object-cover">
                                    </div>
                                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <a href="${imageUrl}" target="_blank" class="text-white bg-blue-500 rounded-full p-2 hover:bg-blue-600 transition-colors">
                                            <i class="fas fa-expand-arrows-alt"></i>
                                        </a>
                                    </div>
                                `;
                                productImagesContainer.appendChild(imageDiv);
                            });

                            noImagesMessage.classList.add('hidden');
                        } else {
                            noImagesMessage.classList.remove('hidden');
                        }
                    }, 800);
                });
            });

            // Close images modal
            closeImagesModal.addEventListener('click', function() {
                // Remove with animation
                imagesModalContent.classList.remove('scale-100', 'opacity-100');
                imagesModalContent.classList.add('scale-95', 'opacity-0');
                modalBackdrop.classList.remove('opacity-50');

                setTimeout(() => {
                    imagesModal.classList.add('hidden');
                    modalBackdrop.classList.add('hidden');
                }, 300);
            });

            // Close modals when clicking outside
            modalBackdrop.addEventListener('click', function() {
                if (!deleteModal.classList.contains('hidden')) {
                    closeDeleteModal();
                } else if (!imagesModal.classList.contains('hidden')) {
                    closeImagesModal.click();
                }
            });
        });
        // Export dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const exportDropdownButton = document.getElementById('exportDropdownButton');
            const exportDropdown = document.getElementById('exportDropdown');
            const exportExcelButton = document.getElementById('exportExcelButton');
            const exportPdfButton = document.getElementById('exportPdfButton');
            
            // Toggle dropdown on button click
            exportDropdownButton.addEventListener('click', function(e) {
                e.stopPropagation();
                if (exportDropdown.classList.contains('hidden')) {
                    // Show dropdown with animation
                    exportDropdown.classList.remove('hidden');
                    exportDropdown.style.opacity = '0';
                    exportDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        exportDropdown.style.transition = 'opacity 150ms ease-in-out, transform 150ms ease-in-out';
                        exportDropdown.style.opacity = '1';
                        exportDropdown.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    // Hide dropdown with animation
                    exportDropdown.style.opacity = '0';
                    exportDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        exportDropdown.classList.add('hidden');
                        exportDropdown.style.transition = '';
                    }, 150);
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                if (!exportDropdown.classList.contains('hidden')) {
                    // Hide dropdown with animation
                    exportDropdown.style.opacity = '0';
                    exportDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        exportDropdown.classList.add('hidden');
                        exportDropdown.style.transition = '';
                    }, 150);
                }
            });
            
            // Prevent dropdown from closing when clicking inside it
            exportDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Handle export buttons
            exportExcelButton.addEventListener('click', function() {
                // Add export Excel functionality here
                window.location.href = '/materials/export/excel';
                exportDropdown.classList.add('hidden');
            });
            
            exportPdfButton.addEventListener('click', function() {
                // Add export PDF functionality here
                window.location.href = '/materials/export/pdf';
                exportDropdown.classList.add('hidden');
            });
        });
    </script>
</body>

</html>
