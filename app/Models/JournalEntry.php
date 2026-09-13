<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use App\Enums\JournalStatus;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    use HasDocumentNumber, HasFactory;

    protected $fillable = [
        'entry_no',
        'entry_date',
        'posting_date',
        'reference_type',
        'reference_id',
        'description',
        'currency_id',
        'exchange_rate',
        'total_debit',
        'total_credit',
        'status',
        'is_balanced',
        'is_reversed',
        'reversed_entry_id',
        'posted_by',
        'posted_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posting_date' => 'date',
            'exchange_rate' => 'decimal:10',
            'total_debit' => 'decimal:2',
            'total_credit' => 'decimal:2',
            'status' => JournalStatus::class,
            'is_balanced' => 'boolean',
            'is_reversed' => 'boolean',
            'posted_at' => 'datetime',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Journal;
    }

    public function documentNumberColumn(): string
    {
        return 'entry_no';
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function reversedEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_entry_id');
    }

    public function reversingEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'reversed_entry_id');
    }

    public function postedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function scopePosted($query)
    {
        return $query->where('status', JournalStatus::Posted);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', JournalStatus::Draft);
    }

    public function scopeBalanced($query)
    {
        return $query->where('is_balanced', true);
    }

    public function scopeReversed($query)
    {
        return $query->where('is_reversed', true);
    }
}
