<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\InventoryImportController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\SoftwareController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\GoodController;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

// Thay thế routes customers cũ bằng resource controller
Route::resource('customers', CustomerController::class);
Route::get('customers/{customer}/activate', [CustomerController::class, 'activateAccount'])->name('customers.activate');
Route::get('customers/{customer}/toggle-lock', [CustomerController::class, 'toggleLock'])->name('customers.toggle-lock');

//Materials
Route::resource('materials', MaterialController::class);
Route::delete('materials/images/{id}', [MaterialController::class, 'deleteImage'])->name('materials.images.delete');
Route::get('materials-hidden', [MaterialController::class, 'showHidden'])->name('materials.hidden');
Route::get('materials-deleted', [MaterialController::class, 'showDeleted'])->name('materials.deleted');
Route::post('materials/{id}/restore', [MaterialController::class, 'restore'])->name('materials.restore');
Route::get('materials/template/download', [MaterialController::class, 'downloadTemplate'])->name('materials.template.download');
Route::post('materials/import', [MaterialController::class, 'import'])->name('materials.import');
Route::get('materials/import/results', [MaterialController::class, 'importResults'])->name('materials.import.results');
Route::get('materials/export/excel', [MaterialController::class, 'exportExcel'])->name('materials.export.excel');
Route::get('materials/export/fdf', [MaterialController::class, 'exportFDF'])->name('materials.export.fdf');

//Products
Route::resource('products', ProductController::class);
Route::get('products-hidden', [ProductController::class, 'showHidden'])->name('products.hidden');
Route::get('products-deleted', [ProductController::class, 'showDeleted'])->name('products.deleted');
Route::patch('products/{product}/restore-hidden', [ProductController::class, 'restoreHidden'])->name('products.restore-hidden');
Route::patch('products/{product}/restore-deleted', [ProductController::class, 'restoreDeleted'])->name('products.restore-deleted');

// Product export routes
Route::get('products/export/pdf', [ProductController::class, 'exportPDF'])->name('products.export.pdf');
Route::get('products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export.excel');
Route::get('products/export/fdf', [ProductController::class, 'exportFDF'])->name('products.export.fdf');

// Product import routes
Route::get('products/import/template', [ProductController::class, 'downloadTemplate'])->name('products.import.template');
Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
Route::get('products/import/results', [ProductController::class, 'importResults'])->name('products.import.results');

// API route for product inventory quantity
Route::get('/api/products/inventory', [ProductController::class, 'getInventoryQuantity']);

// API route for product search
Route::get('/api/products/search', [ProductController::class, 'searchProductsApi'])->name('products.search.api');

//Warehouses
Route::get('/warehouses/api-search', [WarehouseController::class, 'apiSearch'])->name('warehouses.api-search');
Route::resource('warehouses', WarehouseController::class);

// Warehouse hidden and deleted routes
Route::get('warehouses-hidden', [WarehouseController::class, 'showHidden'])->name('warehouses.hidden');
Route::get('warehouses-deleted', [WarehouseController::class, 'showDeleted'])->name('warehouses.deleted');
Route::patch('warehouses/{warehouse}/restore-hidden', [WarehouseController::class, 'restoreHidden'])->name('warehouses.restore-hidden');
Route::patch('warehouses/{warehouse}/restore-deleted', [WarehouseController::class, 'restoreDeleted'])->name('warehouses.restore-deleted');

// API route for warehouse inventory check
Route::get('/warehouses/{id}/check-inventory', [WarehouseController::class, 'checkInventory'])->name('warehouses.check-inventory');

// API route for warehouse materials
Route::get('/api/warehouses/{warehouseId}/materials', [WarehouseController::class, 'getMaterials']);

// API route for material inventory quantity
Route::get('/api/materials/inventory', [MaterialController::class, 'getInventoryQuantity']);

// API route for good inventory quantity
Route::get('/api/goods/inventory', [GoodController::class, 'getInventoryQuantity']);

// API route for material search
Route::get('/api/materials/search', [MaterialController::class, 'searchMaterialsApi'])->name('materials.search.api');

