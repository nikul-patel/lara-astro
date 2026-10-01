<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Astrology\BirthChartCalculator;
use App\Services\Astrology\Transits\SadeSati;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SadeSatiController extends Controller
{
    /**
     * Calculates the Sade Sati report from raw birth details — public,
     * stateless, mirroring ChartCalculationController. `reference_date`
     * defaults to today; passing an earlier/later date reports the cycle
     * relevant to that date instead of "right now" (useful for checking a
     * past or future window rather than only the current moment).
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'place' => ['required', 'string', 'max:255'],
            'reference_date' => ['nullable', 'date'],
        ]);

        $chart = BirthChartCalculator::calculate([...$validated, 'system' => 'vedic']);
        $birthMoment = CarbonImmutable::parse("{$validated['dob']} {$validated['time']}", $chart['timezone']);
        $referenceDate = CarbonImmutable::parse($validated['reference_date'] ?? now()->toDateString());

        return response()->json(SadeSati::forChart($chart, $birthMoment, $referenceDate));
    }
}
