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
                    <div class="flex gap-2 w-full md:w-auto">
                        <input type="text" name="search" placeholder="Tìm kiếm theo mã, nhà cung cấp..."
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64"
                            value="{{ $search ?? '' }}" />
                        <select name="filter"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                            <option value="">Bộ lọc</option>
                            <option value="import_code"
                                {{ isset($filter) && $filter == 'import_code' ? 'selected' : '' }}>Mã phiếu nhập
                            </option>
                            <option value="supplier" {{ isset($filter) && $filter == 'supplier' ? 'selected' : '' }}>Nhà
                                cung cấp</option>
                            <option value="order_code"
                                {{ isset($filter) && $filter == 'order_code' ? 'selected' : '' }}>Mã đơn hàng</option>
                        </select>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-search"></i> Tìm kiếm
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
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    @if (
                                        $isAdmin ||
                                            (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory_imports.view_detail')))
                                        <a href="{{ route('inventory-imports.show', $import->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory_imports.edit')))
                                        <a href="{{ route('inventory-imports.edit', $import->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                            title="Sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('inventory_imports.delete')))
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
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ liệu
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

    <script>
        // Khởi tạo modal khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
        });

        // Mở modal xác nhận xóa
        function openDeleteModal(id, name) {
            // Thay đổi nội dung modal
            document.getElementById('customerNameToDelete').innerText = "phiếu nhập " + name;

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
