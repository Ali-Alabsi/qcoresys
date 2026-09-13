<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use App\Enums\ExpenseStatus;
use App\Enums\PaymentMethod;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_no',
        'category_id',
        'customer_id',
        'project_id',
        'vendor_id',
        'employee_id',
        'account_id',
        'expense_date',
        'description',
        'amount',
        'tax_amount',
        'total_amount',
        'currency_id',
        'exchange_rate',
        'payment_method',
        'reference_no',
        'status',
        'is_billable',
        'billable_amount',
        'receipt_number',
        'approved_by',
        'approved_at',
        'journal_entry_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:10',
            'payment_method' => PaymentMethod::class,
            'status' => ExpenseStatus::class,
            'is_billable' => 'boolean',
            'billable_amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Expense;
    }

    public function documentNumberColumn(): string
    {
        return 'expense_no';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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

    public function projectCost(): HasOne
    {
        return $this->hasOne(ProjectCost::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeWithStatus($query, ExpenseStatus|string $status)
    {
        $value = $status instanceof ExpenseStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopePosted($query)
    {
        return $query->where('status', ExpenseStatus::Posted);
    }

    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }
}
