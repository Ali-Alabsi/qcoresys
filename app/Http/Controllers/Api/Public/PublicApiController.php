<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreConsultationRequest;
use App\Http\Requests\Public\StoreContactRequest;
use App\Http\Requests\Public\StoreServiceRequestRequest;
use App\Http\Resources\Public\FaqResource;
use App\Http\Resources\Public\ServiceCardResource;
use App\Http\Resources\Public\ServiceCategoryResource;
use App\Http\Resources\Public\ServiceDetailResource;
use App\Http\Resources\Public\ServiceRequestOptionResource;
use App\Http\Resources\Public\TechnologyResource;
use App\Http\Resources\Public\TestimonialResource;
use App\Http\Responses\ApiResponse;
use App\Services\Public\CompanySettingsService;
use App\Services\Public\PublicCatalogService;
use App\Services\Public\PublicContentService;
use App\Services\Public\PublicLeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    public function __construct(
        protected CompanySettingsService $companySettings,
        protected PublicCatalogService $catalog,
        protected PublicContentService $content,
        protected PublicLeadService $leads,
    ) {}

    public function company(): JsonResponse
    {
        return ApiResponse::success(
            $this->companySettings->getPublicSettings(),
            __('Company information retrieved successfully.'),
        );
    }

    public function categories(): JsonResponse
    {
        return ApiResponse::success(
            ServiceCategoryResource::collection($this->catalog->categories()),
            __('Service categories retrieved successfully.'),
        );
    }

    public function technologies(): JsonResponse
    {
        return ApiResponse::success(
            TechnologyResource::collection($this->catalog->technologies()),
            __('Technologies retrieved successfully.'),
        );
    }

    public function services(Request $request): JsonResponse
    {
        $paginator = $this->catalog->listServices($request->all());

        return ApiResponse::paginated(
            $paginator,
            __('Services retrieved successfully.'),
            fn ($service) => (new ServiceCardResource($service))->resolve(),
        );
    }

    public function service(string $slug): JsonResponse
    {
        $service = $this->catalog->findBySlug($slug);

        return ApiResponse::success(
            new ServiceDetailResource($service),
            __('Service retrieved successfully.'),
        );
    }

    public function portfolio(Request $request): JsonResponse
    {
        abort(404);
    }

    public function portfolioShow(string $slug): JsonResponse
    {
        abort(404);
    }

    public function testimonials(): JsonResponse
    {
        return ApiResponse::success(
            TestimonialResource::collection($this->content->testimonials()),
            __('Testimonials retrieved successfully.'),
        );
    }

    public function faqs(): JsonResponse
    {
        return ApiResponse::success(
            FaqResource::collection($this->content->faqs()),
            __('FAQs retrieved successfully.'),
        );
    }

    public function requestOptions(): JsonResponse
    {
        $options = $this->content->requestOptions();

        return ApiResponse::success([
            'project_types' => ServiceRequestOptionResource::collection($options['project_types']),
            'budget_ranges' => ServiceRequestOptionResource::collection($options['budget_ranges']),
            'timelines' => ServiceRequestOptionResource::collection($options['timelines']),
            'contact_methods' => ServiceRequestOptionResource::collection($options['contact_methods']),
        ], __('Request options retrieved successfully.'));
    }

    public function storeServiceRequest(StoreServiceRequestRequest $request): JsonResponse
    {
        $lead = $this->leads->submitServiceRequest(
            $request->validated(),
            $request->file('attachments', []),
        );

        return ApiResponse::success([
            'request_number' => $lead->request_no,
            'status' => $lead->status->value,
        ], __('Your request has been submitted successfully.'), 201);
    }

    public function storeConsultation(StoreConsultationRequest $request): JsonResponse
    {
        $lead = $this->leads->submitConsultationRequest($request->validated());

        return ApiResponse::success([
            'request_number' => $lead->request_no,
            'status' => $lead->status->value,
        ], __('Your consultation request has been submitted successfully.'), 201);
    }

    public function storeContact(StoreContactRequest $request): JsonResponse
    {
        $lead = $this->leads->submitContactMessage($request->validated());

        return ApiResponse::success([
            'request_number' => $lead->request_no,
            'status' => $lead->status->value,
        ], __('Your message has been sent successfully.'), 201);
    }
}
