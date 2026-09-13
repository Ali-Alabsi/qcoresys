<?php

namespace App\Policies;

use App\Enums\JournalStatus;
use App\Models\JournalEntry;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermissions;

class JournalEntryPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'journals';
    }

    protected function isLocked(mixed $model): bool
    {
        return in_array($model->status, [JournalStatus::Posted, JournalStatus::Reversed], true);
    }

    public function post(User $user, JournalEntry $journalEntry): bool
    {
        return $this->can($user, 'post');
    }

    public function reverse(User $user, JournalEntry $journalEntry): bool
    {
        return $this->can($user, 'reverse');
    }
}
