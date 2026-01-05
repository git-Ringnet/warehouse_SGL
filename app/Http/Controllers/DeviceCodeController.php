<?php

namespace App\Http\Controllers;

use App\Models\DeviceCode;
use App\Models\DispatchItem;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceCodeController extends Controller
{
    public function downloadTemplate(Request $request)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Nếu có dữ liệu từ request, gom vật tư theo tên UNIQUE
            $modalData = $request->input('modal_data', []);
            $uniqueMaterials = [];

            if (!empty($modalData) && is_array($modalData)) {
                foreach ($modalData as $rowData) {
                    $materials = $rowData['materials'] ?? [];

                    foreach ($materials as $material) {
                        if (!empty($material['name'])) {
                            $baseName = trim($material['name']);
                            if (!in_array($baseName, $uniqueMaterials)) {
                                $uniqueMaterials[] = $baseName;
                            }
                        }
                    }
                }
            }

            // Sắp xếp uniqueMaterials: ưu tiên theo PRODUCT_CODE (trong []) trước, sau đó theo MATERIAL_CODE
            // Format tên: [PRODUCT_CODE] MATERIAL_CODE - MATERIAL_NAME (index)
            // Đảm bảo tất cả vật tư của cùng một thành phẩm xuất hiện liền nhau
            usort($uniqueMaterials, function ($a, $b) {
                // Trích xuất product_code từ tên (phần trong dấu [])
                $extractProductCode = function ($name) {
                    if (preg_match('/^\[([^\]]+)\]/', $name, $matches)) {
                        return strtolower(trim($matches[1]));
                    }
                    return '';
                };

                // Trích xuất material_code từ tên (phần sau dấu ] và trước dấu -)
                $extractMaterialCode = function ($name) {
                    // Thử trích xuất theo format: [PRODUCT_CODE] MATERIAL_CODE - NAME (index)
                    if (preg_match('/\]\s*([A-Z0-9_-]+)\s*-/i', $name, $matches)) {
                        return strtolower(trim($matches[1]));
                    }
                    // Fallback: lấy phần đầu trước dấu -
                    if (preg_match('/^([A-Z0-9_-]+)\s*-/i', $name, $matches)) {
                        return strtolower(trim($matches[1]));
                    }
                    return strtolower(trim($name));
                };

                // 1. Ưu tiên sắp xếp theo product_code trước
                $productCodeA = $extractProductCode($a);
                $productCodeB = $extractProductCode($b);
                $productCompare = strcmp($productCodeA, $productCodeB);
                if ($productCompare !== 0) {
                    return $productCompare;
                }

                // 2. Trong cùng product, sắp xếp theo material_code
                $materialCodeA = $extractMaterialCode($a);
                $materialCodeB = $extractMaterialCode($b);
                $materialCompare = strcmp($materialCodeA, $materialCodeB);
                if ($materialCompare !== 0) {
                    return $materialCompare;
                }

                // 3. Nếu cùng material_code, sắp xếp theo tên gốc để giữ thứ tự index
                return strcmp($a, $b);
            });

            // Mặc định tối thiểu 3 cột vật tư nếu không có dữ liệu
            $numMaterialCols = max(count($uniqueMaterials), 3);

            // Tạo headers theo format ngang
            $headers = ['Mã - Tên thiết bị', 'Serial chính'];
            if (!empty($uniqueMaterials)) {
                foreach ($uniqueMaterials as $materialName) {
                    $headers[] = $materialName;
                }
            } else {
                for ($i = 1; $i <= $numMaterialCols; $i++) {
                    $headers[] = 'Vật tư ' . $i;
                }
            }
            $headers[] = 'Seri SIM';
            $headers[] = 'Mã truy cập';
            $headers[] = 'ID IoT';
            $headers[] = 'Mac 4G';
            $headers[] = 'Chú thích';

            // --- ADD INSTRUCTIONS ---
            $sheet->mergeCells('A1:E1');
            $sheet->setCellValue('A1', 'HƯỚNG DẪN NHẬP LIỆU (Vui lòng đọc kỹ)');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FFFF0000'); // Red

            $sheet->mergeCells('A2:J2');
            $sheet->setCellValue('A2', '1. Dòng tiêu đề bên dưới (bắt đầu bằng "Mã - Tên thiết bị") là CỐ ĐỊNH.');

            $sheet->mergeCells('A3:J3');
            $sheet->setCellValue('A3', '2. "Serial chính" là bắt buộc. Các cột [MÃ]... là serial vật tư đi kèm.');

            $sheet->mergeCells('A4:J4');
            $sheet->setCellValue('A4', '3. QUAN TRỌNG: Điền serial đúng DÒNG của thành phẩm tương ứng (cột "Mã - Tên thiết bị" chỉ rõ thành phẩm nào).');
            $sheet->getStyle('A4')->getFont()->setBold(true);

            $sheet->mergeCells('A5:J5');
            $sheet->setCellValue('A5', '4. Bạn KHÔNG CẦN xóa các phần hướng dẫn này trước khi Import.');

            $sheet->getStyle('A2:A5')->getFont()->setItalic(true)->getColor()->setARGB('FF555555');

            // --- HEADERS (Start at Row 7) ---
            $headerRow = 7;
            $sheet->fromArray([$headers], NULL, 'A' . $headerRow);

            // Tính toán cột cuối cùng
            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));

            // Style headers
            $headerRange = 'A' . $headerRow . ':' . $lastColumn . $headerRow;
            $headerStyle = $sheet->getStyle($headerRange);
            $headerStyle->getFont()->setBold(true);
            $headerStyle->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFEEEEEE');
            $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Freeze header row
            $sheet->freezePane('A' . ($headerRow + 1));

            // AutoSize columns
            for ($i = 1; $i <= count($headers); $i++) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Start data from next row
            $row = $headerRow + 1;

            if (!empty($modalData) && is_array($modalData)) {
                $groupedRows = [];

                foreach ($modalData as $rowData) {
                    $key = $rowData['product_id'] ?? $rowData['product_name'] ?? uniqid('product_', true);
                    if (!isset($groupedRows[$key])) {
                        $groupedRows[$key] = [
                            'product_name' => $rowData['product_name'] ?? '',
                            'rows' => []
                        ];
                    }
                    $groupedRows[$key]['rows'][] = $rowData;
                }

                foreach ($groupedRows as &$group) {
                    usort($group['rows'], function ($a, $b) {
                        return (int) ($a['product_row_index'] ?? 0) <=> (int) ($b['product_row_index'] ?? 0);
                    });
                }
                unset($group);

                foreach ($groupedRows as $group) {
                    $productName = $group['product_name'];
                    $startRow = $row;

                    foreach ($group['rows'] as $rowData) {
                        $serialMain = $rowData['serial_main'] ?? '';
                        $serialMainPlaceholder = $rowData['serial_main_placeholder'] ?? '';
                        $serialSim = $rowData['serial_sim'] ?? '';
                        $accessCode = $rowData['access_code'] ?? '';
                        $iotId = $rowData['iot_id'] ?? '';
                        $mac4g = $rowData['mac_4g'] ?? '';
                        $note = $rowData['note'] ?? '';
                        $materials = $rowData['materials'] ?? [];

                        $effectiveSerialMain = $serialMain !== '' ? $serialMain : $serialMainPlaceholder;

                        // Gom serial theo tên vật tư UNIQUE
                        $materialSerialsByName = [];
                        foreach ($materials as $material) {
                            if (!empty($material['name'])) {
                                $baseName = trim($material['name']);

                                $serial = $material['serial'] ?? '';
                                if (!isset($materialSerialsByName[$baseName])) {
                                    $materialSerialsByName[$baseName] = [];
                                }
                                $materialSerialsByName[$baseName][] = $serial;
                            }
                        }

                        // Tạo dòng dữ liệu
                        $rowDataArray = [
                            '', // Mã - Tên (sẽ merge sau)
                            $effectiveSerialMain
                        ];

                        // Thêm serial vật tư theo thứ tự cột unique (gộp bằng dấu phẩy nếu có nhiều)
                        foreach ($uniqueMaterials as $baseName) {
                            $serials = $materialSerialsByName[$baseName] ?? [];
                            // Gộp các serial cùng tên vật tư bằng dấu phẩy
                            $rowDataArray[] = implode(', ', array_filter($serials));
                        }

                        // Nếu không có uniqueMaterials, thêm cột trống
                        if (empty($uniqueMaterials)) {
                            for ($i = 0; $i < $numMaterialCols; $i++) {
                                $rowDataArray[] = '';
                            }
                        }

                        // Thêm các cột còn lại
                        $rowDataArray[] = $serialSim;
                        $rowDataArray[] = $accessCode;
                        $rowDataArray[] = $iotId;
                        $rowDataArray[] = $mac4g;
                        $rowDataArray[] = $note;

                        $sheet->fromArray([$rowDataArray], NULL, 'A' . $row);

                        // Set serial chính là text để tránh bị format số
                        if ($effectiveSerialMain !== '') {
                            $sheet->getCell('B' . $row)->setValueExplicit($effectiveSerialMain, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }

                        // Set các cột vật tư là text
                        $colOffset = 3; // Bắt đầu từ cột C
                        foreach ($uniqueMaterials as $idx => $baseName) {
                            $colIndex = $colOffset + $idx;
                            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                            $serials = $materialSerialsByName[$baseName] ?? [];
                            $serialValue = implode(', ', array_filter($serials));
                            if ($serialValue !== '') {
                                $sheet->getCell($col . $row)->setValueExplicit($serialValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            }
                        }

                        $row++;
                    }

                    $endRow = $row - 1;
                    if ($startRow <= $endRow) {
                        $sheet->setCellValue('A' . $startRow, $productName);
                        if ($endRow > $startRow) {
                            $sheet->mergeCells('A' . $startRow . ':A' . $endRow);
                        }
                        $sheet->getStyle('A' . $startRow . ':A' . $endRow)
                            ->getAlignment()
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    }
                }
            }

            // Create the file
            $writer = new Xlsx($spreadsheet);
            $filename = 'template_device_codes.xlsx';

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating device codes template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo file template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy file']);
            }

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Tìm header row (Scan first 20 rows)
            $headerRow = [];
            $headerRowIndex = 0;
            $foundHeader = false;

            foreach ($rows as $index => $row) {
                if ($index > 20)
                    break;
                foreach ($row as $cell) {
                    $cellVal = trim(mb_strtolower((string) $cell, 'UTF-8'));
                    if ($cellVal === 'serial chính' || $cellVal === 'serial main' || strpos($cellVal, 'serial ch') === 0) {
                        $headerRow = $row;
                        $headerRowIndex = $index;
                        $foundHeader = true;
                        break 2;
                    }
                }
            }

            if (!$foundHeader) {
                $headerRow = !empty($rows) ? $rows[0] : [];
                $headerRowIndex = 0;
            }

            Log::info('Import: Header found at index ' . $headerRowIndex);

            // Tìm các cột trong header
            $serialMainCol = -1;
            $serialSimCol = -1;
            $accessCodeCol = -1;
            $iotIdCol = -1;
            $mac4gCol = -1;
            $noteCol = -1;

            // Các từ khóa để xác định cột cuối (không phải cột vật tư)
            $excludeKeywords = ['seri sim', 'serial sim', 'sim', 'mã truy cập', 'truy cập', 'access', 'id iot', 'iot', 'mac 4g', 'mac4g', 'chú thích', 'ghi chú', 'note'];

            foreach ($headerRow as $colIndex => $header) {
                $headerStr = trim(strtolower((string) $header));

                // Strict matching for known system columns
                // Expected headers: Serial chính, Seri SIM, Mã truy cập, ID IOT, MAC 4G, Chú thích
                if ($headerStr === 'serial chính' || $headerStr === 'serial main' || strpos($headerStr, 'serial ch') === 0) {
                    $serialMainCol = $colIndex;
                } else if ($headerStr === 'seri sim' || $headerStr === 'serial sim' || $headerStr === 'sim') {
                    $serialSimCol = $colIndex;
                } else if ($headerStr === 'mã truy cập' || $headerStr === 'access code' || $headerStr === 'access_code') {
                    $accessCodeCol = $colIndex;
                } else if ($headerStr === 'id iot' || $headerStr === 'iot id' || $headerStr === 'iot_id') {
                    $iotIdCol = $colIndex;
                } else if ($headerStr === 'mac 4g' || $headerStr === 'mac4g' || $headerStr === 'mac_4g') {
                    $mac4gCol = $colIndex;
                } else if ($headerStr === 'chú thích' || $headerStr === 'ghi chú' || $headerStr === 'note' || $headerStr === 'notes') {
                    $noteCol = $colIndex;
                }
            }

            if ($serialMainCol < 0) {
                $serialMainCol = 1;
            }

            // Xác định các cột vật tư = các cột sau Serial chính và trước các cột cuối
            $vtColumns = [];
            $endColumns = array_filter([$serialSimCol, $accessCodeCol, $iotIdCol, $mac4gCol, $noteCol], function ($v) {
                return $v >= 0;
            });
            $minEndCol = !empty($endColumns) ? min($endColumns) : count($headerRow);

            // Xác định các cột vật tư
            for ($col = $serialMainCol + 1; $col < count($headerRow); $col++) {
                // Stop if we hit a known system column
                if ($col === $serialSimCol || $col === $accessCodeCol || $col === $iotIdCol || $col === $mac4gCol || $col === $noteCol) {
                    break;
                }

                $headerOriginal = trim((string) ($headerRow[$col] ?? ''));
                $headerStr = mb_strtolower($headerOriginal, 'UTF-8');

                $isExcluded = false;
                foreach ($excludeKeywords as $keyword) {
                    if ($headerStr === $keyword) {
                        $isExcluded = true;
                        break;
                    }
                }

                if (!$isExcluded && $headerStr !== '' && $headerStr !== 'mã - tên thiết bị') {
                    $vtColumns[] = $col;
                }
            }

            Log::info('Import: VT Columns detected: ' . count($vtColumns));

            $grouped = [];
            $lastSerialMain = null;

            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex <= $headerRowIndex)
                    continue; // Skip header and pre-header rows
                $serialMain = isset($row[$serialMainCol]) ? trim((string) $row[$serialMainCol]) : '';

                if ($serialMain === '' && $lastSerialMain !== null) {
                    $serialMain = $lastSerialMain;
                }

                if ($serialMain === '') {
                    continue;
                }

                if ($serialMain !== $lastSerialMain) {
                    $lastSerialMain = $serialMain;
                }

                if (!isset($grouped[$serialMain])) {
                    $grouped[$serialMain] = [
                        'serial_main' => $serialMain,
                        'serial_components' => [],
                        'serial_components_map' => [], // Object với key là tên vật tư
                        'serial_sim' => '',
                        'access_code' => '',
                        'iot_id' => '',
                        'mac_4g' => '',
                        'note' => '',
                    ];
                }

                // Lấy serial vật tư từ các cột VT
                foreach ($vtColumns as $vtCol) {
                    $vtValue = isset($row[$vtCol]) ? trim((string) $row[$vtCol]) : '';
                    $headerName = isset($headerRow[$vtCol]) ? trim((string) $headerRow[$vtCol]) : '';

                    // Khởi tạo mảng cho header này nếu chưa có
                    if ($headerName !== '' && !isset($grouped[$serialMain]['serial_components_map'][$headerName])) {
                        $grouped[$serialMain]['serial_components_map'][$headerName] = [];
                    }

                    // Thêm serial vào map
                    if ($headerName !== '') {
                        // Nếu có nhiều serial trong 1 ô (cách nhau bởi dấu phẩy), tách ra
                        if (strpos($vtValue, ',') !== false) {
                            $parts = array_map('trim', explode(',', $vtValue));
                            foreach ($parts as $part) {
                                $grouped[$serialMain]['serial_components_map'][$headerName][] = $part;
                                $grouped[$serialMain]['serial_components'][] = $part;
                            }
                        } else {
                            // For 'serial_components_map': only add non-empty values
                            if ($vtValue !== '') {
                                $grouped[$serialMain]['serial_components_map'][$headerName][] = $vtValue;
                            }
                            // ALWAYS push to serial_components to maintain column alignment
                            $grouped[$serialMain]['serial_components'][] = $vtValue;
                        }
                    }
                }

                // Lấy các cột còn lại
                $serialSim = $serialSimCol >= 0 && isset($row[$serialSimCol]) ? trim((string) $row[$serialSimCol]) : '';
                $accessCode = $accessCodeCol >= 0 && isset($row[$accessCodeCol]) ? trim((string) $row[$accessCodeCol]) : '';
                $iotId = $iotIdCol >= 0 && isset($row[$iotIdCol]) ? trim((string) $row[$iotIdCol]) : '';
                $mac4g = $mac4gCol >= 0 && isset($row[$mac4gCol]) ? trim((string) $row[$mac4gCol]) : '';
                $note = $noteCol >= 0 && isset($row[$noteCol]) ? trim((string) $row[$noteCol]) : '';

                if ($serialSim !== '' && $grouped[$serialMain]['serial_sim'] === '') {
                    $grouped[$serialMain]['serial_sim'] = $serialSim;
                }
                if ($accessCode !== '' && $grouped[$serialMain]['access_code'] === '') {
                    $grouped[$serialMain]['access_code'] = $accessCode;
                }
                if ($iotId !== '' && $grouped[$serialMain]['iot_id'] === '') {
                    $grouped[$serialMain]['iot_id'] = $iotId;
                }
                if ($mac4g !== '' && $grouped[$serialMain]['mac_4g'] === '') {
                    $grouped[$serialMain]['mac_4g'] = $mac4g;
                }
                if ($note !== '' && $grouped[$serialMain]['note'] === '') {
                    $grouped[$serialMain]['note'] = $note;
                }
            }

            // Chuyển grouped thành mảng data
            $data = array_values(array_map(function ($item) {
                $item['serial_components'] = json_encode($item['serial_components']);
                return $item;
            }, $grouped));

            Log::info('Device code import data', [
                'count' => count($data),
                'vtColumns' => $vtColumns,
                'sample' => array_slice($data, 0, 2)
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'deviceCodes' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi import file: ' . $e->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'dispatch_id' => 'nullable|exists:dispatches,id',
                'product_id' => 'required',
                'item_type' => 'required|in:material,good,product',
                'item_id' => 'required|integer',
                'serial_main' => 'required',
                'serial_components' => 'nullable|array',
                'serial_sim' => 'nullable',
                'access_code' => 'nullable',
                'iot_id' => 'nullable',
                'mac_4g' => 'nullable',
                'note' => 'nullable'
            ]);

            $deviceCode = DeviceCode::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Lưu thông tin thành công',
                'data' => $deviceCode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu thông tin: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $deviceCode = DeviceCode::findOrFail($id);

            $validatedData = $request->validate([
                'serial_main' => 'required',
                'serial_components' => 'nullable|array',
                'serial_sim' => 'nullable',
                'access_code' => 'nullable',
                'iot_id' => 'nullable',
                'mac_4g' => 'nullable',
                'note' => 'nullable'
            ]);

            $deviceCode->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công',
                'data' => $deviceCode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông tin: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Lấy danh sách device codes theo dispatch_id
     */
    public function getByDispatch(Request $request, $dispatch_id)
    {
        try {
            $type = $request->input('type', 'contract');

            // Get device codes from device_codes table
            $deviceCodes = DB::table('device_codes')
                ->where('dispatch_id', $dispatch_id)
                ->where('type', $type)
                ->select('id', 'item_id', 'serial_main', 'old_serial', 'product_id', 'item_type', 'serial_components', 'serial_sim', 'access_code', 'iot_id', 'mac_4g', 'note')
                ->get();

            // Nếu không có device codes, lấy dữ liệu từ dispatch_items và assembly để tạo device codes mặc định
            if ($deviceCodes->isEmpty()) {
                $dispatchItems = DB::table('dispatch_items')
                    ->where('dispatch_id', $dispatch_id)
                    ->where('category', $type)
                    ->get();

                $deviceCodes = $dispatchItems->map(function ($item) {
                    // Parse serial_numbers từ JSON string
                    $serialNumbers = [];
                    if ($item->serial_numbers) {
                        try {
                            $serialNumbers = json_decode($item->serial_numbers, true) ?: [];
                        } catch (\Exception $e) {
                            $serialNumbers = [];
                        }
                    }

                    // Lấy serial components từ assembly nếu có
                    $serialComponents = [];
                    if ($item->item_type === 'product') {
                        // Parse assembly_id và product_unit từ dispatch_item
                        $assemblyIds = is_string($item->assembly_id) ? explode(',', $item->assembly_id) : ($item->assembly_id ? [$item->assembly_id] : []);
                        $productUnits = [];
                        if ($item->product_unit) {
                            if (is_string($item->product_unit)) {
                                $decoded = json_decode($item->product_unit, true);
                                $productUnits = is_array($decoded) ? $decoded : [];
                            } else {
                                $productUnits = is_array($item->product_unit) ? $item->product_unit : [];
                            }
                        }

                        // Lấy materials theo từng serial, mỗi serial có assembly_id và product_unit riêng
                        for ($idx = 0; $idx < max(count($serialNumbers), 1); $idx++) {
                            $serial = $serialNumbers[$idx] ?? '';
                            $assemblyId = $assemblyIds[$idx] ?? ($assemblyIds[0] ?? null);
                            $productUnit = isset($productUnits[$idx]) ? $productUnits[$idx] : ($productUnits[0] ?? null);

                            // Chỉ lấy materials từ assembly và product_unit cụ thể này
                            if ($assemblyId !== null && $productUnit !== null) {
                                $assemblyMaterials = DB::table('assembly_materials')
                                    ->where('assembly_materials.assembly_id', $assemblyId)
                                    ->where('assembly_materials.product_unit', $productUnit)
                                    ->where('assembly_materials.target_product_id', $item->item_id)
                                    ->whereNotNull('assembly_materials.serial')
                                    ->where('assembly_materials.serial', '!=', '')
                                    ->where('assembly_materials.serial', '!=', 'null')
                                    ->pluck('assembly_materials.serial')
                                    ->toArray();

                                // Tách serial nếu có nhiều serial phân tách bằng dấu phẩy
                                foreach ($assemblyMaterials as $serialStr) {
                                    $parts = array_map('trim', explode(',', $serialStr));
                                    $parts = array_filter($parts, function ($s) {
                                        return !empty($s) && $s !== 'null';
                                    });
                                    $serialComponents = array_merge($serialComponents, $parts);
                                }
                            }
                        }
                    }

                    return (object) [
                        'id' => null,
                        'item_id' => $item->item_id,
                        'serial_main' => $serialNumbers[0] ?? '', // Lấy serial đầu tiên làm serial chính
                        'old_serial' => $serialNumbers[0] ?? '',
                        'product_id' => $item->item_id,
                        'item_type' => $item->item_type,
                        'serial_components' => json_encode($serialComponents), // Serial components từ assembly
                        'serial_sim' => '',
                        'access_code' => '',
                        'iot_id' => '',
                        'mac_4g' => '',
                        'note' => ''
                    ];
                });
            }

            return response()->json([
                'success' => true,
                'deviceCodes' => $deviceCodes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lưu danh sách device codes
     */
    public function saveDeviceCodes(Request $request)
    {
        try {
            DB::beginTransaction();

            $dispatch_id = $request->input('dispatch_id');
            $device_codes = $request->input('device_codes', []);
            $type = $request->input('type', 'contract'); // Get type from request

            // Kiểm tra trùng serial trước khi xóa và tạo mới
            $usedSerials = [];
            foreach ($device_codes as $deviceCode) {
                $newSerial = $deviceCode['serial_main'] ?? null;
                if ($newSerial) {
                    $productId = $deviceCode['product_id'];

                    // Debug log
                    Log::info('Checking serial: ' . $newSerial . ' for product: ' . $productId);

                    // Kiểm tra trùng serial trong bảng tồn kho
                    // Chỉ báo lỗi nếu serial tồn tại với product_id khác
                    // Lấy danh sách ProductID đang sở hữu serial này trong dispatch hiện tại (để loại trừ)
                    $currentDispatchOwners = DeviceCode::where('dispatch_id', $dispatch_id)
                        ->where('type', $type)
                        ->where('serial_main', $newSerial)
                        ->pluck('product_id')
                        ->toArray();

                    // Kiểm tra trùng serial trong bảng tồn kho
                    // Chỉ báo lỗi nếu serial tồn tại với product_id khác và KHÔNG thuộc về dispatch hiện tại
                    $existsInStockWithDifferentProduct = DB::table('serials')
                        ->where('serial_number', $newSerial)
                        ->where('product_id', '!=', $productId)
                        ->whereNotIn('product_id', $currentDispatchOwners)
                        ->exists();
                    if ($existsInStockWithDifferentProduct) {
                        Log::info('Serial ' . $newSerial . ' exists in serials table with different product');
                        return response()->json([
                            'success' => false,
                            'message' => 'Serial "' . $newSerial . '" đã được sử dụng cho sản phẩm khác trong bảng tồn kho.'
                        ], 422);
                    }

                    // Kiểm tra trùng serial với device codes khác (không cùng product_id)
                    // Loại trừ device codes của cùng dispatch và type (vì sẽ bị xóa)
                    $conflictingDeviceCodes = DeviceCode::where('serial_main', $newSerial)
                        ->where('product_id', '!=', $productId)
                        ->where(function ($query) use ($dispatch_id, $type) {
                            $query->where('dispatch_id', '!=', $dispatch_id)
                                ->orWhere('type', '!=', $type);
                        })
                        ->get();

                    if ($conflictingDeviceCodes->isNotEmpty()) {
                        Log::info('Serial ' . $newSerial . ' conflicts with existing device codes:', $conflictingDeviceCodes->toArray());
                        return response()->json([
                            'success' => false,
                            'message' => 'Serial "' . $newSerial . '" đã được sử dụng cho thiết bị khác (Product ID: ' . $conflictingDeviceCodes->first()->product_id . ').'
                        ], 422);
                    }

                    // Kiểm tra trùng serial trong cùng batch (cùng dispatch và type)
                    $duplicateInBatch = false;
                    foreach ($usedSerials as $usedSerial) {
                        if ($usedSerial['serial'] === $newSerial && $usedSerial['product_id'] !== $productId) {
                            $duplicateInBatch = true;
                            Log::info('Serial ' . $newSerial . ' duplicate in batch with product: ' . $usedSerial['product_id']);
                            break;
                        }
                    }

                    if ($duplicateInBatch) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Serial "' . $newSerial . '" bị trùng lặp trong cùng một lần lưu.'
                        ], 422);
                    }

                    // Lưu serial đã sử dụng trong batch này
                    $usedSerials[] = [
                        'serial' => $newSerial,
                        'product_id' => $productId
                    ];

                    Log::info('Serial ' . $newSerial . ' is valid');
                }
            }

            // Nếu tất cả serial đều hợp lệ, mới xóa và tạo mới
            DeviceCode::where('dispatch_id', $dispatch_id)
                ->where('type', $type)
                ->delete();

            // Create new device codes
            foreach ($device_codes as $deviceCode) {
                $newSerial = $deviceCode['serial_main'] ?? null;
                // Skip empty records
                if (empty($deviceCode['serial_main'])) {
                    continue;
                }

                // Xử lý serial_components để lưu đúng format JSON
                $serialComponents = null; // Default null
                $serialComponentsArray = [];
                if (isset($deviceCode['serial_components']) && $deviceCode['serial_components'] !== null) {
                    if (is_array($deviceCode['serial_components'])) {
                        $serialComponentsArray = $deviceCode['serial_components'];
                        $serialComponents = json_encode($serialComponentsArray);
                    } else {
                        // Nếu đã là string, kiểm tra xem có phải JSON array không
                        $decoded = json_decode($deviceCode['serial_components'], true);
                        if (is_array($decoded)) {
                            $serialComponentsArray = $decoded;
                            $serialComponents = json_encode($decoded);
                        } else {
                            $serialComponents = $deviceCode['serial_components'];
                        }
                    }
                }

                // ===== TẠO serial_components_map (Keyed Object) =====
                // Format: {"MW-75W_1": "11111", "MW-75W_2": "", "TUSAT_1": "2222"}
                // Key = material_code + "_" + slot_index (1-based)
                $serialComponentsMap = null;

                // Ưu tiên sử dụng serial_components_map từ request (do frontend tính toán chính xác)
                if (!empty($deviceCode['serial_components_map'])) {
                    $mapFromRequest = $deviceCode['serial_components_map'];
                    if (is_string($mapFromRequest)) {
                        // Validate JSON
                        $decoded = json_decode($mapFromRequest, true);
                        if (is_array($decoded)) {
                            $serialComponentsMap = $mapFromRequest;
                        }
                    } elseif (is_array($mapFromRequest)) {
                        $serialComponentsMap = json_encode($mapFromRequest);
                    }
                }

                // Fallback: Tính toán map nếu không có từ request
                if ($serialComponentsMap === null && !empty($serialComponentsArray)) {
                    $productId = $deviceCode['item_id'] ?? $deviceCode['product_id'];

                    // Lấy dispatch_item để biết assembly_id và product_unit
                    $dispatchItem = DB::table('dispatch_items')
                        ->where('dispatch_id', $dispatch_id)
                        ->where('item_id', $productId)
                        ->where('item_type', $deviceCode['item_type'] ?? 'product')
                        ->first();

                    if ($dispatchItem && !empty($dispatchItem->assembly_id)) {
                        // Parse assembly_id và product_unit
                        $assemblyIds = is_string($dispatchItem->assembly_id)
                            ? explode(',', $dispatchItem->assembly_id)
                            : [$dispatchItem->assembly_id];
                        $assemblyIds = array_values(array_filter(array_map('trim', $assemblyIds)));

                        $productUnits = $dispatchItem->product_unit;
                        if (is_string($productUnits)) {
                            $decoded = json_decode($productUnits, true);
                            if (is_array($decoded)) {
                                $productUnits = $decoded;
                            } else {
                                $productUnits = explode(',', $productUnits);
                            }
                        }
                        if (!is_array($productUnits)) {
                            $productUnits = [$productUnits];
                        }
                        $productUnits = array_values(array_map('trim', $productUnits));

                        // Lấy vị trí của device code này trong danh sách (để map đúng assembly/unit)
                        $serialMain = $deviceCode['serial_main'] ?? '';
                        $serialNumbers = json_decode($dispatchItem->serial_numbers ?? '[]', true) ?: [];
                        $serialIndex = array_search($serialMain, $serialNumbers);
                        if ($serialIndex === false) {
                            // Fallback: dùng index trong device_codes array
                            $serialIndex = array_search($deviceCode, $device_codes);
                            if ($serialIndex === false)
                                $serialIndex = 0;
                        }

                        // Xác định assembly_id và product_unit cho serial này
                        $aIndex = isset($assemblyIds[$serialIndex]) ? $serialIndex : 0;
                        $uIndex = isset($productUnits[$serialIndex]) ? $serialIndex : 0;
                        $targetAssemblyId = $assemblyIds[$aIndex] ?? $assemblyIds[0] ?? null;
                        $targetProductUnit = $productUnits[$uIndex] ?? null;

                        if ($targetAssemblyId && $targetProductUnit !== null) {
                            // Query assembly_materials với logic Group-By-Material-ID
                            $assemblyMaterials = DB::table('assembly_materials')
                                ->join('materials', 'assembly_materials.material_id', '=', 'materials.id')
                                ->where('assembly_materials.assembly_id', $targetAssemblyId)
                                ->where('assembly_materials.target_product_id', $productId)
                                ->where('assembly_materials.product_unit', $targetProductUnit)
                                ->select('assembly_materials.*', 'materials.code as material_code')
                                ->orderBy('assembly_materials.id', 'asc')
                                ->get();

                            // Group by material_id (mimic ProductController logic)
                            $groupedByMaterial = [];
                            foreach ($assemblyMaterials as $am) {
                                $mid = $am->material_id;
                                if (!isset($groupedByMaterial[$mid])) {
                                    $groupedByMaterial[$mid] = [
                                        'code' => $am->material_code,
                                        'total_qty' => 0
                                    ];
                                }
                                $groupedByMaterial[$mid]['total_qty'] += $am->quantity;
                            }

                            // Sort theo material_id để đảm bảo thứ tự nhất quán với WarrantyController
                            ksort($groupedByMaterial);

                            // Flatten to list
                            $flatList = [];
                            foreach ($groupedByMaterial as $mid => $data) {
                                for ($i = 0; $i < $data['total_qty']; $i++) {
                                    $flatList[] = [
                                        'material_code' => $data['code'],
                                        'slot_index' => $i + 1
                                    ];
                                }
                            }

                            // Build map
                            $map = [];
                            foreach ($flatList as $idx => $item) {
                                $key = $item['material_code'] . '_' . $item['slot_index'];
                                $map[$key] = $serialComponentsArray[$idx] ?? '';
                            }

                            if (!empty($map)) {
                                $serialComponentsMap = json_encode($map);
                            }
                        }
                    }
                }


                // Nếu không có old_serial, lấy từ dispatch_items
                $oldSerial = $deviceCode['old_serial'] ?? null;
                if (empty($oldSerial)) {
                    // Lấy tất cả dispatch_items cho product này
                    $dispatchItems = DB::table('dispatch_items')
                        ->where('dispatch_id', $dispatch_id)
                        ->where('item_id', $deviceCode['item_id'] ?? $deviceCode['product_id'])
                        ->where('item_type', $deviceCode['item_type'] ?? 'product')
                        ->orderBy('id')
                        ->get();

                    if ($dispatchItems->isNotEmpty()) {
                        // Logic xác định old_serial dựa trên type và vị trí
                        if ($type === 'backup') {
                            // Cho backup, lấy serial đầu tiên từ dispatch_item cuối cùng
                            $lastItem = $dispatchItems->last();
                            if ($lastItem && $lastItem->serial_numbers) {
                                $serialNumbers = json_decode($lastItem->serial_numbers, true);
                                if (is_array($serialNumbers) && !empty($serialNumbers)) {
                                    $oldSerial = $serialNumbers[0];
                                }
                            }
                        } else {
                            // Cho contract, cần xác định vị trí của serial_main trong danh sách
                            $firstItem = $dispatchItems->first();
                            if ($firstItem && $firstItem->serial_numbers) {
                                $serialNumbers = json_decode($firstItem->serial_numbers, true);
                                if (is_array($serialNumbers) && !empty($serialNumbers)) {
                                    // Tìm vị trí của serial_main trong danh sách serial gốc
                                    $serialMain = $deviceCode['serial_main'] ?? '';

                                    // Nếu serial_main khớp với một serial trong danh sách, dùng serial đó
                                    if (in_array($serialMain, $serialNumbers)) {
                                        $oldSerial = $serialMain;
                                    } else {
                                        // Fallback: sử dụng vị trí trong danh sách device_codes
                                        // Lấy vị trí của device_code này trong danh sách
                                        $deviceCodeIndex = array_search($deviceCode, $device_codes);
                                        if ($deviceCodeIndex !== false && isset($serialNumbers[$deviceCodeIndex])) {
                                            $oldSerial = $serialNumbers[$deviceCodeIndex];
                                        } else {
                                            // Fallback cuối cùng: lấy serial đầu tiên
                                            $oldSerial = $serialNumbers[0];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                DeviceCode::create([
                    'dispatch_id' => $dispatch_id,
                    'product_id' => $deviceCode['product_id'],
                    'item_type' => $deviceCode['item_type'] ?? null,
                    'item_id' => $deviceCode['item_id'] ?? null,
                    'serial_main' => $deviceCode['serial_main'],
                    'serial_components' => $serialComponents,
                    'serial_components_map' => $serialComponentsMap, // Keyed object for robust reading
                    'serial_sim' => $deviceCode['serial_sim'] ?? null,
                    'access_code' => $deviceCode['access_code'] ?? null,
                    'iot_id' => $deviceCode['iot_id'] ?? null,
                    'mac_4g' => $deviceCode['mac_4g'] ?? null,
                    'note' => $deviceCode['note'] ?? null,
                    'old_serial' => $oldSerial,
                    'type' => $type
                ]);
            }

            // KHÔNG đồng bộ đè serial_numbers của dispatch_items nữa để giữ nguyên serial gốc đã xuất
            // Trước đây việc sync này khiến mất serial gốc nên UI không thể hiển thị đúng.
            // Nếu cần đồng bộ, hãy gọi endpoint sync riêng với chủ ý rõ ràng.

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lưu thông tin mã thiết bị thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu thông tin: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Đồng bộ serial numbers từ device_codes sang dispatch_items
     */
    private function syncSerialNumbersToDispatchItems($dispatch_id, $type)
    {
        try {
            // Lấy thông tin phiếu xuất để biết trạng thái và phục vụ cập nhật tồn kho serial
            $dispatch = \App\Models\Dispatch::find($dispatch_id);

            // Lấy tất cả device codes cho dispatch và type này
            $deviceCodes = DeviceCode::where('dispatch_id', $dispatch_id)
                ->where('type', $type)
                ->get();

            // Lấy tất cả dispatch items cho dispatch và type này
            $dispatchItems = DispatchItem::where('dispatch_id', $dispatch_id)
                ->where('category', $type)
                ->get();

            foreach ($dispatchItems as $dispatchItem) {
                // Lưu lại serial trước khi đồng bộ để xác định những serial cũ bị thay thế
                if (is_array($dispatchItem->serial_numbers)) {
                    $previousSerials = $dispatchItem->serial_numbers;
                } else {
                    $previousSerials = [];
                }

                // Tìm device codes cho dispatch item này
                $itemDeviceCodes = $deviceCodes->where('product_id', $dispatchItem->item_id);

                // Nếu item_type trong device_codes là null, chỉ match theo product_id
                if ($itemDeviceCodes->where('item_type', $dispatchItem->item_type)->isNotEmpty()) {
                    $itemDeviceCodes = $itemDeviceCodes->where('item_type', $dispatchItem->item_type);
                }

                $itemDeviceCodes = $itemDeviceCodes->values();

                if ($itemDeviceCodes->isNotEmpty()) {
                    // Lấy tất cả serial_main từ device codes
                    $serialNumbers = $itemDeviceCodes->pluck('serial_main')->filter()->toArray();

                    // Cập nhật serial_numbers trong dispatch_item
                    $dispatchItem->update([
                        'serial_numbers' => $serialNumbers
                    ]);

                    // Nếu phiếu đã duyệt, cập nhật trạng thái serial trong bảng serials để không hiển thị lại khi tạo phiếu mới
                    if ($dispatch && $dispatch->status === 'approved') {
                        $oldSerials = array_values(array_diff($previousSerials, $serialNumbers));
                        $affectedSerials = array_values(array_unique(array_merge($serialNumbers, $oldSerials)));

                        if (!empty($affectedSerials)) {
                            \App\Models\Serial::where('warehouse_id', $dispatchItem->warehouse_id)
                                ->where('type', $dispatchItem->item_type)
                                ->where('product_id', $dispatchItem->item_id)
                                ->whereIn('serial_number', $affectedSerials)
                                ->update(['status' => 'inactive']);
                        }
                    }
                } else {
                    Log::warning("No device codes found for dispatch item {$dispatchItem->id}");
                }
            }
        } catch (\Exception $e) {
            Log::error('Error syncing serial numbers to dispatch items: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * API endpoint để đồng bộ serial numbers từ device_codes sang dispatch_items
     */
    public function syncSerialNumbers(Request $request)
    {
        try {
            $request->validate([
                'dispatch_id' => 'required|exists:dispatches,id',
                'type' => 'required|in:contract,backup,general'
            ]);

            $dispatch_id = $request->input('dispatch_id');
            $type = $request->input('type');

            DB::beginTransaction();

            // Đồng bộ serial numbers
            $this->syncSerialNumbersToDispatchItems($dispatch_id, $type);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đồng bộ serial numbers thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đồng bộ serial numbers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import device codes từ Excel
     */
    public function importFromExcel(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy file']);
            }

            $dispatch_id = $request->input('dispatch_id');
            $type = $request->input('type', 'all');

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header row
            array_shift($rows);

            $processedData = [];
            foreach ($rows as $row) {
                if (empty($row[0]))
                    continue; // Skip rows without main serial

                $processedData[] = [
                    'serial_main' => $row[0],
                    'serial_components' => !empty($row[1]) ? explode(',', $row[1]) : [],
                    'serial_sim' => $row[2] ?? null,
                    'access_code' => $row[3] ?? null,
                    'iot_id' => $row[4] ?? null,
                    'mac_4g' => $row[5] ?? null,
                    'note' => $row[6] ?? null,
                    'dispatch_id' => $dispatch_id
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $processedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi import file: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Lấy thông tin thiết bị từ serial chính
     */
    public function getDeviceInfo($mainSerial)
    {
        try {
            // Tìm device code theo serial chính
            $deviceCode = DeviceCode::where('serial_main', $mainSerial)->first();

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

            // Nếu không tìm thấy, trả về dữ liệu trống
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

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin thiết bị: ' . $e->getMessage()
            ]);
        }
    }
}