<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\Predictions\Templates\NakshatraTemplates;

/**
 * Nakshatra Phal: temperament, career/education inclination, and family-
 * life description keyed by the Moon's birth nakshatra — a straight lookup
 * by nakshatra name, same shape as AscendantPredictor (each of the 27
 * descriptions in NakshatraTemplates is already complete prose, no
 * chart-specific slots to interpolate).
 */
class NakshatraPredictor
{
    /**
     * @return array{key: string, text: string}
     */
    public static function describe(string $nakshatraName): array
    {
        return [
            'key' => $nakshatraName,
            'text' => NakshatraTemplates::TEMPLATES['en'][$nakshatraName],
        ];
    }
}
