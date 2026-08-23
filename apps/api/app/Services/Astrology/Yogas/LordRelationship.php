<?php

namespace App\Services\Astrology\Yogas;

use App\Services\Astrology\Aspects;
use App\Services\Astrology\HouseLords;

/**
 * Whether two house lords are "linked" in the sense classical yoga rules
 * (Raj yoga, Dhana yoga, etc.) require: conjunction (posited together),
 * parivartana/exchange (each sits in the house the other rules), or mutual
 * aspect. Shared by every detector in this namespace that follows the
 * "kendra lord + trikona lord" or "house-signifier lord + house-signifier
 * lord" pattern rather than duplicating the relationship logic per yoga.
 */
class LordRelationship
{
    /**
     * @param  int  $houseA  the house $planetA rules (its signification, not necessarily where it sits)
     * @param  int  $houseB  the house $planetB rules
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     */
    public static function linked(string $planetA, int $houseA, string $planetB, int $houseB, array $houses): bool
    {
        if ($planetA === $planetB) {
            return false;
        }

        $placementA = HouseLords::houseContainingPlanet($planetA, $houses);
        $placementB = HouseLords::houseContainingPlanet($planetB, $houses);

        if ($placementA === null || $placementB === null) {
            return false;
        }

        if ($placementA === $placementB) {
            return true; // conjunction
        }

        if ($placementA === $houseB && $placementB === $houseA) {
            return true; // parivartana (exchange)
        }

        return Aspects::aspectsHouse($planetA, $placementA, $placementB)
            || Aspects::aspectsHouse($planetB, $placementB, $placementA);
    }
}
