<?php

namespace App\Models;

use App\Enums\ConsultationStatus;
use App\Enums\ConsultationType;
use App\Enums\DocumentSequenceKey;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'consultation_no',
        'customer_id',
        'contact_id',
        'request_id',
        'consultant_id',
        'consultation_type',
        'title',
        'description',
        'objectives',
        'findings',
        'recommendations',
        'technical_requirements',
        'risks',
        'estimated_effort_hours',
        'consultation_date',
        'follow_up_date',
        'status',
        'billable',
        'amount',
        'currency_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'consultation_type' => ConsultationType::class,
            'status' => ConsultationStatus::class,
            'estimated_effort_hours' => 'decimal:2',
            'consultation_date' => 'date',
            'follow_up_date' => 'date',
            'billable' => 'boolean',
            'amount' => 'decimal:2',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Consultation;
    }

    public function documentNumberColumn(): string
    {
        return 'consultation_no';
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

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConsultationParticipant::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeWithStatus($query, ConsultationStatus|string $status)
    {
        $value = $status instanceof ConsultationStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeBillable($query)
    {
        return $query->where('billable', true);
    }
}
