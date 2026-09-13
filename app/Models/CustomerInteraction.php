<?php

namespace App\Models;

use App\Enums\InteractionStatus;
use App\Enums\InteractionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomerInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'contact_id',
        'user_id',
        'interaction_type',
        'subject',
        'description',
        'interaction_date',
        'next_follow_up_at',
        'status',
        'outcome',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'interaction_type' => InteractionType::class,
            'status' => InteractionStatus::class,
            'interaction_date' => 'datetime',
            'next_follow_up_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class, 'contact_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopePendingFollowUp($query)
    {
        return $query->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<=', now());
    }

    public function scopeOfType($query, InteractionType|string $type)
    {
        $value = $type instanceof InteractionType ? $type->value : $type;

        return $query->where('interaction_type', $value);
    }
}
