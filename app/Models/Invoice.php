<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use App\Enums\InvoiceStatus;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_no',
        'customer_id',
        'quotation_id',
        'contract_id',
        'project_id',
        'invoice_date',
        'due_date',
        'currency_id',
        'exchange_rate',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'other_amount',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'payment_terms',
        'notes',
        'issued_by',
        'approved_by',
        'approved_at',
        'journal_entry_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'exchange_rate' => 'decimal:10',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'other_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Invoice;
    }

    public function documentNumberColumn(): string
    {
        return 'invoice_no';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function issuedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeWithStatus($query, InvoiceStatus|string $status)
    {
        $value = $status instanceof InvoiceStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopePosted($query)
    {
        return $query->where('status', InvoiceStatus::Posted);
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', now())
            ->where('remaining_amount', '>', 0)
            ->whereNotIn('status', [
                InvoiceStatus::Paid->value,
                InvoiceStatus::Cancelled->value,
                InvoiceStatus::Void->value,
            ]);
    }

    public function scopeOutstanding($query)
    {
        return $query->where('remaining_amount', '>', 0);
    }
}
