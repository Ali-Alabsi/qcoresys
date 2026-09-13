<?php

namespace Tests\Feature;

use App\Enums\PricingType;
use App\Enums\RequestStatus;
use App\Models\Customer;
use App\Models\CustomerRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_services_api_returns_only_public_active_services(): void
    {
        $private = Service::factory()->privateCatalog()->create([
            'name' => 'Internal Only Service',
            'slug' => 'internal-only-service',
            'cost_price' => 9999,
        ]);

        $response = $this->getJson('/api/public/services');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $slugs = collect($response->json('data'))->pluck('slug');
        $this->assertTrue($slugs->contains('mobile-application-development'));
        $this->assertFalse($slugs->contains($private->slug));

        $json = $response->getContent();
        $this->assertStringNotContainsString('cost_price', $json);
        $this->assertStringNotContainsString('9999', $json);
    }

    public function test_public_service_detail_hides_internal_fields(): void
    {
        $response = $this->getJson('/api/public/services/mobile-application-development');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'mobile-application-development');

        $json = $response->getContent();
        $this->assertStringNotContainsString('cost_price', $json);
        $this->assertStringNotContainsString('default_price', $json);
        $this->assertStringNotContainsString('tax_rate', $json);
        $this->assertStringNotContainsString('estimated_hours', $json);
    }

    public function test_inactive_or_private_service_detail_returns_404(): void
    {
        Service::factory()->create([
            'slug' => 'hidden-service',
            'is_public' => false,
            'is_active' => true,
        ]);

        $this->getJson('/api/public/services/hidden-service')->assertNotFound();
    }

    public function test_server_side_search_filters_services(): void
    {
        $response = $this->getJson('/api/public/services?search=mobile');

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug');
        $this->assertTrue($slugs->contains('mobile-application-development'));
    }

    public function test_guest_can_submit_service_request_and_receive_request_number(): void
    {
        $service = Service::query()->where('slug', 'mobile-application-development')->firstOrFail();

        $response = $this->postJson('/api/public/service-requests', [
            'full_name' => 'Sara Ahmed',
            'company_name' => 'Acme Co',
            'email' => 'sara@example.com',
            'phone' => '+966500000001',
            'service_id' => $service->id,
            'description' => 'Need a mobile app for field operations.',
            'requirements' => 'iOS and Android',
            'project_type' => 'NEW_BUILD',
            'timeline' => '3_6_MONTHS',
            'preferred_contact_method' => 'EMAIL',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['request_number', 'status']]);

        $requestNumber = $response->json('data.request_number');
        $this->assertNotEmpty($requestNumber);
        $this->assertStringStartsWith('REQ-', $requestNumber);

        $this->assertDatabaseHas('customers', [
            'email' => 'sara@example.com',
            'customer_source' => 'WEBSITE',
        ]);

        $this->assertDatabaseHas('customer_requests', [
            'request_no' => $requestNumber,
            'service_id' => $service->id,
            'status' => RequestStatus::New->value,
            'source' => 'WEBSITE',
        ]);

        $customer = Customer::query()->where('email', 'sara@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertTrue($customer->contacts()->where('email', 'sara@example.com')->exists());
    }

    public function test_honeypot_rejects_spam_service_request(): void
    {
        $service = Service::query()->where('slug', 'mobile-application-development')->firstOrFail();

        $this->postJson('/api/public/service-requests', [
            'full_name' => 'Bot',
            'email' => 'bot@example.com',
            'phone' => '+966500000099',
            'service_id' => $service->id,
            'description' => 'Spam',
            'website' => 'http://spam.test',
        ])->assertStatus(422);
    }

    public function test_services_page_renders_and_uses_rtl_for_arabic(): void
    {
        $response = $this->withSession(['locale' => 'ar'])->get('/services');

        $response->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee(__('Services', [], 'ar'), false);
    }

    public function test_home_page_renders_featured_services_from_database(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('QCoreSys', false);
    }

    public function test_public_portfolio_page_is_disabled(): void
    {
        $this->get('/portfolio')->assertNotFound();
        $this->get('/portfolio/example-project')->assertNotFound();
        $this->getJson('/api/public/portfolio')->assertNotFound();
    }

    public function test_company_endpoint_returns_public_settings_only(): void
    {
        $response = $this->getJson('/api/public/company');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.company_name', 'QCoreSys');

        $data = $response->json('data');
        $this->assertArrayNotHasKey('account_cash', $data);
        $this->assertArrayNotHasKey('account_bank', $data);
    }

    public function test_web_service_request_creates_confirmation(): void
    {
        $service = Service::query()->where('slug', 'it-consulting')->firstOrFail();

        $response = $this->post(route('services.request.store', $service->slug), [
            'full_name' => 'Omar Ali',
            'email' => 'omar@example.com',
            'phone' => '+966511111111',
            'service_id' => $service->id,
            'description' => 'Need consulting for ERP modernization.',
        ]);

        $response->assertRedirect(route('request.confirmation'));
        $this->assertTrue(CustomerRequest::query()->where('service_id', $service->id)->exists());
    }

    public function test_pricing_type_quote_required_does_not_expose_starting_price_falsely(): void
    {
        $category = ServiceCategory::factory()->create();
        $service = Service::factory()->public()->create([
            'category_id' => $category->id,
            'slug' => 'custom-quote-service',
            'pricing_type' => PricingType::QuoteRequired,
            'starting_price' => null,
            'default_price' => 12345,
            'cost_price' => 999,
        ]);

        $response = $this->getJson('/api/public/services/'.$service->slug);
        $response->assertOk()
            ->assertJsonPath('data.pricing.type', 'QUOTE_REQUIRED')
            ->assertJsonPath('data.pricing.starting_price', null);
    }
}
