<div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">STT</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã phiếu
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thành phẩm
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Người phụ
                    trách</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ngày lắp ráp
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Thao tác
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($assemblies as $index => $assembly)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $index + 1 }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm">{{ $assembly->code }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            @if ($assembly->products && $assembly->products->count() > 0)
                                @foreach ($assembly->products as $assemblyProduct)
                                    <div class="mb-1">
                                        <span>{{ $assemblyProduct->product->name ?? 'N/A' }}</span>
                                        <span class="text-xs text-gray-500">({{ $assemblyProduct->quantity }})</span>
                                    </div>
                                @endforeach
                            @else
                                <!-- Legacy support -->
                                <div>
                                    <span class="font-medium">{{ $assembly->product->name ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500">({{ $assembly->quantity ?? 1 }})</span>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $assembly->assignedEmployee->name ?? 'Chưa phân công' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $assembly->created_at ? $assembly->created_at->format('d/m/Y') : '' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $assembly->notes ?? '' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if ($assembly->status == 'completed') bg-green-100 text-green-800
                            @elseif($assembly->status == 'approved') bg-emerald-100 text-emerald-800
                            @elseif($assembly->status == 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($assembly->status == 'pending') bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800 @endif">
                            @if ($assembly->status == 'completed')
                                Hoàn thành
                            @elseif($assembly->status == 'approved')
                                Đã duyệt
                            @elseif($assembly->status == 'in_progress')
                                Đang thực hiện
                            @elseif($assembly->status == 'pending')
                                Chờ xử lý
                            @else
                                Đã hủy
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center space-x-2">
                        @php
                            $user = auth()->user();
                            $isAdmin = $user->role === 'admin';
                            $isOwner = $assembly->assigned_employee_id == $user->id;
                        @endphp
                        @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('assembly.view_detail')))
                            <a href="{{ route('assemblies.show', $assembly->id) }}">
                                <button
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                    title="Xem chi tiết">
                                    <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                                </button>
                            </a>
                        @endif
                        @if ($assembly->status !== 'cancelled')
                            @if ($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('assembly.edit')))
                                <a href="{{ route('assemblies.edit', $assembly->id) }}">
                                    <button
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                        title="Sửa">
                                        <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                                    </button>
                                </a>
                            @endif
                        @endif
                        @if ($assembly->status === 'pending' && ($isAdmin || $isOwner))
                            <form action="{{ route('assemblies.approve', $assembly->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 hover:bg-green-500 transition-colors group"
                                    title="Duyệt phiếu"
                                    onclick="return confirm('Bạn có chắc chắn muốn duyệt phiếu lắp ráp này? Khi duyệt sẽ tạo phiếu kiểm thử và xuất kho.')">
                                    <i class="fas fa-check text-green-500 group-hover:text-white"></i>
                                </button>
                            </form>
                        @endif
                        @if ($assembly->status === 'pending' && ($isAdmin || $isOwner))
                            <form action="{{ route('assemblies.cancel', $assembly->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                    title="Huỷ phiếu"
                                    onclick="return confirm('Bạn có chắc chắn muốn huỷ phiếu lắp ráp này?')">
                                    <i class="fas fa-times text-red-500 group-hover:text-white"></i>
                                </button>
                            </form>
                        @endif
                        @if (
                            $assembly->status === 'cancelled' &&
                                ($isAdmin || ($user->roleGroup && $user->roleGroup->hasPermission('assembly.delete'))))
                            <button type="button"
                                class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 hover:bg-red-500 transition-colors group"
                                title="Xóa"
                                onclick="showDeleteConfirm('{{ $assembly->id }}', '{{ $assembly->code }}')">
                                <i class="fas fa-trash text-red-500 group-hover:text-white"></i>
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                            <span>Không có phiếu lắp ráp nào</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Xác nhận xóa -->
<div id="deleteConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Xác nhận xóa</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Bạn có chắc chắn muốn xóa phiếu lắp ráp <span id="assemblyCode" class="font-semibold"></span>?
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    Hành động này không thể hoàn tác.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-50 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Xác nhận
                    </button>
                </form>
                <button onclick="hideDeleteConfirm()"
                    class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-50 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Huỷ
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showDeleteConfirm(assemblyId, assemblyCode) {
        document.getElementById('assemblyCode').textContent = assemblyCode;
        document.getElementById('deleteForm').action = `/assemblies/${assemblyId}`;
        document.getElementById('deleteConfirmModal').classList.remove('hidden');
    }

    function hideDeleteConfirm() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    }

    // Đóng modal khi click bên ngoài
    document.getElementById('deleteConfirmModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideDeleteConfirm();
        }
    });
</script>
