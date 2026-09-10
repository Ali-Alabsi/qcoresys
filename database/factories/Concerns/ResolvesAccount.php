<?php

namespace Database\Factories\Concerns;

use App\Models\Account;

trait ResolvesAccount
{
    protected static function defaultCashAccountId(): int
    {
        $accountId = Account::query()
            ->where('is_cash_account', true)
            ->value('id');

        if ($accountId) {
            return $accountId;
        }

        return Account::factory()->cash()->create()->id;
    }

    protected static function defaultExpenseAccountId(): int
    {
        $accountId = Account::query()
            ->where('account_code', '5100')
            ->value('id');

        if ($accountId) {
            return $accountId;
        }

        return Account::factory()->expense()->create()->id;
    }
}
