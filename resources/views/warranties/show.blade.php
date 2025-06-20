<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chi tiết bảo hành - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chi tiết bảo hành</h1>
            </div>
            <div class="flex space-x-2">
                <button onclick="generateQR()"
                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-qrcode mr-2"></i> Tạo QR Code
                </button>
                <button id="export-pdf-btn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    Hủy phiếu
                </button>
                <a href="{{ route('warranties.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>
        </header>

        <main class="p-6">
            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">
                                    @if($warranty->item_type === 'project')
                                        {{ $warranty->item->name ?? 'Bảo hành dự án' }}
                                    @elseif($warranty->item)
                                        {{ $warranty->item->name ?? 'Thiết bị không xác định' }}
                                    @else
                                        Thiết bị {{ ucfirst($warranty->item_type) }} ID: {{ $warranty->item_id }}
                                    @endif
                                </h2>
                                <div class="flex flex-wrap items-center mt-1 gap-2">
                                    <p class="text-gray-600">Mã bảo hành: <span class="font-medium">{{ $warranty->warranty_code }}</span></p>
                                    @if($warranty->item_type === 'project')
                                        <span class="mx-2 text-gray-300">|</span>
                                        <p class="text-gray-600">Số thiết bị: <span class="font-medium">{{ count($warranty->project_items) }}</span></p>
                                    @endif
                                    @if($warranty->serial_number)
                                    <span class="mx-2 text-gray-300">|</span>
                                    <p class="text-gray-600">Serial: <span class="font-medium">{{ Str::limit($warranty->serial_number, 50) }}</span></p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end">
                                <div class="flex items-center">
                                    @php
                                        $statusColors = [
                                            'active' => 'bg-green-100 text-green-800',
                                            'expired' => 'bg-red-100 text-red-800',
                                            'claimed' => 'bg-yellow-100 text-yellow-800',
                                            'void' => 'bg-gray-100 text-gray-800'
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$warranty->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $warranty->status_label }}
                                    </span>
                                </div>
                                <div class="flex items-center mt-2">
                                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                    <span class="text-blue-700">
                                        {{ $warranty->warranty_start_date->format('d/m/Y') }} - {{ $warranty->warranty_end_date->format('d/m/Y') }}
                                    </span>
                                </div>
                                @if($warranty->is_active)
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-clock text-green-500 mr-2"></i>
                                    <span class="text-green-600 text-sm">Còn {{ $warranty->remaining_days }} ngày</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Thông tin thiết bị -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-microchip mr-2 text-blue-500"></i>
                            Thông tin thiết bị
                        </h3>
                        
                        @if($warranty->item_type === 'project')
                        <!-- Project-wide warranty - show all items -->
                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-6 mb-4">
                                <div>
                                    <p class="text-sm text-gray-500">Loại bảo hành</p>
                                    <p class="text-gray-900 font-medium">Bảo hành cấp dự án</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Ngày mua hàng</p>
                                    <p class="text-gray-900">{{ $warranty->purchase_date->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Thời gian bảo hành</p>
                                    <p class="text-gray-900">{{ $warranty->warranty_period_months }} tháng</p>
                                </div>
                            </div>
                            
                            @if(!empty($warranty->project_items))
                            <div class="border-t border-gray-200 pt-4">
                                <p class="text-sm text-gray-500 mb-3">Danh sách thiết bị trong bảo hành</p>
                                <div class="space-y-3">
                                    @foreach($warranty->project_items as $item)
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div>
                                                <p class="text-xs text-gray-500">Mã thiết bị</p>
                                                <p class="text-gray-900 font-medium">{{ $item['code'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Tên thiết bị</p>
                                                <p class="text-gray-900">{{ $item['name'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Số lượng</p>
                                                <p class="text-gray-900">{{ $item['quantity'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Serial Numbers</p>
                                                <p class="text-gray-900 text-sm">
                                                    @if(!empty($item['serial_numbers']) && is_array($item['serial_numbers']))
                                                        {{ implode(', ', $item['serial_numbers']) }}
                                                    @else
                                                        Chưa có
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            @if(!empty($warranty->product_materials))
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-sm text-gray-500">Chi tiết vật tư/linh kiện trong thành phẩm</p>
                                    <button type="button" id="toggle-materials-btn" 
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium focus:outline-none">
                                        <i class="fas fa-chevron-down mr-1"></i> Hiển thị vật tư
                                    </button>
                                </div>
                                
                                <div id="materials-section" class="hidden space-y-4">
                                    @foreach($warranty->product_materials as $productMaterial)
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h5 class="font-medium text-gray-900">
                                                {{ $productMaterial['product_code'] }} - {{ $productMaterial['product_name'] }}
                                            </h5>
                                            @if($productMaterial['serial_number'] !== 'N/A')
                                            <span class="text-sm text-blue-600 bg-blue-100 px-2 py-1 rounded">
                                                Serial: {{ $productMaterial['serial_number'] }}
                                            </span>
                                            @endif
                                        </div>
                                        
                                        @if(!empty($productMaterial['materials']))
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach($productMaterial['materials'] as $material)
                                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                                <p class="text-xs text-gray-500">Mã vật tư</p>
                                                <p class="font-medium text-gray-900 text-sm">{{ $material['code'] }}</p>
                                                
                                                <p class="text-xs text-gray-500 mt-2">Tên vật tư</p>
                                                <p class="text-gray-800 text-sm">{{ $material['name'] }}</p>
                                                
                                                <div class="grid grid-cols-2 gap-2 mt-2">
                                                    <div>
                                                        <p class="text-xs text-gray-500">Số lượng</p>
                                                        <p class="text-gray-800 text-sm">{{ $material['quantity'] }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-gray-500">Serial</p>
                                                        <p class="text-gray-800 text-sm">{{ $material['serial'] }}</p>
                                                    </div>
                                                </div>
                                                
                                                @if($material['assembly_code'] !== 'N/A')
                                                <div class="mt-2">
                                                    <p class="text-xs text-gray-500">Mã lắp ráp</p>
                                                    <p class="text-blue-600 text-sm">{{ $material['assembly_code'] }}</p>
                                                </div>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                        @else
                                        <p class="text-gray-500 text-sm italic">Không có thông tin vật tư cho serial này</p>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            @if($warranty->serial_number)
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <p class="text-sm text-gray-500">Tất cả Serial Numbers</p>
                                <p class="text-gray-900 font-medium">{{ $warranty->serial_number }}</p>
                            </div>
                            @endif
                        </div>
                        @else
                        <!-- Single item warranty -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-y-4 gap-x-6 mb-6">
                            <div>
                                <p class="text-sm text-gray-500">Tên thiết bị</p>
                                <p class="text-gray-900 font-medium">
                                    @if($warranty->item)
                                        {{ $warranty->item->name ?? 'Không xác định' }}
                                    @else
                                        {{ ucfirst($warranty->item_type) }} ID: {{ $warranty->item_id }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Mã thiết bị</p>
                                <p class="text-gray-900 font-medium">
                                    @if($warranty->item && isset($warranty->item->code))
                                        {{ $warranty->item->code }}
                                    @else
                                        {{ $warranty->warranty_code }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Serial Number</p>
                                <p class="text-gray-900 font-medium">{{ $warranty->serial_number ?? 'Chưa có' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày mua hàng</p>
                                <p class="text-gray-900">{{ $warranty->purchase_date->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Loại bảo hành</p>
                                <p class="text-gray-900">
                                    @switch($warranty->warranty_type)
                                        @case('standard')
                                            Tiêu chuẩn
                                            @break
                                        @case('extended')
                                            Mở rộng
                                            @break
                                        @case('premium')
                                            Cao cấp
                                            @break
                                        @default
                                            {{ ucfirst($warranty->warranty_type) }}
                                    @endswitch
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Thời gian bảo hành</p>
                                <p class="text-gray-900">{{ $warranty->warranty_period_months }} tháng</p>
                            </div>
                        </div>
                        @endif

                        @if($warranty->dispatch)
                        <!-- Thông tin phiếu xuất -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Thông tin phiếu xuất</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Mã phiếu xuất</p>
                                        <p class="text-gray-900 font-medium">{{ $warranty->dispatch->dispatch_code }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Ngày xuất</p>
                                        <p class="text-gray-900">{{ $warranty->dispatch->dispatch_date->format('d/m/Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Loại xuất</p>
                                        <p class="text-gray-900">{{ ucfirst($warranty->dispatch->dispatch_type) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Trạng thái phiếu xuất</p>
                                        <p class="text-gray-900">{{ ucfirst($warranty->dispatch->status) }}</p>
                                    </div>
                                </div>
                                @if($warranty->dispatch->dispatch_note)
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500">Ghi chú phiếu xuất</p>
                                    <p class="text-gray-900">{{ $warranty->dispatch->dispatch_note }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Thông tin khách hàng và dịch vụ -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-500"></i>
                            Thông tin khách hàng
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Tên khách hàng</p>
                                <p class="text-gray-900 font-medium">{{ $warranty->customer_name }}</p>
                            </div>
                            @if($warranty->customer_phone)
                            <div>
                                <p class="text-sm text-gray-500">Số điện thoại</p>
                                <p class="text-gray-900">{{ $warranty->customer_phone }}</p>
                            </div>
                            @endif
                            @if($warranty->customer_email)
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="text-gray-900">{{ $warranty->customer_email }}</p>
                            </div>
                            @endif
                            @if($warranty->customer_address)
                            <div>
                                <p class="text-sm text-gray-500">Địa chỉ</p>
                                <p class="text-gray-900">{{ $warranty->customer_address }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-shield-alt mr-2 text-blue-500"></i>
                            Thông tin bảo hành
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Gói bảo hành</p>
                                <p class="text-gray-900 font-medium">
                                    @switch($warranty->warranty_type)
                                        @case('standard')
                                            Tiêu chuẩn ({{ $warranty->warranty_period_months }} tháng)
                                            @break
                                        @case('extended')
                                            Mở rộng ({{ $warranty->warranty_period_months }} tháng)
                                            @break
                                        @case('premium')
                                            Cao cấp ({{ $warranty->warranty_period_months }} tháng)
                                            @break
                                        @default
                                            {{ ucfirst($warranty->warranty_type) }} ({{ $warranty->warranty_period_months }} tháng)
                                    @endswitch
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày kích hoạt</p>
                                <p class="text-gray-900">{{ $warranty->warranty_start_date->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ngày hết hạn</p>
                                <p class="text-gray-900">{{ $warranty->warranty_end_date->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Trạng thái</p>
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'expired' => 'bg-red-100 text-red-800',
                                        'claimed' => 'bg-yellow-100 text-yellow-800',
                                        'void' => 'bg-gray-100 text-gray-800'
                                    ];
                                @endphp
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$warranty->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $warranty->status_label }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Dự án</p>
                                <p class="text-gray-900">{{ $warranty->project_name }}</p>
                            </div>
                            @if($warranty->is_active)
                            <div>
                                <p class="text-sm text-gray-500">Thời gian còn lại</p>
                                <p class="text-green-600 font-medium">{{ $warranty->remaining_days }} ngày</p>
                            </div>
                            @endif
                        </div>

                        <div class="mt-6 text-center">
                            <button id="warranty-qr-btn"
                                class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg flex items-center justify-center w-full transition-colors">
                                <i class="fas fa-qrcode mr-2"></i> Hiển thị QR Code bảo hành
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- QR Code Modal -->
    <div id="qr-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">QR Code bảo hành</h3>
                <button type="button" onclick="closeQrModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="text-center mb-4">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($warranty->warranty_code) }}" alt="QR Code" class="mx-auto border rounded">
                <p class="text-sm font-medium text-gray-800 mt-2">{{ $warranty->warranty_code }}</p>
                <p class="text-sm text-gray-600">Quét mã QR này để kiểm tra thông tin bảo hành thiết bị</p>
            </div>
            <div class="flex justify-between space-x-3">
                <button type="button"
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex-1">
                    <i class="fas fa-download mr-2"></i> Tải xuống
                </button>
                <button type="button"
                    class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors flex-1">
                    <i class="fas fa-print mr-2"></i> In mã QR
                </button>
            </div>
        </div>
    </div>

    <script>
        function generateQR() {
            document.getElementById('qr-modal').classList.remove('hidden');
        }

        function closeQrModal() {
            document.getElementById('qr-modal').classList.add('hidden');
        }

        document.getElementById('warranty-qr-btn').addEventListener('click', function() {
            generateQR();
        });

        // Toggle materials section
        const toggleMaterialsBtn = document.getElementById('toggle-materials-btn');
        const materialsSection = document.getElementById('materials-section');
        
        if (toggleMaterialsBtn && materialsSection) {
            toggleMaterialsBtn.addEventListener('click', function() {
                const isHidden = materialsSection.classList.contains('hidden');
                
                if (isHidden) {
                    materialsSection.classList.remove('hidden');
                    this.innerHTML = '<i class="fas fa-chevron-up mr-1"></i> Ẩn vật tư';
                } else {
                    materialsSection.classList.add('hidden');
                    this.innerHTML = '<i class="fas fa-chevron-down mr-1"></i> Hiển thị vật tư';
                }
            });
        }
    </script>
</body>

</html> 