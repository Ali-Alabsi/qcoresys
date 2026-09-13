<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksModulePermissions;

class ContractPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'contracts';
    }
}
