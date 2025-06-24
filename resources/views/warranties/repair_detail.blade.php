<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sửa chữa & bảo trì - SGL</title>
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
                <a href="{{ asset('repair') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chi tiết sửa chữa & bảo trì</h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('repairs.edit', $repair->id) }}">
                    <button
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                    </button>
                </a>
                <button type="button"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                </button>
            </div>
        </header>

        <main class="p-6">
            <!-- Header Info -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <div class="flex flex-col lg:flex-row justify-between gap-4">
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-lg font-semibold text-gray-800 mr-2">Mã phiếu:</span>
                            <span class="text-lg text-blue-600 font-bold">{{ $repair->repair_code }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Thiết bị:</span>
                            <span class="text-sm text-gray-700">
                                @if ($repair->repairItems->count() > 0)
                                    {{ $repair->repairItems->pluck('device_code')->join(', ') }}
                                @else
                                    Chưa có thiết bị
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Mã bảo hành:</span>
                            <span class="text-sm text-blue-600">{{ $repair->warranty_code ?? 'Không có' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Khách hàng:</span>
                            <span
                                class="text-sm text-gray-700">{{ $repair->warranty->customer_name ?? 'Chưa xác định' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Ngày sửa chữa:</span>
                            <span class="text-sm text-gray-700">{{ $repair->repair_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Kỹ thuật viên:</span>
                            <span
                                class="text-sm text-gray-700">{{ $repair->technician->name ?? 'Chưa xác định' }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Kho sửa chữa:</span>
                            <span class="text-sm text-gray-700">{{ $repair->warehouse->name ?? 'Chưa xác định' }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Loại sửa chữa:</span>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if ($repair->repair_type == 'maintenance') bg-blue-100 text-blue-800
                                @elseif($repair->repair_type == 'repair') bg-yellow-100 text-yellow-800
                                @elseif($repair->repair_type == 'replacement') bg-purple-100 text-purple-800
                                @elseif($repair->repair_type == 'upgrade') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @switch($repair->repair_type)
                                    @case('maintenance')
                                        Bảo trì định kỳ
                                    @break

                                    @case('repair')
                                        Sửa chữa lỗi
                                    @break

                                    @case('replacement')
                                        Thay thế linh kiện
                                    @break

                                    @case('upgrade')
                                        Nâng cấp
                                    @break

                                    @default
                                        Khác
                                    @break
                                @endswitch
                            </span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Trạng thái:</span>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if ($repair->status == 'completed') bg-green-100 text-green-800
                                @elseif($repair->status == 'in_progress') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @switch($repair->status)
                                    @case('in_progress')
                                        Đang xử lý
                                    @break

                                    @case('completed')
                                        Hoàn thành
                                    @break

                                    @default
                                        {{ ucfirst($repair->status) }}
                                    @break
                                @endswitch
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repair Information -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-tools text-blue-500 mr-2"></i>
                    Thông tin sửa chữa
                </h2>

                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-700 mb-2">Mô tả sửa chữa:</h3>
                    <div class="bg-gray-50 p-4 rounded-lg text-gray-700">
                        <p>{{ $repair->repair_description }}</p>
                        @if ($repair->repair_notes)
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <h4 class="font-medium text-gray-700 mb-2">Ghi chú:</h4>
                                <p>{{ $repair->repair_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-700 mb-2">Linh kiện đã thay thế:</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã thiết bị
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã linh kiện
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên linh kiện
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Serial cũ
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Serial mới
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số lượng
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kho nguồn
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kho đích
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ghi chú
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($repair->materialReplacements as $replacement)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $replacement->device_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $replacement->material_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $replacement->material_name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            @if($replacement->old_serials && count($replacement->old_serials) > 0)
                                                <div class="space-y-1">
                                                    @foreach($replacement->old_serials as $serial)
                                                        <span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded">{{ $serial }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">Không có</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            @if($replacement->new_serials && count($replacement->new_serials) > 0)
                                                <div class="space-y-1">
                                                    @foreach($replacement->new_serials as $serial)
                                                        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded">{{ $serial }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">Không có</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $replacement->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $replacement->sourceWarehouse->name ?? 'Không xác định' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $replacement->targetWarehouse->name ?? 'Không xác định' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            <div class="max-w-xs">
                                                {{ $replacement->notes ?: 'Không có ghi chú' }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Không có linh kiện thay thế
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Device Information -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-microchip text-blue-500 mr-2"></i>
                    Danh sách thiết bị sửa chữa
                </h2>

                <!-- Danh sách thiết bị -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mã thiết bị
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên thiết bị
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Serial
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trạng thái
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Chú thích
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hình ảnh
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($repair->repairItems as $item)
                                <tr class="{{ $item->device_status == 'rejected' ? 'bg-red-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->device_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $item->device_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $item->device_serial ?: 'Không có' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if ($item->device_status == 'selected') bg-green-100 text-green-800
                                            @elseif($item->device_status == 'rejected') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            @switch($item->device_status)
                                                @case('selected')
                                                    Đã xử lý
                                                @break

                                                @case('rejected')
                                                    Từ chối
                                                @break

                                                @default
                                                    {{ ucfirst($item->device_status) }}
                                                @break
                                            @endswitch
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <div class="max-w-xs">
                                            <p>{{ $item->device_notes ?: 'Không có ghi chú' }}</p>
                                            @if ($item->rejected_reason)
                                                <p class="mt-1"><strong>Lý do từ chối:</strong>
                                                    {{ $item->rejected_reason }}</p>
                                                @if ($item->rejectedWarehouse)
                                                    <p class="mt-1 text-xs text-gray-500"><strong>Kho lưu trữ:</strong>
                                                        {{ $item->rejectedWarehouse->name }}</p>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if ($item->device_images && count($item->device_images) > 0)
                                            <div class="flex space-x-2">
                                                @foreach ($item->device_images as $image)
                                                    <img src="{{ asset('storage/' . $image) }}" alt="Device Image"
                                                        class="w-10 h-10 rounded object-cover cursor-pointer"
                                                        onclick="showImageModal('{{ asset('storage/' . $image) }}')">
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-xs">Chưa có ảnh</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Không có thiết bị nào trong phiếu sửa chữa này
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Lịch sử sửa chữa -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-history text-blue-500 mr-2"></i>
                            Lịch sử sửa chữa
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Timeline item: Tạo phiếu sửa chữa -->
                            <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center">
                                        <i class="fas fa-plus text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-medium text-gray-900">Tạo phiếu sửa chữa</h4>
                                        <span class="text-sm text-gray-500">{{ $repair->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="font-medium text-gray-700">Loại sửa chữa:</span>
                                            <span class="ml-1 px-2 py-1 text-xs rounded-full 
                                                @if($repair->repair_type == 'maintenance') bg-blue-100 text-blue-800
                                                @elseif($repair->repair_type == 'repair') bg-yellow-100 text-yellow-800
                                                @elseif($repair->repair_type == 'replacement') bg-purple-100 text-purple-800
                                                @elseif($repair->repair_type == 'upgrade') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $repair->repair_type_label }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Kỹ thuật viên:</span>
                                            <span class="ml-1">{{ $repair->technician->name ?? 'Không xác định' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Kho sửa chữa:</span>
                                            <span class="ml-1">{{ $repair->warehouse->name ?? 'Không xác định' }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="font-medium text-gray-700">Mô tả:</span>
                                        <p class="text-gray-600 mt-1">{{ $repair->repair_description }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline items: Cập nhật phiếu sửa chữa -->
                            @if($repair->updated_at && $repair->updated_at != $repair->created_at)
                                <div class="flex items-start space-x-4 p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-yellow-500 text-white rounded-full flex items-center justify-center">
                                            <i class="fas fa-edit text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900">Cập nhật phiếu sửa chữa</h4>
                                            <span class="text-sm text-gray-500">{{ $repair->updated_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="font-medium text-gray-700">Trạng thái hiện tại:</span>
                                            <span class="ml-1 px-2 py-1 text-xs rounded-full 
                                                @if($repair->status == 'completed') bg-green-100 text-green-800
                                                @elseif($repair->status == 'in_progress') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $repair->status_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Timeline items: Thay thế linh kiện -->
                            @foreach($repair->materialReplacements as $replacement)
                                <div class="flex items-start space-x-4 p-4 bg-orange-50 rounded-lg border-l-4 border-orange-500">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-orange-500 text-white rounded-full flex items-center justify-center">
                                            <i class="fas fa-exchange-alt text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900">Thay thế linh kiện</h4>
                                            <span class="text-sm text-gray-500">
                                                {{ $replacement->replaced_at ? $replacement->replaced_at->format('d/m/Y H:i') : 'Không xác định' }}
                                            </span>
                                        </div>
                                        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="font-medium text-gray-700">Thiết bị:</span>
                                                <span class="ml-1">{{ $replacement->device_code }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-700">Linh kiện:</span>
                                                <span class="ml-1">{{ $replacement->material_code }} - {{ $replacement->material_name }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-700">Serial cũ:</span>
                                                <div class="ml-1 flex flex-wrap gap-1">
                                                    @if($replacement->old_serials && count($replacement->old_serials) > 0)
                                                        @foreach($replacement->old_serials as $serial)
                                                            <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">{{ $serial }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-gray-400">Không có</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-700">Serial mới:</span>
                                                <div class="ml-1 flex flex-wrap gap-1">
                                                    @if($replacement->new_serials && count($replacement->new_serials) > 0)
                                                        @foreach($replacement->new_serials as $serial)
                                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">{{ $serial }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-gray-400">Không có</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-700">Kho lấy vật tư:</span>
                                                <span class="ml-1">{{ $replacement->targetWarehouse->name ?? 'Không xác định' }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-700">Kho chuyển cũ:</span>
                                                <span class="ml-1">{{ $replacement->sourceWarehouse->name ?? 'Không xác định' }}</span>
                                            </div>
                                        </div>
                                        @if($replacement->notes)
                                            <div class="mt-2">
                                                <span class="font-medium text-gray-700">Ghi chú:</span>
                                                <p class="text-gray-600 mt-1">{{ $replacement->notes }}</p>
                                            </div>
                                        @endif
                                        <div class="mt-2">
                                            <span class="font-medium text-gray-700">Thực hiện bởi:</span>
                                            <span class="ml-1">{{ $replacement->replacedBy->name ?? 'Không xác định' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Timeline items: Thiết bị bị từ chối -->
                            @foreach($repair->repairItems->where('device_status', 'rejected') as $rejectedItem)
                                <div class="flex items-start space-x-4 p-4 bg-red-50 rounded-lg border-l-4 border-red-500">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center">
                                            <i class="fas fa-times text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900">Từ chối thiết bị</h4>
                                            <span class="text-sm text-gray-500">
                                                {{ $rejectedItem->rejected_at ? $rejectedItem->rejected_at->format('d/m/Y H:i') : 'Không xác định' }}
                                            </span>
                                        </div>
                                        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="font-medium text-gray-700">Thiết bị:</span>
                                                <span class="ml-1">{{ $rejectedItem->device_code }} - {{ $rejectedItem->device_name }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-700">Serial:</span>
                                                <span class="ml-1">{{ $rejectedItem->device_serial ?: 'Không có' }}</span>
                                            </div>
                                        </div>
                                        @if($rejectedItem->rejected_reason)
                                            <div class="mt-2">
                                                <span class="font-medium text-gray-700">Lý do từ chối:</span>
                                                <p class="text-gray-600 mt-1">{{ $rejectedItem->rejected_reason }}</p>
                                            </div>
                                        @endif
                                        @if($rejectedItem->rejectedWarehouse)
                                            <div class="mt-2">
                                                <span class="font-medium text-gray-700">Kho lưu trữ:</span>
                                                <span class="ml-1">{{ $rejectedItem->rejectedWarehouse->name }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <!-- Timeline item: Vật tư hư hỏng -->
                            @if($repair->damagedMaterials->count() > 0)
                                <div class="flex items-start space-x-4 p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-purple-500 text-white rounded-full flex items-center justify-center">
                                            <i class="fas fa-exclamation-triangle text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900">Vật tư hư hỏng</h4>
                                            <span class="text-sm text-gray-500">{{ $repair->updated_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <div class="mt-2 space-y-2">
                                            @foreach($repair->damagedMaterials as $damaged)
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-700">{{ $damaged->device_code }}</span>
                                                    <span class="mx-1">-</span>
                                                    <span>{{ $damaged->material_code }} ({{ $damaged->material_name }})</span>
                                                    @if($damaged->serial)
                                                        <span class="ml-2 px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">{{ $damaged->serial }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($repair->materialReplacements->count() == 0 && $repair->repairItems->where('device_status', 'rejected')->count() == 0 && $repair->damagedMaterials->count() == 0)
                                <div class="text-center py-8">
                                    <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                                    <p class="text-gray-500">Chưa có hoạt động nào khác ngoài việc tạo phiếu sửa chữa</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Photos & Notes -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-paperclip text-blue-500 mr-2"></i>
                        Hình ảnh & Ghi chú
                    </h2>

                    <div class="mb-6">
                        <h3 class="text-md font-medium text-gray-700 mb-2">Hình ảnh:</h3>
                        @if ($repair->repair_photos && count($repair->repair_photos) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach ($repair->repair_photos as $index => $photo)
                                    <div class="border border-gray-200 rounded-lg overflow-hidden cursor-pointer"
                                        onclick="showImageModal('{{ asset('storage/' . $photo) }}')">
                                        <img src="{{ asset('storage/' . $photo) }}"
                                            alt="Repair Photo {{ $index + 1 }}" class="w-full h-40 object-cover">
                                        <div class="p-2 bg-gray-50">
                                            <p class="text-sm text-gray-600">Hình ảnh sửa chữa {{ $index + 1 }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Không có hình ảnh nào được đính kèm</p>
                        @endif
                    </div>
                    <div class="mb-6">
                        <h3 class="text-md font-medium text-gray-700 mb-2">Ghi chú:</h3>
                        <p class="text-gray-500 text-sm">{{ $repair->repair_notes ?: 'Không có ghi chú' }}</p>
                    </div>
                </div>
            </main>
        </div>

        <!-- Modal xem hình ảnh -->
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-white rounded-lg max-w-3xl max-h-[90%] overflow-auto">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold">Xem hình ảnh</h3>
                    <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-4">
                    <img id="modalImage" src="" alt="Device image" class="max-w-full h-auto">
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Xử lý sự kiện in phiếu
                const printBtn = document.getElementById('print-btn');
                printBtn.addEventListener('click', function() {
                    window.print();
                });
            });

            // Hàm hiển thị modal hình ảnh
            function showImageModal(imageSrc) {
                const modal = document.getElementById('imageModal');
                const modalImage = document.getElementById('modalImage');
                modalImage.src = imageSrc;
                modal.classList.remove('hidden');
            }

            // Hàm đóng modal hình ảnh
            function closeImageModal() {
                const modal = document.getElementById('imageModal');
                modal.classList.add('hidden');
            }

            // Đóng modal khi click vào backdrop
            document.getElementById('imageModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeImageModal();
                }
            });
        </script>
    </body>

    </html>
