<?php

namespace App\Services\Astrology\Houses;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Houses;

/**
 * Real Placidus house cusps — a genuinely different calculation from
 * every other house-related class in this codebase ({@see
 * \App\Services\Astrology\Houses::wholeSignHouses()}, used by every
 * other feature), kept in its own file precisely so it can't
 * accidentally regress the existing, already-shipped whole-sign chart.
 *
 * PRECISION DECISION (per issue #80's explicit flag — "pick one
 * explicitly rather than silently presenting approximate Placidus
 * cusps as precise"): this implements REAL Placidus — not a
 * simplified stand-in — on top of this engine's existing approximate
 * Sun/Moon/planetary longitude series (Meeus/Standish, documented in
 * BirthChartCalculator's class docblock). The user explicitly chose
 * this scope ("Everything, including Placidus cusps / KP / Lal Kitab")
 * when the Phase 4 batch was planned. That means Placidus cusp degrees
 * here carry this engine's existing sign/house-level precision
 * approximation PLUS the cusp formula's own geometry — compounded
 * approximation, same honesty standard as every other precision note
 * in this codebase, not arc-second accuracy. Known to perform
 * irregularly above +66/-66 latitude (inside the Placidus "polar
 * zone" where the semi-arc trisection becomes geometrically
 * impossible for some cusps) — out of scope, same as this engine's
 * existing sunrise/sunset polar-circle handling.
 *
 * Algorithm — the classical semi-arc trisection method, solved by
 * genuine fixed-point iteration (not a closed-form shortcut), derived
 * directly from Placidus's own defining equation: cusp 11/12 (and by
 * the 180°-opposite relation, 5/6) are the ecliptic points whose
 * right ascension α satisfies
 *   α = (RAMC + 90°·f) + f·AD(α)
 * where f is 1/3 for cusp 11 and 2/3 for cusp 12, and AD is the point's
 * ascensional difference asin(tan(latitude)·tan(declination)) — the
 * extra right-ascension "head start" a point's own declination gives
 * or costs it versus a point on the celestial equator. Houses 2/3 (and
 * 8/9) use the same equation measured from the Ascendant/RAMC+180°
 * side instead. This equation is transcendental (AD depends on the
 * ecliptic longitude being solved for), which is exactly why classical
 * sources describe Placidus as needing iteration — solved here by
 * straightforward fixed-point iteration seeded with AD=0 (equivalent
 * to the Equal-House-at-the-equator approximation) and refined until
 * the ecliptic longitude stops moving by more than 1e-6°, converging
 * in under 10 steps for any latitude this engine supports.
 *
 * Cross-checked against the MIT-licensed CircularNatalHoroscopeJS
 * library's independent Placidus implementation
 * (https://github.com/0xStarcat/CircularNatalHoroscopeJS/blob/master/src/utilities/astrology.js,
 * itself citing "An Astrological House Formulary" by Michael P.
 * Munkasey, p.18) — its closed-form trigonometric substitution and
 * this class's direct iteration agree to within a few arcminutes on
 * every case tried. A specific textbook worked numerical example was
 * also sought (common practice in this codebase's other classical-
 * algorithm citations) but couldn't be responsibly used here: the only
 * source found (astrologerdsbaquila.com's "Placidus House Cusps
 * Calculation - Worked Example") was unreachable in this environment,
 * and a secondhand web-search summary of its cited cusp values didn't
 * hold up against independent verification — one of its two numbers
 * was off by double digits of degrees from both methods above, too
 * large a gap to be rounding or a differing obliquity convention. That
 * made it unsafe to cite as a reference figure, so this class relies
 * on the two independently-agreeing calculation methods above instead
 * of an unverifiable secondhand number.
 */
class PlacidusCusps
{
    /** RAMC offset (degrees, = 90°·f) and semi-arc fraction f for each intermediate cusp — 1 and 10 are the Ascendant/Midheaven themselves, not computed this way. */
    private const INTERMEDIATE_CUSPS = [
        11 => ['offset' => 30, 'fraction' => 1 / 3],
        12 => ['offset' => 60, 'fraction' => 2 / 3],
        2 => ['offset' => 120, 'fraction' => 2 / 3],
        3 => ['offset' => 150, 'fraction' => 1 / 3],
    ];

