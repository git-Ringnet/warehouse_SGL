<table class="min-w-full bg-white border border-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(1)">
                Mã vật tư <i class="fas fa-sort text-gray-300 ml-1"></i>
            </th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(2)">
                Tên vật tư <i class="fas fa-sort text-gray-300 ml-1"></i>
            </th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Đơn vị</th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(4)">
                Tồn đầu kỳ <i class="fas fa-sort text-gray-300 ml-1"></i>
            </th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(5)">
                Nhập <i class="fas fa-sort text-gray-300 ml-1"></i>
            </th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(6)">
                Xuất <i class="fas fa-sort text-gray-300 ml-1"></i>
            </th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(7)">
                Tồn cuối kỳ <i class="fas fa-sort text-gray-300 ml-1"></i>
            </th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(8)">
                Tồn hiện tại <i class="fas fa-sort text-gray-300 ml-1"></i>
            </th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(9)">
                Chênh lệch <i class="fas fa-sort text-gray-300 ml-1"></i>
            </th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" onclick="sortTable(10)">
                Hư hỏng <i class="fas fa-sort text-gray-300 ml-1"></i>
            </th>
            <th class="py-3 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @forelse($reportData as $index => $item)
        <tr class="hover:bg-gray-50">
            <td class="py-3 px-4 text-sm text-gray-900">{{ $loop->index + 1 }}</td>
            <td class="py-3 px-4 text-sm text-gray-900 font-medium">{{ $item['item_code'] }}</td>
            <td class="py-3 px-4 text-sm text-gray-900">{{ $item['item_name'] }}</td>
            <td class="py-3 px-4 text-sm text-gray-900">{{ $item['item_unit'] }}</td>
            <td class="py-3 px-4 text-sm text-gray-900">{{ number_format($item['opening_stock']) }}</td>
            <td class="py-3 px-4 text-sm text-gray-900 text-green-600">
                @if($item['imports'] > 0)
                    +{{ number_format($item['imports']) }}
                @else
                    0
                @endif
            </td>
            <td class="py-3 px-4 text-sm text-gray-900 text-red-600">
                @if($item['exports'] > 0)
                    -{{ number_format($item['exports']) }}
                @else
                    0
                @endif
            </td>
            <td class="py-3 px-4 text-sm text-gray-900 font-medium">{{ number_format($item['closing_stock']) }}</td>
            <td class="py-3 px-4 text-sm text-gray-900 font-medium">{{ number_format($item['current_stock']) }}</td>
            <td class="py-3 px-4 text-sm font-medium {{ ($item['current_stock'] - $item['closing_stock']) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                @php
                    $difference = $item['current_stock'] - $item['closing_stock'];
                @endphp
                @if($difference >= 0)
                    +{{ number_format($difference) }}
                @else
                    {{ number_format($difference) }}
                @endif
            </td>
            <td class="py-3 px-4 text-sm text-gray-900 text-orange-600 font-medium">
                @if($item['damaged_quantity'] > 0)
                    {{ number_format($item['damaged_quantity']) }}
                @else
                    0
                @endif
            </td>
            <td class="py-3 px-4 text-sm">
                <a href="{{ route('materials.show', $item['item_id']) }}" class="text-blue-500 hover:text-blue-700 mr-2" title="Xem chi tiết">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="#" onclick="openHistoryModal({{ $item['item_id'] }})" class="text-gray-500 hover:text-gray-700" title="Lịch sử">
                    <i class="fas fa-history"></i>
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="12" class="py-8 px-4 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-4 text-gray-400"></i>
                <p class="text-lg font-medium">Không có dữ liệu</p>
                <p class="text-sm">Thử thay đổi bộ lọc để xem kết quả khác</p>
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($reportData->count() > 0)
    <tfoot class="bg-gray-50">
        <tr>
            <td colspan="4" class="py-3 px-4 text-sm font-medium text-gray-700 text-right">Tổng:</td>
            <td class="py-3 px-4 text-sm font-medium text-gray-700">{{ number_format($reportData->sum('opening_stock')) }}</td>
            <td class="py-3 px-4 text-sm font-medium text-green-600">+{{ number_format($reportData->sum('imports')) }}</td>
            <td class="py-3 px-4 text-sm font-medium text-red-600">-{{ number_format($reportData->sum('exports')) }}</td>
            <td class="py-3 px-4 text-sm font-medium text-gray-700">{{ number_format($reportData->sum('closing_stock')) }}</td>
            <td class="py-3 px-4 text-sm font-medium text-gray-700">{{ number_format($reportData->sum('current_stock')) }}</td>
            <td class="py-3 px-4 text-sm font-medium {{ ($reportData->sum('current_stock') - $reportData->sum('closing_stock')) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                @php
                    $totalDifference = $reportData->sum('current_stock') - $reportData->sum('closing_stock');
                @endphp
                @if($totalDifference >= 0)
                    +{{ number_format($totalDifference) }}
                @else
                    {{ number_format($totalDifference) }}
                @endif
            </td>
            <td class="py-3 px-4 text-sm font-medium text-orange-600">{{ number_format($reportData->sum('damaged_quantity')) }}</td>
            <td class="py-3 px-4"></td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="mt-4 flex justify-between items-center">
    <div class="text-sm text-gray-500">
        @if($reportData->count() > 0)
            Hiển thị {{ $reportData->count() }} kết quả từ {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
        @else
            Không có kết quả
        @endif
    </div>
    <div class="text-sm text-gray-500">
        <i class="fas fa-clock mr-1"></i>
        Cập nhật lúc: {{ \Carbon\Carbon::now()->format('H:i:s d/m/Y') }}
    </div>
</div> 