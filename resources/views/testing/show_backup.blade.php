<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu kiểm thử - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .print-only {
                display: block;
            }
            body {
                font-size: 12pt;
                color: #000;
                background-color: #fff;
            }
            .content-area {
                margin: 0;
                padding: 0;
            }
            .page-break {
                page-break-before: always;
            }
        }
        .print-only {
            display: none;
        }
    </style>
</head>
<body>
    <x-sidebar-component class="no-print" />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40 no-print">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu kiểm thử</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $testing->test_code }}
                </div>
                <div class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $testing->test_type_text }}
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('testing.index') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <button onclick="window.print()" class="h-10 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-print mr-2"></i> In phiếu
                </button>
                <a href="{{ route('testing.edit', $testing->id) }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Sửa
                </a>
            </div>
        </header>

        <!-- Print Header (only visible when printing) -->
        <div class="print-only p-6 border-b border-gray-300 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="SGL Logo" class="h-16 mr-4">
                    <div>
                        <h1 class="text-xl font-bold">CÔNG TY CỔ PHẦN CÔNG NGHỆ SGL</h1>
                        <p class="text-gray-600">Địa chỉ: 123 Đường XYZ, Quận ABC, TP. HCM</p>
                    </div>
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-bold uppercase">Phiếu kiểm thử</h2>
                    <p class="text-lg font-bold text-blue-800">{{ $testing->test_code }}</p>
                </div>
            </div>
        </div>

        <main class="p-6 space-y-6">
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
            
            <!-- Thông tin cơ bản -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">Thông tin phiếu kiểm thử</h2>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-calendar-alt mr-1"></i> Ngày kiểm thử: {{ $testing->test_date->format('d/m/Y') }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                            @if($testing->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($testing->status == 'in_progress') bg-blue-100 text-blue-800
                            @elseif($testing->status == 'completed') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            <i class="fas fa-circle mr-1 text-xs"></i> {{ $testing->status_text }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cột 1 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Loại kiểm thử</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->test_type_text }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Người tạo phiếu</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->tester->name ?? 'N/A' }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Người phụ trách</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->assignedEmployee->name ?? 'N/A' }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Người tiếp nhận kiểm thử</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->receiverEmployee->name ?? 'N/A' }}</p>
                        </div>
                        
                        @if($testing->approved_by)
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Người duyệt</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->approver->name ?? 'N/A' }}</p>
                        </div>
                        @endif
                        
                        @if($testing->notes)
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ghi chú</p>
                            <p class="text-base text-gray-800">{{ $testing->notes }}</p>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Cột 2 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ngày kiểm thử</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->test_date->format('d/m/Y') }}</p>
                        </div>
                        
                        @if($testing->approved_at)
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ngày duyệt</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->approved_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                        
                        @if($testing->received_at)
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ngày tiếp nhận</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->received_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                        
                        @if($testing->status == 'completed')
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Kết quả kiểm thử</p>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> {{ $testing->pass_rate }}% Đạt
                                </span>
                                <span class="text-sm text-gray-600">({{ $testing->pass_quantity }} Đạt / {{ $testing->fail_quantity }} Không đạt)</span>
                            </div>
                        </div>
                        
                        @if($testing->conclusion)
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Kết luận</p>
                            <p class="text-base text-gray-800">{{ $testing->conclusion }}</p>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Danh sách thiết bị và vật tư -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Chi tiết thiết bị kiểm thử</h2>

                @if($testing->status == 'in_progress')
                <form action="{{ route('testing.update', $testing->id) }}" method="POST" class="mb-4" id="test-item-form">
                    @csrf
                    @method('PUT')
                    
                    <!-- Thêm các trường ẩn cần thiết -->
                    <input type="hidden" name="tester_id" value="{{ $testing->tester_id }}">
                    <input type="hidden" name="assigned_to" value="{{ $testing->assigned_to }}">
                    <input type="hidden" name="receiver_id" value="{{ $testing->receiver_id }}">
                    <input type="hidden" name="test_date" value="{{ $testing->test_date->format('Y-m-d') }}">
                    <input type="hidden" name="notes" value="{{ $testing->notes }}">
                    
                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-4">
                            <div class="font-medium">Có lỗi xảy ra:</div>
                            <ul class="mt-1.5 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                @endif
                
                    <div class="space-y-6">
                        @foreach($testing->items->where('item_type', 'product') as $itemIndex => $item)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="mb-4 pb-3 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            {{ $itemIndex + 1 }}. {{ $item->product->code }} - {{ $item->product->name }}
                                        </h3>
                                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
                                            <span>Loại: Thành phẩm</span>
                                            <span>Serial: {{ $item->serial_number ?: 'N/A' }}</span>
                                            <span>Số lượng: {{ $item->quantity }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kết quả tổng thể:</label>
                                        <select name="item_results[{{ $item->id }}]" class="form-select testing-item-result rounded-md shadow-sm mt-1 block w-full" required data-material-id="{{ $item->product_id }}" data-material-name="{{ $item->product->name }}">
                                            <option value="pending" {{ $item->result == 'pending' ? 'selected' : '' }}>Chưa có</option>
                                            <option value="pass" {{ $item->result == 'pass' ? 'selected' : '' }}>Đạt</option>
                                            <option value="fail" {{ $item->result == 'fail' ? 'selected' : '' }}>Không đạt</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vật tư lắp ráp cho thành phẩm này -->
                            <div class="mb-4">
                                <h4 class="font-medium text-gray-800 mb-2">Vật tư lắp ráp cho thành phẩm này:</h4>
                                @php
                                    // Lấy vật tư từ assembly nếu có
                                    $assemblyMaterials = collect();
                                    if ($testing->assembly) {
                                        // Lấy danh sách testing_items cho các vật tư để dễ truy cập
                                        $testingItems = $testing->items
                                            ->where('item_type', 'material')
                                            ->keyBy('material_id');
                                        
                                        $assemblyMaterials = $testing->assembly->materials
                                            ->where('target_product_id', $item->product_id)
                                            ->map(function($asmMaterial) use ($testingItems) {
                                                // Tìm testing_item tương ứng trong danh sách
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
                                    
                                    // Nếu không có assembly hoặc không có vật tư từ assembly, 
                                    // lấy từ product_materials
                                    if ($assemblyMaterials->isEmpty() && $item->product) {
                                        // Lấy danh sách testing_items cho các vật tư để dễ truy cập
                                        $testingItems = $testing->items
                                            ->where('item_type', 'material')
                                            ->keyBy('material_id');
                                            
                                        $assemblyMaterials = $item->product->materials->map(function($material) use ($testing, $testingItems) {
                                            // Tìm testing_item tương ứng trong danh sách
                                            $testingItem = $testingItems->get($material->id);
                                            
                                            // Tạo object giống với assembly_materials để dùng chung code
                                            return (object)[
                                                'material' => $material,
                                                'material_id' => $material->id,
                                                'quantity' => $material->pivot->quantity,
                                                'serial' => null,
                                                // Tìm testing_item tương ứng nếu có
                                                'testing_item' => $testingItem
                                            ];
                                        });
                                    }
                                @endphp

                                @if($assemblyMaterials->isNotEmpty())
                                    <table class="min-w-full divide-y divide-gray-200 mb-2">
                        <thead class="bg-gray-50">
                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã vật tư</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kết quả kiểm thử</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                                            @foreach($assemblyMaterials as $material)
                                                <tr>
                                                    <td class="px-4 py-2 text-sm">{{ $material->material->code }}</td>
                                                    <td class="px-4 py-2 text-sm">{{ $material->material->name }}</td>
                                                    <td class="px-4 py-2 text-sm">{{ $material->quantity }}</td>
                                                    <td class="px-4 py-2 text-sm">{{ $material->material->unit }}</td>
                                                    <td class="px-4 py-2 text-sm">
                                                        @if($testing->status == 'in_progress')
                                                            <select name="item_results[{{ $material->material_id }}]" class="form-select testing-item-result rounded-md shadow-sm mt-1 block w-full" data-material-id="{{ $material->material_id }}" data-material-name="{{ $material->material->name }}">
                                                                <option value="pending" {{ ($material->testing_item && $material->testing_item->result == 'pending') ? 'selected' : '' }}>Chưa có</option>
                                                                <option value="pass" {{ ($material->testing_item && $material->testing_item->result == 'pass') ? 'selected' : '' }}>Đạt</option>
                                                                <option value="fail" {{ ($material->testing_item && $material->testing_item->result == 'fail') ? 'selected' : '' }}>Không đạt</option>
                                                            </select>
                                                            {{-- Debug info: {{ $material->testing_item ? 'ID:' . $material->testing_item->id . ', Result:' . $material->testing_item->result : 'No testing item found' }} --}}
                                                        @else
                                                            @if($material->testing_item)
                                                                @if($material->testing_item->result == 'pass')
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Đạt</span>
                                                                @elseif($material->testing_item->result == 'fail')
                                                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Không đạt</span>
                                                                @else
                                                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                                                                @endif
                                                                {{-- Debug info: {{ 'ID:' . $material->testing_item->id . ', Result:' . $material->testing_item->result }} --}}
                                                            @else
                                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                                                                    {{-- Debug info: No testing item found for material {{ $material->material_id }} --}}
                                                            @endif
                                                        @endif
                                                    </td>
                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-gray-500">Không có vật tư lắp ráp cho thành phẩm này.</div>
                                @endif
                            </div>
                            
                            <!-- Hạng mục kiểm thử cho thiết bị này -->
                            <div class="mb-4">
                                <h4 class="font-medium text-gray-800 mb-3">Hạng mục kiểm thử:</h4>
                                <div class="space-y-3">
                                    @forelse($testing->details as $detailIndex => $detail)
                                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1">
                                            <span class="font-medium text-gray-800">{{ $detailIndex + 1 }}. {{ $detail->test_item_name }}</span>
                                        </div>
                                        <div class="w-32">
                                            <select name="test_results[{{ $detail->id }}]" class="testing-detail-result h-10 border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                                <option value="pending" {{ $detail->result == 'pending' ? 'selected' : '' }}>Chưa có</option>
                                                <option value="pass" {{ $detail->result == 'pass' ? 'selected' : '' }}>Đạt</option>
                                                <option value="fail" {{ $detail->result == 'fail' ? 'selected' : '' }}>Không đạt</option>
                                            </select>
                                        </div>
                                        <div class="flex-1">
                                            <input type="text" name="test_notes[{{ $detail->id }}]" value="{{ $detail->notes }}" class="h-10 border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú">
                                        </div>
                                    </div>
                            @empty
                                    <div class="text-center text-gray-500 py-4">
                                        Chưa có hạng mục kiểm thử nào được thêm
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                            
                            <!-- Ghi chú cho thiết bị -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú thiết bị:</label>
                                <input type="text" name="item_notes[{{ $item->id }}]" value="{{ $item->notes }}" class="h-10 border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú cho thiết bị này">
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="test-item-submit-button px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                            <i class="fas fa-save mr-2"></i> Lưu kết quả kiểm thử
                        </button>
                    </div>
                </form>
                @else
                <div class="space-y-6">
                    @foreach($testing->items->where('item_type', 'product') as $itemIndex => $item)
                    <div class="mb-8 border-b border-gray-200 pb-6">
                        <h4 class="text-lg font-medium text-gray-800 mb-4">{{ $loop->iteration }}. {{ $item->product->code }} - {{ $item->product->name }}</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <p class="text-gray-600">Loại: Thành phẩm</p>
                                <p class="text-gray-600">Serial: {{ $item->serial_number ?: 'N/A' }}</p>
                                <p class="text-gray-600">Số lượng: {{ $item->quantity }}</p>
                            </div>

                            <div>
                                <h5 class="font-medium text-gray-800 mb-2">Kết quả tổng thể:</h5>
                                <div class="mb-4">
                                    @if($item->result == 'pass')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Đạt</span>
                                    @elseif($item->result == 'fail')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Không đạt</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Chưa có</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <h5 class="font-medium text-gray-800 mb-2">Vật tư lắp ráp cho thành phẩm này:</h5>
                                @php
                                    // Lấy vật tư từ assembly nếu có
                                    $assemblyMaterials = collect();
                                    if ($testing->assembly) {
                                        // Lấy danh sách testing_items cho các vật tư để dễ truy cập
                                        $testingItems = $testing->items
                                            ->where('item_type', 'material')
                                            ->keyBy('material_id');
                                        
                                        $assemblyMaterials = $testing->assembly->materials
                                            ->where('target_product_id', $item->product_id)
                                            ->map(function($asmMaterial) use ($testingItems) {
                                                // Tìm testing_item tương ứng trong danh sách
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
                                    
                                    // Nếu không có assembly hoặc không có vật tư từ assembly, 
                                    // lấy từ product_materials
                                    if ($assemblyMaterials->isEmpty() && $item->product) {
                                        // Lấy danh sách testing_items cho các vật tư để dễ truy cập
                                        $testingItems = $testing->items
                                            ->where('item_type', 'material')
                                            ->keyBy('material_id');
                                            
                                        $assemblyMaterials = $item->product->materials->map(function($material) use ($testing, $testingItems) {
                                            // Tìm testing_item tương ứng trong danh sách
                                            $testingItem = $testingItems->get($material->id);
                                            
                                            // Tạo object giống với assembly_materials để dùng chung code
                                            return (object)[
                                                'material' => $material,
                                                'material_id' => $material->id,
                                                'quantity' => $material->pivot->quantity,
                                                'serial' => null,
                                                // Tìm testing_item tương ứng nếu có
                                                'testing_item' => $testingItem
                                            ];
                                        });
                                    }
                                @endphp

                                @if($assemblyMaterials->isNotEmpty())
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead>
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã vật tư</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kết quả kiểm thử</th>
                            </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($assemblyMaterials as $material)
                                                    <tr>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $material->material->code }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $material->material->name }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $material->quantity }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $material->material->unit }}</td>
                                                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                                                            @if($testing->status == 'in_progress')
                                                                <select name="item_results[{{ $material->material_id }}]" class="form-select testing-item-result rounded-md shadow-sm mt-1 block w-full" data-material-id="{{ $material->material_id }}" data-material-name="{{ $material->material->name }}">
                                                                    <option value="pending" {{ ($material->testing_item && $material->testing_item->result == 'pending') ? 'selected' : '' }}>Chưa có</option>
                                                                    <option value="pass" {{ ($material->testing_item && $material->testing_item->result == 'pass') ? 'selected' : '' }}>Đạt</option>
                                                                    <option value="fail" {{ ($material->testing_item && $material->testing_item->result == 'fail') ? 'selected' : '' }}>Không đạt</option>
                                                                </select>
                                                                {{-- Debug info: {{ $material->testing_item ? 'ID:' . $material->testing_item->id . ', Result:' . $material->testing_item->result : 'No testing item found' }} --}}
                                                            @else
                                                                @if($material->testing_item)
                                                                    @if($material->testing_item->result == 'pass')
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Đạt</span>
                                                                    @elseif($material->testing_item->result == 'fail')
                                                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Không đạt</span>
                                                                    @else
                                                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                                                                    @endif
                                                                    {{-- Debug info: {{ 'ID:' . $material->testing_item->id . ', Result:' . $material->testing_item->result }} --}}
                                                                @else
                                                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                                                                    {{-- Debug info: No testing item found for material {{ $material->material_id }} --}}
                                                                @endif
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                        </tbody>
                    </table>
                </div>
                                @else
                                    <p class="text-gray-500 italic">Không có vật tư lắp ráp cho thành phẩm này.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            
            @if($testing->test_type == 'finished_product')
            <!-- Chi tiết vật tư lắp ráp cho thành phẩm -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Chi tiết vật tư lắp ráp</h2>
                
                @if($testing->items && $testing->items->count() > 0)
                @php
                        // Lấy tất cả thành phẩm từ các items
                        $productItems = $testing->items->filter(function($item) {
                            return $item->item_type == 'product' && $item->product;
                        });
                        
                        $goodItems = $testing->items->filter(function($item) {
                            return $item->item_type == 'finished_product' && $item->good;
                        });
                @endphp
                
                    @if($productItems->count() > 0 || $goodItems->count() > 0)
                        <!-- Hiển thị từng thành phẩm và vật tư của nó -->
                        @foreach($productItems as $productItem)
                            <div class="mb-8">
                                <div class="mb-4 pb-2 border-b border-gray-200">
                        <p class="text-sm text-gray-500 font-medium mb-1">Thành phẩm</p>
                                    <p class="text-base text-gray-800 font-semibold">{{ $productItem->product->code }} - {{ $productItem->product->name }} ({{ $productItem->quantity }})</p>
                                    @if($productItem->serial_number)
                                        <p class="text-sm text-gray-600 mt-1">Serial: {{ $productItem->serial_number }}</p>
                                    @endif
                    </div>
                    
                                @if(isset($productItem->product->materials) && $productItem->product->materials->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã vật tư</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                                @forelse($productItem->product->materials as $index => $material)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $material->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->unit }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->pivot->quantity ?? 1 }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-sm text-center text-gray-500">Không có thông tin vật tư lắp ráp</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <p class="text-gray-500 text-center">Không có thông tin về vật tư lắp ráp cho thành phẩm này</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        
                        @foreach($goodItems as $goodItem)
                            <div class="mb-8">
                                <div class="mb-4 pb-2 border-b border-gray-200">
                                    <p class="text-sm text-gray-500 font-medium mb-1">Hàng hóa</p>
                                    <p class="text-base text-gray-800 font-semibold">{{ $goodItem->good->code }} - {{ $goodItem->good->name }} ({{ $goodItem->quantity }})</p>
                                    @if($goodItem->serial_number)
                                        <p class="text-sm text-gray-600 mt-1">Serial: {{ $goodItem->serial_number }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <p class="text-gray-500 text-center">Không tìm thấy thông tin thành phẩm</p>
                        </div>
                    @endif
                @else
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <p class="text-gray-500 text-center">Không tìm thấy thông tin thành phẩm</p>
                    </div>
                @endif
            </div>
            @endif
            
            @if($testing->assembly)
            <!-- Thông tin phiếu lắp ráp liên quan -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-cogs text-blue-500 mr-2"></i>
                    Thông tin lắp ráp liên quan
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Mã phiếu lắp ráp</p>
                            <p class="text-base text-gray-800 font-semibold">
                                <a href="{{ route('assemblies.show', $testing->assembly->id) }}" class="text-blue-600 hover:underline">
                                    {{ $testing->assembly->code }}
                                </a>
                            </p>
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
            
            <!-- Chi tiết kết quả kiểm thử -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Chi tiết kết quả kiểm thử</h2>
                
                <div class="mb-6">
                    <h3 class="font-medium text-gray-800 mb-3">Kết quả tổng thể</h3>
                    @php
                        // Xác định items cần tính toán dựa vào loại kiểm thử
                        $itemsToCount = collect();
                        $itemLabel = '';
                        
                        switch($testing->test_type) {
                            case 'finished_product':
                                // Kiểm thử thành phẩm: chỉ tính các items là thành phẩm (product)
                                $itemsToCount = $testing->items->where('item_type', 'product');
                                $itemLabel = 'thành phẩm';
                                break;
                                
                            case 'material':
                                // Kiểm thử vật tư: tính tất cả các items
                                $itemsToCount = $testing->items;
                                $itemLabel = 'vật tư';
                                break;
                                
                            default:
                                // Các loại kiểm thử khác: tính tất cả các items
                                $itemsToCount = $testing->items;
                                $itemLabel = 'thiết bị';
                                break;
                        }
                        
                        // Tính toán số lượng và tỷ lệ
                        $passItems = $itemsToCount->where('result', 'pass')->count();
                        $failItems = $itemsToCount->where('result', 'fail')->count();
                        $totalItems = $passItems + $failItems;
                        $itemPassRate = ($totalItems > 0) ? round(($passItems / $totalItems) * 100) : 0;
                        $itemFailRate = ($totalItems > 0) ? (100 - $itemPassRate) : 0;
                    @endphp
                    
                    {{-- Hiển thị kết quả --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                            <h4 class="font-medium text-green-800 mb-2">Số lượng {{ $itemLabel }} Đạt: {{ $passItems }}</h4>
                            <p class="text-green-700">{{ $itemPassRate }}% của tổng số {{ $itemLabel }} kiểm thử</p>
                    </div>
                    
                    <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                            <h4 class="font-medium text-red-800 mb-2">Số lượng {{ $itemLabel }} Không Đạt: {{ $failItems }}</h4>
                            <p class="text-red-700">{{ $itemFailRate }}% của tổng số {{ $itemLabel }} kiểm thử</p>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-6">
                        <h4 class="font-medium text-blue-800 mb-2">Lưu ý về phân loại kho:</h4>
                        <p class="text-blue-700">Thiết bị được đánh giá "Đạt" sẽ được chuyển vào Kho thiết bị Đạt.</p>
                        <p class="text-blue-700">Thiết bị được đánh giá "Không đạt" sẽ được chuyển vào Kho thiết bị Không đạt.</p>
                    </div>
                </div>
                
                @if($testing->fail_reasons)
                <div class="mb-6">
                    <h3 class="font-medium text-gray-800 mb-2">Lý do không đạt:</h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $testing->fail_reasons }}</p>
                </div>
                @endif
                
                @if($testing->conclusion)
                <div class="mb-6">
                    <h3 class="font-medium text-gray-800 mb-2">Kết luận:</h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $testing->conclusion }}</p>
                </div>
                @endif
            
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <h3 class="font-medium text-gray-800 mb-4">Xác nhận và hoàn thành</h3>
                
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <p class="font-medium">Người tạo phiếu</p>
                            <p>{{ $testing->tester->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500 mt-2">{{ $testing->test_date ? $testing->test_date->format('d/m/Y') : '' }}</p>
                        </div>
                        
                        <div class="text-center">
                            <p class="font-medium">Người phụ trách</p>
                            <p>{{ $testing->assignedEmployee->name ?? 'N/A' }}</p>
                        </div>
                        
                        <div class="text-center">
                            <p class="font-medium">Người tiếp nhận kiểm thử</p>
                            <p>{{ $testing->receiverEmployee->name ?? 'N/A' }}</p>
                            @if($testing->received_at)
                            <p class="text-sm text-gray-500 mt-2">{{ $testing->received_at->format('d/m/Y') }}</p>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action buttons -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 no-print">
                <div class="flex flex-wrap gap-3">
                    @if($testing->status == 'pending')
                    <form action="{{ route('testing.approve', $testing->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center">
                            <i class="fas fa-check mr-2"></i> Duyệt phiếu
                        </button>
                    </form>
                    
                    <form action="{{ route('testing.reject', $testing->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center" onclick="return confirm('Bạn có chắc chắn muốn từ chối phiếu kiểm thử này?');">
                            <i class="fas fa-times mr-2"></i> Từ chối
                        </button>
                    </form>
                    @endif
                    
                    @if($testing->status == 'in_progress')
                    <form action="{{ route('testing.receive', $testing->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                            <i class="fas fa-clipboard-check mr-2"></i> Tiếp nhận
                        </button>
                    </form>
                    
                    <button onclick="openCompleteModal()" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center">
                        <i class="fas fa-flag-checkered mr-2"></i> Hoàn thành
                    </button>
                    @endif
                    
                    @if($testing->status == 'completed' && !$testing->is_inventory_updated)
                    <button onclick="openUpdateInventory()" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 flex items-center">
                        <i class="fas fa-warehouse mr-2"></i> Cập nhật về kho
                    </button>
                    @endif
                    
                    @if($testing->is_inventory_updated)
                    <div class="ml-3 px-4 py-2 bg-green-100 text-green-800 rounded-lg flex items-center">
                        <i class="fas fa-check-circle mr-2"></i> Đã cập nhật vào kho
                        <span class="ml-2">
                            (Kho đạt: {{ $testing->successWarehouse->name ?? 'N/A' }}, 
                            Kho không đạt: {{ $testing->failWarehouse->name ?? 'N/A' }})
                        </span>
                        <span class="ml-2 px-2 py-0.5 bg-green-200 text-green-800 rounded-full text-xs">
                            {{ $testing->items->where('result', 'pass')->count() }} đạt / 
                            {{ $testing->items->where('result', 'fail')->count() }} không đạt
                        </span>
                    </div>
                    @endif
                    
                    @if($testing->status != 'completed')
                    <form action="{{ route('testing.destroy', $testing->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu kiểm thử này?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center">
                            <i class="fas fa-trash mr-2"></i> Xóa phiếu
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- Complete Modal -->
    <div id="complete-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Hoàn thành kiểm thử</h3>
                    <button onclick="closeCompleteModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form action="{{ route('testing.complete', $testing->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <p class="text-gray-700">Bạn có chắc chắn muốn hoàn thành phiếu kiểm thử này?</p>
                        <p class="text-sm text-gray-600 mt-2">Hệ thống sẽ tự động tính toán kết quả dựa trên các hạng mục kiểm thử đã nhập.</p>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeCompleteModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Hủy</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Xác nhận</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
                
    <!-- Update Inventory Modal -->
    <div id="inventory-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Cập nhật về kho</h3>
                    <button onclick="closeInventoryModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form action="{{ route('testing.update-inventory', $testing->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="success_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Kho hàng đạt</label>
                        <select id="success_warehouse_id" name="success_warehouse_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                            <option value="">-- Chọn kho --</option>
                            @foreach(App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="fail_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Kho hàng không đạt</label>
                        <select id="fail_warehouse_id" name="fail_warehouse_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                            <option value="">-- Chọn kho --</option>
                            @foreach(App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
            </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeInventoryModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Hủy</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Xác nhận</button>
                    </div>
                </form>
                </div>
            </div>
    </div>

    <script>
        function openCompleteModal() {
            // Kiểm tra xem có thiết bị nào chưa có kết quả hay không
            const itemResults = document.querySelectorAll('.testing-item-result');
            let pendingItemCount = 0;
            let hasPendingItems = false;
            
            if (itemResults && itemResults.length > 0) {
                itemResults.forEach(select => {
                    if (select.value === 'pending') {
                        hasPendingItems = true;
                        pendingItemCount++;
                    }
                });
            }
            
            // Kiểm tra xem có hạng mục nào chưa có kết quả không
            const pendingDetails = document.querySelectorAll('.testing-detail-result');
            let hasPendingDetails = false;
            let pendingDetailCount = 0;
            
            pendingDetails.forEach(select => {
                if (select.value === 'pending') {
                    hasPendingDetails = true;
                    pendingDetailCount++;
                }
            });
            
            // Nếu có thiết bị hoặc hạng mục chưa đánh giá, không cho phép hoàn thành
            if (hasPendingItems || hasPendingDetails) {
                let message = "Không thể hoàn thành phiếu kiểm thử:";
                
                if (hasPendingItems) {
                    message += `\n- Còn ${pendingItemCount} thiết bị chưa có kết quả đánh giá`;
                }
                
                if (hasPendingDetails) {
                    message += `\n- Còn ${pendingDetailCount} hạng mục kiểm thử chưa có kết quả`;
                }
                
                message += "\n\nVui lòng cập nhật đầy đủ kết quả trước khi hoàn thành.";
                
                alert(message);
                return;
            }
            
            // Không còn thiết bị và hạng mục nào pending, cho phép hoàn thành
            document.getElementById('complete-modal').classList.remove('hidden');
        }
        
        function closeCompleteModal() {
            document.getElementById('complete-modal').classList.add('hidden');
        }
        
        function openUpdateInventory() {
            document.getElementById('inventory-modal').classList.remove('hidden');
        }
        
        function closeInventoryModal() {
            document.getElementById('inventory-modal').classList.add('hidden');
        }
        
        function showResultDetails(id) {
            const element = document.getElementById(id);
            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
            } else {
                element.classList.add('hidden');
            }
        }

        // Print function
        function printPage() {
            window.print();
        }

        // Kiểm tra các trường kết quả kiểm thử
        function validateTestResults() {
            const materialSelects = document.querySelectorAll('select[name^="item_results"]');
            const materialResults = {};
            
            materialSelects.forEach(select => {
                const name = select.name;
                const value = select.value;
                const materialId = select.dataset.materialId;
                const materialName = select.dataset.materialName || 'Unknown';
                
                console.log(`Validating: ${name} = ${value} (${materialName})`);
                
                // Lưu kết quả để so sánh
                materialResults[materialId] = {
                    name: materialName,
                    value: value,
                    selectElement: select
                };
            });
            
            console.log('Tất cả kết quả kiểm thử:', materialResults);
            return materialResults;
        }

        // Thêm xử lý cho form lưu kết quả kiểm thử
        document.addEventListener('DOMContentLoaded', function() {
            const testItemForm = document.getElementById('test-item-form');
            if (testItemForm) {
                // Thêm sự kiện onChange cho các select kết quả
                const materialSelects = document.querySelectorAll('select[name^="item_results"]');
                materialSelects.forEach(select => {
                    select.addEventListener('change', function() {
                        console.log(`Kết quả thay đổi: ${select.name} = ${select.value} (${select.dataset.materialName || 'Unknown'})`);
                    });
                });
                
                testItemForm.addEventListener('submit', function(event) {
                    // Log ra để debug
                    console.log('Form kiểm thử đang được submit...');
                    
                    // Kiểm tra và hiển thị các kết quả kiểm thử
                    const materialResults = validateTestResults();
                    
                    // Thu thập tất cả dữ liệu form để debug
                    const formData = new FormData(testItemForm);
                    const formDataObj = {};
                    
                    formData.forEach((value, key) => {
                        formDataObj[key] = value;
                        // Đặc biệt log các trường kết quả kiểm thử
                        if (key.startsWith('item_results')) {
                            console.log(`Kết quả kiểm thử ${key}: ${value}`);
                        }
                    });
                    
                    console.log('Dữ liệu form kiểm thử:', formDataObj);
                    
                    // Kiểm tra các trường material_id có được đặt đúng không
                    const materialSelects = document.querySelectorAll('select[name^="item_results"]');
                    console.log(`Tìm thấy ${materialSelects.length} trường select kết quả kiểm thử`);
                    materialSelects.forEach(select => {
                        console.log(`Select name: ${select.name}, value: ${select.value}`);
                    });
                    
                    // Hiển thị thông báo
                    const submitButton = document.querySelector('.test-item-submit-button');
                    if (submitButton) {
                        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang lưu...';
                        submitButton.disabled = true;
                    }
                    
                    // Tiếp tục submit form
                    return true;
                });
            }
        });
    </script>
</body>
</html>  