<?php

namespace App\Services\Astrology\Doshas;

use App\Services\Astrology\Aspects;
use App\Services\Astrology\HouseLords;
use App\Services\Astrology\PlanetaryDignity;

/**
 * Mangal Dosha (a.k.a. Kuja Dosha/Bhom Dosha): Mars posited in the 1st,
 * 2nd, 4th, 7th, 8th, or 12th house counted from a reference point —
 * classically assessed as afflicting marriage/partnership prospects, and
 * the single most commonly checked dosha before Vedic matchmaking.
 *
 * Classical practice checks this from three reference points: the
 * Ascendant (Lagna) and the Moon are the two universally-cited references
 * (either one triggering the dosha); Mars-from-Venus is a third, less
 * universally required check some regional traditions add, reported here
 * separately rather than folded into the primary verdict.
 *
 * Cancellation (Bhanga): classical texts list many conditions; this
 * implementation checks the two most commonly cited ones — the same
 * "documented simplification, not exhaustive coverage" scope as
 * Yogas\NeechaBhangaYoga:
 * 1. Mars is in its own sign (Aries/Scorpio) or exalted (Capricorn) in the
 *    house that would otherwise cause the dosha.
 * 2. Mars is aspected by Jupiter (a natural benefic) from its placement.
 */
class MangalDosha
{
    private const DOSHA_HOUSES = [1, 2, 4, 7, 8, 12];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array{
     *     is_manglik: bool,
     *     from_ascendant: array{afflicted: bool, house: ?int},
     *     from_moon: array{afflicted: bool, house: ?int},
     *     from_venus: array{afflicted: bool, house: ?int},
     *     cancelled: bool,
     *     cancellation_reason: ?string,
     *     description: string,
     * }
     */
    public static function detect(array $houses): array
    {
        $marsHouse = HouseLords::houseContainingPlanet('Mars', $houses);
        $marsSign = HouseLords::signOfPlanet('Mars', $houses);

        $fromAscendant = self::afflictionFromReference($marsHouse, 1);
        $fromMoon = self::afflictionFromReference($marsHouse, self::houseOfReference('Moon', $houses));
        $fromVenus = self::afflictionFromReference($marsHouse, self::houseOfReference('Venus', $houses));

        $baseAfflicted = $fromAscendant['afflicted'] || $fromMoon['afflicted'];

        [$cancelled, $reason] = $baseAfflicted
            ? self::checkCancellation($marsHouse, $marsSign, $houses)
            : [false, null];

        return [
            'is_manglik' => $baseAfflicted && ! $cancelled,
            'from_ascendant' => $fromAscendant,
            'from_moon' => $fromMoon,
            'from_venus' => $fromVenus,
            'cancelled' => $cancelled,
            'cancellation_reason' => $reason,
            'description' => self::describe($baseAfflicted, $cancelled, $marsHouse),
        ];
    }

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     */
    private static function houseOfReference(string $planet, array $houses): ?int
    {
        return HouseLords::houseContainingPlanet($planet, $houses);
    }

    /**
     * @return array{afflicted: bool, house: ?int}
     */
    private static function afflictionFromReference(?int $marsHouse, ?int $referenceHouse): array
    {
        if ($marsHouse === null || $referenceHouse === null) {
            return ['afflicted' => false, 'house' => null];
        }

        $houseFromReference = (($marsHouse - $referenceHouse + 12) % 12) + 1;

        return [
            'afflicted' => in_array($houseFromReference, self::DOSHA_HOUSES, true),
            'house' => $houseFromReference,
        ];
    }

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array{0: bool, 1: ?string}
     */
    private static function checkCancellation(?int $marsHouse, ?string $marsSign, array $houses): array
    {
        if ($marsHouse === null || $marsSign === null) {
            return [false, null];
        }

        if (PlanetaryDignity::isOwnSign('Mars', $marsSign) || PlanetaryDignity::isExalted('Mars', $marsSign)) {
            return [true, "Mars is in its own sign or exalted ({$marsSign}), classically cancelling the dosha it would otherwise cause."];
        }

        $jupiterHouse = HouseLords::houseContainingPlanet('Jupiter', $houses);
        if ($jupiterHouse !== null && Aspects::aspectsHouse('Jupiter', $jupiterHouse, $marsHouse)) {
            return [true, "Jupiter's benefic aspect on Mars's house classically mitigates the dosha."];
        }

        return [false, null];
    }

    private static function describe(bool $afflicted, bool $cancelled, ?int $marsHouse): string
    {
        if (! $afflicted) {
            return 'No Mangal Dosha: Mars is not placed in an afflicting house from the Ascendant or Moon.';
        }

        if ($cancelled) {
            return "Mars in house {$marsHouse} would classically cause Mangal Dosha, but a cancellation (Bhanga) condition is present.";
        }

        return "Mangal Dosha present: Mars is placed in house {$marsHouse}, one of the six houses (1st/2nd/4th/7th/8th/12th) classically considered afflicting from the Ascendant and/or Moon.";
    }
}
