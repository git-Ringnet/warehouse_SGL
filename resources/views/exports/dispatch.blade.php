<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Phiếu xuất kho</title>
</head>

<body>
    <table>
        <tr>
            <td colspan="6" style="text-align: center; font-size: 16px; font-weight: bold;">PHIẾU XUẤT KHO</td>
        </tr>
        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>

        <tr>
            <td colspan="6" style="font-size: 13px; font-weight: bold;">Thông tin phiếu xuất</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Mã phiếu xuất:</td>
            <td>{{ $dispatch->dispatch_code }}</td>
            <td style="font-weight: bold;">Người tạo phiếu:</td>
            <td>{{ $dispatch->creator->name ?? 'N/A' }}</td>
            <td style="font-weight: bold;">Cập nhật lần cuối:</td>
            <td>{{ $dispatch->updated_at->format('H:i:s d/m/Y') }}</td>
        </tr>
        @php $isAutoAssembly = str_contains($dispatch->dispatch_note ?? '', 'Sinh từ phiếu lắp ráp'); @endphp
        <tr>
            <td style="font-weight: bold;">Ngày xuất:</td>
            <td>{{ $dispatch->dispatch_date->format('H:i:s d/m/Y') }}</td>
            <td style="font-weight: bold;">Trạng thái:</td>
            <td>{{ $dispatch->status === 'pending' ? 'Chờ duyệt' : ($dispatch->status === 'approved' ? 'Đã duyệt' : ($dispatch->status === 'cancelled' ? 'Đã hủy' : 'Hoàn thành')) }}
            </td>
            @unless($isAutoAssembly)
                <td style="font-weight: bold;">Chi tiết xuất kho:</td>
                <td>{{ $dispatch->dispatch_detail === 'contract' ? 'Xuất theo hợp đồng' : ($dispatch->dispatch_detail === 'backup' ? 'Xuất thiết bị dự phòng' : 'Tất cả') }}
                </td>
            @else
                <td></td>
                <td></td>
            @endunless
        </tr>
        @unless($isAutoAssembly)
            <tr>
                <td style="font-weight: bold;">Người nhận:</td>
                <td colspan="5">{{ $dispatch->project_receiver }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Loại hình:</td>
                <td>{{ $dispatch->dispatch_type === 'project' ? 'Dự án' : ($dispatch->dispatch_type === 'rental' ? 'Cho thuê' : 'Bảo hành') }}
                </td>
                <td style="font-weight: bold;">Dự án:</td>
                <td colspan="3">{{ $dispatch->project ? $dispatch->project->project_name : 'N/A' }}</td>
            </tr>
            @if ($dispatch->companyRepresentative)
                <tr>
                    <td style="font-weight: bold;">Người đại diện công ty:</td>
                    <td colspan="5">{{ $dispatch->companyRepresentative->name }}</td>
                </tr>
            @endif
        @endunless
        <tr>
            <td style="font-weight: bold;">Ghi chú:</td>
            <td colspan="5">{{ $dispatch->dispatch_note ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>

        @php
            $contractItems = $dispatch->items->where('category', 'contract');
            $backupItems = $dispatch->items->where('category', 'backup');
            $generalItems = $dispatch->items->where('category', 'general');
        @endphp
        @if ($dispatch->dispatch_detail === 'contract' || ($dispatch->dispatch_detail === 'all' && $contractItems->count() > 0))
            <tr>
                <td colspan="6" style="font-size: 13px; font-weight: bold;">Danh sách thiết bị theo hợp đồng</td>
            </tr>
            <tr>
                <th>STT</th>
                <th>Mã</th>
                <th>Tên thiết bị</th>
                <th>Đơn vị</th>
                <th>Số lượng</th>
                <th>Serial</th>
            </tr>
            @php $stt = 1; @endphp
            @foreach ($contractItems as $item)
                <tr>
                    <td>{{ $stt++ }}</td>
                    <td>{{ $item->item_type === 'material' ? $item->material->code ?? 'N/A' : ($item->item_type === 'product' ? $item->product->code ?? 'N/A' : $item->good->code ?? 'N/A') }}
                    </td>
                    <td>{{ $item->item_type === 'material' ? $item->material->name ?? 'N/A' : ($item->item_type === 'product' ? $item->product->name ?? 'N/A' : $item->good->name ?? 'N/A') }}
                    </td>
                    <td>{{ $item->item_type === 'material' ? $item->material->unit ?? 'Cái' : ($item->item_type === 'product' ? 'Cái' : $item->good->unit ?? 'Cái') }}
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ is_array($item->serial_numbers) ? implode(', ', $item->serial_numbers) : $item->serial_numbers ?? 'Chưa có' }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="6">&nbsp;</td>
            </tr>
        @endif

        @if ($dispatch->dispatch_detail === 'backup' || ($dispatch->dispatch_detail === 'all' && $backupItems->count() > 0))
            <tr>
                <td colspan="6" style="font-size: 13px; font-weight: bold;">Danh sách thiết bị dự phòng</td>
            </tr>
            <tr>
                <th>STT</th>
                <th>Mã</th>
                <th>Tên thiết bị</th>
                <th>Đơn vị</th>
                <th>Số lượng</th>
                <th>Serial</th>
            </tr>
            @php $stt = 1; @endphp
            @foreach ($backupItems as $item)
                <tr>
                    <td>{{ $stt++ }}</td>
                    <td>{{ $item->item_type === 'material' ? $item->material->code ?? 'N/A' : ($item->item_type === 'product' ? $item->product->code ?? 'N/A' : $item->good->code ?? 'N/A') }}
                    </td>
                    <td>{{ $item->item_type === 'material' ? $item->material->name ?? 'N/A' : ($item->item_type === 'product' ? $item->product->name ?? 'N/A' : $item->good->name ?? 'N/A') }}
                    </td>
                    <td>{{ $item->item_type === 'material' ? $item->material->unit ?? 'Cái' : ($item->item_type === 'product' ? 'Cái' : $item->good->unit ?? 'Cái') }}
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ is_array($item->serial_numbers) ? implode(', ', $item->serial_numbers) : $item->serial_numbers ?? 'Chưa có' }}
                    </td>
                </tr>
            @endforeach
        @endif

        @if ($dispatch->dispatch_detail === 'all' && $generalItems->count() > 0)
            <tr>
                <td colspan="7" style="font-size: 13px; font-weight: bold;">Danh sách vật tư</td>
            </tr>
            <tr>
                <th>STT</th>
                <th>Mã</th>
                <th>Tên vật tư</th>
                <th>Đơn vị</th>
                <th>Số lượng</th>
                <th>Kho xuất</th>
                <th>Serial</th>
            </tr>
            @php $stt = 1; @endphp
            @foreach ($generalItems as $item)
                <tr>
                    <td>{{ $stt++ }}</td>
                    <td>{{ $item->item_type === 'material' ? $item->material->code ?? 'N/A' : ($item->item_type === 'product' ? $item->product->code ?? 'N/A' : $item->good->code ?? 'N/A') }}
                    </td>
                    <td>{{ $item->item_type === 'material' ? $item->material->name ?? 'N/A' : ($item->item_type === 'product' ? $item->product->name ?? 'N/A' : $item->good->name ?? 'N/A') }}
                    </td>
                    <td>{{ $item->item_type === 'material' ? $item->material->unit ?? 'Cái' : ($item->item_type === 'product' ? 'Cái' : $item->good->unit ?? 'Cái') }}
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->warehouse->name ?? '-' }}</td>
                    <td>{{ is_array($item->serial_numbers) ? implode(', ', $item->serial_numbers) : $item->serial_numbers ?? 'Chưa có' }}
                    </td>
                </tr>
            @endforeach
        @endif

        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>

        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">Người lập phiếu</td>
            <td colspan="3" style="text-align: center; font-weight: bold;">@if($isAutoAssembly)@else Người nhận @endif</td>
        </tr>
        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="6">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center;">{{ $dispatch->creator->name ?? 'N/A' }}</td>
            <td colspan="3" style="text-align: center;">@unless($isAutoAssembly){{ $dispatch->project_receiver }}@endunless</td>
        </tr>
    </table>
</body>

</html>
