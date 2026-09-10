<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\CostType;
use App\Enums\ExpenseStatus;
use App\Exceptions\DomainException;
use App\Models\Expense;
use App\Models\ProjectCost;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function __construct(
        protected AuditService $auditService,
        protected AccountingService $accountingService,
        protected ProjectService $projectService,
    ) {}

    public function create(array $data, ?int $createdBy = null): Expense
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $amount = Money::round($data['amount'] ?? 0);
            $taxAmount = Money::round($data['tax_amount'] ?? 0);
            $totalAmount = isset($data['total_amount'])
                ? Money::round($data['total_amount'])
                : Money::add($amount, $taxAmount);

            if (! Money::isPositive($totalAmount)) {
                throw new DomainException('Expense total amount must be greater than zero.');
            }

            $expense = Expense::create([
                ...$data,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => $data['status'] ?? ExpenseStatus::Draft,
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'is_billable' => $data['is_billable'] ?? false,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            $this->auditService->logModelEvent($expense, AuditAction::Create);

            return $expense->fresh();
        });
    }

    public function approve(Expense $expense, int $approvedBy): Expense
    {
        return DB::transaction(function () use ($expense, $approvedBy) {
            $expense = Expense::query()->lockForUpdate()->findOrFail($expense->id);

            if ($expense->status !== ExpenseStatus::Draft) {
                throw new DomainException('Only draft expenses can be approved.');
            }

            $expense->update([
                'status' => ExpenseStatus::Approved,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'updated_by' => $approvedBy,
            ]);

            $this->auditService->logModelEvent($expense->fresh(), AuditAction::Approve);

            return $expense->fresh();
        });
    }

    public function post(Expense $expense, ?int $postedBy = null): Expense
    {
        return DB::transaction(function () use ($expense, $postedBy) {
            $expense = Expense::query()->lockForUpdate()->findOrFail($expense->id);

            if ($expense->status !== ExpenseStatus::Approved) {
                throw new DomainException('Only approved expenses can be posted.');
            }

            if ($expense->journal_entry_id !== null) {
                throw new DomainException('Expense has already been posted.');
            }

            $entry = $this->accountingService->postExpense($expense, $postedBy);

            $expense->update([
                'journal_entry_id' => $entry->id,
                'status' => ExpenseStatus::Posted,
            ]);

            if ($expense->project_id) {
                ProjectCost::firstOrCreate(
                    ['expense_id' => $expense->id],
                    [
                        'project_id' => $expense->project_id,
                        'vendor_id' => $expense->vendor_id,
                        'employee_id' => $expense->employee_id,
                        'cost_type' => CostType::Other,
                        'description' => $expense->description,
                        'amount' => $expense->total_amount,
                        'currency_id' => $expense->currency_id,
                        'exchange_rate' => $expense->exchange_rate ?? 1,
                        'cost_date' => $expense->expense_date,
                        'billable' => $expense->is_billable,
                        'created_by' => $postedBy ?? $expense->created_by,
                    ],
                );

                $this->projectService->recalculateSnapshots($expense->project);
            }

            $this->auditService->logModelEvent($expense->fresh(), AuditAction::Post);

            return $expense->fresh(['projectCost', 'journalEntry']);
        });
    }
}
