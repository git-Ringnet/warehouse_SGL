<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = Employee::query();

        // Apply filters if provided
        if (!empty($this->filters['search'])) {
            $searchTerm = $this->filters['search'];
            
            if (!empty($this->filters['filter'])) {
                switch ($this->filters['filter']) {
                    case 'username':
                        $query->where('username', 'like', "%{$searchTerm}%");
                        break;
                    case 'name':
                        $query->where('name', 'like', "%{$searchTerm}%");
                        break;
                    case 'phone':
                        $query->where('phone', 'like', "%{$searchTerm}%");
                        break;
                    case 'email':
                        $query->where('email', 'like', "%{$searchTerm}%");
                        break;
                    case 'department':
                        $query->where('department', 'like', "%{$searchTerm}%");
                        break;
                    case 'role':
                        $query->whereHas('roleGroup', function($q) use ($searchTerm) {
                            $q->where('name', 'like', "%{$searchTerm}%");
                        });
                        break;
                    case 'status':
                        if (strtolower($searchTerm) === 'active' || strtolower($searchTerm) === 'hoạt động') {
                            $query->where('is_active', true);
                        } else {
                            $query->where('is_active', false);
                        }
                        break;
                }
            } else {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('username', 'like', "%{$searchTerm}%")
                      ->orWhere('name', 'like', "%{$searchTerm}%")
                      ->orWhere('phone', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('department', 'like', "%{$searchTerm}%")
                      ->orWhereHas('roleGroup', function($q) use ($searchTerm) {
                          $q->where('name', 'like', "%{$searchTerm}%");
                      });
                });
            }
        }
        
        return $query->with('roleGroup')->get();
    }
    
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'STT',
            'Username',
            'Họ và tên',
            'Email',
            'Số điện thoại',
            'Vai trò',
            'Phòng ban',
            'Trạng thái',
            'Ngày tạo'
        ];
    }
    
    /**
     * @param mixed $employee
     * @return array
     */
    public function map($employee): array
    {
        static $counter = 0;
        $counter++;
        
        return [
            $counter,
            $employee->username,
            $employee->name,
            $employee->email ?? 'N/A',
            $employee->phone ?? 'N/A',
            $employee->roleGroup ? $employee->roleGroup->name : 'Chưa phân quyền',
            $employee->department ?? 'Chưa phân công',
            $employee->is_active ? 'Đang hoạt động' : 'Đã khóa',
            $employee->created_at ? $employee->created_at->format('d/m/Y') : 'N/A'
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
            
            // Status column center alignment
            "H:H" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ],
            
            // Date columns center alignment
            "I:J" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ]
            ]
        ];
    }
} 