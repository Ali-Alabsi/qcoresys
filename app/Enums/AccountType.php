<?php

namespace App\Enums;

enum AccountType: string
{
    case Asset = 'ASSET';
    case Liability = 'LIABILITY';
    case Equity = 'EQUITY';
    case Revenue = 'REVENUE';
    case Expense = 'EXPENSE';

    public function label(): string
    {
        return __('account_type.'.$this->value);
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Asset => 'bg-sky-100 text-sky-800',
            self::Liability => 'bg-orange-100 text-orange-800',
            self::Equity => 'bg-amber-100 text-amber-900',
            self::Revenue => 'bg-emerald-100 text-emerald-800',
            self::Expense => 'bg-violet-100 text-violet-800',
        };
    }
}
