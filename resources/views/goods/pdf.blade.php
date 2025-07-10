<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách hàng hóa</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 15px;
        }
        .date {
            font-size: 12px;
            margin-bottom: 10px;
        }
        .filter-info {
            font-size: 11px;
            font-style: italic;
            margin-bottom: 10px;
            text-align: left;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 11px;
        }
        .page-number {
            font-size: 10px;
            text-align: right;
        }
        .inventory-zero {
            background-color: #ffe6e6;
        }
        .inventory-positive {
            background-color: #e6ffe6;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">DANH SÁCH HÀNG HÓA</div>
        <div class="subtitle">Hệ thống quản lý kho SGL</div>
        <div class="date">Ngày xuất: {{ date('d/m/Y H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 12%;" class="text-start">Mã hàng hóa</th>
                <th style="width: 25%;" class="text-start">Tên hàng hóa</th>
                <th style="width: 12%;" class="text-start">Loại</th>
                <th style="width: 8%;">Đơn vị</th>
                <th style="width: 13%;">Tổng tồn kho</th>
            </tr>
        </thead>
        <tbody>
            @foreach($goods as $index => $good)
                <tr class="{{ $good->inventory_quantity > 0 ? 'inventory-positive' : 'inventory-zero' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $good->code }}</td>
                    <td>{{ $good->name }}</td>
                    <td>{{ $good->category }}</td>
                    <td class="text-center">{{ $good->unit }}</td>
                    <td class="text-center">{{ number_format($good->inventory_quantity, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            @if(count($goods) === 0)
                <tr>
                    <td colspan="7" class="text-center" style="font-style: italic;">Không tìm thấy hàng hóa nào</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Người xuất: {{ auth()->user()->name ?? 'Admin' }}</p>
    </div>
</body>
</html> 