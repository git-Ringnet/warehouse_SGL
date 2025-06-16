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
                @if($testing->test_type == 'material')
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Danh sách vật tư cần kiểm thử</h2>
                @else
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Chi tiết thiết bị kiểm thử</h2>
                @endif
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên thiết bị</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Serial</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số lượng</th>
                                @if($testing->test_type == 'material')
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nhà cung cấp</th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kết quả</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($testing->items as $index => $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($item->item_type == 'material')
                                        Vật tư
                                    @elseif($item->item_type == 'product')
                                        Thành phẩm
                                    @elseif($item->item_type == 'finished_product')
                                        Hàng hóa
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    @if($item->item_type == 'material' && $item->material)
                                        {{ $item->material->code }} - {{ $item->material->name }}
                                    @elseif($item->item_type == 'product' && $item->product)
                                        {{ $item->product->code }} - {{ $item->product->name }}
                                    @elseif($item->item_type == 'finished_product' && $item->good)
                                        {{ $item->good->code }} - {{ $item->good->name }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->serial_number ?: 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->quantity }}</td>
                                @if($testing->test_type == 'material')
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $item->supplier ? $item->supplier->name : 'N/A' }}
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($item->result == 'pass')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                    @elseif($item->result == 'fail')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Không đạt</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $testing->test_type == 'material' ? '7' : '6' }}" class="px-6 py-4 text-sm text-center text-gray-500">Chưa có vật tư/hàng hóa nào được thêm</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($testing->test_type == 'finished_product')
            <!-- Chi tiết vật tư lắp ráp cho thành phẩm -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Chi tiết vật tư lắp ráp</h2>
                
                @php
                    // Lấy thông tin về thành phẩm đầu tiên (nếu có)
                    $product = null;
                    foreach($testing->items as $item) {
                        if($item->item_type == 'product' && $item->product) {
                            $product = $item->product;
                            break;
                        } elseif($item->item_type == 'finished_product' && $item->good) {
                            // Nếu là hàng hóa thì hiển thị thông tin của nó
                            $product = $item->good;
                            break;
                        }
                    }
                @endphp
                
                @if($product)
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 font-medium mb-1">Thành phẩm</p>
                        <p class="text-base text-gray-800 font-semibold">{{ $product->code }} - {{ $product->name }}</p>
                    </div>
                    
                    @if(isset($product->materials) && $product->materials->count() > 0)
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
                                    @forelse($product->materials as $index => $material)
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
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->assembly->product->name ?? 'N/A' }} ({{ $testing->assembly->quantity }})</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Serial sản phẩm</p>
                            <p class="text-base text-gray-800">{{ $testing->assembly->product_serials ?: 'N/A' }}</p>
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
            
            <!-- Hạng mục kiểm thử và kết quả -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Hạng mục kiểm thử và kết quả</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hạng mục</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kết quả</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($testing->details as $index => $detail)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $detail->test_item_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($detail->result == 'pass')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                                    @elseif($detail->result == 'fail')
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Không đạt</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $detail->notes ?? '' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-sm text-center text-gray-500">Chưa có hạng mục kiểm thử nào được thêm</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Chi tiết kết quả kiểm thử -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Chi tiết kết quả kiểm thử</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                        <h3 class="font-medium text-green-800 mb-2">Số lượng Đạt: {{ $testing->pass_quantity ?: 0 }}</h3>
                        <p class="text-green-700">{{ $testing->pass_rate }}% của tổng số lượng kiểm thử</p>
                    </div>
                    
                    <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                        <h3 class="font-medium text-red-800 mb-2">Số lượng Không Đạt: {{ $testing->fail_quantity ?: 0 }}</h3>
                        <p class="text-red-700">{{ $testing->fail_quantity > 0 ? (100 - $testing->pass_rate) : 0 }}% của tổng số lượng kiểm thử</p>
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
                        <label for="pass_quantity" class="block text-sm font-medium text-gray-700 mb-1">Số lượng đạt</label>
                        <input type="number" id="pass_quantity" name="pass_quantity" min="0" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                        </div>
                    
                    <div class="mb-4">
                        <label for="fail_quantity" class="block text-sm font-medium text-gray-700 mb-1">Số lượng không đạt</label>
                        <input type="number" id="fail_quantity" name="fail_quantity" min="0" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                        </div>
                    
                    <div class="mb-4">
                        <label for="conclusion" class="block text-sm font-medium text-gray-700 mb-1">Kết luận</label>
                        <textarea id="conclusion" name="conclusion" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required></textarea>
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
    </script>
</body>
</html> 