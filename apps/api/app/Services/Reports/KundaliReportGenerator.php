<?php

namespace App\Services\Reports;

use App\Models\BirthChart;
use App\Models\Setting;
use App\Models\YearWiseForecast;
use App\Services\Astrology\Doshas\DoshaEngine;
use App\Services\Astrology\Transits\SadeSati;
use App\Services\Astrology\Varga\VargaCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Carbon\CarbonImmutable;

/**
 * Renders the downloadable Kundali PDF report from a saved chart's
 * already-computed result (nakshatra/dasha/yogas/predictions/remedies/
 * ashtakvarga/avkahada/jaimini/shadbala/kp/lal_kitab/bhava_madhya/aspects —
 * see BirthChartCalculator) plus an optional year-wise forecast section.
 *
 * Doshas, Sade Sati, and the Shodashvarga divisional-chart table are
 * deliberately NOT part of BirthChartCalculator's own result (same
 * reasoning as their standalone controllers: Sade Sati/doshas are their
 * own endpoints, divisional charts are computed per-varga on demand — see
 * DoshaEngine, Transits\SadeSati, VargaController), so this generator
 * computes them fresh from the chart's own already-computed
 * planetary_positions/houses (for doshas — identical inputs to
 * DoshaController) and birth moment (for Sade Sati/divisional charts —
 * identical inputs to SadeSatiController/VargaController), rather than
 * re-running PlaceLookup/geocoding, which only VargaCalculator's
 * longitude-based sign lookup actually needs.
 *
 * No caching: dompdf rendering is cheap, pure-CPU HTML-to-PDF with no I/O,
 * so every request regenerates the PDF fresh rather than risking a stale
 * copy after a template change.
 */
class KundaliReportGenerator
{
    public static function generate(BirthChart $chart, ?YearWiseForecast $forecast = null): DomPdf
    {
        return Pdf::loadView('pdf.kundali-report', self::viewData($chart, $forecast))->setPaper('a4');
    }

    /**
     * The exact data the report template renders from, factored out of
     * generate() so tests can assert on rendered HTML (view('pdf.
     * kundali-report', ...)->render()) instead of trying to search dompdf's
     * compressed PDF byte stream for section text.
     *
     * @return array<string, mixed>
     */
    public static function viewData(BirthChart $chart, ?YearWiseForecast $forecast = null): array
    {
        $result = $chart->result;
        $isVedic = $result['system'] === 'vedic';

        return [
            'chart' => $chart,
            'result' => $result,
            'forecast' => $forecast,
            'doshas' => $isVedic ? DoshaEngine::detect($result) : null,
            'sadeSati' => $isVedic ? self::sadeSati($chart, $result) : null,
            'divisionalCharts' => $isVedic ? self::divisionalCharts($result) : null,
            'siteName' => Setting::current()->site_name,
            'generatedAt' => now(),
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private static function sadeSati(BirthChart $chart, array $result): array
    {
        $birthMoment = CarbonImmutable::parse("{$chart->dob->toDateString()} {$chart->time}", $result['timezone']);

        return SadeSati::forChart($result, $birthMoment, CarbonImmutable::now());
    }

    /**
     * Shodashvarga table: each planet's (and the Ascendant's) sign in each
     * of the 16 vargas, built directly from the longitudes
     * BirthChartCalculator already computed and stored on
     * planetary_positions — no need to re-geocode or recompute the chart,
     * since VargaCalculator::sign() only ever needs a longitude.
     *
     * @param  array<string, mixed>  $result
     * @return list<array{varga: string, ascendant: string, planets: array<string, string>}>
     */
    private static function divisionalCharts(array $result): array
    {
        $planetLongitudes = [];
        foreach ($result['planetary_positions'] as $planet) {
            $planetLongitudes[$planet['name']] = $planet['longitude'];
        }

        $rows = [];
        foreach (array_keys(VargaCalculator::DIVISIONS) as $varga) {
            if ($varga === 'D1') {
                continue; // The D1 (Rasi) chart is already the main chart shown earlier in the report.
            }

            $planetSigns = [];
            foreach ($planetLongitudes as $planet => $longitude) {
                $planetSigns[$planet] = VargaCalculator::sign($varga, $longitude);
            }

            $rows[] = [
                'varga' => $varga,
                'ascendant' => VargaCalculator::sign($varga, $result['ascendant_longitude']),
                'planets' => $planetSigns,
            ];
        }

        return $rows;
    }
}
