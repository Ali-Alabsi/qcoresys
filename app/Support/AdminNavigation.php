<?php

namespace App\Support;

use App\Models\User;

class AdminNavigation
{
    /**
     * Ordered navigation sections for the admin sidebar.
     *
     * @return list<array{label: string, links: list<array{permission: string, route: string, label: string, active: list<string>}}}>
     */
    public static function sections(): array
    {
        return [
            [
                'label' => __('Overview'),
                'links' => [
                    ['permission' => 'dashboard.view', 'route' => 'admin.dashboard', 'label' => __('Dashboard'), 'active' => ['admin.dashboard']],
                ],
            ],
            [
                'label' => __('Sales'),
                'links' => [
                    ['permission' => 'customers.view', 'route' => 'admin.customers.index', 'label' => __('Customers'), 'active' => ['admin.customers.*']],
                    ['permission' => 'customer_requests.view', 'route' => 'admin.customer-requests.index', 'label' => __('Requests'), 'active' => ['admin.customer-requests.*']],
                    ['permission' => 'quotations.view', 'route' => 'admin.quotations.index', 'label' => __('Quotations'), 'active' => ['admin.quotations.*']],
                ],
            ],
            [
                'label' => __('Finance'),
                'links' => [
                    ['permission' => 'invoices.view', 'route' => 'admin.invoices.index', 'label' => __('Invoices'), 'active' => ['admin.invoices.*']],
                    ['permission' => 'payments.view', 'route' => 'admin.payments.index', 'label' => __('Payments'), 'active' => ['admin.payments.*']],
                    ['permission' => 'expenses.view', 'route' => 'admin.expenses.index', 'label' => __('Expenses'), 'active' => ['admin.expenses.*']],
                    ['permission' => 'accounts.view', 'route' => 'admin.accounts.index', 'label' => __('Accounts'), 'active' => ['admin.accounts.*']],
                    ['permission' => 'journals.view', 'route' => 'admin.journals.index', 'label' => __('Financial operations'), 'active' => ['admin.journals.index', 'admin.journals.show', 'admin.journals.create']],
                ],
            ],
            [
                'label' => __('Website'),
                'links' => [
                    ['permission' => 'services_catalog.view', 'route' => 'admin.services.index', 'label' => __('Services'), 'active' => ['admin.services.*']],
                    ['permission' => 'portfolio.view', 'route' => 'admin.portfolio-projects.index', 'label' => __('Portfolio'), 'active' => ['admin.portfolio-projects.*']],
                ],
            ],
            [
                'label' => __('Administration'),
                'links' => [
                    ['permission' => 'users.view', 'route' => 'admin.users.index', 'label' => __('Users'), 'active' => ['admin.users.*']],
                    ['permission' => 'settings.view', 'route' => 'admin.settings.edit', 'label' => __('Settings'), 'active' => ['admin.settings.*']],
                    ['permission' => 'settings.view', 'route' => 'admin.setup.index', 'label' => __('Setup'), 'active' => ['admin.setup.*']],
                ],
            ],
        ];
    }

    /**
     * Sections with only links the user can see. Empty sections are omitted.
     *
     * @return list<array{label: string, links: list<array{permission: string, route: string, label: string, active: list<string>}}}>
     */
    public static function visibleSections(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $sections = [];

        foreach (self::sections() as $section) {
            $links = array_values(array_filter(
                $section['links'],
                fn (array $link) => $user->hasPermission($link['permission'])
            ));

            if ($links === []) {
                continue;
            }

            $sections[] = [
                'label' => $section['label'],
                'links' => $links,
            ];
        }

        return $sections;
    }

    /**
     * First allowed admin route for post-login redirect and brand home link.
     */
    public static function homeRoute(?User $user): string
    {
        if (! $user) {
            return 'admin.login';
        }

        foreach (self::sections() as $section) {
            foreach ($section['links'] as $link) {
                if ($user->hasPermission($link['permission'])) {
                    return $link['route'];
                }
            }
        }

        return 'admin.login';
    }

    public static function homeUrl(?User $user): string
    {
        $route = self::homeRoute($user);

        return $route === 'admin.login'
            ? route('admin.login')
            : route($route);
    }
}
