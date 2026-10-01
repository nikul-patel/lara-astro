<?php

namespace App\Services\Astrology\Matching\Kootas;

/**
 * Tara Koota (3 points): counts each partner's nakshatra distance from the
 * other's (inclusive count, 1-9, wrapping through the 27 nakshatras taken
 * as 3 cycles of 9) and checks whether that count lands on one of the 3
 * classically inauspicious taras (3rd Vipat, 5th Pratyak, 7th Vadha) out of
 * 9. Both directions are checked independently since the count is not
 * symmetric.
 */
class TaraKoota
{
    public const MAX_POINTS = 3;

    private const INAUSPICIOUS_TARAS = [3, 5, 7];

    /**
     * @return array{name: string, points: float, max_points: float, description: string}
     */
    public static function evaluate(int $brideNakshatraIndex, int $groomNakshatraIndex): array
    {
        $brideToGroom = self::taraNumber($brideNakshatraIndex, $groomNakshatraIndex);
        $groomToBride = self::taraNumber($groomNakshatraIndex, $brideNakshatraIndex);

        $brideFavourable = ! in_array($brideToGroom, self::INAUSPICIOUS_TARAS, true);
        $groomFavourable = ! in_array($groomToBride, self::INAUSPICIOUS_TARAS, true);

        $points = match (true) {
            $brideFavourable && $groomFavourable => 3.0,
            $brideFavourable || $groomFavourable => 1.5,
            default => 0.0,
        };

        return [
            'name' => 'Tara',
            'points' => $points,
            'max_points' => self::MAX_POINTS,
            'description' => "Bride-to-groom tara #{$brideToGroom} is ".($brideFavourable ? 'favourable' : 'inauspicious')
                .", groom-to-bride tara #{$groomToBride} is ".($groomFavourable ? 'favourable' : 'inauspicious').'.',
        ];
    }

    /** Inclusive count from $fromIndex to $toIndex around the 27 nakshatras, reduced mod 9 to a tara number in [1, 9]. */
    private static function taraNumber(int $fromIndex, int $toIndex): int
    {
        $count = (($toIndex - $fromIndex + 27) % 27) + 1;

        $tara = $count % 9;

        return $tara === 0 ? 9 : $tara;
    }
}
