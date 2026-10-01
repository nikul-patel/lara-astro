<?php

namespace App\Services\Astrology\Transits;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Ayanamsa;
use App\Services\Astrology\ChartAssembler;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\LunarNodes;
use App\Services\Astrology\MoonPosition;
use App\Services\Astrology\PlanetaryElements;
use App\Services\Astrology\SunPosition;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;

/**
 * Gochar (transit): where each of the 9 grahas sits, sidereally, at a
 * given moment — not a natal-chart concept, same category as
 * {@see SadeSati}. Computes the same tropical-longitude-minus-ayanamsa
 * series {@see ChartAssembler} uses for a natal
 * chart, but without an ascendant/houses — Gochar is conventionally read
 * from the house-from-natal-Moon a transiting planet currently occupies,
 * not from any "ascendant of the transit moment" (there isn't one in
 * classical practice; the native's own natal Moon sign is the reference
 * point), so this never needs a birth place's latitude/longitude.
 */
class Transit
{
    /**
     * @return array<string, string> Each of the 9 classical grahas mapped to its sidereal sign at $moment.
     */
    public static function signsAt(CarbonImmutable $moment): array
    {
        $julianDay = JulianDay::fromUtc($moment->utc());
        $ayanamsa = Ayanamsa::lahiri($julianDay);

        $tropicalLongitudes = [
            'Sun' => SunPosition::apparentLongitude($julianDay),
            'Moon' => MoonPosition::apparentLongitude($julianDay),
            'Mercury' => PlanetaryElements::geocentricLongitude('mercury', $julianDay),
            'Venus' => PlanetaryElements::geocentricLongitude('venus', $julianDay),
            'Mars' => PlanetaryElements::geocentricLongitude('mars', $julianDay),
            'Jupiter' => PlanetaryElements::geocentricLongitude('jupiter', $julianDay),
            'Saturn' => PlanetaryElements::geocentricLongitude('saturn', $julianDay),
            'Rahu' => LunarNodes::rahuLongitude($julianDay),
            'Ketu' => LunarNodes::ketuLongitude($julianDay),
        ];

        $signs = [];
        foreach ($tropicalLongitudes as $planet => $longitude) {
            $signs[$planet] = ZodiacSigns::forLongitude(AstroMath::normalizeDegrees($longitude - $ayanamsa));
        }

        return $signs;
    }

    /**
     * House-from-Moon (1-12): 1 means the transiting planet is currently
     * in the same sign as the native's natal Moon, counting forward from
     * there — the standard Gochar reference point.
     */
    public static function houseFromMoon(string $transitSign, string $natalMoonSign): int
    {
        return ZodiacSigns::offset($natalMoonSign, $transitSign);
    }

    /**
     * @param  array<string, string>  $transitSigns  signsAt()'s output.
     * @return array<string, array{sign: string, house_from_moon: int}>
     */
    public static function forNatalMoon(array $transitSigns, string $natalMoonSign): array
    {
        $result = [];
        foreach ($transitSigns as $planet => $sign) {
            $result[$planet] = [
                'sign' => $sign,
                'house_from_moon' => self::houseFromMoon($sign, $natalMoonSign),
            ];
        }

        return $result;
    }
}
