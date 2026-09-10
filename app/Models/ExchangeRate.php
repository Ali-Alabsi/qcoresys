<?php

namespace App\Models;

use App\Enums\ExchangeRateType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'rate_date',
        'rate_type',
        'source',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:10',
            'rate_date' => 'date',
            'rate_type' => ExchangeRateType::class,
            'is_active' => 'boolean',
        ];
    }

    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPair($query, int $fromCurrencyId, int $toCurrencyId)
    {
        return $query->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('rate_date', $date);
    }
}
