<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengundiImportController;
use App\Http\Controllers\PengundiTransferController;
use App\Http\Controllers\MembersTransferController;
use App\Http\Controllers\MembersUploadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengundiAnalyticsController;
use App\Http\Controllers\EventController;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\MailController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\StaffController;


Route::get('/', function () {
    return view('auth.login');
});



Route::get('/testimport', function () {
    return view('testimport');
})->middleware(['auth', 'verified'])->name('testimport');







Route::middleware('auth')->group(function () {




    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');




});


Route::middleware(['auth', 'verified'])->group(function () {




 

    Route::view('/dashboard', 'dashboard')->name('dashboard');









    // Staff list page (view only)
    Route::view('/staff/list', 'staff.list')->name('staff.list');

    // Staff page
    Route::get('/staff/data', [StaffController::class, 'getStaff'])->name('staff.data');

    Route::resource('staff', StaffController::class)
        ->except(['index']);
    Route::get('/staff/{staff}', [StaffController::class, 'show'])->name('staff.show');


    Route::post('/staff/{user}/suspend', [StaffController::class, 'suspend'])->name('staff.suspend');
    Route::post('/staff/{user}/activate', [StaffController::class, 'activate'])->name('staff.activate');
    Route::post('/staff/{user}/role', [StaffController::class, 'changeRole'])->name('staff.changeRole');

    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');


    Route::post('/staff/{user}/profile', [StaffController::class, 'updateProfile'])
        ->name('staff.profile.update');
    Route::post('/staff/{user}/change-password', [StaffController::class, 'changePassword'])
        ->name('staff.changePassword');
        


        Route::post('/staff/{user}/avatar', [StaffController::class, 'updateAvatar']);


});


Route::get('/event', function () {
    $users = User::where('id', '!=', auth()->id())
        ->select('id', 'name')
        ->get();

    return view('calendar', compact('users'));
})->middleware(['auth', 'verified'])->name('event');



//////////////////////////////////////////////////////////////////





////////////////////////////////////////////////////////////////////////////////////////




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
//////////////////////////////////////////////////////////////////////////

Route::post('/members/upload', [MembersUploadController::class, 'upload'])
    ->name('members.upload');


Route::get('/members/transfer', [MembersTransferController::class, 'transfer']);





/////////////////////////////////////////////////////////////////////////////////



Route::post('/analytics/chart/overview', [PengundiAnalyticsController::class, 'overview']);
Route::post('/analytics/chart/jantina', [PengundiAnalyticsController::class, 'jantina']);
Route::post('/analytics/chart/jantina2', [PengundiAnalyticsController::class, 'overviewByJantina']);

Route::post('/analytics/chart/ahliumno', [PengundiAnalyticsController::class, 'ahliumno']);
Route::post('/analytics/chart/ahliumno2', [PengundiAnalyticsController::class, 'overviewByAhliumno']);


Route::post('/analytics/chart/dundm', [PengundiAnalyticsController::class, 'dundm']);
Route::post('/analytics/chart/dundm2', [PengundiAnalyticsController::class, 'overviewByDundm']);
Route::post('/analytics/chart/dundm2spec', [PengundiAnalyticsController::class, 'overviewByDundmSpecDun']);


Route::post('/analytics/chart/firsttime', [PengundiAnalyticsController::class, 'overviewByFirstTime']);

Route::post('/analytics/pengundi', [PengundiAnalyticsController::class, 'index']);
Route::get('/analytics/pengundi2', [PengundiAnalyticsController::class, 'index']);







////////////////////////

Route::post('/mail/send', [MailController::class, 'sendEmail'])->name('mail.send');


require __DIR__ . '/auth.php';
