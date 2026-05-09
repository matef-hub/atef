<?php

use App\Http\Controllers\CaseHearingController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\LegalCaseController;
use App\Http\Controllers\LegalReportController;
use App\Http\Controllers\RentController;
use App\Http\Controllers\SystemSettingsController;
use Illuminate\Support\Facades\Route;

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/legal-reports', [LegalReportController::class, 'index'])->name('legal-reports.index');

    Route::prefix('system-settings')->name('system-settings.')->group(function () {
        Route::get('/', [SystemSettingsController::class, 'index'])->name('index');
        Route::put('/general', [SystemSettingsController::class, 'updateGeneral'])->name('general.update');
        Route::put('/services', [SystemSettingsController::class, 'updateServices'])->name('services.update');
        Route::put('/database', [SystemSettingsController::class, 'updateDatabase'])->name('database.update');
        Route::post('/database/create', [SystemSettingsController::class, 'createDatabase'])->name('database.create');
        Route::post('/database/fresh', [SystemSettingsController::class, 'freshDatabase'])->name('database.fresh');
        Route::post('/backup', [SystemSettingsController::class, 'backup'])->name('backup.create');
        Route::post('/backup/restore', [SystemSettingsController::class, 'restore'])->name('backup.restore');
        Route::get('/backup/{file}/download', [SystemSettingsController::class, 'downloadBackup'])
            ->where('file', '[^/]+')
            ->name('backup.download');
        Route::delete('/backup/{file}', [SystemSettingsController::class, 'deleteBackup'])
            ->where('file', '[^/]+')
            ->name('backup.delete');
        Route::post('/maintenance', [SystemSettingsController::class, 'runMaintenance'])->name('maintenance.run');
    });

    Route::resource('cases', LegalCaseController::class);
    Route::resource('hearings', CaseHearingController::class);
    Route::resource('contracts', ContractController::class);
    Route::resource('documents', DocumentController::class);
    Route::resource('rents', RentController::class);
});
