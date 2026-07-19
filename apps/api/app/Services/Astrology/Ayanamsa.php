<?php

namespace App\Services\Astrology;

/**
 * Lahiri (Chitrapaksha) ayanamsa — the offset between the tropical
 * (equinox-based) and sidereal (fixed-star-based) zodiacs used by Vedic
 * astrology, and the PRD's specified default for the Vedic system. Uses
 * the standard linear approximation (value at J2000.0 plus the general
 * precession rate); the real Lahiri ayanamsa has small non-linear terms
 * that official Indian ephemerides publish as a table, but the linear
 * approximation stays within about half an arcminute per decade of J2000 —
 * negligible next to this engine's other approximations.
 */
class Ayanamsa
{
    /**
     * Lahiri ayanamsa at the J2000.0 epoch, in degrees (23°51'11").
     */
    private const AYANAMSA_AT_J2000 = 23.85306;

    /**
     * General precession rate, degrees per Julian century (50.2888"/year).
     */
    private const PRECESSION_PER_CENTURY = 5028.88 / 3600;

    public static function lahiri(float $julianDay): float
    {
        $t = JulianDay::centuriesSinceJ2000($julianDay);

        return AstroMath::normalizeDegrees(self::AYANAMSA_AT_J2000 + self::PRECESSION_PER_CENTURY * $t);
    }
}
