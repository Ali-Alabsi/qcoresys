@php
    use App\Support\ArabicPdfText;

    $settings = \App\Models\Setting::query()
        ->whereIn('key', [
            'company_name', 'company_description_ar', 'company_description_en', 'company_description',
            'company_email', 'company_phone', 'company_logo',
        ])
        ->pluck('value', 'key');

    $locale = app()->getLocale();
    $tagline = $locale === 'ar'
        ? ($settings['company_description_ar'] ?? $settings['company_description'] ?? '')
        : ($settings['company_description_en'] ?? $settings['company_description'] ?? '');

    $forPdf = ! empty($forPdf);
    $t = static function (?string $value) use ($forPdf): string {
        $value = (string) ($value ?? '');

        return $forPdf ? ArabicPdfText::shape($value) : $value;
    };
    $money = static fn ($value) => number_format((float) $value, 2);

    $logoPath = null;
    $logoRelative = $settings['company_logo'] ?? null;
    if ($logoRelative) {
        if (is_file(public_path($logoRelative))) {
            $logoPath = public_path($logoRelative);
        } elseif (is_file(storage_path('app/public/'.$logoRelative))) {
            $logoPath = storage_path('app/public/'.$logoRelative);
        }
        if ($logoPath && $forPdf && ! extension_loaded('gd')) {
            $logoPath = null;
        }
    }
@endphp
<!DOCTYPE html>
<html lang="ar" dir="{{ $forPdf ? 'ltr' : 'rtl' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Account statement') }} — {{ $account->account_code }}</title>
    @if(!$forPdf)
        <link href="https://fonts.bunny.net/css?family=tajawal:400,500,700&display=swap" rel="stylesheet">
    @endif
    <style>
        @if($forPdf)
        @font-face {
            font-family: 'Tajawal';
            font-style: normal;
            font-weight: 400;
            src: url('{{ str_replace('\\', '/', storage_path('fonts/Tajawal-Regular.ttf')) }}') format('truetype');
        }
        @font-face {
            font-family: 'Tajawal';
            font-style: normal;
            font-weight: 700;
            src: url('{{ str_replace('\\', '/', storage_path('fonts/Tajawal-Bold.ttf')) }}') format('truetype');
        }
        @endif
        @page { size: A4 landscape; margin: 10mm 8mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Tajawal', DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
            direction: {{ $forPdf ? 'ltr' : 'rtl' }};
            text-align: right;
        }
        .header { margin-bottom: 12px; border-bottom: 3px solid #0B2545; padding-bottom: 8px; }
        .header table { width: 100%; border-collapse: collapse; }
        .company { font-size: 16px; font-weight: 700; color: #0B2545; }
        .tagline { font-size: 10px; color: #64748b; margin-top: 2px; }
        .doc-title { font-size: 18px; font-weight: 700; color: #0B2545; }
        .meta { margin: 10px 0 14px; font-size: 11px; }
        .meta strong { color: #0B2545; }
        table.ledger { width: 100%; border-collapse: collapse; }
        table.ledger th, table.ledger td {
            border: 1px solid #e2e8f0;
            padding: 5px 6px;
            vertical-align: middle;
        }
        table.ledger th {
            background: #0B2545;
            color: #fff;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.ledger td.num, table.ledger th.num { text-align: left; font-family: DejaVu Sans, monospace; }
        table.ledger tr.section { background: #f1f5f9; font-weight: 700; }
        table.ledger tr.total { background: #e2e8f0; font-weight: 700; }
        .logo { max-height: 40px; max-width: 90px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width:60%;">
                    <div class="company">{{ $t($settings['company_name'] ?? 'QCoreSys') }}</div>
                    @if($tagline)
                        <div class="tagline">{{ $t($tagline) }}</div>
                    @endif
                </td>
                <td style="width:40%; text-align:left;">
                    @if($logoPath)
                        <img class="logo" src="{{ $logoPath }}" alt="">
                    @endif
                    <div class="doc-title">{{ $t(__('Account statement')) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="meta">
        <div><strong>{{ $t(__('Account')) }}:</strong> {{ $account->account_code }} — {{ $t($ledger['account_name'] ?? $account->localized_name) }}</div>
        <div><strong>{{ $t(__('From')) }}:</strong> {{ $ledger['from'] }} &nbsp;|&nbsp; <strong>{{ $t(__('To')) }}:</strong> {{ $ledger['to'] }}</div>
    </div>

    <table class="ledger">
        <thead>
            <tr>
                <th>{{ $t(__('Date')) }}</th>
                <th>{{ $t(__('Reference')) }}</th>
                <th>{{ $t(__('Description')) }}</th>
                <th class="num">{{ $t(__('Debit')) }}</th>
                <th class="num">{{ $t(__('Credit')) }}</th>
                <th class="num">{{ $t(__('Balance')) }}</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section">
                <td colspan="3">{{ $t(__('Opening balance')) }}</td>
                <td class="num">—</td>
                <td class="num">—</td>
                <td class="num">{{ $money($ledger['opening_balance']) }}</td>
            </tr>
            @forelse ($ledger['lines'] as $line)
                <tr>
                    <td>{{ $line['entry_date'] }}</td>
                    <td>{{ $line['entry_no'] }}</td>
                    <td>{{ $t($line['description'] ?? '') }}</td>
                    <td class="num">{{ $line['debit'] > 0 ? $money($line['debit']) : '—' }}</td>
                    <td class="num">{{ $line['credit'] > 0 ? $money($line['credit']) : '—' }}</td>
                    <td class="num">{{ $money($line['running_balance']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#64748b;">{{ $t(__('No ledger entries.')) }}</td>
                </tr>
            @endforelse
            <tr class="total">
                <td colspan="3">{{ $t(__('Closing balance')) }}</td>
                <td class="num">{{ $money($ledger['total_debit'] ?? 0) }}</td>
                <td class="num">{{ $money($ledger['total_credit'] ?? 0) }}</td>
                <td class="num">{{ $money($ledger['closing_balance']) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
