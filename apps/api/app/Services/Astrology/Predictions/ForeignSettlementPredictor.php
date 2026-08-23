<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\PlanetaryDignity;
use App\Services\Astrology\Predictions\Templates\ForeignSettlementTemplates;

/**
 * Foreign settlement / job-or-business-abroad prediction. Reuses
 * Services/Astrology/Yogas/ForeignSettlementIndicators's detections
 * (already present in the chart's yogas list) as the primary signal,
 * ordered by classical strength, before falling back to a plain 12th-lord
 * dignity check and finally a generic "no strong pull abroad" text.
 */
class ForeignSettlementPredictor
{
    private const PRIORITY_KEYS = [
        'foreign_settlement_rahu_12th' => 'rahu_12th',
        'foreign_settlement_moon_12th' => 'moon_12th',
        'foreign_settlement_9th_12th_link' => 'ninth_twelfth_link',
        'foreign_settlement_12th_lord_strong' => 'twelfth_lord_strong',
    ];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @param  list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>  $yogas
     * @return array{key: string, text: string}
     */
    public static function predict(array $houses, array $yogas): array
    {
        $byKey = collect($yogas)->keyBy('key');

        foreach (self::PRIORITY_KEYS as $yogaKey => $templateKey) {
            if (! $byKey->has($yogaKey)) {
                continue;
            }

            $yoga = $byKey->get($yogaKey);
            $slots = match ($templateKey) {
                'ninth_twelfth_link' => ['lord9' => $yoga['planets'][0], 'lord12' => $yoga['planets'][1]],
                'twelfth_lord_strong' => ['lord' => $yoga['planets'][0], 'house' => $yoga['houses'][1]],
                default => [],
            };

            return [
                'key' => $templateKey,
                'text' => TemplateRenderer::render(ForeignSettlementTemplates::TEMPLATES['en'][$templateKey], $slots),
            ];
        }

        $lord12 = HouseLords::lordOfHouse(12, $houses);
        $sign12 = HouseLords::signOfPlanet($lord12, $houses);

        if ($sign12 !== null && (PlanetaryDignity::isExalted($lord12, $sign12) || PlanetaryDignity::isOwnSign($lord12, $sign12))) {
            return [
                'key' => 'moderate',
                'text' => TemplateRenderer::render(ForeignSettlementTemplates::TEMPLATES['en']['moderate'], ['lord' => $lord12, 'sign' => $sign12]),
            ];
        }

        return [
            'key' => 'default',
            'text' => ForeignSettlementTemplates::TEMPLATES['en']['default'],
        ];
    }
}
