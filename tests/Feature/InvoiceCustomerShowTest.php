<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\PaymentMethod;
use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\InvoiceService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCustomerShowTest extends TestCase
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

    public function test_invoice_show_lists_related_payments(): void
    {
        [$customer, $invoice] = $this->createPostedInvoiceWithCustomer(500);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->id,
                'account_id' => $this->bankAccount->id,
                'payment_method' => PaymentMethod::BankTransfer->value,
                'payment_date' => now()->toDateString(),
                'amount' => 200,
                'reference_no' => 'SHOW-1',
            ])
            ->assertRedirect(route('admin.payments.index'));

        $paymentNo = \App\Models\Payment::query()->latest('id')->value('payment_no');

        $this->actingAs($this->admin)
            ->get(route('admin.invoices.show', $invoice))
            ->assertOk()
            ->assertSee(__('Payments for this invoice'), false)
            ->assertSee($paymentNo, false)
            ->assertSee('200.00', false)
            ->assertSee($customer->name, false)
            ->assertSee(route('admin.customers.show', $customer), false);
    }

    public function test_customer_show_lists_invoices_and_outstanding_total(): void
    {
        [$customer, $invoice] = $this->createPostedInvoiceWithCustomer(200);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->id,
                'account_id' => $this->bankAccount->id,
                'payment_method' => PaymentMethod::Cash->value,
                'payment_date' => now()->toDateString(),
                'amount' => 50,
            ])
            ->assertRedirect(route('admin.payments.index'));

        $this->actingAs($this->admin)
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee(__('Customer invoices'), false)
            ->assertSee(__('Total outstanding'), false)
            ->assertSee($invoice->invoice_no, false)
            ->assertSee(route('admin.invoices.show', $invoice), false)
            ->assertSee('150.00', false)
            ->assertSee('50.00', false)
            ->assertSee('200.00', false);
    }

    /**
     * @return array{0: \App\Models\Customer, 1: \App\Models\Invoice}
     */
    private function createPostedInvoiceWithCustomer(float $amount): array
    {
        $customer = app(CustomerService::class)->create([
            'customer_type' => CustomerType::Company,
            'name' => 'Show Page Client '.uniqid(),
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
                'description' => 'Delivery',
                'quantity' => 1,
                'unit_price' => $amount,
                'tax_percentage' => 0,
            ],
        ], $this->admin->id);

        $invoice = $invoiceService->approve($invoice, $this->admin->id);
        $invoice = $invoiceService->post($invoice, $this->admin->id);

        return [$customer, $invoice];
    }
}
