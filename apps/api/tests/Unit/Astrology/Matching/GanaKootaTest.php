<?php

use App\Services\Astrology\Matching\Kootas\GanaKoota;

test('same gana scores full marks', function () {
    // Ashwini is Deva for both partners.
    expect(GanaKoota::evaluate('Ashwini', 'Ashwini')['points'])->toBe(6.0);
});

test('Manushya groom with Rakshasa bride scores zero', function () {
    // Bride Krittika = Rakshasa, groom Bharani = Manushya.
    expect(GanaKoota::evaluate('Krittika', 'Bharani')['points'])->toBe(0.0);
});

test('Deva groom with Rakshasa bride scores a nonzero but reduced value', function () {
    // Bride Krittika = Rakshasa, groom Ashwini = Deva.
    expect(GanaKoota::evaluate('Krittika', 'Ashwini')['points'])->toBe(1.0);
});
