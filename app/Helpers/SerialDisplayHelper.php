<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class SerialDisplayHelper
{
    /**
     * Lấy serial hiển thị cho dự án/cho thuê
     * Ưu tiên serial_main từ device_codes, fallback về serial gốc
     * 
     * @param int $dispatchId
     * @param int $itemId
     * @param string $itemType
     * @param array $originalSerials
     * @return array
     */
    public static function getDisplaySerials($dispatchId, $itemId, $itemType, $originalSerials = [])
    {
        // Chuẩn hóa input
        $normalizedOriginals = array_values(array_filter(array_map(function ($s) {
            return trim((string) $s);
        }, is_array($originalSerials) ? $originalSerials : [])));

        if (empty($normalizedOriginals)) {
            return [];
        }

        $displaySerials = [];

        foreach ($normalizedOriginals as $originalSerial) {
            $resolved = null;

            // 1) Ưu tiên device_code trong cùng dispatch + item
            $deviceCode = DB::table('device_codes')
                ->where('dispatch_id', $dispatchId)
                ->where('item_id', $itemId)
                ->where('item_type', $itemType)
                ->where('old_serial', $originalSerial)
                ->whereNotNull('serial_main')
                ->where('serial_main', '!=', '')
                ->orderBy('created_at', 'desc')
                ->first();
            if ($deviceCode) {
                $resolved = $deviceCode->serial_main;
            }

            // 2) Không có trong dispatch hiện tại: tìm theo item ở các dispatch khác
            if (!$resolved) {
                $deviceCode = DB::table('device_codes')
                    ->where('item_id', $itemId)
                    ->where('item_type', $itemType)
                    ->where('old_serial', $originalSerial)
                    ->whereNotNull('serial_main')
                    ->where('serial_main', '!=', '')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($deviceCode) {
                    $resolved = $deviceCode->serial_main;
                }
            }

            // 3) Fallback cuối: tìm theo old_serial toàn cục (không ràng buộc item)
            if (!$resolved) {
                $deviceCode = DB::table('device_codes')
                    ->where('old_serial', $originalSerial)
                    ->whereNotNull('serial_main')
                    ->where('serial_main', '!=', '')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($deviceCode) {
                    $resolved = $deviceCode->serial_main;
                }
            }

            $displaySerials[] = $resolved ?: $originalSerial;
        }

        return $displaySerials;
    }

    /**
     * Lấy serial hiển thị cho một serial cụ thể
     * 
     * @param int $dispatchId
     * @param int $itemId
     * @param string $itemType
     * @param string $originalSerial
     * @return string
     */
    public static function getDisplaySerial($dispatchId, $itemId, $itemType, $originalSerial)
    {
        // Lấy device_codes cho item này với old_serial khớp
        $deviceCode = DB::table('device_codes')
            ->where('dispatch_id', $dispatchId)
            ->where('item_id', $itemId)
            ->where('item_type', $itemType)
            ->where('old_serial', $originalSerial)
            ->first();

        if (!$deviceCode || empty($deviceCode->serial_main)) {
            // Không có device_code trong dispatch hiện tại, tìm trong các dispatch khác
            // Ưu tiên device_code có item_id và item_type đúng
            $deviceCode = DB::table('device_codes')
                ->where('item_id', $itemId)
                ->where('item_type', $itemType)
                ->where('old_serial', $originalSerial)
                ->whereNotNull('serial_main')
                ->where('serial_main', '!=', '')
                ->orderBy('created_at', 'desc') // Ưu tiên device_code mới nhất
                ->first();
            
            // Nếu vẫn không tìm thấy, tìm với item_id và item_type rỗng (fallback)
            if (!$deviceCode) {
                $deviceCode = DB::table('device_codes')
                    ->where(function($query) use ($itemId, $itemType) {
                        $query->where(function($q) use ($itemId, $itemType) {
                            $q->where('item_id', $itemId)
                              ->where('item_type', $itemType);
                        })->orWhere(function($q) {
                            $q->whereNull('item_id')
                              ->whereNull('item_type');
                        });
                    })
                    ->where('old_serial', $originalSerial)
                    ->whereNotNull('serial_main')
                    ->where('serial_main', '!=', '')
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }

        if (!$deviceCode || empty($deviceCode->serial_main)) {
            // Không có device_code, trả về serial gốc
            return $originalSerial;
        }

        // Có device_code, kiểm tra xem serial gốc có được đổi tên không
        if ($originalSerial === $deviceCode->old_serial) {
            // Serial gốc này đã được đổi tên, trả về serial_main
            return $deviceCode->serial_main;
        }

        // Serial gốc không được đổi tên, trả về serial gốc
        return $originalSerial;
    }

    /**
     * Lấy serial components hiển thị cho dự án/cho thuê
     * 
     * @param int $dispatchId
     * @param int $itemId
     * @param string $itemType
     * @return array
     */
    public static function getDisplaySerialComponents($dispatchId, $itemId, $itemType)
    {
        // Lấy device_codes cho item này
        $deviceCode = DB::table('device_codes')
            ->where('dispatch_id', $dispatchId)
            ->where('item_id', $itemId)
            ->where('item_type', $itemType)
            ->first();

        if (!$deviceCode || empty($deviceCode->serial_components)) {
            return [];
        }

        // Parse serial_components từ JSON
        $serialComponents = json_decode($deviceCode->serial_components, true);
        
        if (!is_array($serialComponents)) {
            return [];
        }

        return array_filter($serialComponents, function($serial) {
            return !empty(trim($serial));
        });
    }
}
