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

        $sheet->setCellValue('A14', 'Tên dự án/thiết bị:');
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
        // TODO: Implement project request export
    }

    private function exportMaintenanceRequest($id)
    {
        // TODO: Implement maintenance request export
    }
} 