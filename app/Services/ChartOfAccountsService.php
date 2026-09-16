<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Exceptions\DomainException;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsService
{
    /**
     * @param  array{
     *     account_code: string,
     *     account_name: string,
     *     account_name_ar?: string|null,
     *     account_type: AccountType|string,
     *     parent_id?: int|null,
     *     currency_id?: int|null,
     *     is_cash_account?: bool,
     *     is_bank_account?: bool,
     *     description?: string|null
     * }  $data
     */
    public function create(array $data, ?int $userId = null): Account
    {
        return DB::transaction(function () use ($data, $userId) {
            $type = $data['account_type'] instanceof AccountType
                ? $data['account_type']
                : AccountType::from((string) $data['account_type']);

            $parent = null;
            if (! empty($data['parent_id'])) {
                $parent = Account::query()->lockForUpdate()->findOrFail((int) $data['parent_id']);

                if (! $parent->is_control_account) {
                    throw new DomainException(__('Parent account must be a control account.'));
                }

                if ($parent->account_type !== $type) {
                    throw new DomainException(__('Account type must match the parent account type.'));
                }
            }

            $currencyId = $data['currency_id'] ?? null;
            if ($currencyId === null && $parent) {
                $currencyId = $parent->currency_id;
            }

            $normalBalance = in_array($type, [AccountType::Asset, AccountType::Expense], true)
                ? NormalBalance::Debit
                : NormalBalance::Credit;

            return Account::query()->create([
                'account_code' => $data['account_code'],
                'account_name' => $data['account_name'],
                'account_name_ar' => $data['account_name_ar'] ?? null,
                'account_type' => $type,
                'parent_id' => $parent?->id,
                'account_level' => $parent ? $parent->account_level + 1 : 1,
                'normal_balance' => $normalBalance,
                'currency_id' => $currencyId,
                'is_control_account' => false,
                'is_cash_account' => (bool) ($data['is_cash_account'] ?? false),
                'is_bank_account' => (bool) ($data['is_bank_account'] ?? false),
                'is_customer_account' => false,
                'is_vendor_account' => false,
                'allow_posting' => true,
                'opening_balance' => 0,
                'opening_debit' => 0,
                'opening_credit' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'description' => $data['description'] ?? ($data['account_name_ar'] ?? $data['account_name']),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }
}
