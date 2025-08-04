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
                @if ($warehouse->status !== 'deleted' && !$warehouse->is_hidden)
                    <a href="{{ route('warehouses.edit', $warehouse->id) }}"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                    </a>
                @endif
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
                                <p class="text-gray-600 mt-1">{{ $warehouse->address ?? 'Không có' }}</p>
                            </div>
                            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end">
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-user text-blue-500 mr-2"></i>
                                    <span class="text-blue-500 font-medium">{{ $warehouse->manager }}</span>
                                </div>
                                <div class="flex items-center mt-1">
                                    @if($warehouse->status === 'deleted')
                                        <span class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Đã xóa</span>
                                    @elseif($warehouse->is_hidden)
                                        <span class="ml-2 px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Đã ẩn</span>
                                    @endif
                                </div>
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
                        <p class="text-gray-900">{{ $warehouse->address ?? 'Không có' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Người quản lý</p>
                        <p class="text-gray-900">{{ $warehouse->managerEmployee->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ngày tạo</p>
                        <p class="text-gray-900">{{ now()->setTimezone('Asia/Ho_Chi_Minh')->parse($warehouse->created_at)->format('H:i d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Cập nhật lần cuối</p>
                        <p class="text-gray-900">{{ now()->setTimezone('Asia/Ho_Chi_Minh')->parse($warehouse->updated_at)->format('H:i d/m/Y') }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4 mb-6">
                    <p class="text-sm text-gray-500">Mô tả kho hàng</p>
                    <p class="text-gray-900 mt-1">{{ $warehouse->description ?? 'Không có' }}</p>
                </div>

                <!-- Bảng hiển thị linh kiện và thành phẩm -->
                <div class="border-t border-gray-200 pt-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tồn kho</h3>

                    <!-- Tab navigation -->
                    <div class="mb-4 border-b border-gray-200">
                        <ul class="flex flex-wrap -mb-px" id="inventoryTabs" role="tablist">
                            <li class="mr-2" role="presentation">
                                <button
                                    class="inline-block p-4 border-b-2 border-blue-500 rounded-t-lg text-blue-600 font-medium"
                                    id="components-tab" data-tabs-target="#components" type="button" role="tab"
                                    aria-controls="components" aria-selected="true" onclick="openTab('components')">
                                    Vật tư ({{ count($materials) }})
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button
                                    class="inline-block p-4 border-b-2 border-transparent rounded-t-lg text-gray-500 hover:text-gray-600 hover:border-gray-300"
                                    id="products-tab" data-tabs-target="#products" type="button" role="tab"
                                    aria-controls="products" aria-selected="false" onclick="openTab('products')">
                                    Thành phẩm ({{ count($products) }})
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button
                                    class="inline-block p-4 border-b-2 border-transparent rounded-t-lg text-gray-500 hover:text-gray-600 hover:border-gray-300"
                                    id="goods-tab" data-tabs-target="#goods" type="button" role="tab"
                                    aria-controls="goods" aria-selected="false" onclick="openTab('goods')">
                                    Hàng hóa ({{ count($goods) }})
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab content -->
                    <div id="inventoryTabContent">
                        <!-- Vật tư tab -->
                        <div class="block" id="components" role="tabpanel" aria-labelledby="components-tab">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Mã vật tư
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tên vật tư
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Loại
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Số lượng tồn
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Đơn vị
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Thao tác
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($materials as $material)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $material['code'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $material['name'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $material['category'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($material['quantity']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $material['unit'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                                    <a href="{{ route('materials.show', $material['id']) }}"
                                                        class="hover:text-blue-800">
                                                        Xem chi tiết
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6"
                                                    class="px-6 py-4 text-center text-sm text-gray-500">
                                                    Không có vật tư nào trong kho này
                                                </td>
                                            </tr>
                                        @endforelse
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
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Mã thành phẩm
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tên thành phẩm
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Mô tả
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Số lượng tồn
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Thao tác
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($products as $product)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $product['code'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $product['name'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ Str::limit($product['description'] ?? '', 50) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($product['quantity']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                                    <a href="{{ route('products.show', $product['id']) }}"
                                                        class="hover:text-blue-800">
                                                        Xem chi tiết
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5"
                                                    class="px-6 py-4 text-center text-sm text-gray-500">
                                                    Không có thành phẩm nào trong kho này
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Hàng hóa tab -->
                        <div class="hidden" id="goods" role="tabpanel" aria-labelledby="goods-tab">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Mã hàng hóa
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tên hàng hóa
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Loại
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Số lượng tồn
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Đơn vị
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Thao tác
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($goods as $good)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $good['code'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $good['name'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $good['category'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($good['quantity']) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $good['unit'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                                    <a href="{{ route('goods.show', $good['id']) }}"
                                                        class="hover:text-blue-800">
                                                        Xem chi tiết
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6"
                                                    class="px-6 py-4 text-center text-sm text-gray-500">
                                                    Không có hàng hóa nào trong kho này
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Có thể thêm các section khác ở đây: Danh sách thành phẩm trong kho, lịch sử nhập xuất, ... -->
                @if ($warehouse->status !== 'deleted' && !$warehouse->is_hidden)
                    <div class="mt-6 flex justify-end">
                        <button onclick="confirmDelete()"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-trash mr-2"></i> Xóa kho hàng
                        </button>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Modal thông báo lỗi -->
    <div id="errorModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-bold text-gray-900">Không thể xóa</h3>
                </div>
            </div>
            <p id="errorMessage" class="text-gray-700 mb-6"></p>
            <div class="flex justify-end">
                <button type="button"
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                    onclick="closeErrorModal()">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- Modal xác nhận xóa khi inventory = 0 -->
    <div id="confirmModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Xác nhận thao tác</h3>
            <p class="text-gray-700 mb-6">Thao tác xóa có thể làm mất dữ liệu. Bạn muốn xác nhận ngừng sử dụng và ẩn hạng mục này thay cho việc xóa?</p>
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeConfirmModal()">
                    Hủy
                </button>
                <form id="hideForm" action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="action" value="hide">
                    <button type="submit"
                        class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors">
                        Có (Ẩn hạng mục)
                    </button>
                </form>
                <form id="deleteForm" action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="action" value="delete">
                    <button type="submit"
                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                        Không (Xóa)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            // Kiểm tra tồn kho trước khi xóa
            fetch(`/warehouses/{{ $warehouse->id }}/check-inventory`)
                .then(response => response.json())
                .then(data => {
                    if (data.hasInventory) {
                        // Có tồn kho - hiển thị modal lỗi
                        document.getElementById('errorMessage').textContent = 
                            `Không thể xóa kho hàng vì còn tồn kho ${data.totalQuantity.toLocaleString()}. Vui lòng xuất hết tồn kho trước khi xóa.`;
                        document.getElementById('errorModal').classList.remove('hidden');
                    } else {
                        // Không có tồn kho - hiển thị modal xác nhận
                        document.getElementById('confirmModal').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback: hiển thị modal xác nhận nếu có lỗi
                    document.getElementById('confirmModal').classList.remove('hidden');
                });
        }

        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
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
                button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-600',
                    'hover:border-gray-300');
            });

            // Style active tab
            const activeButton = document.getElementById(`${tabName}-tab`);
            activeButton.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-600',
                'hover:border-gray-300');
            activeButton.classList.add('border-blue-500', 'text-blue-600');
        }
    </script>
</body>

</html>
