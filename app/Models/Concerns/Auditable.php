<?php

namespace App\Models\Concerns;

use App\Enums\AuditAction;
use App\Services\AuditService;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $model): void {
            app(AuditService::class)->logModelEvent(
                $model,
                AuditAction::Create,
                null,
                $model->getAttributes(),
            );
        });

        static::updated(function (self $model): void {
            if ($model->wasChanged()) {
                app(AuditService::class)->logModelEvent(
                    $model,
                    AuditAction::Update,
                    $model->getOriginal(),
                    $model->getChanges(),
                );
            }
        });

        static::deleted(function (self $model): void {
            $action = method_exists($model, 'trashed') && $model->trashed()
                ? AuditAction::Delete
                : AuditAction::Delete;

            app(AuditService::class)->logModelEvent(
                $model,
                $action,
                $model->getOriginal(),
                null,
            );
        });

        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(function (self $model): void {
                app(AuditService::class)->logModelEvent(
                    $model,
                    AuditAction::Restore,
                    null,
                    $model->getAttributes(),
                );
            });
        }
    }
}
