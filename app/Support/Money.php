<?php

namespace App\Support;

class Money
{
    public static function abbreviate(string $amount): string
    {
        $value = (float) $amount;
        $sign = $value < 0 ? '-' : '';
        $value = abs($value);

        return match (true) {
            $value >= 1_000_000_000 => $sign.number_format($value / 1_000_000_000, 1).'B',
            $value >= 1_000_000 => $sign.number_format($value / 1_000_000, 1).'M',
            default => $sign.number_format($value, 0),
        };
    }
}
