<?php

namespace App\Models;

use App\Models\Concerns\HasRoles;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'employee_no',
        'department_id',
        'is_active',
        'last_login_at',
        'password_changed_at',
        'failed_login_attempts',
        'locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'failed_login_attempts' => 'integer',
            'locked_until' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function assignedCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'assigned_to');
    }

    public function portalCustomer(): HasOne
    {
        return $this->hasOne(Customer::class, 'portal_user_id');
    }

    public function assignedRequests(): HasMany
    {
        return $this->hasMany(CustomerRequest::class, 'assigned_to');
    }

    public function assignedOpportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'assigned_to');
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class, 'consultant_id');
    }

    public function consultationParticipations(): HasMany
    {
        return $this->hasMany(ConsultationParticipant::class);
    }

    public function preparedProposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'prepared_by');
    }

    public function preparedQuotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'prepared_by');
    }

    public function issuedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'issued_by');
    }

    public function receivedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(CustomerInteraction::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'assigned_to');
    }

    public function projectMemberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot(['role', 'allocation_percentage', 'start_date', 'end_date', 'is_active'])
            ->withTimestamps();
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFullNameAttribute(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim("{$this->first_name} {$this->last_name}");
        }

        return (string) $this->name;
    }
}
