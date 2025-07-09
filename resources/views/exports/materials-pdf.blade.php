<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Danh sách vật tư</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .filters {
            margin-bottom: 20px;
            font-style: italic;
        }

        .page-number {
            text-align: right;
            font-size: 10px;
            color: #666;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>DANH SÁCH VẬT TƯ</h2>
        <p>Ngày xuất: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Mã vật tư</th>
                <th>Tên vật tư</th>
                <th>Loại</th>
                <th>Đơn vị</th>
                <th>Tổng tồn kho</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($materials as $index => $material)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $material->code }}</td>
                    <td>{{ $material->name }}</td>
                    <td>{{ $material->category }}</td>
                    <td>{{ $material->unit }}</td>
                    <td>{{ number_format($material->inventory_quantity, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Trang <span class="page-number"></span>
    </div>
</body>

</html>
