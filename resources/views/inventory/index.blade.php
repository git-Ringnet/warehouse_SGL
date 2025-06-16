<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý xuất kho - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Quản lý xuất kho</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.dispatch.create') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i> Tạo phiếu xuất
                </a>
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filter and Search -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-6 border border-gray-100">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" id="search_input"
                                placeholder="Tìm kiếm mã phiếu, thành phẩm, khách hàng..."
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <select id="status_filter"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tất cả trạng thái</option>
                            <option value="completed">Đã hoàn thành</option>
                            <option value="pending">Chờ xử lý</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                        <div class="relative">
                            <input type="date" id="date_from"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="mx-1">-</span>
                            <input type="date" id="date_to"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button id="filter_btn"
                            class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-filter mr-1"></i> Lọc
                        </button>
                        <button id="reset_filter_btn"
                            class="bg-gray-100 text-gray-600 hover:bg-gray-200 px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-redo-alt mr-1"></i> Đặt lại
                        </button>
                    </div>
                </div>
            </div>

            <!-- Records Table -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" id="sort_id">
                                        Mã phiếu <i class="fas fa-sort ml-1 text-gray-400"></i>
                                    </div>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" id="sort_date">
                                        Ngày xuất <i class="fas fa-sort ml-1 text-gray-400"></i>
                                    </div>
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Người nhận
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Số lượng SP
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dự án
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Người đại diện dự án
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Người tạo
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="dispatch_table_body">
                            @forelse($dispatches as $dispatch)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $dispatch->dispatch_code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispatch->dispatch_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispatch->project_receiver }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ $dispatch->total_items }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispatch->dispatch_type === 'project' ? $dispatch->project_receiver : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispatch->companyRepresentative->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispatch->creator->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $dispatch->status_color }}-100 text-{{ $dispatch->status_color }}-800">
                                        {{ $dispatch->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('inventory.dispatch.show', $dispatch->id) }}">
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                title="Xem">
                                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        @if(!in_array($dispatch->status, ['completed', 'cancelled']))
                                        <a href="{{ route('inventory.dispatch.edit', $dispatch->id) }}">
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                title="Sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        @endif
                                        @if($dispatch->status === 'pending')
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group approve-btn"
                                            title="Duyệt phiếu xuất" data-id="{{ $dispatch->id }}">
                                            <i class="fas fa-check text-green-500 group-hover:text-white"></i>
                                        </button>
                                        @endif
                                        @if(!in_array($dispatch->status, ['completed', 'cancelled']))
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group cancel-btn"
                                            title="Hủy phiếu xuất" data-id="{{ $dispatch->id }}">
                                            <i class="fas fa-times text-red-500 group-hover:text-white"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                    Chưa có phiếu xuất kho nào
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-3 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a href="#"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Trước
                        </a>
                        <a href="#"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Sau
                        </a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Hiển thị
                                <span class="font-medium">1</span>
                                đến
                                <span class="font-medium">3</span>
                                trong số
                                <span class="font-medium">3</span>
                                kết quả
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                aria-label="Pagination">
                                <a href="#"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Trang trước</span>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <a href="#" aria-current="page"
                                    class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    1
                                </a>
                                <a href="#"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Trang sau</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lọc và tìm kiếm
            const searchInput = document.getElementById('search_input');
            const statusFilter = document.getElementById('status_filter');
            const typeFilter = document.getElementById('type_filter');
            const dateFrom = document.getElementById('date_from');
            const dateTo = document.getElementById('date_to');
            const filterBtn = document.getElementById('filter_btn');
            const resetFilterBtn = document.getElementById('reset_filter_btn');

            // Sắp xếp
            const sortId = document.getElementById('sort_id');
            const sortDate = document.getElementById('sort_date');

            // Xử lý sự kiện lọc
            filterBtn.addEventListener('click', function() {
                // Mã xử lý lọc và tìm kiếm dữ liệu
                console.log('Filtering data with:');
                console.log('Search:', searchInput.value);
                console.log('Status:', statusFilter.value);
                console.log('Type:', typeFilter.value);
                console.log('Date From:', dateFrom.value);
                console.log('Date To:', dateTo.value);

                // Ở đây sẽ gọi API hoặc xử lý dữ liệu để lọc và cập nhật bảng
                alert('Đã áp dụng bộ lọc!');
            });

            // Xử lý sự kiện đặt lại bộ lọc
            resetFilterBtn.addEventListener('click', function() {
                searchInput.value = '';
                statusFilter.value = '';
                typeFilter.value = '';
                dateFrom.value = '';
                dateTo.value = '';

                // Đặt lại dữ liệu bảng về mặc định
                alert('Đã đặt lại bộ lọc!');
            });

            // Xử lý sự kiện sắp xếp
            let idSortDirection = 'asc';
            sortId.addEventListener('click', function() {
                idSortDirection = idSortDirection === 'asc' ? 'desc' : 'asc';
                const icon = this.querySelector('i');
                icon.className = idSortDirection === 'asc' ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1';

                // Mã xử lý sắp xếp theo ID
                alert(`Đã sắp xếp theo mã phiếu (${idSortDirection === 'asc' ? 'tăng dần' : 'giảm dần'})!`);
            });

            let dateSortDirection = 'asc';
            sortDate.addEventListener('click', function() {
                dateSortDirection = dateSortDirection === 'asc' ? 'desc' : 'asc';
                const icon = this.querySelector('i');
                icon.className = dateSortDirection === 'asc' ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1';

                // Mã xử lý sắp xếp theo ngày
                alert(`Đã sắp xếp theo ngày xuất (${dateSortDirection === 'asc' ? 'tăng dần' : 'giảm dần'})!`);
            });

            // Xử lý xóa bản ghi
            const deleteButtons = document.querySelectorAll('.fa-trash');
            deleteButtons.forEach(button => {
                button.closest('button').addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Bạn có chắc chắn muốn xóa phiếu xuất kho này?')) {
                        // Xử lý xóa bản ghi
                        alert('Đã xóa phiếu xuất kho!');
                    }
                });
            });

            // Các nút thao tác duyệt và hủy phiếu
            const approveButtons = document.querySelectorAll('.approve-btn');
            const cancelButtons = document.querySelectorAll('.cancel-btn');
            
            // Xử lý duyệt phiếu xuất
            approveButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const dispatchId = this.getAttribute('data-id');
                    if (confirm(`Bạn có chắc chắn muốn duyệt phiếu xuất ${dispatchId}?`)) {
                        // Gọi API hoặc xử lý duyệt phiếu
                        alert(`Đã duyệt phiếu xuất ${dispatchId}`);
                        
                        // Cập nhật trạng thái phiếu (trong thực tế sẽ reload từ server)
                        const row = this.closest('tr');
                        const statusCell = row.querySelector('td:nth-child(8)');
                        statusCell.innerHTML = `
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Đã hoàn thành
                            </span>
                        `;
                    }
                });
            });
            
            // Xử lý hủy phiếu xuất
            cancelButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const dispatchId = this.getAttribute('data-id');
                    if (confirm(`Bạn có chắc chắn muốn hủy phiếu xuất ${dispatchId}?`)) {
                        // Gọi API hoặc xử lý hủy phiếu
                        alert(`Đã hủy phiếu xuất ${dispatchId}`);
                        
                        // Cập nhật trạng thái phiếu (trong thực tế sẽ reload từ server)
                        const row = this.closest('tr');
                        const statusCell = row.querySelector('td:nth-child(8)');
                        statusCell.innerHTML = `
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Đã hủy
                            </span>
                        `;
                    }
                });
            });
        });
    </script>
</body>

</html> 