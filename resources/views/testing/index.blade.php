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
                        <input type="text" name="search" placeholder="Tìm kiếm mã phiếu, thiết bị..."
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 w-full md:w-64 h-10"
                            value="{{ request('search') }}">
                        <select name="test_type"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700 h-10">
                            <option value="">Loại kiểm thử</option>
                            <option value="material" {{ request('test_type') == 'material' ? 'selected' : '' }}>Vật
                                tư/Hàng hóa</option>
                            <option value="finished_product"
                                {{ request('test_type') == 'finished_product' ? 'selected' : '' }}>Thiết bị thành phẩm
                            </option>
                        </select>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors h-10">
                            <i class="fas fa-search mr-2"></i> Tìm
                        </button>
                    </form>
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
                    <li>
                        <a href="{{ route('testing.index', ['status' => 'cancelled']) }}"
                            class="inline-block p-4 {{ request('status') == 'cancelled' ? 'text-blue-600 border-b-2 border-blue-600 active' : 'text-gray-500 hover:text-gray-600 hover:border-gray-300 border-b-2 border-transparent' }}">
                            Đã hủy
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
                                Mã phiếu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Loại kiểm thử</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Vật tư/Hàng hóa</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Serial/Mã</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Người kiểm thử</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Ngày kiểm thử</th>
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
                        @forelse($testings as $testing)
                            <tr class="hover:bg-gray-50">
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
                                        {{ $testing->items->first()->item_name }}
                                        @if ($testing->items->count() > 1)
                                            <span class="text-xs text-gray-500">(+{{ $testing->items->count() - 1 }}
                                                khác)</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if ($testing->items->isNotEmpty() && $testing->items->first()->serial_number)
                                        {{ $testing->items->first()->serial_number }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $testing->tester->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $testing->test_date->format('d/m/Y') }}</td>
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

                                    @if ($testing->status == 'pending')
                                        @if ($canApprove)
                                            <form action="{{ route('testing.approve', $testing->id) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                                    title="Duyệt">
                                                    <i class="fas fa-check text-green-500 group-hover:text-white"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($canReject)
                                            <form action="{{ route('testing.reject', $testing->id) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                                    title="Từ chối">
                                                    <i class="fas fa-times text-red-500 group-hover:text-white"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    @if ($testing->status == 'in_progress')
                                        @if ($canReceive)
                                            <form action="{{ route('testing.receive', $testing->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-teal-100 hover:bg-teal-500 transition-colors group"
                                                    title="Tiếp nhận">
                                                    <i
                                                        class="fas fa-clipboard-check text-teal-500 group-hover:text-white"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($canComplete)
                                            <button data-testing-id="{{ $testing->id }}"
                                                onclick="openCompleteModal(this.dataset.testingId)"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                                title="Hoàn thành">
                                                <i
                                                    class="fas fa-flag-checkered text-green-500 group-hover:text-white"></i>
                                            </button>
                                        @endif
                                    @endif

                                    @if ($testing->status == 'completed' && !$testing->is_inventory_updated && $canUpdateInventory)
                                        <button data-testing-id="{{ $testing->id }}"
                                            data-test-code="{{ $testing->test_code }}"
                                            onclick="openUpdateInventory(this.dataset.testingId, this.dataset.testCode)"
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

                                    @if ($testing->status != 'completed' && $canDelete)
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
                                <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ
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
                    <p class="mb-4 text-sm text-gray-600">Cập nhật kết quả kiểm thử <span id="test_code_display"
                            class="font-semibold"></span> vào kho</p>

                    <div class="mb-4">
                        <label for="success_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Kho lưu
                            thiết bị Đạt</label>
                        <select id="success_warehouse_id" name="success_warehouse_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            required>
                            <option value="">-- Chọn kho --</option>
                            @foreach (\App\Models\Warehouse::all() as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="fail_warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Kho lưu
                            thiết bị Không đạt</label>
                        <select id="fail_warehouse_id" name="fail_warehouse_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                            required>
                            <option value="">-- Chọn kho --</option>
                            @foreach (\App\Models\Warehouse::all() as $warehouse)
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
        // Khởi tạo modal xóa
        document.addEventListener('DOMContentLoaded', function() {
            initDeleteModal();
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
            // Kiểm tra xem có thiết bị hoặc hạng mục nào đang pending không
            fetch(`/testing/${testingId}/check-pending`)
                .then(response => response.json())
                .then(data => {
                    if (data.has_pending) {
                        let message = "Không thể hoàn thành phiếu kiểm thử:";

                        if (data.pending_items > 0) {
                            message += "\n- Còn " + data.pending_items + " thiết bị chưa có kết quả đánh giá";
                        }
                        if (data.pending_details > 0) {
                            message += "\n- Còn " + data.pending_details + " hạng mục kiểm thử chưa có kết quả";
                        }

                        message += "\n\nVui lòng cập nhật đầy đủ kết quả trước khi hoàn thành.";

                        alert(message);
                        return;
                    }

                    // Nếu không có gì pending, hiển thị modal xác nhận
                    document.getElementById('completeTestingForm').action = `/testing/${testingId}/complete`;
                    document.getElementById('completeTestingModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi kiểm tra trạng thái phiếu kiểm thử.');
                });
        }

        function closeCompleteModal() {
            document.getElementById('completeTestingModal').classList.add('hidden');
        }

        // Update Inventory Modal functions
        function openUpdateInventory(testingId, testCode) {
            document.getElementById('testing_id').value = testingId;
            document.getElementById('test_code_display').textContent = testCode;
            document.getElementById('updateInventoryForm').action = `/testing/${testingId}/update-inventory`;
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
