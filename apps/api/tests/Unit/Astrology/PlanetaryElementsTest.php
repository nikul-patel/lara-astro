<?php

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\PlanetaryElements;
use App\Services\Astrology\SunPosition;
use Carbon\CarbonImmutable;

test('earth\'s heliocentric longitude stays consistent with the sun\'s geocentric longitude across a century', function (string $date) {
    $jd = JulianDay::fromUtc(CarbonImmutable::parse($date, 'UTC'));

    $sunGeocentricLongitude = SunPosition::apparentLongitude($jd);
    [$earthX, $earthY] = PlanetaryElements::heliocentricPosition('earth', $jd);
    $t = JulianDay::centuriesSinceJ2000($jd);
    $earthHeliocentricLongitude = AstroMath::normalizeDegrees(AstroMath::atan2Deg($earthY, $earthX) + (5029.0966 / 3600) * $t);

    // The Sun's geocentric position and Earth's heliocentric position are
    // always exactly opposite (180° apart) — the two independent formulas
    // (Meeus's Sun series vs. Standish's Keplerian elements) should agree
    // to well within a tenth of a degree once the precession correction
    // (PlanetaryElements::geocentricLongitude's docblock) is applied.
    $expected = AstroMath::normalizeDegrees($sunGeocentricLongitude + 180);
    $diff = AstroMath::normalizeDegrees($earthHeliocentricLongitude - $expected);
    if ($diff > 180) {
        $diff -= 360;
    }

    expect(abs($diff))->toBeLessThan(0.05);
})->with([
    '1975-03-01 00:00:00',
    '1992-10-13 00:00:00',
    '2000-01-01 12:00:00',
    '2024-06-15 06:00:00',
]);

test('planetary longitudes are always normalized to a valid degree range', function (string $planet) {
    $jd = JulianDay::fromUtc(CarbonImmutable::parse('1992-10-13 00:00:00', 'UTC'));

    $longitude = PlanetaryElements::geocentricLongitude($planet, $jd);

    expect($longitude)->toBeGreaterThanOrEqual(0)->toBeLessThan(360);
})->with(['mercury', 'venus', 'mars', 'jupiter', 'saturn']);
