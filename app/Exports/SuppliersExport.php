<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SuppliersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;
    
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }
    
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Supplier::query();
        
        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            if (!empty($this->filters['filter'])) {
                switch ($this->filters['filter']) {
                    case 'name':
                        $query->where('name', 'like', "%{$search}%");
                        break;
                    case 'phone':
                        $query->where('phone', 'like', "%{$search}%");
                        break;
                    case 'email':
                        $query->where('email', 'like', "%{$search}%");
                        break;
                    case 'address':
                        $query->where('address', 'like', "%{$search}%");
                        break;
                }
            } else {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }
        }
        
        return $query->latest()->get();
    }
    
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'STT',
            'Tên nhà cung cấp',
            'Người đại diện',
            'Số điện thoại',
            'Email',
            'Địa chỉ',
            'Ghi chú',
            'Ngày tạo',
            'Cập nhật lần cuối'
        ];
    }
    
    /**
     * @param mixed $supplier
     * @return array
     */
    public function map($supplier): array
    {
        static $counter = 0;
        $counter++;
        
        return [
            $counter,
            $supplier->name,
            $supplier->representative ?? '',
            $supplier->phone,
            $supplier->email ?? '',
            $supplier->address ?? '',
            $supplier->notes ?? '',
            $supplier->created_at ? $supplier->created_at->format('d/m/Y H:i') : '',
            $supplier->updated_at ? $supplier->updated_at->format('d/m/Y H:i') : ''
        ];
    }
    
    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => '4F46E5']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            
            // All cells border and alignment
            "A1:{$highestColumn}{$highestRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ]
            ],
            
            // STT column center alignment
            "A:A" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ],
            
            // Date columns center alignment
            "H:I" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]
        ];
    }
} 