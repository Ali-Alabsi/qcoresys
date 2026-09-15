<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\CustomerStatus;
use App\Exceptions\DomainException;
use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(
        protected AuditService $auditService,
        protected CustomerAccountService $customerAccountService,
    ) {}

    public function create(array $data, ?int $createdBy = null): Customer
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $customer = Customer::create([
                ...$data,
                'status' => $data['status'] ?? CustomerStatus::Lead,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            $this->customerAccountService->ensureFor($customer);

            $this->auditService->logModelEvent($customer->fresh(), AuditAction::Create);

            return $customer->fresh('account');
        });
    }

    public function update(Customer $customer, array $data, ?int $updatedBy = null): Customer
    {
        return DB::transaction(function () use ($customer, $data, $updatedBy) {
            $oldValues = $customer->getAttributes();

            $customer->update([
                ...$data,
                'updated_by' => $updatedBy ?? $customer->updated_by,
            ]);

            $fresh = $customer->fresh();
            $this->customerAccountService->ensureFor($fresh);
            $this->customerAccountService->syncName($fresh);

            $this->auditService->logModelEvent(
                $fresh->fresh(),
                AuditAction::Update,
                $oldValues,
                $fresh->fresh()->getAttributes(),
            );

            return $fresh->fresh('account');
        });
    }

    public function createContact(Customer $customer, array $data, ?int $createdBy = null): CustomerContact
    {
        return DB::transaction(function () use ($customer, $data, $createdBy) {
            if (isset($data['customer_id']) && (int) $data['customer_id'] !== $customer->id) {
                throw new DomainException('Contact customer_id does not match the provided customer.');
            }

            if (($data['is_primary'] ?? false) === true) {
                $customer->contacts()->update(['is_primary' => false]);
            }

            $fullName = $data['full_name'] ?? trim(implode(' ', array_filter([
                $data['first_name'] ?? '',
                $data['middle_name'] ?? '',
                $data['last_name'] ?? '',
            ])));

            $contact = $customer->contacts()->create([
                ...$data,
                'full_name' => $fullName !== '' ? $fullName : ($data['full_name'] ?? null),
                'is_active' => $data['is_active'] ?? true,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            $this->auditService->logModelEvent($contact, AuditAction::Create);

            return $contact->fresh();
        });
    }
}