// API route for material images
Route::get('/api/materials/{id}/images', [MaterialController::class, 'getMaterialImages'])->name('materials.images.api');

//Warranties
Route::get('/warranties', function () {
    return view('warranties.index');
});

Route::get('/warranties/show', function () {
    return view('warranties.show');
});

Route::get('/warranties/activate', function () {
    return view('warranties.activate');
});

Route::get('/warranties/verify', function () {
    return view('warranties.verify');
});

//repair
Route::get('/repair', function () {
    return view('warranties.repair');
});

Route::get('/repair_list', function () {
    return view('warranties.repair_list');
});

Route::get('/repair_detail', function () {
    return view('warranties.repair_detail');
});

Route::get('/repair_edit', function () {
    return view('warranties.repair_edit');
});

//inventory - Dispatch Management
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [DispatchController::class, 'index'])->name('index');
    Route::get('dispatch/create', [DispatchController::class, 'create'])->name('dispatch.create');
    Route::post('dispatch', [DispatchController::class, 'store'])->name('dispatch.store');
    Route::get('dispatch/{dispatch}', [DispatchController::class, 'show'])->name('dispatch.show');
    Route::get('dispatch/{dispatch}/edit', [DispatchController::class, 'edit'])->name('dispatch.edit');
    Route::put('dispatch/{dispatch}', [DispatchController::class, 'update'])->name('dispatch.update');
    Route::post('dispatch/{dispatch}/approve', [DispatchController::class, 'approve'])->name('dispatch.approve');
    Route::post('dispatch/{dispatch}/cancel', [DispatchController::class, 'cancel'])->name('dispatch.cancel');
    Route::post('dispatch/{dispatch}/complete', [DispatchController::class, 'complete'])->name('dispatch.complete');
});

// API routes for dispatch
Route::prefix('api/dispatch')->group(function () {
    Route::get('items', [DispatchController::class, 'getAvailableItems']);
});

// API routes for dispatch
Route::get('/api/dispatch/items', [DispatchController::class, 'getAvailableItems'])->name('api.dispatch.items');

// Warranty routes
Route::prefix('warranties')->name('warranties.')->group(function () {
    Route::get('/', [WarrantyController::class, 'index'])->name('index');
    Route::get('/{warranty}', [WarrantyController::class, 'show'])->name('show');
    Route::patch('/{warranty}/status', [WarrantyController::class, 'updateStatus'])->name('update-status');
});

// Public warranty check routes
Route::get('/warranty/check/{warrantyCode}', [WarrantyController::class, 'check'])->name('warranty.check');
Route::get('/api/warranty/check', [WarrantyController::class, 'apiCheck'])->name('api.warranty.check');
Route::get('/api/dispatch/{dispatchId}/warranties', [WarrantyController::class, 'getDispatchWarranties'])->name('api.dispatch.warranties');

// Legacy routes for compatibility
Route::get('/inventory/dispatch', [DispatchController::class, 'create']);
Route::get('/inventory/dispatch_detail', function () {
    return redirect()->route('inventory.index');
});
Route::get('/inventory/dispatch_edit', function () {
    return redirect()->route('inventory.index');
});

//change_log
Route::get('/change_log', function () {
    return view('changelog.index');
});

//report
Route::get('/reports', function () {
    return view('reports.index');
});

// Thay thế routes suppliers cũ bằng resource controller
Route::resource('suppliers', SupplierController::class);

// Thay thế routes employees cũ bằng resource controller
Route::resource('employees', EmployeeController::class);
Route::patch('employees/{employee}/toggle-active', [EmployeeController::class, 'toggleActive'])->name('employees.toggle-active');

// Thay thế routes inventory-imports cũ bằng resource controller
Route::resource('inventory-imports', InventoryImportController::class);
Route::get('api/materials/{id}', [InventoryImportController::class, 'getMaterialInfo'])->name('api.material.info');

// Quản lý chuyển kho
Route::resource('warehouse-transfers', WarehouseTransferController::class);

