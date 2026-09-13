<?php

namespace App\Models\Concerns;

use App\Enums\DocumentSequenceKey;
use App\Services\DocumentNumberService;

trait HasDocumentNumber
{
    abstract public static function documentSequenceKey(): DocumentSequenceKey;

    abstract public function documentNumberColumn(): string;

    public static function bootHasDocumentNumber(): void
    {
        static::creating(function (self $model): void {
            $column = $model->documentNumberColumn();

            if (empty($model->{$column})) {
                $model->{$column} = app(DocumentNumberService::class)->next(
                    static::documentSequenceKey(),
                    $model->getTable(),
                    $column
                );
            }
        });
    }
}
