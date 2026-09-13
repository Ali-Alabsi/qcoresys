<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerContact extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'contact_code',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'job_title',
        'department',
        'email',
        'phone',
        'mobile',
        'whatsapp',
        'is_primary',
        'is_decision_maker',
        'is_billing_contact',
        'is_technical_contact',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_decision_maker' => 'boolean',
            'is_billing_contact' => 'boolean',
            'is_technical_contact' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Contact;
    }

    public function documentNumberColumn(): string
    {
        return 'contact_code';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(CustomerInteraction::class, 'contact_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class, 'contact_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(CustomerRequest::class, 'contact_id');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'contact_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'contact_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'contact_id');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'contact_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
