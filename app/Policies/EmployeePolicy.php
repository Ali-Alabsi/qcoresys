<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksModulePermissions;

class EmployeePolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'employees';
    }
}
