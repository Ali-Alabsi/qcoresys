<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use App\Enums\OpportunityStage;
use App\Enums\OpportunityStatus;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'opportunity_no',
        'customer_id',
        'contact_id',
        'request_id',
        'title',
        'stage',
        'estimated_value',
        'currency_id',
        'expected_close_date',
        'status',
        'assigned_to',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'stage' => OpportunityStage::class,
            'status' => OpportunityStatus::class,
            'estimated_value' => 'decimal:2',
            'expected_close_date' => 'date',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Opportunity;
    }

    public function documentNumberColumn(): string
    {
        return 'opportunity_no';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class, 'contact_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(CustomerRequest::class, 'request_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', OpportunityStatus::Open);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}
