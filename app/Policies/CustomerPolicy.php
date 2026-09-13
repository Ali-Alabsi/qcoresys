<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksModulePermissions;

class CustomerPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'customers';
    }
}
