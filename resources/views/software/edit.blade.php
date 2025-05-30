<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phần mềm - SGL</title>
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
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa phần mềm</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    Ứng dụng SGL Mobile
                </div>
            </div>
            <a href="{{ url('/software/1') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 h-10 px-4 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ url('/software/1') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Thông tin phần mềm</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cột 1 -->
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 required">Tên phần mềm</label>
                                <input type="text" id="name" name="name" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="Ứng dụng SGL Mobile" required>
                            </div>
                            
                            <div>
                                <label for="version" class="block text-sm font-medium text-gray-700 mb-1 required">Phiên bản</label>
                                <input type="text" id="version" name="version" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="1.2.5" required>
                            </div>
                            
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1 required">Loại phần mềm</label>
                                <select id="type" name="type" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="">-- Chọn loại phần mềm --</option>
                                    <option value="mobile_app" selected>Ứng dụng di động</option>
                                    <option value="firmware">Firmware</option>
                                    <option value="desktop_app">Ứng dụng máy tính</option>
                                    <option value="driver">Driver</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                            
                           
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="space-y-4">                            
                            <div>
                                <label for="release_date" class="block text-sm font-medium text-gray-700 mb-1">Ngày phát hành</label>
                                <input type="date" id="release_date" name="release_date" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" value="2024-06-15">
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái</label>
                                <select id="status" name="status" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                                    <option value="active" selected>Hoạt động</option>
                                    <option value="inactive">Đã ngừng</option>
                                    <option value="beta">Phiên bản beta</option>
                                </select>
                            </div>
                            <div>
                                <label for="platform" class="block text-sm font-medium text-gray-700 mb-1">Nền tảng</label>
                                <select id="platform" name="platform" class="w-full h-10 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">-- Chọn nền tảng --</option>
                                    <option value="android" selected>Android</option>
                                    <option value="ios">iOS</option>
                                    <option value="windows">Windows</option>
                                    <option value="mac">macOS</option>
                                    <option value="linux">Linux</option>
                                    <option value="embedded">Embedded</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current File section -->
                    <div class="mt-6">
                        <h3 class="text-md font-semibold text-gray-800 mb-3">File phần mềm hiện tại</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-mobile-alt text-green-500 text-2xl mr-3"></i>
                                    <div>
                                        <p class="font-medium text-gray-800">sgl_mobile_app_v1.2.5.apk</p>
                                        <p class="text-sm text-gray-500">25.4 MB - Tải lên ngày 15/06/2024</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="#" class="text-blue-500 hover:text-blue-700" title="Tải xuống">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- File replacement section -->
                    <div class="mt-6">
                        <h3 class="text-md font-semibold text-gray-800 mb-3">Thay thế file (tùy chọn)</h3>
                        <div class="border-dashed border-2 border-gray-300 rounded-lg p-6 bg-gray-50">
                            <div class="space-y-4">
                                <div class="flex items-center justify-center">
                                    <label for="software_file" class="cursor-pointer flex flex-col items-center justify-center w-full">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                            <p class="text-gray-700 font-medium">Kéo và thả file mới hoặc</p>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Hỗ trợ: APK, BIN, ZIP, EXE, DMG, TAR.GZ...
                                            </p>
                                            <button type="button" class="mt-3 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm" id="browseBtn">
                                                Chọn file mới
                                            </button>
                                        </div>
                                        <input type="file" id="software_file" name="software_file" class="hidden" accept=".apk,.bin,.zip,.exe,.dmg,.tar.gz">
                                        <!-- No required attribute - file is optional for edit -->
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
                        <textarea id="description" name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">Ứng dụng quản lý kho hàng SGL trên nền tảng di động. Hỗ trợ quét mã vạch, kiểm kê và theo dõi tồn kho.</textarea>
                    </div>
                    
                    <!-- Changelog -->
                    <div class="mt-6">
                        <label for="changelog" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú cập nhật</label>
                        <textarea id="changelog" name="changelog" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">- Sửa lỗi không thể quét mã QR trong điều kiện ánh sáng yếu
- Cải thiện tốc độ đồng bộ dữ liệu
- Thêm tính năng xuất báo cáo PDF</textarea>
                    </div>
                    
                    <!-- Submit buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ url('/software/1') }}" class="h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
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
                    
                    // Check file size (max 500MB)
                    const maxSize = 500 * 1024 * 1024; // 500MB in bytes
                    if (file.size > maxSize) {
                        uploadError.textContent = 'File quá lớn. Kích thước tối đa là 500MB.';
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