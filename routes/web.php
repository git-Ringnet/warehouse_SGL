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

Route::get('/materials', function () {
    return view('materials.index');
});

Route::get('/materials/create', function () {
    return view('materials.create');
});

Route::get('/materials/show', function () {
    return view('materials.show');
});


