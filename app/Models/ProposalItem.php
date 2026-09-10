<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'service_id',
        'title',
        'description',
        'deliverable',
        'quantity',
        'unit',
        'estimated_hours',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'estimated_hours' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
