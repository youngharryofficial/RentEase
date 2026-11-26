<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// M-Pesa: Safaricom calls this URL (no login, CSRF-exempt in bootstrap/app.php)
Route::post('/mpesa/callback', [PaymentController::class, 'callback'])->name('mpesa.callback');

// Tenant: pay rent
Route::middleware(['auth', RoleMiddleware::class . ':tenant'])->group(function () {
    Route::get('/invoices/{invoice}/pay', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/invoices/{invoice}/pay', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/status', [PaymentController::class, 'status'])->name('payments.status');

    // Local testing helper only (returns 404 outside the local environment)
    Route::get('/dev/mpesa/simulate/{payment}/{result?}', [PaymentController::class, 'simulate'])
        ->name('dev.mpesa.simulate');
});

// Admin + landlord: manage everything
Route::middleware(['auth', RoleMiddleware::class . ':admin,landlord'])->group(function () {
    Route::resource('properties', PropertyController::class)->except('show');
    Route::resource('units', UnitController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
    Route::resource('leases', LeaseController::class)->except('show');

    Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::resource('invoices', InvoiceController::class)->except('show');

    Route::get('/payments', [PaymentLogController::class, 'index'])->name('payments.index');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{type}', [ReportController::class, 'show'])
        ->whereIn('type', ['collections', 'arrears', 'occupancy'])
        ->name('reports.show');
});

// Maintenance requests
Route::middleware('auth')->group(function () {
    Route::get('/maintenance', [MaintenanceRequestController::class, 'index'])->name('maintenance.index');

    Route::middleware(RoleMiddleware::class . ':tenant')->group(function () {
        Route::get('/maintenance/create', [MaintenanceRequestController::class, 'create'])->name('maintenance.create');
        Route::post('/maintenance', [MaintenanceRequestController::class, 'store'])->name('maintenance.store');
    });

    Route::middleware(RoleMiddleware::class . ':admin,landlord')->group(function () {
        Route::patch('/maintenance/{maintenance}', [MaintenanceRequestController::class, 'update'])->name('maintenance.update');
        Route::delete('/maintenance/{maintenance}', [MaintenanceRequestController::class, 'destroy'])->name('maintenance.destroy');
    });
});

require __DIR__.'/auth.php';
