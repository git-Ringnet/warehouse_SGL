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

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:web,customer', \App\Http\Middleware\CheckUserType::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile/update-password', [AuthController::class, 'updatePassword'])->name('profile.update_password');

    // Redirect root to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard routes
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/statistics', [App\Http\Controllers\DashboardController::class, 'getStatistics'])->name('dashboard.statistics');
    Route::get('/dashboard/inventory-overview-chart', [App\Http\Controllers\DashboardController::class, 'getInventoryOverviewChart'])->name('dashboard.inventory-overview-chart');
    Route::get('/dashboard/inventory-categories-chart', [App\Http\Controllers\DashboardController::class, 'getInventoryCategoriesChart'])->name('dashboard.inventory-categories-chart');
    Route::get('/dashboard/warehouse-distribution-chart', [App\Http\Controllers\DashboardController::class, 'getWarehouseDistributionChart'])->name('dashboard.warehouse-distribution-chart');
    Route::get('/dashboard/project-growth-chart', [App\Http\Controllers\DashboardController::class, 'getProjectGrowthChart'])->name('dashboard.project-growth-chart');

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
    Route::get('materials/{id}/history-ajax', [MaterialController::class, 'historyAjax'])->name('materials.historyAjax');

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

    Route::get('/warranties/verify', function () {
        return view('warranties.verify');
    });

    //repair - Repair Management
    Route::prefix('repairs')->name('repairs.')->group(function () {
        Route::get('/', [App\Http\Controllers\RepairController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\RepairController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\RepairController::class, 'store'])->name('store');
        Route::get('/{repair}', [App\Http\Controllers\RepairController::class, 'show'])->name('show');
        Route::get('/{repair}/edit', [App\Http\Controllers\RepairController::class, 'edit'])->name('edit');
        Route::put('/{repair}', [App\Http\Controllers\RepairController::class, 'update'])->name('update');
        Route::delete('/{repair}', [App\Http\Controllers\RepairController::class, 'destroy'])->name('destroy');
    });

    // API routes for repairs
    Route::prefix('api/repairs')->group(function () {
        Route::get('search-warranty', [App\Http\Controllers\RepairController::class, 'searchWarranty']);
    });

    // Legacy routes (for backward compatibility)
    Route::get('/repair', [App\Http\Controllers\RepairController::class, 'create']);
    Route::get('/repair_list', [App\Http\Controllers\RepairController::class, 'index']);
    Route::get('/repair_detail/{repair?}', function ($repair = null) {
        if ($repair) {
            return redirect()->route('repairs.show', $repair);
        }
        return redirect()->route('repairs.index');
    });
    Route::get('/repair_edit/{repair?}', function ($repair = null) {
        if ($repair) {
            return redirect()->route('repairs.edit', $repair);
        }
        return redirect()->route('repairs.index');
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
        Route::delete('dispatch/{dispatch}', [DispatchController::class, 'destroy'])->name('dispatch.destroy');
        Route::get('search', [DispatchController::class, 'search'])->name('search');
    });

    // API routes for dispatch
    Route::prefix('api/dispatch')->group(function () {
        Route::get('items', [DispatchController::class, 'getAvailableItems']);
        Route::get('items/all', [DispatchController::class, 'getAllAvailableItems']);
        Route::get('projects', [DispatchController::class, 'getProjects']);
        Route::get('rentals', [DispatchController::class, 'getRentals']);
        Route::get('item-serials', [DispatchController::class, 'getItemSerials']);
    });

    // Temporary route to add warehouse materials data
    Route::get('/debug/add-warehouse-materials', function () {
        // Add stock for products 6 and 10 in warehouse 1
        $products = [6, 10];
        $added = [];

        foreach ($products as $productId) {
            // Check if record exists
            $existing = DB::table('warehouse_materials')
                ->where('item_type', 'product')
                ->where('material_id', $productId)
                ->where('warehouse_id', 1)
                ->first();

            if (!$existing) {
                DB::table('warehouse_materials')->insert([
                    'warehouse_id' => 1,
                    'item_type' => 'product',
                    'material_id' => $productId,
                    'quantity' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $added[] = "Added product $productId with quantity 50";
            } else {
                $added[] = "Product $productId already exists with quantity {$existing->quantity}";
            }
        }

        return response()->json([
            'message' => 'Warehouse materials processing completed',
            'results' => $added
        ]);
    });

    // Simple route to add stock for all products
    Route::get('/debug/add-all-stock', function () {
        $products = DB::table('products')->get();
        $warehouses = DB::table('warehouses')->get();
        $added = 0;

        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                $existing = DB::table('warehouse_materials')
                    ->where('item_type', 'product')
                    ->where('material_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();

                if (!$existing) {
                    DB::table('warehouse_materials')->insert([
                        'warehouse_id' => $warehouse->id,
                        'item_type' => 'product',
                        'material_id' => $product->id,
                        'quantity' => rand(20, 100),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $added++;
                }
            }
        }

        return "Added $added warehouse material records. Total products: " . $products->count() . ", Total warehouses: " . $warehouses->count();
    });

    // API routes for dispatch
    Route::get('/api/dispatch/items', [DispatchController::class, 'getAvailableItems'])->name('api.dispatch.items');
    Route::get('/api/dispatch/items/all', [DispatchController::class, 'getAllAvailableItems'])->name('api.dispatch.items.all');

    // Warranty routes
    Route::prefix('warranties')->name('warranties.')->group(function () {
        Route::get('/', [WarrantyController::class, 'index'])->name('index');
        Route::get('/{warranty}', [WarrantyController::class, 'show'])->name('show');
    });

    // Public warranty check routes
    Route::get('/warranty/check/{warrantyCode}', [WarrantyController::class, 'check'])->name('warranty.check');
    Route::get('/api/warranty/check', [WarrantyController::class, 'apiCheck'])->name('api.warranty.check');
    Route::get('/api/dispatch/{dispatchId}/warranties', [WarrantyController::class, 'getDispatchWarranties'])->name('api.dispatch.warranties');

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
    Route::resource('testing', TestingController::class);
    Route::post('testing/{testing}/approve', [TestingController::class, 'approve'])->name('testing.approve');
    Route::post('testing/{testing}/reject', [TestingController::class, 'reject'])->name('testing.reject');
    Route::post('testing/{testing}/receive', [TestingController::class, 'receive'])->name('testing.receive');
    Route::post('testing/{testing}/complete', [TestingController::class, 'complete'])->name('testing.complete');
    Route::post('testing/{testing}/update-inventory', [TestingController::class, 'updateInventory'])->name('testing.update-inventory');
    Route::get('testing/{testing}/print', [TestingController::class, 'print'])->name('testing.print');
    Route::get('testing/{testing}/check-pending', [TestingController::class, 'checkPending'])->name('testing.check-pending');
    Route::get('api/testing/materials-by-type', [TestingController::class, 'getMaterialsByType'])->name('api.testing.materials-by-type');
    Route::get('api/testing/serial-numbers', [TestingController::class, 'getSerialNumbers'])->name('api.testing.serial-numbers');
    Route::get('api/items/{type}/{id}', [TestingController::class, 'getItemDetails'])->name('api.items.details');

    // Quản lý dự án
    Route::resource('projects', ProjectController::class);

    // API để lấy thông tin khách hàng
    Route::get('/api/customers/{id}', [CustomerController::class, 'getCustomerInfo']);

    // Quản lý cho thuê
    Route::resource('rentals', RentalController::class);
    Route::post('/rentals/{rental}/extend', [RentalController::class, 'extend'])->name('rentals.extend');

    // Phiếu đề xuất triển khai dự án
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [ProjectRequestController::class, 'index'])->name('index');

        // Project Request routes
        Route::prefix('project')->name('project.')->group(function () {
            Route::get('/create', [ProjectRequestController::class, 'create'])->name('create');
            Route::post('/', [ProjectRequestController::class, 'store'])->name('store');
            Route::get('/{id}', [ProjectRequestController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [ProjectRequestController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::patch('/{id}', [ProjectRequestController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [ProjectRequestController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/{id}/approve', [ProjectRequestController::class, 'approve'])->name('approve')->where('id', '[0-9]+');
            Route::post('/{id}/reject', [ProjectRequestController::class, 'reject'])->name('reject')->where('id', '[0-9]+');
            Route::post('/{id}/status', [ProjectRequestController::class, 'updateStatus'])->name('status')->where('id', '[0-9]+');
            Route::get('/{id}/preview', [ProjectRequestController::class, 'preview'])->name('preview')->where('id', '[0-9]+');
        });

        // Maintenance Request routes
        Route::prefix('maintenance')->name('maintenance.')->group(function () {
            Route::get('/create', [MaintenanceRequestController::class, 'create'])->name('create');
            Route::post('/', [MaintenanceRequestController::class, 'store'])->name('store');
            Route::get('/{id}', [MaintenanceRequestController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [MaintenanceRequestController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::patch('/{id}', [MaintenanceRequestController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [MaintenanceRequestController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/{id}/approve', [MaintenanceRequestController::class, 'approve'])->name('approve')->where('id', '[0-9]+');
            Route::post('/{id}/reject', [MaintenanceRequestController::class, 'reject'])->name('reject')->where('id', '[0-9]+');
            Route::post('/{id}/status', [MaintenanceRequestController::class, 'updateStatus'])->name('status')->where('id', '[0-9]+');
            Route::get('/{id}/preview', [MaintenanceRequestController::class, 'preview'])->name('preview')->where('id', '[0-9]+');
        });

        // Customer Maintenance Request routes
        Route::prefix('customer-maintenance')->name('customer-maintenance.')->middleware(\App\Http\Middleware\CustomerOrAdminMiddleware::class)->group(function () {
            Route::get('/create', [CustomerMaintenanceRequestController::class, 'create'])->name('create');
            Route::post('/', [CustomerMaintenanceRequestController::class, 'store'])->name('store');
            Route::get('/{id}', [CustomerMaintenanceRequestController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit', [CustomerMaintenanceRequestController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::patch('/{id}', [CustomerMaintenanceRequestController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}', [CustomerMaintenanceRequestController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/{id}/approve', [CustomerMaintenanceRequestController::class, 'approve'])->name('approve')->where('id', '[0-9]+');
            Route::post('/{id}/reject', [CustomerMaintenanceRequestController::class, 'reject'])->name('reject')->where('id', '[0-9]+');
            Route::post('/{id}/status', [CustomerMaintenanceRequestController::class, 'updateStatus'])->name('status')->where('id', '[0-9]+');
            Route::get('/{id}/preview', [CustomerMaintenanceRequestController::class, 'preview'])->name('preview')->where('id', '[0-9]+');
        });
    });
    // Assembly routes
    Route::get('/assemblies/generate-code', [AssemblyController::class, 'generateAssemblyCode'])->name('assemblies.generate-code');
    Route::get('/assemblies/check-code', [AssemblyController::class, 'checkAssemblyCode'])->name('assemblies.check-code');
    Route::get('/assemblies/product-materials/{productId}', [AssemblyController::class, 'getProductMaterials'])->name('assemblies.product-materials');
    Route::get('/assemblies/employees', [AssemblyController::class, 'getEmployees'])->name('assemblies.employees');
    Route::post('/assemblies/warehouse-stock/{warehouseId}', [AssemblyController::class, 'getWarehouseMaterialsStock'])->name('assemblies.warehouse-stock');
    Route::get('/assemblies/material-serials', [AssemblyController::class, 'getMaterialSerials'])->name('assemblies.material-serials');

    // Assembly export routes
    Route::get('/assemblies/{assembly}/export/excel', [AssemblyController::class, 'exportExcel'])->name('assemblies.export.excel');
    Route::get('/assemblies/{assembly}/export/pdf', [AssemblyController::class, 'exportPdf'])->name('assemblies.export.pdf');

    Route::resource('assemblies', AssemblyController::class);

    // API route for checking serial duplicates
    Route::post('/api/check-serial', [AssemblyController::class, 'checkSerial'])->name('api.check-serial');

    // API route for product components
    Route::get('/api/products/{id}/components', [ProductController::class, 'getComponents'])->name('api.products.components');

    // Thêm phần routes phân quyền
    // Routes cho nhóm quyền (roles) - chỉ admin mới có quyền
    Route::middleware('admin-only')->group(function () {
        Route::resource('roles', RoleController::class);
        Route::patch('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggleStatus');

        // Routes cho danh sách quyền (permissions)
        Route::resource('permissions', PermissionController::class);
    });

    // Routes cho nhật ký người dùng (user logs)
    Route::get('user-logs', [UserLogController::class, 'index'])->name('user-logs.index');
    Route::get('user-logs/{id}', [UserLogController::class, 'show'])->name('user-logs.show');
    Route::get('user-logs-export', [UserLogController::class, 'export'])->name('user-logs.export');

    // Routes cho nhật ký thay đổi (change logs)
    Route::get('change-logs', [ChangeLogController::class, 'index'])->name('change-logs.index');
    Route::get('change-logs/{changeLog}', [ChangeLogController::class, 'show'])->name('change-logs.show');
    Route::get('change-logs/{changeLog}/details', [ChangeLogController::class, 'getDetails'])->name('change-logs.details');
    Route::put('change-logs/{changeLog}', [ChangeLogController::class, 'update'])->name('change-logs.update');
    Route::patch('change-logs/{changeLog}', [ChangeLogController::class, 'update'])->name('change-logs.patch');

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

    // Temporary debug route
    Route::get('/debug/warehouse-materials', function () {
        $totalRecords = DB::table('warehouse_materials')->count();
        $productRecords = DB::table('warehouse_materials')->where('item_type', 'product')->count();
        $sampleRecords = DB::table('warehouse_materials')->where('item_type', 'product')->limit(10)->get();

        // Check specific products that were failing
        $specificProducts = DB::table('warehouse_materials')
            ->where('item_type', 'product')
            ->whereIn('material_id', [6, 10])
            ->where('warehouse_id', 1)
            ->get();

        return response()->json([
            'total_records' => $totalRecords,
            'product_records' => $productRecords,
            'sample_records' => $sampleRecords,
            'specific_products_6_10' => $specificProducts,
            'all_warehouse_1_products' => DB::table('warehouse_materials')
                ->where('item_type', 'product')
                ->where('warehouse_id', 1)
                ->get()
        ]);
    });

    // Routes for Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::post('/filter-ajax', [ReportController::class, 'filterAjax'])->name('filter.ajax');
        Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
    });


    // Equipment service routes (bảo hành, thay thế, thu hồi)
    Route::prefix('equipment-service')->name('equipment.')->group(function () {
        Route::post('/return', [EquipmentServiceController::class, 'returnEquipment'])->name('return');
        Route::post('/replace', [EquipmentServiceController::class, 'replaceEquipment'])->name('replace');
        Route::get('/history/{id}', [EquipmentServiceController::class, 'getEquipmentHistory'])->name('history');
        Route::get('/backup-items/project/{projectId}', [EquipmentServiceController::class, 'getBackupItemsForProject'])->name('backup-items.project');
        Route::get('/backup-items/rental/{rentalId}', [EquipmentServiceController::class, 'getBackupItemsForRental'])->name('backup-items.rental');
    });

    // Export routes
    Route::middleware('auth')->group(function () {
        Route::get('/requests/{type}/{id}/export-excel', [RequestExportController::class, 'exportExcel'])
            ->name('requests.export-excel');
        Route::get('/requests/{type}/{id}/export-pdf', [RequestExportController::class, 'exportPDF'])
            ->name('requests.export-pdf');
    });

    // API routes cho thiết bị của dự án và đơn thuê
    Route::get('/api/projects/{projectId}/items', [ProjectController::class, 'getProjectItems'])->name('api.projects.items');
    Route::get('/api/rentals/{rentalId}/items', [RentalController::class, 'getRentalItems'])->name('api.rentals.items');
});
