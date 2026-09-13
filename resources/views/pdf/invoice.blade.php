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

    $start = $invoice->invoice_date;
    $end = $invoice->due_date;
    $durationLabel = null;
    if ($start && $end) {
        $days = $start->diffInDays($end);
        $durationLabel = $days.' '.__('days');
    }
@endphp
@include('pdf.document', [
    'forPdf' => $forPdf ?? true,
    'previewActions' => $previewActions ?? null,
    'docTitle' => __('INVOICE'),
    'docNo' => $invoice->invoice_no,
    'companyName' => $settings['company_name'] ?? 'QCoreSys',
    'companyTagline' => $tagline,
    'companyContacts' => $contacts,
    'logoPath' => $logoPath,
    'logoUrl' => $logoUrl,
    'customerName' => $invoice->customer->name,
    'customerEmail' => $invoice->customer->email,
    'customerPhone' => $invoice->customer->phone,
    'datesHeader' => __('Billing details'),
    'dateLabel' => __('Invoice date'),
    'dateValue' => optional($start)->format('Y-m-d'),
    'untilLabel' => __('Due date'),
    'untilValue' => optional($end)->format('Y-m-d'),
    'durationLabel' => $durationLabel,
    'items' => $invoice->items->map(fn ($item) => [
        'description' => $item->description,
        'quantity' => $item->quantity,
        'unit_price' => $item->unit_price,
        'total' => $item->total_amount,
    ])->all(),
    'currencyCode' => $invoice->currency->code,
    'subtotal' => $invoice->subtotal,
    'discountAmount' => $invoice->discount_amount,
    'otherAmount' => $invoice->other_amount,
    'taxAmount' => $invoice->tax_amount,
    'totalAmount' => $invoice->total_amount,
    'terms' => $invoice->payment_terms ?: ($settings['default_document_terms'] ?? null),
])
