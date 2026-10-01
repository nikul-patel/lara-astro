<?php

namespace App\Services\Astrology\Shadbala;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\Panchang\Tithi;

/**
 * Bhavabala ("house strength"): Shadbala's planet-strength concept applied
 * to each of the 12 houses instead. Classically three components —
 * Bhavadhipati Bala (the house lord's own Shadbala), Bhava Dig Bala
 * (directional strength from the house's real cusp position), and Bhava
 * Drishti Bala (net aspect strength on the house).
 *
 * Bhava Dig Bala is deliberately excluded here: its classical formula
 * needs a real house-cusp (Bhava Madhya) position, which this engine
 * doesn't have yet — whole-sign houses only (see Houses.php). That's
 * exactly the house-cusp engine scoped separately in issues #80/#81;
 * revisiting Bhavabala to add Bhava Dig Bala belongs there, not here.
 * Because of this exclusion, no "minimum required Rupas" threshold is
 * reported — the classical threshold (commonly cited as 7 Rupas) is
 * calibrated against the FULL 3-component formula and isn't meaningful
 * against a 2-component total.
 *
 * Bhava Drishti Bala here uses the same whole-sign simplification as
 * Shadbala's own Drik Bala's special aspects (Parashari graha drishti:
 * every planet fully aspects the house 7 away; Mars additionally 4/8,
 * Jupiter 5/9, Saturn 3/10) — but since a whole-sign house has no "exact
 * degree" to grade an aspect's strength against, every aspect a planet
 * casts on a house it reaches at all counts as its full 60 Virupas,
 * rather than the degree-graded curve Drik Bala uses between planets.
 */
class BhavabalaCalculator
{
    private const PLANETS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];

    /** Whole-sign house-steps-ahead (0-indexed) each planet's aspects reach — every planet gets the universal 7th; Mars/Jupiter/Saturn add their classical specials. */
    private const ASPECT_OFFSETS = [
        'Mars' => [6, 3, 7],
        'Jupiter' => [6, 4, 8],
        'Saturn' => [6, 2, 9],
    ];

    private const DEFAULT_ASPECT_OFFSETS = [6];

    /**
     * @param  array<string, float>  $shadbalaTotalVirupas  Each classical planet's Shadbala total (ShadbalaCalculator::calculate()'s `total_virupas`).
     * @param  array<string, float>  $chartLongitudes
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array{bhavadhipati: array<int, float>, bhava_drishti: array<int, float>, total_virupas: array<int, float>, total_rupas: array<int, float>}
     */
    public static function calculate(array $shadbalaTotalVirupas, array $chartLongitudes, array $houses): array
    {
        $bhavadhipati = self::bhavadhipatiBala($shadbalaTotalVirupas, $houses);
        $bhavaDrishti = self::bhavaDrishtiBala($chartLongitudes, $houses);

        $totalVirupas = [];
        $totalRupas = [];
        foreach (range(1, 12) as $houseNumber) {
            $totalVirupas[$houseNumber] = round($bhavadhipati[$houseNumber] + $bhavaDrishti[$houseNumber], 2);
            $totalRupas[$houseNumber] = round($totalVirupas[$houseNumber] / 60, 2);
        }

        return [
            'bhavadhipati' => $bhavadhipati,
            'bhava_drishti' => $bhavaDrishti,
            'total_virupas' => $totalVirupas,
            'total_rupas' => $totalRupas,
        ];
    }

    /**
     * @param  array<string, float>  $shadbalaTotalVirupas
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array<int, float>
     */
    private static function bhavadhipatiBala(array $shadbalaTotalVirupas, array $houses): array
    {
        $values = [];
        foreach ($houses as $house) {
            $lord = HouseLords::SIGN_RULERS[$house['sign']];
            $values[$house['number']] = $shadbalaTotalVirupas[$lord];
        }

        return $values;
    }

    /**
     * @param  array<string, float>  $chartLongitudes
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array<int, float>
     */
    private static function bhavaDrishtiBala(array $chartLongitudes, array $houses): array
    {
        $benefics = ['Mercury', 'Jupiter', 'Venus'];
        if (Tithi::forLongitudes($chartLongitudes['Sun'], $chartLongitudes['Moon'])['paksha'] === 'Shukla') {
            $benefics[] = 'Moon';
        }

        $net = array_fill_keys(range(1, 12), 0.0);

        foreach (self::PLANETS as $planet) {
            $ownHouse = HouseLords::houseContainingPlanet($planet, $houses);
            $offsets = self::ASPECT_OFFSETS[$planet] ?? self::DEFAULT_ASPECT_OFFSETS;

            foreach ($offsets as $offset) {
                $targetHouse = (($ownHouse - 1 + $offset) % 12) + 1;
                $net[$targetHouse] += in_array($planet, $benefics, true) ? 60 : -60;
            }
        }

        return array_map(fn (float $v) => round($v / 4, 2), $net);
    }
}
