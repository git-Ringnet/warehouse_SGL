<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo phiếu lắp ráp - SGL</title>
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
                <a href="{{ route('assemblies.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Tạo phiếu lắp ráp</h1>
            </div>
        </header>

        <main class="p-6">
            <form action="{{ route('assemblies.store') }}" method="POST">
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

                <!-- Thông tin phiếu lắp ráp -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tools text-blue-500 mr-2"></i>
                        Thông tin phiếu lắp ráp
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="assembly_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu lắp
                                ráp</label>
                            <input type="text" id="assembly_code" name="assembly_code"
                                value="LR{{ date('Ymd') }}-001"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="assembly_date"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày lắp ráp <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="assembly_date" name="assembly_date" value="{{ date('Y-m-d') }}"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1 required">Sản
                                phẩm <span class="text-red-500">*</span></label>
                            <select id="product_id" name="product_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn sản phẩm --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1 required">Người
                                phụ trách <span class="text-red-500">*</span></label>
                            <select id="assigned_to" name="assigned_to" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người phụ trách --</option>
                                <option value="Nguyễn Văn A">Nguyễn Văn A</option>
                                <option value="Trần Thị B">Trần Thị B</option>
                                <option value="Lê Văn C">Lê Văn C</option>
                                <option value="Phạm Thị D">Phạm Thị D</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="assembly_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea id="assembly_note" name="assembly_note" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nhập ghi chú cho phiếu lắp ráp (nếu có)"></textarea>
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
                            <input type="text" id="component_search"
                                placeholder="Nhập mã hoặc tên vật tư để tìm kiếm..."
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
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Loại vật tư
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên vật tư
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số lượng
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Serial
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ghi chú
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="component_list" class="bg-white divide-y divide-gray-200">
                                <!-- Dữ liệu linh kiện sẽ được thêm vào đây -->
                                <tr id="no_components_row">
                                    <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">
                                        Chưa có linh kiện nào được thêm vào phiếu lắp ráp
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('assemblies.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Lưu phiếu lắp ráp
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const componentSearchInput = document.getElementById('component_search');
            const addComponentBtn = document.getElementById('add_component_btn');
            const componentList = document.getElementById('component_list');
            const noComponentsRow = document.getElementById('no_components_row');
            const submitBtn = document.getElementById('submit-btn');
            const searchResults = document.getElementById('search_results');

            let selectedComponents = [];
            let allMaterials = @json($materials);
            let searchTimeout = null;
            let selectedMaterial = null;

            // Xử lý tìm kiếm linh kiện khi gõ
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
                                        <div class="text-xs text-gray-500">${material.category || ''}</div>
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

            // Xử lý thêm linh kiện
            addComponentBtn.addEventListener('click', function() {
                addSelectedComponent();
            });
            
            // Add selected component function
            function addSelectedComponent() {
                if (selectedMaterial) {
                    // Check if already added
                    if (selectedComponents.some(c => c.id === selectedMaterial.id)) {
                        alert('Vật tư này đã được thêm vào phiếu lắp ráp!');
                        return;
                    }
                    
                    // Add to selected components
                    selectedComponents.push({
                        id: selectedMaterial.id,
                        code: selectedMaterial.code,
                        name: selectedMaterial.name,
                        category: selectedMaterial.category,
                        quantity: 1,
                        serial: '',
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
                        alert('Vui lòng nhập mã hoặc tên vật tư để tìm kiếm!');
                        return;
                    }
                    
                    // Find material in list
                    const foundMaterial = allMaterials.find(m =>
                        m.code.toLowerCase().includes(searchTerm) ||
                        m.name.toLowerCase().includes(searchTerm)
                    );
                    
                    if (!foundMaterial) {
                        alert('Không tìm thấy vật tư phù hợp!');
                        return;
                    }
                    
                    // Check if already added
                    if (selectedComponents.some(c => c.id === foundMaterial.id)) {
                        alert('Vật tư này đã được thêm vào phiếu lắp ráp!');
                        return;
                    }
                    
                    // Add to selected components
                    selectedComponents.push({
                        id: foundMaterial.id,
                        code: foundMaterial.code,
                        name: foundMaterial.name,
                        category: foundMaterial.category,
                        quantity: 1,
                        serial: '',
                        note: ''
                    });
                    
                    // Update UI
                    updateComponentList();
                    componentSearchInput.value = '';
                }
            }

            // Cập nhật danh sách linh kiện
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
                            ${component.code}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${component.category}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${component.name}</td>
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

            // Validation trước khi submit
            document.querySelector('form').addEventListener('submit', function(e) {
                if (selectedComponents.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một vật tư vào phiếu lắp ráp!');
                    return false;
                }
                
                // Kiểm tra số lượng phải lớn hơn 0
                for (const component of selectedComponents) {
                    const quantityInput = document.querySelector(
                        `input[name="components[${selectedComponents.indexOf(component)}][quantity]"]`);
                    if (!quantityInput.value || parseInt(quantityInput.value) < 1) {
                        e.preventDefault();
                        alert('Số lượng phải lớn hơn 0!');
                        quantityInput.focus();
                        return false;
                    }
                }
                
                return true;
            });
        });
    </script>
</body>

</html>
