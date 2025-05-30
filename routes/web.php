<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/assemble', function () {
    return view('assemble.index');
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

Route::get('/materials', function () {
    return view('materials.index');
});

Route::get('/materials/create', function () {
    return view('materials.create');
});

Route::get('/materials/show', function () {
    return view('materials.show');
});


