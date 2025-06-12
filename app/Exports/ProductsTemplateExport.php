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

class ProductsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function array(): array
    {
        return [
            [
                'TP001',
                'Máy bơm nước ly tâm',
                'Máy bơm nước ly tâm công suất 1HP, áp lực cao'
            ],
            [
                'TP002', 
                'Động cơ điện 3 pha',
                'Động cơ điện 3 pha 5HP, 380V, tốc độ 1450 vòng/phút'
            ],
            [
                'TP003',
                'Hệ thống điều khiển tự động',
                'Hệ thống điều khiển tự động với PLC và màn hình HMI'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Mã thành phẩm (*)',
            'Tên thành phẩm (*)', 
            'Mô tả'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style for header row
        $sheet->getStyle('A1:C1')->applyFromArray([
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
        $sheet->getStyle('A2:C4')->applyFromArray([
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
        $sheet->setCellValue('A8', '• Mã thành phẩm: Có thể trùng lặp');
        $sheet->setCellValue('A9', '• Tên thành phẩm: Tối đa 255 ký tự');
        $sheet->setCellValue('A10', '• Mô tả: Mô tả chi tiết về thành phẩm (không bắt buộc)');
        $sheet->setCellValue('A11', '• Xóa các dòng mẫu này trước khi import');

        // Style instructions
        $sheet->getStyle('A6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'CC0000']
            ]
        ]);

        $sheet->getStyle('A7:A11')->applyFromArray([
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
            'A' => 20,  // Mã thành phẩm
            'B' => 35,  // Tên thành phẩm
            'C' => 50,  // Mô tả
        ];
    }

    public function title(): string
    {
        return 'Mẫu nhập thành phẩm';
    }
} 