<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\ExpenseStatus;
use App\Enums\JournalStatus;
use App\Models\JournalEntryLine;
use App\Models\ProjectCost;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class ProfitabilityService
{
    public function customerProfitability(int $customerId, ?string $from = null, ?string $to = null): array
    {
        $journal = $this->journalTotalsByAccountType(
            customerId: $customerId,
            from: $from,
            to: $to,
        );

        return $this->buildProfitabilityResult(
            entityType: 'customer',
            entityId: $customerId,
            revenue: $journal['revenue'],
            expenses: $journal['expenses'],
            projectCosts: 0.0,
            from: $from,
            to: $to,
        );
    }

    public function projectProfitability(int $projectId, ?string $from = null, ?string $to = null): array
    {
        $journal = $this->journalTotalsByAccountType(
            projectId: $projectId,
            from: $from,
            to: $to,
        );

        $projectCosts = $this->sumProjectCosts(projectId: $projectId, from: $from, to: $to);

        return $this->buildProfitabilityResult(
            entityType: 'project',
            entityId: $projectId,
            revenue: $journal['revenue'],
            expenses: Money::add($journal['expenses'], $projectCosts),
            projectCosts: $projectCosts,
            from: $from,
            to: $to,
        );
    }

    public function serviceProfitability(int $serviceId, ?string $from = null, ?string $to = null): array
    {
        $journal = $this->journalTotalsByAccountType(
            serviceId: $serviceId,
            from: $from,
            to: $to,
        );

        return $this->buildProfitabilityResult(
            entityType: 'service',
            entityId: $serviceId,
            revenue: $journal['revenue'],
            expenses: $journal['expenses'],
            projectCosts: 0.0,
            from: $from,
            to: $to,
        );
    }

    public function companyProfitAndLoss(?string $from = null, ?string $to = null): array
    {
        $journal = $this->journalTotalsByAccountType(from: $from, to: $to);
        $projectCosts = $this->sumProjectCosts(from: $from, to: $to);

        $expenses = Money::add($journal['expenses'], $projectCosts);
        $profit = Money::subtract($journal['revenue'], $expenses);

        return [
            'entity_type' => 'company',
            'from' => $from,
            'to' => $to,
            'revenue' => $journal['revenue'],
            'journal_expenses' => $journal['expenses'],
            'project_costs' => $projectCosts,
            'expenses' => $expenses,
            'profit' => $profit,
            'margin_percentage' => $this->marginPercentage($journal['revenue'], $profit),
            'revenue_breakdown' => $journal['revenue_breakdown'],
            'expense_breakdown' => $journal['expense_breakdown'],
        ];
    }

    /**
     * @return array{revenue: float, expenses: float, revenue_breakdown: array<int, array<string, mixed>>, expense_breakdown: array<int, array<string, mixed>>}
     */
    protected function journalTotalsByAccountType(
        ?int $customerId = null,
        ?int $projectId = null,
        ?int $serviceId = null,
        ?string $from = null,
        ?string $to = null,
    ): array {
        $query = JournalEntryLine::query()
            ->select([
                'accounts.id as account_id',
                'accounts.account_code',
                'accounts.account_name',
                'accounts.account_type',
                DB::raw('SUM(journal_entry_lines.debit_base) as total_debit'),
                DB::raw('SUM(journal_entry_lines.credit_base) as total_credit'),
            ])
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entries.status', JournalStatus::Posted->value)
            ->groupBy('accounts.id', 'accounts.account_code', 'accounts.account_name', 'accounts.account_type');

        if ($customerId !== null) {
            $query->where('journal_entry_lines.customer_id', $customerId);
        }

        if ($projectId !== null) {
            $query->where('journal_entry_lines.project_id', $projectId);
        }

        if ($serviceId !== null) {
            $query->where('journal_entry_lines.service_id', $serviceId);
        }

        if ($from !== null) {
            $query->whereDate('journal_entries.entry_date', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('journal_entries.entry_date', '<=', $to);
        }

        $rows = $query->get();

        $revenue = 0.0;
        $expenses = 0.0;
        $revenueBreakdown = [];
        $expenseBreakdown = [];

        foreach ($rows as $row) {
            $debit = (float) $row->total_debit;
            $credit = (float) $row->total_credit;

            if ($row->account_type === AccountType::Revenue->value) {
                $net = Money::subtract($credit, $debit);
                $revenue = Money::add($revenue, $net);
                $revenueBreakdown[] = [
                    'account_id' => (int) $row->account_id,
                    'account_code' => $row->account_code,
                    'account_name' => $row->account_name,
                    'amount' => $net,
                ];
            }

            if ($row->account_type === AccountType::Expense->value) {
                $net = Money::subtract($debit, $credit);
                $expenses = Money::add($expenses, $net);
                $expenseBreakdown[] = [
                    'account_id' => (int) $row->account_id,
                    'account_code' => $row->account_code,
                    'account_name' => $row->account_name,
                    'amount' => $net,
                ];
            }
        }

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'revenue_breakdown' => $revenueBreakdown,
            'expense_breakdown' => $expenseBreakdown,
        ];
    }

    protected function sumProjectCosts(
        ?int $projectId = null,
        ?string $from = null,
        ?string $to = null,
    ): float {
        $query = ProjectCost::query()
            ->where(function ($builder) {
                $builder->whereNull('expense_id')
                    ->orWhereHas('expense', fn ($expenseQuery) => $expenseQuery->where('status', '!=', ExpenseStatus::Posted->value));
            });

        if ($projectId !== null) {
            $query->where('project_id', $projectId);
        }

        if ($from !== null) {
            $query->whereDate('cost_date', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('cost_date', '<=', $to);
        }

        return Money::round($query->sum('amount'));
    }

    protected function buildProfitabilityResult(
        string $entityType,
        int $entityId,
        float $revenue,
        float $expenses,
        float $projectCosts,
        ?string $from,
        ?string $to,
    ): array {
        $profit = Money::subtract($revenue, $expenses);

        return [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'from' => $from,
            'to' => $to,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'project_costs' => $projectCosts,
            'profit' => $profit,
            'margin_percentage' => $this->marginPercentage($revenue, $profit),
        ];
    }

    protected function marginPercentage(float $revenue, float $profit): ?float
    {
        if (Money::isZero($revenue)) {
            return null;
        }

        return Money::round(($profit / $revenue) * 100, 2);
    }
}
