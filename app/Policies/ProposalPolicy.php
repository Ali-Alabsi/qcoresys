<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermissions;

class ProposalPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'proposals';
    }

    public function approve(User $user, Proposal $proposal): bool
    {
        return $this->can($user, 'approve');
    }

    public function send(User $user, Proposal $proposal): bool
    {
        return $this->can($user, 'send');
    }
}
