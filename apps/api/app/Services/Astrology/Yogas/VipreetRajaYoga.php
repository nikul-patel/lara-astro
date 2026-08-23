<?php

namespace App\Services\Astrology\Yogas;

use App\Services\Astrology\HouseLords;

/**
 * Vipreet Raja Yoga: the lord of a dusthana (6th, 8th, or 12th — houses of
 * conflict, loss, and expenditure) is itself posited in a dusthana house.
 * Classically read as a paradoxical strengthening — affliction placed
 * upon affliction cancels out, turning misfortune into unexpected gain.
 */
class VipreetRajaYoga
{
    private const DUSTHANA_HOUSES = [6, 8, 12];

    private const NAMES = [6 => 'Harsha', 8 => 'Sarala', 12 => 'Vimala'];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>
     */
    public static function detect(array $houses): array
    {
        $found = [];

        foreach (self::DUSTHANA_HOUSES as $dusthana) {
            $lord = HouseLords::lordOfHouse($dusthana, $houses);
            $placement = HouseLords::houseContainingPlanet($lord, $houses);

            if ($placement === null || ! in_array($placement, self::DUSTHANA_HOUSES, true)) {
                continue;
            }

            $name = self::NAMES[$dusthana];
            $found[] = [
                'key' => 'vipreet_raja_yoga',
                'name' => "Vipreet Raja Yoga ({$name})",
                'category' => 'status_and_authority',
                'planets' => [$lord],
                'houses' => [$dusthana, $placement],
                'description' => "The {$dusthana}th-house lord ({$lord}) is placed in another dusthana house ({$placement}th), forming {$name} Vipreet Raja Yoga — a paradoxical combination said to turn adversity into unexpected gain.",
            ];
        }

        return $found;
    }
}
