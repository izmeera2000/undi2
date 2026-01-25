<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin.page.dashboard');
});



Route::get('/dashboard', function () {
    return view('admin.page.dashboard');
});