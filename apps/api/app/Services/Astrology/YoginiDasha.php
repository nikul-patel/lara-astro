<?php

namespace App\Services\Astrology;

use Carbon\CarbonImmutable;
use Tests\Unit\Astrology\YoginiDashaTest;

/**
 * Yogini Dasha: a second, independent dasha system (alongside Vimshottari)
 * built on 8 Yoginis in a fixed cyclic order with fixed period lengths
 * (1 through 8 years, an 8-period/36-year cycle), rather than Vimshottari's
 * 9 nakshatra-lords/120-year cycle. The starting Yogini is found from the
 * Moon's nakshatra number via the classical `(nakshatra number + 3) mod 8`
 * rule (result 0 treated as 8); balance-at-birth and the proportional
 * Mahadasha/Antardasha subdivision otherwise mirror VimshottariDasha's
 * conventions exactly (see that class's docblock) for consistency within
 * this codebase.
 *
 * Unlike Vimshottari's 120-year single pass (already far longer than a
 * human lifespan), one 36-year Yogini cycle is not — this timeline()
 * repeats the 8-Yogini cycle for the requested span rather than stopping
 * after one pass, matching how competitor reports present it as a genuine
 * full-life timeline.
 *
 * Starting-lord formula cross-checked against a real third-party report
 * (not just this class's own unit tests): a chart with Moon in Anuradha
 * (nakshatra #17) begins its Yogini Dasha in Bhramari (#4) in that report,
 * which is exactly what `(17 + 3) mod 8 = 4` produces — see
 * {@see YoginiDashaTest} for the encoded version of
 * this check.
 */
class YoginiDasha
{
    private const CYCLE_YEARS = 36;

    private const DAYS_PER_YEAR = 365.25;

    /** Fixed cyclic order and each Yogini's period length in years (sums to 36). */
    public const LORD_YEARS = [
        'Mangala' => 1, 'Pingala' => 2, 'Dhanya' => 3, 'Bhramari' => 4,
        'Bhadrika' => 5, 'Ulka' => 6, 'Siddha' => 7, 'Sankata' => 8,
    ];

    public static function startingLord(int $nakshatraNumber): string
    {
        $lords = array_keys(self::LORD_YEARS);
        $position = $nakshatraNumber + 3;
        $index = $position % 8;

        if ($index === 0) {
            $index = 8;
        }

        return $lords[$index - 1];
    }

    /**
     * @return list<array{lord: string, start: string, end: string, antardashas: list<array{lord: string, start: string, end: string}>}>
     */
    public static function timeline(float $moonSiderealLongitude, CarbonImmutable $birthMoment, int $spanYears = 100): array
    {
        $nakshatra = Nakshatra::forLongitude($moonSiderealLongitude);
        $fractionElapsed = Nakshatra::fractionElapsed($moonSiderealLongitude);

        $lords = array_keys(self::LORD_YEARS);
        $startIndex = array_search(self::startingLord($nakshatra['index'] + 1), $lords, true);

        $mahadashas = [];
        $cursor = $birthMoment;
        $spanEnd = self::addYears($birthMoment, $spanYears);

        for ($i = 0; $cursor->lessThan($spanEnd); $i++) {
            $lord = $lords[($startIndex + $i) % 8];
            $fullYears = self::LORD_YEARS[$lord];
            $years = $i === 0 ? $fullYears * (1 - $fractionElapsed) : $fullYears;

            $start = $cursor;
            $end = self::addYears($cursor, $years);

            $mahadashas[] = [
                'lord' => $lord,
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'antardashas' => self::subPeriods($lord, $start, $end),
            ];

            $cursor = $end;
        }

        return $mahadashas;
    }

    /**
     * @return list<array{lord: string, start: string, end: string}>
     */
    private static function subPeriods(string $parentLord, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $parentDays = $start->diffInSeconds($end) / 86400;
        $lords = array_keys(self::LORD_YEARS);
        $startIndex = array_search($parentLord, $lords, true);

        $periods = [];
        $cursor = $start;

        for ($i = 0; $i < 8; $i++) {
            $lord = $lords[($startIndex + $i) % 8];
            $days = $parentDays * self::LORD_YEARS[$lord] / self::CYCLE_YEARS;

            $periodStart = $cursor;
            $periodEnd = $cursor->addRealSeconds($days * 86400);

            $periods[] = [
                'lord' => $lord,
                'start' => $periodStart->toDateString(),
                'end' => $periodEnd->toDateString(),
            ];

            $cursor = $periodEnd;
        }

        // Pin the last sub-period's end exactly to the parent's end, same
        // floating-point-drift guard as VimshottariDasha::subPeriods().
        $periods[7]['end'] = $end->toDateString();

        return $periods;
    }

    private static function addYears(CarbonImmutable $moment, float $years): CarbonImmutable
    {
        return $moment->addRealSeconds($years * self::DAYS_PER_YEAR * 86400);
    }
}
