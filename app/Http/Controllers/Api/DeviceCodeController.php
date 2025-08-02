<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceCodeController extends Controller
{
    /**
     * Get device codes for a dispatch
     */
    public function getDeviceCodes($dispatchId)
    {
        try {
            // Debug: Log the dispatch ID
            Log::info('Getting device codes for dispatch_id: ' . $dispatchId);
            
            // Get device codes from device_codes table
            $deviceCodes = DB::table('device_codes')
                ->where('dispatch_id', $dispatchId)
                ->select('item_id', 'serial_main', 'old_serial', 'product_id', 'item_type')
                ->get();

            // Debug: Log the query result
            Log::info('Device codes found: ' . $deviceCodes->count());
            Log::info('Device codes data: ' . $deviceCodes->toJson());

            // Also check if there are any device codes at all
            $allDeviceCodes = DB::table('device_codes')->get();
            Log::info('Total device codes in table: ' . $allDeviceCodes->count());
            
            // Debug: Check all device codes with dispatch_id
            $allWithDispatch = DB::table('device_codes')
                ->whereNotNull('dispatch_id')
                ->select('dispatch_id', 'item_id', 'serial_main')
                ->get();
            Log::info('All device codes with dispatch_id: ' . $allWithDispatch->toJson());

            return response()->json([
                'success' => true,
                'deviceCodes' => $deviceCodes,
                'debug' => [
                    'dispatch_id_requested' => $dispatchId,
                    'total_device_codes' => $allDeviceCodes->count(),
                    'found_device_codes' => $deviceCodes->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin mã thiết bị: ' . $e->getMessage()
            ], 500);
        }
    }
} 