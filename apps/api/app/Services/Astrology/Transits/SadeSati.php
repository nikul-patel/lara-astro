<?php

namespace App\Services\Astrology\Transits;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Ayanamsa;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\PlanetaryElements;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;

/**
 * Sade Sati ("seven and a half"): the period when transiting Saturn moves
 * through the 12th, 1st, and 2nd signs counted from the natal Moon —
 * roughly 7.5 years total (Saturn's ~29.5-year cycle divided across 3
 * signs) — plus the two "Panoti" minor difficulty periods (Saturn
 * transiting the 4th or 8th sign from natal Moon), one of the most
 * requested standalone dosha-style reports.
 *
 * Computed by a single continuous day-by-day forward sweep of Saturn's
 * sidereal sign across a ~100-year span from birth (fast in practice: well
 * under a second for the whole span, see SadeSatiTest), recording every
 * contiguous sign-occupancy interval exactly once. This fixes a real bug
 * (#66) in the previous design, which searched backward from whatever
 * `reference_date` was given and could land on either side of a Saturn
 * retrograde station, reporting a different cycle_start depending on what
 * date was asked about for the very same real transit. Sweeping forward
 * once from a fixed point (birth) and never re-deriving per-query removes
 * that whole bug class: the same transit always produces the same
 * intervals, regardless of `reference_date`.
 *
 * A genuine, still-present simplification (unlike #66, intentional and
 * documented rather than a bug): a brief retrograde dip back across a sign
 * boundary is reported as its own separate interval rather than merged
 * into the interval before/after it — e.g. Saturn entering the peak sign,
 * retrograding back into the rising sign for a few weeks, then forward
 * into the peak sign again produces two separate "rising" intervals and
 * two separate "peak" intervals rather than one continuous span of each.
 * This matches how at least one major competitor platform (AstroSage)
 * reports the same real chart's Sade Sati table — multiple rows under the
 * same phase label — so it's treated here as the expected presentation,
 * not a defect to paper over.
 */
class SadeSati
{
    private const SPAN_YEARS = 100;

    /** Gap between one Sade Sati interval's end and the next interval's start, above which they're treated as belonging to different ~29.5-year cycles rather than the same retrograde-interrupted one. */
    private const CYCLE_GAP_YEARS = 3;

    /**
     * @param  array{planetary_positions: list<array{name: string, sign: string}>}  $natalChart
     * @return array{
     *     moon_sign: string,
     *     phase: string,
     *     is_active: bool,
     *     cycle_start: ?string,
     *     peak_phase_start: ?string,
     *     setting_phase_start: ?string,
     *     cycle_end: ?string,
     *     reference_date: string,
     *     lifetime: array{
     *         sade_sati: list<array{phase: string, sign: string, start: string, end: string}>,
     *         panoti: list<array{type: string, sign: string, start: string, end: string}>,
     *     },
     * }
     */
    public static function forChart(array $natalChart, CarbonImmutable $birthMoment, CarbonImmutable $referenceDate): array
    {
        $moonSign = collect($natalChart['planetary_positions'])->firstWhere('name', 'Moon')['sign'];
        $moonIndex = array_search($moonSign, ZodiacSigns::NAMES, true);

        $risingIndex = (($moonIndex - 1) + 12) % 12; // 12th from Moon
        $peakIndex = $moonIndex;                      // Moon's own sign
        $settingIndex = ($moonIndex + 1) % 12;         // 2nd from Moon
        $fourthIndex = ($moonIndex + 3) % 12;          // 4th from Moon (Panoti)
        $eighthIndex = ($moonIndex + 7) % 12;          // 8th from Moon (Panoti)

        $signIntervals = self::saturnSignIntervals($birthMoment, self::SPAN_YEARS);

        $sadeSati = [];
        $panoti = [];

        foreach ($signIntervals as $interval) {
            $phase = match ($interval['sign_index']) {
                $risingIndex => 'rising',
                $peakIndex => 'peak',
                $settingIndex => 'setting',
                default => null,
            };

            $sign = ZodiacSigns::NAMES[$interval['sign_index']];

            if ($phase !== null) {
                $sadeSati[] = ['phase' => $phase, 'sign' => $sign, 'start' => $interval['start']->toDateString(), 'end' => $interval['end']->toDateString()];

                continue;
            }

            $panotiType = match ($interval['sign_index']) {
                $fourthIndex => 'fourth_from_moon',
                $eighthIndex => 'eighth_from_moon',
                default => null,
            };

            if ($panotiType !== null) {
                $panoti[] = ['type' => $panotiType, 'sign' => $sign, 'start' => $interval['start']->toDateString(), 'end' => $interval['end']->toDateString()];
            }
        }

        $cycles = self::groupIntoCycles($sadeSati);
        $summary = self::summarizeForReferenceDate($cycles, $referenceDate);

        return [
            'moon_sign' => $moonSign,
            'phase' => $summary['phase'],
            'is_active' => $summary['is_active'],
            'cycle_start' => $summary['cycle_start'],
            'peak_phase_start' => $summary['peak_phase_start'],
            'setting_phase_start' => $summary['setting_phase_start'],
            'cycle_end' => $summary['cycle_end'],
            'reference_date' => $referenceDate->toDateString(),
            'lifetime' => [
                'sade_sati' => $sadeSati,
                'panoti' => $panoti,
            ],
        ];
    }

