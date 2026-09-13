<?php

namespace App\Services;

use App\Enums\DocumentSequenceKey;
use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DocumentNumberService
{
    /**
     * Preview the next document number without consuming the sequence.
     */
    public function peek(DocumentSequenceKey|string $key): string
    {
        $keyValue = $key instanceof DocumentSequenceKey ? $key->value : $key;

        $sequence = DocumentSequence::query()
            ->where('key', $keyValue)
            ->firstOrFail();

        $padded = str_pad((string) $sequence->next_number, $sequence->padding, '0', STR_PAD_LEFT);

        return sprintf('%s-%s', $sequence->prefix, $padded);
    }

    /**
     * Concurrency-safe next document number (e.g. CUS-000001).
     * Uses lockForUpdate — never count()+1.
     * When $table/$column are provided, advances the sequence past any existing numbers
     * so reseeding sequences cannot collide with leftover rows.
     */
    public function next(DocumentSequenceKey|string $key, ?string $table = null, ?string $column = null): string
    {
        $keyValue = $key instanceof DocumentSequenceKey ? $key->value : $key;

        return DB::transaction(function () use ($keyValue, $table, $column): string {
            $sequence = DocumentSequence::query()
                ->where('key', $keyValue)
                ->lockForUpdate()
                ->firstOrFail();

            if ($table && $column && Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                $this->syncSequenceForward($sequence, $table, $column);
            }

            $number = (int) $sequence->next_number;
            $sequence->increment('next_number');

            $padded = str_pad((string) $number, $sequence->padding, '0', STR_PAD_LEFT);

            return sprintf('%s-%s', $sequence->prefix, $padded);
        });
    }

    protected function syncSequenceForward(DocumentSequence $sequence, string $table, string $column): void
    {
        $prefix = $sequence->prefix.'-';
        $prefixLength = strlen($prefix);

        $maxExisting = DB::table($table)
            ->where($column, 'like', $prefix.'%')
            ->selectRaw('MAX(CAST(SUBSTR('.$column.', ?) AS INTEGER)) as max_num', [$prefixLength + 1])
            ->value('max_num');

        $maxExisting = (int) ($maxExisting ?? 0);

        if ($maxExisting >= (int) $sequence->next_number) {
            $sequence->forceFill(['next_number' => $maxExisting + 1])->save();
            $sequence->refresh();
        }
    }
}
