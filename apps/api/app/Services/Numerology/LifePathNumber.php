<?php

namespace App\Services\Numerology;

use Carbon\CarbonImmutable;

/**
 * The Life Path Number: numerology's equivalent of a Sun sign, derived
 * purely from the birth date. The standard method sums the date's digits
 * as three separately-reduced parts (day, month, year) before combining —
 * not one flat sum of every digit — because a flat sum can miss a Master
 * Number hiding inside, e.g. a birth year of 1992 (1+9+9+2=21→3) versus
 * reducing 1992 itself first. Both conventions exist across numerology
 * schools; this is the more common one used by most mainstream calculators.
 */
class LifePathNumber
{
    public static function forDate(CarbonImmutable $dob): int
    {
        $day = DigitReducer::reduce($dob->day);
        $month = DigitReducer::reduce($dob->month);
        $year = DigitReducer::reduce(array_sum(str_split((string) $dob->year)));

        return DigitReducer::reduce($day + $month + $year);
    }
}
