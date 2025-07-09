<?php

namespace App\Exports;

use App\Models\Material;
use App\Models\WarehouseMaterial;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MaterialsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $materials;

    public function __construct($materials)
    {
        $this->materials = $materials;
    }

    public function collection()
    {
        return $this->materials;
    }

    public function headings(): array
    {
        return [
            'STT',
            'Mã vật tư',
            'Tên vật tư',
            'Loại',
            'Đơn vị',
            'Tổng tồn kho'
        ];
    }

    public function map($material): array
    {
        static $stt = 0;
        $stt++;
        
        return [
            $stt,
            $material->code,
            $material->name,
            $material->category,
            $material->unit,
            (int)$material->inventory_quantity // Đảm bảo là số nguyên
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style cho header
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E4E6EF',
                ]
            ]
        ]);

        // Căn giữa header
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Lấy số hàng có dữ liệu
        $lastRow = $sheet->getHighestRow();

        // Căn trái cho các cột text (Mã vật tư, Tên vật tư, Loại, Đơn vị)
        $sheet->getStyle('B2:E'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Căn phải cho các cột số (STT và Tổng tồn kho)
        $sheet->getStyle('A2:A'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F2:F'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Thêm border cho toàn bộ bảng
        $sheet->getStyle('A1:F'.$lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Set column width
        $sheet->getColumnDimension('A')->setWidth(10); // STT
        $sheet->getColumnDimension('B')->setWidth(20); // Mã vật tư
        $sheet->getColumnDimension('C')->setWidth(40); // Tên vật tư
        $sheet->getColumnDimension('D')->setWidth(20); // Loại
        $sheet->getColumnDimension('E')->setWidth(15); // Đơn vị
        $sheet->getColumnDimension('F')->setWidth(15); // Tổng tồn kho

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
} 