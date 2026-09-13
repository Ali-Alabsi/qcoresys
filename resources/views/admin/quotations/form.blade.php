@include('admin.shared.document-form', [
    'title' => $title,
    'action' => $action,
    'method' => $method,
    'record' => $record,
    'customers' => $customers,
    'currencies' => $currencies,
    'initialItems' => $initialItems,
    'dateField' => 'quotation_date',
    'untilField' => 'valid_until',
    'untilLabel' => __('Valid until'),
    'termsField' => 'terms_and_conditions',
    'termsLabel' => __('Terms'),
])
