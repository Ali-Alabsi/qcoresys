<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use App\Enums\ProposalStatus;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposal extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'proposal_no',
        'customer_id',
        'contact_id',
        'request_id',
        'opportunity_id',
        'title',
        'executive_summary',
        'problem_statement',
        'proposed_solution',
        'scope',
        'assumptions',
        'exclusions',
        'deliverables',
        'methodology',
        'timeline',
        'risks',
        'technical_notes',
        'status',
        'prepared_by',
        'approved_by',
        'approved_at',
        'sent_at',
        'customer_response_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProposalStatus::class,
            'approved_at' => 'datetime',
            'sent_at' => 'datetime',
            'customer_response_at' => 'datetime',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Proposal;
    }

    public function documentNumberColumn(): string
    {
        return 'proposal_no';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class, 'contact_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(CustomerRequest::class, 'request_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function preparedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProposalItem::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeWithStatus($query, ProposalStatus|string $status)
    {
        $value = $status instanceof ProposalStatus ? $status->value : $status;

        return $query->where('status', $value);
    }
}
