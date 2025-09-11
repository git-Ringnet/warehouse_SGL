<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý kiểm thử - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/delete-modal.js') }}"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
</head>

<body>
    <x-sidebar-component />

    <!-- Main Content -->
    <div class="content-area">
        <header
            class="bg-white shadow-sm py-4 px-6 flex flex-col md:flex-row justify-between items-start md:items-center sticky top-0 z-40 gap-4">
            <h1 class="text-xl font-bold text-gray-800">Quản lý kiểm thử</h1>
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex gap-2 w-full md:w-auto">
                    <form action="{{ route('testing.index') }}" method="GET" class="flex gap-2 w-full">
                        <input type="text" name="search" placeholder="Tìm kiếm mã phiếu, ghi chú, người tiếp nhận..."
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64 h-10"
                            value="{{ request('search') }}">
                        <select name="test_type"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 h-10">
                            <option value="">Tất cả loại kiểm thử</option>
                            <option value="material" {{ request('test_type') == 'material' ? 'selected' : '' }}>Vật
                                tư/Hàng hóa</option>
                            <option value="finished_product"
                                {{ request('test_type') == 'finished_product' ? 'selected' : '' }}>Thiết bị thành phẩm
                            </option>
                        </select>
                        <select name="status"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 h-10">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Đang thực hiện</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        </select>
                        <!-- Ẩn các tham số khác để giữ lại khi submit -->
                        @if(request('date_from'))
                            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        @endif
                        @if(request('date_to'))
                            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                        @endif
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors h-10">
                            <i class="fas fa-search mr-2"></i> Tìm
                        </button>
                    </form>
                </div>
                
                <!-- Bộ lọc nâng cao -->
                <div class="mt-2">
                    <button onclick="toggleAdvancedSearch()" class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                        <i class="fas fa-filter mr-1"></i> Bộ lọc nâng cao
                    </button>
                    <div id="advancedSearch" class="hidden mt-2 p-3 bg-gray-50 rounded-lg">
                        <form action="{{ route('testing.index') }}" method="GET" class="flex gap-2 items-end">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Từ ngày</label>
                                <input type="text" name="date_from" placeholder="DD/MM/YYYY"
                                    class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ request('date_from') }}">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Đến ngày</label>
                                <input type="text" name="date_to" placeholder="DD/MM/YYYY"
                                    class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="{{ request('date_to') }}">
                            </div>
                            <!-- Ẩn các tham số khác để giữ lại khi submit -->
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            @if(request('test_type'))
                                <input type="hidden" name="test_type" value="{{ request('test_type') }}">
                            @endif
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                <i class="fas fa-search mr-1"></i> Lọc
                            </button>
                            <a href="{{ route('testing.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                                <i class="fas fa-times mr-1"></i> Xóa bộ lọc
                            </a>
                        </form>
                    </div>
                </div>
                @php
                    $user = Auth::guard('web')->user();
                    $canCreate =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('testing.create')));
                    $canViewDetail =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id &&
                                $user->roleGroup &&
                                $user->roleGroup->hasPermission('testing.view_detail')));
                    $canEdit =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('testing.edit')));
                    $canDelete =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('testing.delete')));
                    $canApprove =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('testing.approve')));
                    $canReject =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('testing.reject')));
                    $canReceive =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('testing.receive')));
                    $canComplete =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id &&
                                $user->roleGroup &&
                                $user->roleGroup->hasPermission('testing.complete')));
                    $canUpdateInventory =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id &&
                                $user->roleGroup &&
                                $user->roleGroup->hasPermission('testing.update_inventory')));
                    $canPrint =
                        $user &&
                        ($user->role === 'admin' ||
                            ($user->role_id && $user->roleGroup && $user->roleGroup->hasPermission('testing.print')));
                @endphp

                @if ($canCreate)
                    <a href="{{ route('testing.create') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors w-full md:w-auto justify-center h-10">
                        <i class="fas fa-plus-circle mr-2"></i> Tạo phiếu kiểm thử
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


            <div class="mb-6">
                <ul class="flex flex-wrap text-sm font-medium text-center border-b border-gray-200">
                    <li class="mr-2">
                        <a href="{{ route('testing.index') }}"
                            class="inline-block p-4 {{ !request('status') ? 'text-blue-600 border-b-2 border-blue-600 active' : 'text-gray-500 hover:text-gray-600 hover:border-gray-300 border-b-2 border-transparent' }}">
                            Tất cả
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="{{ route('testing.index', ['status' => 'pending']) }}"
                            class="inline-block p-4 {{ request('status') == 'pending' ? 'text-blue-600 border-b-2 border-blue-600 active' : 'text-gray-500 hover:text-gray-600 hover:border-gray-300 border-b-2 border-transparent' }}">
                            Chờ xử lý
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="{{ route('testing.index', ['status' => 'in_progress']) }}"
                            class="inline-block p-4 {{ request('status') == 'in_progress' ? 'text-blue-600 border-b-2 border-blue-600 active' : 'text-gray-500 hover:text-gray-600 hover:border-gray-300 border-b-2 border-transparent' }}">
                            Đang thực hiện
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="{{ route('testing.index', ['status' => 'completed']) }}"
                            class="inline-block p-4 {{ request('status') == 'completed' ? 'text-blue-600 border-b-2 border-blue-600 active' : 'text-gray-500 hover:text-gray-600 hover:border-gray-300 border-b-2 border-transparent' }}">
                            Hoàn thành
                        </a>
                    </li>

                </ul>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                STT</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mã phiếu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Loại kiểm thử</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Vật tư / Hàng hoá / Thành phẩm</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Người tiếp nhận</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ngày kiểm thử</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ghi chú</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Trạng thái</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Kết quả</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($testings as $index => $testing)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $index + 1 + ($testings->currentPage() - 1) * $testings->perPage() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $testing->test_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span
                                        class="px-2 py-1 {{ $testing->test_type == 'material' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }} rounded text-xs">
                                        {{ $testing->test_type_text }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if ($testing->items->isNotEmpty())
                                        @php
                                            $item = $testing->items->first();
                                            $itemLabel = '';
                                            if ($item->item_type == 'material' && $item->material) {
                                                $itemLabel = $item->material->name;
                                            } elseif ($item->item_type == 'product' && $item->product) {
                                                $itemLabel = $item->product->name;
                                            } elseif ($item->item_type == 'product' && $item->good) {
                                                $itemLabel = $item->good->name;
                                            } elseif ($item->item_type == 'finished_product' && $item->good) {
                                                $itemLabel = $item->good->name;
                                            }
                                        @endphp
                                        {{ $itemLabel ?: 'N/A' }}
                                        @if ($testing->items->count() > 1)
                                            <span class="text-xs text-gray-500">(+{{ $testing->items->count() - 1 }}
                                                khác)</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $testing->receiverEmployee->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $testing->test_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php
                                        $notesDisplay = '';
                                        $raw = $testing->notes;
                                        if (!empty($raw)) {
                                            if (is_string($raw)) {
                                                $decoded = json_decode($raw, true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                    $notesDisplay = $decoded['general_note'] ?? ($decoded['note'] ?? ($decoded['notes'] ?? ''));
                                                } else {
                                                    $notesDisplay = $raw;
                                                }
                                            } elseif (is_array($raw)) {
                                                $notesDisplay = $raw['general_note'] ?? ($raw['note'] ?? '');
                                            }
                                        }
                                    @endphp
                                    {{ $notesDisplay ? Str::limit($notesDisplay, 80) : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span
                                        class="px-2 py-1 
                                    @if ($testing->status == 'pending') bg-yellow-100 text-yellow-800 
                                    @elseif($testing->status == 'in_progress') bg-blue-100 text-blue-800 
                                    @elseif($testing->status == 'completed') bg-green-100 text-green-800 
                                    @else bg-red-100 text-red-800 @endif 
                                    rounded text-xs">
                                        {{ $testing->status_text }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if ($testing->status == 'completed')
                                        <button onclick="showResultDetails('qa_{{ $testing->id }}')"
                                            class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs hover:bg-green-200">{{ $testing->pass_rate }}%
                                            Đạt</button>
                                        <div id="qa_{{ $testing->id }}"
                                            class="hidden absolute bg-white shadow-lg border rounded-lg p-3 z-50 mt-2 text-xs">
                                            <p>Số lượng đạt:
                                                {{ $testing->pass_quantity }}/{{ $testing->pass_quantity + $testing->fail_quantity }}
                                            </p>
                                            <p>Số lượng không đạt:
                                                {{ $testing->fail_quantity }}/{{ $testing->pass_quantity + $testing->fail_quantity }}
                                            </p>
                                        </div>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Chưa có</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                                    @if ($canViewDetail)
                                        <a href="{{ route('testing.show', $testing->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                            title="Xem">
                                            <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($testing->status != 'completed' && $testing->status != 'cancelled' && $canEdit)
                                        <a href="{{ route('testing.edit', $testing->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                            title="Sửa">
                                            <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($testing->status == 'pending' && $canReceive)
                                        <form action="{{ route('testing.receive', $testing->id) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-teal-100 hover:bg-teal-500 transition-colors group"
                                                title="Tiếp nhận">
                                                <i class="fas fa-clipboard-check text-teal-500 group-hover:text-white"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if ($testing->status == 'in_progress' && $canComplete)
                                        <button data-testing-id="{{ $testing->id }}"
                                            onclick="openCompleteModal(this.dataset.testingId)"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                            title="Hoàn thành">
                                            <i
                                                class="fas fa-flag-checkered text-green-500 group-hover:text-white"></i>
                                        </button>
                                    @endif

                                    @if ($testing->status == 'completed' && !$testing->is_inventory_updated && $canUpdateInventory)
                                        @php
                                            $isProject = $testing->assembly && ($testing->assembly->purpose === 'project');
                                            $projectLabel = '';
                                            if ($isProject) {
                                                $code = $testing->assembly->project_code ?? '';
                                                $name = $testing->assembly->project_name ?? '';
                                                $projectLabel = trim(($code ? ($code . ' - ') : '') . $name);
                                            }
                                        @endphp
                                        <button data-testing-id="{{ $testing->id }}"
                                            data-test-code="{{ $testing->test_code }}"
                                            data-test-type="{{ $testing->test_type }}"
                                            data-assembly-purpose="{{ $isProject ? 'project' : ($testing->assembly->purpose ?? '') }}"
                                            data-project-label="{{ $projectLabel }}"
                                            onclick="openUpdateInventory(this.dataset.testingId, this.dataset.testCode, this.dataset.testType, this.dataset.assemblyPurpose, this.dataset.projectLabel)"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 hover:bg-purple-500 transition-colors group"
                                            title="Cập nhật về kho">
                                            <i class="fas fa-warehouse text-purple-500 group-hover:text-white"></i>
                                        </button>
                                    @endif

                                    @if ($canPrint)
                                        <a href="{{ route('testing.print', $testing->id) }}"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                            title="In phiếu" target="_blank">
                                            <i class="fas fa-print text-green-500 group-hover:text-white"></i>
                                        </a>
                                    @endif

                                    @if ($testing->status != 'in_progress' && $testing->status != 'completed' && !$testing->assembly_id && $canDelete)
                                        <form action="{{ route('testing.destroy', $testing->id) }}" method="POST"
                                            id="delete-form-{{ $testing->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="openDeleteModal('{{ $testing->id }}', '{{ $testing->test_code }}')"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                title="Xóa">
                                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ
                                    liệu phiếu kiểm thử</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Hiển thị {{ $testings->firstItem() ?? 0 }}-{{ $testings->lastItem() ?? 0 }} của
                    {{ $testings->total() }} phiếu kiểm thử
                </div>
                <div>
                    {{ $testings->appends(request()->query())->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <!-- Complete Testing Modal -->
    <div id="completeTestingModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-800">Hoàn thành kiểm thử</h3>
                <button onclick="closeCompleteModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="completeTestingForm" method="POST">
                @csrf
                <div class="px-6 py-4">
                    <div class="mb-4">
                        <p class="text-gray-700">Bạn có chắc chắn muốn hoàn thành phiếu kiểm thử này?</p>
                        <p class="text-sm text-gray-600 mt-2">Hệ thống sẽ tự động tính toán kết quả dựa trên các hạng
                            mục kiểm thử đã nhập.</p>
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 flex justify-end rounded-b-lg">
                    <button type="button" onclick="closeCompleteModal()"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg mr-2">
                        Hủy
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Inventory Modal -->
    <div id="updateInventoryModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-800">Cập nhật vào kho</h3>
                <button onclick="closeUpdateInventoryModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="updateInventoryForm" method="POST">
                @csrf
                <div class="px-6 py-4">
                    <input type="hidden" id="testing_id" name="testing_id">
                    <input type="hidden" id="success_warehouse_hidden" name="success_warehouse_id" value="" disabled>
                    <input type="hidden" name="redirect_to" value="index">
                    <p class="mb-4 text-sm text-gray-600">Cập nhật kết quả kiểm thử <span id="test_code_display"
                            class="font-semibold"></span> vào kho</p>

                    <div id="success_block" class="mb-4">
                        <label for="success_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Kho lưu
                            thiết bị Đạt</label>
                        <select id="success_warehouse_id" name="success_warehouse_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            required>
                            <option value="">-- Chọn kho --</option>
                            <!-- Option Dự án xuất đi sẽ được thêm động nếu assembly purpose = project -->
                            @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="project_block" class="mb-4 hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dự án cho Thành phẩm đạt (không thể chỉnh sửa)</label>
                        <input id="project_label_display" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700" readonly value="">
                    </div>

                    <div class="mb-4">
                        <label for="fail_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Kho lưu
                            thiết bị Không đạt</label>
                        <select id="fail_warehouse_id" name="fail_warehouse_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            required>
                            <option value="">-- Chọn kho --</option>
                            @foreach (\App\Models\Warehouse::where('status', 'active')->get() as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 flex justify-end rounded-b-lg">
                    <button type="button" onclick="closeUpdateInventoryModal()"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg mr-2">
                        Hủy
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Khởi tạo modal xóa và flatpickr
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
            
            // Initialize flatpickr for date inputs
            const dateConfig = {
                locale: 'vn',
                dateFormat: 'd/m/Y',
                allowInput: true,
                disableMobile: true,
                monthSelectorType: 'static',
                yearSelectorType: 'static'
            };

            flatpickr('input[name="date_from"]', dateConfig);
            flatpickr('input[name="date_to"]', dateConfig);
        });

        // Hàm xóa phiếu kiểm thử
        function deleteCustomer(id) {
            document.getElementById(`delete-form-${id}`).submit();
        }

        // Show/hide result details
        function showResultDetails(id) {
            const element = document.getElementById(id);
            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
            } else {
                element.classList.add('hidden');
            }
        }

        // Complete Modal functions
        function openCompleteModal(testingId) {
            // Không kiểm tra pending qua API nữa, chỉ mở modal xác nhận luôn
            document.getElementById('completeTestingForm').action = `/testing/${testingId}/complete`;
            document.getElementById('completeTestingModal').classList.remove('hidden');
        }

        function closeCompleteModal() {
            document.getElementById('completeTestingModal').classList.add('hidden');
        }

        // Update Inventory Modal functions
        function openUpdateInventory(testingId, testCode, testType, assemblyPurpose = '', projectLabel = '') {
            document.getElementById('testing_id').value = testingId;
            document.getElementById('test_code_display').textContent = testCode;
            document.getElementById('updateInventoryForm').action = `/testing/${testingId}/update-inventory`;
            
            // Cập nhật label dựa trên loại kiểm thử
            const successLabel = document.querySelector('label[for="success_warehouse_id"]');
            const failLabel = document.querySelector('label[for="fail_warehouse_id"]');
            const successBlock = document.getElementById('success_block');
            const projectBlock = document.getElementById('project_block');
            const projectLabelDisplay = document.getElementById('project_label_display');
            
            if (testType === 'finished_product') {
                successLabel.textContent = 'Kho lưu Thành phẩm đạt';
                failLabel.textContent = 'Kho lưu Module Vật tư lắp ráp không đạt';
            } else {
                successLabel.textContent = 'Kho lưu thiết bị Đạt';
                failLabel.textContent = 'Kho lưu thiết bị Không đạt';
            }
            
            // Nếu assembly là dự án: ẩn chọn kho đạt, hiển thị dự án (read-only) và set hidden value gửi lên server
            const successSelect = document.getElementById('success_warehouse_id');
            const successHidden = document.getElementById('success_warehouse_hidden');
            if (assemblyPurpose === 'project') {
                successBlock.classList.add('hidden');
                projectBlock.classList.remove('hidden');
                projectLabelDisplay.value = projectLabel || 'Dự án xuất đi';
                // đảm bảo form vẫn gửi giá trị 'project_export' mà không bị validate required
                successSelect.required = false;
                successSelect.disabled = true;
                successSelect.value = '';
                // bật hidden input để submit
                successHidden.disabled = false;
                successHidden.value = 'project_export';
            } else {
                projectBlock.classList.add('hidden');
                successBlock.classList.remove('hidden');
                // reset nếu trước đó là project
                successSelect.required = true;
                successSelect.disabled = false;
                if (successSelect.value === 'project_export') successSelect.value = '';
                // tắt hidden input nếu không phải project
                successHidden.disabled = true;
                successHidden.value = '';
            }
            
            document.getElementById('updateInventoryModal').classList.remove('hidden');
        }

        function closeUpdateInventoryModal() {
            document.getElementById('updateInventoryModal').classList.add('hidden');
        }

        // Mở modal xác nhận xóa
        function openDeleteModal(id, testCode) {
            // Ghi đè hàm deleteCustomer từ delete-modal.js
            window.deleteCustomer = function(id) {
                document.getElementById(`delete-form-${id}`).submit();
            };

            document.getElementById('customerNameToDelete').innerText = `phiếu kiểm thử ${testCode}`;
            document.getElementById('confirmDeleteBtn').setAttribute('onclick', `deleteCustomer('${id}')`);
            document.getElementById('deleteModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        // Toggle advanced search
        function toggleAdvancedSearch() {
            const advancedSearch = document.getElementById('advancedSearch');
            if (advancedSearch.classList.contains('hidden')) {
                advancedSearch.classList.remove('hidden');
            } else {
                advancedSearch.classList.add('hidden');
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const completeModal = document.getElementById('completeTestingModal');
            const updateInventoryModal = document.getElementById('updateInventoryModal');

            if (event.target === completeModal) {
                closeCompleteModal();
            }

            if (event.target === updateInventoryModal) {
                closeUpdateInventoryModal();
            }
        }
    </script>
</body>

</html>