// Quản lý phần mềm
Route::resource('software', SoftwareController::class);
Route::get('software/{software}/download', [SoftwareController::class, 'download'])->name('software.download');
Route::get('software/{software}/download-manual', [SoftwareController::class, 'downloadManual'])->name('software.download_manual');

// Quản lý kiểm thử (QA)
Route::get('/testing', function () {
    return view('testing.index');
});

Route::get('/testing/create', function () {
    return view('testing.create');
});

Route::get('/testing/{id}', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('testing.show');
})->where('id', '[0-9]+');

Route::get('/testing/{id}/edit', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('testing.edit');
})->where('id', '[0-9]+');

Route::post('/testing', function () {
    // Xử lý lưu phiếu kiểm thử mới
    return redirect('/testing');
});

Route::put('/testing/{id}', function ($id) {
    // Xử lý cập nhật phiếu kiểm thử
    return redirect('/testing/' . $id);
});

Route::delete('/testing/{id}', function ($id) {
    // Xử lý xóa phiếu kiểm thử
    return redirect('/testing');
});

// Quản lý dự án
Route::resource('projects', ProjectController::class);

// API để lấy thông tin khách hàng
Route::get('/api/customers/{id}', [CustomerController::class, 'getCustomerInfo']);

// Quản lý cho thuê
Route::resource('rentals', RentalController::class);
Route::post('/rentals/{rental}/extend', [RentalController::class, 'extend'])->name('rentals.extend');

// Quản lý phiếu yêu cầu
Route::get('/requests', function () {
    return view('requests.index');
});

// Phiếu đề xuất triển khai dự án
Route::get('/requests/project/create', function () {
    return view('requests.project.create');
});

Route::post('/requests/project', function () {
    // Xử lý tạo phiếu đề xuất triển khai dự án
    return redirect('/requests');
});

