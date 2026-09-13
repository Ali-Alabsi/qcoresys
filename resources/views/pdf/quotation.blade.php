@php
    $settings = \App\Models\Setting::query()
        ->whereIn('key', [
            'company_name', 'company_description_ar', 'company_description_en', 'company_description',
            'company_email', 'company_phone', 'company_logo', 'bank_details', 'default_document_terms',
        ])
        ->pluck('value', 'key');

    $locale = app()->getLocale();
    $tagline = $locale === 'ar'
        ? ($settings['company_description_ar'] ?? $settings['company_description'] ?? '')
        : ($settings['company_description_en'] ?? $settings['company_description'] ?? '');

    $contacts = collect([
        $settings['company_email'] ?? null,
        $settings['company_phone'] ?? null,
    ])->filter()->implode(' | ');

    $logoRelative = $settings['company_logo'] ?? null;
    $logoPath = null;
    $logoUrl = null;
    if ($logoRelative) {
        if (is_file(public_path($logoRelative))) {
            $logoPath = public_path($logoRelative);
            $logoUrl = asset($logoRelative);
        } elseif (is_file(storage_path('app/public/'.$logoRelative))) {
            $logoPath = storage_path('app/public/'.$logoRelative);
            $logoUrl = asset('storage/'.$logoRelative);
        }
        if ($logoPath && ! empty($forPdf) && ! extension_loaded('gd')) {
            $logoPath = null;
        }
    }

    $start = $quotation->quotation_date;
    $end = $quotation->valid_until;
    $durationLabel = null;
    if ($start && $end) {
        $days = $start->diffInDays($end);
        $durationLabel = $days.' '.__('days');
    }
@endphp
@include('pdf.document', [
    'forPdf' => $forPdf ?? true,
    'previewActions' => $previewActions ?? null,
    'docTitle' => __('QUOTATION'),
    'docNo' => $quotation->quotation_no,
    'companyName' => $settings['company_name'] ?? 'QCoreSys',
    'companyTagline' => $tagline,
    'companyContacts' => $contacts,
    'logoPath' => $logoPath,
    'logoUrl' => $logoUrl,
    'customerName' => $quotation->customer->name,
    'customerEmail' => $quotation->customer->email,
    'customerPhone' => $quotation->customer->phone,
    'datesHeader' => __('Validity details'),
    'dateLabel' => __('Quotation start date'),
    'dateValue' => optional($start)->format('Y-m-d'),
    'untilLabel' => __('Valid until'),
    'untilValue' => optional($end)->format('Y-m-d'),
    'durationLabel' => $durationLabel,
    'items' => $quotation->items->map(fn ($item) => [
        'description' => $item->description,
        'quantity' => $item->quantity,
        'unit_price' => $item->unit_price,
        'total' => $item->total,
    ])->all(),
    'currencyCode' => $quotation->currency->code,
    'subtotal' => $quotation->subtotal,
    'discountAmount' => $quotation->discount_amount,
    'otherAmount' => $quotation->other_amount,
    'taxAmount' => $quotation->tax_amount,
    'totalAmount' => $quotation->total_amount,
    'terms' => $quotation->terms_and_conditions ?: ($settings['default_document_terms'] ?? null),
])
