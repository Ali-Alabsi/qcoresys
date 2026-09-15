<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerReceivableAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Currency $currency;

    private Account $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
        $this->currency = Currency::query()->where('code', 'USD')->firstOrFail();
        $this->bankAccount = Account::query()->where('account_code', '111102')->firstOrFail();
    }

    public function test_creating_customer_opens_zero_balance_ar_subaccount(): void
    {
        $customer = app(CustomerService::class)->create([
            'customer_type' => CustomerType::Company,
            'name' => 'Ahmad Trading',
            'company_name' => 'Ahmad Trading',
            'email' => 'ahmad@example.test',
            'status' => CustomerStatus::Active,
            'default_currency_id' => $this->currency->id,
        ], $this->admin->id);

        $parent = Account::query()->where('account_code', '1121')->firstOrFail();
        $this->assertTrue($parent->is_control_account);
        $this->assertFalse($parent->allow_posting);

        $account = $customer->fresh('account')->account;
        $this->assertNotNull($account);
        $this->assertSame($parent->id, $account->parent_id);
        $this->assertTrue($account->is_customer_account);
        $this->assertTrue($account->allow_posting);
        $this->assertStringStartsWith('1121', $account->account_code);
        $this->assertSame('AR - Ahmad Trading', $account->account_name);
        $this->assertSame('ذمم Ahmad Trading', $account->account_name_ar);
        $this->assertEqualsWithDelta(0.0, (float) $account->current_balance, 0.01);
    }

    public function test_posting_credit_invoice_debits_customer_account_not_control_ar(): void
    {
        $customer = app(CustomerService::class)->create([
            'customer_type' => CustomerType::Company,
            'name' => 'Credit Client',
            'company_name' => 'Credit Client LLC',
            'status' => CustomerStatus::Active,
            'default_currency_id' => $this->currency->id,
        ], $this->admin->id);

        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->create([
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'currency_id' => $this->currency->id,
        ], [
            [
                'description' => 'Consulting',
                'quantity' => 1,
                'unit_price' => 2500,
                'tax_percentage' => 0,
            ],
        ], $this->admin->id);

        $invoice = $invoiceService->approve($invoice, $this->admin->id);
        $invoice = $invoiceService->post($invoice, $this->admin->id);

        $this->assertSame(InvoiceStatus::Posted, $invoice->status);
        $this->assertEquals(2500, (float) $invoice->remaining_amount);
        $this->assertEquals(0, (float) $invoice->paid_amount);

        $arAccount = $customer->fresh('account')->account;
        $controlArId = Account::query()->where('account_code', '1121')->value('id');

        $this->assertTrue(
            $invoice->journalEntry->lines()
                ->where('account_id', $arAccount->id)
                ->where('debit', 2500)
                ->exists()
        );
        $this->assertFalse(
            $invoice->journalEntry->lines()->where('account_id', $controlArId)->exists()
        );
        $this->assertEqualsWithDelta(2500.0, (float) $arAccount->fresh()->current_balance, 0.01);
    }

    public function test_posting_payment_clears_customer_receivable_balance(): void
    {
        $customer = app(CustomerService::class)->create([
            'customer_type' => CustomerType::Company,
            'name' => 'Pay Later Client',
            'status' => CustomerStatus::Active,
            'default_currency_id' => $this->currency->id,
        ], $this->admin->id);

        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->create([
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'currency_id' => $this->currency->id,
        ], [
            [
                'description' => 'Phase delivery',
                'quantity' => 1,
                'unit_price' => 4000,
                'tax_percentage' => 0,
            ],
        ], $this->admin->id);

        $invoice = $invoiceService->approve($invoice, $this->admin->id);
        $invoice = $invoiceService->post($invoice, $this->admin->id);

        $payment = app(PaymentService::class)->create([
            'invoice_id' => $invoice->id,
            'account_id' => $this->bankAccount->id,
            'payment_method' => PaymentMethod::BankTransfer,
            'payment_date' => now()->toDateString(),
            'amount' => 4000,
            'currency_id' => $this->currency->id,
        ], $this->admin->id);

        $payment = app(PaymentService::class)->post($payment, $this->admin->id);

        $this->assertSame(PaymentStatus::Posted, $payment->status);
        $arAccount = $customer->fresh('account')->account;
        $this->assertTrue(
            $payment->journalEntry->lines()
                ->where('account_id', $arAccount->id)
                ->where('credit', 4000)
                ->exists()
        );
        $this->assertEqualsWithDelta(0.0, (float) $arAccount->fresh()->current_balance, 0.01);
        $this->assertEquals(0, (float) $invoice->fresh()->remaining_amount);
    }

    public function test_credit_invoice_posts_without_requiring_ar_balance(): void
    {
        $customer = app(CustomerService::class)->create([
            'customer_type' => CustomerType::Company,
            'name' => 'Credit Note Client',
            'status' => CustomerStatus::Active,
            'default_currency_id' => $this->currency->id,
        ], $this->admin->id);

        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->create([
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'currency_id' => $this->currency->id,
            'discount_amount' => 400,
        ], [
            [
                'description' => 'Small line with large discount',
                'quantity' => 1,
                'unit_price' => 90,
                'tax_percentage' => 0,
            ],
        ], $this->admin->id);

        $this->assertEqualsWithDelta(-310.0, (float) $invoice->total_amount, 0.01);

        $invoice = $invoiceService->approve($invoice, $this->admin->id);
        $invoice = $invoiceService->post($invoice, $this->admin->id);

        $arAccount = $customer->fresh('account')->account;
        $this->assertSame(InvoiceStatus::Posted, $invoice->status);
        $this->assertTrue(
            $invoice->journalEntry->lines()
                ->where('account_id', $arAccount->id)
                ->where('credit', 310)
                ->exists()
        );
        $this->assertEqualsWithDelta(-310.0, (float) $arAccount->fresh()->current_balance, 0.01);
    }

    public function test_ar_control_parents_are_not_postable_leaves(): void
    {
        foreach (['1121', '1122', '1123'] as $code) {
            $account = Account::query()->where('account_code', $code)->firstOrFail();
            $this->assertTrue($account->is_control_account, $code);
            $this->assertFalse($account->allow_posting, $code);
            $this->assertTrue($account->is_active, $code);
        }
    }

    public function test_customer_show_displays_ar_account_code(): void
    {
        $customer = app(CustomerService::class)->create([
            'customer_type' => CustomerType::Individual,
            'name' => 'Sara Ali',
            'status' => CustomerStatus::Active,
            'default_currency_id' => $this->currency->id,
        ], $this->admin->id);

        $code = $customer->fresh('account')->account->account_code;

        $this->actingAs($this->admin)
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee($code, false)
            ->assertSee('حساب الذمة', false);
    }
}
