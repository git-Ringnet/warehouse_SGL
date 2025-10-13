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
                        // Tìm assembly products có serial
                        $assemblyProducts = DB::table('assembly_products')
                            ->where('product_id', $item->item_id)
                            ->whereNotNull('serials')
                            ->where('serials', '!=', '')
                            ->get();

                        foreach ($assemblyProducts as $assemblyProduct) {
                            if ($assemblyProduct->serials) {
                                $serialComponents[] = $assemblyProduct->serials;
                            }
                        }

                        // Lấy serial vật tư từ assembly_materials
                        $assemblyMaterials = DB::table('assembly_materials')
                            ->join('assemblies', 'assembly_materials.assembly_id', '=', 'assemblies.id')
                            ->join('assembly_products', function($join) use ($item) {
                                $join->on('assembly_products.assembly_id', '=', 'assemblies.id')
                                     ->where('assembly_products.product_id', '=', $item->item_id);
                            })
                            ->where('assembly_materials.target_product_id', $item->item_id)
                            ->whereNotNull('assembly_materials.serial')
                            ->where('assembly_materials.serial', '!=', '')
                            ->pluck('assembly_materials.serial')
                            ->toArray();

                        $serialComponents = array_merge($serialComponents, $assemblyMaterials);
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
                    $existsInStockWithDifferentProduct = DB::table('serials')
                        ->where('serial_number', $newSerial)
                        ->where('product_id', '!=', $productId)
                        ->exists();
                    if ($existsInStockWithDifferentProduct) {
                        Log::info('Serial ' . $newSerial . ' exists in serials table with different product');
                        return response()->json([
                            'success' => false,
                            'message' => 'Serial "'. $newSerial .'" đã được sử dụng cho sản phẩm khác trong bảng tồn kho.'
                        ], 422);
                    }
                    
                    // Kiểm tra trùng serial với device codes khác (không cùng product_id)
                    // Loại trừ device codes của cùng dispatch và type (vì sẽ bị xóa)
                    $conflictingDeviceCodes = DeviceCode::where('serial_main', $newSerial)
                        ->where('product_id', '!=', $productId)
                        ->where(function($query) use ($dispatch_id, $type) {
                            $query->where('dispatch_id', '!=', $dispatch_id)
                                  ->orWhere('type', '!=', $type);
                        })
                        ->get();
                    
                    if ($conflictingDeviceCodes->isNotEmpty()) {
                        Log::info('Serial ' . $newSerial . ' conflicts with existing device codes:', $conflictingDeviceCodes->toArray());
                        return response()->json([
                            'success' => false,
                            'message' => 'Serial "'. $newSerial .'" đã được sử dụng cho thiết bị khác (Product ID: ' . $conflictingDeviceCodes->first()->product_id . ').'
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
                            'message' => 'Serial "'. $newSerial .'" bị trùng lặp trong cùng một lần lưu.'
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
                if (isset($deviceCode['serial_components']) && $deviceCode['serial_components'] !== null) {
                    if (is_array($deviceCode['serial_components'])) {
                        // Nếu là array, encode thành JSON string: ["1","2","3"]
                        $serialComponents = json_encode($deviceCode['serial_components']);
                    } else {
                        // Nếu đã là string, sử dụng trực tiếp
                        $serialComponents = $deviceCode['serial_components'];
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
                $previousSerials = is_array($dispatchItem->serial_numbers)
                    ? $dispatchItem->serial_numbers
                    : (empty($dispatchItem->serial_numbers) ? [] : (json_decode($dispatchItem->serial_numbers, true) ?: []));

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
                if (empty($row[0])) continue; // Skip rows without main serial
                
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