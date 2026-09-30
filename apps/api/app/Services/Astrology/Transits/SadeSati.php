<?php

namespace App\Services\Astrology\Transits;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Ayanamsa;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\PlanetaryElements;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;
use RuntimeException;

/**
 * Sade Sati ("seven and a half"): the period when transiting Saturn moves
 * through the 12th, 1st, and 2nd signs counted from the natal Moon —
 * roughly 7.5 years total (Saturn's ~29.5-year cycle divided across 3
 * signs), one of the most requested standalone dosha-style reports.
 *
 * Unlike YearlyForecast\TransitForecast's single mid-year snapshot, this
 * needs the actual sign-ingress dates, found here by day-stepping Saturn's
 * sidereal longitude forward/backward from a reference date until it
 * crosses a sign boundary — the same "good enough for a civil-date result,
 * not arc-second/exact-instant precision" standard as the rest of this
 * engine, and it does not account for retrograde stations causing Saturn
 * to cross a boundary more than once in quick succession (it reports the
 * first crossing found, not every crossing) — a stated simplification.
 */
class SadeSati
{
    private const SIGN_SEARCH_MAX_DAYS = 1500; // ~4.1 years: bounds a single-sign transit search

    private const NEXT_CYCLE_SEARCH_MAX_DAYS = 32 * 365; // one full Saturn cycle (~29.5y) plus margin

    /**
     * @param  array{planetary_positions: list<array{name: string, sign: string}>}  $natalChart
     * @return array{
     *     moon_sign: string,
     *     phase: string,
     *     is_active: bool,
     *     cycle_start: string,
     *     peak_phase_start: string,
     *     setting_phase_start: string,
     *     cycle_end: string,
     * }
     */
    public static function forChart(array $natalChart, CarbonImmutable $referenceDate): array
    {
        $moonSign = collect($natalChart['planetary_positions'])->firstWhere('name', 'Moon')['sign'];
        $moonIndex = array_search($moonSign, ZodiacSigns::NAMES, true);

        $risingIndex = (($moonIndex - 1) + 12) % 12; // 12th from Moon: "rising" phase
        $peakIndex = $moonIndex;                      // Moon's own sign: "peak" phase
        $settingIndex = ($moonIndex + 1) % 12;         // 2nd from Moon: "setting" phase
        $exitIndex = ($moonIndex + 2) % 12;            // 3rd from Moon: the cycle's end

        $currentSignIndex = self::saturnSignIndexAt($referenceDate);

        $phase = match ($currentSignIndex) {
            $risingIndex => 'rising',
            $peakIndex => 'peak',
            $settingIndex => 'setting',
            default => 'none',
        };

        if ($phase === 'none') {
            $cycleStart = self::findCrossing($risingIndex, $referenceDate, forward: true, maxDays: self::NEXT_CYCLE_SEARCH_MAX_DAYS);
        } else {
            $cycleStart = self::findCrossing($currentSignIndex, $referenceDate, forward: false, maxDays: self::SIGN_SEARCH_MAX_DAYS);

            // Walk backward one sign at a time until we reach the rising
            // phase's own entry — 0, 1, or 2 hops depending on whether we're
            // currently in the rising, peak, or setting phase.
            for ($walkBackIndex = $currentSignIndex; $walkBackIndex !== $risingIndex; $walkBackIndex = (($walkBackIndex - 1) + 12) % 12) {
                $precedingIndex = (($walkBackIndex - 1) + 12) % 12;
                $cycleStart = self::findCrossing($precedingIndex, $cycleStart->subDay(), forward: false, maxDays: self::SIGN_SEARCH_MAX_DAYS);
            }
        }

        $peakStart = self::findCrossing($peakIndex, $cycleStart, forward: true, maxDays: self::SIGN_SEARCH_MAX_DAYS);
        $settingStart = self::findCrossing($settingIndex, $peakStart, forward: true, maxDays: self::SIGN_SEARCH_MAX_DAYS);
        $cycleEnd = self::findCrossing($exitIndex, $settingStart, forward: true, maxDays: self::SIGN_SEARCH_MAX_DAYS);

        return [
            'moon_sign' => $moonSign,
            'phase' => $phase,
            'is_active' => $phase !== 'none',
            'cycle_start' => $cycleStart->toDateString(),
            'peak_phase_start' => $peakStart->toDateString(),
            'setting_phase_start' => $settingStart->toDateString(),
            'cycle_end' => $cycleEnd->toDateString(),
        ];
    }

    private static function saturnSignIndexAt(CarbonImmutable $date): int
    {
        $julianDay = JulianDay::fromUtc($date->setTime(12, 0)->utc());
        $ayanamsa = Ayanamsa::lahiri($julianDay);
        $longitude = AstroMath::normalizeDegrees(PlanetaryElements::geocentricLongitude('saturn', $julianDay) - $ayanamsa);

        return (int) floor($longitude / 30);
    }

    /**
     * Finds the first date (searching forward or backward day-by-day from
     * $searchStart) where transiting Saturn's sidereal sign becomes/stops
     * being $targetSignIndex — i.e. the boundary crossing into that sign.
     */
    private static function findCrossing(int $targetSignIndex, CarbonImmutable $searchStart, bool $forward, int $maxDays): CarbonImmutable
    {
        $date = $searchStart;
        $previousSign = self::saturnSignIndexAt($date);

        for ($i = 0; $i < $maxDays; $i++) {
            $date = $forward ? $date->addDay() : $date->subDay();
            $sign = self::saturnSignIndexAt($date);

            if ($forward && $sign === $targetSignIndex && $previousSign !== $targetSignIndex) {
                return $date;
            }

            if (! $forward && $previousSign === $targetSignIndex && $sign !== $targetSignIndex) {
                return $date->addDay();
            }

            $previousSign = $sign;
        }

        throw new RuntimeException("Could not locate Saturn's crossing into sign index {$targetSignIndex} within {$maxDays} days of the search start.");
    }
}
