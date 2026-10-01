<?php

use App\Services\Astrology\Avastha;

test('Baladi Avastha steps forward through the 5 states for an odd sign as degree-in-sign increases', function () {
    // Aries (index 0) is odd.
    expect(Avastha::baladi(0.0))->toBe('Bala');     // 0 Aries.
    expect(Avastha::baladi(5.99))->toBe('Bala');    // 5.99 Aries, still inside the 0-6 band.
    expect(Avastha::baladi(6.0))->toBe('Kumara');   // 6 Aries, start of the next band.
    expect(Avastha::baladi(15.0))->toBe('Yuva');    // 15 Aries, the peak middle band.
    expect(Avastha::baladi(20.0))->toBe('Vriddha'); // 20 Aries.
    expect(Avastha::baladi(29.99))->toBe('Mrita');  // 29.99 Aries, the last band.
});

test('Baladi Avastha reverses direction for an even sign, but the 12-18 degree peak band is always Yuva', function () {
    // Taurus (index 1, longitude 30-60) is even.
    expect(Avastha::baladi(30.0))->toBe('Mrita');   // 0 Taurus -> reversed, so the weakest state starts here.
    expect(Avastha::baladi(45.0))->toBe('Yuva');    // 15 Taurus, same peak band as the odd-sign case.
    expect(Avastha::baladi(59.99))->toBe('Bala');   // 29.99 Taurus -> reversed, strongest state ends here.
});

test('Jagrat/Swapna/Sushupta: own sign and exaltation are both Jagrat', function () {
    expect(Avastha::jagratSwapnaSushupta('Sun', 'Leo'))->toBe('Jagrat');   // Own sign.
    expect(Avastha::jagratSwapnaSushupta('Sun', 'Aries'))->toBe('Jagrat'); // Exalted.
});

test('Jagrat/Swapna/Sushupta: debilitation is always Sushupta, even for a planet (Moon) with no natural enemies', function () {
    expect(Avastha::jagratSwapnaSushupta('Sun', 'Libra'))->toBe('Sushupta');  // Sun debilitated in Libra.
    expect(Avastha::jagratSwapnaSushupta('Moon', 'Scorpio'))->toBe('Sushupta'); // Moon debilitated in Scorpio, despite PlanetaryFriendship::ENEMIES['Moon'] being empty.
});

test('Jagrat/Swapna/Sushupta: a naturally inimical sign lord (outside exaltation/debilitation) is Sushupta', function () {
    // Sun's natural enemies are Venus and Saturn (PlanetaryFriendship::ENEMIES['Sun']).
    // Venus rules Taurus/Libra; Libra is the Sun's debilitation (covered by
    // the test above), so Taurus isolates the "natural enemy, not debilitated" case.
    expect(Avastha::jagratSwapnaSushupta('Sun', 'Taurus'))->toBe('Sushupta');
});

test('Jagrat/Swapna/Sushupta: a naturally friendly or neutral sign lord is Swapna', function () {
    // Sun's natural friends are Moon, Mars, Jupiter (PlanetaryFriendship::FRIENDS['Sun']).
    expect(Avastha::jagratSwapnaSushupta('Sun', 'Cancer'))->toBe('Swapna');      // Moon's sign, friend.
    expect(Avastha::jagratSwapnaSushupta('Sun', 'Sagittarius'))->toBe('Swapna'); // Jupiter's sign, friend.
    // Mercury is neutral to the Sun (neither in FRIENDS nor ENEMIES['Sun']).
    expect(Avastha::jagratSwapnaSushupta('Sun', 'Gemini'))->toBe('Swapna');
});

test('forChart() returns both Avastha systems for all 7 classical planets', function () {
    $chartLongitudes = [
        'Sun' => 10.0, 'Moon' => 100.0, 'Mars' => 65.0, 'Mercury' => 200.0,
        'Jupiter' => 280.0, 'Venus' => 300.0, 'Saturn' => 350.0,
    ];
    $rasiSigns = [
        'Sun' => 'Aries', 'Moon' => 'Cancer', 'Mars' => 'Gemini', 'Mercury' => 'Sagittarius',
        'Jupiter' => 'Capricorn', 'Venus' => 'Capricorn', 'Saturn' => 'Pisces',
    ];

    $result = Avastha::forChart($chartLongitudes, $rasiSigns);

    expect(array_keys($result))->toBe(['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn']);
    expect($result['Sun'])->toBe(['baladi' => 'Kumara', 'jagrat_swapna_sushupta' => 'Jagrat']); // 10 Aries: own sign AND exalted.
    foreach ($result as $planetResult) {
        expect($planetResult['baladi'])->toBeIn(['Bala', 'Kumara', 'Yuva', 'Vriddha', 'Mrita']);
        expect($planetResult['jagrat_swapna_sushupta'])->toBeIn(['Jagrat', 'Swapna', 'Sushupta']);
    }
});
