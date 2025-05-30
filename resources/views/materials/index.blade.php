<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý vật tư - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý vật tư</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm vật tư..."
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64" />
                    <select
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active">Còn hàng</option>
                        <option value="inactive">Hết hàng</option>
                    </select>
                </div>
                <a href="{{ asset('materials/create') }}">
                    <button
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                        <i class="fas fa-plus mr-2"></i> Thêm vật tư
                    </button>
                </a>
            </div>
        </header>
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mã vật tư</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên vật tư</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Loại</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Đơn vị</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Trạng thái</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">VT-0001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Pin lithium 3.7V
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Linh kiện điện tử</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Mới</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">VT-0002</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Module GPS NEO-6M
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Linh kiện định vị</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Mới</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">VT-0003</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Mạch điều khiển
                                Arduino Nano</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Vi điều khiển</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Mới</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">4</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">VT-0004</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Module SIM800L
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Linh kiện viễn thông</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Mới</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">VT-0005</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Vỏ hộp thiết bị
                                A3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Phụ kiện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Cũ
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">6</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">VT-0006</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Cảm biến nhiệt độ
                                DHT22</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cảm biến</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Hư hỏng
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">7</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">VT-0007</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Module nguồn
                                DC-DC Step Down</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Nguồn điện</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Mới</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">8</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">VT-0008</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Anten GSM 5dBi
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Phụ kiện viễn thông</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Cái</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Mới</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                    title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </button>
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-700">
                    Hiển thị <span class="font-medium">1</span> đến <span class="font-medium">8</span> của <span
                        class="font-medium">54</span> vật tư
                </div>
                <div class="flex space-x-1">
                    <button
                        class="px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                        Trước
                    </button>
                    <button class="px-3 py-1 rounded bg-blue-500 text-white">
                        1
                    </button>
                    <button class="px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">
                        2
                    </button>
                    <button class="px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">
                        3
                    </button>
                    <button class="px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">
                        ...
                    </button>
                    <button class="px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">
                        7
                    </button>
                    <button class="px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">
                        Sau
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
