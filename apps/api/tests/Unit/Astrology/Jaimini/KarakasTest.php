<?php

use App\Services\Astrology\Jaimini\Karakas;

test('Atmakaraka is the classical planet with the highest degree within its own sign', function () {
    $planetLongitudes = [
        'Sun' => 10.0,    // Aries, 10 degrees in sign
        'Moon' => 25.0,   // Aries, 25 degrees in sign -- the highest
        'Mars' => 65.0,   // Gemini, 5 degrees in sign
        'Mercury' => 100.0, // Cancer, 10 degrees
        'Jupiter' => 200.0, // Libra, 20 degrees
        'Venus' => 300.0,  // Capricorn, 0 degrees
        'Saturn' => 350.0, // Pisces, 20 degrees
    ];

    expect(Karakas::atmakaraka($planetLongitudes))->toBe('Moon');
});

test('Karakamsa is the Navamsa sign of the Atmakaraka', function () {
    $planetLongitudes = [
        'Sun' => 10.0, 'Moon' => 25.0, 'Mars' => 65.0, 'Mercury' => 100.0,
        'Jupiter' => 200.0, 'Venus' => 300.0, 'Saturn' => 350.0,
    ];

    // Atmakaraka is Moon at 25 degrees (Aries, a fire sign) -> Navamsa
    // starts counting from Aries; 25/3.3333 = segment 7 -> Aries+7 = Scorpio.
    expect(Karakas::karakamsa($planetLongitudes))->toBe('Scorpio');
});

test('Swamsa is the Navamsa sign of the Ascendant point itself', function () {
    // Ascendant at 100 degrees (Cancer, a water sign) -> Navamsa starts
    // counting from Cancer; 10/3.3333 = segment 3 -> Cancer+3 = Libra.
    expect(Karakas::swamsa(100.0))->toBe('Libra');
});
