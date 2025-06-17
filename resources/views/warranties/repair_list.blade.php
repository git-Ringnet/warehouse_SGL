<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách sửa chữa & bảo trì - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Quản lý sửa chữa & bảo trì</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('repairs.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i> Thêm mới
                </a>
            </div>
        </header>

        <main class="p-6">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filter and Search -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-6 border border-gray-100">
                <form method="GET" action="{{ route('repairs.index') }}" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                placeholder="Tìm kiếm mã phiếu, bảo hành, khách hàng, kỹ thuật viên..."
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Đang xử lý</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Đã xử lý</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                        <select name="repair_type" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tất cả loại sửa chữa</option>
                            <option value="maintenance" {{ request('repair_type') == 'maintenance' ? 'selected' : '' }}>Bảo trì định kỳ</option>
                            <option value="repair" {{ request('repair_type') == 'repair' ? 'selected' : '' }}>Sửa chữa lỗi</option>
                            <option value="replacement" {{ request('repair_type') == 'replacement' ? 'selected' : '' }}>Thay thế linh kiện</option>
                            <option value="upgrade" {{ request('repair_type') == 'upgrade' ? 'selected' : '' }}>Nâng cấp</option>
                            <option value="other" {{ request('repair_type') == 'other' ? 'selected' : '' }}>Khác</option>
                        </select>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-filter mr-1"></i> Lọc
                        </button>
                        <a href="{{ route('repairs.index') }}" class="bg-gray-100 text-gray-600 hover:bg-gray-200 px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-redo-alt mr-1"></i> Đặt lại
                        </a>
                    </div>
                </form>
            </div>

            <!-- Records Table -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mã phiếu
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ngày sửa chữa
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mã bảo hành
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Khách hàng
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loại sửa chữa
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kỹ thuật viên
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Chi phí
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($repairs as $repair)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="font-medium text-blue-600">{{ $repair->repair_code }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $repair->repair_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($repair->warranty_code)
                                            <span class="font-medium text-blue-600">{{ $repair->warranty_code }}</span>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $repair->customer_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($repair->repair_type_color == 'green') bg-green-100 text-green-800
                                            @elseif($repair->repair_type_color == 'yellow') bg-yellow-100 text-yellow-800
                                            @elseif($repair->repair_type_color == 'blue') bg-blue-100 text-blue-800
                                            @elseif($repair->repair_type_color == 'purple') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $repair->repair_type_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $repair->technician->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($repair->status_color == 'green') bg-green-100 text-green-800
                                            @elseif($repair->status_color == 'yellow') bg-yellow-100 text-yellow-800
                                            @elseif($repair->status_color == 'blue') bg-blue-100 text-blue-800
                                            @elseif($repair->status_color == 'red') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $repair->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($repair->repair_cost > 0)
                                            {{ number_format($repair->repair_cost, 0, ',', '.') }} VNĐ
                                        @else
                                            <span class="text-gray-400">Chưa có</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('repairs.show', $repair->id) }}">      
                                                <button
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                    title="Xem chi tiết">
                                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                                </button>
                                            </a>
                                            <a href="{{ route('repairs.edit', $repair->id) }}">        
                                                <button
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                                </button>
                                            </a>
                                            <button onclick="confirmDelete({{ $repair->id }}, '{{ $repair->repair_code }}')"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                title="Xóa">
                                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-tools text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium">Chưa có phiếu sửa chữa nào</p>
                                            <p class="text-sm mt-1">Nhấn "Thêm mới" để tạo phiếu sửa chữa đầu tiên</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($repairs->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $repairs->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex items-center mb-4">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
            </div>
            <div class="text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">
                    Xác nhận xóa phiếu sửa chữa
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Bạn có chắc chắn muốn xóa phiếu sửa chữa <span id="deleteRepairCode" class="font-semibold"></span>?
                    Hành động này không thể hoàn tác.
                </p>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeDeleteModal()" 
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Hủy
                </button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                        class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(repairId, repairCode) {
            document.getElementById('deleteRepairCode').textContent = repairCode;
            document.getElementById('deleteForm').action = `/repairs/${repairId}`;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
</body>

</html> 