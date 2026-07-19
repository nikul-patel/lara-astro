<?php

namespace App\Services\Astrology;

use Carbon\CarbonImmutable;

/**
 * Julian Day conversion (Meeus, "Astronomical Algorithms" ch. 7). Every
 * calculation in this namespace works in Universal Time — the sub-minute
 * difference between UT and Terrestrial Dynamical Time (~70s in the modern
 * era) is well within this engine's stated low-precision tolerance (see
 * BirthChartCalculator's docblock).
 */
class JulianDay
{
    /**
     * @param  CarbonImmutable  $utc  A date/time already converted to UTC.
     */
    public static function fromUtc(CarbonImmutable $utc): float
    {
        $year = (int) $utc->format('Y');
        $month = (int) $utc->format('n');
        $day = (int) $utc->format('j')
            + ((int) $utc->format('G')) / 24
            + ((int) $utc->format('i')) / 1440
            + ((int) $utc->format('s')) / 86400;

        if ($month <= 2) {
            $year--;
            $month += 12;
        }

        $a = intdiv($year, 100);
        $b = 2 - $a + intdiv($a, 4);

        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $b - 1524.5;
    }

    /**
     * Julian centuries since the J2000.0 epoch (JD 2451545.0) — the time
     * variable ("T") used throughout Meeus's and Standish's formulas.
     */
    public static function centuriesSinceJ2000(float $julianDay): float
    {
        return ($julianDay - 2451545.0) / 36525;
    }
}
