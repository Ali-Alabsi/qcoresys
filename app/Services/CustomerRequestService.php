<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\RequestStatus;
use App\Exceptions\DomainException;
use App\Models\CustomerRequest;
use Illuminate\Support\Facades\DB;

class CustomerRequestService
{
    public function __construct(
        protected AuditService $auditService,
    ) {}

    public function create(array $data, ?int $createdBy = null): CustomerRequest
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $request = CustomerRequest::create([
                ...$data,
                'status' => $data['status'] ?? RequestStatus::New,
                'received_at' => $data['received_at'] ?? now(),
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
            ]);

            $this->auditService->logModelEvent($request, AuditAction::Create);

            return $request->fresh();
        });
    }

    public function updateStatus(
        CustomerRequest $request,
        RequestStatus $status,
        ?string $closedReason = null,
        ?int $updatedBy = null,
    ): CustomerRequest {
        return DB::transaction(function () use ($request, $status, $closedReason, $updatedBy) {
            $request = CustomerRequest::query()->lockForUpdate()->findOrFail($request->id);
            $oldStatus = $request->status;

            if ($oldStatus === $status) {
                return $request;
            }

            $this->assertStatusTransition($oldStatus, $status);

            $payload = [
                'status' => $status,
                'updated_by' => $updatedBy ?? $request->updated_by,
            ];

            if (in_array($status, [RequestStatus::Closed, RequestStatus::Cancelled, RequestStatus::Rejected], true)) {
                $payload['closed_reason'] = $closedReason;
                $payload['closed_at'] = now();
            }

            if ($status === RequestStatus::ConvertedToProject && ! $request->project_id) {
                throw new DomainException('A request cannot be marked converted without an associated project.');
            }

            $request->update($payload);

            $this->auditService->logModelEvent(
                $request->fresh(),
                AuditAction::Update,
                ['status' => $oldStatus->value],
                ['status' => $status->value],
            );

            return $request->fresh();
        });
    }

    protected function assertStatusTransition(RequestStatus $from, RequestStatus $to): void
    {
        if (in_array($from, [
            RequestStatus::Closed,
            RequestStatus::Cancelled,
            RequestStatus::ConvertedToProject,
        ], true)) {
            throw new DomainException("Request in status [{$from->value}] cannot be changed.");
        }

        if ($to === RequestStatus::ConvertedToProject && ! in_array($from, [
            RequestStatus::Approved,
            RequestStatus::QuotationSent,
        ], true)) {
            throw new DomainException('Only approved or quotation-sent requests can be converted to a project.');
        }
    }
}
