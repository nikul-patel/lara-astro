<?php

namespace App\Services\Astrology;

/**
 * Flags a planet as afflicted for remedy purposes (see
 * Services/Astrology/Remedies/RemedyEngine): debilitated, combust, placed
 * in a sign ruled by a natural enemy, or aspected by a natural malefic
 * (Saturn/Mars/Rahu/Ketu).
 *
 * The natural friendship/enmity table (Naisargika Maitri) below is the
 * classical Parashari one. Rahu/Ketu have no classical entry of their own
 * in most texts; this uses Saturn's table for both, a commonly-cited
 * simplification (their behavior is broadly Saturn-like) rather than
 * settled doctrine.
 */
class AfflictionDetector
{
    private const NATURAL_ENEMIES = [
        'Sun' => ['Venus', 'Saturn'],
        'Moon' => [],
        'Mars' => ['Mercury'],
        'Mercury' => ['Moon'],
        'Jupiter' => ['Mercury', 'Venus'],
        'Venus' => ['Sun', 'Moon'],
        'Saturn' => ['Sun', 'Moon', 'Mars'],
        'Rahu' => ['Sun', 'Moon', 'Mars'],
        'Ketu' => ['Sun', 'Moon', 'Mars'],
    ];

    private const MALEFICS = ['Saturn', 'Mars', 'Rahu', 'Ketu'];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return list<string>
     */
    public static function afflictions(string $planet, array $houses, array $planetLongitudes): array
    {
        $reasons = [];
        $sign = HouseLords::signOfPlanet($planet, $houses);
        $placement = HouseLords::houseContainingPlanet($planet, $houses);

        if ($sign !== null && PlanetaryDignity::isDebilitated($planet, $sign)) {
            $reasons[] = 'debilitated';
        }

        if (PlanetaryDignity::isCombust($planet, $planetLongitudes)) {
            $reasons[] = 'combust';
        }

        if ($sign !== null && in_array(HouseLords::SIGN_RULERS[$sign], self::NATURAL_ENEMIES[$planet] ?? [], true)) {
            $reasons[] = 'enemy_sign';
        }

        if ($placement !== null && self::aspectedByMalefic($planet, $placement, $houses)) {
            $reasons[] = 'aspected_by_malefic';
        }

        return $reasons;
    }

    public static function isAfflicted(string $planet, array $houses, array $planetLongitudes): bool
    {
        return self::afflictions($planet, $houses, $planetLongitudes) !== [];
    }

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     */
    private static function aspectedByMalefic(string $planet, int $placement, array $houses): bool
    {
        foreach (self::MALEFICS as $malefic) {
            if ($malefic === $planet) {
                continue;
            }

            $maleficPlacement = HouseLords::houseContainingPlanet($malefic, $houses);

            if ($maleficPlacement !== null && Aspects::aspectsHouse($malefic, $maleficPlacement, $placement)) {
                return true;
            }
        }

        return false;
    }
}
