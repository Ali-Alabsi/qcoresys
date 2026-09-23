<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\AdminNavigation;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_accountant_sees_finance_links_not_sales_pipeline(): void
    {
        $accountant = User::factory()->create([
            'email' => 'nav.accountant@example.test',
            'username' => 'nav.accountant',
            'is_active' => true,
        ]);
        $accountant->assignRole('ACCOUNTANT');

        $response = $this->actingAs($accountant)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee(__('Finance'), false)
            ->assertSee(__('Invoices'), false)
            ->assertSee(__('Payments'), false)
            ->assertSee(__('Accounts'), false)
            ->assertSee(__('Financial operations'), false)
            ->assertDontSee(route('admin.quotations.index'), false)
            ->assertDontSee(route('admin.users.index'), false)
            ->assertDontSee(route('admin.portfolio-projects.index'), false)
            ->assertDontSee(route('admin.services.index'), false);

        $this->assertFalse($accountant->hasPermission('quotations.view'));
        $this->assertFalse($accountant->hasPermission('journals.reverse'));
        $this->assertTrue($accountant->hasPermission('journals.view'));
        $this->assertTrue($accountant->hasPermission('dashboard.view'));
    }

    public function test_sales_sees_crm_links_not_journals_or_accounts(): void
    {
        $sales = User::factory()->create([
            'email' => 'nav.sales@example.test',
            'username' => 'nav.sales',
            'is_active' => true,
        ]);
        $sales->assignRole('SALES');

        $response = $this->actingAs($sales)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee(__('Sales'), false)
            ->assertSee(__('Customers'), false)
            ->assertSee(__('Requests'), false)
            ->assertSee(__('Quotations'), false)
            ->assertDontSee(route('admin.journals.index'), false)
            ->assertDontSee(route('admin.accounts.index'), false)
            ->assertDontSee(route('admin.users.index'), false)
            ->assertDontSee(route('admin.settings.edit'), false);

        $this->assertFalse($sales->hasPermission('journals.view'));
        $this->assertFalse($sales->hasPermission('accounts.view'));
        $this->assertFalse($sales->hasPermission('invoices.view'));
        $this->assertTrue($sales->hasPermission('quotations.view'));
    }

    public function test_accountant_login_redirects_to_allowed_home(): void
    {
        $accountant = User::factory()->create([
            'email' => 'login.accountant@example.test',
            'username' => 'login.accountant',
            'is_active' => true,
            'password' => 'password123',
        ]);
        $accountant->assignRole('ACCOUNTANT');

        $home = AdminNavigation::homeRoute($accountant);
        $this->assertSame('admin.dashboard', $home);

        $this->post('/qcs/admin/login', [
            'email' => 'login.accountant@example.test',
            'password' => 'password123',
        ])->assertRedirect(route($home));

        $this->get(route($home))->assertOk();
        $this->get(route('admin.quotations.index'))->assertForbidden();
        $this->get(route('admin.journals.index'))->assertOk();
    }

    public function test_sales_manager_still_sees_invoice_and_payment_views(): void
    {
        $manager = User::query()->where('email', 'sales.manager@qcoresys.com')->firstOrFail();

        $this->assertTrue($manager->hasPermission('invoices.view'));
        $this->assertTrue($manager->hasPermission('payments.view'));
        $this->assertFalse($manager->hasPermission('journals.view'));

        $this->actingAs($manager)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.invoices.index'), false)
            ->assertSee(route('admin.payments.index'), false)
            ->assertDontSee(route('admin.journals.index'), false)
            ->assertDontSee(route('admin.accounts.index'), false);
    }

    public function test_dashboard_permission_is_seeded_for_system_roles(): void
    {
        $this->assertTrue(
            Role::query()->where('code', 'ACCOUNTANT')->firstOrFail()
                ->permissions()->where('code', 'dashboard.view')->exists()
        );
    }
}
