<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::query()->where('email', 'admin@qcoresys.com')->firstOrFail();
    }

    public function test_admin_can_create_user_with_role(): void
    {
        $role = Role::query()->where('code', 'SALES')->firstOrFail();

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Sales Rep',
            'username' => 'sales.rep',
            'email' => 'sales.rep@example.test',
            'phone' => '+967770000000',
            'role_id' => $role->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => 1,
        ]);

        $user = User::query()->where('email', 'sales.rep@example.test')->firstOrFail();

        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue($user->hasRole('SALES'));
        $this->assertTrue($user->hasPermission('customers.view'));
        $this->assertFalse($user->hasPermission('settings.update'));
    }

    public function test_user_without_permission_cannot_manage_users(): void
    {
        $salesManager = User::query()->where('email', 'sales.manager@qcoresys.com')->firstOrFail();

        $this->actingAs($salesManager)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($salesManager)->get(route('admin.users.create'))->assertForbidden();
        $this->actingAs($salesManager)->post(route('admin.users.store'), [
            'name' => 'Blocked',
            'username' => 'blocked.user',
            'email' => 'blocked@example.test',
            'role_id' => Role::query()->where('code', 'EMPLOYEE')->value('id'),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => 1,
        ])->assertForbidden();
    }

    public function test_admin_can_update_role_and_password(): void
    {
        $salesRole = Role::query()->where('code', 'SALES')->firstOrFail();
        $accountantRole = Role::query()->where('code', 'ACCOUNTANT')->firstOrFail();

        $user = User::factory()->create([
            'email' => 'role.switch@example.test',
            'username' => 'role.switch',
            'name' => 'Role Switch',
            'is_active' => true,
        ]);
        $user->assignRole($salesRole);

        $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name' => 'Role Switch',
            'username' => 'role.switch',
            'email' => 'role.switch@example.test',
            'role_id' => $accountantRole->id,
            'password' => 'newpassword99',
            'password_confirmation' => 'newpassword99',
            'is_active' => 1,
        ])->assertRedirect(route('admin.users.index'));

        $user->refresh()->forgetAuthorizationCache();

        $this->assertTrue($user->hasRole('ACCOUNTANT'));
        $this->assertFalse($user->hasRole('SALES'));
        $this->assertTrue($user->hasPermission('journals.view'));

        $this->assertTrue(auth()->guard()->getProvider()->validateCredentials(
            $user,
            ['password' => 'newpassword99']
        ));
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin))
            ->assertRedirect()
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_users_index_opens_create_and_edit_popup(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee(__('Users'), false)
            ->assertSee($this->admin->email, false)
            ->assertSee('id="userModal"', false)
            ->assertSee('id="btnOpenUser"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.users.create'))
            ->assertRedirect(route('admin.users.index', ['new' => 1]));

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['new' => 1]))
            ->assertOk()
            ->assertSee('id="userModal"', false)
            ->assertSee('is-open', false)
            ->assertSee(__('New user'), false);

        $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $this->admin))
            ->assertRedirect(route('admin.users.index', ['edit' => $this->admin->id]));

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['edit' => $this->admin->id]))
            ->assertOk()
            ->assertSee('is-open', false)
            ->assertSee(__('Edit user'), false)
            ->assertSee($this->admin->username, false);

        $this->actingAs($this->admin)
            ->get(route('admin.users.show', $this->admin))
            ->assertOk()
            ->assertSee(__('Permissions'), false)
            ->assertSee('Super Administrator', false);
    }
}
