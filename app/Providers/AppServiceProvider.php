<?php

namespace App\Providers;

use App\Services\ApplicationSetupService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->bootstrapApplicationSetupOnce();

        RateLimiter::for('public-leads', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        View::composer('layouts.public', function ($view) {
            if (! isset($view->getData()['settings'])) {
                $view->with('settings', app(\App\Services\Public\CompanySettingsService::class)->getPublicSettings());
            }
        });

        Relation::enforceMorphMap([
            'customer' => \App\Models\Customer::class,
            'customer_contact' => \App\Models\CustomerContact::class,
            'customer_request' => \App\Models\CustomerRequest::class,
            'consultation' => \App\Models\Consultation::class,
            'proposal' => \App\Models\Proposal::class,
            'quotation' => \App\Models\Quotation::class,
            'contract' => \App\Models\Contract::class,
            'project' => \App\Models\Project::class,
            'invoice' => \App\Models\Invoice::class,
            'payment' => \App\Models\Payment::class,
            'expense' => \App\Models\Expense::class,
            'vendor' => \App\Models\Vendor::class,
            'journal_entry' => \App\Models\JournalEntry::class,
            'user' => \App\Models\User::class,
            'employee' => \App\Models\Employee::class,
            'opportunity' => \App\Models\Opportunity::class,
            'service' => \App\Models\Service::class,
            'portfolio_project' => \App\Models\PortfolioProject::class,
            'technology' => \App\Models\Technology::class,
            'faq' => \App\Models\Faq::class,
            'testimonial' => \App\Models\Testimonial::class,
        ]);
    }

    /**
     * Run the one-time database reset on the first HTTP request only.
     * Subsequent boots skip immediately via the completion lock inside the service.
     */
    private function bootstrapApplicationSetupOnce(): void
    {
        if ($this->app->environment('testing')) {
            return;
        }

        $this->app->make(ApplicationSetupService::class)->bootstrap();
    }
}
