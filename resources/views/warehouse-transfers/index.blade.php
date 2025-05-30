<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý chuyển kho - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Quản lý chuyển kho</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm theo mã, serial, vật tư..." class="h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700 w-full md:w-64" />
                    <select class="h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-700">
                        <option value="">Bộ lọc</option>
                        <option value="serial">Serial</option>
                        <option value="material">Vật tư</option>
                        <option value="source">Kho nguồn</option>
                        <option value="destination">Kho đích</option>
                        <option value="status">Trạng thái</option>
                    </select>
                </div>
                <a href="{{ url('/warehouse-transfers/create') }}" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center transition-colors w-full md:w-auto">
                    <i class="fas fa-exchange-alt mr-2"></i> Tạo phiếu chuyển kho
                </a>
            </div>
        </header>
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã chuyển kho</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Serial</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vật tư</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kho nguồn</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kho đích</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày chuyển</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nhân viên</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">CK001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SER-VT001-001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Ốc vít 10mm</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho chính</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho linh kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn A</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                    Hoàn thành
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Chuyển bổ sung</td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/warehouse-transfers/1') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/warehouse-transfers/1/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(1, 'CK001')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">CK002</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SER-VT002-005</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Ống nhựa PVC 20mm</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho chính</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho phụ</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">02/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Trần Thị B</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                    Hoàn thành
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">-</td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/warehouse-transfers/2') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/warehouse-transfers/2/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(2, 'CK002')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">CK003</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SER-VT003-002</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Dây điện 2.5mm</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho phụ</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho chính</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">03/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lê Văn C</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                    Đang chuyển
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cần gấp</td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/warehouse-transfers/3') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/warehouse-transfers/3/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(3, 'CK003')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">CK004</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SER-VT004-010</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bóng đèn LED 10W</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho chính</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho linh kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">04/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Phạm Thị D</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">
                                    Đã hủy
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Hàng bị lỗi</td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/warehouse-transfers/4') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/warehouse-transfers/4/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(4, 'CK004')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">CK005</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SER-VT005-007</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Keo dán 2 thành phần</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho phụ</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kho linh kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">05/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn A</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                    Chờ xác nhận
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">-</td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/warehouse-transfers/5') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/warehouse-transfers/5/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(5, 'CK005')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Hiển thị 1 đến 5 của 15 bản ghi
                </div>
                
                <div class="flex space-x-1">
                    <button class="h-8 px-3 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="h-8 px-3 bg-blue-500 border border-blue-500 rounded-md text-sm text-white hover:bg-blue-600">1</button>
                    <button class="h-8 px-3 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">2</button>
                    <button class="h-8 px-3 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">3</button>
                    <button class="h-8 px-3 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </main>
    </div>

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
        
       
    </script>
</body>
</html> 