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

    public function __construct(Dispatch $dispatch)
    {
        $this->dispatch = $dispatch;
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
        $sheet->getStyle('A1:F' . ($this->lastDataRow + 6))->applyFromArray([
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

        // Style cho các tiêu đề phần (bao gồm cả tiêu đề bảng)
        $titleRows = $this->findTitleRows($sheet);
        foreach($titleRows as $row) {
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 13
                ]
            ]);
        }

        // Style cho header bảng
        $headerRows = $this->findTableHeaderRows($sheet);
        foreach($headerRows as $row) {
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font' => [
                    'bold' => true
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
        $sheet->getStyle("A1:F{$this->lastDataRow}")->applyFromArray([
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
                
                // Xóa tất cả các dòng từ lastDataRow + 7 trở đi
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $this->lastDataRow + 6);
                $sheet->removeRow($this->lastDataRow + 7, $sheet->getHighestRow());

                // Merge cells cho tiêu đề và các phần
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A3:F3');

                // Thiết lập chiều cao hàng
                $sheet->getRowDimension(1)->setRowHeight(30);
                foreach($this->findTableHeaderRows($sheet) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(25);
                }

                // Thiết lập độ rộng cột
                $sheet->getColumnDimension('A')->setWidth(8);  // STT
                $sheet->getColumnDimension('B')->setWidth(20); // Mã
                $sheet->getColumnDimension('C')->setWidth(40); // Tên thiết bị
                $sheet->getColumnDimension('D')->setWidth(15); // Đơn vị
                $sheet->getColumnDimension('E')->setWidth(12); // Số lượng
                $sheet->getColumnDimension('F')->setWidth(30); // Serial

                // Thiết lập vùng in
                $sheet->getPageSetup()->setPrintArea('A1:F' . ($this->lastDataRow + 6));
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            }
        ];
    }

    private function findLastDataRow(Worksheet $sheet): int
    {
        $lastRow = $sheet->getHighestRow();
        for($row = $lastRow; $row >= 1; $row--) {
            $isEmpty = true;
            for($col = 'A'; $col <= 'F'; $col++) {
                $cellValue = $sheet->getCell($col . $row)->getValue();
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
            if (strpos($cellValue, 'Danh sách thiết bị') !== false || 
                $cellValue === 'Thông tin phiếu xuất') {
                $titleRows[] = $row;
            }
        }
        
        return $titleRows;
    }
} 