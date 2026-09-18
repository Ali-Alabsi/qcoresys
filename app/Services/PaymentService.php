<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\DomainException;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected AuditService $auditService,
        protected AccountingService $accountingService,
    ) {}

    public function create(array $data, ?int $createdBy = null): Payment
    {
        return DB::transaction(function () use ($data, $createdBy) {
            if (empty($data['invoice_id'])) {
                throw new DomainException(__('Payment must be linked to an invoice.'));
            }

            $invoice = Invoice::query()->lockForUpdate()->findOrFail($data['invoice_id']);

            $this->assertInvoiceReceivable($invoice);

            $amount = Money::round($data['amount'] ?? 0);

            if (! Money::isPositive($amount)) {
                throw new DomainException(__('Payment amount must be greater than zero.'));
            }

            if ($amount > (float) $invoice->remaining_amount) {
                throw new DomainException(__('Payment amount cannot exceed the invoice remaining balance.'));
            }

            if (isset($data['customer_id']) && (int) $data['customer_id'] !== $invoice->customer_id) {
                throw new DomainException(__('Payment customer does not match the invoice customer.'));
            }

            $payment = Payment::create([
                ...$data,
                'customer_id' => $invoice->customer_id,
                'amount' => $amount,
                'status' => $data['status'] ?? PaymentStatus::Draft,
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'received_by' => $data['received_by'] ?? $createdBy,
                'created_by' => $createdBy,
            ]);

            $this->auditService->logModelEvent($payment, AuditAction::Create);

            return $payment->fresh(['invoice', 'customer']);
        });
    }

    public function post(Payment $payment, ?int $postedBy = null): Payment
    {
        return DB::transaction(function () use ($payment, $postedBy) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === PaymentStatus::Posted) {
                throw new DomainException(__('Payment is already posted.'));
            }

            if ($payment->journal_entry_id !== null) {
                throw new DomainException(__('Payment already has an associated journal entry.'));
            }

            $invoice = Invoice::query()->lockForUpdate()->findOrFail($payment->invoice_id);
            $this->assertInvoiceReceivable($invoice);

            $amount = (float) $payment->amount;

            if ($amount > (float) $invoice->remaining_amount) {
                throw new DomainException(__('Payment amount cannot exceed the invoice remaining balance.'));
            }

            $entry = $this->accountingService->postPayment($payment, $postedBy);

            $paidAmount = Money::add($invoice->paid_amount, $amount);
            $remainingAmount = Money::subtract($invoice->total_amount, $paidAmount);

            $invoiceStatus = Money::isZero($remainingAmount)
                ? InvoiceStatus::Paid
                : InvoiceStatus::PartiallyPaid;

            $invoice->update([
                'paid_amount' => $paidAmount,
                'remaining_amount' => max($remainingAmount, 0),
                'status' => $invoiceStatus,
            ]);

            $payment->update([
                'journal_entry_id' => $entry->id,
                'status' => PaymentStatus::Posted,
                'approved_by' => $postedBy,
            ]);

            $this->auditService->logModelEvent($payment->fresh(), AuditAction::Post);

            return $payment->fresh(['invoice', 'journalEntry']);
        });
    }

    protected function assertInvoiceReceivable(Invoice $invoice): void
    {
        if (! in_array($invoice->status, [
            InvoiceStatus::Posted,
            InvoiceStatus::PartiallyPaid,
            InvoiceStatus::Overdue,
        ], true)) {
            throw new DomainException(__('Payments can only be applied to posted or partially paid invoices.'));
        }

        if ((float) $invoice->remaining_amount <= 0) {
            throw new DomainException(__('Invoice has no remaining balance.'));
        }
    }
}
