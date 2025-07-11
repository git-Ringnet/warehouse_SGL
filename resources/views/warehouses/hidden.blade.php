<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kho hàng ẩn - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    @php
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
    @endphp
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <div class="flex items-center">
                <a href="{{ route('warehouses.index') }}" class="mr-4 text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Kho hàng ẩn</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('warehouses.index') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-list mr-2"></i> Danh sách chính
                </a>
                @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('warehouses.view')))
                <a href="{{ route('warehouses.deleted') }}"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash mr-2"></i> Đã xóa
                </a>
                @endif
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-4">
                <div class="text-sm text-gray-600">
                    <span class="font-medium">{{ count($warehouses) }}</span> kho hàng ẩn được tìm thấy
                    @if (request()->hasAny(['search', 'manager']))
                        | Đang lọc
                        @if (request('search'))
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs ml-1">
                                Từ khóa: "{{ request('search') }}"
                            </span>
                        @endif
                        @if (request('manager'))
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs ml-1">
                                Người quản lý: {{ request('manager') }}
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mã kho</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên kho</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Địa chỉ</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Người quản lý</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tình trạng tồn kho</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach ($warehouses as $index => $warehouse)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $warehouse->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $warehouse->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $warehouse->address ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ optional($warehouse->managerEmployee)->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $totalQuantity = $warehouse->warehouseMaterials()->sum('quantity');
                                    @endphp
                                    @if($totalQuantity > 0)
                                        <span class="px-2 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">
                                            Còn tồn kho
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                            Hết tồn kho
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('warehouses.view_detail')))
                                    <a href="{{ route('warehouses.show', $warehouse->id) }}">
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem chi tiết">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                    @endif
                                    @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('warehouses.edit')))
                                    <form action="{{ route('warehouses.restore-hidden', $warehouse->id) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                            title="Khôi phục"
                                            onclick="return confirm('Bạn có chắc chắn muốn khôi phục kho hàng này?')">
                                            <i class="fas fa-undo text-green-500 group-hover:text-white"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @if (count($warehouses) == 0)
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Không có kho hàng ẩn nào
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>
