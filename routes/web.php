<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

//assemble
Route::get('/assemble', function () {
    return view('assemble.index');
});

Route::get('/assemble/create', function () {
    return view('assemble.create');
});

Route::get('/assemble/show', function () {
    return view('assemble.show');
});

Route::get('/assemble/edit', function () {
    return view('assemble.edit');
});

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

//Materials
Route::get('/materials', function () {
    return view('materials.index');
});

Route::get('/materials/create', function () {
    return view('materials.create');
});

Route::get('/materials/show', function () {
    return view('materials.show');
});

Route::get('/materials/edit', function () {
    return view('materials.edit');
});

//Products
Route::get('/products', function () {
    return view('products.index');
});

Route::get('/products/create', function () {
    return view('products.create');
});

Route::get('/products/show', function () {
    return view('products.show');
});

Route::get('/products/edit', function () {
    return view('products.edit');
});

//warehouses
Route::get('/warehouses', function () {
    return view('warehouses.index');
});

Route::get('/warehouses/create', function () {
    return view('warehouses.create');
});

Route::get('/warehouses/show', function () {
    return view('warehouses.show');
});

Route::get('/warehouses/edit', function () {
    return view('warehouses.edit');
});

//Warranties
Route::get('/warranties', function () {
    return view('warranties.index');
});

Route::get('/warranties/create', function () {
    return view('warranties.create');
});

Route::get('/warranties/show', function () {
    return view('warranties.show');
});

Route::get('/warranties/edit', function () {
    return view('warranties.edit');
});

Route::get('/warranties/activate', function () {
    return view('warranties.activate');
});

Route::get('/warranties/verify', function () {
    return view('warranties.verify');
});

//repair
Route::get('/warranties/repair', function () {
    return view('warranties.repair');
});

Route::get('/warranties/repair_list', function () {
    return view('warranties.repair_list');
});

Route::get('/warranties/repair_detail', function () {
    return view('warranties.repair_detail');
});

Route::get('/warranties/repair_edit', function () {
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


