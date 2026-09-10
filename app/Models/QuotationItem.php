<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'service_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'cost_price',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'subtotal',
        'total',
        'estimated_cost',
        'estimated_profit',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'discount_percentage' => 'decimal:4',
            'discount_amount' => 'decimal:2',
            'tax_percentage' => 'decimal:4',
            'tax_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
            'estimated_profit' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
