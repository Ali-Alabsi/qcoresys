<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            DocumentSequenceSeeder::class,
            DepartmentSeeder::class,
            RolePermissionSeeder::class,
            ChartOfAccountsSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            TechnologySeeder::class,
            PublicServiceContentSeeder::class,
            ServiceRequestOptionSeeder::class,
            SettingsSeeder::class,
        ]);

        $password = (string) config('setup.admin.password', 'password');

        $users = [
            ['admin@qcoressys.local', 'admin', 'System Administrator', 'SUPER_ADMIN', $password],
            [
                (string) config('setup.admin.email', 'admin@qcoresys.com'),
                (string) config('setup.admin.username', 'qcoresys.admin'),
                (string) config('setup.admin.name', 'QCoreSys Administrator'),
                'SUPER_ADMIN',
                $password,
            ],
            ['sales.manager@qcoresys.com', 'sales.manager', 'Sales Manager', 'SALES_MANAGER', $password],
            ['account.manager@qcoresys.com', 'account.manager', 'Account Manager', 'ACCOUNT_MANAGER', $password],
        ];

        foreach ($users as [$email, $username, $name, $role, $userPassword]) {
            $user = User::query()->firstOrNew(['email' => $email]);

            $user->fill([
                'username' => $username,
                'name' => $name,
                'is_active' => true,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            if (! $user->exists) {
                $user->password = $userPassword;
            }

            $user->save();
            $user->assignRole($role);
        }

        $this->call(DemoDataSeeder::class);
    }
}
