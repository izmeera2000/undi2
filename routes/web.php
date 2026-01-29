<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReportController;
use App\Http\Controllers\PengundiController;


Route::get('/', function () {
    return view('admin.page.dashboard');
});



Route::get('/dashboard', function () {
    return view('admin.page.dashboard');
});




Route::post('/chart/sales', [ReportController::class, 'sales']);

Route::get('/pengundi', [PengundiController::class, 'index']);
 

// Tampilkan semua pengundi
Route::get('/pengundi', [PengundiController::class, 'index'])->name('pengundi.index');

// Tampilkan pengundi berdasarkan ID
 