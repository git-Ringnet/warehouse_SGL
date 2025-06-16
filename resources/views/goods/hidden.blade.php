<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hàng hóa đã ẩn - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <style>
        #detailImage {
            cursor: zoom-in;
            transition: transform 0.3s ease;
        }
        #detailImage.zoomed {
            cursor: zoom-out;
        }
        .z-60 {
            z-index: 60;
        }
        .z-70 {
            z-index: 70;
        }
    </style>
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('goods.index') }}" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Hàng hóa đã ẩn</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('goods.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-list mr-2"></i> Danh sách chính
                </a>
                <a href="{{ route('goodsdeleted') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash mr-2"></i> Đã xóa
                </a>
            </div>
        </header>
        <main class="p-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($goods->count() > 0)
                <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã hàng hóa</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên hàng hóa</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tổng tồn kho</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($goods as $index => $good)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $good->code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $good->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $good->category }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $good->unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <span class="font-medium px-2 py-1 rounded bg-yellow-100 text-yellow-800">
                                            {{ number_format($good->inventory_quantity, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                        <a href="{{ route('goods.show', $good->id) }}">
                                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <form action="{{ route('goods.restore', $good->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group" title="Khôi phục">
                                                <i class="fas fa-undo text-green-500 group-hover:text-white"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-md p-8 text-center">
                    <i class="fas fa-eye-slash text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Không có hàng hóa nào đã ẩn</h3>
                    <p class="text-gray-500">Tất cả hàng hóa hiện đang hiển thị trong danh sách chính.</p>
                </div>
            @endif
        </main>
    </div>

    <!-- Modal xác nhận xóa khi có tồn kho -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Không thể xóa</h3>
            <p class="text-red-700 mb-6">Không thể xóa hàng hóa <span id="goodCode" class="font-semibold"></span> vì còn tồn kho: <span id="inventoryQuantity" class="font-semibold"></span></p>
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
    <div id="deleteZeroInventoryModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xác nhận thao tác</h3>
            <p class="text-gray-700 mb-6">Thao tác xóa có thể làm mất dữ liệu. Bạn muốn xác nhận ngừng sử dụng và ẩn hạng mục này thay cho việc xóa?</p>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeDeleteZeroInventoryModal()">
                    Hủy
                </button>
                <form id="hideForm" action="" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="action" value="hide">
                    <button type="submit"
                        class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors">
                        Có (Ẩn hạng mục)
                    </button>
                </form>
                <form id="deleteForm" action="" method="POST" class="inline">
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

    <!-- Modal xem hình ảnh -->
    <div id="imagesModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-4xl w-full transform scale-95 opacity-0 transition-all duration-300">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Hình ảnh hàng hóa: <span id="goodName"></span></h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeImagesModal()">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="goodImagesContainer" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                <!-- Images will be loaded here via JavaScript -->
            </div>
            <div class="text-center text-gray-500 italic text-sm mb-4 hidden" id="noImagesMessage">
                Hàng hóa này chưa có hình ảnh nào.
            </div>
            <div class="flex justify-end">
                <button type="button" 
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeImagesModal()">
                    Đóng
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal xem ảnh chi tiết -->
    <div id="imageDetailModal" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-60 hidden">
        <div class="relative max-w-screen-lg max-h-screen-lg w-full h-full flex items-center justify-center p-4">
            <!-- Close button -->
            <button type="button" onclick="closeImageDetailModal()" 
                class="absolute top-4 right-4 text-white hover:text-gray-300 text-3xl z-70 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
            
            <!-- Previous button -->
            <button type="button" onclick="previousImage()" id="prevImageBtn"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 text-3xl z-70 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <!-- Next button -->
            <button type="button" onclick="nextImage()" id="nextImageBtn"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 text-3xl z-70 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Image container -->
            <div class="relative max-w-full max-h-full flex items-center justify-center">
                <img id="detailImage" src="" alt="Chi tiết hình ảnh" 
                    class="max-w-full max-h-full object-contain rounded-lg shadow-lg transition-transform duration-300"
                    onclick="toggleImageZoom()">
            </div>
            
            <!-- Image info -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg">
                <span id="imageCounter">1 / 1</span>
                <span class="mx-2">•</span>
                <span class="text-sm">Nhấp vào ảnh để zoom</span>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(goodId, goodCode, inventoryQuantity) {
            if (inventoryQuantity > 0) {
                // Show inventory warning modal
                document.getElementById('goodCode').textContent = goodCode;
                document.getElementById('inventoryQuantity').textContent = new Intl.NumberFormat('vi-VN').format(inventoryQuantity);
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
        
        function showImagesModal(goodId, goodName) {
            // Set good name in modal title
            document.getElementById('goodName').textContent = goodName;
            
            // Show the modal with animation
            const modal = document.getElementById('imagesModal');
            const modalContent = modal.querySelector('.bg-white');
            
            // First, display the modal but with 0 opacity
            modal.classList.remove('hidden');
            setTimeout(() => {
                // Then animate in
                modal.style.opacity = '1';
                modalContent.style.opacity = '1';
                modalContent.style.transform = 'scale(1)';
            }, 50);
            
            // Reset the container and add loading spinner
            const container = document.getElementById('goodImagesContainer');
            container.innerHTML = '<div class="flex justify-center items-center h-40 bg-gray-100 rounded-lg animate-pulse col-span-full"><i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i></div>';
            
            // Hide no images message initially
            document.getElementById('noImagesMessage').classList.add('hidden');
            
            // Fetch the images for the good
            fetch(`/api/goods/${goodId}/images`)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API response:', data);
                    container.innerHTML = '';
                    
                    if (data.error) {
                        container.innerHTML = '<div class="col-span-full text-center text-red-500">Lỗi: ' + data.message + '</div>';
                        return;
                    }
                    
                    if (data.images && data.images.length > 0) {
                        data.images.forEach((image, index) => {
                            const imageDiv = document.createElement('div');
                            imageDiv.className = 'relative opacity-0 transform translate-y-4';
                            imageDiv.style.transition = 'all 300ms ease';
                            imageDiv.style.transitionDelay = `${index * 50}ms`;
                            
                            imageDiv.innerHTML = `
                                <div class="w-full h-40 border border-gray-200 rounded-lg overflow-hidden cursor-pointer hover:opacity-75 transition-opacity" onclick="openImageDetailModal(${index}, '${image.url}')">
                                    <img src="${image.url}" alt="Hình ảnh hàng hóa" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<div class=\\'flex items-center justify-center h-full bg-gray-100 text-gray-500\\'>Không thể tải ảnh</div>'">
                                </div>
                            `;
                            
                            container.appendChild(imageDiv);
                            
                            // Trigger animation after a small delay
                            setTimeout(() => {
                                imageDiv.style.opacity = '1';
                                imageDiv.style.transform = 'translateY(0)';
                            }, 10);
                        });
                    } else {
                        document.getElementById('noImagesMessage').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching good images:', error);
                    container.innerHTML = '<div class="col-span-full text-center text-red-500">Có lỗi xảy ra khi tải hình ảnh: ' + error.message + '</div>';
                });
        }

        function closeImagesModal() {
            const modal = document.getElementById('imagesModal');
            const modalContent = modal.querySelector('.bg-white');
            
            // Animate out
            modal.style.opacity = '0';
            modalContent.style.opacity = '0';
            modalContent.style.transform = 'scale(0.95)';
            
            // Wait for animation to finish before hiding
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
        
        // Variables for image detail modal
        let currentImageIndex = 0;
        let currentImages = [];
        let isImageZoomed = false;

        // Functions for the image detail modal
        function openImageDetailModal(index, imageUrl) {
            // Store current images and index
            currentImageIndex = index;
            currentImages = [];
            
            // Get all images from the current modal
            const imageElements = document.querySelectorAll('#goodImagesContainer img');
            imageElements.forEach(img => {
                currentImages.push(img.src);
            });
            
            // Show the detail modal
            const modal = document.getElementById('imageDetailModal');
            const detailImage = document.getElementById('detailImage');
            
            detailImage.src = imageUrl;
            updateImageCounter();
            updateNavigationButtons();
            
            modal.classList.remove('hidden');
            
            // Reset zoom state
            isImageZoomed = false;
            detailImage.style.transform = 'scale(1)';
            detailImage.classList.remove('zoomed');
        }

        function closeImageDetailModal() {
            const modal = document.getElementById('imageDetailModal');
            modal.classList.add('hidden');
            
            // Reset zoom state
            isImageZoomed = false;
            const detailImage = document.getElementById('detailImage');
            detailImage.style.transform = 'scale(1)';
            detailImage.classList.remove('zoomed');
        }

        function previousImage() {
            if (currentImages.length <= 1) return;
            
            currentImageIndex = currentImageIndex > 0 ? currentImageIndex - 1 : currentImages.length - 1;
            
            const detailImage = document.getElementById('detailImage');
            detailImage.src = currentImages[currentImageIndex];
            
            updateImageCounter();
            updateNavigationButtons();
            
            // Reset zoom
            isImageZoomed = false;
            detailImage.style.transform = 'scale(1)';
            detailImage.classList.remove('zoomed');
        }

        function nextImage() {
            if (currentImages.length <= 1) return;
            
            currentImageIndex = currentImageIndex < currentImages.length - 1 ? currentImageIndex + 1 : 0;
            
            const detailImage = document.getElementById('detailImage');
            detailImage.src = currentImages[currentImageIndex];
            
            updateImageCounter();
            updateNavigationButtons();
            
            // Reset zoom
            isImageZoomed = false;
            detailImage.style.transform = 'scale(1)';
            detailImage.classList.remove('zoomed');
        }

        function updateImageCounter() {
            const counter = document.getElementById('imageCounter');
            counter.textContent = `${currentImageIndex + 1} / ${currentImages.length}`;
        }

        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevImageBtn');
            const nextBtn = document.getElementById('nextImageBtn');
            
            if (currentImages.length <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';
            }
        }

        function toggleImageZoom() {
            const detailImage = document.getElementById('detailImage');
            
            if (isImageZoomed) {
                detailImage.style.transform = 'scale(1)';
                detailImage.classList.remove('zoomed');
            } else {
                detailImage.style.transform = 'scale(1.5)';
                detailImage.classList.add('zoomed');
            }
            
            isImageZoomed = !isImageZoomed;
        }
        
        // Add keyboard navigation for image detail modal
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('imageDetailModal');
            
            if (!modal.classList.contains('hidden')) {
                switch(e.key) {
                    case 'Escape':
                        closeImageDetailModal();
                        break;
                    case 'ArrowLeft':
                        previousImage();
                        break;
                    case 'ArrowRight':
                        nextImage();
                        break;
                    case ' ':
                        e.preventDefault();
                        toggleImageZoom();
                        break;
                }
            }
        });
    </script>
</body>

</html> 