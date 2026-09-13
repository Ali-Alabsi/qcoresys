<?php

use App\Http\Controllers\Web\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{locale}', [PublicSiteController::class, 'switchLocale'])->name('locale.switch');

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/services', [PublicSiteController::class, 'services'])->name('services.index');
Route::get('/services/{slug}', [PublicSiteController::class, 'serviceShow'])->name('services.show');
Route::get('/services/{slug}/request', [PublicSiteController::class, 'serviceRequestForm'])->name('services.request');
Route::post('/services/{slug}/request', [PublicSiteController::class, 'storeServiceRequest'])
    ->middleware('throttle:public-leads')
    ->name('services.request.store');
Route::post('/service-requests', [PublicSiteController::class, 'storeServiceRequest'])
    ->middleware('throttle:public-leads')
    ->name('service-requests.store');

Route::get('/consultation', [PublicSiteController::class, 'consultation'])->name('consultation');
Route::post('/consultation', [PublicSiteController::class, 'storeConsultation'])
    ->middleware('throttle:public-leads')
    ->name('consultation.store');

Route::get('/request/confirmation', [PublicSiteController::class, 'confirmation'])->name('request.confirmation');

Route::get('/solutions', [PublicSiteController::class, 'solutions'])->name('solutions');
Route::get('/portfolio', [PublicSiteController::class, 'portfolio'])->name('portfolio.index');
Route::get('/portfolio/{slug}', [PublicSiteController::class, 'portfolioShow'])->name('portfolio.show');
Route::get('/about', [PublicSiteController::class, 'about'])->name('about');
Route::get('/contact', [PublicSiteController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicSiteController::class, 'storeContact'])
    ->middleware('throttle:public-leads')
    ->name('contact.store');
