<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem trước phiếu khách yêu cầu bảo trì - SGL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8fafc;
            padding: 20px;
        }
        .report-container {
            background-color: white;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }
        .company-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .section-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #1a365d;
            text-transform: uppercase;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #e5e7eb;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 500;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-box {
            text-align: center;
            width: 30%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            margin-bottom: 10px;
        }
        .priority-high {
            color: #ef4444;
            font-weight: 600;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
                margin: 0;
            }
            .report-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print bg-blue-600 text-white py-4 px-6 mb-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">Xem trước phiếu khách yêu cầu bảo trì</h1>
        <div class="flex space-x-2">
            <button onclick="window.print()" class="bg-white text-blue-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-print mr-2"></i> In phiếu
            </button>
            <button onclick="exportToExcel()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-file-excel mr-2"></i> Xuất Excel
            </button>
            <a href="{{ url('/requests/customer-maintenance/'.$id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="report-container">
        <div class="report-header">
            <div class="flex justify-between items-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo công ty" height="60" class="h-16">
                <div class="text-right">
                    <h2 class="text-xl font-bold text-blue-800">CÔNG TY SGL TECH</h2>
                    <p class="text-sm text-gray-600">Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM</p>
                    <p class="text-sm text-gray-600">Điện thoại: (028) 3456 7890</p>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-center text-gray-800 mt-6">PHIẾU KHÁCH YÊU CẦU BẢO TRÌ</h1>
            <p class="text-center text-gray-600">Mã phiếu: CUST-MAINT-{{ str_pad($id, 4, '0', STR_PAD_LEFT) }}</p>
        </div>

        <div class="company-info">
            <div>
                <p class="text-sm"><strong>Ngày tiếp nhận:</strong> 25/06/2024</p>
                <p class="text-sm"><strong>Người tiếp nhận:</strong> Nguyễn Văn A</p>
            </div>
            <div class="text-right">
                <p class="text-sm"><strong>Trạng thái:</strong> Đang xử lý</p>
                <p class="text-sm"><strong>Mức độ ưu tiên:</strong> <span class="priority-high">Cao</span></p>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">THÔNG TIN KHÁCH HÀNG</h2>
            <table class="w-full">
                <tr>
                    <td class="w-1/4 bg-gray-50"><strong>Tên khách hàng/Đơn vị:</strong></td>
                    <td>Công ty TNHH Phát triển Công nghệ XYZ</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Số điện thoại:</strong></td>
                    <td>0901234567</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Email:</strong></td>
                    <td>contact@xyztech.com</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Địa chỉ:</strong></td>
                    <td>123 Đường Nguyễn Văn Linh, Quận 7, TP. HCM</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">THÔNG TIN THIẾT BỊ</h2>
            <table class="w-full">
                <tr>
                    <td class="w-1/4 bg-gray-50"><strong>Tên thiết bị:</strong></td>
                    <td>Máy chủ Dell PowerEdge R740</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Model/Serial:</strong></td>
                    <td>SN: ABC12345XYZ</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Mô tả sự cố:</strong></td>
                    <td>Máy chủ gặp lỗi không thể khởi động, đèn báo hiệu lỗi ổ cứng sáng liên tục. Cần kiểm tra và thay thế các linh kiện bị hỏng.</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">THỜI GIAN XỬ LÝ</h2>
            <table class="w-full">
                <tr>
                    <td class="w-1/4 bg-gray-50"><strong>Ngày dự kiến xử lý:</strong></td>
                    <td>15/07/2024</td>
                </tr>
                <tr>
                    <td class="bg-gray-50"><strong>Thời gian dự kiến:</strong></td>
                    <td>4 giờ</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">VẬT TƯ YÊU CẦU</h2>
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="w-1/12">STT</th>
                        <th class="w-7/12">Tên vật tư</th>
                        <th class="w-2/12">Số lượng</th>
                        <th class="w-2/12">Đơn vị</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Ổ cứng SSD 1TB</td>
                        <td>2</td>
                        <td>Cái</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Cáp SATA</td>
                        <td>4</td>
                        <td>Cái</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">GHI CHÚ</h2>
            <p class="text-gray-700 text-sm">Khách hàng yêu cầu xử lý nhanh chóng vì đây là máy chủ quan trọng phục vụ hệ thống của họ.</p>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <p><strong>Người tiếp nhận</strong></p>
                <div class="signature-line"></div>
                <p>Nguyễn Văn A</p>
            </div>
            <div class="signature-box">
                <p><strong>Người xử lý</strong></p>
                <div class="signature-line"></div>
                <p></p>
            </div>
            <div class="signature-box">
                <p><strong>Khách hàng</strong></p>
                <div class="signature-line"></div>
                <p></p>
            </div>
        </div>
    </div>

    <script>
        function exportToExcel() {
            alert('Đang xuất file Excel...');
            // Thực tế sẽ gọi API hoặc sử dụng thư viện để xuất Excel
        }
    </script>
</body>
</html> 