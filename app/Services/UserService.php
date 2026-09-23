<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Exceptions\DomainException;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(
        protected AuditService $auditService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?int $assignedBy = null): User
    {
        return DB::transaction(function () use ($data, $assignedBy) {
            $roleId = (int) $data['role_id'];
            $payload = Arr::except($data, ['role_id', 'password_confirmation']);

            $user = User::query()->create([
                ...$payload,
                'is_active' => (bool) ($payload['is_active'] ?? true),
                'password_changed_at' => now(),
            ]);

            $user->syncRoles([$roleId], $assignedBy);

            $this->auditService->logModelEvent($user->fresh(['roles']), AuditAction::Create);

            return $user->fresh(['roles', 'department']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data, ?int $assignedBy = null): User
    {
        return DB::transaction(function () use ($user, $data, $assignedBy) {
            $roleId = (int) $data['role_id'];
            $payload = Arr::except($data, ['role_id', 'password_confirmation']);

            if (empty($payload['password'])) {
                unset($payload['password']);
            } else {
                $payload['password_changed_at'] = now();
            }

            $payload['is_active'] = (bool) ($payload['is_active'] ?? false);

            if ($user->hasRole('SUPER_ADMIN')) {
                $superAdminRoleId = Role::query()->where('code', 'SUPER_ADMIN')->value('id');
                $losingSuperAdmin = (int) $superAdminRoleId !== $roleId;
                $deactivating = $payload['is_active'] === false;

                if ($losingSuperAdmin || $deactivating) {
                    $this->assertCanLoseSuperAdmin($user);
                }
            }

            $oldValues = $user->getAttributes();

            $user->update($payload);
            $user->syncRoles([$roleId], $assignedBy);

            $fresh = $user->fresh(['roles', 'department']);

            $this->auditService->logModelEvent(
                $fresh,
                AuditAction::Update,
                $oldValues,
                $fresh->getAttributes(),
            );

            return $fresh;
        });
    }

    public function delete(User $user, ?int $actorId = null): void
    {
        if ($actorId !== null && (int) $user->id === $actorId) {
            throw new DomainException(__('You cannot delete your own account.'));
        }

        if ($user->hasRole('SUPER_ADMIN')) {
            $this->assertCanLoseSuperAdmin($user);
        }

        DB::transaction(function () use ($user) {
            $oldValues = $user->getAttributes();
            $user->delete();
            $this->auditService->logModelEvent($user, AuditAction::Delete, $oldValues);
        });
    }

    private function assertCanLoseSuperAdmin(User $user): void
    {
        $hasOther = User::query()
            ->whereKeyNot($user->id)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('roles.code', 'SUPER_ADMIN'))
            ->exists();

        if (! $hasOther) {
            throw new DomainException(__('Cannot remove the last super administrator.'));
        }
    }
}
