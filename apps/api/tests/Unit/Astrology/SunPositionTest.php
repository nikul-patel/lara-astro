<?php

use App\Services\Astrology\JulianDay;
use App\Services\Astrology\SunPosition;
use Carbon\CarbonImmutable;

test('julian day matches the canonical Meeus reference date', function () {
    // Meeus, "Astronomical Algorithms", used throughout the book as the
    // standard worked example: 1992 October 13.0 UT = JD 2448908.5.
    $jd = JulianDay::fromUtc(CarbonImmutable::parse('1992-10-13 00:00:00', 'UTC'));

    expect($jd)->toBeGreaterThan(2448908.499)->toBeLessThan(2448908.501);
});

test('sun longitude matches Meeus\'s worked example to within 0.01 degrees', function () {
    $jd = JulianDay::fromUtc(CarbonImmutable::parse('1992-10-13 00:00:00', 'UTC'));

    // Meeus example 25.a computes the Sun's apparent longitude for this
    // date as approximately 199.9°.
    expect(SunPosition::apparentLongitude($jd))->toBeGreaterThan(199.85)->toBeLessThan(199.95);
});

test('sun longitude lands on the equinoxes and solstices', function (string $date, float $expectedLongitude) {
    $jd = JulianDay::fromUtc(CarbonImmutable::parse($date, 'UTC'));

    expect(SunPosition::apparentLongitude($jd))
        ->toBeGreaterThan($expectedLongitude - 0.05)
        ->toBeLessThan($expectedLongitude + 0.05);
})->with([
    'spring equinox (Aries 0°)' => ['2024-03-20 03:06:00', 0.0],
    'summer solstice (Cancer 0°)' => ['2024-06-20 20:51:00', 90.0],
    'autumn equinox (Libra 0°)' => ['2024-09-22 12:44:00', 180.0],
    'winter solstice (Capricorn 0°)' => ['2024-12-21 09:21:00', 270.0],
]);
