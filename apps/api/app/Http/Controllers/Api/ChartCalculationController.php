<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Astrology\BirthChartCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChartCalculationController extends Controller
{
    /**
     * Calculates a birth chart from raw birth details — public, no
     * account required (docs/API_CONTRACT.md: "Public read endpoints
     * ... chart calculation: no auth required"). Saving the result to an
     * account is the separate, authenticated POST /charts (see
     * ChartController), which this doesn't touch.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'time' => ['required', 'string', 'max:20'],
            'place' => ['required', 'string', 'max:255'],
            'system' => ['nullable', 'string', 'in:vedic,western'],
            'chart_style' => ['nullable', 'string', 'in:north_indian,south_indian,east_indian'],
        ]);

        return response()->json(BirthChartCalculator::calculate($validated));
    }
}
