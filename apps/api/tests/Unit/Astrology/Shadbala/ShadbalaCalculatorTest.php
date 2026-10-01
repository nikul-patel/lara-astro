<?php

use App\Services\Astrology\JulianDay;
use App\Services\Astrology\Shadbala\ShadbalaCalculator;
use Carbon\CarbonImmutable;

test('calculate() assembles all six components into a consistent total, in Rupas, against the classical minimum thresholds', function () {
    $chartLongitudes = ['Sun' => 10.0, 'Moon' => 100.0, 'Mars' => 65.0, 'Mercury' => 200.0, 'Jupiter' => 280.0, 'Venus' => 300.0, 'Saturn' => 350.0];
    $rasiSigns = ['Sun' => 'Aries', 'Moon' => 'Cancer', 'Mars' => 'Gemini', 'Mercury' => 'Sagittarius', 'Jupiter' => 'Capricorn', 'Venus' => 'Capricorn', 'Saturn' => 'Pisces'];
    $signs = ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'];
    $houses = [];
    for ($number = 1; $number <= 12; $number++) {
        $houses[] = ['number' => $number, 'sign' => $signs[$number - 1], 'planets' => []];
    }
    $localBirthMoment = CarbonImmutable::parse('1995-06-15 10:30:00', 'UTC');
    $julianDay = JulianDay::fromUtc($localBirthMoment);

    $result = ShadbalaCalculator::calculate($julianDay, $chartLongitudes, $rasiSigns, $houses, $localBirthMoment, 28.6, 77.2);

    $planets = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];
    foreach ($planets as $planet) {
        $expectedTotal = round(
            $result['sthana']['total'][$planet] + $result['dig'][$planet] + $result['kala']['total'][$planet]
            + $result['chesta'][$planet] + $result['naisargika'][$planet] + $result['drik'][$planet],
            2
        );

        expect($result['total_virupas'][$planet])->toBe($expectedTotal);
        expect($result['total_rupas'][$planet])->toBe(round($expectedTotal / 60, 2));
        expect($result['is_strong'][$planet])->toBe($result['total_rupas'][$planet] >= $result['minimum_required_rupas'][$planet]);

        // Every component must be a Virupas number, not an array leak or null.
        expect($result['chesta'][$planet])->toBeFloat();
        expect($result['naisargika'][$planet])->toBeFloat();
    }

    // Sun and Moon never go to war, so Kala Bala's Yuddha component must be 0 for them.
    expect($result['kala']['yuddha']['Sun'])->toBe(0.0);
    expect($result['kala']['yuddha']['Moon'])->toBe(0.0);
});
