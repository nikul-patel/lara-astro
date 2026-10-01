<?php

use App\Services\Astrology\Matching\Kootas\GrahaMaitriKoota;

test('mutual friend lords score full marks', function () {
    // Cancer (Moon) and Leo (Sun): Moon counts Sun a friend, Sun counts Moon a friend.
    expect(GrahaMaitriKoota::evaluate('Cancer', 'Leo')['points'])->toBe(5.0);
});

test('mutual enemy lords score zero', function () {
    // Taurus (Venus) and Leo (Sun): Venus counts Sun an enemy, Sun counts Venus an enemy.
    expect(GrahaMaitriKoota::evaluate('Taurus', 'Leo')['points'])->toBe(0.0);
});

test('a neutral/enemy mix scores half a point', function () {
    // Cancer (Moon) and Taurus (Venus): Moon is neutral to Venus, Venus counts Moon an enemy.
    expect(GrahaMaitriKoota::evaluate('Cancer', 'Taurus')['points'])->toBe(0.5);
});
