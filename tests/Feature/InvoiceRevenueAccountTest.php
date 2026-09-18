<?php

namespace Tests\Feature;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\InvoiceStatus;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\InvoiceService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceRevenueAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
        $this->currency = Currency::query()->where('code', 'USD')->firstOrFail();
    }

    public function test_posting_invoice_credits_selected_revenue_account(): void
    {
        $customer = $this->makeCustomer('Revenue Pick Client');
        $revenue = Account::query()->where('account_code', '4113')->firstOrFail();
        $fallbackProject = Account::query()->where('account_code', '4111')->value('id');
        $fallbackConsulting = Account::query()->where('account_code', '4112')->value('id');

        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->create([
            'customer_id' => $customer->id,
            'account_id' => $revenue->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'currency_id' => $this->currency->id,
        ], [
            [
                'description' => 'Support SLA',
                'quantity' => 1,
                'unit_price' => 1500,
                'tax_percentage' => 0,
            ],
        ], $this->admin->id);

        $invoice = $invoiceService->approve($invoice, $this->admin->id);
        $invoice = $invoiceService->post($invoice, $this->admin->id);

        $this->assertSame(InvoiceStatus::Posted, $invoice->status);
        $this->assertSame($revenue->id, $invoice->account_id);
        $this->assertTrue(
            $invoice->journalEntry->lines()
                ->where('account_id', $revenue->id)
                ->where('credit', 1500)
                ->exists()
        );
        $this->assertFalse(
            $invoice->journalEntry->lines()->where('account_id', $fallbackProject)->exists()
        );
        $this->assertFalse(
            $invoice->journalEntry->lines()->where('account_id', $fallbackConsulting)->exists()
        );
    }

    public function test_invoice_create_form_lists_only_revenue_accounts(): void
    {
        $revenue = Account::query()->where('account_code', '4113')->firstOrFail();
        $bank = Account::query()->where('account_code', '111102')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.invoices.create'))
            ->assertOk()
            ->assertSee(__('Revenue account'), false)
            ->assertSee($revenue->account_code, false)
            ->assertDontSee($bank->account_code, false);

        $html = $response->getContent();
        $this->assertStringContainsString('name="account_id"', $html);
        $this->assertStringNotContainsString('value="'.$bank->id.'"', $html);
    }

    public function test_invoice_store_rejects_non_revenue_account(): void
    {
        $customer = $this->makeCustomer('Bad Account Client');
        $bank = Account::query()->where('account_code', '111102')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), [
                'customer_id' => $customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'currency_id' => $this->currency->id,
                'account_id' => $bank->id,
                'discount_amount' => 0,
                'other_amount' => 0,
                'items' => [
                    [
                        'description' => 'Invalid revenue pick',
                        'unit_price' => 100,
                        'discount_percentage' => 0,
                        'tax_percentage' => 0,
                    ],
                ],
            ])
            ->assertSessionHasErrors('account_id');
    }

    public function test_invoice_store_saves_selected_revenue_account(): void
    {
        $customer = $this->makeCustomer('Store Revenue Client');
        $revenue = Account::query()->where('account_code', '4114')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.invoices.store'), [
                'customer_id' => $customer->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'currency_id' => $this->currency->id,
                'account_id' => $revenue->id,
                'discount_amount' => 0,
                'other_amount' => 0,
                'items' => [
                    [
                        'description' => 'Dev work',
                        'unit_price' => 800,
                        'discount_percentage' => 0,
                        'tax_percentage' => 0,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $customer->id,
            'account_id' => $revenue->id,
        ]);
    }

    private function makeCustomer(string $name): Customer
    {
        return app(CustomerService::class)->create([
            'customer_type' => CustomerType::Company,
            'name' => $name,
            'company_name' => $name,
            'status' => CustomerStatus::Active,
            'default_currency_id' => $this->currency->id,
        ], $this->admin->id);
    }
}
