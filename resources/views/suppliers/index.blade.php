<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhà cung cấp - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .sidebar {
            background: linear-gradient(180deg, #1a365d 0%, #0f2942 100%);
            transition: all 0.3s ease;
        }
        .sidebar .menu-item a {
            color: #fff;
            transition: all 0.3s ease;
        }
        .sidebar .menu-item a:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .sidebar .menu-item a.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid #3b82f6;
            color: #fff;
        }
        .sidebar .nav-item {
            color: #fff;
            transition: all 0.3s ease;
        }
        .sidebar .nav-item:hover {
            background: rgba(255,255,255,0.1);
        }
        .sidebar .nav-item.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid #3b82f6;
        }
        .sidebar .dropdown-content a {
            color: #fff;
            transition: all 0.3s ease;
        }
        .sidebar .dropdown-content a:hover {
            background: rgba(255,255,255,0.1);
        }
        .sidebar .dropdown-content a.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid #3b82f6;
            color: #fff;
            font-weight: 500;
        }
        .dropdown-content {
            display: none;
        }
        .dropdown-content.show {
            display: block;
        }
        .sidebar .logo-text {
            color: #fff;
            font-weight: 600;
        }
        .sidebar .logo-icon {
            color: #fff;
        }
        .sidebar .search-input {
            color: #e2e8f0;
        }
        .sidebar .search-input::placeholder {
            color: #94a3b8;
        }
        .sidebar .user-info {
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .user-info p {
            color: #e2e8f0;
        }
        .sidebar .user-info .role {
            color: #ffffff;
        }
        .content-area {
            margin-left: 256px;
            min-height: 100vh;
           
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
                width: 70px;
            }
            .content-area {
                margin-left: 0 !important;
            }
        }
        .sidebar .nav-item .flex {
            color: #fff;
        }
        
        .sidebar .flex {
            color: #fff;
        }
        
        .sidebar .dropdown button .flex {
            color: #fff;
        }
        
        /* Modal overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background-color: white;
            border-radius: 0.5rem;
            max-width: 500px;
            width: 90%;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal {
            transform: scale(1);
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý nhà cung cấp</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <form action="{{ route('suppliers.index') }}" method="GET" class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                    <select 
                        name="filter" 
                        id="filterSelect"
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700"
                        onchange="toggleQuantityFilter()"
                    >
                        <option value="">Tất cả</option>
                        <option value="name" {{ ($filter ?? '') == 'name' ? 'selected' : '' }}>Tên nhà cung cấp</option>
                        <option value="representative" {{ ($filter ?? '') == 'representative' ? 'selected' : '' }}>Tên người đại diện</option>
                        <option value="phone" {{ ($filter ?? '') == 'phone' ? 'selected' : '' }}>Số điện thoại</option>
                        <option value="email" {{ ($filter ?? '') == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="address" {{ ($filter ?? '') == 'address' ? 'selected' : '' }}>Địa chỉ</option>
                        <option value="total_items" {{ ($filter ?? '') == 'total_items' ? 'selected' : '' }}>Tổng số lượng đã nhập (lớn hơn hoặc bằng)</option>
                    </select>
                    
                    <div id="normalSearchInput" class="relative flex-grow {{ ($filter ?? '') == 'total_items' ? 'hidden' : '' }}">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Nhập từ khóa tìm kiếm..." 
                            class="border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full"
                            value="{{ ($filter ?? '') != 'total_items' ? ($search ?? '') : '' }}" 
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div id="quantitySearchInput" class="relative flex-grow {{ ($filter ?? '') != 'total_items' ? 'hidden' : '' }}">
                        <input 
                            type="number" 
                            name="quantity" 
                            placeholder="Nhập số lượng tối thiểu" 
                            min="0"
                            class="border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full"
                            value="{{ $quantity ?? '' }}" 
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-sort-numeric-down text-gray-400"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i> Tìm kiếm
                    </button>
                    @if($search || $filter)
                    <a href="{{ route('suppliers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times mr-2"></i> Xóa bộ lọc
                    </a>
                    @endif
                </form>
                <div class="flex flex-wrap gap-2">
                    @php
                        $user = Auth::guard('web')->user();
                        $canExport = $user && ($user->role === 'admin' || ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('suppliers.export')));
                        $canCreate = $user && ($user->role === 'admin' || ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('suppliers.create')));
                    @endphp

                    @if ($canExport)
                        <a href="{{ route('suppliers.export.fdf', request()->query()) }}" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-file-pdf mr-2"></i> Xuất FDF
                        </a>
                        <a href="{{ route('suppliers.export.excel', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-file-excel mr-2"></i> Xuất Excel
                        </a>
                    @endif

                    @if ($canCreate)
                        <a href="{{ route('suppliers.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-plus mr-2"></i> Thêm nhà cung cấp
                        </a>
                    @endif
                </div>
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
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên nhà cung cấp</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người đại diện</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số điện thoại</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Địa chỉ</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tổng SL đã nhập</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($suppliers as $key => $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $suppliers->firstItem() + $key }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $supplier->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $supplier->representative ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $supplier->phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $supplier->email ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $supplier->address ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-medium">{{ $supplier->total_items ?? 0 }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                @php
                                    $user = Auth::guard('web')->user();
                                    $canViewDetail = $user && ($user->role === 'admin' || ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('suppliers.view_detail')));
                                    $canEdit = $user && ($user->role === 'admin' || ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('suppliers.edit')));
                                    $canDelete = $user && ($user->role === 'admin' || ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('suppliers.delete')));
                                @endphp

                                @if ($canViewDetail)
                                    <a href="{{ route('suppliers.show', $supplier->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </a>
                                @endif

                                @if ($canEdit)
                                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                        <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                    </a>
                                @endif

                                @if ($canDelete)
                                    <button onclick="openDeleteModal('{{ $supplier->id }}', '{{ $supplier->name }}')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                        <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ liệu</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Hiển thị {{ $suppliers->firstItem() ?? 0 }}-{{ $suppliers->lastItem() ?? 0 }} của {{ $suppliers->total() ?? 0 }} mục
                </div>
                <div class="flex space-x-1">
                    {{ $suppliers->links() }}
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dropdown Menus
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            
            // Close all other dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== id) {
                    d.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Khởi tạo modal khi trang được load
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
        });

        // Ghi đè hàm deleteCustomer trong file delete-modal.js để thực hiện xóa thật
        function deleteCustomer(id) {
            // Tạo form ẩn để gửi yêu cầu DELETE
            const form = document.createElement('form');
            form.action = `/suppliers/${id}`;
            form.method = 'POST';
            form.style.display = 'none';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);

            document.body.appendChild(form);
            
            // Đóng modal trước khi submit form
            closeDeleteModal();
            
            // Gửi form để xóa nhà cung cấp
            form.submit();
        }

        function toggleQuantityFilter() {
            const filterSelect = document.getElementById('filterSelect');
            const normalSearchInput = document.getElementById('normalSearchInput');
            const quantitySearchInput = document.getElementById('quantitySearchInput');
            
            if (filterSelect.value === 'total_items') {
                normalSearchInput.classList.add('hidden');
                quantitySearchInput.classList.remove('hidden');
            } else {
                normalSearchInput.classList.remove('hidden');
                quantitySearchInput.classList.add('hidden');
            }
        }
    </script>
</body>
</html> 