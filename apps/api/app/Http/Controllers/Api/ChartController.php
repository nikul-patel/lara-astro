<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SavedChartResource;
use App\Models\BirthChart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class ChartController extends Controller
{
    private const VALID_SYSTEMS = ['vedic', 'western'];

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'time' => ['required', 'string', 'max:20'],
            'place' => ['required', 'string', 'max:255'],
            'system' => ['nullable', 'string', 'in:'.implode(',', self::VALID_SYSTEMS)],
            'chart_style' => ['nullable', 'string', 'max:50'],
            // Validated as a whole array (not via 'result.system' dot
            // notation) so every key the caller sent — not just the ones
            // with their own rule — survives into $validated['result'].
            'result' => ['required', 'array'],
        ]);

        // Both omitted at the top level when the user hasn't overridden the
        // automatic recommendation (see apps/web's birth-chart-tool.tsx);
        // result.system/result.chart_style (what the calculation actually
        // used, see BirthChartCalculator) are always present in that flow
        // and are the fallback here.
        $system = $validated['system'] ?? ($validated['result']['system'] ?? null);
        $chartStyle = $validated['chart_style'] ?? ($validated['result']['chart_style'] ?? null);

        if (! in_array($system, self::VALID_SYSTEMS, true)) {
            throw ValidationException::withMessages(['system' => 'A valid astrology system is required.']);
        }

        $chart = BirthChart::create([
            'client_id' => $request->user('sanctum')->id,
            'name' => $validated['name'],
            'dob' => $validated['dob'],
            'time' => $validated['time'],
            'place' => $validated['place'],
            'system' => $system,
            'chart_style' => $chartStyle,
            'result' => $validated['result'],
        ]);

        return (new SavedChartResource($chart))->response()->setStatusCode(201);
    }

    public function mine(Request $request): AnonymousResourceCollection
    {
        $charts = BirthChart::query()
            ->where('client_id', $request->user('sanctum')->id)
            ->latest()
            ->get();

        return SavedChartResource::collection($charts);
    }
}
