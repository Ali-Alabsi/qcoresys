<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Models\Account;
use App\Models\Currency;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    /** @var array<string, int> */
    private array $currencyIds = [];

    /** @var array<string, int> */
    private array $idsByCode = [];

    /** Leaf codes from the HTML workstation that remain postable. */
    private const ACTIVE_LEAF_CODES = [
        '3111', '3112', '3211', '3212', '3311', '3312', '3411', '3412',
        '111101', '111102', '111103', '111104',
        '111201', '111202', '111203', '111204',
        '4111', '4112', '4113', '4114', '4115',
        '5111', '5112', '5113', '5114', '5115', '5116',
        '2111', '2121', '2122',
    ];

    public function run(): void
    {
        $this->currencyIds = Currency::query()
            ->whereIn('code', ['USD', 'SAR', 'YER'])
            ->pluck('id', 'code')
            ->all();

        $tree = $this->tree();

        DB::transaction(function () use ($tree) {
            foreach ($tree as $node) {
                $this->seedNode($node, null, 1);
            }

            $this->deactivateObsoleteLeaves();
        });

        $this->seedExpenseCategories();
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function seedNode(array $node, ?int $parentId, int $level): void
    {
        $isLeaf = empty($node['children']);
        $isControl = ! $isLeaf || (bool) ($node['control'] ?? false);
        $currencyCode = $node['currency'] ?? null;
        $active = (bool) ($node['active'] ?? true);

        $account = Account::withTrashed()->updateOrCreate(
            ['account_code' => $node['code']],
            [
                'account_name' => $node['name'],
                'account_name_ar' => $node['name_ar'] ?? $node['description'] ?? null,
                'account_type' => $node['type'],
                'parent_id' => $parentId,
                'account_level' => $level,
                'normal_balance' => $node['normal'],
                'currency_id' => $currencyCode ? ($this->currencyIds[$currencyCode] ?? null) : null,
                'is_control_account' => $isControl,
                'is_cash_account' => (bool) ($node['cash'] ?? false),
                'is_bank_account' => (bool) ($node['bank'] ?? false),
                'is_customer_account' => (bool) ($node['ar'] ?? false),
                'is_vendor_account' => (bool) ($node['ap'] ?? false),
                'allow_posting' => ! $isControl && $isLeaf && $active,
                'description' => $node['description'] ?? null,
                'is_active' => $active,
                'opening_balance' => 0,
                'opening_debit' => 0,
                'opening_credit' => 0,
                'current_balance' => 0,
                'deleted_at' => null,
            ]
        );

        if ($account->trashed()) {
            $account->restore();
        }

        $this->idsByCode[$node['code']] = $account->id;

        foreach ($node['children'] ?? [] as $child) {
            $this->seedNode($child, $account->id, $level + 1);
        }
    }

    private function deactivateObsoleteLeaves(): void
    {
        Account::query()
            ->where('is_control_account', false)
            ->where('is_customer_account', false)
            ->whereNotIn('account_code', self::ACTIVE_LEAF_CODES)
            ->update(['is_active' => false, 'allow_posting' => false]);
    }

    private function seedExpenseCategories(): void
    {
        $map = [
            'HOST_USD' => ['5111', 'Hosting & Domains (USD)'],
            'CLOUD_USD' => ['5112', 'Cloud & Tools (USD)'],
            'BANK_USD' => ['5113', 'Bank & Payment Fees (USD)'],
            'EXT_SAR' => ['5114', 'External Fees & Consulting (USD)'],
            'BANK_SAR' => ['5113', 'Bank Fees (USD)'],
            'OFFICE_YER' => ['5115', 'Office Supplies (USD)'],
            'UTIL_YER' => ['5116', 'Internet & Utilities (USD)'],
            'XFER_YER' => ['5113', 'Transfer Fees (USD)'],
            'GEN' => ['5111', 'General Operating Expense'],
        ];

        foreach ($map as $code => [$accountCode, $name]) {
            ExpenseCategory::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'account_id' => $this->idsByCode[$accountCode] ?? null,
                    'is_project_expense' => false,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function tree(): array
    {
        $asset = AccountType::Asset;
        $liab = AccountType::Liability;
        $equity = AccountType::Equity;
        $rev = AccountType::Revenue;
        $exp = AccountType::Expense;
        $dr = NormalBalance::Debit;
        $cr = NormalBalance::Credit;

        return [
            [
                'code' => '1000', 'name' => 'Assets', 'type' => $asset, 'normal' => $dr,
                'description' => 'الأصول',
                'children' => [
                    [
                        'code' => '1100', 'name' => 'Current Assets', 'type' => $asset, 'normal' => $dr,
                        'description' => 'الأصول المتداولة',
                        'children' => [
                            [
                                'code' => '1110', 'name' => 'Cash & Equivalents', 'type' => $asset, 'normal' => $dr,
                                'description' => 'النقدية وما في حكمها',
                                'children' => [
                                    [
                                        'code' => '1111', 'name' => 'Mohamed Al-Mahfadi Cash Boxes', 'type' => $asset, 'normal' => $dr,
                                        'description' => 'صناديق محمد المحفدي',
                                        'children' => [
                                            [
                                                'code' => '111101', 'name' => 'Mohamed Visa USD', 'type' => $asset, 'normal' => $dr,
                                                'currency' => 'USD', 'cash' => true,
                                                'name_ar' => 'صندوق محمد المحفدي - فيزا - دولار امريكي',
                                                'description' => 'صندوق محمد المحفدي - فيزا - دولار امريكي',
                                            ],
                                            [
                                                'code' => '111102', 'name' => 'Mohamed Current USD', 'type' => $asset, 'normal' => $dr,
                                                'currency' => 'USD', 'cash' => true, 'bank' => true,
                                                'name_ar' => 'صندوق محمد المحفدي - جاري - دولار امريكي',
                                                'description' => 'صندوق محمد المحفدي - جاري - دولار امريكي',
                                            ],
                                            [
                                                'code' => '111103', 'name' => 'Mohamed Current YER', 'type' => $asset, 'normal' => $dr,
                                                'currency' => 'YER', 'cash' => true,
                                                'name_ar' => 'صندوق محمد المحفدي - جاري - ريال يمني',
                                                'description' => 'صندوق محمد المحفدي - جاري - ريال يمني',
                                            ],
                                            [
                                                'code' => '111104', 'name' => 'Mohamed Current SAR', 'type' => $asset, 'normal' => $dr,
                                                'currency' => 'SAR', 'cash' => true,
                                                'name_ar' => 'صندوق محمد المحفدي - جاري - ريال سعودي',
                                                'description' => 'صندوق محمد المحفدي - جاري - ريال سعودي',
                                            ],
                                        ],
                                    ],
                                    [
                                        'code' => '1112', 'name' => 'Ali Nabil Cash Boxes', 'type' => $asset, 'normal' => $dr,
                                        'description' => 'صناديق علي نبيل',
                                        'children' => [
                                            [
                                                'code' => '111201', 'name' => 'Ali Visa USD', 'type' => $asset, 'normal' => $dr,
                                                'currency' => 'USD', 'cash' => true,
                                                'name_ar' => 'صندوق علي نبيل - فيزا - دولار امريكي',
                                                'description' => 'صندوق علي نبيل - فيزا - دولار امريكي',
                                            ],
                                            [
                                                'code' => '111202', 'name' => 'Ali Current USD', 'type' => $asset, 'normal' => $dr,
                                                'currency' => 'USD', 'cash' => true, 'bank' => true,
                                                'name_ar' => 'صندوق علي نبيل - جاري - دولار امريكي',
                                                'description' => 'صندوق علي نبيل - جاري - دولار امريكي',
                                            ],
                                            [
                                                'code' => '111203', 'name' => 'Ali Current YER', 'type' => $asset, 'normal' => $dr,
                                                'currency' => 'YER', 'cash' => true,
                                                'name_ar' => 'صندوق علي نبيل - جاري - ريال يمني',
                                                'description' => 'صندوق علي نبيل - جاري - ريال يمني',
                                            ],
                                            [
                                                'code' => '111204', 'name' => 'Ali Current SAR', 'type' => $asset, 'normal' => $dr,
                                                'currency' => 'SAR', 'cash' => true,
                                                'name_ar' => 'صندوق علي نبيل - جاري - ريال سعودي',
                                                'description' => 'صندوق علي نبيل - جاري - ريال سعودي',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'code' => '1120', 'name' => 'Accounts Receivable', 'type' => $asset, 'normal' => $dr,
                                'description' => 'الذمم المدينة',
                                'children' => [
                                    [
                                        'code' => '1121', 'name' => 'AR Customers USD', 'type' => $asset, 'normal' => $dr,
                                        'currency' => 'USD', 'ar' => true, 'control' => true,
                                        'name_ar' => 'ذمم العملاء - جاري - دولار امريكي',
                                        'description' => 'ذمم العملاء - جاري - دولار امريكي',
                                    ],
                                    [
                                        'code' => '1122', 'name' => 'AR Customers YER', 'type' => $asset, 'normal' => $dr,
                                        'currency' => 'YER', 'ar' => true, 'control' => true,
                                        'name_ar' => 'ذمم العملاء - جاري - ريال يمني',
                                        'description' => 'ذمم العملاء - جاري - ريال يمني',
                                    ],
                                    [
                                        'code' => '1123', 'name' => 'AR Customers SAR', 'type' => $asset, 'normal' => $dr,
                                        'currency' => 'SAR', 'ar' => true, 'control' => true,
                                        'name_ar' => 'ذمم العملاء - جاري - ريال سعودي',
                                        'description' => 'ذمم العملاء - جاري - ريال سعودي',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => '2000', 'name' => 'Liabilities', 'type' => $liab, 'normal' => $cr,
                'description' => 'الخصوم والالتزامات',
                'children' => [
                    [
                        'code' => '2100', 'name' => 'Current Liabilities', 'type' => $liab, 'normal' => $cr,
                        'description' => 'الالتزامات المتداولة',
                        'children' => [
                            [
                                'code' => '2110', 'name' => 'Accounts Payable', 'type' => $liab, 'normal' => $cr,
                                'description' => 'الموردون والدائنون',
                                'children' => [
                                    [
                                        'code' => '2111', 'name' => 'AP Vendors USD', 'type' => $liab, 'normal' => $cr,
                                        'currency' => 'USD', 'ap' => true, 'active' => false,
                                        'name_ar' => 'دائنو خدمات وموردين — دولار',
                                        'description' => 'دائنو خدمات وموردين — دولار (محفوظ للإعدادات)',
                                    ],
                                ],
                            ],
                            [
                                'code' => '2120', 'name' => 'Dividends Payable', 'type' => $liab, 'normal' => $cr,
                                'description' => 'توزيعات أرباح مستحقة',
                                'children' => [
                                    [
                                        'code' => '2121', 'name' => 'Mohamed Al-Mahfadi Dividends Payable USD', 'type' => $liab, 'normal' => $cr,
                                        'currency' => 'USD',
                                        'name_ar' => 'توزيعات أرباح مستحقة محمد المحفدي - دولار امريكي',
                                        'description' => 'توزيعات أرباح مستحقة محمد المحفدي - دولار امريكي',
                                    ],
                                    [
                                        'code' => '2122', 'name' => 'Ali Nabil Dividends Payable USD', 'type' => $liab, 'normal' => $cr,
                                        'currency' => 'USD',
                                        'name_ar' => 'توزيعات أرباح مستحقة علي نبيل - دولار امريكي',
                                        'description' => 'توزيعات أرباح مستحقة علي نبيل - دولار امريكي',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => '3000', 'name' => 'Equity', 'type' => $equity, 'normal' => $cr,
                'description' => 'حقوق الملكية',
                'children' => [
                    [
                        'code' => '3100', 'name' => 'Paid-in Capital', 'type' => $equity, 'normal' => $cr,
                        'description' => 'رأس المال المدفوع',
                        'children' => [
                            [
                                'code' => '3111', 'name' => 'Mohamed Al-Mahfadi Capital USD', 'type' => $equity, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'راس مال محمد المحفدي - دولار امريكي',
                                'description' => 'راس مال محمد المحفدي - دولار امريكي',
                            ],
                            [
                                'code' => '3112', 'name' => 'Ali Nabil Capital USD', 'type' => $equity, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'راس مال علي نبيل - دولار امريكي',
                                'description' => 'راس مال علي نبيل - دولار امريكي',
                            ],
                        ],
                    ],
                    [
                        'code' => '3200', 'name' => 'Capital Drawings', 'type' => $equity, 'normal' => $dr,
                        'description' => 'مسحوبات رأس المال',
                        'children' => [
                            [
                                'code' => '3211', 'name' => 'Mohamed Al-Mahfadi Capital Drawings USD', 'type' => $equity, 'normal' => $dr,
                                'currency' => 'USD',
                                'name_ar' => 'مسحوبات راس مال محمد المحفدي - دولار امريكي',
                                'description' => 'مسحوبات راس مال محمد المحفدي - دولار امريكي',
                            ],
                            [
                                'code' => '3212', 'name' => 'Ali Nabil Capital Drawings USD', 'type' => $equity, 'normal' => $dr,
                                'currency' => 'USD',
                                'name_ar' => 'مسحوبات راس مال علي نبيل - دولار امريكي',
                                'description' => 'مسحوبات راس مال علي نبيل - دولار امريكي',
                            ],
                        ],
                    ],
                    [
                        'code' => '3300', 'name' => 'Net Profit', 'type' => $equity, 'normal' => $cr,
                        'description' => 'صافي الربح',
                        'children' => [
                            [
                                'code' => '3311', 'name' => 'Mohamed Al-Mahfadi Net Profit USD', 'type' => $equity, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'صافي ربح محمد المحفدي - دولار امريكي',
                                'description' => 'صافي ربح محمد المحفدي - دولار امريكي',
                            ],
                            [
                                'code' => '3312', 'name' => 'Ali Nabil Net Profit USD', 'type' => $equity, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'صافي ربح علي نبيل - دولار امريكي',
                                'description' => 'صافي ربح علي نبيل - دولار امريكي',
                            ],
                        ],
                    ],
                    [
                        'code' => '3400', 'name' => 'Retained Earnings', 'type' => $equity, 'normal' => $cr,
                        'description' => 'أرباح محتجزة / أرباح مرحلة',
                        'children' => [
                            [
                                'code' => '3411', 'name' => 'Mohamed Al-Mahfadi Retained Earnings USD', 'type' => $equity, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'أرباح محتجزة / أرباح مرحلة محمد المحفدي - دولار امريكي',
                                'description' => 'أرباح محتجزة / أرباح مرحلة محمد المحفدي - دولار امريكي',
                            ],
                            [
                                'code' => '3412', 'name' => 'Ali Nabil Retained Earnings USD', 'type' => $equity, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'أرباح محتجزة / أرباح مرحلة علي نبيل - دولار امريكي',
                                'description' => 'أرباح محتجزة / أرباح مرحلة علي نبيل - دولار امريكي',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => '4000', 'name' => 'Revenue', 'type' => $rev, 'normal' => $cr,
                'description' => 'الإيرادات',
                'children' => [
                    [
                        'code' => '4100', 'name' => 'Operating Revenue USD', 'type' => $rev, 'normal' => $cr,
                        'description' => 'إيرادات تشغيلية — دولار',
                        'children' => [
                            [
                                'code' => '4111', 'name' => 'Software Engineering Revenue USD', 'type' => $rev, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'إيرادات تطوير وهندسة برمجيات - دولار امريكي',
                                'description' => 'إيرادات تطوير وهندسة برمجيات - دولار امريكي',
                            ],
                            [
                                'code' => '4112', 'name' => 'Banking Consulting & Integration USD', 'type' => $rev, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'إيرادات استشارات وتكامل بنكي - دولار امريكي',
                                'description' => 'إيرادات استشارات وتكامل بنكي - دولار امريكي',
                            ],
                            [
                                'code' => '4113', 'name' => 'Support & SLA Revenue USD', 'type' => $rev, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'إيرادات عقود دعم وصيانة وتشغيل - دولار امريكي',
                                'description' => 'إيرادات عقود دعم وصيانة وتشغيل - دولار امريكي',
                            ],
                            [
                                'code' => '4114', 'name' => 'Software Development Revenue USD', 'type' => $rev, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'إيرادات تطوير برمجيات - دولار امريكي',
                                'description' => 'إيرادات تطوير برمجيات - دولار امريكي',
                            ],
                            [
                                'code' => '4115', 'name' => 'Apps & Software Revenue USD', 'type' => $rev, 'normal' => $cr,
                                'currency' => 'USD',
                                'name_ar' => 'إيرادات برمجيات وتطبيقات - دولار امريكي',
                                'description' => 'إيرادات برمجيات وتطبيقات - دولار امريكي',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => '5000', 'name' => 'Expenses', 'type' => $exp, 'normal' => $dr,
                'description' => 'المصروفات',
                'children' => [
                    [
                        'code' => '5100', 'name' => 'Operating Expenses USD', 'type' => $exp, 'normal' => $dr,
                        'description' => 'مصروفات تشغيلية — دولار',
                        'children' => [
                            [
                                'code' => '5111', 'name' => 'Hosting & Domains USD', 'type' => $exp, 'normal' => $dr,
                                'currency' => 'USD',
                                'name_ar' => 'اشتراكات استضافة ونطاقات - دولار امريكي',
                                'description' => 'اشتراكات استضافة ونطاقات - دولار امريكي',
                            ],
                            [
                                'code' => '5112', 'name' => 'Cloud, Security & Licenses USD', 'type' => $exp, 'normal' => $dr,
                                'currency' => 'USD',
                                'name_ar' => 'خدمات سحابية وحماية وتراخيص - دولار امريكي',
                                'description' => 'خدمات سحابية وحماية وتراخيص - دولار امريكي',
                            ],
                            [
                                'code' => '5113', 'name' => 'Bank & Payment Gateway Fees USD', 'type' => $exp, 'normal' => $dr,
                                'currency' => 'USD',
                                'name_ar' => 'عمولات تحويل ورسوم بوابات الدفع - دولار امريكي',
                                'description' => 'عمولات تحويل ورسوم بوابات الدفع - دولار امريكي',
                            ],
                            [
                                'code' => '5114', 'name' => 'External Services & Consulting USD', 'type' => $exp, 'normal' => $dr,
                                'currency' => 'USD',
                                'name_ar' => 'أتعاب خدمات خارجية واستشارات - دولار امريكي',
                                'description' => 'أتعاب خدمات خارجية واستشارات - دولار امريكي',
                            ],
                            [
                                'code' => '5115', 'name' => 'Office Supplies USD', 'type' => $exp, 'normal' => $dr,
                                'currency' => 'USD',
                                'name_ar' => 'نثريات ومستلزمات مكتبية وتجهيزات - دولار امريكي',
                                'description' => 'نثريات ومستلزمات مكتبية وتجهيزات - دولار امريكي',
                            ],
                            [
                                'code' => '5116', 'name' => 'Internet, Telecom & Power USD', 'type' => $exp, 'normal' => $dr,
                                'currency' => 'USD',
                                'name_ar' => 'إنترنت واتصالات وكهرباء - دولار امريكي',
                                'description' => 'إنترنت واتصالات وكهرباء - دولار امريكي',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
