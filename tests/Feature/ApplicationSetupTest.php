<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_command_seeds_prototype_data_and_admin(): void
    {
        $this->artisan('qcoresys:install')->assertSuccessful();

        $admin = User::query()->where('email', 'admin@qcoresys.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('SUPER_ADMIN'));
        $this->assertTrue($admin->hasPermission('settings.update'));
        $this->assertTrue(Service::query()->where('slug', 'core-banking-solutions')->exists());
        $this->assertTrue(
            Customer::query()->where('email', 'portal@qcoresys.com')->whereNotNull('portal_user_id')->exists()
        );

        $this->post('/admin/login', [
            'email' => 'admin@qcoresys.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        auth()->logout();
        session()->flush();

        $this->post('/portal/login', [
            'email' => 'portal@qcoresys.com',
            'password' => 'password',
        ])->assertRedirect(route('portal.dashboard'));
    }

    public function test_reinstall_does_not_overwrite_existing_admin_password(): void
    {
        $this->artisan('qcoresys:install')->assertSuccessful();

        $admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
        $admin->password = 'changed-password';
        $admin->save();

        $this->artisan('qcoresys:install')->assertSuccessful();

        $this->post('/admin/login', [
            'email' => 'admin@qcoresys.com',
            'password' => 'changed-password',
        ])->assertRedirect(route('admin.dashboard'));
    }
}
