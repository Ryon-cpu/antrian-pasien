<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\OfficerLoginController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\OfficerDashboardController;
use App\Http\Controllers\QueueDisplayController;
use App\Http\Controllers\QueueHistoryController;
use App\Http\Controllers\QueueRegistrationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/ambil-antrean', [QueueRegistrationController::class, 'create'])->name('queues.create');
Route::post('/ambil-antrean', [QueueRegistrationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('queues.store');
Route::get('/tiket/{uniqueCode}', [QueueRegistrationController::class, 'show'])
    ->where('uniqueCode', '[A-Za-z0-9]{32}')
    ->name('queues.receipt');
Route::get('/layar-antrean', [QueueDisplayController::class, 'index'])->name('queues.display');

Route::middleware('guest')->group(function () {
    Route::get('/login', [OfficerLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [OfficerLoginController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [OfficerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/riwayat-antrean', [QueueHistoryController::class, 'index'])->name('queues.history');
    Route::resource('dokter', DoctorController::class)
        ->parameters(['dokter' => 'doctor'])
        ->except(['show', 'destroy'])
        ->names('doctors');
    Route::post('/dashboard/queues/call-next', [OfficerDashboardController::class, 'callNext'])
        ->name('queues.call-next');
    Route::post('/dashboard/queues/{queue}/complete', [OfficerDashboardController::class, 'complete'])
        ->name('queues.complete');
    Route::post('/dashboard/queues/{queue}/cancel', [OfficerDashboardController::class, 'cancel'])
        ->name('queues.cancel');
    Route::post('/logout', [OfficerLoginController::class, 'logout'])->name('logout');
});
