<?php

namespace App\Services\Astrology\Yogas;

use App\Services\Astrology\Aspects;
use App\Services\Astrology\HouseLords;
use App\Services\Astrology\PlanetaryDignity;

/**
 * Neecha Bhanga Raja Yoga: cancellation of a planet's debilitation,
 * classically held to turn a weakness into a source of unusually strong
 * results. Full classical Neecha Bhanga has several alternative
 * conditions (some involving the Moon or dispositor's own dispositor);
 * this implementation checks the two most commonly cited ones and is a
 * stated simplification, not exhaustive classical coverage:
 *
 * 1. The debilitated planet's dispositor (ruler of its debilitation sign)
 *    is itself posited in a kendra (1st/4th/7th/10th) from the ascendant.
 * 2. The planet that rules the debilitated planet's exaltation sign
 *    aspects the debilitated planet's own placement.
 */
class NeechaBhangaYoga
{
    private const KENDRA_HOUSES = [1, 4, 7, 10];

    private const DEBILITATION_ELIGIBLE_PLANETS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>
     */
    public static function detect(array $houses): array
    {
        $found = [];

        foreach (self::DEBILITATION_ELIGIBLE_PLANETS as $planet) {
            $sign = HouseLords::signOfPlanet($planet, $houses);
            $placement = HouseLords::houseContainingPlanet($planet, $houses);

            if ($sign === null || $placement === null || ! PlanetaryDignity::isDebilitated($planet, $sign)) {
                continue;
            }

            $dispositor = PlanetaryDignity::debilitationSignRuler($planet);
            $dispositorPlacement = HouseLords::houseContainingPlanet($dispositor, $houses);
            $cancelledByDispositor = $dispositorPlacement !== null && in_array($dispositorPlacement, self::KENDRA_HOUSES, true);

            $exaltationRuler = PlanetaryDignity::exaltationSignRuler($planet);
            $exaltationRulerPlacement = HouseLords::houseContainingPlanet($exaltationRuler, $houses);
            $cancelledByAspect = $exaltationRulerPlacement !== null
                && Aspects::aspectsHouse($exaltationRuler, $exaltationRulerPlacement, $placement);

            if (! $cancelledByDispositor && ! $cancelledByAspect) {
                continue;
            }

            $found[] = [
                'key' => 'neecha_bhanga_yoga',
                'name' => 'Neecha Bhanga Raja Yoga',
                'category' => 'status_and_authority',
                'planets' => array_values(array_unique([$planet, $cancelledByDispositor ? $dispositor : $exaltationRuler])),
                'houses' => [$placement],
                'description' => "{$planet}'s debilitation in {$sign} is cancelled (Neecha Bhanga), a combination classically associated with a reversal from weakness into unusually strong results.",
            ];
        }

        return $found;
    }
}
