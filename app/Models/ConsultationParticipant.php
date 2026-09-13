<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'user_id',
        'role',
        'attended',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'attended' => 'boolean',
        ];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAttended($query)
    {
        return $query->where('attended', true);
    }
}
