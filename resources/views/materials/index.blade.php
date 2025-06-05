<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý vật tư - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Quản lý vật tư</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm vật tư..."
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
                    <a href="{{ route('materials.create') }}">
                        <button
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                            <i class="fas fa-plus mr-2"></i> Thêm vật tư
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
                                <i class="fas fa-file-pdf text-red-500 mr-2"></i> Xuất PDF
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
                                Mã vật tư</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên vật tư</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Loại</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Đơn vị</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                title="Tổng số lượng vật tư ở tất cả các kho, dự án, cho thuê, bảo hành, sửa chữa...">
                                <div class="flex items-center">
                                    Tổng vật tư
                                    <i class="fas fa-info-circle ml-1 text-gray-400"></i>
                                </div>
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                title="Số lượng vật tư tồn kho">
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
                        @foreach ($materials as $index => $material)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $material->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->category }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->unit }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="font-medium px-2 py-1 rounded {{ $material->total_quantity > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ number_format($material->total_quantity, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="font-medium px-2 py-1 rounded {{ $material->inventory_quantity > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}"
                                    @php
                                        $warehouseTooltip = '';
                                        if(is_array($material->inventory_warehouses) && in_array('all', $material->inventory_warehouses)) {
                                            $warehouseTooltip = 'Tất cả các kho';
                                        } elseif(is_array($material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                                            $warehouseNames = [];
                                            foreach($material->inventory_warehouses as $warehouseId) {
                                                $warehouse = App\Models\Warehouse::find($warehouseId);
                                                if($warehouse) {
                                                    $warehouseNames[] = $warehouse->name;
                                                }
                                            }
                                            $warehouseTooltip = implode(', ', $warehouseNames);
                                        } else {
                                            $warehouseTooltip = 'Tất cả các kho';
                                        }
                                    @endphp
                                    title="Tính theo: {{ $warehouseTooltip }}">
                                        {{ number_format($material->inventory_quantity, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    <a href="{{ route('materials.show', $material->id) }}">
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                    <a href="{{ route('materials.edit', $material->id) }}">
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                            title="Sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                    <button type="button" onclick="showImagesModal('{{ $material->id }}', '{{ $material->name }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group"
                                        title="Xem hình ảnh">
                                        <i class="fas fa-images text-purple-500 group-hover:text-white"></i>
                                    </button>
                                    <button onclick="openDeleteModal('{{ $material->id }}', '{{ $material->code }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                        title="Xóa">
                                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal xác nhận xóa -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xác nhận xóa</h3>
            <p class="text-gray-700 mb-6">Bạn có chắc chắn muốn xóa vật tư <span id="materialCode"
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
                <h3 class="text-lg font-bold text-gray-900">Hình ảnh vật tư: <span id="materialName"></span></h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeImagesModal()">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="materialImagesContainer" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                <!-- Images will be loaded here via JavaScript -->
            </div>
            <div class="text-center text-gray-500 italic text-sm mb-4 hidden" id="noImagesMessage">
                Vật tư này chưa có hình ảnh nào.
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
        function openDeleteModal(materialId, materialCode) {
            document.getElementById('materialCode').textContent = materialCode;
            document.getElementById('deleteForm').action = `/materials/${materialId}`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Functions for the images modal
        function showImagesModal(materialId, materialName) {
            // Set material name in modal title
            document.getElementById('materialName').textContent = materialName;
            
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
            const container = document.getElementById('materialImagesContainer');
            container.innerHTML = '<div class="flex justify-center items-center h-40 bg-gray-100 rounded-lg animate-pulse col-span-full"><i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i></div>';
            
            // Hide no images message initially
            document.getElementById('noImagesMessage').classList.add('hidden');
            
            // Fetch the images for the material
            fetch(`/api/materials/${materialId}/images`)
                .then(response => response.json())
                .then(data => {
                    container.innerHTML = '';
                    
                    if (data.images && data.images.length > 0) {
                        data.images.forEach((image, index) => {
                            const imageDiv = document.createElement('div');
                            imageDiv.className = 'relative opacity-0 transform translate-y-4';
                            imageDiv.style.transition = 'all 300ms ease';
                            imageDiv.style.transitionDelay = `${index * 50}ms`;
                            
                            imageDiv.innerHTML = `
                                <div class="w-full h-40 border border-gray-200 rounded-lg overflow-hidden">
                                    <img src="${image.url}" alt="Hình ảnh vật tư" class="w-full h-full object-cover">
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
                    console.error('Error fetching material images:', error);
                    container.innerHTML = '<div class="col-span-full text-center text-red-500">Có lỗi xảy ra khi tải hình ảnh. Vui lòng thử lại sau.</div>';
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
