<?php

namespace App\Models;

use App\Enums\ProjectServiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectService extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'service_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total_amount',
        'estimated_hours',
        'actual_hours',
        'estimated_cost',
        'actual_cost',
        'estimated_profit',
        'actual_profit',
        'status',
        'start_date',
        'end_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'estimated_profit' => 'decimal:2',
            'actual_profit' => 'decimal:2',
            'status' => ProjectServiceStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeWithStatus($query, ProjectServiceStatus|string $status)
    {
        $value = $status instanceof ProjectServiceStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