Route::get('/requests/project/{id}', function ($id) {
    return view('requests.project.show', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/project/{id}/edit', function ($id) {
    return view('requests.project.edit', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/project/{id}/preview', function ($id) {
    return view('requests.project.preview', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/project/{id}/copy', function ($id) {
    return view('requests.project.copy', ['id' => $id]);
})->where('id', '[0-9]+');

// Phiếu bảo trì dự án
Route::get('/requests/maintenance/create', function () {
    return view('requests.maintenance.create');
});

Route::post('/requests/maintenance', function () {
    // Xử lý tạo phiếu bảo trì dự án
    return redirect('/requests');
});

Route::get('/requests/maintenance/{id}', function ($id) {
    return view('requests.maintenance.show', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/maintenance/{id}/edit', function ($id) {
    return view('requests.maintenance.edit', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/maintenance/{id}/preview', function ($id) {
    return view('requests.maintenance.preview', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/maintenance/{id}/copy', function ($id) {
    return view('requests.maintenance.copy', ['id' => $id]);
})->where('id', '[0-9]+');

Route::patch('/requests/maintenance/{id}', function ($id) {
    // Xử lý cập nhật phiếu bảo trì dự án
    return redirect('/requests/maintenance/' . $id);
})->where('id', '[0-9]+');

Route::delete('/requests/maintenance/{id}', function ($id) {
    // Xử lý xóa phiếu bảo trì dự án
    return redirect('/requests');
})->where('id', '[0-9]+');

// Phiếu khách yêu cầu bảo trì
Route::get('/requests/customer-maintenance/create', function () {
    return view('requests.customer-maintenance.create');
});

Route::post('/requests/customer-maintenance', function () {
    // Xử lý tạo phiếu khách yêu cầu bảo trì
    return redirect('/requests');
});

Route::get('/requests/customer-maintenance/{id}', function ($id) {
    return view('requests.customer-maintenance.show', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/customer-maintenance/{id}/edit', function ($id) {
    return view('requests.customer-maintenance.edit', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/customer-maintenance/{id}/preview', function ($id) {
    return view('requests.customer-maintenance.preview', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/customer-maintenance/{id}/copy', function ($id) {
    return view('requests.customer-maintenance.copy', ['id' => $id]);
})->where('id', '[0-9]+');

Route::patch('/requests/customer-maintenance/{id}', function ($id) {
    // Xử lý cập nhật phiếu khách yêu cầu bảo trì
    return redirect('/requests/customer-maintenance/' . $id);
})->where('id', '[0-9]+');

Route::delete('/requests/customer-maintenance/{id}', function ($id) {
    // Xử lý xóa phiếu khách yêu cầu bảo trì
    return redirect('/requests');
})->where('id', '[0-9]+');

// Assembly routes
Route::get('/assemblies/generate-code', [AssemblyController::class, 'generateAssemblyCode'])->name('assemblies.generate-code');
Route::get('/assemblies/check-code', [AssemblyController::class, 'checkAssemblyCode'])->name('assemblies.check-code');
Route::get('/assemblies/product-materials/{productId}', [AssemblyController::class, 'getProductMaterials'])->name('assemblies.product-materials');
Route::get('/assemblies/employees', [AssemblyController::class, 'getEmployees'])->name('assemblies.employees');
Route::post('/assemblies/warehouse-stock/{warehouseId}', [AssemblyController::class, 'getWarehouseMaterialsStock'])->name('assemblies.warehouse-stock');
Route::resource('assemblies', AssemblyController::class);

// API route for checking serial duplicates
Route::post('/api/check-serial', [AssemblyController::class, 'checkSerial'])->name('api.check-serial');

// API route for product components
Route::get('/api/products/{id}/components', [ProductController::class, 'getComponents'])->name('api.products.components');

// Thêm phần routes phân quyền
// Routes cho nhóm quyền (roles)
Route::resource('roles', RoleController::class);
Route::patch('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggleStatus');

// Routes cho danh sách quyền (permissions)
Route::resource('permissions', PermissionController::class);

// Routes cho nhật ký người dùng (user logs)
Route::get('user-logs', [UserLogController::class, 'index'])->name('user-logs.index');
Route::get('user-logs/{id}', [UserLogController::class, 'show'])->name('user-logs.show');
Route::get('user-logs-export', [UserLogController::class, 'export'])->name('user-logs.export');

Route::get('/requests/project/create', function () {
    return view('requests.project.create');
});

Route::get('/requests/maintenance/create', function () {
    return view('requests.maintenance.create');
});

// Temporary debug route
Route::get('/debug/materials', function () {
    try {
        $columns = Schema::getColumnListing('materials');
        $sample = \App\Models\Material::first();
        return [
            'columns' => $columns,
            'sample' => $sample,
        ];
    } catch (\Exception $e) {
        return [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
});

// Routes for goods
Route::resource('goods', GoodController::class);
Route::get('/goodshidden', [GoodController::class, 'showHidden'])->name('goodshidden');
Route::get('/goodsdeleted', [GoodController::class, 'showDeleted'])->name('goodsdeleted');
Route::post('/goods/restore/{id}', [GoodController::class, 'restore'])->name('goods.restore');
Route::get('/api/goods/{id}/images', [GoodController::class, 'getGoodImages']);
Route::delete('/api/goods/images/{id}', [GoodController::class, 'deleteImage']);
Route::get('/goods/export/excel', [GoodController::class, 'exportExcel'])->name('goods.export.excel');
Route::get('/goods/export/fdf', [GoodController::class, 'exportFDF'])->name('goods.export.fdf');
Route::get('/goods/template/download', [GoodController::class, 'downloadTemplate'])->name('goods.template.download');
Route::post('/goods/import', [GoodController::class, 'import'])->name('goods.import');
Route::get('/goods/import/results', [GoodController::class, 'showImportResults'])->name('goods.import.results');
Route::get('/api/goods/search', [GoodController::class, 'apiSearch'])->name('goods.api.search');

// Thêm route cho API kiểm tra tồn kho
Route::get('/warehouse-transfers/check-inventory', [WarehouseTransferController::class, 'checkInventory'])->name('warehouse-transfers.check-inventory');
Route::post('/warehouse-transfers/check-inventory', [WarehouseTransferController::class, 'checkInventory'])->name('warehouse-transfers.check-inventory.post');