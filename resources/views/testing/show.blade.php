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

            /* Biến input/select/textarea thành text khi in */
            input, select, textarea {
                border: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
                padding: 0 !important;
                height: auto !important;
                outline: none !important;
                appearance: none !important;
            }

            button, a, .btn, .actions, .action-buttons { display: none !important; }

            /* Loại bỏ viền/thẻ card khi in */
            .print\:border-0 { border: 0 !important; }
            .print\:shadow-none { box-shadow: none !important; }

            .page-break {
                page-break-before: always;
            }
        }

        .print-only {
            display: none;
        }

        /* Styles cho dropdown vật tư trống Serial */
        select.bg-yellow-50 {
            transition: all 0.3s ease;
            font-weight: 500;
        }

        select.bg-yellow-50:focus {
            transform: scale(1.02);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }

        select.bg-green-50 {
            color: #065f46;
        }

        select.bg-red-50 {
            color: #991b1b;
        }

        /* Animation cho dropdown khi thay đổi */
        @keyframes dropdown-change {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        select.bg-yellow-50:not(:focus) {
            animation: dropdown-change 0.2s ease-in-out;
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
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->assignedEmployee->name ?? 'N/A' }}</p>
                        </div>

                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Chỉnh sửa lần cuối</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $testing->updated_at->format('d/m/Y H:i') }}</p>
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

                        @php
                            $__notesData = is_string($testing->notes) ? json_decode($testing->notes, true) : (is_array($testing->notes) ? $testing->notes : []);
                            $__generalNote = '';
                            
                            if (is_array($__notesData) && array_key_exists('general_note', $__notesData)) {
                                $__generalNote = $__notesData['general_note'];
                                // Nếu general_note vẫn là JSON string, decode thêm lần nữa
                                if (is_string($__generalNote) && (strpos($__generalNote, '{') === 0 || strpos($__generalNote, '[') === 0)) {
                                    $decoded = json_decode($__generalNote, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && array_key_exists('general_note', $decoded)) {
                                        $__generalNote = $decoded['general_note'];
                                    }
                                }
                            } elseif (is_string($testing->notes)) {
                                // Nếu notes là string thuần, kiểm tra xem có phải JSON không
                                if (strpos($testing->notes, '{') === 0 || strpos($testing->notes, '[') === 0) {
                                    $decoded = json_decode($testing->notes, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && array_key_exists('general_note', $decoded)) {
                                        $__generalNote = $decoded['general_note'];
                                    }
                                } else {
                                    $__generalNote = $testing->notes;
                                }
                            }
                        @endphp
                        @if(!empty($__generalNote))
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ghi chú</p>
                            <p class="text-base text-gray-800">{{ $__generalNote }}</p>
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
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
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
                                    <th class="py-2 px-3 border-b border-gray-200 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">SERIAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $globalUnitCounter = 0; // Biến đếm toàn cục cho đơn vị thành phẩm
                                @endphp
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
                                                <div class="mb-0.5 text-gray-400">N/A
                                        </div>
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
                    <td colspan="5" class="py-4">Chưa có vật tư/hàng hóa nào được thêm</td>
                </tr>
                @endforelse
                </tbody>
                </table>
            </div>
    </div>

    <!-- Form cập nhật kết quả kiểm thử -->
    @php $isReadOnly = $testing->status != 'in_progress'; @endphp
    @if(true)
    <form action="{{ route('testing.update', $testing->id) }}" method="POST" class="mb-4" id="test-item-form" @if($isReadOnly) onsubmit="return false;" @endif>
        @csrf
        @method('PUT')

        <!-- Thêm các trường ẩn cần thiết -->
        <input type="hidden" name="tester_id" value="{{ $testing->tester_id }}">
        <input type="hidden" name="assigned_to" value="{{ $testing->assigned_to ?? $testing->tester_id ?? '' }}">
        <input type="hidden" name="receiver_id" value="{{ $testing->receiver_id }}">
        <input type="hidden" name="test_date" value="{{ $testing->test_date->format('Y-m-d') }}">
        <input type="hidden" name="notes" value="{{ $testing->notes }}">

    <!-- Vật tư/Hàng hóa cho phiếu kiểm thử loại vật tư/hàng hóa -->
    @if($testing->test_type == 'material')
    @foreach($testing->items as $idx => $item)
    @php
        $code = $item->material->code ?? ($item->good->code ?? '');
        $name = $item->material->name ?? ($item->good->name ?? '');
        $typeText = $item->item_type == 'material' ? 'Vật tư' : 'Hàng hóa';
        $serialsRow = $item->serial_number ? array_values(array_filter(array_map('trim', explode(',', $item->serial_number)))) : [];
        $quantity = (int)($item->quantity ?? 0);
        $serialCount = count($serialsRow);
        $resultMap = $item->serial_results ? json_decode($item->serial_results, true) : [];
    @endphp
    <div class="mb-4 rounded-lg overflow-hidden border border-green-200">
        <div class="bg-green-50 px-3 py-2 flex items-center justify-between border-b border-green-200">
            <div class="text-sm text-green-800 font-medium">
                <i class="fas fa-box-open mr-2"></i>{{ $idx + 1 }}. {{ $code }} - {{ $name }} ({{ $typeText }})
            </div>
            <div class="flex items-center gap-3">
                @php
                    // Tính KẾT QUẢ cho từng vật tư / hàng hoá
                    $passCount = 0; $failCount = 0; $pendingCount = 0;
                    for($__i=0; $__i<$quantity; $__i++){
                        $__label = chr(65 + $__i);
                        $__val = $resultMap[$__label] ?? 'pending';
                        if($__val === 'pass') $passCount++;
                        elseif($__val === 'fail') $failCount++;
                        else $pendingCount++;
                    }
                @endphp
                <div class="text-xs">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium mr-2">
                        <i class="fas fa-check-circle mr-1"></i> {{ $passCount }} Đạt
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">
                        <i class="fas fa-times-circle mr-1"></i> {{ $failCount }} Không đạt
                    </span>
                </div>
                <div class="text-xs text-green-700">Số lượng: {{ $quantity }}</div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-600">
                        <th class="px-3 py-2">STT</th>
                        <th class="px-3 py-2">MÃ</th>
                        <th class="px-3 py-2">LOẠI</th>
                        <th class="px-3 py-2">TÊN</th>
                        <th class="px-3 py-2">SERIAL</th>
                        <th class="px-3 py-2">KHO</th>
                        <th class="px-3 py-2">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @for($row = 0; $row < $quantity; $row++)
                        @php
                            $label = chr(65 + $row);
                            $serialValue = $serialsRow[$row] ?? null;
                        @endphp
                        <tr>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ $row + 1 }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ $code }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ $typeText }}</td>
                            <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ $name }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ $serialValue ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ $item->warehouse->name ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">
                                <select name="serial_results[{{ $item->id }}][{{ $label }}]" class="w-32 h-8 border border-gray-300 rounded px-2 text-xs bg-white">
                                    <option value="pending" {{ ($resultMap[$label] ?? 'pending') == 'pending' ? 'selected' : '' }}>Chưa có</option>
                                    <option value="pass" {{ ($resultMap[$label] ?? '') == 'pass' ? 'selected' : '' }}>Đạt</option>
                                    <option value="fail" {{ ($resultMap[$label] ?? '') == 'fail' ? 'selected' : '' }}>Không đạt</option>
                                </select>
                            </td>
                        </tr>
                    @endfor
                </tbody>
                {{-- KHÔNG CẦN HÀNG "SỐ LƯỢNG ĐẠT" CHO VẬT TƯ HÀNG HOÁ NỮA --}}
            </table>
        </div>

        <!-- Hạng mục kiểm thử (Không bắt buộc) cho Vật tư/Hàng hóa - NẰM TRONG BẢNG -->
        <div class="mt-4 border-t border-gray-200 pt-4">
            <div class="flex justify-between items-center mb-3">
                <h5 class="font-medium text-gray-800 text-sm">🔍 Hạng mục kiểm thử (Không bắt buộc)</h5>
                @if(!$isReadOnly)
                <div class="flex items-center gap-2">
                    <input type="text" placeholder="Nhập hạng mục kiểm thử" class="h-7 border border-gray-300 rounded px-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" id="new_test_item_name_show_{{ $item->id }}">
                    <button type="button" onclick="addDefaultTestItemsForShow('{{ $item->id }}')" class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-xs flex items-center">
                        <i class="fas fa-list-check mr-1"></i> Mặc định
                    </button>
                    <button type="button" onclick="addTestItemForShow('{{ $item->id }}')" class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs flex items-center">
                        <i class="fas fa-plus mr-1"></i> Thêm
                    </button>
                </div>
                @endif
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded p-3">
                <div class="space-y-2" id="test_items_container_show_{{ $item->id }}">
                    @php
                        $testDetails = $testing->details ? $testing->details->where('item_id', $item->id) : collect();
                    @endphp
                    @forelse($testDetails as $detail)
                        <div class="test-item flex items-center gap-3" data-detail-id="{{ $detail->id }}">
                            <input type="text" value="{{ $detail->test_item_name }}" class="h-8 border border-gray-300 rounded px-2 py-1 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm" @if($isReadOnly) readonly @endif>
                            @if(!$isReadOnly)
                            <button type="button" onclick="removeTestItemForShow('{{ $detail->id }}', this)" class="px-2 py-1 bg-red-100 text-red-500 rounded hover:bg-red-200 text-xs">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-gray-500 py-2 text-sm">Chưa có hạng mục kiểm thử nào được thêm</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Ghi chú cho vật tư/hàng hóa này -->
        <div class="mt-4 border-t border-gray-200 pt-4">
            <div class="flex justify-between items-center mb-3">
                <h5 class="font-medium text-gray-800 text-sm">📝 Ghi chú</h5>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded p-3">
                <textarea name="item_notes[{{ $item->id }}]" rows="2" class="w-full border-0 focus:outline-none focus:ring-0 resize-none text-sm" placeholder="Nhập ghi chú cho {{ $typeText }} này..." @if($isReadOnly) readonly @endif>{{ $item->notes }}</textarea>
            </div>
        </div>
    </div>
    @endforeach
    @endif



    <!-- Vật tư lắp ráp cho từng thành phẩm -->
    @if($testing->test_type == 'finished_product')
    @foreach($testing->items->filter(function($item) use ($testing) {
    return $item->item_type == 'product' || $item->item_type == 'finished_product';
    }) as $index => $item)
    <div class="border border-gray-200 rounded-lg p-4 mb-6">
        <div class="mb-4">
            <h4 class="font-medium text-gray-800">
                {{ $index + 1 }}. 
                @if($item->item_type == 'product' && $item->product)
                    {{ $item->product->code }} - {{ $item->product->name }}
                @elseif($item->item_type == 'finished_product' && $item->good)
                    {{ $item->good->code }} - {{ $item->good->name }}
                @else
                    <span class="text-red-500">Không tìm thấy thông tin (Type: {{ $item->item_type }}, ID: {{ $item->product_id ?? $item->good_id }})</span>
                @endif
            </h4>
            <div class="flex items-center gap-4 mt-1 text-sm text-gray-600">
                <span>Loại: Thành phẩm</span>
                <span>Số lượng: {{ $item->quantity }}</span>
                <span class="ml-4">
                    <span class="text-gray-700 font-medium">KẾT QUẢ:</span>
                    <div class="inline-flex items-center gap-2 ml-2">
                        @php
                        $passQuantity = (int)($item->pass_quantity ?? 0);
                        $failQuantity = (int)($item->fail_quantity ?? 0);
                        $totalQuantity = (int)($item->quantity ?? 0);
                        $serialResults = json_decode($item->serial_results ?? '{}', true);
                        $isProductFail = $failQuantity > 0;
                        @endphp
                        
                        @if($passQuantity > 0 && $failQuantity > 0)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i> {{ $passQuantity }} Đạt
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i> {{ $failQuantity }} Không đạt
                        </span>
                        @elseif($passQuantity > 0)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i> {{ $passQuantity }} Đạt
                        </span>
                        @elseif($failQuantity > 0)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i> {{ $failQuantity }} Không đạt
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-clock mr-1"></i> Chưa có kết quả
                        </span>
                        @endif
                        
                        <span class="text-xs text-gray-500">(Tự động tính từ vật tư lắp ráp - Tất cả vật tư đạt → Thành phẩm đạt, Có vật tư fail → Thành phẩm fail)</span>
                    </div>
                    
                    
                </span>
            </div>
        </div>
    @php
    // Lấy product_id từ testing item
    $productIdForView = null;
    if ($item->item_type == 'finished_product') {
    $productIdForView = $item->good_id ?? null;
    } elseif ($item->item_type == 'product') {
    $productIdForView = $item->product_id ?? null;
    }

    $materialsByUnit = [];
    $productSerialsForUnits = [];
    if ($testing->assembly) {
    $apForProduct = $testing->assembly->products ? $testing->assembly->products->firstWhere('product_id', $productIdForView) : null;

    if (!$apForProduct && $testing->assembly->products) {
    $apForProduct = $testing->assembly->products->first();
    }

    if ($apForProduct) {
    if (!empty($apForProduct->serials)) {
        // Tách serial theo từng đơn vị thành phẩm - KHÔNG filter để giữ nguyên thứ tự và N/A
        $allSerials = array_map('trim', explode(',', $apForProduct->serials));
        $productSerialsForUnits = [];
        
        // Nếu có product_unit, sử dụng nó để map serial đúng với unit
        $productUnits = $apForProduct->product_unit;
        if (is_array($productUnits)) {
            // Map serial theo product_unit
            foreach ($allSerials as $index => $serial) {
                if (isset($productUnits[$index])) {
                    $unitIdx = $productUnits[$index];
                    // Chỉ gán serial có giá trị (không phải N/A hoặc rỗng)
                    if (!empty($serial) && strtoupper($serial) !== 'N/A') {
                        $productSerialsForUnits[$unitIdx] = $serial;
                    }
                }
            }
        } else {
            // Fallback: phân bổ serial theo thứ tự (bỏ qua N/A và rỗng)
            $validSerials = array_filter($allSerials, function($s) {
                return !empty($s) && strtoupper($s) !== 'N/A';
            });
            foreach (array_values($validSerials) as $index => $serial) {
                $productSerialsForUnits[$index + 1] = $serial;
            }
        }
    }
    $unitProductName = $apForProduct->product->name ?? ($apForProduct->product->code ?? 'Thành phẩm');
    }

    if (empty($productSerialsForUnits) && !empty($item->serial_number)) {
        // Fallback: nếu không có assembly, dùng serial từ testing item
        $allSerials = array_map('trim', explode(',', $item->serial_number));
        $productSerialsForUnits = [];
        
        // Chỉ lấy serial có giá trị (không phải N/A hoặc rỗng)
        $validSerials = array_filter($allSerials, function($s) {
            return !empty($s) && strtoupper($s) !== 'N/A';
        });
        foreach (array_values($validSerials) as $index => $serial) {
            $productSerialsForUnits[$index + 1] = $serial;  // 1-based
        }
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
    // Tạo mapping chính xác giữa assembly material và testing item
    $testingMaterialMap = collect();
    foreach ($testing->items->where('item_type', 'material') as $testingItem) {
        if ($testingItem->material_id) {
            // Sử dụng item->id thay vì material_id để tránh ảnh hưởng chéo
            $testingMaterialMap->put($testingItem->id, $testingItem);
        }
    }
    @endphp

    @if(!empty($materialsByUnit))
    @foreach($materialsByUnit as $unitIdx => $unitMaterials)
    @php
        $globalUnitCounter++; // Tăng biến đếm toàn cục
        $displayUnitIndex = $globalUnitCounter; // Sử dụng biến đếm toàn cục
    @endphp
    @php
        $serialResultsForUnits = json_decode($item->serial_results ?? '{}', true) ?: [];
        // Hàm lấy label theo index đơn vị: 1->A, 2->B, ...
        $makeLabel = function($idx){ return chr(64 + (int)$idx); };
        $unitNumberForLabel = is_numeric($unitIdx) ? ((int)$unitIdx + 1) : $displayUnitIndex; // ưu tiên index theo sản phẩm
        $isFailUnit = isset($serialResultsForUnits[$makeLabel($unitNumberForLabel)]) && $serialResultsForUnits[$makeLabel($unitNumberForLabel)] === 'fail';
    @endphp
    <div class="mt-6 mb-4 rounded-lg overflow-hidden border {{ $isFailUnit ? 'border-yellow-200' : 'border-green-200' }}">
        <div class="px-3 py-2 flex items-center justify-between border-b {{ $isFailUnit ? 'bg-yellow-50 border-yellow-200' : 'bg-green-50 border-green-200' }}">
            <div class="text-sm font-medium {{ $isFailUnit ? 'text-yellow-800' : 'text-green-800' }}">
                <i class="fas fa-box-open mr-2"></i> Đơn vị thành phẩm {{ $displayUnitIndex }} - {{ $unitProductName ?? 'Thành phẩm' }} - Serial {{ isset($productSerialsForUnits[$unitIdx]) ? $productSerialsForUnits[$unitIdx] : 'N/A' }}
            </div>
            <div class="text-xs {{ $isFailUnit ? 'text-yellow-700' : 'text-green-700' }}">{{ count($unitMaterials) }} vật tư</div>
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
                    // Tìm testing item dựa trên material_id và serial để tránh ảnh hưởng chéo
                    $testingItemRow = null;
                    $serialsRow = $asmMaterial->serial ? array_values(array_filter(array_map('trim', explode(',', $asmMaterial->serial)))) : [];
                    
                    // Tìm item có material_id và serial khớp
                    foreach ($testing->items->where('item_type', 'material') as $testingItem) {
                        if ($testingItem->material_id == $asmMaterial->material_id) {
                            // Kiểm tra serial có khớp không
                            if (!empty($testingItem->serial_number) && !empty($asmMaterial->serial)) {
                                $itemSerials = array_values(array_filter(array_map('trim', explode(',', $testingItem->serial_number))));
                                $asmSerials = array_values(array_filter(array_map('trim', explode(',', $asmMaterial->serial))));
                                
                                // So sánh serial arrays
                                if (count(array_intersect($itemSerials, $asmSerials)) > 0) {
                                    $testingItemRow = $testingItem;
                                    break;
                                }
                    } else {
                                // Nếu không có serial, dùng item đầu tiên có material_id khớp
                                $testingItemRow = $testingItem;
                                break;
                            }
                        }
                    }
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
                                @php
                                $quantity = $asmMaterial->quantity ?? 0;
                                $serialCount = count($serialsRow);
                                $noSerialCount = $quantity - $serialCount;
                                @endphp
                                @foreach($serialsRow as $s)
                                <div class="mb-0.5">{{ $s }}</div>
                                @endforeach
                                @for($i = 0; $i < $noSerialCount; $i++)
                                    <div class="mb-0.5 text-gray-400">N/A
                            </div>
                            @endfor
                            <div class="text-gray-400">{{ $serialCount }} serial{{ $serialCount > 1 ? 's' : '' }}{{ $noSerialCount > 0 ? ', ' . $noSerialCount . ' N/A' : '' }}</div>
        </div>
        @else
        @php
        $quantity = $asmMaterial->quantity ?? 0;
        @endphp
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
    <td class="px-3 py-2 text-sm text-gray-700">
        @if($asmMaterial->warehouse)
        {{ $asmMaterial->warehouse->name }}
        @else
        N/A
        @endif
    </td>
    <td class="px-3 py-2 text-sm text-gray-700">
        @php
            $noteToShow = isset($testingItemRow) && isset($testingItemRow->notes) && trim((string)$testingItemRow->notes) !== ''
                ? $testingItemRow->notes
                : ($asmMaterial->note ?? '');
        @endphp
        {{ $noteToShow }}
    </td>
    <td class="px-3 py-2 text-sm text-gray-700">
        @if($testing->status == 'in_progress')
        @php
        $quantity = $asmMaterial->quantity ?? 0;
        $serialCount = count($serialsRow);
        $noSerialCount = $quantity - $serialCount;
        @endphp
        @if($quantity > 0)
        @php $resultMapRow = $testingItemRow && $testingItemRow->serial_results ? json_decode($testingItemRow->serial_results, true) : []; @endphp
        <div class="space-y-1">
            @for($i = 0; $i < $quantity; $i++)
                @php $label=chr(65 + $i); @endphp
                @if($i < $serialCount)
                    <select name="serial_results[{{ $testingItemRow->id }}][{{ $label }}]" class="w-full h-8 border border-gray-300 rounded px-2 text-xs bg-white">
                <option value="pending" {{ ($resultMapRow[$label] ?? 'pending') == 'pending' ? 'selected' : '' }}>Chưa có</option>
                <option value="pass" {{ ($resultMapRow[$label] ?? '') == 'pass' ? 'selected' : '' }}>Đạt</option>
                <option value="fail" {{ ($resultMapRow[$label] ?? '') == 'fail' ? 'selected' : '' }}>Không đạt</option>
                </select>
                @else
                {{-- Thay N/A bằng dropdown Đạt/Không đạt cho vật tư trống Serial --}}
                <select name="serial_results[{{ $testingItemRow->id }}][{{ $label }}]" class="w-full h-8 border border-yellow-300 rounded px-2 text-xs bg-yellow-50">
                    <option value="pending" {{ ($resultMapRow[$label] ?? 'pending') == 'pending' ? 'selected' : '' }}>Chưa có</option>
                    <option value="pass" {{ ($resultMapRow[$label] ?? '') == 'pass' ? 'selected' : '' }}>Đạt</option>
                    <option value="fail" {{ ($resultMapRow[$label] ?? '') == 'fail' ? 'selected' : '' }}>Không đạt</option>
                </select>
                @endif
                @endfor
                </div>
        @else
        @php $maxQtyRow = (int)($asmMaterial->quantity ?? 0); @endphp
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-600">Số lượng Đạt</span>
            <input type="number" name="item_pass_quantity[{{ $asmMaterial->material_id }}]" min="0" max="{{ $maxQtyRow }}" value="{{ $testingItemRow->pass_quantity ?? 0 }}" class="w-20 h-8 border border-gray-300 rounded px-2 text-sm bg-white" />
            </div>
        @endif
        @else
        @if($testing->status == 'completed')
            @php
                $quantity = (int)($asmMaterial->quantity ?? 0);
                $serialCount = count($serialsRow);
                $resultMapRow = $testingItemRow && $testingItemRow->serial_results ? json_decode($testingItemRow->serial_results, true) : [];
            @endphp
            <div class="space-y-1">
                @for($i = 0; $i < $quantity; $i++)
                    @php $label=chr(65 + $i); @endphp
                    @if($i < $serialCount)
                        <select class="w-full h-8 border border-gray-300 rounded px-2 text-xs bg-gray-100 text-gray-700" disabled>
                            <option {{ ($resultMapRow[$label] ?? 'pending') == 'pending' ? 'selected' : '' }}>Chưa có</option>
                            <option {{ ($resultMapRow[$label] ?? '') == 'pass' ? 'selected' : '' }}>Đạt</option>
                            <option {{ ($resultMapRow[$label] ?? '') == 'fail' ? 'selected' : '' }}>Không đạt</option>
                        </select>
                    @else
                        {{-- Hiển thị dropdown disabled cho vật tư trống Serial khi completed --}}
                        <select class="w-full h-8 border border-gray-300 rounded px-2 text-xs bg-yellow-50 text-gray-700" disabled>
                            <option {{ ($resultMapRow[$label] ?? 'pending') == 'pending' ? 'selected' : '' }}>Chưa có</option>
                            <option {{ ($resultMapRow[$label] ?? '') == 'pass' ? 'selected' : '' }}>Đạt</option>
                            <option {{ ($resultMapRow[$label] ?? '') == 'fail' ? 'selected' : '' }}>Không đạt</option>
                        </select>
                    @endif
                @endfor
            </div>
        @else
            <span class="text-gray-400 text-xs">Chưa tiếp nhận</span>
        @endif
        @endif
    </td>
    </tr>
    @endforeach
    </tbody>

    <!-- Hàng tổng hợp cho vật tư không có serial - CHỈ HIỂN THỊ KHI CÓ VẬT TƯ TRỐNG SERIAL -->
    @php
    // Tính tổng số lượng của các item không có serial (N/A) - luôn tính để dùng cho mọi trạng thái
    $totalNoSerialQuantity = 0;
    foreach($unitMaterials as $asmMaterial) {
        $serialsRow = $asmMaterial->serial ? array_values(array_filter(array_map('trim', explode(',', $asmMaterial->serial)))) : [];
        $quantity = $asmMaterial->quantity ?? 0;
        $serialCount = count($serialsRow);
        $noSerialCount = $quantity - $serialCount;
        $totalNoSerialQuantity += $noSerialCount;
    }

    // Lấy giá trị đã lưu từ notes
    $savedNoSerialPassQuantity = 0;
    if ($testing->notes) {
        $notesData = json_decode($testing->notes, true);
        if (is_array($notesData) && isset($notesData['no_serial_pass_quantity'][$item->id]) && isset($notesData['no_serial_pass_quantity'][$item->id][$unitIdx])) {
            $savedNoSerialPassQuantity = (int) $notesData['no_serial_pass_quantity'][$item->id][$unitIdx];
        }
    }
    @endphp
    
    {{-- KHÔNG CẦN HÀNG "SỐ LƯỢNG ĐẠT" NỮA VÌ ĐÃ CÓ DROPDOWN CHO TỪNG VẬT TƯ N/A --}}
    </table>
    </div>
    </div>
    @endforeach
    @else
    <div class="mt-6 text-center text-gray-500 py-4">Không có vật tư lắp ráp cho thành phẩm này</div>
    @endif
    
    @if($testing->test_type != 'material')
    <!-- Hạng mục kiểm thử (Không bắt buộc) - NHÚNG TRỰC TIẾP vào card thành phẩm -->
    @php
        $productName = $item->product ? $item->product->name : ($item->good ? $item->good->name : 'Thành phẩm');
        $productCode = $item->product ? $item->product->code : ($item->good ? $item->good->code : 'N/A');
        // Hiển thị chỉ hạng mục kiểm thử thuộc về thành phẩm này
        $testDetails = $testing->details ? $testing->details->where('item_id', $item->id) : collect();
    @endphp
    
    <div class="border-t border-green-200 bg-blue-50 p-4">
        <div class="flex justify-between items-center mb-4">
            <h5 class="font-semibold text-gray-800 text-lg">
                <span class="text-blue-700">🔍 Hạng mục kiểm thử (Không bắt buộc)</span>
                <span class="block text-sm text-gray-600 mt-1">Thành phẩm: {{ $productCode }} - {{ $productName }}</span>
            </h5>
            
            @if($testing->status == 'in_progress')
                <div class="flex items-center gap-3">
                    <input type="text" 
                           placeholder="Nhập hạng mục kiểm thử..." 
                           class="h-10 border border-gray-300 rounded-lg px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm" 
                           id="new_test_item_name_show_{{ $item->id }}">
                    
                    <button type="button" 
                            onclick="addDefaultTestItemsForShow('{{ $item->id }}')" 
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm flex items-center shadow-sm transition-colors">
                        <i class="fas fa-list-check mr-2"></i> Thêm mục mặc định
                    </button>
                    
                    <button type="button" 
                            onclick="addTestItemForShow('{{ $item->id }}')" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm flex items-center shadow-sm transition-colors">
                        <i class="fas fa-plus mr-2"></i> Thêm hạng mục
                    </button>
                </div>
            @endif
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-inner">
           
            
            <div class="space-y-3" id="test_items_container_show_{{ $item->id }}">
                @forelse($testDetails as $detail)
                    <div class="test-item flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors" data-detail-id="{{ $detail->id }}">
                        <div class="flex-grow">
                            <input type="text" 
                                   value="{{ $detail->test_item_name }}" 
                                   class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   @if($testing->status != 'in_progress') readonly @endif>
                        </div>
                        
                        @if($testing->status == 'in_progress')
                            <button type="button" 
                                    class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors" 
                                    onclick="removeTestItemForShow('{{ $detail->id }}', this)"
                                    title="Xóa hạng mục này">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-8">
                        <div class="mb-3">
                            <i class="fas fa-clipboard-list text-4xl text-blue-300"></i>
                        </div>
                        <p class="text-lg font-medium">Chưa có hạng mục kiểm thử nào</p>
                        <p class="text-sm text-gray-400 mt-1">Sử dụng các nút bên trên để thêm hạng mục kiểm thử</p>
                    </div>
                @endforelse
            </div>
        </div>
        
    </div>
     <!-- Ghi chú cho thành phẩm này -->
     <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú cho thành phẩm này:</label>
            <textarea name="item_notes[{{ $item->id }}]" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Ghi chú cho thành phẩm này">{{ $item->notes }}</textarea>
        </div>
        

    </div>
    
    @endif
    
    @endforeach
    
    @endif

    @if(!$isReadOnly)
    <div class="mt-8 flex justify-end">
        <button type="submit" class="test-item-submit-button px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center shadow-lg transition-colors">
            <i class="fas fa-save mr-2"></i> Lưu kết quả kiểm thử
        </button>
    </div>
    @endif


    </form>
    @endif
    @elseif(false)
    <!-- Read-only view for completed status -->
    <div class="space-y-6">
        @php
            $globalUnitCounter = 0; // Biến đếm toàn cục cho đơn vị thành phẩm
        @endphp
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
                </div>
            </div>

            <!-- Vật tư lắp ráp cho thành phẩm này (chỉ hiển thị cho finished_product) -->
            @if($item->item_type == 'finished_product' || ($item->item_type == 'product' && $testing->test_type == 'finished_product'))
            <div class="mb-4 border-t border-gray-200 pt-4">
                <h5 class="font-medium text-gray-800 mb-3">Kiểm thử vật tư lắp ráp <span class="text-sm text-gray-500">(# chỉ của loại thành phẩm này, không phải của toàn bộ phiếu)</span></h5>

                @php
                // Lấy product_id từ testing item
                $productIdForView = null;
                if ($item->item_type == 'finished_product') {
                $productIdForView = $item->good_id ?? null;
                } elseif ($item->item_type == 'product') {
                $productIdForView = $item->product_id ?? null;
                }

                // Debug: Log để kiểm tra
                // dd('item_type: ' . $item->item_type, 'good_id: ' . ($item->good_id ?? 'null'), 'product_id: ' . ($item->product_id ?? 'null'), 'productIdForView: ' . $productIdForView);
                $materialsByUnit = [];
                $productSerialsForUnits = [];
                if ($testing->assembly) {
                // Debug: Log để kiểm tra
                // dd($testing->assembly->products->toArray(), $productIdForView);

                $apForProduct = $testing->assembly->products ? $testing->assembly->products->firstWhere('product_id', $productIdForView) : null;

                // Nếu không tìm thấy, thử tìm theo tất cả products
                if (!$apForProduct && $testing->assembly->products) {
                $apForProduct = $testing->assembly->products->first();
                }

                if ($apForProduct) {
                // Lấy serial list theo từng đơn vị từ phiếu lắp ráp
                if (!empty($apForProduct->serials)) {
                    // Tách serial theo từng đơn vị thành phẩm - KHÔNG filter để giữ nguyên thứ tự và N/A
                    $allSerials = array_map('trim', explode(',', $apForProduct->serials));
                    $productSerialsForUnits = [];
                    
                    // Nếu có product_unit, sử dụng nó để map serial đúng với unit
                    $productUnits = $apForProduct->product_unit;
                    if (is_array($productUnits)) {
                        // Map serial theo product_unit
                        foreach ($allSerials as $index => $serial) {
                            if (isset($productUnits[$index])) {
                                $unitIdx = $productUnits[$index];
                                // Chỉ gán serial có giá trị (không phải N/A hoặc rỗng)
                                if (!empty($serial) && strtoupper($serial) !== 'N/A') {
                                    $productSerialsForUnits[$unitIdx] = $serial;
                                }
                            }
                        }
                    } else {
                        // Fallback: phân bổ serial theo thứ tự (bỏ qua N/A và rỗng)
                        $validSerials = array_filter($allSerials, function($s) {
                            return !empty($s) && strtoupper($s) !== 'N/A';
                        });
                        foreach (array_values($validSerials) as $index => $serial) {
                            $productSerialsForUnits[$index + 1] = $serial;
                        }
                    }
                }
                // Lấy tên thành phẩm để hiển thị trên header đơn vị
                $unitProductName = $apForProduct->product->name ?? ($apForProduct->product->code ?? 'Thành phẩm');
                }

                // Fallback: Nếu không tìm thấy từ assembly_products, lấy từ testing_items
                if (empty($productSerialsForUnits) && !empty($item->serial_number)) {
                    // Fallback: nếu không có assembly, dùng serial từ testing item
                    $allSerials = array_map('trim', explode(',', $item->serial_number));
                    $productSerialsForUnits = [];
                    
                    // Chỉ lấy serial có giá trị (không phải N/A hoặc rỗng)
                    $validSerials = array_filter($allSerials, function($s) {
                        return !empty($s) && strtoupper($s) !== 'N/A';
                    });
                    foreach (array_values($validSerials) as $index => $serial) {
                        $productSerialsForUnits[$index + 1] = $serial;
                    }
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
                // Tạo mapping chính xác giữa assembly material và testing item
    $testingMaterialMap = collect();
    foreach ($testing->items->where('item_type', 'material') as $testingItem) {
        if ($testingItem->material_id) {
            // Sử dụng item->id thay vì material_id để tránh ảnh hưởng chéo
            $testingMaterialMap->put($testingItem->id, $testingItem);
        }
    }
                @endphp

                @if(!empty($materialsByUnit))
                @foreach($materialsByUnit as $unitIdx => $unitMaterials)
                @php
                    $globalUnitCounter++; // Tăng biến đếm toàn cục
                    $displayUnitIndex = $globalUnitCounter; // Sử dụng biến đếm toàn cục
                @endphp
                <div class="mb-4 rounded-lg overflow-hidden border border-green-200">
                    <div class="bg-green-50 px-3 py-2 flex items-center justify-between border-b border-green-200">
                        <div class="text-sm text-green-800 font-medium">
                            <i class="fas fa-box-open mr-2"></i> Đơn vị thành phẩm {{ $displayUnitIndex }} - {{ $unitProductName ?? 'Thành phẩm' }} - Serial {{ isset($productSerialsForUnits[$unitIdx]) ? $productSerialsForUnits[$unitIdx] : 'N/A' }}
                        </div>
                        @php
                            // Đọc số liệu N/A theo đơn vị từ DB (bảng testing_item_unit_results)
                            $unitSummary = \Illuminate\Support\Facades\DB::table('testing_item_unit_results')
                                ->where('testing_id', $testing->id)
                                ->where('product_item_id', $item->id)
                                ->where('unit_index', (int)$unitIdx)
                                ->selectRaw('SUM(no_serial_pass_quantity) as pass, SUM(no_serial_fail_quantity) as fail')
                                ->first();
                            $naPass = (int)($unitSummary->pass ?? 0);
                            $naFail = (int)($unitSummary->fail ?? 0);
                        @endphp
                        <div class="text-xs text-green-700">
                            {{ count($unitMaterials) }} vật tư · N/A: Đạt {{ $naPass }}, Không đạt {{ $naFail }}
                        </div>
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
                                    // Tìm testing item dựa trên material_id và serial để tránh ảnh hưởng chéo
                                    $testingItemRow = null;
                                $serialsRow = $asmMaterial->serial ? array_values(array_filter(array_map('trim', explode(',', $asmMaterial->serial)))) : [];
                                
                                    // Tìm item có material_id và serial khớp
                                    foreach ($testing->items->where('item_type', 'material') as $testingItem) {
                                        if ($testingItem->material_id == $asmMaterial->material_id) {
                                            // Kiểm tra serial có khớp không
                                            if (!empty($testingItem->serial_number) && !empty($asmMaterial->serial)) {
                                                $itemSerials = array_values(array_filter(array_map('trim', explode(',', $testingItem->serial_number))));
                                                $asmSerials = array_values(array_filter(array_map('trim', explode(',', $asmMaterial->serial))));
                                                
                                                // So sánh serial arrays
                                                if (count(array_intersect($itemSerials, $asmSerials)) > 0) {
                                                    $testingItemRow = $testingItem;
                                                    break;
                                                }
                                } else {
                                                // Nếu không có serial, dùng item đầu tiên có material_id khớp
                                                $testingItemRow = $testingItem;
                                                break;
                                            }
                                        }
                                }
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
                                                <select name="serial_results[{{ $testingItemRow->id }}][{{ $label }}]" class="w-full h-8 border border-gray-300 rounded px-2 text-xs bg-white">
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
                @else
                <div class="text-center text-gray-500 py-4">Không có vật tư lắp ráp cho thành phẩm này</div>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    @if($testing->assembly)
    <!-- Thông tin phiếu lắp ráp liên quan -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-cogs text-blue-500 mr-2"></i>
            Thông tin lắp ráp liên quan
        </h2>

        <div class="flex items-center">
            <p class="text-sm text-gray-500 font-medium mr-2">Mã phiếu lắp ráp:</p>
            <a href="{{ route('assemblies.show', $testing->assembly->id) }}" class="text-blue-600 hover:underline font-semibold">
                {{ $testing->assembly->code }}
            </a>
        </div>
    </div>
    @endif


    <!-- Chi tiết kết quả kiểm thử -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 print:border-0 print:shadow-none">
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

            // Tính toán chặt chẽ theo thứ tự ưu tiên:
            // Ưu tiên cho Thành phẩm:
            // - Nếu là thành phẩm (product) và có serial_results: ĐẾM THEO serial_results
            // - Ngược lại: dùng pass_quantity/fail_quantity; nếu chưa có thì đếm từ serial_results; nếu vẫn chưa có thì dùng field result
            // Lý do: tránh trường hợp pass_quantity cũ sai lệch làm tổng bị 100% Đạt
            foreach($itemsToCount as $item) {
            $passQuantity = 0;
            $failQuantity = 0;

            // Thành phẩm có serial_results thì đếm theo serial_results trước
            if ($item->item_type === 'product' && !empty($item->serial_results)) {
            $sr = json_decode($item->serial_results, true);
            if (is_array($sr)) {
            foreach ($sr as $val) {
            if ($val === 'pass') $passQuantity++;
            elseif ($val === 'fail') $failQuantity++;
            }
            }
            } else {
            // Các trường hợp khác: ưu tiên pass/fail quantities
            $passQuantity = (int)($item->pass_quantity ?? 0);
            $failQuantity = (int)($item->fail_quantity ?? 0);

            if (($passQuantity + $failQuantity) === 0) {
            // Thử đếm từ serial_results (nếu có) — KHÔNG áp dụng cho thành phẩm
            if ($item->item_type !== 'product' && !empty($item->serial_results)) {
            $sr = json_decode($item->serial_results, true);
            if (is_array($sr)) {
            foreach ($sr as $val) {
            if ($val === 'pass') $passQuantity++;
            elseif ($val === 'fail') $failQuantity++;
            }
            }
            }
            }
            }

            if (($passQuantity + $failQuantity) === 0 && !empty($item->result) && in_array($item->result, ['pass','fail'])) {
            $passQuantity = $item->result === 'pass' ? 1 : 0;
            $failQuantity = $item->result === 'fail' ? 1 : 0;
            }

            $totalPassQuantity += $passQuantity;
            $totalFailQuantity += $failQuantity;
            $totalQuantity += ($passQuantity + $failQuantity);
            }

            // Nếu không có kết quả cụ thể, hiển thị thông tin tổng quan
            if ($totalQuantity == 0 && $testing->test_type == 'finished_product') {
            $totalQuantity = $testing->items->where('item_type', 'product')->sum('quantity') ?? 0;
            }

            $itemPassRate = ($totalQuantity > 0) ? round(($totalPassQuantity / $totalQuantity) * 100) : 0;
            $itemFailRate = ($totalQuantity > 0) ? round(($totalFailQuantity / $totalQuantity) * 100) : 0;

            // Tính toán thêm cho vật tư lắp ráp (nếu là finished_product)
            $assemblyMaterialsPass = 0;
            $assemblyMaterialsFail = 0;
            $assemblyMaterialsTotal = 0;

            if ($testing->test_type == 'finished_product' && $testing->assembly) {
            // Tổng slot kiểm thử của vật tư lắp ráp (serial + N/A)
            $assemblyMaterialsTotal = 0;
            foreach ($testing->assembly->materials as $assemblyMaterial) {
            $assemblyMaterialsTotal += (int) ($assemblyMaterial->quantity ?? 0);
            }

            // 1) Đếm từ dropdown serial
            $serialPass = 0; $serialFail = 0;
            foreach ($testing->items as $item) {
            if ($item->item_type == 'material' && !empty($item->serial_results)) {
            $serialResults = json_decode($item->serial_results, true);
            if (is_array($serialResults)) {
            foreach ($serialResults as $val) {
            if ($val === 'pass') $serialPass++;
            elseif ($val === 'fail') $serialFail++;
            }
            }
            }
            }

            // 2) Lấy số N/A đạt đã nhập theo từng đơn vị từ notes
            $naPassFromNotes = 0;
            if (!empty($testing->notes)) {
            $notesData = json_decode($testing->notes, true);
            if (is_array($notesData) && isset($notesData['no_serial_pass_quantity']) && is_array($notesData['no_serial_pass_quantity'])) {
            foreach ($notesData['no_serial_pass_quantity'] as $byItem) {
            if (is_array($byItem)) {
            foreach ($byItem as $v) { $naPassFromNotes += (int) $v; }
            } else {
            $naPassFromNotes += (int) $byItem;
            }
            }
            }
            }

            // 3) Tổng hợp: phần chưa nhập coi là Không đạt
            $assemblyMaterialsPass = min($assemblyMaterialsTotal, $serialPass + $naPassFromNotes);
            $assemblyMaterialsFail = max(0, $assemblyMaterialsTotal - $assemblyMaterialsPass);
            }
            @endphp

            {{-- Hiển thị kết quả thành phẩm --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                    <h4 class="font-medium text-green-800 mb-2">Số lượng {{ $itemLabel }} Đạt: {{ $totalPassQuantity }}</h4>
                    @if($totalQuantity > 0)
                    <p class="text-green-700">{{ $itemPassRate }}% của tổng số {{ $itemLabel }} kiểm thử</p>
                    @else
                    <p class="text-green-700">Chưa có kết quả kiểm thử cụ thể</p>
                    @endif
                </div>

                <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                    <h4 class="font-medium text-red-800 mb-2">Số lượng {{ $itemLabel }} Không Đạt: {{ $totalFailQuantity }}</h4>
                    @if($totalQuantity > 0)
                    <p class="text-red-700">{{ $itemFailRate }}% của tổng số {{ $itemLabel }} kiểm thử</p>
                    @else
                    <p class="text-red-700">Chưa có kết quả kiểm thử cụ thể</p>
                    @endif
                </div>
            </div>

            @if($totalQuantity > 0)
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 mb-6">
                <h4 class="font-medium text-gray-800 mb-2">Tổng quan:</h4>
                <p class="text-gray-700">Tổng số {{ $itemLabel }} kiểm thử: <strong>{{ $totalQuantity }}</strong></p>
            </div>
            @endif

            {{-- Hiển thị thêm kết quả vật tư lắp ráp (chỉ cho finished_product) --}}
            @if($testing->test_type == 'finished_product' && $testing->assembly && $assemblyMaterialsTotal > 0)
            <div class="mb-6">
                <h4 class="font-medium text-gray-800 mb-3">Kết quả vật tư lắp ráp</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <h5 class="font-medium text-blue-800 mb-2">Tổng số vật tư lắp ráp: {{ $assemblyMaterialsTotal }}</h5>
                        <p class="text-blue-700">Các vật tư này sẽ được xử lý riêng trong phiếu nhập kho</p>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                        <h5 class="font-medium text-green-800 mb-2">Vật tư đạt: {{ $assemblyMaterialsPass }}</h5>
                        @if($assemblyMaterialsTotal > 0)
                        <p class="text-green-700">{{ round(($assemblyMaterialsPass / $assemblyMaterialsTotal) * 100) }}% của tổng số vật tư lắp ráp</p>
                        @else
                        <p class="text-green-700">Chưa có kết quả kiểm thử</p>
                        @endif
                    </div>

                    <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                        <h5 class="font-medium text-red-800 mb-2">Vật tư không đạt: {{ $assemblyMaterialsFail }}</h5>
                        @if($assemblyMaterialsTotal > 0)
                        <p class="text-red-700">{{ round(($assemblyMaterialsFail / $assemblyMaterialsTotal) * 100) }}% của tổng số vật tư lắp ráp</p>
                        @else
                        <p class="text-red-700">Chưa có kết quả kiểm thử</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-6">
                <h4 class="font-medium text-blue-800 mb-2">Lưu ý về phân loại kho:</h4>
                @if($testing->test_type == 'finished_product')
                <p class="text-blue-700"><strong>Thành phẩm đạt:</strong> Sẽ được chuyển vào Kho thành phẩm đạt hoặc xuất đi dự án.</p>
                <p class="text-blue-700"><strong>Vật tư lắp ráp:</strong> Sẽ được chuyển vào Kho vật tư hư hỏng để xử lý riêng.</p>
                @else
                <p class="text-blue-700">Thiết bị được đánh giá "Đạt" sẽ được chuyển vào Kho thiết bị Đạt.</p>
                <p class="text-blue-700">Thiết bị được đánh giá "Không đạt" sẽ được chuyển vào Kho thiết bị Không đạt.</p>
                @endif
            </div>

            @if($testing->is_inventory_updated)
            <div class="bg-green-50 p-4 rounded-lg border border-green-100 mb-6">
                <h4 class="font-medium text-green-800 mb-2">Thông tin kho:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        @if($testing->test_type == 'finished_product')
                        @php
                        $assemblyPurpose = $testing->assembly ? $testing->assembly->purpose : null;
                        $projectName = 'Dự án';
                        $projectCode = '';
                        
                        // Lấy thông tin từ bảng Project thông qua relationship
                        if ($testing->assembly && $testing->assembly->project) {
                            $project = $testing->assembly->project;
                            $projectName = $project->project_name ?? 'Dự án';
                            $projectCode = $project->project_code ?? '';
                        }
                        
                        $projectLabel = trim(($projectCode ? ($projectCode . ' - ') : '') . $projectName);
                        @endphp
                        @if($assemblyPurpose == 'project')
                        <p class="text-sm font-medium text-green-700">Dự án cho Thành phẩm đạt:</p>
                        <p class="text-green-600">{{ $projectLabel }}</p>
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
                    <p>{{ $testing->assignedEmployee->name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500 mt-2">{{ $testing->created_at ? $testing->created_at->format('d/m/Y') : '' }}</p>
                </div>

                <div class="text-center">
                    <p class="font-medium">Người tiếp nhận kiểm thử</p>
                    <p>{{ $testing->receiverEmployee->name ?? 'N/A' }}</p>
                    @if($testing->received_at)
                    <p class="text-sm text-gray-500 mt-2">{{ $testing->received_at->format('d/m/Y') }}</p>
                    @endif
                </div>

                <div class="text-center">
                    <p class="font-medium">Chỉnh sửa lần cuối</p>
                    <p>{{ $testing->updated_at ? $testing->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action buttons -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 no-print">
        <div class="flex flex-wrap gap-3">
            @if($testing->status == 'pending')
            <form action="{{ route('testing.receive', $testing->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                    <i class="fas fa-clipboard-check mr-2"></i> Tiếp nhận phiếu
                </button>
            </form>
            @endif

            @if($testing->status == 'in_progress')
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
                <i class="fas fa-check-circle mr-2"></i>
                @if($testing->test_type == 'material')
                Đã cập nhật vào kho, tự động duyệt phiếu nhập kho và tạo phiếu chuyển kho
                @else
                Đã cập nhật vào kho và tự động duyệt phiếu nhập kho
                @endif
                <span class="ml-2">
                    @if($testing->test_type == 'finished_product')
                    @php
                    $assemblyPurpose = $testing->assembly ? $testing->assembly->purpose : null;
                    $projectName = 'Dự án';
                    $projectCode = '';
                    
                    // Lấy thông tin từ bảng Project thông qua relationship
                    if ($testing->assembly && $testing->assembly->project) {
                        $project = $testing->assembly->project;
                        $projectName = $project->project_name ?? 'Dự án';
                        $projectCode = $project->project_code ?? '';
                    }
                    
                    $projectLabel = trim(($projectCode ? ($projectCode . ' - ') : '') . $projectName);
                    @endphp
                    @if($assemblyPurpose == 'project')
                    (Dự án cho Thành phẩm đạt: {{ $projectLabel }},
                    Kho lưu Module Vật tư lắp ráp không đạt: {{ $testing->failWarehouse->name ?? 'N/A' }})
                    @else
                    (Kho lưu Thành phẩm đạt: {{ $testing->successWarehouse->name ?? 'N/A' }},
                    Kho lưu Module Vật tư lắp ráp không đạt: {{ $testing->failWarehouse->name ?? 'N/A' }})
                    @endif
                    @else
                    (Kho đạt: {{ $testing->successWarehouse->name ?? 'N/A' }},
                    Kho không đạt: {{ $testing->failWarehouse->name ?? 'N/A' }})
                    @endif
                </span>
                <span class="ml-2 px-2 py-0.5 bg-green-200 text-green-800 rounded-full text-xs">
                    {{ $testing->items->where('result', 'pass')->count() }} đạt /
                    {{ $testing->items->where('result', 'fail')->count() }} không đạt
                </span>
            </div>
            @endif

            @if($testing->status != 'in_progress' && $testing->status != 'completed' && !$testing->assembly_id)
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

                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        <div class="text-sm text-blue-700">
                            <p><strong>Lưu ý:</strong></p>
                            <ul class="list-disc list-inside mt-1 space-y-1">
                                <li>Phiếu nhập kho sẽ được tạo tự động và duyệt ngay lập tức khi bạn xác nhận.</li>
                                @if($testing->test_type == 'material')
                                <li>Phiếu chuyển kho sẽ được tạo tự động để ghi lại việc chuyển từ kho ban đầu sang kho đạt/không đạt.</li>
                                <li>Nếu chuyển về chính kho ban đầu thì sẽ không có phiếu chuyển kho nào được tạo.</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                <form action="{{ route('testing.update-inventory', $testing->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        @if($testing->test_type == 'finished_product')
                        @php
                        $assemblyPurpose = $testing->assembly ? $testing->assembly->purpose : null;
                        $projectCode = '';
                        $projectName = 'Dự án';
                        
                        // Debug: Log thông tin assembly và project
                        \Log::info('DEBUG: Assembly info in view', [
                            'assembly_id' => $testing->assembly ? $testing->assembly->id : 'null',
                            'assembly_purpose' => $assemblyPurpose,
                            'assembly_project_id' => $testing->assembly ? $testing->assembly->project_id : 'null',
                            'has_project_relationship' => $testing->assembly && $testing->assembly->project ? 'yes' : 'no'
                        ]);
                        
                        // Lấy thông tin từ bảng Project thông qua relationship
                        if ($testing->assembly && $testing->assembly->project) {
                            $project = $testing->assembly->project;
                            $projectName = $project->project_name ?? 'Dự án';
                            $projectCode = $project->project_code ?? '';
                            
                            \Log::info('DEBUG: Project info in view', [
                                'project_id' => $project->id,
                                'project_name' => $projectName,
                                'project_code' => $projectCode
                            ]);
                        } else {
                            \Log::warning('DEBUG: No project relationship found in view', [
                                'assembly_id' => $testing->assembly ? $testing->assembly->id : 'null',
                                'assembly_purpose' => $assemblyPurpose,
                                'assembly_project_id' => $testing->assembly ? $testing->assembly->project_id : 'null'
                            ]);
                        }
                        
                        $projectLabel = trim(($projectCode ? ($projectCode . ' - ') : '') . $projectName);
                        @endphp
                        @if($assemblyPurpose == 'project')
                        <label for="success_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Dự án cho Thành phẩm đạt (không thể chỉnh sửa)</label>
                        <input type="text" value="{{ $projectLabel }}" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-600" readonly>
                        <p class="mt-1 text-xs text-gray-500">Thành phẩm đạt sẽ được xuất trực tiếp tới dự án này.</p>
                        <input type="hidden" name="success_warehouse_id" value="project_export">
                        @else
                        <label for="success_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Kho lưu Thành phẩm đạt</label>
                        <select id="success_warehouse_id" name="success_warehouse_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                            <option value="">-- Chọn kho --</option>
                            @foreach(App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @endif
                        @else
                        <label for="success_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Kho lưu Vật tư / Hàng hoá đạt</label>
                        <select id="success_warehouse_id" name="success_warehouse_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                            <option value="">-- Chọn kho --</option>
                            @foreach(App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>

                    <div class="mb-4">
                        @if($testing->test_type == 'finished_product')
                        <label for="fail_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Kho lưu Module Vật tư lắp ráp không đạt</label>
                        @else
                        <label for="fail_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Kho lưu Vật tư / Hàng hoá không đạt</label>
                        @endif
                        <select id="fail_warehouse_id" name="fail_warehouse_id" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                            <option value="">-- Chọn kho --</option>
                            @foreach(App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeInventoryModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Hủy</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            <i class="fas fa-check mr-2"></i> Xác nhận và tự động duyệt
                        </button>
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

    <script>
        function addDefaultTestItemsForShow(itemId) {
            const container = document.getElementById('test_items_container_show_' + itemId);
            if (!container) return;
            
            const defaultItems = ['Kiểm tra ngoại quan', 'Kiểm tra kích thước', 'Kiểm tra chức năng', 'Kiểm tra an toàn'];
            
            // Thêm từng hạng mục mặc định vào database
            defaultItems.forEach((itemName, index) => {
                const formData = new FormData();
                formData.append('_token', document.querySelector('input[name="_token"]').value);
                formData.append('_method', 'PUT');
                formData.append('action', 'add_test_detail');
                formData.append('testing_id', '{{ $testing->id }}');
                formData.append('item_id', itemId);
                formData.append('test_item_name', itemName);

                fetch('{{ route("testing.update", $testing->id) }}', {
                    method: 'POST',
                    headers: {
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
                        newItemDiv.className = 'test-item flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors';
                        newItemDiv.setAttribute('data-detail-id', newDetailId);
                        newItemDiv.innerHTML = `
                            <div class="flex-grow">
                                <input type="text" 
                                       value="${itemName}" 
                                       class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                       @if($testing->status != 'in_progress') readonly @endif>
                            </div>
                            <button type="button" 
                                    class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors" 
                                    onclick="removeTestItemForShow('${newDetailId}', this)"
                                    title="Xóa hạng mục này">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                        container.appendChild(newItemDiv);
                        
                        console.log('Đã thêm hạng mục mặc định:', itemName, 'với ID:', newDetailId);
                    } else {
                        console.error('Lỗi khi thêm hạng mục mặc định:', itemName, data.message);
                    }
                })
                .catch(error => {
                    console.error('Lỗi khi thêm hạng mục mặc định:', itemName, error);
                });
            });
            
            console.log('Đã gửi yêu cầu thêm các hạng mục mặc định.');
        }

        function addTestItemForShow(itemId) {
            const input = document.getElementById('new_test_item_name_show_' + itemId);
            const name = input ? input.value.trim() : '';
            if (!name) {
                alert('Vui lòng nhập tên hạng mục kiểm thử.');
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('_method', 'PUT');
            formData.append('action', 'add_test_detail');
            formData.append('testing_id', '{{ $testing->id }}');
            formData.append('item_id', itemId);
            formData.append('test_item_name', name);

            fetch('{{ route("testing.update", $testing->id) }}', {
                method: 'POST',
                headers: {
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('test_items_container_show_' + itemId);
                    const newItemDiv = document.createElement('div');
                    newItemDiv.className = 'test-item flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors';
                    newItemDiv.setAttribute('data-detail-id', data.test_detail_id);
                    newItemDiv.innerHTML = `
                        <div class="flex-grow">
                            <input type="text" 
                                   value="${name}" 
                                   class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   @if($testing->status != 'in_progress') readonly @endif>
                        </div>
                        <button type="button" 
                                class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors" 
                                onclick="removeTestItemForShow('${data.test_detail_id}', this)"
                                title="Xóa hạng mục này">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    container.appendChild(newItemDiv);
                    
                    // Clear input field
                    if (input) input.value = '';
                    
                    console.log('Đã thêm hạng mục kiểm thử mới:', name, 'với ID:', data.test_detail_id);
                } else {
                    alert('Lỗi khi thêm hạng mục kiểm thử: ' + (data.message || 'Không xác định'));
                }
            })
            .catch(error => {
                console.error('Lỗi khi thêm hạng mục kiểm thử:', error);
                alert('Có lỗi xảy ra khi thêm hạng mục kiểm thử');
            });
        }

        function removeTestItemForShow(detailId, btn) {
            // Nếu là hạng mục tạm thời (chưa lưu vào database)
            if (detailId.startsWith('new_')) {
                btn.closest('.test-item').remove();
                return;
            }
            
            // Nếu là hạng mục đã lưu trong database
            if (!confirm('Bạn có chắc chắn muốn xóa hạng mục kiểm thử này?')) return;
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('_method', 'PUT');
            formData.append('action', 'delete_test_detail');
            formData.append('detail_id', detailId);
            
            fetch('{{ route("testing.update", $testing->id) }}', {
                method: 'POST',
                headers: {
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.closest('.test-item').remove();
                    console.log('Đã xóa hạng mục kiểm thử thành công');
                } else {
                    alert('Lỗi khi xóa hạng mục kiểm thử: ' + (data.message || 'Không xác định'));
                }
            })
            .catch(error => {
                console.error('Lỗi khi xóa hạng mục kiểm thử:', error);
                alert('Có lỗi xảy ra khi xóa hạng mục kiểm thử');
            });
        }

        function fixTestDetailsData(itemId) {
            if (!confirm('Bạn có chắc chắn muốn sửa dữ liệu hạng mục kiểm thử?')) return;
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('_method', 'PUT');
            formData.append('action', 'fix_test_details_data');
            formData.append('item_id', itemId);
            
            fetch('{{ route("testing.update", $testing->id) }}', {
                method: 'POST',
                headers: {
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã sửa dữ liệu thành công. Vui lòng refresh trang.');
                    location.reload();
                } else {
                    alert('Lỗi khi sửa dữ liệu: ' + (data.message || 'Không xác định'));
                }
            })
            .catch(error => {
                console.error('Lỗi khi sửa dữ liệu:', error);
                alert('Có lỗi xảy ra khi sửa dữ liệu');
            });
        }
    </script>

    <!-- JavaScript cho tự động lưu -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-save cho test-item-form
            const testItemForm = document.getElementById('test-item-form');
            if (testItemForm) {
                // Auto-save khi thay đổi kết quả tổng thể (chỉ cho vật tư, không cho thành phẩm)
                testItemForm.querySelectorAll('input[name^="item_pass_quantity"], input[name^="item_fail_quantity"]').forEach(function(input) {
                    // Chỉ áp dụng cho vật tư, không áp dụng cho thành phẩm (vì thành phẩm tự động tính)
                    if (!input.name.includes('product')) {
                        input.addEventListener('change', function() {
                            autoSaveTestResults();
                            // Cập nhật ngay lập tức phần "Chi tiết kết quả kiểm thử"
                            updateOverallResults();
                        });
                    }
                });

                // Auto-save khi thay đổi serial results
                testItemForm.querySelectorAll('select[name^="serial_results"]').forEach(function(select) {
                    select.addEventListener('change', function() {
                        autoSaveTestResults();
                    });
                });

                // Auto-save khi thay đổi test quantities
                testItemForm.querySelectorAll('input[name^="test_pass_quantity"], input[name^="test_fail_quantity"]').forEach(function(input) {
                    input.addEventListener('change', function() {
                        autoSaveTestResults();
                    });
                });

                // Auto-save khi thay đổi item notes
                testItemForm.querySelectorAll('textarea[name^="item_notes"]').forEach(function(textarea) {
                    textarea.addEventListener('input', function() {
                        autoSaveTestResults();
                    });
                });

                function autoSaveTestResults() {
                    const formData = new FormData();

                    // Chỉ gửi dữ liệu cần thiết cho auto-save
                    formData.append('_token', document.querySelector('input[name="_token"]').value);
                    formData.append('_method', 'PUT');

                    // Thêm item_pass_quantity và item_fail_quantity (chỉ cho vật tư)
                    const passQuantityInputs = testItemForm.querySelectorAll('input[name^="item_pass_quantity"]');
                    passQuantityInputs.forEach(input => {
                        // Chỉ gửi dữ liệu cho vật tư, không gửi cho thành phẩm
                        if (!input.name.includes('product')) {
                            formData.append(input.name, input.value);
                        }
                    });

                    const failQuantityInputs = testItemForm.querySelectorAll('input[name^="item_fail_quantity"]');
                    failQuantityInputs.forEach(input => {
                        // Chỉ gửi dữ liệu cho vật tư, không gửi cho thành phẩm
                        if (!input.name.includes('product')) {
                            formData.append(input.name, input.value);
                        }
                    });

                    // Thêm serial_results
                    const serialResults = testItemForm.querySelectorAll('select[name^="serial_results"]');
                    serialResults.forEach(select => {
                        formData.append(select.name, select.value);
                    });

                    // Thêm test_pass_quantity và test_fail_quantity
                    const testPassQuantityInputs = testItemForm.querySelectorAll('input[name^="test_pass_quantity"]');
                    testPassQuantityInputs.forEach(input => {
                        formData.append(input.name, input.value);
                    });

                    const testFailQuantityInputs = testItemForm.querySelectorAll('input[name^="test_fail_quantity"]');
                    testFailQuantityInputs.forEach(input => {
                        formData.append(input.name, input.value);
                    });

                    // Thêm item_notes
                    const itemNotesTextareas = testItemForm.querySelectorAll('textarea[name^="item_notes"]');
                    itemNotesTextareas.forEach(textarea => {
                        formData.append(textarea.name, textarea.value);
                    });

                    fetch(testItemForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification('Đã lưu kết quả kiểm thử', 'success');
                                // Tự động cập nhật phần "Chi tiết kết quả kiểm thử"
                                updateOverallResults();
                            } else {
                                showNotification('Có lỗi khi lưu kết quả', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Có lỗi khi lưu kết quả', 'error');
                        });
                }

                function updateOverallResults() {
                    // Tính toán lại kết quả tổng thể dựa trên dữ liệu hiện tại
                    let totalPassQuantity = 0;
                    let totalFailQuantity = 0;
                    let totalQuantity = 0;

                    // Lấy tất cả input pass_quantity và fail_quantity (chỉ cho vật tư)
                    const passQuantityInputs = testItemForm.querySelectorAll('input[name^="item_pass_quantity"]');
                    const failQuantityInputs = testItemForm.querySelectorAll('input[name^="item_fail_quantity"]');

                    // Tính toán tổng số lượng (chỉ cho vật tư)
                    passQuantityInputs.forEach((input, index) => {
                        // Chỉ tính cho vật tư, không tính cho thành phẩm
                        if (!input.name.includes('product')) {
                            const passQuantity = parseInt(input.value) || 0;
                            const failQuantity = parseInt(failQuantityInputs[index]?.value) || 0;

                            totalPassQuantity += passQuantity;
                            totalFailQuantity += failQuantity;
                            totalQuantity += (passQuantity + failQuantity);
                        }
                    });

                    // Cập nhật hiển thị
                    const passItemsElement = document.querySelector('.bg-green-50 h4');
                    const failItemsElement = document.querySelector('.bg-red-50 h4');
                    const passRateElement = document.querySelector('.bg-green-50 p');
                    const failRateElement = document.querySelector('.bg-red-50 p');

                    if (passItemsElement) {
                        passItemsElement.textContent = `Số lượng thiết bị Đạt: ${totalPassQuantity}`;
                    }
                    if (failItemsElement) {
                        failItemsElement.textContent = `Số lượng thiết bị Không Đạt: ${totalFailQuantity}`;
                    }

                    const passRate = totalQuantity > 0 ? Math.round((totalPassQuantity / totalQuantity) * 100) : 0;
                    const failRate = totalQuantity > 0 ? Math.round((totalFailQuantity / totalQuantity) * 100) : 0;

                    if (passRateElement) {
                        passRateElement.textContent = `${passRate}% của tổng số thiết bị kiểm thử`;
                    }
                    if (failRateElement) {
                        failRateElement.textContent = `${failRate}% của tổng số thiết bị kiểm thử`;
                    }
                }

                // Function để lưu kết quả cho từng thành phẩm
                window.saveProductResult = function(productId) {
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('input[name="_token"]').value);
                    formData.append('_method', 'PUT');

                    // Lấy dữ liệu kết quả của thành phẩm này (chỉ serial_results, không có pass/fail quantity vì tự động tính)
                    const serialResults = document.querySelectorAll(`select[name^="serial_results[${productId}]"]`);
                    const itemNotes = document.querySelector(`textarea[name="item_notes[${productId}]"]`);

                    // Không cần lưu pass/fail quantity cho thành phẩm vì tự động tính từ vật tư lắp ráp
                    if (serialResults.length > 0) {
                        serialResults.forEach(select => {
                            formData.append(select.name, select.value);
                        });
                    }
                    if (itemNotes) {
                        formData.append(itemNotes.name, itemNotes.value);
                    }

                    // Gửi request
                    fetch(`{{ route('testing.update', $testing->id) }}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Đã lưu kết quả thành phẩm thành công!', 'success');
                        } else {
                            showNotification('Có lỗi khi lưu kết quả: ' + (data.message || 'Lỗi không xác định'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Có lỗi khi lưu kết quả', 'error');
                    });
                }
            }

            function showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white text-sm z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
                notification.textContent = message;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        });

        // Thêm hiệu ứng cho dropdown vật tư trống Serial
        document.addEventListener('DOMContentLoaded', function() {
            const noSerialSelects = document.querySelectorAll('select.bg-yellow-50');
            
            noSerialSelects.forEach(select => {
                select.addEventListener('change', function() {
                    // Thêm hiệu ứng khi thay đổi giá trị
                    if (this.value === 'pass') {
                        this.classList.add('border-green-400', 'bg-green-50');
                        this.classList.remove('border-yellow-300', 'bg-yellow-50', 'border-red-400', 'bg-red-50');
                    } else if (this.value === 'fail') {
                        this.classList.add('border-red-400', 'bg-red-50');
                        this.classList.remove('border-yellow-300', 'bg-yellow-50', 'border-green-400', 'bg-green-50');
                    } else {
                        this.classList.add('border-yellow-300', 'bg-yellow-50');
                        this.classList.remove('border-green-400', 'bg-green-50', 'border-red-400', 'bg-red-50');
                    }
                });
                
                // Áp dụng màu sắc ban đầu
                select.dispatchEvent(new Event('change'));
            });
        });
    </script>
</body>

</html>