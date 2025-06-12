<?php

namespace App\Exports;

use App\Models\Product;
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

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        // Start with base query for active products that are not hidden
        $query = Product::where('status', 'active')
            ->where('is_hidden', false);

        // Apply filters if provided
        if (!empty($this->filters['search'])) {
            $searchTerm = $this->filters['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        if (!empty($this->filters['stock'])) {
            // We'll filter by stock after calculating quantities
        }

        $products = $query->get();
        
        // Calculate quantities for each product
        foreach ($products as $product) {
            // Get inventory quantity from warehouse_materials table
            $product->inventory_quantity = WarehouseMaterial::where('material_id', $product->id)
                ->where('item_type', 'product')
                ->sum('quantity');
        }

        // Apply stock filter after calculating quantities
        if (!empty($this->filters['stock'])) {
            if ($this->filters['stock'] === 'in_stock') {
                $products = $products->filter(function($product) {
                    return $product->inventory_quantity > 0;
                });
            } elseif ($this->filters['stock'] === 'out_of_stock') {
                $products = $products->filter(function($product) {
                    return $product->inventory_quantity == 0;
                });
            }
        }
        
        return $products;
    }
    
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'STT',
            'Mã thành phẩm',
            'Tên thành phẩm',
            'Mô tả',
            'Tổng tồn kho',
            'Ngày tạo',
            'Cập nhật lần cuối'
        ];
    }
    
    /**
     * @param mixed $product
     * @return array
     */
    public function map($product): array
    {
        static $counter = 0;
        $counter++;
        
        return [
            $counter,
            $product->code,
            $product->name,
            $product->description ?? '',
            number_format($product->inventory_quantity, 0, ',', '.'),
            $product->created_at ? $product->created_at->format('d/m/Y H:i') : '',
            $product->updated_at ? $product->updated_at->format('d/m/Y H:i') : ''
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
            "E:E" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ]
            ],
            
            // Date columns center alignment
            "F:G" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]
        ];
    }
} 