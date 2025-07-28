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
        @php
            $user = Auth::guard('web')->user();
            $canCreate =
                $user &&
                ($user->role === 'admin' ||
                    ($user->role_id &&
                        $user->roleGroup &&
                        $user->roleGroup->hasPermission('warehouse-transfers.create')));
            $canViewDetail =
                $user &&
                ($user->role === 'admin' ||
                    ($user->role_id &&
                        $user->roleGroup &&
                        $user->roleGroup->hasPermission('warehouse-transfers.view_detail')));
            $canEdit =
                $user &&
                ($user->role === 'admin' ||
                    ($user->role_id &&
                        $user->roleGroup &&
                        $user->roleGroup->hasPermission('warehouse-transfers.edit')));
            $canDelete =
                $user &&
                ($user->role === 'admin' ||
                    ($user->role_id &&
                        $user->roleGroup &&
                        $user->roleGroup->hasPermission('warehouse-transfers.delete')));
            $canApprove =
                $user &&
                ($user->role === 'admin' ||
                    ($user->role_id &&
                        $user->roleGroup &&
                        $user->roleGroup->hasPermission('warehouse-transfers.approve')));
        @endphp
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý chuyển kho</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <form action="{{ route('warehouse-transfers.index') }}" method="GET"
                    class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                    <div class="flex gap-2 w-full md:w-auto">
                        <input type="text" name="search" placeholder="Tìm kiếm theo mã, vật tư..."
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64"
                            value="{{ $search ?? '' }}" />
                        <select name="filter"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                            <option value="">Bộ lọc</option>
                            <option value="material" {{ isset($filter) && $filter == 'material' ? 'selected' : '' }}>Vật tư</option>
                            <option value="source" {{ isset($filter) && $filter == 'source' ? 'selected' : '' }}>Kho nguồn</option>
                            <option value="destination" {{ isset($filter) && $filter == 'destination' ? 'selected' : '' }}>Kho đích</option>
                            <option value="status" {{ isset($filter) && $filter == 'status' ? 'selected' : '' }}>Trạng thái</option>
                        </select>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                </form>
                @if ($canCreate)
                    <a href="{{ route('warehouse-transfers.create') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                        <i class="fas fa-exchange-alt mr-2"></i> Tạo phiếu chuyển kho
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
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã chuyển kho</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vật tư</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kho nguồn</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kho đích</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày chuyển</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nhân viên</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($warehouseTransfers as $key => $transfer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $warehouseTransfers->firstItem() + $key }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $transfer->transfer_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php
                                        $materialName = 'Không xác định';
                                        
                                        // Lấy thông tin từ warehouse_transfer_materials để xác định loại item
                                        $transferMaterial = $transfer->materials->first();
                                        if ($transferMaterial) {
                                            $itemType = $transferMaterial->type ?? 'material';
                                            $materialId = $transferMaterial->material_id;
                                            
                                            // Lấy tên dựa trên loại item
                                            switch ($itemType) {
                                                case 'material':
                                                    $material = \App\Models\Material::find($materialId);
                                                    if ($material) {
                                                        $materialName = $material->name;
                                                    }
                                                    break;
                                                case 'product':
                                                    $product = \App\Models\Product::find($materialId);
                                                    if ($product) {
                                                        $materialName = $product->name;
                                                    }
                                                    break;
                                                case 'good':
                                                    $good = \App\Models\Good::find($materialId);
                                                    if ($good) {
                                                        $materialName = $good->name;
                                                    }
                                                    break;
                                            }
                                        } else {
                                            // Fallback: thử tìm trong bảng materials trước
                                            if ($transfer->material) {
                                                $materialName = $transfer->material->name;
                                            } else {
                                                // Thử tìm trong bảng products hoặc goods
                                                $product = \App\Models\Product::find($transfer->material_id);
                                                if ($product) {
                                                    $materialName = $product->name;
                                                } else {
                                                    $good = \App\Models\Good::find($transfer->material_id);
                                                    if ($good) {
                                                        $materialName = $good->name;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    {{ $materialName }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $transfer->source_warehouse->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $transfer->destination_warehouse->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $transfer->transfer_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $transfer->employee ? $transfer->employee->name : 'Không có' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $transfer->notes ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = match($transfer->status) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'canceled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        $statusText = match($transfer->status) {
                                            'pending' => 'Chờ xử lý',
                                            'completed' => 'Hoàn thành',
                                            'canceled' => 'Đã hủy',
                                            default => 'Không xác định'
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    @if ($canViewDetail)
                                        <a href="{{ route('warehouse-transfers.show', $transfer->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($transfer->status === 'pending')
                                    @if ($canEdit)
                                        <a href="{{ route('warehouse-transfers.edit', $transfer->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                            title="Sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                        @if ($canApprove)
                                            <form action="{{ route('warehouse-transfers.approve', $transfer->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                    onclick="return confirm('Bạn có chắc chắn muốn duyệt phiếu chuyển kho này?')"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                                    title="Duyệt">
                                                    <i class="fas fa-check text-green-500 group-hover:text-white"></i>
                                                </button>
                                            </form>
                                        @endif

                                    @if ($canDelete)
                                            <form action="{{ route('warehouse-transfers.destroy', $transfer->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu chuyển kho này?')"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                            title="Xóa">
                                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                        </button>
                                            </form>
                                    @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ liệu
                                    phiếu chuyển kho</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    @if ($warehouseTransfers->count() > 0)
                        Hiển thị {{ $warehouseTransfers->firstItem() }} đến {{ $warehouseTransfers->lastItem() }} của
                        {{ $warehouseTransfers->total() }} bản ghi
                    @else
                        Không có bản ghi nào
                    @endif
                </div>

                <div class="flex space-x-1">
                    {{ $warehouseTransfers->links('pagination::tailwind') }}
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
            document.getElementById('customerNameToDelete').innerText = "phiếu chuyển kho " + name;

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
