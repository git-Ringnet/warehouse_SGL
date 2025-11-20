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

// API Authentication routes
Route::post('/login', [App\Http\Controllers\AuthController::class, 'apiLogin'])->name('api.login');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'apiLogout'])->name('api.logout');
    Route::get('/user', [App\Http\Controllers\AuthController::class, 'apiUser'])->name('api.user');
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
// Tra cứu thiết bị trong kho - Không cần API này
// Route::get('device-info/{mainSerial}', [App\Http\Controllers\InventoryController::class, 'getDeviceInfo'])->name('api.device-info.serial');

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
// Tra cứu thiết bị trong kho - Không cần API này
// Route::get('/inventory/{type}/{id}/{warehouseId}', [App\Http\Controllers\TestingController::class, 'getInventoryInfo']);
Route::get('/testing/serials', [App\Http\Controllers\TestingController::class, 'getAvailableSerials']);

// Maintenance Request API routes (Yêu cầu hỗ trợ bảo hành/sửa chữa)
Route::middleware('auth:sanctum')->prefix('maintenance-requests')->group(function () {
	Route::get('/all', [App\Http\Controllers\MaintenanceRequestController::class, 'apiGetAllProject'])->name('api.maintenance-requests.all'); // Lấy TẤT CẢ (không lọc, không phân trang)
	Route::get('/', [App\Http\Controllers\MaintenanceRequestController::class, 'apiIndex'])->name('api.maintenance-requests.index'); // Tương thích ngược
	Route::get('/project', [App\Http\Controllers\MaintenanceRequestController::class, 'apiIndexProject'])->name('api.maintenance-requests.project'); // API riêng cho bảo trì dự án (có lọc, phân trang)
	Route::get('/{id}', [App\Http\Controllers\MaintenanceRequestController::class, 'apiShow'])->name('api.maintenance-requests.show'); // Lấy chi tiết một phiếu bảo trì theo ID
	Route::post('/', [App\Http\Controllers\MaintenanceRequestController::class, 'apiStore'])->name('api.maintenance-requests.store');
	// Cập nhật yêu cầu bảo hành/sửa chữa: Không cần API này
	// Route::patch('/{id}', [App\Http\Controllers\MaintenanceRequestController::class, 'apiUpdate'])->name('api.maintenance-requests.update');
	// Route::put('/{id}', [App\Http\Controllers\MaintenanceRequestController::class, 'apiUpdate']);
	// Liệt kê thiết bị theo dự án/cho thuê cho API bên ngoài
	Route::post('/devices', [App\Http\Controllers\MaintenanceRequestController::class, 'getDevicesApi'])->name('api.maintenance-requests.devices');
	// Lấy danh sách serial thiết bị dựa trên device_code và project_id
	Route::post('/device-serials', [App\Http\Controllers\MaintenanceRequestController::class, 'getDeviceSerials'])->name('api.maintenance-requests.device-serials');
	// Lấy danh sách dự án/phiếu cho thuê dựa trên project_type và thông tin khách hàng
	Route::post('/projects-or-rentals', [App\Http\Controllers\MaintenanceRequestController::class, 'getProjectsOrRentals'])->name('api.maintenance-requests.projects-or-rentals');
});

// Customer Maintenance Request API routes (Khách yêu cầu bảo trì)
Route::middleware('auth:sanctum')->prefix('customer-maintenance-requests')->group(function () {
	Route::get('/all', [App\Http\Controllers\CustomerMaintenanceRequestController::class, 'apiGetAll'])->name('api.customer-maintenance-requests.all'); // Lấy TẤT CẢ (không lọc, không phân trang)
	Route::get('/', [App\Http\Controllers\CustomerMaintenanceRequestController::class, 'apiIndex'])->name('api.customer-maintenance-requests.index'); // Có lọc và phân trang
	Route::post('/', [App\Http\Controllers\CustomerMaintenanceRequestController::class, 'apiStore'])->name('api.customer-maintenance-requests.store'); // Tạo phiếu khách yêu cầu bảo trì
	Route::patch('/{id}', [App\Http\Controllers\CustomerMaintenanceRequestController::class, 'apiUpdate'])->name('api.customer-maintenance-requests.update'); // Cập nhật phiếu khách yêu cầu bảo trì
	Route::put('/{id}', [App\Http\Controllers\CustomerMaintenanceRequestController::class, 'apiUpdate']);
});

// Repair API routes (token protected)
Route::middleware('auth:sanctum')->prefix('repairs')->group(function () {
    Route::get('/search-warranty', [App\Http\Controllers\RepairController::class, 'searchWarrantyApi'])->name('api.repairs.search-warranty');
    Route::get('/search-warehouse-devices', [App\Http\Controllers\RepairController::class, 'searchWarehouseDevices'])->name('api.repairs.search-warehouse-devices');
    Route::get('/repair-history', [App\Http\Controllers\RepairController::class, 'getRepairHistory'])->name('api.repairs.repair-history');
});