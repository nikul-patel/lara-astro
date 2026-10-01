<?php

namespace App\Services\Astrology\Shadbala;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Panchang\Tithi;

/**
 * Drik Bala ("aspectual strength"): net strength a planet gains from every
 * other classical planet's Parashari graha-drishti (aspect) on it —
 * benefic aspects add, malefic aspects subtract, the total divided by 4.
 *
 * Every planet has a full-strength (60 Virupa) aspect on the planet
 * exactly opposite it (the 7th house away, 180°), falling off linearly on
 * either side to 0 at 30° and 300°; Mars, Jupiter, and Saturn additionally
 * get full-strength "special" aspects at their classical extra houses
 * (Mars: 4th/8th at 90°/210°; Jupiter: 5th/9th at 120°/240°; Saturn:
 * 3rd/10th at 60°/270°) — the piecewise formula below is built so every
 * special-aspect zone smoothly reaches exactly 60 at that exact angle
 * (verified by hand at every breakpoint: continuous at 30°→60°→90°→120°→
 * 150°→180°→300°, see test fixtures). Formula per the BPHS Ch.26
 * tabulation cross-checked against the PyJHora reference implementation
 * (see SthanaBala's doc comment for the citation).
 *
 * Benefic/malefic classification is this engine's established
 * simplification (same one Kala Bala's Paksha Bala uses): Mercury,
 * Jupiter, Venus, and a waxing Moon count as benefic; Sun, Mars, Saturn,
 * and a waning Moon as malefic — not the fuller classical rule where
 * Mercury's nature depends on its companions.
 */
class DrikBala
{
    private const PLANETS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];

    /**
     * @param  array<string, float>  $chartLongitudes
     * @return array<string, float> Each planet's net aspectual strength (can be negative).
     */
    public static function calculate(array $chartLongitudes): array
    {
        $benefics = self::benefics($chartLongitudes);

        $net = array_fill_keys(self::PLANETS, 0.0);

        foreach (self::PLANETS as $aspected) {
            foreach (self::PLANETS as $aspecting) {
                if ($aspected === $aspecting) {
                    continue;
                }

                $angle = AstroMath::normalizeDegrees($chartLongitudes[$aspected] - $chartLongitudes[$aspecting]);
                $strength = self::aspectStrength($angle, $aspecting);

                $net[$aspected] += in_array($aspecting, $benefics, true) ? $strength : -$strength;
            }
        }

        return array_map(fn (float $v) => round($v / 4, 2), $net);
    }

    private static function aspectStrength(float $angle, string $aspectingPlanet): float
    {
        $strength = match (true) {
            $angle < 30 => 0.0,
            $angle < 60 => 0.5 * ($angle - 30),
            $angle < 90 => ($angle - 60) + 15,
            $angle < 120 => 0.5 * (120 - $angle) + 30,
            $angle < 150 => 150 - $angle,
            $angle < 180 => 2 * ($angle - 150),
            $angle < 300 => 0.5 * (300 - $angle),
            default => 0.0,
        };

        if ($aspectingPlanet === 'Saturn' && (($angle >= 60 && $angle < 90) || ($angle >= 270 && $angle < 300))) {
            $strength += 45;
        }
        if ($aspectingPlanet === 'Mars' && (($angle >= 90 && $angle < 120) || ($angle >= 210 && $angle < 240))) {
            $strength += 15;
        }
        if ($aspectingPlanet === 'Jupiter' && (($angle >= 120 && $angle < 150) || ($angle >= 240 && $angle < 270))) {
            $strength += 30;
        }

        return min(60.0, $strength);
    }

    /**
     * @param  array<string, float>  $chartLongitudes
     * @return list<string>
     */
    private static function benefics(array $chartLongitudes): array
    {
        $benefics = ['Mercury', 'Jupiter', 'Venus'];
        $waxing = Tithi::forLongitudes($chartLongitudes['Sun'], $chartLongitudes['Moon'])['paksha'] === 'Shukla';

        if ($waxing) {
            $benefics[] = 'Moon';
        }

        return $benefics;
    }
}
