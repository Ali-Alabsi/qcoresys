<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermissions;

class QuotationPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'quotations';
    }

    public function approve(User $user, Quotation $quotation): bool
    {
        return $this->can($user, 'approve');
    }

    public function send(User $user, Quotation $quotation): bool
    {
        return $this->can($user, 'send');
    }
}
