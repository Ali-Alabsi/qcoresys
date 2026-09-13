<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\InvoiceStatus;
use App\Exceptions\DomainException;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        protected AuditService $auditService,
        protected AccountingService $accountingService,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(array $header, array $items = [], ?int $createdBy = null): Invoice
    {
        return DB::transaction(function () use ($header, $items, $createdBy) {
            if (empty($items)) {
                throw new DomainException('Invoice must have at least one line item.');
            }

            $invoice = Invoice::create([
                ...$header,
                'status' => $header['status'] ?? InvoiceStatus::Draft,
                'exchange_rate' => $header['exchange_rate'] ?? 1,
                'subtotal' => 0,
                'discount_amount' => $header['discount_amount'] ?? 0,
                'tax_amount' => 0,
                'other_amount' => $header['other_amount'] ?? 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'issued_by' => $header['issued_by'] ?? $createdBy,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            $subtotal = 0.0;
            $taxAmount = 0.0;

            foreach ($items as $index => $item) {
                $computed = $this->computeItemTotals($item);

                InvoiceItem::create([
                    ...$item,
                    ...$computed,
                    'invoice_id' => $invoice->id,
                    'project_id' => $item['project_id'] ?? $invoice->project_id,
                    'sort_order' => $item['sort_order'] ?? ($index + 1),
                    'quantity' => $item['quantity'] ?? 1,
                ]);

                $subtotal = Money::add($subtotal, $computed['subtotal']);
                $taxAmount = Money::add($taxAmount, $computed['tax_amount']);
            }

            $discountAmount = Money::round($invoice->discount_amount);
            $otherAmount = Money::round($invoice->other_amount);
            $totalAmount = Money::add($subtotal, -$discountAmount, $taxAmount, $otherAmount);

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'remaining_amount' => $totalAmount,
            ]);

            $this->auditService->logModelEvent($invoice->fresh(), AuditAction::Create);

            return $invoice->fresh('items');
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function update(Invoice $invoice, array $header, array $items, ?int $updatedBy = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $header, $items, $updatedBy) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);

            if ($invoice->status !== InvoiceStatus::Draft) {
                throw new DomainException('Only draft invoices can be fully updated.');
            }

            if ($items === []) {
                throw new DomainException('Invoice must have at least one line item.');
            }

            $invoice->update([
                ...$header,
                'updated_by' => $updatedBy,
            ]);

            $invoice->items()->delete();

            $subtotal = 0.0;
            $taxAmount = 0.0;

            foreach ($items as $index => $item) {
                $computed = $this->computeItemTotals($item);

                InvoiceItem::create([
                    ...$item,
                    ...$computed,
                    'invoice_id' => $invoice->id,
                    'project_id' => $item['project_id'] ?? $invoice->project_id,
                    'sort_order' => $item['sort_order'] ?? ($index + 1),
                    'quantity' => $item['quantity'] ?? 1,
                ]);

                $subtotal = Money::add($subtotal, $computed['subtotal']);
                $taxAmount = Money::add($taxAmount, $computed['tax_amount']);
            }

            $discountAmount = Money::round($invoice->fresh()->discount_amount);
            $otherAmount = Money::round($invoice->fresh()->other_amount);
            $totalAmount = Money::add($subtotal, -$discountAmount, $taxAmount, $otherAmount);

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'remaining_amount' => Money::subtract($totalAmount, $invoice->paid_amount),
            ]);

            $this->auditService->logModelEvent($invoice->fresh(), AuditAction::Update);

            return $invoice->fresh('items');
        });
    }

    public function approve(Invoice $invoice, int $approvedBy): Invoice
    {
        return DB::transaction(function () use ($invoice, $approvedBy) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);

            if ($invoice->status !== InvoiceStatus::Draft) {
                throw new DomainException('Only draft invoices can be approved.');
            }

            if ($invoice->journal_entry_id !== null) {
                throw new DomainException('Invoice already has an associated journal entry.');
            }

            $invoice->update([
                'status' => InvoiceStatus::Approved,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'updated_by' => $approvedBy,
            ]);

            $this->auditService->logModelEvent($invoice->fresh(), AuditAction::Approve);

            return $invoice->fresh('items');
        });
    }

    public function post(Invoice $invoice, ?int $postedBy = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $postedBy) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);

            if ($invoice->status !== InvoiceStatus::Approved) {
                throw new DomainException('Only approved invoices can be posted.');
            }

            if ($invoice->journal_entry_id !== null) {
                throw new DomainException('Invoice has already been posted.');
            }

            $entry = $this->accountingService->postInvoice($invoice, $postedBy);

            $invoice->update([
                'journal_entry_id' => $entry->id,
                'status' => InvoiceStatus::Posted,
                'paid_amount' => 0,
                'remaining_amount' => $invoice->total_amount,
            ]);

            $this->auditService->logModelEvent($invoice->fresh(), AuditAction::Post);

            return $invoice->fresh(['items', 'journalEntry']);
        });
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{subtotal: float, discount_amount: float, tax_amount: float, total_amount: float}
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

        $totalAmount = Money::add($subtotal, $taxAmount);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ];
    }
}
