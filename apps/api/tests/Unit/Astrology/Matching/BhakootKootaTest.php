<?php

use App\Services\Astrology\Matching\Kootas\BhakootKoota;

test('a 6/8 sign distance triggers Bhakoot Dosha and scores zero', function () {
    // Aries to Virgo is 6 signs apart.
    expect(BhakootKoota::evaluate('Aries', 'Virgo')['points'])->toBe(0.0);
});

test('a clear sign distance scores full marks', function () {
    // Aries to Gemini is 3 signs apart, clear of the dosha distances.
    expect(BhakootKoota::evaluate('Aries', 'Gemini')['points'])->toBe(7.0);
});
