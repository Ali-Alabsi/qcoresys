<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\ChecksModulePermissions;

class InvoicePolicy
{
    use ChecksModulePermissions;

    protected function module(): string
    {
        return 'invoices';
    }

    protected function isLocked(mixed $model): bool
    {
        return $model->status === InvoiceStatus::Posted
            || $model->status === InvoiceStatus::Paid
            || $model->status === InvoiceStatus::PartiallyPaid
            || $model->status === InvoiceStatus::Void;
    }

    public function approve(User $user, Invoice $invoice): bool
    {
        return $this->can($user, 'approve');
    }

    public function post(User $user, Invoice $invoice): bool
    {
        return $this->can($user, 'post');
    }
}
