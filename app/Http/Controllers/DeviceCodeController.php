<?php

namespace App\Http\Controllers;

use App\Models\DeviceCode;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class DeviceCodeController extends Controller
{
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['Serial chính', 'Serial vật tư', 'Serial SIM', 'Mã truy cập', 'ID IoT', 'MAC 4G', 'Chú thích'];
        $sheet->fromArray([$headers], NULL, 'A1');

        // Style headers
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        foreach(range('A','G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create the file
        $writer = new Xlsx($spreadsheet);
        $filename = 'device_codes_template.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
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

            // Remove header row
            array_shift($rows);

            $data = [];
            foreach ($rows as $row) {
                if (!empty($row[0])) { // Chỉ xử lý các dòng có serial chính
                    $data[] = [
                        'serial_main' => $row[0],
                        'serial_components' => $row[1],
                        'serial_sim' => $row[2],
                        'access_code' => $row[3],
                        'iot_id' => $row[4],
                        'mac_4g' => $row[5],
                        'note' => $row[6]
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data
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
            $type = $request->query('type', 'all');
            
            $query = DeviceCode::where('dispatch_id', $dispatch_id);
            
            // Lọc theo loại sản phẩm nếu được chỉ định
            if ($type !== 'all') {
                // Lấy các sản phẩm thuộc loại đã chọn
                $productIds = \App\Models\DispatchItem::where('dispatch_id', $dispatch_id)
                    ->where('category', $type)
                    ->pluck('item_id')
                    ->toArray();
                
                $query->whereIn('product_id', $productIds);
            }
            
            $deviceCodes = $query->get();
            
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
            
            foreach ($device_codes as $deviceCode) {
                // Nếu có ID, cập nhật, ngược lại tạo mới
                if (!empty($deviceCode['id'])) {
                    $existingCode = DeviceCode::find($deviceCode['id']);
                    if ($existingCode) {
                        $existingCode->update([
                            'serial_main' => $deviceCode['serial_main'],
                            'serial_components' => $deviceCode['serial_components'] ?? [],
                            'serial_sim' => $deviceCode['serial_sim'] ?? null,
                            'access_code' => $deviceCode['access_code'] ?? null,
                            'iot_id' => $deviceCode['iot_id'] ?? null,
                            'mac_4g' => $deviceCode['mac_4g'] ?? null,
                            'note' => $deviceCode['note'] ?? null
                        ]);
                    }
                } else {
                    DeviceCode::create([
                        'dispatch_id' => $dispatch_id,
                        'product_id' => $deviceCode['product_id'],
                        'serial_main' => $deviceCode['serial_main'],
                        'serial_components' => $deviceCode['serial_components'] ?? [],
                        'serial_sim' => $deviceCode['serial_sim'] ?? null,
                        'access_code' => $deviceCode['access_code'] ?? null,
                        'iot_id' => $deviceCode['iot_id'] ?? null,
                        'mac_4g' => $deviceCode['mac_4g'] ?? null,
                        'note' => $deviceCode['note'] ?? null
                    ]);
                }
            }
            
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
                if (empty($row[0])) continue; // Skip rows without main serial
                
                $processedData[] = [
                    'serial_main' => $row[0],
                    'serial_components' => !empty($row[1]) ? explode(',', $row[1]) : [],
                    'serial_sim' => $row[2] ?? null,
                    'access_code' => $row[3] ?? null,
                    'iot_id' => $row[4] ?? null,
                    'mac_4g' => $row[5] ?? null,
                    'note' => $row[6] ?? null
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