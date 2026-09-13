<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use HasFactory, HasLocalizedAttributes, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'name_ar',
        'company_name',
        'company_name_ar',
        'position',
        'position_ar',
        'content',
        'content_ar',
        'avatar',
        'rating',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getLocalizedNameAttribute(): ?string
    {
        return $this->localized('name');
    }

    public function getLocalizedCompanyNameAttribute(): ?string
    {
        return $this->localized('company_name');
    }

    public function getLocalizedPositionAttribute(): ?string
    {
        return $this->localized('position');
    }

    public function getLocalizedContentAttribute(): ?string
    {
        return $this->localized('content');
    }
}
