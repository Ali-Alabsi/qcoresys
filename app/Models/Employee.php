<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_no',
        'user_id',
        'department_id',
        'first_name',
        'last_name',
        'job_title',
        'email',
        'phone',
        'hire_date',
        'employment_status',
        'salary',
        'currency_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'employment_status' => EmploymentStatus::class,
            'salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function projectCosts(): HasMany
    {
        return $this->hasMany(ProjectCost::class);
    }

    public function scopeActive($query)
    {
        return $query->where('employment_status', EmploymentStatus::Active);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
