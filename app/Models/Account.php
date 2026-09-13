<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, HasLocalizedAttributes, SoftDeletes;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_name_ar',
        'account_type',
        'parent_id',
        'account_level',
        'normal_balance',
        'currency_id',
        'is_control_account',
        'is_cash_account',
        'is_bank_account',
        'is_customer_account',
        'is_vendor_account',
        'allow_posting',
        'opening_balance',
        'opening_debit',
        'opening_credit',
        'current_balance',
        'is_active',
        'description',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'account_type' => AccountType::class,
            'normal_balance' => NormalBalance::class,
            'account_level' => 'integer',
            'is_control_account' => 'boolean',
            'is_cash_account' => 'boolean',
            'is_bank_account' => 'boolean',
            'is_customer_account' => 'boolean',
            'is_vendor_account' => 'boolean',
            'allow_posting' => 'boolean',
            'opening_balance' => 'decimal:2',
            'opening_debit' => 'decimal:2',
            'opening_credit' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
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

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePostable($query)
    {
        return $query->where('allow_posting', true);
    }

    public function scopeOfType($query, AccountType|string $type)
    {
        $value = $type instanceof AccountType ? $type->value : $type;

        return $query->where('account_type', $value);
    }

    public function getLocalizedNameAttribute(): string
    {
        if (app()->getLocale() === 'ar') {
            $arabic = $this->getAttribute('account_name_ar');
            if (filled($arabic)) {
                return (string) $arabic;
            }

            $byCode = __('coa.'.$this->account_code);
            if ($byCode !== 'coa.'.$this->account_code) {
                return $byCode;
            }

            $byName = __($this->account_name);
            if ($byName !== $this->account_name) {
                return $byName;
            }
        }

        return (string) $this->getAttribute('account_name');
    }
}
