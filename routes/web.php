<?php

use App\Http\Controllers\MembersController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengundiImportController;
use App\Http\Controllers\PengundiTransferController;
use App\Http\Controllers\MembersTransferController;
use App\Http\Controllers\MembersUploadController;
use App\Http\Controllers\PengundiAnalyticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\DunController;
use App\Http\Controllers\DmController;
use App\Http\Controllers\ParlimenController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;

// --------------------------------------------------
// Public / Auth Routes
// --------------------------------------------------

Route::get('/', fn() => view('auth.login'));

Route::get('/testimport', fn() => view('testimport'))
    ->middleware(['auth', 'verified'])
    ->name('testimport');

// --------------------------------------------------
// Authenticated Routes
// --------------------------------------------------

Route::middleware('auth')->group(function () {

    // Profile
    // Route::prefix('profile')->group(function () {
    //     Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
    //     Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
    //     Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // });

    // Events
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('events.index');
        Route::post('/', [EventController::class, 'store'])->name('events.store');
        Route::get('{event}', [EventController::class, 'show'])->name('events.show');
        Route::put('{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('{event}', [EventController::class, 'destroy'])->name('events.destroy');
    });

    Route::get('upcoming', [EventController::class, 'upcoming'])->name('events.upcoming');


    Route::get('/first-login', function () {
        return view('auth.first-login');
    })->name('first.login');

    Route::post('/first-login', [StaffController::class, 'firstLoginUpdate'])
        ->name('first.login.update');









    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

});

// --------------------------------------------------
// Verified Routes (Dashboard + Staff + Protected Features)
// --------------------------------------------------

Route::middleware(['auth', 'active'])->group(function () {


    Route::get('/clear-all', function () {
        Artisan::call('optimize:clear');
        return 'Cleared';
    });







    Route::get('/event', [EventController::class, 'list'])
        ->name('event');



    // Dashboard
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Staff
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::view('list', 'staff.list')->name('list');
        Route::get('{staff}/edit', [StaffController::class, 'edit'])->name('edit');
        Route::post('data', [StaffController::class, 'getStaff'])->name('data');
        Route::get('{staff}', [StaffController::class, 'show'])->name('show');
        Route::post('{user}/suspend', [StaffController::class, 'suspend'])->name('suspend');
        Route::post('{user}/activate', [StaffController::class, 'activate'])->name('activate');
        Route::post('{user}/role', [StaffController::class, 'changeRole'])->name('changeRole');
        Route::post('{user}/profile', [StaffController::class, 'updateProfile'])->name('profile.update');
        Route::post('{user}/change-password', [StaffController::class, 'changePassword'])->name('changePassword');
        Route::post('{user}/avatar', [StaffController::class, 'updateAvatar']);
        Route::post('store', [StaffController::class, 'store'])->name('store');


        Route::delete('{staff}', [StaffController::class, 'destroy'])->name('destroy');
    });



    // Pengundi Analytics
    Route::prefix('pengundi')->name('pengundi.')->group(function () {
        Route::get('analytics', [PengundiAnalyticsController::class, 'dropdowns'])->name('analysis');


        Route::view('bulkimport', 'pengundi.bulkimport')->name('bulkimport');

        Route::get('import/progress', [PengundiImportController::class, 'progress'])
            ->name('import.progress');

        Route::post('import', [PengundiImportController::class, 'import'])->name('import');
        Route::get('transfer', [PengundiTransferController::class, 'transfer']);
        Route::post('analytics/pdf', [PengundiAnalyticsController::class, 'generatePdf'])
            ->name('analytics.pdf');

    });

    // Members
    Route::prefix('members')->name('members.')->group(function () {


        Route::get('list', [MembersController::class, 'list'])->name('list');

        Route::get('{member}/edit', [MembersController::class, 'edit'])->name('edit');
        Route::post('data', [MembersController::class, 'getList'])->name('data');
        // Route::resource('/', StaffController::class)->except(['index']);
        Route::get('{member}', [MembersController::class, 'show'])->name('show');
        // Route::post('{user}/suspend', [StaffController::class, 'suspend'])->name('suspend');
        // Route::post('{user}/activate', [StaffController::class, 'activate'])->name('activate');
        // Route::post('{user}/role', [StaffController::class, 'changeRole'])->name('changeRole');
        Route::post('{user}/profile', [StaffController::class, 'updateProfile'])->name('profile.update');
        // Route::post('{user}/change-password', [StaffController::class, 'changePassword'])->name('changePassword');
        Route::post('{member}/avatar', [MembersController::class, 'updateAvatar']);
        Route::delete('{member}', [MembersController::class, 'destroy'])->name('destroy');

        Route::post('store', [MembersController::class, 'store'])->name('store');



        Route::get('/duns/{dunId}', [MembersController::class, 'getDmsByDun'])->name('duns');





        Route::post('upload', [MembersUploadController::class, 'upload'])->name('upload');
        Route::get('transfer', [MembersTransferController::class, 'transfer']);






    });
    Route::prefix('parlimen')->name('parlimen.')->group(function () {
        Route::get('/', [ParlimenController::class, 'index'])->name('index');   // list page
        Route::post('/', [ParlimenController::class, 'store'])->name('store');

        Route::get('/{parlimen}/edit', [ParlimenController::class, 'edit'])->name('edit');
        Route::put('/{parlimen}', [ParlimenController::class, 'update'])->name('update');
        Route::delete('/{parlimen}', [ParlimenController::class, 'destroy'])->name('destroy');

        // Show single Parlimen (must be **after** edit/update/delete routes)
        Route::get('/{parlimen}', [ParlimenController::class, 'show'])->name('show');

        // For DataTables AJAX
        Route::post('/data', [ParlimenController::class, 'getList'])->name('data');
    });





    Route::prefix('dun')->name('dun.')->group(function () {
        Route::get('/', [DunController::class, 'index'])->name('index');   // list page
        Route::post('/', [DunController::class, 'store'])->name('store');

        Route::get('/{dun}/edit', [DunController::class, 'edit'])->name('edit');
        Route::put('/{dun}', [DunController::class, 'update'])->name('update');
        Route::delete('/{dun}', [DunController::class, 'destroy'])->name('destroy');

        // Show single Dun (must be **after** edit/update/delete routes)
        Route::get('/{dun}', [DunController::class, 'show'])->name('show');

        // For DataTables AJAX
        Route::post('/data', [DunController::class, 'getList'])->name('data');
    });


    Route::prefix('dm')->name('dm.')->group(function () {
        Route::get('/', [DmController::class, 'index'])->name('index');   // list page
        Route::post('/', [DmController::class, 'store'])->name('store');

        Route::get('/{dm}/edit', [DmController::class, 'edit'])->name('edit');
        Route::put('/{dm}', [DmController::class, 'update'])->name('update');
        Route::delete('/{dm}', [DmController::class, 'destroy'])->name('destroy');

        // Show single Dm (must be **after** edit/update/delete routes)
        Route::get('/{dm}', [DmController::class, 'show'])->name('show');

        // For DataTables AJAX
        Route::post('/data', [DmController::class, 'getList'])->name('data');
    });




    // Analytics AJAX Charts
    Route::prefix('analytics/chart')->group(function () {
        Route::post('overview', [PengundiAnalyticsController::class, 'overview']);

    });

    Route::post('analytics/pengundi', [PengundiAnalyticsController::class, 'index']);
    Route::get('analytics/pengundi2', [PengundiAnalyticsController::class, 'index']);

    // Mail
    Route::post('mail/send', [MailController::class, 'sendEmail'])->name('mail.send');
});














require __DIR__ . '/auth.php';
