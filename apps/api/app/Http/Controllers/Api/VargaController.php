<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Astrology\ChartAssembler;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\PlaceLookup;
use App\Services\Astrology\Varga\VargaCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VargaController extends Controller
{
    /**
     * Calculates a single Shodashvarga divisional chart from raw birth
     * details — public, stateless, mirroring DoshaController/PanchangController.
     * Vedic-only: divisional charts are a sidereal Vedic concept with no
     * Western equivalent, same restriction BirthChartCalculator applies to
     * nakshatra/dasha/yogas/ashtakvarga.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'place' => ['required', 'string', 'max:255'],
            'varga' => ['required', 'string', 'in:'.implode(',', array_keys(VargaCalculator::DIVISIONS))],
        ]);

        $location = PlaceLookup::resolve($validated['place']);
        $localDateTime = CarbonImmutable::parse("{$validated['dob']} {$validated['time']}", $location['timezone']);
        $julianDay = JulianDay::fromUtc($localDateTime->utc());

        $chart = ChartAssembler::assemble($julianDay, $location['latitude'], $location['longitude'], 'vedic');

        $varga = $validated['varga'];
        $ascendantSign = VargaCalculator::sign($varga, $chart['ascendant_longitude']);

        $planetSigns = [];
        foreach ($chart['chart_longitudes'] as $planet => $longitude) {
            $planetSigns[$planet] = VargaCalculator::sign($varga, $longitude);
        }

        return response()->json([
            'varga' => $varga,
            'ascendant' => $ascendantSign,
            'houses' => VargaCalculator::housesFromSigns($ascendantSign, $planetSigns),
            'location_matched' => $location['matched'],
        ]);
    }
}
