<?php

use App\Services\Astrology\Matching\Kootas\TaraKoota;

test('identical nakshatra scores full marks (tara #1 both directions)', function () {
    expect(TaraKoota::evaluate(0, 0)['points'])->toBe(3.0);
});

test('one favourable and one inauspicious direction scores half marks', function () {
    // bride index 0 (Ashwini) -> groom index 2 (Krittika): inclusive count
    // = ((2-0+27)%27)+1 = 3, tara = 3 mod 9 = 3 -> Vipat, inauspicious.
    // groom index 2 -> bride index 0: count = ((0-2+27)%27)+1 = 26,
    // tara = 26 mod 9 = 8 -> favourable. One good, one bad -> 1.5.
    expect(TaraKoota::evaluate(0, 2)['points'])->toBe(1.5);
});

test('a favourable pair with no inauspicious direction scores full marks', function () {
    // bride index 0 -> groom index 1: count = 2, tara = 2 -> favourable.
    // groom index 1 -> bride index 0: count = ((0-1+27)%27)+1 = 27,
    // tara = 27 mod 9 = 0 -> treated as 9 -> favourable.
    expect(TaraKoota::evaluate(0, 1)['points'])->toBe(3.0);
});
