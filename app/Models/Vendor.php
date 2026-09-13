<?php

namespace App\Models;

use App\Enums\DocumentSequenceKey;
use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_code',
        'vendor_type',
        'name',
        'company_name',
        'tax_number',
        'commercial_register_no',
        'email',
        'phone',
        'mobile',
        'website',
        'country',
        'city',
        'address',
        'default_currency_id',
        'payment_terms_days',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'vendor_type' => VendorType::class,
            'status' => VendorStatus::class,
            'payment_terms_days' => 'integer',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Vendor;
    }

    public function documentNumberColumn(): string
    {
        return 'vendor_code';
    }

    public function defaultCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'default_currency_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function projectCosts(): HasMany
    {
        return $this->hasMany(ProjectCost::class);
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', VendorStatus::Active);
    }
}
