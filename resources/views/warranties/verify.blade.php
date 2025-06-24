<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực bảo hành - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm py-4 px-6">
            <div class="max-w-5xl mx-auto flex justify-between items-center">
                <div class="flex items-center">
                    <span class="ml-3 text-xl font-bold text-gray-800">Hệ thống xác thực bảo hành</span>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-grow py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                @if($warranty)
                    @if($warranty->status == 'active' && $warranty->warranty_end_date > now())
                        <!-- Verified Badge -->
                        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-4">
                                <i class="fas fa-check-circle text-5xl text-green-500"></i>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">Thiết bị đang được bảo hành</h1>
                            <p class="text-gray-600">Thiết bị <strong>{{ $warranty->item_name ?? 'N/A' }}</strong> đang trong thời gian bảo hành</p>
                            <div class="mt-4 inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full">
                                <div class="flex items-center">
                                    <i class="fas fa-shield-alt mr-2"></i>
                                    <span>Còn hiệu lực đến ngày {{ $warranty->warranty_end_date->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                    @elseif($warranty->status == 'expired' || $warranty->warranty_end_date <= now())
                        <!-- Expired Badge -->
                        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full mb-4">
                                <i class="fas fa-times-circle text-5xl text-red-500"></i>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">Bảo hành đã hết hạn</h1>
                            <p class="text-gray-600">Thiết bị <strong>{{ $warranty->item_name ?? 'N/A' }}</strong> đã hết thời gian bảo hành</p>
                            <div class="mt-4 inline-block bg-red-100 text-red-800 px-4 py-2 rounded-full">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <span>Đã hết hạn từ ngày {{ $warranty->warranty_end_date->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Other Status Badge -->
                        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 bg-yellow-100 rounded-full mb-4">
                                <i class="fas fa-info-circle text-5xl text-yellow-500"></i>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">Trạng thái bảo hành: {{ $warranty->status_label }}</h1>
                            <p class="text-gray-600">Thiết bị <strong>{{ $warranty->item_name ?? 'N/A' }}</strong></p>
                            <div class="mt-4 inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full">
                                <div class="flex items-center">
                                    <i class="fas fa-info mr-2"></i>
                                    <span>Trạng thái: {{ $warranty->status_label }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Device Information -->
                    <div class="bg-white rounded-lg shadow-lg mb-6 overflow-hidden">
                        <div class="px-6 py-4 bg-blue-600">
                            <h2 class="text-lg font-semibold text-white">Thông tin thiết bị</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Mã bảo hành</p>
                                            <p class="text-gray-900 font-medium">{{ $warranty->warranty_code }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Tên thiết bị</p>
                                            <p class="text-gray-900 font-medium">{{ $warranty->item_name ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Mã thiết bị</p>
                                            <p class="text-gray-900">{{ $warranty->item_code ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Serial</p>
                                            <p class="text-gray-900">{{ $warranty->serial_number ?: 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Ngày mua</p>
                                            <p class="text-gray-900">{{ $warranty->purchase_date ? $warranty->purchase_date->format('d/m/Y') : 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Tên khách hàng</p>
                                            <p class="text-gray-900">{{ $warranty->customer_name ?: 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Dự án</p>
                                            <p class="text-gray-900">{{ $warranty->project_name ?: 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Warranty Information -->
                    <div class="bg-white rounded-lg shadow-lg mb-6 overflow-hidden">
                        <div class="px-6 py-4 bg-green-600">
                            <h2 class="text-lg font-semibold text-white">Thông tin bảo hành</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Gói bảo hành</p>
                                            <p class="text-gray-900 font-medium">{{ ucfirst($warranty->warranty_type) }} - {{ $warranty->warranty_period_months }} tháng</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Ngày kích hoạt</p>
                                            <p class="text-gray-900">{{ $warranty->warranty_start_date ? $warranty->warranty_start_date->format('d/m/Y') : 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Ngày hết hạn</p>
                                            <p class="text-gray-900">{{ $warranty->warranty_end_date ? $warranty->warranty_end_date->format('d/m/Y') : 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Trạng thái</p>
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $warranty->status_color ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $warranty->status_label }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Thời gian còn lại</p>
                                            <p class="text-gray-900">
                                                @if($warranty->remaining_time === null)
                                                    <span class="text-orange-600">Chưa kích hoạt</span>
                                                @elseif($warranty->is_active && $warranty->remaining_time !== 0)
                                                    <span class="text-green-600">{{ $warranty->remaining_time }}</span>
                                                @elseif($warranty->is_active && $warranty->remaining_time === 0)
                                                    <span class="text-red-600">Hết hạn</span>
                                                @else
                                                    Không áp dụng
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($warranty->warrantyProducts && count($warranty->warrantyProducts) > 0)
                        <!-- Products Information -->
                        <div class="bg-white rounded-lg shadow-lg mb-6 overflow-hidden">
                            <div class="px-6 py-4 bg-purple-600">
                                <h2 class="text-lg font-semibold text-white">Danh sách thiết bị trong bảo hành</h2>
                            </div>
                            <div class="p-6">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Mã thiết bị
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Tên thiết bị
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Số lượng
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Serial Numbers
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($warranty->warrantyProducts as $product)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product['product_code'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $product['product_name'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $product['quantity'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $product['serial_numbers_text'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($warranty->productMaterials && count($warranty->productMaterials) > 0)
                        <!-- Modules/Materials Information -->
                        <div class="bg-white rounded-lg shadow-lg mb-6 overflow-hidden">
                            <div class="px-6 py-4 bg-indigo-600">
                                <h2 class="text-lg font-semibold text-white">Chi tiết vật tư/module trong thiết bị</h2>
                            </div>
                            <div class="p-6">
                                @foreach($warranty->productMaterials as $productMaterial)
                                    <div class="mb-6 last:mb-0">
                                        <h3 class="text-lg font-medium text-gray-900 mb-3">
                                            {{ $productMaterial['product_name'] }} ({{ $productMaterial['product_code'] }})
                                            @if($productMaterial['serial_number'] !== 'N/A')
                                                - Serial: {{ $productMaterial['serial_number'] }}
                                            @endif
                                        </h3>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col"
                                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Mã vật tư
                                                        </th>
                                                        <th scope="col"
                                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Tên vật tư
                                                        </th>
                                                        <th scope="col"
                                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Số lượng
                                                        </th>
                                                        <th scope="col"
                                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Serial
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($productMaterial['materials'] as $material)
                                                        <tr>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $material['code'] }}</td>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $material['name'] }}</td>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $material['quantity'] }}</td>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $material['serial'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Not Found Badge -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6 text-center">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full mb-4">
                            <i class="fas fa-exclamation-triangle text-5xl text-red-500"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Không tìm thấy thông tin bảo hành</h1>
                        <p class="text-gray-600">{{ $message ?? 'Mã bảo hành không hợp lệ hoặc không tồn tại trong hệ thống.' }}</p>
                        <div class="mt-6">
                            <p class="text-sm text-gray-500">Vui lòng kiểm tra lại mã bảo hành hoặc liên hệ với bộ phận hỗ trợ kỹ thuật</p>
                        </div>
                    </div>
                @endif
            </div>
        </main>
    </div>
</body>

</html> 