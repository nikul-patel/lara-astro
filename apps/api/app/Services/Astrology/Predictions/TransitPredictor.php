<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\Predictions\Templates\HouseSignifications;
use App\Services\Astrology\Predictions\Templates\TransitTemplates;

/**
 * Narrative text for each graha's current Gochar (transit), one entry per
 * planet — see TransitTemplates' doc comment for the "9 planets, house
 * interpolated" scope choice.
 */
class TransitPredictor
{
    /**
     * @param  array<string, array{sign: string, house_from_moon: int}>  $transits  Transit::forNatalMoon()'s output.
     * @return array<string, array{sign: string, house_from_moon: int, text: string}>
     */
    public static function generate(array $transits): array
    {
        $narratives = [];

        foreach ($transits as $planet => $transit) {
            $narratives[$planet] = $transit + [
                'text' => TemplateRenderer::render(TransitTemplates::TEMPLATES['en'][$planet], [
                    'house' => $transit['house_from_moon'],
                    'signification' => HouseSignifications::SIGNIFICATION[$transit['house_from_moon']],
                ]),
            ];
        }

        return $narratives;
    }
}
