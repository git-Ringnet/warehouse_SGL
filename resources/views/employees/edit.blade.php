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
                    ID: {{ $employee->id }}
                </div>
            </div>
            <a href="{{ route('employees.show', $employee->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </header>

        <main class="p-6">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif
            
            @if(session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif
            
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 p-6">
                <form action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    @if($errors->any())
                        <x-alert type="error" :message="'Vui lòng kiểm tra lại thông tin gửi đi'" />
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Thông tin tài khoản</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1 required">Username <span class="text-red-500">*</span></label>
                            <input type="text" id="username" name="username" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('username', $employee->username) }}" readonly>
                            <p class="text-xs text-gray-500 mt-1">Username không thể thay đổi sau khi tạo</p>
                        </div>
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1 required">Họ và tên <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('name', $employee->name) }}" required>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                            <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Để trống nếu không đổi mật khẩu">
                            <p class="text-xs text-gray-500 mt-1">Mật khẩu phải có ít nhất 8 ký tự</p>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('email', $employee->email) }}">
                        </div>
                        
                        <div id="password_confirmation_container" style="display: none;">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập lại mật khẩu mới">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1 required">Số điện thoại <span class="text-red-500">*</span></label>
                            <input type="tel" id="phone" name="phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('phone', $employee->phone) }}" required>
                        </div>
                        
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Phòng ban</label>
                            <div class="relative">
                                <div class="flex">
                                    <select id="department" name="department" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                                        <option value="">-- Chọn phòng ban --</option>
                                        <option value="Kỹ thuật" {{ old('department', $employee->department) == 'Kỹ thuật' ? 'selected' : '' }}>Kỹ thuật</option>
                                        <option value="Kinh doanh" {{ old('department', $employee->department) == 'Kinh doanh' ? 'selected' : '' }}>Kinh doanh</option>
                                        <option value="Kế toán" {{ old('department', $employee->department) == 'Kế toán' ? 'selected' : '' }}>Kế toán</option>
                                        <option value="Nhân sự" {{ old('department', $employee->department) == 'Nhân sự' ? 'selected' : '' }}>Nhân sự</option>
                                        <option value="Hành chính" {{ old('department', $employee->department) == 'Hành chính' ? 'selected' : '' }}>Hành chính</option>
                                        <option value="CSKH" {{ old('department', $employee->department) == 'CSKH' ? 'selected' : '' }}>CSKH</option>
                                        <option value="Quản lý" {{ old('department', $employee->department) == 'Quản lý' ? 'selected' : '' }}>Quản lý</option>
                                    </select>
                                    <button type="button" id="addDepartmentBtn" class="ml-2 bg-green-500 hover:bg-green-600 text-white w-10 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <button type="button" id="removeDepartmentBtn" class="absolute right-20 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <!-- Modal thêm phòng ban mới -->
                            <div id="departmentModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
                                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                                    <h3 class="text-lg font-semibold mb-4">Thêm phòng ban mới</h3>
                                    <input type="text" id="newDepartment" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nhập tên phòng ban mới">
                                    <div class="flex justify-end space-x-3">
                                        <button type="button" id="cancelAddDepartment" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">Hủy</button>
                                        <button type="button" id="confirmAddDepartment" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg">Thêm</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                            <input type="text" id="address" name="address" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('address', $employee->address) }}">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái tài khoản</label>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 {{ $employee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} rounded-full text-xs font-semibold">
                                    {{ $employee->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    (Để thay đổi trạng thái, vui lòng sử dụng chức năng khóa/mở khóa tài khoản)
                                </span>
                            </div>
                            <input type="hidden" name="is_active" value="{{ $employee->is_active }}">
                        </div>
                        
                        <div>
                            <label for="avatar" class="block text-sm font-medium text-gray-700 mb-1">Ảnh đại diện</label>
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 h-16 w-16">
                                    @if($employee->avatar)
                                        <img class="h-16 w-16 rounded-full object-cover" src="{{ asset('storage/' . $employee->avatar) }}" alt="{{ $employee->name }}">
                                    @else
                                        <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500 font-medium text-xl">{{ substr($employee->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <input type="file" id="avatar" name="avatar" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Hỗ trợ định dạng: JPG, PNG, GIF (tối đa 2MB)</p>
                        </div>
                        </div>
                        
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mt-6">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                            <textarea id="notes" name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $employee->notes) }}</textarea>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('employees.show', $employee->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Hủy
                        </a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
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

    <script>
        // Xử lý thêm/xóa phòng ban
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy các phần tử
            const departmentSelect = document.getElementById('department');
            const addDepartmentBtn = document.getElementById('addDepartmentBtn');
            const removeDepartmentBtn = document.getElementById('removeDepartmentBtn');
            const departmentModal = document.getElementById('departmentModal');
            const newDepartmentInput = document.getElementById('newDepartment');
            const confirmAddDepartment = document.getElementById('confirmAddDepartment');
            const cancelAddDepartment = document.getElementById('cancelAddDepartment');
            
            // Ẩn nút xóa nếu chưa chọn phòng ban
            if (departmentSelect.value === '') {
                removeDepartmentBtn.style.display = 'none';
            }
            
            // Hiển thị nút xóa khi chọn phòng ban
            departmentSelect.addEventListener('change', function() {
                removeDepartmentBtn.style.display = this.value === '' ? 'none' : 'block';
            });
            
            // Mở modal thêm phòng ban
            addDepartmentBtn.addEventListener('click', function() {
                departmentModal.classList.remove('hidden');
                newDepartmentInput.focus();
            });
            
            // Đóng modal khi nhấn Hủy
            cancelAddDepartment.addEventListener('click', function() {
                departmentModal.classList.add('hidden');
                newDepartmentInput.value = '';
            });
            
            // Xử lý thêm phòng ban mới
            confirmAddDepartment.addEventListener('click', function() {
                const newDepartmentName = newDepartmentInput.value.trim();
                if (newDepartmentName) {
                    // Kiểm tra xem phòng ban đã tồn tại chưa
                    let exists = false;
                    for (let i = 0; i < departmentSelect.options.length; i++) {
                        if (departmentSelect.options[i].text === newDepartmentName) {
                            exists = true;
                            break;
                        }
                    }
                    
                    if (!exists) {
                        // Thêm option mới
                        const newOption = document.createElement('option');
                        newOption.value = newDepartmentName;
                        newOption.text = newDepartmentName;
                        newOption.selected = true;
                        departmentSelect.appendChild(newOption);
                        
                        // Lưu danh sách phòng ban vào localStorage
                        saveDepartments();
                        
                        // Hiện nút xóa
                        removeDepartmentBtn.style.display = 'block';
                    }
                    
                    // Đóng modal
                    departmentModal.classList.add('hidden');
                    newDepartmentInput.value = '';
                }
            });
            
            // Xử lý xóa phòng ban
            removeDepartmentBtn.addEventListener('click', function() {
                const selectedIndex = departmentSelect.selectedIndex;
                if (selectedIndex > 0) { // Không xóa option đầu tiên (-- Chọn phòng ban --)
                    departmentSelect.remove(selectedIndex);
                    departmentSelect.selectedIndex = 0;
                    removeDepartmentBtn.style.display = 'none';
                    saveDepartments();
                }
            });
            
            // Lưu danh sách phòng ban vào localStorage
            function saveDepartments() {
                const departments = [];
                for (let i = 1; i < departmentSelect.options.length; i++) { // Bỏ qua option đầu tiên
                    departments.push(departmentSelect.options[i].text);
                }
                localStorage.setItem('departments', JSON.stringify(departments));
            }
            
            // Tải danh sách phòng ban từ localStorage
            function loadDepartments() {
                const savedDepartments = localStorage.getItem('departments');
                if (savedDepartments) {
                    const departments = JSON.parse(savedDepartments);
                    const defaultOptions = ['Kỹ thuật', 'Kinh doanh', 'Kế toán', 'Nhân sự', 'Hành chính', 'CSKH', 'Quản lý'];
                    const currentDepartment = "{{ old('department', $employee->department) }}";
                    
                    // Thêm phòng ban hiện tại nếu có và chưa có trong danh sách
                    if (currentDepartment && !departments.includes(currentDepartment) && !defaultOptions.includes(currentDepartment)) {
                        departments.push(currentDepartment);
                    }
                    
                    // Xóa tất cả options trừ option đầu tiên
                    while (departmentSelect.options.length > 1) {
                        departmentSelect.remove(1);
                    }
                    
                    // Thêm các options mặc định
                    defaultOptions.forEach(dept => {
                        if (!departments.includes(dept)) {
                            departments.push(dept);
                        }
                    });
                    
                    // Thêm tất cả departments vào select
                    departments.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept;
                        option.text = dept;
                        option.selected = (dept === currentDepartment);
                        departmentSelect.appendChild(option);
                    });
                }
            }
            
            // Tải danh sách phòng ban khi trang tải xong
            loadDepartments();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý hiển thị trường xác nhận mật khẩu
            const passwordField = document.getElementById('password');
            const passwordConfirmationContainer = document.getElementById('password_confirmation_container');
            
            passwordField.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    passwordConfirmationContainer.style.display = 'block';
                } else {
                    passwordConfirmationContainer.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html> 