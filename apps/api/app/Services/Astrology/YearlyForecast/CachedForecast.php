<?php

namespace App\Services\Astrology\YearlyForecast;

use App\Models\BirthChart;
use App\Models\YearWiseForecast;

/**
 * Fetches a saved chart's year-wise forecast from year_wise_forecasts,
 * computing and caching it on first request. Shared by
 * YearWiseForecastController::forSavedChart() and
 * ChartReportController::download() (the PDF report can embed a year-wise
 * section without duplicating this compute-or-fetch logic).
 */
class CachedForecast
{
    public static function forChart(BirthChart $birthChart, int $year, string $style): YearWiseForecast
    {
        $forecast = YearWiseForecast::query()
            ->where(['birth_chart_id' => $birthChart->id, 'year' => $year, 'style' => $style])
            ->first();

        if ($forecast !== null) {
            return $forecast;
        }

        $input = [
            'dob' => $birthChart->dob->toDateString(),
            'time' => $birthChart->time,
            'place' => $birthChart->place,
        ];

        $result = $style === 'varshphal'
            ? VarshphalCalculator::forYear($birthChart->result, $input, $year)
            : TransitForecast::forYear($birthChart->result, $year);

        return YearWiseForecast::create([
            'birth_chart_id' => $birthChart->id,
            'year' => $year,
            'style' => $style,
            'result' => $result,
        ]);
    }
}
