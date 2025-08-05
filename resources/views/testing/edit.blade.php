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
                                        <td class="py-2 px-3 border-b border-gray-200">{{ $item->serial_number ?: 'N/A' }}</td>
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

                                <!-- Kết quả tổng thể cho thiết bị này -->
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

                                <!-- Hạng mục kiểm thử cho thiết bị này -->
                                @if($testing->test_type != 'finished_product' || $testing->details->count() > 0)
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-3">
                                        <h5 class="font-medium text-gray-800">Hạng mục kiểm thử</h5>
                                        @if($testing->test_type != 'finished_product')
                                        <div class="flex items-center gap-2">
                                            <input type="text" placeholder="Ghi chú" class="h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                            <button type="button" onclick="addTestItemForItem({{ $item->id }})" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm flex items-center">
                                                <i class="fas fa-plus mr-1"></i> Thêm hạng mục
                                            </button>
                                        </div>
                                        @else
                                        <div class="text-sm text-gray-500">
                                            <i class="fas fa-info-circle mr-1"></i> Hạng mục kiểm thử được map từ phiếu lắp ráp
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <div class="space-y-4" id="test_items_container_{{ $item->id }}">
                                        <!-- Các hạng mục kiểm thử từ database -->
                                        @forelse($testing->details as $detailIndex => $detail)
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <div class="flex justify-between items-center mb-3">
                                                    <h6 class="font-medium text-gray-700">{{ $detail->test_item_name }}</h6>
                                                    <button type="button" onclick="removeTestItem(this)" class="px-2 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">SERIAL</label>
                                                    @php
                                                        $serials = [];
                                                        if ($item->serial_number) {
                                                            $serials = array_filter(array_map('trim', explode(',', $item->serial_number)));
                                                        }
                                                        $serialCount = count($serials);
                                                    @endphp
                                                    
                                                    @if($serialCount > 0)
                                                        <div class="grid grid-cols-1 md:grid-cols-{{ min($serialCount, 4) }} gap-3">
                                                            @foreach($serials as $index => $serial)
                                                                @php
                                                                    $serialLabel = chr(65 + $index); // A, B, C, D, ...
                                                                    $serialResults = [];
                                                                    if ($item->serial_results) {
                                                                        $serialResults = json_decode($item->serial_results, true);
                                                                    }
                                                                    $selectedValue = $serialResults[$serialLabel] ?? 'pending';
                                                                @endphp
                                                                <div>
                                                                    <label class="block text-xs text-gray-600 mb-1">Serial {{ $serialLabel }} ({{ $serial }})</label>
                                                                    <select name="serial_results[{{ $item->id }}][{{ $serialLabel }}]" class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                                        <option value="pending" {{ $selectedValue == 'pending' ? 'selected' : '' }}>Chưa có</option>
                                                                        <option value="pass" {{ $selectedValue == 'pass' ? 'selected' : '' }}>Đạt</option>
                                                                        <option value="fail" {{ $selectedValue == 'fail' ? 'selected' : '' }}>Không đạt</option>
                                                                    </select>
                                                                    <input type="hidden" value="{{ $serial }}" class="serial-value">
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                            <div>
                                                                <label class="block text-xs text-gray-600 mb-1">Serial A (không thể chỉnh sửa)</label>
                                                                <select class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" disabled>
                                                                    <option>Chưa có</option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs text-gray-600 mb-1">Serial B (không thể chỉnh sửa)</label>
                                                                <select class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" disabled>
                                                                    <option>Chưa có</option>
                                            </select>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Không có serial</label>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        @php
                                                            $passQuantity = $detail->test_pass_quantity ?? 0;
                                                            $failQuantity = $detail->test_fail_quantity ?? 0;
                                                        @endphp
                                                        <div>
                                                            <label class="block text-xs text-gray-600 mb-1">Số lượng Đạt</label>
                                                            <input type="number" name="test_pass_quantity[{{ $item->id }}][{{ $detail->id }}]" min="0" class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $passQuantity }}">
                                                        </div>
                                    <div>
                                                            <label class="block text-xs text-gray-600 mb-1">Số lượng không Đạt</label>
                                                            <input type="number" name="test_fail_quantity[{{ $item->id }}][{{ $detail->id }}]" min="0" class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $failQuantity }}">
                                                        </div>
                                                    </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center text-gray-500 py-4">
                                                Chưa có hạng mục kiểm thử nào được thêm
                                </div>
                                @endforelse
                            </div>
                        </div>
                        @endif

                                    <!-- Vật tư lắp ráp cho thành phẩm này (chỉ hiển thị cho finished_product) -->
                                @if($item->item_type == 'finished_product' || ($item->item_type == 'product' && $testing->test_type == 'finished_product'))
                                <div class="mb-4 border-t border-gray-200 pt-4">
                                    <h5 class="font-medium text-gray-800 mb-3">Kiểm thử vật tư lắp ráp <span class="text-sm text-gray-500">(# chỉ của loại thành phẩm này, không phải của toàn bộ phiếu)</span></h5>
                                    
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
                                        <div class="border border-gray-200 rounded-lg p-3 mb-3">
                                            <div class="flex justify-between items-center mb-3">
                                                <h6 class="font-medium text-gray-700">{{ $materialIndex + 1 }}. {{ $material->material->code }} - {{ $material->material->name }} (map từ phiếu Lắp ráp)</h6>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">SERIAL</label>
                                                @php
                                                    $materialSerials = [];
                                                    if ($material->serial) {
                                                        $materialSerials = array_filter(array_map('trim', explode(',', $material->serial)));
                                                    }
                                                    $materialSerialCount = count($materialSerials);
                                                @endphp
                                                
                                                @if($materialSerialCount > 0)
                                                    <div class="grid grid-cols-1 md:grid-cols-{{ min($materialSerialCount, 4) }} gap-3">
                                                        @foreach($materialSerials as $index => $serial)
                                                            @php
                                                                $serialLabel = chr(67 + $index); // C, D, E, F, ... (bắt đầu từ C để tránh trùng với thành phẩm)
                                                                $materialSerialResults = [];
                                                                if ($material->testing_item && $material->testing_item->serial_results) {
                                                                    $materialSerialResults = json_decode($material->testing_item->serial_results, true);
                                                                }
                                                                $selectedValue = $materialSerialResults[$serialLabel] ?? 'pending';
                                                            @endphp
                                                            <div>
                                                                <label class="block text-xs text-gray-600 mb-1">Serial {{ $serialLabel }} ({{ $serial }})</label>
                                                                <select name="serial_results[{{ $material->material_id }}][{{ $serialLabel }}]" class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                                    <option value="pending" {{ $selectedValue == 'pending' ? 'selected' : '' }}>Chưa có</option>
                                                                    <option value="pass" {{ $selectedValue == 'pass' ? 'selected' : '' }}>Đạt</option>
                                                                    <option value="fail" {{ $selectedValue == 'fail' ? 'selected' : '' }}>Không đạt</option>
                                                                </select>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="block text-xs text-gray-600 mb-1">Serial C (không thể chỉnh sửa)</label>
                                                            <select class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" disabled>
                                                                <option>Chưa có</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-600 mb-1">Serial D (không thể chỉnh sửa)</label>
                                                            <select class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" disabled>
                                                                <option>Chưa có</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Không có serial</label>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    @php
                                                        $materialPassQuantity = $material->testing_item ? ($material->testing_item->pass_quantity ?? 0) : 0;
                                                        $materialFailQuantity = $material->testing_item ? ($material->testing_item->fail_quantity ?? 0) : 0;
                                                    @endphp
                                                    <div>
                                                        <label class="block text-xs text-gray-600 mb-1">Số lượng Đạt</label>
                                                        <input type="number" name="item_pass_quantity[{{ $material->material_id }}]" min="0" class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $materialPassQuantity }}">
                                                    </div>
                                <div>
                                                        <label class="block text-xs text-gray-600 mb-1">Số lượng không Đạt</label>
                                                        <input type="number" name="item_fail_quantity[{{ $material->material_id }}]" min="0" class="w-full h-8 border border-gray-300 rounded px-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $materialFailQuantity }}">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú MAP TỪ PHIẾU LẮP RÁP:</label>
                                                <textarea class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" rows="2" readonly>{{ $material->material->notes ?? 'Không có ghi chú từ phiếu lắp ráp' }}</textarea>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="text-center text-gray-500 py-4">
                                            Không có vật tư lắp ráp cho thành phẩm này
                                        </div>
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
        function addTestItem() {
            const container = document.getElementById('test_items_container');
            const newItem = document.createElement('div');
            newItem.className = 'test-item flex items-center gap-4';
            newItem.innerHTML = `
                <input type="text" name="test_item_names[]" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập hạng mục kiểm thử (không bắt buộc)">
                <select name="test_results[]" class="testing-detail-result h-10 border border-gray-300 rounded px-3 py-2 w-32 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="pending">Chưa có</option>
                        <option value="pass">Đạt</option>
                        <option value="fail">Không đạt</option>
                    </select>
                <input type="text" name="test_notes[]" class="h-10 border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú">
                <button type="button" onclick="removeTestItem(this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                        <i class="fas fa-trash"></i>
                    </button>
            `;
            container.appendChild(newItem);
            
            // Thêm event listener cho select kết quả mới
            const newSelect = newItem.querySelector('select[name="test_results[]"]');
            if (newSelect) {
                newSelect.addEventListener('change', updateTestResults);
            }
        }
        
        function removeTestItem(button) {
            const container = document.getElementById('test_items_container');
            const item = button.closest('.test-item');
            
            // Don't remove if it's the last one
            if (container.children.length > 1) {
                container.removeChild(item);
                
                // Cập nhật lại kết quả sau khi xóa
                updateTestResults();
            }
        }
        
        function addDefaultTestItems() {
            const container = document.getElementById('test_items_container');
            container.innerHTML = '';
            
            const defaultItems = [
                'Kiểm tra ngoại quan',
                'Kiểm tra kích thước',
                'Kiểm tra chức năng',
                'Kiểm tra an toàn'
            ];
            
            defaultItems.forEach(item => {
                const div = document.createElement('div');
                div.className = 'test-item flex items-center gap-4';
                div.innerHTML = `
                    <input type="text" name="test_item_names[]" value="${item}" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <select name="test_results[]" class="testing-detail-result h-10 border border-gray-300 rounded px-3 py-2 w-32 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="pending">Chưa có</option>
                        <option value="pass">Đạt</option>
                        <option value="fail">Không đạt</option>
                    </select>
                    <input type="text" name="test_notes[]" class="h-10 border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú">
                    <button type="button" onclick="removeTestItem(this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                container.appendChild(div);
            });
        }

        function addTestItemForItem(itemId) {
            const container = document.getElementById('test_items_container_' + itemId);
            const newItem = document.createElement('div');
            newItem.className = 'test-item flex items-center gap-4';
            newItem.innerHTML = `
                <input type="text" name="test_item_names[${itemId}][]" class="h-10 border border-gray-300 rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập hạng mục kiểm thử">
                <select name="test_results[${itemId}][]" class="testing-detail-result h-10 border border-gray-300 rounded px-3 py-2 w-32 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="pending">Chưa có</option>
                    <option value="pass">Đạt</option>
                    <option value="fail">Không đạt</option>
                </select>
                <input type="text" name="test_notes[${itemId}][]" class="h-10 border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú">
                <button type="button" onclick="removeTestItem(this)" class="px-3 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(newItem);
        }
    </script>
</body>
</html> 
