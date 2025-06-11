<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phiếu lắp ráp - SGL</title>
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
                <a href="{{ route('assemblies.index') }}" class="text-gray-600 hover:text-blue-500 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phiếu lắp ráp</h1>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('assemblies.edit', $assembly->id) }}">
                    <button
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                    </button>
                </a>
                <button type="button" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Xóa
                </button>
                <button id="print-btn"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-print mr-2"></i> In phiếu
                </button>
            </div>
        </header>

        <main class="p-6">
            <!-- Header Info -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <div class="flex flex-col lg:flex-row justify-between gap-4">
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-lg font-semibold text-gray-800 mr-2">Mã phiếu lắp ráp:</span>
                            <span class="text-lg text-blue-600 font-bold">{{ $assembly->code }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Ngày lắp ráp:</span>
                            <span class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($assembly->date)->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">thành phẩm:</span>
                            <span class="text-sm text-gray-700">{{ $assembly->product->name }}</span>
                        </div>
                        <div class="flex items-center mt-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Số lượng:</span>
                            <span class="text-sm font-semibold text-gray-700">{{ $assembly->quantity ?? '1' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Thành phẩm:</span>
                            <div class="flex flex-col">
                                @if(isset($assembly->products) && count($assembly->products) > 0)
                                    @foreach($assembly->products as $product)
                                        <span class="text-sm text-gray-700">{{ $product->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-sm text-gray-700">{{ $assembly->product->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Người phụ trách:</span>
                            <span class="text-sm text-gray-700">{{ $assembly->assigned_to }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Người tiếp nhận kiểm thử:</span>
                            <span class="text-sm text-gray-700">{{ $assembly->tester_id ?? 'Chưa phân công' }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Kho xuất linh kiện:</span>
                            <span class="text-sm text-gray-700">{{ $assembly->warehouse->name }}
                                ({{ $assembly->warehouse->code }})</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Kho nhập thành phẩm:</span>
                            <span class="text-sm text-gray-700">{{ $assembly->targetWarehouse->name ?? '' }}
                                ({{ $assembly->targetWarehouse->code ?? '' }})</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-700 mr-2">Trạng thái:</span>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($assembly->status == 'completed') bg-green-100 text-green-800
                                @elseif($assembly->status == 'in_progress') bg-yellow-100 text-yellow-800
                                @elseif($assembly->status == 'pending') bg-blue-100 text-blue-800
                                @else bg-red-100 text-red-800 @endif">
                                @if($assembly->status == 'completed')
                                    Hoàn thành
                                @elseif($assembly->status == 'in_progress')
                                    Đang thực hiện
                                @elseif($assembly->status == 'pending')
                                    Chờ xử lý
                                @else
                                    Đã hủy
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center mt-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Mục đích:</span>
                            <span class="text-sm text-gray-700">
                                {{-- @if($assembly->purpose == 'storage')
                                    Lưu kho
                                @elseif($assembly->purpose == 'project')
                                    Xuất đi dự án
                                @else
                                    {{ $assembly->purpose ?? 'Không xác định' }}
                                @endif --}}
                                Lưu kho
                            </span>
                        </div>
                        @if($assembly->purpose == 'project' && isset($assembly->project))
                        <div class="flex items-center mt-2">
                            <span class="text-sm font-medium text-gray-700 mr-2">Dự án:</span>
                            <span class="text-sm text-gray-700">{{ $assembly->project->name ?? '' }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                @if($assembly->product_serials)
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Serial thành phẩm:</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        @foreach(explode(',', $assembly->product_serials) as $serial)
                            @if(!empty($serial))
                            <div class="bg-gray-50 rounded-lg p-2 text-sm">
                                <span class="font-medium">{{ $loop->iteration }}.</span> {{ $serial }}
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                @if($assembly->notes)
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <div class="flex items-center">
                        <span class="text-sm font-medium text-gray-700 mr-2">Ghi chú:</span>
                        <span class="text-sm text-gray-700">{{ $assembly->notes }}</span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Component List -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-microchip text-blue-500 mr-2"></i>
                    Danh sách linh kiện
                </h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    STT
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mã
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Loại linh kiện
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tên vật tư
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Số lượng
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Serial
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ghi chú
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assembly->materials as $index => $material)
                                <tr class="bg-white hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $material->material->code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $material->material->category }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $material->material->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">
                                        {{ $material->quantity }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
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
                                            {{ $material->serial }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $material->note }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex flex-wrap gap-3 justify-end">
                <button id="export-excel-btn"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Xuất Excel
                </button>
                <button id="export-pdf-btn"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Xuất PDF
                </button>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý sự kiện in phiếu
            const printBtn = document.getElementById('print-btn');
            printBtn.addEventListener('click', function() {
                window.print();
            });

            // Xử lý sự kiện xuất Excel
            const exportExcelBtn = document.getElementById('export-excel-btn');
            exportExcelBtn.addEventListener('click', function() {
                alert('Tính năng xuất Excel đang được phát triển!');
            });

            // Xử lý sự kiện xuất PDF
            const exportPdfBtn = document.getElementById('export-pdf-btn');
            exportPdfBtn.addEventListener('click', function() {
                alert('Tính năng xuất PDF đang được phát triển!');
            });
        });
    </script>
</body>

</html>
