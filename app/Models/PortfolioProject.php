<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PortfolioProject extends Model
{
    use HasFactory, HasLocalizedAttributes, SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'title_ar',
        'short_description',
        'short_description_ar',
        'description',
        'description_ar',
        'client_name',
        'industry',
        'industry_ar',
        'featured_image',
        'completion_date',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'completion_date' => 'date',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'portfolio_project_services')
            ->withTimestamps();
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class, 'portfolio_project_technologies')
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioProjectImage::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getLocalizedTitleAttribute(): ?string
    {
        return $this->localized('title');
    }

    public function getLocalizedShortDescriptionAttribute(): ?string
    {
        return $this->localized('short_description');
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        return $this->localized('description');
    }

    public function getLocalizedIndustryAttribute(): ?string
    {
        return $this->localized('industry');
    }
}
