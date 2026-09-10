<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_no',
        'customer_id',
        'invoice_id',
        'account_id',
        'payment_method',
        'payment_date',
        'amount',
        'currency_id',
        'exchange_rate',
        'reference_no',
        'bank_reference',
        'description',
        'status',
        'received_by',
        'approved_by',
        'journal_entry_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'exchange_rate' => 'decimal:10',
            'status' => PaymentStatus::class,
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Payment;
    }

    public function documentNumberColumn(): string
    {
        return 'payment_no';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeWithStatus($query, PaymentStatus|string $status)
    {
        $value = $status instanceof PaymentStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopePosted($query)
    {
        return $query->where('status', PaymentStatus::Posted);
    }
}
