<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Thành phẩm</title>
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
        
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DANH SÁCH THÀNH PHẨM</h1>
        <p>Xuất ngày: {{ date('d/m/Y H:i:s') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Mã thành phẩm</th>
                <th>Tên thành phẩm</th>
                <th>Tổng tồn kho</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $product)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $product->code }}</td>
                <td>{{ $product->name }}</td>
                <td class="text-right">{{ number_format($product->inventory_quantity, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 30px; text-align: center; font-size: 11px; color: #666;">
        <p>Báo cáo được tạo từ hệ thống quản lý kho SGL</p>
    </div>
</body>
</html> 