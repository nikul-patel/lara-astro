<?php

namespace App\Services\Astrology;

/**
 * Degree-based trigonometry helpers. Every angle in this namespace is
 * expressed in degrees (not radians) to match the astronomical formulas
 * (Meeus, "Astronomical Algorithms"; Standish's Keplerian element tables)
 * they're transcribed from — converting once here is less error-prone than
 * converting inline at every call site.
 */
class AstroMath
{
    public static function sinDeg(float $degrees): float
    {
        return sin(deg2rad($degrees));
    }

    public static function cosDeg(float $degrees): float
    {
        return cos(deg2rad($degrees));
    }

    public static function tanDeg(float $degrees): float
    {
        return tan(deg2rad($degrees));
    }

    public static function atan2Deg(float $y, float $x): float
    {
        return rad2deg(atan2($y, $x));
    }

    /**
     * Plain (non-atan2) arctangent, returning degrees in (-90, 90) —
     * deliberately NOT quadrant-resolving like atan2Deg, for formulas
     * (e.g. Houses\PlacidusCusps) that need the raw principal-range
     * result and resolve the quadrant themselves against a known
     * neighboring reference point.
     */
    public static function atanDeg(float $x): float
    {
        return rad2deg(atan($x));
    }

    /**
     * Normalizes an angle to the [0, 360) range.
     */
    public static function normalizeDegrees(float $degrees): float
    {
        $normalized = fmod($degrees, 360);

        return $normalized < 0 ? $normalized + 360 : $normalized;
    }
}
