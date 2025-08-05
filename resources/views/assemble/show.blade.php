<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu lắp ráp - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu lắp ráp</h1>
            </div>
            <div class="flex items-center gap-2">
                {{-- <a href="{{ route('assemblies.edit', $assembly->id) }}">
                    <button
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                    </button>
                </a> --}}
                {{-- <button id="print-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-print mr-2"></i> In phiếu
                </button> --}}
            </div>
        </header>

        <main class="p-6">
            <!-- Header Info -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <div class="flex flex-col lg:flex-row justify-between gap-4">
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-lg font-semibold text-gray-800 mr-2">Mã phiếu lắp ráp:</span>
                            <span class="text-lg text-blue-600 font-bold">{{ $assembly->code }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Ngày lắp ráp:</span>
                            <span
                                class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($assembly->date)->format('d/m/Y') }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Thành phẩm:</span>
                            <div class="flex flex-col space-y-2">
                                @if ($assembly->products && $assembly->products->count() > 0)
                                    @foreach ($assembly->products as $assemblyProduct)
                                        <div class="bg-blue-50 p-3 rounded-lg border-l-4 border-blue-400">
                                            <div class="flex items-center justify-between">
                                                <span
                                                    class="text-sm font-medium text-gray-800">{{ $assemblyProduct->product->name }}</span>
                                                <span
                                                    class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">{{ $assemblyProduct->quantity }}
                                                    sản phẩm</span>
                                            </div>
                                            <div class="text-xs text-gray-600 mt-1">
                                                <span class="font-medium">Mã:</span>
                                                {{ $assemblyProduct->product->code }}
                                            </div>
                                            @if ($assemblyProduct->serials)
                                                <div class="text-xs text-gray-600 mt-1">
                                                    <span class="font-medium">Serial:</span>
                                                    {{ $assemblyProduct->serials }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Legacy support for old assemblies -->
                                    <div class="bg-gray-50 p-3 rounded-lg border-l-4 border-gray-400">
                                        <div class="flex items-center justify-between">
                                            <span
                                                class="text-sm font-medium text-gray-800">{{ $assembly->product->name ?? 'Không có sản phẩm' }}</span>
                                            <span
                                                class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">{{ $assembly->quantity ?? '1' }}
                                                sản phẩm</span>
                                        </div>
                                        @if ($assembly->product)
                                            <div class="text-xs text-gray-600 mt-1">
                                                <span class="font-medium">Mã:</span> {{ $assembly->product->code }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Người phụ trách:</span>
                            <span
                                class="text-sm text-gray-700">{{ $assembly->assignedEmployee->name ?? $assembly->assigned_to }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Người tiếp nhận kiểm thử:</span>
                            <span
                                class="text-sm text-gray-700">{{ $assembly->tester->name ?? 'Chưa phân công' }}</span>
                        </div>
                        @if ($assembly->targetWarehouse)
                            <div class="flex items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 mr-2">Kho nhập thành phẩm:</span>
                                <span class="text-sm text-gray-700">{{ $assembly->targetWarehouse->name ?? '' }}
                                    ({{ $assembly->targetWarehouse->code ?? '' }})</span>
                            </div>
                        @endif
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Trạng thái:</span>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if ($assembly->status == 'completed') bg-green-100 text-green-800
                                @elseif($assembly->status == 'in_progress') bg-yellow-100 text-yellow-800
                                @elseif($assembly->status == 'pending') bg-blue-100 text-blue-800
                                @else bg-red-100 text-red-800 @endif">
                                @if ($assembly->status == 'completed')
                                    Hoàn thành
                                @elseif($assembly->status == 'in_progress')
                                    Đang thực hiện
                                @elseif($assembly->status == 'pending')
                                    Chờ xử lý
                                @else
                                    Đã hủy
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center mt-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Mục đích:</span>
                            <span class="text-sm text-gray-700">
                                @if ($assembly->purpose == 'storage')
                                    Lưu kho
                                @elseif($assembly->purpose == 'project')
                                    Xuất đi dự án
                                @else
                                    {{ $assembly->purpose ?? 'Không xác định' }}
                                @endif
                            </span>
                        </div>
                        @if ($assembly->purpose == 'project' && isset($assembly->project))
                            <div class="flex items-center mt-2">
                                <span class="text-sm font-medium text-gray-700 mr-2">Dự án:</span>
                                <span class="text-sm text-gray-700">{{ $assembly->project->project_name ?? '' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Display product serials --}}
                @php
                    $hasProductSerials = false;
                    if ($assembly->products && $assembly->products->count() > 0) {
                        foreach ($assembly->products as $product) {
                            if ($product->serials) {
                                $hasProductSerials = true;
                                break;
                            }
                        }
                    } elseif ($assembly->product_serials) {
                        $hasProductSerials = true;
                    }
                @endphp

                @if ($hasProductSerials)
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Serial thành phẩm:</h3>
                        <div class="space-y-3">
                            @if ($assembly->products && $assembly->products->count() > 0)
                                {{-- New structure: show serials grouped by product --}}
                                @foreach ($assembly->products as $assemblyProduct)
                                    @if ($assemblyProduct->serials)
                                        <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-blue-400">
                                            <div class="text-sm font-medium text-gray-800 mb-2">
                                                {{ $assemblyProduct->product->name }}
                                                ({{ $assemblyProduct->product->code }})
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                                @foreach (explode(',', $assemblyProduct->serials) as $serial)
                                                    @if (!empty(trim($serial)))
                                                        <div class="bg-white rounded-lg p-2 text-sm border">
                                                            <span class="font-medium">{{ $loop->iteration }}.</span>
                                                            {{ trim($serial) }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @elseif ($assembly->product_serials)
                                {{-- Legacy structure: show serials for single product --}}
                                <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-gray-400">
                                    <div class="text-sm font-medium text-gray-800 mb-2">
                                        {{ $assembly->product->name ?? 'Thành phẩm' }}
                                        ({{ $assembly->product->code ?? '' }})
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                        @foreach (explode(',', $assembly->product_serials) as $serial)
                                            @if (!empty(trim($serial)))
                                                <div class="bg-white rounded-lg p-2 text-sm border">
                                                    <span class="font-medium">{{ $loop->iteration }}.</span>
                                                    {{ trim($serial) }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($assembly->notes)
                    <div class="mt-4 border-t border-gray-200 pt-4">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Ghi chú:</span>
                            <span class="text-sm text-gray-700">{{ $assembly->notes }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Component List -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-microchip text-blue-500 mr-2"></i>
                    Danh sách vật tư
                </h2>

                @php
                    // Group materials by product and product_unit
                    $materialsByProduct = [];

                    // If assembly has products relationship (new structure)
                    if ($assembly->products && $assembly->products->count() > 0) {
                        // Initialize arrays for each product
                        foreach ($assembly->products as $product) {
                            $materialsByProduct[$product->product_id] = [
                                'product' => $product,
                                'units' => [], // Group by product_unit
                            ];
                        }

                        // Group materials by target_product_id and product_unit
                        foreach ($assembly->materials as $material) {
                            $productId = $material->target_product_id ?? $assembly->products->first()->product_id;
                            $productUnit = $material->product_unit ?? 0;
                            
                            if (isset($materialsByProduct[$productId])) {
                                if (!isset($materialsByProduct[$productId]['units'][$productUnit])) {
                                    $materialsByProduct[$productId]['units'][$productUnit] = [];
                                }
                                $materialsByProduct[$productId]['units'][$productUnit][] = $material;
                            } else {
                                // If target_product_id doesn't match any product, assign to first product
                                $firstProductId = $assembly->products->first()->product_id;
                                if (!isset($materialsByProduct[$firstProductId]['units'][$productUnit])) {
                                    $materialsByProduct[$firstProductId]['units'][$productUnit] = [];
                                }
                                $materialsByProduct[$firstProductId]['units'][$productUnit][] = $material;
                            }
                        }
                    } else {
                        // Legacy structure - show all materials under single product
                        $materialsByProduct['legacy'] = [
                            'product' => $assembly->product,
                            'units' => [0 => $assembly->materials->toArray()],
                        ];
                    }
                @endphp

                @php
                    // Debug: Log materials grouping
                    Log::info('Materials grouping debug', [
                        'assembly_id' => $assembly->id,
                        'products_count' => $assembly->products->count(),
                        'materials_count' => $assembly->materials->count(),
                        'materialsByProduct_keys' => array_keys($materialsByProduct),
                        'materialsByProduct_details' => array_map(function($data) {
                            return [
                                'product_name' => $data['product']->product->name ?? 'unknown',
                                'units_count' => count($data['units']),
                                'total_materials' => array_sum(array_map('count', $data['units']))
                            ];
                        }, $materialsByProduct)
                    ]);
                @endphp

                @if (count($materialsByProduct) > 0)
                    @foreach ($materialsByProduct as $productId => $productData)
                        @if (count($productData['units']) > 0)
                            <!-- Product Header -->
                            <div class="mb-6">
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg mb-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-medium text-blue-800">
                                                @if ($productData['product'] && $productData['product']->product)
                                                    {{ $productData['product']->product->name }}
                                                @else
                                                    Thành phẩm chưa xác định (Product ID: {{ $productId }})
                                                @endif
                                            </h3>
                                            <p class="text-sm text-blue-600">
                                                @if ($productData['product'] && $productData['product']->product)
                                                    Mã: {{ $productData['product']->product->code }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-sm text-blue-600">
                                            @php
                                                $totalMaterials = 0;
                                                foreach ($productData['units'] as $unitMaterials) {
                                                    $totalMaterials += count($unitMaterials);
                                                }
                                            @endphp
                                            {{ $totalMaterials }} vật tư
                                        </div>
                                    </div>
                                </div>

                                <!-- Materials by Product Unit -->
                                @foreach ($productData['units'] as $unitIndex => $unitMaterials)
                                    @if (count($unitMaterials) > 0)
                                        <!-- Unit Header -->
                                        <div class="bg-green-50 border-l-4 border-green-400 p-3 rounded-lg mb-3">
                                            <h4 class="text-md font-medium text-green-800">
                                                <i class="fas fa-cube mr-2"></i>
                                                Đơn vị thành phẩm {{ $unitIndex + 1 }}
                                            </h4>
                                        </div>

                                        <!-- Materials Table for this Unit -->
                                        <div class="overflow-x-auto mb-6">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            STT</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Mã</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Loại vật tư</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Tên vật tư</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Số lượng</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Serial</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Ghi chú</th>
                                                        <th scope="col"
                                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach ($unitMaterials as $index => $material)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $index + 1 }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ is_object($material) ? $material->material->code : $material['material']['code'] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ is_object($material) ? $material->material->category : $material['material']['category'] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ is_object($material) ? $material->material->name : $material['material']['name'] }}
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">
                                                        {{ is_object($material) ? $material->quantity : $material['quantity'] }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500">
                                                        @php
                                                            $serialValue = is_object($material)
                                                                ? $material->serial
                                                                : $material['serial'];
                                                        @endphp
                                                        @if ($serialValue && str_contains($serialValue, ','))
                                                            <div class="space-y-1">
                                                                @foreach (explode(',', $serialValue) as $serial)
                                                                    @if (!empty(trim($serial)))
                                                                        <div class="bg-gray-50 px-2 py-1 rounded">
                                                                            {{ trim($serial) }}</div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                            <div class="text-xs text-gray-400 mt-1">
                                                                {{ count(array_filter(explode(',', $serialValue), 'trim')) }}
                                                                serial
                                                            </div>
                                                        @else
                                                            {{ $serialValue }}
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ is_object($material) ? $material->note : $material['note'] }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        @php
                                                            $materialId = is_object($material)
                                                                ? $material->material_id
                                                                : $material['material_id'];
                                                        @endphp
                                                        <a href="{{ route('materials.show', $materialId) }}"
                                                            class="text-blue-500 hover:text-blue-600">
                                                            Xem chi tiết
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-box-open text-4xl mb-4"></i>
                        <p>Không có vật tư nào trong phiếu lắp ráp này.</p>
                    </div>
                @endif
            </div>

            <!-- Buttons -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 no-print">
                @php
                    $user = auth()->user();
                    $isAdmin = $user->role === 'admin';
                @endphp
                <div class="flex flex-wrap gap-3">
                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('assembly.edit')))
                        <a href="{{ route('assemblies.edit', $assembly->id) }}"
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                            <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                        </a>
                    @endif

                    @if ($assembly->status !== 'approved' && ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('assembly.approve'))))
                        <form action="{{ route('assemblies.approve', $assembly->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center"
                                onclick="return confirm('Bạn có chắc chắn muốn duyệt phiếu lắp ráp này? Khi duyệt sẽ tạo phiếu kiểm thử và xuất kho.')">
                                <i class="fas fa-check mr-2"></i> Duyệt phiếu
                            </button>
                        </form>
                    @endif

                    @if ($assembly->status === 'approved')
                        <div class="px-4 py-2 bg-green-100 text-green-800 rounded-lg flex items-center">
                            <i class="fas fa-check-circle mr-2"></i> Đã duyệt
                        </div>
                    @endif

                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('assemblies.export')))
                        <a href="{{ route('assemblies.export.excel', $assembly->id) }}"
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center">
                            <i class="fas fa-file-excel mr-2"></i> Xuất Excel
                        </a>

                        <a href="{{ route('assemblies.export.pdf', $assembly->id) }}"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center">
                            <i class="fas fa-file-pdf mr-2"></i> Xuất PDF
                        </a>
                    @endif

                    {{-- <button onclick="window.print()" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 flex items-center">
                        <i class="fas fa-print mr-2"></i> In phiếu
                    </button> --}}

                    @php
                        $testing = $assembly->testings->first();
                    @endphp
                    @if ($testing)
                        <a href="{{ route('testing.show', $testing->id) }}"
                            class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 flex items-center">
                            <i class="fas fa-vial mr-2"></i> Xem phiếu kiểm thử
                        </a>
                    @else
                        <form action="{{ route('assemblies.create-testing', $assembly->id) }}" method="POST"
                            style="display:inline;">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 flex items-center">
                                <i class="fas fa-vial mr-2"></i> Tạo phiếu kiểm thử
                            </button>
                        </form>
                    @endif

                    @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('assembly.delete')))
                        @if ($assembly->status !== 'completed')
                            <form action="{{ route('assemblies.destroy', $assembly->id) }}" method="POST"
                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu lắp ráp này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center">
                                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Các hàm JavaScript khác nếu cần
        });
    </script>
</body>

</html>
