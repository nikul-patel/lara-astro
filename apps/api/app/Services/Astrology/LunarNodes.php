<?php

namespace App\Services\Astrology;

/**
 * Rahu (mean ascending lunar node) and Ketu (its opposite point, always
 * exactly 180° away) — the two "shadow planets" of Vedic astrology.
 * Formula: Meeus ch. 47's Ω term, the same mean-node longitude used in
 * nutation calculations.
 */
class LunarNodes
{
    /**
     * @return float Rahu's tropical ecliptic longitude in degrees [0, 360).
     */
    public static function rahuLongitude(float $julianDay): float
    {
        $t = JulianDay::centuriesSinceJ2000($julianDay);

        return AstroMath::normalizeDegrees(125.04452 - 1934.136261 * $t + 0.0020708 * $t ** 2 + $t ** 3 / 450000);
    }

    public static function ketuLongitude(float $julianDay): float
    {
        return AstroMath::normalizeDegrees(self::rahuLongitude($julianDay) + 180);
    }
}
