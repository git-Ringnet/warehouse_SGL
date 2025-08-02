<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chi tiết phiếu xuất kho - SGL</title>
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
                <a href="{{ route('inventory.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu xuất kho</h1>
            </div>
            <div class="flex items-center gap-2">
                @if (!in_array($dispatch->status, ['completed', 'cancelled']))
                    <a href="{{ route('inventory.dispatch.edit', $dispatch) }}">
                        <button
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                        </button>
                    </a>
                @endif
                <div class="flex flex-wrap gap-3 justify-end">
                    @if ($dispatch->status === 'pending')
                        <button onclick="approveDispatch({{ $dispatch->id }})"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-check mr-2"></i> Duyệt phiếu
                        </button>
                    @endif
                    @if ($dispatch->status === 'pending')
                        <button onclick="cancelDispatch({{ $dispatch->id }})"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-times mr-2"></i> Hủy phiếu
                        </button>
                    @elseif($dispatch->status === 'cancelled')
                        <button onclick="deleteDispatch({{ $dispatch->id }})"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-trash mr-2"></i> Xóa phiếu
                        </button>
                    @endif
                </div>
                <button id="print-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-print mr-2"></i> In phiếu
                </button>
            </div>
        </header>

        <!-- Thông báo -->
        @if (session()->has('success'))
            <div class="mx-6 mt-6">
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mx-6 mt-6">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <main class="p-6">
            <!-- Header Info -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <div class="flex flex-col lg:flex-row justify-between gap-4">
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-lg font-semibold text-gray-800 mr-2">Mã phiếu xuất:</span>
                            <span class="text-lg text-blue-600 font-bold">{{ $dispatch->dispatch_code }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Ngày xuất:</span>
                            <span
                                class="text-sm text-gray-700">{{ $dispatch->dispatch_date->format('H:i:s d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Người nhận:</span>
                            @if($dispatch->project_id)
                                <a href="{{ route('projects.show', $dispatch->project_id) }}">
                                    <span class="text-sm text-blue-700">{{ $dispatch->project_receiver }}</span>
                                </a>
                            @else
                                <span class="text-sm text-gray-700">{{ $dispatch->project_receiver }}</span>
                            @endif
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Loại hình:</span>
                            <span class="text-sm text-gray-700">
                                @switch($dispatch->dispatch_type)
                                    @case('project')
                                        Dự án
                                    @break

                                    @case('rental')
                                        Cho thuê
                                    @break

                                    @case('warranty')
                                        Bảo hành
                                    @break

                                    @case('other')
                                        Khác
                                    @break

                                    @default
                                        {{ ucfirst($dispatch->dispatch_type) }}
                                @endswitch
                            </span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Chi tiết xuất:</span>
                            <span class="text-sm text-gray-700">
                                @switch($dispatch->dispatch_detail)
                                    @case('all')
                                        Tất cả
                                    @break

                                    @case('contract')
                                        Theo hợp đồng
                                    @break

                                    @case('backup')
                                        Dự phòng
                                    @break

                                    @default
                                        {{ ucfirst($dispatch->dispatch_detail) }}
                                @endswitch
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Người tạo phiếu:</span>
                            <span
                                class="text-sm text-gray-700">{{ $dispatch->creator->name ?? 'Không xác định' }}</span>
                        </div>
                        @if ($dispatch->companyRepresentative)
                            <div class="flex items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 mr-2">Người đại diện công ty:</span>
                                <span class="text-sm text-gray-700">{{ $dispatch->companyRepresentative->name }}</span>
                            </div>
                        @endif
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Cập nhật lần cuối:</span>
                            <span
                                class="text-sm text-gray-700">{{ $dispatch->updated_at->format('H:i:s d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Trạng thái:</span>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                                $statusLabels = [
                                    'pending' => 'Chờ duyệt',
                                    'approved' => 'Đã duyệt',
                                    'completed' => 'Đã hoàn thành',
                                    'cancelled' => 'Đã hủy',
                                ];
                            @endphp
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$dispatch->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statusLabels[$dispatch->status] ?? ucfirst($dispatch->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if ($dispatch->dispatch_note)
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Ghi chú:</span>
                            <span class="text-sm text-gray-700">{{ $dispatch->dispatch_note }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Product List -->
            @if ($dispatch->dispatch_detail === 'all')
                <!-- Khi xuất tất cả, hiển thị 2 bảng riêng biệt -->
                <!-- Danh sách thành phẩm theo hợp đồng -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                        <i class="fas fa-file-contract text-blue-500 mr-2"></i>
                        Danh sách thiết bị theo hợp đồng
                    </h2>

                    @php
                        $contractItems = $dispatch->items->filter(function ($item) {
                            return $item->category === 'contract';
                        });
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider">
                                        STT</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider">
                                        Mã</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider">
                                        Tên thiết bị</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider">
                                        Đơn vị</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider">
                                        Số lượng xuất</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider">
                                        Serial</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider">
                                        Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($contractItems as $index => $item)
                                    <tr class="hover:bg-blue-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-900 font-medium">
                                            @if ($item->item)
                                                {{ $item->item->code ?? $item->item->id }}
                                            @else
                                                {{ ucfirst($item->item_type) }}-{{ $item->item_id }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium">
                                                    @if ($item->item)
                                                        {{ $item->item->name ?? 'Không xác định' }}
                                                    @else
                                                        {{ ucfirst($item->item_type) }} ID: {{ $item->item_id }}
                                                    @endif
                                                </div>
                                                @if ($item->notes)
                                                    <div class="text-xs text-blue-600">{{ $item->notes }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            @if ($item->item && isset($item->item->unit))
                                                {{ $item->item->unit }}
                                            @else
                                                Cái
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-700">
                                            {{ $item->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $serialCount = 0;
                                                if ($item->serial_numbers) {
                                                    if (is_array($item->serial_numbers)) {
                                                        $serialCount = count($item->serial_numbers);
                                                    } elseif (is_string($item->serial_numbers)) {
                                                        $decoded = json_decode($item->serial_numbers, true);
                                                        $serialCount = is_array($decoded) ? count($decoded) : 0;
                                                    }
                                                }
                                            @endphp
                                            @if ($serialCount > 0)
                                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                                    {{ $serialCount }} serial
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-500">Chưa có</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                            <a href="{{ route('products.show', $item->item_id) }}">
                                                <button class="hover:text-blue-800">
                                                    Xem chi tiết
                                                </button>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                            <i class="fas fa-file-contract text-4xl mb-4 text-gray-300"></i>
                                            <p class="text-lg">Chưa có thành phẩm hợp đồng nào được thêm</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Danh sách thiết bị dự phòng -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-orange-800 mb-4 flex items-center">
                        <i class="fas fa-tools text-orange-500 mr-2"></i>
                        Danh sách thiết bị dự phòng
                    </h2>

                    @php
                        $backupItems = $dispatch->items->filter(function ($item) {
                            return $item->category === 'backup';
                        });
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-600 uppercase tracking-wider">
                                        STT</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-600 uppercase tracking-wider">
                                        Mã</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-600 uppercase tracking-wider">
                                        Tên thiết bị</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-600 uppercase tracking-wider">
                                        Đơn vị</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-600 uppercase tracking-wider">
                                        Số lượng xuất</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-600 uppercase tracking-wider">
                                        Serial</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-orange-600 uppercase tracking-wider">
                                        Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($backupItems as $index => $item)
                                    <tr class="hover:bg-orange-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-900 font-medium">
                                            @if ($item->item)
                                                {{ $item->item->code ?? $item->item->id }}
                                            @else
                                                {{ ucfirst($item->item_type) }}-{{ $item->item_id }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium">
                                                    @if ($item->item)
                                                        {{ $item->item->name ?? 'Không xác định' }}
                                                    @else
                                                        {{ ucfirst($item->item_type) }} ID: {{ $item->item_id }}
                                                    @endif
                                                </div>
                                                @if ($item->notes)
                                                    <div class="text-xs text-orange-600">{{ $item->notes }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            @if ($item->item && isset($item->item->unit))
                                                {{ $item->item->unit }}
                                            @else
                                                Cái
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-orange-700">
                                            {{ $item->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $serialCount = 0;
                                                if ($item->serial_numbers) {
                                                    if (is_array($item->serial_numbers)) {
                                                        $serialCount = count($item->serial_numbers);
                                                    } elseif (is_string($item->serial_numbers)) {
                                                        $decoded = json_decode($item->serial_numbers, true);
                                                        $serialCount = is_array($decoded) ? count($decoded) : 0;
                                                    }
                                                }
                                            @endphp
                                            @if ($serialCount > 0)
                                                <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">
                                                    {{ $serialCount }} serial
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-500">Chưa có</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600">
                                            <a href="{{ route('products.show', $item->item_id) }}">
                                                <button class="hover:text-orange-800">
                                                    Xem chi tiết
                                                </button>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                            <i class="fas fa-tools text-4xl mb-4 text-gray-300"></i>
                                            <p class="text-lg">Chưa có thiết bị dự phòng nào được thêm</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- Khi xuất riêng lẻ (contract hoặc backup), hiển thị 1 bảng với title tương ứng -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    @if ($dispatch->dispatch_detail === 'contract')
                        <h2 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                            <i class="fas fa-file-contract text-blue-500 mr-2"></i>
                            Danh sách thiết bị theo hợp đồng
                        </h2>
                    @elseif($dispatch->dispatch_detail === 'backup')
                        <h2 class="text-lg font-semibold text-orange-800 mb-4 flex items-center">
                            <i class="fas fa-tools text-orange-500 mr-2"></i>
                            Danh sách thiết bị dự phòng
                        </h2>
                    @else
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-boxes text-blue-500 mr-2"></i>
                            Danh sách xuất
                        </h2>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead
                                class="bg-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-600 uppercase tracking-wider">
                                        STT</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-600 uppercase tracking-wider">
                                        Mã</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-600 uppercase tracking-wider">
                                        {{ $dispatch->dispatch_detail === 'contract' ? 'Tên thiết bị' : ($dispatch->dispatch_detail === 'backup' ? 'Tên thiết bị' : 'Tên thiết bị') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-600 uppercase tracking-wider">
                                        Đơn vị</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-600 uppercase tracking-wider">
                                        Số lượng</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-600 uppercase tracking-wider">
                                        Kho xuất</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-600 uppercase tracking-wider">
                                        Serial</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-600 uppercase tracking-wider">
                                        Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($dispatch->items as $index => $item)
                                    <tr
                                        class="hover:bg-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $index + 1 }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-900 font-medium">
                                            @if ($item->item)
                                                {{ $item->item->code ?? $item->item->id }}
                                            @else
                                                {{ ucfirst($item->item_type) }}-{{ $item->item_id }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <div>
                                                <div class="font-medium">
                                                    @if ($item->item)
                                                        {{ $item->item->name ?? 'Không xác định' }}
                                                    @else
                                                        {{ ucfirst($item->item_type) }} ID: {{ $item->item_id }}
                                                    @endif
                                                </div>
                                                @if ($item->notes)
                                                    <div
                                                        class="text-xs text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-600">
                                                        {{ $item->notes }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            @if ($item->item && isset($item->item->unit))
                                                {{ $item->item->unit }}
                                            @else
                                                Cái
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $item->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $item->warehouse->name ?? 'Không xác định' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $serialCount = 0;
                                                if ($item->serial_numbers) {
                                                    if (is_array($item->serial_numbers)) {
                                                        $serialCount = count($item->serial_numbers);
                                                    } elseif (is_string($item->serial_numbers)) {
                                                        $decoded = json_decode($item->serial_numbers, true);
                                                        $serialCount = is_array($decoded) ? count($decoded) : 0;
                                                    }
                                                }
                                            @endphp
                                            @if ($serialCount > 0)
                                                <span
                                                    class="text-xs bg-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-100 text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-800 px-2 py-1 rounded">
                                                    {{ $serialCount }} serial
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-500">Chưa có</span>
                                            @endif
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-600">
                                            <a href="{{ route('products.show', $item->item_id) }}">
                                                <button
                                                    class="hover:text-{{ $dispatch->dispatch_detail === 'contract' ? 'blue' : ($dispatch->dispatch_detail === 'backup' ? 'orange' : 'gray') }}-800">
                                                    Xem chi tiết
                                                </button>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                            <i class="fas fa-box-open text-4xl mb-4 text-gray-300"></i>
                                            <p class="text-lg">Không có sản phẩm nào</p>
                                            <p class="text-sm">Phiếu xuất này chưa có sản phẩm nào</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Buttons -->
            <div class="flex flex-wrap gap-3 justify-end">
                <a href="{{ route('inventory.dispatch.export.excel', $dispatch->id) }}"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-file-excel mr-2"></i> Xuất Excel
                </a>

                <a href="{{ route('inventory.dispatch.export.pdf', $dispatch->id) }}"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i> Xuất PDF
                </a>
            </div>
        </main>
    </div>

    <!-- Modal chi tiết sản phẩm -->
    <div id="product-detail-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Chi tiết sản phẩm: <span id="product-detail-name">Bộ
                        điều khiển chính</span></h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" id="close-product-detail-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Mã sản phẩm: <span id="product-detail-code"
                                class="font-medium">-</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Đơn vị: <span id="product-detail-unit"
                                class="font-medium">-</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Số lượng: <span id="product-detail-quantity"
                                class="font-medium">-</span></p>
                    </div>
                </div>

                <h4 class="text-md font-semibold text-gray-800 mb-3">Danh sách Serial Numbers</h4>

                <div class="overflow-x-auto">
                    <div id="serial-numbers-container" class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-500">Đang tải dữ liệu...</p>
                    </div>
                </div>

                <div id="notes-container" class="mt-4" style="display: none;">
                    <h4 class="text-md font-semibold text-gray-800 mb-2">Ghi chú</h4>
                    <div id="item-notes" class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700">
                        <!-- Notes will be populated here -->
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end space-x-3">
                <button type="button" id="close-detail-btn"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Đóng
                </button>
                <button id="export-product-excel-btn"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Xuất Excel
                </button>
                <button id="export-product-pdf-btn"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Xuất PDF
                </button>
            </div>
        </div>
    </div>

    <script>
        // Dữ liệu dispatch items từ server
        const dispatchItems = @json($dispatch->items);

        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý sự kiện in phiếu
            const printBtn = document.getElementById('print-btn');
            printBtn.addEventListener('click', function() {
                window.print();
            });

            // Đóng modal chi tiết sản phẩm
            const productDetailModal = document.getElementById('product-detail-modal');
            const closeProductDetailModalBtn = document.getElementById('close-product-detail-modal');
            const closeDetailBtn = document.getElementById('close-detail-btn');

            closeProductDetailModalBtn.addEventListener('click', function() {
                productDetailModal.classList.add('hidden');
            });

            closeDetailBtn.addEventListener('click', function() {
                productDetailModal.classList.add('hidden');
            });

            // Xử lý sự kiện xuất Excel chi tiết sản phẩm
            const exportProductExcelBtn = document.getElementById('export-product-excel-btn');
            exportProductExcelBtn.addEventListener('click', function() {
                alert('Tính năng xuất Excel chi tiết sản phẩm đang được phát triển!');
            });

            // Xử lý sự kiện xuất PDF chi tiết sản phẩm
            const exportProductPdfBtn = document.getElementById('export-product-pdf-btn');
            exportProductPdfBtn.addEventListener('click', function() {
                alert('Tính năng xuất PDF chi tiết sản phẩm đang được phát triển!');
            });
        });

        // Function to approve dispatch
        function approveDispatch(dispatchId) {
            if (!confirm('Bạn có chắc chắn muốn duyệt phiếu xuất này?')) {
                return;
            }

            fetch(`/inventory/dispatch/${dispatchId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        notes: 'Duyệt từ giao diện web'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        let errorMessage = data.message || 'Không thể duyệt phiếu';

                        // Handle duplicate serials error
                        if (data.duplicate_serials && data.duplicate_serials.length > 0) {
                            errorMessage += '\n\nSerial numbers trùng lặp:';
                            data.duplicate_serials.forEach(duplicate => {
                                errorMessage +=
                                    `\n• ${duplicate.serial} (${duplicate.item_code} - ${duplicate.item_name}) - Đã có trong phiếu ${duplicate.existing_dispatch_code}`;
                            });
                            errorMessage += '\n\nVui lòng kiểm tra và chọn serial numbers khác.';
                        }

                        alert('Có lỗi xảy ra: ' + errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi duyệt phiếu');
                });
        }

        // Function to cancel dispatch
        function cancelDispatch(dispatchId) {
            const reason = prompt('Vui lòng nhập lý do hủy phiếu:');
            if (!reason) {
                return;
            }

            fetch(`/inventory/dispatch/${dispatchId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + (data.message || 'Không thể hủy phiếu'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi hủy phiếu');
                });
        }

        // Function to delete dispatch
        function deleteDispatch(dispatchId) {
            if (!confirm('Bạn có chắc chắn muốn xóa phiếu xuất này? Thao tác này không thể hoàn tác.')) {
                return;
            }

            fetch(`/inventory/dispatch/${dispatchId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.href = '/inventory'; // Redirect to list page
                    } else {
                        alert('Có lỗi xảy ra: ' + (data.message || 'Không thể xóa phiếu'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa phiếu');
                });
        }
    </script>
</body>

</html>
