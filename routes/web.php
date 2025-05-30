<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/assemble', function () {
    return view('assemble.index');
});
//khách hàng
Route::get('/customers', function () {
    return view('customers.index');
});

Route::get('/customers/create', function () {
    return view('customers.create');
});

Route::get('/customers/{id}', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('customers.show');
})->where('id', '[0-9]+');

Route::get('/customers/{id}/edit', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('customers.edit');
})->where('id', '[0-9]+');

Route::post('/customers', function () {
    // Xử lý lưu khách hàng mới
    return redirect('/customers');
});

Route::put('/customers/{id}', function ($id) {
    // Xử lý cập nhật khách hàng
    return redirect('/customers/' . $id);
});

Route::delete('/customers/{id}', function ($id) {
    // Xử lý xóa khách hàng
    return redirect('/customers');
});

// Nhà cung cấp
Route::get('/suppliers', function () {
    return view('suppliers.index');
});

Route::get('/suppliers/create', function () {
    return view('suppliers.create');
});

Route::get('/suppliers/{id}', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('suppliers.show');
})->where('id', '[0-9]+');

Route::get('/suppliers/{id}/edit', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('suppliers.edit');
})->where('id', '[0-9]+');

Route::post('/suppliers', function () {
    // Xử lý lưu nhà cung cấp mới
    return redirect('/suppliers');
});

Route::put('/suppliers/{id}', function ($id) {
    // Xử lý cập nhật nhà cung cấp
    return redirect('/suppliers/' . $id);
});

Route::delete('/suppliers/{id}', function ($id) {
    // Xử lý xóa nhà cung cấp
    return redirect('/suppliers');
});

// Quản lý nhân viên
Route::get('/employees', function () {
    return view('employees.index');
});

Route::get('/employees/create', function () {
    return view('employees.create');
});

Route::get('/employees/{id}', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('employees.show');
})->where('id', '[0-9]+');

Route::get('/employees/{id}/edit', function ($id) {
    // Trong thực tế, sẽ truy vấn dữ liệu từ database ở đây
    return view('employees.edit');
})->where('id', '[0-9]+');

Route::post('/employees', function () {
    // Xử lý lưu nhân viên mới
    return redirect('/employees');
});

Route::put('/employees/{id}', function ($id) {
    // Xử lý cập nhật nhân viên
    return redirect('/employees/' . $id);
});

Route::delete('/employees/{id}', function ($id) {
    // Xử lý xóa nhân viên
    return redirect('/employees');
});

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

Route::get('/materials', function () {
    return view('materials.index');
});

Route::get('/materials/create', function () {
    return view('materials.create');
});

Route::get('/materials/show', function () {
    return view('materials.show');
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


