<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\ConsultationStatus;
use App\Exceptions\DomainException;
use App\Models\Consultation;
use App\Models\ConsultationParticipant;
use App\Models\CustomerContact;
use Illuminate\Support\Facades\DB;

class ConsultationService
{
    public function __construct(
        protected AuditService $auditService,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $participants
     */
    public function create(array $header, array $participants = [], ?int $createdBy = null): Consultation
    {
        return DB::transaction(function () use ($header, $participants, $createdBy) {
            if (isset($header['contact_id'], $header['customer_id'])) {
                $this->assertContactBelongsToCustomer((int) $header['contact_id'], (int) $header['customer_id']);
            }

            $consultation = Consultation::create([
                ...$header,
                'status' => $header['status'] ?? ConsultationStatus::Scheduled,
                'billable' => $header['billable'] ?? false,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            foreach ($participants as $participant) {
                ConsultationParticipant::create([
                    ...$participant,
                    'consultation_id' => $consultation->id,
                    'attended' => $participant['attended'] ?? false,
                ]);
            }

            $this->auditService->logModelEvent($consultation, AuditAction::Create);

            return $consultation->fresh(['participants', 'customer', 'contact', 'consultant']);
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
