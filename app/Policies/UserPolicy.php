<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksModulePermissions;

class UserPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'users';
    }
}
