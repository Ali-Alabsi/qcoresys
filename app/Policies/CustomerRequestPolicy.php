<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksModulePermissions;

class CustomerRequestPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'customer_requests';
    }
}
