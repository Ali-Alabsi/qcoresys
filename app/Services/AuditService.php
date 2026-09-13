<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(
        AuditAction $action,
        string $module,
        string $tableName,
        ?int $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'module' => $module,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public function logModelEvent(Model $model, AuditAction $action, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return $this->log(
            action: $action,
            module: class_basename($model),
            tableName: $model->getTable(),
            recordId: $model->getKey(),
            oldValues: $oldValues,
            newValues: $newValues,
        );
    }
}
