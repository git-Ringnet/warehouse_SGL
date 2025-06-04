<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa nhóm quyền - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <div class="flex items-center">
                <a href="{{ route('roles.show', $role->id) }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa nhóm quyền: {{ $role->name }}</h1>
            </div>
        </header>
        
        <main class="p-6">
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm leading-5 font-medium text-red-800">
                                Đã xảy ra lỗi:
                            </h3>
                            <ul class="mt-1 text-sm leading-5 text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('roles.update', $role->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Thông tin cơ bản -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Thông tin cơ bản</h2>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Tên nhóm quyền <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Tên nhóm quyền phải là duy nhất trong hệ thống</p>
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                            <textarea id="description" name="description" rows="3"
                                     class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $role->description) }}</textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Phân quyền -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Phân quyền</h2>
                    
                    <div class="mb-4 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="select-all-permissions" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="select-all-permissions" class="ml-2 block text-sm font-medium text-gray-700">
                                Chọn tất cả
                            </label>
                        </div>
                        
                        <div class="relative">
                            <input type="text" id="permission-search" placeholder="Tìm kiếm quyền..."
                                class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        @foreach($permissions as $group => $groupPermissions)
                            <div class="permission-group border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-4 py-3 flex justify-between items-center cursor-pointer group-header">
                                    <h3 class="font-medium text-gray-700">{{ $group }}</h3>
                                    <div class="flex items-center space-x-3">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" class="select-group h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Chọn nhóm</span>
                                        </label>
                                        <i class="fas fa-chevron-down text-gray-500 group-toggle-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="px-4 py-3 group-permissions">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($groupPermissions as $permission)
                                            <div class="permission-item flex items-center">
                                                <input type="checkbox" id="permission-{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}"
                                                    {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                    class="permission-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <label for="permission-{{ $permission->id }}" class="ml-2 block text-sm text-gray-700">
                                                    {{ $permission->display_name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('roles.show', $role->id) }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kiểm tra trạng thái ban đầu của các nhóm
            function initializeGroupStatus() {
                document.querySelectorAll('.permission-group').forEach(group => {
                    const groupCheckbox = group.querySelector('.select-group');
                    const checkboxes = group.querySelectorAll('.permission-checkbox');
                    
                    // Nếu tất cả checkbox trong nhóm đã được chọn, chọn checkbox nhóm
                    let allChecked = checkboxes.length > 0;
                    checkboxes.forEach(checkbox => {
                        if (!checkbox.checked) allChecked = false;
                    });
                    
                    groupCheckbox.checked = allChecked;
                });
                
                // Kiểm tra "Chọn tất cả"
                updateSelectAllStatus();
            }
            
            // Xử lý tìm kiếm quyền
            const searchInput = document.getElementById('permission-search');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const permissionItems = document.querySelectorAll('.permission-item');
                
                permissionItems.forEach(item => {
                    const label = item.querySelector('label').textContent.toLowerCase();
                    
                    if (label.includes(searchTerm)) {
                        item.style.display = '';
                        // Hiển thị group chứa item khớp với kết quả tìm kiếm
                        const group = item.closest('.permission-group');
                        group.style.display = '';
                        group.querySelector('.group-permissions').style.display = '';
                        group.querySelector('.group-toggle-icon').classList.remove('fa-chevron-down');
                        group.querySelector('.group-toggle-icon').classList.add('fa-chevron-up');
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                // Ẩn group không có kết quả nào khớp
                document.querySelectorAll('.permission-group').forEach(group => {
                    const visibleItems = group.querySelectorAll('.permission-item[style=""]');
                    if (visibleItems.length === 0 && searchTerm) {
                        group.style.display = 'none';
                    }
                });
            });
            
            // Xử lý chọn tất cả các quyền
            const selectAllCheckbox = document.getElementById('select-all-permissions');
            selectAllCheckbox.addEventListener('change', function() {
                const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
                permissionCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                
                // Cập nhật trạng thái checkbox nhóm
                document.querySelectorAll('.select-group').forEach(groupCheckbox => {
                    groupCheckbox.checked = this.checked;
                });
            });
            
            // Xử lý chọn nhóm quyền
            document.querySelectorAll('.select-group').forEach(groupCheckbox => {
                groupCheckbox.addEventListener('change', function() {
                    const group = this.closest('.permission-group');
                    const checkboxes = group.querySelectorAll('.permission-checkbox');
                    
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    
                    // Cập nhật trạng thái "Chọn tất cả" khi tất cả nhóm được chọn
                    updateSelectAllStatus();
                });
            });
            
            // Cập nhật trạng thái checkbox nhóm khi checkbox con thay đổi
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const group = this.closest('.permission-group');
                    const groupCheckbox = group.querySelector('.select-group');
                    const checkboxes = group.querySelectorAll('.permission-checkbox');
                    
                    // Kiểm tra xem tất cả checkbox con đã được chọn chưa
                    let allChecked = true;
                    checkboxes.forEach(cb => {
                        if (!cb.checked) allChecked = false;
                    });
                    
                    groupCheckbox.checked = allChecked;
                    
                    // Cập nhật trạng thái "Chọn tất cả"
                    updateSelectAllStatus();
                });
            });
            
            // Xử lý đóng/mở nhóm quyền
            document.querySelectorAll('.group-header').forEach(header => {
                header.addEventListener('click', function(e) {
                    // Bỏ qua sự kiện khi click vào checkbox
                    if (e.target.type === 'checkbox' || e.target.tagName === 'LABEL' || e.target.tagName === 'SPAN') {
                        return;
                    }
                    
                    const group = this.closest('.permission-group');
                    const permissionsDiv = group.querySelector('.group-permissions');
                    const toggleIcon = group.querySelector('.group-toggle-icon');
                    
                    if (permissionsDiv.style.display === 'none') {
                        permissionsDiv.style.display = '';
                        toggleIcon.classList.remove('fa-chevron-down');
                        toggleIcon.classList.add('fa-chevron-up');
                    } else {
                        permissionsDiv.style.display = 'none';
                        toggleIcon.classList.remove('fa-chevron-up');
                        toggleIcon.classList.add('fa-chevron-down');
                    }
                });
            });
            
            function updateSelectAllStatus() {
                const allCheckboxes = document.querySelectorAll('.permission-checkbox');
                const selectAllCheckbox = document.getElementById('select-all-permissions');
                
                let allChecked = true;
                allCheckboxes.forEach(checkbox => {
                    if (!checkbox.checked) allChecked = false;
                });
                
                selectAllCheckbox.checked = allChecked;
            }
            
            // Khởi tạo trạng thái ban đầu
            initializeGroupStatus();
        });
    </script>
</body>
</html> 