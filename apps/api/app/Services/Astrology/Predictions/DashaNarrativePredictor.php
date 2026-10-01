<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\Nakshatra;
use App\Services\Astrology\Predictions\Templates\DashaNarrativeTemplates;
use App\Services\Astrology\Predictions\Templates\HouseSignifications;

/**
 * Per-Mahadasha-lord narrative text for the whole natal Vimshottari
 * timeline (VimshottariDasha::timeline()) — the issue behind this class
 * (#79) flagged a genuine scope choice: a full cross-product of 9 dasha
 * lords x 12 possible house placements is 108 entries, almost all of
 * which would need to be hand-written bespoke prose to be worth reading.
 * This codebase instead templates ONE paragraph per lord (9 total, in
 * DashaNarrativeTemplates) describing that graha's classical Mahadasha
 * effects, with the lord's actual natal house placement and that house's
 * own classical signification (HouseSignifications) interpolated in —
 * genuinely chart-specific without needing 108 bespoke paragraphs.
 *
 * A lord's house placement is fixed per natal chart (a planet sits in
 * exactly one house), so this returns one entry per lord covering the
 * whole dasha cycle, rather than per Mahadasha-timeline date range.
 */
class DashaNarrativePredictor
{
    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return list<array{lord: string, house: int, text: string}>
     */
    public static function generate(array $houses): array
    {
        $narratives = [];

        foreach (Nakshatra::LORD_CYCLE as $lord) {
            $house = HouseLords::houseContainingPlanet($lord, $houses);

            $narratives[] = [
                'lord' => $lord,
                'house' => $house,
                'text' => TemplateRenderer::render(DashaNarrativeTemplates::TEMPLATES['en'][$lord], [
                    'house' => $house,
                    'signification' => HouseSignifications::SIGNIFICATION[$house],
                ]),
            ];
        }

        return $narratives;
    }
}
