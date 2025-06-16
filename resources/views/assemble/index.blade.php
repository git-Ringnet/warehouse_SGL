<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SGL - Hệ thống quản lý kho</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body class="bg-gray-50">
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area ml-64">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row md:justify-between md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý lắp ráp</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <input type="text" placeholder="Tìm kiếm phiếu lắp ráp..."
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64" />
                    <select
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="inprogress">Đang xử lý</option>
                        <option value="completed">Hoàn thành</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                <a href="{{ route('assemblies.create') }}">
                    <button
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center">
                        <i class="fas fa-plus mr-2"></i> Tạo phiếu lắp ráp
                    </button>
                </a>
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
                                Mã Phiếu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                thành phẩm</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Người Phụ Trách</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Người Kiểm Thử</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ngày Tạo</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Trạng Thái</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($assemblies as $index => $assembly)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $assembly->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $assembly->product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $assembly->assignedEmployee->name ?? $assembly->assigned_to }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $assembly->tester->name ?? 'Chưa chỉ định' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ date('d/m/Y', strtotime($assembly->date)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($assembly->status == 'completed')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Hoàn
                                            thành</span>
                                    @elseif($assembly->status == 'in_progress')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Đang
                                            xử lý</span>
                                    @elseif($assembly->status == 'pending')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Chờ
                                            xử lý</span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Đã
                                            hủy</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    <a href="{{ route('assemblies.show', $assembly->id) }}">
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                    <a href="{{ route('assemblies.edit', $assembly->id) }}">
                                        <button
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                            title="Sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </button>
                                    </a>
                                    <form action="{{ route('assemblies.destroy', $assembly->id) }}" method="POST"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu lắp ráp này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                            title="Xóa">
                                            <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-sm text-gray-500 text-center">
                                    Không có phiếu lắp ráp nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    {{-- <script>
        // Toggle sidebar
        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const mobileMenuButton = document.getElementById('mobileMenuButton');

        let isCollapsed = false;

        toggleSidebar.addEventListener('click', function() {
            isCollapsed = !isCollapsed;
            if (isCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                content.classList.remove('content-full');
                content.classList.add('content-expanded');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                content.classList.remove('content-expanded');
                content.classList.add('content-full');
            }
        });

        // Mobile menu toggle
        mobileMenuButton.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-mobile-hidden');
        });

        // Initialize Charts
        const assemblyProgressCtx = document.getElementById('assemblyProgressChart').getContext('2d');
        const assemblyProgressChart = new Chart(assemblyProgressCtx, {
            type: 'line',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6'],
                datasets: [{
                    label: 'Đã Hoàn Thành',
                    data: [15, 18, 20, 22, 25, 28],
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Đang Xử Lý',
                    data: [5, 4, 6, 4, 3, 4],
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Chờ Xử Lý',
                    data: [3, 2, 1, 2, 1, 2],
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        const productTypeCtx = document.getElementById('productTypeChart').getContext('2d');
        const productTypeChart = new Chart(productTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Radio SPA Pro', 'Radio SPA Lite', 'Radio SPA Mini', 'Radio SPA Plus'],
                datasets: [{
                    data: [40, 25, 20, 15],
                    backgroundColor: [
                        '#3B82F6',
                        '#10B981',
                        '#F59E0B',
                        '#EF4444'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                cutout: '70%'
            }
        });

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
    </script> --}}
</body>

</html>
