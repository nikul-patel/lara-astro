<?php

namespace App\Services\Astrology\Shadbala;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\HouseLords;
use App\Services\Astrology\PlanetaryDignity;
use App\Services\Astrology\PlanetaryFriendship;
use App\Services\Astrology\Varga\VargaCalculator;
use App\Services\Astrology\ZodiacSigns;

/**
 * Sthana Bala ("positional strength"): the first and heaviest-weighted of
 * the six Shadbala components, itself a sum of five sub-scores. Formulas
 * and point tables below are the classical values reproduced in every
 * published Shadbala worked example (BV Raman, VP Jain) and cross-checked
 * against the AGPL-licensed PyJHora engine's strength.py module
 * (https://github.com/naturalstupid/PyJHora — its own docs state it's
 * calibrated to match those same two authors' examples).
 */
class SthanaBala
{
    /** Panchadha Maitri compound relationship -> Shadbala points, used by Saptavargaja Bala. */
    private const COMPOUND_RELATIONSHIP_POINTS = [
        'great_friend' => 22.5, 'friend' => 15.0, 'neutral' => 7.5,
        'enemy' => 3.75, 'great_enemy' => 1.875,
    ];

    private const SAPTAVARGAJA_VARGAS = ['D1', 'D2', 'D3', 'D7', 'D9', 'D12', 'D30'];

    /** Male planets get Drekkana bala in the 1st decan (0-10°) of their own sign, female in the 2nd (10-20°), neuter in the 3rd (20-30°). */
    private const DREKKANA_DECAN = ['Sun' => 0, 'Mars' => 0, 'Jupiter' => 0, 'Moon' => 1, 'Venus' => 1, 'Mercury' => 2, 'Saturn' => 2];

    /** Moon and Venus are strong in even signs/navamsas; the other 5 classical planets in odd ones. */
    private const EVEN_SIGN_PREFERRING = ['Moon', 'Venus'];

    /**
     * Uchcha Bala: how close a planet sits to its deep exaltation point,
     * maximum 60 Virupas exactly there, falling linearly to 0 at the
     * (180°-opposite) deep debilitation point.
     *
     * @param  array<string, float>  $chartLongitudes
     * @return array<string, float>
     */
    public static function uchchaBala(array $chartLongitudes): array
    {
        $values = [];
        foreach (PlanetaryFriendship::CLASSICAL_PLANETS as $planet) {
            $debilitationLongitude = PlanetaryDignity::deepDebilitationLongitude($planet);
            $distance = AstroMath::normalizeDegrees($chartLongitudes[$planet] - $debilitationLongitude);
            if ($distance > 180) {
                $distance = 360 - $distance;
            }

            $values[$planet] = round($distance / 3, 2);
        }

        return $values;
    }

    /**
     * Saptavargaja Bala: a planet's dignity, summed across 7 divisional
     * charts (D1, D2, D3, D7, D9, D12, D30). In each varga: Moolatrikona
     * (D1 only) scores 45, own sign scores 30, otherwise the Panchadha
     * Maitri compound relationship between the planet and that varga
     * sign's lord (evaluated via each planet's actual D1/rasi position,
     * same as every other temporal-friendship lookup in this codebase)
     * scores per {@see self::COMPOUND_RELATIONSHIP_POINTS}.
     *
     * @param  array<string, float>  $chartLongitudes
     * @param  array<string, string>  $rasiSigns  Each classical planet's D1 sign (for the temporal-friendship lookup).
     * @return array<string, float>
     */
    public static function saptavargajaBala(array $chartLongitudes, array $rasiSigns): array
    {
        $values = array_fill_keys(PlanetaryFriendship::CLASSICAL_PLANETS, 0.0);

        foreach (self::SAPTAVARGAJA_VARGAS as $varga) {
            foreach (PlanetaryFriendship::CLASSICAL_PLANETS as $planet) {
                $sign = VargaCalculator::sign($varga, $chartLongitudes[$planet]);

                if ($varga === 'D1' && self::isInMoolatrikona($planet, $chartLongitudes[$planet])) {
                    $values[$planet] += 45.0;

                    continue;
                }

                if (PlanetaryDignity::isOwnSign($planet, $sign)) {
                    $values[$planet] += 30.0;

                    continue;
                }

                $lord = HouseLords::SIGN_RULERS[$sign];
                $natural = PlanetaryFriendship::relationship($planet, $lord);
                $temporal = PlanetaryFriendship::temporalRelationship($rasiSigns[$planet], $rasiSigns[$lord]);
                $combined = PlanetaryFriendship::combined($natural, $temporal);

                $values[$planet] += self::COMPOUND_RELATIONSHIP_POINTS[$combined];
            }
        }

        return array_map(fn (float $v) => round($v, 2), $values);
    }

