<?php

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Houses;
use App\Services\Astrology\Houses\PlacidusCusps;
use App\Services\Astrology\JulianDay;
use Carbon\CarbonImmutable;

/**
 * Independently recomputes a cusp's right ascension from its returned
 * ecliptic longitude, and checks it against Placidus's own defining
 * equation (see PlacidusCusps's class docblock) — this verifies the
 * OUTPUT satisfies the actual mathematical definition of a Placidus
 * cusp, independent of however the production code arrived at it (so
 * this test can't just be checking the iteration against itself).
 */
function assertSatisfiesPlacidusEquation(float $longitude, float $baseRightAscension, float $fraction, float $latitude, float $obliquity): void
{
    $declination = rad2deg(asin(sin(deg2rad($obliquity)) * sin(deg2rad($longitude))));
    $ascensionalDifference = rad2deg(asin(tan(deg2rad($latitude)) * tan(deg2rad($declination))));
    $expectedRightAscension = AstroMath::normalizeDegrees($baseRightAscension + $fraction * $ascensionalDifference);

    $actualRightAscension = AstroMath::normalizeDegrees(
        AstroMath::atan2Deg(AstroMath::sinDeg($longitude) * AstroMath::cosDeg($obliquity), AstroMath::cosDeg($longitude))
    );

    $diff = abs(AstroMath::normalizeDegrees($actualRightAscension - $expectedRightAscension + 180) - 180);
    expect($diff)->toBeLessThan(1e-4);
}

test('cusps 11 and 12 satisfy Placidus\'s own defining semi-arc equation for a real northern latitude', function () {
    // Inputs from a cited worked example (RAMC=9.77674583, latitude
    // 52.51627778N) — see PlacidusCusps's class docblock for why the
    // example's own cited cusp numbers aren't used directly (one
    // didn't hold up under verification), only its latitude/RAMC as a
    // real, non-trivial test input.
    $ramcTarget = 9.77674583;
    $latitude = 52.51627778;
    $julianDay = 2451545.0; // J2000.0 exactly: GST = 280.46061837 exactly, obliquity = 23.4392911 exactly.
    $obliquity = Houses::obliquity($julianDay);
    $longitude = AstroMath::normalizeDegrees($ramcTarget - Houses::greenwichSiderealTime($julianDay));

    $cusps = PlacidusCusps::calculate($julianDay, $latitude, $longitude);

    assertSatisfiesPlacidusEquation($cusps[11], $ramcTarget + 30, 1 / 3, $latitude, $obliquity);
    assertSatisfiesPlacidusEquation($cusps[12], $ramcTarget + 60, 2 / 3, $latitude, $obliquity);
    assertSatisfiesPlacidusEquation($cusps[2], $ramcTarget + 120, 2 / 3, $latitude, $obliquity);
    assertSatisfiesPlacidusEquation($cusps[3], $ramcTarget + 150, 1 / 3, $latitude, $obliquity);
});

test('at the equator, intermediate cusps reduce exactly to a direct right-ascension-to-longitude conversion', function () {
    // At latitude 0, ascensional difference is exactly 0 for every
    // point (asin(tan(0)*tan(d))=asin(0)=0), so Placidus's semi-arc
    // correction vanishes entirely and the cusp is simply the
    // ecliptic point whose right ascension equals RAMC+30/60/120/150 —
    // the same formula this class's Midheaven uses, generalized. A
    // fully self-verifiable identity, no external citation needed.
    $julianDay = JulianDay::fromUtc(CarbonImmutable::parse('2000-06-15 12:00:00', 'UTC'));
    $latitude = 0.0;
    $longitude = 37.0;

    $ramc = AstroMath::normalizeDegrees(Houses::greenwichSiderealTime($julianDay) + $longitude);
    $obliquity = Houses::obliquity($julianDay);
    $cusps = PlacidusCusps::calculate($julianDay, $latitude, $longitude);

    foreach ([11 => 30, 12 => 60, 2 => 120, 3 => 150] as $house => $offset) {
        $expected = AstroMath::normalizeDegrees(
            AstroMath::atan2Deg(AstroMath::sinDeg($ramc + $offset), AstroMath::cosDeg($ramc + $offset) * AstroMath::cosDeg($obliquity))
        );
        expect($cusps[$house])->toBeFloat();
        expect(abs(AstroMath::normalizeDegrees($cusps[$house] - $expected + 180) - 180))->toBeLessThan(1e-6);
    }
});

