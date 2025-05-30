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

