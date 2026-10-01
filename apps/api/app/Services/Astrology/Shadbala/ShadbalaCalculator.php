<?php

namespace App\Services\Astrology\Shadbala;

use App\Services\Astrology\Houses;
use App\Services\Astrology\MoonPosition;
use App\Services\Astrology\PlanetaryElements;
use App\Services\Astrology\SunPosition;
use Carbon\CarbonImmutable;

/**
 * Shadbala ("six-fold strength"): the composite classical measure of each
 * of the 7 planets' overall power, summing Sthana + Dig + Kala + Chesta +
 * Naisargika + Drik Bala (each in its own class in this namespace — see
 * their doc comments for formulas, sources, and documented
 * simplifications/exclusions). Expressed in Virupas (summed), Rupas
 * (Virupas/60), and against each planet's classical BPHS minimum-required
 * Rupa threshold.
 */
class ShadbalaCalculator
{
    /**
     * Classical minimum Shadbala a planet needs to be considered
     * generally strong/effective, in Rupas (BPHS; reproduced in every
     * published Shadbala table, e.g. the PyJHora reference cited in
     * SthanaBala's doc comment).
     */
    private const MINIMUM_REQUIRED_RUPAS = [
        'Sun' => 5.0, 'Moon' => 6.0, 'Mars' => 5.0, 'Mercury' => 7.0,
        'Jupiter' => 6.5, 'Venus' => 5.5, 'Saturn' => 5.0,
    ];

    private const PLANETS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];

    /**
     * @param  array<string, float>  $chartLongitudes  Sidereal longitudes, must include all 7 classical planets.
     * @param  array<string, string>  $rasiSigns  Each classical planet's D1 sign.
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array{
     *     sthana: array{uchcha: array<string, float>, saptavargaja: array<string, float>, ojayugmarasyamsa: array<string, float>, kendradi: array<string, float>, drekkana: array<string, float>, total: array<string, float>},
     *     dig: array<string, float>,
     *     kala: array{nathonnatha: array<string, float>, paksha: array<string, float>, tribhaga: array<string, float>, vara: array<string, float>, hora: array<string, float>, ayana: array<string, float>, yuddha: array<string, float>, total: array<string, float>},
     *     chesta: array<string, float>,
     *     naisargika: array<string, float>,
     *     drik: array<string, float>,
     *     total_virupas: array<string, float>,
     *     total_rupas: array<string, float>,
     *     minimum_required_rupas: array<string, float>,
     *     is_strong: array<string, bool>,
     * }
     */
    public static function calculate(
        float $julianDay,
        array $chartLongitudes,
        array $rasiSigns,
        array $houses,
        CarbonImmutable $localBirthMoment,
        float $latitude,
        float $longitude
    ): array {
        $sthana = SthanaBala::calculate($chartLongitudes, $rasiSigns, $houses);
        $dig = DigBala::calculate($houses);
        $naisargika = NaisargikaBala::calculate();

        $chesta = array_merge(['Sun' => 0.0, 'Moon' => 0.0], ChestaBala::calculate($julianDay));

        $nathonnatha = KalaBala::nathonnathaBala($localBirthMoment);
        $paksha = KalaBala::pakshaBala($chartLongitudes['Sun'], $chartLongitudes['Moon']);
        $tribhaga = KalaBala::tribhagaBala($localBirthMoment, $latitude, $longitude);
        $vara = KalaBala::varaBala($localBirthMoment);
        $hora = KalaBala::horaBala($localBirthMoment, $latitude, $longitude);
        $ayana = self::ayanaBala($julianDay);

        $kalaWithoutYuddha = [];
        foreach (self::PLANETS as $planet) {
            $kalaWithoutYuddha[$planet] = $nathonnatha[$planet] + $paksha[$planet] + $tribhaga[$planet]
                + $vara[$planet] + $hora[$planet] + $ayana[$planet];
        }

        $otherTotals = [];
        foreach (self::PLANETS as $planet) {
            $otherTotals[$planet] = $sthana['total'][$planet] + $dig[$planet] + $kalaWithoutYuddha[$planet]
                + $chesta[$planet] + $naisargika[$planet];
        }
        $yuddha = array_merge(array_fill_keys(self::PLANETS, 0.0), KalaBala::yuddhaBala($chartLongitudes, $otherTotals));

        $kalaTotal = [];
        foreach (self::PLANETS as $planet) {
            $kalaTotal[$planet] = round($kalaWithoutYuddha[$planet] + $yuddha[$planet], 2);
        }

        $drik = DrikBala::calculate($chartLongitudes);

        $totalVirupas = [];
        $totalRupas = [];
        $isStrong = [];
        foreach (self::PLANETS as $planet) {
            $totalVirupas[$planet] = round(
                $sthana['total'][$planet] + $dig[$planet] + $kalaTotal[$planet] + $chesta[$planet] + $naisargika[$planet] + $drik[$planet],
                2
            );
            $totalRupas[$planet] = round($totalVirupas[$planet] / 60, 2);
            $isStrong[$planet] = $totalRupas[$planet] >= self::MINIMUM_REQUIRED_RUPAS[$planet];
        }

        return [
            'sthana' => $sthana,
            'dig' => $dig,
            'kala' => [
                'nathonnatha' => $nathonnatha,
                'paksha' => $paksha,
                'tribhaga' => $tribhaga,
                'vara' => $vara,
                'hora' => $hora,
                'ayana' => $ayana,
                'yuddha' => $yuddha,
                'total' => $kalaTotal,
            ],
            'chesta' => $chesta,
            'naisargika' => $naisargika,
            'drik' => $drik,
            'total_virupas' => $totalVirupas,
            'total_rupas' => $totalRupas,
            'minimum_required_rupas' => self::MINIMUM_REQUIRED_RUPAS,
            'is_strong' => $isStrong,
        ];
    }

    /** @return array<string, float> */
    private static function ayanaBala(float $julianDay): array
    {
        $tropicalLongitudes = [
            'Sun' => SunPosition::apparentLongitude($julianDay),
            'Moon' => MoonPosition::apparentLongitude($julianDay),
            'Mars' => PlanetaryElements::geocentricLongitude('mars', $julianDay),
            'Mercury' => PlanetaryElements::geocentricLongitude('mercury', $julianDay),
            'Jupiter' => PlanetaryElements::geocentricLongitude('jupiter', $julianDay),
            'Venus' => PlanetaryElements::geocentricLongitude('venus', $julianDay),
            'Saturn' => PlanetaryElements::geocentricLongitude('saturn', $julianDay),
        ];

        return KalaBala::ayanaBala($tropicalLongitudes, Houses::obliquity($julianDay));
    }
}
