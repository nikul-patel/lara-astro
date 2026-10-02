<?php

namespace App\Services\Astrology\Predictions;

/**
 * The detailed natal reading: a twelve-house life-area analysis
 * (LifeAreaAnalyzer) plus the executive summary drawn from it
 * (ChartOverview). Time-independent, so it can be stored with a chart's
 * result; the date-dependent current-period reading lives in
 * CurrentPeriodPredictor instead.
 */
class DetailedReadingEngine
{
    /**
     * @return array{overview: array<string, mixed>, life_areas: list<array<string, mixed>>}
     */
    public static function generate(ChartContext $chart): array
    {
        $areas = array_map(fn (int $house) => LifeAreaAnalyzer::analyze($chart, $house), range(1, 12));

        return [
            'overview' => ChartOverview::generate($chart, $areas),
            'life_areas' => $areas,
        ];
    }
}
