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