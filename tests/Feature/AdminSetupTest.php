<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\PortfolioProject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSetupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
    }

    public function test_admin_can_view_setup_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.setup.index'))
            ->assertOk()
            ->assertSee(__('Setup'), false)
            ->assertSee(__('Initialize accounts'), false)
            ->assertSee(__('Initialize portfolio'), false);
    }

    public function test_sales_manager_is_forbidden_from_setup(): void
    {
        $user = User::query()->where('email', 'sales.manager@qcoresys.com')->firstOrFail();

        $this->actingAs($user)->get(route('admin.setup.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.setup.accounts'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.setup.portfolio'))->assertForbidden();
    }

    public function test_admin_can_initialize_accounts(): void
    {
        $account = Account::query()->where('account_code', '111101')->firstOrFail();
        $account->update(['account_name' => 'Temporary Name']);
        $account->delete();

        $this->assertFalse(Account::query()->where('account_code', '111101')->exists());

        $this->actingAs($this->admin)
            ->from(route('admin.setup.index'))
            ->post(route('admin.setup.accounts'))
            ->assertRedirect(route('admin.setup.index'))
            ->assertSessionHas('status', __('Chart of accounts initialized.'));

        $restored = Account::query()->where('account_code', '111101')->first();
        $this->assertNotNull($restored);
        $this->assertNotSame('Temporary Name', $restored->account_name);
    }

    public function test_admin_can_initialize_portfolio_idempotently(): void
    {
        $this->assertSame(0, PortfolioProject::query()->count());

        $this->actingAs($this->admin)
            ->from(route('admin.setup.index'))
            ->post(route('admin.setup.portfolio'))
            ->assertRedirect(route('admin.setup.index'))
            ->assertSessionHas('status', __('Portfolio initialized.'));

        $this->assertSame(3, PortfolioProject::query()->count());
        $this->assertTrue(PortfolioProject::query()->where('slug', 'core-banking-rollout')->exists());

        $this->actingAs($this->admin)
            ->from(route('admin.setup.index'))
            ->post(route('admin.setup.portfolio'))
            ->assertRedirect(route('admin.setup.index'));

        $this->assertSame(3, PortfolioProject::query()->count());
    }
}
