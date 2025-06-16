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
        <div>
            <h1 class="text-2xl font-bold">PHIẾU KIỂM THỬ</h1>
            <p class="text-gray-600">Mã phiếu: {{ $testing->test_code }}</p>
        </div>
        <div class="text-right">
            <img src="{{ asset('images/logo.png') }}" alt="SGL Logo" class="h-16">
            <p class="text-sm text-gray-600">Công ty TNHH SGL</p>
            <p class="text-sm text-gray-600">Địa chỉ: 123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh</p>
        </div>
    </div>

    <!-- Thông tin cơ bản -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Thông tin cơ bản</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="mb-2"><span class="font-medium">Loại kiểm thử:</span> {{ $testing->test_type_text }}</p>
                <p class="mb-2"><span class="font-medium">Người tạo phiếu:</span> {{ $testing->tester->name ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-medium">Người phụ trách:</span> {{ $testing->assignedEmployee->name ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-medium">Người tiếp nhận kiểm thử:</span> {{ $testing->receiverEmployee->name ?? 'N/A' }}</p>
                <p class="mb-2"><span class="font-medium">Ngày kiểm thử:</span> {{ $testing->test_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="mb-2"><span class="font-medium">Trạng thái:</span> {{ $testing->status_text }}</p>
                <p class="mb-2"><span class="font-medium">Người duyệt:</span> {{ $testing->approver->name ?? 'Chưa được duyệt' }}</p>
                @if($testing->received_by)
                <p class="mb-2"><span class="font-medium">Ngày tiếp nhận:</span> {{ $testing->received_at ? $testing->received_at->format('d/m/Y') : 'N/A' }}</p>
                @endif
                @if($testing->approved_by)
                <p class="mb-2"><span class="font-medium">Ngày duyệt:</span> {{ $testing->approved_at ? $testing->approved_at->format('d/m/Y') : 'N/A' }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Danh sách vật tư/hàng hóa -->
    <div class="mb-8">
        @if($testing->test_type == 'material')
        <h2 class="text-xl font-semibold mb-4">Danh sách vật tư cần kiểm thử</h2>
        @else
        <h2 class="text-xl font-semibold mb-4">Chi tiết thiết bị kiểm thử</h2>
        @endif
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 p-2 text-left">STT</th>
                    <th class="border border-gray-300 p-2 text-left">Loại</th>
                    <th class="border border-gray-300 p-2 text-left">Tên thiết bị</th>
                    <th class="border border-gray-300 p-2 text-left">Serial</th>
                    <th class="border border-gray-300 p-2 text-left">Số lượng</th>
                    @if($testing->test_type == 'material')
                    <th class="border border-gray-300 p-2 text-left">Nhà cung cấp</th>
                    @endif
                    <th class="border border-gray-300 p-2 text-left">Kết quả</th>
                </tr>
            </thead>
            <tbody>
                @forelse($testing->items as $index => $item)
                <tr>
                    <td class="border border-gray-300 p-2">{{ $index + 1 }}</td>
                    <td class="border border-gray-300 p-2">
                        @if($item->item_type == 'material')
                            Vật tư
                        @elseif($item->item_type == 'product')
                            Thành phẩm
                        @elseif($item->item_type == 'finished_product')
                            Hàng hóa
                        @endif
                    </td>
                    <td class="border border-gray-300 p-2">
                        @if($item->item_type == 'material' && $item->material)
                            {{ $item->material->code }} - {{ $item->material->name }}
                        @elseif($item->item_type == 'product' && $item->product)
                            {{ $item->product->code }} - {{ $item->product->name }}
                        @elseif($item->item_type == 'finished_product' && $item->good)
                            {{ $item->good->code }} - {{ $item->good->name }}
                        @endif
                    </td>
                    <td class="border border-gray-300 p-2">{{ $item->serial_number ?: 'N/A' }}</td>
                    <td class="border border-gray-300 p-2">{{ $item->quantity }}</td>
                    @if($testing->test_type == 'material')
                    <td class="border border-gray-300 p-2">
                        {{ $item->supplier ? $item->supplier->name : 'N/A' }}
                    </td>
                    @endif
                    <td class="border border-gray-300 p-2">
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
                    <td colspan="{{ $testing->test_type == 'material' ? '7' : '6' }}" class="border border-gray-300 p-2 text-center">Không có dữ liệu</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($testing->test_type == 'finished_product')
    <!-- Chi tiết vật tư lắp ráp cho thành phẩm -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Chi tiết vật tư lắp ráp</h2>
        
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
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 p-2 text-left">STT</th>
                            <th class="border border-gray-300 p-2 text-left">Mã vật tư</th>
                            <th class="border border-gray-300 p-2 text-left">Tên vật tư</th>
                            <th class="border border-gray-300 p-2 text-left">Đơn vị</th>
                            <th class="border border-gray-300 p-2 text-left">Số lượng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->materials as $index => $material)
                        <tr>
                            <td class="border border-gray-300 p-2">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 p-2">{{ $material->code }}</td>
                            <td class="border border-gray-300 p-2">{{ $material->name }}</td>
                            <td class="border border-gray-300 p-2">{{ $material->unit }}</td>
                            <td class="border border-gray-300 p-2">{{ $material->pivot->quantity ?? 1 }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="border border-gray-300 p-2 text-center">Không có thông tin vật tư lắp ráp</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <div class="p-4 bg-gray-100 border border-gray-300 rounded">
                    <p class="text-center text-gray-500">Không có thông tin về vật tư lắp ráp cho thành phẩm này</p>
                </div>
            @endif
        @else
            <div class="p-4 bg-gray-100 border border-gray-300 rounded">
                <p class="text-center text-gray-500">Không tìm thấy thông tin thành phẩm</p>
            </div>
        @endif
    </div>
    @endif

    <!-- Hạng mục kiểm thử -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Hạng mục kiểm thử và kết quả</h2>
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 p-2 text-left">STT</th>
                    <th class="border border-gray-300 p-2 text-left">Hạng mục</th>
                    <th class="border border-gray-300 p-2 text-left">Kết quả</th>
                    <th class="border border-gray-300 p-2 text-left">Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                @forelse($testing->details as $index => $detail)
                <tr>
                    <td class="border border-gray-300 p-2">{{ $index + 1 }}</td>
                    <td class="border border-gray-300 p-2">{{ $detail->test_item_name }}</td>
                    <td class="border border-gray-300 p-2">
                        @if($detail->result == 'pass')
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Đạt</span>
                        @elseif($detail->result == 'fail')
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Không đạt</span>
                        @else
                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                        @endif
                    </td>
                    <td class="border border-gray-300 p-2">{{ $detail->notes ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="border border-gray-300 p-2 text-center">Không có dữ liệu</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Chi tiết kết quả kiểm thử -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Chi tiết kết quả kiểm thử</h2>
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="p-4 bg-green-50 border border-green-100 rounded">
                <h3 class="font-medium text-green-800 mb-2">Số lượng Đạt: {{ $testing->pass_quantity ?: 0 }}</h3>
                <p class="text-green-700">{{ $testing->pass_rate }}% của tổng số lượng kiểm thử</p>
            </div>
            
            <div class="p-4 bg-red-50 border border-red-100 rounded">
                <h3 class="font-medium text-red-800 mb-2">Số lượng Không Đạt: {{ $testing->fail_quantity ?: 0 }}</h3>
                <p class="text-red-700">{{ $testing->fail_quantity > 0 ? (100 - $testing->pass_rate) : 0 }}% của tổng số lượng kiểm thử</p>
            </div>
        </div>
        
        @if($testing->fail_reasons)
        <div class="mt-4">
            <p class="font-medium">Lý do không đạt:</p>
            <p class="p-2 border border-gray-300 rounded min-h-[50px]">{{ $testing->fail_reasons }}</p>
        </div>
        @endif
        
        @if($testing->conclusion)
        <div class="mt-4">
            <p class="font-medium">Kết luận:</p>
            <p class="p-2 border border-gray-300 rounded min-h-[50px]">{{ $testing->conclusion }}</p>
        </div>
        @endif
    </div>

    <!-- Chữ ký -->
    <div class="grid grid-cols-2 gap-4 mt-12">
        <div class="text-center">
            <p class="font-medium">Người tạo phiếu</p>
            <p class="mt-16">{{ $testing->tester->name ?? '' }}</p>
            <p class="text-sm text-gray-500 mt-2">{{ $testing->test_date ? $testing->test_date->format('d/m/Y') : '' }}</p>
        </div>
        <div class="text-center">
            <p class="font-medium">Người tiếp nhận</p>
            <p class="mt-16">{{ $testing->approver->name ?? '' }}</p>
            <p class="text-sm text-gray-500 mt-2">{{ $testing->approved_at ? $testing->approved_at->format('d/m/Y') : '' }}</p>
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