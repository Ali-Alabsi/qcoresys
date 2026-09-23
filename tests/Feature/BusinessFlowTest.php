<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Enums\ContractType;
use App\Enums\CostType;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\DocumentSequenceKey;
use App\Enums\InvoiceStatus;
use App\Enums\JournalStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProjectType;
use App\Enums\ProposalStatus;
use App\Enums\QuotationStatus;
use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Exceptions\DomainException;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\User;
use App\Policies\InvoicePolicy;
use App\Services\ConsultationService;
use App\Services\ContractService;
use App\Services\CustomerRequestService;
use App\Services\CustomerService;
use App\Services\DocumentNumberService;
use App\Services\ExpenseService;
use App\Services\InvoiceService;
use App\Services\JournalEntryService;
use App\Services\PaymentService;
use App\Services\ProfitabilityService;
use App\Services\ProjectService;
use App\Services\ProposalService;
use App\Services\QuotationService;
use App\Services\ReportingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Currency $currency;

    protected Account $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
        $this->currency = Currency::query()->where('code', 'USD')->firstOrFail();
        $this->bankAccount = Account::query()->where('account_code', '111102')->firstOrFail();
    }

    public function test_full_commercial_and_accounting_flow(): void
    {
        $customerService = app(CustomerService::class);
        $requestService = app(CustomerRequestService::class);
        $consultationService = app(ConsultationService::class);
        $proposalService = app(ProposalService::class);
        $quotationService = app(QuotationService::class);
        $contractService = app(ContractService::class);
        $projectService = app(ProjectService::class);
        $invoiceService = app(InvoiceService::class);
        $paymentService = app(PaymentService::class);
        $expenseService = app(ExpenseService::class);
        $profitability = app(ProfitabilityService::class);
        $reporting = app(ReportingService::class);

        $customer = $customerService->create([
            'customer_type' => CustomerType::Company,
            'name' => 'Acme Tech',
            'company_name' => 'Acme Tech LLC',
            'email' => 'contact@acme.test',
            'status' => CustomerStatus::Active,
            'default_currency_id' => $this->currency->id,
        ], $this->admin->id);

        $this->assertNotNull($customer->customer_code);
        $this->assertStringStartsWith('CUS-', $customer->customer_code);

        $contact = $customerService->createContact($customer, [
            'first_name' => 'Sara',
            'last_name' => 'Ali',
            'email' => 'sara@acme.test',
            'is_primary' => true,
        ], $this->admin->id);

        $this->assertStringStartsWith('CTC-', $contact->contact_code);

        $request = $requestService->create([
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'request_type' => RequestType::SoftwareDevelopment,
            'subject' => 'Build CRM portal',
            'description' => 'Need a consulting CRM',
            'priority' => RequestPriority::High,
            'status' => RequestStatus::New,
            'received_at' => now(),
            'currency_id' => $this->currency->id,
            'consultation_required' => true,
            'quotation_required' => true,
        ], $this->admin->id);

        $this->assertStringStartsWith('REQ-', $request->request_no);

        $consultation = $consultationService->create([
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'request_id' => $request->id,
            'consultant_id' => $this->admin->id,
            'consultation_type' => 'TECHNICAL',
            'title' => 'Requirements workshop',
            'description' => 'Gather requirements',
            'consultation_date' => now()->toDateString(),
            'status' => 'COMPLETED',
            'billable' => true,
            'amount' => 1000,
            'currency_id' => $this->currency->id,
        ], [
            ['user_id' => $this->admin->id, 'role' => 'Consultant', 'attended' => true],
        ], $this->admin->id);

        $this->assertStringStartsWith('CON-', $consultation->consultation_no);

        $proposal = $proposalService->create([
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'request_id' => $request->id,
            'title' => 'CRM Solution Proposal',
            'executive_summary' => 'We propose a custom CRM',
            'status' => ProposalStatus::Draft,
            'prepared_by' => $this->admin->id,
        ], [
            [
                'title' => 'Discovery',
                'description' => 'Requirements and architecture',
                'quantity' => 1,
                'unit' => 'lot',
                'estimated_hours' => 40,
            ],
        ], $this->admin->id);

        $proposal = $proposalService->approve($proposal, $this->admin->id);
        $proposal = $proposalService->send($proposal);
        $this->assertSame(ProposalStatus::Sent, $proposal->status);

        $quotation = $quotationService->create([
            'customer_id' => $customer->id,
            'contact_id' => $contact->id,
            'request_id' => $request->id,
            'proposal_id' => $proposal->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'currency_id' => $this->currency->id,
        ], [
            [
                'description' => 'CRM Development',
                'quantity' => 1,
                'unit' => 'project',
                'unit_price' => 10000,
                'cost_price' => 6000,
                'tax_percentage' => 0,
            ],
        ], $this->admin->id);

        $this->assertEquals(10000, (float) $quotation->total_amount);

        $quotation = $quotationService->approve($quotation, $this->admin->id);
        $quotation = $quotationService->send($quotation);
        $quotation = $quotationService->accept($quotation);
        $this->assertSame(QuotationStatus::Accepted, $quotation->status);

        $contract = $contractService->createFromQuotation($quotation, [
            'contract_type' => ContractType::FixedPrice,
            'title' => 'Acme CRM Contract',
        ], $this->admin->id);
        $contract = $contractService->activate($contract, $this->admin->id);

        $project = $projectService->createFromContract($contract, [
            'project_type' => ProjectType::Software,
            'name' => 'Acme CRM Project',
        ], $this->admin->id);

        $this->assertStringStartsWith('PRJ-', $project->project_no);
        $this->assertGreaterThan(0, $project->projectServices()->count());

        $invoice = $invoiceService->create([
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'contract_id' => $contract->id,
            'quotation_id' => $quotation->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'currency_id' => $this->currency->id,
        ], [
            [
                'description' => 'Phase 1 delivery',
                'quantity' => 1,
                'unit' => 'lot',
                'unit_price' => 10000,
                'tax_percentage' => 0,
            ],
        ], $this->admin->id);

        $invoice = $invoiceService->approve($invoice, $this->admin->id);
        $invoice = $invoiceService->post($invoice, $this->admin->id);

        $this->assertSame(InvoiceStatus::Posted, $invoice->status);
        $this->assertNotNull($invoice->journal_entry_id);
        $this->assertEquals(10000, (float) $invoice->remaining_amount);

        $customerAr = $customer->fresh('account')->account;
        $this->assertNotNull($customerAr);
        $this->assertTrue($customerAr->is_customer_account);
        $this->assertStringStartsWith('1121', $customerAr->account_code);
        $this->assertTrue(
            $invoice->journalEntry->lines()->where('account_id', $customerAr->id)->where('debit', 10000)->exists()
        );
        $this->assertFalse(
            $invoice->journalEntry->lines()->where('account_id', Account::query()->where('account_code', '1121')->value('id'))->exists()
        );
        $this->assertEqualsWithDelta(10000.0, (float) $customerAr->fresh()->current_balance, 0.01);

        $journal = $invoice->journalEntry;
        $this->assertSame(JournalStatus::Posted, $journal->status);
        $this->assertTrue($journal->is_balanced);
        $this->assertEquals((float) $journal->total_debit, (float) $journal->total_credit);

        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'invoices',
            'record_id' => $invoice->id,
            'action' => AuditAction::Post->value,
        ]);

        $payment = $paymentService->create([
            'invoice_id' => $invoice->id,
            'account_id' => $this->bankAccount->id,
            'payment_method' => PaymentMethod::BankTransfer,
            'payment_date' => now()->toDateString(),
            'amount' => 10000,
            'currency_id' => $this->currency->id,
        ], $this->admin->id);

        $payment = $paymentService->post($payment, $this->admin->id);
        $this->assertSame(PaymentStatus::Posted, $payment->status);

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertEquals(0, (float) $invoice->remaining_amount);

        $category = ExpenseCategory::query()->firstOrFail();
        $expenseAccount = Account::query()->where('account_code', '5111')->firstOrFail();

        $expense = $expenseService->create([
            'category_id' => $category->id,
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'account_id' => $expenseAccount->id,
            'expense_date' => now()->toDateString(),
            'description' => 'Cloud hosting',
            'amount' => 500,
            'tax_amount' => 0,
            'currency_id' => $this->currency->id,
            'payment_method' => PaymentMethod::BankTransfer,
        ], $this->admin->id);

        $expense = $expenseService->approve($expense, $this->admin->id);
        $expense = $expenseService->post($expense, $this->admin->id);
        $this->assertNotNull($expense->journal_entry_id);
        $this->assertTrue($project->costs()->where('expense_id', $expense->id)->exists());

        $customerProfit = $profitability->customerProfitability($customer->id);
        $this->assertArrayHasKey('revenue', $customerProfit);
        $this->assertGreaterThan(0, (float) $customerProfit['revenue']);

        $projectProfit = $profitability->projectProfitability($project->id);
        $this->assertArrayHasKey('profit', $projectProfit);

        $pnl = $profitability->companyProfitAndLoss();
        $this->assertArrayHasKey('profit', $pnl);

        $revenueReport = $reporting->revenueReport();
        $this->assertArrayHasKey('total', $revenueReport);
        $this->assertGreaterThan(0, (float) $revenueReport['total']);

        $expenseReport = $reporting->expenseReport();
        $this->assertArrayHasKey('total', $expenseReport);
    }

    public function test_unbalanced_journal_cannot_be_posted(): void
    {
        $service = app(JournalEntryService::class);
        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $revenue = Account::query()->where('account_code', '4112')->firstOrFail();

        $this->expectException(DomainException::class);

        $service->create([
            'entry_date' => now()->toDateString(),
            'description' => 'Unbalanced test',
            'currency_id' => $this->currency->id,
            'exchange_rate' => 1,
            'created_by' => $this->admin->id,
        ], [
            ['account_id' => $cash->id, 'debit' => 100, 'credit' => 0],
            ['account_id' => $revenue->id, 'debit' => 0, 'credit' => 50],
        ], $this->admin->id);
    }

    public function test_journal_reversal_creates_balancing_reverse_entry(): void
    {
        $service = app(JournalEntryService::class);
        $cash = Account::query()->where('account_code', '111101')->firstOrFail();
        $revenue = Account::query()->where('account_code', '4112')->firstOrFail();

        $entry = $service->create([
            'entry_date' => now()->toDateString(),
            'description' => 'Balanced entry',
            'currency_id' => $this->currency->id,
            'exchange_rate' => 1,
        ], [
            ['account_id' => $cash->id, 'debit' => 250, 'credit' => 0],
            ['account_id' => $revenue->id, 'debit' => 0, 'credit' => 250],
        ], $this->admin->id);

        $entry = $service->post($entry, $this->admin->id);
        $reversing = $service->reverse($entry, $this->admin->id);

        $this->assertSame(JournalStatus::Posted, $reversing->status);
        $this->assertEquals((float) $reversing->total_debit, (float) $reversing->total_credit);
        $this->assertTrue($entry->fresh()->is_reversed);
        $this->assertSame(JournalStatus::Posted, $entry->fresh()->status);
        $this->assertEqualsWithDelta(0.0, (float) $cash->fresh()->current_balance, 0.01);
        $this->assertEqualsWithDelta(0.0, (float) $revenue->fresh()->current_balance, 0.01);
    }

    public function test_document_numbers_are_unique_and_sequential(): void
    {
        $numbers = app(DocumentNumberService::class);

        $a = $numbers->next(DocumentSequenceKey::Customer);
        $b = $numbers->next(DocumentSequenceKey::Customer);

        $this->assertNotSame($a, $b);
        $this->assertStringStartsWith('CUS-', $a);
        $this->assertStringStartsWith('CUS-', $b);
        $this->assertSame('CUS-000001', $a);
        $this->assertSame('CUS-000002', $b);
    }

    public function test_posted_invoice_cannot_be_updated_via_policy(): void
    {
        $customer = Customer::factory()->create([
            'default_currency_id' => $this->currency->id,
        ]);

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'currency_id' => $this->currency->id,
            'status' => InvoiceStatus::Posted,
            'total_amount' => 1000,
            'remaining_amount' => 1000,
        ]);

        $policy = new InvoicePolicy;
        $this->assertFalse($policy->update($this->admin, $invoice));
        $this->assertFalse($policy->delete($this->admin, $invoice));
    }

    public function test_customer_soft_delete(): void
    {
        $customer = app(CustomerService::class)->create([
            'customer_type' => CustomerType::Individual,
            'name' => 'Soft Delete Me',
            'status' => CustomerStatus::Lead,
            'default_currency_id' => $this->currency->id,
        ], $this->admin->id);

        $customer->delete();

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
        $this->assertNull(Customer::query()->find($customer->id));
        $this->assertNotNull(Customer::withTrashed()->find($customer->id));
    }

    public function test_role_permissions_for_super_admin(): void
    {
        $this->assertTrue($this->admin->hasRole('SUPER_ADMIN'));
        $this->assertTrue($this->admin->hasPermission('invoices.post'));
        $this->assertTrue($this->admin->hasPermission('journals.reverse'));
    }
}
