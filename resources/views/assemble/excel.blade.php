<table>
    <tr>
        <td colspan="6" style="text-align: center; font-weight: bold; font-size: 16px;">
            PHIẾU LẮP RÁP - {{ $assembly->code }}
        </td>
    </tr>
    <tr>
        <td colspan="6" style="text-align: center; font-size: 12px;">
            Ngày lắp ráp: {{ \Carbon\Carbon::parse($assembly->date)->format('d/m/Y') }}
        </td>
    </tr>
    <tr><td colspan="6"></td></tr>
    
    <!-- Thông tin chung -->
    <tr>
        <td style="font-weight: bold;">Mã phiếu:</td>
        <td>{{ $assembly->code }}</td>
        <td style="font-weight: bold;">Ngày lắp ráp:</td>
        <td>{{ \Carbon\Carbon::parse($assembly->date)->format('d/m/Y') }}</td>
        <td style="font-weight: bold;">Trạng thái:</td>
        <td>
            @if($assembly->status == 'completed') Hoàn thành
            @elseif($assembly->status == 'in_progress') Đang thực hiện  
            @elseif($assembly->status == 'pending') Chờ xử lý
            @else Đã hủy
            @endif
        </td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Người phụ trách:</td>
        <td>{{ $assembly->assignedEmployee->name ?? '' }}</td>
        <td style="font-weight: bold;">Người kiểm thử:</td>
        <td>{{ $assembly->tester->name ?? '' }}</td>
        <td style="font-weight: bold;">Mục đích:</td>
        <td>
            @if($assembly->purpose == 'storage') Lưu kho
            @elseif($assembly->purpose == 'project') Xuất đi dự án
            @else Lưu kho
            @endif
        </td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Kho xuất vật tư:</td>
        <td>{{ $assembly->warehouse->name ?? '' }} ({{ $assembly->warehouse->code ?? '' }})</td>
        <td style="font-weight: bold;">Kho nhập thành phẩm:</td>
        <td>{{ $assembly->targetWarehouse->name ?? '' }} ({{ $assembly->targetWarehouse->code ?? '' }})</td>
        @if($assembly->purpose == 'project' && $assembly->project)
        <td style="font-weight: bold;">Dự án:</td>
        <td>{{ $assembly->project->project_name ?? '' }}</td>
        @else
        <td></td>
        <td></td>
        @endif
    </tr>
    <tr><td colspan="6"></td></tr>
    
    <!-- Danh sách thành phẩm -->
    <tr>
        <td colspan="6" style="font-weight: bold; background-color: #E6F3FF;">DANH SÁCH THÀNH PHẨM</td>
    </tr>
    <tr style="font-weight: bold; background-color: #F0F8FF;">
        <td>STT</td>
        <td>Mã thành phẩm</td>
        <td>Tên thành phẩm</td>
        <td>Số lượng</td>
        <td>Serial</td>
        <td>Ghi chú</td>
    </tr>
    @if($assembly->products && $assembly->products->count() > 0)
        @foreach($assembly->products as $index => $assemblyProduct)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $assemblyProduct->product->code ?? '' }}</td>
            <td>{{ $assemblyProduct->product->name ?? '' }}</td>
            <td>{{ $assemblyProduct->quantity }}</td>
            <td>{{ $assemblyProduct->serials ?? '' }}</td>
            <td></td>
        </tr>
        @endforeach
    @else
        <!-- Legacy support -->
        <tr>
            <td>1</td>
            <td>{{ $assembly->product->code ?? '' }}</td>
            <td>{{ $assembly->product->name ?? '' }}</td>
            <td>{{ $assembly->quantity ?? 1 }}</td>
            <td>{{ $assembly->product_serials ?? '' }}</td>
            <td></td>
        </tr>
    @endif
    <tr><td colspan="6"></td></tr>
    
    <!-- Danh sách vật tư -->
    <tr>
        <td colspan="6" style="font-weight: bold; background-color: #E6F3FF;">DANH SÁCH VẬT TƯ SỬ DỤNG</td>
    </tr>
    <tr style="font-weight: bold; background-color: #F0F8FF;">
        <td>STT</td>
        <td>Mã vật tư</td>
        <td>Tên vật tư</td>
        <td>Số lượng</td>
        <td>Serial</td>
        <td>Ghi chú</td>
    </tr>
    @foreach($assembly->materials as $index => $material)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $material->material->code ?? '' }}</td>
        <td>{{ $material->material->name ?? '' }}</td>
        <td>{{ $material->quantity }}</td>
        <td>{{ $material->serial ?? '' }}</td>
        <td>{{ $material->note ?? '' }}</td>
    </tr>
    @endforeach
    <tr><td colspan="6"></td></tr>
    
    <!-- Ghi chú -->
    @if($assembly->notes)
    <tr>
        <td colspan="6" style="font-weight: bold; background-color: #E6F3FF;">GHI CHÚ</td>
    </tr>
    <tr>
        <td colspan="6">{{ $assembly->notes }}</td>
    </tr>
    <tr><td colspan="6"></td></tr>
    @endif
    
    <!-- Chữ ký -->
    <tr>
        <td colspan="2" style="text-align: center; font-weight: bold;">Người lập phiếu</td>
        <td colspan="2" style="text-align: center; font-weight: bold;">Người phụ trách</td>
        <td colspan="2" style="text-align: center; font-weight: bold;">Người kiểm thử</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; height: 60px;"></td>
        <td colspan="2" style="text-align: center; height: 60px;"></td>
        <td colspan="2" style="text-align: center; height: 60px;"></td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center;">{{ Auth::user()->employee->name ?? '' }}</td>
        <td colspan="2" style="text-align: center;">{{ $assembly->assignedEmployee->name ?? '' }}</td>
        <td colspan="2" style="text-align: center;">{{ $assembly->tester->name ?? '' }}</td>
    </tr>
</table> 