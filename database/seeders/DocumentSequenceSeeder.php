<?php

namespace Database\Seeders;

use App\Enums\DocumentSequenceKey;
use App\Models\DocumentSequence;
use Illuminate\Database\Seeder;

class DocumentSequenceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DocumentSequenceKey::cases() as $key) {
            DocumentSequence::query()->firstOrCreate(
                ['key' => $key->value],
                [
                    'prefix' => $key->value,
                    'next_number' => 1,
                    'padding' => 6,
                    'description' => str_replace('_', ' ', strtolower($key->name)),
                ]
            );
        }
    }
}
