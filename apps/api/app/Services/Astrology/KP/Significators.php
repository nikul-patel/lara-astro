<?php

namespace App\Services\Astrology\KP;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\Houses\PlacidusCusps;

/**
 * KP (Krishnamurti Paddhati) house significators: the standard 4-level
 * hierarchy, strongest to weakest —
 *   1. planets in the star (nakshatra) of a house's occupant,
 *   2. the occupant itself,
 *   3. planets in the star of the house's owner (sign lord),
 *   4. the owner itself.
 * "Occupant" uses the Chalit/Bhava-Madhya cusp-bounded placement (see
 * {@see PlacidusCusps::planetsByHouse()}),
 * not whole-sign houses — standard KP practice uses Placidus cusps for
 * houses, and this engine already computes them for #81.
 */
class Significators
{
    /**
     * @param  list<array{house: int, sign: string, planets: list<string>}>  $bhavaMadhya  BirthChartCalculator's `bhava_madhya` (Chalit occupants).
     * @param  array<string, string>  $planetNakshatraLords  Every planet (including Rahu/Ketu) mapped to its own nakshatra lord — see {@see SubLord::forLongitude()}.
     * @return array<int, array{occupants: list<string>, owner: string, occupant_star_lords: list<string>, owner_star_lords: list<string>, combined: list<string>}>
     */
    public static function forHouses(array $bhavaMadhya, array $planetNakshatraLords): array
    {
        $result = [];

        foreach ($bhavaMadhya as $cusp) {
            $occupants = $cusp['planets'];
            $owner = HouseLords::SIGN_RULERS[$cusp['sign']];

            $occupantStarLords = self::planetsWithNakshatraLordIn($planetNakshatraLords, $occupants);
            $ownerStarLords = self::planetsWithNakshatraLordIn($planetNakshatraLords, [$owner]);

            $combined = [];
            foreach ([$occupantStarLords, $occupants, $ownerStarLords, [$owner]] as $level) {
                foreach ($level as $planet) {
                    if (! in_array($planet, $combined, true)) {
                        $combined[] = $planet;
                    }
                }
            }

            $result[$cusp['house']] = [
                'occupants' => $occupants,
                'owner' => $owner,
                'occupant_star_lords' => $occupantStarLords,
                'owner_star_lords' => $ownerStarLords,
                'combined' => $combined,
            ];
        }

        return $result;
    }

    /**
     * The inverse view of forHouses()'s `combined` lists: for each planet,
     * which houses it significates.
     *
     * @param  array<int, array{combined: list<string>}>  $houseSignificators  forHouses()'s output.
     * @return array<string, list<int>>
     */
    public static function planetSignifications(array $houseSignificators): array
    {
        $byPlanet = [];

        foreach ($houseSignificators as $house => $data) {
            foreach ($data['combined'] as $planet) {
                $byPlanet[$planet][] = $house;
            }
        }

        return $byPlanet;
    }

    /**
     * @param  array<string, string>  $planetNakshatraLords
     * @param  list<string>  $targets
     * @return list<string>
     */
    private static function planetsWithNakshatraLordIn(array $planetNakshatraLords, array $targets): array
    {
        $matches = [];

        foreach ($planetNakshatraLords as $planet => $lord) {
            if (in_array($lord, $targets, true)) {
                $matches[] = $planet;
            }
        }

        return $matches;
    }
}
