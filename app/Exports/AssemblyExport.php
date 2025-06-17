<?php

namespace App\Exports;

use App\Models\Assembly;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Contracts\View\View;

class AssemblyExport implements FromView, WithStyles, ShouldAutoSize
{
    protected $assembly;
    
    public function __construct(Assembly $assembly)
    {
        $this->assembly = $assembly;
    }
    
    /**
     * @return View
     */
    public function view(): View
    {
        // Load relationships if not already loaded
        $this->assembly->load([
            'products.product',
            'materials.material', 
            'warehouse',
            'targetWarehouse',
            'assignedEmployee',
            'tester',
            'project'
        ]);
        
        return view('assemble.excel', [
            'assembly' => $this->assembly
        ]);
    }
    
    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Get the highest row and column
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        return [
            // Header styling
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['argb' => '000000'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            
            // Sub headers
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['argb' => '000000'],
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
            
            // Table headers background
            'A6:Z6' => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => 'E6F3FF']
                ],
                'font' => [
                    'bold' => true,
                ]
            ],
        ];
    }
} 