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
                <a href="{{ asset('repair') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i> Thêm mới
                </a>
            </div>
        </header>

        <main class="p-6">
            <!-- Filter and Search -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-6 border border-gray-100">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" id="search_input" placeholder="Tìm kiếm mã bảo hành, thiết bị, khách hàng..."
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <select id="status_filter" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tất cả trạng thái</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="in_progress">Đang tiến hành</option>
                            <option value="pending">Chờ xử lý</option>
                            <option value="canceled">Đã hủy</option>
                        </select>
                        <select id="repair_type_filter" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tất cả loại sửa chữa</option>
                            <option value="maintenance">Bảo trì định kỳ</option>
                            <option value="repair">Sửa chữa lỗi</option>
                            <option value="replacement">Thay thế linh kiện</option>
                            <option value="upgrade">Nâng cấp</option>
                            <option value="other">Khác</option>
                        </select>
                        <div class="relative">
                            <input type="date" id="date_from" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="mx-1">-</span>
                            <input type="date" id="date_to" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button id="filter_btn" class="bg-blue-100 text-blue-600 hover:bg-blue-200 px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-filter mr-1"></i> Lọc
                        </button>
                        <button id="reset_filter_btn" class="bg-gray-100 text-gray-600 hover:bg-gray-200 px-4 py-2 rounded-lg transition-colors">
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" id="sort_id">
                                        ID <i class="fas fa-sort ml-1 text-gray-400"></i>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center cursor-pointer" id="sort_date">
                                        Ngày <i class="fas fa-sort ml-1 text-gray-400"></i>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mã bảo hành
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thiết bị
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
                                    Thao tác
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="repairs_table_body">
                            <!-- Sample data rows will be replaced with actual data -->
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">REP001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2023-05-15</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="font-medium text-blue-600">W12345</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <span class="font-medium">DEV001</span>
                                        <span class="mx-1">-</span>
                                        <span>Bộ điều khiển chính</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Công ty TNHH ABC</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Bảo trì định kỳ
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Nguyễn Văn A</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Hoàn thành
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ asset('repair_detail') }}">      
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                title="Xem">
                                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <a href="{{ asset('repair_edit') }}">        
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                title="Sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                            title="Xóa">
                                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">REP002</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2023-08-20</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="font-medium text-blue-600">W12345</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <span class="font-medium">DEV002</span>
                                        <span class="mx-1">-</span>
                                        <span>Cảm biến nhiệt độ</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Công ty TNHH ABC</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Sửa chữa lỗi
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Trần Văn B</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Hoàn thành
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ asset('repair_detail') }}">      
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                title="Xem">
                                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <a href="{{ asset('repair_edit') }}">        
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                title="Sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                            title="Xóa">
                                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">REP003</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2023-10-05</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="font-medium text-blue-600">W67890</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <span class="font-medium">DEV003</span>
                                        <span class="mx-1">-</span>
                                        <span>Màn hình giám sát</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Công ty CP XYZ</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Thay thế linh kiện
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Lê Thị C</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Đang tiến hành
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ asset('repair_detail') }}">      
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                                title="Xem">
                                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <a href="{{ asset('repair_edit') }}">        
                                            <button
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                                title="Sửa">
                                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                            title="Xóa">
                                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-3 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Trước
                        </a>
                        <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
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
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Trang trước</span>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <a href="#" aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    1
                                </a>
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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
            const repairTypeFilter = document.getElementById('repair_type_filter');
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
                console.log('Repair Type:', repairTypeFilter.value);
                console.log('Date From:', dateFrom.value);
                console.log('Date To:', dateTo.value);
                
                // Ở đây sẽ gọi API hoặc xử lý dữ liệu để lọc và cập nhật bảng
                // Hiện tại chỉ là mã giả để minh họa
                alert('Đã áp dụng bộ lọc!');
            });
            
            // Xử lý sự kiện đặt lại bộ lọc
            resetFilterBtn.addEventListener('click', function() {
                searchInput.value = '';
                statusFilter.value = '';
                repairTypeFilter.value = '';
                dateFrom.value = '';
                dateTo.value = '';
                
                // Đặt lại dữ liệu bảng về mặc định
                // Ở đây sẽ gọi API hoặc xử lý dữ liệu để cập nhật bảng
                alert('Đã đặt lại bộ lọc!');
            });
            
            // Xử lý sự kiện sắp xếp
            let idSortDirection = 'asc';
            sortId.addEventListener('click', function() {
                idSortDirection = idSortDirection === 'asc' ? 'desc' : 'asc';
                const icon = this.querySelector('i');
                icon.className = idSortDirection === 'asc' ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1';
                
                // Mã xử lý sắp xếp theo ID
                console.log('Sorting by ID:', idSortDirection);
                
                // Ở đây sẽ gọi API hoặc xử lý dữ liệu để sắp xếp và cập nhật bảng
                alert(`Đã sắp xếp theo ID (${idSortDirection === 'asc' ? 'tăng dần' : 'giảm dần'})!`);
            });
            
            let dateSortDirection = 'asc';
            sortDate.addEventListener('click', function() {
                dateSortDirection = dateSortDirection === 'asc' ? 'desc' : 'asc';
                const icon = this.querySelector('i');
                icon.className = dateSortDirection === 'asc' ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1';
                
                // Mã xử lý sắp xếp theo ngày
                console.log('Sorting by Date:', dateSortDirection);
                
                // Ở đây sẽ gọi API hoặc xử lý dữ liệu để sắp xếp và cập nhật bảng
                alert(`Đã sắp xếp theo ngày (${dateSortDirection === 'asc' ? 'tăng dần' : 'giảm dần'})!`);
            });
            
            // Xử lý xóa bản ghi
            const deleteLinks = document.querySelectorAll('.fa-trash');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Bạn có chắc chắn muốn xóa bản ghi sửa chữa này?')) {
                        // Xử lý xóa bản ghi
                        alert('Đã xóa bản ghi!');
                    }
                });
            });
        });
    </script>
</body>

</html> 