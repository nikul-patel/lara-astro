<?php

namespace App\Services\Astrology\Matching;

use App\Services\Astrology\Matching\Kootas\BhakootKoota;
use App\Services\Astrology\Matching\Kootas\GanaKoota;
use App\Services\Astrology\Matching\Kootas\GrahaMaitriKoota;
use App\Services\Astrology\Matching\Kootas\NadiKoota;
use App\Services\Astrology\Matching\Kootas\TaraKoota;
use App\Services\Astrology\Matching\Kootas\VarnaKoota;
use App\Services\Astrology\Matching\Kootas\VashyaKoota;
use App\Services\Astrology\Matching\Kootas\YoniKoota;

/**
 * Ashtakoot Guna Milan: the classical 8-koota (36-point) Vedic marriage
 * compatibility system, scored purely from each partner's Moon nakshatra
 * and Moon sign (no other chart factors — matching classical practice,
 * where Ashtakoot is explicitly a Moon-based screening method, distinct
 * from the fuller chart review a matchmaking consultation would add).
 * Orchestrates the 8 independent koota classes the same way YogaEngine and
 * DoshaEngine orchestrate their detectors.
 */
class AshtakootMatcher
{
    public const MAX_POINTS = 36;

    /**
     * Classical guidance: 18+ is considered an acceptable match, below 18
     * is generally discouraged, and Nadi or Bhakoot Dosha (a 0 in either
     * koota) is often treated as a veto even when the total clears 18 —
     * both are surfaced here rather than collapsed into the score alone.
     */
    private const MINIMUM_RECOMMENDED_SCORE = 18;

    /**
     * @param  array{index: int, name: string}  $brideNakshatra
     * @param  array{index: int, name: string}  $groomNakshatra
     * @return array{
     *     total_points: float,
     *     max_points: float,
     *     minimum_recommended: float,
     *     is_recommended: bool,
     *     has_nadi_dosha: bool,
     *     has_bhakoot_dosha: bool,
     *     kootas: list<array{name: string, points: float, max_points: float, description: string}>,
     * }
     */
    public static function match(array $brideNakshatra, string $brideRashi, array $groomNakshatra, string $groomRashi): array
    {
        $kootas = [
            VarnaKoota::evaluate($brideRashi, $groomRashi),
            VashyaKoota::evaluate($brideRashi, $groomRashi),
            TaraKoota::evaluate($brideNakshatra['index'], $groomNakshatra['index']),
            YoniKoota::evaluate($brideNakshatra['name'], $groomNakshatra['name']),
            GrahaMaitriKoota::evaluate($brideRashi, $groomRashi),
            GanaKoota::evaluate($brideNakshatra['name'], $groomNakshatra['name']),
            BhakootKoota::evaluate($brideRashi, $groomRashi),
            NadiKoota::evaluate($brideNakshatra['name'], $groomNakshatra['name']),
        ];

        $total = array_sum(array_column($kootas, 'points'));
        $nadiDosha = collect($kootas)->firstWhere('name', 'Nadi')['points'] == 0;
        $bhakootDosha = collect($kootas)->firstWhere('name', 'Bhakoot')['points'] == 0;

        return [
            'total_points' => $total,
            'max_points' => self::MAX_POINTS,
            'minimum_recommended' => self::MINIMUM_RECOMMENDED_SCORE,
            'is_recommended' => $total >= self::MINIMUM_RECOMMENDED_SCORE && ! $nadiDosha,
            'has_nadi_dosha' => $nadiDosha,
            'has_bhakoot_dosha' => $bhakootDosha,
            'kootas' => $kootas,
        ];
    }
}
