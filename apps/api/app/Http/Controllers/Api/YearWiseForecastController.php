<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\YearWiseForecastResource;
use App\Models\BirthChart;
use App\Services\Astrology\BirthChartCalculator;
use App\Services\Astrology\YearlyForecast\CachedForecast;
use App\Services\Astrology\YearlyForecast\TransitForecast;
use App\Services\Astrology\YearlyForecast\VarshphalCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YearWiseForecastController extends Controller
{
    /**
     * Calculates a year-wise forecast from raw birth details — public and
     * stateless, mirroring ChartCalculationController, for a guest trying
     * the calculator before saving/registering. Nothing is persisted here;
     * see forSavedChart() for the authenticated, cached version.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $this->validated($request);

        $natalChart = BirthChartCalculator::calculate($validated);

        $result = $validated['style'] === 'varshphal'
            ? VarshphalCalculator::forYear($natalChart, $validated, $validated['year'])
            : TransitForecast::forYear($natalChart, $validated['year']);

        return response()->json($result);
    }

    /**
     * Computes (once) and returns a year-wise forecast for a saved,
     * owned chart, caching the result in year_wise_forecasts (see
     * CachedForecast) so a repeat request for the same (chart, year,
     * style) doesn't recompute it.
     */
    public function forSavedChart(Request $request, BirthChart $birthChart): JsonResponse
    {
        if ($birthChart->client_id !== $request->user('sanctum')->id) {
            abort(403);
        }

        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'style' => ['nullable', 'string', 'in:simplified,varshphal'],
        ]);

        $forecast = CachedForecast::forChart(
            $birthChart,
            $validated['year'] ?? now()->year,
            $validated['style'] ?? 'simplified',
        );

        return (new YearWiseForecastResource($forecast))->response();
    }

    /**
     * @return array{name: string, dob: string, time: string, place: string, year: int, style: string}
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'place' => ['required', 'string', 'max:255'],
            'system' => ['nullable', 'string', 'in:vedic,western'],
            'chart_style' => ['nullable', 'string', 'in:north_indian,south_indian,east_indian'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'style' => ['nullable', 'string', 'in:simplified,varshphal'],
        ]);

        $validated['year'] ??= now()->year;
        $validated['style'] ??= 'simplified';

        return $validated;
    }
}
