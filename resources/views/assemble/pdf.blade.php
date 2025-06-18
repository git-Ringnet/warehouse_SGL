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

        .info-grid {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            width: 100%;
            margin-bottom: 8px;
            overflow: hidden;
        }

        .info-label {
            font-weight: bold;
            width: 180px;
            float: left;
            margin-right: 10px;
        }

        .info-value {
            margin-left: 190px;
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
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
        }

        .signature-box {
            text-align: center;
            width: 33.33%;
            vertical-align: top;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 11px;
        }

        .signature-name {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-style: italic;
            font-size: 10px;
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
        <p>Mã phiếu: {{ $assembly->code }} | Ngày lắp ráp: {{ \Carbon\Carbon::parse($assembly->date)->format('d/m/Y') }}
        </p>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Mã phiếu:</div>
                <div class="info-value">{{ $assembly->code }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Ngày lắp ráp:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($assembly->date)->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Trạng thái:</div>
                <div class="info-value">
                    <span
                        class="status 
                        @if ($assembly->status == 'completed') status-completed
                        @elseif($assembly->status == 'in_progress') status-in-progress  
                        @elseif($assembly->status == 'pending') status-pending
                        @else status-cancelled @endif">
                        @if ($assembly->status == 'completed')
                            Hoàn thành
                        @elseif($assembly->status == 'in_progress')
                            Đang thực hiện
                        @elseif($assembly->status == 'pending')
                            Chờ xử lý
                        @else
                            Đã hủy
                        @endif
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Người phụ trách:</div>
                <div class="info-value">{{ $assembly->assignedEmployee->name ?? 'Nhân viên' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Người kiểm thử:</div>
                <div class="info-value">{{ $assembly->tester->name ?? 'Nhân viên' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Kho xuất vật tư:</div>
                <div class="info-value">{{ $assembly->warehouse->name ?? '' }} ({{ $assembly->warehouse->code ?? '' }})
                </div>
            </div>
            @if ($assembly->targetWarehouse)
                <div class="info-row">
                    <div class="info-label">Kho nhập thành phẩm:</div>
                    <div class="info-value">{{ $assembly->targetWarehouse->name ?? '' }}
                        ({{ $assembly->targetWarehouse->code ?? '' }})</div>
                </div>
            @endif
            <div class="info-row">
                <div class="info-label">Mục đích:</div>
                <div class="info-value">
                    @if ($assembly->purpose == 'storage')
                        Lưu kho
                    @elseif($assembly->purpose == 'project')
                        Xuất đi dự án
                    @else
                        Lưu kho
                    @endif
                </div>
            </div>
            @if ($assembly->purpose == 'project' && $assembly->project)
                <div class="info-row">
                    <div class="info-label">Dự án:</div>
                    <div class="info-value">{{ $assembly->project->project_name ?? '' }}</div>
                </div>
            @endif
        </div>
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
            @if ($assembly->products && $assembly->products->count() > 0)
                @foreach ($assembly->products as $index => $assemblyProduct)
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
            @foreach ($assembly->materials as $index => $material)
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

    @if ($assembly->notes)
        <div class="notes-section">
            <div class="notes-title">Ghi chú:</div>
            <div>{{ $assembly->notes }}</div>
        </div>
    @endif

    <table class="signatures" style="margin-top: 30px; border: none;">
        <tr>
            <td class="signature-box" style="border: none; padding: 20px;">
                <div class="signature-title">Người lập phiếu</div>
                <div style="height: 50px;"></div>
                <div class="signature-name">{{ Auth::user()->employee->name ?? 'Nhân viên' }}</div>
            </td>
            <td class="signature-box" style="border: none; padding: 20px;">
                <div class="signature-title">Người phụ trách</div>
                <div style="height: 50px;"></div>
                <div class="signature-name">{{ $assembly->assignedEmployee->name ?? 'Nhân viên' }}</div>
            </td>
            <td class="signature-box" style="border: none; padding: 20px;">
                <div class="signature-title">Người kiểm thử</div>
                <div style="height: 50px;"></div>
                <div class="signature-name">{{ $assembly->tester->name ?? 'Nhân viên' }}</div>
            </td>
        </tr>
    </table>
</body>

</html>