    /**
     * Groups a chronological list of rising/peak/setting intervals into
     * ~29.5-year cycles, using a gap threshold comfortably larger than any
     * single retrograde-induced gap (observed to be at most a few months)
     * but far smaller than the ~22-year gap between one cycle's end and
     * the next cycle's start.
     *
     * @param  list<array{phase: string, sign: string, start: string, end: string}>  $sadeSatiIntervals
     * @return list<list<array{phase: string, sign: string, start: string, end: string}>>
     */
    private static function groupIntoCycles(array $sadeSatiIntervals): array
    {
        $cycles = [];
        $currentCycle = [];
        $previousEnd = null;

        foreach ($sadeSatiIntervals as $interval) {
            $start = CarbonImmutable::parse($interval['start']);

            if ($previousEnd !== null && $previousEnd->diffInYears($start) >= self::CYCLE_GAP_YEARS) {
                $cycles[] = $currentCycle;
                $currentCycle = [];
            }

            $currentCycle[] = $interval;
            $previousEnd = CarbonImmutable::parse($interval['end']);
        }

        if ($currentCycle !== []) {
            $cycles[] = $currentCycle;
        }

        return $cycles;
    }

    /**
     * @param  list<list<array{phase: string, sign: string, start: string, end: string}>>  $cycles
     * @return array{phase: string, is_active: bool, cycle_start: ?string, peak_phase_start: ?string, setting_phase_start: ?string, cycle_end: ?string}
     */
    private static function summarizeForReferenceDate(array $cycles, CarbonImmutable $referenceDate): array
    {
        foreach ($cycles as $cycle) {
            foreach ($cycle as $interval) {
                $start = CarbonImmutable::parse($interval['start']);
                $end = CarbonImmutable::parse($interval['end']);

                if ($referenceDate->greaterThanOrEqualTo($start) && $referenceDate->lessThan($end)) {
                    return [
                        'phase' => $interval['phase'],
                        'is_active' => true,
                        ...self::cycleBounds($cycle),
                    ];
                }
            }
        }

        // Not currently in any cycle: report the nearest one — the next
        // upcoming cycle if one exists after $referenceDate, else the most
        // recently completed one.
        $next = null;
        $previous = null;

        foreach ($cycles as $cycle) {
            $cycleStart = CarbonImmutable::parse($cycle[0]['start']);

            if ($cycleStart->greaterThan($referenceDate) && $next === null) {
                $next = $cycle;
            }

            if ($cycleStart->lessThanOrEqualTo($referenceDate)) {
                $previous = $cycle;
            }
        }

        $relevantCycle = $next ?? $previous;

        return [
            'phase' => 'none',
            'is_active' => false,
            ...($relevantCycle !== null
                ? self::cycleBounds($relevantCycle)
                : ['cycle_start' => null, 'peak_phase_start' => null, 'setting_phase_start' => null, 'cycle_end' => null]),
        ];
    }

    /**
     * @param  list<array{phase: string, sign: string, start: string, end: string}>  $cycle
     * @return array{cycle_start: string, peak_phase_start: ?string, setting_phase_start: ?string, cycle_end: string}
     */
    private static function cycleBounds(array $cycle): array
    {
        $firstPeak = collect($cycle)->firstWhere('phase', 'peak');
        $firstSetting = collect($cycle)->firstWhere('phase', 'setting');

        return [
            'cycle_start' => $cycle[0]['start'],
            'peak_phase_start' => $firstPeak['start'] ?? null,
            'setting_phase_start' => $firstSetting['start'] ?? null,
            'cycle_end' => end($cycle)['end'],
        ];
    }

    /**
     * Sweeps Saturn's sidereal sign forward day-by-day from $start across
     * $spanYears, returning every contiguous same-sign interval exactly
     * once — the single source of truth every other method here builds
     * from, replacing the old per-query backward search.
     *
     * @return list<array{sign_index: int, start: CarbonImmutable, end: CarbonImmutable}>
     */
    private static function saturnSignIntervals(CarbonImmutable $start, int $spanYears): array
    {
        $end = $start->addRealSeconds($spanYears * 365.25 * 86400);

        $intervals = [];
        $cursor = $start;
        $currentSign = self::saturnSignIndexAt($cursor);
        $intervalStart = $cursor;

        while ($cursor->lessThan($end)) {
            $cursor = $cursor->addDay();
            $sign = self::saturnSignIndexAt($cursor);

            if ($sign !== $currentSign) {
                $intervals[] = ['sign_index' => $currentSign, 'start' => $intervalStart, 'end' => $cursor];
                $currentSign = $sign;
                $intervalStart = $cursor;
            }
        }

        $intervals[] = ['sign_index' => $currentSign, 'start' => $intervalStart, 'end' => $end];

        return $intervals;
    }

    private static function saturnSignIndexAt(CarbonImmutable $date): int
    {
        $julianDay = JulianDay::fromUtc($date->setTime(12, 0)->utc());
        $ayanamsa = Ayanamsa::lahiri($julianDay);
        $longitude = AstroMath::normalizeDegrees(PlanetaryElements::geocentricLongitude('saturn', $julianDay) - $ayanamsa);

        return (int) floor($longitude / 30);
    }
}
