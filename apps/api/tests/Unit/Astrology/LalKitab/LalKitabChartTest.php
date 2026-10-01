<?php

use App\Services\Astrology\LalKitab\LalKitabChart;

function lalKitabChartFixture(array $overrides = []): array
{
    return array_merge([
        'Sun' => 200.0, 'Moon' => 200.0, 'Mars' => 200.0, 'Mercury' => 200.0,
        'Jupiter' => 200.0, 'Venus' => 200.0, 'Saturn' => 200.0, 'Rahu' => 200.0, 'Ketu' => 200.0,
    ], $overrides);
}

test('houses are fixed to signs (Aries=1...Pisces=12) regardless of the Ascendant', function () {
    // Ascendant is Leo here, but the Lal Kitab chart must still number
    // house 1 as Aries, not Leo — the defining difference from the
    // whole-sign system.
    $chart = lalKitabChartFixture(['Sun' => 5.0, 'Moon' => 95.0]); // Sun: 5 Aries. Moon: 5 Cancer.

    $result = LalKitabChart::build($chart, 'Leo');

    expect($result['houses'][0])->toBe(['number' => 1, 'sign' => 'Aries', 'planets' => ['Sun']]);
    expect($result['houses'][3])->toBe(['number' => 4, 'sign' => 'Cancer', 'planets' => ['Moon']]);
    expect($result['houses'][11]['sign'])->toBe('Pisces');
});

test('ascendant_house reports which fixed house the real Lagna falls in', function () {
    $chart = lalKitabChartFixture();

    expect(LalKitabChart::build($chart, 'Leo')['ascendant_house'])->toBe(5);
    expect(LalKitabChart::build($chart, 'Aries')['ascendant_house'])->toBe(1);
    expect(LalKitabChart::build($chart, 'Pisces')['ascendant_house'])->toBe(12);
});

test('empty_houses lists every fixed house with no planet in it', function () {
    // Aries (house 1, Sun) and Cancer (house 4, Moon) are occupied; every
    // other planet in the fixture sits at 200 degrees (Libra, house 7).
    $chart = lalKitabChartFixture(['Sun' => 5.0, 'Moon' => 95.0]);

    $emptyHouses = LalKitabChart::build($chart, 'Leo')['empty_houses'];

    expect($emptyHouses)->toHaveCount(9);
    expect($emptyHouses)->not->toContain(1, 4, 7);
});

test('Pucca Ghar is true exactly when a classical planet sits in its own sign, false otherwise, and Rahu/Ketu are excluded', function () {
    $chart = [
        'Sun' => 125.0,    // 5 Leo -> own sign.
        'Moon' => 95.0,    // 5 Cancer -> own sign.
        'Mars' => 10.0,    // 10 Aries -> own sign.
        'Mercury' => 100.0, // 10 Cancer -> NOT own sign (Mercury rules Gemini/Virgo).
        'Jupiter' => 255.0, // 15 Sagittarius -> own sign.
        'Venus' => 400.0,  // wraps to 40 = 10 Taurus -> own sign.
        'Saturn' => 310.0, // 10 Aquarius -> own sign.
        'Rahu' => 125.0,
        'Ketu' => 305.0,
    ];

    $result = LalKitabChart::build($chart, 'Aries');

    expect($result['pucca_ghar'])->toBe([
        'Sun' => true, 'Moon' => true, 'Mars' => true, 'Mercury' => false,
        'Jupiter' => true, 'Venus' => true, 'Saturn' => true,
    ]);
    expect($result['pucca_ghar'])->not->toHaveKey('Rahu');
    expect($result['pucca_ghar'])->not->toHaveKey('Ketu');
});
