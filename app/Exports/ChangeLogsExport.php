<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\ChangeLog;

class ChangeLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @var \Illuminate\Support\Collection|ChangeLog[]
     */
    protected Collection $logs;

    public function __construct(Collection $logs)
    {
        $this->logs = $logs;
    }

    public function collection(): Collection
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return [
            'STT',
            'Thời gian',
            'Mã vật tư/Thành phẩm/Hàng hóa',
            'Tên vật tư/Thành phẩm/Hàng hóa',
            'Loại hình',
            'Mã phiếu',
            'Số lượng',
            'Mô tả',
            'Người thực hiện',
            'Ghi chú'
        ];
    }

    public function map($log): array
    {
        static $stt = 0;
        $stt++;

        return [
            $stt,
            optional($log->time_changed)->format('d/m/Y H:i:s'),
            $log->item_code,
            $log->item_name,
            $log->getChangeTypeLabel(),
            $log->document_code,
            $log->quantity,
            $log->description,
            $log->performed_by,
            $log->notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header and center align
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        return [];
    }
}
