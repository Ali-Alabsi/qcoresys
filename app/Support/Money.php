<?php

namespace App\Support;

final class Money
{
    public static function round(float|string|null $amount, int $precision = 2): float
    {
        return round((float) ($amount ?? 0), $precision);
    }

    /**
     * @param  float|string|null  ...$amounts
     */
    public static function add(...$amounts): float
    {
        $total = 0.0;

        foreach ($amounts as $amount) {
            $total += (float) ($amount ?? 0);
        }

        return self::round($total);
    }

    public static function subtract(float|string|null $a, float|string|null $b): float
    {
        return self::round((float) ($a ?? 0) - (float) ($b ?? 0));
    }

    public static function multiply(float|string|null $a, float|string|null $b): float
    {
        return self::round((float) ($a ?? 0) * (float) ($b ?? 0));
    }

    public static function percentage(float|string|null $amount, float|string|null $percent): float
    {
        return self::multiply($amount, (float) ($percent ?? 0) / 100);
    }

    public static function equals(float|string|null $a, float|string|null $b, float $tolerance = 0.005): bool
    {
        return abs((float) ($a ?? 0) - (float) ($b ?? 0)) < $tolerance;
    }

    public static function isZero(float|string|null $amount, float $tolerance = 0.005): bool
    {
        return abs((float) ($amount ?? 0)) < $tolerance;
    }

    public static function isPositive(float|string|null $amount): bool
    {
        return (float) ($amount ?? 0) > 0;
    }
}
