<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa kho hàng - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supplier-dropdown.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa kho hàng</h1>
                <div class="ml-4 px-2 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                    ID: {{ $warehouse->code }}
                </div>
            </div>
            <a href="{{ route('warehouses.show', $warehouse->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST">
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
                    
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin kho hàng</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-1 required">Mã kho</label>
                                <input type="text" id="code" name="code" value="{{ $warehouse->code }}" required 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên kho</label>
                                <input type="text" id="name" name="name" value="{{ $warehouse->name }}" required 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="manager" class="block text-sm font-medium text-gray-700 mb-1 required">Người quản lý</label>
                                <div class="relative">
                                    <input type="text" id="manager_search" 
                                           placeholder="Tìm kiếm người quản lý..." 
                                           value="{{ $warehouse->managerEmployee ? $warehouse->managerEmployee->name : '' }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <div id="manager_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                        @foreach($employees as $employee)
                                            <div class="manager-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                                                 data-value="{{ $employee->id }}" 
                                                 data-text="{{ $employee->name }}">
                                                {{ $employee->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" id="manager" name="manager" value="{{ $warehouse->manager }}" required>
                                </div>
                            </div>
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                                <input type="text" id="address" name="address" value="{{ $warehouse->address }}" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả kho hàng</label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $warehouse->description }}</textarea>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <a href="{{ route('warehouses.show', $warehouse->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Handle manager search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const managerSearch = document.getElementById('manager_search');
            const managerDropdown = document.getElementById('manager_dropdown');
            const managerOptions = document.querySelectorAll('.manager-option');
            const managerHidden = document.getElementById('manager');

            let selectedManagerId = '';
            let selectedManagerName = '';

            // Show dropdown when input is focused
            managerSearch.addEventListener('focus', function() {
                managerDropdown.classList.remove('hidden');
                filterManagers();
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!managerSearch.contains(e.target) && !managerDropdown.contains(e.target)) {
                    managerDropdown.classList.add('hidden');
                }
            });

            // Filter managers based on search input
            function filterManagers() {
                const searchTerm = managerSearch.value.toLowerCase();
                managerOptions.forEach(option => {
                    const managerName = option.getAttribute('data-text');
                    const managerNameLower = managerName.toLowerCase();
                    
                    if (managerNameLower.includes(searchTerm)) {
                        option.style.display = 'block';
                        
                        // Highlight search term if it exists
                        if (searchTerm) {
                            const regex = new RegExp(`(${searchTerm})`, 'gi');
                            option.innerHTML = managerName.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
                        } else {
                            option.innerHTML = managerName;
                        }
                    } else {
                        option.style.display = 'none';
                    }
                });
            }

            // Handle search input
            managerSearch.addEventListener('input', filterManagers);

            // Handle manager option selection
            managerOptions.forEach(option => {
                option.addEventListener('click', function() {
                    selectedManagerId = this.getAttribute('data-value');
                    selectedManagerName = this.getAttribute('data-text');
                    managerSearch.value = selectedManagerName;
                    managerHidden.value = selectedManagerId;
                    managerDropdown.classList.add('hidden');
                });
            });

            // Keyboard navigation
            let selectedIndex = -1;
            managerSearch.addEventListener('keydown', function(e) {
                const visibleOptions = Array.from(managerOptions).filter(option => 
                    option.style.display !== 'none'
                );

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, visibleOptions.length - 1);
                    updateSelection(visibleOptions);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateSelection(visibleOptions);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (selectedIndex >= 0 && visibleOptions[selectedIndex]) {
                        const option = visibleOptions[selectedIndex];
                        selectedManagerId = option.getAttribute('data-value');
                        selectedManagerName = option.getAttribute('data-text');
                        managerSearch.value = selectedManagerName;
                        managerHidden.value = selectedManagerId;
                        managerDropdown.classList.add('hidden');
                        selectedIndex = -1;
                    }
                } else if (e.key === 'Escape') {
                    managerDropdown.classList.add('hidden');
                    selectedIndex = -1;
                }
            });

            function updateSelection(visibleOptions) {
                // Remove previous selection
                managerOptions.forEach(option => {
                    option.classList.remove('bg-blue-100', 'text-blue-900');
                });

                // Add selection to current index
                if (selectedIndex >= 0 && visibleOptions[selectedIndex]) {
                    visibleOptions[selectedIndex].classList.add('bg-blue-100', 'text-blue-900');
                }
            }
        });
    </script>
</body>

</html> 