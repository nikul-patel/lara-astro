<?php

namespace App\Services\Astrology;

/**
 * Mercury, Venus, Mars, Jupiter, Saturn (and Earth, needed as the observer's
 * position) via mean Keplerian orbital elements — Standish's "Keplerian
 * Elements for Approximate Positions of the Major Planets" (JPL Solar
 * System Dynamics group), valid 1800–2050. This is the standard
 * approximate-ephemeris technique when a full VSOP87/DE-series
 * implementation isn't available: solve each planet's two-body orbit, then
 * take the geocentric vector difference with Earth's.
 *
 * Accuracy per Standish's own documentation is sub-arcminute for the inner
 * planets and a few arcminutes for the outer ones over this date range —
 * short of Swiss Ephemeris precision, but enough to place a planet in its
 * correct zodiac sign for chart display, which is this engine's scope.
 *
 * Elements: a = semi-major axis (AU), e = eccentricity, i = inclination,
 * L = mean longitude, varpi = longitude of perihelion, Omega = longitude
 * of ascending node — each given at J2000.0 with a rate per Julian century.
 */
class PlanetaryElements
{
    /**
     * @var array<string, array{a: array{float, float}, e: array{float, float}, i: array{float, float}, L: array{float, float}, varpi: array{float, float}, Omega: array{float, float}}>
     */
    private const ELEMENTS = [
        'mercury' => [
            'a' => [0.38709927, 0.00000037], 'e' => [0.20563593, 0.00001906], 'i' => [7.00497902, -0.00594749],
            'L' => [252.25032350, 149472.67411175], 'varpi' => [77.45779628, 0.16047689], 'Omega' => [48.33076593, -0.12534081],
        ],
        'venus' => [
            'a' => [0.72333566, 0.00000390], 'e' => [0.00677672, -0.00004107], 'i' => [3.39467605, -0.00078890],
            'L' => [181.97909950, 58517.81538729], 'varpi' => [131.60246718, 0.00268329], 'Omega' => [76.67984255, -0.27769418],
        ],
        'earth' => [
            'a' => [1.00000261, 0.00000562], 'e' => [0.01671123, -0.00004392], 'i' => [-0.00001531, -0.01294668],
            'L' => [100.46457166, 35999.37244981], 'varpi' => [102.93768193, 0.32327364], 'Omega' => [0.0, 0.0],
        ],
        'mars' => [
            'a' => [1.52371034, 0.00001847], 'e' => [0.09339410, 0.00007882], 'i' => [1.84969142, -0.00813131],
            'L' => [-4.55343205, 19140.30268499], 'varpi' => [-23.94362959, 0.44441088], 'Omega' => [49.55953891, -0.29257343],
        ],
        'jupiter' => [
            'a' => [5.20288700, -0.00011607], 'e' => [0.04838624, -0.00013253], 'i' => [1.30439695, -0.00183714],
            'L' => [34.39644051, 3034.74612775], 'varpi' => [14.72847983, 0.21252668], 'Omega' => [100.47390909, 0.20469106],
        ],
        'saturn' => [
            'a' => [9.53667594, -0.00125060], 'e' => [0.05386179, -0.00050991], 'i' => [2.48599187, 0.00193609],
            'L' => [49.95424423, 1222.49362201], 'varpi' => [92.59887831, -0.41897216], 'Omega' => [113.66242448, -0.28867794],
        ],
    ];

