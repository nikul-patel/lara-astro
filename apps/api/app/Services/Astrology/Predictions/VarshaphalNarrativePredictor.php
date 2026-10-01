<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\Predictions\Templates\HouseSignifications;
use App\Services\Astrology\Predictions\Templates\VarshaphalNarrativeTemplates;

/**
 * Attaches a narrative paragraph to each period of a Mudda Dasha timeline
 * (YearlyForecast\MuddaDasha::timeline()) — same "9 lords, house
 * placement interpolated" approach as DashaNarrativePredictor, templated
 * per Mudda-Dasha-lord x Bhava-number per the originating issue (#79).
 */
class VarshaphalNarrativePredictor
{
    /**
     * @param  list<array{lord: string, start: string, end: string, house: int}>  $muddaDashaTimeline
     * @return list<array{lord: string, start: string, end: string, house: int, text: string}>
     */
    public static function annotate(array $muddaDashaTimeline): array
    {
        return array_map(
            fn (array $period) => $period + [
                'text' => TemplateRenderer::render(VarshaphalNarrativeTemplates::TEMPLATES['en'][$period['lord']], [
                    'house' => $period['house'],
                    'signification' => HouseSignifications::SIGNIFICATION[$period['house']],
                ]),
            ],
            $muddaDashaTimeline
        );
    }
}
