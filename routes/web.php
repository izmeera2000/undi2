<?php

use App\Http\Controllers\MembersController;
use App\Http\Controllers\MembersImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ElectionController;

use App\Jobs\TestQueueJob;

use App\Http\Controllers\CulaanController;
use App\Http\Controllers\CulaanPengundiImportController;

use App\Http\Controllers\PengundiImportController;
use App\Http\Controllers\PengundiAnalyticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\DunController;
use App\Http\Controllers\DmController;
use App\Http\Controllers\LokalitiController;
use App\Http\Controllers\ParlimenController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ActivityLogController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\MapController;

// --------------------------------------------------
// Public / Auth Routes
// --------------------------------------------------

Route::get('/', fn() => view('auth.login'));

Route::get('/testimport', fn() => view('testimport'))
    ->middleware(['auth', 'verified'])
    ->name('testimport');


// Returns a fresh CSRF token
Route::get('/csrf-refresh', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('csrf.refresh');







// --------------------------------------------------
// Authenticated Routes
// --------------------------------------------------

Route::middleware('auth')->group(function () {
    Route::get('/mergelokaliti', [LokalitiController::class, 'mergeDuplicates'])->name('mergeDuplicates');


    Route::post('/weather/today/{location}', [WeatherController::class, 'today'])->name('weather.today');

    Route::get('/map-page', [MapController::class, 'showPage'])->name('map.page');
    Route::get('/map', [MapController::class, 'showPage2'])->name('map.page2');
    Route::post('/fetch-map', [MapController::class, 'fetchAndStoreGeoJson'])->name('map.fetch');



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








    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

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
    })->name('clear-all');



    Route::get('/storage-link', function () {
        Artisan::call('storage:link');
        return 'Linked';
    })->name('clear-all');




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


        ///list
        Route::get('list', [PengundiAnalyticsController::class, 'list'])->name('list');
        ///ajax lsit
        Route::post('list/data', [PengundiAnalyticsController::class, 'list_data'])->name('list_data');



        Route::post('list/data/pdf', [PengundiAnalyticsController::class, 'list_data_pdf'])->name('list_data_pdf');



        ////lsit details saluran page

        Route::get(
            'list/{pr_type}/{pr_series}/{parlimen}/{dun}/{dm}/{lokaliti}/{saluran}/',
            [PengundiAnalyticsController::class, 'list_details']
        )->name('list_details');


        ///ajax before
        Route::post('list/details/data', [PengundiAnalyticsController::class, 'list_details_data'])
            ->name('list_details_data');

        ///page bulk import

        Route::get('bulkimport', [PengundiAnalyticsController::class, 'bulkimport'])->name('bulkimport');
        Route::get('bulkimport2', [PengundiAnalyticsController::class, 'bulkimport2'])->name('bulkimport2');

        ////import progress

        Route::get('importProgress', [PengundiImportController::class, 'importProgress'])
            ->name('importProgress');


        Route::get('transferProgress', [PengundiImportController::class, 'transferProgress'])
            ->name('transferProgress');



        Route::post('import', [PengundiImportController::class, 'import'])->name('import');

        Route::post('import2', [PengundiImportController::class, 'import2'])->name('import2');

        ///analytics page
        Route::get('analytics', [PengundiAnalyticsController::class, 'analytics'])->name('analytics');

        ///analytics data


        Route::post('analytics/data', [PengundiAnalyticsController::class, 'analytics_data'])->name('analytics_data');
        Route::get('analytics/test', [PengundiAnalyticsController::class, 'analytics_test'])->name('analytics_test');



        ///////////// naalytics import pdf
        Route::post('analytics/pdf', [PengundiAnalyticsController::class, 'generatePdf'])
            ->name('analytics.pdf');


        // Route::post('import/paste', [PengundiAnalyticsController::class, 'importFromPaste'])->name('import.paste');



        Route::post('list/check-pdf', [PengundiAnalyticsController::class, 'check_pdf'])
            ->name('list.check_pdf');





        Route::get('/ajax/pru-hierarchy', [PengundiAnalyticsController::class, 'getHierarchyByPru'])
            ->name('ajax.pru.hierarchy');








    });

    // All members routes
    Route::prefix('members')->name('members.')->group(function () {



        // ---------------------------
        // Members Groups Routes
        // ---------------------------
        Route::prefix('groups')->name('groups.')->group(function () {

            // CRUD resource routes for groups
            Route::get('/', [GroupController::class, 'index'])->name('index');
            Route::get('/create', [GroupController::class, 'create'])->name('create');
            Route::post('/', [GroupController::class, 'store'])->name('store');
            Route::get('/{group}', [GroupController::class, 'show'])->name('show');
            Route::get('/{group}/manage', [GroupController::class, 'manage'])->name('manage');
            Route::put('/{group}', [GroupController::class, 'update'])->name('update');
            Route::delete('/{group}', [GroupController::class, 'destroy'])->name('destroy');

            // Member management
            Route::post('{group}/invite', [GroupController::class, 'invite'])->name('invite');
            Route::delete('{group}/members/{member}', [GroupController::class, 'removeMember'])->name('removeMember');
        });

        // ---------------------------
        // Member CRUD + static routes
        // ---------------------------
        Route::get('list', [MembersController::class, 'list'])->name('list');
        Route::post('data', [MembersController::class, 'getList'])->name('data');
        Route::post('store', [MembersController::class, 'store'])->name('store');

        Route::get('bulkimport', [MembersController::class, 'bulkimport'])->name('bulkimport');
        Route::post('import', [MembersImportController::class, 'import'])->name('import');

        Route::get('importProgress', [MembersImportController::class, 'importProgress'])->name('importProgress');
        Route::get('transferProgress', [MembersImportController::class, 'transferProgress'])->name('transferProgress');

        Route::get('duns/{kod_dun}', [MembersController::class, 'getDmsByDun'])->name('duns');

        // Member-specific dynamic routes
        Route::get('{member}', [MembersController::class, 'show'])->name('show');
        Route::get('{member}/edit', [MembersController::class, 'edit'])->name('edit');
        Route::delete('{member}', [MembersController::class, 'destroy'])->name('destroy');

        Route::post('{member}/avatar', [MembersController::class, 'updateAvatar']);
        Route::post('{member}/profile', [MembersController::class, 'updateProfile'])->name('profile.update');




    });



    Route::prefix('elections')->name('elections.')->middleware('auth')->group(function () {

        Route::get('/', [ElectionController::class, 'index'])->name('index');
        Route::post('/', [ElectionController::class, 'store'])->name('store');

        // ✅ FIXED HERE
        Route::post('/list_data', [ElectionController::class, 'data'])->name('data');

        Route::put('/{election}', [ElectionController::class, 'update'])->name('update');
        Route::delete('/{election}', [ElectionController::class, 'destroy'])->name('destroy');
        Route::get('/{election}', [ElectionController::class, 'show'])->name('show');
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

        Route::post('/bulk-store', [DMController::class, 'bulkStore'])
            ->name('bulkStore');

        Route::get('/{dm}/edit', [DmController::class, 'edit'])->name('edit');
        Route::put('/{dm}', [DmController::class, 'update'])->name('update');
        Route::delete('/{dm}', [DmController::class, 'destroy'])->name('destroy');

        // Show single Dm (must be **after** edit/update/delete routes)
        Route::get('/{dm}', [DmController::class, 'show'])->name('show');

        // For DataTables AJAX
        Route::post('/data', [DmController::class, 'getList'])->name('data');
    });


    Route::prefix('lokaliti')->name('lokaliti.')->group(function () {
        Route::get('/', [LokalitiController::class, 'index'])->name('index');   // list page
        Route::post('/', [LokalitiController::class, 'store'])->name('store');
        Route::post('/bulk-store', [LokalitiController::class, 'bulkStore'])
            ->name('bulkStore');

        Route::get('/{lokaliti}/edit', [LokalitiController::class, 'edit'])->name('edit');
        Route::put('/{lokaliti}', [LokalitiController::class, 'update'])->name('update');
        Route::delete('/{lokaliti}', [LokalitiController::class, 'destroy'])->name('destroy');

        // Show single Dm (must be **after** edit/update/delete routes)
        Route::get('/{lokaliti}', [LokalitiController::class, 'show'])->name('show');

        // For DataTables AJAX
        Route::post('/data', [LokalitiController::class, 'getList'])->name('data');
    });


    Route::prefix('culaan')->name('culaan.')->group(function () {

        // Standard Culaan routes
        Route::get('/', [CulaanController::class, 'index'])->name('index');
        Route::post('/list_data', [CulaanController::class, 'data'])->name('data');
        Route::post('/', [CulaanController::class, 'store'])->name('store');


        Route::post('/import', [CulaanPengundiImportController::class, 'import'])
            ->name('culaan.import');

        // Route::get('/import-progress', [CulaanPengundiImportController::class, 'importProgress'])
        //     ->name('culaan.import.progress');



        Route::get('/{culaan}', [CulaanController::class, 'show'])->name('show');
        Route::put('/{culaan}', [CulaanController::class, 'update'])->name('update');
        Route::delete('/{culaan}', [CulaanController::class, 'destroy'])->name('destroy');
        // Route::post('/{culaan}/import', [CulaanController::class, 'import'])->name('import');

        // Nested group for Pengundi routes
        Route::prefix('{culaan}/pengundi')->name('pengundi.')->group(function () {
            // AJAX data table
            Route::post('/data', [CulaanController::class, 'pengundiData'])->name('data');

            // Store individual pengundi
            Route::post('/store', [CulaanController::class, 'storePengundi'])->name('store');

            Route::post('/updateStatus', [CulaanController::class, 'updateStatus'])->name('updateStatus');
            Route::post('/deletePengundi', [CulaanController::class, 'deletePengundi'])->name('deletePengundi');


            // Bulk import page (GET)
            Route::get('/bulkimport', [CulaanController::class, 'showBulkImport'])->name('bulkimport');
            Route::post('/import', [CulaanPengundiImportController::class, 'store'])->name('import.store');

 

        Route::get('/import-progress', [CulaanPengundiImportController::class, 'importProgress'])
            ->name('import.progress');

            // Bulk import action (POST)


        });




    });





    Route::prefix('task')->name('task.')->group(function () {

        // List all tasks (for DataTables or index page)
        Route::get('/', [TaskController::class, 'index'])->name('index');




        // Create a new task
        Route::post('/', [TaskController::class, 'store'])->name('store');

        // Edit task (get data to edit)
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');

        // Update task
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');

        // Delete task
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');

        // Show single task (after edit/update/delete)
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');

        // DataTables AJAX endpoint
        Route::post('/data', [TaskController::class, 'data'])->name('data');

        // Add subtask
        Route::post('/{task}/subtask', [TaskController::class, 'addSubtask'])->name('subtask.store');

        Route::patch('/{task}/toggle', [TaskController::class, 'toggleComplete'])->name('task.toggle');

        // Toggle subtask completion
        Route::patch('/subtask/{subtask}/toggle', [TaskController::class, 'toggleSubtask'])->name('subtask.toggle');

        // Mark task as important
        Route::patch('/{task}/important', [TaskController::class, 'markImportant'])->name('important');
    });




    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::delete('/{activity}', [ActivityLogController::class, 'destroy'])->name('destroy');
        Route::delete('/clear/all', [ActivityLogController::class, 'clear'])->name('clear');
    });







    Route::get('/test-queue', function () {
        TestQueueJob::dispatch();
        return "Job dispatched!";
    });



    // Mail
    Route::post('mail/send', [MailController::class, 'sendEmail'])->name('mail.send');
});














require __DIR__ . '/auth.php';
