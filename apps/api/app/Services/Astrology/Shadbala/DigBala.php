<?php

namespace App\Services\Astrology\Shadbala;

use App\Services\Astrology\HouseLords;

/**
 * Dig Bala ("directional strength"): each planet has one house where it is
 * maximally strong — Sun/Mars at the 10th (the "South", midday strength),
 * Moon/Venus at the 4th ("North"), Jupiter/Mercury at the 1st ("East"),
 * Saturn at the 7th ("West") — falling off linearly to zero at the
 * opposite house.
 *
 * The classical formula measures angular distance from the house CUSP in
 * degrees; this engine has no real house-cusp system yet (whole-sign
 * houses only — see Houses.php, and the deferred Placidus/KP work in
 * issues #80/#81), so distance is measured in whole-sign house-steps
 * (0-6, since the opposite house is always 6 signs away) rather than
 * degrees. A documented simplification, not a different formula — once
 * real cusps exist this can be refined to degree-based distance without
 * changing the public contract.
 */
class DigBala
{
    private const MAX_STRENGTH_HOUSE = [
        'Sun' => 10, 'Mars' => 10, 'Moon' => 4, 'Venus' => 4,
        'Jupiter' => 1, 'Mercury' => 1, 'Saturn' => 7,
    ];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array<string, float> Planet => Virupas (max 60, min 0).
     */
    public static function calculate(array $houses): array
    {
        $values = [];

        foreach (self::MAX_STRENGTH_HOUSE as $planet => $maxHouse) {
            $planetHouse = HouseLords::houseContainingPlanet($planet, $houses);
            $stepsAway = min(
                abs($planetHouse - $maxHouse),
                12 - abs($planetHouse - $maxHouse)
            );

            $values[$planet] = round(60 * (1 - $stepsAway / 6), 2);
        }

        return $values;
    }
}
