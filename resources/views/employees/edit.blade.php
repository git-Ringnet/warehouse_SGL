<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thông tin nhân viên - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
</head>
<body>
    <x-sidebar-component />
    
    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800">Chỉnh sửa thông tin nhân viên</h1>
                <div class="ml-4 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    ID: EMP001
                </div>
            </div>
            <a href="{{ url('/employees/1') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="#" method="POST">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin tài khoản</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cột 1 -->
                        <div class="space-y-4">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1 required">Username</label>
                                <input type="text" id="username" name="username" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="admin" readonly>
                                <p class="text-xs text-gray-500 mt-1">Username không thể thay đổi sau khi tạo</p>
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                                <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Để trống nếu không đổi mật khẩu">
                                <p class="text-xs text-gray-500 mt-1">Mật khẩu phải có ít nhất 8 ký tự</p>
                            </div>
                            
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập lại mật khẩu mới">
                            </div>
                            
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-1 required">Vai trò</label>
                                <select id="role" name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">-- Chọn vai trò --</option>
                                    <option value="admin" selected>Quản trị viên</option>
                                    <option value="manager">Quản lý</option>
                                    <option value="staff">Nhân viên</option>
                                    <option value="tech">Kỹ thuật viên</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Cột 2 -->
                        <div class="space-y-4">
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1 required">Họ và tên</label>
                                <input type="text" id="full_name" name="full_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Nguyễn Văn Quản Trị" required>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1 required">Email</label>
                                <input type="email" id="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="admin@sgl.com" required>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="0987654321">
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1 required">Trạng thái</label>
                                <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    <option value="active" selected>Đang hoạt động</option>
                                    <option value="leave">Nghỉ phép</option>
                                    <option value="inactive">Đã nghỉ việc</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thông tin bổ sung -->
                    <h2 class="text-lg font-semibold text-gray-800 mt-8 mb-4">Thông tin bổ sung</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                                <input type="text" id="address" name="address" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="123 Lê Lợi, Quận 1, TP.HCM">
                            </div>
                            
                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Phòng ban</label>
                                <select id="department" name="department" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Chọn phòng ban --</option>
                                    <option value="it" selected>Công nghệ thông tin</option>
                                    <option value="hr">Nhân sự</option>
                                    <option value="finance">Tài chính</option>
                                    <option value="sales">Kinh doanh</option>
                                    <option value="tech">Kỹ thuật</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="date_joined" class="block text-sm font-medium text-gray-700 mb-1">Ngày vào làm</label>
                                <input type="date" id="date_joined" name="date_joined" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="2024-01-01">
                            </div>
                            
                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Chức vụ</label>
                                <input type="text" id="position" name="position" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="Quản trị hệ thống">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ url('/employees/1') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- <div class="mt-6 bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-history mr-2 text-gray-500"></i>
                        Thời gian tạo tài khoản
                    </h2>
                    <span class="text-gray-600">01/01/2024</span>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-lock mr-2 text-gray-500"></i>
                        Lần đăng nhập cuối
                    </h2>
                    <span class="text-gray-600">30/05/2024 15:30:22</span>
                </div>
            </div> -->
        </main>
    </div>

    <script>
        // Dropdown Menus
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
    </script>
</body>
</html> 