<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhập kho - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Quản lý nhập kho</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <form action="{{ route('inventory-imports.index') }}" method="GET"
                    class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                    <div class="flex flex-col md:flex-row gap-2 w-full">
                        <!-- Bộ lọc -->
                        <select name="filter" id="filter"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                            <option value="">Tất cả</option>
                            <option value="import_code" {{ isset($filter) && $filter == 'import_code' ? 'selected' : '' }}>
                                Mã phiếu nhập
                            </option>
                            <option value="order_code" {{ isset($filter) && $filter == 'order_code' ? 'selected' : '' }}>
                                Mã đơn hàng
                            </option>
                            <option value="supplier" {{ isset($filter) && $filter == 'supplier' ? 'selected' : '' }}>
                                Nhà cung cấp
                            </option>
                            <option value="notes" {{ isset($filter) && $filter == 'notes' ? 'selected' : '' }}>
                                Ghi chú
                            </option>
                            <option value="date" {{ isset($filter) && $filter == 'date' ? 'selected' : '' }}>
                                Ngày nhập
                            </option>
                            <option value="status" {{ isset($filter) && $filter == 'status' ? 'selected' : '' }}>
                                Trạng thái
                            </option>
                        </select>

                        <!-- Ô tìm kiếm cho mã phiếu, mã đơn, ghi chú -->
                        <input type="text" name="search" id="search_text"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64"
                            value="{{ request('search') }}" placeholder="Nhập từ khóa tìm kiếm..." />

                        <!-- Dropdown nhà cung cấp -->
                        <select name="supplier_id" id="supplier_select"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 hidden">
                            <option value="">Chọn nhà cung cấp</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Dropdown trạng thái -->
                        <select name="status" id="status_select"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 hidden">
                            <option value="">Chọn trạng thái</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        </select>

                        <!-- Input date range -->
                        <div id="date_range" class="flex gap-2 hidden">
                            <input type="date" name="start_date"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700"
                                value="{{ request('start_date') }}" />
                            <span class="flex items-center">đến</span>
                            <input type="date" name="end_date"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700"
                                value="{{ request('end_date') }}" />
                        </div>

                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-search mr-2"></i> Tìm kiếm
                        </button>
                    </div>
                </form>
                @php
                    $user = Auth::guard('web')->user();
                    $isAdmin = $user && $user->role === 'admin';
                @endphp
                @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory_imports.create')))
                    <a href="{{ route('inventory-imports.create') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                        <i class="fas fa-plus mr-2"></i> Tạo phiếu nhập
                    </a>
                @endif
            </div>
        </header>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        @if (session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mã phiếu nhập</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nhà cung cấp</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ngày nhập</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mã đơn hàng</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ghi chú</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($inventoryImports as $key => $import)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $inventoryImports->firstItem() + $key }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $import->import_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $import->supplier->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $import->import_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $import->order_code ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $import->notes ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($import->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Đã duyệt
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i> Chờ xử lý
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory_imports.view_detail')))
                                        <a href="{{ route('inventory-imports.show', $import->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($import->status !== 'approved' && ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory_imports.edit'))))
                                        <a href="{{ route('inventory-imports.edit', $import->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                            title="Sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($import->status !== 'approved' && ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory_imports.approve'))))
                                        <form action="{{ route('inventory-imports.approve', $import->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                                title="Duyệt"
                                                onclick="return confirm('Bạn có chắc chắn muốn duyệt phiếu nhập này?')">
                                                <i class="fas fa-check text-green-500 group-hover:text-white"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if ($import->status !== 'approved' && ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory_imports.delete'))))
                                        <button
                                            onclick="openDeleteModal('{{ $import->id }}', '{{ $import->import_code }}')"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                            title="Xóa">
                                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                        </button>
                                        <form id="delete-form-{{ $import->id }}"
                                            action="{{ route('inventory-imports.destroy', $import->id) }}"
                                            method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ liệu
                                    phiếu nhập kho</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    @if ($inventoryImports->count() > 0)
                        Hiển thị {{ $inventoryImports->firstItem() }} đến {{ $inventoryImports->lastItem() }} của
                        {{ $inventoryImports->total() }} bản ghi
                    @else
                        Không có bản ghi nào
                    @endif
                </div>

                <div class="flex space-x-1">
                    {{ $inventoryImports->links('pagination::tailwind') }}
                </div>
            </div>
        </main>
    </div>

    <!-- Modal xác nhận xóa -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h2 class="text-xl font-bold mb-4">Xác nhận xóa</h2>
            <p class="text-gray-700 mb-6">
                Bạn có chắc chắn muốn xóa <span id="customerNameToDelete" class="font-semibold"></span>?
            </p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                    Hủy
                </button>
                <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelect = document.getElementById('filter');
            const searchText = document.getElementById('search_text');
            const supplierSelect = document.getElementById('supplier_select');
            const statusSelect = document.getElementById('status_select');
            const dateRange = document.getElementById('date_range');

            function updateSearchFields() {
                // Hide all search fields first
                searchText.classList.add('hidden');
                supplierSelect.classList.add('hidden');
                statusSelect.classList.add('hidden');
                dateRange.classList.add('hidden');

                // Show appropriate search field based on filter
                switch (filterSelect.value) {
                    case 'import_code':
                    case 'order_code':
                    case 'notes':
                        searchText.classList.remove('hidden');
                        searchText.placeholder = `Nhập ${filterSelect.options[filterSelect.selectedIndex].text.toLowerCase()}...`;
                        break;
                    case 'supplier':
                        supplierSelect.classList.remove('hidden');
                        break;
                    case 'status':
                        statusSelect.classList.remove('hidden');
                        break;
                    case 'date':
                        dateRange.classList.remove('hidden');
                        break;
                    default:
                        searchText.classList.remove('hidden');
                        searchText.placeholder = 'Tìm kiếm...';
                }
            }

            // Initial setup
            updateSearchFields();

            // Update on filter change
            filterSelect.addEventListener('change', updateSearchFields);
        });
    </script>
</body>

</html>
