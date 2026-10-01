<?php

namespace App\Services\Astrology\Matching\Kootas;

use App\Services\Astrology\Matching\NakshatraAttributes;

/**
 * Nadi Koota (8 points): the single heaviest-weighted koota, checking
 * whether both partners share the same constitutional Nadi (Aadi/Vata,
 * Madhya/Pitta, Antya/Kapha). Same Nadi is classically Nadi Dosha —
 * considered to affect health/progeny of offspring — and scores zero
 * regardless of every other koota's outcome; different Nadi scores full
 * marks.
 */
class NadiKoota
{
    public const MAX_POINTS = 8;

    /**
     * @return array{name: string, points: float, max_points: float, description: string}
     */
    public static function evaluate(string $brideNakshatra, string $groomNakshatra): array
    {
        $brideNadi = NakshatraAttributes::nadiOf($brideNakshatra);
        $groomNadi = NakshatraAttributes::nadiOf($groomNakshatra);

        $sameNadi = $brideNadi === $groomNadi;

        return [
            'name' => 'Nadi',
            'points' => $sameNadi ? 0.0 : (float) self::MAX_POINTS,
            'max_points' => self::MAX_POINTS,
            'description' => $sameNadi
                ? "Both partners share the {$brideNadi} Nadi — classical Nadi Dosha, the most significant of the eight kootas."
                : "Bride's Nadi ({$brideNadi}) differs from groom's ({$groomNadi}), clear of Nadi Dosha.",
        ];
    }
}
