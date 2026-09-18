<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AccountType;
use App\Enums\BillingType;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PricingType;
use App\Enums\RequestPriority;
use App\Enums\RequestType;
use App\Enums\ServiceType;
use App\Exceptions\DomainException;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Attachment;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Faq;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\PortfolioProject;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\ServiceBenefit;
use App\Models\ServiceCategory;
use App\Models\ServiceFeature;
use App\Models\ServiceProcessStep;
use App\Models\Setting;
use App\Models\Technology;
use App\Services\AccountBalanceService;
use App\Services\ChartOfAccountsService;
use App\Services\CustomerRequestService;
use App\Services\CustomerService;
use App\Services\DocumentPdfService;
use App\Services\ExpenseService;
use App\Services\InvoiceService;
use App\Services\JournalEntryService;
use App\Services\PaymentService;
use App\Services\Public\CompanySettingsService;
use App\Services\Public\PublicCatalogService;
use App\Services\QuotationService;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlatformController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', ['kpis' => [
            __('Customers') => Customer::count(),
            __('Open requests') => CustomerRequest::open()->count(),
            __('Quotations') => Quotation::count(),
            __('Posted journals') => JournalEntry::posted()->count(),
        ]]);
    }

    public function customersIndex(Request $request): View
    {
        return $this->indexView(__('Customers'), Customer::latest()->paginate($this->perPage($request))->withQueryString(), [
            'customer_code' => __('Code'), 'name' => __('Name'), 'email' => __('Email'), 'status' => __('Status'),
        ], 'admin.customers');
    }

    public function customersCreate(): View
    {
        return $this->formView(__('New customer'), route('admin.customers.store'), $this->customerFields());
    }

    public function customersStore(Request $request, CustomerService $service): RedirectResponse
    {
        $customer = $service->create($this->validateCustomer($request), $request->user()->id);
        return redirect()->route('admin.customers.show', $customer)->with('status', __('Customer created.'));
    }

    public function customersShow(Customer $customer, AccountBalanceService $balances): View
    {
        $customer->load(['account']);

        $invoices = $customer->invoices()
            ->latest('invoice_date')
            ->latest('id')
            ->get();

        $totalInvoiced = (float) $invoices->sum('total_amount');
        $totalPaid = (float) $invoices->sum('paid_amount');
        $totalOutstanding = (float) $invoices->sum('remaining_amount');

        if ($customer->account) {
            $balance = $balances->balanceAsOf($customer->account, now()->toDateString());
            $customer->setAttribute(
                'receivable_balance',
                number_format((float) $balance['foreign'], 2)
            );
        }

        return view('admin.customers.show', [
            'title' => $customer->name,
            'customer' => $customer,
            'invoices' => $invoices,
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }

    public function customersEdit(Customer $customer): View
    {
        return $this->formView(__('Edit customer'), route('admin.customers.update', $customer), $this->customerFields(), $customer, 'PUT');
    }

    public function customersUpdate(Request $request, Customer $customer, CustomerService $service): RedirectResponse
    {
        $service->update($customer, $this->validateCustomer($request), $request->user()->id);
        return redirect()->route('admin.customers.show', $customer)->with('status', __('Customer updated.'));
    }

    public function requestsIndex(Request $request): View
    {
        return $this->indexView(__('Customer requests'), CustomerRequest::with('customer')->latest()->paginate($this->perPage($request))->withQueryString(), [
            'request_no' => __('Number'), 'customer.name' => __('Customer'), 'subject' => __('Subject'), 'status' => __('Status'),
        ], 'admin.customer-requests');
    }

    public function requestsCreate(): View
    {
        return $this->formView(__('New request'), route('admin.customer-requests.store'), $this->requestFields());
    }

    public function requestsStore(Request $request, CustomerRequestService $service): RedirectResponse
    {
        $record = $service->create($this->validateRequest($request), $request->user()->id);
        return redirect()->route('admin.customer-requests.show', $record)->with('status', __('Request created.'));
    }

    public function requestsShow(CustomerRequest $customerRequest): View
    {
        $customerRequest->load(['customer', 'service']);
        return view('admin.shared.show', ['title' => $customerRequest->request_no, 'record' => $customerRequest, 'fields' => [
            'customer.name', 'subject', 'request_type', 'priority', 'status', 'description', 'estimated_budget',
        ]]);
    }

    public function requestsEdit(CustomerRequest $customerRequest): View
    {
        return $this->formView(__('Edit request'), route('admin.customer-requests.update', $customerRequest), $this->requestFields(), $customerRequest, 'PUT');
    }

    public function requestsUpdate(Request $request, CustomerRequest $customerRequest): RedirectResponse
    {
        $customerRequest->update([...$this->validateRequest($request), 'updated_by' => $request->user()->id]);
        return redirect()->route('admin.customer-requests.show', $customerRequest)->with('status', __('Request updated.'));
    }

    public function quotationsIndex(Request $request): View
    {
        return $this->indexView(__('Quotations'), Quotation::with('customer')->latest()->paginate($this->perPage($request))->withQueryString(), [
            'quotation_no' => __('Number'), 'customer.name' => __('Customer'), 'quotation_date' => __('Date'),
            'total_amount' => __('Total'), 'status' => __('Status'),
        ], 'admin.quotations');
    }

    public function quotationsCreate(): View
    {
        return $this->documentFormView(__('New quotation'), route('admin.quotations.store'), 'admin.quotations.form');
    }

    public function quotationsStore(Request $request, QuotationService $service): RedirectResponse
    {
        $data = $this->validateDocumentPayload($request, 'quotation');
        $quotation = $service->create(
            Arr::only($data, ['customer_id', 'quotation_date', 'valid_until', 'currency_id', 'notes', 'terms_and_conditions', 'discount_amount', 'other_amount']),
            $this->normalizedDocumentItems($data['items']),
            $request->user()->id
        );

        return redirect()->route('admin.quotations.show', $quotation)->with('status', __('Quotation created.'));
    }

    public function quotationsShow(Quotation $quotation): View
    {
        $quotation->load(['customer', 'currency', 'items']);
        return view('admin.shared.document', ['title' => $quotation->quotation_no, 'record' => $quotation, 'kind' => 'quotation']);
    }

    public function quotationsEdit(Quotation $quotation): View
    {
        $quotation->load('items');

        return $this->documentFormView(
            __('Edit quotation'),
            route('admin.quotations.update', $quotation),
            'admin.quotations.form',
            $quotation,
            'PUT'
        );
    }

    public function quotationsUpdate(Request $request, Quotation $quotation, QuotationService $service): RedirectResponse
    {
        $data = $this->validateDocumentPayload($request, 'quotation', updating: true);
        $service->update(
            $quotation,
            Arr::only($data, ['quotation_date', 'valid_until', 'notes', 'terms_and_conditions', 'discount_amount', 'other_amount']),
            $this->normalizedDocumentItems($data['items']),
            $request->user()->id
        );

        return redirect()->route('admin.quotations.show', $quotation)->with('status', __('Quotation updated.'));
    }

    public function quotationApprove(Request $request, Quotation $quotation, QuotationService $service): RedirectResponse
    {
        $service->approve($quotation, $request->user()->id);
        return back()->with('status', __('Quotation approved.'));
    }

    public function quotationSend(Quotation $quotation, QuotationService $service): RedirectResponse
    {
        $service->send($quotation);
        return back()->with('status', __('Quotation marked as sent.'));
    }

    public function invoicesIndex(Request $request): View
    {
        return $this->indexView(__('Invoices'), Invoice::with('customer')->latest()->paginate($this->perPage($request))->withQueryString(), [
            'invoice_no' => __('Number'), 'customer.name' => __('Customer'), 'invoice_date' => __('Date'),
            'total_amount' => __('Total'), 'remaining_amount' => __('Balance'), 'status' => __('Status'),
        ], 'admin.invoices');
    }

    public function invoicesCreate(): View
    {
        return $this->documentFormView(__('New invoice'), route('admin.invoices.store'), 'admin.invoices.form');
    }

    public function invoicesStore(Request $request, InvoiceService $service): RedirectResponse
    {
        $data = $this->validateDocumentPayload($request, 'invoice');
        $invoice = $service->create(
            Arr::only($data, ['customer_id', 'invoice_date', 'due_date', 'currency_id', 'account_id', 'notes', 'payment_terms', 'discount_amount', 'other_amount']),
            $this->normalizedDocumentItems($data['items']),
            $request->user()->id
        );

        return redirect()->route('admin.invoices.show', $invoice)->with('status', __('Invoice created.'));
    }

    public function invoicesShow(Invoice $invoice): View
    {
        $invoice->load([
            'customer',
            'currency',
            'items',
            'account',
            'payments' => fn ($query) => $query->with('account')->latest('payment_date')->latest('id'),
        ]);

        return view('admin.shared.document', ['title' => $invoice->invoice_no, 'record' => $invoice, 'kind' => 'invoice']);
    }

    public function invoicesEdit(Invoice $invoice): View
    {
        $invoice->load(['items', 'account']);

        return $this->documentFormView(
            __('Edit invoice'),
            route('admin.invoices.update', $invoice),
            'admin.invoices.form',
            $invoice,
            'PUT'
        );
    }

    public function invoicesUpdate(Request $request, Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        $data = $this->validateDocumentPayload($request, 'invoice', updating: true);
        $service->update(
            $invoice,
            Arr::only($data, ['invoice_date', 'due_date', 'account_id', 'notes', 'payment_terms', 'discount_amount', 'other_amount']),
            $this->normalizedDocumentItems($data['items']),
            $request->user()->id
        );

        return redirect()->route('admin.invoices.show', $invoice)->with('status', __('Invoice updated.'));
    }

    public function invoiceApprove(Request $request, Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        try {
            $service->approve($invoice, $request->user()->id);
        } catch (DomainException $e) {
            return back()->withErrors(['invoice' => $e->getMessage()]);
        }

        return back()->with('status', __('Invoice approved.'));
    }

    public function invoicePost(Request $request, Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        try {
            $service->post($invoice, $request->user()->id);
        } catch (DomainException $e) {
            return back()->withErrors(['invoice' => $e->getMessage()]);
        }

        return back()->with('status', __('Invoice posted.'));
    }

    public function paymentsIndex(Request $request): View
    {
        $payments = Payment::with(['customer', 'invoice'])
            ->latest()
            ->paginate($this->perPage($request))
            ->withQueryString();

        $depositAccounts = Account::postable()
            ->where(fn ($q) => $q->where('is_cash_account', true)->orWhere('is_bank_account', true))
            ->get()
            ->mapWithKeys(fn ($a) => [$a->id => $a->account_code.' — '.$a->localized_name]);

        $outstandingInvoices = Invoice::outstanding()
            ->whereIn('status', [
                InvoiceStatus::Posted,
                InvoiceStatus::PartiallyPaid,
                InvoiceStatus::Overdue,
            ])
            ->orderByDesc('id')
            ->get(['id', 'invoice_no', 'remaining_amount']);

        return view('admin.payments.index', [
            'payments' => $payments,
            'outstandingInvoices' => $outstandingInvoices,
            'depositAccounts' => $depositAccounts,
            'paymentMethods' => $this->enumOptions(PaymentMethod::cases()),
        ]);
    }

    public function paymentsCreate(): RedirectResponse
    {
        return redirect()->route('admin.payments.index', ['new' => 1]);
    }

    public function paymentsStore(Request $request, PaymentService $service): RedirectResponse
    {
        try {
            $data = $request->validate([
                'invoice_id' => ['required', 'exists:invoices,id'],
                'account_id' => [
                    'required',
                    'exists:accounts,id',
                    Rule::exists('accounts', 'id')->where(function ($query) {
                        $query->where('allow_posting', true)
                            ->where(function ($query) {
                                $query->where('is_cash_account', true)
                                    ->orWhere('is_bank_account', true);
                            });
                    }),
                ],
                'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
                'payment_date' => ['required', 'date'],
                'amount' => ['required', 'numeric', 'gt:0'],
                'reference_no' => ['nullable', 'string'],
            ], [
                'account_id.exists' => __('Deposit account must be a cash or bank account.'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e->redirectTo(route('admin.payments.index', ['new' => 1]));
        }

        $invoice = Invoice::query()->findOrFail($data['invoice_id']);
        if ((float) $data['amount'] > (float) $invoice->remaining_amount) {
            return redirect()
                ->route('admin.payments.index', ['new' => 1])
                ->withInput()
                ->withErrors(['amount' => __('Payment amount cannot exceed the invoice remaining balance.')]);
        }

        if (! $request->user()->hasPermission('payments.post')) {
            return redirect()
                ->route('admin.payments.index', ['new' => 1])
                ->withInput()
                ->withErrors(['amount' => __('You do not have permission to post payments.')]);
        }

        $currency = Currency::where('code', 'USD')->firstOrFail();

        try {
            $payment = DB::transaction(function () use ($data, $currency, $request, $service) {
                $payment = $service->create([
                    ...$data,
                    'currency_id' => $currency->id,
                ], $request->user()->id);

                return $service->post($payment, $request->user()->id);
            });
        } catch (DomainException $e) {
            return redirect()
                ->route('admin.payments.index', ['new' => 1])
                ->withInput()
                ->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->route('admin.payments.index')->with('status', __('Payment posted: :number', ['number' => $payment->payment_no]));
    }

    public function paymentPost(Request $request, Payment $payment, PaymentService $service): RedirectResponse
    {
        try {
            $service->post($payment, $request->user()->id);
        } catch (DomainException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return back()->with('status', __('Payment posted.'));
    }

    public function accountsIndex(Request $request, AccountBalanceService $balances): View
    {
        $q = trim((string) $request->input('q', ''));
        $type = (string) $request->input('type', 'all');
        $validTypes = collect(AccountType::cases())->map->value->all();
        if ($type !== 'all' && ! in_array($type, $validTypes, true)) {
            $type = 'all';
        }

        $baseQuery = Account::query()->active()->postable();

        $typeCounts = (clone $baseQuery)
            ->selectRaw('account_type, COUNT(*) as aggregate')
            ->groupBy('account_type')
            ->pluck('aggregate', 'account_type')
            ->map(fn ($count) => (int) $count)
            ->all();

        $totalCount = array_sum($typeCounts);

        $listQuery = (clone $baseQuery)->orderBy('account_code');

        if ($type !== 'all') {
            $listQuery->ofType($type);
        }

        if ($q !== '') {
            $listQuery->where(function ($query) use ($q) {
                $query->where('account_code', 'like', '%'.$q.'%')
                    ->orWhere('account_name', 'like', '%'.$q.'%')
                    ->orWhere('account_name_ar', 'like', '%'.$q.'%');
            });
        }

        $paginator = $listQuery->paginate($this->perPage($request))->withQueryString();
        $asOf = now()->toDateString();

        $accounts = $paginator->getCollection()->map(function (Account $account) use ($balances, $asOf) {
            $accountType = $account->account_type;

            return [
                'id' => $account->id,
                'code' => $account->account_code,
                'name' => $account->localized_name,
                'type' => $accountType->value,
                'type_label' => $accountType->label(),
                'balance' => (float) $balances->balanceAsOf($account, $asOf)['foreign'],
            ];
        });

        $paginator->setCollection($accounts);

        $parentAccounts = Account::query()
            ->where('is_control_account', true)
            ->active()
            ->orderBy('account_code')
            ->get()
            ->mapWithKeys(fn (Account $account) => [
                $account->id => $account->account_code.' — '.$account->localized_name,
            ]);

        $currencies = Currency::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->pluck('code', 'id');

        $accountTypes = collect(AccountType::cases())
            ->mapWithKeys(fn (AccountType $accountType) => [$accountType->value => $accountType->label()])
            ->all();

        return view('admin.accounting.accounts', [
            'accounts' => $paginator,
            'typeCounts' => $typeCounts,
            'totalCount' => $totalCount,
            'activeType' => $type,
            'searchQuery' => $q,
            'parentAccounts' => $parentAccounts,
            'currencies' => $currencies,
            'accountTypes' => $accountTypes,
        ]);
    }

    public function accountsCreate(): RedirectResponse
    {
        return redirect()->route('admin.accounts.index', ['new' => 1]);
    }

    public function accountsStore(Request $request, ChartOfAccountsService $service): RedirectResponse
    {
        try {
            $data = $this->validateAccount($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e->redirectTo(route('admin.accounts.index', ['new' => 1]));
        }

        try {
            $service->create($data, $request->user()->id);
        } catch (DomainException $e) {
            return redirect()
                ->route('admin.accounts.index', ['new' => 1])
                ->withInput()
                ->withErrors(['account_type' => $e->getMessage()]);
        }

        return redirect()->route('admin.accounts.index')->with('status', __('Account created.'));
    }

    public function accountsLedger(Request $request, Account $account, ReportingService $reporting): View
    {
        [$from, $to] = $this->ledgerDateRange($request);
        $ledger = $reporting->generalLedgerPaginated(
            $account->id,
            $from,
            $to,
            $this->perPage($request)
        );

        return view('admin.accounting.ledger', [
            'account' => $account,
            'ledger' => $ledger,
            'from' => $from,
            'to' => $to,
            'paginator' => $ledger['paginator'],
        ]);
    }

    public function accountsLedgerPdf(Request $request, Account $account, ReportingService $reporting, DocumentPdfService $pdf): Response
    {
        [$from, $to] = $this->ledgerDateRange($request);
        $ledger = $reporting->generalLedger($account->id, $from, $to);

        return $pdf->download(
            'pdf.account-ledger',
            ['account' => $account, 'ledger' => $ledger],
            'ledger-'.$account->account_code.'.pdf',
            'landscape'
        );
    }

    public function accountsLedgerExcel(Request $request, Account $account, ReportingService $reporting): StreamedResponse
    {
        [$from, $to] = $this->ledgerDateRange($request);
        $ledger = $reporting->generalLedger($account->id, $from, $to);
        $filename = 'ledger-'.$account->account_code.'.xls';

        $headers = [
            __('Date'),
            __('Reference'),
            __('Description'),
            __('Debit'),
            __('Credit'),
            __('Balance'),
        ];

        $rows = [];
        $rows[] = [__('Opening balance'), '', '', '', '', number_format((float) $ledger['opening_balance'], 2, '.', '')];

        foreach ($ledger['lines'] as $line) {
            $rows[] = [
                $line['entry_date'] ?? '',
                $line['entry_no'] ?? '',
                $line['description'] ?? '',
                number_format((float) $line['debit'], 2, '.', ''),
                number_format((float) $line['credit'], 2, '.', ''),
                number_format((float) $line['running_balance'], 2, '.', ''),
            ];
        }

        $rows[] = [__('Closing balance'), '', '', number_format((float) ($ledger['total_debit'] ?? 0), 2, '.', ''), number_format((float) ($ledger['total_credit'] ?? 0), 2, '.', ''), number_format((float) $ledger['closing_balance'], 2, '.', '')];

        $xml = $this->buildSpreadsheetMl(
            __('Account statement').' — '.$account->account_code.' — '.$account->localized_name,
            $from.' → '.$to,
            $headers,
            $rows
        );

        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function ledgerDateRange(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $validated['from'] ?? now()->startOfYear()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();

        return [(string) $from, (string) $to];
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     */
    private function buildSpreadsheetMl(string $title, string $subtitle, array $headers, array $rows): string
    {
        $esc = static fn (?string $value): string => htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>'."\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'."\n";
        $xml .= '<Worksheet ss:Name="Ledger"><Table>'."\n";
        $xml .= '<Row><Cell><Data ss:Type="String">'.$esc($title).'</Data></Cell></Row>'."\n";
        $xml .= '<Row><Cell><Data ss:Type="String">'.$esc($subtitle).'</Data></Cell></Row>'."\n";
        $xml .= '<Row></Row>'."\n";
        $xml .= '<Row>';
        foreach ($headers as $header) {
            $xml .= '<Cell><Data ss:Type="String">'.$esc($header).'</Data></Cell>';
        }
        $xml .= '</Row>'."\n";

        foreach ($rows as $row) {
            $xml .= '<Row>';
            foreach ($row as $index => $cell) {
                $type = $index >= 3 && is_numeric($cell) ? 'Number' : 'String';
                $xml .= '<Cell><Data ss:Type="'.$type.'">'.$esc((string) $cell).'</Data></Cell>';
            }
            $xml .= '</Row>'."\n";
        }

        $xml .= '</Table></Worksheet></Workbook>';

        return $xml;
    }

    /**
     * @return list<array{id:int,code:string,name:string,type:string,type_label:string,balance:float}>
     */
    private function accountingAccountsPayload(AccountBalanceService $balances): array
    {
        $asOf = now()->toDateString();

        return Account::query()
            ->active()
            ->postable()
            ->orderBy('account_code')
            ->get()
            ->map(function (Account $account) use ($balances, $asOf) {
                $type = $account->account_type;

                return [
                    'id' => $account->id,
                    'code' => $account->account_code,
                    'name' => $account->localized_name,
                    'type' => $type->value,
                    'type_label' => $type->label(),
                    'balance' => (float) $balances->balanceAsOf($account, $asOf)['foreign'],
                ];
            })
            ->values()
            ->all();
    }

    public function expensesIndex(Request $request): View
    {
        return $this->indexView(__('Expenses'), Expense::with('category')->latest()->paginate($this->perPage($request))->withQueryString(), [
            'expense_no' => __('Number'), 'category.name' => __('Category'), 'expense_date' => __('Date'),
            'description' => __('Description'), 'total_amount' => __('Total'), 'status' => __('Status'),
        ], 'admin.expenses', false);
    }

    public function expensesCreate(): View
    {
        return $this->formView(__('New expense'), route('admin.expenses.store'), [
            'category_id' => $this->selectField(__('Category'), ExpenseCategory::pluck('name', 'id')),
            'account_id' => $this->selectField(__('Expense account'), Account::postable()->get()->mapWithKeys(fn ($a) => [$a->id => $a->account_code.' — '.$a->localized_name])),
            'expense_date' => ['label' => __('Date'), 'type' => 'date', 'value' => now()->toDateString()],
            'description' => ['label' => __('Description'), 'type' => 'textarea'],
            'amount' => ['label' => __('Amount'), 'type' => 'number', 'step' => '0.01'],
            'tax_amount' => ['label' => __('Tax'), 'type' => 'number', 'step' => '0.01', 'value' => 0],
        ]);
    }

    public function expensesStore(Request $request, ExpenseService $service): RedirectResponse
    {
        $currency = Currency::where('code', 'USD')->firstOrFail();
        $expense = $service->create([...$request->validate([
            'category_id' => ['required', 'exists:expense_categories,id'], 'account_id' => ['required', 'exists:accounts,id'],
            'expense_date' => ['required', 'date'], 'description' => ['required', 'string'], 'amount' => ['required', 'numeric', 'gt:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
        ]), 'currency_id' => $currency->id], $request->user()->id);
        return redirect()->route('admin.expenses.index')->with('status', __('Expense created: :number', ['number' => $expense->expense_no]));
    }

    public function expenseApprove(Request $request, Expense $expense, ExpenseService $service): RedirectResponse
    {
        $service->approve($expense, $request->user()->id);
        return back()->with('status', __('Expense approved.'));
    }

    public function expensePost(Request $request, Expense $expense, ExpenseService $service): RedirectResponse
    {
        $service->post($expense, $request->user()->id);
        return back()->with('status', __('Expense posted.'));
    }

    public function journalsIndex(Request $request, AccountBalanceService $balances): View
    {
        $paginator = JournalEntry::query()
            ->posted()
            ->with(['lines.account'])
            ->latest('entry_date')
            ->latest('id')
            ->paginate($this->perPage($request, 30))
            ->withQueryString();

        $entries = $paginator->getCollection()->map(function (JournalEntry $entry) {
            return [
                'id' => $entry->id,
                'ref' => $entry->entry_no,
                'date' => $entry->entry_date?->toDateString(),
                'desc' => $entry->description,
                'total' => (float) $entry->total_debit,
                'lines' => $entry->lines->map(fn ($line) => [
                    'account_name' => $line->account?->localized_name ?? (string) $line->account_id,
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                ])->all(),
            ];
        })->all();

        return view('admin.accounting.operations', [
            'entries' => $entries,
            'paginator' => $paginator,
            'accountsPayload' => $this->accountingAccountsPayload($balances),
            'nextRef' => app(\App\Services\DocumentNumberService::class)->peek(\App\Enums\DocumentSequenceKey::Journal),
        ]);
    }

    public function journalsCreate(): RedirectResponse
    {
        return redirect()->route('admin.journals.index', ['new' => 1]);
    }

    public function journalsStore(
        Request $request,
        JournalEntryService $service,
        AccountBalanceService $balances
    ): JsonResponse|RedirectResponse {
        $wantsJson = $request->expectsJson() || $request->ajax();

        $data = $request->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_code' => ['required_without:lines.*.account_id', 'nullable', 'string'],
            'lines.*.account_id' => ['required_without:lines.*.account_code', 'nullable', 'exists:accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string'],
        ]);

        $base = Currency::query()->base()->firstOrFail();
        $accountsByCode = Account::query()
            ->active()
            ->postable()
            ->get()
            ->keyBy('account_code');

        $lines = [];
        foreach ($data['lines'] as $line) {
            $account = null;
            if (! empty($line['account_id'])) {
                $account = Account::query()->find($line['account_id']);
            } elseif (! empty($line['account_code'])) {
                $account = $accountsByCode->get($line['account_code']);
            }

            if (! $account || ! $account->allow_posting || ! $account->is_active) {
                $message = __('Invalid account on journal line.');
                if ($wantsJson) {
                    return response()->json(['message' => $message], 422);
                }

                return back()->withInput()->withErrors(['lines' => $message]);
            }

            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);
            if ($debit > 0 && $credit > 0) {
                $message = __('A line cannot be both debit and credit.');
                if ($wantsJson) {
                    return response()->json(['message' => $message], 422);
                }

                return back()->withInput()->withErrors(['lines' => $message]);
            }
            if ($debit <= 0 && $credit <= 0) {
                continue;
            }

            $currencyId = $account->currency_id ?? $base->id;

            $lines[] = [
                'account_id' => $account->id,
                'currency_id' => $currencyId,
                'exchange_rate' => 1,
                'description' => $line['description'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        if (count($lines) < 2) {
            $message = __('A journal entry requires at least two lines.');
            if ($wantsJson) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withInput()->withErrors(['lines' => $message]);
        }

        try {
            $entry = $service->create([
                'entry_date' => $data['entry_date'],
                'description' => $data['description'],
                'currency_id' => $base->id,
                'exchange_rate' => 1,
            ], $lines, $request->user()->id);
            $entry = $service->post($entry, $request->user()->id);
        } catch (DomainException $e) {
            if ($wantsJson) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->withErrors(['lines' => $e->getMessage()]);
        }

        $entry->load(['lines.account']);

        if ($wantsJson) {
            return response()->json([
                'message' => __('Journal posted.'),
                'entry' => [
                    'id' => $entry->id,
                    'ref' => $entry->entry_no,
                    'date' => $entry->entry_date?->toDateString(),
                    'desc' => $entry->description,
                    'total' => (float) $entry->total_debit,
                    'lines' => $entry->lines->map(fn ($line) => [
                        'account_name' => $line->account?->localized_name,
                        'debit' => (float) $line->debit,
                        'credit' => (float) $line->credit,
                    ])->all(),
                ],
                'accounts' => $this->accountingAccountsPayload($balances),
                'next_ref' => app(\App\Services\DocumentNumberService::class)->peek(\App\Enums\DocumentSequenceKey::Journal),
            ]);
        }

        return redirect()->route('admin.journals.index')->with('status', __('Journal posted.'));
    }

    public function journalsShow(JournalEntry $journal): View
    {
        $journal->load(['lines.account', 'lines.currency', 'attachments']);

        return view('admin.journals.show', compact('journal'));
    }

    public function journalAttachmentDownload(JournalEntry $journal, Attachment $attachment): StreamedResponse
    {
        abort_unless(
            $attachment->attachable_type === 'journal_entry' && (int) $attachment->attachable_id === (int) $journal->id,
            404
        );

        $disk = $attachment->disk ?: 'local';
        abort_unless(Storage::disk($disk)->exists($attachment->file_path), 404);

        return Storage::disk($disk)->download($attachment->file_path, $attachment->original_name);
    }

    public function journalPost(Request $request, JournalEntry $journal, JournalEntryService $service): RedirectResponse
    {
        try {
            $service->post($journal, $request->user()->id);
        } catch (DomainException $e) {
            return back()->withErrors(['journal' => $e->getMessage()]);
        }

        return back()->with('status', __('Journal posted.'));
    }

    /**
     * @param  array<int, UploadedFile|null>  $files
     */
    private function storeJournalAttachments(JournalEntry $entry, array $files, ?int $uploadedBy = null): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $disk = 'local';
            $directory = 'journals/'.$entry->id;
            $storedName = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs($directory, $storedName, $disk);

            $entry->attachments()->create([
                'file_name' => $storedName,
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'disk' => $disk,
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'description' => 'Journal entry attachment',
                'uploaded_by' => $uploadedBy,
            ]);
        }
    }

    public function servicesIndex(Request $request): View
    {
        return $this->indexView(__('Service catalog'), Service::orderBy('sort_order')->paginate($this->perPage($request))->withQueryString(), [
            'service_code' => __('Code'), 'name' => __('English name'), 'name_ar' => __('Arabic name'),
            'is_public' => __('Public'), 'is_featured' => __('Featured'), 'sort_order' => __('Order'),
        ], 'admin.services', false);
    }

    public function servicesEdit(Service $service): View
    {
        $service->load(['features', 'benefits', 'processSteps', 'technologies', 'faqs']);

        return view('admin.services.edit', [
            'service' => $service,
            'categories' => ServiceCategory::query()->orderBy('sort_order')->get()->mapWithKeys(
                fn ($c) => [$c->id => $c->localized('name') ?: $c->name]
            ),
            'currencies' => Currency::query()->orderBy('code')->pluck('code', 'id'),
            'serviceTypes' => $this->enumOptions(ServiceType::cases()),
            'billingTypes' => $this->enumOptions(BillingType::cases()),
            'pricingTypes' => $this->enumOptions(PricingType::cases()),
            'technologies' => Technology::query()->active()->orderBy('sort_order')->get()->mapWithKeys(
                fn ($t) => [$t->id => $t->localized('name') ?: $t->name]
            ),
            'selectedTechnologyIds' => $service->technologies->pluck('id')->all(),
            'faqs' => Faq::query()->active()->orderBy('sort_order')->get()->mapWithKeys(
                fn ($f) => [$f->id => $f->localized('question') ?: $f->question]
            ),
            'selectedFaqIds' => $service->faqs->pluck('id')->all(),
        ]);
    }

    public function servicesUpdate(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:service_categories,id'],
            'service_code' => ['required', 'string', 'max:50', Rule::unique('services', 'service_code')->ignore($service->id)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('services', 'slug')->ignore($service->id)],
            'icon' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'short_description_ar' => ['nullable', 'string'],
            'overview' => ['nullable', 'string'],
            'overview_ar' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'service_type' => ['nullable', Rule::enum(ServiceType::class)],
            'billing_type' => ['nullable', Rule::enum(BillingType::class)],
            'pricing_type' => ['nullable', Rule::enum(PricingType::class)],
            'currency_id' => ['nullable', 'exists:currencies,id'],
            'starting_price' => ['nullable', 'numeric', 'min:0'],
            'default_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_title_ar' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_description_ar' => ['nullable', 'string'],
            'technology_ids' => ['nullable', 'array'],
            'technology_ids.*' => ['integer', 'exists:technologies,id'],
            'faq_ids' => ['nullable', 'array'],
            'faq_ids.*' => ['integer', 'exists:faqs,id'],
            'features' => ['nullable', 'array'],
            'features.*.title' => ['nullable', 'string', 'max:255'],
            'features.*.title_ar' => ['nullable', 'string', 'max:255'],
            'features.*.description' => ['nullable', 'string'],
            'features.*.description_ar' => ['nullable', 'string'],
            'benefits' => ['nullable', 'array'],
            'benefits.*.title' => ['nullable', 'string', 'max:255'],
            'benefits.*.title_ar' => ['nullable', 'string', 'max:255'],
            'benefits.*.description' => ['nullable', 'string'],
            'benefits.*.description_ar' => ['nullable', 'string'],
            'steps' => ['nullable', 'array'],
            'steps.*.title' => ['nullable', 'string', 'max:255'],
            'steps.*.title_ar' => ['nullable', 'string', 'max:255'],
            'steps.*.description' => ['nullable', 'string'],
            'steps.*.description_ar' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $service, $data) {
            $service->update([
                ...Arr::except($data, ['technology_ids', 'faq_ids', 'features', 'benefits', 'steps']),
                'is_active' => $request->boolean('is_active'),
                'is_public' => $request->boolean('is_public'),
                'is_featured' => $request->boolean('is_featured'),
                'updated_by' => $request->user()->id,
            ]);

            $techSync = [];
            foreach (array_values($data['technology_ids'] ?? []) as $index => $techId) {
                $techSync[$techId] = ['sort_order' => $index + 1];
            }
            $service->technologies()->sync($techSync);

            $faqSync = [];
            foreach (array_values($data['faq_ids'] ?? []) as $index => $faqId) {
                $faqSync[$faqId] = ['sort_order' => $index + 1];
            }
            $service->faqs()->sync($faqSync);

            $service->features()->delete();
            foreach (array_values($data['features'] ?? []) as $index => $row) {
                if (! filled($row['title'] ?? null) && ! filled($row['title_ar'] ?? null)) {
                    continue;
                }
                ServiceFeature::query()->create([
                    'service_id' => $service->id,
                    'title' => $row['title'] ?: ($row['title_ar'] ?? 'Feature'),
                    'title_ar' => $row['title_ar'] ?? null,
                    'description' => $row['description'] ?? null,
                    'description_ar' => $row['description_ar'] ?? null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            $service->benefits()->delete();
            foreach (array_values($data['benefits'] ?? []) as $index => $row) {
                if (! filled($row['title'] ?? null) && ! filled($row['title_ar'] ?? null)) {
                    continue;
                }
                ServiceBenefit::query()->create([
                    'service_id' => $service->id,
                    'title' => $row['title'] ?: ($row['title_ar'] ?? 'Benefit'),
                    'title_ar' => $row['title_ar'] ?? null,
                    'description' => $row['description'] ?? null,
                    'description_ar' => $row['description_ar'] ?? null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            $service->processSteps()->delete();
            foreach (array_values($data['steps'] ?? []) as $index => $row) {
                if (! filled($row['title'] ?? null) && ! filled($row['title_ar'] ?? null)) {
                    continue;
                }
                ServiceProcessStep::query()->create([
                    'service_id' => $service->id,
                    'step_number' => $index + 1,
                    'title' => $row['title'] ?: ($row['title_ar'] ?? 'Step'),
                    'title_ar' => $row['title_ar'] ?? null,
                    'description' => $row['description'] ?? null,
                    'description_ar' => $row['description_ar'] ?? null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        });

        app(PublicCatalogService::class)->forgetCatalogCache();

        return redirect()
            ->route('admin.services.edit', $service)
            ->with('status', __('Service updated.'));
    }

    public function portfolioIndex(Request $request): View
    {
        return $this->indexView(__('Portfolio'), PortfolioProject::orderBy('sort_order')->paginate($this->perPage($request))->withQueryString(), [
            'title' => __('English title'), 'title_ar' => __('Arabic title'), 'client_name' => __('Client'),
            'is_active' => __('Active'), 'is_featured' => __('Featured'),
        ], 'admin.portfolio-projects', false);
    }

    public function portfolioCreate(): View
    {
        return $this->formView(__('New portfolio project'), route('admin.portfolio-projects.store'), $this->portfolioFields());
    }

    public function portfolioStore(Request $request): RedirectResponse
    {
        $data = $this->validatePortfolio($request);
        $project = PortfolioProject::create([...$data, 'slug' => Str::slug($data['title']), 'is_active' => $request->boolean('is_active'), 'is_featured' => $request->boolean('is_featured')]);
        return redirect()->route('admin.portfolio-projects.edit', $project)->with('status', __('Portfolio project created.'));
    }

    public function portfolioEdit(PortfolioProject $portfolioProject): View
    {
        return $this->formView(__('Edit portfolio project'), route('admin.portfolio-projects.update', $portfolioProject), $this->portfolioFields(), $portfolioProject, 'PUT');
    }

    public function portfolioUpdate(Request $request, PortfolioProject $portfolioProject): RedirectResponse
    {
        $data = $this->validatePortfolio($request);
        $portfolioProject->update([...$data, 'slug' => Str::slug($data['title']), 'is_active' => $request->boolean('is_active'), 'is_featured' => $request->boolean('is_featured')]);
        return back()->with('status', __('Portfolio project updated.'));
    }

    public function settingsEdit(): View
    {
        return view('admin.settings.edit', ['settings' => Setting::where('is_public', true)->orderBy('group')->orderBy('key')->get()]);
    }

    public function settingsUpdate(Request $request): RedirectResponse
    {
        $values = $request->validate(['settings' => ['required', 'array'], 'settings.*' => ['nullable', 'string']])['settings'];
        foreach ($values as $id => $value) {
            Setting::whereKey($id)->where('is_public', true)->update(['value' => $value, 'updated_by' => $request->user()->id]);
        }
        app(CompanySettingsService::class)->forgetCache();

        return back()->with('status', __('Settings updated.'));
    }

    private function indexView(string $title, $records, array $columns, string $routeBase, bool $show = true): View
    {
        return view('admin.shared.index', compact('title', 'records', 'columns', 'routeBase', 'show'));
    }

    private function formView(string $title, string $action, array $fields, $record = null, string $method = 'POST'): View
    {
        return view('admin.shared.form', compact('title', 'action', 'fields', 'record', 'method'));
    }

    private function documentFormView(string $title, string $action, string $view, $record = null, string $method = 'POST'): View
    {
        $initialItems = old('items');
        if (! is_array($initialItems) || $initialItems === []) {
            $initialItems = $record
                ? $record->items->map(fn ($item) => [
                    'description' => $item->description,
                    'unit_price' => (float) $item->unit_price,
                    'discount_percentage' => (float) ($item->discount_percentage ?? 0),
                    'tax_percentage' => (float) ($item->tax_percentage ?? 0),
                ])->all()
                : [['description' => '', 'unit_price' => 0, 'discount_percentage' => 0, 'tax_percentage' => 0]];
        }

        $payload = [
            'title' => $title,
            'action' => $action,
            'method' => $method,
            'record' => $record,
            'customers' => Customer::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::query()->where('is_active', true)->orderBy('code')->pluck('code', 'id'),
            'initialItems' => $initialItems,
        ];

        if (str_contains($view, 'invoices')) {
            $payload['revenueAccounts'] = Account::query()
                ->active()
                ->postable()
                ->ofType(AccountType::Revenue)
                ->orderBy('account_code')
                ->get()
                ->mapWithKeys(fn (Account $a) => [$a->id => $a->account_code.' — '.$a->localized_name]);
        }

        return view($view, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDocumentPayload(Request $request, string $kind, bool $updating = false): array
    {
        $dateField = $kind === 'quotation' ? 'quotation_date' : 'invoice_date';
        $untilField = $kind === 'quotation' ? 'valid_until' : 'due_date';
        $termsField = $kind === 'quotation' ? 'terms_and_conditions' : 'payment_terms';

        $rules = [
            'customer_id' => [$updating ? 'sometimes' : 'required', 'exists:customers,id'],
            $dateField => ['required', 'date'],
            $untilField => ['nullable', 'date', "after_or_equal:{$dateField}"],
            'currency_id' => [$updating ? 'sometimes' : 'required', 'exists:currencies,id'],
            'notes' => ['nullable', 'string'],
            $termsField => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'other_amount' => ['nullable', 'numeric'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['nullable', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];

        if ($kind === 'invoice') {
            $rules['account_id'] = [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(function ($query) {
                    $query->where('account_type', AccountType::Revenue->value)
                        ->where('allow_posting', true)
                        ->where('is_active', true)
                        ->whereNull('deleted_at');
                }),
            ];
        }

        $data = $request->validate($rules);
        $data['discount_amount'] = (float) ($data['discount_amount'] ?? 0);
        $data['other_amount'] = (float) ($data['other_amount'] ?? 0);

        return $data;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizedDocumentItems(array $items): array
    {
        return collect($items)->values()->map(function (array $item, int $index) {
            return [
                'description' => $item['description'],
                'quantity' => 1,
                'unit_price' => $item['unit_price'],
                'unit' => 'item',
                'discount_percentage' => $item['discount_percentage'] ?? 0,
                'tax_percentage' => $item['tax_percentage'] ?? 0,
                'sort_order' => $index + 1,
            ];
        })->all();
    }

    private function selectField(string $label, $options): array
    {
        return ['label' => $label, 'type' => 'select', 'options' => $options];
    }

    private function enumOptions(array $cases): array
    {
        return collect($cases)->mapWithKeys(function ($case) {
            $key = 'enums.'.$case->value;
            $label = __($key);

            return [$case->value => $label === $key ? Str::headline($case->value) : $label];
        })->all();
    }

    private function customerFields(): array
    {
        return [
            'customer_type' => $this->selectField(__('Type'), $this->enumOptions(CustomerType::cases())),
            'name' => ['label' => __('Name')], 'company_name' => ['label' => __('Company name')],
            'email' => ['label' => __('Email'), 'type' => 'email'], 'phone' => ['label' => __('Phone')],
            'city' => ['label' => __('City')], 'status' => $this->selectField(__('Status'), $this->enumOptions(CustomerStatus::cases())),
            'notes' => ['label' => __('Notes'), 'type' => 'textarea'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAccount(Request $request): array
    {
        $data = $request->validate([
            'account_code' => ['required', 'string', 'max:50', 'unique:accounts,account_code'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_name_ar' => ['nullable', 'string', 'max:255'],
            'account_type' => ['required', Rule::enum(AccountType::class)],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'currency_id' => ['nullable', 'exists:currencies,id'],
            'is_cash_account' => ['nullable', 'boolean'],
            'is_bank_account' => ['nullable', 'boolean'],
        ]);

        $data['is_cash_account'] = $request->boolean('is_cash_account');
        $data['is_bank_account'] = $request->boolean('is_bank_account');
        $data['parent_id'] = $data['parent_id'] ?? null;
        $data['currency_id'] = $data['currency_id'] ?? null;
        $data['account_name_ar'] = $data['account_name_ar'] ?? null;

        return $data;
    }

    private function validateCustomer(Request $request): array
    {
        return $request->validate([
            'customer_type' => ['required', Rule::enum(CustomerType::class)], 'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'], 'city' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::enum(CustomerStatus::class)], 'notes' => ['nullable', 'string'],
        ]);
    }

    private function requestFields(): array
    {
        return [
            'customer_id' => $this->selectField(__('Customer'), Customer::orderBy('name')->pluck('name', 'id')),
            'request_type' => $this->selectField(__('Type'), $this->enumOptions(RequestType::cases())),
            'subject' => ['label' => __('Subject')], 'description' => ['label' => __('Description'), 'type' => 'textarea'],
            'priority' => $this->selectField(__('Priority'), $this->enumOptions(RequestPriority::cases())),
            'estimated_budget' => ['label' => __('Estimated budget'), 'type' => 'number', 'step' => '0.01'],
        ];
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'], 'request_type' => ['required', Rule::enum(RequestType::class)],
            'subject' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::enum(RequestPriority::class)], 'estimated_budget' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function quotationFields(): array
    {
        return [
            'customer_id' => $this->selectField(__('Customer'), Customer::orderBy('name')->pluck('name', 'id')),
            'quotation_date' => ['label' => __('Date'), 'type' => 'date', 'value' => now()->toDateString()],
            'valid_until' => ['label' => __('Valid until'), 'type' => 'date', 'value' => now()->addDays(30)->toDateString()],
            'currency_id' => $this->selectField(__('Currency'), Currency::pluck('code', 'id')),
            'notes' => ['label' => __('Notes'), 'type' => 'textarea'],
        ];
    }

    private function invoiceFields(): array
    {
        return [
            'customer_id' => $this->selectField(__('Customer'), Customer::orderBy('name')->pluck('name', 'id')),
            'invoice_date' => ['label' => __('Date'), 'type' => 'date', 'value' => now()->toDateString()],
            'due_date' => ['label' => __('Due date'), 'type' => 'date', 'value' => now()->addDays(30)->toDateString()],
            'currency_id' => $this->selectField(__('Currency'), Currency::pluck('code', 'id')),
            'notes' => ['label' => __('Notes'), 'type' => 'textarea'],
        ];
    }

    private function portfolioFields(): array
    {
        return [
            'title' => ['label' => __('English title')], 'title_ar' => ['label' => __('Arabic title')],
            'short_description' => ['label' => __('English summary'), 'type' => 'textarea'],
            'short_description_ar' => ['label' => __('Arabic summary'), 'type' => 'textarea'],
            'client_name' => ['label' => __('Client')], 'completion_date' => ['label' => __('Completion date'), 'type' => 'date'],
            'sort_order' => ['label' => __('Sort order'), 'type' => 'number', 'value' => 0],
            'is_active' => ['label' => __('Active'), 'type' => 'checkbox'], 'is_featured' => ['label' => __('Featured'), 'type' => 'checkbox'],
        ];
    }

    private function validatePortfolio(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'], 'title_ar' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'], 'short_description_ar' => ['nullable', 'string'],
            'client_name' => ['nullable', 'string', 'max:255'], 'completion_date' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }
}
