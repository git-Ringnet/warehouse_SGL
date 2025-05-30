<!-- Sidebar -->
<div class="sidebar w-64 fixed top-0 left-0 h-screen overflow-y-auto shadow-lg z-50">
    <div class="p-4 flex items-center justify-between border-b border-gray-700">
        <div class="flex items-center">
            <i class="fas fa-warehouse text-2xl mr-3" style="color: #fff"></i>
            <span class="logo-text text-xl font-bold">SGL WMS</span>
        </div>
        <button id="toggleSidebar" class="text-white focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <div class="p-4">
        <div class="relative mb-4">
            <input type="text" placeholder="Tìm kiếm..."
                class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 placeholder-gray-400" />
            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
        </div>
        <ul class="space-y-2">
            <li>
                <a href="{{asset('')}}" class="nav-item flex items-center px-4 py-3 rounded-lg active">
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
                            <a href="suppliers.html" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Nhà Cung
                                Cấp</a>
                        </li>
                        <li>
                            <a href="employees.html" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Nhân
                                Viên</a>
                        </li>
                        <li>
                            <a href="{{ asset('materials') }}" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Vật Tư</a>
                        </li>
                        <li>
                            <a href="products.html" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Thành
                                Phẩm</a>
                        </li>
                        <li>
                            <a href="warehouses.html" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Kho
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
                    <button onclick="toggleDropdown('testing')"
                        class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                        <div class="flex items-center">
                            <i class="fas fa-vial mr-3"></i>
                            <span class="nav-text">Kiểm Thử</span>
                        </div>
                    </button>
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
                            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Nhập Kho</a>
                        </li>
                        <li>
                            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Xuất Kho</a>
                        </li>
                        <li>
                            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Chuyển Kho</a>
                        </li>
                        <li>
                            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Sửa chữa - bảo
                                hành</a>
                        </li>
                        <li>
                            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Bảo hành điện
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
                            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Quản Lý Dự
                                Án</a>
                        </li>
                        <li>
                            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-gray-700">Quản Lý Cho
                                Thuê</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <div class="dropdown">
                    <button onclick="toggleDropdown('changeLog')"
                        class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                        <div class="flex items-center">
                            <i class="fas fa-history mr-3"></i>
                            <span class="nav-text">Nhật Ký Thay Đổi</span>
                        </div>
                    </button>
                </div>
            </li>
            <li>
                <div class="dropdown">
                    <button onclick="toggleDropdown('software')"
                        class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                        <div class="flex items-center">
                            <i class="fas fa-laptop-code mr-3"></i>
                            <span class="nav-text">Phần mềm</span>
                        </div>
                    </button>
                </div>
            </li>
            <li>
                <div class="dropdown">
                    <button onclick="toggleDropdown('requestForm')"
                        class="nav-item flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-700">
                        <div class="flex items-center">
                            <i class="fas fa-file-alt mr-3"></i>
                            <span class="nav-text">Gửi phiếu yêu cầu</span>
                        </div>
                    </button>
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
                    </button>
                </div>
            </li>
        </ul>
    </div>
</div>