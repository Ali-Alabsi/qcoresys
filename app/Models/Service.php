<?php

namespace App\Models;

use App\Enums\BillingType;
use App\Enums\PricingType;
use App\Enums\RecurringPeriod;
use App\Enums\ServiceType;
use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, HasLocalizedAttributes, SoftDeletes;

    protected $fillable = [
        'service_code',
        'slug',
        'category_id',
        'name',
        'name_ar',
        'short_name',
        'short_description',
        'short_description_ar',
        'description',
        'description_ar',
        'overview',
        'overview_ar',
        'icon',
        'image',
        'banner_image',
        'service_type',
        'billing_type',
        'pricing_type',
        'unit',
        'default_quantity',
        'default_price',
        'starting_price',
        'cost_price',
        'currency_id',
        'tax_rate',
        'estimated_hours',
        'is_consulting',
        'is_project_service',
        'is_recurring',
        'recurring_period',
        'is_active',
        'is_public',
        'is_featured',
        'sort_order',
        'notes',
        'meta_title',
        'meta_title_ar',
        'meta_description',
        'meta_description_ar',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'service_type' => ServiceType::class,
            'billing_type' => BillingType::class,
            'pricing_type' => PricingType::class,
            'default_quantity' => 'decimal:4',
            'default_price' => 'decimal:2',
            'starting_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'tax_rate' => 'decimal:4',
            'estimated_hours' => 'decimal:2',
            'is_consulting' => 'boolean',
            'is_project_service' => 'boolean',
            'is_recurring' => 'boolean',
            'recurring_period' => RecurringPeriod::class,
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class, 'service_technology')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function features(): HasMany
    {
        return $this->hasMany(ServiceFeature::class)->orderBy('sort_order');
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(ServiceBenefit::class)->orderBy('sort_order');
    }

    public function processSteps(): HasMany
    {
        return $this->hasMany(ServiceProcessStep::class)->orderBy('sort_order');
    }

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(Faq::class, 'service_faqs')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function portfolioProjects(): BelongsToMany
    {
        return $this->belongsToMany(PortfolioProject::class, 'portfolio_project_services')
            ->withTimestamps();
    }

    public function customerRequests(): HasMany
    {
        return $this->hasMany(CustomerRequest::class);
    }

    public function proposalItems(): HasMany
    {
        return $this->hasMany(ProposalItem::class);
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function contractItems(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function projectServices(): HasMany
    {
        return $this->hasMany(ProjectService::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true)->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeConsulting($query)
    {
        return $query->where('is_consulting', true);
    }

    public function scopeProjectService($query)
    {
        return $query->where('is_project_service', true);
    }

    public function getLocalizedNameAttribute(): ?string
    {
        return $this->localized('name');
    }

    public function getLocalizedShortDescriptionAttribute(): ?string
    {
        return $this->localized('short_description');
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        return $this->localized('description');
    }

    public function getLocalizedOverviewAttribute(): ?string
    {
        return $this->localized('overview');
    }

    public function getLocalizedMetaTitleAttribute(): ?string
    {
        return $this->localized('meta_title');
    }

    public function getLocalizedMetaDescriptionAttribute(): ?string
    {
        return $this->localized('meta_description');
    }
}
