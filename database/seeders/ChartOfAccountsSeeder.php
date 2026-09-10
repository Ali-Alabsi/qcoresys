<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Models\Account;
use App\Models\Currency;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $currencyId = Currency::query()->where('code', 'USD')->value('id');

        $accounts = [
            [
                'account_code' => '1000',
                'account_name' => 'Cash',
                'account_type' => AccountType::Asset,
                'normal_balance' => NormalBalance::Debit,
                'is_cash_account' => true,
                'allow_posting' => true,
            ],
            [
                'account_code' => '1100',
                'account_name' => 'Bank',
                'account_type' => AccountType::Asset,
                'normal_balance' => NormalBalance::Debit,
                'is_bank_account' => true,
                'allow_posting' => true,
            ],
            [
                'account_code' => '1200',
                'account_name' => 'Accounts Receivable',
                'account_type' => AccountType::Asset,
                'normal_balance' => NormalBalance::Debit,
                'is_customer_account' => true,
                'allow_posting' => true,
            ],
            [
                'account_code' => '2000',
                'account_name' => 'Accounts Payable',
                'account_type' => AccountType::Liability,
                'normal_balance' => NormalBalance::Credit,
                'is_vendor_account' => true,
                'allow_posting' => true,
            ],
            [
                'account_code' => '2100',
                'account_name' => 'VAT Payable',
                'account_type' => AccountType::Liability,
                'normal_balance' => NormalBalance::Credit,
                'allow_posting' => true,
            ],
            [
                'account_code' => '3000',
                'account_name' => 'Equity',
                'account_type' => AccountType::Equity,
                'normal_balance' => NormalBalance::Credit,
                'allow_posting' => true,
            ],
            [
                'account_code' => '4000',
                'account_name' => 'Consulting Revenue',
                'account_type' => AccountType::Revenue,
                'normal_balance' => NormalBalance::Credit,
                'allow_posting' => true,
            ],
            [
                'account_code' => '4100',
                'account_name' => 'Project Revenue',
                'account_type' => AccountType::Revenue,
                'normal_balance' => NormalBalance::Credit,
                'allow_posting' => true,
            ],
            [
                'account_code' => '5000',
                'account_name' => 'Project Direct Costs',
                'account_type' => AccountType::Expense,
                'normal_balance' => NormalBalance::Debit,
                'allow_posting' => true,
            ],
            [
                'account_code' => '5100',
                'account_name' => 'Operating Expenses',
                'account_type' => AccountType::Expense,
                'normal_balance' => NormalBalance::Debit,
                'allow_posting' => true,
            ],
        ];

        foreach ($accounts as $account) {
            Account::query()->updateOrCreate(
                ['account_code' => $account['account_code']],
                array_merge([
                    'account_level' => 1,
                    'currency_id' => $currencyId,
                    'is_control_account' => false,
                    'is_cash_account' => false,
                    'is_bank_account' => false,
                    'is_customer_account' => false,
                    'is_vendor_account' => false,
                    'opening_balance' => 0,
                    'opening_debit' => 0,
                    'opening_credit' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                ], $account)
            );
        }

        $operatingExpenseAccountId = Account::query()->where('account_code', '5100')->value('id');

        ExpenseCategory::query()->updateOrCreate(
            ['code' => 'GEN'],
            [
                'name' => 'General Operating Expense',
                'description' => 'Default operating expense category',
                'account_id' => $operatingExpenseAccountId,
                'is_project_expense' => false,
                'is_active' => true,
            ]
        );
    }
}
