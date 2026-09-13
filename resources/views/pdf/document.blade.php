<!DOCTYPE html>
<html lang="ar" dir="{{ !empty($forPdf) ? 'ltr' : 'rtl' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $docTitle }} — {{ $docNo }}</title>
    @if(empty($forPdf))
        <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700&display=swap" rel="stylesheet">
    @endif
    <style>
        @if(!empty($forPdf))
        @font-face {
            font-family: 'Tajawal';
            font-style: normal;
            font-weight: 400;
            src: url('{{ str_replace('\\', '/', storage_path('fonts/Tajawal-Regular.ttf')) }}') format('truetype');
        }
        @font-face {
            font-family: 'Tajawal';
            font-style: normal;
            font-weight: 500;
            src: url('{{ str_replace('\\', '/', storage_path('fonts/Tajawal-Medium.ttf')) }}') format('truetype');
        }
        @font-face {
            font-family: 'Tajawal';
            font-style: normal;
            font-weight: 700;
            src: url('{{ str_replace('\\', '/', storage_path('fonts/Tajawal-Bold.ttf')) }}') format('truetype');
        }
        @endif
        @page { size: A4 portrait; margin: 10mm 8mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Tajawal', DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.45;
            @if(!empty($forPdf))
            direction: ltr;
            text-align: right;
            @else
            direction: rtl;
            text-align: right;
            background: #eef2f6;
            padding: 16px 12px 40px;
            @endif
        }
        .preview-toolbar {
            max-width: 800px;
            margin: 0 auto 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: space-between;
            align-items: center;
        }
        .preview-toolbar .hint { color: #64748b; font-size: 13px; }
        .preview-toolbar .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .preview-btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .preview-btn-primary { background: #0B2545; color: #fff; }
        .preview-btn-secondary { background: #00b4d8; color: #fff; }
        .preview-btn-muted { background: #64748b; color: #fff; }
        .sheet {
            border-top: 5px solid #0B2545;
            padding-top: 8px;
            @if(empty($forPdf))
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px 28px;
            border-radius: 6px;
            box-shadow: 0 4px 18px rgba(11, 37, 69, 0.08);
            @endif
        }
        .header-block { width: 100%; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1.5px solid #e2e8f0; }
        .header-table { width: 100%; border-collapse: collapse; }
        .brand-title { font-size: 18px; font-weight: bold; color: #0B2545; }
        .brand-tagline { font-size: 10px; color: #64748b; margin-top: 2px; }
        .brand-contacts { font-size: 9px; color: #64748b; margin-top: 2px; direction: ltr; unicode-bidi: embed; text-align: right; }
        .doc-meta { text-align: left; direction: ltr; }
        .doc-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 4px;
            direction: ltr;
            unicode-bidi: embed;
        }
        .doc-heading { font-size: 16px; font-weight: bold; color: #0B2545; text-align: right; direction: rtl; }
        .meta-grid { width: 100%; margin: 8px 0; border-collapse: separate; border-spacing: 8px 0; }
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            vertical-align: top;
            width: 50%;
            text-align: right;
        }
        .info-card h4 {
            font-size: 10px;
            color: #134074;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .info-card .name { font-weight: bold; color: #0B2545; font-size: 11px; }
        .info-card .muted { color: #64748b; font-size: 10px; direction: ltr; unicode-bidi: embed; text-align: right; }
        .info-table { width: 100%; font-size: 10px; }
        .info-table td { padding: 2px 0; }
        .info-table .label { color: #64748b; width: 52%; text-align: right; }
        .info-table .val { font-weight: bold; width: 48%; text-align: left; direction: ltr; unicode-bidi: embed; }
        .accent { color: #00b4d8; }
        .services-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .services-table th {
            background: #0B2545;
            color: #fff;
            padding: 6px 8px;
            font-size: 10px;
            text-align: right;
        }
        .services-table th.num, .services-table td.num {
            text-align: left;
            direction: ltr;
            unicode-bidi: embed;
        }
        .services-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: top;
            text-align: right;
        }
        .item-name { font-weight: bold; color: #0B2545; }
        .totals-wrap { width: 100%; margin-top: 8px; }
        .totals-box {
            width: 280px;
            @if(!empty($forPdf)) float: left; @else float: left; @endif
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            text-align: right;
        }
        .total-line { overflow: hidden; padding: 2px 0; font-size: 10px; color: #64748b; }
        .total-line span.label { float: right; }
        .total-line span.num { float: left; direction: ltr; unicode-bidi: embed; font-weight: bold; }
        .total-line.discount { color: #dc2626; }
        .total-line.grand {
            border-top: 1.5px solid #0B2545;
            margin-top: 4px;
            padding-top: 4px;
            color: #0B2545;
            font-size: 12px;
            font-weight: bold;
        }
        .clear { clear: both; }
        .footer-block { margin-top: 14px; padding-top: 8px; border-top: 1px solid #e2e8f0; text-align: right; }
        .footer-block h5 { font-size: 10px; color: #0B2545; margin-bottom: 4px; }
        .terms-text { font-size: 9px; color: #64748b; white-space: pre-line; }
        .sign-section { width: 100%; margin-top: 28px; }
        .sign-box { width: 44%; text-align: center; vertical-align: bottom; }
        .sign-line {
            border-top: 1px dashed #cbd5e1;
            padding-top: 4px;
            font-size: 10px;
            color: #64748b;
        }
        .logo { max-height: 42px; max-width: 100px; }
        @media print {
            body { background: #fff !important; padding: 0 !important; }
            .preview-toolbar { display: none !important; }
            .sheet { box-shadow: none !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>
@php
    use App\Support\ArabicPdfText;
    $forPdf = !empty($forPdf);
    $t = static function (?string $value) use ($forPdf): string {
        $value = (string) ($value ?? '');
        return $forPdf ? ArabicPdfText::shape($value) : $value;
    };
    $money = fn ($value) => number_format((float) $value, 2);
    $currencyCode = $currencyCode ?? '';
    $durationLabel = $durationLabel ?? null;
    $logoUrl = $logoUrl ?? null;
@endphp

@if(!$forPdf && !empty($previewActions))
<div class="preview-toolbar">
    <div class="hint">{{ __('Document preview — review before exporting PDF') }}</div>
    <div class="actions">
        @if(!empty($previewActions['back']))
            <a class="preview-btn preview-btn-muted" href="{{ $previewActions['back'] }}">{{ __('Back') }}</a>
        @endif
        @if(!empty($previewActions['print']))
            <a class="preview-btn preview-btn-secondary" target="_blank" href="{{ $previewActions['print'] }}">{{ __('Print') }}</a>
        @endif
        @if(!empty($previewActions['export']))
            <a class="preview-btn preview-btn-primary" href="{{ $previewActions['export'] }}">{{ __('Export PDF') }}</a>
        @endif
    </div>
</div>
@endif

<div class="sheet">
    <div class="header-block">
        <table class="header-table">
            <tr>
                @if($forPdf)
                    <td class="doc-meta" style="vertical-align:middle; width:38%;">
                        <div class="doc-badge">{{ $docNo }}</div>
                        <div class="doc-heading">{{ $t($docTitle) }}</div>
                    </td>
                    <td style="width:62%; vertical-align:middle; text-align:right;">
                        <table style="width:100%;">
                            <tr>
                                <td style="vertical-align:middle; text-align:right;">
                                    <div class="brand-title">{{ $t($companyName) }}</div>
                                    @if($companyTagline)<div class="brand-tagline">{{ $t($companyTagline) }}</div>@endif
                                    @if($companyContacts)<div class="brand-contacts">{{ $companyContacts }}</div>@endif
                                </td>
                                @if(!empty($logoPath) && is_file($logoPath))
                                    <td style="width:110px; vertical-align:middle; text-align:left;"><img class="logo" src="{{ $logoPath }}" alt="logo"></td>
                                @endif
                            </tr>
                        </table>
                    </td>
                @else
                    <td style="width:62%; vertical-align:middle; text-align:right;">
                        <table style="width:100%;">
                            <tr>
                                @if($logoUrl)
                                    <td style="width:110px; vertical-align:middle;"><img class="logo" src="{{ $logoUrl }}" alt="logo"></td>
                                @endif
                                <td style="vertical-align:middle; text-align:right;">
                                    <div class="brand-title">{{ $t($companyName) }}</div>
                                    @if($companyTagline)<div class="brand-tagline">{{ $t($companyTagline) }}</div>@endif
                                    @if($companyContacts)<div class="brand-contacts">{{ $companyContacts }}</div>@endif
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="doc-meta" style="vertical-align:middle; width:38%;">
                        <div class="doc-badge">{{ $docNo }}</div>
                        <div class="doc-heading">{{ $t($docTitle) }}</div>
                    </td>
                @endif
            </tr>
        </table>
    </div>

    <table class="meta-grid">
        <tr>
            @if($forPdf)
                <td class="info-card">
                    <h4>{{ $t($datesHeader.':') }}</h4>
                    <table class="info-table">
                        <tr>
                            <td class="val">{{ $dateValue }}</td>
                            <td class="label">{{ $t($dateLabel.':') }}</td>
                        </tr>
                        <tr>
                            <td class="val">{{ $untilValue ?: '—' }}</td>
                            <td class="label">{{ $t($untilLabel.':') }}</td>
                        </tr>
                        @if($durationLabel)
                            <tr>
                                <td class="val accent">{{ $t($durationLabel) }}</td>
                                <td class="label">{{ $t(__('Validity duration').':') }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
                <td class="info-card">
                    <h4>{{ $t(__('Presented to').':') }}</h4>
                    <div class="name">{{ $t($customerName) }}</div>
                    @if($customerEmail)<div class="muted">{{ $customerEmail }}</div>@endif
                    @if($customerPhone)<div class="muted">{{ $customerPhone }}</div>@endif
                </td>
            @else
                <td class="info-card">
                    <h4>{{ $t(__('Presented to').':') }}</h4>
                    <div class="name">{{ $t($customerName) }}</div>
                    @if($customerEmail)<div class="muted">{{ $customerEmail }}</div>@endif
                    @if($customerPhone)<div class="muted">{{ $customerPhone }}</div>@endif
                </td>
                <td class="info-card">
                    <h4>{{ $t($datesHeader.':') }}</h4>
                    <table class="info-table">
                        <tr>
                            <td class="label">{{ $t($dateLabel.':') }}</td>
                            <td class="val">{{ $dateValue }}</td>
                        </tr>
                        <tr>
                            <td class="label">{{ $t($untilLabel.':') }}</td>
                            <td class="val">{{ $untilValue ?: '—' }}</td>
                        </tr>
                        @if($durationLabel)
                            <tr>
                                <td class="label">{{ $t(__('Validity duration').':') }}</td>
                                <td class="val accent">{{ $t($durationLabel) }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
            @endif
        </tr>
    </table>

    <table class="services-table">
        <thead>
            <tr>
                @if($forPdf)
                    <th class="num" style="width:25%;">{{ $t(__('Total')) }}</th>
                    <th class="num" style="width:25%;">{{ $t(__('Price')) }}</th>
                    <th style="width:50%;">{{ $t(__('Service / scope')) }}</th>
                @else
                    <th style="width:50%;">{{ $t(__('Service / scope')) }}</th>
                    <th class="num" style="width:25%;">{{ $t(__('Price')) }}</th>
                    <th class="num" style="width:25%;">{{ $t(__('Total')) }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    @if($forPdf)
                        <td class="num">{{ $currencyCode }} {{ $money($item['total']) }}</td>
                        <td class="num">{{ $currencyCode }} {{ $money($item['unit_price']) }}</td>
                        <td><div class="item-name">{{ $t($item['description']) }}</div></td>
                    @else
                        <td><div class="item-name">{{ $t($item['description']) }}</div></td>
                        <td class="num">{{ $currencyCode }} {{ $money($item['unit_price']) }}</td>
                        <td class="num">{{ $currencyCode }} {{ $money($item['total']) }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-wrap">
        <div class="totals-box">
            <div class="total-line">
                <span class="label">{{ $t(__('Subtotal').':') }}</span>
                <span class="num">{{ $currencyCode }} {{ $money($subtotal) }}</span>
            </div>
            @if((float) $discountAmount > 0)
                <div class="total-line discount">
                    <span class="label">{{ $t(__('Document discount').' (-):') }}</span>
                    <span class="num">{{ $currencyCode }} {{ $money($discountAmount) }}</span>
                </div>
            @endif
            @if((float) $otherAmount != 0)
                <div class="total-line">
                    <span class="label">{{ $t(__('Other amount').':') }}</span>
                    <span class="num">{{ $currencyCode }} {{ $money($otherAmount) }}</span>
                </div>
            @endif
            @if((float) $taxAmount > 0)
                <div class="total-line">
                    <span class="label">{{ $t(__('Tax').':') }}</span>
                    <span class="num">{{ $currencyCode }} {{ $money($taxAmount) }}</span>
                </div>
            @endif
            <div class="total-line grand">
                <span class="label">{{ $t(__('Net amount due').':') }}</span>
                <span class="num">{{ $currencyCode }} {{ $money($totalAmount) }}</span>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="footer-block">
        <h5>{{ $t(__('Terms and conditions').':') }}</h5>
        <div class="terms-text">{{ $t($terms ?: __('Default document terms')) }}</div>
    </div>

    <table class="sign-section">
        <tr>
            @if($forPdf)
                <td class="sign-box"><div class="sign-line">{{ $t(__('Client signature')) }}</div></td>
                <td class="sign-box"><div class="sign-line">{{ $t(__('Company signature')) }}</div></td>
            @else
                <td class="sign-box"><div class="sign-line">{{ $t(__('Company signature')) }}</div></td>
                <td class="sign-box"><div class="sign-line">{{ $t(__('Client signature')) }}</div></td>
            @endif
        </tr>
    </table>
</div>
</body>
</html>
