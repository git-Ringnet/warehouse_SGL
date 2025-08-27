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
use App\Http\Controllers\TestingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquipmentServiceController;
use App\Http\Controllers\ChangeLogController;
use App\Http\Controllers\ProjectRequestController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\CustomerMaintenanceRequestController;
use App\Http\Controllers\Api\RequestExportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Middleware\CheckUserType;
use App\Http\Middleware\CustomerAccessMiddleware;
use App\Http\Middleware\CheckPermissionMiddleware;
use App\Http\Controllers\DeviceCodeController;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Routes chung cho cả nhân viên và khách hàng
Route::middleware(['auth:web,customer', CheckUserType::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update-password', [AuthController::class, 'updatePassword'])->name('profile.update_password');
    
    // Redirect root to dashboard
    Route::get('/', function () {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }
        
        if (Auth::guard('web')->check()) {
            $employee = Auth::guard('web')->user();
            if ($employee->role === 'admin' || 
                ($employee->roleGroup && $employee->roleGroup->hasPermission('reports.overview'))) {
                return redirect()->route('dashboard');
            }
            // Nếu nhân viên chưa có quyền, chuyển đến trang thông báo
            return view('errors.no-permission');
        }
        
        return redirect()->route('login');
    });
});

// Routes cho nhân viên
Route::middleware(['auth:web', CheckUserType::class])->group(function () {
    // Dashboard cho nhân viên
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware(CheckPermissionMiddleware::class . ':reports.overview');
        
    // Các route khác cho nhân viên...
});

