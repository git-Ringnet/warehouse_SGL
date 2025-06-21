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

                <!-- Thông báo trạng thái phiếu -->
                <div
                    class="bg-{{ $dispatch->status === 'pending' ? 'yellow' : ($dispatch->status === 'approved' ? 'green' : 'gray') }}-100 border border-{{ $dispatch->status === 'pending' ? 'yellow' : ($dispatch->status === 'approved' ? 'green' : 'gray') }}-400 text-{{ $dispatch->status === 'pending' ? 'yellow' : ($dispatch->status === 'approved' ? 'green' : 'gray') }}-700 px-4 py-3 rounded mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <div>
                            <strong>Trạng thái phiếu:</strong> 
                            <span class="font-semibold">
                                @if ($dispatch->status === 'pending')
                                    Chờ xử lý
                                @elseif($dispatch->status === 'approved')
                                    Đã duyệt
                                @elseif($dispatch->status === 'cancelled')
                                    Đã hủy
                                @elseif($dispatch->status === 'completed')
                                    Hoàn thành
                                @endif
                            </span>
                            @if ($dispatch->status === 'pending')
                                <br><span class="text-sm">Có thể chỉnh sửa đầy đủ thông tin phiếu xuất</span>
                            @elseif($dispatch->status === 'approved')
                                <br><span class="text-sm">Chỉ có thể cập nhật: Serial numbers (chưa xuất), người đại
                                    diện, ngày xuất, ghi chú</span>
                            @else
                                <br><span class="text-sm">Không thể chỉnh sửa phiếu ở trạng thái này</span>
                            @endif
                        </div>
                    </div>
                </div>

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
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $dispatch->status !== 'pending' && $dispatch->status !== 'approved' ? 'bg-gray-100' : '' }}"
                                {{ $dispatch->status !== 'pending' && $dispatch->status !== 'approved' ? 'readonly' : '' }}>
                        </div>
                        <div>
                            <label for="dispatch_type"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Loại hình <span
                                    class="text-red-500">*</span></label>
                            <select id="dispatch_type" name="dispatch_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $dispatch->status !== 'pending' ? 'bg-gray-100' : '' }}"
                                {{ $dispatch->status !== 'pending' ? 'disabled' : '' }}>
                                <option value="">-- Chọn loại hình --</option>
                                <option value="project" {{ $dispatch->dispatch_type == 'project' ? 'selected' : '' }}>
                                    Dự
                                    án</option>
                                <option value="rental" {{ $dispatch->dispatch_type == 'rental' ? 'selected' : '' }}>Cho
                                    thuê</option>
                                <option value="warranty"
                                    {{ $dispatch->dispatch_type == 'warranty' ? 'selected' : '' }}>Bảo hành</option>
                            </select>
                            @if ($dispatch->status !== 'pending')
                                <input type="hidden" name="dispatch_type" value="{{ $dispatch->dispatch_type }}">
                            @endif
                        </div>
                        <div>
                            <label for="dispatch_detail" class="block text-sm font-medium text-gray-700 mb-1">Chi tiết
                                xuất kho <span class="text-red-500">*</span></label>
                            <select id="dispatch_detail" name="dispatch_detail"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $dispatch->status !== 'pending' ? 'bg-gray-100' : '' }}"
                                {{ $dispatch->status !== 'pending' ? 'disabled' : '' }}>
                                <option value="all" {{ $dispatch->dispatch_detail == 'all' ? 'selected' : '' }}>Tất
                                    cả</option>
                                <option value="contract"
                                    {{ $dispatch->dispatch_detail == 'contract' ? 'selected' : '' }}>Xuất theo hợp đồng
                                </option>
                                <option value="backup" {{ $dispatch->dispatch_detail == 'backup' ? 'selected' : '' }}>
                                    Xuất thiết bị dự phòng</option>
                            </select>
                            @if ($dispatch->status !== 'pending')
                                <input type="hidden" name="dispatch_detail" value="{{ $dispatch->dispatch_detail }}">
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <!-- Phần chọn dự án (hiển thị khi loại hình = project/warranty) -->
                        <div id="project_section">
                            <label for="project_receiver"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Dự án <span
                                    class="text-red-500">*</span></label>
                            <select id="project_receiver" name="project_receiver" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $dispatch->status !== 'pending' ? 'bg-gray-100' : '' }}"
                                {{ $dispatch->status !== 'pending' ? 'disabled' : '' }}>
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
                            @if ($dispatch->status !== 'pending')
                                <input type="hidden" name="project_receiver"
                                    value="{{ $dispatch->project_receiver }}">
                            @endif
                            <input type="hidden" id="project_id" name="project_id"
                                value="{{ $dispatch->project_id }}">
                        </div>
                        
                        <!-- Phần cho thuê (hiển thị khi loại hình = rental) -->
                        <div id="rental_section" class="hidden">
                            <label for="rental_receiver"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Hợp đồng cho thuê<span
                                    class="text-red-500">*</span></label>
                            <select id="rental_receiver" name="rental_receiver"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn hợp đồng cho thuê --</option>
                                <!-- Động load từ API -->
                            </select>
                        </div>
                        <div>
                            <label for="warranty_period" class="block text-sm font-medium text-gray-700 mb-1">Thời
                                gian
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

                <!-- PHP variables for dispatch items (hidden sections) -->
                @php
                    $contractItems = $dispatch->items->where('category', 'contract');
                    $backupItems = $dispatch->items->where('category', 'backup');
                    $generalItems = $dispatch->items->whereNotIn('category', ['contract', 'backup']);
                @endphp

                <!-- Hidden existing items sections - moved to "selected" tables -->
                @if (false && $contractItems->count() > 0)
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
                                                @if ($dispatch->status === 'pending')
                                                    <input type="number"
                                                        name="contract_items[{{ $item->id }}][quantity]"
                                                        value="{{ $item->quantity }}" min="1"
                                                        class="w-20 border border-blue-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                @else
                                                    <span class="text-sm text-gray-600">{{ $item->quantity }}</span>
                                                    <input type="hidden"
                                                        name="contract_items[{{ $item->id }}][quantity]"
                                                        value="{{ $item->quantity }}">
                                                @endif
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
                                                            class="w-32 border border-blue-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 {{ $dispatch->status !== 'pending' && $dispatch->status !== 'approved' ? 'bg-gray-100' : '' }}"
                                                            {{ $dispatch->status !== 'pending' && $dispatch->status !== 'approved' ? 'readonly' : '' }}
                                                            data-item-id="{{ $item->id }}"
                                                            data-serial-index="{{ $i }}">
                                                    @endfor
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if ($dispatch->status === 'pending')
                                                    <button type="button"
                                                        class="text-red-600 hover:text-red-900 remove-contract-item-btn"
                                                        data-item-id="{{ $item->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="text-gray-400">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Danh sách sản phẩm dự phòng hiện tại -->
                @if (false && $backupItems->count() > 0)
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
                                                @if ($dispatch->status === 'pending')
                                                    <input type="number"
                                                        name="backup_items[{{ $item->id }}][quantity]"
                                                        value="{{ $item->quantity }}" min="1"
                                                        class="w-20 border border-orange-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                                                @else
                                                    <span class="text-sm text-gray-600">{{ $item->quantity }}</span>
                                                    <input type="hidden"
                                                        name="backup_items[{{ $item->id }}][quantity]"
                                                        value="{{ $item->quantity }}">
                                                @endif
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
                                                            class="w-32 border border-orange-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 {{ $dispatch->status !== 'pending' && $dispatch->status !== 'approved' ? 'bg-gray-100' : '' }}"
                                                            {{ $dispatch->status !== 'pending' && $dispatch->status !== 'approved' ? 'readonly' : '' }}
                                                            data-item-id="{{ $item->id }}"
                                                            data-serial-index="{{ $i }}">
                                                    @endfor
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if ($dispatch->status === 'pending')
                                                    <button type="button"
                                                        class="text-red-600 hover:text-red-900 remove-backup-item-btn"
                                                        data-item-id="{{ $item->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="text-gray-400">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Danh sách sản phẩm chung (nếu có) -->
                @if (false && $generalItems->count() > 0)
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
                                                @if ($dispatch->status === 'pending')
                                                    <input type="number"
                                                        name="general_items[{{ $item->id }}][quantity]"
                                                        value="{{ $item->quantity }}" min="1"
                                                        class="w-20 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                @else
                                                    <span class="text-sm text-gray-600">{{ $item->quantity }}</span>
                                                    <input type="hidden"
                                                        name="general_items[{{ $item->id }}][quantity]"
                                                        value="{{ $item->quantity }}">
                                                @endif
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
                                                            class="w-32 border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 {{ $dispatch->status !== 'pending' && $dispatch->status !== 'approved' ? 'bg-gray-100' : '' }}"
                                                            {{ $dispatch->status !== 'pending' && $dispatch->status !== 'approved' ? 'readonly' : '' }}
                                                            data-item-id="{{ $item->id }}"
                                                            data-serial-index="{{ $i }}">
                                                    @endfor
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if ($dispatch->status === 'pending')
                                                    <button type="button"
                                                        class="text-red-600 hover:text-red-900 remove-general-item-btn"
                                                        data-item-id="{{ $item->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="text-gray-400">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Empty state message is now handled by JavaScript -->

                <!-- Phần chọn sản phẩm và hiển thị items đã chọn -->
                @if (true)
                    <!-- Always show, but enable/disable based on status -->
                    <!-- Bảng thành phẩm theo hợp đồng (hiển thị conditional) -->
                    <div id="selected-contract-products"
                        class="bg-white rounded-xl shadow-md p-6 border border-blue-200 mb-6 hidden">
                        <h2 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                            <i class="fas fa-file-contract text-blue-500 mr-2"></i>
                            Danh sách thành phẩm theo hợp đồng
                        </h2>
                        
                        @if ($dispatch->status === 'pending')
                            <!-- Chọn thành phẩm hợp đồng -->
                            <div class="mb-4">
                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <select id="contract_product_select"
                                            class="w-full border border-blue-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50">
                                            <option value="">-- Chọn thành phẩm theo hợp đồng --</option>
                                </select>
                            </div>
                                    <button type="button" id="add_contract_product_btn"
                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                        <i class="fas fa-plus mr-1"></i> Thêm
                                </button>
                        </div>
                    </div>
                    @endif
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" id="contract-product-table">
                                <thead class="bg-blue-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            MÃ SP</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            TÊN THÀNH PHẨM</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            ĐƠN VỊ</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            TỒN KHO</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            KHO XUẤT</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            SỐ LƯỢNG XUẤT</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            SERIAL</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Nội dung sẽ được tạo bởi JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Bảng thiết bị dự phòng (hiển thị conditional) -->
                    <div id="selected-backup-products"
                        class="bg-white rounded-xl shadow-md p-6 border border-orange-200 mb-6 hidden">
                        <h2 class="text-lg font-semibold text-orange-800 mb-4 flex items-center">
                            <i class="fas fa-shield-alt text-orange-500 mr-2"></i>
                            Danh sách thiết bị dự phòng
                        </h2>

                        @if ($dispatch->status === 'pending')
                            <!-- Chọn thiết bị dự phòng -->
                            <div class="mb-4">
                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <select id="backup_product_select"
                                            class="w-full border border-orange-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 bg-orange-50">
                                            <option value="">-- Chọn thiết bị dự phòng --</option>
                                        </select>
                                    </div>
                                    <button type="button" id="add_backup_product_btn"
                                        class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                                        <i class="fas fa-plus mr-1"></i> Thêm
                                    </button>
                                </div>
                            </div>
                        @endif
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200" id="backup-product-table">
                                <thead class="bg-orange-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            MÃ SP</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            TÊN THÀNH PHẨM</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            ĐƠN VỊ</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            TỒN KHO</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            KHO XUẤT</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            SỐ LƯỢNG XUẤT</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            SERIAL</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Nội dung sẽ được tạo bởi JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('inventory.dispatch.show', $dispatch->id) }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors">
                        Hủy
                    </a>
                    @if ($dispatch->status !== 'cancelled' && $dispatch->status !== 'completed')
                        <button type="submit" id="submit-btn"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> 
                            @if ($dispatch->status === 'pending')
                                Cập nhật phiếu xuất
                            @else
                                Cập nhật thông tin bổ sung
                            @endif
                        </button>
                    @else
                        <span class="bg-gray-400 text-white px-5 py-2 rounded-lg cursor-not-allowed">
                            <i class="fas fa-lock mr-2"></i> Không thể chỉnh sửa
                        </span>
                    @endif
                </div>
            </form>
        </main>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables for edit page
            let availableItems = [];
            let selectedContractProducts = [];
            let selectedBackupProducts = [];

            // Always load existing items, only load available items if pending
            loadExistingItemsOnly();
            
            // Show appropriate tables based on dispatch_detail
            handleDispatchDetailDisplay();
            
            // Force render after a delay to ensure everything is loaded
            setTimeout(function() {
                renderContractProductTable();
                renderBackupProductTable();
                handleDispatchDetailDisplay();

                // Load available serials for all serial selects
                loadAvailableSerials();

                // Initial validation and option availability update
                setTimeout(function() {
                    updateSerialOptionsAvailability();
                }, 500);
            }, 100);

            @if ($dispatch->status === 'pending')
                loadAvailableItems();
            @endif

            // Load available items from API
            async function loadAvailableItems() {
                try {
                    const response = await fetch('/api/dispatch/items/all');
                    const data = await response.json();
                    
                    if (data.success) {
                        availableItems = data.items;
                        // Load existing items into selected arrays
                        loadExistingItems();
                        // Populate product dropdowns
                        populateProductDropdowns();
                        // Setup dropdown handlers
                        setupDropdownHandlers();
                    }
                } catch (error) {
                    console.error('Error loading available items:', error);
                }
            }

            // Load existing items without API (for all statuses)
            function loadExistingItemsOnly() {
                @foreach ($dispatch->items as $item)
                    {
                    const existingItem = {
                        id: {{ $item->item_id }},
                        code: '{{ $item->item_code }}',
                        name: '{{ $item->item_name }}',
                        type: '{{ $item->item_type }}',
                        unit: '{{ $item->item_unit }}',
                        quantity: {{ $item->quantity }},
                        selected_warehouse_id: {{ $item->warehouse_id }},
                        current_stock: 0, // No API data for non-pending
                        category: '{{ $item->category }}',
                        serial_numbers: @json($item->serial_numbers ?? []),
                        warehouses: [{
                            warehouse_id: {{ $item->warehouse_id }},
                                warehouse_name: '{{ $item->warehouse->name ?? 'N/A' }}',
                            quantity: 0 // No stock info for non-pending
                        }],
                        existing_item_id: {{ $item->id }}, // Track original dispatch item ID
                        is_existing: true // Mark as existing item
                    };

                        @if ($item->category === 'contract')
                        selectedContractProducts.push(existingItem);
                        @elseif ($item->category === 'backup')
                        selectedBackupProducts.push(existingItem);
                    @endif
                    }
                @endforeach

                // Render selected product tables với items hiện tại
                renderContractProductTable();
                renderBackupProductTable();
            }

            // Load existing dispatch items into selected arrays (for pending with API data)
            function loadExistingItems() {
                // Clear arrays first to avoid duplicates
                selectedContractProducts = [];
                selectedBackupProducts = [];
                @foreach ($dispatch->items as $item)
                    {
                    const existingItem = {
                        id: {{ $item->item_id }},
                        code: '{{ $item->item_code }}',
                        name: '{{ $item->item_name }}',
                        type: '{{ $item->item_type }}',
                        unit: '{{ $item->item_unit }}',
                        quantity: {{ $item->quantity }},
                        selected_warehouse_id: {{ $item->warehouse_id }},
                        current_stock: 0, // Will be updated from API
                        category: '{{ $item->category }}',
                        serial_numbers: @json($item->serial_numbers ?? []),
                        warehouses: [], // Will be populated from availableItems
                        existing_item_id: {{ $item->id }}, // Track original dispatch item ID
                        is_existing: true // Mark as existing item
                    };

                    // Find the corresponding item in availableItems to get warehouse info
                    const foundItem = availableItems.find(item => 
                        item.id == {{ $item->item_id }} && item.type == '{{ $item->item_type }}'
                    );
                    
                    if (foundItem) {
                        existingItem.warehouses = foundItem.warehouses;
                            const warehouse = foundItem.warehouses.find(w => w.warehouse_id ==
                                {{ $item->warehouse_id }});
                        if (warehouse) {
                            existingItem.current_stock = warehouse.quantity;
                        }
                    }

                        @if ($item->category === 'contract')
                        selectedContractProducts.push(existingItem);
                        @elseif ($item->category === 'backup')
                        selectedBackupProducts.push(existingItem);
                    @endif
                    }
                @endforeach

                // Render selected product tables với items hiện tại
                renderContractProductTable();
                renderBackupProductTable();
            }

            // Xử lý thay đổi loại hình xuất kho trong edit form
            const dispatchTypeSelect = document.getElementById('dispatch_type');
            const dispatchDetailSelect = document.getElementById('dispatch_detail');
            const projectReceiverSelect = document.getElementById('project_receiver');
            
            if (dispatchTypeSelect) {
                dispatchTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;
                    
                    const projectSection = document.getElementById('project_section');
                    const rentalSection = document.getElementById('rental_section');
                    const projectReceiverInput = document.getElementById('project_receiver');
                    const rentalReceiverInput = document.getElementById('rental_receiver');

                    // Reset all sections
                    projectSection.classList.add('hidden');
                    rentalSection.classList.add('hidden');
                    projectReceiverInput.removeAttribute('required');
                    rentalReceiverInput.removeAttribute('required');

                    if (selectedType === 'rental') {
                        // Hiển thị phần cho thuê, ẩn phần dự án
                        rentalSection.classList.remove('hidden');
                        rentalReceiverInput.setAttribute('required', 'required');
                        
                        // Set project_receiver = rental_receiver để tương thích với backend
                        rentalReceiverInput.addEventListener('input', function() {
                            projectReceiverInput.value = this.value;
                        });
                        
                        // Reset dispatch detail về mặc định cho rental
                        if (dispatchDetailSelect) {
                            dispatchDetailSelect.disabled = false;
                        }
                        
                        // Xóa hidden input nếu có
                        let hiddenDispatchDetail = document.getElementById('hidden_dispatch_detail');
                        if (hiddenDispatchDetail) {
                            hiddenDispatchDetail.remove();
                        }
                    } else if (selectedType === 'project') {
                        // Hiển thị phần dự án, ẩn phần cho thuê
                        projectSection.classList.remove('hidden');
                        projectReceiverInput.setAttribute('required', 'required');
                        
                        // Reset dispatch detail về mặc định cho project
                        if (dispatchDetailSelect) {
                            dispatchDetailSelect.disabled = false;
                        }
                        
                        // Xóa hidden input nếu có
                        hiddenDispatchDetail = document.getElementById('hidden_dispatch_detail');
                        if (hiddenDispatchDetail) {
                            hiddenDispatchDetail.remove();
                        }
                    } else if (selectedType === 'warranty') {
                        // Hiển thị phần dự án, ẩn phần cho thuê
                        projectSection.classList.remove('hidden');
                        projectReceiverInput.setAttribute('required', 'required');
                        
                        // Tự động chọn "backup" và disable dropdown cho warranty
                        if (dispatchDetailSelect) {
                            dispatchDetailSelect.value = 'backup';
                            dispatchDetailSelect.disabled = true;
                            
                            // Tạo hidden input để đảm bảo giá trị được gửi đi
                            let hiddenDispatchDetail = document.getElementById('hidden_dispatch_detail');
                            if (!hiddenDispatchDetail) {
                                hiddenDispatchDetail = document.createElement('input');
                                hiddenDispatchDetail.type = 'hidden';
                                hiddenDispatchDetail.id = 'hidden_dispatch_detail';
                                hiddenDispatchDetail.name = 'dispatch_detail';
                                document.querySelector('form').appendChild(hiddenDispatchDetail);
                            }
                            hiddenDispatchDetail.value = 'backup';
                        }
                    }
                });
                
                // Trigger change event để setup ban đầu
                dispatchTypeSelect.dispatchEvent(new Event('change'));
            }

            // Setup ban đầu cho warranty dispatch
            const currentDispatchType = document.getElementById('dispatch_type')?.value;
            if (currentDispatchType === 'warranty' && dispatchDetailSelect) {
                dispatchDetailSelect.value = 'backup';
                dispatchDetailSelect.disabled = true;
                
                // Tạo hidden input để đảm bảo giá trị được gửi đi
                let hiddenDispatchDetail = document.getElementById('hidden_dispatch_detail');
                if (!hiddenDispatchDetail) {
                    hiddenDispatchDetail = document.createElement('input');
                    hiddenDispatchDetail.type = 'hidden';
                    hiddenDispatchDetail.id = 'hidden_dispatch_detail';
                    hiddenDispatchDetail.name = 'dispatch_detail';
                    document.querySelector('form').appendChild(hiddenDispatchDetail);
                }
                hiddenDispatchDetail.value = 'backup';
            }

            // Xử lý thay đổi dự án - cập nhật thời gian bảo hành và project_id
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

            // Nếu phiếu đã duyệt, load available serials cho các input serial
            @if ($dispatch->status === 'approved')
                loadAvailableSerials();
            @endif

            // Hàm load available serial numbers cho tất cả serial selects
            async function loadAvailableSerials() {
                const serialSelects = document.querySelectorAll('select[name*="serial_numbers"]');

                for (const select of serialSelects) {
                    if (select.disabled) continue; // Skip disabled selects

                    const itemType = select.dataset.itemType;
                    const itemId = select.dataset.itemId;
                    const warehouseId = select.dataset.warehouseId;
                    const selectedSerial = select.dataset.selectedSerial;

                    // Also check if select already has a value selected
                    const currentValue = select.value;

                    if (!itemType || !itemId || !warehouseId) continue;

                    try {
                        const response = await fetch(
                            `/api/dispatch/item-serials?item_type=${itemType}&item_id=${itemId}&warehouse_id=${warehouseId}&current_dispatch_id={{ $dispatch->id }}`
                        );
                        const data = await response.json();
                        
                        if (data.success) {
                            // Log serial availability info for debugging
                            if (data.total_serials && data.used_serials) {
                                console.log(
                                    `Serials for ${itemType} ${itemId}: ${data.available_serials}/${data.total_serials} available (${data.used_serials} used)`
                                );

                                // Show warning if low availability
                                if (data.available_serials < data.total_serials * 0.2 && data
                                    .available_serials > 0) {
                                    console.warn(
                                        `Low serial availability for ${itemType} ${itemId}: Only ${data.available_serials} out of ${data.total_serials} serials available`
                                    );
                                } else if (data.available_serials === 0 && data.total_serials > 0) {
                                    console.error(
                                        `No serial available for ${itemType} ${itemId}: All ${data.total_serials} serials are already used in approved dispatches`
                                    );
                                }
                            }

                            if (data.serials.length > 0) {
                                // Save currently selected value if any
                                const valueToPreserve = currentValue || selectedSerial;

                                // Clear existing options except default and currently selected
                                const optionsToRemove = [];
                                for (let i = 1; i < select.children.length; i++) {
                                    const option = select.children[i];
                                    if (option.value !== valueToPreserve) {
                                        optionsToRemove.push(option);
                                    }
                                }
                                optionsToRemove.forEach(option => option.remove());

                                // Add serial options that are not already added
                            data.serials.forEach(serial => {
                                    // Check if this serial already exists in the select
                                    const existingOption = Array.from(select.options).find(opt => opt
                                        .value === serial);
                                    if (!existingOption) {
                                const option = document.createElement('option');
                                option.value = serial;
                                        option.textContent = serial;
                                        select.appendChild(option);
                                    }
                                });

                                // Set the correct selected value
                                if (valueToPreserve) {
                                    select.value = valueToPreserve;
                                }
                            } else {
                                // If no serials available from API but we have a selected value (for existing items)
                                // Keep the selected value as an option
                                const valueToPreserve = currentValue || selectedSerial;
                                if (valueToPreserve && !Array.from(select.options).find(opt => opt.value ===
                                        valueToPreserve)) {
                                    const option = document.createElement('option');
                                    option.value = valueToPreserve;
                                    option.textContent = valueToPreserve;
                                    option.selected = true;
                                    select.appendChild(option);
                                }

                                // Add change event listener for validation
                                if (!select.hasAttribute('data-validation-listener')) {
                                    select.addEventListener('change', validateSerialOnChange);
                                    select.setAttribute('data-validation-listener', 'true');
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Error loading serials:', error);

                        // If API call fails but we have a selected value, preserve it
                        const valueToPreserve = currentValue || selectedSerial;
                        if (valueToPreserve && !Array.from(select.options).find(opt => opt.value ===
                                valueToPreserve)) {
                            const option = document.createElement('option');
                            option.value = valueToPreserve;
                            option.textContent = valueToPreserve;
                            option.selected = true;
                            select.appendChild(option);
                        }

                        // Add change event listener for validation even on error
                        if (!select.hasAttribute('data-validation-listener')) {
                            select.addEventListener('change', validateSerialOnChange);
                            select.setAttribute('data-validation-listener', 'true');
                        }
                    }
                }
            }

            // Populate product dropdowns giống trang create
            function populateProductDropdowns() {
                const contractProductSelect = document.getElementById('contract_product_select');
                const backupProductSelect = document.getElementById('backup_product_select');

                // Cập nhật dropdown hợp đồng
                if (contractProductSelect) {
                    contractProductSelect.innerHTML =
                        '<option value="">-- Chọn thành phẩm theo hợp đồng --</option>';
                }

                // Cập nhật dropdown dự phòng
                if (backupProductSelect) {
                    backupProductSelect.innerHTML = '<option value="">-- Chọn thiết bị dự phòng --</option>';
                }

                // Thêm options từ availableItems
                availableItems.forEach(item => {
                    const contractOption = document.createElement('option');
                    contractOption.value = item.id;
                    contractOption.textContent = item.display_name;

                    const backupOption = document.createElement('option');
                    backupOption.value = item.id;
                    backupOption.textContent = item.display_name;

                    if (contractProductSelect) contractProductSelect.appendChild(contractOption);
                    if (backupProductSelect) backupProductSelect.appendChild(backupOption);
                });
            }

            // Setup dropdown handlers giống trang create
            function setupDropdownHandlers() {
                // Xử lý thêm sản phẩm hợp đồng
                const addContractProductBtn = document.getElementById('add_contract_product_btn');
                const contractProductSelect = document.getElementById('contract_product_select');

                if (addContractProductBtn) {
                    addContractProductBtn.addEventListener('click', function() {
                        const selectedProductId = contractProductSelect.value;

                        if (!selectedProductId) {
                            alert('Vui lòng chọn thành phẩm hợp đồng để thêm!');
                            return;
                        }

                        addContractProduct(parseInt(selectedProductId));
                        contractProductSelect.value = '';
                    });
                }

                // Xử lý thêm thiết bị dự phòng
                const addBackupProductBtn = document.getElementById('add_backup_product_btn');
                const backupProductSelect = document.getElementById('backup_product_select');

                if (addBackupProductBtn) {
                    addBackupProductBtn.addEventListener('click', function() {
                        const selectedProductId = backupProductSelect.value;

                        if (!selectedProductId) {
                            alert('Vui lòng chọn thiết bị dự phòng để thêm!');
                            return;
                        }

                        addBackupProduct(parseInt(selectedProductId));
                        backupProductSelect.value = '';
                    });
                }
            }

            // Hàm thêm sản phẩm hợp đồng
            function addContractProduct(productId) {
                const foundProduct = availableItems.find(p => p.id == productId);

                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                // Kiểm tra xem sản phẩm đã được thêm chưa
                const existingProduct = selectedContractProducts.find(p => p.id === foundProduct.id);

                if (existingProduct) {
                    alert('Sản phẩm này đã được thêm vào danh sách hợp đồng!');
                        return;
                } else {
                    // Thêm sản phẩm mới
                    selectedContractProducts.push({
                        ...foundProduct,
                        quantity: 1,
                        selected_warehouse_id: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[
                            0].warehouse_id : null,
                        current_stock: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[0]
                            .quantity : 0,
                        is_existing: false
                    });

                    // Cập nhật giao diện
                    renderContractProductTable();

                    // Kiểm tra tồn kho sau khi thêm sản phẩm
                    showStockWarnings();
                }
            }

            // Hàm thêm thiết bị dự phòng
            function addBackupProduct(productId) {
                const foundProduct = availableItems.find(p => p.id == productId);

                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                        return;
                    }
                    
                // Kiểm tra xem sản phẩm đã được thêm chưa
                const existingProduct = selectedBackupProducts.find(p => p.id === foundProduct.id);

                if (existingProduct) {
                    alert('Thiết bị này đã được thêm vào danh sách dự phòng!');
                    return;
                    } else {
                    // Thêm sản phẩm mới
                    selectedBackupProducts.push({
                        ...foundProduct,
                        quantity: 1,
                        selected_warehouse_id: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[
                            0].warehouse_id : null,
                        current_stock: foundProduct.warehouses.length > 0 ? foundProduct.warehouses[0]
                            .quantity : 0,
                        is_existing: false
                    });

                    // Cập nhật giao diện
                        renderBackupProductTable();

                    // Kiểm tra tồn kho sau khi thêm sản phẩm
                    showStockWarnings();
                }
            }

            // Handle dispatch detail display
            function handleDispatchDetailDisplay() {
                const dispatchDetail = '{{ $dispatch->dispatch_detail }}';
                const contractTable = document.getElementById('selected-contract-products');
                const backupTable = document.getElementById('selected-backup-products');
                
                // Hide all tables first
                if (contractTable) contractTable.classList.add('hidden');
                if (backupTable) backupTable.classList.add('hidden');
                
                // Show tables based on dispatch_detail
                if (dispatchDetail === 'all') {
                    if (contractTable) contractTable.classList.remove('hidden');
                    if (backupTable) backupTable.classList.remove('hidden');
                } else if (dispatchDetail === 'contract') {
                    if (contractTable) contractTable.classList.remove('hidden');
                } else if (dispatchDetail === 'backup') {
                    if (backupTable) backupTable.classList.remove('hidden');
                }
            }

            // Hàm tạo serial inputs theo quantity giống trang create
            function generateSerialInputs(quantity, category, productId, index) {
                let inputs = '';
                const borderColor = category === 'contract' ? 'border-blue-300 focus:ring-blue-500' :
                    'border-orange-300 focus:ring-orange-500';

                // Check if this is an existing item or new item
                const product = (category === 'contract' ? selectedContractProducts : selectedBackupProducts)[
                    index];
                const isExisting = product && product.is_existing;

                for (let i = 0; i < quantity; i++) {
                    let inputName;
                    if (isExisting) {
                        // Existing item serial input names
                        inputName = `${category}_items[${product.existing_item_id}][serial_numbers][${i}]`;
                    } else {
                        // New item serial input names
                        inputName = `items[${category}_${productId}_${index}][serial_numbers][${i}]`;
                    }

                    inputs +=
                        `<select name="${inputName}" 
                                class="w-32 border ${borderColor} rounded px-2 py-1 text-xs focus:outline-none focus:ring-1"
                                data-item-type="${product?.type || 'product'}" 
                                data-item-id="${productId}" 
                                data-warehouse-id="${product?.selected_warehouse_id || ''}"
                                data-serial-index="${i}"
                                data-selected-serial="">
                            <option value="">-- Chọn Serial ${i + 1} --</option>
                        </select>`;
                }
                return inputs;
            }

            // Hàm tạo serial inputs với giá trị sẵn có (cho existing items)
            function generateSerialInputsWithValues(quantity, category, productId, index, existingSerials) {
                let inputs = '';
                const borderColor = category === 'contract' ? 'border-blue-300 focus:ring-blue-500' :
                    'border-orange-300 focus:ring-orange-500';

                // Check if this is an existing item or new item
                const product = (category === 'contract' ? selectedContractProducts : selectedBackupProducts)[
                    index];
                const isExisting = product && product.is_existing;

                for (let i = 0; i < quantity; i++) {
                    let inputName;
                    if (isExisting) {
                        // Existing item serial input names
                        inputName = `${category}_items[${product.existing_item_id}][serial_numbers][${i}]`;
                    } else {
                        // New item serial input names
                        inputName = `items[${category}_${productId}_${index}][serial_numbers][${i}]`;
                    }

                    const serialValue = existingSerials[i] || '';

                    // Create select with existing serial value if available
                    let selectOptions = `<option value="">-- Chọn Serial ${i + 1} --</option>`;
                    if (serialValue) {
                        selectOptions += `<option value="${serialValue}" selected>${serialValue}</option>`;
                    }

                    inputs +=
                        `<select name="${inputName}" 
                                class="w-32 border ${borderColor} rounded px-2 py-1 text-xs focus:outline-none focus:ring-1"
                                data-item-type="${product?.type || 'product'}" 
                                data-item-id="${productId}" 
                                data-warehouse-id="${product?.selected_warehouse_id || ''}"
                                data-serial-index="${i}"
                                data-selected-serial="${serialValue}">
                            ${selectOptions}
                        </select>`;
                }
                return inputs;
            }

            // Hàm cập nhật serial inputs khi quantity thay đổi
            function updateSerialInputsCreate(quantity, category, productId, index) {
                const container = document.getElementById(`${category}-serials-${index}`);
                if (container) {
                    // Lưu giá trị hiện tại
                    const currentInputs = container.querySelectorAll('input');
                    const currentValues = Array.from(currentInputs).map(input => input.value);

                    // Tạo lại inputs
                    container.innerHTML = '';
                    const borderColor = category === 'contract' ? 'border-blue-300 focus:ring-blue-500' :
                        'border-orange-300 focus:ring-orange-500';

                    // Check if this is an existing item or new item
                    const product = (category === 'contract' ? selectedContractProducts : selectedBackupProducts)[
                        index];
                    const isExisting = product && product.is_existing;

                    for (let i = 0; i < quantity; i++) {
                        const select = document.createElement('select');

                        // Set correct name based on existing or new item
                        if (isExisting) {
                            select.name = `${category}_items[${product.existing_item_id}][serial_numbers][${i}]`;
                        } else {
                            select.name = `items[${category}_${productId}_${index}][serial_numbers][${i}]`;
                        }

                        select.className =
                            `w-32 border ${borderColor} rounded px-2 py-1 text-xs focus:outline-none focus:ring-1`;

                        // Set data attributes for loading serials
                        select.setAttribute('data-item-type', product?.type || 'product');
                        select.setAttribute('data-item-id', productId);
                        select.setAttribute('data-warehouse-id', product?.selected_warehouse_id || '');
                        select.setAttribute('data-serial-index', i);
                        select.setAttribute('data-selected-serial', currentValues[i] || '');

                        // Add change event listener for validation
                        select.addEventListener('change', validateSerialOnChange);
                        select.setAttribute('data-validation-listener', 'true');

                        // Add default option
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = `-- Chọn Serial ${i + 1} --`;
                        select.appendChild(defaultOption);

                        container.appendChild(select);
                    }

                    // Load available serials for new select elements
                    loadAvailableSerials();
                }
            }

            // Hàm kiểm tra serial numbers có bị trùng lặp không
            function validateSerialNumbers() {
                const allSerialSelects = document.querySelectorAll('select[name*="serial_numbers"]');
                const selectedSerials = [];
                const duplicates = [];

                // Thu thập tất cả serial numbers đã chọn
                allSerialSelects.forEach(select => {
                    if (select.value && select.value.trim() !== '' && !select.disabled) {
                        const serialValue = select.value.trim();
                        if (selectedSerials.includes(serialValue)) {
                            if (!duplicates.includes(serialValue)) {
                                duplicates.push(serialValue);
                            }
                        } else {
                            selectedSerials.push(serialValue);
                        }
                    }
                });

                return duplicates;
            }

            // Hàm hiển thị cảnh báo serial trùng lặp
            function showSerialDuplicateWarning(duplicates) {
                // Xóa cảnh báo cũ
                const oldWarning = document.querySelector('.serial-duplicate-warning');
                if (oldWarning) {
                    oldWarning.remove();
                }

                if (duplicates.length > 0) {
                    // Tạo div cảnh báo
                    const warningDiv = document.createElement('div');
                    warningDiv.className =
                        'serial-duplicate-warning bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
                    warningDiv.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Cảnh báo Serial trùng lặp:</strong>
                        </div>
                        <p class="mt-2">Các serial numbers sau đã được chọn nhiều lần: <strong>${duplicates.join(', ')}</strong></p>
                        <p class="text-sm mt-1">Vui lòng chọn serial numbers khác nhau cho mỗi sản phẩm.</p>`;

                    // Thêm vào đầu form
                    const form = document.querySelector('form');
                    form.insertBefore(warningDiv, form.firstChild);
                }
            }

            // Hàm validate serial khi user thay đổi selection
            function validateSerialOnChange() {
                const duplicates = validateSerialNumbers();
                showSerialDuplicateWarning(duplicates);

                // Disable các option đã chọn ở select khác
                updateSerialOptionsAvailability();
            }

            // Hàm cập nhật available options cho serial selects
            function updateSerialOptionsAvailability() {
                const allSerialSelects = document.querySelectorAll('select[name*="serial_numbers"]');
                const selectedValues = [];

                // Thu thập tất cả giá trị đã chọn
                allSerialSelects.forEach(select => {
                    if (select.value && select.value.trim() !== '' && !select.disabled) {
                        selectedValues.push(select.value.trim());
                    }
                });

                // Cập nhật từng select
                allSerialSelects.forEach(currentSelect => {
                    if (currentSelect.disabled) return; // Skip disabled selects

                    const currentValue = currentSelect.value;

                    Array.from(currentSelect.options).forEach(option => {
                        if (option.value === '') return; // Skip default option

                        if (selectedValues.includes(option.value) && option.value !==
                            currentValue) {
                            // Serial này đã được chọn ở select khác
                            option.disabled = true;
                            option.style.color = '#9CA3AF'; // Gray color
                            option.style.backgroundColor = '#F3F4F6';
                        } else {
                            // Serial có thể chọn
                            option.disabled = false;
                            option.style.color = '';
                            option.style.backgroundColor = '';
                        }
                    });
                });
            }

            // Hàm hiển thị bảng sản phẩm hợp đồng giống trang create
            function renderContractProductTable() {
                const contractProductList = document.querySelector('#contract-product-table tbody');
                
                if (!contractProductList) return;

                // Xóa tất cả hàng hiện tại
                contractProductList.innerHTML = '';

                if (selectedContractProducts.length === 0) {
                    contractProductList.innerHTML =
                        '<tr><td colspan="8" class="px-6 py-4 text-sm text-blue-500 text-center">Chưa có thành phẩm hợp đồng nào được thêm</td></tr>';
                    return;
                }
                
                selectedContractProducts.forEach((product, index) => {
                    // Tạo dropdown kho xuất cho sản phẩm hợp đồng
                    let warehouseOptions = '';

                    // Try to get warehouse info from availableItems first (for pending), then from product.warehouses (for existing items)
                    let warehousesData = null;
                    const productItem = availableItems.find(item => item.id === product.id);
                    if (productItem && productItem.warehouses) {
                        warehousesData = productItem.warehouses;
                    } else if (product.warehouses) {
                        warehousesData = product.warehouses;
                    }

                    if (warehousesData) {
                        warehousesData.forEach(warehouse => {
                            const selected = warehouse.warehouse_id == product
                                .selected_warehouse_id ? 'selected' : '';
                            // For approved dispatches, don't show stock info since it might be outdated
                            const stockInfo = '{{ $dispatch->status }}' === 'pending' ?
                                ` (Tồn: ${warehouse.quantity})` : '';
                            warehouseOptions +=
                                `<option value="${warehouse.warehouse_id}" ${selected} data-quantity="${warehouse.quantity}">${warehouse.warehouse_name}${stockInfo}</option>`;
                        });
                    }

                    const row = document.createElement('tr');
                    const isReadonly = product.is_existing && '{{ $dispatch->status }}' !== 'pending';

                    // Create hidden inputs for form submission (only for new items, not existing ones)
                    const hiddenInputsHtml = product.is_existing ? `
                        <!-- Existing item hidden inputs -->
                        <input type="hidden" name="contract_items[${product.existing_item_id}][item_type]" value="${product.type}">
                        <input type="hidden" name="contract_items[${product.existing_item_id}][item_id]" value="${product.id}">
                        <input type="hidden" name="contract_items[${product.existing_item_id}][warehouse_id]" value="${product.selected_warehouse_id}">
                        <input type="hidden" name="contract_items[${product.existing_item_id}][category]" value="contract">
                        <input type="hidden" name="contract_items[${product.existing_item_id}][quantity]" value="${product.quantity}" class="contract-quantity-hidden" data-index="${index}">
                    ` : `
                        <!-- New item hidden inputs -->
                        <input type="hidden" name="items[contract_${product.id}_${index}][item_type]" value="${product.type}">
                        <input type="hidden" name="items[contract_${product.id}_${index}][item_id]" value="${product.id}">
                        <input type="hidden" name="items[contract_${product.id}_${index}][warehouse_id]" value="${product.selected_warehouse_id}" class="contract-warehouse-hidden" data-index="${index}">
                        <input type="hidden" name="items[contract_${product.id}_${index}][category]" value="contract">
                        <input type="hidden" name="items[contract_${product.id}_${index}][quantity]" value="${product.quantity}" class="contract-quantity-hidden" data-index="${index}">
                    `;

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-900 font-medium">${product.code}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.unit}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" id="contract-stock-${index}">${product.current_stock || 0}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select class="w-32 border border-blue-300 rounded px-2 py-1 text-sm contract-warehouse-select" 
                                data-index="${index}" ${isReadonly ? 'disabled' : ''}>
                                ${warehouseOptions}
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" value="${product.quantity}" min="1" max="${product.current_stock || 0}" 
                                class="w-20 border border-blue-300 rounded px-2 py-1 text-sm contract-quantity-input" 
                                data-index="${index}" id="contract-quantity-${index}" ${isReadonly ? 'readonly' : ''}>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1" id="contract-serials-${index}">
                                ${generateSerialInputsWithValues(product.quantity, 'contract', product.id, index, product.serial_numbers || [])}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            ${isReadonly ? 
                                `<span class="text-gray-400"><i class="fas fa-lock"></i></span>` :
                                `<button type="button" class="text-red-600 hover:text-red-900 remove-contract-product" data-index="${index}">
                                    <i class="fas fa-trash"></i>
                                </button>`}
                        </td>
                        ${hiddenInputsHtml}
                    `;
                    contractProductList.appendChild(row);
                });

                // Thêm event listeners cho dropdown kho xuất hợp đồng
                const contractWarehouseSelects = contractProductList.querySelectorAll('.contract-warehouse-select');
                contractWarehouseSelects.forEach(select => {
                    select.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newWarehouseId = parseInt(this.value);
                        const selectedOption = this.options[this.selectedIndex];
                        const newQuantity = parseInt(selectedOption.dataset.quantity);

                        // Cập nhật thông tin kho đã chọn
                        selectedContractProducts[index].selected_warehouse_id = newWarehouseId;
                        selectedContractProducts[index].current_stock = newQuantity;

                        // Cập nhật hiển thị tồn kho
                        const stockCell = document.getElementById(`contract-stock-${index}`);
                        if (stockCell) {
                            stockCell.textContent = newQuantity;
                        }

                        // Cập nhật max cho input số lượng
                        const quantityInput = document.getElementById(`contract-quantity-${index}`);
                        if (quantityInput) {
                            quantityInput.max = newQuantity;
                            // Nếu số lượng hiện tại lớn hơn tồn kho mới, giảm xuống
                            if (parseInt(quantityInput.value) > newQuantity) {
                                quantityInput.value = Math.min(parseInt(quantityInput.value),
                                    newQuantity);
                                selectedContractProducts[index].quantity = parseInt(quantityInput
                                    .value);
                            }
                        }

                        // Cập nhật hidden inputs
                        const hiddenWarehouseInput = document.querySelector(
                            `.contract-warehouse-hidden[data-index="${index}"]`);
                        if (hiddenWarehouseInput) {
                            hiddenWarehouseInput.value = newWarehouseId;
                        }

                        const hiddenQuantityInput = document.querySelector(
                            `.contract-quantity-hidden[data-index="${index}"]`);
                        if (hiddenQuantityInput) {
                            hiddenQuantityInput.value = quantityInput ? quantityInput.value :
                                selectedContractProducts[index].quantity;
                        }

                        // Cập nhật data-warehouse-id cho tất cả serial selects của product này
                        const serialSelects = document.querySelectorAll(
                            `#contract-serials-${index} select`);
                        serialSelects.forEach(select => {
                            select.setAttribute('data-warehouse-id', newWarehouseId);
                        });

                        // Load lại available serials cho warehouse mới
                        loadAvailableSerials();

                        // Kiểm tra tồn kho ngay khi thay đổi kho
                        showStockWarnings();
                    });
                });

                // Thêm event listeners cho các input và nút xóa
                const contractQuantityInputs = contractProductList.querySelectorAll('.contract-quantity-input');
                contractQuantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newQuantity = parseInt(this.value);
                        if (selectedContractProducts[index]) {
                            selectedContractProducts[index].quantity = newQuantity;
                            // Cập nhật serial inputs
                            updateSerialInputsCreate(newQuantity, 'contract',
                                selectedContractProducts[index].id, index);
                        }

                        // Cập nhật hidden quantity input
                        const hiddenQuantityInput = document.querySelector(
                            `.contract-quantity-hidden[data-index="${index}"]`);
                        if (hiddenQuantityInput) {
                            hiddenQuantityInput.value = newQuantity;
                        }

                        // Kiểm tra tồn kho ngay khi thay đổi
                        showStockWarnings();
                    });
                });

                // Load available serials cho contract products
                loadAvailableSerials();

                const removeContractButtons = contractProductList.querySelectorAll('.remove-contract-product');
                removeContractButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                        selectedContractProducts.splice(index, 1);
                        renderContractProductTable();
                        // Kiểm tra lại tồn kho sau khi xóa sản phẩm
                        showStockWarnings();
                    });
                });
            }

            // Hàm hiển thị bảng thiết bị dự phòng giống trang create
            function renderBackupProductTable() {
                const backupProductList = document.querySelector('#backup-product-table tbody');
                
                if (!backupProductList) return;

                // Xóa tất cả hàng hiện tại
                backupProductList.innerHTML = '';

                if (selectedBackupProducts.length === 0) {
                    backupProductList.innerHTML =
                        '<tr><td colspan="8" class="px-6 py-4 text-sm text-orange-500 text-center">Chưa có thiết bị dự phòng nào được thêm</td></tr>';
                    return;
                }
                
                selectedBackupProducts.forEach((product, index) => {
                    // Tạo dropdown kho xuất cho thiết bị dự phòng
                    let warehouseOptions = '';

                    // Try to get warehouse info from availableItems first (for pending), then from product.warehouses (for existing items)
                    let warehousesData = null;
                    const productItem = availableItems.find(item => item.id === product.id);
                    if (productItem && productItem.warehouses) {
                        warehousesData = productItem.warehouses;
                    } else if (product.warehouses) {
                        warehousesData = product.warehouses;
                    }

                    if (warehousesData) {
                        warehousesData.forEach(warehouse => {
                            const selected = warehouse.warehouse_id == product
                                .selected_warehouse_id ? 'selected' : '';
                            // For approved dispatches, don't show stock info since it might be outdated
                            const stockInfo = '{{ $dispatch->status }}' === 'pending' ?
                                ` (Tồn: ${warehouse.quantity})` : '';
                            warehouseOptions +=
                                `<option value="${warehouse.warehouse_id}" ${selected} data-quantity="${warehouse.quantity}">${warehouse.warehouse_name}${stockInfo}</option>`;
                        });
                    }

                    const row = document.createElement('tr');
                    const isReadonly = product.is_existing && '{{ $dispatch->status }}' !== 'pending';

                    // Create hidden inputs for form submission (only for new items, not existing ones)
                    const hiddenInputsHtml = product.is_existing ? `
                        <!-- Existing item hidden inputs -->
                        <input type="hidden" name="backup_items[${product.existing_item_id}][item_type]" value="${product.type}">
                        <input type="hidden" name="backup_items[${product.existing_item_id}][item_id]" value="${product.id}">
                        <input type="hidden" name="backup_items[${product.existing_item_id}][warehouse_id]" value="${product.selected_warehouse_id}">
                        <input type="hidden" name="backup_items[${product.existing_item_id}][category]" value="backup">
                        <input type="hidden" name="backup_items[${product.existing_item_id}][quantity]" value="${product.quantity}" class="backup-quantity-hidden" data-index="${index}">
                    ` : `
                        <!-- New item hidden inputs -->
                        <input type="hidden" name="items[backup_${product.id}_${index}][item_type]" value="${product.type}">
                        <input type="hidden" name="items[backup_${product.id}_${index}][item_id]" value="${product.id}">
                        <input type="hidden" name="items[backup_${product.id}_${index}][warehouse_id]" value="${product.selected_warehouse_id}" class="backup-warehouse-hidden" data-index="${index}">
                        <input type="hidden" name="items[backup_${product.id}_${index}][category]" value="backup">
                        <input type="hidden" name="items[backup_${product.id}_${index}][quantity]" value="${product.quantity}" class="backup-quantity-hidden" data-index="${index}">
                    `;

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-900 font-medium">${product.code}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.unit}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" id="backup-stock-${index}">${product.current_stock || 0}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select class="w-32 border border-orange-300 rounded px-2 py-1 text-sm backup-warehouse-select" 
                                data-index="${index}" ${isReadonly ? 'disabled' : ''}>
                                ${warehouseOptions}
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="number" value="${product.quantity}" min="1" max="${product.current_stock || 0}" 
                                class="w-20 border border-orange-300 rounded px-2 py-1 text-sm backup-quantity-input" 
                                data-index="${index}" id="backup-quantity-${index}" ${isReadonly ? 'readonly' : ''}>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1" id="backup-serials-${index}">
                                ${generateSerialInputsWithValues(product.quantity, 'backup', product.id, index, product.serial_numbers || [])}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            ${isReadonly ? 
                                `<span class="text-gray-400"><i class="fas fa-lock"></i></span>` :
                                `<button type="button" class="text-red-600 hover:text-red-900 remove-backup-product" data-index="${index}">
                                    <i class="fas fa-trash"></i>
                                </button>`}
                        </td>
                        ${hiddenInputsHtml}
                    `;
                    backupProductList.appendChild(row);
                });

                // Thêm event listeners cho dropdown kho xuất dự phòng
                const backupWarehouseSelects = backupProductList.querySelectorAll('.backup-warehouse-select');
                backupWarehouseSelects.forEach(select => {
                    select.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newWarehouseId = parseInt(this.value);
                        const selectedOption = this.options[this.selectedIndex];
                        const newQuantity = parseInt(selectedOption.dataset.quantity);

                        // Cập nhật thông tin kho đã chọn
                        selectedBackupProducts[index].selected_warehouse_id = newWarehouseId;
                        selectedBackupProducts[index].current_stock = newQuantity;

                        // Cập nhật hiển thị tồn kho
                        const stockCell = document.getElementById(`backup-stock-${index}`);
                        if (stockCell) {
                            stockCell.textContent = newQuantity;
                        }

                        // Cập nhật max cho input số lượng
                        const quantityInput = document.getElementById(`backup-quantity-${index}`);
                        if (quantityInput) {
                            quantityInput.max = newQuantity;
                            // Nếu số lượng hiện tại lớn hơn tồn kho mới, giảm xuống
                            if (parseInt(quantityInput.value) > newQuantity) {
                                quantityInput.value = Math.min(parseInt(quantityInput.value),
                                    newQuantity);
                                selectedBackupProducts[index].quantity = parseInt(quantityInput
                                    .value);
                            }
                        }

                        // Cập nhật hidden inputs
                        const hiddenWarehouseInput = document.querySelector(
                            `.backup-warehouse-hidden[data-index="${index}"]`);
                        if (hiddenWarehouseInput) {
                            hiddenWarehouseInput.value = newWarehouseId;
                        }

                        const hiddenQuantityInput = document.querySelector(
                            `.backup-quantity-hidden[data-index="${index}"]`);
                        if (hiddenQuantityInput) {
                            hiddenQuantityInput.value = quantityInput ? quantityInput.value :
                                selectedBackupProducts[index].quantity;
                        }

                        // Cập nhật data-warehouse-id cho tất cả serial selects của product này
                        const serialSelects = document.querySelectorAll(
                            `#backup-serials-${index} select`);
                        serialSelects.forEach(select => {
                            select.setAttribute('data-warehouse-id', newWarehouseId);
                        });

                        // Load lại available serials cho warehouse mới
                        loadAvailableSerials();

                        // Kiểm tra tồn kho ngay khi thay đổi kho
                showStockWarnings();
                    });
                });

                // Load available serials cho backup products
                loadAvailableSerials();

                // Thêm event listeners cho các input và nút xóa
                const backupQuantityInputs = backupProductList.querySelectorAll('.backup-quantity-input');
                backupQuantityInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const index = parseInt(this.dataset.index);
                        const newQuantity = parseInt(this.value);
                        if (selectedBackupProducts[index]) {
                            selectedBackupProducts[index].quantity = newQuantity;
                            // Cập nhật serial inputs
                            updateSerialInputsCreate(newQuantity, 'backup', selectedBackupProducts[
                                index].id, index);
                        }

                        // Cập nhật hidden quantity input
                        const hiddenQuantityInput = document.querySelector(
                            `.backup-quantity-hidden[data-index="${index}"]`);
                        if (hiddenQuantityInput) {
                            hiddenQuantityInput.value = newQuantity;
                        }

                        // Kiểm tra tồn kho ngay khi thay đổi
                showStockWarnings();
                    });
                });

                const removeBackupButtons = backupProductList.querySelectorAll('.remove-backup-product');
                removeBackupButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index);
                selectedBackupProducts.splice(index, 1);
                renderBackupProductTable();
                        // Kiểm tra lại tồn kho sau khi xóa sản phẩm
                showStockWarnings();
                    });
                });
            }

            // Show stock warnings
            function showStockWarnings() {
                // Implementation similar to create page
                console.log('Stock validation...');
            }

            // Handle dispatch detail changes
            if (dispatchDetailSelect) {
                dispatchDetailSelect.addEventListener('change', function() {
                    const selectedDetail = this.value;
                    
                    // Update table visibility
                    handleDispatchDetailDisplayOnChange(selectedDetail);
                    
                    // Update hidden input if exists (for approved dispatches)
                    const hiddenDispatchDetail = document.getElementById('hidden_dispatch_detail');
                    if (hiddenDispatchDetail) {
                        hiddenDispatchDetail.value = selectedDetail;
                    }
                    
                    // For pending status, dropdown remains visible but category logic changes
                });

                // Trigger initial load based on current value
                if (dispatchDetailSelect.value) {
                    dispatchDetailSelect.dispatchEvent(new Event('change'));
                }
            }

            // Handle table display when dispatch_detail changes
            function handleDispatchDetailDisplayOnChange(selectedDetail) {
                const contractTable = document.getElementById('selected-contract-products');
                const backupTable = document.getElementById('selected-backup-products');
                
                // Hide all tables first
                if (contractTable) contractTable.classList.add('hidden');
                if (backupTable) backupTable.classList.add('hidden');
                
                // Show tables based on selected detail
                if (selectedDetail === 'all') {
                    if (contractTable) contractTable.classList.remove('hidden');
                    if (backupTable) backupTable.classList.remove('hidden');
                } else if (selectedDetail === 'contract') {
                    if (contractTable) contractTable.classList.remove('hidden');
                } else if (selectedDetail === 'backup') {
                    if (backupTable) backupTable.classList.remove('hidden');
                }
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

                // Tạo serial selects mới theo quantity
                for (let i = 0; i < newQuantity; i++) {
                    const select = document.createElement('select');
                    select.className = `w-32 border rounded px-2 py-1 text-xs focus:outline-none focus:ring-1`;

                    // Xác định màu border theo category
                    if (category === 'contract') {
                        select.classList.add('border-blue-300', 'focus:ring-blue-500');
                        select.name = quantityInput.name.replace('[quantity]', `[serial_numbers][${i}]`);
                    } else if (category === 'backup') {
                        select.classList.add('border-orange-300', 'focus:ring-orange-500');
                        select.name = quantityInput.name.replace('[quantity]', `[serial_numbers][${i}]`);
                    } else {
                        select.classList.add('border-gray-300', 'focus:ring-blue-500');
                        select.name = quantityInput.name.replace('[quantity]', `[serial_numbers][${i}]`);
                    }

                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = `-- Chọn Serial ${i + 1} --`;
                    select.appendChild(defaultOption);

                    // Set selected value if exists
                    if (currentValues[i]) {
                        const option = document.createElement('option');
                        option.value = currentValues[i];
                        option.textContent = currentValues[i];
                        option.selected = true;
                        select.appendChild(option);
                    }

                    serialContainer.appendChild(select);

                    // Thêm margin bottom trừ item cuối
                    if (i < newQuantity - 1) {
                        select.classList.add('mb-1');
                    }
                }

                // Load available serials for the new selects
                loadAvailableSerials();
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
                        </ul>`;

                    // Thêm vào đầu form
                    form.insertBefore(warningDiv, form.firstChild);
                }
            }

            // Xử lý form submit
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Kiểm tra xem còn item nào không bị disable (existing items + newly added items)
                    const activeContractItems = form.querySelectorAll(
                        'input[name*="contract_items"][name*="[item_id]"]:not([disabled])');
                    const activeBackupItems = form.querySelectorAll(
                        'input[name*="backup_items"][name*="[item_id]"]:not([disabled])');
                    const activeGeneralItems = form.querySelectorAll(
                        'input[name*="general_items"][name*="[item_id]"]:not([disabled])');
                    
                    // Count newly added items from pending dispatch
                    let newlyAddedItems = 0;
                    @if ($dispatch->status === 'pending')
                        newlyAddedItems = selectedContractProducts.length + selectedBackupProducts.length;
                    @endif

                    const totalActiveItems = activeContractItems.length + activeBackupItems.length +
                        activeGeneralItems.length + newlyAddedItems;

                    // Kiểm tra validation dựa trên dispatch_detail (lấy giá trị hiện tại từ dropdown)
                    const dispatchDetailSelect = document.getElementById('dispatch_detail');
                    const dispatchDetail = dispatchDetailSelect ? dispatchDetailSelect.value : '{{ $dispatch->dispatch_detail }}';
                    console.log('Current dispatch_detail value:', dispatchDetail);
                    let hasRequiredProducts = false;
                    let errorMessage = '';

                    if (dispatchDetail === 'all') {
                        // Với "Tất cả", cần ít nhất một sản phẩm hợp đồng VÀ một thiết bị dự phòng
                        const contractItemsCount = activeContractItems.length;
                        const backupItemsCount = activeBackupItems.length;
                        @if ($dispatch->status === 'pending')
                            const newContractItemsCount = selectedContractProducts.length;
                            const newBackupItemsCount = selectedBackupProducts.length;
                            const totalContractItems = contractItemsCount + newContractItemsCount;
                            const totalBackupItems = backupItemsCount + newBackupItemsCount;
                        @else
                            const totalContractItems = contractItemsCount;
                            const totalBackupItems = backupItemsCount;
                        @endif
                        
                        if (totalContractItems === 0 && totalBackupItems === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Vui lòng chọn ít nhất một sản phẩm hợp đồng và một thiết bị dự phòng để xuất kho!';
                        } else if (totalContractItems === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Phiếu xuất "Tất cả" phải có ít nhất một sản phẩm hợp đồng!';
                        } else if (totalBackupItems === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Phiếu xuất "Tất cả" phải có ít nhất một thiết bị dự phòng!';
                        } else {
                            hasRequiredProducts = true;
                        }
                    } else if (dispatchDetail === 'contract') {
                        // Với "Xuất theo hợp đồng", cần ít nhất một sản phẩm hợp đồng và KHÔNG được có dự phòng
                        const contractItemsCount = activeContractItems.length;
                        const backupItemsCount = activeBackupItems.length;
                        @if ($dispatch->status === 'pending')
                            const newContractItemsCount = selectedContractProducts.length;
                            const newBackupItemsCount = selectedBackupProducts.length;
                            const totalContractItems = contractItemsCount + newContractItemsCount;
                            const totalBackupItems = backupItemsCount + newBackupItemsCount;
                        @else
                            const totalContractItems = contractItemsCount;
                            const totalBackupItems = backupItemsCount;
                        @endif
                        
                        if (totalContractItems === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Phiếu xuất theo hợp đồng phải có ít nhất một thành phẩm theo hợp đồng!';
                        } else if (totalBackupItems > 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Phiếu xuất theo hợp đồng không được chứa thiết bị dự phòng! Vui lòng chọn "Tất cả" nếu muốn xuất cả hai loại.';
                        } else {
                            hasRequiredProducts = true;
                        }
                    } else if (dispatchDetail === 'backup') {
                        // Với "Xuất thiết bị dự phòng", cần ít nhất một thiết bị dự phòng và KHÔNG được có hợp đồng
                        const contractItemsCount = activeContractItems.length;
                        const backupItemsCount = activeBackupItems.length;
                        @if ($dispatch->status === 'pending')
                            const newContractItemsCount = selectedContractProducts.length;
                            const newBackupItemsCount = selectedBackupProducts.length;
                            const totalContractItems = contractItemsCount + newContractItemsCount;
                            const totalBackupItems = backupItemsCount + newBackupItemsCount;
                        @else
                            const totalContractItems = contractItemsCount;
                            const totalBackupItems = backupItemsCount;
                        @endif
                        
                        if (totalBackupItems === 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Phiếu xuất thiết bị dự phòng phải có ít nhất một thiết bị dự phòng!';
                        } else if (totalContractItems > 0) {
                            hasRequiredProducts = false;
                            errorMessage = 'Phiếu xuất thiết bị dự phòng không được chứa sản phẩm hợp đồng! Vui lòng chọn "Tất cả" nếu muốn xuất cả hai loại.';
                        } else {
                            hasRequiredProducts = true;
                        }
                    }

                    if (!hasRequiredProducts) {
                        e.preventDefault();
                        alert(errorMessage);
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

                    // Kiểm tra serial numbers trùng lặp trước khi submit
                    const duplicateSerials = validateSerialNumbers();
                    if (duplicateSerials.length > 0) {
                        e.preventDefault();
                        alert('Có serial numbers bị trùng lặp:\n\n' + duplicateSerials.join(', ') +
                            '\n\nVui lòng chọn serial numbers khác nhau!');
                        showSerialDuplicateWarning(duplicateSerials);
                        return;
                    }

                    // Cho phiếu đã duyệt, validate serial numbers
                    @if ($dispatch->status === 'approved')
                        const emptySerials = [];
                        const serialSelects = document.querySelectorAll(
                            'select[name*="serial_numbers"]:not([disabled])');

                        serialSelects.forEach(select => {
                            if (select.value.trim() === '') {
                                const itemRow = select.closest('tr');
                                const itemCode = itemRow.querySelector('td:first-child').textContent
                                    .trim();
                                const serialIndex = select.getAttribute('name').match(/\[(\d+)\]/g)
                                    ?.pop()?.replace(/[\[\]]/g, '') || '';
                                emptySerials.push(
                                    `${itemCode} - Serial ${parseInt(serialIndex) + 1}`);
                            }
                        });
                        
                        if (emptySerials.length > 0) {
                            e.preventDefault();
                            if (confirm(
                                    `Các serial sau chưa được chọn:\n\n${emptySerials.join('\n')}\n\nBạn có muốn tiếp tục không?`
                                )) {
                                // User confirmed, allow submission
                                e.target.submit();
                            }
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
