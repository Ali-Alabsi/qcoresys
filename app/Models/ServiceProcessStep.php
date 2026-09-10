<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProcessStep extends Model
{
    use HasFactory, HasLocalizedAttributes;

    protected $fillable = [
        'service_id',
        'step_number',
        'title',
        'title_ar',
        'description',
        'description_ar',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'step_number' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getLocalizedTitleAttribute(): ?string
    {
        return $this->localized('title');
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        return $this->localized('description');
    }
}
