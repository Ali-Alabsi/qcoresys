<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\ProposalStatus;
use App\Exceptions\DomainException;
use App\Models\Proposal;
use App\Models\ProposalItem;
use Illuminate\Support\Facades\DB;

class ProposalService
{
    public function __construct(
        protected AuditService $auditService,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(array $header, array $items = [], ?int $createdBy = null): Proposal
    {
        return DB::transaction(function () use ($header, $items, $createdBy) {
            $proposal = Proposal::create([
                ...$header,
                'status' => $header['status'] ?? ProposalStatus::Draft,
                'prepared_by' => $header['prepared_by'] ?? $createdBy,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            foreach ($items as $index => $item) {
                ProposalItem::create([
                    ...$item,
                    'proposal_id' => $proposal->id,
                    'sort_order' => $item['sort_order'] ?? ($index + 1),
                    'quantity' => $item['quantity'] ?? 1,
                ]);
            }

            $this->auditService->logModelEvent($proposal, AuditAction::Create);

            return $proposal->fresh('items');
        });
    }

    public function approve(Proposal $proposal, int $approvedBy): Proposal
    {
        return DB::transaction(function () use ($proposal, $approvedBy) {
            $proposal = Proposal::query()->lockForUpdate()->findOrFail($proposal->id);

            if (! in_array($proposal->status, [ProposalStatus::Draft, ProposalStatus::InReview], true)) {
                throw new DomainException('Only draft or in-review proposals can be approved.');
            }

            $proposal->update([
                'status' => ProposalStatus::Approved,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'updated_by' => $approvedBy,
            ]);

            $this->auditService->logModelEvent($proposal->fresh(), AuditAction::Approve);

            return $proposal->fresh('items');
        });
    }

    public function send(Proposal $proposal): Proposal
    {
        return DB::transaction(function () use ($proposal) {
            $proposal = Proposal::query()->lockForUpdate()->findOrFail($proposal->id);

            if ($proposal->status !== ProposalStatus::Approved) {
                throw new DomainException('Only approved proposals can be sent.');
            }

            $proposal->update([
                'status' => ProposalStatus::Sent,
                'sent_at' => now(),
            ]);

            $this->auditService->logModelEvent($proposal->fresh(), AuditAction::Send);

            return $proposal->fresh('items');
        });
    }
}
