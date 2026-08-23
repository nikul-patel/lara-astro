<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BirthChart;
use App\Services\Astrology\YearlyForecast\CachedForecast;
use App\Services\Reports\KundaliReportGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ChartReportController extends Controller
{
    /**
     * Downloads the PDF Kundali report for a saved, owned chart.
     * Optionally embeds a year-wise forecast section (computed/cached the
     * same way as GET /charts/{id}/year-wise, see CachedForecast) when
     * `year_wise_style` is given.
     */
    public function download(Request $request, BirthChart $birthChart): Response
    {
        if ($birthChart->client_id !== $request->user('sanctum')->id) {
            abort(403);
        }

        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'year_wise_style' => ['nullable', 'string', 'in:simplified,varshphal'],
        ]);

        $forecast = isset($validated['year_wise_style'])
            ? CachedForecast::forChart($birthChart, $validated['year'] ?? now()->year, $validated['year_wise_style'])
            : null;

        $pdf = KundaliReportGenerator::generate($birthChart, $forecast);

        return $pdf->download('kundali-'.Str::slug($birthChart->name).'.pdf');
    }
}
