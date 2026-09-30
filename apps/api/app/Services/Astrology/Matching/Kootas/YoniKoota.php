<?php

namespace App\Services\Astrology\Matching\Kootas;

use App\Services\Astrology\Matching\NakshatraAttributes;

/**
 * Yoni Koota (4 points): compares the animal symbol each partner's
 * nakshatra is classically associated with — a proxy for sexual/physical
 * compatibility. Full marks for the same animal, zero for a classical
 * natural-enemy pair (NakshatraAttributes::YONI_ENEMIES), 2/4 (neutral)
 * otherwise. Classical tables also distinguish male/female-symbol same-
 * animal pairs and a wider friend/neutral gradient; this implementation
 * keeps the three cases that matter most for the final score (identical,
 * enemy, everything else) — the same simplification pattern as
 * VashyaKoota.
 */
class YoniKoota
{
    public const MAX_POINTS = 4;

    /**
     * @return array{name: string, points: float, max_points: float, description: string}
     */
    public static function evaluate(string $brideNakshatra, string $groomNakshatra): array
    {
        $brideYoni = NakshatraAttributes::yoniOf($brideNakshatra);
        $groomYoni = NakshatraAttributes::yoniOf($groomNakshatra);

        if ($brideYoni === $groomYoni) {
            $points = 4.0;
            $note = 'identical yoni';
        } elseif (self::areEnemies($brideYoni, $groomYoni)) {
            $points = 0.0;
            $note = 'natural-enemy yonis';
        } else {
            $points = 2.0;
            $note = 'neutral yonis';
        }

        return [
            'name' => 'Yoni',
            'points' => $points,
            'max_points' => self::MAX_POINTS,
            'description' => "Bride's yoni ({$brideYoni}) and groom's ({$groomYoni}): {$note}.",
        ];
    }

    private static function areEnemies(string $a, string $b): bool
    {
        foreach (NakshatraAttributes::YONI_ENEMIES as [$x, $y]) {
            if (($a === $x && $b === $y) || ($a === $y && $b === $x)) {
                return true;
            }
        }

        return false;
    }
}
