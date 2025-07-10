<!-- Sidebar -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="sidebar w-64 fixed top-0 left-0 overflow-y-auto shadow-lg z-50"
    style="bottom: 0; background: linear-gradient(180deg, #1a365d 0%, #0f2942 100%);">
    <div class="p-4 flex items-center justify-between border-b border-gray-700">
        <div class="flex items-center">
            <i class="fas fa-warehouse text-2xl mr-3" style="color: #fff"></i>
            <span class="logo-text text-xl font-bold">SGL WMS</span>
        </div>
        <button id="toggleSidebar" class="text-white focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <div class="p-4 flex flex-col">
        @php
            $user = Auth::guard('web')->user();
            $hasAnySetupPermission = false;
            $hasAnyOperationPermission = false;
            $hasAnyProjectPermission = false;
            $hasAnyReportPermission = false;

            // Nếu là admin, có tất cả quyền
            if ($user && $user->role === 'admin') {
                $hasAnySetupPermission = true;
                $hasAnyOperationPermission = true;
                $hasAnyProjectPermission = true;
                $hasAnyReportPermission = true;
            } elseif ($user && $user->role_id && $user->roleGroup && $user->roleGroup->is_active) {
                $role = $user->roleGroup;
                $hasAnySetupPermission =
                    $role->hasPermission('materials.view') ||
                    $role->hasPermission('products.view') ||
                    $role->hasPermission('warehouses.view') ||
                    $role->hasPermission('customers.view') ||
                    $role->hasPermission('suppliers.view') ||
                    $role->hasPermission('employees.view') ||
                    $role->hasPermission('goods.view');

                $hasAnyOperationPermission =
                    $role->hasPermission('inventory.view') ||
                    $role->hasPermission('inventory_imports.view') ||
                    $role->hasPermission('warehouse-transfers.view') ||
                    $role->hasPermission('repairs.view') ||
                    $role->hasPermission('warranties.view');

                $hasAnyProjectPermission =
                    $role->hasPermission('projects.view') || $role->hasPermission('rentals.view');

                $hasAnyReportPermission =
                    $role->hasPermission('reports.overview') ||
                    $role->hasPermission('reports.inventory') ||
                    $role->hasPermission('reports.export');
            }
        @endphp

        <ul class="space-y-2">
            @if (
                ($user && $user->role === 'admin') ||
                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('reports.overview')))
                <li>
                    <a href="{{ asset('dashboard') }}" class="nav-item flex items-center px-4 py-3 rounded-lg">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        <span class="nav-text">Tổng Quan</span>
                    </a>
                </li>
            @endif

            @if ($hasAnySetupPermission)
                <li>
                    <div class="dropdown">
                        <button onclick="toggleDropdown('masterData')"
                            class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                            <div class="flex items-center">
                                <i class="fas fa-cog mr-3"></i>
                                <span class="nav-text">Thiết lập</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <ul id="masterData" class="dropdown-content pl-4 mt-1 space-y-1">
                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('customers.view')))
                                <li>
                                    <a href="{{ asset('customers') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">Khách
                                        Hàng</a>
                                </li>
                            @endif

                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('suppliers.view')))
                                <li>
                                    <a href="{{ asset('suppliers') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">Nhà
                                        Cung
                                        Cấp</a>
                                </li>
                            @endif

                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('employees.view')))
                                <li>
                                    <a href="{{ asset('employees') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">Nhân
                                        Viên</a>
                                </li>
                            @endif

                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('materials.view')))
                                <li>
                                    <a href="{{ asset('materials') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">Vật
                                        Tư</a>
                                </li>
                            @endif

                            @if (($user && $user->role === 'admin') || ($user && $user->roleGroup && $user->roleGroup->hasPermission('goods.view')))
                                <li>
                                    <a href="{{ asset('goods') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">
                                        Hàng hóa
                                    </a>
                                </li>
                            @endif

                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('products.view')))
                                <li>
                                    <a href="{{ asset('products') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">Thành
                                        Phẩm</a>
                                </li>
                            @endif

                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('warehouses.view')))
                                <li>
                                    <a href="{{ asset('warehouses') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">Kho
                                        Hàng</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            @if (
                ($user && $user->role === 'admin') ||
                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('assembly.view')))
                <li>
                    <div class="dropdown">
                        <a href="{{ route('assemblies.index') }}">
                            <button onclick="toggleDropdown('assembly')"
                                class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                                <div class="flex items-center">
                                    <i class="fas fa-tools mr-3"></i>
                                    <span class="nav-text">Lắp Ráp</span>
                                </div>
                            </button>
                        </a>
                    </div>
                </li>
            @endif

            @if (
                ($user && $user->role === 'admin') ||
                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('testing.view')))
                <li>
                    <div class="dropdown">
                        <a href="{{ asset('testing') }}">
                            <button onclick="toggleDropdown('testing')"
                                class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                                <div class="flex items-center">
                                    <i class="fas fa-vial mr-3"></i>
                                    <span class="nav-text">Kiểm Thử</span>
                                </div>
                            </button>
                        </a>
                    </div>
                </li>
            @endif

            @if ($hasAnyOperationPermission)
                <li>
                    <div class="dropdown">
                        <button onclick="toggleDropdown('operations')"
                            class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                            <div class="flex items-center">
                                <i class="fas fa-dolly mr-3"></i>
                                <span class="nav-text">Vận Hành</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <ul id="operations" class="dropdown-content pl-4 mt-1 space-y-1">
                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('inventory_imports.view')))
                                <li>
                                    <a href="{{ asset('inventory-imports') }}"
                                        class="nav-subitem flex items-center px-4 py-2 rounded-lg hover:bg-gray-700">
                                        <span class="nav-text">Nhập kho</span>
                                    </a>
                                </li>
                            @endif

                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('inventory.view')))
                                <li>
                                    <a href="{{ asset('inventory') }}"
                                        class="nav-subitem flex items-center px-4 py-2 rounded-lg hover:bg-gray-700">
                                        <span class="nav-text">Xuất kho</span>
                                    </a>
                                </li>
                            @endif

                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('warehouse-transfers.view')))
                                <li>
                                    <a href="{{ asset('warehouse-transfers') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">Chuyển Kho</a>
                                </li>
                            @endif

                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('repairs.view')))
                                <li>
                                    <a href="{{ route('repairs.index') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">
                                        Sửa chữa - bảo trì
                                    </a>
                                </li>
                            @endif

                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('warranties.view')))
                                <li>
                                    <a href="{{ asset('warranties') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">Bảo
                                        hành điện
                                        tử</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            @if ($hasAnyProjectPermission)
                <li>
                    <div class="dropdown">
                        <button onclick="toggleDropdown('projects')"
                            class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                            <div class="flex items-center">
                                <i class="fas fa-tasks mr-3"></i>
                                <span class="nav-text">Dự Án</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <ul id="projects" class="dropdown-content pl-4 mt-1 space-y-1">
                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('projects.view')))
                                <li>
                                    <a href="{{ asset('projects') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">Quản
                                        Lý Dự
                                        Án</a>
                                </li>
                            @endif

                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('rentals.view')))
                                <li>
                                    <a href="{{ asset('rentals') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">Quản
                                        Lý Cho
                                        Thuê</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            @if (
                ($user && $user->role === 'admin') ||
                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('change-logs.view')))
                <li>
                    <div class="dropdown">
                        <a href="{{ route('change-logs.index') }}">
                            <button onclick="toggleDropdown('changeLog')"
                                class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                                <div class="flex items-center">
                                    <i class="fas fa-history mr-3"></i>
                                    <span class="nav-text">Nhật Ký Thay Đổi</span>
                                </div>
                            </button>
                        </a>
                    </div>
                </li>
            @endif

            @if (
                ($user && $user->role === 'admin') ||
                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('software.view')))
                <li>
                    <a href="{{ asset('software') }}"
                        class="nav-item flex items-center px-4 py-3 rounded-lg hover:bg-gray-700">
                        <i class="fas fa-laptop-code mr-3"></i>
                        <span class="nav-text">Phần mềm</span>
                    </a>
                </li>
            @endif

            @if (
                ($user && $user->role === 'admin') ||
                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('requests.view')))
                <li>
                    <div class="dropdown">
                        <a href="{{ asset('requests') }}">
                            <button onclick="toggleDropdown('requestForm')"
                                class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                                <div class="flex items-center">
                                    <i class="fas fa-file-alt mr-3"></i>
                                    <span class="nav-text">Gửi phiếu yêu cầu</span>
                                </div>
                            </button>
                        </a>
                    </div>
                </li>
            @endif

            @if ($hasAnyReportPermission)
                <li>
                    <div class="dropdown">
                        <button onclick="toggleDropdown('reports')"
                            class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                            <div class="flex items-center">
                                <i class="fas fa-chart-bar mr-3"></i>
                                <span class="nav-text">Báo Cáo</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <ul id="reports" class="dropdown-content reports-menu pl-4 mt-1 space-y-1">
                            @if (
                                ($user && $user->role === 'admin') ||
                                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('reports.inventory')))
                                <li>
                                    <a href="{{ asset('reports') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">
                                        Báo cáo xuất nhập tồn chi tiết
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            @php
                $hasRolesPermission =
                    ($user && $user->role === 'admin') ||
                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('roles.view'));
                $hasPermissionsPermission = $user && $user->role === 'admin';
                $hasUserLogsPermission =
                    ($user && $user->role === 'admin') ||
                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('user-logs.view'));
                $hasChangeLogsPermission =
                    ($user && $user->role === 'admin') ||
                    ($user && $user->roleGroup && $user->roleGroup->hasPermission('change-logs.view'));

                $hasAnyPermissionAccess =
                    $hasRolesPermission ||
                    $hasPermissionsPermission ||
                    $hasUserLogsPermission ||
                    $hasChangeLogsPermission;
            @endphp

            @if ($hasAnyPermissionAccess)
                <li>
                    <div class="dropdown">
                        <button onclick="toggleDropdown('permissions')"
                            class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                            <div class="flex items-center">
                                <i class="fas fa-user-shield mr-3"></i>
                                <span class="nav-text">Phân Quyền</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <ul id="permissions" class="dropdown-content pl-4 mt-1 space-y-1">
                            @if ($hasRolesPermission)
                                <li>
                                    <a href="{{ asset('roles') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">
                                        Nhóm quyền
                                    </a>
                                </li>
                            @endif
                            @if ($hasPermissionsPermission)
                                <li>
                                    <a href="{{ asset('permissions') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">
                                        Danh sách quyền
                                        <span class="text-xs text-yellow-300 ml-1">(Admin only)</span>
                                    </a>
                                </li>
                            @endif
                            @if ($hasUserLogsPermission)
                                <li>
                                    <a href="{{ asset('user-logs') }}"
                                        class="block px-4 py-2 rounded-lg hover:bg-gray-700">
                                        Nhật ký người dùng
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @elseif (Auth::guard('web')->check())
                <li>
                    <div class="opacity-50">
                        <div
                            class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg cursor-not-allowed">
                            <div class="flex items-center">
                                <i class="fas fa-user-shield mr-3"></i>
                                <span class="nav-text">Phân Quyền</span>
                            </div>
                            <i class="fas fa-lock text-xs"></i>
                        </div>
                        <p class="text-xs text-gray-400 px-4 pb-2">Bạn không có quyền truy cập</p>
                    </div>
                </li>
            @endif
        </ul>
    </div>
