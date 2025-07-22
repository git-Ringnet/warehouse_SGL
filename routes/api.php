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
Route::get('dispatch/items/all', [App\Http\Controllers\InventoryController::class, 'getAvailableItems']);
Route::get('dispatch/rentals', [App\Http\Controllers\InventoryController::class, 'getRentalContracts']);
Route::get('dispatch/item-serials', [App\Http\Controllers\InventoryController::class, 'getItemSerials']);

// Thêm các route cho device codes API
Route::get('device-codes/{dispatch_id}', [DeviceCodeController::class, 'getByDispatch']);
Route::post('device-codes/save', [DeviceCodeController::class, 'saveDeviceCodes']);
Route::post('device-codes/import', [DeviceCodeController::class, 'importFromExcel']);
Route::get('device-info/{mainSerial}', [App\Http\Controllers\InventoryController::class, 'getDeviceInfo'])->name('api.device-info.serial');

// Product API routes
Route::post('/products/create-from-assembly', [App\Http\Controllers\Api\ProductController::class, 'createFromAssembly'])
    ->middleware(['web', \App\Http\Middleware\CheckPermissionMiddleware::class . ':products.create']); 
    
// Lấy hình ảnh sản phẩm
Route::get('/products/{id}/images', [App\Http\Controllers\ProductController::class, 'getProductImages']); 


// Route cho phiếu nhập kho
Route::prefix('inventory-imports')->group(function () {
    Route::get('generate-code', [InventoryImportController::class, 'generateCode']);
}); 

Route::get('/materials/{material}/warehouses', [App\Http\Controllers\Api\MaterialController::class, 'getAvailableWarehouses']);
Route::get('/materials/{material}/serials/{warehouse}', [App\Http\Controllers\Api\MaterialController::class, 'getAvailableSerials']); 
