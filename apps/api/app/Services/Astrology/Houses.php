<?php

namespace App\Services\Astrology;

/**
 * Ascendant (Lagna) and house cusps.
 *
 * Uses the Whole-Sign house system: the zodiac sign containing the
 * Ascendant is the 1st house in its entirety, and each subsequent house is
 * the next sign in order. This is the traditional Vedic/Jyotish house
 * system (sign = house), and a historically-attested Western one too —
 * chosen over Placidus/Koch-style quadrant houses (which need iterative
 * numerical solving of the time-of-rising equation) as a deliberate MVP
 * scope decision; swapping in a quadrant system later only touches this
 * class.
 */
class Houses
{
    /**
     * Greenwich Mean Sidereal Time, in degrees (Meeus ch. 12, eq 12.4).
     */
    public static function greenwichSiderealTime(float $julianDay): float
    {
        $t = JulianDay::centuriesSinceJ2000($julianDay);

        $theta = 280.46061837
            + 360.98564736629 * ($julianDay - 2451545.0)
            + 0.000387933 * $t ** 2
            - $t ** 3 / 38710000;

        return AstroMath::normalizeDegrees($theta);
    }

    /**
     * Mean obliquity of the ecliptic, in degrees (Meeus ch. 22, truncated
     * to the linear term — sub-arcsecond terms are irrelevant here).
     */
    public static function obliquity(float $julianDay): float
    {
        $t = JulianDay::centuriesSinceJ2000($julianDay);

        return 23.4392911 - 0.0130042 * $t;
    }

    /**
     * Ecliptic longitude of the Ascendant, in degrees [0, 360).
     *
     * Derived from the standard rising-point spherical-trigonometry
     * relation; verified against the ε=0 degenerate case (with no ecliptic
     * obliquity, the ascendant is exactly 90° of right ascension east of
     * the midheaven, independent of latitude).
     */
    public static function ascendant(float $julianDay, float $latitude, float $longitude): float
    {
        $ramc = AstroMath::normalizeDegrees(self::greenwichSiderealTime($julianDay) + $longitude);
        $obliquity = self::obliquity($julianDay);

        $y = AstroMath::cosDeg($ramc);
        $x = -(AstroMath::sinDeg($obliquity) * AstroMath::tanDeg($latitude) + AstroMath::cosDeg($obliquity) * AstroMath::sinDeg($ramc));

        return AstroMath::normalizeDegrees(AstroMath::atan2Deg($y, $x));
    }

    /**
     * @return list<array{number: int, sign: string, planets: list<string>}>
     */
    public static function wholeSignHouses(float $ascendantLongitude, array $planetLongitudes): array
    {
        $ascendantSignIndex = (int) floor($ascendantLongitude / 30);

        $houses = [];

        for ($houseNumber = 1; $houseNumber <= 12; $houseNumber++) {
            $signIndex = ($ascendantSignIndex + $houseNumber - 1) % 12;

            $planetsInHouse = [];
            foreach ($planetLongitudes as $planetName => $longitude) {
                if ((int) floor($longitude / 30) === $signIndex) {
                    $planetsInHouse[] = $planetName;
                }
            }

            $houses[] = [
                'number' => $houseNumber,
                'sign' => ZodiacSigns::NAMES[$signIndex],
                'planets' => $planetsInHouse,
            ];
        }

        return $houses;
    }
}
