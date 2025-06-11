<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MaterialsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function array(): array
    {
        return [
            [
                'VT001',
                'Ốc vít thông dụng M6',
                'Linh kiện',
                'Cái',
                'Ghi chú mẫu',
                'all'
            ],
            [
                'VT002', 
                'Ống nhựa PVC 20mm',
                'Vật tư',
                'Mét',
                'Ống nhựa chất lượng cao',
                'all'
            ],
            [
                'VT003',
                'Dây điện 2.5mm',
                'Điện',
                'Mét', 
                'Dây điện chất lượng cao',
                'all'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Mã vật tư (*)',
            'Tên vật tư (*)', 
            'Loại vật tư (*)',
            'Đơn vị (*)',
            'Ghi chú',
            'Kho tính tồn kho'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style for header row
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Style for data rows
        $sheet->getStyle('A2:F4')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Add instructions
        $sheet->setCellValue('A6', 'HƯỚNG DẪN NHẬP LIỆU:');
        $sheet->setCellValue('A7', '• (*) Các trường bắt buộc phải điền');
        $sheet->setCellValue('A8', '• Mã vật tư: Phải duy nhất, không được trùng');
        $sheet->setCellValue('A9', '• Tên vật tư: Tối đa 255 ký tự');
        $sheet->setCellValue('A10', '• Loại vật tư: Ví dụ: Linh kiện, Vật tư, Điện, Hóa chất...');
        $sheet->setCellValue('A11', '• Đơn vị: Ví dụ: Cái, Bộ, Chiếc, Mét, Cuộn, Kg...');
        $sheet->setCellValue('A12', '• Kho tính tồn kho: Để "all" để tính tất cả kho hoặc để trống');
        $sheet->setCellValue('A13', '• Xóa các dòng mẫu này trước khi import');

        // Style instructions
        $sheet->getStyle('A6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'CC0000']
            ]
        ]);

        $sheet->getStyle('A7:A13')->applyFromArray([
            'font' => [
                'color' => ['rgb' => '666666'],
                'size' => 10
            ]
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Mã vật tư
            'B' => 30,  // Tên vật tư
            'C' => 15,  // Loại vật tư
            'D' => 12,  // Đơn vị
            'E' => 25,  // Ghi chú
            'F' => 15,  // Kho tính tồn kho
        ];
    }

    public function title(): string
    {
        return 'Mẫu nhập vật tư';
    }
}
