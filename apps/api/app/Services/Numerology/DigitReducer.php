<?php

namespace App\Services\Numerology;

/**
 * Reduces a sum to a single digit (1-9) by repeatedly summing its digits —
 * except Master Numbers 11, 22, and 33, which every mainstream numerology
 * system leaves un-reduced whenever they appear as an intermediate sum
 * (not just the final one). This is the one reduction rule shared by every
 * number type in this namespace, so it's centralized here.
 */
class DigitReducer
{
    private const MASTER_NUMBERS = [11, 22, 33];

    public static function reduce(int $number): int
    {
        while ($number > 9 && ! in_array($number, self::MASTER_NUMBERS, true)) {
            $number = array_sum(str_split((string) $number));
        }

        return $number;
    }
}
