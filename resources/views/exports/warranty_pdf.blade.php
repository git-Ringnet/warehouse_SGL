<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Phiếu bảo hành</title>
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
            width: 180px;
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
        .signature-table {
            width: 100%;
            margin-top: 30px;
        }
        .signature-table td {
            width: 50%;
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
            min-width: 150px;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">PHIẾU BẢO HÀNH</div>

        <div class="section-title">Thông tin bảo hành</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Mã bảo hành:</span>
                <span>{{ $warranty->warranty_code }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Trạng thái:</span>
                <span class="status-badge {{ $warranty->status === 'active' ? 'status-active' : 'status-expired' }}">
                    {{ $warranty->status === 'active' ? 'Còn hiệu lực' : 'Hết hạn' }}
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Ngày kích hoạt:</span>
                <span>{{ $warranty->activated_at ? $warranty->activated_at->format('d/m/Y H:i:s') : 'Chưa kích hoạt' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Thời hạn bảo hành:</span>
                <span>{{ $warranty->warranty_period_months }} tháng</span>
            </div>
            @if($warranty->warranty_end_date)
            <div class="info-item">
                <span class="info-label">Ngày hết hạn:</span>
                <span>{{ $warranty->warranty_end_date->format('d/m/Y') }}</span>
            </div>
            @endif
            @if($warranty->remaining_time !== null)
            <div class="info-item">
                <span class="info-label">Thời gian còn lại:</span>
                <span>{{ $warranty->remaining_time > 0 ? $warranty->remaining_time . ' ngày' : 'Đã hết hạn' }}</span>
            </div>
            @endif
        </div>

        <div class="section-title">Thông tin khách hàng</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Tên khách hàng:</span>
                <span>{{ $warranty->customer_name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Số điện thoại:</span>
                <span>{{ $warranty->customer_phone ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span>{{ $warranty->customer_email ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Địa chỉ:</span>
                <span>{{ $warranty->customer_address ?? 'N/A' }}</span>
            </div>
        </div>

        @if($warranty->project_name)
        <div class="section-title">Thông tin dự án</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Tên dự án:</span>
                <span>{{ $warranty->project_name }}</span>
            </div>
            @if($warranty->dispatch && $warranty->dispatch->project)
            <div class="info-item">
                <span class="info-label">Mã dự án:</span>
                <span>{{ $warranty->dispatch->project->project_code ?? 'N/A' }}</span>
            </div>
            @endif
        </div>
        @endif

        @if($warranty->item_type === 'project' && $warranty->project_items)
        <div class="section-title">Danh sách thiết bị được bảo hành</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">STT</th>
                    <th style="width: 20%;">Mã thiết bị</th>
                    <th style="width: 30%;">Tên thiết bị</th>
                    <th style="width: 12%;">Số lượng</th>
                    <th style="width: 30%;">Serial</th>
                </tr>
            </thead>
            <tbody>
                @php $stt = 1; @endphp
                @foreach($warranty->project_items as $item)
                <tr>
                    <td style="text-align: center;">{{ $stt++ }}</td>
                    <td>{{ $item['code'] ?? 'N/A' }}</td>
                    <td>{{ $item['name'] ?? 'N/A' }}</td>
                    <td style="text-align: center;">{{ $item['quantity'] ?? 1 }}</td>
                    <td>{{ is_array($item['serial_numbers'] ?? null) ? implode(', ', $item['serial_numbers']) : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @elseif($warranty->item)
        <div class="section-title">Thông tin thiết bị</div>
        <table class="info-table">
            <tr>
                <td>Mã thiết bị:</td>
                <td>{{ $warranty->item->code ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Tên thiết bị:</td>
                <td>{{ $warranty->item->name ?? 'N/A' }}</td>
            </tr>
            @if($warranty->serial_number)
            <tr>
                <td>Serial Number:</td>
                <td>{{ $warranty->serial_number }}</td>
            </tr>
            @endif
        </table>
        @endif

        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-title">Người tạo phiếu</div>
                    <div>{{ $warranty->creator->name ?? 'N/A' }}</div>
                </td>
                <td>
                    <div class="signature-title">Khách hàng</div>
                    <div>{{ $warranty->customer_name ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
