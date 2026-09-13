<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermissions;

class PaymentPolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'payments';
    }

    protected function isLocked(mixed $model): bool
    {
        return $model->status === PaymentStatus::Posted;
    }

    public function post(User $user, Payment $payment): bool
    {
        return $this->can($user, 'post');
    }
}
