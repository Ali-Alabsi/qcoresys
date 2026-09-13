@include('admin.shared.document-form', [
    'title' => $title,
    'action' => $action,
    'method' => $method,
    'record' => $record,
    'customers' => $customers,
    'currencies' => $currencies,
    'initialItems' => $initialItems,
    'dateField' => 'invoice_date',
    'untilField' => 'due_date',
    'untilLabel' => __('Due date'),
    'termsField' => 'payment_terms',
    'termsLabel' => __('Payment terms'),
])
