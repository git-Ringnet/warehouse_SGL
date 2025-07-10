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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .status-active {
            color: #155724;
        }
        .status-inactive {
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">DANH SÁCH NHÂN VIÊN</div>
            <div class="subtitle">Hệ thống quản lý kho SGL</div>
            <div class="subtitle">Ngày xuất: {{ date('d/m/Y H:i:s') }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">STT</th>
                    <th style="width: 15%;">Tên đăng nhập</th>
                    <th style="width: 20%;">Họ và tên</th>
                    <th style="width: 15%;">Số điện thoại</th>
                    <th style="width: 20%;">Email</th>
                    <th style="width: 15%;">Phòng ban</th>
                    <th style="width: 10%;">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $index => $employee)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $employee->username }}</td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->phone }}</td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->department }}</td>
                        <td class="text-center {{ $employee->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $employee->is_active ? 'Hoạt động' : 'Khóa' }}
                        </td>
                    </tr>
                @endforeach

                @if (count($employees) === 0)
                    <tr>
                        <td colspan="7" class="text-center" style="font-style: italic;">Không tìm thấy nhân viên nào</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="footer">
            <p>Người xuất: {{ auth()->user()->name ?? 'Admin' }}</p>
        </div>
    </div>
</body>
</html> 