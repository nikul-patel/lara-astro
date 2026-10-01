<?php

namespace App\Services\Astrology\Jaimini;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Varga\VargaCalculator;

/**
 * Jaimini Chara Karakas: unlike Parashari's fixed "Sun rules the soul,
 * Moon rules the mind" significators, Jaimini assigns significance by each
 * planet's degree within its own sign at birth — the planet with the
 * highest degree is the Atmakaraka ("soul significator"), the single most
 * important point in Jaimini analysis.
 *
 * Only the 7 classical planets (Sun through Saturn) are considered, the
 * standard 7-karaka scheme — some traditions add Rahu as an 8th
 * (Ashtakavarga) karaka; that variant isn't implemented here.
 */
class Karakas
{
    private const KARAKA_PLANETS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];

    /**
     * @param  array<string, float>  $planetLongitudes
     */
    public static function atmakaraka(array $planetLongitudes): string
    {
        $bestPlanet = null;
        $bestDegree = -1.0;

        foreach (self::KARAKA_PLANETS as $planet) {
            $degreeInSign = self::degreeInSign($planetLongitudes[$planet]);

            if ($degreeInSign > $bestDegree) {
                $bestDegree = $degreeInSign;
                $bestPlanet = $planet;
            }
        }

        return $bestPlanet;
    }

    /**
     * Karakamsa: the Navamsa (D9) sign occupied by the Atmakaraka.
     *
     * @param  array<string, float>  $planetLongitudes
     */
    public static function karakamsa(array $planetLongitudes): string
    {
        $atmakaraka = self::atmakaraka($planetLongitudes);

        return VargaCalculator::sign('D9', $planetLongitudes[$atmakaraka]);
    }

    /**
     * Swamsa ("own Navamsa"): the Navamsa (D9) sign of the Ascendant point
     * itself. Some sources instead define Swamsa as the Navamsa of the
     * Ascendant LORD rather than the Ascendant point — this implements the
     * "Navamsa of the Lagna point" reading specifically; named explicitly
     * since, unlike most terms in this codebase, this one genuinely varies
     * by source.
     */
    public static function swamsa(float $ascendantLongitude): string
    {
        return VargaCalculator::sign('D9', $ascendantLongitude);
    }

    private static function degreeInSign(float $longitude): float
    {
        $normalized = AstroMath::normalizeDegrees($longitude);

        return $normalized - floor($normalized / 30) * 30;
    }
}
