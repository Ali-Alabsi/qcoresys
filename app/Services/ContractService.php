<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\ContractStatus;
use App\Enums\QuotationStatus;
use App\Exceptions\DomainException;
use App\Models\Contract;
use App\Models\ContractItem;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function __construct(
        protected AuditService $auditService,
    ) {}

    public function createFromQuotation(Quotation $quotation, array $overrides = [], ?int $createdBy = null): Contract
    {
        return DB::transaction(function () use ($quotation, $overrides, $createdBy) {
            $quotation = Quotation::query()->lockForUpdate()->with('items')->findOrFail($quotation->id);

            if (! in_array($quotation->status, [QuotationStatus::Accepted, QuotationStatus::Approved], true)) {
                throw new DomainException('Contracts can only be created from approved or accepted quotations.');
            }

            if ($quotation->items->isEmpty()) {
                throw new DomainException('Quotation has no items to convert into a contract.');
            }

            $contract = Contract::create([
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'proposal_id' => $quotation->proposal_id,
                'title' => $overrides['title'] ?? ('Contract for '.$quotation->quotation_no),
                'description' => $overrides['description'] ?? null,
                'contract_type' => $overrides['contract_type'] ?? \App\Enums\ContractType::Consulting,
                'start_date' => $overrides['start_date'] ?? now()->toDateString(),
                'end_date' => $overrides['end_date'] ?? $quotation->valid_until,
                'contract_amount' => $overrides['contract_amount'] ?? $quotation->total_amount,
                'currency_id' => $overrides['currency_id'] ?? $quotation->currency_id,
                'payment_terms' => $overrides['payment_terms'] ?? $quotation->payment_terms,
                'status' => $overrides['status'] ?? ContractStatus::Draft,
                'auto_renew' => $overrides['auto_renew'] ?? false,
                'renewal_period' => $overrides['renewal_period'] ?? null,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            foreach ($quotation->items as $item) {
                ContractItem::create([
                    'contract_id' => $contract->id,
                    'service_id' => $item->service_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'total_amount' => $item->total,
                    'notes' => $item->notes,
                ]);
            }

            if ($quotation->status === QuotationStatus::Accepted) {
                $quotation->update(['status' => QuotationStatus::Converted]);
            }

            $this->auditService->logModelEvent($contract, AuditAction::Create);

            return $contract->fresh(['items', 'customer', 'quotation']);
        });
    }

    public function activate(Contract $contract, ?int $updatedBy = null): Contract
    {
        return DB::transaction(function () use ($contract, $updatedBy) {
            $contract = Contract::query()->lockForUpdate()->findOrFail($contract->id);

            if (! in_array($contract->status, [ContractStatus::Draft, ContractStatus::PendingSignature], true)) {
                throw new DomainException('Only draft or pending-signature contracts can be activated.');
            }

            $contract->update([
                'status' => ContractStatus::Active,
                'signed_date' => $contract->signed_date ?? now()->toDateString(),
                'updated_by' => $updatedBy ?? $contract->updated_by,
            ]);

            $this->auditService->logModelEvent($contract->fresh(), AuditAction::Update);

            return $contract->fresh(['items', 'customer']);
        });
    }
}
