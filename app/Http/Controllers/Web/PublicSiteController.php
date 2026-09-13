<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreConsultationRequest;
use App\Http\Requests\Public\StoreContactRequest;
use App\Http\Requests\Public\StoreServiceRequestRequest;
use App\Services\Public\CompanySettingsService;
use App\Services\Public\PublicCatalogService;
use App\Services\Public\PublicContentService;
use App\Services\Public\PublicLeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function __construct(
        protected CompanySettingsService $companySettings,
        protected PublicCatalogService $catalog,
        protected PublicContentService $content,
        protected PublicLeadService $leads,
    ) {}

    public function home(): View
    {
        $settings = $this->companySettings->getPublicSettings();
        $featured = $this->catalog->spotlightServices(6);
        $testimonials = $this->content->testimonials(true);

        return view('public.home', compact('settings', 'featured', 'testimonials'));
    }

    public function services(Request $request): View
    {
        $settings = $this->companySettings->getPublicSettings();
        $services = $this->catalog->listServices($request->all());
        $categories = $this->catalog->categories();
        $technologies = $this->catalog->technologies();
        $featured = $this->catalog->featuredServices(5);

        return view('public.services.index', compact(
            'settings',
            'services',
            'categories',
            'technologies',
            'featured',
        ));
    }

    public function serviceShow(string $slug): View
    {
        $service = $this->catalog->findBySlug($slug);
        $settings = $this->companySettings->getPublicSettings();

        return view('public.services.show', compact('service', 'settings'));
    }

    public function serviceRequestForm(string $slug): View
    {
        $service = $this->catalog->findBySlug($slug);
        $options = $this->content->requestOptions();
        $settings = $this->companySettings->getPublicSettings();

        return view('public.services.request', compact('service', 'options', 'settings'));
    }

    public function storeServiceRequest(StoreServiceRequestRequest $request, ?string $slug = null): RedirectResponse
    {
        $data = $request->validated();

        if ($slug) {
            $data['service_slug'] = $slug;
        }

        $lead = $this->leads->submitServiceRequest(
            $data,
            $request->file('attachments', []) ?? [],
        );

        return redirect()
            ->route('request.confirmation')
            ->with('request_number', $lead->request_no)
            ->with('success_message', __('Your request has been submitted successfully.'));
    }

    public function consultation(): View
    {
        $settings = $this->companySettings->getPublicSettings();
        $options = $this->content->requestOptions();
        $services = $this->catalog->listServices(['per_page' => 100]);

        return view('public.consultation', compact('settings', 'options', 'services'));
    }

    public function storeConsultation(StoreConsultationRequest $request): RedirectResponse
    {
        $lead = $this->leads->submitConsultationRequest($request->validated());

        return redirect()
            ->route('request.confirmation')
            ->with('request_number', $lead->request_no)
            ->with('success_message', __('Your consultation request has been submitted successfully.'));
    }

    public function confirmation(): View|RedirectResponse
    {
        if (! session('request_number')) {
            return redirect()->route('services.index');
        }

        return view('public.request-confirmation', [
            'requestNumber' => session('request_number'),
            'successMessage' => session('success_message'),
            'settings' => $this->companySettings->getPublicSettings(),
        ]);
    }

    public function solutions(): View
    {
        $categories = $this->catalog->categories();
        $settings = $this->companySettings->getPublicSettings();

        $grouped = [];
        foreach ($categories as $category) {
            $grouped[] = [
                'category' => $category,
                'services' => $this->catalog->listServices([
                    'category' => $category->slug,
                    'per_page' => 12,
                ]),
            ];
        }

        return view('public.solutions', compact('grouped', 'settings'));
    }

    public function portfolio(Request $request): View
    {
        abort(404);
    }

    public function portfolioShow(string $slug): View
    {
        abort(404);
    }

    public function about(): View
    {
        $settings = $this->companySettings->getPublicSettings();
        $featured = $this->catalog->featuredServices(4);

        return view('public.about', compact('settings', 'featured'));
    }

    public function contact(): View
    {
        $settings = $this->companySettings->getPublicSettings();
        $options = $this->content->requestOptions();

        return view('public.contact', compact('settings', 'options'));
    }

    public function storeContact(StoreContactRequest $request): RedirectResponse
    {
        $lead = $this->leads->submitContactMessage($request->validated());

        return redirect()
            ->route('request.confirmation')
            ->with('request_number', $lead->request_no)
            ->with('success_message', __('Your message has been sent successfully.'));
    }

    public function switchLocale(string $locale): RedirectResponse
    {
        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        session(['locale' => $locale]);

        return redirect()->back(fallback: url('/'));
    }
}
