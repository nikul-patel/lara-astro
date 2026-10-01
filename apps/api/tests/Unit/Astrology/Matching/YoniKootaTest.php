<?php

use App\Services\Astrology\Matching\Kootas\YoniKoota;

test('identical yoni scores full marks', function () {
    expect(YoniKoota::evaluate('Ashwini', 'Ashwini')['points'])->toBe(4.0);
});

test('a natural-enemy yoni pair scores zero', function () {
    // Rohini = Serpent, Uttara Ashadha = Mongoose; Serpent-Mongoose is a listed enemy pair.
    expect(YoniKoota::evaluate('Rohini', 'Uttara Ashadha')['points'])->toBe(0.0);
});

test('an unrelated yoni pair scores neutral marks', function () {
    // Ashwini = Horse, Bharani = Elephant; neither identical nor enemies.
    expect(YoniKoota::evaluate('Ashwini', 'Bharani')['points'])->toBe(2.0);
});