// Routes cho khách hàng
Route::middleware(['auth:customer', CheckUserType::class])->group(function () {
    // Dashboard cho khách hàng
    Route::get('/customer/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
});

// Routes cho phiếu yêu cầu bảo trì của khách hàng - ĐÃ XÓA DO TRÙNG LẶP
// Route::prefix('customer/maintenance')->name('customer-maintenance.')->middleware(['auth:customer,web', CheckUserType::class])->group(function () {
//     Route::get('/create', [CustomerMaintenanceRequestController::class, 'create'])
//         ->name('create')
//         ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.create');
//     Route::post('/', [CustomerMaintenanceRequestController::class, 'store'])
//         ->name('store')
//         ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.create');
//     Route::get('/{id}', [CustomerMaintenanceRequestController::class, 'show'])
//         ->name('show')
//         ->where('id', '[0-9]+')
//         ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.view');
//     Route::get('/{id}/edit', [CustomerMaintenanceRequestController::class, 'edit'])
//         ->name('edit')
//         ->where('id', '[0-9]+')
//         ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.edit');
//     Route::patch('/{id}', [CustomerMaintenanceRequestController::class, 'update'])
//         ->name('update')
//         ->where('id', '[0-9]+')
//         ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.edit');
//     Route::delete('/{id}', [CustomerMaintenanceRequestController::class, 'destroy'])
//         ->name('destroy')
//         ->where('id', '[0-9]+')
//         ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.delete');
//     Route::post('/{id}/approve', [CustomerMaintenanceRequestController::class, 'approve'])
//         ->name('approve')
//         ->where('id', '[0-9]+')
//         ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.approve');
//     Route::post('/{id}/reject', [CustomerMaintenanceRequestController::class, 'reject'])
//         ->name('reject')
//         ->where('id', '[0-9]+')
//         ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.reject');
// });

Route::middleware(['auth:web,customer', \App\Http\Middleware\CheckUserType::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update-password', [AuthController::class, 'updatePassword'])->name('profile.update_password');

    // Redirect root to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard routes
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':reports.overview');
    Route::get('/dashboard/statistics', [App\Http\Controllers\DashboardController::class, 'getStatistics'])->name('dashboard.statistics')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':reports.overview');
    Route::get('/dashboard/inventory-overview-chart', [App\Http\Controllers\DashboardController::class, 'getInventoryOverviewChart'])->name('dashboard.inventory-overview-chart')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':reports.overview');
    Route::get('/dashboard/inventory-categories-chart', [App\Http\Controllers\DashboardController::class, 'getInventoryCategoriesChart'])->name('dashboard.inventory-categories-chart')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':reports.overview');
    Route::get('/dashboard/warehouse-distribution-chart', [App\Http\Controllers\DashboardController::class, 'getWarehouseDistributionChart'])->name('dashboard.warehouse-distribution-chart')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':reports.overview');
    Route::get('/dashboard/project-growth-chart', [App\Http\Controllers\DashboardController::class, 'getProjectGrowthChart'])->name('dashboard.project-growth-chart')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':reports.overview');
    Route::get('/dashboard/search', [App\Http\Controllers\DashboardController::class, 'search'])->name('dashboard.search')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':reports.overview');
    Route::post('/dashboard/search', [App\Http\Controllers\DashboardController::class, 'search'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':reports.overview');

    // Thay thế routes customers cũ bằng resource controller
    Route::group(['middleware' => \App\Http\Middleware\CheckPermissionMiddleware::class . ':customers.view'], function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    });

    // Routes cụ thể phải đặt TRƯỚC routes có parameter
    Route::group(['middleware' => \App\Http\Middleware\CheckPermissionMiddleware::class . ':customers.create'], function () {
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    });
    
    // Export routes for customers
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export')
        ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':customers.export');

    Route::group(['middleware' => \App\Http\Middleware\CheckPermissionMiddleware::class . ':customers.edit'], function () {
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::patch('/customers/{customer}', [CustomerController::class, 'update']);
    });

    Route::group(['middleware' => \App\Http\Middleware\CheckPermissionMiddleware::class . ':customers.view_detail'], function () {
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    });

    Route::group(['middleware' => \App\Http\Middleware\CheckPermissionMiddleware::class . ':customers.delete'], function () {
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    Route::get('customers/{customer}/activate', [CustomerController::class, 'activateAccount'])
        ->name('customers.activate')
        ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':customers.manage');
    Route::get('customers/{customer}/toggle-lock', [CustomerController::class, 'toggleLock'])
        ->name('customers.toggle-lock')
        ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':customers.manage');

    // ChangeLog export routes
        Route::get('/changelogs/export/excel', [ChangeLogController::class, 'exportExcel'])->name('changelogs.export.excel')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':changelogs.export');
        Route::get('/changelogs/export/pdf', [ChangeLogController::class, 'exportPDF'])->name('changelogs.export.pdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':changelogs.export');

        //Materials routes với middleware bảo vệ từng quyền cụ thể
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.view');
    Route::get('/materials/create', [MaterialController::class, 'create'])->name('materials.create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.create');
    Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.create');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.view_detail');
    Route::get('/materials/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.edit');
    Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.edit');
    Route::patch('/materials/{material}', [MaterialController::class, 'update'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.edit');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.delete');

    // Materials export routes
    Route::get('materials/export/excel', [MaterialController::class, 'exportExcel'])->name('materials.export.excel')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.export');
    Route::get('materials/export/pdf', [MaterialController::class, 'exportPDF'])->name('materials.export.pdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.export');
    Route::get('materials/export/fdf', [MaterialController::class, 'exportFDF'])->name('materials.export.fdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.export');

    // Materials image management
    Route::delete('materials/images/{id}', [MaterialController::class, 'deleteImage'])->name('materials.images.delete')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.edit');
    Route::get('materials/{id}/history-ajax', [MaterialController::class, 'historyAjax'])->name('materials.historyAjax')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.view_detail');

    // Materials import routes
    Route::get('materials/template/download', [MaterialController::class, 'downloadTemplate'])->name('materials.template.download')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.create');
    Route::post('materials/import', [MaterialController::class, 'import'])->name('materials.import')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.create');
    Route::get('materials/import/results', [MaterialController::class, 'importResults'])->name('materials.import.results')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.create');

    // Materials hidden/deleted management
    Route::get('materials-hidden', [MaterialController::class, 'showHidden'])->name('materials.hidden')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.view');
    Route::get('materials-deleted', [MaterialController::class, 'showDeleted'])->name('materials.deleted')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.view');
    Route::post('materials/{id}/restore', [MaterialController::class, 'restore'])->name('materials.restore')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':materials.edit');

    //Products routes với middleware bảo vệ từng quyền cụ thể
    Route::get('/products', [ProductController::class, 'index'])->name('products.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.view');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.create');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.view_detail');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.edit');
    Route::patch('/products/{product}', [ProductController::class, 'update'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.edit');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.delete');

    Route::get('products-hidden', [ProductController::class, 'showHidden'])->name('products.hidden')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.view');
    Route::get('products-deleted', [ProductController::class, 'showDeleted'])->name('products.deleted')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.view');
    Route::patch('products/{product}/restore-hidden', [ProductController::class, 'restoreHidden'])->name('products.restore-hidden')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.edit');
    Route::patch('products/{product}/restore-deleted', [ProductController::class, 'restoreDeleted'])->name('products.restore-deleted')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.edit');

    // Product export routes
    Route::get('products/export/pdf', [ProductController::class, 'exportPDF'])->name('products.export.pdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.export');
    Route::get('products/export/excel', [ProductController::class, 'exportExcel'])->name('products.export.excel')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.export');
    Route::get('products/export/fdf', [ProductController::class, 'exportFDF'])->name('products.export.fdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.export');

    // Product import routes
    Route::get('products/import/template', [ProductController::class, 'downloadTemplate'])->name('products.import.template')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.create');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.create');
    Route::get('products/import/results', [ProductController::class, 'importResults'])->name('products.import.results')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.create');

    // API route for product inventory quantity
    Route::get('/api/products/inventory', [ProductController::class, 'getInventoryQuantity']);

    //Warehouses routes với middleware bảo vệ từng quyền cụ thể
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.view');
    Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.create');
    Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.create');
    
    // API route for warehouse search - phải đặt trước các route có parameter
    Route::get('/warehouses/api-search', [WarehouseController::class, 'apiSearch'])->middleware(['auth', \App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.view']);
    
    Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.view_detail');
    Route::get('/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.edit');
    Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.edit');
    Route::patch('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.edit');
    Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.delete');

    // Warehouse hidden and deleted routes
    Route::get('warehouses-hidden', [WarehouseController::class, 'showHidden'])->name('warehouses.hidden')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.view');
    Route::get('warehouses-deleted', [WarehouseController::class, 'showDeleted'])->name('warehouses.deleted')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.view');
    Route::patch('warehouses/{warehouse}/restore-hidden', [WarehouseController::class, 'restoreHidden'])->name('warehouses.restore-hidden')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.edit');
    Route::patch('warehouses/{warehouse}/restore-deleted', [WarehouseController::class, 'restoreDeleted'])->name('warehouses.restore-deleted')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.edit');

    // API route for warehouse inventory check
    Route::get('/warehouses/{id}/check-inventory', [WarehouseController::class, 'checkInventory'])->name('warehouses.check-inventory')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.view_detail');

    // API route for warehouse materials
    Route::get('/api/warehouses/{warehouseId}/materials', [WarehouseController::class, 'getMaterials'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.view_detail');

    // API route for material inventory quantity
    Route::get('/api/materials/inventory', [MaterialController::class, 'getInventoryQuantity']);

    // API route for good inventory quantity
    Route::get('/api/goods/inventory', [GoodController::class, 'getInventoryQuantity']);

    // API route for material search
    Route::get('/api/materials/search', [MaterialController::class, 'searchMaterialsApi'])->name('materials.search.api');

    // API route for material images
    Route::get('/api/materials/{id}/images', [MaterialController::class, 'getMaterialImages'])->name('materials.images.api');

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/latest', [NotificationController::class, 'getLatest'])->name('latest');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');

        // Test routes
        Route::get('/test', [NotificationController::class, 'showTestPage'])->name('test');
        Route::post('/create-test', [NotificationController::class, 'createTestNotification'])->name('create-test');
        Route::post('/create-assembly-test', [NotificationController::class, 'createAssemblyTestNotification'])->name('create-assembly-test');
        Route::post('/create-testing-test', [NotificationController::class, 'createTestingTestNotification'])->name('create-testing-test');
        Route::post('/create-dispatch-test', [NotificationController::class, 'createDispatchTestNotification'])->name('create-dispatch-test');
        Route::post('/create-project-test', [NotificationController::class, 'createProjectTestNotification'])->name('create-project-test');
        Route::post('/create-project-expiry-test', [NotificationController::class, 'createProjectExpiryTestNotification'])->name('create-project-expiry-test');
    });

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

    //repair - Repair Management
    Route::prefix('repairs')->name('repairs.')->group(function () {
        Route::get('/', [App\Http\Controllers\RepairController::class, 'index'])->name('index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.view');
        Route::get('/create', [App\Http\Controllers\RepairController::class, 'create'])->name('create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.create');
        Route::post('/', [App\Http\Controllers\RepairController::class, 'store'])->name('store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.create');
        Route::get('/{repair}', [App\Http\Controllers\RepairController::class, 'show'])->name('show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.view_detail');
        Route::get('/{repair}/edit', [App\Http\Controllers\RepairController::class, 'edit'])->name('edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.edit');
        Route::put('/{repair}', [App\Http\Controllers\RepairController::class, 'update'])->name('update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.edit');
        Route::delete('/{repair}', [App\Http\Controllers\RepairController::class, 'destroy'])->name('destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.delete');
    });

    // API routes for repairs
    Route::prefix('api/repairs')->group(function () {
        Route::get('search-warranty', [App\Http\Controllers\RepairController::class, 'searchWarranty'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.view');
        Route::get('search-warehouse-devices', [App\Http\Controllers\RepairController::class, 'searchWarehouseDevices'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.view');
        Route::get('device-materials', [App\Http\Controllers\RepairController::class, 'getDeviceMaterials'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.view');
        Route::get('available-serials', [App\Http\Controllers\RepairController::class, 'getAvailableSerials'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.view');
        Route::post('check-stock-availability', [App\Http\Controllers\RepairController::class, 'checkStockAvailability'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.edit');
        Route::post('replace-material', [App\Http\Controllers\RepairController::class, 'replaceMaterial'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.edit');
        Route::post('update-device-status', [App\Http\Controllers\RepairController::class, 'updateDeviceStatus'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.edit');
    });

    // Legacy routes (for backward compatibility)
    Route::get('/repair', [App\Http\Controllers\RepairController::class, 'create'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.create');
    Route::get('/repair_list', [App\Http\Controllers\RepairController::class, 'index'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.view');
    Route::get('/repair_detail/{repair?}', function ($repair = null) {
        if ($repair) {
            return redirect()->route('repairs.show', $repair);
        }
        return redirect()->route('repairs.index');
    })->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.view_detail');
    Route::get('/repair_edit/{repair?}', function ($repair = null) {
        if ($repair) {
            return redirect()->route('repairs.edit', $repair);
        }
        return redirect()->route('repairs.index');
    })->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':repairs.edit');

    //inventory - Dispatch Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [DispatchController::class, 'index'])->name('index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.view');
        Route::get('dispatch/create', [DispatchController::class, 'create'])->name('dispatch.create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.create');
        Route::post('dispatch', [DispatchController::class, 'store'])->name('dispatch.store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.create');
        Route::get('dispatch/{dispatch}', [DispatchController::class, 'show'])->name('dispatch.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.view_detail');
        Route::get('dispatch/{dispatch}/edit', [DispatchController::class, 'edit'])->name('dispatch.edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.edit');
        Route::put('dispatch/{dispatch}', [DispatchController::class, 'update'])->name('dispatch.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.edit');
        Route::post('dispatch/{dispatch}/approve', [DispatchController::class, 'approve'])->name('dispatch.approve')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.approve');
        Route::post('dispatch/{dispatch}/cancel', [DispatchController::class, 'cancel'])->name('dispatch.cancel')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.cancel');
        Route::post('dispatch/{dispatch}/complete', [DispatchController::class, 'complete'])->name('dispatch.complete')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.complete');
        Route::delete('dispatch/{dispatch}', [DispatchController::class, 'destroy'])->name('dispatch.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.delete');
        Route::get('search', [DispatchController::class, 'search'])->name('search')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.view');
    });
    Route::get('/inventory/dispatch/{dispatch}/export/excel', [DispatchController::class, 'exportExcel'])->name('inventory.dispatch.export.excel');
    Route::get('/inventory/dispatch/{dispatch}/export/pdf', [DispatchController::class, 'exportPdf'])->name('inventory.dispatch.export.pdf');

    // API routes for dispatch
    Route::prefix('api/dispatch')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory.view')->group(function () {
        Route::get('items', [DispatchController::class, 'getAvailableItems']);
        Route::get('items/all', [DispatchController::class, 'getAllAvailableItems']);
        Route::get('projects', [DispatchController::class, 'getProjects']);
        Route::get('rentals', [DispatchController::class, 'getRentals']);
        Route::get('item-serials', [DispatchController::class, 'getItemSerials']);
    });

    // API routes for dispatch
    Route::get('/api/dispatch/items', [DispatchController::class, 'getAvailableItems'])->name('api.dispatch.items');
    Route::get('/api/dispatch/items/all', [DispatchController::class, 'getAllAvailableItems'])->name('api.dispatch.items.all');

    // Warranty routes
    Route::prefix('warranties')->name('warranties.')->group(function () {
        Route::get('/', [WarrantyController::class, 'index'])->name('index');
        Route::get('/{warranty}', [WarrantyController::class, 'show'])->name('show');
    });

    // Repair API routes
    Route::prefix('api/repairs')->group(function () {
        Route::get('/search-warranty', [RepairController::class, 'searchWarranty'])->name('api.repairs.search-warranty');
        Route::get('/device-materials', [RepairController::class, 'getDeviceMaterials'])->name('api.repairs.device-materials');
        Route::get('/available-serials', [RepairController::class, 'getAvailableSerials'])->name('api.repairs.available-serials');
        Route::post('/replace-material', [RepairController::class, 'replaceMaterial'])->name('api.repairs.replace-material');
        Route::post('/update-device-status', [RepairController::class, 'updateDeviceStatus'])->name('api.repairs.update-device-status');
    });



    // Legacy routes for compatibility
    Route::get('/inventory/dispatch', [DispatchController::class, 'create']);
    Route::get('/inventory/dispatch_detail', function () {
        return redirect()->route('inventory.index');
    });
    Route::get('/inventory/dispatch_edit', function () {
        return redirect()->route('inventory.index');
    });



    //report
    Route::get('/reports', function () {
        return view('reports.index');
    });

    // Suppliers routes với middleware bảo vệ từng quyền cụ thể
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.view');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.create');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.view_detail');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.edit');
    Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.edit');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.delete');

    // Export routes for suppliers
    Route::get('/suppliers/export/fdf', [SupplierController::class, 'exportFDF'])->name('suppliers.export.fdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.export');
    Route::get('/suppliers/export/excel', [SupplierController::class, 'exportExcel'])->name('suppliers.export.excel')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.export');
    Route::get('/suppliers/export/pdf', [SupplierController::class, 'exportPDF'])->name('suppliers.export.pdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':suppliers.export');

    // Employees routes với middleware bảo vệ từng quyền cụ thể
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.view');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.create');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.view_detail');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.edit');
    Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.edit');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.delete');
    Route::put('/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.toggle_status');

    // Export routes for employees
    Route::get('/employees/export/pdf', [EmployeeController::class, 'exportPDF'])->name('employees.export.pdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.export');
    Route::get('/employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.export');

    // Routes cho Nhập kho với middleware bảo vệ từng quyền cụ thể
    Route::get('/inventory-imports', [InventoryImportController::class, 'index'])->name('inventory-imports.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory_imports.view');
    Route::get('/inventory-imports/create', [InventoryImportController::class, 'create'])->name('inventory-imports.create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory_imports.create');
    Route::post('/inventory-imports', [InventoryImportController::class, 'store'])->name('inventory-imports.store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory_imports.create');
    Route::get('/inventory-imports/{inventory_import}', [InventoryImportController::class, 'show'])->name('inventory-imports.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory_imports.view_detail');
    Route::get('/inventory-imports/{inventory_import}/edit', [InventoryImportController::class, 'edit'])->name('inventory-imports.edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory_imports.edit');
    Route::put('/inventory-imports/{inventory_import}', [InventoryImportController::class, 'update'])->name('inventory-imports.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory_imports.edit');
    Route::patch('/inventory-imports/{inventory_import}', [InventoryImportController::class, 'update'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory_imports.edit');
    Route::delete('/inventory-imports/{inventory_import}', [InventoryImportController::class, 'destroy'])->name('inventory-imports.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory_imports.delete');
    Route::patch('inventory-imports/{inventory_import}/approve', [InventoryImportController::class, 'approve'])->name('inventory-imports.approve')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory_imports.approve');

    // API route cho thông tin vật tư
    Route::get('api/materials/{id}', [InventoryImportController::class, 'getMaterialInfo'])->name('api.material.info')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':inventory_imports.view');

    // Quản lý chuyển kho
    Route::prefix('warehouse-transfers')->name('warehouse-transfers.')->middleware('auth')->group(function () {
        Route::get('/', [WarehouseTransferController::class, 'index'])->name('index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouse-transfers.view');
        Route::get('/create', [WarehouseTransferController::class, 'create'])->name('create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouse-transfers.create');
        Route::post('/', [WarehouseTransferController::class, 'store'])->name('store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouse-transfers.create');
        Route::get('/{warehouseTransfer}', [WarehouseTransferController::class, 'show'])->name('show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouse-transfers.view_detail');
        Route::get('/{warehouseTransfer}/edit', [WarehouseTransferController::class, 'edit'])->name('edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouse-transfers.edit');
        Route::put('/{warehouseTransfer}', [WarehouseTransferController::class, 'update'])->name('update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouse-transfers.edit');
        Route::delete('/{warehouseTransfer}', [WarehouseTransferController::class, 'destroy'])->name('destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouse-transfers.delete');

        // API routes
        Route::get('/check-inventory', [WarehouseTransferController::class, 'checkInventory'])->name('check-inventory')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouse-transfers.view');
        Route::post('/check-inventory', [WarehouseTransferController::class, 'checkInventory'])->name('check-inventory.post')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouse-transfers.view');
    });
    Route::patch('/warehouse-transfers/{warehouseTransfer}/approve', [WarehouseTransferController::class, 'approve'])->name('warehouse-transfers.approve');

    // Quản lý phần mềm
    Route::prefix('software')->name('software.')->middleware('auth')->group(function () {
        Route::get('/', [SoftwareController::class, 'index'])->name('index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':software.view');
        Route::get('/create', [SoftwareController::class, 'create'])->name('create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':software.create');
        Route::post('/', [SoftwareController::class, 'store'])->name('store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':software.create');
        Route::get('/{software}', [SoftwareController::class, 'show'])->name('show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':software.view_detail');
        Route::get('/{software}/edit', [SoftwareController::class, 'edit'])->name('edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':software.edit');
        Route::put('/{software}', [SoftwareController::class, 'update'])->name('update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':software.edit');
        Route::delete('/{software}', [SoftwareController::class, 'destroy'])->name('destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':software.delete');
        Route::get('/{software}/download', [SoftwareController::class, 'download'])->name('download')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':software.download');
        Route::get('/{software}/download-manual', [SoftwareController::class, 'downloadManual'])->name('download_manual')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':software.download');
    });

    // Quản lý kiểm thử (QA)
    Route::middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.view')->group(function () {
        Route::get('/testing', [TestingController::class, 'index'])->name('testing.index');
        Route::middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.create')->group(function () {
            Route::get('/testing/create', [TestingController::class, 'create'])->name('testing.create');
            Route::post('/testing', [TestingController::class, 'store'])->name('testing.store');
        });
        Route::get('/testing/{testing}', [TestingController::class, 'show'])->name('testing.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.view_detail');
        Route::get('/testing/{testing}/print', [TestingController::class, 'print'])->name('testing.print')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.print');
        Route::get('/testing/{testing}/check-pending', [TestingController::class, 'checkPending'])->name('testing.check-pending');

        Route::middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.edit')->group(function () {
            Route::get('/testing/{testing}/edit', [TestingController::class, 'edit'])->name('testing.edit');
            Route::put('/testing/{testing}', [TestingController::class, 'update'])->name('testing.update');
            Route::patch('/testing/{testing}', [TestingController::class, 'update']);
        });

        Route::post('/testing/{testing}/approve', [TestingController::class, 'approve'])->name('testing.approve')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.approve');
        Route::post('/testing/{testing}/reject', [TestingController::class, 'reject'])->name('testing.reject')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.reject');
        Route::post('/testing/{testing}/receive', [TestingController::class, 'receive'])->name('testing.receive')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.receive');
        Route::post('/testing/{testing}/complete', [TestingController::class, 'complete'])->name('testing.complete')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.complete');
        Route::post('/testing/{testing}/update-inventory', [TestingController::class, 'updateInventory'])->name('testing.update-inventory')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.update_inventory');
        Route::post('/testing/{testing}/save-to-warehouse', [TestingController::class, 'saveToWarehouse'])->name('testing.save-to-warehouse')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.complete');
        Route::post('/testing/{testing}/recalculate-no-serial', [TestingController::class, 'recalculateNoSerialQuantities'])->name('testing.recalculate-no-serial')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.edit');
        Route::delete('/testing/{testing}', [TestingController::class, 'destroy'])->name('testing.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.delete');

    });

    Route::get('api/testing/materials-by-type', [TestingController::class, 'getMaterialsByType'])->name('api.testing.materials-by-type');
    Route::get('api/testing/serial-numbers', [TestingController::class, 'getSerialNumbers'])->name('api.testing.serial-numbers');
    Route::get('api/items/{type}/{id}', [TestingController::class, 'getItemDetails'])->name('api.items.details');

    // Quản lý dự án
    Route::prefix('projects')->name('projects.')->middleware('auth')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':projects.view');
        Route::get('/create', [ProjectController::class, 'create'])->name('create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':projects.create');
        Route::post('/', [ProjectController::class, 'store'])->name('store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':projects.create');
        Route::get('/{project}', [ProjectController::class, 'show'])->name('show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':projects.view_detail');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->name('edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':projects.edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->name('update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':projects.edit');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':projects.delete');
    });

    // API để lấy thông tin khách hàng
    Route::get('/api/customers/{id}', [CustomerController::class, 'getCustomerInfo']);

    // Quản lý cho thuê
    Route::prefix('rentals')->name('rentals.')->middleware('auth')->group(function () {
        Route::get('/', [RentalController::class, 'index'])->name('index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':rentals.view');
        Route::get('/create', [RentalController::class, 'create'])->name('create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':rentals.create');
        Route::post('/', [RentalController::class, 'store'])->name('store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':rentals.create');
        Route::get('/{rental}', [RentalController::class, 'show'])->name('show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':rentals.view_detail');
        Route::get('/{rental}/edit', [RentalController::class, 'edit'])->name('edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':rentals.edit');
        Route::put('/{rental}', [RentalController::class, 'update'])->name('update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':rentals.edit');
        Route::delete('/{rental}', [RentalController::class, 'destroy'])->name('destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':rentals.delete');
        Route::post('/{rental}/extend', [RentalController::class, 'extend'])->name('extend')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':rentals.edit');
    });

    // Phiếu đề xuất triển khai dự án
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [ProjectRequestController::class, 'index'])->name('index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.view');

        // Project Request routes
        Route::prefix('project')->name('project.')->group(function () {
            Route::get('/create', [ProjectRequestController::class, 'create'])->name('create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.project.create');
            Route::post('/', [ProjectRequestController::class, 'store'])->name('store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.project.create');
            Route::get('/{id}', [ProjectRequestController::class, 'show'])->name('show')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.view_detail');
            Route::get('/{id}/edit', [ProjectRequestController::class, 'edit'])->name('edit')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.edit');
            Route::patch('/{id}', [ProjectRequestController::class, 'update'])->name('update')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.edit');
            Route::delete('/{id}', [ProjectRequestController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.delete');
            Route::post('/{id}/approve', [ProjectRequestController::class, 'approve'])->name('approve')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.approve');
            Route::get('/{id}/approve', [ProjectRequestController::class, 'approve'])->name('approve.get')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.approve');
            Route::post('/{id}/reject', [ProjectRequestController::class, 'reject'])->name('reject')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.reject');
            Route::get('/{id}/reject', [ProjectRequestController::class, 'reject'])->name('reject.get')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.reject');
            Route::post('/{id}/status', [ProjectRequestController::class, 'updateStatus'])->name('status')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.update_status');
            Route::get('/{id}/preview', [ProjectRequestController::class, 'preview'])->name('preview')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.view_detail');
            Route::get('/{id}/test-stock', [ProjectRequestController::class, 'testStockCheck'])->name('test-stock')->where('id', '[0-9]+');
        });

        // Maintenance Request routes
        Route::prefix('maintenance')->name('maintenance.')->group(function () {
            Route::get('/create', [MaintenanceRequestController::class, 'create'])->name('create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.maintenance.create');
            Route::post('/', [MaintenanceRequestController::class, 'store'])->name('store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.maintenance.create');
            Route::get('/{id}', [MaintenanceRequestController::class, 'show'])->name('show')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.view_detail');
            Route::get('/{id}/edit', [MaintenanceRequestController::class, 'edit'])->name('edit')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.edit');
            Route::patch('/{id}', [MaintenanceRequestController::class, 'update'])->name('update')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.edit');
            Route::delete('/{id}', [MaintenanceRequestController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.delete');
            Route::post('/{id}/approve', [MaintenanceRequestController::class, 'approve'])->name('approve')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.approve');
            Route::post('/{id}/reject', [MaintenanceRequestController::class, 'reject'])->name('reject')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.reject');
            Route::post('/{id}/status', [MaintenanceRequestController::class, 'updateStatus'])->name('status')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.update_status');
            Route::get('/{id}/preview', [MaintenanceRequestController::class, 'preview'])->name('preview')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.view_detail');
            
            // API route để lấy thiết bị từ project/rental
            Route::post('/api/devices', [MaintenanceRequestController::class, 'getDevices'])->name('api.devices')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.maintenance.create');
        });

        // Customer Maintenance Request routes
        Route::prefix('customer-maintenance')->name('customer-maintenance.')->middleware(\App\Http\Middleware\CustomerOrAdminMiddleware::class)->group(function () {
            Route::get('/create', [CustomerMaintenanceRequestController::class, 'create'])->name('create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.create');
            Route::post('/', [CustomerMaintenanceRequestController::class, 'store'])->name('store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.create');
            Route::get('/{id}', [CustomerMaintenanceRequestController::class, 'show'])->name('show')->where('id', '[0-9]+')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.view');
            Route::get('/{id}/edit', [CustomerMaintenanceRequestController::class, 'edit'])
                ->name('edit')
                ->where('id', '[0-9]+')
                ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.edit');
            Route::patch('/{id}', [CustomerMaintenanceRequestController::class, 'update'])
                ->name('update')
                ->where('id', '[0-9]+')
                ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.edit');
            Route::delete('/{id}', [CustomerMaintenanceRequestController::class, 'destroy'])
                ->name('destroy')
                ->where('id', '[0-9]+')
                ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.delete');
            Route::post('/{id}/approve', [CustomerMaintenanceRequestController::class, 'approve'])
                ->name('approve')
                ->where('id', '[0-9]+')
                ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.approve');
            Route::post('/{id}/reject', [CustomerMaintenanceRequestController::class, 'reject'])
                ->name('reject')
                ->where('id', '[0-9]+')
                ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.reject');
            Route::post('/{id}/status', [CustomerMaintenanceRequestController::class, 'updateStatus'])
                ->name('status')
                ->where('id', '[0-9]+')
                ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.update_status');
            Route::get('/{id}/preview', [CustomerMaintenanceRequestController::class, 'preview'])
                ->name('preview')
                ->where('id', '[0-9]+')
                ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':requests.customer-maintenance.view_detail');
        });
    });

    // Assembly API routes
    Route::get('/assemblies/generate-code', [AssemblyController::class, 'generateAssemblyCode'])->name('assemblies.generate-code')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.create');
    Route::get('/assemblies/check-code', [AssemblyController::class, 'checkAssemblyCode'])->name('assemblies.check-code')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.create');
    Route::get('/assemblies/product-materials/{productId}', [AssemblyController::class, 'getProductMaterials'])->name('assemblies.product-materials')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.create');
    Route::get('/assemblies/product-serials', [AssemblyController::class, 'getProductSerials'])->name('assemblies.product-serials')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.create');
    Route::get('/assemblies/employees', [AssemblyController::class, 'getEmployees'])->name('assemblies.employees')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.create');
    // Route::post('/assemblies/warehouse-stock/{warehouseId}', [AssemblyController::class, 'getWarehouseMaterialsStock'])->name('assemblies.warehouse-stock')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.create'); // REMOVED - Stock checking disabled
    Route::match(['get', 'post'], '/assemblies/material-serials', [AssemblyController::class, 'getMaterialSerials'])->name('assemblies.material-serials')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.create');
    Route::post('/assemblies/check-formula', [AssemblyController::class, 'checkFormula'])->name('assemblies.check-formula')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.create');

    // Assembly routes với middleware bảo vệ từng quyền cụ thể
    Route::get('/assemblies', [AssemblyController::class, 'index'])->name('assemblies.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.view');
    Route::get('/assemblies/create', [AssemblyController::class, 'create'])->name('assemblies.create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.create');
    Route::post('/assemblies', [AssemblyController::class, 'store'])->name('assemblies.store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.create');
    Route::get('/assemblies/{assembly}', [AssemblyController::class, 'show'])->name('assemblies.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.view_detail');
    Route::get('/assemblies/{assembly}/edit', [AssemblyController::class, 'edit'])->name('assemblies.edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.edit');
    Route::put('/assemblies/{assembly}', [AssemblyController::class, 'update'])->name('assemblies.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.edit');
    Route::patch('/assemblies/{assembly}', [AssemblyController::class, 'update'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.edit');
    Route::delete('/assemblies/{assembly}', [AssemblyController::class, 'destroy'])->name('assemblies.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.delete');
    Route::post('/assemblies/{assembly}/approve', [AssemblyController::class, 'approve'])->name('assemblies.approve')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.approve');

    // Assembly export routes
    Route::get('/assemblies/{assembly}/export/excel', [AssemblyController::class, 'exportExcel'])
        ->name('assemblies.export.excel')
        ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.export');
    Route::get('/assemblies/{assembly}/export/pdf', [AssemblyController::class, 'exportPdf'])->name('assemblies.export.pdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.export');

    // API route for checking serial
    Route::post('/api/check-serial', [AssemblyController::class, 'checkSerial'])->name('api.check-serial');

    // Thêm phần routes phân quyền
    // Routes cho nhóm quyền (roles) với middleware phân quyền
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':roles.view');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':roles.create');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':roles.view_detail');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':roles.edit');
    Route::patch('/roles/{role}', [RoleController::class, 'update'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':roles.edit');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':roles.delete');
    Route::patch('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggleStatus')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':roles.edit');

    // Routes cho danh sách quyền (permissions) - vẫn giữ admin-only vì đây là quyền hệ thống
    Route::middleware('admin-only')->group(function () {
        Route::resource('permissions', PermissionController::class);
    });

    // Routes cho nhật ký người dùng (user logs)
    Route::get('user-logs', [UserLogController::class, 'index'])->name('user-logs.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':user-logs.view');
    Route::get('user-logs/{id}', [UserLogController::class, 'show'])->name('user-logs.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':user-logs.view');
    Route::get('user-logs-export', [UserLogController::class, 'export'])->name('user-logs.export')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':user-logs.export');

    // Routes cho nhật ký thay đổi (change logs)
    Route::get('change-logs', [ChangeLogController::class, 'index'])->name('change-logs.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':change-logs.view');
    Route::get('change-logs/{changeLog}', [ChangeLogController::class, 'show'])->name('change-logs.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':change-logs.view_detail');
    Route::get('change-logs/{changeLog}/details', [ChangeLogController::class, 'getDetails'])->name('change-logs.details')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':change-logs.view_detail');
    Route::put('change-logs/{changeLog}', [ChangeLogController::class, 'update'])->name('change-logs.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':change-logs.edit');
    Route::patch('change-logs/{changeLog}', [ChangeLogController::class, 'update'])->name('change-logs.patch')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':change-logs.edit');

    // Routes for Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::post('/filter-ajax', [ReportController::class, 'filterAjax'])->name('filter.ajax');
        Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export.excel')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':reports.export');
        Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('export.pdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':reports.export');
    });

    // Routes for goods
    Route::get('/goods', [GoodController::class, 'index'])->name('goods.index')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.view');
    Route::get('/goods/create', [GoodController::class, 'create'])->name('goods.create')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.create');
    Route::post('/goods', [GoodController::class, 'store'])->name('goods.store')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.create');
    Route::get('/goods/{good}', [GoodController::class, 'show'])->name('goods.show')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.view_detail');
    Route::get('/goods/{good}/edit', [GoodController::class, 'edit'])->name('goods.edit')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.edit');
    Route::put('/goods/{good}', [GoodController::class, 'update'])->name('goods.update')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.edit');
    Route::delete('/goods/{good}', [GoodController::class, 'destroy'])->name('goods.destroy')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.delete');

    Route::get('/goodshidden', [GoodController::class, 'showHidden'])->name('goodshidden')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.view');
    Route::get('/goodsdeleted', [GoodController::class, 'showDeleted'])->name('goodsdeleted')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.view');
    Route::post('/goods/restore/{id}', [GoodController::class, 'restore'])->name('goods.restore')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.edit');

    Route::get('/api/goods/{id}/images', [GoodController::class, 'getGoodImages'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.view_detail');
    Route::delete('/api/goods/images/{id}', [GoodController::class, 'deleteImage'])->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.edit');

    Route::get('/goods/export/excel', [GoodController::class, 'exportExcel'])->name('goods.export.excel')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.export');
    Route::get('/goods/export/fdf', [GoodController::class, 'exportFDF'])->name('goods.export.fdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.export');
    Route::get('/goods/export/pdf', [GoodController::class, 'exportPDF'])->name('goods.export.pdf')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.export');
    Route::get('/goods/template/download', [GoodController::class, 'downloadTemplate'])->name('goods.template.download')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.create');
    Route::post('/goods/import', [GoodController::class, 'import'])->name('goods.import')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.create');
    Route::get('/goods/import/results', [GoodController::class, 'showImportResults'])->name('goods.import.results')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':goods.create');
    

    // Equipment service routes (bảo hành, thay thế, thu hồi)
    Route::prefix('equipment-service')->name('equipment.')->group(function () {
        Route::post('/return', [EquipmentServiceController::class, 'returnEquipment'])->name('return');
        Route::post('/replace', [EquipmentServiceController::class, 'replaceEquipment'])->name('replace');
        Route::get('/history/{id}', [EquipmentServiceController::class, 'getEquipmentHistory'])->name('history');
        Route::get('/backup-items/project/{projectId}', [EquipmentServiceController::class, 'getBackupItemsForProject'])->name('backup-items.project');
        Route::get('/backup-items/rental/{rentalId}', [EquipmentServiceController::class, 'getBackupItemsForRental'])->name('backup-items.rental');
        Route::get('/item-serials/{id}', [\App\Http\Controllers\EquipmentServiceController::class, 'getItemSerials'])->name('equipment.itemSerials');
    });

    // Export routes
    Route::middleware('auth')->group(function () {
        Route::get('/requests/{type}/{id}/export-excel', [RequestExportController::class, 'exportExcel'])
            ->name('requests.export-excel');
        Route::get('/requests/{type}/{id}/export-pdf', [RequestExportController::class, 'exportPDF'])
            ->name('requests.export-pdf');
    });

    // API routes cho thiết bị của dự án và đơn thuê
    Route::get('/api/projects/{projectId}', [ProjectController::class, 'getProjectDetails'])->name('api.projects.details');
    Route::get('/api/projects/{projectId}/items', [ProjectController::class, 'getProjectItems'])->name('api.projects.items');
    Route::get('/api/rentals/{rentalId}/items', [RentalController::class, 'getRentalItems'])->name('api.rentals.items');

    Route::get('/api/warranty/{warrantyId}/items', [WarrantyController::class, 'getWarrantyItems'])->name('api.warranty.items');

    // New route for creating testing from assembly
    Route::post('/assemblies/{assembly}/create-testing', [AssemblyController::class, 'createTestingFromAssembly'])
        ->name('assemblies.create-testing')
        ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':testing.create');
    // Device code Excel routes
    Route::get('/device-codes/template', [DeviceCodeController::class, 'downloadTemplate'])->name('device-codes.template');
    Route::post('/device-codes/import', [DeviceCodeController::class, 'import'])->name('device-codes.import');
    Route::post('/device-codes', [DeviceCodeController::class, 'store'])->name('device-codes.store');
    Route::put('/device-codes/{id}', [DeviceCodeController::class, 'update'])->name('device-codes.update');

    // API route for employees
    Route::get('/api/employees/search', [EmployeeController::class, 'apiSearch'])->name('api.employees.search')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':employees.view');

    // API route for warehouses
    Route::get('/api/warehouses/{warehouse}/materials', [WarehouseController::class, 'getMaterials'])->name('api.warehouses.materials')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':warehouses.view');
    
    // API route for creating products from assembly
    Route::post('/api/products/create-from-assembly', [App\Http\Controllers\Api\ProductController::class, 'createFromAssembly'])
        ->name('api.products.create-from-assembly')
        ->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':products.create');
});

// Testing routes
Route::prefix('api/testing')->group(function () {
    Route::get('materials/{type}', [TestingController::class, 'getMaterialsByType']);
    Route::get('inventory/{type}/{id}/{warehouseId}', [TestingController::class, 'getInventoryInfo']);
    Route::get('check-code', [TestingController::class, 'checkTestCode']);
});

// API route for inventory check
Route::get('/api/inventory/{type}/{id}/{warehouseId}', [TestingController::class, 'getInventoryInfo']);

//QR tra cứu bảo hành
Route::get('/warranties/verify', function () {
    return view('warranties.verify');
});
Route::get('/warranty/check/{warrantyCode}', [WarrantyController::class, 'check'])->name('warranty.check');
Route::get('/api/warranty/check', [WarrantyController::class, 'apiCheck'])->name('api.warranty.check');
Route::get('/api/dispatch/{dispatchId}/warranties', [WarrantyController::class, 'getDispatchWarranties'])->name('api.dispatch.warranties');
Route::get('/api/warranty/{warrantyId}/items', [WarrantyController::class, 'getWarrantyItems'])->name('api.warranty.items');

Route::post('/assemblies/{assembly}/cancel', [AssemblyController::class, 'cancel'])->name('assemblies.cancel')->middleware(\App\Http\Middleware\CheckPermissionMiddleware::class . ':assembly.cancel');