<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý hàng hóa - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý hàng hóa</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm hàng hóa..."
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64" />
                    <select
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active">Còn hàng</option>
                        <option value="inactive">Hết hàng</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <a href="#">
                        <button
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                            <i class="fas fa-file-import mr-2"></i> Import Data
                        </button>
                    </a>
                    <a href="#">
                        <button
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                            <i class="fa-solid fa-download mr-2"></i> Tải mẫu Import
                        </button>
                    </a>
                    <a href="{{ route('goods.create') }}">
                        <button
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                            <i class="fas fa-plus mr-2"></i> Thêm hàng hóa
                        </button>
                    </a>
                </div>
            </div>
        </header>
        <main class="p-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif
            <div class="mb-4 flex justify-end mr-4">
                <div class="relative inline-block text-left">
                    <button id="exportDropdownButton" type="button" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors group">
                        <i class="fas fa-download mr-2"></i> Xuất dữ liệu
                        <i class="fas fa-chevron-down ml-2 transition-transform group-hover:transform group-hover:translate-y-0.5"></i>
                    </button>
                    <div id="exportDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden border border-gray-200 overflow-hidden">
                        <div class="py-1">
                            <button id="exportExcelButton" class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-file-excel text-green-500 mr-2"></i> Xuất Excel
                            </button>
                            <button id="exportPdfButton" class="block w-full text-left px-4 py-2.5 text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-file-pdf text-red-500 mr-2"></i> Xuất FDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mã hàng hóa</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên hàng hóa</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Loại</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Đơn vị</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                title="Số lượng hàng hóa tồn kho">
                                <div class="flex items-center">
                                    Tổng tồn kho
                                    <i class="fas fa-info-circle ml-1 text-gray-400"></i>
                                </div>
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">                        
                        @if(count($goods) === 0)
                            @for($i = 0; $i < 5; $i++)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $i + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">HH-{{ str_pad($i+101, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        Hàng hóa mẫu {{ $i+1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Loại {{ $i % 5 + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <span class="font-medium px-2 py-1 rounded bg-green-100 text-green-800"
                                        title="Tính theo: Tất cả các kho">
                                            {{ number_format(rand(5, 50), 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                        <a href="{{ route('goods.show') }}">
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                title="Xem">
                                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <a href="{{ route('goods.edit') }}">
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                title="Sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <button type="button" onclick="showImagesModal('1', 'Hàng hóa mẫu {{ $i+1 }}')"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group"
                                            title="Xem hình ảnh">
                                            <i class="fas fa-images text-purple-500 group-hover:text-white"></i>
                                        </button>
                                        <button onclick="openDeleteModal('1', 'HH-{{ str_pad($i+101, 3, '0', STR_PAD_LEFT) }}')"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                            title="Xóa">
                                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal xác nhận xóa -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xác nhận xóa</h3>
            <p class="text-gray-700 mb-6">Bạn có chắc chắn muốn xóa hàng hóa <span id="goodCode"
                    class="font-semibold"></span>? Hành động này không thể hoàn tác.</p>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeDeleteModal()">
                    Hủy
                </button>
                <form id="deleteForm" action="" method="POST">
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

    <script>
        function openDeleteModal(goodId, goodCode) {
            document.getElementById('goodCode').textContent = goodCode;
            document.getElementById('deleteForm').action = `/goods/${goodId}`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
        
        function showImagesModal(goodId, goodName) {
            const modal = document.getElementById('imagesModal');
            const imagesContainer = document.getElementById('goodImagesContainer');
            const noImagesMessage = document.getElementById('noImagesMessage');
            
            // Set good name in modal title
            document.getElementById('goodName').textContent = goodName;
            
            // Clear existing images
            imagesContainer.innerHTML = '';
            
            // For demo purposes, add some sample images
            const sampleImageCount = Math.floor(Math.random() * 5); // 0-4 images
            
            if (sampleImageCount === 0) {
                noImagesMessage.classList.remove('hidden');
            } else {
                noImagesMessage.classList.add('hidden');
                
                for (let i = 0; i < sampleImageCount; i++) {
                    // Create image container
                    const imageDiv = document.createElement('div');
                    imageDiv.className = 'relative border border-gray-200 rounded-lg overflow-hidden';
                    imageDiv.style.height = '150px';
                    
                    // Create image element with placeholder
                    const img = document.createElement('img');
                    img.src = `https://source.unsplash.com/random/300x200?product,item&sig=${goodId}-${i}`;
                    img.className = 'w-full h-full object-cover';
                    img.alt = `Hình ảnh ${i+1} của ${goodName}`;
                    
                    imageDiv.appendChild(img);
                    imagesContainer.appendChild(imageDiv);
                }
            }
            
            // Show modal with animation
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.add('opacity-100');
                modal.querySelector('.bg-white').classList.remove('scale-95', 'opacity-0');
            }, 10);
        }

        function closeImagesModal() {
            const modal = document.getElementById('imagesModal');
            const modalContent = modal.querySelector('.bg-white');
            
            modalContent.classList.add('scale-95', 'opacity-0');
            modal.classList.remove('opacity-100');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
        
        // Export dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const exportDropdownButton = document.getElementById('exportDropdownButton');
            const exportDropdown = document.getElementById('exportDropdown');
            
            exportDropdownButton.addEventListener('click', function() {
                exportDropdown.classList.toggle('hidden');
            });
            
            // Close the dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!exportDropdownButton.contains(event.target) && !exportDropdown.contains(event.target)) {
                    exportDropdown.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html> 