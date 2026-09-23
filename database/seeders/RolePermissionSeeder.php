<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private array $modules = [
        'dashboard' => ['view'],
        'customers' => ['view', 'create', 'update', 'delete'],
        'customer_requests' => ['view', 'create', 'update', 'delete'],
        'consultations' => ['view', 'create', 'update', 'delete'],
        'proposals' => ['view', 'create', 'update', 'delete', 'approve', 'send'],
        'quotations' => ['view', 'create', 'update', 'delete', 'approve', 'send'],
        'contracts' => ['view', 'create', 'update', 'delete'],
        'projects' => ['view', 'create', 'update', 'delete'],
        'invoices' => ['view', 'create', 'update', 'delete', 'approve', 'post'],
        'payments' => ['view', 'create', 'update', 'delete', 'post'],
        'expenses' => ['view', 'create', 'update', 'delete', 'approve', 'post'],
        'journals' => ['view', 'create', 'update', 'delete', 'post', 'reverse'],
        'vendors' => ['view', 'create', 'update', 'delete'],
        'accounts' => ['view', 'create', 'update', 'delete'],
        'employees' => ['view', 'create', 'update', 'delete'],
        'users' => ['view', 'create', 'update', 'delete'],
        'settings' => ['view', 'update'],
        'reports' => ['view'],
        'portfolio' => ['view', 'create', 'update', 'delete'],
        'services_catalog' => ['view', 'create', 'update', 'delete'],
    ];

    public function run(): void
    {
        $permissions = $this->seedPermissions();
        $this->seedRoles($permissions);
    }

    /**
     * @return array<string, Permission>
     */
    private function seedPermissions(): array
    {
        $permissions = [];

        foreach ($this->modules as $module => $actions) {
            foreach ($actions as $action) {
                $code = "{$module}.{$action}";

                $permissions[$code] = Permission::query()->updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => ucfirst(str_replace('_', ' ', $module)).' '.ucfirst($action),
                        'module' => $module,
                        'action' => $action,
                        'description' => "Allows {$action} on {$module}",
                        'is_active' => true,
                    ]
                );
            }
        }

        return $permissions;
    }

    /**
     * @param  array<string, Permission>  $permissions
     */
    private function seedRoles(array $permissions): void
    {
        $allCodes = array_keys($permissions);

        $roles = [
            'SUPER_ADMIN' => [
                'name' => 'Super Administrator',
                'description' => 'Full system access',
                'permissions' => $allCodes,
            ],
            'ADMIN' => [
                'name' => 'Administrator',
                'description' => 'Administrative access to all modules',
                'permissions' => $allCodes,
            ],
            'MANAGEMENT' => [
                'name' => 'Management',
                'description' => 'Executive oversight and approvals',
                'permissions' => $this->withDashboard($permissions, [
                    'customers.view', 'customer_requests.view', 'consultations.view',
                    'proposals.view', 'proposals.approve', 'proposals.send',
                    'quotations.view', 'quotations.approve', 'quotations.send',
                    'contracts.view', 'projects.view',
                    'invoices.view', 'invoices.approve', 'invoices.post',
                    'payments.view', 'payments.post',
                    'expenses.view', 'expenses.approve', 'expenses.post',
                    'journals.view', 'journals.post',
                    'vendors.view', 'accounts.view', 'employees.view', 'users.view',
                    'settings.view', 'reports.view',
                ]),
            ],
            'SALES_MANAGER' => [
                'name' => 'Sales Manager',
                'description' => 'Sales pipeline and customer relationship management',
                'permissions' => $this->withDashboard($permissions, [
                    'customers.view', 'customers.create', 'customers.update',
                    'customer_requests.view', 'customer_requests.create', 'customer_requests.update', 'customer_requests.delete',
                    'consultations.view', 'consultations.create', 'consultations.update',
                    'proposals.view', 'proposals.create', 'proposals.update', 'proposals.approve', 'proposals.send',
                    'quotations.view', 'quotations.create', 'quotations.update', 'quotations.approve', 'quotations.send',
                    'contracts.view', 'contracts.create', 'contracts.update',
                    'projects.view', 'projects.create',
                    'invoices.view', 'payments.view',
                    'reports.view',
                ]),
            ],
            'ACCOUNT_MANAGER' => [
                'name' => 'Account Manager',
                'description' => 'Customer account and delivery oversight',
                'permissions' => $this->withDashboard($permissions, [
                    'customers.view', 'customers.update',
                    'customer_requests.view', 'customer_requests.create', 'customer_requests.update', 'customer_requests.delete',
                    'consultations.view', 'consultations.create', 'consultations.update',
                    'proposals.view', 'quotations.view', 'contracts.view',
                    'projects.view', 'projects.update',
                    'invoices.view', 'payments.view', 'reports.view',
                ]),
            ],
            'SALES' => [
                'name' => 'Sales',
                'description' => 'Sales representative',
                'permissions' => $this->withDashboard($permissions, [
                    'customers.view', 'customers.create', 'customers.update',
                    'customer_requests.view', 'customer_requests.create', 'customer_requests.update',
                    'consultations.view', 'consultations.create',
                    'proposals.view', 'proposals.create', 'proposals.update',
                    'quotations.view', 'quotations.create', 'quotations.update',
                    'contracts.view', 'contracts.create',
                    'projects.view',
                ]),
            ],
            'CONSULTANT' => [
                'name' => 'Consultant',
                'description' => 'Technical consultant',
                'permissions' => $this->withDashboard($permissions, [
                    'customers.view', 'customer_requests.view',
                    'consultations.view', 'consultations.create', 'consultations.update',
                    'proposals.view', 'proposals.create', 'proposals.update',
                    'quotations.view',
                    'projects.view', 'projects.update',
                ]),
            ],
            'PROJECT_MANAGER' => [
                'name' => 'Project Manager',
                'description' => 'Project delivery and team management',
                'permissions' => $this->withDashboard($permissions, [
                    'customers.view', 'customer_requests.view',
                    'consultations.view', 'proposals.view', 'quotations.view', 'contracts.view',
                    'projects.view', 'projects.create', 'projects.update', 'projects.delete',
                    'expenses.view', 'expenses.create',
                    'reports.view',
                ]),
            ],
            'DEVELOPER' => [
                'name' => 'Developer',
                'description' => 'Software developer',
                'permissions' => $this->withDashboard($permissions, [
                    'customers.view', 'customer_requests.view',
                    'consultations.view', 'projects.view', 'projects.update',
                ]),
            ],
            'ACCOUNTING_MANAGER' => [
                'name' => 'Accounting Manager',
                'description' => 'Finance and accounting oversight',
                'permissions' => $this->withDashboard($permissions, [
                    'customers.view',
                    'vendors.view', 'vendors.create', 'vendors.update',
                    'accounts.view', 'accounts.create', 'accounts.update',
                    'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete', 'invoices.approve', 'invoices.post',
                    'payments.view', 'payments.create', 'payments.update', 'payments.delete', 'payments.post',
                    'expenses.view', 'expenses.create', 'expenses.update', 'expenses.delete', 'expenses.approve', 'expenses.post',
                    'journals.view', 'journals.create', 'journals.update', 'journals.delete', 'journals.post', 'journals.reverse',
                    'reports.view', 'settings.view',
                ]),
            ],
            'ACCOUNTANT' => [
                'name' => 'Accountant',
                'description' => 'Accounting operations',
                'permissions' => $this->withDashboard($permissions, [
                    'customers.view',
                    'vendors.view',
                    'accounts.view',
                    'invoices.view', 'invoices.create', 'invoices.update', 'invoices.approve', 'invoices.post',
                    'payments.view', 'payments.create', 'payments.update', 'payments.post',
                    'expenses.view', 'expenses.create', 'expenses.update', 'expenses.approve', 'expenses.post',
                    'journals.view', 'journals.create', 'journals.update', 'journals.post',
                    'reports.view',
                ]),
            ],
            'EMPLOYEE' => [
                'name' => 'Employee',
                'description' => 'General employee access',
                'permissions' => $this->withDashboard($permissions, [
                    'customers.view', 'customer_requests.view',
                    'consultations.view', 'projects.view',
                    'expenses.view', 'expenses.create',
                ]),
            ],
        ];

        foreach ($roles as $code => $definition) {
            $role = Role::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'is_system_role' => true,
                    'is_active' => true,
                ]
            );

            $permissionIds = collect($definition['permissions'])
                ->map(fn (string $permissionCode) => $permissions[$permissionCode]->id ?? null)
                ->filter()
                ->values()
                ->all();

            $syncData = [];
            foreach ($permissionIds as $permissionId) {
                $syncData[$permissionId] = ['created_at' => now()];
            }

            $role->permissions()->sync($syncData);
        }
    }

    /**
     * @param  array<string, Permission>  $permissions
     * @param  list<string>  $codes
     * @return list<string>
     */
    private function withDashboard(array $permissions, array $codes): array
    {
        return $this->codes($permissions, array_values(array_unique([
            'dashboard.view',
            ...$codes,
        ])));
    }

    /**
     * @param  array<string, Permission>  $permissions
     * @param  list<string>  $codes
     * @return list<string>
     */
    private function codes(array $permissions, array $codes): array
    {
        return array_values(array_filter($codes, fn (string $code) => isset($permissions[$code])));
    }
}
