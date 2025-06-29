<div class="bg-white rounded-xl shadow-md overflow-x-auto border border-gray-100">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    STT</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Mã phiếu</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Thành phẩm</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Người phụ trách</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Trạng thái</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Thao tác</th>
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
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if ($assembly->status == 'completed') bg-green-100 text-green-800
                            @elseif($assembly->status == 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($assembly->status == 'pending') bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800 @endif">
                            @if ($assembly->status == 'completed')
                                Hoàn thành
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
                        @endphp
                        @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('assembly.view_detail')))
                        <a href="{{ route('assemblies.show', $assembly->id) }}">
                            <button
                                class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 hover:bg-blue-500 transition-colors group"
                                title="Xem chi tiết">
                                <i class="fas fa-eye text-blue-500 group-hover:text-white"></i>
                            </button>
                        </a>
                        @endif
                        @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('assembly.edit')))
                        <a href="{{ route('assemblies.edit', $assembly->id) }}">
                            <button
                                class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 hover:bg-yellow-500 transition-colors group"
                                title="Sửa">
                                <i class="fas fa-edit text-yellow-500 group-hover:text-white"></i>
                            </button>
                        </a>
                        @endif
                        @if($isAdmin || (auth()->user()->roleGroup && auth()->user()->roleGroup->hasPermission('assembly.delete')))
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
