<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vật tư đã ẩn - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('materials.index') }}" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Vật tư đã ẩn</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('materials.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-list mr-2"></i> Danh sách chính
                </a>
                <a href="{{ route('materials.deleted') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-trash mr-2"></i> Đã xóa
                </a>
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

            @if($materials->count() > 0)
                <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã vật tư</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên vật tư</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Đơn vị</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tổng tồn kho</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($materials as $index => $material)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $material->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->category }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $material->unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <span class="font-medium px-2 py-1 rounded bg-yellow-100 text-yellow-800">
                                            {{ number_format($material->inventory_quantity, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                        <a href="{{ route('materials.show', $material->id) }}">
                                            <button class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                            </button>
                                        </a>
                                        <form action="{{ route('materials.restore', $material->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group" title="Khôi phục">
                                                <i class="fas fa-undo text-green-500 group-hover:text-white"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-4">
                        {{ $materials->links() }}
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-md p-8 text-center">
                    <i class="fas fa-eye-slash text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Không có vật tư nào đã ẩn</h3>
                    <p class="text-gray-500">Tất cả vật tư hiện đang hiển thị trong danh sách chính.</p>
                </div>
            @endif
        </main>
    </div>
</body>

</html> 