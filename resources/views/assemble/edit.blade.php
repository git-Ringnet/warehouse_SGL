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
                                                                    <input type="text"
                                                                        name="components[{{ $component['globalIndex'] }}][serial]"
                                                                        value="{{ $component['material']->serial ?? '' }}"
                                                                        class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                        placeholder="Nhập serial">
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
                                                                <input type="text"
                                                                    name="components[{{ $index }}][serial]"
                                                                    value="{{ $material->serial ?? '' }}"
                                                                    class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                    placeholder="Nhập serial">
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                <input type="text"
                                                                    name="components[{{ $index }}][note]"
                                                                    value="{{ $material->note ?? '' }}"
                                                                    class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                    placeholder="Ghi chú">
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

            // Enhanced form validation to check for serial errors
            document.querySelector('form').addEventListener('submit', function(e) {
                // Only check for serial validation errors (not existing serials that haven't changed)
                const serialErrors = document.querySelectorAll('.serial-validation-msg');
                if (serialErrors.length > 0) {
                    e.preventDefault();
                    alert('Vui lòng sửa các lỗi serial trước khi cập nhật phiếu lắp ráp!');
                    // Focus on first error
                    const firstErrorInput = serialErrors[0].parentNode.querySelector('input');
                    if (firstErrorInput) {
                        firstErrorInput.focus();
                    }
                    return false;
                }

                // Check duplicate serials within this form only (not against database)
                // Group serials by product to allow same serial for different products
                const productSerialMap = new Map();
                let hasDuplicateSerials = false;

                // Check product serials (only within same product)
                const productSerialInputs = document.querySelectorAll('input[name*="products"][name*="serials"]');
                productSerialInputs.forEach(input => {
                    const serial = input.value.trim();
                    if (serial) {
                        // Extract product index from input name like "products[0][serials][0]"
                        const match = input.name.match(/products\[(\d+)\]/);
                        if (match) {
                            const productIndex = match[1];
                            
                            if (!productSerialMap.has(productIndex)) {
                                productSerialMap.set(productIndex, []);
                            }
                            
                            const productSerials = productSerialMap.get(productIndex);
                            if (productSerials.includes(serial)) {
                                hasDuplicateSerials = true;
                            } else {
                                productSerials.push(serial);
                            }
                        }
                    }
                });

                if (hasDuplicateSerials) {
                    e.preventDefault();
                    alert('Phát hiện trùng lặp serial trong cùng một thành phẩm. Vui lòng kiểm tra lại!');
                    return false;
                }

                return true;
            });

            // Initialize validation for product serials (only add event listeners, don't validate immediately)
            addValidationToProductSerials();
        });
    </script>
</body>

</html>
