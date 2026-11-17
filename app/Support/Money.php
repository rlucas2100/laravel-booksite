<?php

namespace App\Support;

/** Prices are kept as whole pence, so sums never pick up rounding errors. */
class Money
{
    /** 299 becomes "£2.99". */
    public static function pounds(int $pence): string
    {
        return '£'.number_format($pence / 100, 2);
    }

    /** "2.99" (as typed in a form) becomes 299. Empty or zero means free: null. */
    public static function pence(mixed $pounds): ?int
    {
        if ($pounds === null || $pounds === '') {
            return null;
        }

        $pence = (int) round(((float) $pounds) * 100);

        return $pence > 0 ? $pence : null;
    }
}
