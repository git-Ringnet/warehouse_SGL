<?php

namespace App\Exports;

use App\Models\Dispatch;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;

class DispatchExport implements FromView, ShouldAutoSize, WithStyles, WithEvents
{
    protected $dispatch;
    protected $lastDataRow;
    protected $hasGeneralItems;

    public function __construct(Dispatch $dispatch)
    {
        $this->dispatch = $dispatch;
        $this->hasGeneralItems = $dispatch->items ? $dispatch->items->where('category', 'general')->count() > 0 : false;
    }

    public function view(): View
    {
        return view('exports.dispatch', [
            'dispatch' => $this->dispatch
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Tìm dòng cuối cùng của dữ liệu
        $this->lastDataRow = $this->findLastDataRow($sheet);

        // Thiết lập style mặc định cho toàn bộ sheet
        $endCol = $this->hasGeneralItems ? 'G' : 'F';
        $sheet->getStyle('A1:' . $endCol . ($this->lastDataRow + 6))->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 11
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Style cho tiêu đề chính
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Style cho các tiêu đề phần
        $titleRows = $this->findTitleRows($sheet);
        foreach($titleRows as $row) {
            $sheet->getStyle('A' . $row . ':' . $endCol . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 13
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'E2EFDA'
                    ]
                ]
            ]);
        }

        // Style cho header bảng
        $headerRows = $this->findTableHeaderRows($sheet);
        foreach($headerRows as $row) {
            $sheet->getStyle('A' . $row . ':' . $endCol . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'E2EFDA'
                    ]
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ]);
        }

        // Style cho nội dung bảng
        $sheet->getStyle('A1:' . $endCol . $this->lastDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Căn giữa cho cột STT và Số lượng
        $sheet->getStyle("A1:A{$this->lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E1:E{$this->lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $endCol = $this->hasGeneralItems ? 'G' : 'F';
                
                // Xóa các dòng trống không cần thiết
                $this->cleanupSheet($sheet);

                // Merge cells cho tiêu đề và các phần
                $sheet->mergeCells('A1:' . $endCol . '1');
                $sheet->mergeCells('A3:' . $endCol . '3');

                // Thiết lập chiều cao hàng
                $sheet->getRowDimension(1)->setRowHeight(30);
                foreach($this->findTableHeaderRows($sheet) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(25);
                }

                // Thiết lập độ rộng cột
                $sheet->getColumnDimension('A')->setWidth(8);  // STT
                $sheet->getColumnDimension('B')->setWidth(20); // Mã
                $sheet->getColumnDimension('C')->setWidth(40); // Tên
                $sheet->getColumnDimension('D')->setWidth(15); // Đơn vị
                $sheet->getColumnDimension('E')->setWidth(12); // Số lượng
                if ($this->hasGeneralItems) {
                    $sheet->getColumnDimension('F')->setWidth(18); // Kho xuất
                    $sheet->getColumnDimension('G')->setWidth(30); // Serial
                } else {
                    $sheet->getColumnDimension('F')->setWidth(30); // Serial (6 cột)
                }

                // Thiết lập vùng in
                $sheet->getPageSetup()->setPrintArea('A1:' . $endCol . ($this->lastDataRow + 6));
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            }
        ];
    }

    private function cleanupSheet(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $rowsToDelete = [];
        $emptyRowCount = 0;

        // Tìm các dòng trống liên tiếp
        for ($row = 1; $row <= $highestRow; $row++) {
            $isEmpty = true;
            $endCol = $this->hasGeneralItems ? 'G' : 'F';
            for ($col = 'A'; $col <= $endCol; $col++) {
                $cellValue = trim($sheet->getCell($col . $row)->getValue());
                if (!empty($cellValue) && $cellValue !== '&nbsp;') {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $emptyRowCount++;
                if ($emptyRowCount > 1) { // Giữ lại một dòng trống
                    $rowsToDelete[] = $row;
                }
            } else {
                $emptyRowCount = 0;
            }
        }

        // Xóa các dòng từ dưới lên trên
        rsort($rowsToDelete);
        foreach ($rowsToDelete as $row) {
            if ($row > 1) { // Không xóa dòng đầu tiên
                $sheet->removeRow($row);
            }
        }

        // Cập nhật lại lastDataRow
        $this->lastDataRow = $this->findLastDataRow($sheet);
    }

    private function findLastDataRow(Worksheet $sheet): int
    {
        $lastRow = $sheet->getHighestRow();
        for($row = $lastRow; $row >= 1; $row--) {
            $isEmpty = true;
            $endCol = $this->hasGeneralItems ? 'G' : 'F';
            for($col = 'A'; $col <= $endCol; $col++) {
                $cellValue = trim($sheet->getCell($col . $row)->getValue());
                if (!empty($cellValue) && $cellValue !== '&nbsp;') {
                    $isEmpty = false;
                    break;
                }
            }
            if (!$isEmpty) {
                return $row;
            }
        }
        return $lastRow;
    }

    private function findTableHeaderRows(Worksheet $sheet): array
    {
        $headerRows = [];
        $lastRow = $this->lastDataRow;
        
        for($row = 1; $row <= $lastRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if ($cellValue === 'STT') {
                $headerRows[] = $row;
            }
        }
        
        return $headerRows;
    }

    private function findTitleRows(Worksheet $sheet): array
    {
        $titleRows = [];
        $lastRow = $this->lastDataRow;
        
        for($row = 1; $row <= $lastRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if (strpos($cellValue, 'Danh sách thiết bị') !== false) {
                $titleRows[] = $row;
            }
        }
        
        return $titleRows;
    }
} 