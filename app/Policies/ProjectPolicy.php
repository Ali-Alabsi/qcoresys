<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksModulePermissions;

class ProjectPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'projects';
    }
}
