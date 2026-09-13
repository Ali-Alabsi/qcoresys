<?php

namespace App\Policies;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermissions;

class ExpensePolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'expenses';
    }

    protected function isLocked(mixed $model): bool
    {
        return $model->status === ExpenseStatus::Posted;
    }

    public function approve(User $user, Expense $expense): bool
    {
        return $this->can($user, 'approve');
    }

    public function post(User $user, Expense $expense): bool
    {
        return $this->can($user, 'post');
    }
}
