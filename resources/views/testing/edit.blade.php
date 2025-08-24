<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phiếu kiểm thử - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        .required::after {
            content: " *";
            color: #ef4444;
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phiếu kiểm thử</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $testing->test_code }}
                </div>
                <div class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $testing->test_type_text }}
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('testing.show', $testing->id) }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                <p>{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                <p>{{ session('error') }}</p>
            </div>
            @endif

            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('testing.update', $testing->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Hidden fields for required data -->
                    <input type="hidden" name="tester_id" value="{{ $testing->tester_id ?? '' }}">
                    <input type="hidden" name="assigned_to" value="{{ $testing->assigned_to ?? $testing->tester_id ?? '' }}">
                    
                    <!-- Thông tin cơ bản -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2">Thông tin cơ bản</h2>
                        
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Mã phiếu kiểm thử -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1 required">Mã phiếu kiểm thử</label>
                                <input type="text" id="test_code" name="test_code" value="{{ $testing->test_code }}" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required readonly>
                                @error('test_code')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        
                            <!-- Loại kiểm thử -->
                            <div>
                                <label for="test_type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại kiểm thử</label>
                                <select id="test_type" name="test_type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required {{ $testing->status != 'pending' ? 'disabled' : '' }}>
                                    <option value="">-- Chọn loại kiểm thử --</option>
                                    <option value="material" {{ $testing->test_type == 'material' ? 'selected' : '' }}>Kiểm thử Vật tư/Hàng hóa</option>
                                    <option value="finished_product" {{ $testing->test_type == 'finished_product' ? 'selected' : '' }}>Kiểm thử Thiết bị thành phẩm</option>
                                </select>
                                <small class="text-gray-500 text-xs mt-1 block">Lưu ý: Phiếu kiểm thử Thiết bị thành phẩm chỉ được tạo thông qua lắp ráp</small>
                                @error('test_type')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Ngày kiểm thử -->
                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700 mb-1 required">Ngày kiểm thử</label>
                                <input type="date" id="test_date" name="test_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $testing->test_date->format('Y-m-d') }}" required>
                                @error('test_date')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-4">
                                <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1 required">Người tiếp nhận kiểm thử</label>
                                <select id="receiver_id" name="receiver_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                <option value="">-- Chọn người tiếp nhận --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ $testing->receiver_id == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            @error('receiver_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                            </div>
                        </div>
                        
                        <!-- Bảng tổng hợp vật tư đã thêm -->
                    <div class="mb-6 mt-4">
                        <h3 class="text-md font-medium text-gray-800 mb-3">Tổng hợp vật tư, hàng hóa hoặc thành phẩm đã thêm</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">STT</th>
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">LOẠI</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">MÃ - TÊN</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">SỐ LƯỢNG</th>
                                        <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">KHO HÀNG</th>
                                            <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">SERIAL</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-summary-table">
                                        @forelse($testing->items->filter(function($item) use ($testing) {
                                            // Chỉ hiển thị items chính, không hiển thị vật tư con của thành phẩm
                                            if ($testing->test_type == 'finished_product') {
                                                // Nếu là finished_product, chỉ hiển thị thành phẩm, không hiển thị vật tư
                                                return $item->item_type == 'product' || $item->item_type == 'finished_product';
                                            }
                                            return true; // Hiển thị tất cả cho các loại khác
                                        }) as $index => $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-3 border-b border-gray-200">{{ $index + 1 }}</td>
                                            <td class="py-2 px-3 border-b border-gray-200">
                                                @if($item->item_type == 'material')
                                                    Vật tư
                                                @elseif($item->item_type == 'product' && $testing->test_type == 'finished_product')
                                                    Thành phẩm
                                                @elseif($item->item_type == 'product')
                                                    Hàng hóa
                                            @elseif($item->item_type == 'finished_product')
                                                    Thành phẩm
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 border-b border-gray-200">
                                                @if($item->item_type == 'material' && $item->material)
                                                    {{ $item->material->code }} - {{ $item->material->name }}
                                                @elseif($item->item_type == 'product' && $item->product)
                                                    {{ $item->product->code }} - {{ $item->product->name }}
                                                @elseif($item->item_type == 'product' && $item->good)
                                                    {{ $item->good->code }} - {{ $item->good->name }}
                                                @elseif($item->item_type == 'finished_product' && $item->good)
                                                    {{ $item->good->code }} - {{ $item->good->name }}
                                                @else
                                                    <span class="text-red-500">Không tìm thấy thông tin (Type: {{ $item->item_type }}, ID: {{ $item->material_id ?? $item->product_id ?? $item->good_id }})</span>
                                                @endif
                                            </td>
                                        <td class="py-2 px-3 border-b border-gray-200">{{ $item->quantity }}</td>
                                            <td class="py-2 px-3 border-b border-gray-200">
                                            @if($item->warehouse)
                                                {{ $item->warehouse->name }}
                                            @else
                                                N/A
                                                @endif
                                            </td>
                                        <td class="py-2 px-3 border-b border-gray-200">
                                            @php
                                                $serialsRow = $item->serial_number ? array_values(array_filter(array_map('trim', explode(',', $item->serial_number)))) : [];
                                                $quantity = $item->quantity ?? 0;
                                                $serialCount = count($serialsRow);
                                                $noSerialCount = $quantity - $serialCount;
                                            @endphp
                                            @if(count($serialsRow) > 0)
                                                <div class="text-xs text-gray-700">
                                                    @foreach($serialsRow as $s)
                                                        <div class="mb-0.5">{{ $s }}</div>
                                                    @endforeach
                                                    @for($i = 0; $i < $noSerialCount; $i++)
                                                        <div class="mb-0.5 text-gray-400">N/A</div>
                                                    @endfor
                                                    <div class="text-gray-400">{{ $serialCount }} serial{{ $serialCount > 1 ? 's' : '' }}{{ $noSerialCount > 0 ? ', ' . $noSerialCount . ' N/A' : '' }}</div>
                                                </div>
                                            @else
                                                @if($quantity > 0)
                                                    <div class="text-xs text-gray-700">
                                                        @for($i = 0; $i < $quantity; $i++)
                                                            <div class="mb-0.5 text-gray-400">N/A</div>
                                                        @endfor
                                                        <div class="text-gray-400">{{ $quantity }} N/A</div>
                                                    </div>
                                                @else
                                                    N/A
                                                @endif
                                            @endif
                                        </td>
                                        </tr>
                                        @empty
                                        <tr class="text-gray-500 text-center">
                                        <td colspan="6" class="py-4">Chưa có vật tư/hàng hóa nào được thêm</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    

                        <!-- Kết quả kiểm thử thiết bị -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                            <h3 class="text-md font-medium text-gray-800 mb-3">Kết quả kiểm thử thiết bị</h3>
                            
                        <div class="space-y-6">
                                @forelse($testing->items->filter(function($item) use ($testing) {
                                    // Chỉ hiển thị items chính, không hiển thị vật tư con của thành phẩm
                                    if ($testing->test_type == 'finished_product') {
                                        // Nếu là finished_product, chỉ hiển thị thành phẩm, không hiển thị vật tư
                                        return $item->item_type == 'product' || $item->item_type == 'finished_product';
                                    }
                                    return true; // Hiển thị tất cả cho các loại khác
                                }) as $index => $item)
                                <div class="border border-gray-200 rounded-lg p-4">
                                <div class="mb-4">
                                            <h4 class="font-medium text-gray-800">
                                                {{ $index + 1 }}. 
                                                @if($item->item_type == 'material' && $item->material)
                                                    {{ $item->material->code }} - {{ $item->material->name }}
                                                @elseif($item->item_type == 'product' && $item->product)
                                                    {{ $item->product->code }} - {{ $item->product->name }}
                                        @elseif($item->item_type == 'product' && $item->good)
                                            {{ $item->good->code }} - {{ $item->good->name }}
                                                @elseif($item->item_type == 'finished_product' && $item->good)
                                                    {{ $item->good->code }} - {{ $item->good->name }}
                                        @else
                                            <span class="text-red-500">Không tìm thấy thông tin (Type: {{ $item->item_type }}, ID: {{ $item->material_id ?? $item->product_id ?? $item->good_id }})</span>
                                                @endif
                                            </h4>
                                            <div class="flex items-center gap-4 mt-1 text-sm text-gray-600">
                                                <span>Loại: 
                                                    @if($item->item_type == 'material')
                                                        Vật tư
                                                    @elseif($item->item_type == 'product' && $testing->test_type == 'finished_product')
                                                        Thành phẩm
                                                    @elseif($item->item_type == 'product')
                                                        Hàng hóa
                                            @elseif($item->item_type == 'finished_product')
                                                        Thành phẩm
                                                    @endif
                                                </span>
                                        <span>Kho: {{ $item->warehouse ? $item->warehouse->name : 'N/A' }}</span>
                                                <span>Số lượng: {{ $item->quantity }}</span>
                                            </div>
                                </div>

                                @if(empty($item->serial_number))
                                <!-- Kết quả tổng thể cho thiết bị này (không có Serial) -->
                                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                    <h5 class="font-medium text-gray-800 mb-2">Kết quả tổng thể</h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng thiết bị đạt</label>
                                            <input type="number" name="item_pass_quantity[{{ $item->id }}]" min="0" max="{{ $item->quantity }}" class="w-full h-10 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $item->pass_quantity ?? 0 }}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng thiết bị không đạt</label>
                                            <input type="number" name="item_fail_quantity[{{ $item->id }}]" min="0" max="{{ $item->quantity }}" class="w-full h-10 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $item->fail_quantity ?? 0 }}">
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Hạng mục kiểm thử (UI giống trang tạo mới) -->
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-3">
                                        <h5 class="font-medium text-gray-800">Hạng mục kiểm thử (Không bắt buộc)</h5>
                                        <div class="flex items-center gap-2">
                                            <input type="text" placeholder="Nhập hạng mục kiểm thử (không bắt buộc)" class="h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" id="new_test_item_name_{{ $item->id }}">
                                            <button type="button" onclick="addDefaultTestItemsForEdit('{{ $item->id }}')" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm flex items-center">
                                                <i class="fas fa-list-check mr-1"></i> Thêm mục mặc định
                                            </button>
                                            <button type="button" onclick="addTestItemForItem('{{ $item->id }}')" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm flex items-center">
                                                <i class="fas fa-plus mr-1"></i> Thêm hạng mục
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <div class="space-y-3" id="test_items_container_{{ $item->id }}">
                                            @forelse($testing->details->where('item_id', $item->id) as $detailIndex => $detail)
                                                <div class="test-item flex items-center gap-4" data-detail-id="{{ $detail->id }}">
                                                    <input type="text" value="{{ $detail->test_item_name }}" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" disabled>
                                                    <button type="button" onclick="removeTestItemForEdit('{{ $detail->id }}', this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            @empty
                                                <div class="text-center text-gray-500 py-2">Chưa có hạng mục kiểm thử nào được thêm</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                    <!-- Vật tư lắp ráp cho thành phẩm này (chỉ hiển thị cho finished_product) -->
                                @if($item->item_type == 'finished_product' || ($item->item_type == 'product' && $testing->test_type == 'finished_product'))
                                <div class="mb-4 border-t border-gray-200 pt-4">
                                    <h5 class="font-medium text-gray-800 mb-3">Kiểm thử vật tư lắp ráp <span class="text-sm text-gray-500">(# chỉ của loại thành phẩm này, không phải của toàn bộ phiếu)</span></h5>

                                    @php
                                        $productIdForView = $item->item_type == 'finished_product' ? ($item->good_id ?? null) : ($item->product_id ?? null);
                                        $materialsByUnit = [];
                                        $productSerialsForUnits = [];
                                        if ($testing->assembly) {
                                            $apForProduct = $testing->assembly->products ? $testing->assembly->products->firstWhere('product_id', $productIdForView) : null;
                                            if ($apForProduct) {
                                                if (!empty($apForProduct->serials)) {
                                                    $productSerialsForUnits = array_values(array_filter(array_map('trim', explode(',', $apForProduct->serials))));
                                                }
                                                // Lấy tên thành phẩm để hiển thị trên header đơn vị
                                                $unitProductName = $apForProduct->product->name ?? ($apForProduct->product->code ?? 'Thành phẩm');
                                            }
                                            foreach ($testing->assembly->materials as $asmMaterial) {
                                                $tp = $asmMaterial->target_product_id ?? null;
                                                if ($productIdForView && $tp && $tp != $productIdForView) continue;
                                                $unit = (int)($asmMaterial->product_unit ?? 1);
                                                if (!isset($materialsByUnit[$unit])) $materialsByUnit[$unit] = [];
                                                $materialsByUnit[$unit][] = $asmMaterial;
                                            }
                                            ksort($materialsByUnit);
                                        }
                                        $testingMaterialMap = $testing->items->where('item_type','material')->keyBy('material_id');
                                    @endphp

                                    @if(!empty($materialsByUnit))
                                        @foreach($materialsByUnit as $unitIdx => $unitMaterials)
                                            <div class="mb-4 rounded-lg overflow-hidden border border-green-200">
                                                <div class="bg-green-50 px-3 py-2 flex items-center justify-between border-b border-green-200">
                                                    <div class="text-sm text-green-800 font-medium">
                                                        <i class="fas fa-box-open mr-2"></i> Đơn vị thành phẩm {{ $unitIdx }} - {{ $unitProductName ?? 'Thành phẩm' }} - Serial {{ $productSerialsForUnits[$unitIdx-1] ?? 'N/A' }}
                                                    </div>
                                                    <div class="text-xs text-green-700">{{ count($unitMaterials) }} vật tư</div>
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full bg-white">
                                                        <thead>
                                                            <tr class="bg-gray-50 text-left text-xs text-gray-600">
                                                                <th class="px-3 py-2">STT</th>
                                                                <th class="px-3 py-2">MÃ VẬT TƯ</th>
                                                                <th class="px-3 py-2">LOẠI VẬT TƯ</th>
                                                                <th class="px-3 py-2">TÊN VẬT TƯ</th>
                                                                <th class="px-3 py-2">SỐ LƯỢNG</th>
                                                                <th class="px-3 py-2">SERIAL</th>
                                                                <th class="px-3 py-2">KHO XUẤT</th>
                                                                <th class="px-3 py-2">GHI CHÚ</th>
                                                                <th class="px-3 py-2">THAO TÁC</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100">
                                                            @foreach($unitMaterials as $rowIdx => $asmMaterial)
                                                                @php
                                                                    $m = $asmMaterial->material;
                                                                    $testingItemRow = $testingMaterialMap->get($asmMaterial->material_id);
                                                                    $serialsRow = $asmMaterial->serial ? array_values(array_filter(array_map('trim', explode(',', $asmMaterial->serial)))) : [];
                                                                @endphp
                                                                <tr>
                                                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $rowIdx + 1 }}</td>
                                                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $m->code }}</td>
                                                                    <td class="px-3 py-2 text-sm text-gray-700">Vật tư</td>
                                                                    <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ $m->name }}</td>
                                                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $asmMaterial->quantity }}</td>
                                                                    <td class="px-3 py-2 text-sm text-gray-700">
                                                                        @if(count($serialsRow) > 0)
                                                                            <div class="text-xs text-gray-700">
                                                                                @foreach($serialsRow as $s)
                                                                                    <div class="mb-0.5">{{ $s }}</div>
                                                                                @endforeach
                                                                                <div class="text-gray-400">{{ count($serialsRow) }} serial</div>
                                                                            </div>
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-3 py-2 text-sm text-gray-700">
                                                                        @if($asmMaterial->warehouse)
                                                                            {{ $asmMaterial->warehouse->name }}
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $asmMaterial->note ?? ($testingItemRow->notes ?? '') }}</td>
                                                                    <td class="px-3 py-2 text-sm text-gray-700">
                                                                        @if($testing->status == 'in_progress')
                                                                            @if(count($serialsRow) > 0)
                                                                                @php $resultMapRow = $testingItemRow && $testingItemRow->serial_results ? json_decode($testingItemRow->serial_results, true) : []; @endphp
                                                                                <div class="space-y-1">
                                                                                    @foreach($serialsRow as $sIndex => $s)
                                                                                        @php $label = chr(65 + $sIndex); @endphp
                                                                                        <select name="serial_results[{{ $asmMaterial->material_id }}][{{ $label }}]" class="w-full h-8 border border-gray-300 rounded px-2 text-xs bg-white">
                                                                                            <option value="pending" {{ ($resultMapRow[$label] ?? 'pending') == 'pending' ? 'selected' : '' }}>Chưa có</option>
                                                                                            <option value="pass" {{ ($resultMapRow[$label] ?? '') == 'pass' ? 'selected' : '' }}>Đạt</option>
                                                                                            <option value="fail" {{ ($resultMapRow[$label] ?? '') == 'fail' ? 'selected' : '' }}>Không đạt</option>
                                                                                        </select>
                                                                                    @endforeach
                                                                                </div>
                                                                            @else
                                                                                @php $maxQtyRow = (int)($asmMaterial->quantity ?? 0); @endphp
                                                                                <div class="flex items-center gap-2">
                                                                                    <span class="text-xs text-gray-600">Số lượng Đạt</span>
                                                                                    <input type="number" name="item_pass_quantity[{{ $asmMaterial->material_id }}]" min="0" max="{{ $maxQtyRow }}" value="{{ $testingItemRow->pass_quantity ?? 0 }}" class="w-20 h-8 border border-gray-300 rounded px-2 text-sm bg-white" />
                                                                                </div>
                                                                            @endif
                                                                        @else
                                                                            <span class="text-gray-400 text-xs">Chưa tiếp nhận</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    @if(empty($materialsByUnit))
                                    @php
                                        $assemblyMaterials = collect();
                                        if ($testing->assembly) {
                                            $testingItems = $testing->items
                                                ->where('item_type', 'material')
                                                ->keyBy('material_id');
                                            
                                            // Xác định product_id dựa trên item_type
                                            $productId = null;
                                            if ($item->item_type == 'finished_product') {
                                                $productId = $item->good_id;
                                            } elseif ($item->item_type == 'product') {
                                                $productId = $item->product_id;
                                            }
                                            
                                            if ($productId) {
                                                $assemblyMaterials = $testing->assembly->materials
                                                    ->where('target_product_id', $productId)
                                                    ->map(function($asmMaterial) use ($testingItems) {
                                                        $testingItem = $testingItems->get($asmMaterial->material_id);
                                                        return (object)[
                                                            'material' => $asmMaterial->material,
                                                            'material_id' => $asmMaterial->material_id,
                                                            'quantity' => $asmMaterial->quantity,
                                                            'serial' => $asmMaterial->serial,
                                                            'testing_item' => $testingItem
                                                        ];
                                                    });
                                            }
                                        }
                                        
                                        if ($assemblyMaterials->isEmpty()) {
                                            $testingItems = $testing->items
                                                ->where('item_type', 'material')
                                                ->keyBy('material_id');
                                            
                                            // Xác định product để lấy materials
                                            $product = null;
                                            if ($item->item_type == 'finished_product' && $item->good) {
                                                $product = $item->good;
                                            } elseif ($item->item_type == 'product' && $item->product) {
                                                $product = $item->product;
                                            }
                                            
                                            if ($product && isset($product->materials)) {
                                                $assemblyMaterials = $product->materials->map(function($material) use ($testing, $testingItems) {
                                                    $testingItem = $testingItems->get($material->id);
                                                    return (object)[
                                                        'material' => $material,
                                                        'material_id' => $material->id,
                                                        'quantity' => $material->pivot->quantity,
                                                        'serial' => null,
                                                        'testing_item' => $testingItem
                                                    ];
                                                });
                                            }
                                        }
                                    @endphp

                                    @if($assemblyMaterials->isNotEmpty())
                                        @foreach($assemblyMaterials as $materialIndex => $material)
                                        <div class="border border-gray-200 rounded-lg p-3 mb-3" data-material-id="{{ $material->material_id }}">
                                            <div class="flex justify-between items-center mb-3">
                                                <h6 class="font-medium text-gray-700">{{ $materialIndex + 1 }}. {{ $material->material->code }} - {{ $material->material->name }} (Số lượng: {{ $material->quantity }})</h6>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">SERIAL</label>
                                                @php
                                                    $materialSerials = [];
                                                    $materialSerialResultMap = [];
                                                    
                                                    // Lấy serials từ testing item nếu có
                                                    if ($material->testing_item && $material->testing_item->serial_number) {
                                                        $materialSerials = array_filter(array_map('trim', explode(',', $material->testing_item->serial_number)));
                                                        if ($material->testing_item->serial_results) {
                                                            $materialSerialResultMap = json_decode($material->testing_item->serial_results, true);
                                                        }
                                                    } elseif ($material->serial) {
                                                        // Fallback về serial từ assembly nếu không có trong testing_item
                                                        $materialSerials = array_filter(array_map('trim', explode(',', $material->serial)));
                                                    }

                                                    $materialSerialCount = count($materialSerials);
                                                    $quantity = $material->quantity ?? 0;
                                                    $noSerialCount = $quantity - $materialSerialCount;
                                                @endphp
                                                
                                                @if($materialSerialCount > 0)
                                                    <div class="bg-green-50 border-l-4 border-green-400 p-3 rounded">
                                                        <div class="text-xs text-gray-700 mb-2">
                                                            @foreach($materialSerials as $s)
                                                                <div class="mb-0.5">{{ $s }}</div>
                                                            @endforeach
                                                            @for($i = 0; $i < $noSerialCount; $i++)
                                                                <div class="mb-0.5 text-gray-400">N/A</div>
                                                            @endfor
                                                            <div class="text-gray-400">{{ $materialSerialCount }} serial{{ $materialSerialCount > 1 ? 's' : '' }}{{ $noSerialCount > 0 ? ', ' . $noSerialCount . ' N/A' : '' }}</div>
                                                        </div>
                                                        
                                                        @if($testing->status == 'in_progress')
                                                            <div class="grid grid-cols-1 md:grid-cols-{{ min($quantity, 4) }} gap-3 mt-3">
                                                                @for($i = 0; $i < $quantity; $i++)
                                                                    @php
                                                                        $serialLabel = chr(65 + $i); // A, B, C, D, ...
                                                                        $selectedValue = $materialSerialResultMap[$serialLabel] ?? 'pending';
                                                                    @endphp
                                                                    @if($i < $materialSerialCount)
                                                                        <div>
                                                                            <label class="block text-xs text-gray-600 mb-1">Serial {{ $serialLabel }} ({{ $materialSerials[$i] }})</label>
                                                                            <select name="serial_results[{{ $material->material_id }}][{{ $serialLabel }}]" class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                                                <option value="pending" {{ $selectedValue == 'pending' ? 'selected' : '' }}>Chưa có</option>
                                                                                <option value="pass" {{ $selectedValue == 'pass' ? 'selected' : '' }}>Đạt</option>
                                                                                <option value="fail" {{ $selectedValue == 'fail' ? 'selected' : '' }}>Không đạt</option>
                                                                            </select>
                                                                        </div>
                                                                    @else
                                                                        <div>
                                                                            <label class="block text-xs text-gray-600 mb-1">N/A</label>
                                                                            <div class="w-full h-8 border border-gray-300 rounded px-2 text-sm bg-gray-100 text-gray-500 flex items-center">
                                                                                N/A
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endfor
                                                            </div>
                                                        @else
                                                            <div class="text-center text-gray-400 py-2">
                                                                <i class="fas fa-clock mr-2"></i>Chưa tiếp nhận phiếu kiểm thử
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    @if($quantity > 0)
                                                        <div class="bg-green-50 border-l-4 border-green-400 p-3 rounded">
                                                            <div class="text-xs text-gray-700 mb-2">
                                                                @for($i = 0; $i < $quantity; $i++)
                                                                    <div class="mb-0.5 text-gray-400">N/A</div>
                                                                @endfor
                                                                <div class="text-gray-400">{{ $quantity }} N/A</div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="text-center text-gray-500 py-4">
                                                            Thiết bị được lắp ráp mà không sử dụng vật tư có Serial
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                            
                                            @if($materialSerialCount < $material->quantity)
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Không có serial</label>
                                                @if($testing->status == 'in_progress')
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        @php
                                                            $materialPassQuantity = $material->testing_item ? ($material->testing_item->pass_quantity ?? 0) : 0;
                                                            $materialFailQuantity = $material->testing_item ? ($material->testing_item->fail_quantity ?? 0) : 0;
                                                                $remainingQty = $material->quantity - $materialSerialCount;
                                                        @endphp
                                                        <div>
                                                            <label class="block text-xs text-gray-600 mb-1">Số lượng Đạt</label>
                                                                <input type="number" name="item_pass_quantity[{{ $material->material_id }}]" min="0" max="{{ $remainingQty }}" class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $materialPassQuantity }}">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-600 mb-1">Số lượng không Đạt</label>
                                                                <input type="number" name="item_fail_quantity[{{ $material->material_id }}]" min="0" max="{{ $remainingQty }}" class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $materialFailQuantity }}">
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-center text-gray-400 py-2">
                                                        <i class="fas fa-clock mr-2"></i>Chưa tiếp nhận phiếu kiểm thử
                                                    </div>
                                                @endif
                                            </div>
                                            @endif
                                            
                                            <div class="mt-3">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú:</label>
                                                <textarea name="item_notes[{{ $material->testing_item->id ?? $material->material_id }}]" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú cho vật tư này">{{ $material->testing_item->notes ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="text-center text-gray-500 py-4">
                                            Không có vật tư lắp ráp cho thành phẩm này
                                        </div>
                                    @endif
                                    @endif
                                </div>
                                @endif
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú:</label>
                                    <textarea name="item_notes[{{ $item->id }}]" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú cho thiết bị này">{{ $item->notes }}</textarea>
                                </div>
                        </div>
                            @empty
                            <div class="text-center text-gray-500 py-4">
                                Chưa có thiết bị nào được thêm
                            </div>
                            @endforelse
                            </div>
                        </div>

                        <!-- Ghi chú -->
                    <div class="mb-6 border-t border-gray-200 pt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú bổ sung nếu có">{{ $testing->notes }}</textarea>
                    </div>

                    <!-- Submit buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('testing.show', $testing->id) }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-save logic here (if needed)
            const form = document.querySelector('form');
            const autoSaveInterval = 5000; // 5 seconds
            let autoSaveTimer;
            window.triggerAutoSave = function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(() => {
                    const formData = new FormData(form);
                    const data = {};
                    for (let [key, value] of formData.entries()) {
                        // Handle array inputs (e.g., item_notes[], serial_results[][])
                        if (key.endsWith('[]')) {
                            const baseKey = key.slice(0, -2);
                            if (!data[baseKey]) {
                                data[baseKey] = [];
                            }
                            data[baseKey].push(value);
                        } else if (key.includes('[') && key.includes(']')) {
                            const matches = key.match(/(\w+)\[(\w+)\](?:\[(\w+)\])?/);
                            if (matches) {
                                const field = matches[1];
                                const id1 = matches[2];
                                const id2 = matches[3];
                                if (!data[field]) data[field] = {};
                                if (id2) {
                                    if (!data[field][id1]) data[field][id1] = {};
                                    data[field][id1][id2] = value;
                                } else {
                                    data[field][id1] = value;
                                }
                            }
                        } else {
                            data[key] = value;
                        }
                    }

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'X-HTTP-Method-Override': 'PUT' // For Laravel PUT requests via POST
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Auto-saved successfully', data.message);
                            // Optionally show a small success message on the UI
                        } else {
                            console.error('Auto-save failed', data.message, data.errors);
                        }
                    })
                    .catch(error => {
                        console.error('Error during auto-save:', error);
                    });
                }, autoSaveInterval);
            }

            // Attach event listeners to all relevant input fields for auto-save
            form.querySelectorAll('input, select, textarea').forEach(element => {
                element.addEventListener('input', triggerAutoSave);
                element.addEventListener('change', triggerAutoSave); // For select elements
            });

            // Initial call if form is already populated
            // triggerAutoSave(); // Maybe not needed on page load, only on changes
        });

        function addTestItemForItem(itemId) {
            const container = document.getElementById('test_items_container_' + itemId);
            const newItemNameInput = document.getElementById('new_test_item_name_' + itemId);
            const newItemName = newItemNameInput ? newItemNameInput.value.trim() : '';

            if (newItemName === '') {
                alert('Vui lòng nhập tên hạng mục kiểm thử.');
                return;
            }

            // Tạo dữ liệu để gửi lên server
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('_method', 'PUT');
            formData.append('action', 'add_test_detail');
            formData.append('testing_id', '{{ $testing->id }}');
            formData.append('item_id', itemId);
            formData.append('test_item_name', newItemName);

            // Gửi request đến route update thay vì edit
            const updateUrl = '{{ route("testing.update", $testing->id) }}';
            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Tạo HTML cho hạng mục mới với ID thật từ database
                    const newDetailId = data.test_detail_id;
                    const newItemDiv = document.createElement('div');
                    newItemDiv.className = 'test-item flex items-center gap-4';
                    newItemDiv.setAttribute('data-detail-id', newDetailId);
                    newItemDiv.innerHTML = `
                        <input type="text" value="${newItemName}" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" disabled>
                        <button type="button" onclick="removeTestItemForEdit('${newDetailId}', this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    container.appendChild(newItemDiv);
                    if (newItemNameInput) newItemNameInput.value = ''; // Clear input field

                    // Re-attach event listeners for auto-save to new inputs
                    newItemDiv.querySelectorAll('input, select, textarea').forEach(element => {
                        element.addEventListener('input', triggerAutoSave);
                        element.addEventListener('change', triggerAutoSave);
                    });

                    console.log('Đã thêm hạng mục kiểm thử mới:', newItemName);
                } else {
                    alert('Lỗi khi thêm hạng mục kiểm thử: ' + (data.message || 'Không xác định'));
                }
            })
            .catch(error => {
                console.error('Error adding test item:', error);
                alert('Có lỗi xảy ra khi thêm hạng mục kiểm thử');
            });
        }

        function addDefaultTestItemsForEdit(itemId) {
            const container = document.getElementById('test_items_container_' + itemId);
            const defaultItems = [
                'Kiểm tra ngoại quan',
                'Kiểm tra kích thước',
                'Kiểm tra chức năng',
                'Kiểm tra an toàn'
            ];

            defaultItems.forEach(itemName => {
                const newItemDiv = document.createElement('div');
                newItemDiv.className = 'test-item flex items-center gap-4';
                newItemDiv.setAttribute('data-detail-id', 'new_' + itemName); // Use a prefix for new items
                newItemDiv.innerHTML = `
                    <input type="text" value="${itemName}" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" disabled>
                    <button type="button" onclick="removeTestItemForEdit('new_${itemName}', this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                container.appendChild(newItemDiv);

                // Re-attach event listeners for auto-save to new inputs
                newItemDiv.querySelectorAll('input, select, textarea').forEach(element => {
                    element.addEventListener('input', triggerAutoSave);
                    element.addEventListener('change', triggerAutoSave);
                });
            });
            console.log('Đã thêm các hạng mục mặc định.');
        }
        
        function removeTestItemForEdit(detailId, button) {
            const itemDiv = button.closest('.test-item');
            if (!itemDiv) return; // Should not happen if button is correctly placed

            const isNewItem = detailId.startsWith('new_');
            const originalDetailId = isNewItem ? detailId.substring(4) : detailId; // Remove 'new_' prefix

            if (isNewItem) {
                itemDiv.remove();
                console.log('Đã xóa hạng mục kiểm thử mới:', originalDetailId);
            } else {
                // Nếu là item đã tồn tại trong database, xóa khỏi database
                if (confirm('Bạn có chắc chắn muốn xóa hạng mục kiểm thử này?')) {
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('input[name="_token"]').value);
                    formData.append('_method', 'PUT');
                    formData.append('action', 'delete_test_detail');
                    formData.append('detail_id', originalDetailId);

                    // Gửi request đến route update thay vì edit
                    const updateUrl = '{{ route("testing.update", $testing->id) }}';
                    fetch(updateUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'X-HTTP-Method-Override': 'PUT'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            itemDiv.remove();
                            console.log('Đã xóa hạng mục kiểm thử:', originalDetailId);
                        } else {
                            alert('Lỗi khi xóa hạng mục kiểm thử: ' + (data.message || 'Không xác định'));
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting test item:', error);
                        alert('Có lỗi xảy ra khi xóa hạng mục kiểm thử');
                    });
                }
            }
        }
    </script>
</body>
</html> 
