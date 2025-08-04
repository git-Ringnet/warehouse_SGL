<?php

namespace App\Exports;

use App\Models\Good;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\Request;

class GoodsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(?Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Build the query with filters
        $query = Good::where('status', 'active')
            ->where('is_hidden', false);

        // Apply search filter
        if ($this->request && $this->request->has('search') && !empty($this->request->search)) {
            $searchTerm = $this->request->search;
            
            // Tìm kiếm theo text (luôn luôn thực hiện)
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('category', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('unit', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('notes', 'LIKE', "%{$searchTerm}%");
            });
            
            // Nếu search là số, thêm tìm kiếm theo tổng tồn kho
            if (is_numeric($searchTerm)) {
                $query->orWhereHas('warehouseMaterials', function($q) use ($searchTerm) {
                    $q->where('quantity', '<=' , (int)$searchTerm);
                });
            }
        }

        // Apply category filter
        if ($this->request && $this->request->has('category') && !empty($this->request->category)) {
            $query->where('category', $this->request->category);
        }

        // Apply unit filter
        if ($this->request && $this->request->has('unit') && !empty($this->request->unit)) {
            $query->where('unit', $this->request->unit);
        }

        // Get goods with supplier relationship
        $goods = $query->with('suppliers')->orderBy('id', 'desc')->get();

        // Filter by stock status (must be done after getting inventory quantities)
        if ($this->request && $this->request->has('stock')) {
            $filteredGoods = new Collection();

            foreach ($goods as $good) {
                $inventoryQuantity = $good->getInventoryQuantity();

                if ($this->request->stock === 'in_stock' && $inventoryQuantity > 0) {
                    $filteredGoods->push($good);
                } else if ($this->request->stock === 'out_of_stock' && $inventoryQuantity <= 0) {
                    $filteredGoods->push($good);
                }
            }

            return $filteredGoods;
        }

        return $goods;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'STT',
            'Mã hàng hóa',
            'Tên hàng hóa',
            'Loại',
            'Đơn vị',
            'Tổng tồn kho',
        ];
    }

    /**
     * @param mixed $good
     * @return array
     */
    public function map($good): array
    {
        // Get inventory quantity
        $inventoryQuantity = $good->getInventoryQuantity();

        // Row number will be handled using withColumnFormatting & AfterSheet events
        static $i = 0;
        $i++;

        return [
            $i, // STT
            $good->code,
            $good->name,
            $good->category,
            $good->unit,
            $inventoryQuantity,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Auto size columns
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Lấy tổng số dòng
        $totalRows = $sheet->getHighestRow();

        // Style tất cả cells với border
        $sheet->getStyle('A1:F' . $totalRows)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style cho các dòng chẵn
        for ($i = 2; $i <= $totalRows; $i += 2) {
            $sheet->getStyle('A' . $i . ':F' . $i)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F2F2F2'], // Light gray for even rows
                ],
            ]);
        }

        return [
            // Style the first row (headings)
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'] // Blue header like in the image
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ],
            // Style for all data rows
            'A2:F' . $totalRows => [
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
            // Center STT column and numeric columns
            'A2:A' . $totalRows => [
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ],
            'F2:F' . $totalRows => [
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ],
        ];
    }
}
