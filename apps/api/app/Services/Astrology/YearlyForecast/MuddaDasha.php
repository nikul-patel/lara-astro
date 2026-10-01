<?php

namespace App\Services\Astrology\YearlyForecast;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\Nakshatra;
use App\Services\Astrology\VimshottariDasha;
use Carbon\CarbonImmutable;

/**
 * Tajika Mudda Dasha: the Varshaphal (annual chart) equivalent of natal
 * Vimshottari — the exact same balance-at-birth proportional-subdivision
 * math (Nakshatra::fractionElapsed() on the return chart's own Moon,
 * same 9-lord cycle and the same classical year-shares, see
 * VimshottariDasha::LORD_YEARS), just rescaled from a 120-year cycle down
 * to the solar return's single year. Treats the return moment like a
 * "mini birth" for dasha purposes — the same self-similar-subdivision
 * principle already used across this engine (compare
 * VimshottariDasha::subPeriods() and KP\SubLord::boundaries()).
 */
class MuddaDasha
{
    private const CYCLE_DAYS = 365.25;

    /**
     * Unlike VimshottariDasha's date-only periods (fine over a 120-year
     * cycle), start/end here keep full timestamp precision: Mudda Dasha's
     * whole cycle is a single year, so its shortest period (Sun, ~18
     * days) is short enough that date-only rounding would be a
     * meaningfully large relative error.
     *
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $returnHouses
     * @return list<array{lord: string, start: string, end: string, house: int}>
     */
    public static function timeline(float $returnMoonSiderealLongitude, CarbonImmutable $returnMoment, array $returnHouses): array
    {
        $nakshatra = Nakshatra::forLongitude($returnMoonSiderealLongitude);
        $fractionElapsed = Nakshatra::fractionElapsed($returnMoonSiderealLongitude);

        $lords = Nakshatra::LORD_CYCLE;
        $startIndex = array_search($nakshatra['lord'], $lords, true);

        $periods = [];
        $cursor = $returnMoment;

        for ($i = 0; $i < 9; $i++) {
            $lord = $lords[($startIndex + $i) % 9];
            $fullDays = self::CYCLE_DAYS * VimshottariDasha::LORD_YEARS[$lord] / 120;
            $days = $i === 0 ? $fullDays * (1 - $fractionElapsed) : $fullDays;

            $start = $cursor;
            $end = $cursor->addRealSeconds($days * 86400);

            $periods[] = [
                'lord' => $lord,
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
                'house' => HouseLords::houseContainingPlanet($lord, $returnHouses),
            ];

            $cursor = $end;
        }

        return $periods;
    }
}
