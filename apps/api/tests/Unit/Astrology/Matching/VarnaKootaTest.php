<?php

use App\Services\Astrology\Matching\Kootas\VarnaKoota;

test('equal varna scores full marks', function () {
    // Cancer and Scorpio are both Brahmin varna.
    expect(VarnaKoota::evaluate('Cancer', 'Scorpio')['points'])->toBe(1.0);
});

test('groom outranking bride scores full marks', function () {
    // Bride Gemini = Shudra (lowest), groom Cancer = Brahmin (highest).
    expect(VarnaKoota::evaluate('Gemini', 'Cancer')['points'])->toBe(1.0);
});

test('groom ranking below bride scores zero', function () {
    // Bride Cancer = Brahmin (highest), groom Aries = Kshatriya (lower).
    expect(VarnaKoota::evaluate('Cancer', 'Aries')['points'])->toBe(0.0);
});
