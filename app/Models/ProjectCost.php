<?php

namespace App\Models;

use App\Enums\CostType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'expense_id',
        'employee_id',
        'vendor_id',
        'cost_type',
        'description',
        'amount',
        'currency_id',
        'exchange_rate',
        'cost_date',
        'billable',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'cost_type' => CostType::class,
            'amount' => 'decimal:2',
            'exchange_rate' => 'decimal:10',
            'cost_date' => 'date',
            'billable' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeBillable($query)
    {
        return $query->where('billable', true);
    }

    public function scopeOfType($query, CostType|string $type)
    {
        $value = $type instanceof CostType ? $type->value : $type;

        return $query->where('cost_type', $value);
    }
}
