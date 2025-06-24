<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-row {
            margin-bottom: 10px;
            display: flex;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .info-value {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">PHIẾU ĐỀ XUẤT TRIỂN KHAI DỰ ÁN</div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Mã phiếu:</div>
                <div class="info-value">{{ $request->request_code }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Ngày tạo:</div>
                <div class="info-value">{{ $request->request_date->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Người đề xuất:</div>
                <div class="info-value">{{ $request->proposer->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tên dự án:</div>
                <div class="info-value">{{ $request->project_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Khách hàng:</div>
                <div class="info-value">{{ $request->customer ? $request->customer->company_name : $request->customer_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Mô tả dự án:</div>
                <div class="info-value">{{ $request->project_description }}</div>
            </div>
        </div>

        <div class="section-title">Danh sách sản phẩm</div>
        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã sản phẩm</th>
                    <th>Tên sản phẩm</th>
                    <th>Đơn vị</th>
                    <th>Số lượng</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                @foreach($request->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->notes }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section">
            <div class="section-title">Thành phẩm cần triển khai</div>
            <table>
                <thead>
                    <tr>
                        <th>Mã thành phẩm</th>
                        <th>Tên thành phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn vị</th>
                        <th>Mô tả</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($request->items as $item)
                    <tr>
                        <td>{{ $item->code }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ $item->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 