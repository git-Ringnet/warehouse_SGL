<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết phần mềm - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chi tiết phần mềm</h1>
                <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $software->name }} v{{ $software->version }}
                </div>
                <div class="ml-2 px-3 py-1 {{ $software->statusClass }} text-sm rounded-full">
                    {{ $software->statusLabel }}
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('software.index') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </a>
                <a href="{{ route('software.edit', $software->id) }}" class="h-10 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa
                </a>
            </div>
        </header>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 m-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <main class="p-6 space-y-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">Thông tin phần mềm</h2>
                    <div class="flex items-center space-x-2">
                        @if($software->release_date)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Phát hành: {{ $software->release_date->format('d/m/Y') }}
                        </span>
                        @endif
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $software->download_count }} lượt tải
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cột 1 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Tên phần mềm</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $software->name }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Phiên bản</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $software->version }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Loại phần mềm</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $software->typeLabel }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">File</p>
                            <div class="flex items-center">
                                <span class="px-2 py-1 {{ $software->fileTypeClass }} rounded text-xs mr-2">{{ strtoupper($software->file_type) }}</span>
                                <span class="text-base text-gray-800 font-semibold">{{ $software->file_name }}</span>
                                <span class="ml-2 text-sm text-gray-500">({{ $software->file_size }})</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cột 2 -->
                    <div>
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Nền tảng</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $software->platform ?? 'Không xác định' }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Ngày tải lên</p>
                            <p class="text-base text-gray-800 font-semibold">{{ $software->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Trạng thái</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $software->statusClass }}">
                                <i class="fas fa-circle mr-1 text-xs"></i> {{ $software->statusLabel }}
                            </span>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-500 font-medium mb-1">Tải xuống</p>
                            <a href="{{ route('software.download', $software->id) }}" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded flex items-center w-max transition-colors">
                                <i class="fas fa-download mr-2"></i> Tải xuống
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Mô tả -->
                @if($software->description)
                <div class="mt-6">
                    <h3 class="text-md font-semibold text-gray-800 mb-2">Mô tả phần mềm</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700 whitespace-pre-line">{{ $software->description }}</p>
                    </div>
                </div>
                @endif
                
                <!-- Ghi chú cập nhật -->
                @if($software->changelog)
                <div class="mt-6">
                    <h3 class="text-md font-semibold text-gray-800 mb-2">Ghi chú cập nhật</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700 whitespace-pre-line">{{ $software->changelog }}</p>
                    </div>
                </div>
                @endif

                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between">
                    <div>
                        <p class="text-sm text-gray-600">
                            Thời gian tạo: {{ $software->created_at->format('d/m/Y H:i:s') }}
                        </p>
                        @if($software->created_at != $software->updated_at)
                        <p class="text-sm text-gray-600">
                            Cập nhật lần cuối: {{ $software->updated_at->format('d/m/Y H:i:s') }}
                        </p>
                        @endif
                    </div>
                    
                    <button onclick="openDeleteModal('{{ $software->id }}', '{{ $software->name }}')" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash mr-1"></i> Xóa phần mềm
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal xác nhận xóa -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h2 class="text-xl font-bold mb-4">Xác nhận xóa</h2>
            <p class="mb-6">Bạn có chắc chắn muốn xóa phần mềm <span id="softwareNameToDelete" class="font-bold"></span> không?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancelDeleteBtn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">Hủy</button>
                <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">Xóa</button>
            </div>
        </div>
    </div>

    <!-- Form ẩn để xóa phần mềm -->
    <form id="deleteSoftwareForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
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

        // Xử lý xóa phần mềm
        function openDeleteModal(id, name) {
            document.getElementById('softwareNameToDelete').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
            
            // Set up action for confirm button
            document.getElementById('confirmDeleteBtn').onclick = function() {
                const form = document.getElementById('deleteSoftwareForm');
                form.action = `/software/${id}`;
                form.submit();
            };
        }
        
        document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
            document.getElementById('deleteModal').classList.add('hidden');
        });
    </script>
</body>
</html> 