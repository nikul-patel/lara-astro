<?php

namespace App\Services\Astrology\Matching\Kootas;

use App\Services\Astrology\ZodiacSigns;

/**
 * Bhakoot Koota (7 points): checks the sign-distance between the partners'
 * Moon signs for the three classical Bhakoot Dosha patterns — 2/12
 * (Dvirdvadash), 5/9 (Navapancham), 6/8 (Shadashtak) — any of which zeroes
 * the point; all other distances score full marks.
 */
class BhakootKoota
{
    public const MAX_POINTS = 7;

    private const DOSHA_OFFSETS = [2, 12, 5, 9, 6, 8];

    /**
     * @return array{name: string, points: float, max_points: float, description: string}
     */
    public static function evaluate(string $brideRashi, string $groomRashi): array
    {
        $offset = ZodiacSigns::offset($brideRashi, $groomRashi);
        $hasDosha = in_array($offset, self::DOSHA_OFFSETS, true);

        return [
            'name' => 'Bhakoot',
            'points' => $hasDosha ? 0.0 : (float) self::MAX_POINTS,
            'max_points' => self::MAX_POINTS,
            'description' => $hasDosha
                ? "Bride's and groom's Moon signs are {$offset} signs apart, one of the classical Bhakoot Dosha distances (2/12, 5/9, 6/8)."
                : "Bride's and groom's Moon signs are {$offset} signs apart, clear of the classical Bhakoot Dosha distances.",
        ];
    }
}
