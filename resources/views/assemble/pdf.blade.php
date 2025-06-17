<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu lắp ráp - {{ $assembly->code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        
        .header p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
        }
        
        .section-title {
            background-color: #f0f8ff;
            font-weight: bold;
            padding: 8px;
            margin: 15px 0 5px 0;
            border-left: 4px solid #4472c4;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-in-progress {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 30%;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 40px;
        }
        
        .signature-name {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-style: italic;
        }
        
        .notes-section {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 15px 0;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Phiếu lắp ráp</h1>
        <p>Mã phiếu: {{ $assembly->code }} | Ngày lắp ráp: {{ \Carbon\Carbon::parse($assembly->date)->format('d/m/Y') }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Mã phiếu:</span>
            <span class="info-value">{{ $assembly->code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Ngày lắp ráp:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($assembly->date)->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Trạng thái:</span>
            <span class="info-value">
                <span class="status 
                    @if($assembly->status == 'completed') status-completed
                    @elseif($assembly->status == 'in_progress') status-in-progress  
                    @elseif($assembly->status == 'pending') status-pending
                    @else status-cancelled
                    @endif">
                    @if($assembly->status == 'completed') Hoàn thành
                    @elseif($assembly->status == 'in_progress') Đang thực hiện  
                    @elseif($assembly->status == 'pending') Chờ xử lý
                    @else Đã hủy
                    @endif
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Người phụ trách:</span>
            <span class="info-value">{{ $assembly->assignedEmployee->name ?? 'Chưa phân công' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Người kiểm thử:</span>
            <span class="info-value">{{ $assembly->tester->name ?? 'Chưa phân công' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kho xuất vật tư:</span>
            <span class="info-value">{{ $assembly->warehouse->name ?? '' }} ({{ $assembly->warehouse->code ?? '' }})</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kho nhập thành phẩm:</span>
            <span class="info-value">{{ $assembly->targetWarehouse->name ?? '' }} ({{ $assembly->targetWarehouse->code ?? '' }})</span>
        </div>
        <div class="info-row">
            <span class="info-label">Mục đích:</span>
            <span class="info-value">
                @if($assembly->purpose == 'storage') Lưu kho
                @elseif($assembly->purpose == 'project') Xuất đi dự án
                @else Lưu kho
                @endif
            </span>
        </div>
        @if($assembly->purpose == 'project' && $assembly->project)
        <div class="info-row">
            <span class="info-label">Dự án:</span>
            <span class="info-value">{{ $assembly->project->project_name ?? '' }}</span>
        </div>
        @endif
    </div>

    <div class="section-title">Danh sách thành phẩm</div>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">STT</th>
                <th style="width: 20%;">Mã thành phẩm</th>
                <th style="width: 35%;">Tên thành phẩm</th>
                <th style="width: 12%;">Số lượng</th>
                <th style="width: 25%;">Serial</th>
            </tr>
        </thead>
        <tbody>
            @if($assembly->products && $assembly->products->count() > 0)
                @foreach($assembly->products as $index => $assemblyProduct)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $assemblyProduct->product->code ?? '' }}</td>
                    <td>{{ $assemblyProduct->product->name ?? '' }}</td>
                    <td class="text-center">{{ $assemblyProduct->quantity }}</td>
                    <td>{{ $assemblyProduct->serials ?? '' }}</td>
                </tr>
                @endforeach
            @else
                <!-- Legacy support -->
                <tr>
                    <td class="text-center">1</td>
                    <td>{{ $assembly->product->code ?? '' }}</td>
                    <td>{{ $assembly->product->name ?? '' }}</td>
                    <td class="text-center">{{ $assembly->quantity ?? 1 }}</td>
                    <td>{{ $assembly->product_serials ?? '' }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="section-title">Danh sách vật tư sử dụng</div>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">STT</th>
                <th style="width: 20%;">Mã vật tư</th>
                <th style="width: 30%;">Tên vật tư</th>
                <th style="width: 12%;">Số lượng</th>
                <th style="width: 15%;">Serial</th>
                <th style="width: 15%;">Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assembly->materials as $index => $material)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $material->material->code ?? '' }}</td>
                <td>{{ $material->material->name ?? '' }}</td>
                <td class="text-center">{{ $material->quantity }}</td>
                <td>{{ $material->serial ?? '' }}</td>
                <td>{{ $material->note ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($assembly->notes)
    <div class="notes-section">
        <div class="notes-title">Ghi chú:</div>
        <div>{{ $assembly->notes }}</div>
    </div>
    @endif

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-title">Người lập phiếu</div>
            <div class="signature-name">{{ Auth::user()->employee->name ?? '' }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Người phụ trách</div>
            <div class="signature-name">{{ $assembly->assignedEmployee->name ?? '' }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Người kiểm thử</div>
            <div class="signature-name">{{ $assembly->tester->name ?? '' }}</div>
        </div>
    </div>
</body>
</html> 