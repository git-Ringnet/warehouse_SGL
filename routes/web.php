<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;

use App\Http\Controllers\EmployeeController;

use App\Http\Controllers\AssemblyController;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserLogController;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::resource('assemble', AssemblyController::class);



// Thay thế routes customers cũ bằng resource controller
Route::resource('customers', CustomerController::class);

//Materials
Route::resource('materials', MaterialController::class);

//Products
Route::resource('products', ProductController::class);

//Warehouses
Route::resource('warehouses', WarehouseController::class);

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
Route::get('/report', function () {
    return view('reports.index');
});

Route::get('/report/inventory_import', function () {
    return view('reports.inventory_import');
});

Route::get('/report/testing_verification', function () {
    return view('reports.testing_verification');
});

// Các báo cáo mới
Route::get('/report/material_export_z755', function () {
    return view('reports.material_export_z755');
});

Route::get('/report/finished_product_by_material', function () {
    return view('reports.finished_product_by_material');
});

Route::get('/report/defective_modules', function () {
    return view('reports.defective_modules');
});

Route::get('/report/finished_product_import', function () {
    return view('reports.finished_product_import');
});

Route::get('/report/product_export_by_project', function () {
    return view('reports.product_export_by_project');
});

Route::get('/report/maintenance_history', function () {
    return view('reports.maintenance_history');
});

Route::get('/report/warranty_repair_success', function () {
    return view('reports.warranty_repair_success');
});

Route::get('/report/warranty_product_return', function () {
    return view('reports.warranty_product_return');
});

//tu day tro xuong day
// Thay thế routes suppliers cũ bằng resource controller
Route::resource('suppliers', SupplierController::class);

// Thay thế routes employees cũ bằng resource controller
Route::resource('employees', EmployeeController::class);

// Quản lý nhập kho
Route::get('/inventory-imports', function () {
    return view('inventory-imports.index');
});

Route::get('/inventory-imports/create', function () {
    return view('inventory-imports.create');
});

Route::get('/inventory-imports/{id}', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('inventory-imports.show');
})->where('id', '[0-9]+');

Route::get('/inventory-imports/{id}/edit', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('inventory-imports.edit');
})->where('id', '[0-9]+');

Route::post('/inventory-imports', function () {
    // Xử lý lưu phiếu nhập kho mới
    return redirect('/inventory-imports');
});

Route::put('/inventory-imports/{id}', function ($id) {
    // Xử lý cập nhật phiếu nhập kho
    return redirect('/inventory-imports/' . $id);
});

Route::delete('/inventory-imports/{id}', function ($id) {
    // Xử lý xóa phiếu nhập kho
    return redirect('/inventory-imports');
});


// Quản lý chuyển kho
Route::get('/warehouse-transfers', function () {
    return view('warehouse-transfers.index');
});

Route::get('/warehouse-transfers/create', function () {
    return view('warehouse-transfers.create');
});

Route::get('/warehouse-transfers/{id}', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('warehouse-transfers.show');
})->where('id', '[0-9]+');

Route::get('/warehouse-transfers/{id}/edit', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('warehouse-transfers.edit');
})->where('id', '[0-9]+');

Route::post('/warehouse-transfers', function () {
    // Xử lý lưu phiếu chuyển kho mới
    return redirect('/warehouse-transfers');
});

Route::put('/warehouse-transfers/{id}', function ($id) {
    // Xử lý cập nhật phiếu chuyển kho
    return redirect('/warehouse-transfers/' . $id);
});

Route::delete('/warehouse-transfers/{id}', function ($id) {
    // Xử lý xóa phiếu chuyển kho
    return redirect('/warehouse-transfers');
});

// Quản lý phần mềm
Route::get('/software', function () {
    return view('software.index');
});

Route::get('/software/create', function () {
    return view('software.create');
});

Route::get('/software/{id}', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('software.show');
})->where('id', '[0-9]+');

Route::get('/software/{id}/edit', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('software.edit');
})->where('id', '[0-9]+');

Route::post('/software', function () {
    // Xử lý lưu phần mềm mới
    return redirect('/software');
});

Route::put('/software/{id}', function ($id) {
    // Xử lý cập nhật phần mềm
    return redirect('/software/' . $id);
});

