<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@qcoressys.local'],
            [
                'username' => 'admin',
                'name' => 'System Administrator',
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('SUPER_ADMIN');
    }
}
