<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengundiImportController;
use App\Http\Controllers\PengundiTransferController;
use App\Http\Controllers\MembersTransferController;
use App\Http\Controllers\MembersUploadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengundiAnalyticsController;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/testimport', function () {
    return view('testimport');
})->middleware(['auth', 'verified'])->name('testimport');

Route::middleware('auth')->group(function () {




    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


});


Route::get('/event', function () {
    return view('calendar');
})->middleware(['auth', 'verified'])->name('event');



Route::get('/pengundi/analytics', function () {
    return view('pengundi.analysis');
})->middleware(['auth', 'verified'])->name('pengundi.analysis');

Route::get('/pengundi/transfer', [PengundiTransferController::class, 'transfer']);


Route::post('/pengundi/import', [PengundiImportController::class, 'import']);






Route::post('/members/upload', [MembersUploadController::class, 'upload'])
    ->name('members.upload');


Route::get('/members/transfer', [MembersTransferController::class, 'transfer']);


 

Route::post('/analytics/chart/overview', [PengundiAnalyticsController::class, 'overview']);
Route::post('/analytics/chart/jantina', [PengundiAnalyticsController::class, 'jantina']);
Route::post('/analytics/chart/jantina2', [PengundiAnalyticsController::class, 'overviewByJantina']);



require __DIR__ . '/auth.php';
