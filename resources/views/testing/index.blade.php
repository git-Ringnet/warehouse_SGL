<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý kiểm thử - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Quản lý kiểm thử</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm mã phiếu, thiết bị..." class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64 h-10" />
                    <select class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 h-10">
                        <option value="">Loại kiểm thử</option>
                        <option value="new_component">Linh kiện đầu vào</option>
                        <option value="defective">Module bị lỗi</option>
                        <option value="new_device">Thiết bị mới lắp ráp</option>
                    </select>
                </div>
                <a href="{{ url('/testing/create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center h-10">
                    <i class="fas fa-plus-circle mr-2"></i> Tạo phiếu kiểm thử
                </a>
            </div>
        </header>
        <main class="p-6">
            <!-- Tab Navigation -->
            <div class="mb-6 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li class="mr-2">
                        <a href="{{ url('/testing') }}" class="inline-flex items-center p-4 border-b-2 {{ request()->query('type') ? 'border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-600' : 'border-blue-500 text-blue-600' }} rounded-t-lg group">
                            <i class="fas fa-clipboard-check mr-2"></i> Tất cả phiếu kiểm thử
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="{{ url('/testing?type=new_component') }}" class="inline-flex items-center p-4 border-b-2 {{ request()->query('type') == 'new_component' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-600' }} rounded-t-lg group">
                            <i class="fas fa-microchip mr-2"></i> Linh kiện đầu vào
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="{{ url('/testing?type=defective') }}" class="inline-flex items-center p-4 border-b-2 {{ request()->query('type') == 'defective' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-600' }} rounded-t-lg group">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Module lỗi
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="{{ url('/testing?type=new_device') }}" class="inline-flex items-center p-4 border-b-2 {{ request()->query('type') == 'new_device' ? 'border-blue-500 text-blue-600' : 'border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-600' }} rounded-t-lg group">
                            <i class="fas fa-box mr-2"></i> Thiết bị mới lắp ráp
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã phiếu</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại kiểm thử</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thiết bị/Module</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Serial/Mã</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người kiểm thử</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày kiểm thử</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kết quả</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <!-- Phiếu kiểm thử 1: Linh kiện mới -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">QA-24060001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Linh kiện đầu vào</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Module 4G</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4G-MOD-2305621</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguyễn Văn A</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">15/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Hoàn thành</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/testing/1') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/testing/1/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(1, 'QA-24060001')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Phiếu kiểm thử 2: Module lỗi -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">QA-24060002</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs">Module lỗi</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Module Công suất</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">PWR-2405102</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Trần Văn B</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">12/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Hoàn thành</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Không sửa được</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/testing/2') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/testing/2/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(2, 'QA-24060002')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Phiếu kiểm thử 3: Thiết bị mới lắp ráp -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">QA-24060003</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">Thiết bị mới lắp ráp</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SGL SmartBox</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">SB-2406057</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lê Thị C</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">14/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Đang kiểm thử</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/testing/3') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/testing/3/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(3, 'QA-24060003')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Phiếu kiểm thử 4: Linh kiện mới -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">QA-24060004</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Linh kiện đầu vào</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Android Box</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">AND-2406015</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Phạm Văn D</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">13/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Hoàn thành</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Không đạt</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/testing/4') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/testing/4/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(4, 'QA-24060004')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Phiếu kiểm thử 5: Module lỗi -->
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">QA-24050005</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs">Module lỗi</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Module IoTs</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">IOT-2405089</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Lê Văn E</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">30/05/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Hoàn thành</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Sửa thành công</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <a href="{{ url('/testing/5') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                <a href="{{ url('/testing/5/edit') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                <button onclick="openDeleteModal(5, 'QA-24050005')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">Hiển thị 1-5 của 25 phiếu kiểm thử</div>
                <div class="flex space-x-1">
                <button class="px-3 py-1 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <a href="#" class="px-3 py-1 rounded border border-blue-500 bg-blue-500 text-white">1</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">2</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">3</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">4</a>
                    <a href="#" class="px-3 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">5</a>
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

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html> 