<?php

use App\Models\BirthChart;
use App\Models\YearWiseForecast;
use App\Services\Astrology\BirthChartCalculator;
use App\Services\Astrology\YearlyForecast\TransitForecast;
use App\Services\Reports\KundaliReportGenerator;

test('generates a valid PDF for a full vedic chart (nakshatra, dasha, yogas, predictions, remedies all present)', function () {
    $chart = BirthChart::factory()->make([
        'name' => 'Ananya Singh',
        'result' => BirthChartCalculator::calculate([
            'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India', 'system' => 'vedic',
        ]),
    ]);

    $pdf = KundaliReportGenerator::generate($chart);

    expect($pdf->output())->toStartWith('%PDF');
});

test('the rendered report HTML includes every computed section, not just chart summary/dasha/yogas/predictions/remedies (#82)', function () {
    $chart = BirthChart::factory()->make([
        'name' => 'Ananya Singh',
        'dob' => '1994-05-12',
        'time' => '14:30',
        'result' => BirthChartCalculator::calculate([
            'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India', 'system' => 'vedic',
        ]),
    ]);

    $html = view('pdf.kundali-report', KundaliReportGenerator::viewData($chart))->render();

    foreach ([
        'Avkahada Chakra',
        'Ashtakvarga',
        'Prastharashtakvarga',
        'Shodashvarga (Divisional Charts)',
        'Bhava Madhya (Chalit / Placidus Cusps)',
        'Yogini Dasha',
        'Jaimini System',
        'KP System (Nakshatra Nadi)',
        'Ruling Planets',
        'Significators of Houses',
        'Planet Significations',
        'Shadbala &amp; Bhavabala',
        'Avastha (Planetary States)',
        'Lal Kitab Chart',
        'Planetary Friendship Table',
        'Planetary Aspects (Western)',
        'Dosha Analysis',
        'Sade Sati',
        'Transit Today',
        'Your Ascendant',
        'Nakshatra Phal',
        'Vimshottari Mahadasha Predictions',
        'Your Chart at a Glance',
        'Your Current Planetary Period',
        'Detailed Life Reading',
        'Marriage, Partnerships &amp; Public Dealings',
    ] as $expectedSection) {
        expect($html)->toContain($expectedSection);
    }

    // D1 isn't repeated in the Shodashvarga table — it's already the main chart shown earlier.
    expect($html)->not->toContain('>D1<');
});

test('regression: a chart saved before #82 added ascendant_longitude still generates a PDF instead of fataling', function () {
    // Reproduces a real production bug: BirthChart.result is cached JSON,
    // computed once at save time and never re-run by this generator. A
    // chart saved before #82 added `ascendant_longitude` to
    // BirthChartCalculator's output has that key missing — previously
    // divisionalCharts() passed that missing value (null) straight into
    // VargaCalculator::sign()'s typed float parameter and fataled with a
    // TypeError, taking down the entire report over one new field that
    // every other section already degrades around gracefully.
    $staleResult = BirthChartCalculator::calculate([
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India', 'system' => 'vedic',
    ]);
    unset($staleResult['ascendant_longitude']);

    $chart = BirthChart::factory()->make([
        'name' => 'Ananya Singh',
        'dob' => '1994-05-12',
        'time' => '14:30',
        'result' => $staleResult,
    ]);

    $pdf = KundaliReportGenerator::generate($chart);

    expect($pdf->output())->toStartWith('%PDF');

    // The one section that genuinely needs the missing field is omitted;
    // every other section (computed from fields that did already exist) still renders.
    $html = view('pdf.kundali-report', KundaliReportGenerator::viewData($chart))->render();
    expect($html)->not->toContain('Shodashvarga (Divisional Charts)');
    expect($html)->toContain('Avkahada Chakra');
    expect($html)->toContain('KP System (Nakshatra Nadi)');
});

