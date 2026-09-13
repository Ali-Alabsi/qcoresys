<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'table_name',
        'record_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeForRecord($query, string $tableName, int $recordId)
    {
        return $query->where('table_name', $tableName)
            ->where('record_id', $recordId);
    }

    public function scopeWithAction($query, AuditAction|string $action)
    {
        $value = $action instanceof AuditAction ? $action->value : $action;

        return $query->where('action', $value);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
