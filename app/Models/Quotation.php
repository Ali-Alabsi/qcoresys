<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use App\Enums\QuotationStatus;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_no',
        'customer_id',
        'contact_id',
        'request_id',
        'proposal_id',
        'quotation_date',
        'valid_until',
        'version',
        'parent_quotation_id',
        'status',
        'currency_id',
        'exchange_rate',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'other_amount',
        'total_amount',
        'notes',
        'terms_and_conditions',
        'payment_terms',
        'prepared_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'sent_at',
        'customer_response_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'version' => 'integer',
            'exchange_rate' => 'decimal:10',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'other_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'sent_at' => 'datetime',
            'customer_response_at' => 'datetime',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Quotation;
    }

    public function documentNumberColumn(): string
    {
        return 'quotation_no';
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

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function parentQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'parent_quotation_id');
    }

    public function childQuotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'parent_quotation_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function preparedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
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
        return $this->hasMany(QuotationItem::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function customerRequests(): HasMany
    {
        return $this->hasMany(CustomerRequest::class, 'quotation_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeWithStatus($query, QuotationStatus|string $status)
    {
        $value = $status instanceof QuotationStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeValid($query)
    {
        return $query->whereDate('valid_until', '>=', now());
    }
}
