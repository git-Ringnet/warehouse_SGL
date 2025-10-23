<?php

namespace App\Helpers;

class SerialHelper
{
    /**
     * Tạo virtual serial động cho thiết bị không có serial
     * 
     * @param array $serialNumbers - Mảng serial thực tế từ DB
     * @param int $quantity - Số lượng thiết bị
     * @return array - Mảng serial đầy đủ (thật + ảo)
     */
    public static function expandSerials(array $serialNumbers, int $quantity): array
    {
        $realSerialCount = count($serialNumbers);
        
        // Nếu quantity > số serial thực tế → tạo virtual serial
        if ($quantity > $realSerialCount) {
            $noSerialCount = $quantity - $realSerialCount;
            
            for ($i = 0; $i < $noSerialCount; $i++) {
                $serialNumbers[] = "N/A-{$i}";
            }
        }
        
        return $serialNumbers;
    }
    
    /**
     * Lọc bỏ virtual serial trước khi lưu vào DB
     * 
     * @param array $serialNumbers - Mảng serial (có thể chứa virtual serial)
     * @return array - Mảng chỉ chứa serial thật
     */
    public static function filterRealSerials(array $serialNumbers): array
    {
        return array_values(array_filter($serialNumbers, function($serial) {
            return strpos($serial, 'N/A-') !== 0;
        }));
    }
    
    /**
     * Kiểm tra xem serial có phải là virtual serial không
     * 
     * @param string $serial
     * @return bool
     */
    public static function isVirtualSerial(string $serial): bool
    {
        return strpos($serial, 'N/A-') === 0;
    }
    
    /**
     * Tính quantity từ mảng serial (bao gồm cả virtual serial)
     * 
     * @param array $serialNumbers
     * @return int
     */
    public static function calculateQuantity(array $serialNumbers): int
    {
        return count($serialNumbers);
    }
    
    /**
     * Chuẩn hóa serial để hiển thị
     * 
     * @param string $serial
     * @return string
     */
    public static function formatSerialForDisplay(string $serial): string
    {
        if (self::isVirtualSerial($serial)) {
            // Lấy số thứ tự từ N/A-0, N/A-1...
            $index = (int)str_replace('N/A-', '', $serial);
            return "Không có Serial #" . $index;
        }
        
        return $serial;
    }
    
    /**
     * Lấy virtual serial counter cao nhất trong dự án
     * 
     * @param int $projectId
     * @return int
     */
    public static function getMaxVirtualSerialCounter(int $projectId): int
    {
        $maxCounter = 0;
        
        // Lấy tất cả dispatch items trong dự án
        $dispatches = \App\Models\Dispatch::where('dispatch_type', 'project')
            ->where('project_id', $projectId)
            ->whereIn('status', ['approved', 'completed'])
            ->get();
        
        foreach ($dispatches as $dispatch) {
            foreach ($dispatch->items as $item) {
                $serialNumbers = $item->serial_numbers ?? [];
                $quantity = (int)$item->quantity;
                $serialCount = count(array_filter($serialNumbers, function($s) { return !empty(trim($s)); }));
                
                // Số lượng no serial
                $noSerialCount = $quantity - $serialCount;
                $maxCounter += $noSerialCount;
            }
        }
        
        // Kiểm tra trong DispatchReplacement xem có virtual serial nào cao hơn không
        $replacements = \App\Models\DispatchReplacement::whereHas('originalDispatchItem.dispatch', function($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })
            ->get();
        
        foreach ($replacements as $replacement) {
            // Kiểm tra original_serial
            if (self::isVirtualSerial($replacement->original_serial)) {
                $counter = (int)str_replace('N/A-', '', $replacement->original_serial);
                $maxCounter = max($maxCounter, $counter + 1);
            }
            
            // Kiểm tra replacement_serial
            if (self::isVirtualSerial($replacement->replacement_serial)) {
                $counter = (int)str_replace('N/A-', '', $replacement->replacement_serial);
                $maxCounter = max($maxCounter, $counter + 1);
            }
        }
        
        return $maxCounter;
    }
    
    /**
     * Tạo virtual serial với counter duy nhất trong dự án
     * 
     * @param int $projectId
     * @param int $count - Số lượng virtual serial cần tạo
     * @return array
     */
    public static function generateProjectVirtualSerials(int $projectId, int $count): array
    {
        $startCounter = self::getMaxVirtualSerialCounter($projectId);
        $virtuals = [];
        
        for ($i = 0; $i < $count; $i++) {
            $virtuals[] = "N/A-" . ($startCounter + $i);
        }
        
        return $virtuals;
    }
}
