<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\InteractionStatus;
use App\Exceptions\DomainException;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\CustomerInteraction;
use App\Models\CustomerNote;
use App\Models\CustomerTag;
use Illuminate\Support\Facades\DB;

class CRMService
{
    public function __construct(
        protected AuditService $auditService,
    ) {}

    public function logInteraction(Customer $customer, array $data, ?int $userId = null): CustomerInteraction
    {
        return DB::transaction(function () use ($customer, $data, $userId) {
            if (isset($data['customer_id']) && (int) $data['customer_id'] !== $customer->id) {
                throw new DomainException('Interaction customer_id does not match the provided customer.');
            }

            if (isset($data['contact_id'])) {
                $this->assertContactBelongsToCustomer((int) $data['contact_id'], $customer->id);
            }

            $interaction = CustomerInteraction::create([
                ...$data,
                'customer_id' => $customer->id,
                'user_id' => $userId ?? ($data['user_id'] ?? null),
                'status' => $data['status'] ?? InteractionStatus::Completed,
                'interaction_date' => $data['interaction_date'] ?? now(),
            ]);

            $this->auditService->logModelEvent($interaction, AuditAction::Create);

            return $interaction->fresh(['customer', 'contact', 'user']);
        });
    }

    public function createNote(Customer $customer, array $data, ?int $userId = null): CustomerNote
    {
        return DB::transaction(function () use ($customer, $data, $userId) {
            if (isset($data['customer_id']) && (int) $data['customer_id'] !== $customer->id) {
                throw new DomainException('Note customer_id does not match the provided customer.');
            }

            if (isset($data['contact_id'])) {
                $this->assertContactBelongsToCustomer((int) $data['contact_id'], $customer->id);
            }

            $note = CustomerNote::create([
                ...$data,
                'customer_id' => $customer->id,
                'user_id' => $userId ?? ($data['user_id'] ?? null),
                'is_pinned' => $data['is_pinned'] ?? false,
            ]);

            $this->auditService->logModelEvent($note, AuditAction::Create);

            return $note->fresh(['customer', 'contact', 'user']);
        });
    }

    public function attachTag(Customer $customer, CustomerTag|int $tag): Customer
    {
        return DB::transaction(function () use ($customer, $tag) {
            $tagModel = $tag instanceof CustomerTag
                ? $tag
                : CustomerTag::query()->findOrFail($tag);

            if (! $tagModel->is_active) {
                throw new DomainException('Inactive tags cannot be attached to customers.');
            }

            $customer->tags()->syncWithoutDetaching([$tagModel->id]);

            $this->auditService->log(
                AuditAction::Update,
                'Customer',
                $customer->getTable(),
                $customer->id,
                null,
                ['tag_id' => $tagModel->id, 'action' => 'attach_tag'],
            );

            return $customer->fresh('tags');
        });
    }

    protected function assertContactBelongsToCustomer(int $contactId, int $customerId): void
    {
        $exists = CustomerContact::query()
            ->where('id', $contactId)
            ->where('customer_id', $customerId)
            ->exists();

        if (! $exists) {
            throw new DomainException('Contact does not belong to the specified customer.');
        }
    }
}
