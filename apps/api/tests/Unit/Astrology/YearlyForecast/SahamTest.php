<?php

use App\Services\Astrology\YearlyForecast\Saham;
use App\Services\Astrology\ZodiacSigns;

test('computes 8 curated Sahams, each with a valid sign', function () {
    $longitudes = [
        'Sun' => 10.0, 'Moon' => 100.0, 'Mercury' => 20.0, 'Venus' => 340.0,
        'Mars' => 200.0, 'Jupiter' => 60.0, 'Saturn' => 280.0,
    ];

    $sahams = Saham::compute(ascendantLongitude: 15.0, planetLongitudes: $longitudes, isDayBirth: true);

    expect($sahams)->toHaveCount(8);
    foreach ($sahams as $saham) {
        expect(ZodiacSigns::NAMES)->toContain($saham['sign']);
    }
});

test('the day and night formulas for the same Saham swap the two significators', function () {
    $longitudes = ['Sun' => 10.0, 'Moon' => 100.0];
    // Reduce to just Punya by only supplying its significators; the other
    // formulas would error on a missing key, so isolate via array access.
    $day = collect(Saham::compute(15.0, $longitudes + ['Mercury' => 0, 'Venus' => 0, 'Mars' => 0, 'Jupiter' => 0, 'Saturn' => 0], true))->firstWhere('key', 'punya');
    $night = collect(Saham::compute(15.0, $longitudes + ['Mercury' => 0, 'Venus' => 0, 'Mars' => 0, 'Jupiter' => 0, 'Saturn' => 0], false))->firstWhere('key', 'punya');

    // Day: Asc + Moon - Sun = 15 + 100 - 10 = 105
    // Night: Asc + Sun - Moon = 15 + 10 - 100 = -75 -> normalized 285
    expect($day['longitude'])->toBe(105.0)
        ->and($night['longitude'])->toBe(285.0);
});
