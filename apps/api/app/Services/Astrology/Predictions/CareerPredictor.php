<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\PlanetaryDignity;
use App\Services\Astrology\Predictions\Templates\CareerTemplates;

/**
 * Career/job/business prediction, driven by the 10th-house lord — the
 * classical significator of career and public standing — checked first
 * against any detected Yogas (Services/Astrology/YogaEngine) touching the
 * 10th house, since a yoga is a stronger signal than dignity alone.
 */
class CareerPredictor
{
    private const KENDRA_TRIKONA = [1, 4, 5, 7, 9, 10];

    private const DUSTHANA = [6, 8, 12];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @param  list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>  $yogas
     * @return array{key: string, text: string}
     */
    public static function predict(array $houses, array $yogas): array
    {
        $lord = HouseLords::lordOfHouse(10, $houses);
        $sign = HouseLords::signOfPlanet($lord, $houses);
        $house = HouseLords::houseContainingPlanet($lord, $houses);

        $tenthHouseYoga = collect($yogas)->first(fn (array $yoga) => in_array(10, $yoga['houses'], true));

        [$key, $slots] = match (true) {
            $tenthHouseYoga !== null => ['tenth_lord_yoga', ['lord' => $lord, 'yogaName' => $tenthHouseYoga['name']]],
            $sign !== null && PlanetaryDignity::isExalted($lord, $sign) => ['tenth_lord_exalted', ['lord' => $lord, 'sign' => $sign]],
            $sign !== null && PlanetaryDignity::isDebilitated($lord, $sign) => ['tenth_lord_debilitated', ['lord' => $lord, 'sign' => $sign]],
            $house !== null && in_array($house, self::KENDRA_TRIKONA, true) => ['tenth_lord_kendra_trikona', ['lord' => $lord, 'house' => Ordinal::suffix($house)]],
            $house !== null && in_array($house, self::DUSTHANA, true) => ['tenth_lord_dusthana', ['lord' => $lord, 'house' => Ordinal::suffix($house)]],
            default => ['tenth_lord_default', ['lord' => $lord, 'sign' => $sign, 'house' => $house !== null ? Ordinal::suffix($house) : '']],
        };

        return [
            'key' => $key,
            'text' => TemplateRenderer::render(CareerTemplates::TEMPLATES['en'][$key], $slots),
        ];
    }
}
