<?php

namespace App\Http\Controllers\Web\Portal;

use App\Enums\RequestPriority;
use App\Enums\RequestType;
use App\Http\Controllers\Controller;
use App\Models\CustomerRequest;
use App\Models\Quotation;
use App\Models\Service;
use App\Services\CustomerRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $customer = $request->user()->portalCustomer;
        return view('portal.dashboard', ['customer' => $customer, 'kpis' => [
            __('Requests') => $customer->requests()->count(),
            __('Quotations') => $customer->quotations()->count(),
        ]]);
    }

    public function requestsIndex(Request $request): View
    {
        return view('portal.requests.index', [
            'requests' => $request->user()->portalCustomer->requests()->latest()->paginate($this->perPage($request, 20))->withQueryString(),
        ]);
    }

    public function requestsCreate(): View
    {
        return view('portal.requests.form', [
            'types' => collect(RequestType::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)]),
            'priorities' => collect(RequestPriority::cases())->mapWithKeys(fn ($case) => [$case->value => Str::headline($case->value)]),
            'services' => Service::public()->orderBy('sort_order')->pluck('name', 'id'),
        ]);
    }

    public function requestsStore(Request $request, CustomerRequestService $service): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => ['nullable', 'exists:services,id'],
            'request_type' => ['required', Rule::enum(RequestType::class)],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::enum(RequestPriority::class)],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
        ]);

        $record = $service->create([
            ...$data,
            'customer_id' => $request->user()->portalCustomer->id,
            'source' => 'PORTAL',
        ], $request->user()->id);

        return redirect()->route('portal.requests.show', $record)->with('status', __('Request submitted.'));
    }

    public function requestsShow(Request $request, CustomerRequest $customerRequest): View
    {
        $this->own($request, $customerRequest->customer_id);
        return view('portal.requests.show', ['customerRequest' => $customerRequest->load('service')]);
    }

    public function quotationsIndex(Request $request): View
    {
        return view('portal.documents.index', [
            'title' => __('Quotations'),
            'records' => $request->user()->portalCustomer->quotations()->latest()->paginate($this->perPage($request, 20))->withQueryString(),
            'number' => 'quotation_no',
            'routeBase' => 'portal.quotations',
        ]);
    }

    public function quotationsShow(Request $request, Quotation $quotation): View
    {
        $this->own($request, $quotation->customer_id);
        return view('portal.documents.show', ['record' => $quotation->load(['items', 'currency']), 'kind' => 'quotation']);
    }

    private function own(Request $request, int $customerId): void
    {
        abort_unless($request->user()->portalCustomer->id === $customerId, 404);
    }
}
