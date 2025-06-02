<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu lắp ráp - SGL</title>
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
                <a href="{{ asset('assemble/detail') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu lắp ráp</h1>
            </div>
        </header>

        <main class="p-6">
            <form action="#" method="POST">
                @csrf
                @method('PUT')

                <!-- Thông tin phiếu lắp ráp -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tools text-blue-500 mr-2"></i>
                        Thông tin phiếu lắp ráp
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="assembly_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu lắp ráp</label>
                            <input type="text" id="assembly_code" name="assembly_code" value="LR001" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="assembly_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày lắp ráp <span class="text-red-500">*</span></label>
                            <input type="date" id="assembly_date" name="assembly_date" value="2023-06-01" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1 required">Sản phẩm <span class="text-red-500">*</span></label>
                            <select id="product_id" name="product_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn sản phẩm --</option>
                                <option value="1" selected>Radio SPA Pro</option>
                                <option value="2">Radio SPA Lite</option>
                                <option value="3">Radio SPA Mini</option>
                                <option value="4">Radio SPA Plus</option>
                                <option value="5">Radio SPA Ultra</option>
                            </select>
                        </div>
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1 required">Người phụ trách <span class="text-red-500">*</span></label>
                            <select id="assigned_to" name="assigned_to" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người phụ trách --</option>
                                <option value="1" selected>Nguyễn Văn A</option>
                                <option value="2">Trần Thị B</option>
                                <option value="3">Lê Văn C</option>
                                <option value="4">Phạm Thị D</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="assembly_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="assembly_note" name="assembly_note" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">Lắp ráp thiết bị mới theo đơn đặt hàng ABC123</textarea>
                    </div>
                </div>

                <!-- Danh sách linh kiện -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-microchip text-blue-500 mr-2"></i>
                        Danh sách linh kiện sử dụng
                    </h2>

                    <!-- Tìm kiếm linh kiện -->
                    <div class="mb-4">
                        <div class="relative">
                            <input type="text" id="component_search" placeholder="Nhập serial hoặc tên linh kiện để tìm kiếm..."
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <button type="button" id="add_component_btn"
                                class="absolute inset-y-0 right-0 px-3 bg-blue-500 text-white rounded-r-lg hover:bg-blue-600 transition-colors">
                                Thêm
                            </button>
                            <!-- Dropdown results -->
                            <div id="search_results" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg hidden max-h-60 overflow-y-auto">
                                <!-- Results will be populated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Bảng linh kiện đã chọn -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Loại linh kiện
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên linh kiện
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số lượng
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Serial
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ghi chú
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="component_list" class="bg-white divide-y divide-gray-200">
                                <!-- Sản phẩm hiện tại -->
                                <tr class="component-row">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <input type="hidden" name="components[0][id]" value="1">
                                        SN001
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bộ xử lý</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">CPU Intel i5</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="number" min="1" name="components[0][quantity]" value="1"
                                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="text" name="components[0][serial]" value="SN001"
                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Nhập serial">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="text" name="components[0][note]" value=""
                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Ghi chú">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button type="button" class="text-red-500 hover:text-red-700 delete-component" data-index="0">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="component-row">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <input type="hidden" name="components[1][id]" value="2">
                                        SN002
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bộ nhớ</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM 8GB DDR4</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="number" min="1" name="components[1][quantity]" value="1"
                                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="text" name="components[1][serial]" value="SN002"
                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Nhập serial">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="text" name="components[1][note]" value=""
                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Ghi chú">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button type="button" class="text-red-500 hover:text-red-700 delete-component" data-index="1">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="component-row">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <input type="hidden" name="components[2][id]" value="3">
                                        SN003
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bộ nhớ</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">RAM 8GB DDR4</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="number" min="1" name="components[2][quantity]" value="1"
                                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="text" name="components[2][serial]" value="SN003"
                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Nhập serial">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="text" name="components[2][note]" value=""
                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Ghi chú">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button type="button" class="text-red-500 hover:text-red-700 delete-component" data-index="2">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Hàng "không có linh kiện" -->
                                <tr id="no_components_row" style="display: none;">
                                    <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        Chưa có linh kiện nào được thêm vào phiếu lắp ráp
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ asset('assemble/detail') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Cập nhật
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dữ liệu mẫu cho linh kiện
            const sampleComponents = [
                { id: 1, code: 'VT001', serial: 'SN001', type: 'Bộ xử lý', name: 'CPU Intel i5', category: 'CPU' },
                { id: 2, code: 'VT002', serial: 'SN002', type: 'Bộ nhớ', name: 'RAM 8GB DDR4', category: 'RAM' },
                { id: 3, code: 'VT003', serial: 'SN003', type: 'Bộ nhớ', name: 'RAM 8GB DDR4', category: 'RAM' },
                { id: 4, code: 'VT004', serial: 'SN004', type: 'Lưu trữ', name: 'SSD 256GB', category: 'SSD' },
                { id: 5, code: 'VT005', serial: 'SN005', type: 'Nguồn', name: 'Nguồn 400W', category: 'PSU' },
                { id: 6, code: 'VT006', serial: 'SN006', type: 'Màn hình', name: 'LCD 7 inch', category: 'Display' },
                { id: 7, code: 'VT007', serial: 'SN007', type: 'Bàn phím', name: 'Bàn phím 4x4', category: 'Input' },
                { id: 8, code: 'VT008', serial: 'SN008', type: 'Anten', name: 'Anten 5G', category: 'Antenna' },
                { id: 9, code: 'VT009', serial: 'SN009', type: 'Bo mạch', name: 'Mạch khuếch đại', category: 'PCB' },
                { id: 10, code: 'VT010', serial: 'SN010', type: 'Pin', name: 'Pin Lithium 5000mAh', category: 'Battery' }
            ];
            
            // Khởi tạo mảng linh kiện đã chọn
            let selectedComponents = [
                { id: 1, code: 'VT001', serial: 'SN001', type: 'Bộ xử lý', name: 'CPU Intel i5', quantity: 1, note: '' },
                { id: 2, code: 'VT002', serial: 'SN002', type: 'Bộ nhớ', name: 'RAM 8GB DDR4', quantity: 1, note: '' },
                { id: 3, code: 'VT003', serial: 'SN003', type: 'Bộ nhớ', name: 'RAM 8GB DDR4', quantity: 1, note: '' }
            ];
            
            // Xử lý tìm kiếm linh kiện khi gõ
            const componentSearchInput = document.getElementById('component_search');
            const addComponentBtn = document.getElementById('add_component_btn');
            const componentList = document.getElementById('component_list');
            const noComponentsRow = document.getElementById('no_components_row');
            const searchResults = document.getElementById('search_results');
            let searchTimeout = null;
            let selectedMaterial = null;
            
            componentSearchInput.addEventListener('input', function() {
                const searchTerm = componentSearchInput.value.trim().toLowerCase();
                
                // Clear any existing timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                
                // Set a timeout to avoid too many searches while typing
                searchTimeout = setTimeout(() => {
                    if (searchTerm.length < 1) {
                        searchResults.classList.add('hidden');
                        return;
                    }
                    
                    // Show loading indicator
                    searchResults.innerHTML = '<div class="p-2 text-gray-500">Đang tìm kiếm...</div>';
                    searchResults.classList.remove('hidden');
                    
                    // Call API to search materials
                    fetch(`{{ route('materials.search') }}?term=${encodeURIComponent(searchTerm)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                searchResults.innerHTML = `<div class="p-2 text-red-500">Lỗi: ${data.message}</div>`;
                                console.error('Search error:', data.message);
                                return;
                            }
                            
                            // If data is wrapped in a success object
                            const materials = Array.isArray(data) ? data : (data.data || []);
                            
                            if (materials.length > 0) {
                                searchResults.innerHTML = '';
                                materials.forEach(material => {
                                    const resultItem = document.createElement('div');
                                    resultItem.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                                    resultItem.innerHTML = `
                                        <div class="font-medium">${material.code}: ${material.name}</div>
                                        <div class="text-xs text-gray-500">${material.category || ''} ${material.serial ? '| ' + material.serial : ''}</div>
                                    `;
                                    
                                    // Handle click on search result
                                    resultItem.addEventListener('click', function() {
                                        selectedMaterial = material;
                                        componentSearchInput.value = material.code + ' - ' + material.name;
                                        searchResults.classList.add('hidden');
                                    });
                                    
                                    searchResults.appendChild(resultItem);
                                });
                            } else {
                                searchResults.innerHTML = '<div class="p-2 text-gray-500">Không tìm thấy vật tư phù hợp</div>';
                            }
                        })
                        .catch(error => {
                            console.error('Error searching materials:', error);
                            searchResults.innerHTML = '<div class="p-2 text-red-500">Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại sau!</div>';
                        });
                }, 300);
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!componentSearchInput.contains(event.target) && !searchResults.contains(event.target)) {
                    searchResults.classList.add('hidden');
                }
            });
            
            addComponentBtn.addEventListener('click', function() {
                addSelectedComponent();
            });
            
            // Add selected component function
            function addSelectedComponent() {
                if (selectedMaterial) {
                    // Check if already added
                    if (selectedComponents.some(c => c.id === selectedMaterial.id)) {
                        alert('Linh kiện này đã được thêm vào phiếu lắp ráp!');
                        return;
                    }
                    
                    // Add to selected components
                    selectedComponents.push({
                        id: selectedMaterial.id,
                        code: selectedMaterial.code,
                        name: selectedMaterial.name,
                        type: selectedMaterial.type,
                        serial: selectedMaterial.serial,
                        quantity: 1,
                        note: ''
                    });
                    
                    // Update UI
                    updateComponentList();
                    componentSearchInput.value = '';
                    selectedMaterial = null;
                    searchResults.classList.add('hidden');
                } else {
                    const searchTerm = componentSearchInput.value.trim().toLowerCase();
                    
                    if (!searchTerm) {
                        alert('Vui lòng nhập serial hoặc tên linh kiện để tìm kiếm!');
                        return;
                    }
                    
                    // Tìm linh kiện trong dữ liệu mẫu
                    const foundComponent = sampleComponents.find(c => 
                        c.serial.toLowerCase().includes(searchTerm) || 
                        c.name.toLowerCase().includes(searchTerm)
                    );
                    
                    if (!foundComponent) {
                        alert('Không tìm thấy linh kiện phù hợp!');
                        return;
                    }
                    
                    // Kiểm tra xem linh kiện đã được thêm chưa
                    if (selectedComponents.some(c => c.id === foundComponent.id)) {
                        alert('Linh kiện này đã được thêm vào phiếu lắp ráp!');
                        return;
                    }
                    
                    // Thêm linh kiện vào danh sách
                    selectedComponents.push({
                        id: foundComponent.id,
                        code: foundComponent.code,
                        type: foundComponent.type,
                        name: foundComponent.name,
                        serial: foundComponent.serial,
                        quantity: 1,
                        note: ''
                    });
                    
                    // Cập nhật giao diện
                    updateComponentList();
                    componentSearchInput.value = '';
                }
            }
            
            function updateComponentList() {
                // Ẩn thông báo "không có linh kiện"
                if (selectedComponents.length > 0) {
                    noComponentsRow.style.display = 'none';
                } else {
                    noComponentsRow.style.display = '';
                }
                
                // Xóa các hàng linh kiện hiện tại (trừ hàng thông báo)
                const componentRows = document.querySelectorAll('.component-row');
                componentRows.forEach(row => row.remove());
                
                // Thêm hàng cho mỗi linh kiện đã chọn
                selectedComponents.forEach((component, index) => {
                    const row = document.createElement('tr');
                    row.className = 'component-row';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="hidden" name="components[${index}][id]" value="${component.id}">
                            ${component.code || ''}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${component.type || ''}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${component.name || ''}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="number" min="1" name="components[${index}][quantity]" value="${component.quantity || 1}"
                                class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="text" name="components[${index}][serial]" value="${component.serial || ''}"
                                class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Nhập serial">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="text" name="components[${index}][note]" value="${component.note || ''}"
                                class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ghi chú">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-500 hover:text-red-700 delete-component" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    
                    componentList.insertBefore(row, noComponentsRow);
                    
                    // Thêm event listener để xóa linh kiện
                    row.querySelector('.delete-component').addEventListener('click', function() {
                        selectedComponents.splice(index, 1);
                        updateComponentList();
                    });
                });
            }
            
            // Khởi tạo sự kiện cho các nút xóa ban đầu
            const deleteButtons = document.querySelectorAll('.delete-component');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    selectedComponents.splice(index, 1);
                    updateComponentList();
                });
            });
        });
    </script>
</body>

</html> 