<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()
            ->where('email', config('setup.admin.email'))
            ->first()
            ?? User::query()->where('email', 'admin@qcoressys.local')->first();

        $usdId = Currency::query()->where('code', 'USD')->value('id');

        $portal = $this->upsertUser([
            'email' => (string) config('setup.portal.email', 'portal@qcoresys.com'),
            'username' => (string) config('setup.portal.username', 'portal.demo'),
            'name' => (string) config('setup.portal.name', 'Portal Demo Customer'),
            'password' => (string) config('setup.portal.password', 'password'),
        ]);

        Customer::query()->updateOrCreate(
            ['email' => $portal->email],
            [
                'customer_code' => 'CUS-DEMO-PORTAL',
                'portal_user_id' => $portal->id,
                'customer_type' => CustomerType::Company,
                'name' => $portal->name,
                'company_name' => (string) config('setup.portal.company', 'Demo Client Co.'),
                'phone' => '+967770000001',
                'country' => 'SA',
                'city' => 'Riyadh',
                'status' => CustomerStatus::Active,
                'customer_source' => 'PORTAL',
                'default_currency_id' => $usdId,
                'assigned_to' => $admin?->id,
                'created_by' => $admin?->id,
            ]
        );

        Customer::query()->updateOrCreate(
            ['email' => 'demo.company@qcoresys.com'],
            [
                'customer_code' => 'CUS-DEMO-COMPANY',
                'customer_type' => CustomerType::Company,
                'name' => 'Al Noor Trading',
                'company_name' => 'Al Noor Trading',
                'phone' => '+967770000002',
                'country' => 'SA',
                'city' => 'Jeddah',
                'industry' => 'Finance',
                'status' => CustomerStatus::Active,
                'customer_source' => 'DEMO',
                'default_currency_id' => $usdId,
                'assigned_to' => $admin?->id,
                'created_by' => $admin?->id,
                'notes' => 'Prototype demo customer for the admin CRM.',
            ]
        );
    }

    /**
     * @param  array{email: string, username: string, name: string, password: string}  $data
     */
    private function upsertUser(array $data): User
    {
        $user = User::query()->firstOrNew(['email' => $data['email']]);

        $user->fill([
            'username' => $data['username'],
            'name' => $data['name'],
            'is_active' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        if (! $user->exists) {
            $user->password = $data['password'];
        }

        $user->save();

        return $user;
    }
}
