<?php

use App\Http\Controllers\Web\Admin\AuthController;
use App\Http\Controllers\Web\Admin\PdfController;
use App\Http\Controllers\Web\Admin\PlatformController;
use App\Http\Controllers\Web\Admin\SetupController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [PlatformController::class, 'dashboard'])->middleware('permission:customers.view')->name('dashboard');

        Route::get('customers', [PlatformController::class, 'customersIndex'])->middleware('permission:customers.view')->name('customers.index');
        Route::get('customers/create', [PlatformController::class, 'customersCreate'])->middleware('permission:customers.create')->name('customers.create');
        Route::post('customers', [PlatformController::class, 'customersStore'])->middleware('permission:customers.create')->name('customers.store');
        Route::get('customers/{customer}', [PlatformController::class, 'customersShow'])->middleware('permission:customers.view')->name('customers.show');
        Route::get('customers/{customer}/edit', [PlatformController::class, 'customersEdit'])->middleware('permission:customers.update')->name('customers.edit');
        Route::put('customers/{customer}', [PlatformController::class, 'customersUpdate'])->middleware('permission:customers.update')->name('customers.update');

        Route::get('customer-requests', [PlatformController::class, 'requestsIndex'])->middleware('permission:customer_requests.view')->name('customer-requests.index');
        Route::get('customer-requests/create', [PlatformController::class, 'requestsCreate'])->middleware('permission:customer_requests.create')->name('customer-requests.create');
        Route::post('customer-requests', [PlatformController::class, 'requestsStore'])->middleware('permission:customer_requests.create')->name('customer-requests.store');
        Route::get('customer-requests/{customerRequest}', [PlatformController::class, 'requestsShow'])->middleware('permission:customer_requests.view')->name('customer-requests.show');
        Route::get('customer-requests/{customerRequest}/edit', [PlatformController::class, 'requestsEdit'])->middleware('permission:customer_requests.update')->name('customer-requests.edit');
        Route::put('customer-requests/{customerRequest}', [PlatformController::class, 'requestsUpdate'])->middleware('permission:customer_requests.update')->name('customer-requests.update');

        Route::get('quotations', [PlatformController::class, 'quotationsIndex'])->middleware('permission:quotations.view')->name('quotations.index');
        Route::get('quotations/create', [PlatformController::class, 'quotationsCreate'])->middleware('permission:quotations.create')->name('quotations.create');
        Route::post('quotations', [PlatformController::class, 'quotationsStore'])->middleware('permission:quotations.create')->name('quotations.store');
        Route::get('quotations/{quotation}', [PlatformController::class, 'quotationsShow'])->middleware('permission:quotations.view')->name('quotations.show');
        Route::get('quotations/{quotation}/edit', [PlatformController::class, 'quotationsEdit'])->middleware('permission:quotations.update')->name('quotations.edit');
        Route::put('quotations/{quotation}', [PlatformController::class, 'quotationsUpdate'])->middleware('permission:quotations.update')->name('quotations.update');
        Route::post('quotations/{quotation}/approve', [PlatformController::class, 'quotationApprove'])->middleware('permission:quotations.approve')->name('quotations.approve');
        Route::post('quotations/{quotation}/send', [PlatformController::class, 'quotationSend'])->middleware('permission:quotations.send')->name('quotations.send');
        Route::get('quotations/{quotation}/pdf', [PdfController::class, 'quotation'])->middleware('permission:quotations.view')->name('quotations.pdf');

        Route::get('invoices', [PlatformController::class, 'invoicesIndex'])->middleware('permission:invoices.view')->name('invoices.index');
        Route::get('invoices/create', [PlatformController::class, 'invoicesCreate'])->middleware('permission:invoices.create')->name('invoices.create');
        Route::post('invoices', [PlatformController::class, 'invoicesStore'])->middleware('permission:invoices.create')->name('invoices.store');
        Route::get('invoices/{invoice}', [PlatformController::class, 'invoicesShow'])->middleware('permission:invoices.view')->name('invoices.show');
        Route::get('invoices/{invoice}/edit', [PlatformController::class, 'invoicesEdit'])->middleware('permission:invoices.update')->name('invoices.edit');
        Route::put('invoices/{invoice}', [PlatformController::class, 'invoicesUpdate'])->middleware('permission:invoices.update')->name('invoices.update');
        Route::post('invoices/{invoice}/approve', [PlatformController::class, 'invoiceApprove'])->middleware('permission:invoices.approve')->name('invoices.approve');
        Route::post('invoices/{invoice}/post', [PlatformController::class, 'invoicePost'])->middleware('permission:invoices.post')->name('invoices.post');
        Route::get('invoices/{invoice}/pdf', [PdfController::class, 'invoice'])->middleware('permission:invoices.view')->name('invoices.pdf');

        Route::get('payments', [PlatformController::class, 'paymentsIndex'])->middleware('permission:payments.view')->name('payments.index');
        Route::get('payments/create', [PlatformController::class, 'paymentsCreate'])->middleware('permission:payments.create')->name('payments.create');
        Route::post('payments', [PlatformController::class, 'paymentsStore'])->middleware('permission:payments.create')->name('payments.store');
        Route::post('payments/{payment}/post', [PlatformController::class, 'paymentPost'])->middleware('permission:payments.post')->name('payments.post');

        Route::get('accounts', [PlatformController::class, 'accountsIndex'])->middleware('permission:accounts.view')->name('accounts.index');
        Route::get('accounts/create', [PlatformController::class, 'accountsCreate'])->middleware('permission:accounts.create')->name('accounts.create');
        Route::post('accounts', [PlatformController::class, 'accountsStore'])->middleware('permission:accounts.create')->name('accounts.store');
        Route::get('accounts/{account}/ledger', [PlatformController::class, 'accountsLedger'])->middleware('permission:accounts.view')->name('accounts.ledger');
        Route::get('accounts/{account}/ledger/pdf', [PlatformController::class, 'accountsLedgerPdf'])->middleware('permission:accounts.view')->name('accounts.ledger.pdf');
        Route::get('accounts/{account}/ledger/excel', [PlatformController::class, 'accountsLedgerExcel'])->middleware('permission:accounts.view')->name('accounts.ledger.excel');

        Route::get('journals', [PlatformController::class, 'journalsIndex'])->middleware('permission:journals.view')->name('journals.index');
        Route::get('journals/create', [PlatformController::class, 'journalsCreate'])->middleware('permission:journals.create')->name('journals.create');
        Route::post('journals', [PlatformController::class, 'journalsStore'])->middleware('permission:journals.create')->name('journals.store');
        Route::get('journals/{journal}', [PlatformController::class, 'journalsShow'])->middleware('permission:journals.view')->name('journals.show');
        Route::get('journals/{journal}/attachments/{attachment}', [PlatformController::class, 'journalAttachmentDownload'])->middleware('permission:journals.view')->name('journals.attachments.download');
        Route::post('journals/{journal}/post', [PlatformController::class, 'journalPost'])->middleware('permission:journals.post')->name('journals.post');

        Route::get('services', [PlatformController::class, 'servicesIndex'])->middleware('permission:services_catalog.view')->name('services.index');
        Route::get('services/{service}/edit', [PlatformController::class, 'servicesEdit'])->middleware('permission:services_catalog.update')->name('services.edit');
        Route::put('services/{service}', [PlatformController::class, 'servicesUpdate'])->middleware('permission:services_catalog.update')->name('services.update');

        Route::get('portfolio-projects', [PlatformController::class, 'portfolioIndex'])->middleware('permission:portfolio.view')->name('portfolio-projects.index');
        Route::get('portfolio-projects/create', [PlatformController::class, 'portfolioCreate'])->middleware('permission:portfolio.create')->name('portfolio-projects.create');
        Route::post('portfolio-projects', [PlatformController::class, 'portfolioStore'])->middleware('permission:portfolio.create')->name('portfolio-projects.store');
        Route::get('portfolio-projects/{portfolioProject}/edit', [PlatformController::class, 'portfolioEdit'])->middleware('permission:portfolio.update')->name('portfolio-projects.edit');
        Route::put('portfolio-projects/{portfolioProject}', [PlatformController::class, 'portfolioUpdate'])->middleware('permission:portfolio.update')->name('portfolio-projects.update');

        Route::get('settings', [PlatformController::class, 'settingsEdit'])->middleware('permission:settings.view')->name('settings.edit');
        Route::put('settings', [PlatformController::class, 'settingsUpdate'])->middleware('permission:settings.update')->name('settings.update');

        Route::get('setup', [SetupController::class, 'index'])->middleware('permission:settings.view')->name('setup.index');
        Route::post('setup/accounts', [SetupController::class, 'accounts'])->middleware('permission:settings.update')->name('setup.accounts');
        Route::post('setup/portfolio', [SetupController::class, 'portfolio'])->middleware('permission:settings.update')->name('setup.portfolio');
    });
});
