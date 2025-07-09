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
        // Start with base query for active materials that are not hidden
        $query = Material::where('status', 'active')
            ->where('is_hidden', 0);

        // Apply filters if provided
        if (!empty($this->filters['search'])) {
            $searchTerm = $this->filters['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('category', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('unit', 'LIKE', "%{$searchTerm}%");
            });
        }

        if (!empty($this->filters['category'])) {
            $query->where('category', $this->filters['category']);
        }

        if (!empty($this->filters['unit'])) {
            $query->where('unit', $this->filters['unit']);
        }

        $materials = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate quantities for each material
        foreach ($materials as $material) {
            // Total quantity across all locations
            $material->total_quantity = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material')
                ->sum('quantity');
            
            // Total quantity only in warehouses based on inventory_warehouses setting
            $warehouseQuery = WarehouseMaterial::where('material_id', $material->id)
                ->where('item_type', 'material');
                
            if (is_array($material->inventory_warehouses) && !in_array('all', $material->inventory_warehouses) && !empty($material->inventory_warehouses)) {
                $warehouseQuery->whereIn('warehouse_id', $material->inventory_warehouses);
            }
            
            $material->inventory_quantity = $warehouseQuery->sum('quantity');
        }

        // Apply stock filter after calculating quantities
        if (!empty($this->filters['stock'])) {
            if ($this->filters['stock'] === 'in_stock') {
                $materials = $materials->filter(function($material) {
                    return $material->inventory_quantity > 0;
                });
            } elseif ($this->filters['stock'] === 'out_of_stock') {
                $materials = $materials->filter(function($material) {
                    return $material->inventory_quantity == 0;
                });
            }
        }
        
        return $materials;
    }
    
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'STT',
            'Mã vật tư',
            'Tên vật tư',
            'Loại',
            'Đơn vị',
            'Tổng tồn kho',
        ];
    }
    
    /**
     * @param mixed $material
     * @return array
     */
    public function map($material): array
    {
        static $counter = 0;
        $counter++;
        
        return [
            $counter,
            $material->code,
            $material->name,
            $material->category,
            $material->unit,
            number_format($material->inventory_quantity, 0, ',', '.')
        ];
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
            
            // Quantity column right alignment
            "G:G" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
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