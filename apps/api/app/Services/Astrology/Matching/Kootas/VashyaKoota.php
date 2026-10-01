<?php

namespace App\Services\Astrology\Matching\Kootas;

use App\Services\Astrology\Matching\NakshatraAttributes;

/**
 * Vashya Koota (2 points): compares each partner's Moon-sign Vashya
 * (dominance/temperament) group. Classical texts score specific cross-group
 * pairs at partial credit via a detailed compatibility chart; this
 * implementation documents a simplified version of that chart — full marks
 * for the same group, half marks for the handful of pairs classical texts
 * most consistently call mutually workable, zero otherwise — consistent
 * with this codebase's "documented simplification, not exhaustive
 * classical coverage" convention (see Doshas\MangalDosha's cancellation
 * rules for the same pattern).
 */
class VashyaKoota
{
    public const MAX_POINTS = 2;

    /**
     * Unordered group pairs scored at half credit.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const PARTIAL_PAIRS = [
        ['Manav', 'Jalachar'],
        ['Chatushpada', 'Vanchar'],
        ['Manav', 'Chatushpada'],
    ];

    /**
     * @return array{name: string, points: float, max_points: float, description: string}
     */
    public static function evaluate(string $brideRashi, string $groomRashi): array
    {
        $brideGroup = NakshatraAttributes::vashyaOf($brideRashi);
        $groomGroup = NakshatraAttributes::vashyaOf($groomRashi);

        if ($brideGroup === $groomGroup) {
            $points = 2.0;
        } elseif (self::isPartialPair($brideGroup, $groomGroup)) {
            $points = 1.0;
        } else {
            $points = 0.0;
        }

        return [
            'name' => 'Vashya',
            'points' => $points,
            'max_points' => self::MAX_POINTS,
            'description' => "Bride's Vashya group ({$brideGroup}) and groom's ({$groomGroup}) score {$points}/2 on mutual dominance/temperament.",
        ];
    }

    private static function isPartialPair(string $a, string $b): bool
    {
        foreach (self::PARTIAL_PAIRS as [$x, $y]) {
            if (($a === $x && $b === $y) || ($a === $y && $b === $x)) {
                return true;
            }
        }

        return false;
    }
}
