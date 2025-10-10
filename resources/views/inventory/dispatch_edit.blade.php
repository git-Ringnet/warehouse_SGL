<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chỉnh sửa phiếu xuất kho - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/date-format.js') }}"></script>
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
                <input type="hidden" id="project_receiver_canonical" name="project_receiver" value="{{ $dispatch->project_receiver }}">

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
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày xuất</label>
                            <input type="text" id="dispatch_date" name="dispatch_date"
                                value="{{ $dispatch->dispatch_date->format('d/m/Y') }}" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 date-input {{ $dispatch->status !== 'pending' && $dispatch->status !== 'approved' ? 'bg-gray-100' : '' }}"
                                {{ $dispatch->status !== 'pending' && $dispatch->status !== 'approved' ? 'readonly' : '' }}>
                        </div>
                        <div>
                            <label for="dispatch_type"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Loại hình</label>
                            <select id="dispatch_type" name="dispatch_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $dispatch->status !== 'pending' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
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
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $dispatch->status !== 'pending' ? 'bg-gray-100 cursor-not-allowed' : '' }}"
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
                        <!-- Phần chọn dự án (hiển thị khi loại hình = project) -->
                        <div id="project_section">
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Dự án</label>
                            <div class="relative">
                                <input type="text" id="project_receiver_search" placeholder="Tìm kiếm dự án..." class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $dispatch->dispatch_type === 'project' && $dispatch->project ? $dispatch->project->project_code . ' - ' . $dispatch->project->project_name . ' (' . ($dispatch->project->customer->name ?? 'N/A') . ')' : '' }}" {{ $dispatch->status !== 'pending' ? 'readonly' : '' }}>
                                <div id="project_receiver_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @if (isset($projects))
                                        @foreach ($projects as $project)
                                            <div class="project-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" data-text="{{ $project->project_code }} - {{ $project->project_name }} ({{ $project->customer->name ?? 'N/A' }})" data-project-id="{{ $project->id }}" data-warranty-period="{{ $project->warranty_period }}">
                                                {{ $project->project_code }} - {{ $project->project_name }} ({{ $project->customer->name ?? 'N/A' }})
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <input type="hidden" id="project_receiver" name="project_receiver" value="{{ $dispatch->project_receiver }}">
                                <input type="hidden" id="project_id" name="project_id" value="{{ $dispatch->project_id }}">
                            </div>
                        </div>

                        <!-- Phần cho thuê (hiển thị khi loại hình = rental) -->
                        <div id="rental_section" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Hợp đồng cho thuê</label>
                            <div class="relative">
                                <input type="text" id="rental_receiver_search" placeholder="Tìm kiếm hợp đồng cho thuê..." class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $dispatch->dispatch_type==='rental' && $dispatch->rental ? $dispatch->rental->rental_code . ' - ' . $dispatch->rental->rental_name . ' (' . ($dispatch->rental->customer->name ?? 'N/A') . ')' : '' }}" {{ $dispatch->status !== 'pending' ? 'readonly' : '' }}>
                                <div id="rental_receiver_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @if (isset($rentals))
                                        @foreach ($rentals as $rental)
                                            <div class="rental-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" data-text="{{ $rental->rental_code }} - {{ $rental->rental_name }} ({{ $rental->customer->name ?? 'N/A' }})" data-rental-id="{{ $rental->id }}">
                                                {{ $rental->rental_code }} - {{ $rental->rental_name }} ({{ $rental->customer->name ?? 'N/A' }})
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <input type="hidden" id="rental_receiver" name="rental_receiver" value="{{ $dispatch->dispatch_type==='rental' ? $dispatch->project_receiver : '' }}">
                                <input type="hidden" name="project_receiver" id="rental_project_receiver" value="{{ $dispatch->dispatch_type==='rental' ? $dispatch->project_receiver : '' }}">
                            </div>
                        </div>

                        <!-- Phần bảo hành (hiển thị khi loại hình = warranty) -->
                        <div id="warranty_section" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1 required">Dự án / Hợp đồng cho thuê</label>
                            <div class="relative">
                                <input type="text" id="warranty_receiver_search" placeholder="Tìm kiếm dự án / hợp đồng cho thuê..." class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $dispatch->dispatch_type==='warranty' && $dispatch->project ? $dispatch->project->project_code . ' - ' . $dispatch->project->project_name . ' (' . ($dispatch->project->customer->name ?? 'N/A') . ')' : '' }}" {{ $dispatch->status !== 'pending' ? 'readonly' : '' }}>
                                <div id="warranty_receiver_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @if (isset($projects))
                                        @foreach ($projects as $project)
                                            <div class="warranty-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" data-text="{{ $project->project_code }} - {{ $project->project_name }} ({{ $project->customer->name ?? 'N/A' }})" data-type="project" data-project-id="{{ $project->id }}">{{ $project->project_code }} - {{ $project->project_name }} ({{ $project->customer->name ?? 'N/A' }})</div>
                                        @endforeach
                                    @endif
                                    @if (isset($rentals))
                                        @foreach ($rentals as $rental)
                                            <div class="warranty-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" data-text="{{ $rental->rental_code }} - {{ $rental->rental_name }} ({{ $rental->customer->name ?? 'N/A' }})" data-type="rental" data-rental-id="{{ $rental->id }}">{{ $rental->rental_code }} - {{ $rental->rental_name }} ({{ $rental->customer->name ?? 'N/A' }})</div>
                                        @endforeach
                                    @endif
                                </div>
                                <input type="hidden" id="warranty_receiver" name="project_receiver" value="{{ $dispatch->project_receiver }}">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Người đại diện công ty</label>
                            <div class="relative">
                                <input type="text" id="company_representative_search" autocomplete="off" placeholder="Tìm kiếm người đại diện..." class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ optional($employees->firstWhere('id', $dispatch->company_representative_id))->name }}">
                                <div id="company_representative_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                @if (isset($employees))
                                    @foreach ($employees as $employee)
                                        <div class="employee-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" data-value="{{ $employee->id }}" data-text="{{ $employee->name }}">{{ $employee->name }}</div>
                                    @endforeach
                                @endif
                                </div>
                                <input type="hidden" id="company_representative" name="company_representative_id" value="{{ $dispatch->company_representative_id }}">
                            </div>
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
                                            Mã</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            Tên</th>
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
                                            Mã</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            Tên</th>
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
                                            Mã</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên</th>
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
                            Danh sách thiết bị theo hợp đồng
                        </h2>

                        @if ($dispatch->status === 'pending')
                            <!-- Chọn thành phẩm hợp đồng (searchable) -->
                            <div class="mb-4">
                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <div class="relative">
                                            <input type="text" autocomplete="off" id="contract_product_search" placeholder="Tìm kiếm thiết bị theo hợp đồng..." class="w-full h-10 border border-blue-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50">
                                            <div id="contract_product_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-blue-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                                            <input type="hidden" id="contract_product_select">
                                        </div>
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
                                            MÃ</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                            TÊN</th>
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

                        <!-- Nút cập nhật mã thiết bị hợp đồng -->
                        <div class="mt-4 flex justify-end">
                                <button type="button" id="update_contract_device_codes_btn"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i> Cập nhật mã thiết bị
                                </button>
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
                            <!-- Chọn thiết bị dự phòng (searchable) -->
                            <div class="mb-4">
                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <div class="relative">
                                            <input type="text" autocomplete="off" id="backup_product_search" placeholder="Tìm kiếm thiết bị dự phòng..." class="w-full h-10 border border-orange-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500 bg-orange-50">
                                            <div id="backup_product_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-orange-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"></div>
                                            <input type="hidden" id="backup_product_select">
                                        </div>
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
                                            MÃ</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">
                                            TÊN</th>
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

                        <!-- Nút cập nhật mã thiết bị dự phòng -->
                            <div class="mt-4 flex justify-end">
                                <button type="button" id="update_backup_device_codes_btn"
                                    class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i> Cập nhật mã thiết bị
                                </button>
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

    <!-- Modal cập nhật mã thiết bị -->
    <div id="device-code-modal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-7xl h-[35rem] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Cập nhật mã thiết bị</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" id="close-device-code-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Nhập thông tin mã thiết bị cho sản phẩm. Điền thông tin vào bảng
                    bên dưới:</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        <div class="text-sm text-blue-700">
                            <strong>Lưu ý:</strong> Serial chính trong modal này là serial mới được đổi tên từ bảng device_codes và sẽ được hiển thị ở giao diện chính. 
                            Nếu không có Serial chính trong database, giao diện chính sẽ hiển thị serial hiện tại đã chọn.
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mã - Tên thiết bị
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri chính
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri vật tư
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Seri sim
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mã truy cập
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    ID IoT
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Mac 4G
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-center border border-gray-200">
                                    Chú thích
                                </th>
                            </tr>
                        </thead>
                        <tbody id="device-code-tbody">
                            <!-- Dữ liệu sẽ được tạo động từ JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-between">
                    <div class="flex space-x-2">
                        <button type="button" id="download-template"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-download mr-2"></i> Tải mẫu Excel
                        </button>
                        <button type="button" id="import-device-codes"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-file-import mr-2"></i> Import Excel
                        </button>
                    </div>
                    <div>
                        <button type="button" id="cancel-device-codes"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors mr-2">
                            Hủy
                        </button>
                        <button type="button" id="save-device-codes"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thông tin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function activateProjectReceiver(source) {
                const projectHidden = document.getElementById('project_receiver');
                const rentalHidden = document.getElementById('rental_project_receiver');
                const warrantyHidden = document.getElementById('warranty_receiver');
                // Disable all first
                if (projectHidden) projectHidden.disabled = true;
                if (rentalHidden) rentalHidden.disabled = true;
                if (warrantyHidden) warrantyHidden.disabled = true;
                // Enable the one we want submitted
                if (source === 'project' && projectHidden) projectHidden.disabled = false;
                if (source === 'rental' && rentalHidden) rentalHidden.disabled = false;
                if (source === 'warranty' && warrantyHidden) warrantyHidden.disabled = false;
            }
            // Variables for edit page
            let availableItems = [];

            // Xử lý tải mẫu Excel từ modal
            document.getElementById('download-template').addEventListener('click', function() {
                const tbody = document.getElementById('device-code-tbody');
                const rows = tbody.querySelectorAll('tr');
                let templateData = [];
                let currentProduct = null;

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td, th, input, select');
                    if (cells.length) {
                        // Lấy tên thiết bị từ cột đầu tiên
                        const productName = row.querySelector('td:first-child, th:first-child')?.textContent?.trim();
                        if (productName && !productName.includes('RDO-')) {
                            currentProduct = {
                                name: productName,
                                serials: [],
                                materials: [] // Changed to match backend expectation
                            };
                            templateData.push(currentProduct);
                        }
                        
                        if (currentProduct) {
                            // Lấy các serial và thông tin khác
                            const serialMain = row.querySelector('input[name*="serial_main"]')?.value || '';
                            const materialName = row.querySelector('td:nth-child(3)')?.textContent?.trim();

                            if (serialMain) {
                                currentProduct.serials = [serialMain]; // Use array with single value as expected by backend
                            }
                            
                            if (materialName && materialName.includes('RDO-')) {
                                // Extract code and name from material format "RDO-XXX - Name"
                                const [code, ...nameParts] = materialName.split(' - ');
                                const name = nameParts.join(' - ');
                                
                                currentProduct.materials.push({
                                    code: code,
                                    name: name
                                });
                            }
                        }
                    }
                });

                // Gửi dữ liệu đến server để tạo Excel
                fetch('{{ route("device-codes.template") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        dispatch_id: '{{ $dispatch->id }}',
                        type: '{{ $dispatch->dispatch_type }}',
                        modal_data: templateData
                    })
                })
                .then(async response => {
                    if (!response.ok) {
                        if (response.headers.get('content-type')?.includes('application/json')) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || 'Có lỗi xảy ra khi tải file');
                        }
                        throw new Error('Có lỗi xảy ra khi tải file');
                    }

                    // If it's a JSON response, it means there's an error
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Có lỗi xảy ra');
                    }

                    // Handle Excel file
                    const blob = await response.blob();
                    if (!(blob instanceof Blob) || blob.size === 0) {
                        throw new Error('File không hợp lệ');
                    }

                    // Create a download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'template_device_codes.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    
                    // Cleanup
                    setTimeout(() => {
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                    }, 0);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || 'Có lỗi khi tải file template');
                });
            });
            let selectedContractProducts = [];
            let selectedBackupProducts = [];
            const currentProjectId = @json($dispatch->project_id ?? null);

            // Always load existing items, only load available items if pending
            loadExistingItemsOnly();

            // Show appropriate tables based on dispatch_detail
            handleDispatchDetailDisplay();

            // Force render after a delay to ensure everything is loaded
            setTimeout(async function() {
                renderContractProductTable();
                renderBackupProductTable();
                handleDispatchDetailDisplay();

                await loadAvailableSerials();

                // Load device codes from database để cập nhật serial_main AFTER serials are filtered
                // Thêm delay để đảm bảo dropdown đã render hoàn toàn
                setTimeout(async () => {
                await loadDeviceCodesOnPageLoad();
                    // Gọi lại updateMainInterfaceSerials sau khi load device codes để đảm bảo serial đổi tên được set
                    setTimeout(() => {
                        if (typeof updateMainInterfaceSerials === 'function') {
                            updateMainInterfaceSerials('contract');
                            updateMainInterfaceSerials('backup');
                        }
                    }, 200);
                }, 500);

                // Resolve missing assembly/product_unit for products when not stored on dispatch_items
                await resolveMissingAssemblyForAll();

                // Initial validation and option availability update
                setTimeout(function() {
                    updateSerialOptionsAvailability();
                    // Làm giàu lại số tồn hiển thị trong dropdown sau lần render đầu
                    if (typeof refreshWarehouseOptionStocks === 'function') {
                        refreshWarehouseOptionStocks();
                    }
                }, 500);
            }, 100);

            @if ($dispatch->status === 'pending')
                loadAvailableItems();
            @endif

            // Generic bind function for search dropdowns
            function bindSearchDropdown({ searchEl, dropdownEl, optionClass, onSelect }) {
                if (!searchEl || !dropdownEl) return;
                searchEl.addEventListener('focus', () => {
                    if (dropdownEl.children.length > 0) dropdownEl.classList.remove('hidden');
                });
                searchEl.addEventListener('input', function() {
                    const q = this.value.toLowerCase();
                    dropdownEl.querySelectorAll('.' + optionClass).forEach(opt => {
                        const base = opt.dataset.text || opt.textContent;
                        const show = base.toLowerCase().includes(q);
                        opt.style.display = show ? 'block' : 'none';
                        if (show) opt.innerHTML = base.replace(new RegExp(q, 'gi'), m => `<mark class="bg-yellow-200">${m}</mark>`);
                    });
                    if (dropdownEl.children.length > 0) dropdownEl.classList.remove('hidden');
                });
                dropdownEl.addEventListener('mousedown', function(e){
                    e.stopPropagation();
                    pick(e);
                });
                function pick(e) {
                    const el = e.target.closest('.' + optionClass);
                    if (!el) return;
                    el.innerHTML = el.dataset.text || el.textContent;
                    dropdownEl.classList.add('hidden');
                    if (onSelect) onSelect(el);
                }
                dropdownEl.addEventListener('click', pick);
                searchEl.addEventListener('blur', () => setTimeout(() => dropdownEl.classList.add('hidden'), 150));
                document.addEventListener('mousedown', function(e) {
                    if (!dropdownEl.contains(e.target) && e.target !== searchEl) dropdownEl.classList.add('hidden');
                });
            }

            // Resolve assembly_id/product_unit for products lacking these fields or when serial changes
            async function resolveMissingAssemblyForAll() {
                try {
                    const allProducts = [...selectedContractProducts, ...selectedBackupProducts];
                    // Resolve cho tất cả product, không chỉ những cái chưa có assembly_id
                    const targets = allProducts.filter(p => p.type === 'product');
                    if (targets.length === 0) return;

                    for (const p of targets) {
                        // Lấy serial từ DOM thay vì từ object để đảm bảo lấy được serial đã chọn
                        let serial = '';
                        const serialSelects = document.querySelectorAll(`select[data-item-id="${p.id}"][data-item-type="product"]`);
                        if (serialSelects.length > 0) {
                            // Lấy serial đầu tiên đã được chọn
                            for (const select of serialSelects) {
                                if (select.value && select.value.trim() !== '') {
                                    serial = select.value.trim();
                                    break;
                                }
                            }
                        }

                        const params = new URLSearchParams({
                            product_id: String(p.id),
                            serial: serial || ''
                        });
                        if (currentProjectId) params.set('project_id', String(currentProjectId));

                        const res = await fetch(`/api/dispatch/resolve-assembly?${params.toString()}`);
                        if (!res.ok) continue;
                        const data = await res.json();
                        
                        console.log(`API response for product ${p.id} with serial "${serial}":`, data);
                        
                        if (data && data.success) {
                            if (data.assembly_id) p.assembly_id = data.assembly_id;
                            if (typeof data.product_unit !== 'undefined') p.product_unit = data.product_unit;
                            
                            console.log(`Resolved assembly for product ${p.id} with serial "${serial}":`, {
                                assembly_id: data.assembly_id,
                                product_unit: data.product_unit,
                                updated_object: p
                            });
                        }
                    }

                    // Re-render to reflect newly resolved fields if needed
                    renderContractProductTable();
                    renderBackupProductTable();
                } catch (e) {
                    console.warn('resolveMissingAssemblyForAll error', e);
                }
            }

            // Build options for contract/backup dropdowns from availableItems
            function buildProductOptionsHTML(filterCategory) {
                const items = Array.isArray(availableItems) ? availableItems : [];
                const arr = items
                    .filter(it => !filterCategory || it.category === filterCategory || !it.category)
                    .map(it => ({
                        id: it.id,
                        text: `${it.code || ''} - ${it.name || ''}`.trim(),
                        type: it.type
                    }));
                arr.sort((a,b)=> a.text.localeCompare(b.text,'vi',{sensitivity:'base'}));
                return arr.map(it => `<div class="product-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" data-value="${it.id}" data-type="${it.type}" data-text="${it.text}">${it.text}</div>`).join('');
            }

            function initContractProductSearch() {
                const search = document.getElementById('contract_product_search');
                const dropdown = document.getElementById('contract_product_dropdown');
                const hidden = document.getElementById('contract_product_select');
                if (!search || !dropdown || !hidden) return;
                dropdown.innerHTML = buildProductOptionsHTML('contract');
                bindSearchDropdown({
                    searchEl: search,
                    dropdownEl: dropdown,
                    optionClass: 'product-option',
                    onSelect: (opt) => {
                        search.value = opt.dataset.text;
                        hidden.value = opt.dataset.value;
                    }
                });
            }

            function initBackupProductSearch() {
                const search = document.getElementById('backup_product_search');
                const dropdown = document.getElementById('backup_product_dropdown');
                const hidden = document.getElementById('backup_product_select');
                if (!search || !dropdown || !hidden) return;
                dropdown.innerHTML = buildProductOptionsHTML('backup');
                bindSearchDropdown({
                    searchEl: search,
                    dropdownEl: dropdown,
                    optionClass: 'product-option',
                    onSelect: (opt) => {
                        search.value = opt.dataset.text;
                        hidden.value = opt.dataset.value;
                    }
                });
            }

            // Initialize project search
            (function initProjectSearch(){
                const search = document.getElementById('project_receiver_search');
                const dropdown = document.getElementById('project_receiver_dropdown');
                const hiddenText = document.getElementById('project_receiver');
                const hiddenId = document.getElementById('project_id');
                
                
                // Store initial value
                const initialValue = search.value;
                
                bindSearchDropdown({
                    searchEl: search,
                    dropdownEl: dropdown,
                    optionClass: 'project-option',
                    onSelect: (opt) => {
                        search.value = opt.dataset.text;
                        if (hiddenText) hiddenText.value = opt.dataset.text;
                        if (hiddenId && opt.dataset.projectId) hiddenId.value = opt.dataset.projectId;
                        const wp = opt.dataset.warrantyPeriod || '';
                        const wpEl = document.getElementById('warranty_period');
                        if (wpEl) wpEl.value = wp;
                        activateProjectReceiver('project');
                    }
                });
                
                // Preserve initial value if it exists
                if (initialValue && initialValue.trim()) {
                    if (hiddenText) hiddenText.value = initialValue;
                    
                    // Restore search value if it gets cleared
                    setTimeout(() => {
                        if (search.value !== initialValue) {
                            search.value = initialValue;
                        }
                    }, 100);
                }
            })();

            // Initialize employee search (company representative)
            (function initRepSearch(){
                const search = document.getElementById('company_representative_search');
                const dropdown = document.getElementById('company_representative_dropdown');
                const hidden = document.getElementById('company_representative');
                bindSearchDropdown({
                    searchEl: search,
                    dropdownEl: dropdown,
                    optionClass: 'employee-option',
                    onSelect: (opt) => {
                        search.value = opt.dataset.text;
                        if (hidden) hidden.value = opt.dataset.value;
                    }
                });
            })();

            // Initialize warranty combined search (projects + rentals filled later)
            (function initWarrantySearch(){
                const search = document.getElementById('warranty_receiver_search');
                const dropdown = document.getElementById('warranty_receiver_dropdown');
                const hidden = document.getElementById('warranty_receiver');
                bindSearchDropdown({
                    searchEl: search,
                    dropdownEl: dropdown,
                    optionClass: 'warranty-option',
                    onSelect: (opt) => {
                        search.value = opt.dataset.text;
                        if (hidden) hidden.value = opt.dataset.text;
                        const pid = opt.dataset.projectId || opt.dataset.rentalId || '';
                        const projectIdEl = document.getElementById('project_id');
                        if (projectIdEl && pid) projectIdEl.value = pid;
                        activateProjectReceiver('warranty');
                    }
                });
            })();

            // Initialize rental search (options populated via API)
            (function initRentalSearch(){
                const search = document.getElementById('rental_receiver_search');
                const dropdown = document.getElementById('rental_receiver_dropdown');
                const hidden = document.getElementById('rental_receiver');
                bindSearchDropdown({
                    searchEl: search,
                    dropdownEl: dropdown,
                    optionClass: 'rental-option',
                    onSelect: (opt) => {
                        search.value = opt.dataset.text;
                        if (hidden) hidden.value = opt.dataset.text;
                        const projectIdEl = document.getElementById('project_id');
                        if (projectIdEl && opt.dataset.rentalId) projectIdEl.value = opt.dataset.rentalId;
                        const rentalProjectReceiver = document.getElementById('rental_project_receiver');
                        if (rentalProjectReceiver) rentalProjectReceiver.value = opt.dataset.text;
                        activateProjectReceiver('rental');
                        // Ensure only the active hidden is named project_receiver
                        const pr = document.getElementById('project_receiver');
                        const rr = document.getElementById('rental_project_receiver');
                        const wr = document.getElementById('warranty_receiver');
                        if (pr) pr.name = 'project_receiver_disabled';
                        if (wr) wr.name = 'project_receiver_disabled';
                        if (rr) rr.name = 'project_receiver';
                    }
                });
            })();

            // Populate rentals for rental and warranty dropdowns (searchable)
            async function populateRentalSearchOptions() {
                try {
                    const response = await fetch('/api/dispatch/rentals');
                    const data = await response.json();
                    if (data.success) {
                        const rentalDropdown = document.getElementById('rental_receiver_dropdown');
                        const warrantyDropdown = document.getElementById('warranty_receiver_dropdown');
                        if (rentalDropdown) {
                            rentalDropdown.innerHTML = (data.rentals || []).map(r => `<div class="rental-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" data-text="${r.display_name}" data-rental-id="${r.id}" data-rental-code="${r.rental_code}" data-customer-name="${r.customer_name}">${r.display_name}</div>`).join('');
                        }
                        if (warrantyDropdown) {
                            const existing = warrantyDropdown.innerHTML; // keep project options
                            const rentalHtml = (data.rentals || []).map(r => `<div class=\"warranty-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0\" data-text=\"${r.display_name}\" data-type=\"rental\" data-rental-id=\"${r.id}\">${r.display_name}</div>`).join('');
                            warrantyDropdown.innerHTML = existing + rentalHtml;
                        }
                    }
                } catch (e) {
                    console.error('Error loading rentals for search:', e);
                }
            }

            // Kick off rentals population
            populateRentalSearchOptions();

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
                        // Initialize searchable pickers for contract/backup
                        initContractProductSearch();
                        initBackupProductSearch();
                    }
                } catch (error) {
                    console.error('Error loading available items:', error);
                }
            }

            // Trước khi submit: đảm bảo field project_receiver đúng theo loại hình
            const formEl = document.querySelector('form');
            if (formEl) {
                formEl.addEventListener('submit', function(e) {
                    const type = document.getElementById('dispatch_type')?.value;
                    const pr = document.getElementById('project_receiver');
                    const prSearch = document.getElementById('project_receiver_search');
                    const rr = document.getElementById('rental_project_receiver');
                    const rrSearch = document.getElementById('rental_receiver_search');
                    const wr = document.getElementById('warranty_receiver');
                    const wrSearch = document.getElementById('warranty_receiver_search');
                    const canonical = document.getElementById('project_receiver_canonical');

                    // Validation bắt buộc
                    let hasError = false;
                    let errorMessage = '';

                    if (type === 'project') {
                        if (!prSearch?.value?.trim()) {
                            hasError = true;
                            errorMessage = 'Vui lòng chọn Dự án';
                        }
                    } else if (type === 'rental') {
                        if (!rrSearch?.value?.trim()) {
                            hasError = true;
                            errorMessage = 'Vui lòng chọn Hợp đồng cho thuê';
                        }
                    } else if (type === 'warranty') {
                        if (!wrSearch?.value?.trim()) {
                            hasError = true;
                            errorMessage = 'Vui lòng chọn Dự án / Hợp đồng cho thuê';
                        }
                    }

                    if (hasError) {
                        e.preventDefault();
                        alert(errorMessage);
                        return false;
                    }

                    // Reset names
                    if (pr) { pr.name = 'project_receiver_disabled'; pr.disabled = true; }
                    if (rr) { rr.name = 'project_receiver_disabled'; rr.disabled = true; }
                    if (wr) { wr.name = 'project_receiver_disabled'; wr.disabled = true; }

                    if (type === 'project') {
                        if (pr) {
                            pr.name = 'project_receiver';
                            pr.disabled = false;
                            if (prSearch && !pr.value) pr.value = prSearch.value || '';
                        }
                        if (canonical) canonical.value = pr?.value || prSearch?.value || '';
                    } else if (type === 'rental') {
                        if (rr) {
                            rr.name = 'project_receiver';
                            rr.disabled = false;
                            if (rrSearch && !rr.value) rr.value = rrSearch.value || '';
                        }
                        if (canonical) canonical.value = rr?.value || rrSearch?.value || '';
                    } else if (type === 'warranty') {
                        if (wr) {
                            wr.name = 'project_receiver';
                            wr.disabled = false;
                            if (wrSearch && !wr.value) wr.value = wrSearch.value || '';
                        }
                        if (canonical) canonical.value = wr?.value || wrSearch?.value || '';
                    }
                });
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
                            selected_warehouse_id: @json($item->warehouse_id),
                            current_stock: 0, // Will be updated from API call
                            category: '{{ $item->category }}',
                            serial_numbers: @json($item->serial_numbers ?? []),
                            assembly_id: @json($item->assembly_id),
                            product_unit: @json($item->product_unit),
                            warehouses: [{
                                warehouse_id: @json($item->warehouse_id),
                                warehouse_name: '{{ $item->warehouse->name ?? 'N/A' }}',
                                quantity: 0 // Will be updated from API call
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

                // Load stock information for existing items
                loadStockInfoForExistingItems();

                // Render selected product tables với items hiện tại
                renderContractProductTable();
                renderBackupProductTable();
                
                // Load device codes after rendering to override serials
                setTimeout(() => {
                    loadDeviceCodesOnPageLoad();
                }, 200);
            }

            // Load stock information for existing items
            async function loadStockInfoForExistingItems() {
                try {
                    const response = await fetch('/api/dispatch/items/all');
                    const data = await response.json();

                    if (data.success) {
                        const availableItems = data.items;
                        
                        // Update stock information for existing items
                        selectedContractProducts.forEach(item => {
                            const foundItem = availableItems.find(availableItem => 
                                availableItem.id == item.id && availableItem.type == item.type
                            );
                            
                            if (foundItem) {
                                item.warehouses = foundItem.warehouses;
                                const warehouse = foundItem.warehouses.find(w => w.warehouse_id == item.selected_warehouse_id);
                                if (warehouse) {
                                    // Chỉ cập nhật nếu tồn kho hiện tại là 0 hoặc chưa được set
                                    if (!item.current_stock || item.current_stock === 0) {
                                        item.current_stock = warehouse.quantity;
                                    }
                                }
                            }
                        });

                        selectedBackupProducts.forEach(item => {
                            const foundItem = availableItems.find(availableItem => 
                                availableItem.id == item.id && availableItem.type == item.type
                            );
                            
                            if (foundItem) {
                                item.warehouses = foundItem.warehouses;
                                const warehouse = foundItem.warehouses.find(w => w.warehouse_id == item.selected_warehouse_id);
                                if (warehouse) {
                                    // Chỉ cập nhật nếu tồn kho hiện tại là 0 hoặc chưa được set
                                    if (!item.current_stock || item.current_stock === 0) {
                                        item.current_stock = warehouse.quantity;
                                    }
                                }
                            }
                        });

                        // Re-render tables with updated stock information
                        renderContractProductTable();
                        renderBackupProductTable();
                        // Không cần đồng bộ tồn kho vì đã được cập nhật trong loadExistingItems
                        // Chỉ đồng bộ nếu cần thiết
                    }
                } catch (error) {
                    console.error('Error loading stock info for existing items:', error);
                }
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
                            selected_warehouse_id: @json($item->warehouse_id),
                            current_stock: 0, // Will be updated from API
                            category: '{{ $item->category }}',
                            serial_numbers: @json($item->serial_numbers ?? []),
                            serial_components: [], // Will be populated from assembly
                            warehouses: [], // Will be populated from availableItems
                            assembly_id: @json($item->assembly_id),
                            product_unit: @json($item->product_unit),
                            existing_item_id: {{ $item->id }}, // Track original dispatch item ID
                            is_existing: true // Mark as existing item
                        };

                        // Find the corresponding item in availableItems to get warehouse info
                        const foundItem = availableItems.find(item =>
                            item.id == {{ $item->item_id }} && item.type == '{{ $item->item_type }}'
                        );

                        if (foundItem) {
                            existingItem.warehouses = foundItem.warehouses;
                            const warehouse = foundItem.warehouses.find(w => w.warehouse_id == existingItem.selected_warehouse_id);
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

                // Load serial components from assembly for products
                loadSerialComponentsFromAssembly();

                // Render selected product tables với items hiện tại
                renderContractProductTable();
                renderBackupProductTable();
            }

            // Hàm load serial components từ assembly
            async function loadSerialComponentsFromAssembly() {
                try {
                    // Lấy tất cả product IDs từ selectedContractProducts và selectedBackupProducts
                    const allProducts = [...selectedContractProducts, ...selectedBackupProducts];
                    const productIds = allProducts
                        .filter(item => item.type === 'product')
                        .map(item => item.id);

                    if (productIds.length === 0) return;

                    // Gọi API để lấy serial components từ assembly
                    const response = await fetch('/api/assembly/serial-components', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            product_ids: productIds
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            // Cập nhật serial_components cho các products
                            allProducts.forEach(product => {
                                if (product.type === 'product' && data.serial_components[product.id]) {
                                    product.serial_components = data.serial_components[product.id];
                                }
                            });
                        }
                    }
                } catch (error) {
                    console.error('Error loading serial components from assembly:', error);
                }
            }

            // Lấy thông tin chi tiết về stock (có serial và không có serial)
            async function getDetailedStockInfo(itemType, itemId, warehouseId, fallbackStock = 0) {
                try {
                    // Kiểm tra trong availableItems trước
                    if (typeof availableItems !== 'undefined' && availableItems.length > 0) {
                        const foundItem = availableItems.find(item => 
                            item.id == itemId && item.type == itemType
                        );
                        if (foundItem) {
                            const warehouse = foundItem.warehouses.find(w => w.warehouse_id == warehouseId);
                            if (warehouse) {
                                // Lấy thông tin serial từ API
                                const response = await fetch(`/api/dispatch/item-serials?item_type=${itemType}&item_id=${itemId}&warehouse_id=${warehouseId}&current_dispatch_id={{ $dispatch->id }}`);
                                const data = await response.json();
                                
                                if (data.success) {
                                    const serialStock = data.total_serials;
                                    const nonSerialStock = Math.max(0, warehouse.quantity - serialStock);
                                    
                                    return {
                                        hasSerial: serialStock > 0,
                                        serialStock: serialStock,
                                        nonSerialStock: nonSerialStock,
                                        totalStock: warehouse.quantity
                                    };
                                }
                            }
                        }
                    }
                    
                    // Fallback: chỉ trả về thông tin cơ bản
                    return {
                        hasSerial: false,
                        serialStock: 0,
                        nonSerialStock: fallbackStock,
                        totalStock: fallbackStock
                    };
                } catch (error) {
                    console.warn('Error getting detailed stock info:', error);
                    return {
                        hasSerial: false,
                        serialStock: 0,
                        nonSerialStock: fallbackStock,
                        totalStock: fallbackStock
                    };
                }
            }

            // Đồng bộ ô "TỒN KHO" theo option kho đang chọn cho cả contract và backup
            function syncStockCellsWithSelectedWarehouses() {
                try {
                    // Contract rows
                    document.querySelectorAll('.contract-warehouse-select').forEach(select => {
                        const index = parseInt(select.dataset.index);
                        const selectedOption = select.options[select.selectedIndex];
                        let qty = parseInt(selectedOption?.dataset?.quantity || '0') || 0;

                        // Nếu không có data-quantity từ option, lấy từ selectedContractProducts
                        if (qty === 0 && Array.isArray(selectedContractProducts) && selectedContractProducts[index]) {
                            qty = selectedContractProducts[index].current_stock || 0;
                        }

                        const stockCell = document.getElementById(`contract-stock-${index}`);
                        if (stockCell) {
                            stockCell.textContent = qty;
                        }

                        const quantityInput = document.getElementById(`contract-quantity-${index}`);
                        if (quantityInput) quantityInput.max = qty;

                        if (Array.isArray(selectedContractProducts) && selectedContractProducts[index]) {
                            selectedContractProducts[index].current_stock = qty;
                        }
                    });

                    // Backup rows
                    document.querySelectorAll('.backup-warehouse-select').forEach(select => {
                        const index = parseInt(select.dataset.index);
                        const selectedOption = select.options[select.selectedIndex];
                        let qty = parseInt(selectedOption?.dataset?.quantity || '0') || 0;

                        // Nếu không có data-quantity từ option, lấy từ selectedBackupProducts
                        if (qty === 0 && Array.isArray(selectedBackupProducts) && selectedBackupProducts[index]) {
                            qty = selectedBackupProducts[index].current_stock || 0;
                        }

                        const stockCell = document.getElementById(`backup-stock-${index}`);
                        if (stockCell) stockCell.textContent = qty;

                        const quantityInput = document.getElementById(`backup-quantity-${index}`);
                        if (quantityInput) quantityInput.max = qty;

                        if (Array.isArray(selectedBackupProducts) && selectedBackupProducts[index]) {
                            selectedBackupProducts[index].current_stock = qty;
                        }
                    });
                } catch (e) {
                    console.warn('syncStockCellsWithSelectedWarehouses error:', e);
                }
            }

            // Làm giàu dữ liệu tồn kho hiển thị trong dropdown (không đổi lựa chọn hiện tại)
            async function refreshWarehouseOptionStocks() {
                // Contract rows
                const contractSelects = document.querySelectorAll('.contract-warehouse-select');
                for (const select of contractSelects) {
                    const index = parseInt(select.dataset.index);
                    for (const option of Array.from(select.options)) {
                        const warehouseId = parseInt(option.value);
                        try {
                            const details = await getDetailedStockInfo(
                                selectedContractProducts[index].type,
                                selectedContractProducts[index].id,
                                warehouseId,
                                parseInt(option.dataset.quantity) || 0
                            );
                            if (details && typeof details.totalStock === 'number') {
                                option.dataset.quantity = String(details.totalStock);
                                if (option.textContent && option.textContent.includes('(')) {
                                    option.textContent = option.textContent.replace(/\(Tồn: .*?\)/, `(Tồn: ${details.totalStock})`);
                                }
                            }
                        } catch (_) {}
                    }
                }

                // Backup rows
                const backupSelects = document.querySelectorAll('.backup-warehouse-select');
                for (const select of backupSelects) {
                    const index = parseInt(select.dataset.index);
                    for (const option of Array.from(select.options)) {
                        const warehouseId = parseInt(option.value);
                        try {
                            const details = await getDetailedStockInfo(
                                selectedBackupProducts[index].type,
                                selectedBackupProducts[index].id,
                                warehouseId,
                                parseInt(option.dataset.quantity) || 0
                            );
                            if (details && typeof details.totalStock === 'number') {
                                option.dataset.quantity = String(details.totalStock);
                                if (option.textContent && option.textContent.includes('(')) {
                                    option.textContent = option.textContent.replace(/\(Tồn: .*?\)/, `(Tồn: ${details.totalStock})`);
                                }
                            }
                        } catch (_) {}
                    }
                }
            }

            // Hàm load danh sách hợp đồng cho thuê
            async function loadRentals() {
                try {
                    const response = await fetch('/api/dispatch/rentals');
                    const data = await response.json();

                    if (data.success) {
                        // Cập nhật dropdown cho thuê (nếu cần)
                        const rentalDropdown = document.getElementById('rental_receiver_dropdown');
                        if (rentalDropdown && data.rentals) {
                            // Clear existing options
                            rentalDropdown.innerHTML = '';
                            
                            // Add rental options
                            data.rentals.forEach(rental => {
                                const option = document.createElement('div');
                                option.className = 'rental-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                                option.dataset.text = rental.display_name;
                                option.dataset.rentalId = rental.id;
                                option.textContent = rental.display_name;
                                rentalDropdown.appendChild(option);
                            });
                            
                            // Auto-fill current rental on edit if applicable
                            const rentalSearch = document.getElementById('rental_receiver_search');
                            const rentalHidden = document.getElementById('rental_receiver');
                            const rentalHiddenProjectReceiver = document.getElementById('rental_project_receiver');
                            const projectIdEl = document.getElementById('project_id');
                            const currentReceiver = '{{ $dispatch->dispatch_type === 'rental' ? addslashes($dispatch->project_receiver) : '' }}';
                            if (currentReceiver && rentalSearch && rentalHidden) {
                                rentalSearch.value = currentReceiver;
                                rentalHidden.value = currentReceiver;
                                if (rentalHiddenProjectReceiver) rentalHiddenProjectReceiver.value = currentReceiver;
                                // Find matching option to set project_id
                                const match = Array.from(rentalDropdown.querySelectorAll('.rental-option')).find(o => (o.dataset.text || '').trim() === currentReceiver.trim());
                                if (match && projectIdEl && match.dataset.rentalId) {
                                    projectIdEl.value = match.dataset.rentalId;
                                }
                            }
                        }
                        
                        // Cập nhật dropdown bảo hành
                        const warrantyDropdown = document.getElementById('warranty_receiver_dropdown');
                        if (warrantyDropdown && data.rentals) {
                            // Giữ lại project options hiện có
                            const existingProjectOptions = warrantyDropdown.querySelectorAll('.warranty-option[data-type="project"]');
                            warrantyDropdown.innerHTML = '';
                            
                            // Thêm lại project options
                            existingProjectOptions.forEach(option => {
                                warrantyDropdown.appendChild(option.cloneNode(true));
                            });
                            
                            // Thêm rental options
                            data.rentals.forEach(rental => {
                                const option = document.createElement('div');
                                option.className = 'warranty-option px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                                option.dataset.text = rental.display_name;
                                option.dataset.type = 'rental';
                                option.dataset.rentalId = rental.id;
                                option.textContent = rental.display_name;
                                warrantyDropdown.appendChild(option);
                            });
                        }
                    } else {
                        console.error('Error loading rentals:', data.message);
                    }
                } catch (error) {
                    console.error('Error loading rentals:', error);
                }
            }

            // Xử lý thay đổi loại hình xuất kho trong edit form
            const dispatchTypeSelect = document.getElementById('dispatch_type');
            const dispatchDetailSelect = document.getElementById('dispatch_detail');
            const projectReceiverSelect = document.getElementById('project_receiver');
            const warrantySection = document.getElementById('warranty_section');
            const warrantyReceiverInput = document.getElementById('warranty_receiver');

            if (dispatchTypeSelect) {
                dispatchTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;

                    const projectSection = document.getElementById('project_section');
                    const contractProductsSection = document.getElementById('selected-contract-products');
                    const backupProductsSection = document.getElementById('selected-backup-products');
                    const rentalSection = document.getElementById('rental_section');
                    const projectReceiverInput = document.getElementById('project_receiver');
                    const rentalReceiverInput = document.getElementById('rental_receiver');

                    // Reset all sections
                    projectSection.classList.add('hidden');
                    rentalSection.classList.add('hidden');
                    if (warrantySection) warrantySection.classList.add('hidden');
                    projectReceiverInput.removeAttribute('required');
                    rentalReceiverInput.removeAttribute('required');
                    if (warrantyReceiverInput) warrantyReceiverInput.removeAttribute('required');

                    if (selectedType === 'rental') {
                        if (contractProductsSection) contractProductsSection.classList.remove('hidden');
                        if (backupProductsSection) backupProductsSection.classList.add('hidden');
                        
                        // Disable other receiver selects
                        if (projectReceiverInput) projectReceiverInput.disabled = true;
                        if (warrantyReceiverInput) warrantyReceiverInput.disabled = true;
                        
                        // Hiển thị phần cho thuê, ẩn phần dự án
                        rentalSection.classList.remove('hidden');
                        projectSection.classList.add('hidden');
                        warrantySection.classList.add('hidden');
                        
                        // Clear project input/search when switching to rental
                        const projectText = document.getElementById('project_receiver_search');
                        const projectHidden = document.getElementById('project_receiver');
                        const projectIdInput = document.getElementById('project_id');
                        if (projectText) projectText.value = '';
                        if (projectHidden) projectHidden.value = '';
                        if (projectIdInput) projectIdInput.value = '';
                        
                        // Đảm bảo rental receiver bị disable nếu phiếu đã approved
                        if ('{{ $dispatch->status }}' !== 'pending') {
                            rentalReceiverInput.disabled = true;
                        }

                        // Load danh sách hợp đồng cho thuê
                        loadRentals();

                        // Reset dispatch detail về mặc định cho rental
                        if (dispatchDetailSelect) {
                            // Chỉ enable nếu phiếu đang pending
                            dispatchDetailSelect.disabled = '{{ $dispatch->status }}' !== 'pending';
                        }

                        // Xóa hidden input nếu có
                        let hiddenDispatchDetail = document.getElementById('hidden_dispatch_detail');
                        if (hiddenDispatchDetail) {
                            hiddenDispatchDetail.remove();
                        }
                    } else if (selectedType === 'project') {
                        if (contractProductsSection) contractProductsSection.classList.remove('hidden');
                        if (backupProductsSection) backupProductsSection.classList.add('hidden');
                        
                        // Enable project receiver and disable others
                        if (projectReceiverInput) {
                            projectReceiverInput.disabled = '{{ $dispatch->status }}' !== 'pending';
                            projectReceiverInput.setAttribute('required','required');
                        }
                        if (warrantyReceiverInput) warrantyReceiverInput.disabled = true;
                        
                        // Hiển thị phần dự án, ẩn phần cho thuê
                        projectSection.classList.remove('hidden');
                        rentalSection.classList.add('hidden');
                        
                        // Clear rental input/search when switching to project
                        const rentalHidden = document.getElementById('rental_project_receiver');
                        const rentalText = document.getElementById('rental_receiver_search');
                        const rentalHiddenVal = document.getElementById('rental_receiver');
                        if (rentalHidden) rentalHidden.value = '';
                        if (rentalText) rentalText.value = '';
                        if (rentalHiddenVal) rentalHiddenVal.value = '';
                        // Activate project receiver and canonical, and clear old rental text
                        const canonical = document.getElementById('project_receiver_canonical');
                        const pr = document.getElementById('project_receiver');
                        const prSearch = document.getElementById('project_receiver_search');
                        const projectIdInput = document.getElementById('project_id');
                        
                        if (pr) { pr.disabled = false; pr.name = 'project_receiver'; }
                        // Không xóa giá trị của prSearch nếu đang ở loại hình project
                        if (canonical) canonical.value = '';
                        // Giữ lại project_id hiện có nếu đã được set từ server hoặc người dùng đã chọn dự án

                        // Reset dispatch detail về mặc định cho project
                        if (dispatchDetailSelect) {
                            // Chỉ enable nếu phiếu đang pending
                            dispatchDetailSelect.disabled = '{{ $dispatch->status }}' !== 'pending';
                        }

                        // Xóa hidden input nếu có
                        let hiddenDispatchDetail = document.getElementById('hidden_dispatch_detail');
                        if (hiddenDispatchDetail) {
                            hiddenDispatchDetail.remove();
                        }
                    } else if (selectedType === 'warranty') {
                        if (contractProductsSection) contractProductsSection.classList.add('hidden');
                        if (backupProductsSection) backupProductsSection.classList.remove('hidden');
                        
                        // Disable other receiver selects
                        if (projectReceiverInput) projectReceiverInput.disabled = true;
                        if (warrantyReceiverInput) {
                            warrantyReceiverInput.disabled = '{{ $dispatch->status }}' !== 'pending';
                            warrantyReceiverInput.setAttribute('required','required');
                        }

                        // Xóa các sản phẩm hợp đồng đã chọn khi chuyển sang bảo hành
                        const contractTbody = document.getElementById('contract_product_list_body');
                        if (contractTbody) {
                            contractTbody.innerHTML = ''; // Xóa khỏi giao diện
                        }
                        selectedContractProducts.length = 0; // Xóa khỏi mảng dữ liệu
                        if (typeof updateSelectedProductsHiddenInput === 'function') {
                            updateSelectedProductsHiddenInput(); // Cập nhật input ẩn
                        }
                        
                        // Hiển thị phần bảo hành, ẩn phần khác
                        if (warrantySection) warrantySection.classList.remove('hidden');
                        projectSection.classList.add('hidden');
                        rentalSection.classList.add('hidden');
                        
                        // Xóa hidden input project_receiver của rental nếu có
                        const rentalHidden2 = document.getElementById('rental_project_receiver');
                        if (rentalHidden2) rentalHidden2.remove();

                            // Load rentals cho dropdown bảo hành
                            loadRentals();

                            warrantyReceiverInput.addEventListener('change', function() {
                                const selectedOption = this.options[this.selectedIndex];
                                const projectIdInput = document.getElementById('project_id');

                                if (selectedOption && selectedOption.dataset.projectId) {
                                    if (projectIdInput) projectIdInput.value = selectedOption.dataset.projectId;
                                } else if (selectedOption && selectedOption.dataset.rentalId) {
                                    if (projectIdInput) projectIdInput.value = selectedOption.dataset.rentalId;
                                } else {
                                    if (projectIdInput) projectIdInput.value = '';
                                }
                            });
                        

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

                // Trigger change event khi load page để setup initial state (chỉ khi phiếu pending)
                if ('{{ $dispatch->status }}' === 'pending') {
                    const event = new Event('change');
                    dispatchTypeSelect.dispatchEvent(event);
                } else {
                    // For non-pending dispatches, still need to setup the correct sections
                    const currentType = dispatchTypeSelect.value;
                    if (currentType === 'rental') {
                        const rentalSection = document.getElementById('rental_section');
                        const projectSection = document.getElementById('project_section');
                        const warrantySection = document.getElementById('warranty_section');
                        
                        if (rentalSection) rentalSection.classList.remove('hidden');
                        if (projectSection) projectSection.classList.add('hidden');
                        if (warrantySection) warrantySection.classList.add('hidden');
                        
                        // Load rentals for display
                        loadRentals();
                    } else if (currentType === 'warranty') {
                        const warrantySection = document.getElementById('warranty_section');
                        const projectSection = document.getElementById('project_section');
                        const rentalSection = document.getElementById('rental_section');
                        
                        if (warrantySection) warrantySection.classList.remove('hidden');
                        if (projectSection) projectSection.classList.add('hidden');
                        if (rentalSection) rentalSection.classList.add('hidden');
                        
                        // Load rentals for warranty dropdown
                        loadRentals();
                    }
                }
            }

            // Xử lý sự kiện change cho project_receiver
            if (projectReceiverSelect) {
                projectReceiverSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const projectIdInput = document.getElementById('project_id');
                    
                    if (selectedOption && selectedOption.dataset.projectId) {
                        if (projectIdInput) {
                            projectIdInput.value = selectedOption.dataset.projectId;
                        }
                    } else {
                        if (projectIdInput) {
                            projectIdInput.value = '';
                        }
                    }
                });
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

            // Nếu phiếu đã duyệt, load available serials và device codes để hiển thị serial (đổi tên / gốc)
            @if ($dispatch->status === 'approved')
                // 1) Load options
                loadAvailableSerials();
                // 2) Load device codes (serial đã đổi tên) rồi áp vào giao diện chính
                setTimeout(() => {
                    if (typeof loadDeviceCodesOnPageLoad === 'function') {
                        loadDeviceCodesOnPageLoad().then(() => {
                            // Áp lại mapping serial (đổi tên / gốc) theo từng index
                            if (typeof updateMainInterfaceSerials === 'function') {
                                updateMainInterfaceSerials('contract');
                                updateMainInterfaceSerials('backup');
                            }
                            // 3) Ép các select hiển thị đúng giá trị đã chọn
                            setTimeout(() => {
                                if (typeof updateSerialOptionsAvailability === 'function') {
                                    updateSerialOptionsAvailability();
                                }
                                    if (typeof enforceSelectedSerials === 'function') {
                                        enforceSelectedSerials();
                                    }
                            }, 200);
                        }).catch(() => {
                            // Dù lỗi, vẫn cố gắng sync để giữ giá trị cũ nếu có
                            if (typeof updateSerialOptionsAvailability === 'function') {
                                updateSerialOptionsAvailability();
                            }
                            if (typeof enforceSelectedSerials === 'function') {
                                enforceSelectedSerials();
                            }
                        });
                    }
                }, 100);
            @endif

            // Hàm load available serial numbers cho tất cả serial selects
            async function loadAvailableSerials() {
                const serialSelects = document.querySelectorAll('select[name*="serial_numbers"]');

                for (const select of serialSelects) {
                    // Chống gọi chồng lấp gây lặp option
                    if (select.getAttribute('data-loading-serials') === 'true') {
                        continue;
                    }
                    select.setAttribute('data-loading-serials', 'true');
                    // Vẫn load options cho select dù disabled (để hiển thị serial đã xuất)

                    const itemType = select.dataset.itemType;
                    const itemId = select.dataset.itemId;
                    // Determine latest warehouse id from row select, fallback to dataset
                    let warehouseId = select.dataset.warehouseId;
                    const rowEl = select.closest('tr');
                    if (rowEl) {
                        const rowWarehouseSelect = rowEl.querySelector('select[name*="warehouse_id"], select[data-role="warehouse-select"]');
                        if (rowWarehouseSelect && rowWarehouseSelect.value) {
                            warehouseId = rowWarehouseSelect.value;
                            select.dataset.warehouseId = warehouseId; // keep in sync
                        }
                    }
                    let selectedSerial = select.dataset.selectedSerial;
                    const category = select.dataset.type; // 'contract' | 'backup'
                    const serialIndex = parseInt(select.dataset.serialIndex || '0');
                    const originalSerial = (select.getAttribute('data-original-serial') || '').trim();
                    // Tính trước serial mong muốn theo mapping (ưu tiên đổi tên, nếu không có dùng serial gốc)
                    let mappedDesired = '';
                    try {
                        const productIdNum = parseInt(itemId);
                        const productsArr = category === 'backup' ? selectedBackupProducts : selectedContractProducts;
                        const prod = Array.isArray(productsArr) ? productsArr.find(p => parseInt(p.id) === productIdNum) : null;
                        const dcArr = (window.deviceCodesData && window.deviceCodesData[category]) ? window.deviceCodesData[category] : [];
                        const dcForProduct = dcArr.filter(dc => parseInt(dc.product_id) === productIdNum);
                        const renamed = dcForProduct[serialIndex] && dcForProduct[serialIndex].serial_main ? dcForProduct[serialIndex].serial_main : '';
                        if (renamed && String(renamed).trim() !== '') {
                            mappedDesired = String(renamed).trim();
                        } else if (prod && Array.isArray(prod.serial_numbers)) {
                            const original = prod.serial_numbers[serialIndex] || '';
                            if (original && String(original).trim() !== '') {
                                mappedDesired = String(original).trim();
                            }
                        }
                    } catch (e) {
                        console.warn('mappedDesired error:', e);
                    }

                    // Nếu đã khóa lựa chọn từ device codes thì giữ nguyên và bỏ qua refresh
                    if (select.getAttribute('data-lock-selected') === 'true') {
                        const locked = select.getAttribute('data-mapped-serial') || select.getAttribute('data-selected-serial') || '';
                        if (locked) {
                            selectedSerial = locked;
                            select.setAttribute('data-selected-serial', locked);
                        }
                    }

                    // Cập nhật lại data-selected-serial theo mappedDesired/original để loại bỏ giá trị cũ (a111/a222...)
                    const desiredForThisSelect = mappedDesired || originalSerial || '';
                    if (desiredForThisSelect) {
                        select.setAttribute('data-selected-serial', desiredForThisSelect);
                        selectedSerial = desiredForThisSelect;
                    }

                    // Also check if select already has a value selected
                    const currentValue = select.value;

                    if (!itemType || !itemId || !warehouseId) continue;

                    try {
                        // disable while loading to avoid choosing stale options
                        const prevDisabled = select.disabled;
                        select.disabled = true;
                        const response = await fetch(
                            `/api/dispatch/item-serials?item_type=${itemType}&item_id=${itemId}&warehouse_id=${warehouseId}&current_dispatch_id={{ $dispatch->id }}`
                        );
                        const data = await response.json();

                        if (data.success) {
                            // Log serial availability info for debugging
                            if (data.total_serials && data.used_serials) {
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
                                // Luôn dùng mappedDesired nếu có; chỉ dùng current/selected khi trùng với mappedDesired
                                let valueToPreserve = mappedDesired || originalSerial || '';
                                if (!valueToPreserve) {
                                    const candidate = currentValue || selectedSerial || '';
                                    if (candidate && candidate === selectedSerial) valueToPreserve = candidate;
                                }

                                // Clear ALL existing options except default
                                while (select.children.length > 1) {
                                    select.removeChild(select.lastChild);
                                }

                                // Add serial options from API (dedup) - ẩn serial cũ nếu có serial đổi tên
                                const seen = new Set();
                                data.serials.forEach(serial => {
                                    if (seen.has(serial)) return;
                                    seen.add(serial);
                                    
                                    // Nếu có serial đổi tên, ẩn serial gốc khỏi danh sách
                                    if (mappedDesired && mappedDesired !== serial) {
                                        // Kiểm tra xem serial này có phải là serial gốc của serial đã đổi tên không
                                        const isOriginalSerial = originalSerial === serial;
                                        if (isOriginalSerial) {
                                            return; // Bỏ qua serial gốc, không thêm vào options
                                        }
                                    }
                                    
                                    const option = document.createElement('option');
                                    option.value = serial;
                                    option.textContent = serial;
                                    select.appendChild(option);
                                });
                                
                                // Nếu có serial đổi tên và chưa có trong options, thêm vào
                                if (mappedDesired && !seen.has(mappedDesired)) {
                                    const option = document.createElement('option');
                                    option.value = mappedDesired;
                                    option.textContent = mappedDesired;
                                    select.appendChild(option);
                                    seen.add(mappedDesired); // Thêm vào seen để tránh duplicate
                                }

                                // Set preserved value only if still valid in current warehouse or allowed by status
                                const isLocked = (select.getAttribute('data-lock-selected') === 'true');
                                if (valueToPreserve) {
                                    // Kiểm tra xem valueToPreserve có tồn tại trong options đã được thêm vào select không
                                    const optionExists = Array.from(select.options).some(option => option.value === valueToPreserve);
                                    console.log('Setting serial value:', {
                                        valueToPreserve,
                                        optionExists,
                                        mappedDesired,
                                        originalSerial,
                                        availableOptions: Array.from(select.options).map(opt => opt.value)
                                    });
                                    if (optionExists) {
                                        select.value = valueToPreserve;
                                        select.setAttribute('data-selected-serial', valueToPreserve);
                                        if (isLocked) select.setAttribute('data-mapped-serial', valueToPreserve);
                                    } else {
                                        // Clear invalid preserved selection for current warehouse
                                        select.value = '';
                                        select.setAttribute('data-selected-serial', '');
                                        if (isLocked) select.removeAttribute('data-mapped-serial');
                                    }
                                }
                            } else {
                                // If no serials available from API, clear all options and selections
                                
                                // Clear ALL existing options except default
                                while (select.children.length > 1) {
                                    select.removeChild(select.lastChild);
                                }
                                
                                // For pending: do NOT preserve serial when the new warehouse has none
                                    select.value = '';
                                    select.setAttribute('data-selected-serial', '');

                                // Add change event listener for validation
                                if (!select.hasAttribute('data-validation-listener')) {
                                    select.addEventListener('change', validateSerialOnChange);
                                    select.setAttribute('data-validation-listener', 'true');
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Error loading serials:', error);

                        // If API call fails, clear all options and selections to avoid showing invalid serials
                        const valueToPreserve = currentValue || selectedSerial;
                        if (valueToPreserve) {
                            console.log(`API error - Clearing value "${valueToPreserve}" for ${itemType} ${itemId} to avoid showing potentially invalid serials`);
                        }
                        
                        // Clear ALL existing options except default
                        while (select.children.length > 1) {
                            select.removeChild(select.lastChild);
                        }
                        
                        // Clear selection
                        select.value = '';
                        select.setAttribute('data-selected-serial', '');

                        // Add change event listener for validation even on error
                        if (!select.hasAttribute('data-validation-listener')) {
                            select.addEventListener('change', validateSerialOnChange);
                            select.setAttribute('data-validation-listener', 'true');
                        }
                    } finally {
                        // Mở khóa
                        select.setAttribute('data-loading-serials', 'false');
                        // re-enable after load
                        select.disabled = false;
                    }
                }
            }

            // Populate product dropdowns: now handled by search dropdowns, keep as no-op for compatibility
            function populateProductDropdowns() {}

            // Setup dropdown handlers giống trang create
            function setupDropdownHandlers() {
                // Xử lý thêm sản phẩm hợp đồng
                const addContractProductBtn = document.getElementById('add_contract_product_btn');
                const contractProductHidden = document.getElementById('contract_product_select');

                if (addContractProductBtn) {
                    addContractProductBtn.addEventListener('click', function() {
                        const selectedProductId = contractProductHidden.value;

                        if (!selectedProductId) {
                            alert('Vui lòng chọn thiết bị theo hợp đồng để thêm!');
                            return;
                        }

                        addContractProduct(parseInt(selectedProductId));
                        contractProductHidden.value = '';
                    });
                }

                // Xử lý thêm thiết bị dự phòng
                const addBackupProductBtn = document.getElementById('add_backup_product_btn');
                const backupProductHidden = document.getElementById('backup_product_select');

                if (addBackupProductBtn) {
                    addBackupProductBtn.addEventListener('click', function() {
                        const selectedProductId = backupProductHidden.value;

                        if (!selectedProductId) {
                            alert('Vui lòng chọn thiết bị dự phòng để thêm!');
                            return;
                        }

                        addBackupProduct(parseInt(selectedProductId));
                        backupProductHidden.value = '';
                    });
                }
            }

            // Hàm thêm sản phẩm hợp đồng
            function addContractProduct(productId) {
                const selectedType = 'product'; // Default type for contract products

                // Ưu tiên tìm product trước, nếu không có mới tìm good
                let foundProduct = availableItems.find(p => p.id == productId && p.type === 'product');
                if (!foundProduct) {
                    // Nếu không tìm thấy product, thử tìm good với cùng ID
                    foundProduct = availableItems.find(p => p.id == productId && p.type === 'good');
                }

                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                // Kiểm tra đã thêm chưa
                const existingProduct = selectedContractProducts.find(p => p.id === foundProduct.id && p.type ===
                    foundProduct.type);
                if (existingProduct) {
                    alert('Sản phẩm này đã được thêm vào danh sách hợp đồng!');
                    return;
                }

                selectedContractProducts.push({
                    ...foundProduct,
                    quantity: 1,
                    selected_warehouse_id: foundProduct.warehouses && foundProduct.warehouses.length > 0 ?
                        foundProduct.warehouses[0].warehouse_id : null,
                    current_stock: foundProduct.warehouses && foundProduct.warehouses.length > 0 ?
                        foundProduct.warehouses[0].quantity : 0
                });

                renderContractProductTable();
                showStockWarnings();
                
                // Resolve assembly_id và product_unit cho product mới thêm
                setTimeout(() => {
                    resolveMissingAssemblyForAll();
                }, 100);
            }

            // Hàm thêm thiết bị dự phòng
            function addBackupProduct(productId) {
                const selectedType = 'product'; // Default type for backup products

                // Ưu tiên tìm product trước, nếu không có mới tìm good
                let foundProduct = availableItems.find(p => p.id == productId && p.type === 'product');
                if (!foundProduct) {
                    // Nếu không tìm thấy product, thử tìm good với cùng ID
                    foundProduct = availableItems.find(p => p.id == productId && p.type === 'good');
                }

                if (!foundProduct) {
                    alert('Không tìm thấy thông tin sản phẩm!');
                    return;
                }

                // Kiểm tra đã thêm chưa
                const existingProduct = selectedBackupProducts.find(p => p.id === foundProduct.id && p.type ===
                    foundProduct.type);
                if (existingProduct) {
                    alert('Thiết bị này đã được thêm vào danh sách dự phòng!');
                    return;
                }

                selectedBackupProducts.push({
                    ...foundProduct,
                    quantity: 1,
                    selected_warehouse_id: foundProduct.warehouses && foundProduct.warehouses.length > 0 ?
                        foundProduct.warehouses[0].warehouse_id : null,
                    current_stock: foundProduct.warehouses && foundProduct.warehouses.length > 0 ?
                        foundProduct.warehouses[0].quantity : 0
                });

                renderBackupProductTable();
                showStockWarnings();
                
                // Resolve assembly_id và product_unit cho product mới thêm
                setTimeout(() => {
                    resolveMissingAssemblyForAll();
                }, 100);
            }

            // Hàm load tất cả sản phẩm từ tất cả kho
            async function loadAllAvailableItems() {
                try {
                    const response = await fetch('/api/dispatch/items/all');
                    const data = await response.json();

                    if (data.success) {
                        // Chuẩn hóa dữ liệu để tương thích với frontend
                        availableItems = (data.items || []).map(item => ({
                            id: item.id,
                            type: item.type || 'product',
                            code: item.code,
                            name: item.name,
                            unit: item.unit,
                            warehouses: item.warehouses || [],
                            display_name: `${item.code} - ${item.name} (${item.type === 'product' ? 'Thành phẩm' : 'Hàng hóa'})`
                        }));
                        updateProductSelects();
                    } else {
                        console.error('Error loading items:', data.message);
                        alert('Lỗi từ server: ' + (data.message || 'Không thể tải danh sách sản phẩm'));
                    }
                } catch (error) {
                    console.error('Error loading items:', error);
                    alert('Không thể tải danh sách sản phẩm: ' + error.message);
                }
            }

            // Hàm cập nhật dropdown sản phẩm
            function updateProductSelects() {
                // Cập nhật dropdown hợp đồng
                const contractProductSelect = document.getElementById('contract_product_select');
                if (contractProductSelect) {
                    contractProductSelect.innerHTML = '<option value="">-- Chọn thiết bị theo hợp đồng --</option>';
                }

                // Cập nhật dropdown dự phòng
                const backupProductSelect = document.getElementById('backup_product_select');
                if (backupProductSelect) {
                    backupProductSelect.innerHTML = '<option value="">-- Chọn thiết bị dự phòng --</option>';
                }

                // Thêm options từ availableItems
                availableItems.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.display_name;
                    option.dataset.type = item.type;

                    if (contractProductSelect) {
                        const contractOption = option.cloneNode(true);
                        contractProductSelect.appendChild(contractOption);
                    }

                    if (backupProductSelect) {
                        const backupOption = option.cloneNode(true);
                        backupProductSelect.appendChild(backupOption);
                    }
                });
            }

            // Load tất cả sản phẩm từ tất cả kho ngay từ đầu
            loadAllAvailableItems();

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
                                data-selected-serial=""
                                data-type="${category}">
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

                    // Create select without pre-populating options - let loadAvailableSerials() handle it
                    let selectOptions = `<option value="">-- Chọn Serial ${i + 1} --</option>`;
                    // Don't pre-populate serial value - let API filter decide what's available
                    const isReadonly = product.is_existing && '{{ $dispatch->status }}' !== 'pending';
                    inputs +=
                        `<select name="${inputName}" ${isReadonly ? 'disabled' : ''}
                                class="w-32 border ${borderColor} rounded px-2 py-1 text-xs focus:outline-none focus:ring-1"
                                data-item-type="${product?.type || 'product'}" 
                                data-item-id="${productId}" 
                                data-warehouse-id="${product?.selected_warehouse_id || ''}"
                                data-serial-index="${i}"
                                data-selected-serial="${serialValue}"
                                data-original-serial="${serialValue}"
                                data-type="${category}">
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
                        select.setAttribute('data-type', category);

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
            function validateSerialOnChange(event) {
                const duplicates = validateSerialNumbers();
                showSerialDuplicateWarning(duplicates);

                // Disable các option đã chọn ở select khác
                updateSerialOptionsAvailability();

                // Resolve assembly_id và product_unit khi serial thay đổi cho product
                if (event && event.target) {
                    const itemType = event.target.getAttribute('data-item-type');
                    if (itemType === 'product') {
                        // Delay để đảm bảo DOM đã cập nhật
                        setTimeout(() => {
                            resolveMissingAssemblyForAll();
                        }, 100);
                    }
                }
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

            // Bảo đảm mỗi select hiển thị đúng serial đã chọn (kể cả khi API chưa trả option)
            function enforceSelectedSerials() {
                try {
                    const selects = document.querySelectorAll('select[name*="serial_numbers"]');
                    selects.forEach(select => {
                        const desired = (select.getAttribute('data-selected-serial') || '').trim();
                        if (!desired) return;
                        if (select.value && select.value.trim() === desired) return;
                        let option = Array.from(select.options).find(o => o.value === desired);
                        if (!option) {
                            option = document.createElement('option');
                            option.value = desired;
                            option.textContent = desired;
                            select.appendChild(option);
                        }
                        select.value = desired;
                    });
                } catch (e) {
                    console.warn('enforceSelectedSerials error:', e);
                }
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
                            const selected = warehouse.warehouse_id == product.selected_warehouse_id ? 'selected' : '';
                            // Fallback: nếu quantity = 0 nhưng sản phẩm đang chọn có current_stock > 0 và cùng kho, hiển thị current_stock
                            const effectiveQty = (warehouse.quantity && warehouse.quantity > 0)
                                ? warehouse.quantity
                                : ((warehouse.warehouse_id == product.selected_warehouse_id && (product.current_stock || 0) > 0)
                                    ? product.current_stock
                                    : 0);
                            const stockInfo = '{{ $dispatch->status }}' === 'pending' ? ` (Tồn: ${effectiveQty})` : '';
                            warehouseOptions += `<option value="${warehouse.warehouse_id}" ${selected} data-quantity="${effectiveQty}">${warehouse.warehouse_name}${stockInfo}</option>`;
                        });
                    }

                    const row = document.createElement('tr');
                    const isReadonly = product.is_existing && '{{ $dispatch->status }}' !== 'pending';

                    // Create hidden inputs for form submission (only for new items, not existing ones)
                    const hiddenInputsHtml = product.is_existing ? `
                        <!-- Existing item hidden inputs -->
                        <input type="hidden" name="contract_items[${product.existing_item_id}][item_type]" value="${product.type}">
                        <input type="hidden" name="contract_items[${product.existing_item_id}][item_id]" value="${product.id}">
                        <input type="hidden" name="contract_items[${product.existing_item_id}][warehouse_id]" value="${product.selected_warehouse_id}" class="contract-warehouse-hidden" data-index="${index}">
                        <input type="hidden" name="contract_items[${product.existing_item_id}][category]" value="contract">
                        <input type="hidden" name="contract_items[${product.existing_item_id}][quantity]" value="${product.quantity}" class="contract-quantity-hidden" data-index="${index}">
                        <input type="hidden" name="contract_items[${product.existing_item_id}][assembly_id]" value="${product.assembly_id || ''}">
                        <input type="hidden" name="contract_items[${product.existing_item_id}][product_unit]" value="${product.product_unit || ''}">
                    ` : `
                        <!-- New item hidden inputs -->
                        <input type="hidden" name="items[contract_${product.id}_${index}][item_type]" value="${product.type}">
                        <input type="hidden" name="items[contract_${product.id}_${index}][item_id]" value="${product.id}">
                        <input type="hidden" name="items[contract_${product.id}_${index}][warehouse_id]" value="${product.selected_warehouse_id}" class="contract-warehouse-hidden" data-index="${index}">
                        <input type="hidden" name="items[contract_${product.id}_${index}][category]" value="contract">
                        <input type="hidden" name="items[contract_${product.id}_${index}][quantity]" value="${product.quantity}" class="contract-quantity-hidden" data-index="${index}">
                        <input type="hidden" name="items[contract_${product.id}_${index}][assembly_id]" value="${product.assembly_id || ''}">
                        <input type="hidden" name="items[contract_${product.id}_${index}][product_unit]" value="${product.product_unit || ''}">
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
                    select.addEventListener('change', async function() {
                        const index = parseInt(this.dataset.index);
                        const newWarehouseId = parseInt(this.value);
                        const selectedOption = this.options[this.selectedIndex];
                        let newQuantity = parseInt(selectedOption.dataset.quantity);

                        // Fallback to API if option does not carry the right quantity
                        try {
                            const details = await getDetailedStockInfo(
                                selectedContractProducts[index].type,
                                selectedContractProducts[index].id,
                                newWarehouseId,
                                isNaN(newQuantity) ? (selectedContractProducts[index].current_stock || 0) : newQuantity
                            );
                            if (details && typeof details.totalStock === 'number') {
                                newQuantity = details.totalStock;
                                selectedOption.dataset.quantity = String(newQuantity);
                                if (selectedOption.textContent && selectedOption.textContent.includes('(')) {
                                    selectedOption.textContent = selectedOption.textContent.replace(/\(Tồn: .*?\)/, `(Tồn: ${newQuantity})`);
                                }
                            }
                        } catch (e) {
                            console.warn('Failed to get detailed stock for contract item:', e);
                            newQuantity = isNaN(newQuantity) ? 0 : newQuantity;
                        }

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
                        } else {
                            console.warn(`Contract warehouse hidden input not found for index ${index}`);
                        }

                        const hiddenQuantityInput = document.querySelector(
                            `.contract-quantity-hidden[data-index="${index}"]`);
                        if (hiddenQuantityInput) {
                            hiddenQuantityInput.value = quantityInput ? quantityInput.value :
                                selectedContractProducts[index].quantity;
                        }

                        // Reset serial khi thay đổi kho
                        const serialContainer = document.getElementById(`contract-serials-${index}`);
                        if (serialContainer) {
                            // Xóa tất cả serial đã chọn trước đó
                            const serialSelects = serialContainer.querySelectorAll('select');
                            serialSelects.forEach(serialSelect => {
                                serialSelect.value = '';
                                serialSelect.setAttribute('data-warehouse-id', newWarehouseId);
                                serialSelect.setAttribute('data-selected-serial', ''); // Xóa giá trị cũ
                            });
                        }

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

                // Làm mới số tồn trong dropdown ngay lập tức, không chờ load serial
                if (typeof refreshWarehouseOptionStocks === 'function') {
                    refreshWarehouseOptionStocks();
                }

                // Load available serials cho contract products (chạy song song)
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
                            const selected = warehouse.warehouse_id == product.selected_warehouse_id ? 'selected' : '';
                            const effectiveQty = (warehouse.quantity && warehouse.quantity > 0)
                                ? warehouse.quantity
                                : ((warehouse.warehouse_id == product.selected_warehouse_id && (product.current_stock || 0) > 0)
                                    ? product.current_stock
                                    : 0);
                            const stockInfo = '{{ $dispatch->status }}' === 'pending' ? ` (Tồn: ${effectiveQty})` : '';
                            warehouseOptions += `<option value="${warehouse.warehouse_id}" ${selected} data-quantity="${effectiveQty}">${warehouse.warehouse_name}${stockInfo}</option>`;
                        });
                    }

                    const row = document.createElement('tr');
                    const isReadonly = product.is_existing && '{{ $dispatch->status }}' !== 'pending';

                    // Create hidden inputs for form submission (only for new items, not existing ones)
                    const hiddenInputsHtml = product.is_existing ? `
                        <!-- Existing item hidden inputs -->
                        <input type="hidden" name="backup_items[${product.existing_item_id}][item_type]" value="${product.type}">
                        <input type="hidden" name="backup_items[${product.existing_item_id}][item_id]" value="${product.id}">
                        <input type="hidden" name="backup_items[${product.existing_item_id}][warehouse_id]" value="${product.selected_warehouse_id}" class="backup-warehouse-hidden" data-index="${index}">
                        <input type="hidden" name="backup_items[${product.existing_item_id}][category]" value="backup">
                        <input type="hidden" name="backup_items[${product.existing_item_id}][quantity]" value="${product.quantity}" class="backup-quantity-hidden" data-index="${index}">
                        <input type="hidden" name="backup_items[${product.existing_item_id}][assembly_id]" value="${product.assembly_id || ''}">
                        <input type="hidden" name="backup_items[${product.existing_item_id}][product_unit]" value="${product.product_unit || ''}">
                    ` : `
                        <!-- New item hidden inputs -->
                        <input type="hidden" name="items[backup_${product.id}_${index}][item_type]" value="${product.type}">
                        <input type="hidden" name="items[backup_${product.id}_${index}][item_id]" value="${product.id}">
                        <input type="hidden" name="items[backup_${product.id}_${index}][warehouse_id]" value="${product.selected_warehouse_id}" class="backup-warehouse-hidden" data-index="${index}">
                        <input type="hidden" name="items[backup_${product.id}_${index}][category]" value="backup">
                        <input type="hidden" name="items[backup_${product.id}_${index}][quantity]" value="${product.quantity}" class="backup-quantity-hidden" data-index="${index}">
                        <input type="hidden" name="items[backup_${product.id}_${index}][assembly_id]" value="${product.assembly_id || ''}">
                        <input type="hidden" name="items[backup_${product.id}_${index}][product_unit]" value="${product.product_unit || ''}">
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
                    select.addEventListener('change', async function() {
                        const index = parseInt(this.dataset.index);
                        const newWarehouseId = parseInt(this.value);
                        const selectedOption = this.options[this.selectedIndex];
                        let newQuantity = parseInt(selectedOption.dataset.quantity);

                        // Fallback to API if option does not carry the right quantity
                        try {
                            const details = await getDetailedStockInfo(
                                selectedBackupProducts[index].type,
                                selectedBackupProducts[index].id,
                                newWarehouseId,
                                isNaN(newQuantity) ? (selectedBackupProducts[index].current_stock || 0) : newQuantity
                            );
                            if (details && typeof details.totalStock === 'number') {
                                newQuantity = details.totalStock;
                                selectedOption.dataset.quantity = String(newQuantity);
                                if (selectedOption.textContent && selectedOption.textContent.includes('(')) {
                                    selectedOption.textContent = selectedOption.textContent.replace(/\(Tồn: .*?\)/, `(Tồn: ${newQuantity})`);
                                }
                            }
                        } catch (e) {
                            console.warn('Failed to get detailed stock for backup item:', e);
                            newQuantity = isNaN(newQuantity) ? 0 : newQuantity;
                        }

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
                        } else {
                            console.warn(`Backup warehouse hidden input not found for index ${index}`);
                        }

                        const hiddenQuantityInput = document.querySelector(
                            `.backup-quantity-hidden[data-index="${index}"]`);
                        if (hiddenQuantityInput) {
                            hiddenQuantityInput.value = quantityInput ? quantityInput.value :
                                selectedBackupProducts[index].quantity;
                        }

                        // Reset serial khi thay đổi kho
                        const serialContainer = document.getElementById(`backup-serials-${index}`);
                        if (serialContainer) {
                            // Xóa tất cả serial đã chọn trước đó
                            const serialSelects = serialContainer.querySelectorAll('select');
                            serialSelects.forEach(serialSelect => {
                                serialSelect.value = '';
                                serialSelect.setAttribute('data-warehouse-id', newWarehouseId);
                                serialSelect.setAttribute('data-selected-serial', ''); // Xóa giá trị cũ
                            });
                        }

                        // Load lại available serials cho warehouse mới
                        loadAvailableSerials();

                        // Kiểm tra tồn kho ngay khi thay đổi kho
                        showStockWarnings();
                    });
                });

                // Làm mới số tồn trong dropdown ngay lập tức, không chờ load serial
                if (typeof refreshWarehouseOptionStocks === 'function') {
                    refreshWarehouseOptionStocks();
                }

                // Load available serials cho backup products (chạy song song)
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

                    // Disable inputs của nhóm KHÔNG được chọn để tránh submit nhầm
                    toggleCategoryInputsByDispatchDetail(selectedDetail);
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

            // Bật/tắt input theo dispatch_detail để tránh submit nhầm nhóm
            function toggleCategoryInputsByDispatchDetail(selectedDetail) {
                const formEl = document.querySelector('form');
                if (!formEl) return;

                // Helper: enable/disable inputs by name prefix
                const setDisabledByPrefix = (prefix, disabled) => {
                    formEl.querySelectorAll(`input[name^="${prefix}"]`).forEach(inp => {
                        inp.disabled = !!disabled;
                    });
                };

                if (selectedDetail === 'all') {
                    // Cả hai nhóm đều bật
                    setDisabledByPrefix('contract_items', false);
                    setDisabledByPrefix('backup_items', false);
                } else if (selectedDetail === 'contract') {
                    setDisabledByPrefix('contract_items', false);
                    setDisabledByPrefix('backup_items', true);
                } else if (selectedDetail === 'backup') {
                    setDisabledByPrefix('contract_items', true);
                    setDisabledByPrefix('backup_items', false);
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
                        showStockWarningsWrapper();
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
                        showStockWarningsWrapper();
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
                        showStockWarningsWrapper();
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
                            showStockWarningsWrapper();
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
                            showStockWarningsWrapper();
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
                            showStockWarningsWrapper();
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
                        select.setAttribute('data-type', 'contract');
                    } else if (category === 'backup') {
                        select.classList.add('border-orange-300', 'focus:ring-orange-500');
                        select.name = quantityInput.name.replace('[quantity]', `[serial_numbers][${i}]`);
                        select.setAttribute('data-type', 'backup');
                    } else {
                        select.classList.add('border-gray-300', 'focus:ring-blue-500');
                        select.name = quantityInput.name.replace('[quantity]', `[serial_numbers][${i}]`);
                        select.setAttribute('data-type', 'general');
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
            async function validateEditStock() {
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
                for (const key of Object.keys(groupedItems)) {
                    const group = groupedItems[key];
                    // Tìm thông tin sản phẩm từ availableItems hoặc từ existing data
                    let currentStock = 0;
                    let productName = 'Không xác định';
                    let productCode = 'N/A';

                    // Tìm trong availableItems nếu có
                    if (typeof availableItems !== 'undefined' && availableItems.length > 0) {
                        const foundItem = availableItems.find(item =>
                            item.id == group.item.item_id && item.type == group.item.item_type
                        );
                        if (foundItem) {
                            const warehouse = foundItem.warehouses.find(w => w.warehouse_id == group.item.warehouse_id);
                            if (warehouse) {
                                currentStock = warehouse.quantity;
                                productName = foundItem.name;
                                productCode = foundItem.code;
                            }
                        }
                    }

                    // Nếu không tìm thấy trong availableItems, thử tìm trong selectedContractProducts và selectedBackupProducts
                    if (productName === 'Không xác định') {
                        // Tìm trong contract products
                        const contractItem = selectedContractProducts.find(item => 
                            item.id == group.item.item_id && item.type == group.item.item_type
                        );
                        if (contractItem) {
                            productName = contractItem.name;
                            productCode = contractItem.code;
                            // Tìm stock từ warehouses array
                            const warehouse = contractItem.warehouses.find(w => w.warehouse_id == group.item.warehouse_id);
                            if (warehouse) {
                                currentStock = warehouse.quantity;
                            }
                        } else {
                            // Tìm trong backup products
                            const backupItem = selectedBackupProducts.find(item => 
                                item.id == group.item.item_id && item.type == group.item.item_type
                            );
                            if (backupItem) {
                                productName = backupItem.name;
                                productCode = backupItem.code;
                                // Tìm stock từ warehouses array
                                const warehouse = backupItem.warehouses.find(w => w.warehouse_id == group.item.warehouse_id);
                                if (warehouse) {
                                    currentStock = warehouse.quantity;
                                }
                            }
                        }
                    }

                    // Nếu vẫn không tìm thấy, thử lấy từ DOM elements
                    if (productName === 'Không xác định') {
                        // Tìm row trong DOM để lấy thông tin sản phẩm
                        const allRows = document.querySelectorAll('#contract-product-table tbody tr, #backup-product-table tbody tr');
                        for (let row of allRows) {
                            // Kiểm tra xem row có chứa item_id và item_type trong hidden inputs không
                            const hiddenItemIdInput = row.querySelector('input[name*="[item_id]"]');
                            const hiddenItemTypeInput = row.querySelector('input[name*="[item_type]"]');
                            
                            if (hiddenItemIdInput && hiddenItemTypeInput) {
                                const rowItemId = parseInt(hiddenItemIdInput.value);
                                const rowItemType = hiddenItemTypeInput.value;
                                
                                if (rowItemId == group.item.item_id && rowItemType == group.item.item_type) {
                                    const nameCell = row.querySelector('td:nth-child(2)'); // Cột tên sản phẩm
                                    const codeCell = row.querySelector('td:nth-child(1)'); // Cột mã sản phẩm
                                    if (nameCell) productName = nameCell.textContent.trim();
                                    if (codeCell) productCode = codeCell.textContent.trim();
                                    break;
                                }
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

                        // Tạo thông báo chi tiết hơn về loại tồn kho
                        let stockDetailMessage = '';
                        
                        // Lấy thông tin chi tiết về stock (có serial và không có serial)
                        const stockDetails = await getDetailedStockInfo(group.item.item_type, group.item.item_id, group.item.warehouse_id, currentStock);
                        
                        if (stockDetails.hasSerial) {
                            stockDetailMessage = `Tồn kho có serial: ${stockDetails.serialStock}, không có serial: ${stockDetails.nonSerialStock}, yêu cầu ${group.totalQuantity}`;
                        } else {
                            stockDetailMessage = `Tồn kho không có serial: ${currentStock}, yêu cầu ${group.totalQuantity}`;
                        }

                        stockErrors.push(
                            `${productCode} - ${productName}: ` +
                            `${stockDetailMessage} ` +
                            `(Tổng từ: ${categoriesText})`
                        );
                    }
                }

                return stockErrors;
            }

            // Hàm hiển thị cảnh báo tồn kho cho trang edit
            async function showEditStockWarnings() {
                const stockErrors = await validateEditStock();

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

            // Wrapper function để gọi showEditStockWarnings từ event listeners
            function showStockWarningsWrapper() {
                showEditStockWarnings().catch(error => {
                    console.warn('Error showing stock warnings:', error);
                });
            }

            // Xử lý form submit
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    // Log contract warehouse IDs
                    const contractWarehouseInputs = form.querySelectorAll('input[name*="contract_items"][name*="warehouse_id"], input[name*="items"][name*="contract"][name*="warehouse_id"]');
                    contractWarehouseInputs.forEach((input, index) => {
                        console.log(`Contract warehouse input ${index}:`, {
                            name: input.name,
                            value: input.value,
                            disabled: input.disabled
                        });
                    });
                    
                    // Log backup warehouse IDs
                    const backupWarehouseInputs = form.querySelectorAll('input[name*="backup_items"][name*="warehouse_id"], input[name*="items"][name*="backup"][name*="warehouse_id"]');
                    backupWarehouseInputs.forEach((input, index) => {
                        console.log(`Backup warehouse input ${index}:`, {
                            name: input.name,
                            value: input.value,
                            disabled: input.disabled
                        });
                    });
                    
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
                    const dispatchDetail = dispatchDetailSelect ? dispatchDetailSelect.value :
                        '{{ $dispatch->dispatch_detail }}';
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
                            errorMessage =
                                'Vui lòng chọn ít nhất một sản phẩm hợp đồng và một thiết bị dự phòng để xuất kho!';
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
                            errorMessage =
                                'Phiếu xuất theo hợp đồng phải có ít nhất một thành phẩm theo hợp đồng!';
                        } else if (totalBackupItems > 0) {
                            // Tự động clear nhóm dự phòng và thông báo ngắn gọn
                            const backupTable = document.getElementById('selected-backup-products');
                            if (backupTable) {
                                const rows = backupTable.querySelectorAll('tr');
                                rows.forEach(row => {
                                    const inputs = row.querySelectorAll('input, textarea');
                                    inputs.forEach(inp => inp.disabled = true);
                                });
                            }
                            // Clear mảng tạm nếu đang ở trạng thái pending (sản phẩm mới thêm)
                            try {
                                selectedBackupProducts.length = 0;
                                if (typeof renderBackupProductTable === 'function') {
                                    renderBackupProductTable();
                                }
                            } catch (_) {}
                            hasRequiredProducts = true; // tiếp tục submit sau khi đã clear
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
                            errorMessage =
                                'Phiếu xuất thiết bị dự phòng phải có ít nhất một thiết bị dự phòng!';
                        } else {
                            hasRequiredProducts = true;
                        }
                    }

                    if (!hasRequiredProducts) {
                        e.preventDefault();
                        alert(errorMessage);
                        return;
                    }

                    // Cuối cùng, trước khi submit: khóa nhóm không được chọn theo dispatch_detail
                    try {
                        const selectedDetailFinal = (document.getElementById('dispatch_detail') || {}).value || '{{ $dispatch->dispatch_detail }}';
                        // Gọi lại toggle để đảm bảo trạng thái disabled đúng tại thời điểm submit
                        toggleCategoryInputsByDispatchDetail(selectedDetailFinal);
                    } catch (err) {
                        console.warn('toggleCategoryInputsByDispatchDetail error:', err);
                    }

                    // Kiểm tra tồn kho trước khi submit (chỉ cho dispatch pending)
                    @if ($dispatch->status === 'pending')
                        const stockErrors = await validateEditStock();
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

            // Xử lý modal cập nhật mã thiết bị
            const updateContractDeviceCodesBtn = document.getElementById('update_contract_device_codes_btn');
            const updateBackupDeviceCodesBtn = document.getElementById('update_backup_device_codes_btn');
            const deviceCodeModal = document.getElementById('device-code-modal');
            const closeDeviceCodeModalBtn = document.getElementById('close-device-code-modal');
            const cancelDeviceCodesBtn = document.getElementById('cancel-device-codes');
            const saveDeviceCodesBtn = document.getElementById('save-device-codes');
            const importDeviceCodesBtn = document.getElementById('import-device-codes');
            const syncSerialsBtn = document.getElementById('sync-serials-btn');
            const syncSerialNumbersBtn = document.getElementById('sync-serial-numbers');

            let currentDeviceCodeType = '';
            let currentProductId = null;
            let syncRequested = false; // Biến để kiểm soát việc sync
            let syncEnabled = false; // Biến để kiểm soát việc sync hoàn toàn

            // Lấy dispatch_id từ URL hoặc từ form
            const dispatchId = {{ $dispatch->id }};

            if (updateContractDeviceCodesBtn) {
                updateContractDeviceCodesBtn.addEventListener('click', function() {
                    // Xóa active class từ tất cả buttons
                    updateContractDeviceCodesBtn.classList.remove('active');
                    updateBackupDeviceCodesBtn.classList.remove('active');
                    
                    // Set active class cho button hiện tại
                    updateContractDeviceCodesBtn.classList.add('active');
                    
                    currentDeviceCodeType = 'contract';
                    window.currentDeviceCodeType = 'contract';
                    deviceCodeModal.classList.remove('hidden');
                    // Load device codes mới nhất khi mở modal
                    loadDeviceCodesFromDatabase('contract');
                    
                    // Vô hiệu hóa sync mặc định khi mở modal
                    disableAllSync();
                    
                    // Đồng bộ serial numbers khi mở modal (chỉ một lần)
                    setTimeout(() => {
                        enableSync();
                        syncFromMainToModal();
                        disableAllSync();
                    }, 200);
                });
            }

            if (updateBackupDeviceCodesBtn) {
                updateBackupDeviceCodesBtn.addEventListener('click', function() {
                    // Xóa active class từ tất cả buttons
                    updateContractDeviceCodesBtn.classList.remove('active');
                    updateBackupDeviceCodesBtn.classList.remove('active');
                    
                    // Set active class cho button hiện tại
                    updateBackupDeviceCodesBtn.classList.add('active');
                    
                    currentDeviceCodeType = 'backup';
                    window.currentDeviceCodeType = 'backup';
                    deviceCodeModal.classList.remove('hidden');
                    // Load device codes mới nhất khi mở modal
                    loadDeviceCodesFromDatabase('backup');
                    
                    // Vô hiệu hóa sync mặc định khi mở modal
                    disableAllSync();
                    
                    // Đồng bộ serial numbers khi mở modal (chỉ một lần)
                    setTimeout(() => {
                        enableSync();
                        syncFromMainToModal();
                        disableAllSync();
                    }, 200);
                });
            }

            // Xử lý nút cập nhật serial
            if (syncSerialsBtn) {
                syncSerialsBtn.addEventListener('click', function() {
                    // Kích hoạt sync
                    enableSync();
                    // Chỉ sync khi nhấn nút này
                    syncSerialNumbers();
                    // Vô hiệu hóa sync sau khi hoàn tất
                    disableAllSync();
                });
            }

            // Xử lý nút đồng bộ serial numbers từ device_codes sang dispatch_items
            if (syncSerialNumbersBtn) {
                syncSerialNumbersBtn.addEventListener('click', async function() {
                    try {
                        // Hiển thị trạng thái loading
                        syncSerialNumbersBtn.disabled = true;
                        syncSerialNumbersBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang đồng bộ...';

                        const response = await fetch('/api/device-codes/sync-serial-numbers', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            },
                            body: JSON.stringify({
                                dispatch_id: dispatchId,
                                type: currentDeviceCodeType
                            })
                        });

                        const result = await response.json();

                        if (!result.success) {
                            throw new Error(result.message || 'Lỗi khi đồng bộ serial numbers');
                        }

                        alert('Đồng bộ serial numbers thành công!');
                        
                        // Redirect về trang dự án hoặc rental với tham số refresh
                        const projectId = {{ $dispatch->project_id ?? 'null' }};
                        const dispatchType = '{{ $dispatch->dispatch_type ?? "" }}';
                        
                        if (projectId && dispatchType === 'project') {
                            window.location.href = `/projects/${projectId}?refresh=true`;
                        } else if (projectId && dispatchType === 'rental') {
                            window.location.href = `/rentals/${projectId}?refresh=true`;
                        } else {
                            // Nếu không có project_id hoặc không phải project/rental, refresh trang hiện tại
                            location.reload();
                        }

                    } catch (error) {
                        console.error('Error syncing serial numbers:', error);
                        alert('Có lỗi xảy ra khi đồng bộ serial numbers: ' + error.message);
                    } finally {
                        // Khôi phục trạng thái nút
                        syncSerialNumbersBtn.disabled = false;
                        syncSerialNumbersBtn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Đồng bộ Serial';
                    }
                });
            }

            // Hàm load dữ liệu mã thiết bị từ database
            async function loadDeviceCodesFromDatabase(type) {
                try {
                    // Hiển thị trạng thái loading
                    const tbody = document.getElementById('device-code-tbody');
                    tbody.innerHTML =
                        '<tr><td colspan="8" class="px-4 py-3 text-center">Đang tải dữ liệu...</td></tr>';

                    // Load device code materials - sử dụng API chuyên dụng cho modal
                    try {
                        const materialsResponse = await fetch(`/api/products/device-code-materials?dispatch_id=${dispatchId}&type=${type}`);
                        if (!materialsResponse.ok) {
                            throw new Error('Network response was not ok');
                        }
                        const materialsData = await materialsResponse.json();
                        if (!materialsData.success) {
                            throw new Error(materialsData.message || 'Lỗi khi lấy thông tin vật tư cho device code');
                        }

                        // Store materials count in global variable
                        window.productMaterialsCount = materialsData.data || {};
                        
                        // Chạy autofill sau khi load xong materials data
                        setTimeout(() => {
                            if (typeof runAutofillForAllRows === 'function') {
                                runAutofillForAllRows();
                            }
                        }, 100);
                    } catch (error) {
                        console.error('Error loading device code materials:', error);
                        window.productMaterialsCount = {}; // Use empty object as fallback
                    }

                    // Gọi API để lấy dữ liệu device codes theo type
                    const response = await fetch(`/api/device-codes/${dispatchId}?type=${type}`);
                    const data = await response.json();

                    if (data.success) {
                        // Lưu device codes theo type để sử dụng sau
                        window.deviceCodesData = window.deviceCodesData || {};
                        window.deviceCodesData[type] = data.deviceCodes;
                        
                        renderDeviceCodeTable(data.deviceCodes, type);
                        
                        // Cập nhật serial numbers trong giao diện chính theo type
                        updateMainInterfaceSerials(type);
                    } else {
                        // Nếu không có device codes, vẫn hiển thị bảng với sản phẩm
                        renderDeviceCodeTable([], type);
                    }
                } catch (error) {
                    console.error('Error loading device codes:', error);
                    // Nếu có lỗi, vẫn hiển thị bảng với sản phẩm
                    renderDeviceCodeTable([], type);
                }
            }

            // Hàm load device codes khi trang load để cập nhật serial_main
            async function loadDeviceCodesOnPageLoad() {
                try {
                    // Load device codes cho contract
                    const contractResponse = await fetch(`/api/device-codes/${dispatchId}?type=contract`);
                    const contractData = await contractResponse.json();
                    
                    if (contractData.success) {
                        window.deviceCodesData = window.deviceCodesData || {};
                        window.deviceCodesData['contract'] = contractData.deviceCodes;
                        updateMainInterfaceSerials('contract');
                    } else {
                        console.log('No contract device codes found');
                    }

                    // Load device codes cho backup
                    const backupResponse = await fetch(`/api/device-codes/${dispatchId}?type=backup`);
                    const backupData = await backupResponse.json();
                    
                    if (backupData.success) {
                        window.deviceCodesData = window.deviceCodesData || {};
                        window.deviceCodesData['backup'] = backupData.deviceCodes;
                        updateMainInterfaceSerials('backup');
                    } else {
                        console.log('No backup device codes found');
                    }

                } catch (error) {
                    console.error('Error loading device codes on page load:', error);
                }
            }

            // Hàm cập nhật serial numbers trong giao diện chính theo type
            function updateMainInterfaceSerials(type) {
                const deviceCodes = window.deviceCodesData?.[type] || [];
                console.log(`updateMainInterfaceSerials for ${type}:`, deviceCodes);
                console.log(`selectedContractProducts:`, selectedContractProducts);
                console.log(`selectedBackupProducts:`, selectedBackupProducts);
                
                if (type === 'contract') {
                    // Cập nhật serial cho contract products
                    selectedContractProducts.forEach((product) => {
                        const productDeviceCodes = deviceCodes.filter(dc => dc.product_id == product.id);
                        const quantity = product.quantity || 0;
                        for (let i = 0; i < quantity; i++) {
                            const deviceCode = productDeviceCodes[i];
                            const desiredSerial = deviceCode && deviceCode.serial_main
                                ? deviceCode.serial_main
                                : (Array.isArray(product.serial_numbers) ? (product.serial_numbers[i] || '') : '');
                            // Tìm kiếm theo product_id trong data attributes hoặc theo name pattern
                            let serialSelect = document.querySelector(`select[name*="contract_items"][name*="serial_numbers"][data-item-id="${product.id}"][data-serial-index="${i}"]`);
                            if (!serialSelect) {
                                // Thử tìm theo product_id trong data-product-id
                                serialSelect = document.querySelector(`select[name*="contract_items"][name*="serial_numbers"][data-product-id="${product.id}"][data-serial-index="${i}"]`);
                            }
                            if (!serialSelect) {
                                // Thử tìm theo tất cả contract serial selects và kiểm tra product_id
                                const allSelects = document.querySelectorAll(`select[name*="contract_items"][name*="serial_numbers"][data-serial-index="${i}"]`);
                                serialSelect = Array.from(allSelects).find(select => {
                                    const itemId = select.getAttribute('data-item-id');
                                    return itemId && selectedContractProducts.find(p => p.id == itemId && p.product_id == product.id);
                                });
                            }
                            console.log(`Looking for contract serial select for product ${product.id}, index ${i}:`, serialSelect);
                            if (!serialSelect) continue;
                            if (desiredSerial) {
                                // Đánh dấu lock ngay lập tức để tránh bị ghi đè
                                serialSelect.setAttribute('data-lock-selected', 'true');
                                serialSelect.setAttribute('data-mapped-serial', desiredSerial);
                                
                                let option = Array.from(serialSelect.options).find(opt => opt.value === desiredSerial);
                                if (!option) {
                                    // Chủ động thêm option nếu API chưa trả về (trường hợp serial gốc hoặc vừa đổi tên)
                                    option = document.createElement('option');
                                    option.value = desiredSerial;
                                    option.textContent = desiredSerial;
                                    serialSelect.appendChild(option);
                                }
                                serialSelect.value = desiredSerial;
                                serialSelect.setAttribute('data-selected-serial', desiredSerial);
                            }
                        }
                    });
                } else if (type === 'backup') {
                    // Cập nhật serial cho backup products
                    selectedBackupProducts.forEach((product) => {
                        const productDeviceCodes = deviceCodes.filter(dc => dc.product_id == product.id);
                        const quantity = product.quantity || 0;
                        for (let i = 0; i < quantity; i++) {
                            const deviceCode = productDeviceCodes[i];
                            const desiredSerial = deviceCode && deviceCode.serial_main
                                ? deviceCode.serial_main
                                : (Array.isArray(product.serial_numbers) ? (product.serial_numbers[i] || '') : '');
                            // Tìm kiếm theo product_id trong data attributes hoặc theo name pattern
                            let serialSelect = document.querySelector(`select[name*="backup_items"][name*="serial_numbers"][data-item-id="${product.id}"][data-serial-index="${i}"]`);
                            if (!serialSelect) {
                                // Thử tìm theo product_id trong data-product-id
                                serialSelect = document.querySelector(`select[name*="backup_items"][name*="serial_numbers"][data-product-id="${product.id}"][data-serial-index="${i}"]`);
                            }
                            if (!serialSelect) {
                                // Thử tìm theo tất cả backup serial selects và kiểm tra product_id
                                const allSelects = document.querySelectorAll(`select[name*="backup_items"][name*="serial_numbers"][data-serial-index="${i}"]`);
                                serialSelect = Array.from(allSelects).find(select => {
                                    const itemId = select.getAttribute('data-item-id');
                                    return itemId && selectedBackupProducts.find(p => p.id == itemId && p.product_id == product.id);
                                });
                            }
                            if (!serialSelect) continue;
                            if (desiredSerial) {
                                // Đánh dấu lock ngay lập tức để tránh bị ghi đè
                                serialSelect.setAttribute('data-lock-selected', 'true');
                                serialSelect.setAttribute('data-mapped-serial', desiredSerial);
                                
                                let option = Array.from(serialSelect.options).find(opt => opt.value === desiredSerial);
                                if (!option) {
                                    option = document.createElement('option');
                                    option.value = desiredSerial;
                                    option.textContent = desiredSerial;
                                    serialSelect.appendChild(option);
                                }
                                serialSelect.value = desiredSerial;
                                serialSelect.setAttribute('data-selected-serial', desiredSerial);
                            }
                        }
                    });
                }
            }

            // Helper function to get materials details for a product
            function getProductMaterialsCount(productId) {
                const materials = window.productMaterialsCount?.[productId] || [];
                return materials;
            }

            // Helper function to get materials details for a product by serial
            function getProductMaterialsCountBySerial(productId, serial) {
                const productData = window.productMaterialsCount?.[productId] || {};
                // Nếu productData là object với key là serial, lấy theo serial (kể cả key rỗng '')
                if (typeof productData === 'object' && !Array.isArray(productData)) {
                    return productData.hasOwnProperty(serial) ? (productData[serial] || []) : [];
                }
                // Nếu productData là array (fallback), trả về array đó
                return Array.isArray(productData) ? productData : [];
            }

            // Concurrency-safe cursor để phân bổ vật tư theo dòng N/A, tránh lặp lại cùng bộ vật tư
            window.__materialsCursorByProduct = window.__materialsCursorByProduct || {};

            // Helper: lấy materials cho 1 dòng theo ưu tiên: theo cặp assembly_id:product_unit -> serial -> theo index
            function getMaterialsForRow(productId, serial, rowIndex, assemblyIdForRow, productUnitForRow) {
                const data = window.productMaterialsCount?.[productId];

                // 0) Tra theo cặp assembly_id:product_unit nếu backend có cung cấp
                if (data && typeof data === 'object' && !Array.isArray(data) && assemblyIdForRow !== undefined && productUnitForRow !== undefined) {
                    const pairKeyCandidates = [
                        `${assemblyIdForRow}:${productUnitForRow}`,
                        `${assemblyIdForRow}|${productUnitForRow}`,
                        `A${assemblyIdForRow}_U${productUnitForRow}`
                    ];
                    for (const pk of pairKeyCandidates) {
                        if (Object.prototype.hasOwnProperty.call(data, pk) && Array.isArray(data[pk]) && data[pk].length > 0) {
                            return data[pk];
                        }
                    }
                    if (data.__by_pair__ && typeof data.__by_pair__ === 'object') {
                        const v = data.__by_pair__[`$${assemblyIdForRow}:${productUnitForRow}`] || data.__by_pair__[`$${assemblyIdForRow}|${productUnitForRow}`];
                        if (Array.isArray(v) && v.length > 0) return v;
                    }
                }

                // 1) Tra theo serial (kể cả rỗng)
                let materials = getProductMaterialsCountBySerial(productId, serial);
                if (Array.isArray(materials) && materials.length > 0) return materials;

                // 2) Thử các key đặc biệt mà backend có thể cung cấp cho N/A theo index
                if (data && typeof data === 'object' && !Array.isArray(data)) {
                    const candidateKeys = [
                        `__NA__${rowIndex}`,
                        `NA_${rowIndex}`,
                        `__INDEX__${rowIndex}`,
                        `INDEX_${rowIndex}`,
                        `${rowIndex}`,
                        '',
                        'null',
                        'NULL',
                        'undefined'
                    ];
                    for (const k of candidateKeys) {
                        if (Object.prototype.hasOwnProperty.call(data, k)) {
                            const v = data[k];
                            if (Array.isArray(v) && v.length > 0) return v;
                        }
                    }
                    // Các cấu trúc đặc biệt theo index
                    if (Array.isArray(data.__by_index__)) {
                        if (Array.isArray(data.__by_index__[rowIndex])) return data.__by_index__[rowIndex];
                    }
                    if (Array.isArray(data.by_index)) {
                        if (Array.isArray(data.by_index[rowIndex])) return data.by_index[rowIndex];
                    }
                }

                // 3) Nếu là mảng theo từng dòng, ưu tiên đúng index
                if (Array.isArray(data)) {
                    if (Array.isArray(data[rowIndex])) return data[rowIndex];
                    return Array.isArray(data[0]) ? data[0] : [];
                }

                // 4) Cuối cùng: dùng Object.values theo đúng index dòng để tránh lấy nhầm dòng 0
                if (data && typeof data === 'object') {
                    const values = Object.values(data);
                    if (Array.isArray(values[rowIndex])) return values[rowIndex];
                    // Nếu serial rỗng (N/A), phân bổ round-robin qua các bộ vật tư để tránh trùng lặp
                    if (!serial && values.length > 0) {
                        const key = String(productId);
                        const cursor = (window.__materialsCursorByProduct[key] || 0) % values.length;
                        window.__materialsCursorByProduct[key] = cursor + 1;
                        return Array.isArray(values[cursor]) ? values[cursor] : (Array.isArray(values[0]) ? values[0] : []);
                    }
                    return Array.isArray(values[0]) ? values[0] : [];
                }

                return [];
            }

            // Run autofill for all rows in the device code table
            function runAutofillForAllRows() {
                const tbody = document.getElementById('device-code-tbody');
                if (!tbody) {
                    return;
                }
                const rows = tbody.querySelectorAll('tr[data-product-id]');

                rows.forEach((row, idx) => {
                    fetchAndFillComponentSerials(row);
                });
            }

            // Fetch component serials by main serial from API and fill into a row
            async function fetchAndFillComponentSerials(rowEl) {
                try {
                    if (!rowEl) return;
                    
                    // If inputs are already filled, don't override; otherwise allow filling even if device code exists
                    const existingFilled = Array.from(rowEl.querySelectorAll('input[name*="serial_components"]')).some(inp => (inp.value || '').trim());
                    if (existingFilled) return;

                    // Lấy serial chính từ input (có thể rỗng nếu là N/A)
                    const mainSerialInput = rowEl.querySelector('input[name*="serial_main"]');
                    const mainSerial = (mainSerialInput && mainSerialInput.value) ? mainSerialInput.value.trim() : '';
                    const productId = rowEl.getAttribute('data-product-id') || '';
                    
                    // Tìm serial gốc từ productInfo để lấy materials đúng
                    const currentType = getCurrentModalType();
                    let originalSerial = mainSerial;
                    if (currentType === 'contract') {
                        const productInfo = selectedContractProducts.find(p => p.id == productId);
                        if (productInfo && Array.isArray(productInfo.serial_numbers)) {
                            const rowIndex = parseInt(rowEl.getAttribute('data-row-index') || '0');
                            originalSerial = productInfo.serial_numbers[rowIndex] || mainSerial;
                        }
                    } else if (currentType === 'backup') {
                        const productInfo = selectedBackupProducts.find(p => p.id == productId);
                        if (productInfo && Array.isArray(productInfo.serial_numbers)) {
                            const rowIndex = parseInt(rowEl.getAttribute('data-row-index') || '0');
                            originalSerial = productInfo.serial_numbers[rowIndex] || mainSerial;
                        }
                    }
                    
                    const materials = getProductMaterialsCountBySerial(parseInt(productId, 10) || 0, originalSerial);
                    
                    if (!Array.isArray(materials) || materials.length === 0) {
                        return; // Không có vật tư để fill
                    }
                    
                    // Thử lấy serial vật tư từ device_codes trước
                    const deviceCodes = window.deviceCodesData?.[currentType] || [];
                    const productDeviceCodes = deviceCodes.filter(dc => dc.product_id == productId);
                    const rowIndex = parseInt(rowEl.getAttribute('data-row-index') || '0');
                    const deviceCode = productDeviceCodes[rowIndex];
                    
                    if (deviceCode && deviceCode.serial_components) {
                        let componentSerials = [];
                        try {
                            if (typeof deviceCode.serial_components === 'string') {
                                componentSerials = JSON.parse(deviceCode.serial_components);
                            } else if (Array.isArray(deviceCode.serial_components)) {
                                componentSerials = deviceCode.serial_components;
                            }
                        } catch (e) {
                            console.warn('Error parsing serial_components:', e);
                        }
                        
                        if (Array.isArray(componentSerials) && componentSerials.length > 0) {
                            const componentInputs = Array.from(rowEl.querySelectorAll('input[name*="serial_components"]'));
                            componentInputs.forEach((input, index) => {
                                // Ưu tiên hiển thị serial đổi tên (device_codes) > serial gốc (prefill)
                                const renamed = componentSerials[index];
                                if (renamed && input.value !== renamed) {
                                    input.value = renamed;
                                    input.removeAttribute('data-prefill');
                                }
                            });
                            return; // Đã fill xong, không cần gọi API
                        }
                    }
                    
                    // Nếu không có trong device_codes, chỉ gọi API khi có mainSerial (serial chính)
                    if (mainSerial) {
                        const warehouseId = document.querySelector('select[name="warehouse_id"]')?.value;
                        if (!warehouseId) return;
                        
                        const response = await fetch(`/api/device-info/${encodeURIComponent(mainSerial)}?product_id=${productId}&expected_count=${materials.length}&warehouse_id=${warehouseId}`);
                        const data = await response.json();
                        
                        if (data.success && data.data && data.data.componentSerials) {
                            const componentSerials = data.data.componentSerials;
                            const componentInputs = Array.from(rowEl.querySelectorAll('input[name*="serial_components"]'));
                            
                            // Fill serial vật tư vào inputs theo thứ tự
                            componentInputs.forEach((input, index) => {
                                if (!input.value && componentSerials[index]) {
                                    input.value = componentSerials[index];
                                }
                            });
                        }
                    }
                } catch (e) {
                    console.warn('fetchAndFillComponentSerials error', e);
                }
            }

            // Attach listeners to auto-fill component serials when main serial changes
            function attachSerialMainListeners(tbodyEl) {
                if (!tbodyEl) return;
                const rows = tbodyEl.querySelectorAll('tr[data-product-id]');
                rows.forEach(row => {
                    const input = row.querySelector('input[name*="serial_main"]');
                    if (!input) return;
                    if (input.getAttribute('data-listener-attached') === '1') return;
                    input.setAttribute('data-listener-attached', '1');

                    const handler = () => fetchAndFillComponentSerials(row);
                    input.addEventListener('change', handler);
                    input.addEventListener('blur', handler);

                    // Also react to typing with a small debounce so users don't have to blur
                    let debounceTimer;
                    input.addEventListener('input', () => {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(handler, 500);
                    });

                    if ((input.value || '').trim()) {
                        fetchAndFillComponentSerials(row);
                    }
                });
            }

            // Hàm hiển thị bảng mã thiết bị với dữ liệu từ database
            function renderDeviceCodeTable(deviceCodes, type) {
                const tbody = document.getElementById('device-code-tbody');
                tbody.innerHTML = '';

                // Luôn hiển thị tất cả sản phẩm, kể cả khi không có device codes
                // Điều này đảm bảo không mất sản phẩm nào
                let productsToShow = [];
                if (type === 'contract') {
                    productsToShow = selectedContractProducts;
                } else if (type === 'backup') {
                    productsToShow = selectedBackupProducts;
                }

                if (productsToShow.length === 0) {
                    tbody.innerHTML =
                        `<tr><td colspan="8" class="px-4 py-3 text-center">Chưa có sản phẩm nào được chọn</td></tr>`;
                    return;
                }

                // Nếu không có device codes, vẫn render nhưng không có dữ liệu device codes
                // (sẽ dùng logic assembly_materials thay vì product.serial_components)

                // Nhóm device codes theo product_id để hiển thị
                const groupedDeviceCodes = {};
                
                deviceCodes.forEach(deviceCode => {
                    if (!groupedDeviceCodes[deviceCode.product_id]) {
                        groupedDeviceCodes[deviceCode.product_id] = [];
                    }
                    groupedDeviceCodes[deviceCode.product_id].push(deviceCode);
                });

                // Đảm bảo tất cả sản phẩm đều được hiển thị, kể cả khi không có device codes
                productsToShow.forEach(product => {
                    if (!groupedDeviceCodes[product.id]) {
                        groupedDeviceCodes[product.id] = [];
                    }
                });

                // Render từng nhóm product
                Object.keys(groupedDeviceCodes).forEach(productId => {
                    const productDeviceCodes = groupedDeviceCodes[productId];

                    // Find product info from selected products
                    let productInfo = null;
                    let productQuantity = 0;
                    let componentsPerProduct = 0;

                    if (type === 'contract') {
                        productInfo = selectedContractProducts.find(p => p.id == productId);
                        if (productInfo) {
                            productQuantity = productInfo.quantity;
                            // Fetch components count from API or use a default
                            componentsPerProduct = productInfo.type === 'product' ?
                                (productInfo.components_count || 3) : 0; // Default to 3 if not specified
                        }
                    } else if (type === 'backup') {
                        productInfo = selectedBackupProducts.find(p => p.id == productId);
                        if (productInfo) {
                            productQuantity = productInfo.quantity;
                            // Fetch components count from API or use a default
                            componentsPerProduct = productInfo.type === 'product' ?
                                (productInfo.components_count || 3) : 0; // Default to 3 if not specified
                        }
                    }

                    if (!productInfo) {
                        console.warn(`Product ${productId} not found in selected products for type ${type}`);
                        return;
                    }

                    // Create a single row for the product
                    const row = document.createElement('tr');
                    row.setAttribute('data-product-id', productId);

                    // Create main cell content
                    const mainCellContent = `
                        <td class="px-2 py-2 border border-gray-200" rowspan="${productQuantity || 1}">
                            <input type="text" 
                                name="${type}_product_info[${productId}]" 
                                value="${productInfo.code} - ${productInfo.name}"
                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                readonly>
                        </td>`;

                    // Create serial fields based on quantity
                    let serialFieldsHtml = '';
                    for (let i = 0; i < productQuantity; i++) {
                        // Tìm device code chính xác cho sản phẩm này và index này
                        // Sử dụng index để lấy đúng device code cho từng serial
                        const deviceCode = productDeviceCodes[i] || {};
                        
                        // Ưu tiên serial_main từ database (serial mới được đổi tên)
                        // Nếu không có serial_main, mới dùng serial từ giao diện chính
                        let mainSerialValue = '';
                        
                        // Chỉ hiển thị serial khi thực sự có device code cho index này
                        if (deviceCode.id && deviceCode.serial_main) {
                            mainSerialValue = deviceCode.serial_main; // Ưu tiên serial_main từ database
                        } else {
                            // Fallback: đọc serial hiện tại từ DOM
                            const serialSelect = document.querySelector(`select[name*="${type}_items"][name*="serial_numbers"][data-item-id="${productId}"][data-serial-index="${i}"]`);
                            if (serialSelect && serialSelect.value) {
                                mainSerialValue = serialSelect.value;
                        } else if (productInfo.serial_numbers && productInfo.serial_numbers[i]) {
                                mainSerialValue = productInfo.serial_numbers[i]; // Fallback cuối cùng
                            }
                        }

                        // For first row, include the product info cell
                        const firstRowExtra = i === 0 ? mainCellContent : '';

                        // Create component serial fields only if product has materials
                        let componentSerialsHtml = '';
                        // Lấy materials theo serial cụ thể thay vì theo productId
                        // Nếu có device_codes (serial đã đổi tên), dùng serial gốc để tìm materials
                        const originalSerialValue = Array.isArray(productInfo.serial_numbers) ? (productInfo.serial_numbers[i] ?? '') : '';
                        const currentSerialValue = (deviceCode && deviceCode.serial_main) ? deviceCode.serial_main : originalSerialValue;
                        // Lấy cặp assembly_id và product_unit đúng theo index để map vật tư
                        const assemblyIdsArr = String(productInfo.assembly_id || '').split(',').map(s => s.trim());
                        const productUnitsArr = String(productInfo.product_unit || '').split(',').map(s => s.trim());
                        const assemblyIdForRow = assemblyIdsArr[i] !== undefined ? assemblyIdsArr[i] : '';
                        const productUnitForRow = productUnitsArr[i] !== undefined ? productUnitsArr[i] : '';
                        // Ưu tiên tra theo cặp assembly_id:product_unit, sau đó theo serial/index
                        const materials = getMaterialsForRow(productId, originalSerialValue, i, assemblyIdForRow, productUnitForRow);
                        if (productInfo.type === 'product' && materials && materials.length > 0) {
                            // Ưu tiên serial đổi tên từ device_codes nếu có cho đúng index
                            let renamedComponentSerials = [];
                            if (deviceCode && deviceCode.serial_components) {
                                try {
                                    if (typeof deviceCode.serial_components === 'string') {
                                        // Handle possible double-encoded JSON
                                        let first = JSON.parse(deviceCode.serial_components);
                                        if (typeof first === 'string') {
                                            renamedComponentSerials = JSON.parse(first) || [];
                                        } else if (Array.isArray(first)) {
                                            renamedComponentSerials = first;
                                        }
                                    } else if (Array.isArray(deviceCode.serial_components)) {
                                        renamedComponentSerials = deviceCode.serial_components;
                                    }
                                } catch (e) {
                                    console.warn('render parse serial_components failed', e);
                                    renamedComponentSerials = [];
                                }
                            }

                            componentSerialsHtml = materials.map((material, j) => {
                                let serialValue = '';
                                // 1) Dùng serial đổi tên nếu có
                                if (Array.isArray(renamedComponentSerials) && renamedComponentSerials[j]) {
                                    serialValue = renamedComponentSerials[j];
                                } else {
                                    // 2) Fallback: tách serial gốc theo nhiều ký tự phân tách
                                    if (material.serial && material.serial !== 'null') {
                                        const parts = String(material.serial)
                                            .split(/[\s,;|\/]+/)
                                            .map(s => s.trim())
                                            .filter(Boolean);
                                        const partIndex = (parseInt(material.index, 10) || (j + 1)) - 1;
                                        serialValue = parts[partIndex] || parts[0] || '';
                                    }
                                }
                                const prefillAttr = serialValue && !(Array.isArray(renamedComponentSerials) && renamedComponentSerials[j]) ? 'data-prefill="1"' : '';
                                return `
                                <div class="mb-1">
                                    <label class="text-xs text-gray-600 mb-1">${material.material_code} - ${material.material_name} (${material.index})</label>
                                    <input type="text" 
                                        name="${type}_serial_components[${productId}][${i}][${j}]" 
                                        placeholder="Seri vật tư"
                                        value="${serialValue}"
                                        ${prefillAttr}
                                        data-material-id="${material.material_id}"
                                        data-material-index="${material.index}"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </div>
                            `;
                            }).join('');
                        }

                        // Create row HTML
                        const rowHtml = `
                            <tr data-product-id="${productId}" data-row-index="${i}" data-device-code-id="${deviceCode.id || ''}">
                                ${firstRowExtra}
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${type}_serial_main[${productId}][${i}]" 
                                        placeholder="Seri chính ${i + 1}"
                                        value="${mainSerialValue}"
                                        data-sync-index="${i}"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <div class="flex flex-col space-y-1">
                                        ${componentSerialsHtml}
                                    </div>
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${type}_serial_sim[${productId}][${i}]" 
                                        value="${deviceCode.id ? (deviceCode.serial_sim || '') : ''}"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${type}_access_code[${productId}][${i}]" 
                                        value="${deviceCode.id ? (deviceCode.access_code || '') : ''}"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${type}_iot_id[${productId}][${i}]" 
                                        value="${deviceCode.id ? (deviceCode.iot_id || '') : ''}"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${type}_mac_4g[${productId}][${i}]" 
                                        value="${deviceCode.id ? (deviceCode.mac_4g || '') : ''}"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2 border border-gray-200">
                                    <input type="text" 
                                        name="${type}_note[${productId}][${i}]" 
                                        value="${deviceCode.id ? (deviceCode.note || '') : ''}"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                            </tr>
                        `;

                        serialFieldsHtml += rowHtml;
                    }

                    // Add all rows to tbody
                    tbody.insertAdjacentHTML('beforeend', serialFieldsHtml);
                    attachSerialMainListeners(tbody);
                    // Delay để đảm bảo DOM đã render hoàn toàn
                    setTimeout(() => {
                        runAutofillForAllRows();
                    }, 200);
                });
            }

            // Hàm renderNewDeviceCodeTable đã được thay thế bằng renderDeviceCodeTable

            // Đóng modal
            if (closeDeviceCodeModalBtn) {
                closeDeviceCodeModalBtn.addEventListener('click', function() {
                    deviceCodeModal.classList.add('hidden');
                    // Reset active class và biến global
                    updateContractDeviceCodesBtn.classList.remove('active');
                    updateBackupDeviceCodesBtn.classList.remove('active');
                    window.currentDeviceCodeType = '';
                    // Vô hiệu hóa sync khi đóng modal
                    disableAllSync();
                });
            }

            if (cancelDeviceCodesBtn) {
                cancelDeviceCodesBtn.addEventListener('click', function() {
                    deviceCodeModal.classList.add('hidden');
                    // Reset active class và biến global
                    updateContractDeviceCodesBtn.classList.remove('active');
                    updateBackupDeviceCodesBtn.classList.remove('active');
                    window.currentDeviceCodeType = '';
                    // Vô hiệu hóa sync khi đóng modal
                    disableAllSync();
                });
            }

            // Lưu thông tin mã thiết bị
            if (saveDeviceCodesBtn) {
                saveDeviceCodesBtn.addEventListener('click', async function() {
                    try {
                        // Hiển thị trạng thái loading
                        saveDeviceCodesBtn.disabled = true;
                        saveDeviceCodesBtn.innerHTML =
                            '<i class="fas fa-spinner fa-spin mr-2"></i> Đang lưu...';

                        const tbody = document.getElementById('device-code-tbody');
                        const rows = tbody.querySelectorAll('tr');
                        const deviceCodes = [];

                        rows.forEach(row => {
                            // Skip empty message row
                            if (row.querySelector('td[colspan]')) {
                                return;
                            }

                            // Get product ID from the data attribute
                            const productId = row.getAttribute('data-product-id');
                            if (!productId) {
                                console.warn('Row missing product ID, skipping...');
                                return;
                            }

                            // Get device code ID if exists (for update)
                            const deviceCodeId = row.getAttribute('data-device-code-id');

                            // Get main serial
                            const mainSerialInput = row.querySelector(
                                'input[name*="serial_main"]');
                            if (!mainSerialInput || !mainSerialInput.value) {
                                return;
                            }

                            // Get component serials - giữ lại thứ tự và bao gồm cả giá trị trống
                            const componentSerialInputs = row.querySelectorAll(
                                'input[name*="serial_components"]');
                            const componentSerials = Array.from(componentSerialInputs)
                                .map(input => {
                                    const value = input.value.trim();
                                    // Chỉ loại bỏ giá trị '[' (invalid), giữ lại chuỗi trống
                                    return value === '[' ? '' : value;
                                });

                            // Convert componentSerials to JSON string for database storage
                            const componentSerialsJson = componentSerials.length > 0 ? JSON.stringify(componentSerials) : null;

                            // Get other fields
                            const simSerial = row.querySelector('input[name*="serial_sim"]')
                                ?.value || '';
                            const accessCode = row.querySelector('input[name*="access_code"]')
                                ?.value || '';
                            const iotId = row.querySelector('input[name*="iot_id"]')?.value ||
                                '';
                            const mac4g = row.querySelector('input[name*="mac_4g"]')?.value ||
                                '';
                            const note = row.querySelector('input[name*="note"]')?.value || '';

                            // Create device code entry
                            deviceCodes.push({
                                id: deviceCodeId,
                                dispatch_id: dispatchId,
                                product_id: productId,
                                serial_main: mainSerialInput.value,
                                serial_components: componentSerialsJson,
                                serial_sim: simSerial,
                                access_code: accessCode,
                                iot_id: iotId,
                                mac_4g: mac4g,
                                note: note
                            });
                        });

                        if (deviceCodes.length === 0) {
                            throw new Error('Không có thông tin mã thiết bị nào để lưu!');
                        }

                        // Gửi dữ liệu lên server
                        const response = await fetch('/api/device-codes/save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]')?.content
                            },
                            body: JSON.stringify({
                                dispatch_id: dispatchId,
                                device_codes: deviceCodes,
                                type: currentDeviceCodeType // Add type to request
                            })
                        });

                        const result = await response.json();

                        if (!result.success) {
                            throw new Error(result.message || 'Lỗi khi lưu thông tin');
                        }

                        // Lưu trữ serials từ modal trước khi đóng
                        const modalSerialInputs = document.querySelectorAll('#device-code-modal input[name*="serial_main"]');
                        const modalSerials = Array.from(modalSerialInputs).map(input => input.value).filter(Boolean);
                        
                        // Làm mới dữ liệu ngay sau khi lưu để hiển thị tên mới tức thì
                        try {
                            const type = getCurrentModalType();
                            // Tải lại device codes từ DB để có serial vừa đổi tên
                            await loadDeviceCodesFromDatabase(type);
                            // Áp lại serial về giao diện chính theo mapping (ưu tiên tên mới)
                            updateMainInterfaceSerials(type);
                        } catch (e) {
                            console.warn('Post-save refresh error:', e);
                        }

                        // Kích hoạt sync ngắn để đồng bộ lựa chọn vào dropdown chính
                        enableSync();
                        syncFromModalToMain();
                        disableAllSync();

                        // Làm mới options và ép display đúng giá trị
                        if (typeof loadAvailableSerials === 'function') {
                            await loadAvailableSerials();
                        }
                        if (typeof enforceSelectedSerials === 'function') {
                            enforceSelectedSerials();
                        }
                        
                        // Load lại device codes từ database để đảm bảo dữ liệu mới nhất
                        setTimeout(async () => {
                            const currentType = getCurrentModalType();
                            
                            try {
                                // Load lại device codes từ database
                                await loadDeviceCodesFromDatabase(currentType);
                                
                                // Cập nhật lại giao diện chính
                                updateMainInterfaceSerials(currentType);
                                
                                // Cập nhật lại window.deviceCodesData để modal hiển thị đúng
                                const response = await fetch(`/api/device-codes/${dispatchId}?type=${currentType}`);
                                const data = await response.json();
                                if (data.success) {
                                    window.deviceCodesData = window.deviceCodesData || {};
                                    window.deviceCodesData[currentType] = data.deviceCodes;
                                }
                            } catch (error) {
                                console.error(`Error reloading device codes for type ${currentType}:`, error);
                            }
                            
                            // Vô hiệu hóa sync sau khi hoàn tất
                            disableAllSync();
                        }, 500);

                        alert('Đã lưu thông tin mã thiết bị thành công!');
                        deviceCodeModal.classList.add('hidden');

                    } catch (error) {
                        console.error('Error saving device codes:', error);
                        alert('Có lỗi xảy ra khi lưu thông tin: ' + error.message);
                    } finally {
                        // Reset button state
                        saveDeviceCodesBtn.disabled = false;
                        saveDeviceCodesBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Lưu thông tin';
                    }
                });
            }

            // Import Excel
            if (importDeviceCodesBtn) {
                importDeviceCodesBtn.addEventListener('click', function() {
                    const fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.accept = '.xlsx,.xls';
                    fileInput.style.display = 'none';

                    fileInput.addEventListener('change', async function(e) {
                        const file = e.target.files[0];
                        if (!file) return;

                        const formData = new FormData();
                        formData.append('file', file);
                        formData.append('dispatch_id', dispatchId);
                        formData.append('type', currentDeviceCodeType);

                        try {
                            // Disable import button and show loading state
                            importDeviceCodesBtn.disabled = true;
                            importDeviceCodesBtn.innerHTML =
                                '<i class="fas fa-spinner fa-spin mr-2"></i> Đang import...';

                            const response = await fetch('/api/device-codes/import', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')?.content
                                }
                            });

                            const result = await response.json();

                            if (!result.success) {
                                throw new Error(result.message || 'Lỗi khi import file');
                            }

                            // Get all rows in the table
                            const tbody = document.getElementById('device-code-tbody');
                            const rows = tbody.querySelectorAll('tr');

                            // Clear all input values in existing rows first
                            rows.forEach(row => {
                                const inputs = row.querySelectorAll('input');
                                inputs.forEach(input => {
                                    if (!input.name.includes(
                                            'product_info'
                                        )) { // Don't clear product info
                                        input.value = '';
                                    }
                                });
                            });

                            // Update rows with imported data
                            if (result.data && result.data.length > 0) {
                                result.data.forEach(item => {
                                    // Find matching row by product ID
                                    const row = tbody.querySelector(
                                        `tr[data-product-id="${item.product_id}"]`);
                                    if (row) {
                                        // Update fields with imported data
                                        const serialMainInput = row.querySelector(
                                            `input[name*="serial_main"]`);
                                        if (serialMainInput) {
                                            serialMainInput.value = item.serial_main ||
                                                '';
                                        }

                                        // Update component serials
                                        if (item.serial_components && item.serial_components !== 'null' && item.serial_components !== '[]') {
                                            let componentSerials = [];
                                            
                                            // Parse serial_components nếu là string
                                            if (typeof item.serial_components === 'string') {
                                                try {
                                                    // Xử lý double-encoded JSON: "[\"1\",\"2\",\"3\"]" -> ["1","2","3"]
                                                    let jsonString = item.serial_components;
                                                    
                                                    // Nếu có escape characters, decode 2 lần
                                                    if (jsonString.includes('\\"')) {
                                                        // Parse lần đầu để lấy JSON string
                                                        const firstParse = JSON.parse(jsonString);
                                                        // Parse lần thứ 2 để lấy array
                                                        componentSerials = JSON.parse(firstParse);
                                                    } else {
                                                        // Parse trực tiếp nếu không có escape characters
                                                        componentSerials = JSON.parse(jsonString);
                                                    }
                                                } catch (e) {
                                                    console.error('Error parsing serial_components during import:', e);
                                                    componentSerials = [];
                                                }
                                            } else if (Array.isArray(item.serial_components)) {
                                                componentSerials = item.serial_components;
                                            }
                                            
                                            if (componentSerials.length > 0) {
                                                const componentInputs = row
                                                    .querySelectorAll(
                                                        `input[name*="serial_components"]`);
                                                if (componentInputs.length > 0) {
                                                    componentSerials.forEach((serial, index) => {
                                                        if (componentInputs[index]) {
                                                            componentInputs[index].value = serial;
                                                        }
                                                    });
                                                }
                                            }
                                        }

                                        // Update other fields
                                        const fields = ['serial_sim', 'access_code',
                                            'iot_id', 'mac_4g', 'note'
                                        ];
                                        fields.forEach(field => {
                                            const input = row.querySelector(
                                                `input[name*="${field}"]`);
                                            if (input) {
                                                input.value = item[field] || '';
                                            }
                                        });
                                    }
                                });
                            }

                            alert('Import dữ liệu thành công!');

                        } catch (error) {
                            console.error('Error importing device codes:', error);
                            alert('Có lỗi xảy ra khi import: ' + error.message);
                        } finally {
                            // Reset import button state
                            importDeviceCodesBtn.disabled = false;
                            importDeviceCodesBtn.innerHTML =
                                '<i class="fas fa-file-import mr-2"></i> Import Excel';
                        }
                    });

                    fileInput.click();
                });
            }

            // Hàm lấy loại hiện tại của modal (contract/backup)
            function getCurrentModalType() {
                // Sử dụng biến global đã được set khi click button
                if (window.currentDeviceCodeType) {
                    return window.currentDeviceCodeType;
                }
                
                // Fallback: kiểm tra button nào đang active
                const contractBtn = document.getElementById('update_contract_device_codes_btn');
                const backupBtn = document.getElementById('update_backup_device_codes_btn');
                
                if (contractBtn && contractBtn.classList.contains('active')) {
                    return 'contract';
                } else if (backupBtn && backupBtn.classList.contains('active')) {
                    return 'backup';
                }
                
                // Fallback cuối cùng: kiểm tra biến local
                if (typeof currentDeviceCodeType !== 'undefined' && currentDeviceCodeType) {
                    return currentDeviceCodeType;
                }
                
                // Default fallback
                return 'contract';
            }

            // Hàm kiểm tra xem có cần sync không
            function shouldSync() {
                // Kiểm tra xem sync có được kích hoạt không
                if (!syncEnabled) {
                    return false;
                }
                
                // Chỉ sync khi được yêu cầu rõ ràng
                if (!syncRequested) {
                    return false;
                }
                
                // Chỉ sync khi thực sự cần thiết
                const modal = document.getElementById('device-code-modal');
                if (!modal || modal.classList.contains('hidden')) {
                    return false;
                }
                
                // Kiểm tra xem có input nào đã thay đổi không
                const changedInputs = modal.querySelectorAll('input[data-changed="true"]');
                const hasChanges = changedInputs.length > 0;
                
                return hasChanges;
            }

            // Hàm đồng bộ serial numbers giữa giao diện chính và modal
            function syncSerialNumbers() {
                // Kiểm tra xem có cần sync không
                if (!shouldSync()) {
                    return;
                }
                
                // Lấy loại hiện tại của modal
                const currentType = getCurrentModalType();
                
                // Debug: Kiểm tra tất cả select elements và data-type
                const allSerialSelects = document.querySelectorAll('select[name*="serial_numbers"]');
                allSerialSelects.forEach((select, index) => {
                });
                
                // Đồng bộ từ giao diện chính sang modal
                syncFromMainToModal(currentType);
                
                // Đồng bộ từ modal sang giao diện chính
                syncFromModalToMain(currentType);
                
                // Reset data-changed flags sau khi sync
                const changedInputs = document.querySelectorAll('#device-code-modal input[data-changed="true"]');
                changedInputs.forEach(input => input.removeAttribute('data-changed'));
            }

            // Hàm vô hiệu hóa tất cả sync operations
            function disableAllSync() {
                // Các listener được khai báo bằng hàm ẩn danh nên không thể gỡ bỏ trực tiếp.
                // Dùng cờ để vô hiệu hóa hoàn toàn hành vi đồng bộ.
                syncEnabled = false;
                syncRequested = false;
            }

            // Hàm kích hoạt sync operations
            function enableSync() {
                syncEnabled = true;
                syncRequested = true;
            }

            // Hàm vô hiệu hóa sync operations
            function disableSync() {
                syncEnabled = false;
                syncRequested = false;
            }

            // Đồng bộ từ giao diện chính sang modal
            function syncFromMainToModal(type) {
                // KIỂM TRA GLOBAL SYNC CONTROL TRƯỚC
                if (!syncEnabled) {
                    return;
                }
                
                // Chỉ sync với serial numbers của loại tương ứng
                let mainSerialSelects = document.querySelectorAll(`select[name*="serial_numbers"][data-type="${type}"]`);
                const modalSerialInputs = document.querySelectorAll('#device-code-modal input[name*="serial_main"]');
                
                // Nếu không tìm thấy select elements với data-type, thử tìm theo name pattern
                if (mainSerialSelects.length === 0) {
                    const namePatternSelects = document.querySelectorAll(`select[name*="${type}_items"][name*="serial_numbers"]`);
                    
                    if (namePatternSelects.length > 0) {
                        // Sử dụng name pattern selects
                        mainSerialSelects = namePatternSelects;
                    }
                }
                
                // Tạo mapping giữa main serials và modal serials
                const mainSerials = Array.from(mainSerialSelects).map(select => select.value).filter(Boolean);
                
                modalSerialInputs.forEach((input, index) => {
                    if (mainSerials[index]) {
                        input.value = mainSerials[index];
                    }
                });
            }

            // Đồng bộ từ modal sang giao diện chính
            function syncFromModalToMain(type) {
                // KIỂM TRA GLOBAL SYNC CONTROL TRƯỚC
                if (!syncEnabled) {
                    return;
                }
                
                const modalSerialInputs = document.querySelectorAll('#device-code-modal input[name*="serial_main"]');
                
                // Debug: Kiểm tra tất cả select elements trước khi filter
                const allSerialSelects = document.querySelectorAll('select[name*="serial_numbers"]');
                allSerialSelects.forEach((select, index) => {
                });
                
                // Chỉ sync với serial numbers của loại tương ứng
                const mainSerialSelects = document.querySelectorAll(`select[name*="serial_numbers"][data-type="${type}"]`);
                
                
                // Nếu không tìm thấy select elements với data-type, thử tìm theo name pattern
                if (mainSerialSelects.length === 0) {
                    const namePatternSelects = document.querySelectorAll(`select[name*="${type}_items"][name*="serial_numbers"]`);
                    
                    if (namePatternSelects.length > 0) {
                        // Sử dụng name pattern selects
                        mainSerialSelects = namePatternSelects;
                    }
                }
                
                // Tạo mapping giữa modal serials và main serials
                const modalSerials = Array.from(modalSerialInputs).map(input => input.value).filter(Boolean);
                
                mainSerialSelects.forEach((select, index) => {
                    if (modalSerials[index]) {
                        const modalSerial = modalSerials[index];
                        
                        // Tìm option tương ứng trong select
                        const option = Array.from(select.options).find(opt => opt.value === modalSerial);
                        
                        if (option) {
                            select.value = modalSerial;
                        } else {
                            // Nếu không tìm thấy option, tạo mới
                            const newOption = document.createElement('option');
                            newOption.value = modalSerial;
                            newOption.textContent = modalSerial;
                            select.appendChild(newOption);
                            select.value = modalSerial;
                        }
                        
                        // Trigger change event để các listeners khác biết
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
                
            }

            // Thêm event listeners cho đồng bộ hóa
            function addSerialSyncListeners() {
                // Lắng nghe thay đổi trong giao diện chính
                document.addEventListener('change', function(e) {
                    if (e.target.matches('select[name*="serial_numbers"]')) {
                        // Đồng bộ sang modal nếu modal đang mở
                        if (!document.getElementById('device-code-modal').classList.contains('hidden')) {
                            const currentType = getCurrentModalType();
                            // KIỂM TRA GLOBAL SYNC CONTROL TRƯỚC
                            if (syncEnabled) {
                                syncFromMainToModal(currentType);
                            } else {
                                console.log('Global sync is disabled, skipping main interface sync');
                            }
                        }
                    }
                });

                // Lắng nghe thay đổi trong modal - CHỈ sync khi blur (mất focus), không sync real-time
                document.addEventListener('blur', function(e) {
                    if (e.target.matches('#device-code-modal input[name*="serial_main"]')) {
                        // Chỉ sync khi user hoàn thành việc nhập (blur event)
                        const currentType = getCurrentModalType();
                        // KHÔNG sync ngay, chỉ đánh dấu là có thay đổi
                        e.target.setAttribute('data-changed', 'true');
                    }
                }, true);
                
                // Lắng nghe thay đổi serial vật tư trong modal - CHỈ sync khi blur
                document.addEventListener('blur', function(e) {
                    if (e.target.matches('#device-code-modal input[name*="serial_components"]')) {
                        // Chỉ sync khi user hoàn thành việc nhập (blur event)
                        e.target.setAttribute('data-changed', 'true');
                    }
                }, true);
                
                // Lắng nghe thay đổi serial chính trong modal - CHỈ sync khi change (enter hoặc select)
                document.addEventListener('change', function(e) {
                    if (e.target.matches('#device-code-modal input[name*="serial_main"]')) {
                        // Chỉ sync khi user thực sự thay đổi (change event)
                        const currentType = getCurrentModalType();
                        // KHÔNG sync ngay, chỉ đánh dấu là có thay đổi
                        e.target.setAttribute('data-changed', 'true');
                    }
                });
            }

            // Hàm refresh giao diện chính để hiển thị serial mới
            function refreshMainInterface() {
                const currentType = getCurrentModalType();
                
                // Lấy tất cả serial chính từ modal đã lưu
                const modalSerialInputs = document.querySelectorAll('#device-code-modal input[name*="serial_main"]');
                const modalSerials = Array.from(modalSerialInputs).map(input => input.value).filter(Boolean);
                
                
                // Cập nhật từng select trong giao diện chính (chỉ loại tương ứng)
                let mainSerialSelects = document.querySelectorAll(`select[name*="serial_numbers"][data-type="${currentType}"]`);
                
                // Nếu không tìm thấy select elements với data-type, thử tìm theo name pattern
                if (mainSerialSelects.length === 0) {
                    const namePatternSelects = document.querySelectorAll(`select[name*="${currentType}_items"][name*="serial_numbers"]`);
                    
                    if (namePatternSelects.length > 0) {
                        // Sử dụng name pattern selects
                        mainSerialSelects = namePatternSelects;
                    }
                }
                
                mainSerialSelects.forEach((select, index) => {
                    if (modalSerials[index]) {
                        const modalSerial = modalSerials[index];
                        
                        // Tìm hoặc tạo option mới
                        let option = Array.from(select.options).find(opt => opt.value === modalSerial);
                        if (!option) {
                            option = document.createElement('option');
                            option.value = modalSerial;
                            option.textContent = modalSerial;
                            select.appendChild(option);
                        }
                        
                        // Cập nhật giá trị
                        select.value = modalSerial;
                        
                        // Trigger change event
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            }

            // Hàm validate tính nhất quán của serial numbers
            function validateSerialConsistency() {
                const currentType = getCurrentModalType();
                // Chỉ kiểm tra với serial numbers của loại tương ứng
                let mainSerialSelects = document.querySelectorAll(`select[name*="serial_numbers"][data-type="${currentType}"]`);
                const modalSerialInputs = document.querySelectorAll('#device-code-modal input[name*="serial_main"]');
                
                // Nếu không tìm thấy select elements với data-type, thử tìm theo name pattern
                if (mainSerialSelects.length === 0) {
                    const namePatternSelects = document.querySelectorAll(`select[name*="${currentType}_items"][name*="serial_numbers"]`);
                    
                    if (namePatternSelects.length > 0) {
                        // Sử dụng name pattern selects
                        mainSerialSelects = namePatternSelects;
                    }
                }
                
                const mainSerials = Array.from(mainSerialSelects).map(select => select.value).filter(Boolean);
                const modalSerials = Array.from(modalSerialInputs).map(input => input.value).filter(Boolean);
                
                // Kiểm tra xem có sự khác biệt không
                const inconsistencies = [];
                mainSerials.forEach((serial, index) => {
                    if (modalSerials[index] && serial !== modalSerials[index]) {
                        inconsistencies.push({
                            index: index + 1,
                            main: serial,
                            modal: modalSerials[index]
                        });
                    }
                });
                
                if (inconsistencies.length > 0) {
                    console.warn(`Serial number inconsistencies detected for type ${currentType}:`, inconsistencies);
                    return false;
                }
                
                return true;
            }

            // Hàm hiển thị cảnh báo về sự không nhất quán
            function showInconsistencyWarning() {
                const currentType = getCurrentModalType();
                const typeText = currentType === 'contract' ? 'hợp đồng' : 'dự phòng';
                
                // Xóa cảnh báo cũ
                const oldWarning = document.querySelector('.serial-inconsistency-warning');
                if (oldWarning) {
                    oldWarning.remove();
                }

                const warningDiv = document.createElement('div');
                warningDiv.className = 'serial-inconsistency-warning bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4';
                warningDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Cảnh báo:</strong>
                    </div>
                    <p class="mt-2">Số Serial trong giao diện chính và modal cập nhật mã thiết bị <strong>${typeText}</strong> không khớp nhau. 
                    Vui lòng đồng bộ lại để đảm bảo tính nhất quán.</p>
                    <button type="button" onclick="syncSerialNumbers()" class="mt-2 bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-sync-alt mr-1"></i> Đồng bộ ngay
                    </button>
                `;

                // Thêm vào đầu form
                const form = document.querySelector('form');
                form.insertBefore(warningDiv, form.firstChild);
            }

            // Khởi tạo đồng bộ hóa khi trang load
            document.addEventListener('DOMContentLoaded', function() {
                addSerialSyncListeners();
                
                // Kiểm tra tính nhất quán định kỳ
                setInterval(() => {
                    if (!document.getElementById('device-code-modal').classList.contains('hidden')) {
                        if (!validateSerialConsistency()) {
                            showInconsistencyWarning();
                        }
                    }
                }, 5000); // Kiểm tra mỗi 5 giây
            });
        });
    </script>
</body>

</html>
