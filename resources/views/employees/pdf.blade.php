<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Nhân viên</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #333;
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
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center {
            text-align: center;
        }
        
        .status-active {
            color: green;
            font-weight: bold;
        }
        
        .status-inactive {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DANH SÁCH NHÂN VIÊN</h1>
        <p>Xuất ngày: {{ date('d/m/Y H:i:s') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Username</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Vai trò</th>
                <th>Phòng ban</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $employee)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $employee->username }}</td>
                <td>{{ $employee->name }}</td>
                <td>{{ $employee->email ?? 'N/A' }}</td>
                <td>{{ $employee->phone }}</td>
                <td>{{ $employee->roleGroup ? $employee->roleGroup->name : 'Chưa phân quyền' }}</td>
                <td>{{ $employee->department ?? 'Chưa phân công' }}</td>
                <td class="text-center {{ $employee->is_active ? 'status-active' : 'status-inactive' }}">
                    {{ $employee->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 30px; text-align: center; font-size: 11px; color: #666;">
        <p>Báo cáo được tạo từ hệ thống quản lý kho SGL</p>
    </div>
</body>
</html> 