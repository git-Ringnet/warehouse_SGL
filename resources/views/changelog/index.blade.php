<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhật ký thay đổi vật tư và thiết bị - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>

<body>
    @php
        $user = Auth::guard('web')->user();
        $canExport =
            $user &&
            ($user->role === 'admin' ||
                ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('change-logs.export')));
        $canViewDetail =
            $user &&
            ($user->role === 'admin' ||
                ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('change-logs.view_detail')));
    @endphp

    <x-sidebar-component />

    <!-- Main Content -->
    <div class="content-area">
        <header class="bg-white shadow-sm py-4 px-6 flex justify-between items-center sticky top-0 z-40">
            <h1 class="text-xl font-bold text-gray-800">Nhật ký thay đổi vật tư và thiết bị</h1>
            @if ($canExport)
                <div class="flex gap-2">
                    <button id="export-excel-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-file-excel mr-2"></i> Xuất Excel
                    </button>
                    <button id="export-pdf-btn"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i> Xuất PDF
                    </button>
                </div>
            @endif
        </header>

        <main class="p-6">
            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Bộ lọc
                </h2>

                <form method="GET" action="{{ route('change-logs.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="change_type" class="block text-sm font-medium text-gray-700 mb-1">Loại
                                hình</label>
                            <select name="change_type" id="change_type"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tất cả</option>
                                @php
                                    $filterTypes = [
                                        'xuat_kho' => 'Xuất kho',
                                        'thu_hoi' => 'Thu hồi',
                                        'nhap_kho' => 'Nhập kho',
                                        'chuyen_kho' => 'Chuyển kho'
                                    ];
                                @endphp
                                @foreach ($filterTypes as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ request('change_type') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
                            <input type="date" name="start_date" id="start_date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                value="{{ request('start_date') }}">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
                            <input type="date" name="end_date" id="end_date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                value="{{ request('end_date') }}">
                        </div>
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                            <input type="text" name="search" id="search" placeholder="Mã vật tư, tên, mô tả hoặc ghi chú..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-search mr-2"></i> Lọc
                        </button>
                        <a href="{{ route('change-logs.index') }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-redo mr-2"></i> Đặt lại
                        </a>
                    </div>
                </form>
            </div>

            <!-- Change Log Table -->
            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Thời gian</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mã Vật tư/Thành Phẩm/Hàng Hóa</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Tên Vật tư/Thành Phẩm/Hàng Hóa</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Loại hình</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mã phiếu</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Số lượng</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mô tả</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Người thực hiện</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ghi chú</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Xem Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($changeLogs as $changeLog)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ $changeLog->time_changed->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ $changeLog->item_code }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $changeLog->item_name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $badgeClass = match ($changeLog->change_type) {
                                            'lap_rap' => 'bg-green-100 text-green-800',
                                            'xuat_kho' => 'bg-blue-100 text-blue-800',
                                            'sua_chua' => 'bg-yellow-100 text-yellow-800',
                                            'thu_hoi' => 'bg-red-100 text-red-800',
                                            'nhap_kho' => 'bg-purple-100 text-purple-800',
                                            'chuyen_kho' => 'bg-indigo-100 text-indigo-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                        {{ $changeLog->getChangeTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ $changeLog->document_code ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ number_format($changeLog->quantity) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ Str::limit($changeLog->description, 50) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                    {{ $changeLog->performed_by }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ Str::limit($changeLog->notes, 50) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($canViewDetail)
                                        <button onclick="showDetails({{ $changeLog->id }})"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem chi tiết">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-folder-open text-4xl mb-4"></i>
                                    <p>Không có dữ liệu nhật ký thay đổi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($changeLogs->hasPages())
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Hiển thị {{ $changeLogs->firstItem() }} đến {{ $changeLogs->lastItem() }}
                        của {{ $changeLogs->total() }} bản ghi
                    </div>
                    <div class="flex space-x-2">
                        {{ $changeLogs->withQueryString()->links() }}
                    </div>
                </div>
            @endif
        </main>
    </div>

    <!-- Detail Modal -->
    <div id="detail-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Chi tiết thay đổi</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="modal-content" class="border-t border-gray-200 pt-4">
                    <!-- Content will be loaded here -->
                </div>

                <div class="mt-6 flex justify-end">
                    <button id="close-modal-btn"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal handling
        const detailModal = document.getElementById('detail-modal');
        const closeModalBtn = document.getElementById('close-modal-btn');
        const closeModalX = document.getElementById('close-modal');

        function showDetails(id) {
            fetch(`/change-logs/${id}/details`)
                .then(response => response.json())
                .then(data => {
                    const changeLog = data.change_log;
                    const detailedInfo = data.detailed_info;

                    let content = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Thời gian:</p>
                                <p class="text-sm text-gray-900">${new Date(changeLog.time_changed).toLocaleString('vi-VN')}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Mã vật tư:</p>
                                <p class="text-sm text-gray-900">${changeLog.item_code}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500">Tên vật tư:</p>
                                <p class="text-sm text-gray-900">${changeLog.item_name}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Loại hình:</p>
                                <p class="text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        ${data.change_type_label}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Mã phiếu:</p>
                                <p class="text-sm text-gray-900">${changeLog.document_code || '-'}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Số lượng:</p>
                                <p class="text-sm text-gray-900">${changeLog.quantity.toLocaleString()}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Người thực hiện:</p>
                                <p class="text-sm text-gray-900">${changeLog.performed_by}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500">Mô tả:</p>
                                <p class="text-sm text-gray-900">${changeLog.description || '-'}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500">Chú thích:</p>
                                <p class="text-sm text-gray-900">${changeLog.notes || '-'}</p>
                            </div>
                        </div>
                    `;

                    // Đã loại bỏ phần hiển thị thông tin chi tiết

                    document.getElementById('modal-content').innerHTML = content;
                    detailModal.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modal-content').innerHTML =
                        '<p class="text-red-500">Có lỗi xảy ra khi tải dữ liệu.</p>';
                    detailModal.classList.remove('hidden');
                });
        }

        closeModalBtn.addEventListener('click', function() {
            detailModal.classList.add('hidden');
        });

        closeModalX.addEventListener('click', function() {
            detailModal.classList.add('hidden');
        });

        // Close modal when clicking outside
        detailModal.addEventListener('click', function(e) {
            if (e.target === detailModal) {
                detailModal.classList.add('hidden');
            }
        });

        // Export Excel button
        document.getElementById('export-excel-btn').addEventListener('click', function() {
            const query = window.location.search;
            window.location.href = '{{ route('changelogs.export.excel') }}' + query;
        });

        // Export PDF button
        document.getElementById('export-pdf-btn').addEventListener('click', function() {
            const query = window.location.search;
            window.location.href = '{{ route('changelogs.export.pdf') }}' + query;
        });
    </script>
</body>

</html>
