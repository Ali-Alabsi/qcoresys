<?php

use App\Http\Controllers\Web\Portal\AuthController;
use App\Http\Controllers\Web\Portal\PdfController;
use App\Http\Controllers\Web\Portal\PortalController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.store');
        Route::get('register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('register', [AuthController::class, 'register'])->name('register.store');
    });

    Route::middleware(['auth', 'portal.customer'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('requests', [PortalController::class, 'requestsIndex'])->name('requests.index');
        Route::get('requests/create', [PortalController::class, 'requestsCreate'])->name('requests.create');
        Route::post('requests', [PortalController::class, 'requestsStore'])->name('requests.store');
        Route::get('requests/{customerRequest}', [PortalController::class, 'requestsShow'])->name('requests.show');
        Route::get('quotations', [PortalController::class, 'quotationsIndex'])->name('quotations.index');
        Route::get('quotations/{quotation}', [PortalController::class, 'quotationsShow'])->name('quotations.show');
        Route::get('quotations/{quotation}/pdf', [PdfController::class, 'quotation'])->name('quotations.pdf');
    });
});
