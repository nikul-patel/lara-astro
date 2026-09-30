<?php

use App\Services\Astrology\Matching\Kootas\VashyaKoota;

test('same vashya group scores full marks', function () {
    // Aries and Taurus are both Chatushpada.
    expect(VashyaKoota::evaluate('Aries', 'Taurus')['points'])->toBe(2.0);
});

test('a documented partial-credit pair scores half marks', function () {
    // Gemini (Manav) and Cancer (Jalachar) is a listed partial pair.
    expect(VashyaKoota::evaluate('Gemini', 'Cancer')['points'])->toBe(1.0);
});

test('an unrelated pair scores zero', function () {
    // Scorpio (Keet) and Leo (Vanchar) is neither same-group nor listed.
    expect(VashyaKoota::evaluate('Scorpio', 'Leo')['points'])->toBe(0.0);
});
