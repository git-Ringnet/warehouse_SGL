<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Danh sách nhà cung cấp</title>
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
        <h2>DANH SÁCH NHÀ CUNG CẤP</h2>
        <p>Ngày xuất: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    @if(!empty($filters['search']))
    <div class="filters">
        <p>
            Bộ lọc: 
            @if(!empty($filters['filter']))
                @if($filters['filter'] == 'name')
                    Tên nhà cung cấp
                @elseif($filters['filter'] == 'phone')
                    Số điện thoại
                @elseif($filters['filter'] == 'email')
                    Email
                @elseif($filters['filter'] == 'address')
                    Địa chỉ
                @endif
            @else
                Tất cả
            @endif
            - Từ khóa: "{{ $filters['search'] }}"
        </p>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên nhà cung cấp</th>
                <th>Người đại diện</th>
                <th>Số điện thoại</th>
                <th>Email</th>
                <th>Địa chỉ</th>
                <th>Tổng SL đã nhập</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suppliers as $index => $supplier)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->representative ?? 'N/A' }}</td>
                    <td>{{ $supplier->phone }}</td>
                    <td>{{ $supplier->email ?? 'N/A' }}</td>
                    <td>{{ $supplier->address ?? 'N/A' }}</td>
                    <td>{{ $supplier->total_items }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Trang <span class="page-number"></span>
    </div>
</body>

</html> 