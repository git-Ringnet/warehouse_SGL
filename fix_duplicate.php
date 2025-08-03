<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomerMaintenanceRequest;
use Illuminate\Support\Facades\DB;

echo "=== FIX DUPLICATE CUST-MAINT-0004 ===\n\n";

// 1. Kiểm tra tất cả records có CUST-MAINT-0004
echo "1. Kiểm tra tất cả records có CUST-MAINT-0004:\n";
$duplicates = CustomerMaintenanceRequest::where('request_code', 'CUST-MAINT-0004')->get();
echo "Tìm thấy " . $duplicates->count() . " records với CUST-MAINT-0004\n";

if ($duplicates->count() > 0) {
    echo "Chi tiết:\n";
    foreach ($duplicates as $record) {
        echo "  - ID: " . $record->id . ", Created: " . $record->created_at . "\n";
    }
    
    // 2. Xóa tất cả records duplicate
    echo "\n2. Xóa tất cả records duplicate...\n";
    $deleted = CustomerMaintenanceRequest::where('request_code', 'CUST-MAINT-0004')->delete();
    echo "Đã xóa " . $deleted . " records\n";
}

// 3. Kiểm tra lại
echo "\n3. Kiểm tra lại sau khi xóa:\n";
$remaining = CustomerMaintenanceRequest::where('request_code', 'CUST-MAINT-0004')->count();
echo "Còn lại " . $remaining . " records với CUST-MAINT-0004\n";

// 4. Kiểm tra auto increment
echo "\n4. Kiểm tra auto increment:\n";
$result = DB::select("SHOW TABLE STATUS LIKE 'customer_maintenance_requests'");
if (!empty($result)) {
    echo "Auto increment hiện tại: " . $result[0]->Auto_increment . "\n";
}

// 5. Reset auto increment nếu cần
echo "\n5. Reset auto increment...\n";
$maxId = CustomerMaintenanceRequest::max('id');
if ($maxId) {
    DB::statement("ALTER TABLE customer_maintenance_requests AUTO_INCREMENT = " . ($maxId + 1));
    echo "Đã reset auto increment về " . ($maxId + 1) . "\n";
} else {
    DB::statement("ALTER TABLE customer_maintenance_requests AUTO_INCREMENT = 1");
    echo "Đã reset auto increment về 1\n";
}

// 6. Kiểm tra tất cả request codes
echo "\n6. Tất cả request codes hiện tại:\n";
$allRequests = CustomerMaintenanceRequest::orderBy('id')->get();
foreach ($allRequests as $request) {
    echo "  - ID: " . $request->id . ", Code: " . $request->request_code . ", Created: " . $request->created_at . "\n";
}

echo "\n=== HOÀN THÀNH ===\n"; 