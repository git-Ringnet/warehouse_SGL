<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomerMaintenanceRequest;
use Illuminate\Support\Facades\DB;

echo "=== CHECK ALL DUPLICATE REQUEST CODES ===\n\n";

// 1. Tìm tất cả duplicate request codes
echo "1. Tìm tất cả duplicate request codes:\n";
$duplicates = DB::select("
    SELECT request_code, COUNT(*) as count
    FROM customer_maintenance_requests 
    GROUP BY request_code 
    HAVING COUNT(*) > 1
");

if (empty($duplicates)) {
    echo "✓ Không có duplicate request codes\n";
} else {
    echo "Tìm thấy " . count($duplicates) . " request codes bị duplicate:\n";
    foreach ($duplicates as $dup) {
        echo "  - Code: " . $dup->request_code . " (Count: " . $dup->count . ")\n";
    }
    
    // 2. Xóa tất cả records duplicate, giữ lại record đầu tiên
    echo "\n2. Xóa duplicate records (giữ lại record đầu tiên):\n";
    foreach ($duplicates as $dup) {
        $records = CustomerMaintenanceRequest::where('request_code', $dup->request_code)
            ->orderBy('id')
            ->get();
        
        // Giữ lại record đầu tiên, xóa các record còn lại
        $firstRecord = $records->first();
        $recordsToDelete = $records->slice(1);
        
        echo "  - Code: " . $dup->request_code . " - Giữ ID: " . $firstRecord->id . ", Xóa " . $recordsToDelete->count() . " records\n";
        
        foreach ($recordsToDelete as $record) {
            $record->delete();
        }
    }
}

// 3. Kiểm tra lại
echo "\n3. Kiểm tra lại sau khi xóa:\n";
$remainingDuplicates = DB::select("
    SELECT request_code, COUNT(*) as count
    FROM customer_maintenance_requests 
    GROUP BY request_code 
    HAVING COUNT(*) > 1
");

if (empty($remainingDuplicates)) {
    echo "✓ Không còn duplicate request codes\n";
} else {
    echo "Vẫn còn " . count($remainingDuplicates) . " duplicate request codes:\n";
    foreach ($remainingDuplicates as $dup) {
        echo "  - Code: " . $dup->request_code . " (Count: " . $dup->count . ")\n";
    }
}

// 4. Reset auto increment
echo "\n4. Reset auto increment...\n";
$maxId = CustomerMaintenanceRequest::max('id');
if ($maxId) {
    DB::statement("ALTER TABLE customer_maintenance_requests AUTO_INCREMENT = " . ($maxId + 1));
    echo "Đã reset auto increment về " . ($maxId + 1) . "\n";
} else {
    DB::statement("ALTER TABLE customer_maintenance_requests AUTO_INCREMENT = 1");
    echo "Đã reset auto increment về 1\n";
}

// 5. Hiển thị tất cả request codes
echo "\n5. Tất cả request codes hiện tại:\n";
$allRequests = CustomerMaintenanceRequest::orderBy('id')->get();
foreach ($allRequests as $request) {
    echo "  - ID: " . $request->id . ", Code: " . $request->request_code . ", Created: " . $request->created_at . "\n";
}

echo "\n=== HOÀN THÀNH ===\n"; 