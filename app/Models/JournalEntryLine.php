<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'line_no',
        'account_id',
        'customer_id',
        'vendor_id',
        'project_id',
        'service_id',
        'description',
        'currency_id',
        'exchange_rate',
        'debit',
        'credit',
        'debit_base',
        'credit_base',
        'reference_no',
    ];

    protected function casts(): array
    {
        return [
            'line_no' => 'integer',
            'exchange_rate' => 'decimal:10',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'debit_base' => 'decimal:2',
            'credit_base' => 'decimal:2',
        ];
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
