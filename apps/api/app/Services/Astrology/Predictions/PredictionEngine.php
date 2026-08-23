<?php

namespace App\Services\Astrology\Predictions;

/**
 * Orchestrates the 4 life-area predictors against a computed whole-sign
 * house chart and its already-detected yogas (Services/Astrology/YogaEngine).
 */
class PredictionEngine
{
    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @param  list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>  $yogas
     * @return array{marriage: array{key: string, text: string}, career: array{key: string, text: string}, education: array{key: string, text: string}, foreign_settlement: array{key: string, text: string}}
     */
    public static function generate(array $houses, array $yogas): array
    {
        return [
            'marriage' => MarriagePredictor::predict($houses),
            'career' => CareerPredictor::predict($houses, $yogas),
            'education' => EducationPredictor::predict($houses),
            'foreign_settlement' => ForeignSettlementPredictor::predict($houses, $yogas),
        ];
    }
}
