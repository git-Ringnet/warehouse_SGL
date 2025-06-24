<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 13px;
            color: #666;
        }
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-top: 5px;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-in_progress { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-canceled { background: #e2e3e5; color: #383d41; }
        
        .section {
            margin-bottom: 15px;
            padding: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .info-label {
            color: #666;
            font-size: 12px;
            margin-bottom: 2px;
        }
        .info-value {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th {
            background: #f8f9fa;
            padding: 8px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .notes {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">Chi tiết phiếu bảo trì dự án</div>
            <div class="subtitle">
                Mã phiếu: {{ $request->request_code }} | Ngày tạo: {{ $request->request_date->format('d/m/Y') }}
            </div>
            <div class="status status-{{ $request->status }}">
                @switch($request->status)
                    @case('pending') Chờ duyệt @break
                    @case('approved') Đã duyệt @break
                    @case('rejected') Từ chối @break
                    @case('in_progress') Đang thực hiện @break
                    @case('completed') Hoàn thành @break
                    @case('canceled') Đã hủy @break
                    @default Không xác định
                @endswitch
            </div>
        </div>

        <div class="section">
            <div class="section-title">Thông tin đề xuất</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Người đề xuất</div>
                    <div class="info-value">{{ $request->proposer ? $request->proposer->name : 'Không có' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ngày đề xuất</div>
                    <div class="info-value">{{ $request->request_date->format('d/m/Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tên dự án</div>
                    <div class="info-value">{{ $request->project_name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Địa chỉ dự án</div>
                    <div class="info-value">{{ $request->project_address }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Loại bảo trì</div>
                    <div class="info-value">
                        @if($request->maintenance_type === 'regular')
                            Định kỳ
                        @elseif($request->maintenance_type === 'emergency')
                            Khẩn cấp
                        @elseif($request->maintenance_type === 'preventive')
                            Phòng ngừa
                        @else
                            Không xác định
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Người thực hiện</div>
                    <div class="info-value">{{ $request->proposer ? $request->proposer->name : 'Chưa phân công' }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Thông tin khách hàng</div>
            <div class="info-item">
                <div class="info-label">Đối tác</div>
                <div class="info-value">{{ $request->customer ? $request->customer->company_name : $request->customer_name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Tên người liên hệ</div>
                <div class="info-value">{{ $request->customer_name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Số điện thoại</div>
                <div class="info-value">{{ $request->customer_phone }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $request->customer_email ?: 'Không có' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Địa chỉ</div>
                <div class="info-value">{{ $request->customer_address }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Thông tin bảo trì</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Ngày bảo trì dự kiến</div>
                    <div class="info-value">{{ $request->maintenance_date->format('d/m/Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Lý do bảo trì</div>
                    <div class="info-value">{{ $request->maintenance_reason }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Thành phẩm cần bảo trì</div>
            <table>
                <thead>
                    <tr>
                        <th>Mã thành phẩm</th>
                        <th>Tên thành phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn vị</th>
                        <th>Mô tả</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($request->products as $product)
                    <tr>
                        <td>{{ $product->product_code }}</td>
                        <td>{{ $product->product_name }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>{{ $product->unit }}</td>
                        <td>{{ $product->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Ghi chú</div>
            <div class="notes">
                {{ $request->notes ?: 'Không có ghi chú' }}
            </div>
        </div>
    </div>
</body>
</html> 