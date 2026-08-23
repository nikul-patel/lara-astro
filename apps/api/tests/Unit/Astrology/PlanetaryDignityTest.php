<?php

use App\Services\Astrology\PlanetaryDignity;
use App\Services\Astrology\ZodiacSigns;

test('isExalted and isDebilitated match the classical sign table', function () {
    expect(PlanetaryDignity::isExalted('Sun', 'Aries'))->toBeTrue()
        ->and(PlanetaryDignity::isDebilitated('Sun', 'Libra'))->toBeTrue()
        ->and(PlanetaryDignity::isExalted('Sun', 'Libra'))->toBeFalse();
});

test('exaltation and debilitation signs are always opposite signs', function () {
    foreach (PlanetaryDignity::EXALTATION_SIGN as $planet => $exaltationSign) {
        $exaltationIndex = array_search($exaltationSign, ZodiacSigns::NAMES, true);
        $debilitationIndex = array_search(PlanetaryDignity::DEBILITATION_SIGN[$planet], ZodiacSigns::NAMES, true);

        expect(($exaltationIndex + 6) % 12)->toBe($debilitationIndex);
    }
});

test('isOwnSign recognizes both signs for dual-ruled planets', function () {
    expect(PlanetaryDignity::isOwnSign('Mars', 'Aries'))->toBeTrue()
        ->and(PlanetaryDignity::isOwnSign('Mars', 'Scorpio'))->toBeTrue()
        ->and(PlanetaryDignity::isOwnSign('Mars', 'Taurus'))->toBeFalse();
});

test('exaltationSignRuler and debilitationSignRuler resolve via the sign-ruler map', function () {
    // Saturn is exalted in Libra (ruled by Venus) and debilitated in Aries (ruled by Mars).
    expect(PlanetaryDignity::exaltationSignRuler('Saturn'))->toBe('Venus')
        ->and(PlanetaryDignity::debilitationSignRuler('Saturn'))->toBe('Mars');
});

test('isCombust flags a planet within its orb of the Sun and not beyond it', function () {
    $longitudes = ['Sun' => 100.0, 'Mercury' => 105.0, 'Jupiter' => 130.0];

    expect(PlanetaryDignity::isCombust('Mercury', $longitudes))->toBeTrue() // 5° < 14° orb
        ->and(PlanetaryDignity::isCombust('Jupiter', $longitudes))->toBeFalse(); // 30° > 11° orb
});

test('isCombust handles the wrap-around near 0°/360°', function () {
    $longitudes = ['Sun' => 359.0, 'Moon' => 3.0]; // 4° apart across the 0° boundary

    expect(PlanetaryDignity::isCombust('Moon', $longitudes))->toBeTrue();
});
