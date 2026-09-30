<?php

namespace App\Services\Astrology\Matching\Kootas;

use App\Services\Astrology\Matching\NakshatraAttributes;

/**
 * Gana Koota (6 points): compares each partner's nakshatra temperament
 * class (Deva/divine, Manushya/human, Rakshasa/demonic). Uses the standard
 * classical scoring matrix, which is asymmetric — a Manushya groom with a
 * Deva bride scores higher than the reverse.
 */
class GanaKoota
{
    public const MAX_POINTS = 6;

    /** @var array<string, array<string, float>> Keyed [groomGana][brideGana]. */
    private const SCORES = [
        'Deva' => ['Deva' => 6.0, 'Manushya' => 5.0, 'Rakshasa' => 1.0],
        'Manushya' => ['Deva' => 6.0, 'Manushya' => 6.0, 'Rakshasa' => 0.0],
        'Rakshasa' => ['Deva' => 1.0, 'Manushya' => 0.0, 'Rakshasa' => 6.0],
    ];

    /**
     * @return array{name: string, points: float, max_points: float, description: string}
     */
    public static function evaluate(string $brideNakshatra, string $groomNakshatra): array
    {
        $brideGana = NakshatraAttributes::ganaOf($brideNakshatra);
        $groomGana = NakshatraAttributes::ganaOf($groomNakshatra);

        $points = self::SCORES[$groomGana][$brideGana];

        return [
            'name' => 'Gana',
            'points' => $points,
            'max_points' => self::MAX_POINTS,
            'description' => "Bride's Gana ({$brideGana}) and groom's ({$groomGana}) score {$points}/6 on temperament compatibility.",
        ];
    }
}
