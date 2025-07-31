<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhật ký thay đổi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background: #e4e6ef; text-align: center; }
        td.numeric { text-align: right; }
    </style>
</head>
<body>
    <h3 style="text-align:center">NHẬT KÝ THAY ĐỔI</h3>
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Thời gian</th>
                <th>Mã vật tư/Thành Phẩm/Hàng Hóa</th>
                <th>Tên vật tư/Thành Phẩm/Hàng Hóa</th>
                <th>Loại hình</th>
                <th>Mã phiếu</th>
                <th>Số lượng</th>
                <th>Mô tả</th>
                <th>Người thực hiện</th>
                <th>Ghi chú</th>
            </tr>
        </thead>
        <tbody>
        @php $stt = 1; @endphp
        @foreach($logs as $log)
            <tr>
                <td class="numeric">{{ $stt++ }}</td>
                <td>{{ optional($log->time_changed)->format('d/m/Y H:i') }}</td>
                <td>{{ $log->item_code }}</td>
                <td>{{ $log->item_name }}</td>
                <td>{{ $log->getChangeTypeLabel() }}</td>
                <td>{{ $log->document_code }}</td>
                <td class="numeric">{{ $log->quantity }}</td>
                <td>{{ $log->description }}</td>
                <td>{{ $log->performed_by }}</td>
                <td>{{ $log->notes }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
