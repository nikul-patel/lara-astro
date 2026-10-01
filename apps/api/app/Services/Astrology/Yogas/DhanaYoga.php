<?php

namespace App\Services\Astrology\Yogas;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\Predictions\Ordinal;

/**
 * Dhana yoga: a wealth-house lord (2nd/11th) linked by conjunction,
 * exchange, or mutual aspect with a kendra/trikona-adjacent house lord
 * (1st/5th/9th) — the classical combination for financial gain.
 */
class DhanaYoga
{
    private const WEALTH_HOUSES = [2, 11];

    private const LINKED_HOUSES = [1, 5, 9];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>
     */
    public static function detect(array $houses): array
    {
        $found = [];
        $seenPairs = [];

        foreach (self::WEALTH_HOUSES as $wealth) {
            $wealthLord = HouseLords::lordOfHouse($wealth, $houses);

            foreach (self::LINKED_HOUSES as $linked) {
                if ($wealth === $linked) {
                    continue;
                }

                $linkedLord = HouseLords::lordOfHouse($linked, $houses);
                $pairKey = implode('-', collect([$wealthLord, $linkedLord])->sort()->all());

                if (isset($seenPairs[$pairKey]) || ! LordRelationship::linked($wealthLord, $wealth, $linkedLord, $linked, $houses)) {
                    continue;
                }

                $seenPairs[$pairKey] = true;
                $found[] = [
                    'key' => 'dhana_yoga',
                    'name' => 'Dhana Yoga',
                    'category' => 'wealth',
                    'planets' => [$wealthLord, $linkedLord],
                    'houses' => [$wealth, $linked],
                    'description' => 'The '.Ordinal::suffix($wealth)."-house lord ({$wealthLord}) and ".Ordinal::suffix($linked)."-house lord ({$linkedLord}) are linked, forming a Dhana Yoga associated with financial gain.",
                ];
            }
        }

        return $found;
    }
}
