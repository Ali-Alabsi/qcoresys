<?php

namespace App\Services\Public;

use App\Enums\AuditAction;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\PricingType;
use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Attachment;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\CustomerRequest;
use App\Models\Service;
use App\Services\AuditService;
use App\Services\CustomerRequestService;
use App\Services\CustomerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicLeadService
{
    public function __construct(
        protected CustomerService $customerService,
        protected CustomerRequestService $customerRequestService,
        protected AuditService $auditService,
    ) {}

    public function submitServiceRequest(array $data, array $attachments = []): CustomerRequest
    {
        return DB::transaction(function () use ($data, $attachments) {
            [$customer, $contact] = $this->resolveCustomerAndContact($data);

            $service = null;
            if (! empty($data['service_id'])) {
                $service = Service::query()->public()->findOrFail($data['service_id']);
            } elseif (! empty($data['service_slug'])) {
                $service = Service::query()->public()->where('slug', $data['service_slug'])->firstOrFail();
            }

            $requestType = $this->mapRequestType($service, $data['request_type'] ?? null);
            $quotationRequired = $service
                && in_array($service->pricing_type, [
                    PricingType::Custom,
                    PricingType::QuoteRequired,
                    PricingType::ContactUs,
                ], true);

            $request = $this->customerRequestService->create([
                'customer_id' => $customer->id,
                'contact_id' => $contact->id,
                'service_id' => $service?->id,
                'request_type' => $requestType,
                'subject' => $data['subject'] ?? ($service?->name ? 'Service request: '.$service->name : 'Website service request'),
                'description' => $data['description'] ?? null,
                'requirements' => $data['requirements'] ?? null,
                'project_type' => $data['project_type'] ?? null,
                'expected_timeline' => $data['timeline'] ?? $data['expected_timeline'] ?? null,
                'preferred_contact_method' => $data['preferred_contact_method'] ?? null,
                'additional_notes' => $data['additional_notes'] ?? null,
                'estimated_budget' => $data['estimated_budget'] ?? null,
                'currency_id' => $service?->currency_id ?? $customer->default_currency_id,
                'priority' => RequestPriority::Medium,
                'source' => 'WEBSITE',
                'status' => RequestStatus::New,
                'consultation_required' => (bool) ($data['consultation_required'] ?? false),
                'quotation_required' => $quotationRequired || (bool) ($data['quotation_required'] ?? false),
            ]);

            $this->storeAttachments($request, $attachments);

            $this->auditService->log(
                AuditAction::Create,
                'PublicLead',
                'customer_requests',
                $request->id,
                null,
                [
                    'request_no' => $request->request_no,
                    'source' => 'WEBSITE',
                    'service_id' => $request->service_id,
                ],
            );

            return $request->fresh(['service', 'customer', 'contact']);
        });
    }

    public function submitConsultationRequest(array $data): CustomerRequest
    {
        $data['consultation_required'] = true;
        $data['subject'] = $data['subject'] ?? 'Consultation request';
        $data['request_type'] = $data['request_type'] ?? RequestType::TechnicalConsulting->value;

        return $this->submitServiceRequest($data);
    }

    public function submitContactMessage(array $data): CustomerRequest
    {
        $data['subject'] = $data['subject'] ?? 'Website contact message';
        $data['description'] = $data['message'] ?? $data['description'] ?? null;
        $data['request_type'] = RequestType::Other->value;

        return $this->submitServiceRequest($data);
    }

    /**
     * @return array{0: Customer, 1: CustomerContact}
     */
    protected function resolveCustomerAndContact(array $data): array
    {
        $email = strtolower(trim($data['email']));
        $phone = trim($data['phone'] ?? $data['mobile'] ?? '');

        $customer = Customer::query()
            ->where(function ($q) use ($email, $phone) {
                $q->where('email', $email);
                if ($phone !== '') {
                    $q->orWhere('phone', $phone)->orWhere('mobile', $phone);
                }
            })
            ->first();

        if (! $customer) {
            $hasCompany = filled($data['company_name'] ?? null);
            $customer = $this->customerService->create([
                'customer_type' => $hasCompany ? CustomerType::Company : CustomerType::Individual,
                'name' => $data['full_name'],
                'company_name' => $data['company_name'] ?? null,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'mobile' => $phone !== '' ? $phone : null,
                'status' => CustomerStatus::Lead,
                'customer_source' => 'WEBSITE',
            ]);
        }

        $contact = $customer->contacts()
            ->where(function ($q) use ($email, $phone) {
                $q->where('email', $email);
                if ($phone !== '') {
                    $q->orWhere('phone', $phone)->orWhere('mobile', $phone);
                }
            })
            ->first();

        if (! $contact) {
            $parts = preg_split('/\s+/', trim($data['full_name']), 2);
            $contact = $this->customerService->createContact($customer, [
                'first_name' => $parts[0] ?? $data['full_name'],
                'last_name' => $parts[1] ?? null,
                'full_name' => $data['full_name'],
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'mobile' => $phone !== '' ? $phone : null,
                'is_primary' => $customer->contacts()->count() === 0,
                'is_active' => true,
            ]);
        }

        return [$customer, $contact];
    }

    protected function mapRequestType(?Service $service, ?string $fallback): RequestType
    {
        if ($fallback) {
            return RequestType::tryFrom($fallback) ?? RequestType::Other;
        }

        if (! $service) {
            return RequestType::Other;
        }

        return match ($service->service_type?->value) {
            'CONSULTING' => RequestType::TechnicalConsulting,
            'DEVELOPMENT' => RequestType::SoftwareDevelopment,
            'SECURITY' => RequestType::CyberSecurity,
            'CLOUD' => RequestType::Cloud,
            'INFRASTRUCTURE' => RequestType::Infrastructure,
            'SUPPORT', 'MAINTENANCE' => RequestType::ItSupport,
            'TRAINING' => RequestType::Training,
            default => RequestType::Other,
        };
    }

    /**
     * @param  array<int, UploadedFile>  $attachments
     */
    protected function storeAttachments(CustomerRequest $request, array $attachments): void
    {
        foreach ($attachments as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $disk = 'local';
            $directory = 'public-requests/'.$request->id;
            $storedName = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs($directory, $storedName, $disk);

            Attachment::create([
                'attachable_type' => 'customer_request',
                'attachable_id' => $request->id,
                'file_name' => $storedName,
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'disk' => $disk,
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'description' => 'Public service request attachment',
            ]);
        }
    }
}
