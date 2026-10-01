<?php

use App\Services\Astrology\Doshas\KaalSarpDosha;
use Tests\Support\YogaChartFixture;

test('all seven classical planets hemmed between Rahu and Ketu is detected as present, typed by Rahu\'s house', function () {
    $chartLongitudes = [
        'Rahu' => 0.0, 'Ketu' => 180.0,
        'Sun' => 10.0, 'Moon' => 30.0, 'Mars' => 50.0, 'Mercury' => 70.0,
        'Jupiter' => 90.0, 'Venus' => 110.0, 'Saturn' => 130.0,
    ];
    $houses = YogaChartFixture::houses('Aries', ['Rahu' => 3]);

    $result = KaalSarpDosha::detect($chartLongitudes, $houses);

    expect($result['present'])->toBeTrue()
        ->and($result['type'])->toBe('Vasuki') // house 3
        ->and($result['rahu_house'])->toBe(3);
});

test('a single planet outside the hemmed arc breaks the dosha', function () {
    $chartLongitudes = [
        'Rahu' => 0.0, 'Ketu' => 180.0,
        'Sun' => 10.0, 'Moon' => 30.0, 'Mars' => 50.0, 'Mercury' => 70.0,
        'Jupiter' => 90.0, 'Venus' => 110.0,
        'Saturn' => 200.0, // on the Ketu side, breaking the hem
    ];
    $houses = YogaChartFixture::houses('Aries', ['Rahu' => 3]);

    $result = KaalSarpDosha::detect($chartLongitudes, $houses);

    expect($result['present'])->toBeFalse()
        ->and($result['type'])->toBeNull();
});

test('every house position maps to its classical type name', function () {
    $chartLongitudes = [
        'Rahu' => 0.0, 'Ketu' => 180.0,
        'Sun' => 10.0, 'Moon' => 30.0, 'Mars' => 50.0, 'Mercury' => 70.0,
        'Jupiter' => 90.0, 'Venus' => 110.0, 'Saturn' => 130.0,
    ];
    $expectedNames = [
        1 => 'Anant', 2 => 'Kulik', 3 => 'Vasuki', 4 => 'Shankhpal', 5 => 'Padma', 6 => 'Mahapadma',
        7 => 'Takshak', 8 => 'Karkotak', 9 => 'Shankhachud', 10 => 'Ghatak', 11 => 'Vishdhar', 12 => 'Sheshnag',
    ];

    foreach ($expectedNames as $house => $name) {
        $houses = YogaChartFixture::houses('Aries', ['Rahu' => $house]);
        expect(KaalSarpDosha::detect($chartLongitudes, $houses)['type'])->toBe($name);
    }
});
