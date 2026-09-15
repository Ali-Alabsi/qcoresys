<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Exceptions\DomainException;
use App\Models\Account;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerAccountService
{
    /** @var array<string, string> */
    private const PARENT_BY_CURRENCY = [
        'USD' => '1121',
        'YER' => '1122',
        'SAR' => '1123',
    ];

    public function ensureFor(Customer $customer): Account
    {
        return DB::transaction(function () use ($customer) {
            $customer = Customer::query()->lockForUpdate()->findOrFail($customer->id);

            if ($customer->account_id) {
                $account = Account::query()->find($customer->account_id);
                if ($account) {
                    return $account;
                }
            }

            $parent = $this->resolveParent($customer);
            $code = $this->nextChildCode($parent);
            $names = $this->accountNames($customer);

            $account = Account::query()->create([
                'account_code' => $code,
                'account_name' => $names['en'],
                'account_name_ar' => $names['ar'],
                'account_type' => AccountType::Asset,
                'parent_id' => $parent->id,
                'account_level' => $parent->account_level + 1,
                'normal_balance' => NormalBalance::Debit,
                'currency_id' => $parent->currency_id ?? $customer->default_currency_id,
                'is_control_account' => false,
                'is_cash_account' => false,
                'is_bank_account' => false,
                'is_customer_account' => true,
                'is_vendor_account' => false,
                'allow_posting' => true,
                'opening_balance' => 0,
                'opening_debit' => 0,
                'opening_credit' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'description' => $names['ar'],
                'created_by' => $customer->created_by,
                'updated_by' => $customer->updated_by,
            ]);

            $customer->forceFill(['account_id' => $account->id])->save();

            return $account;
        });
    }

    public function syncName(Customer $customer): void
    {
        if (! $customer->account_id) {
            return;
        }

        $account = Account::query()->find($customer->account_id);
        if (! $account) {
            return;
        }

        $names = $this->accountNames($customer);
        $account->update([
            'account_name' => $names['en'],
            'account_name_ar' => $names['ar'],
            'description' => $names['ar'],
            'updated_by' => $customer->updated_by,
        ]);
    }

    public function ensureForAllMissing(): int
    {
        $count = 0;

        Customer::query()
            ->whereNull('account_id')
            ->orderBy('id')
            ->each(function (Customer $customer) use (&$count) {
                $this->ensureFor($customer);
                $count++;
            });

        return $count;
    }

    protected function resolveParent(Customer $customer): Account
    {
        $customer->loadMissing('defaultCurrency');
        $currencyCode = $customer->defaultCurrency?->code ?? 'USD';
        $parentCode = self::PARENT_BY_CURRENCY[$currencyCode] ?? self::PARENT_BY_CURRENCY['USD'];

        $parent = Account::query()->where('account_code', $parentCode)->first();

        if (! $parent) {
            throw new DomainException("Accounts receivable parent [{$parentCode}] is missing from the chart of accounts.");
        }

        return $parent;
    }

    protected function nextChildCode(Account $parent): string
    {
        $prefix = $parent->account_code;
        $existing = Account::query()
            ->where('parent_id', $parent->id)
            ->where('account_code', 'like', $prefix.'%')
            ->pluck('account_code');

        $maxSuffix = 0;
        foreach ($existing as $code) {
            $suffix = substr((string) $code, strlen($prefix));
            if (ctype_digit($suffix)) {
                $maxSuffix = max($maxSuffix, (int) $suffix);
            }
        }

        return $prefix.str_pad((string) ($maxSuffix + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{en: string, ar: string}
     */
    protected function accountNames(Customer $customer): array
    {
        $label = $customer->company_name ?: $customer->name;

        return [
            'en' => 'AR - '.$label,
            'ar' => 'ذمم '.$label,
        ];
    }
}