    private const CONVERGENCE_TOLERANCE = 1e-6;

    private const MAX_ITERATIONS = 20;

    /**
     * @return array<int, float> House number (1-12) => ecliptic longitude in degrees [0, 360).
     */
    public static function calculate(float $julianDay, float $latitude, float $longitude): array
    {
        $ramc = AstroMath::normalizeDegrees(Houses::greenwichSiderealTime($julianDay) + $longitude);
        $obliquity = Houses::obliquity($julianDay);
        $ascendant = Houses::ascendant($julianDay, $latitude, $longitude);
        $midheaven = self::rightAscensionToLongitude($ramc, $obliquity);

        $cusps = [1 => $ascendant, 10 => $midheaven];

        foreach (self::INTERMEDIATE_CUSPS as $house => $config) {
            $cusps[$house] = self::intermediateCusp($ramc + $config['offset'], $config['fraction'], $latitude, $obliquity);
        }

        // Every opposite house is exactly 180 degrees away — true for
        // any house system built from an axis pair (ASC/DESC, MC/IC)
        // plus 4 intermediate cusps per quadrant, not just Placidus.
        $cusps[4] = AstroMath::normalizeDegrees($midheaven + 180);
        $cusps[7] = AstroMath::normalizeDegrees($ascendant + 180);
        $cusps[5] = AstroMath::normalizeDegrees($cusps[11] + 180);
        $cusps[6] = AstroMath::normalizeDegrees($cusps[12] + 180);
        $cusps[8] = AstroMath::normalizeDegrees($cusps[2] + 180);
        $cusps[9] = AstroMath::normalizeDegrees($cusps[3] + 180);

        ksort($cusps);

        return $cusps;
    }

    /**
     * Fixed-point iteration for Placidus's defining equation — see
     * class docblock. $baseRightAscension is RAMC + 90°·fraction (the
     * zeroth-order guess, ignoring ascensional difference).
     */
    private static function intermediateCusp(float $baseRightAscension, float $fraction, float $latitude, float $obliquity): float
    {
        $longitude = self::rightAscensionToLongitude($baseRightAscension, $obliquity);

        for ($i = 0; $i < self::MAX_ITERATIONS; $i++) {
            $declination = AstroMath::atan2Deg(
                AstroMath::sinDeg($obliquity) * AstroMath::sinDeg($longitude),
                sqrt(1 - (AstroMath::sinDeg($obliquity) * AstroMath::sinDeg($longitude)) ** 2)
            );
            $ascensionalDifference = AstroMath::atanDeg(
                AstroMath::tanDeg($latitude) * AstroMath::tanDeg($declination)
                / sqrt(1 - (AstroMath::tanDeg($latitude) * AstroMath::tanDeg($declination)) ** 2)
            );

            $rightAscension = $baseRightAscension + $fraction * $ascensionalDifference;
            $nextLongitude = self::rightAscensionToLongitude($rightAscension, $obliquity);

            if (abs(AstroMath::normalizeDegrees($nextLongitude - $longitude + 180) - 180) < self::CONVERGENCE_TOLERANCE) {
                return $nextLongitude;
            }

            $longitude = $nextLongitude;
        }

        return $longitude;
    }

    /**
     * Ecliptic longitude of the point with zero ecliptic latitude and
     * the given right ascension — the same relation
     * {@see Houses::ascendant()} and this
     * class's Midheaven both rest on, generalized to any right
     * ascension rather than just RAMC.
     */
    private static function rightAscensionToLongitude(float $rightAscension, float $obliquity): float
    {
        return AstroMath::normalizeDegrees(
            AstroMath::atan2Deg(AstroMath::sinDeg($rightAscension), AstroMath::cosDeg($rightAscension) * AstroMath::cosDeg($obliquity))
        );
    }
}
