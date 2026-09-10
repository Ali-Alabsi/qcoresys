<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerRequest extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'request_no',
        'customer_id',
        'contact_id',
        'service_id',
        'request_type',
        'subject',
        'description',
        'requirements',
        'project_type',
        'priority',
        'source',
        'received_at',
        'requested_start_date',
        'requested_end_date',
        'expected_timeline',
        'preferred_contact_method',
        'additional_notes',
        'estimated_budget',
        'currency_id',
        'assigned_to',
        'status',
        'consultation_required',
        'quotation_required',
        'quotation_id',
        'project_id',
        'closed_reason',
        'closed_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'request_type' => RequestType::class,
            'priority' => RequestPriority::class,
            'status' => RequestStatus::class,
            'received_at' => 'datetime',
            'requested_start_date' => 'date',
            'requested_end_date' => 'date',
            'estimated_budget' => 'decimal:2',
            'consultation_required' => 'boolean',
            'quotation_required' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Request;
    }

    public function documentNumberColumn(): string
    {
        return 'request_no';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class, 'contact_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'request_id');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'request_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'request_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'request_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'request_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeWithStatus($query, RequestStatus|string $status)
    {
        $value = $status instanceof RequestStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [
            RequestStatus::Closed->value,
            RequestStatus::Cancelled->value,
            RequestStatus::ConvertedToProject->value,
        ]);
    }
}
