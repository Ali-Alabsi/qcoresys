<?php

namespace App\Models\Concerns;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    /**
     * In-request cache of role codes and permission codes.
     *
     * @var array{roles: list<string>, permissions: list<string>}|null
     */
    private ?array $authorizationCache = null;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['assigned_at', 'assigned_by']);
    }

    public function hasRole(string $code): bool
    {
        return in_array($code, $this->authorizationSnapshot()['roles'], true);
    }

    public function hasPermission(string $code): bool
    {
        $snapshot = $this->authorizationSnapshot();

        if (in_array('SUPER_ADMIN', $snapshot['roles'], true)) {
            return true;
        }

        return in_array($code, $snapshot['permissions'], true);
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

        $this->forgetAuthorizationCache();
    }

    /**
     * Replace all roles for the user.
     *
     * @param  Role|string|int|list<Role|string|int>  $roles
     */
    public function syncRoles(Role|string|int|array $roles, ?int $assignedBy = null): void
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $syncData = [];

        foreach ($roles as $role) {
            $roleId = match (true) {
                $role instanceof Role => $role->id,
                is_int($role) => $role,
                is_numeric($role) => (int) $role,
                default => Role::query()->where('code', $role)->value('id'),
            };

            if (! $roleId) {
                continue;
            }

            $syncData[$roleId] = [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy,
            ];
        }

        $this->roles()->sync($syncData);
        $this->forgetAuthorizationCache();
    }

    public function forgetAuthorizationCache(): void
    {
        $this->authorizationCache = null;
        $this->unsetRelation('roles');
    }

    /**
     * @return array{roles: list<string>, permissions: list<string>}
     */
    private function authorizationSnapshot(): array
    {
        if ($this->authorizationCache !== null) {
            return $this->authorizationCache;
        }

        if (! $this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        } else {
            $this->loadMissing('roles.permissions');
        }

        $roles = $this->roles;

        $this->authorizationCache = [
            'roles' => $roles->pluck('code')->all(),
            'permissions' => $roles
                ->flatMap(fn (Role $role) => $role->permissions->pluck('code'))
                ->unique()
                ->values()
                ->all(),
        ];

        return $this->authorizationCache;
    }
}
