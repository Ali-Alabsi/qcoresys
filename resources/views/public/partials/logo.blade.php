@php
    $companyName = $companyName ?? ($settings['company_name'] ?? 'QCoreSys');
    $logoPath = ! empty($settings['company_logo'] ?? null)
        ? asset('storage/'.$settings['company_logo'])
        : asset('images/brand/qcore-logo.png');
    $heightClass = $heightClass ?? 'h-9';
    $showName = $showName ?? false;
    $nameClass = $nameClass ?? 'text-base font-semibold text-brand-navy';
@endphp

<img src="{{ $logoPath }}"
     alt="{{ $companyName }}"
     class="{{ $heightClass }} w-auto"
     loading="eager">
@if ($showName)
    <span class="{{ $nameClass }}">{{ $companyName }}</span>
@endif
