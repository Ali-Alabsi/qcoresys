<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioProjectImage extends Model
{
    use HasFactory, HasLocalizedAttributes;

    protected $fillable = [
        'portfolio_project_id',
        'image_path',
        'alt_text',
        'alt_text_ar',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(PortfolioProject::class, 'portfolio_project_id');
    }

    public function getLocalizedAltTextAttribute(): ?string
    {
        return $this->localized('alt_text');
    }
}
