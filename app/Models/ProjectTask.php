<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_task_id',
        'task_no',
        'title',
        'description',
        'assigned_to',
        'priority',
        'status',
        'start_date',
        'due_date',
        'completed_at',
        'estimated_hours',
        'actual_hours',
        'completion_percentage',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'completion_percentage' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'parent_task_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeWithStatus($query, TaskStatus|string $status)
    {
        $value = $status instanceof TaskStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_task_id');
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', now())
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value]);
    }
}