</div>

<!-- Top Header Bar -->
<header
    class="bg-white dark:bg-gray-800 shadow-sm py-4 px-6 flex justify-between items-center fixed top-0 right-0 left-0 z-40"
    style="left: 256px; z-index: 45 !important;">
    <div class="flex items-center">
        <h1 class="text-xl font-bold text-gray-800 dark:text-white opacity-0" id="page-title">
        </h1>
    </div>

    <div class="flex items-center space-x-4">
        <!-- Notification Bell -->
        <div class="relative">
            <button id="notificationToggle" class="flex items-center focus:outline-none relative">
                <i class="fas fa-bell text-gray-700 dark:text-gray-300 text-xl"></i>
                <span id="notificationCount"
                    class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
            </button>
            <div
                class="dropdown-menu absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-0 hidden z-50 border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div
                    class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Thông báo</h3>
                    <div class="flex space-x-2">
                        <button id="markAllReadBtn"
                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Đánh dấu đã
                            đọc</button>
                    </div>
                </div>
                <div id="notificationList" class="max-h-80 overflow-y-auto py-1">
                    <!-- Notifications will be loaded here dynamically -->
                    <div class="px-4 py-3 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Đang tải thông báo...
                    </div>
                </div>
                <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                    <a href="{{ route('notifications.index') }}"
                        class="block text-center text-sm text-blue-600 dark:text-blue-400 hover:underline">Xem tất cả
                        thông báo</a>
                </div>
            </div>
        </div>

        <div class="relative">
            <button id="userMenuToggle" class="flex items-center focus:outline-none">
                @if (session('user_type') === 'customer')
                    @if (Auth::guard('customer')->check() && Auth::guard('customer')->user()->avatar)
                        <img src="{{ asset('storage/' . Auth::guard('customer')->user()->avatar) }}"
                            alt="{{ Auth::guard('customer')->user()->name }}" class="w-8 h-8 rounded-full object-cover mr-2">
                    @else
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                            <span class="text-gray-500 font-medium">
                                {{ Auth::guard('customer')->check() ? substr(Auth::guard('customer')->user()->name, 0, 1) : 'K' }}
                            </span>
                        </div>
                    @endif
                    <span class="text-gray-700 dark:text-gray-300 hidden md:inline">
                        @if (Auth::guard('customer')->check())
                            {{ Auth::guard('customer')->user()->name }} (Khách hàng)
                        @else
                            Khách hàng
                        @endif
                    </span>
                @else
                    @php
                        $user = Auth::guard('web')->user();
                        $avatarPath = $user && $user->avatar ? asset('storage/' . $user->avatar) : null;
                    @endphp
                    
                    @if ($user && $user->avatar && Storage::disk('public')->exists($user->avatar))
                        <img src="{{ $avatarPath }}"
                            alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover mr-2">
                    @else
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                            <span class="text-gray-500 font-medium">
                                {{ $user ? substr($user->name, 0, 1) : 'N' }}
                            </span>
                        </div>
                    @endif
                    <span class="text-gray-700 dark:text-gray-300 hidden md:inline">
                        @if ($user)
                            {{ $user->name }} (Nhân viên)
                        @else
                            Nhân viên
                        @endif
                    </span>
                @endif
            </button>
            <div
                class="dropdown-menu absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 hidden z-50 border border-gray-200 dark:border-gray-700">
                <a href="{{ route('profile') }}"
                    class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700">Hồ
                    sơ</a>
                <a href="#"
                    class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700">Cài
                    đặt</a>
                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                <button onclick="handleLogout()"
                    class="block w-full text-left px-4 py-2 text-red-500 hover:bg-blue-50 dark:hover:bg-gray-700">Đăng
                    xuất</button>
            </div>
        </div>
    </div>
