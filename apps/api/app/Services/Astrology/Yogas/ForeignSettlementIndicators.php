<?php

namespace App\Services\Astrology\Yogas;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\Predictions\Ordinal;

/**
 * Classical indicators of foreign residence/travel and, by extension,
 * suitability for a job or business based abroad — the 12th house governs
 * foreign lands and life away from one's birthplace, the 9th governs
 * long-distance travel and fortune. Three commonly-cited combinations:
 *
 * 1. Rahu or the Moon posited in the 12th house.
 * 2. The 12th-house lord placed in a kendra or trikona (a "strong" house).
 * 3. The 9th-house lord and 12th-house lord conjunct or in mutual
 *    exchange (parivartana) — deliberately narrower than
 *    LordRelationship::linked()'s aspect condition, since a mere aspect is
 *    a weaker signal for this particular combination.
 */
class ForeignSettlementIndicators
{
    private const STRONG_HOUSES = [1, 4, 5, 7, 9, 10];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>
     */
    public static function detect(array $houses): array
    {
        $found = [];

        $rahuHouse = HouseLords::houseContainingPlanet('Rahu', $houses);
        if ($rahuHouse === 12) {
            $found[] = self::entry(
                'foreign_settlement_rahu_12th',
                ['Rahu'],
                [12],
                'Rahu is placed in the 12th house, a classical indicator of drawing toward foreign lands and settling or working abroad.',
            );
        }

        $moonHouse = HouseLords::houseContainingPlanet('Moon', $houses);
        if ($moonHouse === 12) {
            $found[] = self::entry(
                'foreign_settlement_moon_12th',
                ['Moon'],
                [12],
                'The Moon is placed in the 12th house, indicating an emotional and circumstantial pull toward living or working in a foreign land.',
            );
        }

        $lord12 = HouseLords::lordOfHouse(12, $houses);
        $lord12Placement = HouseLords::houseContainingPlanet($lord12, $houses);
        if ($lord12Placement !== null && in_array($lord12Placement, self::STRONG_HOUSES, true)) {
            $found[] = self::entry(
                'foreign_settlement_12th_lord_strong',
                [$lord12],
                [12, $lord12Placement],
                'The 12th-house lord ('.$lord12.') is placed in a kendra/trikona house ('.Ordinal::suffix($lord12Placement).'), strengthening prospects for a successful stint abroad rather than mere foreign travel.',
            );
        }

        $lord9 = HouseLords::lordOfHouse(9, $houses);
        $lord9Placement = HouseLords::houseContainingPlanet($lord9, $houses);
        if ($lord9 !== $lord12 && $lord9Placement !== null && $lord12Placement !== null) {
            $conjunct = $lord9Placement === $lord12Placement;
            $exchanged = $lord9Placement === 12 && $lord12Placement === 9;

            if ($conjunct || $exchanged) {
                $found[] = self::entry(
                    'foreign_settlement_9th_12th_link',
                    [$lord9, $lord12],
                    [9, 12],
                    "The 9th-house lord ({$lord9}) and 12th-house lord ({$lord12}) are ".($exchanged ? 'in mutual exchange' : 'conjunct').', a strong combination for fortune found specifically through foreign connections, business, or a job abroad.',
                );
            }
        }

        return $found;
    }

    /**
     * @param  list<string>  $planets
     * @param  list<int>  $houses
     * @return array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}
     */
    private static function entry(string $key, array $planets, array $houses, string $description): array
    {
        return [
            'key' => $key,
            'name' => 'Foreign Settlement Yoga',
            'category' => 'foreign_settlement',
            'planets' => $planets,
            'houses' => $houses,
            'description' => $description,
        ];
    }
}
