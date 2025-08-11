<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\RequestExport;
use App\Models\ProjectRequest;
use App\Models\MaintenanceRequest;
use App\Models\CustomerMaintenanceRequest;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RequestExportController extends Controller
{
    public function exportExcel($type, $id)
    {
        switch ($type) {
            case 'customer-maintenance':
                return $this->exportCustomerMaintenanceRequest($id);
            case 'project':
                return $this->exportProjectRequest($id);
            case 'maintenance':
                return $this->exportMaintenanceRequest($id);
            default:
                abort(404);
        }
    }

    public function exportPDF($type, $id)
    {
        if ($type === 'project') {
            $request = ProjectRequest::with(['proposer', 'customer', 'items'])->findOrFail($id);
            $view = 'exports.project';
            $filename = 'project_request_';
        } elseif ($type === 'maintenance') {
            $request = MaintenanceRequest::with(['proposer', 'customer', 'products', 'staff'])->findOrFail($id);
            $view = 'exports.maintenance';
            $filename = 'maintenance_request_';
        } elseif ($type === 'customer-maintenance') {
            $request = CustomerMaintenanceRequest::with(['customer', 'approvedByUser'])->findOrFail($id);
            $view = 'exports.customer-maintenance';
            $filename = 'customer_maintenance_request_';
        }

        $filename .= $id . '_' . date('YmdHis') . '.pdf';

        $pdf = PDF::loadView($view, ['request' => $request])
            ->setPaper('a4')
            ->setWarnings(false)
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'isPhpEnabled' => true,
                'isJavascriptEnabled' => true,
                'chroot' => public_path(),
            ]);

        return $pdf->download($filename);
    }

    private function exportCustomerMaintenanceRequest($id)
    {
        $request = CustomerMaintenanceRequest::with(['customer', 'approvedByUser'])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'PHIẾU YÊU CẦU BẢO TRÌ');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Set headers
        $sheet->setCellValue('A3', 'Mã phiếu:');
        $sheet->setCellValue('B3', $request->request_code);
        $sheet->setCellValue('A4', 'Ngày tạo:');
        $sheet->setCellValue('B4', $request->request_date->format('d/m/Y'));
        $sheet->setCellValue('A5', 'Trạng thái:');
        $sheet->setCellValue('B5', $this->getStatusText($request->status));

        // Thông tin khách hàng
        $sheet->mergeCells('A7:G7');
        $sheet->setCellValue('A7', 'THÔNG TIN KHÁCH HÀNG');
        $sheet->getStyle('A7')->getFont()->setBold(true);

        $sheet->setCellValue('A8', 'Tên khách hàng:');
        $sheet->setCellValue('B8', $request->customer ? $request->customer->company_name : $request->customer_name);
        $sheet->setCellValue('A9', 'Số điện thoại:');
        $sheet->setCellValue('B9', $request->customer_phone);
        $sheet->setCellValue('A10', 'Email:');
        $sheet->setCellValue('B10', $request->customer_email);
        $sheet->setCellValue('A11', 'Địa chỉ:');
        $sheet->setCellValue('B11', $request->customer_address);

        // Thông tin yêu cầu
        $sheet->mergeCells('A13:G13');
        $sheet->setCellValue('A13', 'THÔNG TIN YÊU CẦU');
        $sheet->getStyle('A13')->getFont()->setBold(true);

        $sheet->setCellValue('A14', 'Tên dự án/Tên cho thuê:');
        $sheet->setCellValue('B14', $request->project_name);
        $sheet->setCellValue('A15', 'Mức độ ưu tiên:');
        $sheet->setCellValue('B15', $this->getPriorityText($request->priority));


        // Chi tiết yêu cầu
        $sheet->mergeCells('A18:G18');
        $sheet->setCellValue('A18', 'CHI TIẾT YÊU CẦU');
        $sheet->getStyle('A18')->getFont()->setBold(true);

        $sheet->setCellValue('A19', 'Lý do yêu cầu bảo trì:');
        $sheet->setCellValue('B19', $request->maintenance_reason);
        $sheet->setCellValue('A20', 'Chi tiết bảo trì:');
        $sheet->setCellValue('B20', $request->maintenance_details);

        // Thông tin kiểm duyệt
        $sheet->mergeCells('A22:G22');
        $sheet->setCellValue('A22', 'THÔNG TIN KIỂM DUYỆT');
        $sheet->getStyle('A22')->getFont()->setBold(true);

        $sheet->setCellValue('A23', 'Người kiểm duyệt:');
        $sheet->setCellValue('B23', $request->approvedByUser ? $request->approvedByUser->name : 'Chưa được duyệt');
        $sheet->setCellValue('A24', 'Thời gian duyệt:');
        $sheet->setCellValue('B24', $request->approved_at ? $request->approved_at->format('d/m/Y H:i:s') : 'Chưa được duyệt');
        
        if ($request->status === 'rejected') {
            $sheet->setCellValue('A25', 'Lý do từ chối:');
            $sheet->setCellValue('B25', $request->rejection_reason);
        }

        // Ghi chú
        if ($request->notes) {
            $sheet->mergeCells('A27:G27');
            $sheet->setCellValue('A27', 'GHI CHÚ');
            $sheet->getStyle('A27')->getFont()->setBold(true);
            $sheet->setCellValue('A28', $request->notes);
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create the Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'phieu-bao-tri-' . $request->request_code . '.xlsx';

        // Return the file for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 'pending':
                return 'Chờ duyệt';
            case 'approved':
                return 'Đã duyệt';
            case 'rejected':
                return 'Từ chối';
            case 'in_progress':
                return 'Đang thực hiện';
            case 'completed':
                return 'Hoàn thành';
            case 'canceled':
                return 'Đã hủy';
            default:
                return 'Không xác định';
        }
    }

    private function getPriorityText($priority)
    {
        switch ($priority) {
            case 'low':
                return 'Thấp';
            case 'medium':
                return 'Trung bình';
            case 'high':
                return 'Cao';
            case 'urgent':
                return 'Khẩn cấp';
            default:
                return 'Không xác định';
        }
    }

    private function exportProjectRequest($id)
    {
        $request = ProjectRequest::with(['proposer', 'customer', 'items'])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'PHIẾU ĐỀ XUẤT TRIỂN KHAI DỰ ÁN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Set headers
        $sheet->setCellValue('A3', 'Mã phiếu:');
        $sheet->setCellValue('B3', $request->request_code);
        $sheet->setCellValue('A4', 'Ngày tạo:');
        $sheet->setCellValue('B4', $request->request_date->format('d/m/Y'));
        $sheet->setCellValue('A5', 'Trạng thái:');
        $sheet->setCellValue('B5', $this->getStatusText($request->status));

        // Thông tin người đề xuất
        $sheet->mergeCells('A7:H7');
        $sheet->setCellValue('A7', 'THÔNG TIN NGƯỜI ĐỀ XUẤT');
        $sheet->getStyle('A7')->getFont()->setBold(true);

        $sheet->setCellValue('A8', 'Người đề xuất:');
        $sheet->setCellValue('B8', $request->proposer ? $request->proposer->name : 'N/A');
        $sheet->setCellValue('A9', 'Chức vụ:');
        $sheet->setCellValue('B9', $request->proposer ? $request->proposer->position : 'N/A');

        // Thông tin khách hàng
        $sheet->mergeCells('A11:H11');
        $sheet->setCellValue('A11', 'THÔNG TIN KHÁCH HÀNG');
        $sheet->getStyle('A11')->getFont()->setBold(true);

        $sheet->setCellValue('A12', 'Tên khách hàng:');
        $sheet->setCellValue('B12', $request->customer ? $request->customer->company_name : $request->customer_name);
        $sheet->setCellValue('A13', 'Số điện thoại:');
        $sheet->setCellValue('B13', $request->customer_phone);
        $sheet->setCellValue('A14', 'Email:');
        $sheet->setCellValue('B14', $request->customer_email);
        $sheet->setCellValue('A15', 'Địa chỉ:');
        $sheet->setCellValue('B15', $request->customer_address);

        // Thông tin dự án
        $sheet->mergeCells('A17:H17');
        $sheet->setCellValue('A17', 'THÔNG TIN DỰ ÁN');
        $sheet->getStyle('A17')->getFont()->setBold(true);

        $sheet->setCellValue('A18', 'Tên dự án:');
        $sheet->setCellValue('B18', $request->project_name);
        $sheet->setCellValue('A19', 'Mô tả dự án:');
        $sheet->setCellValue('B19', $request->project_description);
        $sheet->setCellValue('A20', 'Địa chỉ dự án:');
        $sheet->setCellValue('B20', $request->project_address);
        $sheet->setCellValue('A21', 'Ngày dự kiến hoàn thành:');
        $sheet->setCellValue('B21', $request->expected_completion_date ? $request->expected_completion_date->format('d/m/Y') : 'N/A');

        // Danh sách thiết bị/vật tư
        if ($request->items && $request->items->count() > 0) {
            $sheet->mergeCells('A23:H23');
            $sheet->setCellValue('A23', 'DANH SÁCH THIẾT BỊ/VẬT TƯ');
            $sheet->getStyle('A23')->getFont()->setBold(true);

            // Headers
            $sheet->setCellValue('A24', 'STT');
            $sheet->setCellValue('B24', 'Loại');
            $sheet->setCellValue('C24', 'Tên thiết bị/vật tư');
            $sheet->setCellValue('D24', 'Mã');
            $sheet->setCellValue('E24', 'Số lượng');
            $sheet->setCellValue('F24', 'Đơn vị');
            $sheet->setCellValue('G24', 'Ghi chú');

            $row = 25;
            foreach ($request->items as $index => $item) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $this->getItemTypeText($item->item_type));
                $sheet->setCellValue('C' . $row, $item->item_name);
                $sheet->setCellValue('D' . $row, $item->item_code);
                $sheet->setCellValue('E' . $row, $item->quantity);
                $sheet->setCellValue('F' . $row, $item->unit);
                $sheet->setCellValue('G' . $row, $item->notes);
                $row++;
            }
        }

        // Thông tin kiểm duyệt
        $sheet->mergeCells('A' . ($row + 1) . ':H' . ($row + 1));
        $sheet->setCellValue('A' . ($row + 1), 'THÔNG TIN KIỂM DUYỆT');
        $sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);

        $sheet->setCellValue('A' . ($row + 2), 'Người kiểm duyệt:');
        $sheet->setCellValue('B' . ($row + 2), $request->approvedByUser ? $request->approvedByUser->name : 'Chưa được duyệt');
        $sheet->setCellValue('A' . ($row + 3), 'Thời gian duyệt:');
        $sheet->setCellValue('B' . ($row + 3), $request->approved_at ? $request->approved_at->format('d/m/Y H:i:s') : 'Chưa được duyệt');

        // Ghi chú
        if ($request->notes) {
            $sheet->mergeCells('A' . ($row + 5) . ':H' . ($row + 5));
            $sheet->setCellValue('A' . ($row + 5), 'GHI CHÚ');
            $sheet->getStyle('A' . ($row + 5))->getFont()->setBold(true);
            $sheet->setCellValue('A' . ($row + 6), $request->notes);
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create the Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'phieu-de-xuat-' . $request->request_code . '.xlsx';

        // Return the file for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function exportMaintenanceRequest($id)
    {
        $request = MaintenanceRequest::with(['proposer', 'customer', 'products', 'staff'])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'PHIẾU BẢO TRÌ DỰ ÁN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Set headers
        $sheet->setCellValue('A3', 'Mã phiếu:');
        $sheet->setCellValue('B3', $request->request_code);
        $sheet->setCellValue('A4', 'Ngày tạo:');
        $sheet->setCellValue('B4', $request->request_date->format('d/m/Y'));
        $sheet->setCellValue('A5', 'Trạng thái:');
        $sheet->setCellValue('B5', $this->getStatusText($request->status));

        // Thông tin người đề xuất
        $sheet->mergeCells('A7:H7');
        $sheet->setCellValue('A7', 'THÔNG TIN NGƯỜI ĐỀ XUẤT');
        $sheet->getStyle('A7')->getFont()->setBold(true);

        $sheet->setCellValue('A8', 'Người đề xuất:');
        $sheet->setCellValue('B8', $request->proposer ? $request->proposer->name : 'N/A');
        $sheet->setCellValue('A9', 'Chức vụ:');
        $sheet->setCellValue('B9', $request->proposer ? $request->proposer->position : 'N/A');

        // Thông tin khách hàng
        $sheet->mergeCells('A11:H11');
        $sheet->setCellValue('A11', 'THÔNG TIN KHÁCH HÀNG');
        $sheet->getStyle('A11')->getFont()->setBold(true);

        $sheet->setCellValue('A12', 'Tên khách hàng:');
        $sheet->setCellValue('B12', $request->customer ? $request->customer->company_name : $request->customer_name);
        $sheet->setCellValue('A13', 'Số điện thoại:');
        $sheet->setCellValue('B13', $request->customer_phone);
        $sheet->setCellValue('A14', 'Email:');
        $sheet->setCellValue('B14', $request->customer_email);
        $sheet->setCellValue('A15', 'Địa chỉ:');
        $sheet->setCellValue('B15', $request->customer_address);

        // Thông tin dự án
        $sheet->mergeCells('A17:H17');
        $sheet->setCellValue('A17', 'THÔNG TIN DỰ ÁN');
        $sheet->getStyle('A17')->getFont()->setBold(true);

        $sheet->setCellValue('A18', 'Tên dự án:');
        $sheet->setCellValue('B18', $request->project_name);
        $sheet->setCellValue('A19', 'Địa chỉ dự án:');
        $sheet->setCellValue('B19', $request->project_address);
        $sheet->setCellValue('A20', 'Ngày bảo trì:');
        $sheet->setCellValue('B20', $request->maintenance_date ? $request->maintenance_date->format('d/m/Y') : 'N/A');
        $sheet->setCellValue('A21', 'Loại bảo trì:');
        $sheet->setCellValue('B21', $this->getMaintenanceTypeText($request->maintenance_type));
        $sheet->setCellValue('A22', 'Lý do bảo trì:');
        $sheet->setCellValue('B22', $request->maintenance_reason);

        // Danh sách thiết bị cần bảo trì
        if ($request->products && $request->products->count() > 0) {
            $sheet->mergeCells('A24:H24');
            $sheet->setCellValue('A24', 'DANH SÁCH THIẾT BỊ CẦN BẢO TRÌ');
            $sheet->getStyle('A24')->getFont()->setBold(true);

            // Headers
            $sheet->setCellValue('A25', 'STT');
            $sheet->setCellValue('B25', 'Tên thiết bị');
            $sheet->setCellValue('C25', 'Mã thiết bị');
            $sheet->setCellValue('D25', 'Số lượng');
            $sheet->setCellValue('E25', 'Tình trạng');
            $sheet->setCellValue('F25', 'Ghi chú');

            $row = 26;
            foreach ($request->products as $index => $product) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $product->product_name);
                $sheet->setCellValue('C' . $row, $product->product_code);
                $sheet->setCellValue('D' . $row, $product->quantity);
                $sheet->setCellValue('E' . $row, $product->condition);
                $sheet->setCellValue('F' . $row, $product->notes);
                $row++;
            }
        }

        // Danh sách nhân sự thực hiện
        if ($request->staff && $request->staff->count() > 0) {
            $sheet->mergeCells('A' . ($row + 1) . ':H' . ($row + 1));
            $sheet->setCellValue('A' . ($row + 1), 'DANH SÁCH NHÂN SỰ THỰC HIỆN');
            $sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);

            // Headers
            $sheet->setCellValue('A' . ($row + 2), 'STT');
            $sheet->setCellValue('B' . ($row + 2), 'Tên nhân viên');
            $sheet->setCellValue('C' . ($row + 2), 'Chức vụ');
            $sheet->setCellValue('D' . ($row + 2), 'Số điện thoại');

            $staffRow = $row + 3;
            foreach ($request->staff as $index => $staff) {
                $sheet->setCellValue('A' . $staffRow, $index + 1);
                $sheet->setCellValue('B' . $staffRow, $staff->name);
                $sheet->setCellValue('C' . $staffRow, $staff->position);
                $sheet->setCellValue('D' . $staffRow, $staff->phone);
                $staffRow++;
            }
            $row = $staffRow;
        }

        // Thông tin kiểm duyệt
        $sheet->mergeCells('A' . ($row + 1) . ':H' . ($row + 1));
        $sheet->setCellValue('A' . ($row + 1), 'THÔNG TIN KIỂM DUYỆT');
        $sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);

        $sheet->setCellValue('A' . ($row + 2), 'Người kiểm duyệt:');
        $sheet->setCellValue('B' . ($row + 2), $request->approvedByUser ? $request->approvedByUser->name : 'Chưa được duyệt');
        $sheet->setCellValue('A' . ($row + 3), 'Thời gian duyệt:');
        $sheet->setCellValue('B' . ($row + 3), $request->approved_at ? $request->approved_at->format('d/m/Y H:i:s') : 'Chưa được duyệt');

        // Ghi chú
        if ($request->notes) {
            $sheet->mergeCells('A' . ($row + 5) . ':H' . ($row + 5));
            $sheet->setCellValue('A' . ($row + 5), 'GHI CHÚ');
            $sheet->getStyle('A' . ($row + 5))->getFont()->setBold(true);
            $sheet->setCellValue('A' . ($row + 6), $request->notes);
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create the Excel file
        $writer = new Xlsx($spreadsheet);
        $filename = 'phieu-bao-tri-du-an-' . $request->request_code . '.xlsx';

        // Return the file for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function getItemTypeText($type)
    {
        switch ($type) {
            case 'product':
                return 'Thiết bị';
            case 'material':
                return 'Vật tư';
            case 'good':
                return 'Hàng hóa';
            default:
                return 'Không xác định';
        }
    }

    private function getMaintenanceTypeText($type)
    {
        switch ($type) {
            case 'preventive':
                return 'Bảo trì phòng ngừa';
            case 'corrective':
                return 'Bảo trì khắc phục';
            case 'emergency':
                return 'Bảo trì khẩn cấp';
            default:
                return 'Không xác định';
        }
    }
} 