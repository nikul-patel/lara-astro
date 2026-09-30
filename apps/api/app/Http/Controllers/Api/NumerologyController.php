<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Numerology\NumerologyCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NumerologyController extends Controller
{
    /**
     * Calculates a numerology reading from a name and birth date — public,
     * stateless, no account required, mirroring ChartCalculationController.
     * Unlike the astrology endpoints this needs no place/time (numerology
     * has no astronomical component), just the birth date and full name.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
        ]);

        return response()->json(NumerologyCalculator::calculate($validated));
    }
}
