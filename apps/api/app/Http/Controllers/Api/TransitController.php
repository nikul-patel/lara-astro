<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Astrology\BirthChartCalculator;
use App\Services\Astrology\Predictions\TransitPredictor;
use App\Services\Astrology\Transits\Transit;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransitController extends Controller
{
    /**
     * Calculates today's (or a given reference date's) Gochar transit
     * report from raw birth details — public, stateless, mirroring
     * SadeSatiController. `reference_date` defaults to today; passing an
     * earlier/later date reports the transits relevant to that date
     * instead of "right now".
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
        $natalMoonSign = collect($chart['planetary_positions'])->firstWhere('name', 'Moon')['sign'];

        $referenceDate = CarbonImmutable::parse($validated['reference_date'] ?? now()->toDateString());

        $transits = Transit::forNatalMoon(Transit::signsAt($referenceDate), $natalMoonSign);

        return response()->json([
            'reference_date' => $referenceDate->toDateString(),
            'natal_moon_sign' => $natalMoonSign,
            'transits' => TransitPredictor::generate($transits),
        ]);
    }
}
