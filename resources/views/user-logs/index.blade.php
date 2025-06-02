<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhật ký người dùng - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>
</head>
<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Nhật ký người dùng</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <form id="filter-form" action="{{ route('user-logs.index') }}" method="GET" class="flex flex-col md:flex-row gap-2 w-full">
                    <div class="flex flex-wrap gap-2 w-full">
                        <div class="w-full md:w-auto">
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tìm kiếm..." class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full" />
                        </div>
                        
                        <div class="w-full md:w-auto">
                            <select name="filter" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full">
                                <option value="" {{ !isset($filter) || !$filter ? 'selected' : '' }}>Tìm ở tất cả</option>
                                <option value="description" {{ isset($filter) && $filter == 'description' ? 'selected' : '' }}>Mô tả</option>
                                <option value="user" {{ isset($filter) && $filter == 'user' ? 'selected' : '' }}>Người dùng</option>
                            </select>
                        </div>

                        <div class="w-full md:w-auto">
                            <select name="user_id" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full">
                                <option value="">-- Tất cả người dùng --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ isset($userId) && $userId == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->username }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-auto">
                            <select name="action" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full">
                                <option value="">-- Tất cả hành động --</option>
                                @foreach($actions as $actionType)
                                    <option value="{{ $actionType }}" {{ isset($action) && $action == $actionType ? 'selected' : '' }}>
                                        {{ ucfirst($actionType) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-auto">
                            <select name="module" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full">
                                <option value="">-- Tất cả module --</option>
                                @foreach($modules as $moduleName)
                                    <option value="{{ $moduleName }}" {{ isset($module) && $module == $moduleName ? 'selected' : '' }}>
                                        {{ ucfirst($moduleName) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-auto">
                            <input type="text" id="start_date" name="start_date" value="{{ $startDate ?? '' }}" placeholder="Từ ngày" class="datepicker border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full" />
                        </div>

                        <div class="w-full md:w-auto">
                            <input type="text" id="end_date" name="end_date" value="{{ $endDate ?? '' }}" placeholder="Đến ngày" class="datepicker border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full" />
                        </div>

                        <div class="w-full md:w-auto flex gap-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg px-4 py-2 transition-colors flex-1 md:flex-none">
                                <i class="fas fa-search mr-2"></i> Lọc
                            </button>
                            
                            <button type="button" id="reset-btn" class="bg-gray-500 hover:bg-gray-600 text-white rounded-lg px-4 py-2 transition-colors flex-1 md:flex-none">
                                <i class="fas fa-sync-alt mr-2"></i> Đặt lại
                            </button>

                            <button type="button" id="export-btn" class="bg-green-500 hover:bg-green-600 text-white rounded-lg px-4 py-2 transition-colors flex-1 md:flex-none">
                                <i class="fas fa-file-export mr-2"></i> Xuất CSV
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </header>
        
        <main class="p-6">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif
            
            @if(session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif
            
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người dùng</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Module</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mô tả</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">IP</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thời gian</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $log->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    @if($log->user)
                                        {{ $log->user->name }}
                                        <div class="text-xs text-gray-500">{{ $log->user->username }}</div>
                                    @else
                                        <span class="text-gray-400">Người dùng không tồn tại</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($log->action == 'login') bg-green-100 text-green-800
                                        @elseif($log->action == 'logout') bg-purple-100 text-purple-800
                                        @elseif($log->action == 'create') bg-blue-100 text-blue-800
                                        @elseif($log->action == 'update') bg-yellow-100 text-yellow-800
                                        @elseif($log->action == 'delete') bg-red-100 text-red-800
                                        @elseif($log->action == 'export') bg-indigo-100 text-indigo-800
                                        @elseif($log->action == 'import') bg-emerald-100 text-emerald-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                    {{ $log->module }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 truncate max-w-xs">{{ $log->description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->ip_address }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('user-logs.show', $log->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem chi tiết">
                                        <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ liệu nhật ký</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $logs->links() }}
            </div>
        </main>
    </div>

    <form id="export-form" action="{{ route('user-logs.export') }}" method="GET" class="hidden">
        <input type="hidden" name="search" value="{{ $search ?? '' }}">
        <input type="hidden" name="filter" value="{{ $filter ?? '' }}">
        <input type="hidden" name="user_id" value="{{ $userId ?? '' }}">
        <input type="hidden" name="action" value="{{ $action ?? '' }}">
        <input type="hidden" name="module" value="{{ $module ?? '' }}">
        <input type="hidden" name="start_date" value="{{ $startDate ?? '' }}">
        <input type="hidden" name="end_date" value="{{ $endDate ?? '' }}">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Khởi tạo date picker
            flatpickr(".datepicker", {
                dateFormat: "Y-m-d",
                locale: "vn",
            });

            // Reset form
            document.getElementById('reset-btn').addEventListener('click', function() {
                window.location.href = "{{ route('user-logs.index') }}";
            });

            // Export to CSV
            document.getElementById('export-btn').addEventListener('click', function() {
                document.getElementById('export-form').submit();
            });
        });
    </script>
</body>
</html> 