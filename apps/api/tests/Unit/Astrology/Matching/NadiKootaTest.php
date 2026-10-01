<?php

use App\Services\Astrology\Matching\Kootas\NadiKoota;

test('same nadi triggers Nadi Dosha and scores zero', function () {
    // Ashwini and Ardra are both Aadi nadi.
    expect(NadiKoota::evaluate('Ashwini', 'Ardra')['points'])->toBe(0.0);
});

test('different nadi scores full marks', function () {
    // Ashwini = Aadi, Bharani = Madhya.
    expect(NadiKoota::evaluate('Ashwini', 'Bharani')['points'])->toBe(8.0);
});
