<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <!-- Thêm thư viện jsPDF và html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- Thay thế thư viện xlsx cũ bằng phiên bản hoạt động -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, #1a365d 0%, #0f2942 100%);
            transition: all 0.3s ease;
        }

        .sidebar .menu-item a {
            color: #fff;
            transition: all 0.3s ease;
        }

        .sidebar .menu-item a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar .menu-item a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #3b82f6;
            color: #fff;
        }

        .sidebar .nav-item {
            color: #fff;
            transition: all 0.3s ease;
        }

        .sidebar .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #3b82f6;
        }

        .sidebar .dropdown-content a {
            color: #fff;
            transition: all 0.3s ease;
        }

        .sidebar .dropdown-content a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .dropdown-content a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #3b82f6;
            color: #fff;
            font-weight: 500;
        }

        .dropdown-content {
            display: none;
        }

        .dropdown-content.show {
            display: block;
        }

        .sidebar .logo-text {
            color: #fff;
            font-weight: 600;
        }

        .sidebar .logo-icon {
            color: #fff;
        }

        .sidebar .search-input {
            color: #e2e8f0;
        }

        .sidebar .search-input::placeholder {
            color: #94a3b8;
        }

        .sidebar .user-info {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .user-info p {
            color: #e2e8f0;
        }

        .sidebar .user-info .role {
            color: #ffffff;
        }

        .content-area {
            margin-left: 256px;
            min-height: 100vh;

            transition: margin-left 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
                width: 70px;
            }

            .content-area {
                margin-left: 0 !important;
            }
        }

        .sidebar .nav-item .flex {
            color: #fff;
        }

        .sidebar .flex {
            color: #fff;
        }

        .sidebar .dropdown button .flex {
            color: #fff;
        }

        /* Modal overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background-color: white;
            border-radius: 0.5rem;
            max-width: 500px;
            width: 90%;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal-overlay.show .modal {
            transform: scale(1);
        }
    </style>
</head>

<body>
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý khách hàng</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <form action="{{ route('customers.index') }}" method="GET"
                    class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                    <div class="relative flex-grow">
                        <input type="text" name="search" placeholder="Tìm kiếm theo tên, số điện thoại..."
                            class="border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full"
                            value="{{ $search ?? '' }}" />
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    <select name="filter"
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                        <option value="">Tất cả</option>
                        <option value="name" {{ ($filter ?? '') == 'name' ? 'selected' : '' }}>Tên người đại diện
                        </option>
                        <option value="company_name" {{ ($filter ?? '') == 'company_name' ? 'selected' : '' }}>Tên công
                            ty</option>
                        <option value="phone" {{ ($filter ?? '') == 'phone' ? 'selected' : '' }}>Số điện thoại</option>
                        <option value="email" {{ ($filter ?? '') == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="address" {{ ($filter ?? '') == 'address' ? 'selected' : '' }}>Địa chỉ</option>
                    </select>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i> Tìm kiếm
                    </button>
                    @if ($search || $filter)
                        <a href="{{ route('customers.index') }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i> Xóa bộ lọc
                        </a>
                    @endif
                </form>
                <div class="">
                    <div class="relative inline-block text-left">
                        @php
                            $user = Auth::guard('web')->user();
                            $canExport =
                                $user &&
                                ($user->role === 'admin' ||
                                    ($user->role_id &&
                                        $user->roleGroup &&
                                        $user->roleGroup->hasPermission('customers.export')));
                        @endphp
                        @if ($canExport)
                            <button id="exportDropdownButton" type="button"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                                <i class="fas fa-download mr-2"></i> Xuất dữ liệu
                                <i class="fas fa-chevron-down ml-2"></i>
                            </button>
                            <div id="exportDropdown"
                                class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                <div class="py-1">
                                    <button id="exportExcelButton"
                                        class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <i class="far fa-file-excel text-green-500 mr-2"></i> Xuất Excel
                                    </button>
                                    <button id="exportPdfButton"
                                        class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <i class="far fa-file-pdf text-red-500 mr-2"></i> Xuất PDF
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @php
                    $canCreate =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id &&
                                $user->roleGroup &&
                                $user->roleGroup->hasPermission('customers.create')));
                @endphp
                @if ($canCreate)
                    <a href="{{ route('customers.create') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                        <i class="fas fa-user-plus mr-2"></i> Thêm khách hàng
                    </a>
                @endif
            </div>
        </header>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" />
        @endif
        @if (session('error'))
            <x-alert type="error" :message="session('error')" />
        @endif
        <main class="p-6">
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên người đại diện</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Công ty</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                SĐT người đại diện</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Email</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Địa chỉ công ty</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ngày tạo</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($customers as $key => $customer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $customers->firstItem() + $key }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $customer->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $customer->company_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $customer->phone }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $customer->email ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $customer->address ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $customer->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    @php
                                        $user = Auth::guard('web')->user();
                                        $canViewDetail =
                                            $user &&
                                            ($user->role === 'admin' ||
                                                ($user->role_id &&
                                                    $user->roleGroup &&
                                                    $user->roleGroup->hasPermission('customers.view_detail')));
                                        $canEdit =
                                            $user &&
                                            ($user->role === 'admin' ||
                                                ($user->role_id &&
                                                    $user->roleGroup &&
                                                    $user->roleGroup->hasPermission('customers.edit')));
                                        $canDelete =
                                            $user &&
                                            ($user->role === 'admin' ||
                                                ($user->role_id &&
                                                    $user->roleGroup &&
                                                    $user->roleGroup->hasPermission('customers.delete')));
                                        $canManage =
                                            $user &&
                                            ($user->role === 'admin' ||
                                                ($user->role_id &&
                                                    $user->roleGroup &&
                                                    $user->roleGroup->hasPermission('customers.manage')));
                                    @endphp

                                    @if ($canViewDetail)
                                        <a href="{{ route('customers.show', $customer->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem chi tiết">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($canEdit)
                                        <a href="{{ route('customers.edit', $customer->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                            title="Chỉnh sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($canDelete)
                                        <button
                                            onclick="openDeleteModal('{{ $customer->id }}', '{{ $customer->name }}')"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                            title="Xóa">
                                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                        </button>
                                    @endif

                                    @if ($canManage)
                                        @if (!$customer->has_account)
                                            <a href="{{ route('customers.activate', $customer->id) }}"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                                title="Kích hoạt tài khoản">
                                                <i class="fas fa-user-check text-green-500 group-hover:text-white"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('customers.toggle-lock', $customer->id) }}"
                                                class="w-8 h-8 flex items-center justify-center rounded-full {{ $customer->is_locked ? 'bg-orange-100 hover:bg-orange-500' : 'bg-gray-100 hover:bg-gray-500' }} transition-colors group"
                                                title="{{ $customer->is_locked ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}">
                                                <i
                                                    class="fas {{ $customer->is_locked ? 'fa-unlock' : 'fa-lock' }} {{ $customer->is_locked ? 'text-orange-500' : 'text-gray-500' }} group-hover:text-white"></i>
                                            </a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ
                                    liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Hiển thị {{ $customers->firstItem() ?? 0 }}-{{ $customers->lastItem() ?? 0 }} của
                    {{ $customers->total() ?? 0 }} mục
                </div>
                <div class="flex space-x-1">
                    {{ $customers->links() }}
                </div>
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

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Khởi tạo modal khi trang được load
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
        });

        // Ghi đè hàm deleteCustomer trong file delete-modal.js để thực hiện xóa thật
        function deleteCustomer(id) {
            // Tạo form ẩn để gửi yêu cầu DELETE
            const form = document.createElement('form');
            form.action = `/customers/${id}`;
            form.method = 'POST';
            form.style.display = 'none';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);

            document.body.appendChild(form);

            // Đóng modal trước khi submit form
            closeDeleteModal();

            // Gửi form để xóa khách hàng
            form.submit();
        }

        // Toggle dropdown menu for export
        const exportDropdownButton = document.getElementById('exportDropdownButton');
        const exportDropdown = document.getElementById('exportDropdown');

        exportDropdownButton.addEventListener('click', function(e) {
            e.stopPropagation();
            exportDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            exportDropdown.classList.add('hidden');
        });

        // Prevent dropdown from closing when clicking inside
        exportDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Hàm xuất dữ liệu sang Excel
        document.getElementById('exportExcelButton').addEventListener('click', function() {
            // Hiển thị thông báo đang xử lý
            alert('Đang xử lý Excel, vui lòng đợi trong giây lát...');

            // Gửi yêu cầu AJAX để lấy toàn bộ dữ liệu
            fetch('{{ route("customers.export") }}')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Lỗi khi tải dữ liệu: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Có lỗi xảy ra khi tải dữ liệu');
                    }
                    
                    try {
                        // Tạo workbook mới và worksheet
                        const wb = XLSX.utils.book_new();
                        
                        // Chuẩn bị dữ liệu cho Excel với định dạng phù hợp
                        const excelData = [
                            ['ID', 'Tên khách hàng', 'Tên công ty', 'SĐT', 'Email', 'Địa chỉ', 'Ngày tạo']
                        ];
                        
                        data.customers.forEach(customer => {
                            excelData.push([
                                customer.id,
                                customer.name,
                                customer.company_name,
                                // Thêm dấu ' để đảm bảo Excel coi số điện thoại là text
                                "'" + customer.phone,
                                customer.email || '',
                                customer.address || '',
                                customer.created_at || ''
                            ]);
                        });
                        
                        // Tạo worksheet từ dữ liệu
                        const ws = XLSX.utils.aoa_to_sheet(excelData);
                        
                        // Điều chỉnh độ rộng cột
                        const wscols = [
                            {wch: 5},  // ID
                            {wch: 20}, // Tên khách hàng
                            {wch: 25}, // Tên công ty
                            {wch: 15}, // SĐT
                            {wch: 25}, // Email
                            {wch: 30}, // Địa chỉ
                            {wch: 15}  // Ngày tạo
                        ];
                        ws['!cols'] = wscols;
                        
                        // Định dạng tiêu đề - Tạo style cho header
                        const headerStyle = {
                            font: { bold: true, color: { rgb: "FFFFFF" } },
                            fill: { fgColor: { rgb: "4B90E2" } },
                            alignment: { horizontal: "center", vertical: "center" },
                            border: {
                                top: { style: "thin", color: { rgb: "000000" } },
                                bottom: { style: "thin", color: { rgb: "000000" } },
                                left: { style: "thin", color: { rgb: "000000" } },
                                right: { style: "thin", color: { rgb: "000000" } }
                            }
                        };
                        
                        // Định dạng nội dung
                        const dataStyle = {
                            border: {
                                top: { style: "thin", color: { rgb: "D3D3D3" } },
                                bottom: { style: "thin", color: { rgb: "D3D3D3" } },
                                left: { style: "thin", color: { rgb: "D3D3D3" } },
                                right: { style: "thin", color: { rgb: "D3D3D3" } }
                            },
                            alignment: { vertical: "center" }
                        };
                        
                        // Định dạng hàng chẵn/lẻ
                        const evenRowStyle = {
                            ...dataStyle,
                            fill: { fgColor: { rgb: "F9F9F9" } }
                        };
                        
                        // Áp dụng style cho header (hàng đầu tiên)
                        const range = XLSX.utils.decode_range(ws['!ref']);
                        for (let col = range.s.c; col <= range.e.c; col++) {
                            const cellAddress = XLSX.utils.encode_cell({ r: 0, c: col });
                            if (!ws[cellAddress]) continue;
                            ws[cellAddress].s = headerStyle;
                        }
                        
                        // Áp dụng style cho data (các hàng còn lại)
                        for (let row = 1; row <= range.e.r; row++) {
                            const rowStyle = row % 2 === 0 ? evenRowStyle : dataStyle;
                            for (let col = range.s.c; col <= range.e.c; col++) {
                                const cellAddress = XLSX.utils.encode_cell({ r: row, c: col });
                                if (!ws[cellAddress]) continue;
                                
                                // Style riêng cho cột ID
                                if (col === 0) {
                                    ws[cellAddress].s = { 
                                        ...rowStyle, 
                                        alignment: { horizontal: "center", vertical: "center" } 
                                    };
                                }
                                // Style riêng cho cột Ngày tạo
                                else if (col === 6) {
                                    ws[cellAddress].s = { 
                                        ...rowStyle, 
                                        alignment: { horizontal: "center", vertical: "center" } 
                                    };
                                } 
                                else {
                                    ws[cellAddress].s = rowStyle;
                                }
                            }
                        }
                        
                        // Thêm tiêu đề chính ở trên cùng
                        const titleWs = XLSX.utils.aoa_to_sheet([
                            ['DANH SÁCH KHÁCH HÀNG'],
                            ['Công ty: SGL - Hệ thống quản lý kho'],
                            [`Ngày xuất: ${new Date().toLocaleDateString('vi-VN')}`],
                            ['']  // Dòng trống
                        ]);
                        
                        // Style cho tiêu đề
                        const titleStyle = {
                            font: { bold: true, size: 16, color: { rgb: "000000" } },
                            alignment: { horizontal: "center" }
                        };
                        
                        const subTitleStyle = {
                            font: { size: 12, color: { rgb: "555555" } },
                            alignment: { horizontal: "center" }
                        };
                        
                        const dateStyle = {
                            font: { italic: true, size: 11, color: { rgb: "555555" } },
                            alignment: { horizontal: "center" }
                        };
                        
                        // Áp dụng style cho tiêu đề
                        titleWs['A1'].s = titleStyle;
                        titleWs['A2'].s = subTitleStyle;
                        titleWs['A3'].s = dateStyle;
                        
                        // Merge cells cho tiêu đề
                        titleWs['!merges'] = [
                            { s: { r: 0, c: 0 }, e: { r: 0, c: 6 } },
                            { s: { r: 1, c: 0 }, e: { r: 1, c: 6 } },
                            { s: { r: 2, c: 0 }, e: { r: 2, c: 6 } }
                        ];
                        
                        // Kết hợp 2 worksheet
                        const finalWs = XLSX.utils.sheet_add_json(titleWs, XLSX.utils.sheet_to_json(ws), {
                            origin: 'A5',
                            skipHeader: false
                        });
                        
                        // Cập nhật lại độ rộng cột
                        finalWs['!cols'] = wscols;
                        
                        // Thêm worksheet vào workbook
                        XLSX.utils.book_append_sheet(wb, finalWs, "Khách hàng");
                        
                        // Xuất file Excel
                        XLSX.writeFile(wb, 'danh_sach_khach_hang.xlsx');
                        
                        // Đóng dropdown
                        exportDropdown.classList.add('hidden');
                    } catch (err) {
                        console.error('Lỗi khi tạo Excel: ', err);
                        alert('Có lỗi xảy ra khi tạo file Excel. Vui lòng thử lại sau.');
                    }
                })
                .catch(error => {
                    console.error('Lỗi khi tải dữ liệu: ', error);
                    alert('Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại sau.');
                });
        });

        // Hàm xuất dữ liệu sang PDF bằng html2canvas
        document.getElementById('exportPdfButton').addEventListener('click', function() {
            // Hiển thị thông báo đang xử lý
            alert('Đang xử lý PDF, vui lòng đợi trong giây lát...');

            // Khởi tạo jsPDF
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4'); // landscape mode

            // Tạo bảng tạm thời để xuất PDF
            const originalTable = document.querySelector('table');
            const tempTable = originalTable.cloneNode(true);
            const tempDiv = document.createElement('div');
            tempDiv.style.position = 'absolute';
            tempDiv.style.left = '-9999px';
            tempDiv.style.top = '-9999px';
            tempDiv.style.width = '1500px'; // Tăng kích thước để đảm bảo tất cả cột hiển thị
            tempDiv.style.maxWidth = 'none'; // Đảm bảo không bị giới hạn bởi max-width 

            // Loại bỏ cột hành động
            const headerRow = tempTable.querySelector('thead tr');
            const lastHeaderCell = headerRow.lastElementChild;
            headerRow.removeChild(lastHeaderCell);

            tempTable.querySelectorAll('tbody tr').forEach(row => {
                if (row.lastElementChild) {
                    row.removeChild(row.lastElementChild);
                }
            });

            // Thêm CSS
            tempTable.style.width = '100%';
            tempTable.style.tableLayout = 'fixed'; // Đảm bảo bảng có kích thước cố định
            tempTable.style.borderCollapse = 'collapse';
            tempTable.style.fontSize = '14px'; // Giảm kích thước font để vừa với trang
            tempTable.style.color = '#333';

            // Thiết lập độ rộng cho từng cột
            const columnWidths = ['5%', '15%', '15%', '15%', '15%', '20%', '15%'];
            tempTable.querySelectorAll('thead th').forEach((th, index) => {
                if (index < columnWidths.length) {
                    th.style.width = columnWidths[index];
                }
                th.style.backgroundColor = '#4b90e2';
                th.style.color = 'white';
                th.style.padding = '8px';
                th.style.textAlign = 'left';
                th.style.fontWeight = 'bold';
                th.style.border = '1px solid #ddd';
                th.style.overflow = 'hidden';
                th.style.whiteSpace = 'normal'; // Cho phép text xuống dòng
                th.style.wordWrap = 'break-word';
            });

            // Thiết lập CSS cho cells
            tempTable.querySelectorAll('tbody td').forEach((td, index) => {
                if (index % 7 < columnWidths.length) {
                    td.style.width = columnWidths[index % 7];
                }
                td.style.padding = '8px';
                td.style.border = '1px solid #ddd';
                td.style.overflow = 'hidden';
                td.style.whiteSpace = 'normal'; // Cho phép text xuống dòng
                td.style.wordWrap = 'break-word';
                if (td.parentElement.rowIndex % 2 === 0) {
                    td.style.backgroundColor = '#f9f9f9';
                }
            });

            // Tạo header
            const header = document.createElement('h2');
            header.textContent = 'DANH SÁCH KHÁCH HÀNG';
            header.style.textAlign = 'center';
            header.style.margin = '20px 0';
            header.style.fontWeight = 'bold';
            header.style.fontSize = '18px';

            // Tạo ngày xuất
            const today = new Date();
            const dateStr = `${today.getDate()}/${today.getMonth() + 1}/${today.getFullYear()}`;
            const dateDiv = document.createElement('div');
            dateDiv.textContent = `Ngày xuất: ${dateStr}`;
            dateDiv.style.textAlign = 'right';
            dateDiv.style.margin = '10px 0';

            // Tạo footer
            const footerDiv = document.createElement('div');
            footerDiv.style.borderTop = '1px solid #ddd';
            footerDiv.style.marginTop = '20px';
            footerDiv.style.paddingTop = '10px';
            footerDiv.style.display = 'flex';
            footerDiv.style.justifyContent = 'space-between';
            
            const footerLeft = document.createElement('div');
            footerLeft.textContent = 'SGL - Hệ thống quản lý kho';
            footerLeft.style.fontSize = '10px';
            
            const footerRight = document.createElement('div');
            footerRight.textContent = 'Trang 1 / 1';
            footerRight.style.fontSize = '10px';
            
            footerDiv.appendChild(footerLeft);
            footerDiv.appendChild(footerRight);

            // Thêm tất cả vào div tạm thời
            tempDiv.appendChild(header);
            tempDiv.appendChild(dateDiv);
            tempDiv.appendChild(tempTable);
            tempDiv.appendChild(footerDiv);
            document.body.appendChild(tempDiv);

            // Sử dụng html2canvas để chụp bảng
            html2canvas(tempDiv, {
                scale: 2, // Tăng scale để cải thiện chất lượng
                useCORS: true,
                logging: false,
                allowTaint: true,
                width: tempDiv.offsetWidth,
                height: tempDiv.offsetHeight
            }).then(canvas => {
                // Xóa div tạm thời
                document.body.removeChild(tempDiv);

                // Tính toán tỷ lệ để vừa với trang PDF
                const imgWidth = 280;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;

                // Thêm ảnh vào PDF
                const imgData = canvas.toDataURL('image/png');
                doc.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);

                // Tải xuống PDF
                doc.save('danh-sach-khach-hang.pdf');

                // Đóng dropdown
                exportDropdown.classList.add('hidden');
            });
        });
    </script>
</body>

</html>
