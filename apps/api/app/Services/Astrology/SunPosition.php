<?php

namespace App\Services\Astrology;

/**
 * Sun's apparent geocentric ecliptic longitude, via Meeus's low-precision
 * solar position formula ("Astronomical Algorithms" ch. 25) — accurate to
 * about 0.01°, which is standard-issue for approximate chart engines.
 */
class SunPosition
{
    /**
     * @return float Apparent tropical ecliptic longitude in degrees [0, 360).
     */
    public static function apparentLongitude(float $julianDay): float
    {
        $t = JulianDay::centuriesSinceJ2000($julianDay);

        $meanLongitude = AstroMath::normalizeDegrees(280.46646 + 36000.76983 * $t + 0.0003032 * $t ** 2);
        $meanAnomaly = AstroMath::normalizeDegrees(357.52911 + 35999.05029 * $t - 0.0001537 * $t ** 2);

        $center = (1.914602 - 0.004817 * $t - 0.000014 * $t ** 2) * AstroMath::sinDeg($meanAnomaly)
            + (0.019993 - 0.000101 * $t) * AstroMath::sinDeg(2 * $meanAnomaly)
            + 0.000289 * AstroMath::sinDeg(3 * $meanAnomaly);

        $trueLongitude = $meanLongitude + $center;

        // Correction for nutation and aberration (Meeus ch. 25, "lower
        // accuracy" apparent longitude formula).
        $omega = 125.04 - 1934.136 * $t;
        $apparentLongitude = $trueLongitude - 0.00569 - 0.00478 * AstroMath::sinDeg($omega);

        return AstroMath::normalizeDegrees($apparentLongitude);
    }
}
