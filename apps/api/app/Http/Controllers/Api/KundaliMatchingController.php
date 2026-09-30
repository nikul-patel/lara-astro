<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Astrology\BirthChartCalculator;
use App\Services\Astrology\HouseLords;
use App\Services\Astrology\Matching\AshtakootMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KundaliMatchingController extends Controller
{
    /**
     * Runs Ashtakoot Guna Milan (36-point Vedic marriage matching) from
     * both partners' raw birth details — public, stateless, mirroring
     * DoshaController/SadeSatiController. Vedic-only, same restriction
     * BirthChartCalculator applies to nakshatra/dasha/yogas: Ashtakoot is a
     * sidereal-nakshatra system with no Western equivalent.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bride.name' => ['required', 'string', 'max:255'],
            'bride.dob' => ['required', 'date'],
            'bride.time' => ['required', 'date_format:H:i'],
            'bride.place' => ['required', 'string', 'max:255'],
            'groom.name' => ['required', 'string', 'max:255'],
            'groom.dob' => ['required', 'date'],
            'groom.time' => ['required', 'date_format:H:i'],
            'groom.place' => ['required', 'string', 'max:255'],
        ]);

        $brideChart = BirthChartCalculator::calculate([...$validated['bride'], 'system' => 'vedic']);
        $groomChart = BirthChartCalculator::calculate([...$validated['groom'], 'system' => 'vedic']);

        $brideRashi = HouseLords::signOfPlanet('Moon', $brideChart['houses']);
        $groomRashi = HouseLords::signOfPlanet('Moon', $groomChart['houses']);

        $result = AshtakootMatcher::match(
            $brideChart['nakshatra'],
            $brideRashi,
            $groomChart['nakshatra'],
            $groomRashi,
        );

        return response()->json([
            ...$result,
            'bride' => ['nakshatra' => $brideChart['nakshatra'], 'rashi' => $brideRashi],
            'groom' => ['nakshatra' => $groomChart['nakshatra'], 'rashi' => $groomRashi],
        ]);
    }
}
