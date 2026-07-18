<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SavedChartResource;
use App\Models\BirthChart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChartController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
            'time' => ['required', 'string', 'max:20'],
            'place' => ['required', 'string', 'max:255'],
            'system' => ['nullable', 'string', 'max:50'],
            'chart_style' => ['nullable', 'string', 'max:50'],
            'result' => ['required', 'array'],
        ]);

        $chart = BirthChart::create([
            'client_id' => $request->user('sanctum')->id,
            'name' => $validated['name'],
            'dob' => $validated['dob'],
            'time' => $validated['time'],
            'place' => $validated['place'],
            'system' => $validated['system'] ?? null,
            'chart_style' => $validated['chart_style'] ?? null,
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
