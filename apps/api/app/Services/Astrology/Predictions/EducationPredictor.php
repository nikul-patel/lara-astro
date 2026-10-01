<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\PlanetaryDignity;
use App\Services\Astrology\Predictions\Templates\EducationTemplates;

/**
 * Education prediction, driven by the 5th-house lord (intellect, academic
 * achievement) as the primary signal and the 4th-house lord (foundational
 * schooling) as the secondary one — checked in that order of precedence.
 */
class EducationPredictor
{
    private const KENDRA_TRIKONA = [1, 4, 5, 7, 9, 10];

    private const DUSTHANA = [6, 8, 12];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return array{key: string, text: string}
     */
    public static function predict(array $houses): array
    {
        $lord5 = HouseLords::lordOfHouse(5, $houses);
        $sign5 = HouseLords::signOfPlanet($lord5, $houses);
        $house5 = HouseLords::houseContainingPlanet($lord5, $houses);

        $lord4 = HouseLords::lordOfHouse(4, $houses);
        $sign4 = HouseLords::signOfPlanet($lord4, $houses);
        $house4 = HouseLords::houseContainingPlanet($lord4, $houses);

        // Dignity (exaltation/debilitation) is a more specific signal than
        // mere house strength, so it's checked before the kendra/trikona
        // fallback — a debilitated 5th lord shouldn't be reported as
        // favorable just because it happens to also sit in a kendra.
        [$key, $slots] = match (true) {
            $sign5 !== null && PlanetaryDignity::isExalted($lord5, $sign5) => ['fifth_lord_exalted', ['lord' => $lord5, 'sign' => $sign5]],
            $sign5 !== null && PlanetaryDignity::isDebilitated($lord5, $sign5) => ['fourth_or_fifth_debilitated', ['houseLabel' => '5th', 'lord' => $lord5, 'sign' => $sign5]],
            $sign4 !== null && PlanetaryDignity::isDebilitated($lord4, $sign4) => ['fourth_or_fifth_debilitated', ['houseLabel' => '4th', 'lord' => $lord4, 'sign' => $sign4]],
            $house5 !== null && in_array($house5, self::KENDRA_TRIKONA, true) => ['fifth_lord_kendra_trikona', ['lord' => $lord5, 'house' => Ordinal::suffix($house5)]],
            $house5 !== null && in_array($house5, self::DUSTHANA, true) => ['fourth_or_fifth_dusthana', ['houseLabel' => '5th', 'lord' => $lord5, 'house' => Ordinal::suffix($house5)]],
            $house4 !== null && in_array($house4, self::DUSTHANA, true) => ['fourth_or_fifth_dusthana', ['houseLabel' => '4th', 'lord' => $lord4, 'house' => Ordinal::suffix($house4)]],
            default => ['default', ['fourthLord' => $lord4, 'fifthLord' => $lord5]],
        };

        return [
            'key' => $key,
            'text' => TemplateRenderer::render(EducationTemplates::TEMPLATES['en'][$key], $slots),
        ];
    }
}
