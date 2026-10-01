<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\Predictions\Templates\AscendantTemplates;

/**
 * Lagna (Ascendant-sign) description: temperament, physical build, and
 * health tendencies — a straight lookup by Ascendant sign, since each of
 * the 12 descriptions in AscendantTemplates is already complete prose
 * with no chart-specific slots to interpolate (unlike the other
 * predictors in this namespace, which vary by a planet's dignity/house).
 */
class AscendantPredictor
{
    /**
     * @return array{key: string, text: string}
     */
    public static function describe(string $ascendantSign): array
    {
        return [
            'key' => $ascendantSign,
            'text' => AscendantTemplates::TEMPLATES['en'][$ascendantSign],
        ];
    }
}
