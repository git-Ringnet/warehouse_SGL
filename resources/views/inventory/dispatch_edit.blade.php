<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu xuất kho - SGL</title>
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
                <a href="{{ asset('inventory/dispatch_detail') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu xuất kho</h1>
            </div>
        </header>

        <main class="p-6">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <strong>Có lỗi xảy ra:</strong>
                    <ul class="mt-2">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('inventory.dispatch.update', $dispatch->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Thông tin phiếu xuất -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-invoice text-blue-500 mr-2"></i>
                        Thông tin phiếu xuất
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="dispatch_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu
                                xuất</label>
                            <input type="text" id="dispatch_code" name="dispatch_code"
                                value="{{ $dispatch->dispatch_code }}" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="dispatch_date"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày xuất <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="dispatch_date" name="dispatch_date"
                                value="{{ $dispatch->dispatch_date->format('Y-m-d') }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="dispatch_type"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Loại hình <span
                                    class="text-red-500">*</span></label>
                            <select id="dispatch_type" name="dispatch_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn loại hình --</option>
                                <option value="project" {{ $dispatch->dispatch_type == 'project' ? 'selected' : '' }}>Dự
                                    án</option>
                                <option value="rental" {{ $dispatch->dispatch_type == 'rental' ? 'selected' : '' }}>Cho
                                    thuê</option>
                                <option value="warranty"
                                    {{ $dispatch->dispatch_type == 'warranty' ? 'selected' : '' }}>Bảo hành</option>
                            </select>
                        </div>
                        <div>
                            <label for="dispatch_detail" class="block text-sm font-medium text-gray-700 mb-1">Chi tiết
                                xuất kho <span class="text-red-500">*</span></label>
                            <select id="dispatch_detail" name="dispatch_detail"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="all" {{ $dispatch->dispatch_detail == 'all' ? 'selected' : '' }}>Tất
                                    cả</option>
                                <option value="contract"
                                    {{ $dispatch->dispatch_detail == 'contract' ? 'selected' : '' }}>Xuất theo hợp đồng
                                </option>
                                <option value="backup" {{ $dispatch->dispatch_detail == 'backup' ? 'selected' : '' }}>
                                    Xuất thiết bị dự phòng</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="project_receiver"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Dự án <span
                                    class="text-red-500">*</span></label>
                            <select id="project_receiver" name="project_receiver" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn dự án --</option>
                                @if (isset($projects))
                                    @foreach ($projects as $project)
                                        <option
                                            value="{{ $project->project_code }} - {{ $project->project_name }} ({{ $project->customer->name ?? 'N/A' }})"
                                            data-project-id="{{ $project->id }}"
                                            data-warranty-period="{{ $project->warranty_period }}"
                                            {{ $dispatch->project_receiver == $project->project_code . ' - ' . $project->project_name . ' (' . ($project->customer->name ?? 'N/A') . ')' ? 'selected' : '' }}>
                                            {{ $project->project_code }} - {{ $project->project_name }}
                                            ({{ $project->customer->name ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <input type="hidden" id="project_id" name="project_id"
                                value="{{ $dispatch->project_id }}">
                        </div>
                        <div>
                            <label for="warranty_period" class="block text-sm font-medium text-gray-700 mb-1">Thời gian
                                bảo hành</label>
                            <input type="text" id="warranty_period" name="warranty_period"
                                value="{{ $dispatch->warranty_period }}" readonly
                                class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label for="company_representative"
                                class="block text-sm font-medium text-gray-700 mb-1">Người đại diện công ty</label>
                            <select id="company_representative" name="company_representative_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người đại diện --</option>
                                @if (isset($employees))
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}"
                                            {{ $dispatch->company_representative_id == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="dispatch_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi
                                chú</label>
                            <textarea id="dispatch_note" name="dispatch_note" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nhập ghi chú cho phiếu xuất (nếu có)">{{ $dispatch->dispatch_note }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Danh sách sản phẩm hợp đồng hiện tại -->
                @php
                    $contractItems = $dispatch->items->where('category', 'contract');
                    $backupItems = $dispatch->items->where('category', 'backup');
                    $generalItems = $dispatch->items->whereNotIn('category', ['contract', 'backup']);
                @endphp

                @if ($contractItems->count() > 0)
                    <div class="bg-white rounded-xl shadow-md p-6 border border-blue-200 mb-6">
                        <h2 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                            <i class="fas fa-file-contract text-blue-500 mr-2"></i>
                            Danh sách sản phẩm hợp đồng hiện tại
                        </h2>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-blue-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            Mã SP</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            Tên sản phẩm</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            Loại</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            Đơn vị</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            Kho xuất</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            Số lượng</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            Serial</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($contractItems as $index => $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-900">
                                                {{ $item->item_code }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->item_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $item->item_type == 'product'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : ($item->item_type == 'material'
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $item->item_type_label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->item_unit }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->warehouse->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number"
                                                    name="contract_items[{{ $item->id }}][quantity]"
                                                    value="{{ $item->quantity }}" min="1"
                                                    class="w-20 border border-blue-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <input type="hidden"
                                                    name="contract_items[{{ $item->id }}][item_type]"
                                                    value="{{ $item->item_type }}">
                                                <input type="hidden"
                                                    name="contract_items[{{ $item->id }}][item_id]"
                                                    value="{{ $item->item_id }}">
                                                <input type="hidden"
                                                    name="contract_items[{{ $item->id }}][warehouse_id]"
                                                    value="{{ $item->warehouse_id }}">
                                                <input type="hidden"
                                                    name="contract_items[{{ $item->id }}][category]"
                                                    value="{{ $item->category }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col space-y-1">
                                                    @php
                                                        $serialNumbers = is_array($item->serial_numbers)
                                                            ? $item->serial_numbers
                                                            : [];
                                                        $quantity = $item->quantity;
                                                    @endphp
                                                    @for ($i = 0; $i < $quantity; $i++)
                                                        <input type="text"
                                                            name="contract_items[{{ $item->id }}][serial_numbers][{{ $i }}]"
                                                            placeholder="Serial {{ $i + 1 }}"
                                                            value="{{ $serialNumbers[$i] ?? '' }}"
                                                            class="w-32 border border-blue-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                    @endfor
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button type="button"
                                                    class="text-red-600 hover:text-red-900 remove-contract-item-btn"
                                                    data-item-id="{{ $item->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Danh sách sản phẩm dự phòng hiện tại -->
                @if ($backupItems->count() > 0)
                    <div class="bg-white rounded-xl shadow-md p-6 border border-orange-200 mb-6">
                        <h2 class="text-lg font-semibold text-orange-800 mb-4 flex items-center">
                            <i class="fas fa-shield-alt text-orange-500 mr-2"></i>
                            Danh sách sản phẩm dự phòng hiện tại
                        </h2>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-orange-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            Mã SP</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            Tên sản phẩm</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            Loại</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            Đơn vị</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            Kho xuất</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            Số lượng</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            Serial</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($backupItems as $index => $item)
                                        <tr>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-orange-900">
                                                {{ $item->item_code }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->item_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $item->item_type == 'product'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : ($item->item_type == 'material'
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $item->item_type_label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->item_unit }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->warehouse->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number"
                                                    name="backup_items[{{ $item->id }}][quantity]"
                                                    value="{{ $item->quantity }}" min="1"
                                                    class="w-20 border border-orange-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                                                <input type="hidden"
                                                    name="backup_items[{{ $item->id }}][item_type]"
                                                    value="{{ $item->item_type }}">
                                                <input type="hidden"
                                                    name="backup_items[{{ $item->id }}][item_id]"
                                                    value="{{ $item->item_id }}">
                                                <input type="hidden"
                                                    name="backup_items[{{ $item->id }}][warehouse_id]"
                                                    value="{{ $item->warehouse_id }}">
                                                <input type="hidden"
                                                    name="backup_items[{{ $item->id }}][category]"
                                                    value="{{ $item->category }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col space-y-1">
                                                    @php
                                                        $serialNumbers = is_array($item->serial_numbers)
                                                            ? $item->serial_numbers
                                                            : [];
                                                        $quantity = $item->quantity;
                                                    @endphp
                                                    @for ($i = 0; $i < $quantity; $i++)
                                                        <input type="text"
                                                            name="backup_items[{{ $item->id }}][serial_numbers][{{ $i }}]"
                                                            placeholder="Serial {{ $i + 1 }}"
                                                            value="{{ $serialNumbers[$i] ?? '' }}"
                                                            class="w-32 border border-orange-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                                                    @endfor
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button type="button"
                                                    class="text-red-600 hover:text-red-900 remove-backup-item-btn"
                                                    data-item-id="{{ $item->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Danh sách sản phẩm chung (nếu có) -->
                @if ($generalItems->count() > 0)
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-200 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-boxes text-gray-500 mr-2"></i>
                            Danh sách sản phẩm chung
                        </h2>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã SP</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên sản phẩm</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Loại</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Đơn vị</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Kho xuất</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Số lượng</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Serial</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($generalItems as $index => $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item->item_code }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->item_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $item->item_type == 'product'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : ($item->item_type == 'material'
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $item->item_type_label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->item_unit }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->warehouse->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number"
                                                    name="general_items[{{ $item->id }}][quantity]"
                                                    value="{{ $item->quantity }}" min="1"
                                                    class="w-20 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <input type="hidden"
                                                    name="general_items[{{ $item->id }}][item_type]"
                                                    value="{{ $item->item_type }}">
                                                <input type="hidden"
                                                    name="general_items[{{ $item->id }}][item_id]"
                                                    value="{{ $item->item_id }}">
                                                <input type="hidden"
                                                    name="general_items[{{ $item->id }}][warehouse_id]"
                                                    value="{{ $item->warehouse_id }}">
                                                <input type="hidden"
                                                    name="general_items[{{ $item->id }}][category]"
                                                    value="{{ $item->category }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col space-y-1">
                                                    @php
                                                        $serialNumbers = is_array($item->serial_numbers)
                                                            ? $item->serial_numbers
                                                            : [];
                                                        $quantity = $item->quantity;
                                                    @endphp
                                                    @for ($i = 0; $i < $quantity; $i++)
                                                        <input type="text"
                                                            name="general_items[{{ $item->id }}][serial_numbers][{{ $i }}]"
                                                            placeholder="Serial {{ $i + 1 }}"
                                                            value="{{ $serialNumbers[$i] ?? '' }}"
                                                            class="w-32 border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                    @endfor
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button type="button"
                                                    class="text-red-600 hover:text-red-900 remove-general-item-btn"
                                                    data-item-id="{{ $item->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($dispatch->items->count() == 0)
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                        <div class="text-center py-8">
                            <div class="text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-4"></i>
                                <p>Chưa có sản phẩm nào trong phiếu xuất này.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('inventory.dispatch.show', $dispatch->id) }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i> Cập nhật phiếu xuất
                    </button>
                </div>
            </form>
        </main>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý thay đổi dự án - cập nhật thời gian bảo hành và project_id
            const projectReceiverSelect = document.getElementById('project_receiver');
            if (projectReceiverSelect) {
                projectReceiverSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const warrantyPeriodInput = document.getElementById('warranty_period');
                    const projectIdInput = document.getElementById('project_id');

                    if (selectedOption && selectedOption.dataset.warrantyPeriod) {
                        warrantyPeriodInput.value = selectedOption.dataset.warrantyPeriod + ' tháng';
                        projectIdInput.value = selectedOption.dataset.projectId || '';
                    } else {
                        warrantyPeriodInput.value = '';
                        projectIdInput.value = '';
                    }
                });
            }

            // Thêm event listeners cho quantity inputs hiện tại theo category
            const contractQuantityInputs = document.querySelectorAll(
                'input[name*="contract_items"][name*="[quantity]"]');
            contractQuantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    updateSerialInputs(this, 'contract');
                    // Chỉ validate cho dispatch pending
                    @if ($dispatch->status === 'pending')
                        showEditStockWarnings();
                    @endif
                });
            });

            const backupQuantityInputs = document.querySelectorAll(
                'input[name*="backup_items"][name*="[quantity]"]');
            backupQuantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    updateSerialInputs(this, 'backup');
                    // Chỉ validate cho dispatch pending
                    @if ($dispatch->status === 'pending')
                        showEditStockWarnings();
                    @endif
                });
            });

            const generalQuantityInputs = document.querySelectorAll(
                'input[name*="general_items"][name*="[quantity]"]');
            generalQuantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    updateSerialInputs(this, 'general');
                    // Chỉ validate cho dispatch pending
                    @if ($dispatch->status === 'pending')
                        showEditStockWarnings();
                    @endif
                });
            });

            // Xử lý xóa item theo category
            const removeContractItemBtns = document.querySelectorAll('.remove-contract-item-btn');
            removeContractItemBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm(
                            'Bạn có chắc chắn muốn xóa sản phẩm hợp đồng này khỏi phiếu xuất?')) {
                        const row = this.closest('tr');
                        row.style.display = 'none';

                        // Disable tất cả input trong row này để không submit
                        const inputs = row.querySelectorAll('input, textarea');
                        inputs.forEach(input => {
                            input.disabled = true;
                        });

                        // Kiểm tra lại tồn kho sau khi xóa
                        @if ($dispatch->status === 'pending')
                            showEditStockWarnings();
                        @endif
                    }
                });
            });

            const removeBackupItemBtns = document.querySelectorAll('.remove-backup-item-btn');
            removeBackupItemBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm(
                            'Bạn có chắc chắn muốn xóa sản phẩm dự phòng này khỏi phiếu xuất?')) {
                        const row = this.closest('tr');
                        row.style.display = 'none';

                        // Disable tất cả input trong row này để không submit
                        const inputs = row.querySelectorAll('input, textarea');
                        inputs.forEach(input => {
                            input.disabled = true;
                        });

                        // Kiểm tra lại tồn kho sau khi xóa
                        @if ($dispatch->status === 'pending')
                            showEditStockWarnings();
                        @endif
                    }
                });
            });

            const removeGeneralItemBtns = document.querySelectorAll('.remove-general-item-btn');
            removeGeneralItemBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi phiếu xuất?')) {
                        const row = this.closest('tr');
                        row.style.display = 'none';

                        // Disable tất cả input trong row này để không submit
                        const inputs = row.querySelectorAll('input, textarea');
                        inputs.forEach(input => {
                            input.disabled = true;
                        });

                        // Kiểm tra lại tồn kho sau khi xóa
                        @if ($dispatch->status === 'pending')
                            showEditStockWarnings();
                        @endif
                    }
                });
            });

            // Hàm cập nhật serial inputs khi quantity thay đổi
            function updateSerialInputs(quantityInput, category) {
                const newQuantity = parseInt(quantityInput.value);
                const row = quantityInput.closest('tr');
                const serialContainer = row.querySelector('.flex.flex-col');

                if (!serialContainer) return;

                const currentSerialInputs = serialContainer.querySelectorAll('input[type="text"]');
                const currentValues = Array.from(currentSerialInputs).map(input => input.value);

                // Xóa tất cả serial inputs hiện tại
                serialContainer.innerHTML = '';

                // Tạo serial inputs mới theo quantity
                for (let i = 0; i < newQuantity; i++) {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.placeholder = `Serial ${i + 1}`;
                    input.value = currentValues[i] || '';
                    input.className = `w-32 border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1`;

                    // Xác định màu border theo category
                    if (category === 'contract') {
                        input.classList.add('border-blue-300', 'focus:ring-blue-500');
                        input.name = quantityInput.name.replace('[quantity]', `[serial_numbers][${i}]`);
                    } else if (category === 'backup') {
                        input.classList.add('border-orange-300', 'focus:ring-orange-500');
                        input.name = quantityInput.name.replace('[quantity]', `[serial_numbers][${i}]`);
                    } else {
                        input.classList.add('border-gray-300', 'focus:ring-blue-500');
                        input.name = quantityInput.name.replace('[quantity]', `[serial_numbers][${i}]`);
                    }

                    serialContainer.appendChild(input);

                    // Thêm margin bottom trừ item cuối
                    if (i < newQuantity - 1) {
                        input.classList.add('mb-1');
                    }
                }
            }

            // Hàm kiểm tra tồn kho tổng hợp cho trang edit
            function validateEditStock() {
                const stockErrors = [];
                const groupedItems = {};

                // Lấy tất cả items hiện tại (chỉ items cũ đã có trong database)
                const allItems = [];

                // Items hiện tại trong form theo category
                // Contract items
                const contractItems = form.querySelectorAll(
                    'input[name*="contract_items"][name*="[item_id]"]:not([disabled])');
                contractItems.forEach((input) => {
                    const row = input.closest('tr');
                    const itemType = row.querySelector('input[name*="[item_type]"]').value;
                    const itemId = parseInt(input.value);
                    const warehouseId = parseInt(row.querySelector('input[name*="[warehouse_id]"]').value);
                    const quantity = parseInt(row.querySelector('input[name*="[quantity]"]').value);
                    const category = row.querySelector('input[name*="[category]"]').value;

                    allItems.push({
                        item_type: itemType,
                        item_id: itemId,
                        warehouse_id: warehouseId,
                        quantity: quantity,
                        category: category
                    });
                });

                // Backup items
                const backupItems = form.querySelectorAll(
                    'input[name*="backup_items"][name*="[item_id]"]:not([disabled])');
                backupItems.forEach((input) => {
                    const row = input.closest('tr');
                    const itemType = row.querySelector('input[name*="[item_type]"]').value;
                    const itemId = parseInt(input.value);
                    const warehouseId = parseInt(row.querySelector('input[name*="[warehouse_id]"]').value);
                    const quantity = parseInt(row.querySelector('input[name*="[quantity]"]').value);
                    const category = row.querySelector('input[name*="[category]"]').value;

                    allItems.push({
                        item_type: itemType,
                        item_id: itemId,
                        warehouse_id: warehouseId,
                        quantity: quantity,
                        category: category
                    });
                });

                // General items
                const generalItems = form.querySelectorAll(
                    'input[name*="general_items"][name*="[item_id]"]:not([disabled])');
                generalItems.forEach((input) => {
                    const row = input.closest('tr');
                    const itemType = row.querySelector('input[name*="[item_type]"]').value;
                    const itemId = parseInt(input.value);
                    const warehouseId = parseInt(row.querySelector('input[name*="[warehouse_id]"]').value);
                    const quantity = parseInt(row.querySelector('input[name*="[quantity]"]').value);
                    const category = row.querySelector('input[name*="[category]"]').value;

                    allItems.push({
                        item_type: itemType,
                        item_id: itemId,
                        warehouse_id: warehouseId,
                        quantity: quantity,
                        category: category
                    });
                });

                // Nhóm items theo key
                allItems.forEach(item => {
                    const key = `${item.item_type}_${item.item_id}_${item.warehouse_id}`;
                    if (!groupedItems[key]) {
                        groupedItems[key] = {
                            item: item,
                            totalQuantity: 0,
                            categories: []
                        };
                    }
                    groupedItems[key].totalQuantity += item.quantity;
                    if (!groupedItems[key].categories.includes(item.category)) {
                        groupedItems[key].categories.push(item.category);
                    }
                });

                // Kiểm tra tồn kho cho từng nhóm
                Object.keys(groupedItems).forEach(key => {
                    const group = groupedItems[key];
                    // Tìm thông tin sản phẩm từ availableItems hoặc từ existing data
                    let currentStock = 0;
                    let productName = 'Không xác định';
                    let productCode = 'N/A';

                    // Tìm trong availableItems nếu có
                    if (typeof availableItems !== 'undefined') {
                        const foundItem = availableItems.find(item =>
                            item.id == group.item.item_id && item.type == group.item.item_type
                        );
                        if (foundItem) {
                            const warehouse = foundItem.warehouses.find(w => w.warehouse_id == group.item
                                .warehouse_id);
                            if (warehouse) {
                                currentStock = warehouse.quantity;
                                productName = foundItem.name;
                                productCode = foundItem.code;
                            }
                        }
                    }

                    if (group.totalQuantity > currentStock) {
                        const categoriesText = group.categories.map(cat => {
                            switch (cat) {
                                case 'contract':
                                    return 'hợp đồng';
                                case 'backup':
                                    return 'dự phòng';
                                default:
                                    return cat;
                            }
                        }).join(', ');

                        stockErrors.push(
                            `${productCode} - ${productName}: ` +
                            `Tồn kho ${currentStock}, yêu cầu ${group.totalQuantity} ` +
                            `(Tổng từ: ${categoriesText})`
                        );
                    }
                });

                return stockErrors;
            }

            // Hàm hiển thị cảnh báo tồn kho cho trang edit
            function showEditStockWarnings() {
                const stockErrors = validateEditStock();

                // Xóa cảnh báo cũ
                const oldWarnings = document.querySelectorAll('.stock-warning');
                oldWarnings.forEach(warning => warning.remove());

                if (stockErrors.length > 0) {
                    // Tạo div cảnh báo
                    const warningDiv = document.createElement('div');
                    warningDiv.className =
                        'stock-warning bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4';
                    warningDiv.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Cảnh báo tồn kho:</strong>
                        </div>
                        <ul class="mt-2 ml-6">
                            ${stockErrors.map(error => `<li>• ${error}</li>`).join('')}
                        </ul>
                    `;

                    // Thêm vào đầu form
                    form.insertBefore(warningDiv, form.firstChild);
                }
            }

            // Xử lý form submit
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Kiểm tra xem còn item nào không bị disable (tất cả categories)
                    const activeContractItems = form.querySelectorAll(
                        'input[name*="contract_items"][name*="[item_id]"]:not([disabled])');
                    const activeBackupItems = form.querySelectorAll(
                        'input[name*="backup_items"][name*="[item_id]"]:not([disabled])');
                    const activeGeneralItems = form.querySelectorAll(
                        'input[name*="general_items"][name*="[item_id]"]:not([disabled])');

                    const totalActiveItems = activeContractItems.length + activeBackupItems.length +
                        activeGeneralItems.length;

                    if (totalActiveItems === 0) {
                        e.preventDefault();
                        alert('Phiếu xuất phải có ít nhất một sản phẩm!');
                        return;
                    }

                    // Kiểm tra tồn kho trước khi submit (chỉ cho dispatch pending)
                    @if ($dispatch->status === 'pending')
                        const stockErrors = validateEditStock();
                        if (stockErrors.length > 0) {
                            e.preventDefault();
                            alert('Không đủ tồn kho:\n\n' + stockErrors.join('\n'));
                            return;
                        }
                    @endif

                    // Serial numbers đã được xử lý trực tiếp qua các input riêng biệt
                    // Không cần xử lý thêm gì
                });
            }
        });
    </script>
</body>

</html>
