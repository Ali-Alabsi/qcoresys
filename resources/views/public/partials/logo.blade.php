@php
    $companyName = $companyName ?? ($settings['company_name'] ?? 'QCoreSys');
    $heightClass = $heightClass ?? 'h-9';
    $showName = $showName ?? false;
    $nameClass = $nameClass ?? 'text-base font-semibold text-brand-navy';

    $logoUrl = null;
    $companyLogo = $settings['company_logo'] ?? null;

    if (! empty($companyLogo)) {
        if (is_file(public_path($companyLogo))) {
            $logoUrl = asset($companyLogo);
        } elseif (is_file(storage_path('app/public/'.$companyLogo))) {
            $logoUrl = asset('storage/'.$companyLogo);
        } elseif (is_file(public_path('images/'.$companyLogo))) {
            $logoUrl = asset('images/'.$companyLogo);
        }
    }

    if ($logoUrl === null && is_file(public_path('images/brand/qcore-logo.png'))) {
        $logoUrl = asset('images/brand/qcore-logo.png');
    }

    $showName = $showName || $logoUrl === null;
@endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}"
         alt="{{ $companyName }}"
         class="{{ $heightClass }} w-auto"
         loading="eager">
@endif
@if ($showName)
    <span class="{{ $nameClass }}">{{ $companyName }}</span>
@endif
