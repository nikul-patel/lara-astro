<?php

namespace App\Services\Reports;

use App\Models\BirthChart;
use App\Models\Setting;
use App\Models\YearWiseForecast;
use App\Services\Astrology\Doshas\DoshaEngine;
use App\Services\Astrology\Predictions\TransitPredictor;
use App\Services\Astrology\Transits\SadeSati;
use App\Services\Astrology\Transits\Transit;
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
 * Doshas, Sade Sati, today's Gochar transits, and the Shodashvarga
 * divisional-chart table are deliberately NOT part of
 * BirthChartCalculator's own result (same reasoning as their standalone
 * controllers: Sade Sati/doshas/transits are their own endpoints,
 * divisional charts are computed per-varga on demand — see DoshaEngine,
 * Transits\SadeSati, Transits\Transit, VargaController), so this
 * generator computes them fresh from the chart's own already-computed
 * planetary_positions/houses (for doshas/transits — identical inputs to
 * DoshaController/TransitController) and birth moment (for Sade
 * Sati/divisional charts — identical inputs to
 * SadeSatiController/VargaController), rather than re-running
 * PlaceLookup/geocoding, which only VargaCalculator's longitude-based
 * sign lookup actually needs.
 *
 * No caching: dompdf rendering is cheap, pure-CPU HTML-to-PDF with no I/O,
 * so every request regenerates the PDF fresh rather than risking a stale
 * copy after a template change.
 */
class KundaliReportGenerator
{
    public static function generate(BirthChart $chart, ?YearWiseForecast $forecast = null): DomPdf
    {
        $pdf = Pdf::loadView('pdf.kundali-report', self::viewData($chart, $forecast))->setPaper('a4');

        // page_text()'s {PAGE_NUM}/{PAGE_COUNT} placeholders are resolved
        // per-page by dompdf's own deferred page-script mechanism (see
        // Dompdf\Adapter\CPDF::page_text()) — this is the documented way to
        // get a real "Page N of M" in dompdf; the CSS-only equivalent,
        // counter(pages), is NOT reliably supported (it resolved to a
        // literal 0 when tried). Requires render() to run first so the
        // canvas exists; output()/download() check $rendered and skip
        // re-rendering, so this doesn't render the document twice.
        // Coordinates are in PDF points (595.28x841.89 for A4), not the
        // template's CSS pixels. x is a fixed estimate centering a
        // "Page NN of NN" string at 8pt — precise width-measured
        // centering isn't worth the complexity for a page number.
        $pdf->render();
        $pdf->getCanvas()->page_text(267, 783, 'Page {PAGE_NUM} of {PAGE_COUNT}', null, 8, [0.66, 0.39, 0.16]);

        return $pdf;
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
            // isset() guard, not just $isVedic: a chart SAVED before #82 added
            // `ascendant_longitude` to BirthChartCalculator's result has that
            // key missing from its stored (cached-at-save-time) `result` JSON
            // — this generator never re-runs BirthChartCalculator on an
            // existing chart, so an old chart's result stays exactly as it
            // was computed. Without this guard, divisionalCharts() would pass
            // null into VargaCalculator::sign()'s typed float parameter and
            // fatal with a TypeError, taking down the ENTIRE report (every
            // other section already degrades gracefully via its own
            // `@if (! empty(...))` guard) for the one new field this is the
            // only section that needs. An old chart simply omits this one
            // section rather than crashing; re-saving/recalculating the
            // chart picks up every new field, this one included.
            'divisionalCharts' => $isVedic && isset($result['ascendant_longitude']) ? self::divisionalCharts($result) : null,
            'transits' => $isVedic ? self::transits($result) : null,
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
     * Today's Gochar (transit), relative to this chart's own natal Moon —
     * computed fresh at request time, same reasoning as sadeSati() above
     * (date-dependent, not part of BirthChartCalculator's own result).
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private static function transits(array $result): array
    {
        $natalMoonSign = collect($result['planetary_positions'])->firstWhere('name', 'Moon')['sign'];
        $transitSigns = Transit::signsAt(CarbonImmutable::now());

        return TransitPredictor::generate(Transit::forNatalMoon($transitSigns, $natalMoonSign));
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
