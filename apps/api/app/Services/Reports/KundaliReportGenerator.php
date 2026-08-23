<?php

namespace App\Services\Reports;

use App\Models\BirthChart;
use App\Models\Setting;
use App\Models\YearWiseForecast;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

/**
 * Renders the downloadable Kundali PDF report from a saved chart's
 * already-computed result (nakshatra/dasha/yogas/predictions/remedies —
 * see BirthChartCalculator) plus an optional year-wise forecast section.
 * No caching: dompdf rendering is cheap, pure-CPU HTML-to-PDF with no I/O,
 * so every request regenerates the PDF fresh rather than risking a stale
 * copy after a template change.
 */
class KundaliReportGenerator
{
    public static function generate(BirthChart $chart, ?YearWiseForecast $forecast = null): DomPdf
    {
        return Pdf::loadView('pdf.kundali-report', [
            'chart' => $chart,
            'result' => $chart->result,
            'forecast' => $forecast,
            'siteName' => Setting::current()->site_name,
            'generatedAt' => now(),
        ])->setPaper('a4');
    }
}
