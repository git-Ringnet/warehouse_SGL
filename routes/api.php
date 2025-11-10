<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RequestExportController;
use App\Http\Controllers\DeviceCodeController;
use App\Http\Controllers\InventoryImportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/export-requests', [RequestExportController::class, 'index']);

// Thêm các route cho dispatch API
Route::get('dispatch/items/all', [App\Http\Controllers\DispatchController::class, 'getAllAvailableItems']);
Route::get('dispatch/rentals', [App\Http\Controllers\DispatchController::class, 'getRentals']);
Route::get('dispatch/item-serials', [App\Http\Controllers\DispatchController::class, 'getItemSerials']);

// Thêm các route cho device codes API
Route::post('device-codes/save', [DeviceCodeController::class, 'saveDeviceCodes']);
Route::post('device-codes/import', [DeviceCodeController::class, 'importFromExcel']);
Route::post('device-codes/sync-serial-numbers', [DeviceCodeController::class, 'syncSerialNumbers']);
Route::get('device-info/{mainSerial}', [App\Http\Controllers\InventoryController::class, 'getDeviceInfo'])->name('api.device-info.serial');

// Product API routes
Route::post('/products/create-from-assembly', [App\Http\Controllers\Api\ProductController::class, 'createFromAssembly'])
    ->middleware(['web', \App\Http\Middleware\CheckPermissionMiddleware::class . ':products.create']); 
    
// Lấy hình ảnh sản phẩm
Route::get('/products/{id}/images', [App\Http\Controllers\ProductController::class, 'getProductImages']); 

// Add this route in the api.php file
Route::get('/products/materials-count', [App\Http\Controllers\Api\ProductController::class, 'getMaterialsCount']);
Route::post('/products/materials-count', [App\Http\Controllers\Api\ProductController::class, 'getMaterialsCount']);
Route::get('/products/device-code-materials', [App\Http\Controllers\Api\ProductController::class, 'getDeviceCodeMaterials']);

// Assembly API routes
Route::post('/assembly/serial-components', [App\Http\Controllers\AssemblyController::class, 'getSerialComponents']);

// Resolve assembly/product_unit for a product in a project context
Route::get('/dispatch/resolve-assembly', [App\Http\Controllers\DispatchController::class, 'resolveAssemblyForProduct']);

// Route cho phiếu nhập kho
Route::prefix('inventory-imports')->group(function () {
    Route::get('generate-code', [InventoryImportController::class, 'generateCode']);
}); 

Route::get('/materials/{material}/warehouses', [App\Http\Controllers\Api\MaterialController::class, 'getAvailableWarehouses']);
Route::get('/materials/{material}/serials/{warehouse}', [App\Http\Controllers\Api\MaterialController::class, 'getAvailableSerials']);
Route::post('/materials/batch-serials', [App\Http\Controllers\Api\MaterialController::class, 'getBatchSerials']); 



// Warehouse Transfers
Route::get('/warehouse-transfers/generate-code', [App\Http\Controllers\WarehouseTransferController::class, 'generateCode']);
Route::get('/warehouse-transfers/get-serials', [App\Http\Controllers\WarehouseTransferController::class, 'getAvailableSerials']);
Route::get('/warehouse-transfers/check-serial-data', [App\Http\Controllers\WarehouseTransferController::class, 'checkSerialData']);
Route::get('/warehouse-transfers/get-items-by-warehouse', [App\Http\Controllers\WarehouseTransferController::class, 'getItemsByWarehouse']); 

// API kiểm tra tồn kho
Route::get('/check-stock/{itemType}/{itemId}', [App\Http\Controllers\Api\StockController::class, 'checkStock']); 

// Device codes route
Route::get('/device-codes/{dispatchId}', [App\Http\Controllers\Api\DeviceCodeController::class, 'getDeviceCodes']); 

// Testing API routes
Route::get('/testing/check-code', [App\Http\Controllers\TestingController::class, 'checkTestCode']);
Route::get('/testing/materials/{type}', [App\Http\Controllers\TestingController::class, 'getMaterialsByType']);
Route::get('/inventory/{type}/{id}/{warehouseId}', [App\Http\Controllers\TestingController::class, 'getInventoryInfo']);
Route::get('/testing/serials', [App\Http\Controllers\TestingController::class, 'getAvailableSerials']);

// Maintenance Request API routes (Yêu cầu hỗ trợ bảo hành/sửa chữa)
Route::prefix('maintenance-requests')->group(function () {
	Route::post('/', [App\Http\Controllers\MaintenanceRequestController::class, 'apiStore'])->name('api.maintenance-requests.store');
	Route::patch('/{id}', [App\Http\Controllers\MaintenanceRequestController::class, 'apiUpdate'])->name('api.maintenance-requests.update');
	Route::put('/{id}', [App\Http\Controllers\MaintenanceRequestController::class, 'apiUpdate']);
	// Liệt kê thiết bị theo dự án/cho thuê để chọn như ở view
	Route::post('/devices', [App\Http\Controllers\MaintenanceRequestController::class, 'getDevices'])->name('api.maintenance-requests.devices');
});