<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết nhóm quyền - SGL</title>
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
                <a href="{{ route('roles.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chi tiết nhóm quyền</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $role->name }}
                </div>
                
                @if($role->is_active)
                    <div class="ml-2 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                        <i class="fas fa-check-circle mr-1"></i> Đang hoạt động
                    </div>
                @else
                    <div class="ml-2 px-3 py-1 bg-red-100 text-red-800 text-sm rounded-full">
                        <i class="fas fa-times-circle mr-1"></i> Đã vô hiệu hóa
                    </div>
                @endif
                
                @if($role->is_system)
                    <div class="ml-2 px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full">
                        <i class="fas fa-lock mr-1"></i> Hệ thống
                    </div>
                @endif
            </div>
            <div class="flex space-x-3">
                @unless($role->is_system)
                <a href="{{ route('roles.edit', $role->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <form action="{{ route('roles.toggleStatus', $role->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="{{ $role->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }} text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas {{ $role->is_active ? 'fa-ban' : 'fa-check-circle' }} mr-2"></i> 
                        {{ $role->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                    </button>
                </form>
                @endunless
            </div>
        </header>

        <main class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Thông tin nhóm quyền -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Thông tin nhóm quyền
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Tên nhóm quyền</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $role->name }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Mô tả</p>
                            <p class="text-base text-gray-800">{{ $role->description ?? 'Không có mô tả' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Phạm vi</p>
                            <p class="text-base text-gray-800">{{ $role->scope ?? 'Không có phạm vi' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái</p>
                            <p class="text-base text-gray-800">
                                @if($role->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Đang hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i> Đã vô hiệu hóa
                                    </span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Loại</p>
                            <p class="text-base text-gray-800">
                                @if($role->is_system)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-lock mr-1"></i> Quyền hệ thống
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-user-cog mr-1"></i> Quyền tùy chỉnh
                                    </span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Thời gian tạo</p>
                            <p class="text-base text-gray-800">{{ $role->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 font-medium mb-1">Cập nhật lần cuối</p>
                            <p class="text-base text-gray-800">{{ $role->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @unless($role->is_system)
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhóm quyền này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center text-red-600 hover:text-red-800">
                                <i class="fas fa-trash-alt mr-2"></i> Xóa nhóm quyền
                            </button>
                        </form>
                    </div>
                    @endunless
                </div>
                
                <!-- Thống kê -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-chart-pie mr-2 text-indigo-500"></i>
                            Thống kê quyền
                        </h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h3 class="text-sm font-medium text-blue-800">Tổng số quyền</h3>
                                <p class="mt-2 text-2xl font-bold text-blue-700">{{ $role->permissions->count() }}</p>
                                <p class="mt-1 text-xs text-blue-600">trên {{ count($permissions) }} nhóm quyền</p>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h3 class="text-sm font-medium text-green-800">Số nhân viên</h3>
                                <p class="mt-2 text-2xl font-bold text-green-700">{{ $role->employees->count() }}</p>
                                <p class="mt-1 text-xs text-green-600">đang sử dụng nhóm quyền này</p>
                            </div>
                            
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <h3 class="text-sm font-medium text-purple-800">Trạng thái</h3>
                                <p class="mt-2 text-lg font-bold {{ $role->is_active ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $role->is_active ? 'Đang hoạt động' : 'Đã vô hiệu hóa' }}
                                </p>
                                <p class="mt-1 text-xs {{ $role->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $role->is_active ? 'Nhóm quyền có thể được sử dụng' : 'Nhóm quyền đã bị khóa' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Danh sách các quyền trong nhóm -->
            <div class="mt-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-key mr-2 text-yellow-500"></i>
                        Danh sách quyền
                    </h2>
                    
                    <div class="mb-6">
                        <input type="text" id="permissionSearch" placeholder="Tìm kiếm quyền..." class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    @if($role->permissions->count() > 0)
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 permission-groups">
                            @foreach($permissions as $group => $groupPermissions)
                                @php
                                    $hasPermissionInGroup = false;
                                    foreach($groupPermissions as $permission) {
                                        if(in_array($permission->id, $rolePermissions)) {
                                            $hasPermissionInGroup = true;
                                            break;
                                        }
                                    }
                                @endphp
                                
                                @if($hasPermissionInGroup)
                                <div class="permission-group">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h3 class="font-medium text-gray-800 mb-3 group-name">{{ $group }}</h3>
                                        <ul class="space-y-2">
                                            @foreach($groupPermissions as $permission)
                                                @if(in_array($permission->id, $rolePermissions))
                                                <li class="permission-item flex items-center text-sm">
                                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                                    <span class="permission-name">{{ $permission->display_name }}</span>
                                                </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                        
                        <!-- No results message -->
                        <div id="noPermissionsFound" class="hidden text-center py-10">
                            <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-500">Không tìm thấy quyền nào khớp với từ khóa tìm kiếm</p>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="fas fa-lock text-gray-400 text-5xl mb-4"></i>
                            <p class="text-gray-500">Nhóm quyền này không có quyền nào</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Danh sách nhân viên được gán nhóm quyền này -->
            <div class="mt-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-users mr-2 text-blue-500"></i>
                            Nhân viên thuộc nhóm quyền này
                        </h2>
                        
                        <div class="mt-4 md:mt-0">
                            <a href="{{ route('roles.edit', $role->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                                <i class="fas fa-user-plus mr-2"></i> Thêm nhân viên
                            </a>
                        </div>
                    </div>
                    
                    @if($role->employees->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nhân viên</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Liên hệ</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($role->employees as $employee)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-500">{{ $employee->id }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-gray-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $employee->username }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $employee->email }}</div>
                                        <div class="text-sm text-gray-500">{{ $employee->phone }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        @if($employee->role == 'admin')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Quản trị viên
                                            </span>
                                        @elseif($employee->role == 'manager')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Quản lý
                                            </span>
                                        @elseif($employee->role == 'tech')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                Kỹ thuật viên
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Nhân viên
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                        @if($employee->status == 'active')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Đang hoạt động
                                            </span>
                                        @elseif($employee->status == 'leave')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i> Nghỉ phép
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i> Đã nghỉ việc
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-right">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('employees.show', $employee->id) }}" class="text-blue-600 hover:text-blue-900" title="Xem thông tin">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('employees.edit', $employee->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-10">
                        <i class="fas fa-user-slash text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-500">Không có nhân viên nào được gán vào nhóm quyền này</p>
                        <a href="{{ route('roles.edit', $role->id) }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                            <i class="fas fa-user-plus mr-2"></i> Thêm nhân viên
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script>
        // Tìm kiếm quyền
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('permissionSearch');
            const permissionGroups = document.querySelectorAll('.permission-group');
            const noResultsMessage = document.getElementById('noPermissionsFound');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    let foundAny = false;
                    
                    permissionGroups.forEach(group => {
                        const groupName = group.querySelector('.group-name').textContent.toLowerCase();
                        const permissionItems = group.querySelectorAll('.permission-item');
                        let foundInGroup = false;
                        
                        permissionItems.forEach(item => {
                            const permissionName = item.querySelector('.permission-name').textContent.toLowerCase();
                            
                            if (permissionName.includes(searchTerm) || groupName.includes(searchTerm)) {
                                item.style.display = '';
                                foundInGroup = true;
                                foundAny = true;
                            } else {
                                item.style.display = 'none';
                            }
                        });
                        
                        group.style.display = foundInGroup ? '' : 'none';
                    });
                    
                    noResultsMessage.style.display = foundAny ? 'none' : 'block';
                });
            }
        });
    </script>
</body>
</html> 