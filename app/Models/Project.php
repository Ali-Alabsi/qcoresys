<?php

namespace App\Models;

use App\Enums\BillingType;
use App\Enums\DocumentSequenceKey;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Models\Concerns\HasDocumentNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasDocumentNumber, HasFactory, SoftDeletes;

    protected $fillable = [
        'project_no',
        'customer_id',
        'quotation_id',
        'contract_id',
        'request_id',
        'name',
        'description',
        'project_type',
        'status',
        'priority',
        'project_manager_id',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'budget_amount',
        'contract_amount',
        'currency_id',
        'estimated_cost',
        'actual_cost',
        'estimated_revenue',
        'actual_revenue',
        'estimated_profit',
        'actual_profit',
        'completion_percentage',
        'billing_type',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'project_type' => ProjectType::class,
            'status' => ProjectStatus::class,
            'priority' => ProjectPriority::class,
            'billing_type' => BillingType::class,
            'start_date' => 'date',
            'expected_end_date' => 'date',
            'actual_end_date' => 'date',
            'budget_amount' => 'decimal:2',
            'contract_amount' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'estimated_revenue' => 'decimal:2',
            'actual_revenue' => 'decimal:2',
            'estimated_profit' => 'decimal:2',
            'actual_profit' => 'decimal:2',
            'completion_percentage' => 'decimal:2',
        ];
    }

    public static function documentSequenceKey(): DocumentSequenceKey
    {
        return DocumentSequenceKey::Project;
    }

    public function documentNumberColumn(): string
    {
        return 'project_no';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(CustomerRequest::class, 'request_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
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

    public function projectServices(): HasMany
    {
        return $this->hasMany(ProjectService::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(ProjectCost::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function team(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot(['role', 'allocation_percentage', 'start_date', 'end_date', 'is_active'])
            ->withTimestamps();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function customerRequests(): HasMany
    {
        return $this->hasMany(CustomerRequest::class, 'project_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeWithStatus($query, ProjectStatus|string $status)
    {
        $value = $status instanceof ProjectStatus ? $status->value : $status;

        return $query->where('status', $value);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            ProjectStatus::Planning->value,
            ProjectStatus::InProgress->value,
            ProjectStatus::OnHold->value,
        ]);
    }

    public function scopeManagedBy($query, int $userId)
    {
        return $query->where('project_manager_id', $userId);
    }
}
