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
            // BỎ QUA nếu đây là hàng thay thế (Vì nó đã được gộp số lượng vào dòng gốc rồi)
            // Tránh việc hiện 2 dòng (Dòng gốc 250 + Dòng thay thế 100) làm bác nhầm thành 350.
            if (!empty($itemData['is_replacement'])) {
                continue;
            }

            $item = $itemData['dispatch_item'];
            $dispatch = $itemData['dispatch'];
            $serialIndex = $itemData['serial_index'];
            $originalSerial = $itemData['serial_number'];
            
            // Lấy serial hiển thị - chỉ gọi helper nếu có serial
            $displaySerial = null;
            if (!empty($originalSerial)) {
                $displaySerial = \App\Helpers\SerialDisplayHelper::getDisplaySerial(
                    $dispatch->id,
                    $item->item_id,
                    $item->item_type,
                    $originalSerial
                );
            }
            
            // Kiểm tra trạng thái ở cấp serial cụ thể - chỉ xem xét records từ cùng project
            $isReplaced = false;
            $isUsed = false;
            $isReturned = false;
            $isReplacementSerial = false;
            
            if (!empty($originalSerial)) {
                // Có serial: kiểm tra theo original_serial
                $isReplaced = \App\Models\DispatchReplacement::where('original_serial', $originalSerial)
                    ->whereHas('originalDispatchItem.dispatch', function($q) use ($project) {
                        $q->where('project_id', $project->id);
                    })->exists();
                $isUsed = \App\Models\DispatchReplacement::where('replacement_serial', $originalSerial)
                    ->whereHas('replacementDispatchItem.dispatch', function($q) use ($project) {
                        $q->where('project_id', $project->id);
                    })->exists();
                $isReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                    ->where(function($q) use ($originalSerial, $displaySerial, $dispatch, $item) {
                        $q->where('serial_number', $originalSerial);
                        if ($displaySerial && $displaySerial !== $originalSerial) {
                            $q->orWhere('serial_number', $displaySerial);
                        }
                        // Tra cứu serial ảo (N/A-xxx) từ device_codes nếu serial hiện tại là serial thực
                        if (!\App\Helpers\SerialHelper::isVirtualSerial($originalSerial)) {
                            $dcReverse = \Illuminate\Support\Facades\DB::table('device_codes')
                                ->where('dispatch_id', $dispatch->id)
                                ->where('item_id', $item->item_id)
                                ->where('item_type', $item->item_type)
                                ->where('serial_main', $originalSerial)
                                ->first();
                            if ($dcReverse && !empty($dcReverse->old_serial)) {
                                $q->orWhere('serial_number', $dcReverse->old_serial);
                            }
                        }
                    })
                    ->whereHas('dispatchItem.dispatch', function($q) use ($project) {
                        $q->where('project_id', $project->id);
                    })->exists();
                
                // Nếu serial đã bị thu hồi nhưng VẪN CÒN trong serial_numbers hiện tại,
                // nghĩa là serial đã được thêm lại qua thay thế (replacement) → không coi là đã thu hồi
                if ($isReturned && in_array($originalSerial, $item->serial_numbers ?? [], true)) {
                    $isReturned = false;
                }
                
                // Serial được sử dụng để thay thế cũng phải hiển thị "Đã thay thế"
                $isReplacementSerial = \App\Models\DispatchReplacement::where('replacement_serial', $originalSerial)
                    ->whereHas('replacementDispatchItem.dispatch', function($q) use ($project) {
                        $q->where('project_id', $project->id);
                    })->exists();
            } else {
                // No serial (virtual serial N/A-0, N/A-1...): Kiểm tra theo virtual serial cụ thể
                $isReplaced = \App\Models\DispatchReplacement::where(function($q) use ($originalSerial) {
                        // Trường hợp 1: Serial → No Serial (no serial này là replacement)
                        $q->where('replacement_serial', $originalSerial);
                    })
                    ->orWhere(function($q) use ($originalSerial) {
                        // Trường hợp 2: No Serial → Serial (no serial này là original)
                        $q->where('original_serial', $originalSerial);
                    })
                    ->whereHas('originalDispatchItem.dispatch', function($q) use ($project) {
                        $q->where('project_id', $project->id);
                    })->exists();
                
                $isReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                    ->where(function($q) {
                        $q->whereNull('serial_number')
                          ->orWhere('serial_number', 'MEASUREMENT');
                    })
                    ->whereHas('dispatchItem.dispatch', function($q) use ($project) {
                        $q->where('project_id', $project->id);
                    })->exists();
            }
        @endphp
        <tr class="hover:bg-gray-50">
            <td class="py-2 px-4 border-b">{{ $loop->iteration }}</td>
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
                @if(!empty($itemData['is_measurement_unit']))
                    Số lượng: {{ (float)($itemData['override_quantity'] ?? $item->quantity) }} {{ $itemData['unit'] ?? '' }}
                @elseif(!empty($displaySerial))
                    {{ $displaySerial }}
                @elseif(!empty($originalSerial) && strpos($originalSerial, 'N/A-') === 0)
                    <span class="text-gray-500 italic">Không có Serial #{{ substr($originalSerial, 4) }}</span>
                @elseif(empty($originalSerial))
                    <span class="text-gray-500 italic">Không có Serial #{{ $serialIndex + 1 }}</span>
                @else
                    N/A
                @endif
            </td>
            <td class="py-2 px-4 border-b">
                @if(!empty($itemData['is_measurement_unit']))
                    @if(!empty($itemData['is_replacement']))
                        @if(!empty($itemData['is_fully_replaced_label']))
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">Đã thay thế</span>
                        @else
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Hàng thay thế</span>
                        @endif
                    @else
                        @php
                            $replacementQty = floatval($itemData['replaced_quantity'] ?? 0);
                        @endphp

                        @if($replacementQty > 0)
                            <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold">Đã thay thế</span>
                        @else
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Chưa thay thế</span>
                        @endif
                    @endif
                @elseif($isReturned)
                    <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs">Đã thu hồi</span>
                @else
                    @if($isReplaced || $isReplacementSerial)
                        <button data-id="{{ $item->id }}" data-serial="{{ $originalSerial }}" class="history-btn px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs hover:bg-orange-200 flex items-center justify-center space-x-1">
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
                <div class="flex space-x-3 items-center">
                    <button type="button" 
                        data-id="{{ $item->id }}" 
                        data-serial="{{ $originalSerial }}" 
                        data-is-measurement="{{ !empty($itemData['is_measurement_unit']) ? '1' : '0' }}"
                        data-max-qty="{{ $itemData['override_quantity'] ?? $item->quantity }}"
                        data-contract-max="{{ $itemData['override_quantity'] ?? $item->quantity }}"
                        data-unit="{{ $itemData['unit'] ?? '' }}"
                        data-code="{{ $item->item_type == 'material' && $item->material ? $item->material->code : ($item->item_type == 'product' && $item->product ? $item->product->code : ($item->item_type == 'good' && $item->good ? $item->good->code : 'N/A')) }}" 
                        class="warranty-btn text-blue-500 hover:text-blue-700">
                        <i class="fas fa-tools mr-1"></i> Bảo hành/Thay thế
                    </button>
                    @if((!$isReturned || !empty($itemData['is_measurement_unit'])) && $item->item_type !== 'material')
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
                    <button type="button" 
                        data-id="{{ $item->id }}" 
                        data-serial="{{ $originalSerial }}" 
                        data-is-measurement="{{ !empty($itemData['is_measurement_unit']) ? '1' : '0' }}"
                        data-max-qty="{{ $itemData['override_quantity'] ?? $item->quantity }}"
                        data-unit="{{ $itemData['unit'] ?? '' }}"
                        data-code="{{ $itemCode }}" 
                        data-replacement-id="{{ $itemData['replacement_id'] ?? '' }}"
                        data-is-used="{{ !empty($itemData['is_used']) ? '1' : '0' }}"
                        class="return-btn text-red-500 hover:text-red-700">
                        <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                    </button>
                    @elseif($item->item_type === 'material')
                    <span class="text-xs text-gray-400 italic" title="Vật tư không thu hồi qua dự án. Sử dụng chức năng Thu hồi Hàng hoá/Vật phẩm khác.">Không thu hồi tại đây</span>
                    @endif
                </div>
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
                                    // Đối với hàng đo lường, không ẩn hàng dự phòng chỉ vì có bản ghi thu hồi 
                                    // vì có thể thu hồi một phần. Quantity sẽ tự xử lý việc ẩn nếu về 0.
                                    if (!empty($itemData['is_measurement_unit'])) {
                                        if (!empty($itemData['is_used'])) {
                                            return (float)($itemData['override_quantity'] ?? 0) > 0;
                                        }
                                        $idle = (float)($itemData['override_quantity'] ?? 0);
                                        $used = (float)($itemData['used_quantity'] ?? 0);
                                        return ($idle + $used) > 0.0001;
                                    }

                                    // Kiểm tra serial cụ thể chưa được thu hồi - chỉ xem xét records từ cùng project
                                    // Cũng check virtual serial tương ứng qua device_codes
                                    $serialsToCheck = [$serialNumber];
                                    if (!\App\Helpers\SerialHelper::isVirtualSerial($serialNumber)) {
                                        $dc = \Illuminate\Support\Facades\DB::table('device_codes')
                                            ->where('dispatch_id', $itemData['dispatch']->id)
                                            ->where('item_id', $item->item_id)
                                            ->where('item_type', $item->item_type)
                                            ->where('serial_main', $serialNumber)
                                            ->first();
                                        if ($dc && !empty($dc->old_serial)) {
                                            $serialsToCheck[] = $dc->old_serial;
                                        }
                                    }
                                    $isSerialReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                                        ->whereIn('serial_number', $serialsToCheck)
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
                                    $originalSerial = $itemData['serial_number'];
                                    
                                    // Lấy serial hiển thị - chỉ gọi helper nếu có serial
                                    $displaySerial = null;
                                    if (!empty($originalSerial)) {
                                        $displaySerial = \App\Helpers\SerialDisplayHelper::getDisplaySerial(
                                            $dispatch->id,
                                            $item->item_id,
                                            $item->item_type,
                                            $originalSerial
                                        );
                                    }
                                    
                                    // Kiểm tra trạng thái ở cấp serial cụ thể - chỉ xem xét records từ cùng project
                                    $isUsed = false;
                                    $isOriginalReplaced = false;
                                    $isReturned = false;
                                    
                                    if (!empty($originalSerial)) {
                                        $isUsed = \App\Models\DispatchReplacement::where('replacement_dispatch_item_id', $item->id)
                                            ->where(function($q) use ($originalSerial, $displaySerial) {
                                                $q->where('replacement_serial', $originalSerial)
                                                  ->orWhere('replacement_serial', $displaySerial);
                                            })->exists();
                                        
                                        // Cũng kiểm tra nếu serial này là serial gốc bị swap sang backup (replaceEquipment swap serial)
                                        // Chỉ match nếu dispatch_item hiện tại THỰC SỰ là một bên trong replacement đó
                                        // (tránh match stale records khi serial đã bị thu hồi rồi xuất kho lại trên dispatch_item mới)
                                        if (!$isUsed) {
                                            $isUsed = \App\Models\DispatchReplacement::where(function($q) use ($originalSerial, $displaySerial) {
                                                $q->where('original_serial', $originalSerial)
                                                  ->orWhere('original_serial', $displaySerial);
                                            })->whereHas('originalDispatchItem.dispatch', function($q) use ($project) {
                                                $q->where('project_id', $project->id);
                                            })
                                            // Scope: chỉ coi là "đã sử dụng" khi dispatch_item hiện tại chính là item liên quan
                                            ->where(function($q) use ($item) {
                                                $q->where('original_dispatch_item_id', $item->id)
                                                  ->orWhere('replacement_dispatch_item_id', $item->id);
                                            })
                                            ->exists();
                                        }
                                        $isOriginalReplaced = \App\Models\DispatchReplacement::where('original_dispatch_item_id', $item->id)
                                            ->where(function($q) use ($originalSerial, $displaySerial) {
                                                $q->where('original_serial', $originalSerial)
                                                  ->orWhere('original_serial', $displaySerial);
                                            })->exists();
                                        $isReturned = \App\Models\DispatchReturn::where('dispatch_item_id', $item->id)
                                            ->where(function($q) use ($originalSerial, $displaySerial, $dispatch, $item) {
                                                $q->where('serial_number', $originalSerial)
                                                  ->orWhere('serial_number', $displaySerial);
                                                // Tra cứu serial ảo (N/A-xxx) từ device_codes nếu serial hiện tại là serial thực
                                                if (!\App\Helpers\SerialHelper::isVirtualSerial($originalSerial)) {
                                                    $dcReverse = \Illuminate\Support\Facades\DB::table('device_codes')
                                                        ->where('dispatch_id', $dispatch->id)
                                                        ->where('item_id', $item->item_id)
                                                        ->where('item_type', $item->item_type)
                                                        ->where('serial_main', $originalSerial)
                                                        ->first();
                                                    if ($dcReverse && !empty($dcReverse->old_serial)) {
                                                        $q->orWhere('serial_number', $dcReverse->old_serial);
                                                    }
                                                }
                                            })
                                            ->whereHas('dispatchItem.dispatch', function($q) use ($project) {
                                                $q->where('project_id', $project->id);
                                            })->exists();
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-4 border-b">{{ $loop->iteration }}</td>
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
                                        @if(!empty($itemData['is_measurement_unit']))
                                            <span class="font-medium text-gray-800">Số lượng: {{ (float)($itemData['override_quantity'] ?? $item->quantity) }} {{ $itemData['unit'] ?? '' }}</span>
                                        @elseif(!empty($displaySerial))
                                            {{ $displaySerial }}
                                        @elseif(!empty($originalSerial) && strpos($originalSerial, 'N/A-') === 0)
                                            <span class="text-gray-500 italic">Không có Serial #{{ substr($originalSerial, 4) }}</span>
                                        @elseif(empty($originalSerial))
                                            <span class="text-gray-500 italic">Không có Serial #{{ $serialIndex + 1 }}</span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        @if(!empty($itemData['is_used']))
                                            <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">Đã sử dụng</span>
                                            <div class="mt-1 text-xs text-gray-500 italic">Lưu ý: Thiết bị đã sử dụng là thiết bị cũ được thay thế; không thể dùng để thay thế nữa. Có thể thu hồi tùy trường hợp.</div>
                                        @elseif(!empty($itemData['is_measurement_unit']))
                                            @php
                                                $usedQty = floatval($itemData['used_quantity'] ?? 0);
                                                $availQty = floatval($itemData['available_quantity'] ?? $item->quantity);
                                            @endphp
                                            @if($usedQty > 0 && $availQty > 0)
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Sử dụng một phần</span>
                                                <div class="mt-1 text-xs text-gray-500">Đã dùng: {{ (float)$usedQty }} {{ $itemData['unit'] ?? '' }} | Còn lại: {{ (float)$availQty }} {{ $itemData['unit'] ?? '' }}</div>
                                            @elseif($usedQty > 0 && $availQty <= 0)
                                                <span class="px-2 py-1 bg-amber-100 text-amber-900 rounded-full text-xs font-semibold">Đang xuất thay thế</span>
                                                <div class="mt-1 text-xs text-gray-500">Toàn bộ tồn trên phiếu dự phòng đã xuất để thay thế (không còn idle trên dòng này).</div>
                                            @elseif($usedQty <= 0 && $availQty > 0)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Còn tồn dự phòng</span>
                                                <div class="mt-1 text-xs text-gray-500">Chưa xuất thay thế hoặc phần đã thay đã thu hồi về kho — không phải hàng mới chưa dùng.</div>
                                            @else
                                                <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">Hết tồn trên phiếu</span>
                                            @endif
                                        @elseif($isUsed || $isOriginalReplaced)
                                            <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">Đã sử dụng</span>
                                            <div class="mt-1 text-xs text-gray-500 italic">Lưu ý: Thiết bị đã sử dụng là thiết bị cũ được thay thế; không thể dùng để thay thế nữa. Có thể thu hồi tùy trường hợp.</div>
                                        @elseif($isReturned)
                                            <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded-full text-xs">Đã thu hồi</span>
                                        @else
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">Chưa sử dụng</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        <a href="{{ route('inventory.dispatch.show', $item->dispatch_id) }}" class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-info-circle mr-1"></i> Xem chi tiết
                                        </a>
                                    </td>
                                    <td class="py-2 px-4 border-b">
                                        @php
                                                    // Khai báo itemCode cho nút thu hồi
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

                                                    // Tính max qty cho thu hồi
                                                    $returnMaxQty = 0;
                                                    $showReturnBtn = false;
                                                    if (!empty($itemData['is_measurement_unit'])) {
                                                        if (!empty($itemData['is_used'])) {
                                                            // Hàng cũ bị thay thế: cho thu hồi theo override_quantity
                                                            $returnMaxQty = (float)($itemData['override_quantity'] ?? $itemData['available_quantity'] ?? $item->quantity);
                                                            $showReturnBtn = $returnMaxQty > 0;
                                                        } else {
                                                            // Hàng dự phòng idle: chỉ cho thu hồi phần available (override_quantity)
                                                            $returnMaxQty = (float)($itemData['override_quantity'] ?? 0);
                                                            $showReturnBtn = $returnMaxQty > 0.0001;
                                                        }
                                                    } else {
                                                        $returnMaxQty = (float)($itemData['override_quantity'] ?? $itemData['available_quantity'] ?? $item->quantity);
                                                        $showReturnBtn = !$isReturned;
                                                    }
                                                @endphp
                                                @if($showReturnBtn && $item->item_type !== 'material')
                                        <button type="button" 
                                            data-id="{{ $item->id }}" 
                                            data-serial="{{ $originalSerial }}" 
                                            data-is-measurement="{{ !empty($itemData['is_measurement_unit']) ? '1' : '0' }}"
                                            data-max-qty="{{ $returnMaxQty }}"
                                            data-unit="{{ $itemData['unit'] ?? '' }}"
                                            data-code="{{ $itemCode }}" 
                                            data-replacement-id="{{ $itemData['replacement_id'] ?? '' }}"
                                            data-is-used="{{ !empty($itemData['is_used']) ? '1' : '0' }}"
                                            data-merged-backup-ids="{{ !empty($itemData['merged_dispatch_item_ids']) ? implode(',', $itemData['merged_dispatch_item_ids']) : '' }}"
                                            class="return-btn text-red-500 hover:text-red-700">
                                            <i class="fas fa-undo-alt mr-1"></i> Thu hồi
                                        </button>
                                        @elseif($item->item_type === 'material')
                                        <span class="text-xs text-gray-400 italic" title="Vật tư không thu hồi qua dự án. Sử dụng chức năng Thu hồi Hàng hoá/Vật phẩm khác.">Không thu hồi tại đây</span>
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
                    <input type="hidden" id="warranty-equipment-serial" name="equipment_serial">
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <p class="mb-4">Bạn đang thực hiện bảo hành/thay thế cho thiết bị <span id="warranty-equipment-code" class="font-semibold"></span></p>
                    <div class="mb-4">
                        <label for="replacement_device_id" class="block text-sm font-medium text-gray-700 mb-1">Chọn thiết bị dự phòng thay thế</label>
                        <select id="replacement_device_id" name="replacement_device_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Chọn thiết bị --</option>
                        </select>
                    </div>

                    <div id="warranty-quantity-container" class="mb-4 hidden">
                        <label for="warranty-quantity" class="block text-sm font-medium text-gray-700 mb-1">Số lượng <span id="warranty-unit-label"></span></label>
                        <input type="number" id="warranty-quantity" name="quantity" step="0.001" min="0.001" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Tối đa: <span id="warranty-max-qty-label"></span></p>
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
                    <input type="hidden" id="return-replacement-id" name="replacement_id">
                    <input type="hidden" id="return-is-used" name="is_used">
                    <input type="hidden" id="return-merged-backup-ids" name="merged_backup_dispatch_item_ids" value="">
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

                    <div id="return-quantity-container" class="mb-4 hidden">
                        <label for="return-quantity" class="block text-sm font-medium text-gray-700 mb-1">Số lượng <span id="return-unit-label"></span></label>
                        <input type="number" id="return-quantity" name="quantity" step="0.001" min="0.001" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Tối đa: <span id="return-max-qty-label"></span></p>
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
                    const isMeasurement = button.getAttribute('data-is-measurement') === '1';
                    const maxQty = button.getAttribute('data-max-qty');
                    const unit = button.getAttribute('data-unit');
                    const replacementId = button.getAttribute('data-replacement-id');
                    const isUsed = button.getAttribute('data-is-used');
                    const mergedBackupIds = button.getAttribute('data-merged-backup-ids');
                    
                    openModal('return-modal');
                    document.getElementById('return-equipment-id').value = equipmentId;
                    document.getElementById('return-equipment-serial').value = equipmentSerial;
                    document.getElementById('return-replacement-id').value = replacementId || '';
                    document.getElementById('return-is-used').value = isUsed || '0';
                    const mergedEl = document.getElementById('return-merged-backup-ids');
                    if (mergedEl) mergedEl.value = mergedBackupIds || '';
                    document.getElementById('return-equipment-code').textContent = equipmentCode;

                    const qtyContainer = document.getElementById('return-quantity-container');
                    const qtyInput = document.getElementById('return-quantity');
                    const unitLabel = document.getElementById('return-unit-label');
                    const maxQtyLabel = document.getElementById('return-max-qty-label');

                    if (isMeasurement) {
                        qtyContainer.classList.remove('hidden');
                        qtyInput.required = true;
                        qtyInput.max = maxQty;
                        qtyInput.value = maxQty;
                        unitLabel.textContent = `(${unit})`;
                        maxQtyLabel.textContent = `${maxQty} ${unit}`;
                    } else {
                        qtyContainer.classList.add('hidden');
                        qtyInput.required = false;
                        qtyInput.value = 1;
                    }
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
                    const isMeasurement = this.getAttribute('data-is-measurement') === '1';
                    const maxQty = this.getAttribute('data-max-qty');
                    const contractMax = this.getAttribute('data-contract-max');
                    const unit = this.getAttribute('data-unit');

                    openModal('warranty-modal');
                    document.getElementById('warranty-equipment-id').value = equipmentId;
                    document.getElementById('warranty-equipment-code').textContent = equipmentCode;
                    document.getElementById('warranty-equipment-serial').value = equipmentSerial;

                    const qtyContainer = document.getElementById('warranty-quantity-container');
                    const qtyInput = document.getElementById('warranty-quantity');
                    const unitLabel = document.getElementById('warranty-unit-label');
                    const maxQtyLabel = document.getElementById('warranty-max-qty-label');

                    if (isMeasurement) {
                        qtyContainer.classList.remove('hidden');
                        qtyInput.required = true;
                        const cMax = parseFloat(contractMax || maxQty || '0');
                        qtyInput.dataset.contractMax = String(cMax);
                        qtyInput.max = cMax;
                        qtyInput.value = cMax;
                        unitLabel.textContent = `(${unit})`;
                        maxQtyLabel.textContent = `${cMax} ${unit}`;
                    } else {
                        qtyContainer.classList.add('hidden');
                        qtyInput.required = false;
                        qtyInput.value = 1;
                        delete qtyInput.dataset.contractMax;
                    }

                    fetchBackupItems(isMeasurement, unit);
                });
            });

            document.getElementById('replacement_device_id').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const selectedValue = this.value;
                const isMeasurement = selectedOption.getAttribute('data-is-measurement') === '1';
                
                if (selectedValue && selectedValue.includes(':')) {
                    const [itemId, serialNumber] = selectedValue.split(':');
                    
                    // Xử lý replacement_serial
                    let replacementSerial = serialNumber;
                    if (isMeasurement) {
                        replacementSerial = 'MEASUREMENT';
                    }
                    
                    const replacementSerialInput = document.createElement('input');
                    replacementSerialInput.type = 'hidden';
                    replacementSerialInput.name = 'replacement_serial';
                    replacementSerialInput.value = replacementSerial;
                    
                    const oldInput = document.querySelector('input[name="replacement_serial"]');
                    if (oldInput) oldInput.remove();
                    document.getElementById('warranty-form').appendChild(replacementSerialInput);
                    
                    if (isMeasurement) {
                        updateWarrantyMeasurementMax();
                    }
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

        /**
         * Tối đa thay thế (đo lường) = min(tổng hợp đồng, tổng dự phòng còn dùng được).
         * Hàng đã đổi từ dự phòng lên hợp đồng vẫn có thể đổi tiếp — không còn trừ "đã thay" khỏi trần hợp đồng ở UI.
         */
        function updateWarrantyMeasurementMax() {
            const qtyInput = document.getElementById('warranty-quantity');
            const replacementSelect = document.getElementById('replacement_device_id');
            const maxQtyLabel = document.getElementById('warranty-max-qty-label');
            const unitLabel = document.getElementById('warranty-unit-label');
            if (!qtyInput || !replacementSelect || qtyInput.dataset.contractMax === undefined) return;

            const contractMax = parseFloat(qtyInput.dataset.contractMax || '0');
            const unitPlain = (unitLabel && unitLabel.textContent) ? unitLabel.textContent.replace(/[()]/g, '').trim() : '';

            const selectedOption = replacementSelect.options[replacementSelect.selectedIndex];
            if (!selectedOption) return;

            const isMeasurement = selectedOption.getAttribute('data-is-measurement') === '1';
            if (!isMeasurement) return;

            if (!selectedOption.value) {
                qtyInput.max = contractMax;
                qtyInput.value = contractMax;
                if (maxQtyLabel) maxQtyLabel.textContent = `${contractMax} ${unitPlain}`;
                return;
            }

            const maxBackupQty = parseFloat(selectedOption.getAttribute('data-max-qty'));
            if (isNaN(maxBackupQty)) return;

            const realMax = Math.min(contractMax, maxBackupQty);
            qtyInput.max = realMax;
            if (parseFloat(qtyInput.value) > realMax) qtyInput.value = realMax;
            if (maxQtyLabel) maxQtyLabel.textContent = `${realMax} ${unitPlain}`;
        }

        // Lấy danh sách thiết bị dự phòng
        function fetchBackupItems(isMeasurement = false, unit = '') {
            const replacementDeviceSelect = document.getElementById('replacement_device_id');
            const currentEquipmentId = document.getElementById('warranty-equipment-id').value;
            const currentEquipmentCode = document.getElementById('warranty-equipment-code').textContent;
            
            replacementDeviceSelect.innerHTML = '<option value="">-- Đang tải dữ liệu... --</option>';
            
            fetch(`/equipment-service/backup-items/project/{{ $project->id }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const backupItems = data.backupItems;
                        const usedGlobalFromApi = Array.isArray(data.usedSerialsGlobal) ? data.usedSerialsGlobal.map(s => String(s).trim()) : [];
                        
                        // Xóa tất cả options hiện tại
                        replacementDeviceSelect.innerHTML = '<option value="">-- Chọn thiết bị --</option>';
                        
                        // Lọc thiết bị dự phòng theo cùng mã
                        const filteredItems = backupItems.filter(item => {
                            let itemCode = '';
                            if (item.item_type === 'material' && item.material) {
                                itemCode = item.material.code;
                            } else if (item.item_type === 'product' && item.product) {
                                itemCode = item.product.code;
                            } else if (item.item_type === 'good' && item.good) {
                                itemCode = item.good.code;
                            }
                            return (itemCode ? itemCode.trim() : '') === (currentEquipmentCode ? currentEquipmentCode.trim() : '');
                        });
                        
                        if (isMeasurement) {
                            // Xử lý đơn vị đo lường: Gom nhóm theo loại và ID sản phẩm
                            const groupedItems = {};
                            filteredItems.forEach(item => {
                                const itemType = item.item_type;
                                const itemId = item.item_id;
                                const key = `${itemType}_${itemId}`;
                                if (!groupedItems[key]) {
                                    groupedItems[key] = {
                                        item: item,
                                        totalQty: 0
                                    };
                                }
                                if (item.available_quantity !== undefined && item.available_quantity !== null) {
                                    groupedItems[key].totalQty += parseFloat(item.available_quantity);
                                } else {
                                    groupedItems[key].totalQty += parseFloat(item.quantity || 0);
                                }
                            });

                            Object.values(groupedItems).forEach(group => {
                                const item = group.item;
                                const qty = group.totalQty;
                                if (qty > 0) {
                                    let itemName = 'Không xác định';
                                    let itemCode = 'N/A';
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

                                    const option = document.createElement('option');
                                    // Gửi format GROUPED để backend nhận biết
                                    option.value = `GROUPED:${item.item_type}:${item.item_id}:MEASUREMENT`;
                                    option.textContent = `${itemCode} - ${itemName} (Tổng sẵn có: ${qty} ${unit})`;
                                    option.setAttribute('data-item-id', item.id);
                                    option.setAttribute('data-is-measurement', '1');
                                    option.setAttribute('data-max-qty', qty);
                                    replacementDeviceSelect.appendChild(option);
                                }
                            });
                            if (replacementDeviceSelect.options.length > 1) {
                                replacementDeviceSelect.selectedIndex = 1;
                                replacementDeviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        } else {
                            // Xử lý thiết bị có serial (Logic cũ)
                            const serialOptions = [];
                            filteredItems.forEach(item => {
                                let itemName = 'Không xác định';
                                let itemCode = 'N/A';
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
                                
                                const serialNumbers = item.serial_numbers || [];
                                const usedSerialSet = new Set([
                                    ...((item.replacement_serials || []).map(s => String(s).trim())),
                                    ...((item.used_serials_global || []).map(s => String(s).trim())),
                                    ...usedGlobalFromApi
                                ]);
                                
                                serialNumbers.forEach(serialNumber => {
                                    const serialStr = String(serialNumber).trim();
                                    if (!serialStr || usedSerialSet.has(serialStr)) return;
                                    
                                    const isVirtual = serialStr.startsWith('N/A-');
                                    let displayText;
                                    if (isVirtual) {
                                        const suffix = serialStr.replace('N/A-', '');
                                        displayText = `${itemCode} - ${itemName} - Không có Serial (#${suffix})`;
                                    } else {
                                        displayText = `${itemCode} - ${itemName} - Serial ${serialStr}`;
                                    }
                                    
                                    serialOptions.push({
                                        itemId: item.id,
                                        serialNumber: serialStr,
                                        displayText: displayText
                                    });
                                });
                            });
                            
                            serialOptions.forEach(option => {
                                const opt = document.createElement('option');
                                opt.value = `${option.itemId}:${option.serialNumber}`;
                                opt.textContent = option.displayText;
                                opt.setAttribute('data-item-id', option.itemId);
                                opt.setAttribute('data-serial', option.serialNumber);
                                replacementDeviceSelect.appendChild(opt);
                            });
                        }
                        
                        if (replacementDeviceSelect.options.length <= 1) {
                            const option = document.createElement('option');
                            option.value = "";
                            option.textContent = "Không có thiết bị dự phòng nào phù hợp";
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
                        const returns = data.returns;

                        
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
                                <div class="mb-4">
                                    <h4 class="text-md font-medium text-gray-800 mb-2">Lịch sử thay đổi</h4>
                                    <p class="text-sm text-gray-500 italic">Chưa có lịch sử thay thế cho thiết bị này.</p>
                                </div>
                            `;
                        }

                        // Hiển thị lịch sử thu hồi
                        if (returns && returns.length > 0) {
                            html += `
                                <div class="mb-4">
                                    <h4 class="text-md font-medium text-gray-800 mb-2">Lịch sử thu hồi</h4>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <table class="min-w-full">
                                            <thead>
                                                <tr class="bg-gray-100">
                                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Ngày thu hồi</th>
                                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Người thực hiện</th>
                                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Serial/Số lượng</th>
                                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-700">Lý do</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                            `;
                            
                            returns.forEach(ret => {
                                const date = new Date(ret.return_date);
                                const formattedDate = date.toLocaleString('vi-VN', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric'
                                }).replace(',', '');
                                
                                const userName = ret.employee_name || 'Không xác định';
                                const qtyInfo = ret.serial_number === 'MEASUREMENT' ? `Số lượng: ${ret.quantity}` : `Serial: ${ret.serial_number}`;
                                
                                html += `
                                    <tr>
                                        <td class="py-2 px-3 border-t text-sm">${formattedDate}</td>
                                        <td class="py-2 px-3 border-t text-sm">${userName}</td>
                                        <td class="py-2 px-3 border-t text-sm font-medium">${qtyInfo}</td>
                                        <td class="py-2 px-3 border-t text-sm">${ret.reason || 'Không có lý do'}</td>
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
                                <div class="mb-4">
                                    <h4 class="text-md font-medium text-gray-800 mb-2">Lịch sử thu hồi</h4>
                                    <p class="text-sm text-gray-500 italic">Chưa có lịch sử thu hồi cho thiết bị này.</p>
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