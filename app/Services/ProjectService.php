<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\ContractStatus;
use App\Enums\CostType;
use App\Enums\InvoiceStatus;
use App\Enums\ProjectServiceStatus;
use App\Enums\ProjectStatus;
use App\Enums\QuotationStatus;
use App\Exceptions\DomainException;
use App\Models\Contract;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectService as ProjectServiceModel;
use App\Models\Quotation;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(
        protected AuditService $auditService,
    ) {}

    public function createFromQuotation(Quotation $quotation, array $overrides = [], ?int $createdBy = null): Project
    {
        return DB::transaction(function () use ($quotation, $overrides, $createdBy) {
            $quotation = Quotation::query()->lockForUpdate()->with('items')->findOrFail($quotation->id);

            if (! in_array($quotation->status, [QuotationStatus::Accepted, QuotationStatus::Approved, QuotationStatus::Sent], true)) {
                throw new DomainException('Projects can only be created from approved, sent, or accepted quotations.');
            }

            if ($quotation->items->isEmpty()) {
                throw new DomainException('Quotation has no items to copy into project services.');
            }

            $estimatedCost = Money::round($quotation->items->sum(fn ($item) => (float) $item->estimated_cost));
            $estimatedRevenue = Money::round($quotation->total_amount);
            $estimatedProfit = Money::subtract($estimatedRevenue, $estimatedCost);

            $project = Project::create([
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'request_id' => $quotation->request_id,
                'name' => $overrides['name'] ?? ('Project for '.$quotation->quotation_no),
                'description' => $overrides['description'] ?? null,
                'project_type' => $overrides['project_type'] ?? \App\Enums\ProjectType::Consulting,
                'status' => $overrides['status'] ?? ProjectStatus::Planning,
                'priority' => $overrides['priority'] ?? \App\Enums\ProjectPriority::Medium,
                'project_manager_id' => $overrides['project_manager_id'] ?? null,
                'start_date' => $overrides['start_date'] ?? now()->toDateString(),
                'expected_end_date' => $overrides['expected_end_date'] ?? $quotation->valid_until,
                'budget_amount' => $overrides['budget_amount'] ?? $quotation->total_amount,
                'contract_amount' => $overrides['contract_amount'] ?? $quotation->total_amount,
                'currency_id' => $overrides['currency_id'] ?? $quotation->currency_id,
                'estimated_cost' => $estimatedCost,
                'actual_cost' => 0,
                'estimated_revenue' => $estimatedRevenue,
                'actual_revenue' => 0,
                'estimated_profit' => $estimatedProfit,
                'actual_profit' => 0,
                'completion_percentage' => 0,
                'billing_type' => $overrides['billing_type'] ?? null,
                'notes' => $overrides['notes'] ?? null,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            $this->copyQuotationItemsToProjectServices($quotation, $project);

            if ($quotation->status === QuotationStatus::Accepted) {
                $quotation->update(['status' => QuotationStatus::Converted]);
            }

            $this->auditService->logModelEvent($project, AuditAction::Create);

            return $project->fresh(['projectServices', 'customer', 'quotation']);
        });
    }

    public function createFromContract(Contract $contract, array $overrides = [], ?int $createdBy = null): Project
    {
        return DB::transaction(function () use ($contract, $overrides, $createdBy) {
            $contract = Contract::query()->lockForUpdate()->with(['items', 'quotation.items'])->findOrFail($contract->id);

            if ($contract->status !== ContractStatus::Active) {
                throw new DomainException('Projects can only be created from active contracts.');
            }

            $quotation = $contract->quotation;
            $estimatedCost = $quotation
                ? Money::round($quotation->items->sum(fn ($item) => (float) $item->estimated_cost))
                : 0.0;

            $estimatedRevenue = Money::round($contract->contract_amount);
            $estimatedProfit = Money::subtract($estimatedRevenue, $estimatedCost);

            $project = Project::create([
                'customer_id' => $contract->customer_id,
                'quotation_id' => $contract->quotation_id,
                'contract_id' => $contract->id,
                'name' => $overrides['name'] ?? ('Project for '.$contract->contract_no),
                'description' => $overrides['description'] ?? $contract->description,
                'project_type' => $overrides['project_type'] ?? \App\Enums\ProjectType::Consulting,
                'status' => $overrides['status'] ?? ProjectStatus::Planning,
                'priority' => $overrides['priority'] ?? \App\Enums\ProjectPriority::Medium,
                'project_manager_id' => $overrides['project_manager_id'] ?? null,
                'start_date' => $overrides['start_date'] ?? $contract->start_date,
                'expected_end_date' => $overrides['expected_end_date'] ?? $contract->end_date,
                'budget_amount' => $overrides['budget_amount'] ?? $contract->contract_amount,
                'contract_amount' => $overrides['contract_amount'] ?? $contract->contract_amount,
                'currency_id' => $overrides['currency_id'] ?? $contract->currency_id,
                'estimated_cost' => $estimatedCost,
                'actual_cost' => 0,
                'estimated_revenue' => $estimatedRevenue,
                'actual_revenue' => 0,
                'estimated_profit' => $estimatedProfit,
                'actual_profit' => 0,
                'completion_percentage' => 0,
                'billing_type' => $overrides['billing_type'] ?? null,
                'notes' => $overrides['notes'] ?? null,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            if ($quotation && $quotation->items->isNotEmpty()) {
                $this->copyQuotationItemsToProjectServices($quotation, $project);
            } else {
                foreach ($contract->items as $item) {
                    ProjectServiceModel::create([
                        'project_id' => $project->id,
                        'service_id' => $item->service_id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'unit_price' => $item->unit_price,
                        'total_amount' => $item->total_amount,
                        'estimated_cost' => 0,
                        'actual_cost' => 0,
                        'estimated_profit' => $item->total_amount,
                        'actual_profit' => 0,
                        'status' => ProjectServiceStatus::Pending,
                    ]);
                }
            }

            $this->auditService->logModelEvent($project, AuditAction::Create);

            return $project->fresh(['projectServices', 'customer', 'contract']);
        });
    }

    public function addCost(Project $project, array $data, ?int $createdBy = null): ProjectCost
    {
        return DB::transaction(function () use ($project, $data, $createdBy) {
            $cost = ProjectCost::create([
                ...$data,
                'project_id' => $project->id,
                'cost_type' => $data['cost_type'] ?? CostType::Other,
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'cost_date' => $data['cost_date'] ?? now()->toDateString(),
                'billable' => $data['billable'] ?? false,
                'created_by' => $createdBy,
            ]);

            $this->recalculateSnapshots($project);

            $this->auditService->logModelEvent($cost, AuditAction::Create);

            return $cost->fresh();
        });
    }

    public function recalculateSnapshots(Project $project): Project
    {
        return DB::transaction(function () use ($project) {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $project->load(['costs', 'projectServices', 'invoices']);

            $projectCostTotal = Money::round($project->costs->sum(fn ($cost) => (float) $cost->amount));
            $serviceActualCost = Money::round($project->projectServices->sum(fn ($service) => (float) $service->actual_cost));
            $actualCost = Money::add($projectCostTotal, $serviceActualCost);

            $postedRevenue = Money::round(
                $project->invoices()
                    ->whereIn('status', [
                        InvoiceStatus::Posted,
                        InvoiceStatus::PartiallyPaid,
                        InvoiceStatus::Paid,
                        InvoiceStatus::Overdue,
                    ])
                    ->sum('total_amount')
            );

            $serviceActualRevenue = Money::round(
                $project->projectServices->sum(fn ($service) => (float) $service->total_amount)
            );

            $actualRevenue = Money::isZero($postedRevenue) ? $serviceActualRevenue : $postedRevenue;
            $actualProfit = Money::subtract($actualRevenue, $actualCost);

            foreach ($project->projectServices as $service) {
                $serviceActualProfit = Money::subtract($service->total_amount, $service->actual_cost);
                if (! Money::equals($service->actual_profit, $serviceActualProfit)) {
                    $service->update(['actual_profit' => $serviceActualProfit]);
                }
            }

            $project->update([
                'actual_cost' => $actualCost,
                'actual_revenue' => $actualRevenue,
                'actual_profit' => $actualProfit,
            ]);

            return $project->fresh(['costs', 'projectServices', 'invoices']);
        });
    }

    protected function copyQuotationItemsToProjectServices(Quotation $quotation, Project $project): void
    {
        foreach ($quotation->items as $item) {
            ProjectServiceModel::create([
                'project_id' => $project->id,
                'service_id' => $item->service_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'total_amount' => $item->total,
                'estimated_hours' => 0,
                'actual_hours' => 0,
                'estimated_cost' => $item->estimated_cost,
                'actual_cost' => 0,
                'estimated_profit' => $item->estimated_profit,
                'actual_profit' => 0,
                'status' => ProjectServiceStatus::Pending,
                'notes' => $item->notes,
            ]);
        }
    }
}
