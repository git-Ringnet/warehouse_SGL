<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả Import Hàng hóa - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    <x-sidebar-component />
    <div class="min-h-screen bg-gray-50">
        <div class="lg:ml-64 flex-1 mt-[4rem]">
            <header class="bg-white shadow-sm border-b border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Kết quả Import Hàng hóa</h1>
                        <p class="text-sm text-gray-600 mt-1">Chi tiết quá trình nhập dữ liệu từ Excel</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('goods.index') }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i> Về danh sách
                        </a>
                    </div>
                </div>
            </header>

            <main class="p-6">

                @if (config('app.debug'))
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <h4 class="font-medium text-yellow-800 mb-2">Debug Info:</h4>
                        <div class="text-xs text-yellow-700 space-y-1">
                            <div>Total rows: {{ $results['total_rows'] ?? 'N/A' }}</div>
                            <div>Success: {{ $results['success_count'] ?? 'N/A' }}</div>
                            <div>Errors: {{ $results['error_count'] ?? 'N/A' }}</div>
                            <div>Duplicates: {{ $results['duplicate_count'] ?? 'N/A' }}</div>
                            <div>Created goods count: {{ count($results['created_goods'] ?? []) }}</div>
                            <div>Errors array count: {{ count($results['errors'] ?? []) }}</div>
                            <div>Duplicates array count: {{ count($results['duplicates'] ?? []) }}</div>
                        </div>
                    </div>
                @endif

                <!-- Summary Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="bg-blue-100 rounded-lg p-3">
                                <i class="fas fa-file-excel text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $results['total_rows'] }}</h3>
                                <p class="text-sm text-gray-600">Tổng dòng dữ liệu</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="bg-green-100 rounded-lg p-3">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $results['success_count'] }}</h3>
                                <p class="text-sm text-gray-600">Thành công</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="bg-red-100 rounded-lg p-3">
                                <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $results['error_count'] }}</h3>
                                <p class="text-sm text-gray-600">Lỗi</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="bg-yellow-100 rounded-lg p-3">
                                <i class="fas fa-copy text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $results['duplicate_count'] }}</h3>
                                <p class="text-sm text-gray-600">Trùng lặp</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Successfully Created Goods -->
                @if ($results['success_count'] > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                Hàng hóa đã tạo thành công ({{ $results['success_count'] }})
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dòng</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã hàng hóa</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên hàng hóa</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Hành động</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach ($results['created_goods'] as $good)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $good['row'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $good['code'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $good['name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <a href="{{ route('goods.show', $good['id']) }}"
                                                    class="text-blue-600 hover:text-blue-900">
                                                    <i class="fas fa-eye mr-1"></i> Xem
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Duplicate Goods -->
                @if ($results['duplicate_count'] > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-copy text-yellow-500 mr-2"></i>
                                Hàng hóa trùng lặp ({{ $results['duplicate_count'] }})
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Các hàng hóa đã tồn tại trong hệ thống (không bao gồm
                                hàng hóa đã xóa)</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dòng</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã hàng hóa</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên hàng hóa</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Lý do</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach ($results['duplicates'] as $duplicate)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $duplicate['row'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $duplicate['code'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $duplicate['name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                                {{ $duplicate['message'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Error Goods -->
                @if ($results['error_count'] > 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                Lỗi khi tạo hàng hóa ({{ $results['error_count'] }})
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Các dòng dữ liệu gặp lỗi validation hoặc lỗi hệ thống
                            </p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dòng</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mã hàng hóa</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên hàng hóa</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Lỗi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach ($results['errors'] as $error)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $error['row'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $error['code'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $error['name'] }}</td>
                                            <td class="px-6 py-4 text-sm text-red-600">
                                                {{ $error['message'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($results['total_rows'] == 0)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                        <i class="fas fa-file-excel text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Không có dữ liệu để import</h3>
                        <p class="text-gray-600">File Excel không chứa dữ liệu hoặc định dạng không đúng.</p>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('goods.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-list mr-2"></i> Xem danh sách hàng hóa
                    </a>
                    <a href="{{ route('goods.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-plus mr-2"></i> Thêm hàng hóa mới
                    </a>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Toggle dropdowns
        function setupDropdown(buttonId, dropdownId) {
            const button = document.getElementById(buttonId);
            const dropdown = document.getElementById(dropdownId);

            if (button && dropdown) {
                button.addEventListener('click', function(e) {
                    dropdown.classList.toggle('hidden');
                    e.stopPropagation();
                });
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            const dropdowns = document.querySelectorAll('[id$="Dropdown"]');
            dropdowns.forEach(dropdown => {
                if (!dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                }
            });
        });

        // Initialize all dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            setupDropdown('moreActionsButton', 'moreActionsDropdown');
            setupDropdown('exportDropdownButton', 'exportDropdown');
        });
    </script>
</body>

</html>
 