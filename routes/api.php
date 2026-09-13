<?php

use App\Http\Controllers\Api\Public\PublicApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->name('api.public.')->group(function () {
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/company', [PublicApiController::class, 'company'])->name('company');
        Route::get('/service-categories', [PublicApiController::class, 'categories'])->name('categories');
        Route::get('/technologies', [PublicApiController::class, 'technologies'])->name('technologies');
        Route::get('/services', [PublicApiController::class, 'services'])->name('services.index');
        Route::get('/services/{slug}', [PublicApiController::class, 'service'])->name('services.show');
        Route::get('/portfolio', [PublicApiController::class, 'portfolio'])->name('portfolio.index');
        Route::get('/portfolio/{slug}', [PublicApiController::class, 'portfolioShow'])->name('portfolio.show');
        Route::get('/testimonials', [PublicApiController::class, 'testimonials'])->name('testimonials');
        Route::get('/faqs', [PublicApiController::class, 'faqs'])->name('faqs');
        Route::get('/request-options', [PublicApiController::class, 'requestOptions'])->name('request-options');
    });

    Route::middleware('throttle:public-leads')->group(function () {
        Route::post('/service-requests', [PublicApiController::class, 'storeServiceRequest'])->name('service-requests');
        Route::post('/consultation-requests', [PublicApiController::class, 'storeConsultation'])->name('consultation-requests');
        Route::post('/contact', [PublicApiController::class, 'storeContact'])->name('contact');
    });
});
