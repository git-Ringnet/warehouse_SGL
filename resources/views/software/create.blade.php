<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm phần mềm mới - SGL</title>
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
            <h1 class="text-xl font-bold text-gray-800">Thêm phần mềm mới</h1>
            <a href="{{ route('software.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 m-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('software.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Thông tin phần mềm</h2>
                    
                    @if ($errors->any())
                    <div class="mb-4 bg-red-50 p-3 rounded border border-red-200">
                        <ul class="list-disc list-inside text-red-500">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cột 1 -->
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên phần mềm</label>
                                <input type="text" id="name" name="name" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập tên phần mềm" required value="{{ old('name') }}">
                            </div>
                            
                            <div>
                                <label for="version" class="block text-sm font-medium text-gray-700 mb-1 required">Phiên bản</label>
                                <input type="text" id="version" name="version" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="VD: 1.0.0" required value="{{ old('version') }}">
                            </div>
                            
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại phần mềm</label>
                                <select id="type" name="type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn loại phần mềm --</option>
                                    <option value="mobile_app" {{ old('type') == 'mobile_app' ? 'selected' : '' }}>Ứng dụng di động</option>
                                    <option value="firmware" {{ old('type') == 'firmware' ? 'selected' : '' }}>Firmware</option>
                                    <option value="desktop_app" {{ old('type') == 'desktop_app' ? 'selected' : '' }}>Ứng dụng máy tính</option>
                                    <option value="driver" {{ old('type') == 'driver' ? 'selected' : '' }}>Driver</option>
                                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Khác</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="space-y-4">                            
                            <div>
                                <label for="release_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày phát hành</label>
                                <input type="date" id="release_date" name="release_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="{{ old('release_date') }}">
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái</label>
                                <select id="status" name="status" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Đã ngừng</option>
                                    <option value="beta" {{ old('status') == 'beta' ? 'selected' : '' }}>Phiên bản beta</option>
                                </select>
                            </div>
                            <div>
                                <label for="platform" class="block text-sm font-medium text-gray-700 mb-1">Nền tảng</label>
                                <select id="platform" name="platform" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">-- Chọn nền tảng --</option>
                                    <option value="android" {{ old('platform') == 'android' ? 'selected' : '' }}>Android</option>
                                    <option value="ios" {{ old('platform') == 'ios' ? 'selected' : '' }}>iOS</option>
                                    <option value="windows" {{ old('platform') == 'windows' ? 'selected' : '' }}>Windows</option>
                                    <option value="mac" {{ old('platform') == 'mac' ? 'selected' : '' }}>macOS</option>
                                    <option value="linux" {{ old('platform') == 'linux' ? 'selected' : '' }}>Linux</option>
                                    <option value="embedded" {{ old('platform') == 'embedded' ? 'selected' : '' }}>Embedded</option>
                                    <option value="other" {{ old('platform') == 'other' ? 'selected' : '' }}>Khác</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- File upload section -->
                    <div class="mt-6">
                        <h3 class="text-md font-semibold text-gray-800 mb-3">Tải lên file phần mềm</h3>
                        <div class="border-dashed border-2 border-gray-300 rounded-lg p-6 bg-gray-50">
                            <div class="space-y-4">
                                <div class="flex items-center justify-center">
                                    <label for="software_file" class="cursor-pointer flex flex-col items-center justify-center w-full">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                            <p class="text-gray-700 font-medium">Kéo và thả file hoặc</p>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Hỗ trợ: APK, BIN, ZIP, EXE, DMG, TAR.GZ... (Tối đa 40MB)
                                            </p>
                                            <button type="button" class="mt-3 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm" id="browseBtn">
                                                Chọn file
                                            </button>
                                        </div>
                                        <input type="file" id="software_file" name="software_file" class="hidden" accept=".apk,.bin,.zip,.exe,.dmg,.tar.gz" required>
                                    </label>
                                </div>
                                
                                <div id="file_details" class="hidden bg-white p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-archive text-blue-500 text-2xl mr-3"></i>
                                            <div>
                                                <p id="file_name" class="font-medium text-gray-800"></p>
                                                <p id="file_size" class="text-sm text-gray-500"></p>
                                            </div>
                                        </div>
                                        <button type="button" id="remove_file" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div id="upload_error" class="hidden text-red-500 text-sm"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả phần mềm</label>
                        <textarea id="description" name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập mô tả về phần mềm">{{ old('description') }}</textarea>
                    </div>
                    
                    <!-- Changelog -->
                    <div class="mt-6">
                        <label for="changelog" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú cập nhật</label>
                        <textarea id="changelog" name="changelog" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Nhập ghi chú về các thay đổi trong phiên bản này">{{ old('changelog') }}</textarea>
                    </div>
                    
                    <!-- Submit buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('software.index') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu phần mềm
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // File upload preview
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('software_file');
            const fileDetails = document.getElementById('file_details');
            const fileName = document.getElementById('file_name');
            const fileSize = document.getElementById('file_size');
            const removeFileBtn = document.getElementById('remove_file');
            const uploadError = document.getElementById('upload_error');
            const browseBtn = document.getElementById('browseBtn');
            
            // Click browse button to trigger file input
            browseBtn.addEventListener('click', function() {
                fileInput.click();
            });
            
            // Handle file selection
            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    
                    // Check file type
                    const allowedTypes = ['.apk', '.bin', '.zip', '.exe', '.dmg', '.tar.gz'];
                    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                    
                    if (!allowedTypes.some(type => fileExtension.endsWith(type))) {
                        uploadError.textContent = 'Loại file không được hỗ trợ. Vui lòng chọn APK, BIN, ZIP, EXE, DMG hoặc TAR.GZ.';
                        uploadError.classList.remove('hidden');
                        fileInput.value = '';
                        return;
                    }
                    
                    // Check file size (max 40MB)
                    const maxSize = 40 * 1024 * 1024; // 40MB in bytes
                    if (file.size > maxSize) {
                        uploadError.textContent = 'File quá lớn. Kích thước tối đa là 40MB.';
                        uploadError.classList.remove('hidden');
                        fileInput.value = '';
                        return;
                    }
                    
                    // Display file details
                    fileName.textContent = file.name;
                    
                    // Format file size
                    let size = file.size;
                    const units = ['B', 'KB', 'MB', 'GB'];
                    let unitIndex = 0;
                    
                    while (size >= 1024 && unitIndex < units.length - 1) {
                        size /= 1024;
                        unitIndex++;
                    }
                    
                    fileSize.textContent = `${size.toFixed(2)} ${units[unitIndex]}`;
                    
                    // Show file details and hide error
                    fileDetails.classList.remove('hidden');
                    uploadError.classList.add('hidden');
                    
                    // Set file icon based on type
                    const fileIcon = fileDetails.querySelector('i');
                    if (fileExtension === '.apk') {
                        fileIcon.className = 'fas fa-mobile-alt text-green-500 text-2xl mr-3';
                    } else if (fileExtension === '.bin') {
                        fileIcon.className = 'fas fa-microchip text-yellow-500 text-2xl mr-3';
                    } else if (fileExtension === '.zip') {
                        fileIcon.className = 'fas fa-file-archive text-blue-500 text-2xl mr-3';
                    } else if (fileExtension === '.exe') {
                        fileIcon.className = 'fas fa-desktop text-purple-500 text-2xl mr-3';
                    } else {
                        fileIcon.className = 'fas fa-file-code text-gray-500 text-2xl mr-3';
                    }
                }
            });
            
            // Remove selected file
            removeFileBtn.addEventListener('click', function() {
                fileInput.value = '';
                fileDetails.classList.add('hidden');
                uploadError.classList.add('hidden');
            });
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