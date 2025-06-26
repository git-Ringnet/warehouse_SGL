<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo nhóm quyền - SGL</title>
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
                <a href="{{ route('roles.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-user-tag mr-2"></i>Tạo nhóm quyền mới
                </h1>
            </div>
        </header>
        
        <main class="p-6">
            <!-- Thông báo quyền admin -->
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-check text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <strong>Xác nhận quyền:</strong> Bạn đang truy cập với quyền admin và có thể tạo nhóm phân quyền mới. 
                            Vui lòng cấu hình quyền một cách cẩn thận để đảm bảo an toàn hệ thống.
                        </p>
                    </div>
                </div>
            </div>
            
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-500"></i>
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

            <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Thông tin cơ bản -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">
                        <i class="fas fa-info-circle mr-2"></i>Thông tin cơ bản
                    </h2>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Tên nhóm quyền <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Tên nhóm quyền phải là duy nhất trong hệ thống</p>
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                            <textarea id="description" name="description" rows="3"
                                     class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Phân quyền -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">
                        <i class="fas fa-key mr-2"></i>Phân quyền
                    </h2>
                    
                    <!-- Template quyền nhanh -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h3 class="text-sm font-medium text-blue-800 mb-3">
                            <i class="fas fa-magic mr-2"></i>Template quyền nhanh
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <button type="button" class="template-btn bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors" data-template="warehouse">
                                <i class="fas fa-warehouse mr-2"></i>Quản lý kho
                            </button>
                            <button type="button" class="template-btn bg-green-100 hover:bg-green-200 text-green-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors" data-template="production">
                                <i class="fas fa-industry mr-2"></i>Sản xuất
                            </button>
                            <button type="button" class="template-btn bg-purple-100 hover:bg-purple-200 text-purple-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors" data-template="maintenance">
                                <i class="fas fa-wrench mr-2"></i>Bảo trì
                            </button>
                            <button type="button" class="template-btn bg-orange-100 hover:bg-orange-200 text-orange-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors" data-template="project">
                                <i class="fas fa-tasks mr-2"></i>Dự án
                            </button>
                            <button type="button" class="template-btn bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors" data-template="viewer">
                                <i class="fas fa-eye mr-2"></i>Chỉ xem
                            </button>
                            <button type="button" class="template-btn bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors" data-template="clear">
                                <i class="fas fa-broom mr-2"></i>Xóa tất cả
                            </button>
                        </div>
                        <p class="text-xs text-blue-600 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>Click vào template để áp dụng nhanh các quyền phổ biến
                        </p>
                    </div>
                    
                    <div class="mb-4 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="select-all-permissions" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="select-all-permissions" class="ml-2 block text-sm font-medium text-gray-700">
                                    Chọn tất cả
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="select-view-only" class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <label for="select-view-only" class="ml-2 block text-sm font-medium text-gray-700">
                                    Chỉ quyền xem
                                </label>
                            </div>
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
                            @php
                                $groupColors = [
                                    'Quản lý hệ thống' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'icon' => 'fas fa-server'],
                                    'Quản lý tài sản' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'icon' => 'fas fa-box-open'],
                                    'Vận hành kho' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'icon' => 'fas fa-warehouse'],
                                    'Sản xuất & Kiểm thử' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'icon' => 'fas fa-microchip'],
                                    'Bảo trì & Sửa chữa' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'icon' => 'fas fa-tools'],
                                    'Quản lý dự án' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'icon' => 'fas fa-project-diagram'],
                                    'Phiếu yêu cầu' => ['bg' => 'bg-pink-50', 'text' => 'text-pink-700', 'icon' => 'fas fa-clipboard-list'],
                                    'Phần mềm & License' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'icon' => 'fas fa-laptop-code'],
                                    'Báo cáo' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'icon' => 'fas fa-chart-line'],
                                    'Phân quyền' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'icon' => 'fas fa-shield-alt'],
                                    'Phạm vi quyền' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-700', 'icon' => 'fas fa-map-marked-alt'],
                                ];
                                $colors = $groupColors[$group] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'icon' => 'fas fa-cog'];
                            @endphp
                            
                            <div class="permission-group border border-gray-200 rounded-lg overflow-hidden">
                                <div class="{{ $colors['bg'] }} px-4 py-3 flex justify-between items-center cursor-pointer group-header">
                                    <div class="flex items-center">
                                        <i class="{{ $colors['icon'] }} {{ $colors['text'] }} mr-2"></i>
                                        <h3 class="font-medium {{ $colors['text'] }}">{{ $group }}</h3>
                                        <span class="ml-2 px-2 py-1 text-xs font-medium bg-white rounded-full {{ $colors['text'] }}">
                                            {{ $groupPermissions->count() }} quyền
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" class="select-group h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="ml-2 text-sm {{ $colors['text'] }}">Chọn nhóm</span>
                                        </label>
                                        <i class="fas fa-chevron-down {{ $colors['text'] }} group-toggle-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="px-4 py-3 group-permissions">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($groupPermissions as $permission)
                                            @php
                                                $permissionType = '';
                                                if (str_contains($permission->name, '.view')) {
                                                    $permissionType = 'view';
                                                    $typeColor = 'text-green-600';
                                                    $typeIcon = 'fas fa-eye';
                                                } elseif (str_contains($permission->name, '.create')) {
                                                    $permissionType = 'create';
                                                    $typeColor = 'text-blue-600';
                                                    $typeIcon = 'fas fa-plus-circle';
                                                } elseif (str_contains($permission->name, '.edit')) {
                                                    $permissionType = 'edit';
                                                    $typeColor = 'text-yellow-600';
                                                    $typeIcon = 'fas fa-edit';
                                                } elseif (str_contains($permission->name, '.delete')) {
                                                    $permissionType = 'delete';
                                                    $typeColor = 'text-red-600';
                                                    $typeIcon = 'fas fa-trash-alt';
                                                } else {
                                                    $permissionType = 'other';
                                                    $typeColor = 'text-gray-600';
                                                    $typeIcon = 'fas fa-cog';
                                                }
                                            @endphp
                                            
                                            <div class="permission-item flex items-center p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                                <input type="checkbox" id="permission-{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}"
                                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                                    class="permission-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                <label for="permission-{{ $permission->id }}" class="ml-2 block text-sm text-gray-700 flex-1">
                                                    <div class="flex items-center">
                                                        <i class="{{ $typeIcon }} {{ $typeColor }} mr-2 text-xs"></i>
                                                        <span>{{ $permission->display_name }}</span>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">{{ $permission->description }}</div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Gán nhân viên vào nhóm quyền -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">
                        <i class="fas fa-users mr-2"></i>Gán nhân viên
                    </h2>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-4">Chọn nhân viên sẽ thuộc nhóm quyền này:</p>
                        
                        <!-- Tìm kiếm nhân viên -->
                        <div class="mb-6 relative">
                            <input type="text" id="employeeSearch" placeholder="Tìm kiếm nhân viên..." 
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="max-h-80 overflow-y-auto">
                                <table class="min-w-full divide-y divide-gray-200 employee-list">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" id="select-all-employees" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            </th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên nhân viên</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($employees as $employee)
                                        <tr class="employee-row hover:bg-gray-50">
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <input type="checkbox" name="employees[]" value="{{ $employee->id }}" class="employee-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 employee-name">{{ $employee->name }}</div>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <div class="text-sm text-gray-500 employee-username">{{ $employee->username }}</div>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                @if($employee->role == 'admin')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                        Quản trị viên
                                                    </span>
                                                @elseif($employee->role == 'manager')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Quản lý
                                                    </span>
                                                @elseif($employee->role == 'tech')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                        Kỹ thuật viên
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Nhân viên
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                @if($employee->status == 'active')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Đang hoạt động
                                                    </span>
                                                @elseif($employee->status == 'leave')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Nghỉ phép
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Đã nghỉ việc
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div id="noEmployeesFound" class="hidden py-4 text-center text-gray-500 text-sm">
                                Không tìm thấy nhân viên nào phù hợp với từ khóa tìm kiếm
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quản lý nhân viên trong nhóm -->
                <div class="mb-6 bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Quản lý nhân viên</h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chọn nhân viên thuộc nhóm quyền này</label>
                        <div class="border border-gray-300 rounded-lg p-4 max-h-80 overflow-y-auto">
                            @if($employees->count() > 0)
                            <div class="mb-2 flex items-center">
                                <button type="button" id="select-all-employees" class="text-sm text-blue-600 hover:text-blue-800">Chọn tất cả</button>
                                <span class="mx-2">|</span>
                                <button type="button" id="deselect-all-employees" class="text-sm text-blue-600 hover:text-blue-800">Bỏ chọn tất cả</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($employees as $employee)
                                <div class="flex items-start">
                                    <input type="checkbox" name="employees[]" id="employee-{{ $employee->id }}" value="{{ $employee->id }}" class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="employee-{{ $employee->id }}" class="ml-2 block text-sm text-gray-700">
                                        <div class="font-medium">{{ $employee->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $employee->position ?? 'Không có chức vụ' }}</div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-gray-500 italic">Không có nhân viên nào trong hệ thống.</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Quản lý dự án có quyền truy cập -->
                <div class="mb-6 bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Phân quyền dự án</h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chọn dự án mà nhóm quyền này có thể truy cập</label>
                        <div class="border border-gray-300 rounded-lg p-4 max-h-80 overflow-y-auto">
                            @if($projects->count() > 0)
                            <div class="mb-2 flex items-center">
                                <button type="button" id="select-all-projects" class="text-sm text-blue-600 hover:text-blue-800">Chọn tất cả</button>
                                <span class="mx-2">|</span>
                                <button type="button" id="deselect-all-projects" class="text-sm text-blue-600 hover:text-blue-800">Bỏ chọn tất cả</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($projects as $project)
                                <div class="flex items-start">
                                    <input type="checkbox" name="projects[]" id="project-{{ $project->id }}" value="{{ $project->id }}" class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="project-{{ $project->id }}" class="ml-2 block text-sm text-gray-700">
                                        <div class="font-medium">{{ $project->project_name }}</div>
                                        <div class="text-xs text-gray-500">
                                            @if($project->customer)
                                                {{ $project->customer->name }}
                                            @else
                                                Không có khách hàng
                                            @endif
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-gray-500 italic">Không có dự án nào đang hoạt động.</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Quản lý hợp đồng cho thuê có quyền truy cập -->
                <div class="mb-6 bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Phân quyền hợp đồng cho thuê</h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Chọn hợp đồng cho thuê mà nhóm quyền này có thể truy cập</label>
                        <div class="border border-gray-300 rounded-lg p-4 max-h-80 overflow-y-auto">
                            @if($rentals->count() > 0)
                            <div class="mb-2 flex items-center">
                                <button type="button" id="select-all-rentals" class="text-sm text-blue-600 hover:text-blue-800">Chọn tất cả</button>
                                <span class="mx-2">|</span>
                                <button type="button" id="deselect-all-rentals" class="text-sm text-blue-600 hover:text-blue-800">Bỏ chọn tất cả</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($rentals as $rental)
                                <div class="flex items-start">
                                    <input type="checkbox" name="rentals[]" id="rental-{{ $rental->id }}" value="{{ $rental->id }}" class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="rental-{{ $rental->id }}" class="ml-2 block text-sm text-gray-700">
                                        <div class="font-medium">Hợp đồng #{{ $rental->id }}</div>
                                        <div class="text-xs text-gray-500">
                                            @if($rental->customer)
                                                {{ $rental->customer->name }}
                                            @else
                                                Không có khách hàng
                                            @endif
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-gray-500 italic">Không có hợp đồng cho thuê nào đang hoạt động.</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('roles.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>Hủy
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Lưu nhóm quyền
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Template quyền nhanh
            const templateButtons = document.querySelectorAll('.template-btn');
            templateButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const template = this.dataset.template;
                    applyPermissionTemplate(template);
                });
            });

            // Xử lý chọn chỉ quyền xem
            const selectViewOnly = document.getElementById('select-view-only');
            if (selectViewOnly) {
                selectViewOnly.addEventListener('change', function() {
                    const allPermissions = document.querySelectorAll('.permission-checkbox');
                    allPermissions.forEach(permission => {
                        if (permission.id.includes('.view')) {
                            permission.checked = this.checked;
                        } else {
                            permission.checked = false;
                        }
                    });
                    updateSelectAllStatus();
                });
            }

            // Xử lý tìm kiếm quyền
            const permissionSearch = document.getElementById('permission-search');
            if (permissionSearch) {
                permissionSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    const permissionItems = document.querySelectorAll('.permission-item');
                    
                    permissionItems.forEach(item => {
                        const label = item.querySelector('label').textContent.toLowerCase();
                        if (label.includes(searchTerm)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }

            // Xử lý chọn tất cả quyền
            const selectAllPermissions = document.getElementById('select-all-permissions');
            if (selectAllPermissions) {
                selectAllPermissions.addEventListener('change', function() {
                    const allPermissions = document.querySelectorAll('.permission-checkbox');
                    allPermissions.forEach(permission => {
                        permission.checked = this.checked;
                    });
                    
                    // Cập nhật trạng thái các checkbox nhóm
                    document.querySelectorAll('.select-group').forEach(groupCheckbox => {
                        groupCheckbox.checked = this.checked;
                    });
                });
            }

            // Xử lý chọn nhóm quyền
            document.querySelectorAll('.select-group').forEach(groupCheckbox => {
                groupCheckbox.addEventListener('change', function() {
                    const group = this.closest('.permission-group');
                    const checkboxes = group.querySelectorAll('.permission-checkbox');
                    
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    
                    updateSelectAllStatus();
                });
            });

            // Cập nhật trạng thái checkbox nhóm khi checkbox con thay đổi
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const group = this.closest('.permission-group');
                    const groupCheckbox = group.querySelector('.select-group');
                    const checkboxes = group.querySelectorAll('.permission-checkbox');
                    
                    let allChecked = true;
                    checkboxes.forEach(cb => {
                        if (!cb.checked) allChecked = false;
                    });
                    
                    groupCheckbox.checked = allChecked;
                    updateSelectAllStatus();
                });
            });

            // Xử lý đóng/mở nhóm quyền
            document.querySelectorAll('.group-header').forEach(header => {
                header.addEventListener('click', function(e) {
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

            function applyPermissionTemplate(template) {
                const allPermissions = document.querySelectorAll('.permission-checkbox');
                
                // Xóa tất cả trước
                allPermissions.forEach(permission => {
                    permission.checked = false;
                });

                switch(template) {
                    case 'warehouse':
                        // Quản lý kho: Vận hành kho + Quản lý tài sản + Báo cáo
                        allPermissions.forEach(permission => {
                            const name = permission.id;
                            if (name.includes('warehouses.') || 
                                name.includes('materials.') || 
                                name.includes('products.') || 
                                name.includes('goods.') ||
                                name.includes('imports.') || 
                                name.includes('exports.') || 
                                name.includes('transfers.') ||
                                name.includes('reports.')) {
                                permission.checked = true;
                            }
                        });
                        break;
                        
                    case 'production':
                        // Sản xuất: Sản xuất & Kiểm thử + Quản lý tài sản
                        allPermissions.forEach(permission => {
                            const name = permission.id;
                            if (name.includes('assembly.') || 
                                name.includes('testing.') || 
                                name.includes('materials.') || 
                                name.includes('products.') ||
                                name.includes('reports.operations')) {
                                permission.checked = true;
                            }
                        });
                        break;
                        
                    case 'maintenance':
                        // Bảo trì: Bảo trì & Sửa chữa + Phần mềm
                        allPermissions.forEach(permission => {
                            const name = permission.id;
                            if (name.includes('repairs.') || 
                                name.includes('warranties.') || 
                                name.includes('software.') ||
                                name.includes('requests.')) {
                                permission.checked = true;
                            }
                        });
                        break;
                        
                    case 'project':
                        // Dự án: Quản lý dự án + Phiếu yêu cầu
                        allPermissions.forEach(permission => {
                            const name = permission.id;
                            if (name.includes('projects.') || 
                                name.includes('rentals.') || 
                                name.includes('requests.') ||
                                name.includes('reports.projects')) {
                                permission.checked = true;
                            }
                        });
                        break;
                        
                    case 'viewer':
                        // Chỉ xem: Tất cả quyền .view
                        allPermissions.forEach(permission => {
                            const name = permission.id;
                            if (name.includes('.view')) {
                                permission.checked = true;
                            }
                        });
                        break;
                        
                    case 'clear':
                        // Xóa tất cả
                        allPermissions.forEach(permission => {
                            permission.checked = false;
                        });
                        break;
                }
                
                // Cập nhật trạng thái các checkbox
                updateSelectAllStatus();
                document.querySelectorAll('.select-group').forEach(groupCheckbox => {
                    const group = groupCheckbox.closest('.permission-group');
                    const checkboxes = group.querySelectorAll('.permission-checkbox');
                    let allChecked = true;
                    checkboxes.forEach(cb => {
                        if (!cb.checked) allChecked = false;
                    });
                    groupCheckbox.checked = allChecked;
                });
            }

            // Xử lý tìm kiếm nhân viên
            const employeeSearch = document.getElementById('employeeSearch');
            const employeeRows = document.querySelectorAll('.employee-row');
            const noEmployeesFoundMessage = document.getElementById('noEmployeesFound');
            
            if (employeeSearch) {
                employeeSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    let foundAny = false;
                    
                    employeeRows.forEach(row => {
                        const name = row.querySelector('.employee-name').textContent.toLowerCase();
                        const username = row.querySelector('.employee-username').textContent.toLowerCase();
                        
                        if (name.includes(searchTerm) || username.includes(searchTerm)) {
                            row.style.display = '';
                            foundAny = true;
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    
                    if (noEmployeesFoundMessage) {
                        noEmployeesFoundMessage.style.display = foundAny ? 'none' : 'block';
                    }
                });
            }
            
            // Xử lý chọn tất cả nhân viên
            const selectAllEmployeesCheckbox = document.getElementById('select-all-employees');
            const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
            
            if (selectAllEmployeesCheckbox) {
                function updateSelectAllEmployeesStatus() {
                    let allChecked = true;
                    let visibleCount = 0;
                    
                    employeeCheckboxes.forEach(checkbox => {
                        if (checkbox.closest('tr').style.display !== 'none') {
                            visibleCount++;
                            if (!checkbox.checked) {
                                allChecked = false;
                            }
                        }
                    });
                    
                    selectAllEmployeesCheckbox.checked = (visibleCount > 0) && allChecked;
                    selectAllEmployeesCheckbox.indeterminate = !allChecked && visibleCount > 0 && document.querySelectorAll('.employee-checkbox:checked').length > 0;
                }
                
                selectAllEmployeesCheckbox.addEventListener('change', function() {
                    employeeCheckboxes.forEach(checkbox => {
                        if (checkbox.closest('tr').style.display !== 'none') {
                            checkbox.checked = this.checked;
                        }
                    });
                    
                    updateSelectAllEmployeesStatus();
                });
                
                employeeCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectAllEmployeesStatus);
                });
                
                updateSelectAllEmployeesStatus();
            }
        });
    </script>
</body>
</html> 