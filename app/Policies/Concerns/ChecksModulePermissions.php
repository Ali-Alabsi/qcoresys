<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksModulePermissions
{
    abstract protected function module(): string;

    protected function can(User $user, string $action): bool
    {
        return $user->hasPermission("{$this->module()}.{$action}");
    }

    /**
     * Override in policies for posted/locked financial documents.
     */
    protected function isLocked(mixed $model): bool
    {
        return false;
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->can($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create');
    }

    public function update(User $user, mixed $model): bool
    {
        if ($this->isLocked($model)) {
            return false;
        }

        return $this->can($user, 'update');
    }

    public function delete(User $user, mixed $model): bool
    {
        if ($this->isLocked($model)) {
            return false;
        }

        return $this->can($user, 'delete');
    }
}
