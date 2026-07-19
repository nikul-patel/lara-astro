<?php

namespace App\Services\Astrology;

/**
 * Moon's apparent geocentric ecliptic longitude, via the largest periodic
 * terms of Meeus's lunar theory ("Astronomical Algorithms" ch. 47, ELP-2000
 * derived). The full series has dozens of periodic terms; keeping only the
 * six largest (as here) is a standard truncation for "quick" lunar
 * ephemerides and stays within roughly a quarter of a degree of the full
 * series — enough to place the Moon in the right sign for chart display,
 * though a birth time within a few minutes of a sign boundary could
 * occasionally land on the wrong side of it.
 */
class MoonPosition
{
    /**
     * @return float Apparent tropical ecliptic longitude in degrees [0, 360).
     */
    public static function apparentLongitude(float $julianDay): float
    {
        $t = JulianDay::centuriesSinceJ2000($julianDay);

        $meanLongitude = AstroMath::normalizeDegrees(
            218.3164477 + 481267.88123421 * $t - 0.0015786 * $t ** 2 + $t ** 3 / 538841 - $t ** 4 / 65194000
        );
        $elongation = AstroMath::normalizeDegrees(
            297.8501921 + 445267.1114034 * $t - 0.0018819 * $t ** 2 + $t ** 3 / 545868 - $t ** 4 / 113065000
        );
        $sunAnomaly = AstroMath::normalizeDegrees(
            357.5291092 + 35999.0502909 * $t - 0.0001536 * $t ** 2 + $t ** 3 / 24490000
        );
        $moonAnomaly = AstroMath::normalizeDegrees(
            134.9633964 + 477198.8675055 * $t + 0.0087414 * $t ** 2 + $t ** 3 / 69699 - $t ** 4 / 14712000
        );
        $argumentOfLatitude = AstroMath::normalizeDegrees(
            93.2720950 + 483202.0175233 * $t - 0.0036539 * $t ** 2 - $t ** 3 / 3526000 + $t ** 4 / 863310000
        );

        $correction = 6.288774 * AstroMath::sinDeg($moonAnomaly)
            + 1.274027 * AstroMath::sinDeg(2 * $elongation - $moonAnomaly)
            + 0.658314 * AstroMath::sinDeg(2 * $elongation)
            + 0.213618 * AstroMath::sinDeg(2 * $moonAnomaly)
            - 0.185116 * AstroMath::sinDeg($sunAnomaly)
            - 0.114332 * AstroMath::sinDeg(2 * $argumentOfLatitude);

        return AstroMath::normalizeDegrees($meanLongitude + $correction);
    }
}
