<?php

namespace App\Services\Astrology\Doshas;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\HouseLords;

/**
 * Kaal Sarp Dosha: all 7 classical planets (Sun, Moon, Mars, Mercury,
 * Jupiter, Venus, Saturn — the lunar nodes Rahu/Ketu don't count
 * themselves) hemmed within the same 180° zodiacal half between Rahu and
 * Ketu, with none on the opposite side. Classically read as constricting
 * (though not universally negative), and one of the most commonly
 * requested standalone dosha reports.
 *
 * Type: once the hemmed condition holds, classical texts name 12 variants
 * purely by which house Rahu occupies from the Ascendant — the arc
 * direction (Rahu-leading vs Ketu-leading) doesn't affect the name, only
 * whether the dosha is present at all. This checks the standard "full"
 * condition only; some traditions also recognize a partial/"Kevala" Kaal
 * Sarp when just one planet breaks the hem — not implemented here, a
 * stated scope simplification rather than a silent gap.
 */
class KaalSarpDosha
{
    private const CLASSICAL_PLANETS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];

    /**
     * The 12 classically-named types, indexed by the house (1-12) Rahu
     * occupies from the Ascendant.
     */
    private const TYPE_NAMES = [
        1 => 'Anant', 2 => 'Kulik', 3 => 'Vasuki', 4 => 'Shankhpal', 5 => 'Padma', 6 => 'Mahapadma',
        7 => 'Takshak', 8 => 'Karkotak', 9 => 'Shankhachud', 10 => 'Ghatak', 11 => 'Vishdhar', 12 => 'Sheshnag',
    ];

    /**
     * @param  array<string, float>  $chartLongitudes  Sidereal longitudes keyed by planet name (ChartAssembler's `chart_longitudes`).
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array{present: bool, type: ?string, rahu_house: ?int, description: string}
     */
    public static function detect(array $chartLongitudes, array $houses): array
    {
        $rahu = $chartLongitudes['Rahu'];
        $ketu = $chartLongitudes['Ketu'];

        $sides = [];
        foreach (self::CLASSICAL_PLANETS as $planet) {
            $sides[] = AstroMath::normalizeDegrees($chartLongitudes[$planet] - $rahu) < 180 ? 'rahu_side' : 'ketu_side';
        }

        $present = count(array_unique($sides)) === 1;
        $rahuHouse = HouseLords::houseContainingPlanet('Rahu', $houses);
        $type = $present && $rahuHouse !== null ? self::TYPE_NAMES[$rahuHouse] : null;

        return [
            'present' => $present,
            'type' => $type,
            'rahu_house' => $rahuHouse,
            'description' => $present
                ? "Kaal Sarp Dosha present ({$type}): all seven classical planets are hemmed between Rahu and Ketu, with Rahu in house {$rahuHouse}."
                : 'No Kaal Sarp Dosha: at least one classical planet falls outside the Rahu-Ketu hemmed arc.',
        ];
    }
}
