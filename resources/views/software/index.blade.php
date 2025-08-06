<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phần mềm - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
</head>
<body>
    @php
        $user = Auth::guard('web')->user();
        $canCreate = $user && ($user->role === 'admin' || ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('software.create')));
        $canEdit = $user && ($user->role === 'admin' || ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('software.edit')));
        $canDelete = $user && ($user->role === 'admin' || ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('software.delete')));
        $canViewDetail = $user && ($user->role === 'admin' || ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('software.view_detail')));
        $canDownload = $user && ($user->role === 'admin' || ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('software.download')));
    @endphp

    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý phần mềm</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <form action="{{ route('software.index') }}" method="GET" class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full">
                    <div class="flex gap-2 w-full md:w-auto">
                        <input type="text" name="search" placeholder="Tìm kiếm theo tên, phiên bản..." class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64 h-10" value="{{ request('search') }}" />
                        <select name="file_type" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 h-10">
                            <option value="">Loại file</option>
                            <option value="apk" {{ request('file_type') == 'apk' ? 'selected' : '' }}>APK</option>
                            <option value="bin" {{ request('file_type') == 'bin' ? 'selected' : '' }}>BIN</option>
                            <option value="zip" {{ request('file_type') == 'zip' ? 'selected' : '' }}>ZIP</option>
                            <option value="exe" {{ request('file_type') == 'exe' ? 'selected' : '' }}>EXE</option>
                            <option value="other" {{ request('file_type') == 'other' ? 'selected' : '' }}>Khác</option>
                        </select>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors h-10">
                            <i class="fas fa-search mr-2"></i> Tìm
                        </button>
                    </div>
                </form>
                @if($canCreate)
                <a href="{{ route('software.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center h-10">
                    <i class="fas fa-plus-circle mr-2"></i> Thêm
                </a>
                @endif
            </div>
        </header>

        @if(session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif

        @if(session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên phần mềm</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phiên bản</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Loại file</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kích thước</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày tải lên</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($software as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $software->firstItem() + $index }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if(!empty($item->version))
                                    {{ $item->version }}
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if(!empty($item->file_path))
                                    <span class="px-2 py-1 {{ $item->fileTypeClass }} rounded text-xs">{{ strtoupper($item->file_type) }}</span>
                                @else
                                    <span class="text-gray-400 italic">Chưa có file</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if(!empty($item->file_path))
                                    {{ $item->file_size }}
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="px-2 py-1 {{ $item->statusClass }} rounded text-xs">{{ $item->statusLabel }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                @if($canViewDetail)
                                <a href="{{ route('software.show', $item->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group" title="Xem">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </a>
                                @endif

                                @if($canEdit)
                                <a href="{{ route('software.edit', $item->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group" title="Sửa">
                                    <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                </a>
                                @endif

                                @if($canDelete)
                                <button onclick="openDeleteModal('{{ $item->id }}', '{{ $item->name }}')" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group" title="Xóa">
                                    <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                </button>
                                @endif

                                @if($canDownload)
                                <a href="{{ route('software.download', $item->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group" title="Tải xuống phần mềm">
                                    <i class="fas fa-download text-purple-500 group-hover:text-white"></i>
                                </a>

                                @if(!empty($item->manual_path))
                                <a href="{{ route('software.download_manual', $item->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group" title="Tải tài liệu hướng dẫn">
                                    <i class="fas fa-file-pdf text-green-500 group-hover:text-white"></i>
                                </a>
                                @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">Không có dữ liệu phần mềm</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Hiển thị {{ $software->firstItem() ?? 0 }}-{{ $software->lastItem() ?? 0 }} của {{ $software->total() ?? 0 }} mục
                </div>
                <div class="flex">
                    {{ $software->appends(request()->query())->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- Form ẩn để xóa phần mềm -->
    <form id="deleteSoftwareForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        // Xử lý xóa phần mềm - sử dụng delete-modal.js
        function deleteCustomer(id) {
            const form = document.getElementById('deleteSoftwareForm');
            form.action = `/software/${id}`;
            form.submit();
        }

        // Khi trang đã tải xong
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
        });

        // Hàm toggleDropdown cho sidebar
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
    </script>
</body>
</html> 