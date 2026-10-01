<?php

namespace App\Services\Astrology\Predictions;

/**
 * English ordinal suffixing ("1st", "2nd", "3rd", "4th", ..., "11th",
 * "12th", "13th", "21st", ...) — every house-number interpolation in this
 * namespace's templates needs this (a bare "{house}th house" renders
 * "1th house" for house 1, "2th house" for house 2, etc.), so it's a
 * single shared helper rather than each predictor reimplementing it.
 */
class Ordinal
{
    public static function suffix(int $number): string
    {
        // 11-13 are always "th", regardless of what the last digit alone
        // would suggest (not "11st"/"12nd"/"13rd") — the classical English
        // exception to the otherwise last-digit-driven rule below.
        if (in_array($number % 100, [11, 12, 13], true)) {
            return "{$number}th";
        }

        $suffix = match ($number % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };

        return "{$number}{$suffix}";
    }
}
