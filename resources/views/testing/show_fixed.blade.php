@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Chi tiết phiếu kiểm thử</h1>
                <p class="text-gray-600">{{ $testing->test_code }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('testing.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ route('testing.print', $testing->id) }}" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-print mr-2"></i> In phiếu
                </a>
            </div>
        </div>

        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-sm text-gray-500 font-medium mb-1">Mã phiếu kiểm thử</p>
                    <p class="text-base text-gray-800 font-semibold">{{ $testing->test_code }}</p>
                </div>
                
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-sm text-gray-500 font-medium mb-1">Loại kiểm thử</p>
                    <p class="text-base text-gray-800 font-semibold">
                        @if($testing->test_type == 'material')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Vật tư/Hàng hóa</span>
                        @elseif($testing->test_type == 'finished_product')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Thiết bị thành phẩm</span>
                        @endif
                    </p>
                </div>
                
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-sm text-gray-500 font-medium mb-1">Ngày kiểm thử</p>
                    <p class="text-base text-gray-800 font-semibold">{{ $testing->test_date ? \Carbon\Carbon::parse($testing->test_date)->format('d/m/Y') : 'N/A' }}</p>
                </div>
                
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái</p>
                    <p class="text-base text-gray-800 font-semibold">
                        @if($testing->status == 'pending')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Chờ xử lý</span>
                        @elseif($testing->status == 'in_progress')
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Đang thực hiện</span>
                        @elseif($testing->status == 'completed')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Hoàn thành</span>
                        @elseif($testing->status == 'cancelled')
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Đã hủy</span>
                        @endif
                    </p>
                </div>
            </div>
            
            <div>
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-sm text-gray-500 font-medium mb-1">Người kiểm thử</p>
                    <p class="text-base text-gray-800 font-semibold">{{ $testing->tester->name ?? 'N/A' }}</p>
                </div>
                
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-sm text-gray-500 font-medium mb-1">Người tiếp nhận</p>
                    <p class="text-base text-gray-800 font-semibold">{{ $testing->receiverEmployee->name ?? 'N/A' }}</p>
                </div>
                
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-sm text-gray-500 font-medium mb-1">Người phụ trách</p>
                    <p class="text-base text-gray-800 font-semibold">{{ $testing->assignedEmployee->name ?? 'N/A' }}</p>
                </div>
                
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <p class="text-sm text-gray-500 font-medium mb-1">Ghi chú</p>
                    <p class="text-base text-gray-800 font-semibold">{{ $testing->notes ?: 'Không có' }}</p>
                </div>
            </div>
        </div>

        <!-- Testing Items -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Chi tiết kiểm thử</h2>
            
            @forelse($testing->items as $itemIndex => $item)
            <div class="border border-gray-200 rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-800">{{ $itemIndex + 1 }}. {{ $item->item_type == 'material' ? ($item->material->name ?? 'N/A') : ($item->good->name ?? 'N/A') }}</h3>
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-sm">{{ $item->quantity }} cái</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-500 font-medium mb-1">Mã</p>
                        <p class="text-base text-gray-800">{{ $item->item_type == 'material' ? ($item->material->code ?? 'N/A') : ($item->good->code ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium mb-1">Kho</p>
                        <p class="text-base text-gray-800">{{ $item->warehouse->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium mb-1">Serial</p>
                        <p class="text-base text-gray-800">{{ $item->serial_number ?: 'Không có' }}</p>
                    </div>
                </div>

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
                                // Tính toán số lượng thành phẩm cụ thể này
                                $currentProductQuantity = $item->quantity ?? 1;
                                
                                // Lấy tổng số lượng thành phẩm từ phiếu lắp ráp
                                $totalAssemblyProductQuantity = 0;
                                if ($testing->assembly->products && $testing->assembly->products->count() > 0) {
                                    $totalAssemblyProductQuantity = $testing->assembly->products
                                        ->where('product_id', $productId)
                                        ->sum('quantity');
                                } else {
                                    // Fallback cho trường hợp assembly cũ
                                    $totalAssemblyProductQuantity = $testing->assembly->quantity ?? 1;
                                }
                                
                                // Tính tỉ lệ để chia vật tư
                                $ratio = $totalAssemblyProductQuantity > 0 ? $currentProductQuantity / $totalAssemblyProductQuantity : 1;
                                
                                $assemblyMaterials = $testing->assembly->materials
                                    ->where('target_product_id', $productId)
                                    ->map(function($asmMaterial) use ($testingItems, $ratio) {
                                        $testingItem = $testingItems->get($asmMaterial->material_id);
                                        
                                        // Tính số lượng vật tư cho thành phẩm này
                                        $adjustedQuantity = round($asmMaterial->quantity * $ratio);
                                        
                                        return (object)[
                                            'material' => $asmMaterial->material,
                                            'material_id' => $asmMaterial->material_id,
                                            'quantity' => $adjustedQuantity,
                                            'original_quantity' => $asmMaterial->quantity,
                                            'ratio' => $ratio,
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
                                @if(isset($material->ratio))
                                    <span class="text-xs text-gray-500">Tỉ lệ: {{ number_format($material->ratio, 2) }} ({{ $material->quantity }}/{{ $material->original_quantity }})</span>
                                @endif
                            </div>
                            
                            <!-- Serial và kết quả kiểm thử cho vật tư lắp ráp -->
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
                                                <input type="hidden" value="{{ $serial }}" class="serial-value">
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
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-gray-500 py-4">
                            Không có vật tư lắp ráp nào được tìm thấy
                        </div>
                    @endif
                </div>
                @endif
            </div>
            @empty
                <div class="text-center text-gray-500 py-4">
                    Chưa có thiết bị kiểm thử nào được thêm
                </div>
            @endforelse
        </div>

        <!-- Testing Details -->
        @if($testing->details && $testing->details->count() > 0)
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Hạng mục kiểm thử</h2>
            <div class="space-y-4">
                @forelse($testing->details as $detailIndex => $detail)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-800">{{ $detailIndex + 1 }}. {{ $detail->test_item_name }}</h3>
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-sm">
                            @if($detail->result == 'pending')
                                <span class="text-yellow-600">Chưa có</span>
                            @elseif($detail->result == 'pass')
                                <span class="text-green-600">Đạt</span>
                            @elseif($detail->result == 'fail')
                                <span class="text-red-600">Không đạt</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kết quả</label>
                            <select name="test_results[{{ $detail->id }}]" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="pending" {{ $detail->result == 'pending' ? 'selected' : '' }}>Chưa có</option>
                                <option value="pass" {{ $detail->result == 'pass' ? 'selected' : '' }}>Đạt</option>
                                <option value="fail" {{ $detail->result == 'fail' ? 'selected' : '' }}>Không đạt</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <input type="text" name="test_notes[{{ $detail->id }}]" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $detail->notes }}">
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

        <!-- Assembly Information -->
        @if($testing->assembly)
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                Thông tin lắp ráp liên quan
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <p class="text-sm text-gray-500 font-medium mb-1">Mã phiếu lắp ráp</p>
                        <p class="text-base text-gray-800 font-semibold">{{ $testing->assembly->code }}</p>
                    </div>
                    
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <p class="text-sm text-gray-500 font-medium mb-1">Ngày lắp ráp</p>
                        <p class="text-base text-gray-800 font-semibold">{{ $testing->assembly->date ? \Carbon\Carbon::parse($testing->assembly->date)->format('d/m/Y') : 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <p class="text-sm text-gray-500 font-medium mb-1">Người lắp ráp</p>
                        <p class="text-base text-gray-800 font-semibold">{{ $testing->assembly->assignedEmployee->name ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <div>
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <p class="text-sm text-gray-500 font-medium mb-1">Thành phẩm</p>
                        <div class="space-y-2">
                            @if($testing->assembly->products && $testing->assembly->products->count() > 0)
                                @foreach($testing->assembly->products as $product)
                                    <div class="flex items-center">
                                        <span class="text-base text-gray-800 font-semibold">{{ $product->product->name ?? 'N/A' }}</span>
                                        <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ $product->quantity }} cái</span>
                                    </div>
                                    @if($product->serials)
                                        <div class="text-sm text-gray-600">Serial: {{ $product->serials }}</div>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-base text-gray-800 font-semibold">{{ $testing->assembly->product->name ?? 'N/A' }} ({{ $testing->assembly->quantity }})</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái lắp ráp</p>
                        <p class="text-base text-gray-800 font-semibold">
                            @if($testing->assembly->status == 'pending')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Chờ xử lý</span>
                            @elseif($testing->assembly->status == 'in_progress')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Đang thực hiện</span>
                            @elseif($testing->assembly->status == 'completed')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Hoàn thành</span>
                            @elseif($testing->assembly->status == 'cancelled')
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Đã hủy</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            @if($testing->assembly->materials && $testing->assembly->materials->count() > 0)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-md font-medium text-gray-800 mb-3">Danh sách vật tư lắp ráp</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($testing->assembly->materials as $index => $material)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $material->material->code }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $material->material->name }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $material->quantity }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                    @if($material->serial && str_contains($material->serial, ','))
                                        <div class="space-y-1">
                                            @foreach(explode(',', $material->serial) as $serial)
                                                @if(!empty($serial))
                                                    <div class="bg-gray-50 px-2 py-1 rounded">{{ $serial }}</div>
                                                @endif
                                            @endforeach
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">{{ count(array_filter(explode(',', $material->serial))) }} serial</div>
                                    @else
                                        {{ $material->serial ?: 'N/A' }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Results Section -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Kết quả kiểm thử</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng đạt</label>
                    <input type="number" name="pass_quantity" min="0" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $testing->pass_quantity ?? 0 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng không đạt</label>
                    <input type="number" name="fail_quantity" min="0" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ $testing->fail_quantity ?? 0 }}">
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Lý do không đạt</label>
                <textarea name="fail_reasons" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">{{ $testing->fail_reasons }}</textarea>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Kết luận</label>
                <textarea name="conclusion" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">{{ $testing->conclusion }}</textarea>
            </div>
        </div>

        <!-- Warehouse Update Information -->
        @if($testing->is_inventory_updated)
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Thông tin cập nhật kho</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <p class="text-sm text-gray-500 font-medium mb-1">Kho lưu thành phẩm đạt</p>
                        <p class="text-base text-gray-800 font-semibold">{{ $testing->successWarehouse->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div>
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <p class="text-sm text-gray-500 font-medium mb-1">Kho lưu vật tư không đạt</p>
                        <p class="text-base text-gray-800 font-semibold">{{ $testing->failWarehouse->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
            <div class="flex gap-2">
                @if($testing->status == 'pending')
                    <form action="{{ route('testing.approve', $testing->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-check mr-2"></i> Duyệt
                        </button>
                    </form>
                    <form action="{{ route('testing.reject', $testing->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i> Từ chối
                        </button>
                    </form>
                @endif
                
                @if($testing->status == 'in_progress')
                    <form action="{{ route('testing.complete', $testing->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-check-double mr-2"></i> Hoàn thành
                        </button>
                    </form>
                @endif
                
                @if($testing->status == 'completed' && !$testing->is_inventory_updated)
                    <a href="{{ route('testing.update-inventory', $testing->id) }}" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-warehouse mr-2"></i> Cập nhật kho
                    </a>
                @endif
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('testing.edit', $testing->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                @if($testing->status == 'pending')
                    <form action="{{ route('testing.destroy', $testing->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu kiểm thử này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-trash mr-2"></i> Xóa
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Auto-save functionality -->
<script>
let autoSaveTimeout;
const autoSaveDelay = 2000; // 2 seconds

function autoSave() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        const formData = new FormData();
        
        // Collect all form data
        const form = document.querySelector('form');
        if (form) {
            const formElements = form.elements;
            for (let element of formElements) {
                if (element.name && element.value !== undefined) {
                    formData.append(element.name, element.value);
                }
            }
        }
        
        // Add CSRF token
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'PUT');
        
        fetch('{{ route("testing.update", $testing->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Auto-saved successfully');
            } else {
                console.error('Auto-save failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Auto-save error:', error);
        });
    }, autoSaveDelay);
}

// Add event listeners for auto-save
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('change', autoSave);
        input.addEventListener('input', autoSave);
    });
});
</script>
@endsection 