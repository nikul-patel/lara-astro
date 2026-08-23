<?php

namespace App\Services\Astrology\Yogas;

use App\Services\Astrology\HouseLords;

/**
 * Gajakesari yoga: Jupiter posited in a kendra (1st/4th/7th/10th) counted
 * from the Moon — a classical combination for intelligence and
 * reputation. "Kendra from the Moon" is counted the same way as kendra
 * from the ascendant, just using the Moon's house as the reference point
 * instead of the 1st house.
 */
class GajakesariYoga
{
    private const KENDRA_OFFSETS = [0, 3, 6, 9]; // 1st, 4th, 7th, 10th, 0-indexed from the reference house

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>
     */
    public static function detect(array $houses): array
    {
        $moonHouse = HouseLords::houseContainingPlanet('Moon', $houses);
        $jupiterHouse = HouseLords::houseContainingPlanet('Jupiter', $houses);

        if ($moonHouse === null || $jupiterHouse === null) {
            return [];
        }

        $offsetFromMoon = ($jupiterHouse - $moonHouse + 12) % 12;

        if (! in_array($offsetFromMoon, self::KENDRA_OFFSETS, true)) {
            return [];
        }

        return [[
            'key' => 'gajakesari_yoga',
            'name' => 'Gajakesari Yoga',
            'category' => 'intelligence_and_reputation',
            'planets' => ['Jupiter', 'Moon'],
            'houses' => [$jupiterHouse, $moonHouse],
            'description' => 'Jupiter is in a kendra (1st/4th/7th/10th house) counted from the Moon, forming Gajakesari Yoga, associated with intelligence, wisdom, and a good reputation.',
        ]];
    }
}
