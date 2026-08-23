<?php

namespace App\Services\Astrology;

use Carbon\CarbonImmutable;

/**
 * Vimshottari Mahadasha/Antardasha timeline — the standard Vedic planetary
 * period system used to time life events. A fixed 120-year cycle is
 * divided among the 9 grahas in fixed proportions and a fixed order (see
 * Nakshatra::LORD_CYCLE); which lord governs at birth, and how much of
 * that lord's full period remains, is determined entirely by where the
 * Moon sits within its nakshatra at birth (Nakshatra::fractionElapsed()).
 *
 * Each level nests the same way: an Antardasha's length is its parent
 * Mahadasha's length scaled by the Antardasha lord's share of 120 years,
 * and a Pratyantardasha's length is its parent Antardasha's length scaled
 * the same way — self-similar at every level.
 *
 * A calendar year is approximated as 365.25 days throughout, consistent
 * with this namespace's existing precision posture (see
 * BirthChartCalculator's class docblock) — dasha transition dates can
 * drift by up to roughly a day per decade from a table computed with the
 * true (slightly shorter) sidereal year.
 */
class VimshottariDasha
{
    private const CYCLE_YEARS = 120;

    private const DAYS_PER_YEAR = 365.25;

    public const LORD_YEARS = [
        'Ketu' => 7, 'Venus' => 20, 'Sun' => 6, 'Moon' => 10, 'Mars' => 7,
        'Rahu' => 18, 'Jupiter' => 16, 'Saturn' => 19, 'Mercury' => 17,
    ];

    /**
     * The full Mahadasha timeline: 9 periods covering one pass through the
     * cycle from birth (the first, birth-lord period truncated to its
     * remaining balance; the rest at full length), each with its nested
     * Antardashas. Pratyantardashas are deliberately not included — 729
     * leaf periods is more than any caller needs eagerly; see
     * pratyantardashas() to compute just the currently-relevant window.
     *
     * @return list<array{lord: string, start: string, end: string, antardashas: list<array{lord: string, start: string, end: string}>}>
     */
    public static function timeline(float $moonSiderealLongitude, CarbonImmutable $birthMoment): array
    {
        $nakshatra = Nakshatra::forLongitude($moonSiderealLongitude);
        $fractionElapsed = Nakshatra::fractionElapsed($moonSiderealLongitude);

        $lords = Nakshatra::LORD_CYCLE;
        $startIndex = array_search($nakshatra['lord'], $lords, true);

        $mahadashas = [];
        $cursor = $birthMoment;

        for ($i = 0; $i < 9; $i++) {
            $lord = $lords[($startIndex + $i) % 9];
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
     * Pratyantardashas within a single Antardasha, computed on demand
     * rather than as part of timeline()'s output (see class docblock).
     *
     * @return list<array{lord: string, start: string, end: string}>
     */
    public static function pratyantardashas(string $antardashaLord, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return self::subPeriods($antardashaLord, $start, $end);
    }

    /**
     * Divides a parent period [$start, $end] into 9 sub-periods cycling
     * the same fixed 9-lord order starting from the parent's own lord,
     * each sized as the parent's span scaled by the sub-lord's share of
     * the 120-year cycle. Used for both Antardasha-within-Mahadasha and
     * Pratyantardasha-within-Antardasha — the nesting rule is identical at
     * every level.
     *
     * @return list<array{lord: string, start: string, end: string}>
     */
    private static function subPeriods(string $parentLord, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $parentDays = $start->diffInSeconds($end) / 86400;
        $lords = Nakshatra::LORD_CYCLE;
        $startIndex = array_search($parentLord, $lords, true);

        $periods = [];
        $cursor = $start;

        for ($i = 0; $i < 9; $i++) {
            $lord = $lords[($startIndex + $i) % 9];
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

        // Floating-point accumulation across 9 additions can drift the
        // last sub-period's end a fraction of a day from the parent's own
        // end; pin it exactly so nested periods never appear to overrun
        // their parent.
        $periods[8]['end'] = $end->toDateString();

        return $periods;
    }

    private static function addYears(CarbonImmutable $moment, float $years): CarbonImmutable
    {
        return $moment->addRealSeconds($years * self::DAYS_PER_YEAR * 86400);
    }
}
