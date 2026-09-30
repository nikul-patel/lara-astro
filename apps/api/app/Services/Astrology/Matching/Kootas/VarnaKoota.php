<?php

namespace App\Services\Astrology\Matching\Kootas;

use App\Services\Astrology\Matching\NakshatraAttributes;

/**
 * Varna Koota (1 point): compares the spiritual/temperament rank implied
 * by each partner's Moon-sign varna (Brahmin > Kshatriya > Vaishya >
 * Shudra). Classically the point is scored when the bride's varna does not
 * outrank the groom's — a hierarchy check, not a symmetric compatibility
 * one.
 */
class VarnaKoota
{
    public const MAX_POINTS = 1;

    /**
     * @return array{name: string, points: float, max_points: float, description: string}
     */
    public static function evaluate(string $brideRashi, string $groomRashi): array
    {
        $brideVarna = NakshatraAttributes::varnaOf($brideRashi);
        $groomVarna = NakshatraAttributes::varnaOf($groomRashi);

        $brideRank = array_search($brideVarna, NakshatraAttributes::VARNA_RANK, true);
        $groomRank = array_search($groomVarna, NakshatraAttributes::VARNA_RANK, true);

        // Lower array index = higher varna rank, so the groom qualifies
        // when his rank is at least as high (index at most as large).
        $points = $groomRank <= $brideRank ? 1.0 : 0.0;

        return [
            'name' => 'Varna',
            'points' => $points,
            'max_points' => self::MAX_POINTS,
            'description' => $points === 1
                ? "Groom's varna ({$groomVarna}) is equal to or higher than bride's varna ({$brideVarna})."
                : "Groom's varna ({$groomVarna}) ranks below bride's varna ({$brideVarna}), classically a minor spiritual-compatibility concern.",
        ];
    }
}
