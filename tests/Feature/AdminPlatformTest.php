<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_staff_can_login_to_admin(): void
    {
        $response = $this->post('/qcs/admin/login', [
            'email' => 'admin@qcoresys.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->get('/qcs/admin')->assertOk()->assertSee('QCoreSys');
    }

    public function test_sales_manager_is_forbidden_from_settings(): void
    {
        $user = User::where('email', 'sales.manager@qcoresys.com')->firstOrFail();

        $this->actingAs($user)->get('/qcs/admin/settings')->assertForbidden();
    }

    public function test_account_manager_cannot_update_settings(): void
    {
        $user = User::where('email', 'account.manager@qcoresys.com')->firstOrFail();

        $this->actingAs($user)->put('/qcs/admin/settings', ['settings' => []])->assertForbidden();
    }

    public function test_portal_user_can_register_and_create_request(): void
    {
        $this->post('/portal/register', [
            'name' => 'Portal Customer',
            'email' => 'portal@example.test',
            'phone' => '+1 555 0100',
            'customer_type' => 'INDIVIDUAL',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('portal.dashboard'));

        $customer = Customer::where('email', 'portal@example.test')->firstOrFail();
        $this->assertNotNull($customer->portal_user_id);
        $this->get('/portal')->assertOk()->assertSee('Portal Customer');

        $this->post('/portal/requests', [
            'request_type' => 'SOFTWARE_DEVELOPMENT',
            'subject' => 'Build customer portal',
            'description' => 'We need a secure self-service portal.',
            'priority' => 'MEDIUM',
            'estimated_budget' => 5000,
        ])->assertRedirect();

        $this->assertTrue(CustomerRequest::where('customer_id', $customer->id)->where('subject', 'Build customer portal')->exists());
    }

    public function test_admin_can_update_full_service_details(): void
    {
        $admin = User::where('email', 'admin@qcoresys.com')->firstOrFail();
        $service = \App\Models\Service::query()->where('slug', 'core-banking-solutions')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.services.update', $service), [
            'category_id' => $service->category_id,
            'service_code' => $service->service_code,
            'slug' => $service->slug,
            'icon' => $service->icon,
            'name' => 'Core Banking Solutions Updated',
            'name_ar' => 'نظم مصرفية محدثة',
            'short_description' => 'Updated short EN',
            'short_description_ar' => 'ملخص عربي محدث',
            'overview' => 'Updated overview for public page',
            'overview_ar' => 'نظرة عامة محدثة للصفحة العامة',
            'description' => $service->description,
            'description_ar' => $service->description_ar,
            'service_type' => $service->service_type?->value,
            'billing_type' => $service->billing_type?->value,
            'pricing_type' => $service->pricing_type?->value,
            'currency_id' => $service->currency_id,
            'starting_price' => $service->starting_price,
            'default_price' => $service->default_price,
            'tax_rate' => $service->tax_rate,
            'sort_order' => 1,
            'is_active' => 1,
            'is_public' => 1,
            'is_featured' => 1,
            'features' => [
                ['title' => 'New Feature', 'title_ar' => 'ميزة جديدة', 'description' => 'Feature body', 'description_ar' => 'نص الميزة'],
            ],
            'benefits' => [
                ['title' => 'New Benefit', 'title_ar' => 'فائدة جديدة', 'description' => 'Benefit body', 'description_ar' => 'نص الفائدة'],
            ],
            'steps' => [
                ['title' => 'Step One', 'title_ar' => 'الخطوة الأولى', 'description' => 'Do work', 'description_ar' => 'تنفيذ العمل'],
            ],
            'technology_ids' => $service->technologies()->pluck('technologies.id')->all(),
            'faq_ids' => [],
        ])->assertRedirect(route('admin.services.edit', $service));

        $service->refresh();
        $this->assertSame('Core Banking Solutions Updated', $service->name);
        $this->assertSame('نظرة عامة محدثة للصفحة العامة', $service->overview_ar);
        $this->assertTrue($service->features()->where('title_ar', 'ميزة جديدة')->exists());
        $this->assertTrue($service->benefits()->where('title_ar', 'فائدة جديدة')->exists());
        $this->assertTrue($service->processSteps()->where('title_ar', 'الخطوة الأولى')->exists());

        $this->get('/services/'.$service->slug)
            ->assertOk()
            ->assertSee('نظرة عامة محدثة للصفحة العامة', false);
    }
}
