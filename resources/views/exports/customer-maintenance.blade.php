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
        .priority {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
        .priority-low { background: #e6f7ff; color: #0066cc; }
        .priority-medium { background: #d9f7be; color: #389e0d; }
        .priority-high { background: #fff7e6; color: #d46b08; }
        .priority-urgent { background: #fff1f0; color: #cf1322; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">Chi tiết phiếu khách yêu cầu bảo trì</div>
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
            <div class="section-title">Thông tin tiếp nhận</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Ngày tiếp nhận</div>
                    <div class="info-value">{{ $request->request_date->format('d/m/Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mức độ ưu tiên</div>
                    <div class="info-value">
                        <span class="priority priority-{{ $request->priority }}">
                            @switch($request->priority)
                                @case('low') Thấp @break
                                @case('medium') Trung bình @break
                                @case('high') Cao @break
                                @case('urgent') Khẩn cấp @break
                                @default Không xác định
                            @endswitch
                        </span>
                    </div>
                </div>

                @if($request->estimated_cost)
                <div class="info-item">
                    <div class="info-label">Chi phí dự kiến</div>
                    <div class="info-value">{{ number_format($request->estimated_cost, 0, ',', '.') }} VNĐ</div>
                </div>
                @endif
            </div>
        </div>

        <div class="section">
            <div class="section-title">Thông tin khách hàng</div>
            <div class="info-item">
                <div class="info-label">Tên khách hàng/Đơn vị</div>
                <div class="info-value">{{ $request->customer ? $request->customer->company_name : $request->customer_name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Số điện thoại</div>
                <div class="info-value">{{ $request->customer_phone ?: 'Không có' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $request->customer_email ?: 'Không có' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Địa chỉ</div>
                <div class="info-value">{{ $request->customer_address ?: 'Không có' }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Thông tin yêu cầu bảo trì</div>
            <div class="info-item">
                <div class="info-label">Tên dự án/thiết bị</div>
                <div class="info-value">{{ $request->project_name }}</div>
            </div>
            @if($request->project_description)
            <div class="info-item">
                <div class="info-label">Mô tả dự án/thiết bị</div>
                <div class="info-value">{{ $request->project_description }}</div>
            </div>
            @endif
            <div class="info-item">
                <div class="info-label">Lý do yêu cầu bảo trì</div>
                <div class="info-value">{{ $request->maintenance_reason }}</div>
            </div>
            @if($request->maintenance_details)
            <div class="info-item">
                <div class="info-label">Chi tiết yêu cầu bảo trì</div>
                <div class="info-value">{{ $request->maintenance_details }}</div>
            </div>
            @endif
        </div>

        @if($request->notes)
        <div class="section">
            <div class="section-title">Ghi chú bổ sung</div>
            <div class="notes">{{ $request->notes }}</div>
        </div>
        @endif

        @if($request->status === 'rejected' && $request->rejection_reason)
        <div class="section">
            <div class="section-title" style="color: #721c24;">Lý do từ chối</div>
            <div class="notes" style="background: #f8d7da; color: #721c24;">{{ $request->rejection_reason }}</div>
        </div>
        @endif

        @if($request->status === 'approved' || $request->status === 'in_progress' || $request->status === 'completed')
        <div class="section">
            <div class="section-title">Thông tin phê duyệt</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Người duyệt</div>
                    <div class="info-value">{{ $request->approvedByUser ? $request->approvedByUser->name : 'Không xác định' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ngày duyệt</div>
                    <div class="info-value">{{ $request->approved_at ? $request->approved_at->format('d/m/Y H:i') : 'Không xác định' }}</div>
                </div>
            </div>
        </div>
        @endif

        <div style="text-align: center; margin-top: 20px; font-size: 11px; color: #666;">
            Phiếu yêu cầu được xuất lúc: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
</body>
</html> 