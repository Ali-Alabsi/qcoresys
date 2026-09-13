<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'description',
        'account_id',
        'is_project_expense',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_project_expense' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class, 'parent_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeProjectExpense($query)
    {
        return $query->where('is_project_expense', true);
    }
}
