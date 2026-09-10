<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksModulePermissions;

class VendorPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'vendors';
    }
}
