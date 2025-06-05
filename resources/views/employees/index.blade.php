<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhân viên - SGL</title>
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
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý nhân viên</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <form action="{{ route('employees.index') }}" method="GET" class="flex flex-col md:flex-row gap-2 w-full">
                    <div class="flex gap-2 w-full md:w-auto">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tìm kiếm..." class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64" />
                        <select name="filter" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                            <option value="" {{ !$filter ? 'selected' : '' }}>Tất cả</option>
                            <option value="username" {{ $filter == 'username' ? 'selected' : '' }}>Username</option>
                            <option value="name" {{ $filter == 'name' ? 'selected' : '' }}>Họ và tên</option>
                            <option value="phone" {{ $filter == 'phone' ? 'selected' : '' }}>Số điện thoại</option>
                            <option value="email" {{ $filter == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="role" {{ $filter == 'role' ? 'selected' : '' }}>Vai trò</option>
                            <option value="status" {{ $filter == 'status' ? 'selected' : '' }}>Trạng thái</option>
                        </select>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg px-4 py-2 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                <a href="{{ route('employees.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full">
                    <i class="fas fa-user-plus mr-2"></i> Thêm nhân viên
                </a>
            </div>
        </header>
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif
            
            @if(session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Họ và tên</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email/SĐT</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chức vụ</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $employee->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $employee->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $employee->email }} / {{ $employee->phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($employee->roleGroup)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $employee->roleGroup->is_active ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $employee->roleGroup->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-500 text-xs">Chưa gán</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $employee->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($employee->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Đang hoạt động
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Đã khóa
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    <a href="{{ route('employees.show', $employee->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                        <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                    </a>
                                    <button onclick="openDeleteModal('{{ $employee->id }}', '{{ $employee->name }}')" 
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" 
                                            title="Xóa">
                                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                    </button>

                                    <!-- Nút khóa tài khoản -->
                                    <form id="toggle-form-{{ $employee->id }}" action="{{ route('employees.toggle-active', $employee->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                    <button onclick="document.getElementById('toggle-form-{{ $employee->id }}').submit()" 
                                            class="w-8 h-8 flex items-center justify-center rounded-full {{ $employee->is_active ? 'bg-purple-100 hover:bg-purple-500' : 'bg-green-100 hover:bg-green-500' }} transition-colors group" 
                                            title="{{ $employee->is_active ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                        <i class="fas {{ $employee->is_active ? 'fa-lock' : 'fa-lock-open' }} {{ $employee->is_active ? 'text-purple-500' : 'text-green-500' }} group-hover:text-white"></i>
                                    </button>
                                    
                                    <form id="delete-form-{{ $employee->id }}" action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ liệu nhân viên</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $employees->links() }}
            </div>
        </main>
    </div>

    <script>
        // Khởi tạo modal khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
        });

        // Mở modal xác nhận xóa
        function openDeleteModal(id, name) {
            // Thay đổi nội dung modal
            document.getElementById('customerNameToDelete').innerText = name;
            
            // Thay đổi hành động khi nút xác nhận được nhấn
            document.getElementById('confirmDeleteBtn').onclick = function() {
                document.getElementById('delete-form-' + id).submit();
                closeDeleteModal();
            };
            
            // Hiển thị modal
            document.getElementById('deleteModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    </script>
</body>
</html> 