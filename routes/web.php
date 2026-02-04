<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengundiImportController;
use App\Http\Controllers\PengundiTransferController;
use App\Http\Controllers\MembersTransferController;
use App\Http\Controllers\MembersUploadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengundiAnalyticsController;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request; // ✅ correct


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




Route::get('/pengundi/analytics', [PengundiAnalyticsController::class, 'dropdowns'])
    ->middleware(['auth', 'verified'])
    ->name('pengundi.analysis');


Route::get('/pengundi/transfer', [PengundiTransferController::class, 'transfer']);


Route::post('/pengundi/import', [PengundiImportController::class, 'import']);


Route::post('/pengundi/analytics/pdf', function (Request $request) {
    $charts = $request->input('charts'); // full array with id, image, title

    return Pdf::loadView('pengundi.pdf', ['charts' => $charts])
        ->setPaper('a4', 'portrait')

        ->stream('pengundi-analytics.pdf');
});


Route::post('/members/upload', [MembersUploadController::class, 'upload'])
    ->name('members.upload');


Route::get('/members/transfer', [MembersTransferController::class, 'transfer']);




Route::post('/analytics/chart/overview', [PengundiAnalyticsController::class, 'overview']);
Route::post('/analytics/chart/jantina', [PengundiAnalyticsController::class, 'jantina']);
Route::post('/analytics/chart/jantina2', [PengundiAnalyticsController::class, 'overviewByJantina']);

Route::post('/analytics/chart/ahliumno', [PengundiAnalyticsController::class, 'ahliumno']);
Route::post('/analytics/chart/ahliumno2', [PengundiAnalyticsController::class, 'overviewByAhliumno']);


Route::post('/analytics/chart/dundm', [PengundiAnalyticsController::class, 'dundm']);
Route::post('/analytics/chart/dundm2', [PengundiAnalyticsController::class, 'overviewByDundm']);
Route::post('/analytics/chart/dundm2spec', [PengundiAnalyticsController::class, 'overviewByDundmSpecDun']);


Route::post('/analytics/chart/firsttime', [PengundiAnalyticsController::class, 'overviewByFirstTime']);

Route::get('/analytics/pengundi', [PengundiAnalyticsController::class, 'index']);


require __DIR__ . '/auth.php';