    /**
     * Ojayugmarasyamsa Bala: 15 points for being in a favorable-gender
     * sign in the D1 (rasi) chart, 15 more for the D9 (navamsa) chart —
     * "favorable" meaning odd (masculine) for 5 of the 7 planets, even
     * (feminine) for Moon and Venus.
     *
     * @param  array<string, float>  $chartLongitudes
     * @return array<string, float>
     */
    public static function ojayugmarasyamsaBala(array $chartLongitudes): array
    {
        $values = [];

        foreach (PlanetaryFriendship::CLASSICAL_PLANETS as $planet) {
            $prefersEven = in_array($planet, self::EVEN_SIGN_PREFERRING, true);

            $rasiSign = ZodiacSigns::forLongitude($chartLongitudes[$planet]);
            $navamsaSign = VargaCalculator::sign('D9', $chartLongitudes[$planet]);

            $score = 0.0;
            $score += self::isEvenSign($rasiSign) === $prefersEven ? 15.0 : 0.0;
            $score += self::isEvenSign($navamsaSign) === $prefersEven ? 15.0 : 0.0;

            $values[$planet] = $score;
        }

        return $values;
    }

    /**
     * Kendradi Bala: 60 Virupas for sitting in a Kendra (angular house: 1,
     * 4, 7, 10), 30 in a Panaphara (succedent: 2, 5, 8, 11), 15 in an
     * Apoklima (cadent: 3, 6, 9, 12).
     *
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array<string, float>
     */
    public static function kendradiBala(array $houses): array
    {
        $values = [];

        foreach (PlanetaryFriendship::CLASSICAL_PLANETS as $planet) {
            $house = HouseLords::houseContainingPlanet($planet, $houses);

            $values[$planet] = match (true) {
                in_array($house, [1, 4, 7, 10], true) => 60.0,
                in_array($house, [2, 5, 8, 11], true) => 30.0,
                default => 15.0,
            };
        }

        return $values;
    }

    /**
     * Drekkana Bala: 15 Virupas if a planet's own degree-within-sign falls
     * in its gender's decan (masculine planets in the 1st decan 0-10°,
     * feminine in the 2nd 10-20°, neuter in the 3rd 20-30°) — note this
     * checks the planet's OWN sign's decan, not a D3/Drekkana-chart sign.
     *
     * @param  array<string, float>  $chartLongitudes
     * @return array<string, float>
     */
    public static function drekkanaBala(array $chartLongitudes): array
    {
        $values = [];

        foreach (PlanetaryFriendship::CLASSICAL_PLANETS as $planet) {
            $degreeInSign = AstroMath::normalizeDegrees($chartLongitudes[$planet]) % 30;
            $decan = (int) floor($degreeInSign / 10);

            $values[$planet] = $decan === self::DREKKANA_DECAN[$planet] ? 15.0 : 0.0;
        }

        return $values;
    }

    /**
     * @param  array<string, float>  $chartLongitudes
     * @param  array<string, string>  $rasiSigns
     * @return array{uchcha: array<string, float>, saptavargaja: array<string, float>, ojayugmarasyamsa: array<string, float>, kendradi: array<string, float>, drekkana: array<string, float>, total: array<string, float>}
     */
    public static function calculate(array $chartLongitudes, array $rasiSigns, array $houses): array
    {
        $uchcha = self::uchchaBala($chartLongitudes);
        $saptavargaja = self::saptavargajaBala($chartLongitudes, $rasiSigns);
        $ojayugmarasyamsa = self::ojayugmarasyamsaBala($chartLongitudes);
        $kendradi = self::kendradiBala($houses);
        $drekkana = self::drekkanaBala($chartLongitudes);

        $total = [];
        foreach (PlanetaryFriendship::CLASSICAL_PLANETS as $planet) {
            $total[$planet] = round(
                $uchcha[$planet] + $saptavargaja[$planet] + $ojayugmarasyamsa[$planet] + $kendradi[$planet] + $drekkana[$planet],
                2
            );
        }

        return [
            'uchcha' => $uchcha,
            'saptavargaja' => $saptavargaja,
            'ojayugmarasyamsa' => $ojayugmarasyamsa,
            'kendradi' => $kendradi,
            'drekkana' => $drekkana,
            'total' => $total,
        ];
    }

    private static function isInMoolatrikona(string $planet, float $longitude): bool
    {
        $moolatrikona = PlanetaryDignity::MOOLATRIKONA[$planet];
        $sign = ZodiacSigns::forLongitude($longitude);
        $degreeInSign = AstroMath::normalizeDegrees($longitude) % 30;

        return $sign === $moolatrikona['sign']
            && $degreeInSign >= $moolatrikona['from']
            && $degreeInSign <= $moolatrikona['to'];
    }

    private static function isEvenSign(string $sign): bool
    {
        return array_search($sign, ZodiacSigns::NAMES, true) % 2 === 1;
    }
}
