<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksModulePermissions;

class ConsultationPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'consultations';
    }
}
