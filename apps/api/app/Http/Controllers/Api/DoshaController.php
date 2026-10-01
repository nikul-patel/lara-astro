<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Astrology\BirthChartCalculator;
use App\Services\Astrology\Doshas\DoshaEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoshaController extends Controller
{
    /**
     * Runs the standalone dosha checks (Mangal, Kaal Sarp) from raw birth
     * details — public, stateless, mirroring ChartCalculationController.
     * Vedic-only: doshas are sidereal Vedic concepts with no Western
     * equivalent, same restriction BirthChartCalculator already applies to
     * nakshatra/dasha/yogas.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'place' => ['required', 'string', 'max:255'],
        ]);

        $chart = BirthChartCalculator::calculate([...$validated, 'system' => 'vedic']);

        return response()->json(DoshaEngine::detect($chart));
    }
}
