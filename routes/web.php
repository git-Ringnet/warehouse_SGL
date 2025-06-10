<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AssemblyController;
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

//Products
Route::resource('products', ProductController::class);

//Warehouses
Route::resource('warehouses', WarehouseController::class);

// API route for warehouse materials
Route::get('/api/warehouses/{warehouseId}/materials', [WarehouseController::class, 'getMaterials']);

// API route for material inventory quantity
Route::get('/api/materials/inventory', [MaterialController::class, 'getInventoryQuantity']);

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

//inventory
Route::get('/inventory', function () {
    return view('inventory.index');
});

Route::get('/inventory/dispatch', function () {
    return view('inventory.dispatch');
});

Route::get('/inventory/dispatch_detail', function () {
    return view('inventory.dispatch_detail');
});

Route::get('/inventory/dispatch_edit', function () {
    return view('inventory.dispatch_edit');
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
Route::resource('assemblies', AssemblyController::class);

// Route for material search in assemblies
Route::get('/materials/search', [AssemblyController::class, 'searchMaterials'])->name('materials.search');

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

// Goods (Hàng hóa)

// Route::resource('goods', GoodController::class);
Route::get('/goods', [GoodController::class, 'index'])->name('goods.index');
Route::get('/goods/create', [GoodController::class, 'create'])->name('goods.create');
// Route::get('/goods/{id}', [GoodController::class, 'show'])->name('goods.show');
// Route::get('/goods/{id}/edit', [GoodController::class, 'edit'])->name('goods.edit');
Route::get('/goods/show', [GoodController::class, 'show'])->name('goods.show');
Route::get('/goods/edit', [GoodController::class, 'edit'])->name('goods.edit');

Route::delete('goods/images/{id}', [GoodController::class, 'deleteImage'])->name('goods.images.delete');
Route::get('/api/goods/{id}/images', [GoodController::class, 'getGoodImages'])->name('api.goods.images');


