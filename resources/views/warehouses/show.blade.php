<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết kho hàng - SGL</title>
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
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết kho hàng</h1>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('warehouses.edit', $warehouse->id) }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <a href="{{ route('warehouses.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
            <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
            @endif
        
            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">{{ $warehouse->name }}</h2>
                                <p class="text-gray-600 mt-1">Mã kho: {{ $warehouse->code }}</p>
                                <p class="text-gray-600 mt-1">{{ $warehouse->address }}</p>
                            </div>
                            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end">
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-user text-blue-500 mr-2"></i>
                                    <span class="text-blue-500 font-medium">{{ $warehouse->manager }}</span>
                                </div>
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-phone text-blue-500 mr-2"></i>
                                    <span class="text-blue-500">{{ $warehouse->phone }}</span>
                                </div>
                                @if($warehouse->email)
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-envelope text-blue-500 mr-2"></i>
                                    <span class="text-blue-500">{{ $warehouse->email }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin chi tiết -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Thông tin kho hàng</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500">Mã kho</p>
                        <p class="text-gray-900 font-medium">{{ $warehouse->code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tên kho</p>
                        <p class="text-gray-900 font-medium">{{ $warehouse->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Địa chỉ</p>
                        <p class="text-gray-900">{{ $warehouse->address }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Người quản lý</p>
                        <p class="text-gray-900">{{ $warehouse->manager }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Số điện thoại</p>
                        <p class="text-gray-900">{{ $warehouse->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="text-gray-900">{{ $warehouse->email ?: 'Chưa có' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ngày tạo</p>
                        <p class="text-gray-900">{{ $warehouse->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Cập nhật lần cuối</p>
                        <p class="text-gray-900">{{ $warehouse->updated_at->format('d/m/Y') }}</p>
                    </div>
                </div>
                
                @if($warehouse->description)
                <div class="border-t border-gray-200 pt-4 mb-6">
                    <p class="text-sm text-gray-500">Mô tả kho hàng</p>
                    <p class="text-gray-900 mt-1">{{ $warehouse->description }}</p>
                </div>
                @endif

                <!-- Bảng hiển thị linh kiện và thành phẩm -->
                <div class="border-t border-gray-200 pt-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tồn kho</h3>
                    
                    <!-- Tab navigation -->
                    <div class="mb-4 border-b border-gray-200">
                        <ul class="flex flex-wrap -mb-px" id="inventoryTabs" role="tablist">
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 border-blue-500 rounded-t-lg text-blue-600 font-medium" 
                                        id="components-tab" 
                                        data-tabs-target="#components" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="components" 
                                        aria-selected="true"
                                        onclick="openTab('components')">
                                    Linh kiện
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg text-gray-500 hover:text-gray-600 hover:border-gray-300"
                                        id="products-tab" 
                                        data-tabs-target="#products" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="products" 
                                        aria-selected="false"
                                        onclick="openTab('products')">
                                    Thành phẩm
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Tab content -->
                    <div id="inventoryTabContent">
                        <!-- Linh kiện tab -->
                        <div class="block" id="components" role="tabpanel" aria-labelledby="components-tab">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Mã linh kiện
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tên linh kiện
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Loại
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Số lượng tồn
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Đơn vị
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <!-- Dữ liệu mẫu - sẽ được thay thế bằng dữ liệu thực từ backend -->
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">LK001</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Bo mạch chủ</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Điện tử</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">150</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Cái</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">LK002</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Chip xử lý</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Điện tử</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">85</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Cái</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">LK003</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Vỏ máy tính</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Phụ kiện</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">52</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Cái</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Thành phẩm tab -->
                        <div class="hidden" id="products" role="tabpanel" aria-labelledby="products-tab">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Mã thành phẩm
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tên thành phẩm
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Danh mục
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Số lượng tồn
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <!-- Dữ liệu mẫu - sẽ được thay thế bằng dữ liệu thực từ backend -->
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">SP001</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Máy tính để bàn Gaming</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Máy tính</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">25</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">SP002</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Laptop Business</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Laptop</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">18</td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">SP003</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Tablet Pro</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Tablet</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">32</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Có thể thêm các section khác ở đây: Danh sách sản phẩm trong kho, lịch sử nhập xuất, ... -->
                
                <div class="mt-6 flex justify-end">
                    <button onclick="confirmDelete()" 
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i> Xóa kho hàng
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal xác nhận xóa -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xác nhận xóa</h3>
            <p class="text-gray-700 mb-6">Bạn có chắc chắn muốn xóa kho hàng <span class="font-semibold">{{ $warehouse->code }}</span>? Hành động này không thể hoàn tác.</p>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeDeleteModal()">
                    Hủy
                </button>
                <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                        Xác nhận xóa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function openTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('#inventoryTabContent > div');
            tabContents.forEach(tab => tab.classList.add('hidden'));
            
            // Show the selected tab content
            document.getElementById(tabName).classList.remove('hidden');
            
            // Update tab button styling
            const tabButtons = document.querySelectorAll('#inventoryTabs button');
            tabButtons.forEach(button => {
                button.classList.remove('border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-600', 'hover:border-gray-300');
            });
            
            // Style active tab
            const activeButton = document.getElementById(`${tabName}-tab`);
            activeButton.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-600', 'hover:border-gray-300');
            activeButton.classList.add('border-blue-500', 'text-blue-600');
        }
    </script>
</body>

</html> 