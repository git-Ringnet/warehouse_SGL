<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In phiếu kiểm thử {{ $testing->test_code }} - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: white;
            color: #333;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body class="p-6">
    <!-- Print Header -->
    <div class="flex justify-between items-center mb-6 border-b border-gray-300 pb-4">
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

    <!-- Thông tin cơ bản -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 mb-6">
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

    <!-- Chi tiết kiểm thử -->
    @if($testing->test_type == 'material' || $testing->test_type == 'finished_product')
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Chi tiết kiểm thử</h2>

        <!-- Tổng hợp vật tư, hàng hóa hoặc thành phẩm đã thêm -->
        <div class="mb-6">
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
            <tbody>
                        @forelse($testing->items->filter(function($item) use ($testing) {
                            if ($testing->test_type == 'finished_product') {
                                return $item->item_type == 'product' || $item->item_type == 'finished_product';
                            }
                            return true;
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
                                    <span class="text-red-500">Không tìm thấy thông tin</span>
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
        @if($testing->status == 'completed')
        <div class="mb-6 border-t border-gray-200 pt-6">
            <h3 class="text-md font-medium text-gray-800 mb-3">Kết quả kiểm thử thiết bị</h3>
            <div class="space-y-6">
                @forelse($testing->items->filter(function($item) use ($testing) {
                    if ($testing->test_type == 'finished_product') {
                        return $item->item_type == 'product' || $item->item_type == 'finished_product';
                    }
                    return true;
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
                                <span class="text-red-500">Không tìm thấy thông tin</span>
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
                                <div class="w-full h-10 border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-700">
                                    {{ $item->pass_quantity ?? 0 }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng thiết bị không đạt</label>
                                <div class="w-full h-10 border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-700">
                                    {{ $item->fail_quantity ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hạng mục kiểm thử cho thiết bị này -->
                    @if($testing->test_type != 'finished_product' || $testing->details->count() > 0)
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-3">
                            <h5 class="font-medium text-gray-800">Hạng mục kiểm thử</h5>
                            @if($testing->test_type == 'finished_product')
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i> Hạng mục kiểm thử được map từ phiếu lắp ráp
                            </div>
                            @endif
                        </div>
                        
                        <div class="space-y-4">
                            @forelse($testing->details as $detailIndex => $detail)
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <div class="flex justify-between items-center mb-3">
                                        <h6 class="font-medium text-gray-700">{{ $detail->test_item_name }}</h6>
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
                                                        $serialLabel = chr(65 + $index);
                                                        $serialResults = [];
                                                        if ($item->serial_results) {
                                                            $serialResults = json_decode($item->serial_results, true);
                                                        }
                                                        $selectedValue = $serialResults[$serialLabel] ?? 'pending';
                                                    @endphp
                                                    <div>
                                                        <label class="block text-xs text-gray-600 mb-1">Serial {{ $serialLabel }} ({{ $serial }})</label>
                                                        <div class="w-full h-8 border border-gray-300 rounded px-2 text-sm bg-gray-100 text-gray-700 flex items-center">
                                                            @if($selectedValue == 'pass')
                                                                <span class="text-green-600">Đạt</span>
                                                            @elseif($selectedValue == 'fail')
                                                                <span class="text-red-600">Không đạt</span>
                                                            @else
                                                                <span class="text-gray-500">Chưa có</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs text-gray-600 mb-1">Serial A (không thể chỉnh sửa)</label>
                                                    <div class="w-full h-8 border border-gray-300 rounded px-2 text-sm bg-gray-100 text-gray-500 flex items-center">
                                                        Chưa có
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-600 mb-1">Serial B (không thể chỉnh sửa)</label>
                                                    <div class="w-full h-8 border border-gray-300 rounded px-2 text-sm bg-gray-100 text-gray-500 flex items-center">
                                                        Chưa có
                                                    </div>
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
                                                <div class="w-full h-8 border border-gray-300 rounded px-2 text-sm bg-gray-100 text-gray-700 flex items-center">
                                                    {{ $passQuantity }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Số lượng không Đạt</label>
                                                <div class="w-full h-8 border border-gray-300 rounded px-2 text-sm bg-gray-100 text-gray-700 flex items-center">
                                                    {{ $failQuantity }}
                                                </div>
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
                </div>
                @empty
                    <div class="text-center text-gray-500 py-4">
                        Chưa có kết quả kiểm thử
                    </div>
                @endforelse
            </div>
            </div>
        @endif
    </div>
    @endif

    @if($testing->assembly)
    <!-- Thông tin phiếu lắp ráp liên quan -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-cogs text-blue-500 mr-2"></i>
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

    <!-- Chi tiết kết quả kiểm thử -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Chi tiết kết quả kiểm thử</h2>
        
        <div class="mb-6">
            <h3 class="font-medium text-gray-800 mb-3">Kết quả tổng thể</h3>
            @php
                // Tính toán dựa trên kết quả tổng thể của từng item
                $totalPassQuantity = 0;
                $totalFailQuantity = 0;
                $totalQuantity = 0;
                
                // Lọc items dựa vào loại kiểm thử
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
                
                // Tính toán dựa trên pass_quantity và fail_quantity của từng item
                foreach($itemsToCount as $item) {
                    $passQuantity = $item->pass_quantity ?? 0;
                    $failQuantity = $item->fail_quantity ?? 0;
                    
                    $totalPassQuantity += $passQuantity;
                    $totalFailQuantity += $failQuantity;
                    $totalQuantity += ($passQuantity + $failQuantity);
                }
                
                $itemPassRate = ($totalQuantity > 0) ? round(($totalPassQuantity / $totalQuantity) * 100) : 0;
                $itemFailRate = ($totalQuantity > 0) ? round(($totalFailQuantity / $totalQuantity) * 100) : 0;
            @endphp
            
            {{-- Hiển thị kết quả --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                    <h4 class="font-medium text-green-800 mb-2">Số lượng {{ $itemLabel }} Đạt: {{ $totalPassQuantity }}</h4>
                    <p class="text-green-700">{{ $itemPassRate }}% của tổng số {{ $itemLabel }} kiểm thử</p>
                </div>
                
                <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                    <h4 class="font-medium text-red-800 mb-2">Số lượng {{ $itemLabel }} Không Đạt: {{ $totalFailQuantity }}</h4>
                    <p class="text-red-700">{{ $itemFailRate }}% của tổng số {{ $itemLabel }} kiểm thử</p>
                </div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-6">
                <h4 class="font-medium text-blue-800 mb-2">Lưu ý về phân loại kho:</h4>
                <p class="text-blue-700">Thiết bị được đánh giá "Đạt" sẽ được chuyển vào Kho thiết bị Đạt.</p>
                <p class="text-blue-700">Thiết bị được đánh giá "Không đạt" sẽ được chuyển vào Kho thiết bị Không đạt.</p>
            </div>
            
            @if($testing->is_inventory_updated)
            <div class="bg-green-50 p-4 rounded-lg border border-green-100 mb-6">
                <h4 class="font-medium text-green-800 mb-2">Thông tin kho:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        @if($testing->test_type == 'finished_product')
                            @php
                                $assemblyPurpose = $testing->assembly ? $testing->assembly->purpose : null;
                                $projectName = $testing->assembly ? $testing->assembly->project_name : 'Dự án';
                            @endphp
                            @if($assemblyPurpose == 'project')
                                <p class="text-sm font-medium text-green-700">Dự án cho Thành phẩm đạt:</p>
                                <p class="text-green-600">{{ $projectName }}</p>
                            @else
                                <p class="text-sm font-medium text-green-700">Kho lưu Thành phẩm đạt:</p>
                                <p class="text-green-600">{{ $testing->successWarehouse->name ?? 'Chưa có' }}</p>
                            @endif
                        @else
                            <p class="text-sm font-medium text-green-700">Kho đạt / Dự án xuất đi:</p>
                            <p class="text-green-600">{{ $testing->successWarehouse->name ?? 'Chưa có' }}</p>
                        @endif
                    </div>
                    <div>
                        @if($testing->test_type == 'finished_product')
                            <p class="text-sm font-medium text-red-700">Kho lưu Module Vật tư lắp ráp không đạt:</p>
                        @else
                            <p class="text-sm font-medium text-red-700">Kho chưa đạt:</p>
                        @endif
                        <p class="text-red-600">{{ $testing->failWarehouse->name ?? 'Chưa có' }}</p>
                    </div>
                </div>
            </div>
            @endif
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
                <p class="font-medium">Người tiếp nhận kiểm thử</p>
                <p>{{ $testing->receiverEmployee->name ?? 'N/A' }}</p>
                @if($testing->received_at)
                <p class="text-sm text-gray-500 mt-2">{{ $testing->received_at->format('d/m/Y') }}</p>
                @endif
            </div>
            </div>
        </div>
    </div>

    <!-- Print buttons -->
    <div class="mt-8 text-center no-print">
        <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg mr-2">
            <i class="fas fa-print mr-2"></i> In phiếu
        </button>
        <a href="{{ route('testing.show', $testing->id) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i> Quay lại
        </a>
    </div>

    <script>
        window.onload = function() {
            // Automatically open print dialog when the page loads
            // setTimeout(function() { window.print(); }, 500);
        };
    </script>
</body>
</html> 