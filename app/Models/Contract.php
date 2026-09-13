<?php

namespace App\Models;

use App\Enums\ContractStatus;
use App\Enums\ContractType;
use App\Enums\DocumentSequenceKey;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_no',
        'customer_id',
        'quotation_id',
        'proposal_id',
        'title',
        'description',
        'contract_type',
        'start_date',
        'end_date',
        'signed_date',
        'contract_amount',
        'currency_id',
        'payment_terms',
        'status',
        'auto_renew',
        'renewal_period',
        'terminated_at',
        'termination_reason',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'contract_type' => ContractType::class,
            'status' => ContractStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'signed_date' => 'date',
            'contract_amount' => 'decimal:2',
            'auto_renew' => 'boolean',
            'terminated_at' => 'datetime',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Contract;
    }

    public function documentNumberColumn(): string
    {
        return 'contract_no';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
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

    public function items(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeWithStatus($query, ContractStatus|string $status)
    {
        $value = $status instanceof ContractStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', ContractStatus::Active);
    }
}
