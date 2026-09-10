<?php

namespace App\Models;

use App\Enums\ServiceRequestOptionType;
use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestOption extends Model
{
    use HasFactory, HasLocalizedAttributes;

    protected $fillable = [
        'option_type',
        'code',
        'label',
        'label_ar',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'option_type' => ServiceRequestOptionType::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, ServiceRequestOptionType|string $type)
    {
        $value = $type instanceof ServiceRequestOptionType ? $type->value : $type;

        return $query->where('option_type', $value);
    }

    public function getLocalizedLabelAttribute(): ?string
    {
        return $this->localized('label');
    }
}
