<?php

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Ayanamsa;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\SunPosition;
use App\Services\Astrology\YearlyForecast\SolarReturn;
use Carbon\CarbonImmutable;

test('the returned moment has the Sun back at its natal sidereal longitude', function () {
    $birthMoment = CarbonImmutable::parse('1994-05-12 14:30:00', 'UTC');
    $birthJulianDay = JulianDay::fromUtc($birthMoment);
    $natalSunLongitude = AstroMath::normalizeDegrees(
        SunPosition::apparentLongitude($birthJulianDay) - Ayanamsa::lahiri($birthJulianDay)
    );

    $returnMoment = SolarReturn::find($natalSunLongitude, 2026, $birthMoment);

    $returnJulianDay = JulianDay::fromUtc($returnMoment->utc());
    $returnSunLongitude = AstroMath::normalizeDegrees(
        SunPosition::apparentLongitude($returnJulianDay) - Ayanamsa::lahiri($returnJulianDay)
    );

    $diff = abs($returnSunLongitude - $natalSunLongitude);
    $diff = min($diff, 360 - $diff);

    expect($diff)->toBeLessThan(0.001);
});

test('the returned moment falls in the requested year, near the birth anniversary', function () {
    $birthMoment = CarbonImmutable::parse('1994-05-12 14:30:00', 'UTC');
    $birthJulianDay = JulianDay::fromUtc($birthMoment);
    $natalSunLongitude = AstroMath::normalizeDegrees(
        SunPosition::apparentLongitude($birthJulianDay) - Ayanamsa::lahiri($birthJulianDay)
    );

    $returnMoment = SolarReturn::find($natalSunLongitude, 2026, $birthMoment);

    expect($returnMoment->year)->toBe(2026)
        ->and($returnMoment->month)->toBe(5)
        ->and($returnMoment->day)->toBeGreaterThanOrEqual(10)->toBeLessThanOrEqual(14);
});
