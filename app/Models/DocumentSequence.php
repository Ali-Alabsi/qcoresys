<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'prefix',
        'next_number',
        'padding',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'key' => DocumentSequenceKey::class,
            'next_number' => 'integer',
            'padding' => 'integer',
        ];
    }

    public function scopeForKey($query, DocumentSequenceKey|string $key)
    {
        $value = $key instanceof DocumentSequenceKey ? $key->value : $key;

        return $query->where('key', $value);
    }
}
