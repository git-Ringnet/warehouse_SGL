<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Download device codes Excel template
     */
    public function downloadDeviceCodesTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['Seri chính', 'Seri vật tư', 'Seri SIM', 'Mã truy cập', 'ID IoT', 'Mac 4G', 'Chú thích'];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF']
            ]
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Auto size columns
        foreach(range('A','G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create Excel file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'device_codes_template.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Import device codes from Excel
     */
    public function importDeviceCodes(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy file Excel']);
            }

            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header row
            array_shift($rows);

            $deviceCodes = [];
            foreach ($rows as $row) {
                if (!empty(array_filter($row))) { // Skip empty rows
                    $deviceCodes[] = [
                        'main_serial' => $row[0] ?? '',
                        'component_serials' => $row[1] ?? '',
                        'sim_serial' => $row[2] ?? '',
                        'access_code' => $row[3] ?? '',
                        'iot_id' => $row[4] ?? '',
                        'mac_4g' => $row[5] ?? '',
                        'note' => $row[6] ?? ''
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Import thành công',
                'data' => $deviceCodes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi import: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get device information by serial number
     */
    public function getDeviceInfo($serial)
    {
        try {
            // Tìm device code theo serial chính
            $deviceCode = \App\Models\DeviceCode::where('serial_main', $serial)->first();
            
            // Nếu có device code, ưu tiên thông tin từ đó
            if ($deviceCode) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'componentSerials' => $deviceCode->serial_components ?? [],
                        'serialSim' => $deviceCode->serial_sim,
                        'accessCode' => $deviceCode->access_code,
                        'iotId' => $deviceCode->iot_id,
                        'mac4g' => $deviceCode->mac_4g,
                        'note' => $deviceCode->note
                    ]
                ]);
            }
            
            // Nếu không tìm thấy device code, thử tìm từ assembly
            $assemblyProduct = DB::table('assembly_products')
                ->where('serials', $serial)
                ->first();

            if (!$assemblyProduct) {
                // Không tìm thấy, trả về kết quả trống
                return response()->json([
                    'success' => true,
                    'data' => [
                        'componentSerials' => [],
                        'serialSim' => null,
                        'accessCode' => null,
                        'iotId' => null,
                        'mac4g' => null,
                        'note' => null
                    ]
                ]);
            }

            // Lấy tất cả serial vật tư từ assembly_materials
            $componentSerials = DB::table('assembly_materials')
                ->where('assembly_id', $assemblyProduct->assembly_id)
                ->where('target_product_id', $assemblyProduct->product_id)
                ->pluck('serial')
                ->filter()
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'componentSerials' => $componentSerials,
                    'serialSim' => null,
                    'accessCode' => null,
                    'iotId' => null,
                    'mac4g' => null,
                    'note' => null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
} 