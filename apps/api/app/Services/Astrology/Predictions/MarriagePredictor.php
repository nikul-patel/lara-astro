<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\PlanetaryDignity;
use App\Services\Astrology\Predictions\Templates\MarriageTemplates;

/**
 * Marriage/partnership prediction, driven entirely by the 7th-house lord —
 * the classical significator of marriage and partnerships — since the
 * birth chart carries no gender field to branch Venus-vs-Jupiter
 * significator logic on. Picks the single strongest matching signal
 * rather than combining every possible factor (see the implementation
 * plan's "content curation scope" risk).
 */
class MarriagePredictor
{
    private const KENDRA_TRIKONA = [1, 4, 5, 7, 9, 10];

    private const DUSTHANA = [6, 8, 12];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array{key: string, text: string}
     */
    public static function predict(array $houses): array
    {
        $lord = HouseLords::lordOfHouse(7, $houses);
        $sign = HouseLords::signOfPlanet($lord, $houses);
        $house = HouseLords::houseContainingPlanet($lord, $houses);

        [$key, $slots] = match (true) {
            $sign !== null && PlanetaryDignity::isExalted($lord, $sign) => ['seventh_lord_exalted', ['lord' => $lord, 'sign' => $sign]],
            $sign !== null && PlanetaryDignity::isDebilitated($lord, $sign) => ['seventh_lord_debilitated', ['lord' => $lord, 'sign' => $sign]],
            $house !== null && in_array($house, self::KENDRA_TRIKONA, true) => ['seventh_lord_kendra_trikona', ['lord' => $lord, 'house' => $house]],
            $house !== null && in_array($house, self::DUSTHANA, true) => ['seventh_lord_dusthana', ['lord' => $lord, 'house' => $house]],
            default => ['seventh_lord_default', ['lord' => $lord, 'sign' => $sign, 'house' => $house]],
        };

        return [
            'key' => $key,
            'text' => TemplateRenderer::render(MarriageTemplates::TEMPLATES['en'][$key], $slots),
        ];
    }
}
