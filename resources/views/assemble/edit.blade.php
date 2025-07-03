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
    <script src="{{ asset('js/assembly-product-unit.js') }}"></script>
    <style>
        /* Styles for product units */
        .product-unit-0 { border-left: 4px solid #3b82f6; }
        .product-unit-1 { border-left: 4px solid #10b981; }
        .product-unit-2 { border-left: 4px solid #f59e0b; }
        .product-unit-3 { border-left: 4px solid #ef4444; }
        .product-unit-4 { border-left: 4px solid #8b5cf6; }
        .product-unit-5 { border-left: 4px solid #ec4899; }
        
        .product-unit-0-bg { background-color: rgba(59, 130, 246, 0.1); padding: 2px 6px; border-radius: 4px; }
        .product-unit-1-bg { background-color: rgba(16, 185, 129, 0.1); padding: 2px 6px; border-radius: 4px; }
        .product-unit-2-bg { background-color: rgba(245, 158, 11, 0.1); padding: 2px 6px; border-radius: 4px; }
        .product-unit-3-bg { background-color: rgba(239, 68, 68, 0.1); padding: 2px 6px; border-radius: 4px; }
        .product-unit-4-bg { background-color: rgba(139, 92, 246, 0.1); padding: 2px 6px; border-radius: 4px; }
        .product-unit-5-bg { background-color: rgba(236, 72, 153, 0.1); padding: 2px 6px; border-radius: 4px; }
    </style>
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Người phụ trách</label>
                            <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                {{ $assembly->assignedEmployee->name ?? ($assembly->assigned_to ?? 'Chưa xác định') }}
                            </div>
                            <input type="hidden" name="assigned_to"
                                value="{{ $assembly->assigned_employee_id ?? $assembly->assigned_to }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Người tiếp nhận kiểm thử</label>
                            <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                {{ $assembly->tester->name ?? 'Chưa phân công' }}
                            </div>
                            <input type="hidden" name="tester_id" value="{{ $assembly->tester_id }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mục đích</label>
                            <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                {{ $assembly->purpose == 'storage' ? 'Lưu kho' : ($assembly->purpose == 'project' ? 'Xuất đi dự án' : 'Không xác định') }}
                            </div>
                            <input type="hidden" name="purpose" value="{{ $assembly->purpose }}">
                        </div>
                        <div>
                            @if ($assembly->purpose == 'project' && $assembly->project)
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dự án</label>
                                <div
                                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                    {{ $assembly->project->project_name ?? 'Không xác định' }}
                                </div>
                                <input type="hidden" name="project_id" value="{{ $assembly->project_id }}">
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kho xuất vật tư</label>
                            <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                {{ $assembly->warehouse->name ?? 'Không xác định' }}
                                ({{ $assembly->warehouse->code ?? '' }})
                            </div>
                            <input type="hidden" name="warehouse_id" value="{{ $assembly->warehouse_id }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kho nhập thành phẩm</label>
                            <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                {{ $assembly->targetWarehouse->name ?? 'Không xác định' }}
                                ({{ $assembly->targetWarehouse->code ?? '' }})
                            </div>
                            <input type="hidden" name="target_warehouse_id"
                                value="{{ $assembly->target_warehouse_id }}">
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
                                                <div
                                                    class="w-20 border border-gray-200 bg-gray-50 rounded-lg px-2 py-1 text-center">
                                                    {{ $assemblyProduct->quantity }}
                                                </div>
                                                <input type="hidden" name="products[{{ $index }}][quantity]"
                                                    value="{{ $assemblyProduct->quantity }}">
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
                                            <div
                                                class="w-20 border border-gray-200 bg-gray-50 rounded-lg px-2 py-1 text-center">
                                                {{ $assembly->quantity ?? 1 }}
                                            </div>
                                            <input type="hidden" name="products[0][quantity]"
                                                value="{{ $assembly->quantity ?? 1 }}">
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
                                            <span class="text-gray-400">
                                                <i class="fas fa-lock"
                                                    title="Không thể xóa trong chế độ chỉnh sửa"></i>
                                            </span>
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

                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span class="text-sm">Chế độ chỉnh sửa: Chỉ có thể cập nhật serial và ghi chú cho linh kiện
                                hiện có. Không thể thêm/xóa linh kiện.</span>
                        </div>
                    </div>

                    <!-- Component blocks container -->
                    <div class="mb-4">
                        @php
                            // Group components by product
                            $componentsByProduct = [];

                            // Group existing materials by target_product_id
                            foreach ($assembly->materials as $index => $material) {
                                $productId = $material->target_product_id;

                                // Map to product index for legacy compatibility
                                if ($assembly->products && $assembly->products->count() > 0) {
                                    foreach ($assembly->products as $productIndex => $assemblyProduct) {
                                        if ($assemblyProduct->product_id == $productId) {
                                            $productKey = "product_{$productIndex}";
                                            break;
                                        }
                                    }
                                } else {
                                    // Legacy support
                                    $productKey = 'product_0';
                                }

                                if (!isset($componentsByProduct[$productKey])) {
                                    $componentsByProduct[$productKey] = [];
                                }

                                $componentsByProduct[$productKey][] = [
                                    'material' => $material,
                                    'globalIndex' => $index,
                                ];
                            }
                        @endphp

                        @if ($assembly->products && $assembly->products->count() > 0)
                            <!-- Multi-product assemblies -->
                            @foreach ($assembly->products as $productIndex => $assemblyProduct)
                                @php
                                    $productKey = "product_{$productIndex}";
                                    $components = $componentsByProduct[$productKey] ?? [];
                                @endphp

                                <div class="mb-6 border border-gray-200 rounded-lg">
                                    <div class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center justify-between">
                                        <div class="font-medium text-blue-800 flex items-center">
                                            <i class="fas fa-box-open mr-2"></i>
                                            <span>Linh kiện cho: {{ $assemblyProduct->product->name }}</span>
                                            <span
                                                class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                                {{ $assemblyProduct->quantity }} thành phẩm
                                            </span>
                                        </div>
                                        <button type="button"
                                            class="toggle-components text-blue-700 hover:text-blue-900">
                                            <i class="fas fa-chevron-up"></i>
                                        </button>
                                    </div>
                                    <div class="component-list-container p-4">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Mã</th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Loại linh kiện</th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Tên linh kiện</th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Số lượng</th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Serial</th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Thuộc thành phẩm</th>
                                                        <th
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Ghi chú</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @if (count($components) === 0)
                                                        <tr>
                                                            <td colspan="6"
                                                                class="px-6 py-4 text-sm text-gray-500 text-center">
                                                                Chưa có linh kiện nào được thêm vào thành phẩm này
                                                            </td>
                                                        </tr>
                                                    @else
                                                        @foreach ($components as $component)
                                                            <tr class="component-row bg-white hover:bg-gray-50">
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    <input type="hidden"
                                                                        name="components[{{ $component['globalIndex'] }}][id]"
                                                                        value="{{ $component['material']->material_id }}">
                                                                    <input type="hidden"
                                                                        name="components[{{ $component['globalIndex'] }}][product_id]"
                                                                        value="product_{{ $productIndex }}">
                                                                    {{ $component['material']->material->code }}
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                    {{ $component['material']->material->category ?? '' }}
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                    {{ $component['material']->material->name }}</td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                    <div
                                                                        class="w-20 border border-gray-200 bg-gray-50 rounded-lg px-2 py-1 text-center">
                                                                        {{ $component['material']->quantity }}
                                                                    </div>
                                                                    <input type="hidden"
                                                                        name="components[{{ $component['globalIndex'] }}][quantity]"
                                                                        value="{{ $component['material']->quantity }}">
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                    <div class="space-y-2"
                                                                        data-product-id="{{ $component['material']->target_product_id }}"
                                                                        data-product-index="{{ $productIndex }}">
                                                                        @php
                                                                            // Ensure we properly handle all serial scenarios
                                                                            $serials = [];
                                                                            if ($component['material']->serials) {
                                                                                // Split by comma
                                                                                $serials = explode(
                                                                                    ',',
                                                                                    $component['material']->serials,
                                                                                );
                                                                            }
                                                                        @endphp

                                                                        @for ($i = 0; $i < $component['material']->quantity; $i++)
                                                                            <div class="flex items-center">
                                                                                <select
                                                                                    name="components[{{ $component['globalIndex'] }}][serials][]"
                                                                                    class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 material-serial-select"
                                                                                    data-serial-index="{{ $i }}"
                                                                                    data-material-id="{{ $component['material']->material_id }}"
                                                                                    data-warehouse-id="{{ $assembly->warehouse_id }}"
                                                                                    data-current-serial="{{ $serials[$i] ?? '' }}"
                                                                                    data-product-id="{{ $component['material']->target_product_id }}"
                                                                                    data-product-unit="{{ $component['material']->product_unit ?? 0 }}">
                                                                                    <option value="">-- Chọn
                                                                                        serial {{ $i + 1 }} --
                                                                                    </option>
                                                                                    @if (isset($serials[$i]) && !empty($serials[$i]))
                                                                                        <option
                                                                                            value="{{ $serials[$i] }}"
                                                                                            selected>
                                                                                            {{ $serials[$i] }}
                                                                                        </option>
                                                                                    @endif
                                                                                </select>
                                                                            </div>
                                                                        @endfor
                                                                    </div>
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                    <select
                                                                        name="components[{{ $component['globalIndex'] }}][product_unit]"
                                                                        class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 product-unit-select"
                                                                        data-component-index="{{ $component['globalIndex'] }}"
                                                                        data-material-id="{{ $component['material']->material_id }}"
                                                                        data-product-id="{{ $component['material']->target_product_id }}"
                                                                        onchange="window.handleProductUnitChange(this)">
                                                                        @php
                                                                            $productUnit =
                                                                                $component['material']->product_unit ??
                                                                                0;
                                                                            $productQuantity =
                                                                                $assemblyProduct->quantity ?? 1;
                                                                        @endphp
                                                                        @for ($i = 0; $i < $productQuantity; $i++)
                                                                            <option value="{{ $i }}"
                                                                                {{ $productUnit == $i ? 'selected' : '' }}>
                                                                                Thành phẩm #{{ $i + 1 }}
                                                                                @if ($assemblyProduct->serials)
                                                                                    @php $serialArray = explode(',', $assemblyProduct->serials); @endphp
                                                                                    @if (isset($serialArray[$i]) && !empty($serialArray[$i]))
                                                                                        (Serial:
                                                                                        {{ $serialArray[$i] }})
                                                                                    @endif
                                                                                @endif
                                                                            </option>
                                                                        @endfor
                                                                    </select>
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                    <input type="text"
                                                                        name="components[{{ $component['globalIndex'] }}][note]"
                                                                        value="{{ $component['material']->note ?? '' }}"
                                                                        class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                        placeholder="Ghi chú">
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <!-- Legacy single product assemblies -->
                            <div class="mb-6 border border-gray-200 rounded-lg">
                                <div class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center justify-between">
                                    <div class="font-medium text-blue-800 flex items-center">
                                        <i class="fas fa-box-open mr-2"></i>
                                        <span>Linh kiện cho:
                                            {{ $assembly->product->name ?? 'Không có sản phẩm' }}</span>
                                        <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                            {{ $assembly->quantity ?? 1 }} thành phẩm
                                        </span>
                                    </div>
                                    <button type="button"
                                        class="toggle-components text-blue-700 hover:text-blue-900">
                                        <i class="fas fa-chevron-up"></i>
                                    </button>
                                </div>
                                <div class="component-list-container p-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Mã</th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Loại linh kiện</th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Tên linh kiện</th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Số lượng</th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Serial</th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Thuộc thành phẩm</th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @if ($assembly->materials && $assembly->materials->count() > 0)
                                                    @foreach ($assembly->materials as $index => $material)
                                                        <tr class="component-row bg-white hover:bg-gray-50">
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                <input type="hidden"
                                                                    name="components[{{ $index }}][id]"
                                                                    value="{{ $material->material_id }}">
                                                                <input type="hidden"
                                                                    name="components[{{ $index }}][product_id]"
                                                                    value="product_0">
                                                                {{ $material->material->code }}
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                {{ $material->material->category ?? '' }}</td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                {{ $material->material->name }}</td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                <div
                                                                    class="w-20 border border-gray-200 bg-gray-50 rounded-lg px-2 py-1 text-center">
                                                                    {{ $material->quantity }}
                                                                </div>
                                                                <input type="hidden"
                                                                    name="components[{{ $index }}][quantity]"
                                                                    value="{{ $material->quantity }}">
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                <div class="space-y-2"
                                                                    data-product-id="{{ $material->target_product_id }}"
                                                                    data-product-index="0">
                                                                    @php
                                                                        // Ensure we properly handle all serial scenarios
                                                                        $serials = [];
                                                                        if ($material->serials) {
                                                                            // Split by comma
                                                                            $serials = explode(',', $material->serials);
                                                                        }
                                                                    @endphp

                                                                    @for ($i = 0; $i < $material->quantity; $i++)
                                                                        <div class="flex items-center">
                                                                            <select
                                                                                name="components[{{ $index }}][serials][]"
                                                                                class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 material-serial-select"
                                                                                data-serial-index="{{ $i }}"
                                                                                data-material-id="{{ $material->material_id }}"
                                                                                data-warehouse-id="{{ $assembly->warehouse_id }}"
                                                                                data-current-serial="{{ $serials[$i] ?? '' }}"
                                                                                data-product-id="{{ $material->target_product_id }}"
                                                                                data-product-unit="{{ $material->product_unit ?? 0 }}">
                                                                                <option value="">-- Chọn serial
                                                                                    {{ $i + 1 }} --</option>
                                                                                @if (isset($serials[$i]) && !empty($serials[$i]))
                                                                                    <option
                                                                                        value="{{ $serials[$i] }}"
                                                                                        selected>
                                                                                        {{ $serials[$i] }}
                                                                                    </option>
                                                                                @endif
                                                                            </select>
                                                                        </div>
                                                                    @endfor
                                                                </div>
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                <input type="text"
                                                                    name="components[{{ $index }}][note]"
                                                                    value="{{ $material->note ?? '' }}"
                                                                    class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                    placeholder="Ghi chú">
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                <select
                                                                    name="components[{{ $index }}][product_unit]"
                                                                    class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 product-unit-select"
                                                                    data-component-index="{{ $index }}"
                                                                    data-material-id="{{ $material->material_id }}"
                                                                    data-product-id="{{ $material->target_product_id }}"
                                                                    onchange="window.handleProductUnitChange(this)">
                                                                    @php
                                                                        $productUnit = $material->product_unit ?? 0;
                                                                        $productQuantity = $assembly->quantity ?? 1;
                                                                    @endphp
                                                                    @for ($i = 0; $i < $productQuantity; $i++)
                                                                        <option value="{{ $i }}"
                                                                            {{ $productUnit == $i ? 'selected' : '' }}>
                                                                            Thành phẩm #{{ $i + 1 }}
                                                                            @if (isset($productSerials) && is_array($productSerials))
                                                                                @if (isset($productSerials[$i]) && !empty($productSerials[$i]))
                                                                                    (Serial:
                                                                                    {{ $productSerials[$i] }})
                                                                                @endif
                                                                            @endif
                                                                        </option>
                                                                    @endfor
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="6"
                                                            class="px-6 py-4 text-sm text-gray-500 text-center">
                                                            Chưa có linh kiện nào được thêm vào phiếu lắp ráp này
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
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

            // Component blocks toggle functionality
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

            // Function to check serial via API for edit mode
            async function checkSerialExists(serial, productId, assemblyId = {{ $assembly->id }}) {
                if (!serial || !serial.trim() || !productId) {
                    return {
                        exists: false,
                        message: ''
                    };
                }

                try {
                    const response = await fetch('{{ route('api.check-serial') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            serial: serial.trim(),
                            product_id: productId,
                            assembly_id: assemblyId
                        })
                    });

                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error checking serial:', error);
                    return {
                        exists: false,
                        message: '',
                        error: true
                    };
                }
            }

            // Function to show serial validation message
            function showSerialValidation(input, isValid, message) {
                // Remove existing validation message
                const existingMsg = input.parentNode.querySelector('.serial-validation-msg');
                if (existingMsg) {
                    existingMsg.remove();
                }

                // Add validation styling
                if (isValid) {
                    input.classList.remove('border-red-500', 'bg-red-50');
                    input.classList.add('border-green-500');
                } else {
                    input.classList.remove('border-green-500');
                    input.classList.add('border-red-500', 'bg-red-50');

                    // Add error message
                    const msgDiv = document.createElement('div');
                    msgDiv.className = 'serial-validation-msg text-xs text-red-600 mt-1';
                    msgDiv.textContent = message;
                    input.parentNode.appendChild(msgDiv);
                }
            }

            // Function to add serial validation to product serial inputs
            function addProductSerialValidation(input, productId) {
                let validationTimeout;
                // Store the original value to compare against
                const originalValue = input.value.trim();

                input.addEventListener('input', function() {
                    const serial = this.value.trim();

                    // Clear previous timeout
                    clearTimeout(validationTimeout);

                    // Reset styling
                    this.classList.remove('border-red-500', 'border-green-500', 'bg-red-50');

                    // Remove existing validation message
                    const existingMsg = this.parentNode.querySelector('.serial-validation-msg');
                    if (existingMsg) {
                        existingMsg.remove();
                    }

                    if (!serial) {
                        return; // No validation for empty serial
                    }

                    // If value hasn't changed from original, don't validate
                    if (serial === originalValue) {
                        return; // Don't validate if same as original value
                    }

                    // Show loading state
                    this.classList.add('border-blue-300');

                    // Debounce validation
                    validationTimeout = setTimeout(async () => {
                        const result = await checkSerialExists(serial, productId);

                        this.classList.remove('border-blue-300');

                        if (result.error) {
                            showSerialValidation(this, false, 'Lỗi kiểm tra serial');
                            return;
                        }

                        if (result.exists) {
                            showSerialValidation(this, false, result.message);
                        } else {
                            // Check if it's a valid message indicating it belongs to current assembly
                            if (result.message && result.message.includes(
                                    'thuộc assembly hiện tại')) {
                                showSerialValidation(this, true, result.message);
                            } else {
                                showSerialValidation(this, true, '');
                            }
                        }
                    }, 500); // Wait 500ms after user stops typing
                });

                // Also check on blur (only if value changed)
                input.addEventListener('blur', async function() {
                    const serial = this.value.trim();

                    // Don't validate if empty or same as original
                    if (!serial || serial === originalValue) return;

                    const result = await checkSerialExists(serial, productId);

                    if (result.error) {
                        showSerialValidation(this, false, 'Lỗi kiểm tra serial');
                        return;
                    }

                    if (result.exists) {
                        showSerialValidation(this, false, result.message);
                    } else {
                        // Check if it's a valid message indicating it belongs to current assembly
                        if (result.message && result.message.includes('thuộc assembly hiện tại')) {
                            showSerialValidation(this, true, result.message);
                        } else {
                            showSerialValidation(this, true, '');
                        }
                    }
                });
            }

            // Add validation to existing product serial inputs
            function addValidationToProductSerials() {
                // For multi-product assemblies
                @if ($assembly->products && $assembly->products->count() > 0)
                    @foreach ($assembly->products as $productIndex => $assemblyProduct)
                        const productSerialInputs{{ $productIndex }} = document.querySelectorAll(
                            'input[name*="products[{{ $productIndex }}][serials]"]');
                        productSerialInputs{{ $productIndex }}.forEach(input => {
                            addProductSerialValidation(input, {{ $assemblyProduct->product_id }});
                        });
                    @endforeach
                @endif
            }

            // Function to load material serials
            async function loadMaterialSerials(selectElement) {
                const materialId = selectElement.dataset.materialId;
                const warehouseId = selectElement.dataset.warehouseId;
                const currentSerial = selectElement.dataset.currentSerial;

                if (!materialId || !warehouseId) {
                    console.error('Missing material ID or warehouse ID');
                    return;
                }

                try {
                    const response = await fetch(
                        `{{ route('assemblies.material-serials') }}?material_id=${materialId}&warehouse_id=${warehouseId}&assembly_id={{ $assembly->id }}`, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            }
                        });

                    const data = await response.json();

                    if (data.success) {
                        // Clear existing options except the first one
                        selectElement.innerHTML = '<option value="">Chọn serial</option>';

                        // Add serial options
                        data.serials.forEach(serial => {
                            const option = document.createElement('option');
                            option.value = serial.serial_number;
                            option.textContent = serial.serial_number;

                            // Select current serial if it matches
                            if (currentSerial && serial.serial_number === currentSerial) {
                                option.selected = true;
                            }

                            selectElement.appendChild(option);
                        });
                    } else {
                        console.error('Error loading serials:', data.message);
                    }
                } catch (error) {
                    console.error('Error loading material serials:', error);
                }
            }

            // Function to initialize material serial selects
            function initializeMaterialSerialSelects() {
                const serialSelects = document.querySelectorAll('.material-serial-select');

                serialSelects.forEach(select => {
                    // Load serials when first opened
                    select.addEventListener('focus', function() {
                        if (!this.dataset.loaded) {
                            loadMaterialSerials(this);
                            this.dataset.loaded = 'true';
                        }
                    });

                    // Handle selection change
                    select.addEventListener('change', function() {
                        const selectedSerial = this.value;
                        this.dataset.currentSerial = selectedSerial;
                    });
                });
            }

            // Function to validate product serials form-wide (check for duplicates)
            function validateProductSerials() {
                // Reset all previous validation messages
                document.querySelectorAll('.serial-validation-msg').forEach(msg => msg.remove());
                document.querySelectorAll('input[name*="products"][name*="serials"]').forEach(input => {
                    input.classList.remove('border-red-500', 'bg-red-50');
                });

                // Track all serials by product
                const serialsByProduct = {};
                let hasError = false;

                // Collect all serials for each product
                document.querySelectorAll('input[name*="products"][name*="serials"]').forEach(input => {
                    // Extract product index from input name like "products[0][serials][0]"
                    const match = input.name.match(/products\[(\d+)\]/);
                    if (!match) return;

                    const productIndex = match[1];
                    const productId = input.closest('div[data-product-id]')?.dataset.productId ||
                        productIndex;
                    const serial = input.value.trim();

                    if (!serial) return; // Skip empty serials

                    if (!serialsByProduct[productId]) {
                        serialsByProduct[productId] = [];
                    }

                    // Check if this serial already exists for this product
                    if (serialsByProduct[productId].includes(serial)) {
                        // Show error on all inputs with this serial for this product
                        document.querySelectorAll(`input[name*="products[${productIndex}][serials]"]`)
                            .forEach(inp => {
                                if (inp.value.trim() === serial) {
                                    inp.classList.add('border-red-500', 'bg-red-50');
                                    const msgDiv = document.createElement('div');
                                    msgDiv.className =
                                        'serial-validation-msg text-xs text-red-600 mt-1';
                                    msgDiv.textContent =
                                        'Serial không được trùng lặp trong cùng một sản phẩm';
                                    inp.parentNode.appendChild(msgDiv);
                                }
                            });

                        hasError = true;
                    }

                    serialsByProduct[productId].push(serial);
                });

                return !hasError;
            }

            // Validate serials before form submission
            document.querySelector('form').addEventListener('submit', function(e) {
                if (!validateProductSerials()) {
                    e.preventDefault();
                    // Show error message at the top of the form
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'mb-4 p-3 bg-red-50 border border-red-300 rounded-lg text-red-700';
                    errorDiv.innerHTML =
                        '<i class="fas fa-exclamation-circle mr-2"></i>Vui lòng sửa lỗi trùng lặp serial sản phẩm trước khi lưu';
                    this.insertBefore(errorDiv, this.firstChild);

                    // Scroll to the top
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });

            // Add check for duplicate serials on input for product serials
            document.querySelectorAll('input[name*="products"][name*="serials"]').forEach(input => {
                input.addEventListener('blur', function() {
                    validateProductSerials();
                });
            });

            // Initialize validation for product serials (only add event listeners, don't validate immediately)
            addValidationToProductSerials();

            // Initialize material serial selects
            initializeMaterialSerialSelects();

            // Initialize product unit selectors
            function initializeProductUnitSelectors() {
                const productUnitSelects = document.querySelectorAll('select[name*="[product_unit]"]');

                productUnitSelects.forEach(select => {
                    // Apply initial styling
                    const unitValue = select.value;
                    select.classList.add('product-unit-select', `product-unit-${unitValue}`);

                    // Add visual indicator of product unit
                    const productId = select.getAttribute('data-product-id');
                    if (productId) {
                        // Find product serials
                        const productSerials = document.querySelectorAll(
                            `input[name*="products"][name*="serials"]`);
                        const productSerial = getProductSerialForUnit(productSerials, unitValue);

                        // Add indicator after select
                        const indicator = document.createElement('div');
                        indicator.className =
                            `product-unit-indicator product-unit-${unitValue}-bg mt-2 text-xs`;

                        if (productSerial && productSerial.value.trim()) {
                            indicator.innerHTML =
                                `<i class="fas fa-link mr-1"></i> Thành phẩm #${parseInt(unitValue) + 1} (Serial: <strong>${productSerial.value}</strong>)`;
                        } else {
                            indicator.innerHTML =
                                `<i class="fas fa-link mr-1"></i> Thành phẩm #${parseInt(unitValue) + 1}`;
                        }

                        // Add indicator after select element
                        select.insertAdjacentElement('afterend', indicator);
                    }

                    // Update styling when selection changes
                    select.addEventListener('change', function() {
                        // Remove old styling
                        const classes = Array.from(this.classList);
                        classes.forEach(cls => {
                            if (cls.match(/product-unit-\d+/)) {
                                this.classList.remove(cls);
                            }
                        });

                        // Add new styling based on selected value
                        const newUnitValue = this.value;
                        this.classList.add('product-unit-select', `product-unit-${newUnitValue}`);

                        // Update indicator
                        const nextElement = this.nextElementSibling;
                        if (nextElement && nextElement.classList.contains(
                                'product-unit-indicator')) {
                            // Remove old classes
                            const indicatorClasses = Array.from(nextElement.classList);
                            indicatorClasses.forEach(cls => {
                                if (cls.match(/product-unit-\d+-bg/)) {
                                    nextElement.classList.remove(cls);
                                }
                            });

                            // Add new class
                            nextElement.classList.add(`product-unit-${newUnitValue}-bg`);

                            // Update text
                            const productId = this.getAttribute('data-product-id');
                            if (productId) {
                                const productSerials = document.querySelectorAll(
                                    `input[name*="products"][name*="serials"]`);
                                const productSerial = getProductSerialForUnit(productSerials,
                                    newUnitValue);

                                if (productSerial && productSerial.value.trim()) {
                                    nextElement.innerHTML =
                                        `<i class="fas fa-link mr-1"></i> Thành phẩm #${parseInt(newUnitValue) + 1} (Serial: <strong>${productSerial.value}</strong>)`;
                                } else {
                                    nextElement.innerHTML =
                                        `<i class="fas fa-link mr-1"></i> Thành phẩm #${parseInt(newUnitValue) + 1}`;
                                }
                            }
                        }

                        // Update serials for this component based on product unit
                        updateComponentSerials(this);
                    });
                });
            }

            // Function to update component serials when product unit changes
            function updateComponentSerials(productUnitSelect) {
                const componentIndex = productUnitSelect.dataset.componentIndex;
                const materialId = productUnitSelect.dataset.materialId;
                const productId = productUnitSelect.dataset.productId;
                const productUnit = productUnitSelect.value;

                if (!componentIndex || !productId) return;

                // Find all serial selects for this component
                const row = productUnitSelect.closest('tr');
                if (!row) return;

                const serialContainer = row.querySelector('div[data-product-id]');
                if (!serialContainer) return;

                const serialSelects = serialContainer.querySelectorAll('.material-serial-select');

                // Update product-unit attribute for all serial selects
                serialSelects.forEach(select => {
                    select.dataset.productUnit = productUnit;

                    // Clear and reload the serial options
                    select.innerHTML = '<option value="">-- Chọn serial --</option>';

                    // Keep the current value if exists
                    const currentSerial = select.dataset.currentSerial;
                    if (currentSerial) {
                        const option = document.createElement('option');
                        option.value = currentSerial;
                        option.textContent = currentSerial;
                        option.selected = true;
                        select.appendChild(option);
                    }
                });
            }

            // Helper function to get product serial for a specific unit
            function getProductSerialForUnit(serialInputs, unitIndex) {
                // Convert serialInputs to array
                const inputsArray = Array.from(serialInputs);

                // If we have exactly the same number of serial inputs as the unit index + 1, 
                // then we can assume they're ordered correctly
                if (inputsArray.length > unitIndex) {
                    return inputsArray[unitIndex];
                }

                // Otherwise return null
                return null;
            }

            // Load and handle product serial dropdowns
            const productSerialSelects = document.querySelectorAll('.material-serial-select');

            productSerialSelects.forEach(select => {
                // Initialize the select options when clicked
                select.addEventListener('click', async function() {
                    // Skip if options are already loaded (more than just the default empty option)
                    if (this.options.length > 1) return;

                    const productId = this.dataset.productId;
                    const serialIndex = this.dataset.serialIndex;
                    const currentSerial = this.dataset.currentSerial;
                    const productUnit = this.dataset.productUnit || 0;

                    if (!productId) {
                        console.error('Missing product ID for serial select');
                        return;
                    }

                    try {
                        // Get a list of all serials already used in other dropdowns with the same product unit
                        const usedSerials = [];
                        const parentContainer = this.closest('div[data-product-id]');
                        if (parentContainer) {
                            const siblingSelects = parentContainer.querySelectorAll(
                                '.material-serial-select');
                            siblingSelects.forEach(sibling => {
                                if (sibling !== this && sibling.value && sibling.dataset
                                    .productUnit === productUnit) {
                                    usedSerials.push(sibling.value);
                                }
                            });
                        }

                        // Also get serials used by other components with the same product unit
                        document.querySelectorAll('.material-serial-select').forEach(
                            otherSelect => {
                                if (otherSelect !== this &&
                                    otherSelect.dataset.productId === productId &&
                                    otherSelect.dataset.productUnit === productUnit &&
                                    otherSelect.value) {
                                    usedSerials.push(otherSelect.value);
                                }
                            });

                        // Fetch serials for this product
                        const response = await fetch(
                            `{{ route('assemblies.product-serials') }}?product_id=${productId}&assembly_id={{ $assembly->id }}&product_unit=${productUnit}&exclude_serials=${JSON.stringify(usedSerials)}`, {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute(
                                        'content')
                                }
                            });

                        const data = await response.json();

                        if (data.success) {
                            // Add options to the select
                            let hasCurrentSerial = false;

                            // First, preserve the current value if it exists
                            if (currentSerial) {
                                const option = document.createElement('option');
                                option.value = currentSerial;
                                option.textContent = currentSerial;
                                option.selected = true;
                                this.appendChild(option);
                                hasCurrentSerial = true;
                            }

                            // Add all available serials
                            data.serials.forEach(serial => {
                                // Skip if this serial is already used in another select in this container
                                if (usedSerials.includes(serial.serial_number) && serial
                                    .serial_number !== currentSerial) {
                                    return;
                                }

                                const option = document.createElement('option');
                                option.value = serial.serial_number;
                                option.textContent = serial.serial_number;

                                // Select this option if it matches the current serial and we haven't already selected something
                                if (!hasCurrentSerial && currentSerial === serial
                                    .serial_number) {
                                    option.selected = true;
                                    hasCurrentSerial = true;
                                }

                                this.appendChild(option);
                            });
                        } else {
                            console.error('Error loading serials:', data.message);
                        }
                    } catch (error) {
                        console.error('Error loading product serials:', error);
                    }
                });
            });

            // Call the initialization function
            initializeProductUnitSelectors();
        });
        
        // Đồng bộ hóa đơn vị vật tư khi trang đã tải xong
        setTimeout(function() {
            console.log('Đồng bộ hóa đơn vị vật tư sau khi trang đã tải xong...');
            document.querySelectorAll('.product-unit-select').forEach(select => {
                window.handleProductUnitChange(select);
            });
        }, 1000);
    </script>
</body>

</html>
