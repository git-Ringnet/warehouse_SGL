<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Nhân viên</title>
    <style>
        @page {
            margin: 10px;
            padding: 0px;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 10px;
            color: #333;
            width: 100%;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            width: 100%;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #333;
            text-transform: uppercase;
        }
        
        .header p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #fff;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 10px;
            word-wrap: break-word;
            max-width: 150px;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            color: #333;
            padding: 8px 4px;
            white-space: nowrap;
        }
        
        td {
            text-align: left;
            vertical-align: middle;
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
        
        .status-active {
            color: #28a745;
            font-weight: bold;
            background-color: #e3fcec;
            padding: 3px 6px;
            border-radius: 3px;
            display: inline-block;
            font-size: 10px;
        }
        
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
            background-color: #fce3e3;
            padding: 3px 6px;
            border-radius: 3px;
            display: inline-block;
            font-size: 10px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
            width: 100%;
        }
        
        .footer p {
            margin: 0;
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
                <th style="width: 4%;">STT</th>
                <th style="width: 10%;">Username</th>
                <th style="width: 13%;">Họ và tên</th>
                <th style="width: 15%;">Email</th>
                <th style="width: 10%;">Số điện thoại</th>
                <th style="width: 12%;">Vai trò</th>
                <th style="width: 12%;">Phòng ban</th>
                <th style="width: 14%;">Trạng thái</th>
                <th style="width: 10%;">Ngày tạo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $employee)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $employee->username }}</td>
                <td>{{ $employee->name }}</td>
                <td>{{ $employee->email ?? 'N/A' }}</td>
                <td class="text-center">{{ $employee->phone }}</td>
                <td class="text-center">{{ $employee->roleGroup ? $employee->roleGroup->name : 'Chưa phân quyền' }}</td>
                <td class="text-center">{{ $employee->department ?? 'Chưa phân công' }}</td>
                <td class="text-center">
                    <span class="{{ $employee->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $employee->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                    </span>
                </td>
                <td class="text-center">{{ $employee->created_at ? $employee->created_at->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Báo cáo được tạo từ hệ thống quản lý kho SGL</p>
    </div>
</body>
</html> 