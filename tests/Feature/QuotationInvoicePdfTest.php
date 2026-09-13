<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationInvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Customer $customer;

    private Currency $usd;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
        $this->customer = Customer::factory()->create();
        $this->usd = Currency::query()->where('code', 'USD')->firstOrFail();
    }

    public function test_admin_can_create_multi_line_quotation_with_discount(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.quotations.store'), [
            'customer_id' => $this->customer->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(20)->toDateString(),
            'currency_id' => $this->usd->id,
            'discount_amount' => 50,
            'other_amount' => 0,
            'notes' => 'Multi-line quote',
            'terms_and_conditions' => 'Net 15',
            'items' => [
                [
                    'description' => 'Consulting discovery',
                    'unit_price' => 200,
                    'discount_percentage' => 0,
                    'tax_percentage' => 0,
                ],
                [
                    'description' => 'Implementation sprint',
                    'unit_price' => 500,
                    'discount_percentage' => 10,
                    'tax_percentage' => 0,
                ],
            ],
        ]);

        $quotation = Quotation::query()->latest('id')->first();
        $this->assertNotNull($quotation);
        $response->assertRedirect(route('admin.quotations.show', $quotation));

        $this->assertCount(2, $quotation->items);
        // 200 + 450 - 50 document discount = 600
        $this->assertEqualsWithDelta(600.0, (float) $quotation->total_amount, 0.01);
        $this->assertEqualsWithDelta(1.0, (float) $quotation->items->first()->quantity, 0.01);
    }

    public function test_quotation_pdf_download_returns_application_pdf(): void
    {
        $this->actingAs($this->admin)->post(route('admin.quotations.store'), [
            'customer_id' => $this->customer->id,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(10)->toDateString(),
            'currency_id' => $this->usd->id,
            'discount_amount' => 0,
            'other_amount' => 0,
            'items' => [
                [
                    'description' => 'PDF line',
                    'unit_price' => 250,
                    'discount_percentage' => 0,
                    'tax_percentage' => 0,
                ],
            ],
        ])->assertRedirect();

        $quotation = Quotation::query()->latest('id')->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.quotations.pdf', $quotation))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_create_multi_line_invoice_via_service_and_export_pdf(): void
    {
        $invoice = app(\App\Services\InvoiceService::class)->create([
            'customer_id' => $this->customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'currency_id' => $this->usd->id,
            'discount_amount' => 25,
            'other_amount' => 10,
            'payment_terms' => 'Due on receipt',
        ], [
            [
                'description' => 'Support retainer',
                'unit_price' => 300,
                'discount_percentage' => 0,
                'tax_percentage' => 0,
            ],
            [
                'description' => 'Addon hours',
                'unit_price' => 150,
                'discount_percentage' => 0,
                'tax_percentage' => 0,
            ],
        ], $this->admin->id);

        // 300 + 150 - 25 + 10 = 435
        $this->assertEqualsWithDelta(435.0, (float) $invoice->total_amount, 0.01);

        $response = app(\App\Http\Controllers\Web\Admin\PdfController::class)
            ->invoice(request(), $invoice);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }
}
