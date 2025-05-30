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
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý nhập kho</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm theo mã, tên vật tư..." class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64" />
                    <select class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                        <option value="">Bộ lọc</option>
                        <option value="material_code">Mã vật tư</option>
                        <option value="material_name">Tên vật tư</option>
                        <option value="category">Phân loại</option>
                        <option value="supplier">Nhà cung cấp</option>
                    </select>
                </div>
                <a href="{{ url('/inventory-imports/create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                    <i class="fas fa-plus mr-2"></i> Tạo phiếu nhập
                </a>
            </div>
        </header>
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã vật tư</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Đơn vị</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phân loại</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày nhập kho</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nhà cung cấp</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">VT001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Ốc vít 10mm</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Kg</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Linh kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">100</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">01/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty TNHH ABC</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nhập đợt 1</td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/inventory-imports/1') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/inventory-imports/1/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(1, 'VT001')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">VT002</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Ống nhựa PVC 20mm</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Mét</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Vật tư</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">50</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">02/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty Vật liệu XYZ</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nhập bổ sung</td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/inventory-imports/2') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/inventory-imports/2/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(2, 'VT002')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">VT003</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Dây điện 2.5mm</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Mét</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Điện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">200</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">03/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty Điện máy MNO</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nhập theo đơn hàng D123</td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/inventory-imports/3') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/inventory-imports/3/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(3, 'VT003')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">VT004</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bóng đèn LED 10W</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Điện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">30</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">04/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty Điện máy MNO</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">-</td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/inventory-imports/4') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/inventory-imports/4/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(4, 'VT004')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">VT005</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Keo dán 2 thành phần</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Tuýp</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Hóa chất</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">20</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">05/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Công ty Hóa chất LKM</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Đơn gấp</td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/inventory-imports/5') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/inventory-imports/5/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(5, 'VT005')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Hiển thị 1 đến 5 của 25 bản ghi
                </div>
                
                <div class="flex space-x-1">
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="px-3 py-1 bg-blue-500 border border-blue-500 rounded-md text-sm text-white hover:bg-blue-600">1</button>
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">2</button>
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">3</button>
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">4</button>
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">5</button>
                    <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
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
    </script>
</body>
</html> 