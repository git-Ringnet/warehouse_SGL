<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem trước phiếu đề xuất triển khai dự án - PRJ-00{{ $id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f3f4f6;
            padding: 20px;
        }
        .excel-sheet {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            max-width: 1024px;
            padding: 20px;
        }
        .excel-header {
            border-bottom: 2px solid #d1d5db;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .excel-title {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            text-align: center;
            margin-bottom: 10px;
        }
        .excel-subtitle {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .excel-table {
            width: 100%;
            border-collapse: collapse;
        }
        .excel-table th, .excel-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
        }
        .excel-table th {
            background-color: #e5e7eb;
            font-weight: 500;
            text-align: center;
        }
        .excel-table td {
            vertical-align: top;
        }
        .excel-section {
            margin-bottom: 20px;
        }
        .excel-section-title {
            font-weight: bold;
            margin-bottom: 10px;
            background-color: #e5e7eb;
            padding: 5px 10px;
        }
        .excel-row {
            display: flex;
            border-bottom: 1px solid #d1d5db;
        }
        .excel-row:last-child {
            border-bottom: none;
        }
        .excel-cell {
            padding: 8px;
            flex: 1;
            border-right: 1px solid #d1d5db;
        }
        .excel-cell:last-child {
            border-right: none;
        }
        .excel-cell-header {
            font-weight: 500;
            background-color: #f3f4f6;
        }
        .excel-footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .excel-signature {
            flex: 1;
            text-align: center;
            padding: 0 20px;
        }
        .excel-signature-title {
            font-weight: bold;
            margin-bottom: 60px;
        }
        .excel-signature-name {
            font-weight: 500;
        }
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .excel-sheet {
                box-shadow: none;
                border: none;
                max-width: 100%;
            }
            .print-controls {
                display: none;
            }
            .excel-footer {
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls mb-4 flex justify-between items-center">
        <div class="flex space-x-2">
            <button onclick="window.print();" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                <i class="fas fa-print mr-2"></i> In phiếu
            </button>
            <button onclick="exportToExcel();" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center">
                <i class="fas fa-file-excel mr-2"></i> Xuất Excel
            </button>
        </div>
        <button onclick="window.close();" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            Đóng
        </button>
    </div>
    
    <div class="excel-sheet" id="excel-content">
        <div class="excel-header">
            <div class="excel-title">PHIẾU ĐỀ XUẤT TRIỂN KHAI DỰ ÁN</div>
            <div class="excel-subtitle">Mã phiếu: PRJ-00{{ $id }} | Ngày tạo: 28/05/2024</div>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">1. THÔNG TIN ĐỀ XUẤT</div>
            <div class="excel-row">
                <div class="excel-cell excel-cell-header">Ngày đề xuất:</div>
                <div class="excel-cell">28/05/2024</div>
                <div class="excel-cell excel-cell-header">Kỹ thuật đề xuất:</div>
                <div class="excel-cell">Duy Đức</div>
            </div>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">2. THÔNG TIN DỰ ÁN</div>
            <div class="excel-row">
                <div class="excel-cell excel-cell-header">Tên dự án:</div>
                <div class="excel-cell">Đất Đỏ</div>
                <div class="excel-cell excel-cell-header">Đối tác:</div>
                <div class="excel-cell">VNPT Vũng Tàu</div>
            </div>
            <div class="excel-row">
                <div class="excel-cell excel-cell-header">Địa chỉ dự án:</div>
                <div class="excel-cell" colspan="3">Thị trấn Đất Đỏ</div>
            </div>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">3. THIẾT BỊ ĐỀ XUẤT</div>
            <table class="excel-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">STT</th>
                        <th>Tên thiết bị</th>
                        <th style="width: 100px;">Số lượng</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td>Cụm thu phát thanh</td>
                        <td style="text-align: center;">3</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">2</td>
                        <td>Bộ điều khiển trung tâm</td>
                        <td style="text-align: center;">1</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">3</td>
                        <td>Bộ thu không dây</td>
                        <td style="text-align: center;">2</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">4. VẬT TƯ ĐỀ XUẤT</div>
            <table class="excel-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">STT</th>
                        <th>Tên vật tư</th>
                        <th style="width: 100px;">Số lượng</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td>Dây điện 1.5mm</td>
                        <td style="text-align: center;">100</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">2</td>
                        <td>Dây tín hiệu 2 lõi</td>
                        <td style="text-align: center;">50</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">3</td>
                        <td>Ống nhựa xoắn</td>
                        <td style="text-align: center;">30</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">5. THÔNG TIN LIÊN HỆ KHÁCH HÀNG</div>
            <div class="excel-row">
                <div class="excel-cell excel-cell-header">Tên khách hàng:</div>
                <div class="excel-cell">Nguyễn Minh Tài</div>
                <div class="excel-cell excel-cell-header">Số điện thoại:</div>
                <div class="excel-cell">982.133.564</div>
            </div>
            <div class="excel-row">
                <div class="excel-cell excel-cell-header">Email:</div>
                <div class="excel-cell">minhta@gmail.com</div>
                <div class="excel-cell excel-cell-header">Địa chỉ:</div>
                <div class="excel-cell">1 Đất Đỏ, Thị trấn Long Điền, huyện Long Đất, Tỉnh BRVT</div>
            </div>
        </div>
        
        <div class="excel-section">
            <div class="excel-section-title">6. GHI CHÚ</div>
            <div style="border: 1px solid #d1d5db; padding: 10px;">
                <p>Thi công trước ngày 15/06/2024. Yêu cầu kỹ thuật lắp đặt đúng kỹ thuật và đảm bảo an toàn.</p>
                <p>Liên hệ với khách hàng trước khi tiến hành thi công ít nhất 2 ngày.</p>
            </div>
        </div>
        
        <div class="excel-footer">
            <div class="excel-signature">
                <div class="excel-signature-title">Người đề xuất</div>
                <div class="excel-signature-name">Duy Đức</div>
            </div>
            <div class="excel-signature">
                <div class="excel-signature-title">Quản lý dự án</div>
                <div class="excel-signature-name"></div>
            </div>
            <div class="excel-signature">
                <div class="excel-signature-title">Giám đốc</div>
                <div class="excel-signature-name"></div>
            </div>
        </div>
    </div>

    <script>
        function exportToExcel() {
            // Trong thực tế, sẽ cần một thư viện như SheetJS để xuất Excel đúng định dạng
            // Đây chỉ là mô phỏng hành động xuất Excel
            alert('Đang tải xuống file Excel...');
            
            // Đường dẫn thực tế đến API xuất file Excel
            // window.location.href = '/api/requests/project/{{ $id }}/export-excel';
        }
    </script>
</body>
</html> 