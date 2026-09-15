<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Services\ApplicationSetupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApplicationSetupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $ready = storage_path('framework/setup.ready');

        if (is_file($ready)) {
            unlink($ready);
        }
    }

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
        $this->assertTrue(app(ApplicationSetupService::class)->isSetupReady());

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

    public function test_second_install_is_noop_when_setup_ready(): void
    {
        $setup = app(ApplicationSetupService::class);
        $setup->install();

        $this->assertTrue($setup->isSetupReady());

        Artisan::spy();

        $setup->install();

        Artisan::shouldNotHaveReceived('call');
    }

    public function test_pending_migration_runs_migrate_without_reseeding(): void
    {
        $setup = app(ApplicationSetupService::class);
        $setup->install();

        $serviceCount = Service::query()->count();
        $this->assertGreaterThan(0, $serviceCount);

        $migrationPath = database_path('migrations/9999_01_01_000000_create_perf_probe_table.php');

        file_put_contents($migrationPath, <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perf_probe', function (Blueprint $table) {
            $table->id();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perf_probe');
    }
};
PHP);

        try {
            $this->assertFalse($setup->isSetupReady());

            $setup->install();

            $this->assertTrue(Schema::hasTable('perf_probe'));
            $this->assertSame($serviceCount, Service::query()->count());
            $this->assertTrue($setup->isSetupReady());
        } finally {
            if (is_file($migrationPath)) {
                unlink($migrationPath);
            }

            if (Schema::hasTable('perf_probe')) {
                Schema::drop('perf_probe');
            }
        }
    }

    public function test_permission_checks_use_in_request_cache(): void
    {
        $this->artisan('qcoresys:install')->assertSuccessful();

        $admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->assertTrue($admin->hasPermission('settings.update'));
        $this->assertTrue($admin->hasPermission('customers.view'));
        $this->assertTrue($admin->hasRole('SUPER_ADMIN'));

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $roleOrPermissionQueries = collect($queries)->filter(function (array $query) {
            $sql = strtolower($query['query']);

            return str_contains($sql, 'roles')
                || str_contains($sql, 'permissions')
                || str_contains($sql, 'user_roles')
                || str_contains($sql, 'role_permissions');
        });

        // One load of roles.permissions, not one query pair per hasPermission call.
        $this->assertLessThanOrEqual(3, $roleOrPermissionQueries->count());
    }

    public function test_assign_role_clears_authorization_cache(): void
    {
        $role = Role::query()->create([
            'name' => 'Viewer',
            'code' => 'VIEWER',
            'description' => 'Test viewer',
            'is_system_role' => false,
            'is_active' => true,
        ]);

        $permission = Permission::query()->create([
            'name' => 'View customers',
            'code' => 'customers.view',
            'module' => 'customers',
            'action' => 'view',
            'description' => 'View customers',
        ]);

        $role->permissions()->attach($permission->id, ['created_at' => now()]);

        $user = User::factory()->create();

        $this->assertFalse($user->hasPermission('customers.view'));

        $user->assignRole($role);

        $this->assertTrue($user->hasPermission('customers.view'));
        $this->assertTrue($user->hasRole('VIEWER'));
    }
}
