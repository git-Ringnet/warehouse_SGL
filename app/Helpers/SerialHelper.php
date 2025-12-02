<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class SerialHelper
{
    /**
     * Tạo virtual serial động cho thiết bị không có serial
     * 
     * LƯU Ý QUAN TRỌNG: Hàm này chỉ dùng để HIỂN THỊ trong memory, KHÔNG dùng để tạo serial mới lưu DB.
     * Để tạo serial mới lưu DB, sử dụng generateUniqueVirtualSerial() hoặc generateUniqueVirtualSerials().
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
            
            // Sử dụng random suffix thay vì index tuần tự để tránh trùng lặp
            for ($i = 0; $i < $noSerialCount; $i++) {
                $serialNumbers[] = self::generateUniqueVirtualSerial();
            }
        }
        
        return $serialNumbers;
    }
    
    /**
     * Tạo một virtual serial duy nhất với random alphanumeric suffix
     * Format: N/A-XXXX (4 ký tự alphanumeric ngẫu nhiên)
     * 
     * Giải thích: Thay vì dùng N/A-0, N/A-1 (dễ trùng giữa các project),
     * ta dùng N/A-A1B2 (random) để đảm bảo duy nhất toàn cục.
     * 
     * @return string
     */
    public static function generateUniqueVirtualSerial(): string
    {
        $maxAttempts = 100; // Giới hạn số lần thử để tránh vòng lặp vô hạn
        $attempt = 0;
        
        do {
            $suffix = self::generateRandomSuffix(6); // 6 ký tự cho độ duy nhất cao hơn
            $virtualSerial = "N/A-{$suffix}";
            $attempt++;
            
            // Kiểm tra xem serial này đã tồn tại trong DB chưa
            $exists = self::virtualSerialExistsInDatabase($virtualSerial);
            
        } while ($exists && $attempt < $maxAttempts);
        
        // Nếu vẫn trùng sau nhiều lần thử, thêm timestamp để đảm bảo duy nhất
        if ($exists) {
            $virtualSerial = "N/A-" . self::generateRandomSuffix(4) . substr(time(), -4);
        }
        
        return $virtualSerial;
    }
    
    /**
     * Tạo nhiều virtual serial duy nhất
     * 
     * @param int $count - Số lượng serial cần tạo
     * @return array
     */
    public static function generateUniqueVirtualSerials(int $count): array
    {
        $serials = [];
        
        for ($i = 0; $i < $count; $i++) {
            $serial = self::generateUniqueVirtualSerial();
            
            // Đảm bảo không trùng với serial vừa tạo trong batch này
            while (in_array($serial, $serials)) {
                $serial = self::generateUniqueVirtualSerial();
            }
            
            $serials[] = $serial;
        }
        
        return $serials;
    }
    
    /**
     * Tạo random alphanumeric suffix
     * 
     * @param int $length - Độ dài suffix (mặc định 6)
     * @return string
     */
    private static function generateRandomSuffix(int $length = 6): string
    {
        // Sử dụng chữ cái viết hoa và số để dễ đọc, tránh nhầm lẫn (bỏ O, 0, I, 1, L)
        $characters = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $suffix = '';
        
        for ($i = 0; $i < $length; $i++) {
            $suffix .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $suffix;
    }
    
    /**
     * Kiểm tra virtual serial đã tồn tại trong database chưa
     * Tìm trong dispatch_items.serial_numbers và dispatch_replacements
     * 
     * @param string $virtualSerial
     * @return bool
     */
    private static function virtualSerialExistsInDatabase(string $virtualSerial): bool
    {
        // Kiểm tra trong dispatch_items
        $existsInDispatchItems = DB::table('dispatch_items')
            ->whereRaw("JSON_CONTAINS(serial_numbers, ?)", [json_encode($virtualSerial)])
            ->exists();
        
        if ($existsInDispatchItems) {
            return true;
        }
        
        // Kiểm tra trong dispatch_replacements
        $existsInReplacements = DB::table('dispatch_replacements')
            ->where('original_serial', $virtualSerial)
            ->orWhere('replacement_serial', $virtualSerial)
            ->exists();
        
        return $existsInReplacements;
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
     * Hỗ trợ cả format cũ (N/A-0, N/A-1) và format mới (N/A-A1B2C3)
     * 
     * @param string $serial
     * @return bool
     */
    public static function isVirtualSerial(string $serial): bool
    {
        return strpos($serial, 'N/A-') === 0;
    }
    
    /**
     * Kiểm tra xem serial có phải là virtual serial format cũ (N/A-0, N/A-1...) không
     * Dùng để phát hiện và migrate serial cũ nếu cần
     * 
     * @param string $serial
     * @return bool
     */
    public static function isLegacyVirtualSerial(string $serial): bool
    {
        // Format cũ: N/A- theo sau bởi chỉ số (0, 1, 2...)
        return preg_match('/^N\/A-\d+$/', $serial) === 1;
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
     * Hỗ trợ cả format cũ (N/A-0) và format mới (N/A-A1B2C3)
     * 
     * @param string $serial
     * @return string
     */
    public static function formatSerialForDisplay(string $serial): string
    {
        if (self::isVirtualSerial($serial)) {
            // Lấy suffix từ N/A-XXX
            $suffix = str_replace('N/A-', '', $serial);
            
            // Nếu là format cũ (chỉ số), hiển thị như cũ để tương thích
            if (is_numeric($suffix)) {
                return "Không có Serial #" . $suffix;
            }
            
            // Format mới: hiển thị mã ngắn gọn
            return "Không có Serial ({$suffix})";
        }
        
        return $serial;
    }
    
    /**
     * [DEPRECATED - Giữ lại để tương thích ngược]
     * Lấy virtual serial counter cao nhất trong dự án
     * 
     * LƯU Ý: Hàm này không còn cần thiết với format mới (random suffix).
     * Giữ lại để hỗ trợ migration và debug serial cũ.
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
                
                // Đếm số virtual serial format cũ (N/A-0, N/A-1...)
                foreach ($serialNumbers as $serial) {
                    if (self::isLegacyVirtualSerial($serial)) {
                        $counter = (int)str_replace('N/A-', '', $serial);
                        $maxCounter = max($maxCounter, $counter + 1);
                    }
                }
            }
        }
        
        // Kiểm tra trong DispatchReplacement
        $replacements = \App\Models\DispatchReplacement::whereHas('originalDispatchItem.dispatch', function($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })
            ->get();
        
        foreach ($replacements as $replacement) {
            if (self::isLegacyVirtualSerial($replacement->original_serial)) {
                $counter = (int)str_replace('N/A-', '', $replacement->original_serial);
                $maxCounter = max($maxCounter, $counter + 1);
            }
            
            if (self::isLegacyVirtualSerial($replacement->replacement_serial)) {
                $counter = (int)str_replace('N/A-', '', $replacement->replacement_serial);
                $maxCounter = max($maxCounter, $counter + 1);
            }
        }
        
        return $maxCounter;
    }
    
    /**
     * [DEPRECATED - Giữ lại để tương thích ngược]
     * Tạo virtual serial với counter duy nhất trong dự án
     * 
     * LƯU Ý: Sử dụng generateUniqueVirtualSerials() thay thế.
     * Hàm này giữ lại để không break code cũ đang gọi.
     * 
     * @param int $projectId
     * @param int $count - Số lượng virtual serial cần tạo
     * @return array
     */
    public static function generateProjectVirtualSerials(int $projectId, int $count): array
    {
        // Chuyển sang sử dụng logic mới với random suffix
        // Không còn phụ thuộc vào projectId vì random suffix đảm bảo duy nhất toàn cục
        return self::generateUniqueVirtualSerials($count);
    }
    
    /**
     * Lấy tất cả virtual serial đang tồn tại trong một project/rental
     * Hữu ích để debug và kiểm tra trùng lặp
     * 
     * @param int $projectId
     * @param string $dispatchType - 'project' hoặc 'rental'
     * @return array
     */
    public static function getExistingVirtualSerials(int $projectId, string $dispatchType = 'project'): array
    {
        $virtualSerials = [];
        
        $dispatches = \App\Models\Dispatch::where('dispatch_type', $dispatchType)
            ->where('project_id', $projectId)
            ->get();
        
        foreach ($dispatches as $dispatch) {
            foreach ($dispatch->items as $item) {
                $serialNumbers = $item->serial_numbers ?? [];
                foreach ($serialNumbers as $serial) {
                    if (self::isVirtualSerial($serial)) {
                        $virtualSerials[] = $serial;
                    }
                }
            }
        }
        
        return array_unique($virtualSerials);
    }
}