test('cusp 1 is exactly the Ascendant and cusp 10 is exactly the Midheaven', function () {
    $julianDay = JulianDay::fromUtc(CarbonImmutable::parse('1994-05-12 09:00:00', 'UTC'));
    $latitude = 26.9124;
    $longitude = 75.7873;

    $cusps = PlacidusCusps::calculate($julianDay, $latitude, $longitude);

    expect($cusps[1])->toBe(Houses::ascendant($julianDay, $latitude, $longitude));

    $ramc = AstroMath::normalizeDegrees(Houses::greenwichSiderealTime($julianDay) + $longitude);
    $obliquity = Houses::obliquity($julianDay);
    $expectedMc = AstroMath::normalizeDegrees(
        AstroMath::atan2Deg(AstroMath::sinDeg($ramc), AstroMath::cosDeg($ramc) * AstroMath::cosDeg($obliquity))
    );
    expect($cusps[10])->toBe($expectedMc);
});

test('every opposite pair of cusps is exactly 180 degrees apart, and houses advance monotonically around the circle', function () {
    $julianDay = JulianDay::fromUtc(CarbonImmutable::parse('1994-05-12 09:00:00', 'UTC'));
    $cusps = PlacidusCusps::calculate($julianDay, 26.9124, 75.7873);

    foreach ([[1, 7], [2, 8], [3, 9], [4, 10], [5, 11], [6, 12]] as [$a, $b]) {
        $diff = abs(AstroMath::normalizeDegrees($cusps[$b] - $cusps[$a]) - 180);
        expect($diff)->toBeLessThan(1e-6);
    }

    // Unrolled from cusp 1, each successive cusp (mod 360) must be
    // strictly further along than the last, all the way back around.
    $unrolled = [];
    $previous = null;
    foreach (range(1, 12) as $house) {
        $value = $cusps[$house];
        if ($previous !== null && $value < $previous) {
            $value += 360;
        }
        $unrolled[] = $value;
        $previous = $value;
    }
    for ($i = 1; $i < 12; $i++) {
        expect($unrolled[$i])->toBeGreaterThan($unrolled[$i - 1]);
    }
    expect($unrolled[11])->toBeLessThan($cusps[1] + 360);
});

test('planetsByHouse() assigns each planet to the cusp-bounded span containing its longitude, including boundary cases', function () {
    $cusps = [1 => 0.0, 2 => 30.0, 3 => 60.0, 4 => 90.0, 5 => 120.0, 6 => 150.0, 7 => 180.0, 8 => 210.0, 9 => 240.0, 10 => 270.0, 11 => 300.0, 12 => 330.0];

    $byHouse = PlacidusCusps::planetsByHouse($cusps, [
        'Sun' => 15.0,   // Mid house 1 (0-30).
        'Moon' => 0.0,   // Exactly at the house 1 cusp -> inclusive start, belongs to house 1.
        'Mars' => 30.0,  // Exactly at the house 2 cusp -> exclusive end for house 1, belongs to house 2.
        'Mercury' => 355.0, // Wraps past house 12's end (330) toward house 1 (0) -> house 12.
    ]);

    expect($byHouse[1])->toBe(['Sun', 'Moon']);
    expect($byHouse[2])->toBe(['Mars']);
    expect($byHouse[12])->toBe(['Mercury']);
    expect($byHouse[5])->toBe([]);
});

test('returns all 12 house numbers with no missing or duplicate keys', function () {
    $julianDay = JulianDay::fromUtc(CarbonImmutable::parse('1994-05-12 09:00:00', 'UTC'));
    $cusps = PlacidusCusps::calculate($julianDay, 26.9124, 75.7873);

    expect(array_keys($cusps))->toBe(range(1, 12));
    foreach ($cusps as $value) {
        expect($value)->toBeGreaterThanOrEqual(0.0)->toBeLessThan(360.0);
    }
});
