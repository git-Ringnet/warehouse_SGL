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
    <script>
        // Store assembly status for quantity validation
        const ASSEMBLY_STATUS = '{{ $assembly->status }}';
        const IS_IN_PROGRESS = ASSEMBLY_STATUS === 'in_progress';
        // Counter for grouping deleted_components fields
        window.deletedComponentIdx = 0;
        
        console.log('Assembly Status:', ASSEMBLY_STATUS);
        console.log('IS_IN_PROGRESS:', IS_IN_PROGRESS);
    </script>
    <style>
        /* Styles for product units */
        .product-unit-0 {
            border-left: 4px solid #3b82f6;
        }

        .product-unit-1 {
            border-left: 4px solid #10b981;
        }

        .product-unit-2 {
            border-left: 4px solid #f59e0b;
        }

        .product-unit-3 {
            border-left: 4px solid #ef4444;
        }

        .product-unit-4 {
            border-left: 4px solid #8b5cf6;
        }

        .product-unit-5 {
            border-left: 4px solid #ec4899;
        }

        .product-unit-0-bg {
            background-color: rgba(59, 130, 246, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .product-unit-1-bg {
            background-color: rgba(16, 185, 129, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .product-unit-2-bg {
            background-color: rgba(245, 158, 11, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .product-unit-3-bg {
            background-color: rgba(239, 68, 68, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .product-unit-4-bg {
            background-color: rgba(139, 92, 246, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .product-unit-5-bg {
            background-color: rgba(236, 72, 153, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }
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
            <form id="assembly-form" action="{{ route('assemblies.update', $assembly->id) }}" method="POST">
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

                @if ($assembly->status === 'completed' || $assembly->status === 'cancelled')
                    <div class="mb-4 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                        <div class="flex items-center text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span class="font-medium">
                                @if ($assembly->status === 'completed')
                                    Phiếu lắp ráp đã hoàn thành, không thể chỉnh sửa.
                                @else
                                    Phiếu lắp ráp đã bị hủy, không thể chỉnh sửa.
                                @endif
                            </span>
                        </div>
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
                                class="block text-sm font-medium text-gray-700 mb-1 required">Ngày lắp ráp</label>
                            <input type="date" id="assembly_date" name="assembly_date" value="{{ $assembly->date }}"
                                required
                                {{ $assembly->status === 'completed' || $assembly->status === 'cancelled' ? 'disabled' : '' }}
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $assembly->status === 'completed' || $assembly->status === 'cancelled' ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Người phụ trách</label>
                            @if ($assembly->status === 'pending')
                                <div
                                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                    {{ $assembly->assignedEmployee->name ?? ($assembly->assigned_to ?? 'Chưa xác định') }}
                                </div>
                                <input type="hidden" name="assigned_to"
                                    value="{{ $assembly->assigned_employee_id ?? $assembly->assigned_to }}">
                            @else
                                <div
                                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                    {{ $assembly->assignedEmployee->name ?? ($assembly->assigned_to ?? 'Chưa xác định') }}
                                </div>
                                <input type="hidden" name="assigned_to"
                                    value="{{ $assembly->assigned_employee_id ?? $assembly->assigned_to }}">
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Người tiếp nhận kiểm thử</label>
                            @if ($assembly->status === 'pending')
                                <select name="tester_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach ($employees ?? [] as $emp)
                                        <option value="{{ $emp->id }}"
                                            {{ $assembly->tester_id == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div
                                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                    {{ $assembly->tester->name ?? 'Chưa phân công' }}
                                </div>
                                <input type="hidden" name="tester_id" value="{{ $assembly->tester_id }}">
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mục đích</label>
                            @if ($assembly->status === 'pending')
                                <select name="purpose" id="purpose-select"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="storage" {{ $assembly->purpose == 'storage' ? 'selected' : '' }}>Lưu
                                        kho</option>
                                    <option value="project" {{ $assembly->purpose == 'project' ? 'selected' : '' }}>
                                        Xuất đi dự án</option>
                                </select>
                            @else
                                <div
                                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                    {{ $assembly->purpose == 'storage' ? 'Lưu kho' : ($assembly->purpose == 'project' ? 'Xuất đi dự án' : 'Không xác định') }}
                                </div>
                                <input type="hidden" name="purpose" value="{{ $assembly->purpose }}">
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dự án</label>
                            @if ($assembly->status === 'pending')
                                <div id="project-select-wrapper"
                                    class="{{ $assembly->purpose === 'storage' ? 'hidden' : '' }}">
                                    <select name="project_id"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- Không chọn --</option>
                                        @foreach ($projects ?? [] as $prj)
                                            <option value="{{ $prj->id }}"
                                                {{ $assembly->project_id == $prj->id ? 'selected' : '' }}>
                                                {{ $prj->project_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                @if ($assembly->purpose == 'project' && $assembly->project)
                                    <div
                                        class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-700">
                                        {{ $assembly->project->project_name ?? 'Không xác định' }}
                                    </div>
                                    <input type="hidden" name="project_id" value="{{ $assembly->project_id }}">
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <div>
                            <label for="assembly_note" class="block text-sm font-medium text-gray-700 mb-1">Ghi
                                chú</label>
                            <textarea id="assembly_note" name="assembly_note" rows="2"
                                {{ $assembly->status === 'completed' || $assembly->status === 'cancelled' ? 'disabled' : '' }}
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $assembly->status === 'completed' || $assembly->status === 'cancelled' ? 'bg-gray-100 cursor-not-allowed' : '' }}">{{ $assembly->notes }}</textarea>
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

                    @if ($assembly->status === 'pending')
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="w-72">
                                <select id="product_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Chọn thành phẩm --</option>
                                    @foreach ($products ?? [] as $p)
                                        <option value="{{ $p->id }}">[{{ $p->code }}]
                                            {{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-24">
                                <input type="number" id="product_add_quantity" min="1" step="1"
                                    value="1"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <button type="button" id="add_product_btn"
                                    class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none">
                                    Thêm thành phẩm
                                </button>
                            </div>
                        </div>
                    @endif

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
                                        Serial</th>
                                    @if ($assembly->status === 'pending')
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thao tác</th>
                                    @endif
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
                                                @if ($assembly->status === 'pending')
                                                    <input type="number"
                                                        name="products[{{ $index }}][quantity]"
                                                        value="{{ $assemblyProduct->quantity }}" min="1"
                                                        class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-blue-500 product-qty-input"
                                                        data-index="{{ $index }}">
                                                @else
                                                    <div
                                                        class="w-20 border border-gray-200 bg-gray-50 rounded-lg px-2 py-1 text-center">
                                                        {{ $assemblyProduct->quantity }}</div>
                                                    <input type="hidden"
                                                        name="products[{{ $index }}][quantity]"
                                                        value="{{ $assemblyProduct->quantity }}">
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700">
                                                @if ($assembly->status === 'pending')
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
                                                @else
                                                    @php
                                                        $serials = [];
                                                        if ($assemblyProduct->serials) {
                                                            $serials = explode(',', $assemblyProduct->serials);
                                                        }
                                                    @endphp
                                                    @if (!empty($serials))
                                                        @if ($assemblyProduct->quantity > 1)
                                                            <div class="space-y-1">
                                                                @foreach ($serials as $serial)
                                                                    <input type="text" value="{{ $serial }}"
                                                                        readonly
                                                                        class="w-full border border-gray-200 bg-gray-50 rounded-lg px-2 py-1 text-sm cursor-not-allowed">
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <input type="text" value="{{ $serials[0] ?? '' }}"
                                                                readonly
                                                                class="w-full border border-gray-200 bg-gray-50 rounded-lg px-2 py-1 text-sm cursor-not-allowed">
                                                        @endif
                                                        @for ($i = 0; $i < $assemblyProduct->quantity; $i++)
                                                            <input type="hidden"
                                                                name="products[{{ $index }}][serials][]"
                                                                value="{{ $serials[$i] ?? '' }}">
                                                        @endfor
                                                    @else
                                                        <div class="text-sm text-gray-400 italic">Chưa có serial (không
                                                            thể chỉnh sửa ở trạng thái hiện tại)</div>
                                                        @for ($i = 0; $i < $assemblyProduct->quantity; $i++)
                                                            <input type="hidden"
                                                                name="products[{{ $index }}][serials][]"
                                                                value="">
                                                        @endfor
                                                    @endif
                                                @endif
                                            </td>
                                            @if ($assembly->status === 'pending')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <button type="button"
                                                        class="text-red-500 hover:text-red-700 delete-product-btn"
                                                        data-product-id="{{ $assemblyProduct->product_id }}"
                                                        title="Xóa thành phẩm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            @endif
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
                                            @if ($assembly->status === 'pending')
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
                                            @else
                                                @if (($assembly->quantity ?? 1) > 1)
                                                    <div class="space-y-1">
                                                        @for ($i = 0; $i < ($assembly->quantity ?? 1); $i++)
                                                            <div
                                                                class="text-sm text-gray-600 bg-gray-50 px-2 py-1 rounded">
                                                                {{ $productSerials[$i] ?? '' }}
                                                            </div>
                                                        @endfor
                                                    </div>
                                                @else
                                                    <div class="text-sm text-gray-600 bg-gray-50 px-2 py-1 rounded">
                                                        {{ $productSerials[0] ?? '' }}
                                                    </div>
                                                @endif
                                                @for ($i = 0; $i < ($assembly->quantity ?? 1); $i++)
                                                    <input type="hidden" name="products[0][serials][]"
                                                        value="{{ $productSerials[$i] ?? '' }}">
                                                @endfor
                                            @endif
                                        </td>
                                        @if ($assembly->status === 'pending')
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button type="button"
                                                    class="text-red-500 hover:text-red-700 delete-product-btn"
                                                    data-product-id="{{ $assembly->product_id }}"
                                                    title="Xóa thành phẩm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
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
                            <span class="text-sm">
                                @if ($assembly->status === 'pending')
                                    Chế độ chỉnh sửa: Có thể thêm/xóa thành phẩm, thêm/xóa vật tư; cập nhật số lượng và
                                    serial.
                                @elseif ($assembly->status === 'in_progress')
                                    Chế độ chỉnh sửa: Cho phép tăng số lượng linh kiện (không được giảm). Không thể thay đổi serial hay thêm/xóa linh kiện.
                                @else
                                    Chế độ chỉnh sửa: Chỉ có thể cập nhật ghi chú linh kiện. Không thể thay đổi serial
                                    hoặc thêm/xóa linh kiện.
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Component blocks container -->
                    <div id="component_blocks_container" class="mb-4">
                        @if ($assembly->status === 'pending')
                            <div class="mt-2 flex items-center space-x-2 mb-4">
                                <div class="flex-grow relative">
                                    <input type="text" id="component_search"
                                        placeholder="Nhập hoặc click để xem danh sách vật tư..."
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <div id="search_results"
                                        class="absolute bg-white mt-1 border border-gray-300 rounded-lg shadow-lg z-10 hidden w-full max-h-60 overflow-y-auto">
                                    </div>
                                </div>
                                <div class="w-48">
                                    <select id="component_product_id"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">--Thành phẩm--</option>
                                        @if ($assembly->products && $assembly->products->count() > 0)
                                            @foreach ($assembly->products as $productIndex => $assemblyProduct)
                                                <option value="{{ $assemblyProduct->product_id }}">
                                                    {{ $assemblyProduct->product->name }}</option>
                                            @endforeach
                                        @elseif($assembly->product)
                                            <option value="{{ $assembly->product->id }}">
                                                {{ $assembly->product->name }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="w-24">
                                    <input type="number" id="component_add_quantity" min="1" step="1"
                                        value="1"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <button id="add_component_btn" type="button"
                                        class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none opacity-50"
                                        disabled>Thêm</button>
                                </div>
                            </div>
                        @endif
                        @php
                            // Group components by product
                            $componentsByProduct = [];

                            // Group existing materials by target_product_id
                            foreach ($assembly->materials as $index => $material) {
                                $productId = $material->target_product_id;
                                $productKey = 'product_0'; // Default to product_0

                                // Map to product index for legacy compatibility
                                if ($assembly->products && $assembly->products->count() > 0) {
                                    foreach ($assembly->products as $productIndex => $assemblyProduct) {
                                        if ($assemblyProduct->product_id == $productId) {
                                            $productKey = "product_{$productIndex}";
                                            break;
                                        }
                                    }
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
                                @php
                                    // Sắp xếp linh kiện theo đơn vị thành phẩm để hiển thị theo từng đơn vị
                                    $components = collect($components)
                                        ->sortBy(function ($c) {
                                            return $c['material']->product_unit ?? 0;
                                        })
                                        ->values()
                                        ->all();
                                @endphp

                                <div class="mb-6 border border-gray-200 rounded-lg component-block"
                                    data-product-id="{{ $assemblyProduct->product_id }}">
                                <div class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center justify-between">
                                        <div class="font-medium text-blue-800 flex items-center">
                                            <i class="fas fa-box-open mr-2"></i>
                                            <span>Linh kiện cho: {{ $assemblyProduct->product->name }}</span>
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
                                                            Kho xuất</th>
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
                                                            <td colspan="7"
                                                                class="px-6 py-4 text-sm text-gray-500 text-center">
                                                                Chưa có linh kiện nào được thêm vào thành phẩm này
                                                            </td>
                                                        </tr>
                                                    @else
                                                        @php $prevUnit = null; @endphp
                                                        @foreach ($components as $component)
                                                            @php $unitIdx = $component['material']->product_unit ?? 0; @endphp
                                                            @if ($prevUnit !== $unitIdx)
                                                                <tr class="bg-green-50">
                                                                    <td colspan="7"
                                                                        class="px-6 py-2 text-sm font-medium text-green-800">
                                                                        Đơn vị thành phẩm {{ $unitIdx + 1 }}
                                                                    </td>
                                                                </tr>
                                                                @php $prevUnit = $unitIdx; @endphp
                                                            @endif
                                                            <tr class="component-row bg-white hover:bg-gray-50">
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    <input type="hidden"
                                                                        name="components[{{ $component['globalIndex'] }}][material_id]"
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
                                                                    @if ($assembly->status === 'pending' || $assembly->status === 'in_progress')
                                                                        <input type="number"
                                                                            name="components[{{ $component['globalIndex'] }}][quantity]"
                                                                            value="{{ $component['material']->quantity }}"
                                                                            min="{{ $assembly->status === 'in_progress' ? $component['material']->quantity : 1 }}"
                                                                            data-original-quantity="{{ $component['material']->quantity }}"
                                                                            class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                                    @else
                                                                        <div
                                                                            class="w-20 border border-gray-200 bg-gray-50 rounded-lg px-2 py-1 text-center">
                                                                            {{ $component['material']->quantity }}
                                                                        </div>
                                                                        <input type="hidden"
                                                                            name="components[{{ $component['globalIndex'] }}][quantity]"
                                                                            value="{{ $component['material']->quantity }}">
                                                                    @endif
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                    <div
                                                                        class="text-sm text-gray-600 bg-gray-50 px-2 py-1 rounded">
                                                                        {{ optional($component['material']->warehouse)->name ?? 'Không xác định' }}
                                                                    </div>
                                                                    <input type="hidden"
                                                                        name="components[{{ $component['globalIndex'] }}][warehouse_id]"
                                                                        value="{{ $component['material']->warehouse_id }}">
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                    @if ($assembly->status === 'pending')
                                                                        <div class="space-y-2 material-serial-selects-container"
                                                                            data-product-id="{{ $component['material']->target_product_id }}"
                                                                            data-product-index="{{ $productIndex }}">
                                                                            @php
                                                                                $serials = [];
                                                                                if ($component['material']->serial) {
                                                                                    $serials = explode(
                                                                                        ',',
                                                                                        $component['material']->serial,
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
                                                                                        data-warehouse-id="{{ $component['material']->warehouse_id }}"
                                                                                        data-current-serial="{{ $serials[$i] ?? '' }}"
                                                                                        data-product-id="{{ $component['material']->target_product_id }}"
                                                                                        data-product-unit="{{ $component['material']->product_unit ?? 0 }}">
                                                                                        <option value="">-- Chọn
                                                                                            serial {{ $i + 1 }}
                                                                                            --</option>
                                                                                        @php
                                                                                            $materialId =
                                                                                                $component['material']
                                                                                                    ->material_id;
                                                                                            $availableSerials =
                                                                                                $materialSerials[
                                                                                                    $materialId
                                                                                                ] ?? [];
                                                                                            $currentSerial =
                                                                                                $serials[$i] ?? '';
                                                                                        @endphp
                                                                                        @foreach ($availableSerials as $serial)
                                                                                            <option
                                                                                                value="{{ $serial['serial_number'] }}"
                                                                                                {{ $currentSerial == $serial['serial_number'] ? 'selected' : '' }}>
                                                                                                {{ $serial['serial_number'] }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            @endfor
                                                                        </div>
                                                                    @else
                                                                        @php
                                                                            $serials = [];
                                                                            if ($component['material']->serial) {
                                                                                $serials = explode(
                                                                                    ',',
                                                                                    $component['material']->serial,
                                                                                );
                                                                            }
                                                                        @endphp
                                                                        @for ($i = 0; $i < $component['material']->quantity; $i++)
                                                                            <div
                                                                                class="text-sm text-gray-600 bg-gray-50 px-2 py-1 rounded">
                                                                                {{ $serials[$i] ?? '' }}
                                                                            </div>
                                                                            <input type="hidden"
                                                                                name="components[{{ $component['globalIndex'] }}][serials][]"
                                                                                value="{{ $serials[$i] ?? '' }}">
                                                                        @endfor
                                                                    @endif
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                    <input type="text"
                                                                        name="components[{{ $component['globalIndex'] }}][note]"
                                                                        value="{{ $component['material']->note ?? '' }}"
                                                                        class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                        placeholder="Ghi chú">
                                                                    <input type="hidden"
                                                                        name="components[{{ $component['globalIndex'] }}][product_unit]"
                                                                        value="{{ $component['material']->product_unit ?? 0 }}"
                                                                        class="product-unit-select"
                                                                        data-component-index="{{ $component['globalIndex'] }}"
                                                                        data-material-id="{{ $component['material']->material_id }}"
                                                                        data-product-id="{{ $component['material']->target_product_id }}">
                                                                    @if ($assembly->status === 'pending')
                                                                        <button type="button"
                                                                            class="text-red-500 hover:text-red-700 ml-2 delete-component-btn"
                                                                            data-material-id="{{ $component['material']->material_id }}"
                                                                            data-product-id="{{ $assemblyProduct->product_id }}"
                                                                            data-unit-index="{{ $component['material']->product_unit ?? 0 }}"
                                                                            title="Xóa vật tư">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    @endif
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
                            <div class="mb-6 border border-gray-200 rounded-lg component-block"
                                data-product-id="{{ $assembly->product->id ?? 0 }}">
                                <div class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center justify-between">
                                    <div class="font-medium text-blue-800 flex items-center">
                                        <i class="fas fa-box-open mr-2"></i>
                                        <span>Linh kiện cho:
                                            {{ $assembly->product->name ?? 'Không có sản phẩm' }}</span>

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
                                                        Kho xuất</th>
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
                                                                    name="components[{{ $index }}][material_id]"
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
                                                                <div class="space-y-2 material-serial-selects-container"
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
                                                                                data-warehouse-id="{{ $material->warehouse_id }}"
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
                                                                <div
                                                                    class="text-sm text-gray-600 bg-gray-50 px-2 py-1 rounded">
                                                                    {{ optional($material->warehouse)->name ?? 'Không xác định' }}
                                                                </div>
                                                                <input type="hidden"
                                                                    name="components[{{ $index }}][warehouse_id]"
                                                                    value="{{ $material->warehouse_id }}">
                                                            </td>
                                                            <td
                                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                                <input type="text"
                                                                    name="components[{{ $index }}][note]"
                                                                    value="{{ $material->note ?? '' }}"
                                                                    class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                    placeholder="Ghi chú">
                                                                @if ($assembly->status === 'pending')
                                                                    <button type="button"
                                                                        class="text-red-500 hover:text-red-700 ml-2 delete-component-btn"
                                                                        data-material-id="{{ $material->material_id }}"
                                                                        data-product-id="{{ $assembly->product->id ?? 0 }}"
                                                                        data-unit-index="{{ $material->product_unit ?? 0 }}"
                                                                        title="Xóa vật tư">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                @endif
                                                            </td>
                                                            <input type="hidden"
                                                                name="components[{{ $index }}][product_unit]"
                                                                value="{{ $material->product_unit ?? 0 }}"
                                                                class="product-unit-select"
                                                                data-component-index="{{ $index }}"
                                                                data-material-id="{{ $material->material_id }}"
                                                                data-product-id="{{ $material->target_product_id }}">
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="7"
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
                    @if ($assembly->status !== 'completed' && $assembly->status !== 'cancelled')
                        <button type="submit" id="submit-btn"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-save mr-2"></i> Cập nhật phiếu
                        </button>
                    @else
                        <button type="button" disabled
                            class="bg-gray-400 cursor-not-allowed text-white px-5 py-2 rounded-lg flex items-center"
                            title="{{ $assembly->status == 'completed' ? 'Không thể cập nhật phiếu đã hoàn thành' : 'Không thể cập nhật phiếu đã hủy' }}">
                            <i class="fas fa-save mr-2"></i> Cập nhật phiếu
                        </button>
                    @endif
                </div>
            </form>
        </main>
    </div>

    <script>
        // Function to get product serial for specific unit

        /**
         * Hiển thị nút & banner "Tạo thành phẩm mới" cho block thành phẩm khi công thức thay đổi
         * @param {string|number} productId
         */
        function computeBlockFormula(block) {
            if (!block) return '';
            const rows = block.querySelectorAll('tr.component-row');
            const parts = [];
            rows.forEach(r => {
                const midInput = r.querySelector('input[name*="[material_id]"]') || r.querySelector(
                    'input[name*="[id]"]');
                const qtyInput = r.querySelector('input[name*="[quantity]"]');
                const unitInput = r.querySelector('input[name*="[product_unit]"]');
                const mid = midInput ? String(midInput.value).trim() : '';
                let qty = '';
                if (qtyInput) {
                    qty = String(qtyInput.value).trim();
                } else {
                    const qtyDiv = r.querySelector('.w-20.border.border-gray-200');
                    qty = qtyDiv ? String(qtyDiv.textContent).trim() : '';
                }
                const unit = unitInput ? String(unitInput.value).trim() : '0';
                if (mid) parts.push(`${mid}:${qty}:${unit}`);
            });
            parts.sort();
            return parts.join(',');
        }

        function setOriginalFormulaIfMissing(block) {
            if (!block) return;
            if (!block.getAttribute('data-original-formula')) {
                block.setAttribute('data-original-formula', computeBlockFormula(block));
            }
        }

        function setBlockOriginalFormula(block) {
            if (!block) return;
            block.setAttribute('data-original-formula', computeBlockFormula(block));
        }

        function updateCreateNewProductButton(productId) {
            const block = document.querySelector(`.component-block[data-product-id="${productId}"]`);
            if (!block) return;
            const headerBtn = block.querySelector('.create-new-product-btn');
            const existingBanner = block.querySelector('.duplicate-section');

            const currentFormula = computeBlockFormula(block);
            const originalFormula = block.getAttribute('data-original-formula') || '';
            const isSame = (currentFormula === originalFormula);

            if (isSame) {
                if (headerBtn) headerBtn.classList.add('hidden');
                if (existingBanner) existingBanner.remove();
                return;
            }

            if (headerBtn) headerBtn.classList.remove('hidden');
            if (!existingBanner) {
                const table = block.querySelector('table');
                if (!table) return;
                const duplicateSection = document.createElement('div');
                duplicateSection.className = 'bg-yellow-50 border-t border-yellow-200 p-3 duplicate-section hidden';
                duplicateSection.innerHTML =
                    `<div class=\"flex justify-between items-center\"><div class=\"text-sm text-yellow-700\"><i class=\"fas fa-info-circle mr-2\"></i>Bạn đã thay đổi công thức gốc. Bạn có thể tạo một thành phẩm mới với công thức này.</div><button type=\"button\" class=\"create-new-product-btn bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm\" data-product-id=\"${productId}\"><i class=\"fas fa-plus-circle mr-1\"></i> Tạo thành phẩm mới</button></div>`;
                table.parentNode.insertBefore(duplicateSection, table.nextSibling);
            }
        }

        function getProductSerialForUnit(productSerials, unitValue) {
            for (let i = 0; i < productSerials.length; i++) {
                const serial = productSerials[i];
                const name = serial.getAttribute('name');
                if (name && name.includes(`[${unitValue}]`)) {
                    return serial;
                }
            }
            return null;
        }

        document.addEventListener('DOMContentLoaded', function() {
                // Attach change listeners to existing material quantity inputs (server-rendered rows)
                function attachMaterialQtyListeners() {
                const quantityInputs = document.querySelectorAll('.component-row input[name^="components"][name*="quantity"]');
                console.log('Found quantity inputs:', quantityInputs.length);
                
                quantityInputs.forEach((inp, index) => {
                    console.log(`Input ${index}:`, inp.name, 'value:', inp.value);
                    
                        // tránh gán trùng listener
                    if (inp.dataset.listenerAttached) {
                        console.log('Listener already attached for:', inp.name);
                        return;
                    }
                        inp.dataset.listenerAttached = 'true';
                    
                    // Store original quantity for validation
                    inp.dataset.originalQuantity = inp.value;
                    console.log('Stored original quantity for:', inp.name, '=', inp.value);
                    
                        inp.addEventListener('change', function() {
                        console.log('=== QUANTITY CHANGE EVENT TRIGGERED ===');
                        console.log('Input name:', this.name);
                        console.log('New value:', this.value);
                        console.log('Original value:', this.dataset.originalQuantity);
                        console.log('IS_IN_PROGRESS:', IS_IN_PROGRESS);
                        
                        // If assembly is in progress, only allow increasing quantities
                        if (IS_IN_PROGRESS) {
                            const newQuantity = parseInt(this.value);
                            const originalQuantity = parseInt(this.dataset.originalQuantity || '0');
                            
                            console.log('Parsed values - new:', newQuantity, 'original:', originalQuantity);
                            console.log('Comparison result:', newQuantity < originalQuantity);
                            
                            if (newQuantity < originalQuantity) {
                                console.log('PREVENTING DECREASE - showing alert');
                                alert('Không thể giảm số lượng vật tư khi phiếu lắp ráp đang thực hiện. Chỉ được phép tăng số lượng.');
                                this.value = originalQuantity;
                                console.log('Value restored to:', this.value);
                                return;
                            } else {
                                console.log('Quantity change allowed');
                            }
                        } else {
                            console.log('Assembly not in progress - allowing all changes');
                        }
                        
                            const row = this.closest('.component-row');
                            if (!row) return;
                            const block = row.closest('.component-block');
                            if (!block) return;
                            const pid = block.getAttribute('data-product-id');
                            if (pid) {
                                setTimeout(() => updateCreateNewProductButton(pid), 100);
                            }
                        });
                    });
                }
                // attachMaterialQtyListeners(); // Temporarily disabled to avoid conflicts
                
                // Initialize original quantities for all existing quantity inputs
                function initializeOriginalQuantities() {
                    document.querySelectorAll('input[name*="components"][name*="quantity"]').forEach(input => {
                        if (!input.dataset.originalQuantity) {
                            input.dataset.originalQuantity = input.value;
                            console.log('Initialized original quantity for:', input.name, '=', input.value);
                        }
                    });
                }
                
                // Call initialization
                initializeOriginalQuantities();
                
                // Global event listener for all quantity inputs (backup)
                document.addEventListener('change', function(e) {
                    if (e.target.matches('input[name*="components"][name*="quantity"]')) {
                        console.log('Global quantity change detected:', e.target.name, 'value:', e.target.value, 'IS_IN_PROGRESS:', IS_IN_PROGRESS);
                        
                        // Ensure original quantity is stored
                        if (!e.target.dataset.originalQuantity) {
                            e.target.dataset.originalQuantity = e.target.value;
                            console.log('Stored original quantity in global listener for:', e.target.name, '=', e.target.value);
                        }
                        
                        // If assembly is in progress, only allow increasing quantities
                        if (IS_IN_PROGRESS) {
                            const newQuantity = parseInt(e.target.value);
                            const originalQuantity = parseInt(e.target.dataset.originalQuantity || '0');
                            
                            console.log('Global check - new:', newQuantity, 'original:', originalQuantity);
                            
                            if (newQuantity < originalQuantity) {
                                alert('Không thể giảm số lượng vật tư khi phiếu lắp ráp đang thực hiện. Chỉ được phép tăng số lượng.');
                                e.target.value = originalQuantity;
                                return;
                            }
                        }
                    }
                });
                
                // Global event listener for input events (catches arrow button clicks)
                document.addEventListener('input', function(e) {
                    if (e.target.matches('input[name*="components"][name*="quantity"]')) {
                        console.log('Global quantity input detected:', e.target.name, 'value:', e.target.value, 'IS_IN_PROGRESS:', IS_IN_PROGRESS);
                        
                        // Ensure original quantity is stored
                        if (!e.target.dataset.originalQuantity) {
                            e.target.dataset.originalQuantity = e.target.value;
                            console.log('Stored original quantity in global input listener for:', e.target.name, '=', e.target.value);
                        }
                        
                        // If assembly is in progress, only allow increasing quantities
                        if (IS_IN_PROGRESS) {
                            const newQuantity = parseInt(e.target.value);
                            const originalQuantity = parseInt(e.target.dataset.originalQuantity || '0');
                            
                            console.log('Global input check - new:', newQuantity, 'original:', originalQuantity);
                            
                            if (newQuantity < originalQuantity) {
                                alert('Không thể giảm số lượng vật tư khi phiếu lắp ráp đang thực hiện. Chỉ được phép tăng số lượng.');
                                e.target.value = originalQuantity;
                                return;
                            }
                        }
                    }
                });
            // Ghi nhận công thức gốc cho các block hiện có trên trang
            document.querySelectorAll('.component-block').forEach(block => {
                try {
                    setOriginalFormulaIfMissing(block);
                } catch (e) {}
            });
            // Toggle project selector by purpose when pending
            try {
                var purposeSelect = document.getElementById('purpose-select');
                var projectWrapper = document.getElementById('project-select-wrapper');
                if (purposeSelect && projectWrapper) {
                    var toggleProject = function() {
                        if (purposeSelect.value === 'project') {
                            projectWrapper.classList.remove('hidden');
                        } else {
                            projectWrapper.classList.add('hidden');
                            // Clear selection when switching to storage
                            var projSel = projectWrapper.querySelector('select[name="project_id"]');
                            if (projSel) projSel.value = '';
                        }
                    };
                    purposeSelect.addEventListener('change', toggleProject);
                    toggleProject();
                }
            } catch (e) {}

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
                            'input[name*="products[${productIndex}][serials]"]');
                        productSerialInputs{{ $productIndex }}.forEach(input => {
                            addProductSerialValidation(input, {{ $assemblyProduct->product_id }});

                            // Add real-time duplicate checking
                            input.addEventListener('input', function() {
                                checkDuplicateSerialsRealTime();
                            });
                        });
                    @endforeach
                @endif

                // For legacy single product assemblies
                const legacyProductSerialInputs = document.querySelectorAll('input[name*="products[0][serials]"]');
                legacyProductSerialInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        checkDuplicateSerialsRealTime();
                    });
                });
            }

            // Function to initialize material serial selects
            function initializeMaterialSerialSelects() {
                const serialSelects = document.querySelectorAll('.material-serial-select');

                serialSelects.forEach(select => {
                    // Handle selection change
                    select.addEventListener('change', handleSerialChange);
                });
            }

            // Function to check for duplicate serials in real-time
            function checkDuplicateSerialsRealTime() {
                // Clear previous error indicators and red borders
                document.querySelectorAll('.serial-error-indicator').forEach(el => el.remove());
                document.querySelectorAll('.border-red-500').forEach(el => {
                    if (el.classList.contains('border-gray-300')) {
                        el.classList.remove('border-red-500');
                    }
                });

                // Debug logging
                console.log('Checking for duplicate serials in real-time...');

                // Check product serials
                const productSerialInputs = document.querySelectorAll('input[name*="products"][name*="serials"]');
                const productSerials = {};

                productSerialInputs.forEach(input => {
                    const productId = input.name.match(/products\[(\d+)\]/)[1];
                    if (!productSerials[productId]) {
                        productSerials[productId] = [];
                    }
                    if (input.value && input.value.trim() !== '') {
                        productSerials[productId].push(input.value.trim());
                    }
                });

                // Check for duplicates within each product
                Object.keys(productSerials).forEach(productId => {
                    const serials = productSerials[productId];
                    const duplicates = serials.filter((item, index) => serials.indexOf(item) !== index);

                    if (duplicates.length > 0) {
                        productSerialInputs.forEach(input => {
                            const inputProductId = input.name.match(/products\[(\d+)\]/)[1];
                            if (inputProductId === productId && duplicates.includes(input.value
                                    .trim())) {
                                showSerialError(input, 'Serial trùng lặp');
                            }
                        });
                    }
                });

                // Check component serials
                const componentSerialSelects = document.querySelectorAll(
                    'select[name*="components"][name*="serials"]');
                const componentSerialInputs = document.querySelectorAll(
                    'input[name*="components"][name*="serials"]');
                console.log('Found component serial selects:', componentSerialSelects.length);
                console.log('Found component serial inputs:', componentSerialInputs.length);
                const componentSerials = {};

                // Process select elements
                componentSerialSelects.forEach(select => {
                    const componentIndex = select.name.match(/components\[(\d+)\]/)[1];
                    if (!componentSerials[componentIndex]) {
                        componentSerials[componentIndex] = [];
                    }
                    if (select.value && select.value.trim() !== '') {
                        componentSerials[componentIndex].push(select.value.trim());
                    }
                });

                // Process input elements
                componentSerialInputs.forEach(input => {
                    const componentIndex = input.name.match(/components\[(\d+)\]/)[1];
                    if (!componentSerials[componentIndex]) {
                        componentSerials[componentIndex] = [];
                    }
                    if (input.value && input.value.trim() !== '') {
                        componentSerials[componentIndex].push(input.value.trim());
                    }
                });

                // Check for duplicates within each component
                Object.keys(componentSerials).forEach(componentIndex => {
                    const serials = componentSerials[componentIndex];
                    const duplicates = serials.filter((item, index) => serials.indexOf(item) !== index);

                    console.log(`Component ${componentIndex} serials:`, serials, 'Duplicates:', duplicates);

                    if (duplicates.length > 0) {
                        componentSerialSelects.forEach(select => {
                            const selectComponentIndex = select.name.match(/components\[(\d+)\]/)[
                            1];
                            if (selectComponentIndex === componentIndex && duplicates.includes(
                                    select.value.trim())) {
                                showSerialError(select, 'Serial trùng lặp');
                            }
                        });
                    }
                });

                // Check for duplicate serials across different product units
                const allComponentSerials = [];
                
                // Process select elements
                componentSerialSelects.forEach(select => {
                    if (select.value && select.value.trim() !== '') {
                        const componentIndex = select.name.match(/components\[(\d+)\]/)[1];
                        const productUnit = select.getAttribute('data-product-unit') || '0';
                        
                        console.log('Serial select found:', {
                            name: select.name,
                            value: select.value,
                            componentIndex: componentIndex,
                            productUnit: productUnit,
                            dataProductUnit: select.getAttribute('data-product-unit')
                        });
                        
                        allComponentSerials.push({
                            serial: select.value.trim(),
                            componentIndex: componentIndex,
                            productUnit: productUnit,
                            element: select
                        });
                    }
                });

                // Process input elements
                componentSerialInputs.forEach(input => {
                    if (input.value && input.value.trim() !== '') {
                        const componentIndex = input.name.match(/components\[(\d+)\]/)[1];
                        // For input elements, we need to find the product unit from the hidden input
                        const row = input.closest('tr');
                        const productUnitInput = row ? row.querySelector('input[name*="product_unit"]') : null;
                        const productUnit = productUnitInput ? productUnitInput.value : '0';
                        
                        console.log('Serial input found:', {
                            name: input.name,
                            value: input.value,
                            componentIndex: componentIndex,
                            productUnit: productUnit
                        });
                        
                        allComponentSerials.push({
                            serial: input.value.trim(),
                            componentIndex: componentIndex,
                            productUnit: productUnit,
                            element: input
                        });
                    }
                });

                // Group serials by serial value and check for duplicates across units
                const serialGroups = {};
                allComponentSerials.forEach(item => {
                    if (!serialGroups[item.serial]) {
                        serialGroups[item.serial] = [];
                    }
                    serialGroups[item.serial].push(item);
                });

                // Check for duplicates across different product units
                Object.keys(serialGroups).forEach(serial => {
                    const items = serialGroups[serial];
                    if (items.length > 1) {
                        // Check if duplicates are in different product units
                        const productUnits = [...new Set(items.map(item => item.productUnit))];
                        console.log(`Checking serial ${serial}:`, {
                            items: items,
                            productUnits: productUnits,
                            isDuplicate: productUnits.length > 1
                        });
                        
                        if (productUnits.length > 1) {
                            console.log(`Found duplicate serial ${serial} across units:`, productUnits);
                            // Show error for all instances of this serial
                            items.forEach(item => {
                                showSerialError(item.element, `Serial trùng lặp (Đơn vị ${parseInt(item.productUnit) + 1})`);
                            });
                        }
                    }
                });
            }

            // Function to show serial error indicator
            function showSerialError(element, message) {
                // Remove existing error indicator
                const existingError = element.parentNode.querySelector('.serial-error-indicator');
                if (existingError) {
                    existingError.remove();
                }

                // Create error indicator
                const errorDiv = document.createElement('div');
                errorDiv.className = 'serial-error-indicator text-red-500 text-xs mt-1';
                errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle mr-1"></i>${message}`;

                // Add red border to element
                element.classList.add('border-red-500');

                // Insert error message after element
                element.parentNode.insertBefore(errorDiv, element.nextSibling);
            }

            // Initialize validation for product serials (only add event listeners, don't validate immediately)
            addValidationToProductSerials();

            // Initialize material serial selects
            initializeMaterialSerialSelects();

            // Function to handle quantity changes for in_progress assemblies
            function handleQuantityChange() {
                const quantityInputs = document.querySelectorAll('input[name*="[quantity]"]');

                quantityInputs.forEach(input => {
                    // Skip if already has listener attached
                    if (input.dataset.listenerAttached) {
                        console.log('Skipping handleQuantityChange for:', input.name, '- already has listener');
                        return;
                    }
                    
                    input.addEventListener('change', function() {
                        console.log('=== HANDLE QUANTITY CHANGE TRIGGERED ===');
                        console.log('Input:', this.name, 'New value:', this.value, 'IS_IN_PROGRESS:', IS_IN_PROGRESS);
                        
                        const newQuantity = parseInt(this.value);
                        
                        // If assembly is in progress, only allow increasing quantities
                        if (IS_IN_PROGRESS) {
                            const originalQuantity = parseInt(this.dataset.originalQuantity || this.getAttribute('min') || this.defaultValue || '0');
                            
                            console.log('handleQuantityChange check - new:', newQuantity, 'original:', originalQuantity);
                            
                            if (newQuantity < originalQuantity) {
                                console.log('handleQuantityChange PREVENTING DECREASE');
                                alert('Không thể giảm số lượng vật tư khi phiếu lắp ráp đang thực hiện. Chỉ được phép tăng số lượng.');
                                this.value = originalQuantity;
                            return;
                            }
                        }

                        // Find the serial container for this component
                        const row = this.closest('tr');
                        const serialContainer = row.querySelector(
                            '.material-serial-selects-container');

                        if (serialContainer) {
                            // Update the number of serial selects based on new quantity
                            updateSerialSelects(serialContainer, newQuantity);
                        }
                        
                        // Cập nhật button "Tạo thành phẩm mới" sau khi thay đổi số lượng
                        const componentBlock = row.closest('.component-block');
                        if (componentBlock) {
                            const productId = componentBlock.getAttribute('data-product-id');
                            if (productId) {
                                setTimeout(() => {
                                    updateCreateNewProductButton(productId);
                                }, 100);
                            }
                        }
                    });
                    
                    // Add input event listener to catch arrow button clicks
                    input.addEventListener('input', function() {
                        console.log('=== HANDLE QUANTITY INPUT TRIGGERED ===');
                        console.log('Input:', this.name, 'New value:', this.value, 'IS_IN_PROGRESS:', IS_IN_PROGRESS);
                        
                        const newQuantity = parseInt(this.value);
                        
                        // If assembly is in progress, only allow increasing quantities
                        if (IS_IN_PROGRESS) {
                            const originalQuantity = parseInt(this.dataset.originalQuantity || this.getAttribute('min') || this.defaultValue || '0');
                            
                            console.log('handleQuantityInput check - new:', newQuantity, 'original:', originalQuantity);
                            
                            if (newQuantity < originalQuantity) {
                                console.log('handleQuantityInput PREVENTING DECREASE');
                                alert('Không thể giảm số lượng vật tư khi phiếu lắp ráp đang thực hiện. Chỉ được phép tăng số lượng.');
                                this.value = originalQuantity;
                                return;
                            }
                        }
                    });
                });
            }

            // Function to update serial selects based on quantity
            function updateSerialSelects(container, newQuantity) {
                // Lấy template trước khi xóa
                const template = container.querySelector('.material-serial-select');
                if (!template) return;

                // Lưu lại các giá trị serial đã chọn
                const oldValues = [];
                container.querySelectorAll('.material-serial-select').forEach(select => {
                    oldValues.push(select.value);
                });

                // Lưu lại các data attributes cần thiết từ template
                const dataAttributes = {};
                Array.from(template.attributes).forEach(attr => {
                    if (attr.name.startsWith('data-')) {
                        dataAttributes[attr.name] = attr.value;
                    }
                });

                // Xóa toàn bộ select cũ
                container.innerHTML = '';
                for (let i = 0; i < newQuantity; i++) {
                    const newSelect = template.cloneNode(true);
                    newSelect.name = template.name.replace(/\[serials\]\[\d+\]/, `[serials][${i}]`);
                    newSelect.setAttribute('data-serial-index', i);
                    // Gán lại các data attributes
                    Object.entries(dataAttributes).forEach(([key, value]) => {
                        newSelect.setAttribute(key, value);
                    });
                    // Cập nhật option text
                    const option = newSelect.querySelector('option[value=""]');
                    if (option) {
                        option.textContent = `-- Chọn serial ${i + 1} --`;
                    }
                    // Gán lại giá trị cũ nếu có
                    if (typeof oldValues[i] !== 'undefined') {
                        newSelect.value = oldValues[i];
                    } else {
                        newSelect.value = '';
                    }
                    newSelect.removeEventListener('change', handleSerialChange);
                    newSelect.addEventListener('change', handleSerialChange);

                    // Bọc trong div để hiển thị dọc
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex items-center';
                    wrapper.appendChild(newSelect);
                    container.appendChild(wrapper);
                }
            }

            // Function to handle serial change events
            function handleSerialChange() {
                const selectedSerial = this.value;
                this.dataset.currentSerial = selectedSerial;

                // Check for duplicates in real-time
                checkDuplicateSerialsRealTime();
            }

            // Function to create a new serial select
            function createSerialSelect(index, container) {
                const template = container.querySelector('.material-serial-select');
                if (!template) return null;

                const newSelect = template.cloneNode(true);
                newSelect.name = template.name.replace(/\[\d+\]/, `[${index}]`);
                newSelect.setAttribute('data-serial-index', index);
                newSelect.value = '';

                // Copy all data attributes from template
                const dataAttributes = ['data-material-id', 'data-warehouse-id', 'data-product-id',
                    'data-product-unit'
                ];
                dataAttributes.forEach(attr => {
                    if (template.hasAttribute(attr)) {
                        newSelect.setAttribute(attr, template.getAttribute(attr));
                    }
                });

                // Update the option text
                const option = newSelect.querySelector('option[value=""]');
                if (option) {
                    option.textContent = `-- Chọn serial ${index + 1} --`;
                }

                // Add event listener for duplicate validation
                newSelect.addEventListener('change', handleSerialChange);

                return newSelect;
            }

            // Function to validate duplicate serials
            function validateDuplicateSerials() {
                let hasError = false;
                const errorMessages = [];

                // Check for duplicate serials in products
                const productSerialInputs = document.querySelectorAll('input[name*="products"][name*="serials"]');
                const productSerials = {};

                productSerialInputs.forEach(input => {
                    const productId = input.name.match(/products\[(\d+)\]/)[1];
                    if (!productSerials[productId]) {
                        productSerials[productId] = [];
                    }
                    if (input.value && input.value.trim() !== '') {
                        productSerials[productId].push(input.value.trim());
                    }
                });

                // Check for duplicates within each product
                Object.keys(productSerials).forEach(productId => {
                    const serials = productSerials[productId];
                    const uniqueSerials = new Set(serials);
                    if (serials.length !== uniqueSerials.size) {
                        hasError = true;
                        errorMessages.push(`Thành phẩm #${parseInt(productId) + 1}: Có serial trùng lặp`);
                    }
                });

                // Check for duplicate serials in components
                const componentSerialSelects = document.querySelectorAll(
                    'select[name*="components"][name*="serials"]');
                const componentSerialInputs = document.querySelectorAll(
                    'input[name*="components"][name*="serials"]');
                const componentSerials = {};

                console.log('Found component serial selects:', componentSerialSelects.length);
                console.log('Found component serial inputs:', componentSerialInputs.length);

                // Process select elements
                componentSerialSelects.forEach(select => {
                    const componentIndex = select.name.match(/components\[(\d+)\]/)[1];
                    if (!componentSerials[componentIndex]) {
                        componentSerials[componentIndex] = [];
                    }
                    if (select.value && select.value.trim() !== '') {
                        componentSerials[componentIndex].push(select.value.trim());
                    }
                    console.log(`Select ${select.name}: value="${select.value}"`);
                });

                // Process input elements
                componentSerialInputs.forEach(input => {
                    const componentIndex = input.name.match(/components\[(\d+)\]/)[1];
                    if (!componentSerials[componentIndex]) {
                        componentSerials[componentIndex] = [];
                    }
                    if (input.value && input.value.trim() !== '') {
                        componentSerials[componentIndex].push(input.value.trim());
                    }
                    console.log(`Input ${input.name}: value="${input.value}"`);
                });

                // Check for duplicates within each component
                Object.keys(componentSerials).forEach(componentIndex => {
                    const serials = componentSerials[componentIndex];
                    const uniqueSerials = new Set(serials);
                    if (serials.length !== uniqueSerials.size) {
                        hasError = true;
                        errorMessages.push(
                            `Linh kiện #${parseInt(componentIndex) + 1}: Có serial trùng lặp`);
                    }
                });

                // Check for duplicate serials across different product units
                const allComponentSerials = [];
                
                // Process select elements
                componentSerialSelects.forEach(select => {
                    if (select.value && select.value.trim() !== '') {
                        const componentIndex = select.name.match(/components\[(\d+)\]/)[1];
                        const productUnit = select.getAttribute('data-product-unit') || '0';
                        
                        allComponentSerials.push({
                            serial: select.value.trim(),
                            componentIndex: componentIndex,
                            productUnit: productUnit
                        });
                    }
                });

                // Process input elements
                componentSerialInputs.forEach(input => {
                    if (input.value && input.value.trim() !== '') {
                        const componentIndex = input.name.match(/components\[(\d+)\]/)[1];
                        // For input elements, we need to find the product unit from the hidden input
                        const row = input.closest('tr');
                        const productUnitInput = row ? row.querySelector('input[name*="product_unit"]') : null;
                        const productUnit = productUnitInput ? productUnitInput.value : '0';
                        
                        allComponentSerials.push({
                            serial: input.value.trim(),
                            componentIndex: componentIndex,
                            productUnit: productUnit
                        });
                    }
                });

                // Group serials by serial value and check for duplicates across units
                const serialGroups = {};
                allComponentSerials.forEach(item => {
                    if (!serialGroups[item.serial]) {
                        serialGroups[item.serial] = [];
                    }
                    serialGroups[item.serial].push(item);
                });

                // Check for duplicates across different product units
                Object.keys(serialGroups).forEach(serial => {
                    const items = serialGroups[serial];
                    if (items.length > 1) {
                        // Check if duplicates are in different product units
                        const productUnits = [...new Set(items.map(item => item.productUnit))];
                        if (productUnits.length > 1) {
                            hasError = true;
                            const unitInfo = productUnits.map(unit => `Đơn vị ${parseInt(unit) + 1}`).join(', ');
                            errorMessages.push(`Serial '${serial}' được sử dụng ở nhiều đơn vị thành phẩm: ${unitInfo}`);
                        }
                    }
                });

                // Debug logging
                console.log('Validation check:', {
                    productSerials: productSerials,
                    componentSerials: componentSerials,
                    allComponentSerials: allComponentSerials,
                    serialGroups: serialGroups,
                    hasError: hasError,
                    errorMessages: errorMessages
                });

                if (hasError) {
                    alert('Phát hiện trùng lặp serial:\n\n' + errorMessages.join('\n'));
                    return false;
                }

                return true;
            }

            // Add validation to form submit
            document.getElementById('assembly-form').addEventListener('submit', function(e) {
                if (!validateDuplicateSerials()) {
                    e.preventDefault();
                    return false;
                }
            });

            // Initialize quantity change handlers
            handleQuantityChange();
            
            // Add event listeners for existing component quantity inputs
            document.addEventListener('change', function(e) {
                if (e.target.matches('input[name*="components"][name*="quantity"]')) {
                    console.log('Global quantity change detected:', e.target.value, 'IS_IN_PROGRESS:', IS_IN_PROGRESS);
                    
                    // If assembly is in progress, only allow increasing quantities
                    if (IS_IN_PROGRESS) {
                        const newQuantity = parseInt(e.target.value);
                        const originalQuantity = parseInt(e.target.dataset.originalQuantity || '0');
                        
                        console.log('Global check - new:', newQuantity, 'original:', originalQuantity);
                        
                        if (newQuantity < originalQuantity) {
                            alert('Không thể giảm số lượng vật tư khi phiếu lắp ráp đang thực hiện. Chỉ được phép tăng số lượng.');
                            e.target.value = originalQuantity;
                            return;
                        }
                    }
                    
                    const row = e.target.closest('tr');
                    if (row) {
                        const componentBlock = row.closest('.component-block');
                        if (componentBlock) {
                            const productId = componentBlock.getAttribute('data-product-id');
                            if (productId) {
                                setTimeout(() => {
                                    updateCreateNewProductButton(productId);
                                }, 100);
                            }
                        }
                    }
                }
            });
            
            // Add event listeners for existing product quantity inputs
            document.addEventListener('change', function(e) {
                if (e.target.matches('input[name*="products"][name*="quantity"]')) {
                    const row = e.target.closest('tr');
                    if (row) {
                        const productId = row.getAttribute('data-product-id');
                        if (productId) {
                            setTimeout(() => {
                                updateCreateNewProductButton(productId);
                            }, 100);
                        }
                    }
                }
            });
            
            // Add event listeners for existing component quantity inputs
            document.addEventListener('change', function(e) {
                if (e.target.matches('input[name*="components"][name*="quantity"]')) {
                    const row = e.target.closest('tr');
                    if (row) {
                        const componentBlock = row.closest('.component-block');
                        if (componentBlock) {
                            const productId = componentBlock.getAttribute('data-product-id');
                            if (productId) {
                                setTimeout(() => {
                                    updateCreateNewProductButton(productId);
                                }, 100);
                            }
                        }
                    }
                }
            });

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
                    });
                });
            }

            // Call the initialization function
            initializeProductUnitSelectors();

            // Enable dynamic add/remove when pending
            @if ($assembly->status === 'pending')
                // Delete existing product
                document.querySelectorAll('.delete-product-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const pid = this.getAttribute('data-product-id');
                        const form = document.getElementById('assembly-form');
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'deleted_products[]';
                        hidden.value = pid;
                        form.appendChild(hidden);
                        const row = this.closest('tr');
                        if (row) row.remove();

                        // Remove related component rows and mark deletions
                        document.querySelectorAll('tr.component-row').forEach(crow => {
                            const targetPidInput = crow.querySelector(
                                'input[name*="[target_product_id]"]');
                            const unitHidden = crow.querySelector(
                                'input.product-unit-select[data-product-id]');
                            const matches = (targetPidInput && targetPidInput.value ==
                                pid) || (unitHidden && unitHidden.getAttribute(
                                    'data-product-id') == pid);
                            if (matches) {
                                const midInput = crow.querySelector(
                                        'input[name*="[material_id]"]') || crow
                                    .querySelector('input[name*="[id]"]');
                                const unitInput = crow.querySelector(
                                    'input[name*="[product_unit]"]');
                                if (midInput) {
                                    const delIdx = (window.deletedComponentIdx = (window.deletedComponentIdx || 0) + 1);
                                    const h1 = document.createElement('input');
                                    h1.type = 'hidden';
                                    h1.name = `deleted_components[${delIdx}][material_id]`;
                                    h1.value = midInput.value;
                                    form.appendChild(h1);
                                    const h2 = document.createElement('input');
                                    h2.type = 'hidden';
                                    h2.name = `deleted_components[${delIdx}][target_product_id]`;
                                    h2.value = pid;
                                    form.appendChild(h2);
                                    const h3 = document.createElement('input');
                                    h3.type = 'hidden';
                                    h3.name = `deleted_components[${delIdx}][product_unit]`;
                                    h3.value = unitInput ? unitInput.value : '';
                                    form.appendChild(h3);
                                }
                                crow.remove();
                            }
                        });
                        // Remove the whole component block for this product if exists
                        document.querySelectorAll(`.component-block[data-product-id="${pid}"]`)
                            .forEach(block => block.remove());
                    });
                });

                // Add product
                (function() {
                    const addProductBtn = document.getElementById('add_product_btn');
                    if (!addProductBtn) return;
                    const productSelect = document.getElementById('product_id');
                    const qtyInput = document.getElementById('product_add_quantity');
                    const productList = document.getElementById('product_list');
                    const blocksContainer = document.getElementById('component_blocks_container');

                    // Warehouses + serial helpers
                    const warehousesData = @json($warehouses ?? []);
                    async function fetchMaterialSerials(materialId, warehouseId) {
                        const url = `{{ route('assemblies.material-serials') }}?` + new URLSearchParams({
                            material_id: materialId,
                            warehouse_id: warehouseId
                        });
                        const resp = await fetch(url);
                        if (!resp.ok) throw new Error('Network error');
                        const data = await resp.json();
                        return Array.isArray(data.serials) ? data.serials : [];
                    }

                    function addWarehouseSelectToCell(cell, component, index) {
                        cell.innerHTML = '';
                        const select = document.createElement('select');
                        select.name = `components[${index}][warehouse_id]`;
                        select.required = true;
                        select.className =
                            'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 warehouse-select';
                        const def = document.createElement('option');
                        def.value = '';
                        def.textContent = '-- Chọn kho --';
                        select.appendChild(def);
                        warehousesData.forEach(w => {
                            const o = document.createElement('option');
                            o.value = w.id;
                            o.textContent = `${w.name} (${w.code})`;
                            if (component.warehouseId && parseInt(component.warehouseId) === parseInt(w
                                    .id)) o.selected = true;
                            select.appendChild(o);
                        });
                        select.addEventListener('change', function() {
                            component.warehouseId = this.value;
                            const serialCell = cell.parentElement.cells[5];
                            if (serialCell) addSerialInputsToCell(serialCell, component, index);
                        });
                        cell.appendChild(select);
                    }
                    async function addSerialInputsToCell(cell, component, index) {
                        cell.innerHTML = '';
                        const q = parseInt(component.quantity || 1);
                        if (!component.warehouseId) {
                            cell.innerHTML =
                                '<div class="text-sm text-gray-400">Chọn kho để tải serial</div>';
                            return;
                        }
                        try {
                            const serials = await fetchMaterialSerials(component.id, component.warehouseId);
                            for (let i = 0; i < q; i++) {
                                const wrap = document.createElement('div');
                                wrap.className = 'flex items-center mb-2 last:mb-0';
                                const sel = document.createElement('select');
                                sel.name = `components[${index}][serials][]`;
                                sel.className =
                                    'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 material-serial-select';
                                const o0 = document.createElement('option');
                                o0.value = '';
                                o0.textContent = `-- Chọn serial ${i+1} --`;
                                sel.appendChild(o0);
                                serials.forEach(s => {
                                    const o = document.createElement('option');
                                    o.value = s.serial_number || s;
                                    o.textContent = (s.serial_number || s);
                                    sel.appendChild(o);
                                });
                                sel.addEventListener('change', () => {
                                    if (typeof checkDuplicateSerialsRealTime === 'function')
                                        checkDuplicateSerialsRealTime();
                                });
                                wrap.appendChild(sel);
                                cell.appendChild(wrap);
                            }
                        } catch (e) {
                            cell.innerHTML = '<div class="text-sm text-red-500">Lỗi tải serial</div>';
                        }
                    }
                    let compAutoIdx = 0;

                    function nextComponentIndex() {
                        compAutoIdx += 1;
                        return `${Date.now()}_${compAutoIdx}`;
                    }

                    function getOrCreateSerialContainer(row) {
                        // Serial column is the 4th cell
                        const serialTd = row && row.cells ? row.cells[3] : row.querySelector('td:nth-child(4)');
                        if (!serialTd) return null;
                        let container = serialTd.querySelector('.serials-container');
                        if (!container) {
                            // create container and move any existing direct inputs into it
                            container = document.createElement('div');
                            container.className = 'serials-container space-y-2';
                            // move existing inputs/text fields if present
                            const existingInputs = Array.from(serialTd.querySelectorAll('input[type="text"]'));
                            serialTd.innerHTML = '';
                            serialTd.appendChild(container);
                            existingInputs.forEach(inp => {
                                const wrap = document.createElement('div');
                                wrap.className = 'mb-2 last:mb-0';
                                wrap.appendChild(inp);
                                container.appendChild(wrap);
                            });
                        }
                        return container;
                    }

                    function generateProductSerialInputs(row, productIndex, productId, quantity,
                        previousValues = []) {
                        const container = getOrCreateSerialContainer(row);
                        if (!container) return;
                        // preserve existing values if not provided
                        if (!previousValues.length) {
                            const existing = Array.from(container.querySelectorAll('input[type="text"]'));
                            previousValues = existing.map(i => i.value);
                        }
                        container.innerHTML = '';
                        for (let i = 0; i < quantity; i++) {
                            const wrap = document.createElement('div');
                            wrap.className = 'mb-2 last:mb-0';
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.name = `products[${productIndex}][serials][]`;
                            input.value = typeof previousValues[i] !== 'undefined' ? previousValues[i] : '';
                            input.placeholder = quantity > 1 ? `Serial ${i + 1} (tùy chọn)` :
                                'Serial (tùy chọn)';
                            input.className =
                                'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
                            if (typeof addProductSerialValidation === 'function') {
                                addProductSerialValidation(input, parseInt(productId));
                            }
                            input.addEventListener('input', () => {
                                if (typeof checkDuplicateSerialsRealTime === 'function') {
                                    checkDuplicateSerialsRealTime();
                                }
                            });
                            wrap.appendChild(input);
                            container.appendChild(wrap);
                        }
                    }

                    // bind quantity change for all current rows (server-rendered)
                    function bindQtyChangeHandlers() {
                        document.querySelectorAll('#product_list .product-qty-input').forEach(q => {
                            q.addEventListener('change', function() {
                                let newQty = parseInt(this.value || '1');
                                if (isNaN(newQty) || newQty < 1) {
                                    newQty = 1;
                                    this.value = '1';
                                }
                                const row = this.closest('tr');
                                const idxMatch = (this.getAttribute('name') || '').match(
                                    /products\[(\d+)\]\[quantity\]/);
                                const productIndex = idxMatch ? parseInt(idxMatch[1]) : null;
                                const productId = row ? row.getAttribute('data-product-id') :
                                    null;
                                if (productIndex !== null && productId) {
                                    const existingValues = Array.from(row.querySelectorAll(
                                        '.space-y-2 input[type="text"]')).map(i => i.value);
                                    generateProductSerialInputs(row, productIndex, productId,
                                        newQty, existingValues);
                                    // if there is a dynamic block for this product, reload default materials for all units count
                                    const titleEl = document.querySelector(
                                        `.component-block[data-product-id="${productId}"] .bg-blue-50 span`
                                        );
                                    if (titleEl) {
                                        const nameText = titleEl.textContent.replace(
                                            'Linh kiện cho: ', '');
                                        try {
                                            // best-effort: re-fetch to reflect unit headers count
                                            fetch(
                                                    `{{ url('/assemblies/product-materials') }}/${productId}`)
                                                .then(r => r.json()).then(data => {
                                                    const block = document.querySelector(
                                                        `.component-block[data-product-id="${productId}"]`
                                                        );
                                                if (!block) return;
                                                    const tbody = block.querySelector(
                                                        'tbody');
                                                if (!tbody) return;
                                                tbody.innerHTML = '';
                                                    const materials = Array.isArray(data
                                                        .materials) ? data.materials :
                                                    [];
                                                    if (materials.length === 0) {
                                                        const tr = document.createElement(
                                                            'tr');
                                                        tr.innerHTML =
                                                            '<td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">Thành phẩm này không có linh kiện định sẵn</td>';
                                                        tbody.appendChild(tr);
                                                        return;
                                                    }
                                                    for (let unitIndex = 0; unitIndex <
                                                        newQty; unitIndex++) {
                                                        const header = document
                                                            .createElement('tr');
                                                        header.className = 'bg-green-50';
                                                        header.innerHTML =
                                                            `<td colspan="7" class="px-6 py-2 text-sm font-medium text-green-800">Đơn vị thành phẩm ${unitIndex+1}</td>`;
                                                        tbody.appendChild(header);
                                                        materials.forEach(m => {
                                                            const idx = Date.now() +
                                                                '' + unitIndex +
                                                                Math.random()
                                                                .toString(36).slice(
                                                                    2, 7);
                                                            const row = document
                                                                .createElement(
                                                                'tr');
                                                            row.className =
                                                                'component-row bg-white hover:bg-gray-50';
                                                            row.innerHTML =
                                                                `
                                                            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900\">
                                                                <input type=\"hidden\" name=\"components[${idx}][id]\" value=\"${m.id}\">
                                                                <input type=\"hidden\" name=\"components[${idx}][material_id]\" value=\"${m.id}\">
                                                                <input type=\"hidden\" name=\"components[${idx}][target_product_id]\" value=\"${productId}\">
                                                                <input type=\"hidden\" name=\"components[${idx}][product_unit]\" value=\"${unitIndex}\">
                                                                ${m.code}
                                                            </td>
                                                            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\">${m.category||''}</td>
                                                            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\">${m.name}</td>
                                                            <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\"><input type=\"number\" name=\"components[${idx}][quantity]\" value=\"${m.quantity||1}\" min=\"1\" class=\"w-20 border border-gray-300 rounded-lg px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-blue-500\"></td>
                                                    <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\"></td>
                                                    <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\"></td>
                                                    <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\"><input type=\"text\" name=\"components[${idx}][note]\" class=\"w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500\" placeholder=\"Ghi chú\"> <button type=\"button\" class=\"text-red-500 hover:text-red-700 ml-2 delete-component-btn\" data-material-id=\"${m.id}\" data-product-id=\"${productId}\" data-unit-index=\"${unitIndex}\"><i class=\"fas fa-trash\"></i></button></td>`;
                                                        tbody.appendChild(row);
                                                    // add warehouse + serial selectors
                                                            const wCell = row.cells[
                                                                4];
                                                            const sCell = row.cells[
                                                                5];
                                                            const compObj = {
                                                                id: m.id,
                                                                quantity: (m
                                                                    .quantity ||
                                                                    1),
                                                                warehouseId: null
                                                            };
                                                            addWarehouseSelectToCell
                                                                (wCell, compObj,
                                                                    idx);
                                                            addSerialInputsToCell(
                                                                sCell, compObj,
                                                                idx);
                                                        })
                                                    }
                                                    // Lưu lại công thức gốc sau khi render mặc định theo số lượng mới
                                                    try {
                                                        setBlockOriginalFormula(block);
                                                    } catch (e) {}
                                                
                                                // Cập nhật button "Tạo thành phẩm mới" sau khi thay đổi số lượng thành phẩm
                                                setTimeout(() => {
                                                        updateCreateNewProductButton
                                                            (productId);
                                                }, 100);
                                            });
                                        } catch (e) {}
                                    }
                                }
                            });
                        });
                    }
                    // Normalize serial cells for existing rows and bind handlers
                    document.querySelectorAll('#product_list tr.product-row').forEach(row => {
                        // create container and keep existing inputs
                        getOrCreateSerialContainer(row);
                    });
                    bindQtyChangeHandlers();
                    addProductBtn.addEventListener('click', function() {
                        const pid = productSelect && productSelect.value;
                        const qty = parseInt(qtyInput && qtyInput.value ? qtyInput.value : '1');
                        if (!pid) {
                            alert('Vui lòng chọn thành phẩm');
                            return;
                        }
                        if (document.querySelector(
                                `#product_list tr.product-row[data-product-id="${pid}"]`)) {
                            alert('Thành phẩm đã có trong danh sách.');
                            return;
                        }
                        const opt = productSelect.options[productSelect.selectedIndex];
                        const text = opt ? opt.text : '';
                        const codeMatch = text.match(/\[(.*?)\]/);
                        const code = codeMatch ? codeMatch[1] : '';
                        const name = text.replace(/\[.*?\]\s*/, '');
                        const index = document.querySelectorAll('#product_list tr.product-row').length;
                        const tr = document.createElement('tr');
                        tr.className = 'product-row bg-white hover:bg-gray-50';
                        tr.setAttribute('data-product-id', pid);
                        tr.innerHTML =
                            `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${code}
                            <input type="hidden" name="products[${index}][id]" value="${pid}">
                            <input type="hidden" name="products[${index}][code]" value="${code}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><input type="number" name="products[${index}][quantity]" value="${qty}" min="1" class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-blue-500 product-qty-input" data-index="${index}"></td>
                        <td class="px-6 py-4 text-sm text-gray-700"><div class="space-y-2"></div></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><button type="button" class="text-red-500 hover:text-red-700 delete-product-btn" data-product-id="${pid}"><i class="fas fa-trash"></i></button></td>`;
                        productList.appendChild(tr);
                        // build serial inputs for the new product
                        generateProductSerialInputs(tr, index, pid, qty);
                        // also create component block for this product so user can thêm vật tư
                        if (blocksContainer) {
                            const block = document.createElement('div');
                            block.className = 'mb-6 border border-gray-200 rounded-lg component-block';
                            block.setAttribute('data-product-id', pid);
                            block.innerHTML = `
                                <div class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center justify-between">
                                    <div class="font-medium text-blue-800 flex items-center">
                                        <i class="fas fa-box-open mr-2"></i>
                                        <span>Linh kiện cho: ${name}</span>
                                    </div>
                                    <button type="button" class="toggle-components text-blue-700 hover:text-blue-900">
                                        <i class="fas fa-chevron-up"></i>
                                    </button>
                                    <button type="button" class="ml-3 text-sm px-3 py-1 rounded bg-amber-500 text-white hidden create-new-product-btn" data-product-id="${pid}">
                                        Tạo thành phẩm mới
                                    </button>
                                </div>
                                <div class="component-list-container p-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại linh kiện</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên linh kiện</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kho xuất</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr>
                                                    <td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">Chưa có linh kiện nào được thêm vào thành phẩm này</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>`;
                            blocksContainer.appendChild(block);
                            // fetch and render default materials for this product
                            try {
                                fetch(`{{ url('/assemblies/product-materials') }}/${pid}`)
                                    .then(r => r.json())
                                    .then(data => {
                                        const materials = Array.isArray(data.materials) ? data
                                            .materials : [];
                                        const tbody = block.querySelector('tbody');
                                        if (!tbody) return;
                                        tbody.innerHTML = '';
                                        if (materials.length === 0) {
                                            const tr = document.createElement('tr');
                                            tr.innerHTML =
                                                '<td colspan="7" class="px-6 py-4 text-sm text-gray-500 text-center">Thành phẩm này không có linh kiện định sẵn</td>';
                                            tbody.appendChild(tr);
                                            return;
                                        }
                                        for (let unitIndex = 0; unitIndex < (parseInt(qty) ||
                                            1); unitIndex++) {
                                            const header = document.createElement('tr');
                                            header.className = 'bg-green-50';
                                            header.innerHTML =
                                                `<td colspan="7" class="px-6 py-2 text-sm font-medium text-green-800">Đơn vị thành phẩm ${unitIndex + 1}</td>`;
                                            tbody.appendChild(header);
                                            materials.forEach(m => {
                                                const idx = Date.now() + '' + unitIndex +
                                                    Math.random().toString(36).slice(2, 7);
                                                const row = document.createElement('tr');
                                                row.className =
                                                    'component-row bg-white hover:bg-gray-50';
                                                row.innerHTML = `
                                                    <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-900\">
                                                        <input type=\"hidden\" name=\"components[${idx}][id]\" value=\"${m.id}\"> 
                                                        <input type=\"hidden\" name=\"components[${idx}][material_id]\" value=\"${m.id}\"> 
                                                        <input type=\"hidden\" name=\"components[${idx}][target_product_id]\" value=\"${pid}\"> 
                                                        <input type=\"hidden\" name=\"components[${idx}][product_unit]\" value=\"${unitIndex}\"> 
                                                        ${m.code}
                                                    </td>
                                                    <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\">${m.category || ''}</td>
                                                    <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\">${m.name}</td>
                                                    <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\">
                                                        <input type=\"number\" name=\"components[${idx}][quantity]\" value=\"${m.quantity || 1}\" min=\"1\" class=\"w-20 border border-gray-300 rounded-lg px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-blue-500\">
                                                    </td>
                                                    <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\"></td>
                                                    <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\"></td>
                                                    <td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-700\"><input type=\"text\" name=\"components[${idx}][note]\" class=\"w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500\" placeholder=\"Ghi chú\"> <button type=\"button\" class=\"text-red-500 hover:text-red-700 ml-2 delete-component-btn\" data-material-id=\"${m.id}\" data-product-id=\"${pid}\" data-unit-index=\"${unitIndex}\"><i class=\"fas fa-trash\"></i></button></td>
                                                `;
                                                tbody.appendChild(row);
                                                // add warehouse and serial selectors
                                                const wCell = row.cells[4];
                                                const sCell = row.cells[5];
                                                const compObj = {
                                                    id: m.id,
                                                    quantity: (m.quantity || 1),
                                                    warehouseId: null
                                                };
                                                addWarehouseSelectToCell(wCell, compObj,
                                                    idx);
                                                addSerialInputsToCell(sCell, compObj, idx);
                                                // react to quantity changes to rebuild serial selects
                                                const qtyInputEl = row.querySelector(
                                                    'input[name^="components[' + idx +
                                                    '][quantity]"]');
                                                if (qtyInputEl) {
                                                    qtyInputEl.addEventListener('change',
                                                        async () => {
                                                            let newQ = parseInt(
                                                                qtyInputEl
                                                                .value || '1');
                                                            if (isNaN(newQ) ||
                                                                newQ < 1) {
                                                                newQ = 1;
                                                                qtyInputEl.value =
                                                                    '1';
                                                            }
                                                        compObj.quantity = newQ;
                                                            await addSerialInputsToCell
                                                                (sCell, compObj,
                                                                    idx);
                                                        
                                                        // Cập nhật button "Tạo thành phẩm mới" sau khi thay đổi số lượng linh kiện
                                                        setTimeout(() => {
                                                                updateCreateNewProductButton
                                                                    (pid);
                                                        }, 100);
                                                    });
                                                }
                                            });
                                        }

                                        // Sau khi render mặc định cho block mới, lưu công thức gốc để so sánh các lần chỉnh sau
                                        try {
                                            setBlockOriginalFormula(block);
                                        } catch (e) {}
                                    });
                            } catch (e) {}
                        }
                        // attach change listener for its qty
                        const qtyEl = tr.querySelector('.product-qty-input');
                        if (qtyEl) {
                            qtyEl.addEventListener('change', function() {
                                let newQty = parseInt(this.value || '1');
                                if (isNaN(newQty) || newQty < 1) {
                                    newQty = 1;
                                    this.value = '1';
                                }
                                generateProductSerialInputs(tr, index, pid, newQty);
                                
                                // Cập nhật button "Tạo thành phẩm mới" sau khi thay đổi số lượng thành phẩm mới
                                setTimeout(() => {
                                    updateCreateNewProductButton(pid);
                                }, 100);
                            });
                        }
                        // add delete handler
                        tr.querySelector('.delete-product-btn').addEventListener('click', function() {
                            const pid = this.getAttribute('data-product-id');
                            const form = document.getElementById('assembly-form');
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'deleted_products[]';
                            hidden.value = pid;
                            form.appendChild(hidden);
                            tr.remove();
                            // Remove related component rows and mark deletions
                            document.querySelectorAll('tr.component-row').forEach(crow => {
                                const targetPidInput = crow.querySelector(
                                    'input[name*="[target_product_id]"]');
                                const unitHidden = crow.querySelector(
                                    'input.product-unit-select[data-product-id]');
                                const matches = (targetPidInput && targetPidInput
                                    .value == pid) || (unitHidden && unitHidden
                                    .getAttribute('data-product-id') == pid);
                                if (matches) {
                                    const midInput = crow.querySelector(
                                            'input[name*="[material_id]"]') || crow
                                        .querySelector('input[name*="[id]"]');
                                    const unitInput = crow.querySelector(
                                        'input[name*="[product_unit]"]');
                                    if (midInput) {
                                        const di = (window.deletedComponentIdx = (window.deletedComponentIdx || 0) + 1);
                                        const h1 = document.createElement('input');
                                        h1.type = 'hidden';
                                        h1.name = `deleted_components[${di}][material_id]`;
                                        h1.value = midInput.value;
                                        form.appendChild(h1);
                                        const h2 = document.createElement('input');
                                        h2.type = 'hidden';
                                        h2.name = `deleted_components[${di}][target_product_id]`;
                                        h2.value = pid;
                                        form.appendChild(h2);
                                        const h3 = document.createElement('input');
                                        h3.type = 'hidden';
                                        h3.name = `deleted_components[${di}][product_unit]`;
                                        h3.value = unitInput ? unitInput.value : '';
                                        form.appendChild(h3);
                                    }
                                    crow.remove();
                                }
                            });
                            // Remove the whole component block for this product if exists
                            document.querySelectorAll(
                                `.component-block[data-product-id="${pid}"]`).forEach(
                                block => block.remove());
                        });
                    });
                })();

                // Component search and add (client-side from provided materials)
                (function() {
                    const searchInput = document.getElementById('component_search');
                    const results = document.getElementById('search_results');
                    const addBtn = document.getElementById('add_component_btn');
                    const productSelect = document.getElementById('component_product_id');
                    const qtyInput = document.getElementById('component_add_quantity');
                    let selectedMaterial = null;
                    const materials = @json($materials ?? []);
                    
                    // Compare formula of a product block against original to decide create-new-product
                    function isFormulaModified(productId) {
                        // Collect current components for this productId
                        const block = document.querySelector(
                        `.component-block[data-product-id="${productId}"]`);
                        if (!block) return false;
                        const rows = Array.from(block.querySelectorAll('tr.component-row'));
                        const current = rows.map(r => {
                            const mid = r.querySelector('input[name*="[material_id]"]')?.value;
                            const qty = r.querySelector('input[name*="[quantity]"]')?.value;
                            const unit = r.querySelector('input[name*="[product_unit]"]')?.value ?? '0';
                            return `${mid}:${qty}:${unit}`;
                        }).filter(Boolean).sort();
                        // Original components from server (if any) embedded on header as data-original
                        const original = (block.getAttribute('data-original-formula') || '').split(',').filter(
                            Boolean).sort();
                        if (original.length === 0) {
                            // If we don't have original, treat as modified when user has added any component
                            return current.length > 0;
                        }
                        if (current.length !== original.length) return true;
                        for (let i = 0; i < current.length; i++) {
                            if (current[i] !== original[i]) return true;
                        }
                        return false;
                    }

                    function updateCreateNewProductButton(productId) {
                        const block = document.querySelector(
                        `.component-block[data-product-id="${productId}"]`);
                if (!block) return;
                        if (typeof window.updateCreateNewProductButton === 'function') {
                            window.updateCreateNewProductButton(productId);
                        }
                    }

                    function render(list) {
                        if (!results) return;
                        if (!list || list.length === 0) {
                            results.innerHTML = '<div class="p-2 text-gray-500">Không tìm thấy vật tư</div>';
                            results.classList.remove('hidden');
                            return;
                        }
                        let html = '';
                        list.slice(0, 20).forEach(m => {
                            html +=
                                `<div class=\"p-2 hover:bg-gray-100 cursor-pointer component-item\" data-id=\"${m.id}\" data-code=\"${m.code}\" data-name=\"${m.name}\" data-category=\"${m.category||''}\" data-unit=\"${m.unit||''}\">` +
                                `<div class=\"font-medium text-sm\">[${m.code}] ${m.name}</div>` +
                                `<div class=\"text-xs text-gray-500\">${m.category||''} ${m.unit?('- '+m.unit):''}</div>` +
                                `</div>`;
                        });
                        results.innerHTML = html;
                        results.classList.remove('hidden');
                        results.querySelectorAll('.component-item').forEach(it => {
                            it.addEventListener('click', function() {
                                selectedMaterial = {
                                    id: parseInt(this.getAttribute('data-id')),
                                    code: this.getAttribute('data-code'),
                                    name: this.getAttribute('data-name'),
                                    category: this.getAttribute('data-category') || '',
                                    unit: this.getAttribute('data-unit') || ''
                                };
                                searchInput.value =
                                    `[${selectedMaterial.code}] ${selectedMaterial.name}`;
                                results.classList.add('hidden');
                                if (productSelect && productSelect.value) {
                                    addBtn.disabled = false;
                                    addBtn.classList.remove('opacity-50');
                                }
                            });
                        });
                    }

                    if (searchInput) {
                        searchInput.addEventListener('focus', () => render(materials));
                        searchInput.addEventListener('input', function() {
                            const term = this.value.trim().toLowerCase();
                            if (!term) {
                                render(materials);
                                return;
                            }
                            const filtered = materials.filter(m => (m.code || '').toLowerCase()
                                .includes(term) || (m.name || '').toLowerCase().includes(term) || (m
                                    .category || '').toLowerCase().includes(term));
                            render(filtered);
                        });
                    }

                    if (productSelect) {
                        productSelect.addEventListener('change', function() {
                            const enable = !!(this.value && selectedMaterial);
                            addBtn.disabled = !enable;
                            addBtn.classList.toggle('opacity-50', !enable);
                        });
                    }

                    if (addBtn) {
                        addBtn.addEventListener('click', function() {
                            if (!selectedMaterial) {
                                alert('Vui lòng chọn vật tư');
                                return;
                            }
                            if (!productSelect || !productSelect.value) {
                                alert('Vui lòng chọn thành phẩm');
                                return;
                            }
                            const pid = productSelect.value;
                            const qty = parseInt(qtyInput && qtyInput.value ? qtyInput.value : '1');
                            const block = document.querySelector(
                                `.component-block[data-product-id="${pid}"]`);
                            if (!block) {
                                alert('Không tìm thấy khu vực vật tư cho thành phẩm đã chọn.');
                                return;
                            }
                            const tbody = block.querySelector('tbody');
                            if (!tbody) {
                                alert('Không tìm thấy bảng linh kiện cho thành phẩm đã chọn.');
                                return;
                            }

                            const unitHeaders = Array.from(tbody.querySelectorAll('tr.bg-green-50'));
                            const unitCount = unitHeaders.length > 0 ? unitHeaders.length : 1;

                            const emptyRow = tbody.querySelector('tr td[colspan]');
                            if (emptyRow && /Chưa có linh kiện nào/i.test(emptyRow.textContent || '')) {
                                emptyRow.parentElement.remove();
                            }

                            // Check if material already exists in the entire product BEFORE adding to any unit
                            const materialExistsInProduct = Array.from(tbody.querySelectorAll('tr.component-row')).some(r => {
                                const m = r.querySelector('input[name*="[material_id]"]');
                                return m && parseInt(m.value) === selectedMaterial.id;
                            });
                            
                            // If material already exists in the product, don't add it
                            if (materialExistsInProduct) {
                                alert(`Vật tư "${selectedMaterial.name}" đã tồn tại trong thành phẩm này.`);
                                return;
                            }

                            for (let unitIndex = 0; unitIndex < unitCount; unitIndex++) {
                                const idx = Date.now() + '' + unitIndex + Math.random().toString(36).slice(2,7);
                                        const tr = document.createElement('tr');
                                        tr.className = 'component-row bg-white hover:bg-gray-50';
                                        tr.innerHTML = `
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <input type="hidden" name="components[${idx}][id]" value="${selectedMaterial.id}">
                                        <input type="hidden" name="components[${idx}][material_id]" value="${selectedMaterial.id}">
                                        <input type="hidden" name="components[${idx}][target_product_id]" value="${pid}">
                                        <input type="hidden" name="components[${idx}][product_unit]" value="${unitIndex}">
                                        ${selectedMaterial.code}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${selectedMaterial.category||''}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${selectedMaterial.name}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><input type="number" name="components[${idx}][quantity]" value="${qty}" min="1" class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-blue-500"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <input type="text" name="components[${idx}][note]" class="w-full border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ghi chú">
                                        <button type="button" class="text-red-500 hover:text-red-700 ml-2 delete-component-btn" data-material-id="${selectedMaterial.id}" data-product-id="${pid}" data-unit-index="${unitIndex}"><i class="fas fa-trash"></i></button>
                                    </td>`;
                                // Positioning per unit
                                const unitRows = Array.from(tbody.querySelectorAll('tr.component-row')).filter(r => {
                                    const u = r.querySelector('input[name*="[product_unit]"]');
                                    return u && parseInt(u.value) === unitIndex;
                                });
                                if (unitRows.length > 0) {
                                    unitRows[unitRows.length - 1].insertAdjacentElement('afterend', tr);
                                } else if (unitHeaders[unitIndex]) {
                                    unitHeaders[unitIndex].insertAdjacentElement('afterend', tr);
                                } else {
                                        tbody.appendChild(tr);
                                }
                                // Fill dynamic warehouse/serial selectors
                                const wCell = tr.cells[4];
                                const sCell = tr.cells[5];
                                const compObj = { id: selectedMaterial.id, quantity: qty, warehouseId: null };
                                addWarehouseSelectToCellForAdd(wCell, compObj, idx);
                                addSerialInputsToCellForAdd(sCell, compObj, idx);
                                
                                // Add quantity validation for dynamically created inputs
                                const quantityInput = tr.querySelector('input[name*="[quantity]"]');
                                if (quantityInput) {
                                    // Store original quantity for validation
                                    quantityInput.dataset.originalQuantity = quantityInput.value;
                                    
                                    quantityInput.addEventListener('change', function() {
                                        // If assembly is in progress, only allow increasing quantities
                                        if (IS_IN_PROGRESS) {
                                            const newQuantity = parseInt(this.value);
                                            const originalQuantity = parseInt(this.dataset.originalQuantity || '0');
                                            
                                            if (newQuantity < originalQuantity) {
                                                alert('Không thể giảm số lượng vật tư khi phiếu lắp ráp đang thực hiện. Chỉ được phép tăng số lượng.');
                                                this.value = originalQuantity;
                                                return;
                                            }
                                        }
                                        
                                        // Update create new product button
                                        const block = tr.closest('.component-block');
                                        if (block) {
                                            const productId = block.getAttribute('data-product-id');
                                            if (productId) {
                                                setTimeout(() => updateCreateNewProductButton(productId), 100);
                                            }
                                        }
                                    });
                                }
                            }

                            // Add "Tạo thành phẩm mới" button below the table for each unit (only if not exists)
                            for (let unitIndex = 0; unitIndex < unitCount; unitIndex++) {
                                // Check if this unit already has the banner by looking in the entire tbody
                                const existingBanner = tbody.querySelector(`.duplicate-section[data-unit="${unitIndex}"]`);
                                if (!existingBanner) {
                                    // Find the last component row for this unit
                                    const unitRows = Array.from(tbody.querySelectorAll('tr.component-row')).filter(r => {
                                        const u = r.querySelector('input[name*="[product_unit]"]');
                                        return u && parseInt(u.value) === unitIndex;
                                    });
                                    
                                    if (unitRows.length > 0) {
                                        const lastRow = unitRows[unitRows.length - 1];
                                        const duplicateSection = document.createElement('tr');
                                        duplicateSection.className = 'duplicate-section';
                                        duplicateSection.setAttribute('data-unit', unitIndex);
                                        duplicateSection.innerHTML = `
                                            <td colspan="7" class="bg-yellow-50 border-t border-yellow-200 p-3">
                                                <div class="flex justify-between items-center">
                                                    <div class="text-sm text-yellow-700">
                                                        <i class="fas fa-info-circle mr-2"></i>Bạn đã thay đổi công thức gốc. Bạn có thể tạo một thành phẩm mới với công thức này.
                                                    </div>
                                                    <button type="button" class="create-new-product-btn bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm" data-product-id="${pid}" data-unit-index="${unitIndex}">
                                                        <i class="fas fa-plus-circle mr-1"></i> Tạo thành phẩm mới
                                                    </button>
                                                </div>
                                            </td>`;
                                        lastRow.insertAdjacentElement('afterend', duplicateSection);
                                    }
                                }
                            }
                        });
                    }

                })();

                // Delete component - mark for backend
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.delete-component-btn');
                    if (!btn) return;
                    const materialId = btn.getAttribute('data-material-id');
                    const targetProductId = btn.getAttribute('data-product-id');
                    const unitIndex = btn.getAttribute('data-unit-index');
                    const formEl = document.getElementById('assembly-form');
                    const idx = (window.deletedComponentIdx = (window.deletedComponentIdx || 0) + 1);
                    const h1 = document.createElement('input');
                    h1.type = 'hidden';
                    h1.name = `deleted_components[${idx}][material_id]`;
                    h1.value = materialId;
                    formEl.appendChild(h1);
                    const h2 = document.createElement('input');
                    h2.type = 'hidden';
                    h2.name = `deleted_components[${idx}][target_product_id]`;
                    h2.value = targetProductId;
                    formEl.appendChild(h2);
                    const h3 = document.createElement('input');
                    h3.type = 'hidden';
                    h3.name = `deleted_components[${idx}][product_unit]`;
                    h3.value = (unitIndex ?? '');
                    formEl.appendChild(h3);
                    btn.closest('tr').remove();
                    
                    // Cập nhật button "Tạo thành phẩm mới" sau khi xóa vật tư
                    // Kiểm tra xem công thức có trở về gốc không và ẩn nút cho từng đơn vị riêng biệt
                    setTimeout(() => {
                        const block = document.querySelector(`.component-block[data-product-id="${targetProductId}"]`);
                        if (block) {
                            // Nếu có unitIndex cụ thể, chỉ kiểm tra đơn vị đó
                            if (unitIndex !== null && unitIndex !== '') {
                                const unitIndexInt = parseInt(unitIndex);
                                // Kiểm tra xem đơn vị này có trở về công thức gốc không
                                const unitRows = Array.from(block.querySelectorAll('tr.component-row')).filter(r => {
                                    const u = r.querySelector('input[name*="[product_unit]"]');
                                    return u && parseInt(u.value) === unitIndexInt;
                                });
                                
                                // Tạo công thức hiện tại chỉ cho đơn vị này
                                const unitCurrentFormula = unitRows.map(r => {
                                    const midInput = r.querySelector('input[name*="[material_id]"]') || r.querySelector('input[name*="[id]"]');
                                    const qtyInput = r.querySelector('input[name*="[quantity]"]');
                                    const unitInput = r.querySelector('input[name*="[product_unit]"]');
                                    const mid = midInput ? String(midInput.value).trim() : '';
                                    let qty = '';
                                    if (qtyInput) {
                                        qty = String(qtyInput.value).trim();
                                    } else {
                                        const qtyDiv = r.querySelector('.w-20.border.border-gray-200');
                                        qty = qtyDiv ? String(qtyDiv.textContent).trim() : '';
                                    }
                                    const unit = unitInput ? String(unitInput.value).trim() : '0';
                                    if (mid) return `${mid}:${qty}:${unit}`;
                                    return '';
                                }).filter(Boolean).sort().join(',');
                                
                                // Lấy công thức gốc cho đơn vị này từ data-original-formula
                                const originalFormula = block.getAttribute('data-original-formula') || '';
                                const originalParts = originalFormula.split(',').filter(Boolean);
                                const unitOriginalParts = originalParts.filter(part => {
                                    const unitPart = part.split(':');
                                    return unitPart.length >= 3 && parseInt(unitPart[2]) === unitIndexInt;
                                });
                                const unitOriginalFormula = unitOriginalParts.sort().join(',');
                                
                                // Nếu đơn vị này đã trở về gốc, ẩn nút của đơn vị này
                                if (unitCurrentFormula === unitOriginalFormula) {
                                    // Tìm và ẩn banner của đơn vị này
                                    const unitBanner = block.querySelector(`.duplicate-section[data-unit="${unitIndex}"]`);
                                    if (unitBanner) {
                                        unitBanner.remove();
                                    }
                                }
                            } else {
                                // Nếu không có unitIndex, kiểm tra toàn bộ sản phẩm
                                const currentFormula = computeBlockFormula(block);
                                const originalFormula = block.getAttribute('data-original-formula') || '';
                                
                                if (currentFormula === originalFormula) {
                                    // Công thức đã trở về gốc, ẩn tất cả nút "Tạo thành phẩm mới"
                                    block.querySelectorAll('.duplicate-section').forEach(section => {
                                        section.remove();
                                    });
                                    // Cũng ẩn nút ở header nếu có
                                    const headerBtn = block.querySelector('.create-new-product-btn');
                                    if (headerBtn) headerBtn.classList.add('hidden');
                                } else {
                                    // Công thức vẫn khác gốc, cập nhật button cho đơn vị cụ thể
                        updateCreateNewProductButton(targetProductId);
                                }
                            }
                        }
                    }, 100);
                });
            @endif
        });

        // Đồng bộ hóa đơn vị vật tư khi trang đã tải xong
        setTimeout(function() {
            console.log('Đồng bộ hóa đơn vị vật tư sau khi trang đã tải xong...');
            document.querySelectorAll('.product-unit-select').forEach(select => {
                // Trigger change event to update styling
                const event = new Event('change', {
                    bubbles: true
                });
                select.dispatchEvent(event);
            });
        }, 1000);

        // Global helpers for dynamic warehouse & serial when adding components
        const warehousesDataForAdd = @json($warehouses ?? []);
        async function fetchMaterialSerialsForAdd(materialId, warehouseId) {
            const url = `{{ route('assemblies.material-serials') }}?` + new URLSearchParams({
                material_id: materialId,
                warehouse_id: warehouseId
            });
            const resp = await fetch(url);
            if (!resp.ok) throw new Error('Network error');
            const data = await resp.json();
            return Array.isArray(data.serials) ? data.serials : [];
        }

        function addWarehouseSelectToCellForAdd(cell, component, index) {
            cell.innerHTML = '';
            const select = document.createElement('select');
            select.name = `components[${index}][warehouse_id]`;
            select.required = true;
            select.className =
                'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 warehouse-select';
            const def = document.createElement('option');
            def.value = '';
            def.textContent = '-- Chọn kho --';
            select.appendChild(def);
            warehousesDataForAdd.forEach(w => {
                const o = document.createElement('option');
                o.value = w.id;
                o.textContent = `${w.name} (${w.code})`;
                if (component.warehouseId && parseInt(component.warehouseId) === parseInt(w.id)) o.selected = true;
                select.appendChild(o);
            });
            select.addEventListener('change', function() {
                component.warehouseId = this.value;
                const serialCell = cell.parentElement.cells[5];
                if (serialCell) addSerialInputsToCellForAdd(serialCell, component, index);
            });
            cell.appendChild(select);
        }
        async function addSerialInputsToCellForAdd(cell, component, index) {
            cell.innerHTML = '';
            const q = parseInt(component.quantity || 1);
            if (!component.warehouseId) {
                cell.innerHTML = '<div class="text-sm text-gray-400">Chọn kho để tải serial</div>';
                return;
            }
            try {
                const serials = await fetchMaterialSerialsForAdd(component.id, component.warehouseId);
                for (let i = 0; i < q; i++) {
                    const wrap = document.createElement('div');
                    wrap.className = 'flex items-center mb-2 last:mb-0';
                    const sel = document.createElement('select');
                    sel.name = `components[${index}][serials][]`;
                    sel.className =
                        'w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 material-serial-select';
                    const o0 = document.createElement('option');
                    o0.value = '';
                    o0.textContent = `-- Chọn serial ${i+1} --`;
                    sel.appendChild(o0);
                    serials.forEach(s => {
                        const o = document.createElement('option');
                        o.value = s.serial_number || s;
                        o.textContent = (s.serial_number || s);
                        sel.appendChild(o);
                    });
                    sel.addEventListener('change', () => {
                        if (typeof checkDuplicateSerialsRealTime === 'function')
                    checkDuplicateSerialsRealTime();
                    });
                    wrap.appendChild(sel);
                    cell.appendChild(wrap);
                }
            } catch (e) {
                cell.innerHTML = '<div class="text-sm text-red-500">Lỗi tải serial</div>';
            }
        }
    </script>
</body>

</html>
