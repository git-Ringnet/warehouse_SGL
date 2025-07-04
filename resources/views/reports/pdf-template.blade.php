<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo tổng hợp vật tư</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #2c3e50;
            margin: 0;
            padding: 15px;
            background-color: #fff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3498db;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 8px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #3498db;
            margin: 8px 0;
            text-transform: uppercase;
        }
        
        .report-period {
            font-size: 13px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .summary-section {
            margin: 20px 0;
            background: linear-gradient(135deg, #f1f8ff 0%, #e6f3ff 100%);
            padding: 20px;
            border: 2px solid #3498db;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(52, 152, 219, 0.1);
        }
        
        .summary-title {
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .summary-stats {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
        }
        
        .stat-item {
            display: table-cell;
            text-align: center;
            background-color: #fff;
            padding: 12px 8px;
            border-radius: 8px;
            border: 1px solid #bdc3c7;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .stat-label {
            font-size: 10px;
            color: #7f8c8d;
            margin-bottom: 5px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .table-container {
            margin: 25px 0;
        }
        
        .table-title {
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 12px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px;
            background: linear-gradient(135deg, #ecf0f1 0%, #bdc3c7 100%);
            border-radius: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        th {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 12px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: none;
        }
        
        td {
            padding: 8px 6px;
            border: 1px solid #ecf0f1;
            text-align: center;
            background-color: #fff;
        }
        
        tr:nth-child(even) td {
            background-color: #f8f9fa;
        }
        
        tr:hover td {
            background-color: #e3f2fd;
        }
        
        .text-left {
            text-align: left !important;
            padding-left: 8px !important;
        }
        
        .text-right {
            text-align: right !important;
            padding-right: 8px !important;
        }
        
        .positive {
            color: #27ae60;
            font-weight: bold;
            background-color: #d5f4e6 !important;
        }
        
        .negative {
            color: #e74c3c;
            font-weight: bold;
            background-color: #fadbd8 !important;
        }
        
        .zero {
            color: #95a5a6;
            font-style: italic;
        }
        
        tfoot tr {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%) !important;
            color: white;
            font-weight: bold;
        }
        
        tfoot td {
            background: none !important;
            border: none !important;
            color: white;
            padding: 10px 6px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #bdc3c7;
            font-size: 10px;
            color: #7f8c8d;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px;
            border-radius: 5px;
        }
        
        .footer-info {
            display: table;
            width: 100%;
        }
        
        .footer-info > div {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .notes {
            margin: 20px 0;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            padding: 15px;
            border-left: 5px solid #f39c12;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(243, 156, 18, 0.1);
        }
        
        .notes-title {
            font-weight: bold;
            color: #d35400;
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .notes-content {
            font-size: 10px;
            color: #8b4513;
            line-height: 1.6;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            font-style: italic;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            border: 2px dashed #bdc3c7;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .summary-section,
            .table-container,
            .notes,
            .footer {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="report-title">BÁO CÁO TỔNG HỢP VẬT TƯ</div>
        <div class="report-period">
            Từ {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} 
            đến {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="summary-section">
        <div class="summary-title">THỐNG KÊ TỔNG QUAN</div>
        <div class="summary-stats">
            <div class="stat-item">
                <div class="stat-label">Tổng số vật tư</div>
                <div class="stat-value">{{ $stats['total_materials'] ?? 0 }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Tổng tồn đầu kỳ</div>
                <div class="stat-value">{{ number_format($stats['total_opening'] ?? 0) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Tổng nhập kho</div>
                <div class="stat-value">{{ number_format($stats['total_imports'] ?? 0) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Tổng xuất kho</div>
                <div class="stat-value">{{ number_format($stats['total_exports'] ?? 0) }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Tổng tồn cuối kỳ</div>
                <div class="stat-value">{{ number_format($stats['total_closing'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <!-- Ghi chú giải thích -->
    <div class="notes">
        <div class="notes-title">Ghi chú quan trọng</div>
        <div class="notes-content">
            <strong>Tồn cuối kỳ (tính toán)</strong>: Tồn đầu kỳ + Nhập - Xuất<br>
            <strong>Tồn hiện tại</strong>: Số lượng thực tế trong kho hiện tại<br>
            <strong>Chênh lệch</strong>: Tồn hiện tại - Tồn cuối kỳ (tính toán)<br>
            Chênh lệch dương (nền xanh): Thừa so với tính toán<br>
            Chênh lệch âm (nền đỏ): Thiếu so với tính toán<br>
            Chênh lệch = 0: Khớp với tính toán
        </div>
    </div>

    <!-- Bảng chi tiết -->
    <div class="table-container">
        <div class="table-title">CHI TIẾT VẬT TƯ</div>
        
        @if($reportData->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">STT</th>
                        <th style="width: 12%">Mã vật tư</th>
                        <th style="width: 25%">Tên vật tư</th>
                        <th style="width: 8%">Đơn vị</th>
                        <th style="width: 15%">Danh mục</th>
                        <th style="width: 8%">Tồn đầu kỳ</th>
                        <th style="width: 8%">Nhập</th>
                        <th style="width: 8%">Xuất</th>
                        <th style="width: 8%">Tồn hiện tại</th>
                        <th style="width: 8%">Chênh lệch</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData as $index => $item)
                        @php
                            $difference = $item['current_stock'] - $item['closing_stock'];
                            $differenceClass = $difference > 0 ? 'positive' : ($difference < 0 ? 'negative' : 'zero');
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-left">{{ $item['item_code'] }}</td>
                            <td class="text-left">{{ $item['item_name'] }}</td>
                            <td>{{ $item['item_unit'] }}</td>
                            <td class="text-left">{{ $item['item_category'] }}</td>
                            <td class="text-right">{{ number_format($item['opening_stock']) }}</td>
                            <td class="text-right">{{ number_format($item['imports']) }}</td>
                            <td class="text-right">{{ number_format($item['exports']) }}</td>
                            <td class="text-right">{{ number_format($item['current_stock']) }}</td>
                            <td class="text-right {{ $differenceClass }}">
                                {{ $difference > 0 ? '+' : '' }}{{ number_format($difference) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-left" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                            TỔNG CỘNG
                        </td>
                        <td class="text-right" style="font-size: 11px;">{{ number_format($reportData->sum('opening_stock')) }}</td>
                        <td class="text-right" style="font-size: 11px;">{{ number_format($reportData->sum('imports')) }}</td>
                        <td class="text-right" style="font-size: 11px;">{{ number_format($reportData->sum('exports')) }}</td>
                        <td class="text-right" style="font-size: 11px;">{{ number_format($reportData->sum('current_stock')) }}</td>
                        <td class="text-right" style="font-size: 11px;">
                            @php
                                $totalDiff = $reportData->sum('current_stock') - $reportData->sum('closing_stock');
                            @endphp
                            {{ $totalDiff > 0 ? '+' : '' }}{{ number_format($totalDiff) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        @else
            <div class="no-data">
                <i class="fas fa-inbox" style="font-size: 24px; margin-bottom: 10px; color: #bdc3c7;"></i><br>
                Không có dữ liệu để hiển thị trong kỳ báo cáo này
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-info">
            <div>
                <strong>Thời gian xuất:</strong> {{ \Carbon\Carbon::now()->format('H:i:s d/m/Y') }}<br>
                <strong>Người xuất:</strong> {{ auth()->user()->name ?? 'Hệ thống' }}
            </div>
            <div style="text-align: right;">
                <strong>Báo cáo:</strong> Tổng hợp vật tư<br>
                <strong>Kỳ báo cáo:</strong> {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </div>
        </div>
        @if($search || $category)
            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #bdc3c7; text-align: center;">
                🔍 <strong>Bộ lọc áp dụng:</strong>
                @if($search)
                    <span style="background-color: #e8f5e8; padding: 2px 6px; border-radius: 3px; margin: 0 3px;">
                        Từ khóa: "{{ $search }}"
                    </span>
                @endif
                @if($category)
                    <span style="background-color: #e8f0ff; padding: 2px 6px; border-radius: 3px; margin: 0 3px;">
                        Danh mục: "{{ $category }}"
                    </span>
                @endif
            </div>
        @endif
    </div>
</body>
</html> 