</header>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Include notifications.js -->
<script src="{{ asset('js/notifications.js') }}"></script>

<!-- JavaScript to fix sidebar height -->
<script>
    // Dropdown Menus
    function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        const allDropdowns = document.querySelectorAll(".dropdown-content");

        // Close all other dropdowns
        allDropdowns.forEach((d) => {
            if (d.id !== id) {
                d.classList.remove("show");
            }
        });

        // Toggle current dropdown
        dropdown.classList.toggle("show");
    }

    // Close dropdowns when clicking outside
    document.addEventListener("click", (e) => {
        if (!e.target.closest(".dropdown")) {
            document.querySelectorAll(".dropdown-content").forEach((dropdown) => {
                dropdown.classList.remove("show");
            });
        }

        // Đóng dropdown menu cho header (notification và user menu)
        if (!e.target.closest("#notificationToggle") && !e.target.closest("#userMenuToggle")) {
            document.querySelectorAll(".dropdown-menu").forEach((menu) => {
                menu.classList.add("hidden");
            });
        }
    });

    // Prevent dropdown from closing when clicking inside
    document.querySelectorAll(".dropdown-content").forEach((dropdown) => {
        dropdown.addEventListener("click", (e) => {
            e.stopPropagation();
        });
    });

    // Header dropdown menus
    const dropdownToggles = document.querySelectorAll('[id$="Toggle"]');

    dropdownToggles.forEach((toggle) => {
        toggle.addEventListener("click", (e) => {
            e.stopPropagation();
            const menuId = toggle.id.replace('Toggle', '');
            const menu = toggle.nextElementSibling;

            // Hide all other dropdown menus
            document.querySelectorAll(".dropdown-menu").forEach((otherMenu) => {
                if (otherMenu !== menu) {
                    otherMenu.classList.add("hidden");
                }
            });

            // Toggle current dropdown menu
            menu.classList.toggle("hidden");
        });
    });

    // Prevent header dropdown from closing when clicking inside
    document.querySelectorAll(".dropdown-menu").forEach((menu) => {
        menu.addEventListener("click", (e) => {
            e.stopPropagation();
        });
    });

    // Handle logout with form submission (không dùng AJAX để tránh lỗi CSRF)
    function handleLogout() {
        // Show confirmation dialog with SweetAlert2
        Swal.fire({
            title: 'Xác nhận đăng xuất',
            text: 'Bạn có chắc chắn muốn đăng xuất?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đăng xuất',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Đang đăng xuất...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Tạo form ẩn để submit logout
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('logout') }}';
                form.style.display = 'none';

                // Thêm CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Thêm form vào body và submit
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Update page title based on current route if needed
    document.addEventListener('DOMContentLoaded', function() {
        // You can customize this function based on your needs
        function updatePageTitle() {
            const currentPath = window.location.pathname;
            let title = 'Tổng quan';

            // Map paths to titles
            const pageTitles = {
                '/dashboard': 'Tổng quan',
                '/customers': 'Quản lý khách hàng',
                '/suppliers': 'Quản lý nhà cung cấp',
                '/employees': 'Quản lý nhân viên',
                '/materials': 'Quản lý vật tư',
                '/goods': 'Quản lý hàng hóa',
                '/products': 'Quản lý thành phẩm',
                '/warehouses': 'Quản lý kho hàng',
                // Add more mappings as needed
            };

            // Set the title based on the path or use a default
            if (pageTitles[currentPath]) {
                title = pageTitles[currentPath];
            }

            // Update the page title element if it exists
            const pageTitleElement = document.getElementById('page-title');
            if (pageTitleElement) {
                pageTitleElement.textContent = title;
            }
        }
        // Call the function when the page loads
        updatePageTitle();
    });
</script>
<script src="{{ asset('js/sidebar-active.js') }}"></script>

<style>
    .sidebar {
        color: #fff;
    }

    .logo-text {
        color: #fff;
    }

    .nav-item {
        color: #cbd5e0;
    }

    .nav-item:hover {
        color: #fff;
    }

    .dropdown-content {
        max-height: 0;
        overflow: hidden;
        transition: all 0.3s;
    }

    .dropdown-content.show {
        max-height: 500px;
        overflow-y: auto;
    }

    /* Đặc biệt cho menu báo cáo do có nhiều mục */
    .dropdown-content.reports-menu.show {
        overflow-y: auto;
    }

    .dropdown-content a {
        color: #a0aec0;
        padding: 8px 12px;
        font-size: 14px;
    }

    .dropdown-content a:hover {
        color: #fff;
    }

    .nav-item.active,
    .dropdown-content a.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    /* Dropdown menu styles for header */
    .dropdown-menu {
        position: absolute;
        transition: all 0.3s ease;
    }

    /* Style for content area to accommodate the fixed header and sidebar */
    .content-area {
        margin-left: 256px;
        padding-top: 72px;
        /* Adjust based on your header height */
    }
</style>
