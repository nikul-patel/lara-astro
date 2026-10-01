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
        'Shadbala &amp; Bhavabala',
        'Avastha (Planetary States)',
        'Lal Kitab Chart',
        'Planetary Friendship Table',
        'Planetary Aspects (Western)',
        'Dosha Analysis',
        'Sade Sati',
        'Your Ascendant',
        'Vimshottari Mahadasha Predictions',
    ] as $expectedSection) {
        expect($html)->toContain($expectedSection);
    }

    // D1 isn't repeated in the Shodashvarga table — it's already the main chart shown earlier.
    expect($html)->not->toContain('>D1<');
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
