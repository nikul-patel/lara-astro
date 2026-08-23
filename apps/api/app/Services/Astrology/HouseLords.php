<?php

namespace App\Services\Astrology;

/**
 * Sign rulerships (traditional/Parashari, not modern outer-planet
 * co-rulership) and lookups derived from them against a computed
 * whole-sign house chart (Houses::wholeSignHouses()'s output shape).
 */
class HouseLords
{
    public const SIGN_RULERS = [
        'Aries' => 'Mars', 'Taurus' => 'Venus', 'Gemini' => 'Mercury', 'Cancer' => 'Moon',
        'Leo' => 'Sun', 'Virgo' => 'Mercury', 'Libra' => 'Venus', 'Scorpio' => 'Mars',
        'Sagittarius' => 'Jupiter', 'Capricorn' => 'Saturn', 'Aquarius' => 'Saturn', 'Pisces' => 'Jupiter',
    ];

    /**
     * The planet ruling the sign occupying the given house number.
     *
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     */
    public static function lordOfHouse(int $houseNumber, array $houses): string
    {
        $house = collect($houses)->firstWhere('number', $houseNumber);

        return self::SIGN_RULERS[$house['sign']];
    }

    /**
     * The house number a given planet is posited in, or null if it isn't
     * present in the chart (Rahu/Ketu are always present; this null case
     * exists mainly so callers don't need a separate existence check).
     *
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     */
    public static function houseContainingPlanet(string $planet, array $houses): ?int
    {
        foreach ($houses as $house) {
            if (in_array($planet, $house['planets'], true)) {
                return $house['number'];
            }
        }

        return null;
    }

    /**
     * The zodiac sign a given planet occupies. Under the whole-sign house
     * system every planet in a house shares that house's sign, so this is
     * just houseContainingPlanet()'s sign, looked up in one call for
     * callers that only care about the sign (dignity checks, mainly).
     *
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     */
    public static function signOfPlanet(string $planet, array $houses): ?string
    {
        $house = collect($houses)->first(fn (array $house) => in_array($planet, $house['planets'], true));

        return $house['sign'] ?? null;
    }
}
