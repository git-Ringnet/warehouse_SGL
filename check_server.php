<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CustomerMaintenanceRequest;
use Illuminate\Support\Facades\DB;

echo "=== KIỂM TRA DỮ LIỆU TRÊN SERVER ===\n\n";

// 0. Tổng quan tất cả phiếu
echo "0. TỔNG QUAN TẤT CẢ PHIẾU:\n";
$totalRequests = CustomerMaintenanceRequest::count();
echo "Tổng số phiếu: {$totalRequests}\n";

// Phân tích theo status
$statusCounts = CustomerMaintenanceRequest::selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get();
echo "Phân bố theo trạng thái:\n";
$statusCounts->each(function($item) {
    echo "  - {$item->status}: {$item->count} phiếu\n";
});

// Phân tích theo tháng
$monthlyCounts = CustomerMaintenanceRequest::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
    ->groupBy('month')
    ->orderBy('month')
    ->get();
echo "Phân bố theo tháng:\n";
$monthlyCounts->each(function($item) {
    $monthName = date('F', mktime(0, 0, 0, $item->month, 1));
    echo "  - {$monthName}: {$item->count} phiếu\n";
});

echo "\n";

// 1. Kiểm tra tất cả records
echo "1. Tất cả customer maintenance requests:\n";
$allRequests = CustomerMaintenanceRequest::orderBy('id')->get();
echo "Tổng số records: " . $allRequests->count() . "\n";

$allRequests->each(function($req) {
    echo "ID: {$req->id}, Code: {$req->request_code}, Status: {$req->status}, Created: {$req->created_at}\n";
});

echo "\n";

// 2. Kiểm tra record với ID 4
echo "2. Kiểm tra record với ID 4:\n";
$req4 = CustomerMaintenanceRequest::find(4);
if ($req4) {
    echo "✓ Tìm thấy ID 4: Code: {$req4->request_code}, Status: {$req4->status}, Created: {$req4->created_at}\n";
} else {
    echo "✗ Không tìm thấy ID 4\n";
}

echo "\n";

// 3. Kiểm tra duplicate CUST-MAINT-0004
echo "3. Kiểm tra duplicate CUST-MAINT-0004:\n";
$duplicates = CustomerMaintenanceRequest::where('request_code', 'CUST-MAINT-0004')->get();
echo "Số records có CUST-MAINT-0004: " . $duplicates->count() . "\n";

$duplicates->each(function($req) {
    echo "ID: {$req->id}, Code: {$req->request_code}, Status: {$req->status}, Created: {$req->created_at}\n";
});

echo "\n";

// 4. Kiểm tra auto increment
echo "4. Kiểm tra auto increment:\n";
$result = DB::select("SHOW TABLE STATUS LIKE 'customer_maintenance_requests'");
if (!empty($result)) {
    $autoIncrement = $result[0]->Auto_increment;
    echo "Auto increment hiện tại: {$autoIncrement}\n";
}

echo "\n";

// 5. Kiểm tra các request codes có thể bị duplicate
echo "5. Kiểm tra các request codes có thể bị duplicate:\n";
$duplicateCodes = CustomerMaintenanceRequest::selectRaw('request_code, COUNT(*) as count')
    ->groupBy('request_code')
    ->having('count', '>', 1)
    ->get();

if ($duplicateCodes->count() > 0) {
    echo "⚠️  Tìm thấy các mã phiếu bị duplicate:\n";
    $duplicateCodes->each(function($item) {
        echo "  - {$item->request_code}: {$item->count} records\n";
    });
} else {
    echo "✓ Không có mã phiếu nào bị duplicate!\n";
}

echo "\n";

// 6. Nếu có duplicate CUST-MAINT-0004, hỏi có muốn xóa không
if ($duplicates->count() > 0) {
    echo "6. CÓ DUPLICATE CUST-MAINT-0004! Bạn có muốn xóa không? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim($line) === 'y' || trim($line) === 'Y') {
        echo "Đang xóa duplicate...\n";
        CustomerMaintenanceRequest::where('request_code', 'CUST-MAINT-0004')->delete();
        echo "✓ Đã xóa duplicate!\n";
    } else {
        echo "Không xóa.\n";
    }
} else {
    echo "6. ✓ Không có duplicate CUST-MAINT-0004!\n";
}

echo "\n=== HOÀN THÀNH ===\n"; 