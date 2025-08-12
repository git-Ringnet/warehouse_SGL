<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết dự án - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết dự án</h1>
            <div class="flex space-x-2">
                <a href="{{ route('projects.edit', $project->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
                <button id="deleteButton" 
                    data-id="{{ $project->id }}" 
                    data-name="{{ $project->project_name }}"
                    class="bg-red-500 hover:bg-red-600 text-white h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                </button>
                <a href="{{ route('projects.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>
        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif
        <main class="p-6">
                <!-- Thông tin dự án -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-project-diagram mr-2 text-blue-500"></i> Thông tin dự án
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Mã dự án</p>
                            <p class="text-base font-medium text-gray-900">{{ $project->project_code }}</p>
                            </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Tên dự án</p>
                            <p class="text-base text-gray-900">{{ $project->project_name }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Ngày bắt đầu</p>
                            <p class="text-base text-gray-900">{{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Thời gian bảo hành</p>
                            <p class="text-base text-gray-900">{{ $project->warranty_period }} tháng</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Thời gian bảo hành còn lại</p>
                            @if($project->has_valid_warranty)
                                @php
                                    $daysLeft = $project->remaining_warranty_days;
                                    $colorClass = 'text-green-600';
                                    $icon = 'check-circle';
                                    
                                    if ($daysLeft <= 7) {
                                        $colorClass = 'text-red-600';
                                        $icon = 'exclamation-circle';
                                    } elseif ($daysLeft <= 30) {
                                        $colorClass = 'text-orange-500';
                                        $icon = 'exclamation-triangle';
                                    } elseif ($daysLeft <= 90) {
                                        $colorClass = 'text-yellow-500';
                                        $icon = 'info-circle';
                                    }
                                @endphp
                                <p class="text-base font-medium flex items-center {{ $colorClass }}">
                                    <i class="fas fa-{{ $icon }} mr-1"></i>
                                    {{ $daysLeft }} ngày
                                </p>
                            @else
                                <p class="text-base text-red-600 font-medium flex items-center">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Đã hết hạn bảo hành
                                </p>
                            @endif
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Ngày kết thúc bảo hành</p>
                            @php
                                $daysLeft = $project->remaining_warranty_days;
                                $colorClass = 'text-gray-900';
                                
                                if (!$project->has_valid_warranty) {
                                    $colorClass = 'text-red-600 font-medium';
                                } elseif ($daysLeft <= 7) {
                                    $colorClass = 'text-red-600 font-medium';
                                } elseif ($daysLeft <= 30) {
                                    $colorClass = 'text-orange-500 font-medium';
                                } elseif ($daysLeft <= 90) {
                                    $colorClass = 'text-yellow-500 font-medium';
                                }
                            @endphp
                            <p class="text-base {{ $colorClass }}">
                                {{ \Carbon\Carbon::parse($project->warranty_end_date)->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                                <div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Công ty khách hàng</p>
                            <p class="text-base text-gray-900">{{ $project->customer->company_name }}</p>
                                </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Người đại diện</p>
                            <p class="text-base text-gray-900">{{ $project->customer->name }}</p>
                            </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Ngày kết thúc dự kiến</p>
                            <p class="text-base text-gray-900">{{ \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Nhân viên phụ trách</p>
                            <p class="text-base text-gray-900">{{ $project->employee ? $project->employee->name : 'Chưa phân công' }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Ngày tạo</p>
                            <p class="text-base text-gray-900">{{ \Carbon\Carbon::parse($project->created_at)->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <p class="text-sm font-medium text-gray-500 mb-1">Mô tả</p>
                        <p class="text-base text-gray-900 whitespace-pre-line">{{ $project->description ?? 'Không có mô tả' }}</p>
                        </div>
                    </div>
                </div>
                
            <!-- Thông tin khách hàng -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user-tie mr-2 text-blue-500"></i> Thông tin người đại diện
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Họ và tên</p>
                            <p class="text-base text-gray-900">{{ $project->customer->name }}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Số điện thoại</p>
                            <p class="text-base text-gray-900">{{ $project->customer->phone }}</p>
                        </div>
                    </div>
                            <div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Email</p>
                            <p class="text-base text-gray-900">{{ $project->customer->email ?? 'N/A' }}</p>
                            </div>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-500 mb-1">Địa chỉ</p>
                            <p class="text-base text-gray-900">{{ $project->customer->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thiết bị hàng hóa theo hợp đồng -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-box mr-2 text-blue-500"></i> Thiết bị hàng hóa theo hợp đồng
                    </h2>
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
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Trạng thái</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thông tin chi tiết</th>
                                <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
    @forelse($contractItems as $index => $itemData)
        @php
            $item = $itemData['dispatch_item'];
            $dispatch = $itemData['dispatch'];
            $serialIndex = $itemData['serial_index'];
            $serialNumber = $itemData['serial_number'];
            
            // Kiểm tra trạng thái ở cấp serial cụ thể - chỉ xem xét records từ cùng project
            $isReplaced = \App\Models\DispatchReplacement::where('original_serial', $serialNumber)
                ->whereHas('originalDispatchItem.dispatch', function($q) use ($project) {
                    $q->where('project_id', $project->id);
                })->exists();
            $isUsed = \App\Models\DispatchReplacement::where('replacement_serial', $serialNumber)
                ->whereHas('replacementDispatchItem.dispatch', function($q) use ($project) {
                    $q->where('project_id', $project->id);
                })->exists();
            $isReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                ->where('serial_number', $serialNumber)
                ->whereHas('dispatchItem.dispatch', function($q) use ($project) {
                    $q->where('project_id', $project->id);
                })->exists();
            
            // Serial được sử dụng để thay thế cũng phải hiển thị "Đã thay thế"
            $isReplacementSerial = \App\Models\DispatchReplacement::where('replacement_serial', $serialNumber)
                ->whereHas('replacementDispatchItem.dispatch', function($q) use ($project) {
                    $q->where('project_id', $project->id);
                })->exists();
                
            // Debug: Hiển thị thông tin chi tiết
            $debugInfo = "ItemID: {$item->id}, Serial: {$serialNumber}, isReplaced: " . ($isReplaced ? 'true' : 'false') . ", isReplacementSerial: " . ($isReplacementSerial ? 'true' : 'false');
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
                    @if($isReplaced || $isReplacementSerial)
                        <button data-id="{{ $item->id }}" data-serial="{{ $serialNumber }}" class="history-btn px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs hover:bg-orange-200 flex items-center justify-center space-x-1">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Đã thay thế</span>
                        </button>
                    @else
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Chưa thay thế</span>
                    @endif
                @endif
                <!-- Debug info -->
                <div class="text-xs text-gray-500 mt-1">{{ $debugInfo }}</div>
            </td>
            <td class="py-2 px-4 border-b">
                <a href="{{ route('inventory.dispatch.show', $item->dispatch_id) }}" class="text-blue-500 hover:text-blue-700">
                    <i class="fas fa-info-circle mr-1"></i> Xem chi tiết
                </a>
            </td>
            <td class="py-2 px-4 border-b">
                @if(!$isReturned && !$isReplaced && !$isReplacementSerial)
                    <button type="button" data-id="{{ $item->id }}" data-serial="{{ $serialNumber }}" data-code="{{ $item->item_type == 'material' && $item->material ? $item->material->code : ($item->item_type == 'product' && $item->product ? $item->product->code : ($item->item_type == 'good' && $item->good ? $item->good->code : 'N/A')) }}" class="warranty-btn text-blue-500 hover:text-blue-700">
                        <i class="fas fa-tools mr-1"></i> Bảo hành/Thay thế
                    </button>
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
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-tools mr-2 text-blue-500"></i> Thiết bị dự phòng/bảo hành
                    </h2>
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
                                use App\Models\DispatchReturn;
                                use App\Models\DispatchReplacement;
                                $visibleBackupItems = $backupItems->filter(function($itemData) use ($project) {
                                    $item = $itemData['dispatch_item'];
                                    $serialNumber = $itemData['serial_number'];
                                    
                                    // Kiểm tra serial cụ thể chưa được thu hồi - chỉ xem xét records từ cùng project
                                    $isSerialReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                                        ->where('serial_number', $serialNumber)
                                        ->whereHas('dispatchItem.dispatch', function($q) use ($project) {
                                            $q->where('project_id', $project->id);
                                        })->exists();
                                    
                                    return !$isSerialReturned;
                                });
                            @endphp
                            
                            @forelse($visibleBackupItems as $index => $itemData)
                                @php
                                    $item = $itemData['dispatch_item'];
                                    $dispatch = $itemData['dispatch'];
                                    $serialIndex = $itemData['serial_index'];
                                    $serialNumber = $itemData['serial_number'];
                                    
                                    // Kiểm tra trạng thái ở cấp serial cụ thể - chỉ xem xét records từ cùng project
                                    $isUsed = \App\Models\DispatchReplacement::where('replacement_serial', $serialNumber)
                                        ->whereHas('replacementDispatchItem.dispatch', function($q) use ($project) {
                                            $q->where('project_id', $project->id);
                                        })->exists();
                                    $isOriginalReplaced = \App\Models\DispatchReplacement::where('original_serial', $serialNumber)
                                        ->whereHas('originalDispatchItem.dispatch', function($q) use ($project) {
                                            $q->where('project_id', $project->id);
                                        })->exists();
                                    $isReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                                        ->where('serial_number', $serialNumber)
                                        ->whereHas('dispatchItem.dispatch', function($q) use ($project) {
                                            $q->where('project_id', $project->id);
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
        </main>
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
                    <div class="mb-4">
                        <label for="equipment_serial" class="block text-sm font-medium text-gray-700 mb-1">Chọn serial thiết bị hợp đồng</label>
                        <select id="equipment_serial" name="equipment_serial" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Chọn serial --</option>
                        </select>
                    </div>
                    <p class="mb-4">Bạn đang thực hiện bảo hành/thay thế cho thiết bị <span id="warranty-equipment-code" class="font-semibold"></span></p>
                    <div class="mb-4">
                        <label for="replacement_device_id" class="block text-sm font-medium text-gray-700 mb-1">Chọn thiết bị dự phòng thay thế</label>
                        <select id="replacement_device_id" name="replacement_device_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Chọn thiết bị --</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="replacement_serial" class="block text-sm font-medium text-gray-700 mb-1">Chọn serial thiết bị dự phòng</label>
                        <select id="replacement_serial" name="replacement_serial" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Chọn serial --</option>
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
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    
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
                // Get the project name from a data attribute to avoid JS issues
                const projectName = this.getAttribute('data-name');
                const projectId = this.getAttribute('data-id');
                openDeleteModal(projectId, projectName);
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
                    openModal('warranty-modal');
                    document.getElementById('warranty-equipment-id').value = equipmentId;
                    document.getElementById('warranty-equipment-code').textContent = equipmentCode;
                    loadEquipmentSerials(equipmentId);
                    fetchBackupItems();
                });
            });

            document.getElementById('replacement_device_id').addEventListener('change', function() {
                const selectedValue = this.value;
                if (selectedValue && selectedValue.includes(':')) {
                    const [itemId, serialNumber] = selectedValue.split(':');
                    // Tự động điền serial vào dropdown replacement_serial
                    const replacementSerialSelect = document.getElementById('replacement_serial');
                    replacementSerialSelect.innerHTML = '<option value="">-- Chọn serial --</option>';
                    
                    const option = document.createElement('option');
                    option.value = serialNumber;
                    option.textContent = `Serial ${serialNumber}`;
                    replacementSerialSelect.appendChild(option);
                    replacementSerialSelect.value = serialNumber;
                } else {
                    document.getElementById('replacement_serial').innerHTML = '<option value="">-- Chọn serial --</option>';
                }
            });

            // Khi chọn thiết bị dự phòng, load serial của thiết bị dự phòng
            document.getElementById('replacement_device_id').addEventListener('change', function() {
                const selectedValue = this.value;
                if (selectedValue && selectedValue.includes(':')) {
                    const [itemId, serialNumber] = selectedValue.split(':');
                    loadReplacementSerials(itemId);
                }
            });
        });

        // Override deleteCustomer function from delete-modal.js
        function deleteCustomer(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('projects.index') }}/" + id;
            
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

        // Dropdown Menus
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            
            // Close all other dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== id) {
                    d.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

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
            
            fetch(`/equipment-service/backup-items/project/{{ $project->id }}`)
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
            
            fetch(`/equipment-service/history/${equipmentId}`)
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
                                // Định dạng ngày theo yêu cầu: AA:BB DD/MM/YYYY
                                const date = new Date(replacement.replacement_date);
                                const formattedDate = date.toLocaleString('vi-VN', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric'
                                }).replace(',', '');
                                
                                // Lấy thông tin người thực hiện
                                const userName = replacement.employee_name || 'Không xác định';
                                
                                // Lấy thông tin serial thiết bị thay thế
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
                                        <td class="py-2 px-3 border-t">${userName}</td>
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

        // Khi mở modal, load serial của thiết bị hợp đồng
        function loadEquipmentSerials(equipmentId) {
            fetch(`/equipment-service/item-serials/${equipmentId}`)
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('equipment_serial');
                    select.innerHTML = '<option value="">-- Chọn serial --</option>';
                    if (data.serials && data.serials.length > 0) {
                        data.serials.forEach(serial => {
                            const option = document.createElement('option');
                            option.value = serial;
                            option.textContent = serial;
                            select.appendChild(option);
                        });
                    }
                });
        }
        // Khi chọn thiết bị dự phòng, load serial của thiết bị dự phòng
        function loadReplacementSerials(deviceId) {
            fetch(`/equipment-service/item-serials/${deviceId}`)
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('replacement_serial');
                    select.innerHTML = '<option value="">-- Chọn serial --</option>';
                    if (data.serials && data.serials.length > 0) {
                        data.serials.forEach(serial => {
                            const option = document.createElement('option');
                            option.value = serial;
                            option.textContent = serial;
                            select.appendChild(option);
                        });
                    }
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