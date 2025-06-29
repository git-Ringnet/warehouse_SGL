<table>
    <thead>
        <tr>
            <th colspan="7" style="text-align: center; font-size: 16px; font-weight: bold;">PHIẾU LẮP RÁP</th>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr>
            <th colspan="3">Mã phiếu: {{ $assembly->code }}</th>
            <th colspan="4">Ngày lắp ráp: {{ \Carbon\Carbon::parse($assembly->date)->format('d/m/Y') }}</th>
        </tr>
        <tr>
            <th colspan="3">Người phụ trách: {{ $assembly->assignedEmployee->name ?? $assembly->assigned_to }}</th>
            <th colspan="4">Người kiểm thử: {{ $assembly->tester->name ?? 'Chưa phân công' }}</th>
        </tr>
        <tr>
            <th colspan="3">Kho xuất vật tư: {{ $assembly->warehouse->name }} ({{ $assembly->warehouse->code }})</th>
            <th colspan="4">Kho nhập thành phẩm: {{ $assembly->targetWarehouse->name ?? '' }} ({{ $assembly->targetWarehouse->code ?? '' }})</th>
        </tr>
        <tr>
            <th colspan="3">Trạng thái: 
                @if ($assembly->status == 'completed')
                    Hoàn thành
                @elseif($assembly->status == 'in_progress')
                    Đang thực hiện
                @elseif($assembly->status == 'pending')
                    Chờ xử lý
                @else
                    Đã hủy
                @endif
            </th>
            <th colspan="4">Mục đích: 
                @if ($assembly->purpose == 'storage')
                    Lưu kho
                @elseif($assembly->purpose == 'project')
                    Xuất đi dự án
                @else
                    {{ $assembly->purpose ?? 'Không xác định' }}
                @endif
            </th>
        </tr>
        @if ($assembly->purpose == 'project' && isset($assembly->project))
        <tr>
            <th colspan="7">Dự án: {{ $assembly->project->project_name ?? '' }}</th>
        </tr>
        @endif
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: left; font-weight: bold;">THÀNH PHẨM</th>
        </tr>
        <tr>
            <th>STT</th>
            <th>Mã</th>
            <th>Tên thành phẩm</th>
            <th>Số lượng</th>
            <th colspan="3">Serial</th>
        </tr>
        @if ($assembly->products && $assembly->products->count() > 0)
            @foreach ($assembly->products as $index => $assemblyProduct)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $assemblyProduct->product->code }}</td>
                <td>{{ $assemblyProduct->product->name }}</td>
                <td>{{ $assemblyProduct->quantity }}</td>
                <td colspan="3">{{ $assemblyProduct->serials }}</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td>1</td>
                <td>{{ $assembly->product->code ?? '' }}</td>
                <td>{{ $assembly->product->name ?? 'Không có sản phẩm' }}</td>
                <td>{{ $assembly->quantity ?? '1' }}</td>
                <td colspan="3">{{ $assembly->product_serials ?? '' }}</td>
            </tr>
        @endif

        <tr>
            <th colspan="7"></th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: left; font-weight: bold;">DANH SÁCH VẬT TƯ</th>
        </tr>
        <tr>
            <th>STT</th>
            <th>Mã</th>
            <th>Loại vật tư</th>
            <th>Tên vật tư</th>
            <th>Số lượng</th>
            <th>Serial</th>
            <th>Ghi chú</th>
        </tr>
        @php
            $materialsByProduct = [];
            if ($assembly->products && $assembly->products->count() > 0) {
                foreach ($assembly->products as $product) {
                    $materialsByProduct[$product->product_id] = [
                        'product' => $product,
                        'materials' => [],
                    ];
                }
                foreach ($assembly->materials as $material) {
                    $productId = $material->target_product_id ?? $assembly->products->first()->product_id;
                    if (isset($materialsByProduct[$productId])) {
                        $materialsByProduct[$productId]['materials'][] = $material;
                    } else {
                        $firstProductId = $assembly->products->first()->product_id;
                        $materialsByProduct[$firstProductId]['materials'][] = $material;
                    }
                }
            } else {
                $materialsByProduct['legacy'] = [
                    'product' => $assembly->product,
                    'materials' => $assembly->materials->toArray(),
                ];
            }
        @endphp

        @foreach ($materialsByProduct as $productId => $productData)
            @if (count($productData['materials']) > 0)
                <tr>
                    <td colspan="7" style="font-weight: bold;">
                        @if ($productData['product'] && $productData['product']->product)
                            {{ $productData['product']->product->name }} ({{ $productData['product']->product->code }})
                        @else
                            Thành phẩm chưa xác định
                        @endif
                    </td>
                </tr>
                @foreach ($productData['materials'] as $index => $material)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ is_object($material) ? $material->material->code : $material['material']['code'] }}</td>
                    <td>{{ is_object($material) ? $material->material->category : $material['material']['category'] }}</td>
                    <td>{{ is_object($material) ? $material->material->name : $material['material']['name'] }}</td>
                    <td>{{ is_object($material) ? $material->quantity : $material['quantity'] }}</td>
                    <td>{{ is_object($material) ? $material->serial : $material['serial'] }}</td>
                    <td>{{ is_object($material) ? $material->note : $material['note'] }}</td>
                </tr>
                @endforeach
            @endif
        @endforeach

        @if ($assembly->notes)
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr>
            <th colspan="2">Ghi chú:</th>
            <td colspan="5">{{ $assembly->notes }}</td>
        </tr>
        @endif
    </thead>
</table> 