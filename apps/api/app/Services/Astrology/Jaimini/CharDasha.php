<?php

namespace App\Services\Astrology\Jaimini;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;

/**
 * Jaimini Chara Dasha ("Rashi Dasha"): a sign-based dasha system,
 * completely independent of Vimshottari/Yogini's nakshatra-based
 * approach. The sequence of 12 signs runs forward from the Ascendant if
 * the Ascendant is an odd sign, backward if even; each sign's own period
 * length (1-12 years) comes from counting from that sign to its lord's
 * sign — forward for an odd sign, backward for an even one.
 *
 * Documented scope: this implements the BASE duration rule only. Several
 * published variants add a correction (e.g. an extra year, or counting
 * differently) when a sign's lord sits in a Kendra — 1st/4th/7th/10th —
 * from that sign, including the degenerate case where a sign's lord
 * occupies the sign itself (base rule gives a 1-year period here, which
 * some texts instead treat as a special full 12-year case). That
 * refinement is NOT implemented — the same "documented simplification,
 * not exhaustive classical coverage" scope as the rest of this engine.
 */
class CharDasha
{
    private const DAYS_PER_YEAR = 365.25;

    /**
     * @param  array<string, string>  $planetSigns  Planet name => sign, must include the 7 classical planets.
     * @return list<array{sign: string, years: int, start: string, end: string}>
     */
    public static function timeline(string $lagnaSign, array $planetSigns, CarbonImmutable $birthMoment): array
    {
        $signs = ZodiacSigns::NAMES;
        $lagnaIndex = array_search($lagnaSign, $signs, true);
        $forward = self::isOddSign($lagnaSign);

        $periods = [];
        $cursor = $birthMoment;

        for ($i = 0; $i < 12; $i++) {
            $signIndex = $forward
                ? ($lagnaIndex + $i) % 12
                : ((($lagnaIndex - $i) % 12) + 12) % 12; // PHP's % can return negative, so re-normalize into [0, 12)

            $sign = $signs[$signIndex];
            $years = self::durationYears($sign, $planetSigns);
            $end = $cursor->addRealSeconds($years * self::DAYS_PER_YEAR * 86400);

            $periods[] = [
                'sign' => $sign,
                'years' => $years,
                'start' => $cursor->toDateString(),
                'end' => $end->toDateString(),
            ];

            $cursor = $end;
        }

        return $periods;
    }

    private static function durationYears(string $sign, array $planetSigns): int
    {
        $lord = HouseLords::SIGN_RULERS[$sign];
        $lordSign = $planetSigns[$lord];

        return self::isOddSign($sign)
            ? ZodiacSigns::offset($sign, $lordSign)
            : ZodiacSigns::offset($lordSign, $sign);
    }

    /** Classical "odd sign" (1st, 3rd, 5th... in 1-indexed terms) — Aries(index 0) is odd. */
    private static function isOddSign(string $sign): bool
    {
        return array_search($sign, ZodiacSigns::NAMES, true) % 2 === 0;
    }
}
