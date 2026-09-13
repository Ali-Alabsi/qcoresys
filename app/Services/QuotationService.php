<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\QuotationStatus;
use App\Exceptions\DomainException;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function __construct(
        protected AuditService $auditService,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(array $header, array $items = [], ?int $createdBy = null): Quotation
    {
        return DB::transaction(function () use ($header, $items, $createdBy) {
            $quotation = Quotation::create([
                ...$header,
                'status' => $header['status'] ?? QuotationStatus::Draft,
                'version' => $header['version'] ?? 1,
                'exchange_rate' => $header['exchange_rate'] ?? 1,
                'subtotal' => 0,
                'discount_amount' => $header['discount_amount'] ?? 0,
                'tax_amount' => 0,
                'other_amount' => $header['other_amount'] ?? 0,
                'total_amount' => 0,
                'prepared_by' => $header['prepared_by'] ?? $createdBy,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            foreach ($items as $index => $item) {
                $computed = $this->computeItemTotals($item);

                QuotationItem::create([
                    ...$item,
                    ...$computed,
                    'quotation_id' => $quotation->id,
                    'sort_order' => $item['sort_order'] ?? ($index + 1),
                    'quantity' => $item['quantity'] ?? 1,
                ]);
            }

            $quotation = $this->recalculateTotals($quotation);

            $this->auditService->logModelEvent($quotation, AuditAction::Create);

            return $quotation->fresh('items');
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function update(Quotation $quotation, array $header, array $items, ?int $updatedBy = null): Quotation
    {
        return DB::transaction(function () use ($quotation, $header, $items, $updatedBy) {
            $quotation = Quotation::query()->lockForUpdate()->findOrFail($quotation->id);

            if (! in_array($quotation->status, [QuotationStatus::Draft, QuotationStatus::InReview], true)) {
                throw new DomainException('Only draft or in-review quotations can be fully updated.');
            }

            if ($items === []) {
                throw new DomainException('Quotation must have at least one line item.');
            }

            $quotation->update([
                ...$header,
                'updated_by' => $updatedBy,
            ]);

            $quotation->items()->delete();

            foreach ($items as $index => $item) {
                $computed = $this->computeItemTotals($item);

                QuotationItem::create([
                    ...$item,
                    ...$computed,
                    'quotation_id' => $quotation->id,
                    'sort_order' => $item['sort_order'] ?? ($index + 1),
                    'quantity' => $item['quantity'] ?? 1,
                ]);
            }

            $quotation = $this->recalculateTotals($quotation->fresh('items'));

            $this->auditService->logModelEvent($quotation, AuditAction::Update);

            return $quotation->fresh('items');
        });
    }

    public function recalculateTotals(Quotation $quotation): Quotation
    {
        $quotation->loadMissing('items');

        $subtotal = 0.0;
        $taxAmount = 0.0;

        foreach ($quotation->items as $item) {
            $computed = $this->computeItemTotals($item->getAttributes());
            $item->update($computed);

            $subtotal = Money::add($subtotal, $computed['subtotal']);
            $taxAmount = Money::add($taxAmount, $computed['tax_amount']);
        }

        $discountAmount = Money::round($quotation->discount_amount);
        $otherAmount = Money::round($quotation->other_amount);
        $totalAmount = Money::add($subtotal, -$discountAmount, $taxAmount, $otherAmount);

        $quotation->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        return $quotation->fresh('items');
    }

    public function approve(Quotation $quotation, int $approvedBy): Quotation
    {
        return DB::transaction(function () use ($quotation, $approvedBy) {
            $quotation = Quotation::query()->lockForUpdate()->findOrFail($quotation->id);
            $quotation->load('items');

            if ($quotation->items->isEmpty()) {
                throw new DomainException('Quotation must have at least one line item before approval.');
            }

            if (! in_array($quotation->status, [QuotationStatus::Draft, QuotationStatus::InReview], true)) {
                throw new DomainException('Only draft or in-review quotations can be approved.');
            }

            $this->recalculateTotals($quotation);

            $quotation->update([
                'status' => QuotationStatus::Approved,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'updated_by' => $approvedBy,
            ]);

            $this->auditService->logModelEvent($quotation->fresh(), AuditAction::Approve);

            return $quotation->fresh('items');
        });
    }

    public function reject(Quotation $quotation, int $rejectedBy, ?string $reason = null): Quotation
    {
        return DB::transaction(function () use ($quotation, $rejectedBy, $reason) {
            $quotation = Quotation::query()->lockForUpdate()->findOrFail($quotation->id);

            if (in_array($quotation->status, [
                QuotationStatus::Accepted,
                QuotationStatus::Converted,
                QuotationStatus::Cancelled,
            ], true)) {
                throw new DomainException("Quotation in status [{$quotation->status->value}] cannot be rejected.");
            }

            $quotation->update([
                'status' => QuotationStatus::Rejected,
                'rejected_by' => $rejectedBy,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'updated_by' => $rejectedBy,
            ]);

            $this->auditService->logModelEvent($quotation->fresh(), AuditAction::Reject);

            return $quotation->fresh('items');
        });
    }

    public function send(Quotation $quotation): Quotation
    {
        return DB::transaction(function () use ($quotation) {
            $quotation = Quotation::query()->lockForUpdate()->findOrFail($quotation->id);

            if ($quotation->status !== QuotationStatus::Approved) {
                throw new DomainException('Only approved quotations can be sent.');
            }

            if ($quotation->valid_until && $quotation->valid_until->isPast()) {
                throw new DomainException('Cannot send an expired quotation.');
            }

            $quotation->update([
                'status' => QuotationStatus::Sent,
                'sent_at' => now(),
            ]);

            $this->auditService->logModelEvent($quotation->fresh(), AuditAction::Send);

            return $quotation->fresh('items');
        });
    }

    public function accept(Quotation $quotation): Quotation
    {
        return DB::transaction(function () use ($quotation) {
            $quotation = Quotation::query()->lockForUpdate()->findOrFail($quotation->id);

            if ($quotation->status !== QuotationStatus::Sent) {
                throw new DomainException('Only sent quotations can be accepted.');
            }

            if ($quotation->valid_until && $quotation->valid_until->isPast()) {
                throw new DomainException('Cannot accept an expired quotation.');
            }

            $quotation->update([
                'status' => QuotationStatus::Accepted,
                'customer_response_at' => now(),
            ]);

            $this->auditService->logModelEvent($quotation->fresh(), AuditAction::Update);

            return $quotation->fresh('items');
        });
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{subtotal: float, discount_amount: float, tax_amount: float, total: float, estimated_cost: float, estimated_profit: float}
     */
    protected function computeItemTotals(array $item): array
    {
        $quantity = (float) ($item['quantity'] ?? 1);
        $unitPrice = (float) ($item['unit_price'] ?? 0);
        $gross = Money::multiply($quantity, $unitPrice);

        $discountAmount = isset($item['discount_amount'])
            ? Money::round($item['discount_amount'])
            : Money::percentage($gross, $item['discount_percentage'] ?? 0);

        $subtotal = Money::subtract($gross, $discountAmount);

        $taxAmount = isset($item['tax_amount'])
            ? Money::round($item['tax_amount'])
            : Money::percentage($subtotal, $item['tax_percentage'] ?? 0);

        $total = Money::add($subtotal, $taxAmount);

        $costPrice = (float) ($item['cost_price'] ?? 0);
        $estimatedCost = isset($item['estimated_cost'])
            ? Money::round($item['estimated_cost'])
            : Money::multiply($quantity, $costPrice);

        $estimatedProfit = Money::subtract($total, $estimatedCost);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'estimated_cost' => $estimatedCost,
            'estimated_profit' => $estimatedProfit,
        ];
    }
}
