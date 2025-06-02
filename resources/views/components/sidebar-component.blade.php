<!-- Sidebar -->
<div class="sidebar w-64 fixed top-0 left-0 overflow-y-auto shadow-lg z-50" style="bottom: 0; background: linear-gradient(180deg, #1a365d 0%, #0f2942 100%);">
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
        <div class="relative mb-4">
            <input type="text" placeholder="Tìm kiếm..."
                class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 placeholder-gray-400" />
            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
        </div>
        <ul class="space-y-2">
            <li>
                <a href="{{ asset('') }}" class="nav-item flex items-center px-4 py-3 rounded-lg">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    <span class="nav-text">Tổng Quan</span>
                </a>
            </li>
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
                        <li>
                            <a href="{{ asset('customers') }}"
                                class="block px-4 py-2 rounded-lg hover:bg-gray-700">Khách
                                Hàng</a>
                        </li>
                        <li>
                        <a href="{{ asset('suppliers') }}"
                                 class="block px-4 py-2 rounded-lg hover:bg-gray-700">Nhà Cung
                                Cấp</a>
                        </li>
                        <li>
                        <a href="{{ asset('employees') }}"
                         class="block px-4 py-2 rounded-lg hover:bg-gray-700">Nhân
                                Viên</a>
                        </li>
                        <li>
                            <a href="{{ asset('materials') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Vật
                                Tư</a>
                        </li>
                        <li>
                            <a href="{{ asset('products') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Thành
                                Phẩm</a>
                        </li>
                        <li>
                            <a href="{{ asset('warehouses') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Kho
                                Hàng</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <div class="dropdown">
                    <a href="{{ asset('assemble') }}">
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
                        <li>
                            <a href="{{ asset('inventory-imports') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Nhập Kho</a>
                        </li>
                        <li>
                            <a href="{{ asset('inventory') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Xuất
                                Kho</a>
                        </li>
                        <li>
                            <a href="{{ asset('warehouse-transfers') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Chuyển Kho</a>
                        </li>
                        <li>
                            <a href="{{ asset('warranties/repair_list') }}"
                                class="block px-4 py-2 rounded-lg hover:bg-gray-700">
                                Sửa chữa - bảo hành
                            </a>
                        </li>
                        <li>
                            <a href="{{ asset('warranties') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Bảo
                                hành điện
                                tử</a>
                        </li>
                    </ul>
                </div>
            </li>
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
                        <li>
                            <a href="{{ asset('projects') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Quản Lý Dự
                                Án</a>
                        </li>
                        <li>
                        <a href="{{ asset('rentals') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Quản Lý Cho
                                Thuê</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <div class="dropdown">
                    <a href="{{ asset('change_log') }}">
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
            <li>
                <a href="{{ asset('software') }}" class="nav-item flex items-center px-4 py-3 rounded-lg hover:bg-gray-700">
                    <i class="fas fa-laptop-code mr-3"></i>
                    <span class="nav-text">Phần mềm</span>
                </a>
            </li>
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
                    <ul id="reports" class="dropdown-content pl-4 mt-1 space-y-1">
                        <li>
                            <a href="{{ asset('report') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Báo
                                cáo tổng quan</a>
                        </li>
                        <li>
                            <a href="{{ asset('report/inventory_import') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Báo cáo vật tư nhập</a>
                        </li>
                        <li>
                            <a href="{{ asset('report/testing_verification') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Báo cáo vật tư kiểm thử cài đặt nghiệm thu</a>
                        </li>
                    </ul>
                </div>
            </li>
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
                        <li>
                            <a href="{{ asset('roles') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">
                                Nhóm quyền
                            </a>
                        </li>
                        <li>
                            <a href="{{ asset('permissions') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">
                                Danh sách quyền
                            </a>
                        </li>
                        <li>
                            <a href="{{ asset('user-logs') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">
                                Nhật ký người dùng
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</div>

<!-- JavaScript to fix sidebar height -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function adjustSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            const html = document.documentElement;
            
            // Get the maximum height of the page content
            const height = Math.max(
                body.scrollHeight,
                body.offsetHeight,
                html.clientHeight,
                html.scrollHeight,
                html.offsetHeight
            );
            
            // Set the sidebar height
            sidebar.style.height = height + 'px';
        }
        
        // Run on page load
        adjustSidebar();
        
        // Also run when window is resized
        window.addEventListener('resize', adjustSidebar);
        
        // Run again after a short delay to ensure all content is loaded
        setTimeout(adjustSidebar, 500);
    });

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
    });

    // Prevent dropdown from closing when clicking inside
    document.querySelectorAll(".dropdown-content").forEach((dropdown) => {
        dropdown.addEventListener("click", (e) => {
            e.stopPropagation();
        });
    });
</script>
<script src="{{ asset('js/sidebar-active.js') }}"></script>
