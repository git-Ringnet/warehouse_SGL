<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chỉnh sửa phiếu lắp ráp - SGL</title>
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
                <a href="{{ route('assemblies.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu lắp ráp</h1>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Mã phiếu: <span
                        class="font-medium">{{ $assembly->code }}</span></span>
                <span
                    class="bg-{{ $assembly->status == 'completed' ? 'green' : ($assembly->status == 'in_progress' ? 'yellow' : ($assembly->status == 'cancelled' ? 'red' : 'blue')) }}-100 text-{{ $assembly->status == 'completed' ? 'green' : ($assembly->status == 'in_progress' ? 'yellow' : ($assembly->status == 'cancelled' ? 'red' : 'blue')) }}-800 text-xs px-2 py-1 rounded-full capitalize">
                    {{ $assembly->status == 'in_progress' ? 'Đang thực hiện' : ($assembly->status == 'completed' ? 'Hoàn thành' : ($assembly->status == 'cancelled' ? 'Đã hủy' : 'Chờ xử lý')) }}
                </span>
            </div>
        </header>

        <main class="p-6">
            <form action="{{ route('assemblies.update', $assembly->id) }}" method="POST">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="mb-4 bg-red-50 p-4 rounded-lg border border-red-200">
                        <div class="text-red-600 font-medium mb-2">Có lỗi xảy ra:</div>
                        <ul class="list-disc pl-5 text-red-500">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Thông tin phiếu lắp ráp -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tools text-blue-500 mr-2"></i>
                        Thông tin phiếu lắp ráp
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="assembly_code" class="block text-sm font-medium text-gray-700 mb-1">Mã phiếu lắp
                                ráp</label>
                            <div class="flex items-center space-x-2">
                                <input type="text" id="assembly_code" name="assembly_code"
                                    value="{{ $assembly->code }}" readonly
                                    class="w-full border border-gray-300 bg-gray-50 rounded-lg px-3 py-2">
                            </div>
                        </div>
                        <div>
                            <label for="assembly_date"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày lắp ráp <span
                                    class="text-red-500">*</span></label>
                            <input type="date" id="assembly_date" name="assembly_date" value="{{ $assembly->date }}"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Thêm thành
                                phẩm mới</label>
                            <div>
                                <div class="relative flex space-x-2">
                                    <select id="product_id" name="product_id"
                                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">-- Chọn thành phẩm để thêm --</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                                                data-code="{{ $product->code }}">
                                                [{{ $product->code }}] {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="w-24">
                                        <input type="number" id="product_add_quantity" min="1" value="1"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Số lượng">
                                    </div>
                                    <button type="button" id="add_product_btn"
                                        class="px-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                        Thêm
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Chọn thành phẩm từ dropdown, nhập số lượng và nhấn "Thêm" để bổ sung thêm thành phẩm vào phiếu lắp ráp.</div>
                            </div>
                        </div>
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1 required">Người
                                phụ trách <span class="text-red-500">*</span></label>
                            <select id="assigned_to" name="assigned_to" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người phụ trách --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ ($assembly->assigned_employee_id ?? $assembly->assigned_to) == $employee->id || $assembly->assigned_to == $employee->name ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="tester_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người
                                tiếp nhận kiểm thử <span class="text-red-500">*</span></label>
                            <select id="tester_id" name="tester_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn người tiếp nhận kiểm thử --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ $assembly->tester_id == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1 required">Mục
                                đích <span class="text-red-500">*</span></label>
                            <select id="purpose" name="purpose" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="storage" {{ $assembly->purpose == 'storage' ? 'selected' : '' }}>Lưu
                                    kho</option>
                                <option value="project" {{ $assembly->purpose == 'project' ? 'selected' : '' }}>Xuất
                                    đi dự án</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1 required">Kho
                                xuất
                                <span class="text-red-500">*</span></label>
                            <select id="warehouse_id" name="warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho xuất linh kiện --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ $assembly->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} ({{ $warehouse->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="target_warehouse_container">
                            <label for="target_warehouse_id"
                                class="block text-sm font-medium text-gray-700 mb-1 required">Kho nhập
                                <span class="text-red-500">*</span></label>
                            <select id="target_warehouse_id" name="target_warehouse_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Chọn kho nhập thành phẩm --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ $assembly->target_warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} ({{ $warehouse->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="project_selection" class="{{ $assembly->purpose != 'project' ? 'hidden' : '' }}">
                            <div>
                                <label for="project_id"
                                    class="block text-sm font-medium text-gray-700 mb-1 required">Dự
                                    án <span class="text-red-500">*</span></label>
                                <select id="project_id" name="project_id"
                                    {{ $assembly->purpose == 'project' ? 'required' : '' }}
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Chọn dự án --</option>
                                    @foreach ($projects ?? [] as $project)
                                        <option value="{{ $project->id }}"
                                            {{ $assembly->project_id == $project->id ? 'selected' : '' }}>
                                            {{ $project->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div>
                            <label for="assembly_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi
                                chú</label>
                            <textarea id="assembly_note" name="assembly_note" rows="2"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $assembly->notes }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Danh sách linh kiện -->
                <!-- Thành phẩm đã thêm -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-box-open text-blue-500 mr-2"></i>
                        Thành phẩm đã thêm
                    </h2>

                    <!-- Bảng thành phẩm -->
                    <div class="overflow-x-auto mb-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên thành phẩm
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số lượng
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Serial
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Thao tác
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="product_list" class="bg-white divide-y divide-gray-200">
                                @if ($assembly->products && $assembly->products->count() > 0)
                                    <!-- Hiển thị products từ assembly_products table -->
                                    @foreach ($assembly->products as $index => $assemblyProduct)
                                        <tr class="product-row bg-white hover:bg-gray-50"
                                            data-product-id="{{ $assemblyProduct->product_id }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $assemblyProduct->product->code }}
                                                <input type="hidden" name="products[{{ $index }}][id]"
                                                    value="{{ $assemblyProduct->product_id }}">
                                                <input type="hidden" name="products[{{ $index }}][code]"
                                                    value="{{ $assemblyProduct->product->code }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $assemblyProduct->product->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                <input type="number" min="1"
                                                    name="products[{{ $index }}][quantity]"
                                                    value="{{ $assemblyProduct->quantity }}"
                                                    class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700">
                                                @if ($assemblyProduct->serials)
                                                    @php
                                                        $serials = explode(',', $assemblyProduct->serials);
                                                    @endphp
                                                    @if ($assemblyProduct->quantity > 1)
                                                        <div class="space-y-2">
                                                            @for ($i = 0; $i < $assemblyProduct->quantity; $i++)
                                                                <input type="text"
                                                                    name="products[{{ $index }}][serials][]"
                                                                    value="{{ $serials[$i] ?? '' }}"
                                                                    placeholder="Serial {{ $i + 1 }}"
                                                                    class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                            @endfor
                                                        </div>
                                                    @else
                                                        <input type="text"
                                                            name="products[{{ $index }}][serials][]"
                                                            value="{{ $serials[0] ?? '' }}" placeholder="Serial"
                                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    @endif
                                                @else
                                                    @if ($assemblyProduct->quantity > 1)
                                                        <div class="space-y-2">
                                                            @for ($i = 0; $i < $assemblyProduct->quantity; $i++)
                                                                <input type="text"
                                                                    name="products[{{ $index }}][serials][]"
                                                                    value=""
                                                                    placeholder="Serial {{ $i + 1 }}"
                                                                    class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                            @endfor
                                                        </div>
                                                    @else
                                                        <input type="text"
                                                            name="products[{{ $index }}][serials][]"
                                                            value="" placeholder="Serial"
                                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button type="button"
                                                    class="text-red-500 hover:text-red-700 delete-product"
                                                    data-index="{{ $index }}"><i
                                                        class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <!-- Fallback cho assemblies cũ (legacy support) -->
                                    <tr class="product-row bg-white hover:bg-gray-50"
                                        data-product-id="{{ $assembly->product_id }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $assembly->product->code ?? 'N/A' }}
                                            <input type="hidden" name="products[0][id]"
                                                value="{{ $assembly->product_id }}">
                                            <input type="hidden" name="products[0][code]"
                                                value="{{ $assembly->product->code ?? '' }}">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $assembly->product->name ?? 'Không có sản phẩm' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <input type="number" min="1" name="products[0][quantity]"
                                                value="{{ $assembly->quantity ?? 1 }}"
                                                class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            @if (($assembly->quantity ?? 1) > 1)
                                                <div class="space-y-2">
                                                    @for ($i = 0; $i < ($assembly->quantity ?? 1); $i++)
                                                        <input type="text" name="products[0][serials][]"
                                                            value="{{ $productSerials[$i] ?? '' }}"
                                                            placeholder="Serial {{ $i + 1 }}"
                                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    @endfor
                                                </div>
                                            @else
                                                <input type="text" name="products[0][serials][]"
                                                    value="{{ $productSerials[0] ?? '' }}" placeholder="Serial"
                                                    class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button type="button"
                                                class="text-red-500 hover:text-red-700 delete-product"
                                                data-index="0"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Danh sách linh kiện -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-microchip text-blue-500 mr-2"></i>
                        Danh sách linh kiện sử dụng
                    </h2>

                    <div class="mt-4 flex items-center space-x-2 mb-4">
                        <div class="flex-grow">
                            <input type="text" id="component_search"
                                placeholder="Nhập hoặc click để xem danh sách linh kiện..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <!-- Search Results Popup -->
                            <div id="search_results"
                                class="absolute bg-white mt-1 border border-gray-300 rounded-lg shadow-lg z-10 hidden w-full max-w-2xl max-h-60 overflow-y-auto">
                                <!-- Search results will be populated here -->
                            </div>
                        </div>
                        <div class="w-24">
                            <input type="number" id="component_add_quantity" min="1" value="1"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <button id="add_component_btn"
                                class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none flex items-center">
                                <i class="fas fa-plus mr-1"></i> Thêm
                            </button>
                        </div>
                    </div>

                    <!-- Component blocks container -->
                    <div id="component_blocks_container" class="mb-4">
                        <div id="component_block_product_1" class="mb-6 border border-gray-200 rounded-lg">
                            <div class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center justify-between">
                                <div class="font-medium text-blue-800 flex items-center">
                                    <i class="fas fa-box-open mr-2"></i>
                                    <span>Linh kiện cho: 
                                        @if($assembly->products && $assembly->products->count() > 0)
                                            @foreach($assembly->products as $index => $assemblyProduct)
                                                {{ $assemblyProduct->product->name }}@if(!$loop->last), @endif
                                            @endforeach
                                        @else
                                            {{ $assembly->product->name ?? 'Không có sản phẩm' }}
                                        @endif
                                    </span>
                                    <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                        @if($assembly->products && $assembly->products->count() > 0)
                                            {{ $assembly->products->sum('quantity') }} thành phẩm
                                        @else
                                            {{ $assembly->quantity ?? 0 }} thành phẩm
                                        @endif
                                    </span>
                                </div>
                                <button type="button" class="toggle-components text-blue-700 hover:text-blue-900">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                            </div>
                            <div class="component-list-container p-4">
                                <!-- Bảng linh kiện cho thành phẩm -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Mã
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Loại linh kiện
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Tên linh kiện
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Số lượng
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Serial
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Ghi chú
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Thao tác
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="component_list" class="bg-white divide-y divide-gray-200">
                                            <!-- thành phẩm hiện tại -->
                                            @foreach ($assembly->materials as $index => $material)
                                                <tr class="component-row bg-white hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <input type="hidden"
                                                            name="components[{{ $index }}][id]"
                                                            value="{{ $material->material_id }}">
                                                        <input type="hidden"
                                                            name="components[{{ $index }}][product_id]"
                                                            value="@if ($assembly->products && $assembly->products->count() > 0) {{ $assembly->products->first()->product_id }}@else{{ $assembly->product_id }} @endif">
                                                        {{ $material->material->code }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        {{ $material->material->category }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        {{ $material->material->name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        <input type="number" min="1"
                                                            name="components[{{ $index }}][quantity]"
                                                            value="{{ $material->quantity }}"
                                                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        <input type="text"
                                                            name="components[{{ $index }}][serial]"
                                                            value="{{ $material->serial ?? '' }}"
                                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                            placeholder="Nhập serial">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                        <input type="text"
                                                            name="components[{{ $index }}][note]"
                                                            value="{{ $material->note ?? '' }}"
                                                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                            placeholder="Ghi chú">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <button type="button"
                                                            class="text-red-500 hover:text-red-700 delete-component"
                                                            data-index="{{ $index }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <!-- Hàng "không có linh kiện" -->
                                            <tr id="no_components_row"
                                                style="{{ count($assembly->materials) > 0 ? 'display: none;' : '' }}">
                                                <td colspan="7"
                                                    class="px-6 py-4 text-sm text-gray-500 text-center">
                                                    Chưa có linh kiện nào được thêm vào thành phẩm này
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('assemblies.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-times mr-2"></i> Hủy
                    </a>
                    <button type="submit" id="submit-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-save mr-2"></i> Cập nhật phiếu
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Global variables
            let productIndex = {{ ($assembly->products && $assembly->products->count() > 0) ? $assembly->products->count() : 1 }};
            let componentIndex = {{ count($assembly->materials) }};
            
            // Component blocks toggle
            document.querySelectorAll('.toggle-components').forEach(button => {
                button.addEventListener('click', function() {
                    const block = this.closest('.mb-6');
                    const container = block.querySelector('.component-list-container');
                    const icon = this.querySelector('i');

                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        icon.className = 'fas fa-chevron-up';
                    } else {
                        container.style.display = 'none';
                        icon.className = 'fas fa-chevron-down';
                    }
                });
            });

            // Handle adding new products
            const addProductBtn = document.getElementById('add_product_btn');
            const productSelect = document.getElementById('product_id');
            const productQuantityInput = document.getElementById('product_add_quantity');
            const productList = document.getElementById('product_list');

            if (addProductBtn) {
                addProductBtn.addEventListener('click', function() {
                    const selectedOption = productSelect.options[productSelect.selectedIndex];
                    if (!selectedOption.value) {
                        alert('Vui lòng chọn thành phẩm!');
                        return;
                    }

                    const productId = selectedOption.value;
                    const productCode = selectedOption.dataset.code;
                    const productName = selectedOption.dataset.name;
                    const quantity = parseInt(productQuantityInput.value) || 1;

                    // Check if product already exists
                    const existingRow = productList.querySelector(`tr[data-product-id="${productId}"]`);
                    if (existingRow) {
                        alert('Thành phẩm này đã được thêm vào danh sách!');
                        return;
                    }

                    addProductRow(productId, productCode, productName, quantity);
                    
                    // Reset form
                    productSelect.value = '';
                    productQuantityInput.value = '1';
                });
            }

            // Function to add product row
            function addProductRow(productId, productCode, productName, quantity) {
                const row = document.createElement('tr');
                row.className = 'product-row bg-white hover:bg-gray-50';
                row.setAttribute('data-product-id', productId);

                let serialInputs = '';
                for (let i = 0; i < quantity; i++) {
                    serialInputs += `
                        <input type="text" name="products[${productIndex}][serials][]" 
                            value="" placeholder="Serial ${i + 1}"
                            class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-1">
                    `;
                }

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${productCode}
                        <input type="hidden" name="products[${productIndex}][id]" value="${productId}">
                        <input type="hidden" name="products[${productIndex}][code]" value="${productCode}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        ${productName}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <input type="number" min="1" name="products[${productIndex}][quantity]" 
                            value="${quantity}" onchange="updateProductSerials(this, ${productIndex})"
                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <div class="serials-container">
                            ${serialInputs}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button type="button" class="text-red-500 hover:text-red-700 delete-product"
                            data-index="${productIndex}"><i class="fas fa-trash"></i></button>
                    </td>
                `;

                productList.appendChild(row);
                productIndex++;
                updateProductDeleteButtons();
            }

            // Function to update serial inputs when quantity changes
            window.updateProductSerials = function(quantityInput, index) {
                const newQuantity = parseInt(quantityInput.value) || 1;
                const row = quantityInput.closest('tr');
                const serialsContainer = row.querySelector('.serials-container');
                
                // Get existing serial values
                const existingSerials = [];
                const existingInputs = serialsContainer.querySelectorAll('input[type="text"]');
                existingInputs.forEach(input => {
                    if (input.value.trim()) {
                        existingSerials.push(input.value.trim());
                    }
                });
                
                let serialInputs = '';
                for (let i = 0; i < newQuantity; i++) {
                    const existingValue = existingSerials[i] || '';
                    serialInputs += `
                        <input type="text" name="products[${index}][serials][]" 
                            value="${existingValue}" placeholder="Serial ${i + 1}"
                            class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-1">
                    `;
                }
                
                serialsContainer.innerHTML = serialInputs;
            };

            // Handle deleting products
            function updateProductDeleteButtons() {
                document.querySelectorAll('.delete-product').forEach(button => {
                    button.addEventListener('click', function() {
                        if (confirm('Bạn có chắc chắn muốn xóa thành phẩm này?')) {
                            this.closest('tr').remove();
                        }
                    });
                });
            }

            // Initialize existing delete buttons
            updateProductDeleteButtons();

            // Handle adding components
            const addComponentBtn = document.getElementById('add_component_btn');
            const componentSearchInput = document.getElementById('component_search');
            const componentAddQuantity = document.getElementById('component_add_quantity');
            const componentList = document.getElementById('component_list');
            const noComponentsRow = document.getElementById('no_components_row');
            
            let selectedMaterial = null;

            if (addComponentBtn) {
                addComponentBtn.addEventListener('click', function() {
                    if (!selectedMaterial) {
                        alert('Vui lòng chọn linh kiện từ danh sách tìm kiếm!');
                        return;
                    }

                    const quantity = parseInt(componentAddQuantity.value) || 1;
                    
                    // Check if component already exists
                    const existingComponent = componentList.querySelector(`input[value="${selectedMaterial.id}"]`);
                    if (existingComponent) {
                        alert('Linh kiện này đã được thêm vào danh sách!');
                        return;
                    }

                    // Get the first product ID for this component
                    const firstProductId = getFirstProductId();
                    if (!firstProductId) {
                        alert('Vui lòng thêm ít nhất một thành phẩm trước!');
                        return;
                    }

                    addComponentRow(selectedMaterial, quantity, firstProductId);
                    
                    // Reset form
                    selectedMaterial = null;
                    componentSearchInput.value = '';
                    componentAddQuantity.value = '1';
                    document.getElementById('search_results').classList.add('hidden');
                });
            }

            // Function to get first product ID
            function getFirstProductId() {
                const firstProductRow = productList.querySelector('tr[data-product-id]');
                return firstProductRow ? firstProductRow.getAttribute('data-product-id') : null;
            }

            // Function to add component row
            function addComponentRow(material, quantity, productId) {
                const row = document.createElement('tr');
                row.className = 'component-row bg-white hover:bg-gray-50';

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <input type="hidden" name="components[${componentIndex}][id]" value="${material.id}">
                        <input type="hidden" name="components[${componentIndex}][product_id]" value="${productId}">
                        ${material.code}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        ${material.category || '-'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        ${material.name}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <input type="number" min="1" name="components[${componentIndex}][quantity]" 
                            value="${quantity}"
                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <input type="text" name="components[${componentIndex}][serial]" 
                            value="" placeholder="Nhập serial"
                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <input type="text" name="components[${componentIndex}][note]" 
                            value="" placeholder="Ghi chú"
                            class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button type="button" class="text-red-500 hover:text-red-700 delete-component"
                            data-index="${componentIndex}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;

                // Hide "no components" row if it exists
                if (noComponentsRow) {
                    noComponentsRow.style.display = 'none';
                }

                componentList.appendChild(row);
                componentIndex++;
                updateComponentDeleteButtons();
            }

            // Handle deleting components
            function updateComponentDeleteButtons() {
                document.querySelectorAll('.delete-component').forEach(button => {
                    button.addEventListener('click', function() {
                        if (confirm('Bạn có chắc chắn muốn xóa linh kiện này?')) {
                            this.closest('tr').remove();
                            
                            // Show "no components" row if no components left
                            const remainingComponents = componentList.querySelectorAll('.component-row');
                            if (remainingComponents.length === 0 && noComponentsRow) {
                                noComponentsRow.style.display = '';
                            }
                        }
                    });
                });
            }

            // Initialize existing component delete buttons
            updateComponentDeleteButtons();

            // Handle existing product quantity changes
            document.querySelectorAll('input[name*="[quantity]"]').forEach(input => {
                if (input.name.includes('products')) {
                    input.addEventListener('change', function() {
                        const matches = this.name.match(/products\[(\d+)\]\[quantity\]/);
                        if (matches) {
                            const index = matches[1];
                            updateProductSerials(this, index);
                        }
                    });
                }
            });

            // Handle form submission validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const products = productList.querySelectorAll('.product-row');
                    if (products.length === 0) {
                        e.preventDefault();
                        alert('Vui lòng thêm ít nhất một thành phẩm!');
                        return false;
                    }

                    const components = componentList.querySelectorAll('.component-row');
                    if (components.length === 0) {
                        e.preventDefault();
                        alert('Vui lòng thêm ít nhất một linh kiện!');
                        return false;
                    }

                    return true;
                });
            }

            // Remove the required attribute from product dropdown on page load to prevent HTML5 validation
            const productDropdown = document.getElementById('product_id');
            if (productDropdown) {
                productDropdown.removeAttribute('required');
            }

            // Xử lý tìm kiếm linh kiện khi gõ
            const componentSearchInput = document.getElementById('component_search');
            const addComponentBtn = document.getElementById('add_component_btn');
            const componentList = document.getElementById('component_list');
            const noComponentsRow = document.getElementById('no_components_row');
            const searchResults = document.getElementById('search_results');
            const productSelect = document.getElementById('product_id');
            const warehouseSelect = document.getElementById('warehouse_id');
            const componentAddQuantity = document.getElementById('component_add_quantity');

            // Component blocks toggle
            document.querySelectorAll('.toggle-components').forEach(button => {
                button.addEventListener('click', function() {
                    const block = this.closest('.mb-6');
                    const container = block.querySelector('.component-list-container');
                    const icon = this.querySelector('i');

                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        icon.className = 'fas fa-chevron-up';
                    } else {
                        container.style.display = 'none';
                        icon.className = 'fas fa-chevron-down';
                    }
                });
            });

            let searchTimeout = null;
            let selectedMaterial = null;
            let warehouseMaterials = [];

            // Ensure component quantity is at least 1
            componentAddQuantity.addEventListener('change', function() {
                if (parseInt(this.value) < 1) {
                    this.value = 1;
                }
            });

            // Khởi tạo mảng linh kiện đã chọn từ dữ liệu hiện có
            let selectedComponents = [];

            // Tải linh kiện đã có vào mảng
            document.querySelectorAll('.component-row').forEach((row, index) => {
                const idInput = row.querySelector('input[name^="components"][name$="[id]"]');
                const quantityInput = row.querySelector('input[name^="components"][name$="[quantity]"]');
                const serialInput = row.querySelector('input[name^="components"][name$="[serial]"]');
                const noteInput = row.querySelector('input[name^="components"][name$="[note]"]');

                if (idInput) {
                    const code = row.cells[0].textContent.trim();
                    const type = row.cells[1].textContent.trim();
                    const name = row.cells[2].textContent.trim();
                    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                    const serial = serialInput ? serialInput.value : '';

                    // Parse multiple serials if comma-separated
                    let serials = [];
                    if (serial && serial.includes(',')) {
                        serials = serial.split(',').map(s => s.trim());
                    }

                    selectedComponents.push({
                        id: idInput.value,
                        code: code,
                        type: type,
                        name: name,
                        quantity: quantity,
                        originalQuantity: quantity, // Store the original quantity for validation
                        serial: serial,
                        serials: serials,
                        note: noteInput ? noteInput.value : '',
                        stock_quantity: 0, // Will be updated when fetching warehouse materials
                        isExisting: true // Flag to mark components already in the assembly
                    });
                }
            });

            // Note: Edit mode doesn't have product quantity input since we're editing existing assembly

            // Thêm event listener cho dropdown kho
            warehouseSelect.addEventListener('change', function() {
                fetchWarehouseMaterials(this.value);
            });

            // Lấy danh sách linh kiện khi click vào ô tìm kiếm
            componentSearchInput.addEventListener('click', function() {
                const warehouseId = warehouseSelect.value;
                if (!warehouseId) {
                    alert('Vui lòng chọn kho trước khi tìm kiếm linh kiện!');
                    return;
                }

                showAllMaterials();
            });

            // Hàm lấy danh sách linh kiện theo kho
            function fetchWarehouseMaterials(warehouseId) {
                if (!warehouseId) return;

                // Hiển thị đang tải
                warehouseMaterials = [];

                // Gọi API để lấy linh kiện theo kho
                fetch(`/api/warehouses/${warehouseId}/materials`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error('Error fetching warehouse materials:', data.message);
                            return;
                        }

                        // Lưu danh sách vật tư của kho
                        warehouseMaterials = Array.isArray(data) ? data : (data.materials || []);

                        // Update stock quantities for existing components
                        selectedComponents.forEach(component => {
                            const warehouseMaterial = warehouseMaterials.find(m => m.id == component
                                .id);
                            if (warehouseMaterial) {
                                component.stock_quantity = warehouseMaterial.stock_quantity;
                            }
                        });

                        // Update the component list to show stock warnings
                        updateComponentList();
                    })
                    .catch(error => {
                        console.error('Error loading warehouse materials:', error);
                    });
            }

            // Hiển thị tất cả linh kiện của kho
            function showAllMaterials() {
                if (warehouseMaterials.length === 0) {
                    // Nếu chưa có dữ liệu, lấy từ API
                    const warehouseId = warehouseSelect.value;
                    if (!warehouseId) return;

                    // Hiển thị đang tải
                    searchResults.innerHTML =
                        '<div class="p-2 text-gray-500">Đang tải danh sách linh kiện...</div>';
                    searchResults.classList.remove('hidden');

                    // Gọi API để lấy linh kiện theo kho
                    fetch(`/api/warehouses/${warehouseId}/materials`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                searchResults.innerHTML = `<div class="p-2 text-red-500">Lỗi: ${data.message}
                                </div>`;
                                console.error('Error fetching warehouse materials:', data.message);
                                return;
                            }

                            // Lưu và hiển thị danh sách vật tư của kho
                            warehouseMaterials = Array.isArray(data) ? data : (data.materials || []);
                            displaySearchResults(warehouseMaterials);
                        })
                        .catch(error => {
                            console.error('Error loading warehouse materials:', error);
                            searchResults.innerHTML =
                                '<div class="p-2 text-red-500">Có lỗi xảy ra khi tải dữ liệu!</div>';
                        });
                } else {
                    // Hiển thị danh sách đã có
                    displaySearchResults(warehouseMaterials);
                }
            }

            // Hiển thị kết quả tìm kiếm
            function displaySearchResults(materials) {
                if (materials.length === 0) {
                    searchResults.innerHTML =
                        '<div class="p-2 text-gray-500">Không có linh kiện nào trong kho này</div>';
                    return;
                }

                searchResults.innerHTML = '';
                materials.forEach(material => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                    resultItem.innerHTML = `
                        <div class="font-medium">${material.code}: ${material.name}</div>
                        <div class="text-xs text-gray-500">
                            ${material.category || ''} 
                            ${material.serial ? '| ' + material.serial : ''} 
                            | Tồn kho: ${material.stock_quantity || 0}
                        </div>
                    `;

                    // Handle click on search result
                    resultItem.addEventListener('click', function() {
                        selectedMaterial = material;
                        componentSearchInput.value = material.code + ' - ' + material.name;
                        searchResults.classList.add('hidden');
                    });

                    searchResults.appendChild(resultItem);
                });

                searchResults.classList.remove('hidden');
            }

            componentSearchInput.addEventListener('input', function() {
                const searchTerm = componentSearchInput.value.trim().toLowerCase();

                // Clear any existing timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                // Set a timeout to avoid too many searches while typing
                searchTimeout = setTimeout(() => {
                    const warehouseId = warehouseSelect.value;
                    if (!warehouseId) {
                        alert('Vui lòng chọn kho trước khi tìm kiếm linh kiện!');
                        return;
                    }

                    if (searchTerm.length === 0) {
                        // Nếu ô tìm kiếm trống, hiển thị tất cả linh kiện
                        showAllMaterials();
                        return;
                    }

                    // Nếu đã có danh sách linh kiện của kho, lọc trực tiếp
                    if (warehouseMaterials.length > 0) {
                        const filteredMaterials = warehouseMaterials.filter(material =>
                            material.code?.toLowerCase().includes(searchTerm) ||
                            material.name?.toLowerCase().includes(searchTerm) ||
                            material.category?.toLowerCase().includes(searchTerm) ||
                            material.serial?.toLowerCase().includes(searchTerm)
                        );

                        displaySearchResults(filteredMaterials);
                        return;
                    }

                    // Nếu chưa có danh sách linh kiện, tìm kiếm qua API
                    searchResults.innerHTML =
                        '<div class="p-2 text-gray-500">Đang tìm kiếm...</div>';
                    searchResults.classList.remove('hidden');

                    // Gọi API để tìm kiếm linh kiện
                    fetch(`/api/warehouses/${warehouseId}/materials?term=${
                        encodeURIComponent(searchTerm)
                    }`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                searchResults.innerHTML = `<div class="p-2 text-red-500">Lỗi: ${data.message}
                                </div>`;
                                console.error('Error searching materials:', data.message);
                                return;
                            }

                            const materials = Array.isArray(data) ? data : (data.materials ||
                            []);
                            displaySearchResults(materials);
                        })
                        .catch(error => {
                            console.error('Error searching materials:', error);
                            searchResults.innerHTML =
                                '<div class="p-2 text-red-500">Có lỗi xảy ra khi tìm kiếm!</div>';
                        });
                }, 300);
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!componentSearchInput.contains(event.target) && !searchResults.contains(event.target)) {
                    searchResults.classList.add('hidden');
                }
            });

            addComponentBtn.addEventListener('click', function() {
                addSelectedComponent();
            });

            // Check if stock is sufficient for a component based on product quantity
            function checkStockSufficiency(component) {
                // Số lượng thành phẩm
                const productQty = {{ $assembly->quantity }};

                // Số lượng linh kiện cho mỗi thành phẩm
                const componentQtyPerProduct = parseInt(component.quantity);

                // Tổng số linh kiện cần = số lượng thành phẩm * số lượng linh kiện cho mỗi thành phẩm
                const totalRequiredQty = productQty * componentQtyPerProduct;

                // For existing components, we only need to check additional quantity beyond original
                let effectiveRequiredQty = totalRequiredQty;
                let additionalQtyNeeded = 0;

                if (component.isExisting && component.originalQuantity) {
                    const originalTotalQty = component.originalQuantity * ({{ $assembly->quantity }});
                    // If we're using less than or equal to original amount, no stock check needed
                    if (totalRequiredQty <= originalTotalQty) {
                        component.isStockSufficient = true;
                        component.stockWarning = '';
                        return true;
                    }
                    // Otherwise, only check the additional quantity needed
                    additionalQtyNeeded = totalRequiredQty - originalTotalQty;
                    // We only need to check if stock can cover this additional amount
                    effectiveRequiredQty = additionalQtyNeeded;
                }

                // Tồn kho hiện có
                const stockQty = parseInt(component.stock_quantity);

                // Check if stock is sufficient for the effective required quantity
                component.isStockSufficient = effectiveRequiredQty <= stockQty;

                if (!component.isStockSufficient) {
                    // Calculate the actual shortage (how many more we need beyond what's in stock)
                    let actualShortage;

                    if (component.isExisting) {
                        // For existing components
                        actualShortage = additionalQtyNeeded - stockQty;
                        component.stockWarning = `Không đủ tồn kho (còn ${stockQty}, cần thêm ${
                            actualShortage
                        })`;
                    } else {
                        // For new components
                        actualShortage = totalRequiredQty - stockQty;
                        component.stockWarning = `Không đủ tồn kho (còn ${stockQty}, cần thêm ${
                            actualShortage
                        })`;
                    }
                } else {
                    component.stockWarning = '';
                }

                return component.isStockSufficient;
            }

            // Update component quantities based on product quantity
            function updateComponentQuantities() {
                // In edit mode, we use the existing assembly quantity
                const productQty = {{ $assembly->quantity }};

                selectedComponents.forEach(component => {
                    // Only update if component doesn't have manually adjusted quantity
                    // AND is not an existing component from the assembly
                    if (!component.manuallyAdjusted && !component.isExisting) {
                        component.quantity = productQty;
                    }

                    // Always check stock sufficiency when product quantity changes
                    checkStockSufficiency(component);
                });

                updateComponentList();
            }

            // Generate serial input fields based on quantity
            function generateSerialInputs(component, index, container) {
                const quantity = parseInt(component.quantity);
                const serialsContainer = document.createElement('div');
                serialsContainer.className = 'serial-inputs mt-2';

                // Clear existing serials container
                const existingContainer = container.querySelector('.serial-inputs');
                if (existingContainer) {
                    existingContainer.remove();
                }

                // Remove any existing serial count label
                const existingLabel = container.querySelector('.serial-count-label');
                if (existingLabel) {
                    existingLabel.remove();
                }

                if (quantity > 1) {
                    // For quantities > 1, show multiple serial inputs
                    // If we have comma-separated serials, split them
                    let serials = component.serials || [];
                    if (!serials.length && component.serial && component.serial.includes(',')) {
                        serials = component.serial.split(',').map(s => s.trim());
                        component.serials = serials;
                    }

                    for (let i = 0; i < quantity; i++) {
                        const serialDiv = document.createElement('div');
                        serialDiv.className = 'mb-1';
                        const serialInput = document.createElement('input');
                        serialInput.type = 'text';
                        serialInput.name = `components[${index}][serials][]`;
                        serialInput.value = serials[i] || '';
                        serialInput.placeholder = `Serial ${i+1}`;
                        serialInput.className =
                            'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                        // Save serial when typing
                        serialInput.addEventListener('input', function() {
                            if (!component.serials) component.serials = [];
                            component.serials[i] = this.value;
                        });

                        serialDiv.appendChild(serialInput);
                        serialsContainer.appendChild(serialDiv);
                    }
                } else {
                    // For quantity = 1, show single serial input
                    const serialInput = document.createElement('input');
                    serialInput.type = 'text';
                    serialInput.name = `components[${index}][serial]`;
                    serialInput.value = component.serial || (component.serials && component.serials[0] || '');
                    serialInput.placeholder = 'Nhập serial';
                    serialInput.className =
                        'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500';

                    // Save serial when typing
                    serialInput.addEventListener('input', function() {
                        component.serial = this.value;
                    });

                    serialsContainer.appendChild(serialInput);
                }

                container.appendChild(serialsContainer);

                // Add label indicating multiple serials if needed
                if (quantity > 1) {
                    const label = document.createElement('div');
                    label.className = 'text-xs text-gray-500 mt-1 serial-count-label';
                    label.textContent = `${quantity} serial cho linh kiện này`;
                    container.appendChild(label);
                }
            }

            // Add selected component function
            function addSelectedComponent() {
                if (selectedMaterial) {
                    // Check if already added
                    const existingComponent = selectedComponents.find(c => c.id == selectedMaterial.id);
                    if (existingComponent) {
                        // Allow updating existing components with validation
                        const newQty = parseInt(componentAddQuantity.value) || 1;
                        existingComponent.quantity = newQty;

                        // If the component was already in the assembly, check for increased quantity
                        if (existingComponent.isExisting) {
                            if (newQty > existingComponent.originalQuantity) {
                                // Validate stock for the increased amount
                                if (!checkStockSufficiency(existingComponent)) {
                                    alert('Không đủ tồn kho cho số lượng mới!');
                                    existingComponent.quantity = existingComponent.originalQuantity;
                                }
                            }
                        }

                        updateComponentList();
                        componentSearchInput.value = '';
                        componentAddQuantity.value = '1';
                        selectedMaterial = null;
                        searchResults.classList.add('hidden');
                        return;
                    }

                    // Số lượng linh kiện cho mỗi thành phẩm
                    const componentQtyPerProduct = parseInt(componentAddQuantity.value) || 1;

                    // Số lượng thành phẩm (từ assembly hiện tại)
                    const productQty = {{ $assembly->quantity }};

                    // Tổng số linh kiện cần = số lượng thành phẩm * số lượng linh kiện cho mỗi thành phẩm
                    const totalRequiredQty = productQty * componentQtyPerProduct;

                    // Tồn kho hiện có
                    const stockQty = parseInt(selectedMaterial.stock_quantity) || 0;

                    // Check if there's enough stock
                    if (totalRequiredQty > stockQty) {
                        alert(
                            `Không đủ tồn kho! Tồn kho hiện tại: ${stockQty}, Yêu cầu: ${totalRequiredQty} (${productQty} thành phẩm × ${componentQtyPerProduct} linh kiện/thành phẩm)`
                        );
                        return;
                    }

                    // Add to selected components
                    const newComponent = {
                        id: selectedMaterial.id,
                        code: selectedMaterial.code,
                        name: selectedMaterial.name,
                        type: selectedMaterial.category || '',
                        quantity: componentQtyPerProduct,
                        originalQuantity: componentQtyPerProduct, // Store the original quantity for validation
                        stock_quantity: selectedMaterial.stock_quantity || 0,
                        serial: selectedMaterial.serial || '',
                        serials: [],
                        note: '',
                        manuallyAdjusted: true, // Mark as manually adjusted to prevent auto-update from product quantity
                        isExisting: false // This is a newly added component
                    };

                    // Check stock sufficiency
                    checkStockSufficiency(newComponent);

                    selectedComponents.push(newComponent);

                    // Update UI
                    updateComponentList();
                    componentSearchInput.value = '';
                    componentAddQuantity.value = '1'; // Reset quantity to 1
                    selectedMaterial = null;
                    searchResults.classList.add('hidden');
                } else {
                    const searchTerm = componentSearchInput.value.trim();

                    if (!searchTerm) {
                        alert('Vui lòng chọn linh kiện trước khi thêm!');
                        return;
                    }

                    // Không tìm thấy linh kiện hoặc chưa chọn
                    alert('Vui lòng chọn một linh kiện từ danh sách!');
                }
            }

            // Update stock warning when quantity changes
            function updateStockWarning(row, component) {
                const stockWarningContainer = row.querySelector('td:nth-child(4) div');

                if (component.stockWarning) {
                    if (stockWarningContainer) {
                        stockWarningContainer.innerHTML = component.stockWarning;
                    } else {
                        const warningDiv = document.createElement('div');
                        warningDiv.className = 'text-xs text-red-600 font-medium mt-1';
                        warningDiv.textContent = component.stockWarning;
                        row.querySelector('td:nth-child(4)').appendChild(warningDiv);
                    }
                } else if (stockWarningContainer) {
                    stockWarningContainer.remove();
                }
            }

            // Cập nhật danh sách linh kiện
            function updateComponentList() {
                // Ẩn thông báo "không có linh kiện"
                if (selectedComponents.length > 0) {
                    noComponentsRow.style.display = 'none';
                } else {
                    noComponentsRow.style.display = '';
                }

                // Xóa các hàng linh kiện hiện tại (trừ hàng thông báo)
                const componentRows = document.querySelectorAll('.component-row');
                componentRows.forEach(row => row.remove());

                // Thêm hàng cho mỗi linh kiện đã chọn
                selectedComponents.forEach((component, index) => {
                    const row = document.createElement('tr');
                    row.className = 'component-row bg-white hover:bg-gray-50';

                    // Hiển thị cảnh báo tồn kho nếu có
                    const stockWarningHtml = component.stockWarning ?
                        `<div class="text-xs text-red-600 font-medium mt-1">${component.stockWarning}</div>` :
                        '';

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="hidden" name="components[${index}][id]" value="${component.id}">
                            ${component.code}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            ${component.type || ''}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${component.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="number" min="1" name="components[${index}][quantity]" value="${component.quantity}"
                                class="w-20 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 quantity-input">
                            ${stockWarningHtml}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 serial-cell">
                            <!-- Serial inputs will be added here -->
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <input type="text" name="components[${index}][note]" value="${component.note || ''}"
                                class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ghi chú">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-500 hover:text-red-700 delete-component" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;

                    componentList.insertBefore(row, noComponentsRow);

                    // Generate serial inputs based on quantity
                    const serialCell = row.querySelector('.serial-cell');
                    generateSerialInputs(component, index, serialCell);

                    // Add event listener for quantity change
                    const quantityInput = row.querySelector('.quantity-input');
                    quantityInput.addEventListener('change', function() {
                        const newQty = parseInt(this.value) || 1;
                        if (newQty < 1) {
                            this.value = component.quantity = 1;
                        } else {
                            component.quantity = newQty;
                        }

                        // Mark as manually adjusted
                        component.manuallyAdjusted = true;

                        // Check if quantity differs from original formula
                        checkAndShowCreateNewProductButton();

                        // Update serial inputs
                        generateSerialInputs(component, index, serialCell);

                        // Check stock sufficiency and update warning
                        checkStockSufficiency(component);
                        updateStockWarning(row, component);
                    });

                    // Thêm event listener để xóa linh kiện
                    row.querySelector('.delete-component').addEventListener('click', function() {
                        selectedComponents.splice(index, 1);
                        updateComponentList();
                    });
                });

                // Check and show create new product button after updating components
                checkAndShowCreateNewProductButton();
            }

            // Validation trước khi submit
            document.querySelector('form').addEventListener('submit', function(e) {
                if (selectedComponents.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một vật tư vào phiếu lắp ráp!');
                    return false;
                }

                // Kiểm tra serial thành phẩm (nếu có)
                const serialInputs = document.querySelectorAll('input[name*="serials"]');
                let hasSerialError = false;
                let hasDuplicateSerials = false;
                let serialValues = [];

                // Kiểm tra các trường serial có lỗi
                serialInputs.forEach(input => {
                    if (input.classList.contains('border-red-500')) {
                        hasSerialError = true;
                    }

                    if (input.value.trim()) {
                        // Kiểm tra trùng lặp
                        if (serialValues.includes(input.value.trim())) {
                            hasDuplicateSerials = true;
                        } else {
                            serialValues.push(input.value.trim());
                        }
                    }
                });

                if (hasSerialError) {
                    e.preventDefault();
                    alert('Vui lòng kiểm tra lại các serial có lỗi!');
                    return false;
                }

                if (hasDuplicateSerials) {
                    e.preventDefault();
                    alert('Phát hiện trùng lặp serial. Vui lòng kiểm tra lại!');
                    return false;
                }

                // Kiểm tra số lượng và tồn kho
                let hasStockError = false;
                let errorMessages = [];

                // Kiểm tra số lượng vật tư
                for (const component of selectedComponents) {
                    if (parseInt(component.quantity) < 1) {
                        e.preventDefault();
                        alert('Số lượng vật tư phải lớn hơn 0!');
                        return false;
                    }

                    // Kiểm tra lại tồn kho
                    checkStockSufficiency(component);
                    if (!component.isStockSufficient) {
                        hasStockError = true;
                        errorMessages.push(
                            `- ${component.code}: ${component.name} - ${component.stockWarning}`
                        );
                    }
                }

                // Nếu có lỗi tồn kho, hiển thị thông báo và ngăn submit form
                if (hasStockError) {
                    e.preventDefault();
                    alert(`Không thể lưu phiếu lắp ráp do không đủ tồn kho:\n${
                        errorMessages.join('\n')
                    }`);
                    return false;
                }

                return true;
            });

            // Khởi tạo: tải danh sách linh kiện của kho nếu đã chọn kho
            if (warehouseSelect.value) {
                fetchWarehouseMaterials(warehouseSelect.value);
            }

            // Note: Edit mode uses existing product serial inputs from the HTML template

            // Note: Serial validation functions removed for edit mode since we use simple inputs

            // Function to check if any components have modified quantities
            function checkComponentsModified() {
                console.log('Checking modified components');
                console.log('Selected components:', selectedComponents);

                const hasModified = selectedComponents.some(component => {
                    const isModified = component.quantity !== component.originalQuantity;
                    console.log(
                        `Component ${component.name}: current=${component.quantity}, original=${component.originalQuantity}, modified=${isModified}`
                    );
                    return isModified;
                });

                console.log('Has modified components:', hasModified);
                return hasModified;
            }

            // Function to add the "Create New Product" button
            function addCreateNewProductButton() {
                // Look for existing component table container
                const componentContainer = document.querySelector('.component-container') || componentList
                    .parentElement;
                if (!componentContainer) return;

                // Remove existing button if any
                const existingSection = componentContainer.querySelector('.duplicate-section');
                if (existingSection) {
                    existingSection.remove();
                }

                const duplicateSection = document.createElement('div');
                duplicateSection.className =
                    'bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4 duplicate-section';
                duplicateSection.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-yellow-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            Bạn đã thay đổi công thức gốc. Bạn có thể tạo một thành phẩm mới với công thức này.
                        </div>
                        <button type="button" class="create-new-product-btn bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm">
                            <i class="fas fa-plus-circle mr-1"></i> Tạo thành phẩm mới
                        </button>
                    </div>
                `;

                // Insert after the component list container
                componentContainer.appendChild(duplicateSection);

                // Add event listener to the create new product button
                setTimeout(() => {
                    const createNewBtn = duplicateSection.querySelector('.create-new-product-btn');
                    if (createNewBtn) {
                        createNewBtn.addEventListener('click', function() {
                            showCreateNewProductModal();
                        });
                    }
                }, 100);
            }

            // Function to check and show create new product button
            function checkAndShowCreateNewProductButton() {
                console.log('checkAndShowCreateNewProductButton called');

                const isModified = checkComponentsModified();
                console.log('IsModified:', isModified);

                if (isModified) {
                    console.log('Adding create new product button');
                    addCreateNewProductButton();
                } else {
                    console.log('Removing create new product button');
                    // Remove the button if no longer modified
                    const existingSection = document.querySelector('.duplicate-section');
                    if (existingSection) {
                        existingSection.remove();
                    }
                }
            }

            // Function to show create new product modal/alert
            function showCreateNewProductModal() {
                const productName = productSelect.options[productSelect.selectedIndex].text;

                if (selectedComponents.length === 0) return;

                // Create a summary of the modified formula
                let formulaSummary = 'Công thức mới:\n';
                selectedComponents.forEach(comp => {
                    const isModified = comp.quantity !== comp.originalQuantity;
                    const status = isModified ? ` (đã thay đổi từ ${comp.originalQuantity})` : '';
                    formulaSummary += `- ${comp.name}: ${comp.quantity}${status}\n`;
                });

                // Show confirmation dialog
                const confirmed = confirm(
                    `Bạn có muốn tạo thành phẩm mới "${productName} (Modified)" với công thức sau?\n\n${formulaSummary}\n` +
                    `Chức năng này sẽ lưu công thức mới vào hệ thống để sử dụng cho các lần lắp ráp tiếp theo.`
                );
            }

            // Purpose selection handler
            const purposeSelect = document.getElementById('purpose');
            const projectSelection = document.getElementById('project_selection');
            const projectIdSelect = document.getElementById('project_id');
            const targetWarehouseContainer = document.getElementById('target_warehouse_container');
            const targetWarehouseSelect = document.getElementById('target_warehouse_id');

            purposeSelect.addEventListener('change', function() {
                if (this.value === 'project') {
                    // Chọn "Xuất đi dự án" -> Ẩn kho nhập, hiện dự án
                    targetWarehouseContainer.style.display = 'none';
                    targetWarehouseSelect.removeAttribute('required');

                    projectSelection.classList.remove('hidden');
                    projectSelection.style.display = 'block';
                    projectIdSelect.setAttribute('required', 'required');
                } else {
                    // Chọn "Lưu kho" -> Hiện kho nhập, ẩn dự án
                    targetWarehouseContainer.style.display = 'block';
                    targetWarehouseSelect.setAttribute('required', 'required');

                    projectSelection.classList.add('hidden');
                    projectSelection.style.display = 'none';
                    projectIdSelect.removeAttribute('required');
                }
            });

            // Khởi tạo trạng thái ban đầu dựa trên giá trị hiện tại
            if (purposeSelect.value === 'project') {
                targetWarehouseContainer.style.display = 'none';
                targetWarehouseSelect.removeAttribute('required');
                projectSelection.classList.remove('hidden');
                projectSelection.style.display = 'block';
                projectIdSelect.setAttribute('required', 'required');
            } else {
                targetWarehouseContainer.style.display = 'block';
                targetWarehouseSelect.setAttribute('required', 'required');
                projectSelection.classList.add('hidden');
                projectSelection.style.display = 'none';
                projectIdSelect.removeAttribute('required');
            }
        });
    </script>
</body>

</html>
