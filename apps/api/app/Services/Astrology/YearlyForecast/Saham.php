<?php

namespace App\Services\Astrology\YearlyForecast;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\ZodiacSigns;

/**
 * Saham (sensitive point) computation for a Varshphal (solar-return)
 * chart: `Ascendant + PlanetA - PlanetB`, with the two planets swapped for
 * a night birth versus a day birth — the general Arabic-parts formula
 * pattern classical Tajika texts use throughout.
 *
 * Full classical Varshaphala texts list on the order of 16-32 Sahams, each
 * with its own textually-attested significator pair (and some with
 * further formula variants beyond the simple day/night swap). This
 * implements a curated ~8 of the most commonly referenced ones with the
 * standard swap rule — a stated simplification of scope, not a verified
 * transcription of every classical variant.
 */
class Saham
{
    private const FORMULAS = [
        'punya' => ['name' => 'Punya Saham (Fortune)', 'planet_a' => 'Moon', 'planet_b' => 'Sun'],
        'vidya' => ['name' => 'Vidya Saham (Education)', 'planet_a' => 'Jupiter', 'planet_b' => 'Mercury'],
        'yasha' => ['name' => 'Yasha Saham (Fame)', 'planet_a' => 'Jupiter', 'planet_b' => 'Sun'],
        'mitra' => ['name' => 'Mitra Saham (Friendship)', 'planet_a' => 'Venus', 'planet_b' => 'Mercury'],
        'mahatmya' => ['name' => 'Mahatmya Saham (Honor)', 'planet_a' => 'Sun', 'planet_b' => 'Saturn'],
        'karma' => ['name' => 'Karma Saham (Career)', 'planet_a' => 'Saturn', 'planet_b' => 'Mercury'],
        'rogha' => ['name' => 'Rogha Saham (Health)', 'planet_a' => 'Saturn', 'planet_b' => 'Moon'],
        'vivaha' => ['name' => 'Vivaha Saham (Marriage)', 'planet_a' => 'Venus', 'planet_b' => 'Jupiter'],
    ];

    /**
     * @param  array<string, float>  $planetLongitudes
     * @return list<array{key: string, name: string, longitude: float, sign: string}>
     */
    public static function compute(float $ascendantLongitude, array $planetLongitudes, bool $isDayBirth): array
    {
        $results = [];

        foreach (self::FORMULAS as $key => $formula) {
            [$a, $b] = $isDayBirth
                ? [$formula['planet_a'], $formula['planet_b']]
                : [$formula['planet_b'], $formula['planet_a']];

            $longitude = AstroMath::normalizeDegrees($ascendantLongitude + $planetLongitudes[$a] - $planetLongitudes[$b]);

            $results[] = [
                'key' => $key,
                'name' => $formula['name'],
                'longitude' => round($longitude, 4),
                'sign' => ZodiacSigns::forLongitude($longitude),
            ];
        }

        return $results;
    }
}
