<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\InvoiceService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWorkstationTest extends TestCase
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

    public function test_payment_create_opens_as_popup_on_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.payments.create'))
            ->assertRedirect(route('admin.payments.index', ['new' => 1]));

        $this->actingAs($this->admin)
            ->get(route('admin.payments.index', ['new' => 1]))
            ->assertOk()
            ->assertSee('id="btnOpenPayment"', false)
            ->assertSee('id="paymentModal"', false)
            ->assertSee('is-open', false)
            ->assertSee(__('New payment'), false)
            ->assertSee(__('Save and post'), false);
    }

    public function test_full_payment_is_posted_and_clears_invoice(): void
    {
        $invoice = $this->createPostedInvoice(1500);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->id,
                'account_id' => $this->bankAccount->id,
                'payment_method' => PaymentMethod::BankTransfer->value,
                'payment_date' => now()->toDateString(),
                'amount' => 1500,
                'reference_no' => 'TRX-FULL',
            ])
            ->assertRedirect(route('admin.payments.index'));

        $payment = Payment::query()->latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertSame(PaymentStatus::Posted, $payment->status);
        $this->assertNotNull($payment->journal_entry_id);
        $this->assertEquals(1500, (float) $payment->amount);
        $this->assertSame($invoice->id, $payment->invoice_id);

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertEquals(0, (float) $invoice->remaining_amount);
    }

    public function test_partial_payment_is_accepted_and_updates_invoice(): void
    {
        $invoice = $this->createPostedInvoice(200);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->id,
                'account_id' => $this->bankAccount->id,
                'payment_method' => PaymentMethod::Cash->value,
                'payment_date' => now()->toDateString(),
                'amount' => 100,
            ])
            ->assertRedirect(route('admin.payments.index'));

        $payment = Payment::query()->latest('id')->first();
        $this->assertSame(PaymentStatus::Posted, $payment->status);
        $this->assertNotNull($payment->journal_entry_id);

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->status);
        $this->assertEquals(100, (float) $invoice->remaining_amount);
        $this->assertEquals(100, (float) $invoice->paid_amount);
    }

    public function test_payment_exceeding_invoice_remaining_is_rejected(): void
    {
        $invoice = $this->createPostedInvoice(200);

        $this->actingAs($this->admin)
            ->from(route('admin.payments.index', ['new' => 1]))
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->id,
                'account_id' => $this->bankAccount->id,
                'payment_method' => PaymentMethod::BankTransfer->value,
                'payment_date' => now()->toDateString(),
                'amount' => 300,
            ])
            ->assertRedirect(route('admin.payments.index', ['new' => 1]))
            ->assertSessionHasErrors(['amount']);

        $this->assertSame(0, Payment::query()->count());
        $invoice->refresh();
        $this->assertEquals(200, (float) $invoice->remaining_amount);
        $this->assertSame(InvoiceStatus::Posted, $invoice->status);
    }

    public function test_non_cash_bank_deposit_account_is_rejected(): void
    {
        $invoice = $this->createPostedInvoice(200);
        $nonDepositAccount = Account::query()
            ->postable()
            ->where('is_cash_account', false)
            ->where('is_bank_account', false)
            ->firstOrFail();

        $this->actingAs($this->admin)
            ->from(route('admin.payments.index', ['new' => 1]))
            ->post(route('admin.payments.store'), [
                'invoice_id' => $invoice->id,
                'account_id' => $nonDepositAccount->id,
                'payment_method' => PaymentMethod::BankTransfer->value,
                'payment_date' => now()->toDateString(),
                'amount' => 200,
            ])
            ->assertRedirect(route('admin.payments.index', ['new' => 1]))
            ->assertSessionHasErrors(['account_id']);

        $this->assertSame(0, Payment::query()->count());
    }

    public function test_invalid_payment_store_reopens_popup_with_errors(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.payments.index', ['new' => 1]))
            ->post(route('admin.payments.store'), [
                'invoice_id' => '',
                'account_id' => '',
                'payment_method' => '',
                'payment_date' => '',
                'amount' => '',
            ])
            ->assertRedirect(route('admin.payments.index', ['new' => 1]))
            ->assertSessionHasErrors(['invoice_id', 'account_id', 'payment_method', 'payment_date', 'amount']);

        $this->actingAs($this->admin)
            ->get(route('admin.payments.index', ['new' => 1]))
            ->assertOk()
            ->assertSee('id="paymentModal"', false)
            ->assertSee('is-open', false);
    }

    private function createPostedInvoice(float $amount)
    {
        $customer = app(CustomerService::class)->create([
            'customer_type' => CustomerType::Company,
            'name' => 'Payment Popup Client '.uniqid(),
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
                'description' => 'Service delivery',
                'quantity' => 1,
                'unit_price' => $amount,
                'tax_percentage' => 0,
            ],
        ], $this->admin->id);

        $invoice = $invoiceService->approve($invoice, $this->admin->id);

        return $invoiceService->post($invoice, $this->admin->id);
    }
}
