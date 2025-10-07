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
    public function getDeviceInfo(\Illuminate\Http\Request $request, $serial)
    {
        try {
            $productId = $request->get('product_id');
            $expectedCount = (int) ($request->get('expected_count') ?? 0);
            // Tìm device code theo serial chính
            $normalizedSerial = strtoupper(trim((string)$serial));
            $deviceCodeQuery = \App\Models\DeviceCode::whereRaw('UPPER(TRIM(serial_main)) = ?', [$normalizedSerial]);
            if ($productId) {
                $deviceCodeQuery->where('product_id', $productId);
            }
            $deviceCode = $deviceCodeQuery->first();
            
            // Nếu có device code, ưu tiên thông tin từ đó
            if ($deviceCode) {
                $debug = [
                    'matched' => 'device_codes',
                    'product_id' => $deviceCode->product_id,
                    'serial_main' => $deviceCode->serial_main,
                ];
                $components = $deviceCode->serial_components ?? [];
                if (is_string($components)) {
                    $decoded = json_decode($components, true);
                    if (is_array($decoded)) {
                        $components = $decoded;
                    } else {
                        $components = [];
                    }
                }
                $components = array_values(array_filter($components, fn($s) => isset($s) && trim((string)$s) !== ''));
                if ($expectedCount > 0) {
                    if (count($components) > $expectedCount) {
                        $components = array_slice($components, 0, $expectedCount);
                    } elseif (count($components) < $expectedCount) {
                        $components = array_pad($components, $expectedCount, '');
                    }
                }

                // Build components array aligned to product materials order if possible
                $materialsDetailsForZip = DB::table('product_materials')
                    ->select('material_id', 'quantity')
                    ->where('product_id', $productId ?: $deviceCode->product_id)
                    ->get();
                $expandedForZip = [];
                foreach ($materialsDetailsForZip as $md) {
                    $qty = max(1, (int)$md->quantity);
                    for ($i = 0; $i < $qty; $i++) {
                        $expandedForZip[] = (int)$md->material_id;
                    }
                }
                $componentsOut = [];
                for ($i = 0; $i < count($components); $i++) {
                    $componentsOut[] = [
                        'material_id' => $expandedForZip[$i] ?? null,
                        'serial' => $components[$i] ?? ''
                    ];
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'componentSerials' => $components,
                        'components' => $componentsOut,
                        'serialSim' => $deviceCode->serial_sim,
                        'accessCode' => $deviceCode->access_code,
                        'iotId' => $deviceCode->iot_id,
                        'mac4g' => $deviceCode->mac_4g,
                        'note' => $deviceCode->note
                    ],
                    'debug' => $debug
                ]);
            }
            
            // Nếu không tìm thấy device code, thử tìm từ assembly
            $assemblyProductQuery = DB::table('assembly_products')
                ->whereRaw('UPPER(TRIM(serials)) = ?', [$normalizedSerial]);
            if ($productId) {
                $assemblyProductQuery->where('product_id', $productId);
            }
            $assemblyProduct = $assemblyProductQuery->orderByDesc('id')->first();

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
                    ],
                    'debug' => [
                        'matched' => 'none'
                    ]
                ]);
            }

            // Lấy tất cả hàng assembly_materials kèm material_id để map đúng thứ tự vật tư
            $componentRows = DB::table('assembly_materials')
                ->select('material_id', 'serial', 'quantity')
                ->where('assembly_id', $assemblyProduct->assembly_id)
                ->where('target_product_id', $productId ?: $assemblyProduct->product_id)
                ->orderBy('id')
                ->get();

            // Nhóm serial theo material_id, giữ thứ tự phát sinh
            $serialsByMaterialId = [];
            $serialsLinearInAssemblyOrder = [];
            foreach ($componentRows as $row) {
                $raw = (string)($row->serial ?? '');
                $tokens = [];
                // Prefer JSON array if present
                $trimmed = ltrim($raw);
                if ($trimmed !== '' && $trimmed[0] === '[') {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $tokens = array_map(function ($v) { return trim((string)$v); }, $decoded);
                    }
                }
                // Fallback to splitting by common delimiters: comma, newline, semicolon, pipe
                if (empty($tokens)) {
                    $split = preg_split('/[\s]*[,;|\n\r]+[\s]*/u', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    $tokens = array_map(function ($v) { return trim((string)$v); }, $split);
                }
                $tokens = array_values(array_filter($tokens, function ($v) {
                    if ($v === '') return false;
                    $u = strtoupper($v);
                    return $u !== 'N/A' && $u !== 'NA' && $u !== 'NULL';
                }));

                if (empty($tokens)) {
                    continue;
                }

                // Preserve assembly row order first, then token order
                foreach ($tokens as $t) {
                    $serialsLinearInAssemblyOrder[] = $t;
                    $serialsByMaterialId[$row->material_id] = $serialsByMaterialId[$row->material_id] ?? [];
                    $serialsByMaterialId[$row->material_id][] = $t;
                }
            }

            // Lấy danh sách vật tư và số lượng của product để xác định expectedCount & thứ tự
            $materialsDetails = DB::table('product_materials')
                ->select('material_id', 'quantity')
                ->where('product_id', $productId ?: $assemblyProduct->product_id)
                ->get();

            $expandedMaterials = [];
            foreach ($materialsDetails as $md) {
                $qty = max(1, (int)$md->quantity);
                for ($i = 0; $i < $qty; $i++) {
                    $expandedMaterials[] = (int)$md->material_id;
                }
            }

            // Nếu client truyền expectedCount thì ưu tiên (đề phòng chi tiết cấu hình khác nhau)
            $targetCount = $expectedCount > 0 ? $expectedCount : count($expandedMaterials);

            // Trường hợp phổ biến: nếu số seri trong assembly đúng bằng số ô cần hiển thị, ưu tiên dùng thứ tự tuyến tính theo assembly
            if ($targetCount > 0 && count($serialsLinearInAssemblyOrder) === $targetCount) {
                $componentSerials = array_values($serialsLinearInAssemblyOrder);
                // Không cho seri vật tư trùng seri chính
                for ($i = 0; $i < count($componentSerials); $i++) {
                    if (strtoupper(trim((string)$componentSerials[$i])) === $normalizedSerial) {
                        $componentSerials[$i] = '';
                    }
                }
            } else {
                // Map serials theo đúng thứ tự từng vật tư và index
            $componentSerials = [];
            $materialUsageIndex = [];
            for ($i = 0; $i < $targetCount; $i++) {
                $materialId = $expandedMaterials[$i] ?? null;
                if ($materialId === null) {
                    $componentSerials[] = '';
                    continue;
                }
                $materialUsageIndex[$materialId] = ($materialUsageIndex[$materialId] ?? 0);
                $pool = $serialsByMaterialId[$materialId] ?? [];
                $serialVal = $pool[$materialUsageIndex[$materialId]] ?? '';
                // Never allow component serial to equal the main device serial
                if (trim((string)$serialVal) !== '' && strtoupper(trim((string)$serialVal)) === $normalizedSerial) {
                    $serialVal = '';
                }
                $componentSerials[] = $serialVal;
                $materialUsageIndex[$materialId]++;
            }
            }

            // Build components array with material_id pairing in order
            $componentsOut = [];
            for ($i = 0; $i < $targetCount; $i++) {
                $componentsOut[] = [
                    'material_id' => $expandedMaterials[$i] ?? null,
                    'serial' => $componentSerials[$i] ?? ''
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'componentSerials' => $componentSerials,
                    'components' => $componentsOut,
                    'serialSim' => null,
                    'accessCode' => null,
                    'iotId' => null,
                    'mac4g' => null,
                    'note' => null
                ],
                'debug' => [
                    'matched' => 'assembly',
                    'assembly_id' => $assemblyProduct->assembly_id,
                    'product_id' => $productId ?: $assemblyProduct->product_id,
                    'count' => count($componentSerials)
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