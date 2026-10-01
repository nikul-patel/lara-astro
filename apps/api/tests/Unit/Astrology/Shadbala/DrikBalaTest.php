<?php

use App\Services\Astrology\Shadbala\DrikBala;

/**
 * Places every classical planet at Moon's own longitude (so they cast a
 * trivial 0-degree, 0-strength "aspect" on it) except for the one planet
 * under test, isolating a single aspect's contribution to Moon's net Drik
 * Bala. Moon itself is always the aspected target, longitude 0.
 *
 * @return array<string, float>
 */
function drikBalaIsolatedAspectFixture(string $aspectingPlanet, float $aspectingLongitude): array
{
    $chart = array_fill_keys(['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'], 0.0);
    $chart[$aspectingPlanet] = $aspectingLongitude;

    return $chart;
}

test('a malefic\'s universal (non-special) aspect at exactly 180 degrees scores the full 60, subtracted', function () {
    // Mars at longitude 180 aspects Moon (at 0) across exactly 180
    // degrees. Every planet's base formula gives 60 there (the universal
    // 7th-house aspect), and 180 isn't in any of Mars's own special
    // (4th/8th) zones, so this isolates the plain base formula.
    $chart = drikBalaIsolatedAspectFixture('Mars', 180.0);

    // net = -60 (Mars is malefic) / 4 = -15.
    expect(DrikBala::calculate($chart)['Moon'])->toBe(-15.0);
});

test('Saturn\'s special 3rd-house aspect reaches a full 60 at exactly 60 degrees, where the base formula alone would only give 15', function () {
    // Aspecting longitude 300 -> angle to Moon (at 0) = (0-300) mod 360 = 60.
    // Base formula at a=60 (the 60<=a<90 branch): (60-60)+15 = 15.
    // Saturn's special-aspect bonus (60<=a<90) adds 45 -> exactly 60, no capping needed.
    $chart = drikBalaIsolatedAspectFixture('Saturn', 300.0);

    // Saturn is malefic: net = -60 / 4 = -15.
    expect(DrikBala::calculate($chart)['Moon'])->toBe(-15.0);
});

test('Mars\'s special 4th-house aspect reaches a full 60 at exactly 90 degrees', function () {
    // Aspecting longitude 270 -> angle to Moon = (0-270) mod 360 = 90.
    // Base formula at a=90 (90<=a<120 branch): 0.5*(120-90)+30 = 45.
    // Mars's special-aspect bonus (90<=a<120) adds 15 -> exactly 60.
    $chart = drikBalaIsolatedAspectFixture('Mars', 270.0);

    expect(DrikBala::calculate($chart)['Moon'])->toBe(-15.0); // Malefic.
});

test('Jupiter\'s special 5th-house aspect reaches a full 60 at exactly 120 degrees, and benefics add rather than subtract', function () {
    // Aspecting longitude 240 -> angle to Moon = (0-240) mod 360 = 120.
    // Base formula at a=120 (120<=a<150 branch): 150-120 = 30.
    // Jupiter's special-aspect bonus (120<=a<150) adds 30 -> exactly 60.
    $chart = drikBalaIsolatedAspectFixture('Jupiter', 240.0);

    // Jupiter is benefic: net = +60 / 4 = 15.
    expect(DrikBala::calculate($chart)['Moon'])->toBe(15.0);
});

test('a conjunction (0 degrees) carries no aspect strength at all', function () {
    $chart = drikBalaIsolatedAspectFixture('Saturn', 0.0);

    expect(DrikBala::calculate($chart)['Moon'])->toBe(0.0);
});

test('multiple aspecting planets sum before the final /4 division', function () {
    // Jupiter at 240 (benefic, +60 per the special-aspect case above) and
    // Mars at 270 (malefic, +60-magnitude per its special-aspect case,
    // subtracted) on the same target: net = (60 - 60) / 4 = 0.
    $chart = array_fill_keys(['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'], 0.0);
    $chart['Jupiter'] = 240.0;
    $chart['Mars'] = 270.0;

    expect(DrikBala::calculate($chart)['Moon'])->toBe(0.0);
});