    /**
     * Heliocentric ecliptic (J2000) rectangular coordinates in AU.
     *
     * @return array{0: float, 1: float, 2: float}
     */
    public static function heliocentricPosition(string $planet, float $julianDay): array
    {
        $elements = self::ELEMENTS[$planet] ?? throw new \InvalidArgumentException("Unknown planet: {$planet}");
        $t = JulianDay::centuriesSinceJ2000($julianDay);

        $a = $elements['a'][0] + $elements['a'][1] * $t;
        $e = $elements['e'][0] + $elements['e'][1] * $t;
        $i = $elements['i'][0] + $elements['i'][1] * $t;
        $meanLongitude = $elements['L'][0] + $elements['L'][1] * $t;
        $perihelionLongitude = $elements['varpi'][0] + $elements['varpi'][1] * $t;
        $ascendingNode = $elements['Omega'][0] + $elements['Omega'][1] * $t;

        $meanAnomaly = AstroMath::normalizeDegrees($meanLongitude - $perihelionLongitude);
        // Reduce to [-180, 180) so Kepler's equation converges from the
        // nearest direction.
        if ($meanAnomaly > 180) {
            $meanAnomaly -= 360;
        }

        $eccentricAnomaly = self::solveKepler($meanAnomaly, $e);

        $xOrbital = $a * (AstroMath::cosDeg($eccentricAnomaly) - $e);
        $yOrbital = $a * sqrt(1 - $e ** 2) * AstroMath::sinDeg($eccentricAnomaly);

        $argumentOfPerihelion = $perihelionLongitude - $ascendingNode;

        $cosOmega = AstroMath::cosDeg($ascendingNode);
        $sinOmega = AstroMath::sinDeg($ascendingNode);
        $cosArg = AstroMath::cosDeg($argumentOfPerihelion);
        $sinArg = AstroMath::sinDeg($argumentOfPerihelion);
        $cosI = AstroMath::cosDeg($i);
        $sinI = AstroMath::sinDeg($i);

        $x = ($cosArg * $cosOmega - $sinArg * $sinOmega * $cosI) * $xOrbital
            + (-$sinArg * $cosOmega - $cosArg * $sinOmega * $cosI) * $yOrbital;
        $y = ($cosArg * $sinOmega + $sinArg * $cosOmega * $cosI) * $xOrbital
            + (-$sinArg * $sinOmega + $cosArg * $cosOmega * $cosI) * $yOrbital;
        $z = ($sinArg * $sinI) * $xOrbital + ($cosArg * $sinI) * $yOrbital;

        return [$x, $y, $z];
    }

    /**
     * Geocentric ecliptic longitude via vector subtraction of Earth's
     * heliocentric position from the target planet's.
     *
     * Standish's elements are fixed to the J2000.0 mean equinox, while
     * SunPosition/MoonPosition's formulas are referred to the mean equinox
     * of date (their T-dependent mean-longitude terms bake precession in).
     * Mixing the two frames without correction drifts by the general
     * precession rate (~1.4°/century) the further a birth date is from
     * 2000 — small on its own, but enough to occasionally misplace a
     * planet near a sign boundary decades out. Meeus ch. 21's leading-term
     * precession-in-longitude brings this back into the same frame.
     */
    public static function geocentricLongitude(string $planet, float $julianDay): float
    {
        [$px, $py] = self::heliocentricPosition($planet, $julianDay);
        [$ex, $ey] = self::heliocentricPosition('earth', $julianDay);

        $t = JulianDay::centuriesSinceJ2000($julianDay);
        $precession = (5029.0966 / 3600) * $t;

        return AstroMath::normalizeDegrees(AstroMath::atan2Deg($py - $ey, $px - $ex) + $precession);
    }

    /**
     * Solves Kepler's equation E - e*sin(E) = M for the eccentric anomaly E,
     * by fixed-point iteration (converges in a handful of steps for the low
     * eccentricities of the major planets).
     */
    private static function solveKepler(float $meanAnomalyDegrees, float $eccentricity): float
    {
        $eccentricityDegrees = rad2deg($eccentricity);
        $E = $meanAnomalyDegrees;

        for ($iteration = 0; $iteration < 30; $iteration++) {
            $deltaE = ($meanAnomalyDegrees - $E + $eccentricityDegrees * AstroMath::sinDeg($E))
                / (1 - $eccentricity * AstroMath::cosDeg($E));
            $E += $deltaE;

            if (abs($deltaE) < 1e-9) {
                break;
            }
        }

        return $E;
    }
}
