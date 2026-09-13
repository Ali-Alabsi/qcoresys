<?php

namespace App\Models\Concerns;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['assigned_at', 'assigned_by']);
    }

    public function hasRole(string $code): bool
    {
        return $this->roles()->where('code', $code)->exists();
    }

    public function hasPermission(string $code): bool
    {
        if ($this->hasRole('SUPER_ADMIN')) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('code', $code))
            ->exists();
    }

    public function assignRole(Role|string $role, ?int $assignedBy = null): void
    {
        $roleId = $role instanceof Role
            ? $role->id
            : Role::query()->where('code', $role)->value('id');

        if (! $roleId) {
            return;
        }

        $this->roles()->syncWithoutDetaching([
            $roleId => [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy,
            ],
        ]);
    }
}
