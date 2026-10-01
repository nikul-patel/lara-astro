<?php

namespace App\Services\Astrology;

/**
 * Western-style major aspects (conjunction/sextile/square/trine/
 * opposition) between every pair of the chart's planets, based purely on
 * their angular separation — distinct from the Vedic "special aspects"
 * (Mars 3rd/7th/8th, Jupiter 5th/7th/9th, Saturn 3rd/7th/10th; see
 * {@see Aspects}) used internally for yoga/dosha detection, which work in
 * whole houses rather than exact degrees.
 *
 * Available for both Vedic and Western charts: unlike nakshatra/dasha/
 * yogas, "aspect by angular separation" isn't a sidereal-only concept —
 * it's computed from whichever longitudes (sidereal or tropical) the
 * requested system already produced.
 *
 * Documented simplification: classical Western practice varies orb width
 * by aspect type and by which planets are involved (tighter for minor
 * planets, wider for the Sun/Moon). This uses a single flat 6° orb for
 * every aspect and every planet pair — not the full variable-orb system —
 * and does not report applying/separating (which needs each planet's
 * daily motion; this engine's PlanetaryElements only exposes position, not
 * speed).
 */
class WesternAspects
{
    private const ORB_DEGREES = 6.0;

    /** Exact angle in degrees => aspect name. */
    private const ASPECTS = [
        0 => 'conjunction',
        60 => 'sextile',
        90 => 'square',
        120 => 'trine',
        180 => 'opposition',
    ];

    /**
     * @param  array<string, float>  $planetLongitudes
     * @return list<array{from: string, to: string, aspect: string, angle: float, orb: float}>
     */
    public static function detect(array $planetLongitudes): array
    {
        $planets = array_keys($planetLongitudes);
        $aspects = [];

        for ($i = 0; $i < count($planets); $i++) {
            for ($j = $i + 1; $j < count($planets); $j++) {
                $from = $planets[$i];
                $to = $planets[$j];

                $angle = self::angularSeparation($planetLongitudes[$from], $planetLongitudes[$to]);

                foreach (self::ASPECTS as $exactAngle => $name) {
                    $orb = abs($angle - $exactAngle);

                    if ($orb <= self::ORB_DEGREES) {
                        $aspects[] = [
                            'from' => $from,
                            'to' => $to,
                            'aspect' => $name,
                            'angle' => round($angle, 2),
                            'orb' => round($orb, 2),
                        ];

                        break; // the 5 exact angles are >= 60° apart and the orb is 6°, so at most one can match
                    }
                }
            }
        }

        return $aspects;
    }

    /** The shorter angular distance between two longitudes, in [0, 180]. */
    private static function angularSeparation(float $a, float $b): float
    {
        $diff = AstroMath::normalizeDegrees($b - $a);

        return $diff > 180 ? 360 - $diff : $diff;
    }
}
