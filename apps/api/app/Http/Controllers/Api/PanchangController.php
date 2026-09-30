<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Astrology\Panchang\PanchangCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PanchangController extends Controller
{
    /**
     * Calculates the daily Panchang for a date and place — public,
     * stateless, no account required, mirroring ChartCalculationController.
     * Defaults `date` to today when omitted, so the frontend's homepage
     * "today's Panchang" widget can call this with just a place.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'place' => ['required', 'string', 'max:255'],
        ]);

        $validated['date'] ??= now()->toDateString();

        return response()->json(PanchangCalculator::calculate($validated));
    }
}
