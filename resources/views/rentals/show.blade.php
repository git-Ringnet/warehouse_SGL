<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu cho thuê - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .sidebar {
            background: linear-gradient(180deg, #1a365d 0%, #0f2942 100%);
            transition: all 0.3s ease;
        }
        .content-area {
            margin-left: 256px;
            min-height: 100vh;
          
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
                width: 70px;
            }
            .content-area {
                margin-left: 0 !important;
            }
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background-color: white;
            border-radius: 0.5rem;
            max-width: 500px;
            width: 90%;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal {
            transform: scale(1);
        }
    </style>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu cho thuê</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $rental->rental_code }}
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('rentals.index') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ route('rentals.edit', $rental->id) }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <button id="deleteButton" 
                    data-id="{{ $rental->id }}" 
                    data-name="{{ $rental->rental_name }}"
                    class="h-10 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                </button>
            </div>
        </header>

        <main class="p-6">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif
            @if(session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Thông tin phiếu cho thuê -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Thông tin phiếu cho thuê</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
                            <div>
                                <p class="text-sm text-gray-500">Mã phiếu</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->rental_code }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tên phiếu</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->rental_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Khách hàng</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->customer->company_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Người đại diện</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->customer->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày cho thuê</p>
                                <p class="text-base text-gray-800 font-medium">{{ \Carbon\Carbon::parse($rental->rental_date)->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày hẹn trả</p>
                                <p class="text-base text-gray-800 font-medium">{{ \Carbon\Carbon::parse($rental->due_date)->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email liên hệ</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->customer->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Số điện thoại</p>
                                <p class="text-base text-gray-800 font-medium">{{ $rental->customer->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Ghi chú</p>
                            <p class="text-base text-gray-800 mt-1">
                                {{ $rental->notes ?? 'Không có ghi chú' }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Thiết bị hàng hóa theo hợp đồng -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Thiết bị hàng hóa theo hợp đồng</h2>
                            <button id="refresh-contract-items" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-sm transition-colors flex items-center">
                                <i class="fas fa-sync-alt mr-1"></i> Làm mới
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">STT</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Mã thiết bị</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tên thiết bị</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Serial</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Phiếu xuất</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Trạng thái</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thông tin chi tiết</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        use App\Models\DispatchReturn;
                                        use App\Models\DispatchReplacement;
                                    @endphp
                                    @forelse($contractItems as $index => $itemData)
                                        @php
                                            $item = $itemData['dispatch_item'];
                                            $dispatch = $itemData['dispatch'];
                                            $serialIndex = $itemData['serial_index'];
                                            $serialNumber = $itemData['serial_number'];
                                            
                                            // Kiểm tra trạng thái ở cấp serial cụ thể - chỉ xem xét records từ cùng rental
                                            $isReplaced = \App\Models\DispatchReplacement::where('original_serial', $serialNumber)
                                                ->whereHas('originalDispatchItem.dispatch', function($q) use ($rental) {
                                                    $q->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                                      ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                                                })->exists();
                                            $isUsed = \App\Models\DispatchReplacement::where('replacement_serial', $serialNumber)
                                                ->whereHas('replacementDispatchItem.dispatch', function($q) use ($rental) {
                                                    $q->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                                      ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                                                })->exists();
                                            $isReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                                                ->where('serial_number', $serialNumber)
                                                ->whereHas('dispatchItem.dispatch', function($q) use ($rental) {
                                                    $q->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                                      ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                                                })->exists();
                                            
                                            // Serial được sử dụng để thay thế cũng phải hiển thị "Đã thay thế"
                                            $isReplacementSerial = \App\Models\DispatchReplacement::where('replacement_serial', $serialNumber)
                                                ->whereHas('replacementDispatchItem.dispatch', function($q) use ($rental) {
                                                    $q->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                                      ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                                                })->exists();
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-4 border-b">{{ $index + 1 }}</td>
                                            <td class="py-2 px-4 border-b">
                                                @if($item->item_type == 'material' && $item->material)
                                                    {{ $item->material->code }}
                                                @elseif($item->item_type == 'product' && $item->product)
                                                    {{ $item->product->code }}
                                                @elseif($item->item_type == 'good' && $item->good)
                                                    {{ $item->good->code }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                @if($item->item_type == 'material' && $item->material)
                                                    {{ $item->material->name }}
                                                @elseif($item->item_type == 'product' && $item->product)
                                                    {{ $item->product->name }}
                                                @elseif($item->item_type == 'good' && $item->good)
                                                    {{ $item->good->name }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                @if($serialNumber)
                                                    {{ $serialNumber }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                <span class="text-sm text-gray-600">{{ $dispatch->dispatch_code }}</span>
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                @if($isReturned)
                                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs">Đã thu hồi</span>
                                                @else
                                                    @if($isReplaced || $isReplacementSerial)
                                                        <button data-id="{{ $item->id }}" data-serial="{{ $serialNumber }}" class="history-btn px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs hover:bg-orange-200 flex items-center justify-center space-x-1">
                                                            <i class="fas fa-exchange-alt"></i>
                                                            <span>Đã thay thế</span>
                                                        </button>
                                                    @else
                                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Chưa thay thế</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                <a href="{{ route('inventory.dispatch.show', $item->dispatch_id) }}" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-info-circle mr-1"></i> Xem chi tiết
                                                </a>
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                @if(!$isReturned && !$isReplaced && !$isReplacementSerial)
                                                <div class="flex space-x-2">
                                                    <button type="button" data-id="{{ $item->id }}" data-serial="{{ $serialNumber }}" data-code="{{ $item->item_type == 'material' && $item->material ? $item->material->code : ($item->item_type == 'product' && $item->product ? $item->product->code : ($item->item_type == 'good' && $item->good ? $item->good->code : 'N/A')) }}" class="warranty-btn text-blue-500 hover:text-blue-700">
                                                        <i class="fas fa-tools mr-1"></i> Bảo hành/Thay thế
                                                    </button>
                                                </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="py-4 px-4 border-b text-center text-gray-500">
                                                Không có thiết bị nào theo hợp đồng
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Thiết bị dự phòng/bảo hành -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Thiết bị dự phòng/bảo hành</h2>
                            <button id="refresh-backup-items" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-sm transition-colors flex items-center">
                                <i class="fas fa-sync-alt mr-1"></i> Làm mới
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">STT</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Mã thiết bị</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tên thiết bị</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Serial</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Trạng thái</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thông tin chi tiết</th>
                                        <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Lọc ra các thiết bị chưa bị thu hồi
                                        $visibleBackupItems = $backupItems->filter(function($itemData) use ($rental) {
                                            $item = $itemData['dispatch_item'];
                                            $serialNumber = $itemData['serial_number'];
                                            
                                            // Kiểm tra serial cụ thể chưa được thu hồi - chỉ xem xét records từ cùng rental
                                            $isSerialReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                                                ->where('serial_number', $serialNumber)
                                                ->whereHas('dispatchItem.dispatch', function($q) use ($rental) {
                                                    $q->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                                      ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                                                })->exists();
                                            
                                            return !$isSerialReturned;
                                        });
                                        
                                        // Lấy các item từ phiếu bảo hành
                                        $warrantyItems = \App\Models\Warranty::whereHas('dispatch', function($query) use ($rental) {
                                            $query->where('dispatch_type', 'rental')
                                                ->where(function($q) use ($rental) {
                                                    $q->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                                        ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                                                });
                                        })->get();
                                    @endphp
                                    
                                    @forelse($visibleBackupItems as $index => $itemData)
                                        @php
                                            $item = $itemData['dispatch_item'];
                                            $dispatch = $itemData['dispatch'];
                                            $serialIndex = $itemData['serial_index'];
                                            $serialNumber = $itemData['serial_number'];
                                            
                                                                                // Kiểm tra trạng thái ở cấp serial cụ thể - chỉ xem xét records từ cùng rental
                                    $isUsed = \App\Models\DispatchReplacement::where('replacement_serial', $serialNumber)
                                        ->whereHas('replacementDispatchItem.dispatch', function($q) use ($rental) {
                                            $q->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                              ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                                        })->exists();
                                    $isOriginalReplaced = \App\Models\DispatchReplacement::where('original_serial', $serialNumber)
                                        ->whereHas('originalDispatchItem.dispatch', function($q) use ($rental) {
                                            $q->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                              ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                                        })->exists();
                                    $isReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                                        ->where('serial_number', $serialNumber)
                                        ->whereHas('dispatchItem.dispatch', function($q) use ($rental) {
                                            $q->where('dispatch_note', 'LIKE', "%{$rental->rental_code}%")
                                              ->orWhere('project_receiver', 'LIKE', "%{$rental->rental_code}%");
                                        })->exists();
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-4 border-b">{{ $index + 1 }}</td>
                                            <td class="py-2 px-4 border-b">
                                                @if($item->item_type == 'material' && $item->material)
                                                    {{ $item->material->code }}
                                                @elseif($item->item_type == 'product' && $item->product)
                                                    {{ $item->product->code }}
                                                @elseif($item->item_type == 'good' && $item->good)
                                                    {{ $item->good->code }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                @if($item->item_type == 'material' && $item->material)
                                                    {{ $item->material->name }}
                                                @elseif($item->item_type == 'product' && $item->product)
                                                    {{ $item->product->name }}
                                                @elseif($item->item_type == 'good' && $item->good)
                                                    {{ $item->good->name }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                @if($serialNumber)
                                                    {{ $serialNumber }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                @if($isReturned)
                                                    <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs">Đã thu hồi</span>
                                                @else
                                                    @if($isUsed || $isOriginalReplaced)
                                                        <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">Đã sử dụng</span>
                                                    @else
                                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Chưa sử dụng</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                <a href="{{ route('inventory.dispatch.show', $item->dispatch_id) }}" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-info-circle mr-1"></i> Xem chi tiết
                                                </a>
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                @if(!$isReturned)
                                                @php
                                                    $itemCode = '';
                                                    if ($item->item_type == 'material' && $item->material) {
                                                        $itemCode = $item->material->code;
                                                    } elseif ($item->item_type == 'product' && $item->product) {
                                                        $itemCode = $item->product->code;
                                                    } elseif ($item->item_type == 'good' && $item->good) {
                                                        $itemCode = $item->good->code;
                                                    } else {
                                                        $itemCode = 'N/A';
                                                    }
                                                @endphp
                                                <button type="button" data-id="{{ $item->id }}" data-serial="{{ $serialNumber }}" data-code="{{ $itemCode }}" class="return-btn text-red-500 hover:text-red-700">
                                                    <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-4 px-4 border-b text-center text-gray-500">
                                                Không có thiết bị dự phòng/bảo hành
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Lịch sử gia hạn -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Lịch sử gia hạn</h2>
                            <button onclick="openExtendModal()" class="text-blue-500 hover:text-blue-600 text-sm font-medium">
                                <i class="fas fa-clock mr-1"></i> Gia hạn phiếu thuê
                            </button>
                        </div>
                        
                        @if(strpos($rental->notes ?? '', 'Gia hạn') !== false)
                            <div class="space-y-3">
                                @php
                                    $notes = explode("\n", $rental->notes);
                                    $extensionNotes = array_filter($notes, function($note) {
                                        return strpos($note, 'Gia hạn') !== false;
                                    });
                                @endphp
                                
                                @foreach($extensionNotes as $note)
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <p class="text-sm text-gray-800">{{ $note }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">Chưa có lịch sử gia hạn</p>
                        @endif
                    </div>
                </div>
                
                <!-- Sidebar thông tin -->
                <div class="lg:col-span-1">
                    <!-- Thông tin trạng thái -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Tình trạng phiếu thuê</h2>
                        
                        @php
                            $daysRemaining = $rental->daysRemaining();
                        @endphp
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Thời gian còn lại</p>
                                @if($rental->isOverdue())
                                    <p class="text-xl font-semibold text-red-600">Quá hạn {{ abs(intval($daysRemaining)) }} ngày</p>
                                @else
                                    <p class="text-xl font-semibold text-gray-800">{{ intval($daysRemaining) }} ngày</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày hẹn trả</p>
                                <p class="text-xl font-semibold {{ $rental->isOverdue() ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ \Carbon\Carbon::parse($rental->due_date)->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-500 mb-1">Nhân viên phụ trách</p>
                                <p class="text-base text-gray-900">{{ $rental->employee ? $rental->employee->name : 'Chưa phân công' }}</p>
                            </div>
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-500 mb-1">Ngày tạo</p>
                                <p class="text-base text-gray-900">{{ \Carbon\Carbon::parse($rental->created_at)->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="mt-2">
                                <button onclick="openExtendModal()" class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center justify-center transition-colors">
                                    <i class="fas fa-clock mr-2"></i> Gia hạn phiếu thuê
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Hành động -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">Hành động</h2>
                        
                        <div class="space-y-3">
                            <button onclick="confirmDelete()" class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-trash mr-2"></i> Xóa phiếu
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal gia hạn phiếu thuê -->
    <div id="extendModal" class="modal-overlay">
        <div class="modal p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Gia hạn phiếu thuê</h3>
            
            <form action="{{ route('rentals.extend', $rental->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="extend_days" class="block text-sm font-medium text-gray-700 mb-1">Số ngày gia hạn</label>
                    <input type="number" name="extend_days" id="extend_days" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="7">
                </div>
                <div class="mb-4">
                    <label for="extend_notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                    <textarea name="extend_notes" id="extend_notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeExtendModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        <i class="fas fa-check mr-2"></i> Xác nhận gia hạn
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Chuyển sang dự phòng -->
    <div id="convert-modal" class="modal-overlay">
        <div class="modal max-w-md w-full">
            <div class="p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Chuyển thiết bị sang dự phòng</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('convert-modal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="convert-form" action="#" method="POST">
                    @csrf
                    <input type="hidden" id="convert-equipment-id" name="equipment_id">
                    
                    <p class="mb-4">Bạn muốn chuyển thiết bị <span id="convert-equipment-code" class="font-semibold"></span> sang trạng thái dự phòng?</p>
                    
                    <div class="mb-4">
                        <label for="convert-reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do</label>
                        <textarea id="convert-reason" name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-5">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300" onclick="closeModal('convert-modal')">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Xác nhận
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Bảo hành/Thay thế -->
    <div id="warranty-modal" class="modal-overlay">
        <div class="modal max-w-lg w-full">
            <div class="p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Bảo hành/Thay thế thiết bị</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('warranty-modal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="warranty-form" action="{{ route('equipment.replace') }}" method="POST">
                    @csrf
                    <input type="hidden" id="warranty-equipment-id" name="equipment_id">
                    <input type="hidden" id="warranty-equipment-serial" name="equipment_serial">
                    <p class="mb-4">Bạn đang thực hiện bảo hành/thay thế cho thiết bị <span id="warranty-equipment-code" class="font-semibold"></span></p>
                    <div class="mb-4">
                        <label for="replacement_device_id" class="block text-sm font-medium text-gray-700 mb-1">Chọn thiết bị dự phòng thay thế</label>
                        <select id="replacement_device_id" name="replacement_device_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Chọn thiết bị --</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do bảo hành/thay thế</label>
                        <textarea id="reason" name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 mt-5">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300" onclick="closeModal('warranty-modal')">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Xác nhận
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Lịch sử thay đổi -->
    <div id="history-modal" class="modal-overlay">
        <div class="modal max-w-lg w-full">
            <div class="p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Lịch sử thay đổi thiết bị</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('history-modal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="history-content" class="mb-4">
                    <div class="flex justify-center items-center py-8">
                        <i class="fas fa-spinner fa-spin text-blue-500 mr-2"></i> Đang tải dữ liệu...
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300" onclick="closeModal('history-modal')">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thu hồi thiết bị -->
    <div id="return-modal" class="modal-overlay">
        <div class="modal max-w-lg w-full">
            <div class="p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Thu hồi thiết bị</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('return-modal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="return-form" action="{{ route('equipment.return') }}" method="POST">
                    @csrf
                    <input type="hidden" id="return-equipment-id" name="equipment_id">
                    <input type="hidden" id="return-equipment-serial" name="equipment_serial">
                    <input type="hidden" name="rental_id" value="{{ $rental->id }}">
                    
                    <p class="mb-4">Bạn đang thực hiện thu hồi thiết bị <span id="return-equipment-code" class="font-semibold"></span></p>
                    
                    <div class="mb-4">
                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Chọn kho</label>
                        <select id="warehouse_id" name="warehouse_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Chọn kho --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Lý do thu hồi</label>
                        <textarea id="reason" name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-5">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300" onclick="closeModal('return-modal')">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Xác nhận
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Document ready event
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
            
            // Attach click event to delete button
            document.getElementById('deleteButton').addEventListener('click', function() {
                // Get the rental name from a data attribute to avoid JS issues
                const rentalName = this.getAttribute('data-name');
                const rentalId = this.getAttribute('data-id');
                openDeleteModal(rentalId, rentalName);
            });

            // Sử dụng event delegation cho nút thu hồi
            document.addEventListener('click', function(e) {
                if (e.target.closest('.return-btn')) {
                    const button = e.target.closest('.return-btn');
                    const equipmentId = button.getAttribute('data-id');
                    const equipmentCode = button.getAttribute('data-code');
                    const equipmentSerial = button.getAttribute('data-serial');
                    openModal('return-modal');
                    document.getElementById('return-equipment-id').value = equipmentId;
                    document.getElementById('return-equipment-serial').value = equipmentSerial;
                    document.getElementById('return-equipment-code').textContent = equipmentCode;
                }
            });

            // Attach event listeners for modal buttons
            document.querySelectorAll('.history-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const equipmentId = this.getAttribute('data-id');
                    openModal('history-modal');
                    fetchEquipmentHistory(equipmentId);
                });
            });

            document.querySelectorAll('.warranty-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const equipmentId = this.getAttribute('data-id');
                    const equipmentCode = this.getAttribute('data-code');
                    const equipmentSerial = this.getAttribute('data-serial');
                    openModal('warranty-modal');
                    document.getElementById('warranty-equipment-id').value = equipmentId;
                    document.getElementById('warranty-equipment-code').textContent = equipmentCode;
                    document.getElementById('warranty-equipment-serial').value = equipmentSerial;
                    fetchBackupItems();
                });
            });

            document.getElementById('replacement_device_id').addEventListener('change', function() {
                const selectedValue = this.value;
                if (selectedValue && selectedValue.includes(':')) {
                    const [itemId, serialNumber] = selectedValue.split(':');
                    // Tự động điền serial vào hidden input
                    const replacementSerialInput = document.createElement('input');
                    replacementSerialInput.type = 'hidden';
                    replacementSerialInput.name = 'replacement_serial';
                    replacementSerialInput.value = serialNumber;
                    
                    // Xóa input cũ nếu có
                    const oldInput = document.querySelector('input[name="replacement_serial"]');
                    if (oldInput) oldInput.remove();
                    
                    // Thêm input mới vào form
                    document.getElementById('warranty-form').appendChild(replacementSerialInput);
                }
            });
        });

        // Override deleteCustomer function from delete-modal.js
        function deleteCustomer(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('rentals.index') }}/" + id;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = "{{ csrf_token() }}";
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
        
        // Mở modal gia hạn phiếu thuê
        function openExtendModal() {
            document.getElementById('extendModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        // Đóng modal gia hạn phiếu thuê
        function closeExtendModal() {
            document.getElementById('extendModal').classList.remove('show');
            document.body.style.overflow = '';
        }
        
        // Xác nhận xóa phiếu
        function confirmDelete() {
            if (confirm('Bạn có chắc chắn muốn xóa phiếu cho thuê này không?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('rentals.destroy', $rental->id) }}";
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = "{{ csrf_token() }}";
                
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Mở modal
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }
        
        // Đóng modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Lấy danh sách thiết bị dự phòng
        function fetchBackupItems() {
            const replacementDeviceSelect = document.getElementById('replacement_device_id');
            const currentEquipmentId = document.getElementById('warranty-equipment-id').value;
            const currentEquipmentCode = document.getElementById('warranty-equipment-code').textContent;
            
            replacementDeviceSelect.innerHTML = '<option value="">-- Đang tải dữ liệu... --</option>';
            
            fetch(`/equipment-service/backup-items/rental/{{ $rental->id }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const backupItems = data.backupItems;
                        
                        // Xóa tất cả options hiện tại
                        replacementDeviceSelect.innerHTML = '<option value="">-- Chọn thiết bị --</option>';
                        
                        // Lọc thiết bị dự phòng theo cùng mã và trạng thái "Chưa sử dụng"
                        const filteredItems = backupItems.filter(item => {
                            let itemCode = '';
                            if (item.item_type === 'material' && item.material) {
                                itemCode = item.material.code;
                            } else if (item.item_type === 'product' && item.product) {
                                itemCode = item.product.code;
                            } else if (item.item_type === 'good' && item.good) {
                                itemCode = item.good.code;
                            }
                            
                            // Loại bỏ khoảng trắng và so sánh
                            const cleanItemCode = itemCode ? itemCode.trim() : '';
                            const cleanCurrentCode = currentEquipmentCode ? currentEquipmentCode.trim() : '';
                            
                            // Kiểm tra cùng mã thiết bị
                            return cleanItemCode === cleanCurrentCode;
                        });
                        
                        // Tạo danh sách serial riêng biệt thay vì gộp theo item
                        const serialOptions = [];
                        
                        filteredItems.forEach(item => {
                            let itemName = 'Không xác định';
                            let itemCode = 'N/A';
                            let serialNumbers = [];
                            
                            if (item.item_type === 'material' && item.material) {
                                itemName = item.material.name;
                                itemCode = item.material.code;
                            } else if (item.item_type === 'product' && item.product) {
                                itemName = item.product.name;
                                itemCode = item.product.code;
                            } else if (item.item_type === 'good' && item.good) {
                                itemName = item.good.name;
                                itemCode = item.good.code;
                            }
                            
                            // Lấy serial numbers
                            if (item.serial_numbers && item.serial_numbers.length > 0) {
                                serialNumbers = item.serial_numbers;
                            }
                            
                            // Tạo option riêng cho từng serial và kiểm tra trạng thái từng serial
                            serialNumbers.forEach(serialNumber => {
                                // Kiểm tra serial này có được sử dụng chưa
                                const isSerialUsed = item.replacement_serials && item.replacement_serials.includes(serialNumber);
                                
                                // Chỉ hiển thị serial chưa được sử dụng
                                if (!isSerialUsed) {
                                    serialOptions.push({
                                        itemId: item.id,
                                        serialNumber: serialNumber,
                                        itemCode: itemCode,
                                        itemName: itemName,
                                        displayText: `${itemCode} - ${itemName} - Serial ${serialNumber}`
                                    });
                                }
                            });
                        });
                        
                        // Thêm các options mới với format "Mã - Tên - Serial X"
                        serialOptions.forEach(option => {
                            const optionElement = document.createElement('option');
                            optionElement.value = `${option.itemId}:${option.serialNumber}`;
                            optionElement.textContent = option.displayText;
                            optionElement.setAttribute('data-item-id', option.itemId);
                            optionElement.setAttribute('data-serial', option.serialNumber);
                            replacementDeviceSelect.appendChild(optionElement);
                        });
                        
                        // Thêm option "Không có thiết bị dự phòng nào" nếu không có thiết bị phù hợp
                        if (serialOptions.length === 0) {
                            const option = document.createElement('option');
                            option.value = "";
                            option.textContent = "Không có thiết bị dự phòng nào";
                            replacementDeviceSelect.appendChild(option);
                        }
                    } else {
                        replacementDeviceSelect.innerHTML = '<option value="">-- Lỗi khi tải dữ liệu --</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching backup items:', error);
                    replacementDeviceSelect.innerHTML = '<option value="">-- Lỗi khi tải dữ liệu --</option>';
                });
        }

        // Lấy lịch sử thay đổi thiết bị
        function fetchEquipmentHistory(equipmentId) {
            const historyContent = document.getElementById('history-content');
            historyContent.innerHTML = `
                <div class="flex justify-center items-center py-8">
                    <i class="fas fa-spinner fa-spin text-blue-500 mr-2"></i> Đang tải dữ liệu...
                </div>
            `;
            
            // Lấy rental ID từ trang hiện tại
            const rentalId = window.rentalId;
            
            fetch(`/equipment-service/history/${equipmentId}?rental_id=${rentalId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const dispatchItem = data.dispatchItem;
                        const replacements = data.replacements;
                        
                        // Tạo nội dung hiển thị thông tin thiết bị gốc
                        let itemName = '';
                        let itemCode = '';
                        let serialNumbers = '';
                        
                        if (dispatchItem.item_type === 'material' && dispatchItem.material) {
                            itemName = dispatchItem.material.name;
                            itemCode = dispatchItem.material.code;
                        } else if (dispatchItem.item_type === 'product' && dispatchItem.product) {
                            itemName = dispatchItem.product.name;
                            itemCode = dispatchItem.product.code;
                        } else if (dispatchItem.item_type === 'good' && dispatchItem.good) {
                            itemName = dispatchItem.good.name;
                            itemCode = dispatchItem.good.code;
                        }
                        
                        if (dispatchItem.serial_numbers && dispatchItem.serial_numbers.length > 0) {
                            serialNumbers = dispatchItem.serial_numbers.join(', ');
                        }
                        
                        // Hiển thị thông tin thiết bị gốc
                        let html = `
                            <div class="mb-4">
                                <h4 class="text-md font-medium text-gray-800 mb-2">Thông tin thiết bị</h4>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p><span class="font-medium">Mã thiết bị:</span> ${itemCode || 'Không có mã'}</p>
                                    <p><span class="font-medium">Tên thiết bị:</span> ${itemName || 'Không có tên'}</p>
                                    <p><span class="font-medium">Serial:</span> ${serialNumbers || 'Không có serial'}</p>
                                    <p><span class="font-medium">Phiếu xuất:</span> ${dispatchItem.dispatch ? dispatchItem.dispatch.dispatch_code : 'Không có phiếu xuất'}</p>
                                </div>
                            </div>
                        `;
                        
                        // Hiển thị lịch sử thay thế
                        if (replacements && replacements.length > 0) {
                            html += `
                                <div class="mb-4">
                                    <h4 class="text-md font-medium text-gray-800 mb-2">Lịch sử thay đổi</h4>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <table class="min-w-full">
                                            <thead>
                                                <tr class="bg-gray-100">
                                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Ngày thay đổi</th>
                                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Người thực hiện</th>
                                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Serial thiết bị thay thế</th>
                                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Lý do</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                            `;
                            
                            replacements.forEach(replacement => {
                                const date = new Date(replacement.replacement_date);
                                const formattedDate = date.toLocaleString('vi-VN', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric'
                                }).replace(',', '');
                                
                                // Lấy thông tin nhân viên phụ trách từ rental
                                let employeeName = 'Không xác định';
                                if (replacement.employee_name) {
                                    employeeName = replacement.employee_name;
                                } else if (replacement.user && replacement.user.role !== 'customer') {
                                    employeeName = replacement.user.name;
                                }
                                
                                // Lấy thông tin serial của thiết bị thay thế
                                let replacementItem = replacement.replacement_dispatch_item;
                                let replacementSerial = 'Không có serial';
                                
                                if (replacementItem && replacementItem.serial_numbers && replacementItem.serial_numbers.length > 0) {
                                    replacementSerial = replacementItem.serial_numbers.join(', ');
                                }
                                
                                // Hiển thị thông tin serial cụ thể được thay thế (nếu có)
                                let originalSerial = 'Không có thông tin';
                                let replacedSerial = 'Không có thông tin';
                                
                                if (replacement.original_serial && replacement.replacement_serial) {
                                    originalSerial = replacement.original_serial;
                                    replacedSerial = replacement.replacement_serial;
                                }
                                
                                html += `
                                    <tr>
                                        <td class="py-2 px-3 border-t">${formattedDate}</td>
                                        <td class="py-2 px-3 border-t">${employeeName}</td>
                                        <td class="py-2 px-3 border-t">
                                            <div class="text-xs">
                                                <div><strong>Serial gốc:</strong> ${originalSerial}</div>
                                                <div><strong>Serial thay thế:</strong> ${replacedSerial}</div>
                                            </div>
                                        </td>
                                        <td class="py-2 px-3 border-t">${replacement.reason || 'Không có thông tin'}</td>
                                    </tr>
                                `;
                            });
                            
                            html += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            `;
                        } else {
                            html += `
                                <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                                    <p class="text-yellow-700">Chưa có lịch sử thay đổi cho thiết bị này.</p>
                                </div>
                            `;
                        }
                        
                        historyContent.innerHTML = html;
                    } else {
                        historyContent.innerHTML = `
                            <div class="bg-red-50 p-4 rounded-lg">
                                <p class="text-red-700">Có lỗi xảy ra khi lấy lịch sử thay đổi thiết bị.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching equipment history:', error);
                    historyContent.innerHTML = `
                        <div class="bg-red-50 p-4 rounded-lg">
                            <p class="text-red-700">Có lỗi xảy ra khi lấy lịch sử thay đổi thiết bị.</p>
                        </div>
                    `;
                });
        }



        // Xử lý nút refresh cho thiết bị hợp đồng
        document.getElementById('refresh-contract-items')?.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Đang làm mới...';
            
            // Refresh trang để lấy dữ liệu mới nhất
            location.reload();
        });

        // Xử lý nút refresh cho thiết bị dự phòng
        document.getElementById('refresh-backup-items')?.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Đang làm mới...';
            
            // Refresh trang để lấy dữ liệu mới nhất
            location.reload();
        });

        // Auto-refresh khi có thay đổi từ dispatch edit
        // Kiểm tra nếu có tham số refresh trong URL
        if (window.location.search.includes('refresh=true')) {
            // Xóa tham số refresh khỏi URL
            const url = new URL(window.location);
            url.searchParams.delete('refresh');
            window.history.replaceState({}, '', url);
            
            // Hiển thị thông báo
            const alert = document.createElement('div');
            alert.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4';
            alert.innerHTML = `
                <strong class="font-bold">Thành công!</strong>
                <span class="block sm:inline">Dữ liệu đã được cập nhật. Serial numbers đã được đồng bộ.</span>
            `;
            
            // Thêm alert vào đầu trang
            const header = document.querySelector('header');
            header.parentNode.insertBefore(alert, header.nextSibling);
            
            // Tự động ẩn alert sau 5 giây
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
    </script>
</body>
</html>