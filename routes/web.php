<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengundiImportController;
use App\Http\Controllers\PengundiTransferController;
use App\Http\Controllers\MembersTransferController;
use App\Http\Controllers\MembersUploadController;
use App\Http\Controllers\PengundiAnalyticsController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\MailController;
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
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Events
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('events.index');
        Route::post('/', [EventController::class, 'store'])->name('events.store');
        Route::get('{event}', [EventController::class, 'show'])->name('events.show');
        Route::put('{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('{event}', [EventController::class, 'destroy'])->name('events.destroy');
    });

    // Calendar
    Route::get('/event', function () {
        $users = User::where('id', '!=', auth()->id())
            ->select('id', 'name')
            ->get();
        return view('calendar', compact('users'));
    })->middleware(['verified'])->name('event');

});

// --------------------------------------------------
// Verified Routes (Dashboard + Staff + Protected Features)
// --------------------------------------------------

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Staff
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::view('list', 'staff.list')->name('list');
        Route::get('{staff}/edit', [StaffController::class, 'edit'])->name('edit');
        Route::get('data', [StaffController::class, 'getStaff'])->name('data');
        Route::resource('/', StaffController::class)->except(['index']);
        Route::get('{staff}', [StaffController::class, 'show'])->name('show');
        Route::post('{user}/suspend', [StaffController::class, 'suspend'])->name('suspend');
        Route::post('{user}/activate', [StaffController::class, 'activate'])->name('activate');
        Route::post('{user}/role', [StaffController::class, 'changeRole'])->name('changeRole');
        Route::post('{user}/profile', [StaffController::class, 'updateProfile'])->name('profile.update');
        Route::post('{user}/change-password', [StaffController::class, 'changePassword'])->name('changePassword');
        Route::post('{user}/avatar', [StaffController::class, 'updateAvatar']);
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
        Route::post('upload', [MembersUploadController::class, 'upload'])->name('upload');
        Route::get('transfer', [MembersTransferController::class, 'transfer']);
    });

    // Analytics AJAX Charts
    Route::prefix('analytics/chart')->group(function () {
        Route::post('overview', [PengundiAnalyticsController::class, 'overview']);
        Route::post('jantina', [PengundiAnalyticsController::class, 'jantina']);
        Route::post('jantina2', [PengundiAnalyticsController::class, 'overviewByJantina']);
        Route::post('ahliumno', [PengundiAnalyticsController::class, 'ahliumno']);
        Route::post('ahliumno2', [PengundiAnalyticsController::class, 'overviewByAhliumno']);
        Route::post('dundm', [PengundiAnalyticsController::class, 'dundm']);
        Route::post('dundm2', [PengundiAnalyticsController::class, 'overviewByDundm']);
        Route::post('dundm2spec', [PengundiAnalyticsController::class, 'overviewByDundmSpecDun']);
        Route::post('firsttime', [PengundiAnalyticsController::class, 'overviewByFirstTime']);
    });

    Route::post('analytics/pengundi', [PengundiAnalyticsController::class, 'index']);
    Route::get('analytics/pengundi2', [PengundiAnalyticsController::class, 'index']);

    // Mail
    Route::post('mail/send', [MailController::class, 'sendEmail'])->name('mail.send');
});






Route::get('/clear-all', function () {
    Artisan::call('optimize:clear');
    return 'Cleared';
});












require __DIR__ . '/auth.php';