Route::delete('/software/{id}', function ($id) {
    // Xử lý xóa phần mềm
    return redirect('/software');
});

// Tải xuống phần mềm
Route::get('/software/{id}/download', function ($id) {
    // Trong thực tế, sẽ xử lý tải xuống file phần mềm tại đây
    // return response()->download($filePath);
    return redirect('/software/' . $id);
})->where('id', '[0-9]+');

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
Route::get('/projects', function () {
    return view('projects.index');
});

Route::get('/projects/create', function () {
    return view('projects.create');
});

Route::get('/projects/{id}', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('projects.show');
})->where('id', '[0-9]+');

Route::get('/projects/{id}/edit', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('projects.edit');
})->where('id', '[0-9]+');

Route::post('/projects', function () {
    // Xử lý lưu dự án mới
    return redirect('/projects');
});

Route::put('/projects/{id}', function ($id) {
    // Xử lý cập nhật dự án
    return redirect('/projects/' . $id);
});

Route::delete('/projects/{id}', function ($id) {
    // Xử lý xóa dự án
    return redirect('/projects');
});

// Quản lý cho thuê
Route::get('/rentals', function () {
    return view('rentals.index');
});

Route::get('/rentals/create', function () {
    return view('rentals.create');
});

Route::get('/rentals/{id}', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('rentals.show');
})->where('id', '[0-9]+');

Route::get('/rentals/{id}/edit', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('rentals.edit');
})->where('id', '[0-9]+');

Route::post('/rentals', function () {
    // Xử lý lưu phiếu cho thuê mới
    return redirect('/rentals');
});

Route::put('/rentals/{id}', function ($id) {
    // Xử lý cập nhật phiếu cho thuê
    return redirect('/rentals/' . $id);
});

Route::delete('/rentals/{id}', function ($id) {
    // Xử lý xóa phiếu cho thuê
    return redirect('/rentals');
});

// Gia hạn phiếu cho thuê
Route::post('/rentals/{id}/extend', function ($id) {
    // Xử lý gia hạn phiếu cho thuê
    return redirect('/rentals/' . $id);
});

// Quản lý phiếu yêu cầu
Route::get('/requests', function () {
    return view('requests.index');
});

// Phiếu đề xuất triển khai dự án
Route::get('/requests/project', function () {
    return view('requests.project');
});

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

// Phiếu đề xuất bảo trì dự án
Route::get('/requests/maintenance', function () {
    return view('requests.maintenance');
});

Route::get('/requests/maintenance/create', function () {
    return view('requests.maintenance.create');
});

Route::post('/requests/maintenance', function () {
    // Xử lý tạo phiếu đề xuất bảo trì dự án
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

// Phiếu đề xuất nhập thêm linh kiện
Route::get('/requests/components', function () {
    return view('requests.components');
});

Route::get('/requests/components/create', function () {
    return view('requests.components.create');
});

Route::post('/requests/components', function () {
    // Xử lý tạo phiếu đề xuất nhập thêm linh kiện
    return redirect('/requests');
});

Route::get('/requests/components/{id}', function ($id) {
    return view('requests.components.show', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/components/{id}/edit', function ($id) {
    return view('requests.components.edit', ['id' => $id]);
})->where('id', '[0-9]+');

Route::get('/requests/components/{id}/preview', function ($id) {
    return view('requests.components.preview', ['id' => $id]);
})->where('id', '[0-9]+');

// Assembly routes
Route::resource('assemblies', AssemblyController::class);
Route::get('/assemblies/search-materials', [AssemblyController::class, 'searchMaterials'])->name('assemblies.search-materials');

// Route for material search in assemblies
Route::get('/materials/search', [App\Http\Controllers\AssemblyController::class, 'searchMaterials'])->name('materials.search');

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

// Thêm phần routes phân quyền
// Routes cho nhóm quyền (roles)
Route::resource('roles', RoleController::class);
Route::put('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');

// Routes cho danh sách quyền (permissions)
Route::resource('permissions', PermissionController::class);

// Routes cho nhật ký người dùng (user logs)
Route::get('user-logs', [UserLogController::class, 'index'])->name('user-logs.index');
Route::get('user-logs/{id}', [UserLogController::class, 'show'])->name('user-logs.show');
Route::get('user-logs-export', [UserLogController::class, 'export'])->name('user-logs.export');


