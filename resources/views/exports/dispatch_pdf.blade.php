<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Phiếu xuất kho</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            line-height: 1.6;
        }
        .container {
            padding: 20px;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            background-color: #f5f5f5;
            padding: 5px;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.info-table td {
            padding: 5px;
            vertical-align: top;
        }
        table.info-table td:first-child {
            font-weight: bold;
            width: 150px;
        }
        table.data-table {
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #E2EFDA;
            padding: 8px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
        }
        table.data-table td {
            padding: 8px;
            border: 1px solid #000;
            vertical-align: middle;
        }
        table.data-table td:nth-child(1), /* STT */
        table.data-table td:nth-child(5) { /* Số lượng */
            text-align: center;
        }
        .signature-section {
            margin-top: 30px;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 50px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        .info-item {
            display: flex;
            gap: 10px;
        }
        .info-label {
            font-weight: bold;
            min-width: 120px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">PHIẾU XUẤT KHO</div>

        <div class="section-title">Thông tin phiếu xuất</div>
        @php
            $isAutoAssembly = str_contains($dispatch->dispatch_note ?? '', 'Sinh từ phiếu lắp ráp');
            $contractItems = $dispatch->items->where('category', 'contract');
            $backupItems = $dispatch->items->where('category', 'backup');
            $generalItems = $dispatch->items->where('category', 'general');
        @endphp
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Mã phiếu xuất:</span>
                <span>{{ $dispatch->dispatch_code }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Người tạo phiếu:</span>
                <span>{{ $dispatch->creator->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Ngày xuất:</span>
                <span>{{ $dispatch->dispatch_date->format('H:i:s d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Trạng thái:</span>
                <span>{{ $dispatch->status === 'pending' ? 'Chờ duyệt' : ($dispatch->status === 'approved' ? 'Đã duyệt' : ($dispatch->status === 'cancelled' ? 'Đã hủy' : 'Hoàn thành')) }}</span>
            </div>
            @unless($isAutoAssembly)
                <div class="info-item">
                    <span class="info-label">Người nhận:</span>
                    <span>{{ $dispatch->project_receiver }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Chi tiết xuất kho:</span>
                    <span>{{ $dispatch->dispatch_detail === 'contract' ? 'Xuất theo hợp đồng' : ($dispatch->dispatch_detail === 'backup' ? 'Xuất thiết bị dự phòng' : 'Tất cả') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Loại hình:</span>
                    <span>{{ $dispatch->dispatch_type === 'project' ? 'Dự án' : ($dispatch->dispatch_type === 'rental' ? 'Cho thuê' : 'Bảo hành') }}</span>
                </div>
            @endunless
            <div class="info-item">
                <span class="info-label">Dự án:</span>
                <span>{{ $dispatch->project ? $dispatch->project->project_name : 'N/A' }}</span>
            </div>
            @if(!$isAutoAssembly && $dispatch->companyRepresentative)
            <div class="info-item">
                <span class="info-label">Người đại diện:</span>
                <span>{{ $dispatch->companyRepresentative->name }}</span>
            </div>
            @endif
            <div class="info-item">
                <span class="info-label">Ghi chú:</span>
                <span>{{ $dispatch->dispatch_note ?? 'N/A' }}</span>
            </div>
        </div>

        @if($dispatch->dispatch_detail === 'contract' || ($dispatch->dispatch_detail === 'all' && $contractItems->count() > 0))
        <div class="section-title">Danh sách thiết bị theo hợp đồng</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">STT</th>
                    <th style="width: 20%;">Mã</th>
                    <th style="width: 30%;">Tên thiết bị</th>
                    <th style="width: 12%;">Đơn vị</th>
                    <th style="width: 10%;">Số lượng</th>
                    <th style="width: 20%;">Serial</th>
                </tr>
            </thead>
            <tbody>
                @php $stt = 1; @endphp
                @foreach($contractItems as $item)
                <tr>
                    <td>{{ $stt++ }}</td>
                    <td>{{ $item->item_type === 'material' ? ($item->material->code ?? 'N/A') : ($item->item_type === 'product' ? ($item->product->code ?? 'N/A') : ($item->good->code ?? 'N/A')) }}</td>
                    <td>{{ $item->item_type === 'material' ? ($item->material->name ?? 'N/A') : ($item->item_type === 'product' ? ($item->product->name ?? 'N/A') : ($item->good->name ?? 'N/A')) }}</td>
                    <td style="text-align: center;">{{ $item->item_type === 'material' ? ($item->material->unit ?? 'Cái') : ($item->item_type === 'product' ? 'Cái' : ($item->good->unit ?? 'Cái')) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ is_array($item->serial_numbers) ? implode(', ', $item->serial_numbers) : ($item->serial_numbers ?? 'Chưa có') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($dispatch->dispatch_detail === 'backup' || ($dispatch->dispatch_detail === 'all' && $backupItems->count() > 0))
        <div class="section-title">Danh sách thiết bị dự phòng</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">STT</th>
                    <th style="width: 20%;">Mã</th>
                    <th style="width: 30%;">Tên thiết bị</th>
                    <th style="width: 12%;">Đơn vị</th>
                    <th style="width: 10%;">Số lượng</th>
                    <th style="width: 20%;">Serial</th>
                </tr>
            </thead>
            <tbody>
                @php $stt = 1; @endphp
                @foreach($backupItems as $item)
                <tr>
                    <td>{{ $stt++ }}</td>
                    <td>{{ $item->item_type === 'material' ? ($item->material->code ?? 'N/A') : ($item->item_type === 'product' ? ($item->product->code ?? 'N/A') : ($item->good->code ?? 'N/A')) }}</td>
                    <td>{{ $item->item_type === 'material' ? ($item->material->name ?? 'N/A') : ($item->item_type === 'product' ? ($item->product->name ?? 'N/A') : ($item->good->name ?? 'N/A')) }}</td>
                    <td style="text-align: center;">{{ $item->item_type === 'material' ? ($item->material->unit ?? 'Cái') : ($item->item_type === 'product' ? 'Cái' : ($item->good->unit ?? 'Cái')) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ is_array($item->serial_numbers) ? implode(', ', $item->serial_numbers) : ($item->serial_numbers ?? 'Chưa có') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if(($dispatch->dispatch_detail === 'all' && $generalItems->count() > 0))
        <div class="section-title">Danh sách vật tư</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">STT</th>
                    <th style="width: 20%;">Mã</th>
                    <th style="width: 30%;">Tên vật tư</th>
                    <th style="width: 12%;">Đơn vị</th>
                    <th style="width: 10%;">Số lượng</th>
                    <th style="width: 20%;">Kho xuất</th>
                    <th style="width: 20%;">Serial</th>
                </tr>
            </thead>
            <tbody>
                @php $stt = 1; @endphp
                @foreach($generalItems as $item)
                <tr>
                    <td>{{ $stt++ }}</td>
                    <td>{{ $item->item_type === 'material' ? ($item->material->code ?? 'N/A') : ($item->item_type === 'product' ? ($item->product->code ?? 'N/A') : ($item->good->code ?? 'N/A')) }}</td>
                    <td>{{ $item->item_type === 'material' ? ($item->material->name ?? 'N/A') : ($item->item_type === 'product' ? ($item->product->name ?? 'N/A') : ($item->good->name ?? 'N/A')) }}</td>
                    <td style="text-align: center;">{{ $item->item_type === 'material' ? ($item->material->unit ?? 'Cái') : ($item->item_type === 'product' ? 'Cái' : ($item->good->unit ?? 'Cái')) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->warehouse->name ?? '-' }}</td>
                    <td>{{ is_array($item->serial_numbers) ? implode(', ', $item->serial_numbers) : ($item->serial_numbers ?? 'Chưa có') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-title">Người lập phiếu</div>
                    <div>{{ $dispatch->creator->name ?? 'N/A' }}</div>
                </td>
                <td></td>
                <td>
                    @unless($isAutoAssembly)
                        <div class="signature-title">Người nhận</div>
                        <div>{{ $dispatch->project_receiver }}</div>
                    @endunless
                </td>
            </tr>
        </table>
    </div>
</body>
</html> 