test('regression: a chart saved between #77 and #84 has kp.sub_lords/cusps but not the newer kp sub-sections, and still renders cleanly', function () {
    // `kp` itself existed since #77 (sub_lords/ascendant/cusps), so the
    // top-level `@if (! empty($result['kp']))` guard alone doesn't catch
    // this case — the section renders, but its 3 newer sub-tables
    // (ruling_planets/house_significators/planet_significations, #84)
    // need their own guards or they warn on every row and render empty.
    $staleResult = BirthChartCalculator::calculate([
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India', 'system' => 'vedic',
    ]);
    unset($staleResult['kp']['ruling_planets'], $staleResult['kp']['house_significators'], $staleResult['kp']['planet_significations']);

    $chart = BirthChart::factory()->make([
        'name' => 'Ananya Singh',
        'dob' => '1994-05-12',
        'time' => '14:30',
        'result' => $staleResult,
    ]);

    $pdf = KundaliReportGenerator::generate($chart);

    expect($pdf->output())->toStartWith('%PDF');

    $html = view('pdf.kundali-report', KundaliReportGenerator::viewData($chart))->render();
    expect($html)->toContain('KP System (Nakshatra Nadi)')->toContain('Planet Sub-Lords');
    expect($html)->not->toContain('Ruling Planets')
        ->not->toContain('Significators of Houses')
        ->not->toContain('Planet Significations');
});

test('generates a valid PDF for a western chart, where nakshatra/dasha/yogas/predictions/remedies are all null', function () {
    $chart = BirthChart::factory()->make([
        'name' => 'Test',
        'result' => BirthChartCalculator::calculate([
            'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India', 'system' => 'western',
        ]),
    ]);

    $pdf = KundaliReportGenerator::generate($chart);

    expect($pdf->output())->toStartWith('%PDF');
});

test('generates a valid PDF with an embedded simplified year-wise forecast section', function () {
    $chart = BirthChart::factory()->create([
        'name' => 'Ananya Singh',
        'dob' => '1994-05-12',
        'time' => '14:30',
        'place' => 'Jaipur, India',
        'result' => BirthChartCalculator::calculate([
            'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India', 'system' => 'vedic',
        ]),
    ]);
    $forecast = YearWiseForecast::factory()->for($chart, 'birthChart')->create([
        'year' => 2026,
        'style' => 'simplified',
        'result' => TransitForecast::forYear($chart->result, 2026),
    ]);

    $pdf = KundaliReportGenerator::generate($chart, $forecast);

    expect($pdf->output())->toStartWith('%PDF');
});

test('a chart saved before the detailed reading existed still gets it, computed fresh from its stored result', function () {
    // Simulates a chart saved before predictions.overview/life_areas, the
    // Ashtakvarga and Shadbala existed: the report recomputes the reading
    // from planets/houses/dasha, and simply skips the factors it lacks.
    $staleResult = BirthChartCalculator::calculate([
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India', 'system' => 'vedic',
    ]);
    unset($staleResult['predictions']['overview'], $staleResult['predictions']['life_areas'], $staleResult['ashtakvarga'], $staleResult['shadbala']);

    $chart = BirthChart::factory()->make(['name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'result' => $staleResult]);

    $viewData = KundaliReportGenerator::viewData($chart);
    expect($viewData['detailedReading']['life_areas'])->toHaveCount(12)
        ->and($viewData['detailedReading']['life_areas'][0]['sav_bindus'])->toBeNull()
        ->and($viewData['detailedReading']['overview']['strongest_planet'])->toBeNull();

    expect(view('pdf.kundali-report', $viewData)->render())->toContain('Detailed Life Reading');
    expect(KundaliReportGenerator::generate($chart)->output())->toStartWith('%PDF');
});

test('no detailed reading is rendered when the chart was saved with predictions disabled', function () {
    $result = BirthChartCalculator::calculate([
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India', 'system' => 'vedic',
    ]);
    $result['predictions'] = null;

    $chart = BirthChart::factory()->make(['name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'result' => $result]);

    $viewData = KundaliReportGenerator::viewData($chart);
    expect($viewData['detailedReading'])->toBeNull()
        ->and($viewData['currentPeriod'])->toBeNull()
        ->and(view('pdf.kundali-report', $viewData)->render())->not->toContain('Detailed Life Reading');
});
