<?php

namespace App\Services;

use App\Enums\DocumentSequenceKey;
use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Concurrency-safe next document number (e.g. CUS-000001).
     * Uses lockForUpdate — never count()+1.
     */
    public function next(DocumentSequenceKey|string $key): string
    {
        $keyValue = $key instanceof DocumentSequenceKey ? $key->value : $key;

        return DB::transaction(function () use ($keyValue): string {
            $sequence = DocumentSequence::query()
                ->where('key', $keyValue)
                ->lockForUpdate()
                ->firstOrFail();

            $number = $sequence->next_number;
            $sequence->increment('next_number');

            $padded = str_pad((string) $number, $sequence->padding, '0', STR_PAD_LEFT);

            return sprintf('%s-%s', $sequence->prefix, $padded);
        });
    }
}
