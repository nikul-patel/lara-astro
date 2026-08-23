<?php

namespace App\Services\Astrology;

/**
 * Graha drishti (planetary aspect) under the whole-sign house system:
 * every planet fully aspects the house 7th from its own (opposite sign),
 * and four planets carry additional special aspects — Mars the 4th and
 * 8th, Jupiter the 5th and 9th, Saturn the 3rd and 10th, all counted
 * inclusively from the planet's own house.
 */
class Aspects
{
    private const SPECIAL_ASPECT_OFFSETS = [
        'Mars' => [3, 7],
        'Jupiter' => [4, 8],
        'Saturn' => [2, 9],
    ];

    /**
     * @return list<int> house numbers (1-12) this planet aspects from the given house
     */
    public static function aspectedHouses(string $planet, int $fromHouse): array
    {
        $offsets = [6, ...self::SPECIAL_ASPECT_OFFSETS[$planet] ?? []];

        return array_map(
            fn (int $offset) => (($fromHouse - 1 + $offset) % 12) + 1,
            $offsets,
        );
    }

    public static function aspectsHouse(string $planet, int $fromHouse, int $toHouse): bool
    {
        return in_array($toHouse, self::aspectedHouses($planet, $fromHouse), true);
    }
}
