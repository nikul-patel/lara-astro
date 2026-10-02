<?php

namespace App\Services\Astrology\Predictions;

/**
 * Orchestrates the 4 life-area predictors against a computed whole-sign
 * house chart and its already-detected yogas (Services/Astrology/YogaEngine),
 * plus — when a ChartContext is supplied — the detailed twelve-house
 * reading and its overview (DetailedReadingEngine).
 */
class PredictionEngine
{
    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @param  list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>  $yogas
     * @return array<string, mixed>
     */
    public static function generate(array $houses, array $yogas, string $ascendantSign, ?string $moonNakshatraName = null, ?ChartContext $context = null): array
    {
        $predictions = [
            'marriage' => MarriagePredictor::predict($houses),
            'career' => CareerPredictor::predict($houses, $yogas),
            'education' => EducationPredictor::predict($houses),
            'foreign_settlement' => ForeignSettlementPredictor::predict($houses, $yogas),
            'ascendant' => AscendantPredictor::describe($ascendantSign),
            'nakshatra' => $moonNakshatraName !== null ? NakshatraPredictor::describe($moonNakshatraName) : null,
            'dasha_narrative' => DashaNarrativePredictor::generate($houses),
        ];

        return $context === null ? $predictions : $predictions + DetailedReadingEngine::generate($context);
    }
}
