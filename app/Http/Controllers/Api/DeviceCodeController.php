<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceCodeController extends Controller
{
    /**
     * Get device codes for a dispatch by type
     */
    public function getDeviceCodes($dispatchId)
    {
        try {
            $type = request()->get('type', 'all');
            
            // Debug: Log the dispatch ID and type
            Log::info('Getting device codes for dispatch_id: ' . $dispatchId . ', type: ' . $type);
            
            // Build query based on type
            $query = DB::table('device_codes')
                ->where('dispatch_id', $dispatchId);
            
            // Filter by type if specified
            if ($type !== 'all') {
                $query->where('type', $type);
            }
            
            // Get device codes with all necessary fields
            $deviceCodes = $query->select(
                'id',
                'dispatch_id', 
                'product_id', 
                'item_type',
                'item_id',
                'serial_main',
                'serial_components',
                'serial_sim',
                'access_code',
                'iot_id',
                'mac_4g',
                'note',
                'type'
            )->get();

            // Debug: Log the query result
            Log::info('Device codes found for type ' . $type . ': ' . $deviceCodes->count());
            Log::info('Device codes data: ' . $deviceCodes->toJson());

            // Log raw serial_components để debug
            $deviceCodes->each(function ($deviceCode) {
                Log::info('Raw serial_components for device code ' . $deviceCode->id . ': ' . $deviceCode->serial_components);
            });

            // Clean up double-encoded serial_components và đảm bảo format đúng
            $deviceCodes->each(function ($deviceCode) {
                if ($deviceCode->serial_components && is_string($deviceCode->serial_components)) {
                    // Kiểm tra xem có phải double-encoded JSON không: "[\"1\",\"2\",\"3\"]"
                    if (strpos($deviceCode->serial_components, '\\"') !== false) {
                        try {
                            // Decode double-encoded JSON
                            $firstDecode = json_decode($deviceCode->serial_components, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_string($firstDecode)) {
                                // Decode lần thứ 2 để lấy array
                                $secondDecode = json_decode($firstDecode, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($secondDecode)) {
                                    // Re-encode thành JSON string đúng format: ["1","2","3"]
                                    $deviceCode->serial_components = json_encode($secondDecode);
                                    Log::info('Fixed double-encoded serial_components for device code ' . $deviceCode->id);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning('Error fixing double-encoded serial_components for device code ' . $deviceCode->id . ': ' . $e->getMessage());
                        }
                    } else {
                        // Đảm bảo format JSON đúng
                        try {
                            $decoded = json_decode($deviceCode->serial_components, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                // Re-encode để đảm bảo format nhất quán
                                $deviceCode->serial_components = json_encode($decoded);
                            }
                        } catch (\Exception $e) {
                            Log::warning('Error processing serial_components for device code ' . $deviceCode->id . ': ' . $e->getMessage());
                        }
                    }
                }
            });

            return response()->json([
                'success' => true,
                'deviceCodes' => $deviceCodes,
                'debug' => [
                    'dispatch_id_requested' => $dispatchId,
                    'type_requested' => $type,
                    'found_device_codes' => $deviceCodes->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting device codes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin mã thiết bị: ' . $e->getMessage()
            ], 500);
        }
    }
} 